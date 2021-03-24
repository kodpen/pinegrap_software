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

if (!$_POST) {

    //Output config code if exist
    $config_code_output = '';
    //get product_image_code_template from config
    $query = "SELECT product_image_code_template FROM config";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    if($row['product_image_code_template'] != ''){
        $config_code_output = $row['product_image_code_template'];
    }

    // if tax is on, check tax checkbox
    if (ECOMMERCE_TAX == true) {
        $tax_checked = 'checked="checked"';
    }
    
    // if shipping is on, check shippable checkbox
    if (ECOMMERCE_SHIPPING == true) {
        $shippable_checked = 'checked="checked"';

    // else shipping is not on, so do not check shippable checkbox and hide shipping options
    } else {
        $shippable_checked = '';
        $shippable_row_style = 'display: none';
        $weight_row_style = 'display: none';
        $primary_weight_points_row_style = 'display: none';
        $secondary_weight_points_row_style = 'display: none';
        $dimensions_row_style = 'display: none';
        $container_required_row_style = 'display: none';
        $preparation_time_row_style = 'display: none';
        $free_shipping_row_style = 'display: none';
        $extra_shipping_cost_row_style = 'display: none';
        $allowed_zones_row_style = 'display: none';
    }

    // get all zones for zones selection
    $query = "SELECT id, name FROM zones ORDER BY name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        $output_allowed_zones .= '<option value="' . $row['id'] . '">' . h($row['name']) . '</option>';
    }
    
    // if the affiliate program is enabled, prepare affiliate program output
    if (AFFILIATE_PROGRAM == true) {
        $output_commissionable =
            '<tr>
                <td>Commissionable:</td>
                <td><input type="checkbox" name="commissionable" id="commissionable" value="1" checked="checked" class="checkbox" onclick="show_or_hide_commissionable()" /></td>
            </tr>
            <tr id="commission_rate_limit_row">
                <td style="padding-left: 2em">Commission Rate Limit:</td>
                <td><input type="text" name="commission_rate_limit" size="6" maxlength="6" /> % (leave blank for no limit)</td>
            </tr>';
    }
    
    // determine if start row should be outputted
    $output_start_row = '';
    
    // if payment gateway is not ClearCommerce, then prepare to output start row
    if (ECOMMERCE_PAYMENT_GATEWAY != 'ClearCommerce') {
        $output_start_row =
            '<tr id="start_row" style="display: none">
                <td style="padding-left: 2em">Start (days):</td>
                <td><input type="text" name="start" value="0" size="7" maxlength="7" /> day(s) from order date. (0 to start immediately)</td>
            </tr>';
    }
    
    // determine if recurring profile disabled rows should be outputted
    $output_recurring_profile_disabled_rows = '';
    
    // if credit/debit card payment method is enabled and payment gateway is PayPal Payments Pro, then prepare to output recurring profile disabled rows
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')) {
        $output_recurring_profile_disabled_rows =
            '<tr id="recurring_profile_disabled_perform_actions_row" style="display: none">
                <td style="padding-left: 2em"><label for="recurring_profile_disabled_perform_actions">Perform action(s) if profile is disabled:</label></td>
                <td><input type="checkbox" name="recurring_profile_disabled_perform_actions" id="recurring_profile_disabled_perform_actions" value="1" class="checkbox" onclick="show_or_hide_recurring_profile_disabled_perform_actions()" /> (requires recurring payment job)</td>
            </tr>
            <tr id="recurring_profile_disabled_expire_membership_row" style="display: none">
                <td style="padding-left: 40px"><label for="recurring_profile_disabled_expire_membership">Expire Membership:</label></td>
                <td><input type="checkbox" name="recurring_profile_disabled_expire_membership" id="recurring_profile_disabled_expire_membership" value="1" class="checkbox" /></td>
            </tr>
            <tr id="recurring_profile_disabled_revoke_private_access_row" style="display: none">
                <td style="padding-left: 40px"><label for="recurring_profile_disabled_revoke_private_access">Revoke Private Access:</label></td>
                <td><input type="checkbox" name="recurring_profile_disabled_revoke_private_access" id="recurring_profile_disabled_revoke_private_access" value="1" class="checkbox" /></td>
            </tr>
            <tr id="recurring_profile_disabled_email_row" style="display: none">
                <td style="padding-left: 40px"><label for="recurring_profile_disabled_email">Send E-mail to Customer:</label></td>
                <td><input type="checkbox" name="recurring_profile_disabled_email" id="recurring_profile_disabled_email" value="1" class="checkbox" onclick="show_or_hide_recurring_profile_disabled_email()" /></td>
            </tr>
            <tr id="recurring_profile_disabled_email_subject_row" style="display: none">
                <td style="padding-left: 60px">Subject:</td>
                <td><input type="text" name="recurring_profile_disabled_email_subject" maxlength="255" size="40" /></td>
            </tr>
            <tr id="recurring_profile_disabled_email_page_id_row" style="display: none">
                <td style="padding-left: 60px">Page:</td>
                <td><select name="recurring_profile_disabled_email_page_id"><option value="">-None-</option>' .  select_page() . '</select></td>
            </tr>';
    }
    
    // determine if Sage group ID row should be shown
    $output_sage_group_id_row = '';
    
    // if credit/debit card payment method is enabled and payment gateway is Sage, then output Sage group ID row
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == TRUE) && (ECOMMERCE_PAYMENT_GATEWAY == 'Sage')) {
        $output_sage_group_id_row =
            '<tr id="sage_group_id_row" style="display: none">
                <td style="padding-left: 2em">Sage Group ID:</td>
                <td><input type="text" name="sage_group_id" value="" size="10" maxlength="9" /></td>
            </tr>';
    }

    $output_attributes = '';
	
    // Get product attributes.
    $attributes = db_items(
        "SELECT
            id,
            name
        FROM product_attributes
        ORDER BY name", 'id');

    // If there are attributes, then get options and output attribute area.
    if ($attributes) {
        $attribute_options = db_items(
            "SELECT
                id,
                product_attribute_id,
                label
            FROM product_attribute_options
            ORDER BY
                product_attribute_id,
                sort_order");

        // Loop through the options in order to add them to the attributes array.
        foreach ($attribute_options as $attribute_option) {
            $attributes[$attribute_option['product_attribute_id']]['options'][] = $attribute_option;
        }
        
        // We use array_values() below so that the array is treated as an array
        // and not an object in js, in order to maintain order of the attributes.
        $output_attributes =
            '<tr>
                <th colspan="2"><h2>Attributes</h2></th>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="attributes"></div>
                    <script>init_product_attributes({attributes: ' . encode_json(array_values($attributes)) . '})</script>
                </td>
            </tr>';
    }

    $output_groups = '';
	
    // Get product groups.
    $groups = db_items(
        "SELECT
            id,
            name
        FROM product_groups
        ORDER BY name", 'id');

    // If there are groups, then get options and output group area.
    if ($groups) {      
        // We use array_values() below so that the array is treated as an array
        // and not an object in js, in order to maintain order of the groups.
        $output_groups =
            '<tr>
                <th colspan="2"><h2>Product Groups</h2></th>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="groups"></div>
                    <script>init_product_groups({groups: ' . encode_json(array_values($groups)) . '})</script>
                </td>
            </tr>';
    }


    // hide the product form fields by default
    $form_name_row_style = 'display: none';
    $form_label_column_width_row_style = 'display: none';
    $form_quantity_type_row_style = 'display: none';
    $form_designer_row_style = 'display: none';

    $output_custom_product_field_rows = '';

    // If there is at least one active custom product field, then output area for that.
    if (
        (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '')
        || (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '')
    ) {
        $output_custom_product_field_rows .=
            '<tr>
                    <th colspan="2"><h2>Custom Product Fields</h2></th>
            </tr>';

        // If the first custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL != '') {
            $output_custom_product_field_rows .=
                '<tr>
                    <td>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_1_LABEL) . ':</td>
                    <td><input type="text" name="custom_field_1" size="80" maxlength="255" /></td>
                </tr>';
        }

        // If the second custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL != '') {
            $output_custom_product_field_rows .=
                '<tr>
                    <td>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_2_LABEL) . ':</td>
                    <td><input type="text" name="custom_field_2" size="80" maxlength="255" /></td>
                </tr>';
        }

        // If the third custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL != '') {
            $output_custom_product_field_rows .=
                '<tr>
                    <td>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_3_LABEL) . ':</td>
                    <td><input type="text" name="custom_field_3" size="80" maxlength="255" /></td>
                </tr>';
        }

        // If the fourth custom product field is active, then output row for it.
        if (ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL != '') {
            $output_custom_product_field_rows .=
                '<tr>
                    <td>' . h(ECOMMERCE_CUSTOM_PRODUCT_FIELD_4_LABEL) . ':</td>
                    <td><input type="text" name="custom_field_4" size="80" maxlength="255" /></td>
                </tr>';
        }
    }
    $gpc_url ='';
    if(language_ruler() === 'en'){
       $gpc_url ='https://www.kodpen.com/blog-en/online-google-product-category-table';
    }elseif(language_ruler() === 'tr'){
        $gpc_url ='https://www.kodpen.com/blog/evrimi-i-google-r-n-kategori-tablosu';
    }

print
    output_header() . '
    <div id="subnav">
        <h1>[new product]</h1>
    </div>
    <div id="button_bar">

        <a id="taxonomybtn" href="'. $gpc_url.'" target="_blank">GPC Table</a>
    </div>
    <div id="content">
        
        <a href="#" id="help_link">Help</a>
        <h1>Create Product</h1>
        <div class="subheading">Create products to offer merchandise, memberships, downloads, recurring services, donations, or account payments.</div>' .
        get_wysiwyg_editor_code(array('order_receipt_message', 'full_description', 'details', 'out_of_stock_message')) . '
        <form name="form" action="add_product.php" method="post" onsubmit="prepare_selects(new Array(\'allowed_zones\'))" class="product_form">
            ' . get_token_field() . '
            <table class="field">
                <tr>
                        <th colspan="2"><h2>New Product Information</h2></th>
                </tr>
                <tr>
                    <td>Product ID / SKU:</td>
                    <td><input type="text" name="name" /></td>
                </tr>
                <tr>
                    <td><label for="enabled">Enable:</label></td>
                    <td><input type="checkbox" id="enabled" name="enabled" value="1" checked="checked" class="checkbox"></td>
                </tr>
                <tr>
                    <td>Unit Price (' . BASE_CURRENCY_SYMBOL . '):</td>
                    <td><input type="text" name="price" size="5" /></td>
                </tr>
                <tr>
                        <th colspan="2"><h2>Product Options</h2></th>
                </tr>
                <tr>
                    <td><label for="recurring">Recurring Payment:</label></td>
                    <td><input type="checkbox" name="recurring" id="recurring" value="1" class="checkbox" onclick="show_or_hide_recurring()" /></td>
                </tr>
                <tr id="recurring_schedule_editable_by_customer_row" style="display: none">
                    <td style="padding-left: 2em"><label for="recurring_schedule_editable_by_customer">Allow customer to set schedule:</label></td>
                    <td><input type="checkbox" name="recurring_schedule_editable_by_customer" id="recurring_schedule_editable_by_customer" value="1" class="checkbox" onclick="if (this.checked == true) {document.getElementById(\'recurring_schedule_editable_by_customer_message\').style.display = \'\';} else {document.getElementById(\'recurring_schedule_editable_by_customer_message\').style.display = \'none\';}" /><span id="recurring_schedule_editable_by_customer_message" style="display: none"> (you may select default values for the schedule below)</span></td>
                </tr>
                ' . $output_start_row . '
                <tr id="number_of_payments_row" style="display: none">
                    <td style="padding-left: 2em">Number of Payments:</td>
                    <td><input type="text" name="number_of_payments" value="" size="7" maxlength="7" />' . get_number_of_payments_message() . '</td>
                </tr>
                <tr id="payment_period_row" style="display: none">
                    <td style="padding-left: 2em">Payment Period:</td>
                    <td><select name="payment_period">' .  select_payment_period('Monthly') . '</select></td>
                </tr>
                ' . $output_recurring_profile_disabled_rows . '
                ' . $output_sage_group_id_row . '
                <tr>
                    <td>Taxable:</td>
                    <td><input type="checkbox" name="taxable" value="1"' . $tax_checked . ' class="checkbox" /></td>
                </tr>
                <tr style="' . $shippable_row_style . '">
                    <td><label for="shippable">Shippable:</label></td>
                    <td><input type="checkbox" name="shippable" id="shippable" value="1"' . $shippable_checked . ' class="checkbox" onclick="show_or_hide_shippable()"></td>
                </tr>
                <tr id="weight_row" style="' . $weight_row_style . '">
                    <td colspan="2" style="padding-left: 2em">
                        <table style="width: 480px" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="padding-left: 2em"><label for="weight">Weight Type:</label></td>
                                <td><input type="radio" class="radio" value="Weight type pounds" id="weight_type_pounds" name="weight_type" checked="checked" /><label for="weight_type_pounds">Pounds</label><br/>
                                <input type="radio" class="radio" value="Weight type kg" id="weight_type_kg" name="weight_type" /><label for="weight_type_kg">Kg</label></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 2em"><label for="weight">Weight:</label></td>
                                <td id="weight_pound">
                                    <input
                                        type="number"
                                        step="any"
                                        id="weight"
                                        name="weight"
                                        style="width: 90px"
                                    />&nbsp;pounds
                                </td>
                                <td id="weight_kg" style="display:none">
                                    <input
                                        type="number"
                                        step="any"
                                        id="weightkg"
                                        name="weightkg"
                                        style="width: 90px"
                                    />&nbsp;kg
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr id="primary_weight_points_row" style="' . $primary_weight_points_row_style . '">
                    <td style="padding-left: 2em">Primary Weight Points:</td>
                    <td><input type="text" name="primary_weight_points" size="13" /></td>
                </tr>
                <tr id="secondary_weight_points_row" style="' . $secondary_weight_points_row_style . '">
                    <td style="padding-left: 2em">Secondary Weight Points:</td>
                    <td><input type="text" name="secondary_weight_points" size="4" /></td>
                </tr>
                <tr id="dimensions_row" style="' . $dimensions_row_style . '">
                    <td colspan="2" style="padding-left: 2em">
                        <table style="width: 1000px" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="padding-left: 2em"><label for="length">Length Type:</label></td>
                                <td><input type="radio" class="radio" value="Length type Inches" id="length_type_inches" name="length_type" checked="checked" /><label for="length_type_inches">Inches</label><br/>
                                <input type="radio" class="radio" value="Length type Cm" id="length_type_cm" name="length_type" /><label for="length_type_cm">Cm</label></td>
                            </tr>
                            <tr>
                                <td style="padding-left: 2em"><label for="length">Dimensions:</label></td>
                                <td id="length_inches">
                                    <label for="length">L:</label>
                                    <input
                                        type="number"
                                        step="any"
                                        id="length"
                                        name="length" 
                                        placeholder="Length"
                                        style="width: 90px"> &nbsp;

                                    <label for="width">W:</label>

                                    <input
                                        type="number"
                                        step="any"
                                        id="width"
                                        name="width"
                                        placeholder="Width"
                                        style="width: 90px"> &nbsp;

                                    <label for="height">H:</label>

                                    <input
                                        type="number"
                                        step="any"
                                        id="height"
                                        name="height"
                                        placeholder="Height"
                                        style="width: 90px"/> 
                                    &nbsp;inches
                                </td>

                                <td id="length_cm" style="display:none">
                                    <label for="length">L:</label>
                                    <input
                                        type="number"
                                        step="any"
                                        id="lengthcm"
                                        name="lengthcm" 
                                        placeholder="Length"
                                        style="width: 90px"> &nbsp;

                                    <label for="width">W:</label>

                                    <input
                                        type="number"
                                        step="any"
                                        id="widthcm"
                                        name="widthcm"
                                        placeholder="Width"
                                        style="width: 90px"/> &nbsp;

                                    <label for="height">H:</label>

                                    <input
                                        type="number"
                                        step="any"
                                        id="heightcm"
                                        name="heightcm"
                                        placeholder="Height"
                                        style="width: 90px"> 
                                    &nbsp;cm
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr id="container_required_row" style="' . $container_required_row_style . '">
                    <td style="padding-left: 2em">
                        <label for="container_required">Container Required:</label>
                    </td>
                    <td>
                        <input
                            type="checkbox"
                            id="container_required"
                            name="container_required"
                            value="1"
                            class="checkbox">
                    </td>
                </tr>
                <tr id="preparation_time_row" style="' . $preparation_time_row_style . '">
                    <td style="padding-left: 2em">Preparation Time:</td>
                    <td><input type="text" name="preparation_time" size="3" /> day(s) from order date.</td>
                </tr>
                <tr id="free_shipping_row" style="' . $free_shipping_row_style . '">
                    <td style="padding-left: 2em">Free Shipping:</td>
                    <td><input type="checkbox" id="free_shipping" name="free_shipping" value="1" class="checkbox" onclick="show_or_hide_free_shipping()" /></td>
                </tr>
                <tr id="extra_shipping_cost_row" style="' . $extra_shipping_cost_row_style . '">
                    <td style="padding-left: 2em">Extra Shipping Cost (' . BASE_CURRENCY_SYMBOL . '):</td>
                    <td><input type="text" name="extra_shipping_cost" size="5" maxlength="9" /></td>
                </tr>
                <tr id="allowed_zones_row" style="' . $allowed_zones_row_style . '">
                    <td colspan="2" style="padding-left: 2em">
                        <table style="width: 500px" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width: 50%">
                                    <div style="margin-bottom: 3px">Allowed Zones</div>
                                    <input type="hidden" id="allowed_zones_hidden" name="allowed_zones_hidden" value="">
                                    <select id="allowed_zones" multiple="multiple" size="10" style="width: 95%">' . $output_allowed_zones . '</select>
                                </td>
                                <td style="text-align: center; vertical-align: middle; padding-left: 15px; padding-right: 15px">
                                    <input type="button" value="&gt;&gt;" onclick="move_options(\'allowed_zones\', \'disallowed_zones\', \'right\');" /><br />
                                    <br />
                                    <input type="button" value="&lt;&lt;" onclick="move_options(\'allowed_zones\', \'disallowed_zones\', \'left\');" /><br />
                                </td>
                                <td style="width: 50%">
                                    <div style="margin-bottom: 3px">Disallowed Zones</div>
                                    <select id="disallowed_zones" multiple="multiple" size="10" style="width: 95%"></select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                ' . $output_commissionable . '
                <tr>
                    <th colspan="2"><h2>Catalog, Order Form & Cart Page Display Options</h2></th>
                </tr>
                <tr>
                    <td>Short Description:</td>
                    <td><input type="text" name="short_description" maxlength="100" size="60" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top">Full Description:</td>
                    <td><textarea id="full_description" name="full_description" style="width: 600px; height: 200px"></textarea></td>
                </tr>
                <tr>
                    <td style="vertical-align: top">Details:</td>
                    <td><textarea id="details" name="details" style="width: 600px; height: 200px"></textarea></td>
                </tr>  
                    <div id="code_tips_dialog" style="display:none;" title="Code Area usage tips">                      
                            <strong>Code System Fields</strong>
                            <div class="scrollable fields" style="height: 100px; padding: 5px;max-width:100%;">
                                ^^image_loop_start^^<br>^^image_alt^^<br>^^image_url^^<br>^^image_loop_end^^
                            </div>
                        <strong>Hints</strong>
                            <ul style="margin-top: 0px; margin-left: 20px">
                                <li>Copy fields from here and paste in the layout below.</li>
                                <li>example 1 - this is best and newest way to use code. add code and select multiple product image, its auto insert images in code.
                                <textarea readonly rows="10" style="width:85%;border:none !important;box-shadow:none !important;    background-color: #0000001f;height:auto;" ><style>img.image-url {display: none !important;}</style>
<div class="image-slider slider-thumb-controls controls-inside">
<ul class="slides">
   ^^image_loop_start^^
<li>
    <img alt="^^image_alt^^" src="^^image_url^^" style="visibility: hidden"/>
</li>
^^image_loop_end^^
</ul> 
</div></textarea>
                            </li>
                            <li>or example 2 - this methot is oldest way to use code. you have to select one product image and insert image names with manuel.
                                <textarea readonly rows="17" style="width:85%;border:none !important;box-shadow:none !important;    background-color: #0000001f;height:auto;" ><style>img.image-url {display: none !important;}</style>
<div class="image-slider slider-thumb-controls controls-inside">
<ul class="slides">
    <li>
        <img alt="Image" src="image1.jpg" style="visibility: hidden"/>
    </li>
    <li>
        <img alt="Image" src="image2.jpg" style="visibility: hidden"/>
    </li>
    <li>
        <img alt="Image" src="image3.jpg" style="visibility: hidden"/>
    </li>
    <li>
        <img alt="Image" src="image4.jpg" style="visibility: hidden"/>
    </li>
</ul> 
</div></textarea>
</li>
                            <li>
                               for using code system fields as example 1 you must insert all tag: ^^image_loop_start^^	^^image_url^^	^^image_loop_end^^. else its not work!. It is basicly loop all codes between the tags: ^^image_loop_start^^	^^image_loop_end^^, replace ^^image_url^^ and ^^image_url^^ tag to product image selected. example 1 and example 2 methods is perfect compatible with your theme if use theme slider or carausel html codes. Choose your own choice.
                            </li>
                           
                        </ul>
                    </div>
                <tr>
                    <td style="vertical-align: top">Code:</td>
                    <td>
                        <a id="show_code_tips_dialog" class="button">Show Code Tips</a>
                        <textarea id="code" name="code" style="width: 500px; height: 100px">' . $config_code_output . '</textarea>
                        ' . get_codemirror_includes() . '
                        ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                    </td>
                </tr>
                <tr>
                    <td>Search Keywords:</td>
                    <td><input type="text" name="keywords" maxlength="255" size="100" /></td>
                </tr>
                <tr>
                    <td>Select Image: </td>
                    <td>

                        <ul class="sortable-list img-list">
                            <li class="add_new_item no-drag" >
                                
                                <a id="show_img_selector_dialog" class="button" >Add Image</a>
                            </li>
                        </ul>
                        <div id="img_selector_dialog" style="display:none;" class="select_image" title="Select Product Image(s)">   
                            <div class="images">
                                <input type="file" id="file" style="visibility:hidden;width:0;height:0;line-height:0;"  accept="image/*"/>
                                <div class="image image_selector_item upload" id="imageupload" style="width: 100%;display: flex;justify-content: center;align-items: center;"><p>Upload</p></div>
                                
                                ' . select_image_options() . '  
                            </div>     
                        </div>

                    </td>
                </tr>
                <tr>
                    <td>Selection Type:</td>
                    <td><select name="selection_type">' .  select_selection_type() . '</select></td>
                </tr>
                <tr>
                    <td>Default Quantity:</td>
                    <td><input type="text" name="default_quantity" value="1" size="3" maxlength="9" /></td>
                </tr>
                <tr>
                    <td>Minimum Quantity:</td>
                    <td><input type="text" name="minimum_quantity" value="" size="3" maxlength="9" /></td>
                </tr>
                <tr>
                    <td>Maximum Quantity:</td>
                    <td><input type="text" name="maximum_quantity" value="" size="3" maxlength="9" /></td>
                </tr>
                ' . $output_attributes . '
                <tr>
                    <th colspan="2"><h2>Search Engine Optimization</h2></th>
                </tr>
                <tr>
                    <td>Catalog Name:</td>
                    <td><span style="white-space: nowrap">' . URL_SCHEME . HOSTNAME . OUTPUT_PATH . 'example-catalog/<input type="text" name="address_name" size="60" maxlength="255" /></span></td>
                </tr>
                <tr>
                    <td>
                        <label for="title">Web Browser Title:</label>
                    </td>
                    <td>
                        <input id="title" name="title" type="text" maxlength="255" style="width: 98%">
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top">
                        <label for="meta_description">Web Browser Description:</label>
                    </td>
                    <td>
                        <textarea id="meta_description" name="meta_description" maxlength="255" rows="3" style="width: 99%"></textarea>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top">
                        <label for="meta_keywords">Web Browser Keywords:</label>
                    </td>
                    <td>
                        <textarea id="meta_keywords" name="meta_keywords" rows="3" style="width: 99%"></textarea>
                    </td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Inventory</h2></th>
                </tr>
                <tr>
                    <td><label for="inventory">Track Inventory:</label></td>
                    <td><input type="checkbox" name="inventory" id="inventory" value="1" class="checkbox" onclick="show_or_hide_inventory()" /></td>
                </tr>
                <tr id="inventory_quantity_row" style="display: none">
                    <td style="padding-left: 2em">Inventory Quantity:</td>
                    <td><input type="text" name="inventory_quantity" value="" size="6" maxlength="9" /></td>
                </tr>
                <tr id="backorder_row" style="display: none">
                    <td style="padding-left: 2em"><label for="backorder">Accept Backorders:</label></td>
                    <td><input type="checkbox" name="backorder" id="backorder" value="1" class="checkbox" /></td>
                </tr>
                <tr id="out_of_stock_message_row" style="display: none">
                    <td style="padding-left: 2em; vertical-align: top">Out of Stock Message:</td>
                    <td><textarea id="out_of_stock_message" name="out_of_stock_message" style="width: 600px; height: 200px">' . h('<p>Sorry, this item is not currently available.</p>') . '</textarea></td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Order Checkout Options</h2></th>
                </tr>
                <tr>
                    <td>Requires Product:</td>
                    <td><select name="required_product"><option value=""></option>' .  select_product() . '</select></td>
                </tr>
                <tr>
                    <td>Enable Product Form:</td>
                    <td>
                        <input type="checkbox" onclick="show_or_hide_form()" class="checkbox" value="1" id="product_form" name="product_form" /><span id="form_notice" style="display: none; padding-left: 1em">(when ready, click "Create &amp; Continue" at the bottom of this screen to create the Product Form.)</span>
                        <input type="hidden" id="current_form_state" name="current_form_state" value="" />
                    </td>
                </tr>
                <tr id="form_name_row" style="' . $form_name_row_style . '">
                    <td style="padding-left: 2em">Form Title for Display:</td>
                    <td><input type="text" maxlength="100" size="30" value="" name="form_name" /></td>
                </tr>
                <tr id="form_label_column_width_row" style="' . $form_label_column_width_row_style . '">
                    <td style="padding-left: 2em">Label Column Width:</td>
                    <td><input type="text" maxlength="3" size="3" value="" name="form_label_column_width" /> % (leave blank for auto)</td>
                </tr>
                <tr id="form_quantity_type_row" style="' . $form_quantity_type_row_style . '">
                    <td style="padding-left: 2em; vertical-align: top">Quantity Type:</td>
                    <td><input type="radio" class="radio" value="One Form per Quantity" id="form_quantity_type_one_form_per_quantity" name="form_quantity_type" checked="checked" /><label for="form_quantity_type_one_form_per_quantity"> One form per quantity</label><br />
                    <input type="radio" class="radio" value="One Form per Product" id="form_quantity_type_one_form_per_product" name="form_quantity_type" /><label for="form_quantity_type_one_form_per_product"> One form per product</label></td>
                </tr>
                <tr id="form_designer_row" style="' . $form_designer_row_style . '">
                    <td style="padding-left: 2em" colspan="2">Please create and then edit the product to access the form designer.</td>
                </tr>
                <tr>
                    <th colspan="2"><h2>Order Complete Options</h2></th>
                </tr>
                <tr>
                    <td style="vertical-align: top">Order Receipt Page Message:</td>
                    <td><textarea id="order_receipt_message" name="order_receipt_message" style="width: 600px; height: 200px"></textarea></td>
                </tr>
                <tr>
                    <td>Order Receipt BCC E-mail Address:</td>
                    <td><input type="email" name="order_receipt_bcc_email_address" size="40" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>E-mail Additional Page to Customer:</td>
                    <td><select name="email_page"><option value=""></option>' .  select_page() . '</select></td>
                </tr>
                <tr>
                    <td style="padding-left: 2em">BCC E-mail Address:</td>
                    <td><input type="email" name="email_bcc" size="40" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>Add to Contact Group:</td>
                    <td><select name="contact_group_id"><option value=""></option>' . select_contact_group(0, $user) . '</select></td>
                </tr>
                <tr>
                    <td>Add Days to Customer\'s Membership:</td>
                    <td><input type="text" name="membership_renewal" value="0" size="3" />&nbsp;&nbsp;day(s) (0 for none)</td>
                </tr>
                <tr>
                    <td><label for="grant_private_access">Grant Private Access to Customer:</label></td>
                    <td><input type="checkbox" name="grant_private_access" id="grant_private_access" value="1" class="checkbox" onclick="show_or_hide_grant_private_access()" /></td>
                </tr>
                <tr id="private_folder_row" style="display: none">
                    <td style="padding-left: 2em">Set &quot;View&quot; Access to Folder:</td>
                    <td><select name="private_folder"><option value=""></option>' .  select_folder(0, 0, 0, 0, array(), array(), 'private') . '</select></td>
                </tr>
                <tr id="private_days_row" style="display: none">
                    <td style="padding-left: 2em">Length:</td>
                    <td><input type="text" name="private_days" value="" size="3" /> day(s) (leave blank for no expiration)</td>
                </tr>
                <tr id="send_to_page_row" style="display: none">
                    <td style="padding-left: 2em">Set Customer\'s Start Page to:</td>
                    <td><select name="send_to_page"><option value=""></option>' .  select_page() . '</select></td>
                </tr>
                <tr>
                    <td>Reward Points:</td>
                    <td><input type="text" name="reward_points" value="" size="5" maxlength="9" /></td>
                </tr>
                <tr>
                    <td><label for="gift_card">Email Gift Card:</label></td>
                    <td><input type="checkbox" name="gift_card" id="gift_card" value="1" class="checkbox" onclick="toggle_product_gift_card()" /></td>
                </tr>
                <tr id="gift_card_email_subject_row" style="display: none">
                    <td style="padding-left: 2em">Subject:</td>
                    <td><input name="gift_card_email_subject" value="" type="text" size="80" maxlength="255" /></td>
                </tr>
                <tr id="gift_card_email_format_row" style="display: none">
                    <td style="padding-left: 2em">Format:</td>
                    <td><input type="radio" id="gift_card_email_format_plain_text" name="gift_card_email_format" value="plain_text" class="radio" checked="checked" onclick="toggle_product_gift_card_email_format()" /><label for="gift_card_email_format_plain_text">Plain Text</label> &nbsp;<input type="radio" id="gift_card_email_format_html" name="gift_card_email_format" value="html" class="radio" onclick="toggle_product_gift_card_email_format()" /><label for="gift_card_email_format_html">HTML</label></td>
                </tr>
                <tr id="gift_card_email_body_row" style="display: none">
                    <td style="padding-left: 2em; vertical-align: top">Body:</td>
                    <td><textarea name="gift_card_email_body" rows="10" cols="70"></textarea></td>
                </tr>
                <tr id="gift_card_email_page_id_row" style="display: none">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select name="gift_card_email_page_id"><option value=""></option>' . select_page() . '</select></td>
                </tr>
                <tr>
                    <td><label for="submit_form">Create/Update Submitted Form:</label></td>
                    <td><input type="checkbox" name="submit_form" id="submit_form" value="1" class="checkbox" onclick="toggle_product_submit_form()" /></td>
                </tr>
                <tr id="submit_form_custom_form_page_id_row" style="display: none">
                    <td style="padding-left: 2em">Custom Form:</td>
                    <td>
                        <select id="submit_form_custom_form_page_id" name="submit_form_custom_form_page_id" onchange="product_submit_form_update_custom_form_fields()"><option value=""></option>' .  select_page('', 'custom form') . '</select>
                        <script>product_submit_form_update_custom_form_fields();</script>
                    </td>
                </tr>
                <tr id="submit_form_create_row" style="display: none">
                    <td style="padding-left: 2em"><label for="submit_form_create">Create Submitted Form:</label></td>
                    <td><input type="checkbox" name="submit_form_create" id="submit_form_create" value="1" class="checkbox" onclick="toggle_product_submit_form_create()" /></td>
                </tr>
                <tr id="submit_form_create_fields_row" style="display: none">
                    <td>&nbsp;</td>
                    <td>
                        <div style="margin-bottom: 1em">
                            Please configure the fields below that should be set when a Submitted Form is created.
                        </div>
                        <table id="submit_form_create_field_table" class="chart" style="margin-bottom: 1.25em; width: auto">
                            <tbody>
                            </tbody>
                        </table>
                        <div style="margin-bottom: 1em"><a href="javascript:void(0)" onclick="product_submit_form_add_field({action: \'create\'})" class="button">Add Field</a></div>
                        <input type="hidden" id="last_submit_form_create_field_number" name="last_submit_form_create_field_number" value="0" />
                        <script>
                            var last_submit_form_field_number = [];                            
                            last_submit_form_field_number["create"] = 0;
                        </script>
                    </td>
                </tr>
                <tr id="submit_form_update_row" style="display: none">
                    <td style="padding-left: 2em"><label for="submit_form_update">Update Submitted Form:</label></td>
                    <td><input type="checkbox" name="submit_form_update" id="submit_form_update" value="1" class="checkbox" onclick="toggle_product_submit_form_update()" /></td>
                </tr>
                <tr id="submit_form_update_fields_row" style="display: none">
                    <td style="padding-left: 4em">&nbsp;</td>
                    <td>
                        <div style="margin-bottom: 1em">
                            Please configure the fields below that should be set when a Submitted Form is updated.
                        </div>
                        <table id="submit_form_update_field_table" class="chart" style="margin-bottom: 1.25em; width: auto">
                            <tbody>
                            </tbody>
                        </table>
                        <div style="margin-bottom: 3em"><a href="javascript:void(0)" onclick="product_submit_form_add_field({action: \'update\'})" class="button">Add Field</a></div>
                        <input type="hidden" id="last_submit_form_update_field_number" name="last_submit_form_update_field_number" value="0" />
                        <script>
                            last_submit_form_field_number["update"] = 0;
                        </script>
                        <div style="margin-bottom: 1em">
                            Please specify which Submitted Form should be updated.
                        </div>
                        <div style="margin-bottom: 1em">
                            Where&nbsp;
                            <select id="submit_form_update_where_field" name="submit_form_update_where_field"></select>&nbsp;
                            is equal to &nbsp;
                            <input type="text" name="submit_form_update_where_value" value="' . h($submit_form_update_where_value) . '" size="40" maxlength="255">
                            <script>init_product_submit_form_update_where("' . escape_javascript($submit_form_update_where_field) . '")</script>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="add_comment">Add Comment:</label></td>
                    <td><input type="checkbox" name="add_comment" id="add_comment" value="1" class="checkbox" onclick="toggle_product_add_comment()" /></td>
                </tr>
                <tr id="add_comment_page_id_row" style="display: none">
                    <td style="padding-left: 2em">Page:</td>
                    <td><select id="add_comment_page_id" name="add_comment_page_id"><option value=""></option>' .  select_page() . '</select></td>
                </tr>
                <tr id="add_comment_message_row" style="display: none">
                    <td style="padding-left: 2em; vertical-align: top">Comment:</td>
                    <td><textarea name="add_comment_message" style="width: 400px; height: 100px"></textarea></td>
                </tr>
                <tr id="add_comment_name_row" style="display: none">
                    <td style="padding-left: 2em">Added by:</td>
                    <td><input type="text" name="add_comment_name" value="" size="40" /></td>
                </tr>
                <tr id="add_comment_only_for_submit_form_update_row" style="display: none">
                    <td style="padding-left: 2em"><label for="add_comment_only_for_submit_form_update">Only add Comment if<br />Submitted Form was updated:</label></td>
                    <td><input type="checkbox" id="add_comment_only_for_submit_form_update" name="add_comment_only_for_submit_form_update" value="1" class="checkbox" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top">Form/Comment Quantity Type:</td>
                    <td>
                        <label>
                            <input type="radio" class="radio" value="One Form per Quantity" name="submit_form_quantity_type" checked="checked">
                            One form/comment per quantity
                        </label><br>

                        <label>
                            <input type="radio" class="radio" value="One Form per Product" name="submit_form_quantity_type">
                            One form/comment per product
                        </label>
                    </td>
                </tr>
                ' . $output_custom_product_field_rows . '
                <tr>
                    <th colspan="2"><h2>Product Notes for Order Exporting</h2></th>
                </tr>
                <tr>
                    <td style="vertical-align: top">Notes:</td>
                    <td><textarea id="notes" name="notes" style="width: 225px; height: 70px"></textarea></td>
                </tr>
                <tr>
                    <th colspan="2"><h2>RSS Feed</h2></th>
                </tr>
                <tr>
                    <td>Google Product Category:</td>
                    <td><input type="text" name="google_product_category" size="100" maxlength="255" /></td>
                </tr>
                <tr>
                    <td>GTIN:</td>
                    <td><input type="text" name="gtin" size="30" maxlength="50" /> (e.g. UPC)</td>
                </tr>
                <tr>
                    <td>Brand:</td>
                    <td><input type="text" name="brand" size="30" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>MPN:</td>
                    <td><input type="text" name="mpn" size="30" maxlength="50" /> (i.e. manufacturer product number)</td>
                </tr>
                ' . $output_groups . '
            </table>
            <input type="hidden" id="submitted_button_field" name="submitted_button_field" value="submit" />
            <div class="buttons">
                <input type="submit" id="create_button" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit">
            </div>
        </form>
    </div>
    <script>




    

    
    $(document).ready(function() {
        
        var width_o_screen = $(window).width() - 100;
        var height_o_screen = $(window).height() - 100;


        var img_selector_dialog = $( "#img_selector_dialog" );
        img_selector_dialog.dialog({
            autoOpen: false,
            modal: true,
            height: height_o_screen,
            width: width_o_screen,
            beforeClose: function( event, ui ) {
                $("body").attr("style","overflow:auto;");
                
            }
        });
        var tips_dialog = $( "#code_tips_dialog" );
        tips_dialog.dialog({
             autoOpen: false,
             height: height_o_screen,
             width: width_o_screen,
             modal: true,
        });
        $( "#show_code_tips_dialog" ).on( "click", function() {
          tips_dialog.dialog( "open" );
        });
        
        $(".sortable-list").sortable({
            items: "> li:not(.add_new_item)",
            placeholder: ".list-placeholder",
            connectWith: "ul",
            cancel: ".no-drag"
        });
        $(".sortable-list li:not(.add_new_item)").append("<div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div>");
        $(".image_selector_item.upload").click(function(){
            var file = $("#file");
            file.click();
        });
        $("#file").change(function(){
            readURL(this);
        });
        $items = $(".image_selector_item:not(.upload):not(.uploading)");
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.fileName = input.files[0].name;
                reader.fileSize = (input.files[0].size / (1024*1024)).toFixed(2) + " MB ";
                reader.fileExtention = input.files[0].name.split(".").pop().toLowerCase();
                reader.onload = function (e) {
                var data = e.target.result;
                var name = input.files[0].name;
                $(".image_selector_item.upload").after("<div class=\u0022image image_selector_item uploading\u0022 ><div class=\u0022thumbnail\u0022><img  src=\u0022' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/loading.gif\u0022 width=\u0022100\u0022 height=\u0022100\u0022 alt=\u0022\u0022 style=\u0022display: block; overflow: hidden;\u0022></div><div class=\u0022image_content\u0022><strong class=\u0022image_name\u0022>Uploading...</strong></div></div>");
                // Use AJAX to upload image.
                $.ajax({
                    contentType: "application/json",
                    url: "api.php",
                    data: JSON.stringify({
                        action: "upload_file",
                        token:software_token ,
                        data: data,
                        name: name,
                        contentType: false,
                        processData: false,
                    }),
                    type: "POST",
                    success: function(response) {
                        // Check the values in console
                        $status = response.status;
                        
                        if($status == "success"){
                            console.log(response.message);
                            $(".image_selector_item.upload").after("<div class=\u0022image image_selector_item\u0022 ><div class=\u0022thumbnail\u0022><img  src=\u0022' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + response.name + "\u0022 width=\u0022100\u0022 height=\u0022100\u0022 alt=\u0022\u0022 style=\u0022display: block; overflow: hidden;\u0022></div><div class=\u0022image_content\u0022><strong class=\u0022image_name\u0022>" + response.name + "</strong><br><span>Size: " + response.filesize + "</span></div></div>");
                            $(".image_selector_item.uploading").remove();
                            $items = $(".image_selector_item:not(.upload):not(.uploading)");
                            $items.on("click",function(){
                                $img_name = $(this).find(".image_name").text();
                                $( "#img_selector_dialog" ).dialog( "close" );
                                if (!$img_name) {
                                    return false;
                                }
                                $img_url = "' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + $img_name;
                                $("<li><img src=\u0022" + $img_url + "\u0022><div class=\u0022 list-item-title \u0022>" + $img_name + "</div><div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div><input type=\u0022hidden\u0022 name=\u0022selected_images[]\u0022 id=\u0022selected_images\u0022 value=\u0022" + $img_name + "\u0022 /></li>").insertBefore(".add_new_item ");
                                $img_name = "";
                                $img_url = "";
                            });
                        }
                    }
                });
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $( "#show_img_selector_dialog" ).on( "click", function() {
            $("body").attr("style","overflow:hidden;");
            img_selector_dialog.dialog( "open" );
        });
        $items.on("click",function(){
            $img_name = $(this).find(".image_name").text();
            $( "#img_selector_dialog" ).dialog( "close" );
            if (!$img_name) {
                return false;
            }
            $img_url = "' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . '" + $img_name;
            $("<li><img src=\u0022" + $img_url + "\u0022><div class=\u0022 list-item-title \u0022>" + $img_name + "</div><div class=\u0022list-item-remove no-drag\u0022 onclick=\u0022$(this)[0].parentNode.remove();\u0022>x</div><input type=\u0022hidden\u0022 name=\u0022selected_images[]\u0022 id=\u0022selected_images\u0022 value=\u0022" + $img_name + "\u0022 /></li>").insertBefore(".add_new_item ");
            $img_name = "";
            $img_url = "";
        });
        
        $("input[name=weight_type]").click(function() {    
            if($("#weight_type_kg").is(":checked")) {  
                $("#weight_pound").attr("style","display:none")
                $("#weight_kg").attr("style","")
            }
            if($("#weight_type_pounds").is(":checked")) {  
                $("#weight_kg").attr("style","display:none")
                $("#weight_pound").attr("style","")
            }
        });
        var wp = $("#weight");
        wp.change(function(){
            var wp = $("#weight");
            var wkg = $("#weightkg");
            var wkgval = wkg.val();
            var wpval = wp.val();
            wkg.val(wpval*0.45359237); 
        }).change();
        var wkg = $("#weightkg");
        wkg.change(function(){
            var wkg = $("#weightkg");
            var wkgval = wkg.val();
            var wp = $("#weight");
            var wpval = wp.val();
            wp.val(wkgval*2.20462262); 
        }).change();
        $("input[name=length_type]").click(function() {    
            if($("#length_type_inches").is(":checked")) {  
                $("#length_cm").attr("style","display:none")
                $("#length_inches").attr("style","")
            }
            if($("#length_type_cm").is(":checked")) {  
                $("#length_inches").attr("style","display:none")
                $("#length_cm").attr("style","")
            }
        });
        var lil = $("#length");
        var liw = $("#width");
        var lih = $("#height");
        var lilcm = $("#lengthcm");
        var liwcm = $("#widthcm");
        var lihcm = $("#heightcm");
        var lilval = $("#length").val();
        var liwval = $("#width").val();
        var lihval = $("#height").val();
        var lilcmval = $("#lengthcm").val();
        var liwcmval = $("#widthcm").val();
        var lihcmval = $("#heightcm").val();
        lil.change(function(){  
            var lilcm = $("#lengthcm");
            var lilval = $("#length").val();
            lilcm.val(lilval*2.54); 
        }).change();
        liw.change(function(){  
            var liwcm = $("#widthcm");
            var liwval = $("#width").val();
            liwcm.val(liwval*2.54); 
        }).change();
        lih.change(function(){  
            var lihcm = $("#heightcm");
            var lihval = $("#height").val();
            lihcm.val(lihval*2.54); 
        }).change();
        lilcm.change(function(){
            var lil = $("#length");
            var lilcmval = $("#lengthcm").val();
            lil.val(lilcmval*0.39370078740158); 
        }).change();
        liwcm.change(function(){
            var liw = $("#width");
            var liwcmval = $("#widthcm").val();
            liw.val(liwcmval*0.39370078740158); 
        }).change();
        lihcm.change(function(){
            var lih = $("#height");
            var lihcmval = $("#heightcm").val();
            lih.val(lihcmval*0.39370078740158); 
        }).change();
    });
    </script>
    
    
    ' .
    output_footer();

} else {
    
    validate_token_field();
    
    // if user selected a contact group and user does not have access to contact group
    if ($_POST['contact_group_id'] && (validate_contact_group_access($user, $_POST['contact_group_id']) == false)) {
        log_activity("access denied because user does not have access to contact group that user selected for product", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    // remove commas from price
    $price = str_replace(',', '', $_POST['price']);
    
    // convert price from dollars to cents
    $price = $price * 100;
    
    // remove commas from extra shipping cost
    $extra_shipping_cost = str_replace(',', '', $_POST['extra_shipping_cost']);
    
    // convert extra shipping cost from dollars to cents
    $extra_shipping_cost = $extra_shipping_cost * 100;

    $_POST['order_receipt_bcc_email_address'] = trim($_POST['order_receipt_bcc_email_address']);
    
    // if a order receipt bcc email address was supplied, validate the e-mail address
    if ($_POST['order_receipt_bcc_email_address']) {
        if (validate_email_address($_POST['order_receipt_bcc_email_address']) == FALSE) {
            output_error('The order receipt bcc e-mail address is invalid. <a href="javascript:history.go(-1);">Go back</a>.');
        }
    }

    $_POST['email_bcc'] = trim($_POST['email_bcc']);
    
    // if a bcc e-mail address was supplied, validate the e-mail address
    if ($_POST['email_bcc']) {
        if (validate_email_address($_POST['email_bcc']) == FALSE) {
            output_error('The additional page bcc e-mail address is invalid. <a href="javascript:history.go(-1);">Go back</a>.');
        }
    }
    
    // if the affiliate program is enabled, prepare affiliate program SQL
    if (AFFILIATE_PROGRAM == true) {
        $sql_commissionable_1 =
            "commissionable,
            commission_rate_limit,";
        
        $sql_commissionable_2 =
            "'" . escape($_POST['commissionable']) . "',
            '" . escape($_POST['commission_rate_limit']) . "',";
    }

    // determine if recurring profile disabled fields should be updated
    $sql_recurring_profile_disabled_1 = '';
    $sql_recurring_profile_disabled_2 = '';
    
    // if credit/debit card payment method is enabled and payment gateway is PayPal Payments Pro, then prepare to update recurring profile disabled fields
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == true) && (ECOMMERCE_PAYMENT_GATEWAY == 'PayPal Payments Pro')) {
        $sql_recurring_profile_disabled_1 =
            "recurring_profile_disabled_perform_actions,
            recurring_profile_disabled_expire_membership,
            recurring_profile_disabled_revoke_private_access,
            recurring_profile_disabled_email,
            recurring_profile_disabled_email_subject,
            recurring_profile_disabled_email_page_id,";
        
        $sql_recurring_profile_disabled_2 =
            "'" . escape($_POST['recurring_profile_disabled_perform_actions']) . "',
            '" . escape($_POST['recurring_profile_disabled_expire_membership']) . "',
            '" . escape($_POST['recurring_profile_disabled_revoke_private_access']) . "',
            '" . escape($_POST['recurring_profile_disabled_email']) . "',
            '" . escape($_POST['recurring_profile_disabled_email_subject']) . "',
            '" . escape($_POST['recurring_profile_disabled_email_page_id']) . "',";
    }
    
    // determine if Sage group ID field should be updated
    $sql_sage_group_id_1 = '';
    $sql_sage_group_id_2 = '';
    
    // if credit/debit card payment method is enabled and payment gateway is Sage, then update Sage group ID field
    if ((ECOMMERCE_CREDIT_DEBIT_CARD == TRUE) && (ECOMMERCE_PAYMENT_GATEWAY == 'Sage')) {
        $sql_sage_group_id_1 = "sage_group_id,";
        $sql_sage_group_id_2 = "'" . escape($_POST['sage_group_id']) . "',";
    }


    $selected_images = array();
    foreach ($_POST['selected_images'] as $selected_image ) {
        $selected_images[] = $selected_image ;
    }
    
    $selected_count = 0;
    foreach ($selected_images as $value) {
        $selected_count++;
    }
    if($selected_count >= 1){
        $selected_cover_image = reset($selected_images);
        array_shift($selected_images);
        if($selected_cover_image){
            $sql_imagename = 
            "'" . escape($selected_cover_image) . "',";
        }
    }else{
        $sql_imagename = 
        "'',";
    }

	// Insert db
    $query = "INSERT INTO products (
                name,
                enabled,
                short_description,
                full_description,
                details,
                code,
                keywords,
                image_name,
                price,
                taxable,
                contact_group_id,
                order_receipt_bcc_email_address,
                email_page,
                email_bcc,
                order_receipt_message,
                required_product,
                shippable,
                weight,
                primary_weight_points,
                secondary_weight_points,
                length,
                width,
                height,
                container_required,
                preparation_time,
                free_shipping,
                extra_shipping_cost,
                $sql_commissionable_1
                selection_type,
                default_quantity,
                minimum_quantity,
                maximum_quantity,
                title,
                meta_description,
                meta_keywords,
                inventory,
                inventory_quantity,
                backorder,
                out_of_stock_message,
                recurring,
                recurring_schedule_editable_by_customer,
                start,
                number_of_payments,
                payment_period,
                $sql_recurring_profile_disabled_1
                $sql_sage_group_id_1
                membership_renewal,
                grant_private_access,
                private_folder,
                private_days,
                send_to_page,
                reward_points,
                gift_card,
                gift_card_email_subject,
                gift_card_email_format,
                gift_card_email_body,
                gift_card_email_page_id,
                submit_form,
                submit_form_custom_form_page_id,
                submit_form_quantity_type,
                submit_form_create,
                submit_form_update,
                submit_form_update_where_field,
                submit_form_update_where_value,
                add_comment,
                add_comment_page_id,
                add_comment_message,
                add_comment_name,
                add_comment_only_for_submit_form_update,
                form,
                form_name,
                form_label_column_width,
                form_quantity_type,
                custom_field_1,
                custom_field_2,
                custom_field_3,
                custom_field_4,
                notes,
                google_product_category,
                gtin,
                brand,
                mpn,
                user,
                timestamp)
             VALUES (
                '" . escape($_POST['name']) . "',
                '" . escape($_POST['enabled']) . "',
                '" . escape($_POST['short_description']) . "',
                '" . escape(prepare_rich_text_editor_content_for_input($_POST['full_description'])) . "',
                '" . escape(prepare_rich_text_editor_content_for_input($_POST['details'])) . "',
                '" . escape($_POST['code']) . "',
                '" . escape($_POST['keywords']) . "',
               	$sql_imagename
                '" . escape($price) . "',
                '" . escape($_POST['taxable']) . "',
                '" . escape($_POST['contact_group_id']) . "',
                '" . escape($_POST['order_receipt_bcc_email_address']) . "',
                '" . escape($_POST['email_page']) . "',
                '" . escape($_POST['email_bcc']) . "',
                '" . escape(prepare_rich_text_editor_content_for_input($_POST['order_receipt_message'])) . "',
                '" . escape($_POST['required_product']) . "',
                '" . escape($_POST['shippable']) . "',
                '" . escape($_POST['weight']) . "',
                '" . escape($_POST['primary_weight_points']) . "',
                '" . escape($_POST['secondary_weight_points']) . "',
                '" . e($_POST['length']) . "',
                '" . e($_POST['width']) . "',
                '" . e($_POST['height']) . "',
                '" . e($_POST['container_required']) . "',
                '" . escape($_POST['preparation_time']) . "',
                '" . escape($_POST['free_shipping']) . "',
                '" . escape($extra_shipping_cost) . "',
                $sql_commissionable_2
                '" . escape($_POST['selection_type']) . "',
                '" . escape($_POST['default_quantity']) . "',
                '" . escape($_POST['minimum_quantity']) . "',
                '" . escape($_POST['maximum_quantity']) . "',
                '" . escape($_POST['title']) . "',
                '" . escape($_POST['meta_description']) . "',
                '" . escape($_POST['meta_keywords']) . "',
                '" . escape($_POST['inventory']) . "',
                '" . escape($_POST['inventory_quantity']) . "',
                '" . escape($_POST['backorder']) . "',
                '" . escape(prepare_rich_text_editor_content_for_input($_POST['out_of_stock_message'])) . "',
                '" . escape($_POST['recurring']) . "',
                '" . escape($_POST['recurring_schedule_editable_by_customer']) . "',
                '" . escape($_POST['start']) . "',
                '" . escape($_POST['number_of_payments']) . "',
                '" . escape($_POST['payment_period']) . "',
                $sql_recurring_profile_disabled_2
                $sql_sage_group_id_2
                '" . escape($_POST['membership_renewal']) . "',
                '" . escape($_POST['grant_private_access']) . "',
                '" . escape($_POST['private_folder']) . "',
                '" . escape($_POST['private_days']) . "',
                '" . escape($_POST['send_to_page']) . "',
                '" . escape($_POST['reward_points']) . "',
                '" . escape($_POST['gift_card']) . "',
                '" . escape($_POST['gift_card_email_subject']) . "',
                '" . escape($_POST['gift_card_email_format']) . "',
                '" . escape($_POST['gift_card_email_body']) . "',
                '" . escape($_POST['gift_card_email_page_id']) . "',
                '" . escape($_POST['submit_form']) . "',
                '" . escape($_POST['submit_form_custom_form_page_id']) . "',
                '" . e($_POST['submit_form_quantity_type']) . "',
                '" . escape($_POST['submit_form_create']) . "',
                '" . escape($_POST['submit_form_update']) . "',
                '" . e($_POST['submit_form_update_where_field']) . "',
                '" . e($_POST['submit_form_update_where_value']) . "',
                '" . escape($_POST['add_comment']) . "',
                '" . escape($_POST['add_comment_page_id']) . "',
                '" . escape($_POST['add_comment_message']) . "',
                '" . escape($_POST['add_comment_name']) . "',
                '" . escape($_POST['add_comment_only_for_submit_form_update']) . "',
                '" . escape($_POST['product_form']) . "',
                '" . escape($_POST['form_name']) . "',
                '" . escape($_POST['form_label_column_width']) . "',
                '" . escape($_POST['form_quantity_type']) . "',
                '" . escape($_POST['custom_field_1']) . "',
                '" . escape($_POST['custom_field_2']) . "',
                '" . escape($_POST['custom_field_3']) . "',
                '" . escape($_POST['custom_field_4']) . "',
                '" . escape($_POST['notes']) . "',
                '" . escape($_POST['google_product_category']) . "',
                '" . escape($_POST['gtin']) . "',
                '" . escape($_POST['brand']) . "',
                '" . escape($_POST['mpn']) . "',
                '" . $user['id'] . "',
                UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $product_id = mysqli_insert_id(db::$con);

    if($selected_count > 1){
        foreach ($selected_images as $value) {db("INSERT INTO products_images_xref (product,file_name)VALUES ('$product_id','" . escape($value) . "')");}
    }
  
    // If the user added attributes, then save them.
    if ($_POST['attributes']) {
        $attributes = decode_json($_POST['attributes']);

        $sort_order = 0;

        foreach ($attributes as $attribute) {
            $sort_order++;

            db(
                "INSERT INTO products_attributes_xref (
                    product_id,
                    attribute_id,
                    option_id,
                    sort_order)
                VALUES (
                    '$product_id',
                    '" . e($attribute['attribute_id']) . "',
                    '" . e($attribute['option_id']) . "',
                    '$sort_order')");
        }
    }
    

	// If the user added groups, then save them.
    if ($_POST['groups']) {
        $groups = decode_json($_POST['groups']);

        foreach ($groups as $group) {
            db(
                "INSERT INTO products_groups_xref (
                    product,
                    product_group)
                VALUES (
                    '$product_id',
                    '" . e($group['group_id']) . "'
                   )");
        }
    }
    


    // if the address name is NOT blank then use that value for the address name
    if ($_POST['address_name'] != '') {
        $address_name = $_POST['address_name'];
        
    // else if the short description is NOT blank then use that value
    } elseif ($_POST['short_description'] != '') {
        $address_name = $_POST['short_description'];
        
    // else if the name is NOT blank then use that value
    } elseif ($_POST['name'] != '') {
        $address_name = $_POST['name'];
        
    // else use the product id
    } else {
        $address_name = $product_id;
    }
    
    // prepare the address name for the database
    $address_name = prepare_catalog_item_address_name($address_name);
    
    // update the product's address name
    $query = "UPDATE products SET address_name = '" . escape($address_name) . "' WHERE id = '$product_id'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // load all allowed zones in array by exploding string that has allowed zone ids separated by commas
    $allowed_zones = explode(',', $_POST['allowed_zones_hidden']);

    // foreach allowed zone insert row in products_zones_xref table
    foreach ($allowed_zones as $zone_id) {
        // if zone id is not blank, insert row
        if ($zone_id) {
            $query = "INSERT INTO products_zones_xref (product_id, zone_id) VALUES ($product_id, '" . escape($zone_id) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }

    // If a custom form was selected for submit form feature, then check if we need to add fields to database.
    if ($_POST['submit_form_custom_form_page_id']) {
        // Create array for storing submit form fields that have a value set, so if a user tried
        // to set multiple values for the same field, we don't add the extras.
        $added_submit_form_fields = array();
        
        // Loop through all submit form create fields in order to insert them into database.
        for ($field_number = 1; $field_number <= $_POST['last_submit_form_create_field_number']; $field_number++) {
            // If a field was selected, and the field has not already been added,
            // then continue to check if field should be added to database.
            if (
                ($_POST['submit_form_create_field_' . $field_number . '_form_field_id'])
                && (in_array($_POST['submit_form_create_field_' . $field_number . '_form_field_id'], $added_submit_form_fields) == false)
            ) {
                // Check to make sure that selected field actually exists on the custom form
                // in order to make sure that user is not trying to do something funny like trying to
                // set a field on a different form from the one they selected.
                $field_id = db_value(
                    "SELECT id
                    FROM form_fields
                    WHERE
                        (id = '" . escape($_POST['submit_form_create_field_' . $field_number . '_form_field_id']) . "')
                        AND (page_id = '" . escape($_POST['submit_form_custom_form_page_id']) . "')");

                // If a field was found for the selected field and selected custom form,
                // then continue to add field to database.
                if ($field_id) {
                    db(
                        "INSERT INTO product_submit_form_fields (
                            product_id,
                            action,
                            form_field_id,
                            value)
                        VALUES (
                            '" . escape($product_id) . "',
                            'create',
                            '" . escape($_POST['submit_form_create_field_' . $field_number . '_form_field_id']) . "',
                            '" . escape(trim($_POST['submit_form_create_field_' . $field_number . '_value'])) . "')");

                    // Remember that the field has been added so we don't add multiple records for the same field.
                    $added_submit_form_fields[] = $_POST['submit_form_create_field_' . $field_number . '_form_field_id'];
                }
            }
        }

        $added_submit_form_fields = array();
        
        // Loop through all submit form update fields in order to insert them into database.
        for ($field_number = 1; $field_number <= $_POST['last_submit_form_update_field_number']; $field_number++) {
            // If a field was selected, and the field has not already been added,
            // then continue to check if field should be added to database.
            if (
                ($_POST['submit_form_update_field_' . $field_number . '_form_field_id'])
                && (in_array($_POST['submit_form_update_field_' . $field_number . '_form_field_id'], $added_submit_form_fields) == false)
            ) {
                // Check to make sure that selected field actually exists on the custom form
                // in order to make sure that user is not trying to do something funny like trying to
                // set a field on a different form from the one they selected.
                $field_id = db_value(
                    "SELECT id
                    FROM form_fields
                    WHERE
                        (id = '" . escape($_POST['submit_form_update_field_' . $field_number . '_form_field_id']) . "')
                        AND (page_id = '" . escape($_POST['submit_form_custom_form_page_id']) . "')");

                // If a field was found for the selected field and selected custom form,
                // then continue to add field to database.
                if ($field_id) {
                    db(
                        "INSERT INTO product_submit_form_fields (
                            product_id,
                            action,
                            form_field_id,
                            value)
                        VALUES (
                            '" . escape($product_id) . "',
                            'update',
                            '" . escape($_POST['submit_form_update_field_' . $field_number . '_form_field_id']) . "',
                            '" . escape(trim($_POST['submit_form_update_field_' . $field_number . '_value'])) . "')");

                    // Remember that the field has been added so we don't add multiple records for the same field.
                    $added_submit_form_fields[] = $_POST['submit_form_update_field_' . $field_number . '_form_field_id'];
                }
            }
        }
    }

    // If submit form, and submit form update are enabled for this product,
    // then add product form field for the reference code and set that in the product property.
    if (
        $_POST['submit_form']
        && $_POST['submit_form_update']
        && ($_POST['submit_form_update_where_field'] == 'reference_code')
        &&
        (
            ($_POST['submit_form_update_where_value'] == '')
            || (mb_strpos($_POST['submit_form_update_where_value'], '^^') !== false)
        )
    ) {
        // Remove carets from where value, in order to get field name.
        $field_name = str_replace('^^', '', $_POST['submit_form_update_where_value']);

        if ($field_name == '') {
            $field_name = 'reference_code';
        }
        
        db(
            "INSERT INTO form_fields (
                form_type,
                product_id,
                name,
                label,
                type,
                required,
                user,
                timestamp)
            VALUES (
                'product',
                '$product_id',
                '" . e($field_name) . "',
                'Conversation Number:',
                'text box',
                '0',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        $field_id = mysqli_insert_id(db::$con);

        // Enable product form and set reference code field in product.
        db(
            "UPDATE products
            SET
                form = '1',
                submit_form_update_where_value = '^^" . e($field_name) . "^^'
            WHERE id = '$product_id'");
    }

    //if code has ^^image_loop_start^^ and ^^image_url^^ and ^^image_loop_end^^. so we prepare to code to insert in config
    if( 
        (strpos($_POST['code'], '^^image_url^^') !== false)&&
        (strpos($_POST['code'], '^^image_loop_start^^') !== false)&&
        (strpos($_POST['code'], '^^image_loop_end^^') !== false)
    ){ 
        //get product_image_code_template from config
        $query = "SELECT product_image_code_template FROM config";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $config_code = $row['product_image_code_template'];
        //check if config_code is not equal to POSTED code
        if($config_code != $_POST['code']){
            //update config_code with new code
            $query = "UPDATE config SET product_image_code_template = '" . escape($_POST['code']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }
    }       
 
    log_activity("product ($_POST[name]) was created", $_SESSION['sessionusername']);

    if ($_POST['current_form_state'] == 1) {
        
        $query = "SELECT id FROM products WHERE name = '" . $_POST['name'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        // forward user to view products page
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_fields.php?product_id=' . $row['id'] . '');
    } else {
        // forward user to view products page
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_products.php');
    }


}