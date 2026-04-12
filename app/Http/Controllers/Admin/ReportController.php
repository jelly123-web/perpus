<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        [$bookReport, $loanReport, $returnReport] = $this->getReportCollections($filters);

        $reportStats = $this->buildReportStats($bookReport, $loanReport, $returnReport);
        $usageStats = $this->buildUsageStats($loanReport, $returnReport);

        $reportMeta = [
            'title' => $this->buildReportTitle($filters),
            'range_label' => $this->buildRangeLabel($filters['start'], $filters['end']),
            'printed_at' => now()->translatedFormat('d F Y H:i'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Laporan diperbarui.',
                'status' => 'success'
            ]);
        }

        return view('admin.reports.index', compact(
            'bookReport',
            'loanReport',
            'returnReport',
            'reportStats',
            'usageStats',
            'reportMeta',
            'filters',
        ));
    }

    public function export(Request $request)
    {
        $filters = $this->resolveFilters($request);
        [$bookReport, $loanReport, $returnReport] = $this->getReportCollections($filters);
        $format = $request->query('format', 'excel');

        $reportStats = $this->buildReportStats($bookReport, $loanReport, $returnReport);
        $usageStats = $this->buildUsageStats($loanReport, $returnReport);
        
        $appName = \App\Models\Setting::valueOr('app_name', 'LibraVault');
        $appLogo = \App\Models\Setting::valueOr('app_logo');
        $appLogoBase64 = null;

        if ($appLogo && file_exists(public_path($appLogo))) {
            $path = public_path($appLogo);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $appLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $reportMeta = [
            'title' => $this->buildReportTitle($filters),
            'range_label' => $this->buildRangeLabel($filters['start'], $filters['end']),
            'printed_at' => now()->translatedFormat('d F Y H:i'),
            'app_name' => $appName,
            'app_logo' => $appLogoBase64,
        ];

        $filename = $this->buildExportFileName($filters, $format);

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.export-pdf', compact(
                'bookReport',
                'loanReport',
                'returnReport',
                'reportStats',
                'usageStats',
                'reportMeta',
                'filters',
            ));
            return $pdf->download($filename);
        }

        // Default to Excel (using HTML table)
        $html = view('admin.reports.export-excel', compact(
            'bookReport',
            'loanReport',
            'returnReport',
            'reportStats',
            'usageStats',
            'reportMeta',
            'filters',
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $period = $request->string('period')->toString() ?: 'monthly';
        $type = $request->string('type')->toString() ?: 'all';
        $today = now();

        [$start, $end] = match ($period) {
            'daily' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'weekly' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'yearly' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom' => $this->resolveCustomRange($request),
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };

        return [
            'period' => in_array($period, ['daily', 'weekly', 'monthly', 'yearly', 'custom'], true) ? $period : 'monthly',
            'type' => in_array($type, ['all', 'books', 'loans', 'returns'], true) ? $type : 'all',
            'start' => $start,
            'end' => $end,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'date_field' => 'created_at',
        ];
    }

    private function resolveCustomRange(Request $request): array
    {
        $startInput = $request->string('start_date')->toString();
        $endInput = $request->string('end_date')->toString();

        $start = $startInput !== '' ? Carbon::parse($startInput)->startOfDay() : now()->startOfMonth();
        $end = $endInput !== '' ? Carbon::parse($endInput)->endOfDay() : now()->endOfMonth();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private function buildReportTitle(array $filters): string
    {
        $labels = [
            'daily' => 'Laporan Harian',
            'weekly' => 'Laporan Mingguan',
            'monthly' => 'Laporan Bulanan',
            'yearly' => 'Laporan Tahunan',
            'custom' => 'Laporan Periode Kustom',
        ];

        return $labels[$filters['period']] ?? 'Laporan';
    }

    private function buildRangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->translatedFormat('d F Y');
        }

        return $start->translatedFormat('d F Y').' - '.$end->translatedFormat('d F Y');
    }

    private function getReportCollections(array $filters): array
    {
        $bookReport = Book::query()
            ->with('category')
            ->withCount('loans')
            ->when(
                $filters['date_field'] === 'created_at',
                fn ($query) => $query->whereBetween('created_at', [$filters['start'], $filters['end']])
            )
            ->latest()
            ->get();

        $loanReport = Loan::query()
            ->with(['book.category', 'member', 'processor'])
            ->whereBetween('borrowed_at', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->latest('borrowed_at')
            ->get();

        $returnReport = Loan::query()
            ->with(['book.category', 'member', 'processor'])
            ->whereNotNull('returned_at')
            ->whereBetween('returned_at', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->latest('returned_at')
            ->get();

        return [$bookReport, $loanReport, $returnReport];
    }

    private function buildReportStats($bookReport, $loanReport, $returnReport): array
    {
        return [
            'books' => $bookReport->count(),
            'loans' => $loanReport->count(),
            'returns' => $returnReport->count(),
            'returned_late' => $returnReport->where('status', 'late')->count(),
            'active_loans' => $loanReport->whereIn('status', ['borrowed', 'late'])->count(),
        ];
    }

    private function buildUsageStats($loanReport, $returnReport): array
    {
        $topBook = $loanReport
            ->groupBy('book_id')
            ->sortByDesc(fn ($group) => $group->count())
            ->map(fn ($group) => $group->first()?->book?->title)
            ->filter()
            ->first();

        $topCategory = $loanReport
            ->groupBy(fn ($loan) => $loan->book?->category?->name)
            ->sortByDesc(fn ($group) => $group->count())
            ->keys()
            ->filter()
            ->first();

        return [
            'unique_borrowers' => $loanReport->pluck('member_id')->filter()->unique()->count(),
            'books_in_circulation' => $loanReport->pluck('book_id')->filter()->unique()->count(),
            'completed_returns' => $returnReport->count(),
            'top_book' => $topBook ?: 'Belum ada',
            'top_category' => $topCategory ?: 'Belum ada',
        ];
    }


    private function buildExportFileName(array $filters, string $format): string
    {
        $prefix = match ($filters['type']) {
            'books' => 'Laporan-Buku',
            'loans' => 'Laporan-Peminjaman',
            'returns' => 'Laporan-Pengembalian',
            default => 'Laporan-Perpustakaan',
        };

        $ext = $format === 'pdf' ? 'pdf' : 'xls';

        return $prefix.'-'.now()->format('Y-m-d').'.'.$ext;
    }
}
