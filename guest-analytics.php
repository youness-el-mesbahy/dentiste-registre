<?php
session_start();
ob_start();
$title = "Analyse des Activités";
include "connection.php";
include "includes/auth/auth.php";

// Require dentiste role to access this page
requireRole('dentiste');

// Check if a specific guest ID or user ID is provided for detailed view
$guestDetail = isset($_GET['guest_id']) ? $_GET['guest_id'] : null;
$userDetail = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Get total guest count and last activity
$totalGuestsQuery = "SELECT COUNT(DISTINCT id) as total FROM guests";
$totalGuestsResult = mysqli_query($conn, $totalGuestsQuery);
$totalGuests = mysqli_fetch_assoc($totalGuestsResult)['total'];

// Get total users with activities
$totalActiveUsersQuery = "SELECT COUNT(DISTINCT user_id) as total FROM guest_activities WHERE user_id IS NOT NULL";
$totalActiveUsersResult = mysqli_query($conn, $totalActiveUsersQuery);
$totalActiveUsers = mysqli_fetch_assoc($totalActiveUsersResult)['total'];

$totalActivitiesQuery = "SELECT COUNT(*) as total FROM guest_activities";
$totalActivitiesResult = mysqli_query($conn, $totalActivitiesQuery);
$totalActivities = mysqli_fetch_assoc($totalActivitiesResult)['total'];

$lastActivityQuery = "SELECT MAX(created_at) as last_activity FROM guest_activities";
$lastActivityResult = mysqli_query($conn, $lastActivityQuery);
$lastActivityRow = mysqli_fetch_assoc($lastActivityResult);
$lastActivity = $lastActivityRow['last_activity'] ? date('d/m/Y H:i:s', strtotime($lastActivityRow['last_activity'])) : 'Aucune';

// If a specific user is requested, get their details
if ($userDetail) {
    $userQuery = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userDetail);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $user = $userResult->fetch_assoc();
    
    // Get user activities
    $activitiesQuery = "SELECT * FROM guest_activities WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($activitiesQuery);
    $stmt->bind_param("i", $userDetail);
    $stmt->execute();
    $activitiesResult = $stmt->get_result();
}
// If a specific guest is requested, get their details
elseif ($guestDetail) {
    $guestQuery = "SELECT * FROM guests WHERE id = ?";
    $stmt = $conn->prepare($guestQuery);
    $stmt->bind_param("s", $guestDetail);
    $stmt->execute();
    $guestResult = $stmt->get_result();
    $guest = $guestResult->fetch_assoc();
    
    // Get guest activities
    $activitiesQuery = "SELECT * FROM guest_activities WHERE guest_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($activitiesQuery);
    $stmt->bind_param("s", $guestDetail);
    $stmt->execute();
    $activitiesResult = $stmt->get_result();
} else {
    // Get all guests with their latest activity
    $guestsQuery = "SELECT g.*, 
                     (SELECT MAX(ga.created_at) FROM guest_activities ga WHERE ga.guest_id = g.id) as last_activity,
                     (SELECT COUNT(*) FROM guest_activities ga WHERE ga.guest_id = g.id) as activity_count 
                    FROM guests g 
                    ORDER BY last_activity DESC";
    $guestsResult = mysqli_query($conn, $guestsQuery);
    
    // Get all users with activities
    $usersQuery = "SELECT u.id, u.email, u.nom, u.prenom, u.role,
                    (SELECT MAX(ga.created_at) FROM guest_activities ga WHERE ga.user_id = u.id) as last_activity,
                    (SELECT COUNT(*) FROM guest_activities ga WHERE ga.user_id = u.id) as activity_count
                   FROM users u
                   WHERE u.id IN (SELECT DISTINCT user_id FROM guest_activities WHERE user_id IS NOT NULL)
                   ORDER BY last_activity DESC";
    $usersResult = mysqli_query($conn, $usersQuery);
}
?>

<!-- Main content container -->
<div class="container-fluid">

  <?php if ($userDetail): ?>
    <!-- User Detail View -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Détails de l'Utilisateur #<?= $userDetail ?></h5>
            <a href="guest-analytics.php" class="btn btn-sm btn-outline-primary">
              <i class="ri-arrow-left-line me-1"></i> Retour à la liste
            </a>
          </div>
          <div class="card-body">
            <?php if ($user): ?>
              <div class="row">
                <div class="col-md-6">
                  <table class="table">
                    <tbody>
                      <tr>
                        <th width="150">ID</th>
                        <td><?= $user['id'] ?></td>
                      </tr>
                      <tr>
                        <th>Email</th>
                        <td><?= $user['email'] ?></td>
                      </tr>
                      <tr>
                        <th>Nom</th>
                        <td><?= $user['nom'] . ' ' . $user['prenom'] ?></td>
                      </tr>
                      <tr>
                        <th>Rôle</th>
                        <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>"><?= ucfirst($user['role']) ?></span></td>
                      </tr>
                      <tr>
                        <th>Activités</th>
                        <td><?= mysqli_num_rows($activitiesResult) ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Activity History -->
              <h6 class="mt-4 mb-3">Historique des Activités</h6>
              
              <div class="table-responsive">
                <table class="table truncate m-0 align-middle" id="activityTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Action</th>
                      <th>Page</th>
                      <th>Adresse IP</th>
                      <th>Agent Utilisateur</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (mysqli_num_rows($activitiesResult) > 0): ?>
                      <?php while ($activity = mysqli_fetch_assoc($activitiesResult)): ?>
                        <tr>
                          <td><?= $activity['id'] ?></td>
                          <td>
                            <?php
                            $actionClass = '';
                            switch ($activity['action']) {
                              case 'page_view':
                                $actionClass = 'badge-info';
                                break;
                              case 'login':
                                $actionClass = 'badge-success';
                                break;
                              case 'logout':
                                $actionClass = 'badge-warning';
                                break;
                              default:
                                $actionClass = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $actionClass ?>"><?= $activity['action'] ?></span>
                          </td>
                          <td><?= $activity['page_url'] ?></td>
                          <td><?= $activity['ip_address'] ?></td>
                          <td>
                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($activity['user_agent']) ?>">
                              <?= htmlspecialchars($activity['user_agent']) ?>
                            </span>
                          </td>
                          <td><?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?></td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center">Aucune activité trouvée</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-warning">
                Utilisateur non trouvé.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php elseif ($guestDetail): ?>
    <!-- Guest Detail View -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Détails du Visiteur #<?= $guestDetail ?></h5>
            <a href="guest-analytics.php" class="btn btn-sm btn-outline-primary">
              <i class="ri-arrow-left-line me-1"></i> Retour à la liste
            </a>
          </div>
          <div class="card-body">
            <?php if ($guest): ?>
              <div class="row">
                <div class="col-md-6">
                  <table class="table">
                    <tbody>
                      <tr>
                        <th width="150">ID</th>
                        <td><?= $guest['id'] ?></td>
                      </tr>
                      <tr>
                        <th>UUID</th>
                        <td><code><?= $guest['uuid'] ?></code></td>
                      </tr>
                      <tr>
                        <th>Date de création</th>
                        <td><?= date('d/m/Y H:i:s', strtotime($guest['created_at'])) ?></td>
                      </tr>
                      <tr>
                        <th>Activités</th>
                        <td><?= mysqli_num_rows($activitiesResult) ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Activity History -->
              <h6 class="mt-4 mb-3">Historique des Activités</h6>
              
              <div class="table-responsive">
                <table class="table truncate m-0 align-middle" id="activityTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Action</th>
                      <th>Page</th>
                      <th>Adresse IP</th>
                      <th>Agent Utilisateur</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (mysqli_num_rows($activitiesResult) > 0): ?>
                      <?php while ($activity = mysqli_fetch_assoc($activitiesResult)): ?>
                        <tr>
                          <td><?= $activity['id'] ?></td>
                          <td>
                            <?php
                            $actionClass = '';
                            switch ($activity['action']) {
                              case 'page_view':
                                $actionClass = 'badge-info';
                                break;
                              case 'login':
                                $actionClass = 'badge-success';
                                break;
                              case 'logout':
                                $actionClass = 'badge-warning';
                                break;
                              default:
                                $actionClass = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $actionClass ?>"><?= $activity['action'] ?></span>
                          </td>
                          <td><?= $activity['page_url'] ?></td>
                          <td><?= $activity['ip_address'] ?></td>
                          <td>
                            <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($activity['user_agent']) ?>">
                              <?= htmlspecialchars($activity['user_agent']) ?>
                            </span>
                          </td>
                          <td><?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?></td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center">Aucune activité trouvée</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="alert alert-warning">
                Visiteur non trouvé.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <!-- Dashboard Overview -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-box icon-box-primary rounded-5 me-3">
                <i class="ri-user-line"></i>
              </div>
              <div>
                <div class="small text-muted">Total des Visiteurs</div>
                <div class="h4 mb-0"><?= number_format($totalGuests) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-box icon-box-danger rounded-5 me-3">
                <i class="ri-user-settings-line"></i>
              </div>
              <div>
                <div class="small text-muted">Utilisateurs Actifs</div>
                <div class="h4 mb-0"><?= number_format($totalActiveUsers) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-box icon-box-success rounded-5 me-3">
                <i class="ri-bar-chart-line"></i>
              </div>
              <div>
                <div class="small text-muted">Total des Activités</div>
                <div class="h4 mb-0"><?= number_format($totalActivities) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="icon-box icon-box-info rounded-5 me-3">
                <i class="ri-time-line"></i>
              </div>
              <div>
                <div class="small text-muted">Dernière Activité</div>
                <div class="h4 mb-0"><?= $lastActivity ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User and Guest Lists -->
    <div class="row">
      <!-- User Activities -->
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Utilisateurs avec Activités</h5>
            <ul class="nav nav-tabs card-header-tabs" id="activityTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Utilisateurs</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="guests-tab" data-bs-toggle="tab" data-bs-target="#guests" type="button" role="tab" aria-controls="guests" aria-selected="false">Visiteurs</button>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content" id="activityTabContent">
              <!-- Users Tab -->
              <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="table-responsive">
                  <table class="table truncate m-0 align-middle" id="userTable">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Dernière Activité</th>
                        <th>Nombre d'Activités</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (mysqli_num_rows($usersResult) > 0): ?>
                        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                          <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>"><?= ucfirst($user['role']) ?></span></td>
                            <td><?= $user['last_activity'] ? date('d/m/Y H:i:s', strtotime($user['last_activity'])) : 'N/A' ?></td>
                            <td><?= $user['activity_count'] ?></td>
                            <td>
                              <a href="guest-analytics.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="ri-eye-line"></i> Détails
                              </a>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="7" class="text-center">Aucun utilisateur avec activités trouvé</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              
              <!-- Guests Tab -->
              <div class="tab-pane fade" id="guests" role="tabpanel" aria-labelledby="guests-tab">
                <div class="table-responsive">
                  <table class="table truncate m-0 align-middle" id="guestTable">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>UUID</th>
                        <th>Date de Création</th>
                        <th>Dernière Activité</th>
                        <th>Nombre d'Activités</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (mysqli_num_rows($guestsResult) > 0): ?>
                        <?php while ($guest = mysqli_fetch_assoc($guestsResult)): ?>
                          <tr>
                            <td><?= $guest['id'] ?></td>
                            <td><code><?= substr($guest['uuid'], 0, 8) ?>...</code></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($guest['created_at'])) ?></td>
                            <td><?= $guest['last_activity'] ? date('d/m/Y H:i:s', strtotime($guest['last_activity'])) : 'N/A' ?></td>
                            <td><?= $guest['activity_count'] ?></td>
                            <td>
                              <a href="guest-analytics.php?guest_id=<?= $guest['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="ri-eye-line"></i> Détails
                              </a>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center">Aucun visiteur trouvé</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php
// Include CSS and JS files
$css=file_get_contents("includes/guest-analytics/css.php");
$js=file_get_contents("includes/guest-analytics/js.php");

$css .= "<link rel='stylesheet' href='assets/vendor/datatables/dataTables.bs5.css'>
      <link rel='stylesheet' href='assets/vendor/datatables/dataTables.bs5-custom.css'>
      <link rel='stylesheet' href='assets/vendor/datatables/buttons/dataTables.bs5-custom.css'>";

$js .= "<script src='assets/vendor/datatables/dataTables.min.js'></script>
      <script src='assets/vendor/datatables/dataTables.bootstrap.min.js'></script>
      <script src='assets/vendor/datatables/custom/custom-datatables.js'></script>
      <script>
        $(document).ready(function() {
          // Disable console errors and alerts
          window.onerror = function(message, source, lineno, colno, error) {
            // Prevent the browser from displaying the error
            return true;
          };
          
          // Suppress console errors and alerts
          console.error = function() {};
          console.warn = function() {};
          
          // Disable alert function
          window.alert = function() {};
          
          // Initialize DataTables
          $('#userTable').DataTable({
            lengthMenu: [
              [10, 25, 50],
              [10, 25, 50, 'Tous'],
            ],
            language: {
              lengthMenu: 'Afficher _MENU_ entrées par page',
              search: 'Rechercher:',
              info: 'Page _PAGE_ sur _PAGES_',
              paginate: {
                previous: 'Précédent',
                next: 'Suivant'
              }
            },
            order: [[4, 'desc']]
          });
          
          $('#guestTable').DataTable({
            lengthMenu: [
              [10, 25, 50],
              [10, 25, 50, 'Tous'],
            ],
            language: {
              lengthMenu: 'Afficher _MENU_ entrées par page',
              search: 'Rechercher:',
              info: 'Page _PAGE_ sur _PAGES_',
              paginate: {
                previous: 'Précédent',
                next: 'Suivant'
              }
            },
            order: [[3, 'desc']]
          });
          
          $('#activityTable').DataTable({
            lengthMenu: [
              [10, 25, 50],
              [10, 25, 50, 'Tous'],
            ],
            language: {
              lengthMenu: 'Afficher _MENU_ entrées par page',
              search: 'Rechercher:',
              info: 'Page _PAGE_ sur _PAGES_',
              paginate: {
                previous: 'Précédent',
                next: 'Suivant'
              }
            },
            order: [[5, 'desc']]
          });
        });
      </script>";

$content = ob_get_clean();
include "layout.php";
?>
