<?php

/**
 * 商品模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/25
 * Time: 17:32
 */
class GudsModel extends RelationModel
{
    protected $trueTableName = "tb_ms_guds";
    protected $gudsOptionsTable = 'tb_ms_guds_opt';
    protected $brandCateTable = 'tb_ms_sllr_cat';
    protected $commonCateTable = 'tb_ms_cmn_cat';
    protected $gudsChkTable = 'tb_ms_guds_chk';
    protected $commonCdTable = 'tb_ms_cmn_cd';
    static $_SAERCH_FIELD_VALS = array('gudsName' => 'g.GUDS_CNS_NM', 'gudsId' => 'g.GUDS_ID', 'mainId' => 'g.MAIN_GUDS_ID', 'Upc' => 'go.GUDS_OPT_UPC_ID', 'selfCode' => 'go.GUDS_OPT_CODE');
    static $_LANG_VALS = array(
        'cn' => 'N000920100',
        'kr' => 'N000920400',
        'en' => 'N000920200',
        'jp' => 'N000920300',
    );
    static $_CHK_STATUS_VALS = array(
        'chkwait' => 'N000420100',   //草稿
        'chking' => 'N000420200',   //审核中
        'chksucc' => 'N000420400',  //审核成功
        'chkfail' => 'N000420300',  //审核失败
    );

    /**
     * 获取商品列表
     * @param $params
     * @param $type
     * @param $offset
     * @param $limit
     * @param array $order
     * @return mixed
     */
    public function getGudsList($params, $type, $offset, $limit, $order = array('g.MAIN_GUDS_ID' => 'DESC'))
    {
        //创建日期
        $dbTimeField = 'g.PUSH_TIME';
        $tempArr = $where = $where1 = array();
        if (!empty($params['brandId'])) {
            $where['g.SLLR_ID'] = $params['brandId'];
        }

        if (!empty($params['typeVal']) && !empty($params['type']) && !empty(self::$_SAERCH_FIELD_VALS[$params['type']])) {
            if($params['type'] == 'gudsName')
            {
                $where1['g.GUDS_CNS_NM'] = array('like', "%{$params['typeVal']}%");
                $where1['g.GUDS_NM'] = array('like', "%{$params['typeVal']}%");
                $where1['_logic'] = 'OR';
            }
            else
            {
                $where[self::$_SAERCH_FIELD_VALS[$params['type']]] = $params['typeVal'];
            }

        }

        if (!empty($params['level1Id']) || !empty($params['level2Id']) || !empty($params['level3Id'])) {
            $catId = !empty($params['level3Id']) ? $params['level3Id'] : (!empty($params['level2Id']) ? $params['level2Id'] : $params['level1Id']);
            $subQuery = $this->table($this->brandCateTable)->field($this->brandCateTable . '.CAT_CD')->where("COMM_CAT_CD = '" . $catId . "'")->buildSql();
            $where['g.CAT_CD'] = array('exp', ' IN (' . $subQuery . ') ');
        }
        if (!empty($params['lang'])) {
            foreach (self::$_LANG_VALS as $key => $val) {
                if (in_array($val, $params['lang'])) {
                    $tempArr[] = $val;
                }
            }
            $where['g.LANGUAGE'] = array('in', implode(',', $tempArr));
            unset($tempArr);
        }
        //审核状态
        if (!empty($params['status'])) {
            foreach (self::$_CHK_STATUS_VALS as $key => $val) {
                if (in_array($val, $params['status'])) {
                    $tempArr[] = $val;
                }
            }
            $where['g.GUDS_REG_STAT_CD'] = array('in', implode(',', $tempArr));
            unset($tempArr);
        }
        //更新日期
        if ($params['dateType'] == 'ud') {
            $dbTimeField = 'g.updated_time';
        }
        if (!empty($params['startDate'])) {
            $where[$dbTimeField][] = array('EGT', $params['startDate']);
        }
        if (!empty($params['endDate'])) {
            $where[$dbTimeField][] = array('ELT', $params['endDate']);
        }
        if (!empty($where) && !empty($where1)) {
            $where = array($where, $where1);
        }
        elseif(!empty($where1))
        {
            $where = $where1;
        }
        //获取商品总数据

        $field = '  g.MAIN_GUDS_ID as mainId,
                    g.SLLR_ID as brandId,
                    g.GUDS_CODE as gudsCode,
                    g.GUDS_ID as gudsId,
                    g.CAT_CD as catId,
                    g.GUDS_NM as gudsName,
                    g.GUDS_CNS_NM as gudsCnName,
                    g.GUDS_VICE_NM as gudsSubName,
                    g.GUDS_VICE_CNS_NM as gudsSubCnName,
                    g.BRND_NM as brandName,
                    g.LANGUAGE as lang,
                    g.VALUATION_UNIT as unit,
                    g.GUDS_REG_STAT_CD as chkStatus,
                    g.STD_XCHR_KIND_CD as priceType,
                    MIN(go.GUDS_OPT_ORG_PRC) as price,
                    gc.CHK_CONTENT as remark';
        $subQuery = $this->table('tb_ms_guds g')
            ->field($field)
            ->join($this->gudsOptionsTable . ' go  on g.MAIN_GUDS_ID = go.GUDS_ID')
            ->join($this->gudsChkTable . ' gc on gc.MAIN_GUDS_ID = g.MAIN_GUDS_ID and gc.GUDS_ID = g.GUDS_ID')
            ->where($where)
            ->order($order)
            ->group('go.GUDS_ID,g.LANGUAGE')
            ->limit($offset, $limit)
            ->buildSql();
        if ($type == 'count') {
            $sql = 'SELECT COUNT(*) as num FROM (' . $subQuery . ') AS T';
            return $this->query($sql);
        }
        $result = $this->query($subQuery);
        return $result;
    }

    /**
     * 获取商品数据
     * @param array $where
     * @return mixed
     */
    public function getGudsData($where = array())
    {
        $field = 'SLLR_ID as brandId,
                    GUDS_CODE as gudsCode,
                    GUDS_ID as gudsId,
                    MAIN_GUDS_ID as mainId,
                    CAT_CD as catId,
                    GUDS_NM as gudsName,
                    GUDS_CNS_NM as gudsCnName,
                    GUDS_VICE_NM as gudsSubName,
                    GUDS_VICE_CNS_NM as gudsSubCnName,
                    BRND_NM as brandName,
                    VALUATION_UNIT as unit,
                    LANGUAGE as lang,
                    SHELF_LIFE as shelfLife,
                    GUDS_ORGP_CD as gudsOrgp,
                    STD_XCHR_KIND_CD as priceType';
        return $this->field($field)
            ->where($where)
            ->select();
    }

    /**
     * 通过gudsId获取商品详情
     * @param $gudsId 商品id
     */
    public function getGudsDetailByGudsId($GUDS_ID)
    {
        $where = array('GUDS_ID', $GUDS_ID);
        return $this->getGudsData($where);
    }

    /**
     * 通过MainIdAndSllrId获取商品详情
     * @param $MAIN_GUDS_ID
     * @param $SLLR_ID
     * @return mixed
     */
    public function getGudsDetailByMainIdAndSllrId($MAIN_GUDS_ID, $SLLR_ID)
    {
        $where = empty($MAIN_GUDS_ID) ? array() : array('MAIN_GUDS_ID' => array('eq', $MAIN_GUDS_ID));
        $where = empty($where) ? array() : (empty($SLLR_ID) ? $where : array_merge($where, array('SLLR_ID' => array('eq', $SLLR_ID))));
        return $this->getGudsData($where);
    }

    /**
     * 通过MAIN_GUDS_ID||/&& LANGUAGE 获取商品详情
     * @param $MAIN_GUDS_ID 主商品id
     * @param $LANGUAGE   语言
     * @return mixed
     */
    public function getGudsDetailByMgudsIdAndLang($MAIN_GUDS_ID, $LANGUAGE)
    {
        $where = empty($MAIN_GUDS_ID) ? array() : array('MAIN_GUDS_ID' => array('eq', $MAIN_GUDS_ID));
        $where = empty($where) ? array() : (empty($LANGUAGE) ? $where : array_merge($where, array('LANGUAGE' => array('eq', $LANGUAGE))));
        return $this->getGudsData($where);
    }

    /**
     * 通过GUDS_ID||/&& SLLR_ID 获取商品详情
     * @param $GUDS_ID  商品id
     * @param $SLLR_ID  品牌id
     * @return mixed
     */
    public function getGudsDetailByGudsIdAndSllrId($GUDS_ID, $SLLR_ID)
    {
        $where = empty($GUDS_ID) ? array() : array('GUDS_ID' => array('eq', $GUDS_ID));
        $where = empty($where) ? array() : (empty($SLLR_ID) ? $where : array_merge($where, array('SLLR_ID' => array('eq', $SLLR_ID))));
        return $this->getGudsData($where);
    }

    public function saveGuds($params)
    {
        $imgData = array();
        $insertSql = 'INSERT INTO ' . $this->trueTableName . '
        (
          `SLLR_ID`,
          `MAIN_GUDS_ID`,
          `GUDS_CODE`,
          `GUDS_ID`,
          `CAT_CD`,
          `GUDS_NM`,
          `GUDS_CNS_NM`,
          `GUDS_VICE_NM`,
          `GUDS_VICE_CNS_NM`,
          `BRND_NM`,
          `GUDS_PVDR_NM`,
          `GUDS_ORGP_CD`,
          `GUDS_REG_STAT_CD`,
          `GUDS_SALE_STAT_CD`,
          `GUDS_AUTH_YN`,
          `GUDS_VAT_RFND_YN`,
          `GUDS_DLVC_DESN_VAL_1`,
          `GUDS_DLVC_DESN_VAL_2`,
          `GUDS_DLVC_DESN_VAL_3`,
          `GUDS_DLVC_DESN_VAL_4`,
          `GUDS_DLVC_DESN_VAL_5`,
          `GUDS_OPT_YN`,
          `GUDS_XPSR_CNT_USE_YN`,
          `GUDS_CLCK_CNT`,
          `GUDS_MIN_CLCK_CNT`,
          `GUDS_ADD_CLCK_CNT`,
          `GUDS_SALE_QTY`,
          `GUDS_MIN_SALE_QTY`,
          `GUDS_ADD_SALE_QTY`,
          `SYS_REGR_ID`,
          `SYS_REG_DTTM`,
          `SYS_CHGR_ID`,
          `SYS_CHG_DTTM`,
          `GUDS_FLAG`,
          `GUDS_SUB_FLAG`,
          `GUDS_DLPY_YN`,
          `DELIVERY_WAREHOUSE`,
          `VALUATION_UNIT`,
          `MIN_BUY_NUM`,
          `MAX_BUY_NUM`,
          `BELONG_SEND_CAT`,
          `LOGISTICS_TYPE`,
          `LOGUSTICS_FEE`,
          `IS_NOPOSTAGE`,
          `LANGUAGE`,
          `OVER_YN`,
          `PROCUREMENT_SOURCE`,
          `TEMP1`,
          `TEMP2`,
          `updated_time`,
          `SHELF_LIFE`,
          `STD_XCHR_KIND_CD`,
          `SALE_CHANNEL`,
          `RETURN_RATE`,
          `ADDED_TAX`,
          `OVERSEAS_RATE`,
          `PUSH_STATUS`,
          `PUSH_TIME`,
          `GUDS_STAT_CD`
        ) VALUES ';
        $this->startTrans();
        $selectSql = "select max(GUDS_ID) as maxId from " . $this->trueTableName . " for update";
        $result = $this->query($selectSql);
        $mainId = $result[0]['maxId'] + 1;
        $flag = 0;
        $sqlStr = '(';
        foreach ($params['langData'] as $key => $val) {
            if (empty($val['gudsName']) && empty($val['gudsSubName'])) {
                continue;
            }
            if ($flag) {
                $sqlStr .= '),(';
            }
            $gudsId = $mainId + $flag;
            $sqlStr .= "'" . $params['brandId'] . "',";         #SLLR_ID
            $sqlStr .= "'" . $mainId . "',";                    #MAIN_GUDS_ID
            $sqlStr .= "'',";                                   #GUDS_CODE
            $sqlStr .= "'" . $gudsId . "',";                    #GUDS_ID
            $sqlStr .= "'" . $params['cateId'] . "',";           #CAT_CD
            $sqlStr .= "'" . $val['gudsName'] . "',";           #GUDS_NM
            $sqlStr .= "'" . $val['gudsName'] . "',";           #GUDS_CNS_NM
            $sqlStr .= "'" . $val['gudsSubName'] . "',";        #GUDS_VICE_NM
            $sqlStr .= "'" . $val['gudsSubName'] . "',";        #GUDS_VICE_CNS_NM
            $sqlStr .= "'" . $params['brandName'] . "',";       #BRND_NM
            $sqlStr .= "'" . $params['brandId'] . "',";         #GUDS_PVDR_NM
            $sqlStr .= "'N000410100',";                         #GUDS_ORGP_CD
            $sqlStr .= "'" . self::$_CHK_STATUS_VALS['chkwait'] . "',";#GUDS_REG_STAT_CD
            $sqlStr .= "'N000100200',";                         #GUDS_SALE_STAT_CD
            $sqlStr .= "'',";                                   #GUDS_AUTH_YN
            $sqlStr .= "'Y',";                                  #GUDS_VAT_RFND_YN
            $sqlStr .= "'',";                                   #GUDS_DLVC_DESN_VAL_1
            $sqlStr .= "'',";                                   #GUDS_DLVC_DESN_VAL_2
            $sqlStr .= "'',";                                   #GUDS_DLVC_DESN_VAL_3
            $sqlStr .= "'',";                                   #GUDS_DLVC_DESN_VAL_4
            $sqlStr .= "'',";                                   #GUDS_DLVC_DESN_VAL_5
            $sqlStr .= "'Y',";                                  #GUDS_OPT_YN
            $sqlStr .= "'N',";                                  #GUDS_XPSR_CNT_USE_YN
            $sqlStr .= "'0',";                                  #GUDS_CLCK_CNT
            $sqlStr .= "'0',";                                  #GUDS_MIN_CLCK_CNT
            $sqlStr .= "'0',";                                  #GUDS_ADD_CLCK_CNT
            $sqlStr .= "'0',";                                  #GUDS_SALE_QTY
            $sqlStr .= "'0',";                                  #GUDS_MIN_SALE_QTY
            $sqlStr .= "'0',";                                  #GUDS_ADD_SALE_QTY
            $sqlStr .= "'" . $params['brandId'] . "',";         #SYS_REGR_ID
            $sqlStr .= "'" . date('Y-m-d H:i:s') . "',"; #SYS_REG_DTTM
            $sqlStr .= "'" . $params['brandId'] . "',";         #SYS_CHGR_ID
            $sqlStr .= "'" . date('Y-m-d H:i:s') . "',"; #SYS_CHG_DTTM
            $sqlStr .= "'',";                                   #GUDS_FLAG
            $sqlStr .= "'',";                                   #GUDS_SUB_FLAG
            $sqlStr .= "'',";                                   #GUDS_DLPY_YN
            $sqlStr .= "'N000680100',";                         #DELIVERY_WAREHOUSE
            $sqlStr .= "'" . $params['unit'] . "',";            #VALUATION_UNIT
            $sqlStr .= "'',";                                   #MIN_BUY_NUM
            $sqlStr .= "'',";                                   #MAX_BUY_NUM
            $sqlStr .= "'',";                                   #BELONG_SEND_CAT
            $sqlStr .= "'',";                                   #LOGISTICS_TYPE
            $sqlStr .= "'',";                                   #LOGUSTICS_FEE
            $sqlStr .= "'N',";                                  #IS_NOPOSTAGE
            $sqlStr .= "'" . $key . "',";                       #LANGUAGE
            $sqlStr .= "'N',";                                  #OVER_YN
            $sqlStr .= "'N001030200',";                         #PROCUREMENT_SOURCE
            $sqlStr .= "'',";                                   #TEMP1
            $sqlStr .= "'',";                                   #TEMP2
            $sqlStr .= "'" . date('Y-m-d H:i:s') . "',"; #updated_time
            $sqlStr .= "'" . $params['lifeTime'] . "',";        #SHELF_LIFE
            $sqlStr .= "'N000590200',";                         #STD_XCHR_KIND_CD
            $sqlStr .= "'',";                                   #SALE_CHANNEL
            $sqlStr .= "'',";                                   #RETURN_RATE
            $sqlStr .= "'',";                                   #ADDED_TAX
            $sqlStr .= "'',";                                   #OVERSEAS_RATE
            $sqlStr .= "'N000890100',";                         #PUSH_STATUS
            $sqlStr .= "'" . date('Y-m-d H:i:s') . "',"; #PUSH_TIME
            $sqlStr .= "'N001180100'";                          #GUDS_STAT_CD
            $flag++;
            $commData['langData'][$key] = array('mainId' => $mainId, 'sllrId' => $params['brandId'], 'gudsId' => $gudsId);
        }
        $commData['mainId'] = $mainId;
        $sqlStr .= ')';
        $insertSql .= $sqlStr;
        $result = $this->execute($insertSql);
        if (empty($this->getDbError())) {
            $this->commit();
            return $commData;
        } else {
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新商品数据
     * @param array $data 待更新数据
     * @param string $SLLR_ID 品牌编号
     * @param int $GUDS_ID 商品SPU编号
     * @return mixed
     */
    public function updateGuds($data, $SLLR_ID, $GUDS_ID)
    {
        if (empty($data) || empty($SLLR_ID) || empty($GUDS_ID)) {
            return false;
        }

        !empty($data['GUDS_NM']) && $update['GUDS_NM'] = $data['GUDS_NM'];
        !empty($data['GUDS_CNS_NM']) && $update['GUDS_CNS_NM'] = $data['GUDS_CNS_NM'];
        !empty($data['GUDS_VICE_NM']) && $update['GUDS_VICE_NM'] = $data['GUDS_VICE_NM'];
        !empty($data['GUDS_VICE_CNS_NM']) && $update['GUDS_VICE_CNS_NM'] = $data['GUDS_VICE_CNS_NM'];
        !empty($data['BRND_NM']) && $update['BRND_NM'] = $data['BRND_NM'];
        !empty($data['GUDS_PVDR_NM']) && $update['GUDS_PVDR_NM'] = $data['GUDS_PVDR_NM'];
        !empty($data['GUDS_ORGP_CD']) && $update['GUDS_ORGP_CD'] = $data['GUDS_ORGP_CD'];
        !empty($data['GUDS_REG_STAT_CD']) && $update['GUDS_REG_STAT_CD'] = $data['GUDS_REG_STAT_CD'];
        !empty($data['GUDS_SALE_STAT_CD']) && $update['GUDS_SALE_STAT_CD'] = $data['GUDS_SALE_STAT_CD'];
        !empty($data['GUDS_AUTH_YN']) && $update['GUDS_AUTH_YN'] = $data['GUDS_AUTH_YN'];
        !empty($data['GUDS_VAT_RFND_YN']) && $update['GUDS_VAT_RFND_YN'] = $data['GUDS_VAT_RFND_YN'];
        !empty($data['GUDS_DLVC_DESN_VAL_1']) && $update['GUDS_DLVC_DESN_VAL_1'] = $data['GUDS_DLVC_DESN_VAL_1'];
        !empty($data['GUDS_DLVC_DESN_VAL_2']) && $update['GUDS_DLVC_DESN_VAL_2'] = $data['GUDS_DLVC_DESN_VAL_2'];
        !empty($data['GUDS_DLVC_DESN_VAL_3']) && $update['GUDS_DLVC_DESN_VAL_3'] = $data['GUDS_DLVC_DESN_VAL_3'];
        !empty($data['GUDS_DLVC_DESN_VAL_4']) && $update['GUDS_DLVC_DESN_VAL_4'] = $data['GUDS_DLVC_DESN_VAL_4'];
        !empty($data['GUDS_DLVC_DESN_VAL_5']) && $update['GUDS_DLVC_DESN_VAL_5'] = $data['GUDS_DLVC_DESN_VAL_5'];
        !empty($data['GUDS_OPT_YN']) && $update['GUDS_OPT_YN'] = $data['GUDS_OPT_YN'];
        !empty($data['GUDS_XPSR_CNT_USE_YN']) && $update['GUDS_XPSR_CNT_USE_YN'] = $data['GUDS_XPSR_CNT_USE_YN'];
        !empty($data['GUDS_CLCK_CNT']) && $update['GUDS_CLCK_CNT'] = $data['GUDS_CLCK_CNT'];
        !empty($data['GUDS_MIN_CLCK_CNT']) && $update['GUDS_MIN_CLCK_CNT'] = $data['GUDS_MIN_CLCK_CNT'];
        !empty($data['GUDS_ADD_CLCK_CNT']) && $update['GUDS_ADD_CLCK_CNT'] = $data['GUDS_ADD_CLCK_CNT'];
        !empty($data['GUDS_SALE_QTY']) && $update['GUDS_SALE_QTY'] = $data['GUDS_SALE_QTY'];
        !empty($data['GUDS_MIN_SALE_QTY']) && $update['GUDS_MIN_SALE_QTY'] = $data['GUDS_MIN_SALE_QTY'];
        !empty($data['GUDS_ADD_SALE_QTY']) && $update['GUDS_ADD_SALE_QTY'] = $data['GUDS_ADD_SALE_QTY'];
        !empty($data['SYS_CHGR_ID']) && $update['SYS_CHGR_ID'] = $data['SYS_CHGR_ID'];
        !empty($data['SYS_CHG_DTTM']) && $update['SYS_CHG_DTTM'] = $data['SYS_CHG_DTTM'];
        !empty($data['GUDS_FLAG']) && $update['GUDS_FLAG'] = $data['GUDS_FLAG'];
        !empty($data['GUDS_DLPY_YN']) && $update['GUDS_DLPY_YN'] = $data['GUDS_DLPY_YN'];
        !empty($data['DELIVERY_WAREHOUSE']) && $update['DELIVERY_WAREHOUSE'] = $data['DELIVERY_WAREHOUSE'];
        !empty($data['VALUATION_UNIT']) && $update['VALUATION_UNIT'] = $data['VALUATION_UNIT'];
        !empty($data['MIN_BUY_NUM']) && $update['MIN_BUY_NUM'] = $data['MIN_BUY_NUM'];
        !empty($data['MAX_BUY_NUM']) && $update['MAX_BUY_NUM'] = $data['MAX_BUY_NUM'];
        !empty($data['BELONG_SEND_CAT']) && $update['BELONG_SEND_CAT'] = $data['BELONG_SEND_CAT'];
        !empty($data['LOGISTICS_TYPE']) && $update['LOGISTICS_TYPE'] = $data['LOGISTICS_TYPE'];
        !empty($data['LOGUSTICS_FEE']) && $update['LOGUSTICS_FEE'] = $data['LOGUSTICS_FEE'];
        !empty($data['IS_NOPOSTAGE']) && $update['IS_NOPOSTAGE'] = $data['IS_NOPOSTAGE'];
        !empty($data['OVER_YN']) && $update['OVER_YN'] = $data['OVER_YN'];
        !empty($data['PROCUREMENT_SOURCE']) && $update['PROCUREMENT_SOURCE'] = $data['PROCUREMENT_SOURCE'];
        !empty($data['SHELF_LIFE']) && $update['SHELF_LIFE'] = $data['SHELF_LIFE'];
        !empty($data['STD_XCHR_KIND_CD']) && $update['STD_XCHR_KIND_CD'] = $data['STD_XCHR_KIND_CD'];
        !empty($data['SALE_CHANNEL']) && $update['SALE_CHANNEL'] = $data['SALE_CHANNEL'];
        !empty($data['RETURN_RATE']) && $update['RETURN_RATE'] = $data['RETURN_RATE'];
        !empty($data['ADDED_TAX']) && $update['ADDED_TAX'] = $data['ADDED_TAX'];
        !empty($data['OVERSEAS_RATE']) && $update['OVERSEAS_RATE'] = $data['OVERSEAS_RATE'];
        !empty($data['PUSH_STATUS']) && $update['PUSH_STATUS'] = $data['PUSH_STATUS'];
        !empty($data['PUSH_TIME']) && $update['PUSH_TIME'] = $data['PUSH_TIME'];
        !empty($data['GUDS_STAT_CD']) && $update['GUDS_STAT_CD'] = $data['GUDS_STAT_CD'];
        $update['updated_time'] = date('Y-m-d H:i:s');
        return $this->where(" SLLR_ID = '{$SLLR_ID}' AND GUDS_ID={$GUDS_ID} ")
            ->save($update);

    }

    /**
     * 同步修改商品状态
     * @param $MAIN_GUDS_ID
     * @param $GUDS_REG_STAT_CD
     */
    public function updateGudsStatus($MAIN_GUDS_ID, $GUDS_ID, $GUDS_REG_STAT_CD)
    {
        $this->where(array('MAIN_GUDS_ID' => $MAIN_GUDS_ID, 'GUDS_ID' => $GUDS_ID))->save(array('GUDS_REG_STAT_CD' => $GUDS_REG_STAT_CD));
    }

    public function dealWithCateLever($CD)
    {
        $key1 = $key2 = $key3 = '';
        $cd = ltrim($CD, 'C');
        $level1 = (int)($cd / 10000000);
        $mod1 = $cd % 10000000;
        $key1 = "C0" . $level1 * 10000000;
        if ($level1 && $mod1 != 0) {
            $level2 = (int)($mod1 / 10000);
            $mod2 = $mod1 % 10000;
            $key2 = "C0" . ($level1 * 10000000 + $level2 * 10000);
            if ($level2 && $mod2 != 0) {
                $level3 = $mod2 / 10;
                $key3 = "C0" . ($level1 * 10000000 + $level2 * 10000 + $level3 * 10);
            }
        }
        return array(
            'catLev1' => $key1,
            'catLev2' => $key2,
            'catLev3' => $key3,
        );
    }


}