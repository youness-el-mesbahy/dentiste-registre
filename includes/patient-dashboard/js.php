    <!-- *************
			************ JavaScript Files *************
		************* -->
    <!-- Required jQuery first, then Bootstrap Bundle JS -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/moment.min.js"></script>

    <!-- *************
			************ Vendor Js Files *************
		************* -->

    <!-- Overlay Scroll JS -->
    <script src="assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
    <script src="assets/vendor/overlay-scroll/custom-scrollbar.js"></script>

    <!-- Apex js -->
    <script src="assets/vendor/apex/apexcharts.min.js"></script>
    <script src="assets/vendor/apex/custom/patients/sparklines.js"></script>
    <script src="assets/vendor/apex/custom/patients/insuranceClaims.js"></script>
    <script src="assets/vendor/apex/custom/patients/medicalExpenses.js"></script>
    <script src="assets/vendor/apex/custom/patients/healthActivity.js"></script>

    <!-- Custom JS files -->
    <script src="assets/js/custom.js"></script>
    
    <!-- Patient Dashboard Custom JS -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Set document ID in delete document modal when button is clicked
        const deleteDocModal = document.getElementById('delRow');
        if (deleteDocModal) {
          deleteDocModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const docId = button.getAttribute('data-doc-id');
            document.getElementById('docIdToDelete').value = docId;
          });
        }
        
        // Set consultation ID in delete consultation modal when button is clicked
        const deleteConsultModal = document.getElementById('deleteConsultationModal');
        if (deleteConsultModal) {
          deleteConsultModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const consultId = button.getAttribute('data-consult-id');
            document.getElementById('consultIdToDelete').value = consultId;
          });
        }
        
        // Set consultation data in edit consultation modal when button is clicked
        const editConsultModal = document.getElementById('editConsultationModal');
        if (editConsultModal) {
          editConsultModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const consultId = button.getAttribute('data-consult-id');
            const date = button.getAttribute('data-date');
            const motif = button.getAttribute('data-motif');
            const diagnostic = button.getAttribute('data-diagnostic');
            const traitement = button.getAttribute('data-traitement');
            const cout = button.getAttribute('data-cout');
            
            // Format date for datetime-local input
            const dateObj = new Date(date);
            const formattedDate = dateObj.toISOString().slice(0, 16);
            
            document.getElementById('edit_consult_id').value = consultId;
            document.getElementById('edit_date_consultation').value = formattedDate;
            document.getElementById('edit_motif').value = motif;
            document.getElementById('edit_diagnostic').value = diagnostic;
            document.getElementById('edit_traitement').value = traitement;
            document.getElementById('edit_cout').value = cout;
          });
        }
      });
    </script>