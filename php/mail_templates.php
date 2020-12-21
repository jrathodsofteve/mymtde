<?php

// mail signatur
define("MAIL_SIGNATURE", <<<HTML
<br><br><br>
<small style="color:#aaa">
<b>Medical Tribune Verlagsgesellschaft mbH,</b><br>
Unter den Eichen 5, 65195 Wiesbaden, Telefon 0611 9746-0,<br>
<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a><br>
<a href="http://www.medical-tribune.de">www.medical-tribune.de</a><br>
Registergericht Amtsgericht Wiesbaden, HRB 12808, Umsatzsteueridentifikationsnummer<br>
DE206862684, Geschäftsführer: Alexander Paasch, Dr. Karl Ulrich.
</small>
HTML
);

// mail body format string
define("MAIL_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
vielen Dank, dass Sie sich auf www.<span>medical-tribune</span>.de registriert haben. Um Ihre Registrierung abzuschließen, klicken Sie bitte auf den nachfolgenden Aktivierungslink:
<br><br>
<a href="%s/php/confirm.php?type=register&token=%s">Hier klicken: Registrierung abschließen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/php/confirm.php?type=register&token=%s
<br><br>
Hiermit bestätigen Sie außerdem, folgende kostenlose und jederzeit abbestellbare Newsletter abonniert zu haben:
<br><br>
%s
<br><br>
Falls Sie sich nicht bei Medical Tribune registriert haben, ignorieren Sie diese Mail einfach.
HTML
. MAIL_SIGNATURE
);

// first update template
define("STATUS2_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
vielen Dank, dass Sie auf www.<span>medical-tribune</span>.de Ihre Nutzerdaten aktualisiert haben. Um den Prozess abzuschließen, klicken Sie bitte auf den nachfolgenden Bestätigungslink:
<br><br>
<a href="%s/php/confirm.php?type=register&token=%s">Hier klicken: Aktualisierungsprozess abschließen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/php/confirm.php?type=register&token=%s
<br><br>
Hiermit bestätigen Sie außerdem, folgende kostenlose und jederzeit abbestellbare Newsletter abonniert zu haben:
<br><br>
%s
<br><br>
Falls Sie sich nicht bei Medical Tribune registriert haben, ignorieren Sie diese Mail einfach.
<br><br>
Bei Fragen stehen wir Ihnen gerne unter <a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a> zur Verfügung. 
<br><br>
Mit besten Grüßen aus Wiesbaden
<br>
Ihr MT-Team
HTML
. MAIL_SIGNATURE
);

// newsletter mail format string
define("NEWSLETTER_MAIL_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
um Ihre neuen Newsletter-Abonnements zu bestätigen, klicken Sie bitte auf folgenden Link:
<br><br>
<a href="%s/php/confirm.php?type=newsletters&token=%s">Hier klicken: Neue Newsletter bestätigen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/php/confirm.php?type=newsletters&token=%s
<br><br>
%s
<br><br>
Falls Sie diese E-Mail versehentlich erhalten haben, ignorieren Sie diese einfach.
HTML
. MAIL_SIGNATURE
);

// email change mail format string
define("CHANGE_MAIL_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
um Ihre neue E-Mail-Adresse bei Medical Tribune zu bestätigen, klicken Sie bitte auf folgenden Link:
<br><br>
<a href="%s/php/confirm.php?type=email&token=%s">Hier klicken: Neue E-Mail-Adresse bestätigen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile ihres Browsers:
<br><br>
%s/php/confirm.php?type=email&token=%s
<br><br>
Wenn Sie Ihre E-Mail-Adresse doch nicht ändern möchten oder diese E-Mail versehentlich erhalten haben, ignorieren Sie die E-Mail einfach.
HTML
. MAIL_SIGNATURE
);

// email change mail format string 2
// define("MAIL_CHANGED_TEMPLATE", <<<HTML
// Sehr geehrte%s %s %s,
// <br><br>
// ihre E-Mail-Adresse bei Medical Tribune wurde zu %s geändert. Falls Sie dies veranlasst haben, bestätigen Sie bitte Ihre neue E-Mail-Adresse.
// <br><br>
// Falls Sie dies nicht veranlasst haben, könnte jemand die Kontrolle über Ihr Konto übernommen haben. Ändern sie schnellstmöglich ihr Passwort:
// <br><br>
// <a href="%s/profile.php">Zum Profil</a>
// HTML
// );

define("REGISTER_LINK_MAIL_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Vielen Dank, dass Sie Ihre Profilinformationen erneuern. Um Ihr kostenloses Nutzerkonto bei medical-tribune.de zu überprüfen und ein neues Passwort festzulegen, rufen Sie bitte folgenden Link auf:
<br><br>
<a href="%s/new-profile.php?mode=1&token=%s">Hier klicken: Profil überprüfen und Passwort ändern</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/new-profile.php?mode=1&token=%s
<br><br>
Wir haben unsere Webseite neugestaltet und viele Funktionen verbessert. Dies beinhaltet auch den bisherigen Login-Bereich. 
Leider können wir Ihr bisheriges Passwort aus datenschutzrechtlichen Gründen nicht übernehmen. 
Daher möchten wir Sie bitten, kurz Ihre Profildaten zu überprüfen und sich einfach ein neues Passwort (es kann auch wieder ihr altes sein) anzulegen.
Bei Fragen stehen wir Ihnen gerne unter <a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a> zur Verfügung.
Wir wünschen Ihnen weiterhin viele Freude beim Lesen der Medical Tribune.
<br><br>
Besten Dank
<br>
Ihr MT-Team
HTML
. MAIL_SIGNATURE
);

define("DELETE_ACCOUNT_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
Sie haben eine Löschung Ihres Kontos bei Medical Tribune beantragt. Um Ihr Konto zu löschen, klicken Sie auf folgenden Link:
<br><br>
<a href="%s/delete.php?mail=%s">Hier klicken: Konto löschen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/delete.php?mail=%s
<br><br>
Falls Sie diese E-Mail versehentlich erhalten haben, ignorieren Sie diese einfach.
HTML
. MAIL_SIGNATURE
);

define("RESET_PASSWORD_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Sehr geehrte%s %s %s,
<br><br>
Sie haben ein Zurücksetzen Ihres Passworts bei Medical Tribune beantragt. Um ein neues Passwort anzulegen, klicken Sie bitte auf folgenden Link:
<br><br>
<a href="%s/new-password.php?token=%s">Hier klicken: Passwort zurücksetzen</a>
<br><br>
Oder kopieren Sie einfach folgenden Link in die Adresszeile Ihres Browsers:
<br><br>
%s/new-password.php?token=%s
<br><br>
Falls Sie diese E-Mail versehentlich erhalten haben, ignorieren Sie diese einfach.
HTML
. MAIL_SIGNATURE
);

define("NEWSLETTERS_CONFIRMED_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Newsletter erfolgreich bestätigt.
HTML
. MAIL_SIGNATURE
);

define("NEW_DOC_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Customer ID: %s<br>
Name: %s %s<br>
E-Mail: %s<br>
Adresse: %s<br>
%s
HTML
);

?>