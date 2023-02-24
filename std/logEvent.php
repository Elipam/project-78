<?php
    /*
        std/logEvent.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        PHP-function that inserts a log into the database.
    */

    // php function used to create logs in the database

    include_once "dbconfigurations.php";

    function logEvent($mysql, $message) {
        //define variables
        $user_id = (int) $_SESSION["user_id"];
        if (!isset($user_id)) {
            $user_id = 0;
            // when resetting a password, a user may not be logged in. 
        }
        // message is given as parameter
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        //prepare sql statement and bind parameters
        $query = $mysql->prepare("INSERT INTO logs (user_id, message, ip) VALUES (?, ?, ?);");
        $query->bind_param('iss', $user_id, $message, $ipAddress);
        //execute query
        $query->execute();
        $query->close();

        return 0;
    }

    // NOTE !!!
    //
    // You cannot call the function if you have not closed a previous query.
    // You can close queries after execution with the command:
    // query->close();
    //
?>