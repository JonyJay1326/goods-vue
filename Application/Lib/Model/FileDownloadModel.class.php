<?php

/**
 * 文件下载工具类
 * 
 */  
class FileDownloadModel extends BaseModel
{
    public $path = '/opt/b5c-disk/img/';
    
    public $fname = '';
    
    public $depr = '/';
    
    private $file = '';
    
    public function __construct()
    {
        import('ORG.Net.Http');
    }
        
    public function downloadFile()
    {
        if ($this->checkFileExists()) {
            Http::download($this->file);
        }
        
        return false;
    }
    
    public function transcoding()
    {
        $this->fname = iconv("utf-8", "gb2312", $this->fname);
    }
    
    public function checkFileExists()
    {
        $this->file = $this->path . $this->depr . $this->fname;
        if (!file_exists($this->file)) return false;
        
        return true;
    }
}