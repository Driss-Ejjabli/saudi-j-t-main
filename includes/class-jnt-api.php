<?php

 global $wpdb;
 
class Jnt_Api {

    protected $basePath = "https://woocommerce.jtjms-sa.com/woocommerce/";
    protected $key = "AKe62df84bJ3d8e4b1hea2R45j11klsb";
    public function shippingQuote($shipment_info) {
        $url = $this->basePath.'api/shipping/quote';

        $data = array(
            'password'          => $shipment_info['password'],
            'cuscode'           => $shipment_info['cuscode'],
            'weight'            => $shipment_info['weight'],
            'shipper_city'      => $shipment_info['shipper_city'],
            'shipper_province'  => $shipment_info['shipper_province'],
            'receiver_city'     => $shipment_info['receiver_city'],
            'receiver_province' => $shipment_info['receiver_province'],
        );

        $json_data = json_encode($data);
        $signature = hash("sha256", ($json_data . $this->key));
        $post = array(
            'data_param' => $json_data,
            'data_sign'  => $signature
        );

        return self::curl($post,  $url);
    }
    
     
    
    public function order($shipment_info){
        $sign = "AKe62df84bJ3d8e4b1hea2R45j11klsb";
        $res = array();
        foreach ($shipment_info as $value) {
            $data = [ "detail" => array( array(
                        "username"          => "WORDPRESS",
                        "api_key"           => "WORD12",
                        // "origin_code"       => "JKT",
                        // "destination_code"  => "JKT",
                        // "receiver_area"     => "JKT001",
                        "password"          => $value["password"],
                        "cuscode"           => $value["cuscode"],
                        "orderid"           => $value["orderid"],
                        "shipper_name"      => $value["sender_name"],
                        "shipper_addr"      => $value["sender_addr"],
                        "shipper_city"      => $value["sender_city"],
                        "shipper_province"  => $value["sender_province"],
                        "shipper_contact"   => $value["sender_name"],
                        "shipper_phone"     => $value["sender_phone"],
                        "sender_zip"        => $value["sender_zip"],
                        "receiver_name"     => $value["receiver_name"],
                        "receiver_addr"     => $value["receiver_addr"],
                        "receiver_city"     => $value["receiver_city"],
                        "receiver_province" => $value["receiver_province"],
                        "receiver_phone"    => $value["receiver_phone"],
                        "receiver_zip"      => $value["receiver_zip"],
                        "qty"               => $value["qty"],
                        "weight"            => $value["weight"],
                        "payType"           => $value["payType"],
                        "goodsType"         => $value["goodsType"],
                        "servicetype"       => $value["servicetype"],
                        "expresstype"       => $value["expresstype"],
                        "total"             => $value["total"],
                        "currency"          => $value["currency"],
                        "payment_method"    => $value["payment_method"],
                        "item_name"         => mb_substr($value["item"], 0, 50, 'UTF-8'),
                        "goodsdesc"         => mb_substr($value["goodsdesc"], 0, 40, 'UTF-8'),   
            ) ) ];
            $json_data = json_encode($data);
            $signature = hash("sha256", ($json_data . $sign));
            $post = array(
                'data_param' => $json_data,
                'data_sign'	=> $signature,
            );
            $curl = self::curl($post, $this->basePath."api/order/add");
            $res[] = array(
                "id"        => $value["id"],
                "json_data" => $json_data,
                "data"      => "<pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>". print_r($data,1) ."</pre>",
                "detail"    => $curl
            );
            try {
                //Hide Errors and Warnings on This page
                    //error_reporting(0);
                
                $result = json_decode($curl);
                if (empty($result->success) || $result->success != "1") {
                    
                    $order = wc_get_order( $value['id'] );
                    $order->add_order_note("<strong>J&T Express Error Sync with Server:</strong><pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>". print_r($result,1) ."</pre>", false, true);
               
//started new code     
    
                    // Save info not added in I&T Express
                        $rootDirectory = $_SERVER['DOCUMENT_ROOT']; 
                        require($rootDirectory . "/wp-load.php");
                        global $wpdb; 
                        
                        $table_name = $wpdb->prefix."orders_no_added_jt_express";
                        $sql = $wpdb->prepare("INSERT INTO ".$table_name." (data_order_json, id_shipment_info) VALUES ( %s, %d )", $json_data, $value["id"]);
                        $wpdb->query($sql);

                    // Send alert mail Content info not added in I&T Express
                        $admin_email = get_option('admin_email');
                        $to = $admin_email;
                        $subject = 'Alert for order not added in J&T Express';
                        $body = 'Error :'."<pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>".  print_r($result,1)  ."</pre>".'<br />Info Order :<br /><pre>'.$json_data.'</pre>';
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        
                        wp_mail( $to, $subject, $body, $headers );
                    
                    
//end new code 
               
                }else
                {
                   
                  
                    /*
                        $admin_email = get_option('admin_email');
                        $to = $admin_email;
                        $subject = 'Alert for order added in J&T Express';
                        $body = 'Success :'."<pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>".  print_r($result,1)  ."</pre>".'<br />Info Order :<br /><pre>'.$json_data.'</pre>';
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        
                        wp_mail( $to, $subject, $body, $headers );
                    */

                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        return $res;
    }
    public function tracking($awb){
        $key       = "AKe62df84bJ3d8e4b1hea2R45j11klsb";
        $data      = ["billCode" => $awb];
        $json_data = json_encode($data);
        $signature = hash("sha256", ($json_data . $key));
        $post      = ["data_param" => $json_data, "data_sign" => $signature];
        return self::curl($post, $this->basePath.'api/logistics/trace');
    }
    public function cancel($awbs) {

        $url = $this->basePath.'api/order/cancel';

        $key = 'AKe62df84bJ3d8e4b1hea2R45j11klsb';

        $res = array();
        $shipping = get_option('woocommerce_jnt_settings');

        foreach ($awbs as $value) {
            $data = array(
                'username' => 'WORDPRESS',
                'api_key'  => 'WORD12',
                'awb_no'   => $value['awb'],
                'orderid'  => '',
                'remark'   => '',
                'password' => $shipping['apikey'],
                'cuscode'  => $shipping['vipcode']
            );

            $json_data = json_encode($data);
            $signature = hash("sha256", ($json_data . $key));
            $post = array(
                'data_param' => $json_data,
                'data_sign'  => $signature
            );

            $res[] = array('id'=>$value['id'], 'detail'=>self::curl($post, $url));
        }

        return $res;
    }
    public static function curl($post, $url) {
        $r = wp_remote_post($url, array(
            "sslverify"   => false,
            "method"      => "POST",
            "httpversion" => "1.1",
            "body"        => $post
        ));
        return wp_remote_retrieve_body($r);
    }
    public function printA4($cuscode="", $awbs="", $return=false) {
        $url = $this->basePath.'api/order/print';

        $logistics_interface	= array(
            'account'      => 'WORDPRESS',
            'password'     => 'WORD12',
            'customercode' => $cuscode,
            'billcode'     => $awbs,
        );

        $post = array('logistics_interface' => json_encode($logistics_interface), 'data_digest' => '123', 'msg_type' => '1');

        $result = wp_remote_post($url, array('body' => $post));
        $body = wp_remote_retrieve_body($result);
        if ($this->str_contains($body, '{"code"')) {
            $data = json_decode($body);
          //  wp_die("<h2>Print [$awbs] Error</h2>$data->msg", "Print Error", ["back_link"=>true]);
        }
        if (true === $return) return $body;
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=\"print-document.pdf\"");
        print_r($body);
    }
    public function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
    public function print($cuscode="", $awbs="", $return=false) {
        $url = $this->basePath.'api/order/print';

        $logistics_interface = array(
            'account'      => 'WORDPRESS',
            'password'     => 'WORD12',
            'customercode' => $cuscode,
            'billcode'     => $awbs,
        );

        $post = array('logistics_interface' => json_encode($logistics_interface), 'data_digest' => '123', 'msg_type' => '1');

        $result = wp_remote_post($url, array('body' => $post));
        $body = wp_remote_retrieve_body($result);
        if ($this->str_contains($body, '{"code"')) {
            $data = json_decode($body);
           // wp_die("<h2>Print [$awbs] Error</h2>$data->msg", "Print Error", ["back_link"=>true]);
        }
        if (true === $return)return $body;
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=\"print-document.pdf\"");
        print_r($body);
    }
    public function generate($value){
        echo '<img alt="testing" src="https://pmp.jtexpress.my/wordpresslib/barcode.php?text='.trim($value).'&size=55&sizefactor=2" />';
    }
    public function generate2($value){
        echo '<img alt="testing" src="https://pmp.jtexpress.my/wordpresslib/barcode.php?text='.trim($value).'&size=25&sizefactor=1" />';

    }

}
