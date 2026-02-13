<?php
require_once 'config.php';

// --- ADMIN SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Check if an item ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$item_id = $_GET['id'];
$success = '';

// --- HANDLE FORM SUBMISSION (UPDATE LOGIC) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!empty($_POST['title']) && !empty($_POST['type'])) {
        $stmt = $db->prepare("
            UPDATE items 
            SET title = :title, type = :type, event_date = :event_date 
            WHERE id = :id
        ");
        
        $stmt->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
        $stmt->bindValue(':type', $_POST['type'], PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $_POST['type'] === 'event' ? $_POST['event_date'] : null);
        $stmt->bindValue(':id', $item_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $success = 'Item updated successfully!';
        }
    }
}


// --- FETCH ITEM DATA TO PRE-FILL THE FORM ---
$stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
$stmt->execute([':id' => $item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// If item doesn't exist, redirect
if (!$item) {
    header('Location: admin_dashboard.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Focused Edit Mode" Page --- */
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338CA;
            --bg-color: #F8FAFC; /* Slate-50 */
            --card-bg: #FFFFFF;
            --text-dark: #1E293B; /* Slate-800 */
            --text-light: #64748B; /* Slate-500 */
            --border-color: #E2E8F0; /* Slate-200 */
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); color: var(--text-dark); }
        .edit-container { max-width: 800px; margin: 0 auto; padding: 2.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
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
        .card {
            background-color: var(--card-bg); border-radius: 1rem;
            padding: 2.5rem; border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
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
    </style>
</head>
<body>

    <div class="edit-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">Edit Item</h1>
                <p class="header-subtitle">Update the details for "<?php echo htmlspecialchars($item['title']); ?>"</p>
            </div>
            <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        </header>

        <main>
            <div class="card">
                <?php if ($success): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-center font-medium"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="edit_item.php?id=<?php echo $item_id; ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="itemForm">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="md:col-span-2">
                        <label for="itemTitle" class="input-label">Title</label>
                        <input type="text" id="itemTitle" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required class="input-field">
                    </div>
                    
                    <div>
                        <label for="itemType" class="input-label">Type</label>
                        <select id="itemType" name="type" class="input-field">
                            <option value="task" <?php if ($item['type'] === 'task') echo 'selected'; ?>>Task</option>
                            <option value="event" <?php if ($item['type'] === 'event') echo 'selected'; ?>>Event</option>
                        </select>
                    </div>
                    
                    <div id="eventDateContainer" class="<?php if ($item['type'] !== 'event') echo 'hidden'; ?>">
                        <label for="eventDate" class="input-label">Event Date</label>
                        <input type="date" id="eventDate" name="event_date" value="<?php echo htmlspecialchars($item['event_date']); ?>" class="input-field">
                    </div>

                    <div class="md:col-span-2 text-right">
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
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
        });
    </script>
</body>
</html>