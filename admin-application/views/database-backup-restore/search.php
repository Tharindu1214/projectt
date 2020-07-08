<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (count($arr_listing) > 0): ?>
<table class="table table-responsive" id="dtTable">
	<thead>
		<tr>
			<th><?php echo Labels::getLabel('LBL_Backup_File_Name',$adminLangId); ?></th>
			<th><?php echo Labels::getLabel('LBL_Database_Backup_Date',$adminLangId); ?></th>
			<th><?php echo Labels::getLabel('LBL_Action',$adminLangId); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($arr_listing as $sn => $row) { ?>
		<tr>
			<td><?php echo $row ?></td>
			<td><?php echo date("d/m/Y H:i:s", filectime(CONF_DB_BACKUP_DIRECTORY_FULL_PATH . "/" . $row)); ?></td>
			<td>
				<ul class="actions"></ul>
				<ul class="actions actions--centered">
					<li class="droplink">
						<a href="javascript:void(0)" class="button small green" title="Edit"><i class="ion-android-more-horizontal icon"></i></a>
						<div class="dropwrap">
							<ul class="linksvertical">
								<li><a href="javascript:void(0)" class="button small green" title="<?php echo Labels::getLabel('LBL_Download',$adminLangId); ?>" onclick="window.open('<?php echo CommonHelper::generateUrl('DatabaseBackupRestore', 'download', array($row)) ?>');"><?php echo Labels::getLabel('LBL_Download',$adminLangId); ?></a></li>
								<li><a href="javascript:void(0)" class="button small green" title="<?php echo Labels::getLabel('LBL_Restore',$adminLangId); ?>" onclick="restoreBackup('<?php echo $row; ?>')"><?php echo Labels::getLabel('LBL_Restore',$adminLangId); ?></a></li>
								<li><a href="javascript:void(0)" class="button small green" title="<?php echo Labels::getLabel('LBL_Delete',$adminLangId); ?>" onclick="deleteBackup('<?php echo $row; ?>')"><?php echo Labels::getLabel('LBL_Delete',$adminLangId); ?></a></li>
							</ul>
						</div>
					</li>
				</ul>
			</td>
			<?php } ?>
			<?php else: ?>
				<td colspan="3"><?php echo Labels::getLabel('LBL_No_Records_Found',$adminLangId); ?></td>
			<?php endif; ?>
		</tr>
	</tbody>
</table>