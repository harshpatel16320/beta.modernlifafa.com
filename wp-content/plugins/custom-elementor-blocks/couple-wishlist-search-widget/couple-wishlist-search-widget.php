<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Couple_Wishlist_Search_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'couple_wishlist_search'; }
    public function get_title() { return __('Couple Wishlist Search', 'elementor'); }
    public function get_icon() { return 'eicon-search'; }
    public function get_categories() { return ['general']; }

    protected function render() { ?>
        <div class="modal fade modern-lifafa-model" id="findCoupleModel" tabindex="-1" aria-labelledby="findCoupleModelLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">                    
                    <div class="modal-header">
                        <h4 class="modal-title">FIND A COUPLE'S WISHLIST</h4>
                    </div>
                    <div class="couple-wishlist">
                        <div class="couple-input">
                            <input type="text" class="form-control user_search" placeholder="Search...">
                        </div>
                        <div class="search-results">
                            <h5 class="model-mini-title">Search results...</h5>
                            <ul class="search-results-list">
                               
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function _register_controls() {
        // Add Elementor controls if needed
    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Couple_Wishlist_Search_Widget());

// function register_couple_wishlist_search_widget($widgets_manager) {
//     require_once(__FILE__);
//     $widgets_manager->register(new \Couple_Wishlist_Search_Widget());
// }
add_action('elementor/widgets/register', 'register_couple_wishlist_search_widget');
?>

