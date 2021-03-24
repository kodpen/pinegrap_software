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
// Validate the users access
validate_area_access($user, 'manager');
$page_name = initialize_developer_security();
include_once('liveform.class.php');

$liveform = new liveform('developer_lock');

$SUser = $_SESSION['sessionusername'];

// if the form has not been submitted
if (!$_POST) {
    $query =
    "SELECT
        user_id,
        user_username,
        contacts.first_name
    FROM user
    LEFT JOIN contacts ON user.user_contact = contacts.id
    WHERE user_username = '" . $SUser . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$id = $row['user_id'];
$username = $row['user_username'];
$first_name = trim($row['first_name']);

$firstname_or_username ='';
if (!$first_name){
    $firstname_or_username = $username;
}else {
    $firstname_or_username = $first_name;
}
    print
        output_header() . '
		
        <div id="subnav"></div>
        <div id="content">
          
            <form name="form" action="developer_lock.php" method="post" style="margin-bottom: 1em">
                ' . get_token_field() . '
                <div style="text-align:center;margin-top:100px;" >
				
					<i class="material-icons">dialpad</i><br/>
					<h3 style="margin:20px;"><span class="minitext">Hi,</span> '.$firstname_or_username.',<br/> <strong>'.$page_name.'</strong> locked by a developer.</h4>
				
					<div style="width:300px;max-width:100% ;margin: auto;">
						' . $liveform->output_errors() . '
						' . $liveform->output_notices() . '
					</div>
                    <div style="margin:20px 0;">
						 <h3>Enter Pin Code:</h3>					
						<input type="password" style="width:30px;text-align: center;font-size:18px" id="pin1" maxlength="1" size="1" pattern="[0-9]*" inputmode="numeric"/>
						<input type="password" style="width:30px;text-align: center;font-size:18px" id="pin2" maxlength="1" size="1" pattern="[0-9]*" inputmode="numeric"/>
						<input type="password" style="width:30px;text-align: center;font-size:18px" id="pin3" maxlength="1" size="1" pattern="[0-9]*" inputmode="numeric"/>
						<input type="password" style="width:30px;text-align: center;font-size:18px" id="pin4" maxlength="1" size="1" pattern="[0-9]*" inputmode="numeric"/>
						<input type="password" id="pin"	name="pin" style="display:none"  />
						<br/><br/>
						<div class="alert" style="    color: red;"></div>
                    </div>
                   
                </div>




				
				<script>
					// Restricts input for each element in the set of matched elements to the given inputFilter.
					(function($) {
					  $.fn.inputFilter = function(inputFilter) {
					    return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
					      if (inputFilter(this.value)) {
					        this.oldValue = this.value;
					        this.oldSelectionStart = this.selectionStart;
					        this.oldSelectionEnd = this.selectionEnd;
					      } else if (this.hasOwnProperty("oldValue")) {
					        this.value = this.oldValue;
					        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
							
					      }
					    });
					  };
					}(jQuery));
					
					// Restrict input to digits by using a regular expression filter.
					$("input").inputFilter(function(value) {
					  return /^\d*$/.test(value);
					});

					$("input").on("input",function(){
						if($(this).val()){
							$(this).next().focus();
						
						}
						var a = $("input#pin1").val();
						var b = $("input#pin2").val();
						var c = $("input#pin3").val();
						var d = $("input#pin4").val();
						$("input#pin").val(a+b+c+d);
						if($("input#pin4").val()){
							$("form").submit();
						}
					});
				</script>


			
            </form>
        </div>
        ' . output_footer();
    $liveform->remove_form();

// else the form has been submitted
} else {
    validate_token_field();
    // Add liveform notices
    include_once('liveform.class.php');
    $liveform_settings = new liveform('developer_lock');
    // Initialize variables and strip single quotes
    $pin = str_replace("'", '', $_POST['pin']);
    if (DEVELOPER_PIN == $pin) {
        // update user
        $query =
            "UPDATE
            user
            SET
            user_devpasspin = '" . $pin . "'
            WHERE user_username = '" . $SUser . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        log_activity("Unlock Pin correct for access to $page_name. Page and other developer locked pages accessable for user", $_SESSION['sessionusername']);
        // Redirect user back here.
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/'.$page_name);

    }else{
        $error = 'Unlock pin is not correct';
        // Add notice to liveform.
        $liveform_settings->mark_error('error',$error);

        log_activity("Unlock Pin is not correct for access to $page_name", $_SESSION['sessionusername']);
        // Redirect user back here.
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/developer_lock.php');
    }
}
?>