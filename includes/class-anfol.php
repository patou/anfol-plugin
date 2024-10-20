<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/patou
 * @since      1.0.0
 *
 * @package    Anfol
 * @subpackage Anfol/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Anfol
 * @subpackage Anfol/includes
 * @author     Patrice de Saint Steban <patrice@desaintsteban.fr>
 */
class Anfol
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Anfol_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('ANFOL_VERSION')) {
			$this->version = ANFOL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'anfol';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Anfol_Loader. Orchestrates the hooks of the plugin.
	 * - Anfol_i18n. Defines internationalization functionality.
	 * - Anfol_Admin. Defines all hooks for the admin area.
	 * - Anfol_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-anfol-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-anfol-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-anfol-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-anfol-public.php';

		$this->loader = new Anfol_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Anfol_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Anfol_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Anfol_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'remove_menu', 999);
		$this->loader->add_action('woocommerce_admin_order_actions', $plugin_admin, 'order_actions_to_shipping', 10, 2);
		$this->loader->add_action('woocommerce_admin_order_preview_actions', $plugin_admin, 'order_preview_actions_to_shipping', 10, 2);
		$this->loader->add_filter('bulk_actions-edit-shop_subscription', $plugin_admin, 'update_subscription_price_add_custom_bulk_action');
		$this->loader->add_action('load-edit.php', $plugin_admin, 'update_subscription_price_parse_bulk_actions');
		$this->loader->add_filter('handle_bulk_actions-edit-shop_subscription', $plugin_admin, 'update_subscription_price_handle_custom_bulk_action', 10, 3);
		$this->loader->add_action('admin_notices', $plugin_admin, 'update_subscription_price_custom_bulk_action_admin_notice');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Anfol_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('tablepress_cell_content', $plugin_public, 'tablepress_chants_buy_button', 10, 4);
		$this->loader->add_action('init', $plugin_public, 'register_to_shipping_order_status');
		$this->loader->add_action('wc_order_statuses', $plugin_public, 'add_to_shipping_to_order_statuses');
		$this->loader->add_action('woocommerce_order_status_changed', $plugin_public, 'change_order_status_to_shipping', 10, 4);
		$this->loader->add_action('woocommerce_email_classes', $this, 'register_order_to_shipping_email');
	}

	function register_order_to_shipping_email($email_classes)
	{
		include_once("class-anfol-to-shipping-email.php");
		$email_classes['wc_to_shipping'] = new WC_Email_Commande_ToShipping();
		return $email_classes;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Anfol_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}

}
