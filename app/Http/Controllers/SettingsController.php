<?php

namespace App\Http\Controllers;

use App\Models\LibrarySetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    // Display settings
    public function index()
    {
        $settings = LibrarySetting::all()->pluck('setting_value', 'setting_key')->toArray();

        return view('settings.index', compact('settings'));
    }

    // Update settings
    public function update(Request $request)
    {
        $validated = $request->validate([
            'borrow_days_student' => 'required|integer|min:1|max:30',
            'borrow_days_faculty' => 'required|integer|min:1|max:60',
            'max_books_student' => 'required|integer|min:1|max:10',
            'max_books_faculty' => 'required|integer|min:1|max:10',
            'fine_per_day' => 'required|numeric|min:0|max:100',
            'grace_period' => 'required|integer|min:0|max:7',
            'max_fine_amount' => 'required|numeric|min:0|max:1000',
            'fine_waiver_limit' => 'required|numeric|min:0|max:500',
            'max_reservations' => 'required|integer|min:1|max:10',
            'reservation_hold_days' => 'required|integer|min:1|max:14',
            'send_due_reminders' => 'nullable|boolean',
            'reminder_days_before' => 'required|integer|min:1|max:7',
            'send_overdue_alerts' => 'nullable|boolean',
            'send_reservation_notifications' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            LibrarySetting::setValue($key, $value);
        }

        AuditLog::create([
            'action_type' => 'SETTINGS_UPDATE',
            'description' => "System settings updated",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully');
    }

    // Backup database
    public function backup()
    {
        try {
            $filename = 'library-backup-' . date('Y-m-d-H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            // Ensure directory exists
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Simple backup (you might want to use a package like spatie/laravel-backup)
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                AuditLog::create([
                    'action_type' => 'DATABASE_BACKUP',
                    'description' => "Database backup created: {$filename}",
                    'performed_by' => auth()->id() ?? null,
                ]);
                return response()->download($path)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('Backup failed');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    // Clear cache
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        AuditLog::create([
            'action_type' => 'CACHE_CLEAR',
            'description' => "System cache cleared",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->back()
            ->with('success', 'Cache cleared successfully');
    }

    // Reset to defaults
    public function reset()
    {
        LibrarySetting::truncate();

        $defaults = [
            ['setting_key' => 'BORROW_DAYS_STUDENT', 'setting_value' => '7'],
            ['setting_key' => 'BORROW_DAYS_FACULTY', 'setting_value' => '14'],
            ['setting_key' => 'MAX_BOOKS_STUDENT', 'setting_value' => '3'],
            ['setting_key' => 'MAX_BOOKS_FACULTY', 'setting_value' => '5'],
            ['setting_key' => 'FINE_PER_DAY', 'setting_value' => '5'],
            ['setting_key' => 'GRACE_PERIOD', 'setting_value' => '2'],
            ['setting_key' => 'MAX_FINE_AMOUNT', 'setting_value' => '500'],
            ['setting_key' => 'FINE_WAIVER_LIMIT', 'setting_value' => '50'],
            ['setting_key' => 'MAX_RESERVATIONS', 'setting_value' => '2'],
            ['setting_key' => 'RESERVATION_HOLD_DAYS', 'setting_value' => '3'],
            ['setting_key' => 'SEND_DUE_REMINDERS', 'setting_value' => '1'],
            ['setting_key' => 'REMINDER_DAYS_BEFORE', 'setting_value' => '2'],
            ['setting_key' => 'SEND_OVERDUE_ALERTS', 'setting_value' => '1'],
            ['setting_key' => 'SEND_RESERVATION_NOTIFICATIONS', 'setting_value' => '1'],
        ];

        LibrarySetting::insert($defaults);

        AuditLog::create([
            'action_type' => 'SETTINGS_RESET',
            'description' => "System settings reset to defaults",
            'performed_by' => auth()->id() ?? null,
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Settings reset to defaults successfully');
    }
}