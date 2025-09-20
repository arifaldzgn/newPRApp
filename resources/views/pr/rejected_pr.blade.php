@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')
    <!-- start:: Content -->

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
                        <h4 class="card-title mb-0">Your Rejected Tickets</h4>
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
                                <th class="reason-column">Reason</th>
                                <th class="t-center"><center>Action</center></th>
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
                                    <span class="badge badge-pill badge-secondary">{{ $dT->status }}</span>
                                    @elseif ($dT->status === 'Rejected')
                                    <span class="badge badge-pill badge-danger">{{ $dT->status }}</span>
                                    @else
                                    <span class="badge badge-pill badge-success">{{ $dT->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $dT->reason_reject     }}</small>
                                </td>
                                <td>
                                    @if ($dT->status === 'Pending')
                                    <center><button class="updateBtn btn btn-primary" data-request-id="{{ $dT->id }}">Update</button></center>
                                    @elseif ($dT->status === 'Rejected')
                                    <center><button class="updateBtn btn btn-warning" data-request-id="{{ $dT->id }}">Update</button></center>
                                    @else
                                    <center>-</center>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div> <!-- end col -->
    </div>
    <!-- end:: Content -->

    @include('parts.pr_modals')

@endsection

@section('page-vendors-scripts')
    <script>
        $(document).ready(function() {
            $("#example").DataTable({
                lengthChange: false,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#example_wrapper .col-md-6:eq(0)");
            $(".dataTables_length select").addClass("form-select form-select-sm");
        });

        jQuery(document).ready(function($) {
            // $.noConflict();

            // Submit Request
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
                            text: xhr.responseJSON.error || 'Failed to add new part'
                        });
                    }
                });
            });

            // Add Item
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
                        $(`input[name="pr_request[${arrayCount}][UoM]"]`).val(data.UoM || '');
                        $(`input[name="pr_request[${arrayCount}][category]"]`).val(data.category || '');
                        $(`textarea[name="pr_request[${arrayCount}][type]"]`).val(data.type || '');
                        $(`input[name="pr_request[${arrayCount}][partlist_id]"]`).val(data.id || '');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch part details'
                        });
                    }
                });
            }

            $("#addItem").click(function() {
                if (arrayCount <= 5) {
                    var newItem = `
                        <div class="card card-body border border-primary">
                            <div class="mb-3">
                                <div class="form-group">
                                    <label>Part/Service Name</label>
                                    <select class="form-control selectpicker" multiple data-max-options="1" name="pr_request[${arrayCount}][part_name]" data-live-search="true" data-array-count="${arrayCount}">
                                    </select>
                                    <small id="emailHelp" class="form-text text-muted">Part/Service not available? <a href="" class="text-primary">Click here</a> to add new data.</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="typeNumber">Amount / 1 Item (Rp)</label>
                                <input type="text" id="typeNumber" class="form-control" name="pr_request[${arrayCount}][amount]" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="typeNumber">Quantity</label>
                                <input type="number" min="1" class="form-control qty-input" name="pr_request[${arrayCount}][qty]" data-array-count="${arrayCount}" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vendor</label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][vendor]">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stocks</label>
                                <input type="text" class="form-control" id="requires_stock_reduction" name="pr_request[${arrayCount}][requires_stock_reduction]" value="" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">UoM</label>
                                <input type="text" class="form-control" id="UoM" name="pr_request[${arrayCount}][UoM]" value="" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="pr_request[${arrayCount}][category]" value="" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description/Others</label>
                                <textarea type="text" class="form-control" id="type" name="pr_request[${arrayCount}][type]" placeholder="Type" readonly></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <input type="text" class="form-control" name="pr_request[${arrayCount}][remark]">
                                <input type="hidden" class="form-control" name="pr_request[${arrayCount}][partlist_id]">
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

                if (requiresStockReduction !== "false" && qty > parseInt(requiresStockReduction)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Exceeds Available Stock',
                        text: `The quantity cannot exceed the available stock of ${requiresStockReduction}.`
                    });
                    $(this).val('');
                }
            });

            // Update Button Handler
            $(document).on('click', '.updateBtn', function() {
                var requestId = $(this).data('request-id');
                $('#approveButton').data('request-id', requestId);
                $('#rejectButton').data('request-id', requestId);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/ticketDetails/' + requestId,
                    method: 'GET',
                    success: function(response) {
                        if (!response || !response.pr_requests || !Array.isArray(response.pr_requests)) {
                            console.error("Invalid response: Expected pr_requests array, got:", response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid data received from server.'
                            });
                            return;
                        }

                        $('#typeNumber').val(response.advance_cash || '');
                        $('#materialDataForm').empty();
                        var itemCount = 1;
                        var materialCount = 0;

                        $.each(response.pr_requests, function(index, pr_request) {
                            if (!pr_request || !pr_request.partlist_id) {
                                console.warn("Skipping invalid pr_request:", pr_request);
                                return;
                            }

                            $.ajax({
                                url: '/retrieve-part-name/' + pr_request.partlist_id,
                                method: 'GET',
                                success: function(data) {
                                    var partName = data.part_name || 'N/A';
                                    var availableStock = parseInt(data.stock) || 0;
                                    var initialQuantity = parseInt(pr_request.qty) || 0;
                                    var totalStock = initialQuantity + availableStock;

                                    var cardHeader =
                                        '<div class="card-header d-flex justify-content-between align-items-center">' +
                                        '<h5 class="mb-0">Part Request No. ' + itemCount + '</h5>' +
                                        '<button type="button" class="btn btn-sm close" aria-label="Close" data-delete-id="' + pr_request.id + '"' + (response.pr_requests.length === 1 ? ' disabled' : '') + '>' +
                                        '<i class="bi bi-trash"></i>' +
                                        '</button>' +
                                        '</div>';

                                    var row =
                                        '<div class="card mb-3 border-primary">' +
                                        cardHeader +
                                        '<div class="card-body">' +
                                        '<div class="mb-3">' +
                                        '<label for="materialName" class="form-label">Requested Part Name:</label>' +
                                        '<input type="text" class="form-control mb-2" id="materialName" name="pr_request[' + materialCount + '][material_name]" value="' + partName + '" readonly>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Category</label>' +
                                        '<input type="text" class="form-control" id="requestedCategory" name="pr_request[' + materialCount + '][category]" value="' + (pr_request.category || '') + '" readonly>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Available Stock</label>' +
                                        '<input type="text" class="form-control" id="availStock' + materialCount + '" name="pr_request[' + materialCount + '][requires_stock_reduction]" value="' + (isNaN(availableStock) ? 'N/A' : availableStock) + '" disabled>' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Requested Quantity</label>' +
                                        '<input type="number" class="form-control requested-quantity" id="requestedQuantity' + materialCount + '" name="pr_request[' + materialCount + '][qty]" value="' + (pr_request.qty || '') + '" data-initial-quantity="' + (pr_request.qty || 0) + '" data-total-stock="' + totalStock + '" data-pr-id="' + pr_request.id + '" data-available-stock="' + availableStock + '" min="0">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Amount (Rp.)</label>' +
                                        '<input type="number" class="form-control" id="requestedAmount" name="pr_request[' + materialCount + '][amount]" value="' + (pr_request.amount || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Other Cost</label>' +
                                        '<input type="number" class="form-control" id="requestedOtherCost" name="pr_request[' + materialCount + '][other_cost]" value="' + (pr_request.other_cost || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Vendor</label>' +
                                        '<input type="text" class="form-control" id="requestedVendor" name="pr_request[' + materialCount + '][vendor]" value="' + (pr_request.vendor || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Remark</label>' +
                                        '<input type="text" class="form-control" id="requestedRemark" name="pr_request[' + materialCount + '][remark]" value="' + (pr_request.remark || '') + '">' +
                                        '</div>' +
                                        '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Tag</label>' +
                                        '<input type="text" class="form-control" id="requestedTag" name="pr_request[' + materialCount + '][tag]" value="' + (pr_request.tag || '') + '">' +
                                        '</div>' +
                                        '<input type="hidden" class="form-control" name="pr_request[' + materialCount + '][id]" value="' + (pr_request.id || '') + '">' +
                                        '<input type="hidden" class="form-control" name="pr_request[' + materialCount + '][ticket_id]" value="' + (pr_request.ticket_id || '') + '">' +
                                        '<input type="hidden" class="form-control" name="pr_request[' + materialCount + '][partlist_id]" value="' + (pr_request.partlist_id || '') + '">' +
                                        '</div>' +
                                        '</div>';

                                    $('#materialDataForm').append(row);
                                    itemCount++;
                                    materialCount++;
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching part name for partlist_id:", pr_request.partlist_id, "Error:", error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to fetch part name for partlist_id: ' + pr_request.partlist_id
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
                            text: xhr.responseJSON.error || 'Failed to load ticket details.'
                        });
                    }
                });
            });

            // Save Material Changes
            $('#saveMaterialChanges').click(function() {
                $(this).prop('disabled', true);

                var formData = $('#materialDataForm').serialize();
                var advanceCash = $('#typeNumber').val();
                formData += '&advance_cash=' + encodeURIComponent(advanceCash);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/updateTicketR',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON.error || 'Failed to update material data';
                        if (xhr.status === 422) {
                            errorMessage = xhr.responseJSON.error || 'Invalid input data provided';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        $('#saveMaterialChanges').prop('disabled', false);
                    }
                });
            });

            // Delete Confirmation Modal & Button
            $(document).on('click', '.delete-btn', function() {
                var itemId = $(this).data('item-id');
                $('#deleteModal').modal('show');
                $('#confirmDelete').data('item-id', itemId);
            });

            $('#confirmDelete').on('click', function() {
                var itemId = $(this).data('item-id');
                $('#deleteModal').modal('hide');
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
                            text: response.message
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to delete ticket'
                        });
                    }
                });
            });

            $('#cancelDelete').on('click', function() {
                $('#deleteModal').modal('hide');
            });

            // Reject Button Handler
            $(document).on('click', '#rejectButton', function() {
                var requestId = $(this).data('request-id');
                $('#rejectReasonModal').modal('show');
                $('#materialModal').modal('hide');
                $('#confirmRejectButton').data('request-id', requestId);
            });

            $('#confirmRejectButton').on('click', function() {
                var requestId = $(this).data('request-id');
                var rejectReason = $('#rejectReasonTextarea').val();
                if (!rejectReason.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please provide a reason for rejection.'
                    });
                    return;
                }

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            location.reload();
                        });
                        $('#rejectReasonModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to reject request'
                        });
                    }
                });
            });

            $('#rejectReasonModal').on('hidden.bs.modal', function() {
                $('#rejectReasonTextarea').val('');
            });

            // Approve Button Handler
            $(document).on('click', '#approveButton', function() {
                var requestId = $(this).data('request-id');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/ticket/' + requestId + '/approve',
                    method: 'PUT',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        }).then(function() {
                            location.reload();
                        });
                        $('#materialModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to approve request'
                        });
                    }
                });
            });

            // Material Modal Card Close Button
            $(document).on('click', '#materialDataForm .close', function() {
                if ($(this).is(':disabled')) {
                    return;
                }

                var partId = $(this).data('delete-id');
                var cardToRemove = $(this).closest('.card');

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                $.ajax({
                    url: '/delete-part/' + partId,
                    method: 'DELETE',
                    success: function(response) {
                        cardToRemove.remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Part deleted successfully'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Failed to delete part'
                        });
                    }
                });
            });

            // Quantity Validation
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

            // Advance Cash Checkbox
            $(document).ready(function() {
                const switchCheckbox = $('#flexSwitchCheckDefault');
                const inputField = $('#cashAdvance');

                switchCheckbox.on('change', function() {
                    inputField.prop('disabled', !this.checked);
                });
            });
        });
    </script>
@endsection