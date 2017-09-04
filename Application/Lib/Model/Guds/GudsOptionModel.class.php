<?php

/**
 * 品牌模型类
 * User: afanti
 * Date: 2017/7/25
 * Time: 13:40
 */
class GudsOptionModel extends RelationModel
{
    protected $trueTableName = "tb_ms_guds_opt";
    protected $_link = array();

    public function getGudsOptionById($gudsOptionId){
        if (empty($gudsOptionId))
        {
            return false;
        }
        
        $res = $this->where(" GUDS_OPT_ID ='{$gudsOptionId}' ")
            ->find();
        
        return $res;
    }
    
    /**
     * 查询指定品牌的指定商品的，指定的语言类型的SKU列表。
     * @param string $sellerId 品牌ID
     * @param int $gudsId 商品SPU id
     * @return array | bool
     */
    public function getGudsOptions($sellerId, $gudsId)
    {
        $optionGroup = $this->where(" SLLR_ID = '{$sellerId}' AND GUDS_ID = {$gudsId}")
            ->field('
            SLLR_ID,
            GUDS_ID,
            GUDS_OPT_ID,
            GUDS_OPT_CODE,
            GUDS_OPT_VAL_MPNG,
            GUDS_OPT_UPC_ID,
            GUDS_OPT_REG_STAT_CD,
            GUDS_OPT_SALE_STAT_CD,
            GUDS_OPT_MIN_ORD_QTY,
            GUDS_OPT_EXP_DT,
            GUDS_OPT_ORG_PRC,
            GUDS_OPT_SALE_PRC,
            GUDS_OPT_BELOW_SALE_PRC,
            GUDS_OPT_LENGTH,
            GUDS_OPT_WIDTH,
            GUDS_OPT_HEIGHT,
            GUDS_OPT_WEIGHT,
            GUDS_HS_CODE,
            GUDS_HS_CODE2')
            ->select();
        //GUDS_OPT_LOCK_QTY,
        //GUDS_OPT_SALE_QTY,
        //GUDS_OPT_ORDER_QTY,
        //GUDS_OPT_STK_QTY,
        return !empty($optionGroup) ? $optionGroup : false;
    }

    /**
     * 验证指定的品牌，指定mainGudsId 下面是否有SKU数据了，如果有了就不允许修改和添加。
     * @param $sellerId
     * @param $mainGudsId
     * @return bool
     */
    public function checkOptions($sellerId, $mainGudsId)
    {
        $maxOptId = $this->where("SLLR_ID = '{$sellerId}' AND GUDS_ID='{$mainGudsId}' ")->getField("MAX(GUDS_OPT_ID)");
        return !empty($maxOptId) ? $maxOptId : false;
    }

    /**
     * 构建商品的SKU组数据，按照选择的Option和OptionValue和语言版本构建SKU属性组。
     *
     * `SLLR_ID` varchar(50)  销售者ID，实际是商家ID；需要商品页面传过来，保存商品后才会有这个属性。
     * `GUDS_ID` varchar(20) 商品ID，关联商品主表；SPU的主ID，
     * `GUDS_OPT_ID` varchar(20) NOT NULL COMMENT '상품옵션아이디 | 商品SKU ID + 01，+02，+03...+99
     * `GUDS_OPT_CODE` varchar(14) DEFAULT NULL COMMENT '商品SKU自编码（同仓不能相同 不同仓可以相同）',
     *
     * @param array $data SKU属性集合
     * @return false|int
     */
    public function saveGudsOptions($data)
    {

        $optionGroup = $this->buildOptionGroup($data);
        $result = $this->saveOptionsToDB($optionGroup);
        return $result;
    }

    /**
     * 构造SKU的属性和属性值对，然后处理映射关系。
     * @param array $data SKU属性参数，来自前段传来
     * @return array
     */
    private function buildOptionGroup($data)
    {
        $sellerId = $data['sellerId'];
        $gudsId = $data['gudsId'];
        $mainGudsId = $data['mainGudsId'];
        $group = array();

        foreach ($data['optionGroup'] as $key => $option) {
            $attributes = $option['attributes'];
            unset($option['attributes']);
            $optId = $this->buildOptionId($sellerId, $mainGudsId, $key);   // SKU ID 跟着MainGudsId走的，因为所有语言公用SKU。
            $group[$optId]['SLLR_ID'] = $sellerId;
            $group[$optId]['GUDS_ID'] = $mainGudsId;//SKU表中的 GUDS_ID实际上是 MAIN_GUDS_ID。
            $group[$optId]['GUDS_OPT_ID'] = $optId;
            $group[$optId]['GUDS_OPT_CODE'] = $this->buildCustomCode($data['CAT_CD_ALP'], $data['BRND_ID'], $optId);
            $group[$optId]['GUDS_OPT_VAL_MPNG'] = $this->buildOptionMap($option);
            $group[$optId]['GUDS_OPT_UPC_ID'] = $attributes['UPC'];
            $group[$optId]['GUDS_OPT_REG_STAT_CD'] = 'N000420200'; //默认的SKU审核状态，默认为【草稿】
            $group[$optId]['GUDS_OPT_SALE_STAT_CD'] = 'N000100200';//销售状态，默认【销售准备】状态。
            $group[$optId]['GUDS_OPT_ORG_PRC'] = $attributes['PRICE'];
            $group[$optId]['GUDS_OPT_SALE_PRC'] = $attributes['PRICE'] * (1+0.5);
            $group[$optId]['GUDS_OPT_LENGTH'] = $attributes['LENGTH'];
            $group[$optId]['GUDS_OPT_WIDTH'] = $attributes['WIDTH'];
            $group[$optId]['GUDS_OPT_HEIGHT'] = $attributes['HEIGHT'];
            $group[$optId]['GUDS_OPT_WEIGHT'] = !empty($attributes['WEIGHT']) ? $attributes['WEIGHT'] : 0;
            $group[$optId]['GUDS_OPT_USE_YN'] = 'Y';
            $group[$optId]['SYS_REGR_ID'] = $_SESSION['userId'];
            $group[$optId]['SYS_CHGR_ID'] = $_SESSION['userId'];
            $group[$optId]['SYS_REG_DTTM'] = date('Y-m-d H:i:s', time());
            $group[$optId]['SYS_CHG_DTTM'] = date('Y-m-d H:i:s', time());
            $group[$optId]['GUDS_HS_CODE'] = $attributes['CR'];
            $group[$optId]['GUDS_HS_CODE2'] = $attributes['HS'];
        }

        return $group;
    }

    /**
     * 自编码生成
     * @param string $catFlag 通用类目大类编号：A-Z
     * @param $brandId
     * @param $optId
     * @return string
     */
    private function buildCustomCode($catFlag, $brandId, $optId)
    {
        $brandId = sprintf('%04d', $brandId);
        $optId = sprintf("%'X-11s", $optId);
        return $catFlag . $brandId . $optId;
    }

    /**
     * SKU id生成
     * @param $sellerId
     * @param $mainGudsId
     * @param $key
     * @return string
     */
    private function buildOptionId($sellerId, $mainGudsId, $key)
    {
        if ($key <= 99) {
            $key = sprintf('%02d', $key + 1); //因为数组索引为 0开始，所以 +1；两位数的前面补 0，三位数直接串起来。
        }
        return $mainGudsId . $key;
    }

    /**
     * 构建Option和OptionValue关系
     * @param $option
     * @return string
     */
    private function buildOptionMap($option)
    {
        //映射属性名和属性值
        $optionMap = '';
        foreach ($option as $optCode => $optVal) {
            $optionMap .= $optVal['PAR_CODE'] . ':' . $optVal['CODE'] . ';';
        }

        return trim($optionMap, ';');
    }

    /**
     * 构造保存OptionGroup的SQL语句。
     * @param $optionGroup
     * @return false|int
     */
    private function saveOptionsToDB($optionGroup)
    {
        $fields = '`SLLR_ID`, `GUDS_ID`, `GUDS_OPT_ID`,`GUDS_OPT_CODE`, `GUDS_OPT_VAL_MPNG`, `GUDS_OPT_UPC_ID`,';
        $fields .= '`GUDS_OPT_REG_STAT_CD`, `GUDS_OPT_SALE_STAT_CD`,`GUDS_OPT_ORG_PRC`,`GUDS_OPT_SALE_PRC`,';
        $fields .= '`GUDS_OPT_LENGTH`, `GUDS_OPT_WIDTH`, `GUDS_OPT_HEIGHT`, `GUDS_OPT_WEIGHT`, `GUDS_OPT_USE_YN`,';
        $fields .= '`SYS_REGR_ID`,`SYS_CHGR_ID`, `SYS_REG_DTTM`,`SYS_CHG_DTTM`,`GUDS_HS_CODE`, `GUDS_HS_CODE2`';
        $sql = 'INSERT INTO ' . $this->trueTableName . ' (' . $fields . ') VALUES ';
        foreach ($optionGroup as $key => $option) {
            $sql .= '("' . implode('","', array_values($option)) . '"),';
        }

        $sql = trim($sql, ',') . ';';
        return $this->execute($sql);
    }

    /**
     * 更新SKU信息
     * @param array $data 更新数据
     * @param string $sellerId 品牌id
     * @param number $mainGudsId 商品id
     * @return mixed
     */
    public function updateOptions($data, $sellerId, $mainGudsId)
    {
        !empty($data['optCode']) && $update['GUDS_OPT_CODE'] = $data['optCode'];
        !empty($data['PRICE']) && $update['GUDS_OPT_ORG_PRC'] = $data['PRICE'];
        !empty($data['UPC']) && $update['GUDS_OPT_UPC_ID'] = $data['UPC'];
        !empty($data['CR']) && $update['GUDS_HS_CODE'] = $data['CR'];
        !empty($data['HS']) && $update['GUDS_HS_CODE2'] = $data['HS'];
        !empty($data['LENGTH']) && $update['GUDS_OPT_LENGTH'] = $data['LENGTH'];
        !empty($data['WIDTH']) && $update['GUDS_OPT_WIDTH'] = $data['WIDTH'];
        !empty($data['HEIGHT']) && $update['GUDS_OPT_HEIGHT'] = $data['HEIGHT'];
        !empty($data['WEIGHT']) && $update['GUDS_OPT_WEIGHT'] = $data['WEIGHT'];
        $update['SYS_CHGR_ID'] = $_SESSION['userId'];
        $update['SYS_CHG_DTTM'] = date('Y-m-d H:i:s', time());

        return $this->where(" SLLR_ID = '{$sellerId}' AND GUDS_ID = {$mainGudsId} AND GUDS_OPT_ID = {$data['GUDS_OPT_ID']}")
            ->save($update);
    }

}