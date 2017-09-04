<?php

/**
 *  class: obtain some data from db
 *
 */


class DataMain{


    public static function getItems($items, $key = null,$throw=false)
    {
        if ($key !== null)
        {
            if (key_exists($key, $items))
            {
                return $items[$key];
            }
            if($throw)
            {
                return null;
            }
            return 'unknown key:' . $key;
        }
        return $items;
    }

    const Status_Enable = 1;
    const Status_Desable = 0;
    public static function getStatusItems($key = null)
    {
        $items = [
            self::Status_Enable => L('可用'), 
            self::Status_Desable => L('禁用')
        ];
        return self::getItems($items, $key);
    }

    const Status_open = 1;
    const Status_close = 0;
    public static function getStatusItemsForOpen($key = null)
    {
        $items = [
            self::Status_open => L('启用'), 
            self::Status_close => L('关闭')
        ];
        return self::getItems($items, $key);
    }

    const Status_Y = 'Y';
    const Status_N = 'N';
    public static function getStatusYN($key = null)
    {
        $items = [
            self::Status_N => L('否'),
            self::Status_Y => L('是'),
        ];
        return self::getItems($items, $key);
    }


    /**
     *  gain admin info
     *
     */
    static public function gainAdminById($id){
        $info = D("Admin")->find($id);
        return $info;
    }

    /**
     *  gain admin name
     *
     */
    static public function gainAdminName($id){
        $data = self::gainAdminById($id);
        $name = isset($data['M_NAME'])?$data['M_NAME']:'';
        return $name;
    }





}

