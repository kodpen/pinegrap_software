<?php

/**
 *
 * liveSite - Enterprise Website Platform
 *
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include('init.php');

// If this screen is loading for the first time, then add JavaScript to reload screen.
if (isset($_SESSION['software']['theme_reloaded']) == FALSE) {
    $_SESSION['software']['theme_reloaded'] = TRUE;

    $output_javascript =
        '<script>
            function init()
            {
                window.location.reload(true);
            }
            
            window.onload = init;
        </script>';

// Else this screen was just reloaded, so unsert session property and add JavaScript to send user back to where he/she came from
} else {
    // remove the theme reloaded property from the session so that it does not exist
    // the next time that this script is used
    unset($_SESSION['software']['theme_reloaded']);

    // output JavaScript to send user back to where he/she came from after the theme has been reloaded
    $output_javascript =
        '<script>
            function init()
            {
                window.parent.location = "' . escape_javascript(URL_SCHEME . HOSTNAME . $_GET['send_to']) . '";
            }
            
            window.onload = init;
        </script>';
}

echo
    '<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            ' . get_generator_meta_tag() . '
            <link rel="stylesheet" type="text/css" href="' . OUTPUT_PATH . h($_GET['name']) . '" />
            ' . $output_javascript . '
        </head>
        <body style="background-color: black !important; background-image: none !important"></body>
    </html>';