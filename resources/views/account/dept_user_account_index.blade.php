@extends('layouts.master')

@php
    $getDeptCode = auth()->user()->deptList ? auth()->user()->deptList->dept_code : 'N/A';
@endphp

@section('title', $getDeptCode . ' Users')
@section('description', 'List User account page for ' . $getDeptCode . ' department')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $getDeptCode }} - Users</h4>
                {{-- <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createAccountModal"
                    data-bs-toggle="modal">
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New User
                </button> --}}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Users List</h4>
                    {{-- <p class="card-description text-muted"><small>All user accounts with changeable <code>Roles</code></small></p> --}}
                    <table id="example" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Badge</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>PIC Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                {{-- <th class="t-center">Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $d->name }}</td>
                                    <td>{{ $d->badge_no }}</td>
                                    <td>{{ $d->email }}</td>
                                    <td>{{ $d->deptList ? $d->deptList->dept_name : 'N/A' }}</td>
                                    <td>{{ $d->deptList && $d->deptList->hod ? $d->deptList->hod->name : 'N/A' }}</td>
                                    <td>
                                        @if ($d->role === 'admin')
                                            <small class="text-primary">{{ $d->role }}</small>
                                        @elseif ($d->role === 'hod')
                                            <small class="text-warning">{{ $d->role }}</small>
                                        @elseif ($d->role === 'purchasing')
                                            <small class="text-danger">{{ $d->role }}</small>
                                        @else
                                            <small class="text-secondary">{{ $d->role }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $d->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $d->status ?? 'Active' }}
                                        </span>
                                    </td>
                                    {{-- <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light dropdown-toggle" type="button"
                                                id="dropdownMenuButton-{{ $d->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action <i class="mdi mdi-chevron-down"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $d->id }}">
                                                <a class="dropdown-item updateBtn" data-request-id="{{ $d->id }}"
                                                    href="javascript:void(0);">Edit</a>
                                                <a class="dropdown-item viewBtn" data-view-id="{{ $d->id }}"
                                                    href="javascript:void(0);">View</a>
                                                @if ($d->role !== 'admin')
                                                    <a class="dropdown-item delete-btn" data-user-id="{{ $d->id }}"
                                                        href="javascript:void(0);">Delete</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Account Modal -->
    <div class="modal fade" id="createAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createAccountModalLabel">Create Account</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createAccountForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Employee Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Badge <small class="text-muted">as Username</small></label>
                            <input type="text" class="form-control" name="badge_no" placeholder="Employee Badge" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Employee Email" required>
                        </div>
                        <div class="mb-3">
                            <label for="deptList_id">Department</label>
                            <select class="form-control" name="deptList_id" required>
                                <option value="">Select Department</option>
                                @foreach ($deptList as $dl)
                                    <option value="{{ $dl->id }}">{{ $dl->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="role">Role</label>
                            <select class="form-control" name="role" required>
                                <option value="hod">HOD | PIC</option>
                                <option value="regular">Clerk | Regular</option>
                                <option value="purchasing">Purchase Dept | Purchasing</option>
                                <option value="security">Security | Security</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default password: <a class="text-primary">12345</a></label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-block" id="submitRequest">Submit Request</button>
                    <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Account Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <input type="hidden" id="userId" name="id">
                        <div class="form-group mb-3" id="nameContainer"></div>
                        <div class="form-group mb-3" id="badgeNoContainer"></div>
                        <div class="form-group mb-3" id="emailContainer"></div>
                        <div class="form-group mb-3">
                            <label for="hod">HOD Email</label>
                            <input type="email" class="form-control" id="hod" name="hod" disabled>
                        </div>
                        <div class="form-group mb-3">
                            <label for="deptList_id">Department</label>
                            <select class="form-control" id="deptList_id" name="deptList_id" required>
                                <option value="">Select Department</option>
                                @foreach ($deptList as $dl)
                                    <option value="{{ $dl->id }}">{{ $dl->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="hod">HOD | PIC</option>
                                <option value="regular">Clerk | Regular</option>
                                <option value="purchasing">Purchase Dept | Purchasing</option>
                                <option value="security">Security | Security</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" form="editForm">Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <th>Name</th>
                                    <td id="viewName"></td>
                                </tr>
                                <tr>
                                    <th>Badge No</th>
                                    <td id="viewBadgeNo"></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td id="viewEmail"></td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td id="viewDept"></td>
                                </tr>
                                <tr>
                                    <th>HOD</th>
                                    <td id="viewHod"></td>
                                </tr>
                                <tr>
                                    <th>Role</th>
                                    <td id="viewRole"></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="viewStatus"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-vendors-scripts')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $("#example").DataTable({
                lengthChange: false,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#example_wrapper .col-md-6:eq(0)");
            $(".dataTables_length select").addClass("form-select form-select-sm");

            // Create Account
            $('#submitRequest').on('click', function(e) {
                e.preventDefault();
                console.log('Create button clicked');
                var formData = $('#createAccountForm').serialize();
                console.log('Create form data:', formData);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/account',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('Sending create request');
                    },
                    success: function(response) {
                        console.log('Create success:', response);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            $('#createAccountModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Create error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to add new account'
                        });
                    }
                });
            });

            // Update Account
            $('.updateBtn').on('click', function() {
                console.log('Update button clicked');
                var requestId = $(this).data('request-id');
                console.log('User ID:', requestId);

                if (!requestId) {
                    console.error('No request ID provided for element:', this);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'User ID is missing'
                    });
                    return;
                }

                // Clear previous data but preserve structure
                $('#editForm')[0].reset();

                $.ajax({
                    url: '/get-user-details/' + requestId,
                    type: 'GET',
                    beforeSend: function() {
                        console.log('Fetching user details for ID:', requestId);
                    },
                    success: function(response) {
                        console.log('Full response:', JSON.stringify(response, null, 2));
                        if (response.user && $('#editForm').length) {
                            // Set userId after reset
                            $('#userId').val(requestId).attr('name', 'id');

                            // Create and populate name input
                            $('#nameContainer').html(`
                                <label for="name">Employee Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.user.name || ''}" readonly>
                            `);
                            // Create and populate badge_no input
                            $('#badgeNoContainer').html(`
                                <label for="badge_no">Badge No</label>
                                <input type="text" class="form-control" id="badge_no" name="badge_no" value="${response.user.badge_no || ''}" readonly>
                            `);
                            // Create and populate email input
                            $('#emailContainer').html(`
                                <label for="email">Employee Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="${response.user.email || ''}" readonly>
                            `);

                            $('#hod').val(response.hod || 'N/A');
                            $('#status').val(response.user.status || 'Active');

                            // Populate department select
                            var selectDept = $('#deptList_id');
                            if (selectDept.length) {
                                selectDept.empty();
                                selectDept.append('<option value="">Select Department</option>');
                                response.dept_list.forEach(function(dept) {
                                    var selected = dept.id == response.user.dept_id ? 'selected' : '';
                                    selectDept.append(`<option value="${dept.id}" ${selected}>${dept.dept_name}</option>`);
                                });
                            } else {
                                console.error('deptList_id select not found in DOM');
                            }

                            // Populate role select
                            var selectRole = $('#role');
                            if (selectRole.length) {
                                selectRole.empty();
                                selectRole.append('<option value="">Select Role</option>');
                                var roles = ['hod', 'regular', 'purchasing', 'security', 'admin'];
                                roles.forEach(function(role) {
                                    var selected = role === response.user.role ? 'selected' : '';
                                    selectRole.append(`<option value="${role}" ${selected}>${role.charAt(0).toUpperCase() + role.slice(1)}</option>`);
                                });
                            } else {
                                console.error('role select not found in DOM');
                            }

                            // Show modal
                            $('#editModal').modal('show');
                        } else {
                            console.error('No user data or form not found in response:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load user details or form not available'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching user:', {
                            status: status,
                            error: error,
                            response: xhr.responseText
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to fetch user details'
                        });
                    }
                });
            });

            // Update Account Submission (Single Handler)
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                console.log('Edit form submitted');
                var formData = $(this).serialize();
                console.log('Edit form data before append:', formData);

                // Ensure userId is included
                var userId = $('#userId').val();
                if (userId) {
                    formData += '&id=' + encodeURIComponent(userId);
                } else {
                    console.error('userId is missing:', $('#userId').val());
                }
                console.log('Edit form data after append:', formData);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/update-user-details',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('Sending update request');
                    },
                    success: function(response) {
                        console.log('Update success:', response);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            $('#editModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Update error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to update account'
                        });
                    }
                });
            });

            // View User Details
            $('.viewBtn').on('click', function() {
                console.log('View button clicked');
                var userId = $(this).data('view-id');
                console.log('User ID for view:', userId);

                $.ajax({
                    url: '/get-user-details/' + userId,
                    type: 'GET',
                    beforeSend: function() {
                        console.log('Fetching user details for view:', userId);
                    },
                    success: function(response) {
                        console.log('View response:', response);
                        $('#viewName').text(response.user.name);
                        $('#viewBadgeNo').text(response.user.badge_no);
                        $('#viewEmail').text(response.user.email);
                        $('#viewDept').text(response.dept ? response.dept.dept_name : 'N/A');
                        $('#viewHod').text(response.hod || 'N/A');
                        $('#viewRole').text(response.user.role);
                        $('#viewStatus').text(response.user.status || 'Active');
                        $('#viewModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error fetching user for view:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to load user details'
                        });
                    }
                });
            });

            // Delete User
            let userIdToDelete;
            $('.delete-btn').on('click', function() {
                console.log('Delete button clicked');
                userIdToDelete = $(this).data('user-id');
                console.log('User ID to delete:', userIdToDelete);
                $('#deleteModal').modal('show');
            });

            $('#confirmDelete').on('click', function() {
                console.log('Confirm delete clicked');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/account/' + userIdToDelete,
                    method: 'DELETE',
                    beforeSend: function() {
                        console.log('Sending delete request for ID:', userIdToDelete);
                    },
                    success: function(response) {
                        console.log('Delete success:', response);
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            $('#deleteModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to delete user'
                        });
                    }
                });
            });

            $('#cancelDelete').on('click', function() {
                console.log('Cancel delete clicked');
                $('#deleteModal').modal('hide');
            });
        });
    </script>
@endsection