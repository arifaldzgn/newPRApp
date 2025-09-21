@extends('layouts.master')

@section('title', 'Manage Departments')
@section('description', 'Department management page')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manage Departments</h4>
                <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createAccountModal"
                    data-bs-toggle="modal">
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New Dept
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Department Management</h4>
                    <table id="example" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Dept Name</th>
                                <th>Dept Code</th>
                                <th>HOD</th>
                                <th>HOD Email</th>
                                <th class="t-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deptList as $dept)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dept->dept_name }}</td>
                                    <td>{{ $dept->dept_code }}</td>
                                    <td>{{ $dept->hod ? $dept->hod->name : 'N/A' }}</td>
                                    <td>{{ $dept->hod ? $dept->hod->email : 'N/A' }}</td>
                                    <td class="t-center">
                                        <button type="button" class="btn btn-light viewRoleBtn"
                                            data-view-id="{{ $dept->id }}">View Role</button>
                                        <button type="button" class="btn btn-primary updateBtn"
                                            data-request-id="{{ $dept->id }}">Edit</button>
                                        <button type="button" class="btn btn-danger delete-btn"
                                            data-dept-id="{{ $dept->id }}">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Department Modal -->
    <div class="modal fade" id="createAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createAccountModalLabel">Create New Department</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createAccountForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" class="form-control" name="dept_name" placeholder="Department Name"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department Code</label>
                            <input type="text" class="form-control" name="dept_code" placeholder="Department Code"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="user_hod_id">Head of Department</label>
                            <select class="form-control" name="user_hod_id" id="create_user_hod_id" required>
                                <option value="">Select HOD</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-block" id="submitRequest">Submit</button>
                    <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <input type="hidden" id="deptId" name="id">
                        <div class="form-group mb-3">
                            <label for="dept_name">Department Name</label>
                            <input type="text" class="form-control" id="dept_name" name="dept_name" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="dept_code">Department Code</label>
                            <input type="text" class="form-control" id="dept_code" name="dept_code" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="user_hod_id">Head of Department</label>
                            <select class="form-control" id="user_hod_id" name="user_hod_id" required>
                                <option value="">Select HOD</option>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this department? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Role Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Users in Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody id="deptUserTable">
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-vendors-scripts')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script>
        $(document).ready(function() {
            console.log('Document ready'); // Debug: Check if script runs

            // Initialize DataTable
            $("#example").DataTable({
                lengthChange: false,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#example_wrapper .col-md-6:eq(0)");
            $(".dataTables_length select").addClass("form-select form-select-sm");

            // Debug button clicks
            $('.updateBtn').on('click', function() {
                console.log('Update button clicked'); // Debug
                let deptIdToUpdate = $(this).data('request-id');
                console.log('Department ID:', deptIdToUpdate); // Debug

                $.ajax({
                    url: '/departments/' + deptIdToUpdate, // Corrected to match department_details
                    type: 'GET',
                    beforeSend: function() {
                        console.log('Fetching department details for ID:', deptIdToUpdate); // Debug
                    },
                    success: function(response) {
                        console.log('Response:', JSON.stringify(response, null, 2)); // Debug full response
                        if (!response.dept) {
                            console.error('No dept data in response:', response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid department data received'
                            });
                            return;
                        }
                        $('#deptId').val(response.dept.id);
                        $('#dept_name').val(response.dept.dept_name);
                        $('#dept_code').val(response.dept.dept_code);

                        // Dynamically populate user_hod_id select for edit
                        let selectHod = $('#user_hod_id'); // Targets edit modal select
                        selectHod.empty(); // Clear any existing options
                        selectHod.append('<option value="">Select HOD</option>');
                        let hodId = String(response.dept.user_hod_id); // Ensure string
                        let allHods = response.allHod || [];
                        console.log('allHod from response:', allHods); // Debug allHod array

                        // Ensure current HOD is included
                        let currentHod = response.dept.hod;
                        if (currentHod && currentHod.id) {
                            let hodExists = allHods.some(hod => String(hod.id) === String(currentHod.id));
                            if (!hodExists) {
                                allHods.unshift(currentHod);
                                console.log('Added current HOD to allHod:', currentHod); // Debug
                            }
                        } else {
                            console.warn('No current HOD data available:', response.dept.hod);
                        }

                        let hodFound = false;
                        if (allHods.length === 0) {
                            console.error('No HODs available in allHod:', allHods);
                        } else {
                            allHods.forEach(function(hod) {
                                let userId = String(hod.id);
                                let selected = userId === hodId ? 'selected' : '';
                                if (userId === hodId) hodFound = true;
                                selectHod.append(`<option value="${userId}" ${selected}>${hod.name} (${hod.email})</option>`);
                                console.log(`Added option: ${userId} - ${hod.name} (${hod.email})`, selected ? 'selected' : ''); // Debug
                            });
                        }

                        // Set default value and trigger change
                        setTimeout(() => {
                            if (hodFound) {
                                selectHod.val(hodId);
                                selectHod.trigger('change'); // Force re-render
                                console.log('Set default HOD to:', selectHod.val()); // Debug
                            } else {
                                console.warn('HOD ID', hodId, 'not found in allHod list');
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Warning',
                                    text: 'Current HOD not found in the list. Please select a new HOD.'
                                });
                            }
                            console.log('Select HTML after population:', selectHod.prop('outerHTML')); // Debug DOM
                        }, 0);

                        $('#editModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error fetching department:', xhr.responseText); // Debug
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to fetch department details'
                        });
                    }
                });
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                let deptIdToUpdate = $('#deptId').val();
                console.log('Submitting edit form for ID:', deptIdToUpdate); // Debug
                var formData = $(this).serialize();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/departments/' + deptIdToUpdate + '/update',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('Sending update request:', formData); // Debug
                    },
                    success: function(response) {
                        console.log('Update success:', response); // Debug
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
                        console.error('Error updating department:', xhr.responseText); // Debug
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to update department'
                        });
                    }
                });
            });

            $('.delete-btn').on('click', function() {
                console.log('Delete button clicked'); // Debug
                let deptIdToDelete = $(this).data('dept-id');
                console.log('Department ID to delete:', deptIdToDelete); // Debug
                $('#deleteModal').modal('show');

                $('#confirmDelete').off('click').on('click', function() {
                    console.log('Confirm delete clicked'); // Debug
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: '/departments/' + deptIdToDelete,
                        method: 'DELETE',
                        beforeSend: function() {
                            console.log('Sending delete request for ID:', deptIdToDelete); // Debug
                        },
                        success: function(response) {
                            console.log('Delete success:', response); // Debug
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
                            console.error('Error deleting department:', xhr.responseText); // Debug
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.error || 'Failed to delete department'
                            });
                        }
                    });
                });
            });

            $('#cancelDelete').on('click', function() {
                console.log('Cancel delete clicked'); // Debug
                $('#deleteModal').modal('hide');
            });

            $('.viewRoleBtn').on('click', function() {
                console.log('View button clicked'); // Debug
                var deptId = $(this).data('view-id');
                console.log('Department ID for view:', deptId); // Debug

                $.ajax({
                    url: '/departments/' + deptId + '/users',
                    type: 'GET',
                    beforeSend: function() {
                        console.log('Fetching users for department ID:', deptId); // Debug
                    },
                    success: function(data) {
                        console.log('Users response:', data); // Debug
                        let tableBody = '';
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach((user, index) => {
                                tableBody += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${user.name || 'N/A'}</td>
                                        <td>${user.role || 'N/A'}</td>
                                        <td>${user.email || 'N/A'}</td>
                                    </tr>
                                `;
                            });
                        } else if (data.message && data.message === 'No users found for this department') {
                            tableBody = `<tr><td colspan="4" class="text-center">No users found in this department.</td></tr>`;
                        } else {
                            tableBody = `<tr><td colspan="4" class="text-center">Unexpected response format.</td></tr>`;
                        }

                        $('#deptUserTable').html(tableBody);
                        $('#viewModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error fetching users:', xhr.responseText); // Debug
                        $('#deptUserTable').html(`<tr><td colspan="4" class="text-center">Failed to load department users.</td></tr>`);
                        $('#viewModal').modal('show');
                    }
                });
            });

            // Create Department
            $('#submitRequest').on('click', function(e) {
                e.preventDefault();
                console.log('Create button clicked'); // Debug
                var formData = $('#createAccountForm').serialize();
                console.log('Create form data:', formData); // Debug

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/departments',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('Sending create request:', formData); // Debug
                    },
                    success: function(response) {
                        console.log('Create success:', response); // Debug
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
                        console.error('Error creating department:', xhr.responseText); // Debug
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.error || 'Failed to add new department'
                        });
                    }
                });
            });
        });
    </script>
@endsection