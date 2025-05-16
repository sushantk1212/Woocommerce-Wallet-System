<?php
/**
 * Plugin Name: Simple WooCommerce Wallet System
 * Description: A simple wallet system for WooCommerce. 
 * Version: 1.0
 * Author: Sushant Khadilkar
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Wallet_System {

    public function __construct() {
        add_action('show_user_profile', [$this, 'walletBalanceField']);
        add_action('edit_user_profile', [$this, 'walletBalanceField']);
        add_action('personal_options_update', [$this, 'SaveWalletBalance']);
        add_action('edit_user_profile_update', [$this, 'SaveWalletBalance']);

        add_filter('woocommerce_payment_gateways', [$this, 'addWalletGateway']);
        add_action('plugins_loaded', [$this, 'initWalletGateway']);
    }

    // Add a field to the user profile page to manage wallet balance
    public function walletBalanceField($user) {
        $balance = get_user_meta($user->ID, '_wallet_balance', true);
        ?>
        <h3>Wallet Balance</h3>
        <table class="form-table">
            <tr>
                <th><label for="wallet_balance">Wallet Balance</label></th>
                <td>
                    <input type="number" step="0.01" name="wallet_balance" id="wallet_balance" value="<?php echo esc_attr($balance); ?>" class="regular-text" />
                    <p class="description">Enter the user's wallet balance in store currency.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    // Save the wallet balance when the user profile is updated
    public function SaveWalletBalance($user_id) {
        if (current_user_can('edit_user', $user_id)) {
            update_user_meta($user_id, '_wallet_balance', floatval($_POST['wallet_balance']));
        }
    }

    // Add the wallet payment gateway to WooCommerce
    public function addWalletGateway($gateways) {
        error_log('Adding Wallet gateway');
        $gateways[] = 'WC_Gateway_Wallet';
        return $gateways;
    }

    // Initialize the wallet payment gateway class
    public function initWalletGateway() {
        if (!class_exists('WC_Gateway_Wallet')) {
            include_once plugin_dir_path(__FILE__) . 'class-wc-gateway-wallet.php';
        }
    }
}

// Initialize the wallet system
new WC_Wallet_System();