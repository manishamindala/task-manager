<?php
require_once 'config.php';

// --- ADMIN SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// --- HANDLE POST REQUESTS ---
// (The PHP logic for adding and deleting remains the same)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!empty($_POST['title']) && !empty($_POST['type'])) {
        $stmt = $db->prepare("INSERT INTO items (title, type, event_date) VALUES (:title, :type, :event_date)");
        $stmt->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
        $stmt->bindValue(':type', $_POST['type'], PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $_POST['type'] === 'event' ? $_POST['event_date'] : null);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("DELETE FROM feedback WHERE event_id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $stmt = $db->prepare("DELETE FROM registrations WHERE event_id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// --- FETCH DATA FOR STATS & LISTS ---
$total_events = $db->query("SELECT COUNT(id) FROM items WHERE type = 'event'")->fetchColumn();
$total_users = $db->query("SELECT COUNT(id) FROM users")->fetchColumn();
$total_feedback = $db->query("SELECT COUNT(id) FROM feedback")->fetchColumn();

$tasks = $db->query("SELECT * FROM items WHERE type = 'task' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$events_query = $db->query("
    SELECT 
        i.*, 
        COUNT(DISTINCT f.id) as feedback_count,
        COUNT(DISTINCT r.id) as attendee_count
    FROM items i
    LEFT JOIN feedback f ON i.id = f.event_id
    LEFT JOIN registrations r ON i.id = r.event_id
    WHERE i.type = 'event'
    GROUP BY i.id
    ORDER BY i.event_date ASC
");
$events = $events_query->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Control Panel" Admin Dashboard --- */
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
            --danger-color: #DC2626;
            --bg-color: #F8FAFC; /* Slate-50 */
            --card-bg: #FFFFFF;
            --text-dark: #1E293B; /* Slate-800 */
            --text-light: #64748B; /* Slate-500 */
            --border-color: #E2E8F0; /* Slate-200 */
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2.5rem;
        }

        .header-title { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
        .header-subtitle { color: var(--text-light); }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .header-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-manage-users {
            background-color: var(--card-bg);
            color: var(--text-light);
        }
        .btn-manage-users:hover {
            background-color: #F1F5F9;
            color: var(--primary-color);
        }
        .btn-logout {
            background-color: var(--danger-color);
            color: white;
            border-color: transparent;
        }
        .btn-logout:hover { background-color: #B91C1C; }

        .card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
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

        .list-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1rem;
            background-color: #F8FAFC; border: 1px solid var(--border-color);
        }
        
        .item-actions { display: flex; align-items: center; gap: 1rem; }
        .action-link {
            font-size: 0.875rem; font-weight: 600; text-decoration: none;
            color: var(--primary-color); transition: color 0.2s ease;
        }
        .action-link:hover { color: var(--primary-hover); }
        
        .delete-btn, .edit-btn {
            background: none; border: none; color: var(--text-light);
            cursor: pointer; padding: 0.25rem; border-radius: 9999px;
            transition: all 0.2s ease;
        }
        .delete-btn:hover { color: var(--danger-color); background-color: #FEE2E2; }
        .edit-btn:hover { color: var(--primary-color); background-color: #EEF2FF; }

    </style>
</head>
<body>

    <div class="admin-container">
        <header class="admin-header">
            <div>
                <h1 class="header-title">Control Panel</h1>
                <p class="header-subtitle">Welcome, Administrator <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
            </div>
            <!-- NEW HEADER ACTIONS AREA -->
            <div class="header-actions">
                <a href="user_management.php" class="header-btn btn-manage-users">Manage Users</a>
                <a href="logout.php" class="header-btn btn-logout">Logout</a>
            </div>
        </header>

        <main>
            <section class="card">
                <h2 class="card-title">Quick Actions: Add New Item</h2>
                <form action="admin_dashboard.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end" id="itemForm">
                    <input type="hidden" name="action" value="add">
                    <div class="md:col-span-2">
                        <label for="itemTitle" class="input-label">Title</label>
                        <input type="text" id="itemTitle" name="title" required class="input-field">
                    </div>
                    <div>
                        <label for="itemType" class="input-label">Type</label>
                        <select id="itemType" name="type" class="input-field">
                            <option value="task">Task</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div id="eventDateContainer" class="hidden">
                        <label for="eventDate" class="input-label">Event Date</label>
                        <input type="date" id="eventDate" name="event_date" class="input-field">
                    </div>
                    <div class="text-right md:col-start-4">
                        <button type="submit" class="btn w-full">Add Item</button>
                    </div>
                </form>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <section class="card">
                    <h2 class="card-title">Managed Events</h2>
                    <?php if (empty($events)): ?>
                        <p class="text-text-light italic">No events have been created.</p>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="list-item">
                                <div>
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p class="text-sm text-text-light"><?php echo date("M d, Y", strtotime($event['event_date'])); ?></p>
                                </div>
                                <div class="item-actions">
                                    <a href="view_attendees.php?id=<?php echo $event['id']; ?>" class="action-link">Attendees (<?php echo $event['attendee_count']; ?>)</a>
                                    <a href="view_feedback.php?id=<?php echo $event['id']; ?>" class="action-link">Feedback (<?php echo $event['feedback_count']; ?>)</a>
                                    <a href="edit_item.php?id=<?php echo $event['id']; ?>" class="edit-btn" aria-label="Edit event"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg></a>
                                    <form action="admin_dashboard.php" method="POST">
                                        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" class="delete-btn" aria-label="Delete event"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

                <section class="card">
                    <h2 class="card-title">Internal Tasks</h2>
                    <?php if (empty($tasks)): ?>
                        <p class="text-text-light italic">No internal tasks have been created.</p>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="list-item">
                                <span><?php echo htmlspecialchars($task['title']); ?></span>
                                <div class="item-actions">
                                    <a href="edit_item.php?id=<?php echo $task['id']; ?>" class="edit-btn" aria-label="Edit task"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg></a>
                                    <form action="admin_dashboard.php" method="POST">
                                        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="delete-btn" aria-label="Delete task"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const itemTypeSelect = document.getElementById('itemType');
            const eventDateContainer = document.getElementById('eventDateContainer');
            function toggleDateVisibility() {
                eventDateContainer.classList.toggle('hidden', itemTypeSelect.value !== 'event');
            }
            itemTypeSelect.addEventListener('change', toggleDateVisibility);
            toggleDateVisibility();
        });
    </script>
</body>
</html>