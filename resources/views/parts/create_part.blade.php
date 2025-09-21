@extends('layouts.master')

@section('title', 'Parts Management')
@section('description', 'Manage Stock and Non-Stock Parts')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .card-header {
        background: linear-gradient(45deg, #2c3e50, #4a6580);
        color: white;
    }
    .summary-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        height: 100%;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .part-table th {
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
    }
    .stock-indicator {
        width: 100px;
        height: 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 5px auto;
    }
    .stock-fill {
        height: 100%;
        border-radius: 4px;
    }
    .low-stock {
        background-color: #dc3545;
    }
    .medium-stock {
        background-color: #ffc107;
    }
    .high-stock {
        background-color: #198754;
    }
    .action-btn {
        transition: all 0.2s;
    }
    .action-btn:hover {
        transform: scale(1.05);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 202, 240, 0.1);
    }
    .badge-stock {
        background-color: #198754;
    }
    .badge-nonstock {
        background-color: #6c757d;
    }
</style>
@endsection

@section('content')
<!-- begin:: Content -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18"><i class="fas fa-boxes me-2"></i>Parts Management</h4>
            <button type="button" class="btn btn-success waves-effect waves-light" data-bs-target="#createPartList" data-bs-toggle="modal">
                <i class="fas fa-plus-circle font-size-16 align-middle me-2"></i> Add New Part
            </button>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Parts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($stock) + count($nonStock) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock Parts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($stock) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Non-Stock Parts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($nonStock) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-archive fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            @php
                                $lowStockCount = 0;
                                foreach ($stock as $s) {
                                    $stockQty = $s->PartStock->where('operations', 'plus')->sum('quantity') - 
                                               $s->PartStock->where('operations', 'minus')->sum('quantity');
                                    if ($stockQty <= 5) $lowStockCount++;
                                }
                                echo $lowStockCount;
                            @endphp
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top: -20px;">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-box-open me-2"></i>Stock Parts</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-export me-2"></i>Export Data</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <p class="card-description text-muted mb-4"><small>All parts with changeable <span class="badge bg-success">Stock</span></small></p>
                <div class="table-responsive">
                    <table id="stockPartTable" class="table table-hover part-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Part Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Stock</th>
                                <th>UoM</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock as $s)
                                @php
                                    $stockQty = $s->PartStock->where('operations', 'plus')->sum('quantity') - 
                                               $s->PartStock->where('operations', 'minus')->sum('quantity');
                                    $stockPercentage = min(100, ($stockQty / 20) * 100); // Assuming 20 is max for visualization
                                    $stockClass = $stockQty <= 2 ? 'low-stock' : ($stockQty <= 5 ? 'medium-stock' : 'high-stock');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fas fa-box-open text-primary"></i>
                                            </div>
                                            <div class="fw-bold">{{ $s->part_name }}</div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $s->category }}</span></td>
                                    <td>{{ $s->type }}</td>
                                    <td>
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="fw-bold @if($stockQty <= 2) text-danger @elseif($stockQty <= 5) text-warning @else text-success @endif">
                                                {{ $stockQty }}
                                            </span>
                                            <div class="stock-indicator">
                                                <div class="stock-fill {{ $stockClass }}" style="width: {{ $stockPercentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $s->UoM }}</td>
                                    <td>
                                        @if($stockQty <= 2)
                                            <span class="badge bg-danger">Low Stock</span>
                                        @elseif($stockQty <= 5)
                                            <span class="badge bg-warning">Medium Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary action-btn updateBtn" data-request-id="{{ $s->id }}" data-bs-toggle="tooltip" title="Edit Part">
                                            <i class="far fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn delete-btn" data-part-id="{{ $s->id }}" data-bs-toggle="tooltip" title="Delete Part">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-info action-btn view-history-btn" data-part-id="{{ $s->id }}" data-bs-toggle="tooltip" title="View Stock History" disabled>
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> <!-- end col -->
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-archive me-2"></i>Non-Stock Parts</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-export me-2"></i>Export Data</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <p class="card-description text-muted mb-4"><small>All parts with unchangeable <span class="badge bg-secondary">Stock</span></small></p>
                <div class="table-responsive">
                    <table id="nonStockPartTable" class="table table-hover part-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Part Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>UoM</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nonStock as $nS)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fas fa-archive text-secondary"></i>
                                            </div>
                                            <div class="fw-bold">{{ $nS->part_name }}</div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $nS->category }}</span></td>
                                    <td>{{ $nS->type }}</td>
                                    <td>{{ $nS->UoM }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary action-btn updateBtn" data-request-id="{{ $nS->id }}" data-bs-toggle="tooltip" title="Edit Part">
                                            <i class="far fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn delete-btn" data-part-id="{{ $nS->id }}" data-bs-toggle="tooltip" title="Delete Part">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Create Start --}}
<div class="modal fade" id="createPartList" aria-hidden="true" aria-labelledby="createPartListLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createPartListLabel"><i class="fas fa-plus-circle me-2"></i>Create New Part</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createPartlistForm" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="itemTypeSelector" required>
                            <option value="" selected disabled>Select Item Type</option>
                            <option value="stock">Stock Item</option>
                            <option value="non-stock">Non-Stock Item</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="part_name" placeholder="Part/Item/Service Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="type" placeholder="Description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-group">
                            <label class="form-label">Part Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="" disabled selected>Select Category</option>
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
                        <label class="form-label">Initial Stock</label>
                        <input type="number" class="form-control" name="stocks" min="0" value="0">
                    </div>

                    <div class="mb-3" id="UoMField" style="display: none;">
                        <label class="form-label">Unit of Measure (UoM) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="UoM" placeholder="e.g., pcs, unit, user, years">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitRequest">Create Part</button>
            </div>
        </div>
    </div>
</div>
{{-- Modal Create End --}}

<!-- Delete Confirmation Modal -->
<div class="modal fade" tabindex="-1" id="deleteModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this part? This action cannot be undone.</p>
                <p class="text-muted"><small>This will permanently remove the part from the system.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Part</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="far fa-edit me-2"></i>Edit Part Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="partId">
                    <div class="form-group mb-3">
                        <label for="partName" class="form-label">Part Name</label>
                        <input type="text" class="form-control" id="partName" name="part_name">
                    </div>
                    <div class="form-group mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="stock" class="form-label">Available Stock</label>
                        <input type="number" class="form-control" id="stock" readonly>
                    </div>
                    <div class="form-group mb-3">
                        <label for="quantity" class="form-label">Stock Adjustment</label>
                        <input type="number" class="form-control" id="quantity" name="quantity">
                        <small class="form-text text-muted">Enter positive numbers to increase stock, negative to decrease</small>
                    </div>
                    <input type="hidden" name="part_id" id="part_id">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables with enhanced options
        $("#stockPartTable").DataTable({
            responsive: true,
            pageLength: 10,
            order: [[3, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search stock parts...",
            }
        });

        $("#nonStockPartTable").DataTable({
            responsive: true,
            pageLength: 10,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search non-stock parts...",
            }
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Item type selector functionality
        $('#itemTypeSelector').change(function() {
            if ($(this).val() === 'stock') {
                $('#stockField').slideDown();
                $('#UoMField').slideDown();
                $('input[name="UoM"]').prop('required', true);
            } else if ($(this).val() === 'non-stock') {
                $('#stockField').slideUp();
                $('#UoMField').slideDown();
                $('input[name="UoM"]').prop('required', true);
            }
        });

        // Create part form submission
        $('#submitRequest').click(function() {
            if ($('#createPartlistForm')[0].checkValidity()) {
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
                            text: 'Failed to add new part: ' + (xhr.responseJSON?.message || 'Unknown error')
                        });
                    }
                });
            } else {
                $('#createPartlistForm')[0].reportValidity();
            }
        });

        // Delete functionality
        var deletePartId;
        $('.delete-btn').click(function() {
            deletePartId = $(this).data('part-id');
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').click(function() {
            $('#deleteModal').modal('hide');
            
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            $.ajax({
                url: '/partlist/' + deletePartId,
                method: 'DELETE',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Part has been deleted successfully'
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete the part: ' + (xhr.responseJSON?.message || 'Unknown error')
                    });
                }
            });
        });

        // Edit part functionality
        $('.updateBtn').click(function() {
            var requestId = $(this).data('request-id');

            $.ajax({
                url: '/get-part-details/' + requestId,
                type: 'GET',
                success: function(response) {
                    $('#partId').val(response.id);
                    $('#partName').val(response.data.part_name);
                    $('#category').val(response.data.category);
                    $('#description').val(response.data.type);
                    $('#stock').val(response.stock);
                    $('#part_id').val(response.data.id);
                    $('#quantity').attr('min', -response.stock).val('');

                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load part details'
                    });
                }
            });
        });

        // Save changes in edit modal
        $('#saveChanges').click(function() {
            var formData = $('#editForm').serialize();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            $.ajax({
                url: '/update-part-details',
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        $('#editModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update part: ' + (xhr.responseJSON?.message || 'Unknown error')
                    });
                }
            });
        });

        // View history button
        $('.view-history-btn').click(function() {
            var partId = $(this).data('part-id');
            window.location.href = '/part-stock-history/' + partId;
        });
    });
</script>
@endsection