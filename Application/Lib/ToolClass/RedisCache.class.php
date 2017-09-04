<?php

/**
 * redis 操作类
   $cache = new RedisCache();
   $cache->mset(['aaa' => 'benyin1', 'bbb' => 'benyin2', 'ccc' => 'benyin3']);
   echo '<pre/>';var_dump($cache->mget(['aaa', 'bbb', 'ccc']));exit;
 * 
 */
class RedisCache
{
    public $cache;
    public $cacheType = 'redis';
    public $defaultOptions = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 60,
        'persistent' => false
    ];
    
    public function __construct()
    {
        $this->cache = Cache::getInstance($this->cacheType, $this->defaultOptions);
    }
    
    /**
     * @param $key 
     * @param $value
     * @param $expire 缓存时间,如果不设置会直接读取C('DATA_CACHE_TIME')
     */
    public function set($key, $value, $expire = null)
    {
        $this->cache->set($key, $value, $expire = null);
    }
    
    /**
     * @param $key
     * @return $value
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }
    
    /**
     * @param array $mget_data
     * 
     */
    public function mset($mset_data)
    {
        $this->cache->mset($mset_data);
    }
    
    /**
     * @param array $mset_data
     * @return array data
     */
    public function mget($mget_data)
    {
        return $this->cache->mget($mget_data);
    }
    
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return $this->cache->rm($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->cache->clear();
    }
}