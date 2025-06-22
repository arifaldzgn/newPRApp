@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')

    <!-- begin:: Content -->
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
                    {{-- <p class="card-description text-muted"><small> All user accounts with changeable <code> Roles --}}
                    {{-- </code></small></p> --}}
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
                            @php
                                // dd($data);
                            @endphp
                            @foreach ($deptList as $dept)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dept->dept_name }}</td>
                                    <td>{{ $dept->dept_code }}</td>
                                    <td>{{ App\Models\User::find($dept->user_hod_id)->name }}</td>
                                    <td>{{ App\Models\User::find($dept->user_hod_id)->email }}</td>
                                    <td class="t-center">
                                        <button type="button" class="btn btn-light viewRoleBtn"
                                            data-view-id="{{ $dept->id }}">View Role</button>
                                        <button type="button" class="btn btn-primary updateBtn"
                                            data-request-id="{{ $dept->id }}">Edit</button>
                                        <button type="button" class="btn btn-danger delete-btn"
                                            data-user-id="{{ $dept->id }}">Delete</button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- end col -->
    </div>

    {{-- Modal Create Account --}}
    <div class="modal fade" id="createAccountModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createAccountModalLabel">Create Account</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="container">
                        <form id="createAccountForm" method="POST">
                            @csrf
                            <!-- Material Request Information -->
                            <div class="mb-3">
                                <label class="form-label">Dept Name</label>
                                <input type="text" class="form-control" name="dept_name" placeholder="Department Name"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dept Code</label>
                                <input type="text" class="form-control" name="dept_code" placeholder="Department Code"
                                    required>
                            </div>
                            <div class="mb-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Department</label>
                                    <select class="form-control" name="user_hod_id">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="mb-3">
                                <label class="form-label"> The default password for all users is
                                    <a class="text-primary">12345</a>
                                </label>
                            </div> --}}
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" id="submitRequest">Submit Request</button>
                </div>
            </div>
        </div>
    </div>
    {{-- End Of Modal Create Account --}}

    {{-- Edit Account Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for editing/inputting -->
                    <form id="editForm">
                        <input type="hidden" id="partId">
                        <div class="form-group mb-3">
                            <label for="name">Employee Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="form-group mb-3">
                            <label for="badge_no">Badge No</label>
                            <input type="text" class="form-control" id="badge_no" name="badge_no" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Employee Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group mb-3">
                            <label for="hod">Hod Email</label>
                            <input type="hod" class="form-control" id="hod" name="hod" disabled>
                        </div>
                        <div class="form-group mb-3">
                            <label>Dept Name</label>
                            <select name="deptList_id" id="dept" class="form-control">
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="hod">HOD | PIC</option>
                                <option value="hod2">HOD | GatePass Approval</option>
                                <option value="regular">Clerk | Regular</option>
                                <option value="security">Security | Regular</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success btn-block">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- End Of Edit Modal --}}

    {{-- View Modal --}}
    <!-- View Role Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Users in Department</h4>
                        <p class="card-title-desc">List of users assigned to this department.</p>

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
    </div>



    {{-- End of View --}}






@endsection

@section('page-vendors-scripts')

    <script>
        $(document).ready(function() {
            $("#example").DataTable(), $("#datatable-buttons").DataTable({
                lengthChange: !1,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"), $(
                ".dataTables_length select").addClass("form-select form-select-sm")
        });
    </script>

    <script>
        jQuery(document).ready(function($) {

            $.noConflict();

            // Save Changes / Update Button
            $('#submitRequest').click(function() {
                console.log('saveMaterial');
                var formData = $('#createAccountForm').serialize();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/department',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'New department successfully added'
                        }).then(function() {
                            location.reload();
                        });;;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to add new department'
                        });
                    }
                });
            });

            // Delete Confirmation Modal & Button
            document.addEventListener('DOMContentLoaded', function() {
                var modal = document.getElementById('deleteModal');

                var deleteButtons = document.querySelectorAll('.delete-btn');

                var userId;

                deleteButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        userId = button.getAttribute('data-user-id');
                        modal.style.display = 'block';
                    });
                });

                // Delete
                var confirmDeleteBtn = document.getElementById('confirmDelete');

                confirmDeleteBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                    deleteItem(userId);
                });

                // Cancel
                var cancelDeleteBtn = document.getElementById('cancelDelete');

                cancelDeleteBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });

                function deleteItem(userId) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    $.ajax({
                        url: '/account/' + userId,
                        method: 'DELETE',
                        success: function(response) {
                            Swal.fire('Success', 'User deleted', 'success').then(function() {
                                location.reload();
                            });;;
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'User delete failed', 'error');
                        }
                    });
                }
            });


            // Handle click event on the button
            $('.updateBtn').click(function() {
                var requestId = $(this).data('request-id');

                // Fetch data using AJAX
                $.ajax({
                    url: '/get-user-details/' +
                        requestId, // Assuming this route exists in your Laravel routes
                    type: 'GET',
                    success: function(response) {
                        // Populate modal form fields with fetched data
                        $('#name').val(response.user.name);
                        $('#email').val(response.user.email);
                        $('#badge_no').val(response.user.badge_no);
                        $('#role').val(response.user.role);
                        $('#hod').val(response.hod);
                        var select = document.getElementById("dept");
                        response.dept_list.forEach(function(dept) {
                            var option = document.createElement("option");
                            option.text = dept.dept_name;
                            option.value = dept.id;
                            if (dept.id === response.user.deptList_id) {
                                option.selected =
                                    true; // Set this option as selected if it matches the default department ID
                            }
                            select.add(option);
                        });
                        document.addEventListener('DOMContentLoaded', function() {
                            const roleSelect = document.getElementById('role');
                            const userRole = response.user.role;
                            console.log(response.user.role);
                            // Set the value of the select element to match the user's role
                            if (roleSelect) {
                                roleSelect.value = userRole;
                            }
                        });

                        // Show the modal
                        $('#editModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        console.error(xhr.responseText);
                    }
                });
            });

            // Handle form submission
            $('#editForm').submit(function(event) {
                event.preventDefault();

                // Get form data
                var formData = $(this).serialize();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Submit form using AJAX
                $.ajax({
                    url: '/update-user-details/', // Assuming this route exists in your Laravel routes
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000, // Auto close after 2 seconds
                            showConfirmButton: false
                        }).then(function() {
                            // Optionally close modal or redirect
                            $('#editModal').modal('hide');
                        });
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error...',
                            text: 'Something went wrong!',
                        });
                    }
                });
            });

            $('.viewRoleBtn').on('click', function() {
                var deptId = $(this).data('view-id');

                $.ajax({
                    url: '/get-department-users/' + deptId,
                    type: 'GET',
                    success: function(data) {
                        let tableBody = '';
                        if (data.length > 0) {
                            data.forEach((user, index) => {
                                tableBody += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${user.name}</td>
                                <td>${user.role}</td>
                                <td>${user.email}</td>
                            </tr>
                        `;
                            });
                        } else {
                            tableBody =
                                `<tr><td colspan="4" class="text-center">No users found in this department.</td></tr>`;
                        }

                        $('#deptUserTable').html(tableBody);
                        $('#viewModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Failed to load department users.', 'error');
                    }
                });
            });




        });
    </script>




@endsection
