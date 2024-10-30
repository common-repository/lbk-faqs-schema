<?php

// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) ) exit;

if ( !class_exists( 'lbkFAQs_Admin' ) ) {
    /**
     * Class lbkFAQs_Admin
     */
    final class lbkFAQs_Admin {
        /**
         * Setup plugin for admin use
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public function __construct() {
            $this->hooks();
        }

        /**
         * Add the core admin hooks.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        private function hooks() {
            add_action( 'add_meta_boxes', array( $this, 'lbk_custom_faqs' ) );
            add_action( 'save_post', array( $this, 'lbk_custom_faq_save' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
            add_action( 'restrict_manage_posts', array( $this, 'post_has_faqs_filter' ) );
            add_filter( 'parse_query', array( $this, 'post_has_faqs_filter_results' ) );
        }

        /**
         * Add meta box custom faqs.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public function lbk_custom_faqs() {
          if ( class_exists( 'lbkFAQsPro' ) ) $post_types = get_option("lbk_post_types_enable_faqs");
          else $post_types = array( 'post', 'product', 'page' );
          add_meta_box(
            '_lbk-custom-faq',
            'Custom FAQs',
            array( $this, 'lbk_custom_faq_inner'),
            $post_types,
            'normal',
            'default'
          );
        }

        /**
         * Callback of add_meta_box in lbk_custom_faqs.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public function lbk_custom_faq_inner() {
            $post_id = get_the_ID();
        
            $custom_faqs = get_post_meta( $post_id, '_lbk_custom_faqs', true );
            $faqs_in_footer = get_post_meta( $post_id, '__lbk_faqs_in_footer_post', true );
            if (!$custom_faqs) $custom_faqs = array();
            if (!$faqs_in_footer) $faqs_in_footer = '';
        
            wp_nonce_field( 'lbk_custom_faqs_nonce'.$post_id, 'lbk_custom_faqs_nonce' );
            ?>
            <label for="enable_faqs_footer_post">
              <input type="checkbox" name="lbk_faqs_in_footer_post" id="enable_faqs_footer_post" <?php echo ($faqs_in_footer) ? 'checked' : ''; ?>>
              Hiển thị FAQs ở cuối bài
            </label>
            <div class="lbk_list_faqs" id="lbk_list_faqs">
                <a href="#" class="button" id="add_new_faqs_button" style="margin: 5px auto;">Add New</a>
                <div id="lbk_list_faqs_inner" style="width:100%">
                    <?php if ( $custom_faqs > 0 ) : $c = 1; ?>
                        <?php foreach( $custom_faqs as $faqs ) : $args['textarea_name'] = "lbk_faqs_custom[{$c}][answer]"; ?>
                            <div class="collapsible state-close" id="faqs-<?php echo $c ?>">
                                <div class="collapsible_heading">
                                    <h4>Question <?php echo $c; ?></h4>
                                    <a href="#" class="button" id="lbk_delete_faqs" data-id="<?php echo $c; ?>">Remove</a>
                                    <span class="collapsible-icon-toggle"></span>
                                </div>
                                <div class="faqs-content collapsible_content">
                                    <input type="hidden" class="faqs-id" value="<?php echo $c; ?>">
                                    <div class="faqsData">
                                        <h4>Question</h4>
                                        <input name="lbk_faqs_custom[<?php echo $c; ?>][question]" value="<?php echo esc_textarea($faqs['question']); ?>" style="width:100%">
                                    </div>
                                    <div class="faqsData">
                                        <h4>Answer</h4>
                                        <?php wp_editor( $faqs['answer'], "answer-{$c}", $args );?>
                                    </div>
                                </div>
                            </div>
                            <?php $c++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }

        /**
         * Add post meta after save post
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public function lbk_custom_faq_save($post_id) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            
            if (!isset($_POST['lbk_custom_faqs_nonce']) || !wp_verify_nonce($_POST['lbk_custom_faqs_nonce'], 'lbk_custom_faqs_nonce'.$post_id)) {
                return;
            }
            
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        
            if (isset($_POST['lbk_faqs_custom'])) {
                update_post_meta($post_id, '_lbk_custom_faqs', lbk_sanitize_faqs_field( $_POST['lbk_faqs_custom'] ));
            } else {
                delete_post_meta($post_id, '_lbk_custom_faqs');
            }

            if ( isset( $_POST['lbk_faqs_in_footer_post'] ) ) {
              update_post_meta($post_id, '__lbk_faqs_in_footer_post', lbk_sanitize_faqs_field( $_POST['lbk_faqs_in_footer_post'] ));
            }
            else {
              delete_post_meta($post_id, '__lbk_faqs_in_footer_post');
            }
        }

        public function post_has_faqs_filter() {
          $values = array(
            'Bài viết có FAQs' => 'posts_have_faqs'
          );
          ?>
          <select name="faqs_post_filter">
          <option value=""><?php _e('Tất cả bài viết ', 'lbk-faqs'); ?></option>
          <?php
            $current_v = isset($_GET['faqs_post_filter'])? $_GET['faqs_post_filter']:'';
            foreach ($values as $label => $value) {
              printf(
                '<option value="%s" %s>%s</option>',
                $value,
                $value == $current_v? ' selected="selected"':'',
                $label
              );
            }
          ?>
          </select>
          <?php
        }

        public function post_has_faqs_filter_results($query){
          global $pagenow;
          if ( is_admin() && $pagenow=='edit.php' && isset($_GET['faqs_post_filter']) && $_GET['faqs_post_filter'] == 'posts_have_faqs' ) {
            $query->query_vars['meta_key'] = '_lbk_custom_faqs';
          }
        }

        /**
         * Enqueue scripts
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public function enqueue_script() {
            wp_enqueue_editor();
            $localize = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' )
            );

            wp_enqueue_script('lbk-custom-faq', LBK_FAQ_URL.'js/admin.js', array( 'jquery' ), lbkFAQs::VERSION, 'all' );
            wp_localize_script( 'lbk-custom-faq', 'lbk_custom_faq_script', $localize);

            wp_enqueue_style( 'lbk-faqs', LBK_FAQ_URL . 'css/admin.css', array(), lbkFAQs::VERSION, 'all' );
        }
    }
    new lbkFAQs_Admin();
}