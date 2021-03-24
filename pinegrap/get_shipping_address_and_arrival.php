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

function get_shipping_address_and_arrival($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];
    $folder_id_for_default_value = $properties['folder_id_for_default_value'];

    $properties = get_page_type_properties($page_id, 'shipping address and arrival');

    $address_type = $properties['address_type'];
    $address_type_page_id = $properties['address_type_page_id'];
    $form_enabled = $properties['form'];
    $form_name = $properties['form_name'];
    $form_label_column_width = $properties['form_label_column_width'];
    $submit_button_label = $properties['submit_button_label'];
    $next_page_id = $properties['next_page_id'];

    $layout_type = get_layout_type($page_id);

    // store page id for shipping address and arrival page in case we need to come back to this page later in the order process
    $_SESSION['ecommerce']['shipping_address_and_arrival_page_id'] = $page_id;

    // if the user came from the control panel, clear ship_to_id, to prevent edit user from accessing ship to that user should not have access to
    if ((isset($_GET['from']) == true) && ($_GET['from'] == 'control_panel')) {
        unset($_GET['ship_to_id']);

    // else the user did not come from the control panel, so perform validation to make sure that this user has access to the ship to
    } else {

        // if no ship to is found, return error message
        if (!isset($_GET['ship_to_id']) || empty($_GET['ship_to_id'])) {
            $form = new liveform('shipping_address_and_arrival');
            $form->mark_error('', 'We\'re sorry, shipping information can only be gathered during the checkout process. <a href="javascript:history.go(-1)">Go back</a>');
            $content =
                '<div class="software_shipping_address_and_arrival">
                    '  . $form->get_messages() . '
                </div>';
            $form->remove();
            return $content;
        }

        $recipient = db_item(
            "SELECT
                ship_tos.order_id,
                ship_tos.ship_to_name,
                orders.status AS order_status
            FROM ship_tos
            LEFT JOIN orders ON ship_tos.order_id = orders.id
            WHERE ship_tos.id = '" . e($_GET['ship_to_id']) . "'");

        if (!$recipient) {
           output_error('Sorry, that recipient could not be found.  This might happen if you used the back button to go back to a recipient that no longer exists in the order.');
        }

        if (
            ($recipient['order_status'] == 'complete')
            or ($recipient['order_status'] == 'exported')
        ) {
            output_error('Sorry, the order for that recipient has already been completed, so that recipient may not be updated.  This might happen if you already completed your order and then used the back button to go back to a recipient.  Since your order is complete, there is nothing further you need to do.');
        }

        if (!$_SESSION['ecommerce']['order_id']) {
            output_error('Sorry, we could not find your order, so we can\'t allow you to update that recipient.  This might happen if you were inactive for a while and your session expired.  You might try starting a new order or retrieving a past order.');
        }

        if ($recipient['order_id'] != $_SESSION['ecommerce']['order_id']) {
            output_error('Sorry, that recipient belongs to a different order, so we can\'t allow you to update that recipient.  This might happen if your session expired and you started a new order, and then you used the back button to go back to a recipient for an old order.');
        }

        // Store ship to name for later use.
        $ship_to_name = $recipient['ship_to_name'];
    }

    $form = new liveform('shipping_address_and_arrival');

    $ghost = $_SESSION['software']['ghost'];

    // if form has not been filled out yet, get recipient data in order to populate fields
    if ($form->field_in_session('first_name') == false) {

        $used_address_book = false;

        // If the user is logged in and not ghosting, get recipient info from address book.
        if (USER_LOGGED_IN and !$ghost) {

            $query =
                "SELECT * FROM address_book
                WHERE user = '" . e(USER_ID) . "' AND ship_to_name = '" . e($ship_to_name) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            // if recipient was found in the address book, get field data and take note
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $used_address_book = true;
            }
        }

        // if recipient data was not found in address book, get data from ship to
        if ($used_address_book == false) {
            $query = "SELECT * FROM ship_tos WHERE id = '" . escape($_GET['ship_to_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
        }

        // set field values
        $form->assign_field_value('salutation', $row['salutation']);
        $form->assign_field_value('first_name', $row['first_name']);
        $form->assign_field_value('last_name', $row['last_name']);
        $form->assign_field_value('company', $row['company']);
        $form->assign_field_value('address_1', $row['address_1']);
        $form->assign_field_value('address_2', $row['address_2']);
        $form->assign_field_value('city', $row['city']);
        $form->assign_field_value('state', $row['state']);
        $form->assign_field_value('zip_code', $row['zip_code']);
        $form->assign_field_value('country', $row['country']);
        
        // if address type is enabled then prefill value
        if ($address_type == 1) {
            $form->assign_field_value('address_type', $row['address_type']);
        }
        
        $form->assign_field_value('phone_number', $row['phone_number']);

        // if country is blank, set default country
        if (!$form->get_field_value('country')) {
            $query = "SELECT code FROM countries WHERE default_selected = 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            // if a default country was found, set default country
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $form->assign_field_value('country', $row['code']);
            }
        }
        
        // If a custom shipping form is enabled and the visitor did not come from the control panel,
        // then prefill those fields.
        if (
            ($form_enabled == 1)
            && ($_GET['from'] != 'control_panel')
        ) {
            $query =
                "SELECT
                    form_data.form_field_id,
                    form_data.data,
                    count(*) as number_of_values,
                    form_fields.type
                FROM form_data
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                WHERE form_data.ship_to_id = '" . escape($_GET['ship_to_id']) . "'
                GROUP BY form_data.form_field_id";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $fields = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row;
            }
            
            // loop through all field data in order to prefill fields
            foreach ($fields as $field) {
                // if there is more than one value, get all values
                if ($field['number_of_values'] > 1) {
                    $query =
                        "SELECT data
                        FROM form_data
                        WHERE
                            (ship_to_id = '" . escape($_GET['ship_to_id']) . "')
                            AND (form_field_id = '" . $field['form_field_id'] . "')
                        ORDER BY id";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    $field['data'] = array();
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $field['data'][] = $row['data'];
                    }
                }
                
                $html_name = 'field_' . $field['form_field_id'];
                
                $form->assign_field_value($html_name, prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false));
            }
        }

        // Check if the visitor has already selected an arrival date,
        // in order to select it by default.
        $selected_arrival_date = db_item(
            "SELECT
                arrival_dates.id,
                arrival_dates.custom,
                ship_tos.arrival_date
            FROM ship_tos
            LEFT JOIN arrival_dates ON arrival_dates.id = ship_tos.arrival_date_id
            WHERE ship_tos.id = '" . e($_GET['ship_to_id']) . "'");

        // If a selected arrival date was found, then select it by default.
        if ($selected_arrival_date['id']) {
            $form->assign_field_value('arrival_date', $selected_arrival_date['id']);

            // If the selected arrival date has a custom field,
            // then prefill that value also.
            if ($selected_arrival_date['custom']) {
                $form->assign_field_value('custom_arrival_date_' . $selected_arrival_date['id'], prepare_form_data_for_output($selected_arrival_date['arrival_date'], 'date'));
            }

        // Otherwise the visitor has not selected an arrival date yet,
        // so check if there is an active arrival date that should be selected by default.
        } else {
            $default_arrival_date = db_item(
                "SELECT id
                FROM arrival_dates
                WHERE
                    (status = 'enabled')
                    AND (start_date <= CURRENT_DATE())
                    AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
                    AND (default_selected = '1')
                ORDER BY sort_order, arrival_date
                LIMIT 1");

            // If a default arrival date was found, then select it.
            if ($default_arrival_date) {
                $form->assign_field_value('arrival_date', $default_arrival_date['id']);
            }
        }
    }

    // assume that the shipping same as billing option should be outputted, until we find out otherwise
    $shipping_same_as_billing = TRUE;
    
    // if recipient mode is multi-recipient then determine if shipping same as billing option should be displayed
    if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
        // get all recipients for this order
        $query = "SELECT ship_to_name FROM ship_tos WHERE order_id = '" . escape($_SESSION['ecommerce']['order_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // get number of recipients for this order
        $number_of_recipients = mysqli_num_rows($result);
        
        // assume that there is not a myself recipient, until we find out otherwise
        $myself_recipient_exists = false;
        
        // loop through recipients in order to check if there is a myself recipient
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['ship_to_name'] == 'myself') {
                $myself_recipient_exists = true;
                break;
            }
        }
        
        // if there are multiple recipients, and if there is a myself recipient, and if this recipient is not myself,
        // then do not display the option
        if (($number_of_recipients > 0) && ($myself_recipient_exists == true) && ($ship_to_name != 'myself')) {
            $shipping_same_as_billing = false;
        }
    }

    // If the shipping same as billing is set to be displayed,
    // and there is already billing info, then remember that the
    // option should not be shown.
    if (
        $shipping_same_as_billing
        && db_value(
            "SELECT id
            FROM orders
            WHERE
                (id = '" . escape($_SESSION['ecommerce']['order_id']) . "')
                AND
                (
                    (billing_salutation != '')
                    OR (billing_first_name != '')
                    OR (billing_last_name != '')
                    OR (billing_company != '')
                    OR (billing_address_1 != '')
                    OR (billing_address_2 != '')
                    OR (billing_city != '')
                    OR (billing_state != '')
                    OR (billing_zip_code != '')
                    OR (billing_country != '')
                    OR (billing_phone_number != '')
                )")
    ) {
        $shipping_same_as_billing = false;
    }

    $system = '';

    // set blank to be the first option of the country selection drop-down
    $country_options[''] = '';

    // get countries for country selection
    $countries = db_items("SELECT name, code, zip_code_required AS zip FROM countries ORDER BY name", 'code');

    foreach ($countries as $country) {
        $country_options[$country['name']] = $country['code'];
    }

    $states = db_items(
        "SELECT
            states.name,
            states.code,
            countries.code AS country_code
        FROM states
        LEFT JOIN countries ON countries.id = states.country_id
        ORDER BY states.name");

    // Loop through the states in order to add them to countries.
    foreach ($states as $state) {
        $countries[$state['country_code']]['states'][] = array(
            'name' => $state['name'],
            'code' => $state['code']);
    }

    $system .=
        '<script>
            software.countries = ' . encode_json($countries) . ';
            software.init_country({
                country_id: "country",
                state_text_box_id: "state_text_box",
                state_pick_list_id: "state_pick_list",
                zip_code_id: "zip_code"});
        </script>';

    if ($layout_type == 'system') {

        // if recipient mode is multi-recipient, then prepare "for [ship to name]"
        if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient') {
            $for_ship_to_name = ' for <span class="software_highlight">' . h($ship_to_name) . '</span>';
        } else {
            $for_ship_to_name = '';
        }
        
        // if address type field is enabled, then output it
        if ($address_type == 1) {
            $output_address_type_what_is_this_link = '';
            
            // if there is an address type instruction page set, then check if page still exists
            if ($address_type_page_id != 0) {
                // get page name in order to check if page still exists
                $address_type_page_name = get_page_name($address_type_page_id);
                
                // if there is a page name, then page still exists, so output what is this link
                if ($address_type_page_name != '') {
                    $output_address_type_what_is_this_link = '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . OUTPUT_PATH . h(encode_url_path($address_type_page_name)) . '" target="_blank">What is this?</a>';
                }
            }
            
            $output_address_type_row =
                '<tr>
                    <td>Address Type*</td>
                    <td style="padding-top: 0.5em; padding-bottom: 0.5em">' . $form->output_field(array('type'=>'radio', 'name'=>'address_type', 'id'=>'address_type_residential', 'value'=>'residential', 'class'=>'software_input_radio', 'required' => 'true')) . '<label for="address_type_residential"> Residential</label>&nbsp;&nbsp;&nbsp;&nbsp;' . $form->output_field(array('type'=>'radio', 'name'=>'address_type', 'id'=>'address_type_business', 'value'=>'business', 'class'=>'software_input_radio', 'required' => 'true')) . '<label for="address_type_business"> Business</label>' . $output_address_type_what_is_this_link . '</td>
                </tr>';
        }
        
        $output_shipping_same_as_billing_option = '';
        
        // If the option should be shown, then show it.
        if ($shipping_same_as_billing) {
            $output_shipping_same_as_billing_option = '
                <div style="margin-top: 1em">
                    ' . $form->output_field(array('type'=>'checkbox', 'name'=>'shipping_same_as_billing', 'id'=>'shipping_same_as_billing', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="shipping_same_as_billing"> My billing address is the same as this shipping address.</label>
                </div>';
        }
        
        $output_shipping_address_fields =
            '<table>
                <tr>
                    <td>Salutation</td>
                    <td>' . $form->output_field(array('type'=>'select', 'name'=>'salutation', 'options'=>get_salutation_options(), 'class'=>'software_select')) . '</td>
                </tr>
                <tr>
                    <td>First Name*</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'first_name', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                </tr>
                <tr>
                    <td>Last Name*</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'last_name', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                </tr>
                <tr>
                    <td>Company</td>
                    <td>' . $form->output_field(array('type'=>'text', 'id'=>'company', 'name'=>'company', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Address 1*</td>
                    <td>' . $form->output_field(array('type'=>'text', 'id'=>'address_1', 'name'=>'address_1', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                </tr>
                <tr>
                    <td>Address 2</td>
                    <td>' . $form->output_field(array('type'=>'text', 'id'=>'address_2', 'name'=>'address_2', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>City*</td>
                    <td>' . $form->output_field(array('type'=>'text', 'id'=>'city', 'name'=>'city', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                </tr>
                <tr>
                    <td>Country*</td>
                    <td>' . $form->output_field(array('type'=>'select', 'id'=>'country', 'name'=>'country', 'options'=>$country_options, 'class'=>'software_select', 'required' => 'true')) . '</td>
                </tr>
                <tr>
                    <td>
                        <label for="state_text_box">
                            State / Province
                        </label>

                        <label for="state_pick_list" style="display: none">
                            State / Province*
                        </label>
                    </td>
                    <td>' .
                        $form->output_field(array('type'=>'text', 'id'=> 'state_text_box', 'name'=> 'state', 'size'=>'40', 'maxlength'=>'50', 'class'=>'software_input_text')) .

                        $form->output_field(array('type'=>'select', 'id'=> 'state_pick_list', 'name'=> 'state', 'class'=>'software_select')) . '
                    </td>
                </tr>
                <tr>
                    <td>Zip / Postal Code<span id="zip_code_required" style="display: none">*</span></td>
                    <td>' . $form->output_field(array('type'=>'text', 'id'=>'zip_code', 'name'=>'zip_code', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                ' . $output_address_type_row . '
                <tr>
                    <td>Phone</td>
                    <td>' . $form->output_field(array('type'=>'tel', 'name'=>'phone_number', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
            </table>
            ' . $output_shipping_same_as_billing_option;
        
        // get verified shipping addresses
        $query =
            "SELECT
                verified_shipping_addresses.id,
                verified_shipping_addresses.company,
                verified_shipping_addresses.address_1,
                verified_shipping_addresses.address_2,
                verified_shipping_addresses.city,
                verified_shipping_addresses.state_id,
                states.code as state_code,
                verified_shipping_addresses.zip_code,
                countries.code as country_code,
                countries.name as country_name
            FROM verified_shipping_addresses
            LEFT JOIN states ON verified_shipping_addresses.state_id = states.id
            LEFT JOIN countries ON states.country_id = countries.id
            ORDER BY
                verified_shipping_addresses.company,
                verified_shipping_addresses.address_1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if there is a verified shipping address, then output shipping address fields with verified shipping address column
        if (mysqli_num_rows($result) > 0) {
            $verified_shipping_addresses = array();
            
            // loop through verified shipping addresses in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $verified_shipping_addresses[] = $row;
            }

            // get all states
            $query =
                "SELECT
                    states.id,
                    states.name,
                    states.code,
                    countries.id as country_id,
                    countries.code as country_code
                FROM states
                LEFT JOIN countries ON countries.id = states.country_id
                ORDER BY states.name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $states = array();
            
            // loop through the states in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $states[] = $row;
            }
            
            $output_verified_states_for_javascript = '';
            
            $count = 0;
            
            // loop through the states in order to prepare JavaScript
            foreach ($states as $state) {
                $output_verified_states_for_javascript .=
                    'software_verified_states[' . $count . '] = {
                        "id": ' . $state['id'] . ',
                        "name": "' . escape_javascript($state['name']) . '",
                        "country_id": ' . escape_javascript($state['country_id']) . '
                    };' . "\n";
                
                $count++;
            }
            
            $output_verified_addresses_for_javascript = '';
            
            $count = 0;
            
            // loop through the address in order to prepare JavaScript
            foreach ($verified_shipping_addresses as $verified_shipping_address) {
                $label = '';
                
                // if there is a company then start the label with the company
                if ($verified_shipping_address['company'] != '') {
                    $label .= $verified_shipping_address['company'];
                    
                // else there is not a company, so start the label with the address 1
                } else {
                    $label .= $verified_shipping_address['address_1'];
                }
                
                // add the city to the label
                $label .= ', ' . $verified_shipping_address['city'];
                
                $output_verified_addresses_for_javascript .=
                    'software_verified_addresses[' . $count . '] = {
                        "id": ' . $verified_shipping_address['id'] . ',
                        "label": "' . escape_javascript($label) . '",
                        "company": "' . escape_javascript($verified_shipping_address['company']) . '",
                        "address_1": "' . escape_javascript($verified_shipping_address['address_1']) . '",
                        "address_2": "' . escape_javascript($verified_shipping_address['address_2']) . '",
                        "city": "' . escape_javascript($verified_shipping_address['city']) . '",
                        "state_id": ' . $verified_shipping_address['state_id'] . ',
                        "state_code": "' . $verified_shipping_address['state_code'] . '",
                        "zip_code": "' . $verified_shipping_address['zip_code'] . '",
                        "country_code": "' . escape_javascript($verified_shipping_address['country_code']) . '",
                        "country_name": "' . escape_javascript($verified_shipping_address['country_name']) . '"
                    };' . "\n";
                
                $count++;
            }

            // get all countries which we will use in a couple of places below
            $query =
                "SELECT
                    id,
                    name,
                    code,
                    default_selected
                FROM countries
                ORDER BY name";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $countries = array();
            
            // loop through the countries in order to add them to array
            while ($row = mysqli_fetch_assoc($result)) {
                $countries[] = $row;
            }
            
            $output_verified_country_options = '';
            
            // loop through the countries in order to prepare pick list
            foreach ($countries as $country) {
                $selected = '';
                
                // if this is the default country, then select it by default
                if ($country['default_selected'] == 1) {
                    $selected = ' selected="selected"';
                }
                
                $output_verified_country_options .= '<option value="' . $country['id'] . '"' . $selected . '>' . h($country['name']) . '</option>';
            }
            
            $output_shipping_address =
                '<table class="shipping_address" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr class="data">
                        <td class="address mobile_margin_bottom" style="vertical-align: top; float: left">
                            ' . $output_shipping_address_fields . '
                        </td>
                        <td class="verified mobile_left" style="vertical-align: top; float: right">
                            <fieldset id="verified_shipping_addresses" class="software_fieldset" style="padding: 1em">
                                <legend class="software_legend">Shipping Address Help</legend>
                                <script type="text/javascript">
                                    var software_verified_states = new Array();
                                    ' . $output_verified_states_for_javascript . '
                                    
                                    var software_verified_addresses = new Array();
                                    ' . $output_verified_addresses_for_javascript . '
                                    
                                    software_$(document).ready(function () {
                                        software_change_verified_country();
                                    });
                                </script>
                                <div class="verified_heading">
                                    <div style="font-size: 125%; margin-bottom: .25em">Don\'t know the exact address?</div>
                                    <div style="margin-bottom: 1em">Choose from one of our verified shipping addresses.</div>
                                </div>
                                <div id="verified_country_container" style="margin-bottom: 1em">
                                    <div>Select a Country:</div>
                                    <div><select id="verified_country_id" onchange="software_change_verified_country()"><option value=""></option>' . $output_verified_country_options . '</select></div>
                                </div>
                                <div id="verified_state_container" style="display: none; margin-bottom: 1em">
                                    <div>Select a State:</div>
                                    <div><select id="verified_state_id" onchange="software_change_verified_state()"></select></div>
                                </div>
                                <div id="verified_address_container" style="display: none; margin-bottom: 1em">
                                    <div>Select a Verified Address:</div>
                                    <div><select id="verified_address_id" onchange="software_change_verified_address()"></select></div>
                                </div>
                                <div id="verified_message" style="display: none; margin-bottom: .5em"></div>
                                <div id="verified_summary" style="display: none; margin-bottom: 1em; font-weight: bold"></div>
                                <div id="verified_button" style="display: none; margin-bottom: 1em"><input type="button" value="Use This Address" class="software_input_submit_primary address_button" onclick="software_use_verified_address()" /></div>
                            </fieldset>
                        </td>
                    </tr>
                </table>';
            
        // else there is not a verified shipping address, so just output shipping address fields
        } else {
            $output_shipping_address = $output_shipping_address_fields;
        }
        
        $output_custom_shipping_form = '';

        // if a custom shipping form is enabled, then output it
        if ($form_enabled == 1) {
            $output_form_name = '';
            
            // if there is a form name, then output form name
            if ($form_name != '') {
                $output_form_name =
                    '<div class="address heading">' . h($form_name) . $for_ship_to_name . '</div>';
            }
            
            // get form info (form content, wysiwyg fields)
            $form_info = get_form_info($page_id, 0, 0, 0, $form_label_column_width, $office_use_only = false, $form, 'frontend', false, $device_type, $folder_id_for_default_value);
            
            $output_wysiwyg_javascript = '';
            
            // if there is at least one wysiwyg field, prepare wysiwyg fields
            if (count($form_info['wysiwyg_fields']) > 0) {
                $output_wysiwyg_javascript = get_wysiwyg_editor_code($form_info['wysiwyg_fields']);
            }
            
            $output_custom_shipping_form =
                $output_wysiwyg_javascript . '
                ' . $output_form_name . '
                <div class="data">
                    <table class="custom_shipping_form" style="margin-bottom: 1.5em">
                        ' . $form_info['content'] . '
                    </table>
                </div>';
            
            // if edit mode is on, then output grid around custom shipping form
            if ($editable == true) {
                $output_title = 'Custom Shipping Form';
                
                // if the form name is not blank, then add it to the title
                if ($form_name != '') {
                    $output_title .= ': ' . h($form_name);
                }
                
                $output_custom_shipping_form =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&from=pages&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $output_title . '">Edit</a>
                        ' . $output_custom_shipping_form . '
                    </div>';
            }
        }

        // get arrival dates
        $query = "SELECT id, name, description, arrival_date, default_selected, custom
                 FROM arrival_dates
                 WHERE (status = 'enabled') AND (start_date <= CURRENT_DATE()) AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
                 ORDER BY sort_order, arrival_date";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while ($row = mysqli_fetch_assoc($result)) {
            // if this is the default arrival date add checked attribute
            if ($row['default_selected'] == 1) {
                $arrival_date_field = $form->output_field(array('type'=>'radio', 'name'=>'arrival_date', 'id' => 'arrival_date_' . $row['id'], 'value'=>$row['id'], 'checked'=>'checked', 'class'=>'software_input_radio', 'required' => 'true'));

            // else this is not the default arrival date, so do not add checked attribute
            } else {
                $arrival_date_field = $form->output_field(array('type'=>'radio', 'name'=>'arrival_date', 'id' => 'arrival_date_' . $row['id'], 'value'=>$row['id'],  'class'=>'software_input_radio', 'required' => 'true'));
            }

            // if this arrival date displays a custom arrival date field, prepare field
            if ($row['custom']) {
                $custom_field =
                    $form->output_field(array('type'=>'text', 'id'=>'custom_arrival_date_' . $row['id'], 'name'=>'custom_arrival_date_' . $row['id'], 'size'=>'10', 'class'=>'software_input_text')) . '
                    <script>
                        software.init_custom_arrival_date({
                            radio_field_id: "arrival_date_' . $row['id'] . '",
                            date_field_id: "custom_arrival_date_' . $row['id'] . '"})
                    </script> ';

            } else {
                $custom_field = '';
            }

            $output_arrival_dates .=
                '<tr>
                    <td style="vertical-align: top">' . $arrival_date_field . '<label for="arrival_date_' . $row['id'] . '"> ' . h($row['name']) . '</label></td>
                    <td style="width: 100%;">' . $custom_field . $row['description'] . '</td>
                </tr>';
        }
        
        // if there was at least one active arrival date, prepare requested arrival date area
        if ($output_arrival_dates) {
            $output_requested_arrival_date =
                '<div class="arrival heading">Requested Arrival Date' . $for_ship_to_name . '</div>
                <div class="data">
                    <table class="arrival_dates" style="margin-bottom: 15px">
                        <tr>
                            <th style="width: 30%;text-align:left;">Select One</th>
                            <th style="width: 70%;text-align:left;">Description</th>
                        </tr>
                        ' . $output_arrival_dates . '
                    </table>
                </div>';
        }
        
        // if a submit button label was entered for the page, then use that
        if ($submit_button_label) {
            $output_submit_button_label = h($submit_button_label);
            
        // else a submit button label could not be found, so use a default label
        } else {
            $output_submit_button_label = 'Continue';
        }

        $output =
            get_date_picker_format() . '
            ' . $form->output_errors() . '
            <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shipping_address_and_arrival.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="page_id" value="' . $page_id . '" />
                <input type="hidden" name="ship_to_id" value="' . h($_GET['ship_to_id']) . '" />
                <input type="hidden" name="folder_id" value="' . $folder_id_for_default_value . '" />
                <div class="heading">Shipping Address' . $for_ship_to_name . '</div>
                <div class="data" style="margin-bottom: 1.5em">
                    ' . $output_shipping_address . '
                </div>
                ' . $output_custom_shipping_form . '
                ' . $output_requested_arrival_date . '
                <div style="text-align: right"><input type="submit" name="submit" value="' . $output_submit_button_label . '" class="software_input_submit_primary ship_arrival_button" /></div>
                ' . $system . '
            </form>';

    // Otherwise the layout is custom.
    } else {

        $form_attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/shipping_address_and_arrival.php" ' .
            'method="post"';

        $form->set('salutation', 'options', get_salutation_options());

        $form->set('first_name', 'maxlength', 50);
        $form->set('first_name', 'required', true);

        $form->set('last_name', 'maxlength', 50);
        $form->set('last_name', 'required', true);

        $form->set('company', 'maxlength', 50);
        
        $form->set('address_1', 'maxlength', 50);
        $form->set('address_1', 'required', true);

        $form->set('address_2', 'maxlength', 50);

        $form->set('city', 'maxlength', 50);
        $form->set('city', 'required', true);

        $form->set('state', 'maxlength', 50);

        $form->set('zip_code', 'maxlength', 50);

        $form->set('country', 'required', true);

        $form->set('country', 'options', $country_options);

        settype($address_type, 'bool');

        $address_type_url = '';

        // If address type is enabled, then deal with it.
        if ($address_type) {
            $form->set('address_type', 'required', true);

            // If there is an address type page selected,
            // and that page still exists, then prepare address type URL.
            if (
                $address_type_page_id
                && ($address_type_page_name = get_page_name($address_type_page_id))
            ) {
                $address_type_url = PATH . encode_url_path($address_type_page_name);
            }
        }

        $form->set('phone_number', 'maxlength', 50);

        $custom_shipping_form = false;
        $custom_shipping_form_title = '';
        $fields = array();
        $edit = false;
        $edit_start = '';
        $edit_end = '';

        if ($form_enabled) {
            $custom_shipping_form = true;
            $custom_shipping_form_title = $form_name;

            $fields = db_items(
                "SELECT
                    id,
                    name,
                    label,
                    type,
                    default_value,
                    use_folder_name_for_default_value,
                    multiple,
                    required,
                    size,
                    maxlength,
                    wysiwyg,
                    `rows`, # Backticks for reserved word.
                    cols,
                    information
                FROM form_fields
                WHERE page_id = '" . e($page_id) . "'
                ORDER BY sort_order",
                'id');

            $wysiwyg_fields = array();
            $date_fields = array();
            $date_and_time_fields = array();

            foreach ($fields as $field) {
                $html_name = 'field_' . $field['id'];

                $attributes = $form->get_field($html_name);

                // If this is a check box group or pick list,
                // then get options for the field, because we will need them below.
                if (
                    ($field['type'] == 'check box')
                    || ($field['type'] == 'pick list')
                ) {
                    $options = db_items(
                        "SELECT
                            label,
                            value,
                            default_selected
                        FROM form_field_options
                        WHERE form_field_id = '" . $field['id'] . "'
                        ORDER BY sort_order");
                }

                // If the value for this field has not been set yet,
                // (e.g. the form has not been submitted by the customer),
                // then set default value.
                if (!isset($attributes['value'])) {
                    $value_from_query_string = trim($_GET['value_' . $field['id']]);

                    // If a default value was passed in the query string, then use that.
                    if ($value_from_query_string != '') {
                        $attributes['value'] = $value_from_query_string;
                        
                    // Otherwise, if field is set to use folder name for default value,
                    // then use it.
                    } else if ($field['use_folder_name_for_default_value']) {
                        $default_value = db_value(
                            "SELECT folder_name
                            FROM folder
                            WHERE folder_id = '" . e($folder_id_for_default_value) . "'");

                        $attributes['value'] = $default_value;

                    // Otherwise if there is a default value, then use that.
                    } else if ($field['default_value'] != '') {
                        $attributes['value'] = $field['default_value'];

                    // Otherwise if this is a check box group or pick list,
                    // then use on/off values (default_selected) from choices.
                    } else if (
                        ($field['type'] == 'check box')
                        || ($field['type'] == 'pick list')
                    ) {
                        $values = array();

                        foreach ($options as $option) {
                            if ($option['default_selected']) {
                                $values[] = $option['value'];
                            }
                        }

                        // If there is at least one default-selected value, then continue to set it.
                        if ($values) {
                            // If there is only one value, then set it.
                            if (count($values) == 1) {
                                $attributes['value'] = $values[0];

                            // Otherwise, there is more than one value, so set all of them.
                            } else {
                                $attributes['value'] = $values;
                            }
                        }
                    }
                }

                switch ($field['type']) {

                    case 'date':
                        $date_fields[] = $html_name;

                        break;

                    case 'date and time':
                        $date_and_time_fields[] = $html_name;

                        break;

                    case 'pick list':
                        
                        $attributes['options'] = array();
                        
                        foreach ($options as $option) {
                            $attributes['options'][$option['label']] =
                                array(
                                    'value' => $option['value'],
                                    'default_selected' => $option['default_selected']
                                );
                        }

                        break;

                    case 'text area':
                        // If field is a rich-text editor, then remember that,
                        // so we can prepare JS later.
                        if ($field['wysiwyg']) {
                            $wysiwyg_fields[] = $html_name;
                        }

                        if ($field['rows']) {
                            $attributes['rows'] = $field['rows'];
                        }

                        if ($field['cols']) {
                            $attributes['cols'] = $field['cols'];
                        }

                        break;

                }

                if (
                    $field['size']
                    and
                    (
                        ($field['type'] == 'text box')
                        or ($field['type'] == 'pick list')
                        or ($field['type'] == 'file upload')
                        or ($field['type'] == 'date')
                        or ($field['type'] == 'date and time')
                        or ($field['type'] == 'email address')
                        or ($field['type'] == 'time')
                    )
                ) {
                    $attributes['size'] = $field['size'];
                }

                if ($field['type'] == 'date') {
                    $attributes['maxlength'] = 10;

                } else if ($field['type'] == 'date and time') {
                    $attributes['maxlength'] = 22;

                } else if ($field['type'] == 'time') {
                    $attributes['maxlength'] = 11;

                } else if (
                    $field['maxlength']
                    and
                    (
                        ($field['type'] == 'text box')
                        or ($field['type'] == 'text area')
                        or ($field['type'] == 'file upload')
                        or ($field['type'] == 'email address')
                    )
                ) {
                    $attributes['maxlength'] = $field['maxlength'];
                }

                // If this field is required, and it is not a check box,
                // or it is a check box and there is just one check box option,
                // then add required attribute.  We don't add the required attribute,
                // when there are multiple check box options, because it would require
                // that all of them be checked.
                if (
                    $field['required']
                    &&
                    (
                        ($field['type'] != 'check box')
                        || (count($options) == 1)
                    )
                ) {
                    $attributes['required'] = true;
                }

                // If there is at least one attribute to set, then set them.
                if ($attributes) {
                    $form->set_field($html_name, $attributes);
                }
            }

            // If edit mode is on, then output grid around custom shipping form.
            if ($editable) {
                $edit = true;
                
                // If the form name is not blank, then add it to the title.
                if ($form_name != '') {
                    $title = ': ' . h($form_name);
                } else {
                    $title = '';
                }

                $edit_start =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&from=pages&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Custom Shipping Form' . $title . '">Edit</a>';

                $edit_end = '</div>';
            }

        }

        $arrival_dates = db_items(
            "SELECT id, name, description, default_selected, custom
            FROM arrival_dates
            WHERE
                (status = 'enabled')
                AND (start_date <= CURRENT_DATE())
                AND (concat(end_date, ' " . ECOMMERCE_END_OF_DAY_TIME . "') > NOW())
            ORDER BY sort_order, arrival_date");

        // If there is at least one active arrival date, then require one to be selected.
        if ($arrival_dates) {
            $form->set('arrival_date', 'required', true);
        }

        $custom_arrival_dates = array();

        foreach ($arrival_dates as $arrival_date) {
            if ($arrival_date['custom']) {
                $custom_arrival_dates[] = $arrival_date['id'];
            }
        }

        // If a submit button label was not entered for the page, then set default label.
        if ($submit_button_label == '') {
            $submit_button_label = 'Continue';
        }

        $system .=
            get_token_field() . '

            <input type="hidden" name="page_id" value="' . $page_id . '">
            <input type="hidden" name="ship_to_id" value="' . h($_GET['ship_to_id']) . '">
            <input type="hidden" name="folder_id" value="' . $folder_id_for_default_value . '">';

        // If there is at least one rich-text editor field,
        // then output JS for them.
        if ($wysiwyg_fields) {
            $system .= get_wysiwyg_editor_code($wysiwyg_fields);
        }

        // If there is a date or date and time field, then prepare system content,
        // for the date/time picker.
        if (
            (
                $form_enabled
                && ($date_fields || $date_and_time_fields)
            )
            || $custom_arrival_dates
        ) {
            $system .= get_date_picker_format();

            if ($form_enabled) {

                foreach ($date_fields as $date_field) {
                    $system .=
                        '<script>
                            software_$("#' . escape_javascript($date_field) . '").datepicker({
                                dateFormat: date_picker_format
                            });
                        </script>';
                }

                // If there is a date and time field, then prepare JS for those.
                if ($date_and_time_fields) {
                    // Include JS file for timepicker.
                    $system .=
                        '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/jquery/jquery-ui-timepicker-addon-1.2.1.min.js"></script>';

                    foreach ($date_and_time_fields as $date_and_time_field) {
                        $system .=
                            '<script>
                                software_$("#' . escape_javascript($date_and_time_field) . '").datetimepicker({
                                    dateFormat: date_picker_format,
                                    timeFormat: "h:mm TT"
                                });
                            </script>';
                    }
                }

            }

            foreach ($custom_arrival_dates as $custom_arrival_date) {
                $system .=
                    '<script>
                        software.init_custom_arrival_date({
                            radio_field_id: "arrival_date_' . $custom_arrival_date . '",
                            date_field_id: "custom_arrival_date_' . $custom_arrival_date . '"})
                    </script>';
            }
        }

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $form_attributes,
            'ship_to_name' => $ship_to_name,
            'address_type' => $address_type,
            'address_type_url' => $address_type_url,
            'shipping_same_as_billing' => $shipping_same_as_billing,
            'custom_shipping_form' => $custom_shipping_form,
            'custom_shipping_form_title' => $custom_shipping_form_title,
            'field' => $fields,
            'edit' => $edit,
            'edit_start' => $edit_start,
            'edit_end' => $edit_end,
            'arrival_dates' => $arrival_dates,
            'submit_button_label' => $submit_button_label,
            'system' => $system));

        $output = $form->prepare($output);
        
    }

    $form->remove_form();

    return
        '<div class="software_shipping_address_and_arrival">
            ' . $output . '
        </div>';
}