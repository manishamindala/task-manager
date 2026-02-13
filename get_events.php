<?php
// This file acts as a data source for the FullCalendar library.

require_once 'config.php';

// Fetch all items that are events
$stmt = $db->query("SELECT title, event_date FROM items WHERE type = 'event'");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($items as $item) {
    // FullCalendar requires events to have 'title' and 'start' properties.
    $events[] = [
        'title' => $item['title'],
        'start' => $item['event_date']
    ];
}

// Set the content type header to JSON, so the browser knows how to read it.
header('Content-Type: application/json');

// Output the events array in JSON format.
echo json_encode($events);
?>