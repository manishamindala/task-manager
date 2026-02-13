<?php
// This line is the most important part. It connects to the database.
require_once 'config.php';

// --- USER SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// --- HANDLE PROFILE UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    if (empty($full_name) || empty($email) || empty($username)) {
        $error = 'Full name, email, and username cannot be empty.';
    } else {
        // Check if the new username or email is already taken by ANOTHER user
        $stmt = $db->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :user_id");
        $stmt->execute([':username' => $username, ':email' => $email, ':user_id' => $user_id]);
        if ($stmt->fetch()) {
            $error = 'That username or email is already in use by another account.';
        } else {
            // Update user details
            $stmt = $db->prepare("UPDATE users SET full_name = :full_name, email = :email, username = :username WHERE id = :user_id");
            $params = [
                ':full_name' => $full_name,
                ':email' => $email,
                ':username' => $username,
                ':user_id' => $user_id
            ];
            if ($stmt->execute($params)) {
                // IMPORTANT: Update the session username in case it was changed
                $_SESSION['username'] = $username;
                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// --- FETCH CURRENT USER DATA ---
$stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Account Settings" Page --- */
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
            --bg-color: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-dark: #1E293B;
            --text-light: #64748B;
            --border-color: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
        }

        .profile-container { max-width: 900px; margin: 0 auto; padding: 2.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; }
        .header-title { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
        .header-subtitle { color: var(--text-light); }
        .header-actions { display: flex; gap: 1rem; }
        .header-btn {
            display: inline-block; padding: 0.75rem 1.5rem; background-color: var(--card-bg);
            border: 1px solid var(--border-color); color: var(--text-light);
            border-radius: 0.5rem; text-decoration: none; font-weight: 600;
            transition: all 0.3s ease;
        }
        .header-btn:hover { background-color: #F1F5F9; color: var(--primary-color); }
        
        .card {
            background-color: var(--card-bg); border-radius: 1rem; padding: 2.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .card-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); }
        
        .input-label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
        .input-field {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.875rem 1.5rem; border: none; border-radius: 0.5rem;
            font-size: 1rem; font-weight: 600; color: white;
            cursor: pointer; text-decoration: none;
            background-color: var(--primary-color);
            transition: background-color 0.2s ease;
        }
        .btn:hover { background-color: var(--primary-hover); }
    </style>
</head>
<body>

    <div class="profile-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">My Profile</h1>
                <p class="header-subtitle">View and update your account details.</p>
            </div>
            <div class="header-actions">
                <a href="events.php" class="header-btn">Discover Events</a>
                <a href="my_events.php" class="header-btn">My Events</a>
                <a href="logout.php" class="header-btn" style="color: var(--danger-color);">Logout</a>
            </div>
        </header>

        <main>
            <div class="card">
                <?php if ($success): ?><div style="background-color: #ECFDF5; color: #065F46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-weight: 500;"><?php echo $success; ?></div><?php endif; ?>
                <?php if ($error): ?><div style="background-color: #FEF2F2; color: #B91C1C; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-weight: 500;"><?php echo $error; ?></div><?php endif; ?>

                <form action="my_profile.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <h2 class="card-title">Personal Information</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div>
                            <label for="full_name" class="input-label">Full Name</label>
                            <!-- THE FIX IS HERE: Added '?? ""' to prevent errors if the value doesn't exist -->
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required class="input-field">
                        </div>
                        <div>
                            <label for="email" class="input-label">Email Address</label>
                            <!-- THE FIX IS HERE: Added '?? ""' to prevent errors if the value doesn't exist -->
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required class="input-field">
                        </div>
                    </div>

                    <h2 class="card-title">Login Details</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div>
                            <label for="username" class="input-label">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="input-field">
                        </div>
                        <div>
                            <label for="password" class="input-label">Password</label>
                            <input type="password" id="password" name="password" disabled class="input-field" value="************" style="background-color: #F8FAFC; cursor: not-allowed;">
                            <p style="font-size: 0.875rem; color: var(--text-light); margin-top: 0.25rem;">Password cannot be changed here for security.</p>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 2rem;">
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
