$(function() {
    var cb = $("#praxisletter-cb, #onkoletter-cb, #pneumoletter-cb, #kardioletter-cb, #neuroletter-cb, #gastroletter-cb, #infoletter-cb, #honorarletter-cb, #diabetesletter-cb");
    $("#permission-granted-yes").click(function() {
        cb.prop("disabled", false); // {"checked": true, "disabled": false});
		$("#all-newsletters-cb").prop({"checked": false, "disabled": false}).change();
    });
    $("#permission-granted-no").click(function() {
        cb.prop({"checked": false, "disabled": true}).change();
		$("#all-newsletters-cb").prop({"checked": false, "disabled": true}).change();
    });
    $("#all-newsletters-cb").click(function() {
        cb.prop("checked", $(this).prop("checked"));
    });
    cb.change(function() {
        $("#all-newsletters-cb").prop("checked", cb.not(":checked").length == 0);
    });
    cb.change();
});