
<div class="cart">
    
<div class="row">
    
    <div class="col-sm-12"><?=$messages?></div>
    
<?php
    // If there are pending or upsell offers, then show them.
    if ($number_of_special_offers):
?>
        
    <h5 class="col-sm-12">
        Özel Teklif<?php if ($number_of_special_offers > 1): ?>ler<?php endif ?>
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

                    <label for="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to">Alıcı</label>

                    <div class="select-option">
                        <i class="ti-angle-down"></i>
                        <select name="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to" id="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_ship_to" style="min-width: 150px"></select>
                    </div>
                </div>

                <?php
                    // If add name is allowed for this action, then show field.
                    // Some actions require certain recipients, so the add name
                    // is not allowed in those cases.
                    if ($action['add_name']):
                ?>
                    <div class="form-group col-sm-3">
                        <label>yada isim ekle</label>
                        <input type="text" name="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_add_name" id="pending_offer_<?=$offer['id']?>_<?=$action['id']?>_add_name" class="form-control" placeholder="Örnek: Tuna">
                    </div>
                <?php endif ?>

            <?php endif ?>
 
                <div class="form-group col-sm-3">
                <button type="submit" name="add_pending_offer_<?=$offer['id']?>_<?=$action['id']?>" class="btn btn-secondary btn-thin" style="margin-top:29px">
                    Ekle
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
            <label for="quick_add_product_id">Ürün</label>
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
                <label for="quick_add_ship_to">Alıcı</label>
                <div class="select-option">
                    <i class="ti-angle-down"></i>
                    <select name="quick_add_ship_to" id="quick_add_ship_to"></select>
                </div>
            </div>

            <div id="quick_add_add_name_row" class="form-group col-sm-3">
                <label for="quick_add_add_name">yada isim ekle</label>
                <input type="text" name="quick_add_add_name" id="quick_add_add_name" class="form-control" placeholder="Örnek: Tuna">
            </div>

        <?php endif ?>

        <?php if ($quick_add['quantity']): ?>
            <div id="quick_add_quantity_row" class="form-group col-sm-3">
                <label for="quick_add_quantity">Miktar</label>
                <input type="number" name="quick_add_quantity" id="quick_add_quantity" class="form-control">
            </div>
        <?php endif ?>

        <?php if ($quick_add['amount']): ?>

            <div id="quick_add_amount_row" class="form-group col-sm-3">

                <label for="quick_add_amount">Tutar</label>

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
                <button type="submit" class="btn btn-secondary btn-thin" style="margin-top:29px">
                    Ekle
                </button>
            </div>
            <?php endif ?>
        
        <?=$quick_add['system'] // Required hidden fields and JS (do not remove) ?>

    </form>

    <?php endif ?>
        
    </div>

<?php
    // If there are no recipients in the order, then show message.
    if (!$recipients):
?>

    <h5><strong>Alışveriş sepetinize hiçbir ürün eklenmedi.</strong></h5>

<?php
    // Otherwise there is at least one recipient, so show items.
    else:
?>

    <p class="text-center lead">Lütfen teklifinizi gözden geçirin ve siparişinize başlamak için 'Ödemeye Devam Et'i tıklayın.</p>
    
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
                                    // If multi-recipient shipping is enabled and this is a shipping
                                    // recipient, then show ship to heading.
                                    if ($recipient['ship_to_heading']):
                                ?>
                                    <tr>
                                        <td colspan="6" style="border:none">
                                            <h4 class="mt24">Alıcı <strong><?=h($recipient['ship_to_name'])?></strong></h4>
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

                                    <th></th>

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
                                                    // If there was an image shown for this item, then close
                                                    // column and row structure.
                                                    if (($product_description_type == 'full_description') and ($item['image_url'])):
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

                                                        <div class="legend">Ödeme Planı</div>

                                                        <div class="form-group">

                                                            <label for="recurring_payment_period_<?=$item['id']?>">
                                                                Sıklık*
                                                            </label>

                                                            <div class="select-option">
                                                                <i class="ti-angle-down"></i>
                                                                <select name="recurring_payment_period_<?=$item['id']?>" id="recurring_payment_period_<?=$item['id']?>"></select>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">

                                                            <label for="recurring_number_of_payments_<?=$item['id']?>">
                                                                Ödeme Sayısı<?php if ($number_of_payments_required): ?>*<?php endif ?>
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
                                                                    Başlangıç Tarihi*
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

                                                                Hediye Kartı

                                                                <?php if ($item['number_of_gift_cards'] > 1): ?>
                                                                    (<?=$quantity_number?>
                                                                    /
                                                                    <?=$item['number_of_gift_cards']?>)
                                                                <?php endif ?>

                                                            </div>

                                                            <div class="form-group">

                                                                <label>
                                                                    Tutar
                                                                </label>

                                                                <p class="form-control-static">
                                                                    <strong><?=$item['price_info']?></strong>
                                                                </p>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address">
                                                                    Alıcının E-postası*
                                                                </label>

                                                                <input type="email" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_recipient_email_address" class="form-control" placeholder="alici@ornek.com">

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name">
                                                                    İsminiz
                                                                </label>

                                                                <input type="text" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_from_name" class="form-control" placeholder="E-postada görünecek olan adınız.">

                                                                <p class="help-block">
                                                                    (isimsiz kalmak istiyorsanız boş bırakın)
                                                                </p>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message">
                                                                    Mesaj
                                                                </label>

                                                                <textarea name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_message" rows="3" placeholder="E-postada görünecek olan mesaj."></textarea>

                                                            </div>

                                                            <div class="form-group">

                                                                <label for="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date">
                                                                    Teslim Tarihi
                                                                </label>

                                                                <input type="text" name="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date" id="order_item_<?=$item['id']?>_quantity_number_<?=$quantity_number?>_gift_card_delivery_date" class="form-control" placeholder="E-postada görünecek olan isminiz.">

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
                                                                        /
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
                                                                                    (Format: s:dd AM/PM)
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
                                                                Miktar
                                                            </label>

                                                            <input type="number" name="quantity[<?=$item['id']?>]" id="quantity[<?=$item['id']?>]" class="form-control" style="min-width: 5em">

                                                        </div>

                                                    <?php endif ?>

                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php if ($item['selection_type'] != 'donation'): ?>
                                                    <span class="visible-xs-inline">Fiyat:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php if ($item['selection_type'] == 'donation'): ?>

                                                    <div class="form-group">

                                                        <label for="donations[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                            Tutar
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

                                                <?php else: ?>
                                                    <span class="visible-xs-inline">Tutar:</span>
                                                    <?=$item['amount_info']?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-center">

                                                <a href="<?=h($item['remove_url'])?>" class="remove-item" title="Kaldır">
                                                    <i class="ti-close"></i>
                                                </a>

                                            </td>                    

                                        </tr>

                                    <?php endif ?>

                                <?php endforeach ?>

                            <?php endif ?>

                        <?php endforeach ?>

                    </table>

                </div>

                <div class="col-lg-3">

            <?php endif ?>

                    
            <h5 class="uppercase text-right">Toplam</h5>

            <table class="table">

                <tr>
                    <th scope="row" class="text-right" style="width: 100%">Ara Toplam:</th>
                    <td class="text-right"><?=$subtotal_info?></td>
                </tr>

                <?php
                    // If there is a discount then show discount and total.
                    if ($discount_info):
                ?>

                    <tr>
                        <th scope="row" class="text-right">İndirim:</th>
                        <td class="text-right">-<?=$discount_info?></td>
                    </tr>

                    <tr>
                        <th scope="row" class="text-right">Toplam:</th>
                        <td class="text-right"><?=$total_info?></td>
                    </tr>

                <?php endif ?>

            </table>

            <?php
                // If there are taxable or shippable items, then show disclaimer
                // about how total does not include those charges yet.
                if ($taxable_items or $shippable_items):
            ?>
                <p class="text-muted">
                    <small>
                        <?php if ($taxable_items): ?> 
                        
                        vergileri 
                        
                        	<?php endif ?>

                        <?php if ($shippable_items): ?>

                       	 <?php if ($taxable_items): ?> 
                        
                        	ve  
                        
                        	<?php endif ?> 
                        
                        ödeme sırasında 
                        
                        <?php endif ?> 
                        
                        hesaplanacak nakliye ücretlerini içermez.
                      
                    </small>
                </p>
            <?php endif ?>

            <div class="row clearfix mt40 mb40">
                        
            <?php if ($show_special_offer_code): ?>

                <div class="col-sm-6 col-lg-12">
                                            
                <div class="form-group">
                        
                        <?php if ($special_offer_code_label): ?>
                            <label for="special_offer_code">
                                <h5 class="uppercase mb8"><?=h($special_offer_code_label)?></h5>
                            </label>
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
                         
                <h5 class="uppercase mb8 red-color">
                    Uygulanan Teklif<?php if ($number_of_applied_offers > 1): ?>ler<?php endif ?>
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

            <?php if ($offline_payment_allowed): ?>
                <div class="software_notice">
                <div class="checkbox">
                    <label class="check-box">
                        <input type="checkbox" name="offline_payment_allowed" value="1">
                        <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                        Bu <?=h($shopping_cart_label)?> için çevrimdışı ödeme seçeneğine izin ver (ve uygulanacak güncellemeyi tıklayın).
                    </label>
                </div>
                </div>
            <?php endif ?>

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

                <button type="submit" name="submit_checkout" class="btn btn-primary btn-lg">
                    <?=h($checkout_button_label)?>
                </button>

            </div>

            <p class="help-block text-muted">
                <small>Toplamları güncellemek için <?=h($update_button_label)?> düğmesine tıklayın veya toplamları güncellemek ve güvenli sunucumuzdaki siparişinizi tamamlamak için <?=h($checkout_button_label)?> düğmesine tıklayın.</small>
            </p>         

        <?php
            // If there are nonrecurring items, then close column and row.
            if ($nonrecurring_items):
        ?>

                </div>

            </div>

        <?php endif ?>

        <?php if ($recurring_items): ?>

            <h4 class="mt64">Sürekli Ücretler</h4>

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
                                            <h4 class="mt24">Alıcı <strong><?=h($recipient['ship_to_name'])?></strong></h4>
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

                                    <th></th>

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

                                                        <div class="legend">Ödeme Planı</div>

                                                        <div class="form-group">

                                                            <label for="recurring_payment_period_<?=$item['id']?>">
                                                                Sıklık*
                                                            </label>

                                                            <div class="select-option">
                                                            <i class="ti-angle-down"></i>
                                                                <select name="recurring_payment_period_<?=$item['id']?>" id="recurring_payment_period_<?=$item['id']?>"></select>
                                                            </div>

                                                        </div>

                                                        <div class="form-group">

                                                            <label for="recurring_number_of_payments_<?=$item['id']?>">
                                                                Ödeme Sayısı<?php if ($number_of_payments_required): ?>*<?php endif ?>
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
                                                                    Başlangıç Tarihi*
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
                                                                                    (Format: s:dd AM/PM)
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
                                                <span class="visible-xs-inline">Sıklık:</span>
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
                                                                Miktar
                                                            </label>

                                                            <input type="number" name="quantity[<?=$item['id']?>]" id="quantity[<?=$item['id']?>]" class="form-control" style="min-width: 5em">

                                                        </div>

                                                    <?php endif ?>

                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php if ($item['selection_type'] != 'donation'): ?>
                                                    <span class="visible-xs-inline">Fiyat:</span>
                                                    <?=$item['price_info']?>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-right">

                                                <?php
                                                    // If the item is a donation and it is not
                                                    // already listed in nonrecurring area,
                                                    // then allow customer to edit amount.
                                                    if (
                                                        ($item['selection_type'] == 'donation')
                                                        and !$item['in_nonrecurring']
                                                    ):
                                                ?>

                                                    <div class="form-group">

                                                        <label for="donations[<?=$item['id']?>]" class="visible-xs-inline-block">
                                                            Tutar
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

                                                <?php else: ?>
                                                    <span class="visible-xs-inline">Tutar:</span>
                                                <?php endif ?>

                                            </td>

                                            <td class="text-center">

                                                <?php
                                                    // If the item is not already listed in
                                                    // nonrecurring area, then show remove button.
                                                    // We don't want multiple remove buttons
                                                    // for the same item that could confuse customer.
                                                    if (!$item['in_nonrecurring']):
                                                ?>

                                                    <a href="<?=h($item['remove_url'])?>" class="remove-item" title="Kaldır">
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

                    <h5 class="uppercase text-right">Yinelenen Toplamlar</h5>

                    <table class="table">

                        <?php
                            // Loop through the payment periods in order to
                            // show a subtotal for each one.
                            foreach($payment_periods as $payment_period):
                        ?>

                            <tr>
                                <th scope="row" class="text-right" style="width: 100%">
                                    <?=h($payment_period['name'])?> Ara Toplam:
                                </th>
                                <td class="text-right"><?=$payment_period['subtotal_info']?></td>
                            </tr>

                        <?php endforeach ?>

                    </table>

                </div>

            </div>

        <?php endif ?>

        <?=$system // Required hidden fields and JS (do not remove) ?>

    </form>

    <p class="text-muted">
        <small>
            Bu <?=h($shopping_cart_label)?> kurtarıldı. Bu <?=h($shopping_cart_label)?>i daha sonra almak için lütfen bu bağlantıyı kullanın:<br>
            <a href="<?=h($retrieve_order_url)?>"><?=h($retrieve_order_url)?></a>
        </small>
    </p>

<?php endif ?>

<?php if ($currency and $recipients): ?>

    <form <?=$currency_attributes?>>

        <div class="form-group mt32">
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