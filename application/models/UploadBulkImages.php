<?php
class UploadBulkImages extends FatModel
{
    public $bulkRoot;

    public function __construct($langId = 0)
    {
        parent::__construct();
        $this->db = FatApp::getDb();

        $this->langId = $langId;
        if (1 > $langId) {
            $this->langId = CommonHelper::getLangId();
        }

        $this->bulkRoot = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH;
    }

    public function bulkMediaFileObject($loggedUserId = 0)
    {
        $srch = AttachedFile::getSearchObject();
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'credential_user_id = afile_record_id');
        $srch->addCondition('afile_type', '=', AttachedFile::FILETYPE_BULK_IMAGES);

        if (0 < $loggedUserId) {
            $srch->addCondition('afile_record_id', '=', $loggedUserId);
        }

        $srch->addMultipleFields(
            array( 'afile_physical_path', 'afile_name', 'afile_record_id', 'credential_username', 'credential_email' )
        );

        $srch->addOrder('afile_id', 'DESC');

        return $srch;
    }

    public function deleteSingleBulkMediaDir($dirPath)
    {
        if (empty($dirPath)) {
            return Labels::getLabel('LBL_Directory_Path_is_required.', $this->langId) ;
        }

        if (!file_exists($dirPath)) {
            return Labels::getLabel('LBL_Invalid_Directory.', $this->langId) ;
        }

        $files = array_diff(scandir($dirPath), array( '..', '.' ));

        if (0 < count($files)) {
            foreach ($files as $file) {
                $filePath = $dirPath .'/'. $file;
                if (false !== strpos($dirPath, $this->bulkRoot)) {
                    if (is_dir($filePath)) {
                        $this->deleteSingleBulkMediaDir($filePath);
                    } else {
                        unlink($filePath);
                    }
                }
            }
            rmdir($dirPath);
        } else {
            if (false !== strpos($dirPath, $this->bulkRoot)) {
                rmdir($dirPath);
            }
        }

        // Remove Db row having that directory detail.
        $this->updateDb($dirPath);
        return Labels::getLabel('LBL_Directory_removed.', $this->langId) ;
    }

    public function deleteBulkUploadSubDirs($hoursBefore = '48', $dirPath = '')
    {
        if (empty($dirPath)) {
            $dirPath = $this->bulkRoot;
        } elseif ($dirPath == $this->bulkRoot) {
            return Labels::getLabel('LBL_Bulk_Images_Directory_Clean', $this->langId) ;
        }

        if (is_dir($dirPath)) {
            $directories = array_diff(scandir($dirPath), array( '..', '.' ));

            if (0 < count($directories)) {
                foreach ($directories as $dir) {
                    $dirPath = $dirPath . $dir;
                    if (is_dir($dirPath)) {
                        $dirPath .= '/';
                    }
                    return $this->deleteBulkUploadSubDirs($hoursBefore, $dirPath);
                }
            } else {
                if (false !== strpos($dirPath, $this->bulkRoot) && rmdir($dirPath)) {
                    // Remove Db row having that directory detail.
                    $this->updateDb($dirPath);

                    $removedDir = array_reverse(array_filter(explode('/', $dirPath)));
                    $dirPath = str_replace($removedDir[0].'/', '', $dirPath);
                    return $this->deleteBulkUploadSubDirs($hoursBefore, $dirPath);
                }
            }
        }

        if (is_file($dirPath)) {
            $modifiedOn = filemtime($dirPath);
            if ($modifiedOn <= strtotime('-'.$hoursBefore.' hour')) {
                if (false !== strpos($dirPath, $this->bulkRoot) && unlink($dirPath)) {
                    $dirPath = str_replace(basename($dirPath), '', $dirPath);
                    return $this->deleteBulkUploadSubDirs($hoursBefore, $dirPath);
                }
            }
        }
        return Labels::getLabel('LBL_Done!', $this->langId) ;
    }

    private function updateDb($dirPath)
    {
        $afile_physical_path = rtrim(str_replace($this->bulkRoot, '', $dirPath), '/');
        $this->db->deleteRecords(AttachedFile::DB_TBL, array(
                'smt' => 'afile_type = ? AND afile_physical_path = ?',
                'vals' => array( AttachedFile::FILETYPE_BULK_IMAGES, $afile_physical_path )
        ));
    }

    public function upload($fileName, $tmpName, $userId = 0)
    {
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileExt = strtolower($fileExt);
        if ('zip' != $fileExt) {
            $this->error = Labels::getLabel('MSG_INVALID_FILE', $this->langId);
            return false;
        }

        $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH;

        $fileHandlerObj = new AttachedFile();

        $savedFile = $fileHandlerObj->saveAttachment($tmpName, AttachedFile::FILETYPE_BULK_IMAGES, $userId, 0, $fileName);

        if (false === $savedFile) {
            $this->error = $fileHandlerObj->getError();
            return false;
        }

        if (false === $fileHandlerObj->extractZip($path . $savedFile)) {
            $this->error = Labels::getLabel('MSG_COULD_NOT_SAVE_FILE', $this->langId);
            return false;
        }

        return $savedFile;
    }

    public static function getAllFilesPath($path)
    {
        if (empty($path)) {
            return false;
        }
        $locations = [];
        if (file_exists($path)) {
            $allFiles = scandir($path);
            $files = array_diff($allFiles, array( '..', '.' ));
            foreach ($files as $fileName) {
                if (is_dir($path.'/'.$fileName)) {
                    $subLocations = static::getAllFilesPath($path.'/'.$fileName);
                    $locations = array_merge($locations, $subLocations);
                } else {
                    $locations[] = array(
                                        str_replace(CONF_UPLOADS_PATH, '', $path.'/'.$fileName),
                                        $fileName
                                    );
                }
            }
            return $locations;
        }
        die('here');
        return [];
    }
}
