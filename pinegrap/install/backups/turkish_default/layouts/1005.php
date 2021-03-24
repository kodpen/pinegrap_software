
<?=$messages?>

<form <?=$attributes?>>

    <h4>Contact Information</h4>

    <div class="form-group">
        <label for="salutation">Salutation</label>
        <div class="select-option">
            <i class="ti-angle-down"></i>
            <select name="salutation" id="salutation"></select>
        </div>
    </div>

    <div class="form-group">
        <label for="first_name">First Name*</label>
        <input type="text" name="first_name" id="first_name">
    </div>

    <div class="form-group">
        <label for="last_name">Last Name*</label>
        <input type="text" name="last_name" id="last_name">
    </div>

    <div class="form-group">
        <label for="suffix">Suffix</label>
        <select name="suffix" id="suffix"></select>
    </div>

    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title">
    </div>

    <div class="form-group">
        <label for="business_phone">Main Phone</label>
        <input type="tel" name="business_phone" id="business_phone">
    </div>

    <div class="form-group">
        <label for="mobile_phone">Mobile Phone</label>
        <input type="tel" name="mobile_phone" id="mobile_phone">
    </div>

    <div class="form-group">
        <label for="home_phone">Home Phone</label>
        <input type="tel" name="home_phone" id="home_phone">
    </div>

    <div class="form-group">
        <label for="business_fax">Fax</label>
        <input type="tel" name="business_fax" id="business_fax">
    </div>

    <h4>Billing / Mailing Address</h4>

    <div class="form-group">
        <label for="company">Organization</label>
        <input type="text" name="company" id="company">
    </div>

    <div class="form-group">
        <label for="business_address_1">Address 1</label>
        <input type="text" name="business_address_1" id="business_address_1">
    </div>

    <div class="form-group">
        <label for="business_address_2">Address 2</label>
        <input type="text" name="business_address_2" id="business_address_2">
    </div>

    <div class="form-group">
        <label for="business_city">City</label>
        <input type="text" name="business_city" id="business_city">
    </div>

    <div class="form-group">
        <label for="business_state_text_box">State / Province</label>
        <input type="text" name="business_state" id="business_state_text_box">
        
        <label for="business_state_pick_list" style="display: none">State / Province</label>
        <select name="business_state" id="business_state_pick_list" style="display: none"></select>
    </div>

    <div class="form-group">
        <label for="business_zip_code">Zip / Postal Code</label>
        <input type="text" name="business_zip_code" id="business_zip_code">
    </div>

    <div class="form-group">
        <label for="business_country">Country</label>
        <select name="business_country" id="business_country"></select>
    </div>

    <?php if ($timezone): ?>
        <div class="form-group">
            <label for="timezone">Timezone</label>
        	<div class="select-option">
            	<i class="ti-angle-down"></i>
            	<select name="timezone" id="timezone"></select>
            </div>
        </div>
    <?php endif ?>

    <button type="submit" class="btn btn-primary">Update</button>

    <a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">Cancel</a>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
