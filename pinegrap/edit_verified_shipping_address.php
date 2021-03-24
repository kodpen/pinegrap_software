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
$liveform = new liveform('edit_verified_shipping_address');

// get verified shipping address data
$query =
    "SELECT 
        verified_shipping_addresses.company,
        verified_shipping_addresses.address_1,
        verified_shipping_addresses.address_2,
        verified_shipping_addresses.city,
        verified_shipping_addresses.state_id,
        verified_shipping_addresses.zip_code,
        states.name as state_name
    FROM verified_shipping_addresses
    LEFT JOIN states ON verified_shipping_addresses.state_id = states.id
    WHERE verified_shipping_addresses.id = '" . escape($_REQUEST['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$verified_shipping_address = mysqli_fetch_assoc($result);

// if the verified shipping address has a company, then use that for the name
if ($verified_shipping_address['company'] != '') {
    $verified_shipping_address['name'] = $verified_shipping_address['company'];
    
// else the verified shipping address does not have a company, so use address 1 for the name
} else {
    $verified_shipping_address['name'] = $verified_shipping_address['address_1'];
}

// if the form has not just been submitted, then output form
if (!$_POST) {
    // if the form has not been submitted yet, pre-populate fields with data
    if ($liveform->field_in_session('id') == false) {
        $liveform->assign_field_value('company', $verified_shipping_address['company']);
        $liveform->assign_field_value('address_1', $verified_shipping_address['address_1']);
        $liveform->assign_field_value('address_2', $verified_shipping_address['address_2']);
        $liveform->assign_field_value('city', $verified_shipping_address['city']);
        $liveform->assign_field_value('state_id', $verified_shipping_address['state_id']);
        $liveform->assign_field_value('zip_code', $verified_shipping_address['zip_code']);
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($verified_shipping_address['name']) . '</h1>
            <div class="subheading">State: ' . h($verified_shipping_address['state_name']) . '</div>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Verified Shipping Address</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Update this verified shipping address and assign it to any existing state.</div>
            <form method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                <table class="field">
                    <tr>
                        <td>Company:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'company', 'size'=>'40', 'maxlength'=>'50')) . '</td>
                    </tr>
                    <tr>
                        <td>Address 1:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'address_1', 'size'=>'40', 'maxlength'=>'50')) . '</td>
                    </tr>
                    <tr>
                        <td>Address 2:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'address_2', 'size'=>'40', 'maxlength'=>'50')) . '</td>
                    </tr>
                    <tr>
                        <td>City:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'city', 'size'=>'40', 'maxlength'=>'50')) . '</td>
                    </tr>
                    <tr>
                        <td>State:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'state_id', 'options'=>get_state_options())) . '</td>
                    </tr>
                    <tr>
                        <td>Zip Code:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'zip_code', 'size'=>'10', 'maxlength'=>'50')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This verified shipping address will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // if the user selected to delete this verified shipping address, then delete it
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        $query = "DELETE FROM verified_shipping_addresses WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('verified shipping address (' . $verified_shipping_address['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_verified_shipping_addresses = new liveform('view_verified_shipping_addresses');
        $liveform_view_verified_shipping_addresses->add_notice('The verified shipping address has been deleted.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_verified_shipping_addresses.php');
        
    // else the user selected to save the verified shipping address
    } else {
        $liveform->validate_required_field('address_1', 'Address 1 is required.');
        $liveform->validate_required_field('city', 'City is required.');
        $liveform->validate_required_field('state_id', 'State is required.');
        $liveform->validate_required_field('zip_code', 'Zip Code is required.');
        
        // if there is an error, forward user back to previous screen
        if ($liveform->check_form_errors() == TRUE) {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
            exit();
        }
        
        // update verified shipping address
        $query =
            "UPDATE verified_shipping_addresses
            SET
                company = '" . escape($liveform->get_field_value('company')) . "',
                address_1 = '" . escape($liveform->get_field_value('address_1')) . "',
                address_2 = '" . escape($liveform->get_field_value('address_2')) . "',
                city = '" . escape($liveform->get_field_value('city')) . "',
                state_id = '" . escape($liveform->get_field_value('state_id')) . "',
                zip_code = '" . escape($liveform->get_field_value('zip_code')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if the verified shipping address has a company, then use that for the name
        if ($liveform->get_field_value('company') != '') {
            $name = $liveform->get_field_value('company');
            
        // else the verified shipping address does not have a company, so use address 1 for the name
        } else {
            $name = $liveform->get_field_value('address_1');
        }
        
        log_activity('verified shipping address (' . $name . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_verified_shipping_addresses = new liveform('view_verified_shipping_addresses');
        $liveform_view_verified_shipping_addresses->add_notice('The verified shipping address has been saved.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_verified_shipping_addresses.php');
    }
    
    $liveform->remove_form();
    exit();
}
?>