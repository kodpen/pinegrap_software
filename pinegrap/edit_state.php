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
validate_ecommerce_access($user);

if (!$_POST) {
    // get state data
    $query = "SELECT * FROM states WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = h($row['name']);
    $code = h($row['code']);
    $country_id = $row['country_id'];

    $output =
        output_header() . '
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit State</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a state/province that can be included in any shipping zone or tax zone.</div>
            <form name="form" action="edit_state.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>State/Province Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . $code . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>State Name</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Country</h2></th>
                    </tr>
                    <tr>
                        <td>Country:</td>
                        <td><select name="country_id">' .  select_country($country_id) . '</select></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This state and all verified shipping addresses in this state will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();
    
    // if state was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete state
        $query = "DELETE FROM states WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete state references in zones_states_xref
        $query = "DELETE FROM zones_states_xref WHERE state_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete state references in tax_zones_states_xref
        $query = "DELETE FROM tax_zones_states_xref WHERE state_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete verified shipping addresses for this state
        $query = "DELETE FROM verified_shipping_addresses WHERE state_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('state (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
    // else state was not selected for delete
    } else {
        // update state
        $query = "UPDATE states SET
                    name = '" . escape($_POST['name']) . "',
                    code = '" . escape($_POST['code']) . "',
                    country_id = '" . escape($_POST['country_id']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('state (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view states screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_states.php');
}
?>