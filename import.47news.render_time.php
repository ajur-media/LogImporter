<?php

/**
 * User: Karel Wintersky <karel.wintersky@gmail.com>
 * Date: 21.07.2019, time: 9:27
 * Date: 02.12.2019, time: 5:00
 */

ini_set('memory_limit', '4G');

require __DIR__ . '/vendor/autoload.php';

use AJUR\LogEstimator\DBPool;
use AJUR\LogEstimator\ParseLogString;
use Arris\CLIConsole;
use Arris\DB;

DB::init(NULL, include '_connection_settings.php');

if ($argc < 2) {
    CLIConsole::say("<hr><br>Use {$argv[0]} <file.log> <br><br>");
    die;
}
echo PHP_EOL;

$files_list = array_slice($argv, 1);
$files_count_total = count($files_list);

$db_pool = new DBPool(DB::C(), 5000,
    'log_rendertime',
    [
        'dt'            ,
        'article_id'    ,
        'render_time'   ,
        'size_textbb'   ,
        'size_html'     ,
        'size_diff'     ,
        'url'           ,
    ]);

$logrecords_total = 0;
$files_count_current = 1;

// iterate args (all files)
foreach ($files_list as $file_name) {
    
    if (!is_file($file_name)) {
        CLIConsole::say("File `{$file_name}` not found");
        continue;
    }
    
    $strings_list = file($file_name); // read file
    $logrecords_thisfile_total = count($strings_list);
    $logrecords_thisfile = 0;

    // iterate all strings
    foreach ($strings_list as $str) {
        $data = ParseLogString::parse($str, '47news');

        if (!empty($data)) {
            $db_pool->push($data);

            $logrecords_thisfile++;
            $logrecords_total++;
            echo "[{$files_count_current}/{$files_count_total}] File: {$file_name} -- {$data['dt']} -- Rows: {$logrecords_thisfile}    /   Total: {$logrecords_thisfile_total}" . "\r";
        }
        unset($data);
        
    }
    $db_pool->commit();
    unset($strings_list);

    CLIConsole::say("<br>{$file_name} completed with <font color='yellow'>{$logrecords_thisfile}</font> rows");
    $files_count_current++;
}
$db_pool->commit();

CLIConsole::say("<br>Task completed with <font color='yellow'>{$logrecords_total}</font> rows");

// $time = round(TimerStats::stop(), 3);

// CLIConsole::say("Time consumed: <font color='yellow'>{$time}</font> sec.");



 
