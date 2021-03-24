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
            <h1>[new state]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Add State</h1>
            <div class="subheading" style="margin-bottom: 1em">Add a new state/province that can be included in any shipping zone or tax zone.</div>
            <form name="form" action="add_state.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New State/Province Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td><input type="text" name="code" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>State Name</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input type="text" name="name" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Country</h2></th>
                    </tr>
                    <tr>
                        <td>Country:</td>
                        <td><select name="country_id">' .  select_country() . '</select></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();
    
    // create state
    $query = "INSERT INTO states (
                name,
                code,
                country_id,
                user,
                timestamp)
            VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['code']) . "',
                '" . escape($_POST['country_id']) . "',
                " . $user['id'] . ",
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    log_activity('state (' . $_POST['name'] . ') was created', $_SESSION['sessionusername']);

    // forward user to view states page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_states.php');
}
?>