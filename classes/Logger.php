<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Logger
{

    const DIRECTORY_LOGS ='logs';
    const LOGS_FILE = 'wineac_logs.log';
    const DIRECTORY_SEPARATOR = '/';

    /**
     * log data to file
     * @param $data
     */
    public static function writeLog($data){

        $directory_path = WINEAC_DIR_PATH.self::DIRECTORY_LOGS;
        $file_path = WINEAC_DIR_PATH.self::DIRECTORY_LOGS.self::DIRECTORY_SEPARATOR.self::LOGS_FILE;

        if (!file_exists($directory_path)) {
            mkdir($directory_path, 0777, true);
        }

        if(!file_exists($file_path)){
            fopen($file_path , 'w');
        }

        if(is_array($data)) {
            $data = json_encode($data);
        }
        $file = fopen($file_path ,"a");
        fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $data);
        fclose($file);

    }
}