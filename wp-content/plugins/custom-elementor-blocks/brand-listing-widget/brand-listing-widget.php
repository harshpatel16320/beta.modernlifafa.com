<?php
if (!defined('ABSPATH')) {
    exit;
}

class Brand_Listing_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'brand_listing';
    }

    public function get_title() {
        return __('Brand Listing', 'custom-widgets');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        $taxonomy = 'brand'; // Change this to your actual taxonomy name
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        if (empty($terms)) return;
        ?>
        <div class="brand-listing-section">
            <div class="container">
                <ul class="brands-list">
                    <?php foreach ($terms as $term) {
                        $image = get_field('image', $term->taxonomy . '_' . $term->term_id);
                        $image_url = $image ? esc_url($image['url']) : wc_placeholder_img_src();
                        ?>
                        <li class="brand-box">
                            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="brand-logo">
                                <picture>
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr($term->name); ?>">
                                </picture>
                            </a>
                            <div class="view-brands">
                                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="custom_button primary_button_outline">View More</a>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php
    }
}

\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Brand_Listing_Widget());
