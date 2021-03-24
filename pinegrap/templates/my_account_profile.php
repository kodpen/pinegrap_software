
<?=$messages?>

<form <?=$attributes?>>

    <h2>Contact Information</h2>

    <div class="form-group">
        <label for="salutation">Salutation</label>
        <select name="salutation" id="salutation" class="form-control"></select>
    </div>

    <div class="form-group">
        <label for="first_name">First Name*</label>
        <input type="text" name="first_name" id="first_name" class="form-control">
    </div>

    <div class="form-group">
        <label for="last_name">Last Name*</label>
        <input type="text" name="last_name" id="last_name" class="form-control">
    </div>

    <div class="form-group">
        <label for="suffix">Suffix</label>
        <select name="suffix" id="suffix" class="form-control"></select>
    </div>

    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_phone">Main Phone</label>
        <input type="tel" name="business_phone" id="business_phone" class="form-control">
    </div>

    <div class="form-group">
        <label for="mobile_phone">Mobile Phone</label>
        <input type="tel" name="mobile_phone" id="mobile_phone" class="form-control">
    </div>

    <div class="form-group">
        <label for="home_phone">Home Phone</label>
        <input type="tel" name="home_phone" id="home_phone" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_fax">Fax</label>
        <input type="tel" name="business_fax" id="business_fax" class="form-control">
    </div>

    <h2>Billing / Mailing Address</h2>

    <div class="form-group">
        <label for="company">Organization</label>
        <input type="text" name="company" id="company" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_address_1">Address 1</label>
        <input type="text" name="business_address_1" id="business_address_1" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_address_2">Address 2</label>
        <input type="text" name="business_address_2" id="business_address_2" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_city">City</label>
        <input type="text" name="business_city" id="business_city" class="form-control">
    </div>

    <div class="form-group">
        <label for="business_country">Country</label>
        <select name="business_country" id="business_country" class="form-control"></select>
    </div>

    <div class="form-group">
        <label for="business_state_text_box">State / Province</label>
        <input type="text" name="business_state" id="business_state_text_box" class="form-control">
        
        <label for="business_state_pick_list" style="display: none">State / Province</label>
        <select name="business_state" id="business_state_pick_list" class="form-control" style="display: none"></select>
    </div>

    <div class="form-group">
        <label for="business_zip_code">Zip / Postal Code</label>
        <input type="text" name="business_zip_code" id="business_zip_code" class="form-control">
    </div>

    <?php if ($timezone): ?>
        <div class="form-group">
            <label for="timezone">Timezone</label>
            <select name="timezone" id="timezone" class="form-control"></select>
        </div>
    <?php endif ?>

    <button type="submit" class="btn btn-primary">Update</button>

    <a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">Cancel</a>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
