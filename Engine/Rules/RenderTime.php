<?php

namespace AJUR\LogEstimator\Rules;

use Spatie\Regex\Regex;

class RenderTime
{
    
    public static function parse_rendertime_v1(string $string)
    {
        $MASK = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\].+Render time:.+\[(\d{1,3}\.\d{5,6}),\"(\d{1,6})\",(\d{3,5}),(\d{3,5})\]/';
        
        $match = Regex::match($MASK, $string);
        
        if ($match->hasMatch()) {
            $dt = $match->group(1);
            $render_time = $match->group(2);
            $article_id = $match->group(3);
            $article_bb = $match->group(4);
            $article_html = $match->group(5);
        
            $dataset = [
                'dt'            =>  $dt,
                'article_id'    =>  $article_id,
                'render_time'   =>  $render_time,
                'size_textbb'   =>  $article_bb,
                'size_html'     =>  $article_html,
                'size_diff'     =>  $article_html - $article_bb,
                'url'           =>  "https://47news.ru/articles/{$article_id}/",
            ];
        
            return $dataset;
        }
    
        return [];
    }
    
    public static function parse_rendertime_v2(string $string)
    {
        $MASK = '/\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\].+Render time \(json\)\: (\{.+\}).+\[\] \[\]/';
        
        $match = Regex::match($MASK, $string);
        
        if ($match->hasMatch()) {
            $dt = $match->group(1);
            $json = json_decode($match->group(2), true);
            
            
            $dataset = [
                'dt'            =>  $dt,
                'article_id'    =>  $json['article_id'],
                'render_time'   =>  $json['time'],
                'size_textbb'   =>  $json['length:text_bb'],
                'size_html'     =>  $json['length:render'],
                'size_diff'     =>  $json['length:render'] - $json['length:text_bb'],
                'url'           =>  "https://47news.ru/articles/{$json['article_id']}/",
            ];
            
            return $dataset;
        }
        
        return [];
    }
}