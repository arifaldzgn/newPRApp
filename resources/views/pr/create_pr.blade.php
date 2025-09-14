@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

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
                        <button type="button" class="btn btn-success waves-effect btn-label waves-light"
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2023-10-01</td>
                                <td>PR-001</td>
                                <td>John Doe</td>
                                <td><span class="badge bg-success">Approved</span></td>
                            </tr>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- end col -->
    </div>

    {{-- Modal Start --}}
    <div class="modal fade" id="createPR" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog"
        aria-labelledby="createPRLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="createPrLabel">New Purchase Requisition</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="container">
                        <form id="createPrForm" method="POST" action="">
                            <div class="card mb-3 card-body border border-primary">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="flexSwitchCheckDefault">
                                    <label>Enable advance cash</label>
                                </div>
                                <div class="form-group">
                                    <input type="number" id="cashAdvance" class="form-control" name="advance_cash"
                                        disabled>
                                    <small class="form-text text-muted">This will refer to the total amount of this
                                        PR</small>
                                </div>
                            </div>
                            <div id="prRequestForm">
                                @csrf
                                <!-- Material Request Information -->
                            </div>
                    </div>
                    <div class="d-grid col-6 mx-auto">
                        <button class="btn btn-primary btn-block" id="addItem" type="button">Add New Items</button>
                    </div>
                </div>
                {{-- <button type="submit" class="btn btn-primary btn-block">Submit Request 2</button> --}}
                </form>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-block" id="submitRequest" disabled>Submit
                        Request</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
    {{-- Modal End --}}

    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <!-- Add any page-specific scripts here -->



    <script>
        $(document).ready(function() {
            $("#datatable").DataTable(), $("#datatable-buttons").DataTable({
                lengthChange: !1,
                buttons: ["copy", "excel", "pdf", "colvis"]
            }).buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"), $(
                ".dataTables_length select").addClass("form-select form-select-sm")
        });
    </script>

    <script>
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
                        });;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', xhr.responseJSON.error, 'error');
                    }
                });
            });


            var arrayCount = 1;
            var itemCount = 2;

            $.noConflict();

            function initializeSelectpicker($el) {
                $el.selectpicker();
            }

            function fillOtherFields(partName, arrayCount) {
                // Make an AJAX request to retrieve data based on partName
                $.ajax({
                    url: '{{ route('retrieve.part.details') }}',
                    method: 'GET',
                    data: {
                        partName: partName
                    },
                    success: function(data) {
                        $(`input[name="pr_request[${arrayCount}][UoM]"]`).val(data.part.UoM);
                        console.log(data.stock);
                        console.log(arrayCount);
                        $(`input[name="pr_request[${arrayCount}][requires_stock_reduction]"]`).val(data
                            .stock);
                        $(`input[name="pr_request[${arrayCount}][category]"]`).val(data.part.category);
                        $(`textarea[name="pr_request[${arrayCount}][type]"]`).val(data.part.type);
                        $(`input[name="pr_request[${arrayCount}][partlist_id]"]`).val(data.part.id);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        // Handle error
                    }
                });
            }

            // Event handler for adding new item
            $("#addItem").click(function() {
                if (arrayCount <= 5) {
                    var newItem = `
                <div class="card card-body border border-primary">
                    <div class="mb-3">
                        <div class="form-group">
                            <label>Part/Service Name</label>
                            <select class="form-control selectpicker" 
                                    name="pr_request[${arrayCount}][part_name]" 
                                    data-live-search="true" 
                                    data-array-count="${arrayCount}" 
                                    title="Select Part/Service">
                                @foreach ($dataR as $dR)
                                    <option value="{{ $dR->id }}">{{ $dR->part_name }}</option>
                                @endforeach
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

                    // Initialize only the new selectpicker (not all)
                    let $newSelect = $(`#prRequestForm select[name="pr_request[${arrayCount}][part_name]"]`);
                    $newSelect.selectpicker();

                    arrayCount++;
                    itemCount++;
                    $('#submitRequest').prop('disabled', false);
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
                var qty = $(this).val();
                var arrayCount = $(this).data('array-count');
                var requiresStockReduction = $(
                    `input[name="pr_request[${arrayCount}][requires_stock_reduction]"]`).val();

                if (requiresStockReduction !== "false" && qty > parseInt(requiresStockReduction)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Exceeds Available Stock',
                        text: `The quantity cannot exceed the available stock of ${requiresStockReduction}.`
                    });
                    $(this).val(''); // Clear the invalid input
                }
            });

            initializeSelectpicker();
        });

        // Show Details Material Button
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

                    $.each(response, function(index, pr_request) {
                        var id = pr_request.partlist_id;

                        $.ajax({
                            url: '/retrieve-part-name/' + id,
                            method: 'GET',
                            success: function(partName) {

                                var cardHeader =
                                    '<div class="card-header">' +
                                    'Part Request No. ' + itemCount +
                                    '<button type="button" class="close" aria-label="Close" data-part-id="' +
                                    pr_request.id + '">' +
                                    '<span aria-hidden="true">&times;</span>' +
                                    '</button>' +
                                    '</div>';

                                var row =
                                    '<div class="card mb-3 border-primary">' +
                                    cardHeader +
                                    '<div class="card-body">' +
                                    '<div class="mb-3">' +
                                    '<label for="materialName" class="form-label">Requested Part Name :</label>' +
                                    '<input type="text" class="form-control mb-2" id="materialName" name="pr_request[' +
                                    materialCount + '][material_name]" value="' +
                                    partName + '" readonly>' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Quantity</label>' +
                                    '<input type="number" class="form-control" id="requestedQuantity" name="pr_request[' +
                                    materialCount + '][qty]" value="' + pr_request
                                    .qty + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Amount / 1 Item (Rp)</label>' +
                                    '<input type="number" class="form-control" id="requestedAmount" name="pr_request[' +
                                    materialCount + '][amount]" value="' +
                                    pr_request.amount + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Other Cost</label>' +
                                    '<input type="number" class="form-control" id="requestedOtherCost" name="pr_request[' +
                                    materialCount + '][other_cost]" value="' +
                                    pr_request.other_cost + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Vendor</label>' +
                                    '<input type="text" class="form-control" id="requestedVendor" name="pr_request[' +
                                    materialCount + '][vendor]" value="' +
                                    pr_request.vendor + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Remark</label>' +
                                    '<input type="text" class="form-control" id="requestedRemark" name="pr_request[' +
                                    materialCount + '][remark]" value="' +
                                    pr_request.remark + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Category</label>' +
                                    '<input type="text" class="form-control" id="requestedCategory" name="pr_request[' +
                                    materialCount + '][category]" value="' +
                                    pr_request.category + '">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                    '<label for="materialQuantity" class="form-label">Tag</label>' +
                                    '<input type="text" class="form-control" id="requestedTag" name="pr_request[' +
                                    materialCount + '][tag]" value="' + pr_request
                                    .tag + '">' +
                                    '</div>' +
                                    '<input type="hidden" class="form-control" name="pr_request[' +
                                    materialCount + '][id]" value="' + pr_request
                                    .id + '">' +
                                    '<input type="hidden" class="form-control" name="pr_request[' +
                                    materialCount + '][ticket_id]" value="' +
                                    pr_request.ticket_id + '">' +
                                    '</div>' +
                                    '</div>';

                                $('#materialDataForm').append(row);
                                itemCount++;
                                materialCount++;
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    });

                    $('#materialModal').modal('show');
                },
                error: function(xhr, status, error) {
                    // Handle error
                }
            });
        });

        // Save
        $('#saveMaterialChanges').click(function() {
            // console.log('saveMaterial');
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
                    });;
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

        // Delete Confirmation Modal & Button
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('deleteModal');

            var deleteButtons = document.querySelectorAll('.delete-btn');

            var itemId;

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    itemId = button.getAttribute('data-item-id');
                    modal.style.display = 'block';
                });
            });

            // Delete
            var confirmDeleteBtn = document.getElementById('confirmDelete');

            confirmDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                deleteItem(itemId);
            });

            // Cancel
            var cancelDeleteBtn = document.getElementById('cancelDelete');

            cancelDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
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
                        });;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Failed to delete ticket', 'error');
                    }
                });
            }
        });

        // Reject
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
                    data: {
                        reason: rejectReason
                    },
                    success: function(response) {
                        Swal.fire("Success", response.message, "success").then(function() {
                            location.reload();
                        });;;

                        $('#materialModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire("Error", "Failed to reject request", "error");
                    }
                });
            });
        });

        // Approve
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

        document.addEventListener('DOMContentLoaded', function() {
            const switchCheckbox = document.getElementById('flexSwitchCheckDefault');
            const inputField = document.getElementById('cashAdvance');

            switchCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    inputField.disabled = false;
                } else {
                    inputField.disabled = true;
                }
            });
        });
    </script>
@endsection
