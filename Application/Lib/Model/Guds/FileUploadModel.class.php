<?php

/**
 * 文件上传工具类
 *
 */
class FileUploadModel extends BaseModel
{
    public $filePath = '/opt/b5c-disk/img/';

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

    public $maxSize = 20971520;
    public $error = '';
    public $save_name = '';
    public $config = array(
        'bsDomain' => 'http://10.80.6.66:8999/imageUpload',
        'tfsUrl' => 'http://upm01.b5m.com/',
        'userName' => 'duanyi',
        'topicName' => 'goods',
        'singleFileName' => 'file',
        'multiFileName' => 'files',
    );

    public function fileUploadExtend()
    {
        set_time_limit(0);
        // 图片上传
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts = $this->fileExts;  // 设置附件上传类型
        $upload->savePath = $this->filePath;  // 设置附件上传目录
        $upload->saveRule = uniqid;
        if (!$upload->upload()) {
            // 上传错误提示错误信息
            exit(json_encode(array('code' => 0, 'msg' => 'failed', 'data' => $upload->getErrorMsg())));
        } else {                                // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            $save_img = $info[0]['savepath'] . $info[0]['savename'];
            //$full_name = date('Ym') . '/' . md5(basename($save_img)) . "." . $info[0]['extension'];
            $params = array('fileName' => $info[0]['savename']);
            $responseData = $this->uploadEvaluationPhoto($save_img, $params);
            $resultData = json_decode($responseData, true);
            if ($resultData['code'] == 200 && !empty($resultData['data'][0]['data'][$info[0]['savename']])) {
                @link($save_img);
                $result = array(
                    'orgtName' => $info[0]['name'],
                    'newName' => $info[0]['savename'],
                    'cdnAddr' => $this->config['tfsUrl'] . $resultData['data'][0]['data'][$info[0]['savename']],

                );
            } else {
                exit(json_encode(array('code' => 0, 'msg' => 'failed', 'data' => null)));
            }

        }

        return $result;
    }

    public function uploadFile()
    {
        set_time_limit(0);
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();             // 实例化上传类
        $upload->maxSize = $this->maxSize;          // 设置附件上传大小
        $upload->allowExts = $this->fileExts;  // 设置附件上传类型
        $upload->savePath = $this->filePath;  // 设置附件上传目录
        if (!$upload->upload()) {                // 上传错误提示错误信息
            $this->error = $upload->getErrorMsg();
            return false;
        } else {                                // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            foreach ($info as $v) {
                $save_names[] = $v['savename'];
            }
            $this->save_name = implode(',', $save_names);
            return true;
        }

    }

    /**
     * 上传图片到，调用java接口
     * @param $file
     * @param $params
     * @return mixed
     */
    public function uploadEvaluationPhoto($file, $params)
    {
        if (class_exists('\CURLFile', false)) {
            $uploadData = array(
                $params['fileName'] => new \CURLFile($file),
                'userName' => $this->config['userName'],
                'topic' => $this->config['topicName'],

            );
        } else {
            $uploadData = array(
                $params['fileName'] => '@' . $file,
                'userName' => $this->config['userName'],
                'topic' => $this->config['topicName'],
            );
        }
        $ch = curl_init();
        $url = $this->config['bsDomain'];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadData);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
