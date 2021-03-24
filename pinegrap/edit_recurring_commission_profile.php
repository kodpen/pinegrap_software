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

include_once('liveform.class.php');
$liveform = new liveform('edit_recurring_commission_profile');

$liveform->add_fields_to_session();

// get profile info
$query =
    "SELECT
        recurring_commission_profiles.id,
        recurring_commission_profiles.affiliate_code,
        recurring_commission_profiles.enabled,
        contacts.affiliate_name
    FROM recurring_commission_profiles
    LEFT JOIN contacts ON recurring_commission_profiles.affiliate_code = contacts.affiliate_code
    WHERE recurring_commission_profiles.id = '" . escape($liveform->get_field_value('id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$recurring_commission_profile = mysqli_fetch_assoc($result);

// if the form has not just been submitted, then output form
if (!$_POST) {
    // if the form has not been submitted yet, pre-populate fields with data
    if ($liveform->field_in_session('enabled') == FALSE) {
        $liveform->assign_field_value('enabled', $recurring_commission_profile['enabled']);
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($recurring_commission_profile['affiliate_name']) . ' (' . h($recurring_commission_profile['affiliate_code']) . ')</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Recurring Commission Profile</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Enable or disable this recurring commission profile.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                <table class="field">
                    <tr>
                        <td><label for="enabled">Enable:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'enabled', 'name'=>'enabled', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    // update profile
    $query =
        "UPDATE recurring_commission_profiles
        SET
            enabled = '" . escape($liveform->get_field_value('enabled')) . "',
            last_modified_user_id = '" . $user['id'] . "',
            last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('recurring commission profile (' . $recurring_commission_profile['id'] . ') was modified', $_SESSION['sessionusername']);
    
    $liveform_view_recurring_commission_profiles = new liveform('view_recurring_commission_profiles');
    $liveform_view_recurring_commission_profiles->add_notice('The recurring commission profile has been saved.');
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_recurring_commission_profiles.php');
    
    $liveform->remove_form();
    exit();
}
?>