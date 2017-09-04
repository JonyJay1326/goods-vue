<?php

/**
 * 商品品牌模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/25
 * Time: 13:56
 */
class  BrandModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_brnd_str';
    protected $relateTableName = 'tb_ms_sllr_cat';

    /**
     *品牌列表
     * @param $where 品牌id
     * @param string $filed
     * @param int $limit
     * @return mixed
     */
    protected $field = 'SLLR_ID as brandId,BRND_STR_NM as brandCnName,BRND_STR_KR_NM as brandKrName,BRND_STR_JPA_NM as brandJpName,BRND_STR_ENG_NM as brandEnName';

    public function searchBrandList($params, $type, $offset, $limit, $order = array('g.BRND_ID' => 'DESC'))
    {
        //创建日期
        $dbTimeField = 'g.SYS_CHG_DTTM';
        $tempArr = $where = $where1 = array();
        if (!empty($params['authType'])) {
            $where['g.VEST_WAY'] = $params['authType'];
        }
        if (!empty($params['brandStatus'])) {
            $where['g.BRND_STR_STAT_CD'] = array('in', implode(',', $params['brandStatus']));
        }
        if (!empty($params['brandId'])) {
            $where['g.SLLR_ID'] = $params['brandId'];
        }

        /*//审核状态
        if (!empty($params['status'])) {
            foreach (self::$_CHK_STATUS_VALS as $key => $val) {
                if (in_array($val, $params['status'])) {
                    $tempArr[] = $val;
                }
            }
            $where['g.GUDS_REG_STAT_CD'] = array('in', implode(',', $tempArr));
            unset($tempArr);
        }*/
        //更新日期
        if ($params['datetype'] == 'ud') {
            $dbTimeField = 'g.updated_time';
        }
        if (!empty($params['startDate'])) {
            $where[$dbTimeField][] = array('EGT', $params['startDate']);
        }
        if (!empty($params['endDate'])) {
            $where[$dbTimeField][] = array('ELT', $params['endDate']);
        }
        //获取商品总数据

        $field = '  g.SLLR_ID as brandId,
                    g.BRND_STR_NM as brandCnName,
                    g.BRND_STR_KR_NM as brandKrName,
                    g.BRND_STR_JPA_NM as brandJpName,
                    g.BRND_STR_ENG_NM as brandEnName,
                    g.SYS_REG_DTTM as createTime,
                    g.updated_time as updatedTime,
                    g.VEST_WAY as authType,
                    g.BRND_STR_STAT_CD as brandStatus';

        if ($type == 'count') {
            return $this->table($this->trueTableName . ' g')->field('SLLR_ID')->where($where)->count();
        }
        if ($type == 'allData') {
            return $this->table($this->trueTableName . ' g')
                ->field($field)
                ->where($where)
                ->order($order)
                ->select();
        }
        $result = $this->table($this->trueTableName . ' g')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($offset, $limit)
            ->select();
        return $result;

    }

    public function getBrandList($where = array(), $limit = 50)
    {
        if (empty($limit)) {
            return $this->field($this->field)->where($where)->select();
        }
        return $this->field($this->field)->where($where)->limit($limit)->select();
    }

    /**
     *通过$SLLR_ID获取品牌列表
     * @param $SLLR_ID 品牌id
     * @param string $filed
     * @param int $limit
     * @return mixed
     */
    public function getBrandListBySllrId($SLLR_ID, $limit = 50)
    {
        $where['SLLR_ID'] = $SLLR_ID;
        return $this->getBrandList($where, $limit);
    }

    /**
     * 根据指定的品牌ID (实际上是名称，原来的数据表设计很不专业)
     * @param $sellerId
     * @return bool
     */
    public function getBrand($sellerId)
    {
        if (empty($sellerId)) {
            return false;
        }

        return $this->where(" SLLR_ID = '{$sellerId}' ")->find();
    }

    /**
     * 添加品牌数据
     * @param $data
     * @return mixed
     */
    public function addBrandData($data)
    {
        $data['SYS_REGR_ID'] = $data['SLLR_ID'];
        $data['SYS_REG_DTTM'] = date("Y-m-d H:i:s");
        $data['SYS_CHGR_ID'] = $data['SLLR_ID'];
        $data['SYS_CHG_DTTM'] = date("Y-m-d H:i:s");
        $data['updated_time'] = date("Y-m-d H:i:s");
        return $this->add($data);
    }

    /**
     * 保存品牌信息更改数据
     * @param $params
     * @return mixed
     */
    public function updateBrandData($params)
    {
        $where['SLLR_ID'] = $params['SLLR_ID'];
        !empty($params['BRND_STR_NM']) && $data['BRND_STR_NM'] = $params['BRND_STR_NM'];
        !empty($params['BRND_STR_KR_NM']) && $data['BRND_STR_KR_NM'] = $params['BRND_STR_KR_NM'];
        !empty($params['BRND_STR_JPA_NM']) && $data['BRND_STR_JPA_NM'] = $params['BRND_STR_JPA_NM'];
        !empty($params['BRND_STR_ENG_NM']) && $data['BRND_STR_ENG_NM'] = $params['BRND_STR_ENG_NM'];
        !empty($params['BRND_STR_STAT_CD']) && $data['BRND_STR_STAT_CD'] = $params['BRND_STR_STAT_CD'];
        !empty($params['BRND_STR_OP_OPR_NM']) && $data['BRND_STR_OP_OPR_NM'] = $params['BRND_STR_OP_OPR_NM'];
        !empty($params['BRND_STR_OP_OPR_CP_NO']) && $data['BRND_STR_OP_OPR_CP_NO'] = $params['BRND_STR_OP_OPR_CP_NO'];
        !empty($params['BRND_STR_OP_OPR_CMP_TEL_NO']) && $data['BRND_STR_OP_OPR_CMP_TEL_NO'] = $params['BRND_STR_OP_OPR_CMP_TEL_NO'];
        !empty($params['BRND_STR_OP_OPR_EML']) && $data['BRND_STR_OP_OPR_EML'] = $params['BRND_STR_OP_OPR_EML'];
        !empty($params['BRND_STR_BACT_OPR_NM']) && $data['BRND_STR_BACT_OPR_NM'] = $params['BRND_STR_BACT_OPR_NM'];
        !empty($params['BRND_STR_BACT_OPR_CP_NO']) && $data['BRND_STR_BACT_OPR_CP_NO'] = $params['BRND_STR_BACT_OPR_CP_NO'];
        !empty($params['BRND_STR_BACT_OPR_CMP_TEL_NO']) && $data['BRND_STR_BACT_OPR_CMP_TEL_NO'] = $params['BRND_STR_BACT_OPR_CMP_TEL_NO'];
        !empty($params['BRND_STR_BACT_OPR_EML']) && $data['BRND_STR_BACT_OPR_EML'] = $params['BRND_STR_BACT_OPR_EML'];
        !empty($params['BRND_AUTH_YN']) && $data['BRND_AUTH_YN'] = $params['BRND_AUTH_YN'];
        !empty($params['ENST_REQ_DT']) && $data['ENST_REQ_DT'] = $params['ENST_REQ_DT'];
        !empty($params['ENST_APRV_DT']) && $data['ENST_APRV_DT'] = $params['ENST_APRV_DT'];
        !empty($params['ENST_APRV_DNY_RSN_CONT']) && $data['ENST_APRV_DNY_RSN_CONT'] = $params['ENST_APRV_DNY_RSN_CONT'];
        !empty($params['MULTI_BRND_SLLR_ID']) && $data['MULTI_BRND_SLLR_ID'] = $params['MULTI_BRND_SLLR_ID'];
        !empty($params['MULTI_BRND_STR_ENG_NM']) && $data['MULTI_BRND_STR_ENG_NM'] = $params['MULTI_BRND_STR_ENG_NM'];
        !empty($params['BRND_ORGP_CD']) && $data['BRND_ORGP_CD'] = $params['BRND_ORGP_CD'];
        !empty($params['BRND_INTD_CONT']) && $data['BRND_INTD_CONT'] = $params['BRND_INTD_CONT'];
        !empty($params['VEST_WAY']) && $data['VEST_WAY'] = $params['VEST_WAY'];
        !empty($params['SALE_CHANNEL']) && $data['SALE_CHANNEL'] = $params['SALE_CHANNEL'];
        !empty($params['ENST_APRV_DNY_RSN_CONT']) && $data['ENST_APRV_DNY_RSN_CONT'] = $params['ENST_APRV_DNY_RSN_CONT'];
        $data['updated_time'] = date('Y-m-d H:i:s');
        $data['SYS_CHG_DTTM'] = date('Y-m-d H:i:s');
        return $this->where($where)->save($data);
    }

}