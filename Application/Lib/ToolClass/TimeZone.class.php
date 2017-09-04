<?php

/**
 * 时间工具类，避免使用strtotime
 * 用法 
$tz = new TimeZone('2017/06/11 00:00:00');
$tz->add('P6M2D');
echo $tz->transformationDate('U');
 * 
 */
class TimeZone
{
    private $date;
    
    public function __construct($date, $zone = 'Asia/Shanghai')
    {
        $this->date = new DateTime($date, $this->setTimeZone($zone));
    }
    
    /**
     * @params $format
     * 
     */
    public function transformationDate($format = 'Y-m-d H:i:s')
    {
        
        return $this->date->format($format);
    }
    
    /**
     * 时间增加
     * 使用DateInterval，看手册参数设置
     * @param $potime 时间点
     */
    public function add($potime)
    {
        $this->date->add(new DateInterval($potime));
    }
    
    /**
     * 时区设置
     * @param default 'Asia/Shanghai' 时区
     * 
     */
    public function setTimeZone($zone)
    {
        return new DateTimeZone($zone);
    }
}