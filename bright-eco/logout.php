<?php require 'includes/auth.php'; if(user()) log_action('Logged out'); session_destroy(); header('Location: login.php');
