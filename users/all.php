<!--
    users/all.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    The users-page is part of the main pages.
    Users can edit their profile. Admins can see all users, edit their profiles, delete users and create new users. 

-->

<!doctype html>
<html>
    <head>
        <?php
			include "../std/dbconfiguration.php";
            include "../std/head.php";
			include "../std/sanitize.php";
            include "../std/session.php";
            $title = "Users";
        ?>

		<link href='../css/main.css' rel='stylesheet'>
        <title>Users</title>
    </head>

    <body className='snippet-body'>
        <body id="body-pd">
            
            <?php include '../std/sidebar.php'; ?>

            <div class="height-100 bg-light">
				<p>
					This is a list with all users that can access the data. Users can only see and edit their own profile.
					Only Administrators can edit other users, remove users and add new users.
				</p>
				
				<?php
					//connect to database
					$mysql = new mysqli($servername, $username, $password, $dbname);
					//prepare sql statement
					$query = $mysql->prepare("SELECT user_id, admin, username, name, email FROM users LIMIT 1000;");
					//execute query
					$query->execute();
					//bind results
					$query->bind_result($user_id, $admin, $username, $name, $email);
				
                    // display message if set
					if(isset($_SESSION["message"])){
						echo '<div class="alert alert-primary" role="alert">', $_SESSION["message"], '</div>';
						unset($_SESSION["message"]);
					}

					//display all users on page
					while ($query->fetch()) {
						// if user is an admin, display 'Administrator'
						if ($admin == '1') {
							$adminText = "is an Administrator";
						}
						else {
							$adminText = "";
						}

						// if user is you, display 'you'
						if ($user_id == $_SESSION["user_id"]) {
							$you = "<em><b>You</b></em>";
						}
						else {
							$you = "";
						}

                        if ($user_id == $_SESSION["user_id"]) {
                            // display only if user is you or you are admin
                            echo '				
                                <div class="card mb-3 shadow-lg">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-md-2">
                                            <img style="max-width: 150px;" src="../img/user.png" class="card-img" alt="...">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h2 class="card-title">'.$name.' ('.$username.') '.$you.'</h2>
                                                <p class="card-text">'.$email.'</p>
                                                <p class="card-text"><small class="text-muted">'.$adminText.'</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <form class="sub_link" action="edit" method="post">
                                                <input type="hidden" name="user_id" value="'.$user_id.'">
                                                <button type="submit" class="sub_link">
                                                    <i class="bx bx-edit"></i> <span>Edit</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                        elseif ($_SESSION["admin"]) {
                            // display only if user is you or you are admin
                            echo '				
                                <div class="card mb-3 shadow-lg">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-md-2">
                                            <img style="max-width: 150px;" src="../img/user.png" class="card-img" alt="...">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h2 class="card-title">'.$name.' ('.$username.') '.$you.'</h2>
                                                <p class="card-text">'.$email.'</p>
                                                <p class="card-text"><small class="text-muted">'.$adminText.'</small></p>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <form class="sub_link" action="edit" method="post">
                                                <input type="hidden" name="user_id" value="'.$user_id.'">
                                                <button type="submit" class="sub_link">
                                                    <i class="bx bx-edit"></i> <span>Edit</span>
                                                </button>
                                            </form>
                                            <br>
                                            <form class="sub_link" action="remove" method="post">
                                                <input type="hidden" name="user_id" value="'.$user_id.'">
                                                <button type="submit" class="sub_link">
                                                    <i class="bx bx-trash"></i> <span>Remove</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
					}

                    // if user is an admin
                    if ($_SESSION["admin"]) {
                        echo '
                            <a href="/users/new">
                                <div class="card mb-3 shadow-lg add_new_device">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-md-2">
                                            <img style="max-width: 150px;" src="../img/addUser.png" class="card-img" alt="...">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h2 class="card-title">Add new User</h2>
                                                <p class="card-text">Click here to add a new user</p>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                        </div>
                                    </div>
                                </div>
                            </a>
                        ';
                    }
				?>
				
				<div style="min-height: 4vh;"></div>
            </div>

            <!-- scripts -->
            <?php include '../std/script.php'; ?>
			<script type='text/javascript' src='../js/main.js'></script>
    </body>
</html>