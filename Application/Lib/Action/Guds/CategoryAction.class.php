<?php
/**
 * 类目处理相关的接口和页面
 * User: afanti
 * Date: 2017/8/14
 * Time: 10:05
 */

class CategoryAction extends BaseAction{


    public function _initialize()
    {
        import('ORG.Util.Page');// 导入分页类

//        parent::_initialize();
    }

    /**
     * 类目页面入口，用于渲染前端初始页面的
     */
    public function index(){
        //TODO 渲染页面
    }

    /**
     * 编辑时 先读取类目信息，在编辑，这个接口提供读取的数据。
     */
    public function getCategoryById()
    {
        $id = I('id');
        if (empty($id)){
            $result = array('code' => '4001', 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }

        $cateModel = new CategoryModel();
        $data =$cateModel->getCategoryById($id);
        $result = array('code' => '200', 'msg' => 'success', 'data' => $data);
        $this->jsonOut($result);
    }

    /**
     * 读取类目列表
     */
    public function getList()
    {
        $pageCount = I('rows', 20);
        $pageNumber = I('page', 1);

        $cateModel = new CategoryModel();
        $totalCount = $cateModel->getSearchCount();
        $start = max($pageNumber -1, 0) * $pageCount;

        $condition = array();
        $cateList = $cateModel->search($condition ,$start, $pageCount);
        $result = array(
            'code' => 200,
            'msg' => 'success',
            'data' => array(
                'totalCount' => $totalCount,
                'rows' => $pageCount,
                'page' => $pageNumber,
                'list' => $cateList
            )
        );
        $this->jsonOut($result);
    }

    /**
     * 搜索类目
     * @see http://erp.stage.com/index.php?g=guds&m=category&a=search&catCode=C010000000&levels=1,3&page=7&rows=2
     */
    public function search(){
        $pageCount = I('rows', 20);
        $pageNumber = I('page', 1);

        $catCode = I('catCode');
        $firstCode = substr($catCode, 0, 3);
        $secondCode = substr($catCode, 3, 3);
        $thirdCode = substr($catCode, 6, 4);
        
        if ($thirdCode !== '0000' && $secondCode !== '000'){ //三级类目
            $code = $catCode;
        } elseif($thirdCode === '0000' && $secondCode !== '000'){ //二级类目
            $code = $firstCode . $secondCode;
        } elseif ($thirdCode === '0000' && $secondCode === '000'){ //一级类目
            $code = $firstCode;
        } else {
            $code = '';
        }
        
        $levels = I('levels');
        $condition = array('CAT_CD' => $code, 'CAT_LEVEL' => $levels);

        $cateModel = new CategoryModel();
        $totalCount = $cateModel->getSearchCount($condition);
        $start = max($pageNumber - 1, 0) * $pageCount;

        $cateList = $cateModel->search($condition ,$start, $pageCount);
        $result = array(
            'code' => 200,
            'msg' => 'success',
            'data' => array(
                'totalCount' => $totalCount,
                'rows' => $pageCount,
                'page' => $pageNumber,
                'list' => $cateList
            )
        );
        $this->jsonOut($result);
    }

    /**
     * 读取一级类目列表
     * @see http://erp.stage.com/index.php?g=guds&m=category&a=getCategoryByLevel&level=2&page=3&rows=5
     */
    public function getCategoryByLevel()
    {
        $level = I('level', 1);
        $pageCount = I('rows', 20);
        $pageNumber = I('page', 1);
        $start = max($pageNumber - 1, 0) * $pageCount;
        
        $cateModel = new CategoryModel();
        $total = $cateModel->getTotalByLevel($level);
        $cateList = $cateModel->getCateGoryByLevel($level, $start, $pageCount);
        $result = array(
            'code' => 200,
            'msg' => 'success',
            'data' => ['totalCount' => $total, 'page' => $pageNumber, 'rows' => $pageCount, 'list' => $cateList]
        );
        $this->jsonOut($result);
    }

    /**
     * 添加新类目
     *
     * TODO 添加一级类目的时候 一级类目的 CAT_ALP 如何确定？
     */
    public function create(){
        $params['levelFirst'] = I('levelFirst', 'C020000000');
        $params['levelSecond'] = I('levelSecond', 'C020010000');
        $params['catLevel'] = I('catLevel', 2);
        $params['catName'] = I('catName', 'nanku');
        $params['catCnName'] = I('cnName', '男裤');
        $params['aliasName'] = I('aliasName', '男裤');
        //$params['catAlp'] = I('catAlp');
        
        if (empty($params['catName']) || empty($params['catCnName']) || empty($params['aliasName']) ){
            $result = array('code' => '4002', 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }

        if (empty($params['catLevel']) ){
            $result = array('code' => '4003', 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }
    
        $cateModel = new CategoryModel();
        
        //检查是否重名，如果重名返回重名的 id和code码
        //$duplicate = $this->where("CAT_NM ='{$data['CAT_NM']}' OR CAT_CNS_NM='{$data['CAT_CNS_NM']}'")->getField("id,CAT_CD");
        $data = ['CAT_NM' => $params['catName'], 'CAT_CNS_NM' => $params['catCnName']];
        $isDuplicate = $cateModel->checkDuplicateName($data, []);
        if ($isDuplicate !== false){
            $result = array('code' => '4006', 'msg' => L('DUPLICATE_NAME'), 'data' => $isDuplicate);
            $this->jsonOut($result);
        }
    
        $res = $cateModel->addCategory($params);
        $result = array('code' => 200, 'msg' => 'success', 'data' => ['id' => $res]);
        $this->jsonOut($result);
    }

    /**
     * 读取指定类目下的子类目，并指定类目层级。
     * @see http://erp.stage.com/index.php?g=guds&m=category&a=getSubcategory&catCode=C020000000&catLevel=2
     */
    public function getSubcategory()
    {
        $catCode = I('catCode');
        $catLevel = I('catLevel');
        if (empty($catCode)){
            $result = array('code' => '4004', 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }

        $cateModel = new CategoryModel();
        $catList = $cateModel->getSubcategory($catCode,$catLevel);
        $result = array('code' => 200, 'msg' => 'success', 'data' => $catList);
        $this->jsonOut($result);
    }

    /**
     * 更新类目数据接口
     * @see http://erp.stage.com/index.php?g=guds&m=category&a=update&catCode=C020000000&id=2&catName=fushixiebao&catCnName=%E6%9C%8D%E9%A5%B0%E9%9E%8B%E5%8C%85&aliasName=%E7%A9%BF%E6%88%B4
     */
    public function update()
    {
        $keyId = I('id');
        $catCode = I('catCode');
        $catName = I('catName');
        $catCnName = I('catCnName');
        $catAlias = I('aliasName');
        if (empty($keyId) || empty($catName) || empty($catCnName)){
            $result = array('code' => '4005', 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }

        $cateModel = new CategoryModel();
        $update = array('CAT_NM' => $catName, 'CAT_CNS_NM'=>$catCnName, 'ALIAS' => $catAlias);
        $condition = array('id' => $keyId);
        
        //检查是否重名，如果重名返回重名的 id和code码
        $isDuplicate = $cateModel->checkDuplicateName($update, $condition);
        if ($isDuplicate !== false){
            $result = array('code' => '4006', 'msg' => L('DUPLICATE_NAME'), 'data' => $isDuplicate);
            $this->jsonOut($result);
        }
        
        //执行更新操作
        $res = $cateModel->update($update, $condition);
        $result = array('code' => 200, 'msg' => 'success', 'data' => $res);
        $this->jsonOut($result);
    }

}