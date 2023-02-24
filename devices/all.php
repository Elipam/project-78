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
					// connect to database
					$mysql = new mysqli($servername, $username, $password, $dbname);
					// prepare sql statement
					$query = $mysql->prepare("SELECT device_id, display_name, api_key, description, image FROM devices;");
					// execute query
					$query->execute();
					// bind results
					$query->bind_result($deviceId, $displayName, $apiKey, $description, $image);

                    // print message if set
					if(isset($_SESSION["message"])){
						echo '<div class="alert alert-primary" role="alert">', $_SESSION["message"], '</div>';
						unset($_SESSION["message"]);
					}

					//display all devices on page
					while ($query->fetch()) {

						// get last updated
						$mysql2 = new mysqli($servername, $username, $password, $dbname);
						// prepare sql statement
						$query2 = $mysql2->prepare("SELECT MAX(time_) AS latest FROM measurements WHERE device_id = ?");
						// bind paramters to query
						$query2->bind_param('i', $deviceId);
						// execute query
						$query2->execute();
						// bind results
						$query2->bind_result($latest);

						if ($query2->fetch()) {
							$lastUpdated = get_time_ago($latest);
						}
					
                        // if user is an admin
						if ($_SESSION["admin"]) {
                            echo '				
                                <div class="card mb-3 shadow-lg">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-md-2">
                                            <img style="max-width: 150px;" src="../img/', $image, '" class="card-img" alt="...">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h2 class="card-title">', $displayName , '</h2>
                                                <p class="card-text">', $description , '</p>
                                                <p class="card-text"><small class="text-muted">Last updated: ', $lastUpdated ,'</small></p>
                                            </div>
                                        </div>
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
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h2 class="card-title">', $displayName , '</h2>
                                                <p class="card-text">', $description , '</p>
                                                <p class="card-text"><small class="text-muted">Last updated: ', $lastUpdated ,'</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            
                                        </div>
                                    </div>
                                </div>
                            ';
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
    
				
				
				<div style="min-height: 4vh;"></div>

            </div>
            
            <!-- scripts -->
            <?php include "../std/script.php"; ?>
			<script type='text/javascript' src='../js/main.js'></script>
    </body>
</html>