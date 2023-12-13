/*
 * @Author: Amirhossein Hosseinpour <https://amirhp.com>
 * @Date Created: 2023/10/11 18:30:07
 * @Last modified by: amirhp-com <its@amirhp.com>
 * @Last modified time: 2023/11/20 20:39:36
 */

function runCode() { document.querySelector("li.current").classList.remove("current"); var anchor = document.querySelector("a[href*='admin.php?page=wc-settings&tab=shipping&section=jnt']"); if (anchor) { anchor.parentElement.classList.add("current"); } } setTimeout(runCode, 10); document.addEventListener("DOMContentLoaded", function () { setTimeout(runCode, 10); setTimeout(runCode, 100); setTimeout(runCode, 300); setTimeout(runCode, 700); setTimeout(runCode, 1000); setTimeout(runCode, 1500); setTimeout(runCode, 2000); });

(function ($) {
    $(document).ready(function () {
        var $success_color = "rgba(21, 139, 2, 0.8)";
        var $error_color   = "rgba(139, 2, 2, 0.8)";
        var $info_color    = "rgba(2, 133, 139, 0.8)";
        if (!$("toast").length) {$(document.body).append($("<toast>"));}

        show_toast("DEV: jquery loaded for jnt setting! test passed.", $success_color);

        setTimeout(function () { $("#woocommerce_jnt_sync_type").trigger("refresh"); }, 150);
        setTimeout(function () { $("#woocommerce_jnt_notify_missed_delivery").trigger("refresh"); }, 150);

        $(document).on("change refresh", "#woocommerce_jnt_sync_type", function(e){
            e.preventDefault();
            var me = $(this);
            if (me.val() == "0") {
                $("#woocommerce_jnt_sync_condition").parents("tr").hide();
                $("#woocommerce_jnt_sync_shipping").parents("tr").hide();
            }else{
                $("#woocommerce_jnt_sync_condition").parents("tr").show();
                $("#woocommerce_jnt_sync_shipping").parents("tr").show();
            }
        });
        $(document).on("change refresh", "#woocommerce_jnt_notify_missed_delivery", function(e){
            e.preventDefault();
            var me = $(this);
            if (!me.prop("checked")) {
                $("#woocommerce_jnt_notify_missed_delay").parents("tr").hide();
                $("#woocommerce_jnt_notify_missed_subject").parents("tr").hide();
                $("#woocommerce_jnt_notify_missed_receivers").parents("tr").hide();
                $("#woocommerce_jnt_missed_body").parents("tr").hide();
            }else{
                $("#woocommerce_jnt_notify_missed_delay").parents("tr").show();
                $("#woocommerce_jnt_notify_missed_subject").parents("tr").show();
                $("#woocommerce_jnt_notify_missed_receivers").parents("tr").show();
                $("#woocommerce_jnt_missed_body").parents("tr").show();
            }
        });

        function show_toast(data = "Sample Toast!", bg="", delay = 4500) {
        if (!$("toast").length) {$(document.body).append($("<toast>"));}else{$("toast").removeClass("active");}
        setTimeout(function () {
            $("toast").css("--toast-bg", bg).html(data).stop().addClass("active").delay(delay).queue(function () {
            $(this).removeClass("active").dequeue().off("click tap");
            }).on("click tap", function (e) {e.preventDefault(); $(this).stop().removeClass("active");});
        }, 200);
        }
    });
})(jQuery);