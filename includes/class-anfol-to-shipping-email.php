<?php

include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email-new-order.php';
if ( ! class_exists( 'WC_Email_Commande_ToShipping' ) ) :
    class WC_Email_Commande_ToShipping extends WC_Email_New_Order {

        public function __construct() {
            $this->id = 'to_shipping';
            $this->title = __( 'Commande à expédier', 'woocommerce' );
            $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email', 'livraison@anfol.org' ) );
            $this->description = __( 'Cet email est envoyé lorsqu\'une commande WooCommerce passe au statut "A livrer".', 'woocommerce' );
            $this->template_html  = 'emails/admin-new-order.php';
			$this->template_plain = 'emails/plain/admin-new-order.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
            );

            add_action('woocommerce_order_status_processing_to_to-shipping', array( $this,'trigger') );

            WC_Email::__construct();
        }

        function trigger( $order_id, $order = null ) {
            $this->setup_locale();

            if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

            if ($this->is_enabled() && $this->get_recipient()) {
                $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            }

            $this->restore_locale();
        }

        public function get_default_subject() {
            return __( '[{site_title}]: Commande à livrer #{order_number}', 'woocommerce' );
        }

        /**
         * Get email heading.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'Commande à livrer: #{order_number}', 'woocommerce' );
        }
    }

endif;