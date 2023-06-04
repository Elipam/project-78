<!--
    users/edit.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Users can edit their profile, using this page.

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
        <title>Edit User</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">

            <?php include "../std/sidebar.php"; ?>

            <div class="height-100">

				<div style="min-height: 5vh;"></div>
				
				<?php
					//connect to database
					$mysql = new mysqli($servername, $username, $password, $dbname);
                    // sanitize input
					$userId = sanitize($_POST["user_id"]);
					// User can only be edited by Admin or the user
					$query = $mysql->prepare("SELECT admin, username FROM users WHERE user_id = ?");
					//bind paramters to query
					$query->bind_param('i', $_SESSION["user_id"]);
					//execute query
					$query->execute();
					//bind results
					$query->bind_result($admin, $username);
					
					if ($query->fetch()){
						if (!($userId == $_SESSION["user_id"] || $admin == '1')) {
							// user is not allowed to edit
							$_SESSION["message"] = "You cannot edit users because you're not an Admin.";
							header("Location: /users/all");
						}
					}
					else {
						header("Location: /users/all");
					}

					$query->close();

					//if page is accessed through save button, update record in the database
					if(isset($_POST["submit"])){
						
						$username = sanitize($_POST["username"]);
						$name = sanitize($_POST["name"]);
						$email = sanitize($_POST["email"]);						

						//prepare sql statement and bin parameters
						$query = $mysql->prepare("UPDATE users SET username = ?, name = ?, email = ? WHERE user_id = ?;");
						$query->bind_param('sssi', $username, $name, $email, $userId);				

						//execute query
						$query->execute();
						
                        // set message
						$_SESSION["message"] = "Your modifications to User: " . $name . " have been saved";
						
						// write log
                        $userName = $_SESSION["username"];
                        $user_Id = $_SESSION["user_id"];
                        logEvent($mysql, "The user '$userName' ($user_Id) has edited the user '$username' ($userId).");

						// go back to users
						header("Location:/users/all");
					}
				
					//if page is accessed for first time, display edit fields with relevant data
					if(!isset($_POST["submit"])){

						//prepare sql statement
						$query = $mysql->prepare("SELECT admin, username, name, email FROM users WHERE user_id = ?");
						//bind paramters to query
						$query->bind_param('i', $userId);
						//execute query
						$query->execute();
						//bind results
						$query->bind_result($admin, $username, $name, $email);

						if($query->fetch()){
							echo "<h1 class='text-center'>Edit: ", $username, "</h1>";
						}
					}

				?>
				
				<div style="min-height: 12vh;"></div>

				<form action="/users/edit" method="post">
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Username</label>
								<input type="text" class="form-control form-control-lg" name="username" value="<?php echo $username; ?>">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Name</label>
								<input type="text" class="form-control form-control-lg" name="name" value="<?php echo $name; ?>">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>

					<div class="row">
						<div class="col-md-2"></div>
						
						<div class="col-lg-8">
							<div class="form-group">
								<label style="font-size: 1.6em;">Email</label>
								<input type="text" class="form-control form-control-lg" name="email" value="<?php echo $email; ?>">
				  			</div>
						</div>
						
						<div class="col-md-2"></div>
					
					</div>
					
					<div style="min-height: 5vh;"></div>
					
					<div style="min-height: 8vh;"></div>
					<input type="hidden" name="user_id" value="<?php echo $userId; ?>">
					
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