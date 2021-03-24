
<?=$messages?>

<form <?=$attributes?>>

    <div class="row">
        
    <div class="col-sm-12">
    	<h4>&#304;leti&#351;im Bilgileri</h4>
    </div>
    
    <div class="col-sm-4">        
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
        <label for="suffix">Ek</label>
        <div class="select-option">
            <i class="ti-angle-down"></i>
        	<select name="suffix" id="suffix"></select>
        </div>
    </div>
    </div>

	<div class="col-sm-4">
    <div class="form-group">
        <label for="title">Tan&#305;m</label>
        <input type="text" name="title" id="title">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_phone">İş Telefonu</label>
        <input type="tel" name="business_phone" id="business_phone">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="mobile_phone">Cep Telefonu</label>
        <input type="tel" name="mobile_phone" id="mobile_phone">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="home_phone">Ev Telefonu</label>
        <input type="tel" name="home_phone" id="home_phone">
    </div>
    </div>
        
    <div class="col-sm-4">
   	<div class="form-group">
        <label for="business_fax">Faks</label>
        <input type="tel" name="business_fax" id="business_fax">
    </div>
   	</div>
        
    <?php if ($timezone): ?>
        <div class="col-sm-12">
        <div class="form-group">
            <label for="timezone">Zaman dilimi</label>
        	<div class="select-option">
            	<i class="ti-angle-down"></i>
            	<select name="timezone" id="timezone"></select>
            </div>
        </div>
        </div>
    <?php endif ?>
        
    </div>
      
    <div class="row mt40">
        
    <div class="col-sm-12">
    <h4>Fatura / Posta Adresi</h4>
    </div>
        
    <div class="col-sm-4">
    <div class="form-group">
        <label for="company">Şirket</label>
        <input type="text" name="company" id="company">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_address_1">Adres 1</label>
        <input type="text" name="business_address_1" id="business_address_1">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_address_2">Adres 2</label>
        <input type="text" name="business_address_2" id="business_address_2">
    </div>
    </div>
    
    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_city">&#304;l&ccedil;e/Semt</label>
        <input type="text" name="business_city" id="business_city">
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_state_text_box">&#304;l</label>
        <input type="text" name="business_state" id="business_state_text_box">
        
        <label for="business_state_pick_list" style="display: none">&#304;l</label>
        <div class="select-option">
            <i class="ti-angle-down"></i>
        	<select name="business_state" id="business_state_pick_list" style="display: none"></select>
        </div>
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="business_zip_code">Posta Kodu</label>
        <input type="text" name="business_zip_code" id="business_zip_code">
    </div>
    </div>

    <div class="col-sm-12">
    <div class="form-group">
        <label for="business_country">&Uuml;lke</label>
        <div class="select-option">
            <i class="ti-angle-down"></i>
        	<select name="business_country" id="business_country"></select>
        </div>
    </div>
    </div>

    <div class="col-sm-12">
    	<button type="submit" class="btn btn-primary">G&uuml;ncelle</button>
		<a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">&#304;ptal</a>
    </div>
        
    </div>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
