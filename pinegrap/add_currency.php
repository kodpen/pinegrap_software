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
$liveform = new liveform('add_currency');

// if the form has not been submitted, print it out on the screen
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new currency]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Add Currency</h1>
            <div class="subheading">Add a currency conversion that is selectable by customers.</div>
            <form name="form" action="add_currency.php" method="post">
                ' . get_token_field() . '
                <table style="margin-bottom: 15px">
                    <tr>
                        <td>Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <td><label for="base">Base:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'base', 'name'=>'base', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr>
                        <td>Code:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'code', 'maxlength'=>'3')) . '</td>
                    </tr>
                    <tr>
                        <td>Symbol:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'symbol', 'maxlength'=>'10')) . '</td>
                    </tr>
                    <tr>
                        <td>Exchange Rate:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'exchange_rate', 'maxlength'=>'11')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted, validate the information.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('name', 'Name is required.');
    $liveform->validate_required_field('code', 'Code is required.');
    
    // if there is an error, send the user back to the add currency screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_currency.php');
        exit();
    }
    
    // check to see if code is already in use by a different currency
    $query =
        "SELECT id
        FROM currencies
        WHERE (code = '" . escape($liveform->get_field_value('code')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if code is already in use by a different currency, prepare error and forward user back to screen
    if (mysqli_num_rows($result) > 0) {
        $liveform->mark_error('code', 'The code that you entered is already in use by another currency, please enter a different code.');
        
        // forward user to add currency screen
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_currency.php');
        exit();
    }

    $exchange_rate = $liveform->get_field_value('exchange_rate');

    // If this currency was set as the base currency then set exchange rate to 1.
    if ($liveform->get_field_value('base') == 1) {
        $exchange_rate = 1;
    }
    
    // insert currency information into the database.
    $query =
        "INSERT INTO currencies(
            name,
            base,
            code,
            symbol,
            exchange_rate,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES (
            '" . escape($liveform->get_field_value('name')) . "',
            '" . escape($liveform->get_field_value('base')) . "',
            '" . escape($liveform->get_field_value('code')) . "',
            '" . escape($liveform->get_field_value('symbol')) . "',
            '" . escape($exchange_rate) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $currency_id = mysqli_insert_id(db::$con);

    // If this currency was set as the base currency then update all other currencies
    // in order to make sure that none are set as the base currency.
    if ($liveform->get_field_value('base') == 1) {
        db(
            "UPDATE currencies
            SET base = '0'
            WHERE
                (base = '1')
                AND (id != '" . $currency_id . "')");
    }

    // Log that the currency has been created.
    log_activity('currency (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);

    // Add a notice that the currency has been created, then send the user to the view currencies page.
    $liveform_view_currencies = new liveform('view_currencies');
    $liveform_view_currencies->add_notice('The currency has been created.');
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_currencies.php');
    
    $liveform->remove_form();
}
?>