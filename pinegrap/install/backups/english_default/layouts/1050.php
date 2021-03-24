<div class="widget">
    <h6 class="title">Shop Our Catalog</h6>
    <hr>
	<?php if (!$photos): ?>
   		<p>Sorry, no photos where found.</p>  
	<?php else: ?>
        <div class="image-slider slider-all-controls">
        	<ul class="slides">
    		<?php if ($photos): ?>
    			<?php foreach($photos as $photo): ?>        
        		<li>
                    <a href="{path}shop-sidebar"><img alt="<?=h($photo['name'])?>" src="<?=h($photo['url'])?>"/></a>
        		</li>
     			<?php endforeach ?>
  			<?php endif ?>
    		</ul>
        </div>
	<?php endif ?>           
</div>