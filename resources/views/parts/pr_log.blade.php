@extends('layouts.master')

@section('title', 'Part Stock Log')
@section('description', 'View Part Stock Log History')

@section('content')
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
              <th>Action</th>
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
                <td>{{ $log->source ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d F Y, h:i A') }}</td>
                <td>{{ \Carbon\Carbon::parse($log->updated_at)->format('d F Y, h:i A') }}</td>
                <td>
                  <button class="btn btn-sm btn-info view-info-btn"
                          data-id="{{ $log->id }}"
                          data-source="{{ e($log->source ?? '-') }}"
                          data-type="{{ e($log->source_type ?? '-') }}"
                          data-ref="{{ e($log->source_ref ?? '-') }}">
                    Info
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
@endsection

@section('page-vendors-scripts')
<script>
  $(document).ready(function() {
    var table = $('#partStockLogTable').DataTable({
      responsive: true,
      pageLength: 10,
      order: [[0, "desc"]],
      columnDefs: [
        { width: "8%", targets: 0 },
        { width: "8%", targets: 1 },
        { width: "12%", targets: 2 },
        { width: "8%", targets: 3 },
        { width: "30%", targets: 4 },
        { width: "12%", targets: 5 },
        { width: "12%", targets: 6 },
        { width: "5%", targets: 7 }
      ]
    });

    // toggle child row for info
    $('#partStockLogTable tbody').on('click', '.view-info-btn', function() {
      var tr = $(this).closest('tr');
      var row = table.row(tr);

      var source = $(this).attr('data-source') || '-';
      var type   = $(this).attr('data-type')   || '-';
      var ref    = $(this).attr('data-ref')    || '-';
      var id     = $(this).attr('data-id');

      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
      } else {
        var content = `
          <div class="card bg-info text-white-50 mb-2">
            <div class="card-body p-3">
              <h6 class="text-white mb-2">Log #${id} — Detail</h6>
              <p class="mb-1"><strong>Source:</strong> ${source}</p>
              <p class="mb-1"><strong>Source Type:</strong> ${type}</p>
              <p class="mb-0"><strong>Source Ref:</strong> ${ref}</p>
            </div>
          </div>
        `;
        row.child(content).show();
        tr.addClass('shown');
      }
    });
  });
</script>
@endsection
