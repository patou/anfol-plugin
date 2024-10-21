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
class Anfol_Admin
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/anfol-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/anfol-admin.js', array('jquery'), $this->version, false);

	}

	public function remove_menu()
	{
		if (class_exists('Jetpack') && !current_user_can('manage_options') && is_admin() && is_user_logged_in()) {
			remove_menu_page('jetpack');
			remove_menu_page('wpseo_workouts');
		}
		remove_submenu_page('options-general.php', 'git-updater-pricing');
		remove_submenu_page('mailpoet-newsletters', 'mailpoet-upgrade');
		remove_submenu_page('wpforms-overview', 'mailpoet-upgrade');
		remove_submenu_page('wphb', 'wphb-upgrade');
		remove_submenu_page('wphb', 'wphb-tutorials');
		remove_submenu_page('wpseo_dashboard', 'wpseo_workouts');
		remove_submenu_page('wpseo_dashboard', 'wpseo_redirects');
		remove_submenu_page('mailpoet-newsletters', 'mailpoet-automation');
	}

	public function order_actions_to_shipping($actions, $order)
	{
		if ($order->has_status('to-shipping')) {
			$actions['complete'] = array(
				'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id()), 'woocommerce-mark-order-status'),
				'name' => __('Complete', 'woocommerce'),
				'action' => 'complete',
			);
		}
		return $actions;
	}

	public function order_preview_actions_to_shipping($status_actions, $order)
	{
		if ($order->has_status('to-shipping')) {
			$status_actions['complete'] = array(
				'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id()), 'woocommerce-mark-order-status'),
				'name' => __('Completed', 'woocommerce'),
				'title' => __('Change order status to completed', 'woocommerce'),
				'action' => 'complete',
			);
		}
		return $status_actions;
	}

	// Ajouter une action personnalisée au menu déroulant des actions groupées d'abonnement
	public function update_subscription_price_add_custom_bulk_action($bulk_actions)
	{
		$bulk_actions['update_subscription_prices'] = __('Mettre à jour les prix des abonnements', 'text-domain');
		return $bulk_actions;
	}
	public function update_subscription_price_parse_bulk_actions()
	{

		// We only want to deal with shop_subscriptions. In case any other CPTs have an 'active' action
		if (!isset($_REQUEST['post_type']) || 'shop_subscription' !== $_REQUEST['post_type'] || !isset($_REQUEST['post'])) {
			return;
		}

		// Verify the nonce before proceeding, using the bulk actions nonce name as defined in WP core.
		check_admin_referer('bulk-posts');

		$action = '';

		if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) { // phpcs:ignore
			$action = wc_clean(wp_unslash($_REQUEST['action']));
		} elseif (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) { // phpcs:ignore
			$action = wc_clean(wp_unslash($_REQUEST['action2']));
		}

		if (!in_array($action, ['update_subscription_prices'], true)) {
			return;
		}

		$subscription_ids = array_map('absint', (array) $_REQUEST['post']);
		$base_redirect_url = wp_get_referer() ? wp_get_referer() : '';
		$redirect_url = $this->update_subscription_price_handle_custom_bulk_action($base_redirect_url, $action, $subscription_ids);

		wp_safe_redirect($redirect_url);
		exit();
	}


	// Traiter l'action personnalisée
	private function update_subscription_price_handle_custom_bulk_action($redirect_to, $doaction, $post_ids)
	{
		if ($doaction !== 'update_subscription_prices') {
			return $redirect_to;
		}

		$update_count = 0;

		foreach ($post_ids as $post_id) {
			$subscription = wcs_get_subscription($post_id);

			if (!$subscription) {
				continue;
			}
			$subscription_updated = 0;

			foreach ($subscription->get_items() as $item_id => $item) {
				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				$product = wc_get_product($variation_id ? $variation_id : $product_id);


				if ($product && ($product->is_type('subscription') || $product->is_type('variable-subscription') || $product->is_type('subscription_variation'))) {
					// Récupérer le nouveau prix du produit
					$new_price = wc_get_price_excluding_tax($product);

					// Mettre à jour le prix de l'article de l'abonnement
					$item->set_subtotal($new_price);
					$item->set_total($new_price);
					$item->calculate_taxes();
					$item->save();
					$update_count++;
					$subscription_updated++;
				}
			}

			// Sauvegarder l'abonnement après la modification
			if ($subscription_updated > 0) {
				$subscription->calculate_totals();
				$subscription->save();
			}
		}

		// Ajouter un paramètre de requête pour confirmer que l'action s'est terminée
		$redirect_to = add_query_arg('bulk_update_subscription_prices', $update_count, $redirect_to);
		return $redirect_to;
	}

	// Afficher un message de confirmation après l'exécution de l'action
	public function update_subscription_price_custom_bulk_action_admin_notice()
	{
		if (!empty($_REQUEST['bulk_update_subscription_prices'])) {
			$updated_count = intval($_REQUEST['bulk_update_subscription_prices']);
			printf(
				'<div id="message" class="updated fade"><p>' .
				_n('%s abonnement mis à jour avec succès.', '%s abonnements mis à jour avec succès.', $updated_count, 'text-domain') .
				'</p></div>',
				$updated_count
			);
		}
	}



}
