<?php
    /*
        std/sideBar.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 13-01-2023

        html header and navigator element for the sidebar.
    */
?>

<header class="header" id="header">
    <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i></div>
    <?php 
        echo '<div style="font-size: 2.5em;">', $title, '</div>';
        echo '<div style="float: right; font-size: 2em; text-decoration:none;"><a title="Click to go to User profiles" href="/users/all">Profile of ', $_SESSION["username"], '</a></div>';
    ?>
</header>
<div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="#" class="nav_logo"> <i class='bx bx-layer nav_logo-icon'></i> <span class="nav_logo-name">VRFB Dashboard</span> </a>
            <div class="nav_list"> 
                <a href="/home" class="nav_link <?php if($title == 'Home'){echo 'active';} ?>"> 
                    <i class='bx bx-grid-alt nav_icon'></i> 
                    <span class="Home">Home</span> 
                </a> 
                <a href="/measurements" class="nav_link <?php if($title == 'Measurements'){echo 'active';} ?>"> 
                    <i class='bx bx-bar-chart-alt-2 nav_icon'></i> 
                    <span class="Measurements">Measurements</span> 
                </a> 
                <a href="/devices/all" class="nav_link <?php if($title == 'Devices'){echo 'active';} ?>"> 
                    <i class='bx bxs-battery-charging nav_icon'></i> 
                    <span class="Devices">Devices</span> 
                </a>
                <?php if($_SESSION["admin"]) { ?>
                    <a href="/users/all" class="nav_link <?php if($title == 'Users'){echo 'active';} ?>"> 
                        <i class='bx bx-user-circle nav_icon'></i> 
                        <span class="Settings">Users</span> 
                    </a> 
                    <a href="/settings" class="nav_link <?php if($title == 'Settings'){echo 'active';} ?>">
                        <i class='bx bx-toggle-right nav_icon'></i> 
                        <span class="Settings">Settings</span> 
                    </a>
                <?php };?>
            </div>
        </div>
        <a href="/signout" class="nav_link"> <i class='bx bx-log-out nav_icon'></i> <span class="nav_name">Sign Out</span> </a>
    </nav>
</div>