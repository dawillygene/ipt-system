<?php
/**
 * Supervisor Index Page - Auto Redirect to Login
 * IPT System - Supervisor Portal Entry Point
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if supervisor is already logged in
if (isset($_SESSION['supervisor_id']) && !empty($_SESSION['supervisor_id'])) {
    // If supervisor is already logged in, redirect to dashboard
    header('Location: dashboard.php');
    exit();
} else {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

// Fallback in case headers don't work (though this shouldn't be reached)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting... - Supervisor Portal</title>
    <meta http-equiv="refresh" content="0;url=login.php">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: white;
        }
        .redirect-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
        }
        p {
            margin: 0;
            opacity: 0.9;
        }
        .manual-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .manual-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
    <script>
        // JavaScript redirect as additional fallback
        window.onload = function() {
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000);
        };
    </script>
</head>
<body>
    <div class="redirect-container">
        <div class="spinner"></div>
        <h2>Supervisor Portal</h2>
        <p>Redirecting to login page...</p>
        <p>If you are not redirected automatically, <a href="login.php" class="manual-link">click here</a>.</p>
    </div>
</body>
</html>
