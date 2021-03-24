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
    $output =
        output_header() . '
        <div id="subnav">
            <h1>[new country]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Add Country</h1>
            <div class="subheading" style="margin-bottom: 1em">Add a new country that can be included in any shipping zone or tax zone.</div>
            <form name="form" action="add_country.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Country Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Commerce Pages Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <td><label for="zip_code_required">Zip Code Required:</label></td>
                        <td>
                            <input type="checkbox" id="zip_code_required" name="zip_code_required"
                                value="1" class="checkbox">
                        </td>
                    </tr>
                    <tr>
                        <td><label for="default_selected">Selected by Default:</label></td>
                        <td>
                            <input type="checkbox" id="default_selected" name="default_selected"
                                value="1" class="checkbox">
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Shipping Delays Specific to this Country</h2></th>
                    </tr>
                    <tr>
                        <td>Transit Adjustment Days:</td>
                        <td><input type="text" name="transit_adjustment_days" size="3" maxlength="10" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    echo $output;

} else {

    validate_token_field();
    
    // create country
    $query = "INSERT INTO countries (
                name,
                code,
                zip_code_required,
                transit_adjustment_days,
                default_selected,
                user,
                timestamp)
            VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['code']) . "',
                '" . e($_POST['zip_code_required']) . "',
                '" . escape($_POST['transit_adjustment_days']) . "',
                '" . escape($_POST['default_selected']) . "',
                '" . $user['id'] . "',
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $country_id = mysqli_insert_id(db::$con);

    // if default selected was checked, then turn value off for all other countries
    if ($_POST['default_selected'] == 1) {
        // update country
        $query = "UPDATE countries SET default_selected = 0 WHERE id != $country_id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    }

    log_activity('country (' . $_POST['name'] . ') was created');

    // forward user to view countries page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_countries.php');
}