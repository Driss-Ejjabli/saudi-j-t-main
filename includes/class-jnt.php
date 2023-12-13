<?php

class Jnt{

    private static $initiated;

    public static function init() {
        if (!isset(self::$initiated))
        {
            self::$initiated = new self();
        }
        return self::$initiated;
    }

    public function InitPlugin() {

        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-admin.php';
        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-setting.php';
        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-order.php';
        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-consignment-note.php';
        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-thermal.php';

        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-thermal-new.php';

        require_once JNT_PLUGIN_DIR . 'admin/class-jnt-cancel-order.php';
        require_once JNT_PLUGIN_DIR . 'includes/class-jnt-helper.php';
        require_once JNT_PLUGIN_DIR . 'includes/class-jnt-api.php';


        new Jnt_Admin();
        new Jnt_Settings();
        new Jnt_Shipment_Order();
        new Jnt_Consignment_Note();
        new Jnt_Thermal();

        new Jnt_Thermal_New();
        new JNT_Cancel();
        new Jnt_Helper();
        new Jnt_Api();

    }

}