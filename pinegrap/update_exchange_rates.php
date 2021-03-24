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

// if there is a send to value, then this is not being run by a scheduled task
if ($_GET['send_to']) {    
    // Validate the user, so we can say that he was the one that ran the update.
    $user = validate_user();
    $user_id = $user['id'];
    
// else there is not a send to value, so this is being run by a scheduled task
} else {
    $user_id = 0;
}

include_once('liveform.class.php');

$liveform = new liveform('view_currencies');

// get the currency listings
$query =
    "SELECT
        id,
        code
    FROM currencies";

$results = mysqli_query(db::$con, $query) or output_error('Query failed.');

$currencies = array();

// Loop through each returned result
while ($row = mysqli_fetch_assoc($results)) {
    $currency_has_been_updated = false;
    $currency_id = $row['id'];
    $currency_code = $row['code'];
    
    $source_file = '';
    // If this is not the base currency, request for it to be updated.
    if ($currency_code != BASE_CURRENCY_CODE) {
        // Open the exchangeratesapi page for this currency
        $url = 'https://api.exchangeratesapi.io/latest?base=' . BASE_CURRENCY_CODE . '&symbols='.$currency_code;
        $handle = file_get_contents($url, FALSE, NULL, 16, 9);
       
        
        // Trim the beginning and ending spaces.
        $source_file = trim($handle);

        // If it opened, continue on with the show.
        if ($handle) {
            // If there is information in the variable, and it is not equal to 0.00, store it in the datbase
            if (($source_file) && ($source_file != '0.00')) {
                    $query =
                        "UPDATE currencies SET
                            exchange_rate = '" . $source_file . "',
                            last_modified_user_id = '" . $user_id . "',
                            last_modified_timestamp = UNIX_TIMESTAMP()
                        WHERE
                            id = '" . $currency_id . "'";
                    mysqli_query(db::$con, $query);
                    $currency_has_been_updated = true;
            }
        }

        // If we could not find an exchange rate, then output error.
        // In the past we had a fall-back that also looked at xe.net if Yahoo failed,
        // however xe.net changed their output code which broke our implementation,
        // and they added warnings about unauthorized use to to their source code,
        // so we decided to remove the fall-back for xe.net.
        if ($currency_has_been_updated == false) {
            $liveform->mark_error('currency_' . $currency_code, 'Failed to update exchange rate for currency ' . $currency_code);
        }
        
    // Else, this is the base currency so set it to 1.0000 by default.
    } else {
        $query =
            "UPDATE currencies SET
                exchange_rate = '1.00000',
                last_modified_user_id = '" . $user_id . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE
                id = '" . $currency_id . "'";
        mysqli_query(db::$con, $query);
        
    }
}

// Redirect them back to the page they came from, if they came from one.
if ($_GET['send_to']) {
    $liveform->add_notice('The exchange rates have been updated.');
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $_GET['send_to']);
    exit();
}
?>