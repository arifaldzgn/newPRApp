<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Etowa PR Dept</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <!-- FullCalendar CSS -->
    <link href="{{ asset('assets/libs/@fullcalendar/core/main.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/@fullcalendar/daygrid/main.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/@fullcalendar/bootstrap/main.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/@fullcalendar/timegrid/main.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>

        /* Notification things */
        #notification-dropdown.dropdown-menu {
            min-width: 300px;
            max-width: 400px;
            z-index: 1050 !important;
            right: 0;
            left: auto;
        }
        .notification-item {
            display: block;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        #notification-content {
            max-height: 230px;
            overflow-y: auto !important;
        }
        /* #notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            display: inline-block !important; 
            z-index: 1051; /* Above other elements */
        } */
        .navbar-header {
            position: relative;
            overflow: visible !important;
        }
        .modal-dialog {
            z-index: 1060 !important; 
        }
        @media (max-width: 768px) {
            #notification-dropdown.dropdown-menu {
                width: 100%;
                max-width: none;
                right: 0;
                left: 0;
            }
        }

        #ticketModal .modal-header {
            border-bottom: 1px solid #e9ecef;
        }

        #ticketModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #ticketModal .card {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        #ticketModal .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        #ticketModal .table-responsive {
            margin-bottom: 0;
        }

        #ticketModal .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        #ticketModal .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        #ticketModal .badge {
            font-size: 0.9rem;
            padding: 0.25rem 0.5rem;
        }

        #ticketModal .collapse.show {
            display: block;
        }
    </style>

    @stack('styles')
</head>

<body data-sidebar="dark" data-layout-mode="light">
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('includes.header')

        @include('includes.aside')

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            @include('includes.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title d-flex align-items-center px-3 py-4">
                <h5 class="m-0 me-2">Settings</h5>
                <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                    <i class="mdi mdi-close noti-icon"></i>
                </a>
            </div>

            <!-- Settings -->
            <hr class="mt-0" />
            <h6 class="text-center mb-0">Choose Layouts</h6>

            <div class="p-4">
                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-1.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-2.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch">
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-3.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="rtl-mode-switch">
                    <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-4.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-5">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-rtl-mode-switch">
                    <label class="form-check-label" for="dark-rtl-mode-switch">Dark RTL Mode</label>
                </div>
            </div>
        </div> <!-- end slimscroll-menu-->
    </div>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    {{-- Notification Modal --}}
    <div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ticketModalLabel">
                        <i class="bx bx-detail me-2"></i> Ticket Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="ticket-details-content">
                        <!-- Ticket details will be loaded here -->
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bx bx-info-circle me-2"></i> View detailed information about the ticket and its associated purchase requests.
                        </div>
                    </div>
                </div>
                {{-- <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="exportPdfBtn" disabled>
                        <i class="bx bx-download me-1"></i> Export to PDF
                    </button>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Cari semua menu yang aktif
        document.querySelectorAll("#side-menu .mm-active").forEach(function (activeItem) {
            let parent = activeItem.closest("ul.sub-menu");
            if (parent) {
                parent.classList.add("mm-show"); // buka sub-menu
                let parentLink = parent.previousElementSibling;
                if (parentLink) {
                    parentLink.classList.add("mm-active"); // highlight induk
                }
            }
        });
    });

    $(document).ready(function() {
        // Set CSRF token globally
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize SimpleBar
        if (typeof SimpleBar !== 'undefined') {
            new SimpleBar(document.getElementById('notification-content'));
        } else {
            console.warn('SimpleBar not loaded, using default scroll');
        }

        function loadNotifications() {
            $.ajax({
                url: '{{ route("notifications.fetch") }}',
                method: 'GET',
                success: function(response) {
                    console.log('Response:', response);
                    $('#notification-count').text(response.count || 0);
                    $('#notification-content').html(response.html || '<p class="p-3 text-center">No new notifications.</p>');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error, xhr.responseText);
                    $('#notification-count').text('0');
                    $('#notification-content').html('<p class="p-3 text-center">Failed to load notifications.</p>');
                }
            });
        }

        // Load notifications on page load
        loadNotifications();
        setInterval(loadNotifications, 30000);

        // Handle notification clicks
        $(document).on('click', '.notification-item', function(e) {
            console.log('Notification item clicked:', $(this).data('id'));
            e.preventDefault();
            let $link = $(this);
            let id = $link.data('id');
            let href = $link.attr('href');
            console.log('Clicked notification:', id, 'Href:', href);

            let ticketId = null;
            if (href) {
                let parts = href.split('/');
                ticketId = parts[parts.length - 1];
                if (!ticketId || isNaN(ticketId)) {
                    console.error('Invalid ticket ID from href:', href);
                    $('#ticket-details-content').html('<div class="alert alert-danger" role="alert"><i class="bx bx-error me-2"></i> Invalid ticket ID.</div>');
                    $('#ticketModal').modal('show');
                    return;
                }
            } else {
                console.error('Href attribute missing on notification item');
                $('#ticket-details-content').html('<div class="alert alert-danger" role="alert"><i class="bx bx-error me-2"></i> Unable to load ticket details.</div>');
                $('#ticketModal').modal('show');
                return;
            }

            $link.addClass('disabled');

            // Mark notification as read
            $.ajax({
                url: '{{ route("notifications.read") }}',
                method: 'POST',
                data: { id: id },
                success: function() {
                    console.log('Marked as read:', id);
                    loadNotifications();
                },
                error: function(xhr, status, error) {
                    console.error('Read error:', error, xhr.status);
                    $link.removeClass('disabled');
                }
            });

            // Fetch ticket details
            $.ajax({
                url: '{{ route("ticketDetails", ":id") }}'.replace(':id', ticketId),
                method: 'GET',
                success: function(data) {
                    console.log('Ticket details:', data);
                    let statusBadge = '';
                    switch (data.ticket.status.toLowerCase()) {
                        case 'pending':
                            statusBadge = '<span class="badge bg-secondary">Pending</span>';
                            break;
                        case 'approved':
                            statusBadge = '<span class="badge bg-success">Approved</span>';
                            break;
                        case 'rejected':
                            statusBadge = '<span class="badge bg-danger">Rejected</span>';
                            break;
                        case 'revised':
                            statusBadge = '<span class="badge bg-warning">Revised</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge bg-info">Unknown</span>';
                    }

                    let html = `
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0"><i class="bx bx-ticket me-2"></i> Ticket Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Ticket Code:</strong> ${data.ticket.ticketCode}</p>
                                        <p class="mb-1"><strong>Status:</strong> ${statusBadge}</p>
                                        <p class="mb-1"><strong>Requester:</strong> ${data.requester_name}</p>
                                        <p class="mb-1"><strong>Approver:</strong> ${data.approver_name || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Advance Cash:</strong> ${data.advance_cash || '0'} Rp</p>
                                        <p class="mb-1"><strong>Date Approval:</strong> ${data.ticket.date_approval || 'N/A'}</p>
                                        <p class="mb-1"><strong>Reason for Rejection:</strong> ${data.ticket.reason_reject || 'N/A'}</p>
                                        <p class="mb-1"><strong>Created At:</strong> ${new Date(data.ticket.created_at).toLocaleString()}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="bx bx-list-ul me-2"></i> Purchase Requests</h5>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#prRequestsTable" aria-expanded="false" aria-controls="prRequestsTable">
                                    <i class="bx bx-chevron-down"></i> Toggle
                                </button>
                            </div>
                            <div class="collapse show" id="prRequestsTable">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Vendor</th>
                                                    <th>Quantity</th>
                                                    <th>Amount (Rp)</th>
                                                    <th>Other Cost (Rp)</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.pr_requests.length > 0 ? data.pr_requests.map(req => `
                                                    <tr>
                                                        <td>${req.category || 'N/A'}</td>
                                                        <td>${req.vendor || 'N/A'}</td>
                                                        <td>${req.qty || '0'}</td>
                                                        <td>${req.amount || '0'}</td>
                                                        <td>${req.other_cost || '0'}</td>
                                                        <td>${req.remark || 'N/A'}</td>
                                                    </tr>
                                                `).join('') : '<tr><td colspan="6" class="text-center">No PR requests found.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#ticket-details-content').html(html);
                    $('#ticketModal').modal('show');
                    $('#exportPdfBtn').prop('disabled', false); // Enable export button
                    $link.removeClass('disabled');
                },
                error: function(xhr, status, error) {
                    console.error('Ticket details error:', error, xhr.status);
                    $('#ticket-details-content').html('<div class="alert alert-danger" role="alert"><i class="bx bx-error me-2"></i> Failed to load ticket details.</div>');
                    $('#ticketModal').modal('show');
                    $link.removeClass('disabled');
                }
            });
        });

        // Initialize Bootstrap dropdown
        $('#page-header-notifications-dropdown').dropdown({ autoClose: false });
    });
    </script>

    


    @stack('scripts')
</body>
@yield('page-vendors-scripts')

</html>
