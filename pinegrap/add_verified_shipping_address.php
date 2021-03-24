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
$liveform = new liveform('add_verified_shipping_address');

// if the form has not been submitted
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new verified shipping address]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Verified Shipping Address</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a new verified shipping address and assign it to any existing state.</div>
            <form action="add_verified_shipping_address.php" method="post">
                ' . get_token_field() . '
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
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'state_id', 'options'=>get_state_options(), 'value'=>$_GET['state_id'])) . '</td>
                    </tr>
                    <tr>
                        <td>Zip Code:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'zip_code', 'size'=>'10', 'maxlength'=>'50')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('address_1', 'Address 1 is required.');
    $liveform->validate_required_field('city', 'City is required.');
    $liveform->validate_required_field('state_id', 'State is required.');
    $liveform->validate_required_field('zip_code', 'Zip Code is required.');
    
    // if there is an error, forward user back to previous screen
    if ($liveform->check_form_errors() == TRUE) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_verified_shipping_address.php');
        exit();
    }
    
    // create verified shipping address
    $query =
        "INSERT INTO verified_shipping_addresses (
            company,
            address_1,
            address_2,
            city,
            state_id,
            zip_code,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('company')) . "',
            '" . escape($liveform->get_field_value('address_1')) . "',
            '" . escape($liveform->get_field_value('address_2')) . "',
            '" . escape($liveform->get_field_value('city')) . "',
            '" . escape($liveform->get_field_value('state_id')) . "',
            '" . escape($liveform->get_field_value('zip_code')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('verified shipping address (' . $liveform->get_field_value('company') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_verified_shipping_addresses = new liveform('view_verified_shipping_addresses');
    $liveform_view_verified_shipping_addresses->add_notice('The verified shipping address has been created.');

    // forward user to view verified shipping addresses screen
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_verified_shipping_addresses.php');
    
    $liveform->remove_form();
}
?>