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

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Books (AJAX routes MUST come before resource)
|--------------------------------------------------------------------------
*/
Route::get('/books/search', [BookController::class, 'search'])
    ->name('books.search');

Route::get('/books/copies/available', [BookController::class, 'availableCopies'])
    ->name('books.copies.available');

Route::get('/books/copies', [BookController::class, 'copies'])
    ->name('books.copies');

Route::post('/books/copies/bulk', [BookController::class, 'storeCopies'])
    ->name('books.copies.bulk');

Route::post('/books/copies/bulk-update', [BookController::class, 'bulkUpdateCopies'])
    ->name('books.copies.bulk-update');

// Book copies CRUD (manage copies of a book)
Route::post('/books/{book}/copies', [BookController::class, 'storeCopy'])
    ->name('books.copies.store');

Route::put('/books/copies/{copy}', [BookController::class, 'updateCopy'])
    ->name('books.copies.update');

Route::delete('/books/copies/{copy}', [BookController::class, 'destroyCopy'])
    ->name('books.copies.destroy');

Route::resource('books', BookController::class);

/*
|--------------------------------------------------------------------------
| Members (AJAX routes MUST come before resource)
|--------------------------------------------------------------------------
*/
Route::get('/members/search', [MemberController::class, 'search'])
    ->name('members.search');

Route::get('/members/stats', [MemberController::class, 'stats'])
    ->name('members.stats');

Route::patch('/members/{member}/activate', [MemberController::class, 'activate'])
    ->name('members.activate');

Route::patch('/members/{member}/deactivate', [MemberController::class, 'deactivate'])
    ->name('members.deactivate');

/* ✅ ADD THIS */
Route::post('/members/send-warning', [MemberController::class, 'sendWarning'])
    ->name('members.send-warning');

Route::resource('members', MemberController::class);


/*
|--------------------------------------------------------------------------
| Circulation
|--------------------------------------------------------------------------
*/
Route::prefix('circulation')->name('circulation.')->group(function () {

    Route::get('/issue', [CirculationController::class, 'create'])
        ->name('issue');

    Route::post('/issue', [CirculationController::class, 'store'])
        ->name('store');

    Route::get('/return', [CirculationController::class, 'returnForm'])
        ->name('return');

    Route::post('/return', [CirculationController::class, 'returnBook'])
        ->name('return.submit');

    Route::get('/active', [CirculationController::class, 'active'])
        ->name('active');

    Route::get('/overdue', [CirculationController::class, 'overdue'])
        ->name('overdue');

    Route::post('/send-overdue-alerts', [CirculationController::class, 'sendOverdueAlerts'])
    ->name('send-overdue-alerts');

    Route::post('/send-message', [CirculationController::class, 'sendMessage'])
    ->name('send-message');

    Route::post('/renew/{circulation}', [CirculationController::class, 'renew'])
        ->name('renew');
});

/*
|--------------------------------------------------------------------------
| Reservations
|--------------------------------------------------------------------------
*/
Route::resource('reservations', ReservationController::class)
    ->except(['edit', 'update']);

Route::post('/reservations/{reservation}/allocate', [ReservationController::class, 'allocate'])
    ->name('reservations.allocate');

/*
|--------------------------------------------------------------------------
| Fines
|--------------------------------------------------------------------------
*/
Route::resource('fines', FineController::class)
    ->except(['create', 'store', 'edit', 'update']);

Route::get('/fines/{fine}/payment', [FineController::class, 'paymentPage'])
    ->name('fines.payment');

Route::post('/fines/{fine}/pay', [FineController::class, 'pay'])
    ->name('fines.pay');

Route::post('/fines/{fine}/waive', [FineController::class, 'waive'])
    ->name('fines.waive');

Route::post('/fines/apply', [FineController::class, 'apply'])
    ->name('fines.apply');

Route::post('/fines/apply-bulk', [FineController::class, 'applyBulk'])
    ->name('fines.apply-bulk');

/*
|--------------------------------------------------------------------------
| Reports
|--------------------------------------------------------------------------
*/
Route::prefix('reports')->name('reports.')->group(function () {

    Route::get('/', [ReportController::class, 'index'])
        ->name('index');

    Route::get('/circulation', [ReportController::class, 'circulation'])
        ->name('circulation');

    Route::get('/overdue', [ReportController::class, 'overdue'])
        ->name('overdue');

    Route::get('/member-activity', [ReportController::class, 'memberActivity'])
        ->name('member-activity');

    Route::get('/inventory', [ReportController::class, 'inventory'])
        ->name('inventory');

    // ✅ FIXED SEND REMINDERS ROUTE
    Route::post('/send-reminders', [ReportController::class, 'sendReminders'])
        ->name('send-reminders');

    Route::post('/send-message', [ReportController::class, 'sendMessage'])
        ->name('send-message');

    
});

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
Route::get('/settings', [SettingsController::class, 'index'])
    ->name('settings.index');

Route::put('/settings', [SettingsController::class, 'update'])
    ->name('settings.update');

Route::post('/settings/backup', [SettingsController::class, 'backup'])
    ->name('settings.backup');

Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])
    ->name('settings.clear-cache');

Route::post('/settings/reset', [SettingsController::class, 'reset'])
    ->name('settings.reset');
