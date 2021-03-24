
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-sm-2">
    <div class="form-group">
        <label for="ship_to_name">Ship to Name*</label>
        <input type="text" name="ship_to_name" id="ship_to_name">
    </div>
    </div>
    
    <div class="col-sm-2">
    <div class="form-group">
        <label for="salutation">Salutation</label>
        <div class="select-option">
		<i class="ti-angle-down"></i>
        	<select name="salutation" id="salutation"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="first_name">First Name*</label>
        <input type="text" name="first_name" id="first_name">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="last_name">Last Name*</label>
        <input type="text" name="last_name" id="last_name">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="company">Organization</label>
        <input type="text" name="company" id="company">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="address_1">Address 1*</label>
        <input type="text" name="address_1" id="address_1">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="address_2">Address 2</label>
        <input type="text" name="address_2" id="address_2">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="city">City*</label>
        <input type="text" name="city" id="city">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="state_text_box">State / Province</label>
        <input type="text" name="state" id="state_text_box">
        <label for="state_pick_list" style="display: none">State / Province*</label>
        <div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="state" id="state_pick_list" style="display: none"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="zip_code">Zip / Postal Code*</label>
        <input type="text" name="zip_code" id="zip_code">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="country">Country*</label>
        <div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="country" id="country"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <?php if ($address_type): ?>

       <div>
            <label>Address Type*
            <?php if ($address_type_url): ?>
                &nbsp;<a href="<?=h($address_type_url)?>" target="_blank">What is this?</a>
            <?php endif ?>
           </label>
        </div>
   
        <div>
            <div class="radio-option">
                <div class="inner"></div>
                    <input type="radio" name="address_type" value="residential">
                    <span>Residential</span>
        	</div>
        </div>
        
        <div>
        	<div class="radio-option">
            	<div class="inner"></div>
                	<input type="radio" name="address_type" value="business">
                	<span>Business</span>
        	</div>
    	</div>

    <?php endif ?>
   	</div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="phone_number">Phone</label> (if delivery issues)
        <input type="tel" name="phone_number" id="phone_number">
    </div>
    </div>
    
    <div class="col-sm-12">
    	<button type="submit" class="btn btn-primary">Update</button>
    	<a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">Cancel</a>
    </div>
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
    </div>
</form>
