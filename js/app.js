var interests = [
    "Akupunktur",
    "Allergologie",
    "Andrologie",
    "Angiologie",
    "Ärztliches Qualitätsmanagement",
    "Balneologie und Medizinische Klimatologie",
    "Betriebsmedizin",
    "Bluttransfusionswesen",
    "Chirotherapie / Manuelle Medizin",
    "Chirurgie",
    "Dermatologie",
    "Dermatohistologie",
    "Diabetologie",
    "Endokrinologie",
    "Ernährungsmedizin",
    "Flugmedizin",
    "Gastroenterologie",
    "Geriatrie",
    "Gynäkologie",
    "Gynäkologische Exfoliativ-Zytologie",
    "Hals-Nasen-Ohren-Heilkunde",
    "Hämatologie",
    "Hämostaseologie",
    "Handchirurgie",
    "Homöopathie",
    "Infektiologie und Impfen",
    "Intensivmedizin",
    "Kardiologie",
    "Labordiagnostik",
    "Magnetresonanztomografie",
    "Medikamentöse Tumortherapie",
    "Medizinische Genetik",
    "Medizinische Informatik",
    "Medizintechnik",
    "Naturheilverfahren",
    "Nephrologie",
    "Neurologie",
    "Notfallmedizin",
    "Onkologie",
    "Ophtalmologie",
    "Orthopädie",
    "Orthopädische Chirurgie",
    "Osteologie",
    "Pädiatrie",
    "Palliativmedizin",
    "Phlebologie",
    "Physikalische Therapie",
    "Physikalische Therapie und Balneologie",
    "Plastische Operationen",
    "Pneumologie",
    "Proktologie",
    "Psychiatrie",
    "Psychoanalyse",
    "Psychotherapie",
    "Rare Diseases",
    "Reisemedizin",
    "Rehabilitationswesen",
    "Rheumatologie",
    "Röntgendiagnostik",
    "Schlafmedizin",
    "Sozialmedizin",
    "Schmerztherapie",
    "Sportmedizin",
    "Stimm- und Sprachstörungen",
    "Suchtmedizin",
    "Tropenmedizin",
    "Umweltmedizin",
    "Unfallchirurgie",
    "Urologie"
];
var validated = false;
var priorityCountries = ["Deutschland", "Österreich", "Schweiz"];
var toload = 2;

$(function() {
    $.post(apiPath, { cmd: "get/countries" }, function(data) {
        var json = tryParseJSON(data);
        if (json && json.success) {
            json.data.sort(function(a, b) {
                for (var i = 0; i < priorityCountries.length; i++) {
                    if (a.val == priorityCountries[i]) {
                        return -1;
                    }
                    if (b.val == priorityCountries[i]) {
                        return 1;
                    }
                }
                return a.val.localeCompare(b.val);
            });
            if (typeof search_selects !== "undefined") {
                search_selects["country_id"] = json.data;
            }
            json.data.forEach(function(el) {
                $("#formhandler-country").append("<option value='" + el.id + "'>" + el.val + "</option>\n");
            });
            toload--;
            $("#formhandler-country").val("");
        } else {
            console.log("loading countries failed: ", json);
        }
    });
    $.post(apiPath, { cmd: "get/jobs" }, function(data) {
        var json = tryParseJSON(data);
        if (json && json.success) {
            json.data.sort(function(a, b) { return a.val.localeCompare(b.val); });
            if (typeof search_selects !== "undefined") {
                search_selects["customer_group_id"] = json.data;
            }
            json.data.forEach(function(el) {
                if (el.val) {
                    $("#formhandler-job").append("<option value='" + el.id + "'>" + el.val + "</option>\n");
                }
            });
            toload--;
            $("#formhandler-job").val("");
            $("#formhandler-job").change(updateSelects);
        } else {
            console.log("loading jobs failed: ", json);
        }
    });
    $("#formhandler-birthday").datepicker({
        startView: "years",
        autoclose: true,
        format: "dd.mm.yyyy"
    });
    var sel = $("form input[required][type=text], form input[required][type=password], form input[required][type=email]");
    sel.on("invalid", function() {
        this.setCustomValidity("");
        if (!this.validity.valid) {
            this.setCustomValidity("Pflichtfeld. Bitte noch ausfüllen.");
        }
    });
    sel.on("input change", function() {
        this.setCustomValidity("");
    });
    $("form[validate]").find("[type=submit]").click(function() {
        $("form[validate]").attr("validated", "");
    });
});