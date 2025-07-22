<script>
$(document).ready(function() {
  // Edit Appointment Modal Functionality
  $('.edit-appointment-btn').click(function() {
    const appointmentId = $(this).data('appointment-id');
    const patientId = $(this).data('patient-id');
    const date = $(this).data('date');
    const time = $(this).data('time');
    const duration = $(this).data('duration');
    const type = $(this).data('type');
    
    $('#editAppointmentModal #edit_appointment_id').val(appointmentId);
    // If the patient_id field exists in the form
    if ($('#editAppointmentModal input[name="patient_id"]').length) {
      $('#editAppointmentModal input[name="patient_id"]').val(patientId);
    }
    
    // Combine date and time for the datetime-local input
    const dateTime = date + 'T' + time;
    $('#editAppointmentModal #edit_appointment_date').val(dateTime);
    $('#editAppointmentModal #edit_duration').val(duration);
    
    // Notes might not be passed in the data attributes, so we don't set it here
  });

  // Update Status Modal Functionality
  $('.update-status-btn').click(function() {
    const appointmentId = $(this).data('appointment-id');
    const currentStatus = $(this).data('current-status');
    
    $('#updateAppointmentStatusModal #status_appointment_id').val(appointmentId);
    
    // Pre-select the current status
    $('#updateAppointmentStatusModal #appointment_status').val(currentStatus);
  });

  // View Notes Modal Functionality
  $('.view-notes-btn').click(function() {
    const appointmentId = $(this).data('appointment-id');
    const notes = $(this).data('notes');
    
    // Set the notes content in the modal
    $('#viewAppointmentNotesModal #appointment_notes_content').html(notes);
  });

  // Let's check the actual forms in the modals are properly identified
  const formsMapping = {
    'editAppointmentModal': 'form[action="includes/appointments/update-appointment.php"]',
    'updateAppointmentStatusModal': 'form[action="includes/appointments/update-status.php"]',
    'deleteAppointmentModal': 'form:has(input[name="appointment_id"])'  
  };

  // Log any potential data transfer issues for troubleshooting
  $('.update-status-btn, .edit-appointment-btn, .delete-appointment-btn').on('click', function() {
    const btnType = $(this).hasClass('update-status-btn') ? 'update-status' : 
                   $(this).hasClass('edit-appointment-btn') ? 'edit-appointment' : 'delete-appointment';
    const appointmentId = $(this).data('appointment-id');
    console.log(`Button clicked: ${btnType}, Appointment ID: ${appointmentId}`);
    
    // For Edit Appointment, ensure we're setting all form values properly
    if (btnType === 'edit-appointment') {
      // Additional validation to make sure data is properly transferred
      setTimeout(function() {
        console.log('Edit form values:');
        console.log('- ID:', $('#editAppointmentModal #edit_appointment_id').val());
        console.log('- Date:', $('#editAppointmentModal #edit_appointment_date').val());
        console.log('- Duration:', $('#editAppointmentModal #edit_duration').val());
      }, 100);
    }
  });

  // Make sure forms are found and data transfer is working
  $('[data-bs-toggle="modal"]').on('shown.bs.modal', function(e) {
    const modalId = $(e.target).attr('id');
    console.log(`Modal shown: ${modalId}`);
    
    // For debugging purposes, log the form fields
    if (modalId) {
      const formSelector = formsMapping[modalId];
      const $form = $(e.target).find('form');
      console.log(`Modal form found: ${$form.length > 0}`);
      if ($form.length > 0) {
        console.log(`Appointment ID in form: ${$form.find('input[name="appointment_id"]').val()}`);
      }
    }
  });
});
</script>
