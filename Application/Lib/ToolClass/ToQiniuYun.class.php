<?php
// namespace common\components;




class ToQiniuYun {
    //用户的key
    public $SecretKey;
    public $AccessKey;

    private $strategyData; //token策略的值的数组
    public $strategyKey; //token策略的键

    public $url;

    const SUCCESS = 200;
    const FIELD_ERROR = 300;
    const FILE_ERROR = 500;
    const REQUEST_ERROR = 500;
    const SERVER_ERROR = 600;

    private $errorCode = array(
        200=>'',
        300=>'field is null',
        400=>'file is no exist',
        500=>'http request if fail!!!',
        600=>'',
    );

    public $qiniu_info = array(
        'upload_url_path' => 'http://upload.qiniu.com/',
        'url' => 'http://cdnweb.b5m.com',
        'path_name' => 'web/sms2/',
        'scope' => 'qiniu-web-new',
        'SecretKey' => '_smU1Cfv1zj6S6AumpBthsoaUOx40F-xhBK4L_Pd',
        'AccessKey' => 'D4sR4pwkKcfUC4HXZyK68QWax5OwtzV3bizqb0Ge',
    );


    public function __construct() {
        //此处作为配置，从数据库读取
        $this->strategyKey  = array('scope','deadline','insertOnly','saveKey','endUser','returnUrl','returnBody','callbackUrl','callbackHost','callbackBody','persistentOps','persistentNotifyUrl','persistentPipeline','fsizeLimit','detectMime','mimeLimit');
        $this->url          = $this->qiniu_info['upload_url_path'];
        $this->SecretKey    = $this->qiniu_info['SecretKey'];
        $this->AccessKey    = $this->qiniu_info['AccessKey'];
    }

    //生成凭证
    private function createToken() {
        //检查必填字段是否填写
        if(!isset($this->strategyData['scope'])||!isset($this->strategyData['deadline'])) {
            return false;
        }
        $data = json_encode($this->strategyData);
        $encodeData = $this->urlsafeBase64Encode($data);
        $sign = hash_hmac('sha1',$encodeData , $this->SecretKey, true);
        $encodeSign = $this->urlsafeBase64Encode($sign);
        return $this->AccessKey . ':' . $encodeSign. ':' . $encodeData;
    }

    public function upload($file,$path='') {
        $token = $this->createToken();
        if(!$token) {   
            return $this->getErrorByCode(self::FIELD_ERROR);
        } elseif(empty($file)||!file_exists($file)) {
            return $this->getErrorByCode(self::FILE_ERROR);
        }
        if (class_exists('\CURLFile',false)) {
            $uploadData = array(
                'file' => new \CURLFile($file),
                'token' => $token,
            );
        } else {
            $uploadData = array(
                'file' => '@'.$file,
                'token' => $token,
            );
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadData);
        curl_setopt($ch, CURLOPT_INFILESIZE,filesize($file));
        $result = curl_exec($ch);
        curl_close($ch);

        if(!$result) {
            return $this->getErrorByCode(self::REQUEST_ERROR);
        }

        $res = json_decode($result,true);
        return isset($res['error'])?$this->getErrorByCode(self::SERVER_ERROR,$res):$this->getErrorByCode(self::SUCCESS,$res);
    }

    public function setTokenStrategy(array $data) {
        foreach($data as $k=>$v) {
            if(in_array($k,$this->strategyKey)) {
                $this->strategyData[$k] = $v;
            }
        }
        //如果时间没有设置，设置默认时间 3600s
        if(!isset($this->strategyData['deadline'])) {
            $this->strategyData['deadline'] = time() + 60*60;
        }
    }

    //返回错误信息
    private function getErrorByCode($code,$msg='') {
        $rmsg = !empty($msg)?$msg:$this->errorCode[$code];
        return array('code'=>$code,'msg'=>$rmsg);
    }

    //url64位安全编码
    private function urlsafeBase64Encode($str) {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($str));
    }

    /**
     *  local file to yun
     *
     */
    public function doToYun($local_file, $full_name='') {
        $ret = null;

        $saveKey = $this->qiniu_info['path_name'] . ltrim($local_file,'/');
        if($full_name){
            $saveKey = $this->qiniu_info['path_name'] . ltrim($full_name,'/');
        }

        $this->setTokenStrategy(array('scope' => $this->qiniu_info['scope'], 'saveKey' => $saveKey));
        $ret = $this->upload($local_file);
        return $ret;
    }

    /**
     *  get url , file to yun
     *
     *  @param   string  $local_file
     *  @param   string  $full_name
     *  @return  mix  $ret
     *
     */
    public function doToYunOfUrl($local_file, $full_name='') {
        $ret = $this->doToYun($local_file, $full_name);
        if(isset($ret['msg']['key'])){
            $ret['msg']['yun_url'] = $this->qiniu_info['url'] . '/' . $ret['msg']['key'];
        }
        return $ret;
    }

}
