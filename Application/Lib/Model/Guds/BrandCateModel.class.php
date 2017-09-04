<?php

/**
 * 品牌分类模块
 * Created by PhpStorm.
 * User: lscm
 * Date: 2017/7/27
 * Time: 15:42
 */
class BrandCateModel extends RelationModel
{
    protected $trueTableName = 'tb_ms_sllr_cat';
    
    /**
     * 获取品牌分类数据
     * @param array $where
     * @param string $filed
     * @return mixed
     */
    protected $field = array(
        'SLLR_ID' => 'brandId',
        'CAT_CD' => 'catId',
        'COMM_CAT_CD' => 'commCatId',
        'CAT_NM' => 'catName',
        'CAT_KOR_NM' => 'catKrName',
        'CAT_SORT_NO' => 'catSort',
        'CAT_MPNG_YN' => 'catMpng',
    );
    
    public function getBrandCateData($where = array())
    {
        #$where['CAT_MPNG_YN'] = 'Y';
        return $this->field($this->field)->where($where)->select();
    }
    
    /**
     * 获取品牌分类数据通过SllrId和CatCd
     * @param $SLLR_ID 品牌id
     * @param $CAT_CD  分类id
     * @return mixed
     */
    public function getBrandCateDataBySllrIdAndCatCd($SLLR_ID, $CAT_CD)
    {
        #$where['COMM_CAT_CD'] = array('neq', '');
        #$where['CAT_MPNG_YN'] = 'Y';
        $where['SLLR_ID'] = $SLLR_ID;
        $where['CAT_CD'] = $CAT_CD;
        return $this->field($this->field)->where($where)->find();
    }
    
    /**
     * 品牌分类详细信息
     * @param $SLLR_ID 品牌id
     * @param $CAT_CD  分类
     * @return mixed
     */
    public function getOneBrandCateData($SLLR_ID, $CAT_CD)
    {
        $where = empty($SLLR_ID) ? array() : array('SLLR_ID' => $SLLR_ID);
        $where = empty($where) ? $where : array_merge($where, array('CAT_CD' => $CAT_CD));
        return $this->field($this->field)->where($where)->limit(1)->select();
    }
    
    /**
     * 获取品牌分类列表
     * @param $SLLR_ID 品牌id
     * @return mixed
     */
    public function getBrandCateList($SLLR_ID)
    {
        $where = empty($SLLR_ID) ? array() : array('SLLR_ID' => $SLLR_ID);
        $limit = empty($SLLR_ID) ? 1 : '';
        return $this->field($this->field)->where($where)->limit($limit)->select();
    }
    
    /**
     * 通过 $SLLR_ID 和 $COMM_CAT_CD 获取分类信息
     * @param $SLLR_ID
     * @param string $COMM_CAT_CD
     * @return mixed
     */
    public function getCatIdByCommCatCdAndSllrId($SLLR_ID, $COMM_CAT_CD = '')
    {
        $where['SLLR_ID'] = $SLLR_ID;
        $where['COMM_CAT_CD'] = $COMM_CAT_CD;
        return $this->field($this->field)->where($where)->find();
    }

    /**
    * 添加品牌分类数据
    */
    public function addBrandData($data)
    {
        return $this->add($data);
    }
    
    
    /**
     * 读取品牌类目和对应的 多语言名称
     * @param $sellerId
     * @param string $lang
     * @return bool
     */
    public function getCategoryList($sellerId, $lang = 'N000920100')
    {
        if (empty($sellerId)) return false;
        
        $sql = "SELECT
            A.SLLR_ID AS brandId ,
            A.CAT_CD AS catCode,
            A.COMM_CAT_CD AS commCatCode,
            A.CAT_NM AS catName,
            A.CAT_KOR_NM AS catKrName,
            A.CAT_SORT_NO AS catSort,
            A.CAT_MPNG_YN AS catMpng,
            A.CAT_DSC AS cateDesc,
            B.ALL_VAL AS allName
            FROM tb_ms_sllr_cat A 
            LEFT JOIN tb_ms_multi_code B
            ON A.SLLR_ID = B.SLLR_ID AND A.CAT_CD = B.CODE
            WHERE A.SLLR_ID = '{$sellerId}' AND B.TYPE='N000960400' AND B. LANGUAGE = '{$lang}'
            ORDER BY CAT_SORT_NO";
        
        $cateMap = $this->query($sql);
        return $cateMap;
    }
    
    /**
     * 拆分类目数据层级关系，并按照层级关系组合到一起。
     * 第一级别直接读取  firstCate 数组的数据，循环输出即可。
     * 第二级别选中一级  读取secondCate数组中索引为第一级Code的数组
     * 第三级别选中一级和二级， 读取 thirdCate 数组中 按照 一级和二级的层级Code读取即可。
     * @param $cateMap
     * @return array|bool
     */
    public function buildCateByLevel($cateMap)
    {
        if (empty($cateMap)) return false;
        $catGroup = array();
        foreach ($cateMap as $key => $cate) {
            $codeA = substr($cate['catCode'], 0, 3);
            $codeB = substr($cate['catCode'], 3, 3);
            $codeC = substr($cate['catCode'], 6, 4);
            $catGroup[$codeA][$codeB][$codeC] = $cate;
        }
    
        //按照层级组合成层级分离的数据
        $lastCate = array('firstCate' => [], 'secondCate' => [], 'thirdCate' => []);
        foreach ($catGroup as $keyA => $itemA) {
            //Level 1 的Code
            $cateKeyL1 = $keyA . '0000000';
            //处理第二级的类目
            foreach ($itemA as $keyB => $itemB) {
                //Level 2 的Code
                $cateKeyL2 = $keyA . $keyB . '0000';
                //解析出第三层
                foreach ($itemB as $keyC => $itemC) {
                    //First Level，类目具体信息为 itemC
                    if ($keyB == '000' && $keyC == '0000') {
                        //指定第一级类目的 索引 key为: firstCate
                        $lastCate['firstCate'][$cateKeyL1] = $itemC;
                    } elseif ($keyB != '000' && $keyC == '0000') {
                        //Second Level, 指定类目的子类目数组的 索引key为：secondCate
                        $lastCate['secondCate'][$cateKeyL1][$cateKeyL2] = $itemC;
                    } else{
                        if ($keyB != '000' && $keyC != '0000') {
                            //Third Level, 指定类目的子类目数组的 索引key为： thirdCate
                            $cateKeyL3 = $keyA . $keyB . $keyC;
                            $lastCate['thirdCate'][$cateKeyL1][$cateKeyL2][$cateKeyL3] = $itemC;
                        } else {
                            $lastCate['thirdCate'] = [];
                        }
                    }
                    //end处理结束
                }
            }
        }
        
        return $lastCate;
    }
    
    /**
     * 添加新的品牌类目。
     * @param array $data [SLLR_ID,CAT_NM,CAT_KOR_NM,bind3] 类目属性数据
     * @param array $condition [selected,createSub] 选中的品牌类目CODE，是否是创建子类目
     * @return bool
     */
    public function createBrandCate($data, $condition)
    {
        if (empty($data))
        {
            return false;
        }
        
        //处理和验证绑定关系，需要验证绑定类目的层做不同处理。
        if ($condition['bindL3'] != -1) {
            $bind = $condition['bindL3'];
        } elseif ($condition['bindL2'] != -1) {
            $bind = $condition['bindL2'];
        } else {
            $bind = $condition['bindL1'];
        }
        
        //$cateData = ['SLLR_ID' => $brandId, 'CAT_NM' => $cnName, 'CAT_KOR_NM' => $krName];
        !empty($data['SLLR_ID']) && $cate['SLLR_ID'] = $data['SLLR_ID'];
        $cate['CAT_CD'] = $this->getMaxCatCode($data['SLLR_ID'], $condition);
        $cate['COMM_CAT_CD'] = $bind;
        $cate['CAT_NM'] = !empty($data['CAT_NM']) ? $data['CAT_NM'] : '';
        $cate['CAT_KOR_NM'] = !empty($data['CAT_KOR_NM']) ? $data['CAT_KOR_NM'] : '';
        $cate['CAT_SORT_NO'] = $this->getMaxSortNumber($data['SLLR_ID']) + 1;
        $cate['CAT_MPNG_YN'] = !empty($data['CAT_MPNG_YN']) ? $data['CAT_MPNG_YN'] : 'Y';
        $cate['CAT_DSC'] = !empty($data['CAT_DSC']) ? $data['CAT_DSC'] : NULL;
        $cate['updated_time'] = date('Y-m-d H:i:s', time());
        
        $result = $this->add($cate);
        //var_dump($result,$this->getLastSql());exit;
        if ($result){
            return  $cate;
        } else {
            return false;
        }
    }
    
    
    private function getMaxCatCode($sellerId, $condition)
    {
        $selected = $condition['selected'];
        $isSub = $condition['createSub'];
        
        //拆解类目层级
        $first = substr($selected, 0, 3);
        $second = substr($selected, 3, 3);
        $third = substr($selected, 6, 4);
        
        if ($second == '000' && $third == '0000') //第一级的最大Code
        {
            if ( $isSub == 'Y') { //找二级类最大的CODE
                $last = $this->where("SLLR_ID = '{$sellerId}' AND CAT_CD LIKE '{$first}%0000'")
                    ->order('CAT_CD DESC')
                    ->limit(1)
                    ->getField('CAT_CD');
                $newSecond = substr($last,3,3) + 1;
                $last = $first . sprintf('%03d', $newSecond) . $third;
            } else { //找一级最大CODE
                $last = $this->where("SLLR_ID = '{$sellerId}' AND CAT_CD LIKE '%0000000'")
                    ->order('CAT_CD DESC')
                    ->limit(1)
                    ->getField('CAT_CD');
                $newFirst = substr($last, 1, 2) + 1;
                $last = 'C' . sprintf('%02d', $newFirst) . $second . $third;
            }
        } elseif ($second != '000' && $third == '0000') {
            if ( $isSub == 'Y') { //找三级类最大的CODE
                $last = $this->where("SLLR_ID = '{$sellerId}' AND CAT_CD LIKE '{$first}{$second}%'")
                    ->order('CAT_CD DESC')
                    ->limit(1)
                    ->getField('CAT_CD');
                $last = $first . $second . sprintf('%04d', substr($last, 6, 4) + 1);
            } else { //找二级最大CODE
                $last = $this->where("SLLR_ID = '{$sellerId}' AND CAT_CD LIKE '{$first}%0000'")
                    ->order('CAT_CD DESC')
                    ->limit(1)
                    ->getField('CAT_CD');
                $newSecond = substr($last,3,3) + 1;
                $last = $first . sprintf('%03d', $newSecond) . $third;
            }
        } else{
            $last = $this->where("SLLR_ID = '{$sellerId}' AND CAT_CD LIKE '{$first}{$second}%'")
                ->order('CAT_CD DESC')
                ->limit(1)
                ->getField('CAT_CD');
            $last = $first . $second . sprintf('%04d', substr($last, 6, 4) + 1);
        }
        
        return $last;
    }
    
    /**
     * 获取最大排序号码
     * @param $sellerId
     * @return mixed
     */
    private function getMaxSortNumber($sellerId)
    {
        return $this->where("SLLR_ID = '{$sellerId}' ")->getField('MAX(CAT_SORT_NO)');
    }
    
    /**
     * 更新品牌类目数据
     * @param array $data 待更新数据
     * @param array $condition 更新条件
     * @return bool | int 影响行数
     */
    public function updateBrandCate($data, $condition)
    {
        if (empty($data)) {
            return false;
        }
    
        //处理和验证绑定关系，需要验证绑定类目的层做不同处理。
        if ($condition['bindL3'] != -1) {
            $bind = $condition['bindL3'];
        } elseif ($condition['bindL2'] != -1) {
            $bind = $condition['bindL2'];
        } else {
            $bind = $condition['bindL1'];
        }
    
        //$cateData = ['CAT_NM' => $cnName, 'CAT_KOR_NM' => $krName];
        $cate['COMM_CAT_CD'] = $bind;
        $cate['CAT_NM'] = !empty($data['CAT_NM']) ? $data['CAT_NM'] : '';
        $cate['CAT_KOR_NM'] = !empty($data['CAT_KOR_NM']) ? $data['CAT_KOR_NM'] : '';
        $cate['CAT_MPNG_YN'] = !empty($data['CAT_MPNG_YN']) ? $data['CAT_MPNG_YN'] : 'Y';
        $cate['CAT_DSC'] = !empty($data['CAT_DSC']) ? $data['CAT_DSC'] : NULL;
    
        return $this->where("SLLR_ID='{$condition['SLLR_ID']}' AND CAT_CD='{$condition['selected']}'")
            ->save($cate);
    }
    
    /**
     * @param array $condition 删除条件
     * @return bool 影响行数，失败false。
     */
    public function deleteCate($condition)
    {
        if (empty($condition['sellerId']) || empty($condition['selected']))
        {
            return false;
        }
        
        $res = $this->where("SLLR_ID='{$condition['sellerId']}' AND `CAT_CD` = '{$condition['selected']}'")
            ->delete();
        return $res;
        
    }
}