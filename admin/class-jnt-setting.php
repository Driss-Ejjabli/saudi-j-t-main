<?php

class Jnt_Settings {

    public function __construct() {

        $this->define_hooks();
    }


    /**
     * Define hooks
     */
    protected function define_hooks() {

        add_filter('woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
        // add_filter('woocommerce_general_settings', [$this, 'jnt_general_settings']);

        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'open_shipping_address']);
    }

    public function add_shipping_method ($methods) {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jnt-shipping.php';
        $methods['jnt'] = Jnt_Shipping::class;
        return $methods;
    }

    public function jnt_general_settings($settings) {
        $province = array(array(
            'title'    => __( 'Province', 'woocommerce' ),
            'desc'     => __( 'J&T Express: The province in which your business is located.', 'woocommerce' ),
            'id'       => 'woocommerce_store_province',
            'default'  => 'Makkah',
            'type'     => 'text',
            'desc_tip' => false,
        ));

        $first = array_splice($settings,0, 4);

        return array_merge($first, $province, $settings);
    }

    public function open_shipping_address() {

        remove_meta_box('woocommerce-order-downloads', 'shop_order', 'normal');
        remove_meta_box('postcustom', null, 'normal');

        echo '<script type="text/javascript">window.onload = function () {
                const order_data_column = document.getElementsByClassName("order_data_column")[2];
                order_data_column.getElementsByClassName("edit_address")[0].click();
            }</script>';
    }

}