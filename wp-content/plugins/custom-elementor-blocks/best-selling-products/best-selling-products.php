<?php
if (!defined('ABSPATH')) exit;

class Best_Selling_Products_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'best_selling_products'; }
    public function get_title() { return __('Best Selling Products', 'elementor'); }
    public function get_icon() { return 'eicon-products'; }
    public function get_categories() { return ['general']; }

    protected function _register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Content', 'plugin-name'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('number_of_products', [
            'label' => __('Number of Products', 'plugin-name'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 10,
            'default' => 5,
        ]);

        $this->add_control('first_image', [
            'label' => __('First Image', 'plugin-name'),
            'type' => \Elementor\Controls_Manager::MEDIA,
        ]);

        $this->add_control('second_image', [
            'label' => __('Second Image', 'plugin-name'),
            'type' => \Elementor\Controls_Manager::MEDIA,
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $num_products = $settings['number_of_products'];

        // $args = [
        //     // 'post_type'      => 'product',
        //     // 'posts_per_page' => 4, // number of products
        //     // 'meta_key'       => 'total_sales',
        //     // 'orderby'        => 'meta_value_num',
        //     // 'order'          => 'DESC',
        //     // 'post_status'    => 'publish',

        //     'post_type'      => 'product',
        //     'posts_per_page' => 8,
        //     'meta_key'       => 'featured',
        //     'meta_value'     => 'yes'
        // ];

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 4,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'featured',
                    'operator' => 'IN',
                ),
            ),
        );
        
        $query = new WP_Query($args);
           
        echo '<div class="container">
            <div class="product-wrapper">';
            if ($query->have_posts()) {
                $i = 0;
                // var_dump($query);
                while ($query->have_posts()) {
                    $i++;
                    $query->the_post();
                    global $product;
                    $term_names_array = wp_get_post_terms($product->get_id(), 'brand', array('fields' => 'names'));
                    if($i == 4){
                        echo '<div class="perfect-match">
                            <picture>';

                            $settings['second_image']['url'] = 'https://modernlifafa.com/wp-content/uploads/2026/04/find-match-1.png';

                            if (!empty($settings['second_image']['url'])) {
                                echo '<img src="' . esc_url($settings['second_image']['url']) . '" class="custom-img" alt="perfect match" width="224" height="260"/>';
                            }

                            echo '</picture>
                        </div>';
                    }
                    echo '<div class="product-item">';
                    echo '<picture><img src="' . get_the_post_thumbnail_url(get_the_ID(), 'medium') . '" alt="product 1" width="478" height="478"></picture>';
                    echo '<div class="product-info"><a href="' . get_permalink() . '" aria-label="product-name" class="product-name">' . get_the_title() . '</a>';
                    // echo '<span class="product-by">by </span></div>';
                    if (isset($term_names_array[0]) && !empty($term_names_array[0]) && $term_names_array[0] != '') {
                        ?>
<span class="product-by">by <?php echo esc_html($term_names_array[0]); ?></span>
<?php
                    }
                    echo '</div></div>';
                }
                wp_reset_postdata();
            } 
                // <div class="product-item">
                //     <picture>
                //         <img src="http://203.109.113.155/modernlifafa-elementor/wp-content/uploads/2025/02/banner-2.webp"
                //             alt="product 1" width="478" height="478">
                //     </picture>
                //     <div class="product-info">
                //         <a href="#" aria-label="product-name" class="product-name">Romantic Dinner in the Alps</a>
                //         <span class="product-by">by Nowhere</span>
                //     </div>
                // </div>
                // <div class="product-item">
                //     <picture>
                //         <img src="http://203.109.113.155/modernlifafa-elementor/wp-content/uploads/2025/02/banner-2.webp"
                //             alt="product 2" width="478" height="478">
                //     </picture>
                //     <div class="product-info">
                //         <a href="#" aria-label="product-name" class="product-name">Romantic Dinner in the Alps</a>
                //         <span class="product-by">by Nowhere</span>
                //     </div>
                // </div>
                // <div class="product-item">
                //     <picture>
                //         <img src="http://203.109.113.155/modernlifafa-elementor/wp-content/uploads/2025/02/banner-2.webp"
                //             alt="product 3" width="478" height="478">
                //     </picture>
                //     <div class="product-info">
                //         <a href="#" aria-label="product-name" class="product-name">Romantic Dinner in the Alps</a>
                //         <span class="product-by">by Nowhere</span>
                //     </div>
                // </div>
                // <div class="perfect-match">
                //     <picture>
                //         <img src="http://203.109.113.155/modernlifafa-elementor/wp-content/uploads/2025/02/find-match.png"
                //             alt="perfect match" width="224" height="260">
                //     </picture>
                // </div>
                // <div class="product-item">
                //     <picture>
                //         <img src="http://203.109.113.155/modernlifafa-elementor/wp-content/uploads/2025/02/banner-2.webp"
                //             alt="product 4" width="478" height="478">
                //     </picture>
                //     <div class="product-info">
                //         <a href="#" aria-label="product-name" class="product-name">Romantic Dinner in the Alps</a>
                //         <span class="product-by">by Nowhere</span>
                //     </div>
                // </div>
             echo '</div>
        </div>';

        // echo '<div class="best-selling-products-widget">';
        // if (!empty($settings['first_image']['url'])) {
        //     echo '<img src="' . esc_url($settings['first_image']['url']) . '" class="custom-img" />';
        // }

        // echo '<div class="products-list">';
        // if ($query->have_posts()) {
        //     while ($query->have_posts()) {
        //         $query->the_post();
        //         global $product;
        //         echo '<div class="product-item">';
        //         echo '<a href="' . get_permalink() . '">';
        //         echo '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'medium') . '" />';
        //         echo '<h3>' . get_the_title() . '</h3>';
        //         echo '<p>' . wc_price($product->get_price()) . '</p>';
        //         echo '</a>';
        //         echo '</div>';
        //     }
        //     wp_reset_postdata();
        // } else {
        //     echo '<p>No best-selling products found.</p>';
        // }
        // echo '</div>';

        // if (!empty($settings['second_image']['url'])) {
        //     echo '<img src="' . esc_url($settings['second_image']['url']) . '" class="custom-img" />';
        // }

        // echo '</div>';
    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Best_Selling_Products_Widget());

?>
<style>
.container {
    max-width: 1170px;
    margin: auto;
}

.product-wrapper {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.product-wrapper .product-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.product-wrapper .product-item:first-child {
    grid-row: span 2;
    grid-column: span 1;
    width: 458px;
    height: 620px;
}

.product-wrapper .product-item::after {
    content: '';
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(0deg, rgba(35, 20, 9, 0.7) 18.5%, rgba(137, 78, 35, 0) 100%);
}

.product-wrapper .product-item:first-child::before {
    content: url('./wp-content/uploads/2025/02/best-seller.webp');
    position: absolute;
    right: 20px;
    top: 20px;
    z-index: 1;
}

.product-wrapper .product-item:nth-child(2) {
    grid-column: span 2;
    grid-row: span 1;
    width: 458px;
    height: 342px;
}

.product-wrapper .product-item:nth-child(3) {
    grid-row: span 2;
    grid-column: span 1;
    width: 214px;
    height: 620px;
}

.product-wrapper .perfect-match {
    display: flex;
    justify-content: center;
    align-items: center;
    grid-row: span 1;
    grid-column: span 1;
    width: 219px;
    height: 258px;
    transition: all .3s ease;
    /* animation: borderWave 3s infinite linear; */
}

.product-wrapper .perfect-match:hover {
    transform: scale(1.05);
}

/* @keyframes borderWave {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    } */

.product-wrapper .perfect-match picture {
    display: block;
    width: 100%;
    height: 100%;
}

.product-wrapper .perfect-match picture img {
    border-radius: 15px;
    width: 100%;
    height: 100%;
    object-fit: cover;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
}

.product-wrapper .product-item:nth-child(5) {
    grid-row: span 1;
    grid-column: span 1;
    width: 219px;
    height: 258px;
}

.product-wrapper .product-item picture {
    display: block;
    width: 100%;
    height: 100%;
}

.product-wrapper .product-item picture img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1);
    transition: all .3s ease;
}

.product-wrapper .product-item:hover img {
    transform: scale(1.1);
}

.product-wrapper .product-item .product-info {
    position: absolute;
    bottom: 20px;
    text-align: center;
    font-family: "DM Sans", Sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    z-index: 1;
    width: 100%;
}


.product-wrapper .product-item .product-info .product-name {
    display: inline-block;
    color: #fff;
    padding: 0 6px;
    font-family: "DM Sans", Sans-serif;
    text-decoration: underline;
    transition: all 0.3s ease;
}

.product-wrapper .product-item .product-info .product-name:hover {
    color: #cccccc !important;
}

.product-wrapper .product-item .product-info .product-by {
    display: block;
    padding: 0 6px;
    font-weight: 300;
    font-family: "DM Sans", Sans-serif;
}

@media (max-width: 1279px) {
    .product-wrapper>* {
        width: auto !important;
    }

    .product-wrapper {
        gap: 10px;
    }

    .product-wrapper .product-item:first-child::before {
        content: "";
        display: block;
        background: url('./wp-content/uploads/2025/02/best-seller.webp') no-repeat center;
        background-size: 100%;
        width: 80px;
        height: 80px;
        right: 10px;
        top: 10px;
    }

    .product-wrapper .product-item .product-info {
        bottom: 10px;
    }
}

@media (max-width: 1023px) {
    .product-wrapper .product-item:first-child::before {
        width: 70px;
        height: 70px;
    }
}

@media (max-width: 767px) {
    .product-wrapper {
        grid-template-columns: repeat(2, 1fr);
    }

    .product-wrapper>* {
        grid-row: span 1 !important;
        grid-column: span 1 !important;
        height: 180px !important;
    }

    .product-wrapper .product-item:first-child::before {
        width: 50px;
        height: 50px;
    }

    .product-wrapper .product-item:nth-child(5) {
        grid-column: span 2 !important;
    }
}
</style>