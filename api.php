<?php
    /*
        api.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 09-12-2022

        Script used by devices to put data into the database.
        Devices are authenicated. Only if the data is valid, it is inserted into the database.
    */
    
    include './std/dbconfiguration.php';
    include './std/sanitize.php';

    //check whether a request was made and whether login credentials were provided
    if(!($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["API-key"]))){
        die("API-key not found");
    }

    //clean login credentials
    $apiKey = sanitize($_POST["API-key"]);
        
    //create connection to database
    $mysql = new mysqli($servername, $username, $password, $dbname);

    //check whether connection to database was susccessfull
    if($mysql->connect_error){
        die("Connection failed: " . $mysql->connect_error);
    }

    //check user authentication credentials
    $deviceId = device_id($apiKey, $mysql);

    if($deviceId == false){
        //close the database connection
        $mysql->close();

        //close if api key not found
        die("API-key is not valid");
    }

    if(!isset($_POST["dateTime"])){
        die("timestamp not found");
    }

    $dateTime = sanitize($_POST["dateTime"]);

    if(!isset($_POST["temperature"]) && !isset($_POST["voltage"]) && !isset($_POST["ampere"])){
        die("no data found");
    }

    //check and clean measurement data
    $temperature = !isset($_POST["temperature"]) ? null : sanitize($_POST["temperature"]);
    $voltage = !isset($_POST["voltage"]) ? null : sanitize($_POST["voltage"]);
    $ampere = !isset($_POST["ampere"]) ? null : sanitize($_POST["ampere"]);

    if(isset($temperature)){
        if(!is_numeric($temperature)){
            die("invalid data type");
        }
    }

    if(isset($voltage)){
        if(!is_numeric($voltage)){
            die("invalid data type");
        }
    }
    
    if(isset($ampere)){
        if(!is_numeric($ampere)){
            die("invalid data type");
        }
    }

    //insert data to database
    insertMeasurement($deviceId, $temperature, $voltage, $dateTime, $ampere, $mysql);

    //inserts measurement to dabase
    function insertMeasurement($deviceId, $temperature, $voltage, $dateTime, $ampere, $mysql){

        //prepare sql statement
        $query = $mysql->prepare("INSERT INTO measurements(device_id, temperature, voltage, time_, ampere) VALUES (?, ?, ?, ?, ?)");

        //bind paramaters to query
        $query->bind_param("iddsd", $deviceId, $temperature, $voltage, $dateTime, $ampere);

        //execute query 
        $query->execute();

        $query->close();

        //prepare sql statement
        $query = $mysql->prepare("INSERT INTO test(measurement_time, deviceId) VALUES (?, ?)");

        //bind paramaters to query
        $query->bind_param("si", $dateTime, $deviceId);

        //execute query 
        $query->execute();

        echo "New record created successfully";
    }


    //authenticates a user
    function device_id($apiKey, $mysql){

        $apiKeyHash = hash("sha256", $apiKey);

        //prepare sql statement
        $query = $mysql->prepare("SELECT device_id, display_name, api_key, description, image FROM devices WHERE api_key = ?");
        
        //bind paramters to query
        $query->bind_param('s', $apiKeyHash);
        
        //execute query
        $query->execute();

        //bind results
        $query->bind_result($deviceId, $displayName, $apiKey, $description, $image);
        
        //fetch results
        // $result = $query->get_result();
        
        while ($query->fetch()) {
            return $deviceId;
        }
        
        //API-key is not found or incorrect
        return false;
    } 

?>
