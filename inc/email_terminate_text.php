<?php
/**
 * The default terminate e-mail text
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
$email_terminate_default_text = <<<EMAIL
Hallo <<Benutzername>>,

Vielen Dank für die Aktualisierung Ihrer Reservierung! 
Aktualisierte Liste der Buchungen:

<<Termine>>

Viel Spaß und Entspannung - Ihr neckAttack Team


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

$email_terminate_default_text = htmlspecialchars(trim($email_terminate_default_text));
?>