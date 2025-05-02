<?php
/**
 * The default reminder e-mail text
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
$email_reminder_default_text = <<<EMAIL
Hallo <<Benutzername>>,

nochmal Dank für Ihre Buchung! Wir wollten Sie nur nochmal kurz an Ihre Buchung erinnern. Sie haben sich für folgenden Termin angemeldet:

<<Termine>>

Viel Spaß und Entspannung - Ihr neckAttack Team

PS: Ihre Buchung ist verbindlich. Falls Sie aus wichtigen Gründen Ihren Termin nicht einhalten können, verwenden Sie diesen Link:

<<StornierenLink>>

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

$email_reminder_default_text = htmlspecialchars(trim($email_reminder_default_text));
?>