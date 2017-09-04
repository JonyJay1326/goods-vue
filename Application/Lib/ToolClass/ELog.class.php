<?php

/**
 * User: yuanshixiao
 * Date: 2017/7/27
 * Time: 16:59
 */
class ELog
{
    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const REQUEST   = 'REQUEST';  // 操作信息: 后台用户操作记录

    public function __construct() {

    }

    /**
     * @param mixed $msg 消息内容，$msg可以为数组,字符串或json
     * @param string $level
     */
    public static function add($msg='',$level=self::REQUEST) {
        if(is_array($msg)) {
            $msg = json_encode($msg,JSON_UNESCAPED_UNICODE);
        }elseif(!is_null(json_decode($msg))) {

        }elseif(is_string($msg)) {
            $msg = json_encode(['msg'=>$msg],JSON_UNESCAPED_UNICODE);
        }else {
            return false;
        }
        $filePath               = '/opt/logs/logstash/';
        $fileName               = 'logstash_' . date('Ymd') . '_erp_json.log';
        $a                      = parse_url($_SERVER["REQUEST_URI"]);
        parse_str($a["query"], $s);
        $m                      = M('');
        $data ['uId']           = create_guid();
        $data ['noteType']      = 'N001940200';
        $data ['source']        = 'N001950500';
        $data ['level']         = $level;
        $data ['ip']            = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $data ['space']         = null;
        $data ['cTime']         = date('Y-m-d H:i:s');
        $data ['cTimeStamp']    = time();
        $data ['action']        = $s ['a'];
        $data ['model']         = $s ['m'];
        $data ['msg']           = $msg;
        $data ['user']          = $_SESSION['m_loginname'];
        $m->table('tb_ms_user_operation_log')->add($data);
        $data ['id']            = $m->getLastInsID();
        $data ['msg']           = json_decode($data['msg']);
        $txt                    = json_encode($data);
        $file                   = $filePath.$fileName;
        file_put_contents($file, $txt . "\n", FILE_APPEND);
    }
}