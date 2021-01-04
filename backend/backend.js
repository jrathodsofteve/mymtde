var scroll_pos = 0;

$(function() {
    appendOptions(search_selects["newsletter"], $("#clone-search-div select[name='filter-field[]']"), "newsletter");
    // appendOptions(search_selects["newsletter"], $("#advance-csv-div select[name='filter-field[]']"), "newsletter");
    $("[data-customer-id]").click(function(e) {
        if ($(e.target).is("a[href], .state-select")) {
            return;
        }

        loadProfileData($(this).attr("data-customer-id"));
        prepareShowAdminForm();
        // revert new user
        $("#details-delete-button").attr("data-customer-id", $(this).attr("data-customer-id"));
        $("#customer-id").val($(this).attr("data-customer-id"));
        $("#customer-id").removeAttr("disabled");
        $("#admin_profile_form .heading").html("Profil");
        $("#form_submit").attr("value", "Speichern");
        $("#new_pw_div").hide();
        $("#change_pw_div, save_pw_div").show();
        $("#new_pw_div input").attr("disabled", "");
        $("#formhandler-email").attr("disabled", "");
        $("#admin_profile_form .profile-only").show();
        $("#admin_profile_form .enabled-profile-only").removeAttr("disabled");

        $("#admin_profile_form").show();
    });
    $("#cancel_edit_button").click(function() {
        $("#admin_profile_form").hide();
        $("#cancel-edit-div").attr("hidden", "");
        $("#customers-div").show();
        $("#admin_profile_form")[0].reset();
        $("#admin_profile_form select").change();
        $("#details-delete-button").removeAttr("data-customer-id");
        $("#customer-id").val("");
        $(document).scrollTop(scroll_pos);
    });

    $(".state-select").change(function() {
        $this = $(this);
        $this.attr("disabled", "");
        $.post(apiPath, { cmd: "update_status", customer_id: $this.parent().parent().attr("data-customer-id"), status: $this.val() }, function(data) {
            var json = tryParseJSON(data);
            if (json && json.success) {
                $this.removeAttr("disabled");
            } else {
                console.error("Could not change user status.");
            }
        });
    });

    $("#toggle-search-button").click(function() {
        var $i = $(this).children("i");
        if ($i.hasClass("fa-angle-down")) {
            $("#search-div").slideDown();
            $i.removeClass("fa-angle-down").addClass("fa-angle-up");
        } else {
            $("#search-div").slideUp();
            $i.removeClass("fa-angle-up").addClass("fa-angle-down");
        }
    });

    var _newsletterCsvRows = $(".newsletter-rows").html();
    $(".newsletter-rows").html('');
    $(".add-news-letters").on("click", function() {
        $(this).toggleClass("added");
        if($(this).hasClass("added")) {
            $(".newsletter-rows").html(_newsletterCsvRows);
            $(this).text("- Newsletter")
        } else {
            $(".newsletter-rows").html('');
            $(this).text("+ Newsletter")
        }
    });

    $(document).on("click", "input[name='selectAllNewsletter']", function() {
        console.log("clicked");
        var checkedStatus = this.checked;
        $(".newsletter-checkboxes").find("input[type='checkbox']").prop("checked", checkedStatus);
    });

    $("#toggle-advance-export").click(function() {
        var $i = $(this).children("i");
        if ($i.hasClass("fa-angle-down")) {
            $("#advance-csv-div").slideDown();
            $i.removeClass("fa-angle-down").addClass("fa-angle-up");
        } else {
            $("#advance-csv-div").slideUp();
            $i.removeClass("fa-angle-up").addClass("fa-angle-down");
        }
    });
    
    $("#advance-csv-div .add-search-row").click(function() {
        $("#clone-search-div .logic-row.row").clone().insertBefore($("#advance-csv-div .newsletter-rows"));
        $("#advance-csv-div .default-row").first().clone().insertBefore($("#advance-csv-div .newsletter-rows"));
        
        if ($("#advance-csv-div .filter-row").length > 1) {
            $("#advance-csv-div .remove-search-row").prop("disabled", false);
        }
        updateCSVButtons();
    });

    $("#search-div .add-search-row").click(function() {
        $("#clone-search-div .row").clone().insertBefore($("#search-div .search-row"));
        if ($("#search-div .filter-row").length > 1) {
            $("#search-div .remove-search-row").prop("disabled", false);
        }
        updateButtons();
    });

    $("#advance-csv-div .remove-search-row").click(function() {
        if ($("#advance-csv-div .filter-row").length > 1) {
            $("#advance-csv-div .filter-row").last().remove();
            $("#advance-csv-div .logic-row").last().remove();
        }
        if ($("#advance-csv-div .filter-row").length <= 1) {
            $("#advance-csv-div .remove-search-row").prop("disabled", true);
        }
        var indent = 0;
        if ($("#advance-csv-div .logic-row").length) {
            indent = getIndent($("#advance-csv-div .logic-row").last());
        }
        var curInd = getIndent($("#advance-csv-div .filter-row").last());
        if (curInd != indent) {
            $("#advance-csv-div .filter-row").last().addClass("indent-" + indent).removeClass("indent-" + curInd);
        }
        updateCSVButtons();
    });

    $("#search-div .remove-search-row").click(function() {
        if ($("#search-div .filter-row").length > 1) {
            $("#search-div .filter-row").last().remove();
            $("#search-div .logic-row").last().remove();
        }
        if ($("#search-div .filter-row").length <= 1) {
            $("#search-div .remove-search-row").prop("disabled", true);
        }
        var indent = 0;
        if ($("#search-div .logic-row").length) {
            indent = getIndent($("#search-div .logic-row").last());
        }
        var curInd = getIndent($("#search-div .filter-row").last());
        if (curInd != indent) {
            $("#search-div .filter-row").last().addClass("indent-" + indent).removeClass("indent-" + curInd);
        }
        updateButtons();
    });

    $("#new-user-button").click(function() {
        prepareShowAdminForm();
        // revert edit mode
        $("#admin_profile_form .heading").html("Benutzer erstellen");
        $("#form_submit").attr("value", "Benutzer erstellen");
        $("#new_pw_div").show();
        $("#change_pw_div, #save_pw_div").hide();
        $("#new_pw_div input").removeAttr("disabled");
        $("#formhandler-email").removeAttr("disabled");
        $("#customer-id").attr("disabled", "");
        $("#admin_profile_form .profile-only").hide();
        $("#admin_profile_form .enabled-profile-only").attr("disabled", "");

        $("#admin_profile_form").show();

    });
    $(".delete-button").click(function(e) {
        e.stopPropagation();

        if (!confirm('Der Benutzer wird endgültig gelöscht. Fortfahren?')) {
            return;
        }

        var elem = $(this);
        while (!elem.is("[data-customer-id]") || elem.is("body")) {
            elem = elem.parent();
        }
        if (elem.is("body")) {
            alert("Could not find a customer id");
            return;
        }

        $.post(apiPath, { cmd: "delete_account", customer_id: elem.attr("data-customer-id") }, function(data) {
            var json = tryParseJSON(data);
            if (json && json.success) {
                location.replace(window.location.href);
            } else {
                alert("Could not delete user!");
            }
        });
    });

    $("#search-div").on("click", ".indent-btn-left", null, function() {
        var $row = $(this).parent().parent();
        if (!$row.is(".logic-row") || getIndent($row) <= 0) {
            throw new Error("!!");
        }
        var $col = $row;
        if (checkValidIndentBack($row, "prev")) {
            $col = $col.add($row.prev());
        }
        if (checkValidIndentBack($row, "next")) {
            $col = $col.add($row.next());
        }
        
        var indent = getIndent($col);
        $col.addClass("indent-" + (indent - 1)).removeClass("indent-" + indent);
        updateButtons();
    });

    $("#search-div").on("click", ".indent-btn-right", null, function() {
        var $row = $(this).parent().parent();
        var indent = getIndent($row);
        if (!$row.is(".logic-row")) {
            throw new Error("!!");
        }
        var $col = $row;
        if (indent == getIndent($row.prev())) {
            $col = $col.add($row.prev());
        }
        if (indent == getIndent($row.next())) {
            $col = $col.add($row.next());
        }
        
        $col.addClass("indent-" + (indent + 1)).removeClass("indent-" + indent);
        updateButtons();
    });

    $("#search-div, #advance-csv-div").on("change", "select[name='filter-field[]']", null, function() {
        var options = search_selects[$(this).val().split(".").pop()];
        var isMultiSelect = $(this).find("option:selected").data("multiselect");
        var select = $(this).parent().parent().find("select[name='compareValue[]']");
        if(isMultiSelect) {
            select.attr("name","compareValue[" + $(this).val() + "][]");
            select.attr("multiple","multiple");
        } else {
            select.removeAttr("multiple");
        }
        var selectCB = $(this).parent().parent().find("select[name='compareBy[]']");
        if (options || $(this).find("option:selected").is("[data-newsletter]")) {
            select.html("");
            options = options || { 0: "nicht abonniert", 1: "abonniert" };
            appendOptions(options, select);
            if (select.is("[disabled]")) {
                select.prop("disabled", false);
                select.siblings().addBack().toggle();
                select.siblings().prop("disabled", true);
            }

            selectCB.children(":not([value='='], [value='!='])").hide()
        } else if (!select.is("[disabled]")) {
            select.siblings().prop("disabled", false).val("");
            select.siblings().addBack().toggle();
            select.prop("disabled", true);
            selectCB.children().show();
        }
    });

    $("#search-div").submit(function(e) {
        var indents = "";
        $("#search-div").children(".filter-row, .logic-row").each(function() {
            indents += getIndent(this);
        });
        $("input[type=hidden][name=indentations]").val(indents);
    });

    $("#logins-count-from, #logins-count-to").datepicker({
        startView: "days",
        autoclose: true,
        format: "dd.mm.yyyy"
    }).change(function() {
        if (!$("#logins-count-from").val() || !$("#logins-count-to").val()) return;
        $("#logins-count").text("");
        $.post(apiPath, { cmd: "get/logins", from: $("#logins-count-from").val(), to: $("#logins-count-to").val() }, function(data) {
            var json = tryParseJSON(data);
            if (json && json.success && json.data) {
                $("#logins-count").text(json.data[0][0]);
            } else {
                console.error("could not load login logs");
            }
        });
    }).on("changeDate", function() {
        $("#logins-count-time").val("timespan");
    }).datepicker("update", "now");

    $("#logins-count-time").change(function() {
        if ($(this).val() == "timespan") return;
        switch ($(this).val()) {
            case "today":
                $("#logins-count-from").datepicker("update", "now");
                break;
            case "last_week":
                $("#logins-count-from").datepicker("update", "-1w");
                break;
            case "last_month":
                $("#logins-count-from").datepicker("update", "-1m");
                break;
        }
        $("#logins-count-to").datepicker("update", "now");
    });

    if (getURLParam("search") === null && $("#stored-filters").val() == null) {
        $("#clone-search-div .row.filter-row").clone().insertBefore($("#search-div .search-row"));
        updateButtons();
    }
    setTimeout(initFields, 1);
});

function initFields() {
    if (toload) {
        setTimeout(initFields, 10);
        return;
    }

    var storedFilters = '';
    if($("#stored-filters").val()) {
        storedFilters = $.parseJSON($("#stored-filters").val());
    }
    if (getURLParam("search") !== null || storedFilters != null) {
        // console.log(getURLParam("indentations"));
        // console.log(storedFilters['indentations']);

        if(getURLParam("search") !== null) {
            var filters = getURLParam("filter-field");
            var operators = getURLParam("operator");
            var compareBy = getURLParam("compareBy");
            var compareValues = getURLParam("compareValue");
            var indents = getURLParam("indentations");
        } else if(storedFilters != null) {
            var filters = storedFilters["filter-field"];
            var operators = storedFilters["operator"];
            var compareBy = storedFilters["compareBy"];
            var compareValues = storedFilters["compareValue"];
            var indents = storedFilters["indentations"];

            // Removing first empty filter
            // $("#search-div").find(".form-group.filter-row:first-child").remove();
            // Removing first empty filter
            // console.log(indents)
            console.log("storedFilters");
        }

        $srow = $("#search-div .search-row");
        $frow = $("#clone-search-div .row.filter-row");
        $lrow = $("#clone-search-div .row.logic-row")
        function insertFRow(id) {
            var $row = $frow.clone().insertBefore($srow).addClass("indent-" + indents[2 * id]);
            $row.find("select[name='filter-field[]']").val(filters[id]).change();
            $row.find("select[name='compareBy[]']").val(compareBy[id]);
            $row.find("[name='compareValue[]']").val(compareValues[id]);
        }

        if (operators) {
            for (var i = 0; i < operators.length; ++i) {
                insertFRow(i);
                var $row = $lrow.clone().insertBefore($srow).addClass("indent-" + indents[2 * i + 1]);
                $row.find("select[name='operator[]']").val(operators[i]);
            }
        }
        if(filters)
           insertFRow(filters.length - 1);
        updateButtons();
    }

    // init infos
    $("[id^=count-by-]").each(function() {
        var cb = $(this).attr("id").replace("count-by-", "");
        if (search_selects[cb]) {
            appendOptions(search_selects[cb], $(this));
        }
        $(this).change(function() {
            $("#user-count-by-" + cb).html("");
            $.post(apiPath, { cmd: "get/count/" + cb, value: $(this).val() }, function(data) {
                var json = tryParseJSON(data);
                if (json && json.success && json.data && json.data[0] && json.data[0][0] !== undefined) {
                    $("#user-count-by-" + cb).html(json.data[0][0]);
                } else {
                    $("#user-count-by-" + cb).html("Could not load data");
                }
            });
        }).change();
    });
    
}

function appendOptions(options, select, extra) {
    extra = extra || "";
    if (extra) {
        extra = " data-" + extra;
    }
    if (options.forEach) {
        options.forEach(function(value) {
            if (typeof value == "string") {
                select.append("<option" + extra  + ">" + value + "</option>");
            } else {
                select.append("<option value='" + value.id + "'" + extra  + ">" + value.val + "</option>");
            }
        });
    } else {
        for (key in options) {
            if (options.hasOwnProperty(key)) {
                select.append("<option value='" + key + "'" + extra  + ">" + options[key] + "</option>");
            }
        }
    }
}

function prepareShowAdminForm() {
    scroll_pos = document.documentElement.scrollTop;
    $("#customers-div").hide();
    $("#alert-row").hide();
    $("#cancel-edit-div").removeAttr("hidden");
    $(document).scrollTop(0);
}

function updateButtons() {
    $("#search-div .logic-row").each(function(i, e) {
        $e = $(e);
        $e.find(".indent-btn-right").prop("disabled", !checkIndentPossible($e));
        $e.find(".indent-btn-left").prop("disabled", getIndent(e) <= 0);
    });
}

function updateCSVButtons() {
    $("#advance-csv-div .logic-row").each(function(i, e) {
        $e = $(e);
        $e.find(".indent-btn-right").prop("disabled", !checkIndentPossible($e));
        $e.find(".indent-btn-left").prop("disabled", getIndent(e) <= 0);
    });
}

function checkIndentPossible($row) {
    return     getIndent($row) < 5 &&
              (checkIndentPartlyPossible($row, "prevAll")
            || checkIndentPartlyPossible($row, "nextAll")
            || checkIndentPartlyPossible($row, "prevAll", true)
            || checkIndentPartlyPossible($row, "nextAll", true));
}

// iterate over all siblings before/after
// if first sibling lower/higher indent found before sibling with same indent, return false
// else return true
function checkIndentPartlyPossible($row, func, before) {
    var indent = getIndent($row);
    var $col = $row[func](".logic-row");
    for (var i = 0; i < $col.length; ++i) {
        var indent2 = getIndent($col[i]);
        if (!before && indent > indent2) {
            return false;
        }
        if (before && indent < indent2) {
            return false;
        }
        if (indent == indent2) {
            return true;
        }
    }
    return before && $col.length == 0;
}

function checkValidIndentBack($row, func) {
    return $row[func]().length && getIndent($row) == getIndent($row[func]()) && ($row[func + "All"](".logic-row").length == 0 || getIndent($row[func]()) > getIndent($row[func + "All"](".logic-row")[0]));
}

function getIndent(row) {
    return parseInt(($(row).attr("class").match(/indent-(\d)/) || { 1: 0 })[1]) || 0;
}

function getURLParam(key, target){
    var values = [];
    target = target || location.href;
    target = decodeURIComponent(target.replace("+", " "));
    
    var pattern = key + '(\\\[\\\d*\\\])?=([^&#]*)';
    var o_reg = new RegExp(pattern, 'ig');
    var matches;
    while (matches = o_reg.exec(target)) {
        if (matches[1]) {
            if (!(values instanceof Array)) {
                values = [];
            }
            values.push(matches[2]);
        } else {
            values = matches[2];
        }
    }

    if (values instanceof Array && values.length == 0) {
        return null;   
    } else {
        return values;
    }
}

var search_selects = {
    status: {
        1: "Aktiv",
        0: "Inaktiv", 
        2: "Update"
    },
    newsletter: {
        praxisletter: "PraxisLetter",
        onkoletter: "OnkoLetter",
        pneumoletter: "PneumoLetter",
        kardioletter: "KardioLetter",
        neuroletter: "NeuroLetter",
        gastroletter: "GastroLetter",
        infoletter: "InfoLetter mit Cartoon",
        honorarletter: "HonorarLetter",
        diabetesletter: "DiabetesLetter",
        paediatrieletter: "PädiatrieLetter",
        gynletter: "GynLetter",
        dermaletter: "DermaLetter"
    },
    work_area: [
        "Ambulanter Pflegedienst",
        "Apotheke",
        "Apothekerkammer",
        "Apothekerverband",
        "Apothekerverein",
        "Ärztekammer",
        "Berufsverband",
        "Dentalindustrie",
        "Geschäftsführung",
        "Großhandel",
        "Kilinikverbund",
        "Klinik",
        "Klinikverbund",
        "Krankenhaus",
        "Krankenkasse",
        "Medizinische Institution",
        "MVZ",
        "Personalwesen",
        "Pflegekammer",
        "Pflegeschule",
        "Pharmazeutische Industrie",
        "Praxis",
        "Rechtsanwaltskanzlei",
        "Rettungsdienst",
        "Sonstiges ",
        "Sonstiges",
        "Stationäre Pflegeeinrichtung",
        "Universität/Forschung",
        "Verband",
        "Verlag/Presse/Medien",
        "Verwaltung",
        "Vorsorge- und Reha-Einrichtung"
    ],
    work_area_extra: [
        "Ärztekammer/KV",
        "Außendienst/Vertrieb",
        "Berufsausübungsgemeinschaft",
        "Berufsverband",
        "Bundeswehrapotheke",
        "E-Business",
        "Einkauf",
        "Einzelpraxis",
        "Geschäftsführung",
        "Gesellschaft",
        "IT",
        "Klinische Forschung",
        "Krankenhausapotheke",
        "Krankenkasse",
        "Marketing",
        "Marktforschung",
        "Med.-Wiss.",
        "nicht deutsche stationäre Apotheke",
        "nicht deutsche Versandapotheke",
        "öffentliche Filialapotheke",
        "öffentliche Hauptapotheke",
        "Personalabteilung",
        "PR/Öffentlichkeitsarbeit",
        "Praxisgemeinschaft",
        "Produktmanagement",
        "Sonstiges",
        "Versandbereich"
    ],
    association: [
        "ABVP",
        "ADA",
        "Adexa",
        "ADS",
        "AGVP",
        "Apothekerverband Brandenburg",
        "Apothekerverband Mecklenburg-Vorpommern",
        "Apothekerverband Nordrhein",
        "Apothekerverband Rheinland-Pfalz",
        "Apothekerverband Schleswig-Holstein",
        "Apothekerverband Westafeln-Lippe",
        "AVG",
        "AWMF",
        "BAPP",
        "Bayerischer Apothekerverband",
        "BDDH",
        "BdK",
        "BDO",
        "BeKD",
        "Berliner Apotheker-Verein",
        "BFLK",
        "BLGS",
        "BPA",
        "Bremer Apothekerverband",
        "BV Pflegemanagement",
        "BVPP",
        "DBfK",
        "DBVA",
        "DG Paro",
        "DGAEZ",
        "DGAZ",
        "DGCZ",
        "DGF",
        "DGFDT",
        "DGI",
        "DGKFO",
        "DGKiZ",
        "DGL",
        "DGMKG",
        "DGPro",
        "DGZ",
        "DGZMK",
        "DGZS",
        "DHV",
        "DPV",
        "DRK",
        "DVLAB",
        "Freie Apothekerschaft e.V.",
        "FVDZ",
        "Hamburger Apothekerverein",
        "Hessischer Apothekerverband",
        "Landesapothekerverband Baden-Württemberg",
        "Landesapothekerverband Niedersachsen",
        "Landesapothekerverband Sachsen-Anhalt",
        "nicht-deutscher Verband",
        "Saarländischer Apothekerverein",
        "Sächsischer Apothekerverband",
        "SGMKG",
        "Sonstige",
        "Thüringer Apothekerverband",
        "VDPP",
        "Verband medizinischer Fachberufe",
        "VfAP",
        "VHD",
        "VPU",
        "Wohlfahrtsverbände",
        "young dentists"
    ],
    pharmacist_chamber: [
        "Apothekerkammer Berlin",
        "Apothekerkammer Bremen",
        "Apothekerkammer des Saarlandes",
        "Apothekerkammer Hamburg",
        "Apothekerkammer Mecklenburg-Vorpommern",
        "Apothekerkammer Niedersachsen",
        "Apothekerkammer Nordrhein",
        "Apothekerkammer Sachsen-Anhalt",
        "Apothekerkammer Schleswig-Holstein",
        "Apothekerkammer Westfalen-Lippe",
        "Bayerische Landesapothekerkammer",
        "Landesapothekerkammer Baden-Württemberg",
        "Landesapothekerkammer Brandenburg",
        "Landesapothekerkammer Hessen",
        "Landesapothekerkammer Rheinland-Pfalz",
        "Landesapothekerkammer Thüringen",
        "Sächsische Landesapothekerkammer",
        "kein Mitglied einer Apothekerkammer",
        "nicht-deutsche Apothekerkammer"
    ],
    special_field: [
        "FA Allgemeinchirurgie",
        "FA Allgemeinmedizin",
        "FA Anästhesiologie",
        "FA Anatomie",
        "FA Arbeitsmedizin",
        "FA Augenheilkunde",
        "FA Biochemie",
        "FA Frauenheilkunde und Geburtshilfe",
        "FA Gefäßchirurgie",
        "FA Hals-Nasen-Ohrenheilkunde",
        "FA Haut- und Geschlechtskrankheiten",
        "FA Herzchirurgie",
        "FA Humangenetik",
        "FA Hygiene und Umweltmedizin",
        "FA Innere Medizin",
        "FA Innere und Allgemeinmedizin",
        "FA Kinder- und Jugendmedizin",
        "FA Kinder- und Jugendpsychiatrie und -psychotherapie",
        "FA Kinderchirurgie",
        "FA Klinische Pharmakologie",
        "FA Laboratoriumsmedizin",
        "FA Mikrobiologie, Virologie und Infektionsepidemiologie",
        "FA Mund-Kiefer-Gesichtschirurgie",
        "FA Neurochirurgie",
        "FA Neurologie",
        "FA Neuropathologie",
        "FA Nuklearmedizin",
        "FA Öffentliches Gesundheitswesen",
        "FA Orthopädie und Unfallchirurgie",
        "FA Pathologie",
        "FA Pharmakologie und Toxikologie",
        "FA Physikalische und Rehabilitative Medizin",
        "FA Physiologie",
        "FA Plastische Chirurgie",
        "FA Plastische und Ästhetische Chirurgie",
        "FA Psychiatrie und Psychotherapie",
        "FA Psychosomatische Medizin und Psychotherapie",
        "FA Radiologie",
        "FA Rechtsmedizin",
        "FA Sprach-, Stimm- und kindliche Hörstörungen",
        "FA Strahlentherapie",
        "FA Thoraxchirurgie",
        "FA Transfusionsmedizin",
        "FA Urologie",
        "FA Visceralchirurgie",
        "Fachbiologie der Medizin",
        "Fachwissenschaftler Chemie und Labordiagnostik",
        "Fachwissenschaftler Genetik",
        "Fachwissenschaftler Immunologie",
        "Fachwissenschaftler Zytologie/Histologie",
        "Fachzahnarzt für Kieferchirurgie",
        "Fachzahnarzt für Mikrobiologie",
        "Fachzahnarzt für theoretisch-experimentelle Medizin",
        "Kinder- und Jugendlichen-Psychotherapeut",
        "Psychologischer Psychotherapeut",
        "Sonstiges Fachgebiet"
    ],
    specialisation: [
        "SP Angiologie",
        "SP Endokrinologie und Diabetologie",
        "SP Forensische Psychiatrie",
        "SP Gastroenterologie",
        "SP Geriatrie",
        "SP Gesamte Innere Medizin",
        "SP Gynäkologische Endokrinologie und Reproduktionsmedizin",
        "SP Gynäkologische Onkologie",
        "SP Hämatologie und Onkologie",
        "SP Kardiologie",
        "SP Kinder-Endokrinologie und -Diabetologie",
        "SP Kinder-Gastroenterologie",
        "SP Kinder-Hämatologie und -Onkologie",
        "SP Kinder-Kardiologie",
        "SP Kinder-Nephrologie",
        "SP Kinder-Pneumologie",
        "SP Kinderradiologie",
        "SP Neonatologie",
        "SP Nephrologie",
        "SP Neuropädiatrie",
        "SP Neuroradiologie",
        "SP Plastische Chirurgie",
        "SP Pneumologie",
        "SP Rheumatologie",
        "SP Spezielle Geburtshilfe und Perinatalmedizin",
        "Kieferorthopädie",
        "Mund-Kiefer-Gesichtschirurgie",
        "Oralchirurgie",
        "Zahnmedizin"
    ],
    main_interests_1: interests,
    main_interests_2: interests,
    submit_method: [
        "Dokument hochladen",
        "E-Mail"
    ],
    form_of_employment: [
        "angestellt",
        "selbstständig"
    ],
    focus: [
        "Allgemeine Zahnheilkunde",
        "Alternative Heilmethoden",
        "Alterszahnmedizin",
        "Ästhetik",
        "Bildgebung",
        "Chirurgie",
        "Computergestützte Zahnmedizin",
        "Endodontie",
        "Funktionstherapie",
        "Implantologie",
        "Kieferorthopädie",
        "Kinderzahnheilkunde",
        "Konservierende Zahnheilkunde",
        "Laserzahnheilkunde",
        "Öffentliches Gesundheitswesen",
        "Parodontologie",
        "Prophylaxe",
        "Prothetik",
        "Psychosomatik",
        "Restaurative Zahnheilkunde",
        "Schlafmedizin",
        "Traumatologie",
        "keinen Schwerpunkt / keine Spezialisierung"
    ],
    subject_area: [
        "Akademische Pflegekraft",
        "Altenpfleger/in",
        "Dentalhygienikerin (DH)",
        "Dipl. Pflegewirt/in",
        "Ergotherapeuten/in",
        "Familienrecht",
        "Gesundheits- und Kinderkrankenpfleger/in",
        "Gesundheits- und Krankenpflegehelfer/in",
        "Gesundheits- und Krankenpfleger/in",
        "Gesundheitswissenschaftler/in",
        "Hebamme/Entbindungshelfer",
        "Heilpraktiker/in",
        "Logopäde/Logopädin",
        "Medizinische Fachangestellte (MFA)/Arzthelferin",
        "Medizinrecht",
        "MTA",
        "Notfallsanitäter",
        "Operationstechn. Assistentinnen/Assistenten",
        "PDL",
        "Pflegedirektor/in",
        "Pflegehelfer/in",
        "Pflegekraft",
        "Pflegelehrer",
        "Pflegepädagoge/Pflegepädagogin",
        "Pflegeschüler/in",
        "Pharmazeutisch kaufmännische/r Angestellte/r",
        "Physiotherapeut/in",
        "Praxisanleiter/in",
        "Praxismanagerin (PM)",
        "PTA",
        "Qualitätsmanager/in",
        "Rettungsassistent/in",
        "Rettungssanitäter/in",
        "RTA",
        "Sozialrecht",
        "Stationsleiter",
        "Zahnarzthelfer/in",
        "Zahnmedizinische Fachangestellte (ZFA)/ Zahnarzthelferin (ZAH)",
        "Zahnmedizinische Fachassistentin (ZMF)",
        "Zahnmedizinische Prophylaxeassistentin (ZMP)",
        "Zahnmedizinische Verwaltungsassistentin (ZMV)"
    ],
    dentist_chamber: [
        "Ärztekammer des Saarlandes - Abteilung Zahnärzte",
        "Bayerische Landeszahnärztekammer",
        "Landeszahnärztekammer Baden-Württemberg",
        "Landeszahnärztekammer Brandenburg",
        "Landeszahnärztekammer Hessen",
        "Landeszahnärztekammer Rheinland-Pfalz",
        "Landeszahnärztekammer Sachsen",
        "Landeszahnärztekammer Thüringen",
        "Zahnärztekammer Berlin",
        "Zahnärztekammer Bremen",
        "Zahnärztekammer Hamburg",
        "Zahnärztekammer Mecklenburg-Vorpommern",
        "Zahnärztekammer Niedersachsen",
        "Zahnärztekammer Nordrhein",
        "Zahnärztekammer Sachsen-Anhalt",
        "Zahnärztekammer Schleswig-Holstein",
        "Zahnärztekammer Westfalen-Lippe",
        "kein Mitglied einer Zahnärztekammer",
        "nicht-deutsche Zahnärztekammer"
    ],
    study_subject: [
        "Gesundheitswissenschaft",
        "Medizin",
        "Pflegemanagement",
        "Pflegepädagogik",
        "Pflegewissenschaft",
        "Pharmazie",
        "Psychologie",
        "Tiermedizin",
        "Zahnmedizin + Medizin",
        "Zahnmedizin"
    ],
    medical_association: [
        "Ärztekammer Berlin",
        "Ärztekammer Bremen",
        "Ärztekammer des Saarlandes",
        "Ärztekammer Hamburg",
        "Ärztekammer Mecklenburg-Vorpommern",
        "Ärztekammer Niedersachsen",
        "Ärztekammer Nordrhein",
        "Ärztekammer Sachsen-Anhalt",
        "Ärztekammer Schleswig-Holstein",
        "Ärztekammer Westfalen-Lippe",
        "Bayerische Landesärztekammer",
        "Landesärztekammer Baden-Württemberg",
        "Landesärztekammer Brandenburg",
        "Landesärztekammer Hessen",
        "Landesärztekammer Rheinland-Pfalz",
        "Landesärztekammer Thüringen",
        "Sächsische Landesärztekammer",
        "kein Mitglied einer Ärztekammer",
        "nicht-deutsche Ärztekammer"
    ]
}