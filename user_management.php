<?php
require_once 'config.php';

// --- ADMIN SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// --- HANDLE USER DELETION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    if (!empty($_POST['user_id'])) {
        $user_id_to_delete = $_POST['user_id'];

        // To maintain database integrity, we delete all related records first
        $stmt = $db->prepare("DELETE FROM feedback WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id_to_delete]);

        $stmt = $db->prepare("DELETE FROM registrations WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id_to_delete]);

        // Finally, delete the user
        $stmt = $db->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $user_id_to_delete]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- FETCH ALL USERS ---
$users = $db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Using the same impressive design language as the admin dashboard */
        :root {
            --primary-color: #4F46E5;
            --danger-color: #DC2626;
            --bg-color: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-dark: #1E293B;
            --text-light: #64748B;
            --border-color: #E2E8F0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); color: var(--text-dark); }
        .main-container { max-width: 900px; margin: 0 auto; padding: 2.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
        .header-title { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
        .header-subtitle { color: var(--text-light); }
        .back-link {
            display: inline-block; padding: 0.75rem 1.5rem; background-color: var(--card-bg);
            border: 1px solid var(--border-color); color: var(--text-light);
            border-radius: 0.5rem; text-decoration: none; font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-link:hover { background-color: #F1F5F9; color: var(--primary-color); }
        .card {
            background-color: var(--card-bg); border-radius: 1rem; padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .card-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem; }
        .user-list-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1rem;
            background-color: #F8FAFC; border: 1px solid var(--border-color);
        }
        .user-info { display: flex; align-items: center; gap: 1rem; font-weight: 500; }
        .delete-btn {
            background: none; border: none; color: var(--text-light);
            cursor: pointer; padding: 0.25rem; border-radius: 9999px;
            transition: all 0.2s ease;
        }
        .delete-btn:hover { color: var(--danger-color); background-color: #FEE2E2; }
    </style>
</head>
<body>

    <div class="main-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">User Management</h1>
                <p class="header-subtitle">View and manage all registered user accounts.</p>
            </div>
            <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        </header>

        <main>
            <div class="card">
                <h2 class="card-title">All Users (<?php echo count($users); ?>)</h2>
                <?php if (empty($users)): ?>
                    <p class="text-text-light italic">No users have registered yet.</p>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <div class="user-list-item">
                            <div class="user-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <form action="user_management.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user and all their data? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="delete-btn" aria-label="Delete user">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>