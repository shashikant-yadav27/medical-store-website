// assets/js/main.js

$(document).ready(function() {
    
    // Live Search functionality
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        let query = $(this).val().trim();
        
        if (query.length > 2) {
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: 'ajax/search.php',
                    method: 'GET',
                    data: { q: query },
                    success: function(response) {
                        $('#searchSuggestions').html(response).show();
                    }
                });
            }, 300);
        } else {
            $('#searchSuggestions').hide();
        }
    });

    // Hide search suggestions on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#searchInput, #searchSuggestions').length) {
            $('#searchSuggestions').hide();
        }
    });

    // Add to Cart functionality
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        let productId = $(this).data('id');
        let quantity = $(this).data('quantity') || 1;
        let btn = $(this);
        let originalText = btn.html();

        btn.html('<i class="spinner-border spinner-border-sm"></i> Adding...');
        btn.prop('disabled', true);

        $.ajax({
            url: 'ajax/cart.php',
            method: 'POST',
            data: { 
                action: 'add',
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Update cart count
                    $('#cart-count').text(response.cart_count);
                    
                    // Show success toast (using Bootstrap Toast - assume we have one in footer later, or alert for now)
                    alert('Product added to cart!');
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Something went wrong. Please try again.');
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });
});
