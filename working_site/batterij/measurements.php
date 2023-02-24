<!--
    measurements.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 13-01-2023

    The Measurements-page is part of the main pages.
    Users can choose an interval and the application will show all data
    that falls within the interval. Users can also choose the export the data.

-->

<?php 
    include "std/session.php"; 
	include 'std/dbconfiguration.php';
	include "std/sanitize.php";


?>

<!doctype html>
<html>
	<head>
		<?php 
		include 'std/head.php';
		$title = "Measurements";
		?>
		<link href='css/main.css' rel='stylesheet'>
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" href="css/mdb.min.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
	</head>
	<body className='snippet-body'>
		<body id="body-pd">

			<?php include 'std/sidebar.php'; ?>

            <div style="min-height:10vh;"></div>
                <p>
                    There is a diagram displaying the temperature, voltage and ampere for each device that has data saved, that falls within the interval.
                    You can choose to display all data in history, display a pre-determined interval or display the data you desire.
                </p>
                <div class="alert alert-primary" role="alert"><em><b>NOTE!</b></em><br>A too broad request can take a lot of time to display.</div>
                

                <?php
                    if (isset($_POST["display-specific-interval"])) {
                        $start = sanitize($_POST["start"]);
                        $end = sanitize($_POST["end"]);
                    }
                    else if (isset($_POST["display-24h"])) {
                        $_POST["start"] = date("Y-m-d\TH:i", strtotime('-24 hours'));
                        $_POST["end"] = date("Y-m-d\TH:i");

                        $start = sanitize($_POST["start"]);
                        $end = sanitize($_POST["end"]);
                    }
                    else if (isset($_POST["display-7d"])) {
                        $_POST["start"] = date("Y-m-d\TH:i", strtotime('-7 days'));
                        $_POST["end"] = date("Y-m-d\TH:i");
                        
                        $start = sanitize($_POST["start"]);
                        $end = sanitize($_POST["end"]);
                    }
                    else {
                        // default is last 24h
                        $_POST["start"] = date("Y-m-d\TH:i", strtotime('-24 hours'));
                        $_POST["end"] = date("Y-m-d\TH:i");

                        $start = sanitize($_POST["start"]);
                        $end = sanitize($_POST["end"]);
                    }

                    $start = sanitize($_POST["start"]);
                    $end = sanitize($_POST["end"]);
                ?>

                <form method="post">
                    Display data between 
                    <input type="datetime-local" name="start" value=<?php echo $_POST["start"]; ?>>
                    and
                    <input type="datetime-local" name="end" value=<?php echo $_POST["end"]; ?>>
                    &ensp;
                    <input type="submit" name="display-specific-interval" value="Display specific interval">
                    <input type="submit" name="display-24h" value="Display last 24 hours">
                    <input type="submit" name="display-7d" value="Display last 7 days">
                </form>
                <!-- possible new place for Export-All-Device button -->
                
                

                <?php include 'std/script.php'; ?>

                <script type='text/javascript' src='js/main.js'></script>
                <script type="text/javascript" src="js/mdb.min.js"></script>

                <?php

                    // connect to database
                    $mysql = new mysqli($servername, $username, $password, $dbname);

                    // query to get data from database
                    $query = $mysql->prepare("SELECT DISTINCT device_id FROM measurements WHERE time_ BETWEEN ? AND ?;");
                    $query->bind_param('ss', $start, $end);

                    // execute query
                    $query->execute();

                    //bind results
                    $query->bind_result($deviceId);

                    
                    $activeDevices = array();
                    while ($query -> fetch()){
                        array_push($activeDevices, $deviceId);
                    }


                    foreach($activeDevices as $deviceId) {

                        // get additional information from database to display
                        $mysql = new mysqli($servername, $username, $password, $dbname);
                        // query to get data from database
                        $query = $mysql->prepare("SELECT display_name, description FROM devices WHERE device_id = ?;");
                        $query->bind_param('i', $deviceId);
                        // execute query
                        $query->execute();

                        //bind results
                        $query->bind_result($display_name, $description);
                        $query -> fetch();
                        // Place canvas
                        echo "
                            <hr>
                            <h5>$display_name</h5>
                            <p>
                                $description
                            </p>
                            <div class='container'>
                                <div class='row'>
                                    <div class='col-xl-10'>
                                        <canvas id='lineChart-device-$deviceId'></canvas>
                                    </div>
                                </div>
                                <i style='font-size:10px;'>You can specify a quantity by deactivating all other. To deactivate a quantity, click the quantity in the legend.</i>
                                

                                <form action='export' method='post'>
                                    <input type='hidden' name='device_id' value='$deviceId'>
                                    <input type='hidden' name='start' value='$start'>
                                    <input type='hidden' name='end' value='$end'>
                                    <button type='submit' class='sub_link' title='Export data on specific interval from this device'>
                                        <i class='bx bx-export'></i> <span>Export</span>
                                    </button>
                                </form>
                            </div>
                        ";

                        // Get data from database
                        $mysql = new mysqli($servername, $username, $password, $dbname);
                        // query to get data from database
                        $query = $mysql->prepare("SELECT measurement_id, device_id, time_, voltage, temperature, ampere FROM measurements WHERE device_id = ? AND time_ BETWEEN ? AND ? ORDER BY time_;");
                        $query->bind_param('iss', $deviceId, $start, $end);
                        // execute query
                        $query->execute();
                        //bind results
                        $query->bind_result($measurementId, $deviceId, $time_, $voltage, $temperature, $ampere);

                        // create empty arrays
                        $timeArray = array();
                        $voltageArray = array();
                        $temperatureArray = array();
                        $ampereArray = array();

                        // fill arrays with data
                        while ($query -> fetch()){
                            array_push($timeArray, $time_);
                            array_push($voltageArray, $voltage);
                            array_push($temperatureArray,$temperature);
                            array_push($ampereArray, $ampere);
                        }

                        // js script for constructing the chart
                        echo "
                            <script type='text/javascript'>
                
                                var ctxL = document.getElementById('lineChart-device-$deviceId').getContext('2d');
                
                                var myLineChart = new Chart(ctxL, {
                                    type: 'line',
                                    data: {
                                        labels: ".json_encode($timeArray).",
                                        datasets: [
                                            {
                                                label: 'temperature',
                                                data: ".json_encode($temperatureArray).",
                                                backgroundColor: ['rgba(105, 0, 132, .2)'],
                                                borderColor: ['rgba(200, 99, 132, .7)'],
                                                borderWidth: 2
                                            },
                                            {
                                                label: 'voltage',
                                                data: ".json_encode($voltageArray).",
                                                backgroundColor: ['rgba(0, 137, 132, .2)'],
                                                borderColor: ['rgba(0, 10, 130, .7)'],
                                                borderWidth: 2
                                            },
                                            {
                                                label: 'ampere',
                                                data: ".json_encode($ampereArray).",
                                                backgroundColor:['rgba(10, 180, 132, .2)'],
                                                borderColor: ['rgba(90, 140, 180, .7)'],
                                                borderWidth: 2
                                            },
                                        ]
                                    },
                                    options: {
                                        responsive: true
                                    }
                                });
                            </script>
                        ";

                    }
                ?>
                <hr>
                <p>
                    This were all devices that has data in the specified interval.
                </p>

                <!-- regular place for Export-All-Device button -->
                <div class='container'>
                    <form action='export' method='post'>
                        <input type='hidden' name='device_id' value=''>
                        <input type='hidden' name='start' value=<?php echo $start;?>>
                        <input type='hidden' name='end' value=<?php echo $end;?>>
                        <button type='submit' class='sub_link' title='Export data on specific interval from all devices'>
                            <i class='bx bx-export'></i> <span>Export All Devices</span>
                        </button>
                    </form>
                </div>
                <br>
            </div>			
	</body>
</html>