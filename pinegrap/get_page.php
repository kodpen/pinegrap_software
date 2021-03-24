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



// if visitor tracking is on, initialize tracking

if (VISITOR_TRACKING) {

    init_tracking();

}



// If user is logged into software, then initialize user and view page mode.

if (isset($_SESSION['sessionusername']) == true) {

    $user = validate_user();



    // If view page mode is not set, then set it.

    if (isset($_SESSION['software']['view_page_mode']) == false) {

        // If there is a cookie value, then use that.

        if ($_COOKIE['software']['view_page_mode']) {

            $_SESSION['software']['view_page_mode'] = $_COOKIE['software']['view_page_mode'];



        // Otherwise, set default value to preview.

        } else {

            $_SESSION['software']['view_page_mode'] = 'preview';

            setcookie('software[view_page_mode]', 'preview', time() + 315360000, '/');

        }

    }

}



// If a page name was passed, then prepare page name.

if ($_GET['page']) {

    $page_name = $_GET['page'];

    

    // If there is a forward slash in the page name then check to see if this is special type of

    // page that supports pretty URLs with slashes.

    if (mb_strpos($page_name, '/') !== FALSE) {

        // extract everything from the page name before the first forward slash

        $page_name_before_slash = mb_substr($page_name, 0, mb_strpos($page_name, '/'));

        

        // check to see if there is a page with the page name

        $query = "SELECT page_id, page_type FROM page WHERE page_name = '" . escape($page_name_before_slash) . "'";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        $row = mysqli_fetch_assoc($result);



        $page_id = $row['page_id'];

        $page_type = $row['page_type'];



        switch ($page_type) {

            // If this is catalog or catalog detail page, then just set page name.

            case 'catalog':

            case 'catalog detail':

                $page_name = $page_name_before_slash;

                break;



            // If the name in the URL is a form list view page, then this is actually

            // a request for a form item view (e.g. /blog/happy-holidays), so update page

            // name and get reference code for the address name that was passed.

            case 'form list view':

                // Get the requested address name.

                $address_name = trim(mb_substr($page_name, mb_strpos($page_name, '/') + 1));



                // If the address name is not blank, then direct visitor to form item view.

                // We do this check so if someone requests /blog/ with just a slash on the end,

                // the form list view will load.

                if ($address_name != '') {

                    // Get info about form list view.

                    $form_list_view = db_item(

                        "SELECT

                            form_list_view_pages.custom_form_page_id,

                            form_item_view_page.page_name AS form_item_view_page_name

                        FROM form_list_view_pages

                        LEFT JOIN page AS form_item_view_page ON form_list_view_pages.form_item_view_page_id = form_item_view_page.page_id

                        WHERE

                            (form_list_view_pages.page_id = '$page_id')

                            AND (form_list_view_pages.collection = 'a')");



                    // If pretty URLs are enabled for the custom form,

                    // then prepare to show requested submitted form on form item view.

                    if (check_if_pretty_urls_are_enabled($form_list_view['custom_form_page_id']) == true) {

                        // Store the requested page name and address name in a constant,

                        // so that we can later use this URL to share with social networking services.

                        define('PRETTY_URL_PATH', $page_name);



                        $page_name = $form_list_view['form_item_view_page_name'];



                        // Try to find a submitted form for the address name and

                        // set the reference code value in the query string because

                        // that is the parameter that we later look for to display the form item view.

                        $_GET['r'] = db_value(

                            "SELECT reference_code

                            FROM forms

                            WHERE

                                (page_id = '" . $form_list_view['custom_form_page_id'] . "')

                                AND (address_name = '" . e($address_name) . "')");

                    }



                // Otherwise the address name is blank, so remove the trailing slash on the end

                // of the form list view page name so that the form list view will load.

                } else {

                    $page_name = mb_substr($page_name, 0, mb_strpos($page_name, '/'));

                }



                break;

        }

    }

}



// if ecommerce is on and a special offer code was supplied in the query string, add special offer code to session

if ((ECOMMERCE == true) && ($_GET['o'])) {

    $_SESSION['ecommerce']['special_offer_code'] = trim($_GET['o']);

}



// if ecommerce is enabled

// and affiliate program is enabled

// and there is an affiliate code in the query string

// and there is not already an offer code in the query string

// and there is not already an offer code in the session

// and if affiliate code is also a special offer code (e.g. affiliate group offer)

// then add affiliate code as a special offer code

if (

    (ECOMMERCE == TRUE)

    && (AFFILIATE_PROGRAM == TRUE)

    && ((isset($_GET['a']) == TRUE) && ($_GET['a'] != ''))

    && ((isset($_GET['o']) == FALSE) || ($_GET['o'] == ''))

    && ((isset($_SESSION['ecommerce']['special_offer_code']) == FALSE) || ($_SESSION['ecommerce']['special_offer_code'] == ''))

    && (get_offer_code_for_special_offer_code($_GET['a']) != '')

) {

    $_SESSION['ecommerce']['special_offer_code'] = trim($_GET['a']);

}



// if there is a page name, then get page that was requested

if ($page_name) {

    $query =

        "SELECT

            page.page_id,

            page.page_folder,

            page.page_type,

            page.page_title,

            page.page_meta_description,

            folder.folder_archived

        FROM page

        LEFT JOIN folder ON page.page_folder = folder.folder_id

        WHERE page.page_name = '" . escape($page_name) . "'";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    

    // if page does not exist then output error

    if (mysqli_num_rows($result) == 0) {

        output_error('<a href="javascript:history.go(-1)">Page cannot be found.</a>', 404);

    }

    

    $row = mysqli_fetch_assoc($result);

    $page_id = $row['page_id'];

    $folder_id = $row['page_folder'];

    $page_type = $row['page_type'];

    $page_title = $row['page_title'];

    $page_meta_description = $row['page_meta_description'];

    $folder_archived = $row['folder_archived'];

    

// else there is not a page name, so get random home page

} else {

    $query =

        "SELECT

            page.page_id,

            page.page_name,

            page.page_folder,

            page.page_type,

            page.page_title,

            page.page_meta_description,

            folder.folder_archived

        FROM page

        LEFT JOIN folder ON page.page_folder = folder.folder_id

        WHERE page.page_home = 'yes'

        ORDER BY RAND()

        LIMIT 1";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed');



    // if home page does not exist then output error

    if (mysqli_num_rows($result) == 0) {

        output_error('<a href="javascript:history.go(-1)">The home page cannot be found.</a>', 404);

    }

    

    $row = mysqli_fetch_assoc($result);

    $page_id = $row['page_id'];

    $page_name = $row['page_name'];

    $folder_id = $row['page_folder'];

    $page_type = $row['page_type'];

    $page_title = $row['page_title'];

    $page_meta_description = $row['page_meta_description'];

    $folder_archived = $row['folder_archived'];

}



// Determine if user has edit access to page.

// We will use this in several places below.

if (check_edit_access($folder_id) == true) {

    $edit_access = true;

} else {

    $edit_access = false;

}



// If this page is archived, and the visitor does not have edit access to this page, then output error.

if (($folder_archived == '1') && ($edit_access == false)) {

    log_activity("access denied to archived page ($page_name)", $_SESSION['sessionusername']);

    output_error('The page that you requested is no longer available because it has been archived.', 410);

}



// do different things depending on page type

switch ($page_type) {

    case 'login':

        // If the user is already logged in and the user does not have edit access to this page,

        // then send user to the login home.  We don't want to send users with edit access to the login home,

        // because we want to allow them to view this page in order to be able to edit it.

        if (

            (isset($_SESSION['sessionusername']) == true)

            && (isset($_SESSION['sessionpassword']) == true)

            && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)

            && (check_edit_access($folder_id) == false)

        ) {

            send_user_to_login_home();

        }

        

        break;

    

    case 'logout':

        // if user did not come from the control panel, and if they are currently logged in, then send the user to the logout script

        if (

            (

                (isset($_GET['from']) == false) 

                || ($_GET['from'] != 'control_panel')

            )

            && ($_GET['logged_out'] != true)

        ) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/logout.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;

        

    case 'registration entrance':

        // If the user is already logged in and the user does not have edit access to this page,

        // then send user to the login home.  We don't want to send users with edit access to the login home,

        // because we want to allow them to view this page in order to be able to edit it.

        if (

            (isset($_SESSION['sessionusername']) == true)

            && (isset($_SESSION['sessionpassword']) == true)

            && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)

            && (check_edit_access($folder_id) == false)

        ) {

            send_user_to_login_home();

        }

        

        break;

        

    case 'membership entrance':

        // If the user is already logged in and the user does not have edit access to this page,

        // then send user to the login home.  We don't want to send users with edit access to the login home,

        // because we want to allow them to view this page in order to be able to edit it.

        if (

            (isset($_SESSION['sessionusername']) == true)

            && (isset($_SESSION['sessionpassword']) == true)

            && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)

            && (check_edit_access($folder_id) == false)

        ) {

            send_user_to_login_home();

        }

        

        break;

        

    case 'my account':

        // if user is not logged in, send user to registration entrance screen to login or register

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;



    case 'my account profile':

        // if user is not logged in, send user to registration entrance screen to login or register

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;

        

    case 'view order':

        // if user is not logged in, send user to registration entrance screen to login or register

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;

        

    case 'affiliate sign up form':

        // if user is not logged in, send user to registration entrance screen to login or register

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;



    case 'custom form':

        

        // get page type properties

        $properties = get_page_type_properties($page_id, $page_type);

        

        // If the user is not logged in,

        // and auto-registration is disabled for the custom form,

        // and the custom form grants trial membership access or private access,

        // then send user to registration entrance screen.

        if (

            (!USER_LOGGED_IN)

            && ($properties['auto_registration'] == 0)

            &&

            (

                (

                    ($properties['membership'] == 1)

                    && ($properties['membership_days'] > 0)

                )

                || ($properties['private'] && $properties['private_folder_id'])

            )

        ) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        

        break;

        

    case 'form item view':

        // if the user has chosen to edit the submitted form,

        // and the user is not logged in

        // then determine if submitted form can be edited by a registered user, in order to see if we need to send user to registration entrance screen

        if (

            ($_GET['edit_submitted_form'] == 'true')

            && ((isset($_SESSION['sessionusername']) == false ) || (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false))

        ) {

            $properties = get_page_type_properties($page_id, $page_type);

            

            // if submitted form can be edited by a registered user, then send user to registration entrance screen

            if ($properties['submitted_form_editable_by_registered_user'] == 1) {

                header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

                exit();

            }

        }

        

        break;

}



$access_control_type = get_access_control_type($folder_id);



// do different things depending on access control type of page

switch ($access_control_type) {

    case 'private':



        // if user is not logged in then send user to login screen

        if (USER_LOGGED_IN == false) {



            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/index.php?send_to=' . urlencode(REQUEST_URL));

            exit();



        // else user is logged in

        } else {



            $access_check = check_private_access($folder_id);



            // If the user's private access to this folder has expired, then log activity and output error.

            if ($access_check['expired'] == true) {

                log_activity('access denied to private page (' . $page_name . ') because user\'s access had expired', $_SESSION['sessionusername']);

                output_error('You do not have access to view this page because your access has expired.', 403);



            // Otherwise if the user just doesn't have private access to this folder, then log activity and output error.

            } else if ($access_check['access'] == false) {

                log_activity('access denied to private page (' . $page_name . ')', $_SESSION['sessionusername']);

                output_error('You do not have access to view this page.', 403);

            }

        }



        break;



    case 'guest':

        // if user is not logged in or has an invalid login, then forward user to Registration Entrance screen

        // and, if the user is not browsing the site as a Guest

        if ((validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) 

            && ($_SESSION['software']['guest'] !== true)) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?allow_guest=true&send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;



    case 'registration':

        // if user is not logged in or has an invalid login, then forward user to Registration Entrance page

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        break;



    case 'membership':

        // if user is not logged in or has an invalid login, then forward user to Membership Entrance page

        if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {

            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php?send_to=' . urlencode(REQUEST_URL));

            exit();

        }

        

        // if user does not have edit rights to this folder, then we need to validate membership

        if (check_edit_access($folder_id) == false) {

            // get user information so we can find contact for user

            $query = "SELECT user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            $row = mysqli_fetch_assoc($result);

            $contact_id = $row['user_contact'];

            

            // get contact information

            $query = "SELECT member_id, expiration_date FROM contacts WHERE id = '" . $contact_id . "'";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            $row = mysqli_fetch_assoc($result);

            $member_id = $row['member_id'];

            $expiration_date = $row['expiration_date'];



            // if member id cannot be found and user has a user role, then output error

            if (!$member_id) {

                output_error('Access denied. The page that you requested requires membership. Your ' . h(MEMBER_ID_LABEL) . ' could not be found. You can view your membership status in your account area. Please contact us for more information.', 403);

            }



            // if expiration date is blank, then make expiration date high for lifetime membership

            if (!$expiration_date || ($expiration_date == '0000-00-00')) {

                $expiration_date = '9999-99-99';

            }



            // if membership has expired and user has a user role, then output error

            if ($expiration_date < date('Y-m-d')) {

                output_error('Access denied. The page that you requested requires membership. Your membership could not be verified.  You can view your membership status in your account area. Please contact us for more information.', 403);

            }

        }

        

        break;

}



// if a PDF is being requested, then output PDF instead of the page

if ($_GET['pdf'] == 'true') {

    // if server is running Windows, then check if wkhtmltopdf exists in a certain way and get path

    if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) == 'WIN') {

        $path = '"C:\program files\wkhtmltopdf\wkhtmltopdf.exe"';

        

        // if wkhtmltopdf does not exist, then output error

        if (file_exists(str_replace('"', '', $path)) == FALSE) {

            output_error('The page cannot be viewed as a PDF, because the PDF feature is not enabled for this website.');

        }

        

    // else the server is running Unix, so check if wkhtmltopdf exists in a different way and get path

    } else {

        // try to find wkhtmltopdf at /usr/bin/

        $path = '/usr/bin/wkhtmltopdf';

        

        // check to see if wkhtmltopdf exists there

        $data = shell_exec($path . ' --version');

        

        // if wkhtmltopdf was not found there, then check /usr/local/bin/

        if ($data == '') {

        	$path = '/usr/local/bin/wkhtmltopdf';

            

            $data = shell_exec($path . ' --version');

            

            // if it was not found there either, then output error

            if ($data == '') {

                output_error('The page cannot be viewed as a PDF, because the PDF feature is not enabled for this website.');

            }

        }

    }



    require_once(dirname(__FILE__) . '/get_page_content.php');

    

    // get content for page

    $content = get_page_content($page_id, $system_content = '', $extra_system_content = '', $mode = 'preview', $email = false);

    

    // change relative URLs to absolute URLs for links

    $content = preg_replace('/(<\s*a\s+[^>]*href\s*=\s*["\'])(?!ftp:\/\/|https:\/\/|mailto:|http:\/\/)(?:\/|\.\.\/|\.\/|)(.*?["\'].*?>)/is', "$1http://" . HOSTNAME . "/$2", $content);



    // if there is not a base tag in the content, then add a base tag

    if (preg_match('/<\s*base\s+[^>]*href\s*=\s*["\'](?:http:\/\/|https:\/\/|ftp:\/\/).*?["\']/is', $content) == 0) {

        // Originally we used URL_SCHEME in the base tag below instead of "http://", however this caused stylesheets to not work,

        // when site was in secure mode, so now we just use "http://".  We do not know why this happened.  Probably a bug or limitation in wkhtmltopdf.

        $base = '<head>' . "\n" . '<base href="http://' . HOSTNAME . '/" />';

        $content = preg_replace('/<head>/i', $base, $content);

    }

    

    $content = preg_replace(

        '/(<\s*img\s+[^>]*src\s*=\s*["\'])(' . escape_regex(OUTPUT_PATH) . ')(files\/)*(.*?["\'].*?>)/is',

        "$1file://" . FILE_DIRECTORY_PATH . "/$4",

        $content);

    

    $descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));

    

    $process = proc_open($path . ' - -', $descriptor, $pipes, NULL, NULL);

    

    fwrite($pipes[0], $content);

    fclose($pipes[0]);

    

    $content = stream_get_contents($pipes[1]);

    fclose($pipes[1]);

    stream_set_blocking($pipes[2], 0);

    $error = stream_get_contents($pipes[2]);

    fclose($pipes[2]);

    proc_terminate($process);

    

    // remove white space from the beginning and the end in order to do checks on the error

    $error = trim($error);

    

    // if there is an error, then output error

    // the error can contain the contents of the confirmation output for some reason,

    // so we have to check to make sure that the error is actually an error

    if (mb_substr($error, -4) != 'Done') {

        output_error(h('The PDF could not be generated because of the following error: ' . $error));

    }

    

    header('Content-Type: application/pdf');

    header('Content-disposition: filename=' . $page_name . '.pdf');



// else if rss is being requested, first verify that RSS needs to be outputted, and then output RSS if all conditions are met

} else if ($_GET['rss'] == 'true') {

    // init variables for XML

    $output_google_declaration = '';

    $output_channel_title = '';

    $output_channel_link = '';

    $output_channel_description = '';

    $output_rss_items = '';

    

    // if this page is a catalog or catalog detail page,

    // and there is an address name in the path,

    // then prepare the channel link with the page name and address name

    if (

        (($page_type == 'catalog') || ($page_type == 'catalog detail'))

        && (mb_strpos($_GET['page'], '/') !== FALSE)

    ) {

        // get address name from the current path

        $address_name = mb_substr(mb_substr($_GET['page'], mb_strpos($_GET['page'], '/')), 1);

        

        // prepare the channel link

        $output_channel_link = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($page_name)) . '/' . h(encode_url_path($address_name));

        

    // else prepare the channel link with just the page name

    } else {

        $output_channel_link = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($page_name));

    }

    

    // if this is a form list view, calendar view, catalog, catalog detail, or order form, and if it is public then continue

    if ((($page_type == 'form list view') || ($page_type == 'calendar view') || ($page_type == 'catalog') || ($page_type == 'catalog detail') || ($page_type == 'order form')) && ($access_control_type == 'public')) {

        // if this is a catalog, catalog detail, or order form, then do things that are required by those page types

        if (($page_type == 'catalog') || ($page_type == 'catalog detail') || ($page_type == 'order form')) {

            // output namespace for google in order to support custom RSS fields for products

            $output_google_declaration = ' xmlns:g="http://base.google.com/ns/1.0"';

            

            // define function that is used to get all products in a product group through multiple levels

            // the multi-level value is used to determine if we should just look in the current product group or go through the whole tree of product groups to look for products

            // the path will be used for the product type field in a Google data feed

            function get_products_in_product_group($parent_product_group_id, $multilevel, $product_groups = array(), $products_product_groups_xrefs = array(), $path = '') {

                // if the product groups array is empty, then this is the first time this function has run, so get data for arrays

                if (count($product_groups) == 0) {

                    // if multilevel is enabled, then get all product groups so we can determine which product groups are in the parent product group

                    if ($multilevel == TRUE) {

                        $query =

                            "SELECT

                                id,

                                parent_id

                            FROM product_groups

                            WHERE enabled = '1'";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        

                        $product_groups = array();

                        

                        // add each product group to an array

                        while ($row = mysqli_fetch_assoc($result)) {

                            $product_groups[] = $row;

                        }

                    }

                    

                    // get all relationships between products and product groups so we can determine which products are in which product groups

                    // order by the name.

                    // we are not ordering by sort order because we are getting all products, not just products in a certain product group,

                    // so sort order would not really work very well

                    $query =

                        "SELECT

                            products_groups_xref.product as product_id,

                            products_groups_xref.product_group as product_group_id

                        FROM products_groups_xref

                        LEFT JOIN products ON products_groups_xref.product = products.id

                        LEFT JOIN product_groups ON products_groups_xref.product_group = product_groups.id

                        WHERE

                            (products.enabled = '1')

                            AND (product_groups.enabled = '1')

                        ORDER BY products.name";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    

                    $products_product_groups_xrefs = array();

                    

                    // add each relationship to an array

                    while ($row = mysqli_fetch_assoc($result)) {

                        $products_product_groups_xrefs[] = $row;

                    }

                }

                

                // if the path is not blank, then add separator

                if ($path != '') {

                    $path .= ' > ';

                }

                

                // get product group name and short description in order to prepare path

                $query =

                    "SELECT

                        name,

                        short_description

                    FROM product_groups

                    WHERE id = '$parent_product_group_id'";

                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $row = mysqli_fetch_assoc($result);

                

                $name = $row['name'];

                $short_description = $row['short_description'];

                

                // if there is a short description then add it to the path

                if ($short_description != '') {

                    $path .= trim($short_description);

                    

                // else there is not a short description so add name to path

                } else {

                    $path .= trim($name);

                }

                

                $products = array();

                

                // if multilevel is enabled, then loop through product groups array in order to get all product groups that are in parent product group

                if ($multilevel == TRUE) {

                    foreach ($product_groups as $product_group) {

                        // if the parent product group id for this product group is equal to the parent product group id, then this is a child product group,

                        // so use recursion to get products for child product group

                        if ($product_group['parent_id'] == $parent_product_group_id) {

                            $products = array_merge($products, get_products_in_product_group($product_group['id'], $multilevel, $product_groups, $products_product_groups_xrefs, $path));

                        }

                    }

                }

                

                // loop through products_product_groups_xrefs array in order to get all products that are in the parent product group

                foreach ($products_product_groups_xrefs as $products_product_groups_xref) {

                    // if this product is in the parent product group, then add it to the array

                    if ($products_product_groups_xref['product_group_id'] == $parent_product_group_id) {

                        $products[] =

                            array (

                                'id' => $products_product_groups_xref['product_id'],

                                'product_group_id' => $parent_product_group_id,

                                'path' => $path

                            );

                    }

                }

                

                // initialize array that will be used for detecting if a product has already been added, so we can remove duplicates

                $added_products = array();

                

                // loop through all products in order to remove duplicates

                foreach ($products as $key => $product) {

                    // if this product already exists, then remove this item from the array

                    if (in_array($product['id'], $added_products) == TRUE) {

                        unset($products[$key]);

                        

                    // else this product does not already exist, so remember that it exists now

                    } else {

                        $added_products[] = $product['id'];

                    }

                }

                

                return $products;

            }

        }

        

        // prepare the XML based on the page type

        switch ($page_type) {

            case 'form list view':

                // if there is a page title, then use that for the channel title

                if ($page_title != '') {

                    $output_channel_title = h(trim($page_title));

                // else output page name with error

                } else {

                    $output_channel_title = '[No Web Browser Title property found for ' . h($page_name) . ']';

                }

                

                // if there is a page meta description, then use that for the channel description

                if ($page_meta_description != '') {

                    $output_channel_description = h(trim($page_meta_description));

                    

                // else output page name with error

                } else {

                    $output_channel_description = '[No Web Browser Description property found for ' . h($page_name) . ']';

                }

                

                // get info for this form list view

                // do left join for form item view page in order to make sure it still exists

                $query =

                    "SELECT

                        form_list_view_pages.custom_form_page_id,

                        form_list_view_pages.layout,

                        form_list_view_pages.viewer_filter,

                        form_list_view_pages.viewer_filter_submitter,

                        form_list_view_pages.viewer_filter_watcher,

                        form_list_view_pages.viewer_filter_editor,

                        form_item_view_page.page_id AS form_item_view_page_id,

                        form_item_view_page.page_name AS form_item_view_page_name,

                        form_item_view_page.comments AS form_item_view_page_comments

                    FROM form_list_view_pages

                    LEFT JOIN page AS form_item_view_page ON form_list_view_pages.form_item_view_page_id = form_item_view_page.page_id

                    WHERE

                        (form_list_view_pages.page_id = '" . escape($page_id) . "')

                        AND (form_list_view_pages.collection = 'a')";

                $result = mysqli_query(db::$con, $query) or output_error('Query failed');

                $row = mysqli_fetch_assoc($result);



                $custom_form_page_id = $row['custom_form_page_id'];

                $layout = $row['layout'];

                $viewer_filter = $row['viewer_filter'];

                $viewer_filter_submitter = $row['viewer_filter_submitter'];

                $viewer_filter_watcher = $row['viewer_filter_watcher'];

                $viewer_filter_editor = $row['viewer_filter_editor'];

                $form_item_view_page_id = $row['form_item_view_page_id'];

                $form_item_view_page_name = $row['form_item_view_page_name'];

                $form_item_view_page_comments = $row['form_item_view_page_comments'];

                

                // determine if there are any RSS fields for the custom form

                $query = "SELECT COUNT(*) FROM form_fields WHERE (page_id = '" . escape($custom_form_page_id) . "') AND (rss_field != '')";

                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $row = mysqli_fetch_row($result);

                

                // if there is at least one RSS field, then build XML

                if ($row[0] > 0) {

                    // if the form list view does not have a form item view page set in the page properties,

                    // then try to figure out the form item view page by looking for a link in the layout code

                    if ($form_item_view_page_id == '') {

                        preg_match('/{path}(pages\/)*(.*?)\?r=\^\^reference_code\^\^/is', $layout, $matches);

                    

                        $form_item_view_page_name = $matches[2];



                        // get id and whether comments are enabled for form item view page

                        $query =

                            "SELECT

                                page_id,

                                comments

                            FROM page

                            WHERE page_name = '" . escape($form_item_view_page_name) . "'";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $row = mysqli_fetch_assoc($result);



                        $form_item_view_page_id = $row['page_id'];

                        $form_item_view_page_comments = $row['comments'];

                    }

                    

                    // prepare form item view page id that will be used for joining tables for comments later

                    $form_item_view_page_id_for_comments = 0;



                    // if comments are enabled for form item view page, then store id

                    if ($form_item_view_page_comments == 1) {

                        $form_item_view_page_id_for_comments = $form_item_view_page_id;

                    }



                    // get standard fields

                    $standard_fields = get_standard_fields_for_view();



                    // get all custom fields for custom form

                    $query =

                        "SELECT

                            id,

                            name,

                            type,

                            multiple

                        FROM form_fields

                        WHERE page_id = '" . escape($custom_form_page_id) . "'";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $custom_fields = mysqli_fetch_items($result);



                    // get MySQL version so we can decide how we want to prepare the query

                    // in order to optimize performance

                    $query = "SELECT VERSION()";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $row = mysqli_fetch_row($result);

                    $mysql_version = $row[0];



                    $mysql_version_parts = explode('.', $mysql_version);

                    $mysql_major_version = $mysql_version_parts[0];

                    $mysql_minor_version = $mysql_version_parts[1];



                    // if the MySQL version is at least 4.1 then get submitted forms

                    // by using subqueries which are not available in previous version

                    if (

                        (

                            ($mysql_major_version == 4)

                            && ($mysql_minor_version >= 1)

                        )

                        || ($mysql_major_version >= 5)

                    ) {

                        $sql_select_category = "";



                        // Get info about category field, if one exists, so we know how to select it.

                        $query =

                            "SELECT

                                id,

                                type,

                                multiple

                            FROM form_fields

                            WHERE

                                (page_id = '" . escape($custom_form_page_id) . "')

                                AND (rss_field = 'category')";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



                        // if there is a category field, then select it

                        if (mysqli_num_rows($result) > 0) {

                            $category_field = mysqli_fetch_assoc($result);



                            // If the category field is a pick list and allow multiple selection is enabled,

                            // or it is a check box, then select field in a certain way, so that multiple values may be retrieved.

                            if (

                                (

                                    ($category_field['type'] == 'pick list')

                                    && ($category_field['multiple'] == 1)

                                )

                                || ($category_field['type'] == 'check box')

                            ) {

                                $sql_select_category = "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR '||') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $category_field['id'] . "')) AS category,";



                            // else the category field has any other type, so select field in a different way

                            } else {

                                $sql_select_category = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $category_field['id'] . "') LIMIT 1) AS category,";

                            }

                        }



                        $sql_select_title = "";



                        // Get info about title field, if one exists, so we know how to select it.

                        $query =

                            "SELECT id

                            FROM form_fields

                            WHERE

                                (page_id = '" . escape($custom_form_page_id) . "')

                                AND (rss_field = 'title')";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



                        // if there is a title field, then select it

                        if (mysqli_num_rows($result) > 0) {

                            $title_field = mysqli_fetch_assoc($result);



                            $sql_select_title = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $title_field['id'] . "') LIMIT 1) AS title,";

                        }



                        $sql_select_description = "";



                        // Get info about description field, if one exists, so we know how to select it.

                        $query =

                            "SELECT id

                            FROM form_fields

                            WHERE

                                (page_id = '" . escape($custom_form_page_id) . "')

                                AND (rss_field = 'description')";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



                        // if there is a description field, then select it

                        if (mysqli_num_rows($result) > 0) {

                            $description_field = mysqli_fetch_assoc($result);



                            $sql_select_description = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $description_field['id'] . "') LIMIT 1) AS description,";

                        }



                        // assume that we don't need to join various tables until we find out otherwise

                        $submitter_join_required = FALSE;

                        $last_modifier_join_required = FALSE;

                        $submitted_form_info_join_required = FALSE;

                        $newest_comment_join_required = FALSE;



                        $sql_where = "";



                        // Get filters in order to prepare SQL for where clause.

                        // We order by form_field_id ASC in order to get filters for standard

                        // fields first, in order to improve performance.

                        $query =

                            "SELECT

                                form_field_id,

                                standard_field,

                                operator,

                                value,

                                dynamic_value,

                                dynamic_value_attribute

                            FROM form_list_view_filters

                            WHERE page_id = '" . escape($page_id) . "'

                            ORDER BY form_field_id ASC";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $filters = mysqli_fetch_items($result);

                        

                        // loop through all filters in order to prepare SQL

                        foreach ($filters as $filter) {

                            // get operand 1

                            $operand_1 = '';

                            

                            // if a standard field was selected for filter

                            if ($filter['standard_field'] != '') {

                                // determine if a join is required based on the field

                                switch ($filter['standard_field']) {

                                    case 'submitter':

                                        $submitter_join_required = TRUE;

                                        break;



                                    case 'last_modifier':

                                        $last_modifier_join_required = TRUE;

                                        break;



                                    case 'number_of_views':

                                    case 'number_of_comments':

                                    case 'comment_attachments':

                                        $submitted_form_info_join_required = TRUE;

                                        break;



                                    case 'newest_comment_name':

                                    case 'newest_comment':

                                    case 'newest_comment_date_and_time':

                                    case 'newest_comment_id':

                                    case 'newest_activity_date_and_time':

                                        $submitted_form_info_join_required = TRUE;

                                        $newest_comment_join_required = TRUE;

                                        break;

                                }



                                // get operand 1 which is sql name by looping through standard fields

                                foreach ($standard_fields as $standard_field) {

                                    // if this is the standard field that was selected for this filter, then set operand 1 and break out of loop

                                    if ($standard_field['value'] == $filter['standard_field']) {

                                        $operand_1 = $standard_field['sql_name'];

                                        break;

                                    }

                                }

                                

                            // else a custom field was selected for the filter

                            } else {

                                // Assume that the custom field for this filter is not valid, until we find out otherwise.

                                // A custom field for a filter is valid if the custom field exists on the custom form for this form list view.

                                $custom_field_is_valid = FALSE;

                                

                                // get field type and determine if field is valid by looping through custom fields

                                foreach ($custom_fields as $custom_field) {

                                    // if this is the custom field that was selected for this filter,

                                    // then we know the custom field is valid, so break out of loop

                                    if ($custom_field['id'] == $filter['form_field_id']) {

                                        $field_type = $custom_field['type'];

                                        $field_multiple = $custom_field['multiple'];

                                        $custom_field_is_valid = TRUE;

                                        break;

                                    }

                                }

                                

                                // If the custom field for this filter is not valid, then do not prepare SQL filter and skip to the next filter.

                                // We have to do this in order to prevent a database error.

                                if ($custom_field_is_valid == FALSE) {

                                    continue;

                                }



                                // If this custom field is a pick list and allow multiple selection is enabled,

                                // or if this custom field is a check box, then prepare operand in a certain way,

                                // because there might be multiple values.

                                if (

                                    (

                                        ($field_type == 'pick list')

                                        && ($field_multiple == 1)

                                    )

                                    || ($field_type == 'check box')

                                ) {

                                    // If the operator is "is equal to" or "is not equal to" then prepare special filter in a way

                                    // so that it looks at individual values instead of a string of grouped values.

                                    if (

                                        ($filter['operator'] == 'is equal to')

                                        || ($filter['operator'] == 'is not equal to')

                                    ) {

                                        $operand_1 = "(SELECT COUNT(*) FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') AND (form_data.data = '" . escape($filter['value']) . "'))";



                                        // If the operator is "is equal to", then change operator to "is greater than",

                                        // because of the special comparison we are using.

                                        if ($filter['operator'] == 'is equal to') {

                                            $filter['operator'] = 'is greater than';



                                        // Otherwise the operator is "is not equal to", so change operator to "is equal to",

                                        // because of the special comparison we are using.

                                        } else {

                                            $filter['operator'] = 'is equal to';

                                        }



                                        // Change the filter value to 0 in order for our special comparison to work.

                                        $filter['value'] = '0';



                                    // Otherwise the operator is a different operator, so prepare operand 1,

                                    // so that multiple values are grouped into one concatenated string, separated by commas.

                                    } else {

                                        $operand_1 = "(SELECT GROUP_CONCAT(form_data.data ORDER BY form_data.id ASC SEPARATOR ', ') FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "'))";

                                    }



                                // else if this custom field is a file upload field, then prepare operand so it contains file name

                                } else if ($field_type == 'file upload') {

                                    $operand_1 = "(SELECT files.name FROM form_data LEFT JOIN files ON form_data.file_id = files.id WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";



                                // else this custom field has any other type, so prepare operand in the default way

                                } else {

                                    $operand_1 = "(SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $filter['form_field_id'] . "') LIMIT 1)";

                                }

                            }

                            

                            // if a basic value was entered, use that value

                            if ($filter['value'] != '') {

                                $operand_2 = $filter['value'];

                                

                            // else a dynamic value was entered, so use dynamic value

                            } else {

                                $operand_2 = get_dynamic_value($filter['dynamic_value'], $filter['dynamic_value_attribute']);

                            }

                            

                            $sql_where .= " AND (" . prepare_sql_operation($filter['operator'], $operand_1, $operand_2) . ")";

                        }



                        $sql_join = "";



                        // if a submitter join is required, then add join

                        if ($submitter_join_required == TRUE) {

                            $sql_join .= "LEFT JOIN user AS submitter ON forms.user_id = submitter.user_id\n";

                        }



                        // if a last modifier join is required, then add join

                        if ($last_modifier_join_required == TRUE) {

                            $sql_join .= "LEFT JOIN user AS last_modifier ON forms.last_modified_user_id = last_modifier.user_id\n";

                        }



                        // if a submitted form comment info join is required, then add join

                        if ($submitted_form_info_join_required == TRUE) {

                            $sql_join .= "LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '" . $form_item_view_page_id_for_comments . "'))\n";

                        }



                        // if a newest comment join is required, then add join

                        if ($newest_comment_join_required == TRUE) {

                            $sql_join .= "LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id\n";

                        }



                        $sql_viewer_filter = "";



                        // If viewer filter is enabled, then prepare SQL to filter results.

                        if ($viewer_filter == 1) {

                            // If none of the 3 filters are enabled or viewer is not logged in,

                            // then add an SQL filter that will guarantee that no results are returned.

                            if (

                                (

                                    ($viewer_filter_submitter == 0)

                                    && ($viewer_filter_watcher == 0)

                                    && ($viewer_filter_editor == 0)

                                )

                                || (USER_LOGGED_IN == false)

                            ) {

                                // Ideally, we should probably just not bother to run the query at all,

                                // rather than setting a where condition that can never be true,

                                // however updating all of the logic below to deal with that is too complicated for now.

                                $sql_viewer_filter = "AND (TRUE = FALSE)";



                            // Otherwise at least one of the 3 filters is enabled or viewer is logged in,

                            // so prepare SQL filter.

                            } else {

                                // Assume that user does not have edit access until we find out otherwise.

                                // It is important to find out of the viewer has edit access to the custom form

                                // because we don't need to add filters if the editor filter is enable and the viewer

                                // has edit rights to the custom form, because then the viewer has access to all submitted forms

                                // so we want to show them all.

                                $edit_custom_form_access = false;



                                // If editor filter is enabled, then determine if viewer has edit access to custom form.

                                if ($viewer_filter_editor == 1) {

                                    // If this user is a manager or above, then user has edit access to custom form.

                                    if (USER_ROLE < 3) {

                                        $edit_custom_form_access = true;



                                    // Otherwise this user has a user role, so check if user has edit access to custom form.

                                    } else {

                                        // Get folder id that custom form is in, in order to check if user

                                        // has edit access to custom form.

                                        $custom_form_folder_id = db_value("SELECT page_folder FROM page WHERE page_id = '" . escape($custom_form_page_id) . "'");



                                        // If user has edit access to custom form, then remember that.

                                        if (check_edit_access($custom_form_folder_id) == true) {

                                            $edit_custom_form_access = true;

                                        }

                                    }

                                }



                                // If the editor filter is disabled, or if the viewer does not have edit access

                                // to custom form, then it is necessary to filter the submitted forms based on who the viewer is.

                                if (($viewer_filter_editor == 0) || ($edit_custom_form_access == false)) {

                                    $sql_viewer_filters = "";



                                    // If the submitter filter is enabled, then prepare SQL for forms.user_id column.

                                    if ($viewer_filter_submitter == 1) {

                                        // We only do the forms.user_id column and not the forms.form_editor_user_id column yet,

                                        // because the order of these filters are important for performance reasons.

                                        $sql_viewer_filters .= "(forms.user_id = '" . USER_ID . "')";

                                    }



                                    // If the editor filter is enabled, then prepare SQL for that filter.

                                    if ($viewer_filter_editor == 1) {

                                        // If this is not the first filter, then add an "OR" for separation.

                                        if ($sql_viewer_filters != '') {

                                            $sql_viewer_filters .= " OR ";

                                        }



                                        $sql_viewer_filters .= "(forms.form_editor_user_id = '" . USER_ID . "')";

                                    }



                                    // If the submitter filter is enabled, then determine if we need to add an SQL filter for

                                    // email address connect-to-contact field. This is important because we also consider that field's

                                    // value to be the submitter.

                                    if ($viewer_filter_submitter == 1) {

                                        // Check if there is a connect-to-contact email address field for the custom form.

                                        $submitter_field_id = db_value(

                                            "SELECT id

                                            FROM form_fields

                                            WHERE

                                                (page_id = '" . escape($custom_form_page_id) . "')

                                                AND (contact_field = 'email_address')");



                                        // If a submitter field was found for the custom form, then include SQL filter for it.

                                        if ($submitter_field_id) {

                                            // If this is not the first filter, then add an "OR" for separation.

                                            if ($sql_viewer_filters != '') {

                                                $sql_viewer_filters .= " OR ";

                                            }



                                            $sql_viewer_filters .= "((SELECT form_data.data FROM form_data WHERE (form_data.form_id = forms.id) AND (form_data.form_field_id = '" . $submitter_field_id . "') LIMIT 1) = '" . escape(USER_EMAIL_ADDRESS) . "')";

                                        }

                                    }



                                    // If the watcher filter is enabled, then determine if we should prepare SQL for that filter.

                                    if ($viewer_filter_watcher == 1) {

                                        // Determine if form item view page has comments and watchers enabled,

                                        // so that we can determine if it is necessary to check if viewer is a watcher.

                                        $watcher_page_id = db_value(

                                            "SELECT page_id

                                            FROM page

                                            WHERE

                                                (page_id = '" . escape($form_item_view_page_id) . "')

                                                AND (page_type = 'form item view')

                                                AND (comments = '1')

                                                AND (comments_watcher_email_page_id != '0')");



                                        // If this is not the first filter, then add an "OR" for separation.

                                        if ($sql_viewer_filters != '') {

                                            $sql_viewer_filters .= " OR ";

                                        }



                                        // If a watcher page was found, then include a check to see if viewer is a watcher.

                                        // We are using EXISTS because supposedly it offers the best performance for this type of query.

                                        if ($watcher_page_id) {

                                            $sql_viewer_filters .=

                                                "(

                                                    EXISTS (SELECT 1

                                                    FROM watchers

                                                    WHERE

                                                        (watchers.page_id = '$watcher_page_id')

                                                        AND (watchers.item_id = forms.id)

                                                        AND (watchers.item_type = 'submitted_form')

                                                        AND

                                                        (

                                                            (watchers.user_id = '" . USER_ID . "')

                                                            OR (watchers.email_address = '" . escape(USER_EMAIL_ADDRESS) . "')

                                                        )

                                                    LIMIT 1)

                                                )";



                                        // Otherwise, a watcher page was not found, so we need to add a filter that computes to false

                                        // in case this is the only filter.  We have to add this filter so that all submitted forms are not

                                        // returned if this is the only filter.

                                        } else {

                                            $sql_viewer_filters .= "(TRUE = FALSE)";

                                        }

                                    }



                                    $sql_viewer_filter = "AND ($sql_viewer_filters)";

                                }

                            }

                        }



                        // get submitted forms

                        $query = 

                            "SELECT 

                                " . $sql_select_category . "

                                " . $sql_select_title . "

                                " . $sql_select_description . "

                                forms.reference_code,

                                forms.address_name,

                                forms.submitted_timestamp

                            FROM forms

                            $sql_join

                            WHERE

                                (forms.page_id = '" . escape($custom_form_page_id) . "')

                                $sql_where

                                $sql_viewer_filter

                            ORDER BY forms.submitted_timestamp DESC

                            LIMIT 50";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $submitted_forms = mysqli_fetch_items($result);



                        $pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);



                        // loop through submitted forms, in order to prepare output

                        foreach ($submitted_forms as $submitted_form) {

                            $output_rss_categories = '';



                            // If there is at least one category, then output it.

                            if ($submitted_form['category'] != '') {

                                // If there are multiple categories, then output all of them.

                                if (mb_strpos($submitted_form['category'], '||') !== FALSE) {

                                    $categories = explode('||', $submitted_form['category']);

                                    

                                    // Loop through categories in order to output them.

                                    foreach ($categories as $category) {

                                        $output_rss_categories .= '<category>' . h($category) . '</category>';

                                    }



                                // Otherwise there is just one category, so output it.

                                } else {

                                    $output_rss_categories = '<category>' . h($submitted_form['category']) . '</category>';

                                }



                            // Otherwise there is not a category, so output notice.

                            } else {

                                $output_rss_categories = '<category>There is not an RSS category specified in the form.</category>';

                            }



                            $output_rss_title = '';



                            // If there is a title, then output it.

                            if ($submitted_form['title'] != '') {

                                $output_rss_title = h($submitted_form['title']);



                            // Otherwise there is not a title, so output notice.

                            } else {

                                $output_rss_title = 'There is not an RSS title specified in the form.';

                            }



                            $output_rss_description = '';



                            // If there is a description, then output it.

                            if ($submitted_form['description'] != '') {

                                $output_rss_description = h($submitted_form['description']);



                            // Otherwise there is not a description, so output notice.

                            } else {

                                $output_rss_description = 'There is not an RSS description specified in the form.';

                            }

                            

                            $output_form_item_view_page_link = '';

                            

                            // if there is a form item view page, then add link to it

                            if ($form_item_view_page_id != '') {

                                // If pretty URLs are enabled for the custom form, and this submitted form has

                                // an address name, then prepare pretty URL.

                                if (($pretty_urls == true) && ($submitted_form['address_name'] != '')) {

                                    $output_form_item_view_page_link = URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path($page_name)) . '/' . h($submitted_form['address_name']);



                                // Otherwise, prepare ugly URL.

                                } else {

                                    $output_form_item_view_page_link = URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path($form_item_view_page_name)) . '?r=' . $submitted_form['reference_code'];

                                }

                            

                            // else there is not a form item view page so output an error

                            } else {

                                $output_form_item_view_page_link = 'There is not a Form Item View Page set in the properties for the Form List View Page.';

                            }

                            

                // build RSS item

                $output_rss_items .=

                    '<item>

            ' . $output_rss_categories . '

            <title>' . $output_rss_title . '</title>

            <description>' . $output_rss_description . '</description>

            <link>' . $output_form_item_view_page_link . '</link>

            <pubDate>' . date('D, d M Y H:i:s O', $submitted_form['submitted_timestamp']) . '</pubDate>

        </item>

        ';

                        }



                    // else the MySQL version is below 4.1, so get form list view

                    // by using joins instead because subqueries are not supported

                    } else {

                        $sql_field_joins = '';

                        

                        // loop through form fields, in order to prepare SQL field joins

                        foreach ($custom_fields as $custom_field) {

                            $sql_field_joins .= "LEFT JOIN form_data AS field_" . $custom_field['id'] . " ON ((forms.id = field_" . $custom_field['id'] . ".form_id) AND (field_" . $custom_field['id'] . ".form_field_id = '" . $custom_field['id'] . "'))\n";

                        }

                        

                        // Get filters.

                        // We order by form_field_id ASC in order to get filters for standard

                        // fields first, in order to improve performance.

                        $query =

                            "SELECT

                                form_field_id,

                                standard_field,

                                operator,

                                value,

                                dynamic_value,

                                dynamic_value_attribute

                            FROM form_list_view_filters

                            WHERE page_id = '" . escape($page_id) . "'

                            ORDER BY form_field_id ASC";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



                        $filters = array();

                        

                        while ($row = mysqli_fetch_assoc($result)) {

                            $filters[] = $row;

                        }

                        

                        // prepare SQL for filters

                        $sql_filters = '';

                        

                        // loop through all filters in order to prepare SQL

                        foreach ($filters as $filter) {

                            // get operand 1

                            $operand_1 = '';

                            

                            // if a standard field was selected for filter

                            if ($filter['standard_field'] != '') {

                                // get operand 1 which is sql name by looping through standard fields

                                foreach ($standard_fields as $standard_field) {

                                    // if this is the standard field that was selected for this filter, then set operand 1 and break out of loop

                                    if ($standard_field['value'] == $filter['standard_field']) {

                                        $operand_1 = $standard_field['sql_name'];

                                        break;

                                    }

                                }

                                

                            // else a custom field was selected for the filter

                            } else {

                                // assume that the custom field for this filter is not valid, until we find out otherwise.  A custom field for a filter is valid if the custom field exists on the custom form for this form list view.

                                $custom_field_is_valid = FALSE;

                                

                                // get field type by looping through custom fields

                                foreach ($custom_fields as $custom_field) {

                                    // if this is the custom field that was selected for this filter, then set field type and break out of loop

                                    if ($custom_field['id'] == $filter['form_field_id']) {

                                        $field_type = $custom_field['type'];

                                        $custom_field_is_valid = TRUE;

                                        break;

                                    }

                                }

                                

                                // if the custom field for this filter is not valid, then do not prepare SQL filter and skip to the next filter.  We have to do this in order to prevent a database error.

                                if ($custom_field_is_valid == FALSE) {

                                    continue;

                                }

                                

                                $operand_1 = 'field_' . $filter['form_field_id'] . '.data';

                            }

                            

                            // if a basic value was entered, use that value

                            if ($filter['value'] != '') {

                                $operand_2 = $filter['value'];

                                

                            // else a dynamic value was entered, so use dynamic value

                            } else {

                                $operand_2 = get_dynamic_value($filter['dynamic_value'], $filter['dynamic_value_attribute']);

                            }

                            

                            $sql_filters .= "AND (" . prepare_sql_operation($filter['operator'], $operand_1, $operand_2) . ") ";

                        }

                        

                        $submitted_forms = array();



                        // run query in order to fix the following error on certain hosting providers (e.g. BlueHost)

                        // The SELECT would examine more than MAX_JOIN_SIZE rows; check your WHERE and use SET SQL_BIG_SELECTS=1 or SET SQL_MAX_JOIN_SIZE=# if the SELECT is okay

                        $query = "SET SQL_BIG_SELECTS=1";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        

                        // get submitted forms

                        $query = 

                            "SELECT 

                                forms.id,

                                forms.reference_code,

                                forms.address_name,

                                forms.submitted_timestamp

                            FROM forms

                            LEFT JOIN user as submitter ON forms.user_id = submitter.user_id

                            LEFT JOIN user as last_modifier ON forms.last_modified_user_id = last_modifier.user_id

                            LEFT JOIN submitted_form_info ON ((forms.id = submitted_form_info.submitted_form_id) AND (submitted_form_info.page_id = '$form_item_view_page_id_for_comments'))

                            LEFT JOIN comments AS newest_comment ON submitted_form_info.newest_comment_id = newest_comment.id

                            $sql_field_joins

                            WHERE

                                forms.page_id = '" . escape($custom_form_page_id) . "'

                                $sql_filters

                            GROUP BY forms.id 

                            ORDER BY submitted_timestamp DESC

                            LIMIT 50";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        

                        // put submitted forms into an array

                        while ($row = mysqli_fetch_assoc($result)) {

                            $submitted_forms[] = $row;

                        }



                        $pretty_urls = check_if_pretty_urls_are_enabled($custom_form_page_id);

                        

                        // loop through submitted forms, in order to prepare output

                        foreach ($submitted_forms as $submitted_form) {

                            $submitted_form_data_and_rss_fields_in_array = array();

                            

                            // get submitted form data

                            $query = "SELECT

                                        form_data.data as submitted_form_data,

                                        form_fields.rss_field

                                     FROM form_data

                                     LEFT JOIN form_fields ON form_data.form_field_id = form_fields.id

                                     WHERE 

                                        (form_data.form_id = '" . escape($submitted_form['id']) . "')

                                        AND (form_fields.rss_field != '')

                                     ORDER BY form_data.id";

                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            

                            // put submitted form data into an array

                            while ($row = mysqli_fetch_assoc($result)) {

                                $submitted_form_data_and_rss_fields_in_array[] = array('submitted_form_data' => $row['submitted_form_data'], 'rss_field' => $row['rss_field']);

                            }

                            

                            $output_rss_categories = '';

                            $output_rss_title = '';

                            $output_rss_description = '';

                            

                            // loop through submitted form data and put it into the appropriate rss field

                            foreach ($submitted_form_data_and_rss_fields_in_array as $item) {

                                switch($item['rss_field']) {

                                    case 'category':

                                        // if the category is not blank, then output category

                                        if ($item['submitted_form_data'] != '') {

                                            $output_rss_categories .= '<category>' . h($item['submitted_form_data']) . '</category>';

                                        }

                                        break;

                                    

                                    case 'title':

                                        $output_rss_title = h($item['submitted_form_data']);

                                        break;

                                    

                                    case 'description':

                                        $output_rss_description = h($item['submitted_form_data']);

                                        break;

                                }

                            }

                            

                            // if there is not an rss category from the database, then output error in XML

                            if ($output_rss_categories == '') {

                                $output_rss_categories = '<category>There is not an RSS category specified in the form.</category>';

                            }

                            

                            // if there is not an rss title from the database, then output error in XML

                            if ($output_rss_title == '') {

                                $output_rss_title = 'There is not an RSS title specified in the form.';

                            }

                            

                            // if there is not an rss description from the database, then output error in XML

                            if ($output_rss_description == '') {

                                $output_rss_description = 'There is not an RSS description specified in the form.';

                            }

                            

                            $output_form_item_view_page_link = '';

                            

                            // if there is a form item view page, then add link to it

                            if ($form_item_view_page_id != '') {

                                // If pretty URLs are enabled for the custom form, and this submitted form has

                                // an address name, then prepare pretty URL.

                                if (($pretty_urls == true) && ($submitted_form['address_name'] != '')) {

                                    $output_form_item_view_page_link = URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path($page_name)) . '/' . h($submitted_form['address_name']);



                                // Otherwise, prepare ugly URL.

                                } else {

                                    $output_form_item_view_page_link = URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path($form_item_view_page_name)) . '?r=' . $submitted_form['reference_code'];

                                }

                            

                            // else there is not a form item view page so output an error

                            } else {

                                $output_form_item_view_page_link = 'There is not a Form Item View Page set in the properties for the Form List View Page.';

                            }





                // build RSS item

                $output_rss_items .=

                    '<item>

            ' . $output_rss_categories . '

            <title>' . $output_rss_title . '</title>

            <description>' . $output_rss_description . '</description>

            <link>' . $output_form_item_view_page_link . '</link>

            <pubDate>' . date('D, d M Y H:i:s O', $submitted_form['submitted_timestamp']) . '</pubDate>

        </item>

        ';

                        }

                    }

                

                // else there are not any RSS fields defined for this custom form, so output an error in the channel

                } else {

                    $output_channel_title = 'No feed is available for ' . h($page_name);

                    $output_channel_description = 'RSS Elements must be defined in the Custom Form before a feed can be created.';

                    set_response_code(404);

                }

                

                break;

            

            case 'calendar view':

                // if there is a page title, then use that for the channel title

                if ($page_title != '') {

                    $output_channel_title = h(trim($page_title));

                // else output page name with error

                } else {

                    $output_channel_title = '[No Web Browser Title property found for ' . h($page_name) . ']';

                }

                

                // if there is a page meta description, then use that for the channel description

                if ($page_meta_description != '') {

                    $output_channel_description = h(trim($page_meta_description));

                    

                // else output page name with error

                } else {

                    $output_channel_description = '[No Web Browser Description property found for ' . h($page_name) . ']';

                }

                

                // get the id for all calendars attached to this page

                $query = "SELECT calendar_id FROM calendar_views_calendars_xref WHERE page_id = '" . escape($page_id) . "'";

                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');



                $calendar_ids = array();

                

                // put calendars for this page into an array

                while ($row = mysqli_fetch_assoc($result)) {

                    $calendar_ids[] = $row;

                }

                

                $sql_where = '';

                

                // loop through all calendars in order to prepare where statement for sql

                foreach ($calendar_ids as $calendar_id) {

                    // if where statement is not blank then add OR

                    if ($sql_where != '') {

                        $sql_where .= ' OR ';

                    }

                    

                    $sql_where .= "calendar_events_calendars_xref.calendar_id = '" . escape($calendar_id['calendar_id']) . "'";

                }

                

                // if where statement is not blank then format sql where statment for sql

                if ($sql_where != '') {

                    $sql_where = ' AND (' . $sql_where . ')';

                }

                

                // get all calendar events

                $query =

                    "SELECT

                        calendar_events.id,

                        calendar_events.name,

                        calendar_events.short_description,

                        calendar_events.all_day,

                        calendar_events.start_time,

                        calendar_events.recurrence_number,

                        calendar_events.recurrence_type,

                        calendar_events.recurrence_day_sun,

                        calendar_events.recurrence_day_mon,

                        calendar_events.recurrence_day_tue,

                        calendar_events.recurrence_day_wed,

                        calendar_events.recurrence_day_thu,

                        calendar_events.recurrence_day_fri,

                        calendar_events.recurrence_day_sat,

                        calendar_events.recurrence_month_type

                    FROM calendar_events

                    LEFT JOIN calendar_events_calendars_xref ON calendar_events_calendars_xref.calendar_event_id = calendar_events.id

                    WHERE

                        published = '1'

                        $sql_where";

                $result = mysqli_query(db::$con, $query);

                

                $calendar_events = array();



                // Add each event information to array

                while ($row = mysqli_fetch_assoc($result)) {

                    $calendar_events[] = $row;

                }

                

                // get calendar event exceptions

                $query =

                    "SELECT

                        calendar_event_id,

                        recurrence_number

                    FROM calendar_event_exceptions";

                $result = mysqli_query(db::$con, $query);



                $calendar_event_exceptions = array();

                

                // Place all of the exceptions into an array

                while ($row = mysqli_fetch_assoc($result)) {

                    $calendar_event_exceptions[] = $row;

                }

                

                $events = array();

                $processed_events = array();

                $start_date_for_comparison = date('Y-m-d');

                

                // loop through all calendar events

                foreach ($calendar_events as $calendar_event) {

                    $id = $calendar_event['id'];

                    $name = $calendar_event['name'];

                    $short_description = $calendar_event['short_description'];

                    $all_day = $calendar_event['all_day'];

                    $event_start_date_and_time = $calendar_event['start_time'];

                    $recurrence_number = $calendar_event['recurrence_number'];

                    $recurrence_type = $calendar_event['recurrence_type'];

                    $recurrence_day_sun = $calendar_event['recurrence_day_sun'];

                    $recurrence_day_mon = $calendar_event['recurrence_day_mon'];

                    $recurrence_day_tue = $calendar_event['recurrence_day_tue'];

                    $recurrence_day_wed = $calendar_event['recurrence_day_wed'];

                    $recurrence_day_thu = $calendar_event['recurrence_day_thu'];

                    $recurrence_day_fri = $calendar_event['recurrence_day_fri'];

                    $recurrence_day_sat = $calendar_event['recurrence_day_sat'];

                    $recurrence_month_type = $calendar_event['recurrence_month_type'];

                    

                    // If the event id has not already been processed, process it and log that it has been processed.

                    if (!in_array($id, $processed_events)) {

                        // Keep track that we have already processed this event.

                        $processed_events[] = $id;

                        

                        // split event start date and time into parts

                        $event_start_date_and_time_parts = explode(' ', $event_start_date_and_time);

                        $event_start_date = $event_start_date_and_time_parts[0];

                        $event_start_time = $event_start_date_and_time_parts[1];

                        

                        $calendar_exceptions = array();



                        // if recurrence number is greater than zero, then split event start date into parts, that we will use later

                        if ($recurrence_number > 0) {

                            $event_start_date_parts = explode('-', $event_start_date);

                            $event_start_year = $event_start_date_parts[0];

                            $event_start_month = $event_start_date_parts[1];

                            $event_start_day = $event_start_date_parts[2];

                            

                            // Loop through all calendar exceptions and separate the ones we need for the event we are working with into $calendar_exceptions

                            foreach ($calendar_event_exceptions as $calendar_event_exception) {

                                if ($calendar_event_exception[calendar_event_id] == $id) {

                                    $calendar_exceptions[] = $calendar_event_exception[recurrence_number];

                                }

                            }



                            // If this is a monthly event and the month type is "day of the week",

                            // then determine which week in the month the event is on.

                            // If the week is 1-4 then we will use that, however if the week is 5,

                            // then we interpret that as the last week.

                            if (

                                ($recurrence_type == 'month')

                                && ($recurrence_month_type == 'day_of_the_week')

                            ) {

                                $day_of_the_week = date('l', strtotime($event_start_date));

                                $first_day_of_the_month_timestamp = strtotime($event_start_year . '-' . $event_start_month . '-01');



                                $week = '';



                                // Create a loop in order to determine which week event falls on.

                                // We only loop through 4 weeks, because we are going to set "last" below for 5th week.

                                for ($week_index = 0; $week_index <= 3; $week_index++) {

                                    // If the event is in this week, then remember the week number and break out of this loop.

                                    if ($event_start_date == date('Y-m-d', strtotime('+' . $week_index . ' week ' . $day_of_the_week, $first_day_of_the_month_timestamp))) {

                                        $week = $week_index + 1;

                                        break;

                                    }

                                }



                                // If a week was not found, then that means it falls on the 5th week,

                                // so set it to be the last week.

                                if ($week == '') {

                                    $week = 'last';

                                }

                            }

                        }

                        

                        // loop in order to create a new event for each recurrence

                        for ($i = 0; $i <= $recurrence_number; $i++) {

                            // We will use this variable to stop the calculations if the recurrence number is hidden by an exception

                            $halt_on_exception = '0';

                            $hidden_exception = '0';

                            // If calendar_exceptions is not empty.

                            if (count($calendar_exceptions) > 0) {

                                // Check if this recurrence is an exception.

                                if (in_array($i, $calendar_exceptions)) {

                                    $halt_on_exception = '1';

                                    

                                // The recurrence was not an exception, so do not hide it.

                                } else {

                                    $hidden_exception = '0';

                                }

                            }

                            

                            // if recurrence number is greater than 0, then adjust event start date

                            if ($i > 0) {

                                // adjust event start date depending on recurrence type

                                switch ($recurrence_type) {

                                    // Daily

                                    case 'day':

                                        $count = 0;



                                        // Loop through days in the future until we find a date that is valid

                                        // based on the valid days of the week that were selected.

                                        while (true) {

                                            $new_time = strtotime('+1 day', strtotime($event_start_date));

                                            $event_start_date = date('Y-m-d', $new_time);

                                            $day_of_the_week = strtolower(date('D', $new_time));



                                            // If this day of the week is valid for this calendar event,

                                            // then we have found a valid date, so break out of the loop.

                                            if (${'recurrence_day_' . $day_of_the_week} == 1) {

                                                break;

                                            }



                                            $count++;



                                            // If we have already looped 7 times, then something is wrong,

                                            // so break out of this loop and the recurrence loop above.

                                            // This should never happen but is added just in case in order to

                                            // prevent an endless loop.

                                            if ($count == 7) {

                                                break 3;

                                            }

                                        }



                                        break;

                                        

                                    // Weekly

                                    case 'week':

                                        $new_time = mktime(0, 0, 0, $event_start_month, $event_start_day + (7 * $i), $event_start_year);

                                        $event_start_date = date('Y', $new_time) . '-' . date('m', $new_time) . '-' . date('d', $new_time);

                                        break;



                                    // Monthly

                                    case 'month':

                                        switch ($recurrence_month_type) {

                                            case 'day_of_the_month':

                                                $new_time = mktime(0, 0, 0, $event_start_month + $i, 1, $event_start_year);

                                                $new_event_start_year = date('Y', $new_time);

                                                $new_event_start_month = date('m', $new_time);

                                                $new_event_start_day = $event_start_day;



                                                // if date is not valid, then get last date for month

                                                if (checkdate($new_event_start_month, $new_event_start_day, $new_event_start_year) == false) {

                                                    $new_event_start_day = date('t', mktime(0, 0, 0, $new_event_start_month, 1, $new_event_start_year));

                                                }



                                                $event_start_date = $new_event_start_year . '-' . $new_event_start_month . '-' . $new_event_start_day;



                                                break;



                                            case 'day_of_the_week':

                                                $first_day_of_the_month_timestamp = mktime(0, 0, 0, $event_start_month + $i, 1, $event_start_year);



                                                // If the week is 1-4 then find the date in a certain way.

                                                if ($week != 'last') {

                                                    $week_index = $week - 1;



                                                    $new_time = strtotime('+' . $week_index . ' week ' . $day_of_the_week, $first_day_of_the_month_timestamp);



                                                // Otherwise the week is last, so find the date in a different way.

                                                } else {

                                                    $last_day_of_the_month_timestamp = strtotime(date('Y-m-t', $first_day_of_the_month_timestamp));



                                                    // If the last day of the month happens to be the right day of the week,

                                                    // then thats that day that we want.

                                                    if (date('l', $last_day_of_the_month_timestamp) == $day_of_the_week) {

                                                        $new_time = $last_day_of_the_month_timestamp;



                                                    // Otherwise find the day of the week that we want in the last week of the month.

                                                    } else {

                                                        $new_time = strtotime('last ' . $day_of_the_week, $last_day_of_the_month_timestamp);

                                                    }

                                                }



                                                $event_start_date = date('Y-m-d', $new_time);



                                                break;

                                        }



                                        break;

                                    

                                    // Yearly

                                    case 'year':

                                        $new_event_start_year = $event_start_year + $i;

                                        $new_event_start_month = $event_start_month;

                                        $new_event_start_day = $event_start_day;

                                        

                                        // if date is not valid, then get last date for month

                                        if (checkdate($new_event_start_month, $new_event_start_day, $new_event_start_year) == false) {

                                            $new_event_start_day = date('t', mktime(0, 0, 0, $new_event_start_month, 1, $new_event_start_year));

                                        }

                                        

                                        $event_start_date = $new_event_start_year . '-' . $new_event_start_month . '-' . $new_event_start_day;

                                        break;

                                }

                            }

                            

                            if ($halt_on_exception == '0') {

                                // if the event start date is on or after the current date then add event to array

                                if ($start_date_for_comparison <= $event_start_date) {

                                    $events[] =

                                        array (

                                            'id' => $id,

                                            'name' => $name,

                                            'short_description' => $short_description,

                                            'event_start_date' => $event_start_date,

                                            'event_start_time' => $event_start_time,

                                            'event_start_date_and_time' => $event_start_date . ' ' . $event_start_time,

                                            'recurring_event' => $recurrence_number,

                                            'recurrence_number' => $i,

                                            'all_day' => $all_day,

                                            'hidden_exception' => $hidden_exception

                                        );

                                }

                            }

                        }

                    }

                }

                

                // create array for storing event start dates and times, so that we can sort events later

                $event_start_dates_and_times = array();

                

                foreach ($events as $key => $event) {

                   $event_start_dates_and_times[$key] = $event['event_start_date_and_time'];

                }

                

                // sort events by date and time

                array_multisort($event_start_dates_and_times, SORT_ASC, $events);

                

                // get the next 50 events

                $events = array_slice($events, 0, 50);

                

                // get the calendar event view page name that is tied to this calendar view page, so that we can supply link in XML

                $query =

                    "SELECT page.page_name

                    FROM calendar_view_pages

                    LEFT JOIN page ON calendar_view_pages.calendar_event_view_page_id = page.page_id

                    WHERE calendar_view_pages.page_id = '" . escape($page_id) . "'";

                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                $row = mysqli_fetch_assoc($result);

                $calendar_event_view_page_name = $row['page_name'];

                

                // output each event in XML

                foreach ($events as $event) {

                    $output_rss_categories = '';

                    $output_rss_description = '';

                    

                    // get the calendars that this item shows up on

                    $query = 

                        "SELECT

                            calendars.name

                        FROM calendar_events_calendars_xref

                        LEFT JOIN calendars ON calendars.id = calendar_events_calendars_xref.calendar_id

                        WHERE calendar_events_calendars_xref.calendar_event_id = '" . escape($event['id']) . "'";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    

                    // Output rss categories to XML if there are any results

                    while($row = mysqli_fetch_assoc($result)) {

                        $output_rss_categories .= '<category>' . h($row['name']) . '</category>';

                    }

                    

                    // if there is a short description from the database, then output it in XML

                    if ($event['short_description'] != '') {

                        $output_rss_description = h($event['short_description']);

                        

                    // else output error

                    } else {

                        $output_rss_description = 'There is not an RSS description specified in the Calendar Event.';

                    }

                    

                    $output_calendar_event_view_page_link = '';

                    

                    // if there is a calendar event view page for this calendar then create a link to it, this is to be outputted as the item link

                    if ($calendar_event_view_page_name != '') {

                        $output_recurring_query_string = '';

                        

                        // If the event is recurring then set URL parameter

                        if ($event['recurrence_number'] > 0) {

                            $output_recurring_query_string = '&amp;recurrence_number=' . $event['recurrence_number'];

                        }

                        

                        $output_calendar_event_view_page_link = URL_SCHEME . HOSTNAME . OUTPUT_PATH . h(encode_url_path($calendar_event_view_page_name)) . '?id=' . $event['id'] . $output_recurring_query_string;

                        

                    // else output a error

                    } else {

                        $output_calendar_event_view_page_link = 'There is not a Calendar Event View Page linked to this Calendar View Page.';

                    }

                    





                // build RSS item

                $output_rss_items .=

                    '<item>

            ' . $output_rss_categories . '

            <title>' . h($event['name']) . '</title>

            <description>' . $output_rss_description . '</description>

            <link>' . $output_calendar_event_view_page_link . '</link>

            <pubDate>' . date('D, d M Y H:i:s O', strtotime($event['event_start_date_and_time'])) . '</pubDate>

        </item>

        ';

                }

                

                break;

                

            case 'catalog':

            case 'catalog detail':

            case 'order form':

                $product_group = array();

                $product_group['id'] = '';

                

                // get product group differently based on the page type

                switch ($page_type) {

                    case 'catalog':

                    case 'catalog detail':

                        // if there is a forward slash in the page name then get the product group information

                        if (mb_strpos($_GET['page'], '/') !== FALSE) {

                            $item = get_catalog_item_from_url();

                            

                            // if the item is a product group, then set product group

                            if ($item['type'] == 'product group') {

                                $product_group = $item;

                            }

                            

                        // else there is not a forward slash in the page name, so a product group was not passed, so determine product group

                        } else {

                            $query = "SELECT product_group_id FROM catalog_pages WHERE page_id = '" . escape($page_id) . "'";

                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            $row = mysqli_fetch_assoc($result);

                            $product_group['id'] = $row['product_group_id'];

                            

                            // if the current product group still has not been found, then get the top-level product group

                            if ($product_group['id'] == '') {

                                $query = "SELECT id FROM product_groups WHERE parent_id = '0'";

                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                                $row = mysqli_fetch_assoc($result);

                                $product_group['id'] = $row['id'];

                            }

                        }

                        

                        break;

                        

                    case 'order form':

                        // get product group for page

                        $query = "SELECT product_group_id FROM order_form_pages WHERE page_id = '" . escape($page_id) . "'";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $row = mysqli_fetch_assoc($result);

                        $product_group['id'] = $row['product_group_id'];

                        

                        break;

                }

                

                // if a product group was found, then get product group information and verify that product group still exists

                if ($product_group['id'] != '') {

                    $query =

                        "SELECT

                            id,

                            enabled,

                            short_description,

                            full_description,

                            title,

                            meta_description

                        FROM product_groups

                        WHERE id = '" . escape($product_group['id']) . "'";

                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    $product_group = mysqli_fetch_assoc($result);

                }



                // If a product group was not found, then output error.

                if (!$product_group['id']) {

                    $output_channel_title = 'Sorry, no feed is available because the item could not be found.';

                    $output_channel_description = '';

                    set_response_code(404);



                // Otherwise if the product group is disabled, then output error for that.

                } else if (!$product_group['enabled']) {

                    $output_channel_title = 'Sorry, no feed is available because the item is not currently available.';

                    $output_channel_description = '';

                    set_response_code(410);

                

                // Otherwise if a product group was found, then get RSS for product group

                } else if ($product_group['id'] != '') {

                    // get channel title and description differently based on the page type

                    switch ($page_type) {

                        case 'catalog':

                        case 'catalog detail':

                            // if the product group has a title, then use that for the channel title

                            if ($product_group['title'] != '') {

                                $output_channel_title = h(trim($product_group['title']));

                                

                            // else the product group does not have a title, so look for it elsewhere

                            } else {

                                // if the product group has a short description, then use that for the channel title

                                if ($product_group['short_description'] != '') {

                                    $output_channel_title = h(trim($product_group['short_description']));

                                    

                                // else the product group does not have a short description, so look elsewhere

                                } else {

                                    // if the page has a title, then use that for the channel title

                                    if ($page_title != '') {

                                        $output_channel_title = h(trim($page_title));

                                        

                                    // else the page does not have a title, so output notice

                                    } else {

                                        $output_channel_title = '[No title available]';

                                    }

                                }

                            }

                            

                            // if the product group has a meta description, then use that for the channel description

                            if ($product_group['meta_description'] != '') {

                                $output_channel_description = h(trim($product_group['meta_description']));

                                

                            // else the product group does not have a meta description, so look for it elsewhere

                            } else {

                                // if the product group has a full description, then use that for the channel description

                                if ($product_group['full_description'] != '') {

                                    $output_channel_description = h(trim(convert_html_to_text($product_group['full_description'])));

                                    

                                // else the product group does not have a full description, so look elsewhere

                                } else {

                                    // if the page has a meta description, then use that for the channel description

                                    if ($page_meta_description != '') {

                                        $output_channel_description = h(trim($page_meta_description));

                                        

                                    // else the page does not have a meta description, so output notice

                                    } else {

                                        $output_channel_description = '[No description available]';

                                    }

                                }

                            }

                            

                            break;

                            

                        case 'order form':

                            // if there is a page title, then use that for the channel title

                            if ($page_title != '') {

                                $output_channel_title = h(trim($page_title));

                            // else output page name with error

                            } else {

                                $output_channel_title = '[No Web Browser Title property found for ' . h($page_name) . ']';

                            }



                            // if there is a page meta description, then use that for the channel description

                            if ($page_meta_description != '') {

                                $output_channel_description = h(trim($page_meta_description));

                                

                            // else output page name with error

                            } else {

                                $output_channel_description = '[No Web Browser Description property found for ' . h($page_name) . ']';

                            }

                            

                            break;

                    }

                    

                    // get multi-level value differently based on the page type

                    switch ($page_type) {

                        case 'catalog':

                            $multilevel = TRUE;

                            break;

                            

                        case 'catalog detail':

                        case 'order form':

                            $multilevel = FALSE;

                            break;

                    }

                    

                    // get all products in the current product group

                    $products = get_products_in_product_group($product_group['id'], $multilevel);



                    // Get prices for products that have been discounted by offers, so we can include sale price in RSS.

                    $discounted_product_prices = get_discounted_product_prices();

                    

                    // loop through all products in order to prepare RSS

                    foreach ($products as $product) {

                        // get product information

                        $query =

                            "SELECT

                                selection_type,

                                title,

                                short_description,

                                address_name,

                                meta_description,

                                full_description,

                                image_name,

                                price,

                                name,

                                google_product_category,

                                gtin,

                                brand,

                                mpn,

                                inventory,

                                inventory_quantity,

                                backorder

                            FROM products

                            WHERE id = '" . $product['id'] . "'";

                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        $row = mysqli_fetch_assoc($result);

                        

                        $product['selection_type'] = $row['selection_type'];

                        $product['title'] = $row['title'];

                        $product['short_description'] = $row['short_description'];

                        $product['address_name'] = $row['address_name'];

                        $product['meta_description'] = $row['meta_description'];

                        $product['full_description'] = $row['full_description'];

                        $product['image_name'] = $row['image_name'];

                        $product['price'] = $row['price'];

                        $product['name'] = $row['name'];

                        $product['google_product_category'] = $row['google_product_category'];

                        $product['gtin'] = $row['gtin'];

                        $product['brand'] = $row['brand'];

                        $product['mpn'] = $row['mpn'];

                        $product['inventory'] = $row['inventory'];

                        $product['inventory_quantity'] = $row['inventory_quantity'];

                        $product['backorder'] = $row['backorder'];

                        

                        // if the product is not a donation, then continue to prepare RSS for product

                        // Google does not allow products that have variable prices

                        if ($product['selection_type'] != 'donation') {

                            // get parent product group information (we might use this in a couple of places below)

                            $query =

                                "SELECT

                                    display_type,

                                    address_name,

                                    full_description,

                                    meta_description,

                                    image_name

                                FROM product_groups

                                WHERE id = '" . $product['product_group_id'] . "'";

                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            $parent_product_group = mysqli_fetch_assoc($result);

                            

                            $output_title = '';

                            

                            // if the product has a title, then use that for the title

                            if ($product['title'] != '') {

                                $output_title = h(trim($product['title']));

                            

                            // else the product does not have a title, so use short description for the title

                            } else {

                                $output_title = h(trim($product['short_description']));

                            }

                            

                            // prepare link differently differently based on the page type

                            switch ($page_type) {

                                case 'catalog':

                                    // get the catalog detail page name in order to prepare link to product

                                    $query =

                                        "SELECT page.page_name

                                        FROM catalog_pages

                                        LEFT JOIN page ON catalog_pages.catalog_detail_page_id = page.page_id

                                        WHERE catalog_pages.page_id = '" . escape($page_id) . "'";

                                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                                    $row = mysqli_fetch_assoc($result);

                                    $catalog_detail_page_name = $row['page_name'];

                                    

                                    // if the parent product group's display type is browse, then use the product's address name, because we want to link directly to the product

                                    if ($parent_product_group['display_type'] == 'browse') {

                                        $item_address_name = $product['address_name'];

                                    

                                    // else the parent product group's display type is selected, so use the parent product group's address name, because we want to link to the parent product group

                                    } else {

                                        $item_address_name = $parent_product_group['address_name'];

                                    }

                                    

                                    $output_link = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($catalog_detail_page_name)) . '/' . h(encode_url_path($item_address_name));

                                    

                                    break;

                                    

                                case 'catalog detail':

                                    $output_link = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($page_name)) . '/' . h(encode_url_path($address_name));

                                    

                                    break;

                                    

                                case 'order form':

                                    $output_link = URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($page_name));

                                    

                                    break;

                            }

                            

                            $output_description = '';

                            

                            // if the product has a meta description, then use that for the description

                            if ($product['meta_description'] != '') {

                                $output_description = h(trim($product['meta_description']));

                                

                            // else if the product has a full description and the full description is not the same as the short description,

                            // then use a plain text version of the full description

                            } else if (($product['full_description'] != '') && (trim(strip_tags($product['full_description'])) != trim($product['short_description']))) {

                                $output_description = h(trim(convert_html_to_text($product['full_description'])));

                            

                            // else if the parent product group has a meta description, then use that for the description

                            } else if ($parent_product_group['meta_description'] != '') {

                                $output_description = h(trim($parent_product_group['meta_description']));

                                    

                            // else use plain text version of full description

                            } else {

                                $output_description = h(trim(convert_html_to_text($parent_product_group['full_description'])));

                            }

                            

                            $image_name = '';

                            

                            // if the product has an image name, then use that

                            if ($product['image_name'] != '') {

                                $image_name = $product['image_name'];

                                

                            // else the product does not have an image name, so use the parent product group's image name

                            } else {

                                $image_name = $parent_product_group['image_name'];

                            }

                            

                            $output_image_link = '';

                            

                            // if the image name is not blank, then output image link

                            if ($image_name != '') {

                                $output_image_link = '<g:image_link>' . URL_SCHEME . HOSTNAME_SETTING . OUTPUT_PATH . h(encode_url_path($image_name)) . '</g:image_link>';

                            }



                            $output_sale_price = '';



                            // get all of the currency information. For RSS Feed Prices

                            $query ="SELECT currencies.base,currencies.code FROM currencies WHERE currencies.base = '1'";

                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            $currencies = mysqli_fetch_assoc($result);

                            $software_base_code = $currencies ['code'];  



                            // If this product is currently discounted by an offer, then include sale price.

                            if (isset($discounted_product_prices[$product['id']]) == true) {

                                $output_sale_price = '<g:sale_price>' . sprintf("%01.2lf", $discounted_product_prices[$product['id']] / 100) . ' '.$software_base_code.'</g:sale_price>';

                            }



                            $output_google_product_category = '';

                            

                            // If there is a Google product category, then output it.

                            if ($product['google_product_category'] != '') {

                                $output_google_product_category = '<g:google_product_category>' . h(trim($product['google_product_category'])) . '</g:google_product_category>';

                            }

                            

                            $output_gtin = '';

                            

                            // if there is a GTIN, then output it

                            if ($product['gtin'] != '') {

                                $output_gtin = '<g:gtin>' . h(trim($product['gtin'])) . '</g:gtin>';

                            }

                            

                            $output_brand = '';

                            

                            // if there is a brand, then output it

                            if ($product['brand'] != '') {

                                $output_brand = '<g:brand>' . h(trim($product['brand'])) . '</g:brand>';

                            }

                            

                            $output_mpn = '';

                            

                            // if there is a mpn, then output it

                            if ($product['mpn'] != '') {

                                $output_mpn = '<g:mpn>' . h(trim($product['mpn'])) . '</g:mpn>';

                            }



                            $output_availability = '';



                            // If inventory is tracked for this product, then determine what the availability should be set to.

                            if ($product['inventory'] == 1) {

                                // If the inventory quantity is greater than zero then the product is in stock.

                                if ($product['inventory_quantity'] > 0) {

                                    $output_availability = 'in stock';



                                // Otherwise the inventory quantity is zero, so determine if product can be backordered or not.

                                } else {

                                    // If the product can be backordered, then prepare availability for that.

                                    if ($product['backorder'] == 1) {

                                        $output_availability = 'available for order';

                                    // Otherwise the product cannot be backordered, so prepare availability for that.

                                    } else {

                                        $output_availability = 'out of stock';

                                    }

                                }



                            // Otherwise the inventory is not tracked for this product, so just assume it is available.

                            } else {

                                $output_availability = 'in stock';

                            }

                            

                // prepare rss content for product

                $output_rss_items .=

                    '<item>

            <title>' . $output_title . '</title>

            <link>' . $output_link . '</link>

            <description>' . $output_description . '</description>

            ' . $output_image_link . '

            <g:price>' . sprintf("%01.2lf", $product['price'] / 100) . ' '.$software_base_code.'</g:price>

            ' . $output_sale_price . '

            <g:condition>new</g:condition>

            <g:id>' . h(trim($product['name'])) . '</g:id>

            ' . $output_google_product_category . '

            ' . $output_gtin . '

            ' . $output_brand . '

            ' . $output_mpn . '

            <g:product_type>' . h($product['path']) . '</g:product_type>

            <g:availability>' . $output_availability . '</g:availability>

        </item>

        ';

                        }

                    }

                }

                

                break;

        }

        

    // else either this page does not have a valid page type or it is not public, so output an error in the channel

    } else {

        $output_channel_title = 'No feed is available for ' . h($page_name);

        $output_channel_description = 'The page (' . $page_name . ') must be in a Public Folder, and it must be either a Form List View, Calendar View, Catalog, Catalog Detail, or Order Form before a feed can be created.';

        set_response_code(404);

    }

    

    // set content type header for XML

    header('Content-type: application/rss+xml; charset=UTF-8');

    

    // output RSS XML

    $content = 

        '<?xml version="1.0" encoding="utf-8" ?>

<rss version="2.0"' . $output_google_declaration . '>

    <channel>

        <title>' . $output_channel_title . '</title>

        <link>' . $output_channel_link . '</link>

        <description>' . $output_channel_description . '</description>

        ' . $output_rss_items . '

    </channel>

</rss>

';



// else if iCalendar is being requested then output ICS format

} elseif ($_GET['icalendar'] == 'true') {

    // get calendar event data

    $calendar_event = get_calendar_event($_GET['id'], 0);

    

    // if calendar event could not be found, then output error

    if ($calendar_event == FALSE) {

        output_error('The requested calendar event could not be found.', 404);

    }

    

    // if calendar event is not published, output error

    if ($calendar_event['published'] == 0) {

        output_error('The requested calendar event is not currently published.', 403);

    }

    

    // get all calendars that belong to this event

    $query =

        "SELECT

            calendar_id

        FROM calendar_events_calendars_xref

        WHERE calendar_event_id = '" . escape($_GET['id']) . "'";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    

    $event_calendars = array();

    

    // Step through each row and add it to the array

    while ($row = mysqli_fetch_assoc($result)) {

        $event_calendars[] = $row;

    }

    

    $where_calendar_id = '';

    

    // Loop through each calendar so that we can check if we are allowed to output the event on this calendar view

    foreach ($event_calendars as $event_calendar) {

        // check if calendar event is allowed in this view

        $where_calendar_id .= "(calendar_id = '" . escape($event_calendar['calendar_id']) . "') OR ";

    }

    

    // Remove the last OR from the where_calendar_id variable and enclose it

    if (mb_strlen($where_calendar_id) > 0) {

        $where_calendar_id = mb_substr($where_calendar_id, 0, -4);

        $where_calendar_id = '(' . $where_calendar_id . ')';

    }

    

    // Query the rules for this calendar event view page.

    $query =

        "SELECT calendar_id

        FROM calendar_event_views_calendars_xref

        WHERE

            " . $where_calendar_id . "

            AND (page_id = '" . escape($page_id) . "')";

    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    

    // if calendar event is not allowed in this view, then output error

    if (mysqli_num_rows($result) == 0) {

        output_error('The requested calendar event is not allowed in this calendar event view.', 403);

    }

    

    // format dates so that they always have an identical format

    $start_date_and_time = date('l, F j, Y g:i A', strtotime($calendar_event['start_date_and_time']));

    $end_date_and_time = date('l, F j, Y g:i A', strtotime($calendar_event['end_date_and_time']));

    

    $output_recurrence_rule = '';

    

    // if this event has recurrence enabled then output the recurrence rule

    if ($calendar_event['recurrence'] == 1) {

        $frequency = '';

        $by_day = '';

        

        // switch between the recurrence types and output the correct frequency

        switch ($calendar_event['recurrence_type']) {

            // Daily

            // There is currently a bug in this code where the ical event will be marked

            // as daily for every week day, even though some week days might be disabled.

            // Did not have time to deal with this when adding day of the week feature for daily repeat.

            case 'day':

                $frequency = 'DAILY';

                break;

                

            // Weekly

            case 'week':

                $frequency = 'WEEKLY';

                break;

            

            // Monthly

            case 'month':

                $frequency = 'MONTHLY';



                // If this event recurs by the day of the week (e.g. second Sunday), then prepare by day value.

                if ($calendar_event['recurrence_month_type'] == 'day_of_the_week') {

                    $event_start_date = $calendar_event['start_date'];

                    $event_start_date_parts = explode('-', $event_start_date);

                    $event_start_year = $event_start_date_parts[0];

                    $event_start_month = $event_start_date_parts[1];



                    $day_of_the_week = date('l', strtotime($event_start_date));

                    $first_day_of_the_month_timestamp = strtotime($event_start_year . '-' . $event_start_month . '-01');



                    $week = '';



                    // Create a loop in order to determine which week event falls on.

                    // We only loop through 4 weeks, because we are going to set "last" below for 5th week.

                    for ($week_index = 0; $week_index <= 3; $week_index++) {

                        // If the event is in this week, then remember the week number and break out of this loop.

                        if ($event_start_date == date('Y-m-d', strtotime('+' . $week_index . ' week ' . $day_of_the_week, $first_day_of_the_month_timestamp))) {

                            $week = $week_index + 1;

                            break;

                        }

                    }



                    // If a week was not found, then that means it falls on the 5th week,

                    // so set it to be the last week.

                    if ($week == '') {

                        $week = '-1';

                    }



                    $uppercase_two_character_day_of_the_week = mb_strtoupper(mb_substr($day_of_the_week, 0, 2));



                    $by_day = ';BYDAY=' . $week . $uppercase_two_character_day_of_the_week;

                }



                break;

            

            // Yearly

            case 'year':

                $frequency = 'YEARLY';

                break;

        }

        

        $count = $calendar_event['total_recurrence_number'] + 1;

        

        $output_recurrence_rule = 'RRULE:FREQ=' . $frequency . ';COUNT=' . $count . $by_day . "\n";

    }

    

    // set content type and disposition headers for ICS

    header('Content-type: text/calendar');

    header('Content-Disposition: attachment; filename="' . $calendar_event['id'] . $calendar_event['created_timestamp'] . '.ics"');

    

    // output calendar event data in ICS format

    $content = 

        'BEGIN:VCALENDAR' . "\n" .

        'VERSION:2.0' . "\n" .

        'PRODID:-//' . HOSTNAME . '//NONSGML ' . $page_name . '//EN' . "\n" .

        'METHOD:PUBLISH' . "\n" .

        'BEGIN:VEVENT' . "\n" .

        'UID:' . $calendar_event['id'] . $calendar_event['created_timestamp'] . "\n" .

        'DTSTAMP:' . date("Ymd\THis", strtotime($start_date_and_time)) . "\n" .

        'DTSTART:' . date("Ymd\THis", strtotime($start_date_and_time)) . "\n" .

        'DTEND:' . date("Ymd\THis", strtotime($end_date_and_time)) . "\n" .

        'LOCATION:' . trim($calendar_event['location']) . "\n" .

        'SUMMARY:' . trim($calendar_event['name']) . "\n" .

        'DESCRIPTION:' . trim($calendar_event['short_description']) . "\n" .

        $output_recurrence_rule .

        'END:VEVENT' . "\n" .

        'END:VCALENDAR';



// else output the page normally

} else {

    // if this page is appearing for an e-mail preview, then prepare values to pass to get_page_content()

    if ($_GET['email'] == 'true') {

        $email_mode = TRUE;



        // set the device type to desktop, because we don't send mobile versions in e-mail campaigns

        $device_type = 'desktop';



    // else this page is not appearing for an e-mail preview, so prepare values in a different way

    } else {

        $email_mode = FALSE;



        // set the device type to the visitor's device type

        $device_type = $_SESSION['software']['device_type'];

    }

    

    // assume that the toolbar should not be outputted until we find out otherwise

    $toolbar = FALSE;

    

    // if the user is logged in and edit is not set to 'no' and the user has control panel access, then remember that the toolbar should be outputted

    if (

        (isset($user) == true)

        && ($_GET['edit'] != 'no')

        &&

        (

            ($user['role'] < 3)

            || (no_acl_check($user['id']) == true)

            || ($user['manage_calendars'] == true)

            || ($user['manage_forms'] == true)

            || ($user['manage_visitors'] == true)

            || ($user['manage_contacts'] == true)

            || ($user['manage_emails'] == true)

            || ($user['manage_ecommerce'] == true)

            || $user['manage_ecommerce_reports']

            || (count(get_items_user_can_edit('ad_regions', $user['id'])) > 0)

        )

    ) {

        $toolbar = TRUE;

    }



    // If the view page mode is edit,

    // and edit is not set to no in the query string

    // (e.g. body preview in create e-mail campaign, preview style, preview theme)

    // and the user has edit access to this page,

    // then leave view page mode set to edit.

    if (

        ($_SESSION['software']['view_page_mode'] == 'edit')

        && ($_GET['edit'] != 'no')

        && ($edit_access == true)

    ) {

        $view_page_mode = 'edit';



    // Otherwise use preview mode.

    } else {

        $view_page_mode = 'preview';

    }



    require_once(dirname(__FILE__) . '/get_page_content.php');

    

    $content = get_page_content($page_id, '', '', $view_page_mode, $email_mode, array(), $toolbar, $device_type);

    

    // if the toolbar should be outputted, then output it

    if ($toolbar == TRUE) {

        $output_mobile_border_constraint = '';



        // if the device type is mobile then output mobile border constraint

        if ($device_type == 'mobile') {

            $output_mobile_border_constraint =

                '#mobile_border

                {

                    width: 340px !important;

                    margin-left: auto !important;

                    margin-right: auto !important;

                    float: none !important;

                }';

        }

        // Set styling for grid mode and full screen buttons.

        $output_css = '

             <style>

             

             #hide_buttons_hoverme,

             input.toggle_button_on,

             input.toggle_button_off,

             input.up_button,input.down_button{

                    padding: 0;

                    margin: 0;

                    display: inline-block;

                    position: fixed; 

                    white-space: nowrap;

                    border: none !important;

                    text-decoration: none;

                    cursor: pointer;

                    outline:none;

                    transition: all .3s ease;

                    background-color: #1976d2;
                    

                }

                #hide_buttons_hoverme:hover,

                input.toggle_button_on:hover,

                input.toggle_button_off:hover,

                input.down_button:hover,

                #software_inline_editing_save_button:hover,

                #software_inline_editing_cancel_button:hover{

                    border-radius:50%;

                    -webkit-animation: ripples 1.5s infinite ease-in-out;

                    animation: ripples 1.5s infinite ease-in-out;

                   

                }





                @-webkit-keyframes ripples {

                    0% {

                        -webkit-box-shadow: 0 0 0 0px rgba(255, 255, 255, 0.7);

                        box-shadow:0 0 0 0px rgba(255, 255, 255, 0.2);

                    }

                    33% {

                        -webkit-box-shadow: 0 0 0 0px rgba(255, 255, 255, 0.7);

                        box-shadow:0 0 0 5px rgba(111, 111, 111, 0.44);

                    }

                    66% {

                        -webkit-box-shadow: 0 0 0 7px rgba(216, 216, 216, 0);

                        box-shadow: 0 0 0 10px rgba(216, 216, 216, 0);

                     

                    }

                  }

                input.toggle_button_on,

                input.toggle_button_off{

                    right: 58px;

                    height: 30px;

                    width: 30px;

                    top: 23px;

                    background: #1976d2 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/grid_toggle_on.png) no-repeat center;

                    z-index: 9999999;

                    background-size: 50%;

                    border-radius: 50%;

                    border-top-right-radius:0;

                }



                input.toggle_button_on{

                    background-color: #be19d2;

                    background: #be19d2 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/grid_toggle_off.png) no-repeat center;

                    background-size: 50%;

                    border-radius: 50%;

                }



                #software_inline_editing_save_button{

                    background-color: #67bf5f !important;

                    border-radius: 50%;

                    border:0 !important;

                }

                #software_inline_editing_cancel_button{

                    background-color: #c15e5e !important;

                    border-radius: 50%;

                    border:0 !important;

                }



                input.down_button,

                input.up_button{

                    top: 19px;

                    right: 0px;

                    width: 53px;

                    height: 53px;

                    background: #1976d2 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/toolbar_down_arrow.png) no-repeat center;

                    z-index: 99999998;

                    border-radius: 50%;

                    border-top-left-radius:0;

                    background-size: 35%;

                }



                input.up_button{

                    top: 50px;

                    background: #000 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/toolbar_down_arrow.png) no-repeat center;

                    z-index: 999999999;

                    transform:rotate(180deg);

                    border-radius: 0;

                    background-size: 35%;

                    filter: invert(1);

                    -webkit-box-shadow: 2px -2px 2px #e3e3e396;

                    box-shadow: 2px -2px 2px #e3e3e396;

                    border-top-right-radius: 3px !important;
                }
                @media only screen and ( max-width:930px){
                    input.up_button{
                        top: 185px;
                        border-radius:3px;
                        right: 3px;
                    }
                }


                #hide_buttons_hoverme {

                    right: 44px;

                    width: 25px;

                    height: 25px;

                    top: 3px;

                    background: #1976d2 url(' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/toolbar_up_arrow.png) no-repeat center;

                    z-index: 99999999;

                    background-size: 45%;

                    

                    border-radius: 50%;

                    

                  

                }

                #button_container.collapse_buttons input.down_button,

                #button_container.collapse_buttons input.toggle_button_on,

                #button_container.collapse_buttons input.toggle_button_off{

                    right: 44px;

                    width: 25px;

                    height: 25px;

                    border-radius: 50%;

                    top: 3px;

                    opacity:0;

                }



                #button_container.collapse_buttons #hide_buttons_hoverme{

                    z-index: 9999999999;

                    transform:rotate(180deg);

                    border-radius:50%;

                }







                

                ' . $output_mobile_border_constraint . '

            </style>

        ';

        // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,

        // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).

        

        // if </head> is in the HTML, place CSS before </head>

        if (stristr($content, '</head>')) {

            $content = preg_replace('/<\/head>/i', $output_css . '</head>', $content);

            

        // else if </body> is in the HTML, place CSS before </body>

        } elseif (stristr($content, '</body>')) {

            $content = preg_replace('/<\/body>/i', $output_css . '</body>', $content);

            

        // else if </html> is in the HTML, place CSS before </html>

        } elseif (stristr($content, '</html>')) {

            $content = preg_replace('/<\/html>/i', $output_css . '</html>', $content);

            

        // else HTML does not contain </head>, </body>, or </html>, so just place CSS at the end of the content

        } else {

            $content .= $output_css;

        }

        

        // If theme preview is enabled and the toolbar was expanded on the last page visit,

        // then prepare it to be expanded by default.

        if (

            ($_SESSION['software']['preview_theme_id'])

            && ($_SESSION['software']['toolbar_enabled'] == true)

        ) {

            $fullscreen_toggle = "up_button";

            $fullscreen_toggle_title = "Activate Fullscreen Mode (Ctrl+D | &#8984;+D)";



        // Otherwise theme preview is disabled or toolbar was collapsed on last page visit,

        // so prepare it to be collapsed by default.

        } else {

            $fullscreen_toggle = "down_button";

            $fullscreen_toggle_title = "Deactivate Fullscreen Mode (Ctrl+D | &#8984;+D)";

        }



        // If user has edit access to this page, then output grid button.

        if ($edit_access == true) {

            // if view page mode is set to preview, then set change mode value to edit

            if ($view_page_mode == 'preview') {

                $output_update_view_page_mode = 'edit';

                $output_button_class = 'toggle_button_off';

                $output_toogle_title = 'Activate Edit Mode (Ctrl+E | &#8984;+E)';

               

            // else view page mode is set to edit, so set change mode value to preview

            } else {

                $output_update_view_page_mode = 'preview';

                $output_button_class = 'toggle_button_on';

                $output_toogle_title = 'Deactivate Edit Mode (Ctrl+E | &#8984;+E)';

            }

            

            // output the grid button

            $output_grid_button = '<input class="' . $output_button_class . '" type="button" id="grid_toggle" title="' . $output_toogle_title . '" onclick="javascript:window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_view_page_mode.php?mode=' . $output_update_view_page_mode . '&amp;send_to=' . h(escape_javascript(urlencode(REQUEST_URL))) . '\'"  value="" />';

        }

        // prepare to add toolbar and buttons below toolbar

        $output_toolbar =

            '<iframe id="software_toolbar" scrolling="no" src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/toolbar.php?page_id=' . $page_id .'&amp;style_id=' . STYLE_ID . '&amp;send_to=' . h(urlencode(REQUEST_URL)) . '" style="position: fixed; top: 0; right: 0; left: 0; z-index: 999999999; padding: 0; margin: 0; width: 100%; display: none" frameborder="0"></iframe>

            <div id="button_container">

                

                ' . $output_grid_button . '

                <div id="hide_buttons_hoverme" title="Hide Toggle Buttons"></div>

                <input class="'. $fullscreen_toggle .'" type="button" id="software_fullscreen_toggle" title="' . $fullscreen_toggle_title . '"  value="" />

           

		   <script>

			var hidebtn = document.querySelector("#hide_buttons_hoverme");

			var toogle_edit = document.querySelector("#grid_toggle"); 

			var toogle_arrow = document.querySelector("#software_fullscreen_toggle"); 

            var button_container = document.querySelector("#button_container"); 

			var hide = "Hide Toggle Buttons";

			var show = "Show Toggle Buttons";

            var first_click = true;

           

			hidebtn.onclick = function() {

			    if (first_click) {

					button_container.classList.add("collapse_buttons")

					hidebtn.title = show;

			      

			       first_click = false;

			    } else {

                    button_container.classList.remove("collapse_buttons")

					hidebtn.title = hide;

					first_click = true;

			    }

			}

			</script>

			

			</div>';



            

        // We are using stristr instead of mb_stristr because mb_stristr requires PHP 5.2,

        // and we still have some sites on PHP 5.1 (probably won't cause any utf-8 issue).

        

        // if there is a body tag in the HTML, then add toolbar after body tag

        if (stristr($content, '<body') == TRUE) {

            $content = preg_replace('/(<body.*?>)/i', '$1' . $output_toolbar, $content);

            

        // else if there is an html tag in the HTML, then add toolbar after html tag

        } else if (stristr($content, '<html') == TRUE) {

            $content = preg_replace('/(<html.*?>)/i', '$1' . $output_toolbar, $content);

            

        // else HTML does not contain body tag or html tag, so add toolbar to the beginning of the content

        } else {

            $content = $output_toolbar . $content;

        }

    

    // else, if the user is at least a designer, and if they are editing a theme, then prepare the document

    } elseif ((isset($user) == true) && ($user['role'] < 2) && ($_GET['edit_theme'] == 'true')) {

        // disable all links in the content

        $content = disable_links_in_content($content);

        

        $code = $_SESSION['software']['theme_designer'][$_GET['theme_id']]['code'];



        // if the device type is mobile then output mobile border constraint

        if ($device_type == 'mobile') {

            $code .=

                '#mobile_border

                {

                    width: 340px !important;

                    margin-left: auto !important;

                    margin-right: auto !important;

                    float: none !important;

                }';

        }

        

        // Replace path placeholders in CSS.

        $code = preg_replace('/{path}/i', PATH, $code);



        // Replace software directory placeholders.

        $code = preg_replace('/{software_directory}/i', SOFTWARE_DIRECTORY, $code);

        

        // put the code that is in the session into the document's head tag

        $content = preg_replace('/(<\/head>)/i', '<style type="text/css">' . h($code) . '</style>' . '$1', $content);

    }

}



echo $content;



// if visitor tracking is on

if (VISITOR_TRACKING == true) {

    update_visitor_page_data($page_name);

}



// If this is a private page, then log access so admins can keep track of users accessing private

// content.  We wait until now to log, for performance reasons, so that the page content will

// be shown as soon as possible and then the logging can be completed after that.



if ($access_control_type == 'private') {

    log_activity(

        'User viewed private page.' . "\n" .

        "\n" .

        URL_SCHEME . HOSTNAME . REQUEST_URL);

}



function init_tracking() {

    

    // if there is a tracking code in the query string, then store tracking code in session and cookie (overwrite existing cookie, if there is one)

    if ($_GET['t']) {

        $tracking_code = trim($_GET['t']);

        $_SESSION['software']['tracking_code'] = $tracking_code;



        // If we have not already retrieved the tracking code duration from the db, then get it.

        // Tracking code duration is the number of days that tracking code should be stored.

        if (!defined('TRACKING_CODE_DURATION')) {

            define('TRACKING_CODE_DURATION', db("SELECT tracking_code_duration FROM config"));

        }



        // Set tracking code cookie.

        setcookie('software[tracking_code]', $tracking_code, time() + (86400 * TRACKING_CODE_DURATION), '/');

        

    // else there is not a tracking code in the query string, so get tracking code

    } else {

        $tracking_code = get_tracking_code();

    }

    

    // if there is an affiliate code in the query string, then store affiliate code in session and cookie (overwrite existing cookie, if there is one)

    if ($_GET['a']) {

        $affiliate_code = trim($_GET['a']);

        $_SESSION['software']['affiliate_code'] = $affiliate_code;

        // set affiliate code cookie for 10 years

        setcookie('software[affiliate_code]', $affiliate_code, time() + 315360000, '/');

        

    // else there is not an affiliate code in the query string, so get affiliate code

    } else {

        $affiliate_code = get_affiliate_code();

    }

    

    // if there is an http referer and the http referer has not already been tracked

    if ($_SERVER['HTTP_REFERER'] && !$_SESSION['software']['http_referer']) {

        // parse the http referer url in order to get host

        $http_referer_parsed_url = parse_url($_SERVER['HTTP_REFERER']);

        

        // remove www. from http referer host

        $http_referer_host = str_replace('www.', '', $http_referer_parsed_url['host']);

        

        // remove www. from http host

        $http_host = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        

        // if the http referer is not from the same host as the http host, then store http referer in session

        if ($http_referer_host != $http_host) {

            $_SESSION['software']['http_referer'] = $_SERVER['HTTP_REFERER']; 

        }

    }

    

    // if visitor id is not already set in the session, then this is a new visitor

    if (!$_SESSION['software']['visitor_id']) {

        // if the visitor is a robot from host-tracker.com, then ignore visitor

        if (mb_strpos($_SERVER['HTTP_USER_AGENT'], 'host-tracker.com') !== false) {

            return;

        }

        

        // check to see if visitor should be connected to an existing visitor record

        // (e.g. if the visitor is a robot that just recently visited)

        // if all of the data is the same (e.g. ip address) and the visitor visited in the last 30 minutes,

        // then we will connect the visitor to an existing record

        

        $thirty_minutes_ago = time() - 1800;

        

        $query = "SELECT id

                 FROM visitors

                 WHERE

                    (ip_address = IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0))

                    AND (stop_timestamp > '$thirty_minutes_ago')

                    AND (http_referer = '" . escape($_SESSION['software']['http_referer']) . "')

                    AND (tracking_code = '" . escape($tracking_code) . "')

                    AND (affiliate_code = '" . escape($affiliate_code) . "')";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        

        // if a matching visitor was found, connect visitor to existing record

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            $_SESSION['software']['visitor_id'] = $row['id'];

        

        // else a matching visitor was not found, so create new visitor record

        } else {

            // if number of visits cookie is set

            if ($_COOKIE['software']['number_of_visits']) {

                $first_visit = 0;

                setcookie('software[number_of_visits]', $_COOKIE['software']['number_of_visits'] + 1, time() + 315360000, '/');

                

            // else number of visits cookie is not set

            } else {

                $first_visit = 1;

                setcookie('software[number_of_visits]', '1', time() + 315360000, '/');

            }

            

            $referer = $_SESSION['software']['http_referer'];

            

            // if there is an http referer

            if ($referer) {

                $referring_host_name = $http_referer_parsed_url['host'];

                

                // check to see if http referer is a search engine and find search query delimiter

                if (preg_match('/www\.google.*/i', $referer)) {

                    $referring_search_engine = 'Google';

                    $delimiter = 'q';

                } elseif (preg_match('/search\.msn.*/i', $referer)) {

                    $referring_search_engine = 'MSN';

                    $delimiter = 'q';

                } elseif (preg_match('/search\.yahoo.*/i', $referer)) {

                    $referring_search_engine = 'Yahoo!';

                    $delimiter = 'p';

                } elseif (preg_match('/www\.bing\.com/i', $referer)) {

                    $referring_search_engine = 'Bing';

                    $delimiter = 'q';

                } elseif (preg_match('/(search\.aol\.com)|(aolsearch\.aol\.com)/i', $referer)) {

                    $referring_search_engine = 'AOL';

                    $delimiter = 'query';

                } elseif (preg_match('/ask\.com/i', $referer)) {

                    $referring_search_engine = 'Ask.com';

                    $delimiter = 'q';

                } elseif (preg_match('/www\.ask\.co\.uk/i', $referer)) {

                    $referring_search_engine = 'Ask.com';

                    $delimiter = 'ask';

                } elseif (preg_match('/search\.earthlink\.net/i', $referer)) {

                    $referring_search_engine = 'EarthLink';

                    $delimiter = 'q';

                } elseif (preg_match('/www\.netscape\.com/i', $referer)) {

                    $referring_search_engine = 'Netscape';

                    $delimiter = 's';

                } elseif (preg_match('/(search\.netscape\.com)|(searcht\.netscape\.com)/i', $referer)) {

                    $referring_search_engine = 'Netscape';

                    $delimiter = 'query';

                } elseif (preg_match('/www\.alltheweb\.com/i', $referer)) {

                    $referring_search_engine = 'AlltheWeb';

                    $delimiter = 'q';

                } elseif (preg_match('/msxml\.excite\.com/i', $referer)) {

                    $referring_search_engine = 'Excite';

                    $delimiter = 'qkw';

                } elseif (preg_match('/www\.metacrawler\.com/i', $referer)) {

                    $referring_search_engine = 'MetaCrawler';

                    $delimiter = 'qkw';

                } elseif (preg_match('/dpxml\.webcrawler\.com/i', $referer)) {

                    $referring_search_engine = 'WebCrawler';

                    $delimiter = 'qkw';

                } elseif (preg_match('/search\.lycos\.com/i', $referer)) {

                    $referring_search_engine = 'Lycos';

                    $delimiter = 'query';

                } elseif (preg_match('/www\.hotbot\.com/i', $referer)) {

                    $referring_search_engine = 'HotBot';

                    $delimiter = 'query';

                } elseif (preg_match('/search\.mamma\.com/i', $referer)) {

                    $referring_search_engine = 'Mamma.com';

                    $delimiter = 'query';

                } elseif (preg_match('/search\.cometsystems\.com/i', $referer)) {

                    $referring_search_engine = 'Comet Web Search';

                    $delimiter = 'qry';

                } elseif (preg_match('/www\.overture\.com/i', $referer)) {

                    $referring_search_engine = 'Overture';

                    $delimiter = 'Keywords';

                } elseif (preg_match('/www\.looksmart\.com/i', $referer)) {

                    $referring_search_engine = 'LookSmart';

                    $delimiter = 'key';

                } elseif (preg_match('/search\.looksmart\.com/i', $referer)) {

                    $referring_search_engine = 'LookSmart';

                    $delimiter = 'qt';

                } elseif (preg_match('/search\.viewpoint\.com/i', $referer)) {

                    $referring_search_engine = 'Viewpoint';

                    $delimiter = 'k';

                } elseif (preg_match('/search\.dmoz\.org/i', $referer)) {

                    $referring_search_engine = 'Open Directory Project';

                    $delimiter = 'search';

                }

                

                // if a delimiter was found, get search terms

                if ($delimiter) {

                    // get query string items

                    parse_str($http_referer_parsed_url['query'], $query_string_items);

                    

                    // get referring search terms from query string

                    $referring_search_terms = trim($query_string_items[$delimiter]);

                    

                    // if magic quotes is on, then remove slashes from referring search terms

                    if (get_magic_quotes_gpc()) {

                        $referring_search_terms = stripslashes($referring_search_terms);

                    }

                    

                    $referring_search_terms = urldecode($referring_search_terms);

                }

            }

            

            $query = "INSERT INTO visitors (

                        ip_address,

                        http_referer,

                        referring_host_name,

                        referring_search_engine,

                        referring_search_terms,

                        first_visit,

                        tracking_code,

                        currency_code,

                        affiliate_code,

                        start_timestamp,

                        stop_timestamp)

                     VALUES (

                        IFNULL(INET_ATON('" . escape($_SERVER['REMOTE_ADDR']) . "'), 0),

                        '" . escape($_SESSION['software']['http_referer']) . "',

                        '" . escape($referring_host_name) . "',

                        '" . escape($referring_search_engine) . "',

                        '" . escape($referring_search_terms) . "',

                        '$first_visit',

                        '" . escape($tracking_code) . "',

                        '" . escape(VISITOR_CURRENCY_CODE) . "',

                        '" . escape($affiliate_code) . "',

                        UNIX_TIMESTAMP(),

                        UNIX_TIMESTAMP())";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            

            $_SESSION['software']['visitor_id'] = mysqli_insert_id(db::$con);

        }

    }



    // Assume that we don't need to update visitor or order utm tags, until we find out otherwise.

    $update_utm = false;



    // If utm tags appear in the URL, then store them in session, cookie and visitor record.

    if ($_GET['utm_source']) {



        $_SESSION['software']['utm_source'] = trim($_GET['utm_source']);

        $_SESSION['software']['utm_medium'] = trim($_GET['utm_medium']);

        $_SESSION['software']['utm_campaign'] = trim($_GET['utm_campaign']);

        $_SESSION['software']['utm_term'] = trim($_GET['utm_term']);

        $_SESSION['software']['utm_content'] = trim($_GET['utm_content']);



        $cookie_id = 0;



        if ($_SESSION['software']['cookie_id']) {

            $cookie_id = db("SELECT id FROM cookies WHERE id = '" . e($_SESSION['software']['cookie_id']) . "'");

        } else if ($_COOKIE['lsid']) {

            $cookie_id = db("SELECT id FROM cookies WHERE lsid = '" . e($_COOKIE['lsid']) . "'");

        }



        // If a cookie was found in the db, then update it.

        if ($cookie_id) {



            db(

                "UPDATE cookies SET

                    utm_source = '" . e($_SESSION['software']['utm_source']) . "',

                    utm_medium = '" . e($_SESSION['software']['utm_medium']) . "',

                    utm_campaign = '" . e($_SESSION['software']['utm_campaign']) . "',

                    utm_term = '" . e($_SESSION['software']['utm_term']) . "',

                    utm_content = '" . e($_SESSION['software']['utm_content']) . "',

                    modified_timestamp = UNIX_TIMESTAMP()

                WHERE id = '" . e($cookie_id) . "'");



        // Otherwise a cookie was not found, so create one.  We store the UTM data in the db instead

        // of the actual cookie for performance reasons, so that the visitor's browser does not

        // have to send all of that UTM data in a cookie for every request.  We just store a UUID

        // value in the actual cookie, which connects to a db record.

        } else {



            $lsid = db("SELECT UUID()");



            db(

                "INSERT INTO cookies (

                    lsid,

                    utm_source,

                    utm_medium,

                    utm_campaign,

                    utm_term,

                    utm_content,

                    created_timestamp,

                    modified_timestamp)

                VALUES (

                    '" . e($lsid) . "',

                    '" . e($_SESSION['software']['utm_source']) . "',

                    '" . e($_SESSION['software']['utm_medium']) . "',

                    '" . e($_SESSION['software']['utm_campaign']) . "',

                    '" . e($_SESSION['software']['utm_term']) . "',

                    '" . e($_SESSION['software']['utm_content']) . "',

                    UNIX_TIMESTAMP(),

                    UNIX_TIMESTAMP())");



            $cookie_id = mysqli_insert_id(db::$con);



            // Set cookie in visitor's browser for 10 years.  Even though we only care about the

            // UTM data for 30 days, we store the cookie for 10 years, because we might eventually

            // use this same cookie to store data for other features.

            setcookie('lsid', $lsid, time() + 315360000, '/');



            // For 1 in 100 chance, delete cookies that have not been modified in a year.

            // The 1 in 100 chance is just added for performance reasons.

            if (rand(1, 100) == 1) {

                db("DELETE FROM cookies WHERE modified_timestamp < " . (time() - 31536000));

            }

        }



        $_SESSION['software']['cookie_id'] = $cookie_id;



        $update_utm = true;



    // Otherwise, there are no utm tags in the URL, so if we have not looked for utm tags in a

    // cookie yet for this session and there is a cookie, then do that, so they will be available

    // in the session.  We do this once per session.

    } else if (!isset($_SESSION['software']['utm_source']) and $_COOKIE['lsid']) {



        // If we have not already retrieved the tracking code duration from the db, then get it.

        // Tracking code duration is the number of days that tracking code should be stored.

        if (!defined('TRACKING_CODE_DURATION')) {

            define('TRACKING_CODE_DURATION', db("SELECT tracking_code_duration FROM config"));

        }



        // Try to find utm tag info in cookie that is less than the tracking code duration, from

        // the site settings.

        $cookie = db_item(

            "SELECT

                id,

                utm_source,

                utm_medium,

                utm_campaign,

                utm_term,

                utm_content

            FROM cookies

            WHERE

                (lsid = '" . e($_COOKIE['lsid']) . "')

                AND (modified_timestamp > '" . (time() - (86400 * TRACKING_CODE_DURATION)) . "')");



        // If a cookie was found, then update session.

        if ($cookie) {



            $_SESSION['software']['cookie_id'] = $cookie['id'];

            $_SESSION['software']['utm_source'] = $cookie['utm_source'];

            $_SESSION['software']['utm_medium'] = $cookie['utm_medium'];

            $_SESSION['software']['utm_campaign'] = $cookie['utm_campaign'];

            $_SESSION['software']['utm_term'] = $cookie['utm_term'];

            $_SESSION['software']['utm_content'] = $cookie['utm_content'];



            $update_utm = true;



        // Otherwise a cookie could not be found, so remember that we have already done this check

        // by setting utm_source to blank.

        } else {

            $_SESSION['software']['utm_source'] = '';

        }

    }



    // If the utm tags for the visitor or order should be updated, then do that.

    if ($update_utm) {

        

        if ($_SESSION['software']['visitor_id']) {

            db(

                "UPDATE visitors

                SET

                    utm_source = '" . e($_SESSION['software']['utm_source']) . "',

                    utm_medium = '" . e($_SESSION['software']['utm_medium']) . "',

                    utm_campaign = '" . e($_SESSION['software']['utm_campaign']) . "',

                    utm_term = '" . e($_SESSION['software']['utm_term']) . "',

                    utm_content = '" . e($_SESSION['software']['utm_content']) . "',

                    stop_timestamp = UNIX_TIMESTAMP()

                WHERE id = '" . e($_SESSION['software']['visitor_id']) . "'");

        }



        if ($_SESSION['ecommerce']['order_id']) {

            db(

                "UPDATE orders

                SET

                    utm_source = '" . e($_SESSION['software']['utm_source']) . "',

                    utm_medium = '" . e($_SESSION['software']['utm_medium']) . "',

                    utm_campaign = '" . e($_SESSION['software']['utm_campaign']) . "',

                    utm_term = '" . e($_SESSION['software']['utm_term']) . "',

                    utm_content = '" . e($_SESSION['software']['utm_content']) . "'

                WHERE id = '" . e($_SESSION['ecommerce']['order_id']) . "'");

        }



    }

}