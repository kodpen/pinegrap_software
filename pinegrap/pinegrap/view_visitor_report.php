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
validate_visitors_access($user);

$liveform = new liveform('view_visitor_report');

// If the base currency symbol is not defined, then that is because commerce
// is disabled, so we need to set a base currency.  This is the only area
// that shows currency when commerce is disabled.  In the future,
// we should probably consider not showing commerce data on this screen
// if commerce is disabled, however we are not going to spend time on that for now.
if (defined('BASE_CURRENCY_SYMBOL') == false) {
    define('BASE_CURRENCY_SYMBOL', '$');
}

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

// prepare query string with id if necessary
$query_string_visitor_report_id = '';

// if an id is set, then prepare query string with id
if (isset($id) == true) {
    $query_string_visitor_report_id = '&visitor_report_id=' . $id;
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
    
    // if the visitor report is being viewed
    if (isset($_GET['id']) == true) {
        $output_edit_button = '<div id="button_bar"><a href="#" id="edit_button" onclick="document.getElementById(\'button_bar\').style.display = \'none\'; document.getElementById(\'edit_form\').style.display = \'block\'; return false;">Edit Visitor Report</a></div>';
        $output_edit_form_style = '; display: none';
    }
    
    // if a visitor report is being created and this screen has not already been submitted, then set default values for form fields
    if ((isset($_GET['id']) == false) && ($liveform->field_in_session('submit_button') == false)) {
        // set default values for summarize by fields
        $liveform->assign_field_value('summarize_by_1', 'year');
        $liveform->assign_field_value('summarize_by_2', 'month');
        $liveform->assign_field_value('summarize_by_3', 'day');
    
    // else if a visitor report is being edited and this screen has not been submitted already, pre-populate fields with data
    } elseif ((isset($_GET['id']) == true) && ($liveform->field_in_session('id') == false)) {
        // get visitor report data
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
            FROM visitor_reports
            WHERE id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $output_visitor_report_name = $row['name'];
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('detail', $row['detail']);
        $liveform->assign_field_value('summarize_by_1', $row['summarize_by_1']);
        $liveform->assign_field_value('order_by_1', $row['order_by_1']);
        $liveform->assign_field_value('summarize_by_2', $row['summarize_by_2']);
        $liveform->assign_field_value('order_by_2', $row['order_by_2']);
        $liveform->assign_field_value('summarize_by_3', $row['summarize_by_3']);
        $liveform->assign_field_value('order_by_3', $row['order_by_3']);
    }
    
    // if a visitor report is being created, then prepare screen name
    if (isset($_GET['id']) == false) {
        $output_screen_name = 'Create Visitor Report';
        
    // else a visitor report is being viewed, so prepare screen name
    } else {
        $output_screen_name = 'View Visitor Report: <strong>' . $liveform->get_field_value('name') . '</strong>';
    }
    
    $output_hidden_id_field = '';
    
    // if a visitor report is being edited, then prepare to display hidden id field
    if (isset($_GET['id']) == true) {
        $output_hidden_id_field = $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id']));
    }
    
    $summarize_by_options = array();
    
    $summarize_by_options['-None-'] = '';
    $summarize_by_options['General'] = '<optgroup>';
    $summarize_by_options['Year'] = 'year';
    $summarize_by_options['Month'] = 'month';
    $summarize_by_options['Day'] = 'day';
    $summarize_by_options['Site Search Terms'] = 'site_search_terms';
    
    // if multi currency is enabled, prepare currency option
    if (ECOMMERCE_MULTICURRENCY == true) {
        $summarize_by_options['Currency'] = 'currency_code';
    }
    
    $summarize_by_options[''] = '</optgroup>';
    $summarize_by_options['Referral'] = '<optgroup>';
    $summarize_by_options['URL'] = 'http_referer';
    $summarize_by_options['Host Name'] = 'referring_host_name';
    $summarize_by_options['Search Engine'] = 'referring_search_engine';
    $summarize_by_options['Search Terms'] = 'referring_search_terms';
    $summarize_by_options['Pay Per Click / Organic'] = 'pay_per_click_organic';
    $summarize_by_options['First Visit'] = 'first_visit';
    $summarize_by_options['Landing Page'] = 'landing_page_name';
    $summarize_by_options['Tracking Code'] = 'tracking_code';
    
    // if affiliate program is enabled, prepare affiliate code option
    if (AFFILIATE_PROGRAM == true) {
        $summarize_by_options['Affiliate Code'] = 'affiliate_code';
    }
    
    $summarize_by_options[''] = '</optgroup>';

    $summarize_by_options['UTM'] = '<optgroup>';
    $summarize_by_options['Source'] = 'utm_source';
    $summarize_by_options['Medium'] = 'utm_medium';
    $summarize_by_options['Campaign'] = 'utm_campaign';
    $summarize_by_options['Term'] = 'utm_term';
    $summarize_by_options['Content'] = 'utm_content';
    $summarize_by_options[''] = '</optgroup>';

    $summarize_by_options['Action'] = '<optgroup>';
    $summarize_by_options['Page Views'] = 'page_views';
    $summarize_by_options['Custom Form Submitted'] = 'custom_form_submitted';
    $summarize_by_options['Custom Form Name'] = 'custom_form_name';
    $summarize_by_options['Order Created'] = 'order_created';
    $summarize_by_options['Order Retrieved'] = 'order_retrieved';
    $summarize_by_options['Order Checked Out'] = 'order_checked_out';
    $summarize_by_options['Order Completed'] = 'order_completed';
    $summarize_by_options[''] = '</optgroup>';
    $summarize_by_options['Location'] = '<optgroup>';
    $summarize_by_options['City'] = 'city';
    $summarize_by_options['State'] = 'state';
    $summarize_by_options['Zip Code'] = 'zip_code';
    $summarize_by_options['Country'] = 'country';
    $summarize_by_options[''] = '</optgroup>';
    
    $order_by_options = array(
        'alphabet' => 'alphabet',
        'number of visitors' => 'number of visitors',
        'number of page views' => 'number of page views',
        'order total' => 'order total'
    );
    
    // get filters
    $filters = array();
    
    // if a visitor report is being created or a form has been submitted, then get filters from liveform
    if ((isset($_GET['id']) == false) || ($liveform->field_in_session('submit_button') == true)) {
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
        
    // else a visitor report is being edited and a form has not been submitted, so get filters from database
    } else {
        $query =
            "SELECT
                field,
                operator,
                value,
                dynamic_value,
                dynamic_value_attribute
            FROM visitor_report_filters
            WHERE visitor_report_id = '" . escape($_GET['id']) . "'
            ORDER BY id";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            // get field type
            $field_type = '';
            
            // if the field for this filter is date, then set type to date
            if ($row['field'] == 'date') {
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
    $date_filter_exists = false;
    $output_filters_for_javascript = '';
    $count = 0;
    
    // loop through filters in order to prepare output for javascript
    foreach ($filters as $filter) {
        // if the field for this filter is date, then remember that a date filter exists
        if ($filter['field'] == 'date') {
            $date_filter_exists = true;
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
    $field_options[] = array('name' => 'Date', 'value' => 'date', 'type' => 'date');
    $field_options[] = array('name' => 'Site Search Terms', 'value' => 'site_search_terms');
    
    // if multi currency is enabled, prepare currency option
    if (ECOMMERCE_MULTICURRENCY == true) {
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
    
    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    $field_options[] = array('name' => 'Referral', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'URL', 'value' => 'http_referer');
    $field_options[] = array('name' => 'Host Name', 'value' => 'referring_host_name');
    
    // create array for referring search engines
    $referring_search_engines = array(
        'AlltheWeb',
        'AltaVista',
        'AOL',
        'Ask.com',
        'Bing',
        'Comet Web Search',
        'EarthLink',
        'Excite',
        'Google',
        'HotBot',
        'LookSmart',
        'Lycos',
        'Mamma.com',
        'MetaCrawler',
        'MSN',
        'Netscape',
        'Open Directory Project',
        'Overture',
        'Viewpoint',
        'WebCrawler',
        'Yahoo!'
    );
    
    $referring_search_engine_options = array();
    
    // loop through each referring search engine in order to add it to array
    foreach ($referring_search_engines as $referring_search_engine) {
        $referring_search_engine_options[] = array(
            'name' => $referring_search_engine,
            'value' => $referring_search_engine
        );
    }
    
    $field_options[] = array('name' => 'Search Engine', 'value' => 'referring_search_engine', 'value_options' => $referring_search_engine_options);
    $field_options[] = array('name' => 'Search Terms', 'value' => 'referring_search_terms');
    
    $pay_per_click_organic_options = array();
    $pay_per_click_organic_options[] = array('name' => 'Pay Per Click', 'value' => 'pay_per_click');
    $pay_per_click_organic_options[] = array('name' => 'Organic', 'value' => 'organic');
    $pay_per_click_organic_options[] = array('name' => 'Neither', 'value' => 'neither');
    
    $field_options[] = array('name' => 'Pay Per Click / Organic', 'value' => 'pay_per_click_organic', 'value_options' => $pay_per_click_organic_options);
    
    $first_visit_options = array();
    $first_visit_options[] = array('name' => 'First Visit', 'value' => '1');
    $first_visit_options[] = array('name' => 'Return Visit', 'value' => '0');
    
    $field_options[] = array('name' => 'First Visit', 'value' => 'first_visit', 'value_options' => $pay_per_click_organic_options);
    $field_options[] = array('name' => 'Landing Page', 'value' => 'landing_page_name');
    $field_options[] = array('name' => 'Tracking Code', 'value' => 'tracking_code');
    
    // if the affiliate program is enabled, then prepare affiliate code field
    if (AFFILIATE_PROGRAM == true) {
        $field_options[] = array('name' => 'Affiliate Code', 'value' => 'affiliate_code');
    }
    
    $field_options[] = array('name' => '', 'value' => '</optgroup>');

    $field_options[] = array('name' => 'UTM', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Source', 'value' => 'utm_source');
    $field_options[] = array('name' => 'Medium', 'value' => 'utm_medium');
    $field_options[] = array('name' => 'Campaign', 'value' => 'utm_campaign');
    $field_options[] = array('name' => 'Term', 'value' => 'utm_term');
    $field_options[] = array('name' => 'Content', 'value' => 'utm_content');
    $field_options[] = array('name' => '', 'value' => '</optgroup>');

    $field_options[] = array('name' => 'Action', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'Page Views', 'value' => 'page_views');
    
    $custom_form_submitted_options = array();
    $custom_form_submitted_options[] = array('name' => 'Submitted', 'value' => '1');
    $custom_form_submitted_options[] = array('name' => 'Not Submitted', 'value' => '0');
    
    $field_options[] = array('name' => 'Custom Form Submitted', 'value' => 'custom_form_submitted', 'value_options' => $custom_form_submitted_options);
    $field_options[] = array('name' => 'Custom Form', 'value' => 'custom_form_name');
    
    $order_created_options = array();
    $order_created_options[] = array('name' => 'Created', 'value' => '1');
    $order_created_options[] = array('name' => 'Not Created', 'value' => '0');
    
    $field_options[] = array('name' => 'Order Created', 'value' => 'order_created', 'value_options' => $order_created_options);
    
    $order_retrieved_options = array();
    $order_retrieved_options[] = array('name' => 'Retrieved', 'value' => '1');
    $order_retrieved_options[] = array('name' => 'Not Retrieved', 'value' => '0');
    
    $field_options[] = array('name' => 'Order Retrieved', 'value' => 'order_retrieved', 'value_options' => $order_retrieved_options);
    
    $order_checked_out_options = array();
    $order_checked_out_options[] = array('name' => 'Checked Out', 'value' => '1');
    $order_checked_out_options[] = array('name' => 'Not Checked Out', 'value' => '0');
    
    $field_options[] = array('name' => 'Order Checked Out', 'value' => 'order_checked_out', 'value_options' => $order_checked_out_options);
    
    $order_completed_options = array();
    $order_completed_options[] = array('name' => 'Completed', 'value' => '1');
    $order_completed_options[] = array('name' => 'Not Completed', 'value' => '0');
    
    $field_options[] = array('name' => 'Order Completed', 'value' => 'order_completed', 'value_options' => $order_completed_options);
    $field_options[] = array('name' => '', 'value' => '</optgroup>');
    $field_options[] = array('name' => 'Location', 'value' => '<optgroup>');
    $field_options[] = array('name' => 'City', 'value' => 'city');
    $field_options[] = array('name' => 'State', 'value' => 'state');
    $field_options[] = array('name' => 'Zip Code', 'value' => 'zip_code');
    $field_options[] = array('name' => 'Country', 'value' => 'country');
    $field_options[] = array('name' => 'IP Address', 'value' => 'ip_address');
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
    
    // if the user is creating a visitor report, then prepare cancel button to send user back a page
    if (isset($_GET['id']) == false) {
        $output_cancel_button_onclick = 'history.go(-1);';
    
    // else the user is editing a visitor report, so prepare cancel button to hide edit form and show edit button
    } else {
        $output_cancel_button_onclick = 'document.getElementById(\'button_bar\').style.display = \'block\'; document.getElementById(\'edit_form\').style.display = \'none\';';
    }
    
    $output_delete_button = '';
    
    // if user is editing an existing visitor report, then prepare to output delete button
    if (isset($_GET['id']) == true) {
        $output_delete_button = '<input type="submit" name="submit_button" value="Delete" class="delete" onclick="return confirm(\'WARNING: This visitor report will be permanently deleted.\')" />';
    }
    
    // if there are no date filters, then prepare to output date changer
    if ($date_filter_exists == false) {
        // if the date has not been set in the session yet, populate start and stop days with default, which is the past week
        if (isset($_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month']) == false) {
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'] = date('m', time() - 2678400);
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'] = date('d', time() - 2678400);
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] = date('Y', time() - 2678400);
            
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_month'] = date('m');
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_day'] = date('d');
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_year'] = date('Y');
            
        // else if the date has been passed in the query string, then set date in session
        } elseif (isset($_GET['start_month']) == true) {
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'] = $_GET['start_month'];
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'] = $_GET['start_day'];
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] = $_GET['start_year'];
            
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_month'] = $_GET['stop_month'];
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_day'] = $_GET['stop_day'];
            $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_year'] = $_GET['stop_year'];
        }
        
        $decrease_year['start_month'] = '01';
        $decrease_year['start_day'] = '01';
        $decrease_year['start_year'] = $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] - 1;
        $decrease_year['stop_month'] = '12';
        $decrease_year['stop_day'] = '31';
        $decrease_year['stop_year'] = $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] - 1;

        $current_year['start_month'] = '01';
        $current_year['start_day'] = '01';
        $current_year['start_year'] = date('Y');
        $current_year['stop_month'] = '12';
        $current_year['stop_day'] = '31';
        $current_year['stop_year'] = date('Y');

        $increase_year['start_month'] = '01';
        $increase_year['start_day'] = '01';
        $increase_year['start_year'] = $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] + 1;
        $increase_year['stop_month'] = '12';
        $increase_year['stop_day'] = '31';
        $increase_year['stop_year'] = $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year'] + 1;

        $decrease_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'] - 1, 1, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
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

        $increase_month['new_time'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'] + 1, 1, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
        $increase_month['new_month'] = date('m', $increase_month['new_time']);
        $increase_month['new_year'] = date('Y', $increase_month['new_time']);
        $increase_month['start_month'] = $increase_month['new_month'];
        $increase_month['start_day'] = '01';
        $increase_month['start_year'] = $increase_month['new_year'];
        $increase_month['stop_month'] = $increase_month['new_month'];
        $increase_month['stop_day'] = date('t', $increase_month['new_time']);
        $increase_month['stop_year'] = $increase_month['new_year'];

        $decrease_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
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

        $increase_week['start_date_timestamp'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
        $increase_week['new_time_start'] = strtotime('next Sunday', $increase_week['start_date_timestamp']);
        $increase_week['new_time_stop'] = strtotime('Saturday', $increase_week['new_time_start']);
        $increase_week['start_month'] = date('m', $increase_week['new_time_start']);
        $increase_week['start_day'] = date('d', $increase_week['new_time_start']);
        $increase_week['start_year'] = date('Y', $increase_week['new_time_start']);
        $increase_week['stop_month'] = date('m', $increase_week['new_time_stop']);
        $increase_week['stop_day'] = date('d', $increase_week['new_time_stop']);
        $increase_week['stop_year'] = date('Y', $increase_week['new_time_stop']);

        $decrease_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'] - 1, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
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
        
        $increase_day['new_time'] = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'] + 1, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
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
        $start_timestamp = mktime(0, 0, 0, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_day'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['start_year']);
        $stop_timestamp = mktime(23, 59, 59, $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_month'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_day'], $_SESSION['software']['statistics']['view_visitor_report'][$session_index]['stop_year']);
        
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
        
        $output_date_changer = '<div style="margin-bottom: .5em"><span style="display: block; font-size: 150%; font-weight: bold;margin-bottom: .5em;">' . $output_date_range . '</span><a href="view_visitor_report.php?start_month=' . $decrease_year['start_month'] . '&start_day=' . $decrease_year['start_day'] . '&start_year=' . $decrease_year['start_year'] . '&stop_month=' . $decrease_year['stop_month'] . '&stop_day=' . $decrease_year['stop_day'] . '&stop_year=' . $decrease_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_visitor_report.php?start_month=' . $current_year['start_month'] . '&start_day=' . $current_year['start_day'] . '&start_year=' . $current_year['start_year'] . '&stop_month=' . $current_year['stop_month'] . '&stop_day=' . $current_year['stop_day'] . '&stop_year=' . $current_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Year&nbsp;</a><a href="view_visitor_report.php?start_month=' . $increase_year['start_month'] . '&start_day=' . $increase_year['start_day'] . '&start_year=' . $increase_year['start_year'] . '&stop_month=' . $increase_year['stop_month'] . '&stop_day=' . $increase_year['stop_day'] . '&stop_year=' . $increase_year['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_visitor_report.php?start_month=' . $decrease_month['start_month'] . '&start_day=' . $decrease_month['start_day'] . '&start_year=' . $decrease_month['start_year'] . '&stop_month=' . $decrease_month['stop_month'] . '&stop_day=' . $decrease_month['stop_day'] . '&stop_year=' . $decrease_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_visitor_report.php?start_month=' . $current_month['start_month'] . '&start_day=' . $current_month['start_day'] . '&start_year=' . $current_month['start_year'] . '&stop_month=' . $current_month['stop_month'] . '&stop_day=' . $current_month['stop_day'] . '&stop_year=' . $current_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Month&nbsp;</a><a href="view_visitor_report.php?start_month=' . $increase_month['start_month'] . '&start_day=' . $increase_month['start_day'] . '&start_year=' . $increase_month['start_year'] . '&stop_month=' . $increase_month['stop_month'] . '&stop_day=' . $increase_month['stop_day'] . '&stop_year=' . $increase_month['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_visitor_report.php?start_month=' . $decrease_week['start_month'] . '&start_day=' . $decrease_week['start_day'] . '&start_year=' . $decrease_week['start_year'] . '&stop_month=' . $decrease_week['stop_month'] . '&stop_day=' . $decrease_week['stop_day'] . '&stop_year=' . $decrease_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_visitor_report.php?start_month=' . $current_week['start_month'] . '&start_day=' . $current_week['start_day'] . '&start_year=' . $current_week['start_year'] . '&stop_month=' . $current_week['stop_month'] . '&stop_day=' . $current_week['stop_day'] . '&stop_year=' . $current_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Week&nbsp;</a><a href="view_visitor_report.php?start_month=' . $increase_week['start_month'] . '&start_day=' . $increase_week['start_day'] . '&start_year=' . $increase_week['start_year'] . '&stop_month=' . $increase_week['stop_month'] . '&stop_day=' . $increase_week['stop_day'] . '&stop_year=' . $increase_week['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a>&nbsp;&nbsp;<a href="view_visitor_report.php?start_month=' . $decrease_day['start_month'] . '&start_day=' . $decrease_day['start_day'] . '&start_year=' . $decrease_day['start_year'] . '&stop_month=' . $decrease_day['stop_month'] . '&stop_day=' . $decrease_day['stop_day'] . '&stop_year=' . $decrease_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&lt;&nbsp;</a><a href="view_visitor_report.php?start_month=' . $current_day['start_month'] . '&start_day=' . $current_day['start_day'] . '&start_year=' . $current_day['start_year'] . '&stop_month=' . $current_day['stop_month'] . '&stop_day=' . $current_day['stop_day'] . '&stop_year=' . $current_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;Day&nbsp;</a><a href="view_visitor_report.php?start_month=' . $increase_day['start_month'] . '&start_day=' . $increase_day['start_day'] . '&start_year=' . $increase_day['start_year'] . '&stop_month=' . $increase_day['stop_month'] . '&stop_day=' . $increase_day['stop_day'] . '&stop_year=' . $increase_day['stop_year'] . $output_date_changer_query_string_id . '" class="button_3d_secondary">&nbsp;&gt;&nbsp;</a></div>';
    }
    
    // start where
    $where = '';
    
    // loop through all filters in order to prepare SQL
    foreach ($filters as $filter) {
        // get operand 1 and field type when necessary
        $operand_1 = '';
        $field_type = '';
        
        switch ($filter['field']) {
            case 'date': $operand_1 = 'FROM_UNIXTIME(visitors.start_timestamp, \'%Y-%m-%d\')'; $field_type = 'date'; break;
            case 'site_search_terms': $operand_1 = 'visitors.site_search_terms'; break;
            case 'currency_code': $operand_1 = 'visitors.currency_code'; break;
            case 'http_referer': $operand_1 = 'visitors.http_referer'; break;
            case 'referring_host_name': $operand_1 = 'visitors.referring_host_name'; break;
            case 'referring_search_engine': $operand_1 = 'visitors.referring_search_engine'; break;
            case 'referring_search_terms': $operand_1 = 'visitors.referring_search_terms'; break;
            case 'pay_per_click_organic': $operand_1 = 'CASE WHEN (visitors.tracking_code LIKE "%' . escape(escape_like(PAY_PER_CLICK_FLAG)) . '%") THEN "pay_per_click" WHEN (visitors.referring_search_engine != "") THEN "organic" ELSE "neither" END'; break;
            case 'first_visit': $operand_1 = 'visitors.first_visit'; break;
            case 'landing_page_name': $operand_1 = 'visitors.landing_page_name'; break;
            case 'tracking_code': $operand_1 = 'visitors.tracking_code'; break;
            case 'affiliate_code': $operand_1 = 'visitors.affiliate_code'; break;

            case 'utm_source': $operand_1 = 'visitors.utm_source'; break;
            case 'utm_medium': $operand_1 = 'visitors.utm_medium'; break;
            case 'utm_campaign': $operand_1 = 'visitors.utm_campaign'; break;
            case 'utm_term': $operand_1 = 'visitors.utm_term'; break;
            case 'utm_content': $operand_1 = 'visitors.utm_content'; break;
            
            case 'page_views': $operand_1 = 'visitors.page_views'; break;
            case 'custom_form_submitted': $operand_1 = 'visitors.custom_form_submitted'; break;
            case 'custom_form_name': $operand_1 = 'visitors.custom_form_name'; break;
            case 'order_created': $operand_1 = 'visitors.order_created'; break;
            case 'order_retrieved': $operand_1 = 'visitors.order_retrieved'; break;
            case 'order_checked_out': $operand_1 = 'visitors.order_checked_out'; break;
            case 'order_completed': $operand_1 = 'visitors.order_completed'; break;
            case 'city': $operand_1 = 'visitors.city'; break;
            case 'state': $operand_1 = 'visitors.state'; break;
            case 'zip_code': $operand_1 = 'visitors.zip_code'; break;
            case 'country': $operand_1 = 'visitors.country'; break;
            case 'ip_address': $operand_1 = 'INET_NTOA(visitors.ip_address)'; break;
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
    
    // if there are no date filters, then add filter for date changer
    if ($date_filter_exists == false) {
        // if where is blank, then add the start of the where clause
        if ($where == '') {
            $where .= "WHERE ";
            
        // else where is not blank, so add and
        } else {
            $where .= "AND ";
        }
        
        // add start and stop timestamp to where clause
        $where .= "(visitors.start_timestamp >= $start_timestamp) AND (visitors.start_timestamp <= $stop_timestamp)";
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
    
    // get all visitors
    $query =
        "SELECT
            $sql_summarize_by_1
            $sql_summarize_by_2
            $sql_summarize_by_3
            visitors.id,
            visitors.start_timestamp,
            visitors.page_views,
            visitors.custom_form_submitted,
            visitors.order_created,
            visitors.order_retrieved,
            visitors.order_checked_out,
            visitors.order_completed,
            visitors.order_total
        FROM visitors
        $where
        ORDER BY
            $sql_summarize_by_1
            $sql_summarize_by_2
            $sql_summarize_by_3
            visitors.id";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $results = array();
    
    switch ($number_of_summarize_bys) {
        case 0:
            $visitors = array();
            
            while ($row = mysqli_fetch_array($result)) {
                // store data for visitor in array
                $visitors[] = array(
                    'id' => $row['id'],
                    'timestamp' => $row['start_timestamp'],
                    'page_views' => $row['page_views'],
                    'custom_form_submitted' => $row['custom_form_submitted'],                
                    'order_created' => $row['order_created'],
                    'order_retrieved' => $row['order_retrieved'],
                    'order_checked_out' => $row['order_checked_out'],
                    'order_completed' => $row['order_completed'],
                    'order_total' => $row['order_total']);
                
                // update grand totals
                $grand_count++;
                $grand_page_views += $row['page_views'];
                $grand_custom_form_submitted += $row['custom_form_submitted'];
                $grand_order_created += $row['order_created'];
                $grand_order_retrieved += $row['order_retrieved'];
                $grand_order_checked_out += $row['order_checked_out'];
                $grand_order_completed += $row['order_completed'];
                $grand_order_total += $row['order_total'];
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
                
                // store data for visitor in array
                $results[$summarize_by_1_key]['visitors'][] = array(
                    'id' => $row['id'],
                    'timestamp' => $row['start_timestamp'],
                    'page_views' => $row['page_views'],
                    'custom_form_submitted' => $row['custom_form_submitted'],                
                    'order_created' => $row['order_created'],
                    'order_retrieved' => $row['order_retrieved'],
                    'order_checked_out' => $row['order_checked_out'],
                    'order_completed' => $row['order_completed'],
                    'order_total' => $row['order_total']);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['order_total'] += $row['order_total'];
                
                // update grand totals
                $grand_count++;
                $grand_page_views += $row['page_views'];
                $grand_custom_form_submitted += $row['custom_form_submitted'];
                $grand_order_created += $row['order_created'];
                $grand_order_retrieved += $row['order_retrieved'];
                $grand_order_checked_out += $row['order_checked_out'];
                $grand_order_completed += $row['order_completed'];
                $grand_order_total += $row['order_total'];
                
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
                
                // store data for visitor in array
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['visitors'][] = array(
                    'id' => $row['id'],
                    'timestamp' => $row['start_timestamp'],
                    'page_views' => $row['page_views'],
                    'custom_form_submitted' => $row['custom_form_submitted'],                
                    'order_created' => $row['order_created'],
                    'order_retrieved' => $row['order_retrieved'],
                    'order_checked_out' => $row['order_checked_out'],
                    'order_completed' => $row['order_completed'],
                    'order_total' => $row['order_total']);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['order_total'] += $row['order_total'];
                
                // update totals for summarize by 2
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_total'] += $row['order_total'];
                
                // update grand totals
                $grand_count++;
                $grand_page_views += $row['page_views'];
                $grand_custom_form_submitted += $row['custom_form_submitted'];
                $grand_order_created += $row['order_created'];
                $grand_order_retrieved += $row['order_retrieved'];
                $grand_order_checked_out += $row['order_checked_out'];
                $grand_order_completed += $row['order_completed'];
                $grand_order_total += $row['order_total'];
                
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
                
                // store data for visitor in array
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['visitors'][] = array(
                    'id' => $row['id'],
                    'timestamp' => $row['start_timestamp'],
                    'page_views' => $row['page_views'],
                    'custom_form_submitted' => $row['custom_form_submitted'],                
                    'order_created' => $row['order_created'],
                    'order_retrieved' => $row['order_retrieved'],
                    'order_checked_out' => $row['order_checked_out'],
                    'order_completed' => $row['order_completed'],
                    'order_total' => $row['order_total']);
                
                // update totals for summarize by 1
                $results[$summarize_by_1_key]['count']++;
                $results[$summarize_by_1_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['order_total'] += $row['order_total'];
                
                // update totals for summarize by 2
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['order_total'] += $row['order_total'];
                
                // update totals for summarize by 3
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['count']++;
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['page_views'] += $row['page_views'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['custom_form_submitted'] += $row['custom_form_submitted'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['order_created'] += $row['order_created'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['order_retrieved'] += $row['order_retrieved'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['order_checked_out'] += $row['order_checked_out'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['order_completed'] += $row['order_completed'];
                $results[$summarize_by_1_key]['results'][$summarize_by_2_key]['results'][$summarize_by_3_key]['order_total'] += $row['order_total'];
                
                // update grand totals
                $grand_count++;
                $grand_page_views += $row['page_views'];
                $grand_custom_form_submitted += $row['custom_form_submitted'];
                $grand_order_created += $row['order_created'];
                $grand_order_retrieved += $row['order_retrieved'];
                $grand_order_checked_out += $row['order_checked_out'];
                $grand_order_completed += $row['order_completed'];
                $grand_order_total += $row['order_total'];
                
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
            '<th>Visitor Number</th>
            <th>Date</th>';
        
        $output_report_detail_cells =
            '<td>&nbsp;</td>
            <td>&nbsp;</td>';
    }
    
    // Check to see if the order report name is empty, and add new order report header if it is.
    if ($output_visitor_report_name == '') {
        $output_visitor_report_name = '[new visitor report]';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($output_visitor_report_name) . '</h1>
        </div>
        ' . $output_edit_button . '
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Visitor Report</h1>
            <div class="subheading" style="margin-bottom: 1em">View or update this real-time visitor report.</div>
            <form action="view_visitor_report.php" id="edit_form" method="post" style="margin-bottom: 2em' . $output_edit_form_style . '">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'id'=>'last_filter_number', 'name'=>'last_filter_number', 'value'=>'0')) . '
                ' . $output_hidden_id_field . '
                <h2 style="margin-bottom: .5em">Visitor Report Name</h2>
                <table class="field">
                    <tr>
                        <td>Visitor Report Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                </table>
                <h2 style="margin-bottom: .5em">Visitor Report Layout</h2>
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
                <script type="text/javascript">
                    var last_filter_number = 0;
                    
                    var filters = new Array();
                    
                    ' . $output_filters_for_javascript . '
                    
                    var field_options = new Array();
                    
                    ' . $output_field_options_for_javascript . '
                    
                    window.onload = initialize_filters;
                </script>
                <h2 style="margin-bottom: .85em">Visitor Report Filters</h2>
                <div style="margin: 2em 0em"><a href="javascript:void(0)" onclick="create_filter()" class="button">Add Filter</a></div>
                <table id="filter_table" class="chart_no_hover">
                    <tr>
                        <th style="text-align: left; padding-right: 3em">Field</th>
                        <th style="text-align: left; padding-right: 3em">Operation</th>
                        <th style="text-align: left; padding-right: 3em">Value</th>
                        <th style="text-align: left; padding-right: 3em">Dynamic Value</th>
                        <th style="text-align: left">&nbsp;</th>
                    </tr>
                </table>
                <h2 style="margin-bottom: .5em">Include Visitor Session Information</h2>
                <table class="field">
                    <tr>
                        <td><label for="detail">Show Detail:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'detail', 'id'=>'detail', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_button" value="Save &amp; Run" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="' . $output_cancel_button_onclick . '" class="submit-secondary" />&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
            </form>
            ' . $output_date_changer . '
            <table class="chart">
                <tr>
                    <th colspan="' . $number_of_summarize_bys . '">&nbsp;</th>
                    ' . $output_report_detail_headers . '
                    <th style="text-align: right">Visitors</th>
                    <th style="text-align: right">Visitors %</th>
                    <th style="text-align: right">Page Views</th>
                    <th style="text-align: right">Page Views %</th>
                    <th style="text-align: right">Custom Form Submitted %</th>
                    <th style="text-align: right">Order Created %</th>
                    <th style="text-align: right">Order Retrieved %</th>
                    <th style="text-align: right">Order Checked Out %</th>
                    <th style="text-align: right">Order Completed %</th>
                    <th style="text-align: right">Order Total</th>
                    <th style="text-align: right">Order Total %</th>
                    <th style="text-align: right">Average Order Total</th>
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
            
            // get percentages
            $count_percentage = ($grand_count > 0) ? $summarize_by_1_result['count'] / $grand_count * 100 : 0;
            $page_views_percentage = ($grand_page_views > 0) ? $summarize_by_1_result['page_views'] / $grand_page_views * 100 : 0;
            $custom_form_submitted_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['custom_form_submitted'] / $summarize_by_1_result['count'] * 100 : 0;
            $order_created_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['order_created'] / $summarize_by_1_result['count'] * 100 : 0;
            $order_retrieved_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['order_retrieved'] / $summarize_by_1_result['count'] * 100 : 0;
            $order_checked_out_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['order_checked_out'] / $summarize_by_1_result['count'] * 100 : 0;
            $order_completed_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['order_completed'] / $summarize_by_1_result['count'] * 100 : 0;
            $order_total_percentage = ($grand_order_total > 0) ? $summarize_by_1_result['order_total'] / $grand_order_total * 100 : 0;
            $average_order_total = ($summarize_by_1_result['count'] > 0) ? $summarize_by_1_result['order_total'] / $summarize_by_1_result['count'] : 0;
            
            print
                '<tr style="font-weight: bold; color: #008000; cursor: default">
                    <td colspan="' . $number_of_summarize_bys . '">' . nl2br(h($summarize_by_1_name)) . '</td>
                    ' . $output_report_detail_cells . '
                    <td style="text-align: right">' . number_format($summarize_by_1_result['count']) . '</td>
                    <td style="text-align: right">' . number_format($count_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($summarize_by_1_result['page_views']) . '</td>
                    <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($custom_form_submitted_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($order_created_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($order_retrieved_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($order_checked_out_percentage, 2) . '%</td>
                    <td style="text-align: right">' . number_format($order_completed_percentage, 2) . '%</td>
                    <td style="text-align: right">' . prepare_amount($summarize_by_1_result['order_total'] / 100) . '</td>
                    <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                    <td style="text-align: right">' . prepare_amount($average_order_total / 100) . '</td>
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
                    
                    // get percentages
                    $count_percentage = ($summarize_by_1_result['count'] > 0) ? $summarize_by_2_result['count'] / $summarize_by_1_result['count'] * 100 : 0;
                    $page_views_percentage = ($summarize_by_1_result['page_views'] > 0) ? $summarize_by_2_result['page_views'] / $summarize_by_1_result['page_views'] * 100 : 0;
                    $custom_form_submitted_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['custom_form_submitted'] / $summarize_by_2_result['count'] * 100 : 0;
                    $order_created_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['order_created'] / $summarize_by_2_result['count'] * 100 : 0;
                    $order_retrieved_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['order_retrieved'] / $summarize_by_2_result['count'] * 100 : 0;
                    $order_checked_out_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['order_checked_out'] / $summarize_by_2_result['count'] * 100 : 0;
                    $order_completed_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['order_completed'] / $summarize_by_2_result['count'] * 100 : 0;
                    $order_total_percentage = ($summarize_by_1_result['order_total'] > 0) ? $summarize_by_2_result['order_total'] / $summarize_by_1_result['order_total'] * 100 : 0;
                    $average_order_total = ($summarize_by_2_result['count'] > 0) ? $summarize_by_2_result['order_total'] / $summarize_by_2_result['count'] : 0;
                    
                    print
                        '<tr style="font-weight: bold; color: #719700; cursor: default">
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td colspan="' . $colspan . '">' . nl2br(h($summarize_by_2_name)) . '</td>
                            ' . $output_report_detail_cells . '
                            <td style="text-align: right">' . number_format($summarize_by_2_result['count']) . '</td>
                            <td style="text-align: right">' . number_format($count_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($summarize_by_2_result['page_views']) . '</td>
                            <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($custom_form_submitted_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($order_created_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($order_retrieved_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($order_checked_out_percentage, 2) . '%</td>
                            <td style="text-align: right">' . number_format($order_completed_percentage, 2) . '%</td>
                            <td style="text-align: right">' . prepare_amount($summarize_by_2_result['order_total'] / 100) . '</td>
                            <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                            <td style="text-align: right">' . prepare_amount($average_order_total / 100) . '</td>
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

                            // get percentages
                            $count_percentage = ($summarize_by_2_result['count'] > 0) ? $summarize_by_3_result['count'] / $summarize_by_2_result['count'] * 100 : 0;
                            $page_views_percentage = ($summarize_by_2_result['page_views'] > 0) ? $summarize_by_3_result['page_views'] / $summarize_by_2_result['page_views'] * 100 : 0;
                            $custom_form_submitted_percentage = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['custom_form_submitted'] / $summarize_by_3_result['count'] * 100 : 0;
                            $order_created_percentage = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['order_created'] / $summarize_by_3_result['count'] * 100 : 0;
                            $order_retrieved_percentage = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['order_retrieved'] / $summarize_by_3_result['count'] * 100 : 0;
                            $order_checked_out_percentage = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['order_checked_out'] / $summarize_by_3_result['count'] * 100 : 0;
                            $order_completed_percentage = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['order_completed'] / $summarize_by_3_result['count'] * 100 : 0;
                            $order_total_percentage = ($summarize_by_2_result['order_total'] > 0) ? $summarize_by_3_result['order_total'] / $summarize_by_2_result['order_total'] * 100 : 0;
                            $average_order_total = ($summarize_by_3_result['count'] > 0) ? $summarize_by_3_result['order_total'] / $summarize_by_3_result['count'] : 0;
                            
                            print
                                '<tr style="font-weight: bold; color: #808080; cursor: default">
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td>' . nl2br(h($summarize_by_3_name)) . '</td>
                                    ' . $output_report_detail_cells . '
                                    <td style="text-align: right">' . number_format($summarize_by_3_result['count']) . '</td>
                                    <td style="text-align: right">' . number_format($count_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($summarize_by_3_result['page_views']) . '</td>
                                    <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($custom_form_submitted_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($order_created_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($order_retrieved_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($order_checked_out_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . number_format($order_completed_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . prepare_amount($summarize_by_3_result['order_total'] / 100) . '</td>
                                    <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                                    <td style="text-align: right">' . prepare_amount($average_order_total / 100) . '</td>
                                </tr>';
                            
                            if ($liveform->get_field_value('detail') == 1) {
                                foreach ($summarize_by_3_result['visitors'] as $visitor) {
                                    // get percentages
                                    $count_percentage = ($summarize_by_3_result['count'] > 0) ? $visitor['count'] / $summarize_by_3_result['count'] * 100 : 0;
                                    $page_views_percentage = ($summarize_by_3_result['page_views'] > 0) ? $visitor['page_views'] / $summarize_by_3_result['page_views'] * 100 : 0;
                                    $order_total_percentage = ($summarize_by_3_result['order_total'] > 0) ? $visitor['order_total'] / $summarize_by_3_result['order_total'] * 100 : 0;
                                    
                                    $output_custom_form_submitted = ($visitor['custom_form_submitted']) ? '*' : '';
                                    $output_order_created = ($visitor['order_created']) ? '*' : '';
                                    $output_order_retrieved = ($visitor['order_retrieved']) ? '*' : '';
                                    $output_order_checked_out = ($visitor['order_checked_out']) ? '*' : '';
                                    $output_order_completed = ($visitor['order_completed']) ? '*' : '';
                                    
                                    $output_link_url = 'view_visitor.php?id=' . $visitor['id'] . h(escape_javascript($query_string_visitor_report_id));
                                    
                                    print
                                        '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                            <td colspan="3">&nbsp;</td>
                                            <td>' . $visitor['id'] . '</td>
                                            <td style="white-space: nowrap">' . get_relative_time(array('timestamp' => $visitor['timestamp'])) . '</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right">' . number_format($visitor['page_views']) . '</td>
                                            <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                                            <td style="text-align: right">' . $output_custom_form_submitted . '</td>
                                            <td style="text-align: right">' . $output_order_created . '</td>
                                            <td style="text-align: right">' . $output_order_retrieved . '</td>
                                            <td style="text-align: right">' . $output_order_checked_out . '</td>
                                            <td style="text-align: right">' . $output_order_completed . '</td>
                                            <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                                            <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                                            <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                                        </tr>';
                                }
                            }
                        }
                        
                    // else there are not 3 summarize bys
                    } else {
                        if ($liveform->get_field_value('detail') == 1) {
                            foreach ($summarize_by_2_result['visitors'] as $visitor) {
                                // get percentages
                                $count_percentage = ($summarize_by_2_result['count'] > 0) ? $visitor['count'] / $summarize_by_2_result['count'] * 100 : 0;
                                $page_views_percentage = ($summarize_by_2_result['page_views'] > 0) ? $visitor['page_views'] / $summarize_by_2_result['page_views'] * 100 : 0;
                                $order_total_percentage = ($summarize_by_2_result['order_total'] > 0) ? $visitor['order_total'] / $summarize_by_2_result['order_total'] * 100 : 0;
                                
                                $output_custom_form_submitted = ($visitor['custom_form_submitted']) ? '*' : '';
                                $output_order_created = ($visitor['order_created']) ? '*' : '';
                                $output_order_retrieved = ($visitor['order_retrieved']) ? '*' : '';
                                $output_order_checked_out = ($visitor['order_checked_out']) ? '*' : '';
                                $output_order_completed = ($visitor['order_completed']) ? '*' : '';
                                
                                $output_link_url = 'view_visitor.php?id=' . $visitor['id'] . h(escape_javascript($query_string_visitor_report_id));
                                
                                print
                                    '<tr style="color: #969696;" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                        <td colspan="2">&nbsp;</td>
                                        <td>' . $visitor['id'] . '</td>
                                        <td style="white-space: nowrap">' . get_relative_time(array('timestamp' => $visitor['timestamp'])) . '</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right">' . number_format($visitor['page_views']) . '</td>
                                        <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                                        <td style="text-align: right">' . $output_custom_form_submitted . '</td>
                                        <td style="text-align: right">' . $output_order_created . '</td>
                                        <td style="text-align: right">' . $output_order_retrieved . '</td>
                                        <td style="text-align: right">' . $output_order_checked_out . '</td>
                                        <td style="text-align: right">' . $output_order_completed . '</td>
                                        <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                                        <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                                        <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                                    </tr>';
                            }
                        }
                    }
                }
                
            // else there is not at least 2 summarize bys
            } else {
                if ($liveform->get_field_value('detail') == 1) {
                    foreach ($summarize_by_1_result['visitors'] as $visitor) {
                        // get percentages
                        $count_percentage = ($summarize_by_1_result['count'] > 0) ? $visitor['count'] / $summarize_by_1_result['count'] * 100 : 0;
                        $page_views_percentage = ($summarize_by_1_result['page_views'] > 0) ? $visitor['page_views'] / $summarize_by_1_result['page_views'] * 100 : 0;
                        $order_total_percentage = ($summarize_by_1_result['order_total'] > 0) ? $visitor['order_total'] / $summarize_by_1_result['order_total'] * 100 : 0;
                        
                        $output_custom_form_submitted = ($visitor['custom_form_submitted']) ? '*' : '';
                        $output_order_created = ($visitor['order_created']) ? '*' : '';
                        $output_order_retrieved = ($visitor['order_retrieved']) ? '*' : '';
                        $output_order_checked_out = ($visitor['order_checked_out']) ? '*' : '';
                        $output_order_completed = ($visitor['order_completed']) ? '*' : '';
                        
                        $output_link_url = 'view_visitor.php?id=' . $visitor['id'] . h(escape_javascript($query_string_visitor_report_id));
                        
                        print
                            '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                                <td>&nbsp;</td>
                                <td>' . $visitor['id'] . '</td>
                                <td style="white-space: nowrap">' . get_relative_time(array('timestamp' => $visitor['timestamp'])) . '</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right">' . number_format($visitor['page_views']) . '</td>
                                <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                                <td style="text-align: right">' . $output_custom_form_submitted . '</td>
                                <td style="text-align: right">' . $output_order_created . '</td>
                                <td style="text-align: right">' . $output_order_retrieved . '</td>
                                <td style="text-align: right">' . $output_order_checked_out . '</td>
                                <td style="text-align: right">' . $output_order_completed . '</td>
                                <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                                <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                                <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                            </tr>';
                    }
                }
            }
        }
        
    // else there is not at least 1 summarize by
    } else {
        if ($liveform->get_field_value('detail') == 1) {
            foreach ($visitors as $visitor) {
                // get percentages
                $count_percentage = ($grand_count > 0) ? $visitor['count'] / $grand_count * 100 : 0;
                $page_views_percentage = ($grand_page_views > 0) ? $visitor['page_views'] / $grand_page_views * 100 : 0;
                $order_total_percentage = ($grand_order_total > 0) ? $visitor['order_total'] / $grand_order_total * 100 : 0;
                
                $output_custom_form_submitted = ($visitor['custom_form_submitted']) ? '*' : '';
                $output_order_created = ($visitor['order_created']) ? '*' : '';
                $output_order_retrieved = ($visitor['order_retrieved']) ? '*' : '';
                $output_order_checked_out = ($visitor['order_checked_out']) ? '*' : '';
                $output_order_completed = ($visitor['order_completed']) ? '*' : '';
                
                $output_link_url = 'view_visitor.php?id=' . $visitor['id'] . h(escape_javascript($query_string_visitor_report_id));
                
                print
                    '<tr style="color: #969696" class="pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                        <td>&nbsp;</td>
                        <td>' . $visitor['id'] . '</td>
                        <td style="white-space: nowrap">' . get_relative_time(array('timestamp' => $visitor['timestamp'])) . '</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="text-align: right">' . number_format($visitor['page_views']) . '</td>
                        <td style="text-align: right">' . number_format($page_views_percentage, 2) . '%</td>
                        <td style="text-align: right">' . $output_custom_form_submitted . '</td>
                        <td style="text-align: right">' . $output_order_created . '</td>
                        <td style="text-align: right">' . $output_order_retrieved . '</td>
                        <td style="text-align: right">' . $output_order_checked_out . '</td>
                        <td style="text-align: right">' . $output_order_completed . '</td>
                        <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                        <td style="text-align: right">' . number_format($order_total_percentage, 2) . '%</td>
                        <td style="text-align: right">' . prepare_amount($visitor['order_total'] / 100) . '</td>
                    </tr>';
            }
        }
    }
    
    $grand_custom_form_submitted_percentage = ($grand_count > 0) ? $grand_custom_form_submitted / $grand_count * 100 : 0;
    $grand_order_created_percentage = ($grand_count > 0) ? $grand_order_created / $grand_count * 100 : 0;
    $grand_order_retrieved_percentage = ($grand_count > 0) ? $grand_order_retrieved / $grand_count * 100 : 0;
    $grand_order_checked_out_percentage = ($grand_count > 0) ? $grand_order_checked_out / $grand_count * 100 : 0;
    $grand_order_completed_percentage = ($grand_count > 0) ? $grand_order_completed / $grand_count * 100 : 0;
    $grand_average_order_total = ($grand_count > 0) ? $grand_order_total / $grand_count : 0;
    
    print
            '        <tr style="font-weight: bold; color: #c49700;  cursor: default">
                        <td colspan="' . $number_of_summarize_bys . '">Grand Total</td>
                        ' . $output_report_detail_cells . '
                        <td style="text-align: right">' . number_format($grand_count) . '</td>
                        <td style="text-align: right">100.00%</td>
                        <td style="text-align: right">' . number_format($grand_page_views) . '</td>
                        <td style="text-align: right">100.00%</td>
                        <td style="text-align: right">' . number_format($grand_custom_form_submitted_percentage, 2) . '%</td>
                        <td style="text-align: right">' . number_format($grand_order_created_percentage, 2) . '%</td>
                        <td style="text-align: right">' . number_format($grand_order_retrieved_percentage, 2) . '%</td>
                        <td style="text-align: right">' . number_format($grand_order_checked_out_percentage, 2) . '%</td>
                        <td style="text-align: right">' . number_format($grand_order_completed_percentage, 2) . '%</td>
                        <td style="text-align: right">' . prepare_amount($grand_order_total / 100) . '</td>
                        <td style="text-align: right">100.00%</td>
                        <td style="text-align: right">' . prepare_amount($grand_average_order_total / 100) . '</td>
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
    
    // if the user selected to delete this visitor report, then delete it
    if ($liveform->get_field_value('submit_button') == 'Delete') {
        // get visitor report name for log
        $query = "SELECT name FROM visitor_reports WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $visitor_report_name = $row['name'];
        
        // delete visitor report
        $query = "DELETE FROM visitor_reports WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // delete visitor report filters
        $query = "DELETE FROM visitor_report_filters WHERE visitor_report_id = '" . escape($liveform->get_field_value('id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity('visitor report (' . $visitor_report_name . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_visitor_reports = new liveform('view_visitor_reports');
        $liveform_view_visitor_reports->add_notice('The visitor report has been deleted.');
        
        // send user to view visitor reports screen
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_visitor_reports.php');
        
        $liveform->remove_form();
        
        exit();
        
    // else the user did not choose to delete the visitor report
    } else {
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to previous screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_visitor_report.php' . $query_string_id);
            exit();
        }
        
        // check to see if name is already in use
        $query =
            "SELECT id
            FROM visitor_reports
            WHERE
                (name = '" . escape($liveform->get_field_value('name')) . "')
                AND (id != '" . escape($liveform->get_field_value('id')) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use, prepare error and forward user back to previous screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to previous screen
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_visitor_report.php' . $query_string_id);
            exit();
        }
        
        // if the user is creating a new visitor report, then create visitor report record
        if ($liveform->field_in_session('id') == false) {
            $query =
                "INSERT INTO visitor_reports (
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
            
        // else the user is updating an existing visitor report, so update visitor report record
        } else {
            $query =
                "UPDATE visitor_reports
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
            
            // delete visitor report filters
            $query = "DELETE FROM visitor_report_filters WHERE visitor_report_id = '" . escape($liveform->get_field_value('id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
        
        $date_filter_exists = false;
        
        // loop through all filters in order to insert filters into database
        for ($i = 1; $i <= $liveform->get_field_value('last_filter_number'); $i++) {
            // if filter exists and an operator was selected for this filter, then insert filter
            if ($liveform->get_field_value('filter_' . $i . '_operator') != '') {
                // get field type
                $field_type = '';
                
                // if the field for this filter is date, then set type to date
                if ($liveform->get_field_value('filter_' . $i . '_field') == 'date') {
                    $field_type = 'date';
                    $date_filter_exists = true;
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
                    "INSERT INTO visitor_report_filters (
                        visitor_report_id,
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
        
        $date_filter_message = '';
        
        // if there is a date filter, then prepare message
        if ($date_filter_exists == true) {
            $date_filter_message = ' Date browsing has been disabled because there is a date filter in this report.';
        }
        
        // if the user is creating a new visitor report, then log activity, remove form, and add notice in a certain way
        if ($liveform->field_in_session('id') == false) {
            log_activity('visitor report (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
            $liveform->remove_form();
            $liveform->add_notice('The visitor report has been created, and the results appear below.' . $date_filter_message);
            
        // else the user is updating an existing visitor report, so log activity, remove form, and add notice in a certain way
        } else {
            log_activity('visitor report (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
            $liveform->remove_form();
            $liveform->add_notice('The visitor report has been saved, and the results appear below.' . $date_filter_message);
        }
        
        // send user back to visitor report
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_visitor_report.php' . $query_string_id);
        exit();
    }
}

function get_summarize_by_column($summarize_by)
{
    switch ($summarize_by) {
        case 'year': return 'FROM_UNIXTIME(visitors.start_timestamp, \'%Y\')';
        case 'month': return 'FROM_UNIXTIME(visitors.start_timestamp, \'%m\')';
        case 'day': return 'FROM_UNIXTIME(visitors.start_timestamp, \'%d\')';
        case 'site_search_terms': return 'visitors.site_search_terms';
        case 'currency_code': return 'visitors.currency_code';
        case 'http_referer': return 'visitors.http_referer';
        case 'referring_host_name': return 'visitors.referring_host_name';
        case 'referring_search_engine': return 'visitors.referring_search_engine';
        case 'referring_search_terms': return 'visitors.referring_search_terms';
        case 'pay_per_click_organic': return 'CASE WHEN (visitors.tracking_code LIKE "%' . escape(escape_like(PAY_PER_CLICK_FLAG)) . '%") THEN "Pay Per Click" WHEN (visitors.referring_search_engine != "") THEN "Organic" ELSE "" END';
        case 'first_visit': return 'visitors.first_visit';
        case 'landing_page_name': return 'visitors.landing_page_name';
        case 'tracking_code': return 'visitors.tracking_code';
        case 'affiliate_code': return 'visitors.affiliate_code';

        case 'utm_source': return 'visitors.utm_source';
        case 'utm_medium': return 'visitors.utm_medium';
        case 'utm_campaign': return 'visitors.utm_campaign';
        case 'utm_term': return 'visitors.utm_term';
        case 'utm_content': return 'visitors.utm_content';

        case 'page_views': return 'visitors.page_views';
        case 'custom_form_submitted': return 'visitors.custom_form_submitted';
        case 'custom_form_name': return 'visitors.custom_form_name';
        case 'order_created': return 'visitors.order_created';
        case 'order_retrieved': return 'visitors.order_retrieved';
        case 'order_checked_out': return 'visitors.order_checked_out';
        case 'order_completed': return 'visitors.order_completed';
        case 'city': return 'visitors.city';
        case 'state': return 'visitors.state';
        case 'zip_code': return 'visitors.zip_code';
        case 'country': return 'visitors.country';
    }
}

function update_summarize_by_name($summarize_by_name, $summarize_by) {
    switch ($summarize_by) {
        case 'month':
            $summarize_by_name = get_month_name_from_number($summarize_by_name);
            break;
            
        case 'site_search_terms':
            $summarize_by_name = str_replace('|', ",\n", $summarize_by_name);
            break;
            
        case 'first_visit':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'First Visit';
            } else {
                $summarize_by_name = 'Return Visit';
            }
            break;
            
        case 'custom_form_submitted':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Custom Form Submitted';
            } else {
                $summarize_by_name = 'Custom Form Not Submitted';
            }
            break;
            
        case 'order_created':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Order Created';
            } else {
                $summarize_by_name = 'Order Not Created';
            }
            break;
        
        case 'order_retrieved':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Order Retrieved';
            } else {
                $summarize_by_name = 'Order Not Retrieved';
            }
            break;
        
        case 'order_checked_out':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Order Checked Out';
            } else {
                $summarize_by_name = 'Order Not Checked Out';
            }
            break;
        
        case 'order_completed':
            if ($summarize_by_name == 1) {
                $summarize_by_name = 'Order Completed';
            } else {
                $summarize_by_name = 'Order Not Completed';
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
        case 'number of visitors': return 'count';
        case 'number of page views': return 'page_views';
        case 'order total': return 'order_total';
    }
}