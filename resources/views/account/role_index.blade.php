@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')

    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Manage Roles</h4>
                <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createAccountModal"
                    data-bs-toggle="modal" disabled>
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New Roles
                </button>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-flush h-md-100 border border-light p-3">
                <!--begin::Card header-->
                <div class="card-header bg-transparent border-primary">
                    <div class="card-title">
                        <h5 class="my-0 text-primary">
                            <i class="mdi mdi-bullseye-arrow me-2"></i>Admin
                        </h5>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-1">
                    <!--begin::Users-->
                    <div class="fw-bold text-gray-600 mb-3">Total users with this role: {{ $adminCount }}</div>
                    <!--end::Users-->

                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create database management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Read repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create financial management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write payroll
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span><a href="#">and more...</a>
                        </div>
                    </div>
                    <!--end::Permissions-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class=" pt-0 p-3 ">
                    <button type="button" class="btn btn-light btn-active-primary my-1 me-2 viewRoleUsersBtn"
                        data-role="admin" data-bs-toggle="modal" data-bs-target="#roleModal">
                        View Role
                    </button>
                    <button type="button" class="btn btn-light btn-active-light-primary my-1" data-bs-toggle="modal"
                        data-bs-target="#editModal">Edit Role</button>
                </div>
                <!--end::Card footer-->
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-flush h-md-100 border border-light p-3">
                <!--begin::Card header-->
                <div class="card-header bg-transparent border-primary">
                    <div class="card-title">
                        <h5 class="my-0 text-warning">
                            <i class="mdi mdi-bullseye-arrow me-2"></i>HOD
                        </h5>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-1">
                    <!--begin::Users-->
                    <div class="fw-bold text-gray-600 mb-3">Total users with this role: {{ $hodCount }}</div>
                    <!--end::Users-->

                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create database management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Read repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create financial management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write payroll
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span><a href="#">and more...</a>
                        </div>
                    </div>
                    <!--end::Permissions-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class=" pt-0 p-3 ">
                    <button type="button" class="btn btn-light btn-active-primary my-1 me-2 viewRoleUsersBtn"
                        data-role="hod" data-bs-toggle="modal" data-bs-target="#roleModal">
                        View Role
                    </button>

                    <button type="button" class="btn btn-light btn-active-light-primary my-1" data-bs-toggle="modal"
                        data-bs-target="#editModal">Edit Role</button>
                </div>
                <!--end::Card footer-->
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-flush h-md-100 border border-light p-3">
                <!--begin::Card header-->
                <div class="card-header bg-transparent border-secondary">
                    <div class="card-title">
                        <h5 class="my-0 text-secondary">
                            <i class="mdi mdi-bullseye-arrow me-2"></i>Clerk/Regular
                        </h5>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-1">
                    <!--begin::Users-->
                    <div class="fw-bold text-gray-600 mb-3">Total users with this role: {{ $regularCount }}</div>
                    <!--end::Users-->

                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create database management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Read repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create financial management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write payroll
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span><a href="#">and more...</a>
                        </div>
                    </div>
                    <!--end::Permissions-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class=" pt-0 p-3 ">
                    <button type="button" class="btn btn-light btn-active-primary my-1 me-2 viewRoleUsersBtn"
                        data-role="regular" data-bs-toggle="modal" data-bs-target="#roleModal">
                        View Role
                    </button>
                    <button type="button" class="btn btn-light btn-active-light-primary my-1" data-bs-toggle="modal"
                        data-bs-target="#editModal">Edit Role</button>
                </div>
                <!--end::Card footer-->
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-flush h-md-100 border border-light p-3">
                <!--begin::Card header-->
                <div class="card-header bg-transparent border-secondary">
                    <div class="card-title">
                        <h5 class="my-0 text-danger">
                            <i class="mdi mdi-bullseye-arrow me-2"></i>Purchasing Dept
                        </h5>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-1">
                    <!--begin::Users-->
                    <div class="fw-bold text-gray-600 mb-3">Total users with this role: {{ $purchasingCount }}</div>
                    <!--end::Users-->

                    <!--begin::Permissions-->
                    <div class="d-flex flex-column text-gray-600">
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create database management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Read repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Create financial management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write repository management
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span>Write payroll
                        </div>
                        <div class="d-flex align-items-center py-2">
                            <span class="bullet bg-primary me-3"></span><a href="#">and more...</a>
                        </div>
                    </div>
                    <!--end::Permissions-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class=" pt-0 p-3 ">
                    <button type="button" class="btn btn-light btn-active-primary my-1 me-2 viewRoleUsersBtn"
                        data-role="purchasing" data-bs-toggle="modal" data-bs-target="#roleModal">
                        View Role
                    </button>
                    <button type="button" class="btn btn-light btn-active-light-primary my-1" data-bs-toggle="modal"
                        data-bs-target="#editModal">Edit Role</button>
                </div>
                <!--end::Card footer-->
            </div>
        </div>
    </div>


    {{-- Modal View --}}
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Users in Role</h4>
                        <p class="card-title-desc">List of users assigned to this role.</p>

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
                                <tbody id="roleUserTable">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- End Of View --}}






@endsection

@section('page-vendors-scripts')


    <script>
        $(document).on('click', '.viewRoleUsersBtn', function() {
            let role = $(this).data('role');
            $('#roleUserTable').html('<tr><td colspan="4">Loading...</td></tr>');

            $.ajax({
                url: `/get-role-users/${role}`,
                method: 'GET',
                success: function(data) {
                    let rows = '';
                    if (data.length === 0) {
                        rows =
                            `<tr><td colspan="4" class="text-center">No users found for role: ${role}</td></tr>`;
                    } else {
                        data.forEach((user, index) => {
                            rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${user.name}</td>
                                <td>${user.role}</td>
                                <td>${user.email}</td>
                            </tr>
                        `;
                        });
                    }
                    $('#roleUserTable').html(rows);
                },
                error: function() {
                    $('#roleUserTable').html('<tr><td colspan="4">Error loading data.</td></tr>');
                }
            });
        });
    </script>



@endsection
