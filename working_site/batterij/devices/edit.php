<!--
    devices/edit.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Admins can edit devices and generate a new API-key.

-->

<!doctype html>
<html>
    <head>
        <?php 
            include "../std/dbconfiguration.php";
			include '../std/generate.php';
            include '../std/head.php';
            include "../std/logEvent.php";
            include "../std/sanitize.php";
            include "../std/session.php";
            $title = "Devices";
        ?>

		<link href='../css/main.css' rel='stylesheet'>		
        <title>Edit Device</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">

            <?php include "../std/sidebar.php"; ?>

            <div class="height-100">

				<div style="min-height: 5vh;"></div>
				
				<?php
					// connect to database
					$mysql = new mysqli($servername, $username, $password, $dbname);
                    // sanitize data
					$deviceId = sanitize($_POST["device_id"]);
                    $displayName = sanitize($_POST["displayName"]);
                    $description = sanitize($_POST["description"]);
					$image = sanitize($_POST["image"]);

					// Device can only be edited by Admin
					$query = $mysql->prepare("SELECT admin, username FROM users WHERE user_id = ?");
					// bind paramters to query
					$query->bind_param('i', $_SESSION["user_id"]);
					// execute query
					$query->execute();
					// bind results
					$query->bind_result($admin, $username);
					
					if ($query->fetch()){
						if ($admin != '1') {
							// user is not allowed to edit
							$_SESSION["message"] = "You cannot edit devices because you're not an Admin.";
							header("Location: /devices/all");
						}
					}
					else {
						header("Location: /devices/all");
					}

					$query->close();
				
                    // if generate-button is clicked
					if(isset($_POST["generate"])){
						$newApiKey = generate($mysql);
						$apiKeyHash = hash("sha256", $newApiKey);
						
						// prepare sql statement and bin parameters
						$query = $mysql->prepare("UPDATE devices SET api_key = ? WHERE device_id = ?;");
						$query->bind_param('si', $apiKeyHash, $deviceId);				

						// execute query
						$query->execute();

                        // Log event
                        $userName = $_SESSION["username"];
                        $userId = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$userName' ($userId) has generated a new API-key for device '$displayName' ($deviceId).");
						
						$success = true;
					}
					
					//if page is accessed through save button, update record in the database
					if(isset($_POST["submit"])){
						
						//prepare sql statement and bin parameters
						$query = $mysql->prepare("UPDATE devices SET display_name = ?, description = ?, image = ? WHERE device_id = ?;");
						$query->bind_param('sssi', $displayName, $description, $image, $deviceId);				

						//execute query
						$query->execute();
						
						$_SESSION["message"] = "Your modifications to Device: " . $displayName . " have been saved";

                        
                        $userName = $_SESSION["username"];
                        $userId = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$userName' ($userId) has edited device '$displayName' ($deviceId).");
							
						header("Location:/devices/all");

					}
				
					//if page accessed for first time, display edit fields with relevant data
					if(!isset($_POST["submit"])){

						//prepare sql statement
						$query = $mysql->prepare("SELECT device_id, display_name, api_key, description, image FROM devices WHERE device_id = ?");

						//bind paramters to query
						$query->bind_param('i', $deviceId);

						//execute query
						$query->execute();

						//bind results
						$query->bind_result($deviceId, $displayName, $apiKey, $description, $image);

						if($query->fetch() && !isset($_POST["remove_data"])){

							echo "<h1 class='text-center'>Edit: ", $displayName, "</h1>";

						}
						
						$query->close();
					}
				
					if(isset($_POST["remove_data"])){
						//prepare sql statement and bin parameters
						$query2 = $mysql->prepare("DELETE FROM measurements WHERE device_id = ?");
						$query2->bind_param('i', $deviceId);				

						//execute query
						$query2->execute();
						
						$_SESSION["message"] = "All data entries for device: " . $displayName . " have been removed";
                        
						$userName = $_SESSION["username"];
                        $userId = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$userName' ($userId) has deleted all data connected to device '$displayName' ($deviceId).");

						header("Location:/devices/all");
					}
				

				?>
				
				<div style="min-height: 12vh;"></div>

				<form action="/devices/edit" method="post">
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Display Name</label>
								<input type="text" class="form-control form-control-lg" name="displayName" value="<?php echo $displayName; ?>">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row align-items-center">
						<div class="col-md-2"></div>
						
						<div class="col-lg-7">
							<div class="form-group">
								<label style="font-size: 1.6em;">API Key</label>
								<input type="text" id="api" readonly class="form-control-plaintext form-control-lg" name="apiKey" value="**********">
								<small class="form-text text-muted">current API Key can't be displayed, in case of loss please generate a new API Key</small>
				  			</div>	
						</div>
						
						<div class="col-md-1">
							<button class="devices_button btn btn-primary mb-2" type="submit" name="generate">Generate API Key</button>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<?php 
							
						if(isset($newApiKey) && $success == true){
							echo '<div class="alert alert-primary" role="alert">Your API Key has been generated. Please store this API Key securely, you wont be able to retrieve this again. You can always generate a new API Key through the edit device menu. API KEY: ', $newApiKey, '</div>';
						}
							
					?>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label for="description" style="font-size: 1.6em;">Description</label>
								<textarea class="form-control form-control-lg" name="description" id="description" rows="3"><?php echo $description; ?></textarea>
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label for="description" style="font-size: 1.6em;">Remove all data</label>
								<div class="alert alert-danger" role="alert">IMPORTANT NOTICE! --- Pressing this button will remove all data currently attached to this device from the database. <b>This action cannot be undone</b></div>
								<button class="devices_button_big btn btn-danger mb-2" type="submit" name="remove_data">
									<i class="bx bx-trash"></i>
									<span>REMOVE ALL DEVICE'S DATA ENTRIES</span>
								</button>
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
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device1.png" <?php if($image == "device1.png") {echo "checked";} ?>>
								<img style="max-width: 200px;" src="../img/device1.png" alt="device 1">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device2.png" <?php if($image == "device2.png") {echo "checked";} ?>>
								<img style="max-width: 200px;" src="../img/device2.png" alt="device 2">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device3.png" <?php if($image == "device3.png") {echo "checked";} ?>>
								<img style="max-width: 200px;" src="../img/device3.png" alt="device 3">
							</label>
						</div>
						
						<div class="col-md-2">
							<label>
								<input type="radio" name="image" id="img1" class="d-none imgbgchk" value="device4.png" <?php if($image == "device4.png") {echo "checked";} ?>>
								<img style="max-width: 200px;" src="../img/device4.png" alt="device 4">
							</label>
						</div>
						
						<div class="col-md-2"></div>
					</div>
					
					<div style="min-height: 8vh;"></div>
					<input type="hidden" name="device_id" value="<?php echo $deviceId; ?>">
					
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
            <?php include '../std/script.php'; ?>
			<script type='text/javascript' src='../js/main.js'></script>
    </body>
</html>