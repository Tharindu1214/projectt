<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$str='<table style="border:1px solid #ddd; border-collapse:collapse; padding:20px 0 30px;" cellspacing="0" cellpadding="0" border="0">
	<tbody>
		<tr>
			<td style="padding:10px;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="153">Request Comments</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" width="620">'.CommonHelper::renderHtml($brandRequestComments).'</td>
		</tr>
	</tbody></table>';
echo $str;