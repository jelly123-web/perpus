<div class="loan-card">
    <div class="loan-card-header">
        <div>
            <h2 class="loan-card-title">Pengajuan</h2>
            <p class="loan-card-subtitle">Pengajuan dari akun peminjam.</p>
        </div>
        <span class="loan-item-badge pending" id="loanRequestedBadge">{{ $requestedLoans->count() }} Menunggu</span>
    </div>
    <div class="loan-card-body">
        <div class="flex flex-col gap-4">
            @forelse ($requestedLoans as $requestedLoan)
                <div class="loan-item-card">
                    <div class="loan-item-head">
                        <div>
                            <div class="loan-item-name">{{ $requestedLoan->member?->name ?? 'Peminjam' }}</div>
                            <div class="loan-item-info mt-1">
                                <strong>{{ $requestedLoan->book?->title ?? 'Buku' }}</strong>
                            </div>
                        </div>
                        <span class="loan-item-badge pending">Sistem</span>
                    </div>
                    <div class="loan-item-info">
                        Pinjam: {{ optional($requestedLoan->borrowed_at)->translatedFormat('d M Y') }}<br>
                        Batas: {{ optional($requestedLoan->due_at)->translatedFormat('d M Y') }}
                    </div>
                    @if($requestedLoan->notes)
                        <div class="p-3 rounded-xl bg-white border border-slate2-100 text-xs italic text-slate2-500">
                            "{{ $requestedLoan->notes }}"
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.loans.update', $requestedLoan) }}" data-async="true" data-refresh-targets="#loanStatsWrap,#loanPageWrap">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="borrowed">
                        <button type="submit" class="btn-loan-submit">
                            <i data-lucide="book-check" class="w-4 h-4"></i> Proses Sekarang
                        </button>
                    </form>
                </div>
            @empty
                <div class="report-empty-state" style="padding: 40px 20px;">
                    <div class="report-empty-icon"><i data-lucide="inbox"></i></div>
                    <p class="report-empty-text">Belum ada pengajuan baru.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
