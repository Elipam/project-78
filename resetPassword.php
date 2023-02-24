<!--
    resetPassword.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Users can reset their password with this page.

-->

<?php

include './std/sanitize.php';
include './std/dbconfiguration.php';
include './std/logEvent.php';

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 900);
// hier wordt een sessie gestart
session_start();

ini_set('display_errors', 1);

//connect to database
$mysql = new mysqli($servername, $username, $password, $dbname);

if(isset($_GET["reset-key"])){
	
	$resetKey = sanitize($_GET["reset-key"]);
	
}
elseif(isset($_POST["reset-key"])){
	$resetKey = sanitize($_POST["reset-key"]);
}

?>

<!doctype html>
<html class="no-js" lang="">

<head>
  <meta charset="utf-8">
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">

  <!-- <link rel="stylesheet" href="css/normalize.css"> -->
  <link rel="stylesheet" href="css/login.css">

  <link rel="icon" href="./favicon.ico">

  <meta name="theme-color" content="#fafafa">
</head>

<body>

  <div class="container-fluid ps-md-0">
    <div class="row g-0">
      <div class="d-none d-md-flex col-md-4 col-lg-6 bg-image"></div>
      <div class="col-md-8 col-lg-6">
        <div class="login d-flex align-items-center py-5">
          <div class="container">
            <div class="row">
              <div class="col-md-9 col-lg-8 mx-auto">
                <!-- <h3 class="login-heading mb-4">Please Login to Continue</h3> -->
                <div class="jumbotron">
                	<h1 class="text-center">Password Reset</h1>
						<?php
							if(isset($resetKey)){
								echo "<p>Please choose a new password with a minimum length of 16 characters</p>";
							}
							else{
								echo "<p>Please enter your email connected to your account, you will receive instructions for resetting your password</p>";
							}
						?>
					<p></p>
                </div>
  
                <!-- Sign In Form -->
                <form action="resetPassword.php" method="post">
					
					<?php
				  
						if(!isset($resetKey)){
							
							echo '
								<div class="form-floating mb-3">
									<input type="email" class="form-control" id="floatingInput" placeholder="email" name="email">
									<label for="floatingInput">email</label>
							  	</div>
							';
						}
						else{
							
							echo'
							
								<div class="form-floating mb-3">
									<input type="password" minLength="16" class="form-control" id="floatingPassword" placeholder="Password" name="password">
									<label for="floatingPassword">Password</label>
								</div>
								<input type="hidden" name="reset-key" value="', $resetKey, '">
								<div class="form-floating mb-3">
									<input type="password" class="form-control" id="floatingPassword" placeholder="Repeat Password" name="passwordRepeat">
									<label for="floatingPassword">Password</label>
								</div>
							
							';
							
						}
				  
				  	?>

                  <br>
  
                  <div class="d-grid">
                    <button class="btn btn-lg btn-primary btn-login text-uppercase fw-bold mb-2" type="submit" name="send">Reset Password</button>
                  </div>
  
                </form>
				  
				  
                <?php
		
					// Check whether link in the email was pressed and new passwords were provided
					if (isset($_POST["send"]) && isset($_POST["reset-key"])) {

						// retrieve given username and password and sanitize input
						$passwd = sanitize($_POST["password"]);
						$passwdRepeat = sanitize($_POST["passwordRepeat"]);
						
						//check passwords are equal
						if($passwd != $passwdRepeat){
							//if login details are not correct display following message
							echo "
										  <br>
										  <br>
										  <div class=\"jumbotron\">
											<h3 class=\"text-center\">Given passwords do not match, please make sure you enter the same password twice</h3>
										  </div>";
						}
						else{
							//get current expiry date 
							$now = (new DateTime())->format('Y-m-d H:i:s');
							
							//prepare sql statement
							$query = $mysql->prepare("SELECT reset_id, expiry, user_id FROM reset_password WHERE reset_id = ? AND expiry > ?");

							//bind paramters to query
							$query->bind_param('ss', $resetKey, $now);

							//execute query
							$query->execute();

							//bind results
							$query->bind_result($resetKey, $expiry, $userId);
							

							//if login details are correct redirect to home page 
							if($query->fetch()) {
								
								$query->close();
								
								// create hash from password
								$passwordHash = hash("sha256", $passwd);

								//prepare sql statement
								$query = $mysql->prepare("UPDATE users SET password = ? WHERE user_id = ?");

								//bind paramters to query
								$query->bind_param('si', $passwordHash, $userId);

								//execute query
								$query->execute();
								
								$query->close();
								
								//prepare sql statement
								$query = $mysql->prepare("DELETE FROM reset_password WHERE reset_id = ?");

								//bind paramters to query
								$query->bind_param('s', $resetKey);

								//execute query
								$query->execute();

                                $query->close();
								
								logEvent($mysql, "An user ($userId) has reset their password.");

								echo "
									  <br>
									  <br>
									  <div class=\"jumbotron\">
										<h3 class=\"text-center\">Your password has been reset</h3>
                                        <h5 class=\"text-center\">Please click <a href = '/login'>here</a> to go back to the home screen</h5>
									  </div>";
									
							}
							else{
								echo "
										  <br>
										  <br>
										  <div class=\"jumbotron\">
											<h3 class=\"text-center\">This link has expired please request a new link from the main login page</h3>
										  </div>";
							}
							
						}
					}
				  //check whether email was provided for reset request
				  	elseif(isset($_POST["send"])){
					  
					  $email = sanitize($_POST["email"]);
					  
					  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						  echo "
										  <br>
										  <br>
										  <div class=\"jumbotron\">
											<h3 class=\"text-center\">provided email is not valid</h3>
										  </div>";
					  }
					  else{
						  //possible characters for reset_id
						  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
						  
						  $charactersLength = strlen($characters);
						  
						  $resetId = '';
						  
						  //generate reset Id
						  for ($i = 0; $i < 32; $i++) {
							  $resetId .= $characters[rand(0, $charactersLength - 1)];
						  }
						  
						  $url = "https://" . $_SERVER['SERVER_NAME'] . "/resetPassword.php?reset-key=" . $resetId;
						  
						  //prepare sql statement
						  $query = $mysql->prepare("SELECT user_id, admin, username, password, name, email FROM users WHERE email = ?");

						  //bind paramters to query
						  $query->bind_param('s', $email);

						  //execute query
						  $query->execute();
						  
						  $query->bind_result($userId, $adminh, $uname, $pwd, $name, $email);
						  
						  if($query->fetch()){
							  
							  $expiry = new DateTime();
							  $expiry = $expiry->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s');

							  $query->close();

							  //prepare sql statement
							  $query = $mysql->prepare("INSERT INTO reset_password VALUES(?, ?, ?)");

							  //bind paramters to query
							  $query->bind_param('ssi', $resetId, $expiry, $userId);

							  //execute query
							  $query->execute();

							  $subject = "Reset Password";
							  $txt = "Please go to the following link to reset your password:\n" . $url;
							  $headers = "From: no-reply@vrfb-test.nl" . "\r\n";
							  mail($email,$subject,$txt,$headers);

							  echo "
											  <br>
											  <br>
											  <div class=\"jumbotron\">
												<h3 class=\"text-center\">An email has been sent to ", $email , "</h3>
											  </div>";
							  
						  }
						  else{
							  echo "
										  <br>
										  <br>
										  <div class=\"jumbotron\">
											<h3 class=\"text-center\">provided email is not connected to any known user</h3>
										  </div>";
						  }
						  
						  
						  
						  
					  }
				
					  
				  }



				?>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
  <script src="js/passMeter.js"></script>
  <script src="js/vendor/modernizr-3.11.2.min.js"></script>
  <script src="js/plugins.js"></script>
  <script src="js/main.js"></script>
</body>

</html>