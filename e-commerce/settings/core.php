<?php
// Start session if not already started
session_start();

ob_start();


function isLoggedIn() {
    return isset($_SESSION['user']);
}


 //Check if the logged-in user has admin privileges
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    return (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin');
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
?>
