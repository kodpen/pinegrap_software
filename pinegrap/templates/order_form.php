
<?=$messages?>

<?php
    // If there are products that are allowed to be added to the cart,
    // then start the form.
    if ($available_products):
?>
    <form <?=$attributes?>>
<?php endif ?>

<?php if ($product_layout == 'list'): ?>

    <table class="table mobile_stacked">

        <thead>

            <tr>
                <th>Item</th>

                <th>Description</th>

                <th class="text-right">
                    <?php if ($available_donations and !$available_non_donations): ?>
                        Amount
                    <?php else: ?>
                        Price
                    <?php endif ?>
                </th>

                <th class="text-center">
                    <?php if ($checkbox_selections and $quantity_selections): ?>
                        Select/Qty
                    <?php elseif ($checkbox_selections): ?>
                        Select
                    <?php elseif ($quantity_selections): ?>
                        Qty
                    <?php endif ?>
                </th>

            </tr>

        </thead>

        <?php foreach($products as $product): ?>

            <tr>

                <td>
                    <span class="visible-xs-inline">Item:</span>
                    
                    <?=h($product['name'])?>

                    <?=$product['edit'] // Add edit button in edit mode ?>
                </td>

                <td>

                    <?php
                        // If this product has an image, then start row structure
                        // and output column for image.
                        if ($product['image_url']):
                    ?>

                        <div class="row">

                            <div class="col-xs-6">

                                <?php
                                    // The containers around the image fixes Firefox
                                    // issue with responsive images in tables.
                                ?>
                                <div class="responsive_table_image_1">
                                    <div class="responsive_table_image_2">
                                        <img src="<?=h($product['image_url'])?>" class="img-responsive img-fluid img-rounded center-block">
                                    </div>
                                </div>

                            </div>

                            <div class="col-xs-6">

                    <?php endif ?>

                    <?php
                        // You can choose to show the short description or full description,
                        // by removing the one below, that you don't want.
                    ?>

                    <span style="font-weight:bold"><?=h($product['short_description'])?></span>

                    <?=$product['full_description']?>

                    <?php
                        // If there was an image shown for this product, then close
                        // column and row structure.
                        if ($product['image_url']):
                    ?>

                            </div>

                        </div>

                    <?php endif ?>

                    <?php if ($product['recurring_schedule']): ?>

                        <fieldset>

                            <legend>Payment Schedule</legend>

                            <div class="form-group">

                                <label for="recurring_payment_period_<?=$product['id']?>">
                                    Frequency*
                                </label>

                                <select name="recurring_payment_period_<?=$product['id']?>" id="recurring_payment_period_<?=$product['id']?>" class="form-control"></select>

                            </div>

                            <div class="form-group">

                                <label for="recurring_number_of_payments_<?=$product['id']?>">
                                    Number of Payments<?php if ($number_of_payments_required): ?>*<?php endif ?>
                                </label>

                                <input type="number" name="recurring_number_of_payments_<?=$product['id']?>" id="recurring_number_of_payments_<?=$product['id']?>" class="form-control">

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

                                    <label for="recurring_start_date_<?=$product['id']?>">
                                        Start Date*
                                    </label>

                                    <input type="text" name="recurring_start_date_<?=$product['id']?>" id="recurring_start_date_<?=$product['id']?>" class="form-control">

                                </div>

                            <?php endif ?>

                        </fieldset>

                    <?php endif ?>

                </td>

                <td class="text-right">

                    <?php if ($product['selection_type'] == 'donation'): ?>

                        <?php if ($product['available']): ?>

                            <div class="form-group">

                                <label for="donation_<?=$product['id']?>" class="visible-xs-inline-block">
                                    Amount
                                </label>

                                <div class="input-group">

                                    <span class="input-group-addon"><?=$currency_symbol?></span>

                                    <input type="number" step="any" name="donation_<?=$product['id']?>" id="donation_<?=$product['id']?>" class="form-control" style="min-width: 6em">

                                    <?php if ($currency_code): ?>
                                        <span class="input-group-addon"><?=h($currency_code)?></span>
                                    <?php endif ?>

                                </div>

                            </div>

                        <?php endif ?>

                    <?php else: ?>
                        <?=$product['price_info']?>
                    <?php endif ?>

                </td>

                <td class="text-center">

                    <?php if ($product['available']): ?>

                        <?php if ($product['selection_type'] == 'checkbox'): ?>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="product_<?=$product['id']?>" value="1">
                                    <span class="visible-xs-inline">
                                        Select
                                    </span>
                                </label>
                            </div>

                        <?php elseif ($product['selection_type'] == 'quantity'): ?>

                            <div class="form-group">
                                <label for="product_<?=$product['id']?>" class="visible-xs-inline-block">
                                    Qty
                                </label>
                                <input type="number" name="product_<?=$product['id']?>" id="product_<?=$product['id']?>" class="form-control" style="min-width: 5em">
                            </div>

                        <?php endif ?>

                    <?php endif ?>

                </td>

            </tr>

        <?php endforeach ?>

    </table>

<?php
    // Otherwise, the product layout is drop-down selection,
    // so show pick list of products.
    else:
?>

    <div class="form-group">
        <label for="product_id">Item</label>
        <select name="product_id" id="product_id" class="form-control"></select>
    </div>

<?php endif ?>

<?php if ($recipient): ?>

    <div class="form-group">
        <label for="ship_to">Ship to</label>
        <select name="ship_to" id="ship_to" class="form-control"></select>
    </div>

    <div class="form-group">
        <label for="add_name">or add name</label>
        <input type="text" name="add_name" id="add_name" class="form-control" placeholder="Example: Tom">
    </div>

<?php endif ?>

<?php if ($quantity): ?>
    <div class="form-group">
        <label for="quantity">Qty</label>
        <input type="number" name="quantity" id="quantity" value="1" min="1" class="form-control">
    </div>
<?php endif ?>

<?php
    // If there are products that are allowed to be added to the cart,
    // then show buttons and close form.
    if ($available_products):
?>

        <div class="form-group">

            <button type="submit" class="btn btn-primary">
                <?=h($add_button_label)?>
            </button>

            <?php if ($skip_button_url): ?>
                <a href="<?=h($skip_button_url)?>" class="btn btn-default btn-secondary">
                    <?=h($skip_button_label)?>
                </a>
            <?php endif ?>

        </div>

        <?=$system // Required hidden fields and JS (do not remove) ?>

    </form>

<?php
    // Otherwise, there are no products that are allowed to be
    // added to the cart, so if there is a skip button, then show that.
    elseif ($skip_button_url):
?>
    <div class="form-group">
        <a href="<?=h($skip_button_url)?>" class="btn btn-default btn-secondary">
            <?=h($skip_button_label)?>
        </a>
    </div>
<?php endif ?>

<?php if ($currency): ?>

    <form <?=$currency_attributes?>>

        <div class="form-group">
            <label for="currency_id" class="sr-only">Currency</label>
            <select name="currency_id" id="currency_id" class="form-control"></select>
        </div>

        <?=$currency_system // Required hidden fields and JS (do not remove) ?>

    </form>

<?php endif ?>