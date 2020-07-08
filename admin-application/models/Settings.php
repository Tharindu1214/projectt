<?php 
class Settings
{
    public function __construct() 
    {
        $this->db = FatApp::getDb();
    }
    
    public function restoreDatabase($backupFile, $concate_path = true)
    {
        $db_server = CONF_DB_SERVER;
        $db_user = CONF_DB_USER;
        $db_password = CONF_DB_PASS;
        $db_databasename = CONF_DB_NAME;
        $conf_db_path = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
        if ($concate_path == true) {
            $backupFile = $conf_db_path . $backupFile;
        }
        /* die($backupFile); */
        $sql = "SHOW TABLES FROM $db_databasename";
        if ($rs = $this->db->query($sql)) {
            while ($row = $this->db->fetch($rs)) {
                $table_name = $row["Tables_in_" . $db_databasename];
                $this->db->query("DROP TABLE $db_databasename.$table_name");
            }
        }
        $cmd = "mysql --user=" . $db_user . " --password='" . $db_password . "' " . $db_databasename . " < " . $backupFile;
        system($cmd);
    }

    public function getDatabaseDirectoryFiles()
    {
        $dir = dir(CONF_DB_BACKUP_DIRECTORY_FULL_PATH);
        $files_arr = array();
        $count = 0;
        while (($file = $dir->read()) !== false) {
            if (!($file == "." || $file == ".." || $file == ".htaccess")) {
                $files_arr[] = $file;
            }
        }
        return $files_arr;
    }

    public function backupDatabase($name, $attachtime = true, $download = false, $backup_path = "")
    {
        set_time_limit(0);
        $db_server = CONF_DB_SERVER;
        $db_user = CONF_DB_USER;
        $db_password = CONF_DB_PASS;
        $db_databasename = CONF_DB_NAME;
        $conf_db_path = $backup_path != "" ? $backup_path : CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
        if ($attachtime) {
            $backupFile = $conf_db_path . "/" . $name . "_" . date("Y-m-d-H-i-s") . '.sql';
            $fileToDownload = $name . "_" . date("Y-m-d-H-i-s") . '.sql';
        } else {
            $backupFile = $conf_db_path . "/" . $name . '.sql';
            $fileToDownload = $name . '.sql';
        }
        $data_str = "mysqldump --opt --host=" . $db_server . " --user=" . $db_user . " --password=" . $db_password . " " . $db_databasename . " > " . $backupFile;
        $create_backup = system($data_str);
        if ($download) {
            $this->download_file($fileToDownload);
        } 
        return true;
    }

    public function download_file($file)
    {
        ini_set('memory_limit', '100M'); 
        set_time_limit(0);
        $download_dir = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
        $path = $download_dir . $file;
        if (!file_exists($path)) {
            Message::addErrorMessage(Labels::getLabel('LBL_The_file_is_not_available_for_download.', CommonHelper::getLangId()));
            return false;
        }
        $filename = $download_dir . "/" . $file;
        header('Content-Description: File Transfer');
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        return true;
    }
    
    public function recurse_zip($src, &$zip, $path_length)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_zip($src . '/' . $file, $zip, $path_length);
                } else {
                    $zip->addFile($src . '/' . $file, substr($src . '/' . $file, $path_length));
                }
            }
        }
        closedir($dir);
    }

    public function compress($src, $destination)
    {
        if (substr($src, -1) === '/') {
            $src = substr($src, 0, -1);
        }
        $arr_src = explode('/', $src);
        $filename = end($arr_src);
        unset($arr_src[count($arr_src) - 1]);
        $path_length = strlen(implode('/', $arr_src) . '/');
        $f = explode('.', $filename);
        $filename = $f[0];
        $filename = (($filename == '') ? $destination . date("d-m-y H-i-s") . '.zip' : $destination . $filename . '.zip');
        $zip = new ZipArchive;
        $res = $zip->open($filename, ZipArchive::CREATE);
        if ($res !== true) {
            echo Labels::getLabel('LBL_Unable_to_create_zip_file', CommonHelper::getLangId());
            exit;
        }
        if (is_file($src)) {
            $zip->addFile($src, substr($src, $path_length));
        } else {
            if (!is_dir($src)) {
                $zip->close();
                @unlink($filename);
                echo Labels::getLabel('LBL_File_not_found', CommonHelper::getLangId());
                exit;
            }
            $this->recurse_zip($src, $zip, $path_length);
        }
        $zip->close();
        return true;
    }

    public function findandDeleteOldestFile($directory)
    {
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                $files[] = $file;
            }

            foreach ($files as $file) {
                if (is_file($directory . '/' . $file)) {
                    $file_date[$file] = filemtime($directory . '/' . $file);
                }
            }
        }
        closedir($handle);
        if(isset($file_date)) {
            asort($file_date, SORT_NUMERIC);
            reset($file_date);
            $oldest = key($file_date);
            if (count($file_date) > 3) {
                return @unlink($directory . '/' . $oldest);
            }
        }
    }
}
?>
