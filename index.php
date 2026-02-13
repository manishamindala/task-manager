<?php
// This must be the very first thing on the page
require_once 'config.php';

// If a user is already logged in, redirect them to their correct dashboard
if (isset($_SESSION['loggedin']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header('Location: events.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vertex Events - Welcome</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Unique Design for "Live Event" Welcome Page --- */
        :root {
            --primary-color: #14B8A6; /* NEW: Teal-500 */
            --secondary-color: #4F46E5; /* Indigo-600 */
            --text-light: #F9FAFB;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-light);
            background-color: #111827; /* Fallback background */
        }

        .hero-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 100vh;
            position: relative;
            padding: 2rem;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.6);
            z-index: 1;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* NEW GRADIENT: Replaced pink with teal */
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.6), rgba(79, 70, 229, 0.6));
            z-index: 2;
        }

        .main-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2.5rem;
            z-index: 3;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            text-decoration: none;
            color: var(--text-light);
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            padding: 0.6rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-login {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-login:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .hero-content {
            position: relative;
            z-index: 3;
        }

        .hero-title {
            font-size: clamp(2.5rem, 8vw, 5rem);
            font-weight: 700;
            line-height: 1.1;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .hero-tagline {
            font-size: clamp(1rem, 3vw, 1.5rem);
            font-weight: 400;
            margin-top: 1rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2.5rem;
            border: 2px solid var(--text-light);
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            color: var(--text-light);
            background-color: transparent;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background-color: var(--text-light);
            color: var(--secondary-color); /* Updated hover color */
            transform: translateY(-3px);
        }

    </style>
</head>
<body>

    <section class="hero-section">
        <header class="main-header">
            <!-- NEW NAME HERE -->
            <a href="#" class="logo">Vertex Events</a>
            <nav class="nav-buttons">
                <a href="admin_login.php" class="nav-btn btn-login">Admin Login</a>
            </nav>
        </header>

        <main class="hero-content">
            <h1 class="hero-title">Experience Events Like Never Before</h1>
            <p class="hero-tagline">THE BEST TIME TO CELEBRATE IS NOW</p>
            <a href="login.php" class="cta-button">GET STARTED</a>
        </main>
    </section>

</body>
</html>