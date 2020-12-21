var old_email;

$(function() {
    $("#logout-btn").click(function() {
        disableProfileSection();
        hideEmailSection();
        hidePwSection();
    });
    $("#cancel_pw_change_button").click(function() {
        hidePwSection();
        enableProfileSection();
    });
    $("#change_pw_button").click(function() {
        removeAllErrors();
        hideEmailSection();
        disableProfileSection();
        showPWSection();
    });
    $("#cancel_email_change_button").click(function() {
        hideEmailSection();
        enableProfileSection();
    });
    $("#change_email_button").click(function() {
        removeAllErrors();
        hidePwSection();
        disableProfileSection();
        showEmailSection();
    });
    $("#formhandler-email").on("input", function() {
        if ($("#formhandler-email").val() == old_email) {
            $("#save_email_change_button").attr("disabled", "");
        } else {
            $("#save_email_change_button").removeAttr("disabled");
        }
    })
    $("#cancel_edit_button").click(disableProfileSection);
    $("#edit_button").click(function() {
        removeAllErrors();
        hidePwSection();
        hideEmailSection();
        enableProfileSection();
    });
    $("form[validate]").submit(function(e) {
        if (validated) {
            return true;
        }
        return doValidation(e, function(error) {
            if ($("#formhandler-password_old").length && !$("#formhandler-password_old").is("[disabled]")) {
                $.post(apiPath, { cmd: "login", password: $("#formhandler-password_old").val() }, function(data) {
                    var json = tryParseJSON(data);
                    if (json && json.success) {
                        if (!json.login) {
                            addError($("#formhandler-password_old"), "Falsches Passwort!");
                            $(document).scrollTop(0);
                            error = true;
                        } else {
                            removeError($("#formhandler-password_old"));
                        }
                        $("#form_submit").removeAttr("disabled");
                        if (!error) {
                            validated = true;
                            $(e.target).submit();
                        }
                    } else {
                        console.log("checking password failed: ", json);
                    }
                });
                return true;
            } else if ($("#formhandler-email").length && !$("#formhandler-email").is("[disabled]")) {
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
                            $(e.target).submit();
                        }
                    } else {
                        console.log("checking email failed: ", json);
                    }
                });
                return true;
            } else {
                $("#form_submit").removeAttr("disabled");
            }
            return error;
        });
    });
    $("#deleteBtn").click(function() {
        $("#deleteBtn").attr("disabled", "");
        $.post(apiPath, { cmd: "send_delete_email" }, function(data) {
            var json = tryParseJSON(data);
            if (json && json.success) {
                $("form").hide();
                $("#deletedDiv").show();
                $(document).scrollTop(0);
            } else {
                console.log("could not send delete email");
            }
        });
    });
    loadProfileData($("#formhandler-email").val());
    if (typeof enablePasswordSection === "undefined") {
        hidePwSection();
    }
    hideEmailSection();
});

function hidePwSection() {
    $("#change_pw_div").show();
    $("#new_pw_div").hide();
    $("#formhandler-password_old").attr("disabled", "");
    $("#formhandler-password").attr("disabled", "");
    $("#formhandler-passwordconfirm").attr("disabled", "");
}

function showPWSection() {
    $("#change_pw_div").hide();
    $("#new_pw_div").show();
    $("#formhandler-password_old").removeAttr("disabled", "");
    $("#formhandler-password").removeAttr("disabled", "");
    $("#formhandler-passwordconfirm").removeAttr("disabled", "");
}

function hideEmailSection() {
    if (old_email) {
        $("#formhandler-email").val(old_email);
    }
    $("#formhandler-email").attr("disabled", "");
    $("#change_email_button").removeAttr("hidden");
    $("#cancel_email_change_button").attr("hidden", "");
    $("#save_email_change_button").attr("hidden", "");
}

function showEmailSection() {
    old_email = $("#formhandler-email").val();
    $("#formhandler-email").removeAttr("disabled");
    $("#change_email_button").attr("hidden", "");
    $("#cancel_email_change_button").removeAttr("hidden");
    $("#save_email_change_button").attr("disabled", "");
    $("#save_email_change_button").removeAttr("hidden");
}

function disableProfileSection() {
    $("[not-disabled]").removeAttr("not-disabled").attr("disabled", "");
    $("#cancel_edit_button").attr("hidden", "");
    $("#edit_button").removeAttr("hidden");
    $("#submit_profile_div").attr("hidden", "");
}

function enableProfileSection() {
    $("[disabled]:not(button, [type=email], [name^='data[password'], [no-enable])").removeAttr("disabled").attr("not-disabled", "");
    $("#edit_button").attr("hidden", "");
    $("#cancel_edit_button").removeAttr("hidden");
    $("#submit_profile_div").removeAttr("hidden");
}
