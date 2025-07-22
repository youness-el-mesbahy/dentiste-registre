<!-- Sidebar wrapper starts -->
 <style>
    .sidebar-wrapper{
        top: 0;
    }
 </style>
<?php
    // Get the current page filename
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar" class="sidebar-wrapper">

    <!-- Sidebar logo starts -->
    <div class="sidebar-profile d-flex align-items-center justify-content-center p-3">
        <div class="text-center">
            <div class="icon-box xl bg-primary rounded-circle mb-2 mx-auto">
                <img src="assets/images/dent.png" alt="Dentiste Registre Logo" class="img-fluid" style="width: 50%; height: 50%; object-fit: contain; margin: 0 auto;">
            </div>
            <h5 class="mb-1 profile-name text-nowrap text-truncate">Dentiste Registre</h5>
            <p class="m-0 small profile-name text-nowrap text-truncate">Patient Management</p>
        </div>
    </div>
    <!-- Sidebar logo ends -->

    <!-- Sidebar menu starts -->
    <div class="sidebarMenuScroll">
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active current-page' : ''; ?>">
                <a href="dashboard.php">
                    <i class="ri-home-line"></i>
                    <span class="menu-text">Tableau de Bord</span>
                </a>
            </li>
            <li class="<?php echo ($currentPage == 'patients-list.php') ? 'active current-page' : ''; ?>">
                <a href="patients-list.php">
                    <i class="ri-user-search-line"></i>
                    <span class="menu-text">Patients</span>
                </a>
            </li>
            <li class="<?php echo ($currentPage == 'add-patient.php') ? 'active current-page' : ''; ?>">
                <a href="add-patient.php">
                    <i class="ri-user-add-line"></i>
                    <span class="menu-text">Ajouter Patient</span>
                </a>
            </li>
            <li class="<?php echo ($currentPage == 'appointments-list.php') ? 'active current-page' : ''; ?>">
                <a href="appointments-list.php">
                    <i class="ri-calendar-line"></i>
                    <span class="menu-text">Les Rendez-vous</span>
                </a>
            </li>
            <?php if (isLoggedIn() && hasRole('admin')): ?>
            <li class="<?php echo ($currentPage == 'guest-analytics.php') ? 'active current-page' : ''; ?>">
                <a href="guest-analytics.php">
                    <i class="ri-line-chart-line"></i>
                    <span class="menu-text">Analyse des Visiteurs</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (isLoggedIn() && hasRole('admin')): ?>
            <li class="<?php echo ($currentPage == 'user-management.php') ? 'active current-page' : ''; ?>">
                <a href="user-management.php">
                    <i class="ri-user-settings-line"></i>
                    <span class="menu-text">Gestion des Utilisateurs</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="<?php echo ($currentPage == 'export-data.php') ? 'active current-page' : ''; ?>">
                <a href="export-data.php">
                    <i class="ri-download-cloud-line"></i>
                    <span class="menu-text">Sauvegarder</span>
                </a>
            </li>

        </ul>
    </div>
    <!-- Sidebar menu ends -->

</nav>
<!-- Sidebar wrapper ends -->