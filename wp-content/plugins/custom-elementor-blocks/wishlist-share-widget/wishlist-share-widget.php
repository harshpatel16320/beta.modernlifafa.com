<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Wishlist_Share_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'wishlist_share';
    }

    public function get_title() {
        return __('Wishlist Share', 'text-domain');
    }

    public function get_icon() {
        return 'eicon-share';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'text-domain'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'wishlist_link',
            [
                'label'       => __('Wishlist Link', 'text-domain'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'https://your-wishlist-link.com',
                'placeholder' => __('Enter wishlist link', 'text-domain'),
            ]
        );

        // Social Icons Upload
        $this->add_control(
            'whatsapp_icon',
            [
                'label'   => __('WhatsApp Icon', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => ['url' => plugins_url('assets/images/WhatsApp.svg', __FILE__)],
            ]
        );

        $this->add_control(
            'facebook_icon',
            [
                'label'   => __('Facebook Icon', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => ['url' => plugins_url('assets/images/Facebook.svg', __FILE__)],
            ]
        );

        $this->add_control(
            'twitter_icon',
            [
                'label'   => __('Twitter Icon', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => ['url' => plugins_url('assets/images/Twitter.svg', __FILE__)],
            ]
        );

        $this->add_control(
            'gmail_icon',
            [
                'label'   => __('Gmail Icon', 'text-domain'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => ['url' => plugins_url('assets/images/Gmail.svg', __FILE__)],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $wishlist_link = esc_url($settings['wishlist_link']);
        $whatsapp_icon = esc_url($settings['whatsapp_icon']['url']);
        $facebook_icon = esc_url($settings['facebook_icon']['url']);
        $twitter_icon = esc_url($settings['twitter_icon']['url']);
        $gmail_icon = esc_url($settings['gmail_icon']['url']); ?>
        <?php 
        $user_id = $gift_user_id ?? get_current_user_id();
        $first_name = esc_attr(get_user_meta($user_id, 'first_name', true));
        $partner_name = esc_attr(get_user_meta($user_id, 'partner_fname', true));
        $couple_name = $first_name . '-and-' . $partner_name;          
        ?>
        <div class="modal-content">
            <h2 class="modal-title">SHARE YOUR REGISTRY</h2>
            <input type="hidden" class="couple_share_link" value="<?php echo get_permalink(get_page_by_path('wishlist'));?><?php echo '?couple_name='.$couple_name; ?>&original_user_id=<?php echo $user_id; ?>">
            <p class="sub-content"><strong>You’ve curated the dream list—now share it!</strong><br/> Let your guests know what you love and make gifting joyful for everyone.</p>
            <div class="copy-input">
                <input type="text" class="form-control page_link_share" value="<?php echo $wishlist_link; ?>">
                <button class="copy-btn" id="copyButton">
                    <picture>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/copy.png" alt="Copy" width="20" height="20">
                    </picture>
                </button>
            </div>
            <div class="social-share">
                <a href="https://api.whatsapp.com/send?text=<?php echo $wishlist_link; ?>" target="_blank" class="wtsp_share" title="Share on WhatsApp">
                    <img src="<?php echo $whatsapp_icon; ?>" alt="WhatsApp">
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $wishlist_link; ?>" target="_blank" class="fcbk_share" title="Share on Facebook">
                    <img src="<?php echo $facebook_icon; ?>" alt="Facebook">
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $wishlist_link; ?>" class="twtr_share" target="_blank" title="Share on Twitter">
                    <img src="<?php echo $twitter_icon; ?>" alt="Twitter">
                </a>
                <a href="mailto:?subject=<?php echo urlencode('Check out our Modern Lifafa Registry!'); ?>&body=<?php echo urlencode('Check out our Modern Lifafa Registry at: ' . $wishlist_link); ?>" class="gmail_share" title="Share via Email">
                    <img src="<?php echo esc_url($gmail_icon); ?>" alt="Email">
                </a>
            </div>
        </div>
        <?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Wishlist_Share_Widget());

?>