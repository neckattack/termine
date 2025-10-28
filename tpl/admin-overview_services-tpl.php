<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * Template: Übersicht GebüH-Services
 * @param $services array
 */

// Header
$title = "Serviceverwaltung";
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<ul class="menu">
			<li><a href="overview_clients.php">&raquo; Kundenverwaltung</a></li>
			<?php if ($SUPERADMIN === true) {?>
			<li><a href="overview_groups.php">&raquo; Gruppenverwaltung</a></li>
			<li><a href="overview_users.php">&raquo; Benutzerverwaltung</a></li>
			<li><a href="overview_services.php">&raquo; Serviceverwaltung</a></li>
			<?php }?>
		</ul>

		<h3>Gebührenordnung (GebüH) – Leistungen</h3>
		<table class="general-list" style="width:100%; border-collapse:collapse;">
			<thead>
				<tr>
					<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">Nr.</th>
					<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">Leistung</th>
					<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">Kurzbeschreibung</th>
					<th style="text-align:right; padding:6px; border-bottom:1px solid #ccc;">Gebührenrahmen</th>
					<th style="text-align:center; padding:6px; border-bottom:1px solid #ccc;">Analog</th>
				</tr>
			</thead>
			<tbody>
				<?php if (is_array($services) && count($services) > 0) {?>
				<?php foreach ($services as $s) {?>
				<tr>
					<td style="padding:6px; border-bottom:1px solid #eee;"><?=htmlspecialchars($s['code'])?></td>
					<td style="padding:6px; border-bottom:1px solid #eee;"><?=htmlspecialchars($s['title'])?></td>
					<td style="padding:6px; border-bottom:1px solid #eee;"><?=htmlspecialchars($s['short_desc'])?></td>
					<td style="padding:6px; border-bottom:1px solid #eee; text-align:right;">
						<?php
							$min = isset($s['fee_min']) ? number_format((float)$s['fee_min'], 2, ',', '.') : '';
							$mid = isset($s['fee_mid']) ? number_format((float)$s['fee_mid'], 2, ',', '.') : '';
							$max = isset($s['fee_max']) ? number_format((float)$s['fee_max'], 2, ',', '.') : '';
							$parts = array_filter([$min, $mid, $max], function($v){ return $v !== ''; });
							echo implode(' – ', $parts);
						?>
					</td>
					<td style="padding:6px; border-bottom:1px solid #eee; text-align:center;">
						<?= ($s['is_analog'] ? 'ja'.($s['analog_ref']? ' ('.htmlspecialchars($s['analog_ref']).')':'') : 'nein') ?>
					</td>
				</tr>
				<?php }?>
				<?php } else {?>
				<tr><td colspan="5" style="padding:8px; color:#666;">Keine Einträge vorhanden.</td></tr>
				<?php }?>
			</tbody>
		</table>
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  
