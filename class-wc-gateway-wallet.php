<?php

if (!defined('ABSPATH')) exit;

class WC_Gateway_Wallet extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'wallet';
        $this->method_title = 'Wallet';
        $this->method_description = 'Allow customers to pay using their wallet balance.';
        $this->has_fields = false;

        $this->enabled = 'yes';
        $this->title = 'Wallet';
        $this->supports = [ 'products' ];

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }
    
    // display the payment method in the checkout page
    public function is_available() {
        if (!is_user_logged_in()) return false;
        $user_id = get_current_user_id();
        $balance = floatval(get_user_meta($user_id, '_wallet_balance', true));
        $total = WC()->cart ? WC()->cart->total : 0;

        return $balance >= $total;
    }

    // process the payment when the order is placed
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $balance = floatval(get_user_meta($user_id, '_wallet_balance', true));

        if ($balance >= $order->get_total()) {
            $new_balance = $balance - $order->get_total();
            update_user_meta($user_id, '_wallet_balance', $new_balance);

            $order->payment_complete();
            $order->add_order_note('Paid via Wallet. New balance: ' . wc_price($new_balance));

            return [
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        } else {
            wc_add_notice('Insufficient wallet balance.', 'error');
            return ['result' => 'fail'];
        }
    }
}