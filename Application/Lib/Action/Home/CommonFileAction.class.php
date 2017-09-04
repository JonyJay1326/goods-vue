<?php

/**
 * User: yuanshixiao
 * Date: 2017/6/27
 * Time: 13:28
 */
class CommonFileAction extends BaseAction
{
    public function download()
    {
        $name = I('get.name');
        import('ORG.Net.Http');
        $filename = APP_PATH . 'Tpl/CommonFile/' . $name;
        Http::download($filename, $filename);
    }
}