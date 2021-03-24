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

// Various functions related to shipping


function update_shipping_cost_for_ship_to($ship_to_id) {

    // get info for this ship to
    $query =
        "SELECT
            ship_tos.state,
            ship_tos.zip_code,
            ship_tos.shipping_method_id,
            shipping_methods.service as shipping_method_service,
            shipping_methods.realtime_rate as shipping_method_realtime_rate,
            shipping_methods.base_rate as shipping_method_base_rate,
            shipping_methods.variable_base_rate AS shipping_method_variable_base_rate,
            shipping_methods.base_rate_2 AS shipping_method_base_rate_2,
            shipping_methods.base_rate_2_subtotal AS shipping_method_base_rate_2_subtotal,
            shipping_methods.base_rate_3 AS shipping_method_base_rate_3,
            shipping_methods.base_rate_3_subtotal AS shipping_method_base_rate_3_subtotal,
            shipping_methods.base_rate_4 AS shipping_method_base_rate_4,
            shipping_methods.base_rate_4_subtotal AS shipping_method_base_rate_4_subtotal,
            shipping_methods.primary_weight_rate AS shipping_method_primary_weight_rate,
            shipping_methods.primary_weight_rate_first_item_excluded AS shipping_method_primary_weight_rate_first_item_excluded,
            shipping_methods.secondary_weight_rate AS shipping_method_secondary_weight_rate,
            shipping_methods.secondary_weight_rate_first_item_excluded AS shipping_method_secondary_weight_rate_first_item_excluded,
            shipping_methods.item_rate AS shipping_method_item_rate,
            shipping_methods.item_rate_first_item_excluded AS shipping_method_item_rate_first_item_excluded,
            zones.base_rate as zone_base_rate,
            zones.primary_weight_rate AS zone_primary_weight_rate,
            zones.secondary_weight_rate AS zone_secondary_weight_rate,
            zones.item_rate AS zone_item_rate
        FROM ship_tos
        LEFT JOIN shipping_methods ON ship_tos.shipping_method_id = shipping_methods.id
        LEFT JOIN zones ON ship_tos.zone_id = zones.id
        WHERE ship_tos.id = '" . escape($ship_to_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $state = $row['state'];
    $zip_code = $row['zip_code'];
    $shipping_method_id = $row['shipping_method_id'];
    $shipping_method_service = $row['shipping_method_service'];
    $shipping_method_realtime_rate = $row['shipping_method_realtime_rate'];
    $shipping_method_base_rate = $row['shipping_method_base_rate'];
    $shipping_method_variable_base_rate = $row['shipping_method_variable_base_rate'];
    $shipping_method_base_rate_2 = $row['shipping_method_base_rate_2'];
    $shipping_method_base_rate_2_subtotal = $row['shipping_method_base_rate_2_subtotal'];
    $shipping_method_base_rate_3 = $row['shipping_method_base_rate_3'];
    $shipping_method_base_rate_3_subtotal = $row['shipping_method_base_rate_3_subtotal'];
    $shipping_method_base_rate_4 = $row['shipping_method_base_rate_4'];
    $shipping_method_base_rate_4_subtotal = $row['shipping_method_base_rate_4_subtotal'];
    $shipping_method_primary_weight_rate = $row['shipping_method_primary_weight_rate'];
    $shipping_method_primary_weight_rate_first_item_excluded = $row['shipping_method_primary_weight_rate_first_item_excluded'];
    $shipping_method_secondary_weight_rate = $row['shipping_method_secondary_weight_rate'];
    $shipping_method_secondary_weight_rate_first_item_excluded = $row['shipping_method_secondary_weight_rate_first_item_excluded'];
    $shipping_method_item_rate = $row['shipping_method_item_rate'];
    $shipping_method_item_rate_first_item_excluded = $row['shipping_method_item_rate_first_item_excluded'];
    $zone_base_rate = $row['zone_base_rate'];
    $zone_primary_weight_rate = $row['zone_primary_weight_rate'];
    $zone_secondary_weight_rate = $row['zone_secondary_weight_rate'];
    $zone_item_rate = $row['zone_item_rate'];

    // Get all order items for this ship to that do not have free shipping enabled
    // in order to calculate costs for each item.
    $order_items = db_items(
        "SELECT
            order_items.id,
            order_items.quantity,
            products.name,
            products.weight,
            products.primary_weight_points,
            products.secondary_weight_points,
            products.length,
            products.width,
            products.height,
            products.container_required,
            products.extra_shipping_cost
         FROM order_items
         LEFT JOIN products ON products.id = order_items.product_id
         WHERE
            (order_items.ship_to_id = '" . escape($ship_to_id) . "')
            AND (products.free_shipping = '0')
         ORDER BY order_items.id ASC");

    $shipping_cost = 0;

    $realtime_rate = get_shipping_realtime_rate(array(
        'ship_to_id' => $ship_to_id,
        'service' => $shipping_method_service,
        'realtime_rate' => $shipping_method_realtime_rate,
        'state' => $state,
        'zip_code' => $zip_code,
        'items' => $order_items));

    // If there was an error getting the realtime rate, then this shipping method is not valid for
    // the recipient, so mark recipient incomplete, so customer will be forced to choose a different
    // shipping method.
    if ($realtime_rate === false) {
        db("UPDATE ship_tos SET complete = '0' WHERE id = '" . e($ship_to_id) . "'");
        return;
    }

    $shipping_cost += $realtime_rate;

    $shipping_cost += get_shipping_method_base_rate(array(
        'base_rate' => $shipping_method_base_rate,
        'variable_base_rate' => $shipping_method_variable_base_rate,
        'base_rate_2' => $shipping_method_base_rate_2,
        'base_rate_2_subtotal' => $shipping_method_base_rate_2_subtotal,
        'base_rate_3' => $shipping_method_base_rate_3,
        'base_rate_3_subtotal' => $shipping_method_base_rate_3_subtotal,
        'base_rate_4' => $shipping_method_base_rate_4,
        'base_rate_4_subtotal' => $shipping_method_base_rate_4_subtotal,
        'ship_to_id' => $ship_to_id));

    $shipping_cost += $zone_base_rate;

    // Loop through all items in cart for this recipient in order
    // to calculate shipping cost for each item.
    foreach ($order_items as $key => $order_item) {
        $shipping_method_primary_weight_rate_quantity = $order_item['quantity'];
        $shipping_method_secondary_weight_rate_quantity = $order_item['quantity'];
        $shipping_method_item_rate_quantity = $order_item['quantity'];

        // If this is the first order item then check if we need to reduce the quantity
        // in order to exclude the first item from shipping charges.
        if ($key == 0) {
            // If the first item should be excluded for the primary weight calculation
            // for the shipping method, then reduce the quantity by one.
            if ($shipping_method_primary_weight_rate_first_item_excluded == 1) {
                $shipping_method_primary_weight_rate_quantity--;
            }

            // If the first item should be excluded for the secondary weight calculation
            // for the shipping method, then reduce the quantity by one.
            if ($shipping_method_secondary_weight_rate_first_item_excluded == 1) {
                $shipping_method_secondary_weight_rate_quantity--;
            }

            // If the first item should be excluded for the item calculation
            // for the shipping method, then reduce the quantity by one.
            if ($shipping_method_item_rate_first_item_excluded == 1) {
                $shipping_method_item_rate_quantity--;
            }
        }

        $shipping_method_cost = ($order_item['primary_weight_points'] * $shipping_method_primary_weight_rate * $shipping_method_primary_weight_rate_quantity) + ($order_item['secondary_weight_points'] * $shipping_method_secondary_weight_rate * $shipping_method_secondary_weight_rate_quantity) + ($shipping_method_item_rate * $shipping_method_item_rate_quantity);
        $zone_cost = ($order_item['primary_weight_points'] * $zone_primary_weight_rate * $order_item['quantity']) + ($order_item['secondary_weight_points'] * $zone_secondary_weight_rate * $order_item['quantity']) + ($zone_item_rate * $order_item['quantity']);
        $extra_shipping_cost = $order_item['extra_shipping_cost'] * $order_item['quantity'];
        $shipping_cost_for_item = $shipping_method_cost + $zone_cost + $extra_shipping_cost;
        $shipping_cost = $shipping_cost + $shipping_cost_for_item;
        
        db("UPDATE order_items SET shipping = '" . round($shipping_cost_for_item / $order_item['quantity']) . "' WHERE id = '" . $order_item['id'] . "'");
    }
    
    // Check for a shipping discount from an offer.  We go ahead and do this now, instead of waiting
    // for a feature to run apply_offers_to_cart(), because there are times, like when a customer
    // completes shipping fields on an express order page and clicks purchase now, where we do not
    // run apply_offers_to_cart().

    $original_shipping_cost = 0;

    $offer = get_best_shipping_discount_offer($ship_to_id, $shipping_method_id);
    
    // If a shipping discount offer was found, then discount shipping.
    if ($offer) {

        // Remember the original shipping cost, before it is updated.
        $original_shipping_cost = $shipping_cost;
        
        // Update shipping cost to contain discount.
        $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));
    }
    
    // Update shipping cost for recipient.
    db("
        UPDATE ship_tos
        SET
            shipping_cost = '$shipping_cost',
            original_shipping_cost = '$original_shipping_cost',
            offer_id = '" . e($offer['id']) . "'
        WHERE id = '" . e($ship_to_id) . "'");
}

function get_delivery_date($properties) {

    $ship_to_id = $properties['ship_to_id'];
    $shipping_method = $properties['shipping_method'];
    $zip_code = trim($properties['zip_code']);
    $country = $properties['country'];

    // Allows us to specify that we only want an exact delivery date from a carrier and don't want
    // to calculate a general delivery date from liveSite, if there is an issue with carrier.
    // This is used on express order to show delivery date, because we only want to show an exact
    // delivery date.
    $only_from_carrier = $properties['only_from_carrier'];

    // Allows you to set that you also want a formatted date returned (e.g. "Mon Jul 1 2017" and not
    // just "2017-07-01").
    $formatted = $properties['formatted'];

    // If there is not shipping method info, then get info.
    if (!isset($shipping_method['service'])) {

        $shipping_method = db_item(
            "SELECT
                id,
                service,
                handle_days,
                handle_mon,
                handle_tue,
                handle_wed,
                handle_thu,
                handle_fri,
                handle_sat,
                handle_sun,
                ship_mon,
                ship_tue,
                ship_wed,
                ship_thu,
                ship_fri,
                ship_sat,
                ship_sun,
                end_of_day,
                base_transit_days,
                adjust_transit,
                transit_on_sunday,
                transit_on_saturday
            FROM shipping_methods
            WHERE id = '" . e($shipping_method['id']) . "'");

        if (!$shipping_method) {
            return array(
                'status' => 'error',
                'message' => 'Sorry, the shipping method could not be found.');
        }
    }

    $service = $shipping_method['service'];

    // Get largest preparation time by looking at all products for this recipient.
    $preparation_time = db(
        "SELECT MAX(products.preparation_time)
        FROM order_items
        LEFT JOIN products on products.id = order_items.product_id
        WHERE ship_to_id = '" . e($ship_to_id) . "'");

    $excluded_transit_dates = db_values(
        "SELECT date FROM excluded_transit_dates
        WHERE shipping_method_id = '" . e($shipping_method['id']) . "'");

    $response = get_ship_date(array(
        'preparation_time' => $preparation_time,
        'shipping_method' => $shipping_method,
        'excluded_transit_dates' => $excluded_transit_dates
    ));

    if ($response['status'] == 'error') {
        return $response;
    }

    $ship_date = $response['ship_date'];

    // Get the carrier from the first part of the service name.
    $carrier = strtok($service, '_');

    // If we support real-time delivery date for the carrier, then get real-time delivery date.
    if (
        ($carrier == 'usps' and ($country == 'US' or $country == 'USA'))
        or (FEDEX and $carrier == 'fedex')
    ) {

        // If the country is US, then check zip code and truncate to 5 characters.
        if ($country == 'US' or $country == 'USA') {

            // If the zip is less then 5 characters, then we know it is invalid, so return error.
            // This helps us avoid sending invalid queries to the carriers.
            if (mb_strlen($zip_code) < 5) {
                return array(
                    'status' => 'error',
                    'message' => 'Sorry, the zip code is invalid. It has less than 5 characters.');
            }

            // Just get the first 5 digits of the zip code. The +4 part probably does not make a
            // difference for the delivery date and it would make the caching less efficient.
            $zip_code = mb_substr($zip_code, 0, 5);
        }

        // Prepare timestamp so we only get cached delivery date that is recent.
        $day_ago_timestamp = time() - 86400;

        // Get a random number between 1 and 100 in order to determine if we should delete old
        // delivery dates from the db cache table.  There is a 1 in 100 chance that we will delete
        // old delivery dates each time we request a delivery date for a recipient.  Old is
        // considered more than a day old.

        $random_number = rand(1, 100);
        
        if ($random_number == 1) {
            db("DELETE FROM shipping_delivery_dates WHERE timestamp < '$day_ago_timestamp'");
        }

        // Check cache table first for a delivery date.
        $delivery_date = db(
            "SELECT delivery_date
            FROM shipping_delivery_dates
            WHERE
                (service = '" . e($service) . "')
                AND (zip_code = '" . e($zip_code) . "')
                AND (ship_date = '" . e($ship_date) . "')
                AND (timestamp > '$day_ago_timestamp')");

        // If we found a delivery date from the cache table, then return it.
        if ($delivery_date) {

            $delivery_date_info = '';

            if ($formatted) {
                $delivery_date_info =
                    get_absolute_time(array(
                        'timestamp' => strtotime($delivery_date),
                        'type' => 'date',
                        'year' => false));
            }

            return array(
                'status' => 'success',
                'delivery_date' => $delivery_date,
                'delivery_date_info' => $delivery_date_info,
                'from_carrier' => true);
        }

        // Get the real-time delivery date differently based on the carrier.
        switch ($carrier) {

            case 'usps':
                
                $response = get_usps_delivery_date(array(
                    'service' => $service,
                    'ship_date' => $ship_date,
                    'zip_code' => $zip_code
                ));

                break;
            
            case 'fedex':
                
                // Get heaviest item for this recipient so we can pass that to FedEx for the weight.
                // FedEx requires the weight because we use a rate request, but we don't believe the
                // weight will actually affect the delivery date, so we are not going to bother
                // to package all the items and use containers and make a request for every package.

                $weight = db(
                    "SELECT MAX(products.weight)
                    FROM order_items
                    LEFT JOIN products on products.id = order_items.product_id
                    WHERE ship_to_id = '" . e($ship_to_id) . "'");

                $response = get_fedex_delivery_date(array(
                    'service' => $service,
                    'ship_date' => $ship_date,
                    'zip_code' => $zip_code,
                    'country' => $country,
                    'weight' => $weight
                ));

                break;
        }

        if ($response['status'] == 'success') {

            $delivery_date_info = '';

            if ($formatted) {
                $delivery_date_info =
                    get_absolute_time(array(
                        'timestamp' => strtotime($response['delivery_date']),
                        'type' => 'date',
                        'year' => false));
            }

            return array(
                'status' => 'success',
                'delivery_date' => $response['delivery_date'],
                'delivery_date_info' => $delivery_date_info,
                'from_carrier' => true);
        }
    }

    // If the request specified that we should only get a delivery date from carrier, then return
    // error now before bothering to calculate delivery date.
    if ($only_from_carrier) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, the delivery date could not be found from the carrier, and the request specified that we should not manually calculate a general delivery date.');
    }

    // If we have gotten here then that means we were not able to get a delivery date from a
    // carrier, so we need to calculate the delivery date ourself.

    $transit_days = $shipping_method['base_transit_days'];
    
    // If adjust transit is on for this shipping method, get transit adjustment days for country
    if ($shipping_method['adjust_transit']) {
        $transit_adjustment_days =
            db("SELECT transit_adjustment_days FROM countries WHERE code = '" . e($country) . "'");

        if ($transit_adjustment_days) {
            $transit_days += $transit_adjustment_days;
        }
    }

    // If we have not already gotten excluded transit dates above, then get them now.
    if (!isset($excluded_transit_dates)) {
        $excluded_transit_dates = db_values(
            "SELECT date FROM excluded_transit_dates
            WHERE shipping_method_id = '" . e($shipping_method['id']) . "'");
    }

    $delivery_date = calculate_delivery_date(array(
        'ship_date' => $ship_date,
        'transit_days' => $transit_days,
        'transit_on_sunday' => $shipping_method['transit_on_sunday'],
        'transit_on_saturday' => $shipping_method['transit_on_saturday'],
        'excluded_transit_dates' => $excluded_transit_dates));
    
    if (!$delivery_date) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, the delivery date could not be found.');
    }

    // If we have gotten here then we found a delivery date, so return it.

    $delivery_date_info = '';

    if ($formatted) {
        $delivery_date_info =
            get_absolute_time(array(
                'timestamp' => strtotime($delivery_date),
                'type' => 'date',
                'year' => false));
    }

    return array(
        'status' => 'success',
        'delivery_date' => $delivery_date,
        'delivery_date_info' => $delivery_date_info,
        'from_carrier' => false);
}

function get_usps_delivery_date($properties) {

    $service = $properties['service'];
    $ship_date = $properties['ship_date'];
    $zip_code = trim($properties['zip_code']);

    if (!function_exists('curl_init')) {

        $message = 'This website cannot communicate with shipping carrier for delivery date, because
            cURL is not installed. The administrator of this website should install cURL.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    if (!USPS_USER_ID) {

        $message = 'This website cannot communicate with USPS to get delivery date, because a USPS Web Tools User ID could not be found in the site settings.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // Update zip codes to only contains the first 5 characters, because USPS will error otherwise.
    $organization_zip_code = mb_substr(ORGANIZATION_ZIP_CODE, 0, 5);
    $zip_code = mb_substr($zip_code, 0, 5);

    // Assume that the package will be shipped at the shipping end of day time, because we don't
    // currently have a better way of knowing when the package will be shipped.
    $accept_time = str_replace(':', '', ECOMMERCE_END_OF_DAY_TIME);

    // We send the request below so that USPS will return info for all services (MailClass: 0), so
    // we can store data for all services in cache table.  We do this in order to avoid having to
    // make an API request for each service, which would cause delays.

    $request =
        '<SDCGetLocationsRequest USERID="' . h(USPS_USER_ID) . '">
            <MailClass>0</MailClass>
            <OriginZIP>' . h($organization_zip_code) . '</OriginZIP>
            <DestinationZIP>' . h($zip_code) . '</DestinationZIP>
            <AcceptDate>' . h($ship_date) . '</AcceptDate>
            <AcceptTime>' . h($accept_time) . '</AcceptTime>
        </SDCGetLocationsRequest>';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,
        'http://production.shippingapis.com/ShippingAPI.dll?API=SDCGetLocations&XML=' .
        urlencode($request));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // We had issues in the past where the timeout did not work, when USPS' service
    // went down, and we were only using CURLOPT_TIMEOUT. The request would go on for
    // too long. We are adding CURLOPT_CONNECTTIMEOUT also to attempt to resolve that.
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // If there was a cURL problem, then log the error and return false.
    if (!$response or $curl_errno) {

        $message = 
            'An error occurred while trying to communicate with USPS for delivery date. ' .
            'cURL Error Number: ' . $curl_errno . '. ' .
            'cURL Error Message: ' . $curl_error . '. Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    $response_content = $response;

    $response = @simplexml_load_string($response);

    // If there was a problem processing the XML, then log the error and return false.
    if ($response === false) {

        $message = 'An error occurred while trying to communicate with USPS for delivery date. The XML response from USPS could not be processed. ' . $response_content . ' Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // If there was an error, then log and return false.
    if (mb_strtolower($response->getName()) == 'error') {

        $message = 
            'An error occurred while trying to communicate with USPS for delivery date: ' .
            $response->Description . ' Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // Get all USPS services from enabled shipping methods, so we know which services we need
    // to deal with.
    $enabled_services = db_values(
        "SELECT DISTINCT(service)
        FROM shipping_methods
        WHERE
            (SUBSTRING(service, 1, 4) = 'usps')
            AND (status = 'enabled')
            AND (start_time <= NOW())
            AND (end_time >= NOW())");

    $services = array();

    // If expedited services were returned, then loop through them.
    if ($response->Expedited->Commitment) {

        foreach($response->Expedited->Commitment as $service_info) {

            // Figure out the service from the mail class number.

            $service_value = '';

            if ($service_info->MailClass == 1) {
                $service_value = 'usps_express';

            } else if ($service_info->MailClass == 2) {
                $service_value = 'usps_priority';
            }

            $delivery_date = (string) $service_info->Location->SDD;

            // If a service was not found (i.e. we don't support the service), or there was no
            // delivery date, or no enabled shipping methods use this service, or we have already
            // added info for this service, then continue to next service.  For some reason, USPS
            // returns info for the same service multiple times. One might be for street and the
            // other one might be for po, however we don't see evidence that the delivery date is
            // different, so we are going to ignore the duplicate.
            if (
                !$service_value or !$delivery_date or !in_array($service_value, $enabled_services)
                or isset($services[$service_value])
            ) {
                continue;
            }

            // Add service to array so we can remember it for later.
            $services[$service_value] = array(
                'service' => $service_value,
                'delivery_date' => $delivery_date);
        }
    }

    // If non-expedited services were returned, then loop through them.
    if ($response->NonExpedited) {

        foreach($response->NonExpedited as $service_info) {

            // Figure out the service from the mail class number.

            $service_value = '';

            if ($service_info->MailClass == 6) {
                $service_value = 'usps_ground';
            }

            $delivery_date = (string) $service_info->SchedDlvryDate;

            // If a service was not found (i.e. we don't support the service), or there was no
            // delivery date, or no enabled shipping methods use this service, or we have already
            // added info for this service, then continue to next service.  For some reason, USPS
            // returns info for the same service multiple times. One might be for street and the
            // other one might be for po, however we don't see evidence that the delivery date is
            // different, so we are going to ignore the duplicate.
            if (
                !$service_value or !$delivery_date or !in_array($service_value, $enabled_services)
                or isset($services[$service_value])
            ) {
                continue;
            }

            // Add service to array so we can remember it for later.
            $services[$service_value] = array(
                'service' => $service_value,
                'delivery_date' => $delivery_date);
        }
    }

    // Prepare variable we will use to keep track of the delivery date for the service that this
    // request was originally made for.
    $service_delivery_date = '';

    // Loop through the services in order to add delivery dates to cache table.
    foreach ($services as $service_info) {

        // Delete any old delivery date in db cache table that might exist.
        db(
            "DELETE FROM shipping_delivery_dates
            WHERE
                (service = '" . e($service_info['service']) . "')
                AND (zip_code = '" . e($zip_code) . "')
                AND (ship_date = '" . e($ship_date) . "')");

        // Insert new delivery date into db cache table.
        db(
            "INSERT INTO shipping_delivery_dates (
                service,
                zip_code,
                ship_date,
                delivery_date,
                timestamp)
            VALUES (
                '" . e($service_info['service']) . "',
                '" . e($zip_code) . "',
                '" . e($ship_date) . "',
                '" . e($service_info['delivery_date']) . "',
                UNIX_TIMESTAMP())");

        // If this is the service that this request was originally made for, then remember the
        // delivery date, so we can respond later.
        if ($service_info['service'] == $service) {
            $service_delivery_date = $service_info['delivery_date'];
        }
    }

    // If USPS did not return a delivery date for the service that this request was originally
    // made for, then log and return error.
    if (!$service_delivery_date) {

        $message = 'Could not get a delivery date for ' . get_shipping_service_name($service) . ', because USPS did not return info for that service, for an unknown reason. This likely means that the carrier does not offer that service for the zip code (' . $zip_code . '). Request: ' . $request . ' Response: ' . $response_content;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // If we have gotten here, then everything worked, so return delivery date.
    return array(
        'status' => 'success',
        'delivery_date' => $service_delivery_date);
}

function get_fedex_delivery_date($properties) {

    $service = $properties['service'];
    $ship_date = $properties['ship_date'];
    $zip_code = trim($properties['zip_code']);
    $country = $properties['country'];
    $weight = $properties['weight'];

    if (!FEDEX) {
        $message = 'This website cannot communicate with FedEx to get delivery date, because FedEx is disabled in the site settings.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    if (!function_exists('curl_init')) {
        $message = 'This website cannot communicate with shipping carrier for delivery date, because
            cURL is not installed. The administrator of this website should install cURL.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // Get FedEx settings.
    $config = db_item(
        "SELECT
            fedex_key,
            fedex_password,
            fedex_account,
            fedex_meter
        FROM config");

    // If the FedEx login info is missing from the settings, then log and return false.
    if (
        !$config['fedex_key'] or !$config['fedex_password'] or !$config['fedex_account']
        or !$config['fedex_meter']
    ) {
        $message = 'This website cannot communicate with FedEx to get delivery date, because a FedEx Key, Password, Account Number, and/or Meter Number could not be found in the site settings.';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // If the country is US, then just get the first 5 digits of the zip code. The +4 part
    // probably does not make a difference for the delivery date and it would make the caching
    // less efficient.
    if ($country == 'US' or $country == 'USA') {
        $zip_code = mb_substr($zip_code, 0, 5);
    }

    // If no weight was passed, then someone has probably forgotten to enter a weight for a product,
    // so just set the weight to 1 lb.  We don't believe the weight affects the delivery date in
    // most circumstances, so this should be fine.
    if ($weight <= 0) {
        $weight = 1;
    }

    // We send the request below so that FedEx will return info for all services, so we can store
    // data for all services in cache table.  We do this in order to avoid having to make an API
    // request for each service, which would cause delays.
    //
    // We don't support SmartPost because FedEx does not support returning precise info for the
    // transit time for SmartPost.  FedEx always returns 2-7 days, no matter what the zip code is.
    // This is because FedEx SmartPost hands off the shipment to USPS, and USPS is not reliable, or
    // does not give info to FedEx.  One workaround is to set FedEx Ground instead and add 2 extra
    // handling days in the method, because SmartPost generally takes 1-2 days more than Ground,
    // on average.
    //
    // Below we set that the package will be shipped at the shipping end of day time, because we
    // don't currently have a better way of knowing when the package will be shipped.

    $request =
        '<?xml version="1.0"?>
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://fedex.com/ws/rate/v20">
            <SOAP-ENV:Body>
                <ns1:RateRequest>
                    <ns1:WebAuthenticationDetail>
                        <ns1:UserCredential>
                            <ns1:Key>' . h($config['fedex_key']) . '</ns1:Key>
                            <ns1:Password>' . h($config['fedex_password']) . '</ns1:Password>
                        </ns1:UserCredential>
                    </ns1:WebAuthenticationDetail>
                    <ns1:ClientDetail>
                        <ns1:AccountNumber>' . h($config['fedex_account']) . '</ns1:AccountNumber>
                        <ns1:MeterNumber>' . h($config['fedex_meter']) . '</ns1:MeterNumber>
                    </ns1:ClientDetail>
                    <ns1:Version>
                        <ns1:ServiceId>crs</ns1:ServiceId>
                        <ns1:Major>20</ns1:Major>
                        <ns1:Intermediate>0</ns1:Intermediate>
                        <ns1:Minor>0</ns1:Minor>
                    </ns1:Version>
                    <ns1:ReturnTransitAndCommit>true</ns1:ReturnTransitAndCommit>
                    <ns1:RequestedShipment>
                        <ns1:ShipTimestamp>' . h($ship_date) . 'T' . h(ECOMMERCE_END_OF_DAY_TIME) . ':00</ns1:ShipTimestamp>
                        <ns1:Shipper>
                            <ns1:Address>
                                <ns1:PostalCode>' . h(ORGANIZATION_ZIP_CODE) . '</ns1:PostalCode>
                                <ns1:CountryCode>US</ns1:CountryCode>
                            </ns1:Address>
                        </ns1:Shipper>
                        <ns1:Recipient>
                            <ns1:Address>
                                <ns1:PostalCode>' . h($zip_code) . '</ns1:PostalCode>
                                <ns1:CountryCode>' . h($country) . '</ns1:CountryCode>
                            </ns1:Address>
                        </ns1:Recipient>
                        <ns1:PackageCount>1</ns1:PackageCount>
                        <ns1:RequestedPackageLineItems>
                            <ns1:SequenceNumber>1</ns1:SequenceNumber>
                            <ns1:GroupPackageCount>1</ns1:GroupPackageCount>
                            <ns1:Weight>
                                <ns1:Units>LB</ns1:Units>
                                <ns1:Value>' . h($weight) . '</ns1:Value>
                            </ns1:Weight>
                        </ns1:RequestedPackageLineItems>
                    </ns1:RequestedShipment>
                </ns1:RateRequest>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://ws.fedex.com:443/web-services');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_POST, 1);
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // If there was a cURL problem, then log the error and return false.
    if (!$response or $curl_errno) {

        $message = 
            'An error occurred while trying to communicate with FedEx for delivery date. ' .
            'cURL Error Number: ' . $curl_errno . '. ' .
            'cURL Error Message: ' . $curl_error . '. Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    $response_content = $response;

    if (strpos($response, 'ERROR') !== false) {
        
        // Errors are written in SOAP envelop need to clean SOAP tags to read to xml.
        $response = str_ireplace('SOAP-ENV:', '', $response);
        $response = str_ireplace('SOAP:', '', $response);
        $response = str_ireplace('v20:', '', $response);
        $response  = simplexml_load_string($response);

        $message = 
            'An error occurred while trying to communicate with FedEx for delivery date: ' .
            (string) $response->Body->RateReply->Notifications->Message . ' Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    $soap_response = $response;

    // Convert SOAP to xml.
    $response = simplexml_load_string($response, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");
    $response = $response->xpath('//SOAP-ENV:Body');
    $response = $response[0];

    // If the response is not what we expect, then log and return error.
    if (!isset($response->RateReply->RateReplyDetails)) {

        $message = 
            'An error occurred while trying to communicate with FedEx for delivery date. ' .
            $soap_response . ' Request: ' . $request;

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // Get all FedEx services from enabled shipping methods, so we know which services we need
    // to deal with.
    $enabled_services = db_values(
        "SELECT DISTINCT(service)
        FROM shipping_methods
        WHERE
            (SUBSTRING(service, 1, 5) = 'fedex')
            AND (status = 'enabled')
            AND (start_time <= NOW())
            AND (end_time >= NOW())");

    // Prepare variable we will use to keep track of the delivery date for the service that this
    // request was originally made for.
    $service_delivery_date = '';

    // Loop through the different services to get delivery dates.
    foreach ($response->RateReply->RateReplyDetails as $rate_reply) {

        $service_type = (string) $rate_reply->ServiceType;

        $service_value = '';

        // Convert service type to our service value.
        switch ($service_type) {
            
            case 'FIRST_OVERNIGHT':
                $service_value = 'fedex_first_overnight';
                break;

            case 'PRIORITY_OVERNIGHT':
                $service_value = 'fedex_priority_overnight';
                break;

            case 'STANDARD_OVERNIGHT':
                $service_value = 'fedex_standard_overnight';
                break;

            case 'FEDEX_2_DAY_AM':
                $service_value = 'fedex_2_day_am';
                break;

            case 'FEDEX_2_DAY':
                $service_value = 'fedex_2_day';
                break;

            case 'FEDEX_EXPRESS_SAVER':
                $service_value = 'fedex_express_saver';
                break;

            case 'FEDEX_GROUND':
                $service_value = 'fedex_ground';
                break;
        }
    
        // If this is a service that we don't support, or no enabled shipping
        // methods use this service, then skip to next service.
        if (!$service_value or !in_array($service_value, $enabled_services)) {
            continue;
        }

        // If this is FedEx Ground, then we have to calculate the delivery date, because FedEx only
        // returns transit time.
        if ($service_value == 'fedex_ground') {
            
            $transit_time = (string) $rate_reply->TransitTime;

            // Convert string value ("ONE_DAY") to number (1).

            $transit_days = 0;

            switch ($transit_time) {
                case 'ONE_DAY': $transit_days = 1; break;
                case 'TWO_DAYS': $transit_days = 2; break;
                case 'THREE_DAYS': $transit_days = 3; break;
                case 'FOUR_DAYS': $transit_days = 4; break;
                case 'FIVE_DAYS': $transit_days = 5; break;
                case 'SIX_DAYS': $transit_days = 6; break;
                case 'SEVEN_DAYS': $transit_days = 7; break;
                case 'EIGHT_DAYS': $transit_days = 8; break;
                case 'NINE_DAYS': $transit_days = 9; break;
                case 'TEN_DAYS': $transit_days = 10; break;
                case 'ELEVEN_DAYS': $transit_days = 11; break;
                case 'TWELVE_DAYS': $transit_days = 12; break;
                case 'THIRTEEN_DAYS': $transit_days = 13; break;
                case 'FOURTEEN_DAYS': $transit_days = 14; break;
                case 'FIFTEEN_DAYS': $transit_days = 15; break;
                case 'SIXTEEN_DAYS': $transit_days = 16; break;
                case 'SEVENTEEN_DAYS': $transit_days = 17; break;
                case 'EIGHTEEN_DAYS': $transit_days = 18; break;
                case 'NINETEEN_DAYS': $transit_days = 19; break;
                case 'TWENTY_DAYS': $transit_days = 20; break;
            }

            // If the transit days could not be found, then skip to next service.
            if (!$transit_days) {
                continue;
            }

            // Get info for the FedEx Ground shipping method, so we know about transit on weekend
            // and excluded dates.  There could be multiple methods, but we will just get info
            // for first method we find, which should be good enough.
            $shipping_method = db_item(
                "SELECT
                    id,
                    transit_on_sunday,
                    transit_on_saturday
                FROM shipping_methods
                WHERE
                    (service = 'fedex_ground')
                    AND (status = 'enabled')
                    AND (start_time <= NOW())
                    AND (end_time >= NOW())
                LIMIT 1");

            // If a FedEx Ground method could not be found, then skip to next service.
            if (!$shipping_method) {
                continue;
            }

            $excluded_transit_dates = db_values(
                "SELECT date FROM excluded_transit_dates
                WHERE shipping_method_id = '" . e($shipping_method['id']) . "'");

            $delivery_date = calculate_delivery_date(array(
                'ship_date' => $ship_date,
                'transit_days' => $transit_days,
                'transit_on_sunday' => $shipping_method['transit_on_sunday'],
                'transit_on_saturday' => $shipping_method['transit_on_saturday'],
                'excluded_transit_dates' => $excluded_transit_dates));

        // Otherwise this service is not FedEx Ground, so get delivery date that FedEx returns.
        } else {

            $delivery_timestamp = (string) $rate_reply->DeliveryTimestamp;
            $delivery_date = date('Y-m-d', strtotime($delivery_timestamp));
        }

        // If a delivery date was not found, then skip to next service.
        if (!$delivery_date) {
            continue;
        }

        // Delete any old delivery date in db cache table that might exist.
        db(
            "DELETE FROM shipping_delivery_dates
            WHERE
                (service = '" . e($service_value) . "')
                AND (zip_code = '" . e($zip_code) . "')
                AND (ship_date = '" . e($ship_date) . "')");

        // Insert new delivery date into db cache table.
        db(
            "INSERT INTO shipping_delivery_dates (
                service,
                zip_code,
                ship_date,
                delivery_date,
                timestamp)
            VALUES (
                '" . e($service_value) . "',
                '" . e($zip_code) . "',
                '" . e($ship_date) . "',
                '" . e($delivery_date) . "',
                UNIX_TIMESTAMP())");

        // If this is the service that this request was originally made for, then remember the
        // delivery date, so we can respond later.
        if ($service_value == $service) {
            $service_delivery_date = $delivery_date;
        }
    }

    // If FedEx did not return a delivery date for the service that this request was originally
    // made for, then log and return error.
    if (!$service_delivery_date) {

        $message = 'Could not get a delivery date for ' . get_shipping_service_name($service) . ', because FedEx did not return info for that service, for an unknown reason. This likely means that the carrier does not offer that service for the zip code (' . $zip_code . ').';

        log_activity($message);

        return array(
            'status' => 'error',
            'message' => $message);
    }

    // If we have gotten here, then everything worked, so return delivery date.
    return array(
        'status' => 'success',
        'delivery_date' => $service_delivery_date);
}

// Give it a ship date and transit days and it will calculate the delivery date.

function calculate_delivery_date($properties) {

    $ship_date = $properties['ship_date'];
    $transit_days = $properties['transit_days'];
    $transit_on_sunday = $properties['transit_on_sunday'];
    $transit_on_saturday = $properties['transit_on_saturday'];
    $excluded_transit_dates = $properties['excluded_transit_dates'];

    // If excluded transit days was not passed or not valid, then just set to empty array, to avoid
    // error further below.
    if (!is_array($excluded_transit_dates)) {
        $excluded_transit_dates = array();
    }
    
    $date = '';
    $count = 0;
    
    // Calculate the delivery date by looping through days and determining if days are excluded.
    while ($count <= $transit_days) {

        // If date is blank, then set date to ship date.
        if ($date == '') {
            $date = $ship_date;
            
        // else date is not blank, so set date to next day
        } else {
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }
        
        $day_name = date('l', strtotime($date));
        
        // if date is not an excluded date, then increase count
        if (
            ($day_name != 'Sunday' or $transit_on_sunday)
            and ($day_name != 'Saturday' or $transit_on_saturday)
            and !in_array($date, $excluded_transit_dates)
        ) {
            $count++;
        }
    }
    
    return $date;
}

function get_shipping_realtime_rate($properties) {

    $ship_to_id = $properties['ship_to_id'];
    $service = $properties['service'];
    $realtime_rate = $properties['realtime_rate'];
    $state = $properties['state'];
    $zip_code = trim($properties['zip_code']);
    $items = $properties['items'];

    // If there is not a real-time rate service selected for this shipping method or real-time rate
    // is disabled for the method, then return a real-time rate of zero.
    if (!$service or mb_substr($service, 0, 5) == 'fedex' or !$realtime_rate) {
        return 0;
    }

    // If a ship to id was passed and a state or zip was not passed, then get info for recipient.
    if ($ship_to_id and (!$state or !$zip_code)) {

        $recipient = db_item(
            "SELECT
                state,
                zip_code
            FROM ship_tos
            WHERE id = '" . e($ship_to_id) . "'");
        
        $state = $recipient['state'];
        $zip_code = $recipient['zip_code'];

    }

    // Just get the first 5 digits of the zip code, because USPS does not support +4.
    // It appears that UPS might support +4, but we are just going to remove the +4
    // for UPS also, so the same zip code is used for both carriers.
    $zip_code = mb_substr($zip_code, 0, 5);

    // Prepare timestamp so we only get cached rates that are recent.
    $day_ago_timestamp = time() - 86400;

    // Get a random number between 1 and 100 in order to determine if we should delete old rates
    // from the db cache table.  There is a 1 in 100 chance that we will delete old rates each time
    // we calculate real-time rates for a recipient.  Old is considered more than a day old.

    $random_number = rand(1, 100);
    
    if ($random_number == 1) {
        db("DELETE FROM shipping_rates WHERE timestamp < '$day_ago_timestamp'");
    }

    // Loop through the items in order to prepare package items.  Package items are every single
    // item that needs to be considered when determing the rate of packaging.  This includes
    // all quantities for all items that do not get free shipping.

    $package_items = array();

    foreach ($items as $item) {

        // If this is a free-shipping item, then skip to next item.
        if ($item['free_shipping']) {
            continue;
        }

        // Add a package item for each quantity.
        for ($quantity = 1; $quantity <= $item['quantity']; $quantity++) { 
            $package_items[] = $item;
        }

    }

    // If there are no items to package, then return zero.
    if (!$package_items) {
        return 0;
    }

    $packages = array();

    // If there is more than one item or there is just one item and it requires a container,
    // then check if we should package item(s) in containers.
    if ((count($package_items) > 1) or $package_items[0]['container_required']) {

        // Get all enabled containers.  We get the largest containers first so that later when
        // we loop through each container to see if an item will fit in a container, that check
        // will be more efficient.
        $containers = db_items(
            "SELECT
                id,
                name,
                length,
                width,
                height,
                weight,
                cost
            FROM containers
            WHERE enabled = '1'
            ORDER BY length DESC, width DESC, height DESC");

        // If there is at least one enabled container, then determine if we should use them.
        if ($containers) {

            // Now, we need to figure out which items can fit in at least one container, and which
            // items do not fit in any containers.  We need to figure this out before we use
            // BoxPacker, because BoxPacker will throw an exception and fail if we pass it an item
            // that does not fit in any containers.

            $container_items = array();

            // Loop through each package item in order to determine if it fits in a container.
            foreach ($package_items as $item) {

                // Prepare the item dimensions in order from largest to smallest so we can compare
                // it to the container dimensions.
                $item_dimensions = array($item['length'], $item['width'], $item['height']);
                rsort($item_dimensions);

                $fits = false;

                // Loop through each container to see if this item will fit in a container.
                foreach ($containers as $container) {

                    // Prepare the container dimensions in order from largest to smallest so we can
                    // compare it to the item dimensions.
                    $container_dimensions = array(
                        $container['length'], $container['width'], $container['height']);
                    rsort($container_dimensions);

                    // If this item fits in this container, then remember that and break out of
                    // container loop.
                    if (
                        $item_dimensions[0] <= $container_dimensions[0]
                        and $item_dimensions[1] <= $container_dimensions[1]
                        and $item_dimensions[2] <= $container_dimensions[2]
                    ) {
                        $fits = true;
                        break;
                    }

                }

                // If this item fits in at least one container, then add it as a container item.
                if ($fits) {

                    $container_items[] = $item;

                // Otherwise, this item does not fit in any container, so add package just for it.
                } else {

                    $packages[] = array(
                        'weight' => $item['weight'],
                        'length' => $item['length'],
                        'width' => $item['width'],
                        'height' => $item['height'],
                        'rate_key' => get_rate_key($item),
                        'items' => array($item));

                }

            }

            // If there is at least one item that fits in a container, then use BoxPacker to figure
            // out how the items should be packed in containers.
            if ($container_items) {

                require_once(dirname(__FILE__) . '/boxpacker/init.php');

                $packer = new \DVDoug\BoxPacker\Packer();

                // Add all containers to BoxPacker.
                foreach ($containers as $key => $container) {
                    $packer->addBox(new \DVDoug\BoxPacker\TestBox(
                        $key,
                        $container['width'],
                        $container['length'],
                        $container['height'],
                        $container['weight'],
                        $container['width'],
                        $container['length'],
                        $container['height'],

                        // Use a large max weight, because we don't know the container's max weight.
                        9999999));
                }

                // Add all container items to BoxPacker.
                foreach ($container_items as $key => $item) {
                    $packer->addItem(new \DVDoug\BoxPacker\TestItem(
                        $key,
                        $item['width'],
                        $item['length'],
                        $item['height'],
                        $item['weight'],

                        // Tell BoxPacker that the item must lay flat.  This is the default/normal.
                        // This appears to mean that BoxPacker will only do "2D rotation",
                        // where only the length and width are switched for rotation testing.
                        // BoxPacker has a new feature called "3D rotation" (false)
                        // where there are more orientations (e.g. item is flipped over?).
                        // However, we found that "3D rotation" (false) had bugs, so we stopped
                        // using it.  For example, it incorrectly said 2 items (24x12x18) could
                        // fit in a 28x18x24 container.
                        true));
                }

                // Try to pack the items with BoxPacker.
                try {
                    $boxes = $packer->pack();

                // If there was a BoxPacker error, then log activity and return false, so we don't
                // report an incorrect real-time rate.
                } catch (Exception $e) {
                    log_activity(
                        'Container packer error: ' . $e->getMessage() . "\n" .
                        'Containers: ' . print_r($containers, true) . "\n" .
                        'Items: ' . print_r($container_items, true) . "\n" .
                        'Packer: ' . print_r($packer, true));

                    return false;
                }

                // Loop through the containers that BoxPacker packed, in order to add a package for
                // each.
                foreach ($boxes as $box) {

                    $items = $box->getItems();

                    // If BoxPacker only put one item in this container, then check if we should
                    // not use a container and just package it by itself.
                    if (count($items) == 1) {

                        // Loop through the items object in order to get info for the only item.
                        foreach ($items as $item) {

                            $item = $container_items[$item->getDescription()];

                            // If a container is not required for this item, then don't use the
                            // container and just package the item individually.
                            if (!$item['container_required']) {

                                $packages[] = array(
                                    'weight' => $item['weight'],
                                    'length' => $item['length'],
                                    'width' => $item['width'],
                                    'height' => $item['height'],
                                    'rate_key' => get_rate_key($item),
                                    'items' => array($item));

                                // We are done with this container, so skip to the next container.
                                continue 2;

                            }

                            break;
                            
                        }

                    }

                    // If we got here then that means the container, as BoxPacker packed it, is
                    // good, so let's package it.

                    // We are going to trust BoxPacker and use the combined weight and dimensions
                    // that they give us.
                    $weight = $box->getWeight();
                    $length = $box->getBox()->getOuterLength();
                    $width = $box->getBox()->getOuterWidth();
                    $height = $box->getBox()->getOuterDepth();

                    $package = array(
                        'weight' => $weight,
                        'length' => $length,
                        'width' => $width,
                        'height' => $height,
                        'rate_key' => get_rate_key(array(
                            'weight' => $weight,
                            'length' => $length,
                            'width' => $width,
                            'height' => $height)),
                        'container' => $containers[$box->getBox()->getReference()]);

                    // Let's add info about all the items to the package array, for debugging,
                    // so we know which items are packed in this package.

                    $package['items'] = array();

                    foreach ($items as $item) {
                        $package['items'][] = $container_items[$item->getDescription()];
                    }

                    $packages[] = $package;
                }
            }
        }
    }

    // If there are no packages at this point, then that means that containers were not used,
    // e.g. (maybe because there are no containers) so let's package all the items individually.
    if (!$packages) {

        // Loop through the package items in order to prepare the packages that are required.
        foreach ($package_items as $item) {

            $packages[] = array(
                'weight' => $item['weight'],
                'length' => $item['length'],
                'width' => $item['width'],
                'height' => $item['height'],
                'rate_key' => get_rate_key($item),
                'items' => array($item));
        }
    }

    // Used to store a rate for each unique package property combination
    // so that we don't have to make extra db queries for identical packages.
    $rates = array();

    // Prepare timestamp so we only get cached rates that are recent.
    $day_ago_timestamp = time() - 86400;

    // Used to keep track of the packages, that we don't have cached info for, that we will need
    // to ask the carrier about.
    $pending_packages = array();

    // Loop through the packages in order to get rates for the packages that we have cached
    // rates for, and to determine which are pending packages that we will need to ask the
    // carrier about.
    foreach ($packages as $key => $package) {

        // If we have already dealt with an identical package to this one, then we have already
        // figured out what we need, so skip to the next package.
        if (isset($rates[$package['rate_key']])) {
            continue;
        }

        // Check the cache table for a rate.
        $rate = db_value(
            "SELECT rate
            FROM shipping_rates
            WHERE
                (service = '" . e($service) . "')
                AND (zip_code = '" . e($zip_code) . "')
                AND (weight = '" . e($package['weight']) . "')
                AND (length = '" . e($package['length']) . "')
                AND (width = '" . e($package['width']) . "')
                AND (height = '" . e($package['height']) . "')
                AND (timestamp > '$day_ago_timestamp')");

        // Store the rate in the rates array.  We store a blank rate in the rates array if we
        // do not find a rate, so if there is an identical package in a future loop, then we don't
        // have to check the db again.
        $rates[$package['rate_key']] = $rate;

        // If a rate was not found, then add this package to the pending packages, so we can ask
        // the carrier for the rate.
        if ($rate == '') {
            $pending_packages[] = $package;
        }

    }

    // If there are pending packages, then ask the carrier for the rate.
    if ($pending_packages) {

        if (!function_exists('curl_init')) {
            log_activity(
                'This website cannot communicate with shipping carrier for real-time rates, because
                cURL is not installed. The administrator of this website should install cURL.');
            return false;
        }

        // Figure out the carrier for this service.

        $carrier = '';

        if (mb_substr($service, 0, 4) == 'usps') {
            $carrier = 'usps';

        } else if (mb_substr($service, 0, 3) == 'ups') {
            $carrier = 'ups';
        }

        // Used to store all the new rates that we collect from the carrier for various packages
        // and services.  We will use this array to store the rate info so that we can
        // add info to db after we are done communicating with the carrier.
        $new_rates = array();

        // Get all services from enabled shipping methods, so we know which services we need
        // to deal with.  For example, if there is no enabled shipping method for UPS Ground,
        // then we don't want to hurt performance by dealing with rate for UPS Ground, or fill
        // cache table with unnecessary data.
        $enabled_services = db_values(
            "SELECT DISTINCT(service)
            FROM shipping_methods
            WHERE
                (service != '')
                AND (status = 'enabled')
                AND (start_time <= NOW())
                AND (end_time >= NOW())");

        switch ($carrier) {

            case 'usps':

                if (!USPS_USER_ID) {
                    log_activity(
                        'This website cannot communicate with USPS for real-time rates, because a
                        USPS Web Tools User ID could not be found in the site settings.');
                    return false;
                }

                $request =
                    '<RateV4Request USERID="' . h(USPS_USER_ID) . '">';

                // Get the first 5 digits of the organization zip code, because USPS returns an
                // error if you include +4.
                $organization_zip_code = mb_substr(ORGANIZATION_ZIP_CODE, 0, 5);

                // Loop through the pending packages in order to prepare XML for each.
                foreach ($pending_packages as $key => $package) {

                    // If any dimension is over 12 inches, then USPS considers the package size
                    // to be "LARGE", so prepare data for that.
                    if (
                        ($package['length'] > 12)
                        or ($package['width'] > 12)
                        or ($package['height'] > 12)
                    ) {

                        // The size is large, so USPS requires us to set the container
                        // in that case for some reason.  Otherwise, we just leave it blank.
                        $container = 'RECTANGULAR';

                        $size = 'LARGE';

                    } else {

                        // The size is regular, so we leave the container blank (i.e. "variable"),
                        // because USPS does not allow "RECTANGULAR" for a regular size, for some
                        // reason.
                        $container = '';

                        $size = 'REGULAR';

                    }

                    // Determine if USPS considers the package to be machinable.
                    if (
                        ($package['length'] >= 6)
                        and ($package['length'] <= 34)
                        and ($package['width'] >= 3)
                        and ($package['width'] <= 17)
                        and ($package['height'] >= 0.25)
                        and ($package['height'] <= 17)
                        and ($package['weight'] >= 0.375)
                        and ($package['weight'] <= 35)
                    ) {
                        $machinable = 'true';
                    } else {
                        $machinable = 'false';
                    }

                    // We request rates for all services, and not just the one service that we are
                    // interested in here, in order to minimize carrier API requests.
                    // The rates for the other services will get added to the cache table,
                    // so future requests for the other services will benefit.

                    $request .=
                        '<Package ID="' . $key . '">
                            <Service>All</Service>
                            <ZipOrigination>' . h($organization_zip_code) . '</ZipOrigination>
                            <ZipDestination>' . h($zip_code) . '</ZipDestination>
                            <Pounds>' . h($package['weight']) . '</Pounds>
                            <Ounces>0</Ounces>
                            <Container>' . $container . '</Container>
                            <Size>' . $size . '</Size>
                            <Width>' . h($package['width']) . '</Width>
                            <Length>' . h($package['length']) . '</Length>
                            <Height>' . h($package['height']) . '</Height>
                            <Machinable>' . $machinable . '</Machinable>
                        </Package>';

                }
                    
                $request .= '</RateV4Request>';

                log_activity($request);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,
                    'http://production.shippingapis.com/ShippingAPI.dll?API=RateV4&XML=' .
                    urlencode($request));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                // We had issues in the past where the timeout did not work, when USPS' service
                // went down, and we were only using CURLOPT_TIMEOUT. The request would go on for
                // too long. We are adding CURLOPT_CONNECTTIMEOUT also to attempt to resolve that.
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                $response = curl_exec($ch);
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);
                curl_close($ch);

                log_activity($response);

                // If there was a cURL problem, then log the error and return false.
                if ($curl_errno) {
                    log_activity(
                        'An error occurred while trying to communicate with USPS for real-time rates.' .
                        'cURL Error Number: ' . $curl_errno . '. ' .
                        'cURL Error Message: ' . $curl_error . '.');
                    return false;
                }

                // Check if there is an error.
                preg_match_all('/<error>(.*?)<\/error>/msi', $response, $errors, PREG_SET_ORDER);

                // If there was at least one error, then log errors and return false.
                if ($errors) {

                    $log_error = '';

                    // Loop through the errors so we can add each error to log message.
                    foreach ($errors as $error) {

                        preg_match('/<number>(.*?)<\/number>/msi', $error[1], $match);
                        $number = unhtmlspecialchars($match[1]);
                        
                        preg_match('/<description>(.*?)<\/description>/msi', $error[1], $match);
                        $description = unhtmlspecialchars($match[1]);

                        $log_error .=
                            ' USPS Error Number: ' . $number . '. ' .
                            'USPS Error Message: ' . $description;

                    }

                    log_activity(
                        'An error occurred while trying to communicate with USPS for real-time
                        rates.' . $log_error . ' Request: ' . $request);

                    return false;

                }

                // Loop through the pending packages in order to get the rates out of the XML.
                foreach ($pending_packages as $key => $package) {

                    // Try to find the response for this package in the XMl.
                    preg_match(
                        '/<Package ID="' . $key . '">(.*?)<\/Package>/msi', $response, $package_match);

                    // If USPS did not return info for this package, then something went wrong,
                    // so log and return false.
                    if (!$package_match) {
                        log_activity(
                            'An error occurred while trying to communicate with USPS for real-time
                            rates.  We could not find info for a package in the USPS response. ' .
                            'Request: ' . $request . ' ' .
                            'Response: ' . $response);
                        return false;
                    }

                    // Get all postage containers from response that contain service and rate.
                    preg_match_all(
                        '/<Postage(.*?)>(.*?)<\/Postage>/msi',
                        $package_match[1], $postages, PREG_SET_ORDER);

                    // Loop through all the postages responses, in order to get rate.
                    foreach ($postages as $postage) {

                        // Get the class id so we can figure out the service.
                        preg_match('/CLASSID="(.*?)"/si', $postage[1], $class_id_match);
                        $class_id = $class_id_match[1];

                        // If we could not find the class id for some reason, then skip to the next
                        // postage.
                        if ($class_id == '') {
                            continue;
                        }

                        $rate_service = '';

                        // Get the service from the class id.  We only support some services.
                        switch ($class_id) {
                            
                            case 1:
                                $rate_service = 'usps_priority';
                                break;

                            case 3:
                                $rate_service = 'usps_express';
                                break;

                            case 4:
                                $rate_service = 'usps_ground';
                                break;

                        }

                        // If this is a service that we don't support, or no enabled shipping
                        // methods use this service, then skip to next postage.
                        if (!$rate_service or !in_array($rate_service, $enabled_services)) {
                            continue;
                        }

                        // Get the rate.
                        preg_match('/<Rate>(.*?)<\/Rate>/si', $postage[2], $rate_match);
                        $rate = $rate_match[1];

                        // If we could not find the rate for some reason, then skip to the next
                        // postage.
                        if ($rate == '') {
                            continue;
                        }

                        // Convert to cents.
                        $rate = $rate * 100;

                        // Store the info for this new rate, so later we can add rate to db.
                        $new_rates[] = array(
                            'service' => $rate_service,
                            'weight' => $package['weight'],
                            'length' => $package['length'],
                            'width' => $package['width'],
                            'height' => $package['height'],
                            'rate_key' => $package['rate_key'],
                            'rate' => $rate);
                        
                    }

                }

                break;
            
            case 'ups':

                if (!UPS) {
                    log_activity('This website cannot communicate with UPS for real-time rates, because UPS is disabled in the site settings.');
                    return false;
                }
                
                // Get UPS settings, because we won't have global constants for them, unlike USPS,
                // for performance reasons.
                $config = db_item(
                    "SELECT
                        ups_key,
                        ups_user_id,
                        ups_password,
                        ups_account
                    FROM config");

                if (!$config['ups_key'] or !$config['ups_user_id'] or !$config['ups_password']) {
                    log_activity(
                        'This website cannot communicate with UPS for real-time rates, because a ' .
                        'UPS Access Key, User ID, and/or Password could not be found in the site ' .
                        'settings.');
                    return false;
                }

                // UPS does not support passing multiple packages and showing the negotiated rate
                // for each package, so we are going to send an API request for each package.
                // If you send multiple packages in the same request, then UPS will only show a
                // total negotiated rate for all packages, which creates issues for us when dealing
                // with the cache table and etc.  We need to know the negotiated rate for each
                // package.

                $rate_information = '';
                $shipper_number = '';

                // If there is an account number in the site settings, then enable negotiated rates.
                if ($config['ups_account']) {

                    $rate_information =
                        '<RateInformation>
                            <NegotiatedRatesIndicator/>
                        </RateInformation>';

                    $shipper_number = '<ShipperNumber>' . h($config['ups_account']) . '</ShipperNumber>';

                }

                $state_province_code = '';

                // If there is a state in the site settings, then prepare to pass that to UPS,
                // because UPS might give more accurate info if state is provided.
                if (ORGANIZATION_STATE) {

                    $organization_state = ORGANIZATION_STATE;

                    // If the state in the site settings appears to be the name of the state then
                    // check state table for 2-char state code, because that is what UPS requires.
                    if (mb_strlen($organization_state) > 2) {

                        $organization_state_code = db_value(
                            "SELECT code
                            FROM states
                            WHERE name = '" . e($organization_state) . "'");

                        if ($organization_state_code) {
                            $organization_state = $organization_state_code;
                        }

                    }

                    $state_province_code = '<StateProvinceCode>' . h($organization_state) . '</StateProvinceCode>';

                }

                // We request rates for all services (RequestOption: Shop), and not just the
                // one service that we are interested in here, in order to minimize carrier API
                // requests. The rates for the other services will get added to the cache table,
                // so future requests for the other services will benefit.

                // Prepare the header request info that will be the same for all packages.
                $request_header =
                    '<?xml version="1.0" ?>
                    <AccessRequest xml:lang="en-US">
                        <AccessLicenseNumber>' . h($config['ups_key']) . '</AccessLicenseNumber>
                        <UserId>' . h($config['ups_user_id']) . '</UserId>
                        <Password>' . h($config['ups_password']) . '</Password>
                    </AccessRequest>
                    <?xml version="1.0" ?>
                    <RatingServiceSelectionRequest xml:lang="en-US">
                        <Request>
                            <RequestAction>Rate</RequestAction>
                            <RequestOption>Shop</RequestOption>
                        </Request>
                        <Shipment>
                            ' . $rate_information . '
                            <Shipper>
                                ' . $shipper_number . '
                                <Address>
                                    <PostalCode>' . h(ORGANIZATION_ZIP_CODE) . '</PostalCode>
                                    ' . $state_province_code . '
                                    <CountryCode>US</CountryCode>
                                </Address>
                            </Shipper>
                            <ShipTo>
                                <Address>
                                    <PostalCode>' . h($zip_code) . '</PostalCode>
                                    <StateProvinceCode>' . h($state) . '</StateProvinceCode>
                                    <CountryCode>US</CountryCode>
                                </Address>
                            </ShipTo>';

                $request_footer =
                    '    </Shipment>
                    </RatingServiceSelectionRequest>';

                foreach ($pending_packages as $package) {

                    // UPS returns an error if a dimension or weight is greater than 6 chars,
                    // so let's reduce the length of those values, and round if necessary.

                    // Remove extra zeros on the end.
                    $length = $package['length'] + 0;
                    $width = $package['width'] + 0;
                    $height = $package['height'] + 0;
                    $weight = $package['weight'] + 0;

                    if (strlen($length) > 6) {
                        $length = round($length, 2);
                    }

                    if (strlen($width) > 6) {
                        $width = round($width, 2);
                    }

                    if (strlen($height) > 6) {
                        $height = round($height, 2);
                    }

                    if (strlen($weight) > 6) {
                        $weight = round($weight, 2);
                    }

                    $request =
                        $request_header . '
                        <Package>
                            <PackagingType>
                                <Code>02</Code>
                            </PackagingType>
                            <Dimensions>
                                <UnitOfMeasurement>
                                    <Code>IN</Code>
                                </UnitOfMeasurement>
                                <Length>' . h($length) . '</Length>
                                <Width>' . h($width) . '</Width>
                                <Height>' . h($height) . '</Height>
                            </Dimensions>
                            <PackageWeight>
                                <UnitOfMeasurement>
                                    <Code>LBS</Code>
                                </UnitOfMeasurement>
                                <Weight>' . h($weight) . '</Weight>
                            </PackageWeight>
                        </Package>
                        ' . $request_footer;

                    log_activity($request);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://onlinetools.ups.com/ups.app/xml/Rate');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    $response = curl_exec($ch);
                    $curl_errno = curl_errno($ch);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    log_activity($response);

                    // If there was a cURL problem, then log the error and return false.
                    if ($curl_errno) {
                        log_activity(
                            'An error occurred while trying to communicate with UPS for real-time rates.' .
                            'cURL Error Number: ' . $curl_errno . '. ' .
                            'cURL Error Message: ' . $curl_error . '.');
                        return false;
                    }

                    // Get the response status code in order to determine if there was an error.
                    preg_match('/<ResponseStatusCode>(.*?)<\/ResponseStatusCode>/si', $response, $match);

                    // If there was an error, then log error(s) and return false.
                    if ($match[1] != '1') {

                        // Get all errors.  Normally, it appears there is just one error per
                        // response, however it appears multiple errors can technically appear,
                        // according to the docs, so we check for multiple errors.
                        preg_match_all('/<Error>(.*?)<\/Error>/si', $response, $errors, PREG_SET_ORDER);

                        $log_error = '';

                        // Loop through the errors so we can add each error to log message.
                        foreach ($errors as $error) {

                            preg_match('/<ErrorCode>(.*?)<\/ErrorCode>/si', $error[1], $match);
                            $code = unhtmlspecialchars($match[1]);
                            
                            preg_match('/<ErrorDescription>(.*?)<\/ErrorDescription>/si', $error[1], $match);
                            $description = unhtmlspecialchars($match[1]);

                            $log_error .=
                                ' UPS Error Code: ' . $code . '. ' .
                                'UPS Error Description: ' . $description;

                        }

                        log_activity(
                            'An error occurred while trying to communicate with UPS for real-time
                            rates.' . $log_error . ' Request: ' . $request);

                        return false;

                    }

                    // Get all rated shipments. There is one for each service.
                    preg_match_all('/<RatedShipment>(.*?)<\/RatedShipment>/si', $response,
                        $rated_shipments, PREG_SET_ORDER);

                    // Loop through all rated shipments in order to get rate for each service.
                    foreach ($rated_shipments as $rated_shipment) {

                        // Get service code.
                        preg_match('/<Service>.*?<Code>(.*?)<\/Code>.*?<\/Service>/si',
                            $rated_shipment[1], $match);
                        $service_code = $match[1];

                        $rate_service = '';

                        // Get the service from the service code.  We only support domestic services.
                        switch ($service_code) {
                            
                            case '01':
                                $rate_service = 'ups_next_day_air';
                                break;

                            case '14':
                                $rate_service = 'ups_next_day_air_early';
                                break;

                            case '13':
                                $rate_service = 'ups_next_day_air_saver';
                                break;

                            case '02':
                                $rate_service = 'ups_2nd_day_air';
                                break;

                            case '59':
                                $rate_service = 'ups_2nd_day_air_am';
                                break;

                            case '12':
                                $rate_service = 'ups_3_day_select';
                                break;

                            case '03':
                                $rate_service = 'ups_ground';
                                break;

                        }

                        // If this is a service that we don't support, or no enabled shipping
                        // methods use this service, then skip to next service.
                        if (!$rate_service or !in_array($rate_service, $enabled_services)) {
                            continue;
                        }

                        $rate = '';

                        // If we requested negotiated rates, then look for a negotiated rate first.
                        if ($config['ups_account']) {
                            preg_match('/<NegotiatedRates>.*?<NetSummaryCharges>.*?<GrandTotal>.*?<MonetaryValue>(.*?)<\/MonetaryValue>.*?<\/GrandTotal>.*?<\/NetSummaryCharges>.*?<\/NegotiatedRates>/si',
                                $rated_shipment[1], $match);
                            $rate = $match[1];
                        }

                        // If we did not find a negotiated rate, then look for a standard rate.
                        if ($rate == '') {
                            preg_match('/<TotalCharges>.*?<MonetaryValue>(.*?)<\/MonetaryValue>.*?<\/TotalCharges>/si',
                            $rated_shipment[1], $match);
                            $rate = $match[1];
                        }

                        // If we could not find the rate for some reason, then skip to the next
                        // service.
                        if ($rate == '') {
                            continue;
                        }

                        // Convert to cents.
                        $rate = $rate * 100;

                        // Store the info for this new rate, so later we can add rate to db.
                        $new_rates[] = array(
                            'service' => $rate_service,
                            'weight' => $package['weight'],
                            'length' => $package['length'],
                            'width' => $package['width'],
                            'height' => $package['height'],
                            'rate_key' => $package['rate_key'],
                            'rate' => $rate);

                    }

                }
                
                break;
        }

        // Loop through the new rates that we found, in order to add them to array and db cache
        // table.
        foreach ($new_rates as $rate) {
            
            // If the service for this rate matches the service we are dealing with now,
            // then add the rate to the rates array, so that later we can figure out the rate for
            // each package.
            if ($rate['service'] == $service) {
                $rates[$rate['rate_key']] = $rate['rate'];
            }

            // Delete any old rates in db cache table that might exist.
            db(
                "DELETE FROM shipping_rates
                WHERE
                    (service = '" . e($rate['service']) . "')
                    AND (zip_code = '" . e($zip_code) . "')
                    AND (weight = '" . e($rate['weight']) . "')
                    AND (length = '" . e($rate['length']) . "')
                    AND (width = '" . e($rate['width']) . "')
                    AND (height = '" . e($rate['height']) . "')");

            // Insert new rate into db cache table.
            db(
                "INSERT INTO shipping_rates (
                    service,
                    zip_code,
                    weight,
                    length,
                    width,
                    height,
                    rate,
                    timestamp)
                VALUES (
                    '" . e($rate['service']) . "',
                    '" . e($zip_code) . "',
                    '" . e($rate['weight']) . "',
                    '" . e($rate['length']) . "',
                    '" . e($rate['width']) . "',
                    '" . e($rate['height']) . "',
                    '" . e($rate['rate']) . "',
                    UNIX_TIMESTAMP())");
        }
    }

    $rate = 0;
    $error = false;
    $description = '';

    // Now that we have rates for all packages, loop through packages to calculate total rate.
    foreach ($packages as $key => $package) {

        $package['rate'] = $rates[$package['rate_key']];

        // If we can't find the rate in the rates array, then some issue happened, so mark error.
        // This might happen for example if the service is USPS Ground and the recipient's address
        // does not support Ground (e.g. too close to the origination address).
        if ($package['rate'] == '') {
            $package['error'] = true;
            $error = true;

        } else {

            // If this package is a container and it has a cost, then add cost to the rate.
            if ($package['container']['cost']) {
                $package['rate'] += $package['container']['cost'];
            }

            $rate += $package['rate'];

            // If we are updating a recipient with this real-time rate, rather than just calculating
            // a rate for the list of methods on the shipping method screen, then prepare a
            // description of packages for admin to view on view order screen.

            if ($ship_to_id) {

                if ($description != '') {
                    $description .= ', ';
                }

                // If this package is a container of items, then describe package in a certain way.
                if ($package['container']) {

                    $description .=
                        $package['container']['name'] .
                        ' (' . prepare_amount($package['rate'] / 100) . ', ';

                    foreach ($package['items'] as $item_key => $item) {

                        if ($item_key != 0) {
                            $description .= ', ';
                        }

                        $description .= $item['name'];

                    }

                    $description .= ')';

                // Otherwise this package is just one item, so describe more simply.
                } else {

                    $description .=
                        $package['items'][0]['name'] .
                        ' (' . prepare_amount($package['rate'] / 100) . ')';
                }
            }
        }

        $packages[$key] = $package;
    }

    log_activity(
        'Real-time shipping rate calculation' . "\n" .
        'Total Rate: ' . prepare_amount($rate / 100) . "\n" .
        'Order ID: ' . $_SESSION['ecommerce']['order_id'] . "\n" .
        'Ship To ID: ' . $ship_to_id . "\n" .
        'Service: ' . get_shipping_service_name($service) . "\n" .
        'State: ' . $state . "\n" .
        'Zip Code: ' . $zip_code . "\n" .
        'Error: ' . $error . "\n" .
        'Packages: ' . print_r($packages, true));

    if ($error) {
        return false;
    }

    // If there is a description of packages, then add it to recipient, so admin can view it on
    // view order screen.
    if ($description != '') {
        db(
            "UPDATE ship_tos SET packages = '" . e($description) . "'
            WHERE id = '" . e($ship_to_id) . "'");
    }

    return $rate;

}

// Returns a string with the weight, length, width, and height that can be used for comparions.

function get_rate_key($properties) {

    // We add zero in order to remove extra zeros on the end of the decimal, so that we have issues
    // with "1.10" not matching "1.1".
    return
        ($properties['weight']+0) . '_' .
        ($properties['length']+0) . '_' .
        ($properties['width']+0) . '_' .
        ($properties['height']+0);

}

// Figure out the shipping method base rate for a recipient based on
// whether variable base rate is enabled and the recipient subtotal.
function get_shipping_method_base_rate($properties) {

    $base_rate = $properties['base_rate'];
    $variable_base_rate = $properties['variable_base_rate'];
    $base_rate_2 = $properties['base_rate_2'];
    $base_rate_2_subtotal = $properties['base_rate_2_subtotal'];
    $base_rate_3 = $properties['base_rate_3'];
    $base_rate_3_subtotal = $properties['base_rate_3_subtotal'];
    $base_rate_4 = $properties['base_rate_4'];
    $base_rate_4_subtotal = $properties['base_rate_4_subtotal'];
    $ship_to_id = $properties['ship_to_id'];

    // If variable base rate is disabled for the shipping method,
    // then return the normal base rate.
    if (!$variable_base_rate) {
        return $base_rate;
    }

    $recipient_subtotal = db_value(
        "SELECT SUM(price * CAST(quantity AS signed))
        FROM order_items
        WHERE ship_to_id = '" . e($ship_to_id) . "'");

    // If there is not a 2nd base rate, or if the recipient total is less than
    // the 2nd base rate, then return the base rate.
    if (
        (!$base_rate_2_subtotal)
        || ($recipient_subtotal < $base_rate_2_subtotal)
    ) {
        return $base_rate;

    // Otherwise if there is not a 3rd base rate, or if the recipient total is less than
    // the 3rd base rate, then return the 2nd base rate.
    } else if (
        (!$base_rate_3_subtotal)
        || ($recipient_subtotal < $base_rate_3_subtotal)
    ) {
        return $base_rate_2;

    // Otherwise if there is not a 4th base rate, or if the recipient total is less than
    // the 4th base rate, then return the 3rd base rate.
    } else if (
        (!$base_rate_4_subtotal)
        || ($recipient_subtotal < $base_rate_4_subtotal)
    ) {
        return $base_rate_3;

    // Otherwise return the 4th base rate.
    } else {
        return $base_rate_4;
    }
}

// Get all the available shipping methods, in proper order, with pricing, for the shipping address
// and arrival date that visitor selected, so they can be shown to the visitor for selection.
// This is used by the API and JS on express order

function get_shipping_methods($properties) {

    $ship_to_id = $properties['ship_to_id'];
    $address_1 = $properties['address_1'];
    $state = $properties['state'];
    $zip_code = trim($properties['zip_code']);
    $country = $properties['country'];
    $arrival_date_id = $properties['arrival_date_id'];
    $arrival_date = $properties['arrival_date'];

    $order_id = $_SESSION['ecommerce']['order_id'];

    if (!$order_id or !$ship_to_id) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, the order id or ship to id is missing.');
    }

    // If an arrival date id was passed, then get info for arrival date
    if ($arrival_date_id) {

        // Get information about arrival date
        $arrival_date_item = db_item(
            "SELECT id, arrival_date, custom
            FROM arrival_dates
            WHERE id = '" . e($arrival_date_id) . "'");

        // If an arrival date was found, then prepare info
        if ($arrival_date_item) {

            // If the arrival date allows a custom date, then use date that was passed and convert
            // to y-m-d.
            if ($arrival_date_item['custom']) {

                // If the date is not valid, then return error
                if (!validate_date($arrival_date)) {
                    return array(
                        'status' => 'error',
                        'message' => 'Sorry, the requested arrival date is not valid.');
                }

                // Convert arrival date to y-m-d, because code below requires that
                $arrival_date = prepare_form_data_for_input($arrival_date, 'date');

            // Otherwise, the arrival date does not allow a custom date, so use standard date
            } else {
                $arrival_date = $arrival_date_item['arrival_date'];
            }

        // Otherwise, an arrival date was not found, so clear info.
        } else {
            $arrival_date_id = 0;
            $arrival_date = '0000-00-00';
        }
    }

    // If the arrival date is empty for whatever reason, then set it to the default At Once/no
    // arrival date (i.e. 0000-00-00)
    if (!$arrival_date) {
        $arrival_date = '0000-00-00';
    }

    // Get all items in cart for this ship to.
    $items = db_items(
        "SELECT
            order_items.id,
            order_items.product_id,
            order_items.product_name AS name,
            order_items.quantity,
            order_items.price / 100 AS price,
            order_items.discounted_by_offer,
            order_items.calendar_event_id,
            order_items.recurrence_number,
            products.image_name,
            products.short_description,
            products.full_description,
            products.selection_type,
            products.inventory,
            products.inventory_quantity,
            products.out_of_stock_message,
            products.price / 100 AS product_price,
            products.weight,
            products.primary_weight_points,
            products.secondary_weight_points,
            products.length,
            products.width,
            products.height,
            products.container_required,
            products.free_shipping,
            products.extra_shipping_cost
        FROM order_items
        LEFT JOIN products ON products.id = order_items.product_id
        WHERE
            (order_items.order_id = '" . e($order_id) . "')
            AND (order_items.ship_to_id = '" . e($ship_to_id) . "')
        ORDER BY order_items.id");

    // Loop through the items for this recipient in order to determine if any are not valid for
    // the destination.
    foreach ($items as $item) {
        // If product is not valid for destination then return error
        if (!validate_product_for_destination($item['product_id'], $country, $state)) {

            // If product restriction message is set, then use it
            if (ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE) {
                $message = ECOMMERCE_PRODUCT_RESTRICTION_MESSAGE;

            // Otherwise product restriction message is not set, so use default message
            } else {
                $message = 'Sorry, this item cannot be delivered to the specified shipping address. Please remove it from your order to continue.';
            }

            $message .= ' (' . $item['name'] . ' - ' . $item['short_description'] . ')';

            return array(
                'status' => 'error',
                'message' => $message);
        }
    }

    /* begin: find all valid shipping methods for this recipient's shipping address and requested arrival date */

    $shipping_methods = array();

    $address_type = get_address_type($address_1);
    $address_type = str_replace(' ', '_', $address_type);
    
    // get the current day of the week
    $day_of_week = mb_strtolower(date('l'));
    
    // get all valid shipping methods where address type is correct
    $shipping_methods = db_values(
        "SELECT id
        FROM shipping_methods
        WHERE
            (status = 'enabled')
            AND (start_time <= NOW())
            AND (end_time >= NOW())
            " . get_protected_shipping_method_filter() . "
            AND ($address_type = 1)
            AND (available_on_" . $day_of_week . " = '1')
            AND ((available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))");

    // If no shipping methods could be found, then return error
    if (!$shipping_methods) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not find a shipping method for this recipient. There may be no enabled shipping methods in general or no enabled methods for your address type (street or PO box).');
    }

    // get valid zones
    $zones = get_valid_zones($ship_to_id, $country, $state);

    // foreach valid shipping method
    foreach ($shipping_methods as $key => $shipping_method_id) {
        $zone_found = false;

        // foreach valid zone
        foreach ($zones as $zone_id) {
            // see if this zone is allowed for this shipping method
            $query = "SELECT shipping_method_id
                     FROM shipping_methods_zones_xref
                     WHERE (shipping_method_id = $shipping_method_id) AND (zone_id = '$zone_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // if this zone is allowed for this shipping method, remember that
            if (mysqli_num_rows($result) > 0) {
                $zone_found = true;
                // we have found a valid zone for this shipping method, so the shipping method is valid, so break
                break;
            }
        }

        // if a valid zone was not found for this shipping method, then this shipping method is no longer valid
        if ($zone_found == false) {
            unset($shipping_methods[$key]);
        }
    }
    
    /* begin: if necessary, work out issue with no intersecting shipping methods being found for all products */
    
    if (!$shipping_methods) {

        /*
           At this point, we know that all order items are valid for destination,
           however no shipping methods intercepted for all order items, so we need to force shipping methods to be used.
           We force the shipping methods by the following.  We find the greatest transit time allowed for each order item,
           by looking at all of the shipping methods for an order item. We then use the shipping methods for the order item
           that had the lowest transit time.
        */
        
        $zones_for_destination = get_valid_zones_for_destination($country, $state);
        
        foreach ($items as $key => $value) {
            
            // Get zones that are allowed for this item.
            $zones_for_order_item = db_values(
                "SELECT zone_id
                FROM products_zones_xref
                WHERE product_id = '" . e($items[$key]['product_id']) . "'");
            
            // Get zones that are valid for both item and destination.
            $valid_zones = array_intersect($zones_for_order_item, $zones_for_destination);

            // If there are no valid zones, the skip to next item.
            if (!$valid_zones) {
                continue;
            }

            $sql_zones = "";

            foreach ($valid_zones as $zone_id) {

                if ($sql_zones) {
                    $sql_zones .= " OR ";
                }

                $sql_zones .= "shipping_methods_zones_xref.zone_id = '" . e($zone_id) . "'";
            }
            
            /* begin: get valid shipping methods for order item and find largest base transit days */
            
            $query = "SELECT
                        DISTINCT(shipping_methods_zones_xref.shipping_method_id),
                        shipping_methods.base_transit_days
                     FROM shipping_methods_zones_xref
                     LEFT JOIN shipping_methods on shipping_methods_zones_xref.shipping_method_id = shipping_methods.id
                     WHERE
                        ($sql_zones)
                        AND (shipping_methods.status = 'enabled')
                        AND (shipping_methods.start_time <= NOW())
                        AND (shipping_methods.end_time >= NOW())
                        " . get_protected_shipping_method_filter() . "
                        AND ($address_type = 1)
                        AND (shipping_methods.available_on_" . $day_of_week . " = '1')
                        AND ((shipping_methods.available_on_" . $day_of_week . "_cutoff_time = '00:00:00') OR (shipping_methods.available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME()))";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // get prepared to store shipping methods for this order item
            $items[$key]['shipping_methods'] = array();
            
            $largest_transit_for_order_item = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {
                $shipping_method_id = $row['shipping_method_id'];
                $base_transit_days = $row['base_transit_days'];
                
                // if the base transit days for this shipping method are larger than the largest base transit days so far, set largest base transit days
                if ($base_transit_days > $largest_transit_for_order_item) {
                    $largest_transit_for_order_item = $base_transit_days;
                }
                
                // store shipping method for this order item
                $items[$key]['shipping_methods'][] = $shipping_method_id;
            }
            
            // if $smallest_transit_for_order_items has not been set yet or this order item has the smallest transit,
            // update smallest transit and remember order item that has the smallest transit
            if ((isset($smallest_transit_for_order_items) == false) || ($largest_transit_for_order_item < $smallest_transit_for_order_items)) {
                $smallest_transit_for_order_items = $largest_transit_for_order_item;
                $order_item_with_smallest_transit = $key;
            }
            
            /* end: get valid shipping methods for order item and find largest base transit days */
        }
        
        // valid shipping methods are all valid shipping methods for order item with smallest largest transit
        $shipping_methods = $items[$order_item_with_smallest_transit]['shipping_methods'];
    }

    /* end: if necessary, work out issue with no intersecting shipping methods being found for all products */

    // If no shipping methods could be found, then return error.  We are not sure if this would ever
    // happen but are just adding this error in case.  In other words, we are not sure if the logic
    // to address issue with no intersecting shipping methods above would ever result in no shipping
    // methods.
    if (!$shipping_methods) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not find a shipping method for this recipient, even after trying to resolve issue with no intersecting shipping methods.');
    }

    // if requested arrival date was set (and it was not At Once), determine which shipping methods can guarantee arrival by requested arrival date
    if ($arrival_date > '0000-00-00') {
        
        // loop through all shipping methods in order to determine if shipping method will get products to recipient by requested arrival date
        foreach ($shipping_methods as $key => $shipping_method_id) {
            
            // determine if there is a shipping cut-off for the arrival date and shipping method
            $query =
                "SELECT
                    shipping_cutoffs.date_and_time
                FROM shipping_cutoffs
                LEFT JOIN arrival_dates ON shipping_cutoffs.arrival_date_id = arrival_dates.id
                WHERE
                    (arrival_dates.arrival_date = '" . e($arrival_date) . "')
                    AND (shipping_cutoffs.shipping_method_id = '$shipping_method_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is a shipping cut-off, then determine if this shipping method is valid by looking at the cut-off date & time
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $shipping_cutoff_date_and_time = $row['date_and_time'];
                
                // if the shipping cut-off date & time is less than or equal to the current date & time, then this shipping method is not valid, so remove it from shipping methods array
                if ($shipping_cutoff_date_and_time <= date('Y-m-d H:i:s')) {
                    unset($shipping_methods[$key]);
                }
                
            // Otherwise there is not a shipping cut-off, so determine if this shipping method is
            // valid by getting the delivery date.
            } else {

                $response = get_delivery_date(array(
                    'ship_to_id' => $ship_to_id,
                    'shipping_method' => array('id' => $shipping_method_id),
                    'zip_code' => $zip_code,
                    'country' => $country));

                $delivery_date = $response['delivery_date'];

                // If a delivery date could not be found, or it is after the requested arrival date,
                // then this shipping method is not valid, so remove it.
                if (!$delivery_date or $delivery_date > $arrival_date) {
                    unset($shipping_methods[$key]);
                }
            }
        }

        // If there are no valid shipping methods now then that means that the arrival date caused
        // issue so return error about that
        if (!$shipping_methods) {
            return array(
                'status' => 'error',
                'message' => 'Sorry, we could not find a shipping method that would guarantee delivery of your shipment by the Requested Arrival Date. Please select a different Requested Arrival Date to continue.');
        }
    }

    /* end: find all valid shipping methods for this recipient's shipping address and requested arrival date */

    // create another array for shipping methods so that we can eventually sort shipping methods by shipping cost
    $shipping_methods_for_output = array();
    
    // determine if an active shipping discount offer exists (we will use this later below)
    $active_shipping_discount_offer_exists = check_if_active_shipping_discount_offer_exists();
    
    // Loop through all valid shipping methods in order to prepare costs
    foreach ($shipping_methods as $shipping_method_id) {
        // get shipping method info
        $query =
            "SELECT
                name,
                description,
                service,
                realtime_rate,
                base_rate,
                variable_base_rate,
                base_rate_2,
                base_rate_2_subtotal,
                base_rate_3,
                base_rate_3_subtotal,
                base_rate_4,
                base_rate_4_subtotal,
                primary_weight_rate,
                primary_weight_rate_first_item_excluded,
                secondary_weight_rate,
                secondary_weight_rate_first_item_excluded,
                item_rate,
                item_rate_first_item_excluded,
                protected
            FROM shipping_methods
            WHERE id = '$shipping_method_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $shipping_method_name = $row['name'];
        $shipping_method_description = $row['description'];
        $shipping_method_service = $row['service'];
        $shipping_method_realtime_rate = $row['realtime_rate'];
        $shipping_method_base_rate = $row['base_rate'];
        $shipping_method_variable_base_rate = $row['variable_base_rate'];
        $shipping_method_base_rate_2 = $row['base_rate_2'];
        $shipping_method_base_rate_2_subtotal = $row['base_rate_2_subtotal'];
        $shipping_method_base_rate_3 = $row['base_rate_3'];
        $shipping_method_base_rate_3_subtotal = $row['base_rate_3_subtotal'];
        $shipping_method_base_rate_4 = $row['base_rate_4'];
        $shipping_method_base_rate_4_subtotal = $row['base_rate_4_subtotal'];
        $shipping_method_primary_weight_rate = $row['primary_weight_rate'];
        $shipping_method_primary_weight_rate_first_item_excluded = $row['primary_weight_rate_first_item_excluded'];
        $shipping_method_secondary_weight_rate = $row['secondary_weight_rate'];
        $shipping_method_secondary_weight_rate_first_item_excluded = $row['secondary_weight_rate_first_item_excluded'];
        $shipping_method_item_rate = $row['item_rate'];
        $shipping_method_item_rate_first_item_excluded = $row['item_rate_first_item_excluded'];
        $protected = $row['protected'];

        // loop through all valid zones in order to find a zone that is allowed for this shipping method
        foreach ($zones as $zone_id) {
            $query = "SELECT shipping_method_id
                     FROM shipping_methods_zones_xref
                     WHERE (shipping_method_id = '$shipping_method_id') AND (zone_id = '$zone_id')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            if (mysqli_num_rows($result) > 0) {
                // get zone info
                $query = "SELECT base_rate, primary_weight_rate, secondary_weight_rate, item_rate FROM zones WHERE id = '$zone_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);

                $zone_base_rate = $row['base_rate'];
                $zone_primary_weight_rate = $row['primary_weight_rate'];
                $zone_secondary_weight_rate = $row['secondary_weight_rate'];
                $zone_item_rate = $row['item_rate'];

                // we have found a zone, so we need to break out of this loop
                break;
            }
        }

        /* begin: get shipping cost for this shipping method */

        $shipping_cost = 0;

        $realtime_rate = get_shipping_realtime_rate(array(
            'service' => $shipping_method_service,
            'realtime_rate' => $shipping_method_realtime_rate,
            'state' => $state,
            'zip_code' => $zip_code,
            'items' => $items));

        // If there was an error getting the realtime rate, then this shipping method is
        // not valid, so skip to the next method.
        if ($realtime_rate === false) {
            continue;
        }

        $shipping_cost += $realtime_rate;

        $shipping_cost += get_shipping_method_base_rate(array(
            'base_rate' => $shipping_method_base_rate,
            'variable_base_rate' => $shipping_method_variable_base_rate,
            'base_rate_2' => $shipping_method_base_rate_2,
            'base_rate_2_subtotal' => $shipping_method_base_rate_2_subtotal,
            'base_rate_3' => $shipping_method_base_rate_3,
            'base_rate_3_subtotal' => $shipping_method_base_rate_3_subtotal,
            'base_rate_4' => $shipping_method_base_rate_4,
            'base_rate_4_subtotal' => $shipping_method_base_rate_4_subtotal,
            'ship_to_id' => $ship_to_id));

        $shipping_cost += $zone_base_rate;

        // Create a counter so that later we can determine if the first item
        // should be excluded from shipping charges based on shipping method settings.
        $count = 0;

        // loop through all items in cart for this recipient in order to calculate shipping cost
        foreach ($items as $key => $value) {
            $quantity = $items[$key]['quantity'];
            $primary_weight_points = $items[$key]['primary_weight_points'];
            $secondary_weight_points = $items[$key]['secondary_weight_points'];
            $free_shipping = $items[$key]['free_shipping'];
            $extra_shipping_cost = $items[$key]['extra_shipping_cost'];

            // if this is not a free shipping product, calculate shipping cost for product
            if ($free_shipping == 0) {
                $count++;

                $shipping_method_primary_weight_rate_quantity = $quantity;
                $shipping_method_secondary_weight_rate_quantity = $quantity;
                $shipping_method_item_rate_quantity = $quantity;

                // If this is the first order item that has a shipping charge,
                // then check if we need to reduce the quantity in order to exclude the first item
                // from shipping charges.
                if ($count == 1) {
                    // If the first item should be excluded for the primary weight calculation
                    // for the shipping method, then reduce the quantity by one.
                    if ($shipping_method_primary_weight_rate_first_item_excluded == 1) {
                        $shipping_method_primary_weight_rate_quantity--;
                    }

                    // If the first item should be excluded for the secondary weight calculation
                    // for the shipping method, then reduce the quantity by one.
                    if ($shipping_method_secondary_weight_rate_first_item_excluded == 1) {
                        $shipping_method_secondary_weight_rate_quantity--;
                    }

                    // If the first item should be excluded for the item calculation
                    // for the shipping method, then reduce the quantity by one.
                    if ($shipping_method_item_rate_first_item_excluded == 1) {
                        $shipping_method_item_rate_quantity--;
                    }
                }

                $shipping_method_cost = ($primary_weight_points * $shipping_method_primary_weight_rate * $shipping_method_primary_weight_rate_quantity) + ($secondary_weight_points * $shipping_method_secondary_weight_rate * $shipping_method_secondary_weight_rate_quantity) + ($shipping_method_item_rate * $shipping_method_item_rate_quantity);
                $zone_cost = ($primary_weight_points * $zone_primary_weight_rate * $quantity) + ($secondary_weight_points * $zone_secondary_weight_rate * $quantity) + ($zone_item_rate * $quantity);
                $extra_shipping_cost = $extra_shipping_cost * $quantity;
                $shipping_cost_for_item = $shipping_method_cost + $zone_cost + $extra_shipping_cost;
                $shipping_cost = $shipping_cost + $shipping_cost_for_item;
            }
        }
        
        // prepare for checking for shipping discounts
        $original_shipping_cost = 0;

        $discounted = false;
        
        // if an active shipping discount offer exists, then check if there is an offer that will discount this shipping method
        if ($active_shipping_discount_offer_exists == TRUE) {
            $offer = get_best_shipping_discount_offer($ship_to_id, $shipping_method_id);
            
            // if a shipping discount offer was found, then discount shipping
            if ($offer != FALSE) {
                // remember the original shipping cost, before it is updated
                $original_shipping_cost = $shipping_cost;
                
                // update shipping cost to contain discount
                $shipping_cost = $shipping_cost - ($shipping_cost * ($offer['discount_shipping_percentage'] / 100));

                $discounted = true;

            }
        }
        
        // convert shipping cost from cents to dollars
        $shipping_cost = $shipping_cost / 100;
        $original_shipping_cost = $original_shipping_cost / 100;

        if ($discounted) {
            $cost_info = prepare_price_for_output($original_shipping_cost * 100, $discounted, $shipping_cost * 100, 'html');
        } else {
            $discounted_shipping_cost = '';
            $original_shipping_cost = $shipping_cost;

            $cost_info = prepare_price_for_output($shipping_cost * 100, $discounted, $discounted_shipping_cost, 'html');
        }
        
        /* end: get shipping cost for this shipping method */

        // add this shipping method data to array so we can order shipping methods by shipping cost
        $shipping_methods_for_output[] =
            array(
                'cost' => $shipping_cost,
                'original_cost' => $original_shipping_cost,
                'cost_info' => $cost_info,
                'discounted' => $discounted,
                'name' => $shipping_method_name,
                'id' => $shipping_method_id,
                'description' => $shipping_method_description,
                'protected' => (bool) $protected,
                'service' => $shipping_method_service);
    }

    // If there are no shipping methods at this point then that means that all of the shipping
    // methods were realtime rate methods and getting the realtime rates failed for all of them
    if (!$shipping_methods_for_output) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not find a shipping method for this recipient because we could not get the real-time rate. Please check that the address is correct.  If it is already correct, then you might try again in a few minutes.');
    }

    // sort shipping methods by cost
    sort($shipping_methods_for_output);

    $shipping_methods = $shipping_methods_for_output;

    // We return the arrival date, so that the JS code can determine if the at once arrival date
    // was selected and so therefore it needs to get an estimated delivery date.

    return array(
        'status' => 'success',
        'shipping_methods' => $shipping_methods,
        'arrival_date' => $arrival_date);
}

// Used to check if shipping method is active and valid for recipient, in order for us to know
// if we should allow it to be selected for a recipient.

function check_shipping_method($properties) {

    $shipping_method_id = $properties['shipping_method_id'];
    $ship_to_id = $properties['ship_to_id'];
    $address_1 = $properties['address_1'];
    $state = $properties['state'];
    $zip_code = trim($properties['zip_code']);
    $country = $properties['country'];
    $arrival_date = $properties['arrival_date'];

    $po_or_street = get_address_type($address_1);
    $po_or_street = str_replace(' ', '_', $po_or_street);
    $day_of_week = mb_strtolower(date('l'));
    
    // Try to find an active shipping method for the selected method that is valid for recipient
    $shipping_method = db_item(
        "SELECT
            id,
            service,
            code,
            handle_days,
            handle_mon,
            handle_tue,
            handle_wed,
            handle_thu,
            handle_fri,
            handle_sat,
            handle_sun,
            ship_mon,
            ship_tue,
            ship_wed,
            ship_thu,
            ship_fri,
            ship_sat,
            ship_sun,
            end_of_day,
            base_transit_days,
            adjust_transit,
            transit_on_sunday,
            transit_on_saturday
        FROM shipping_methods
        WHERE
            (id = '" . e($shipping_method_id) . "')
            AND (status = 'enabled')
            AND (start_time <= NOW())
            AND (end_time >= NOW())
            " . get_protected_shipping_method_filter() . "
            AND ($po_or_street = 1)
            AND (available_on_" . $day_of_week . " = '1')
            AND (
                (available_on_" . $day_of_week . "_cutoff_time = '00:00:00')
                OR (available_on_" . $day_of_week . "_cutoff_time > CURRENT_TIME())
            )");

    // If an active shipping method could not be found, then return error
    if (!$shipping_method) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, that shipping method is not valid for the recipient. It may not be available anymore or might not be valid for the recipient\'s address. Please select a different method.');
    }

    // Get zones that are valid for destination
    $zones = get_valid_zones($ship_to_id, $country, $state);

    // Get zones that are valid for shipping method
    $shipping_method_zones = db_values(
        "SELECT zone_id FROM shipping_methods_zones_xref
        WHERE shipping_method_id = '" . e($shipping_method['id']) . "'");

    $zone = array();

    // Loop through zones in order to find one that is valid for shipping method
    foreach ($zones as $zone_id) {
        // If zone is allowed for method, then remember zone and break out of loop
        if (in_array($zone_id, $shipping_method_zones)) {
            $zone['id'] = $zone_id;
            break;
        }
    }

    // If none of the zones for this recipient were valid for the shipping method, then there is
    // a chance that the method is still valid, because if no methods intersect for the combination
    // of the destination and products, then we force a method to be allowed.  So, figure out if
    // this method is a method that is forced to be allowed.

    if (!$zone) {

        // Get all valid shipping methods for this recipient, which will return the forced valid
        // method, if there is one.
        $response = get_shipping_methods(array(
            'ship_to_id' => $ship_to_id,
            'address_1' => $address_1,
            'state' => $state,
            'zip_code' => $zip_code,
            'country' => $country,
            'arrival_date' => $arrival_date));

        // If this method is valid, then return info. We don't return a zone because we don't
        // store/use any zone info when a method is forced.
        if (
            $response['shipping_methods']
            and array_find($response['shipping_methods'], 'id', $shipping_method['id'])
        ) {
            return array(
                'status' => 'success',
                'shipping_method' => $shipping_method);

        // Otherwise the method is not valid, so return error.
        } else {
            return array(
                'status' => 'error',
                'message' => 'Sorry, that shipping method is not allowed for the recipient because the recipient\'s address and/or items are not supported by the method. Please select a different method.');
        }
    }

    // If there is no arrival date or it is "At Once", then we know the shipping method is valid,
    // so return success.
    if (!$arrival_date or $arrival_date == '0000-00-00') {
        return array(
            'status' => 'success',
            'shipping_method' => $shipping_method,
            'zone' => $zone);
    }

    // Otherwise there is an arrival date, so we need to check if shipping method can get items to
    // recipient by the arrival date

    // Determine if there is a shipping cut-off for the arrival date and shipping method
    $cutoff = db(
        "SELECT shipping_cutoffs.date_and_time
        FROM shipping_cutoffs
        LEFT JOIN arrival_dates ON shipping_cutoffs.arrival_date_id = arrival_dates.id
        WHERE
            (arrival_dates.arrival_date = '" . e($arrival_date) . "')
            AND (shipping_cutoffs.shipping_method_id = '" . e($shipping_method['id']) . "')");

    // If there is a cut-off and it has occurred, then this shipping method is not valid,
    // so return error.
    if ($cutoff and $cutoff <= date('Y-m-d H:i:s')) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, the shipping method is no longer available for that arrival date. The cut-off time has passed. You might try selecting a different arrival date or shipping method.');
    }

    $response = get_delivery_date(array(
        'ship_to_id' => $ship_to_id,
        'shipping_method' => $shipping_method,
        'zip_code' => $zip_code,
        'country' => $country
    ));

    $delivery_date = $response['delivery_date'];

    // If a delivery date could not be found, or it is after the requested arrival date,
    // then this shipping method is not valid, so return error.
    if (!$delivery_date or $delivery_date > $arrival_date) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, the shipping method cannot guarantee delivery of the shipment by the requested arrival date. You might try selecting a different arrival date or shipping method.');
    }

    // If we have gotten here then the shipping method is valid, so return success
    return array(
        'status' => 'success',
        'shipping_method' => $shipping_method,
        'zone' => $zone);
}

// this function is responsible for verifying a US address using USPS

function verify_address($properties) {

    $liveform = $properties['liveform'];
    $address_type = $properties['address_type'];

    // $prefix is used on express order to make field names unique because there might be fields for
    // multiple recipients on the same page.
    $prefix = $properties['prefix'];

    $old_address_verified = $properties['old_address_verified'];
    $ship_to_id = $properties['ship_to_id'];

    $address_verified = 0;
    $address_fields = array();
    
    // if the address that we are verifying is the billing address then set the address fields array to the billing address fields
    if ($address_type == 'billing') {
        $address_fields['address_1'] = $prefix . 'billing_address_1';
        $address_fields['address_2'] = $prefix . 'billing_address_2';
        $address_fields['city'] = $prefix . 'billing_city';
        $address_fields['state'] = $prefix . 'billing_state';
        $address_fields['zip_code'] = $prefix . 'billing_zip_code';
        $address_fields['country'] = $prefix . 'billing_country';
    
    // else use the shipping address fields
    } else {
        $address_fields['salutation'] = $prefix . 'salutation';
        $address_fields['first_name'] = $prefix . 'first_name';
        $address_fields['last_name'] = $prefix . 'last_name';
        $address_fields['company'] = $prefix . 'company';
        $address_fields['address_1'] = $prefix . 'address_1';
        $address_fields['address_2'] = $prefix . 'address_2';
        $address_fields['city'] = $prefix . 'city';
        $address_fields['state'] = $prefix . 'state';
        $address_fields['zip_code'] = $prefix . 'zip_code';
        $address_fields['country'] = $prefix . 'country';
    }
    
    // If shipping & address verification is enabled, and there are not any errors in the relevant
    // fields, and if United States was selected as the country, then verify the address.
    // Shipping has to be enabled, even if this is a billing address, because the address
    // verification site setting is located under the shipping section, and it is against USPS
    // terms anyway to verify billing addresses if you are not shipping anything.
    if (
        ECOMMERCE_SHIPPING
        && ECOMMERCE_ADDRESS_VERIFICATION
        &&
        (
            ($liveform->check_field_error($address_fields['address_1']) == FALSE)
            && ($liveform->check_field_error($address_fields['address_2']) == FALSE)
            && ($liveform->check_field_error($address_fields['city']) == FALSE)
            && ($liveform->check_field_error($address_fields['state']) == FALSE)
            && ($liveform->check_field_error($address_fields['zip_code']) == FALSE)
            && ($liveform->check_field_error($address_fields['country']) == FALSE)
        ) 
        && (($liveform->get_field_value($address_fields['country']) == 'US') || ($liveform->get_field_value($address_fields['country']) == 'USA'))
    ) {
        // if cURL is not installed, then log error
        if (function_exists('curl_init') == FALSE) {
            log_activity('This website cannot communicate with USPS for address verification, because cURL is not installed. The administrator of this website should install cURL.', $_SESSION['sessionusername']);
            
        // else cURL is installed, so continue address verification
        } else {
            // If the enforcement type is set to warning, then get old hash in order to determine
            // if the address has changed and we need to verify address again.
            // We don't need to deal with the hash for the error enforcement type,
            // because we verify the address every single time for that type.
            if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                // if the address is a billing address then get the old address verification hash in a certain way
                if ($address_type == 'billing') {
                    $old_address_verification_hash = $_SESSION['ecommerce']['billing_address_verification_hash'];
                
                // else the address is a shipping address, so get the old address verification hash in a different way
                } else {
                    $old_address_verification_hash = $_SESSION['ecommerce']['shipping_address_verification_hashes'][$ship_to_id];
                }
                
                // create hash of new address data in order to determine if the address has changed so that we can determine if we need to verify the address or not
                $new_address_verification_hash = md5($liveform->get_field_value($address_fields['address_1']) . $liveform->get_field_value($address_fields['address_2']) . $liveform->get_field_value($address_fields['city']) . $liveform->get_field_value($address_fields['state']) . $liveform->get_field_value($address_fields['zip_code']));
            }

            // If the enforcement type is error or if the new and old address verification hashes
            // are different, then continue to verify the address.
            if (
                (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'error')
                || ($new_address_verification_hash != $old_address_verification_hash)
            ) {
                // set url parameters needed to connect with USPS address verification service
                $usps_api_url_parameters = 
                    '<AddressValidateRequest USERID="' . h(USPS_USER_ID) . '">' . 
                        '<Address ID="0">' . 
                            '<Address1>' . h($liveform->get_field_value($address_fields['address_2'])) . '</Address1>' . 
                            '<Address2>' . h($liveform->get_field_value($address_fields['address_1'])) . '</Address2>' . 
                            '<City>' . h($liveform->get_field_value($address_fields['city'])) . '</City>' . 
                            '<State>' . h($liveform->get_field_value($address_fields['state'])) . '</State>' . 
                            '<Zip5>' . h($liveform->get_field_value($address_fields['zip_code'])) . '</Zip5>' . 
                            '<Zip4></Zip4>' . 
                        '</Address>' . 
                    '</AddressValidateRequest>';
                
                // initialize cURL
                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, 'http://production.shippingapis.com/ShippingAPI.dll?API=Verify&XML=' . urlencode($usps_api_url_parameters));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                // We had issues in the past where the timeout did not work, when USPS' service
                // went down, and we were only using CURLOPT_TIMEOUT. The request would go on for
                // too long. We are adding CURLOPT_CONNECTTIMEOUT also to attempt to resolve that.
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                
                // get cURL response
                $response_data = curl_exec($ch);
                
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);
                
                curl_close($ch);
                
                // if there was a cURL problem, then log the error
                if ($curl_errno) {
                    log_activity('An error occurred while trying to communicate with your USPS Web Tools Account. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.', $_SESSION['sessionusername']);
                
                // else determine if an USPS error needs to be outputted or if the form fields need to be updated, and then process the action
                } else {
                    $usps_error = '';
                    
                    // get Errors
                    preg_match('/<error>(.*?)<\/error>/msi', $response_data, $matches);
                    $usps_error = $matches[1];
                    
                    // if there is a USPS error, then process the error
                    if ($usps_error != '') {
                        preg_match('/<number>(.*?)<\/number>/msi', $response_data, $matches);
                        $usps_error_number = $matches[1];
                        
                        preg_match('/<description>(.*?)<\/description>/msi', $response_data, $matches);
                        $usps_error_description = $matches[1];
                        
                        // determine what needs to be done based on the error code
                        switch($usps_error_number) {
                            // address not found error or multiple addresses found error
                            case '-2147219401':
                            case '-2147219403':
                                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                                    $message = 'We were not able to find the address in our postal database, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then you may leave it unchanged and continue below.';
                                } else {
                                    $message = 'Sorry, we were not able to find the address in our postal database, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then please feel free to contact us.';
                                }

                                // output error
                                $liveform->mark_error($address_fields['address_1'], $message);
                                $liveform->mark_error($address_fields['address_2'], '');
                                $liveform->mark_error($address_fields['city'], '');
                                $liveform->mark_error($address_fields['state'], '');
                                $liveform->mark_error($address_fields['zip_code'], '');
                                break;
                                
                            // invalid city error
                            case '-2147219400':
                                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                                    $message = 'According to our postal database, the city is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then you may leave it unchanged and continue below.';
                                } else {
                                    $message = 'Sorry, according to our postal database, the city is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then please feel free to contact us.';
                                }

                                // output error
                                $liveform->mark_error($address_fields['city'], $message);
                                break;
                                
                            // invalid state error
                            case '-2147219402':
                                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                                    $message = 'According to our postal database, the state/province is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then you may leave it unchanged and continue below.';
                                } else {
                                    $message = 'Sorry, according to our postal database, the state/province is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then please feel free to contact us.';
                                }

                                // output error
                                $liveform->mark_error($address_fields['state'], $message);
                                break;
                            
                            // invalid zip code error
                            case '-2147219399':
                                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                                    $message = 'According to our postal database, the zip/postal code is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then you may leave it unchanged and continue below.';
                                } else {
                                    $message = 'Sorry, according to our postal database, the zip/postal code is not valid, so please review it below.  If it is incorrect, then please correct it.  If you are sure that it is correct then please feel free to contact us.';
                                }

                                // output error
                                $liveform->mark_error($address_fields['zip_code'], $message);
                                break;
                            
                            // log all unknown errors
                            default:
                                log_activity('An error occurred while trying to communicate with the USPS Web Tools Account for address verification. USPS Error Number: ' . $usps_error_number . '. USPS Error Message: ' . $usps_error_description, $_SESSION['sessionusername']);
                                break;
                        }
                    
                    // else USPS did not return an error, so determine if they responded properly with a verified address.
                    } else {
                        // Look for an address 1 that is not blank in the response, in order to determine if USPS
                        // responded correctly.  We have seen rare occurrences where USPS will either respond with a blank
                        // response or a blank address possibly, so that is why this protection is necessary.
                        // We don't want to update the address with blank info in that case, which might result in the order
                        // being allowed to be submitted with blank info.  USPS' "address2" is actually our address 1,
                        // so the "address2" reference below is not a mistake.
                        preg_match('/<address2>(.*?)<\/address2>/msi', $response_data, $matches);

                        // If an address 1 cannot be found in the response, then just log activity.
                        if (trim($matches[1]) == '') {
                            log_activity('An error occurred while trying to communicate with your USPS Web Tools Account.  USPS did not return a verified address. Response: ' . $response_data, $_SESSION['sessionusername']);

                        // Otherwise an address 1 was found, so update the fields with the new address,
                        // and also output any return text
                        } else {
                            // if the address that we are verifying is a billing address, then format the address accordingly
                            if ($address_type == 'billing') {
                                // if the return data is a PO Box then format it correctly
                                if (preg_match('/PO BOX/msi', unhtmlspecialchars($matches[1]), $po_matches) > 0) {
                                    $matches[1] = preg_replace('/(.*?)PO BOX(.*?)/si', '$1PO Box$2', unhtmlspecialchars($matches[1]), 1);
                                    
                                // else proper case the response data
                                } else {
                                    $matches[1] = ucwords(mb_strtolower(unhtmlspecialchars($matches[1])));
                                }
                                
                                // update the value in the liveForm
                                $liveform->assign_field_value($address_fields['address_1'], $matches[1]);
                                
                                // get the response data for this field
                                preg_match('/<address1>(.*?)<\/address1>/msi', $response_data, $matches);
                                
                                // if the return data is a PO Box then format it correctly
                                if (preg_match('/PO BOX/msi', unhtmlspecialchars($matches[1]), $po_matches) > 0) {
                                    $matches[1] = preg_replace('/(.*?)PO BOX(.*?)/si', '$1PO Box$2', unhtmlspecialchars($matches[1]), 1);
                                    
                                // else proper case the response data
                                } else {
                                    $matches[1] = ucwords(mb_strtolower(unhtmlspecialchars($matches[1])));
                                }
                                
                                // update the value in the liveForm
                                $liveform->assign_field_value($address_fields['address_2'], $matches[1]);
                                
                                // get the response data for this field and update the corresponding liveForm field
                                preg_match('/<city>(.*?)<\/city>/msi', $response_data, $matches);
                                $liveform->assign_field_value($address_fields['city'], ucwords(mb_strtolower(unhtmlspecialchars($matches[1]))));
                                
                            // else this is a shipping address, so set field values to the values that USPS returned
                            } else {
                                $liveform->assign_field_value($address_fields['address_1'], unhtmlspecialchars($matches[1]));
                                
                                // get the response data for this field and update the corresponding liveForm field
                                preg_match('/<address1>(.*?)<\/address1>/msi', $response_data, $matches);
                                $liveform->assign_field_value($address_fields['address_2'], unhtmlspecialchars($matches[1]));
                                
                                // get the response data for this field and update the corresponding liveForm field
                                preg_match('/<city>(.*?)<\/city>/msi', $response_data, $matches);
                                $liveform->assign_field_value($address_fields['city'], unhtmlspecialchars($matches[1]));
                            }
                            
                            // get the response data for this field and update the corresponding liveForm field
                            preg_match('/<state>(.*?)<\/state>/msi', $response_data, $matches);
                            $liveform->assign_field_value($address_fields['state'], unhtmlspecialchars($matches[1]));
                            
                            $zip_code = '';
                            
                            // get the response data for this field and update the corresponding liveForm field
                            preg_match('/<zip5>(.*?)<\/zip5>/msi', $response_data, $matches);
                            $zip_code = unhtmlspecialchars($matches[1]);
                            
                            // get the response data for this field and update the corresponding liveForm field
                            preg_match('/<zip4>(.*?)<\/zip4>/msi', $response_data, $matches);
                            $zip_code .= '-' . unhtmlspecialchars($matches[1]);
                            
                            $liveform->assign_field_value($address_fields['zip_code'], $zip_code);
                            
                            // check for response text
                            preg_match('/<returntext>(.*?)<\/returntext>/msi', $response_data, $matches);
                            
                            // if there is response text, then return an error
                            if ($matches[1] != '') {
                                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                                    $message = 'Our postal database indicates that the address might be missing information, such as an apartment, suite, or box number, so please review the address below.  If there is missing information, then please add the missing information.  If you are sure that it is complete then you may leave it unchanged and continue below.';
                                } else {
                                    $message = 'Sorry, our postal database indicates that the address might be missing information, such as an apartment, suite, or box number, so please review the address below.  If there is missing information, then please add the missing information.  If you are sure that it is complete then please feel free to contact us.';
                                }

                                // output error
                                $liveform->mark_error($address_fields['address_1'], $message);
                                $liveform->mark_error($address_fields['address_2'], '');
                                
                            // else there is not any response text, so the address is fully verified, so remember that
                            } else {
                                $address_verified = 1;
                            }
                        }
                    }
                }

                // If the verification type is warning, then get new hash and store it in session,
                // so that we don't have to verify the address again.
                // If the verification type is error, then we don't need to deal with hashes,
                // because we verify the address everytime.
                if (ECOMMERCE_ADDRESS_VERIFICATION_ENFORCEMENT_TYPE == 'warning') {
                    $new_address_verification_hash = md5($liveform->get_field_value($address_fields['address_1']) . $liveform->get_field_value($address_fields['address_2']) . $liveform->get_field_value($address_fields['city']) . $liveform->get_field_value($address_fields['state']) . $liveform->get_field_value($address_fields['zip_code']));
                    
                    // if the address is a billing address then store hash for address data in a certain way, so that we do not verify the same address again
                    if ($address_type == 'billing') {
                        $_SESSION['ecommerce']['billing_address_verification_hash'] = $new_address_verification_hash;
                    
                    // else the address is a shipping address, so store hash in a different way, so that we do not verify the same address again
                    } else {
                        $_SESSION['ecommerce']['shipping_address_verification_hashes'][$ship_to_id] = $new_address_verification_hash;
                    }
                }
            
            // Otherwise the enforcement type is error and the hash is the same,
            // so do not verify the address, but set address verified value to old address verified value
            } else {
                $address_verified = $old_address_verified;
            }
            
            // if the address that we are verifying is a shipping address, and the address has been verified,
            // then convert the name and company values to all uppercase,
            // so that they match the other field values that the USPS returned
            if (($address_type == 'shipping') && ($address_verified == 1)) {
                $liveform->assign_field_value($address_fields['first_name'], mb_strtoupper($liveform->get_field_value($address_fields['first_name'])));
                $liveform->assign_field_value($address_fields['last_name'], mb_strtoupper($liveform->get_field_value($address_fields['last_name'])));
                $liveform->assign_field_value($address_fields['company'], mb_strtoupper($liveform->get_field_value($address_fields['company'])));
            }
        }
    }
    
    // return the address verified value
    return $address_verified;
}

// Used to figure out the ship date for a product preparation time and method handling time.

function get_ship_date($properties) {

    $preparation_time = $properties['preparation_time'];
    $shipping_method = $properties['shipping_method'];
    $excluded_transit_dates = $properties['excluded_transit_dates'];

    // If there is not shipping method info that we need, then get info.
    if (!isset($shipping_method['handle_days'])) {

        $shipping_method = db_item(
            "SELECT
                id,
                handle_days,
                handle_mon,
                handle_tue,
                handle_wed,
                handle_thu,
                handle_fri,
                handle_sat,
                handle_sun,
                ship_mon,
                ship_tue,
                ship_wed,
                ship_thu,
                ship_fri,
                ship_sat,
                ship_sun,
                end_of_day
            FROM shipping_methods
            WHERE id = '" . e($shipping_method['id']) . "'");

        if (!$shipping_method) {
            return array(
                'status' => 'error',
                'message' => 'Sorry, the shipping method could not be found.');
        }
    }

    // If excluded transit days was not passed or not valid, then get them.
    if (!is_array($excluded_transit_dates)) {
        $excluded_transit_dates = db_values(
            "SELECT date FROM excluded_transit_dates
            WHERE shipping_method_id = '" . e($shipping_method['id']) . "'");
    }

    // Add the product preparation time to the method's handling time, to get the total handling
    // time. Suppressing error for PHP 7.1+ support
    $handle_days = @($preparation_time + $shipping_method['handle_days']);

    // If the handling is more than zero days, but there are no valid handle days, then return error.
    if (
        $handle_days
        and !$shipping_method['handle_mon']
        and !$shipping_method['handle_tue']
        and !$shipping_method['handle_wed']
        and !$shipping_method['handle_thu']
        and !$shipping_method['handle_fri']
        and !$shipping_method['handle_sat']
        and !$shipping_method['handle_sun']
    ) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not determine a ship date for the shipping method, because there are no valid handling days selected for the method. Please review the handling days for the shipping method (id: ' . $shipping_method['id'] . ').');
    }

    // If there are no valid ship days, then return error.
    if (
        !$shipping_method['ship_mon']
        and !$shipping_method['ship_tue']
        and !$shipping_method['ship_wed']
        and !$shipping_method['ship_thu']
        and !$shipping_method['ship_fri']
        and !$shipping_method['ship_sat']
        and !$shipping_method['ship_sun']
    ) {
        return array(
            'status' => 'error',
            'message' => 'Sorry, we could not determine a ship date for the shipping method, because there are no valid ship days selected for the method. Please review the ship days for the shipping method (id: ' . $shipping_method['id'] . ').');
    }

    // If the shipping method has an end of day override, then use that.
    if ($shipping_method['end_of_day'] != '00:00:00') {

        // Remove the last colon and seconds from the end of day, because we only need to check
        // the hour and minute.
        $end_of_day = mb_substr($shipping_method['end_of_day'], 0, 5);

    // Otherwise use the end of day from the site settings.
    } else {
        $end_of_day = ECOMMERCE_END_OF_DAY_TIME;
    }
    
    // If current time is before end of day time, then start date is today.
    if (date('H:i') < $end_of_day) {
        $date = date('Y-m-d');
        
    // Otherwise the current time is after end of day time, so start date is tomorrow.
    } else {
        $date = date('Y-m-d', strtotime('+1 day'));
    }
    
    $count = 0;
    $handle_count = 0;
    
    // Figure out the ship date by looping through days in the future.
    while (true) {

        // Get lowercase day of the week abbreviation (e.g. "mon") so we can use it in tests below.
        $day_of_the_week = strtolower(date('D', strtotime($date)));

        $excluded = in_array($date, $excluded_transit_dates);

        // If we have handled the package for enough days, and this date is a valid ship date,
        // then this date is the ship date, so return it.
        if (
            $handle_count >= $handle_days
            and $shipping_method['ship_' . $day_of_the_week]
            and !$excluded
        ) {
            return array(
                'status' => 'success',
                'ship_date' => $date);
        }

        $count++;

        // If we have already looped through 1,000 days, then let's assume that something is wrong
        // and return error, in order to prevent an endless loop.  This should rarely/never happen.
        if ($count >= 1000) {
            return array(
                'status' => 'error',
                'message' => 'Sorry, we could not determine a ship date for the shipping method. We gave up after checking 1,000 days, in order to prevent an endless loop. Please check the shipping method properties.');
        }

        // If packages are handled on this date, then increase handle count.
        // count.
        if ($shipping_method['handle_' . $day_of_the_week] and !$excluded) {
            $handle_count++;
        }

        // Set date to the next day, so we can analyze it during next loop.
        $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
    }
}