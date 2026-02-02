@extends('layouts.app')

@section('title', 'Issue Book')
@section('subtitle', 'Issue book to library member')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Issue Book to Member</h5>
            </div>
            <div class="card-body">
                <form id="issueForm" action="{{ route('circulation.store') }}" method="POST">
                    @csrf
                    
                    <!-- Step 1: Select Member -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-2">
                                <span class="fw-bold">1</span>
                            </div>
                            <h6 class="mb-0">Select Member</h6>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Search Member *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" 
                                       id="memberSearch" 
                                       placeholder="Search by member ID, name, email or phone..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="scanMemberBtn">
                                    <i class="bi bi-upc-scan me-1"></i> Scan ID
                                </button>
                            </div>
                            <small class="text-muted">Type at least 3 characters to search</small>
                        </div>
                        
                        <!-- Member Results -->
                        <div id="memberResults" class="border rounded" style="display: none; max-height: 300px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: absolute; width: 100%; z-index: 1000; background: white;">
                            <div class="list-group list-group-flush" id="memberList"></div>
                        </div>
                        
                        <!-- Selected Member -->
                        <div id="selectedMember" class="card border-success mt-3" style="display: none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 id="memberName" class="mb-1"></h6>
                                        <small class="text-muted" id="memberDetails"></small>
                                    </div>
                                    <input type="hidden" name="member_id" id="memberId">
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="clearMember()">
                                        <i class="bi bi-x-lg"></i> Change
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Select Book -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-2">
                                <span class="fw-bold">2</span>
                            </div>
                            <h6 class="mb-0">Select Book</h6>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Search Book *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" 
                                       id="bookSearch" 
                                       placeholder="Search by title, ISBN, or author..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="scanBookBtn">
                                    <i class="bi bi-upc-scan me-1"></i> Scan ISBN
                                </button>
                            </div>
                        </div>
                        
                        <!-- Book Results -->
                        <div id="bookResults" class="border rounded" style="display: none; max-height: 300px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: absolute; width: 100%; z-index: 1000; background: white;">
                            <div class="list-group list-group-flush" id="bookList"></div>
                        </div>
                        
                        <!-- Selected Book -->
                        <div id="selectedBook" class="card border-success mt-3" style="display: none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 id="bookTitle" class="mb-1"></h6>
                                        <small class="text-muted" id="bookDetails"></small>
                                        
                                        <!-- Available Copies -->
                                        <div class="mt-3">
                                            <label class="form-label">Select Copy *</label>
                                            <select name="copy_id" id="copySelect" class="form-select" required>
                                                <option value="">Select a copy</option>
                                            </select>
                                            <small class="text-muted" id="copyInfo"></small>
                                        </div>
                                    </div>
                                    <input type="hidden" id="bookId">
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="clearBook()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3: Issue Details -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle p-2 me-2">
                                <span class="fw-bold">3</span>
                            </div>
                            <h6 class="mb-0">Issue Details</h6>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Issue Date *</label>
                                    <input type="date" name="issue_date" 
                                           class="form-control" 
                                           value="{{ date('Y-m-d') }}" 
                                           readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date *</label>
                                    <input type="date" name="due_date" 
                                           class="form-control" 
                                           id="dueDate">
                                    <small class="text-muted" id="dueDateInfo"></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" 
                                      placeholder="Any special instructions or notes..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="issueBtn" disabled style="cursor: pointer; min-width: 150px;">
                            <i class="bi bi-check-circle me-1"></i> Issue Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Member Information Panel -->
    <div class="col-lg-4">
        <div class="card shadow sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Member Information</h5>
            </div>
            <div class="card-body">
                <!-- Initial State -->
                <div id="memberInfo" class="text-center text-muted py-4">
                    <i class="bi bi-person fs-1"></i>
                    <p class="mt-2">Select a member to view details</p>
                </div>
                
                <!-- Member Details -->
                <div id="memberStats" style="display: none;">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle p-3 d-inline-block mb-2">
                            <i class="bi bi-person fs-3"></i>
                        </div>
                        <h5 id="statsMemberName"></h5>
                        <span class="badge bg-info" id="statsMemberType"></span>
                        <p class="text-muted mb-0" id="statsMemberEmail"></p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Current Borrowings:</span>
                            <span id="currentBorrowings" class="badge bg-warning">0</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div id="borrowingProgress" class="progress-bar bg-warning" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Borrow Limit:</span>
                            <span id="borrowLimit" class="badge bg-info">3</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Pending Fines:</span>
                            <span id="pendingFines" class="badge bg-danger">₹0</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Status:</span>
                            <span id="memberStatus" class="badge bg-success">Active</span>
                        </div>
                    </div>
                    
                    <!-- Warnings -->
                    <div id="overdueWarning" class="alert alert-warning alert-sm" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Member has overdue books</small>
                    </div>
                    
                    <div id="limitWarning" class="alert alert-danger alert-sm" style="display: none;">
                        <i class="bi bi-x-circle me-2"></i>
                        <small>Member has reached borrow limit</small>
                    </div>
                    
                    <div id="fineWarning" class="alert alert-danger alert-sm" style="display: none;">
                        <i class="bi bi-cash-coin me-2"></i>
                        <small>Member has pending fines</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('members.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i> Register New Member
                    </a>
                    <a href="{{ route('books.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-book-plus me-1"></i> Add New Book
                    </a>
                    <a href="{{ route('circulation.active') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-list-ul me-1"></i> View Active Borrowings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedMember = null;
let selectedBook = null;

// Init date
$(document).ready(function () {
    const today = new Date().toISOString().split('T')[0];
    $('#dueDate').attr('min', today);
    
    // Direct click handler for the issue button
    $('#issueBtn').on('click', function(e) {
        if ($(this).prop('disabled')) {
            e.preventDefault();
            alert('Please select member, book, and copy first');
            return false;
        }
    });
});

/* -------------------- MEMBER SEARCH -------------------- */
$('#memberSearch').on('input', function () {
    const query = $(this).val();
    query.length >= 3 ? searchMembers(query) : $('#memberResults').hide();
});

function esc(str) {
    return (str ?? '').toString().replace(/'/g, "\\'");
}

function searchMembers(query) {
    $.get('{{ route("members.search") }}', { q: query }, function (response) {
        const list = $('#memberList').empty();

        if (!response || response.length === 0) {
            list.html(`<div class="list-group-item text-center text-muted py-3">No members found</div>`);
            $('#memberResults').show();
            return;
        }

        response.forEach(m => {
            const statusBadge = m.status === 'ACTIVE' ? '<span class="badge bg-success ms-2">Active</span>' : '<span class="badge bg-danger ms-2">Inactive</span>';
            list.append(`
                <button type="button" class="list-group-item list-group-item-action p-3 border-bottom"
                    onclick="selectMember(
                        ${m.id},
                        '${esc(m.full_name)}',
                        '${esc(m.member_type)}',
                        '${esc(m.email)}',
                        ${m.borrow_limit ?? 0},
                        '${esc(m.status)}'
                    )">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${m.full_name}</strong> ${statusBadge}<br>
                            <small class="text-muted">📧 ${m.email ?? 'N/A'}</small><br>
                            <small class="text-muted">📱 ${m.phone ?? 'N/A'} • ID: ${m.id}</small><br>
                            <small class="text-info">Type: ${m.member_type}</small>
                        </div>
                    </div>
                </button>
            `);
        });

        $('#memberResults').show();
    });
}

function selectMember(id, name, type, email, limit, status) {
    selectedMember = { id };
    $('#memberId').val(id);
    $('#memberName').text(name);
    $('#memberDetails').text(`${email} • ${type} • ID: ${id}`);
    $('#selectedMember').show();
    $('#memberResults').hide();
    $('#memberSearch').val('');
    loadMemberStats(id);
    checkIssueButton();
}

function clearMember() {
    selectedMember = null;
    $('#memberId').val('');
    $('#selectedMember').hide();
    $('#memberStats').hide();
    $('#memberInfo').show();
    checkIssueButton();
}

/* -------------------- BOOK SEARCH -------------------- */
$('#bookSearch').on('input', function () {
    const query = $(this).val();
    query.length >= 3 ? searchBooks(query) : $('#bookResults').hide();
});

function searchBooks(query) {
    $.get('{{ route("books.search") }}', { q: query }, function (response) {
        const list = $('#bookList').empty();

        if (!response || response.length === 0) {
            list.html(`<div class="list-group-item text-center text-muted py-3">No books found</div>`);
            $('#bookResults').show();
            return;
        }

        response.forEach(b => {
            list.append(`
                <button type="button" class="list-group-item list-group-item-action p-3 border-bottom"
                    onclick="selectBook(
                        ${b.id},
                        '${esc(b.title)}',
                        '${esc(b.author)}',
                        '${esc(b.isbn)}'
                    )">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${b.title}</strong><br>
                            <small class="text-muted">✍️ ${b.author ?? 'Unknown Author'}</small><br>
                            <small class="text-muted">📖 ISBN: ${b.isbn ?? 'N/A'}</small><br>
                            <small class="text-info">Available Copies: <span class="badge bg-success">${b.available_copies ?? 0}</span></small>
                        </div>
                    </div>
                </button>
            `);
        });

        $('#bookResults').show();
    });
}

function selectBook(id, title, author, isbn) {
    selectedBook = { id };
    $('#bookId').val(id);
    $('#bookTitle').text(title);
    $('#bookDetails').text(`${author} • ISBN: ${isbn}`);
    $('#selectedBook').show();
    $('#bookResults').hide();
    $('#bookSearch').val('');
    loadAvailableCopies(id);
    checkIssueButton();
}

function clearBook() {
    selectedBook = null;
    $('#bookId').val('');
    $('#selectedBook').hide();
    $('#copySelect').empty();
    $('#copyInfo').text('');
    checkIssueButton();
}

/* -------------------- COPIES -------------------- */
function loadAvailableCopies(bookId) {
    $.get('{{ route("books.copies.available") }}', { book_id: bookId }, function (response) {
        const select = $('#copySelect').empty();

        if (!response || response.length === 0) {
            select.append('<option value="">No copies available</option>');
            checkIssueButton();
            return;
        }

        select.append('<option value="">Select copy</option>');
        response.forEach(c => {
            select.append(`<option value="${c.id}">Copy #${c.copy_number}</option>`);
        });
        checkIssueButton();
    });
}

// Enable button when copy is selected
$('#copySelect').on('change', function () {
    checkIssueButton();
});

/* -------------------- MEMBER STATS -------------------- */
function loadMemberStats(memberId) {
    $.ajax({
        url: '{{ route("members.stats") }}',
        data: { member_id: memberId },
        success: function(response) {
            $('#memberInfo').hide();
            $('#memberStats').show();
            $('#statsMemberName').text(response.full_name);
            $('#statsMemberType').text(response.member_type);
            $('#statsMemberEmail').text(response.email);
            setDueDate(response.member_type);
            checkIssueButton(); // 🔥 IMPORTANT
        },
        error: function () {
            // Fail-safe: allow issue even if stats fail
            checkIssueButton();
        }
    });
}


function setDueDate(type) {
    const d = new Date();
    d.setDate(d.getDate() + (type === 'FACULTY' ? 14 : 7));
    $('#dueDate').val(d.toISOString().split('T')[0]);
}

function checkIssueButton() {
    const hasMember = selectedMember && selectedMember.id;
    const hasBook = selectedBook && selectedBook.id;
    const hasCopy = $('#copySelect').val() && $('#copySelect').val() !== '';
    
    const isEnabled = hasMember && hasBook && hasCopy;
    
    console.log('Check button:', { hasMember, hasBook, hasCopy, isEnabled });
    $('#issueBtn').prop('disabled', !isEnabled);
}

$('#issueForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!selectedMember || !selectedBook || !$('#copySelect').val()) {
        alert('Please select member, book, and copy');
        return false;
    }
    
    // Submit the form
    console.log('Submitting form with:', {
        member_id: $('#memberId').val(),
        copy_id: $('#copySelect').val(),
        issue_date: $('input[name="issue_date"]').val(),
        due_date: $('#dueDate').val(),
        notes: $('textarea[name="notes"]').val()
    });
    
    this.submit();
});


</script>
@endpush
