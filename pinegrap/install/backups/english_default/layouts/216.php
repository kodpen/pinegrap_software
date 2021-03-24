
<?=$messages?>
    
<form <?=$attributes?>>

    <div class="form-group input-group">
      <input type="text" name="<?=$page_id?>_query" class="form-control" placeholder="Search Site" autofocus>
      <span class="input-group-btn">
          <button class="btn btn-primary btn-thin" style="width:69px" type="submit">
              <i class="ti-search" style="font-size:16px"></i>
          </button>
      </span>
    </div>

    <!-- Required hidden fields (do not remove) -->
    <?=$system?>

</form>

<?php if ($query == ''): ?>

    <p><strong>Please enter a keyword or phrase to search.</strong></p>

<?php elseif ($number_of_results == 0): ?>
    
    <p><strong>No results were found for: <?=h($query)?></strong></p>

<?php else: ?>

    <?php if ($limited): ?>
        <p>
            <strong>Showing <?=number_format($number_of_results)?> of the most
            relevant results for: <?=h($query)?></strong>
        </p>
    <?php else: ?>
        <p>
            <strong>Found <?=number_format($number_of_results)?>
            result<?php if ($number_of_results > 1): ?>s<?php endif ?> for:
            <?=h($query)?></strong>
        </p>
    <?php endif ?>

    <?php if ($featured_items): ?>

        <h4>Featured Results</h4>

        <div>

            <?php foreach($featured_items as $item): ?>

                <div class="mb24">

                    <div>
                        <strong>
							<a href="<?=h($item['url'])?>"><?=h($item['title'])?></a>
                        </strong>
                    </div>

                    <div>
                        <em><?=h($item['full_url'])?></em>
                    </div>

                    <?php if ($item['description']): ?>
                        <div>
                            <?=h($item['description'])?>
                        </div>
                    <?php endif ?>
                    
                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>

    <?php if ($catalog_items): ?>

        <h4 class="mt24">Shop Results</h4>

      	<div class="masonry-loader" style="position:relative">
			<div class="text-center">
				<div class="spinner"></div>
    		</div>
		</div>

        <div class="masonry" style="position: relative; height: 966px;">

            <?php foreach($catalog_items as $item): ?>

                <div class="masonry-item col-sm-6 col-xs-12" style="position: absolute; left: 0px; top: 0px;">

                    <?php if ($item['url']): ?>
                        <a class="text-center" href="<?=h($item['url'])?>">
                    <?php endif ?>

                    <?php if ($item['image_url']): ?>
                        <div class="image-tile outer-title text-center">
                            <img src="<?=h($item['image_url'])?>" class="img-responsive">
                        </div>
                    <?php endif ?>

                    <?php if ($item['short_description']): ?>
                        <div class="text-center"><?=h($item['short_description'])?></div>
                    <?php endif ?>

                    <?php if ($item['url']): ?>
                        </a>
                    <?php endif ?>

                    <?php if ($item['price_info']): ?>
                        <div class="text-center"><?=$item['price_info']?></div>
                    <?php endif ?>
                    
                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>


    <?php if ($results): ?>

        <?php if ($featured_items or $catalog_items): ?>
            <h4 class="mt8">Other Results</h4>
        <?php endif ?>

        <div>

            <?php foreach($results as $result): ?>

                <div class="mb24">

                    <div>
                        <strong>
                            <a href="<?=h($result['url'])?>"><?=h($result['title'])?></a>
                        </strong>
                    </div>

                    <div><em><?=h($result['full_url'])?></em></div>

                    <?php if ($result['description']): ?>
                        <div><?=h($result['description'])?></div>
                    <?php endif ?>
                    
                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>

<?php endif ?>