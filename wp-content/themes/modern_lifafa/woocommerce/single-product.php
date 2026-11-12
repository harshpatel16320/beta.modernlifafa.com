<?php

/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

get_header('shop'); ?>

<?php
/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */

use AmpProject\Validator\Spec\Tag\Em;

// do_action( 'woocommerce_before_main_content' );
$gift_for = isset($_GET['gift_for']) ? intval($_GET['gift_for']) : 0;
$gift_for_user_id = isset($_GET['gift_for']) ? intval($_GET['gift_for']) : 0;
$user_exists = $gift_for_user_id ? get_user_by('ID', $gift_for_user_id) : false;
$logged_in = is_user_logged_in();
// $is_out_of_stock = !$product->is_in_stock();
global $product;
$product = wc_get_product(get_the_ID());
$is_out_of_stock = $product ? !$product->is_in_stock() : true;

$user_id = '';
if (is_user_logged_in()) {
	$user_id = get_current_user_id();
}
$disable_class = ($gift_for && !$logged_in) ? ['disabled'] : ['qty'];
if (function_exists('wc_print_notices')) {
	wc_print_notices();
}

?>
<section class="our-products-section">
	<div class="container">
		<?php if (is_user_logged_in()) { ?>
			<a href="<?php echo home_url() . '/wishlist'; ?>" class="personalised-page" title="Go to Personalised Page">Go
				to Wishlist</a>
		<?php } ?>
	</div>
</section>

<?php while (have_posts()): ?>
	<?php the_post(); ?>

	<?php //wc_get_template_part( 'content', 'single-product' ); 
	// global $product;
	// $product_id = $product->get_id();
	global $product;
	$product = wc_get_product(get_the_ID());
	$product_id = $product->get_id();
	$attachment_ids = $product->get_gallery_image_ids();
	$featured_image_url = wp_get_attachment_url(get_post_thumbnail_id($product->get_id()));
	if ($product && $product->is_type('variable')) {
		$variations = $product->get_available_variations();
		$valid_combinations = [];
		foreach ($variations as $variation) {
			$valid_combinations[] = $variation['attributes'];
		}
	} ?>
	<section class="product-detail-section">
		<div class="container">
			<div class="product-detail-wrap">
				<div class="product-img">
					<?php
					$attachment_ids = $product->get_gallery_image_ids();

					// Add featured image if no gallery images
					if (empty($attachment_ids)) {
						$attachment_ids[] = get_post_thumbnail_id($product->get_id());
					}

					// Include variation images
					//$variation_id = 0;

					if ($product->is_type('variable')) {
						$available_variations = $product->get_available_variations();
						$selected_attributes = [];

						// Get selected attributes from URL
						foreach ($_GET as $key => $value) {
							if (strpos($key, 'attribute_') === 0) {
								$attribute_key = str_replace('attribute_', '', wc_clean($key));
								$selected_attributes[$attribute_key] = wc_clean($value);
							}
						}

						$matched_variation_id = 0;

						foreach ($available_variations as $variation) {
							$variation_matches = true;

							foreach ($variation['attributes'] as $attr_name => $attr_value) {
								$attr_key = str_replace('attribute_', '', $attr_name);
								if (!isset($selected_attributes[$attr_key]) || strtolower($selected_attributes[$attr_key]) !== strtolower($attr_value)) {
									$variation_matches = false;
									break;
								}
							}

							if ($variation_matches) {
								$matched_variation_id = $variation['variation_id'];
								break;
							}
						}

						if ($matched_variation_id) {
							$variation_product = wc_get_product($matched_variation_id);
							$variation_image_id = $variation_product->get_image_id();

							if ($variation_image_id) {
								$attachment_ids = [$variation_image_id]; // Use variation image only
							}
						}

						// If no matched image, fallback to gallery
						if (empty($attachment_ids)) {
							$attachment_ids = $product->get_gallery_image_ids();
						}
					}
					?>
					<div class="swiper-container products-slider">
						<div class="swiper-wrapper">
							<?php foreach ($attachment_ids as $attachment_id) {
								$image_link = wp_get_attachment_url($attachment_id);
								if (empty($image_link) && $image_link == '') {
									$image_link = get_template_directory_uri() . '/assets/images/default_image.png';
								} ?>
								<div class="swiper-slide">
									<picture><img src="<?php echo $image_link; ?>" alt=""></picture>
								</div>
							<?php } ?>
						</div>
						<div class="swiper-button-next product-button-next"><span aria-hidden="true"
								class="fas fa-chevron-right"></span></div>
						<div class="swiper-button-prev product-button-prev"><span aria-hidden="true"
								class="fas fa-chevron-left"></span></div>
					</div>
					<?php // } 
					?>
					<?php // if($attachment_ids){ 
					?>
					<div class="swiper-container products-thumbnail">
						<div class="swiper-wrapper">
							<?php foreach ($attachment_ids as $attachment_id) {
								$image_link = wp_get_attachment_url($attachment_id);
								if (empty($image_link) && $image_link == '') {
									$image_link = get_template_directory_uri() . '/assets/images/default_image.png';
								} ?>
								<div class="swiper-slide">
									<picture><img src="<?php echo $image_link; ?>" alt=""></picture>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="swiper-pagination"></div>
					<?php if (!$product->is_in_stock()) { ?>
						<button type="button" class="out-stock-btn">OUT OF STOCK</button>
					<?php } ?>
				</div>
				<div class="product-details">
					<h3 class="product-title">
						<?php the_title(); ?>
					</h3>
					<?php if (!empty($product->get_sku())) { ?>
						<!-- <div class="sku"><strong>SKU: </strong><?php // echo $product->get_sku(); 
																	?></p>
						</div> -->
					<?php } ?>
					<?php
					$custom_price = isset($_GET['custom_price']) ? (float) $_GET['custom_price'] : null;
					if ($custom_price !== null && $custom_price > 0) {
						// Display custom price first if available
						echo '<div class="price custom-price">' . get_woocommerce_currency_symbol() . indian_number_format($custom_price) . '</div>';
					} elseif ($product->is_type('simple')) {
						// Simple product: display base price
						$price = $product->get_price(); // raw float
						$formatted_price = indian_number_format($price);
						echo '<div class="price">' . get_woocommerce_currency_symbol() . $formatted_price . '</div>';
					} elseif ($product->is_type('variable')) {
						// Variable product: check for attribute-based price in URL
						$selected_attributes = [];

						foreach ($_GET as $key => $value) {
							if (strpos($key, 'attribute_') === 0) {
								$attribute_name = str_replace('attribute_', '', $key);
								$selected_attributes[$attribute_name] = sanitize_text_field($value);
							}
						}

						$variation_price = '';
						if (!empty($selected_attributes)) {
							$available_variations = $product->get_available_variations();
							foreach ($available_variations as $variation) {
								$variation_attributes = [];
								foreach ($variation['attributes'] as $attr_name => $attr_value) {
									$clean_attr = str_replace('attribute_', '', $attr_name);
									$variation_attributes[$clean_attr] = $attr_value;
								}

								// Compare selected attributes with variation attributes
								if (array_map('strtolower', $variation_attributes) === array_map('strtolower', $selected_attributes)) {
									$variation_price = $variation['display_price'];
									break;
								}
							}
						}

						if ($variation_price !== '') {
							echo '<div class="price variation-price">' . get_woocommerce_currency_symbol() . indian_number_format((float) $variation_price) . '</div>';
						} else {
							echo '<div class="price variation-price"></div>';
						}
					}
					?>
					<hr class="line">
					<div class="product-content">
						<div class="tab-listing-cls-all-tabbing">
							<div class="product-tabs">
								<div class="product-tabs">

									<ul class="tab-nav">
										<li class="tab-link active" data-tab="product-description">Product Description</li>
										<li class="tab-link" data-tab="shipping-description">Additional Information
										</li>
									</ul>
									<div id="product-description" class="tab-content active">
										<p>
											<?php echo $product->get_description(); ?>
										</p>
									</div>
									<div id="shipping-description" class="tab-content">
										<p>
											<?php
											$product_id = get_the_ID();
											$shipping_description = get_field('shipping_description', $product_id);
											if ($shipping_description) {
												echo wp_kses_post($shipping_description);
											} else {
												echo '<p class="test">' . get_the_excerpt() . '</p>';
											}
											?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="add-product cash-product-gift">
						<form class="cart" method="post" enctype="multipart/form-data">
							<?php do_action('woocommerce_before_add_to_cart_button'); ?>
							<input type="hidden" name="gift_for"
								value="<?php echo esc_attr(isset($_GET['gift_for']) ? $_GET['gift_for'] : $gift_for); ?>" />
							<input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
							<?php
							$product = wc_get_product(the_product: get_the_ID());
							$target_category_slug = 'cash';
							$is_cash_product = has_term($target_category_slug, 'product_cat', $product->get_id());
							?>
							<input type="hidden" class="is_cash_pro" value="<?php echo $is_cash_product; ?>">
							<?php if ($product->is_type('variable')): ?>

								<input type="hidden" name="variation_id"
									value="<?php echo esc_attr($matched_variation_id); ?>" />

								<div class="variations">
									<div class="card-radiobtn-wrap">
										<?php woocommerce_variable_add_to_cart(); ?>

										<?php if ($is_cash_product): ?>
											<div class="custom-price-container">
												<label for="custom-price">Custom Price</label>
												<input type="number" id="custom-price" name="custom_price" min="0" step="1"
													value="<?php echo isset($_GET['custom_price']) ? esc_attr($_GET['custom_price']) : ''; ?>">

											</div>
										<?php endif; ?>
									</div>
								</div>

								<?php
								$gift_for = isset($_GET['gift_for']) ? $_GET['gift_for'] : '';
								if (!$is_cash_product): ?>
									<?php
									$min_qty = $product->get_min_purchase_quantity();
									$max_qty = $product->get_max_purchase_quantity();
									$max_qty = $product->get_max_purchase_quantity();
									if ($max_qty <= 0) {
										$max_qty = 5000;
									}
									$default_qty = isset($_GET['quantity']) ? intval($_GET['quantity']) : $min_qty;
									if (!empty($gift_for) || $gift_for != '') {
										$enable = 'readonly';
										$dis_class = 'disabled';
									} else {
										$enable = '';
										$dis_class = '';
									}
									?>
									<div class="add-qty new-add-qty-cls-adding-here">
										<span class="minus icon-minus-circle <?php echo $dis_class; ?>">-</span>
										<div class="quantity">
											<input type="number" class="input-text qty text" name="quantity"
												value="<?php echo esc_attr($default_qty); ?>"
												min="<?php echo esc_attr($min_qty); ?>" max="<?php echo esc_attr($max_qty); ?>"
												step="1" <?php echo $enable; ?> style="display:inline-block!important; opacity:1;"
												size="4" inputmode="numeric" autocomplete="off" />
										</div>
										<span class="plus icon-plus-circle <?php echo $dis_class; ?>">+</span>
									</div>
								<?php endif; ?>
							<?php else: ?>
								<?php
								$min_qty = $product->get_min_purchase_quantity();
								$max_qty = $product->get_max_purchase_quantity();
								if ($max_qty <= 0) {
									$max_qty = 5000;
								}
								$current_user_id = get_current_user_id();
								if (!isset($_GET['quantity']) && empty($_GET['quantity'])) {
									$qtty = 1;
								} else {
									$qtty = $_GET['quantity'];
								}
								$has_default_qty = isset($qtty) && intval($qtty) > 0;
								$gift_for_invalid = isset($_GET['gift_for']) && intval($_GET['gift_for']) !== $current_user_id;
								$default_qty = $has_default_qty ? intval($qtty) : $min_qty;
								$btn_class = !$logged_in ? 'disabled' : '';
								?>

								<div class="add-qty">
									<span class="minus icon-minus-circle <?php echo esc_attr($btn_class); ?>">-</span>

									<?php
									if ($has_default_qty && $gift_for_invalid) { ?>
										<div class="quantity">
											<input type="number" class="input-text qty text" name="quantity"
												value="<?php echo esc_attr($default_qty); ?>"
												min="<?php echo esc_attr($min_qty); ?>" max="<?php echo esc_attr($max_qty); ?>"
												step="1" readonly style="display:inline-block!important; opacity:1;"
												aria-label="<?php esc_attr_e('Product quantity', 'woocommerce'); ?>" size="4"
												inputmode="numeric" autocomplete="off" />
										</div>
									<?php
									} else { ?>
										<div class="quantity">
											<input type="number" class="input-text qty text aa" name="quantity"
												value="<?php echo esc_attr($default_qty); ?>"
												min="<?php echo esc_attr($min_qty); ?>" max="<?php echo esc_attr($max_qty); ?>"
												step="1" <?php if (!is_user_logged_in())
																echo 'readonly'; ?> style="display:inline-block!important; opacity:1;" size="4" inputmode="numeric"
												autocomplete="off" />
										</div>
									<?php }
									?>

									<span class="plus icon-plus-circle <?php echo esc_attr($btn_class); ?>">+</span>
								</div>
							<?php endif; ?>
							<?php if ($product->is_type('variable') && $product->get_id() == 230): ?>
								<div class="variation-product-container">
								<?php endif; ?>
								<?php
								// $product = wc_get_product( $product_id );
								$product_type = $product->get_type();
								?>
								<?php if (!$user_exists || ($logged_in && $user_id === $gift_for)) { ?>
									<button
										class="custom_button add-to-personalized <?php echo $is_out_of_stock ? 'disable_product_no_stock' : ''; ?>"
										data-product-id="<?php echo $product->get_id(); ?>"
										data-product-type="<?php echo esc_attr($product_type); ?>" data-action="add"
										data-custom-cash="">Add to Registry</button>
								<?php } ?>
								<?php if ($product->is_type('variable') && $product->get_id() == 230): ?>
									<div class="custom-amount-wrapper">
										<button type="button" id="add_custom_variation"
											class="custom_button add-to-personalized">Lifafa Amount</button>
										<input type="text" id="custom_amount" name="custom_amount"
											placeholder="Enter Lifafa Amount">
									</div>
								</div>
							<?php endif; ?>
							<?php if ($product->is_type('simple') && $user_exists && $user_id !== $gift_for): ?>
								<?php if (isset($_GET['gift_for'])) { ?>
									<div class="add-to-cart-container">
										<button class="custom_button secondary_button single_add_to_cart_button button alt"
											data-product-id="<?php echo $product->get_id(); ?>"
											data-gift-for="<?php echo esc_attr(isset($_GET['gift_for']) ? $_GET['gift_for'] : ''); ?>">
											<?php echo esc_html($product->single_add_to_cart_text()); ?>
										</button>
									<?php } ?>
									</div>
								<?php endif; ?>
								<?php if ($product->is_type('variable') && $user_exists && $user_id !== $gift_for): ?>
									<?php if (isset($_GET['gift_for']) && $current_user_id !== $gift_for_user_id) { ?>
										<div class="add-to-cart-container">
											<button
												class="add-to-cart-button custom_button secondary_button single_add_to_cart_button button alt"
												data-product-id="<?php echo $product->get_id(); ?>"
												data-gift-for="<?php echo esc_attr(isset($_GET['gift_for']) ? $_GET['gift_for'] : ''); ?>">
												<?php echo esc_html($product->single_add_to_cart_text()); ?>
											</button>
										<?php } ?>
										</div>
									<?php endif; ?>
						</form>
						<?php do_action('woocommerce_after_add_to_cart_button'); ?>
					</div>
				</div>
			</div>
		</div>
		</div>
		</div>
	</section>
	<?php
	$target_category_slug = 'cash';
	$is_cash_product = has_term($target_category_slug, 'product_cat', $product->get_id());

	if ($gift_for && !$logged_in): ?>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				const selects = document.querySelectorAll('.variations select');
				const isCashInput = document.querySelector('.is_cash_pro');
				selects.forEach(select => {
					select.disabled = true;
					select.style.opacity = "0.5";
					select.style.pointerEvents = "none";
				});

				// alert(isCashInput);
				if (isCashInput && isCashInput.value == "1") {
					// alert('hello');
					setTimeout(() => {
						selects.forEach(select => {
							select.disabled = false;
							select.style.opacity = "";
							select.style.pointerEvents = "";
						});
					}, 1000);
				}
			});
		</script>
	<?php endif; ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery(document).on('change', 'select', function($) {
				var selectedAttributes = {};
				var selected_value = jQuery(this).val();
				var attribute_name = jQuery(this).attr('name');
				var current_url = new URL(window.location.href);
				var has_query_params = current_url.searchParams.has(attribute_name);
				if (current_url.searchParams.has('price')) {
					current_url.searchParams.delete('price');
				}
				if (has_query_params) {
					current_url.searchParams.set(attribute_name, selected_value);
				} else {
					current_url.searchParams.append(attribute_name, selected_value);
				}
				history.pushState(null, '', current_url.toString());
				jQuery('.cart select').each(function() {
					selectedAttributes[jQuery(this).attr('name')] = jQuery(this).val();
				});
				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					type: 'POST',
					data: {
						action: 'get_variation_price',
						product_id: <?php echo $product->get_id(); ?>,
						attributes: selectedAttributes
					},
					success: function(response) {
						if (response.success && response.data.price_html) {
							var variationId = response.data.variation_id;
							console.log(variationId);
							var priceHtml = response.data.price_html;
							var newImage = response.data.image_url;
							jQuery('input[name="variation_id"]').val(variationId);
							jQuery('.variation-price').html(priceHtml);
							jQuery('.variation-message').html('');
							jQuery('.products-slider .swiper-slide picture img').attr('src',
								newImage);
							jQuery('.products-thumbnail .swiper-slide').removeClass(
								'selected-thumb');
							jQuery('.products-thumbnail .swiper-slide picture img').each(
								function() {
									var thumbImg = jQuery(this);
									var thumbSrc = thumbImg.attr('src');
									if (thumbSrc && thumbSrc.includes(newImage)) {
										thumbImg.closest('.swiper-slide').addClass(
											'selected-thumb');
									}
								});
							location.reload();
						} else {
							jQuery('.variation-price').html('');
							jQuery('.variation-message').html(
								'<p style="color:red;">Please select a valid variation.</p>');
						}
					},
					error: function() {
						jQuery('.variation-price').html('');
						jQuery('.variation-message').html(
							'<p style="color:red;">Error retrieving price. Please try again.</p>'
						);
					}
				});
			});

			// Working code start
			// $('#custom-price').on('input', function () {
			// 	var customPrice = parseFloat($(this).val());
			// 	var current_url = new URL(window.location.href);
			// 	var selectedAttribute = current_url.searchParams.get('attribute_lifafa');
			// 	if (!isNaN(customPrice) && customPrice > 0) {
			// 		current_url.searchParams.delete('custom_price');
			// 		current_url.searchParams.set('custom_price', customPrice);
			// 		$('.variation-price').html('<?php // echo get_woocommerce_currency_symbol(); 
													?>' + customPrice);
			// 		$.ajax({
			// 			url: '<?php // echo admin_url('admin-ajax.php'); 
									?>',
			// 			type: 'POST',
			// 			data: {
			// 				action: 'save_custom_price',
			// 				product_id: <?php // echo $product->get_id(); 
											?>,
			// 				custom_price: customPrice
			// 			}
			// 		});
			// 	} else {
			// 		current_url.searchParams.delete('custom_price');
			// 		if (selectedAttribute) {
			// 			current_url.searchParams.set('attribute_lifafa', selectedAttribute);
			// 		}
			// 		$('.variation-price').html('');
			// 	}
			// 	history.pushState(null, '', current_url.toString());
			// });
			// Working code end

			$('#custom-price').on('input', function() {
				var customPrice = parseFloat($(this).val());
				var current_url = new URL(window.location.href);

				// Save `gift_for` if it exists
				var giftFor = current_url.searchParams.get('gift_for');

				// Clear all parameters
				current_url.search = '';

				// Restore only `gift_for`
				if (giftFor) {
					current_url.searchParams.set('gift_for', giftFor);
				}

				if (!isNaN(customPrice) && customPrice > 0) {
					current_url.searchParams.set('custom_price', customPrice);
					$('.variation-price').html('<?php echo get_woocommerce_currency_symbol(); ?>' +
						customPrice);

					$.ajax({
						url: '<?php echo admin_url('admin-ajax.php'); ?>',
						type: 'POST',
						data: {
							action: 'save_custom_price',
							product_id: <?php echo $product->get_id(); ?>,
							custom_price: customPrice
						}
					});
				} else {
					$('.variation-price').html('');
				}

				history.pushState(null, '', current_url.toString());
			});


			// $('#custom-price').on('input', function () {
			// 	var customPrice = parseFloat($(this).val());
			// 	var defaultPrice = $('.variation-price').data('default-price'); // Store the default price in data attribute
			// 	if (!isNaN(customPrice) && customPrice > 0) {
			// 		$('.variation-price').html('<?php  // echo get_woocommerce_currency_symbol(); 
													?>' + customPrice);
			// 	} else {
			// 		if (defaultPrice) {
			// 			$('.variation-price').html('<?php  // echo get_woocommerce_currency_symbol(); 
														?>' + defaultPrice);
			// 		} else {
			// 			$('.variation-price').html('');
			// 		}
			// 	}
			// });

			// $('#custom-price').on('input', function () {
			// 	var customPrice = parseFloat($(this).val());

			// 	if (!isNaN(customPrice) && customPrice > 0) {
			// 		$('.variation-price').html('<?php // echo get_woocommerce_currency_symbol(); 
													?>' + customPrice);

			// 		$.ajax({
			// 			url: '<?php // echo admin_url('admin-ajax.php'); 
									?>',
			// 			type: 'POST',
			// 			data: {
			// 				action: 'add_custom_price_to_cart',
			// 				product_id: <?php // echo $product->get_id(); 
											?>,
			// 				custom_price: customPrice
			// 			},
			// 			success: function(response) {
			// 				if (response.success) {
			// 					window.location.href = '<?php // echo wc_get_checkout_url(); 
															?>'; // redirect to checkout
			// 				} else {
			// 					alert(response.data.message);
			// 				}
			// 			}
			// 		});
			// 	}
			// });

			$('#custom-price').on('input', function() {
				var customPrice = parseFloat($(this).val());
				if (!isNaN(customPrice) && customPrice > 0) {
					$('.variation-price').html('<?php echo get_woocommerce_currency_symbol(); ?>' +
						customPrice);
				} else {
					$('.variation-price').html('<?php echo get_woocommerce_currency_symbol(); ?>0');
				}
			});
			$('.add-to-cart-button').on('click', function(e) {
				e.preventDefault();
				var urlParams = new URLSearchParams(window.location.search);
				var product_id = $(this).data('product-id');
				var variation_id = $('input[name="variation_id"]').val() || 0;
				var quantity = $('input[name="quantity"]').val() || 0;
				var gift_for = urlParams.get('gift_for');
				var attributes = {};
				var custom_amount = urlParams.get('custom_price');
				if (custom_amount) {
					attributes['Custom amount'] = custom_amount;
				} else {
					$('form.variations_form select').each(function() {
						var name = $(this).attr('name'); // e.g., attribute_pa_color
						var value = $(this).val();
						if (name && value) {
							attributes[name] = value;
						}
					});
				}
				var attribute_amount = urlParams.get('attribute_amount');

				if (!variation_id || variation_id === '0') {
					variation_id = 0;
				}

				var data = {
					action: 'custom_woocommerce_add_to_cart',
					product_id: product_id,
					variation_id: variation_id,
					quantity: quantity,
					gift_for: gift_for,
					attributes: JSON.stringify(attributes),
					attribute_amount: attribute_amount,
					custom_amount: custom_amount
				};

				$.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
					if (response.success) {
						toastr.success('Product Added to Cart.');
						setTimeout(() => {
							location.reload();
						}, 200);
						$('.wc-block-cart-items').html(response.fragments['.wc-block-cart-items']);
						$('.wc-block-components-totals-item__value').html(response.fragments[
							'.wc-block-components-totals-item__value']);
					} else {
						toastr.warning(response.data.message);
					}
				}).fail(function(jqXHR, textStatus, errorThrown) {
					console.log('AJAX Request failed: ' + textStatus + ', ' + errorThrown);
					toastr.warning('An error occurred. Please try again.');
				});
			});
			$('.reset_variations').on('click', function(e) {
				e.preventDefault();
				var $form = $('form.cart');
				$form.trigger('reset_variations');
				$form.find('select[name="attribute_amount"]').val('');
				$form.find('select[name="attribute_amount"]').change();
				$('.variation-price').html('');
				$('.variation-message').html('');

			});
		});
	</script>
<?php endwhile; ?>
<script>
	var validCombinations = <?php echo json_encode($valid_combinations); ?>;
</script>
<script>
	jQuery(document).ready(function($) {
		function updateOptions() {
			let selectedAttrs = {};

			// Collect selected values
			$('select[name^="attribute_"]').each(function() {
				const attr = $(this).attr('name');
				const val = $(this).val();
				if (val) selectedAttrs[attr] = val;
			});

			// For each select
			$('select[name^="attribute_"]').each(function() {
				const $select = $(this);
				const attr = $select.attr('name');

				// For each option in this select
				$select.find('option').each(function() {
					const $option = $(this);
					const val = $option.val();
					if (!val) return $option.prop('disabled', false); // Skip "Choose option"

					let isValid = false;

					validCombinations.forEach(function(combination) {
						let matches = true;

						// check if all other selected attrs match
						for (let key in selectedAttrs) {
							if (key === attr) continue;
							if (combination[key] !== selectedAttrs[key]) {
								matches = false;
								break;
							}
						}

						// if other attrs match, this option is valid only if it also matches
						if (matches && combination[attr] === val) {
							isValid = true;
						}
					});

					$option.prop('disabled', !isValid);
				});
			});
		}

		$('select[name^="attribute_"]').on('change', updateOptions);

		updateOptions(); // Initial call
	});


	jQuery(document).ready(function($) {
		jQuery(document).ready(function($) {
			let mainSwiper;
			let thumbnailSwiper;

			function initSwipers() {
				thumbnailSwiper = new Swiper('.products-thumbnail', {
					spaceBetween: 10,
					slidesPerView: 4,
					watchSlidesVisibility: true,
					watchSlidesProgress: true,
				});

				mainSwiper = new Swiper('.products-slider', {
					spaceBetween: 10,
					navigation: {
						nextEl: '.product-button-next',
						prevEl: '.product-button-prev',
					},
					thumbs: {
						swiper: thumbnailSwiper
					}
				});
			}

			function destroySwipers() {
				if (mainSwiper) {
					mainSwiper.destroy(true, true);
				}
				if (thumbnailSwiper) {
					thumbnailSwiper.destroy(true, true);
				}
			}

			// Init on first load
			initSwipers();

			$('form.variations_form').on('found_variation', function(event, variation) {
				if (!variation.image || !variation.image.src) return;

				const imageSrc = variation.image.gallery_thumbnail_src || variation.image.src;

				// Replace HTML with just the variation image
				$('#main-slider-wrapper').html(`
			<div class="swiper-slide">
				<picture><img src="${variation.image.src}" alt=""></picture>
			</div>
		`);

				$('#thumbnail-slider-wrapper').html(`
			<div class="swiper-slide">
				<picture><img src="${imageSrc}" alt=""></picture>
			</div>
		`);

				destroySwipers();
				setTimeout(() => {
					initSwipers();
				}, 100);
			});
		});

	});
</script>


<?php //get_custom_related_products( get_the_ID(), 4 ); 
// echo do_shortcode('[elementor-template id="4354"]'); 
echo do_shortcode('[custom_cross_sells]');  ?>

<!-- /Faqs Section -->
<?php // include get_template_directory() . '/common_block/faq-section.php'; 
?>

<?php
/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');
?>

<?php
/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');
?>

<?php
get_footer('shop');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */