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
      <table class="list">
        <thead>
          <tr>
            <th>Kunde</th>
            <th>Datum</th>
            <th>Zeit</th>
            <th>Name</th>
            <th>E-Mail</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['client_name']) ?></td>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td><?= htmlspecialchars($r['time_start']) ?> - <?= htmlspecialchars($r['time_end']) ?></td>
            <td><?= htmlspecialchars($r['booker_name']) ?></td>
            <td><?= htmlspecialchars($r['booker_email']) ?></td>
            <td>
              <a href="<?= ABSURL ?>web/bookings.php?e=<?= md5(strtolower($r['booker_email'])) ?>" target="_blank">Buchungen verwalten</a>
              &nbsp;|&nbsp;
              <a href="<?= ABSURL ?>web/ics.php?tid=<?= (int)$r['time_id'] ?>" target="_blank">ICS</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>
