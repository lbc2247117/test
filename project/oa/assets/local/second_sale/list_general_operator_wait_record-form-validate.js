$('#add_generationOperation_form').validate({
    errorElement: 'span', //default input error message container
    errorClass: 'help-block help-block-error', // default input error message class
    focusInvalid: false, // do not focus the last invalid input
    ignore: "", // validate all fields including form hidden inpu
    messages: {},
    rules: {
        platform_num: {
            required: true,
            minlength: 11
        },
        ww: {
            required: true,
            minlength: 2
        },
        qq: {
            required: true,
            minlength: 5
        },
        name: {
            required: true,
            minlength: 2
        }
    },
    errorPlacement: function (error, element) { // render error placement for each input type
        var icon = $(element).parent('.input-icon').children('i');
        if (!icon.hasClass("fa")) {
            icon = $(element).parent('.auto_radio_ul').parent('.input-icon').children('i');
        }
        icon.removeClass('fa-check').addClass("fa-warning");
        icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
    },
    success: function (label, element) {
        var icon = $(element).parent('.input-icon').children('i');
        if (!icon.hasClass("fa")) {
            icon = $(element).parent('.auto_radio_ul').parent('.input-icon').children('i');
        }
        $(element).closest('.col-md-3').removeClass('has-error').addClass('has-success'); // set success class to the control group
        icon.removeClass("fa-warning").addClass("fa-check");
    },
    submitHandler: function () {
        var data=$("#add_generationOperation_form").serializeJson();
        data.action=2;
        data.id=$("#gen_id").val();
        ycoa.ajaxLoadPost("/api/second_sale/list_general_operator_wait_record.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                $("#claimModal").modal("hide");
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1, payType: 0});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    }
});
$('#add_wait_record_form').validate({
    submitHandler: function () {
        var data = $("#add_wait_record_form").serializeJson();
        data.action = 1;
        ycoa.ajaxLoadPost("/api/second_sale/list_general_operator_wait_record.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                $("#fillModal").modal("hide");
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1, payType: 0});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    }
})
