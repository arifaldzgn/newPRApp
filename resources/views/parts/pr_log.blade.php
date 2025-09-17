@extends('layouts.master')

@section('title', 'Part Stock Log')
@section('description', 'View Part Stock Log History')

@section('content')
    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Part Stock Log</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Stock Transaction History</h4>
                    </div>

                    <table id="partStockLogTable" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Part ID</th>
                                <th>Operation</th>
                                <th>Quantity</th>
                                <th>Source</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partStockLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->part_list_id }}</td>
                                    <td>
                                        @if ($log->operations === 'plus')
                                            <span class="badge bg-success">{{ $log->operations }}</span>
                                        @elseif($log->operations === 'minus')
                                            <span class="badge bg-danger">{{ $log->operations }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->operations }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->quantity }}</td>
                                    <td>{{ $log->source }}</td>
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
    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <script>
        $(document).ready(function() {
            $('#partStockLogTable').DataTable({
                "responsive": true,
                "pageLength": 10,
                "order": [[0, "desc"]], // Sort by Log ID descending
                "columnDefs": [
                    { "width": "10%", "targets": 0 }, // Log ID
                    { "width": "10%", "targets": 1 }, // Part ID
                    { "width": "15%", "targets": 2 }, // Operation
                    { "width": "10%", "targets": 3 }, // Quantity
                    { "width": "25%", "targets": 4 }, // Source
                    { "width": "15%", "targets": 5 }, // Created At
                    { "width": "15%", "targets": 6 }  // Updated At
                ]
            });
        });
    </script>
@endsection