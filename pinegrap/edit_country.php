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
    // get country data
    $query = "SELECT * FROM countries WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = h($row['name']);
    $code = h($row['code']);
    $zip_code_required = $row['zip_code_required'];
    $transit_adjustment_days = $row['transit_adjustment_days'];
    $default_selected = $row['default_selected'];

    $zip_code_required_checked = '';

    if ($zip_code_required) {
        $zip_code_required_checked = ' checked="checked"';
    }

    // prepare checked status for default selected checkbox
    if ($default_selected == 1) {
        $default_selected_checked = ' checked="checked"';
    } else {
        $default_selected_checked = '';
    }

    $output =
        output_header() . '
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Country</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a country that can be included in any shipping zone or tax zone.</div>
            <form name="form" action="edit_country.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Country Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . $code . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Commerce Pages Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <td><label for="zip_code_required">Zip Code Required:</label></td>
                        <td>
                            <input type="checkbox" id="zip_code_required" name="zip_code_required"
                                value="1"' . $zip_code_required_checked . ' class="checkbox">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="default_selected">Selected by Default:</label></td>
                        <td>
                            <input type="checkbox" id="default_selected" name="default_selected"
                                value="1"' . $default_selected_checked . ' class="checkbox">
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Delays Specific to this Country</h2></th>
                    </tr>
                    <tr>
                        <td>Transit Adjustment Days:</td>
                        <td><input type="text" name="transit_adjustment_days" size="3" maxlength="10" value="' . $transit_adjustment_days . '" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This country and all states and verified shipping addresses in this country will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    echo $output;

} else {

    validate_token_field();
    
    // if country was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // get all states so that we can delete all items that are connected to each state
        $query = "SELECT id FROM states WHERE country_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while ($row = mysqli_fetch_assoc($result)) {
            // delete state references in zones_states_xref
            $query = "DELETE FROM zones_states_xref WHERE state_id = '" . $row['id'] . "'";
            $result_2 = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete state references in tax_zones_states_xref
            $query = "DELETE FROM tax_zones_states_xref WHERE state_id = '" . $row['id'] . "'";
            $result_2 = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete verified shipping addresses for this state
            $query = "DELETE FROM verified_shipping_addresses WHERE state_id = '" . $row['id'] . "'";
            $result_2 = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        // delete states that are in this country
        $query = "DELETE FROM states WHERE country_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // delete country references in zones_countries_xref
        $query = "DELETE FROM zones_countries_xref WHERE country_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // delete country
        $query = "DELETE FROM countries WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('country (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
    // else country was not selected for delete
    } else {
        // update country
        $query = "UPDATE countries SET
                    name = '" . escape($_POST['name']) . "',
                    code = '" . escape($_POST['code']) . "',
                    zip_code_required = '" . e($_POST['zip_code_required']) . "',
                    transit_adjustment_days = '" . escape($_POST['transit_adjustment_days']) . "',
                    default_selected = '" . escape($_POST['default_selected']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if default selected was checked, then turn value off for all other countries
        if ($_POST['default_selected'] == 1) {
            // update country
            $query = "UPDATE countries SET default_selected = 0 WHERE id != '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        log_activity('country (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view countries screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_countries.php');
}