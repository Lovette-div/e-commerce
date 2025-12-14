<?php
// Start session if not already started
session_start();

ob_start();


function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}


 //Check if the logged-in user has admin privileges
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1);
}



 //Enforce login (redirect if not logged in)

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}


//Enforce admin privileges (redirect if not admin)
 
function requireAdmin() {
    if (!isAdmin()) {
        echo "Access denied: Admins only.";
        exit();
    }
}

/**
 * Return the current logged-in customer id or null
 */
function get_user_id() {
    return isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
}

?>
