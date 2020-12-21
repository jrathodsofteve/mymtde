<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-gender" class="inline">
            Anrede <?= !empty($_GET["mode"]) ? "" : "<span>*</span>" ?>
        </label>
    </div>
    <div class="medium-9 columns">
        <select id="formhandler-gender" name="data[gender]" <?= !empty($_GET["mode"]) ? "" : "required" ?> not-disabled>
            <option value="" selected disabled> Bitte wählen </option>
            <option value="w">Frau</option>
            <option value="m">Herr</option>
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-title_prefix" class="inline">
            Titel (Präfix)
        </label>
    </div>
    <div class="medium-9 columns">
        <select id="formhandler-title_prefix" name="data[title_prefix]" not-disabled>
            <option value="" selected> --- </option>
            <option>Assoc. Prof.</option>
            <option>Dipl. med.</option>
            <option>Dipl.-Psych.</option>
            <option>Dr.</option>
            <option>Dr. h. c.</option>
            <option>Dr. med.</option>
            <option>Dr. mult.</option>
            <option>Drs.</option>
            <option>Dr. Dr.</option>
            <option>Dr. Dr. med.</option>
            <option>Dr. med. dent.</option>
            <option>Dr. rer. nat.</option>
            <option>Dipl. Ing.</option>
            <option>Mag.</option>
            <option>MBA</option>
            <option>Ph.D.</option>
            <option>Primar</option>
            <option>Prof.</option>
            <option>Prof. Dr. </option>
            <option>Prof. Dr. h. c.</option>
            <option>Prof. Dr. med.</option>
            <option>Prof. Dr. mult.</option>
            <option>Prof. Dr. Dr.</option>
            <option>Prof. Dr. Dr. h.c.</option>
            <option>Prof. Dr. Dr. h.c. mult.</option>
            <option>Prof. Dr. Dr. med.</option>
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-title_suffix" class="inline">
            Titel (Suffix)
        </label>
    </div>
    <div class="medium-9 columns">
        <select id="formhandler-title_suffix" name="data[title_suffix]" not-disabled>
            <option value="" selected> --- </option>
            <option>MD</option>
            <option>Phd</option>
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-firstname" class="inline">
            Vorname <span>*</span>
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihren Vornamen ein" pattern="^.{2,}$" required name="data[firstname]" id="formhandler-firstname" class="required" type="text" not-disabled>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-lastname" class="inline">
            Nachname <span>*</span>
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihren Nachnamen ein" pattern="^.{2,}$" required name="data[lastname]" id="formhandler-lastname" class="required" type="text" not-disabled>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-address" class="inline">
            Straße & Hausnr. <?= !empty($_GET["mode"]) ? "" : "<span>*</span>" ?>
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihre Adresse ein" pattern="^.{2,}$" <?= !empty($_GET["mode"]) ? "" : "required" ?> name="data[address]" id="formhandler-address" type="text" not-disabled>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-postcode" class="inline">
            PLZ <?= !empty($_GET["mode"]) ? "" : "<span>*</span>" ?>
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihre PLZ ein" <?= !empty($_GET["mode"]) ? "" : "required" ?> name="data[postcode]" id="formhandler-postcode" type="text" not-disabled>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-city" class="inline">
            Stadt <?= !empty($_GET["mode"]) ? "" : "<span>*</span>" ?>
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihre Stadt ein" pattern="^.{2,}$" <?= !empty($_GET["mode"]) ? "" : "required" ?> name="data[city]" id="formhandler-city" type="text" not-disabled>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-country" class="inline">
            Land
        </label>
    </div>
    <div class="medium-9 columns">
        <select id="formhandler-country" name="data[country]" not-disabled>
            <option value="" selected disabled> Bitte wählen </option>
        </select>
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-telephone" class="inline">
            Telefonnummer
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihre Telefonnummer ein" name="data[telephone]" id="formhandler-telephone" type="text" not-disabled> <!-- pattern="^(\+|0)[\d ]{4,14}$" -->
    </div>
</div>
<div class="row form-group">
    <div class="medium-3 columns">
        <label for="formhandler-birthday" class="inline">
            Geburtsdatum
        </label>
    </div>
    <div class="medium-9 columns">
        <input title="Geben Sie hier Ihr Geburtsdatum ein" name="data[birthday]" id="formhandler-birthday" type="text" readonly not-disabled>
    </div>
</div>
<div id="job-choices">
    <div class="row form-group">
        <div class="medium-3 columns">
            <label for="formhandler-job" class="inline">
                Beruf <span>*</span>
            </label>
        </div>
        <div class="medium-9 columns">
            <select id="formhandler-job" class="required" key="main" name="data[job]" required not-disabled>
                <option value="" selected disabled> Bitte wählen </option>
            </select>
        </div>
    </div>
</div>
