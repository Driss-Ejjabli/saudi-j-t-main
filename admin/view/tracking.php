<!DOCTYPE html>
<html>

<head>
    <title></title>
    <style type="text/css">
        .body-item {
            display: flex;
            width: 100%;
            justify-content: start;
            max-width: 650px;
        }

        .body-middle-axis {
            display: flex;
            width: 1rem;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .body-left-date {
            display: flex;
            width: 30%;
            align-items: flex-end;
            flex-direction: column;
            justify-content: center;
            padding: 10px;
        }

        .online-top-closing {
            width: 1px;
            height: 3rem;
            background: #999
        }

        .dot-closing {
            width: .6rem;
            height: .6rem;
            border-radius: 50%;
            background: #fe4f33;
        }

        .online-bottom {
            width: 1px;
            height: 3rem;
            background: #999;
        }

        .body-right {
            display: flex;
            flex-grow: 1;
            flex-direction: column;
            justify-content: center;
            padding: 10px;
        }
    </style>
</head>

<body>
    <h1 class="wp-heading-inline">Tracking &nbsp;&nbsp;&nbsp;&nbsp;<a class="button-secondary" href="<?=admin_url("admin.php?page=wc-settings&tab=shipping&section=jnt");?>">J&T Express Setting</a></h1>
    <hr class="wp-header-end">
    <form method="GET">
        <div class="form-wrap">
            <input type="hidden" name="page" value="jnt_main_page">
            <input type="text" name="tracking" size="30" value="<?=$_GET["tracking"]??"";?>" placeholder="Input Waybill Number" style="width: 60%">
            <button class="button-primary" style="width: 15%">Track</button>
        </div>
    </form>

    <?php

    if (isset($res) && is_array($res)) {

        if ($res['code'] == 1) {
            $value = $res['data'];
            ?>
            <div>
                <div style="width: 100%; display: flex;">
                    <div style=" display: flex;justify-content: space-around;flex-direction: column;align-items: flex-start;">
                        <h3>Waybill Number : <?= $value[0]['billCode'] ?></h3>
                        <div class='head-right-middle'>Status : <?= $value[0]['status'] ?></div>
                        <div class='head-right-bottom'>Dispatcher Contact : <?= $value[0]['staffContact'] ?></div>
                    </div>
                </div>
                <hr />
                <div class='body-container'>
                    <?php if (!empty($value)) { ?>
                        <?php foreach ($value as $key => $data) { ?>
                            <?php
                            // echo "<pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>" . print_r($data, 1) . "</pre>";
                            ?>
                            <div class="body-item">
                                <div class='body-left-date'>
                                    <div><?= date('Y-m-d H:i', strtotime($data['scanTime'])) ?></div>
                                </div>

                                <div class='body-middle-axis'>
                                    <div class='online-top-closing'></div>
                                    <div class='dot-closing'></div>
                                    <div class='online-bottom'></div>
                                </div>

                                <div class='body-right'>
                                    <div class='body-statusing'><b><?= $data['scanTypeName'] ?></b></div>
                                    <div class='body-status-address'><?= $data['status'] ?></div>
                                    <div class='body-status-address'>City : <?= $data['scanNetworkCity'] ?></div>
                                    <div class='body-status-address'><?= $data['remark1'] ?></div>
                                    <?php 
                                        if (isset($_GET["debug"])) {
                                            ?>
                                            <div class='body-status-extras'><?= "<pre style='text-align: left; direction: ltr; border:1px solid gray; padding: 1rem; overflow: auto;'>". print_r($data,1) ."</pre>"; ?></div>
                                            <?php
                                        }
                                     ?>
                                </div>
                            </div>

                        <?php } ?>
                    <?php } else { ?>
                        <div class='body-container'>
                            <h3 class="msg">Sorry, information not found</h3>
                            <h3 class="msg"> please check again later!!!</h3>
                        </div>
                    <?php } ?>
                </div>
                <hr />
            </div>

        <?php
        }
        else{
            ?>
            <div style="width: 100%; display: flex;">
                <div style=" display: flex;justify-content: space-around;flex-direction: column;align-items: flex-start;">
                    <h3>Waybill Number : </h3>
                </div>
            </div>
            <hr />
            <div class='body-container'>
                <h3 class="msg">Sorry, information not found</h3>
                <h3 class="msg"> please check again later!!!</h3>
            </div>
            <?php
        }
    }
    else{
        ?>
            <center>
                <h3><?= isset($res) ? $res : "" ?></h3>
            </center>
        <?php
    }
    ?>
</body>
</html>