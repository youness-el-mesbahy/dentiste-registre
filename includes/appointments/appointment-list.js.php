<?php
/**
 * JavaScript for appointments list page
 */
?>
<script>
  $(document).ready(function() {
    // Get the DataTable instance that was already initialized in custom-datatables.js
    var appointmentsTable;
    
    // Check if DataTable is already initialized (which it should be from custom-datatables.js)
    if ($.fn.dataTable.isDataTable('#basicExample')) {
      appointmentsTable = $('#basicExample').DataTable();
    } else {
      // Initialize only if not already initialized (shouldn't happen, but as a fallback)
      appointmentsTable = $('#basicExample').DataTable({
        responsive: true,
        paging: true,
        info: true,
        ordering: true,
        lengthMenu: [
          [10, 25, 50, -1],
          [10, 25, 50, "Tous"]
        ],
        language: {
          "emptyTable":     "Aucune donnée disponible",
          "info":           "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
          "infoEmpty":      "Affichage de 0 à 0 sur 0 entrée",
          "infoFiltered":   "(filtré sur _MAX_ entrées au total)",
          "lengthMenu":     "Afficher _MENU_ entrées",
          "loadingRecords": "Chargement...",
          "processing":     "Traitement...",
          "search":         "Rechercher :",
          "zeroRecords":    "Aucun résultat trouvé",
          "paginate": {
            "first":      "Premier",
            "last":       "Dernier",
            "next":       "Suivant",
            "previous":   "Précédent"
          }
        }
      });
    }

    // Clear any existing custom filters for this table
    // First make a copy of the current search functions
    var existingSearchFunctions = $.fn.dataTable.ext.search.slice();
    
    // Clear the array
    $.fn.dataTable.ext.search.length = 0;
    
    // Re-add only the filters that aren't for our table
    for (var i = 0; i < existingSearchFunctions.length; i++) {
      var filterFn = existingSearchFunctions[i];
      if (!filterFn._appointmentsFilter) {
        $.fn.dataTable.ext.search.push(filterFn);
      }
    }
    
    // Add patient name search field (similar to CIN search in patients-list.php)
    $('.dataTables_filter').append('&nbsp;&nbsp;<label>Patient:&nbsp;</label><input type="text" id="patientSearch" class="form-control form-control-sm d-inline-block" style="width: 150px; margin-left: 5px;">');
    
    // Define our custom filter function
    function appointmentsFilter(settings, data, dataIndex) {
      // Only apply to appointments table
      if (settings.nTable.id !== 'basicExample') {
        return true;
      }
      
      // Get filter values
      var statusFilter = $('#statusFilter').val();
      var dateFilter = $('#dateFilter').val();
      var patientValue = $('#patientSearch').val().toLowerCase();
      
      // If no filters are active, show all rows
      if (!statusFilter && !dateFilter && !patientValue) {
        return true;
      }
      
      // Get row data
      var $row = $(settings.aoData[dataIndex].nTr);
      var rowStatus = $row.data('status');
      var rowDate = $row.data('date');
      
      // Get patient name from the row data (column index 1)
      var patientName = data[1].toLowerCase();
      
      // Apply status filter if set
      if (statusFilter && rowStatus !== statusFilter) {
        return false;
      }
      
      // Apply date filter if set
      if (dateFilter && rowDate !== dateFilter) {
        return false;
      }
      
      // Apply patient name filter if set
      if (patientValue && !patientName.includes(patientValue)) {
        return false;
      }
      
      // If we got here, the row passes all active filters
      return true;
    }
    
    // Tag our function so we can identify it later
    appointmentsFilter._appointmentsFilter = true;
    
    // Add our custom filter
    $.fn.dataTable.ext.search.push(appointmentsFilter);
    
    // Event listeners for our filter controls
    $('#statusFilter, #dateFilter').on('change', function() {
      appointmentsTable.draw();
    });
    
    // Add custom search for patient name with debounce
    var patientSearchTimeout;
    $('#patientSearch').on('keyup', function() {
      clearTimeout(patientSearchTimeout);
      patientSearchTimeout = setTimeout(function() {
        appointmentsTable.draw();
      }, 300);
    });
    
    // Reset filters button
    $('#resetFilters').on('click', function() {
      $('#statusFilter').val('');
      $('#dateFilter').val('');
      $('#patientSearch').val('');
      appointmentsTable.draw();
    });
    
    // Update Status Modal
    $('#updateStatusModal').on('show.bs.modal', function(event) {
      const button = $(event.relatedTarget);
      const appointmentId = button.data('appointment-id');
      const currentStatus = button.data('current-status');
      
      $('#appointmentId').val(appointmentId);
      $('#statusSelect').val(currentStatus);
    });
    
    // View Notes Modal
    $('.view-notes').on('click', function() {
      const notes = $(this).data('notes');
      const patient = $(this).data('patient');
      
      $('#notesPatientName').text(patient);
      $('#notesContent').text(notes || 'Aucune note disponible.');
    });
    
    // Edit Appointment Modal
    $('.edit-appointment-btn').on('click', function() {
      const appointmentId = $(this).data('appointment-id');
      const patientName = $(this).data('patient-name');
      const appointmentDate = $(this).data('appointment-date');
      const duration = $(this).data('duration');
      const notes = $(this).data('notes');
      
      $('#editAppointmentId').val(appointmentId);
      $('#editPatientName').val(patientName);
      $('#editAppointmentDate').val(appointmentDate);
      
      // Set duration dropdown or custom value
      if ([15, 30, 45, 60, 90, 120].includes(parseInt(duration))) {
        $('#editAppointmentDuration').val(duration);
        $('#editCustomDurationContainer').hide();
      } else {
        $('#editAppointmentDuration').val('custom');
        $('#editCustomDuration').val(duration);
        $('#editCustomDurationContainer').show();
      }
      
      $('#editAppointmentNotes').val(notes);
    });
    
    // Handle custom duration selection
    $('#editAppointmentDuration').on('change', function() {
      if ($(this).val() === 'custom') {
        $('#editCustomDurationContainer').show();
      } else {
        $('#editCustomDurationContainer').hide();
      }
    });
    
    // Delete Appointment Modal
    $('#deleteAppointmentModal').on('show.bs.modal', function(event) {
      const button = $(event.relatedTarget);
      const appointmentId = button.data('appointment-id');
      const patient = button.data('patient');
      
      $('#deleteAppointmentId').val(appointmentId);
      $('#deletePatientName').text(patient);
    });
  });
</script>
