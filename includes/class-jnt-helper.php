<?php

class Jnt_Helper {
    public $jnt_api = null;
    public function __construct() {
        $this->jnt_api = new Jnt_Api();
        
        if (!has_action("init", array($this, "jnt_act_run"))) {
            add_action("init", array($this, "jnt_act_run"));
        }
        // Add Payment Gateway Based Fees in Woocommerce
        add_action("woocommerce_cart_calculate_fees", array($this, "add_cod_calculated_fees") );
        // Update checkout on payment method change
        add_action("woocommerce_checkout_init", array($this, "refresh_checkout_page") );
        // Hook into the WooCommerce order status change event
        if (!has_action("woocommerce_order_status_changed", array($this, "wc_order_status_changed"))) {
            add_action("woocommerce_order_status_changed", array($this, "wc_order_status_changed"), 99999, 3);
        }
        // Register custom order statuses
        add_action("init", array($this, "register_order_statuses"));
        // Add custom order statuses to the list of order statuses in the WooCommerce admin
        add_filter("wc_order_statuses", array($this, "add_order_statuses"));
        // show order extra info on edit order page
        add_action("woocommerce_admin_order_data_after_shipping_address", array($this, "show_order_details"));
        // add custom cronjob interval
        add_filter("cron_schedules", array($this, "add_cron_interval"));
        // Hook the scheduling function to the activation of the plugin
        // Hook the callback function to the scheduled cron job
        if (!has_action("jnt_shipment_status_check")) {
            add_action("jnt_shipment_status_check", array($this, "cron_shipment_status_check_callback"));
        }
        $this->schedule_jnt_check();
        register_activation_hook(__FILE__, array($this, "schedule_jnt_check"));
        register_deactivation_hook( __FILE__, array($this, "unschedule_jnt_check") );
    }
    public function jnt_act_run(){
        if (is_blog_admin() && isset($_GET["jnt_act"], $_GET["post"]) && !empty($_GET["jnt_act"])) {
            $jnt_act = sanitize_text_field(trim($_GET["jnt_act"]));
            $order_id = sanitize_text_field(trim($_GET["post"]));
            if ($order = wc_get_order( $order_id )) {
                switch ($jnt_act) {
                    case 'sync':
                        @$this->process_order($order->get_id(), true, false);
                        @$this->refresh_order_status_from_jnt($order, true);
                        wp_redirect(remove_query_arg("jnt_act"));
                        exit;
                    break;
                    case 'a4':
                        $awbs = get_post_meta($order->get_id(), "jtawb", true);
                        $content = @$this->process_print([$awbs], true, $order->get_id());
                        $name = sanitize_file_name("{$order->get_id()}-a4.pdf");
                        $pdf = plugin_dir_path(dirname(__FILE__))."pdf/{$name}";
                        if (file_exists($pdf)) {
                            header("Content-Type: application/pdf");
                            header("Content-Disposition: inline; filename=\"print-document.pdf\"");
                            print_r($content);
                        }else{
                            wp_die("<strong>J&T Express UAE </strong>failed Creating PDF file.", "Error");
                        }
                        exit;
                    break;
                    case 'thermal':
                        $awbs = get_post_meta($order->get_id(), "jtawb", true);
                        $content = @$this->process_print_thermal([$awbs], true, $order->get_id());
                        $name = sanitize_file_name("{$order->get_id()}-thermal.pdf");
                        $pdf = plugin_dir_path(dirname(__FILE__))."pdf/{$name}";
                        if (file_exists($pdf)) {
                            header("Content-Type: application/pdf");
                            header("Content-Disposition: inline; filename=\"print-document.pdf\"");
                            print_r($content);
                        }else{
                            wp_die("<strong>J&T Express UAE </strong>failed Creating PDF file.", "Error");
                        }
                        exit;
                    break;
                    case 'thermal_new':
                        @$this->process_print_thermal_new([$order->get_id()]);
                        exit;
                    break;
                    case 'remove':
                        delete_post_meta($order->get_id(), "jtawb");
                        delete_post_meta($order->get_id(), "_jnt_pdf_a4");
                        delete_post_meta($order->get_id(), "_jnt_pdf_thermal");
                        delete_post_meta($order->get_id(), "_jnt_staff_name");
                        delete_post_meta($order->get_id(), "_jnt_staff_contact");
                        delete_post_meta($order->get_id(), "_jnt_last_shipping_status");
                        delete_post_meta($order->get_id(), "_jnt_last_changed_date");
                        delete_post_meta($order->get_id(), "_jnt_last_shipping_data");
                        $order->add_order_note("<strong>J&T Express</strong> meta removed.", false, true);
                        wp_redirect(remove_query_arg("jnt_act"));
                        exit;
                        // wp_die("All J&T Express meta are removed. Now you can sync Order to J&T again.", "Success", ["back_link"=>true]);
                    break;
                    default:
                    break;
                }
            }
        }
    }
    public function refresh_checkout_page() {
        wc_enqueue_js("jQuery( function($){ $('form.checkout').on('change', 'input[name=payment_method]', function(){ $(document.body).trigger('update_checkout'); /* Developed by amirhp.com */ }); });");
    }
    public function add_cod_calculated_fees ( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        $chosen_payment_id = WC()->session->get('chosen_payment_method');
        if ( empty( $chosen_payment_id ) ) return;
        $amount    = jnt_option("cod_fee_amount");
        $fee_label = jnt_option("cod_fee_label");
        $fee_type  = jnt_option("cod_fee_type");
        if ("0" == $fee_type || empty($amount) || empty($fee_label) || empty($fee_type)) return;
        // Here you can set up your fees (fixed or percentage)
        if ( $chosen_payment_id == "cod" ) {
            $fee_price = (float) ("percentage" == $fee_type) ? ($cart->subtotal * $amount / 100) : $amount;
            $cart->add_fee($fee_label, $fee_price, true );
        }
    }
    public function show_order_details($order){
        global $already_shown;
        if (isset($already_shown[$order->get_id()])) { return false; }
        $already_shown[$order->get_id()] = true;
        // $this->order_track_change_from_jnt($order);
        // $data = $this->refresh_order_status_from_jnt($order, true);
        $jtawb                = get_post_meta($order->get_id(), "jtawb", true);
        $staff_name           = get_post_meta($order->get_id(), "_jnt_staff_name", true);
        $staff_contact        = get_post_meta($order->get_id(), "_jnt_staff_contact", true);
        $last_shipping_status = get_post_meta($order->get_id(), "_jnt_last_shipping_status", true);
        $last_changed_date    = get_post_meta($order->get_id(), "_jnt_last_changed_date", true);
        $last_shipping_data   = get_post_meta($order->get_id(), "_jnt_last_shipping_data", true);
        $pdf_a4               = get_post_meta($order->get_id(), "_jnt_pdf_a4", true);
        $pdf_thermal          = get_post_meta($order->get_id(), "_jnt_pdf_thermal", true);
        ?>
        <div class="jnt-express-info dev-by-amirhp com">
            <?php if ( !empty($jtawb) ) : ?>
                <p class="form-field form-field-wide">
                    <strong><?=__("Tracking number:", "jnt");?></strong>
                    <span><a href="<?=admin_url("admin.php?page=jnt_main_page&tracking=$jtawb");?>"><?=$jtawb;?></a></span>
                </p>
            <?php endif; ?>
            <?php if ( !empty($last_changed_date) ) : ?>
                <p class="form-field form-field-wide">
                    <strong><?=__("Last Delivery Status Change:", "jnt");?></strong>
                    <span><?=date_i18n("Y/m/d H:i:s", strtotime($last_changed_date));?></span>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($staff_contact)) : ?>
                <p class="form-field form-field-wide">
                    <strong><?=__("Dispatcher Contact:", "jnt");?></strong>
                    <span><?="<a href='tel://$staff_contact'>$staff_contact</a> — $staff_name";?></span>
                </p>
            <?php endif; ?>
            <?php if ( isset($last_shipping_data->status) ) : ?>
                <p class="form-field form-field-wide">
                    <strong><?=__("Current Delivery Status:", "jnt");?></strong>
                    <span><?=$last_shipping_data->status;?></span>
                </p>
            <?php endif; ?>
            <div class="form-field form-field-wide">
                <a href="<?=add_query_arg(["jnt_act"=>"sync"]);?>" class="button button-secondary button-small"><span style="margin: 4px 0;" class="dashicons dashicons-update"></span>&nbsp;Sync into J&T</a>&nbsp;
                <?php if ( !empty($jtawb) ) : ?>
                    <a onclick="if(!confirm('Are you sure you want to remove J&T Express Meta?'))return false;" href="<?=add_query_arg(["jnt_act"=>"remove"]);?>" class="button button-secondary button-small" style="color: #d63638; border-color: #d63638;"><span style="margin: 4px 0;" class="dashicons dashicons-trash"></span>&nbsp;Remove J&T Meta</a>&nbsp;
                    <a target="_blank" href="<?=add_query_arg(["jnt_act"=>"a4"]);?>" class="button button-secondary button-small"><span style="margin: 4px 0;" class="dashicons dashicons-admin-site-alt3"></span>&nbsp;Live A4-PDF</a>&nbsp;
                    <a target="_blank" href="<?=add_query_arg(["jnt_act"=>"thermal"]);?>" class="button button-secondary button-small"><span style="margin: 4px 0;" class="dashicons dashicons-admin-site-alt3"></span>&nbsp;Live Thermal-PDF</a>
                <?php endif; ?>
                
                <p class="new">
                    <?php if ( !empty($pdf_a4) ) : ?>
                        <a target="_self" download href="<?=$pdf_a4;?>" class="button button-secondary button-small"><span style="margin: 4px 0;" class="dashicons dashicons-download"></span>&nbsp;Local A4-PDF</a>&nbsp;
                    <?php endif; ?>
                    <?php if ( !empty($pdf_thermal) ) : ?>
                        <a target="_self" download href="<?=$pdf_thermal;?>" class="button button-secondary button-small"><span style="margin: 4px 0;" class="dashicons dashicons-download"></span>&nbsp;Local Thermal-PDF</a>&nbsp;
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php 
    }
    #region cronjob
    public function add_cron_interval( $schedules ) {
        $min = (int) jnt_option("cronjob_interval", 30);
        $schedules['jntcron'] = array("interval" => $min * 60, "display" => "J&T Cron (each $min min)");
        return $schedules;
    }
    public function schedule_jnt_check() {
        if (!wp_next_scheduled("jnt_shipment_status_check")) {
            wp_schedule_event(time(), "jntcron", "jnt_shipment_status_check");
        }
    }
    public function unschedule_jnt_check() {
        wp_clear_scheduled_hook("jnt_shipment_status_check");
    }
    public function cron_shipment_status_check_callback() {
        // Get a list of orders you want to monitor
        $order_statuses_to_monitor = (array) jnt_option("monitoring_statuses", ["wc-processing"]);
    
        $orders = wc_get_orders(array(
            "limit"   => -1,
            "orderby" => "date",
            "order"   => "DESC",
            "status"  => $order_statuses_to_monitor,
        ));

        foreach ($orders as $order) {
            // Get the last checked date from order metadata
            $last_changed_date      = get_post_meta($order->get_id(), '_jnt_last_changed_date', true);
            $last_shipping_status   = get_post_meta($order->get_id(), '_jnt_last_shipping_status', true);
            $last_checked_timestamp = strtotime($last_changed_date);
            $current_timestamp      = current_time('timestamp');
            $current_status         = $this->refresh_order_status_from_jnt($order);

            // Skip for delivered orders
            if ($current_status == 'signed' && $current_status == 'delivering' && $current_status == 'delivered') continue;

            // Shipment status has changed since last check, update the last checked date & status
            if ($current_status != $last_shipping_status) {
                $this->order_track_change_from_jnt($order);
            }
            // Shipment status has NOT changed since last check, Now see if the status hasn't changed for "X days"
            elseif ($last_changed_date){
                $x_days = jnt_option("notify_missed_delay", 7); // Replace with the desired number of days
                if ($current_timestamp - $last_checked_timestamp >= $x_days * 24 * 60 * 60) {
                    $this->notify_missed_order($order);
                }
            }
        }
        
    }
    #endregion
    #region get and set jnt_status
    public function refresh_order_status_from_jnt($order, $raw=false){
        $status = "unknown";
        if (!is_object($order)) $order = wc_get_order($order);
        $track_number = get_post_meta($order->get_id(), 'jtawb', true);
        if (!$track_number || empty($track_number)) return false;
        $awbs = get_post_meta($order->get_id(), "jtawb", true);
        @$this->process_print([$awbs], true, $order->get_id());
        @$this->process_print_thermal([$awbs], true, $order->get_id());
        @$this->tracking($track_number);    
        try {
            $track = $this->tracking($track_number);
            $res = json_decode($track);
            if (isset($res->data[0]->status)) {
                $status = sanitize_title(strtolower($res->data[0]->status));
                $data   = $res->data[0];
                $status = sanitize_title(strtolower($data->status));
                $last_shipping_status = get_post_meta($order->get_id(), '_jnt_last_shipping_status', true);
                if ($status != $last_shipping_status) {
                    update_post_meta($order->get_id(), '_jnt_last_changed_date', $data->scanTime);
                    $order->add_order_note("<strong>J&T Express:</strong> shipment status changed from <u>$last_shipping_status</u> to <u>$status</u>.", false, true);
                }
                update_post_meta($order->get_id(), '_jnt_staff_contact', $data->staffContact);
                update_post_meta($order->get_id(), '_jnt_last_shipping_status', $status);
                update_post_meta($order->get_id(), '_jnt_last_shipping_data'  , $data);
                if ($raw) return $res->data[0];
            }
        } catch (\Throwable $th) { }

        return $status;
    }
    public function order_track_change_from_jnt($order){
        if (!is_object($order)) $order = wc_get_order($order);
        // update the last checked date & status
        $data = $this->refresh_order_status_from_jnt($order, true);
        if (isset($data->status)){
            $status = sanitize_title(strtolower($data->status));           
            if ($status == "delivering" || $status == "signed") {
                update_post_meta($order->get_id(), '_jnt_staff_name', $data->staffName);
                update_post_meta($order->get_id(), '_jnt_staff_contact', $data->staffContact);
                $new_status = jnt_option("after_deliver_status");
                if ($new_status && "none" != $new_status && "0" != $new_status && $order->get_status() != $new_status) {
                    $order->update_status($new_status, __("Changed Order status because J&T Express Shipping status is {$data->status}.", "jnt") . "<br>");
                }
            }
            if ($status == "picked-up") {
                $order->update_status("wc-delivery-in-progress", __("Changed Order status because J&T Express Shipping status is {$data->status}.", "jnt"). "<br>");
            }
            if ($status == "returned") {
                $order->update_status("wc-returned", __("Changed Order status because J&T Express Shipping status is {$data->status}.", "jnt"). "<br>");
            }
            if ($status == "returned-signed") {
                $order->update_status("wc-returned-signed", __("Changed Order status because J&T Express Shipping status is {$data->status}.", "jnt"). "<br>");
            }
        }
    }
    #endregion
    #region notify admin
    public function notify_missed_order($order){
        if ("yes" != jnt_option("notify_missed_delivery")) return false;
        $email_list = array_map("trim", (array) explode(",", jnt_option("notify_missed_receivers")));
        $subject    = str_replace("{order_id}", $order->get_id(), jnt_option("notify_missed_subject"));
        $message    = str_replace("{order_id}", $order->get_id(), jnt_option("notify_missed_body"));
        // Set headers to send an HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        // Send the email to all recipients at once
        return wp_mail($email_list, $subject, $message, $headers);
    }
    #endregion
    #region order_status
    public function wc_order_status_changed($order_id, $old_status, $new_status) {
        // update latest order shipping data
        $this->order_track_change_from_jnt($order_id);
        $order = wc_get_order($order_id);
        if (!$order) return false;
        if ("1" == jnt_option("sync_type")){ // Automatically:1 / Manually: 0
            $condition = array_values((array) jnt_option("sync_condition"));
            $status_to_check = str_replace("wc-", "", $new_status);
            if ( $old_status != $new_status && (in_array("wc-".$status_to_check, $condition) || in_array($status_to_check, $condition)) ) {
                $second_condition = jnt_option("sync_shipping");
                if ($second_condition && !empty(trim($second_condition))) {
                    $order = wc_get_order($order_id);
                    if (!$order) return false;
                    $shippingMethodTitle = $order->get_shipping_method();
                    if (strpos($shippingMethodTitle, $second_condition) !== false) {
                        $this->process_order([$order_id], false, false);
                    }
                }
                else{
                    $this->process_order([$order_id], false, false);
                }
            }
        }

        if ("yes" == jnt_option("disable_cancellation") && ($new_status == "wc-cancelled" || $new_status == "cancelled")) {
            // $order->add_order_note("You cannot Cancel this Order because of its Picked-up by J&T Express before.");
            $order->update_status($old_status, "You cannot Cancel this Order because of its Picked-up by J&T Express before.<br>");
        }
        if ("yes" == jnt_option("cancel_jnt_order") && ($new_status == "wc-cancelled" || $new_status == "cancelled")) {
            $this->cancel_order([$order_id]);
        }
    }
    public function register_order_statuses() {
        register_post_status('wc-delivered', array(
            'label'                     => _x('Delivered', 'Order status', 'jnt'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Delivered <span class="count">(%s)</span>', 'Delivered <span class="count">(%s)</span>', 'jnt')
        ));
        register_post_status('wc-delivery-in-progress', array(
            'label'                     => _x('Delivery in Progress', 'Order status', 'jnt'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Delivery in Progress <span class="count">(%s)</span>', 'Delivery in Progress <span class="count">(%s)</span>', 'jnt')
        ));
        register_post_status('wc-returned', array(
            'label'                     => _x('Returned', 'Order status', 'jnt'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Delivery Returned<span class="count">(%s)</span>', 'Delivery Returned<span class="count">(%s)</span>', 'jnt')
        ));
        register_post_status('wc-returned-signed', array(
            'label'                     => _x('Returned Signed', 'Order status', 'jnt'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Delivery Returned Signed<span class="count">(%s)</span>', 'Delivery Returned Signed<span class="count">(%s)</span>', 'jnt')
        ));
    }
    public function add_order_statuses($order_statuses) {
        $new_order_statuses = array();
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-completed' === $key) {
                // Add 'Delivered' after 'Completed'
                $new_order_statuses['wc-delivery-in-progress'] = _x('Delivery in Progress', 'Order status', 'jnt');
                $new_order_statuses['wc-delivered'] = _x('Delivered', 'Order status', 'jnt');
                $new_order_statuses['wc-returned'] = _x('Returned', 'Order status', 'jnt');
                $new_order_statuses['wc-returned-signed'] = _x('Returned Signed', 'Order status', 'jnt');
            }
        }
        return $new_order_statuses;
    }
    #endregion

    
    #region internal functions
    public function shippingQuote($order, $weight) {
        //        $order = wc_get_order($order_id);

        //get_option('woocommerce_store_city');
        $shipping = get_option('woocommerce_jnt_settings');

        $quote = array(
            'password' => $shipping['apikey'],
            'cuscode' => $shipping['vipcode'],
            'weight' => $weight,
            'shipper_city' => get_option('woocommerce_store_city'),
            'shipper_province' => get_option('woocommerce_store_province'),
            'receiver_city' => $order->get_shipping_city(),
            'receiver_province' => $order->get_shipping_state()
        );

        return $this->jnt_api->shippingQuote($quote);
    }
    public function process_order($ids, $byref=false, $echo=true) {
        $merge = array(); $awb_no = "";
        $setting = get_option('woocommerce_jnt_settings');
        foreach ((array) $ids as $id) {
            $order = wc_get_order($id);
            if (!$order) continue;
            $prev_jtawb = get_post_meta($id, "jtawb", true);
            if (!empty($prev_jtawb) && strlen($prev_jtawb) == 15) continue;
            $order->add_order_note("Trying to Send Order to J&T ...", false, true);
            $sender = array(
                'sender_name'     => $setting['name'],
                'sender_phone'    => $setting['phone'],
                'sender_addr'     => implode(",", array( get_option('woocommerce_store_address'), get_option('woocommerce_store_address_2') )),
                'sender_city'     => get_option('woocommerce_store_city'),
                'sender_province' => $setting['province'],
                'sender_zip'      => get_option('woocommerce_store_postcode'),
                'cuscode'         => $setting['vipcode'],
                'password'        => $setting['apikey'],
            );
            $shipping_phone = (!empty($order->get_shipping_phone())) ? $order->get_shipping_phone() : $order->get_billing_phone();
            if (strpos($shipping_phone, '/') !== false) {
                $receiver_phone = explode("/", $shipping_phone);
                $receiver_phone = $receiver_phone[0];
            } else {
                $receiver_phone = $shipping_phone;
            }
            $receiver = array(
                'receiver_phone'    => $receiver_phone,
                'receiver_name'     => $order->has_shipping_address() ? $order->get_formatted_shipping_full_name() : $order->get_formatted_billing_full_name(),
                'receiver_addr'     => $order->has_shipping_address() ? WC()->countries->get_formatted_address($order->get_address("shipping"), ", ") : WC()->countries->get_formatted_address($order->get_address("billing"), ", "),
                'receiver_city'     => $order->has_shipping_address() ? $order->get_shipping_city() : $order->get_billing_city(),
                'receiver_province' => $order->has_shipping_address() ? $order->get_shipping_state() : $order->get_billing_state(),
                'receiver_zip'      => $order->has_shipping_address() ? $order->get_shipping_postcode() : $order->get_billing_postcode(),
            );
            $weight_unit = get_option('woocommerce_weight_unit');
            $kg          = 1000;
            $weight      = 0;
            $item_name   = [];
            foreach ($order->get_items() as $item) {
                $item_qty = $item->get_quantity();
                $_product = $item->get_product();
                if (!$_product->is_virtual()) {
                    if (is_numeric($_product->get_weight()) && is_numeric($item_qty)) {
                        $weight += ($_product->get_weight() * $item_qty);
                    }
                    $item_name[] = $item->get_name() . "($item_qty)";
                }
            }
            if ($weight == '0') {
                $weight = 0.1;
            } else {
                if ($weight_unit == 'kg') {
                    $weight = $weight;
                } else if ($weight_unit == 'g') {
                    $weight = $weight / $kg;
                    if ($weight <= 0.01) {
                        $weight = 0.01;
                    }
                }
            }
            $items = array(
                'id'             => $id,
                'orderid'        => date('ymdHi') . str_pad($id, 6, 0, STR_PAD_LEFT),
                'weight'         => $weight,
                'item'           => implode(", ", $item_name),
                'qty'            => $order->get_item_count(),
                'payType'        => 'PP_PM',
                'goodsType'      => 'PARCEL',
                'servicetype'    => $setting['service'],
                'expresstype'    => 'EZ',
                'goodsdesc'      => $order->get_customer_note(),
                'total'          => $order->get_total(),
                'currency'       => $order->get_currency(),
                'payment_method' => $order->get_payment_method()
            );
            array_push($merge, array_merge($sender, $receiver, $items));
        }
        $details = $this->jnt_api->order($merge);
        foreach ($details as $res_api) {
            $details = json_decode($res_api["detail"]);
            if ($details) {
                if (empty($details->data) && isset($details->msg) && "success" != $details->msg) {
                    $temp_order = wc_get_order($res_api["id"]);
                    if ($temp_order) $temp_order->add_order_note("<strong>Process Order #{$res_api["id"]} Error</strong><br>{$details->msg}");
                    if ($echo) {
                        wp_die("<h2>Process Order #{$res_api["id"]} Error</h2>{$details->msg}", "Process Order #{$res_api["id"]} Error", ["back_link"=>true]);
                    }
                }
                if (isset($details->details[0]->awb_no) && !empty($details->details[0]->awb_no) && !is_null($details->details[0]->awb_no)) {
                    $awb_no = $details->details[0]->awb_no;
                    $order = wc_get_order($res_api["id"]);
                    $order->add_order_note("<strong>J&T Express Tracking number: <strong><pre>{$details->details[0]->awb_no}</pre>");
                    $new_status = jnt_option("after_sync_status");
                    update_post_meta($id, "jtawb", $details->details[0]->awb_no);
                    update_post_meta($id, "jtorder", $details->details[0]->orderid);
                    if ($new_status && "none" != $new_status && "0" != $new_status && $order->get_status() != $new_status) {
                        $order->update_status($new_status, "Changed Order status because it is sent to J&T Express.<br>");
                    }
                }
            }
        }
        return true == $byref ? $awb_no : $details;
    }
    public function process_print_thermal_new($ids) {
        $upOne = realpath(dirname(__FILE__) . '/..');
        include $upOne . '/admin/view/thermal-new.php';
    }
    public function process_print_thermal($awbs=[], $return=false, $order_id="") {
        $setting = get_option('woocommerce_jnt_settings');
        $cuscode = $setting['vipcode'];
        $awbs = implode(",", $awbs);
        $content = $this->jnt_api->print($cuscode, $awbs, $return);
        $name = sanitize_file_name("{$order_id}-thermal.pdf");
        $pdf = plugin_dir_path(dirname(__FILE__))."pdf/{$name}";
        $url = plugins_url("/pdf/{$name}", dirname(__FILE__));
        if (file_exists($pdf)) unlink($pdf);
        if (false !== file_put_contents($pdf, print_r($content, 1))) update_post_meta($order_id, "_jnt_pdf_thermal", $url);
        if ($return) return $content;
    }
    public function process_print($awbs=[], $return=false, $order_id="") {
        $setting = get_option('woocommerce_jnt_settings');
        $cuscode = $setting['vipcode'];
        $awbs = implode(",", $awbs);
        $content = $this->jnt_api->printA4($cuscode, $awbs, $return);
        $name = sanitize_file_name("{$order_id}-a4.pdf");
        $pdf = plugin_dir_path(dirname(__FILE__))."pdf/{$name}";
        $url = plugins_url("/pdf/{$name}", dirname(__FILE__));
        if (file_exists($pdf)) unlink($pdf);
        if (false !== file_put_contents($pdf, print_r($content, 1))) update_post_meta($order_id, "_jnt_pdf_a4", $url);
        if ($return) return $content;
    }
    public function tracking($awb) {
        $awb = trim($awb);
        if (strlen($awb) == 15) {
            return $this->jnt_api->tracking($awb);
        } else {
            return "Invalid Tracking Number";
        }
    }
    public function cancel_order($ids) {
        $awbs = array();
        foreach ($ids as $key => $id) {
            $infos = array( 'id' => $id, 'awb' => get_post_meta($id, 'jtawb', true), );
            array_push($awbs, $infos);
        }
        return $this->jnt_api->cancel($awbs);
    }
    #endregion
}
