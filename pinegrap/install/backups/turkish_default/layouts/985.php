
<?=$messages?>
    
<a name="catalog-top"></a>

<div class="row fadeIn">

    <div class="col-sm-9 col-sm-push-3">
        
        <form <?=$search_attributes?>>
            
            <div class="widget" style="margin-bottom: 0">
            	<h6 class="title">KATALOGDA ARA</h6>
                <hr>

            	<div class="form-group input-group">   
                	<span class="input-group-btn" title="Ara">
                    	<button type="submit" name="<?=$page_id?>_submit" style="width:69px" class="btn btn-primary btn-thin">
                        	<i style="font-size:16px" class="ti-search"></i>
                    	</button>
                	</span>

                	<input type="text" name="<?=$page_id?>_query" class="form-control" placeholder="Arama">

                <?php if ($query != ''): ?>
                    <span class="input-group-btn" title="Temizle">
                        <button type="submit" name="<?=$page_id?>_clear" class="btn btn-secondary btn-thin" style="width:3em;padding:0">
                            <i class="ti-close"></i>
                        </button>
                    </span>
                <?php endif ?>
                
            	</div>    
            </div>

            <!-- Required hidden fields (do not remove) -->
            <?=$search_system?>

        </form>

        <?=$edit_start // Add edit button and grid around product group in edit mode ?>

        <?=$full_description?>

        <?php if ($items): ?>
        
        	<div class="masonry-loader" style="position:relative">
				<div class="text-center">
					<div class="spinner"></div>
    			</div>
			</div>

            <?php if ($mode == 'search'): ?>
                <h5>
					<strong><?=h($query)?></strong> i&ccedil;eren <?=number_format($number_of_items)?> &uuml;r&uuml;n bulundu.
                </h5>
            <?php endif ?>

            <div class="masonry" style="position: relative; height: 966px;">
                
                <?php foreach($items as $item): ?>

                    <div class="col-md-4 col-sm-4 masonry-item col-xs-12" style="position: absolute; left: 0px; top: 0px;">

                        <?=$item['edit_start'] // Add edit button and grid around item in edit mode ?>

                        <?php if ($item['url']): ?>
                            <a class="text-center" href="<?=h($item['url'])?>#catalog-top">
                        <?php endif ?>

                        <?php if ($item['image_url']): ?>
                            <div class="image-tile outer-title text-center">
                                <img class="product-thumb" src="<?=h($item['image_url'])?>" class="img-responsive">
                                
                            </div>
                        <?php endif ?>

                        <?php if ($item['short_description']): ?>
                            <div class="title text-center"><h5 class="mb0"><?=h($item['short_description'])?></h5></div>
                        <?php endif ?>

                        <?php if ($item['url']): ?>
                            </a>
                        <?php endif ?>

                        <?php if ($item['price_info']): ?>
                            <div class="mb16 text-center"><?=$item['price_info']?></div>
                        <?php endif ?>

                        <?=$item['edit_end'] // Close the edit grid ?>
                        
                    </div>

                <?php endforeach ?>

            </div>

        <?php else: // Otherwise no items were found, so output a message. ?>

            <?php if ($mode == 'browse'): ?>

                <h5>Üzgünüz, bu ürün grubunda ürün bulunamadı.</h5>

            <?php else: // Otherwise the mode is search ?>

        <h5>&Uuml;zg&uuml;n&uuml;z, <strong><?=h($query)?></strong> i&ccedil;eren &uuml;r&uuml;n bulunamad&#305;.</h5>

            <?php endif ?>

        <?php endif ?>

        <!-- HTML or JS from the product group's code field (e.g. tracking, remarketing) -->
        <?=$code?>

        <?=$edit_end // Close the edit grid ?>

    </div>

    <div class="col-sm-3 col-sm-pull-9">

        <div class="widget">
        	<h6 class="title">KATALO&#286;A G&Ouml;Z AT</h6>
            <hr>
            
        <nav>
            <ul class="nav nav-stacked link-list">
                <?php foreach($product_groups as $product_group): ?>
                    <li
                        <?php if ($product_group['current']): ?>
                            class="active"
                        <?php endif ?>
                    >
                        <a href="<?=h($product_group['url'])?>#catalog-top"
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
                    <div class="select-option">
						<i class="ti-angle-down"></i>
                    	<select name="currency_id" id="currency_id"></select>
                    </div>
                </div>

                <!-- Required hidden fields and JS (do not remove) -->
                <?=$currency_system?>

            </form>

        <?php endif ?>
        
	</div>

</div>