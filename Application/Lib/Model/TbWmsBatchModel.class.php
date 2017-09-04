<?php
/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 2017/8/17
 * Time: 10:41
 */

class TbWmsBatchModel extends BaseModel
{
    protected $trueTableName = 'tb_wms_batch';

    /**
     * 获取批次id与skuid
     * @param  int $batch_id 批次id
     * @return string $sku_id
     */
    public function getBatchIdSkuId($batch_id)
    {
        return $ret = $this->where('id = ' . $batch_id)->find();
    }

    /**
     * 占用订单查询
     */
    public function take_up($batch_id)
    {
        if ($ret = $this->getBatchIdSkuId($batch_id)) {
            $Operation_history = M('operation_history', 'tb_wms_');
            $where['sku_id'] = $ret ['SKU_ID'];
            if (!empty(I("post.p"))) {
                $_GET['p'] = I("post.p");
            }
            import('ORG.Util.Page');// 导入分页类
            $Operation_history_sql = $Operation_history->where($where)->group('tb_wms_operation_history.order_id')->having('count(tb_wms_operation_history.id)=1')->select(false);
            $model = new Model();
            $count = $model->table($Operation_history_sql . ' a')->where("a.ope_type = 'N001010100'")->count();
    //        $count = count($Operation_history->where($where)->group('tb_wms_operation_history.order_id')->having('count(tb_wms_operation_history.id)=1')->select());

            $Page = new Page($count, 50);
            $show['ajax'] = $Page->ajax_show();
            $show['sum'] = $Page->get_totalPages();
            $show['sku'] = $ret ['SKU_ID'];

            /*$operation_history = $Operation_history->field($ope_field)
                ->where($where)->group('tb_wms_operation_history.order_id')->having('count(tb_wms_operation_history.id)=1')
                ->order('tb_wms_operation_history.id desc')
                ->join('left join tb_op_order on tb_op_order.B5C_ORDER_NO = tb_wms_operation_history.order_id')
                ->limit($Page->firstRow . ',' . $Page->listRows)->select();*/
            $ope_field = 'tb_op_order.ORDER_ID,a.*';
            $operation_history = $model->field($ope_field)->table($Operation_history_sql . ' a')->where("a.ope_type = 'N001010100'")
                ->join('left join tb_op_order on tb_op_order.B5C_ORDER_NO = a.order_id')
                ->order('a.id desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)->select();
            if ($operation_history) {
                $info = '查询正常';
                $status = 'y';
            } else {
                $info = '查询无结果';
                $status = 'n';
            }
        } else {
            $operation_history = null;
            $info = '查询无结果';
            $status = 'n';
        }
        return $return_arr = array('info' => $info, "status" => $status, 'data' => $operation_history, 'show' => $show);
    }
}