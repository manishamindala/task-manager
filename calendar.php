<?php
require_once 'config.php';

// --- USER SECURITY CHECK ---
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <style>
        /* --- Unique Design for "Event Schedule" Calendar Page --- */
        :root {
            --primary-color: #4F46E5;
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

        .calendar-container { max-width: 1400px; margin: 0 auto; padding: 2.5rem; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
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
        
        .calendar-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        /* FullCalendar Customization */
        :root {
            --fc-border-color: var(--border-color);
            --fc-daygrid-event-dot-width: 8px;
            --fc-event-bg-color: var(--primary-color);
            --fc-event-border-color: var(--primary-color);
            --fc-today-bg-color: rgba(79, 70, 229, 0.05);
        }
        .fc .fc-toolbar-title { font-size: 1.5rem; font-weight: 600; }
        .fc .fc-button-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        .fc .fc-button-primary:hover {
            background-color: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
        }
    </style>
</head>
<body>

    <div class="calendar-container">
        <header class="page-header">
            <div>
                <h1 class="header-title">Event Schedule</h1>
                <p class="header-subtitle">A calendar view of all upcoming events.</p>
            </div>
            <div class="header-actions">
                <a href="events.php" class="header-btn">List View</a>
                <a href="my_events.php" class="header-btn">My Events</a>
                <a href="logout.php" class="header-btn" style="color: var(--danger-color);">Logout</a>
            </div>
        </header>

        <main>
            <div class="calendar-card">
                <div id='calendar'></div>
            </div>
        </main>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          // This is where the magic happens.
          // We tell the calendar to fetch events from a separate PHP file.
          events: 'get_events.php' 
        });
        calendar.render();
      });
    </script>

</body>
</html>