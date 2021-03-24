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
validate_area_access($user, 'manager');

include_once('liveform.class.php');
$liveform = new liveform('backups');



//Backup Files Directory
function backup_list($directory){ 
	$directory_path = 'install/backups/';
    foreach(array_diff(scandir($directory_path),array('..','.' )) as $backup_folder)if(is_dir($directory_path.'/'.$backup_folder))$l[]=$backup_folder; 
    return $l; 
}

$directory = backup_list(getcwd()); 
if($directory){
	foreach($directory as $backup_folder) {
		$backup_value .= '
			<li id="backup_folder_'.$backup_folder.'">
				<span><button type="submit" name="download_selected" value="Download '.$backup_folder.'" class="button submit-primary" title="Download"><img  src="images/download.svg" width="15" height="auto" border="0" class="" alt=""></button></span>
				<span><button type="submit" name="delete_selected" value="Delete '.$backup_folder.'" class="button delete" title="Delete" onclick="return confirm(\'WARNING: This Backup will be permanently deleted.\')"><img  src="images/delete.svg" width="15" height="auto" border="0" class="" alt=""></button></span>
				
				<span class="folder">'.$backup_folder.'
				<span style="display:none" class="backup_folder_extras"><time title="Last Modified">'.date('d F Y', filectime('install/backups/'.$backup_folder)).'</time></span>
				</span>
				
				
			</li>';
	}  
	$backup_values = '
	<style>
		.folder {font-size: 15px;margin:auto 15px;cursor:help}
		.folder .backup_folder_extras{
			color: #2e7d32;font-size: 9px;
		}

		.folder:hover .backup_folder_extras{display:contents !important;}

		
	</style>
	<ul style="padding:20px;max-width:100%;overflow:auto;max-height:300px; ">
		'.$backup_value.'
	</ul>';
	   
}else{
	$backup_values ='<p class="no-backup-output">There is No Backup</p>';
}

$mailchimp_settings = '';
if (ECOMMERCE) {
    $mailchimp_settings = '<td><ul><li><a href="mailchimp_settings.php">MailChimp Settings</a></li></ul></td>';
}

if (!$_POST) {
	$terminal_output ='';
	if(is_file(dirname(__FILE__) . '/../terminal.php')){
        if (USER_ROLE < 1) {
           $terminal_output ='
			<td>    
				<ul>
			        <li><a href="../terminal.php"> Terminal</a></li>
			    </ul>
			</td>
		';
        }
    
		
	}
    $output = 
	output_header() . '
	    <script>
        function backup(){
            $status = "";
            var backup_btn = $("#backup");
            var Progress = $("progress");
			var LogBox = $(".logbox");
			var backup_folder_name = $("#backup_folder_name").val();
			backup_btn.text("Backing up...").addClass("disabled").removeClass("ready");
			
            Progress.removeAttr("value").removeClass("hide");
			LogBox.empty().append("Backup Builder Starting...").addClass("software_notice").removeClass("software_error");
			
            // Use AJAX to get various card info.
            $.ajax({
                contentType: "application/json",
                url: "api.php",
                data: JSON.stringify({
                    action: "software_backup",
                    token:software_token ,
					step: "create_backup_folder",
					backup_name: backup_folder_name
                }),
                type: "POST",
                success: function(response) {
                    // Check the values in console
                    $status = response.status;
                    if($status == "success"){
                        Progress.attr("value","15");
						LogBox.empty().append(response.message).addClass("software_notice");
						var backup_name = response.backup_name;
						// Use AJAX to get various card info.
            			$.ajax({
            			    contentType: "application/json",
            			    url: "api.php",
            			    data: JSON.stringify({
            			        action: "software_backup",
            			        token:software_token ,
            			        step: "create_mysql_dumb",
								backup_name: backup_name
            			    }),
            			    type: "POST",
            			    success: function(response) {
            			        // Check the values in console
            			        $status = response.status;
            			        if($status == "success"){
            			            Progress.attr("value","30");
            			            LogBox.empty().append(response.message).addClass("software_notice");
									backup_name = response.backup_name;
									// Use AJAX to get various card info.
            						$.ajax({
            						    contentType: "application/json",
            						    url: "api.php",
            						    data: JSON.stringify({
            						        action: "software_backup",
            						        token:software_token ,
            						        step: "clear_files_and_layouts",
											backup_name: backup_name
            						    }),
            						    type: "POST",
            						    success: function(response) {
            						        // Check the values in console
            						        $status = response.status;
            						        if($status == "success"){
            						            Progress.attr("value","45");
            						            LogBox.empty().append(response.message).addClass("software_notice");
												backup_name = response.backup_name;
												// Use AJAX to get various card info.
            									$.ajax({
            									    contentType: "application/json",
            									    url: "api.php",
            									    data: JSON.stringify({
            									        action: "software_backup",
            									        token:software_token ,
            									        step: "move_files",
														backup_name: backup_name
            									    }),
            									    type: "POST",
            									    success: function(response) {
            									        // Check the values in console
            									        $status = response.status;
            									        if($status == "success"){
            									            Progress.attr("value","60");
            									            LogBox.empty().append(response.message).addClass("software_notice");
															backup_name = response.backup_name;
															// Use AJAX to get various card info.
															$.ajax({
																contentType: "application/json",
																url: "api.php",
																data: JSON.stringify({
																	action: "software_backup",
																	token:software_token ,
																	step: "move_layouts",
																	backup_name: backup_name
																}),
																type: "POST",
																success: function(response) {
																	// Check the values in console
																	$status = response.status;
																	if($status == "success"){
																		Progress.attr("value","75");
																		LogBox.empty().append(response.message).addClass("software_notice");
																		backup_name = response.backup_name;
																		// Use AJAX to get various card info.
																		$.ajax({
																			contentType: "application/json",
																			url: "api.php",
																			data: JSON.stringify({
																				action: "software_backup",
																				token:software_token ,
																				step: "create_htaccess",
																				backup_name: backup_name
																			}),
																			type: "POST",
																			success: function(response) {
																				// Check the values in console
																				$status = response.status;
																				if($status == "success"){
																					Progress.attr("value","90");
																					LogBox.empty().append(response.message).addClass("software_notice");
																					backup_name = response.backup_name;
																					// Use AJAX to get various card info.
																					$.ajax({
																						contentType: "application/json",
																						url: "api.php",
																						data: JSON.stringify({
																							action: "software_backup",
																							token:software_token ,
																							step: "check",
																							backup_name: backup_name
																						}),
																						type: "POST",
																						success: function(response) {
																							// Check the values in console
																							$status = response.status;
																							if($status == "success"){
																								Progress.attr("value","100");
																								LogBox.empty().append(response.message).addClass("software_notice");
																								
																								location.reload();

																							}else{
																								Progress.attr("value","0");
																								LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
																								backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
																							}
																						}
																					});
									
																					
																				}else{
																					Progress.attr("value","0");
																					LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
																					backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
																				}
																			}
																		});
						
																	}else{
																		Progress.attr("value","0");
																		LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
																		backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
																	}
																}
															});
			

            									        }else{
            									            Progress.attr("value","0");
            									            LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
            									            backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
            									        }
            									    }
												});

            						        }else{
            						            Progress.attr("value","0");
            						            LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
            						            backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
            						        }
            						    }
									});

            			        }else{
            			            Progress.attr("value","0");
            			            LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
            			            backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
            			        }
            			    }
						});
						
                    }else{
                        Progress.attr("value","0");
                        LogBox.empty().append(response.message).addClass("software_error").removeClass("software_notice");
                        backup_btn.text("Retry Backup").removeClass("disabled").addClass("ready");
                    }
                }
			});
			
        }
        $(function(){
            $("#backup").click(function(){
                if ($(this).hasClass("ready")) {
                    backup();
                }
            });
        });
    </script>
	<div id="subnav">
		<table>
		    <tbody>
		        <tr>     
		            <td>    
		                <ul>
		                    <li><a href="settings.php">Site Settings</a></li>
		                </ul>
		            </td> 
		            ' . $mailchimp_settings . '
		            <td>    
		                <ul>
		                    <li><a href="smtp_settings.php">SMTP Settings</a></li>
		                </ul>
		            </td>
					<td>    
						<ul>
							<li><a href="backups.php">Backup Manager</a></li>
						</ul>
					</td>
					<td>
						<ul>
						<li><a href="view_log.php" >Site Log</a></li>
						</ul>
					</td>
					'. $terminal_output .'
		        </tr>
		    </tbody>
		</table>
	</div>
	<div id="content">
		' . $liveform->output_errors() . '
		' . $liveform->output_notices() . '
		<a href="#" id="help_link">Help</a>
		<h1>Site Backup Manager</h1>
		<div class="subheading">Add, Edit, Delete or Download Website Backups.</div>

		<form name="form" action="backups.php" method="post" style="margin: 0px" autocomplete="off">
			' . get_token_field() . '
			<h2>Backups</h2>
			<div style="margin-top: 2em;" id="folder_tree">
				'.$backup_values.'
			</div>
			<h2>Create or Update Backup</h2>
			<table>
				<tr>
					<td>Backup Name:</td>
					<td>' . $liveform->output_field(array('type'=>'text','id'=>'backup_folder_name', 'name'=>'backup_folder_name', 'size'=>'30')) . '</td>
				
				</tr>
			</table>
			
            <progress  style="width:100%;" class="hide" max="100" value="0"></progress><br/>
			<div class="logbox"></div>
			
			<div class="buttons">
				<a href="#!" id="backup"class="submit-primary ready">Backup</a>&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
			<script>
				var input = document.getElementById("backup_folder_name");
				input.addEventListener("keyup", function(event) {
				  if (event.keyCode === 13) {
				   event.preventDefault();
				   document.getElementById("backup").click();
				  }
				});
			</script>
			</div>
		</form>
	</div>

	' .output_footer();
	print $output;
	$liveform->remove_form('backups');
}else{
	validate_token_field();
	$liveform->add_fields_to_session();
	$backup_location ='install/backups/';


	//Find if download or delete button selected
	foreach($directory as $backup_folder) {
		if ($_POST['download_selected'] == 'Download '.$backup_folder) {
			$dir = $backup_location.$backup_folder;
			$zip_file = $backup_location.$backup_folder.'/'.$backup_folder.'.zip';

			unlink($zip_file);
			
			// Get real path for our folder
			$rootPath = realpath($dir);
			
			// Initialize archive object
			$zip = new ZipArchive();
			$zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
			
			// Create recursive directory iterator
			/** @var SplFileInfo[] $files */
			$files = new RecursiveIteratorIterator(
			    new RecursiveDirectoryIterator($rootPath),
			    RecursiveIteratorIterator::LEAVES_ONLY
			);
			
			foreach ($files as $name => $file)
			{
			    // Skip directories (they would be added automatically)
			    if (!$file->isDir())
			    {
			        // Get real and relative path for current file
			        $filePath = $file->getRealPath();
			        $relativePath = substr($filePath, strlen($rootPath) + 1);
			
			        // Add current file to archive
			        $zip->addFile($filePath, $relativePath);
			    }
			}
			
			// Zip archive will be created only after closing object
			$zip->close();
			
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($zip_file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($zip_file));
			readfile($zip_file);
		}

		if ($_POST['delete_selected'] == 'Delete '.$backup_folder) {
			//delete backup directory
			$delete_backup = $backup_folder;
			$dir = $backup_location.$delete_backup;
			$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it,
			         RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
			    if ($file->isDir()){
			        rmdir($file->getRealPath());
			    } else {
			        unlink($file->getRealPath());
			    }
			}
			rmdir($dir);
			//Success Delete Backup so add notice and  log
			$liveform->add_notice( $backup_folder.' is deleted successful.');
			log_activity('Backup: '.$backup_folder.' is deleted successful', $_SESSION['sessionusername']);
		}
	}
	// forward user back to backups screen
	header('Location: http://' . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/backups.php');
}


