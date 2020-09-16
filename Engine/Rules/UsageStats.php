<?php

namespace AJUR\LogEstimator\Rules;

use Spatie\Regex\Regex;

class UsageStats
{
    /**
     * @param string $string
     * @param string $source
     * @return array|bool
     * @throws \Spatie\Regex\RegexFailed
     */
    public static function parse_v1(string $string, string $source)
    {
        $MASK_v1 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] ' . $source . '\.[\d\w]+.NOTICE: Usage:\s+\[(\d{1,4}\.\d{1,3}),(\d+),\"(\w+)\",\"(.*)\"\]\s+\[\]/';
        
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
    public static function parse_v2(string $string, string $source)
    {
        $MASK_v2 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] ' . $source . '\.[\d\w]+.NOTICE: Usage \["\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}","Time",(\d{1,4}\.\d{1,3}),"Memory",(\d+),"MySQL",\["Time",(\d{1,5}\.\d{1,3}),"Queries",(\d+)\],"(\w+)","(.*)"\] \[\]/';
        
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
            
            /*if (array_key_exists($site_area, self::$area_mapping)) {
                $site_area = self::$area_mapping[ $site_area ];
            }*/
            
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
    public static function parse_v3(string $string, string $source):array
    {
        $MASK_v3 = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\] ' . $source . '\.[\d\w]+.NOTICE: Metrics: (\{\S*})/';
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
    
    
    
    
    
}