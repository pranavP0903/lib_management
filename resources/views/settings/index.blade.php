@extends('layouts.app')

@section('title', 'System Settings')
@section('subtitle', 'Configure library rules and preferences')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- General Settings -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Borrowing Rules -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">Borrowing Rules</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Borrow Days (Students)</label>
                                    <div class="input-group">
                                        <input type="number" name="borrow_days_student" class="form-control" 
                                               value="{{ $settings['borrow_days_student'] ?? 7 }}">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <small class="text-muted">Number of days students can borrow books</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Borrow Days (Faculty)</label>
                                    <div class="input-group">
                                        <input type="number" name="borrow_days_faculty" class="form-control" 
                                               value="{{ $settings['borrow_days_faculty'] ?? 14 }}">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <small class="text-muted">Number of days faculty can borrow books</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Books per Student</label>
                                    <div class="input-group">
                                        <input type="number" name="max_books_student" class="form-control" 
                                               value="{{ $settings['max_books_student'] ?? 3 }}">
                                        <span class="input-group-text">books</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Books per Faculty</label>
                                    <div class="input-group">
                                        <input type="number" name="max_books_faculty" class="form-control" 
                                               value="{{ $settings['max_books_faculty'] ?? 5 }}">
                                        <span class="input-group-text">books</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fine Settings -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">Fine Settings</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fine per Day</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="fine_per_day" class="form-control" 
                                               value="{{ $settings['fine_per_day'] ?? 5 }}" step="0.5">
                                    </div>
                                    <small class="text-muted">Fine amount per overdue day</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Grace Period</label>
                                    <div class="input-group">
                                        <input type="number" name="grace_period" class="form-control" 
                                               value="{{ $settings['grace_period'] ?? 2 }}">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <small class="text-muted">Days after due date before fine applies</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Fine Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="max_fine_amount" class="form-control" 
                                               value="{{ $settings['max_fine_amount'] ?? 500 }}">
                                    </div>
                                    <small class="text-muted">Maximum fine that can be charged</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fine Waiver Limit</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" name="fine_waiver_limit" class="form-control" 
                                               value="{{ $settings['fine_waiver_limit'] ?? 50 }}">
                                    </div>
                                    <small class="text-muted">Maximum amount that can be waived</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reservation Settings -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">Reservation Settings</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Reservations per Member</label>
                                    <div class="input-group">
                                        <input type="number" name="max_reservations" class="form-control" 
                                               value="{{ $settings['max_reservations'] ?? 2 }}">
                                        <span class="input-group-text">reservations</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Reservation Hold Days</label>
                                    <div class="input-group">
                                        <input type="number" name="reservation_hold_days" class="form-control" 
                                               value="{{ $settings['reservation_hold_days'] ?? 3 }}">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <small class="text-muted">Days to hold reserved book for pickup</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">Notification Settings</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="send_due_reminders" 
                                           id="send_due_reminders" {{ $settings['send_due_reminders'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_due_reminders">
                                        Send Due Date Reminders
                                    </label>
                                    <small class="text-muted d-block">Send reminders before due date</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Reminder Days Before</label>
                                    <div class="input-group">
                                        <input type="number" name="reminder_days_before" class="form-control" 
                                               value="{{ $settings['reminder_days_before'] ?? 2 }}">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="send_overdue_alerts" 
                                           id="send_overdue_alerts" {{ $settings['send_overdue_alerts'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_overdue_alerts">
                                        Send Overdue Alerts
                                    </label>
                                    <small class="text-muted d-block">Send alerts for overdue books</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="send_reservation_notifications" 
                                           id="send_reservation_notifications" {{ $settings['send_reservation_notifications'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_reservation_notifications">
                                        Send Reservation Notifications
                                    </label>
                                    <small class="text-muted d-block">Notify when reserved book is available</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset to Defaults
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- System Information -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>System Version</th>
                        <td>v1.0.0</td>
                    </tr>
                    <tr>
                        <th>Laravel Version</th>
                        <td>{{ app()->version() }}</td>
                    </tr>
                    <tr>
                        <th>PHP Version</th>
                        <td>{{ phpversion() }}</td>
                    </tr>
                    <tr>
                        <th>Database</th>
                        <td>MySQL</td>
                    </tr>
                    <tr>
                        <th>Server Time</th>
                        <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Timezone</th>
                        <td>{{ config('app.timezone') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#backupModal">
                        <i class="bi bi-database me-1"></i> Backup Database
                    </button>
                    <button class="btn btn-outline-warning" onclick="clearCache()">
                        <i class="bi bi-trash me-1"></i> Clear Cache
                    </button>
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetModal">
                        <i class="bi bi-exclamation-triangle me-1"></i> Reset System
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Setting Categories -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Setting Categories</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="#borrowing" class="list-group-item list-group-item-action">
                        <i class="bi bi-book me-2"></i> Borrowing Rules
                    </a>
                    <a href="#fines" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-coin me-2"></i> Fine Management
                    </a>
                    <a href="#reservations" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history me-2"></i> Reservations
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action">
                        <i class="bi bi-bell me-2"></i> Notifications
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield me-2"></i> Security
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Modal -->
<div class="modal fade" id="backupModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Backup Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Create a backup of the entire database. This may take a few minutes.</p>
                <div class="mb-3">
                    <label class="form-label">Backup Type</label>
                    <select class="form-select">
                        <option value="full">Full Backup</option>
                        <option value="data_only">Data Only</option>
                        <option value="schema_only">Schema Only</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Compression</label>
                    <select class="form-select">
                        <option value="none">No Compression</option>
                        <option value="gzip">GZIP Compression</option>
                        <option value="zip">ZIP Compression</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createBackup()">
                    <i class="bi bi-database me-1"></i> Create Backup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Modal -->
<div class="modal fade" id="resetModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset System</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action will reset all system settings to defaults. This cannot be undone.
                </div>
                <p>Are you sure you want to reset all settings to their default values?</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmReset">
                    <label class="form-check-label" for="confirmReset">
                        I understand this action cannot be undone
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="resetButton" disabled onclick="resetSystem()">
                    <i class="bi bi-exclamation-triangle me-1"></i> Reset System
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Confirm reset checkbox
$('#confirmReset').change(function() {
    $('#resetButton').prop('disabled', !this.checked);
});

function createBackup() {
    if(confirm('Create database backup?')) {
        $.ajax({
            url: '{{ route("settings.backup") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data) {
                const a = document.createElement('a');
                const url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = 'library-backup-' + new Date().toISOString().split('T')[0] + '.sql';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                $('#backupModal').modal('hide');
            }
        });
    }
}

function clearCache() {
    if(confirm('Clear system cache?')) {
        $.ajax({
            url: '{{ route("settings.clear-cache") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('Cache cleared successfully');
            }
        });
    }
}

function resetSystem() {
    if(confirm('Are you absolutely sure? This will reset ALL settings to defaults.')) {
        $.ajax({
            url: '{{ route("settings.reset") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('System reset successfully');
                location.reload();
            }
        });
    }
}
</script>
@endpush