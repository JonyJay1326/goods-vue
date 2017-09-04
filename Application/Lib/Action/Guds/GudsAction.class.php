<?php
/**
 * Created by PhpStorm.
 * User: afanti
 * Date: 2017/7/24
 * Time: 14:08
 */

class GudsAction extends BaseAction
{

    #语言
    static $_LANG_VALS = array(
        'N000920100' => array('cn' => '中文', 'kr' => '중국어', 'en' => 'chinese', 'jp' => '中国語'),
        'N000920400' => array('cn' => '韩语', 'kr' => '한국어', 'en' => 'korean', 'jp' => '韓国語'),
        'N000920200' => array('cn' => '英语', 'kr' => '영어', 'en' => 'english', 'jp' => '英語'),
        'N000920300' => array('cn' => '日语', 'kr' => '일본어', 'en' => 'Japanese', 'jp' => '日本語'),
    );
    static $_CHK_STATUS_VALS = array(
        'N000420100' => array('cn' => '草稿', 'kr' => '', 'en' => '', 'jp' => ''),
        'N000420200' => array('cn' => '审核中', 'kr' => '', 'en' => '', 'jp' => ''),
        'N000420400' => array('cn' => '审核成功', 'kr' => '', 'en' => '', 'jp' => ''),
        'N000420300' => array('cn' => '审核失败', 'kr' => '', 'en' => 'Japaese', 'jp' => ''),
    );
    #按扭权限
    static $_RIGHTS_VALS = array(
        'Guds/Guds/chk'
    );

    public $GudsUnit, $Directory;


    public function _initialize()
    {
        import('ORG.Util.Page');// 导入分页类
        header('Access-Control-Allow-Origin: *');
        header('Content-Type:text/html;charset=utf-8');
        $this->Directory = D('Directory');
        $this->GudsUnit = $this->Directory->getGudsUnitVals('CD', 'CD_VAL');//获取商品单位
        parent::_initialize();
    }

    public function index()
    {
        $this->display('listMain');
    }


    public function showList()
    {
        $order = $params = $data = array();
        $chkRights = false;
        $params['startDate'] = $params['endDate'] = '';
        $page_num = empty($params['pageNum']) ? C('PAGE_NUM') : $params['pageNum'];
        $Guds = D('@Model/Guds/Guds');
        $B5caiCate = D('@Model/Guds/B5caiCate');
        $BrandCate = D("@Model/Guds/BrandCate");
        $b5caiCateList = $B5caiCate->getB5caiCateList(1);
        $currencyData = $this->Directory->getCurrency();
        $getData = file_get_contents("php://input");
        $params = json_decode($getData, true);
        $dateVal = $params['dateVal'];
        if (!empty($dateVal)) {
            $dateArr = explode('-', $dateVal);
            $params['startDate'] = empty($dateArr[0]) || empty($dateArr[1]) || empty($dateArr[2]) ? '' : $dateArr[0] . '-' . $dateArr[1] . '-' . $dateArr[2] . " 00:00:00";
            $params['endDate'] = empty($dateArr[3]) || empty($dateArr[4]) || empty($dateArr[5]) ? '' : $dateArr[3] . '-' . $dateArr[4] . '-' . $dateArr[5] . " 23:59:59";

        }
        $count = $Guds->getGudsList($params, 'count');
        $page = I('get.page', 1);
        $firstRow = ($page - 1) * $page_num;
        $totalPage = ceil($count[0]['num'] / $page_num);
        $gudsData = $Guds->getGudsList($params, 'list', $firstRow, $page_num);
        #echo $Guds->getLastSql();die;
        foreach ((array)$gudsData as $guds) {

            //$brandCateData[] = $guds['catId'];
            $data = $BrandCate->getBrandCateDataBySllrIdAndCatCd($guds['brandId'], $guds['catId']);
            if (!empty($data)) {
                $tempArr[] = $data;
            }
        }
        foreach ((array)$tempArr as $brandCate) {
            /*$brandCateData[$brandCate['brandId'] . '_' . $brandCate['catId']] = $brandCate['commCatId']*/;
            $brandCateData[$brandCate['brandId'] . '_' . $brandCate['catId']] = !empty($brandCate['catName']) ? $brandCate['catName'] : (!empty($brandCate['catKrName']) ? empty($brandCate['catKrName']) : '');
        }
        /*$tempArr = array();
        $b5caiCateData = empty($brandCateData) ? array() : $B5caiCate->getB5caiCateById(array('CAT_CD' => array('exp', "IN ('" . implode("','", $brandCateData) . "')")), 'CAT_CD,CAT_NM,CAT_CNS_NM');
        if (!empty($b5caiCateData)) {
            foreach ((array)$b5caiCateData as $b5caiCate) {
                $tempArr[$b5caiCate['CAT_CD']] = empty($b5caiCate['CAT_CNS_NM']) ? $b5caiCate['CAT_NM'] : $b5caiCate['CAT_CNS_NM'];
            }
        }*/
        $actList = $_SESSION['actlist'];
        $roleId = $_SESSION['role_id'];
        if (in_array(self::$_RIGHTS_VALS[0], $actList) || $roleId == 1) {
            $chkRights = true;
        }
        foreach ((array)$gudsData as $key => $guds) {
            $gudsData[$key]['catName'] = empty($brandCateData[$guds['brandId'] . '_' . $guds['catId']]) ? '' : $brandCateData[$guds['brandId'] . '_' . $guds['catId']];//关联b5cai类目是名称
            $gudsData[$key]['unitName'] = $this->GudsUnit[$guds['unit']];//单位
            $gudsData[$key]['langName'] = self::$_LANG_VALS[$guds['lang']]['cn'];
            $gudsData[$key]['chkStatusName'] = self::$_CHK_STATUS_VALS[$guds['chkStatus']]['cn'];
            $gudsData[$key]['price'] = sprintf('%0.2f', $guds['price']);
            $gudsData[$key]['priceType'] = $currencyData[$guds['priceType']]['CD_VAL'];
        }

        $result = array('code' => 2000, 'msg' => 'success', 'data' => array('pageNum' => $page_num, 'page' => $page, 'totalPage' => $totalPage, 'totalNum' => $count[0]['num'], 'chkRights' => $chkRights, 'list' => $gudsData, 'b5caiCateList' => $b5caiCateList));
        $this->jsonOut($result);
    }

    public function addPage()
    {
        $brandAction = A("Guds/Brand");
        $brandList = $brandAction->showBrandList();
        $result = array('code' => 2000, 'msg' => 'success', 'data' => array('brandList' => $brandList, 'unit' => $this->GudsUnit, 'lang' => self::$_LANG_VALS));
        $this->jsonOut($result);
    }

    /**
     * 创建商品页面的输出。
     */
    public function create()
    {
        $this->display('addGoods');
    }


    public function doAdd()
    {
        /*$BrandCate = D("@Model/Guds/BrandCate");*/
        $Guds = D("@Model/Guds/Guds");
        $GudsImg = D("@Model/Guds/GudsImg");
        $getData = file_get_contents("php://input");
        $params = json_decode($getData, true);
        $cateId = $params['cateId'];
        $sllrId = $params['brandId'];
        //$params['brandName']
        //$params['unit']
        //$params['lifeTime']
        if (empty($cateId) || empty($sllrId)) {
            $result = array('40000000', 'msg' => '参数不正确', 'data' => null);
            $this->jsonOut($result);
        }
        if (empty($params['brandName'])) {
            $result = array('40000001', 'msg' => '商品品牌名不空', 'data' => null);
            $this->jsonOut($result);
        }
        if (empty($params['unit'])) {
            $result = array('40000002', 'msg' => '商品单位不能为空', 'data' => null);
            $this->jsonOut($result);
        }
        /*$brandCateData = $BrandCate->getBrandCateDataBySllrIdAndCatCd($sllrId, $cateId);
        $params['cateId'] = $brandCateData['commCatId'];*/
        $data = $Guds->saveGuds($params);
        if (empty($data)) {
            $result = array('40000100', 'msg' => 'failed', 'data' => 'guds basic info error');
            $this->jsonOut($result);
        }
        foreach ($data['langData'] as $key => $val) {
            if (empty($params['langData'][$key]['imgData'])) {
                continue;
            }
            $oldName = $params['langData'][$key]['imgData']['orgtName'];
            $newName = $params['langData'][$key]['imgData']['newName'];
            $cdnAddr = $params['langData'][$key]['imgData']['cdnAddr'];
            $params['SLLR_ID'] = $val['sllrId'];
            $params['MAIN_GUDS_ID'] = $val['mainId'];
            $params['GUDS_ID'] = $val['gudsId'];
            $params['GUDS_IMG_CD'] = 'N000080200';
            $params['GUDS_IMG_ORGT_FILE_NM'] = $oldName;
            $params['GUDS_IMG_SYS_FILE_NM'] = $newName;
            $params['GUDS_IMG_CDN_ADDR'] = $cdnAddr;
            $params['SYS_REGR_ID'] = $val['sllrId'];
            $params['SYS_CHGR_ID'] = $val['sllrId'];
            $params['LANGUAGE'] = $key;
            $GudsImg->saveData($params);
        }
        $result = array('code' => '2000', 'msg' => 'success', 'data' => $data);
        $this->jsonOut($result);


    }

    /**
     * 商品详情
     */
    public function showGuds()
    {
        $langStr = $key1 = $key2 = $key3 = '';
        $mainId = I('get.mainId', 0);
        $sllrId = I('get.sllrId', 0);
        $gudsId = I('get.gudsId', 0);
        //$langId = I('get.langId', 'N000920100');

        $gudsImgsData = $gudsData = array();
        if (empty($mainId) || empty($sllrId) || empty($gudsId)) {
            $this->jsonOut(array('code' => '40000020', 'msg' => 'params is error', 'data' => null));
        }
        $Guds = D('@Model/Guds/Guds');
        $GudsImg = D('@Model/Guds/GudsImg');
        $GudsChk = D('@Model/Guds/GudsChk');
        $BrandCate = D("@Model/Guds/BrandCate");
        $brandAction = A("Guds/Brand");
        $brandCateList = $brandAction->showBrandCateList($sllrId);#品牌分类
        $gudsDataArr = $Guds->getGudsDetailByGudsIdAndSllrId($gudsId, $sllrId);#商品信息
        $gudsImgsDataArr = $GudsImg->getGudsImgByMainIdAndSllrId($mainId, $sllrId);#商品图片
        if (!empty($gudsImgsDataArr)) {
            foreach ($gudsImgsDataArr as $gudsImgs) {
                $gudsImgsData[$gudsImgs['LANGUAGE']] = $gudsImgs['GUDS_IMG_CDN_ADDR'];
            }
        }
        if (!empty($gudsDataArr)) {
            /*$BrandCateData = $BrandCate->getOneBrandCateData($gudsDataArr[0]['brandId'], $gudsDataArr[0]['catId'])[0];*/
            $leverArr = $Guds->dealWithCateLever($gudsDataArr[0]['catId']);
            $gudsData['common'] = array(
                'brandId' => $gudsDataArr[0]['brandId'],
                'mainId' => $gudsDataArr[0]['mainId'],
                'gudsId' => $gudsDataArr[0]['gudsId'],
                /*'commCateId' => $BrandCateData['commCatId'],*/
                'cateId' => $gudsDataArr[0]['catId'],
                'brandName' => $gudsDataArr[0]['brandName'],
                'unit' => $this->GudsUnit[$gudsDataArr[0]['unit']],
                'shelfLife' => $gudsDataArr[0]['shelfLife'],
                'priceType' => $gudsDataArr[0]['priceType'],
                'gudsOrgp' => $gudsDataArr[0]['gudsOrgp'],
            );

            $gudsData['common'] = array_merge($gudsData['common'], $leverArr);
        }
        foreach ((array)$gudsDataArr as $val) {
            $langStr = self::$_LANG_VALS[$val['lang']]['cn'];
            $gudsData['common']['lang'][$val['lang']] = array(
                'gudsName' => $gudsDataArr[0]['gudsName'],
                'gudsCnName' => $gudsDataArr[0]['gudsCnName'],
                'gudsSubName' => $gudsDataArr[0]['gudsSubName'],
                'gudsSubCnName' => $gudsDataArr[0]['gudsSubCnName'],
                'langName' => $langStr,
                'img' => empty($gudsImgsData) ? '' : $gudsImgsData[$val['lang']],
            );
        }
        //$gudsData['common']['langStr'] = trim($langStr, ';');
        $chkData = $GudsChk->getChkContent($mainId, $gudsId);
        $gudsData['common']['remark'] = $chkData['content'];
        $result = array('code' => 2000, 'msg' => 'success', 'data' => array('guds' => $gudsData, 'brandList' => $brandCateList));
        $this->jsonOut($result);
    }

    /**
     * 处理审核状态
     */
    public function doChkGuds()
    {
        $GudsChk = D('@Model/Guds/GudsChk');
        $Guds = D('@Model/Guds/Guds');
        $getData = file_get_contents("php://input");
        $getParams = json_decode($getData, true);
        $params['MAIN_GUDS_ID'] = $getParams['mainId'];
        $params['GUDS_ID'] = $getParams['gudsId'];
        $params['CHK_STATUS'] = $getParams['status'];
        $params['CHK_CONTENT'] = $getParams['content'];
        if (empty($params['MAIN_GUDS_ID']) || empty($params['CHK_STATUS']) || empty($params['GUDS_ID'])) {
            $this->jsonOut(array('code' => '400000001', 'msg' => 'params is  error', 'data' => null));
        }
        $data = $GudsChk->getChkData($params['MAIN_GUDS_ID'], $params['GUDS_ID']);
        if (empty($data)) {
            $result = $GudsChk->saveData($params);
        } else {
            $result = $GudsChk->updateData($params);
        }
        if ($result === false) {
            $this->jsonOut(array('code' => '400000002', 'msg' => 'update failed', 'data' => null));
        } else {
            $Guds->updateGudsStatus($params['MAIN_GUDS_ID'], $params['GUDS_ID'], $params['CHK_STATUS']);
            $this->jsonOut(array('code' => '2000', 'msg' => 'success', 'data' => $result));
        }
    }

    /**
     * 上传图片
     */
    public function uploadGudsImage()
    {
        $uploadFile = D('@Model/Guds/FileUpload');
        $uploadFile->filePath = sys_get_temp_dir() . '/';
        $result = $uploadFile->fileUploadExtend();
        $this->jsonOut(array('code' => 2000, 'msg' => 'success', 'data' => $result));
    }

    /**
     * 更新商品
     */
    public function updateGudsData()
    {
        $Guds = D('@Model/Guds/Guds');
        $GudsImg = D("@Model/Guds/GudsImg");
        $getData = file_get_contents('php://input');
        $params = json_decode($getData, true);
        if (empty($params['mainId']) || empty($params['gudsId']) || empty($params['brandId'])) {
            $this->jsonOut(array('code' => '40000020', 'msg' => 'params is  error', 'data' => null));
        }
        if (!empty($params['gudsName']) || !empty($params['gudsSubName']) || !empty($params['priceType']) || !empty($params['gudsOrgp']) || !empty($params['lifeTime']) || !empty($params['unit'])) {
            $data['GUDS_NM'] = $params['gudsName'];
            $data['GUDS_CNS_NM'] = $params['gudsName'];
            $data['GUDS_VICE_NM'] = $params['gudsSubName'];
            $data['GUDS_VICE_CNS_NM'] = $params['gudsSubName'];
            $data['STD_XCHR_KIND_CD'] = $params['priceType'];
            $data['GUDS_ORGP_CD'] = $params['gudsOrgp'];
            $data['SHELF_LIFE'] = $params['lifeTime'];
            $data['VALUATION_UNIT'] = $params['unit'];
            $result = $Guds->updateGuds($data, $params['brandId'], $params['gudsId']);
            if ($result === false) {
                $this->jsonOut(array('code' => 40000003, 'msg' => 'failed', 'data' => $result));
            }
        }

        $oldName = $params['imgData']['orgtName'];
        $newName = $params['imgData']['newName'];
        $cdnAddr = $params['imgData']['cdnAddr'];
        if (!empty($params['imgData']) && !empty($oldName) && !empty($newName) && !empty($cdnAddr)) {
            $data = array();
            $data['SLLR_ID'] = $params['brandId'];
            $data['GUDS_ID'] = $params['gudsId'];
            $data['GUDS_IMG_ORGT_FILE_NM'] = $oldName;
            $data['GUDS_IMG_SYS_FILE_NM'] = $newName;
            $data['GUDS_IMG_CDN_ADDR'] = $cdnAddr;
            $result = $GudsImg->updateData($data);
        }
        $this->jsonOut(array('code' => 2000, 'msg' => 'success', 'data' => $result));
    }

    public function delete()
    {
        $this->_empty();
    }


    /**
     * 借助空操作，处理错误请求和扫描请求（扫描请求往往是暴力猜测请求，造成很大压力）。
     */
    public function _empty()
    {
        //错误请求，可能是输错了也可能是而已的扫描请求，进行404处理。
        die("Illegal Request, Please make sure you send a correct request! ");
    }

}