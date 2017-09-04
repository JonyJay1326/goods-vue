<?php

/**
 * 入库测试类
 *
 */
class TestAction extends BaseAction
{
    /**
     * 入库测试案例
     * 
     */    
    public function in_test()
    {
        $bill = new TbWmsBillModel();
        $data = [
            'bill' => [
                'bill_type' => 'N000940100',//收发类型，采购入库为N000940100固定不变
                'link_bill_id' => 'b5cb49053203333',//b5c id
                'warehouse_rule' => '0',// 数据库无对应字段
                'batch' => date('Y-m-d', time()),//批次，这个待定
                'sale_no' => 'test20170717',// 数据库无对应字段
                'channel' => 'B5C',// 91440300311647055E
                'supplier' => '',// 供应商（tb_crm_sp_supplier所对应的供应商 id）
                'purchase_logistics_cost' => '58.67',//采购端的物流费用
                'warehouse_id' => 'N000680300',// 仓库id（码表或数据字典对应的值）
                'total_cost' => '300.01',//入库总成本
                'bill_state' => '',   //单据状态，可为空
                'bill_date' => date('Y-m-d', time()),//单据日期
            ],
            'guds' => [
                [
                    'GSKU' => '8000360401', // sku
                    'taxes' => '0.7',       // 税率
                    'should_num' => '800',  // 应发货
                    'send_num' => '489',    // 实际发货
                    'production_date' => '20170713',// 生产日期
                    'price' => '168.54',    // 单价（不含税）
                    'currency_id' => 'N000590300',// 币种（码表或数据字典对应的值）
                    'currency_time' => '20170713',// 具体交易时间（用作取币种当天的汇率）
                ],
                [
                    'GSKU' => '8000372401',
                    'taxes' => '0.4',
                    'should_num' => '999',
                    'send_num' => '321',
                    'production_date' => '20170713',
                    'price' => '189.5',
                    'currency_id' => 'N000590300',// 数据库无对应字段
                    'currency_time' => '20170713',// 数据库无对应字段
                ],
            ],
        ];
        $isok = $bill->outAndInStorage($data);
        echo '<Pre/>';var_dump($isok);exit;
        exit;

    }
    
    /**
     * 出库测试
     * 
     */
    public function out_test()
    {
        $bill = new TbWmsBillModel();
        /**
         * 批量data操作
         * $data = [
         *    [
         *          ['GSKU'] => 'xxxx',
         *          ['send_num'] => (int)xxx,
         *    ],
         *    [
         *          ['GSKU'] => 'xxxx',
         *          ['send_num'] => (int)xxx,
         *    ]
         * ] 
         * 
         */
        // 单条操作
        $data = [
            //'bill_type' => 'N000950100',
            'GSKU' => '8000360401',
            'send_num' => 200,
        ];
        $isok = $bill->outStorage($data);
        echo '<Pre/>';var_dump($isok);exit;
        exit;
    }

    public function index() {
        ELog::add(json_encode(['aa'=>1,'bbb'=>2]));
    }
}