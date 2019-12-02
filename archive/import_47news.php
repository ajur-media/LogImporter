<?php

/**
 * User: Karel Wintersky <karel.wintersky@gmail.com>
 * Date: 21.07.2019, time: 9:27
 */

use function Arris\DBC as DBCAlias;
use Spatie\Regex\Regex;
use Arris\DB;
use Arris\TimerStats;

ini_set('memory_limit', '1G');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/class.DBPool.php';
require __DIR__ . '/class.ParseLogString.php';

use AJUR\LogEstimator\DBPool;
use AJUR\LogEstimator\ParseLogString;

$AREA_MAPPING_47news = [
    'callback_admin'    =>  'request_admin',
    'callback_mobile'   =>  'request_mobile',
    'callback_default'  =>  'request_default',
];

TimerStats::init();
TimerStats::go();

DB::init(NULL, include '_connection_settings.php');

if ($argc < 2) {
    \Arris\CLIConsole::echo_status("<hr><br>Use {$argv[0]} <file.log> <br><br>");
    die;
}
echo PHP_EOL;

$files_list = array_slice($argv, 1);
$files_count_total = count($files_list);

$pool_full = new DBPool(5000, 'log_47news', ['version', 'dt', 'php_worktime', 'php_memory', 'mysql_worktime', 'mysql_queries', 'sitearea', 'url']);
// $pool_logs = new DBPool(3000, 'log_47news_base', ['version', 'dt', 'php_worktime', 'php_memory', 'mysql_worktime', 'mysql_queries', 'sitearea']);
// $pool_urls = new DBPool(3000, 'log_47news_urls', ['url']);

$logrecords_total = 0;
$files_count_current = 1;

// iterate args (all files)
foreach ($files_list as $file_name) {
    
    $strings_list = file($file_name); // read file
    $logrecords_thisfile = 0;

    // iterate all strings
    foreach ($strings_list as $str) {
        $data = ParseLogString::parse($str, '47news');

        if ($data) {
            if (array_key_exists($data['site_area'], $AREA_MAPPING_47news)) {
                $data['site_area'] = $AREA_MAPPING_47news[ $data['site_area'] ];
            }

            $pool_full->push($data);

            $logrecords_thisfile++;
            $logrecords_total++;
            echo "[{$files_count_current}/{$files_count_total}] File: {$file_name} -- {$data['dt']} -- Rows: {$logrecords_thisfile}    /   Total: {$logrecords_total}" . "\r";
        }
        
    }
    $pool_full->commit();

    \Arris\CLIConsole::echo_status("<br>{$file_name} completed with <font color='yellow'>{$logrecords_thisfile}</font> rows");
    $files_count_current++;
}
$pool_full->commit();

\Arris\CLIConsole::echo_status("<br>Task completed with <font color='yellow'>{$logrecords_total}</font> rows");

$time = round(TimerStats::stop(), 3);

\Arris\CLIConsole::echo_status("Time consumed: <font color='yellow'>{$time}</font> sec.");


 
