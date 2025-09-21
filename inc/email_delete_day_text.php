<?php
/**
 * The default delete day e-mail text
 * Uses the "heredoc" syntax
 * http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 *
 * Possible variables:
 * <<Benutzername>>
 * <<Termine>>
 * <<Ansprechpartner>>
 * <<Telefonnummer>>
 * <<StornierenLink>>
 */
$email_delete_day_default_text = <<<EMAIL
Hallo <<Benutzername>>,

es tut uns sehr leid aber Ihr Termin am 

<<Termine>> 

müssen wir hiermit leider absagen. Ihr Masseur kommt an diesem Tag nicht in Ihre Firma.

Bitte entschuldigen Sie die Umstände und schreiben uns, wenn Sie schon gezahlte Beträge zurückerstattet haben möchten.

Bis hoffentlich zum nächsten mal,
Liebe Grüße - Ihr neckattack Team

--------------
neckAttack Ltd.
Landhausstr. 90
D-70190 Stuttgart
Fon: +49 711/3 58 36 09
Fax: +49 711/3 58 36 11
Web: www.neckattack.net

Sitz der Gesellschaft: Stuttgart
Registergericht: Amtsgericht Stuttgart, HRB 25076
USt-IdNr. DE 239 255 541
Geschäftsführer: Dipl.-Kfm. Chris Walther 
EMAIL;

$email_delete_day_default_text = htmlspecialchars(trim($email_delete_day_default_text));
?>