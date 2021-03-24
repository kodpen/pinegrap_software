
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-sm-2">
    <div class="form-group">
        <label for="ship_to_name">&#304;sme Nakliye*</label>
        <input type="text" name="ship_to_name" id="ship_to_name">
    </div>
    </div>
    
    <div class="col-sm-2">
    <div class="form-group">
        <label for="salutation">Ünvan</label>
        <div class="select-option">
		<i class="ti-angle-down"></i>
        	<select name="salutation" id="salutation"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="first_name">&#304;sim*</label>
        <input type="text" name="first_name" id="first_name">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="last_name">Soyisim*</label>
        <input type="text" name="last_name" id="last_name">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="company">Organizasyon</label>
        <input type="text" name="company" id="company">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="address_1">Adres 1*</label>
        <input type="text" name="address_1" id="address_1">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="address_2">Adres 2</label>
        <input type="text" name="address_2" id="address_2">
    </div>
    </div>
        
    <div class="col-sm-4">    
    <div class="form-group">
        <label for="city">&#304;l&ccedil;e/Semt*</label>
        <input type="text" name="city" id="city">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="state_text_box">&#304;l</label>
        <input type="text" name="state" id="state_text_box">
        <label for="state_pick_list" style="display: none">&#304;l*</label>
        <div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="state" id="state_pick_list" style="display: none"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="zip_code">Posta Kodu*</label>
        <input type="text" name="zip_code" id="zip_code">
    </div>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="country">&Uuml;lke*</label>
        <div class="select-option">
			<i class="ti-angle-down"></i>
        	<select name="country" id="country"></select>
        </div>
    </div>
    </div>
        
    <div class="col-sm-4">
    <?php if ($address_type): ?>

       <div>
            <label>Adres Tipi*
            <?php if ($address_type_url): ?>
                &nbsp;<a href="<?=h($address_type_url)?>" target="_blank">Bu nedir?</a>
            <?php endif ?>
           </label>
        </div>
   
        <div>
            <div class="radio-option">
                <div class="inner"></div>
                    <input type="radio" name="address_type" value="residential" checked="checked">
                    <span>Ev</span>
        	</div>
        </div>
        
        <div>
        	<div class="radio-option">
            	<div class="inner"></div>
                	<input type="radio" name="address_type" value="business">
                	<span>&#304;&#351;yeri</span>
        	</div>
    	</div>

    <?php endif ?>
   	</div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="phone_number">Telefon</label> (teslimat sorunlar&#305; i&ccedil;in)
        <input type="tel" name="phone_number" id="phone_number">
    </div>
    </div>
    
    <div class="col-sm-12">
    	<button type="submit" class="btn btn-primary">G&uuml;ncelle</button>
    	<a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">&#304;ptal</a>
    </div>
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
    </div>
</form>
