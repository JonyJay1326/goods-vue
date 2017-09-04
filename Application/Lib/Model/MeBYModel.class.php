<?php

/**
 * 所有的操作，通过实现MeBYModel::$oci
 * eg:
 *   查询-> MeBYModel::$oci->query($sql)
 *   关闭-> MeBYModel::$oci->close()
 *   事物-> MeBYModel::$oci->startTrans()
 *   提交-> MeBYModel::$oci->commit()
 *   回滚-> MeBYModel::$oci->rollback()
 * 注意事项：
 *   表名需要跟模型名：ECOLOGY
 */
class MeBYModel
{
    protected $table_prefix = 'ECOLOGY';
    
    public $config = [
        'database' => "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.30.99.232)(PORT = 1521)))(CONNECT_DATA=(SID=ECOLOGY)))",
        'username' => 'sms',
        'password' => 'rlhOwijeZ1WTlKQp'
    ];
    
    public static $oci;
    
    public function __construct()
    {
        if (is_resource(static::$oci)) return $oci;
        $oci = new DbOracle($this->config);
        $this->connect();
        
        return static::$oci = $oci;
    }
    
    /**
     * 重载连接数据库方法，返回_linkID
     * @access public
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config = $this->config;
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            $conn = $pconnect ? 'oci_pconnect':'oci_new_connect';
            $this->linkID[$linkNum] = $conn($config['username'], $config['password'],$config['database']);//modify by wyfeng at 2008.12.19

            if (!$this->linkID[$linkNum]){
                $this->error(false);
            }
            // 标记连接成功
            $this->connected = true;
            //注销数据库安全信息
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        $this->_linkID = $this->linkID[$linkNum];
        return $this->linkID[$linkNum];
    }
    
    /**
     * test query
     * 默认返回HR所有信息
     */
    public function testQuery($sql = 'SELECT * FROM ECOLOGY.HRMRESOURCE')
    {
        // 获取所有hr信息
        $ret = static::$oci->query($sql);
        
        return $ret;
    }
    
    public function query($sql)
    {
        return static::$oci->query($sql);
    }
}