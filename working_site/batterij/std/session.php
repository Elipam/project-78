<?php
    /*
        std/session.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        Script that starts a session. If users is not logged in, it directs to the login-page.
    */

    // server should keep session data for 15 min
    ini_set('session.gc_maxlifetime', 900);

    // each client should remember their session id for EXACTLY 15
    session_set_cookie_params(900);

    // hier wordt een sessie gestart
    session_start();

    if(!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
        header("Location: login");
    }

?>