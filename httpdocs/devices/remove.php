<!--
    devices/remove.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Admins can delete devices using this script.

-->

<?php 
	include "../std/session.php";
	include "../std/sanitize.php";
	include "../std/dbconfiguration.php";
    include "../std/logEvent.php";

	$deviceId = sanitize($_POST["device_id"]);

    if ($_SESSION["admin"]) {
        
        //connect to database
        $mysql = new mysqli($servername, $username, $password, $dbname);
		
		//prepare sql statement
        $query = $mysql->prepare("SELECT device_id FROM measurements WHERE device_id = ?");
		
		//bind parameters to sql statement
        $query->bind_param('i', $deviceId);
                    
        //execute query
        $query->execute();

        $query->bind_result($deviceId);
		
		if($query->fetch()){
			$_SESSION["message"] = "Device cannot be removed because there is still data attached to this device. Please remove all data connected to this device before removing it!";
        	header("Location: /devices/all");
			die();
		}
		
		$query->close();

        

        //prepare sql statement
        $query = $mysql->prepare("SELECT device_id, display_name FROM devices WHERE device_id = ?");
		
		//bind parameters to sql statement
        $query->bind_param('i', $deviceId);
                    
        //execute query
        $query->execute();

        $query->bind_result($deviceId, $displayName);
		
		$query->fetch();
		
		$query->close();



        //prepare sql statement
        $query2 = $mysql->prepare("DELETE FROM devices WHERE device_id = ?;");
            
        //bind parameters to sql statement
        $query2->bind_param('i', $deviceId);
                    
        //execute query
        $query2->execute();
		echo "execute";

        $query2->close();

        $_SESSION["message"] = "Device has been removed";

        // write log

        $userName = $_SESSION["username"];
        $userId = $_SESSION["user_id"];
        logEvent($mysql, "The user '$userName' ($userId) has deleted device '$displayName' ($deviceId).");

        header("Location:/devices/all");
    }
    else {
        $_SESSION["message"] = "Device cannot be removed because you're not an Admin.";

        header("Location:/devices/all");
    }


	

?>