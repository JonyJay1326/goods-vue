<?php

/**
 * 入库测试类
 * 
 */
class BillAction extends BaseAction
{
    public function _initialize()
    {
         
    }
    /**
     * 出入库接口
     * 
     */    
    public function out_and_in_storage()
    {
        $bill = new TbWmsBillModel();
        if (IS_POST) {
            $ret = $bill->outAndInStorage($_POST);
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $a = [
              "code" => 10000000,
              "msg" => "入库成功",
              "info" => NULL
            ];
            $data = [
                'bill' => [
                    'field' => 'data',
                ],
                'guds' => [
                    [
                        'field' => 'data',
                    ],
                    [
                        'field' => 'data',
                    ],
                ]
            ];
            $this->assign('title', 'out_and_in_storyage(出入库)接口参数：');
            $this->assign('exampleRequestFormat', $data);
            $this->assign('msgCode', $bill->msgCode());
            $this->assign('example', json_encode($a));
            $this->assign('bill', $bill->bill_attributes);
            $this->assign('guds', $bill->guds_attributes);
            $this->display();
        }
    }
    
    /**
     * 在途，在途金接口
     * 
     */
    public function on_way_and_on_way_money()
    {
        $data = [
                [
                    'SKU_ID' => '8000000401',
                    'TYPE' => 0,
                    'on_way' => 123,
                    'on_way_money' => 3300
                ],
                [
                    'SKU_ID' => '8000000401',
                    'TYPE' => 1,
                    'on_way' => 20,
                    'on_way_money' => 300
                ],
            ];
        $standing = new TbWmsStandingModel();
        if (IS_POST) {
            $ret = $standing->onWayAndOnWayMoney($_POST);
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $a = [
                "code" => 10000000,
                "msg" => "写入成功",
                "info" => null,
            ];
            $data = [
                [
                    'SKU_ID' => 'SKU_ID_01',
                    'TYPE' => 0,
                    'on_way' => 123,
                    'on_way_money' => 3300.12,
                ],
                [
                    'SKU_ID' => 'SKU_ID_02',
                    'TYPE' => 1,
                    'on_way' => 234,
                    'on_way_money' => 124123.24,
                ],
            ];
            $this->assign('title', 'on_way_and_on_way_money(在途，在途金接口)接口参数：');
            $this->assign('bill', $standing->attributes);
            $this->assign('msgCode', $standing->msgCode());
            $this->assign('exampleRequestFormat', $data);
            $this->assign('example', json_encode($a));
            $this->display('out_and_in_storage');
        }
    } 
    
    public function test()
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
                    'warehouse_id' => '',// 仓库id（码表或数据字典对应的值）
                    'total_cost' => '300.01',//入库总成本
                    'bill_state' => null,   //单据状态，可为空
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
            $ret = $bill->outAndInStorage($data);
            echo '<pre/>';var_dump($ret);exit;
        if (IS_POST) {
            
        } else {
            $a = [
              "code" => 10000000,
              "msg" => "入库成功",
              "info" => NULL
            ];
            $this->assign('msgCode', $bill->msgCode());
            $this->assign('example', json_encode($a));
            $this->assign('bill', $bill->bill_attributes);
            $this->assign('guds', $bill->guds_attributes);
            $this->display();
        }
        
        return json_encode();
    }

    /**
     * Response 测试类
     *
     */
    public function testResponse()
    {
        $response = new Response();
        $response->format = 'jsonp';
        $response->data = [
            'callback' => 'hello',
            'data' => [
                'a' => 'b',
                'c' => 'd'
            ]
        ];
        $response->send();
    }
}