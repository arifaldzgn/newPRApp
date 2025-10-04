@extends('layouts.master')

@section('title', 'Pending PR')
@section('description', 'Pending Purchase Request page')

@section('content')
    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Pending Item Requests</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Your Requested Items</h4>
                        <button type="button" class="btn btn-success waves-effect btn-label waves-light d-none"
                            data-bs-toggle="modal" data-bs-target="#createPR">
                            <i class="bx bx-check-double label-icon"></i> Create
                        </button>
                    </div>

                    <table id="datatable" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>Request Date</th>
                                <th>Ticket Code</th>
                                <th>Requestor</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataT as $dT)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($dT->created_at)->format('d F Y, h:i A') }}</td>
                                    <td>{{ $dT->ticketCode }}</td>
                                    <td>{{ $dT->user->name }}</td>
                                    <td>
                                        @if ($dT->status === 'Pending')
                                            <span class="badge bg-secondary">{{ $dT->status }}</span>
                                        @elseif($dT->status === 'Revised')
                                            <span class="badge bg-warning">{{ $dT->status }}</span>
                                        @elseif($dT->status === 'Rejected')
                                            <span class="badge bg-danger">{{ $dT->status }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $dT->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dT->status === 'Pending' || $dT->status === 'Revised' || $dT->status === 'HOD_Approved')
                                            <button class="btn btn-info btn-sm updateBtn" data-request-id="{{ $dT->id }}">Edit</button>
                                            <button class="btn btn-danger btn-sm delete-btn" data-item-id="{{ $dT->id }}">Delete</button>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="createPR" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="createPRLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createPrLabel">New Purchase Requisition</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <form id="createPrForm" method="POST" action="">
                            <div class="card mb-3 card-body border border-primary">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                    <label>Enable advance cash</label>
                                </div>
                                <div class="form-group">
                                    <input type="number" id="cashAdvance" class="form-control" name="advance_cash" value="0" disabled>
                                    <small class="form-text text-muted">Optional. This will refer to the total amount of this PR (default: 0).</small>
                                </div>
                            </div>
                            <div id="prRequestForm">
                                @csrf
                                <!-- Material Request Information -->
                            </div>
                            <div class="d-grid col-6 mx-auto">
                                <button class="btn btn-primary btn-block" id="addItem" type="button">Add New Items</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" id="submitRequest" disabled>Submit Request</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this ticket?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div id="rejectReasonModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea id="rejectReasonTextarea" class="form-control" placeholder="Enter reason for rejection"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectButton">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <div id="materialModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Material Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="materialDataForm"></form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="approveButton">Approve</button>
                    <button type="button" class="btn btn-danger" id="rejectButton">Reject</button>
                    <button type="button" class="btn btn-primary" id="saveMaterialChanges">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingSpinner" style="display: none; text-align: center;">Loading...</div>
    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <!-- DataTables and dependencies -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            const dataTable = $('#datatable').DataTable({
                responsive: true,
                stateSave: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search requests..."
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 3 },
                    { responsivePriority: 3, targets: 4 }
                ]
            });

            // Initialize selectpicker
            $('.selectpicker').selectpicker();

            // Cash advance toggle
            $('#flexSwitchCheckDefault').on('change', function() {
                $('#cashAdvance').prop('disabled', !this.checked);
            });

            let arrayCount = 1;
            const maxItems = 5;

            // Add new item
            $('#addItem').click(function() {
                if (arrayCount <= maxItems) {
                    const newItem = `
                        <div class="card card-body border border-primary" data-item-id="${arrayCount}">
                            <div class="mb-3">
                                <label>Part/Service Name <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" 
                                        name="pr_request[${arrayCount}][part_name]" 
                                        data-live-search="true" 
                                        data-array-count="${arrayCount}" 
                                        title="Select Part/Service">
                                    @foreach ($dataT as $dR)
                                        <option value="{{ $dR->id }}">{{ $dR->part_name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Required. Part/Service not available? <a href="" class="text-primary">Click here</a> to add new data.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount / 1 Item (Rp) <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][amount]" value="0">
                                <small class="form-text text-muted">Optional. Default: 0 (will be calculated if not provided).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control qty-input" name="pr_request[${arrayCount}][qty]" data-array-count="${arrayCount}" value="1">
                                <small class="form-text text-muted">Required. Minimum value: 1.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][vendor]" value="">
                                <small class="form-text text-muted">Required. Specify the supplier or vendor.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stocks</label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][requires_stock_reduction]" value="0" readonly>
                                <small class="form-text text-muted">Read-only. Default: 0 (updated via stock check).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">UoM</label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][UoM]" value="N/A" readonly>
                                <small class="form-text text-muted">Read-only. Default: N/A (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][category]" value="N/A" readonly>
                                <small class="form-text text-muted">Read-only. Default: N/A (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description/Others</label>
                                <textarea class="form-control" name="pr_request[${arrayCount}][type]" readonly></textarea>
                                <small class="form-text text-muted">Read-only. Default: empty (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][remark]" value="">
                                <small class="form-text text-muted">Optional. Add any additional notes (default: empty).</small>
                                <input type="hidden" name="pr_request[${arrayCount}][partlist_id]" value="">
                                <input type="hidden" name="pr_request[${arrayCount}][other_cost]" value="0">
                                <input type="hidden" name="pr_request[${arrayCount}][tag]" value="0">
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                        </div>
                        <br>
                    `;
                    $("#prRequestForm").append(newItem);
                    $(`select[name="pr_request[${arrayCount}][part_name]"]`).selectpicker();
                    arrayCount++;
                    $('#submitRequest').prop('disabled', false);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Maximum Items Reached',
                        text: `Cannot add more than ${maxItems} items.`
                    });
                }
            });

            // Remove item
            $(document).on('click', '.remove-item', function() {
                $(this).closest('.card').remove();
                arrayCount--;
                if (arrayCount <= 1) {
                    $('#submitRequest').prop('disabled', true);
                }
            });

            // Submit request
            $('#submitRequest').click(function() {
                const $button = $(this);
                $button.prop('disabled', true).text('Submitting...');
                $('#loadingSpinner').show();

                const formData = $('#createPrForm').serializeArray();
                let prRequests = [];

                formData.forEach(function(item) {
                    if (item.name.match(/pr_request\[(\d+)\]\[(.*)\]/)) {
                        const index = parseInt(RegExp.$1);
                        const field = RegExp.$2;
                        if (!prRequests[index]) prRequests[index] = {};
                        prRequests[index][field] = item.value;
                    }
                });

                prRequests = prRequests.filter(item => item && Object.keys(item).length > 0);

                $.ajax({
                    url: '{{ route('validate.stock') }}',
                    method: 'POST',
                    data: { pr_request: prRequests },
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.valid) {
                            $.ajax({
                                url: '/ticket',
                                method: 'POST',
                                data: $('#createPrForm').serialize(),
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                success: function(response) {
                                    Swal.fire('Success', response.message, 'success').then(() => location.reload());
                                },
                                error: function(xhr) {
                                    $button.prop('disabled', false).text('Submit Request');
                                    $('#loadingSpinner').hide();
                                    Swal.fire('Error', xhr.responseJSON?.error || 'Failed to create request', 'error');
                                }
                            });
                        } else {
                            $button.prop('disabled', false).text('Submit Request');
                            $('#loadingSpinner').hide();
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function(xhr) {
                        $button.prop('disabled', false).text('Submit Request');
                        $('#loadingSpinner').hide();
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to validate stock', 'error');
                    }
                });
            });

            // Fetch part details
            function fillOtherFields(partName, arrayCount) {
                $.ajax({
                    url: '{{ route('retrieve.part.details') }}',
                    method: 'GET',
                    data: { partName: partName },
                    success: function(data) {
                        $(`input[name="pr_request[${arrayCount}][UoM]"]`).val(data.part.UoM || 'N/A');
                        $(`input[name="pr_request[${arrayCount}][requires_stock_reduction]"]`).val(data.stock || '0');
                        $(`input[name="pr_request[${arrayCount}][category]"]`).val(data.part.category || 'N/A');
                        $(`textarea[name="pr_request[${arrayCount}][type]"]`).val(data.part.type || '');
                        $(`input[name="pr_request[${arrayCount}][partlist_id]"]`).val(data.part.id || '');
                        if (data.stock !== "false" && parseInt(data.stock) <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No Stock Available',
                                text: `The part ${data.part.name} has no available stock.`
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to retrieve part details', 'error');
                    }
                });
            }

            // Quantity validation
            $(document).on('input', '.qty-input', function() {
                const qty = parseInt($(this).val());
                const arrayCount = $(this).data('array-count');
                const requiresStockReduction = $(`input[name="pr_request[${arrayCount}][requires_stock_reduction]"]`).val();

                if (requiresStockReduction !== "false" && (isNaN(qty) || qty <= 0)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Quantity must be a positive number.'
                    });
                    $(this).val('1');
                    return;
                }

                if (requiresStockReduction !== "false") {
                    const stock = parseInt(requiresStockReduction);
                    if (stock <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No Stock Available',
                            text: 'The selected part has no available stock.'
                        });
                        $(this).val('1');
                        return;
                    }
                    if (qty > stock) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Exceeds Available Stock',
                            text: `The quantity cannot exceed the available stock of ${stock}.`
                        });
                        $(this).val('1');
                    }
                }
            });

            // Part selection
            $(document).on('change', '.selectpicker', function() {
                const partName = $(this).val();
                const arrayCount = $(this).data('array-count');
                fillOtherFields(partName, arrayCount);
            });

            // View/Edit details
            $('.updateBtn').click(function() {
                const requestId = $(this).data('request-id');
                $('#approveButton').data('request-id', requestId);
                $('#rejectButton').data('request-id', requestId);

                $.ajax({
                    url: '/ticketDetails/' + requestId,
                    method: 'GET',
                    success: function(response) {
                        $('#materialDataForm').empty();
                        let itemCount = 1;
                        let materialCount = 0;

                        if (!response.pr_requests || !Array.isArray(response.pr_requests)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid data received from server.'
                            });
                            return;
                        }

                        $.each(response.pr_requests, function(index, pr_request) {
                            if (!pr_request || typeof pr_request !== 'object' || !pr_request.partlist_id) {
                                return;
                            }

                            const id = pr_request.partlist_id;
                            $.ajax({
                                url: '/retrieve-part-name/' + id,
                                method: 'GET',
                                success: function(partData) {
                                    const partName = partData.part_name || '';
                                    if (!partName) return;

                                    const availableStock = parseInt(partData.stock) || 0;
                                    const initialQuantity = parseInt(pr_request.qty) || 0;
                                    const totalStock = initialQuantity + availableStock;

                                    const cardHeader = `
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Part Request No. ${itemCount}</h5>
                                            <button type="button" class="btn-close remove-part" data-part-id="${pr_request.id}"></button>
                                        </div>`;
                                    const row = `
                                        <div class="card mb-3 border-primary">
                                            ${cardHeader}
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Requested Part Name</label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][material_name]" value="${partName}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Category</label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][category]" value="${pr_request.category || 'N/A'}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Available Stock</label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][requires_stock_reduction]" value="${isNaN(availableStock) ? 'N/A' : availableStock}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Requested Quantity</label>
                                                    <input type="number" class="form-control requested-quantity" name="pr_request[${materialCount}][qty]" value="${pr_request.qty || '1'}" data-initial-quantity="${pr_request.qty || 0}" data-total-stock="${totalStock}" data-pr-id="${pr_request.id}" data-available-stock="${availableStock}" min="0">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount / 1 Item (Rp) <span class="text-muted">(Optional)</span></label>
                                                    <input type="number" class="form-control" name="pr_request[${materialCount}][amount]" value="${pr_request.amount || '0'}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Other Cost</label>
                                                    <input type="number" class="form-control" name="pr_request[${materialCount}][other_cost]" value="${pr_request.other_cost || '0'}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][vendor]" value="${pr_request.vendor || ''}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Remark <span class="text-muted">(Optional)</span></label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][remark]" value="${pr_request.remark || ''}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tag</label>
                                                    <input type="text" class="form-control" name="pr_request[${materialCount}][tag]" value="${pr_request.tag || '0'}">
                                                </div>
                                                <input type="hidden" name="pr_request[${materialCount}][id]" value="${pr_request.id || ''}">
                                                <input type="hidden" name="pr_request[${materialCount}][ticket_id]" value="${pr_request.ticket_id || ''}">
                                            </div>
                                        </div>`;
                                    $('#materialDataForm').append(row);
                                    itemCount++;
                                    materialCount++;
                                },
                                error: function(xhr) {
                                    Swal.fire('Error', 'Failed to retrieve part name', 'error');
                                },
                                complete: function() {
                                    if (index === response.pr_requests.length - 1) {
                                        if (materialCount > 0) {
                                            $('#materialModal').modal('show');
                                        } else {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'No Data',
                                                text: 'No valid purchase requests found.'
                                            });
                                        }
                                    }
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to retrieve ticket details', 'error');
                    }
                });
            });

            // Save material changes
            $('#saveMaterialChanges').click(function() {
                const formData = $('#materialDataForm').serialize();
                $.ajax({
                    url: '/updateTicket',
                    method: 'POST',
                    data: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Material data updated successfully'
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to update material data', 'error');
                    }
                });
            });

            // Delete ticket
            $('.delete-btn').click(function() {
                const itemId = $(this).data('item-id');
                $('#deleteModal').modal('show');
                $('#confirmDelete').data('item-id', itemId);
            });

            $('#confirmDelete').click(function() {
                const itemId = $(this).data('item-id');
                $.ajax({
                    url: '/ticket/' + itemId,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Ticket successfully deleted'
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to delete ticket', 'error');
                    }
                });
                $('#deleteModal').modal('hide');
            });

            $('#cancelDelete').click(function() {
                $('#deleteModal').modal('hide');
            });

            // Approve request
            $('#approveButton').click(function() {
                const requestId = $(this).data('request-id');
                const userRole = window.userRole || '{{ auth()->user()->role ?? "guest" }}';
                let approveUrl;

                if (userRole === 'purchasing' || userRole === 'pic' || userRole === 'admin') {
                    approveUrl = '/ticket/' + requestId + '/purchasing_approve';
                } else if (userRole === 'hod' || userRole === 'admin') {
                    approveUrl = '/ticket/' + requestId + '/approve';
                } else {
                    Swal.fire('Error', 'You are not authorized to approve this ticket', 'error');
                    return;
                }

                $.ajax({
                    url: approveUrl,
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(() => location.reload());
                        $('#materialModal').modal('hide');
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'Failed to approve request';
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            });

            // Reject request
            $('#rejectButton').click(function() {
                const requestId = $(this).data('request-id');
                $('#rejectReasonModal').modal('show');
                $('#materialModal').modal('hide');
                $('#confirmRejectButton').data('request-id', requestId);
            });

            $('#confirmRejectButton').click(function() {
                const requestId = $(this).data('request-id');
                const rejectReason = $('#rejectReasonTextarea').val();
                $.ajax({
                    url: '/ticket/' + requestId + '/reject',
                    method: 'PUT',
                    data: { reason: rejectReason },
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(() => location.reload());
                        $('#rejectReasonModal').modal('hide');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to reject request', 'error');
                    }
                });
            });

            // Remove part
            $(document).on('click', '.remove-part', function() {
                $(this).closest('.card').remove();
            });

            // Quantity validation in modal
            $(document).on('input', '.requested-quantity', function() {
                const requestedQuantity = parseInt($(this).val());
                const totalStock = parseInt($(this).data('total-stock'));

                if (!isNaN(totalStock) && requestedQuantity > totalStock) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Quantity',
                        text: 'Requested quantity exceeds the available stock.'
                    });
                    $(this).val($(this).data('initial-quantity'));
                }
            });
        });
    </script>
@endsection