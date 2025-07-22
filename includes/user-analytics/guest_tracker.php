<?php
/**
 * Guest Tracker Utility
 * 
 * Handles tracking of unique guests and their activities
 */

/**
 * Get or create a unique guest ID
 * 
 * @return string The guest UUID
 */
function getGuestId() {
    // Check if guest ID exists in cookie
    if (isset($_COOKIE['guest_uuid'])) {
        $guestId = $_COOKIE['guest_uuid'];
    } else {
        // Generate a new UUID
        $guestId = generateUUID();
        
        // Store in cookie for 1 year
        setcookie('guest_uuid', $guestId, time() + (86400 * 365), '/');
        
        // Register new guest in database
        registerNewGuest($guestId);
    }
    
    return $guestId;
}

/**
 * Generate a UUID v4
 * 
 * @return string UUID
 */
function generateUUID() {
    // Generate 16 bytes (128 bits) of random data
    $data = random_bytes(16);
    
    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    // Output the 36 character UUID
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Register a new guest in the database
 * 
 * @param string $guestId The guest UUID
 * @return bool Success status
 */
function registerNewGuest($guestId) {
    global $conn;
    
    // Check if guest already exists
    $checkSql = "SELECT id FROM guests WHERE uuid = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $guestId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Guest already exists
        return true;
    }
    
    // Insert new guest
    $sql = "INSERT INTO guests (uuid, created_at) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $guestId);
    
    return $stmt->execute();
}

/**
 * Track guest activity
 * 
 * @param string $guestId The guest UUID
 * @param string $action The action performed
 * @param string $pageUrl The URL of the page
 * @return bool Success status
 */
function trackGuestActivity($guestId, $action, $pageUrl = null) {
    global $conn;
    
    // Get current page URL if not provided
    if ($pageUrl === null) {
        $pageUrl = $_SERVER['REQUEST_URI'];
    }
    
    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    // Get user ID if logged in (assuming you have a session variable for this)
    $userId = $_SESSION['user_id'] ?? null;
    
    // Insert activity record
    $sql = "INSERT INTO guest_activities (guest_id, user_id, action, page_url, user_agent, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if ($userId) {
        $stmt->bind_param("sissss", $guestId, $userId, $action, $pageUrl, $userAgent, $ipAddress);
    } else {
        $userId = null;
        $stmt->bind_param("sissss", $guestId, $userId, $action, $pageUrl, $userAgent, $ipAddress);
    }
    
    return $stmt->execute();
}
