@extends('layouts.master')

@section('title', 'Part Stock Log')
@section('description', 'View Part Stock Log History')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .card-header {
        background: linear-gradient(45deg, #2c3e50, #4a6580);
        color: white;
    }
    .badge-plus {
        background-color: #198754;
    }
    .badge-minus {
        background-color: #dc3545;
    }
    .badge-neutral {
        background-color: #6c757d;
    }
    .log-table th {
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
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
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 202, 240, 0.1);
    }
    .stock-change {
        font-weight: bold;
    }
    .stock-increase {
        color: #198754;
    }
    .stock-decrease {
        color: #dc3545;
    }
    .action-btn {
        transition: all 0.2s;
    }
    .action-btn:hover {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18"><i class="fas fa-list-alt me-2"></i>Part Stock Log</h4>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Transactions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $partStockLogs->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock Increases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $partStockLogs->where('operations', 'plus')->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="summary-card card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Stock Decreases</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $partStockLogs->where('operations', 'minus')->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today's Transactions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $partStockLogs->where('created_at', '>=', \Carbon\Carbon::today('Asia/Jakarta')->startOfDay())->where('created_at', '<=', \Carbon\Carbon::today('Asia/Jakarta')->endOfDay())->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-history me-2"></i>Stock Transaction History</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-export me-2"></i>Export Data</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-filter me-2"></i>Advanced Filters</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-columns me-2"></i>Customize Columns</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="row filter-section mb-4">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Operation Type</label>
                        <select class="form-select" id="operationFilter">
                            <option value="">All Operations</option>
                            <option value="plus">Stock Increase</option>
                            <option value="minus">Stock Decrease</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Part</label>
                        <select class="form-select" id="partFilter">
                            <option value="">All Parts</option>
                            @foreach($partStockLogs->pluck('part_list_id')->unique() as $partId)
                                <option value="{{ $partId }}">{{ App\Models\partList::find($partId)->part_name }} ({{ $partId }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                    <div class="col-12 mt-2">
                        <button class="btn btn-primary me-2" id="applyFilters"><i class="fas fa-filter me-1"></i> Apply Filters</button>
                        <button class="btn btn-outline-secondary" id="resetFilters"><i class="fas fa-sync-alt me-1"></i> Reset</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="partStockLogTable" class="table table-bordered table-hover log-table">
                        <thead class="table-light">
                            <tr>
                                <th>Log ID</th>
                                <th>Part Name (ID)</th>
                                <th>Operation</th>
                                <th>Quantity</th>
                                <th>Before Qty</th>
                                <th>After Qty</th>
                                <th>Source</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partStockLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->partList->part_name }} ({{ $log->part_list_id }})</td>
                                <td>
                                    @if ($log->operations === 'plus')
                                        <span class="badge bg-success">{{ $log->operations }}</span>
                                    @elseif($log->operations === 'minus')
                                        <span class="badge bg-danger">{{ $log->operations }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->operations }}</span>
                                    @endif
                                </td>
                                <td class="stock-change @if($log->operations === 'plus') stock-increase @elseif($log->operations === 'minus') stock-decrease @endif">
                                    @if($log->operations === 'plus') + @elseif($log->operations === 'minus') - @endif{{ $log->quantity }}
                                </td>
                                <td>{{ $log->before_quantity ?? 0 }}</td>
                                <td>{{ $log->after_quantity ?? 0 }}</td>
                                <td>{{ $log->source ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info action-btn view-info-btn"
                                            data-id="{{ $log->id }}"
                                            data-source="{{ e($log->source ?? '-') }}"
                                            data-type="{{ e($log->source_type ?? '-') }}"
                                            data-ref="{{ e($log->source_ref ?? '-') }}"
                                            data-bs-toggle="tooltip" title="View Details">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary action-btn export-btn" 
                                            data-id="{{ $log->id }}" 
                                            data-bs-toggle="tooltip" title="Export Entry">
                                        <i class="fas fa-file-export"></i>
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
@endsection

@section('page-vendors-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#partStockLogTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, "desc"]],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            { targets: 0, width: "7%" },
            { targets: 1, width: "15%" },
            { targets: 2, width: "10%" },
            { targets: 3, width: "8%" },
            { targets: 4, width: "8%" },
            { targets: 5, width: "8%" },
            { targets: 6, width: "20%" },
            { targets: 7, width: "15%" },
            { targets: 8, width: "9%" }
        ]
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Custom date range filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();
        var createdAt = data[7]; // Column 7 is 'Created At'

        // Parse the table date (format: "d M Y, h:i A")
        var momentDate = moment(createdAt, 'D MMM YYYY, h:mm A');
        if (!momentDate.isValid()) {
            return true; // If date is invalid, include the row
        }

        // Parse filter dates
        var startDate = dateFrom ? moment(dateFrom, 'YYYY-MM-DD') : null;
        var endDate = dateTo ? moment(dateTo, 'YYYY-MM-DD').endOf('day') : null;

        // Date range filtering logic
        if (startDate && endDate) {
            return momentDate.isBetween(startDate, endDate, null, '[]');
        } else if (startDate) {
            return momentDate.isSameOrAfter(startDate);
        } else if (endDate) {
            return momentDate.isSameOrBefore(endDate);
        }

        return true; // No date filters applied
    });

    // Custom part filter
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var partFilter = $('#partFilter').val();
        if (!partFilter) {
            return true; // No part filter applied
        }

        var partColumn = data[1]; // Column 1 is 'Part Name (ID)'
        // Extract part_list_id from the format "Part Name (ID)"
        var matches = partColumn.match(/\((\d+)\)$/);
        var partId = matches ? matches[1] : '';

        return partId === partFilter;
    });

    // Filter functionality
    $('#applyFilters').on('click', function() {
        var operationFilter = $('#operationFilter').val();
        var partFilter = $('#partFilter').val();

        // Apply operation filter (column 2)
        table.column(2).search(operationFilter).draw();

        // Trigger part and date filters (handled by custom filters)
        table.draw();

        // Debugging: Log the applied filters
        console.log('Applied Filters:', {
            operation: operationFilter,
            part: partFilter,
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val()
        });
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#operationFilter').val('');
        $('#partFilter').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.column(2).search('').draw();
    });

    // Toggle child row for info
    $('#partStockLogTable tbody').on('click', '.view-info-btn', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        var source = $(this).data('source') || '-';
        var type = $(this).data('type') || '-';
        var ref = $(this).data('ref') || '-';
        var id = $(this).data('id');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            var content = `
            <div class="card bg-light mb-2">
                <div class="card-body p-3">
                    <h6 class="card-title mb-3">Log #${id} — Detail</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Source:</strong> ${source}</p>
                            <p class="mb-1"><strong>Source Type:</strong> ${type}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Source Ref:</strong> ${ref}</p>
                        </div>
                    </div>
                </div>
            </div>
            `;
            row.child(content).show();
            tr.addClass('shown');
        }
    });

    // Export button functionality
    $('#partStockLogTable tbody').on('click', '.export-btn', function() {
        var logId = $(this).data('id');
        // Implement export functionality here
        console.log('Exporting log entry: ' + logId);
        alert('Export functionality would be implemented for log ID: ' + logId);
    });
});
</script>
@endsection 