@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <!-- Unread Notifications Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Unread Notifications</p>
                            <h4 class="mb-0" id="unread-notifications">0</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-circle">
                                <i class="bx bx-bell"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Part Stock Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">Total Part Stock</p>
                            <h4 class="mb-0" id="total-stock">0</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-circle">
                                <i class="bx bx-box"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PR Status Chart -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">PR Status Overview</h4>
                    <canvas id="pr-status-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent PR Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Recent PR Activities</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>Ticket Code</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recent-pr-activities">
                                <!-- Data will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-vendors-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch dashboard data
            $.ajax({
                url: '{{ route("dashboard.data") }}',
                method: 'GET',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    // Unread Notifications
                    $('#unread-notifications').text(response.unread_notifications);

                    // Total Part Stock
                    $('#total-stock').text(response.total_stock);

                    // PR Status Chart
                    const ctx = document.getElementById('pr-status-chart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Pending', 'Approved', 'Rejected', 'Revised'],
                            datasets: [{
                                label: 'Number of PR Tickets',
                                data: response.pr_status_counts,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.5)',
                                    'rgba(54, 162, 235, 0.5)',
                                    'rgba(255, 206, 86, 0.5)',
                                    'rgba(75, 192, 192, 0.5)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });

                    // Recent PR Activities
                    let html = '';
                    response.recent_pr_tickets.forEach(ticket => {
                        html += `<tr>
                            <td>${ticket.ticketCode}</td>
                            <td>${ticket.status}</td>
                            <td>${new Date(ticket.updated_at).toLocaleDateString()}</td>
                        </tr>`;
                    });
                    $('#recent-pr-activities').html(html);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching dashboard data:', error);
                }
            });
        });
    </script>
@endsection