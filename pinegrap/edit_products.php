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

$liveform = new liveform('view_products');

// If the form has not been submitted yet, then output form.
if (!$_POST) {

    $enabled_options = array(
        '' => '',
        'Enable' => '1',
        'Disable' => '0'
    );

    if (ECOMMERCE_SHIPPING) {
        $zones = db_items(
            "SELECT
                id,
                name
            FROM zones
            ORDER BY name");
    }
	
	$price_change_method_options = array(
        '' => '',
		'decrease (eg. - 30.00 ' . BASE_CURRENCY_SYMBOL . ')' => '0',
        'increase (eg. + 30.00 ' . BASE_CURRENCY_SYMBOL . ')' => '1',
		'decrease percentage (eg. - 30%)' => '2',
		'increase percentage (eg. + 30%)' => '3'
    );



	
    require('templates/edit_products.php');

// Otherwise the form has been submitted, so process it.
} else {

    validate_token_field();

	$edit_change_price_method = $_POST['edit_change_price_method'];
	if(($edit_change_price_method == '0') || ($edit_change_price_method == '1')){
		$raw_price_value = $_POST['edit_price_value'];
		// remove commas from price
		$price_value = str_replace(',', '', $raw_price_value);
		// convert price from dollars to cents
		$price_value = $price_value * 100;
	}
	else if(($edit_change_price_method == '2') || ($edit_change_price_method == '3')){
		$price_value = $_POST['edit_price_value'];
	}

    
	



    // If at least one product was selected then continue.
    if ($_POST['products']) {
        $number_of_products = 0;
        
        switch ($_POST['action']) {
            // If products are being edited, proceed.
            case 'edit':
                // If at least one action was selected, then continue.
                if (
                    ($_POST['edit_enabled'] != '')
                    || ($_POST['edit_allowed_zones'])
                    || ($_POST['edit_disallowed_zones'])
					|| ($_POST['edit_change_price_method'] != '')
                ) {
                    if ($_POST['edit_allowed_zones']) {
                        $allowed_zones = explode(',', $_POST['edit_allowed_zones']);
                    } else {
                        $allowed_zones = array();
                    }
                    
                    if ($_POST['edit_disallowed_zones']) {
                        $disallowed_zones = explode(',', $_POST['edit_disallowed_zones']);
                    } else {
                        $disallowed_zones = array();
                    }

					$number_of_undecerease_products =0;
					
                    // Loop through selected products in order to edit them.
                    foreach ($_POST['products'] as $product_id) {
                        $sql_enabled = '';
					
						if($edit_change_price_method != ''){
							$query = "SELECT products.id as id, products.price as price,products.name as name,price,products.short_description as short_description FROM products  WHERE id = '" . escape($product_id) . "'";
							$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
							$row = mysqli_fetch_assoc($result);
							$price = $row['price'];
						}	
						
                        // If the status pick list was selected, then update status for product.
                        if ($_POST['edit_enabled'] != '') {
                            // Delete any existing tag cloud data for this product.
                            db("DELETE FROM tag_cloud_keywords WHERE (item_id = '" . escape($product_id) . "') AND (item_type = 'product')");

                            // If products are being enabled, and there are xref records for this product,
                            // then deal with adding keywords for tag clouds.
                            if (
                                ($_POST['edit_enabled'] == '1')
                                && (db_value("SELECT COUNT(*) FROM tag_cloud_keywords_xref WHERE (item_id = '" . escape($product_id) . "') AND (item_type = 'product')") > 0)
                            ) {
                                // Create an array of keywords.
                                $keywords = explode(',', db_value("SELECT keywords FROM products WHERE id = '" . escape($product_id) . "'"));

                                // Remove spaces from the beginning and end of all keywords.
                                $keywords = array_map('trim', $keywords);

                                // Remove blank keywords.
                                $keywords = array_filter($keywords, 'strlen');

                                // Remove duplicate keywords.
                                $keywords = array_unique($keywords);

                                // Loop through the keywords in order to add them to database.
                                foreach ($keywords as $keyword) {
                                    db(
                                        "INSERT INTO tag_cloud_keywords 
                                        (
                                            keyword, 
                                            item_id, 
                                            item_type
                                        ) VALUES (
                                            '" . escape($keyword) . "',
                                            '" . escape($product_id) . "',
                                            'product')");
                                }
                            }

                            $sql_enabled = "enabled = '" . e($_POST['edit_enabled']) . "',";
                        }



						// Price Update
						$sql_new_prices ='';
						if($edit_change_price_method != ''){
						
							
							
							if($edit_change_price_method == '0'){
								$price = $row['price'];
								$price = $price - $price_value;
								if($price <= '0'){
									$price =  $row['price'];
									$number_of_undecerease_products++;
									$liveform->mark_error('Update Error', $number_of_undecerease_products . ' product price cant be decerease. product price equal or smaller than 0!');
								}
								$sql_new_prices = "price = '" . e($price) . "',";
							}
							else if($edit_change_price_method == '1'){
								$price = $row['price'];
								$price = $price + $price_value;
								$sql_new_prices = "price = '" . e($price) . "',";
							} 
							else if($edit_change_price_method == '2') {
								$price = $row['price'];
								$price = ((100 - $price_value) / 100) * $price;
								if($price <= '0'){
									$price =  $row['price'];
									$number_of_undecerease_products++;
									$liveform->mark_error('Update Error', $number_of_undecerease_products . ' product price cant be decerease. product price equal or smaller than 0!');
								}
								$sql_new_prices = "price = '" . e($price) . "',";
							}
							else if($edit_change_price_method == '3') {
								$price = $row['price'];
								$price = ((100 + $price_value) / 100) * $price;
								$sql_new_prices = "price = '" . e($price) . "',";
							}
						}







                        // Loop through all checked allowed zones, in order to allow product for them.
                        foreach ($allowed_zones as $zone_id) {
                            // If this product is not already allowed for this zone, then allow it.
                            if (
                                !db_value(
                                    "SELECT product_id
                                    FROM products_zones_xref
                                    WHERE
                                        (product_id = '" . e($product_id) . "')
                                        AND (zone_id = '" . e($zone_id) . "')")
                            ) {
                                db(
                                    "INSERT INTO products_zones_xref (
                                        product_id,
                                        zone_id)
                                    VALUES (
                                        '" . e($product_id) . "',
                                        '" . e($zone_id) . "')");
                            }
                        }
                        
                        // Loop through all checked disallowed zones
                        // so we can disallow this product for each one.
                        foreach ($disallowed_zones as $zone_id) {
                            db(
                                "DELETE FROM products_zones_xref
                                WHERE
                                    (product_id = '" . e($product_id) . "')
                                    AND (zone_id = '" . e($zone_id) . "')");
                        }

                        // Update product enabled property, if necessary, and timestamp.
                        db(
                            "UPDATE products
                            SET
                                $sql_enabled
								$sql_new_prices
                                timestamp = UNIX_TIMESTAMP(),
                                user = '" . USER_ID . "'
                            WHERE id = '" . escape($product_id) . "'");

                        $number_of_products++;
						
                    }
                    
                    // If at least one product was modified, then log activity.
                    if ($number_of_products > 0) {
                        $log_message = '';
                        
                        // If the enabled property was selected to be edited then set message for log.
                        if ($_POST['edit_enabled'] != '') {
                            // If the log message is not blank, then add separator.
                            if ($log_message != '') {
                                $log_message .= ', and ';
                            }

                            if ($number_of_products == 1) {
                                $verb = 'was';
                            } else {
                                $verb = 'were';
                            }

                            if ($_POST['edit_enabled'] == '1') {
                                $log_message .= $verb . ' enabled';
                            } else {
                                $log_message .= $verb . ' disabled';
                            }
                        }

                        // If there were allowed or disallowed zones then set message for log.
                        if (
                            ($_POST['edit_allowed_zones'])
                            || ($_POST['edit_disallowed_zones'])
                        ) {
                            // If the log message is not blank, then add separator.
                            if ($log_message != '') {
                                $log_message .= ', and ';
                            }

                            $log_message .= ' had shipping zones allowed/disallowed';
                        }



						// If the enabled property was selected to be edited then set message for log.
                        if ($edit_change_price_method != '')  {
                            // If the log message is not blank, then add separator.
                            if ($log_message != '') {
                                $log_message .= ', and ';
                            }

                           
							 if ($number_of_products == 1) {
								    $verb = 'was';
							} else {
								    $verb = 'were';
							}
							
							if(($number_of_undecerease_products >= 1) && ($log_message == '')){
								$verb = '/';
							}


                            if ($edit_change_price_method == '0') {
								if($number_of_undecerease_products >= 1){
									$log_message .= $verb . ' ' .($number_of_products - $number_of_undecerease_products).' products price decerease to ' . ($price_value / 100) . ' ' . BASE_CURRENCY_CODE;
								}else{
									$log_message .= $verb . ' decerease price to ' . ($price_value / 100) . ' ' . BASE_CURRENCY_CODE;
								}
                               
                            } 
							else if($edit_change_price_method == '1') {
                                $log_message .= $verb . ' increrease price to ' . ($price_value / 100) . ' ' . BASE_CURRENCY_CODE;
                            } 
							else if($edit_change_price_method == '2') {
								$price_value = $price_value * 100;
								if($number_of_undecerease_products >= 1){
									$log_message .= $verb . ' ' .($number_of_products - $number_of_undecerease_products).' products price decerease to ' . ($price_value / 100) . '%';
								}else{
									$log_message .= $verb . ' decerease price to ' . ($price_value / 100) . '%';
								}
								
							}
							else if($edit_change_price_method == '3') {
								$price_value = $price_value * 100;
								$log_message .= $verb . ' increrease price to ' . ($price_value / 100) . '%';
							}
                        }

						
                         
                        // If there is a log message, then log it and add notice.
                        if ($log_message != '') {
                            $plural_suffix = '';

                            if ($number_of_products > 1) {
                                $plural_suffix = 's';
                            }

                            log_activity($number_of_products . ' product' . $plural_suffix . ' ' . $log_message, $_SESSION['sessionusername']);
                            $liveform->add_notice($number_of_products . ' product' . $plural_suffix . ' ' . h($log_message) . '.');
                        }
                    }
                }
                
                break;

            // If products are being deleted, then delete them.
            case 'delete':
                $number_of_products = 0;

                foreach ($_POST['products'] as $product_id) {
                    $query = "DELETE FROM products ".
                             "WHERE id = '$product_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete product references in products_groups_xref
                    $query = "DELETE FROM products_groups_xref ".
                             "WHERE product = '$product_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete product references in products_zones_xref
                    $query = "DELETE FROM products_zones_xref ".
                             "WHERE product_id = '$product_id'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete form fields for this product
                    $query = "DELETE FROM form_fields WHERE (product_id = '" . $product_id . "') AND (product_id != '0')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete form field options for this product
                    $query = "DELETE FROM form_field_options WHERE (product_id = '" . $product_id . "') AND (product_id != '0')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // Delete target options for this product.
                    db("DELETE FROM target_options WHERE (product_id = '" . $product_id . "') AND (product_id != '0')");

                    db("DELETE FROM products_images_xref WHERE product = '" . e($product_id) . "'");

                    db("DELETE FROM products_attributes_xref WHERE product_id = '" . e($product_id) . "'");

                    db("DELETE FROM product_submit_form_fields WHERE (product_id = '" . e($product_id) . "')");
                    
                    // delete all of the keywords for this product
                    $query = "DELETE FROM tag_cloud_keywords WHERE (item_id = '" . escape($product_id) . "') AND (item_type = 'product')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    
                    // delete all of the keywords xref records for this product
                    $query = "DELETE FROM tag_cloud_keywords_xref WHERE (item_id = '" . escape($product_id) . "') AND (item_type = 'product')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // Check if this product has short links, in order to determine if we need to delete them.
                    $query =
                        "SELECT COUNT(*)
                        FROM short_links
                        WHERE
                            (destination_type = 'product')
                            AND (product_id = '" . escape($product_id) . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_row($result);

                    // If a short link exists, then delete short links for this product.
                    if ($row[0] != 0) {
                        $query =
                            "DELETE FROM short_links
                            WHERE
                                (destination_type = 'product')
                                AND (product_id = '" . escape($product_id) . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    }

                    // Delete offer rule associations with product.
                    db("DELETE FROM offer_rules_products_xref WHERE product_id = '" . e($product_id) . "'");
                    
                    $number_of_products++;
                }
                
                // If at least one product was deleted, then log this information and output a notice.
                if ($number_of_products > 0) {
                    if ($number_of_products == 1) {
                        $plural_suffix = '';
                        $verb = 'was';
                    } else {
                        $plural_suffix = 's';
                        $verb = 'were';
                    }

                    log_activity($number_of_products . ' product' . $plural_suffix . ' ' . $verb . ' deleted', $_SESSION['sessionusername']);
                    $liveform->add_notice($number_of_products . ' product' . $plural_suffix . ' ' . $verb . ' deleted.');
                }
                
                break;
        }
    }

    go(PATH . SOFTWARE_DIRECTORY . '/view_products.php');
}