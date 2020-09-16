<?php

/**
 * User: Karel Wintersky <karel.wintersky@gmail.com>
 * Date: 21.07.2019, time: 9:27
 * Date: 02.12.2019, time: 5:00
 */

use Arris\DB;
use Arris\TimerStats;

ini_set('memory_limit', '1G');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/class.DBPool.php';
require __DIR__ . '/class.ParseLogString.php';

use AJUR\LogEstimator\DBPool;
use AJUR\LogEstimator\ParseLogString;

TimerStats::init();
TimerStats::go();

DB::init(NULL, include '_connection_settings.php');

if ($argc < 2) {
    \Arris\CLIConsole::say("<hr><br>Use {$argv[0]} <file.log> <br><br>");
    die;
}
echo PHP_EOL;

$files_list = array_slice($argv, 1);
$files_count_total = count($files_list);

$pool_full = new DBPool(5000, 
    'log_doctorpiter_mobile',
    [
        // Summary/Legacy/isMobile/isTablet/UA
        'dt', 
        'summary',
        'nginx',
        'ismobile',
        'istablet',
        'useragent',
    ]);

$logrecords_total = 0;
$files_count_current = 1;

// iterate args (all files)
foreach ($files_list as $file_name) {

    if (!is_file($file_name)) {
        \Arris\CLIConsole::say("File `{$file_name}` not found");
        continue;
    }
    
    $strings_list = file($file_name); // read file
    $logrecords_thisfile = 0;

    // iterate all strings
    foreach ($strings_list as $str) {
        $data = ParseLogString::parse_isMobile($str);
        
        if (!empty($data)) {
            $pool_full->push($data);

            $logrecords_thisfile++;
            $logrecords_total++;
            echo "[{$files_count_current}/{$files_count_total}] File: {$file_name} -- {$data['dt']} -- Rows: {$logrecords_thisfile}    /   Total: {$logrecords_total}" . "\r";
        }
        
    }
    $pool_full->commit();

    \Arris\CLIConsole::say("<br>{$file_name} completed with <font color='yellow'>{$logrecords_thisfile}</font> rows");
    $files_count_current++;
}
$pool_full->commit();

\Arris\CLIConsole::say("<br>Task completed with <font color='yellow'>{$logrecords_total}</font> rows");

$time = round(TimerStats::stop(), 3);

\Arris\CLIConsole::say("Time consumed: <font color='yellow'>{$time}</font> sec.");



 
