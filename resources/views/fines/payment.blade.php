@extends('layouts.app')

@section('title', 'Fine Payment')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <!-- Payment Header -->
        <div class="card shadow mb-4 bg-gradient-primary text-white">
            <div class="card-body py-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-1">Fine Payment</h3>
                        <p class="mb-0 opacity-75">Complete your payment to clear the fine</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h2 class="mb-0">₹{{ number_format($fine->fine_amount, 2) }}</h2>
                        <small class="opacity-75">Amount Due</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <!-- QR Code Section -->
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Scan QR Code</h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <!-- QR Code Generator using API -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrData) }}" 
                             alt="Payment QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                        <p class="text-muted small">Scan this QR code with your phone to pay instantly</p>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            Supported by: Google Pay, PhonePe, Paytm, BHIM
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Payment Methods -->
                <div class="card shadow">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        <!-- UPI Payment Methods -->
                        <div class="payment-method mb-3">
                            <div class="d-flex align-items-center p-3 border rounded mb-3" style="cursor: pointer;" onclick="selectPaymentMethod('upi-app')">
                                <div class="me-3">
                                    <i class="bi bi-phone-fill fs-3 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Pay via UPI App</h6>
                                    <small class="text-muted">Google Pay, PhonePe, Paytm</small>
                                </div>
                                <input type="radio" name="payment_method" value="upi-app" id="upi-app" class="form-check-input">
                            </div>

                            <div class="d-flex align-items-center p-3 border rounded mb-3" style="cursor: pointer;" onclick="selectPaymentMethod('upi-id')">
                                <div class="me-3">
                                    <i class="bi bi-person-circle fs-3 text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Pay via UPI ID</h6>
                                    <small class="text-muted">Enter UPI ID (e.g., username@bank)</small>
                                </div>
                                <input type="radio" name="payment_method" value="upi-id" id="upi-id" class="form-check-input">
                            </div>

                            <div class="d-flex align-items-center p-3 border rounded mb-3" style="cursor: pointer;" onclick="selectPaymentMethod('debit-card')">
                                <div class="me-3">
                                    <i class="bi bi-credit-card fs-3 text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Debit Card</h6>
                                    <small class="text-muted">Visa, Mastercard, RuPay</small>
                                </div>
                                <input type="radio" name="payment_method" value="debit-card" id="debit-card" class="form-check-input">
                            </div>

                            <div class="d-flex align-items-center p-3 border rounded" style="cursor: pointer;" onclick="selectPaymentMethod('credit-card')">
                                <div class="me-3">
                                    <i class="bi bi-credit-card-2-front fs-3 text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Credit Card</h6>
                                    <small class="text-muted">Visa, Mastercard, American Express</small>
                                </div>
                                <input type="radio" name="payment_method" value="credit-card" id="credit-card" class="form-check-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fine Details -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Fine Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Fine ID</label>
                            <p class="h6 mb-0">{{ $fine->id }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Member Name</label>
                            <p class="h6 mb-0">{{ $fine->circulation->member->full_name }}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Book Title</label>
                            <p class="h6 mb-0">{{ $fine->circulation->copy->book->title }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Overdue Days</label>
                            <p class="h6 mb-0">{{ $fine->circulation->due_date->diffInDays(now()) }} days</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Fine Amount</label>
                            <p class="h4 mb-0 text-danger">₹{{ number_format($fine->fine_amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Status</label>
                            <p class="h6 mb-0"><span class="badge bg-warning">{{ $fine->fine_status }}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Security Info -->
        <div class="alert alert-success border-start border-success border-4">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="bi bi-shield-check fs-3 text-success"></i>
                </div>
                <div>
                    <h6 class="mb-1">Your payment is secure</h6>
                    <small class="text-muted">All transactions are encrypted and processed securely through authorized payment gateways.</small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('fines.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Fines
                    </a>
                    <form action="{{ route('fines.pay', $fine->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Are you sure you want to mark this fine as paid?')">
                            <i class="bi bi-check-circle me-2"></i> Confirm Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FAQs Section -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Payment FAQs</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How can I pay the fine?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can pay your fine using any of the following methods:
                                <ul class="mb-0 mt-2">
                                    <li>Scan the QR code with your phone using a UPI app</li>
                                    <li>Enter your UPI ID directly</li>
                                    <li>Use your Debit or Credit Card</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is there a transaction fee?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No transaction fee is charged for fine payments. You only pay the fine amount due.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Will I get a receipt?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you will receive a payment receipt via email after successful payment completion.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .payment-method input[type="radio"] {
        cursor: pointer;
        margin-top: 10px;
    }

    .payment-method > div:hover {
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    function selectPaymentMethod(method) {
        document.getElementById(method).checked = true;
    }
</script>
@endpush

@endsection
