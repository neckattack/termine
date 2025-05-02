<?php
/**
 * The default e-mail text
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
$email_default_text = <<<EMAIL
Hallo <<Benutzername>>,

vielen Dank für Ihre Buchung! Sie haben sich erfolgreich für
folgende Termine angemeldet:

<<Termine>>

Viel Spaß und Entspannung - Ihr neckAttack Team

PS: Ihre Buchung ist verbindlich. Falls Sie aus wichtigen Gründen
Ihren Termin nicht einhalten können, schreiben Sie uns eine kurze eMail an termine@neckattack.net

Um die Buchung zu ändern oder zu stornieren, verwenden Sie den Link:
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

$email_default_text = htmlspecialchars(trim($email_default_text));
?>