<!--
    users/remove.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    Script can only be used by admins. It deletes specific user.

-->

<?php 
	include "../std/session.php";
	include "../std/sanitize.php";
	include "../std/dbconfiguration.php";
    include "../std/logEvent.php";

	$userId = sanitize($_POST["user_id"]);
						
	//connect to database
	$mysql = new mysqli($servername, $username, $password, $dbname);

	// Only Administrators can remove users
	$query = $mysql->prepare("SELECT admin FROM users WHERE user_id = ?;");
	//bind paramters to query
	$query->bind_param('i', $_SESSION["user_id"]);
	
	//execute query
	$query->execute();
	
	//bind results
	$query->bind_result($admin);
	
	if ($query->fetch()){
		if ($admin == '1') {
			if ($_SESSION["user_id"] != $userId) {
				// Only admins can delete users. Admins cannot delete themself

				$query->close();

                $query = $mysql->prepare("SELECT username FROM users WHERE user_id = ?;");
                //bind paramters to query
				$query->bind_param('i', $userId);
                
                //execute query
                $query->execute();
                
                //bind results
                $query->bind_result($userName);

                $query->fetch();

                $query->close();

				//prepare sql statement
				$query = $mysql->prepare("DELETE FROM users WHERE user_id = ?;");
				
				//bind parameters to sql statement
				$query->bind_param('i', $userId);
							
				//execute query
				$query->execute();

				$_SESSION["message"] = "User has been removed";

				// write log
                $user_Name = $_SESSION["username"];
                $user_Id = $_SESSION["user_id"];
                logEvent($mysql, "The user '$user_Name' ($user_Id) has deleted the user '$userName' ($userId).");
				
				// go back to users
				header("Location: /users/all");
			}
			else {
				$query->close();

				$_SESSION["message"] = "You cannot delete yourself.";
				
				// go back to users
				header("Location: /users/all");
			}
			
		}
		else {
			$_SESSION["message"] = "You cannot remove users because you're not an Admin.";

			header("Location: /users/all");
		}
	}
	else {
		header("Location: /users/all");
	}

	

?>