<script>
$(document).ready(function() {
  // Initialize date/time picker for new appointment
  $('#appointment_date').val(getCurrentDateTime());
  
  // Handle duration change for new appointment
  $('#duration').on('change', function() {
    const selectedValue = $(this).val();
    
    if (selectedValue === 'custom') {
      $('.custom-duration-container').removeClass('d-none');
    } else {
      $('.custom-duration-container').addClass('d-none');
    }
  });
  
  // Handle duration change for edit appointment
  $('#edit_duration').on('change', function() {
    const selectedValue = $(this).val();
    
    if (selectedValue === 'custom') {
      $('.edit-custom-duration-container').removeClass('d-none');
    } else {
      $('.edit-custom-duration-container').addClass('d-none');
    }
  });
  
  // Handle edit appointment modal
  $('#editAppointmentModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const appointmentId = button.data('appointment-id');
    const appointmentDate = button.data('appointment-date');
    const duration = button.data('duration');
    const notes = button.data('notes');
    
    $('#edit_appointment_id').val(appointmentId);
    $('#edit_appointment_date').val(appointmentDate);
    
    // Set duration
    if (duration === 15 || duration === 30 || duration === 45 || duration === 60 || duration === 90 || duration === 120) {
      $('#edit_duration').val(duration);
      $('.edit-custom-duration-container').addClass('d-none');
    } else {
      $('#edit_duration').val('custom');
      $('#edit_custom_duration').val(duration);
      $('.edit-custom-duration-container').removeClass('d-none');
    }
    
    $('#edit_notes').val(notes);
  });
  
  // Handle update status modal
  $('#updateAppointmentStatusModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const appointmentId = button.data('appointment-id');
    const currentStatus = button.data('current-status');
    
    $('#status_appointment_id').val(appointmentId);
    $('#appointment_status').val(currentStatus);
  });
  
  // Handle view notes modal
  $('#viewAppointmentNotesModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const notes = button.data('notes');
    
    if (notes && notes.trim() !== '') {
      $('#appointment_notes_content').html(notes.replace(/\n/g, '<br>'));
    } else {
      $('#appointment_notes_content').html('Aucune note disponible pour ce rendez-vous.');
    }
  });
  
  // Handle delete appointment modal
  $('#deleteAppointmentModal').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const appointmentId = button.data('appointment-id');
    
    $('#delete_appointment_id').val(appointmentId);
  });
  
  // Helper function to get current date and time in the format required by datetime-local input
  function getCurrentDateTime() {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30); // Set default time to 30 minutes from now
    
    // Format date as YYYY-MM-DDThh:mm
    const year = now.getFullYear();
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const day = now.getDate().toString().padStart(2, '0');
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }
});
</script>
