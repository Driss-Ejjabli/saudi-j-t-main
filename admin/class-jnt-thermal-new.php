<?php

class Jnt_Thermal_New {

    public $jnt_helper = null;

    public function __construct() {

        $this->jnt_helper = new Jnt_Helper();
        $this->define_hooks();

    }

    /**
     * Define hooks
     */
    protected function define_hooks() {

        add_filter( 'bulk_actions-edit-shop_order', [ $this, 'bulk_actions_consignment_note_thermal_new' ], 33 );
        add_filter( 'handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_consignment_note_thermal_new'], 10, 3 );
        add_filter( 'handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_jnt_local_pdf_a4'], 10, 3 );
        add_filter( 'handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_jnt_local_pdf_thermal'], 10, 3 );

    }

    public function bulk_actions_consignment_note_thermal_new ( $actions ) {

        $actions['jnt_consignment_note_thermal_new'] = __( 'Print J&T Consignment Note (more item)', "jnt" );
        $actions['jnt_local_pdf_a4'] = __( 'Print Local PDF A4', "jnt" );
        $actions['jnt_local_pdf_thermal'] = __( 'Print Local PDF Thermal', "jnt" );

        return $actions;
    }

    public function handle_bulk_action_jnt_local_pdf_a4 ( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'jnt_local_pdf_a4' ) { return $redirect_to; }
        $processed_ids = array();
        foreach ( $post_ids as $post_id ) {
            $url = get_post_meta( $post_id, '_jnt_pdf_a4', true );
            if (!$url || empty($url) ) {
                $empty_awb[] = $post_id;
            }else{
                $processed_ids[$post_id] = "<a href='$url' download='{$post_id}_pdf_a4.pdf' target='_self'>Download PDF Order #$post_id</a>";
            }
        }
        if (!empty( $processed_ids ) ) {
            wp_die("Click on each Item to download or Select all Links and Use IDM to download all at once. <br>".implode("<br>", $processed_ids), "Success");
        } else {
            wp_die("Empty List. Please check Orders to Have Created PDFs first.", "Error");
        }
    }
    public function handle_bulk_action_jnt_local_pdf_thermal ( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'jnt_local_pdf_thermal' ) { return $redirect_to; }
        $processed_ids = array();
        foreach ( $post_ids as $post_id ) {
            $url = get_post_meta( $post_id, '_jnt_pdf_thermal', true );
            if (!$url || empty($url) ) {
                $empty_awb[] = $post_id;
            }else{
                $processed_ids[$post_id] = "<a href='$url' download='{$post_id}_pdf_thermal.pdf' target='_self'>Download PDF Order #$post_id</a>";
            }
        }
        if (!empty( $processed_ids ) ) {
            wp_die("Click on each Item to download or Select all Links and Use IDM to download all at once. <br>".implode("<br>", $processed_ids), "Success");
        } else {
            wp_die("Empty List. Please check Orders to Have Created PDFs first.", "Error");
        }
    }
    public function handle_bulk_action_consignment_note_thermal_new ( $redirect_to, $action, $post_ids ) {

        if ( $action !== 'jnt_consignment_note_thermal_new' ) {
            return $redirect_to;
        }

        $processed_ids = array();
        $empty_awb = array();

        foreach ( $post_ids as $post_id ) {
            if ( ! get_post_meta( $post_id, 'jtawb', true ) ) {
                $empty_awb[] = $post_id;
            }else{
                $processed_ids[] = $post_id;
            }
        }

        if ( ! empty( $processed_ids ) ) {
            $result = $this->jnt_helper->process_print_thermal_new($processed_ids);

        } else {

            $redirect_to = add_query_arg( array(
                'acti' => 'error',
                'msg' => 'Not yet Order',
            ), $redirect_to );

            return $redirect_to;
        }

    }

}