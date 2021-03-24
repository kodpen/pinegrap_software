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

if (!$_POST) {
    $output =
        output_header() . '
        <div id="subnav">
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Delete Key Codes</h1>
            <div class="subheading" style="margin-bottom: 1em">Click "Delete" to delete all key codes.</div>
            <form action="delete_key_codes.php" method="post" name="form" class="disable_shortcut">
                ' . get_token_field() . '
                    <br /><br /><br />
                    <div class="buttons">
                        <input type="submit" name="submit_delete" value="Delete" class="submit-primary" onclick="return confirm(\'WARNING: All key codes will be permanently deleted.\')" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                    </div>
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();
    
    // delete all key codes
    $query = "TRUNCATE key_codes";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity("all key codes were deleted", $_SESSION['sessionusername']);
    
    // forward user to view key codes screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_key_codes.php');
}
?>