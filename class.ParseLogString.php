<?php

namespace AJUR\LogEstimator;

use Spatie\Regex\Regex;

/**
 * User: Arris
 *
 * Class ParseLogString
 *
 * Date: 02.12.2019, time: 4:58
 */
/**
 * Class ParseLogString
 */
class ParseLogString
{
    private static $source = '';
    private static $area_mapping = [];

    /**
     * @param string $string
     * @param string $source
     * @return array|bool
     * @throws \Spatie\Regex\RegexFailed
     */
    public static function parse(string $string, string $source)
    {
        self::$source = $source;

        if (strpos($string, ': Usage:') !== false) {
            return self::parse_v1($string);
        }

        if (strpos($string, ': Usage [') !== false) {
            return self::parse_v2($string);
        }
        
        if (strpos($string, 'Metrics:') !== false) {
            return self::parse_v3($string);
        }

        return false;
    }

    /**
     * @param string $string
     * @param string $source
     * @return array|bool
     * @throws \Spatie\Regex\RegexFailed
     */
    private static function parse_v1(string $string)
    {
        $MASK_v1 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] ' . self::$source . '\.[\d\w]+.NOTICE: Usage:\s+\[(\d{1,4}\.\d{1,3}),(\d+),\"(\w+)\",\"(.*)\"\]\s+\[\]/';

        $match = Regex::match($MASK_v1, $string);

        if ($match->hasMatch()) {
            $site_area = $match->group(4);

            return [
                'version'       =>  1,
                'dt'            =>  $match->group(1),
                'php_worktime'  =>  $match->group(2),
                'php_memory'    =>  $match->group(3),
                'mysql_worktime'    =>  NULL,
                'mysql_queries'     =>  NULL,
                'site_area'     =>  $site_area,
                'url'           =>  $match->group(5)
            ];
        }
        return false;
    }

    /**
     * @param string $string
     * @param string $source
     * @return array|bool
     * @throws \Spatie\Regex\RegexFailed
     */
    private static function parse_v2(string $string)
    {
        $MASK_v2 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] ' . self::$source . '\.[\d\w]+.NOTICE: Usage \["\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}","Time",(\d{1,4}\.\d{1,3}),"Memory",(\d+),"MySQL",\["Time",(\d{1,5}\.\d{1,3}),"Queries",(\d+)\],"(\w+)","(.*)"\] \[\]/';

        //0     "217.66.154.252",        
        //1     "Time",
        //2     0.547,
        //3     "Memory",
        //4     2078832,
        //5     "MySQL",
        //6     ["Time",0.022,"Queries",20],
        //7     "site_default",
        //8     "doctorpiter.ru/articles/22200/"

        $match = Regex::match($MASK_v2, $string);

        if ($match->hasMatch()) {
            $site_area = $match->group(4);

            if (array_key_exists($site_area, self::$area_mapping)) {
                $site_area = self::$area_mapping[ $site_area ];
            }

            return [
                'version'       =>  2,
                'dt'            =>  $match->group(1),
                'php_worktime'  =>  $match->group(2),
                'php_memory'    =>  $match->group(3),
                'mysql_worktime'=>  $match->group(4),
                'mysql_queries' =>  $match->group(5),
                'site_area'     =>  $site_area,
                'url'           =>  $match->group(7)
            ];
        }
        return false;
    }

    /**
     * @param string $string
     * @return string
     * @throws \Spatie\Regex\RegexFailed
     */
    private static function parse_v3(string $string):array 
    {
        $MASK_v3 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] DoctorPiter\.[\d\w]+.NOTICE: Metrics: (\{\S*})/';
        $match = Regex::match($MASK_v3, $string);
        if ($match->hasMatch()) {
            $dt = $match->group(1);
            $set = $match->group(2);
            $parsed = json_decode($match->group(2), true);
            
            $dataset = [
                'dt'            =>  $dt,
                'memory_usage'  =>  $parsed['memory.usage'],
                'memory_peak'   =>  $parsed['memory.peak'],
                'mysql_query_count' =>  $parsed['mysql.query_count'],
                'mysql_query_time'  =>  $parsed['mysql.query_time'],
                'time_total'        =>  $parsed['time.total'],
                'site_routed'       =>  $parsed['site.routed'],
                'site_url'          =>  $parsed['site.url']
            ];
            
            return $dataset;
        }

        return [];
    }

    /**
     * Парсит декект мобильности:
     * [2019-11-20 17:44:51] DoctorPiter.a259d613c78a96f4.DEBUG: Summary/GET_FLAG/Legacy/isMobile/isTablet/UA:  [false,false,"000",false,false,"Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)"] []
     * 
     * @param string $string
     * @return array
     * @throws \Spatie\Regex\RegexFailed
     */
    public static function parse_isMobile(string $string):array
    {
        $MASK = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\sDoctorPiter\.[\d\w]+\.DEBUG:\sSummary\/GET_FLAG\/Legacy\/isMobile\/isTablet\/UA:\s\s\[(\w{4,5}),(\w{4,5}),"(\d{3})",(\w{4,5}),(\w{4,5}),"(.+)"\]/';

        $match = Regex::match($MASK, $string);
        
        if ($match->hasMatch()) {
            $dataset = [
                'dt'        =>  $match->group(1),
                'summary'   =>  (bool)$match->group(2),
                'nginx'     =>  $match->group(4),
                'ismobile'  =>  (bool)$match->group(5),
                'istablet'  =>  (bool)$match->group(6),
                'useragent' =>  $match->group(7),
            ];
            return $dataset;
        }
        return [];
    }
}