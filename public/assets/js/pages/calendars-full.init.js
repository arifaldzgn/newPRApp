(function ($) {
  "use strict";

  // Room data with your updated information
  const rooms = {
    "Room A": {
      name: "Training Room",
      capacity: "30-45 people",
      location: "Lantai 2 VF",
      image: "assets/images/small/img-3.jpg"
    },
    "Room B": {
      name: "Daily Meeting Room",
      capacity: "12 people",
      location: "Lantai 2 Office",
      image: "assets/images/small/img-3.jpg"
    },
    "Room C": {
      name: "Daily Meeting Room 2",
      capacity: "20 people",
      location: "Lantai 1",
      image: "assets/images/small/img-3.jpg"
    }
  };

  function initCalendar() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    const modal = $('#event-modal');
    const roomSelectionModal = $('#room-selection-modal');
    const roomDetailModal = $('#room-detail-modal');
    const modalTitle = $('#modal-title');
    const form = $('#form-event');

    const eventTitleInput = $('#event-title');
    const eventCategoryInput = $('#event-category');
    const eventRoomInput = $('#event-room');
    const eventTimeFromInput = $('#event-time-from');
    const eventTimeToInput = $('#event-time-to');
    const eventRequestedByInput = $('#event-requested-by');
    const eventStatusInput = $('#event-status');
    const eventRemarksInput = $('#event-remark');

    const detailSection = $('#detail-section');
    const formSection = $('#form-section');

    const infoRoom = $('#info-room');
    const infoDate = $('#info-date');
    const infoTime = $('#info-time');
    const infoRequestedBy = $('#info-requested-by');
    const infoStatus = $('#info-status');

    let currentEvent = null;
    let currentRoom = null;
    let selectedDate = null;

    const validationForm = document.querySelector('.needs-validation');

    // Initialize FullCalendar with AJAX events source
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ['bootstrap', 'interaction', 'dayGrid', 'timeGrid'],
      themeSystem: 'bootstrap',
      editable: true,
      droppable: true,
      selectable: true,
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      },
      validRange: {
        start: new Date() // Disable past dates
      },
      events: {
        url: '/booking_room/list',
        method: 'GET',
      },
      dateClick: function (info) {
        selectedDate = info.date;
        currentEvent = null;
        
        // Show room selection modal first
        roomSelectionModal.modal('show');
      },
      eventClick: function (info) {
        currentEvent = info.event;
        selectedDate = null;

        const ext = currentEvent.extendedProps;

        // Show details section and hide form section
        modalTitle.text('Event Details');
        formSection.hide();
        detailSection.show();

        // Fill details with event data
        $('#event-detail-title').text(currentEvent.title);
        $('#info-room').text(ext.room || '');
        
        // Format date (e.g., "Monday, January 1, 2023")
        const eventDate = currentEvent.start ? new Date(currentEvent.start) : null;
        const formattedDate = eventDate ? eventDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }) : '';
        $('#info-date').text(formattedDate);

        // Format time (e.g., "09:00 AM - 10:00 AM")
        const formatTime = (timeStr) => {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':');
            const time = new Date();
            time.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0);
            return time.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
            });
        };

        const timeFrom = ext.time_from ? formatTime(ext.time_from) : '';
        const timeTo = ext.time_to ? formatTime(ext.time_to) : '';
        $('#info-time').text(`${timeFrom} - ${timeTo}`);

        $('#info-requested-by').text(ext.requested_by || '');
        
        // Status with badge styling
        const status = ext.status || '';
        const statusBadge = $('#info-status');
        statusBadge.text(status);
        
        // Remove all previous classes
        statusBadge.removeClass('bg-success bg-warning bg-danger bg-secondary');
        
        // Add appropriate class based on status
        if (status.toLowerCase() === 'approved') {
            statusBadge.addClass('bg-success');
        } else if (status.toLowerCase() === 'pending') {
            statusBadge.addClass('bg-warning');
        } else if (status.toLowerCase() === 'rejected') {
            statusBadge.addClass('bg-danger');
        } else {
            statusBadge.addClass('bg-secondary');
        }

        // Additional fields from your schema
        $('#info-category').text(ext.category || 'Not specified');
        $('#info-is-started').text(ext.is_started || 'No');
        $('#info-remark').text(ext.remark || 'No remarks');

        // Hide delete button in view mode
        $('#btn-delete-event').hide();

        modal.modal('show');
        },
    });

    calendar.render();

    // Room card click handler
    $('.room-card').on('click', function() {
      const roomKey = $(this).data('room');
      const room = rooms[roomKey];
      
      // Fill room details modal
      $('#room-detail-title').text(room.name);
      $('#detail-room-name').text(room.name);
      $('#detail-room-capacity').text(room.capacity);
      $('#detail-room-location').text(room.location);
      
      // Add room image if needed
      if (room.image) {
        $('#room-detail-modal .modal-body').prepend(
          `<img src="${room.image}" class="img-fluid mb-3" alt="${room.name}">`
        );
      }
      
      // Store selected room data
      currentRoom = {
        key: roomKey,
        name: room.name,
        capacity: room.capacity,
        location: room.location
      };
      
      // Hide selection modal and show details modal
      roomSelectionModal.modal('hide');
      roomDetailModal.modal('show');
    });

    // Select room button handler
    $('#btn-select-room').on('click', function() {
      roomDetailModal.modal('hide');
      
      // Clear any previous room image
      $('#room-detail-modal .modal-body img').remove();
      
      // Now show the event form with the selected room
      modalTitle.text('Add Event');
      form[0].reset();
      form.removeClass('was-validated');
      $('#btn-delete-event').hide();

      // Set defaults for time (e.g., 09:00 to 10:00)
      const defaultTimeFrom = "09:00";
      const defaultTimeTo = "10:00";

      eventTitleInput.val('');
      eventCategoryInput.val('');
      eventRoomInput.val(currentRoom.name); // Set the selected room name
      eventTimeFromInput.val(defaultTimeFrom);
      eventTimeToInput.val(defaultTimeTo);
      eventRequestedByInput.val('');
      eventStatusInput.val('');

      formSection.show();
      detailSection.hide();

      modal.modal('show');
    });

    // Submit form (Create or Update)
    form.on('submit', function (e) {
      e.preventDefault();

      if (!validationForm.checkValidity()) {
        e.stopPropagation();
        validationForm.classList.add('was-validated');
        return;
      }

      const data = {
        id: currentEvent ? currentEvent.id : null,
        title: eventTitleInput.val(),
        category: eventCategoryInput.val(),
        room: currentRoom ? currentRoom.name : eventRoomInput.val(), // Use selected room name
        room_key: currentRoom ? currentRoom.key : '', // Pass room key
        room_capacity: currentRoom ? currentRoom.capacity : '', // Pass room capacity
        room_location: currentRoom ? currentRoom.location : '', // Pass room location
        date: currentEvent ? currentEvent.start.toISOString().slice(0, 10) : selectedDate.toISOString().slice(0, 10),
        time_from: eventTimeFromInput.val(),
        time_to: eventTimeToInput.val(),
        remark: eventRemarksInput.val(),
        requested_by: eventRequestedByInput.val(),
        status: eventStatusInput.val(),
      };

      $.ajax({
        url: '/booking_room/store',
        method: 'POST',
        data: data,
        success: function (res) {
          if (res.success) {
            modal.modal('hide');
            calendar.refetchEvents();
            currentEvent = null;
            selectedDate = null;
            currentRoom = null;
          } else {
            alert('Failed to save event.');
          }
        },
        error: function (xhr) {
          alert('Error saving event: ' + xhr.responseText);
        }
      });
    });

    // Delete event
    $('#btn-delete-event').on('click', function () {
      if (!currentEvent) return;

      if (!confirm('Are you sure you want to delete this event?')) return;

      $.ajax({
        url: `/booking_room/${currentEvent.id}`,
        method: 'DELETE',
        success: function (res) {
          if (res.success) {
            modal.modal('hide');
            calendar.refetchEvents();
            currentEvent = null;
            selectedDate = null;
          } else {
            alert('Failed to delete event.');
          }
        },
        error: function (xhr) {
          alert('Error deleting event: ' + xhr.responseText);
        }
      });
    });
  }

  $(document).ready(function () {
    initCalendar();
  });

})(window.jQuery);