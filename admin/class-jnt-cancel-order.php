<?php

class Jnt_Cancel {

    public $jnt_helper = null;

    public function __construct() {

        $this->jnt_helper = new Jnt_Helper();
        $this->define_hooks();

    }

    protected function define_hooks() {

        add_filter( 'bulk_actions-edit-shop_order', [ $this, 'bulk_actions_cancel_order' ], 30 );
        add_filter( 'handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_cancel_order'], 10, 3 );

    }

    public function bulk_actions_cancel_order ( $actions ) {
        $actions['jnt_cancel_order'] = __( 'Cancel Order from J&T Express (UAE)', "jnt" );
        return $actions;
    }

    public function handle_bulk_action_cancel_order ( $redirect_to, $action, $post_ids ) {
        if ( $action !== 'jnt_cancel_order' ) {
            return $redirect_to;
        }

        $processed_ids = array();
        $reasons = array();

        foreach ( $post_ids as $post_id ) {
            if ( ! get_post_meta( $post_id, 'jtawb', true ) ) {
            }else{
                $processed_ids[] = $post_id;
            }
        }

//        $processed_ids = array('11','22');

        if ( ! empty( $processed_ids ) ) {
            $result = $this->jnt_helper->cancel_order($processed_ids);

            foreach ($result as $details) {

                $id = $details['id'];
                $detail = json_decode($details['detail'], true);
                if (isset($detail["status"]) && $detail["status"] == "Error") {
                    $temp_order = wc_get_order($id);
                    if ($temp_order) $temp_order->add_order_note("<h2>Order #$id Cancellation Error</h2>{$detail["details"][0]["reason"]}");
                    wp_die("<h2>Order #$id Cancellation Error</h2>{$detail["details"][0]["reason"]}", "Order #$id Cancellation Error", ["back_link"=>true]);
                }
                if (isset($detail["msg"]) && "success" != $detail["msg"] && !empty(trim($detail["msg"]))) {
                    $temp_order = wc_get_order($id);
                    if ($temp_order) $temp_order->add_order_note("<h2>Order #$id Cancellation Error</h2>{$detail["msg"]}");
                    wp_die("<h2>Order #$id Cancellation Error</h2>{$detail["msg"]}", "Cancellation Error", ["back_link"=>true]);
                }
                $status = "";
                foreach ($detail["details"] as $d) {
                    $awb_no = $d[0]['awb_no'];
                    $status = $d[0]['status'];
                    $reason = $d[0]['reason'];
                }
                if($status == 'success'){
                    if ( ! get_post_meta( $id, 'cancel', true ) ) {
                        $cancelled = array();
                        array_push($cancelled, $awb_no);
                        update_post_meta($id, 'cancel', $cancelled);
                    }else{
                        $cancelled = get_post_meta($id, 'cancel', true);
                        array_push($cancelled, $awb_no);
                        update_post_meta($id, 'cancel', $cancelled);
                    }
                    delete_post_meta($id, 'jtawb');
                    delete_post_meta($id, 'jtorder');
                    delete_post_meta($id, 'jtcode');

                }else{
                    array_push($reasons, array('id' => $id, 'reason' => $reason));
                }
            }

            $redirect_to = add_query_arg( array(
                'acti' => 'cancel',
                'status' => $status,
                'reasons' => $reasons,
                'id' => $id
            ), $redirect_to );

            return $redirect_to;

        }else{
            $redirect_to = add_query_arg( array(
                'acti' => 'error',
                'msg' => 'Not yet Order',
            ), $redirect_to );

            return $redirect_to;
        }
    }

}