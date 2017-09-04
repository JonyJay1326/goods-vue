<?php
/**
 * Created by PhpStorm.
 * User: b5m
 * Date: 2017/8/18
 * Time: 10:37
 */

/**
 * Class ProcessHistoryBillModel
 * 处理出入库订单老数据
 * 将老数据增加批次
 * 如果是有效期类型，需要按照有效期近的先出
 *
 */
class ProcessHistoryBillModel extends BaseModel
{
    protected $_requestAttributes = [
        'skuId' => '商品SKUID',
        'changeNm' => '数量',
        'gudsId' => 'SPU',
        'saleTeamCode' => '销售团队编码',
        'spTeamCode' => '采购团队编码',
        'channel' => '渠道',
        'billId' => '出入库单号bill_id'
    ];

    /**
     *
     *
     */
    public function main()
    {

    }

    /**
     * 生成批次号
     * 根据tb_wms_bill与tb_wms_stream和入库时间生成每个sku每次出入库的数据写入tb_wms_batch中，且批次号根据sku递增
     * 不能再次进行operation_history表操作
     * 根据opeartion_history 中sku_id的操作，进行出入库操作
     *
     */
    public function generateBatchCode()
    {
        $bill = M('_wms_bill', 'tb_');
        $ret = $bill->join('tb_wms_stream')->on('tb_wms_bill.bill_id = tb_wms_stream.bill_id')->select();

    }

    public function sendData()
    {

    }

    /**
     * 将批次号与sku
     *
     */
    public function bindBatchCodeThroughSku()
    {

    }
}