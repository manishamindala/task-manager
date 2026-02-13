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

// 2. Get all feedback for this event
$stmt = $db->prepare("
    SELECT f.rating, f.comment, f.created_at, u.username
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    WHERE f.event_id = :event_id
    ORDER BY f.created_at DESC
");
$stmt->execute([':event_id' => $event_id]);
$feedback_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Calculate average rating
$total_ratings = 0;
$average_rating = 0;
if (count($feedback_list) > 0) {
    foreach ($feedback_list as $feedback) {
        $total_ratings += $feedback['rating'];
    }
    $average_rating = round($total_ratings / count($feedback_list), 1);
}

// Function to display stars
function render_stars($rating) {
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        $output .= '<span class="text-2xl ' . ($i <= $rating ? 'text-amber-400' : 'text-slate-300') . '">★</span>';
    }
    return $output;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback - <?php echo htmlspecialchars($event['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Feedback Insights" Page --- */
        :root {
            --primary-color: #4F46E5;
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

        .feedback-container {
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
            background: linear-gradient(135deg, var(--primary-color), #3730A3);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .summary-card .label { font-size: 1rem; opacity: 0.8; margin-bottom: 0.5rem; }
        .summary-card .average-rating { font-size: 3rem; font-weight: 700; line-height: 1; }
        .summary-card .total-feedback { font-size: 0.875rem; opacity: 0.7; margin-top: 0.5rem; }

        .feedback-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .feedback-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .feedback-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }

        .feedback-card-header .username { font-weight: 600; color: var(--primary-color); }
        .feedback-card-header .date { font-size: 0.875rem; color: var(--text-light); }
        .feedback-card .comment {
            font-style: italic;
            color: var(--text-dark);
            line-height: 1.6;
        }
        .feedback-card .comment::before { content: '“'; }
        .feedback-card .comment::after { content: '”'; }
    </style>
</head>
<body>

    <div class="feedback-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">Feedback Insights</h1>
                <p class="header-subtitle">For event: "<?php echo htmlspecialchars($event['title']); ?>"</p>
            </div>
            <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        </header>

        <div class="summary-card">
            <p class="label">Average Rating</p>
            <p class="average-rating"><?php echo $average_rating; ?> <span style="font-size: 1.5rem; opacity: 0.8;">/ 5</span></p>
            <p class="total-feedback"><?php echo count($feedback_list); ?> total responses</p>
        </div>

        <main>
            <div class="feedback-list">
                <?php if (empty($feedback_list)): ?>
                    <div class="text-center py-12 card">
                        <p class="text-text-light italic">No feedback has been submitted for this event yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedback_list as $feedback): ?>
                        <div class="feedback-card">
                            <div class="feedback-card-header">
                                <span class="username"><?php echo htmlspecialchars($feedback['username']); ?></span>
                                <span class="date"><?php echo date("M d, Y", strtotime($feedback['created_at'])); ?></span>
                            </div>
                            <div><?php echo render_stars($feedback['rating']); ?></div>
                            <?php if (!empty($feedback['comment'])): ?>
                                <p class="comment mt-4"><?php echo nl2br(htmlspecialchars($feedback['comment'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>