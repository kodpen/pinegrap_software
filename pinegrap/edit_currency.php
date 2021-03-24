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
$liveform = new liveform('edit_currency', $_REQUEST['id']);

if (!$_POST) {
    // if edit currency screen has not been submitted already, pre-populate fields with data
    if (isset($_SESSION['software']['liveforms']['edit_currency'][$_GET['id']]) == false) {
        // get currency information
        $query =
            "SELECT
                name,
                base,
                code,
                symbol,
                exchange_rate
            FROM currencies
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // Assign the values to the fields.
        $name = $row['name'];
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('base', $row['base']);
        $liveform->assign_field_value('code', $row['code']);
        $liveform->assign_field_value('symbol', $row['symbol']);
        $liveform->assign_field_value('exchange_rate', $row['exchange_rate']);
    }

    print
        output_header() . '
        <div id="subnav">
            <h1>' . $name . '</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Currency</h1>
            <div class="subheading">Edit this currency conversion selectable by customers.</div>
            <form name="form" action="edit_currency.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table style="margin-bottom: 15px">
        			<br />
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
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This currency will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if currency was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        // get currency name for the log
        $query = "SELECT name FROM currencies WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $currency_name = $row['name'];
        
        // delete currency
        $query = "DELETE FROM currencies WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('currency (' . $currency_name . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_currencies = new liveform('view_currencies');
        $liveform_view_currencies->add_notice('The currency has been deleted.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_currencies.php');
        
        $liveform->remove_form();
        
    // else the currency was not selected for delete
    } else {
        $liveform->add_fields_to_session();
        
        $liveform->validate_required_field('name', 'Name is required.');
        $liveform->validate_required_field('code', 'Code is required.');
        
        // if there is an error, forward user back to edit currency screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_currency.php?id=' . $_POST['id']);
            exit();
        }
        
        // check to see if code is already in use by a different currency
        $query =
            "SELECT id
            FROM currencies
            WHERE
                (code = '" . escape($liveform->get_field_value('code')) . "')
                AND (id != '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if code is already in use, prepare error and forward user back to screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('code', 'The code that you entered is already in use by another currency, please enter a different code.');
            
            // forward user to edit currency screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/edit_currency.php?id=' . $_POST['id']);
            exit();
        }

        $exchange_rate = $liveform->get_field_value('exchange_rate');

        // If this currency was set as the base currency then set exchange rate to 1.
        if ($liveform->get_field_value('base') == 1) {
            $exchange_rate = 1;
        }
        
        // update currency
        $query =
            "UPDATE currencies
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                base = '" . escape($liveform->get_field_value('base')) . "',
                code = '" . escape($liveform->get_field_value('code')) . "',
                symbol = '" . escape($liveform->get_field_value('symbol')) . "',
                exchange_rate = '" . escape($exchange_rate) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // If this currency was set as the base currency then update all other currencies
        // in order to make sure that none are set as the base currency.
        if ($liveform->get_field_value('base') == 1) {
            db(
                "UPDATE currencies
                SET base = '0'
                WHERE
                    (base = '1')
                    AND (id != '" . escape($_POST['id']) . "')");
        }

        log_activity('currency (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_currencies = new liveform('view_currencies');
        $liveform_view_currencies->add_notice('The currency has been saved.');
        
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_currencies.php');
        
        $liveform->remove_form();
    }
}
?>