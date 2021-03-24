
<?=$messages?>

<form <?=$attributes?>>

    <?php
        // If there is a requested arrival date, then start two-column structure
        // for the address in one column and the requested arrival date in the other.
        if ($arrival_date):
    ?>
        
        <div class="row">

            <div class="col-sm-6">

    <?php endif ?>

    <h4>

        <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
        <strong><?=h($ship_to_name)?></strong> için 
        <?php endif ?>
        Teslimat Adresi

        <a href="<?=h($update_url)?>" class="btn btn-sm btn-thin" style="margin-bottom:4px">
            Güncelle
        </a>

    </h4>

    <p>

        <?=h($salutation)?> <?=h($first_name)?> <?=h($last_name)?><br>

        <?php if ($company): ?>
            <?=h($company)?><br>
        <?php endif ?>

        <?=h($address_1)?><br>

        <?php if ($address_2): ?>
            <?=h($address_2)?><br>
        <?php endif ?>

        <?=h($city)?>, <?=h($state)?> <?=h($zip_code)?><br>

        <?=h($country)?>

    </p>

    <?php if ($arrival_date): ?>
        
            </div>

            <div class="col-sm-6">

                <h4>

                    Talep Edilen Varış Tarihi

                    <a href="<?=h($update_url)?>" class="btn btn-sm btn-thin" style="margin-bottom:4px">
                        Güncelle
                    </a>

                </h4>

                <p>

                    <?php if ($arrival_date['custom']): ?>
                        <?=$arrival_date['date_info']?>
                    <?php else: ?>
                        <?=h($arrival_date['name'])?>
                    <?php endif ?>

                </p>

            </div>

        </div>

    <?php endif ?>

    <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>

        <h4>Alıcı <strong><?=h($ship_to_name)?></strong></h4>

    <?php endif ?>

    <table class="table mobile_stacked">

        <tr>

            <th>Ürün</th>

            <th>Açıklama</th>

            <th class="text-center">Miktar</th>

            <th class="text-right">Fiyat</th>

            <th class="text-right">Tutar</th>

            <th></th>

        </tr>

        <?php foreach($items as $item): ?>

            <tr
                <?php if ($item['product_restriction']): ?>
                    class="danger"
                <?php endif ?>
            >

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

                    <?php if ($item['product_restriction']): ?>
                        <p class="alert alert-danger" role="alert">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <strong><?=h($product_restriction_message)?></strong>
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

                </td>

                <td class="text-center">
                    <?=number_format($item['quantity'])?>
                </td>

                <td class="text-right">
                    <span class="visible-xs-inline">Fiyat:</span>
                    <?=$item['price_info']?>
                </td>

                <td class="text-right">
                    <span class="visible-xs-inline">Tutar:</span>
                    <?=$item['amount_info']?>
                </td>

                <td class="text-center">
                    
                    <a href="<?=h($item['remove_url'])?>" class="remove-item" title="Kaldır">
                    	<i class="ti-close"></i>
                    </a>
                </td>

            </tr>

        <?php endforeach ?>

    </table>

    <h4 class="mt64">

         <?php if (ECOMMERCE_RECIPIENT_MODE == 'multi-recipient'): ?>
        <strong><?=h($ship_to_name)?></strong> için 
        <?php endif ?>
        Nakliye Yöntemi

    </h4>

    <?php
        // If there are no valid shipping methods for this recipient, then show message.
        if (!$shipping_methods):
    ?>

        <p class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            <strong><?=h($no_shipping_methods_message)?></strong>
        </p>

    <?php
        // Otherwise there is at least one valid shipping method for this recipient,
        // so show shipping methods in a table.
        else:
    ?>
    
        <table class="table mobile_stacked">

            <tr>

                <th>Birini Seçin</th>

                <th class="text-right">Maliyet</th>

                <th>Detaylar</th>

            </tr>

            <?php foreach($shipping_methods as $shipping_method): ?>
            
            <?php if ($shipping_method['protected']): ?>
				<tr class="software_notice" style="border:none;padding:0">
			<?php else: ?>
				<tr>
			<?php endif ?>
                    <td>
                        <div class="pt8">
                    	<div class="radio-option">
                			<div class="inner"></div>
                        		<input type="radio" name="shipping_method" value="<?=$shipping_method['id']?>">
                           	<span><?=h($shipping_method['name'])?></span>
                        </div>
                        </div>
                    </td>

                    <td class="text-right pull-left-sm">
                        <div class="pt8">
                    		<?=$shipping_method['cost_info']?>
                        </div>
                    </td>

                    <td>
                        <div class="pt8">
                    		<?=h($shipping_method['description'])?>
                        </div>
                    </td>

                </tr>

            <?php endforeach ?>

        </table>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg"><?=h($submit_button_label)?></button>
        </div>
    
    <?php endif ?>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

<?php if ($currency): ?>

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

