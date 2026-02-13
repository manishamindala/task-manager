<?php
require_once 'config.php';

// --- ADMIN SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Check if an event ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$event_id = $_GET['id'];

// --- FETCH DATA ---
// 1. Get the event details
$stmt = $db->prepare("SELECT title FROM items WHERE id = :id AND type = 'event'");
$stmt->execute([':id' => $event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header('Location: admin_dashboard.php');
    exit;
}

// 2. Get all attendees for this event
$stmt = $db->prepare("
    SELECT u.username
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = :event_id
    ORDER BY u.username ASC
");
$stmt->execute([':event_id' => $event_id]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendees - <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Attendee Roster" Page --- */
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #10B981; /* Emerald-500 */
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

        .attendee-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .header-title { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
        .header-subtitle { color: var(--text-light); }

        .back-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back-link:hover { background-color: #F1F5F9; color: var(--primary-color); }

        .summary-card {
            background: linear-gradient(135deg, var(--secondary-color), #059669);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .summary-card .label { font-size: 1rem; opacity: 0.8; margin-bottom: 0.5rem; }
        .summary-card .total-attendees { font-size: 3rem; font-weight: 700; line-height: 1; }

        .card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .attendee-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .attendee-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: #F8FAFC;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
        }

        .attendee-icon {
            color: var(--text-light);
        }
    </style>
</head>
<body>

    <div class="attendee-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">Attendee Roster</h1>
                <p class="header-subtitle">For event: "<?php echo htmlspecialchars($event['title']); ?>"</p>
            </div>
            <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        </header>

        <div class="summary-card">
            <p class="label">Total Registered Attendees</p>
            <p class="total-attendees"><?php echo count($attendees); ?></p>
        </div>

        <main>
            <div class="card">
                <?php if (empty($attendees)): ?>
                    <p class="text-text-light italic text-center">No users have registered for this event yet.</p>
                <?php else: ?>
                    <div class="attendee-list">
                        <?php foreach ($attendees as $attendee): ?>
                            <div class="attendee-item">
                                <div class="attendee-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span><?php echo htmlspecialchars($attendee['username']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>