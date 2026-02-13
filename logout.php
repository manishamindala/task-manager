<?php
// Include the configuration to start the session
require_once 'config.php';

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit;
?>