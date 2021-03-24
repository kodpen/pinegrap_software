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
    // get referral source data
    $query = "SELECT * FROM referral_sources WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $name = h($row['name']);
    $code = h($row['code']);
    $sort_order = $row['sort_order'];

    $output =
        output_header() . '
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Referral Source</h1>
            <div class="subheading" style="margin-bottom: 1em">Edit a referral source to gather marketing information during checkout. (Delete all will hide feature.)</div>
            <form name="form" action="edit_referral_source.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Referral Source Code for Order Reporting</h2></th>
                    </tr>
                    <tr>
                        <td>Referral Source Code:</td>
                        <td><input type="text" name="code" maxlength="50" value="' . $code . '" /></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Order Preview Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Referral Source Name:</td>
                        <td><input type="text" name="name" maxlength="50" value="' . $name . '" /></td>
                    </tr>
                    <tr>
                        <td>Sort Order:</td>
                        <td><input name="sort_order" type="text" size="3" maxlength="3" value="' . $sort_order . '" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This referral source will be permanently deleted.\')">
                </div>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
            </form>
        </div>' .
        output_footer();

    print $output;

} else {
    validate_token_field();

    // if referral source was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // delete referral source
        $query = "DELETE FROM referral_sources WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('referral source (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
    // else referral source was not selected for delete
    } else {
        // update referral source
        $query = "UPDATE referral_sources SET
                    name = '" . escape($_POST['name']) . "',
                    code = '" . escape($_POST['code']) . "',
                    sort_order = '" . escape($_POST['sort_order']) . "',
                    user = '" . $user['id'] . "',
                    timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        log_activity('referral source (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
    }

    // forward user to view referral sources screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_referral_sources.php');
}
?>