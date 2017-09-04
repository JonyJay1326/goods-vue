<?php

/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 17/1/4
 * Time: 9:40
 */
class BasicAction extends BaseAction
{
    public function _initialize()
    {
//   定义变量
        $HI_PATH = '../Public/';
        $this->assign('HI_PATH', $HI_PATH);

    }


    /**
     * 商品档案
     */
    public function goods()
    {
        if (IS_POST) {
            switch (I("post.operate")) {
                case 'add':
                    $goods = I('post.goods_data');
                    $return_arr = $this->goods_add($goods);
                    break;
                case 'del':
                    $goods_id = I('post.goods_id');
                    $return_arr = $this->goods_del($goods_id);
                    break;
                case 'edit':
                    break;
                default:
                    $return_arr = array('info' => '错误状态', "status" => "n");
            }
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit;
        }
//        init
        $this->assign('producer', json_encode($this->get_producer(), JSON_UNESCAPED_UNICODE));
        $this->assign('brand', json_encode($this->get_brand(), JSON_UNESCAPED_UNICODE));
        $this->assign('category', json_encode($this->get_category(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));

        $Goods = M('goods', 'tb_wms_');
        $goods_data = $Goods->select();
        $this->assign('goods_data', json_encode($goods_data, JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    private function goods_add($goods)
    {
        $goods_data = array(
            'GSKU' => 'GSKU',
            'UP_SKU' => null,
            'goods_name' => $goods['goods_name'],
            'goods_english_name' => $goods['goods_name'],
            'spec' => $goods['spec'],
            'model' => $goods['model'],
            'brand' => $goods['brand'],
            'producer' => $goods['producer'],
            'goods_class_id' => $goods['goods_class_id'],
            'digit' => $goods['digit'],
            'weight' => $goods['weight'],
            'refer_price' => $goods['refer_price'],
            'refer_cost' => $goods['refer_cost'],
            'min_price' => $goods['min_price'],
            'batch_switch' => $goods['batch_switch'],
            'prime_date_switch' => $goods['prime_date_switch'],
        );
        $Goods = M('goods', 'tb_wms_');
        $goods_id = $Goods->add($goods_data);
        if ($goods_id) {

            $GSKU = $goods['goods_class_id'] . '-' . $goods['brand'] . '-' . $goods_id;
            $where['id'] = $goods_id;
            $Goods->GSKU = $GSKU;
            $Goods->where($where)->save();
            $return_arr = array('info' => '创建成功', "status" => "y", 'goods_id' => $goods_id, 'GSKU' => $GSKU, 'data' => $goods);
        } else {
            $return_arr = array('info' => '创建失败', "status" => "n", 'data' => $goods);
        }

        return $return_arr;
    }

    private function goods_del($id)
    {
        $Goods = M('goods', 'tb_wms_');
        if ($Goods->delete($id)) {
            $return_arr = array('info' => '删除成功', "status" => "y");
        } else {
            $return_arr = array('info' => '删除失败', "status" => "n", 'err_data' => $id);
        }
        return $return_arr;
    }


    /**
     * 品牌档案
     */
    public function brand()
    {
        $Brand = M('brand', 'tb_wms_');
        $brand_list = $Brand->select();
        if (empty($brand_list)) {
            $test = '[{"id": "", "brand_name": "", "remarks": "", "start": 0, "date": "","user_name":"","input": false}]';
            $this->assign('brand_list', $test);
            $this->assign('show', 0);
        } else {
            $this->assign('show', 1);
            $this->assign('brand_list', json_encode($brand_list, JSON_UNESCAPED_UNICODE));
        }
        $this->display();
    }

    public function brand_add()
    {
        if (IS_POST) {
            $brand_data_get = array(
                'brand_name' => I("post.brand_name"),
                'remarks' => I("post.remarks"),
                'date' => I("post.date"),
                'user_name' => $_SESSION['m_loginname']
            );
//            检验货位
            $Brand = M('brand', 'tb_wms_');
            $brand_id = $Brand->add($brand_data_get);

            if ($brand_id) {
                $return_arr = array('info' => '创建成功', "status" => "y", 'brand_id' => $brand_id);
            } else {
                $return_arr = array('info' => '创建失败', "status" => "n", 'data' => $brand_data_get);
            }

        } else {
            $return_arr = array('info' => '请求错误', "status" => "n");
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
        exit;

    }



    /**
     *原产国
     */
    private function get_producer()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '원산지코드';
        return $Cmn_cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//品牌
    private function get_brand()
    {
        $Brand = M('brand', 'tb_wms_');
        $where['start'] = 1;
        return $Brand->where($where)->getField('id,brand_name,remarks');
    }

//商品类
    private function get_category()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '商品大类';
        return $Cmn_cd->where($where)->getField('CD,CD_VAL,ETc');
    }

//计量单位
    private function get_metering()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = 'VALUATION_UNIT';
        return $Cmn_cd->where($where)->getField('CD,CD_VAL,ETc');
    }


}