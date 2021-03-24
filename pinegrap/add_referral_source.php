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
            <h1>[new referral source]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Referral Source</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new referral source to gather marketing information during checkout. (Delete all will hide feature.)</div>
            <form name="form" action="add_referral_source.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Referral Source Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Referral Source Code:</td>
                        <td><input type="text" name="code" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Order Preview Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Referral Source Name:</td>
                        <td><input type="text" name="name" maxlength="50" /></td>
                    </tr>
                    <tr>
                        <td>Sort Order:</td>
                        <td><input name="sort_order" type="text" size="3" maxlength="3" /></td>
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
    
    // create referral source
    $query = "INSERT INTO referral_sources (
                name,
                code,
                sort_order,
                user,
                timestamp)
            VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['code']) . "',
                '" . escape($_POST['sort_order']) . "',
                " . $user['id'] . ",
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $referral_source_id = mysqli_insert_id(db::$con);

    log_activity('referral source (' . $_POST['name'] . ') was created', $_SESSION['sessionusername']);

    // forward user to view referral sources page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_referral_sources.php');
}
?>