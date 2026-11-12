<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Product_List_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'product_list';
    }

    public function get_title() {
        return __('Product List', 'text-domain');
    }

    public function get_icon() {
        return 'eicon-products';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Filters', 'text-domain'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Category Filter
        $this->add_control(
            'category_filter',
            [
                'label'   => __('Enable Category Filter', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        // Price Range Filter
        $this->add_control(
            'price_filter',
            [
                'label'   => __('Enable Price Filter', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        // Sorting Options
        $this->add_control(
            'sort_filter',
            [
                'label'   => __('Enable Sorting', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <div class="product-listing-widget">
            <div class="filters">
                <select id="category_filter">
                    <option value="">All Categories</option>
                    <?php
                    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
                    foreach ($categories as $category) {
                        echo "<option value='{$category->term_id}'>{$category->name}</option>";
                    }
                    ?>
                </select>
                <input type="number" id="min_price" placeholder="Min Price">
                <input type="number" id="max_price" placeholder="Max Price">
                <select id="sort_filter">
                    <option value="">Sort By</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
                <button id="apply_filters">Apply Filters</button>
            </div>
            <div id="product_list"></div>
        </div>
        <script src="<?php echo plugins_url('assets/js/product-filter.js', __FILE__); ?>"></script>
        <?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Product_List_Widget());

// function register_product_list_widget($widgets_manager) {
//     require_once __DIR__ . '/product-list-widget.php';
//     $widgets_manager->register(new \Product_List_Widget());
// }
// add_action('elementor/widgets/register', 'register_product_list_widget');

// AJAX Handler
add_action('wp_ajax_get_filtered_products', 'get_filtered_products');
add_action('wp_ajax_nopriv_get_filtered_products', 'get_filtered_products');
function get_filtered_products() {
    $args = ['post_type' => 'product', 'posts_per_page' => 10];
    
    if (!empty($_POST['category'])) {
        $args['tax_query'] = [['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $_POST['category']]];
    }
    if (!empty($_POST['min_price']) || !empty($_POST['max_price'])) {
        $args['meta_query'] = [['key' => '_price', 'value' => [$_POST['min_price'], $_POST['max_price']], 'compare' => 'BETWEEN', 'type' => 'NUMERIC']];
    }
    if (!empty($_POST['sort'])) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = $_POST['sort'] == 'price_asc' ? 'ASC' : 'DESC';
        $args['meta_key'] = '_price';
    }
    
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div>' . get_the_title() . ' - $' . get_post_meta(get_the_ID(), '_price', true) . '</div>';
        }
    } else {
        echo '<p>No products found.</p>';
    }
    wp_die();
}

?>
