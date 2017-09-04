<?php

/**
 * Alone API class
 * 2017
 * author: huaxin
 *
 */
class ApiAction extends Action {

    private function output_json($ret){
        echo ZWebHttp::CallbackBegin(1).
                json_encode($ret).
                ZWebHttp::CallbackEnd(1);
    }

    private function getJson() {
        $data = file_get_contents('php://input');
        $data = json_decode($data,true);
        return $data;
    }

    public function index(){
    }

    public function pushmsg(){
        $tmp = A('Msg','Aapi');
        echo $tmp->apppushresult();
    }

    public function codevalue(){
        $tmp = A('Code','Aapi');
        echo $tmp->codeall();
    }

    /**
     *  make and write a post registrant
     *
     */
    public function merchant_apply_register(){
        $this->output_json(A('Merchant','Aapi')->register());
    }

    /**
     * 查询人员
     * @return [type] [description]
     */
    public function search()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->showList($data);
        Response::push($ret);
    }

    /**
     * 新增人员
     * @param string $value [description]
     */
    public function AddPersonnel()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
       $ret = $tmp->addCustomer($data);
        Response::push($ret);
    }

    /**
     * 上传文件接口
     */
    
    public function hrUpload()
    {
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->upload($_FILES);
        Response::push($ret);

    }

    

     public function show()
    {
        $filename = $_REQUEST['filename'];
        $str = ATTACHMENT_DIR.$filename;
        //check img doc - skip
        $read_data = file_get_contents($str);
        echo $read_data;
    }

    /**
     * 新增跟踪信息
     * @param string $value [description]
     */
    public function addTrack()
    {

        $data = $this->getJson(); 
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->editTrack($data);
        Response::push($ret);
    }
    /**
     *批量修改 
     * 
     */
    public function batchChange(){
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->statusChange($data);
        Response::push($ret);
    }

    /**
     * 下拉选项接口
     */
    public function choice()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->acquireData($data);
        Response::push($ret);
    }

    /**
     * 编辑名片接口
     */
    public function editCard()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->changeCard($data);
        Response::push($ret);
    }

/**
     * 编辑追踪接口
     */
    public function changeTrack()
    {
        //$id = $data['emplid'];
        $data = $this->getJson();
        //var_dump($data);die;
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->changeTrack($data);
        Response::push($ret);
    }


   /**
     * 名片信息展示
     * @return [type] [description]
     */
    public function card()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->Mycard($data);
        Response::push($ret);
    }
    
    /**
     * excel导入员信息
     * @return [type] [description]
     */
    public function import_emp()
    {
        $im = new ImportEmpModel();
        $ret = $im->import();
        Response::push($ret);
    }

    /**
     * 导出excel
     * 
     * @return [type][description]
     */
    public function export_emp()
    {
        $id = $_REQUEST['EMPL_ID'];
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->export($id);
        Response::push($ret);
    }

    /**
     * 省市县三级联动
     */
    public function address()
    {
        $data = $this->getJson();
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->address($data);
        Response::push($ret);
    }

    public function download()
    {
        import('ORG.Net.Http');
        $filename = APP_PATH.'Tpl/Home/Hr/empl.xlsx';
        Http::download($filename, 'empl.xlsx');
    }

    public function emplDelele()
    {
        $data = $this->getJson();
        //var_dump($data);die;
        $tmp = A('Hr','Aapi',true);
        $ret = $tmp->delete($data);
        Response::push($ret);
    }

    /**
     * 查看附件信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function checkAttach()
    {
        $data = $_REQUEST;
        import('ORG.Net.Http');
        if ($data['perCardPic']) {
            $fileName = $data['perCardPic'];
        }elseif ($data['resume']) {
            $fileName = $data['resume'];
        }elseif ($data['degCert']) {
            $fileName = $data['degCert'];
        }elseif ($data['graCert']) {
             $fileName = $data['graCert'];
        }elseif ($data['learnProve']) {
            $fileName = $data['learnProve'];
        }
        $filename = $fileName;
        $direct_down_filename=ATTACHMENT_DIR.$filename; // For test: 'C:\Users\b5m\Downloads/huaxintestpic.jpg';
        $type = mime_content_type($direct_down_filename);
        $showname = basename($direct_down_filename);
        header("Content-type: ".$type);
        header("Content-Disposition: attachment; filename=".$showname);
        echo file_get_contents($direct_down_filename);  die();
    }


}


