<?php
session_start();
require '../config/function.php'; // Ginamit ko 'yung relative path mo

// --- START NG FIX: ITO 'YUNG "BOUNCER" ---
// Tinitingnan nito kung ang 'loggedIn' ay HINDI true
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    
    // Kung HINDI, ibig sabihin hindi siya naka-log in.
    redirect('../loginform.php', 'You must be logged in to access the dashboard.');
    exit; // Itigil ang pag-load ng page
}
// --- END NG FIX ---
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link href="assets/css/styles.css" rel="stylesheet" />
        

        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">

        <?php include('navbar.php'); ?>

        <div id="layoutSidenav">

        <?php include('sidebar.php'); ?>

        <div id="layoutSidenav_content">
        
            <main>