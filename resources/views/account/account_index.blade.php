@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')

    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manage Users Account</h4>
                <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createAccountModal"
                    data-bs-toggle="modal">
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New User
                </button>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Users Management</h4>
                    <p class="card-description text-muted"><small> All user accounts with changeable <code> Roles
                            </code></small></p>
                    <table id="example" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Badge</th>
                                <th>Department</th>
                                <th>PIC Name</th>
                                <th>Role</th>
                                <th class="t-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // dd($data);
                            @endphp
                            @foreach ($data as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $d->name }}</td>
                                    <td>{{ $d->badge_no }}</td>
                                    <td>{{ $d->deptList->dept_name }}</td>
                                    <td>{{ App\Models\User::find($d->deptList->user_hod_id)->name }}</td>
                                    @if ($d->role === 'admin')
                                        <td>
                                            <small class="text-primary">{{ $d->role }}</small>
                                        </td>
                                    @elseif ($d->role === 'hod')
                                        <td>
                                            <small class="text-warning">{{ $d->role }}</small>
                                        </td>
                                    @elseif ($d->role === 'purchasing')
                                        <td>
                                            <small class="text-danger">{{ $d->role }}</small>
                                        </td>
                                    @else
                                        <td>
                                            <small class="text-secondary">{{ $d->role }}</small>
                                        </td>
                                    @endif
                                    {{-- <td>
                                        <button class="updateBtn btn btn-primary" data-request-id="{{ $d->id }}"
                                            onclick="console.log('{{ $d->id }}')"><i class="fa fa-edit"></i></button>
                                        @if ($d->role !== 'admin')
                                            <button class="btn btn-danger delete-btn" data-user-id="{{ $d->id }}"><i
                                                    class="fa fa-trash"></i></button>
                                        @endif
                                    </td> --}}
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light dropdown-toggle" type="button"
                                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action<i class="mdi mdi-chevron-down"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                                                data-popper-placement="bottom-start"
                                                style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(0px, 39px, 0px);">
                                                <a class="dropdown-item updateBtn" data-request-id="{{ $d->id }}"
                                                    href="#">Edit</a>
                                                <a class="dropdown-item" href="#">View</a>
                                                @if ($d->role !== 'admin')
                                                    <a class="dropdown-item delete-btn" data-user-id="{{ $d->id }}"
                                                        href="#">Delete</a>
                                                @endif
                                            </div>
                                        </div>
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
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" placeholder="Employee Name"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Badge <small class="small-text text-secondary">as
                                        Username</small></label>
                                <input type="text" class="form-control" name="badge_no" placeholder="Employee Badge"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Employee Email"
                                    required>
                            </div>
                            <div class="mb-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Department</label>
                                    <select class="form-control" name="deptList_id">
                                        @foreach ($deptList as $dl)
                                            <option value="{{ $dl->id }}">{{ $dl->dept_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Role</label>
                                    <select class="form-control" name="role">
                                        <option value="hod">HOD | PIC</option>
                                        {{-- <option value="hod2">HOD | GatePass Approval</option> --}}
                                        <option value="regular">Clerk | Regular</option>
                                        <option value="purchasing">Purchase Dept | Purchasing</option>
                                        <option value="security">Security | Security</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"> The default password for all users is
                                    <a class="text-primary">12345</a>
                                </label>
                            </div>
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
                    <h5 class="modal-title">Edit Account Details</h5>
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
                    url: '/account',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'New account successfully added'
                        }).then(function() {
                            location.reload();
                        });;;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to add new account'
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
        });
    </script>




@endsection
