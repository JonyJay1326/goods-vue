<?php
/**
 * Created by PhpStorm.
 * User: afanti
 * Date: 2017/8/23
 * Time: 13:49
 */
class BrandCategoryAction extends BaseAction {

    public function _initialize()
    {
        import('ORG.Util.Page');// 导入分页类

//        parent::_initialize();
    }

    public function index(){

    }
    
    /**
     * 指定品牌名称ID，读取其所属的所有品牌类目，并进行等级分组接口。
     * 用于品牌分类首页的已存在的类目数据加载。
     * @see http://erp.stage.com/index.php?g=guds&m=brandCategory&a=getCategoryList
     */
    public function getCategoryList(){
        $brandId = I('sellerId', 'absorba');
        $cateCode = I('cateCode');
        $brandCateModel = new BrandCateModel();
        $cateList = $brandCateModel->getCategoryList($brandId);
        $cateGroup = $brandCateModel->buildCateByLevel($cateList);

        $result = array('code' => 200, 'msg' => 'success', 'data' => $cateGroup);
        $this->jsonOut($result);
    }
    
    /**
     * 读取指定品牌ID和CODE的品牌类目，与通用类目的绑定关系数据。
     * 包括绑定的通用类目各级的CODE码，和各级的所有类目列表
     * @see http://erp.stage.com/index.php?g=guds&m=brandCategory&a=getBindRelation&sellerId=absorba&parCode=C010010000
     */
    public function getBindRelation(){
        $parentCode = I('parCode', 'C030010000');
        $sellerId = I('sellerId','absorba');
        
        $cateModel = new CategoryModel();
        $brandCateModel = new BrandCateModel();
        
        //找到品牌类目数据信息，从中获得绑定的通用类目CODE值
        $brandCate = $brandCateModel->getOneBrandCateData($sellerId, $parentCode);
        $brandCate = array_pop($brandCate);
        if(empty($brandCate)){
            //没有指定的品牌类目，报错。
            $result = ['code' => 500, 'msg' => L('INVALID_PARAM'), 'data' => null];
            $this->jsonOut($result);
        }
        
        //没有绑定通用类目，直接返回空。
        if (empty($brandCate['commCatId'])){
            $result = ['code' => 200, 'msg' => 'Have not bind to any common category!', 'data' => null];
            $this->jsonOut($result);
        }
    
        //读取当前选中的品牌类目，所绑定的通用类目的CODE码和类目信息
        $boundCd = $brandCate['commCatId'];
        $boundCate = $cateModel->getCategoryByCode($boundCd);
        //如果数据库中查询不到 绑定的 通用类目，则报错。要么参数错误，要么数据库数据本身错误，要么就是逻辑错我
        if (empty($boundCate)){
            $result = ['code' => 200, 'msg' => 'Not exist bound common category, please check data!', 'data' => null];
            $this->jsonOut($result);
        }
        
        //品牌类绑定第三级通用类目的情况：
        $start = 0;
        $limit = 50;
        if (!empty($boundCate) && $boundCate['CAT_LEVEL'] == 3){
            //已绑定的各级通用类目的CODE码
            $boundThirdCD = $boundCd;
            $boundSecondCD = $boundCate['PAR_CAT_CD'];
            $boundFirstCD = substr($boundCate['PAR_CAT_CD'], 0, 3) . '0000000';
    
            //查询当前选中的品牌类目，所绑定通用类目的三级,二级，一级类目列表，用于更换通用类目绑定关系
            $thirdLevel = $cateModel->getCategoryByParent($boundSecondCD);
            $secondLevel = $cateModel->getCategoryByParent($boundFirstCD, 2);
            $firstLevel = $cateModel->getCateGoryByLevel(1, $start, $limit);
        } elseif(!empty($boundCate) && $boundCate['CAT_LEVEL'] == 2) {
            //如果绑定的是二级类目，那么boundCd和boundCate就是二级类目CODE和数据。
            $boundThirdCD = -1; //没有绑定三级类目，即选择了 通用类目： 无 选项
            $boundSecondCD = $boundCd;
            $boundFirstCD = $boundCate['PAR_CAT_CD'];
            $thirdLevel = [];
            $secondLevel = $cateModel->getCategoryByParent($boundFirstCD, 2);
            $firstLevel = $cateModel->getCateGoryByLevel(1, $start, $limit);
        } elseif(!empty($boundCate) && $boundCate['CAT_LEVEL'] == 1){
            //绑定的是一级类目
            $boundThirdCD = -1; //没有绑定三级类目，即选择了 通用类目： 无 选项
            $boundSecondCD = -1;
            $boundFirstCD = $boundCd;
            $thirdLevel = $secondLevel = [];
            $firstLevel = $cateModel->getCateGoryByLevel(1, $start, $limit);
        } else{
            //没有绑定任何类目
            $boundThirdCD = -1; //没有绑定三级类目，即选择了 通用类目： 无 选项
            $boundSecondCD = -1;
            $boundFirstCD = $boundCd;
            $thirdLevel = $secondLevel = $firstLevel = [];
        }
        
        //构造返回结果数据
        $bindData = [
            'boundThirdCd' => $boundThirdCD,
            'boundSecondCD' => $boundSecondCD,
            'boundFirstCD' => $boundFirstCD,
            'thirdLevel' => $thirdLevel,
            'secondLevel' => $secondLevel,
            'firstLevel' => $firstLevel
        ];
        
        $result = ['code' => 200, 'msg' => 'success', 'data' => $bindData];
        
        $this->jsonOut($result);
    }
    
    /**
     * 添加新的品牌类目数据
     * @see 
     */
    public function create()
    {
        $brandId = I('sellerId','2scandle');
        $selected = I('selected', 'C070000000');
        $isCreateSub = I('isCreateSub', 'N');
        $cnName = I('cnName', '测试-CN');
        $krName = I('krName', '测试-KR');
        $enName = I('enName','测试-EN');
        $jpName = I('jpName','测试-JP');
        //品牌类目绑定 通用类目 类目的CODE可能为-1，就是页面上的 无 选项。
        $bindL1 = I('bindL1','C020000000');
        $bindL2 = I('bindL2','C020010000');
        $bindL3 = I('bindL3','C020010003');
        
        if(empty($brandId) || empty($selected) || empty($cnName) || empty($krName) || empty($enName) || empty($jpName))
        {
            $result = array('code' => 4002, 'msg' => L('INVALID_PARAMS'), 'data' => null);
            $this->jsonOut($result);
        }
        
        $cateData = ['SLLR_ID' => $brandId, 'CAT_NM' => $cnName, 'CAT_KOR_NM' => $krName];
        $multiCode = ['SLLR_ID' => $brandId, 'cn' => $cnName, 'kr' => $krName, 'en' => $enName, 'jp' => $jpName];
        $condition = [
            'selected' => $selected,  //确定那一层级，配合是否子目录，如果设定了Y，就是子目录，没有就是同级目录。
            'createSub' => $isCreateSub,
            'bindL1' => $bindL1,
            'bindL2' => $bindL2,
            'bindL3' => $bindL3
        ];
        
        $brandCateModel = new BrandCateModel();
        $optionMapModel = new OptionMapModel();
    
        $res = $brandCateModel->createBrandCate($cateData, $condition);
        if ($res !== false && !empty($res)) {
            $multiCode['CAT_CD'] = $res['CAT_CD'];
        } else {
            $result = array('code' => 500, 'msg' => L('SYSTEM_ERROR'), 'data' => null);
            $this->jsonOut($result);
        }
        
        //添加Multi_code数据。
        $multiCodeRes = $optionMapModel->createBrandCate($multiCode);
        if ($multiCodeRes) {
            $result = array('code' => 200, 'msg' => 'success', 'data' => null);
            $this->jsonOut($result);
        } else {
            $result = array('code' => 500, 'msg' => L('SYSTEM_ERROR'), 'data' => null);
            $this->jsonOut($result);
        }
        
    }
    
    /**
     * 修改品牌类目，以及与基础类目的绑定关系
     */
    public function update()
    {
        $sellerId = I('sellerId', '2scandle');
        $selected = I('selected', 'C140010000');
        $cnName = I('cnName', '二级绑2');
        $krName = I('krName', '二级绑3');
        $enName = I('enName', '二级绑2');
        $jpName = I('jpName', '二级绑2');
        $bindL1 = I('bindL1', 'C030000000');
        $bindL2 = I('bindL2', 'C030010000');
        $bindL3 = I('bindL3', -1);
    
        if (empty($sellerId) || empty($selected))
        {
            $result = ['code' => 4005, 'msg' => L('INVALID_PARAMS'), 'data' => null];
            $this->jsonOut($result);
        }
        
        //区分验证参数 方便调试和确定那一部分异常
        if (empty($cnName) || empty($krName) || empty($enName) || empty($jpName))
        {
            $result = ['code' => 4006, 'msg' => L('INVALID_PARAMS'), 'data' => null];
            $this->jsonOut($result);
        }
        
        $brandCateModel = new BrandCateModel();
        $optionMapModel = new OptionMapModel();
        $cateData = ['CAT_NM' => $cnName, 'CAT_KOR_NM' => $krName];
        $multiCode = ['cn' => $cnName, 'kr' => $krName, 'en' => $enName, 'jp' => $jpName];
        $condition = [
            'SLLR_ID' => $sellerId,
            'selected' => $selected,  //要更新的类目Code
            'bindL1' => $bindL1,
            'bindL2' => $bindL2,
            'bindL3' => $bindL3
        ];
        
        //更新并组织返回结果
        $brandUpdate = $brandCateModel->updateBrandCate($cateData, $condition);
        $multiCodeUpdate = $optionMapModel->updateBrandCate($multiCode, $condition);
        
        $result = [
            'code' => 200,
            'msg' => 'success',
            'data' => ['brandCate' => $brandUpdate, 'multiCode' => $multiCodeUpdate]
        ];
        $this->jsonOut($result);
    }
    
    /**
     * 删除品牌类目
     */
    public function delete()
    {
        $sellerId = I('sellerId');
        $selected = I('selected');
        
        if (empty($sellerId) || empty($selected)){
            $result = ['code' => 4007, 'msg' => L('INVALID_PARAMS'), 'data' => null];
            $this->jsonOut($result);
        }
    
        $brandCateModel = new BrandCateModel();
        $optionMapModel = new OptionMapModel();
        $condition = ['sellerId' => $sellerId, 'selected' => $selected];
        $brandDelRes = $brandCateModel->deleteCate($condition);
        $multiDelRes = $optionMapModel->deleteBrandCate($condition);
    
        $result = [
            'code' => 200,
            'msg' => 'success',
            'data' => ['brandCate' => $brandDelRes, 'multiCode' => $multiDelRes]
        ];
        $this->jsonOut($result);
    }
    
}