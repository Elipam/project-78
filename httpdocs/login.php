<!--
    login.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Users that are not logged in, are redirected to this page.
    On this page, users have to provide a valid combination of username and password.
    Users are authenicated and authorized. 

-->

<?php
	include './std/sanitize.php';
	include './std/dbconfiguration.php';
	// include './std/logEvent.php';

	// server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 900);
	// hier wordt een sessie gestart
	session_start();
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
                  <h1 class="text-center">VANADIUM REDOX FLOW BATTERY</h1>
                  <p class="text-center">Please login to continue</p>
                </div>
  
                <!-- Sign In Form -->
                <form action="login" method="post">
                  <div class="form-floating mb-3">
                    <input type="username" class="form-control" id="floatingInput" placeholder="User" name="username">
                    <label for="floatingInput">Username</label>
                  </div>
                  <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
                    <label for="floatingPassword">Password</label>
                  </div>

                  <br>
  
                  <div class="d-grid">
                    <button class="btn btn-lg btn-primary btn-login text-uppercase fw-bold mb-2" type="submit" name="send">Sign in</button>
                    <div class="text-center">
                      <a class="small" href="/resetPassword">Forgot password?</a>
                    </div>
                  </div>
  
                </form>
				  
                <?php
					// Check whether login request was made
					if (isset($_POST["send"])) {

						// retrieve given username and password and sanitize input
						$usr = sanitize($_POST["username"]);
						$passwd = sanitize($_POST["password"]);
						
						// create hash from password
						$passwordHash = $passwd;
						
						//connect to database
						$mysql = new mysqli($servername, $username, $password, $dbname);

						//prepare sql statement
						$query = $mysql->prepare("SELECT user_id, admin, username, password, name, email FROM users WHERE username = ?");

						//bind paramters to query
						$query->bind_param('s', $usr);

						//execute query
						$query->execute();

						//bind results
						$query->bind_result($userId, $admin, $userName, $passwd, $name, $email);

						//if login details are correct redirect to home page 
						while ($query->fetch()) {
							if($passwd == $passwordHash){
								$_SESSION["login"] = 1;
								$_SESSION["username"] = $userName;
								$_SESSION["user_id"] = $userId;
								$_SESSION["admin"] = $admin;

                // write log
                include "std/logEvent.php";

                $mysql = new mysqli($servername, $username, $password, $dbname);
                logEvent($mysql, "The user '$userName' ($userId) has logged in.");

								// go to home-page
								header("Location:home");
                
              }
						}
						
						//if login details are not correct display following message
						echo "
										  <br>
										  <br>
										  <div class=\"jumbotron\">
											<h3 class=\"text-center\">Password is incorrect, please try again</h3>
										  </div>";
					}

					// if login page is visited when device is already logged in, redirect to home page
					if (isset($_SESSION["login"])) {
						if ($_SESSION["login"] == 1) {
							header("Location:./home");
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
  <script src="js/vendor/modernizr-3.11.2.min.js"></script>
  <script src="js/plugins.js"></script>
  <script src="js/main.js"></script>
</body>

</html>


