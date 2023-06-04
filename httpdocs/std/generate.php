<?php
    /*
        std/generate.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        PHP-function that generates a new API-key and check if it already exists.
    */

function generate($mysql){
	
	//possible characters for api KEY
	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$charactersLength = strlen($characters);

	while(true){

		$apiKey = '';

		//generate api key
		for ($i = 0; $i < 10; $i++) {
			$apiKey .= $characters[rand(0, $charactersLength - 1)];
		}

		$apiKeyHash = hash("sha256", $apiKey);

		//prepare sql statement
		$query = $mysql->prepare("SELECT device_id, display_name, api_key, description, image FROM devices WHERE api_key = ?");

		//bind paramters to query
		$query->bind_param('s', $apiKeyHash);

		//execute query
		$query->execute();

		//bind results
		$query->bind_result($deviceId, $displayName, $apiKey, $description, $image);

		if(!$query->fetch()){
			return $apiKey;
		}
	}
	
}


?>