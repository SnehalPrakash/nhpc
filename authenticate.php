<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$apiUrl = 'https://apihub.nhpc.in:8443/erp-auth'; 
$apiUser = '103593A';                  
$apiPassword = 'fdsfds';              

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: login.php');
        exit;
    }
    unset($_SESSION['csrf_token']);

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $postData = json_encode([
        'user' => $username,
        'pass' => $password
    ]);


    $ch = curl_init();


    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);           
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
    
    // --- SECURITY IMPROVEMENT: Enforce SSL/TLS verification ---
    // Prevents Man-in-the-Middle (MitM) attacks by verifying the remote server's certificate.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);


    curl_setopt($ch, CURLOPT_USERPWD, "$apiUser:$apiPassword");


    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);


    if ($error) {
        $_SESSION['error'] = 'Authentication service is currently unavailable. Please try again later.';
        error_log('cURL Error: ' . $error);
        header('Location: login.php');
        exit;
    }

    // --- IMPROVED RESPONSE HANDLING ---
    if ($httpCode >= 500) {
        // API server error (5xx)
        $_SESSION['error'] = 'Authentication service is currently unavailable. Please try again later.';
        error_log("Authentication API Error: HTTP Code {$httpCode} | Response: {$response}");
        header('Location: login.php');
        exit;
    } elseif ($httpCode === 200) { 
        $responseData = json_decode($response, true);

        if (isset($responseData['status']) && $responseData['status'] === 'success' && isset($responseData['user'])) {
            $user = $responseData['user'];

            $_SESSION['user_id'] = $user['id'];       
            $_SESSION['username'] = $user['username']; 
            $_SESSION['role'] = $user['role'];       
            $_SESSION['full_name'] = $user['fullName']; 

            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = 'The username or password you entered is incorrect.';
            header('Location: login.php');
            exit;
        }
    } else {
        // Any other error code (e.g., 401, 403, 404) is treated as a failed login.
        $_SESSION['error'] = 'The username or password you entered is incorrect.';
        error_log("Authentication API Error: HTTP Code {$httpCode} | Response: {$response}");
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
