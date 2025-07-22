<?php
session_start();
include "connection.php";
include "includes/auth/auth.php";
include "includes/utils/error_handler.php";

// Require user to be logged in
requireLogin();

// Get the current user ID
$currentUserId = $_SESSION['user_id'] ?? 0;

// Create a temporary directory for the export files
$tempDir = sys_get_temp_dir() . '/mediregister_export_' . time();
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Create SQL backup file
$sqlFilePath = $tempDir . '/database_backup.sql';
$sqlFile = fopen($sqlFilePath, 'w');

// Add SQL header and comments
fwrite($sqlFile, "-- Dentiste Registre Database Backup\n");
fwrite($sqlFile, "-- Generated on: " . date('Y-m-d H:i:s') . "\n");
fwrite($sqlFile, "-- For user ID: {$currentUserId}\n\n");

// Get user information
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $currentUserId);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userRow = $userResult->fetch_assoc()) {
    // Generate SQL for user
    fwrite($sqlFile, "-- User Information\n");
    fwrite($sqlFile, generateInsertSQL('users', $userRow) . "\n\n");
}

// Create a directory for patient data
$patientsDir = $tempDir . '/patients';
if (!file_exists($patientsDir)) {
    mkdir($patientsDir, 0755, true);
}

// Array to store patient IDs for document collection
$patientIds = [];

// Get patient data
$patientQuery = "SELECT * FROM patients WHERE user_id = ?";
$patientStmt = $conn->prepare($patientQuery);
$patientStmt->bind_param("i", $currentUserId);
$patientStmt->execute();
$patientResult = $patientStmt->get_result();

if ($patientResult->num_rows > 0) {
    fwrite($sqlFile, "-- Patient Data\n");
    while ($patientRow = $patientResult->fetch_assoc()) {
        fwrite($sqlFile, generateInsertSQL('patients', $patientRow) . "\n");
        $patientIds[] = $patientRow['id'];
    }
    fwrite($sqlFile, "\n");
}

// Get appointment data
$appointmentQuery = "SELECT a.* FROM appointments a 
                    JOIN patients p ON a.patient_id = p.id 
                    WHERE a.user_id = ?";
$appointmentStmt = $conn->prepare($appointmentQuery);
$appointmentStmt->bind_param("i", $currentUserId);
$appointmentStmt->execute();
$appointmentResult = $appointmentStmt->get_result();

if ($appointmentResult->num_rows > 0) {
    fwrite($sqlFile, "-- Appointment Data\n");
    while ($appointmentRow = $appointmentResult->fetch_assoc()) {
        fwrite($sqlFile, generateInsertSQL('appointments', $appointmentRow) . "\n");
    }
    fwrite($sqlFile, "\n");
}

// Close SQL file
fclose($sqlFile);

// Get patient documents
if (!empty($patientIds)) {
    // Check if documents table exists
    $tableCheckQuery = "SHOW TABLES LIKE 'patient_documents'";
    $tableResult = $conn->query($tableCheckQuery);
    
    if ($tableResult->num_rows > 0) {
        // Create documents directory
        $documentsDir = $tempDir . '/documents';
        if (!file_exists($documentsDir)) {
            mkdir($documentsDir, 0755, true);
        }
        
        // Get document records
        $placeholders = str_repeat('?,', count($patientIds) - 1) . '?';
        $documentQuery = "SELECT * FROM patient_documents WHERE patient_id IN ($placeholders)";
        $documentStmt = $conn->prepare($documentQuery);
        
        // Bind patient IDs as parameters
        $types = str_repeat('i', count($patientIds));
        $documentStmt->bind_param($types, ...$patientIds);
        $documentStmt->execute();
        $documentResult = $documentStmt->get_result();
        
        // Create a documents index file
        $documentsIndexPath = $documentsDir . '/index.html';
        $documentsIndex = fopen($documentsIndexPath, 'w');
        fwrite($documentsIndex, "<!DOCTYPE html>\n<html>\n<head>\n<title>Patient Documents Index</title>\n");
        fwrite($documentsIndex, "<style>body{font-family:Arial,sans-serif;margin:20px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px}th{background-color:#f2f2f2}</style>\n");
        fwrite($documentsIndex, "</head>\n<body>\n<h1>Patient Documents Index</h1>\n");
        fwrite($documentsIndex, "<table>\n<tr><th>Patient ID</th><th>Document Name</th><th>File Path</th><th>Upload Date</th></tr>\n");
        
        // Add SQL for documents and copy files
        if ($documentResult->num_rows > 0) {
            $sqlFile = fopen($sqlFilePath, 'a');
            fwrite($sqlFile, "-- Patient Documents Data\n");
            
            while ($docRow = $documentResult->fetch_assoc()) {
                // Add SQL insert for document record
                fwrite($sqlFile, generateInsertSQL('patient_documents', $docRow) . "\n");
                
                // Copy the actual document file if it exists
                $originalFilePath = $docRow['file_path'] ?? '';
                
                // Fix path if it's relative or missing document root
                if (!empty($originalFilePath)) {
                    // If path doesn't start with a slash or drive letter, it's relative
                    if ($originalFilePath[0] !== '/' && !preg_match('/^[a-zA-Z]:\\/', $originalFilePath)) {
                        $originalFilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $originalFilePath;
                    }
                    
                    // If path starts with /documents, add document root
                    if (strpos($originalFilePath, '/documents') === 0) {
                        $originalFilePath = $_SERVER['DOCUMENT_ROOT'] . $originalFilePath;
                    }
                    
                    // Log the file path for debugging
                    error_log("Looking for document file at: {$originalFilePath}");
                }
                
                if (!empty($originalFilePath) && file_exists($originalFilePath)) {
                    $fileName = basename($originalFilePath);
                    $patientFolder = $documentsDir . '/patient_' . $docRow['patient_id'];
                    
                    if (!file_exists($patientFolder)) {
                        mkdir($patientFolder, 0755, true);
                    }
                    
                    $destinationPath = $patientFolder . '/' . $fileName;
                    if (copy($originalFilePath, $destinationPath)) {
                        error_log("Successfully copied {$originalFilePath} to {$destinationPath}");
                    } else {
                        error_log("Failed to copy {$originalFilePath} to {$destinationPath}");
                    }
                    
                    // Add to index
                    $uploadDate = !empty($docRow['uploaded_at']) ? $docRow['uploaded_at'] : 'N/A';
                    $documentName = !empty($docRow['file_name']) ? $docRow['file_name'] : $fileName;
                    fwrite($documentsIndex, "<tr><td>{$docRow['patient_id']}</td><td>{$documentName}</td><td>documents/patient_{$docRow['patient_id']}/{$fileName}</td><td>{$uploadDate}</td></tr>\n");
                } else {
                    // Document file not found, create a placeholder note
                    $fileName = !empty($docRow['file_name']) ? $docRow['file_name'] : 'unknown_file';
                    $patientFolder = $documentsDir . '/patient_' . $docRow['patient_id'];
                    
                    if (!file_exists($patientFolder)) {
                        mkdir($patientFolder, 0755, true);
                    }
                    
                    // Create a placeholder text file
                    $placeholderPath = $patientFolder . '/' . $fileName . '.txt';
                    $placeholderContent = "Original document not found.\nOriginal path: {$originalFilePath}\nFile name: {$docRow['file_name']}\nFile type: {$docRow['file_type']}\nUploaded at: {$docRow['uploaded_at']}";
                    file_put_contents($placeholderPath, $placeholderContent);
                    
                    // Add to index with warning
                    $uploadDate = !empty($docRow['uploaded_at']) ? $docRow['uploaded_at'] : 'N/A';
                    $documentName = !empty($docRow['file_name']) ? $docRow['file_name'] : 'unknown_file';
                    fwrite($documentsIndex, "<tr><td>{$docRow['patient_id']}</td><td>{$documentName} (NOT FOUND)</td><td>documents/patient_{$docRow['patient_id']}/{$fileName}.txt</td><td>{$uploadDate}</td></tr>\n");
                }
            }
            fwrite($sqlFile, "\n");
            fclose($sqlFile);
        }
        
        fwrite($documentsIndex, "</table>\n</body>\n</html>");
        fclose($documentsIndex);
    }
}

// Helper function to generate INSERT SQL statements
function generateInsertSQL($table, $data) {
    $columns = array_keys($data);
    $values = array_values($data);
    
    // Format each value properly for SQL
    foreach ($values as &$value) {
        if (is_null($value)) {
            $value = 'NULL';
        } elseif (is_numeric($value)) {
            // Keep numeric values as is
        } else {
            // Escape and quote string values
            $value = "'" . addslashes($value) . "'";
        }
    }
    
    return "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
}

// Create a README file
$readmePath = $tempDir . '/README.txt';
$readme = fopen($readmePath, 'w');
fwrite($readme, "Dentiste Registre Backup\n");
fwrite($readme, "=================\n\n");
fwrite($readme, "Generated on: " . date('Y-m-d H:i:s') . "\n");
fwrite($readme, "For user ID: {$currentUserId}\n\n");
fwrite($readme, "This archive contains:\n");
fwrite($readme, "1. database_backup.sql - SQL file to restore your database data\n");
fwrite($readme, "2. documents/ - Directory containing patient documents (if any)\n");
fwrite($readme, "3. documents/index.html - Index of all documents included\n\n");
fwrite($readme, "To restore:\n");
fwrite($readme, "1. Import the SQL file into your MySQL database\n");
fwrite($readme, "2. Copy the documents to your server's document storage location\n");
fclose($readme);

// Use a simpler approach for creating the backup file
// First, determine if we can use ZIP or need to fall back to SQL
$useZip = class_exists('ZipArchive');
$backupDate = date('Y-m-d');

// Set appropriate headers based on file type
if ($useZip) {
    $zipFilename = 'mediregister_backup_' . $backupDate . '.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
} else {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="database_backup_' . $backupDate . '.sql"');
}

// Disable output buffering to prevent memory issues with large files
if (ob_get_level()) {
    ob_end_clean();
}

// Disable max execution time
set_time_limit(0);

// Disable output compression
ini_set('zlib.output_compression', 'Off');

if ($useZip) {
    // Create the ZIP file
    $zipPath = $tempDir . '/' . $zipFilename;
    $zip = new ZipArchive();
    
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        // Add SQL file to ZIP
        $zip->addFile($sqlFilePath, 'database_backup.sql');
        
        // Add documents directory if it exists
        $documentsDir = $tempDir . '/documents';
        if (file_exists($documentsDir)) {
            // Add documents directory manually
            $docsIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($documentsDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($docsIterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = 'documents/' . substr($filePath, strlen($documentsDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        
        // Add README file
        $readmePath = $tempDir . '/README.txt';
        if (file_exists($readmePath)) {
            $zip->addFile($readmePath, 'README.txt');
        }
        
        // Close ZIP file
        $zip->close();
        
        // Output the ZIP file
        readfile($zipPath);
    } else {
        // If ZIP creation fails, fall back to SQL file
        readfile($sqlFilePath);
    }
} else {
    // Just output the SQL file
    readfile($sqlFilePath);
}

// Clean up
deleteDirectory($tempDir);
exit;

// Helper function to recursively delete a directory
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}
?>
