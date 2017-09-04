<?php

/**
 * 通用类目的模型类
 * User: afanti
 * Date: 2017/8/2
 * Time: 17:11
 */
class CategoryModel extends RelationModel
{

    protected $trueTableName = "tb_ms_cmn_cat";
    
    
    protected $fields = '
                id,
                CAT_CD as code,
                CAT_NM as name,
                CAT_CNS_NM as cnName,
                PAR_CAT_CD as parentCode,
                CAT_NM_PATH as namePath,
                CAT_LEVEL as level,
                status,
                DISABLE_YN as disable,
                updated_time,
                CAT_CD_ALP as alp
                ';
    
    /**
     * 读取商品所属的大类的类型标记A-Z
     * @param $sellerId
     * @param $gudsId
     * @return array
     */
    public function getCateFlag($sellerId, $gudsId)
    {
        $sql = "SELECT C.CAT_CD_ALP FROM tb_ms_cmn_cat AS C
                LEFT JOIN tb_ms_sllr_cat AS SC ON SC.COMM_CAT_CD = C.CAT_CD
                LEFT JOIN tb_ms_guds AS G ON G.CAT_CD = SC.CAT_CD AND SC.COMM_CAT_CD = C.CAT_CD 
                WHERE G.GUDS_ID = {$gudsId} AND SC.SLLR_ID='{$sellerId}' ";
        
        $res = $this->query($sql);
        $data = array_pop($res);
        return $data['CAT_CD_ALP'];
    }

    /**
     * 根据主键id读取类目信息
     * @param $id
     * @return mixed
     */
    public function getCategoryById($id)
    {
        return $this->where(" id={$id}")
            ->field($this->fields)
            ->find();
    }

    /**
     * 根据类目编码来读取类目信息
     * @param $code
     * @param null $parentCode
     * @return bool
     */
    public function getCategoryByCode($code, $parentCode = null)
    {
        if (empty($code))   {
            return  false;
        }
        
        $where = "CAT_CD= '{$code}'";
        !is_null($parentCode) && $where .= " AND PAR_CAT_CD = '{$parentCode}' ";
        
        $result = $this->where($where)->find();
        
        return $result;
    }
    
    /**
     * 按层级读取类目列表，计算指定层级的所有类目数量。
     * @param int $level
     * @return bool
     */
    public function getTotalByLevel($level){
        if (empty($level))  {
            return false;
        }
    
        return $this->where("CAT_LEVEL = {$level}")
            ->getField('COUNT(id)');
    }

    /**
     * 按层级查询类目列表
     * @param int $level
     * @param int $start
     * @param int $limit
     * @return bool
     */
    public function getCateGoryByLevel($level, $start, $limit)
    {
        if (empty($level))  {
            return false;
        }

        return $this->where("CAT_LEVEL = {$level}")
            ->field('
                id,
                CAT_CD as code,
                CAT_NM as name,
                CAT_CNS_NM as cnName,
                PAR_CAT_CD as parentCode, 
                CAT_NM_PATH as namePath,
                CAT_LEVEL as level,
                status,
                DISABLE_YN as disable,
                updated_time,
                CAT_CD_ALP as alp
                ')
            ->limit($start, $limit)
            ->select();
    }
    
    
    /**
     * 根据父类CODE和等级读取类目列表。
     * 用户查询品牌类目的绑定关系列表以及其他需要查询类似数据的页面。
     * 
     * @param $parentCode
     * @param null $level
     * @return bool
     */
    public function getCategoryByParent($parentCode, $level = null)
    {
        if (empty($parentCode)){
            
            return false;
        }
        
        $condition = "PAR_CAT_CD = '{$parentCode}' ";
        if (!empty($level))
        {
            $condition = $condition . " AND CAT_LEVEL = {$level}";
        }
        
        
        $cateList = $this->where($condition)->field($this->fields)->select();
        return $cateList;
    }
    

    /**
     * 查询指定类目的子类目
     * @param string $code code码
     * @param int $level 层级
     * @return bool | array
     */
    public function getSubcategory($code, $level=null)
    {
        if (empty($code))   {
            return false;
        }
        
        //判定等级情况，实际上等级条件没有实际用处，这里只是留作扩展，万一绑定类目等级有异常或者业务有改动留余地。
        $where = " PAR_CAT_CD = '{$code}' ";
        if (!empty($level)){
            $where .= " AND CAT_LEVEL={$level}";
        }
        return $this->where($where)->field($this->fields)->select();
    }


    /**
     * 根据等级和类目编码搜索类目。
     * @param string $conditions['levels']
     * @param string $conditions['catCode']
     * @param int $start page limit start
     * @param int $limit page limit.
     * 
     * @return bool | array
     */
    public function search($conditions = array(), $start, $limit)
    {
        $where = " 1 ";
        if (!empty($conditions['CAT_CD'])) {
            $where .= " AND CAT_CD LIKE '{$conditions['CAT_CD']}%' ";
        }
    
        if (!empty($conditions['CAT_LEVEL'])){
            $where .= " AND CAT_LEVEL IN ({$conditions['CAT_LEVEL']}) ";
        }
    
        $result = $this->where($where)
            ->field($this->fields)
            ->limit($start, $limit)
            ->select();

        return $result;
    }

    /**
     * 查询符合搜索条件的数据数量
     * @param $conditions
     * @return bool
     */
    public function getSearchCount($conditions = null)
    {
        $where = " 1 ";
        if (!empty($conditions['CAT_CD'])) {
            $where .= " AND CAT_CD LIKE '{$conditions['CAT_CD']}%' ";
        }
        
        if (!empty($conditions['CAT_LEVEL'])){
            $where .= " AND CAT_LEVEL IN ({$conditions['CAT_LEVEL']}) ";
        }
        
        $count = $this->where($where)->getField("COUNT(id)");
        return $count;
    }

    /**
     * 添加通用类目功能
     * TODO CAT_ALP 怎么添加？
     * @param array $data
     * @return int|bool
     */
    public function addCategory($data)
    {
        if (empty($data) || !is_array($data))
        {
            return false;
        }
        
        $first = $second = array();
        if (!empty($data['levelFirst'])){
            $first = $this->getCategoryByCode($data['levelFirst']);
            
            if (!empty($data['levelSecond'])){
                $second = $this->getCategoryByCode($data['levelSecond'], $data['levelFirst']);
            }
        }

        $parentCode = $catPath = null;
        switch ($data['catLevel']){
            case 1:
                $parentCode = null;
                $catPath = $data['catCnName'];
                $maxCode = $this->getMaxCatCode($data['catLevel']);
                $subCode = (int) substr($maxCode, -1, 1) + 1;
                $catCode = 'C' . sprintf('%02d', $subCode);
                break;
            case 2:
                $parentCode = $data['levelFirst'];
                $catPath = $first['CAT_NM_PATH'] . '>' . $data['catCnName'];
                $maxCode = $this->getMaxCatCode($data['catLevel'], $parentCode);
                $subCode = (int) substr($maxCode, 3, 3) + 1;
                $catCode = substr($maxCode, 0, 3) . sprintf('%03d', $subCode);
                $catCode = $catCode . substr($maxCode, 6, 4);
                break;
            case 3:
                $parentCode = $data['levelSecond'];
                $catPath = $second['CAT_NM_PATH'] . '>' . $data['catCnName'];
                $maxCode = $this->getMaxCatCode($data['catLevel'], $parentCode);
                $subCode = (int) substr($maxCode, 6, 4) + 1;
                $catCode = substr($maxCode, 0, 6) . sprintf('%04d', $subCode);
                break;
            default:
                $parentCode = null;
                $catPath = $data['catCnName'];
        }

       $catData['CAT_CD'] = $catCode;
       $catData['CAT_NM'] = $data['catName'];
       $catData['CAT_CNS_NM'] = $data['catCnName'];
       $catData['CAT_SORT'] = !empty($data['sort']) ? $data['sort'] : 0;
       $catData['PAR_CAT_CD'] = $parentCode;
       $catData['CAT_NM_PATH'] = $catPath;
       $catData['ALIAS'] = !empty($data['aliasName']) ? $data['aliasName'] : null;
       $catData['CAT_LEVEL'] = $data['catLevel'];
       $catData['status'] = 1;
       $catData['DISABLE_YN'] = 'N';
       $res =  $this->add($catData);
       return $res;
       //return $this->execute($sql);
    }

    /**
     * 找出同层级最大的 CODE值，计算新增的CODE值。
     * @param int $level 3 =< 类目层级 >= 1
     * @param string $parentCode 父类目CODE
     * @return mixed
     */
    public function getMaxCatCode($level, $parentCode = null)
    {
        return $this->where("CAT_LEVEL={$level} AND PAR_CAT_CD='{$parentCode}' ")
            ->order("CAT_CD DESC")->limit(1)->getField("CAT_CD");
    }

    /**
     * 更新类目
     *
     * @param array $data 要更新的最新数据
     * @param array $condition 更新条件，必须包含 id属性
     * @return bool
     */
    public function update($data, $condition)
    {
        if (empty($data))   return false;

        $res =  $this->where(" id = '{$condition['id']}' ")
            ->save($data);

        return $res;
    }
    
    /**
     * 验证是否有重复名称
     * 
     * @param array $data
     * @param array $condition
     * @return bool | array false 表示没有有重复，数组表示重复的数据
     */
    public function checkDuplicateName($data, $condition)
    {
        if (empty($data))   return false;
        
        $duplicate = $this->where("CAT_NM ='{$data['CAT_NM']}' OR CAT_CNS_NM='{$data['CAT_CNS_NM']}'")
            ->getField("id,CAT_CD");
    
        //从重复名称的数据中去掉 id为当前要更新的数据，
        //因为如果查出来的不是要修改的本身，清理后数组肯定不空，如果就只有当前条自己数组就空了，防止没有修改名称的情况下更新数据
        if (isset($condition['id'])) {
            unset($duplicate[$condition['id']]);
        }
        
        if (!empty($duplicate)){
            return $duplicate;
        }
        
        return false;
    }
    
    
    
}