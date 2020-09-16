<?php

namespace AJUR\LogEstimator;

use AJUR\LogEstimator\Rules\RenderTime;
use AJUR\LogEstimator\Rules\UsageStats;
use AJUR\LogEstimator\Rules\IsMobile;
use Spatie\Regex\Regex;


/**
 * User: Arris
 *
 * Class ParseLogString
 *
 * Date: 02.12.2019, time: 4:58
 * Date: 20.05.2020, time 19:40
 *
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
            return UsageStats::parse_v1($string, $source);
        }

        if (strpos($string, ': Usage [') !== false) {
            return UsageStats::parse_v2($string, $source);
        }
        
        if (strpos($string, 'Metrics:') !== false) {
            return UsageStats::parse_v3($string, $source);
        }
        
        if (strpos($string, 'Render time:') !== false) {
            return RenderTime::parse_rendertime_v1($string);
        }
        
        if (strpos($string, 'Render time (json):') !== false) {
            return RenderTime::parse_rendertime_v2($string);
        }

        return false;
    }

    
    
    
    
}