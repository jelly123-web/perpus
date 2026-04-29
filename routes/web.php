<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Member\LoanRequestController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RestoreController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp');
    Route::post('/login/otp/send', [LoginController::class, 'sendOtp'])->middleware('throttle:6,1')->name('login.otp.send');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('login.otp.verify');
    Route::get('/auth/google/redirect', [LoginController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/verify-code', [PasswordResetController::class, 'showVerifyCodeForm'])->name('password.verify_code');
    Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify_code.post');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::view('/email/verify', 'auth.verify-email')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): RedirectResponse {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Email berhasil diverifikasi.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request): RedirectResponse|\Illuminate\Http\JsonResponse {
        if ($request->user()?->hasVerifiedEmail()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email sudah terverifikasi.',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->route('dashboard');
        }

        $request->user()?->sendEmailVerificationNotification();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Link verifikasi baru sudah dikirim ke email Anda.',
            ]);
        }

        return back()->with('status', 'Link verifikasi baru sudah dikirim ke email Anda.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:access_dashboard')->name('dashboard');
    Route::get('/riwayat-peminjaman', [DashboardController::class, 'history'])->middleware('permission:view_borrower_history')->name('borrower.history');
    Route::get('/borrower/books', [DashboardController::class, 'borrowerBooks'])->middleware('permission:view_borrower_history')->name('borrower.books');
    Route::get('/borrower/notifications', [DashboardController::class, 'notifications'])->middleware('permission:view_borrower_history,manage_loans,view_reports')->name('borrower.notifications');
    Route::post('/chatbot/respond', [DashboardController::class, 'chatbotRespond'])->name('chatbot.respond');
    Route::get('/profil-saya', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil-saya', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/loan-requests', [LoanRequestController::class, 'store'])->name('loan-requests.store');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/users', [UserManagementController::class, 'index'])->middleware('permission:manage_users')->name('users.index');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->middleware('permission:manage_users')->name('users.edit');
        Route::post('/users', [UserManagementController::class, 'store'])->middleware('permission:manage_users')->name('users.store');
        Route::post('/users/import', [UserManagementController::class, 'import'])->middleware('permission:manage_users')->name('users.import');
        Route::get('/users/export', [UserManagementController::class, 'export'])->middleware('permission:manage_users')->name('users.export');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->middleware('permission:manage_users')->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->middleware('permission:manage_users')->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:manage_roles')->name('roles.index');
        Route::put('/roles/matrix', [RoleController::class, 'updateMatrix'])->middleware('permission:manage_roles')->name('roles.matrix.update');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:manage_roles')->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:manage_roles')->name('roles.update');

        Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:manage_categories')->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:manage_categories')->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:manage_categories')->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:manage_categories')->name('categories.destroy');

        Route::get('/books', [BookController::class, 'index'])->middleware('permission:manage_books')->name('books.index');
        Route::post('/books', [BookController::class, 'store'])->middleware('permission:manage_books')->name('books.store');
        Route::post('/books/import', [BookController::class, 'import'])->middleware('permission:manage_books')->name('books.import');
        Route::get('/books/export', [BookController::class, 'export'])->middleware('permission:manage_books')->name('books.export');
        Route::post('/books/procurements', [BookController::class, 'storeProcurement'])->middleware('permission:manage_books')->name('books.procurements.store');
        Route::put('/books/procurements/{procurement}/approve', [BookController::class, 'approveProcurement'])->middleware('permission:view_reports')->name('books.procurements.approve');
        Route::put('/books/procurements/{procurement}/reject', [BookController::class, 'rejectProcurement'])->middleware('permission:view_reports')->name('books.procurements.reject');
        Route::put('/books/{book}', [BookController::class, 'update'])->middleware('permission:manage_books')->name('books.update');
        Route::get('/books/{book}', [BookController::class, 'destroy'])->middleware('permission:manage_books')->name('books.destroy');
        Route::get('/books/search-by-image', [\App\Http\Controllers\Admin\BookSearchController::class, 'show'])->name('books.search-by-image.page');
        Route::post('/books/search-by-image', [\App\Http\Controllers\Admin\BookSearchController::class, 'searchByImage'])->name('books.search-by-image');

        Route::get('/loans', [LoanController::class, 'index'])->middleware('permission:manage_loans')->name('loans.index');
        Route::get('/loans/live-snapshot', [LoanController::class, 'liveSnapshot'])->middleware('permission:manage_loans')->name('loans.live-snapshot');
        Route::get('/loans/requested-panel', [LoanController::class, 'requestedPanel'])->middleware('permission:manage_loans')->name('loans.requested-panel');
        Route::post('/loans', [LoanController::class, 'store'])->middleware('permission:manage_loans')->name('loans.store');
        Route::post('/loans/return', [LoanController::class, 'returnBook'])->middleware('permission:manage_loans')->name('loans.return');
        Route::post('/loans/sanctions', [LoanController::class, 'storeSanction'])->middleware('permission:manage_loans')->name('loans.sanctions.store');
        Route::post('/loans/borrower-status', [LoanController::class, 'updateBorrowerStatus'])->middleware('permission:manage_loans')->name('loans.borrower-status.update');
        Route::put('/loans/sanctions/{sanction}', [LoanController::class, 'updateSanctionStatus'])->middleware('permission:manage_loans')->name('loans.sanctions.update');
        Route::put('/loans/{loan}', [LoanController::class, 'update'])->middleware('permission:manage_loans')->name('loans.update');

        Route::get('/settings', [SettingController::class, 'index'])->middleware('permission:manage_settings')->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->middleware('permission:manage_settings')->name('settings.update');

        Route::get('/backups', [BackupController::class, 'index'])->middleware('permission:manage_backups')->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->middleware('permission:manage_backups')->name('backups.store');
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->middleware('permission:manage_backups')->name('backups.restore');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->middleware('permission:manage_backups')->name('backups.download');

        Route::get('/restore', [RestoreController::class, 'index'])->middleware('permission:manage_users')->name('restore.index');
        Route::patch('/restore/{table}/{id}', [RestoreController::class, 'restore'])->middleware('permission:manage_users')->name('restore.restore');
        Route::delete('/restore/{table}/{id}', [RestoreController::class, 'forceDelete'])->middleware('permission:manage_users')->name('restore.force-delete');

        Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:view_reports')->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->middleware('permission:view_reports')->name('reports.export');
    });
});
