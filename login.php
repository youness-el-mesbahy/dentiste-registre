<?php
session_start();
include "connection.php";
include "includes/utils/error_handler.php";
include "includes/utils/validation.php";
include "includes/utils/csrf.php";

// Initialize CSRF protection
initCSRF();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Process login form submission
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!validateCSRFToken()) {
        logError("CSRF token validation failed in login.php", __FILE__, __LINE__, 'security');
        $error = "Session expirée ou requête invalide. Veuillez réessayer.";
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else if (!validateEmail($email)) {
            $error = "Format d'email invalide.";
        } else {
        // Check user credentials
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password - try both formats for compatibility
            // First try password_verify (for properly hashed passwords)
            if (strlen($user['password']) > 40 && password_verify($password, $user['password'])) {
                $passwordValid = true;
            }
            // Fallback for plaintext passwords (should be deprecated)
            else if ($password == $user['password']) {
                $passwordValid = true;
                
                // Log the use of plaintext password
                logError("Plaintext password used for user ID: {$user['id']}", __FILE__, __LINE__, 'security');
                
                // Upgrade the password to hashed version if possible
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $user['id']);
                $updateStmt->execute();
            } else {
                $passwordValid = false;
            }
            
            if ($passwordValid) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom'] ?? '';
                $_SESSION['user_prenom'] = $user['prenom'] ?? '';
                
                // Redirect to dashboard
                header("Location: index.php");
                exit();
            } else {
                // Log failed login attempt
                logError("Failed login attempt for email: $email", __FILE__, __LINE__, 'security');
                $error = "Mot de passe incorrect.";
            }
        } else {
            // Log failed login attempt
            logError("Failed login attempt for non-existent email: $email", __FILE__, __LINE__, 'security');
            $error = "Aucun compte trouvé avec cet email.";
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Dentiste Registre</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/fonts/remix/remixicon.css">
    <link rel="stylesheet" href="assets/css/main.min.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="icon-box xl bg-primary rounded-circle mb-3 mx-auto">
                                <img src="assets/images/dent.png" alt="Dentiste Registre Logo" class="img-fluid" style="width: 50%; height: 50%; object-fit: contain; margin: 0 auto;">
                            </div>
                            <h3 class="mb-1">Dentiste Registre</h3>
                            <p class="text-muted">Connectez-vous pour accéder au système</p>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <?php echo generateCSRFToken(); ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Entrez votre email" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-lock-line"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Files -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
