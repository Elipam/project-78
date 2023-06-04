<!--
    devices/all.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    The Devices-page is part of the main pages.
    Users can see all devices and when they were last active.
    Admins can see, edit, delete and add devices.

-->

<!doctype html>
<html>
    <head>
        <?php 
            include "../std/dbconfiguration.php";
            include "../std/head.php";
            include "../std/sanitize.php";
            include "../std/session.php"; 
			include "../std/timeAgo.php";
            $title = "Devices";
        ?>

		<link href='../css/main.css' rel='stylesheet'>
        <title>Devices</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">
            
            <?php include "../std/sidebar.php"; ?>

            <div class="height-100 bg-light">	
                
                <p>
					This is a list with all devices. Users can see devices.
					Only Administrators can edit, remove and add new devices.
				</p>
				
				<?php
                    //mysql statement to see which devices the user has acces to
                    $mysql = new mysqli($servername, $username, $password, $dbname);
                    // prepare sql statement
                    if ($_SESSION["admin"]) {
                        $query = $mysql->prepare("SELECT device_id FROM devices;");
                 
                        $query->execute();
                       
                        $query->bind_result($deviceId);
                        $permission = "Write";
                    }
                        
                    else{
                        $query = $mysql->prepare("SELECT device_id , permission FROM user_permissions WHERE user_id = ?;");
                        // bind paramters to query
                        $query->bind_param('i', $_SESSION["user_id"]);
                        // execute query
                        $query->execute();
                        // bind results
                        $query->bind_result($deviceId, $permission);
                    }
                    
                    
					// connect to database
				
					

                    // print message if set
					if(isset($_SESSION["message"])){
						echo '<div class="alert alert-primary" role="alert">', $_SESSION["message"], '</div>';
						unset($_SESSION["message"]);
					}

					//display all devices on page
					while ($query->fetch()) {
                        
						// get last updated
						$mysql2 = new mysqli($servername, $username, $password, $dbname);
						
                        $query2 = $mysql2->prepare("SELECT display_name, api_key, description, image , online ,Motor1, Motor2 FROM devices where device_id = ?;");   
                        // bind paramters to query
                        $query2->bind_param('i', $deviceId); 
                        // execute query
                        $query2->execute();
                        // bind results
                        
                        $query2->bind_result($displayName, $apiKey, $description, $image, $online, $motor1, $motor2);
                        $query2->fetch();
                        $query2->close();

                        $query2 = $mysql2->prepare("SELECT voltage, temperature, ampere, Motor1, Motor2 FROM measurements WHERE device_id = ? ORDER BY time_ DESC LIMIT 1;");
                        // bind paramters to query
                        
                        
                        $query2->bind_param('i', $deviceId);
                        
                        // execute query
                        $query2->execute();
                        // bind results
                        $query2->bind_result($voltage, $temperature, $ampere, $motor1, $motor2);
                        
                        if(!$query2->fetch()){
                            $voltage = "N/A";
                            $temperature = "N/A";
                            $ampere = "N/A";
                            $motor1 = "N/A";
                            $motor2 = "N/A";
                        }

                        $query2->close();
                        
                        if ($displayName != NULL) {  
                            if ($online == 1) {
                                $online = "Online";
                            }
                            else {
                                $online = "Offline";
                            } 

                            $slider = strval($deviceId) . "slider";
                            $output = strval($deviceId) . "output";
                            $button = strval($deviceId) . "button";
                            $speed = strval($deviceId) . "speed";
                            $onlineindex = strval($deviceId) . "online";
                            $voltageindex = strval($deviceId) . "voltage";
                            $amperageindex = strval($deviceId) . "amperage";
                            $tempratureindex = strval($deviceId) . "temprature";
                            
                            // if user is an admin
                            if ($_SESSION["admin"] or $permission == "Write") {
                                echo '				
                                    <div class="card mb-3 shadow-lg">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-md-2">
                                                <img style="max-width: 150px;" src="../img/', $image, '" class="card-img" alt="...">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card-body">
                                                    <h2 class="card-title">', $displayName , '</h2>
                                                    <p class="card-text">', $description , '</p>
                                                    <p class="card-text"><small class="text-muted" id=',$onlineindex,'>', $online ,'</small></p>
                                                </div>
                                            </div>
                                        <div class="col-md-2">
                                            <div class="card-body">
                                                <p class="card-text" id=',$voltageindex,'>voltage: ', $voltage , '</p>
                                                <p class="card-text" id=',$amperageindex,'>amperage: ', $ampere , '</p>
                                                <p class="card-text" id=',$tempratureindex,'>temprature: ', $temperature , '</p>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card-body">
                                                <p>Motor 1 speed: <span id="',$speed,'1">', $motor1 ,'</span></p>
                                                <input type="range" min="0" max="100" value="50" class="slider" id="',$slider,'1">
                                                <p>speed: <span id="',$output,'1""></span> %</p>
                                                <button onclick="Send(', $deviceId ,',1)" id= "',$button,'1">Update!</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="card-body">
                                                <p>Motor 2 speed: <span id="',$speed,'2">', $motor2 ,'</span></p>
                                                <input type="range" min="0" max="100" value="50" class="slider" id="',$slider,'2">
                                                <p>speed: <span id="',$output,'2""></span> %</p>
                                                <button onclick="Send(', $deviceId ,',2)" id= "',$button,'2">Update!</button>
                                            </div>
                                        </div>
                                        <script>
                                            var slider',$slider,'1 = document.getElementById("',$slider,'1");
                                            var output',$output,'1 = document.getElementById("',$output,'1");
                                            output',$output,'1.innerHTML = slider',$slider,'1.value;

                                            slider',$slider,'1.oninput = function() {
                                            output',$output,'1.innerHTML = this.value;
                                            }
                                            var slider',$slider,'2 = document.getElementById("',$slider,'2");
                                            var output',$output,'2 = document.getElementById("',$output,'2");
                                            output',$output,'2.innerHTML = slider',$slider,'2.value;

                                            slider',$slider,'2.oninput = function() {
                                            output',$output,'2.innerHTML = this.value;
                                            }
                                        </script>
                                        <div class="col-md-2">

                                            <form class="sub_link" action="edit" method="post">
                                                <input type="hidden" name="device_id" value="', $deviceId , '">
                                                <button type="submit" class="sub_link">
                                                    <i class="bx bx-edit"></i> <span>Edit</span>
                                                </button>
                                            </form>
                                            <br>
                                            <form class="sub_link" action="remove" method="post">
                                                <input type="hidden" name="device_id" value="', $deviceId , '">
                                                <button type="submit" class="sub_link">
                                                    <i class="bx bx-trash"></i> <span>Remove</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            ';
                            }
                            else {
                                echo '				
                                    <div class="card mb-3 shadow-lg">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-md-2">
                                                <img style="max-width: 150px;" src="../img/', $image, '" class="card-img" alt="...">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card-body">
                                                    <h2 class="card-title">', $displayName , '</h2>
                                                    <p class="card-text">', $description , '</p>
                                                    <p class="card-text"><small class="text-muted"id=',$onlineindex,'>Last updated: ', $online ,'</small></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                            <div class="card-body">
                                            <p class="card-text" id=',$voltageindex,'>voltage: ', $voltage , '</p>
                                            <p class="card-text" id=',$amperageindex,'>amperage: ', $ampere , '</p>
                                            <p class="card-text" id=',$tempratureindex,'>temprature: ', $temperature , '</p>
                                        </div>
                                        </div>
                                            <div class="col-md-2">
                                                
                                            </div>
                                        </div>
                                    </div>
                                ';
                            }	
                        }
					}

                    // if user is an admin
                    if ($_SESSION["admin"]) {
                        echo "
                            <a href='new'>
                                <div class='card mb-3 shadow-lg add_new_device'>
                                    <div class='row no-gutters align-items-center'>
                                        <div class='col-md-2'>
                                            <img style='max-width: 150px;' src='../img/add.png' class='card-img' alt='...'>
                                        </div>
                                        <div class='col-md-8'>
                                            <div class='card-body'>
                                                <h2 class='card-title'>Add new Device</h2>
                                                <p class='card-text'>Click here to add a new device to your devices list and generate an API KEY</p>
                                            </div>
                                        </div>
                                        <div class='col-md-2'>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        ";
                    }

				?>
                <script>
                    var conn = new WebSocket('wss://vrfb-test.nl/wsapp/');
                    conn.onopen = function(e) {
                    console.log("Connection established!");
                    };

                    conn.onmessage = function(e) {
                    console.log(e.data);
                    var data = JSON.parse(e.data);
                    var type = data.type;
                    if (type == "response"){
                        var api = data.device_id;
                        var speed = data.data.response;
                        var motor_id = data.data.motor_id;
                        console.log(api+"speed");
                        document.getElementById(api+"button"+motor_id).innerHTML = "updated!";
                        document.getElementById(api+"speed"+motor_id).innerHTML = speed + "%";
                        data = "fefe\"fefe"
                    }
                    else if (type == "DB_update"){
                        var api = 8;
                        var voltage = data.data.voltage;
                        var amperage = data.data.amperage;
                        var temprature = data.data.temp;
                        console.log(api+"voltage");
                        document.getElementById(api+"voltage").innerHTML = "voltage: "+voltage;
                        document.getElementById(api+"amperage").innerHTML = "amperage: "+amperage;
                        document.getElementById(api+"temprature").innerHTML = "temprature: "+temprature;
                    }
                    else if (type == "online"){
                        var device = data.device_id;
                        var online = data.online;
                        if (online == 1){
                            online = "online";
                        }
                        else{
                            online = "offline";
                        }
                        document.getElementById(device+"online").innerHTML = online;
                   
                    };}

                    conn.onclose = function(e) {
                        console.log("Connection closed.");
                        
                        conn = new WebSocket('wss://vrfb-test.nl/wsapp/');
                    };

                    function Send(id, motor_id){
                        document.getElementById(id+"button"+motor_id).innerHTML = "Updating...";
                        speed = document.getElementById(id+"slider"+motor_id).value;
                        var data = {
                            "type": "request",
                            "data": {
                                "device_id": id,
                                "request_type": "motor_speed",
                                "motor_id": motor_id,
                                "request_data": speed
                            }
                        }
                        conn.send(JSON.stringify(data));
                    } 

                 </script>
				
				
				<div style="min-height: 4vh;"></div>

            </div>
            
            <!-- scripts -->
            <?php include "../std/script.php"; ?>
			<script type='text/javascript' src='../js/main.js'></script>
    </body>
</html>