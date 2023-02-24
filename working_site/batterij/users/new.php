<!--
    users/new.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Admins can create new users, using this page.

-->

<?php 
	

?>

<!doctype html>
<html>
    <head>
        <?php 
            include "../std/dbconfiguration.php";
            include "../std/head.php";
            include "../std/logEvent.php";
            include "../std/sanitize.php";
            include "../std/session.php";
            $title = "Users";
        ?>

		<link href='../css/main.css' rel='stylesheet'>		
        <title>Add New User</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">

            <?php include "../std/sidebar.php"; ?>

            <div class="height-100">

				<div style="min-height: 5vh;"></div>
				
				<?php
					//connect to database
					$mysql = new mysqli($servername, $username, $password, $dbname);
					// Only Admins can create new users
					$query = $mysql->prepare("SELECT admin, username FROM users WHERE user_id = ?");
					//bind paramters to query
					$query->bind_param('i', $_SESSION["user_id"]);
					//execute query
					$query->execute();
					//bind results
					$query->bind_result($admin, $userName);
					
                    // check if user is admin
					if ($query->fetch()){
						if ($admin != '1') {
							// user is not allowed to edit
							$_SESSION["message"] = "You cannot create new users because you're not an Admin.";
							header("Location: /users/all");
						}
					}
					else {
						header("Location: /users/all");
					}

					$query->close();
					
					//if page is accessed through save button, update record in the database
					if(isset($_POST["submit"])){
						
						//connect to database
						$mysql = new mysqli($servername, $username, $password, $dbname);
						//define variables
						$userName = sanitize($_POST["username"]);
						$name = sanitize($_POST["name"]);
						$email = sanitize($_POST["email"]);
						$admin = 0; // users zijn standaard geen admin
						//prepare sql statement and bind parameters
						$query = $mysql->prepare("INSERT INTO users (admin, username, name, email) VALUES (?, ?, ?, ?);");
						$query->bind_param('isss', $admin, $userName, $name, $email);					

						//execute query
						$query->execute();
						$query->close();

                        //prepare sql statement and bind parameters
						$query = $mysql->prepare("SELECT user_id FROM users WHERE username = ?;");
						$query->bind_param('s', $userName);					
						//execute query
						$query->execute();
                        $query->bind_result($userId);
                        $query->fetch();
						$query->close();

						// write log
                        $user_Name = $_SESSION["username"];
                        $user_Id = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$user_Name' ($user_Id) has created the user '$userName' ($userId).");
						
						//prepare sql statement and bind parameters
						$query = $mysql->prepare("SELECT user_id, admin, username, password, name, email FROM users WHERE name = ? AND email = ? AND username = ?");
						$query->bind_param('sss', $name, $email, $userName);					

						//execute query
						$query->execute();
						
						$query->bind_result($userId, $a, $b, $c, $d, $e);
						
						$query->fetch();
						
						$query->close();

						//possible characters for reset_id
						$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
						  
						$charactersLength = strlen($characters);
						  
						$resetId = '';
						  
						//generate reset Id
						for ($i = 0; $i < 32; $i++) {
							$resetId .= $characters[rand(0, $charactersLength - 1)];
						}
						
						$url = "https://" . $_SERVER['SERVER_NAME'] . "/resetPassword.php?reset-key=" . $resetId;
						
						$expiry = new DateTime();
						$expiry = $expiry->add(new DateInterval('PT18H'))->format('Y-m-d H:i:s');
						
						//prepare sql statement
						$query = $mysql->prepare("INSERT INTO reset_password VALUES(?, ?, ?)");

						//bind paramters to query
						$query->bind_param('ssi', $resetId, $expiry, $userId);

						//execute query
						$query->execute();

						$subject = "Set Password VRFB";
						$txt = "Please follow this link to set your password\n" . $url;
						$headers = "From: no-reply@vrfb-test.nl" . "\r\n";

						mail($email,$subject,$txt,$headers);
						
						$_SESSION["message"] = "User: " . $name . " with username: " . $userName . " has been created. An email has been sent to " . $email;

						
						// write log
						// logEvent.php already included

						logEvent($mysql, "An email has been send to '".$email."' to create a password.");

						header("Location: /users/all");
					}
				?>

				<h1 class='text-center'>Add New User</h1>
				
				<div style="min-height: 6vh;"></div>

				<form action="/users/new" method="post">
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Username</label>
								<input type="text" class="form-control form-control-lg" name="username">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label for="description" style="font-size: 1.6em;">Name</label>
								<input type="text" class="form-control form-control-lg" name="name">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>

					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label for="description" style="font-size: 1.6em;">Email</label>
								<input type="text" class="form-control form-control-lg" name="email">
				  			</div>
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
									<span>Save User</span>
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