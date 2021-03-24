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
$liveform = new liveform('add_product_group');

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

    // get all products
    $query = 
        "SELECT 
            id,
            name,
            enabled,
            short_description,
            details,
            price
        FROM products
        ORDER BY name";
    
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while ($row = mysqli_fetch_assoc($result)) {
        // if this product is not in the selected products array
        $row['name'] = h($row['name']);
        $row['price'] = $row['price'] / 100;

        // If this product is enabled, then use green color for name and short description.
        if ($row['enabled'] == 1) {
            $output_name_and_short_description_color = '#009900';
        
        // Otherwise this product is disabled, so use red color for name and short description.
        } else {
            $output_name_and_short_description_color = '#ff0000';
        }
        
        $output_rows .=
            '<tr id="' . $row['id'] . '">
                <td class="selectall"><input type="checkbox" name="products[]" value="' . $row['id'] . '" class="checkbox" /></td>
                <td onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'" class="chart_label pointer" style="color: ' . $output_name_and_short_description_color . '">' . $row['name'] . '</td>
                <td class="pointer" onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'" style="white-space: normal; color: ' . $output_name_and_short_description_color . '">' . h($row['short_description']) . '</td>
                <td class="pointer" onclick="window.location.href=\'edit_product.php?id=' . $row['id'] . '\'">' . prepare_amount($row['price']) . '</td>
                <td><input type="text" name="sort_order_product_' . $row['id'] . '" size="5" value="" maxlength="4" /></td>
            </tr>';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>[new product group]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            ' . get_wysiwyg_editor_code(array('full_description', 'details')) . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Product Group</h1>
            <div class="subheading" style="margin-bottom: 1em">Create a new product group and include products and other product groups.</div>
            <form name="form" action="add_product_group.php" method="post" >
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Product Group Information</h2></th>
                    </tr>
                    <tr>
                        <td>Product Group Name:</td>
                        <td><input type="text" name="name" /></td>
                    </tr>
                    <tr>
                        <td><label for="enabled">Enable:</label></td>
                        <td><input type="checkbox" id="enabled" name="enabled" value="1" checked="checked" class="checkbox"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Catalog Page Display Options</h2></th>
                    </tr>
                    <tr>
                        <td>Parent Product Group:</td>
                        <td><select name="parent_id">' . get_product_group_options($_GET['id'], $parent_product_group_id = 0, $excluded_product_group_id = 0, $level = 0, $product_groups = array(), $include_select_product_groups = FALSE) . '</select></td>
                    </tr>
                    <tr>
                        <td>Short Description:</td>
                        <td><input type="text" name="short_description" maxlength="100" size="60" /></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Full Description:</td>
                        <td><textarea name="full_description" id="full_description" style="width: 600px; height: 200px;"></textarea></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Display Type:</td>
                        <td>
                            <input type="radio" class="radio" value="browse" id="browse" name="display_type" checked="checked" onclick="show_or_hide_product_group_display_type(\'browse\')" /><label for="browse"> Display contents for browsing on catalog page</label><br />
                            <input type="radio" class="radio" value="select" id="select" name="display_type" onclick="show_or_hide_product_group_display_type(\'select\')" /><label for="select"> Display contents for selection on catalog detail page</label>
                        </td>
                    </tr>
                    <tr id="details_row" style="display: none">
                        <td style="vertical-align: top">Details:</td>
                        <td><textarea name="details" id="details" style="width: 600px; height: 200px;"></textarea></td>
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
                    <tr id="keywords_row" style="display: none">
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
                </table>
                <h2 style="margin-bottom: 1em">Products to Include</h2>
                <table class="chart" style="margin-bottom: 1.5em">
                    <tr>
                        <th style="text-align: center;" id="select_all">Select</th>
                        <th>Name</th>
                        <th>Short Description</th>
                        <th>Price</th>
                        <th>Order</th>
                    </tr>
                    ' . $output_rows . '
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_button" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
                <input type="hidden" name="max_input_vars_test" value="true">
            </form>
        </div>
        <script>
    
        
        $(document).ready(function() {
            
            $( "#show_img_selector_dialog" ).on( "click", function() {
                $("body").attr("style","overflow:hidden;");
                img_selector_dialog.dialog( "open" );
            });
            
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
            
        });
    </script>
            
            ' .
        output_footer();

$liveform->remove_form();

} else {
    validate_token_field();
    
    // If the max_input_vars_test hidden field is not in the post data then that means the post data was truncated,
    // so output error. This can happen because of max_input_vars (i.e. php.ini
    // setting added in PHP v5.3.9 and often backported to earlier versions).
    // This can happen when there is a large number of products (e.g. 1,000+).
    // The default value for max_input_vars is 1,000.
    if (isset($_POST['max_input_vars_test']) == FALSE) {
        output_error('Sorry, the server did not accept the form that you submitted. We recommend that you ask the server administrator to check the max_input_vars PHP setting in the php.ini file.  We recommend that it be set to a number that is at least double the number of Products that the site will contain. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // if the name is blank, then mark error and forward user back to previous screen
    if ($_POST['name'] == '') {
        $liveform->mark_error('name', 'Name is required.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_product_group.php');
        exit();
    }
    
    // if the address name is NOT blank then use that value for the address name
    if ($_POST['address_name'] != '') {
        $address_name = $_POST['address_name'];
        
    // else if the short description is NOT blank then use that value
    } elseif ($_POST['short_description'] != '') {
        $address_name = $_POST['short_description'];
        
    // else use the name as the value
    } else {
        $address_name = $_POST['name'];
    }
    
    // prepare the address name for the database
    $address_name = prepare_catalog_item_address_name($address_name);

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

    // create product group
    db(
        "INSERT INTO product_groups (
            name,
            enabled,
            parent_id,
            short_description,
            full_description,
            details,
            code,
            keywords,
            image_name,
            display_type,
            address_name,
            title,
            meta_description,
            meta_keywords,
            attributes,
            user, 
            timestamp)
        VALUES (
            '" . e($_POST['name']) . "',
            '" . e($_POST['enabled']) . "',
            '" . e($_POST['parent_id']) . "', 
            '" . e($_POST['short_description']) . "', 
            '" . e(prepare_rich_text_editor_content_for_input($_POST['full_description'])) . "', 
            '" . e(prepare_rich_text_editor_content_for_input($_POST['details'])) . "', 
            '" . e($_POST['code']) . "', 
            '" . e($_POST['keywords']) . "', 
           	$sql_imagename
            '" . e($_POST['display_type']) . "', 
            '" . e($address_name) . "',
            '" . e($_POST['title']) . "',
            '" . e($_POST['meta_description']) . "',
            '" . e($_POST['meta_keywords']) . "',
            '1',
            '$user[id]', 
            UNIX_TIMESTAMP())");
    
    $product_group_id = mysqli_insert_id(db::$con);

    if($selected_count > 1){
        foreach ($selected_images as $value) {db("INSERT INTO product_groups_images_xref (product_group,file_name)VALUES ('$product_group_id','" . escape($value) . "')");}
    }

    // if at least one product was selected then proceed with update to products_groups_xref table
    if ($_POST['products']) {
        // foreach product that was selected
        foreach($_POST['products'] as $product_id) {
            $query = "INSERT INTO products_groups_xref (product, product_group, sort_order) VALUES ('" . escape($product_id) . "', '" . $product_group_id . "', '" . escape($_POST['sort_order_product_' . $product_id]) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        }
    }
    
    $search_results_pages = array();
        
    // get data from all search result pages that have "search products" enabled
    $query = "SELECT page_id, product_group_id FROM search_results_pages WHERE search_catalog_items = '1'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while($row = mysqli_fetch_assoc($result)) {
        $search_results_pages[] = $row;
    }
    
    $search_results_pages_using_parent_product_group = array();
    
    // loop through each search result page to get the search results page ids that uses this product group
    foreach ($search_results_pages as $search_results_page) {
        $search_results_page_product_groups = array();
        
        // get the product groups inside of this product group
        $search_results_page_product_groups = get_product_groups_in_product_group_tree($search_results_page['product_group_id']);
        
        // loop through the product groups to see if any of them match this one, and if there is a match then add it to the array
        foreach ($search_results_page_product_groups as $product_group) {
            if ($product_group['id'] == $product_group_id) {
                $search_results_pages_using_parent_product_group[] = $search_results_page;
            }
        }
    }
    
    // loop through the search result pages for the parent product group and delete then re-build it's tag cloud
    foreach($search_results_pages_using_parent_product_group as $search_results_page) {
        delete_tag_cloud_keywords_for_search_results_page($search_results_page['page_id']);
        update_tag_cloud_keywords_for_search_results_page_product_group($search_results_page['page_id'], $search_results_page['product_group_id']);
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
    
    log_activity("product group ($_POST[name]) was created", $_SESSION['sessionusername']);

    // forward user to view product groups page
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_product_groups.php');
}