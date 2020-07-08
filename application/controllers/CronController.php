<?php
class CronController extends FatController
{
    public function __construct($action)
    {
        $this->_autoCreateModel = false;
        parent::__construct($action);
    }

    public function index($id = 0)
    {
        $db = FatApp::getDb();

        $allCrons = Cron::getAllRecords(true, $id);

        foreach ($allCrons as $row) {
            $cron = new Cron($row ['cron_id']);
            $cron->loadFromDb();

            $logId = $cron->markStarted();
            if (! $logId) {
                continue;
            }

            $arr = explode('/', $row['cron_command']);

            $class = $arr[0];
            $obj = new $class();
            array_shift($arr);
            $action = $arr[0];
            array_shift($arr);

            $success = call_user_func_array(array($obj, $action), $arr);

            if ($success !== false) {
                $cron->markFinished($logId, 'Response Got: ' . $success);
            } else {
                $cron->markFinished($logId, 'Marked finished with error ');
            }
            echo 'Ended';
        }
        Cron::clearOldLog();
    }

    public function manually($cron_command = '')
    {
        $allCrons = Cron::getAllRecords(true);
        $found = false;

        foreach ($allCrons as $row) {
            if (strtolower($row['cron_command']) == strtolower('cronjob/'.$cron_command)) {
                $cron = new Cron($row ['cron_id']);
                $found = true;

                $arr = explode('/', $row['cron_command']);
                $class = $arr[0];
                $obj = new $class();
                array_shift($arr);
                $action = $arr[0];
                array_shift($arr);

                $success = call_user_func_array(array($obj, $action), $arr);

                if ($success !== false) {
                    echo 'Response Got: ' . $success;
                } else {
                    echo 'Finished with error';
                }
                echo '<br>Ended';
            }
        }

        if (!$found) {
            echo "No record found";
        }
    }
}
