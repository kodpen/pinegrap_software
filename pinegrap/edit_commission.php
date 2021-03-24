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
$liveform = new liveform('edit_commission');

$liveform->add_fields_to_session();

// get commission info
$query =
    "SELECT
        commissions.affiliate_code,
        commissions.reference_code,
        commissions.status,
        contacts.affiliate_name
    FROM commissions
    LEFT JOIN contacts ON commissions.affiliate_code = contacts.affiliate_code
    WHERE commissions.id = '" . escape($liveform->get_field_value('id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$commission = mysqli_fetch_assoc($result);

// if the form has not just been submitted, then output form
if (!$_POST) {
    // if the form has not been submitted yet, pre-populate fields with data
    if ($liveform->field_in_session('status') == FALSE) {
        $liveform->assign_field_value('status', $commission['status']);
    }
    
    $status_options =
        array(
            'Pending' => 'pending',
            'Payable' => 'payable',
            'Ineligible' => 'ineligible',
            'Paid' => 'paid'
        );
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($commission['affiliate_name']) . ' (' . h($commission['affiliate_code']) . ')</h1>
            <div class="subheading">Reference Code: ' . h($commission['reference_code']) . '</div>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Commission</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Update the status of this commission.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                <table class="field">
                    <tr>
                        <td>Status:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'status', 'options'=>$status_options)) . '</td>
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
    
    // update commission
    $query =
        "UPDATE commissions
        SET
            status = '" . escape($liveform->get_field_value('status')) . "',
            last_modified_user_id = '" . $user['id'] . "',
            last_modified_timestamp = UNIX_TIMESTAMP()
        WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('commission (' . $commission['reference_code'] . ') was modified', $_SESSION['sessionusername']);
    
    $liveform_view_commissions = new liveform('view_commissions');
    $liveform_view_commissions->add_notice('The commission has been saved.');
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_commissions.php');
    
    $liveform->remove_form();
    exit();
}
?>