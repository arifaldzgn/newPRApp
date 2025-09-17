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
                        <h4 class="card-title mb-0">Your Approved Tickets</h4>
                        <button type="button" class="btn btn-success waves-effect btn-label waves-light"
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
                                <th class="t-center"><center>Action</center></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // dd($dataT);
                            @endphp
                            @foreach ($dataT as $dT)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($dT->created_at)->format('d F Y, h:i A') }}</td>
                                <td>{{ $dT->ticketCode }}</td>
                                <td>{{ $dT->user->name }}</td>
                                <td>
                                    @if ($dT->status === 'Pending')
                                        <span class="badge bg-secondary">{{ $dT->status }}</span>
                                    @elseif ($dT->status === 'Approved')
                                        <span class="badge bg-success">{{ $dT->status }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $dT->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <center>
                                        @if ($dT->status === 'Pending')
                                        <button class="updateBtn btn btn-primary" data-request-id="{{ $dT->id }}"><i class="bi bi-pencil-square"></i></button>
                                        @else
                                        <a class="btn btn-primary" data-request-id="{{ $dT->id }}" href="/printTicket/{{$dT->ticketCode}}" target="_blank"><i class="bi bi-printer"></i></a>
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
    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <script>
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
                    }).then(function(){
                        location.reload();
                    });;
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
                        Swal.fire('Success', 'Part deleted', 'success').then(function(){
                            location.reload();
                        });;
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Failed to delete the part', 'error');
                    }
                });
            }
        });




        // // Create our number formatter.
        // var formatter = new Intl.NumberFormat('id-ID', {
        // style: 'currency',
        // currency: 'IDR',
        // });

        // document.querySelector('#typeNumber').addEventListener('change', (e)=>{
        // if(isNaN(e.target.value)){
        //     e.target.value = ''
        // }else{
        //     e.target.value = formatter.format(e.target.value);
        // }
        // })




    jQuery(document).ready(function($) {
        var arrayCount = 1;
        var itemCount = 2;

        $.noConflict();

        // Function to initialize selectpicker
        function initializeSelectpicker() {
            $('.selectpicker').selectpicker('refresh');
        }

        // Function to handle filling other fields based on selected part_name
        function fillOtherFields(partName, arrayCount) {
            // Make an AJAX request to retrieve data based on partName
            $.ajax({
                url: '{{ route('retrieve.part.details') }}',
                method: 'GET',
                data: { partName: partName },
                success: function(data) {
                    // Populate other fields based on retrieved data
                    $(`input[name="pr_request[${arrayCount}][UoM]"]`).val(data.UoM);
                    console.log(data.UoM);
                    console.log(arrayCount);
                    $(`input[name="pr_request[${arrayCount}][category]"]`).val(data.category);
                    $(`textarea[name="pr_request[${arrayCount}][type]"]`).val(data.type);
                    $(`input[name="pr_request[${arrayCount}][partlist_id]"]`).val(data.id);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    // Handle error
                }
            });
        }



        // Event listener for change on part_name select
        $(document).on('change', '.selectpicker', function() {
            var partName = $(this).val();
            var arrayCount = $(this).attr('data-array-count');
            fillOtherFields(partName, arrayCount);
        });

        // Initialize selectpicker on page load
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
                            // Create and append row inside the success function
                            var row =
                                '<div class="card mb-3 border-success">' +
                                '<div class="card-header">' +
                                'Part Request No. '+ itemCount + '' +
                                '</div>' +
                                '<div class="card-body">' +
                                    '<div class="mb-3">' +
                                        '<label for="materialName" class="form-label">Requested Part Name :</label>' +
                                        '<input type="text" class="form-control mb-2" id="materialName" name="pr_request['+materialCount+'][material_name]" value="'+partName+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Quantity</label>' +
                                        '<input type="number" class="form-control" id="requestedQuantity" name="pr_request['+materialCount+'][qty]" value="'+pr_request.qty+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Amount (Rp.)</label>' +
                                        '<input type="number" class="form-control" id="requestedAmount" name="pr_request['+materialCount+'][amount]" value="'+pr_request.amount+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Other Cost</label>' +
                                        '<input type="number" class="form-control" id="requestedOtherCost" name="pr_request['+materialCount+'][other_cost]" value="'+pr_request.other_cost+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Vendor</label>' +
                                        '<input type="text" class="form-control" id="requestedVendor" name="pr_request['+materialCount+'][vendor]" value="'+pr_request.vendor+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Remark</label>' +
                                        '<input type="text" class="form-control" id="requestedRemark" name="pr_request['+materialCount+'][remark]" value="'+pr_request.remark+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Category</label>' +
                                        '<input type="text" class="form-control" id="requestedCategory" name="pr_request['+materialCount+'][category]" value="'+pr_request.category+'">' +
                                    '</div>' +
                                    '<div class="mb-3">' +
                                        '<label for="materialQuantity" class="form-label">Tag</label>' +
                                        '<input type="text" class="form-control" id="requestedTag" name="pr_request['+materialCount+'][tag]" value="'+pr_request.tag+'">' +
                                    '</div>' +
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
    </script>
@endsection