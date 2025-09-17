@extends('layouts.master')

@section('title', 'Manage Roles')
@section('description', 'Role management page for user permissions')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manage Roles</h4>
                <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createRoleModal"
                    data-bs-toggle="modal">
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New Role
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach ([
            ['role' => 'admin', 'title' => 'Admin', 'color' => 'primary', 'count' => $adminCount],
            ['role' => 'hod', 'title' => 'HOD', 'color' => 'warning', 'count' => $hodCount],
            ['role' => 'regular', 'title' => 'Clerk/Regular', 'color' => 'secondary', 'count' => $regularCount],
            ['role' => 'purchasing', 'title' => 'Purchasing Dept', 'color' => 'danger', 'count' => $purchasingCount]
        ] as $role)
            <div class="col-lg-4">
                <div class="card card-flush h-md-100 border border-light p-3">
                    <!-- Card header -->
                    <div class="card-header bg-transparent border-{{ $role['color'] }}">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <h5 class="my-0 text-{{ $role['color'] }}">
                                <i class="mdi mdi-bullseye-arrow me-2"></i>{{ $role['title'] }}
                            </h5>
                            <span class="badge {{ $role['status'] ?? 'bg-success' }}">
                                {{ $role['status'] ?? 'Active' }}
                            </span>
                        </div>
                    </div>
                    <!-- Card body -->
                    <div class="card-body pt-1">
                        <div class="fw-bold text-gray-600 mb-3">Total users with this role: {{ $role['count'] }}</div>
                        <div class="d-flex flex-column text-gray-600">
                            <div class="d-flex align-items-center py-2">
                                <button class="btn btn-sm btn-{{ $role['color'] }} me-2 manageDbBtn"
                                    data-role="{{ $role['role'] }}" data-bs-toggle="modal"
                                    data-bs-target="#databaseModal">
                                    <span class="bullet bg-{{ $role['color'] }} me-2"></span>Manage Database
                                </button>
                            </div>
                            @foreach ([
                                'Read repository management',
                                'Create financial management',
                                'Write repository management',
                                'Write payroll'
                            ] as $permission)
                                <div class="d-flex align-items-center py-2">
                                    <span class="bullet bg-{{ $role['color'] }} me-3"></span>
                                    {{ $permission }}
                                </div>
                            @endforeach
                            <div class="d-flex align-items-center py-2">
                                <span class="bullet bg-{{ $role['color'] }} me-3"></span>
                                <a href="#" data-bs-toggle="tooltip" title="Additional permissions available">and more...</a>
                            </div>
                        </div>
                    </div>
                    <!-- Card footer -->
                    <div class="pt-0 p-3">
                        <button type="button" class="btn btn-light btn-active-{{ $role['color'] }} my-1 me-2 viewRoleUsersBtn"
                            data-role="{{ $role['role'] }}" data-bs-toggle="modal" data-bs-target="#roleModal">
                            View Users
                        </button>
                        <button type="button" class="btn btn-light btn-active-light-{{ $role['color'] }} my-1 editRoleBtn"
                            data-role="{{ $role['role'] }}" data-bs-toggle="modal" data-bs-target="#editRoleModal">
                            Edit Role
                        </button>
                        <button type="button" class="btn btn-light btn-active-light-{{ $role['color'] }} my-1 toggleStatusBtn"
                            data-role="{{ $role['role'] }}" data-bs-toggle="tooltip" title="Toggle role status">
                            Toggle Status
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Role Modal -->
    <div class="modal fade" id="createRoleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createRoleModalLabel">Create New Role</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createRoleForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" name="role_name" placeholder="e.g., Manager" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="perm1" name="permissions[]"
                                    value="create_database">
                                <label class="form-check-label" for="perm1">Create database management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="perm2" name="permissions[]"
                                    value="read_repository">
                                <label class="form-check-label" for="perm2">Read repository management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="perm3" name="permissions[]"
                                    value="create_financial">
                                <label class="form-check-label" for="perm3">Create financial management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="perm4" name="permissions[]"
                                    value="write_repository">
                                <label class="form-check-label" for="perm4">Write repository management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="perm5" name="permissions[]"
                                    value="write_payroll">
                                <label class="form-check-label" for="perm5">Write payroll</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-block" id="submitRole">Create Role</button>
                    <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Role Users Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Users in Role</h5>
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
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="roleUserTable">
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoleForm">
                        @csrf
                        <input type="hidden" id="roleId" name="role">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="roleName" name="role_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editPerm1" name="permissions[]"
                                    value="create_database">
                                <label class="form-check-label" for="editPerm1">Create database management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editPerm2" name="permissions[]"
                                    value="read_repository">
                                <label class="form-check-label" for="editPerm2">Read repository management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editPerm3" name="permissions[]"
                                    value="create_financial">
                                <label class="form-check-label" for="editPerm3">Create financial management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editPerm4" name="permissions[]"
                                    value="write_repository">
                                <label class="form-check-label" for="editPerm4">Write repository management</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="editPerm5" name="permissions[]"
                                    value="write_payroll">
                                <label class="form-check-label" for="editPerm5">Write payroll</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" form="editRoleForm">Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Management Modal -->
    <div class="modal fade" id="databaseModal" tabindex="-1" aria-labelledby="databaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="databaseModalLabel">Database Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Records</h6>
                        <div class="table-responsive">
                            <table class="table table-striped" id="dbTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="dbRecords">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="dbActions" class="mb-3">
                        <!-- Actions will be populated based on role -->
                    </div>
                    <form id="addRecordForm" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="recordName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" id="recordDept" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Record</button>
                        <button type="button" class="btn btn-secondary" id="cancelAdd">Cancel</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-vendors-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Mock user data and records
            const mockUsers = [
                { id: 1, name: "Admin", email: "admin@etowa.com", role: "admin", status: "Active" },
                { id: 2, name: "John Doe", email: "john@etowa.com", role: "hod", status: "Active" },
                { id: 3, name: "Jane Smith", email: "jane@etowa.com", role: "regular", status: "Active" },
                { id: 4, name: "Bob Johnson", email: "bob@etowa.com", role: "purchasing", status: "Inactive" }
            ];
            let mockRecords = [
                { id: 1, name: "Record 1", dept: "IT", timestamp: "2025-09-15 14:30" },
                { id: 2, name: "Record 2", dept: "HR", timestamp: "2025-09-16 10:15" }
            ];

            // View Role Users
            $(document).on('click', '.viewRoleUsersBtn', function() {
                let role = $(this).data('role');
                $('#roleModalLabel').text(`Users in ${role.charAt(0).toUpperCase() + role.slice(1)} Role`);
                $('#roleUserTable').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');

                setTimeout(() => {
                    let filteredUsers = mockUsers.filter(user => user.role === role);
                    let rows = '';
                    if (filteredUsers.length === 0) {
                        rows = `<tr><td colspan="5" class="text-center">No users found for role: ${role}</td></tr>`;
                    } else {
                        filteredUsers.forEach((user, index) => {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${user.name}</td>
                                    <td>${user.role}</td>
                                    <td>${user.email}</td>
                                    <td><span class="badge ${user.status === 'Active' ? 'bg-success' : 'bg-danger'}">${user.status}</span></td>
                                </tr>
                            `;
                        });
                    }
                    $('#roleUserTable').html(rows);
                }, 500);
            });

            // Edit Role
            $(document).on('click', '.editRoleBtn', function() {
                let role = $(this).data('role');
                $('#editRoleModalLabel').text(`Edit ${role.charAt(0).toUpperCase() + role.slice(1)} Role`);
                $('#roleId').val(role);
                $('#roleName').val(role.charAt(0).toUpperCase() + role.slice(1));
                $('#editStatus').val('Active');
                $('#editPerm1, #editPerm2, #editPerm3, #editPerm4, #editPerm5').prop('checked', true);
                $('#editRoleModal').modal('show');
            });

            // Create Role
            $('#submitRole').on('click', function(e) {
                e.preventDefault();
                let roleName = $('input[name="role_name"]').val();
                let permissions = $('input[name="permissions[]"]:checked').map(function() {
                    return this.value;
                }).get();
                let status = $('select[name="status"]').val();

                Swal.fire({
                    icon: 'success',
                    title: 'Role Created',
                    text: `Role ${roleName} created with ${permissions.length} permissions and status ${status}`,
                    timer: 2000
                }).then(() => {
                    $('#createRoleModal').modal('hide');
                    $('#createRoleForm')[0].reset();
                });
            });

            // Edit Role Form Submission
            $('#editRoleForm').on('submit', function(e) {
                e.preventDefault();
                let role = $('#roleId').val();
                let permissions = $('input[name="permissions[]"]:checked').map(function() {
                    return this.value;
                }).get();
                let status = $('#editStatus').val();

                Swal.fire({
                    icon: 'success',
                    title: 'Role Updated',
                    text: `Role ${role} updated with ${permissions.length} permissions and status ${status}`,
                    timer: 2000
                }).then(() => {
                    $('#editRoleModal').modal('hide');
                });
            });

            // Toggle Status
            $(document).on('click', '.toggleStatusBtn', function() {
                let role = $(this).data('role');
                let currentStatus = $(this).closest('.card').find('.badge').text();
                let newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';

                Swal.fire({
                    icon: 'success',
                    title: 'Status Toggled',
                    text: `Role ${role} is now ${newStatus}`,
                    timer: 2000
                }).then(() => {
                    $(this).closest('.card').find('.badge')
                        .text(newStatus)
                        .removeClass(newStatus === 'Active' ? 'bg-danger' : 'bg-success')
                        .addClass(newStatus === 'Active' ? 'bg-success' : 'bg-danger');
                });
            });

            // Database Management
            $(document).on('click', '.manageDbBtn', function() {
                let role = $(this).data('role');
                $('#databaseModalLabel').text(`${role.charAt(0).toUpperCase() + role.slice(1)} Database Management`);
                $('#dbRecords').html(mockRecords.map(record => `
                    <tr>
                        <td>${record.id}</td>
                        <td>${record.name}</td>
                        <td>${record.dept}</td>
                        <td>
                            ${role === 'admin' || role === 'hod' ? '<button class="btn btn-sm btn-success editRecordBtn">Edit</button>' : ''}
                            ${role === 'admin' || role === 'purchasing' ? '<button class="btn btn-sm btn-danger deleteRecordBtn" data-id="${record.id}">Delete</button>' : ''}
                        </td>
                    </tr>
                `).join(''));
                $('#dbActions').empty();

                if (role === 'admin') {
                    $('#dbActions').html(`
                        <button class="btn btn-primary mb-2 addRecordBtn">Add Record</button>
                        <button class="btn btn-info mb-2 viewAllBtn">View All Records</button>
                    `);
                } else if (role === 'hod') {
                    $('#dbActions').html(`
                        <button class="btn btn-primary mb-2 addRecordBtn">Add Record</button>
                    `);
                } else if (role === 'regular') {
                    $('#dbActions').html(`
                        <button class="btn btn-info mb-2 viewAllBtn">View All Records</button>
                    `);
                } else if (role === 'purchasing') {
                    $('#dbActions').html(`
                        <button class="btn btn-info mb-2 viewAllBtn">View All Records</button>
                    `);
                }

                $('#databaseModal').modal('show');
            });

            // Add Record
            $(document).on('click', '.addRecordBtn', function() {
                $('#addRecordForm').removeClass('d-none');
                $('#dbActions').addClass('d-none');
            });

            $('#addRecordForm').on('submit', function(e) {
                e.preventDefault();
                let name = $('#recordName').val();
                let dept = $('#recordDept').val();
                let timestamp = new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });

                mockRecords.push({ id: mockRecords.length + 1, name, dept, timestamp });
                $('#dbRecords').append(`
                    <tr>
                        <td>${mockRecords.length}</td>
                        <td>${name}</td>
                        <td>${dept}</td>
                        <td><button class="btn btn-sm btn-danger deleteRecordBtn" data-id="${mockRecords.length}">Delete</button></td>
                    </tr>
                `);
                $('#addRecordForm').addClass('d-none');
                $('#dbActions').removeClass('d-none');
                $('#addRecordForm')[0].reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Record Added',
                    text: `Record added at ${timestamp}`,
                    timer: 2000
                });
            });

            $('#cancelAdd').on('click', function() {
                $('#addRecordForm').addClass('d-none');
                $('#dbActions').removeClass('d-none');
                $('#addRecordForm')[0].reset();
            });

            // Delete Record
            $(document).on('click', '.deleteRecordBtn', function() {
                let id = $(this).data('id');
                mockRecords = mockRecords.filter(record => record.id !== id);
                $(this).closest('tr').remove();
                Swal.fire({
                    icon: 'success',
                    title: 'Record Deleted',
                    text: `Record ${id} deleted at ${new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' })}`,
                    timer: 2000
                });
            });

            // View All Records
            $(document).on('click', '.viewAllBtn', function() {
                Swal.fire({
                    icon: 'info',
                    title: 'View All Records',
                    html: mockRecords.map(record => `
                        <p>ID: ${record.id}, Name: ${record.name}, Dept: ${record.dept}, Time: ${record.timestamp}</p>
                    `).join(''),
                    timer: 5000
                });
            });
        });
    </script>
@endsection