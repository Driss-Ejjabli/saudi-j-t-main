<?php

class Jnt_Thermal {

    public $jnt_helper = null;

    public function __construct() {

        $this->jnt_helper = new Jnt_Helper();
        $this->define_hooks();

    }

    /**
     * Define hooks
     */
    protected function define_hooks() {

        add_filter( 'bulk_actions-edit-shop_order', [ $this, 'bulk_actions_consignment_note_thermal' ], 33 );
        add_filter( 'handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_consignment_note_thermal'], 10, 3 );

    }

    public function bulk_actions_consignment_note_thermal ( $actions ) {

        $actions['jnt_consignment_note_thermal'] = __( 'Print J&T Consignment Note (Thermal)', "jnt" );

        return $actions;
    }

    public function handle_bulk_action_consignment_note_thermal ( $redirect_to, $action, $post_ids ) {

        if ( $action !== 'jnt_consignment_note_thermal' ) {
            return $redirect_to;
        }

        $processed_ids = array();
        $empty_awb = array();

        foreach ( $post_ids as $post_id ) {
            if ( ! get_post_meta( $post_id, 'jtawb', true ) ) {
                $empty_awb[] = $post_id;
            }else{
                $processed_ids[] = get_post_meta( $post_id, 'jtawb', true );
            }
        }

        if ( ! empty( $processed_ids ) ) {
            $result = $this->jnt_helper->process_print_thermal($processed_ids);

        }else{

            $redirect_to = add_query_arg( array(
                'acti' => 'error',
                'msg' => 'Not yet Order',
            ), $redirect_to );

            return $redirect_to;
        }

    }

}