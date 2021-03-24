<div class="cart">

	<div class="row">
    	<div class="col-sm-12"><?=$messages?></div>
    </div>
<?php
    // If there are no recipients in the order, then show message.
    if (!$recipients):
?>

    <h5><strong>Hiçbir ürün eklenmedi.</strong></h5>

<?php
    // Otherwise there is at least one recipient, so show items.
    else:
?>
    
    <p class="text-center lead">Lütfen siparişinizi gözden geçirin ve siparişinizi tamamlamak için ödeme bilgilerini ekleyin.</p>

    <form <?=$attributes?>>

        <?php
            // If there are recurring items, then show heading to
            // differentiate "Today's Charges" from the "Recurring Charges".
            if ($recurring_items):
        ?>
            <h4>Bugünün Ücretleri</h4>
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

                                                Alıcı

                                                <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                                                    <strong><?=h($recipient['ship_to_name'])?></strong>
                                                <?php endif ?>

                                                <a href="<?=h($recipient['update_url'])?>" class="btn btn-sm btn-thin" style="margin-bottom:4px">
                                                    Güncelle
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

                                    <th>Ürün</th>

                                    <th>Açıklama</th>

                                    <th class="text-center">
                                        <?php if ($recipient['non_donations_in_nonrecurring']): ?>
                                            Miktar
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">
                                        <?php if ($recipient['non_donations_in_nonrecurring']): ?>
                                            Fiyat
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">Tutar</th>

                                </tr>

                                <?php foreach($recipient['items'] as $item): ?>

                                    <?php
                                        // If this item is in nonrecurring transaction then show it.
                                        if ($item['in_nonrecurring']):
                                    ?>

                                        <tr>

                                            <td>
                                                <span class="visible-xs-inline">Ürün:</span>
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
                                                    
                                                    <div class="legend">Ödeme Planı</div>

                                                    <dl class="dl-horizontal">

                                                        <dt>Sıklık</dt>
                                                        <dd><?=h($item['recurring_payment_period'])?></dd>

                                                        <dt>Ödeme Sayısı</dt>
                                                        <dd>
                                                            <?php if ($item['recurring_number_of_payments']): ?>
                                                                <?=number_format($item['recurring_number_of_payments'])?>
                                                            <?php else: ?>
                                                                [limitsiz]
                                                            <?php endif ?>
                                                        </dd>

                                                        <?php
                                                            // We only allow the start date to be selected
                                                            // for certain payment gateways.
                                                            if ($start_date):
                                                        ?>
                                                            <dt>Başlangıç Tarihi</dt>
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
                                                            
                                                            Hediye Kartı

                                                            <?php if ($item['number_of_gift_cards'] > 1): ?>
                                                                (<?=$gift_card['quantity_number']?>
                                                                /
                                                                <?=$item['number_of_gift_cards']?>)
                                                            <?php endif ?>

                                                        </div>

                                                        <dl class="dl-horizontal">

                                                            <dt>Tutar</dt>
                                                            <dd><strong><?=$item['price_info']?></strong></dd>

                                                            <dt>Alıcının E-postası</dt>
                                                            <dd><?=h($gift_card['recipient_email_address'])?></dd>

                                                            <dt>İsminiz</dt>
                                                            <dd><?=h($gift_card['from_name'])?></dd>

                                                            <dt>Mesaj</dt>
                                                            <dd><?=nl2br(h($gift_card['message']))?></dd>

                                                            <dt>Teslimat Tarihi</dt>
                                                            <dd>
                                                                <?php if ($gift_card['delivery_date'] == '0000-00-00'): ?>
                                                                    Acil
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
                                                    <span class="visible-xs-inline">Fiyat:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">
                                                <span class="visible-xs-inline">Tutar:</span>
                                                <?=$item['amount_info']?>
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

                                            Nakliye Yöntemi:

                                            <?=h($recipient['shipping_method_name'])?><?php if ($recipient['shipping_method_description']): ?>; <?=h($recipient['shipping_method_description'])?>
                                            <?php endif ?>

                                        </td>

                                        <td></td>

                                        <td></td>

                                        <td class="text-right">
                                            <span class="visible-xs-inline">Nakliye:</span>
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

            <h5 class="uppercase text-right">Toplam</h5>

            <table class="table">

                <?php
                    // We only show the subtotal if there is an offer discount, tax,
                    // shipping, or gift card discount.  Otherwise the subtotal
                    // would be redundant with the total due.
                    if ($show_subtotal):
                ?>
                    <tr>
                        <th scope="row" class="text-right">Ara Toplam:</th>
                        <td class="text-right"><?=$subtotal_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($discount_info): ?>
                    <tr>
                        <th scope="row" class="text-right">İndirim:</th>
                        <td class="text-right">-<?=$discount_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($tax_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Vergi:</th>
                        <td class="text-right"><?=$tax_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($shipping_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Nakliye:</th>
                        <td class="text-right"><?=$shipping_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($gift_card_discount_info): ?>
                    <tr>
                        <th scope="row" class="text-right">Hediye Kartı:</th>
                        <td class="text-right">-<?=$gift_card_discount_info?></td>
                    </tr>
                <?php endif ?>

                <?php if ($show_surcharge): ?>

                    <tr class="surcharge_row">
                        <th scope="row" class="text-right">Fazla Vergi:</th>
                        <td class="text-right"><?=$surcharge_info?></td>
                    </tr>

                    <tr class="surcharge_total_row">
                        <th scope="row" class="text-right" style="width: 100%">Ödenecek Toplam:</th>
                        <td class="text-right">
                            <strong>
                                <?=$total_with_surcharge_info?><?php if ($base_currency_total_with_surcharge_info): ?>*
                                (<?=$base_currency_total_with_surcharge_info?>)<?php endif ?>
                            </strong>
                        </td>
                    </tr>

                <?php endif ?>

                <tr class="total_row">
                    <th scope="row" class="text-right" style="width: 100%">Ödenecek Toplam:</th>
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
                        * Bu tutar şu andaki döviz kurumunuza bağlı olan <?=h($base_currency_name)?>'tir ve kesin masraflardan farklı olabilir (yukarıda gösterilen <?=h($base_currency_name)?>).                    </small>
                </p>
            <?php endif ?>
                    
            <div class="row clearfix mt40 mb40">

            <?php if ($applied_offers): ?>
                
                <div class="col-sm-6 col-lg-12">

                <h5 class="mb8 red-color">
                    Uygulanan Teklif
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
                    // If the purchase now button for the sidebar should be shown
                    // then show it.  We don't output the purchase now button in
                    // the sidebar if there are recurring items because the recurring
                    // items appear below the button and might confuse customers.
                    if ($purchase_now_button and !$recurring_items):
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

        <?php
            // If there are nonrecurring items, then close column and row.
            if ($nonrecurring_items):
        ?>

                </div>

            </div>

        <?php endif ?>

        <?php if ($recurring_items): ?>

            <h4 class="mt64">Sürekli Yinelenen Ücretler</h4>

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

                                                Alıcı

                                                <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
                                                    <strong><?=h($recipient['ship_to_name'])?></strong>
                                                <?php endif ?>

                                                <a href="<?=h($recipient['update_url'])?>" class="btn btn-secondary btn-sm" style="margin-bottom:4px">
                                                    Güncelle
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

                                    <th>Ürün</th>

                                    <th>Açıklama</th>

                                    <th>Sıklık</th>

                                    <th class="text-center">
                                        <?php if ($recipient['non_donations_in_recurring']): ?>
                                            Miktar
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">
                                        <?php if ($recipient['non_donations_in_recurring']): ?>
                                            Fiyat
                                        <?php endif ?>
                                    </th>

                                    <th class="text-right">Tutar</th>

                                </tr>

                                <?php foreach($recipient['items'] as $item): ?>

                                    <?php
                                        // If this item is in recurring transaction then show it.
                                        if ($item['in_recurring']):
                                    ?>

                                        <tr>

                                            <td>
                                                <span class="visible-xs-inline">Ürün:</span>
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

                                                   	<div class="legend">Ödeme Planı</div>

                                                    <dl class="dl-horizontal">

                                                        <dt>Sıklık</dt>
                                                        <dd><?=h($item['recurring_payment_period'])?></dd>

                                                        <dt>Ödeme Sayısı</dt>
                                                        <dd>

                                                            <?php if ($item['recurring_number_of_payments']): ?>
                                                                <?=number_format($item['recurring_number_of_payments'])?>
                                                            <?php else: ?>
                                                                [limitsiz]
                                                            <?php endif ?>

                                                        </dd>

                                                        <?php
                                                            // We only allow the start date to be selected
                                                            // for certain payment gateways.
                                                            if ($start_date):
                                                        ?>
                                                            <dt>Başlangıç Tarihi</dt>
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

                                                	<div class="fieldset">
                                                    
                                                    <?php foreach($item['forms'] as $form): ?>

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

                                                    <?php endforeach ?>
                                                        
                                                	</div>

                                                <?php endif ?>

                                            </td>

                                            <td>
                                                <span class="visible-xs-inline">Sıklık:</span>
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
                                                    <span class="visible-xs-inline">Fiyat:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">
                                                <span class="visible-xs-inline">Tutar:</span>
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

                    <h5 class="uppercase text-right">Toplam</h5>

                    <table class="table">

                        <?php
                            // Loop through the payment periods in order to show totals.
                            foreach($payment_periods as $payment_period):
                        ?>

                            <tr>
                                <th scope="row" class="text-right" style="width: 100%">
                                    <?=h($payment_period['name'])?> Ara Toplam:
                                </th>
                                <td class="text-right">
                                    <?=$payment_period['subtotal_info']?>
                                </td>
                            </tr>

                            <?php if ($payment_period['tax_info']): ?>
                                <tr>
                                    <th scope="row" class="text-right" style="width: 100%">
                                        <?=h($payment_period['name'])?> Vergi:
                                    </th>
                                    <td class="text-right">
                                        <?=$payment_period['tax_info']?>
                                    </td>
                                </tr>
                            <?php endif ?>

                            <tr>
                                <th scope="row" class="text-right" style="width: 100%">
                                    <?=h($payment_period['name'])?> Toplam:
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

    	<div class="mt24 mb24">
            
        <h4>
            Fatura

            <a href="<?=h($billing_update_url)?>" class="btn btn-sm btn-thin" style="margin-bottom:4px">
                Güncelle
            </a>

        </h4><hr>

        <div>

            <?php if ($custom_field_1_label and $custom_field_1): ?>
                <?=h($custom_field_1_label)?>: <?=h($custom_field_1)?><br>
            <?php endif ?>

            <?php if ($custom_field_2_label and $custom_field_2): ?>
                <?=h($custom_field_2_label)?>: <?=h($custom_field_2)?><br>
            <?php endif ?>

            <?php if ($billing_first_name): ?>
            	<?=h($billing_salutation)?> <?=h($billing_first_name)?> <?=h($billing_last_name)?><br>
            <?php endif ?>
            
            <?php if ($billing_company): ?>
                <?=h($billing_company)?><br>
            <?php endif ?>

            <?php if ($$billing_address_1): ?>
            	<?=h($billing_address_1)?><br>
            <?php endif ?>            

            <?php if ($billing_address_2): ?>
                <?=h($billing_address_2)?><br>
            <?php endif ?>

            <?php if ($billing_city or $billing_state or $billing_zip_code): ?>

                <?php if ($billing_city): ?>
                    <?=h($billing_city)?>,
                <?php endif ?>

                <?=h($billing_state)?>

                <?=h($billing_zip_code)?>

                <br>

            <?php endif ?>
            
            <?php if ($billing_country): ?>
            	<?=h($billing_country)?><br>
            <?php endif ?>
            
            <?php if ($billing_phone_number): ?>
            	Phone: <?=h($billing_phone_number)?><br>
            <?php endif ?>
            
            <?php if ($billing_fax_number): ?>
                Fax: <?=h($billing_fax_number)?><br>
            <?php endif ?>

            <?php if ($billing_email_address): ?>
            	<?=h($billing_email_address)?><br>
            <?php endif ?>
            
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
                Uygulamalı Hediye Kartı 
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

                (Kalan Bakiye: <?=$gift_card['remaining_balance_info']?>)

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

    		</div>
    
        <?php endif ?>
    
		</div>

        <?php if ($payment): ?>

			<div class="mb40">

            <h4>Ödeme</h4><hr>

            <?php if ($gift_card_code): ?>

                <div class="form-group form-inline">
                    <h5 class="mb8">Hediye Kartı Kodu</h5>
                    <div class="form-group form-inline mb0">
                        <input type="text" name="gift_card_code" id="gift_card_code" class="form-control" style="min-width:200px">
                    </div>
                    
                    <button type="submit" name="submit_apply_gift_card" class="btn btn-thin"  formnovalidate>
                        Uygula
                    </button>
                </div>

            <?php endif ?>

            <?php if ($credit_debit_card): ?>
                
                <?php
                    // If this is the only payment method, then show a heading.
                    if ($number_of_payment_methods == 1):
                ?>
                    <h5>Kredi / Banka Kartı</h5>

                <?php
                    // Otherwise there are multiple payment methods, so show radio button.
                    else:
                ?>
                    <div class="radio">
                        <div class="radio-option">
                            <div class="inner"></div>
                            <input type="radio" name="payment_method" value="Credit/Debit Card">
                            <span><h5>Kredi / Banka Kartı</h5></span>
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
                                <label for="card_number">Kart Numarası*</label>
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
                                <label for="expiration">SKT. Tarihi*</label>
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
                                <label for="card_verification_number">CVC Numarası*</label>

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
                                        <a href="<?=h($card_verification_number_url)?>" target="_blank">Bu Nedir?</a>
                                    </p>
                                <?php endif ?>
                            </div>
                        </div>

                        
                    
                        <?php if($installment ): ?>
                        	<div class="col-xs-12">
                        	    <style>.installment-row .installment_box{
                        	        display: inline-block;
                        	        margin: 1rem;
                        	        padding: 1rem;
                        	        background-color: whitesmoke;
                        	        border-radius: 3px;
                        	        
                        	        transition:background-color .3s ease;
                        	        border: 1px solid #e5e5e5;}
                        	        .installment-row .installment_box:hover{
                        	           	border: 1px solid #58b675;
                        	            background-color: #d6ffbc;
                        	            transition: background-color .3s ease;
                        	        }
                        	        .installment-row .installment_box:active{
                        	            background-color: #c0fb9b;
                        	            transition: background-color .3s ease;
                        	        }
                        	   .installment_box input[name="installment"] {height: 2rem;width: 100%;}
                        	        
                            	</style>
                            	<div class="installment-row" style="display:none">
                         			<div>
                						<label>Taksitli Ödeme Seçenekleri</label>
            						</div>
                            	    <span id="oneinstallment" class="installment_box">
                        				<input type="radio" name="installment" checked value="1" >
                        				<label for="1">Tek Çekim<br/><br/><?if($total_with_surcharge_info):?><?=($total_with_surcharge_info)?><?else:?><?=($total_info)?><?endif?></label>
                            	    </span>
                            	    <span id="twoinstallment" class="installment_box" style="display:none">
                        				<input type="radio" name="installment" value="2">
                            	        <label for="2">2 Taksit <br/><span class="installment_prices_here"></span></label>
                            	    </span>
                            	    <span id="threeinstallment" class="installment_box" style="display:none">
                        				<input type="radio" name="installment" value="3">
                        				<label for="3">3 Taksit <br/><span class="installment_prices_here"></span></label>
                                	</span>
                                	<span id="sixinstallment" class="installment_box" style="display:none">
                        				<input type="radio" name="installment" value="6">
                        				<label for="6">6 Taksit <br/><span class="installment_prices_here"></span></label>
                                	</span>
                                	<span id="nineinstallment" class="installment_box" style="display:none">
                        				<input type="radio" name="installment" value="9">
                        				<label for="9">9 Taksit <br/><span class="installment_prices_here"></span></label>
                                	</span>
                                	<span id="twelveinstallment" class="installment_box" style="display:none">
                        				<input type="radio" name="installment" value="12">
                        				<label for="12">12 Taksit <br/><span class="installment_prices_here"></span></label>
                                	</span>
                            	</div>
                    			<?php if($installment_table):?>
                        			<div class="modal-container">
    									<a class="btn-modal " href="#" style="text-decoration:underline;">Taksit seçeneklerini görüntülemek için tıklayın.</a>
    									<div class="site_modal">
        									<?=($installment_table)?>
                                		</div>
									</div> 
                    			<?php endif ?>
                      		</div> 	
                  		<? endif ?>
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
                                % <?=h($surcharge_percentage)?> ek ücret eklendi.
                            </small>
                        </p>
                    <?php endif ?>
                    <? if($threedsecure): ?>
                    	<input type="checkbox" id="threedsecure" name="threedsecure" value="1" <?=($threedsecure_required)?>>
                    	<label class="check-box">3DSecure ile Ödeme</label>
                	<? endif ?>
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
                            <input type="radio" name="payment_method" value="Çevrimdışı Ödeme">
                            <span><h5 class="mb8"><?=h($offline_payment_label)?></h5></span>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>

        <?php endif ?>

        <?php if ($terms_url): ?>
            <div class="checkbox">
                  <label class="check-box">
                    <input type="checkbox" name="terms" value="1">
                    <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                    <span><a href="<?=h($terms_url)?>" target="_blank">Hükümleri ve koşulları</a> onaylıyorum.</span>
                </label>
            </div>
        <?php endif ?>
                
		</div>

        <div class="form-group">

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

<?php endif ?>

<?php if ($currency): ?>

    <form <?=$currency_attributes?>>

        <div class="form-group">
            <label for="currency_id" class="sr-only">Para Birimi</label>
            <div class="select-option">
                <i class="ti-angle-down"></i>
                <select name="currency_id" id="currency_id"></select>
            </div>
        </div>

        <?=$currency_system // Required hidden fields and JS (do not remove) ?>

    </form>

<?php endif ?>

</div>