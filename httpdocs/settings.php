<!--
    settings.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    The Settings-page is part of the main pages.
    It can only be visited by admins.
    It shows buttons that direct to the manuals, admin centers and the Logs are displayed.

-->

<?php include "std/session.php"; ?>

<style>

	/* temporary styling for table */
	table, td, th {
		border: 1px solid;
	}

	table {
		width: 100%;
		border-collapse: collapse;
	}
</style>


<!doctype html>
<html>
    <head>
    <?php 
            include 'std/head.php';
			include 'std/dbconfiguration.php';
            $title = "Settings";
        ?>
		<link href='css/main.css' rel='stylesheet'>
        <title>Settings</title>
    </head>
    <body className='snippet-body'>
        <body id="body-pd">
            
            <?php include 'std/sidebar.php'; ?>

           

            <div class="height-100 bg-light">

                <?php
                    // only Admins can see this page
                    // check if user is Admin
                    if ($_SESSION["admin"] != '1') {
                        header("Location: /");
                    }
                ?>

				<h5>Manuals</h5>
				<p>
					Click a manual to open...
				</p>
				
				<br>

				<a target="_blank" href="/documents/User_manual.pdf">
					<div class="card mb-3 shadow-lg add_new_device">
						<div class="row no-gutters align-items-center">
							<div class="col-md-2">
								<img style="max-width: 150px;" src="/img/document.png" class="card-img" alt="...">
							</div>
							<div class="col-md-8">
								<div class="card-body">
									<h2 class="card-title">User Manual</h2>
									<p class="card-text">Click here to access the Manual for users.</p>
								</div>
							</div>
							<div class="col-md-2">
							</div>
						</div>
					</div>
				</a>

				<a target="_blank" href="/documents/Admin_manual.pdf">
					<div class="card mb-3 shadow-lg add_new_device">
						<div class="row no-gutters align-items-center">
							<div class="col-md-2">
								<img style="max-width: 150px;" src="/img/document.png" class="card-img" alt="...">
							</div>
							<div class="col-md-8">
								<div class="card-body">
									<h2 class="card-title">Admin Manual</h2>
									<p class="card-text">Click here to access the Manual for admin.</p>
								</div>
							</div>
							<div class="col-md-2">
							</div>
						</div>
					</div>
				</a>

				<hr>
				<h5>Admin centers</h5>
				<p>
					Here are some links to the Admin centers.
				</p>

				<div class="col-md-1">
					<a class="devices_button btn btn-primary mb-2" href="https://web0150.zxcs.nl:2222/">DirectAdmin</a>
				</div>

				<div class="col-md-1">
					<a class="devices_button btn btn-primary mb-2" href="https://web0150.zxcs.nl/phpmyadmin/">phpMyAdmin</a>
				</div>
				<hr>

				<form method="post">
				<select name="user">
				Here you can add permissions to users to see devices. <br>
				
				Grant permissions to user: 
				<option value="">--users--</option>
					<?php
						// connect to database
						$mysql = new mysqli($servername, $username, $password, $dbname);

						// query to get data from database
						$query = $mysql->prepare("SELECT user_id, username FROM users;");
						// execute query
						$query->execute();

						//bind results
						$query->bind_result($userId, $username);
						
						while ($query -> fetch()){
							echo "
								<option value='$userId'>$username</option>
							";
						}
						$query->close();
						?>
				</select>
				For the device:
				<select name="Device">
				<option value="">--devices--</option>
					<?php
						
						// query to get data from database
						$query = $mysql->prepare("SELECT device_id, display_name FROM devices;");
						// execute query
						$query->execute();

						//bind results
						$query->bind_result($deviceId, $deviceName);
						
						while ($query -> fetch()){
							echo "
								<option value='$deviceId'>$deviceName</option>
							";
							
						}
						$query->close();
						?>
				</select>
				With the permission:
				<select name="Permission">
				<option value="None">None</option>
				<option value="Read">Read</option>
				<option value="Write">Write</option>
				</select>
				<input type="submit" name="Submit" value="Submit">
                </form>

				<?php
					if(isset($_POST['Submit'])){
						$userId = (int)$_POST['user'];
						$deviceId = (int)$_POST['Device'];
						$permission = $_POST['Permission'];						
						// query to get data from database
						$query = $mysql->prepare("SELECT * FROM user_permissions where user_id = ? and device_id = ?;");
						//bind parameters
						$query->bind_param("ii", $userId, $deviceId);
						// execute query
						$query->execute();

						$query->bind_result($userId, $deviceId, $permission);
						if ($query -> fetch()){
							// query to get data from database
							$query->close();
							$query = $mysql->prepare("UPDATE user_permissions SET permission = ? WHERE user_id = ? and device_id = ?;");
							//bind parameters
							
							$query->bind_param("sii", $permission, $userId, $deviceId);
							// execute query
							$query->execute();
							echo "$permission Permissions updated for user: $userId and device: $deviceId";
						}else{
							// query to get data from database
							$query = $mysql->prepare("INSERT INTO user_permissions (user_id, device_id, permission) VALUES (?, ?, ?);");
							//bind parameters
							$query->bind_param("iis", $userId, $deviceId, $permission);
							// execute query
							$query->execute();
							echo "$permission Permissions added for user: $userId and device: $deviceId";
						}
						

						
						


					}

				?>
				<hr>


				<h5>Logs</h5>
				<p>
					Here you can see the last logs. Only Admins can see the last 100 logs.
				</p>

				<table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">User ID</th>
                            <th scope="col">Timestamp</th>
                            <th scope="col">Message</th>
                            <th scope="col">Ip-address</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            // query to get data from database
                            $query = $mysql->prepare("SELECT user_id, time, message, ip FROM logs ORDER BY time DESC LIMIT 100;");
                            // execute query
                            $query->execute();

                            //bind results
                            $query->bind_result($userId, $timeStamp, $message, $ip);
                            
                            while ($query -> fetch()){
                                echo "
                                    <tr>
                                        <th scope='row'>$userId</td>
                                        <td>$timeStamp</td>
                                        <td>$message</td>
                                        <td>$ip</td>
                                    </tr>
                                ";
                            }

                        ?>
                    </tbody>
				</table>
				<br>
				<hr>
            </div>

			
			<div class="col-md-5"></div>

            <?php include 'std/script.php'; ?>
			<script type='text/javascript' src='js/main.js'></script>
    </body>
</html>