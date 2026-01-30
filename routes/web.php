<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CirculationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Books Management
Route::resource('books', BookController::class);
Route::get('/books/search', [BookController::class, 'search'])->name('books.search');
Route::get('/books/copies', [BookController::class, 'copies'])->name('books.copies');
Route::get('/books/copies/available', [BookController::class, 'availableCopies'])->name('books.copies.available');

// Members Management
Route::resource('members', MemberController::class);
Route::get('/members/search', [MemberController::class, 'search'])->name('members.search');
Route::get('/members/stats', [MemberController::class, 'stats'])->name('members.stats');
Route::patch('/members/{id}/activate', [MemberController::class, 'activate'])->name('members.activate');
Route::patch('/members/{id}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');

// Circulation (Borrowings)
Route::prefix('circulation')->name('circulation.')->group(function () {
    Route::get('/issue', [CirculationController::class, 'create'])->name('issue');
    Route::post('/issue', [CirculationController::class, 'store'])->name('store');
    Route::get('/return', [CirculationController::class, 'returnForm'])->name('return.form');
    Route::post('/return', [CirculationController::class, 'returnBook'])->name('return.submit');
    Route::get('/active', [CirculationController::class, 'active'])->name('active');
    Route::get('/overdue', [CirculationController::class, 'overdue'])->name('overdue');
    Route::post('/renew/{id}', [CirculationController::class, 'renew'])->name('renew');
});

// Reservations
Route::resource('reservations', ReservationController::class)->except(['edit', 'update']);
Route::post('/reservations/{id}/allocate', [ReservationController::class, 'allocate'])->name('reservations.allocate');

// Fines Management
Route::resource('fines', FineController::class)->except(['create', 'store', 'edit', 'update']);
Route::post('/fines/{id}/pay', [FineController::class, 'pay'])->name('fines.pay');
Route::post('/fines/{id}/waive', [FineController::class, 'waive'])->name('fines.waive');
Route::post('/fines/apply', [FineController::class, 'apply'])->name('fines.apply');
Route::post('/fines/apply-bulk', [FineController::class, 'applyBulk'])->name('fines.apply-bulk');

// Reports
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/circulation', [ReportController::class, 'circulation'])->name('circulation');
    Route::get('/overdue', [ReportController::class, 'overdue'])->name('overdue');
    Route::get('/member-activity', [ReportController::class, 'memberActivity'])->name('member-activity');
    Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
});

// Settings
Route::resource('settings', SettingsController::class)->only(['index', 'update']);
Route::post('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
Route::post('/settings/reset', [SettingsController::class, 'reset'])->name('settings.reset');