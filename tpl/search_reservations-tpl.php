<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
// Header
$title = "Kundenverwaltung";
require ROOT."/tpl/header-tpl.php";
?>
<div id="content">
  <h2>Reservierungen nach E-Mail suchen</h2>
  <form method="get" action="<?= $_SERVER['PHP_SELF'] ?>" style="margin:10px 0; display:flex; gap:8px;">
    <input type="text" name="email" placeholder="E-Mail-Adresse" value="<?= htmlspecialchars($email) ?>" style="padding:6px 8px; border:1px solid #999; border-radius:4px; width:280px;" />
    <button type="submit" class="btn">Suchen</button>
  </form>

  <?php if ($email !== ''): ?>
    <p><strong>Ergebnisse für:</strong> <?= htmlspecialchars($email) ?></p>
    <?php if (empty($rows)): ?>
      <p>Keine Reservierungen gefunden.</p>
    <?php else: ?>
      <style>
        .admin-card{margin:16px 0 20px;padding:14px 16px;border:1px solid #e5e5e5;border-radius:10px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.06);clear:both}
        .admin-card__row{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
        .admin-card__left{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap}
        .admin-card__date{color:#0aa;font-weight:700}
        .admin-card__name{font-size:18px;font-weight:700;margin:0}
        .admin-card__actions a{display:inline-block;margin-left:6px;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:13px}
        .admin-card__actions{white-space:nowrap;margin-left:auto}
        .tag{display:inline-block;background:#f0f0f0;color:#333;border-radius:6px;padding:3px 8px;font-size:12px}
        .primary{background:#007bff;color:#fff}
        .secondary{background:#f0f0f0;color:#333}
      </style>
      <?php foreach ($rows as $idx=>$r): ?>
        <?php 
          $dateStr = htmlspecialchars($r['date'], ENT_QUOTES, 'UTF-8');
          $timeStr = htmlspecialchars($r['time_start'].' - '.$r['time_end'], ENT_QUOTES, 'UTF-8');
          $clientName = htmlspecialchars($r['client_name'], ENT_QUOTES, 'UTF-8');
          $booker    = htmlspecialchars($r['booker_name'], ENT_QUOTES, 'UTF-8');
          $emailOut  = htmlspecialchars($r['booker_email'], ENT_QUOTES, 'UTF-8');
          $bookingsLink = ABSURL.'web/bookings.php?e='.md5(strtolower($r['booker_email']));
          $icsLink   = ABSURL.'web/ics.php?tid='.(int)$r['time_id'];
          $clientLink= 'editclient.php?cid='.(int)$r['client_id'];
        ?>
        <div class="admin-card" data-idx="<?= $idx+1 ?>">
          <div class="admin-card__row">
            <div class="admin-card__left">
              <div class="admin-card__date"><?= $dateStr ?></div>
              <div class="tag"><?= $timeStr ?></div>
              <h3 class="admin-card__name"><a href="<?= $clientLink ?>"><?= $clientName ?></a></h3>
              <div class="tag" title="Bucher"><?= $booker ?></div>
              <div class="tag" title="E-Mail"><?= $emailOut ?></div>
            </div>
            <div class="admin-card__actions">
              <a class="primary" href="<?= htmlspecialchars($bookingsLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank">Buchungen verwalten</a>
              <a class="secondary" href="<?= htmlspecialchars($icsLink, ENT_QUOTES, 'UTF-8') ?>" target="_blank">ICS</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>
