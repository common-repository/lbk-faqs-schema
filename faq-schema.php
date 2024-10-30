<?php
/**
 * LBK FAQs Schema
 * 
 * @package LBK FAQs Schema
 * @author Briki
 * @copyright 2021 LBK
 * @license GPL-2.0-or-later
 * @category plugin
 * @version 1.2.0
 * 
 * @wordpress-plugin
 * Plugin Name:       LBK FAQs Schema
 * Plugin URI:        https://lbk.vn/
 * Description:       This plugin will create FAQs Schema for Post, Page, Product Woo
 * Version:           1.2.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Briki - LBK
 * Author             URI: https://facebook.com/vuong.briki
 * Text Domain:       lbk-faqs
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain path:       /languages/
 * 
 * LBK FAQs Schema is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *  
 * LBK FAQs Schema is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with LBK FAQs Schema. If not, see <http://www.gnu.org/licenses/>.
*/

// Die if accessed directly
if ( !defined('ABSPATH') ) die( 'What are you doing here? You silly human!' );

if ( !class_exists('lbkFAQs') ) {
    /**
     * class lbkFAQs
     */
    final class lbkFAQs {
        /**
         * Current version
         * 
         * @since 1.0
         * @var string
         */
        const VERSION = '1.2.0';

        /**
         * Stores the instance of this class
         * 
         * @access private
         * @since 1.0
         * @static
         * 
         * @var lbkFAQs
         */
        private static $instance;

        /**
         * A dummny contructor to prevent the class from being loaded more than once
         * 
         * @access public
         * @since 1.0
         */
        public function __construct() { 
            /** Do nothing here */
        }

        /**
         * @access private
         * @since 1.0
         * @static
         * 
         * @return lbkFAQs
         */
        public static function instance() {
            if ( !isset( self::$instance ) && !( self::$instance instanceof lbkFAQs ) ) {
                self::$instance = new lbkFAQs();

                self::defineConstants();
                self::includes();
                self::hooks();

                // self::loadTextdomain();
            }

            return self::$instance;
        }

        /**
         * Define the plugin constants.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        private static function defineConstants() {
            define( 'LBK_FAQ_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
            define( 'LBK_FAQ_BASE_NAME', plugin_basename( __FILE__ ) );
            define( 'LBK_FAQ_PATH', plugin_dir_path( __FILE__ ) );
            define( 'LBK_FAQ_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Includes the plugin dependency files.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        private static function includes() {
            if ( is_admin() ) {
                require_once( LBK_FAQ_PATH . 'includes/class.admin.php' );
                require_once( LBK_FAQ_PATH . 'includes/function.php' );
            }
            // require_once( LBK_FAQ_PATH . 'includes/public-function.php' );
            // require_once( LBK_FAQ_PATH . 'includes/template.php' );
        }

        /**
         * Add the core action filter hook.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        private static function hooks() {
            // wp_enqueue_style( 'lbk-faq-style', LBK_FAQ_URL . 'assets/css/style.css', array( 'wp-color-picker' ), lbkFAQs::VERSION );
            add_action( 'wp_footer', array( __CLASS__, 'add_faq_script') );
            add_filter( 'the_content', array( __CLASS__, 'faqs_in_footer_post') );
            add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScripts') );
        }

        /**
         * Call back for the `wp_enqueue_scripts` action.
         * 
         * Register add enqueue CSS and javascript files for frontend
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public static function enqueueScripts() {
            // If SCRIPT_DEBUG is set and TRUE load the non-minifed JS files, otherwise, load the minifed files.
            // $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_style( 'lbk-faqs', LBK_FAQ_URL . 'css/front.css', array(), lbkFAQs::VERSION, 'all' );
            wp_enqueue_script( 'lbk-faqs', LBK_FAQ_URL . 'js/front.js', array( 'jquery' ), lbkFAQs::VERSION, 'all' );
        }

        /**
         * Prints out inline CSS after the core CSS file to allow overriding core styles via options.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public static function inlineCSS() {

        }

        /**
         * Add script FAQs.
         * 
         * @access private
         * @since 1.0
         * @static
         */
        public static function add_faq_script() {
            $custom_faqs = get_post_meta(get_the_id(), '_lbk_custom_faqs', true);

            if ( ! isset( $custom_faqs ) || ! $custom_faqs ) return;

            $i = 0; $num_custom_faqs = count($custom_faqs);?>
            
                <!--#region FAQPage-->
                <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "FAQPage",
                        "mainEntity": [ 
                                <?php foreach($custom_faqs as $faq) : ?>
                                {
                                    "@type": "Question",
                                    "name": "<?php esc_html_e($faq['question']); ?>",
                                    "acceptedAnswer": {
                                            "@type": "Answer",
                                            "text": "<?php esc_html_e($faq['answer']); ?>"
                                        }
                                }<?php if(++$i !== $num_custom_faqs) echo ","; ?>
                                <?php endforeach; ?>
                        ]
                    }
                </script>
                <!--#endregion-->
            <?php
        }

        /**
         * Enable FAQs in footer post
         */
        public static function faqs_in_footer_post( $content ) {
          $post_id = get_the_ID();

          $faqs_in_footer = get_post_meta( $post_id, '__lbk_faqs_in_footer_post', true );
          if (!$faqs_in_footer) return $content;

          $custom_faqs = get_post_meta( $post_id, '_lbk_custom_faqs', true );
          if ( ! isset( $custom_faqs ) || ! $custom_faqs ) return $content;

          ob_start();
          ?>

          <div class="faqs">
            <h3 class="faqs-title">Câu hỏi thường gặp</h3>
            <?php foreach($custom_faqs as $index => $faq) : ?>
              <div class="faqs-item">
                <div class="faqs-item__question">
                  <?php esc_html_e($faq['question']); ?>
                  <span class="dashicons dashicons-arrow-down-alt2"></span>
                </div>
                <div class="faqs-item__content hidden">
                  <?php echo wpautop( $faq['answer'] ); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php
          $faqs = ob_get_clean();

          return $content . $faqs;
        }
    } // end class
    
    /**
     * The main function reponsible for returning the LBK Fixed Contact instance to function everywhere.
     * 
     * Use this function like you would a global variable, except without needing to declare the global.
     * 
     * Example: <?php $instance = lbkFAQs(); ?>
     * 
     * @access public
     * @since 1.0
     * 
     * @return lbkFAQs
     */
    function lbkFAQs() {
        return lbkFAQs::instance();
    }

    // Start LBK Fixed Contact
    add_action( 'plugins_loaded', 'lbkFAQs' );
}