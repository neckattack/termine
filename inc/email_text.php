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
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Buchungsbestätigung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
<div style="max-width: 600px; margin: 0 auto; padding: 20px;">

<p>Hallo <strong><<Benutzername>></strong>,</p>

<p>vielen Dank für Ihre Buchung! Sie haben sich erfolgreich für folgende Termine angemeldet:</p>

<div style="margin: 20px 0;">
<<Termine>>
</div>

<p>Viel Spaß und Entspannung - Ihr neckAttack Team</p>

<p style="font-size: 13px; color: #666;">
<strong>PS:</strong> Ihre Buchung ist verbindlich. Falls Sie aus wichtigen Gründen Ihren Termin nicht einhalten können, schreiben Sie uns eine kurze E-Mail an <a href="mailto:termine@neckattack.net">termine@neckattack.net</a>
</p>

<p>Um die Buchung zu ändern oder zu stornieren, verwenden Sie den Link:<br>
<<StornierenLink>></p>

<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

<div style="font-size: 12px; color: #666;">
<strong>neckAttack Ltd.</strong><br>
Landhausstr. 90<br>
D-70190 Stuttgart<br>
Fon: +49 711/3 58 36 09<br>
Fax: +49 711/3 58 36 11<br>
Web: <a href="https://www.neckattack.net">www.neckattack.net</a><br>
<br>
Sitz der Gesellschaft: Stuttgart<br>
Registergericht: Amtsgericht Stuttgart, HRB 25076<br>
USt-IdNr. DE 239 255 541<br>
Geschäftsführer: Dipl.-Kfm. Chris Walther
</div>

</div>
</body>
</html>
EMAIL;

$email_default_text = trim($email_default_text);
?>