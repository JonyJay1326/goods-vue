<?php

class UpdateSelfOrderModel extends Model
{
    public $url = 'http://api.qoo10.com/GMKT.INC.Front.OpenApiService/ShippingBasicService.api/GetShippingAndClaimInfoByOrderNo';
    public $key = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKuEIK_g_1_7G5ScsrXQUzo4euo_g_2_iQ6_g_2_QXTKmfDQDTU5qN6_g_2_K6xTD_g_2_A3DKGVEEEym9lWcrJTmMz8YvLHHB2FeZJFEmbw_g_3__g_3_';
    
    private $_request;
    private $_requestData;
    
    private $_response;
    private $_responseData;
    
    public $runTest = false;
    
    // 接口请求配置时间，避免被封ip，大于1s，随机时间最优
    public $sleepTime = 1;
    
    public function __construct()
    {
        print str_repeat(" ", 4096);
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cli') {
            $this->flushS('You are using cli PHP can\'t use this tool');
        }
    }
    
    public function flushS($content, $isBr = true)
    {
        if (is_array($content)) {
            echo '<pre/>';var_dump($content); if ($isBr) echo '<br />';   
        }  else {
            echo $content;
            if ($isBr) echo "<br />";   
        }
        ob_flush();
        flush();
    }
    
    public function setRequestData($data)
    {
        $this->_requestData = $data;
    }
    
    public function setResponseData($data)
    {
        $this->_responseData = $data;
    }
    
    public function getReqeustData()
    {
        return $this->_requestData;
    }
    
    public function getResponseData()
    {
        return $this->_responseData;
    }
    
    public function curl_request($requestData = '', $cookie = '', $returnCookie = 0)
    {
        set_time_limit(300);
        is_null($requestData) or $this->setRequestData($requestData);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($requestData) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestData));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        //$this->setResponseData($result);
        if (curl_errno($curl)) return curl_error($curl);
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $result, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            //$this->setResponseData($info);
            return $info;
        }
        //sleep($this->sleepTime);
        return $result;
    }
    
    public function run($OrderNo)
    {
        if ($this->runTest) return $this->test(); 
        else {
            $requestData = [
                'Key' => $this->key,
                'OrderNo' => $OrderNo
            ];
            
            return $this->curl_request($requestData);
        }
    }
    
    public function test($ordId)
    {
        $this->url = 'http://sms2.b5cai.stage.com/index.php?m=stock&a=deliver';
        $requestData = [
            'userId' => 1,
            'ordId' => $ordId
        ];
        
        $this->curl_request($requestData);
    }
    
    public function toString()
    {
        return json_decode(json_encode((simplexml_load_string($this->getResponseData()[0]))), true);
    }
}

class ExcelRead
{
    public $index;
    
    public $data;
    
    public $inputFileName;
    
    public $needUpdateOrder;
    
    public function __construct(Index $index)
    {
        $this->index = $index;
        vendor("PHPExcel.PHPExcel");
    }
    
    public $point = 10500;
    
    public function toString($content)
    {
        return json_decode(json_encode((simplexml_load_string($content))), true);
    }
    
    public function getOrderIds()
    {
        //$this->index->flushS("Init Application");
//        $inputFileType = PHPExcel_IOFactory::identify($this->inputFileName);
//        echo 'File ',pathinfo($this->inputFileName,PATHINFO_BASENAME),' has been identified as an ',$inputFileType,' file<br />';
//        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
//        $objPHPExcel = $objReader->load($this->inputFileName);
//        
//        //-----------------
//        $startRow = 3;
//        $activeSheet = $objPHPExcel->getActiveSheet();
//
//        $highestColumn      = $activeSheet->getHighestColumn(); //最后列数所对应的字母，例如第1行就是A
//        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); //总列数
//        $endRow = $activeSheet->getHighestRow();
//        
//        $this->data = array();
//        for ($row = $startRow; $row <= $endRow; $row++) {
//            $this->data [] = $activeSheet->getCellByColumnAndRow('A', $row)->getValue();
//        }
        $handle = @fopen("D:\data.txt", "r");
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                $data [] = trim($buffer);
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }
        
        $this->data = array_slice($data, $this->point, 500);
        
        return;
    }
    
    public function getOrderInfo()
    {
        $needUpdateOrder = [];
        foreach ($this->data as $key => $OrderNo) {
            $result = $ret = $flat = null;
            $this->index->flushS("|-$key-单号-" . $OrderNo, false);
            
            
            $order = M('op_order', 'tb_')->field('SHIPPING_DELIVERY_COMPANY,SHIPPING_TRACKING_CODE,ADDRESS_USER_COUNTRY_CODE')->where('ORDER_ID ="' . $OrderNo . '"')->find();
            
            //if ($order['ADDRESS_USER_COUNTRY_CODE'] == 'KR') {
//                $this->index->key = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKXZTCi_g_2_ovyEI0ZqrNa7MWaBW2xEhIeDEQXOVPaW55ajZWkwUx9GIoB4vxd65M_g_1_3i_g_2_9NT8x8k3l0pFhDK8LLgb6g_g_3__g_3_';
//            } else {
//                $this->index->key = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKuEIK_g_1_7G5ScsrXQUzo4euo_g_2_iQ6_g_2_QXTKmfDQDTU5qN6_g_2_K6xTD_g_2_A3DKGVEEEym9lWcrJTmMz8YvLHHB2FeZJFEmbw_g_3__g_3_';
//            }S5bnbfynQvO1AD3ap2KazBpuBUscJQcKXZTCi_g_2_ovyEI0ZqrNa7MWaBW2xEhIeDEQXOVPaW55ajZWkwUx9GIoB4vxd65M_g_1_3i_g_2_9NT8x8k3l0pFhDK8LLgb6g_g_3__g_3_ 
            $this->index->key = 'S5bnbfynQvO1AD3ap2KazBpuBUscJQcKXZTCi_g_2_ovyEI0ZqrNa7MWaBW2xEhIeDEQXOVPaW55ajZWkwUx9GIoB4vxd65M_g_1_3i_g_2_9NT8x8k3l0pFhDK8LLgb6g_g_3__g_3_';
            $result = $this->index->run($OrderNo);
            $ret = $this->toString($result);
            //$this->index->flushS($ret);
            if ($ret ['ResultCode'] == '-201') {
                $this->index->flushS('<span style="color:red;">&nbsp;&nbsp;|-Fail (Code = -201)</span>');
            } else {
                $this->index->flushS('<span style="color:green;">&nbsp;&nbsp;|-Success (Cide = ' . $ret ['ResultCode'] . ')</span>');
                //shippingStatus
                preg_match('/[0-9]/', $ret["shippingStatus"], $flat);
                if ((int)$flat[0] >= 4) {
                    $needUpdateOrder [] = $OrderNo;
                }
            }
            $this->index->flushS('_____________________________________');
        }
        ob_clean();
        $this->needUpdateOrder = $needUpdateOrder;
        unset($needUpdateOrder);
    }
    
    public function send()
    {
        $urlstock = 'http://erp.gshopper.com/index.php?m=stock&a=deliver';
        
        $success = 0;
        $needUpdateStateOrders = [];
        foreach ($this->needUpdateOrder as $key => $orderId)
        {
            $res = null;
            $data_stock = [];
            $data_stock['userId'] = 1;
            $data_stock['ordId'] = $orderId;
            $res = json_decode(curl_request($urlstock, $data_stock), true);
            if ($res['status'] == 'y') {
                // 出库成功
                $needUpdateStateOrders [] = $orderId;
            } elseif ($res ['status'] == 'n' and !$res ['code'] == 'x01') {
                // 者没有B5C订单号
                
            } elseif ($res ['status'] == 'n' and $res ['code'] == 'x01') {
                // 单据已处理
                $needUpdateStateOrders [] = $orderId;
                $this->_catchMe($res, $orderId);
            }
        }
        
        foreach ($needUpdateStateOrders as $key => $ordid)
        {
            $ids .= $ordid . ',';
        }
        $ids = rtrim($ids, ',');
        $info = [
            'point' => $this->point,
            'read_count' => 500,
            'ord_ids' => $ids,
            'count' => count($needUpdateStateOrders)
        ];
        $this->_sords($info);
        $this->index->flushS('需要更新的总条数:' . count($needUpdateStateOrders));
        $this->index->flushS('sql' . $ids);
    }
    
    public function main()
    {
        $this->index->flushS('Start:');
        $this->index->flushS('解析Excel');
        $this->getOrderIds();
        if (!$this->data) {
            $this->index->flushS('无数据');
            $this->index->flushS('End');
            return;
        }
        $this->index->flushS("|-第三方订单号已获取，共计[". count($this->data) ."] 条");
        if ($this->index->runTest) $this->index->flushS("|-启用测试");  
        else $this->index->flushS("|-未启用测试");
        
        $this->index->flushS('开始从Q10拉取数据');
        $this->getOrderInfo();
        if (!$this->needUpdateOrder) {
            $this->index->flushS('无需要更新的订单');
            $this->index->flushS('End');
            return;
        }
        //$this->index->flushS('需要更新的订单：');
        //$this->index->flushS($this->needUpdateOrder);
        $this->index->flushS('开始更新数据');
        $this->send();
        
        $this->index->flushS('End');
    }
    
    /**
     * success ordid
     */
    private function _sords($ordIds)
    {
        $logFilePath = __DIR__ . '/../../Runtime/logs/';
        // 日志对象
        $log = new \stdClass();
        $trace = debug_backtrace(0);
        $logName = date('Y-m-d') . @$trace[2]['function'] . '-sords.log';
        $txt = "\n------------------------------------------------------------------";
        $txt .= "@@@成功的订单：\n".print_r($ordIds, true);
        $txt .= "\n------------------------------------------------------------------";
        // 保存到日志文件
        $file = $logFilePath.$logName;
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        $old = fread($_fo, 1024 * 1024);
        fclose($_fo);
        file_put_contents($file, $txt.$old);
    }
    
    /**
     * 记录接口日志
     */
    private function _catchMe($stockBack, $ordId)
    {
        $logFilePath = __DIR__ . '/../../Runtime/logs/';
        // 日志对象
        $trace = debug_backtrace(0);
        $logName = date('Y-m-d') . @$trace[2]['function'] . '-order.log';
        $txt = "\n------------------------------------------------------------------";
        $txt .= "\n@@@时间：" . date('Y-m-d H:i:s');
        $txt .= "\n@@@来源：" . isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
        $txt .= "\n@@@方法：" . $_SERVER["REQUEST_METHOD"];
        $txt .= "\n@@@订单号：". $ordId;
        $txt .= "\n@@@仓库接口返回(GET)：\n".print_r($stockBack, true);
        $txt .= "\n------------------------------------------------------------------";
        // 保存到日志文件
        $file = $logFilePath.$logName;
        fclose(fopen($file, 'a+'));
        $_fo = fopen($file, 'rb');
        $old = fread($_fo, 1024 * 1024);
        fclose($_fo);
        file_put_contents($file, $txt.$old);
    }
}






