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
                        <div id="memberResults" class="border rounded" style="display: none; max-height: 300px; overflow-y: auto;">
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
                        <div id="bookResults" class="border rounded" style="display: none; max-height: 300px; overflow-y: auto;">
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
                        <button type="submit" class="btn btn-primary" id="issueBtn" disabled>
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

// Initialize date picker
$(document).ready(function() {
    const today = new Date().toISOString().split('T')[0];
    $('#dueDate').attr('min', today);
});

// Member Search
$('#memberSearch').on('input', function() {
    const query = $(this).val();
    if (query.length >= 3) {
        searchMembers(query);
    } else {
        $('#memberResults').hide();
    }
});

function searchMembers(query) {
    $.ajax({
        url: '{{ route("members.search") }}',
        data: { q: query },
        success: function(response) {
            const list = $('#memberList');
            list.empty();
            
            if (response.length > 0) {
                response.forEach(member => {
                    list.append(`
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="selectMember(${member.member_id}, '${member.full_name.replace(/'/g, "\\'")}', 
                                '${member.member_type}', '${member.email.replace(/'/g, "\\'")}', 
                                ${member.borrow_limit}, '${member.status}')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${member.full_name}</strong><br>
                                    <small class="text-muted">
                                        ${member.email} • ${member.member_type}<br>
                                        ID: ${member.member_id}
                                    </small>
                                </div>
                                <span class="badge bg-${member.status === 'ACTIVE' ? 'success' : 'danger'}">
                                    ${member.status}
                                </span>
                            </div>
                        </button>
                    `);
                });
                $('#memberResults').show();
            } else {
                list.append(`
                    <div class="list-group-item text-center text-muted py-3">
                        <i class="bi bi-search me-1"></i>
                        No members found
                    </div>
                `);
                $('#memberResults').show();
            }
        }
    });
}

function selectMember(id, name, type, email, limit, status) {
    selectedMember = { id, name, type, email, limit, status };
    
    $('#memberId').val(id);
    $('#memberName').text(name);
    $('#memberDetails').text(`${email} • ${type} • ID: ${id}`);
    $('#selectedMember').show();
    $('#memberResults').hide();
    $('#memberSearch').val('');
    
    // Load member stats
    loadMemberStats(id);
    checkIssueButton();
}

function clearMember() {
    selectedMember = null;
    $('#memberId').val('');
    $('#selectedMember').hide();
    $('#memberInfo').show();
    $('#memberStats').hide();
    checkIssueButton();
}

// Book Search
$('#bookSearch').on('input', function() {
    const query = $(this).val();
    if (query.length >= 3) {
        searchBooks(query);
    } else {
        $('#bookResults').hide();
    }
});

function searchBooks(query) {
    $.ajax({
        url: '{{ route("books.search") }}',
        data: { q: query },
        success: function(response) {
            const list = $('#bookList');
            list.empty();
            
            if (response.length > 0) {
                response.forEach(book => {
                    const available = book.available_copies || 0;
                    list.append(`
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="selectBook(${book.book_id}, '${book.title.replace(/'/g, "\\'")}', 
                                '${book.author.replace(/'/g, "\\'")}', '${book.isbn}', 
                                ${available}, '${book.category}')">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${book.title}</strong><br>
                                    <small class="text-muted">
                                        ${book.author} • ${book.category}<br>
                                        ISBN: ${book.isbn}
                                    </small>
                                </div>
                                <span class="badge bg-${available > 0 ? 'success' : 'danger'}">
                                    ${available} available
                                </span>
                            </div>
                        </button>
                    `);
                });
                $('#bookResults').show();
            } else {
                list.append(`
                    <div class="list-group-item text-center text-muted py-3">
                        <i class="bi bi-book me-1"></i>
                        No books found
                    </div>
                `);
                $('#bookResults').show();
            }
        }
    });
}

function selectBook(id, title, author, isbn, available, category) {
    selectedBook = { id, title, author, isbn, available, category };
    
    $('#bookId').val(id);
    $('#bookTitle').text(title);
    $('#bookDetails').text(`${author} • ${category} • ISBN: ${isbn}`);
    $('#selectedBook').show();
    $('#bookResults').hide();
    $('#bookSearch').val('');
    
    // Load available copies
    loadAvailableCopies(id);
    checkIssueButton();
}

function loadAvailableCopies(bookId) {
    $.ajax({
        url: '{{ route("books.copies.available") }}',
        data: { book_id: bookId },
        success: function(response) {
            const select = $('#copySelect');
            const copyInfo = $('#copyInfo');
            select.empty();
            
            if (response.length > 0) {
                select.append('<option value="">Select a copy</option>');
                response.forEach(copy => {
                    select.append(`<option value="${copy.copy_id}">Copy #${copy.copy_number} - ${copy.location || 'No location'}</option>`);
                });
                copyInfo.text(`${response.length} copies available`);
                copyInfo.removeClass('text-danger').addClass('text-success');
            } else {
                select.append('<option value="">No available copies</option>');
                copyInfo.text('No copies available for issue');
                copyInfo.removeClass('text-success').addClass('text-danger');
            }
        }
    });
}

function clearBook() {
    selectedBook = null;
    $('#bookId').val('');
    $('#selectedBook').hide();
    $('#copySelect').empty();
    $('#copyInfo').text('');
    checkIssueButton();
}

function loadMemberStats(memberId) {
    $.ajax({
        url: '{{ route("members.stats") }}',
        data: { member_id: memberId },
        success: function(response) {
            $('#memberInfo').hide();
            $('#memberStats').show();
            
            // Update member info
            $('#statsMemberName').text(response.full_name);
            $('#statsMemberType').text(response.member_type);
            $('#statsMemberEmail').text(response.email);
            
            // Update stats
            $('#currentBorrowings').text(response.current_borrowings);
            $('#borrowLimit').text(response.borrow_limit);
            $('#pendingFines').text('₹' + response.pending_fines);
            
            // Calculate progress
            const progress = (response.current_borrowings / response.borrow_limit) * 100;
            $('#borrowingProgress').css('width', progress + '%');
            
            // Update status
            $('#memberStatus').text(response.status)
                .removeClass('bg-success bg-danger')
                .addClass(response.status === 'ACTIVE' ? 'bg-success' : 'bg-danger');
            
            // Show/Hide warnings
            if (response.has_overdue) {
                $('#overdueWarning').show();
            } else {
                $('#overdueWarning').hide();
            }
            
            if (response.current_borrowings >= response.borrow_limit) {
                $('#limitWarning').show();
            } else {
                $('#limitWarning').hide();
            }
            
            if (response.pending_fines > 0) {
                $('#fineWarning').show();
            } else {
                $('#fineWarning').hide();
            }
            
            // Set due date based on member type
            setDueDate(response.member_type);
        }
    });
}

function setDueDate(memberType) {
    const today = new Date();
    const dueDate = new Date(today);
    
    if (memberType === 'FACULTY') {
        dueDate.setDate(today.getDate() + 14); // 14 days for faculty
        $('#dueDateInfo').text('14 days for faculty members');
    } else {
        dueDate.setDate(today.getDate() + 7); // 7 days for students
        $('#dueDateInfo').text('7 days for student members');
    }
    
    $('#dueDate').val(dueDate.toISOString().split('T')[0]);
}

function checkIssueButton() {
    const memberSelected = selectedMember !== null;
    const bookSelected = selectedBook !== null;
    const copySelected = $('#copySelect').val() !== '';
    
    $('#issueBtn').prop('disabled', !(memberSelected && bookSelected && copySelected));
}

// Form validation
$('#issueForm').on('submit', function(e) {
    if (!selectedMember || !selectedBook) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please select both member and book',
        });
    }
});
</script>
@endpush