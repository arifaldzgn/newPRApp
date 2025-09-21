@extends('layouts.master')

@section('title', 'Pending PR')
@section('description', 'Pending Purchase Request page')

@section('content')
    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Create Item Request</h4>
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

                    <table id="example" class="table table-striped display compact" style="width:100%">
                        <thead>
                            <tr>
                                <th>Request Date</th>
                                <th>Ticket Code</th>
                                <th>Requestor</th>
                                <th>Status</th>
                                <th class="t-center">
                                    <center>Action</center>
                                </th>
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
                                        <center>
                                            @if ($dT->status === 'Pending' or $dT->status === 'Revised')
                                                <button type="button" class="updateBtn btn btn-primary"
                                                    data-request-id="{{ $dT->id }}"><i
                                                        class="bi bi-pencil-square"></i> Edit</button>
                                                <button class="delete-btn btn btn-danger"
                                                    data-item-id="{{ $dT->id }}"><i class="bi bi-trash"></i> Delete</button>
                                            @else
                                                -
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

    @include('parts.pr_modals')

    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    {{-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

    <script>
        $(document).ready(function() {
            $("#datatable").DataTable();
            $("#datatable-buttons").DataTable({
                lengthChange: false,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");
            $(".dataTables_length select").addClass("form-select form-select-sm");
        });

        jQuery(document).ready(function($) {
            $('#submitRequest').click(function() {
                var formData = $('#createPrForm').serialize();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/ticket',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to create request', 'error');
                    }
                });
            });

            var arrayCount = 1;
            var itemCount = 2;

            function initializeSelectpicker() {
                $('.selectpicker').selectpicker('refresh');
            }

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
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching part details:', error);
                    }
                });
            }

            $("#addItem").click(function() {
                if (arrayCount <= 5) {
                    var newItem = `
                        <div class="card card-body border border-primary">
                            <div class="mb-3">
                                <div class="form-group">
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
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="typeNumber">Amount / 1 Item (Rp) <span class="text-muted">(Optional)</span></label>
                                <input type="text" id="typeNumber" class="form-control" name="pr_request[${arrayCount}][amount]" value="0">
                                <small class="form-text text-muted">Optional. Default: 0 (will be calculated if not provided).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="typeNumber">Quantity <span class="text-danger">*</span></label>
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
                                <input type="text" class="form-control" id="requires_stock_reduction" name="pr_request[${arrayCount}][requires_stock_reduction]" value="0" readonly>
                                <small class="form-text text-muted">Read-only. Default: 0 (updated via stock check).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">UoM</label>
                                <input type="text" class="form-control" id="UoM" name="pr_request[${arrayCount}][UoM]" value="N/A" readonly>
                                <small class="form-text text-muted">Read-only. Default: N/A (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="pr_request[${arrayCount}][category]" value="N/A" readonly>
                                <small class="form-text text-muted">Read-only. Default: N/A (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description/Others</label>
                                <textarea type="text" class="form-control" id="type" name="pr_request[${arrayCount}][type]" placeholder="Type" readonly></textarea>
                                <small class="form-text text-muted">Read-only. Default: empty (updated via part details).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][remark]" value="">
                                <small class="form-text text-muted">Optional. Add any additional notes (default: empty).</small>
                                <input type="hidden" class="form-control" name="pr_request[${arrayCount}][partlist_id]" value="">
                                <input type="hidden" class="form-control" name="pr_request[${arrayCount}][other_cost]" value="0">
                                <input type="hidden" class="form-control" name="pr_request[${arrayCount}][tag]" value="0">
                            </div>
                        </div>
                        <br>
                    `;
                    $("#prRequestForm").append(newItem);
                    arrayCount++;
                    itemCount++;
                    $('#submitRequest').prop('disabled', false);
                    initializeSelectpicker();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Maximum is 5',
                        text: 'Maximum items have been reached, Failed to add new request.'
                    });
                }
            });

            $(document).on('change', '.selectpicker', function() {
                var partName = $(this).val();
                var arrayCount = $(this).attr('data-array-count');
                fillOtherFields(partName, arrayCount);
            });

            $(document).on('input', '.qty-input', function() {
                var qty = parseInt($(this).val());
                var arrayCount = $(this).data('array-count');
                var requiresStockReduction = $(`input[name="pr_request[${arrayCount}][requires_stock_reduction]"]`).val();

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
                    var stock = parseInt(requiresStockReduction);
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

            $('.updateBtn').click(function() {
                var requestId = $(this).data('request-id');
                $('#approveButton').data('request-id', requestId);
                $('#rejectButton').data('request-id', requestId);

                $.ajax({
                    url: '/ticketDetails/' + requestId,
                    method: 'GET',
                    success: function(response) {
                        $('#materialDataForm').empty();
                        var itemCount = 1;
                        var materialCount = 0;

                        if (!response.pr_requests || !Array.isArray(response.pr_requests)) {
                            console.error("Invalid response: Expected pr_requests array, got:", response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid data received from server.'
                            });
                            return;
                        }

                        $.each(response.pr_requests, function(index, pr_request) {
                            if (!pr_request || typeof pr_request !== 'object') {
                                console.warn("Skipping invalid pr_request:", pr_request);
                                return;
                            }

                            var id = pr_request.partlist_id;
                            if (!id) {
                                console.warn("Skipping because partlist_id is missing:", pr_request);
                                return;
                            }

                            $.ajax({
                                url: '/retrieve-part-name/' + id,
                                method: 'GET',
                                success: function(partData) {
                                    var partName = partData.part_name || '';
                                    if (!partName) {
                                        console.warn("Part name is empty for partlist_id:", id);
                                        return;
                                    }

                                    var availableStock = parseInt(partData.stock) || 0;
                                    var initialQuantity = parseInt(pr_request.qty) || 0;
                                    var totalStock = initialQuantity + availableStock;

                                    var cardHeader =
                                        '<div class="card-header d-flex justify-content-between align-items-center">' +
                                        '<h5 class="mb-0">Part Request No. ' + itemCount + '</h5>' +
                                        '<button type="button" class="btn-close" aria-label="Close" data-part-id="' +
                                        pr_request.id + '"></button>' +
                                        '</div>';

                                    var row =
                                        '<div class="card mb-3 border-primary">' +
                                        cardHeader +
                                        '<div class="card-body">' +
                                        '<div class="mb-3">' +
                                        '<label for="materialName" class="form-label">Requested Part Name:</label>' +
                                        '<input type="text" class="form-control mb-2" id="materialName" name="pr_request[' +
                                        materialCount + '][material_name]" value="' + partName + '" readonly>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Category</label>' +
                                        '<input type="text" class="form-control" id="requestedCategory" name="pr_request[' +
                                        materialCount + '][category]" value="' + (pr_request.category || 'N/A') + '" readonly>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Available Stock</label>' +
                                        '<input type="text" class="form-control" id="availStock' + materialCount + '" name="pr_request[' + materialCount + '][requires_stock_reduction]" value="' + (isNaN(availableStock) ? 'N/A' : availableStock) + '" disabled>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Requested Quantity</label>' +
                                        '<input type="number" class="form-control requested-quantity" id="requestedQuantity' + materialCount + '" name="pr_request[' + materialCount + '][qty]" value="' + (pr_request.qty || '1') + '" data-initial-quantity="' + (pr_request.qty || 0) + '" data-total-stock="' + totalStock + '" data-pr-id="' + pr_request.id + '" data-available-stock="' + availableStock + '" min="0">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Amount / 1 Item (Rp) <span class="text-muted">(Optional)</span></label>' +
                                        '<input type="number" class="form-control" id="requestedAmount" name="pr_request[' +
                                        materialCount + '][amount]" value="' + (pr_request.amount || '0') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Other Cost</label>' +
                                        '<input type="number" class="form-control" id="requestedOtherCost" name="pr_request[' +
                                        materialCount + '][other_cost]" value="' + (pr_request.other_cost || '0') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Vendor <span class="text-danger">*</span></label>' +
                                        '<input type="text" class="form-control" id="requestedVendor" name="pr_request[' +
                                        materialCount + '][vendor]" value="' + (pr_request.vendor || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Remark <span class="text-muted">(Optional)</span></label>' +
                                        '<input type="text" class="form-control" id="requestedRemark" name="pr_request[' +
                                        materialCount + '][remark]" value="' + (pr_request.remark || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Tag</label>' +
                                        '<input type="text" class="form-control" id="requestedTag" name="pr_request[' +
                                        materialCount + '][tag]" value="' + (pr_request.tag || '0') + '">' +
                                        '</div>' +
                                        '<input type="hidden" class="form-control" name="pr_request[' +
                                        materialCount + '][id]" value="' + (pr_request.id || '') + '">' +
                                        '<input type="hidden" class="form-control" name="pr_request[' +
                                        materialCount + '][ticket_id]" value="' + (pr_request.ticket_id || '') + '">' +
                                        '</div>' +
                                        '</div>';

                                    $('#materialDataForm').append(row);
                                    itemCount++;
                                    materialCount++;
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching part name for partlist_id:", id, "Error:", error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to fetch part name for partlist_id: ' + id
                                    });
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
                    error: function(xhr, status, error) {
                        console.error("Error fetching ticket details:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load ticket details.'
                        });
                    }
                });
            });

            $('#saveMaterialChanges').click(function() {
                var formData = $('#materialDataForm').serialize();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/updateTicket',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Success'
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update material data'
                        });
                    }
                });
            });

            $('.delete-btn').on('click', function() {
                var itemId = $(this).data('item-id');
                $('#deleteModal').modal('show');
                $('#confirmDelete').data('item-id', itemId);
            });

            $('#confirmDelete').on('click', function() {
                var itemId = $(this).data('item-id');
                $('#deleteModal').modal('hide');
                deleteItem(itemId);
            });

            $('#cancelDelete').on('click', function() {
                $('#deleteModal').modal('hide');
            });

            function deleteItem(itemId) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                $.ajax({
                    url: '/ticket/' + itemId,
                    method: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Ticket successfully deleted'
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Failed to delete ticket', 'error');
                    }
                });
            }

            $('#rejectButton').click(function() {
                var requestId = $(this).data('request-id');
                console.log(requestId);

                $('#rejectReasonModal').modal('show');
                $('#materialModal').modal('hide');

                $('#confirmRejectButton').click(function() {
                    var rejectReason = $('#rejectReasonTextarea').val();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    $.ajax({
                        url: '/ticket/' + requestId + '/reject',
                        method: 'PUT',
                        data: { reason: rejectReason },
                        success: function(response) {
                            Swal.fire("Success", response.message, "success").then(function() {
                                location.reload();
                            });
                            $('#materialModal').modal('hide');
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            Swal.fire("Error", "Failed to reject request", "error");
                        }
                    });
                });
            });

            $('#approveButton').click(function() {
                var requestId = $(this).data('request-id');
                console.log(requestId);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/ticket/' + requestId + '/approve',
                    method: 'PUT',
                    success: function(response) {
                        Swal.fire("Success", response.message, "success");
                        $('#materialModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire("Error", "Failed to approve request", "error");
                    }
                });
            });

            const switchCheckbox = $('#flexSwitchCheckDefault');
            const inputField = $('#cashAdvance');

            switchCheckbox.on('change', function() {
                inputField.prop('disabled', !this.checked);
            });

            $(document).on('click', '.btn-close[data-part-id]', function() {
                var partId = $(this).data('part-id');
                $(this).closest('.card.mb-3').remove();
                console.log('Removed part with ID:', partId);
            });

            $(document).on('input', '.requested-quantity', function() {
                var requestedQuantity = parseInt($(this).val());
                var totalStock = parseInt($(this).data('total-stock'));
                var availableStock = parseInt($(this).data('available-stock'));

                if (!isNaN(availableStock) && requestedQuantity > totalStock) {
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