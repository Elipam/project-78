<?php
    /*
        std/timeAgo.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        PHP-function that returns the time difference in human-readable text.
    */

    // function used to get correct format of time
    function get_time_ago( $time ) {
        if ($time == null) {
            return "Never";
        }
        $time_difference = time() - strtotime($time);

        if( $time_difference < 1 ) { return 'less than 1 second ago'; }
        $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                    30 * 24 * 60 * 60       =>  'month',
                    24 * 60 * 60            =>  'day',
                    60 * 60                 =>  'hour',
                    60                      =>  'minute',
                    1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $time_difference / $secs;

            if( $d >= 1 )
            {
                $t = round( $d );
                return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
?>