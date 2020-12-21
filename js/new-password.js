$(function() {
    $("#reset_form").submit(function() {
        return !validatePassword($("[name=password]"), $("[name=password_confirm]"));
    });
});