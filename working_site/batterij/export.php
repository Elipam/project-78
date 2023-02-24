<?php 
    /*
        export.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        Script called when a users wants to export data.
        If $_POST["device_id"] is empty, all devices are exported.
        If not, data of the specified device are exported to csv.
    */

    include "std/session.php"; 
    include_once 'std/dbconfiguration.php';
    include 'std/sanitize.php';
    include "std/logEvent.php";

    
    // assume the following _post parameters are set: 'device_id', 'start' and 'end'
    $device_id = sanitize($_POST["device_id"]);
    $start = date('Y-m-d H:i:s', strtotime(sanitize($_POST["start"])));
    $end = date('Y-m-d H:i:s', strtotime(sanitize($_POST["end"])));

    $csv_filename = 'db_export_measurements_'.date('Y-m-d').'.csv';
    $csv_export = '';

    // connect to database
    $mysql = new mysqli($servername, $username, $password, $dbname);

    if ($mysql->connect_error) {
        die("Connection failed: " . $mysql->connect_error);
    }
    
    if(!empty($device_id)) {
        // specific device
        $query = $mysql->prepare("SELECT measurement_id, device_id, time_, voltage, temperature, ampere FROM measurements WHERE device_id = ? AND time_ BETWEEN ? AND ? ORDER BY time_;");
        $query->bind_param('iss', $device_id, $start, $end);

            
        $userName = $_SESSION["username"];
        $userId = $_SESSION["user_id"];
        logEvent($mysql, "The user '$userName' ($userId) has exported data, from device ($device_id) between '$start' and '$end'.");
    }
    else {
        // all devices
        $query = $mysql->prepare("SELECT measurement_id, device_id, time_, voltage, temperature, ampere FROM measurements WHERE time_ BETWEEN ? AND ? ORDER BY time_;");
        $query->bind_param('ss', $start, $end);

        
        $userName = $_SESSION["username"];
        $userId = $_SESSION["user_id"];
        logEvent($mysql, "The user '$userName' ($userId) has exported data, between '$start' and '$end'.");
    }

    
    // execute query
    $query->execute();
    //bind results
    $query->bind_result($measurementId, $deviceId, $time_, $voltage, $temperature, $ampere);

    // create line with field names
    $csv_export .= "measurment_id; device_id; time; voltage; temperature; ampere;";

    $csv_export .= "\n";

    

    while ($query -> fetch()){
        $csv_export .= "$measurementId; $deviceId; $time_; $voltage; $temperature; $ampere;";

        $csv_export .= "\n";

    }



    // Export the data and prompt a csv file for download
    header("Content-type: text/x-csv");
    header("Content-Disposition: attachment; filename=".$csv_filename."");
    echo($csv_export);

    ?>