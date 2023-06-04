<!--
    devices/new.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Admins can add new devices.

-->

<!doctype html>
<html>
    <head>
        <?php 
            include "../std/dbconfiguration.php";
            include "../std/generate.php";
            include "../std/head.php";
            include "../std/logEvent.php";
            include "../std/sanitize.php";
            include "../std/session.php";
            $title = "Devices";
        ?>
		<link href='../css/main.css' rel='stylesheet'>		
        <title>Add New Device</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">

            <?php include "../std/sidebar.php"; ?>

            <div class="height-100">

				<div style="min-height: 5vh;"></div>
				
				<?php
                
                    // connect to database
                    $mysql = new mysqli($servername, $username, $password, $dbname);
                    // Only Admins can create new users
					$query = $mysql->prepare("SELECT admin, username FROM users WHERE user_id = ?");
					// bind paramters to query
					$query->bind_param('i', $_SESSION["user_id"]);
					// execute query
					$query->execute();
					// bind results
					$query->bind_result($admin, $userName);

					if ($query->fetch()) {
						if ($admin != '1') {
							// user is not allowed to edit
							$_SESSION["message"] = "You cannot create new devices because you're not an Admin.";
							header("Location: /devices/all");
						}
					}
					else {
						header("Location: /devices/all");
					}

					$query->close();

					// if page is accessed through save button, update record in the database
					if(isset($_POST["submit"])){
						
						// define variables
						$displayName = sanitize($_POST["displayName"]);
						$apiKey = generate($mysql);
						$description = sanitize($_POST["description"]);
						$image = sanitize($_POST["image"]);
						$apiKeyHash = hash("sha256", $apiKey);

						// prepare sql statement and bind parameters
						$query = $mysql->prepare("INSERT INTO devices(display_name, api_key, description, image) VALUES (?, ?, ?, ?);");
						$query->bind_param('ssss', $displayName, $apiKeyHash, $description, $image);					
						// execute query
						$query->execute();
                        $query->close();
                        // get deviceId

                        $query = $mysql->prepare("SELECT device_id from devices WHERE api_key = ? LIMIT 1;");
                        $query->bind_param('s', $apiKeyHash);

                        $query->execute();

                        $query->bind_result($deviceId);

                        $query->fetch();
                        $query->close();
						
                        $userName = $_SESSION["username"];
                        $userId = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$userName' ($userId) has created device '$displayName' ($deviceId).");

						$_SESSION["message"] = "Device: " . $displayName . " has been created with API Key: " . $apiKey;

                        header("Location:/devices/all");
						
					}

				?>

				<h1 class='text-center'>Add New Device</h1>
				
				<div style="min-height: 6vh;"></div>
				
				<div style="min-height: 6vh;"></div>

				<form action="/devices/new" method="post">
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Display Name</label>
								<input type="text" class="form-control form-control-lg" name="displayName">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row align-items-center">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">API Key</label>
								<input type="text" id="api" readonly class="form-control-plaintext form-control-lg" name="apiKey">
								<small class="form-text text-muted">API Key will be automatically generated when saving the new device</small>
							</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label for="description" style="font-size: 1.6em;">Description</label>
								<textarea class="form-control form-control-lg" name="description" id="description" rows="3"></textarea>
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Image</label>
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 8vh;"></div>
					
					<div class="row text-center">
						<div class="col-md-2"></div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device1.png">
								<img style="max-width: 200px;" src="../img/device1.png" alt="device 1">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device2.png">
								<img style="max-width: 200px;" src="../img/device2.png" alt="device 2">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device3.png">
								<img style="max-width: 200px;" src="../img/device3.png" alt="device 3">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device4.png">
								<img style="max-width: 200px;" src="../img/device4.png" alt="device 4">
							</label>
						</div>
						
						<div class="col-md-2"></div>
					</div>
					
					<div style="min-height: 8vh;"></div>
					
					<div class="row text-center">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<button class="devices_button_big btn btn-primary mb-2" type="submit" name="submit">
									<i class="bx bx-save"></i>
									<span>Save</span>
								</button>
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 10vh;"></div>
				
				</form>
            </div>

            <!-- scripts -->
            <?php include "../std/script.php"; ?>
			<script type='text/javascript' src='../js/main.js'></script>
    </body>
</html>