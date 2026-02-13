<?php
require_once 'config.php';

// --- USER SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// --- FETCH DATA ---
// This query is special: It joins the items and registrations tables
// to get only the events this specific user has registered for.
$stmt = $db->prepare("
    SELECT i.* FROM items i
    JOIN registrations r ON i.id = r.event_id
    WHERE r.user_id = :user_id AND i.type = 'event'
    ORDER BY i.event_date ASC
");
$stmt->execute([':user_id' => $user_id]);
$my_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// We still need to check which events have feedback
$feedback_given = $db->query("SELECT event_id FROM feedback WHERE user_id = $user_id")->fetchAll(PDO::FETCH_COLUMN, 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Personal Itinerary" Page --- */
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
            --feedback-color: #F59E0B;
            --feedback-hover: #D97706;
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

        .my-events-container { max-width: 900px; margin: 0 auto; padding: 2.5rem; }
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
        
        .event-card {
            background-color: var(--card-bg); border-radius: 1rem; padding: 1.5rem;
            border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem;
        }
        .event-info .title { font-size: 1.25rem; font-weight: 600; }
        .event-info .date { color: var(--text-light); font-size: 0.875rem; }
        
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.6rem 1.2rem; border: none; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; color: white;
            cursor: pointer; text-decoration: none; transition: background-color 0.2s ease;
        }
        .btn-feedback { background-color: var(--feedback-color); }
        .btn-feedback:hover { background-color: var(--feedback-hover); }
        .status-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: 9999px;
            font-size: 0.875rem; font-weight: 500;
            background-color: #ECFDF5; color: #065F46;
        }
    </style>
</head>
<body>

    <div class="my-events-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">My Personal Itinerary</h1>
                <p class="header-subtitle">A list of all the events you've registered for.</p>
            </div>
            <div class="header-actions">
                <a href="events.php" class="header-btn">Discover More Events</a>
                <a href="logout.php" class="header-btn" style="color: var(--danger-color);">Logout</a>
            </div>
        </header>

        <main>
            <?php if (empty($my_events)): ?>
                <div class="text-center py-12 card">
                    <h2 class="text-xl font-semibold mb-2">Your Itinerary is Empty</h2>
                    <p class="text-text-light">You haven't registered for any events yet. Head over to the discovery page to find your next experience!</p>
                </div>
            <?php else: ?>
                <?php foreach ($my_events as $event): ?>
                    <div class="event-card">
                        <div class="event-info">
                            <p class="title"><?php echo htmlspecialchars($event['title']); ?></p>
                            <p class="date"><?php echo date("l, F j, Y", strtotime($event['event_date'])); ?></p>
                        </div>
                        <div class="event-actions">
                            <?php if (in_array($event['id'], $feedback_given)): ?>
                                <div class="status-badge">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Feedback Sent</span>
                                </div>
                            <?php else: ?>
                                <!-- Note: The feedback modal is on the events.php page. For simplicity, we link back there. -->
                                <a href="events.php" class="btn btn-feedback">Leave Feedback</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>