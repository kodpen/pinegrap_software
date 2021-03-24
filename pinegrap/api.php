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
//required for software backup mysql dumb
use Ifsnop\Mysqldump as IMysqldump;
$request = json_decode(@file_get_contents('php://input'), true);

// If login info was included in the request, then store it, so that initialize_user() can login user.
if ((isset($request['username']))&&($request['username'] != '')) {
    define('API_USERNAME', $request['username']);
    define('API_PASSWORD', md5($request['password']));
}
  
include('init.php');

// Add header in order to start response.
header('Content-Type: application/json');

$action = $request['action'];
$token = $request['token'];

// We only do access control checks for certain sensitive actions.
// Some actions have their own access control checks further below.
if (
    ($action != 'add_to_cart')
    and ($action != 'get_product')
    and ($action != 'get_installment_options')
    and ($action != 'get_shipping_methods')
    and ($action != 'get_delivery_date')
    and ($action != 'get_cross_sell_items')
    and ($action != 'update_product_status')
    and ($action != 'update_product_group_status')
) {

    // If a user was not found then respond with an error.
    if (!USER_LOGGED_IN) {
        respond(array(
            'status' => 'error',
            'message' => 'Invalid login.'));
    }

    if ($action != 'get_form' and $action != 'get_forms') {
        // If the user does not have a designer or administrator role, then respond with an error.
        if (USER_ROLE > 1) {
            respond(array(
                'status' => 'error',
                'message' => 'The User must have a Designer or Administrator role.'));
        }
    }
}
include_once('mysqldump.php');

switch ($action) {
    case 'upload_file': 
        $data = $request['data'];
        $file_name = $request['name'];
        // get file name with and without file extension
        $file_name_without_extension = mb_substr($file_name, 0, mb_strrpos($file_name, '.'));
        $file_extension = mb_substr($file_name, mb_strrpos($file_name, '.') + 1);
        $image_data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
        
        $image_data = str_replace(' ', '+', $image_data);
        $image_data = base64_decode($image_data);
        // Check if file name is already in use and change it if necessary.
        $file_name = get_unique_name(array(
            'name' => $file_name,
            'type' => 'file'));

        // save the file
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $file_name, 'w');
        fwrite($handle, $image_data);
        fclose($handle);
        
        // insert file data into files table
        $query =
            "INSERT INTO files (
                name,
                folder,
                type,
                size,
                user,
                design,
                optimized,
                timestamp) 
            VALUES (
                '" . escape($file_name) . "',
                '1',
                '" . escape($file_extension) . "',
                '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $file_name)) . "',
                '" . $user['id'] . "',
                '0',
                '0',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
        log_activity("file ($file_name) was created via Image Editor", $_SESSION['sessionusername']);
        //return success json output
        $response = array(
        'status' => 'success',
        'name' => $file_name,
        'filesize' => h(convert_bytes_to_string(filesize(FILE_DIRECTORY_PATH . '/' . $file_name))),
        'message' => 'Upload Success');
        echo encode_json($response);  
        exit();
        break;
    
    case 'update_dashboard_note':
        $notes = $request['notes'];
        $notes = base64_encode($notes);
        $query =
            "UPDATE dashboard
            SET
            notes_widget_data = '$notes'
        ";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        //return success json output
        $response = array(
        'status' => 'success',
        'message' => 'Dashboard update is successful');
        echo encode_json($response);  
        exit();
        break;

    case 'update_dashboard':
        $message_text = $request['message_text'];

        $query = "SELECT * FROM dashboard";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $dashboard = mysqli_fetch_assoc($result);
        $exBackground = $dashboard['bg_image'];


        $NewBackground = $request['NewBackground'];
        if(!empty($NewBackground)){
            $query ="UPDATE dashboard SET bg_image = '$NewBackground'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $response = array(
                'status' => 'success',
                'exBackground' => $exBackground,
                'NewBackground' => $NewBackground,
                'message' => 'widget ' . $message_text . ' process successful.');

        }

        $NewTheme = $request['NewTheme'];
        if(!empty($NewTheme)){
            $query ="UPDATE dashboard SET widget_themes = '$NewTheme'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $response = array(
                'status' => 'success',
                'message' => 'widget ' . $message_text . ' process successful.');
        }
        

        
        echo encode_json($response);  
        exit();
        break; 


    case 'update_dashboard_widgets':
        $message_text = $request['message_text'];
        $widgets = $request['widgets'];
        $widgets = implode(',', $widgets);
        $query =
            "UPDATE dashboard
            SET
            order_widgets = '$widgets'
        ";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        //return success json output
        $response = array(
        'status' => 'success',
        'message' => 'widget ' . $message_text . ' process successful.');
        echo encode_json($response);  
        exit();
        break; 
 

    case 'software_backup':
        
        $step = $request['step'];
        $backup_name = $request['backup_name'];

        $backup_location ='install/backups/';
        $backup_folder_name = $backup_name;

        switch($step){
            
            case 'create_backup_folder':
                if(!$backup_name){
                    $backup_name = date("Y_m_d_H_i", time());
                }

                //replace some spacial caracters, because we cant delete if contain '.'
                $sReplace = array('.', ',', '!','?'); 
                $backup_folder_name = str_replace($sReplace, '_',$backup_name);

                //check if directory is exists
			    //if not exist Create directory.
			    if (!file_exists($backup_location.$backup_folder_name)) {
				    mkdir($backup_location.$backup_folder_name, 0777, true);
                }
                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Site backup folder create successful. Mysql dumb creating, please wait...');
                echo encode_json($response);  
                exit();   
            break;

            case 'create_mysql_dumb':
                include_once('mysqldump.php');
                
                //Create mysql dump file named slq.sql and save it in backup directory
			    // first backup Mysql because, if there is timeout when file copy mysql important for us. so even timeout to copy files or layouts we have mysql dump anyway.
			    try {
			    	$dump = new IMysqldump\Mysqldump('mysql:host='.DB_HOST.';dbname='.DB_DATABASE.'', ''.DB_USERNAME.'', ''.DB_PASSWORD.'');
			        $dump->start($backup_location.$backup_folder_name .'/sql.sql');
			    } catch (\Exception $e) {
			    	$backups_error_message = $e->getMessage();
                
			    	//if mysql error and backup folder is empty, delete it.
			    	if (!file_exists($backup_location.$backup_folder_name.'/*')) {
			    		rmdir($backup_location.$backup_folder_name);
                    }
                    
                    log_activity('Creating Mysql Dumb is Failure. Because: '.h($backups_error_message), $_SESSION['sessionusername']);
                    //return error json output
                    $response = array(
                    'status' => 'error',
                    'message' => h($backups_error_message));
                    echo encode_json($response);  
                    exit();   
                }
                
                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Mysql dumb created in backup directory successful. Clearing old files in directory, please wait...');
                echo encode_json($response);  
                exit();   
                //Mysql Backup complete
            break;

            case 'clear_files_and_layouts':
                //Prepare for files and layouts**
			    //if files directory not exist Create directory
			    if (!file_exists($backup_location.$backup_folder_name.'/files')) {
			    	mkdir($backup_location.$backup_folder_name.'/files', 0777, true);
			    }
			    //if layouts directory not exist Create directory
			    if (!file_exists($backup_location.$backup_folder_name.'/layouts')) {
			    	mkdir($backup_location.$backup_folder_name.'/layouts', 0777, true);
			    }
			    //CLEAR//
			    // delete all files from template files directory
			    $files = glob($backup_location.$backup_folder_name.'/files/{,.}*', GLOB_BRACE); // get all file names
			    foreach($files as $file){ // iterate files
			    	if(is_file($file))
			    	unlink($file); // delete file
			    }
			    // delete all files from template layouts directory
			    $layouts = glob($backup_location.$backup_folder_name.'/layouts/{,.}*', GLOB_BRACE); // get all layouts names
			    foreach($layouts as $layout){ // iterate layouts files
			    	if(is_file($layout))
			    	unlink($layout); // delete layouts files
                }
                
                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Files and layouts cleared in backup directory. Copying files, please wait...');
                echo encode_json($response);  
                exit();   
            break;


            case 'move_files':

			    //WRITE//
			    // prepare path to template files
			    $backup_files_path = $backup_location.$backup_folder_name.'/files/';
			    $handle = opendir(FILE_DIRECTORY_PATH);
			    // copy files to backup directory
			    while (false !== ($file = readdir($handle))) {
			        if (($file != '.') && ($file != '..')) {
			            copy(FILE_DIRECTORY_PATH . '/' . $file,$backup_files_path . $file);
			        }
			    }
			    closedir($handle);

                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Files copied to backup directory. Copying layouts, please wait...');
                echo encode_json($response);  
                exit();   
            break;

            case 'move_layouts':

			    //WRITE//
			    // prepare path to template layouts
			    $backup_layouts_path = $backup_location.$backup_folder_name.'/layouts/';
			    $handle = opendir(LAYOUT_DIRECTORY_PATH);
			    // copy files to backup directory
			    while (false !== ($file = readdir($handle))) {
			        if (($file != '.') && ($file != '..')) {
			            copy(LAYOUT_DIRECTORY_PATH . '/' . $file,$backup_layouts_path . $file);
			        }
			    }
			    closedir($handle);

                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Layouts copied to backup directory. Creating .htaccess for security reason, please wait...');
                echo encode_json($response);  
                exit();   
            break;

            case 'create_htaccess':

                file_put_contents($backup_location.$backup_folder_name.'/.htaccess','deny from all');
                //return success json output
                $response = array(
                    'status' => 'success',
                    'backup_name' => $backup_folder_name,
                    'message' => 'Htaccess create in backup directory successful. Check backup folder create success or not, please wait...');
                echo encode_json($response);  
                exit();   
            break;

            case 'check':
               
                if (file_exists($backup_location.$backup_folder_name)) {
                    
                    if (file_exists($backup_location.$backup_folder_name.'/sql.sql')) {
                        if (file_exists($backup_location.$backup_folder_name.'/files')) {
                            if (file_exists($backup_location.$backup_folder_name.'/layouts')) {
                                $liveform_backups= new liveform('backups');

                                log_activity("Software Backup (".$backup_name.") Success", $_SESSION['sessionusername']);
                                // Add notice to liveform.
                                $liveform_backups->add_notice('Software Backup ('.$backup_name.') Create Success.');
                                //return success json output
                                $response = array(
                                    'status' => 'success',
                                    'backup_name' => $backup_folder_name,
                                    'message' => 'Software Backup process Successful. Page will be refresh...');
                                echo encode_json($response);  
                                exit();   
                            }
                        }
                    }
                    
                }

                //return error json output
                $response = array(
                'status' => 'error',
                'message' => 'software Backup check has error. backup maybe still created but we cant provide.');
                echo encode_json($response);  
                exit();   

                
            break;

            default:
                //return error json output
                $response = array(
                'status' => 'error',
                'message' => 'software Backup steps error.');
                echo encode_json($response);  
                exit();   
        }
        

        
    
    
    break;



    case 'software_update':
        //software update is not software update check.
        //it is action to update software from software_update.php
        //used api because some slow servers connections down, timeout or somethings like this when do this one step.

        $step = $request['step'];
        switch($step){
            case 'check':
                //check if there is really have a software update, also software_update page check but may user open 2 page and update and update again.
                // now if try software update after an update user get error message and update stop.
                if(!function_exists('curl_init')){
                    $liveform->mark_error('Update','Software update check could not communicate with the software update server, because cURL is not installed, so it is not known if there is a software update available.');
                }
                $request = array();
                $request['hostname'] = HOSTNAME_SETTING;
                $request['url'] = URL_SCHEME . HOSTNAME_SETTING . PATH;
                $request['version'] = VERSION;
                $request['edition'] = EDITION;
                $request['uname'] = php_uname();
                $request['os'] = PHP_OS;
                $request['web_server'] = $_SERVER['SERVER_SOFTWARE'];
                $request['php_version'] = phpversion();
                $request['mysql_version'] = db("SELECT VERSION()");
                $request['installer'] = INSTALLER;
                $request['private_label'] = PRIVATE_LABEL;
                $data = encode_json($request);
                $API = '59593DS72233483322T669223344';
                $REQUEST ='version';
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://www.kodpen.com/api2?API='.$API.'&REQUEST='.$REQUEST);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data)));
                
                // if there is a proxy address, then send cURL request through proxy
                if (PROXY_ADDRESS != '') {
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                }
                
                $response = curl_exec($ch);
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);
                curl_close($ch);
                
                if ($response === false) {
                    log_activity(
                        'software update check could not communicate with the software update server, so it is not known if there is a software update available. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
                    //return error json output
                    $response = array(
                        'status' => 'error',
                        'message' => 'software update check could not communicate with the software update server, so it is not known if there is a software update available. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
                    echo encode_json($response);  
                    exit();
                }
                
                $response = decode_json($response);
                
                if (!isset($response['version'])) {
                    log_activity('software update check received an invalid response from the software update server, so it is not known if there is a software update available');
                    //return error json output
                    $response = array(
                        'status' => 'error',
                        'message' => 'software update check received an invalid response from the software update server, so it is not known if there is a software update available');
                    echo encode_json($response);  
                    exit();

                }
                // If the software update check is not disabled in the config.php file,
                // then continue to determine if there is a software update.
                if (
                    (defined('SOFTWARE_UPDATE_CHECK') == FALSE)
                    || (SOFTWARE_UPDATE_CHECK == TRUE)
                ) {
                    // figure out if new version is greater than old version
                    
                    $new_version = trim($response['version']);
                    $new_version_parts = explode('.', $new_version);
                    
                    $old_version = VERSION;
                    $old_version_parts = explode('.', $old_version);
                    
                    // assume that new version is not greater than old version, until we find out otherwise
                    $new_version_is_greater_than_old_version = FALSE;
                
                    // if the major number of the new version is greater than the major number of the old version,
                    // then the new version is greater than the old version
                    if ($new_version_parts[0] > $old_version_parts[0]) {
                        $new_version_is_greater_than_old_version = TRUE;
                        
                    // else if the major number of the new version is equal to the major number of the old version,
                    // then continue to check
                    } elseif ($new_version_parts[0] == $old_version_parts[0]) {
                        // if the minor number of the new version is greater than the minor number of the old version,
                        // then the new version is greater than the old version
                        if ($new_version_parts[1] > $old_version_parts[1]) {
                            $new_version_is_greater_than_old_version = TRUE;
                            
                        // else if the minor number of the new version is equal to the minor number of the old version,
                        // then continue to check
                        } elseif ($new_version_parts[1] == $old_version_parts[1]) {
                            // if the maintenance number of the new version is greater than the maintenance number of the old version,
                            // then the new version is greater than the old version
                            if ($new_version_parts[2] > $old_version_parts[2]) {
                                $new_version_is_greater_than_old_version = TRUE;
                            }
                        }
                    }
                
                    // assume that there is not an available software update until we find out otherwise
                    $software_update_available = 0;
                    
                    // if the new version is greater than the old version, then there is an available software update
                    if ($new_version_is_greater_than_old_version == TRUE) {
                        $software_update_available = 1;
                    }
                
                }
                //there is no software
                if($software_update_available == 0){
                    //return error json output
                    $response = array(
                    'status' => 'error',
                    'message' => 'There is no update available.');
                    echo encode_json($response);  
                    exit();
                }
                //there is software update so we can go step 2:Download the update file.
                //return success json output
                $response = array(
                    'status' => 'success',
                    'message' => 'Update Check Successful there is a update to install, The update files downloading please wait...');
                echo encode_json($response);
                exit();
                
            break;
            case 'download':
                //Step 2: download update file from curl 
                $ch = curl_init ("https://www.kodpen.com/pinegrap_update.zip");
	            curl_setopt($ch, CURLOPT_HEADER, 0);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

                // if there is a proxy address, then send cURL request through proxy
                if (PROXY_ADDRESS != '') {
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
                }
                $raw = curl_exec($ch);
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);
                curl_close($ch);
                if ($raw === false) {
                    // there is an error about download so notice user and log activiy
                    log_activity(
                        'software update file get could not communicate with the software update server, may its about update server so try it later. cURL Error Number: ' . $curl_errno . '. cURL Error Message: ' . $curl_error . '.');
                    //return error json output
                    $response = array(
                        'status' => 'error',
                        'message' => 'software update file get could not communicate with the software update server. Curl Error');
                    echo encode_json($response);  
                    exit();
                }

                // Zip file name
	            $filename = 'pinegrap_update.zip';
	            if(file_exists($filename)){
	                unlink($filename);
                }
                
                $fp = fopen('pinegrap_update.zip','x');
	            fwrite($fp, $raw);
                fclose($fp);
                //zip file download success we can go step 3: replace the software files
                $response = array(
                    'status' => 'success',
                    'message' => 'The update files has been downloaded. Update Files overwriting Please Wait...');
                echo encode_json($response);
                exit();
                break;

            case 'replace':
                //Step 3: replace files.
                define('_PATH', dirname(__FILE__));
	            // Zip file name
	            $filename = 'pinegrap_update.zip';
	            $zip = new ZipArchive;
	            $res = $zip->open($filename);
	            if ($res === TRUE) {
	            	// Unzip path
	            	$path = _PATH."/../";
	            	// Extract file
	            	$zip->extractTo($path);
	            	$zip->close();
	            	unlink($filename);
	            } else {
                    //there is error to write notice user. stop update 
                    //return error json output
                    $response = array(
                    'status' => 'error',
                    'message' => 'Something went wrong to write update files.');
                    echo encode_json($response);  
                    exit();
                }
                

                //there is no error so update complete.
                //return success json output
                $response = array(
                    'status' => 'success',
                    'message' => 'Software Update process Successful. You will be redirected for Upgrade.');
                echo encode_json($response);
                exit();

            break;

            default:
                //return error json output
                $response = array(
                'status' => 'error',
                'message' => 'software update steps error.');
                echo encode_json($response);  
                exit();
        }
         
        exit();
    break;

    case 'get_installment_options':
        //This options for only for Credit/debit Cart and to get installment table,
        // if supported installment, we output a table with all supported cards and banks installment prices.
        switch (ECOMMERCE_PAYMENT_GATEWAY) {
            case 'Iyzipay':
                //prepare to installment check
                $card_number_request = $request['card'];
                //remove spaces in card number.
                $card_number_without_spaces = str_replace(' ', '', $card_number_request);

                //get total price 
                $price = $request['price'];

                //if installment option not activated from site settings than pass to installment check.
                if( ECOMMERCE_IYZIPAY_INSTALLMENT >= 2 ){      
				    // if test or live mode for iyzipay gateway.
				    if (ECOMMERCE_PAYMENT_GATEWAY_MODE == 'test') {
				    	$payment_gateway_host = 'https://sandbox-api.iyzipay.com';
				    }else {
				    	$payment_gateway_host = 'https://api.iyzipay.com';
                    }
				    require_once('iyzipay-php/IyzipayBootstrap.php');
                    IyzipayBootstrap::init();
				    $card_binNumber = substr($card_number_without_spaces, 0, 6);
				    // Conversation ID Digits amount
				    $digits = 9;
				    // Random Conversation ID
				    $conversationid = rand(pow(10, $digits-1), pow(10, $digits)-1);
				    //config
				    $options = new \Iyzipay\Options();
				    $options->setApiKey(ECOMMERCE_IYZIPAY_API_KEY);
				    $options->setSecretKey(ECOMMERCE_IYZIPAY_SECRET_KEY);
				    $options->setBaseUrl($payment_gateway_host);
				    $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
				    $request->setLocale(strtoupper(language_ruler()));//get location from sofware language, where set from software settings
				    $request->setConversationId($conversationid);
				    $request->setBinNumber($card_binNumber);
				    $request->setPrice($price);
                    $installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, $options);
                    $result = $installmentInfo->getRawResult();
                    $oneinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[0]->installmentPrice;
                    $oneinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[0]->totalPrice;
					$twoinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[1]->installmentPrice;
					$twoinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[1]->totalPrice;
					$threeinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[2]->installmentPrice;
					$threeinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[2]->totalPrice;
					$sixinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[3]->installmentPrice;
					$sixinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[3]->totalPrice;	
					$nineinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[4]->installmentPrice;
					$nineinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[4]->totalPrice;
					$twelveinstallment_price = json_decode($result)->installmentDetails[0]->installmentPrices[5]->installmentPrice;
                    $twelveinstallment_totalprice = json_decode($result)->installmentDetails[0]->installmentPrices[5]->totalPrice;

                    //create array for response. We will update values with array_replace later. if ajax return 0 value it mean there is no  
                    $response =array(
                        "monthlytwo"    => "0",
                        "totaltwo"      => "0",
                        "two_supported" => "0",
                        "two_inst_increase" => "0",
                        "monthlythree"    => "0",
                        "totalthree"      => "0",
                        "three_supported" => "0",
                        "three_inst_increase" => "0",
                        "monthlysix"    => "0",
                        "totalsix"      => "0",
                        "six_supported" => "0",
                        "six_inst_increase" => "0",
                        "monthlynine"    => "0",
                        "totalnine"      => "0",
                        "nine_supported" => "0",
                        "nine_inst_increase" => "0",
                        "monthlytwelve"    => "0",
                        "totaltwelve"      => "0",
                        "twelve_supported" => "0",
                        "twelve_inst_increase" => "0",
                    );


                    //if there is result from iyzipay installment check than update array with them.
                    if($result){

                        if( ($twoinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 2) ){
                            $two_installment_monthly_price = BASE_CURRENCY_SYMBOL . $twoinstallment_price;
                            $two_installment_total_price = BASE_CURRENCY_SYMBOL . $twoinstallment_totalprice;
                            $twoinstallment_increase_price = BASE_CURRENCY_SYMBOL . ($twoinstallment_totalprice - $price);

                            $array_replace = ['monthlytwo' => $two_installment_monthly_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['totaltwo' => $two_installment_total_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['two_supported' => '1'];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['two_inst_increase' => $twoinstallment_increase_price];
                            $response = array_replace($response, $array_replace);
                        }
                        if( ($threeinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 3) ){
                            $three_installment_monthly_price = BASE_CURRENCY_SYMBOL . $threeinstallment_price;
                            $three_installment_total_price = BASE_CURRENCY_SYMBOL . $threeinstallment_totalprice;
                            $threeinstallment_increase_price = BASE_CURRENCY_SYMBOL . ($threeinstallment_totalprice - $price);

                            $array_replace = ['monthlythree' => $three_installment_monthly_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['totalthree' => $three_installment_total_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['three_supported' => '1'];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['three_inst_increase' => $threeinstallment_increase_price];
                            $response = array_replace($response, $array_replace);
                        }
                        if( ($sixinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 6) ){
                            $six_installment_monthly_price = BASE_CURRENCY_SYMBOL . $sixinstallment_price;
                            $six_installment_total_price = BASE_CURRENCY_SYMBOL . $sixinstallment_totalprice;
                            $sixinstallment_increase_price = BASE_CURRENCY_SYMBOL . ($sixinstallment_totalprice - $price);

                            $array_replace = ['monthlysix' => $six_installment_monthly_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['totalsix' => $six_installment_total_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['six_supported' => '1'];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['six_inst_increase' => $sixinstallment_increase_price];
                            $response = array_replace($response, $array_replace);
                        }
                        if( ($nineinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 9) ){
                            $nine_installment_monthly_price = BASE_CURRENCY_SYMBOL . $nineinstallment_price;
                            $nine_installment_total_price = BASE_CURRENCY_SYMBOL . $nineinstallment_totalprice;
                            $nineinstallment_increase_price = BASE_CURRENCY_SYMBOL . ($nineinstallment_totalprice - $price);

                            $array_replace = ['monthlynine' => $nine_installment_monthly_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['totalnine' => $nine_installment_total_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['nine_supported' => '1'];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['nine_inst_increase' => $nineinstallment_increase_price];
                            $response = array_replace($response, $array_replace);
                        }
                        if( ($twelveinstallment_price)&&(ECOMMERCE_IYZIPAY_INSTALLMENT >= 12) ){
                            $twelve_installment_monthly_price = BASE_CURRENCY_SYMBOL . $twelveinstallment_price;
                            $twelve_installment_total_price = BASE_CURRENCY_SYMBOL . $twelveinstallment_totalprice;
                            $twelveinstallment_increase_price = BASE_CURRENCY_SYMBOL . ($twelveinstallment_totalprice - $price);

                            $array_replace = ['monthlytwelve' => $twelve_installment_monthly_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['totaltwelve' => $twelve_installment_total_price];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['twelve_supported' => '1'];
                            $response = array_replace($response, $array_replace);

                            $array_replace = ['twelve_inst_increase' => $twelveinstallment_increase_price];
                            $response = array_replace($response, $array_replace);
                        }


                    }
                    //return json output
                    echo encode_json($response);  

                }else{
                    //return error json output
                    $response = array(
                        'status' => 'error',
                        'message' => 'No Installment Supported');
                    echo encode_json($response);  
                }   
            break;
        }
        exit();
    break;

    case 'add_to_cart':

        require_once(dirname(__FILE__) . '/add_to_cart.php');

        $response = add_to_cart($request);

        echo encode_json($response);
        exit();
        
        break;

    case 'delete_order':

        validate_token();

        require_once(dirname(__FILE__) . '/delete_order.php');

        $response = delete_order(array('order' => $request['order']));

        echo encode_json($response);
        exit();
        
        break;

    case 'get_common_regions':
        $common_regions = db_items(
            "SELECT
                cregion_id AS id,
                cregion_name AS name,
                cregion_content AS content
            FROM cregion
            WHERE cregion_designer_type = 'no'
            ORDER BY cregion_name ASC");

        $response = array(
            'status' => 'success',
            'common_regions' => $common_regions);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_cross_sell_items':

        require_once(dirname(__FILE__) . '/get_cross_sell_items.php');

        $response = get_cross_sell_items($request);

        echo encode_json($response);
        exit();
        
        break;

    // Used to get the estimated delivery date for a recipient and method.

    case 'get_delivery_date':

        require_once(dirname(__FILE__) . '/shipping.php');

        $response = get_delivery_date($request);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_design_files':
        $sql_types = "";

        // If types is an array and has at least one item,
        // then prepare SQL to limit by types.
        if ((is_array($request['types']) == true) && $request['types']) {
            foreach ($request['types'] as $type) {
                if ($sql_types != '') {
                    $sql_types .= " OR ";
                }

                $sql_types .= "(type = '" . escape($type) . "')";
            }

            $sql_types = "AND ($sql_types)";
        }

        $sql_search = "";

        if ($request['search'] != '') {
            $sql_search = "AND (name LIKE '%" . escape(escape_like($request['search'])) . "%')";
        }

        $design_files = db_items(
            "SELECT
                id,
                name,
                type,
                theme
            FROM files
            WHERE
                (design = '1')
                $sql_types
                $sql_search
            ORDER BY timestamp DESC");

        // If a specific type of theme was specified, then loop through design files
        // in order to only include that type of theme.
        if ($request['theme_type'] != '') {
            foreach ($design_files as $key => $design_file) {
                // If this file is a CSS theme, then determine what type of theme it is.
                if ($design_file['theme'] == 1) {
                    // If this is a system theme, then set that.
                    if (db_value("SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $design_file['id'] . "'") > 0) {
                        $design_file['theme_type'] = 'system';

                    // Otherwise this is a custom theme, so set that.
                    } else {
                        $design_file['theme_type'] = 'custom';
                    }

                    // If the theme type does not matched the requested theme type,
                    // then remove this design file from the array.
                    if ($design_file['theme_type'] != $request['theme_type']) {
                        unset($design_files[$key]);
                    }
                }
            }
        }

        $response = array(
            'status' => 'success',
            'design_files' => $design_files);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_designer_region':
        $designer_region = db_item(
            "SELECT
                cregion_id AS id,
                cregion_name AS name,
                cregion_content AS content
            FROM cregion
            WHERE
                (cregion_designer_type = 'yes')
                AND (cregion_id = '" . escape($request['designer_region']['id']) . "')");
        
        // If a designer region was not found then respond with an error.
        if (!$designer_region) {
            $response = array(
                'status' => 'error',
                'message' => 'Designer Region could not be found.');

            echo encode_json($response);
            exit();
        }

        $response = array(
            'status' => 'success',
            'designer_region' => $designer_region);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_designer_regions':
        $sql_content = "";

        if ($request['content'] == true) {
            $sql_content = ", cregion_content AS content";
        }

        $sql_search = "";

        if ($request['search'] != '') {
            $sql_search = "AND (cregion_name LIKE '%" . escape(escape_like($request['search'])) . "%')";
        }

        $designer_regions = db_items(
            "SELECT
                cregion_id AS id,
                cregion_name AS name
                $sql_content
            FROM cregion
            WHERE
                (cregion_designer_type = 'yes')
                $sql_search
            ORDER BY cregion_timestamp DESC");

        $response = array(
            'status' => 'success',
            'designer_regions' => $designer_regions);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_dynamic_region':
        $dynamic_region = db_item(
            "SELECT
                dregion_id AS id,
                dregion_name AS name,
                dregion_code AS content
            FROM dregion
            WHERE dregion_id = '" . escape($request['dynamic_region']['id']) . "'");
        
        // If a dynamic region was not found then respond with an error.
        if (!$dynamic_region) {
            $response = array(
                'status' => 'error',
                'message' => 'Dynamic Region could not be found.');

            echo encode_json($response);
            exit();
        }

        $response = array(
            'status' => 'success',
            'dynamic_region' => $dynamic_region);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_dynamic_regions':
        $sql_content = "";

        if ($request['content'] == true) {
            $sql_content = ", dregion_code AS content";
        }

        $sql_search = "";

        if ($request['search'] != '') {
            $sql_search = "WHERE dregion_name LIKE '%" . escape(escape_like($request['search'])) . "%'";
        }

        $dynamic_regions = db_items(
            "SELECT
                dregion_id AS id,
                dregion_name AS name
                $sql_content
            FROM dregion
            $sql_search
            ORDER BY dregion_timestamp DESC");

        $response = array(
            'status' => 'success',
            'dynamic_regions' => $dynamic_regions);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_file':
        $file = db_item(
            "SELECT
                id,
                name,
                theme
            FROM files
            WHERE id = '" . escape($request['file']['id']) . "'");
        
        // If a file was not found then respond with an error.
        if (!$file) {
            $response = array(
                'status' => 'error',
                'message' => 'File could not be found.');

            echo encode_json($response);
            exit();
        }

        // If this file is a CSS theme, then determine what type of theme it is.
        if ($file['theme'] == 1) {
            // If this is a system theme, then set that.
            if (db_value("SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $file['id'] . "'") > 0) {
                $file['theme_type'] = 'system';

            } else {
                $file['theme_type'] = 'custom';
            }
        }

        $file['content'] = file_get_contents(FILE_DIRECTORY_PATH . '/' . $file['name']);

        $response = array(
            'status' => 'success',
            'file' => $file);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_folders':
        $folders = db_items(
            "SELECT
                folder_id AS id,
                folder_name AS name,
                folder_parent AS parent_folder_id,
                folder_level AS level,
                folder_style AS style_id,
                mobile_style_id,
                folder_order AS sort_order,
                folder_access_control_type AS access_control_type,
                folder_archived AS archived
            FROM folder
            ORDER BY
                folder_level ASC,
                folder_order ASC,
                folder_name ASC");

        $response = array(
            'status' => 'success',
            'folders' => $folders);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_form':

        require_once(dirname(__FILE__) . '/forms.php');

        $request['check_access'] = true;

        respond(get_form($request));
        
        break;

    case 'get_forms':

        require_once(dirname(__FILE__) . '/forms.php');

        $request['check_access'] = true;

        respond(get_forms($request));
        
        break;

    case 'get_items_in_style':
        $style = db_item(
            "SELECT
                style_id AS id,
                style_code AS code
            FROM style
            WHERE style_id = '" . escape($request['style_id']) . "'");
        
        // If a style was not found then respond with an error.
        if (!$style) {
            $response = array(
                'status' => 'error',
                'message' => 'Style could not be found.');

            echo encode_json($response);
            exit();
        }

        $content = $style['code'];

        $content = preg_replace('/{path}/i', OUTPUT_PATH, $content);

        $design_files = array();

        // Find all CSS and JS resources in the style content.
        preg_match_all('/["\']\s*([^"\']*\.(css|js)[^"\']*)\s*["\']/i', $content, $matches, PREG_SET_ORDER);

        // Loop through all of the resources in order to determine if they
        // are design files for this site.
        foreach ($matches as $match) {
            $url = trim($match[1]);
            $url = unhtmlspecialchars($url);
            $url_parts = parse_url($url);
            $file_name = basename($url_parts['path']);
            $file_name = rawurldecode($file_name);

            // Check if design file exists for this file name.
            $design_file = db_item(
                "SELECT
                    id,
                    name,
                    type,
                    theme
                FROM files
                WHERE
                    (design = '1')
                    AND (name = '" . escape($file_name) . "')");

            // If a design file was found, then add it to array.
            if ($design_file) {
                // If this file is a CSS theme, then determine what type of theme it is.
                if ($design_file['theme'] == 1) {
                    // If this is a system theme, then set that.
                    if (db_value("SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . $design_file['id'] . "'") > 0) {
                        $design_file['theme_type'] = 'system';

                    } else {
                        $design_file['theme_type'] = 'custom';
                    }
                }

                $design_files[] = $design_file;
            }
        }

        $designer_regions = array();

        // Get all designer regions in the style content.
        preg_match_all('/<cregion>.*?<\/cregion>/i', $content, $regions);

        foreach ($regions[0] as $region) {
            $name = strip_tags($region);

            $designer_region = db_item(
                "SELECT
                    cregion_id AS id,
                    cregion_name AS name
                FROM cregion
                WHERE
                    (cregion_name = '" . escape($name) . "')
                    AND (cregion_designer_type = 'yes')");

            // If a designer region was found, then add it to array.
            if ($designer_region) {
                $designer_regions[] = $designer_region;
            }
        }

        $dynamic_regions = array();

        // Get all dynamic regions in the style content.
        preg_match_all('/<dregion.*?>.*?<\/dregion>/i', $content, $regions);

        foreach ($regions[0] as $region) {
            $name = strip_tags($region);

            $dynamic_region = db_item(
                "SELECT
                    dregion_id AS id,
                    dregion_name AS name
                FROM dregion
                WHERE dregion_name = '" . escape($name) . "'");

            // If a dynamic region was found, then add it to array.
            if ($dynamic_region) {
                $dynamic_regions[] = $dynamic_region;
            }
        }

        $system_regions = array();

        // Get all system regions.
        preg_match_all('/<system>.*?<\/system>/i', $content, $regions);

        foreach ($regions[0] as $region) {
            $name = strip_tags($region);

            // If this is a secondary system region with a page name,
            // then add it to the array.
            if ($name != '') {
                $system_region = array();
                $system_region['name'] = $name;

                $system_regions[] = $system_region;
            }
        }

        $response = array(
            'status' => 'success',
            'design_files' => $design_files,
            'designer_regions' => $designer_regions,
            'dynamic_regions' => $dynamic_regions,
            'system_regions' => $system_regions);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_layout':
        $layout = db_item(
            "SELECT
                page_id AS id,
                page_name AS name,
                layout_modified AS modified
            FROM page
            WHERE page_id = '" . escape($request['layout']['id']) . "'");
        
        // If a layout was not found then respond with an error.
        if (!$layout) {
            $response = array(
                'status' => 'error',
                'message' => 'Layout could not be found.');

            echo encode_json($response);
            exit();
        }

        if ($layout['modified']) {
            $layout['content'] = @file_get_contents(LAYOUT_DIRECTORY_PATH . '/' . $layout['id'] . '.php');

        } else {
            require_once(dirname(__FILE__) . '/generate_layout_content.php');

            $layout['content'] = generate_layout_content($layout['id']);
        }

        $response = array(
            'status' => 'success',
            'layout' => $layout);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_page':
        if ($request['page']['id'] != '') {
            $where = "page_id = '" . e($request['page']['id']) . "'";

        } else {
            $where = "page_name = '" . e($request['page']['name']) . "'";
        }

        $page = db_item(
            "SELECT
                page_id AS id,
                page_name AS name,
                page_type AS type,
                layout_type
            FROM page
            WHERE $where");
        
        // If a page was not found then respond with an error.
        if (!$page) {
            $response = array(
                'status' => 'error',
                'message' => 'Page could not be found.');

            echo encode_json($response);
            exit();
        }

        // If page type properties were requested, and this page has page type properties,
        // then get them.
        if (
            $request['page_type_properties']
            && check_for_page_type_properties($page['type'])
        ) {
            $page_type_properties = get_page_type_properties($page['id'], $page['type']);

            if ($page_type_properties) {
                $page['page_type_properties'] = $page_type_properties;
            }
        }

        $response = array(
            'status' => 'success',
            'page' => $page);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_pages':
        $pages = db_items(
            "SELECT
                page_id AS id,
                page_name AS name,
                page_folder AS folder_id,
                page_style AS style_id,
                mobile_style_id,
                page_home AS home,
                page_title AS title,
                page_search AS search,
                page_search_keywords AS search_keywords,
                page_meta_description AS meta_description,
                page_meta_keywords AS meta_keywords,
                page_type AS type,
                layout_type,
                layout_modified,
                comments,
                comments_label,
                comments_message,
                comments_allow_new_comments,
                comments_disallow_new_comment_message,
                comments_automatic_publish,
                comments_allow_user_to_select_name,
                comments_require_login_to_comment,
                comments_allow_file_attachments,
                comments_show_submitted_date_and_time,
                comments_administrator_email_to_email_address,
                comments_administrator_email_subject,
                comments_administrator_email_conditional_administrators,
                comments_submitter_email_page_id,
                comments_submitter_email_subject,
                comments_watcher_email_page_id,
                comments_watcher_email_subject,
                comments_watchers_managed_by_submitter,
                seo_score,
                seo_analysis,
                seo_analysis_current,
                sitemap,
                system_region_header,
                system_region_footer
            FROM page
            ORDER BY page_name ASC");

        // Loop through the pages in order to get page type properties and page regions.
        foreach ($pages as $key => $page) {
            // If this page's type has page type properties, then get them.
            if (check_for_page_type_properties($page['type']) == true) {
                $page_type_properties = get_page_type_properties($page['id'], $page['type']);

                // If properties were found, then add them.
                if (is_array($page_type_properties) == true) {
                    $pages[$key]['page_type_properties'] = $page_type_properties;
                }
            }

            $page_regions = db_items(
                "SELECT
                    pregion_id AS id,
                    pregion_name AS name,
                    pregion_content AS content,
                    pregion_page AS page_id,
                    pregion_order AS sort_order,
                    collection
                FROM pregion
                WHERE pregion_page = '" . $page['id'] . "'");

            $pages[$key]['page_regions'] = $page_regions;
        }

        $response = array(
            'status' => 'success',
            'pages' => $pages);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_product':
        $product_raw = db_item(
            "SELECT
                id,
                name,
                short_description,
                full_description,
                details,
                code,
                image_name,
                inventory,
                inventory_quantity,
                out_of_stock_message
            FROM products
            WHERE id = '" . e($request['product']['id']) . "'");    


        
        //if code has ^^image_loop_start^^ and ^^image_url^^ and ^^image_loop_end^^. with these we can make an ease loop
        if( (strpos($product_raw['code'], '^^image_url^^') !== false)&&
            (strpos($product_raw['code'], '^^image_loop_start^^') !== false)&&
            (strpos($product_raw['code'], '^^image_loop_end^^') !== false)
        ){        
            //check for image list from products_images_xref
            $item_images = "SELECT product,file_name FROM products_images_xref WHERE product = '" . e($request['product']['id']) . "'";
            $image_results = mysqli_query(db::$con, $item_images) or output_error('Query failed');
            
            $code_header_position = strpos($product_raw['code'], '^^image_loop_start^');//number
            $code_content_position = strpos($product_raw['code'], '^^image_url^^');
            $code_footer_position = strpos($product_raw['code'], '^^image_loop_end^');//number
            $code_header = substr( $product_raw['code'], 0 ,strpos($product_raw['code'], '^^image_loop_start^') );
            $code_content_raw = substr( $product_raw['code'], (strpos($product_raw['code'], '^^image_loop_start^') + 20) , (strpos($product_raw['code'], '^^image_loop_end^') - strpos($product_raw['code'], '^^image_loop_start^')  - 20) );
            $code_footer = substr($product_raw['code'], strpos($product_raw['code'], '^^image_loop_end^') + 18 );
            $code_image_alt = false;
            if(strpos($product_raw['code'], '^^image_alt^^') !== false){  
                $code_image_alt = true;
            }

            //if product image xref or product group  xref exist. this mean this selected multiple product image
            if(mysqli_num_rows($image_results) != 0){

                //if there is image alt tag
                if($code_image_alt !== false){  
                    $code_content = str_replace("^^image_url^^", PATH . encode_url_path($product_raw['image_name']) , str_replace("^^image_alt^^", $product_raw['short_description'] ,$code_content_raw) );
                //if there is no image alt tag
                }else{
                    $code_content = str_replace("^^image_url^^", PATH . encode_url_path($product_raw['image_name']) , $code_content_raw);

                }
                while ($image = mysqli_fetch_assoc($image_results)){
                    //if there is image alt tag
                    if($code_image_alt !== false){  
                        $code_content .= str_replace("^^image_url^^", PATH . encode_url_path($image['file_name']) , str_replace("^^image_alt^^", $product_raw['short_description'] ,$code_content_raw) );
                    //if there is no image alt tag
                    }else{
                        $code_content .= str_replace("^^image_url^^", PATH . encode_url_path($image['file_name']) , $code_content_raw);
                    }
                }
                $product_replace = ['code' => $code_header.$code_content.$code_footer];
                $product = array_replace($product_raw, $product_replace);
            }else{
                //else if less an image selected and only one image selected, but there is code for action we output single image
                if($product_raw['image_name']){
                    //if there is image alt tag
                    if($code_image_alt !== false){  
                        $code_single_content = str_replace("^^image_url^^", PATH . encode_url_path($product_raw['image_name']) , str_replace("^^image_alt^^", $product_raw['short_description'] ,$code_content_raw) );
                    //if there is no image alt tag
                    }else{
                        $code_single_content = str_replace("^^image_url^^", PATH . encode_url_path($product_raw['image_name']) , $code_content_raw);
                    }
                    $product_replace = ['code' => $code_header.$code_single_content.$code_footer];
                    $product = array_replace($product_raw, $product_replace);
                }else{
                    $product = $product_raw;
                }
            }
        }else{
            $product = $product_raw;
        }

        // If a product was not found then respond with an error.
        if (!$product) {
            $response = array(
                'status' => 'error',
                'message' => 'Product could not be found.');

            echo encode_json($response);
            exit();
        }

        $response = array(
            'status' => 'success',
            'product' => $product);

        echo encode_json($response);
        exit();
        
        break;

    // Used to get the appropriate shipping methods for the shipping address and arrival date
    // that customer selected on express order

    case 'get_shipping_methods':

        require_once(dirname(__FILE__) . '/shipping.php');

        $response = get_shipping_methods($request);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_style':
        $style = db_item(
            "SELECT
                style_id AS id,
                style_name AS name,
                style_type AS type,
                style_layout AS layout,
                style_empty_cell_width_percentage AS empty_cell_width_percentage,
                style_code AS code,
                style_head AS head,
                social_networking_position,
                additional_body_classes,
                collection,
                layout_type
            FROM style
            WHERE style_id = '" . escape($request['style']['id']) . "'");
        
        // If a style was not found then respond with an error.
        if (!$style) {
            $response = array(
                'status' => 'error',
                'message' => 'Style could not be found.');

            echo encode_json($response);
            exit();
        }

        $response = array(
            'status' => 'success',
            'style' => $style);

        echo encode_json($response);
        exit();
        
        break;

    case 'get_styles':
        $sql_code = "";

        if ($request['code'] == true) {
            $sql_code = "style_code AS code,";
        }

        $sql_where = "";

        if ($request['type'] != '') {
            $sql_where .= "WHERE (style_type = '" . escape($request['type']) . "')";
        }

        if ($request['search'] != '') {
            if ($sql_where == '') {
                $sql_where .= "WHERE ";
            } else {
                $sql_where .= " AND ";
            }

            $sql_where .= "(style_name LIKE '%" . escape(escape_like($request['search'])) . "%')";
        }

        $styles = db_items(
            "SELECT
                style_id AS id,
                style_name AS name,
                style_type AS type,
                style_layout AS layout,
                style_empty_cell_width_percentage AS empty_cell_width_percentage,
                $sql_code
                style_head AS head,
                social_networking_position,
                additional_body_classes,
                collection,
                layout_type
            FROM style
            $sql_where
            ORDER BY style_timestamp DESC");

        // Loop through the styles in order to get cells for system styles.
        foreach ($styles as $key => $style) {
            // If this style is a system style, then get cells.
            if ($style['type'] == 'system') {
                $cells = db_items(
                    "SELECT
                        area,
                        `row`, # Backticks for reserved word.
                        col,
                        region_type,
                        region_name
                    FROM system_style_cells
                    WHERE style_id = '" . $style['id'] . "'");

                $styles[$key]['cells'] = $cells;
            }
        }

        $response = array(
            'status' => 'success',
            'styles' => $styles);

        echo encode_json($response);
        exit();
        
        break;

    case 'test':
        $response = array('status' => 'success');

        echo encode_json($response);
        exit();

        break;

    case 'update_designer_region':
        validate_token();

        $designer_region = db_item(
            "SELECT cregion_id AS id
            FROM cregion
            WHERE
                (cregion_designer_type = 'yes')
                AND (cregion_id = '" . escape($request['designer_region']['id']) . "')");
        
        // If a designer region was not found then respond with an error.
        if (!$designer_region) {
            $response = array(
                'status' => 'error',
                'message' => 'Designer Region could not be found.');

            echo encode_json($response);
            exit();
        }

        db(
            "UPDATE cregion
            SET
                cregion_content = '" . escape($request['designer_region']['content']) . "',
                cregion_timestamp = UNIX_TIMESTAMP(),
                cregion_user = '" . USER_ID . "'
            WHERE cregion_id = '" . escape($request['designer_region']['id']) . "'");

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    case 'update_dynamic_region':
        validate_token();
        
        $dynamic_region = db_item(
            "SELECT dregion_id AS id
            FROM dregion
            WHERE dregion_id = '" . escape($request['dynamic_region']['id']) . "'");
        
        // If a dynamic region was not found then respond with an error.
        if (!$dynamic_region) {
            $response = array(
                'status' => 'error',
                'message' => 'Dynamic Region could not be found.');

            echo encode_json($response);
            exit();
        }

        db(
            "UPDATE dregion
            SET
                dregion_code = '" . escape($request['dynamic_region']['content']) . "',
                dregion_timestamp = UNIX_TIMESTAMP(),
                dregion_user = '" . USER_ID . "'
            WHERE dregion_id = '" . escape($request['dynamic_region']['id']) . "'");

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    case 'update_file':
        validate_token();
        
        $file = db_item(
            "SELECT
                id,
                name
            FROM files
            WHERE id = '" . escape($request['file']['id']) . "'");
        
        // If a file was not found then respond with an error.
        if (!$file) {
            $response = array(
                'status' => 'error',
                'message' => 'File could not be found.');

            echo encode_json($response);
            exit();
        }

        unlink(FILE_DIRECTORY_PATH . '/' . $file['name']);

        file_put_contents(FILE_DIRECTORY_PATH . '/' . $file['name'], $request['file']['content']);

        db(
            "UPDATE files
            SET
                timestamp = UNIX_TIMESTAMP(),
                user = '" . USER_ID . "'
            WHERE id = '" . escape($request['file']['id']) . "'");

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    case 'update_layout':
        validate_token();

        $layout = db_item(
            "SELECT
                page_id AS id,
                page_name AS name
            FROM page
            WHERE page_id = '" . e($request['layout']['id']) . "'");
        
        // If a layout was not found then respond with an error.
        if (!$layout) {
            $response = array(
                'status' => 'error',
                'message' => 'Layout could not be found.');

            echo encode_json($response);
            exit();
        }

        require_once(dirname(__FILE__) . '/generate_layout_content.php');

        // If the saved layout matches the generated layout, then mark
        // that the layout has not been modified and delete layout file.
        // We strip white-spaces, because we had issues where possibly
        // new lines characters were different in the generated content from
        // the codemirror content.
        if (preg_replace('/\s+/', '', $request['layout']['content']) === preg_replace('/\s+/', '', generate_layout_content($request['layout']['id']))) {
            // If a layout file exists, then delete it.
            if (file_exists(LAYOUT_DIRECTORY_PATH . '/' . $layout['id'] . '.php')) {
                unlink(LAYOUT_DIRECTORY_PATH . '/' . $layout['id'] . '.php');
            }
            
            $modified = 0;

        // Otherwise the layout content is unique, so save content to file system.
        } else {
            @file_put_contents(LAYOUT_DIRECTORY_PATH . '/' . $layout['id'] . '.php', $request['layout']['content']);

            $modified = 1;
        }

        // Update the page to mark whether the layout has been modified or not,
        // so that we don't auto-generate the layout anymore when changes are made to the form.
        db(
            "UPDATE page
            SET
                layout_modified = '$modified',
                page_user = '" . USER_ID . "',
                page_timestamp = UNIX_TIMESTAMP()
            WHERE page_id = '" . e($request['layout']['id']) . "'");

        log_activity('layout for page (' . $layout['name'] . ') was modified');

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    // Update shipping & tracking info for completed order.

    case 'update_order':

        validate_token();

        require_once(dirname(__FILE__) . '/update_order.php');

        $response = update_order(array('order' => $request['order']));

        echo encode_json($response);
        exit();
        
        break;

    case 'update_page_designer_properties':
    
        validate_token();

        $_SESSION['software']['page_designer']['preview_panel_width'] = $request['preview_panel_width'];
        $_SESSION['software']['page_designer']['code_panel_width'] = $request['code_panel_width'];
        $_SESSION['software']['page_designer']['tool_panel_width'] = $request['tool_panel_width'];
        $_SESSION['software']['page_designer']['cursor_positions'] = $request['cursor_positions'];
        $_SESSION['software']['page_designer']['query'] = $request['query'];

        respond(array('status' => 'success'));
        
        break;

    case 'update_product_status':

        validate_token();

        $user = validate_user();
        validate_ecommerce_access($user);

        if ($request['status'] == 'enabled') {
            $enabled = 1;
        } else {
            $enabled = 0;
        }

        db(
            "UPDATE products
            SET
                enabled = '$enabled',
                user = '" . USER_ID . "',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . e($request['id']) . "'");

        $product = db_item(
            "SELECT name, short_description
            FROM products
            WHERE id = '" . e($request['id']) . "'");

        log_activity('product (' . $product['name'] . ' - ' . $product['short_description'] . ') was ' . $request['status']);

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    case 'update_product_group_status':

        validate_token();

        $user = validate_user();
        validate_ecommerce_access($user);

        require_once(dirname(__FILE__) . '/update_product_group_status.php');

        $items = update_product_group_status(array(
            'id' => $request['id'],
            'status' => $request['status']));

        $response = array(
            'status' => 'success',
            'items' => $items);

        echo encode_json($response);
        exit();
        
        break;

    case 'update_style':
        validate_token();
        
        $style = db_item(
            "SELECT style_id AS id
            FROM style
            WHERE style_id = '" . escape($request['style']['id']) . "'");
        
        // If a style was not found then respond with an error.
        if (!$style) {
            $response = array(
                'status' => 'error',
                'message' => 'Style could not be found.');

            echo encode_json($response);
            exit();
        }

        db(
            "UPDATE style
            SET
                style_code = '" . escape($request['style']['code']) . "',
                style_timestamp = UNIX_TIMESTAMP(),
                style_user = '" . USER_ID . "'
            WHERE style_id = '" . escape($request['style']['id']) . "'");

        $response = array('status' => 'success');

        echo encode_json($response);
        exit();
        
        break;

    case 'update_toolbar_properties':
        validate_token();

        $_SESSION['software']['toolbar_enabled'] = $request['enabled'];

        respond(array('status' => 'success'));
        
        break;

    default:
        $response = array(
            'status' => 'error',
            'message' => 'Invalid action.');

        echo encode_json($response);
        exit();

        break;
}

function respond($response) {
    echo encode_json($response);
    exit;
}

// A token is required to be passed in the request for session login requests
// that update an item.
function validate_token() {

    global $token;

    // If the user passed a username and password in this request
    // and did not login via a session, then token validation is not
    // necessary, so return true.
    if (defined('API_USERNAME')) {
        return true;
    }

    // If the token does not exist in the session,
    // or the passed token does not match the token from the session,
    // then this might be a CSRF attack so respond with an error.
    if (
        ($_SESSION['software']['token'] == '')
        || ($token != $_SESSION['software']['token'])
    ) {
        respond(array(
            'status' => 'error',
            'message' => 'Invalid token.'));
    }
}