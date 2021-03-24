<!DOCTYPE html>

<?php if(language_ruler() == "en"): ?><html lang="en"><?elseif(language_ruler() == "tr"):?><html lang="tr"><?endif?>
    <head>
        <meta charset="utf-8">
        <title>Modify Products</title>
        <?=get_generator_meta_tag()?>
		<?=output_control_panel_header_includes() ?>        
        <link rel="stylesheet" type="text/css" href="<?=CONTROL_PANEL_STYLESHEET_URL?>">
        <script type="text/javascript">
            function edit_products()
            {                        
                // If the user selected an option for the enabled field then update field in the form.
                if (document.getElementById("enabled").value != "") {
                    opener.document.form.edit_enabled.value = document.getElementById("enabled").value;
                }
				// If the user selected an option for the enabled field then update field in the form.
                if (document.getElementById("change_price_method").value != "") {
					var $change_price_value = document.getElementById("change_price_method").value;
                    opener.document.form.edit_change_price_method.value = $change_price_value;
					var $price_value = document.getElementById("price_value").value;
                    opener.document.form.edit_price_value.value = $price_value;
					
					
                }
               
				
                // If shipping is enabled, then deal with allowed/disallowed zones.
                if (document.form.allowed_zones) {
                    opener.document.form.edit_allowed_zones.value = "";
                    
                    // Loop through all allowed zone checkboxes.
                    for (i = 0; i < document.form.allowed_zones.length; i++) {
                        // If zone checkbox is checked, then add zone to hidden form field on opener.
                        if (document.form.allowed_zones[i].checked == true) {
                            // If there is already zones in the list of zones, then add a comma first.
                            if (opener.document.form.edit_allowed_zones.value) {
                                opener.document.form.edit_allowed_zones.value += ",";
                            }
                          
                            opener.document.form.edit_allowed_zones.value += document.form.allowed_zones[i].value;
                        }
                    }
                    
                    opener.document.form.edit_disallowed_zones.value = "";
                    
                    // Loop through all disallowed zone checkboxes.
                    for (i = 0; i < document.form.disallowed_zones.length; i++) {
                        // If zone checkbox is checked, then add zone to hidden form field on opener.
                        if (document.form.disallowed_zones[i].checked == true) {
                            // If there is already zones in the list of zones, then add a comma first.
                            if (opener.document.form.edit_disallowed_zones.value) {
                                opener.document.form.edit_disallowed_zones.value += ",";
                            }
                            
                            opener.document.form.edit_disallowed_zones.value += document.form.disallowed_zones[i].value;
                        }
                    }
                }
				
                 
                opener.document.form.submit();
                window.close();
            }
        </script>
    </head>
    <body class="ecommerce">
        <div id="content">
            <h1>Modify Products</h1>
            <div class="subheading" style="margin-bottom: 1em">You may update the selected Products via the form below.</div>
            <form name="form">
                <table class="field" style="margin-bottom: 1em !important">
                    <tr>
                        <td>Status:</td>
                        <td>
                            <?=$liveform->output_field(array(
                                'type' => 'select',
                                'id' => 'enabled',
                                'name' => 'enabled',
                                'options' => $enabled_options))?>
                        </td>
                    </tr>
                </table>
				<table class="field" style="margin-bottom: 1em !important">
					<tr>
                        <td>Price Change Method:</td>

                        <td>
							<?=$liveform->output_field(array(
                                'type' => 'select',
                                'id' => 'change_price_method',
                                'name' => 'change_price_method',
                                'options' => $price_change_method_options,
								'onchange'=>'change_price_methodfunc()'))?>
                        </td>
						
                    </tr>

					<tr id="increase_price_row" style="visibility: hidden;">
						<td>Price Change Value (<span id="value_type"><?=h(BASE_CURRENCY_CODE)?></span>):</td>
                        <td>
							<?=$liveform->output_field(array(
                                'type' => 'number',
								'placeholder' =>'12,30',
                                'id' => 'price_value'))?>
                        </td>
                    </tr>
					
                </table>
                <?php if (ECOMMERCE_SHIPPING): ?>
                    <table style="margin-bottom: 1em">
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding-right: 2em">

                                <div style="margin-bottom: .5em; font-weight: bold">
                                    Allow Shipping Zones
                                </div>
								


                                <?php foreach($zones as $zone): ?>
                                    <?=$liveform->output_field(array(
                                        'type' => 'checkbox',
                                        'id' => 'allowed_zone_' . $zone['id'],
                                        'name' => 'allowed_zones',
                                        'value' => $zone['id'],
                                        'class' => 'checkbox'))?><label for="allowed_zone_<?=$zone['id']?>">

                                    <?=h($zone['name'])?></label><br>
                                <?php endforeach ?>

                            </td>
                            <td style="width: 50%; vertical-align: top">

                                <div style="margin-bottom: .5em; font-weight: bold">
                                    Disallow Shipping Zones
                                </div>

                                <?php foreach($zones as $zone): ?>
                                    <?=$liveform->output_field(array(
                                        'type' => 'checkbox',
                                        'id' => 'disallowed_zone_' . $zone['id'],
                                        'name' => 'disallowed_zones',
                                        'value' => $zone['id'],
                                        'class' => 'checkbox'))?><label for="disallowed_zone_<?=$zone['id']?>">
                                    <?=h($zone['name'])?></label><br>
                                <?php endforeach ?>

                            </td>
                        </tr>
                    </table>
                <?php endif ?>
                <div class="buttons">
                    <input type="button" value="Modify Products" class="submit-primary" onclick="edit_products()">
                </div>
            </form>
        </div>
		<script>
			function change_price_methodfunc(){
				var base_currency = '<?=h(BASE_CURRENCY_CODE)?>';
				var percentiles = '\u0025';
				var $increase_price_row =  document.getElementById("increase_price_row");
				var $value_type =  document.getElementById("value_type");
				var $change_price = document.getElementById("change_price_method").value;
				var $price_value_input = document.getElementById("price_value");
				if(($change_price == '0') || ($change_price == '1')){
					$value_type.innerHTML = base_currency;
					$price_value_input.placeholder='12,30';
					$increase_price_row.style.visibility = "visible";
				}
				else if(($change_price == '2') || ($change_price == '3')){
					$value_type.innerHTML = percentiles;
					$price_value_input.placeholder='30';
					$increase_price_row.style.visibility = "visible";
				}
				$price_value_input.focus(); 
			}
		</script>
    </body>
</html>