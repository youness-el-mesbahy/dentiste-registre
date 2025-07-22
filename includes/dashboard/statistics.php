<?php
/**
 * Dashboard statistics queries
 * This file handles all database queries for dashboard statistics
 */

// Get current user ID
$currentUserId = $_SESSION['user_id'] ?? 0;
// Check if user is admin
$isAdmin = hasRole('admin');

// Get current date and time
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m');
$currentYear = date('Y');

// PATIENT STATISTICS
// Total patients
if ($isAdmin) {
    $patientCountQuery = "SELECT COUNT(*) as total FROM patients";
} else {
    $patientCountQuery = "SELECT COUNT(*) as total FROM patients WHERE user_id = $currentUserId";
}
$patientCountResult = mysqli_query($conn, $patientCountQuery);
$totalPatients = mysqli_fetch_assoc($patientCountResult)['total'];

// Gender distribution
if ($isAdmin) {
    $genderQuery = "SELECT genre, COUNT(*) as count FROM patients GROUP BY genre";
} else {
    $genderQuery = "SELECT genre, COUNT(*) as count FROM patients WHERE user_id = $currentUserId GROUP BY genre";
}
$genderResult = mysqli_query($conn, $genderQuery);
$maleCount = 0;
$femaleCount = 0;
while ($gender = mysqli_fetch_assoc($genderResult)) {
    if ($gender['genre'] == 'M') {
        $maleCount = $gender['count'];
    } else {
        $femaleCount = $gender['count'];
    }
}

// Age distribution
if ($isAdmin) {
    $ageQuery = "SELECT 
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) < 18 THEN 1 ELSE 0 END) as under_18,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) BETWEEN 18 AND 40 THEN 1 ELSE 0 END) as age_18_40,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) BETWEEN 41 AND 60 THEN 1 ELSE 0 END) as age_41_60,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) > 60 THEN 1 ELSE 0 END) as over_60
                FROM patients";
} else {
    $ageQuery = "SELECT 
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) < 18 THEN 1 ELSE 0 END) as under_18,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) BETWEEN 18 AND 40 THEN 1 ELSE 0 END) as age_18_40,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) BETWEEN 41 AND 60 THEN 1 ELSE 0 END) as age_41_60,
                    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) > 60 THEN 1 ELSE 0 END) as over_60
                FROM patients
                WHERE user_id = $currentUserId";
}
$ageResult = mysqli_query($conn, $ageQuery);
$ageDistribution = mysqli_fetch_assoc($ageResult);

// New patients this month
if ($isAdmin) {
    $newPatientsQuery = "SELECT COUNT(*) as count FROM patients WHERE DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
} else {
    $newPatientsQuery = "SELECT COUNT(*) as count FROM patients WHERE user_id = $currentUserId AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
}
$newPatientsResult = mysqli_query($conn, $newPatientsQuery);
$newPatients = mysqli_fetch_assoc($newPatientsResult)['count'];

// APPOINTMENT STATISTICS
// Today's appointments
if ($isAdmin) {
    $todayAppointmentsQuery = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = '$currentDate'";
} else {
    $todayAppointmentsQuery = "SELECT COUNT(*) as count FROM appointments WHERE user_id = $currentUserId AND DATE(appointment_date) = '$currentDate'";
}
$todayAppointmentsResult = mysqli_query($conn, $todayAppointmentsQuery);
$todayAppointments = mysqli_fetch_assoc($todayAppointmentsResult)['count'];

// Upcoming appointments (next 7 days)
if ($isAdmin) {
    $upcomingAppointmentsQuery = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) BETWEEN '$currentDate' AND DATE_ADD('$currentDate', INTERVAL 7 DAY) AND status = 'scheduled'";
} else {
    $upcomingAppointmentsQuery = "SELECT COUNT(*) as count FROM appointments WHERE user_id = $currentUserId AND DATE(appointment_date) BETWEEN '$currentDate' AND DATE_ADD('$currentDate', INTERVAL 7 DAY) AND status = 'scheduled'";
}
$upcomingAppointmentsResult = mysqli_query($conn, $upcomingAppointmentsQuery);
$upcomingAppointments = mysqli_fetch_assoc($upcomingAppointmentsResult)['count'];

// Appointment status distribution
if ($isAdmin) {
    $statusQuery = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
} else {
    $statusQuery = "SELECT status, COUNT(*) as count FROM appointments WHERE user_id = $currentUserId GROUP BY status";
}
$statusResult = mysqli_query($conn, $statusQuery);
$scheduled = 0;
$completed = 0;
$cancelled = 0;
$noShow = 0;
while ($status = mysqli_fetch_assoc($statusResult)) {
    switch ($status['status']) {
        case 'scheduled':
            $scheduled = $status['count'];
            break;
        case 'completed':
            $completed = $status['count'];
            break;
        case 'cancelled':
            $cancelled = $status['count'];
            break;
        case 'no_show':
            $noShow = $status['count'];
            break;
    }
}
$totalAppointments = $scheduled + $completed + $cancelled + $noShow;
$completionRate = ($totalAppointments > 0) ? round(($completed / $totalAppointments) * 100) : 0;

// FINANCIAL STATISTICS
// Total revenue this month
if ($isAdmin) {
    $revenueQuery = "SELECT SUM(cout) as total FROM consultations WHERE DATE_FORMAT(date_consultation, '%Y-%m') = '$currentMonth'";
} else {
    $revenueQuery = "SELECT SUM(c.cout) as total FROM consultations c INNER JOIN patients p ON c.patient_id = p.id WHERE p.user_id = $currentUserId AND DATE_FORMAT(c.date_consultation, '%Y-%m') = '$currentMonth'";
}
$revenueResult = mysqli_query($conn, $revenueQuery);
$monthlyRevenue = mysqli_fetch_assoc($revenueResult)['total'] ?? 0;

// Average treatment cost
if ($isAdmin) {
    $avgCostQuery = "SELECT AVG(cout) as avg_cost FROM consultations WHERE DATE_FORMAT(date_consultation, '%Y-%m') = '$currentMonth'";
} else {
    $avgCostQuery = "SELECT AVG(c.cout) as avg_cost FROM consultations c INNER JOIN patients p ON c.patient_id = p.id WHERE p.user_id = $currentUserId AND DATE_FORMAT(c.date_consultation, '%Y-%m') = '$currentMonth'";
}
$avgCostResult = mysqli_query($conn, $avgCostQuery);
$avgCost = mysqli_fetch_assoc($avgCostResult)['avg_cost'] ?? 0;

// PRACTICE ACTIVITY
// Recent consultations
if ($isAdmin) {
    $recentConsultationsQuery = "SELECT c.*, p.nom, p.prenom 
                               FROM consultations c 
                               INNER JOIN patients p ON c.patient_id = p.id 
                               ORDER BY c.date_consultation DESC LIMIT 5";
} else {
    $recentConsultationsQuery = "SELECT c.*, p.nom, p.prenom 
                               FROM consultations c 
                               INNER JOIN patients p ON c.patient_id = p.id 
                               WHERE p.user_id = $currentUserId 
                               ORDER BY c.date_consultation DESC LIMIT 5";
}
$recentConsultationsResult = mysqli_query($conn, $recentConsultationsQuery);

// Documents uploaded this month
if ($isAdmin) {
    $documentsQuery = "SELECT COUNT(*) as count FROM patient_documents WHERE DATE_FORMAT(uploaded_at, '%Y-%m') = '$currentMonth'";
} else {
    $documentsQuery = "SELECT COUNT(*) as count FROM patient_documents pd 
                     INNER JOIN patients p ON pd.patient_id = p.id 
                     WHERE p.user_id = $currentUserId AND DATE_FORMAT(pd.uploaded_at, '%Y-%m') = '$currentMonth'";
}
$documentsResult = mysqli_query($conn, $documentsQuery);
$monthlyDocuments = mysqli_fetch_assoc($documentsResult)['count'];

// Today's appointments detail
if ($isAdmin) {
    $todayAppointmentsDetailQuery = "SELECT a.*, p.nom, p.prenom 
                                    FROM appointments a 
                                    INNER JOIN patients p ON a.patient_id = p.id 
                                    WHERE DATE(a.appointment_date) = '$currentDate' 
                                    ORDER BY a.id ASC";
} else {
    $todayAppointmentsDetailQuery = "SELECT a.*, p.nom, p.prenom 
                                    FROM appointments a 
                                    INNER JOIN patients p ON a.patient_id = p.id 
                                    WHERE a.user_id = $currentUserId AND DATE(a.appointment_date) = '$currentDate' 
                                    ORDER BY a.id ASC";
}
$todayAppointmentsDetailResult = mysqli_query($conn, $todayAppointmentsDetailQuery);
?>
