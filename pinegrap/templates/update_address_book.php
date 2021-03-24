
<?=$messages?>

<form <?=$attributes?>>

    <div class="form-group">
        <label for="ship_to_name">Ship to Name*</label>
        <input type="text" name="ship_to_name" id="ship_to_name" class="form-control">
    </div>

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
        <label for="company">Organization</label>
        <input type="text" name="company" id="company" class="form-control">
    </div>

    <div class="form-group">
        <label for="address_1">Address 1*</label>
        <input type="text" name="address_1" id="address_1" class="form-control">
    </div>

    <div class="form-group">
        <label for="address_2">Address 2</label>
        <input type="text" name="address_2" id="address_2" class="form-control">
    </div>

    <div class="form-group">
        <label for="city">City*</label>
        <input type="text" name="city" id="city" class="form-control">
    </div>

    <div class="form-group">
        <label for="country">Country*</label>
        <select name="country" id="country" class="form-control"></select>
    </div>

    <div class="form-group">
        <label for="state_text_box">State / Province</label>
        <input type="text" name="state" id="state_text_box" class="form-control">
        
        <label for="state_pick_list" style="display: none">State / Province*</label>
        <select name="state" id="state_pick_list" class="form-control" style="display: none"></select>
    </div>

    <div class="form-group">
        <label for="zip_code">Zip / Postal Code<span id="zip_code_required" style="display: none">*</span></label>
        <input type="text" name="zip_code" id="zip_code" class="form-control">
    </div>

    <?php if ($address_type): ?>

        <div>
            Address Type*
            <?php if ($address_type_url): ?>
                &nbsp;<a href="<?=h($address_type_url)?>" target="_blank">What is this?</a>
            <?php endif ?>
        </div>

        <div class="radio">
            <label>
                <input type="radio" name="address_type" value="residential">
                Residential
            </label>
        </div>

        <div class="radio">
            <label>
                <input type="radio" name="address_type" value="business">
                Business
            </label>
        </div>

    <?php endif ?>

    <div class="form-group">
        <label for="phone_number">Phone</label>
        <input type="tel" name="phone_number" id="phone_number" class="form-control">
    </div>
    
    <button type="submit" class="btn btn-primary">Update</button>

    <a href="<?=h($my_account_url)?>" class="btn btn-default btn-secondary">Cancel</a>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
