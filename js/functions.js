var old_values;

$(function() {
    $("#formhandler-country").change(updateSelects);
    $("#formhandler-birthday").change(updateSelects);
});

function updateSelects() {
    var job = [];
    $("#job-choices select, #job-choices input").each(function(i, e) {
        var key = $(e).attr("key");
        if (!key) return;
        if ($(e).is("select")) {
            job[key] = $(e).find("option:selected").text();
        } else if ($(e).is("input[type=radio]:checked")) {
            job[key] = $("label[for='" + $(e).attr("id") + "']").text();
        } else if ($(e).is("input[type=checkbox]")) {
            job[key] = $(e).is(":checked");
        } else {
            job[key] = $(e).val();
        }
        $(e).off();
    });
    var data = getJobSelects(job);

    var divs = $("#job-choices").children(), lastDiv = divs[0];
    for (var i = 0; i < data.length; i++) {
        var found = false;
        var hash = createHash(data[i]);
        for (var j = 0; j < divs.length && !found; j++) {
            if (hash == $(divs[j]).attr("data-hash")) {
                found = true;
                lastDiv = divs.splice(j, 1)[0];
            }
        }
        if (!found) {
            lastDiv = insertSelect(data[i], lastDiv)[0];
        }
    }
    for (var j = 1; j < divs.length; j++) {
        $(divs[j]).remove();
    }
    $("#job-choices select, #job-choices input").change(updateSelects);
}

function insertSelect(select, lastDiv) {
    var html = '<div class="row form-group"><div class="medium-3 columns">';
    if (select.name && select.key) {
        html += 
            '<label for="formhandler-' + select.key + '" class="inline">' +
            '    ' + select.name + (select.required ? ' <span>*</span>' : '') +
            '</label>';
    }
    html += '</div><div class="medium-9 columns">';

    switch (select.type) {
        case undefined:
            html += '<select ' + getAttributes(select) + '>' + 
                        '<option value="" selected="selected"' + (select.required ? ' disabled> Bitte wählen' : '> ---') + ' </option>';
            for (var i = 0; i < select.children.length; i++) {
                if (typeof select.children[i] === 'string') {
                    html += '<option>' + select.children[i] + '</option>';
                } else {
                    html += '<optgroup label="' + select.children[i].name + '">';
                    for (var j = 0; j < select.children[i].children.length; j++) {
                        html += '<option>' + select.children[i].children[j] + '</option>';
                    }
                    html += '</optgroup>';
                }
            }
            html += '</select>';
            break;
        case "radio":
            html += "<fieldset class='radiod'>";
            for (var i = 0; i < select.children.length; i++) {
                var suf = "_" + select.children[i].replace(" ", "_");
                html += '<input type="radio" ' + getAttributes(select, suf) + ' value="' + select.children[i] + '">';
                html += '<label for="formhandler-' + select.key + suf + '">' + select.children[i] + '</label>';
            }
            html += "</fieldset>";
            break;
        case "checkbox":
            html += '<input type="checkbox" ' + getAttributes(select) + ' value="1">';
            html += '<label for="formhandler-' + select.key + '">' + select.text + '</label>';
            break;
        case "text":
            html += '<input type="text" ' + getAttributes(select) + '>';
            break;
        case "file":
            html += '<input type="file" accept=".pdf,.jpg,.jpeg,.png" ' + getAttributes(select) + '>';
            break;
        case "static_text":
            html += '<div class="padded-span">' + select.text + '</div>';
            break;
        default:
            console.error("unknown type: " + select.type);
            return;
    }

    html = '<div data-hash="' + createHash(select) + '">' + html + '</div></div></div>';

    if (typeof waitSetValue === "function") {
        if (old_values && old_values[select.key]) {
            waitSetValue(select.key, old_values[select.key], 0);
        } else if (select.children && !select.mode) {
            waitSetValue(select.key, "", 0);
        }
    }

    var div = $(html);
    if (lastDiv) {
        div.insertAfter(lastDiv);
    } else {
        $("#job-choices").append(div);
    }
    return div;
}

function getAttributes(obj, id_suf) {
    id_suf = id_suf || "";
    var str = "";
    if (obj.key) {
        str += 'key="' + obj.key + '" id="formhandler-' + obj.key + id_suf + '"';
        if (!obj.no_name) {
            str += ' name="data[sub_job][' + obj.key + ']"';
        }
    }
    str += obj.required ? ' required' : '';
    str += (obj.disabled || $("#formhandler-job").attr("disabled")) ? ' disabled' : ' not-disabled';
    if (obj.attributes) {
        str += " " + obj.attributes;
    }
    return str;
}

function doValidation(e, valFunc) {
    $("#form_submit").attr("disabled", "");
    var error = false;
    // confirm efn
    // console.log(e);
    $(e.target).find("[data-efn]").each(function(i, e) {
        if (!$(e).is("[required]") && $(e).val() === "") {
            return;
        }
        
        var tmp_error = false;
        var efn = $(e).val().replace(" ", "");
        // if (efn.length != 15) {
        //     tmp_error = true;
        // } else {
        if (efn.length != 15 || !$.isNumeric(efn)) {
            tmp_error = true;
        } else {
            tmp_error = false;
            // var tmp = 0;
            // for (var i = 0; i < efn.length; i++) {
            //     var x = parseInt(efn[i]);
            //     if (x === NaN) {
            //         tmp_error = true;
            //         break;
            //     }
            //     z = x * (2 - (efn.length - i) % 2); // multiplizieren mit 2 an jeder 2. stelle
            //     tmp += z < 10 ? z : z % 9 || 9;    // quersumme
            // }
            // if (!tmp_error && tmp % 10 != 0) {
            //     tmp_error = true;
            // }
        }
        // console.log("---------");
        // console.log(efn.length);
        // console.log(tmp_error);
        // console.log($.isNumeric(efn));
        // console.log("---------");
        if (tmp_error == true) {
            addError(e, "Ungültige EFN.");
            error = true;
        } else {
            removeError(e);
        }
    });
    $(e.target).find("[data-year]").each(function(i, e) {
        var val = parseInt($(e).val());
        if (val == NaN || val < new Date().getFullYear() || val > new Date().getFullYear() + 15) {
            addError(e, "Bitte geben Sie ein gültiges Abschlussjahr an.");
            error = true;
        } else {
            removeError(e);
        }
    });
    error = validatePassword($("#formhandler-password"), $("#formhandler-passwordconfirm")) || error;
    error = valFunc(error) || error;
    return !error;
}

function validatePassword(pass, confirm) {
    if (pass.length > 0 && !pass.is("[disabled]")) {
        if (pass.val() != confirm.val()) {
            addError(pass);
            addError(confirm, "Die Passwörter stimmen nicht überein.");
            $(document).scrollTop(0);
            return true;
        } else if (  pass.val().length < 8 ||
                     pass.val().length > 40 ||
                    !pass.val().match(/.*\d.*/) ||
                    !pass.val().match(/.*[a-z].*/) ||
                    !pass.val().match(/.*[A-Z].*/)) {
            addError(pass);
            addError(confirm, "Ihr Passwort entspricht nicht den Vorgaben.");
            $(document).scrollTop(0);
            return true;
        } else {
            removeError(pass);
            removeError(confirm);
        }
    }
    return false;
}

function addError(elem, msg) {
    $(elem).addClass("error");
    if (msg) {
        if ($("#error_" + $(elem)[0].id).length == 0) {
            var parent = $(elem).parent();
            var classes = parent.attr("class");
            while (!parent.hasClass("row form-group")) {
                classes = parent.attr("class");
                parent = parent.parent();
            }
            parent.after(
                '<div class="row form-group" id="error_' + $(elem)[0].id + '">' +
                    (classes.indexOf("9") > -1 ? '<div class="medium-3 columns"></div>' : '') + 
                    '<div class="' + classes + '">' +
                        '<div class="alert alert-danger">' + msg + '</div>' +
                    '</div>' + 
                '</div>'
            );
        } else {
            console.log($("#error_" + $(elem)[0].id).find("div.alert").length);
            $("#error_" + $(elem)[0].id).find("div.alert").html(msg);
        }
    }
}

function removeError(elem) {
    $(elem).removeClass("error");
    $("#error_" + $(elem)[0].id).remove();
}

function removeAllErrors() {
    $("[id^='error_']").each(function() {
        removeError("#" + this.id.substring(6));
    })
}

function loadProfileData(email) {
    if (typeof toload !== "undefined" && toload) {
        setTimeout(loadProfileData.bind(this, email), 10);
        return;
    }
    $.post(apiPath, { cmd: "get/data", email: email }, function(data) {
        var json = tryParseJSON(data);
        if (json && json.success) {
            data = old_values = json.data[0];
            Object.keys(data).forEach(function (key) {
                // if (data[key] === null) {
                //     return;
                // }
                waitSetValue(key, data[key], 0);
            });
        } else {
            console.log("loading data failed: ", json);
        }
    });
}

function waitSetValue(key, value, step) {
    if (step > 50) {
        // console.log("could not set " + selector + " to " + value);
        return;
    }
    var elem = $("[name*='[" + key + "]']");
    if (elem.is("input, textarea") || elem.children().length > 0) {
        if (elem.is("input[type=checkbox]")) {
            if (value == 1) {
                elem.prop("checked", true);
            }
        } else if (elem.is("input[type=radio]")) {
            elem.each(function(i, e) {
                if ($("label[for='" + $(e).attr("id") + "']").text() == value) {
                    $(e).prop("checked", true);
                }
            });
        } else if (elem.is("input[type=file]") && value !== null) {
            var html = "<div class='padded'><a href='" + value + "' target='_blank'>Ihr Dokument</a><input type='hidden' " + getAttributes({key: "keep-uploaded_document"}) + " value='1'></div>";
            if (typeof search_selects !== "undefined") {
                html += "<div class='padded'><label><input type='checkbox' key='doc_status' id='formhandler-doc_status' name='data[doc_status]' value='1'>Dokument bestätigt</label></div>";
            }
            elem.replaceWith(html);
        } else {
            elem.val(value);
        }
        elem.change();

        if (value === null || value === "") {
            var no_elem = $("[key='no-" + key + "']");
            if (no_elem.length) {
                no_elem.prop("checked", true);
                no_elem.change();
            }
        }
    } else {
        setTimeout(waitSetValue.bind(this, key, value, step + 1), 30);
    }
}

function tryParseJSON(data) {
    try {
        json = JSON.parse(data);
        console.log(json);
        return json;
    } catch (e) {
        console.log("error parsing: " + e, data);
    };
}

function createHash(select) {
    var str = select.key + select.required + addChildrenToHashString("", select.children);
    var hash = 0, i, chr;
    if (str.length === 0) return hash;
    for (i = 0; i < str.length; i++) {
      chr   = str.charCodeAt(i);
      hash  = ((hash << 5) - hash) + chr;
      hash |= 0; // Convert to 32bit integer
    }
    return hash;
}

function addChildrenToHashString(str, children) {
    if (!children) {
        return str;
    } else if (typeof children == "string") {
        return str + children;
    } else {
        for (var i = 0; i < children.length; i++) {
            str = addChildrenToHashString(str, children[i]);
        }
        return str;
    }
}

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function pushEfn(job, obj, required) {
    obj.push({
        type: "static_text",
        text: 'Ärzte geben im folgenden Abschnitt Ihre EFN (Einheitliche Fortbildungsnummer oder "Barcode-Nummer" von Ihrer Ärztekammer) ein. Sie dient als Berufsnachweis. <a target="_blank" class="underline" href="http://medical-tribune.de/faq/#c4025">Mehr Informationen</a>'
    });
    obj.push({
        name: "EFN",
        key: "efn",
        type: "text",
        required: Boolean(required && !job["no-efn"]),
        disabled: Boolean(job["no-efn"]),
        attributes: "data-efn"
    });
    if (required) {
        obj.push({
            key: "no-efn",
            type: "checkbox",
            no_name: true,
            text: "Ich habe keine EFN."
        });
        if (job["no-efn"]) {
            obj.push({
                type: "static_text",
                text: "Bitte laden Sie eine Kopie bzw. ein Foto eines Berufsnachweises (z.B. Arztausweis, Approbationsurkunde) hoch. Nach kurzer Prüfung werden wir Ihr Nutzerkonto freischalten und Sie benachrichtigen."
            });
            obj.push({
                name: "Nachweis für medizinischen Beruf",
                key: "uploaded_document",
                required: Boolean(required),
                type: "file"
            });
        }
    }
}

function getJobSelects(job) {
    var obj = [];
    switch (job["main"]) {
        case "Apotheker/in angestellt":
        case "Apotheker/in selbstständig":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",
                required: true,
                children: [
                    "Apotheke",
                    "Apothekerkammer",
                    "Apothekerverband",
                    "Apothekerverein",
                    "Großhandel",
                    "Krankenhaus",
                    "Krankenkasse",
                    "Pharmazeutische Industrie",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            switch (job["work_area"]) {
                case "Apotheke":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Bundeswehrapotheke",
                            "Krankenhausapotheke",
                            "Versandbereich",
                            "nicht deutsche Versandapotheke",
                            "nicht deutsche stationäre Apotheke",
                            "öffentliche Filialapotheke",
                            "öffentliche Hauptapotheke"
                        ]
                    });
                    break;
                case "Pharmazeutische Industrie":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Außendienst/Vertrieb",
                            "E-Business",
                            "Einkauf",
                            "Geschäftsführung",
                            "IT",
                            "Klinische Forschung",
                            "Marketing",
                            "Marktforschung",
                            "Med.-Wiss.",
                            "Personalabteilung",
                            "PR/Öffentlichkeitsarbeit",
                            "Produktmanagement",
                            "Sonstiges"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "Verband",
                key: "association",
                children: [
                    "ADA",
                    "Adexa",
                    "Apothekerverband Brandenburg",
                    "Apothekerverband Mecklenburg-Vorpommern",
                    "Apothekerverband Nordrhein",
                    "Apothekerverband Rheinland-Pfalz",
                    "Apothekerverband Schleswig-Holstein",
                    "Apothekerverband Westafeln-Lippe",
                    "Bayerischer Apothekerverband",
                    "Berliner Apotheker-Verein",
                    "Bremer Apothekerverband",
                    "DPV",
                    "Freie Apothekerschaft e.V.",
                    "Hamburger Apothekerverein",
                    "Hessischer Apothekerverband",
                    "Landesapothekerverband Baden-Württemberg",
                    "Landesapothekerverband Niedersachsen",
                    "Landesapothekerverband Sachsen-Anhalt",
                    "Saarländischer Apothekerverein",
                    "Sächsischer Apothekerverband",
                    "Thüringer Apothekerverband",
                    "VDPP",
                    "Sonstige",
                    "nicht-deutscher Verband"
                ]
            });
            obj.push({
                name: "Apothekerkammer",
                key: "pharmacist_chamber",
                children: [
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
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Arzt/Ärztin im Rentenalter":
        case "Arzt/Ärztin in Weiterbildung": 
        case "Arzt/Ärztin bzw. Facharzt/Fachärztin": 
            obj.push({
                name: "Fachgebiet",
                key: "special_field",
                required: true,
                children: [
                    {
                        name: "Allgemeinmedizin",
                        children: [
                            "FA Allgemeinmedizin",
                            "FA Innere und Allgemeinmedizin"
                        ]
                    },
                    {
                        name: "Anästhesiologie",
                        children: [
                            "FA Anästhesiologie"
                        ]
                    },
                    {
                        name: "Anatomie",
                        children: [
                            "FA Anatomie"
                        ]
                    },
                    {
                        name: "Arbeitsmedizin",
                        children: [
                            "FA Arbeitsmedizin"
                        ]
                    },
                    {
                        name: "Augenheilkunde",
                        children: [
                            "FA Augenheilkunde"
                        ]
                    },
                    {
                        name: "Biochemie",
                        children: [
                            "FA Biochemie"
                        ]
                    },
                    {
                        name: "Chirurgie",
                        children: [
                            "FA Allgemeinchirurgie",
                            "FA Gefäßchirurgie",
                            "FA Herzchirurgie",
                            "FA Kinderchirurgie",
                            "FA Orthopädie und Unfallchirurgie",
                            "FA Plastische Chirurgie",
                            "FA Plastische und Ästhetische Chirurgie",
                            "FA Thoraxchirurgie",
                            "FA Visceralchirurgie"
                        ]
                    },
                    {
                        name: "Frauenheilkunde und Geburtshilfe",
                        children: [
                            "FA Frauenheilkunde und Geburtshilfe"
                        ]
                    },
                    {
                        name: "Hals-Nasen-Ohrenheilkunde",
                        children: [
                            "FA Hals-Nasen-Ohrenheilkunde",
                            "FA Sprach-, Stimm- und kindliche Hörstörungen"
                        ]
                    },
                    {
                        name: "Haut- und Geschlechtskrankheiten",
                        children: [
                            "FA Haut- und Geschlechtskrankheiten"
                        ]
                    },
                    {
                        name: "Humangenetik",
                        children: [
                            "FA Humangenetik",
                            "Fachwissenschaftler Genetik"
                        ]
                    },
                    {
                        name: "Hygiene und Umweltmedizin",
                        children: [
                            "FA Hygiene und Umweltmedizin"
                        ]
                    },
                    {
                        name: "Innere Medizin",
                        children: [
                            "FA Innere Medizin"
                        ]
                    },
                    {
                        name: "Kinder- und Jugendmedizin",
                        children: [
                            "FA Kinder- und Jugendmedizin"
                        ]
                    },
                    {
                        name: "Kinder- und Jugendpsychiatrie und -psychotherapie",
                        children: [
                            "FA Kinder- und Jugendpsychiatrie und -psychotherapie"
                        ]
                    },
                    {
                        name: "Laboratoriumsmedizin",
                        children: [
                            "FA Laboratoriumsmedizin",
                            "Fachwissenschaftler Chemie und Labordiagnostik"
                        ]
                    },
                    {
                        name: "Mikrobiologie, Virologie und Infektionsepidemiologie",
                        children: [
                            "FA Mikrobiologie, Virologie und Infektionsepidemiologie",
                            "Fachzahnarzt für Mikrobiologie"
                        ]
                    },
                    {
                        name: "Mund-Kiefer-Gesichtschirurgie",
                        children: [
                            "FA Mund-Kiefer-Gesichtschirurgie",
                            "Fachzahnarzt für Kieferchirurgie"
                        ]
                    },
                    {
                        name: "Neurochirurgie",
                        children: [
                            "FA Neurochirurgie"
                        ]
                    },
                    {
                        name: "Neurologie",
                        children: [
                            "FA Neurologie"
                        ]
                    },
                    {
                        name: "Nuklearmedizin",
                        children: [
                            "FA Nuklearmedizin"
                        ]
                    },
                    {
                        name: "Öffentliches Gesundheitswesen",
                        children: [
                            "FA Öffentliches Gesundheitswesen"
                        ]
                    },
                    {
                        name: "Pathologie",
                        children: [
                            "FA Neuropathologie",
                            "FA Pathologie",
                            "Fachwissenschaftler Zytologie/Histologie"
                        ]
                    },
                    {
                        name: "Pharmakologie",
                        children: [
                            "FA Klinische Pharmakologie",
                            "FA Pharmakologie und Toxikologie"
                        ]
                    },
                    {
                        name: "Physikalische und Rehabilitative Medizin",
                        children: [
                            "FA Physikalische und Rehabilitative Medizin"
                        ]
                    },
                    {
                        name: "Physiologie",
                        children: [
                            "FA Physiologie"
                        ]
                    },
                    {
                        name: "Psychiatrie und Psychotherapie",
                        children: [
                            "FA Psychiatrie und Psychotherapie"
                        ]
                    },
                    {
                        name: "Psychosomatische Medizin und Psychotherapie",
                        children: [
                            "FA Psychosomatische Medizin und Psychotherapie"
                        ]
                    },
                    {
                        name: "Radiologie",
                        children: [
                            "FA Radiologie"
                        ]
                    },
                    {
                        name: "Rechtsmedizin",
                        children: [
                            "FA Rechtsmedizin"
                        ]
                    },
                    {
                        name: "Strahlentherapie",
                        children: [
                            "FA Strahlentherapie"
                        ]
                    },
                    {
                        name: "Transfusionsmedizin",
                        children: [
                            "FA Transfusionsmedizin"
                        ]
                    },
                    {
                        name: "Urologie",
                        children: [
                            "FA Urologie"
                        ]
                    },
                    {
                        name: "Sonstige Fachgruppen",
                        children: [
                            "Fachzahnarzt für theoretisch-experimentelle Medizin",
                            "Fachbiologie der Medizin",
                            "Fachwissenschaftler Immunologie",
                            "Kinder- und Jugendlichen-Psychotherapeut",
                            "Psychologischer Psychotherapeut",
                            "Sonstiges Fachgebiet"
                        ]
                    }
                ]
            });
            switch (job["special_field"]) {
                case "FA Allgemeinmedizin":
                case "FA Innere und Allgemeinmedizin":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Geriatrie"
                        ]
                    });
                    break;
                case "FA Allgemeinchirurgie":
                case "FA Gefäßchirurgie":
                case "FA Herzchirurgie":
                case "FA Kinderchirurgie":
                case "FA Orthopädie und Unfallchirurgie":
                case "FA Plastische Chirurgie":
                case "FA Plastische und Ästhetische Chirurgie":
                case "FA Thoraxchirurgie":
                case "FA Visceralchirurgie":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Plastische Chirurgie"
                        ]
                    });
                    break;
                case "FA Frauenheilkunde und Geburtshilfe":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Gynäkologische Endokrinologie und Reproduktionsmedizin",
                            "SP Gynäkologische Onkologie",
                            "SP Spezielle Geburtshilfe und Perinatalmedizin",
                        ]
                    });
                    break;
                case "FA Innere Medizin":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        required: true,
                        children: [
                            "SP Gesamte Innere Medizin",
                            "SP Angiologie",
                            "SP Endokrinologie und Diabetologie",
                            "SP Gastroenterologie",
                            "SP Hämatologie und Onkologie",
                            "SP Kardiologie",
                            "SP Nephrologie",
                            "SP Pneumologie",
                            "SP Rheumatologie",
                            "SP Geriatrie",
                        ]
                    });
                    break;
                case "FA Kinder- und Jugendmedizin":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Kinder-Endokrinologie und -Diabetologie",
                            "SP Kinder-Gastroenterologie",
                            "SP Kinder-Nephrologie",
                            "SP Kinder-Hämatologie und -Onkologie",
                            "SP Kinder-Kardiologie",
                            "SP Neonatologie",
                            "SP Neuropädiatrie",
                            "SP Kinder-Pneumologie"
                        ]
                    });
                    break;
                case "FA Neurologie":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Geriatrie"
                        ]
                    });
                    break;
                case "FA Psychiatrie und Psychotherapie":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Geriatrie",
                            "SP Forensische Psychiatrie"
                        ]
                    });
                    break;
                case "FA Radiologie":
                    obj.push({
                        name: "Schwerpunkt/Spezialisierung",
                        key: "specialisation",
                        children: [
                            "SP Kinderradiologie",
                            "SP Neuroradiologie"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            obj.push({
                name: "Ärztekammer",
                key: "medical_association",
                required: true,
                children: [
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
                ],
            });
            pushEfn(job, obj, true);
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Sonstiges",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            obj.push({
                name: "Arbeitsbereich Zusatz",
                key: "work_area_extra",
                children: [
                    "Ärztekammer/KV",
                    "Berufsverband",
                    "Gesellschaft",
                    "Krankenkasse",
                    "Sonstiges"
                ]
            });
            switch (job["work_area"]) {
                case "Apotheke":
                case "MVZ":
                case "Praxis":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;
            }
            if (job["main"] == "Arzt/Ärztin in Weiterbildung") {
                obj.push({
                    name: "Voraussichtliches Abschlussjahr",
                    key: "expected_graduation_year",
                    type: "text",
                    attributes: "data-year"
                });
            }
            break;
        case "Assistent/in (Zahnmedizin)":
        case "Zahnarzt/Zahnärztin":
            obj.push({
                name: "Spezialisierung",
                key: "specialisation",                    
                required: true,
                children: [
                    "Kieferorthopädie",
                    "Mund-Kiefer-Gesichtschirurgie",
                    "Oralchirurgie",
                    "Zahnmedizin",
                ]
            });
            obj.push({
                name: "Fokus",
                key: "focus",                    
                required: true,
                children: [
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
                ]
            });
            obj.push({
                name: "Zahnärztekammer",
                key: "dentist_chamber",
                children: [
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
                ]
            });
            obj.push({
                name: "Verband",
                key: "association",
                children: [
                    "AWMF",
                    "BDDH",
                    "BdK",
                    "BDO",
                    "DG Paro",
                    "DGAEZ",
                    "DGAZ",
                    "DGCZ",
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
                    "FVDZ",
                    "SGMKG",
                    "Verband medizinischer Fachberufe",
                    "young dentists"
                ]
            });
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Sonstiges",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            switch (job["work_area"]) {
                case "Apotheke":
                case "MVZ":
                case "Praxis":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Jurist/in":
            obj.push({
                name: "Fachrichtung",
                key: "subject_area",                
                required: true,
                children: [
                    "Familienrecht",
                    "Medizinrecht",
                    "Sozialrecht"
                ]
            });
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                
                required: true,
                children: [
                    "Medizinische Institution",
                    "Rechtsanwaltskanzlei",
                    "Sonstiges ",
                    "Verband"
                ]          
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Medizin-Journalist/in":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Sonstiges",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            obj.push({
                name: "Arbeitsbereich Zusatz",
                key: "work_area_extra",
                children: [
                    "Ärztekammer/KV",
                    "Berufsverband",
                    "Gesellschaft",
                    "Krankenkasse",
                    "Sonstiges"
                ] 
            });
            switch (job["work_area"]) {
                case "Apotheke":
                case "MVZ":
                case "Praxis":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Medizinisches Fachpersonal":
            obj.push({
                name: "Fachrichtung",
                key: "subject_area",                
                required: true,
                children: [
                    "Akademische Pflegekraft",
                    "Altenpfleger/in",
                    "Dipl. Pflegewirt/in",
                    "Ergotherapeut/in",
                    "Gesundheits- und Kinderkrankenpfleger/in",
                    "Gesundheits- und Krankenpflegehelfer/in",
                    "Gesundheits- und Krankenpfleger/in",
                    "Hebamme/Entbindungshelfer",
                    "Heilpraktiker/in",
                    "Logopäde/Logopädin",
                    "Medizinische Fachangestellte (MFA)/Arzthelferin",
                    "MTA",
                    "Notfallsanitäter",
                    "Operationstechn. Assistentinnen/Assistenten",
                    "PDL",
                    "Pflegedirektor/in",
                    "Pflegehelfer/in",
                    "Pflegekraft",
                    "Pflegelehrer",
                    "Pflegeschüler/in",
                    "Pharmazeutisch kaufmännische/r Angestellte/r",
                    "Physiotherapeut/in",
                    "PTA",
                    "Rettungsassistent/in",
                    "Rettungssanitäter/in",
                    "RTA",
                    "Stationsleiter",
                    "Zahnarzthelfer/in"
                ]
            });
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                
                required: true,
                children: [
                    "Ambulanter Pflegedienst",
                    "Apotheke",
                    "Ärztekammer",
                    "Berufsverband",
                    "Klinik",
                    "Klinikverbund",
                    "Krankenkasse",
                    "Medizinische Institution",
                    "Pflegekammer",
                    "Pflegeschule",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Stationäre Pflegeeinrichtung",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien",
                    "Vorsorge- und Reha-Einrichtung",
                    "Sonstiges"
                ]
            });
            obj.push({
                name: "Arbeitsbereich Zusatz",
                key: "work_area_extra",
                children: [
                    "Berufsverband",
                    "Gesellschaft"
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            obj.push({
                name: "Verband",
                key: "association",
                children: [
                    "ABVP",
                    "ADS",
                    "AGVP",
                    "AVG",
                    "BAPP",
                    "BeKD",
                    "BFLK",
                    "BLGS",
                    "BPA",
                    "BV Pflegemanagement",
                    "BVPP",
                    "DBfK",
                    "DBVA",
                    "DGF",
                    "DHV",
                    "DPV",
                    "DRK",
                    "DVLAB",
                    "VfAP",
                    "VHD",
                    "VPU",
                    "Wohlfahrtsverbände"
                ]
            });
            pushEfn(job, obj);
            break;
        case "Mitarbeiter/in Gesundheitswirtschaft":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                
                required: true,
                children: [
                     "Geschäftsführung",
                     "Personalwesen",
                     "Verwaltung",
                     "Sonstiges"
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Mitarbeiter/in medizinischer Institutionen":
            obj.push({
                name: "Fachrichtung",
                key: "subject_area",                
                required: true,
                children: [
                     "Gesundheitswissenschaftler/in",
                     "Pflegepädagoge/Pflegepädagogin",
                     "Praxisanleiter/in",
                     "Qualitätsmanager/in"
                ]
            });
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                
                required: true,
                children: [
                     "Ambulanter Pflegedienst",
                     "Apotheke",
                     "Ärztekammer",
                     "Berufsverband",
                     "Klinik",
                     "Kilinikverbund",
                     "Krankenkasse",
                     "Medizinische Institution",
                     "Pflegekammer",
                     "Pflegeschule",
                     "Pharmazeutische Industrie",
                     "Praxis",
                     "Rettungsdienst",
                     "Stationäre Pflegeeinrichtung",
                     "Universität/Forschung",
                     "Verlag/Presse/Medien",
                     "Vorsorge- und Reha-Einrichtung",
                     "Sonstiges"
                ]
            });
            obj.push({
                name: "Arbeitsbereich Zusatz",
                key: "work_area_extra",
                children: [
                     "Berufsverband",
                     "Gesellschaft"
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Pharmazeutische Industrie":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien",
                    "Sonstiges"
                ]
            });
            switch (job["work_area"]) {
                case "Medizinische Institution":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Ärztekammer/KV",
                            "Berufsverband",
                            "Gesellschaft",
                            "Krankenkasse",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Pharmazeutische Industrie":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Außendienst/Vertrieb",
                            "E-Business",
                            "Einkauf",
                            "Geschäftsführung",
                            "IT",
                            "Klinische Forschung",
                            "Marketing",
                            "Marktforschung",
                            "Med.-Wiss.",
                            "Personalabteilung",
                            "PR/Öffentlichkeitsarbeit",
                            "Produktmanagement",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Praxis":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Berufsausübungsgemeinschaft",
                            "Einzelpraxis",
                            "Praxisgemeinschaft"
                        ]
                    });
                case "MVZ":
                case "Apotheke":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Pharmazieingenieur/in":
        case "PTA":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Apothekerkammer",
                    "Apothekerverband",
                    "Apothekerverein",
                    "Großhandel",
                    "Krankenhaus",
                    "Krankenkasse",
                    "Pharmazeutische Industrie",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            switch(job["work_area"]) {
                case "Apotheke":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Bundeswehrapotheke",
                            "Krankenhausapotheke",
                            "Versandbereich",
                            "nicht deutsche Versandapotheke",
                            "nicht deutsche stationäre Apotheke",
                            "öffentliche Filialapotheke",
                            "öffentliche Hauptapotheke"
                        ]
                    });
                    break;
                case "Pharmazeutische Industrie":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Außendienst/Vertrieb",
                            "E-Business",
                            "Einkauf",
                            "Geschäftsführung",
                            "IT",
                            "Klinische Forschung",
                            "Marketing",
                            "Marktforschung",
                            "Med.-Wiss.",
                            "Personalabteilung",
                            "PR/Öffentlichkeitsarbeit",
                            "Produktmanagement",
                            "Sonstiges"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "Verband",
                key: "association",
                children: [
                    "ADA",
                    "Adexa",
                    "Apothekerverband Brandenburg",
                    "Apothekerverband Mecklenburg-Vorpommern",
                    "Apothekerverband Nordrhein",
                    "Apothekerverband Rheinland-Pfalz",
                    "Apothekerverband Schleswig-Holstein",
                    "Apothekerverband Westafeln-Lippe",
                    "Bayerischer Apothekerverband",
                    "Berliner Apotheker-Verein",
                    "Bremer Apothekerverband",
                    "DPV",
                    "Freie Apothekerschaft e.V.",
                    "Hamburger Apothekerverein",
                    "Hessischer Apothekerverband",
                    "Landesapothekerverband Baden-Württemberg",
                    "Landesapothekerverband Niedersachsen",
                    "Landesapothekerverband Sachsen-Anhalt",
                    "Saarländischer Apothekerverein",
                    "Sächsischer Apothekerverband",
                    "Thüringer Apothekerverband",
                    "VDPP",
                    "Sonstige",
                    "nicht-deutscher Verband"
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Psychologe/Psychologin":
        case "Psychologische/r Psychotherapeut/in":
        case "Tierarzt":
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Sonstiges",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            switch (job["work_area"]) {
                case "Medizinische Institution":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Ärztekammer/KV",
                            "Berufsverband",
                            "Gesellschaft",
                            "Krankenkasse",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Pharmazeutische Industrie":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Außendienst/Vertrieb",
                            "E-Business",
                            "Einkauf",
                            "Geschäftsführung",
                            "IT",
                            "Klinische Forschung",
                            "Marketing",
                            "Marktforschung",
                            "Med.-Wiss.",
                            "Personalabteilung",
                            "PR/Öffentlichkeitsarbeit",
                            "Produktmanagement",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Praxis":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Berufsausübungsgemeinschaft",
                            "Einzelpraxis",
                            "Praxisgemeinschaft"
                        ]
                    });
                case "MVZ":
                case "Apotheke":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;             
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Student/in":
            obj.push({
                name: "Studienfach",
                key: "study_subject",                
                required: true,
                children: [
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
                ]
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Zahnmedizinisches Fachpersonal":
            obj.push({
                name: "Fachrichtung",
                key: "subject_area",                    
                required: true,
                children: [
                    "Dentalhygienikerin (DH)",
                    "Praxismanagerin (PM)",
                    "Zahnmedizinische Fachangestellte (ZFA)/ Zahnarzthelferin (ZAH)",
                    "Zahnmedizinische Fachassistentin (ZMF)",
                    "Zahnmedizinische Prophylaxeassistentin (ZMP)",
                    "Zahnmedizinische Verwaltungsassistentin (ZMV)"
                ]
            });
            obj.push({
                name: "Arbeitsbereich",
                key: "work_area",                    
                required: true,
                children: [
                    "Apotheke",
                    "Dentalindustrie",
                    "Klinik",
                    "Medizinische Institution",
                    "MVZ",
                    "Pharmazeutische Industrie",
                    "Praxis",
                    "Rettungsdienst",
                    "Sonstiges",
                    "Universität/Forschung",
                    "Verlag/Presse/Medien"
                ]
            });
            switch (job["work_area"]) {
                case "Medizinische Institution":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Ärztekammer/KV",
                            "Berufsverband",
                            "Gesellschaft",
                            "Krankenkasse",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Pharmazeutische Industrie":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Außendienst/Vertrieb",
                            "E-Business",
                            "Einkauf",
                            "Geschäftsführung",
                            "IT",
                            "Klinische Forschung",
                            "Marketing",
                            "Marktforschung",
                            "Med.-Wiss.",
                            "Personalabteilung",
                            "PR/Öffentlichkeitsarbeit",
                            "Produktmanagement",
                            "Sonstiges"
                        ]
                    });
                    break;
                case "Praxis":
                    obj.push({
                        name: "Arbeitsbereich Zusatz",
                        key: "work_area_extra",
                        children: [
                            "Berufsausübungsgemeinschaft",
                            "Einzelpraxis",
                            "Praxisgemeinschaft"
                        ]
                    });
                case "MVZ":
                case "Apotheke":
                    obj.push({
                        name: "Form der Erwerbstätigkeit",
                        key: "form_of_employment",
                        children: [
                            "angestellt",
                            "selbstständig"
                        ]
                    });
                    break;
            }
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
        case "Sonstiger medizinischer Beruf":
            obj.push({
                name: "Beschreibung Ihrer Tätigkeit:",
                key: "description",
                required: true,
                type: "text"
            });
            obj.push({
                name: "1. Hauptinteressengebiet",
                key: "main_interests_1",
                children: interests
            });
            obj.push({
                name: "2. Hauptinteressengebiet",
                key: "main_interests_2",
                children: interests
            });
            break;
    }
    return obj;
}