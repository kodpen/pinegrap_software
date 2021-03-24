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

// set memory limit to unlimited
ini_set('memory_limit', '-1');
include('init.php');
$user = validate_user();

validate_ecommerce_report_access();

include_once('liveform.class.php');
$liveform = new liveform('view_order_report');

// if an id was passed in the query string, then set id
if (isset($_GET['id']) == true) {
    $id = $_GET['id'];
    
// else if an id was passed in post, then set id
} elseif (isset($_POST['id']) == true) {
    $id = $_POST['id'];
}

// prepare query string with id if necessary
$query_string_id = '';

// if an id is set, then prepare query string with id
if (isset($id) == true) {
    $query_string_id = '?id=' . $id;
}

// get session index in order to store different session values for this screen depending on the report that is being edited
$session_index = 0;

// if there is an id set, then set session index to id
if (isset($id) == true) {
    $session_index = $id;
}

// if the form has not been submitted, then display form and report
if (!$_POST) {
    $output_edit_button = '';
    $output_edit_form_style = '';
    
    // if the order report is being viewed
    if (isset($_GET['id']) == true) {
        $output_edit_button = '<div id="button_bar"><a href="#" id="edit_button" onclick="document.getElementById(\'button_bar\').style.display = \'none\'; document.getElementById(\'edit_form\').style.display = \'block\'; return false;">Edit Order Report</a></div>';
        $output_edit_form_style = '; display: none';
    }
    
    // if an order report is being created and this screen has not already been submitted, then set default values for form fields
    if ((isset($_GET['id']) == false) && ($liveform->field_in_session('submit') == false)) {
        // set default values for summarize by fields
        $liveform->assign_field_value('summarize_by_1', 'year');
        $liveform->assign_field_value('summarize_by_2', 'month');
        $liveform->assign_field_value('summarize_by_3', 'day');
        
        // add default filter in order to only show data for complete and exported orders
        $liveform->assign_field_value('filter_1_field', 'order_status');
        $liveform->assign_field_value('filter_1_operator', 'is not equal to');
        $liveform->assign_field_value('filter_1_value', 'incomplete');
        $liveform->assign_field_value('last_filter_number', '1');
    
    // else if an order report is being edited and this screen has not been submitted already, pre-populate fields with data
    } elseif ((isset($_GET['id']) == true) && ($liveform->field_in_session('id') == false)) {
        // get order report data
        $query =
            "SELECT
                name,
                detail,
                summarize_by_1,
                order_by_1,
                summarize_by_2,
                order_by_2,
                summarize_by_3,
                order_by_3
            FROM order_reports
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $output_order_report_name = $row['name'];
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('detail', $row['detail']);
        $liveform->assign_field_value('summarize_by_1', $row['summarize_by_1']);
        $liveform->assign_field_value('order_by_1', $row['order_by_1']);
        $liveform->assign_field_value('summarize_by_2', $row['summarize_by_2']);
        $liveform->assign_field_value('order_by_2', $row['order_by_2']);
        $liveform->assign_field_value('summarize_by_3', $row['summarize_by_3']);
        $liveform->assign_field_value('order_by_3', $row['order_by_3']);
    }
    
    // if an order report is being created, then prepare screen name
    if (isset($_GET['id']) == false) {
        $output_screen_name = 'Create Order Report';
        
    // else an order report is being viewed, so prepare screen name
    } else {
        $output_screen_name = 'View Order Report: <strong>' . $liveform->get_field_value('name') . '</strong>';
    }
    
    $output_hidden_id_field = '';
    
    // if an order report is being edited, then prepare to display hidden id field
    if (isset($_GET['id']) == true) {
        $output_hidden_id_field = $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id']));
    }
    
    $summarize_by_options = array();
    
    $summarize_by_options['-None-'] = '';
    $summarize_by_options['General'] = '<optgroup>';
    $summarize_by_options['Year'] = 'year';
    $summarize_by_options['Month'] = 'month';
    $summarize_by_options['Day'] = 'day';
    $summarize_by_options['Order Status'] = 'order_status';
    $summarize_by_options['Special Offer Code'] = 'special_offer_code';
    $summarize_by_options['Referral Source Code'] = 'referral_source_code';
    $summarize_by_options['Reference Code'] = 'reference_code';
    $summarize_by_options['Tracking Code'] = 'tracking_code';
    $summarize_by_options['Referring URL'] = 'http_referer';
    
    // if multi currency is enabled, prepare currency option
    if (ECOMMERCE_MULTICURRENCY === true) {
        $summarize_by_options['Currency'] = 'currency_code';
    }
    
    $summarize_by_options['Customer\'s IP Address'] = 'ip_address';
    $summarize_by_options[''] = '</optgroup>';

    $summarize_by_options['UTM'] = '<optgroup>';
    $summarize_by_options['Source'] = 'utm_source';
    $summarize_by_options['Medium'] = 'utm_medium';
    $summarize_by_options['Campaign'] = 'utm_campaign';
    $summarize_by_options['Term'] = 'utm_term';
    $summarize_by_options['Content'] = 'utm_content';
    $summarize_by_options[''] = '</optgroup>';

    $summarize_by_options['Payment'] = '<optgroup>';
    $summarize_by_options['Payment Method'] = 'payment_method';
    $summarize_by_options['Card Type'] = 'card_type';
    $summarize_by_options['Cardholder'] = 'cardholder';
    $summarize_by_options['Card Number'] = 'card_number';
    $summarize_by_options[''] = '</optgroup>';
    $summarize_by_options['Billing'] = '<optgroup>';
    $summarize_by_options['Custom Field #1'] = 'custom_field_1';
    $summarize_by_options['Custom Field #2'] = 'custom_field_2';
    $summarize_by_options['Billing Salutation'] = 'billing_salutation';
    $summarize_by_options['Billing First Name'] = 'billing_first_name';
    $summarize_by_options['Billing Last Name'] = 'billing_last_name';
    $summarize_by_options['Billing Company'] = 'billing_company';
    $summarize_by_options['Billing Address 1'] = 'billing_address_1';
    $summarize_by_options['Billing Address 2'] = 'billing_address_2';
    $summarize_by_options['Billing City'] = 'billing_city';
    $summarize_by_options['Billing State'] = 'billing_state';
    $summarize_by_options['Billing Zip Code'] = 'billing_zip_code';
    $summarize_by_options['Billing Country'] = 'billing_country';
    $summarize_by_options['Billing Phone'] = 'billing_phone_number';
    $summarize_by_options['Billing Fax'] = 'billing_fax_number';
    $summarize_by_options['Billing Email'] = 'billing_email_address';
    $summarize_by_options['Opt-In Status'] = 'opt_in_status';
    $summarize_by_options['PO Number'] = 'po_number';
    $summarize_by_options['Tax Status'] = 'tax_status';
    $summarize_by_options[''] = '</optgroup>';
    
    $order_by_options = array(
        'alphabet' => 'alphabet',
        'number of orders' => 'number of orders',
        'total' => 'total'
    );
    
    // get filters
    $filters = array();
    
    // if an order report is being created or a form has been submitted, then get filters from liveform
    if ((isset($_GET['id']) == false) || ($liveform->field_in_session('submit') == true)) {
        // loop through all filters in order to add them to array
        for ($i = 1; $i <= $liveform->get_field_value('last_filter_number'); $i++) {
            // if filter exists and an operator was selected for this filter, then add filter to array
            if ($liveform->get_field_value('filter_' . $i . '_operator') != '') {
                // if user entered a value, clear dynamic value, in order to prevent user from using two values
                if ($liveform->get_field_value('filter_' . $i . '_value') != '') {
                    $dynamic_value = '';
                    $dynamic_value_attribute = '';
                } else {
                    $dynamic_value = $liveform->get_field_value('filter_' . $i . '_dynamic_value');
                    
                    // if days ago was selected for dynamic value, then set dynamic value attribute
                    if ($dynamic_value == 'days ago') {
                        $dynamic_value_attribute = $liveform->get_field_value('filter_' . $i . '_dynamic_value_attribute');
                    } else {
                        $dynamic_value_attribute = '';
                    }
                }
                
                $filters[] = array(
                    'field' => $liveform->get_field_value('filter_' . $i . '_field'),
                    'operator' => $liveform->get_field_value('filter_' . $i . '_operator'),
                    'value' => $liveform->get_field_value('filter_' . $i . '_value'),
                    'dynamic_value' => $dynamic_value,
                    'dynamic_value_attribute' => $dynamic_value_attribute
                );
            }
        }
        
    // else an order report is being edited and a form has not been submitted, so get filters from database
    } else {
        $query =
            "SELECT
                field,
                operator,
                value,
                dynamic_value,
                dynamic_value_attribute
            FROM order_report_filters
            WHERE order_report_id = '" . escape($_GET['id']) . "'
            ORDER BY id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            // get field type
            $field_type = '';
            
            // if the field for this filter is order date, then set type to date
            if ($row['field'] == 'order_date') {
                $field_type = 'date';
            }
            
            $filters[] = array(
                'field' => $row['field'],
                'operator' => $row['operator'],
                'value' => prepare_form_data_for_output($row['value'], $field_type),
                'dynamic_value' => $row['dynamic_value'],
                'dynamic_value_attribute' => $row['dynamic_value_attribute']
            );
        }
    }
    
    // initialize variables
    $order_date_filter_exists = false;
    $incomplete_orders_included = false;
    $output_filters_for_javascript = '';
    $count = 0;
    
    // loop through filters in order to prepare output for javascript
    foreach ($filters as $filter) {
        // if the field for this filter is order date, then remember that an order date filter exists
        if ($filter['field'] == 'order_date') {
            $order_date_filter_exists = true;
        }
        
        // if the field for this filter is order status, then determine if incomplete orders are included
        if ($filter['field'] == 'order_status') {
            // if operator is "is equal to" or "contains" and value is "Incomplete", then incomplete orders are included
            if (
                (($filter['operator'] == 'is equal to') || ($filter['operator'] == 'contains')) && ($filter['value'] == 'incomplete')
                || (($filter['operator'] == 'is not equal to') || ($filter['operator'] == 'does not contain')) && ($filter['value'] != 'incomplete')
            ) {
                $incomplete_orders_included = true;
                
            // else incomplete orders are not included
            } else {
                $incomplete_orders_included = false;
            }
        }
        
        // if dynamic value attribute is equal to 0, then set to empty string
        if ($filter['dynamic_value_attribute'] == 0) {
            $filter['dynamic_value_attribute'] = '';
        }
        
        $output_filters_for_javascript .=
            'filters[' . $count . '] = new Array();
            filters[' . $count . ']["field"] = "' . $filter['field'] . '";
            filters[' . $count . ']["operator"] = "' . $filter['operator'] . '";
            filters[' . $count . ']["value"] = "' . escape_javascript($filter['value']) . '";
            filters[' . $count . ']["dynamic_value"] = "' . $filter['dynamic_value'] . '";
            filters[' . $count . ']["dynamic_value_attribute"] = "' . $filter['dynamic_value_attribute'] . '";' . "\n";
        
        $count++;
    }
    
    // set field options
    $field_options = array();
    $field_options[] = array('name' => 'General', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Order Date', 'value' => 'order_date', 'type' => 'date');
    $field_options[] = array('name' => 'Order Status', 'value' => 'order_status', 'value_options' => array(array('name' => 'Incomplete', 'value' => 'incomplete'), array('name' => 'Complete', 'value' => 'complete'), array('name' => 'Exported', 'value' => 'exported')));
    $field_options[] = array('name' => 'Order Number', 'value' => 'order_number');
    $field_options[] = array('name' => 'Transaction ID', 'value' => 'transaction_id');
    $field_options[] = array('name' => 'Authorization Code', 'value' => 'authorization_code');
    $field_options[] = array('name' => 'Special Offer Code', 'value' => 'special_offer_code');
    $field_options[] = array('name' => 'Referral Source Code', 'value' => 'referral_source_code');
    $field_options[] = array('name' => 'Reference Code', 'value' => 'reference_code');
    $field_options[] = array('name' => 'Tracking Code', 'value' => 'tracking_code');
    $field_options[] = array('name' => 'Referring URL', 'value' => 'http_referer');
    
    // if multi currency is enabled, prepare currency option
    if (ECOMMERCE_MULTICURRENCY === true) {
        $currency_options = array();
        
        // get currencies in order to build currency options
        $query =
            "SELECT
                name,
                code
            FROM currencies
            ORDER BY
                base DESC,
                name ASC";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through each currency in order to add it to array
        while ($row = mysqli_fetch_assoc($result)) {
            $currency_option = array(
                'name' => $row['name'] . ' (' . $row['code'] . ')',
                'value' => $row['code']
            );

            $currency_options[] = $currency_option;
        }
        
        $field_options[] = array('name' => 'Currency', 'value' => 'currency_code', 'value_options' => $currency_options);
    }
    
    $field_options[] = array('name' => 'Customer\'s IP Address', 'value' => 'ip_address');
    $field_options[] = array('name' => 'Product Name', 'value' => 'product_name');
    $field_options[] = array('name' => '', 'value' => '</optgroup>');

    $field_options[] = array('name' => 'UTM', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Source', 'value' => 'utm_source');
    $field_options[] = array('name' => 'Medium', 'value' => 'utm_medium');
    $field_options[] = array('name' => 'Campaign', 'value' => 'utm_campaign');
    $field_options[] = array('name' => 'Term', 'value' => 'utm_term');
    $field_options[] = array('name' => 'Content', 'value' => 'utm_content');
    $field_options[] = array('name' => '', 'value' => '</optgroup>');

    $field_options[] = array('name' => 'Payment', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Payment Method', 'value' => 'payment_method', 'value_options' => array(array('name' => 'Credit/Debit Card', 'value' => 'Credit/Debit Card'), array('name' => 'PayPal Express Checkout', 'value' => 'PayPal Express Checkout'), array('name' => 'Offline Payment', 'value' => 'Offline Payment')));
    $field_options[] = array('name' => 'Card Type', 'value' => 'card_type');
    $field_options[] = array('name' => 'Cardholder', 'value' => 'cardholder');
    $field_options[] = array('name' => 'Card Number', 'value' => 'card_number');
    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    $field_options[] = array('name' => 'Billing', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Custom Field #1', 'value' => 'custom_field_1');
    $field_options[] = array('name' => 'Custom Field #2', 'value' => 'custom_field_2');
    $field_options[] = array('name' => 'Salutation', 'value' => 'billing_salutation');
    $field_options[] = array('name' => 'First Name', 'value' => 'billing_first_name');
    $field_options[] = array('name' => 'Last Name', 'value' => 'billing_last_name');
    $field_options[] = array('name' => 'Company', 'value' => 'billing_company');
    $field_options[] = array('name' => 'Address 1', 'value' => 'billing_address_1');
    $field_options[] = array('name' => 'Address 2', 'value' => 'billing_address_2');
    $field_options[] = array('name' => 'City', 'value' => 'billing_city');
    $field_options[] = array('name' => 'State', 'value' => 'billing_state');
    $field_options[] = array('name' => 'Zip Code', 'value' => 'billing_zip_code');
    $field_options[] = array('name' => 'Country', 'value' => 'billing_country');
    $field_options[] = array('name' => 'Phone', 'value' => 'billing_phone_number');
    $field_options[] = array('name' => 'Fax', 'value' => 'billing_fax_number');
    $field_options[] = array('name' => 'Email', 'value' => 'billing_email_address');
    $field_options[] = array('name' => 'Opt-In Status', 'value' => 'opt_in_status', 'value_options' => array(array('name' => 'Opt-In', 'value' => '1'), array('name' => 'Opt-Out', 'value' => '0')));
    $field_options[] = array('name' => 'PO Number', 'value' => 'po_number');
    $field_options[] = array('name' => 'Tax Status', 'value' => 'tax_status', 'value_options' => array(array('name' => 'Tax-Exempt', 'value' => '1'), array('name' => 'Not Tax-Exempt', 'value' => '0')));
    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    $field_options[] = array('name' => 'Shipping', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Salutation', 'value' => 'shipping_salutation');
    $field_options[] = array('name' => 'First Name', 'value' => 'shipping_first_name');
    $field_options[] = array('name' => 'Last Name', 'value' => 'shipping_last_name');
    $field_options[] = array('name' => 'Company', 'value' => 'shipping_company');
    $field_options[] = array('name' => 'Address 1', 'value' => 'shipping_address_1');
    $field_options[] = array('name' => 'Address 2', 'value' => 'shipping_address_2');
    $field_options[] = array('name' => 'City', 'value' => 'shipping_city');
    $field_options[] = array('name' => 'State', 'value' => 'shipping_state');
    $field_options[] = array('name' => 'Zip Code', 'value' => 'shipping_zip_code');
    $field_options[] = array('name' => 'Country', 'value' => 'shipping_country');
    $field_options[] = array('name' => 'Address Type', 'value' => 'shipping_address_type', 'value_options' => array(array('name' => 'None', 'value' => ''), array('name' => 'Residential', 'value' => 'residential'), array('name' => 'Business', 'value' => 'business')));
    $field_options[] = array('name' => 'Phone', 'value' => 'shipping_phone_number');
    $field_options[] = array('name' => 'Ship to Name', 'value' => 'ship_to_name');
    $field_options[] = array('name' => 'Arrival Date Code', 'value' => 'arrival_date_code');
    $field_options[] = array('name' => 'Shipping Method Code', 'value' => 'shipping_method_code');
    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    
    $output_field_options_for_javascript = '';
    $count = 0;

    // loop through all field options in order to prepare javascript array
    foreach ($field_options as $field_option) {
        $output_field_options_for_javascript .=
            'field_options[' . $count . '] = new Array();
            field_options[' . $count . ']["name"] = "' . escape_javascript($field_option['name']) . '";
            field_options[' . $count . ']["value"] = "' . escape_javascript($field_option['value']) . '";
            field_options[' . $count . ']["type"] = "' . escape_javascript($field_option['type']) . '";' . "\n";
        
        // if there are value options, then add value options to javascript array
        if (isset($field_option['value_options']) == true) {
            $output_field_options_for_javascript .=
                'field_options[' . $count . ']["value_options"] = new Array();' . "\n";
            
            $count_2 = 0;
            
            // loop through value options in order to add options to javascript array
            foreach ($field_option['value_options'] as $value_option) {
                $output_field_options_for_javascript .=
                    'field_options[' . $count . ']["value_options"][' . $count_2 . '] = new Array();
                    field_options[' . $count . ']["value_options"][' . $count_2 . ']["name"] = "' . escape_javascript($value_option['name']) . '";
                    field_options[' . $count . ']["value_options"][' . $count_2 . ']["value"] = "' . escape_javascript($value_option['value']) . '";' . "\n";
                    
                $count_2++;
            }
        }
        
        $count++;
    }
    
    $output_cancel_button_onclick = '';
    
    // if the user is creating an order report, then prepare cancel button to send user back a page
    if (isset($_GET['id']) == false) {
        $output_cancel_button_onclick = 'history.go(-1);';
    
    // else the user is editing an order report, so prepare cancel button to hide edit form and show edit button
    } else {
        $output_cancel_button_onclick = 'document.getElementById(\'button_bar\').style.display = \'block\'; document.getElementById(\'edit_form\').style.display = \'none\';';
    }
    
    $output_delete_button = '';
    
    // if user is editing an existing order report, then prepare to output delete button
    if (isset($_GET['id']) == true) {
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This order report will be permanently deleted.\')" />';
    }
    
    // if there are no order date filters, then prepare to output date changer
    if ($order_date_filter_exists == false) {
        // if the date has not been set in the session yet, populate start and stop days with default, which is the past week
        if (isset($_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month']) == false) {
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'] = date('m', time() - 2678400);
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'] = date('d', time() - 2678400);
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] = date('Y', time() - 2678400);
            
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_month'] = date('m');
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_day'] = date('d');
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_year'] = date('Y');
            
        // else if the date has been passed in the query string, then set date in session
        } elseif (isset($_GET['start_month']) == true) {
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'] = $_GET['start_month'];
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'] = $_GET['start_day'];
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] = $_GET['start_year'];
            
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_month'] = $_GET['stop_month'];
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_day'] = $_GET['stop_day'];
            $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_year'] = $_GET['stop_year'];
        }
        
        $decrease_year['start_month'] = '01';
        $decrease_year['start_day'] = '01';
        $decrease_year['start_year'] = $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] - 1;
        $decrease_year['stop_month'] = '12';
        $decrease_year['stop_day'] = '31';
        $decrease_year['stop_year'] = $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] - 1;

        $current_year['start_month'] = '01';
        $current_year['start_day'] = '01';
        $current_year['start_year'] = date('Y');
        $current_year['stop_month'] = '12';
        $current_year['stop_day'] = '31';
        $current_year['stop_year'] = date('Y');

        $increase_year['start_month'] = '01';
        $increase_year['start_day'] = '01';
        $increase_year['start_year'] = $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] + 1;
        $increase_year['stop_month'] = '12';
        $increase_year['stop_day'] = '31';
        $increase_year['stop_year'] = $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year'] + 1;

        $decrease_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'] - 1, 1, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $decrease_month['new_month'] = date('m', $decrease_month['new_time']);
        $decrease_month['new_year'] = date('Y', $decrease_month['new_time']);
        $decrease_month['start_month'] = $decrease_month['new_month'];
        $decrease_month['start_day'] = '01';
        $decrease_month['start_year'] = $decrease_month['new_year'];
        $decrease_month['stop_month'] = $decrease_month['new_month'];
        $decrease_month['stop_day'] = date('t', $decrease_month['new_time']);
        $decrease_month['stop_year'] = $decrease_month['new_year'];

        $current_month['new_month'] = date('m');
        $current_month['new_year'] = date('Y');
        $current_month['start_month'] = $current_month['new_month'];
        $current_month['start_day'] = '01';
        $current_month['start_year'] = $current_month['new_year'];
        $current_month['stop_month'] = $current_month['new_month'];
        $current_month['stop_day'] = date('t');
        $current_month['stop_year'] = $current_month['new_year'];

        $increase_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'] + 1, 1, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $increase_month['new_month'] = date('m', $increase_month['new_time']);
        $increase_month['new_year'] = date('Y', $increase_month['new_time']);
        $increase_month['start_month'] = $increase_month['new_month'];
        $increase_month['start_day'] = '01';
        $increase_month['start_year'] = $increase_month['new_year'];
        $increase_month['stop_month'] = $increase_month['new_month'];
        $increase_month['stop_day'] = date('t', $increase_month['new_time']);
        $increase_month['stop_year'] = $increase_month['new_year'];

        $decrease_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $decrease_week['new_time_start'] = strtotime('last Sunday', $decrease_week['start_date_timestamp']);
        $decrease_week['new_time_stop'] = strtotime('Saturday', $decrease_week['new_time_start']);
        $decrease_week['start_month'] = date('m', $decrease_week['new_time_start']);
        $decrease_week['start_day'] = date('d', $decrease_week['new_time_start']);
        $decrease_week['start_year'] = date('Y', $decrease_week['new_time_start']);
        $decrease_week['stop_month'] = date('m', $decrease_week['new_time_stop']);
        $decrease_week['stop_day'] = date('d', $decrease_week['new_time_stop']);
        $decrease_week['stop_year'] = date('Y', $decrease_week['new_time_stop']);

        // if today is Sunday
        if (date('l') == 'Sunday') {
            $current_week['new_time_start'] = strtotime('Sunday');
        } else {
            $current_week['new_time_start'] = strtotime('last Sunday');
        }
        $current_week['new_time_stop'] = strtotime('Saturday', $current_week['new_time_start']);
        $current_week['start_month'] = date('m', $current_week['new_time_start']);
        $current_week['start_day'] = date('d', $current_week['new_time_start']);
        $current_week['start_year'] = date('Y', $current_week['new_time_start']);
        $current_week['stop_month'] = date('m', $current_week['new_time_stop']);
        $current_week['stop_day'] = date('d', $current_week['new_time_stop']);
        $current_week['stop_year'] = date('Y', $current_week['new_time_stop']);

        $increase_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $increase_week['new_time_start'] = strtotime('next Sunday', $increase_week['start_date_timestamp']);
        $increase_week['new_time_stop'] = strtotime('Saturday', $increase_week['new_time_start']);
        $increase_week['start_month'] = date('m', $increase_week['new_time_start']);
        $increase_week['start_day'] = date('d', $increase_week['new_time_start']);
        $increase_week['start_year'] = date('Y', $increase_week['new_time_start']);
        $increase_week['stop_month'] = date('m', $increase_week['new_time_stop']);
        $increase_week['stop_day'] = date('d', $increase_week['new_time_stop']);
        $increase_week['stop_year'] = date('Y', $increase_week['new_time_stop']);

        $decrease_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'] - 1, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $decrease_day['new_month'] = date('m', $decrease_day['new_time']);
        $decrease_day['new_day'] = date('d', $decrease_day['new_time']);
        $decrease_day['new_year'] = date('Y', $decrease_day['new_time']);
        $decrease_day['start_month'] = $decrease_day['new_month'];
        $decrease_day['start_day'] = $decrease_day['new_day'];
        $decrease_day['start_year'] = $decrease_day['new_year'];
        $decrease_day['stop_month'] = $decrease_day['new_month'];
        $decrease_day['stop_day'] = $decrease_day['new_day'];
        $decrease_day['stop_year'] = $decrease_day['new_year'];
        
        $current_day['new_month'] = date('m');
        $current_day['new_day'] = date('d');
        $current_day['new_year'] = date('Y');
        $current_day['start_month'] = $current_day['new_month'];
        $current_day['start_day'] = $current_day['new_day'];
        $current_day['start_year'] = $current_day['new_year'];
        $current_day['stop_month'] = $current_day['new_month'];
        $current_day['stop_day'] = $current_day['new_day'];
        $current_day['stop_year'] = $current_day['new_year'];
        
        $increase_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'] + 1, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $increase_day['new_month'] = date('m', $increase_day['new_time']);
        $increase_day['new_day'] = date('d', $increase_day['new_time']);
        $increase_day['new_year'] = date('Y', $increase_day['new_time']);
        $increase_day['start_month'] = $increase_day['new_month'];
        $increase_day['start_day'] = $increase_day['new_day'];
        $increase_day['start_year'] = $increase_day['new_year'];
        $increase_day['stop_month'] = $increase_day['new_month'];
        $increase_day['stop_day'] = $increase_day['new_day'];
        $increase_day['stop_year'] = $increase_day['new_year'];
        
        // get timestamps for start and stop dates
        $start_timestamp = mktime(0, 0, 0, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_day'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['start_year']);
        $stop_timestamp = mktime(23, 59, 59, $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_month'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_day'], $_SESSION['software']['ecommerce']['view_order_report'][$session_index]['stop_year']);
        
        // prepare query string with id if necessary
        $output_date_changer_query_string_id = '';

        // if an id is set, then prepare query string with id
        if (isset($id) == true) {
            $output_date_changer_query_string_id = '&id=' . $id;
        }
        
        $start_date = get_absolute_time(array('timestamp' => $start_timestamp, 'type' => 'date'));
        $stop_date = get_absolute_time(array('timestamp' => $stop_timestamp, 'type' => 'date'));
        
        $output_date_range = $start_date;
        
        // if the stop date is different from the start date, then add stop date to date range
        if ($stop_date != $start_date) {
            $output_date_range .= ' - ' . $stop_date;
        }
        
        $output_date_changer = '<div style="margin-bottom: .5em"><a href="view_order_report.php?start_month=' . $decrease_year['start_month'] . '&start_day=' . $decrease_year['start_day'] . '&start_year=' . $decrease_year['start_year'] . '&stop_month=' . $decrease_year['stop_month'] . '&stop_day=' . $decrease_year['stop_day'] . '&stop_year=' . $decrease_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_order_report.php?start_month=' . $current_year['start_month'] . '&start_day=' . $current_year['start_day'] . '&start_year=' . $current_year['start_year'] . '&stop_month=' . $current_year['stop_month'] . '&stop_day=' . $current_year['stop_day'] . '&stop_year=' . $current_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Year&nbsp;</a><a href="view_order_report.php?start_month=' . $increase_year['start_month'] . '&start_day=' . $increase_year['start_day'] . '&start_year=' . $increase_year['start_year'] . '&stop_month=' . $increase_year['stop_month'] . '&stop_day=' . $increase_year['stop_day'] . '&stop_year=' . $increase_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_order_report.php?start_month=' . $decrease_month['start_month'] . '&start_day=' . $decrease_month['start_day'] . '&start_year=' . $decrease_month['start_year'] . '&stop_month=' . $decrease_month['stop_month'] . '&stop_day=' . $decrease_month['stop_day'] . '&stop_year=' . $decrease_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_order_report.php?start_month=' . $current_month['start_month'] . '&start_day=' . $current_month['start_day'] . '&start_year=' . $current_month['start_year'] . '&stop_month=' . $current_month['stop_month'] . '&stop_day=' . $current_month['stop_day'] . '&stop_year=' . $current_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Month&nbsp;</a><a href="view_order_report.php?start_month=' . $increase_month['start_month'] . '&start_day=' . $increase_month['start_day'] . '&start_year=' . $increase_month['start_year'] . '&stop_month=' . $increase_month['stop_month'] . '&stop_day=' . $increase_month['stop_day'] . '&stop_year=' . $increase_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_order_report.php?start_month=' . $decrease_week['start_month'] . '&start_day=' . $decrease_week['start_day'] . '&start_year=' . $decrease_week['start_year'] . '&stop_month=' . $decrease_week['stop_month'] . '&stop_day=' . $decrease_week['stop_day'] . '&stop_year=' . $decrease_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_order_report.php?start_month=' . $current_week['start_month'] . '&start_day=' . $current_week['start_day'] . '&start_year=' . $current_week['start_year'] . '&stop_month=' . $current_week['stop_month'] . '&stop_day=' . $current_week['stop_day'] . '&stop_year=' . $current_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Week&nbsp;</a><a href="view_order_report.php?start_month=' . $increase_week['start_month'] . '&start_day=' . $increase_week['start_day'] . '&start_year=' . $increase_week['start_year'] . '&stop_month=' . $increase_week['stop_month'] . '&stop_day=' . $increase_week['stop_day'] . '&stop_year=' . $increase_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_order_report.php?start_month=' . $decrease_day['start_month'] . '&start_day=' . $decrease_day['start_day'] . '&start_year=' . $decrease_day['start_year'] . '&stop_month=' . $decrease_day['stop_month'] . '&stop_day=' . $decrease_day['stop_day'] . '&stop_year=' . $decrease_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_order_report.php?start_month=' . $current_day['start_month'] . '&start_day=' . $current_day['start_day'] . '&start_year=' . $current_day['start_year'] . '&stop_month=' . $current_day['stop_month'] . '&stop_day=' . $current_day['stop_day'] . '&stop_year=' . $current_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Day&nbsp;</a><a href="view_order_report.php?start_month=' . $increase_day['start_month'] . '&start_day=' . $increase_day['start_day'] . '&start_year=' . $increase_day['start_year'] . '&stop_month=' . $increase_day['stop_month'] . '&stop_day=' . $increase_day['stop_day'] . '&stop_year=' . $increase_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $output_date_range . '</div>';
    }
    
    // start where
    $where = '';
    
    $order_status_filter_exists = false;
    $order_item_filter_exists = false;
    $ship_to_filter_exists = false;
    
    // loop through all filters in order to prepare SQL
    foreach ($filters as $filter) {
        // get operand 1 and field type when necessary
        $operand_1 = '';
        $field_type = '';
        
        switch ($filter['field']) {
            case 'order_date': $operand_1 = 'FROM_UNIXTIME(orders.order_date, \'%Y-%m-%d\')'; $field_type = 'date'; break;
            case 'order_status': $operand_1 = 'orders.status'; $order_status_filter_exists = true; break;
            case 'order_number': $operand_1 = 'orders.order_number'; break;
            case 'transaction_id': $operand_1 = 'orders.transaction_id'; break;
            case 'authorization_code': $operand_1 = 'orders.authorization_code'; break;
            case 'special_offer_code': $operand_1 = 'orders.special_offer_code'; break;
            case 'referral_source_code': $operand_1 = 'orders.referral_source_code'; break;
            case 'reference_code': $operand_1 = 'orders.reference_code'; break;
            case 'tracking_code': $operand_1 = 'orders.tracking_code'; break;
            case 'http_referer': $operand_1 = 'orders.http_referer'; break;
            case 'currency_code': $operand_1 = 'orders.currency_code'; break;
            case 'ip_address': $operand_1 = 'CASE WHEN (INET_NTOA(orders.ip_address) = "0.0.0.0") THEN "" ELSE INET_NTOA(orders.ip_address) END'; break;
            case 'product_name': $operand_1 = 'order_items.product_name'; $order_item_filter_exists = true; break;

            case 'utm_source': $operand_1 = 'orders.utm_source'; break;
            case 'utm_medium': $operand_1 = 'orders.utm_medium'; break;
            case 'utm_campaign': $operand_1 = 'orders.utm_campaign'; break;
            case 'utm_term': $operand_1 = 'orders.utm_term'; break;
            case 'utm_content': $operand_1 = 'orders.utm_content'; break;

            case 'payment_method': $operand_1 = 'orders.payment_method'; break;
            case 'card_type': $operand_1 = 'orders.card_type'; break;
            case 'cardholder': $operand_1 = 'orders.cardholder'; break;
            case 'card_number': $operand_1 = 'orders.card_number'; break;
            case 'custom_field_1': $operand_1 = 'orders.custom_field_1'; break;
            case 'custom_field_2': $operand_1 = 'orders.custom_field_2'; break;
            case 'billing_salutation': $operand_1 = 'orders.billing_salutation'; break;
            case 'billing_first_name': $operand_1 = 'orders.billing_first_name'; break;
            case 'billing_last_name': $operand_1 = 'orders.billing_last_name'; break;
            case 'billing_company': $operand_1 = 'orders.billing_company'; break;
            case 'billing_address_1': $operand_1 = 'orders.billing_address_1'; break;
            case 'billing_address_2': $operand_1 = 'orders.billing_address_2'; break;
            case 'billing_city': $operand_1 = 'orders.billing_city'; break;
            case 'billing_state': $operand_1 = 'orders.billing_state'; break;
            case 'billing_zip_code': $operand_1 = 'orders.billing_zip_code'; break;
            case 'billing_country': $operand_1 = 'orders.billing_country'; break;
            case 'billing_phone_number': $operand_1 = 'orders.billing_phone_number'; break;
            case 'billing_fax_number': $operand_1 = 'orders.billing_fax_number'; break;
            case 'billing_email_address': $operand_1 = 'orders.billing_email_address'; break;
            case 'opt_in_status': $operand_1 = 'orders.opt_in'; break;
            case 'po_number': $operand_1 = 'orders.po_number'; break;
            case 'tax_status': $operand_1 = 'orders.tax_exempt'; break;
            case 'shipping_salutation': $operand_1 = 'ship_tos.salutation'; $ship_to_filter_exists = true; break;
            case 'shipping_first_name': $operand_1 = 'ship_tos.first_name'; $ship_to_filter_exists = true; break;
            case 'shipping_last_name': $operand_1 = 'ship_tos.last_name'; $ship_to_filter_exists = true; break;
            case 'shipping_company': $operand_1 = 'ship_tos.company'; $ship_to_filter_exists = true; break;
            case 'shipping_address_1': $operand_1 = 'ship_tos.address_1'; $ship_to_filter_exists = true; break;
            case 'shipping_address_2': $operand_1 = 'ship_tos.address_2'; $ship_to_filter_exists = true; break;
            case 'shipping_city': $operand_1 = 'ship_tos.city'; $ship_to_filter_exists = true; break;
            case 'shipping_state': $operand_1 = 'ship_tos.state'; $ship_to_filter_exists = true; break;
            case 'shipping_zip_code': $operand_1 = 'ship_tos.zip_code'; $ship_to_filter_exists = true; break;
            case 'shipping_country': $operand_1 = 'ship_tos.country'; $ship_to_filter_exists = true; break;
            case 'shipping_address_type': $operand_1 = 'ship_tos.address_type'; $ship_to_filter_exists = true; break;
            case 'shipping_phone_number': $operand_1 = 'ship_tos.phone_number'; $ship_to_filter_exists = true; break;
            case 'ship_to_name': $operand_1 = 'ship_tos.ship_to_name'; $ship_to_filter_exists = true; break;
            case 'arrival_date_code': $operand_1 = 'ship_tos.arrival_date_code'; $ship_to_filter_exists = true; break;
            case 'shipping_method_code': $operand_1 = 'ship_tos.shipping_method_code'; $ship_to_filter_exists = true; break;
        }
        
        // if a basic value was entered, use that value
        if ($filter['value'] != '') {
            $operand_2 = prepare_form_data_for_input($filter['value'], $field_type);
            
        // else a dynamic value was entered, so use dynamic value
        } else {
            $operand_2 = get_dynamic_value($filter['dynamic_value'], $filter['dynamic_value_attribute']);
        }
        
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";
            
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        $where .= "(" . prepare_sql_operation($filter['operator'], $operand_1, $operand_2) . ") ";
    }
    
    // if there are no order date filters, then add filter for date changer
    if ($order_date_filter_exists == false) {
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";
            
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // add start and stop timestamp to where clause
        $where .= "(orders.order_date >= $start_timestamp) AND (orders.order_date <= $stop_timestamp)";
    }
    
    // if an order item filter exists, then join order_items table
    if ($order_item_filter_exists == true) {
        $join_order_items = " LEFT JOIN order_items ON orders.id = order_items.order_id";
    }
    
    // if a ship to filter exists, then join ship_to table
    if ($ship_to_filter_exists == true) {
        $join_ship_tos = " LEFT JOIN ship_tos ON orders.id = ship_tos.order_id";
    }
    
    if ($liveform->get_field_value('summarize_by_1') != '') {
        $sql_summarize_by_1 = get_summarize_by_column($liveform->get_field_value('summarize_by_1')) . ',';
        $number_of_summarize_bys = 1;
        
        if ($liveform->get_field_value('summarize_by_2') != '') {
            $sql_summarize_by_2 = get_summarize_by_column($liveform->get_field_value('summarize_by_2')) . ',';
            $number_of_summarize_bys = 2;
            
            if ($liveform->get_field_value('summarize_by_3') != '') {
                $sql_summarize_by_3 = get_summarize_by_column($liveform->get_field_value('summarize_by_3')) . ',';
                $number_of_summarize_bys = 3;
            }
        }
        
    } else {
        $number_of_summarize_bys = 0;
    }
    
    // if incomplete orders are included in this report,
    // then get data for incomplete orders first because we have to calculate
    // subtotal, tax, shipping and total differently for incomplete orders
    if ($incomplete_orders_included == true) {
        // get all incomplete orders
        $query =
            "SELECT
                orders.id,
                SUM(order_items.price * CAST(order_items.quantity AS signed)) as subtotal,
                SUM(order_items.tax * CAST(order_items.quantity AS signed)) as tax,
                SUM(order_items.shipping * order_items.quantity) as shipping
            FROM order_items
            LEFT JOIN orders on order_items.order_id = orders.id
            $join_ship_tos
            $where AND (orders.status = 'incomplete')
            GROUP BY orders.id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        $incomplete_orders = array();
        
        while ($row = mysqli_fetch_assoc($result)) {
            // store data for incomplete order in array
            $incomplete_orders[$row['id']] = array(
                'subtotal' => $row['subtotal'],
                'tax' => $row['tax'],
                'shipping' => $row['shipping'],
                'total' => $row['subtotal'] + $row['tax'] + $row['shipping']);
        }
    }

    // get all orders
    $query =
        "SELECT
            $sql_summarize_by_1
            $sql_summarize_by_2
            $sql_summarize_by_3
            orders.id,
            orders.order_number,
            orders.order_date,
            orders.subtotal,
            orders.discount,
            orders.tax,
            orders.shipping,
            orders.total,
            orders.status
        FROM orders
        $join_order_items
        $join_ship_tos
        $where
        GROUP BY orders.id
        ORDER BY
            $sql_summarize_by_1
            $sql_summarize_by_2
            $sql_summarize_by_3
            orders.order_number,
            orders.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $results = array();

    switch ($number_of_summarize_bys) {
        case 0:
            $orders = array();
            
            while ($row = mysqli_fetch_array($result)) {
                // store data for order in array
                $orders[] = array(
                    'id' => $row['id'],
                    'order_number' => $row['order_number'],
                    'order_date' => $row['order_date'],
                    'subtotal' => $row['subtotal'],
                    'discount' => $row['discount'],
                    'tax' => $row['tax'],
                    'shipping' => $row['shipping'],
                    'total' => $row['total']);
                
                // update grand totals
                $grand_count++;
                $grand_subtotal += $row['subtotal'];
                $grand_discount += $row['discount'];
                $grand_tax += $row['tax'];
                $grand_shipping += $row['shipping'];
                $grand_total += $row['total'];
            }
            break;
        
        case 1:
            while ($row = mysqli_fetch_array($result)) {

                $summarize_by_1_name = trim($row[0]);
                
                // if we have a new summarize by in summarize by 1, increment key and store name
                if (mb_strtolower($summarize_by_1_name) !== mb_strtolower($previous_summarize_by_1_name)) {
                    $summarize_by_1_key++;
                    $results[$summarize_by_1_key]['name'] = $summarize_by_1_name;
                }
                
                // if order is complete, use values from query
                if ($row['status'] != 'incomplete') {
                    $subtotal = $row['subtotal'];
                    $tax = $row['tax'];
                    $shipping = $row['shipping'];
                    $total = $row['total'];
                
                // else order is incomplete, so use values from incomplete orders array
                } else {
                    $subtotal = $incomplete_orders[$row['id']]['subtotal'];
                    $tax = $incomplete_orders[$row['id']]['tax'];
                    $shipping = $incomplete_orders[$row['id']]['shipping'];
                    $total = $incomplete_orders[$row['id']]['total'];
                }
                
                // store data for order in array
                $results[$summarize_by_1_key]['orders'][] = array(
                    'id' => $row['id'],
                    'order_number' => $row['order_number'],
                    'order_date' => $row['order_date'],
                    'subtotal' => $subtotal,
                    'discount' => $row['discount'],
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'total' => $total);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['total'] += $total;
                
                // update grand totals
                $grand_count++;
                $grand_subtotal += $subtotal;
                $grand_discount += $row['discount'];
                $grand_tax += $tax;
                $grand_shipping += $shipping;
                $grand_total += $total;
                
                $previous_summarize_by_1_name = $summarize_by_1_name;
            }
            break;
        
        case 2:
            while ($row = mysqli_fetch_array($result)) {

                $summarize_by_1_name = trim($row[0]);
                $summarize_by_2_name = trim($row[1]);
                
                // if we have a new summarize by in summarize by 1, increment key and store name, and reset key and store name for summarize by 2
                if (mb_strtolower($summarize_by_1_name) !== mb_strtolower($previous_summarize_by_1_name)) {
                    $summarize_by_1_key++;
                    $results[$summarize_by_1_key]['name'] = $summarize_by_1_name;
                    
                    $summarize_by_2_key = 0;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['name'] = $summarize_by_2_name;
                    
                // else if we have a new summarize by in summarize by 2, increment key and store name
                } elseif (mb_strtolower($summarize_by_2_name) !== mb_strtolower($previous_summarize_by_2_name)) {
                    $summarize_by_2_key++;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['name'] = $summarize_by_2_name;
                }
                
                // if order is complete, use values from query
                if ($row['status'] != 'incomplete') {
                    $subtotal = $row['subtotal'];
                    $tax = $row['tax'];
                    $shipping = $row['shipping'];
                    $total = $row['total'];
                
                // else order is incomplete, so use values from incomplete orders array
                } else {
                    $subtotal = $incomplete_orders[$row['id']]['subtotal'];
                    $tax = $incomplete_orders[$row['id']]['tax'];
                    $shipping = $incomplete_orders[$row['id']]['shipping'];
                    $total = $incomplete_orders[$row['id']]['total'];
                }
                
                // store data for order in array
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['orders'][] = array(
                    'id' => $row['id'],
                    'order_number' => $row['order_number'],
                    'order_date' => $row['order_date'],
                    'subtotal' => $subtotal,
                    'discount' => $row['discount'],
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'total' => $total);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['total'] += $total;
                
                // update totals for summarize by 2
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['total'] += $total;
                
                // update grand totals
                $grand_count++;
                $grand_subtotal += $subtotal;
                $grand_discount += $row['discount'];
                $grand_tax += $tax;
                $grand_shipping += $shipping;
                $grand_total += $total;
                
                $previous_summarize_by_1_name = $summarize_by_1_name;
                $previous_summarize_by_2_name = $summarize_by_2_name;
            }
            break;
            
        case 3:
            while ($row = mysqli_fetch_array($result)) {
                
                $summarize_by_1_name = trim($row[0]);
                $summarize_by_2_name = trim($row[1]);
                $summarize_by_3_name = trim($row[2]);
                
                // if we have a new summarize by in summarize by 1, increment key and store name, and reset key and store name for summarize by 2 and summarize by 3
                if (mb_strtolower($summarize_by_1_name) !== mb_strtolower($previous_summarize_by_1_name)) {
                    $summarize_by_1_key++;
                    $results[$summarize_by_1_key]['name'] = $summarize_by_1_name;
                    
                    $summarize_by_2_key = 0;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['name'] = $summarize_by_2_name;
                    
                    $summarize_by_3_key = 0;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['name'] = $summarize_by_3_name;
                    
                // else if we have a new summarize by in summarize by 2, increment key and store name and reset key and store name for summarize by 3
                } elseif (mb_strtolower($summarize_by_2_name) !== mb_strtolower($previous_summarize_by_2_name)) {
                    $summarize_by_2_key++;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['name'] = $summarize_by_2_name;
                    
                    $summarize_by_3_key = 0;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['name'] = $summarize_by_3_name;
                    
                // else if we have a new summarize by in summarize by 3, increment key and store name
                } elseif (mb_strtolower($summarize_by_3_name) !== mb_strtolower($previous_summarize_by_3_name)) {
                    $summarize_by_3_key++;
                    $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['name'] = $summarize_by_3_name;
                }
                
                // if order is complete, use values from query
                if ($row['status'] != 'incomplete') {
                    $subtotal = $row['subtotal'];
                    $tax = $row['tax'];
                    $shipping = $row['shipping'];
                    $total = $row['total'];
                
                // else order is incomplete, so use values from incomplete orders array
                } else {
                    $subtotal = $incomplete_orders[$row['id']]['subtotal'];
                    $tax = $incomplete_orders[$row['id']]['tax'];
                    $shipping = $incomplete_orders[$row['id']]['shipping'];
                    $total = $incomplete_orders[$row['id']]['total'];
                }
                
                // store data for order in array
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['orders'][] = array(
                    'id' => $row['id'],
                    'order_number' => $row['order_number'],
                    'order_date' => $row['order_date'],
                    'subtotal' => $subtotal,
                    'discount' => $row['discount'],
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'total' => $total);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['total'] += $total;
                
                // update totals for summarize by 2
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['total'] += $total;
                
                // update totals for summarize by 3
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['subtotal'] += $subtotal;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['discount'] += $row['discount'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['tax'] += $tax;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['shipping'] += $shipping;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['total'] += $total;
                
                // update grand totals
                $grand_count++;
                $grand_subtotal += $subtotal;
                $grand_discount += $row['discount'];
                $grand_tax += $tax;
                $grand_shipping += $shipping;
                $grand_total += $total;
                
                $previous_summarize_by_1_name = $summarize_by_1_name;
                $previous_summarize_by_2_name = $summarize_by_2_name;
                $previous_summarize_by_3_name = $summarize_by_3_name;
            }
            break;
    }
    
    $output_report_detail_headers = '';
    $output_report_detail_cells = '';
    
    // if detail is on, then prepare to output detail
    if ($liveform->get_field_value('detail') == 1) {
        $output_report_detail_headers =
            '<th>Order Number</th>
            <th>Date</th>';
        
        $output_report_detail_cells =
            '<td>&nbsp;</td>
            <td>&nbsp;</td>';
    }
    
    // Check to see if the order report name is empty, and add new order report header if it is.
    if ($output_order_report_name == '') {
        $output_order_report_name = '[new order report]';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($output_order_report_name) . '</h1>
        </div>
        ' . $output_edit_button . '
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Order Report</h1>
            <div class="subheading" style="margin-bottom: 1em">View or update this real-time order report.</div>
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <form action="view_order_report.php" id="edit_form" method="post" style="margin-bottom: 2em' . $output_edit_form_style . '">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'id'=>'last_filter_number', 'name'=>'last_filter_number', 'value'=>'0')) . '
                ' . $output_hidden_id_field . '
                <h2 style="margin-bottom: .5em">Order Report Name</h2>
                <table class="field">
                    <tr>
                        <td>Order Report Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                </table>
                <h2 style="margin-bottom: .5em">Order Report Layout</h2>
                <table class="field">
                    <tr>
                        <td>Summarize by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'summarize_by_1', 'options'=>$summarize_by_options)) . '</td>
                        <td>order by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'order_by_1', 'options'=>$order_by_options)) . '</td>
                    </tr>
                    <tr>
                        <td>and then by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'summarize_by_2', 'options'=>$summarize_by_options)) . '</td>
                        <td>order by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'order_by_2', 'options'=>$order_by_options)) . '</td>
                    </tr>
                    <tr>
                        <td>and finally by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'summarize_by_3', 'options'=>$summarize_by_options)) . '</td>
                        <td>order by</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'order_by_3', 'options'=>$order_by_options)) . '</td>
                    </tr>
                </table>
                <script type="text/javascript" language="JavaScript 1.2">
                    var last_filter_number = 0;
                    
                    var filters = new Array();
                    
                    ' . $output_filters_for_javascript . '
                    
                    var field_options = new Array();
                    
                    ' . $output_field_options_for_javascript . '
                    
                    window.onload = initialize_filters;
                </script>                
                <h2 style="margin-bottom: .85em">Order Report Filters</h2>
                <div style="margin: 2em 0em"><a href="javascript:void(0)" onclick="create_filter()" class="button">Add Filter</a></div>
                <table id="filter_table" class="chart_no_hover">
                    <tr>
                        <th style="text-align: left; padding-right: 3em"">Field</th>
                        <th style="text-align: left; padding-right: 3em"">Operation</th>
                        <th style="text-align: left; padding-right: 3em"">Value</th>
                        <th style="text-align: left; padding-right: 3em"">Dynamic Value</th>
                        <th style="text-align: left; padding-right: 3em"">&nbsp;</th>
                    </tr>
                </table>
                <h2 style="margin-bottom: .5em">Include Order Session Information</h2>
                <table class="field">
                    <tr>
                        <td><label for="detail">Show Detail:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'detail', 'id'=>'detail', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save &amp; Run" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="' . $output_cancel_button_onclick . '" class="submit-secondary" />&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
            </form>
            ' . $output_date_changer . '
            <table class="chart">
                <tr>
                    <th colspan="' . $number_of_summarize_bys . '">&nbsp;</th>
                    ' . $output_report_detail_headers . '
                    <th style="text-align: right">Orders</th>
                    <th style="text-align: right">Orders %</th>
                    <th style="text-align: right">Subtotal</th>
                    <th style="text-align: right">Discount</th>
                    <th style="text-align: right">Tax</th>
                    <th style="text-align: right">Shipping</th>
                    <th style="text-align: right">Total</th>
                    <th style="text-align: right">Total %</th>
                </tr>';

    // if there is at least one summarize by
    if ($number_of_summarize_bys >= 1) {
        // if an order by has been selected for summarize by 1
        if (get_order_by_sort_value($liveform->get_field_value('order_by_1'))) {
            // prepare temp array in order to sort array
            $temp = array();    
            
            foreach ($results as $result) {
                 $temp[] = $result[get_order_by_sort_value($liveform->get_field_value('order_by_1'))];
            }

            array_multisort($temp, SORT_DESC, $results);
        }
        
        foreach ($results as $summarize_by_1_result) {
            $summarize_by_1_name = update_summarize_by_name($summarize_by_1_result['name'], $liveform->get_field_value('summarize_by_1'));
            
            // prevent division by zero
            if ($grand_total > 0) {
                $total_percentage = $summarize_by_1_result['total'] / $grand_total * 100;
            } else {
                $total_percentage = 0;
            }
            
            print
                '<tr style="font-weight: bold; color: #008000; cursor: default">
                    <td colspan="' . $number_of_summarize_bys . '">' . h($summarize_by_1_name) . '</td>
                    ' . $output_report_detail_cells . '
                    <td style="text-align: right">' . number_format($summarize_by_1_result['count']) . '</td>
                    <td style="text-align: right">' . number_format($summarize_by_1_result['count'] / $grand_count * 100, 2) . '%</td>
                    <td style="text-align: right">' . prepare_amount($summarize_by_1_result['subtotal'] / 100) . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_1_result['discount'] / 100, 2, '.', ',') . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_1_result['tax'] / 100, 2, '.', ',') . '</td>
                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_1_result['shipping'] / 100, 2, '.', ',') . '</td>
                    <td style="text-align: right">' . prepare_amount($summarize_by_1_result['total'] / 100) . '</td>
                    <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                </tr>';
            
            // if there is at least 2 summarize bys
            if ($number_of_summarize_bys >= 2) {
                // if an order by has been selected for summarize by 2
                if (get_order_by_sort_value($liveform->get_field_value('order_by_2'))) {
                    // prepare temp array in order to sort array
                    $temp = array();    
                    
                    foreach ($summarize_by_1_result['results'] as $result) {
                         $temp[] = $result[get_order_by_sort_value($liveform->get_field_value('order_by_2'))];
                    }

                    array_multisort($temp, SORT_DESC, $summarize_by_1_result['results']);
                }
                
                foreach ($summarize_by_1_result['results'] as $summarize_by_2_result) {
                    $summarize_by_2_name = update_summarize_by_name($summarize_by_2_result['name'], $liveform->get_field_value('summarize_by_2'));
                    
                    $colspan = $number_of_summarize_bys - 1;
                    
                    // prevent division by zero
                    if ($summarize_by_1_result['total'] > 0) {
                        $total_percentage = $summarize_by_2_result['total'] / $summarize_by_1_result['total'] * 100;
                    } else {
                        $total_percentage = 0;
                    }
                    
                    print
                        '<tr style="font-weight: bold; color: #719700; cursor: default">
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td colspan="' . $colspan . '">' . h($summarize_by_2_name) . '</td>
                            ' . $output_report_detail_cells . '
                            <td style="text-align: right">' . number_format($summarize_by_2_result['count']) . '</td>
                            <td style="text-align: right">' . number_format($summarize_by_2_result['count'] / $summarize_by_1_result['count'] * 100, 2) . '%</td>
                            <td style="text-align: right">' . prepare_amount($summarize_by_2_result['subtotal'] / 100) . '</td>
                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_2_result['discount'] / 100, 2, '.', ',') . '</td>
                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_2_result['tax'] / 100, 2, '.', ',') . '</td>
                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_2_result['shipping'] / 100, 2, '.', ',') . '</td>
                            <td style="text-align: right">' . prepare_amount($summarize_by_2_result['total'] / 100) . '</td>
                            <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                        </tr>';
                    
                    // if there are 3 summarize bys
                    if ($number_of_summarize_bys == 3) {
                        // if an order by has been selected for summarize by 3
                        if (get_order_by_sort_value($liveform->get_field_value('order_by_3'))) {
                            // prepare temp array in order to sort array
                            $temp = array();    
                            
                            foreach ($summarize_by_2_result['results'] as $result) {
                                 $temp[] = $result[get_order_by_sort_value($liveform->get_field_value('order_by_3'))];
                            }

                            array_multisort($temp, SORT_DESC, $summarize_by_2_result['results']);
                        }
                        
                        foreach ($summarize_by_2_result['results'] as $summarize_by_3_result) {
                            $summarize_by_3_name = update_summarize_by_name($summarize_by_3_result['name'], $liveform->get_field_value('summarize_by_3'));

                            // prevent division by zero
                            if ($summarize_by_2_result['total'] > 0) {
                                $total_percentage = $summarize_by_3_result['total'] / $summarize_by_2_result['total'] * 100;
                            } else {
                                $total_percentage = 0;
                            }

                            print
                                '<tr style="font-weight: bold; color: #808080; cursor: default">
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td>' . h($summarize_by_3_name) . '</td>
                                    ' . $output_report_detail_cells . '
                                    <td style="text-align: right">' . number_format($summarize_by_3_result['count']) . '</td>
                                    <td style="text-align: right">' . number_format($summarize_by_3_result['count'] / $summarize_by_2_result['count'] * 100, 2) . '%</td>
                                    <td style="text-align: right">' . prepare_amount($summarize_by_3_result['subtotal'] / 100) . '</td>
                                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_3_result['discount'] / 100, 2, '.', ',') . '</td>
                                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_3_result['tax'] / 100, 2, '.', ',') . '</td>
                                    <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($summarize_by_3_result['shipping'] / 100, 2, '.', ',') . '</td>
                                    <td style="text-align: right">' . prepare_amount($summarize_by_3_result['total'] / 100) . '</td>
                                    <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                                </tr>';
                            
                            if ($liveform->get_field_value('detail') == 1) {
                                foreach ($summarize_by_3_result['orders'] as $order) {
                                    if (!$order['order_number']) {
                                        $order['order_number'] = '[Incomplete]';
                                    }
                                    
                                    // prevent division by zero
                                    if ($summarize_by_3_result['total'] > 0) {
                                        $total_percentage = $order['total'] / $summarize_by_3_result['total'] * 100;
                                    } else {
                                        $total_percentage = 0;
                                    }
                                    
                                    $output_link_url = 'view_order.php?id=' . $order['id'] . '&send_to=' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/view_order_report.php' . h(escape_javascript($query_string_id));
                                    
                                    print
                                        '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                            <td colspan="3">&nbsp;</td>
                                            <td>' . $order['order_number'] . '</td>
                                            <td>' . get_relative_time(array('timestamp' => $order['order_date'])) . '</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right">' . prepare_amount($order['subtotal'] / 100) . '</td>
                                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['discount'] / 100, 2, '.', ',') . '</td>
                                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['tax'] / 100, 2, '.', ',') . '</td>
                                            <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['shipping'] / 100, 2, '.', ',') . '</td>
                                            <td style="text-align: right">' . prepare_amount($order['total'] / 100) . '</td>
                                            <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                                        </tr>';
                                }
                            }
                        }
                        
                    // else there are not 3 summarize bys
                    } else {
                        if ($liveform->get_field_value('detail') == 1) {
                            foreach ($summarize_by_2_result['orders'] as $order) {
                                if (!$order['order_number']) {
                                    $order['order_number'] = '[Incomplete]';
                                }
                                
                                // prevent division by zero
                                if ($summarize_by_2_result['total'] > 0) {
                                    $total_percentage = $order['total'] / $summarize_by_2_result['total'] * 100;
                                } else {
                                    $total_percentage = 0;
                                }
                                
                                $output_link_url = 'view_order.php?id=' . $order['id'] . '&send_to=' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/view_order_report.php' . h(escape_javascript($query_string_id));
                                
                                print
                                    '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                        <td colspan="2">&nbsp;</td>
                                        <td>' . $order['order_number'] . '</td>
                                        <td>' . get_relative_time(array('timestamp' => $order['order_date'])) . '</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right">' . prepare_amount($order['subtotal'] / 100) . '</td>
                                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['discount'] / 100, 2, '.', ',') . '</td>
                                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['tax'] / 100, 2, '.', ',') . '</td>
                                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['shipping'] / 100, 2, '.', ',') . '</td>
                                        <td style="text-align: right">' . prepare_amount($order['total'] / 100) . '</td>
                                        <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                                    </tr>';
                            }
                        }
                    }
                }
                
            // else there is not at least 2 summarize bys
            } else {
                if ($liveform->get_field_value('detail') == 1) {
                    foreach ($summarize_by_1_result['orders'] as $order) {
                        if (!$order['order_number']) {
                            $order['order_number'] = '[Incomplete]';
                        }
                        
                        // prevent division by zero
                        if ($summarize_by_1_result['total'] > 0) {
                            $total_percentage = $order['total'] / $summarize_by_1_result['total'] * 100;
                        } else {
                            $total_percentage = 0;
                        }
                        
                        $output_link_url = 'view_order.php?id=' . $order['id'] . '&send_to=' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/view_order_report.php' . h(escape_javascript($query_string_id));
                        
                        print
                            '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                <td>&nbsp;</td>
                                <td>' . $order['order_number'] . '</td>
                                <td>' . get_relative_time(array('timestamp' => $order['order_date'])) . '</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right">' . prepare_amount($order['subtotal'] / 100) . '</td>
                                <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['discount'] / 100, 2, '.', ',') . '</td>
                                <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['tax'] / 100, 2, '.', ',') . '</td>
                                <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['shipping'] / 100, 2, '.', ',') . '</td>
                                <td style="text-align: right">' . prepare_amount($order['total'] / 100) . '</td>
                                <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                            </tr>';
                    }
                }
            }
        }
        
    // else there is not at least 1 summarize by
    } else {
        if ($liveform->get_field_value('detail') == 1) {
            foreach ($orders as $order) {
                if (!$order['order_number']) {
                    $order['order_number'] = '[Incomplete]';
                }
                
                // prevent division by zero
                if ($grand_total > 0) {
                    $total_percentage = $order['total'] / $grand_total * 100;
                } else {
                    $total_percentage = 0;
                }
                
                $output_link_url = 'view_order.php?id=' . $order['id'] . '&send_to=' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY)) . '/view_order_report.php' . h(escape_javascript($query_string_id));
                
                print
                    '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                        <td>&nbsp;</td>
                        <td>' . $order['order_number'] . '</td>
                        <td>' . get_relative_time(array('timestamp' => $order['order_date'])) . '</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right">' . prepare_amount($order['subtotal'] / 100) . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['discount'] / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['tax'] / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($order['shipping'] / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . prepare_amount($order['total'] / 100) . '</td>
                        <td style="text-align: right">' . number_format($total_percentage, 2) . '%</td>
                    </tr>';
            }
        }
    }

    print
            '        <tr style="font-weight: bold; color: #c49700;  cursor: default">
                        <td colspan="' . $number_of_summarize_bys . '">Grand Total</td>
                        ' . $output_report_detail_cells . '
                        <td style="text-align: right">' . number_format($grand_count) . '</td>
                        <td style="text-align: right">100.00%</td>
                        <td style="text-align: right">' . prepare_amount($grand_subtotal / 100) . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($grand_discount / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($grand_tax / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . BASE_CURRENCY_SYMBOL . number_format($grand_shipping / 100, 2, '.', ',') . '</td>
                        <td style="text-align: right">' . prepare_amount($grand_total / 100) . '</td>
                        <td style="text-align: right">100.00%</td>
                    </tr>
                </table>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();
    
// else the form has been submitted, so process form
} else {
    validate_token_field();

    $liveform->add_fields_to_session();
    
    // if the user selected to delete this order report, then delete it
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        // get order report name for log
        $query = "SELECT name FROM order_reports WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $order_report_name = $row['name'];
        
        // delete order report
        $query = "DELETE FROM order_reports WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete order report filters
        $query = "DELETE FROM order_report_filters WHERE order_report_id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('order report (' . $order_report_name . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_order_reports = new liveform('view_order_reports');
        $liveform_view_order_reports->add_notice('The order report has been deleted.');
        
        // send user to view order reports screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_order_reports.php');
        
        $liveform->remove_form();
        
        exit();
        
    // else the user did not choose to delete the order report
    } else {
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to previous screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_order_report.php' . $query_string_id);
            exit();
        }
        
        // check to see if name is already in use
        $query =
            "SELECT id
            FROM order_reports
            WHERE
                (name = '" . escape($liveform->get_field_value('name')) . "')
                AND (id != '" . escape($liveform->get_field_value('id')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use, prepare error and forward user back to previous screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to previous screen
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_order_report.php' . $query_string_id);
            exit();
        }
        
        // if the user is creating a new order report, then create order report record
        if ($liveform->field_in_session('id') == false) {
            $query =
                "INSERT INTO order_reports (
                    name,
                    detail,
                    summarize_by_1,
                    order_by_1,
                    summarize_by_2,
                    order_by_2,
                    summarize_by_3,
                    order_by_3,
                    created_user_id,
                    created_timestamp,
                    last_modified_user_id,
                    last_modified_timestamp)
                VALUES (
                    '" . escape($liveform->get_field_value('name')) . "',
                    '" . escape($liveform->get_field_value('detail')) . "',
                    '" . escape($liveform->get_field_value('summarize_by_1')) . "',
                    '" . escape($liveform->get_field_value('order_by_1')) . "',
                    '" . escape($liveform->get_field_value('summarize_by_2')) . "',
                    '" . escape($liveform->get_field_value('order_by_2')) . "',
                    '" . escape($liveform->get_field_value('summarize_by_3')) . "',
                    '" . escape($liveform->get_field_value('order_by_3')) . "',
                    '" . $user['id'] . "',
                    UNIX_TIMESTAMP(),
                    '" . $user['id'] . "',
                    UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $id = mysqli_insert_id(db::$con);
            $query_string_id = '?id=' . $id;
            
        // else the user is updating an existing order report, so update order report record
        } else {
            $query =
                "UPDATE order_reports
                SET
                    name = '" . escape($liveform->get_field_value('name')) . "',
                    detail = '" . escape($liveform->get_field_value('detail')) . "',
                    summarize_by_1 = '" . escape($liveform->get_field_value('summarize_by_1')) . "',
                    order_by_1 = '" . escape($liveform->get_field_value('order_by_1')) . "',
                    summarize_by_2 = '" . escape($liveform->get_field_value('summarize_by_2')) . "',
                    order_by_2 = '" . escape($liveform->get_field_value('order_by_2')) . "',
                    summarize_by_3 = '" . escape($liveform->get_field_value('summarize_by_3')) . "',
                    order_by_3 = '" . escape($liveform->get_field_value('order_by_3')) . "',
                    last_modified_user_id = '" . $user['id'] . "',
                    last_modified_timestamp = UNIX_TIMESTAMP()
                WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // delete order report filters
            $query = "DELETE FROM order_report_filters WHERE order_report_id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        $order_date_filter_exists = false;
        
        // loop through all filters in order to insert filters into database
        for ($i = 1; $i <= $liveform->get_field_value('last_filter_number'); $i++) {
            // if filter exists and an operator was selected for this filter, then insert filter
            if ($liveform->get_field_value('filter_' . $i . '_operator') != '') {
                // get field type
                $field_type = '';
                
                // if the field for this filter is order date, then set type to date
                if ($liveform->get_field_value('filter_' . $i . '_field') == 'order_date') {
                    $field_type = 'date';
                    $order_date_filter_exists = true;
                }
                
                // if user entered a value, clear dynamic value, in order to prevent user from using two values
                if ($liveform->get_field_value('filter_' . $i . '_value') != '') {
                    $dynamic_value = '';
                    $dynamic_value_attribute = '';
                } else {
                    $dynamic_value = $liveform->get_field_value('filter_' . $i . '_dynamic_value');
                    
                    // if days ago was selected for dynamic value, then set dynamic value attribute
                    if ($dynamic_value == 'days ago') {
                        $dynamic_value_attribute = $liveform->get_field_value('filter_' . $i . '_dynamic_value_attribute');
                    } else {
                        $dynamic_value_attribute = '';
                    }
                }
                
                // insert filter
                $query =
                    "INSERT INTO order_report_filters (
                        order_report_id,
                        field,
                        operator,
                        value,
                        dynamic_value,
                        dynamic_value_attribute)
                    VALUES (
                        '" . escape($id) . "',
                        '" . escape($liveform->get_field_value('filter_' . $i . '_field')) . "',
                        '" . escape($liveform->get_field_value('filter_' . $i . '_operator')) . "',
                        '" . escape(prepare_form_data_for_input($liveform->get_field_value('filter_' . $i . '_value'), $field_type)) . "',
                        '" . escape($dynamic_value) . "',
                        '" . escape($dynamic_value_attribute) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
        
        $order_date_filter_message = '';
        
        // if there is an order date filter, then prepare message
        if ($order_date_filter_exists == true) {
            $order_date_filter_message = ' Date browsing has been disabled because there is a date filter in this report.';
        }
        
        // if the user is creating a new order report, then log activity, remove form, and add notice in a certain way
        if ($liveform->field_in_session('id') == false) {
            log_activity('order report (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
            $liveform->remove_form();
            $liveform->add_notice('The order report has been created, and the results appear below.' . $order_date_filter_message);
            
        // else the user is updating an existing order report, so log activity, remove form, and add notice in a certain way
        } else {
            log_activity('order report (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
            $liveform->remove_form();
            $liveform->add_notice('The order report has been saved, and the results appear below.' . $order_date_filter_message);
        }
        
        // send user back to order report
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_order_report.php' . $query_string_id);
        exit();
    }
}

function get_summarize_by_column($summarize_by)
{
    switch ($summarize_by) {
        case 'year': return 'FROM_UNIXTIME(orders.order_date, \'%Y\')';
        case 'month': return 'FROM_UNIXTIME(orders.order_date, \'%m\')';
        case 'day': return 'FROM_UNIXTIME(orders.order_date, \'%d\')';
        case 'order_status': return 'orders.status';
        case 'special_offer_code': return 'orders.special_offer_code';
        case 'referral_source_code': return 'orders.referral_source_code';
        case 'reference_code': return 'orders.reference_code';
        case 'tracking_code': return 'orders.tracking_code';
        case 'http_referer': return 'orders.http_referer';
        case 'ip_address': return 'CASE WHEN (INET_NTOA(orders.ip_address) = "0.0.0.0") THEN "" ELSE INET_NTOA(orders.ip_address) END';

        case 'utm_source': return 'orders.utm_source';
        case 'utm_medium': return 'orders.utm_medium';
        case 'utm_campaign': return 'orders.utm_campaign';
        case 'utm_term': return 'orders.utm_term';
        case 'utm_content': return 'orders.utm_content';

        case 'payment_method': return 'orders.payment_method';
        case 'card_type': return 'orders.card_type';
        case 'cardholder': return 'orders.cardholder';
        case 'card_number': return 'orders.card_number';
        case 'custom_field_1': return 'orders.custom_field_1';
        case 'custom_field_2': return 'orders.custom_field_2';
        case 'billing_salutation': return 'orders.billing_salutation';
        case 'billing_first_name': return 'orders.billing_first_name';
        case 'billing_last_name': return 'orders.billing_last_name';
        case 'billing_company': return 'orders.billing_company';
        case 'billing_address_1': return 'orders.billing_address_1';
        case 'billing_address_2': return 'orders.billing_address_2';
        case 'billing_city': return 'orders.billing_city';
        case 'billing_state': return 'orders.billing_state';
        case 'billing_zip_code': return 'orders.billing_zip_code';
        case 'billing_country': return 'orders.billing_country';
        case 'billing_phone_number': return 'orders.billing_phone_number';
        case 'billing_fax_number': return 'orders.billing_fax_number';
        case 'billing_email_address': return 'orders.billing_email_address';
        case 'opt_in_status': return 'orders.opt_in';
        case 'po_number': return 'orders.po_number';
        case 'tax_status': return 'orders.tax_exempt';
        case 'currency_code': return 'orders.currency_code';
    }
}

function update_summarize_by_name($summarize_by_name, $summarize_by) {
    global $user;
    
    switch ($summarize_by) {
        case 'month':
            $summarize_by_name = get_month_name_from_number($summarize_by_name);
            break;
            
        case 'order_status':
            $summarize_by_name = ucwords($summarize_by_name);
            break;
            
        case 'ip_address':
            // if the IP address is 0.0.0.0, then we don't know the IP address, so set it to [Not Specified]
            if ($summarize_by_name == '0.0.0.0') {
                $summarize_by_name = '[Not Specified]';
            }
            break;
            
        case 'opt_in_status':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Opt-In';
            } else {
                $summarize_by_name = 'Opt-Out';
            }
            break;
            
        case 'tax_status':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Tax-Exempt';
            } else {
                $summarize_by_name = 'Not Tax-Exempt';
            }
            break;
            
        case 'currency_code':
            // if the currency is known then output currency
            if ($summarize_by_name != '') {
                $summarize_by_name = get_currency_name_from_code($summarize_by_name) . ' (' . $summarize_by_name . ')';
                
            // else the currency is not known so output placeholder
            } else {
                $summarize_by_name = '[Not Specified]';
            }
            break;
            
        case 'payment_method':
            // if there is no payment method, then set to None
            if ($summarize_by_name == '') {
                $summarize_by_name = 'None';
            }
            
            break;
            
        case 'card_number':
            $card_number = $summarize_by_name;
            
            // if the credit card number is encrypted, then decrypt it
            if ((mb_substr($card_number, 0, 1) != '*') && (mb_strlen($card_number) > 16)) {
                // if encryption is enabled, then decrypt the credit card number
                if (
                    (defined('ENCRYPTION_KEY') == TRUE)
                    && (extension_loaded('mcrypt') == TRUE)
                    && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
                ) {
                    $card_number = decrypt_credit_card_number($card_number, ENCRYPTION_KEY);
                    
                    // if the credit card number is not numeric, then there was a decryption error
                    if (is_numeric($card_number) == FALSE) {
                        $card_number = '[decryption error]';

                    // else the credit card number was decrypted successfully,
                    // so if the user does not have access to view card data,
                    // then protect the credit card number
                    } else if (($user['role'] == 3) && ($user['view_card_data'] == FALSE)) {
                        $card_number = protect_credit_card_number($card_number);
                    }
                    
                // else encryption is disabled, so output error
                } else {
                    $card_number = '[decryption error]';
                }
            }
            
            $summarize_by_name = $card_number;
            
            break;
    }
    
    if ($summarize_by_name == '') {
        $summarize_by_name = '[Not Specified]';
    }
    
    return $summarize_by_name;
}

function get_order_by_sort_value($order_by)
{
    switch ($order_by) {
        case 'alphabet': return '';
        case 'number of orders': return 'count';
        case 'total': return 'total';
    }
}