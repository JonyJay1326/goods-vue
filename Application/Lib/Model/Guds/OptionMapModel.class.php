<?php
/**
 * SKU属性和属性值映射表，多语言版本配置表。
 * 这个数据表设计有缺陷，如果要扩展语言种类的话，会是一件非常悲剧的事情。
 * 
 * User: afanti
 * Date: 2017/7/25
 * Time: 16:27
 */

class OptionMapModel extends Model
{
    /**
     * @var string 数据字典表
     */
    protected $trueTableName = "tb_ms_multi_code";

    /**
     * 创建新的OptionMap 值列表。
     * @param $optNameCode
     * @param $values
     * @return false|int
     */
    public function createNewValues($optNameCode, $values){
        $data['PAR_CODE'] = $optNameCode;

        $dataStr = "";
        foreach ( $values as $optValCode => $item) {
            $allVal = implode('/', $item);
            $dataStr .= "({$optNameCode}, {$optValCode}, '{$item['KR']}','{$allVal}', 'N000960200', 'N000920400', 'Y'),";
            $dataStr .= "({$optNameCode}, {$optValCode}, '{$item['CN']}','{$allVal}', 'N000960200', 'N000920100', 'Y'),";
            $dataStr .= "({$optNameCode}, {$optValCode}, '{$item['EN']}','{$allVal}', 'N000960200', 'N000920200', 'Y'),";
            $dataStr .= "({$optNameCode}, {$optValCode}, '{$item['JP']}','{$allVal}', 'N000960200', 'N000920300', 'Y'),";
        }
        $dataStr = trim($dataStr, ',');

        $sql = "INSERT INTO tb_ms_multi_code (PAR_CODE,CODE,VAL,ALL_VAL,TYPE,LANGUAGE,USED_YN) VALUES  {$dataStr} ;";
        return $this->execute($sql);
    }

    /**
     * 根据指定的语言类型，查询所有SKU属性，以及多语言名称。
     * 用于在添加SKU时，提供的SKU属性名称列表。
     *
     * @param string $lang 当前的语言类型
     * @return bool | array 数组数据或者 false
     */
    public function getOptions($lang = 'N000920100'){

        if (empty($lang))   return false;
        
        //select * from tb_ms_multi_code where TYPE='N000960100' AND LANGUAGE='N000920100';
        return $this->where(" TYPE= 'N000960100' AND LANGUAGE='{$lang}' ")
            ->getField('CODE,VAL,ALL_VAL', true);
    }

    /**
     * 根据系统选择的语言类型，查询指定属性的所有属性值列表。
     * 用于添加SKU时，选取SKU属性 对应的属性值，例如：对于颜色这个属性，选择了：黑、白、灰；尺寸属性选择：L、M
     *
     * 表中属性的多语言配置和对应的属性值的多语言配置，属性值与属性的关系是通过 CODE 和 PAR_CODE关联的。
     * 属性的CODE码，就是属性值的 PAR_CODE即父Code。
     * 
     * @param string $optionCode SKU多语言属性 对应的 Code码。
     * @param string $lang 语言类型Code码
     * @return array|bool
     */
    public function getOptionValues($optionCode, $lang = 'N000920100'){

        if(empty($optionCode) || empty($lang))
        {
            $this->error = 'Invalid Param';
            return false;
        }
        
        $result =  $this->where("PAR_CODE = '{$optionCode}' AND TYPE='N000960200' AND LANGUAGE = '{$lang}' ")
            ->getField('CODE,PAR_CODE,VAL,ALL_VAL,TYPE', true);

        return $result;
    }


    /**
     * 从给定的Options名称对应的OptionValue值数据中，搜索指定的keyword关键词的数据行。
     * @param number $optionNameCode 属性名的Code码
     * @param string $keyword 搜索关键词
     * @param string $lang
     * @return bool|mixed
     */
    public function searchOptionValues($optionNameCode, $keyword, $lang = 'N000920100'){
        if (empty($optionNameCode)){
            $this->error = '搜索SKU选项值参数错误';
            return false;
        }

        //SELECT * FROM `tb_ms_multi_code` WHERE PAR_CODE = 8002 AND ALL_VAL LIKE '%color%';
        $where = " PAR_CODE = '{$optionNameCode}' AND LANGUAGE='{$lang}'";
        if(!empty($keyword)) $where.=" AND ALL_VAL LIKE '%{$keyword}%' ";
        return $this->where($where)
            ->getField('CODE,PAR_CODE,VAL,ALL_VAL', true);
    }

    /**
     * 读取所有指定CODE码的数据。
     *
     * 用于构造SKU组合数据，SKU基本属性名Code和属性值Code，每种语言的Code码都是一样的，所以存储时只存一套即可。
     * 只有在展示层面的时候，才需要关联对应的序言，找出对应语言的属性名称和属性值。
     * @param string $nameCode 属性名CODE码
     * @param string $valueCodes 必须是字符串格式，支持多个CODE，用逗号分隔。
     * @param string $lang 语言类型编码
     * @return array|bool
     */
    public function getOptionByCodes($nameCode, $valueCodes, $lang = 'N000920100'){
        if(empty($nameCode) || !is_string($valueCodes))
        {
            $this->error = 'Invalid Param';
            return false;
        }

        return $this->where("PAR_CODE='{$nameCode}' AND LANGUAGE='{$lang}' AND CODE IN ({$valueCodes}) OR CODE='{$nameCode}'")
            ->getField('CODE,PAR_CODE,VAL,ALL_VAL,TYPE', true);
    }

    /**
     * 根据SKU列表，找出所有的SKU属性和属性值，构成数组返回。
     * @param $optionGroup
     * @return array|bool
     */
    public function getOptionMaps($optionGroup)
    {
        if (empty($optionGroup)) return false;
        
        $mapList = array();
        foreach ($optionGroup as $key => $option)
        {
            //分离Option和OptionValue对组合(8001:800171;8002:800242)
            $optMap = explode(';', $option['GUDS_OPT_VAL_MPNG']);
            foreach ($optMap as $item)
            {
                list($optNameCode, $optValueCode) = explode(':', $item);
                $mapList[$optNameCode] .= $optValueCode . ",";
            }
        }
        return $mapList;
    }

    /**
     * 根据SKU列表抽象组合出所有的Option属性名和属性值列表。
     *
     * @param array $mapList
     * @param string $lang
     * @return mixed
     */
    public function getOptionByCodeMap($mapList, $lang= 'N000920100'){
        //SQL
        $where = "";
        foreach ($mapList as $nameCode => $valueCodeList)
        {
            $valueCodeList = rtrim($valueCodeList, ',');
            $where .= "PAR_CODE='{$nameCode}' AND CODE IN ($valueCodeList) OR CODE='{$nameCode}' OR ";
        }

        $where = rtrim($where, 'OR or');//去掉末尾多余的 OR or 和空格
        !empty($lang) && $where . " AND LANGUAGE='{$lang}'";
        return $this->where($where)->getField('CODE,PAR_CODE,VAL,ALL_VAL,TYPE', true);
    }


    /**
     * 笛卡尔积计算数组，用户根据SKU属性值数据，构造SKU的组合。
     * 
     * @param $data
     * @return array
     */
    public function CartesianGroup($data) {
        $result = array();
        foreach (array_shift($data) as $k=>$item) {
            $result[] = array($k=>$item);
        }

        foreach ($data as $k => $v) {
            $result2 = [];
            foreach ($result as $k1=>$item1) {
                foreach ($v as $k2=>$item2) {
                    $temp     = $item1;
                    $temp[$k2]   = $item2;
                    $result2[] = $temp;
                }
            }
            $result = $result2;
        }
        return $result;
    }

    /**
     * SKU组合映射关系，转换为属性和属性值数组，拆分为单独的项，方便页面展示。
     * 因为SKU不允许修改，不允许新增所以，只是展示即可。
     *
     * @param string $optMap 单个SKU的Option和OptionValue组合映射组。
     * @param array $options 所有的Option和OptionValue构成的数组，数据来自于：tb_ms_multi_code 表。
     * @return array
     */
    public function optionMapTranslate($optMap, $options)
    {
        //SKU组拆分为，属性名:属性值对 数组。
        $optionMap = $select = array();
        $optionArr = explode(';', $optMap);
        foreach ($optionArr as $key => $map){
            list($optNameCode, $optValueCode) = explode(':', $map);

            $optionMap[] = array(
                'optNameCode'   => $optNameCode,
                'optName'       => $options[$optNameCode]['VAL'],
                'optNameAll'    => $options[$optNameCode]['ALL_VAL'],
                'optValueCode'  => $optValueCode,
                'optValue'      => $options[$optValueCode]['VAL'],
                'optValueAll'      => $options[$optValueCode]['ALL_VAL'],
            );
        }

        return $optionMap;
    }
    
    /**
     * 根据Option属性数据，构建SKU配置工具栏信息
     * 按照层级关系 组合成属性 =>属性值列表和名称等数据。
     * @param array $allOption 属性与属性值映射关系数据
     * @return array
     */
    public function optionMapParse($allOption)
    {
        $list = array();
        foreach ($allOption as $code => $item){
            if (empty($item['PAR_CODE'])){
                $list[$code]['optNameCode'] = $item['CODE'];
                $list[$code]['optNameValue'] = explode('/', $item['ALL_VAL']);
            } else {
                $list[$item['PAR_CODE']]['optValueCode'][] = $item['CODE'];
                $list[$item['PAR_CODE']]['optValueValue'][] = explode('/', $item['ALL_VAL']);
            }
        }

        return $list;
    }
    
    /**
     * 添加品牌类目的多语言内容，multi_code 表数据.
     * @param array $data 多语言内容
     * @return bool|false|int 返回结果
     */
    public function createBrandCate($data){
        if (empty($data)){
            return false;
        }
        
        $val = '';
        $all = $data['kr'] . '/' . $data['cn'] . '/' . $data['en'] . '/' . $data['jp'];
        $val .= "('{$data['SLLR_ID']}','{$data['CAT_CD']}', '{$data['cn']}','{$all}', 'N000960400', 'N000920100', 'Y'),";
        $val .= "('{$data['SLLR_ID']}','{$data['CAT_CD']}', '{$data['kr']}','{$all}', 'N000960400', 'N000920200', 'Y'),";
        $val .= "('{$data['SLLR_ID']}','{$data['CAT_CD']}', '{$data['en']}','{$all}', 'N000960400', 'N000920300', 'Y'),";
        $val .= "('{$data['SLLR_ID']}','{$data['CAT_CD']}', '{$data['jp']}','{$all}', 'N000960400', 'N000920400', 'Y'),";
    
        $val = trim($val, ',');
        $sql = "INSERT INTO tb_ms_multi_code (SLLR_ID,CODE,VAL,ALL_VAL,TYPE,LANGUAGE,USED_YN) VALUES  {$val};";
        $res = $this->execute($sql);
        return $res;
    }
    
    /**
     * 更新品牌类目的多语言名称内容。
     * 用于品牌类目更新名称的功能页面使用。
     *
     * @param array $data 待更新数据
     * @param array $condition 更新数据的条件
     * @return bool|int 影响行数
     */
    public function updateBrandCate($data, $condition){
        if (empty($data) || empty($condition)){
            return false;
        }
        
        $allVal = $data['kr'] . '/' . $data['cn'] . '/' . $data['en'] . '/' . $data['jp'];
        $sql = "
        UPDATE
          tb_ms_multi_code
        SET `VAL` = CASE LANGUAGE
            WHEN 'N000920100' THEN '{$data['cn']}'
            WHEN 'N000920200' THEN '{$data['kr']}'
            WHEN 'N000920300' THEN '{$data['en']}'
            WHEN 'N000920400' THEN '{$data['jp']}'
        END,
        `ALL_VAL` = '{$allVal}' 
        WHERE
            `SLLR_ID`='{$condition['SLLR_ID']}'
            AND `CODE`='{$condition['selected']}'
            AND `TYPE` = 'N000960400'
        ";
        
        $res = $this->execute($sql);
        return $res;
    }
    
    /**
     * 删除指定的品牌类目的读语言班
     * @param array $condition 删除条件
     * @return bool 删除行数，失败false。
     */
    public function deleteBrandCate($condition)
    {
        if (empty($condition['sellerId']) || empty($condition['selected']))
        {
            return false;
        }
        
        //删除这里一定要带上 类型，防止单纯靠品牌和编码多删的可能性。
        $where = "SLLR_ID='{$condition['sellerId']}' AND `CODE` = '{$condition['selected']}' AND `TYPE`='N000960400'";
        $res = $this->where($where)->delete();
        return $res;
    }
}