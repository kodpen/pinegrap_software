/**
 *
 * liveSite - Enterprise Website Platform
 * 
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

// Used by things like a myself upsell to add a product to the cart.  Only supports adding to myself
// recipient for now.

software.add_to_cart = {};

software.add_to_cart.init = function(request) {

    request = request || {};

    var class_name = request.class_name;

    if (!class_name) {
        class_name = 'add-to-cart';
    }

    // Update all add-to-cart buttons so that a product is added when button is clicked.
    software_$('.' + class_name).each(function() {

        var button = software_$(this);
        var product_id = button.data('product-id');

        // If there is no product defined in the button, then we don't need to do anything,
        // so skip to next button.
        if (!product_id) {
            return true;
        }

        var product = {};
        product.id = product_id;

        // When the add-to-cart button is clicked, then send API request to add item to cart.
        button.click(function() {

            var add_to_cart = software_$.ajax({
                contentType: 'application/json',
                url: software_path + software_directory + '/api.php',
                data: JSON.stringify({
                    action: 'add_to_cart',
                    product: product,
                    form: request.form,
                    notice: request.notice}),
                type: 'POST'});

            add_to_cart.done(function(response) {
                if (typeof request.complete == 'function') {
                    request.complete();
                }
            });
        });

        // Fade in the button in case it has CSS to hide it until it is ready.
        button.fadeIn();
    });
};