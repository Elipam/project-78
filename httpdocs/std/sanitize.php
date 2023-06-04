<?php
    /*
        std/sanitize.php

        CMI-TI 22 TINPRJ0456
        Students: Ahmet Oral, Thijs Dregmans, Prashant Chotkan and Niels van Amsterdam
        Last edited: 02-12-2022

        PHP-function that cleans user input.
    */

    // sanitizes user input
    function sanitize($input) {
        $input = trim($input);
        $input = htmlspecialchars($input);

        return $input;

    }

?>