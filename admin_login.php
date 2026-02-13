<?php
require_once 'config.php';

$error = '';

// If admin is already logged in, redirect to the dashboard
if (isset($_SESSION['loggedin']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verify admin credentials
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        // If correct, set session and redirect
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = ADMIN_USERNAME;
        $_SESSION['role'] = 'admin';
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid admin username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Event Planners</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for Admin "Command Center" Login Page --- */
        :root {
            --danger-color: #DC2626; /* Red-600 */
            --danger-hover: #B91C1C; /* Red-700 */
            --bg-light: #F9FAFB;
            --text-dark: #1F2937;
            --text-muted: #6B7280;
            --border-color: #D1D5DB;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        .login-layout {
            display: flex;
            min-height: 100vh;
        }

        .image-panel {
            width: 50%;
            background: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2084&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
        }

        .form-panel {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }

        .welcome-text {
            margin-bottom: 2.5rem;
        }

        .welcome-title {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .welcome-subtitle {
            font-size: 1rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            background-color: var(--danger-color);
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: var(--danger-hover);
        }

        .form-link {
            font-weight: 500;
            color: var(--danger-color);
            text-decoration: none;
        }
        .form-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-layout">
        <div class="image-panel"></div>
        <div class="form-panel">
            <div class="form-container">
                <div class="welcome-text">
                    <h1 class="welcome-title">Administrator Access</h1>
                    <p class="welcome-subtitle">Welcome to the command center. Please sign in to continue.</p>
                </div>
                
                <form method="POST" action="admin_login.php">
                    <div class="input-group">
                        <label for="username" class="input-label">Admin Username</label>
                        <input id="username" name="username" type="text" required class="input-field">
                    </div>

                    <div class="input-group">
                        <label for="password" class="input-label">Admin Password</label>
                        <input id="password" name="password" type="password" required class="input-field">
                    </div>

                    <?php if ($error): ?>
                        <p class="text-sm text-red-600 mb-4 text-center"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <div class="mt-6">
                        <button type="submit" class="btn">
                            Sign In
                        </button>
                    </div>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    <a href="index.php" class="form-link">
                        &larr; Back to Welcome Page
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>