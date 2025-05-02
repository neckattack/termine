<?php
/**
 * The default therapist_list e-mail text
 * Uses the "heredoc" syntax
 * http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 *
 * Possible variables:
 * <<Therapist>>
 * <<Kunde>>
 * <<Termine>>
 */
$email_therapist_list_default_text = <<<EMAIL
Hallo <<Therapist>>,

Für Deinen Termin morgen bei "<<Kunde>>" haben wir Dir die Anmeldeliste angehangen.

<<Termine>>

Viel Spass und danke - Dein neckattack Team

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

$email_therapist_list_default_text = htmlspecialchars(trim($email_therapist_list_default_text));
?>