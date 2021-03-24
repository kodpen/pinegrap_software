<div class="row mb12">
    
    <div class="col-sm-12"><?=$messages?></div>
    
    <div class="col-sm-12">
        <h4>Thank you for your donation!</h4>
    </div>
    
    <div class="col-sm-6">
        Order Number: <strong><?=$order_number?></strong><br>

    	Order Date: 
        	<?=get_absolute_time(array(
            'timestamp' => $order_date,
            'size' => 'long'))?>
    </div>
    
    <div class="col-sm-6"><p class="text-right text-left-sm">Please print and keep this page for your records.<br>You will receive an email copy too.</p></div>

</div>

<?php foreach($order_receipt_messages as $message): ?>
	<div class="mb24" style="border: 1px solid #777; padding: 1rem 1rem 0rem"><?=$message?></div>
<?php endforeach ?>


<?php
    // If there are recurring items, then show heading to
    // differentiate "Today's Charges" from the "Recurring Charges".
    if ($recurring_items):
?>
    <h4>Today's Donations</h4>
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
                                <td colspan="5" style="border:none">

                                    <h4 class="mt24">

                                        Ship to

                                        <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                                            <strong><?=h($recipient['ship_to_name'])?></strong>
                                        <?php endif ?>

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

                                        <?php if ($item['calendar_event']): ?>
                                            <p>
                                                <?=h($item['calendar_event']['name'])?><br>
                                                <?=$item['calendar_event']['date_and_time_range']?>
                                            </p>
                                        <?php endif ?>

                                        <?php
                                            // If there was an image shown for this item,
                                            // then close column and row structure.
                                            if (
                                                $item['image_url']
                                                and ($product_description_type == 'full_description')
                                            ):
                                        ?>

                                                </div>

                                            </div>

                                        <?php endif ?>

                                        <?php
                                            // If the recurring schedule is editable
                                            // by the customer, then show values.
                                            if ($item['recurring_schedule']):
                                        ?>

                                            <div class="fieldset">
                                                    
                                            <div class="legend">Payment Schedule</div>

                                            <dl class="dl-horizontal">

                                                <dt>Frequency</dt>
                                                <dd><?=h($item['recurring_payment_period'])?></dd>

                                                <dt>Number of Payments</dt>
                                                <dd>
                                                    <?php if ($item['recurring_number_of_payments']): ?>
                                                        <?=number_format($item['recurring_number_of_payments'])?>
                                                    <?php else: ?>
                                                        [no limit]
                                                    <?php endif ?>
                                                </dd>

                                                <?php
                                                    // We only allow the start date to be selected
                                                    // for certain payment gateways.
                                                    if ($start_date):
                                                ?>
                                                    <dt>Start Date</dt>
                                                    <dd>
                                                        <?=get_absolute_time(array('timestamp' => strtotime($item['recurring_start_date']), 'type' => 'date', 'size' => 'long'))?>
                                                    </dd>

                                                <?php endif ?>

                                            </dl>
                                                
                                        	</div>

                                        <?php endif ?>

                                        <?php
                                            // If this is a gift card, then show info.
                                            if ($item['gift_card']):
                                        ?>

                                            <?php foreach($item['gift_cards'] as $gift_card): ?>

                                                <div class="fieldset">
                                                    
                                                    <div class="legend">
                                                    
                                                    Gift Card

                                                    <?php if ($item['number_of_gift_cards'] > 1): ?>
                                                        (<?=$gift_card['quantity_number']?>
                                                        of
                                                        <?=$item['number_of_gift_cards']?>)
                                                    <?php endif ?>

                                                </div>

                                                <dl class="dl-horizontal">

                                                    <dt>Amount</dt>
                                                    <dd><strong><?=$item['price_info']?></strong></dd>

                                                    <dt>Recipient Email</dt>
                                                    <dd><?=h($gift_card['recipient_email_address'])?></dd>

                                                    <dt>Your Name</dt>
                                                    <dd><?=h($gift_card['from_name'])?></dd>

                                                    <dt>Message</dt>
                                                    <dd><?=nl2br(h($gift_card['message']))?></dd>

                                                    <dt>Delivery Date</dt>
                                                    <dd>
                                                        <?php if ($gift_card['delivery_date'] == '0000-00-00'): ?>
                                                            Immediate
                                                        <?php else: ?>
                                                            <?=get_absolute_time(array('timestamp' => strtotime($gift_card['delivery_date']), 'type' => 'date', 'size' => 'long'))?>
                                                        <?php endif ?>
                                                    </dd>

                                                </dl>
                                                    
                                        	</div>

                                            <?php endforeach ?>

                                        <?php endif ?>

                                        <?php
                                            // If this item has a product form,
                                            // then show form for review.
                                            if ($item['form']):
                                        ?>

                                            <?php foreach($item['forms'] as $form): ?>

                                                <div class="fieldset">
                                                    
                                                	<div class="legend">

                                                    <?php if ($item['form_title']): ?>
                                                        <?=h($item['form_title'])?>
                                                    <?php endif ?>

                                                    <?php if ($item['number_of_forms'] > 1): ?>
                                                        (<?=$form['quantity_number']?>
                                                        of
                                                        <?=$item['number_of_forms']?>)
                                                    <?php endif ?>

                                                	</div>

                                                <dl class="dl-horizontal">

                                                    <?php foreach ($form['fields'] as $field): ?>

                                                        <?php if ($field['type'] == 'information'): ?>

                                                            <?=$field['information']?>

                                                        <?php else: ?>

                                                            <dt><?=$field['label']?></dt>

                                                            <dd><?=$field['data_info']?></dd>

                                                        <?php endif ?>

                                                    <?php endforeach ?>

                                                </dl>
                                                    
                                                </div>

                                            <?php endforeach ?>

                                        <?php endif ?>

                                    </td>

                                    <td class="text-center">

                                        <?php
                                            // If the item is not a donation then show quantity.
                                            if ($item['selection_type'] != 'donation'):
                                        ?>
                                            <?=number_format($item['quantity'])?>
                                        <?php endif ?>

                                    </td>

                                    <td class="text-right">

                                        <?php if ($item['selection_type'] != 'donation'): ?>
                                            <span class="visible-xs-inline">Price:</span>
                                            <?=$item['price_info']?>
                                        <?php endif ?>

                                    </td>

                                    <td class="text-right">
                                        <span class="visible-xs-inline">Amount:</span>
                                        <?=$item['amount_info']?>
                                    </td>

                                </tr>

                            <?php endif ?>

                        <?php endforeach ?>

                        <?php
                            // If this is a shipping recipient then show shipping fee row.
                            if ($recipient['shipping']):
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
            // would be redundant with the total.
            if ($show_subtotal):
        ?>
            <tr>
                <th scope="row" class="text-right">Subtotal:</th>
                <td class="text-right"><?=$subtotal_info?></td>
            </tr>
        <?php endif ?>

        <?php if ($discount_info): ?>
            <tr>
                <th scope="row" class="text-right">Discount:</th>
                <td class="text-right">-<?=$discount_info?></td>
            </tr>
        <?php endif ?>

        <?php if ($tax_info): // removed ?>
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

        <?php if ($surcharge_info): ?>
            <tr>
                <th scope="row" class="text-right">Surcharge:</th>
                <td class="text-right"><?=$surcharge_info?></td>
            </tr>
        <?php endif ?>

        <tr>
            <th scope="row" class="text-right" style="width: 100%">Total:</th>
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

    <?php if ($applied_offers): ?>

        <div class="col-sm-6 col-lg-12">
            
        <h5 class="mb8">
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

            <em><?=h($offer['description'])?></em>

            <?php if ($number_of_applied_offers > 1): ?>
                </li>
            <?php endif ?>

        <?php endforeach ?>

        <?php if ($number_of_applied_offers > 1): ?>
            </ul>
        <?php else: ?>
            </p>
        <?php endif ?>

    <?php endif ?>

<?php
    // If there are nonrecurring items, then close column and row.
    if ($nonrecurring_items):
?>

        </div>

    </div>

<?php endif ?>

<?php if ($recurring_items): ?>

    <h4>Recurring Donations</h4>

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

                                        <?php if ($item['calendar_event']): ?>
                                            <p>
                                                <?=h($item['calendar_event']['name'])?><br>
                                                <?=$item['calendar_event']['date_and_time_range']?>
                                            </p>
                                        <?php endif ?>

                                        <?php
                                            // If there was an image shown for this item,
                                            // then close column and row structure.
                                            if (
                                                $item['image_url']
                                                and ($product_description_type == 'full_description')
                                            ):
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

                                            <dl class="dl-horizontal">

                                                <dt>Frequency</dt>
                                                <dd><?=h($item['recurring_payment_period'])?></dd>

                                                <dt>Number of Payments</dt>
                                                <dd>

                                                    <?php if ($item['recurring_number_of_payments']): ?>
                                                        <?=number_format($item['recurring_number_of_payments'])?>
                                                    <?php else: ?>
                                                        [no limit]
                                                    <?php endif ?>

                                                </dd>

                                                <?php
                                                    // We only allow the start date to be selected
                                                    // for certain payment gateways.
                                                    if ($start_date):
                                                ?>
                                                    <dt>Start Date</dt>
                                                    <dd>
                                                        <?=get_absolute_time(array('timestamp' => strtotime($item['recurring_start_date']), 'type' => 'date', 'size' => 'long'))?>
                                                    </dd>

                                                <?php endif ?>

                                            </dl>
                                                
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

                                            <?php foreach($item['forms'] as $form): ?>

                                            	<div class="fieldset">

                                                   	<div class="legend">

                                                    <?php if ($item['form_title']): ?>
                                                        <?=h($item['form_title'])?>
                                                    <?php endif ?>

                                                    <?php if ($item['number_of_forms'] > 1): ?>
                                                        (<?=$form['quantity_number']?>
                                                        of
                                                        <?=$item['number_of_forms']?>)
                                                    <?php endif ?>

                                                	</div>

                                                <dl class="dl-horizontal">

                                                    <?php foreach ($form['fields'] as $field): ?>

                                                        <?php if ($field['type'] == 'information'): ?>

                                                            <?=$field['information']?>

                                                        <?php else: ?>

                                                            <dt><?=$field['label']?></dt>

                                                            <dd><?=$field['data_info']?></dd>

                                                        <?php endif ?>

                                                    <?php endforeach ?>

                                                </dl>
                                                    
                                        		</div>

                                            <?php endforeach ?>

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
                                            <?=number_format($item['quantity'])?>
                                        <?php endif ?>

                                    </td>

                                    <td class="text-right">

                                        <?php if ($item['selection_type'] != 'donation'): ?>
                                            <span class="visible-xs-inline">Price:</span>
                                            <?=$item['price_info']?>
                                        <?php endif ?>

                                    </td>

                                    <td class="text-right">
                                        <span class="visible-xs-inline">Amount:</span>
                                        <?=$item['amount_info']?>
                                    </td>

                                </tr>

                            <?php endif ?>

                        <?php endforeach ?>

                    <?php endif ?>

                <?php endforeach ?>

            </table>

        </div>

        <div class="col-lg-3">

            <h5 class="uppercase text-right">Totals</h5>

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

                    <?php if ($payment_period['tax_info']): // removed ?>
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

<h4>Billing</h4>

<div>

    <?php if ($custom_field_1_label and $custom_field_1): ?>
        <?=h($custom_field_1_label)?>: <?=h($custom_field_1)?><br>
    <?php endif ?>

    <?php if ($custom_field_2_label and $custom_field_2): ?>
        <?=h($custom_field_2_label)?>: <?=h($custom_field_2)?><br>
    <?php endif ?>

    <?=h($billing_salutation)?> <?=h($billing_first_name)?> <?=h($billing_last_name)?><br>

    <?php if ($billing_company): ?>
        <?=h($billing_company)?><br>
    <?php endif ?>

    <?=h($billing_address_1)?><br>

    <?php if ($billing_address_2): ?>
        <?=h($billing_address_2)?><br>
    <?php endif ?>

    <?=h($billing_city)?>, <?=h($billing_state)?> <?=h($billing_zip_code)?><br>

    <?=h($billing_country)?><br>

    Phone: <?=h($billing_phone_number)?><br>

    <?php if ($billing_fax_number): ?>
        Fax: <?=h($billing_fax_number)?><br>
    <?php endif ?>

    <?=h($billing_email_address)?><br>

    <?php if ($po_number): ?>
        PO Number: <?=h($po_number)?><br>
    <?php endif ?>

    <?php if ($tax_exempt): ?>
        Tax-Exempt<br>
    <?php endif ?>

    <?php
        // If there is a custom billing form,
        // then allow customer to review it.
        if ($billing_form):
    ?>

        <?php if ($billing_form_title): ?>
            <h4><?=h($billing_form_title)?></h4>
        <?php endif ?>

        <dl class="dl-horizontal">

            <?php foreach ($fields as $field): ?>

                <?php if ($field['type'] == 'information'): ?>

                    <?=$field['information']?>

                <?php else: ?>

                    <dt><?=$field['label']?></dt>

                    <dd><?=$field['data_info']?></dd>

                <?php endif ?>

            <?php endforeach ?>

        </dl>

    <?php endif ?>

</div>

<?php if ($applied_gift_cards): ?>

	<div class="mt40 mb40">

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

        (Remaining Balance: <?=$gift_card['new_balance_info']?>)

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

<?php if ($payment_method): ?>

    <h4 class="mt40">Payment</h4>

	<p>
    
        <?=h($payment_method_label)?><br>

        <?php if ($payment_method == 'Credit/Debit Card'): ?>

            <?=h($card_type)?><br>
            <?=h($card_number)?><br>
			<?=h($cardholder)?>

        <?php endif ?>

    </p>

<?php endif ?>

<?php if ($auto_registration): ?>
        
    <div class="fieldset mt40">
        
    <div class="legend">New Account</div>

    <p>We have created a new account for you so you can view your orders on our site. You can find your login info below.</p>

    <dl class="dl-horizontal">

        <dt>Email</dt>
        <dd><?=h($auto_registration_email_address)?></dd>

        <dt>Temporary Password</dt>
        <dd><?=h($auto_registration_password)?></dd>

    </dl>
        
	</div>

<?php endif ?>
