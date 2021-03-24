
<?=$messages?>

<form <?=$attributes?>>

    <h2>
        Shipping Address
        <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
            for <strong><?=h($ship_to_name)?></strong>
        <?php endif ?>
    </h2>

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
        <label for="company">Company</label>
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

    <?php if ($shipping_same_as_billing): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="shipping_same_as_billing" value="1">
                My billing address is the same as this shipping address.
            </label>
        </div>
    <?php endif ?>

    <?php if ($custom_shipping_form): ?>
        
        <!-- Adds edit button and grid around form when user is in edit mode -->
        <?=$edit_start?>
        
        <?php if ($custom_shipping_form_title): ?>
            <h2><?=h($custom_shipping_form_title)?></h2>
        <?php endif ?>

<?=eval('?>' . generate_form_layout_content(array('page_id' => $page_id, 'indent' => '        ')))?>
        <!-- Closes the edit grid -->
        <?=$edit_end?>

    <?php endif ?>

    <?php if ($arrival_dates): ?>

        <h2>
            Requested Arrival Date
            <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                for <strong><?=h($ship_to_name)?></strong>
            <?php endif ?>
        </h2>

        <?php foreach($arrival_dates as $arrival_date): ?>

            <div class="radio">
                <label>
                    <input type="radio" name="arrival_date" id="arrival_date_<?=$arrival_date['id']?>" value="<?=$arrival_date['id']?>">
                    <?=h($arrival_date['name'])?>
                </label>
            </div>

            <div><?=$arrival_date['description']?></div>

            <?php if ($arrival_date['custom']): ?>
                <div class="form-group">
                    <input type="text" name="custom_arrival_date_<?=$arrival_date['id']?>" id="custom_arrival_date_<?=$arrival_date['id']?>" class="form-control">
                </div>
            <?php endif ?>

        <?php endforeach ?>

    <?php endif ?>

    <button type="submit" class="btn btn-primary"><?=h($submit_button_label)?></button>
    
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>
    
</form>
