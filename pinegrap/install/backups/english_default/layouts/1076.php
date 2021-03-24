
<?=$messages?>

<form <?=$attributes?>>

    <?php if ($custom_field_1 OR $custom_field_2): ?>
   	<div class="row mb24">
        
        <?php if ($custom_field_1): ?>
    	<div class="col-sm-12"> 
        <div class="form-group">
            <label for="custom_field_1"><?=h($custom_field_1_label)?><?php if ($custom_field_1_required): ?>*<?php endif ?></label>
            <input type="text" name="custom_field_1" id="custom_field_1" class="form-control">
        </div>
        </div>
    	<?php endif ?>

    	<?php if ($custom_field_2): ?>
    	<div class="col-sm-12"> 
        <div class="form-group">
            <label for="custom_field_2"><?=h($custom_field_2_label)?><?php if ($custom_field_2_required): ?>*<?php endif ?></label>
            <input type="text" name="custom_field_2" id="custom_field_2" class="form-control">
        </div>
        </div>
    	<?php endif ?>
        
    </div>
    <?php endif ?>
    

    <div class="row">
    <div class="col-sm-2"> 
    <div class="form-group">
        <label for="billing_salutation">Salutation</label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="billing_salutation" id="billing_salutation"></select>
        </div>
    </div>
    </div>

    <div class="col-sm-3">
    <div class="form-group">
        <label for="billing_first_name">First Name*</label>
        <input type="text" name="billing_first_name" id="billing_first_name" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-3">
    <div class="form-group">
        <label for="billing_last_name">Last Name*</label>
        <input type="text" name="billing_last_name" id="billing_last_name" class="form-control">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_email_address">Email*</label>
        <input type="email" name="billing_email_address" id="billing_email_address" class="form-control">
    </div>
    </div>
        
    </div>
        
    <?php if ($opt_in): ?>
    <div class="row">
    <div class="col-sm-3 col-sm-offset-8"> 
        <div class="checkbox" style="margin-top: -12px">
            <label class="check-box">
                <input type="checkbox" name="opt_in" value="1">
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span><?=h($opt_in_label)?></span>
            </label>
        </div>
    </div>
    </div>
    <?php endif ?>
        
        

    
    <div class="row mt24">

    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_company">Company</label>
        <input type="text" name="billing_company" id="billing_company" class="form-control">
    </div>
    </div>
     
    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_address_1">Address 1*</label>
        <input type="text" name="billing_address_1" id="billing_address_1" class="form-control">
    </div>
	</div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_address_2">Address 2</label>
        <input type="text" name="billing_address_2" id="billing_address_2" class="form-control">
    </div>
	</div>
        
    </div>
        
    <div class="row">

    <div class="col-sm-4">        
    <div class="form-group">
        <label for="billing_city">City*</label>
        <input type="text" name="billing_city" id="billing_city" class="form-control">
    </div>
    </div>

    <div class="col-sm-4">        
    <div class="form-group">
        <label for="billing_state_text_box">State / Province</label>
        <input type="text" name="billing_state" id="billing_state_text_box" class="form-control">
        
        <label for="billing_state_pick_list" style="display: none">State / Province*</label>
        	<div class="select-option">
    			<i class="ti-angle-down"></i>
        		<select name="billing_state" id="billing_state_pick_list" style="display: none"></select>
        	</div>
    </div>
    </div>

    <div class="col-sm-4">        
    <div class="form-group">
        <label for="billing_zip_code">Zip / Postal Code*</label>
        <input type="text" name="billing_zip_code" id="billing_zip_code" class="form-control">
    </div>
    </div>
        
    </div>

    <div class="row">

    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_country">Country*</label>
        <div class="select-option">
    		<i class="ti-angle-down"></i>
        	<select name="billing_country" id="billing_country"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_phone_number">Phone*</label>
        <input type="tel" name="billing_phone_number" id="billing_phone_number" class="form-control">
    </div>
    </div>
        
    </div>
    
    
    <?php if ($update_contact): ?>
    	<div class="row">
    	<div class="col-sm-12">
        <div class="checkbox" style="margin-top: -12px">
            <label class="check-box">
                <input type="checkbox" name="update_contact" value="1">
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span>Update my account with this billing info</span>
            </label>
        </div>
    	</div>
    	</div>
    <?php endif ?>      
            
    <div class="row mt24 mb24">

    <?php if ($po_number): ?>
        <div class="col-sm-4">
        <div class="form-group">
            <label for="po_number">PO Number</label>
            <input type="text" name="po_number" id="po_number" class="form-control">
        </div>
        </div>
    <?php endif ?>
        
    <?php if ($referral_source): ?>
        <div class="col-sm-4">
        <div class="form-group">
            <label for="referral_source">How did you hear about us?</label>
            <div class="select-option">
    			<i class="ti-angle-down"></i>
            	<select name="referral_source" id="referral_source"></select>
                </div>
        </div>
        </div>
    <?php endif ?>        

    <?php if ($tax_exempt): ?>
        <div class="col-sm-12">
        <div class="checkbox">
            <label class="check-box">
                <input type="checkbox" name="tax_exempt" value="1">
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span><?=h($tax_exempt_label)?></span>
            </label>
        </div>
        </div>
    <?php endif ?>
        
    </div>
   

    <button type="submit" class="btn btn-primary btn-lg"><?=h($submit_button_label)?></button>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
