<?php
/**
 * Created by PhpStorm.
 * User: afanti
 * Date: 2017/7/24
 * Time: 14:45
 */

class GudsOptionsAction extends BaseAction
{

    /**
     * 为了避免权限验证导致的前段调试麻烦，暂时覆盖掉父类的登录验证
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * SKU 列表，一个商品的SKU列表。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=getOptionList&gudsId=80006184&mainGudsId=80006184&sellerId=ahc
     */
    public function getOptionList()
    {
        $sellerId = I('sellerId');
        $gudsId = I('gudsId');
        $mainGudsId = I('mainGudsId');//独立语言之外的
        $languageType = I('lang', 'N000920100');
        if (empty($gudsId) || empty($sellerId)) {
            $this->jsonOut(array('code' => 400, 'msg' => L('INVALID_PARAMS'), 'data' => null));
        }

        $optionMaps = $allOptions = array();
        $GudsOptionModel = new GudsOptionModel();
        $OptionMap = new OptionMapModel();
        $gudsModel = D('@Model/Guds/Guds');
        $gudsInfo = $gudsModel->getGudsData('GUDS_ID=' . $gudsId);
        $languageType = $gudsInfo[0]['lang'];
        $optionGroup = $GudsOptionModel->getGudsOptions($sellerId, $mainGudsId);
        $skuMaps = $OptionMap->getOptionMaps($optionGroup);
        $allOptions = $OptionMap->getOptionByCodeMap($skuMaps, $languageType);

        //解析和映射出SKU列表数据。
        foreach ($optionGroup as $key => $option) {
            $optMap = $option['GUDS_OPT_VAL_MPNG'];
            $optId = $option['GUDS_OPT_ID'];
            //分离Option和OptionValue对组合(8001:800171;8002:800242)，转为逗号分隔的Code字符串，查询所有属性信息.
            //$optionMaps[$optId] = $OptionMap->optionMapTranslate($optMap, $allOptions);
            $optionGroup[$key]['optionMaps'] = $OptionMap->optionMapTranslate($optMap, $allOptions);
            $optionGroup[$key]['GUDS_OPT_ORG_PRC'] = sprintf('%0.2f',$option['GUDS_OPT_ORG_PRC']);
        }

        //SKU配置工具栏信息，选中的属性名列表，和属性值列表；
        $list = $OptionMap->optionMapParse($allOptions);
        $returnData = array('optionList' => $optionGroup, 'selector' => $list);
        $result = array('code' => 200, 'msg' => 'success', 'data' => $returnData);
        $this->jsonOut($result);
    }

    /**
     * 添加SKU页面，读取基本的SKU属性信息，所有的基本属性值。
     * 包括：币种，产地，Option属性，Option属性值
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=getBasicOptions
     *
     */
    public function getBasicOptions()
    {
        $Directory = D('Directory');
        $currency = $Directory->getCurrency();
        $origin = $Directory->getOrigin();

        $OptionMap = D('OptionMap');
        $options = $OptionMap->getOptions(LANG_CODE);
        $data = array('currency' => $currency, 'origin' => $origin, 'options' => $options);

        $result = array('code' => 200, 'msg' => 'success', 'data' => $data);
        $this->jsonOut($result);
    }

    /**
     * 获取指定语言版本的，指定SKU属性的 属性值列表。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=getOptionValues&selectedOptId=8007
     *
     * @params number selectedOptId 请求参数为，添加SKU属性时选取的SKU 属性 ID (CODE).
     * @throws Exception
     */
    public function getOptionValues()
    {
        $optionId = I('selectedOptId');
        if (empty($optionId)) {
            $this->jsonOut(array('code' => 400, 'msg' => L('INVALID_PARAMS'), 'data' => null));
        }

        $OptionMap = D('OptionMap');
        $optionValues = $OptionMap->getOptionValues($optionId, LANG_CODE);

        $result = array('code' => 200, 'msg' => 'success', 'data' => $optionValues);
        $this->jsonOut($result);
    }

    /**
     * 根据选择的SKU属性名称组和SKU属性值组，构建SKU组合，生成列表用于填写其他信息。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=getOptionGroup
     *
     * 请求方式为：JSON格式参数， 异步请求。
     * @params json 参数格式为：[{'8001':'800126,800127,800130','8002':'800352,800353'}]
     */
    public function getOptionGroup()
    {
        //[{'8001':'800126,800127,800130','8002':'800352,800353'}]
        $optionList = file_get_contents('php://input');
        $selected = json_decode($optionList, true);
        //测试数据 Fixme Delete
        //$selected = array('8001'=>'800126,800127,800130','8002'=>'800352,800353');
        if (empty($selected) || !is_array($selected)) {
            $this->jsonOut(array('code' => 4001, 'msg' => '参数错误', 'data' => $optionList));
        }

        $OptionMap = D('OptionMap');
        $OptionMap = new OptionMapModel();
        //组合所有属性名的CODE码和属性值的CODE码，语言默认中文。
        $optionData = $OptionMap->getOptionByCodeMap($selected, LANG_CODE);
        //$optionData = array();
        //foreach($selected as $optNameCode => $optValueCode)
        //{
        //读取所有CODE码数据，用于查询出所有CODE码对应的数据，然后分组构建SKU组合。
        //因为Option和OptionValue的Code码之间并没有唯一的约束关系，所以导致必须分开查询。
        //$optionData += $OptionMap->getOptionByCodes($optNameCode, $optValueCode);

        //}

        //分离SKU属性名称，并根据不同的SKU属性名称 组合成单独的属性值数组。
        $optionNames = $optionValues = $optionGroup = [];
        foreach ($optionData as $code => $option) {
            if (empty($option['PAR_CODE'])) //Option name 数组分离。
            {
                $optionNames[$code] = $option;
            } else // 分离属性值，并且用属性名的CODE作为数组索引，拆分出不同属性名对应的属性值到单独数组。
            {
                $optionValues[$option['PAR_CODE']][$option['CODE']] = $option; //@important 这里要注意索引，不可默认
            }
        }

        //根据属性值构造SKU属性组。
        $optionGroup = $OptionMap->CartesianGroup($optionValues);
        $data = array('optionNames' => $optionNames, 'optionGroup' => $optionGroup);
        $result = array('code' => 200, 'msg' => 'success', 'data' => $data);
        $this->jsonOut($result);
    }

    /**
     * 根据选定的Option属性，和关键词搜索，该属性下的属性值。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=searchOptionValue&optNameCode=8001&keyword=110
     */
    public function searchOptionValue()
    {
        $optionNameCode = I('optNameCode');
        $keyword = I('keyword');

        if (empty($optionNameCode)) {
            $this->jsonOut(array('code' => 4002, 'msg' => L('INVALID_PARAMS'), 'data' => null));
        }

        $OptionMap = D('OptionMap');
        $optionValues = $OptionMap->searchOptionValues($optionNameCode, $keyword);
        $result = array('code' => 200, 'msg' => 'success', 'data' => $optionValues);
        $this->jsonOut($result);
    }


    /**
     * 添加新的SKU值数据，支持多组同时添加。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=addNewOptionValue
     */
    public function addNewOptionValue()
    {
        $params = file_get_contents('php://input');

        //测试数据 Fixme Delete
//        $params = '{
//          "optNameCode": "8001",
//          "optValues": [
//            {
//              "KR": "KR-TEST-1",
//              "CN": "CN-TEST-1",
//              "EN": "EN-TEST-1",
//              "JP": "JP-TEST-1"
//            },
//            {
//              "KR": "KR-TEST-2",
//              "CN": "CN-TEST-2",
//              "EN": "EN-TEST-2",
//              "JP": "JP-TEST-2"
//            },
//            {
//              "KR": "KR-TEST-3",
//              "CN": "CN-TEST-3",
//              "EN": "EN-TEST-3",
//              "JP": "JP-TEST-3"
//            },
//            {
//              "KR": "KR-TEST-4",
//              "CN": "CN-TEST-4",
//              "EN": "EN-TEST-4",
//              "JP": "JP-TEST-4"
//            }
//          ]
//        }';
        $params = json_decode($params, true);
        if (empty($params) || empty($params['optNameCode']) || empty($params['optValues'])) {
            $this->jsonOut(array('code' => 4003, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
        }

        //$multipleLang = $optValKr . '/' . $optValCn . '/' . $optValEn . '/' . $optValJp;
        $optValueModel = new OptionValueModel();
        list($result, $lastValues) = $optValueModel->createOptionValues($params['optNameCode'], $params['optValues']);
        if ($result <= 0 || $result == false) {
            $this->jsonOut(array('code' => 4004, 'msg' => L('INVALID_PARAMS'), 'data' => null));
        }
        $optionMapModel = new OptionMapModel();
        $result = $optionMapModel->createNewValues($params['optNameCode'], $lastValues);
        if ($result <= 0 || $result == false) {
            $this->jsonOut(array('code' => 4005, 'msg' => L('SYSTEM_ERROR'), 'data' => null));
        }

        $this->jsonOut(array('code' => 200, 'msg' => 'Success', 'data' => null));
    }


    /**
     * 构建新的SKU信息，根据选择的SKU属性情况，会是多条生成。
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=create
     */
    public function create()
    {
        $params = file_get_contents('php://input');
        //测试参数格式
//        $params = '{
//    "sellerId": "ahc",
//    "mainGudsId":"80006184",
//    "gudsId": "80006184",
//    "origin": "N909837923",
//    "currency": "N0239238",
//    "optionGroup": [
//        {
//            "800126": {
//            "CODE": "800126",
//                "PAR_CODE": "8001",
//                "VAL": "230",
//                "ALL_VAL": "230/230/en/JPA",
//                "TYPE": "N000960200"
//            },
//            "800352": {
//            "CODE": "800352",
//                "PAR_CODE": "8002",
//                "VAL": "1JPA",
//                "ALL_VAL": "1호/1号/en/JPA",
//                "TYPE": "N000960200"
//            },
//            "attributes": {
//                "PRICE" : "10.12",
//                "UPC": "8809091729942",
//                "CR": "9876543210",
//                "HS": "8976543210",
//                "LENGTH": "30",
//                "WIDTH": "10",
//                "HEIGHT":"12",
//                "WEIGHT": "200"
//            }
//        },
//        {
//            "800126": {
//            "CODE": "800126",
//                "PAR_CODE": "8001",
//                "VAL": "230",
//                "ALL_VAL": "230/230/en/JPA",
//                "TYPE": "N000960200"
//            },
//            "800353": {
//            "CODE": "800353",
//                "PAR_CODE": "8002",
//                "VAL": "2JPA",
//                "ALL_VAL": "2호/2号/en/JPA",
//                "TYPE": "N000960200"
//            },
//            "attributes": {
//                "PRICE" : "10.12",
//                "UPC": "8809091729942",
//                "CR": "9876543210",
//                "HS": "8976543210",
//                "LENGTH": "30",
//                "WIDTH": "10",
//                "HEIGHT":"12",
//                "WEIGHT": "200"
//            }
//        }
//    ]
//}';
        $options = json_decode($params, true);
        if (empty($options) || empty($options['optionGroup'])) {
            $this->jsonOut(array('code' => 4006, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
        }

        if (empty($options['sellerId']) || empty($options['gudsId']) || empty($options['mainGudsId'])) {
            $this->jsonOut(array('code' => 4007, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
        }

        //验证属性 必填项
        $firstSku = array(); //这里吧第一个SKU提取出来，因为SPU只有一条记录，体积属性，没法保存每一个SKU的，所以去第一条。
        foreach ($options['optionGroup'] as $key => $values) {
            if (empty($values['attributes']) || empty($values['attributes']['PRICE'])) {
                $this->jsonOut(array('code' => 4008, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
            }
        }


        //更新SPU的产地和币种信息
        $sellerId = $options['sellerId'];
        $gudsId = $options['gudsId'];
        $mainGudsId = $options['mainGudsId'];

        //如果一个商品 存在SKU了就不允许添加新的SKU属性了，也不允许修改现有的SKU属性组
        $GudsOptionModel = D('GudsOption');
        $GudsOptionModel = new GudsOptionModel();
        $res = $GudsOptionModel->checkOptions($sellerId, $mainGudsId);
        if (false !== $res) {
            $this->jsonOut(array('code' => 4009, 'msg' => L('CAN_NOT_MORE_SKU'), 'data' => null));
        }

        $gudsUpdate['GUDS_ORGP_CD'] = $options['origin'];
        $gudsUpdate['STD_XCHR_KIND_CD'] = $options['currency'];
        $gudsUpdate['GUDS_DLVC_DESN_VAL_1'] = $options['optionGroup'][0]['LENGTH'];
        $gudsUpdate['GUDS_DLVC_DESN_VAL_2'] = $options['optionGroup'][0]['WIDTH'];
        $gudsUpdate['GUDS_DLVC_DESN_VAL_3'] = $options['optionGroup'][0]['HEIGHT'];
        $gudsUpdate['GUDS_DLVC_DESN_VAL_4'] = $options['optionGroup'][0]['WEIGHT'];
        $GudsModel = D('@Model/Guds/Guds');
        //update guds info, 这里一定是更新 GUDS_ID的商品，就是特定语言的商品的产地和币种。
        $res = $GudsModel->updateGuds($gudsUpdate, $sellerId, $gudsId);
        $BrandModel = D('Brand');
        $categoryModel = new CategoryModel();

        $brand = $BrandModel->getBrand($sellerId);
        //SKU自编码的类目应关联第一次发布的商品GUDS_ID也就是MAIN_GUDS_ID的类目，即GUDS表中GUDS_ID=此处参数MainGUdsID的
        $options['CAT_CD_ALP'] = $categoryModel->getCateFlag($sellerId, $mainGudsId);
        $options['BRND_ID'] = $brand['BRND_ID'];

        //save SKU
        $result = $GudsOptionModel->saveGudsOptions($options);
        if ($result === false || empty($result)) {
            $this->jsonOut(array('code' => 500, 'msg' => L('SYSTEM_ERROR'), 'data' => null));
        }

        $this->jsonOut(array('code' => 200, 'msg' => 'success', 'data' => null));
    }

    /**
     * 修改SKU信息
     * @see http://erp.stage.com/index.php?g=guds&m=gudsOptions&a=modify
     */
    public function modify()
    {
        $params = file_get_contents("php://input");
//        $params = '{
//            "sellerId": "ahc",
//            "mainGudsId" : "80006184",
//            "gudsId": "80006184",
//            "attributes": [
//                    {
//                        "GUDS_OPT_ID": "8000618401",
//                        "PRICE":"25.21",
//                        "UPC": "8809091729942",
//                        "CR": "9876543210",
//                        "HS": "8976543210",
//                        "LENGTH": "30",
//                        "WIDTH": "10",
//                        "HEIGHT": "12",
//                        "WEIGHT": "200"
//                    }
//                ]
//            }';

        $options = json_decode($params, true);
        if (empty($options) || empty($options['attributes'])) {
            $this->jsonOut(array('code' => 4010, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
        }

        if (empty($options['sellerId']) || empty($options['gudsId']) || empty($options['mainGudsId'])) {
            $this->jsonOut(array('code' => 4011, 'msg' => L('INVALID_PARAMS'), 'data' => $params));
        }


        $sellerId = $options['sellerId'];
        $mainGudsId = $options['mainGudsId'];
        $GudsOption = D('GudsOption');
        try {
            foreach ($options['attributes'] as $key => $item) {
                $res = $GudsOption->updateOptions($item, $sellerId, $mainGudsId);
            }
        } catch (Exception $e) {
            $this->jsonOut(array('code' => 500, 'msg' => L('SYSTEM_ERROR'), 'data' => $e));
        }

        $this->jsonOut(array('code' => 200, 'msg' => 'success', 'data' => null));
    }

    /**
     * Delete a SKU line, Logic delete, but real delete form DB.
     */
    public function delete()
    {
        die('SKU 不允许删除');
    }

    /**
     * Change the stock of indicate SKU.
     */
    public function changeStock()
    {

    }

    /**
     * Upload images of SKU.
     */
    public function upload()
    {

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