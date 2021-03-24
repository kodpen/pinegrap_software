<?php if ($albums): ?>
        <div class="row">
            <div class="col-sm-12 text-center">
                <ul class="filters floating cast-shadow mb0">
                </ul>
        	</div>
        </div>
<?php endif ?>

<?php if ($back_button_url): ?>
                
        <div class="row mt24" style="padding: 0 16px">
            <div class="col-sm-6 pull-left">
                <h4><?=h($album_name)?></h4>
			</div>
            <div class="col-sm-6 text-right">
            	<a href="<?=h($back_button_url)?>" class="btn btn-sm btn-icon">
                 	<span class="ti-arrow-up"></span>
               	</a>
            </div>
    	</div>

<?php else: ?>

		<?php if (!$albums and $photos): ?>
        <div class="row">
        	<div class="col-sm-12">
            	<h4><?=h($album_name)?></h4>
			</div>
    	</div>
		<?php endif ?>
                
<?php endif ?>
   
        
<?php if (!$albums and !$photos): ?>

    	<div class="row">
    		<div class="col-sm-12">
                <p> Maalesef <strong> <?=h($album_name)?></strong> klasöründe fotoğraf albümü veya fotoğraf bulunamadı.</p>
			</div>
    	</div>
        
<?php else: ?>

        <div class="row masonry-loader">
            <div class="col-sm-12 text-center">
                <div class="spinner"></div>
            </div>
        </div>

    	<div class="row masonry masonryFlyIn">

    	<?php if ($albums): ?>
            <?php foreach($albums as $album): ?>

           	<div class="col-sm-6 masonry-item project" data-filter="<?=h($album['name'])?>">
                <div class="image-tile inner-title hover-reveal text-center">
                    <a href="<?=h($album['url'])?>">
                        <div class="album-button">
                            <span class="ti-layout-grid3"></span>
                        </div>
                        <img alt="<?=h($album['name'])?>" src="<?=h($album['image_url'])?>" />
                        <div class="title">
                            <h5 class="uppercase mb0"><?=h($album['name'])?></h5>
                            <span>(<?=number_format($album['number_of_photos'])?> Fotoğraf)</span>
                        </div>
                    </a>
                </div>
            </div>

        <?php endforeach ?>
    	<?php endif ?>

    	<?php if ($photos): ?>
            <?php foreach($photos as $photo): ?>
                
            <div class="col-sm-6 masonry-item project" data-filter="Photos">
                <div class="image-tile inner-title hover-reveal text-center">
                    <a href="<?=h($photo['url'])?>" data-lightbox="true" data-title="<?=h($photo['description'])?>">              
                        <img alt="<?=h($photo['name'])?>" src="<?=h($photo['url'])?>"/>
                        <div class="title">
                            <span><?=h($photo['description'])?></span>
                        </div>
                    </a>
                </div>
            </div>

            <?php endforeach ?>
    	<?php endif ?>
            
		</div>
               
<?php endif ?>