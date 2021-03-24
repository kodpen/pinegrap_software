/* This front-end theme is provided free of charge, as-is, 
as a working example of how you can take any HTML template
and apply it to the liveSite back-end.
*/

var mr_firstSectionHeight,
    mr_nav,
    mr_navOuterHeight,
    mr_navScrolled = false,
    mr_navFixed = false,
    mr_outOfSight = false,
    mr_floatingProjectSections,
    mr_scrollTop = 0;

$(document).ready(function() { 
    "use strict";
    
    // liveSite mods
    
    // convert liveSite menu regions to use this design's mega-menu styling and behavior
    //   use #mega-menu and #hide on parent menu items and #title on children menu items
    $('ul.software_menu').addClass('menu'); 
    $('ul.software_menu > li.parent').addClass('has-dropdown');     
    $('ul.software_menu li.parent a[href*="#mega-menu"]').siblings().addClass('mega-menu'); 
    $('ul.software_menu li a[href*="#title"]').addClass('title');
    $('ul.software_menu li a[href*="#hide"]').css('display','none');
    $('ul.software_menu').css('visibility','visible'); /* show menu region after initialized */
    
    // style software messages
    $('.software_error').addClass('alert').addClass('alert-danger').attr('role','alert');
    $('.software_notice').addClass('alert').addClass('alert-success').attr('role','alert');
    
    // show cart button if cart not empty
    $('div.software_cart_region.full a').prepend('<i class="ti-shopping-cart">&nbsp;</i>');
    $('div.software_cart_region.full a').addClass('btn').addClass('btn-primary');
    
    // convert all primary and secondary (default) buttons to bootstrap 
    $('.software_button_primary').addClass('btn').addClass('btn-primary');
    $('.software_input_submit_primary').addClass('btn').addClass('btn-primary');
    $('.software_button_secondary').addClass('btn');
    $('.software_input_submit_secondary').addClass('btn');
    $('.button_3d_secondary').addClass('btn').addClass('btn-xs').addClass('btn-thin');
    $('.software_button_small_secondary').addClass('btn').addClass('btn-xs');
    $('.software_input_submit_small_secondary').addClass('btn');
    $('.software_button_tiny_secondary').addClass('btn').addClass('btn-xs');
    $('.software_input_submit_tiny_secondary').addClass('btn').addClass('btn-secondary').addClass('btn-thin');
    
    // comments
    $('input.add_comment').addClass('btn').addClass('btn-primary');
    $('.name_line .name').addClass('bold-h6');
    $('.software_attachment a:first-child').prepend('<i class="ti-clip"></i>');
    
    // file upload fields
    $('input[type="file"]').wrap('<div class="input-group file-upload"><span class="input-group-btn"><span class="btn btn-secondary btn-file btn-sm btn-thin"><i class="ti-clip"></i> Attach a File</span></span></div>');
    $('div.input-group.file-upload').append('<input type="text" class="file-upload-text" readonly>');
    
    // scheduled comments
    var labeltxt = $('.publish_schedule input.software_input_checkbox + label').html();
    $('.publish_schedule input.software_input_checkbox + label').remove();
    $('.publish_schedule input#publish_cancel').wrap('<label class="check-box"></label>');
    $('.publish_schedule label.check-box').append('<span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span><span>' + labeltxt + '</span>');
    
    // form item view notify
    var labeltxt = $('input#watcher + label').html();
    $('input#watcher + label').remove();
    $('input#watcher').wrap('<div class="watcher mb24" style="padding-left: 4px"><label class="check-box"></label></div>');
    $('.watcher label.check-box').append('<span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span><span>' + labeltxt + '</span>');
    
    // calendar pages
    $('.software_calendar select').wrap('<div class="select-option"></div>');
    $('.software_calendar .select-option').prepend('<i class="ti-angle-down"></i>')
    $('.software_calendar .software_input_submit_small_secondary').addClass('btn-thin');
    
    // affiliate signup pages
    var labeltxt = $('.software_affiliate_sign_up_form input.software_input_checkbox + label').html();
    $('.software_affiliate_sign_up_form input.software_input_checkbox + label').remove();
    $('.software_affiliate_sign_up_form input.software_input_checkbox').wrap('<label class="check-box"></label>');
    $('.software_affiliate_sign_up_form label.check-box').append('<span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span><span>' + labeltxt + '</span>');
    $('.software_affiliate_sign_up_form select').wrap('<div class="select-option"></div>');
    $('.software_affiliate_sign_up_form .select-option').prepend('<i class="ti-angle-down"></i>')
    $('.software_affiliate_sign_up_form .software_input_submit_small_secondary').addClass('btn-thin');    
    
    // style upcoming concerts widget
    $('.upcoming-concerts-widget .software_calendar_view .calendar_7 a').addClass('btn').addClass('btn-white');
    $('.upcoming-concerts-widget .software_calendar_view .upcoming p > span').wrap('<div class="col-sm-6 text-center-sm"></div>');
    
    // set checkbox defaults on page load
    $('label.check-box').each(function(){
        var checkbox = $(this).find('input');
        if (checkbox.prop('checked') === true) {
			$(this).addClass('active');
        }
    });
    
    // set radio button defaults on page load
   	$('.radio-option input').each(function() {
        var checked = $(this).attr('checked');
        if (checked == 'checked') {
       		$(this).parent().addClass('checked');
        }
    });
    
    // style select in comments
    $('select#publish').removeAttr('size').wrap('<div class="form-group"><div class="select-option publish"></div></div>');
    $('.select-option.publish').prepend('<i class="ti-angle-down"></i>');
    
    $('.add_comment_form select.software_select').wrap('<div class="select-option"></div>');
    $('.add_comment_form .select-option').prepend('<i class="ti-angle-down"></i>')
    
    // form view directory username search feature
    $('.form_view_directory_search form').removeClass("mobile_align_left");
    $('.form_view_directory_search .simple').css("display","block");
    $('.form_view_directory_search .simple input[type="text"]').css("width","30%");
    $('.form_view_directory_search .simple input[type="submit"]').attr("value","Search");
    $('.form_view_directory_search .simple input[type="submit"]').addClass('btn').addClass('btn-primary').addClass('btn-thin').css("margin-bottom","0px");
    
    // reinit flexslider if product attribute changes (must use software_$ namespace)
    software_$('.software_catalog_detail .code').on('content_change', function() {
   		// reinit flexslider for catalog detail page silders (if present)
   		$('.slider-thumb-controls .slides li').each(function() {
        	var imgSrc = $(this).find('img').attr('src');
        	$(this).attr('data-thumb', imgSrc);
    	});
    	$('.slider-thumb-controls').flexslider({
        	animation: "slide",
        	controlNav: "thumbnails",
        	directionNav: true
    	});
        $('.software_catalog_detail .slides img').css("visibility","visible").hide().fadeIn('slow'); // show image after reinit
	}); 
    
    
    
    // add bootstrap responsive media classes to any paragraph marked with special custom format
    $('p.embed-responsive').addClass("embed-video-container").addClass("embed-responsive").addClass("embed-responsive-16by9");
    $('p.embed-responsive embed').addClass("embed-responsive-item");
    $('p.embed-responsive object').addClass("embed-responsive-item");
    $('p.embed-responsive video').addClass("embed-responsive-item");
    $('p.embed-responsive iframe').addClass("embed-responsive-item");

    // replace image icon with font icon
    $('.software_rss_link a img').css("display","none").wrap('<div title="Subscribe to RSS feed" class="ti-rss icon-xs"></div>');
    
    // end of liveSite mods  //
    
    
   // scroll up and down long pages
    
   $(window).scroll(function(){
        if ($(this).scrollTop() > 100) {
            $('.scrollup').fadeIn();
            $('.scrolldn').fadeIn();
        } else {
            $('.scrollup').fadeOut();
            $('.scrolldn').fadeOut();
        }
    });
    $('.scrollup').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 1000);
        return false;
    });
    $('.scrolldn').click(function(){
        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
        return false;
    });
    
    // Smooth scroll to inner links
    
    $('.inner-link').each(function(){
        var href = $(this).attr('href');
        if(href.charAt(0) !== "#"){
            $(this).removeClass('inner-link');
        }
    });

	if($('.inner-link').length){
		$('.inner-link').smoothScroll({
			offset: -55,
			speed: 800
		});
    }

    // Update scroll variable for scrolling functions

    addEventListener('scroll', function() {
        mr_scrollTop = window.pageYOffset;
    }, false);

    // Append .background-image-holder <img>'s as CSS backgrounds

    $('.background-image-holder').each(function() {
        var imgSrc = $(this).children('img').attr('src');
        $(this).css('background', 'url("' + imgSrc + '")');
        $(this).children('img').hide();
        $(this).css('background-position', 'initial');
    });

    // Fade in background images

    setTimeout(function() {
        $('.background-image-holder').each(function() {
            $(this).addClass('fadeIn');
        });
    }, 200);

    // Initialize Tooltips

    $('[data-toggle="tooltip"]').tooltip();

    // Icon bulleted lists

    $('ul[data-bullet]').each(function(){
        var bullet = $(this).attr('data-bullet');
        $(this).find('li').prepend('<i class="'+bullet+'"></i>');
    });

    // Progress Bars

    $('.progress-bar').each(function() {
        $(this).css('width', $(this).attr('data-progress') + '%');
    });

    // Navigation

    if (!$('nav').hasClass('fixed') && !$('nav').hasClass('absolute')) {

        // Make nav container height of nav

        $('.nav-container').css('min-height', $('nav').outerHeight(true));

        $(window).resize(function() {
            $('.nav-container').css('min-height', $('nav').outerHeight(true));
        });

        // Compensate the height of parallax element for inline nav

        if ($(window).width() > 768) {
            $('.parallax:nth-of-type(1) .background-image-holder').css('top', -($('nav').outerHeight(true)));
        }

        // Adjust fullscreen elements

        if ($(window).width() > 768) {
            $('section.fullscreen:nth-of-type(1)').css('height', ($(window).height() - $('nav').outerHeight(true)));
        }

    } else {
        $('body').addClass('nav-is-overlay');
    }

    if ($('nav').hasClass('bg-dark')) {
        $('.nav-container').addClass('bg-dark');
    }


    // Fix nav to top while scrolling

    mr_nav = $('body .nav-container nav:first');
    mr_navOuterHeight = $('body .nav-container nav:first').outerHeight();
    window.addEventListener("scroll", updateNav, false);

    // Menu dropdown initial positioning (v2017.3)
    $('.menu').position_dropdowns();
      
    // Menu dropdown repositioning if device orientation changes
    window.document.addEventListener('orientationchange', function() {
       $('.menu').position_dropdowns();
	}, false);


    // Mobile Menu

    $('.mobile-toggle').click(function() {
        $('.nav-bar').toggleClass('nav-open');
        $(this).toggleClass('active');
    });

    $('.menu li').click(function(e) {
        if (!e) e = window.event;
        e.stopPropagation();
        if ($(this).find('ul').length) {
            $(this).toggleClass('toggle-sub');
        } else {
            $(this).parents('.toggle-sub').removeClass('toggle-sub');
        }
    });

    $('.menu li a').click(function() {
        if ($(this).hasClass('inner-link')){
            $(this).closest('.nav-bar').removeClass('nav-open');
        }
    });

    $('.module.widget-handle').click(function() {
        $(this).toggleClass('toggle-widget-handle');
    });

    $('.search-widget-handle .search-form input').click(function(e){
        if (!e) e = window.event;
        e.stopPropagation();
    });
    
    // Offscreen Nav
    
    if($('.offscreen-toggle').length){
    	$('body').addClass('has-offscreen-nav');
    }
    else{
        $('body').removeClass('has-offscreen-nav');
    }
    
    $('.offscreen-toggle').click(function(){
    	$('.main-container').toggleClass('reveal-nav');
    	$('nav').toggleClass('reveal-nav');
    	$('.offscreen-container').toggleClass('reveal-nav');
    });
    
    $('.main-container').click(function(){
    	if($(this).hasClass('reveal-nav')){
    		$(this).removeClass('reveal-nav');
    		$('.offscreen-container').removeClass('reveal-nav');
    		$('nav').removeClass('reveal-nav');
    	}
    });
    
    $('.offscreen-container a').click(function(){
    	$('.offscreen-container').removeClass('reveal-nav');
    	$('.main-container').removeClass('reveal-nav');
    	$('nav').removeClass('reveal-nav');
    });

    // Populate filters
    
    $('.projects').each(function() {

        var filters = "";

        $(this).find('.project').each(function() {

            var filterTags = $(this).attr('data-filter').split(',');

            filterTags.forEach(function(tagName) {
                if (filters.indexOf(tagName) == -1) {
                    filters += '<li data-filter="' + tagName + '">' + capitaliseFirstLetter(tagName) + '</li>';
                }
            });
            $(this).closest('.projects')
                .find('ul.filters').empty().append('<li data-filter="all" class="active">All</li>').append(filters);
        });
    });

    $('.filters li').click(function() {
        var filter = $(this).attr('data-filter');
        $(this).closest('.filters').find('li').removeClass('active');
        $(this).addClass('active');

        $(this).closest('.projects').find('.project').each(function() {
            var filters = $(this).attr('data-filter');

            if (filters.indexOf(filter) == -1) {
                $(this).addClass('inactive');
            } else {
                $(this).removeClass('inactive');
            }
        });

        if (filter == 'all') {
            $(this).closest('.projects').find('.project').removeClass('inactive');
        }
    });

    // Twitter Feed
       jQuery('.tweets-feed').each(function(index) {
           jQuery(this).attr('id', 'tweets-' + index);
       }).each(function(index) {
           
           var TweetConfig = {
               "id": jQuery('#tweets-' + index).attr('data-widget-id'),
               "domId": '',
               "maxTweets": jQuery('#tweets-' + index).attr('data-amount'),
               "enableLinks": true,
               "showUser": true,
               "showTime": true,
               "dateFunction": '',
               "showRetweet": false,
               "customCallback": handleTweets
           };
           function handleTweets(tweets) {
               var x = tweets.length;
               var n = 0;
               var element = document.getElementById('tweets-' + index);
               var html = '<ul class="slides">';
               while (n < x) {
                   html += '<li>' + tweets[n] + '</li>';
                   n++;
               }
               html += '</ul>';
               element.innerHTML = html;
               return html;
           }
           twitterFetcher.fetch(TweetConfig);
       });

    // Instagram Feed
    
    if($('.instafeed').length){
    	jQuery.fn.spectragram.accessData = {
			accessToken: '1406933036.fedaafa.feec3d50f5194ce5b705a1f11a107e0b',
			clientID: 'fedaafacf224447e8aef74872d3820a1'
		};	

        $('.instafeed').each(function() {
            var feedID = $(this).attr('data-user-name');
            $(this).children('ul').spectragram('getUserFeed', {
                query: feedID,
                max: 12
            });
        });
    }

    // Flickr Feeds

    if($('.flickr-feed').length){
        $('.flickr-feed').each(function(){
            var userID = $(this).attr('data-user-id');
            var albumID = $(this).attr('data-album-id');
            $(this).flickrPhotoStream({ id: userID, setId: albumID, container: '<li class="masonry-item" />' });   
            setTimeout(function(){
                initializeMasonry();
                window.dispatchEvent(new Event('resize'));
            }, 1000); 
        });

    }

    // Image Sliders (don't run animation if liveSite edit mode)

    if( $('div.edit_mode').length ) {
	    $('.slider-all-controls').flexslider({
        	slideshow: false
    	});
    } else {
    	$('.slider-all-controls').flexslider({
        	start: function(slider){
            	if(slider.find('.slides li:first-child').find('.fs-vid-background video').length){
               	slider.find('.slides li:first-child').find('.fs-vid-background video').get(0).play(); 
            	}
        	},
        	after: function(slider){
            	if(slider.find('.fs-vid-background video').length){
                	if(slider.find('li:not(.flex-active-slide)').find('.fs-vid-background video').length){
                    	slider.find('li:not(.flex-active-slide)').find('.fs-vid-background video').get(0).pause();
                	}
                	if(slider.find('.flex-active-slide').find('.fs-vid-background video').length){
                    	slider.find('.flex-active-slide').find('.fs-vid-background video').get(0).play();
                	}
            	}
        	}
    	});
    }
    
    $('.slider-paging-controls').flexslider({
        animation: "slide",
        directionNav: false
    });
    $('.slider-arrow-controls').flexslider({
        controlNav: false
    });
    $('.slider-thumb-controls .slides li').each(function() {
        var imgSrc = $(this).find('img').attr('src');
        $(this).attr('data-thumb', imgSrc);
    });
    $('.slider-thumb-controls').flexslider({
        animation: "slide",
        controlNav: "thumbnails",
        directionNav: true
    });

    $('.software_catalog_detail .slides img').css("visibility","visible").hide().fadeIn('slow'); // show image after init
    
    $('.logo-carousel').flexslider({
        minItems: 1,
        maxItems: 4,
        move: 1,
        itemWidth: 200,
        itemMargin: 0,
        animation: "slide",
        slideshow: true,
        slideshowSpeed: 3000,
        directionNav: false,
        controlNav: false
    });
    
    // Lightbox gallery titles
    
    $('.lightbox-grid li a').each(function(){
    	var galleryTitle = $(this).closest('.lightbox-grid').attr('data-gallery-title');
    	$(this).attr('data-lightbox', galleryTitle);
    });

    
    // Prepare embedded video modals

    $('iframe[data-provider]').each(function(){
        var provider = jQuery(this).attr('data-provider');
        var videoID = jQuery(this).attr('data-video-id');
        var autoplay = jQuery(this).attr('data-autoplay');
        var vidURL = '';

        if(provider == 'vimeo'){
            vidURL = "http://player.vimeo.com/video/"+videoID+"?badge=0&title=0&byline=0&title=0&autoplay="+autoplay;
            $(this).attr('data-src', vidURL);
        }else if (provider == 'youtube'){
            vidURL = "https://www.youtube.com/embed/"+videoID+"?showinfo=0&autoplay="+autoplay;
            $(this).attr('data-src', vidURL);
        }else{
            console.log('Only Vimeo and Youtube videos are supported at this time');
        }
    });
    
    // Multipurpose Modals
    
    jQuery('.site_modal[modal-link]').remove();

    if($('.site_modal').length && (!jQuery('.modal-screen').length)){
        // Add a div.modal-screen if there isn't already one there.
        var modalScreen = jQuery('<div />').addClass('modal-screen').appendTo('body');

    }

    jQuery('.site_modal').click(function(){
        jQuery(this).addClass('modal-acknowledged');
    });

    jQuery(document).on('wheel mousewheel scroll', '.site_modal, .modal-screen', function(evt){
        $(this).get(0).scrollTop += (evt.originalEvent.deltaY); 
        return false;
    });
    
    $('.modal-container:not([modal-link])').each(function(index) {
        if(jQuery(this).find('iframe[src]').length){
        	jQuery(this).find('.site_modal').addClass('iframe-modal');
        	var iframe = jQuery(this).find('iframe');
        	iframe.attr('data-src',iframe.attr('src'));
            iframe.attr('src', '');

        }
        jQuery(this).find('.btn-modal').attr('modal-link', index);

        // Only clone and append to body if there isn't already one there
        if(!jQuery('.site_modal[modal-link="'+index+'"]').length){
            jQuery(this).find('.site_modal').clone().appendTo('body').attr('modal-link', index).prepend(jQuery('<i class="ti-close close-modal">'));
        }
    });
    
    $('.btn-modal').unbind('click').click(function(){
    	var linkedModal = jQuery('.site_modal[modal-link="' + jQuery(this).attr('modal-link') + '"]'),
            autoplayMsg = "";
        jQuery('.modal-screen').toggleClass('reveal-modal');
        if(linkedModal.find('iframe').length){
            if(linkedModal.find('iframe').attr('data-autoplay') === '1'){
                var autoplayMsg = '&autoplay=1'
            }
        	linkedModal.find('iframe').attr('src', (linkedModal.find('iframe').attr('data-src') + autoplayMsg));
        }
        linkedModal.toggleClass('reveal-modal');
        return false; 
    });
    
    // Autoshow modals
	
	$('.site_modal[data-time-delay]').each(function(){
		var modal = $(this);
		var delay = modal.attr('data-time-delay');
		modal.prepend($('<i class="ti-close close-modal">'));
    	if(typeof modal.attr('data-cookie') != "undefined"){
        	if(!mr_cookies.hasItem(modal.attr('data-cookie'))){
                setTimeout(function(){
        			modal.addClass('reveal-modal');
        			$('.modal-screen').addClass('reveal-modal');
        		},delay);
            }
        }else{
            setTimeout(function(){
                modal.addClass('reveal-modal');
                $('.modal-screen').addClass('reveal-modal');
            },delay);
        }
	});

    // Autoclose modals

    $('.site_modal[data-hide-after]').each(function(){
        var modal = $(this);
        var delay = modal.attr('data-hide-after');
        if(typeof modal.attr('data-cookie') != "undefined"){
            if(!mr_cookies.hasItem(modal.attr('data-cookie'))){
                setTimeout(function(){
                if(!modal.hasClass('modal-acknowledged')){
                    modal.removeClass('reveal-modal');
                    $('.modal-screen').removeClass('reveal-modal');
                }
                },delay); 
            }
        }else{
            setTimeout(function(){
                if(!modal.hasClass('modal-acknowledged')){
                    modal.removeClass('reveal-modal');
                    $('.modal-screen').removeClass('reveal-modal');
                }
            },delay); 
        }
    });
    
    jQuery('.close-modal:not(.modal-strip .close-modal)').unbind('click').click(function(){
    	var modal = jQuery(this).closest('.site_modal');
        modal.toggleClass('reveal-modal');
        if(typeof modal.attr('data-cookie') !== "undefined"){
            mr_cookies.setItem(modal.attr('data-cookie'), "true", Infinity);
        }
    	if(modal.find('iframe').length){
            modal.find('iframe').attr('src', '');
        }
        jQuery('.modal-screen').removeClass('reveal-modal');
    });
    
    jQuery('.modal-screen').unbind('click').click(function(){
        if(jQuery('.site_modal.reveal-modal').find('iframe').length){
            jQuery('.site_modal.reveal-modal').find('iframe').attr('src', '');
        }
    	jQuery('.site_modal.reveal-modal').toggleClass('reveal-modal');
    	jQuery(this).toggleClass('reveal-modal');
    });
    
    jQuery(document).keyup(function(e) {
		 if (e.keyCode == 27) { // escape key maps to keycode `27`
            if(jQuery('.site_modal').find('iframe').length){
                jQuery('.site_modal').find('iframe').attr('src', '');
            }
			jQuery('.site_modal').removeClass('reveal-modal');
			jQuery('.modal-screen').removeClass('reveal-modal');
		}
	});
    
    // Modal Strips
    
    jQuery('.modal-strip').each(function(){
    	if(!jQuery(this).find('.close-modal').length){
    		jQuery(this).append(jQuery('<i class="ti-close close-modal">'));
    	}
    	var modal = jQuery(this);

        if(typeof modal.attr('data-cookie') != "undefined"){
           
            if(!mr_cookies.hasItem(modal.attr('data-cookie'))){
            	setTimeout(function(){
            		modal.addClass('reveal-modal');
            	},1000);
            }
        }else{
            setTimeout(function(){
                    modal.addClass('reveal-modal');
            },1000);
        }
    });
    
    jQuery('.modal-strip .close-modal').click(function(){
        var modal = jQuery(this).closest('.modal-strip');
        if(typeof modal.attr('data-cookie') != "undefined"){
            mr_cookies.setItem(modal.attr('data-cookie'), "true", Infinity);
        }
    	jQuery(this).closest('.modal-strip').removeClass('reveal-modal');
    	return false;
    });

    // Video Modals

    jQuery('.close-iframe').click(function() {
        jQuery(this).closest('.modal-video').removeClass('reveal-modal');
        jQuery(this).siblings('iframe').attr('src', '');
        jQuery(this).siblings('video').get(0).pause();
    });

    // Checkboxes
        
    $('label.check-box').on("click",function() {
        var checkbox = $(this).find('input');
        if (checkbox.prop('checked') === false) {
            checkbox.prop('checked', true);
            $(this).addClass('active');
        } else {
            checkbox.prop('checked', false);
            $(this).removeClass('active');
        }
    });

    // Radio Buttons

    $('.radio-option').click(function() {
        var checked = $(this).hasClass('checked'); // Get the current status of the radio
        var name = $(this).find('input').attr('name'); // Get the name of the input clicked
        if (!checked) {
            var name = $(this).find('input').attr('name'); // Get the name of the input clicked
            $('input[name="'+name+'"]').parent().removeClass('checked');
            $(this).addClass('checked');
            $(this).find('input').prop('checked', true);     	
        }
        software_$(this).find('input[name="'+name+'"]').trigger('click'); // trigger liveSite open/close animations  
    });
    
    // File Upload Fields (added)
    $(document).on('change', '.btn-file :file', function() {
  		var input = $(this),
      	numFiles = input.get(0).files ? input.get(0).files.length : 1,
      	label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  		input.trigger('fileselect', [numFiles, label]);
	});
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {   
    	var input = $(this).parents('.input-group').find(':text'),
        log = numFiles > 1 ? numFiles + ' files selected' : label;
       	if( input.length ) {
        	input.val(log);
       	} else {
            if( log ) alert(log);
        }  
    });

    // Accordions

    $('.accordion li').click(function() {
        if ($(this).closest('.accordion').hasClass('one-open')) {
            $(this).closest('.accordion').find('li').removeClass('active');
            $(this).addClass('active');
        } else {
            $(this).toggleClass('active');
        }
        if(typeof window.mr_parallax !== "undefined"){
            setTimeout(mr_parallax.windowLoad, 500);
        }
    });

    // Tabbed Content

    $('.tabbed-content').each(function() {
        $(this).append('<ul class="content"></ul>');
    });

    $('.tabs li').each(function() {
        var originalTab = $(this),
            activeClass = "";
        if (originalTab.is('.tabs>li:first-child')) {
            activeClass = ' class="active"';
        }
        var tabContent = originalTab.find('.tab-content').detach().wrap('<li' + activeClass + '></li>').parent();
        originalTab.closest('.tabbed-content').find('.content').append(tabContent);
    });

    $('.tabs li').click(function() {
        $(this).closest('.tabs').find('li').removeClass('active');
        $(this).addClass('active');
        var liIndex = $(this).index() + 1;
        $(this).closest('.tabbed-content').find('.content>li').removeClass('active');
        $(this).closest('.tabbed-content').find('.content>li:nth-of-type(' + liIndex + ')').addClass('active');
    });

    // Local Videos

    $('section').closest('body').find('.local-video-container .play-button').click(function() {
        $(this).siblings('.background-image-holder').removeClass('fadeIn');
        $(this).siblings('.background-image-holder').css('z-index', -1);
        $(this).css('opacity', 0);
        $(this).siblings('video').get(0).play();
    });

    // Youtube Videos

    $('section').closest('body').find('.player').each(function() {
        var section = $(this).closest('section');
        section.find('.container').addClass('fadeOut');
        var src = $(this).attr('data-video-id');
        var startat = $(this).attr('data-start-at');
        $(this).attr('data-property', "{videoURL:'http://youtu.be/" + src + "',containment:'self',autoPlay:true, mute:true, startAt:" + startat + ", opacity:1, showControls:false}");
    });

	if($('.player').length){
        $('.player').each(function(){

            var section = $(this).closest('section');
            var player = section.find('.player');
            player.YTPlayer();
            player.on("YTPStart",function(e){
                section.find('.container').removeClass('fadeOut');
                section.find('.masonry-loader').addClass('fadeOut');
            });

        });
    }

    // Interact with Map once the user has clicked (to prevent scrolling the page = zooming the map

    $('.map-holder').click(function() {
        $(this).addClass('interact');
    });
    
    if($('.map-holder').length){
    	$(window).scroll(function() {
			if ($('.map-holder.interact').length) {
				$('.map-holder.interact').removeClass('interact');
			}
		});
    }
    
    // Countdown Timers

    if ($('.countdown').length) {
        $('.countdown').each(function() {
            var date = $(this).attr('data-date');
            $(this).countdown(date, function(event) {
                $(this).text(
                    event.strftime('%D days %H:%M:%S')
                );
            });
        });
    }
    

    // Get referrer from URL string 
    if (getURLParameter("ref")) {
        $('form.form-email').append('<input type="text" name="referrer" class="hidden" value="' + getURLParameter("ref") + '"/>');
    }

    function getURLParameter(name) {
        return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
    }

    // Disable parallax on mobile

    if ((/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i).test(navigator.userAgent || navigator.vendor || window.opera)) {
        $('section').removeClass('parallax');
    }
    

    // Load Google MAP API JS with callback to initialise when fully loaded
    if(document.querySelector('[data-maps-api-key]') && !document.querySelector('.gMapsAPI')){
        if($('[data-maps-api-key]').length){
            var script = document.createElement('script');
            var apiKey = $('[data-maps-api-key]:first').attr('data-maps-api-key');
            script.type = 'text/javascript';
            script.src = 'https://maps.googleapis.com/maps/api/js?key='+apiKey+'&callback=initializeMaps';
            script.className = 'gMapsAPI';
            document.body.appendChild(script);  
        } 
    }

}); 

$(window).load(function() { 
    "use strict";

    // Initialize Masonry

    setTimeout(initializeMasonry, 1000);

    // Initialize twitter feed

    var setUpTweets = setInterval(function() {
        if ($('.tweets-slider').find('li.flex-active-slide').length) {
            clearInterval(setUpTweets);
            return;
        } else {
            if ($('.tweets-slider').length) {
                $('.tweets-slider').flexslider({
                    directionNav: false,
                    controlNav: false
                });
            }
        }
    }, 500);

    mr_firstSectionHeight = $('.main-container section:nth-of-type(1)').outerHeight(true);


}); 

function updateNav() {

    var scrollY = mr_scrollTop;

    if (scrollY <= 0) {
        if (mr_navFixed) {
            mr_navFixed = false;
            mr_nav.removeClass('fixed');
        }
        if (mr_outOfSight) {
            mr_outOfSight = false;
            mr_nav.removeClass('outOfSight');
        }
        if (mr_navScrolled) {
            mr_navScrolled = false;
            mr_nav.removeClass('scrolled');
        }
        return;
    }

    if (scrollY > mr_firstSectionHeight) {
        if (!mr_navScrolled) {
            mr_nav.addClass('scrolled');
            mr_navScrolled = true;
            return;
        }
    } else {
        if (scrollY > mr_navOuterHeight) {
            if (!mr_navFixed) {
                mr_nav.addClass('fixed');
                mr_navFixed = true;
            }

            if (scrollY > mr_navOuterHeight * 2) {
                if (!mr_outOfSight) {
                    mr_nav.addClass('outOfSight');
                    mr_outOfSight = true;
                }
            } else {
                if (mr_outOfSight) {
                    mr_outOfSight = false;
                    mr_nav.removeClass('outOfSight');
                }
            }
        } else {
            if (mr_navFixed) {
                mr_navFixed = false;
                mr_nav.removeClass('fixed');
            }
            if (mr_outOfSight) {
                mr_outOfSight = false;
                mr_nav.removeClass('outOfSight');
            }
        }

        if (mr_navScrolled) {
            mr_navScrolled = false;
            mr_nav.removeClass('scrolled');
        }

    }
}

function capitaliseFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function initializeMasonry(){
    $('.masonry').each(function(){
        var container = $(this).get(0);
        var msnry = new Masonry(container, {
            itemSelector: '.masonry-item'
        });

        msnry.on('layoutComplete', function() {

            mr_firstSectionHeight = $('.main-container section:nth-of-type(1)').outerHeight(true);

            // Fix floating project filters to bottom of projects container

            if ($('.filters.floating').length) {
                setupFloatingProjectFilters();
                updateFloatingFilters();
                window.addEventListener("scroll", updateFloatingFilters, false);
            }

            $('.masonry').addClass('fadeIn');
            $('.masonry-loader').addClass('fadeOut');
            if ($('.masonryFlyIn').length) {
                masonryFlyIn();
            }
        });

        msnry.layout();
    });
}

function masonryFlyIn() {
    var $items = $('.masonryFlyIn .masonry-item');
    var time = 0;

    $items.each(function() {
        var item = $(this);
        setTimeout(function() {
            item.addClass('fadeIn');
        }, time);
        time += 170;
    });
}

function setupFloatingProjectFilters() {
    mr_floatingProjectSections = [];
    $('.filters.floating').closest('section').each(function() {
        var section = $(this);

        mr_floatingProjectSections.push({
            section: section.get(0),
            outerHeight: section.outerHeight(),
            elemTop: section.offset().top,
            elemBottom: section.offset().top + section.outerHeight(),
            filters: section.find('.filters.floating'),
            filersHeight: section.find('.filters.floating').outerHeight(true)
        });
    });
}

function updateFloatingFilters() {
    var l = mr_floatingProjectSections.length;
    while (l--) {
        var section = mr_floatingProjectSections[l];

        if ((section.elemTop < mr_scrollTop) && typeof window.mr_variant == "undefined" ) {
            section.filters.css({
                position: 'fixed',
                top: '16px',
                bottom: 'auto'
            });
            if (mr_navScrolled) {
                section.filters.css({
                    transform: 'translate3d(-50%,48px,0)'
                });
            }
            if (mr_scrollTop > (section.elemBottom - 70)) {
                section.filters.css({
                    position: 'absolute',
                    bottom: '16px',
                    top: 'auto'
                });
                section.filters.css({
                    transform: 'translate3d(-50%,0,0)'
                });
            }
        } else {
            section.filters.css({
                position: 'absolute',
                transform: 'translate3d(-50%,0,0)'
            });
        }
    }
}

window.initializeMaps = function(){
    if(typeof google !== "undefined"){
        if(typeof google.maps !== "undefined"){
            $('.map-canvas[data-maps-api-key]').each(function(){
                    var mapInstance   = this,
                        mapJSON       = typeof $(this).attr('data-map-style') !== "undefined" ? $(this).attr('data-map-style'): false,
                        mapStyle      = JSON.parse(mapJSON) || [{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}],
                        zoomLevel     = (typeof $(this).attr('data-map-zoom') !== "undefined" && $(this).attr('data-map-zoom') !== "") ? $(this).attr('data-map-zoom') * 1: 17,
                        latlong       = typeof $(this).attr('data-latlong') != "undefined" ? $(this).attr('data-latlong') : false,
                        latitude      = latlong ? 1 *latlong.substr(0, latlong.indexOf(',')) : false,
                        longitude     = latlong ? 1 * latlong.substr(latlong.indexOf(",") + 1) : false,
                        geocoder      = new google.maps.Geocoder(),
                        address       = typeof $(this).attr('data-address') !== "undefined" ? $(this).attr('data-address').split(';'): false,
                        markerTitle   = "We Are Here",
                        isDraggable = $(document).width() > 766 ? true : false,
                        map, marker, markerImage,
                        mapOptions = {
                            draggable: isDraggable,
                            scrollwheel: false,
                            zoom: zoomLevel,
                            disableDefaultUI: true,
                            styles: mapStyle
                        };

                    if($(this).attr('data-marker-title') != undefined && $(this).attr('data-marker-title') != "" )
                    {
                        markerTitle = $(this).attr('data-marker-title');
                    }

                    if(address != undefined && address[0] != ""){
                            geocoder.geocode( { 'address': address[0].replace('[nomarker]','')}, function(results, status) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                var map = new google.maps.Map(mapInstance, mapOptions); 
                                map.setCenter(results[0].geometry.location);
                                
                                address.forEach(function(address){
                                    var markerGeoCoder;
                                    
                                    markerImage = {url: window.mr_variant == undefined ? '{path}mapmarker.png' : '{path}mapmarker.png', size: new google.maps.Size(50,50), scaledSize: new google.maps.Size(50,50)};
                                    if(/(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)/.test(address) ){
                                        var latlong = address.split(','),
                                        marker = new google.maps.Marker({
                                                        position: { lat: 1*latlong[0], lng: 1*latlong[1] },
                                                        map: map,
                                                        icon: markerImage,
                                                        title: markerTitle,
                                                        optimised: false
                                                    });
                                    }
                                    else if(address.indexOf('[nomarker]') < 0){
                                        markerGeoCoder = new google.maps.Geocoder();
                                        markerGeoCoder.geocode( { 'address': address.replace('[nomarker]','')}, function(results, status) {
                                            if (status == google.maps.GeocoderStatus.OK) {
                                                marker = new google.maps.Marker({
                                                    map: map,
                                                    icon: markerImage,
                                                    title: markerTitle,
                                                    position: results[0].geometry.location,
                                                    optimised: false
                                                });
                                            }
                                        });
                                    }

                                });
                            } else {
                                console.log('There was a problem geocoding the address.');
                            }
                        });
                    }
                    else if(latitude != undefined && latitude != "" && latitude != false && longitude != undefined && longitude != "" && longitude != false ){
                        mapOptions.center   = { lat: latitude, lng: longitude};
                        map = new google.maps.Map(mapInstance, mapOptions); 
                        marker              = new google.maps.Marker({
                                                    position: { lat: latitude, lng: longitude },
                                                    map: map,
                                                    icon: markerImage,
                                                    title: markerTitle
                                                });

                    }

                }); 
        }
    }
}
initializeMaps();

// End of Maps


/*\
|*|  COOKIE LIBRARY THANKS TO MDN
|*|
|*|  A complete cookies reader/writer framework with full unicode support.
|*|
|*|  Revision #1 - September 4, 2014
|*|
|*|  https://developer.mozilla.org/en-US/docs/MDN/About#Copyrights_and_licenses
|*|  "Code samples added on or after August 20, 2010 are in the public domain."
|*|  https://creativecommons.org/publicdomain/zero/1.0/
|*|
|*|  Syntaxes:
|*|
|*|  * mr_cookies.setItem(name, value[, end[, path[, domain[, secure]]]])
|*|  * mr_cookies.getItem(name)
|*|  * mr_cookies.removeItem(name[, path[, domain]])
|*|  * mr_cookies.hasItem(name)
|*|  * mr_cookies.keys()
|*|
\*/

var mr_cookies = {
  getItem: function (sKey) {
    if (!sKey) { return null; }
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
  },
  setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
    if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
    var sExpires = "";
    if (vEnd) {
      switch (vEnd.constructor) {
        case Number:
          sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
          break;
        case String:
          sExpires = "; expires=" + vEnd;
          break;
        case Date:
          sExpires = "; expires=" + vEnd.toUTCString();
          break;
      }
    }
    document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
    return true;
  },
  removeItem: function (sKey, sPath, sDomain) {
    if (!this.hasItem(sKey)) { return false; }
    document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "");
    return true;
  },
  hasItem: function (sKey) {
    if (!sKey) { return false; }
    return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
  },
  keys: function () {
    var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
    for (var nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
    return aKeys;
  }
};

// function to position dropdown menus based on screen width (v2017.3)
$.fn.position_dropdowns = function(){
    $('.menu > li > ul').each(function() {
        var menu = $(this).offset();
        var farRight = menu.left + $(this).outerWidth(true);
        if (farRight > $(window).width() && !$(this).hasClass('mega-menu')) {
            $(this).addClass('make-right');
        } else if (farRight > $(window).width() && $(this).hasClass('mega-menu')) {
            var isOnScreen = $(window).width() - menu.left;
            var difference = $(this).outerWidth(true) - isOnScreen;
            $(this).css('margin-left', -(difference));
        }
    });
};