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
$user = validate_user();
validate_area_access($user, 'manager');

validate_token_field();

// do different things depending on mode
switch ($_GET['mode']) {
    // if the user has selected to preview a theme, then do that
    case 'preview':    
        // if a theme ID was not passed, then set the user to preview the activated desktop theme
        if (isset($_GET['id']) == FALSE) {
            // do things differently based on the device type (i.e. desktop or mobile)
            switch ($_SESSION['software']['device_type']) {
                // if the device type is desktop then get the activated desktop theme id
                case 'desktop':
                default:
                    $query = "SELECT id FROM files WHERE activated_desktop_theme = '1'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    break;
                
                // if the device type is mobile, then get the activated mobile theme id
                // and fall back to the activated desktop theme if necessary
                case 'mobile':
                    $query = "SELECT id FROM files WHERE activated_mobile_theme = '1'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // if an activated mobile theme could not be found, then get activated desktop theme id
                    if (mysqli_num_rows($result) == 0) {
                        $query = "SELECT id FROM files WHERE activated_desktop_theme = '1'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }

                    break;
            }

            // if an activated theme was found, then set theme in the session
            if (mysqli_num_rows($result) != 0) {
                $row = mysqli_fetch_assoc($result);
                $_SESSION['software']['preview_theme_id'] = $row['id'];

            // Otherwise an activated theme was not found, so set it to the "none" theme,
            // so the theme preview pick list will still appear.
            } else {
                $_SESSION['software']['preview_theme_id'] = '';
            }

        // else a theme ID was passed, so set it in the session
        } else {
            $_SESSION['software']['preview_theme_id'] = $_GET['id'];
        }
        
        // send user back to where they came from, so the user can preview the theme
        header('Location: ' . URL_SCHEME . HOSTNAME . $_GET['send_to']);
        exit();
        
        break;
    
    // if the user has selected to cancel the theme preview, then do that
    case 'cancel':
        // remove the session values
        unset($_SESSION['software']['preview_theme_id']);
        unset($_SESSION['software']['preview_style']);
        
        // send user back to where they came from
        header('Location: ' . URL_SCHEME . HOSTNAME . $_GET['send_to']);
        exit();
        
        break;
}
?>