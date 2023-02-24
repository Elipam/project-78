<!--
    signout.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    User is directed to this page when the users has to be logged out.
    The session in destroyed and the user is directed to the login page.

-->

<?php 
    include "std/session.php"; 

    $_SESSION["login"] = null;
    $_SESSION["username"] = null;
    $_SESSION["admin"] = null;

    header("Location:login");
?>