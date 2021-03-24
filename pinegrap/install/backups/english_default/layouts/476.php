<div class="cart">
    
    <div class="row"><div class="col-sm-12"><?=$messages?></div></div>

<?php
    // If there are pending or upsell offers, then show them.
    if ($number_of_special_offers):
?>

    <h5 class="col-sm-12">
        Special Offer<?php if ($number_of_special_offers > 1): ?>s<?php endif ?>
    </h5>

    <?php
        // If there are pending offers, then start form
        if ($pending_offers):
    ?>
        <form <?=$attributes?>>
    <?php endif ?>
            

    <?php foreach($pending_offers as $offer): ?>

        <?php foreach($offer['offer_actions'] as $action): ?>
            
            <div class="form-group col-sm-12">

                <label>

                    <?=h($offer['description'])?>

                    <?php
                        // If this offer has multiple actions that add a product, then also
                        // show action name, so the customer will understand what action does.
                        if ($offer['multiple_actions']):
                    ?>
                        (<?=h($action['name'])?>)
                    <?php endif ?>

                    <?php
                        // Add some spacing between the description
                        // and the fields that follow.
                    ?>
                    &nbsp;

                </label>

            </div>

            <?php
                // If this action needs the customer to select a recipient,
                // then show recipient fields.
                if ($action['recipient']):
            ?>

                <div class="form-group col-sm-3">

                    <label for="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to">Ship to</label>

                    <div class="select-option">
                        <i class="ti-angle-down"></i>
                        <select name="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to" id="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to" class="form-control"></select>
                    </div>
                </div>

                <?php
                    // If add name is allowed for this action, then show field.
                    // Some actions require certain recipients, so the add name
                    // is not allowed in those cases.
                    if ($action['add_name']):
                ?>
                    <div class="form-group col-sm-3">
                        <label>or add name</label>
                        <input type="text" name="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_add_name" id="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_add_name" class="form-control" placeholder="or add name">
                    </div>
                <?php endif ?>

            <?php endif ?>

            <div class="form-group col-sm-3">
                <button type="submit" name="add_pending_offer_<?=$offer['id']?>_<?=$action['id']?>" class="btn btn-secondary btn-thin" style="margin-top:29px">
                    Add
                </button>
            </div>

        <?php endforeach ?>

    <?php endforeach ?>

    <?php foreach($upsell_offers as $offer): ?>

       <div class="form-group col-sm-12">

            <label>

                <?php if ($offer['upsell_message']): ?>
                    <?=h($offer['upsell_message'])?>
                <?php else: ?>
                    <?=h($offer['description'])?>
                <?php endif ?>

            </label>

        </div>

        <?php if ($offer['upsell_action_url']): ?>
            <div class="form-group col-sm-12">
                <a href="<?=h($offer['upsell_action_url'])?>" class="btn btn-secondary btn-thin" style="margin-top:29px">
                    <?=h($offer['upsell_action_button_label'])?>
                </a>
            </div>
        <?php endif ?>

    <?php endforeach ?>

    <?php
        // If there are pending offers, then close form
        if ($pending_offers):
    ?>

            <?=$pending_system // Required hidden fields (do not remove) ?>

        </form>

    <?php endif ?>

<?php endif ?>

<?php
    // If quick add is enabled, then show that area.
    if ($quick_add):
?>

    <?php if ($quick_add['label']): ?>
        <h5 class="col-sm-12"><?=h($quick_add['label'])?></h5>
    <?php endif ?>

    <form <?=$attributes?>>

        <div class="form-group col-sm-12">
            <label for="quick_add_product_id">Item</label>
            <div class="select-option">
                <i class="ti-angle-down"></i>
                <select name="quick_add_product_id" id="quick_add_product_id"></select>
            </div>
        </div>

        <?php
            // The ids on the various container rows below allows the JS to
            // dynamically show and hide rows based on the product that is selected.
        ?>

        <?php if ($quick_add['recipient']): ?>

            <div id="quick_add_ship_to_row" class="form-group col-sm-3">
                <label for="quick_add_ship_to">Ship to</label>
                <div class="select-option">
                    <i class="ti-angle-down"></i>
                    <select name="quick_add_ship_to" id="quick_add_ship_to"></select>
                </div>
            </div>

            <div id="quick_add_add_name_row" class="form-group col-sm-3">
                <label for="quick_add_add_name">or add name</label>
                <input type="text" name="quick_add_add_name" id="quick_add_add_name" class="form-control" placeholder="Example: Tom">
            </div>

        <?php endif ?>

        <?php if ($quick_add['quantity']): ?>
            <div id="quick_add_quantity_row" class="form-group col-sm-3">
                <label for="quick_add_quantity">Qty</label>
                <input type="number" name="quick_add_quantity" id="quick_add_quantity" class="form-control">
            </div>
        <?php endif ?>

        <?php if ($quick_add['amount']): ?>

            <div id="quick_add_amount_row" class="form-group col-sm-3">

                <label for="quick_add_amount">Amount</label>

                <div class="input-group">

                    <span class="input-group-addon"><?=$currency_symbol?></span>

                    <input type="number" step="any" name="quick_add_amount" id="quick_add_amount" class="form-control">

                    <?php if ($currency_code): ?>
                        <span class="input-group-addon"><?=h($currency_code)?></span>
                    <?php endif ?>

                </div>

            </div>

        <?php endif ?>

        <?php if ($quick_add['available_products']): ?>
            <div class="form-group col-sm-3">
                <button type="submit" class="btn btn-secondary btn-thin">
                    Add
                </button>
            </div>
        <?php endif ?>

        <?=$quick_add['system'] // Required hidden fields and JS (do not remove) ?>

    </form>

    <?php endif ?>

<?php
    // If there are no recipients in the order, then show message.
    if (!$recipients):
?>

    <h5><strong>Please use the Donation Form to view your donation opportunities.</strong></h5>

<?php
    // Otherwise there is at least one recipient, so show items.
    else:
?>
    <p class="text-center lead">Please verify your donations and click 'Submit Donation' to process your gift.</p>
    
    <form <?=$attributes?>>

        <?php
            // If there are recurring items, then show heading to
            // differentiate "Today's Charges" from the "Recurring Charges".
            if ($recurring_items):
        ?>
            <h4>Today's Donation</h4>
        <?php endif ?>

        <?php
            // If there are nonrecurring items, then place items in a column.
            if ($nonrecurring_items):
        ?>

            <div class="row">

                <div class="col-lg-9">

                    <table class="table mobile_stacked">

                        <?php foreach($recipients as $recipient): ?>

                            <?php
                                // If this recipient has an item in nonrecurring transaction
                                // then show this recipient and its items.
                                if ($recipient['in_nonrecurring']):
                            ?>

                                <?php
                                    // If this is a shipping recipient,
                                    // then show ship to heading.
                                    if ($recipient['ship_to_heading']):
                                ?>
                                    <tr>
                                        <td colspan="6" style="border:none">

                                            <h4 class="mt24">

                                                Ship to

                                                <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                                                    <strong><?=h($recipient['ship_to_name'])?></strong>
                                                <?php endif ?>

                                                <?php if (!$recipient['complete']): ?>
                                                    <strong>(Incomplete)</strong>
                                                <?php endif ?>

                                                <a href="<?=h($recipient['update_url'])?>" class="btn btn-sm btn-thin" style="margin-bottom:4px">
                                                    Update
                                                </a>

                                            </h4>

                                            <p>
                                                
                                                <?=h($recipient['name'])?>

                                                <?php if ($recipient['address']): ?>

                                                    <?php if ($recipient['name']): ?>
                                                        <br>
                                                    <?php endif ?>

                                                    <?=h($recipient['address'])?>

                                                <?php endif ?>

                                            </p>
                                            
                                            <?php
                                                // If there is a custom shipping form (use $recipient['form'])
    											// If there is a custom shipping form and was not completed by customer (use $recipient['form_data'])
    											// So we will only show the data fields if they were completed by the customer
                                                if ($recipient['form_data']):
                                            ?>

                                            	<p>
                                                    
                                                <?php if ($recipient['form_title']): ?>
                                                    <strong><?=h($recipient['form_title'])?></strong><br>
                                                <?php endif ?>

                                                    <?php foreach ($recipient['fields'] as $field): ?>

                                                        <?php if ($field['type'] == 'information'): ?>

                                                            <?=$field['information']?><br>

                                                        <?php else: ?>

                                                            <?=$field['label']?>:

                                                            <?=$field['data_info']?><br>

                                                        <?php endif ?>

                                                    <?php endforeach ?>

                                            	</p>

                                            <?php endif ?>                                         

                                            <?php
                                                // If there are active arrival dates,
                                                // then show arrival date info.
                                                if ($arrival_dates):
                                            ?>

                                                <p>

                                                    Requested Arrival Date:

                                                    <?php
                                                        // If this is a custom arrival date
                                                        // then show actual date.
                                                        if ($recipient['arrival_date_custom']):
                                                    ?>

                                                        <?=get_absolute_time(array(
                                                            'timestamp' => strtotime($recipient['arrival_date']),
                                                            'type' => 'date',
                                                            'size' => 'long'))?>

                                                    <?php
                                                        // Otherwise the arrival date is not custom,
                                                        // so show arrival date name.
                                                        else:
                                                    ?>
                                                        <?=h($recipient['arrival_date_name'])?>
                                                    <?php endif ?>

                                                </p>

                                            <?php endif ?>

                                        </td>
                                    </tr>
                                <?php endif ?>

                                <tr>

                                    <th>Item</th>

                                    <th>Description</th>

                                    <th class="text-center">
                                        <?php if ($recipient['non_donations_in_nonrecurring']): ?>
                                            Qty
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">
                                        <?php if ($recipient['non_donations_in_nonrecurring']): ?>
                                            Price
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">Amount</th>

                                    <th></th>

                                </tr>

                                <?php foreach($recipient['items'] as $item): ?>

                                    <?php
                                        // If this item is in nonrecurring transaction then show it.
                                        if ($item['in_nonrecurring']):
                                    ?>

                                        <tr>

                                            <td>
                                                <span class="visible-xs-inline">Item:</span>
                                                <?=h($item['name'])?>
                                            </td>

                                            <td>

                                                <?php
                                                    // Use the page property to determine whether the
                                                    // full or short description should be shown.
                                                    if ($product_description_type == 'full_description'):
                                                ?>
                                                
                                                    <?php
                                                        // If this item has an image, then start row structure
                                                        // and output column for image.
                                                        if ($item['image_url']):
                                                    ?>

                                                        <div class="row">

                                                            <div class="col-md-6">

                                                                <?php
                                                                    // The containers around the image fixes Firefox
                                                                    // issue with responsive images in tables.
                                                                ?>
                                                                <div class="responsive_table_image_1">
                                                                    <div class="responsive_table_image_2">
                                                                        <img src="<?=h($item['image_url'])?>" class="img-responsive img-fluid center-block">
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="col-md-6">

                                                    <?php endif ?>                  
                                                
                                                    <span style="font-weight:bold"><?=h($item['short_description'])?></span>
                                                    <?=$item['full_description']?>
                                                <?php else: ?>
                                                    <?=h($item['short_description'])?>
                                                <?php endif ?>

                                                <?php if ($item['show_out_of_stock_message']): ?>
                                                    <?=$item['out_of_stock_message']?>
                                                <?php endif ?>

                                                <?php if ($item['calendar_event']): ?>
                                                    <p>
                                                        <?=h($item['calendar_event']['name'])?><br>
                                                        <?=$item['calendar_event']['date_and_time_range']?>
                                                    </p>
                                                <?php endif ?>

                                                <?php
                                                    // If there was an image shown for this item, then close
                                                    // column and row structure.
                                                    if ($item['image_url']
                                                       and ($product_description_type == 'full_description')
                                                    ):
                                                ?>

                                                        </div>

                                                    </div>

                                                <?php endif ?>

                                                <?php
                                                    // If the recurring schedule is editable
                                                    // by the customer, then show fields.
                                                    if ($item['recurring_schedule']):
                                                ?>

                                                    <div class="fieldset">

                                                        <div class="legend">Payment Schedule</div>

                                                        <div class="form-group">

                                                            <label for="recurring_payment_period_<?=$item['id']?>">
                                                                Frequency*
                                                            </label>

                                                            <div class="select-option">
                                                                <i class="ti-angle-down"></i>
                                                                <select name="recurring_payment_period_<?=$item['id']?>" id="recurring_payment_period_<?=$item['id']?>"></select>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">

                                                            <label for="recurring_number_of_payments_<?=$item['id']?>">
                                                                Number of Payments<?php if ($number_of_payments_required): ?>*<?php endif ?>
                                                            </label>

                                                            <input type="number" name="recurring_number_of_payments_<?=$item['id']?>" id="recurring_number_of_payments_<?=$item['id']?>" class="form-control">

                                                            <p class="help-block">
                                                                <?=$number_of_payments_message?>
                                                            </p>

                                                        </div>

                                                        <?php
                                                            // We only allow the start date to be selected
                                                            // for certain payment gateways.
                                                            if ($start_date):
                                                        ?>

                                                            <div class="form-group">

                                                                <label for="recurring_start_date_<?=$item['id']?>">
                                                                    Start Date*
                                                                </label>

                                                                <input type="text" name="recurring_start_date_<?=$item['id']?>" id="recurring_start_date_<?=$item['id']?>" class="form-control">

                                                            </div>

                                                        <?php endif ?>

                                                    </div>

                                                <?php endif ?>

                                                <?php
                                                    // If this is a gift card, then show fields.
                                                    if ($item['gift_card']):
                                                ?>

                                                    <?php
                                                        // Show gift card fields for every quantity.
                                                        for ($quantity_number = 1; $quantity_number <= $item['number_of_gift_cards']; $quantity_number++):
                                                    ?>

                                                        <div class="fieldset">

                                                            <div class="legend">

                                                                Gift Card

                                                                <?php if ($item['number_of_gift_cards'] > 1): ?>
                                                                    (<?=$quantity_number?>
                                                                    of
                                                                    <?=$item['number_of_gift_cards']?>)
                                                                <?php endif ?>

                                                            </div>

                                                            <div class="form-group">

                                                                <label>
                                                                    Amount
                                                                </label>

                                                                <p class="form-control-static">
                                                                    <strong><?=$item['price_info']?></strong>
                                                                </p>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address">
                                                                    Recipient Email*
                                                                </label>

                                                                <input type="email" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address" class="form-control" placeholder="recipient@example.com">

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name">
                                                                    Your Name
                                                                </label>

                                                                <input type="text" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name" class="form-control" placeholder="Your name that will appear in the email.">

                                                                <p class="help-block">
                                                                    (leave blank if you want to be anonymous)
                                                                </p>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message">
                                                                    Message
                                                                </label>

                                                                <textarea name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message" rows="3" placeholder="The message that will appear in the email."></textarea>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date">
                                                                    Delivery Date
                                                                </label>

                                                                <input type="text" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date" class="form-control" placeholder="Your name that will appear in the email.">

                                                            </div>

                                                        </div>

                                                    <?php endfor ?>

                                                <?php endif ?>

                                                <?php
                                                    // If this item has a product form,
                                                    // then show form.
                                                    if ($item['form']):
                                                ?>
                                                    <?php
                                                        // Show product form for every quantity if necessary.
                                                        for ($quantity_number = 1; $quantity_number <= $item['number_of_forms']; $quantity_number++):
                                                    ?>
                                                    
                                                        <div class="fieldset">
                                                    
                                                            <?php if ($item['form_title'] or ($item['number_of_forms'] > 1)): ?>
                                                    
                                                                <div class="legend">
                                                    
                                                                    <?php if ($item['form_title']): ?>
                                                                        <?=h($item['form_title'])?>
                                                                    <?php endif ?>
                                                    
                                                                    <?php if ($item['number_of_forms'] > 1): ?>
                                                                        (<?=$quantity_number?>
                                                                        of
                                                                        <?=$item['number_of_forms']?>)
                                                                    <?php endif ?>
                                                    
                                                                </div>
                                                    
                                                            <?php endif ?>
                                                    
                                                            <?php foreach ($item['fields'] as $field): ?>
                                                    
                                                                <?php
                                                                    // Prepare field name and id.
                                                                    $name =
                                                                        'order_item_' . $item['id'] .
                                                                        '_quantity_number_' . $quantity_number .
                                                                        '_form_field_' . $field['id'];
                                                                ?>
                                                    
                                                                <?php switch($field['type']):
                                                                    case 'text box':
                                                                    case 'email address':
                                                                    case 'date':
                                                                    case 'date and time':
                                                                    case 'time':
                                                                ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                    
                                                                            <input
                                                    
                                                                                type="<?php if ($field['type'] == 'email address'): ?>email<?php else: ?>text<?php endif ?>"
                                                    
                                                                                name="<?=$name?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['size']): ?>
                                                                                    size="<?=$field['size']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['maxlength']): ?>
                                                                                    maxlength="<?=$field['maxlength']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                                class="form-control"
                                                    
                                                                            >
                                                    
                                                                            <?php if ($field['type'] == 'time'): ?>
                                                                                <p class="help-block">
                                                                                    (Format: h:mm AM/PM)
                                                                                </p>
                                                                            <?php endif ?>
                                                    
                                                                        </div>
                                                            
                                                                        <?php
                                                                           // If there is a title for this field and quantity
                                                                           // number, then show title label and title.  We show the title
                                                                           // of a submitted form when a customer enters a valid reference
                                                                           // code in order to help the customer understand which submitted
                                                                           // form the reference code is related to (e.g. ordering credits
                                                                           // for a conversation/support ticket).
                                                                           if ($field['titles'][$quantity_number]['title']):
                                                                        ?>

                                                                           <div class="form-group">

                                                                               <label>
                                                                                   <?=$field['titles'][$quantity_number]['title_label']?>
                                                                               </label>

                                                                               <p class="form-control-static">
                                                                                   <?=h($field['titles'][$quantity_number]['title'])?>
                                                                               </p>

                                                                           </div>

                                                                        <?php endif ?>                                                         
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'text area': ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                    
                                                                            <textarea
                                                    
                                                                                name="<?=$name?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['rows']): ?>
                                                                                    rows="<?=$field['rows']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['cols']): ?>
                                                                                    cols="<?=$field['cols']?>"
                                                                                <?php endif ?>
                                                                                      
                                                                                <?php if ($field['maxlength']): ?>
                                                                                    maxlength="<?=$field['maxlength']?>"
                                                                                <?php endif ?>
                                                                                      
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                            ></textarea>
                                                    
                                                                        </div>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'pick list': ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                                            
                                                                            <div class="select-option">
                                                                            <i class="ti-angle-down"></i>                                                    
                                                                            <select
                                                    
                                                                                name="<?=$name?><?php if ($field['multiple']): ?>[]<?php endif ?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['size']): ?>
                                                                                    size="<?=$field['size']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['multiple']): ?>
                                                                                    multiple
                                                                                <?php endif ?>
                                                    
                                                                            ></select>
                                                                            </div>
                                                    
                                                                        </div>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'radio button': ?>
                                                    
                                                                        <?php if ($field['label']): ?>
                                                                            <label><?=$field['label']?></label>
                                                                        <?php endif ?>
                                                    
                                                                        <?php foreach ($field['options'] as $option): ?>
                                                    
                                                                            <div class="radio">
                                                    
                                                                                <label>
                                                    
                                                                                    <input
                                                    
                                                                                        type="radio"
                                                    
                                                                                        name="<?=$name?>"
                                                    
                                                                                        value="<?=h($option['value'])?>"
                                                    
                                                                                        <?php if ($field['required']): ?>
                                                                                            required
                                                                                        <?php endif ?>
                                                    
                                                                                    >
                                                    
                                                                                    <?=h($option['label'])?>
                                                    
                                                                                </label>
                                                    
                                                                            </div>
                                                    
                                                                        <?php endforeach ?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'check box': ?>
                                                    
                                                                        <?php if ($field['label']): ?>
                                                                            <label><?=$field['label']?></label>
                                                                        <?php endif ?>
                                                    
                                                                        <?php foreach ($field['options'] as $option): ?>
                                                    
                                                                            <div class="checkbox">
                                                    
                                                                                <label>
                                                    
                                                                                    <input
                                                    
                                                                                        type="checkbox"
                                                    
                                                                                        name="<?=$name?><?php if (count($field['options']) > 1): ?>[]<?php endif ?>"
                                                    
                                                                                        value="<?=h($option['value'])?>"
                                                    
                                                                                        <?php
                                                                                            // If the field is required and there is
                                                                                            // only one check box option, then make
                                                                                            // field required.
                                                                                            if (
                                                                                                $field['required']
                                                                                                and (count($field['options']) == 1)
                                                                                            ):
                                                                                        ?>
                                                                                            required
                                                                                        <?php endif ?>
                                                    
                                                                                    >
                                                    
                                                                                    <?=h($option['label'])?>
                                                    
                                                                                </label>
                                                    
                                                                            </div>
                                                    
                                                                        <?php endforeach ?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'information': ?>
                                                    
                                                                        <?=$field['information']?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                <?php endswitch ?>
                                                    
                                                            <?php endforeach ?>
                                                    
                                                        </div>
                                                    
                                                    <?php endfor ?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-center">

                                                <?php
                                                    // If the item is not a donation then show quantity.
                                                    if ($item['selection_type'] != 'donation'):
                                                ?>

                                                    <?php
                                                        // If the item was added by an offer,
                                                        // then just show uneditable quantity amount.
                                                        if ($item['added_by_offer']):
                                                    ?>

                                                        <?=number_format($item['quantity'])?>

                                                    <?php
                                                        // Otherwise the item was not added by an offer
                                                        // so allow customer to change quantity.
                                                        else:
                                                    ?>

                                                        <div class="form-group">

                                                            <label for="quantity[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                                Qty
                                                            </label>

                                                            <input type="number" name="quantity[<?=$item['id']?>]" id="quantity[<?=$item['id']?>]" class="form-control" style="min-width: 5em">

                                                        </div>

                                                    <?php endif ?>

                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php if ($item['selection_type'] != 'donation'): ?>
                                                    <span class="visible-xs-inline">Price:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            

                                                <?php if ($item['selection_type'] == 'donation'): ?>
                                            
                                        	<td class="text-right donation">

                                                    <div class="form-group">

                                                        <label for="donations[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                            Amount
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-addon">
                                                                <?=$currency_symbol?>
                                                            </span>

                                                            <input type="number" step="any" name="donations[<?=$item['id']?>]" id="donations[<?=$item['id']?>]" class="form-control" style="min-width: 6em">

                                                            <?php if ($currency_code): ?>
                                                                <span class="input-group-addon">
                                                                    <?=h($currency_code)?>
                                                                </span>
                                                            <?php endif ?>

                                                        </div>

                                                    </div>

                                            </td>
                                            
                                                <?php else: ?>
                                                
                                            <td class="text-right">
                                                
                                                    <span class="visible-xs-inline">Amount:</span>
                                                    <?=$item['amount_info']?>
                                                
                                            </td>
                                            
                                                <?php endif ?>

                                            

                                            <td class="text-center">

                                                <a href="<?=h($item['remove_url'])?>" class="remove-item" title="Remove">
                                                    <i class="ti-close"></i>
                                                </a>

                                            </td>                    

                                        </tr>

                                    <?php endif ?>

                                <?php endforeach ?>

                                <?php
                                    // If this is a shipping recipient and it is complete,
                                    // then show shipping fee row.
                                    if ($recipient['shipping'] and $recipient['complete']):
                                ?>

                                    <tr>

                                        <td colspan="2">

                                            Shipping Method:

                                            <?=h($recipient['shipping_method_name'])?><?php if ($recipient['shipping_method_description']): ?>; <?=h($recipient['shipping_method_description'])?>
                                            <?php endif ?>

                                        </td>

                                        <td></td>

                                        <td></td>

                                        <td class="text-right">
                                            <span class="visible-xs-inline">Shipping:</span>
                                            <?=$recipient['shipping_cost_info']?>
                                        </td>

                                        <td></td>

                                    </tr>

                                <?php endif ?>

                            <?php endif ?>

                        <?php endforeach ?>

                    </table>

                </div>

                <div class="col-lg-3">

            <?php endif ?>

            <h5 class="uppercase text-right">Totals</h5>

            <table class="table">

                <?php
                    // We only show the subtotal if there is an offer discount, tax,
                    // shipping, or gift card discount.  Otherwise the subtotal
                    // would be redundant with the total due.
                    if ($show_subtotal):
                ?>
                    <tr>
                        <th scope="row" class="text-right" style="width: 100%">Subtotal:</th>
                        <td class="text-right"><?=$subtotal_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($discount_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Discount:</th>
                        <td class="text-right">-<?=$discount_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($tax_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Tax:</th>
                        <td class="text-right"><?=$tax_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($shipping_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Shipping:</th>
                        <td class="text-right"><?=$shipping_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($gift_card_discount_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Gift Card<?php if ($number_of_applied_gift_cards > 1): ?>s<?php endif ?>:</th>
                        <td class="text-right">-<?=$gift_card_discount_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($show_surcharge): ?>

                    <tr class="surcharge_row">
                        <th scope="row" class="text-right">Surcharge:</th>
                        <td class="text-right"><?=$surcharge_info?></td>
                    </tr>

                    <tr class="surcharge_total_row">
                        <th scope="row" class="text-right">Total Due:</th>
                        <td class="text-right">
                            <strong>
                                <?=$total_with_surcharge_info?><?php if ($base_currency_total_with_surcharge_info): ?>*
                                (<?=$base_currency_total_with_surcharge_info?>)<?php endif ?>
                            </strong>
                        </td>
                    </tr>

                <?php endif ?>

                <tr class="total_row">
                    <th scope="row" class="text-right">Total Due:</th>
                    <td class="text-right">
                        <strong>
                            <?=$total_info?><?php if ($base_currency_total_info): ?>*
                            (<?=$base_currency_total_info?>)<?php endif ?>
                        </strong>
                    </td>
                </tr>

            </table>

            <?php
                // If the customer has a currency selected that is different
                // from the base currency, then show total disclaimer.
                if ($total_disclaimer):
            ?>
                <p class="text-muted">
                    <small>
                        *This amount is based on our current currency exchange rate to <?=h($base_currency_name)?> and may differ from the exact charges (displayed above in <?=h($base_currency_name)?>).
                    </small>
                </p>
            <?php endif ?>
                    
            <div class="row clearfix mt40 mb40">

            <?php if ($show_special_offer_code): ?>
                
                <div class="col-sm-6 col-lg-12">

                <div class="form-group">

                    <?php if ($special_offer_code_label): ?>
                        <h5 class="mb8"><?=h($special_offer_code_label)?></h5>
                    <?php endif ?>

                    <input type="text" name="special_offer_code" id="special_offer_code" class="form-control">
                    </div>
                    
                    <?php if ($special_offer_code_message): ?>
                        <p class="help-block">
                            <?=h($special_offer_code_message)?>
                        </p>
                    <?php endif ?>

                </div>

            <?php endif ?>

            <?php if ($applied_offers): ?>
                
                <div class="col-sm-6 col-lg-12">

                <h5 class="mb8 red-color">
                    Applied Offer<?php if ($number_of_applied_offers > 1): ?>s<?php endif ?>
                </h5>

                <?php if ($number_of_applied_offers > 1): ?>
                    <ul>
                <?php else: ?>
                    <p>
                <?php endif ?>

                <?php foreach($applied_offers as $offer): ?>

                    <?php if ($number_of_applied_offers > 1): ?>
                        <li>
                    <?php endif ?>

                    <span class="red-color"><em><?=h($offer['description'])?></em></span>

                    <?php if ($number_of_applied_offers > 1): ?>
                        </li>
                    <?php endif ?>

                <?php endforeach ?>

                <?php if ($number_of_applied_offers > 1): ?>
                    </ul>
                <?php else: ?>
                    </p>
                <?php endif ?>
                
                </div>

            <?php endif ?>
                    
            </div>

            <div class="form-group">

                <?php
                    // formnovalidate prevents browser from validating fields
                    // (e.g. required fields) when update button is clicked.
                    // This allows customer to partially complete and update cart.
                    // Browser validation will only occur for checkout button.
                ?>

                <button type="submit" name="submit_update" class="btn btn-secondary btn-lg" formnovalidate>
                    <?=h($update_button_label)?>
                </button>

            </div>

        <?php
            // If there are nonrecurring items, then close column and row.
            if ($nonrecurring_items):
        ?>

                </div>

            </div>

        <?php endif ?>

        <?php if ($recurring_items): ?>

            <h4 class="mt64">Recurring Donation</h4>

            <div class="row">

                <div class="col-lg-9">

                    <table class="table mobile_stacked">

                        <?php foreach($recipients as $recipient): ?>

                            <?php
                                // If this recipient has an item for recurring transaction
                                // then show this recipient and its items.
                                if ($recipient['in_recurring']):
                            ?>

                                <?php
                                    // If multi-recipient shipping is enabled and this is a shipping
                                    // recipient, then show ship to heading.
                                    if ($recipient['ship_to_heading']):
                                ?>
                                    <tr>
                                        <td colspan="7" style="border:none">

                                            <h4 class="mt24">

                                                Ship to

                                                <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                                                    <strong><?=h($recipient['ship_to_name'])?></strong>
                                                <?php endif ?>

                                                <?php if (!$recipient['complete']): ?>
                                                    <strong>(Incomplete)</strong>
                                                <?php endif ?>

                                                <a href="<?=h($recipient['update_url'])?>" class="btn  btn-secondary btn-sm" style="margin-bottom:4px">
                                                    Update
                                                </a>

                                            </h4>

                                            <?php
                                                // If the address and other info about
                                                // this recipient was not already shown
                                                // in nonrecurring area, then show it.
                                                if (!$recipient['in_nonrecurring']):
                                            ?>

                                                <p>
                                                    
                                                    <?=h($recipient['name'])?>

                                                    <?php if ($recipient['address']): ?>

                                                        <?php if ($recipient['name']): ?>
                                                            <br>
                                                        <?php endif ?>

                                                        <?=h($recipient['address'])?>

                                                    <?php endif ?>

                                                </p>

                                                <?php
                                                    // If there is a custom shipping form,
                                                    // then allow customer to review it.
                                                    if ($recipient['form']):
                                                ?>

                                                    <?php if ($recipient['form_title']): ?>
                                                        <h4><?=h($recipient['form_title'])?></h4>
                                                    <?php endif ?>

                                                    <dl class="dl-horizontal">

                                                        <?php foreach ($recipient['fields'] as $field): ?>

                                                            <?php if ($field['type'] == 'information'): ?>

                                                                <?=$field['information']?>

                                                            <?php else: ?>

                                                                <dt><?=$field['label']?></dt>

                                                                <dd><?=$field['data_info']?></dd>

                                                            <?php endif ?>

                                                        <?php endforeach ?>

                                                    </dl>

                                                <?php endif ?>

                                            <?php endif ?>

                                        </td>
                                    </tr>
                                <?php endif ?>

                                <tr>

                                    <th>Item</th>

                                    <th>Description</th>

                                    <th>Frequency</th>

                                    <th class="text-center">
                                        <?php if ($recipient['non_donations_in_recurring']): ?>
                                            Qty
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">
                                        <?php if ($recipient['non_donations_in_recurring']): ?>
                                            Price
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">Amount</th>

                                    <th></th>

                                </tr>

                                <?php foreach($recipient['items'] as $item): ?>

                                    <?php
                                        // If this item is in recurring transaction then show it.
                                        if ($item['in_recurring']):
                                    ?>

                                        <tr>

                                            <td>
                                                <span class="visible-xs-inline">Item:</span>
                                                <?=h($item['name'])?>
                                            </td>

                                            <td>

                                                <?php
                                                    // Use the page property to determine whether the
                                                    // full or short description should be shown.
                                                    if ($product_description_type == 'full_description'):
                                                ?>
                                                
                                                    <?php
                                                        // If this item has an image, then start row structure
                                                        // and output column for image.
                                                        if ($item['image_url']):
                                                    ?>

                                                        <div class="row">

                                                            <div class="col-md-6">

                                                                <?php
                                                                    // The containers around the image fixes Firefox
                                                                    // issue with responsive images in tables.
                                                                ?>
                                                                <div class="responsive_table_image_1">
                                                                    <div class="responsive_table_image_2">
                                                                        <img src="<?=h($item['image_url'])?>" class="img-responsive img-fluid center-block">
                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="col-md-6">

                                                    <?php endif ?>                                                 
                                                                                          
                                                    <span style="font-weight:bold"><?=h($item['short_description'])?></span>
                                                    <?=$item['full_description']?>
                                                <?php else: ?>
                                                    <?=h($item['short_description'])?>
                                                <?php endif ?>

                                                <?php if ($item['show_out_of_stock_message']): ?>
                                                    <?=$item['out_of_stock_message']?>
                                                <?php endif ?>

                                                <?php if ($item['calendar_event']): ?>
                                                    <p>
                                                        <?=h($item['calendar_event']['name'])?><br>
                                                        <?=$item['calendar_event']['date_and_time_range']?>
                                                    </p>
                                                <?php endif ?>

                                                <?php
                                                    // If there was an image shown for this item, then close
                                                    // column and row structure.
                                                    if (($product_description_type == 'full_description') and ($item['image_url'])):
                                                ?>

                                                        </div>

                                                    </div>

                                                <?php endif ?>

                                                <?php
                                                    // If the recurring schedule is editable
                                                    // by the customer, and this item does not
                                                    // appear in the nonrecurring area,
                                                    // then show fields.
                                                    if (
                                                        $item['recurring_schedule']
                                                        and !$item['in_nonrecurring']
                                                    ):
                                                ?>

                                                    <div class="fieldset">

                                                        <div class="legend">Payment Schedule</div>

                                                        <div class="form-group">

                                                            <label for="recurring_payment_period_<?=$item['id']?>">
                                                                Frequency*
                                                            </label>

                                                            <div class="select-option">
                                                                <i class="ti-angle-down"></i>
                                                                <select name="recurring_payment_period_<?=$item['id']?>" id="recurring_payment_period_<?=$item['id']?>"></select>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">

                                                            <label for="recurring_number_of_payments_<?=$item['id']?>">
                                                                Number of Payments<?php if ($number_of_payments_required): ?>*<?php endif ?>
                                                            </label>

                                                            <input type="number" name="recurring_number_of_payments_<?=$item['id']?>" id="recurring_number_of_payments_<?=$item['id']?>" class="form-control">

                                                            <p class="help-block">
                                                                <?=$number_of_payments_message?>
                                                            </p>

                                                        </div>

                                                        <?php
                                                            // We only allow the start date to be selected
                                                            // for certain payment gateways.
                                                            if ($start_date):
                                                        ?>

                                                            <div class="form-group">

                                                                <label for="recurring_start_date_<?=$item['id']?>">
                                                                    Start Date*
                                                                </label>

                                                                <input type="text" name="recurring_start_date_<?=$item['id']?>" id="recurring_start_date_<?=$item['id']?>" class="form-control">

                                                            </div>

                                                        <?php endif ?>

                                                    </div>

                                                <?php endif ?>

                                                <?php
                                                    // If this item has a product form,
                                                    // and this item does not appear
                                                    // in nonrecurring area, then show form.
                                                    if (
                                                        $item['form']
                                                        and !$item['in_nonrecurring']
                                                    ):
                                                ?>
                                                    <?php
                                                        // Show product form for every quantity if necessary.
                                                        for ($quantity_number = 1; $quantity_number <= $item['number_of_forms']; $quantity_number++):
                                                    ?>
                                                    
                                                        <div class="fieldset">
                                                    
                                                            <?php if ($item['form_title'] or ($item['number_of_forms'] > 1)): ?>
                                                    
                                                                <div class="legend">
                                                    
                                                                    <?php if ($item['form_title']): ?>
                                                                        <?=h($item['form_title'])?>
                                                                    <?php endif ?>
                                                    
                                                                    <?php if ($item['number_of_forms'] > 1): ?>
                                                                        (<?=$quantity_number?>
                                                                        of
                                                                        <?=$item['number_of_forms']?>)
                                                                    <?php endif ?>
                                                    
                                                                </div>
                                                    
                                                            <?php endif ?>
                                                    
                                                            <?php foreach ($item['fields'] as $field): ?>
                                                    
                                                                <?php
                                                                    // Prepare field name and id.
                                                                    $name =
                                                                        'order_item_' . $item['id'] .
                                                                        '_quantity_number_' . $quantity_number .
                                                                        '_form_field_' . $field['id'];
                                                                ?>
                                                    
                                                                <?php switch($field['type']):
                                                                    case 'text box':
                                                                    case 'email address':
                                                                    case 'date':
                                                                    case 'date and time':
                                                                    case 'time':
                                                                ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                    
                                                                            <input
                                                    
                                                                                type="<?php if ($field['type'] == 'email address'): ?>email<?php else: ?>text<?php endif ?>"
                                                    
                                                                                name="<?=$name?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['size']): ?>
                                                                                    size="<?=$field['size']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['maxlength']): ?>
                                                                                    maxlength="<?=$field['maxlength']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                                class="form-control"
                                                    
                                                                            >
                                                    
                                                                            <?php if ($field['type'] == 'time'): ?>
                                                                                <p class="help-block">
                                                                                    (Format: h:mm AM/PM)
                                                                                </p>
                                                                            <?php endif ?>
                                                    
                                                                        </div>
                                                            
                                                                        <?php
                                                                           // If this field has a title and this is the first quantity
                                                                           // number, then show title label and title.  We show the title
                                                                           // of a submitted form when a customer enters a valid reference
                                                                           // code in order to help the customer understand which submitted
                                                                           // form the reference code is related to (e.g. ordering credits
                                                                           // for a conversation/support ticket).
                                                                           if ($field['title'] and ($quantity_number == 1)):
                                                                        ?>

                                                                           <div class="form-group">

                                                                               <label>
                                                                                   <?=$field['title_label']?>
                                                                               </label>

                                                                               <p class="form-control-static">
                                                                                   <?=h($field['title'])?>
                                                                               </p>

                                                                           </div>

                                                                        <?php endif ?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'text area': ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                    
                                                                            <textarea
                                                    
                                                                                name="<?=$name?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['rows']): ?>
                                                                                    rows="<?=$field['rows']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['cols']): ?>
                                                                                    cols="<?=$field['cols']?>"
                                                                                <?php endif ?>
                                                                                                                                  																				  <?php if ($field['maxlength']): ?>
                                                                                    maxlength="<?=$field['maxlength']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                            ></textarea>
                                                    
                                                                        </div>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'pick list': ?>
                                                    
                                                                        <div class="form-group">
                                                    
                                                                            <?php if ($field['label']): ?>
                                                                                <label for="<?=$name?>">
                                                                                    <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                                                                                </label>
                                                                            <?php endif ?>
                                                    
                                                                            <div class="select-option">
                                                                            <i class="ti-angle-down"></i>
                                                                            <select
                                                    
                                                                                name="<?=$name?><?php if ($field['multiple']): ?>[]<?php endif ?>"
                                                    
                                                                                id="<?=$name?>"
                                                    
                                                                                <?php if ($field['size']): ?>
                                                                                    size="<?=$field['size']?>"
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['required']): ?>
                                                                                    required
                                                                                <?php endif ?>
                                                    
                                                                                <?php if ($field['multiple']): ?>
                                                                                    multiple
                                                                                <?php endif ?>
                                                    
                                                                                class="form-control"
                                                    
                                                                            ></select>
                                                                            </div>
                                                    
                                                                        </div>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'radio button': ?>
                                                    
                                                                        <?php if ($field['label']): ?>
                                                                            <label><?=$field['label']?></label>
                                                                        <?php endif ?>
                                                    
                                                                        <?php foreach ($field['options'] as $option): ?>
                                                    
                                                                            <div class="radio">
                                                    
                                                                                <label>
                                                    
                                                                                    <input
                                                    
                                                                                        type="radio"
                                                    
                                                                                        name="<?=$name?>"
                                                    
                                                                                        value="<?=h($option['value'])?>"
                                                    
                                                                                        <?php if ($field['required']): ?>
                                                                                            required
                                                                                        <?php endif ?>
                                                    
                                                                                    >
                                                    
                                                                                    <?=h($option['label'])?>
                                                    
                                                                                </label>
                                                    
                                                                            </div>
                                                    
                                                                        <?php endforeach ?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'check box': ?>
                                                    
                                                                        <?php if ($field['label']): ?>
                                                                            <label><?=$field['label']?></label>
                                                                        <?php endif ?>
                                                    
                                                                        <?php foreach ($field['options'] as $option): ?>
                                                    
                                                                            <div class="checkbox">
                                                    
                                                                                <label>
                                                    
                                                                                    <input
                                                    
                                                                                        type="checkbox"
                                                    
                                                                                        name="<?=$name?><?php if (count($field['options']) > 1): ?>[]<?php endif ?>"
                                                    
                                                                                        value="<?=h($option['value'])?>"
                                                    
                                                                                        <?php
                                                                                            // If the field is required and there is
                                                                                            // only one check box option, then make
                                                                                            // field required.
                                                                                            if (
                                                                                                $field['required']
                                                                                                and (count($field['options']) == 1)
                                                                                            ):
                                                                                        ?>
                                                                                            required
                                                                                        <?php endif ?>
                                                    
                                                                                    >
                                                    
                                                                                    <?=h($option['label'])?>
                                                    
                                                                                </label>
                                                    
                                                                            </div>
                                                    
                                                                        <?php endforeach ?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                    <?php case 'information': ?>
                                                    
                                                                        <?=$field['information']?>
                                                    
                                                                        <?php break ?>
                                                    
                                                                <?php endswitch ?>
                                                    
                                                            <?php endforeach ?>
                                                    
                                                        </div>
                                                    
                                                    <?php endfor ?>
                                                <?php endif ?>

                                            </td>

                                            <td>
                                                <span class="visible-xs-inline">Frequency:</span>
                                                <?=h($item['payment_period'])?>
                                            </td>

                                            <td class="text-center">

                                                <?php
                                                    // If the item is not a donation then show quantity.
                                                    if ($item['selection_type'] != 'donation'):
                                                ?>

                                                    <?php
                                                        // If the item was added by an offer,
                                                        // or was already shown in nonrecurring area,
                                                        // then just show uneditable quantity amount,
                                                        // (i.e. don't allow customer to change quantity).
                                                        if ($item['added_by_offer'] or $item['in_nonrecurring']):
                                                    ?>

                                                        <?=number_format($item['quantity'])?>

                                                    <?php
                                                        // Otherwise allow customer to change quantity.
                                                        else:
                                                    ?>

                                                        <div class="form-group">

                                                            <label for="quantity[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                                Qty
                                                            </label>

                                                            <input type="number" name="quantity[<?=$item['id']?>]" id="quantity[<?=$item['id']?>]" class="form-control" style="min-width: 5em">

                                                        </div>

                                                    <?php endif ?>

                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php if ($item['selection_type'] != 'donation'): ?>
                                                    <span class="visible-xs-inline">Price:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            

                                                <?php
                                                    // If the item is a donation and it is not
                                                    // already listed in nonrecurring area,
                                                    // then allow customer to edit amount.
                                                    if (
                                                        ($item['selection_type'] == 'donation')
                                                        and !$item['in_nonrecurring']
                                                    ):
                                                ?>

                                            <td class="text-right donation">
                                                
                                                    <div class="form-group">

                                                        <label for="donations[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                            Amount
                                                        </label>

                                                        <div class="input-group">

                                                            <span class="input-group-addon">
                                                                <?=$currency_symbol?>
                                                            </span>

                                                            <input type="number" step="any" name="donations[<?=$item['id']?>]" id="donations[<?=$item['id']?>]" class="form-control" style="min-width: 6em">

                                                            <?php if ($currency_code): ?>
                                                                <span class="input-group-addon">
                                                                    <?=h($currency_code)?>
                                                                </span>
                                                            <?php endif ?>

                                                        </div>

                                                    </div>
                                                
                                            </td>

                                                <?php else: ?>
                                            
                                            <td class="text-right">
                                                
                                                    <span class="visible-xs-inline">Amount:</span>
                                                    <?=$item['amount_info']?>
                                            </td>
                                                <?php endif ?>

                                            <td class="text-center">

                                                <?php
                                                    // If the item is not already listed in
                                                    // nonrecurring area, then show remove button.
                                                    // We don't want multiple remove buttons
                                                    // for the same item that could confuse customer.
                                                    if (!$item['in_nonrecurring']):
                                                ?>

                                                	<a href="<?=h($item['remove_url'])?>" class="remove-item" title="Remove">
                                                    	<i class="ti-close"></i>
                                                	</a>

                                                <?php endif ?>

                                            </td>                    

                                        </tr>

                                    <?php endif ?>

                                <?php endforeach ?>

                            <?php endif ?>

                        <?php endforeach ?>

                    </table>

                </div>

                <div class="col-lg-3">

                    <h5 class="uppercase text-right">Recurring Totals</h5>

                    <table class="table">

                        <?php
                            // Loop through the payment periods in order to show totals.
                            foreach($payment_periods as $payment_period):
                        ?>

                            <tr>
                                <th scope="row" class="text-right" style="width: 100%">
                                    <?=h($payment_period['name'])?> Subtotal:
                                </th>
                                <td class="text-right">
                                    <?=$payment_period['subtotal_info']?>
                                </td>
                            </tr>

                            <?php if ($payment_period['tax_info']): ?>
                                <tr>
                                    <th scope="row" class="text-right" style="width: 100%">
                                        <?=h($payment_period['name'])?> Tax:
                                    </th>
                                    <td class="text-right">
                                        <?=$payment_period['tax_info']?>
                                    </td>
                                </tr>
                            <?php endif ?>

                            <tr>
                                <th scope="row" class="text-right" style="width: 100%">
                                    <?=h($payment_period['name'])?> Total:
                                </th>
                                <td class="text-right">
                                    <strong><?=$payment_period['total_info']?></strong>
                                </td>
                            </tr>

                        <?php endforeach ?>

                    </table>

                </div>

            </div>

        <?php endif ?>

    <div class="mt40 mb40">
        
    <h4>Billing</h4><hr>
    
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
    <div class="col-sm-4"> 
    <div class="form-group">
        <label for="billing_salutation">Salutation</label>
        <div class="select-option">
            <i class="ti-angle-down"></i>
            <select name="billing_salutation" id="billing_salutation"></select>
        </div>
    </div>
    </div>

    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_first_name">First Name*</label>
        <input type="text" name="billing_first_name" id="billing_first_name" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <div class="form-group">
        <label for="billing_last_name">Last Name*</label>
        <input type="text" name="billing_last_name" id="billing_last_name" class="form-control">
    </div>
    </div>
        
    <div class="col-sm-5">
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

    <?php if ($tax_exempt): // removed since not applicable for donations ?>
    <?php endif ?>
        
    </div>

        <?php if ($custom_billing_form): ?>
            
            <?=
                // Add edit button and grid if edit mode is enabled.
                $edit_custom_billing_form_start
            ?>
            
            <?php if ($custom_billing_form_title): ?>
                <h2><?=h($custom_billing_form_title)?></h2>
            <?php endif ?>
            

            <?=
                // Close the edit grid.
                $edit_custom_billing_form_end
            ?>
            
        <?php endif ?>

        <?php if ($applied_gift_cards): ?>

            <h5>
                Applied Gift Card<?php if ($number_of_applied_gift_cards > 1): ?>s<?php endif ?>
            </h5>

            <?php if ($number_of_applied_gift_cards > 1): ?>
                <ul>
            <?php else: ?>
                <p>
            <?php endif ?>

            <?php foreach($applied_gift_cards as $gift_card): ?>

                <?php if ($number_of_applied_gift_cards > 1): ?>
                    <li>
                <?php endif ?>

                <?=h($gift_card['protected_code'])?>

                (Remaining Balance: <?=$gift_card['remaining_balance_info']?>)

                <a href="<?=h($gift_card['remove_url'])?>" class="remove-item" title="Remove">
                    <i class="ti-close"></i>
                </a>

                <?php if ($number_of_applied_gift_cards > 1): ?>
                    </li>
                <?php endif ?>

            <?php endforeach ?>

            <?php if ($number_of_applied_gift_cards > 1): ?>
                </ul>
            <?php else: ?>
                </p>
            <?php endif ?>

        <?php endif ?>
    
        </div>

        <?php if ($payment): ?>

            <div class="mb40">

            <h4>Payment</h4><hr>

            <?php if ($gift_card_code): // removed since not applicable for donation payments ?>
            <?php endif ?>

            <?php if ($credit_debit_card): ?>
                
                <?php
                    // If this is the only payment method, then show a heading.
                    if ($number_of_payment_methods == 1):
                ?>
                    <h5>Credit/Debit Card</h5>

                <?php
                    // Otherwise there are multiple payment methods, so show radio button.
                    else:
                ?>
                    <div class="radio">
                        <div class="radio-option">
                            <div class="inner"></div>
                            <input type="radio" name="payment_method" value="Credit/Debit Card">
                            <span><h5>Credit/Debit Card</h5></span>
                        </div>
                    </div>
                <?php endif ?>

                <?php
                    // This container and class allows the credit/debit card
                    // fields to be dynamically shown/hidden as necesssary.
                ?>
                <div class="credit_debit_card" style="display: none">

                    <div class="row">
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="card_number">Card Number*</label>
                                <input
                                    type="tel"
                                    id="card_number"
                                    name="card_number" 
                                    autocomplete="cc-number"
                                    spellcheck="false"
                                    inputmode="numeric"
                                    class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label for="expiration">Expiration*</label>
                                <input
                                    type="tel"
                                    id="expiration"
                                    name="expiration" 
                                    autocomplete="cc-exp"
                                    spellcheck="false"
                                    inputmode="numeric"
                                    placeholder="MM / YY"
                                    class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-sm-3 col-xs-6">
                            <div class="form-group">
                                <label for="card_verification_number">Security Code*</label>

                                <input
                                    type="tel"
                                    id="card_verification_number"
                                    name="card_verification_number" 
                                    autocomplete="cc-csc"
                                    spellcheck="false"
                                    inputmode="numeric"
                                    placeholder="CSC"
                                    maxlength="4"
                                    class="form-control">

                                <?php if ($card_verification_number_url): ?>
                                    <p class="help-block">
                                        <a href="<?=h($card_verification_number_url)?>" target="_blank">What is this?</a>
                                    </p>
                                <?php endif ?>
                            </div>
                        </div>
                    
                    </div>

                    <?php
                        // We only show the surcharge message if there are other
                        // payment method options (e.g. PayPal Express Checkout, Offline)
                        // because the total might not include the surcharge until
                        // the customer selects the credit/debit card payment method.
                        if ($surcharge_message):
                    ?>
                        <p class="text-muted">
                            <small>
                                <?=h($surcharge_percentage)?>% surcharge has been added.
                            </small>
                        </p>
                    <?php endif ?>

                </div>

            <?php endif ?>

            <?php if ($paypal_express_checkout): ?>
                
                <?php
                    // If this is the only payment method, then just show the PayPal image.
                    if ($number_of_payment_methods == 1):
                ?>
                    <h5><img src="<?=h($paypal_express_checkout_image_url)?>" alt="PayPal"></h5>
                    
                <?php
                    // Otherwise there are multiple payment methods, so show radio button.
                    else:
                ?>
                    <div class="radio">
                        <div class="radio-option">
                            <div class="inner"></div>
                            <input type="radio" name="payment_method" value="PayPal Express Checkout">
                            <span><img src="<?=h($paypal_express_checkout_image_url)?>" alt="PayPal" style="position: relative; top:-4px"></span>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>

            <?php if ($offline_payment): ?>
                
                <?php
                    // If this is the only payment method, then show a heading.
                    if ($number_of_payment_methods == 1):
                ?>
                    <h5 class="mb8"><?=h($offline_payment_label)?></h5>

                <?php
                    // Otherwise there are multiple payment methods, so show radio button.
                    else:
                ?>
                    <div class="radio">
                        <div class="radio-option">
                            <div class="inner"></div>
                            <input type="radio" name="payment_method" value="Offline Payment">
                            <span><h5 class="mb8"><?=h($offline_payment_label)?></h5></span>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>

        <?php endif ?>
                
            <?php if ($offline_payment_allowed): ?>
                <div class="software_notice">
                <div class="checkbox">
                    <label class="check-box">
                        <input type="checkbox" name="offline_payment_allowed" value="1">
                        <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                        Allow offline payment option for this <?=h($shopping_cart_label)?> (and click update to apply).
                    </label>
                </div>
                </div>
            <?php endif ?>                         
                

        <?php if ($terms_url): ?>
            <div class="checkbox">
                  <label class="check-box">
                    <input type="checkbox" name="terms" value="1">
                    <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                    <span>I agree to the <a href="<?=h($terms_url)?>" target="_blank">terms and conditions</a>.</span>
                </label>
            </div>
        <?php endif ?>
                
        </div>

        <div class="form-group">

            <?php
                // formnovalidate prevents browser from validating fields
                // (e.g. required fields) when update button is clicked.
                // This allows customer to partially complete and update cart.
                // Browser validation will only occur for checkout button.
            ?>
            
            <button type="submit" name="submit_update" class="btn btn-secondary btn-lg" formnovalidate>
                <?=h($update_button_label)?>
            </button>

            <?php
                // If there is at least one payment method and order is
                // allowed to be submitted, then show purchase now button.
                if ($purchase_now_button):
            ?>
                <button
                    type="submit"
                    name="submit_purchase_now"
                    class="purchase_button btn btn-primary btn-lg"

                    <?php if ($paypal_express_checkout): ?>
                        data-paypal-label="Continue to PayPal"
                    <?php endif ?>
                >
                    <?=h($purchase_now_button_label)?>
                </button>
            <?php endif ?>

        </div>

        <?=$system // Required hidden fields and JS (do not remove) ?>

    </form>

    <p class="text-muted">
        <small>
            This <?=h($shopping_cart_label)?> has been saved.  To retrieve this <?=h($shopping_cart_label)?> at a later time, please use this link:<br>
            <a href="<?=h($retrieve_order_url)?>"><?=h($retrieve_order_url)?></a>
        </small>
    </p>

<?php endif ?>

<?php if ($currency and $recipients): ?>

    <form <?=$currency_attributes?>>

        <div class="form-group">
            <label for="currency_id" class="sr-only">Currency</label>
            <div class="select-option">
                <i class="ti-angle-down"></i>
                <select name="currency_id" id="currency_id"></select>
            </div>
        </div>

        <?=$currency_system // Required hidden fields and JS (do not remove) ?>

    </form>

<?php endif ?>

</div>
