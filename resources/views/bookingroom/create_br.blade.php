@extends('layouts.master')

@section('title', 'Create PR')
@section('description', 'Create Purchase Request page description')

@section('content')

    <!-- begin:: Content -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Booking a Meeting Room</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <div style='clear:both'></div>


            <!-- Add New Event MODAL -->
            <div class="modal fade" id="event-modal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-3 px-4 border-bottom-0">
                            <h5 class="modal-title" id="modal-title">Event</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body p-4">

                            <!-- Form Section -->
                            <div id="form-section">
                                <form id="form-event" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="event-title" class="form-label">Event Title</label>
                                        <input type="text" id="event-title" class="form-control" required
                                            placeholder="Event Title">
                                        <div class="invalid-feedback">Please enter event title.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="event-category" class="form-label">Category</label>
                                        <select id="event-category" class="form-select">
                                            <option value="">Select Category</option>
                                            <option value="bg-success">Green</option>
                                            <option value="bg-warning">Yellow</option>
                                            <option value="bg-danger">Red</option>
                                        </select>
                                    </div>

                                    {{-- <div class="mb-3">
                                        <label for="event-room" class="form-label">Room</label>
                                        <select id="event-room" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <option value="Room A">Room A</option>
                                            <option value="Room B">Room B</option>
                                            <option value="Room C">Room C</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a room.</div>
                                    </div> --}}
                                    {{-- 
                                    <div class="mb-3">
                                        <label for="event-date" class="form-label">Date</label>
                                        <input type="date" id="event-date" class="form-control" required>
                                        <div class="invalid-feedback">Please select a date.</div>
                                    </div> --}}

                                    <div class="mb-3 row">
                                        <div class="col">
                                            <label for="event-time-from" class="form-label">Time From</label>
                                            <input type="time" id="event-time-from" class="form-control" required>
                                            <div class="invalid-feedback">Please enter start time.</div>
                                        </div>
                                        <div class="col">
                                            <label for="event-time-to" class="form-label">Time To</label>
                                            <input type="time" id="event-time-to" class="form-control" required>
                                            <div class="invalid-feedback">Please enter end time.</div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="event-remark" class="form-label">Remark</label>
                                        <input type="text" id="event-remark" class="form-control" placeholder="Remark">
                                    </div>

                                    {{-- <div class="mb-3">
                                        <label for="event-requested-by" class="form-label">Requested By</label>
                                        <input autocomplete="off" type="text" id="event-requested-by" name="requested_by"
                                            class="form-control" value="{{ auth()->user()->name }}" required>

                                        <div class="invalid-feedback">Please enter who requested the event.</div>
                                    </div> --}}

                                    {{-- <div class="mb-3" id="status-field">
                                        <label for="event-status" class="form-label">Status</label>
                                        <select id="event-status" class="form-select">
                                            <option value="Pending">Pending</option>
                                            <option value="Approved">Approved</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                    </div> --}}

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="button" id="btn-delete-event" class="btn btn-danger"
                                            style="display:none;">Delete</button>
                                    </div>
                                </form>
                            </div>

                            <div id="detail-section" style="display: none;">
                                <div class="mb-3">
                                    <h5 id="event-detail-title"></h5>
                                </div>
                                <div class="mb-3">
                                    <strong>Room:</strong> <span id="info-room"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Date:</strong> <span id="info-date"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Time:</strong> <span id="info-time"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Category:</strong> <span id="info-category"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Requested By:</strong> <span id="info-requested-by"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong>
                                    <span id="info-status" class="badge"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Meeting Started:</strong> <span id="info-is-started"></span>
                                </div>
                                <div class="mb-3">
                                    <strong>Remark:</strong> <span id="info-remark"></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- end Add New Event MODAL -->

            <!-- Room Selection Modal -->
            <div class="modal fade" id="room-selection-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select a Room</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <div class="card room-card" data-room="Room A">
                                        <img src="assets/images/small/img-3.jpg" class="card-img-top" alt="...">
                                        <div class="card-body">
                                            <h5 class="card-title">Training Room</h5>
                                            <p class="card-text">Capacity: 30-45 people</p>
                                            <p class="card-text">Lantai 2 VF</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="card room-card" data-room="Room B">
                                        <img src="assets/images/small/img-3.jpg" class="card-img-top" alt="...">
                                        <div class="card-body">
                                            <h5 class="card-title">Daily Meeting Room</h5>
                                            <p class="card-text">Capacity: 12 people</p>
                                            <p class="card-text">Lantai 2 Office</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Card 3 -->
                                <div class="col-md-4 mb-3">
                                    <div class="card room-card" data-room="Room C">
                                        <img src="assets/images/small/img-3.jpg" class="card-img-top" alt="...">
                                        <div class="card-body">
                                            <h5 class="card-title">Daily Meeting Room 2</h5>
                                            <p class="card-text">Capacity: 20 people</p>
                                            <p class="card-text">Lantai 1</p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Detail Modal -->
            <div class="modal fade" id="room-detail-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="room-detail-title">Room Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Name:</strong> <span id="detail-room-name"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Capacity:</strong> <span id="detail-room-capacity"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Location:</strong> <span id="detail-room-location"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="button" id="btn-select-room" class="btn btn-primary">Select This Room</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end Room Detail Modal -->


        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Your Active Meeting</h4>
                    </div>
                    <table id="active-table" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Room</th>
                                <th>Title</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Requested By</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataT['active'] as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['room'] }}</td>
                                    <td>{{ $row['title'] }}</td>
                                    <td>{{ $row['category'] }}</td>
                                    <td>{{ $row['date'] }}</td>
                                    <td>{{ $row['time_from'] }} - {{ $row['time_to'] }}</td>
                                    <td>{{ $row['requested_by'] }}</td>
                                    <td><span
                                            class="badge bg-{{ $row['status'] === 'Approved' ? 'success' : ($row['status'] === 'Pending' ? 'warning' : ($row['status'] === 'Rejected' ? 'secondary' : 'danger')) }}">{{ $row['status'] }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#edit-event-modal"
                                            data-event="{{ json_encode($row) }}">Edit</button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#delete-modal"
                                            data-event-id="{{ $row['id'] }}">Cancel</button>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#event-modal" data-event="{{ json_encode($row) }}">Start
                                            Meeting</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Your Past Requests</h4>
                    </div>
                    <table id="past-table" class="table table-bordered nowrap w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Room</th>
                                <th>Title</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Requested By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataT['past'] as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['room'] }}</td>
                                    <td>{{ $row['title'] }}</td>
                                    <td>{{ $row['category'] }}</td>
                                    <td>{{ $row['date'] }}</td>
                                    <td>{{ $row['time_from'] }} - {{ $row['time_to'] }}</td>
                                    <td>{{ $row['requested_by'] }}</td>
                                    <td><span
                                            class="badge bg-{{ $row['status'] === 'Approved' ? 'success' : ($row['status'] === 'Pending' ? 'warning' : ($row['status'] === 'Rejected' ? 'secondary' : 'danger')) }}">{{ $row['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <!-- Event Modal -->
    <div class="modal fade" id="edit-event-modal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Reschedule Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="event-form" class="needs-validation" novalidate>
                        <input type="hidden" id="event-id" name="id">
                        <input type="hidden" id="event-user-id" name="user_id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light p-3">
                                    <h6>Current Booking Details</h6>
                                    <div><strong>Room:</strong> <span id="current-room"></span></div>
                                    <div><strong>Date:</strong> <span id="current-date"></span></div>
                                    <div><strong>Time:</strong> <span id="current-time"></span></div>
                                    <div><strong>Status:</strong> <span id="current-status" class="badge"></span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light p-3">
                                    <h6>Additional Information</h6>
                                    <div><strong>Requested by:</strong> <span id="current-requested-by"></span></div>
                                    <div><strong>Category:</strong> <span id="current-category" class="badge"></span>
                                    </div>
                                    <div><strong>Remarks:</strong> <span id="current-remark"></span></div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">Reschedule Options</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event-date" class="form-label">New Date *</label>
                                    <input type="date" class="form-control" id="event-date" name="date" required>
                                    <div class="invalid-feedback">Please select a date.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="event-time-from" class="form-label">New Start Time *</label>
                                            <input type="time" class="form-control" id="event-time-from"
                                                name="time_from" required>
                                            <div class="invalid-feedback">Please select start time.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="event-time-to" class="form-label">New End Time *</label>
                                            <input type="time" class="form-control" id="event-time-to" name="time_to"
                                                required>
                                            <div class="invalid-feedback">Please select end time.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="event-category" class="form-label">Category</label>
                                    <select class="form-select" id="event-category" name="category">
                                        <option value="bg-success">Green (Available)</option>
                                        <option value="bg-warning">Yellow (Limited)</option>
                                        <option value="bg-danger">Red (Booked)</option>
                                        <option value="bg-primary">Blue (Private)</option>
                                        <option value="bg-info">Teal (Meeting)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="event-remark" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="event-remark" name="remark" rows="2" maxlength="500"></textarea>
                                    <div class="form-text">Maximum 500 characters</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-danger" id="btn-delete-event">
                                <i class="fas fa-trash-alt me-2"></i>Delete Booking
                            </button>
                            <div>
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this booking? This action cannot be undone.</p>
                    <input type="hidden" id="delete-event-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>





    <!-- end:: Content -->
@endsection

@section('page-vendors-scripts')
    <!-- Add any page-specific scripts here -->

    <!-- FullCalendar plugins -->
    <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-ui-dist/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/core/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/bootstrap/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/daygrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/timegrid/main.min.js') }}"></script>
    <script src="{{ asset('assets/libs/@fullcalendar/interaction/main.min.js') }}"></script>
    <script>
        window.authUser = {
            id: {{ auth()->id() }},
            name: '{{ auth()->user()->name }}',
            role: '{{ auth()->user()->role }}'
        };
    </script>
    <script src="assets/js/pages/calendars-full.init.js"></script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventModal = new bootstrap.Modal(document.getElementById('event-modal'));
            const eventForm = document.getElementById('event-form');

            // Format time for display (HH:MM:00 to HH:MM)
            function formatTimeForDisplay(timeString) {
                if (!timeString) return '';
                return timeString.substring(0, 5);
            }

            // Format date for display (YYYY-MM-DD to DD/MM/YYYY)
            function formatDateForDisplay(dateString) {
                if (!dateString) return '';
                const [year, month, day] = dateString.split('-');
                return `${day}/${month}/${year}`;
            }

            // Format badge for category
            function getCategoryBadge(category) {
                if (!category) return '';
                const color = category.split('-')[1] || 'secondary';
                const text = category === 'bg-success' ? 'Available' :
                    category === 'bg-warning' ? 'Limited' :
                    category === 'bg-danger' ? 'Booked' :
                    category === 'bg-primary' ? 'Private' :
                    category === 'bg-info' ? 'Meeting' : 'Other';
                return `<span class="badge bg-${color}">${text}</span>`;
            }

            // Format badge for status
            function getStatusBadge(status) {
                if (!status) return '';
                const color = status === 'Approved' ? 'success' :
                    status === 'Pending' ? 'warning' :
                    status === 'Rejected' ? 'danger' : 'secondary';
                return `<span class="badge bg-${color}">${status}</span>`;
            }

            // Handle edit button clicks
            document.querySelectorAll('[data-bs-target="#event-modal"]').forEach(button => {
                button.addEventListener('click', function() {
                    const eventData = JSON.parse(this.getAttribute('data-event'));
                    populateEventForm(eventData);
                });
            });

            // Populate form with event data
            function populateEventForm(eventData) {
                // Set hidden fields
                document.getElementById('event-id').value = eventData.id || '';
                document.getElementById('event-user-id').value = eventData.user_id || '';

                // Display current booking details
                document.getElementById('current-room').textContent = eventData.room || '';
                document.getElementById('current-date').textContent = formatDateForDisplay(eventData.date) || '';
                document.getElementById('current-time').textContent =
                    `${formatTimeForDisplay(eventData.time_from)} - ${formatTimeForDisplay(eventData.time_to)}`;
                document.getElementById('current-status').innerHTML = getStatusBadge(eventData.status);
                document.getElementById('current-requested-by').textContent = eventData.requested_by || '';
                document.getElementById('current-category').innerHTML = getCategoryBadge(eventData.category);
                document.getElementById('current-remark').textContent = eventData.remark || 'No remarks';

                // Set editable fields
                document.getElementById('event-date').value = eventData.date || '';
                document.getElementById('event-time-from').value = formatTimeForDisplay(eventData.time_from) || '';
                document.getElementById('event-time-to').value = formatTimeForDisplay(eventData.time_to) || '';
                document.getElementById('event-category').value = eventData.category || 'bg-warning';
                document.getElementById('event-remark').value = eventData.remark || '';

                eventModal.show();
            }

            // Form submission handler
            eventForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!this.checkValidity()) {
                    e.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }

                const formData = new FormData(this);
                const eventData = Object.fromEntries(formData.entries());

                // Convert time fields to proper format (HH:MM:00)
                if (eventData.time_from && !eventData.time_from.includes(':')) {
                    eventData.time_from = `${eventData.time_from}:00`;
                }
                if (eventData.time_to && !eventData.time_to.includes(':')) {
                    eventData.time_to = `${eventData.time_to}:00`;
                }

                saveEvent(eventData);
            });

            // Save event via AJAX
            function saveEvent(eventData) {
                fetch('/booking_room/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(eventData)
                    })
                    .then(response => {
                        if (response.status === 403) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Unauthorized');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error saving booking');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'Error processing request');
                    });
            }
        });
    </script>




@endsection
