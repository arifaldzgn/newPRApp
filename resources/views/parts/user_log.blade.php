@extends('layouts.master')

@section('title', 'User Log')
@section('description', 'All Time User Log')

@section('content')

    <!-- Log History Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Log History</h4>
                    </div>

                    <table id="logTable" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>User Name/ID</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Row ID</th>
                                <th>Ticket Code</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                @php
                                    // Parse new_data JSON if it exists
                                    $newData = $log->new_data ? json_decode($log->new_data, true) : [];
                                @endphp
                                <tr>
                                    {{-- <td>{{ $loop->iteration }}</td> --}}
                                    <td>{{ $log->id}}</td>
                                    <td>{{ $log->user->name . ' (' . $log->user_id . ')'}}</td>
                                    <td>
                                        @if ($log->action === 'updated')
                                            <span class="badge bg-info">{{ $log->action }}</span>
                                        @elseif($log->action === 'deleted')
                                            <span class="badge bg-danger">{{ $log->action }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->table_name }}</td>
                                    <td>{{ $log->row_id }}</td>
                                    <td>{{ $newData['ticketCode'] ?? 'N/A' }}</td>
                                    <td>
                                        @if (isset($newData['status']))
                                            @if ($newData['status'] === 'Pending')
                                                <span class="badge bg-secondary">{{ $newData['status'] }}</span>
                                            @elseif($newData['status'] === 'Revised')
                                                <span class="badge bg-warning">{{ $newData['status'] }}</span>
                                            @elseif($newData['status'] === 'Rejected')
                                                <span class="badge bg-danger">{{ $newData['status'] }}</span>
                                            @else
                                                <span class="badge bg-success">{{ $newData['status'] }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d F Y, h:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->updated_at)->format('d F Y, h:i A') }}</td>
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
                        </form>
                        <div class="d-grid col-6 mx-auto">
                            <button class="btn btn-primary btn-block" id="addItem" type="button">Add New Items</button>
                        </div>
                    </div>
                </div>
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
    <!-- Add DataTables initialization for the log table -->
    <script>
        $(document).ready(function() {
            $('#logTable').DataTable({
                "responsive": true,
                "pageLength": 10,
                "order": [[0, "desc"]], // Sort by Log ID descending
                "columnDefs": [
                    { "width": "10%", "targets": 0 }, // Log ID
                    { "width": "10%", "targets": 1 }, // User ID
                    { "width": "10%", "targets": 2 }, // Action
                    { "width": "15%", "targets": 3 }, // Table
                    { "width": "10%", "targets": 4 }, // Row ID
                    { "width": "15%", "targets": 5 }, // Ticket Code
                    { "width": "10%", "targets": 6 }, // Status
                    { "width": "15%", "targets": 7 }, // Created At
                    { "width": "15%", "targets": 8 }  // Updated At
                ]
            });
        });
    </script>
@endsection