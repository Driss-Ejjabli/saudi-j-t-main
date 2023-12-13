<?php
$setting = get_option('woocommerce_jnt_settings');
foreach ($ids as $id) {
    $order = wc_get_order($id);
    $weight_unit = get_option('woocommerce_weight_unit');
    $kg = 1000;
    $weight = 0;
    $item_name = array();
    $order_no = (!empty(get_post_meta($id, '_order_number', true))) ? get_post_meta($id, '_order_number', true) : $id;
    $receiver_phone = (!empty($order->get_shipping_phone())) ? $order->get_shipping_phone() : $order->get_billing_phone();

    if (sizeof($order->get_items()) > 0) {
        foreach ($order->get_items() as $item) {
            if ($item['product_id'] > 0) {
                $_product = $item->get_product();
                if (!$_product->is_virtual()) {
                    if (is_numeric($_product->get_weight()) && is_numeric($item['qty'])) {
                        $weight += ($_product->get_weight() * $item['qty']);
                    }
                    $item_name[] = $item['name'] . ' ' . $item['qty'];
                }
            }
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

    ?>
    <html>

    <head>
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
        <style type="text/css">
            body,
            html {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: "Roboto", sans-serif;
            }

            .container {
                height: 680px;
                width: 374px;
                border: 1px solid black;
                margin: 0 auto;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 0.8fr 1.2fr 1.2fr;
                grid-template-areas:
					"receiverCopy"
					"dispatcherCopy"
					"senderCopy";
            }

            #receiverCopy {
                grid-area: receiverCopy;
                border-bottom: 1px dotted black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 0.5fr 1fr 1fr 0.1fr;
                grid-template-areas:
					"receiverCopyRow1"
					"receiverCopyRow2"
					"receiverCopyRow3"
					"receiverCopyRow4";
            }

            #receiverCopyRow1 {
                grid-area: receiverCopyRow1;
                display: grid;
                grid-template-columns: 1fr 0.5fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"receiverRow1barcode receiverRow1AwbNo";
                border-bottom: 1px solid black;
            }

            #receiverRow1barcode {
                grid-area: receiverRow1barcode;
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
            }

            #receiverRow1AwbNo {
                grid-area: receiverRow1AwbNo;
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
            }

            #receiverRow1barcode img {
                max-width: 80%;
            }

            #receiverCopyRow2 {
                grid-area: receiverCopyRow2;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"receiverCopyRow2Postcode receiverCopyRow2Details";
                border-bottom: 1px solid black;
            }

            #receiverCopyRow2Postcode {
                grid-area: receiverCopyRow2Postcode;
                border-right: 1px solid black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 1fr;
                grid-template-areas:
					"PostcodeTo"
					"PostcodeNo";
                justify-items: center;
            }

            .PostcodeTo {
                grid-area: PostcodeTo;
                font-weight: bold;
            }

            .PostcodeNo {
                grid-area: PostcodeNo;
            }

            #receiverCopyRow2Details {
                grid-area: receiverCopyRow2Details;
                display: grid;
                grid-template-columns: 1fr 0.3fr;
                grid-template-rows: 0.5fr 1fr;
                grid-template-areas:
					"DetailsName DetailsPhone"
					"DetailsAddress DetailsPhone";
                font-size: 12px;
            }

            .DetailsName {
                grid-area: DetailsName;
                overflow: hidden;
            }

            .DetailsPhone {
                grid-area: DetailsPhone;
            }

            .DetailsAddress {
                grid-area: DetailsAddress;
                overflow: hidden;
            }

            #receiverCopyRow3 {
                grid-area: receiverCopyRow3;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"awbInfoPostcode awbInfoDetails";
                border-bottom: 1px solid black;
            }

            .awbInfoPostcode {
                grid-area: awbInfoPostcode;
                border-right: 1px solid black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 1fr;
                grid-template-areas:
					"PostcodeTo"
					"PostcodeNo";
                justify-items: center;
            }

            .awbInfoDetails {
                grid-area: awbInfoDetails;
                display: grid;
                grid-template-columns: 1fr 0.3fr;
                /*grid-template-rows: 0.5fr 1fr;*/
                grid-template-areas:
					"DetailsName DetailsPhone"
					"DetailsAddress DetailsPhone";
                font-size: 11px;
            }

            #receiverCopyRow4 {
                grid-area: receiverCopyRow4;
                display: grid;
                grid-template-columns: 0.25fr 1fr 0.25fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"receiverWeight receiverLabel receiverPaymentType";
                font-size: 9px;
                text-align: center;
            }

            #receiverWeight {
                grid-area: receiverWeight;
            }

            #receiverLabel {
                grid-area: receiverLabel;
                background: black;
                color: white;
            }

            #receiverPaymentType {
                grid-area: receiverPaymentType;
            }

            #dispatcherCopy {
                grid-area: dispatcherCopy;
                border-bottom: 1px dotted black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 0.5fr 1fr 0.6fr 0.7fr 0.1fr;
                grid-template-areas:
					"dispatcherCopyRouteCode"
					"dispatcherCopyBarcode"
					"dispatcherCopyDeliveryDetails"
					"dispatcherCopyRemarks"
					"dispatcherCopySign"
					"dispatcherCopyLabel";
            }

            #dispatcherCopyRouteCode {
                grid-area: dispatcherCopyRouteCode;
                border-bottom: 1px solid black;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 900;
                font-size: 40px;
            }

            #dispatcherCopyBarcode {
                grid-area: dispatcherCopyBarcode;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"pickupDate dispatcherSenderBarcode";
            }

            #pickupDate {
                grid-area: pickupDate;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                font-size: 9px;
                font-weight: 900;
            }

            .dispatcherSenderBarcode {
                grid-area: dispatcherSenderBarcode;
            }

            .dispatcherSenderBarcode img {
                max-width: 70%;
            }

            #dispatcherCopyDeliveryDetails {
                grid-area: dispatcherCopyDeliveryDetails;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"awbInfoPostcode awbInfoDetails";
            }

            #dispatcherCopyParcelInfo {
                grid-area: dispatcherCopyParcelInfo;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 1fr;
                grid-template-areas:
					"infoLabel"
					"infoDetails";
                font-size: 9px;
                padding-left: 10px;
            }

            #infoLabel {
                grid-area: infoLabel;
                font-weight: 900;
            }

            #infoDetails {
                grid-area: infoDetails;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #dispatcherCopyRemarks {
                grid-area: dispatcherCopyRemarks;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"remarksLabel remarksDetails";
                font-size: 9px;
                align-items: center;
            }

            #remarksLabel {
                grid-area: remarksLabel;
                padding-left: 10px;
            }

            #remarksDetails {
                grid-area: remarksDetails;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #dispatcherCopySign {
                grid-area: dispatcherCopySign;
                border-bottom: 1px dotted black;
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"signDisclaimer signSpace";
                padding-left: 10px;
                font-weight: 900;
            }

            #signDisclaimer {
                grid-area: signDisclaimer;
                border-right: 1px solid black;
                font-style: italic;
                font-size: 8px;
            }

            #signSpace {
                grid-area: signSpace;
                font-size: 11px;
            }

            #dispatcherCopyLabel {
                grid-area: dispatcherCopyLabel;
                display: grid;
                grid-template-columns: 0.53fr 0.25fr 0.25fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"distLabel icSpace distPaymentType";
                font-size: 9px;
            }

            #distLabel {
                grid-area: distLabel;
                background: black;
                color: white;
                text-align: center;
            }

            #icSpace {
                grid-area: icSpace;
                font-weight: 900;
                padding-left: 5px;
            }

            #distPaymentType {
                grid-area: distPaymentType;
                text-align: right;
            }

            #senderCopy {
                grid-area: senderCopy;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 0.7fr 0.3fr 1.5fr 0.1fr;
                grid-template-areas:
					"senderCopyBarcode"
					"senderDetailsTo"
					"senderDetailsFrom"
					"senderInfoDetails"
					"senderLabel";
            }

            #senderCopyBarcode {
                grid-area: senderCopyBarcode;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 1fr 0.2fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"SenderBarcode senderDetail";
            }

            #senderDetail {
                grid-area: senderDetail;
                display: flex;
                align-items: flex-end;
                border-left: 1px solid black;
                text-align: center;
                font-size: 10px;
                font-weight: 900;
                display: grid;
                grid-template-rows: 1fr 1fr 1fr;
                grid-template-areas:
					"date"
					"orderno"
					"weight";
            }

            #date {
                grid-area: date;
                border-bottom: 1px solid black;
            }

            #orderno {
                grid-area: orderno;
                border-bottom: 1px solid black;
            }

            #weight {
                grid-area: weight;
            }

            .SenderBarcode {
                grid-area: SenderBarcode;
                text-align: center;
                margin-left: 46px;
            }

            .SenderBarcode img {
                max-width: 82%;
            }

            #senderDetailsTo {
                grid-area: senderDetailsTo;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"awbInfoPostcode awbInfoDetails";
            }

            #senderDetailsFrom {
                grid-area: senderDetailsFrom;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 0.2fr 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"senderFromPostcode senderFromDetails";
            }

            .senderFromPostcode {
                grid-area: senderFromPostcode;
                border-right: 1px solid black;
                display: grid;
                grid-template-columns: 1fr;
                /*grid-template-rows: 1fr 1fr;*/
                grid-template-areas:
					"senderPostcodeFrom"
					"senderPostcodeNo";
                justify-items: center;
            }

            .senderPostcodeFrom {
                grid-area: senderPostcodeFrom;
                font-weight: bold;
            }

            .senderFromDetails {
                grid-area: senderFromDetails;
                display: grid;
                grid-template-columns: 1fr 0.3fr;
                /*grid-template-rows: 0.5fr 0.5fr;*/
                grid-template-areas:
					"DetailsName DetailsPhone"
					"DetailsAddress DetailsPhone";
                font-size: 11px;
            }

            #senderInfoDetails {
                grid-area: senderInfoDetails;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns:
					/*0.2fr*/
                        1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"senderInfoDetailsComplicated";
            }

            #senderInfoDetailsComplicated {
                grid-area: senderInfoDetailsComplicated;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"senderInfoDetailsComplicatedParcelInfo";
                font-size: 11px;
            }

            #senderInfoDetailsComplicatedParcelInfo {
                grid-area: senderInfoDetailsComplicatedParcelInfo;
                border-bottom: 1px solid black;
                display: grid;
                grid-template-columns: 1fr;
                grid-template-rows: 1fr;
                grid-template-areas:
					"parcelInfoDetailsSender";
                font-size: 10px;
            }

            #parcelInfoDetailsSender {
                grid-area: parcelInfoDetailsSender;
            }

            #remarksSenderLabel {
                grid-area: remarksSenderLabel;
            }

            #remarksSenderDetails {
                grid-area: remarksSenderDetails;
            }

            #senderLabel {
                grid-area: senderLabel;
                text-align: center;
                background: black;
                color: white;
                font-size: 9px;
            }

            div.container {
                page-break-before: always;
            }
        </style>
    </head>

    <body>
    <div class="container">
        <div id="receiverCopy">
            <div id="receiverCopyRow1" style="height: 39px;">
                <div id="receiverRow1barcode">
                    <?php $this->jnt_api->generate2(get_post_meta($id, 'jtawb', true)); ?>
                </div>
                <div id="receiverRow1AwbNo"><?= get_post_meta($id, 'jtawb', true) ?></div>
            </div>
            <div id="receiverCopyRow2">
                <div id="receiverCopyRow2Postcode">
                    <div class="PostcodeTo">TO</div>
                    <div class="PostcodeNo"><?= $order->get_shipping_postcode() ?></div>
                </div>
                <div id="receiverCopyRow2Details">
                    <div class="DetailsName"><?= $order->get_formatted_shipping_full_name() ?></div>
                    <div class="DetailsPhone"><?= $receiver_phone ?></div>
                    <div class="DetailsAddress" style="height: 39px;"><?= implode(" ", array(
                            $order->get_shipping_address_1(),
                            $order->get_shipping_address_2(),
                            $order->get_shipping_city(),
                            $order->get_shipping_postcode()
                        )) ?></div>
                </div>
            </div>
            <div id="receiverCopyRow3">
                <div class="awbInfoPostcode">
                    <div class="PostcodeTo">FROM</div>
                    <div class="PostcodeNo"><?= get_option('woocommerce_store_postcode') ?></div>
                </div>
                <div class="awbInfoDetails">
                    <div class="DetailsName"><?= $setting['name']; ?></div>
                    <div class="DetailsPhone"><?= $setting['phone'] ?></div>
                    <div class="DetailsAddress" style="height: 39px;"><?= implode(" ", array(
                            get_option('woocommerce_store_address'),
                            get_option('woocommerce_store_address_2'),
                            get_option('woocommerce_store_city'),
                            get_option('woocommerce_store_postcode')
                        )) ?></div>
                </div>
            </div>
            <div id="receiverCopyRow4">
                <div id="receiverWeight"><?= $weight ?> KG</div>
                <div id="receiverLabel">Receiver Copy</div>
                <div id="receiverPaymentType">MONTHLY</div>
            </div>
        </div>
        <div id="dispatcherCopy">
            <div id="dispatcherCopyRouteCode"><?= get_post_meta($id, 'jtcode', true) ?></div>
            <div id="dispatcherCopyBarcode">
                <div id="pickupDate"><?= date('Y-m-d') ?></div>
                <div class="dispatcherSenderBarcode">
                    <center>
                        <?php $this->jnt_api->generate(get_post_meta($id, 'jtawb', true)); ?>
                        <span class="font12 bold"><?= get_post_meta($id, 'jtawb', true) ?></span>
                    </center>
                </div>
            </div>
            <div id="dispatcherCopyDeliveryDetails">
                <div class="awbInfoPostcode">
                    <div class="PostcodeTo">TO</div>
                    <div class="PostcodeNo"><?= $order->get_shipping_postcode() ?></div>
                </div>
                <div class="awbInfoDetails">
                    <div class="DetailsName"><?= $order->get_formatted_shipping_full_name() ?></div>
                    <div class="DetailsPhone"><?= $receiver_phone ?></div>
                    <div class="DetailsAddress" style="height: 42px;"><?= implode(" ", array(
                            $order->get_shipping_address_1(),
                            $order->get_shipping_address_2(),
                            $order->get_shipping_city(),
                            $order->get_shipping_postcode()
                        )) ?></div>
                </div>
            </div>
            <div id="dispatcherCopyRemarks">
                <div id="remarksLabel">Parcel Info: </div>
                <div id="remarksDetails">
                    <?php
                    if ($setting['goods'] == 'yes') {
                        foreach ($item_name as $key => $value) {
                            echo $value . str_repeat('&nbsp;', 3);
                        }
                    } else {
                        echo 'N/A';
                    }
                    ?></div>
            </div>
            <div id="dispatcherCopySign">
                <div id="signDisclaimer">
                    By signing this package, receiver confirms all of the information of the customer and parcel are true, and understand and agree to all the rules and regulation of using J&T Express
                </div>
                <div id="signSpace">
                    Signature
                </div>
            </div>
            <div id="dispatcherCopyLabel">
                <div id="distLabel">Dispatcher Copy</div>
                <div id="icSpace">IC</div>
                <div id="distPaymentType">MONTHLY</div>
            </div>
        </div>
        <div id="senderCopy">
            <div id="senderCopyBarcode">
                <div class="SenderBarcode">
                    <!-- <center> -->
                    <?php $this->jnt_api->generate(get_post_meta($id, 'jtawb', true)); ?>
                    <span class="font12 bold"><?= get_post_meta($id, 'jtawb', true) ?></span>
                    <!-- </center> -->
                </div>
                <div id="senderDetail">
                    <div id="date"><?= date('Y-m-d') ?></div>
                    <div id="orderno"><?= ($setting['orderid'] == 'yes') ? $order_no : 'N/A' ?></div>
                    <div id="weight"><?= $weight ?> KG</div>
                </div>
            </div>
            <div id="senderDetailsTo">
                <div class="awbInfoPostcode">
                    <div class="PostcodeTo">TO</div>
                    <div class="PostcodeNo"><?= $order->get_shipping_postcode() ?></div>
                </div>
                <div class="awbInfoDetails">
                    <div class="DetailsName"><?= $order->get_formatted_shipping_full_name() ?></div>
                    <div class="DetailsPhone"><?= $receiver_phone ?></div>
                    <div class="DetailsAddress" style="height: 36px;"><?= implode(" ", array(
                            $order->get_shipping_address_1(),
                            $order->get_shipping_address_2(),
                            $order->get_shipping_city(),
                            $order->get_shipping_postcode()
                        )) ?></div>
                </div>
            </div>
            <div id="senderDetailsFrom">
                <div class="senderFromPostcode">
                    <div class="senderPostcodeFrom">From</div>
                    <div class="senderPostcodeNo"><?= get_option('woocommerce_store_postcode') ?></div>
                </div>
                <div class="senderFromDetails">
                    <div class="DetailsName"><?= $setting['name']; ?></div>
                    <div class="DetailsPhone"><?= $setting['phone']; ?></div>
                    <div class="DetailsAddress" style="height: 30px;"><?= implode(" ", array(
                            get_option('woocommerce_store_address'),
                            get_option('woocommerce_store_address_2'),
                            get_option('woocommerce_store_city'),
                            get_option('woocommerce_store_postcode')
                        )) ?></div>
                </div>
            </div>
            <div id="senderInfoDetails">
                <div id="senderInfoDetailsComplicated">
                    <div id="senderInfoDetailsComplicatedParcelInfo">
                        <div id="parcelInfoDetailsSender" style="height: 80px; margin-left: 3px">
                            <?php
                            if ($setting['goods'] == 'yes') {
                                foreach ($item_name as $key => $value) {
                                    echo $value . str_repeat('&nbsp;', 3);
                                }
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div id="senderLabel">Sender Copy</div>
        </div>
    </div>
    </body>

    </html>
<?php } ?>