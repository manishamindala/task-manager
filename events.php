<?php
require_once 'config.php';

// --- USER SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// --- HANDLE POST REQUESTS (Registration & Feedback) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event_id'])) {
    $event_id = $_POST['register_event_id'];
    $stmt = $db->prepare("SELECT id FROM registrations WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute([':user_id' => $user_id, ':event_id' => $event_id]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO registrations (user_id, event_id) VALUES (:user_id, :event_id)");
        if ($stmt->execute([':user_id' => $user_id, ':event_id' => $event_id])) {
            $success = "Successfully registered for the event!";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $event_id = $_POST['feedback_event_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    $stmt = $db->prepare("SELECT id FROM feedback WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute([':user_id' => $user_id, ':event_id' => $event_id]);
    if ($stmt->fetch()) {
        $error = "You have already submitted feedback for this event.";
    } else {
        $stmt = $db->prepare("INSERT INTO feedback (user_id, event_id, rating, comment) VALUES (:user_id, :event_id, :rating, :comment)");
        if ($stmt->execute([':user_id' => $user_id, ':event_id' => $event_id, ':rating' => $rating, ':comment' => $comment])) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Failed to submit feedback. Please try again.";
        }
    }
}


// --- HANDLE SEARCH & FETCH DATA ---
$search_term = '';
$sql = "SELECT * FROM items WHERE type = 'event'";
$params = [];

if (!empty($_GET['search'])) {
    $search_term = trim($_GET['search']);
    $sql .= " AND title LIKE :search_term";
    $params[':search_term'] = '%' . $search_term . '%';
}

$sql .= " ORDER BY event_date ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$registrations = $db->query("SELECT event_id FROM registrations WHERE user_id = $user_id")->fetchAll(PDO::FETCH_COLUMN, 0);
$feedback_given = $db->query("SELECT event_id FROM feedback WHERE user_id = $user_id")->fetchAll(PDO::FETCH_COLUMN, 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for User Dashboard --- */
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

        .user-container { max-width: 900px; margin: 0 auto; padding: 2.5rem; }
        .user-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
        .header-title { font-size: 2.25rem; font-weight: 700; line-height: 1.2; }
        .header-subtitle { color: var(--text-light); }
        .header-actions { display: flex; gap: 1rem; } /* Container for buttons */
        .header-btn {
            display: inline-block; padding: 0.75rem 1.5rem; background-color: var(--card-bg);
            border: 1px solid var(--border-color); color: var(--text-light);
            border-radius: 0.5rem; text-decoration: none; font-weight: 600;
            transition: all 0.3s ease;
        }
        .header-btn:hover { background-color: #F1F5F9; color: var(--primary-color); }
        
        /* Search Bar Styles */
        .search-container { margin-bottom: 2rem; position: relative; }
        .search-input {
            width: 100%; padding: 1rem 1rem 1rem 3rem;
            border: 1px solid var(--border-color); border-radius: 0.75rem;
            font-size: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .search-input:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-light); }

        .event-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        .event-card {
            background-color: var(--card-bg); border-radius: 1rem; padding: 1.5rem;
            border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center;
            transition: all 0.3s ease;
        }
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07); }
        .event-info .title { font-size: 1.25rem; font-weight: 600; }
        .event-info .date { color: var(--text-light); font-size: 0.875rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 0.6rem 1.2rem; border: none; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; color: white;
            cursor: pointer; text-decoration: none; transition: background-color 0.2s ease;
        }
        .btn-primary { background-color: var(--primary-color); }
        .btn-primary:hover { background-color: var(--primary-hover); }
        .btn-feedback { background-color: var(--feedback-color); }
        .btn-feedback:hover { background-color: var(--feedback-hover); }
        .status-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: 9999px;
            font-size: 0.875rem; font-weight: 500;
            background-color: #ECFDF5; color: #065F46;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(30, 41, 59, 0.8);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000; opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal-overlay.visible { opacity: 1; visibility: visible; }
        .modal-content {
            background: white; padding: 2rem; border-radius: 1rem;
            width: 90%; max-width: 500px;
            transform: scale(0.95); transition: transform 0.3s ease;
        }
        .modal-overlay.visible .modal-content { transform: scale(1); }
        .star-rating { display: flex; direction: rtl; justify-content: center; gap: 0.5rem; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { font-size: 2.5rem; color: #d1d5db; cursor: pointer; transition: color 0.2s; }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #f59e0b; }
        .input-label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
        .input-field {
            width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color);
            border-radius: 0.5rem; transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    </style>
</head>
<body>

    <div class="user-container">
        <header class="user-header">
            <div>
                <h1 class="header-title">Event Discovery</h1>
                <p class="header-subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            <!-- NEW HEADER ACTIONS AREA -->
            <div class="header-actions">
                <a href="my_profile.php" class="header-btn">My Profile</a>
                <a href="my_events.php" class="header-btn">My Events</a>
                <a href="logout.php" class="header-btn" style="color: var(--danger-color);">Logout</a>
            </div>
        </header>

        <main>
            <div class="search-container">
                <form action="events.php" method="GET">
                    <div class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="search" name="search" class="search-input" placeholder="Search for events by name..." value="<?php echo htmlspecialchars($search_term); ?>">
                </form>
            </div>

            <?php if ($success): ?><div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center font-medium"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-center font-medium"><?php echo $error; ?></div><?php endif; ?>

            <div class="event-grid">
                <?php if (empty($events)): ?>
                    <div class="text-center py-12 card">
                        <?php if (!empty($search_term)): ?>
                            <p class="text-text-light italic">No events found matching your search for "<?php echo htmlspecialchars($search_term); ?>".</p>
                        <?php else: ?>
                            <p class="text-text-light italic">No events have been scheduled by the admin yet.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-info">
                                <p class="title"><?php echo htmlspecialchars($event['title']); ?></p>
                                <p class="date"><?php echo date("l, F j, Y", strtotime($event['event_date'])); ?></p>
                            </div>
                            <div class="event-actions">
                                <?php if (in_array($event['id'], $registrations)): ?>
                                    <?php if (in_array($event['id'], $feedback_given)): ?>
                                        <div class="status-badge">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <span>Feedback Sent</span>
                                        </div>
                                    <?php else: ?>
                                        <button onclick="openFeedbackModal(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars(addslashes($event['title'])); ?>')" class="btn btn-feedback">Leave Feedback</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <form method="POST" action="events.php">
                                        <input type="hidden" name="register_event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" class="btn btn-primary">Register</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal-overlay">
        <div class="modal-content">
            <h2 class="card-header" id="modal-title" style="font-size: 1.5rem;">Leave Feedback</h2>
            <form method="POST" action="events.php">
                <input type="hidden" name="submit_feedback" value="1">
                <input type="hidden" name="feedback_event_id" id="feedback_event_id">
                <div class="mb-6">
                    <label class="input-label text-center">Your Rating</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars">★</label>
                        <input type="radio" id="star4" name="rating" value="4"/><label for="star4" title="4 stars">★</label>
                        <input type="radio" id="star3" name="rating" value="3"/><label for="star3" title="3 stars">★</label>
                        <input type="radio" id="star2" name="rating" value="2"/><label for="star2" title="2 stars">★</label>
                        <input type="radio" id="star1" name="rating" value="1"/><label for="star1" title="1 star">★</label>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="comment" class="input-label">Comments (Optional)</label>
                    <textarea name="comment" id="comment" rows="4" class="input-field" placeholder="Tell us more about your experience..."></textarea>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeFeedbackModal()" class="btn" style="background-color: #e2e8f0; color: var(--text-dark);">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('feedbackModal');
        const modalTitle = document.getElementById('modal-title');
        const eventIdInput = document.getElementById('feedback_event_id');
        function openFeedbackModal(eventId, eventTitle) {
            eventIdInput.value = eventId;
            modalTitle.textContent = `Feedback for: ${eventTitle}`;
            modal.classList.add('visible');
        }
        function closeFeedbackModal() {
            modal.classList.remove('visible');
        }
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeFeedbackModal();
            }
        });
    </script>

</body>
</html>
