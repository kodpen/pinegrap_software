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

function get_billing_information($properties) {

    $page_id = $properties['page_id'];
    $editable = $properties['editable'];
    $device_type = $properties['device_type'];
    $folder_id_for_default_value = $properties['folder_id_for_default_value'];

    $properties = get_page_type_properties($page_id, 'billing information');

    $custom_field_1_label = $properties['custom_field_1_label'];
    $custom_field_1_required = $properties['custom_field_1_required'];
    $custom_field_2_label = $properties['custom_field_2_label'];
    $custom_field_2_required = $properties['custom_field_2_required'];
    $po_number = $properties['po_number'];
    $form_enabled = $properties['form'];
    $form_name = $properties['form_name'];
    $form_label_column_width = $properties['form_label_column_width'];
    $submit_button_label = $properties['submit_button_label'];
    $next_page_id = $properties['next_page_id'];

    $layout_type = get_layout_type($page_id);
    
    $order_id = $_SESSION['ecommerce']['order_id'];
    
    // store page id for billing information page in case we need to come back to this page later in the order process
    $_SESSION['ecommerce']['billing_information_page_id'] = $page_id;

    $ghost = $_SESSION['software']['ghost'];

    $contact = array();
    
    // If user is logged in and not ghosting, get contact for user.
    if (USER_LOGGED_IN and !$ghost) {
        $contact = db_item(
            "SELECT
                id,
                salutation,
                first_name,
                last_name,
                company,
                business_address_1,
                business_address_2,
                business_city,
                business_state,
                business_zip_code,
                business_country,
                business_phone,
                business_fax,
                email_address,
                lead_source,
                opt_in
            FROM contacts
            WHERE id = '" . USER_CONTACT_ID . "'");
    }
    
    $form = new liveform('billing_information');

    // if form was not just filled out, get data from order in order to populate fields
    if ($form->field_in_session('billing_first_name') == false) {
        $query = "SELECT
                    billing_salutation,
                    billing_first_name,
                    billing_last_name,
                    billing_company,
                    billing_address_1,
                    billing_address_2,
                    billing_city,
                    billing_state,
                    billing_zip_code,
                    billing_country,
                    billing_phone_number,
                    billing_fax_number,
                    billing_email_address,
                    custom_field_1,
                    custom_field_2,
                    opt_in,
                    po_number,
                    tax_exempt,
                    referral_source_code
                 FROM orders WHERE id = '$order_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $order = mysqli_fetch_assoc($result);

        if ($order['billing_salutation'] != '') {
            $form->assign_field_value('billing_salutation', $order['billing_salutation']);
        } else if ($contact['salutation'] != '') {
            $form->assign_field_value('billing_salutation', $contact['salutation']);
        }

        if ($order['billing_first_name'] != '') {
            $form->assign_field_value('billing_first_name', $order['billing_first_name']);
        } else if ($contact['first_name'] != '') {
            $form->assign_field_value('billing_first_name', $contact['first_name']);
        }

        if ($order['billing_last_name'] != '') {
            $form->assign_field_value('billing_last_name', $order['billing_last_name']);
        } else if ($contact['last_name'] != '') {
            $form->assign_field_value('billing_last_name', $contact['last_name']);
        }

        if ($order['billing_company'] != '') {
            $form->assign_field_value('billing_company', $order['billing_company']);
        } else if ($contact['company'] != '') {
            $form->assign_field_value('billing_company', $contact['company']);
        }

        if ($order['billing_address_1'] != '') {
            $form->assign_field_value('billing_address_1', $order['billing_address_1']);
        } else if ($contact['business_address_1'] != '') {
            $form->assign_field_value('billing_address_1', $contact['business_address_1']);
        }

        if ($order['billing_address_2'] != '') {
            $form->assign_field_value('billing_address_2', $order['billing_address_2']);
        } else if ($contact['business_address_2'] != '') {
            $form->assign_field_value('billing_address_2', $contact['business_address_2']);
        }

        if ($order['billing_city'] != '') {
            $form->assign_field_value('billing_city', $order['billing_city']);
        } else if ($contact['business_city'] != '') {
            $form->assign_field_value('billing_city', $contact['business_city']);
        }

        if ($order['billing_state'] != '') {
            $form->assign_field_value('billing_state', $order['billing_state']);
        } else if ($contact['business_state'] != '') {
            $form->assign_field_value('billing_state', $contact['business_state']);
        }

        if ($order['billing_zip_code'] != '') {
            $form->assign_field_value('billing_zip_code', $order['billing_zip_code']);
        } else if ($contact['business_zip_code'] != '') {
            $form->assign_field_value('billing_zip_code', $contact['business_zip_code']);
        }

        if ($order['billing_country'] != '') {
            $form->assign_field_value('billing_country', $order['billing_country']);
        } else if ($contact['business_country'] != '') {
            $form->assign_field_value('billing_country', $contact['business_country']);

        // Otherwise country is blank, so set default country.
        } else {
            $query = "SELECT code FROM countries WHERE default_selected = 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            // if a default country was found, set default country
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $form->assign_field_value('billing_country', $row['code']);
            }
        }

        if ($order['billing_phone_number'] != '') {
            $form->assign_field_value('billing_phone_number', $order['billing_phone_number']);
        } else if ($contact['business_phone'] != '') {
            $form->assign_field_value('billing_phone_number', $contact['business_phone']);
        }

        if ($order['billing_fax_number'] != '') {
            $form->assign_field_value('billing_fax_number', $order['billing_fax_number']);
        } else if ($contact['business_fax'] != '') {
            $form->assign_field_value('billing_fax_number', $contact['business_fax']);
        }

        if ($order['billing_email_address'] != '') {
            $form->assign_field_value('billing_email_address', $order['billing_email_address']);
        } else if ($contact['email_address'] != '') {
            $form->assign_field_value('billing_email_address', $contact['email_address']);
        }

        if ($order['custom_field_1'] != '') {
            $form->assign_field_value('custom_field_1', $order['custom_field_1']);
        }

        if ($order['custom_field_2'] != '') {
            $form->assign_field_value('custom_field_2', $order['custom_field_2']);
        }

        if ($order['po_number'] != '') {
            $form->assign_field_value('po_number', $order['po_number']);
        }

        if ($order['referral_source_code'] != '') {
            $form->assign_field_value('referral_source', $order['referral_source_code']);
        } else if ($contact['lead_source'] != '') {
            $form->assign_field_value('referral_source', $contact['lead_source']);
        }

        if ($order['opt_in'] == 1) {
            $form->assign_field_value('opt_in', '1');
        } else {
            $form->assign_field_value('opt_in', '');
        }

        // If the visitor is logged in and not ghosting and the visitor has selected
        // that he/she does not want his/her contact info updated with the billing info
        // previously in this session, then uncheck the update contact check box.
        if (USER_LOGGED_IN and !$ghost and ($_SESSION['software']['update_contact'] === false)) {
            $form->assign_field_value('update_contact', '');
        } else {
            $form->assign_field_value('update_contact', '1');
        }

        if ($order['tax_exempt'] == 1) {
            $form->assign_field_value('tax_exempt', '1');
        } else {
            $form->assign_field_value('tax_exempt', '');
        }

        // If a custom billing form is enabled and this visitor has an order,
        // then prefill those fields.
        if ($form_enabled && $order_id) {
            $fields = db_items(
                "SELECT
                    form_data.form_field_id,
                    form_data.data,
                    count(*) as number_of_values,
                    form_fields.type
                FROM form_data
                LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id
                WHERE
                    (form_data.order_id = '$order_id')
                    AND (form_data.ship_to_id = '0')
                    AND (form_data.order_item_id = '0')
                GROUP BY form_data.form_field_id");
            
            // Loop through all field data in order to prefill fields.
            foreach ($fields as $field) {
                // If there is more than one value, get all values.
                if ($field['number_of_values'] > 1) {
                    $field['data'] = db_values(
                        "SELECT data
                        FROM form_data
                        WHERE
                            (order_id = '$order_id')
                            AND (ship_to_id = '0')
                            AND (order_item_id = '0')
                            AND (form_field_id = '" . $field['form_field_id'] . "')
                        ORDER BY id");
                }
                
                $html_name = 'field_' . $field['form_field_id'];
                
                $form->assign_field_value($html_name, prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false));
            }
        }
    }

    $system = '';

    // set blank to be the first option of the county selection drop-down
    $billing_country_options[''] = '';

    // get countries for country selection
    $countries = db_items("SELECT name, code, zip_code_required AS zip FROM countries ORDER BY name", 'code');

    foreach ($countries as $country) {
        $billing_country_options[$country['name']] = $country['code'];
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
                country_id: "billing_country",
                state_text_box_id: "billing_state_text_box",
                state_pick_list_id: "billing_state_pick_list",
                zip_code_id: "billing_zip_code"});
        </script>';

    if ($layout_type == 'system') {
    
        $output_custom_field_1 = '';
        
        // if there is a custom field 1 label, then prepare to output custom field 1
        if ($custom_field_1_label) {
            $output_custom_field_1_label = $custom_field_1_label;

            $required = '';
            
            // if custom field 1 is required, then prepare asterisk
            if ($custom_field_1_required == 1) {
                $output_custom_field_1_label .= '*';
                $required = 'true';
            }
            
            $output_custom_field_1 =
                '<tr>
                    <td>' . h($output_custom_field_1_label) . '</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'custom_field_1', 'maxlength'=>'255', 'class'=>'software_input_text', 'required' => $required)) . '</td>
                </tr>';
        }
        
        $output_custom_field_2 = '';
        
        // if there is a custom field 2 label, then prepare to output custom field 2
        if ($custom_field_2_label) {
            $output_custom_field_2_label = $custom_field_2_label;

            $required = '';
            
            // if custom field 2 is required, then prepare asterisk
            if ($custom_field_2_required == 1) {
                $output_custom_field_2_label .= '*';
                $required = 'true';
            }
            
            $output_custom_field_2 =
                '<tr>
                    <td>' . h($output_custom_field_2_label) . '</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'custom_field_2', 'maxlength'=>'255', 'class'=>'software_input_text', 'required' => $required)) . '</td>
                </tr>';
        }
        
        $output_custom_field_spacing = '';
        
        // if there is a custom field 1 or custom field 2 to output, prepare spacing
        if ($output_custom_field_1 || $output_custom_field_2) {
            $output_custom_field_spacing =
                '<tr>
                    <td colspan="2">&nbsp;</td>
                </tr>';
        }
        
        // assume that the opt in field will not be displayed until we find out otherwise
        $output_opt_in = '';
        $output_opt_in_displayed_value = 'false';
        
        // if contact cannot be found or if contact is opted out, we are going to display opt-in field
        if ((!$contact['id']) || ($contact['opt_in'] == 0)) {
            $output_opt_in =
                '<tr>
                    <td>&nbsp;</td>
                    <td>' . $form->output_field(array('type'=>'checkbox', 'name'=>'opt_in', 'id'=>'opt_in', 'value' => '1', 'checked'=>'checked', 'class'=>'software_input_checkbox')) . '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label></td>
                </tr>';
            
            $output_opt_in_displayed_value = 'true';
        }
        
        // if po number is enabled, output po number field
        if ($po_number) {
            $output_po_number =
                '<tr>
                    <td style="white-space: nowrap">PO Number</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'po_number', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>';
        }
        
        // check to see if there is at least one referral source
        $query = "SELECT name, code FROM referral_sources";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if there is at least one referral source, prepare referral source drop-down selection field
        if (mysqli_num_rows($result) > 0) {
            $referral_source_options[''] = '';

            // loop through all referral sources to prepare all options for selection drop-down field
            while ($row = mysqli_fetch_assoc($result)) {
                $referral_source_options[$row['name']] = $row['code'];
            }

            $output_referral_source =
                '<tr>
                    <td>How did you hear about us?</td>
                    <td>' . $form->output_field(array('type'=>'select', 'name'=>'referral_source', 'options'=>$referral_source_options, 'class'=>'software_select')) . '</td>
                </tr>';
        }

        $output_update_contact = '';

        // If this visitor is logged in and not ghosting, then output check box to allow the visitor
        // to decide if he/she wants his/her contact info to be updated with the billing info.
        if (USER_LOGGED_IN and !$ghost) {
            // If tax exempt area is going to be outputted below this area,
            // then just output a little bottom margin.
            if ((ECOMMERCE_TAX == true) && (ECOMMERCE_TAX_EXEMPT == true)) {
                $output_margin_bottom = '.7em';

            // Otherwise the tax exempt area is not going to be outputted,
            // so output more margin, so there is a greater space before the next area.
            } else {
                $output_margin_bottom = '1.5em';
            }
            
            $output_update_contact =
                '<div class="update_contact" style="margin-bottom: ' . $output_margin_bottom . '">' .
                    $form->output_field(array(
                        'type' => 'checkbox',
                        'id' => 'update_contact',
                        'name' => 'update_contact',
                        'value' => '1',
                        'class' => 'software_input_checkbox')) .
                    '<label for="update_contact"> Update my contact info with this billing info.</label>
                </div>';
        }

        $output_tax_exempt = '';
        
        // if tax is on and tax-exempt is allowed, prepare tax-exempt checkbox
        if ((ECOMMERCE_TAX == true) && (ECOMMERCE_TAX_EXEMPT == true)) {
            if (ECOMMERCE_TAX_EXEMPT_LABEL) {
                $output_tax_exempt_label = ECOMMERCE_TAX_EXEMPT_LABEL;
            } else {
                $output_tax_exempt_label = 'Tax-Exempt?';
            }
            
            $output_tax_exempt =
                '<div style="margin-bottom: 1.5em; text-align: left">
                    ' . $form->output_field(array('type'=>'checkbox', 'name'=>'tax_exempt', 'value' => '1', 'id'=>'tax_exempt', 'class'=>'software_input_checkbox')) . '<label for="tax_exempt"> ' . h($output_tax_exempt_label) . '</label>
                </div>';
        }

        $output_custom_billing_form = '';

        // If a custom billing form is enabled, then output it.
        if ($form_enabled) {
            $output_form_name = '';
            
            // If there is a form name, then output form name.
            if ($form_name != '') {
                $output_form_name = '<div class="heading">' . h($form_name) . '</div>';
            }
            
            // Get form info (form content, wysiwyg fields).
            $form_info = get_form_info($page_id, 0, 0, 0, $form_label_column_width, $office_use_only = false, $form, 'frontend', false, $device_type, $folder_id_for_default_value);
            
            $output_wysiwyg_javascript = '';
            
            // If there is at least one wysiwyg field, prepare wysiwyg fields.
            if (count($form_info['wysiwyg_fields']) > 0) {
                $output_wysiwyg_javascript = get_wysiwyg_editor_code($form_info['wysiwyg_fields']);
            }
            
            $output_custom_billing_form =
                $output_wysiwyg_javascript . '
                ' . $output_form_name . '
                <div class="data">
                    <table class="custom_billing_form" style="margin-bottom: 1.5em">
                        ' . $form_info['content'] . '
                    </table>
                </div>';
            
            // If edit mode is on, then output grid around custom billing form.
            if ($editable == true) {
                $output_title = 'Custom Billing Form';
                
                // if the form name is not blank, then add it to the title
                if ($form_name != '') {
                    $output_title .= ': ' . h($form_name);
                }
                
                $output_custom_billing_form =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&from=pages&send_to=' . h(urlencode(get_request_uri())) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="' . $output_title . '">Edit</a>
                        ' . $output_custom_billing_form . '
                    </div>';
            }
        }
        
        // if a submit button label was entered for the page, then use that
        if ($submit_button_label) {
            $output_submit_button_label = h($submit_button_label);
            
        // else a submit button label could not be found, so use a default label
        } else {
            $output_submit_button_label = 'Continue';
        }

        $output =
            $form->output_errors() . '
            <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/billing_information.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="page_id" value="' . $page_id . '" />
                <input type="hidden" name="next_page_id" value="' . $next_page_id . '" />
                <input type="hidden" name="opt_in_displayed" value="' . $output_opt_in_displayed_value . '" />
                <div class="heading"></div>
                <table class="custom_fields" style="margin-bottom: 1.5em">
                    ' . $output_custom_field_1 . '
                    ' . $output_custom_field_2 . '
                    ' . $output_custom_field_spacing . '
                    <tr>
                        <td>Salutation</td>
                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'billing_salutation', 'options'=>get_salutation_options(), 'class'=>'software_select')) . '</td>
                    </tr>
                    <tr>
                        <td>First Name*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_first_name', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>Last Name*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_last_name', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>Company</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_company', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>Address 1*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_address_1', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>Address 2</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_address_2', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>City*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'billing_city', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>Country*</td>
                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'billing_country', 'id'=>'billing_country', 'options'=>$billing_country_options, 'class'=>'software_select', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>
                            <label for="billing_state_text_box">
                                State / Province
                            </label>

                            <label for="billing_state_pick_list" style="display: none">
                                State / Province*
                            </label>
                        </td>
                        <td>' .
                            $form->output_field(array('type'=>'text', 'id'=> 'billing_state_text_box', 'name'=> 'billing_state', 'maxlength'=>'50', 'class'=>'software_input_text')) .

                            $form->output_field(array('type'=>'select', 'id'=> 'billing_state_pick_list', 'name'=> 'billing_state', 'class'=>'software_select')) . '
                        </td>
                    </tr>
                    <tr>
                        <td>Zip / Postal Code<span id="billing_zip_code_required" style="display: none">*</span></td>
                        <td>' . $form->output_field(array('type'=>'text', 'id' => 'billing_zip_code', 'name'=>'billing_zip_code', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>Phone*</td>
                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'billing_phone_number', 'maxlength'=>'50', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    <tr>
                        <td>Email*</td>
                        <td>' . $form->output_field(array('type'=>'email', 'name'=>'billing_email_address', 'maxlength'=>'100', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                    </tr>
                    ' . $output_opt_in . '
                    ' . $output_po_number . '
                    ' . $output_referral_source . '

                </table>
                ' . $output_update_contact . '
                ' . $output_tax_exempt . '
                ' . $output_custom_billing_form . '
                <div class="mobile_margin_bottom" style="text-align: right; padding-top: 1em">
                    <input type="submit" name="submit" value="' . $output_submit_button_label . '" class="software_input_submit_primary billing_button" />
                </div>
                ' . $system . '
            </form>';

    // Otherwise the layout is custom.
    } else {

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/billing_information.php" ' .
            'method="post"';

        settype($custom_field_1_required, 'bool');
        
        // If there is a custom field 1 label, then prepare to output custom field 1.
        if ($custom_field_1_label != '') {
            $custom_field_1 = true;

            $form->set('custom_field_1', 'maxlength', 255);
            $form->set('custom_field_1', 'required', $custom_field_1_required);

        } else {
            $custom_field_1 = false;
        }

        settype($custom_field_2_required, 'bool');

        // If there is a custom field 2 label, then prepare to output custom field 2.
        if ($custom_field_2_label != '') {
            $custom_field_2 = true;

            $form->set('custom_field_2', 'maxlength', 255);
            $form->set('custom_field_2', 'required', $custom_field_2_required);

        } else {
            $custom_field_2 = false;
        }

        $form->set('billing_salutation', 'options', get_salutation_options());

        $form->set('billing_first_name', 'maxlength', 50);
        $form->set('billing_first_name', 'required', true);

        $form->set('billing_last_name', 'maxlength', 50);
        $form->set('billing_last_name', 'required', true);

        $form->set('billing_company', 'maxlength', 50);
        
        $form->set('billing_address_1', 'maxlength', 50);
        $form->set('billing_address_1', 'required', true);

        $form->set('billing_address_2', 'maxlength', 50);

        $form->set('billing_city', 'maxlength', 50);
        $form->set('billing_city', 'required', true);

        $form->set('billing_state', 'maxlength', 50);

        $form->set('billing_zip_code', 'maxlength', 50);

        $form->set('billing_country', 'required', true);

        $form->set('billing_country', 'options', $billing_country_options);

        $form->set('billing_phone_number', 'maxlength', 50);
        $form->set('billing_phone_number', 'required', true);

        $form->set('billing_fax_number', 'maxlength', 50);

        $form->set('billing_email_address', 'maxlength', 100);
        $form->set('billing_email_address', 'required', true);
        
        // If contact cannot be found or if contact is opted out,
        // we are going to display opt-in field.
        if (!$contact['id'] || !$contact['opt_in']) {
            $opt_in = true;
            $opt_in_label = OPT_IN_LABEL;
            $opt_in_displayed_value = 'true';

        } else {
            $opt_in = false;
            $opt_in_label = '';
            $opt_in_displayed_value = 'false';
        }

        settype($po_number, 'bool');

        if ($po_number) {
            $form->set('po_number', 'maxlength', 50);
        }

        $referral_sources = db_items(
            "SELECT name, code
            FROM referral_sources
            ORDER BY sort_order, name");

        // If there is at least one referral source, prepare referral source pick list.
        if ($referral_sources) {
            $referral_source = true;

            $referral_source_options[''] = '';

            foreach ($referral_sources as $referral_source) {
                $referral_source_options[$referral_source['name']] = $referral_source['code'];
            }

            $form->set('referral_source', 'options', $referral_source_options);

        } else {
            $referral_source = false;
        }

        // If this visitor is logged in and not ghosting, then output check box to allow the visitor
        // to decide if he/she wants his/her contact info to be updated with the billing info.
        if (USER_LOGGED_IN and !$ghost) {
            $update_contact = true;
        } else {
            $update_contact = false;
        }
        
        // If tax is on and tax-exempt is allowed, prepare tax-exempt checkbox.
        if (ECOMMERCE_TAX && ECOMMERCE_TAX_EXEMPT) {
            $tax_exempt = true;

            if (ECOMMERCE_TAX_EXEMPT_LABEL != '') {
                $tax_exempt_label = ECOMMERCE_TAX_EXEMPT_LABEL;
            } else {
                $tax_exempt_label = 'Tax-Exempt?';
            }

        } else {
            $tax_exempt = false;
            $tax_exempt_label = '';
        }

        $custom_billing_form = false;
        $custom_billing_form_title = '';
        $fields = array();
        $edit = false;
        $edit_start = '';
        $edit_end = '';

        if ($form_enabled) {

            $custom_billing_form = true;
            $custom_billing_form_title = $form_name;

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

                // If this is a radio, check box group, or pick list,
                // then get options for the field, because we will need them below.
                if (
                    ($field['type'] == 'radio button')
                    || ($field['type'] == 'check box')
                    || ($field['type'] == 'pick list')
                ) {
                    $field['options'] = db_items(
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
                if (!$form->field_in_session($html_name)) {
                    $value_from_query_string = trim($_GET['value_' . $field['id']]);

                    // If a default value was passed in the query string, then use that.
                    if ($value_from_query_string != '') {
                        $form->set($html_name, $value_from_query_string);
                        
                    // Otherwise, if field is set to use folder name for default value,
                    // then use it.
                    } else if ($field['use_folder_name_for_default_value']) {
                        $default_value = db_value(
                            "SELECT folder_name
                            FROM folder
                            WHERE folder_id = '" . e($folder_id_for_default_value) . "'");

                        $form->set($html_name, $default_value);

                    // Otherwise if there is a default value, then use that.
                    } else if ($field['default_value'] != '') {
                        $form->set($html_name, $field['default_value']);

                    // Otherwise if this is a check box group or pick list,
                    // then use on/off values (default_selected) from choices.
                    } else if (
                        ($field['type'] == 'check box')
                        || ($field['type'] == 'pick list')
                    ) {
                        $values = array();

                        foreach ($field['options'] as $option) {
                            if ($option['default_selected']) {
                                $values[] = $option['value'];
                            }
                        }

                        // If there is at least one default-selected value, then continue to set it.
                        if ($values) {
                            // If there is only one value, then set it.
                            if (count($values) == 1) {
                                $form->set($html_name, $values[0]);

                            // Otherwise, there is more than one value, so set all of them.
                            } else {
                                $form->set($html_name, $values);
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

                        $field['pick_list_options'] = array();
                        
                        foreach ($field['options'] as $option) {
                            $field['pick_list_options'][$option['label']] =
                                array(
                                    'value' => $option['value'],
                                    'default_selected' => $option['default_selected']
                                );
                        }

                        $form->set($html_name, 'options', $field['pick_list_options']);

                        break;

                    case 'text area':
                        // If field is a rich-text editor, then remember that,
                        // so we can prepare JS later.
                        if ($field['wysiwyg']) {
                            $wysiwyg_fields[] = $html_name;
                        }

                        if ($field['rows']) {
                            $form->set($html_name, 'rows', $field['rows']);
                        }

                        if ($field['cols']) {
                            $form->set($html_name, 'cols', $field['cols']);
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
                    $form->set($html_name, 'size', $field['size']);
                }

                if ($field['type'] == 'date') {
                    $form->set($html_name, 'maxlength', 10);

                } else if ($field['type'] == 'date and time') {
                    $form->set($html_name, 'maxlength', 22);

                } else if ($field['type'] == 'time') {
                    $form->set($html_name, 'maxlength', 11);

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
                    $form->set($html_name, 'maxlength', $field['maxlength']);
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
                        || (count($field['options']) == 1)
                    )
                ) {
                    $form->set($html_name, 'required', true);
                }

            }

            // If edit mode is on, then output grid around custom billing form.
            if ($editable) {
                $edit = true;
                
                // If the form name is not blank, then add it to the title.
                if ($form_name != '') {
                    $title = ': ' . h($form_name);
                } else {
                    $title = '';
                }

                $edit_start =
                    '<div class="edit_mode" style="position: relative; border: 1px dashed #4780C5; margin: -1px;"><a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_fields.php?page_id=' . $page_id . '&from=pages&send_to=' . h(urlencode(REQUEST_URL)) . '" style="background: #4780C5; position: absolute; display: block; font-family: \'Lucida Grande\',\'Lucida Sans Unicode\',\'Lucida Sans\',Lucida,Arial,Verdana,sans-serif; color: #FFFFFF; font-size: 12px; font-style: normal; font-weight: normal; text-decoration: none; z-index: 10; line-height: normal; padding: 5px 8px; margin: -1px 25px 0 -1px; border: none; -webkit-border-bottom-right-radius: 6px; -moz-border-bottom-right-radius: 6px; border-bottom-right-radius: 6px;" title="Custom Billing Form' . $title . '">Edit</a>';

                $edit_end = '</div>';
            }

        }

        // If a submit button label was not entered for the page, then set default label.
        if ($submit_button_label == '') {
            $submit_button_label = 'Continue';
        }

        $system .=
            get_token_field() . '

            <input type="hidden" name="page_id" value="' . $page_id . '">
            <input type="hidden" name="next_page_id" value="' . $next_page_id . '">
            <input type="hidden" name="opt_in_displayed" value="' . $opt_in_displayed_value . '">
            <input type="hidden" name="folder_id" value="' . $folder_id_for_default_value . '">';

        // If there is at least one rich-text editor field,
        // then output JS for them.
        if ($wysiwyg_fields) {
            $system .= get_wysiwyg_editor_code($wysiwyg_fields);
        }

        // If there is a date or date and time field, then prepare system content,
        // for the date/time picker.
        if ($date_fields || $date_and_time_fields) {
            $system .= get_date_picker_format();

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

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'custom_field_1' => $custom_field_1,
            'custom_field_1_label' => $custom_field_1_label,
            'custom_field_1_required' => $custom_field_1_required,
            'custom_field_2' => $custom_field_2,
            'custom_field_2_label' => $custom_field_2_label,
            'custom_field_2_required' => $custom_field_2_required,
            'opt_in' => $opt_in,
            'opt_in_label' => $opt_in_label,
            'po_number' => $po_number,
            'referral_source' => $referral_source,
            'update_contact' => $update_contact,
            'tax_exempt' => $tax_exempt,
            'tax_exempt_label' => $tax_exempt_label,
            'custom_billing_form' => $custom_billing_form,
            'custom_billing_form_title' => $custom_billing_form_title,
            'field' => $fields,
            'edit' => $edit,
            'edit_start' => $edit_start,
            'edit_end' => $edit_end,
            'submit_button_label' => $submit_button_label,
            'system' => $system));

        $output = $form->prepare($output);

    }

    $form->remove();

    return
        '<div class="software_billing_information">
            ' . $output . '
        </div>';
}