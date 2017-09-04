<?php
/**
 * 文件上传工具类
 *
 */
class FileUploadModel
{
    public $filePath = '/opt/b5c-disk/img/';
    public static $fileUPath = '/opt/b5c-disk/img/';

    public $fileExts = [
        'jpg',
        'gif',
        'png',
        'jpeg',
        'zip',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
    ];

    public $maxSize     = 20971520;
    public $error       = '';
    public $save_name   = '';
    public $info        = '';

    public function fileUploadExtend()
    {
        set_time_limit(0);
        // 图片上传
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize    = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts  = $this->fileExts;  // 设置附件上传类型
        $upload->savePath   = $this->filePath;  // 设置附件上传目录
        if(!$upload->upload()) {                // 上传错误提示错误信息
            exit(json_encode(['status' => 0, 'msg' => '图片上传失败', 'data' => $upload->getErrorMsg()]));
        } else {                                // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
        }

        return $info[0]['savename'];
    }

    public function uploadFile() {
        set_time_limit(0);
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize    = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts  = $this->fileExts;  // 设置附件上传类型
        $upload->savePath   = $this->filePath;  // 设置附件上传目录
        if(!$upload->upload()) {                // 上传错误提示错误信息
            $this->error = $upload->getErrorMsg();
            return false;
        } else {                                // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            foreach ($info as $v) {
                $save_names[] = $v['savename'];
            }
            $this->save_name = implode(',',$save_names);
            return true;
        }
    }


    public function uploadFileArr() {
        set_time_limit(0);
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize    = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts  = $this->fileExts;  // 设置附件上传类型
        $upload->savePath   = $this->filePath;  // 设置附件上传目录
        if(!$upload->upload()) {                // 上传错误提示错误信息
            $this->error = $upload->getErrorMsg();
            return false;
        } else {                                // 上传成功 获取上传文件信息
            return $upload->getUploadFileInfo();
        }

    }

    /**
     * 单文件上传
     * @param $file 文件
     */
    public function saveFile($file) {
        set_time_limit(0);
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize    = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts  = $this->fileExts;  // 设置附件上传类型
        $upload->savePath   = $this->filePath;  // 设置附件上传目录
        if(!$upload->upload()) {                // 上传错误提示错误信息
            $this->error = $upload->getErrorMsg();
            return false;
        } else {                                // 上传成功 获取上传文件信息
            $this->info = $upload->getUploadFileInfo();
            return true;
        }
    }
}