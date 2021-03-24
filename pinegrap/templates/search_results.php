
<?=$messages?>

<form <?=$attributes?>>

    <div class="form-group input-group col-sm-4">
        <input type="search" name="<?=$page_id?>_query"
            class="form-control" placeholder="Search" autofocus>
        <span class="input-group-btn">
            <button type="submit" class="btn btn-default btn-secondary">
                <span class="glyphicon glyphicon-search"></span>
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

        <h2>Featured Results</h2>

        <div>

            <?php foreach($featured_items as $item): ?>

                <div>

                    <div>
                        <strong>
                            <a href="<?=h($item['url'])?>"><?=h($item['title'])?></a>
                        </strong>
                    </div>

                    <div>
                        <em><?=h($item['full_url'])?></em>
                    </div>

                    <?php if ($item['description']): ?>
                        <div><?=h($item['description'])?></div>
                    <?php endif ?>

                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>

    <?php if ($catalog_items): ?>

        <h2>Catalog Results</h2>

        <div>

            <?php foreach($catalog_items as $item): ?>

                <div>

                    <?php if ($item['url']): ?>
                        <a href="<?=h($item['url'])?>">
                    <?php endif ?>

                    <?php if ($item['image_url']): ?>
                        <div><img src="<?=h($item['image_url'])?>" class="img-responsive"></div>
                    <?php endif ?>

                    <?php if ($item['short_description']): ?>
                        <div><?=h($item['short_description'])?></div>
                    <?php endif ?>

                    <?php if ($item['url']): ?>
                        </a>
                    <?php endif ?>

                    <?php if ($item['price_info']): ?>
                        <div><?=$item['price_info']?></div>
                    <?php endif ?>
                    
                </div>

            <?php endforeach ?>

        </div>

    <?php endif ?>


    <?php if ($results): ?>

        <?php if ($featured_items or $catalog_items): ?>
            <h2>Other Results</h2>
        <?php endif ?>

        <div>

            <?php foreach($results as $result): ?>

                <div>

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
