<?php
/**
 * Auto Load Next Post - Admin Assets.
 *
 * @since    1.6.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  Auto Load Next Post/Admin
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ALNP_Admin_Assets' ) ) {

	class ALNP_Admin_Assets {

		/**
		 * Constructor
		 *
		 * @access  public
		 */
		public function __construct() {
			// Register scripts and styles for settings page.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10 );

			// Register Stylesheet for Dark Mode if active.
			add_action( 'doing_dark_mode', array( $this, 'do_dark_mode' ), 99 );

			// Adds admin body classes.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		} // END __construct()

		/**
		 * Registers and enqueues Stylesheets.
		 *
		 * @access public
		 */
		public function admin_styles() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, alnp_get_admin_screens() ) ) {
				Auto_Load_Next_Post::load_file( AUTO_LOAD_NEXT_POST_SLUG . '_admin', '/assets/css/admin/auto-load-next-post' . AUTO_LOAD_NEXT_POST_SCRIPT_MODE . '.css' );

				$current_view = ! empty( $_GET['view'] ) ? sanitize_title( wp_unslash( $_GET['view'] ) ) : '';

				// Dont load stylesheet if viewing any of these pages.
				$dont_style = array( 'getting-started', 'setup-wizard', 'extensions', 'videos' );
				if ( ! in_array( $current_view, $dont_style ) ) {

					// Select2 - Make sure that we remove other registered Select2 to prevent styling issues.
					if ( wp_script_is( 'select2', 'registered' ) ) {
						wp_dequeue_style( 'select2' );
						wp_deregister_style( 'select2' );
					}

					Auto_Load_Next_Post::load_file( 'select2', '/assets/css/libs/select2.min.css' );
				}
			}
		} // END admin_styles()

		/**
		 * Registers and enqueue JavaScripts.
		 *
		 * @access public
		 */
		public function admin_scripts() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( $screen_id == 'settings_page_auto-load-next-post' ) {

				// Load jQuery Confirm
				Auto_Load_Next_Post::load_file( 'jquery-confirm', '/assets/css/libs/jquery-confirm.min.css' );
				Auto_Load_Next_Post::load_file( 'jquery-confirm', '/assets/js/libs/jquery-confirm.min.js', true, array( 'jquery' ), '3.3.4', true );

				$current_view = ! empty( $_GET['view'] ) ? sanitize_title( wp_unslash( $_GET['view'] ) ) : '';

				switch( $current_view ) {
					case 'setup-wizard':
						// Scanner.
						Auto_Load_Next_Post::load_file( AUTO_LOAD_NEXT_POST_SLUG . '_scanner', '/assets/js/admin/scanner' . AUTO_LOAD_NEXT_POST_SCRIPT_MODE . '.js', true, array( 'jquery' ), AUTO_LOAD_NEXT_POST_VERSION, true );

						// Variables for Scanner JavaScript.
						wp_localize_script( AUTO_LOAD_NEXT_POST_SLUG . '_scanner', 'alnp_scanner_params', array(
							'is_rtl'                      => is_rtl() ? true : false,
							'ajax_url'                    => admin_url( 'admin-ajax.php', 'relative' ),
							'random_page'                 => alnp_get_random_page_permalink(),
							'i18n_searching'              => esc_html__( 'Searching...', 'auto-load-next-post' ),
							'i18n_scanning_theme'         => esc_html__( 'Scanning Theme', 'auto-load-next-post' ),
							'i18n_scanning_theme_content' => esc_html__( 'Currently scanning a post on your site for your theme selectors. Please wait...', 'auto-load-next-post' ),
							'i18n_please_wait'            => esc_html__( 'Please Wait', 'auto-load-next-post' ),
							'i18n_loading_post'           => esc_html__( 'Loading post...', 'auto-load-next-post' ),
							'i18n_copied'                 => esc_html__( 'Copied', 'auto-load-next-post' ),
							'i18n_copy_title'             => esc_html__( 'Click to copy theme selector', 'auto-load-next-post' ),
							'i18n_post_nav_missing'       => esc_html__( 'Post Navigation Missing?', 'auto-load-next-post' ),
							'i18n_error_post_nav'         => sprintf( esc_html__( '%1$s requires a theme with a post navigation in order to work and the setup wizard did not detect one. You may scan again to be sure. If you get the same results, %1$s may not recognise it.', 'auto-load-next-post' ), esc_html__( 'Auto Load Next Post', 'auto-load-next-post' ) ),
						) );

						break;
					default:
						// Select2 - Make sure that we remove other registered Select2 to prevent plugin conflict issues.
						if ( wp_script_is( 'select2', 'registered' ) ) {
							wp_dequeue_script( 'select2' );
							wp_deregister_script( 'select2' );
						}

						// Load Select2
						Auto_Load_Next_Post::load_file( 'select2', '/assets/js/libs/select2.min.js', true, array( 'jquery' ), '4.0.5', true );

						// Auto Load Next Post Preview.
						Auto_Load_Next_Post::load_file( AUTO_LOAD_NEXT_POST_SLUG . '_pro_preview', '/assets/js/admin/pro-preview' . AUTO_LOAD_NEXT_POST_SCRIPT_MODE . '.js', true, array( 'jquery', 'jquery-confirm' ), AUTO_LOAD_NEXT_POST_VERSION, true );

						// Variables for Pro Preview JavaScript.
						wp_localize_script( AUTO_LOAD_NEXT_POST_SLUG . '_pro_preview', 'alnp_pro_preview_params', array(
							'is_rtl'                    => is_rtl() ? true : false,
							'i18n_coming_soon'          => esc_html__( 'Coming Soon', 'auto-load-next-post' ),
							'i18n_coming_soon_content'  => sprintf( esc_html__( '%1$s%3$s%2$s coming soon with more features. Sign up in the sidebar to be notified!', 'auto-load-next-post' ), '<strong>', '</strong>', esc_html__( 'Auto Load Next Post Pro', 'auto-load-next-post' ) ),
							'i18n_comments_content'     => sprintf( esc_html__( '%1$sComments:%2$s More control on how comments should interact when each post loads.', 'auto-load-next-post' ), '<strong>', '</strong>' ),
							'i18n_load_scroll_content'  => sprintf( esc_html__( '%1$sLoad and Scroll:%2$s Choose what loads next, enable to load split posts, scroll transistion and scroll speed.', 'auto-load-next-post' ), '<strong>', '</strong>' ),
							'i18n_restrictions_content' => sprintf( esc_html__( '%1$sRestrictions:%2$s Manage which post types %3$s should load on, how many posts to load per session and which user roles and users are excluded from loading posts.', 'auto-load-next-post' ), '<strong>', '</strong>', esc_html__( 'Auto Load Next Post', 'auto-load-next-post' ) ),
							'i18n_query_content'        => sprintf( esc_html__( '%1$sQuery:%2$s Customise the query of posts before %3$s loads them including excluded posts and post formats.', 'auto-load-next-post' ), '<strong>', '</strong>', esc_html__( 'Auto Load Next Post', 'auto-load-next-post' ) ),
							'i18n_license_content'      => esc_html__( 'License is managed here for support and remote plugin updates.', 'auto-load-next-post' )
						) );

						// Load plugin settings.
						Auto_Load_Next_Post::load_file( AUTO_LOAD_NEXT_POST_SLUG . '_admin', '/assets/js/admin/settings' . AUTO_LOAD_NEXT_POST_SCRIPT_MODE . '.js', true, array( 'jquery' ), AUTO_LOAD_NEXT_POST_VERSION, true );

						// Variables for Admin Settings JavaScript.
						wp_localize_script( AUTO_LOAD_NEXT_POST_SLUG . '_admin', 'alnp_settings_params', array(
							'is_rtl'                    => is_rtl() ? 'rtl' : 'ltr',
							'i18n_nav_warning'          => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'auto-load-next-post' ),
							'i18n_reset_warning'        => sprintf( esc_html__( 'This will reset all settings back to default and re-initialize %s. Are you sure?', 'auto-load-next-post' ), esc_html__( 'Auto Load Next Post', 'auto-load-next-post' ) ),
							'i18n_setup_wizard_warning' => esc_html__( 'The setup wizard will override settings that you may already have working. Are you sure?', 'auto-load-next-post' ),
							'i18n_coming_soon'          => esc_html__( 'Coming Soon', 'auto-load-next-post' ),
							'i18n_continue'             => esc_html__( 'Continue', 'auto-load-next-post' ),
							'i18n_save'                 => esc_html__( 'Save', 'auto-load-next-post' ),
							'i18n_save_recommendation'  => sprintf( esc_html__( 'Press the %1$sSave Changes%2$s button to keep changes.', 'auto-load-next-post' ), '<strong>', '</strong>' ),
							'i18n_warning'              => esc_html__( 'Warning', 'auto-load-next-post' ),
						) );

						break;
				}
			}
		} // END admin_scripts()

		/**
		 * Adds support for displaying plugin pages in DARK MODE.
		 *
		 * @access public
		 */
		public function do_dark_mode() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( $screen_id == 'settings_page_auto-load-next-post' ) {
				Auto_Load_Next_Post::load_file( AUTO_LOAD_NEXT_POST_SLUG . '_dark_mode', '/assets/css/admin/auto-load-next-post-dark-mode' . AUTO_LOAD_NEXT_POST_SCRIPT_MODE . '.css' );
			}
		} // END do_dark_mode()

		/**
		 * Adds admin body classes depending on what page of 
		 * Auto Load Next Post the user is viewing.
		 *
		 * @access public
		 * @since  1.6.0
		 * @param  string $classes
		 * @return string $classes
		 */
		public function admin_body_class( $classes ) {
			$current_view = ! empty( $_GET['view'] ) ? sanitize_title( wp_unslash( $_GET['view'] ) ) : '';

			switch( $current_view ) {
				case 'getting-started':
					$classes = ' alnp-getting-started ';
					break;
				case 'setup-wizard':
					$classes = ' alnp-setup-wizard ';
					break;
				default:
					$classes = '';
					break;
			}
		 
			return $classes;
		}

	} // END class

} // END if class exists

return new ALNP_Admin_Assets();
