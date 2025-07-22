<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include guest tracking functionality
require_once 'includes/user-analytics/guest_tracker.php';

// Include authentication functions
require_once 'includes/auth/auth.php';

// Get or create guest ID
$guestId = getGuestId();

// Track page view
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $title ?? 'Unknown Page';
trackGuestActivity($guestId, 'page_view', $currentPage);
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>

    <!-- Meta -->
    <meta name="description" content="Marketplace for Bootstrap Admin Dashboards">
    <meta property="og:title" content="Admin Templates - Dashboard Templates">
    <meta property="og:description" content="Marketplace for Bootstrap Admin Dashboards">
    <meta property="og:type" content="Website">
    <link rel="shortcut icon" href="assets/images/logo.webp">

    <!-- Icons -->
    <?= $css?>
  </head>

  <body>

    <!-- Page wrapper starts -->
    <div class="page-wrapper">


      <!-- Main container starts -->
      <div class="main-container">

        <?php include 'sidebar.php'; ?>

        <!-- App container starts -->
        <div class="app-container">
          <!-- Header starts -->
          <header class="header-wrapper">
            <div class="header-container">
              <div class="header-left">
              </div>
              <div class="header-right d-flex align-items-center">
                <?php if (isLoggedIn()): ?>
                  <div class="dropdown ms-auto">
                    <a href="#" class="dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <div class="d-flex align-items-center">
                        <div class="icon-box sm bg-primary rounded-5 me-2">
                          <i class="ri-user-line"></i>
                        </div>
                        <span class="d-none d-md-inline-block fw-medium">
                          <?php
                          if (!empty($_SESSION['user_nom']) && !empty($_SESSION['user_prenom'])) {
                            echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']);
                          } else {
                            echo htmlspecialchars($_SESSION['user_email']);
                          }
                          ?>
                        </span>
                      </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                      <li>
                        <div class="dropdown-item-text">
                          <div class="d-flex align-items-center mb-2">
                            <div class="icon-box bg-primary rounded-circle me-2">
                              <i class="ri-user-line"></i>
                            </div>
                            <div>
                              <div class="fw-medium">
                                <?php
                                if (!empty($_SESSION['user_nom']) && !empty($_SESSION['user_prenom'])) {
                                  echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']);
                                } else {
                                  echo htmlspecialchars($_SESSION['user_email']);
                                }
                                ?>
                              </div>
                              <div class="text-muted small"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
                            </div>
                          </div>
                          <span class="badge bg-<?= $_SESSION['user_role'] === 'admin' ? 'danger' : 'primary' ?>-subtle text-<?= $_SESSION['user_role'] === 'admin' ? 'danger' : 'primary' ?> small">
                            <?= ucfirst($_SESSION['user_role']) ?>
                          </span>
                        </div>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item" href="includes/auth/logout.php"><i class="ri-logout-box-line me-2"></i>Déconnexion</a></li>
                    </ul>
                  </div>
                <?php else: ?>
                  <a href="login.php" class="btn btn-sm btn-primary ms-auto"><i class="ri-login-box-line me-1"></i>Connexion</a>
                <?php endif; ?>
              </div>
            </div>
          </header>
          <!-- Header ends -->
          
          <div class="app-body">
            <?= $content ?>
        </div>
        </div>
        <!-- App container ends -->

      </div>
      <!-- Main container ends -->

    </div>
    <!-- Page wrapper ends -->
    <?= $js ?>
  </body>

</html>