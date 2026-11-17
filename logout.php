<?php

require 'config/function.php';

if (isset($_SESSION['loggedIn']))
    {
        logoutSession();
        redirect('loginform.php', 'Logged out Successfully');
    }
?>