
<?=$messages?>

<div class="row product-single">

    <div class="col-sm-12">
        
        <?php if ($back_button_url): ?>
        <div class="form-group text-right">
        	<a href="<?=h($back_button_url)?>" class="btn btn-sm btn-icon">
            	<span class="ti-arrow-up"></span>
            </a>
        </div>
        <?php endif ?>    

        <div class="row">

            <div class="col-sm-6">
                <?php // The image class allows the attribute system to change the image. image-url allows us to hide the image in the code area of the product as needed ?>
                <img src="<?=h($image_url)?>" class="image img-responsive center-block image-url">
                <div class="code"><?=$code?></div>
            </div>

            <div class="col-sm-6">
                
                <div class="description">
                  
                <h4 class="short_description uppercase"><?=h($short_description)?></h4>

                <div class="full_description"><?=$full_description?></div>

                <div class="mb32 mb-xs-24 number price"><?=$price_info?></div>
                    
                <hr class="mb24">
                 
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
                            
                            <div class="select-option">
    							<i class="ti-angle-down"></i>
                            	<select name="attribute_<?=$attribute['id']?>" id="attribute_<?=$attribute['id']?>"></select>
                            </div>

                            <span class="clear input-group-btn" title="Temizle">
                        		<button type="button" class="btn btn-secondary btn-thin" style="width:69px; border-width:1px" >
                            		<i class="ti-close" style="font-size:16px"></i>
                        		</button>
                    		</span>
                        </div>

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
                            <label for="product_id">&Uuml;r&uuml;n</label>
                            <select name="product_id" id="product_id"></select>
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

                            <p><strong>&Uuml;r&uuml;n:</strong> <?=$products[0]['description']?></p>

                        <?php
                            // Otherwise there are multiple products, so output a list of them.
                            else:
                        ?>

                            <p><strong>&Uuml;r&uuml;nler:</strong></p>

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

                            <div class="row">
                                <div class="col-md-6">
                        			<div class="form-group">
                                		<label for="ship_to">Al&#305;c&#305;</label>
                                			<div class="select-option">
												<i class="ti-angle-down"></i>
                                				<select name="ship_to" id="ship_to"></select>
                                			</div>
                            		</div>
                            	</div>

                        		<div class="col-md-6">
                            		<div class="form-group">
                                		<label for="add_name">yada isim ekle</label>
                                			<input type="text" name="add_name" id="add_name" placeholder="&Ouml;rne&#287;in: &quot;Annem&quot;">
                            		</div>
                        		</div>
                        	</div>

                        <?php endif ?>

                        <div class="form-group add-to-cart">
                            
                        <?php if ($quantity): ?>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="999999999" placeholder="Miktar">
                        <?php endif ?>

                  		<button type="submit" class="btn btn-primary" style="margin-bottom:0"><?=h($add_button_label)?></button> 

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

        </div>

        <!--
			Setup a container for the cross-sell section. We reference the id further below in
            the JS to load the cross-sell. This cross-sell section is hidden by default in the theme
			CSS. The JS further below will automatically show the cross-sell section if there are items.
		-->
        <div id="cross-sell" class="cross-sell">

            <h4>Customers who bought this item also bought</h4>
            
            <!--
				Cross-sell uses the "items" class below to find the container for all items.
				This container can be a div instead of a ul.
			-->
            <ul class="items row item-grid">
                
                <!--
					Setup an HTML template for a single item. Cross-sell uses the "item" class below
					to find the template.  This can be setup as a div instead of an li. The "link",
					"image", "description", and "price" classes below are required in order for the
					cross-sell to dynamically update them.
				-->
                <li class="item col-xs-6 col-sm-4">
                    <a class="link">
                        <div><img class="image"></div>
                        <div class="description"></div>
                    </a>
                    <div class="price"></div>
                </li>
            </ul>
        </div>

        <div class="details mt32"><?=$details?></div>

        <?=
            // JS for attributes. Must be placed below all elements that are updated by attribute
    		// system (e.g. details, code). (do not remove)
            $footer_system
        ?>
        
        <!--
			Load the cross-sell via JS. This code will load the cross-sell JS module, make an API
			request for the cross-sell items, and then update the cross-sell section accordingly.
			It is necessary to load this code below the $footer_system JS code for attributes above.
		-->
        <script>
            software.load({
                module: 'cross_sell',
                complete: function() {
                    software.cross_sell.init({
                        
                        // This should match the cross-sell container id in the HTML above.
                        id: 'cross-sell',
						
                        // Set which product/group the cross-sell should use to find related products.
                        <?php if ($item['type'] == 'product group'): ?>
                        	product_group: {id: <?=json_encode($item['id'])?>},
                        <?php else: ?>
                        	products: [{id: <?=json_encode($item['id'])?>}],
                        <?php endif ?>
						
                        // Set the catalog detail page that the items should be linked to.
                        catalog_detail_page: {id: <?=json_encode($page_id)?>},
                                       
                        // Enter the max number of cross-sell items you want to appear.
                        number_of_items: 3,
                        
                        // Update the cross-sell items when the visitor selects product attributes,
						// related to products that were matched by the visitor's selection.
                        attribute_update: true
                    });
                }
            });
        </script>

    </div>

</div>
