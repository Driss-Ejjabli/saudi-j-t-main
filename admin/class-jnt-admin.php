<?php

class Jnt_Admin {

    public function __construct() {

        $this->jnt_helper = new Jnt_Helper();
        $this->define_hooks();

    }

    public function define_hooks() {

        add_action( 'plugins_loaded', [ $this, 'check_woocommerce_activated' ] );
        add_action( 'admin_menu', [ $this, 'add_menu' ], PHP_INT_MAX, 1 );

    }

    /**
     * Check if Woocommerce installed
     */
    public function check_woocommerce_activated() {
        if ( defined( 'WC_VERSION' ) ) {
            return;
        }

        add_action( 'admin_notices', [ $this, 'notice_woocommerce_required' ] );
    }

    /**
     * Admin error notifying user that Woocommerce is required
     */
    public function notice_woocommerce_required() {
        ?>
        <div class="notice notice-error">
            <p><?= 'Jnt requires WooCommerce to be installed and activated!' ?></p>
        </div>
        <?php
    }

    /**
     * Add menu
     */
    public function add_menu() {
        $icon = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMTIwMC4wMDAwMDBwdCIgaGVpZ2h0PSI0MDAuMDAwMDAwcHQiICB2aWV3Qm94PSIwIDAgMTIwMC4wMDAwMDAgNDAwLjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgZmlsbD0iI2E3YWFhZCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsNDAwLjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTI4OTggMzc2MiBjLTcxIC0zMTIgLTQzMCAtMTkwNCAtNTI2IC0yMzI3IGwtMTE4IC01MjAgLTc4NCAtNSAtNzg1Ci01IC0xMTUgLTIyNSBjLTY0IC0xMjQgLTE0NyAtMjgzIC0xODUgLTM1NSAtMzkgLTcyIC03MSAtMTM2IC03MyAtMTQyIC0zIC0xMAoyODQgLTEzIDE0MTEgLTEzIGwxNDE1IDAgMjEgODggYzMxIDEzMyA0OTAgMjE1MCA2NjAgMjkwMCA4MyAzNjUgMTUxIDY2OCAxNTEKNjczIDAgNSAtMjE4IDkgLTUyNyA5IGwtNTI4IDAgLTE3IC03OHoiLz4KPHBhdGggZD0iTTQ0NjAgMzU5NyBjLTUxIC0yMjYgLTU1IC0yNTAgLTU0IC0zNjcgMCAtMTQwIDE5IC0yNDYgNjYgLTM4MCA1NQotMTU2IDE1MiAtMzI2IDI1NCAtNDQ3IDI0IC0yOCA0NCAtNTUgNDMgLTYwIDAgLTQgLTM3IC0zNCAtODIgLTY1IC0zMzEgLTIzMQotNjE2IC01NjcgLTcxNyAtODQ1IC04NyAtMjQyIC03NiAtNDYxIDM1IC02ODggOTQgLTE5MCAxODMgLTI3NyA0MDAgLTM4NSAyOTAKLTE0NCA2MDkgLTIwOSA5NzQgLTE5NyAzMjEgMTEgNjIwIDczIDkwNSAxODkgbDk5IDQxIDM1IC0zOSBjMTkgLTIxIDY2IC03MgoxMDUgLTExMSBsNjkgLTczIDQ3NCAwIGMyNjEgMCA0NzQgMiA0NzQgNSAwIDQgLTU2NSA2OTggLTYxMCA3NTAgLTE3IDE5IC0xNgoyNCA1NiAxNDUgMTM3IDIzMSAyNDEgNDk1IDI4OSA3MzYgMTQgNjcgMjUgMTMyIDI1IDE0NSAwIDIzIC0xNyAyOSAtMzUxIDEyNAotMTkyIDU1IC0zNTUgMTAxIC0zNjMgMTAzIC04IDIgLTE2IC0yMCAtMjUgLTY1IC0zNiAtMjAxIC05OCAtNDA4IC0xNDEgLTQ3NgpsLTMxIC00OSAtNDcgMzggYy0yNTQgMjAzIC01NjEgNDkzIC03MDQgNjY0IC05NyAxMTUgLTE5OCAyNjUgLTIzMyAzNDUgLTQxCjkxIC01MiAxOTMgLTMyIDI5MCA1MyAyNTYgMjYwIDQyNiA1MjEgNDI1IDE4MCAtMSAzNDggLTgwIDQwOSAtMTk0IDIwIC0zNyAyMgotNTQgMjAgLTE3NSAtMiAtNzUgMSAtMTMxIDYgLTEyNSAxNSAxNiA1MzEgOTYyIDUzMSA5NzMgMCA4IC0zMzAgMTEgLTExNzIgMTEKbC0xMTczIDAgLTU1IC0yNDN6IG0xMTQ5IC0yMjI3IGMxODIgLTIxNyAzMzAgLTM5OSAzMjggLTQwNSAtNiAtMTcgLTE2NyAtOTMKLTI2MiAtMTI0IC0xMzcgLTQ0IC0yMDAgLTU1IC0zMjAgLTU1IC0xNzcgMCAtMjk5IDQ4IC00MTYgMTY0IC04MSA4MSAtMTE5CjE1NSAtMTI2IDI0NCAtMTQgMTYzIDk0IDMxNCAzNTYgNTAxIDU4IDQxIDEwNiA3NCAxMDcgNzIgMSAtMSAxNTEgLTE4MCAzMzMKLTM5N3oiLz4KPHBhdGggZD0iTTczMzcgMzcwMyBjLTQxIC03NiAtMTYzIC0zMDAgLTI3MSAtNDk4IGwtMTk2IC0zNjAgODAwIC0zIGM3MjggLTIKODAwIC00IDgwMCAtMTggMCAtOSAtMTI4IC01MjggLTI4NSAtMTE1MyAtMjU2IC0xMDIxIC0zNjUgLTE0NjggLTM2NSAtMTQ5MiAwCi01IDIzMSAtOSA1MTQgLTkgbDUxNCAwIDMzMiAxMzM1IDMzMiAxMzM1IDU5MiAwIDU5MSAwIDE0NSAxNDUgMTQ1IDE0NSAtNTQwCjAgLTU0MCAwIDY2IDY2IDY2IDY1IDUzNyAtMSA1MzggLTEgMTAyIDEwMSAxMDEgMTAwIC01MjcgMCBjLTI5MSAwIC01MjggNAotNTI4IDggMCA0IDI4IDM2IDYzIDcwIGw2MyA2MiA1MzQgMCA1MzUgMCAxMjAgMTIwIDEyMCAxMjAgLTIxNDEgMCAtMjE0MSAwCi03NiAtMTM3eiIvPgo8L2c+Cjwvc3ZnPgo=";
        add_menu_page( 'J&T ARAB', 'J&T ARAB', 'manage_options', 'jnt_main_page', array($this, 'parcel_admin_page'), $icon, '56' );
        add_submenu_page("woocommerce", 'J&T Express', 'J&T Express', 'manage_options', "admin.php?page=wc-settings&tab=shipping&section=jnt");
    }

    public function parcel_admin_page(){
        if (isset($_GET["page"], $_GET["tracking"]) && $_GET["page"] == "jnt_main_page" && !empty($_GET['tracking']) ) {
            $awb = $_GET['tracking'];
            $res = $this->jnt_helper->tracking ( $awb );
            try {
                $res = json_decode($res, true);
            } catch (\Throwable $th) {
                
            }
        }
        include 'view/tracking.php';
    }

}