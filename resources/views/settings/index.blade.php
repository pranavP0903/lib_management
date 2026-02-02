@extends('layouts.app')

@section('title', 'System Settings')
@section('subtitle', 'Configure library rules and preferences')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">General Settings</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Borrowing Rules --}}
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Borrowing Rules</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Borrow Days (Students)</label>
                                <input type="number" name="borrow_days_student"
                                       class="form-control"
                                       value="{{ $settings['borrow_days_student'] ?? 7 }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Borrow Days (Faculty)</label>
                                <input type="number" name="borrow_days_faculty"
                                       class="form-control"
                                       value="{{ $settings['borrow_days_faculty'] ?? 14 }}">
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Max Books (Student)</label>
                                <input type="number" name="max_books_student"
                                       class="form-control"
                                       value="{{ $settings['max_books_student'] ?? 3 }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Max Books (Faculty)</label>
                                <input type="number" name="max_books_faculty"
                                       class="form-control"
                                       value="{{ $settings['max_books_faculty'] ?? 5 }}">
                            </div>
                        </div>
                    </div>

                    {{-- Fine Settings --}}
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Fine Settings</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Fine per Day (₹)</label>
                                <input type="number" step="0.5"
                                       name="fine_per_day"
                                       class="form-control"
                                       value="{{ $settings['fine_per_day'] ?? 5 }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Grace Period (days)</label>
                                <input type="number"
                                       name="grace_period"
                                       class="form-control"
                                       value="{{ $settings['grace_period'] ?? 2 }}">
                            </div>
                        </div>
                    </div>

                    {{-- Notification Settings --}}
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Notification Settings</h6>

                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="send_due_reminders"
                                   {{ !empty($settings['send_due_reminders']) ? 'checked' : '' }}>
                            <label class="form-check-label">Send Due Date Reminders</label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="send_overdue_alerts"
                                   {{ !empty($settings['send_overdue_alerts']) ? 'checked' : '' }}>
                            <label class="form-check-label">Send Overdue Alerts</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="send_reservation_notifications"
                                   {{ !empty($settings['send_reservation_notifications']) ? 'checked' : '' }}>
                            <label class="form-check-label">Send Reservation Notifications</label>
                        </div>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- System Info --}}
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Laravel</th><td>{{ app()->version() }}</td></tr>
                    <tr><th>PHP</th><td>{{ phpversion() }}</td></tr>
                    <tr><th>Time</th><td>{{ now() }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function clearCache() {
    if(confirm('Clear cache?')) {
        fetch("{{ route('settings.clear-cache') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        }).then(() => alert('Cache cleared'));
    }
}
</script>
@endpush
