<?php
/* This should be present in the last of restore db file
INSERT INTO `tbl_configurations` (`conf_name`, `conf_val`, `conf_common`) VALUES  ('CONF_RESTORED_SUCCESSFULLY', '1', 0) ;
*/
class RestoreSystemController extends MyAppController
{
    const CONF_FILE = 'public/settings.php';
    const BACKUP_FILE = CONF_INSTALLATION_PATH."restore/database/db.sql";
    const DATABASE_FIRST = CONF_RESTORE_DB_INSTANCE_1;
    const DATABASE_SECOND = CONF_RESTORE_DB_INSTANCE_2;
    const RESTORE_TIME_INTERVAL_HOURS = 4;

    public function index()
    {
        if (!CommonHelper::demoUrl()) {
            Message::addMessage('Restore process is only valid for Demo urls!');
            FatUtility::dieJsonSuccess(Message::getHtml());
        }

        if (!FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage('Auto restore disabled by admin!');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $dateTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +'.static::RESTORE_TIME_INTERVAL_HOURS.' hours'));
        $restoreTime = FatApp::getConfig('CONF_RESTORE_SCHEDULE_TIME', FatUtility::VAR_STRING, $dateTime);

        if (strtotime($restoreTime) >= strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +1 min')))) {
            // $this->resetRestoreTime(CONF_DB_NAME);
            Message::addErrorMessage('Auto restore scheduled on '.$restoreTime);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$this->isRestoredSuccessfully()) {
            $this->resetRestoreTime(CONF_DB_NAME);

            $anotherDbName = $this->getAnotherDbName();
            $this->restoreDatabase($anotherDbName);
            //$this->resetRestoreTime($anotherDbName);

            Message::addMessage('System unable to process the request and re-scheduled the restore process!');
            FatUtility::dieJsonSuccess(Message::getHtml());
        }

        $this->createRestoreProcessFile();

        $anotherDbName = $this->getAnotherDbName();
        $this->writeSettings(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, $anotherDbName);

        $this->resetRestoreTime();

        $this->resetUserUploads();

        $this->restoreDatabase(CONF_DB_NAME);

        $this->unlinkRestoreProcessFile();

        Message::addMessage('Restored Successfully!');
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function customMessage()
    {
        $this->set('restoreInverval', static::RESTORE_TIME_INTERVAL_HOURS);
        echo $this->_template->render(false, false, 'restore-system/custom-message.php', true);
        exit;
    }

    private function createRestoreProcessFile()
    {
        $f = fopen(CONF_UPLOADS_PATH.'database-restore-progress.txt', 'w');
        $rs = fwrite($f, time());
        fclose($f);
    }

    private function unlinkRestoreProcessFile()
    {
        @unlink(CONF_UPLOADS_PATH.'database-restore-progress.txt');
    }

    private function getAnotherDbName()
    {
        return (CONF_DB_NAME == static::DATABASE_FIRST)?static::DATABASE_SECOND:static::DATABASE_FIRST;
    }

    private function isRestoredSuccessfully()
    {
        $databasename = (CONF_DB_NAME == static::DATABASE_FIRST)?static::DATABASE_SECOND:static::DATABASE_FIRST;

        $mysqli = new mysqli(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, $databasename);

        $sql = "SELECT * FROM `tbl_configurations` WHERE `conf_name` = 'CONF_RESTORED_SUCCESSFULLY'";
        $rs = $mysqli->query($sql);
        if (!$rs) {
            return false;
        }

        $row = $rs->fetch_assoc();
        if (!empty($row) && $row['conf_val'] > 0) {
            return true;
        }
        return false;
    }

    private function resetUserUploads()
    {
        $source = CONF_INSTALLATION_PATH."restore/user-uploads";
        $target = CONF_UPLOADS_PATH;
        $this->fullCopy($source, $target);
    }

    private function resetRestoreTime($databasename = '')
    {
        if (empty($databasename)) {
            $databasename = (CONF_DB_NAME == static::DATABASE_FIRST)?static::DATABASE_SECOND:static::DATABASE_FIRST;
        }

        $mysqli = new mysqli(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, $databasename);
        $date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' +'.static::RESTORE_TIME_INTERVAL_HOURS.' hours'));
        $sql = "UPDATE `tbl_configurations` set `conf_val` = '".$date."' where `conf_name` = 'CONF_RESTORE_SCHEDULE_TIME'";
        $mysqli->query($sql);        
    }

    private function restoreDatabase($databasename)
    {
        if (empty($databasename)) {
            return false;
        }
        $backupFile = static::BACKUP_FILE;
        $dbServer = CONF_DB_SERVER;
        $dbUser = CONF_DB_USER;
        $dbPassword = CONF_DB_PASS;

        $mysqli = new mysqli($dbServer, $dbUser, $dbPassword, $databasename);

        $sql = "SHOW TABLES FROM $databasename";
        if ($rs = $mysqli->query($sql)) {
            while ($row = $rs->fetch_array()) {
                $tableName=$row["Tables_in_".$databasename];
                $mysqli->query("DROP TABLE $databasename.$tableName");
            }
        }
        $cmd ="mysql --user=" . $dbUser . " --password='" . $dbPassword . "' " . $databasename . " < " . $backupFile;
        exec($cmd . " > /dev/null &");
    }

    private function writeSettings($hostName, $userName, $password, $database)
    {
        $admin = 'admin/';
        $settings_file = CONF_INSTALLATION_PATH . static::CONF_FILE;

        $output  = '<?php' . "\n";
        $output .= '// DB' . "\n";
        $output .= 'define(\'CONF_WEBROOT_FRONTEND\', \'' . addslashes(CONF_WEBROOT_URL) . '\');' . "\n";
        $output .= 'define(\'CONF_WEBROOT_BACKEND\', \'' . addslashes(CONF_WEBROOT_URL) .$admin. '\');' . "\n";
        $output .= 'define(\'CONF_DB_SERVER\', \'' . addslashes($hostName) . '\');' . "\n";
        $output .= 'define(\'CONF_DB_USER\', \'' . addslashes($userName) . '\');' . "\n";
        $output .= 'define(\'CONF_DB_PASS\', \'' . addslashes(html_entity_decode($password, ENT_QUOTES, 'UTF-8')) . '\');' . "\n";
        $output .= 'define(\'CONF_DB_NAME\', \'' . addslashes($database) . '\');';
        $file = fopen($settings_file, 'w');
        fwrite($file, $output);
        fclose($file);
    }

    private function recursiveDelete($str)
    {
        if (is_file($str)) {
            return @unlink($str);
        } elseif (is_dir($str)) {
            $scan = glob(rtrim($str, '/').'/*');
            foreach ($scan as $index => $path) {
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }

    private function fullCopy($source, $target, $empty_first = true)
    {
        if ($empty_first) {
            $this->recursiveDelete($target);
        }

        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }
}
