<!--
    home.php

    CMI-TI 22 TINPRJ0456
    Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
    Last edited: 02-12-2022

    The home-page is part of the main pages.
    Users can see stats about devices and the user manual. 
    Admins can see additional stats about users.

-->

<?php include "std/session.php"; ?>
<!doctype html>
<html>
    <head>
        <?php 
            include 'std/head.php'; 
            include 'std/dbconfiguration.php';
            $title = "Home";
        ?>
		<link href='css/main.css' rel='stylesheet'>
        <title>Home</title>
    </head>
    <body className='snippet-body'>
        <body id="body-pd">
            
            <?php include 'std/sidebar.php'; ?>

            <div class="height-100 bg-light">
                <h4>Dashboard</h4>
                <div class="row">

                <div class="card mb-3 shadow-lg col-xl-4" style="padding: 10px; margin: 10px;">
                    <h5>24-Hour active device report</h5>

                    <?php
                        // get devices from database
                        $mysql = new mysqli($servername, $username, $password, $dbname);
                        // query to get data from database

                        // get number of records form database in last 24 hours
                        $oneDayAgo = date("Y-m-d H:i:s", strtotime('-24 hours'));

                        $query = $mysql->prepare("SELECT devices.display_name, COUNT(measurements.measurement_id) FROM devices
                            LEFT JOIN measurements ON devices.device_id = measurements.device_id
                            WHERE measurements.time_ >= ?
                            GROUP BY devices.display_name;");

                        $query->bind_param('s', $oneDayAgo);
                        // execute query
                        $query->execute();
                        //bind results
                        $query->bind_result($display_name, $noOfRecords);

                        // create empty arrays
                        $deviceRecordCount = array();
                        $deviceNamesArray = array();

                        $deviceColorArray = array();

                        // fill arrays with data
                        while ($query -> fetch()) {

                            array_push($deviceNamesArray, $display_name);
                            array_push($deviceRecordCount, $noOfRecords);

                            // generate random color for device
                            array_push($deviceColorArray, "#".substr(md5(rand()), 0, 6));
                            
                        }

                        $query->close();

                        if(count($deviceNamesArray) > 0) {
                            echo "
                                <p>
                                    The following devices were active in the last 24 hours:
                                </p>
                                <canvas id='24h-device-report'></canvas>
                            ";
                        }
                        else {
                            echo "
                                <p>
                                    There were no devices active in the last 24 hours!
                                </p>
                            ";
                        }
                    ?>

                    <?php include 'std/script.php'; ?>
                    <script type="text/javascript" src="js/mdb.min.js"></script>
                    <script type="text/javascript">
                        
                    var conn = new WebSocket('ws://192.168.2.11:1000');
                        var ctxP = document.getElementById("24h-device-report").getContext('2d');
                        var myPieChart = new Chart(ctxP, {
                            plugins: [ChartDataLabels],
                            type: 'pie',
                            data: {
                                labels: <?php echo json_encode($deviceNamesArray);?>,
                                datasets: [{
                                    data: <?php echo json_encode($deviceRecordCount);?>,
                                    backgroundColor: <?php echo json_encode($deviceColorArray);?>,
                                    hoverBackgroundColor: []
                                }]
                            },
                            options: {
                                responsive: true,
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 20,
                                        boxWidth: 10
                                    }
                                },
                                plugins: {
                                    datalabels: {
                                        formatter: (value, ctx) => {
                                            let sum = 0;
                                            let dataArr = ctx.chart.data.datasets[0].data;
                                            dataArr.map(data => {
                                                sum += data;
                                            });
                                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                                            return percentage;
                                        },
                                        color: 'white',
                                        labels: {title: {font: {size: '10'}}}
                                    }
                                }
                            }
                        });
                    </script>
                </div>

                <div class="card mb-3 shadow-lg col-xl-4" style="padding: 10px; margin: 10px;">
                    <h5>7-Day active device report</h5>
                    
                    <?php
                        // get devices from database
                        $mysql = new mysqli($servername, $username, $password, $dbname);
                        // query to get data from database

                        // get number of records form database in last 7 days
                        $oneWeekAgo = date("Y-m-d H:i:s", strtotime('-7 days'));

                        $query = $mysql->prepare("SELECT devices.display_name, COUNT(measurements.measurement_id) FROM devices
                            LEFT JOIN measurements ON devices.device_id = measurements.device_id
                            WHERE measurements.time_ >= ?
                            GROUP BY devices.display_name;");

                        $query->bind_param('s', $oneWeekAgo);
                        // execute query
                        $query->execute();
                        //bind results
                        $query->bind_result($display_name, $noOfRecords);

                        // create empty arrays
                        $deviceRecordCount = array();
                        $deviceNamesArray = array();

                        $deviceColorArray = array();

                        // fill arrays with data
                        while ($query -> fetch()) {

                            array_push($deviceNamesArray, $display_name);
                            array_push($deviceRecordCount, $noOfRecords);

                            // generate random color for device
                            array_push($deviceColorArray, "#".substr(md5(rand()), 0, 6));
                            
                        }

                        $query->close();

                        if (count($deviceNamesArray) > 0) {
                            echo "
                                <p>
                                    The following devices were active in the last 7 days:
                                </p>
                                <canvas id='7d-device-report'></canvas>
                            ";
                        }
                        else {
                            echo "
                                <p>
                                    There were no devices active in the last 7 days!
                                </p>
                            ";
                        }
                    ?>


                    <?php include 'std/script.php'; ?>
                    <script type="text/javascript" src="js/mdb.min.js"></script>
                    <script type="text/javascript">
            
                        var ctxP = document.getElementById("7d-device-report").getContext('2d');
                        var myPieChart = new Chart(ctxP, {
                            plugins: [ChartDataLabels],
                            type: 'pie',
                            data: {
                                labels: <?php echo json_encode($deviceNamesArray);?>,
                                datasets: [{
                                    data: <?php echo json_encode($deviceRecordCount);?>,
                                    backgroundColor: <?php echo json_encode($deviceColorArray);?>,
                                    hoverBackgroundColor: []
                                }]
                            },
                            options: {
                                responsive: true,
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 20,
                                        boxWidth: 10
                                    }
                                },
                                plugins: {
                                    datalabels: {
                                        formatter: (value, ctx) => {
                                            let sum = 0;
                                            let dataArr = ctx.chart.data.datasets[0].data;
                                            dataArr.map(data => {
                                                sum += data;
                                            });
                                            let percentage = (value * 100 / sum).toFixed(2) + "%";
                                            return percentage;
                                        },
                                        color: 'white',
                                        labels: {title: {font: {size: '10'}}}
                                    }
                                }
                            }
                        });
                    </script>
                </div>

                
                <!-- <div class="card mb-3 shadow-lg col-xl-4" style="padding: 10px; margin: 10px;">
                    <h5>User Manual</h5>
                    <a target="_blank" href="/documents/User_manual.pdf">
                        <div class="card-body">
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. ...</p>
                        </div>
                        <div class="col-md-4">
                            <img style="max-width: 150px;" src="/img/document.png" class="card-img" alt="...">
                        </div>
                    </a>
                </div> -->
                

                
                <?php if($_SESSION["admin"] == "1") { ?>
                    <div class="card mb-3 shadow-lg col-xl-4" style="padding: 10px; margin: 10px;">
                        <h5>7-Day usage report</h5>
                        
                        <?php
                            // get devices from database
                            $mysql = new mysqli($servername, $username, $password, $dbname);
                            // query to get data from database

                            // get number of records form database in last 7 days
                            $oneWeekAgo = date("Y-m-d H:i:s", strtotime('-7 days'));

                            $query = $mysql->prepare("SELECT users.username, COUNT(logs.user_id) FROM users
                            LEFT JOIN logs ON users.user_id = logs.user_id
                            WHERE logs.time >= ? AND logs.message LIKE '%logged in%'
                            GROUP BY users.username;");

                            $query->bind_param('s', $oneWeekAgo);
                            // execute query
                            $query->execute();
                            //bind results
                            $query->bind_result($usernameActiveUser, $noOfRecords);

                            // create empty arrays
                            $userLoginsCount = array();
                            $usernameArray = array();

                            $userColorArray = array();

                            // fill arrays with data
                            while ($query -> fetch()) {
                                
                                array_push($usernameArray, $usernameActiveUser);
                                array_push($userLoginsCount, $noOfRecords);

                                // generate random color for device
                                array_push($userColorArray, "#".substr(md5(rand()), 0, 6));
                                
                            }

                            $query->close();

                            if (count($usernameArray) > 0) {
                                echo "
                                    <p>
                                        The following users have logged in, in the last 7 days:
                                    </p>
                                    <canvas id='7d-user-report'></canvas>
                                    <small class='text-muted'>This dashboard item is only visible for admins.</small>
                                ";
                            }
                            else {
                                echo "
                                    <p>
                                        There were no users that logged in, in the last 7 days!
                                    </p>
                                    <small class='text-muted'>This dashboard item is only visible for admins.</small>
                                ";
                            }
                        ?>


                        <?php include 'std/script.php'; ?>
                        <script type="text/javascript" src="js/mdb.min.js"></script>
                        <script type="text/javascript">
                
                            var ctxP = document.getElementById("7d-user-report").getContext('2d');
                            var myPieChart = new Chart(ctxP, {
                                plugins: [ChartDataLabels],
                                type: 'pie',
                                data: {
                                    labels: <?php echo json_encode($usernameArray);?>,
                                    datasets: [{
                                        data: <?php echo json_encode($userLoginsCount);?>,
                                        backgroundColor: <?php echo json_encode($userColorArray);?>,
                                        hoverBackgroundColor: []
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            padding: 20,
                                            boxWidth: 10
                                        }
                                    },
                                    plugins: {
                                        datalabels: {
                                            formatter: (value, ctx) => {
                                                let sum = 0;
                                                let dataArr = ctx.chart.data.datasets[0].data;
                                                dataArr.map(data => {
                                                    sum += data;
                                                });
                                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                                return percentage;
                                            },
                                            color: 'white',
                                            labels: {title: {font: {size: '10'}}}
                                        }
                                    }
                                }
                            });
                        </script>
                    </div>
                <?php } ?>

                <?php if($_SESSION["admin"] == "1") { ?>
                    <div class="card mb-3 shadow-lg col-xl-4" style="padding: 10px; margin: 10px;">
                        <h5>7-Day usage report</h5>
                        
                        <?php
                            // get devices from database
                            $mysql = new mysqli($servername, $username, $password, $dbname);
                            // query to get data from database

                            // get number of records form database in last 7 days
                            $oneWeekAgo = date("Y-m-d H:i:s", strtotime('-7 days'));

                            $query = $mysql->prepare("SELECT users.username, COUNT(logs.user_id) FROM users
                            LEFT JOIN logs ON users.user_id = logs.user_id
                            WHERE logs.time >= ? AND NOT(logs.message LIKE '%logged in%')
                            GROUP BY users.username;");

                            $query->bind_param('s', $oneWeekAgo);
                            // execute query
                            $query->execute();
                            //bind results
                            $query->bind_result($usernameActiveUser, $noOfRecords);

                            // create empty arrays
                            $userLoginsCount = array();
                            $usernameArray = array();

                            $userColorArray = array();

                            // fill arrays with data
                            while ($query -> fetch()) {
                                
                                array_push($usernameArray, $usernameActiveUser);
                                array_push($userLoginsCount, $noOfRecords);

                                // generate random color for device
                                array_push($userColorArray, "#".substr(md5(rand()), 0, 6));
                                
                            }

                            $query->close();

                            if (count($usernameArray) > 0) {
                                echo "
                                    <p>
                                        The following users have made changes in the last 7 days:
                                    </p>
                                    <canvas id='7d-user-action-report'></canvas>
                                    <small class='text-muted'>This dashboard item is only visible for admins.</small>
                                ";
                            }
                            else {
                                echo "
                                    <p>
                                        There were no users that made changes in the last 7 days!
                                    </p>
                                    <small class='text-muted'>This dashboard item is only visible for admins.</small>
                                ";
                            }
                        ?>


                        <?php include 'std/script.php'; ?>
                        <script type="text/javascript" src="js/mdb.min.js"></script>
                        <script type="text/javascript">
                
                            var ctxP = document.getElementById("7d-user-action-report").getContext('2d');
                            var myPieChart = new Chart(ctxP, {
                                plugins: [ChartDataLabels],
                                type: 'pie',
                                data: {
                                    labels: <?php echo json_encode($usernameArray);?>,
                                    datasets: [{
                                        data: <?php echo json_encode($userLoginsCount);?>,
                                        backgroundColor: <?php echo json_encode($userColorArray);?>,
                                        hoverBackgroundColor: []
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            padding: 20,
                                            boxWidth: 10
                                        }
                                    },
                                    plugins: {
                                        datalabels: {
                                            formatter: (value, ctx) => {
                                                let sum = 0;
                                                let dataArr = ctx.chart.data.datasets[0].data;
                                                dataArr.map(data => {
                                                    sum += data;
                                                });
                                                let percentage = (value * 100 / sum).toFixed(2) + "%";
                                                return percentage;
                                            },
                                            color: 'white',
                                            labels: {title: {font: {size: '10'}}}
                                        }
                                    }
                                }
                            });
                        </script>
                    </div>
                <?php }; ?>


            </div>
            
            <hr>

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
                
        </div>
        <script type='text/javascript' src='js/main.js'></script>
    </body>
</html>