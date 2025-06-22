@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')
    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Items Stock Management</h4>
                <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createPartList"
                    data-bs-toggle="modal">
                    <i class="bx bx-check-double font-size-16 align-middle me-2"></i> Add New Part
                </button>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Stock Part</h4>
                    <p class="card-description text-muted"><small> All part with changeable<code> Stock </code></p></small>
                    <table id="stockPartTable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Part Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>
                                    <center>Stock</center>
                                </th>
                                <th>UoM</th>
                                <th>
                                    <center>Action</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock as $s)
                                <tr>
                                    <td>{{ $s->part_name }}</td>

                                    <td>{{ $s->category }}</td>
                                    <td>{{ $s->type }}</td>
                                    <td>
                                        <center>
                                            {{ $s->PartStock->where('operations', 'plus')->sum('quantity') - $s->PartStock->where('operations', 'minus')->sum('quantity') }}
                                        </center>
                                    </td>
                                    <td>{{ $s->UoM }}</td>
                                    <td>
                                        <center>
                                            <button class="updateBtn btn btn-primary" data-request-id="{{ $s->id }}"
                                                onclick="console.log('{{ $s->id }}')"><i
                                                    class="far fa-edit"></i></button>
                                            <button class="btn btn-danger delete-btn" data-part-id="{{ $s->id }}"
                                                onclick="console.log('{{ $s->id }}')"><i
                                                    class="fas fa-trash"></i></button>
                                            @if (auth()->user()->role !== 'admin')
                                            @endif
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- end col -->
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Non-Stock Part</h4>
                    <p class="card-description text-muted"><small> All part with un-changeable<code> Stock </code></p>
                    </small>
                    <table id="nonStockPartTable" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Part Name</th>
                                {{-- <th>Asset Code</th> --}}
                                <th>Category</th>
                                <th>Description</th>
                                <th>UoM</th>
                                <th>
                                    <center>Action</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nonStock as $nS)
                                <tr>
                                    <td>{{ $nS->part_name }}</td>
                                    <td>{{ $nS->category }}</td>
                                    <td>{{ $nS->type }}</td>
                                    <td>{{ $nS->UoM }}</td>
                                    <td>
                                        <center>
                                            <button class="updateBtn btn btn-primary" data-request-id="{{ $nS->id }}"
                                                onclick="console.log('{{ $nS->id }}')"><i
                                                    class="far fa-edit"></i></button>
                                            <button class="btn btn-danger delete-btn" data-part-id="{{ $nS->id }}"
                                                onclick="console.log('{{ $nS->id }}')"><i
                                                    class="fas fa-trash"></i></button>
                                            @if ($nS->role !== 'admin')
                                            @endif
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create Start --}}
    <div class="modal fade" id="createPartList" aria-hidden="true" aria-labelledby="createPartListLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createPartListLabel">Create New Part</h3>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="container">
                        <form id="createPartlistForm" method="POST">
                            @csrf
                            <!-- Material Request Information -->
                            <div class="mb-3">
                                <label class="form-label">Item Type</label>
                                <select class="form-control" id="itemTypeSelector">
                                    <option value="none" selected>None</option>
                                    <option value="stock">Stock</option>
                                    <option value="non-stock">Non-Stock</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="part_name"
                                    placeholder="Part/Item/Service Name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="type" placeholder="Description">
                            </div>
                            <div class="mb-3">
                                <div class="form-group mb-3">
                                    <label>Part Category</label>
                                    <select class="form-control" name="category">
                                        <option value="Asset">Asset</option>
                                        <option value="Consumable">Consumable</option>
                                        <option value="License">License</option>
                                        <option value="Service">Service</option>
                                        <option value="Software">Software</option>
                                        <option value="Spare Part">Spare Part</option>
                                        <option value="Subscription">Subscription</option>
                                        <option value="System">System</option>
                                        <option value="Stationery">Stationery</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3" id="stockField" style="display: none;">
                                <label class="form-label">Current Stocks</label>
                                <input type="number" class="form-control" name="stocks" disabled>
                            </div>

                            <div class="mb-3" id="UoM" style="display: none;">
                                <label class="form-label">UoM</label>
                                <input type="text" class="form-control" name="UoM"
                                    placeholder="example: years/unit/pcs/user" disabled>
                            </div>

                    </div>
                    </form>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-block" id="submitRequest">Submit
                            Request</button>
                        <button type="button" class="btn btn-secondary btn-block" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    {{-- Modal Create End --}}


    <div class="modal fade" tabindex="-1" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this part?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        id="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Action --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Part Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for editing/inputting -->
                    <form id="editForm">
                        <input type="hidden" id="partId">
                        <div class="form-group mb-3">
                            <label for="partName">Part Name</label>
                            <input type="text" class="form-control" id="partName" name="part_name">
                        </div>
                        <div class="form-group mb-3">
                            <label for="category">Category</label>
                            <input type="text" class="form-control" id="category" name="category" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="stock">Available Stock</label>
                            <input type="number" class="form-control" id="stock" readonly>
                        </div>
                        <div class="form-group mb-3">
                            <label for="stock">Stock</label>
                            <input type="number" class="form-control" id="quantity" name="quantity">
                            <small class="form-text text-muted">Enter positive numbers (0+) to increase stock, and vice
                                versa</small>
                        </div>
                        <input type="hidden" name="part_id" id="part_id">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <!-- Add any page-specific scripts here -->



    <script>
        $(document).ready(function() {
            // Initialize both stockPartTable and nonStockPartTable
            $("#stockPartTable, #nonStockPartTable").DataTable();

            // Initialize datatable-buttons
            $("#datatable-buttons").DataTable({
                lengthChange: false,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

            // Add Bootstrap style to all select elements in DataTables
            $(".dataTables_length select").addClass("form-select form-select-sm");
        });
    </script>

    <script>
        $('#submitRequest').click(function() {
            console.log('saveMaterial');
            var formData = $('#createPartlistForm').serialize();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $.ajax({
                url: '/partlist',
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'New Part successfully added'
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to add new part'
                    });
                }
            });
        });

        // Delete
        // Delete Confirmation Modal & Button
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('deleteModal');

            var deleteButtons = document.querySelectorAll('.delete-btn');

            var partId;

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    partId = button.getAttribute('data-part-id');
                    modal.style.display = 'block';
                });
            });

            // Delete
            var confirmDeleteBtn = document.getElementById('confirmDelete');

            confirmDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                deleteItem(partId);
            });

            // Cancel
            var cancelDeleteBtn = document.getElementById('cancelDelete');

            cancelDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            function deleteItem(partId) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: '/partlist/' + partId,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire('Success', 'Part deleted', 'success').then(function() {
                            location.reload();
                        });;;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Failed to delete the part', 'error');
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const itemTypeSelector = document.getElementById('itemTypeSelector');
            const formFields = document.querySelectorAll('#createPartlistForm input, #createPartlistForm select');
            const stockField = document.getElementById('stockField');
            const uomField = document.getElementById('UoM');
            const submitButton = document.getElementById('submitRequest');

            itemTypeSelector.addEventListener('change', function() {
                if (itemTypeSelector.value === 'stock') {
                    stockField.style.display = 'block';
                    uomField.style.display = 'none';
                    enableFormFields();
                } else if (itemTypeSelector.value === 'non-stock') {
                    stockField.style.display = 'none';
                    uomField.style.display = 'block';
                    enableFormFields();
                } else {
                    disableFormFields();
                }

                const noneOption = itemTypeSelector.querySelector('option[value="none"]');
                if (noneOption) {
                    noneOption.remove();
                }
            });

            function enableFormFields() {
                formFields.forEach(field => {
                    if (field !== itemTypeSelector) {
                        field.disabled = false;
                    }
                });
                submitButton.disabled = false;
            }

            function disableFormFields() {
                formFields.forEach(field => {
                    if (field !== itemTypeSelector) {
                        field.disabled = true;
                    }
                });
                submitButton.disabled = true;
            }

            // Initially disable all fields except the selector
            disableFormFields();
        });

        $(document).ready(function() {
            // Handle click event on the button
            $('.updateBtn').click(function() {
                var requestId = $(this).data('request-id');

                // Fetch data using AJAX
                $.ajax({
                    url: '/get-part-details/' +
                        requestId, // Assuming this route exists in your Laravel routes
                    type: 'GET',
                    success: function(response) {
                        // Populate modal form fields with fetched data
                        $('#partId').val(response.id);
                        $('#partName').val(response.data.part_name);
                        $('#category').val(response.data.category);
                        $('#description').val(response.data.type);
                        $('#stock').val(response.stock);
                        $('#part_id').val(response.data.id);
                        $('#quantity').attr('min', -response.stock);

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
                    url: '/update-part-details', // Assuming this route exists in your Laravel routes
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
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        });
                    }
                });
            });
        });
    </script>


@endsection
