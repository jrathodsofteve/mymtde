$(function() {
    // validate_form();
    // validate();
    $("form[validate]").submit(validate);
});

function validate(e) {
    console.log("1");
    if (validated) {
        $(document).scrollTop(0);
        return true;
    }
    return doValidation(e, function(error) {
        // console.log(error);
        // console.log("TEst");
        if ($("#formhandler-email").length && !$("#formhandler-email").is("[disabled]")) {
            $.post(apiPath, { cmd: "check/mail", token: $("#formhandler-email").val() }, function(data) {
                var json = tryParseJSON(data);
                if (json && json.success) {
                    if (json.inUse) {
                        addError($("#formhandler-email"), "Diese E-Mail-Adresse existiert bereits!");
                        $(document).scrollTop(0);
                        error = true;
                    } else {
                        removeError($("#formhandler-email"));
                    }
                    $("#form_submit").removeAttr("disabled");
                    if (!error) {
                        validated = true;
                        $("#form_submit").click();
                    }
                } else {
                    console.log("checking email failed: ", json);
                }
            });
            return true;
        }
    });
}

// function validate_form(){
//     // console.log("2");
//     $("#form_submit").on("click", function(e){
//         // console.log("3");
//         // var form_errors = false;
//         // $('input[required]').each(function(){
//         //     // console.log($(this));
//         //     if($(this).val() == ""){
//         //         $(this).addClass("error");
//         //     }else if($(this).hasClass("error")){
//         //         $(this).removeClass("error");
//         //     }
//         // });
//         // $('select[required]').each(function(){
//         //     // console.log($(this));
//         //     if($(this).val() == ""){
//         //         $(this).addClass("error");
//         //     }else if($(this).hasClass("error")){
//         //         $(this).removeClass("error");
//         //     }
//         // });

//         // var _efn = $("#formhandler-efn").val();
//         // if(_efn) {
//         //     var efn = _efn.replace(" ", "");
//         //     console.log($.isNumeric(efn));
//         //     console.log(efn.length);
//         //     if (efn.length != 16 || !$.isNumeric(efn)) {
//         //         addError($("#formhandler-efn"), "Ungültige EFN.");
//         //         e.preventDefault();
//         //     } else {
//         //         removeError($("#formhandler-efn"));
//         //     }
//         //     console.log(_efn);
//         // }
//     });
// }
