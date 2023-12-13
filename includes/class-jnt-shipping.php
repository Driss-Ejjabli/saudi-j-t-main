<?php

class Jnt_Shipping extends WC_Shipping_Method {

    public $jnt_helper = null;
    public $td = "jnt";

    public function __construct() {

        $this->jnt_helper = new Jnt_Helper();

        $this->id = "jnt";
        $this->method_title = __("J&T Express", 'jnt');
        $this->method_description = __("To start order to J&T Express (UAE), please fill in your info.", "jnt");

        $this->availability = 'including';
        $this->countries = array('SA');

        $this->init();

        $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
        $this->title   = isset($this->settings['title']) ? $this->settings['title'] : __('cloudways Shipping', 'cloudways');
    }
    public function init() {
        $this->init_form_fields();
        $this->init_settings();
        add_action("woocommerce_update_options_shipping_" . $this->id, array($this, 'process_admin_options'));
    }
    public function init_form_fields() {
        if (isset($_GET["page"], $_GET["tab"], $_GET["section"]) && $_GET["page"] == "wc-settings" && $_GET["tab"] == "shipping" && $_GET["section"] == "jnt") {
            wp_enqueue_script("jnt-setting", plugin_dir_url(dirname(__FILE__)) . "/assets/setting.js", ["jquery"], current_time("timestamp"));
        }
        $order_statuses_raw = wc_get_order_statuses();
        $order_statuses = array("0" => __("-- Disabled (Do Nothing) --", "jnt"));
        $order_statuses = array_merge($order_statuses, $order_statuses_raw);
        $this->form_fields = array(
            #region General =========================================================
            "enabled" => array(
                "title"   => __("Enable", "jnt"),
                "label"   => __("Enable this shipping.", "jnt"),
                "type"    => "checkbox",
                "default" => "yes"
            ),
            "title" => array(
                "title"             => __("Title", "jnt"),
                "type"              => "text",
                "default"           => "J&T Express",
                "custom_attributes" => array('readonly' => 'readonly'),
            ),
            "province" => array(
                "title"   => __( "Province", "jnt" ),
                "desc"    => __( 'J&T Express: The province in which your business is located.', 'jnt' ),
                "default" => "Makkah",
                "type"    => "text",
            ),
            #endregion =========================================================
            #region Authorization ===================================================
            "sep1" => ["type" => "title", "title" => "<hr>" . __("J&T Express Authorization", "jnt") . "<hr>"],
            "vipcode" => array(
                "title" => __("VIP Code", "jnt"),
                "type" => 'text',
                "description" => __("Go to J&T Express get your VIP Code.", "jnt"),
            ),
            "apikey" => array(
                "title" => __("API Key", "jnt"),
                "type" => "password",
                "description" => __("Provided by J&T Express", "jnt"),
            ),
            #endregion =========================================================
            #region Configuration ===================================================
            "sep2" => ["type" => "title", "title" => "<hr>" . __("J&T Express Configuration", "jnt") . "<hr>"],
            "name" => array(
                "title" => __("Sender Name", "jnt"),
                "type" => "text",
                "custom_attributes" => array("required" => "required"),
            ),
            "phone" => array(
                "title" => __("Sender Phone Number", "jnt"),
                "type" => "text",
                "custom_attributes" => array("required" => "required"),
            ),
            "service" => array(
                "title"   => __("Service Type", "jnt"),
                "type"    => 'select',
                "options" => ["1" => __("PICKUP", "jnt"), "6" => __("DROPOFF", "jnt")]
            ),
            "goods" => array(
                "title" => __("Goods Name", "jnt"),
                "type"  => 'checkbox',
                "label" => __("Tick this to show Goods Name in Consignment Note (more item).", 'jnt'),
            ),
            "orderid" => array(
                "title" => __("Order ID", "jnt"),
                "type"  => "checkbox",
                "label" => __("Tick this to show Order ID in Consignment Note (more item).", 'jnt'),
            ),
            #endregion =========================================================
            #region Sync via J&T Express ============================================
            "sep3" => ["type" => "title", "title" => "<hr>" . __("Sync Order via J&T Express", "jnt") . "<hr>"],
            "sync_type" => array(
                "title"       => __("Send Order to J&T Express (UAE)", "jnt"),
                "description" => __("Whether to Send WooCommerce Orders to J&T Express (UAE) automatically or manually", 'jnt'),
                "type"        => "select",
                "options"     => ["1" => __("Automatically", "jnt"), "0" => __("Manually", "jnt")]
            ),
            "sync_condition" => array(
                "title"       => __("First Condition", "jnt"),
                "description" => __("Select WooCommerce Order Statuses you want to automatically send Orders to J&T Express (UAE)", 'jnt'),
                "type"        => "multiselect",
                "class"       => "wc-enhanced-select",
                "default"     => array("wc-processing"),
                "options"     => $order_statuses_raw,
            ),
            "sync_shipping" => array(
                "title"       => __("Second Condition", "jnt"),
                "description" => __("Enter Shipping method title or a part of it, after Checking Order Status it would also check<br> if Shipping method title is matching your entered name then automatically send Orders to<br>J&T Express (UAE). You can Empty this field to disable Second Condition and only work with Order Status.", 'jnt'),
                "type"        => "text",
                "default"     => "",
            ),
            #endregion =========================================================
            #region Status Transition ===============================================
            "sep4" => ["type" => "title", "title" => "<hr>" . __("Status Transition", "jnt") . "<hr>"],
            "after_sync_status" => array(
                "title"       => __("Status after Sent to J&T Express", "jnt"),
                "description" => __("You can change Order Status When Order is sent to J&T Express (UAE)", 'jnt'),
                "type"        => "select",
                "class"       => "wc-enhanced-select",
                "default"     => "0",
                "options"     => $order_statuses,
            ),
            "after_deliver_status" => array(
                "title"       => __("Status after Delivered by J&T Express", "jnt"),
                "description" => __("You can change Order Status When Order is Delivered by J&T Express (UAE)", 'jnt'),
                "type"        => "select",
                "class"       => "wc-enhanced-select",
                "default"     => "wc-delivered",
                "options"     => $order_statuses,
            ),
            "cancel_jnt_order" => array(
                "title"       => __("Cancel Order from J&T Express", "jnt"),
                "label"       => __("Cancel Order from J&T Express on WC Order Cancellation", "jnt"),
                "description" => __("Cancel Order from J&T Express if WooCommerce Order status is equal to Canceled", "jnt"),
                "type"        => "checkbox",
                "default"     => "no",
            ),
            "disable_cancellation" => array(
                "title"       => __("Disable Cancellation", "jnt"),
                "label"       => __("Disable Picked-up WC Order Cancellation", "jnt"),
                "description" => __("Do NOT allow WooCommerce / Admin to Cancel an Order which delivery status is Picked-up or further", "jnt"),
                "type"        => "checkbox",
                "default"     => "no",
            ),
            #endregion =========================================================
            #region Missed Delivery Notification ====================================
            "sep5" => ["type" => "title", "title" => "<hr>" . __("Notification", "jnt") . "<hr>"],
            "notify_missed_delivery" => array(
                "title"       => __("Missed Delivery Email Notification", "jnt"),
                "label"       => __("Notify Admin via Email when Missed Delivery Status", "jnt"),
                "description" => __("If an Order is sent to J&T Express and specific days has passed and still Delivery state is not changed, notify Admin via Email to double-check the situation", "jnt"),
                "type"        => "checkbox",
                "default"     => "no",
            ),
            "notify_missed_delay" => array(
                "title"       => __("Missed Delivery Delay", "jnt"),
                "desc_tip"    => __("Enter delayed time in days,<br>e.g. Enter '7' for a week", "jnt"),
                "description" => __("How many days should be passed from date Order is sent to J&T Express to consider it missed if status has not changed yet", "jnt"),
                "type"        => "number",
                "default"     => "7",
            ),
            "notify_missed_subject" => array(
                "title"       => __("Email Subject", "jnt"),
                "description" => __("Notification Email's subject, you can use {order_id} as macro", "jnt"),
                "type"        => "text",
                "default"     => __("Missed Delivery", "jnt"),
            ),
            "notify_missed_receivers" => array(
                "title"       => __("Email Receivers", "jnt"),
                "description" => __("Email addresses separated by comma to receive notification", "jnt"),
                "type"        => "text",
                "default"     => get_bloginfo("admin_email"),
            ),
            "notify_missed_body" => array(
                "title"       => __("Email Content", "jnt"),
                "description" => __("HTML and Shortcodes are supported, you can use {order_id} as macro", "jnt"),
                "type"        => "textarea",
                "css"         => "width: 100%;",
                "default"     => "Dear admin, Order #{order_id} delivery status has not been changed in a while, please take required actions.",
            ),
            #endregion =========================================================
            #region Cash on Delivery ================================================
            "sep6" => ["type" => "title", "title" => "<hr>" . __("Cash on Delivery", "jnt") . "<hr>"],
            "cod_fee_type" => array(
                "title"       => __("Cash on Delivery Fee Type", "jnt"),
                "description" => __("Select an Option to add Custom Fee if Payment method is Cash-on-Delivery", 'jnt'),
                "type"        => "select",
                "options"     => ["0" => __("-- Disabled (Do Nothing) --", "jnt"), "fix" => __("Fixed Amount", "jnt"), "percentage" => __("Percentage of Order Total", "jnt")]
            ),
            "cod_fee_amount" => array(
                "title"       => __("Cash on Delivery Fee Amount", "jnt"),
                "desc_tip"    => __("Enter number only, <br>e.g. for 15% only enter 15", "jnt"),
                "description" => __("Enter Cash on Delivery Fee Amount based on previous option, Only enter Numeric value", "jnt"),
                "type"        => "number",
                "default"     => "",
            ),
            "cod_fee_label" => array(
                "title"       => __("Cash on Delivery Fee Label", "jnt"),
                "description" => __("Enter Cash on Delivery Fee Label which would be Added into Order Totals", "jnt"),
                "type"        => "text",
                "default"     => "Extra Delivery Fee",
            ),
            #endregion =========================================================
            #region Cronjob =========================================================
            "sep7" => ["type" => "title", "title" => "<hr>" . __("Cronjob", "jnt") . "</h3><hr>If you want to use <a href='https://www.hostgator.com/help/article/how-to-replace-wordpress-cron-with-a-real-cron-job' target='_blank'>C-Panel Cronjob</a>, you can use this command:&nbsp;<code>wget -q -O - " . home_url("/wp-cron.php?doing_wp_cron") . " >/dev/null 2>&1</code><hr>"],
            "cronjob_interval" => array(
                "title"       => __("Cronjob Interval (Minutes)", "jnt"),
                "desc_tip"    => __("Enter number only, <br>e.g. for 30 minutes only enter 30<br><strong>It is recomonded to NOT use number lower than 30</strong>", "jnt"),
                "description" => __("How often should orders delivery status be checked, set time in Minutes, Only enter Numeric value", "jnt"),
                "type"        => "number",
                "default"     => "30",
            ),
            "monitoring_statuses" => array(
                "title"       => __("Monitoring Statuses", "jnt"),
                "description" => __("Select WooCommerce Order Statuses you want to monitor while each cron runs<br>This list should not Include Canceled, Pending, Completed or Delivered Status because we DO NOT NEED to double-check those Orders", "jnt"),
                "type"        => "multiselect",
                "class"       => "wc-enhanced-select",
                "default"     => array("wc-processing"),
                "options"     => $order_statuses_raw,
            ),
            #endregion =========================================================
        );
    }
}
