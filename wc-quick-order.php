add_shortcode('quick_order_form', function () {
    ob_start(); ?>
    
    <div id="quick-order-wrapper" style="max-width:800px; margin:auto; font-family:inherit;">
        <div id="quick-order-summary" style="margin-bottom:30px; padding:20px; border:2px dashed #2d818f; border-radius:10px;">
            <h3>🛒 Items Ready to Add to Cart</h3>
            <ul id="quick-order-list" style="list-style:none; padding-left:0;">
                <li id="empty-cart-message" style="font-style:italic; color:#666;">No items selected yet. Search for products below and set quantities to add them here.</li>
            </ul>
            <div style="display:flex; align-items:center; margin-top:20px;">
                <button id="quick-order-submit" 
                    style="padding:12px 20px; background-color:#1c74bb; color:white; border:none; border-radius:8px; font-size:16px;">
                    Add All to Cart
                </button>
                <div id="loading-indicator" style="display:none; margin-left:15px;">
                    <div class="spinner" style="border:3px solid rgba(0,0,0,0.1); border-top:3px solid #2d818f; border-radius:50%; width:24px; height:24px; animation:spin 1s linear infinite;"></div>
                </div>
<div id="cart-success" style="display:none; margin-left:15px; color:#2d818f; font-weight:bold;">
    ✅ Added to cart! 
    <a href="<?php echo wc_get_cart_url(); ?>" style="color:#2d818f; text-decoration:underline; margin-left:5px;">View Cart</a>
    <span style="color:#2d818f; margin:0 5px;">or</span>
    <a href="<?php echo wc_get_checkout_url(); ?>" style="color:#2d818f; text-decoration:underline;">Checkout Now</a>
</div>
            </div>
        </div>

        <input type="text" id="quick-order-search" placeholder="Search products by name, code, or SKU (e.g. B0047)..." 
            style="width:100%; padding:12px 16px; font-size:16px; border:1px solid #ccc; border-radius:8px;">
        
        <div id="quick-order-results" style="margin-top: 20px;"></div>
    </div>

    <style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .product-code-badge {
        background-color: #f0f0f0;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: bold;
        margin-left: 10px;
    }
    .product-type-badge {
        margin-left: 10px;
        font-size: 12px;
        background-color: #eaf4f7;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .quick-order-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .quick-order-item:hover {
        background-color: #f9f9f9;
    }
    </style>

    <script>
    jQuery(function($) {
        const selectedProducts = {};
        
        // Always show the summary
        $('#quick-order-summary').show();

        $('#quick-order-search').on('input', function () {
            const term = $(this).val();
            if (term.length < 2) {
                $('#quick-order-results').empty();
                return;
            }

            $.post('<?php echo admin_url("admin-ajax.php"); ?>', {
                action: 'quick_order_search',
                term: term
            }, function (response) {
                $('#quick-order-results').html(response);
            });
        });

        // Handle quantity input for all product types
        $(document).on('input', '.quick-order-qty', function () {
            const productId = $(this).data('product-id');
            const variationId = $(this).data('variation-id') || 0;
            const productName = $(this).data('product-name');
            const variationAttributes = $(this).data('variation-attributes') || {};
            const qty = parseInt($(this).val());
            
            // Create a unique key for storage
            const cartKey = variationId > 0 ? `${productId}_${variationId}` : `${productId}`;

            if (qty > 0) {
                selectedProducts[cartKey] = { 
                    qty, 
                    productName,
                    product_id: productId,
                    variation_id: variationId,
                    attributes: variationAttributes
                };
            } else {
                delete selectedProducts[cartKey];
            }

            updateSummary();
        });

        function updateSummary() {
            const $list = $('#quick-order-list');
            $list.empty();

            const keys = Object.keys(selectedProducts);
            if (keys.length === 0) {
                $list.append(`<li id="empty-cart-message" style="font-style:italic; color:#666;">No items selected yet. Search for products below and set quantities to add them here.</li>`);
                $('#quick-order-submit').prop('disabled', true).css('opacity', '0.6');
                return;
            }

            $('#quick-order-submit').prop('disabled', false).css('opacity', '1');
            
            keys.forEach(key => {
                const item = selectedProducts[key];
                $list.append(`<li style="margin-bottom:8px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span>${item.productName}</span>
                        <span>Qty: ${item.qty} 
                            <button class="remove-item" data-key="${key}" style="background:none; border:none; color:#ff0000; cursor:pointer; margin-left:10px; font-size:18px;">×</button>
                        </span>
                    </div>
                </li>`);
            });
        }
        
        // Disable the submit button initially
        $('#quick-order-submit').prop('disabled', true).css('opacity', '0.6');
        
        // Remove item from selection
        $(document).on('click', '.remove-item', function() {
            const key = $(this).data('key');
            delete selectedProducts[key];
            updateSummary();
        });

        $('#quick-order-submit').click(function () {
            const keys = Object.keys(selectedProducts);
            if (keys.length === 0) return;

            // Show loading indicator and disable button
            $('#loading-indicator').show();
            $('#cart-success').hide();
            $(this).prop('disabled', true).css('opacity', '0.6');

            // Process all items sequentially to avoid race conditions
            processItemsSequentially(keys, 0);
        });

        function processItemsSequentially(keys, index) {
            if (index >= keys.length) {
                // All items processed
                $('#loading-indicator').hide();
                $('#cart-success').show();
                
                // Clear the selection
                for (const key in selectedProducts) {
                    delete selectedProducts[key];
                }
                updateSummary();
                
                // Trigger WooCommerce cart update
                $(document.body).trigger('added_to_cart');
                
                // Re-enable button
                $('#quick-order-submit').prop('disabled', false).css('opacity', '1');
                return;
            }

            const key = keys[index];
            const item = selectedProducts[key];
            
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: {
                    action: 'add_to_cart_quick_order',
                    product_id: item.product_id,
                    variation_id: item.variation_id,
                    quantity: item.qty,
                    variation: item.attributes
                },
                success: function (response) {
                    // Process next item whether success or error
                    processItemsSequentially(keys, index + 1);
                },
                error: function() {
                    // Continue with next item even if this one failed
                    processItemsSequentially(keys, index + 1);
                }
            });
        }
    });
    </script>

    <?php return ob_get_clean();
});

// Handle product search with support for code search in tags
add_action('wp_ajax_quick_order_search', 'handle_quick_order_search');
add_action('wp_ajax_nopriv_quick_order_search', 'handle_quick_order_search');
function handle_quick_order_search() {
    $term = sanitize_text_field($_POST['term']);
    
    // Remove any asterisks for the standard WP search
    $clean_term = str_replace('*', '', $term);
    
    // First try: standard search in titles
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 20,
        'post_status' => 'publish',
        's' => $clean_term
    );
    
    $results = array();
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            $results[$product_id] = $product_id; // Using as key to avoid duplicates
        }
    }
    
    wp_reset_postdata();
    
    // Second search: look for product tags that contain the search term (including pattern search)
    $product_tag_args = array(
        'taxonomy' => 'product_tag',
        'hide_empty' => true,
        'name__like' => $clean_term
    );
    
    $product_tags = get_terms($product_tag_args);
    
    if (!empty($product_tags) && !is_wp_error($product_tags)) {
        foreach ($product_tags as $tag) {
            // Find all products with this tag
            $tag_products = get_posts(array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_tag',
                        'field' => 'term_id',
                        'terms' => $tag->term_id
                    )
                )
            ));
            
            foreach ($tag_products as $product) {
                $results[$product->ID] = $product->ID; // Use as key to avoid duplicates
            }
        }
    }
    
    // For code search patterns like B0047
    if (preg_match('/[A-Z][0-9]+/', $term)) {
        // Get all products
        $tagged_products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_tag',
                    'field' => 'name',
                    'operator' => 'EXISTS'
                )
            )
        ));
        
        foreach ($tagged_products as $tagged_product) {
            $product_tags = wp_get_post_terms($tagged_product->ID, 'product_tag', array('fields' => 'names'));
            
            if (!empty($product_tags) && !is_wp_error($product_tags)) {
                foreach ($product_tags as $tag_name) {
                    // Check if the tag contains the code pattern
                    if (stripos($tag_name, $clean_term) !== false) {
                        $results[$tagged_product->ID] = $tagged_product->ID;
                    }
                }
            }
        }
    }
    
    // Search by SKU
    global $wpdb;
    
    // First, try exact match for SKU
    $sku_query = $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} 
        WHERE meta_key = '_sku' AND meta_value = %s",
        $clean_term
    );
    
    $sku_results = $wpdb->get_col($sku_query);
    
    if (!empty($sku_results)) {
        foreach ($sku_results as $product_id) {
            $results[$product_id] = $product_id;
        }
    }
    
    // Then try partial match for SKU
    $sku_partial_query = $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} 
        WHERE meta_key = '_sku' AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($clean_term) . '%'
    );
    
    $sku_partial_results = $wpdb->get_col($sku_partial_query);
    
    if (!empty($sku_partial_results)) {
        foreach ($sku_partial_results as $product_id) {
            $results[$product_id] = $product_id;
        }
    }
    
    // Check if we have results
    if (!empty($results)) {
        echo '<div class="quick-order-list">';
        
        foreach ($results as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product || !$product->is_in_stock()) continue;
            
            // Get product tags with highlighting for matches
            $tags = get_the_terms($product_id, 'product_tag');
            $tag_list = '';
            $product_code = '';
            
            if ($tags && !is_wp_error($tags)) {
                $tag_names = array();
                foreach ($tags as $tag) {
                    // Check if this tag contains our search term for highlighting
                    if (stripos($tag->name, $clean_term) !== false) {
                        // Highlight the matching tag
                        $tag_names[] = '<strong style="color:#2d818f;">' . esc_html($tag->name) . '</strong>';
                        
                        // If this looks like a product code (matches our pattern), extract it
                        if (preg_match('/[A-Z][0-9]+/', $tag->name, $matches)) {
                            $product_code = $matches[0];
                        }
                    } else {
                        $tag_names[] = esc_html($tag->name);
                    }
                }
                
                $tag_list = '<span class="product-tags" style="font-size:12px; color:#666; font-style:italic;">Tags: ' . 
                            implode(', ', $tag_names) . '</span>';
            }
            
            // Get the product SKU and highlight if it matches the search term
            $product_sku = $product->get_sku();
            $sku_display = '';
            
            if (!empty($product_sku)) {
                if (stripos($product_sku, $clean_term) !== false) {
                    // Highlight the SKU if it matches the search term
                    $sku_display = '<span class="product-sku" style="font-size:12px; color:#666; display:block;">
                                    <strong style="color:#2d818f;">SKU: ' . esc_html($product_sku) . '</strong></span>';
                } else {
                    // Just display the SKU normally
                    $sku_display = '<span class="product-sku" style="font-size:12px; color:#666; display:block;">
                                    SKU: ' . esc_html($product_sku) . '</span>';
                }
            }
            
            // Check if product is variable or simple
            $product_type = $product->get_type();
            
            if ($product_type === 'variable') {
                // For variable products, list each variation as a completely separate product
                $variations = $product->get_available_variations();
                
                foreach ($variations as $variation) {
                    if (!$variation['is_in_stock']) continue;
                    
                    $variation_id = $variation['variation_id'];
                    $variation_obj = wc_get_product($variation_id);
                    
                    // Skip invalid variations
                    if (!$variation_obj) continue;
                    
                    // Get variation attributes
                    $attribute_summary = array();
                    $variation_attributes = array();
                    $code_display = '';
                    
                    foreach ($variation['attributes'] as $attr_name => $attr_value) {
                        $taxonomy = str_replace('attribute_', '', $attr_name);
                        
                        // Get attribute label and value
                        $attr_label = wc_attribute_label($taxonomy);
                        
                        if (!empty($attr_value)) {
                            $term = get_term_by('slug', $attr_value, $taxonomy);
                            $attr_display_value = $term ? $term->name : $attr_value;
                            $attribute_summary[] = $attr_display_value;
                            $variation_attributes[$attr_label] = $attr_display_value;
                        }
                    }
                    
                    // Get variation-specific SKU to show as product code and highlight if it matches
                    $variation_sku = $variation_obj->get_sku();
                    $variation_sku_display = '';
                    
                    if (!empty($variation_sku)) {
                        if (stripos($variation_sku, $clean_term) !== false) {
                            $variation_sku_display = '<span class="product-sku" style="font-size:12px; color:#666; display:block;">
                                                    <strong style="color:#2d818f;">SKU: ' . esc_html($variation_sku) . '</strong></span>';
                        } else {
                            $variation_sku_display = '<span class="product-sku" style="font-size:12px; color:#666; display:block;">
                                                    SKU: ' . esc_html($variation_sku) . '</span>';
                        }
                        
                        if (preg_match('/[A-Z][0-9]+/', $variation_sku)) {
                            $code_display = '<span class="product-code-badge">Code: ' . esc_html($variation_sku) . '</span>';
                        }
                    } elseif (!empty($product_code)) {
                        $code_display = '<span class="product-code-badge">Code: ' . esc_html($product_code) . '</span>';
                    }
                    
                    // Format the variation display
                    $variation_display = '';
                    if (!empty($attribute_summary)) {
                        $variation_display = implode(', ', $attribute_summary);
                    }
                    
                    // Get variation-specific price
                    $variation_price_html = $variation_obj->get_price_html();
                    
                    // JSON encode the variation attributes for storage in data attribute
                    $variation_attrs_json = htmlspecialchars(json_encode($variation_attributes), ENT_QUOTES, 'UTF-8');
                    
                    // Create a proper name for the variation to display like in your screenshot
                    $base_name = get_the_title($product_id);
                    $display_name = $base_name;
                    
                    // Format the name to match your screenshot format (e.g., "Avian Protein Pellets 2 kg (B0012)")
                    if (!empty($variation_display)) {
                        $display_name = $base_name . ' ' . $variation_display;
                    }
                    
                    // Extract any code in parentheses
                    $code_in_parentheses = '';
                    if (!empty($variation_sku) && preg_match('/[A-Z][0-9]+/', $variation_sku)) {
                        $code_in_parentheses = ' (' . $variation_sku . ')';
                    } elseif (!empty($product_code)) {
                        $code_in_parentheses = ' (' . $product_code . ')';
                    }
                    
                    $display_name_with_code = $display_name . $code_in_parentheses;
                    
                    echo '<div class="quick-order-item">
                        <div style="flex:1;">
                            <strong>' . esc_html($display_name_with_code) . '</strong>
                            <span class="product-type-badge">' . ucfirst($product_type) . '</span><br>
                            <span>' . $variation_price_html . '</span><br>
                            ' . $variation_sku_display . '
                            ' . $tag_list . '
                        </div>
                        <div style="flex-shrink:0;">
                            <input type="number" class="quick-order-qty" 
                                data-product-id="' . $product_id . '" 
                                data-variation-id="' . $variation_id . '"
                                data-product-name="' . esc_attr($display_name_with_code) . '"
                                data-variation-attributes="' . $variation_attrs_json . '"
                                min="0" value="0" style="width:60px; padding:6px; border:1px solid #ccc; border-radius:6px;">
                        </div>
                    </div>';
                }
            } else {
                // Simple product display
                $code_display = '';
                if (!empty($product_code)) {
                    $code_display = ' (' . $product_code . ')';
                } elseif (!empty($product_sku) && preg_match('/[A-Z][0-9]+/', $product_sku)) {
                    $code_display = ' (' . $product_sku . ')';
                }
                
                echo '<div class="quick-order-item">
                    <div style="flex:1;">
                        <strong>' . get_the_title($product_id) . $code_display . '</strong>
                        <span class="product-type-badge">' . ucfirst($product_type) . '</span><br>
                        <span>' . $product->get_price_html() . '</span><br>
                        ' . $sku_display . '
                        ' . $tag_list . '
                    </div>
                    <div style="flex-shrink:0;">
                        <input type="number" class="quick-order-qty" 
                            data-product-id="' . $product->get_id() . '" 
                            data-product-name="' . esc_attr(get_the_title($product_id) . $code_display) . '"
                            min="0" value="0" style="width:60px; padding:6px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                </div>';
            }
        }
        
        echo '</div>';
    } else {
        echo '<p>No products found. Try a different search term.</p>';
    }

    wp_die();
}

// Handle custom add to cart with variation support
add_action('wp_ajax_add_to_cart_quick_order', 'quick_order_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart_quick_order', 'quick_order_add_to_cart');
function quick_order_add_to_cart() {
    $product_id = absint($_POST['product_id']);
    $variation_id = absint($_POST['variation_id']);
    $quantity = absint($_POST['quantity']);
    $variation = isset($_POST['variation']) ? (array) $_POST['variation'] : array();
    
    // Format variation data for WooCommerce
    $variation_data = array();
    foreach ($variation as $attr => $value) {
        $taxonomy = sanitize_title('pa_' . strtolower(str_replace(' ', '-', $attr)));
        $term = get_term_by('name', $value, $taxonomy);
        
        if ($term) {
            $variation_data['attribute_' . $taxonomy] = $term->slug;
        } else {
            // Try to find a matching attribute
            $product = wc_get_product($product_id);
            if ($product && $product->get_type() === 'variable') {
                foreach ($product->get_attributes() as $attr_key => $attr_obj) {
                    if (wc_attribute_label($attr_key) === $attr) {
                        $variation_data['attribute_' . $attr_key] = $value;
                        break;
                    }
                }
            }
        }
    }

    // Add to cart
    if ($product_id > 0 && $quantity > 0) {
        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data);
        if ($added) {
            wp_send_json_success();
        }
    }

    wp_send_json_error();
}