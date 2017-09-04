<?php

class Response
{
    private static $request_type;

    public function __construct()
    {
        static::$request_type = $_SERVER['CONTENT_TYPE'];
    }

    public static function push($code = '', $message = '', $response_data = '', $request_data = '')
    {
        $args = func_get_args();


        switch (static::$request_type) {
            case 'application/xml':
                static::returnXml($args);
                break;
            default:
                static::returnJson($args);
                break;
        }
    }

    public static function returnXml()
    {

    }

    public static function returnJson($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

}
?>
