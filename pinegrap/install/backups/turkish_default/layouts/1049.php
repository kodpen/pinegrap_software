<div class="widget">
    <h6 class="title"><?=h($album_name)?></h6>
    <hr>
	<?php if (!$photos): ?>
   		<p>Sorry, no photos where found.</p>  
	<?php else: ?>
    	<ul class="gallery masonry masonryFlyIn">
    	<?php if ($photos): ?>
    		<?php foreach($photos as $photo): ?>        
        	<li class="masonry-item mb0">
            	<a href="<?=h($photo['url'])?>" data-lightbox="true" data-title="<?=h($photo['description'])?>">
                	<img alt="<?=h($photo['name'])?>" src="<?=h($photo['url'])?>"/>
            	</a>
        	</li>
     		<?php endforeach ?>
  		<?php endif ?>
    	</ul>
	<?php endif ?>           
</div>