<?php


namespace AJUR\LogEstimator\Rules;


use Spatie\Regex\Regex;

class IsMobile
{
    /**
     * Парсит декект мобильности для ДП
     * [2019-11-20 17:44:51] DoctorPiter.a259d613c78a96f4.DEBUG: Summary/GET_FLAG/Legacy/isMobile/isTablet/UA:  [false,false,"000",false,false,"Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)"] []
     *
     * @param string $string
     * @return array
     * @throws \Spatie\Regex\RegexFailed
     */
    public static function parse_isMobile_DP(string $string):array
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