<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
function render_email_template($title, $bodyHtml) {
    $title = (string)$title;
    $body  = (string)$bodyHtml;
    return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"/>'
         . '<meta name="viewport" content="width=device-width,initial-scale=1"/>'
         . '<title>'.htmlspecialchars($title).'</title>'
         . '</head><body style="margin:0;padding:0;background:#f7f7f7;font-family:Arial,Helvetica,sans-serif;color:#222;">'
         . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f7f7;">'
         . '<tr><td align="center" style="padding:24px 12px;">'
         . '<table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #e6e6e6;border-radius:6px;overflow:hidden;">'
         . '<tr><td style="background:#fffbdd;border-bottom:1px solid #f0e6a6;padding:12px 16px;font-weight:bold;color:#333;">neckAttack</td></tr>'
         . '<tr><td style="padding:20px 16px;line-height:1.6;font-size:14px;">'.$body.'</td></tr>'
         . '<tr><td style="padding:12px 16px;border-top:1px solid #eee;font-size:12px;color:#666;">'
         . 'Diese E‑Mail wurde automatisch versendet. Antworten Sie gerne direkt auf diese Nachricht.</td></tr>'
         . '</table>'
         . '</td></tr></table>'
         . '</body></html>';
}
