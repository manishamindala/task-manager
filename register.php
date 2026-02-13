<?php
require_once 'config.php';

$error = '';
$success = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    // NEW: Get full_name and email from the form
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
        $error = 'All fields are required.';
    } else {
        // Check if username or email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already taken. Please choose another.';
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // NEW: Insert new user with all details into the database
            $stmt = $db->prepare("INSERT INTO users (username, password, full_name, email) VALUES (:username, :password, :full_name, :email)");
            $params = [
                ':username' => $username,
                ':password' => $hashed_password,
                ':full_name' => $full_name,
                ':email' => $email
            ];
            
            if ($stmt->execute($params)) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Planners</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Gallery Login" Page --- */
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
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
            background: url('https://images.unsplash.com/photo-1531058020387-3be344556be6?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
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

        .handwriting-title {
            font-family: 'Dancing Script', cursive;
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--primary-color);
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
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            background-color: var(--primary-color);
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: var(--primary-hover);
        }

        .form-link {
            font-weight: 500;
            color: var(--primary-color);
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
                    <h1 class="handwriting-title">Join the Celebration</h1>
                    <p class="welcome-subtitle">Create your account to begin a new journey of discovery.</p>
                </div>
                
                <form method="POST" action="register.php">
                    <!-- NEW FIELDS ADDED HERE -->
                    <div class="input-group">
                        <label for="full_name" class="input-label">Full Name</label>
                        <input id="full_name" name="full_name" type="text" required class="input-field">
                    </div>
                    <div class="input-group">
                        <label for="email" class="input-label">Email Address</label>
                        <input id="email" name="email" type="email" required class="input-field">
                    </div>
                    <div class="input-group">
                        <label for="username" class="input-label">Username</label>
                        <input id="username" name="username" type="text" required class="input-field">
                    </div>
                    <div class="input-group">
                        <label for="password" class="input-label">Password</label>
                        <input id="password" name="password" type="password" required class="input-field">
                    </div>

                    <?php if ($error): ?>
                        <p class="text-sm text-red-600 mb-4 text-center" style="color: #DC2626;"><?php echo $error; ?></p>
                    <?php endif; ?>
                     <?php if ($success): ?>
                        <p class="text-sm text-green-600 mb-4 text-center" style="color: #10B981;"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="mt-6">
                        <button type="submit" class="btn">
                            Create Account
                        </button>
                    </div>
                </form>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Already have an account? 
                    <a href="login.php" class="form-link">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
