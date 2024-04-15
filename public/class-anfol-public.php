<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/patou
 * @since      1.0.0
 *
 * @package    Anfol
 * @subpackage Anfol/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Anfol
 * @subpackage Anfol/public
 * @author     Patrice de Saint Steban <patrice@desaintsteban.fr>
 */
class Anfol_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anfol-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anfol-public.js', array( 'jquery' ), $this->version, false );

	}

    public function tablepress_chants_buy_button($cell_content, $table_id, $row_idx, $col_idx) {
        if ($table_id === 'chants' && $col_idx === 7 && stripos($cell_content, 'http') === 0) {
            return "<a href='$cell_content' target='_blank' class='acheter-chants'>Acheter</a>";
        }
        return $cell_content;
    }

	public function register_to_shipping_order_status() {
		register_post_status( 'wc-to-shipping', array(
			'label'                     => 'À livrer',
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			'label_count'               => _n_noop( 'À livrer <span class="count">(%s)</span>', 'À livrer <span class="count">(%s)</span>' )
		) );
	}

	public function add_to_shipping_to_order_statuses( $order_statuses ) {
		$new_order_statuses = array();
		foreach ( $order_statuses as $key => $status ) {
			$new_order_statuses[ $key ] = $status;
			if ( 'wc-processing' === $key ) {
				$new_order_statuses['wc-to-shipping'] = 'À livrer';
			}
		}
		return $new_order_statuses;
	 }

	public function change_order_status_to_shipping($order_id, $from, $to, $order) {
		if ($to !== 'processing') return;
		$has_to_shipping = false;

        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();

            if ( ! $product->is_virtual() ) {
                $has_to_shipping = true;
                break;
            }
        }

        if ( $has_to_shipping ) {
            $order->update_status( 'to-shipping' );
        }
		else {
			$order->update_status( 'completed' );
		}
	}

}
