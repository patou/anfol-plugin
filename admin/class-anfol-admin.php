<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/patou
 * @since      1.0.0
 *
 * @package    Anfol
 * @subpackage Anfol/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Anfol
 * @subpackage Anfol/admin
 * @author     Patrice de Saint Steban <patrice@desaintsteban.fr>
 */
class Anfol_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anfol_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anfol_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anfol-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anfol_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anfol_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anfol-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function remove_menu() {
        if ( class_exists( 'Jetpack' ) && ! current_user_can( 'manage_options' ) && is_admin() && is_user_logged_in() ) {
            remove_menu_page( 'jetpack' );
            remove_menu_page( 'wpseo_workouts' );
        }
        remove_submenu_page( 'options-general.php', 'git-updater-pricing' );
        remove_submenu_page( 'mailpoet-newsletters', 'mailpoet-upgrade' );
        remove_submenu_page( 'wpforms-overview', 'mailpoet-upgrade' );
        remove_submenu_page( 'wphb', 'wphb-upgrade' );
        remove_submenu_page( 'wphb', 'wphb-tutorials' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_workouts' );
        remove_submenu_page( 'wpseo_dashboard', 'wpseo_redirects' );
        remove_submenu_page( 'mailpoet-newsletters', 'mailpoet-automation' );
    }

	public function order_actions_to_shipping($actions, $order) {
		if ( $order->has_status( 'to-shipping' ) ) {
			$actions['complete'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
				'name'   => __( 'Complete', 'woocommerce' ),
				'action' => 'complete',
			);
		}
		return $actions;
	}

	public function order_preview_actions_to_shipping($status_actions, $order) {
		if ( $order->has_status( 'to-shipping' ) ) {
			$status_actions['complete'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ),
				'name'   => __( 'Completed', 'woocommerce' ),
				'title'  => __( 'Change order status to completed', 'woocommerce' ),
				'action' => 'complete',
			);
		}
		return $status_actions;
	}


}
