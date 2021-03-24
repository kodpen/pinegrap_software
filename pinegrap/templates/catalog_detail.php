
<?=$messages?>

<div class="row">

    <div class="col-sm-9 col-sm-push-3">

        <div class="row">

            <div class="col-sm-6">
                <?php // The image class allows the attribute system to change the image. ?>
                <img src="<?=h($image_url)?>" class="image img-responsive img-rounded center-block">
            </div>

            <div class="col-sm-6">

                <?php
                    // You can choose to show the short description or full description,
                    // by removing the one below, that you don't want.
                ?>

                <div class="short_description"><?=h($short_description)?></div>

                <div class="full_description"><?=$full_description?></div>

                <?php if ($keywords): ?>
                    <div class="keywords">
                        Keywords:

                        <?php foreach($keywords as $keyword): ?>
                            <a href="<?=h($keyword['url'])?>" class="btn btn-default btn-secondary btn-xs">
                                <?=h($keyword['keyword'])?>
                            </a>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>

                <div class="price"><?=$price_info?></div>

                <?php
                    // If there are products that are allowed to be added to the cart,
                    // then start the form.
                    if ($available_products):
                ?>
                    <form <?=$form_attributes?>>
                <?php endif ?>

                <?php
                    // Loop through the product attributes in order to output
                    // a row for each attribute.
                    foreach($attributes as $attribute):
                ?>

                    <div class="attribute_<?=$attribute['id']?> attribute_row form-group">

                        <label for="attribute_<?=$attribute['id']?>">
                            <?=h($attribute['label'])?>
                        </label>

                        <?php
                            // The "width: 100%" fixes a Bootstrap issue where the
                            // select was not 100% width when the clear button was hidden.
                        ?>
                        <div class="input-group" style="width: 100%">
                            <select name="attribute_<?=$attribute['id']?>" id="attribute_<?=$attribute['id']?>" class="form-control"></select>

                            <span class="clear input-group-btn" title="Clear">
                                <button type="button" class="btn btn-default btn-secondary">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button>
                            </span>
                        </div>

                        <?php
                            // If you want the attribute options to appear as buttons instead of
                            // options in a pick list, then you can use code like below instead of
                            // the <select> code above.
                            
                            /*
                            <?php foreach($attribute['options'] as $option): ?>
                                <?php if (!$option['no_value']): ?>
                                    <button type="button" class="option_<?=$option['id']?> option"><?=h($option['label'])?></button>
                                <?php endif ?>
                            <?php endforeach ?>

                            <input type="hidden" name="attribute_<?=$attribute['id']?>">
                            */
                        ?>

                    </div>

                <?php endforeach ?>

                <?php
                    // The product row is shown when a product group is being shown,
                    // there is at least one product in that product group, and
                    // there are no attributes.
                    if ($product_row):
                ?>

                    <?php
                        // If there are available products and there is more than 1 product
                        // then show pick list of products to allow customer to select product.
                        if ($product_pick_list):
                    ?>

                        <div class="form-group">
                            <label for="product_id">Item</label>
                            <select name="product_id" id="product_id" class="form-control"></select>
                        </div>

                    <?php
                        // Otherwise there are no available products or there is just 1
                        // available product, so just output text list of product(s).
                        else:
                    ?>

                        <?php
                            // If there is only 1 product, then just output that 1 product.
                            if (count($products) == 1):
                        ?>

                            <p><strong>Item:</strong> <?=$products[0]['description']?></p>

                        <?php
                            // Otherwise there are multiple products, so output a list of them.
                            else:
                        ?>

                            <p><strong>Items:</strong></p>

                            <ul>
                                <?php foreach($products as $product): ?>
                                    <li><?=$product['description']?></li>
                                <?php endforeach ?>
                            </ul>

                        <?php endif ?>

                    <?php endif ?>

                <?php endif ?>

                <?php
                    // If there are products that are allowed to be added to the cart,
                    // then output recipient & quantity rows, buttons, and close form.
                    if ($available_products):
                ?>

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

                        <div class="form-group">

                            <button type="submit" class="btn btn-primary"><?=h($add_button_label)?></button>

                            <?php if ($back_button_url): ?>
                                <a href="<?=h($back_button_url)?>" class="btn btn-default btn-secondary">
                                    <?=h($back_button_label)?>
                                </a>
                            <?php endif ?>

                        </div>

                        <?=$system // Required hidden fields and JS (do not remove) ?>

                    </form>

                <?php
                    // Otherwise, there are no products that are allowed to be
                    // added to the cart, so just output the back button, if necessary.
                    else:
                ?>

                    <?php if ($back_button_url): ?>
                        <div class="form-group">
                            <a href="<?=h($back_button_url)?>" class="btn btn-default btn-secondary">
                                <?=h($back_button_label)?>
                            </a>
                        </div>
                    <?php endif ?>

                <?php endif ?>

            </div>

        </div>

        <div class="details"><?=$details?></div>

        <div class="code"><?=$code?></div>

        <?=
            // JS for attributes. Must be placed below all elements that are
            // updated by attribute system (e.g. details, code). (do not remove)
            $footer_system
        ?>

    </div>

    <div class="col-sm-3 col-sm-pull-9">

        <form <?=$search_attributes?>>

            <div class="form-group input-group">

                <span class="input-group-btn" title="Search">
                    <button type="submit" name="<?=$catalog_page['id']?>_submit" class="btn btn-default btn-secondary">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>

                <input type="search" name="<?=$catalog_page['id']?>_query"
                    class="form-control" placeholder="Search">
                
            </div>

        </form>

        <h3>Categories</h3>

        <nav>
            <ul class="nav nav-pills nav-stacked">
                <?php foreach($product_groups as $product_group): ?>
                    <li
                        <?php if ($product_group['current']): ?>
                            class="active"
                        <?php endif ?>
                    >
                        <a href="<?=h($product_group['url'])?>"
                            <?php if ($product_group['level']): ?>
                                style="padding-left: <?=(2*$product_group['level'])?>em"
                            <?php endif ?>
                        >
                            <?=h($product_group['name'])?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </nav>

        <br>

        <?php if ($currency): ?>

            <form <?=$currency_attributes?>>

                <div class="form-group">
                    <label for="currency_id" class="sr-only">Currency</label>
                    <select name="currency_id" id="currency_id" class="form-control"></select>
                </div>

                <?=$currency_system // Required hidden fields and JS (do not remove) ?>

            </form>

        <?php endif ?>

    </div>

</div>
