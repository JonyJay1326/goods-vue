<?php

/**
 * User: yangsu
 * Date: 16/12/21
 * Time: 17:25
 */
//use Think\Controller;

class StockAction extends BaseAction
{

    private $attribute = [
        '0' => '自用'
    ];
    private $valuation = [
        '0' => '全月加平均'
    ];
    private $bill_state = [
        '0' => '未确认',
        '1' => '已确认'
    ];
    private $location = 0;
    private $back = 0;
    private $outgoing = [];
    private $outgo = null;
    public $actlist = [];

    public function _initialize()
    {
// 定义变量
        header('Access-Control-Allow-Origin: *');
        $HI_PATH = '../Public/';
        $this->assign('HI_PATH', $HI_PATH);
        ini_set('date.timezone', 'Asia/Shanghai');
        /* if (!session('m_loginname')) {
             header("Location:/index.php?m=public&a=login");
         }*/
        $module = $this->_get_menu();
    }


    /**
     * 仓库
     */
    public function warehouse()
    {
        $Warehouse = M('warehouse', 'tb_wms_');

        if (IS_POST) {
            $location_switch = 0;
            if (I("post.location_switch") == 1) {
                $location_switch = 1;
            }
            $warehouse_data = array(
                'warehouse' => I("post.warehouse"),
                'attribute_id' => I("post.attribute_id"),
                'location' => I("post.location"),
                'valuation' => I("post.valuation"),
                'remarks' => I("post.remarks"),
                'contacts' => I("post.contacts"),
                'address' => I("post.address"),
                'phone' => I("post.phone"),
                'place' => I("post.place"),
                'city' => I("post.city"),
                'company_id' => I("post.company_id"),
                'location_switch' => $location_switch
            );
            //
            trace($warehouse_data, '$warehouse_data');
            $warehouse_cd = $this->warehouse_cd($warehouse_data['warehouse']);
            $Warehouse->startTrans();
            if (!empty(I("post.id"))) {
//                edit

                $where['id'] = I("post.id");

                $warehouse_data['CD'] = empty($warehouse_cd) ? $where['id'] : $warehouse_cd;
                $result = $Warehouse->where($where)->save($warehouse_data);
                if ($result) {
                    $return_arr = array('info' => '修改成功,2秒后重载', "status" => "y");
                    $Warehouse->commit();
                } else {
                    $return_arr = array('info' => '修改失败', "status" => "n");
                    $Warehouse->rollback();
                }
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit();
            } else {
//                add
                $result = $Warehouse->add($warehouse_data);
                $Warehouse->CD = empty($warehouse_cd) ? $result : $warehouse_cd;
                $result_cd = $Warehouse->where('id = ' . $result)->save();
                if ($result && $result_cd) {
                    $return_arr = array('info' => '增加成功,2秒后重载', "status" => "y");
                    $_POST['attribute'] = I("post.attribute_id");
                    $return_arr['data'] = $_POST;
                    $Warehouse->commit();
                } else {
                    $Warehouse->rollback();
                    $return_arr = array('info' => '增加失败', "status" => "n");
                }
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
//        初始化

//        国家
//        hot
        $hot = [
            ['value' => 2356, 'label' => 'USA美国', 'children' => array(new stdClass())],
            ['value' => 3025, 'label' => 'JPN日本', 'children' => array(new stdClass())],
            ['value' => 1517, 'label' => 'KOR韩国', 'children' => array(new stdClass())],
        ];
        $packCountry = $this->packCountry(BaseModel::getCountry());
        foreach ($hot as $v) {
            array_unshift($packCountry, $v);
        }
        $this->assign('getCountry', json_encode($packCountry, JSON_UNESCAPED_UNICODE));
        $Countrykey = array_column($packCountry, 'value');
        $this->assign('Countrykey', json_encode($Countrykey, JSON_UNESCAPED_UNICODE));

        $company_arr = $this->get_company();
        $this->assign('company_arr', json_encode($company_arr, JSON_UNESCAPED_UNICODE));
        $this->assign('attribute_arr', json_encode($this->attribute, JSON_UNESCAPED_UNICODE));
        $this->assign('valuation_arr', json_encode($this->valuation, JSON_UNESCAPED_UNICODE));
        $this->assign('various', '4');
//        sku
        $this->assign('all_house_sku', json_encode($this->get_all_house_sku(), JSON_UNESCAPED_UNICODE));
//        show list
        $list = $Warehouse->where('is_show = 1')->select();
        $this->assign('list', $list);
        $this->assign('json_list', json_encode($list, JSON_UNESCAPED_UNICODE));
        $this->assign('warehouseContacts', json_encode(BaseModel::warehouseContacts(), JSON_UNESCAPED_UNICODE));
        $this->display();
    }


    /**
     *检查仓库
     */
    public function check_warehouse()
    {

        $Warehouse = M('warehouse', 'tb_wms_');
        $warehouse_name = I('post.warehouse_name');
        $where['warehouse'] = $warehouse_name;
        $where['is_show'] = 1;
        $result = $Warehouse->where($where)->select();
        if ($result) {
            $return_arr = array('info' => '仓库名已存在', "status" => "n");
        } else {
            $return_arr = array('info' => '仓库名未存在', "status" => "y", 'data' => $result);
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 删除仓库
     */
    public function warehouse_del()
    {
        $id = I("post.id");
        $Location = M('location', 'tb_wms_');
        $where['warehouse_id'] = $id;
        $location_link = $Location->where($where)->count();
        if ($location_link == 0) {
            $Warehouse = M('warehouse', 'tb_wms_');
            $data['is_show'] = 0;
            $where_id['id'] = $id;
            if ($Warehouse->where($where_id)->save($data)) {
                $return_arr = array('info' => '删除成功', "status" => "y");
            } else {
                $return_arr = array('info' => '删除失败', "status" => "n");
            }
        } else {
            $return_arr = array('info' => '请先删除关联货位', "status" => "n");
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 栏位
     */
    public function position()
    {
        $Location = M('location', 'tb_wms_');
        $location_data = $Location->where('tb_wms_location.is_show = 1 ')->
        join('tb_wms_warehouse on  tb_wms_warehouse.CD = tb_wms_location.warehouse_id')->
        join('tb_wms_location_details on tb_wms_location_details.location_id = tb_wms_location.id')->
        field(array('tb_wms_location.id' => 'l_id', 'location_code', 'location_name', 'tb_wms_warehouse.warehouse' => 'warehouse_name', 'count(tb_wms_location_details.id)' => 'l_sum'))->
        group('tb_wms_location_details.location_id')->select();
        $this->assign('location_data', json_encode($location_data, JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     *检查货位
     */
    public function check_location()
    {
        $location = I('post.location');
        $house = I('post.house');
        $outgo_state = I('post.outgo_state');
        if (empty($house)) {
            $return_arr = array('info' => '选择仓库', "status" => "n");
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit();
        }
        if ($outgo_state == 'storage') {
            $Location_details = M('location_details', 'tb_wms_');
            $where['box_name'] = $location;
            $where['warehouse_id'] = $house;
            $l_count = $Location_details->where($where)->count();
            if ($l_count > 0) {
                $data = $Location_details->where($where)->find();
                $return_arr = array('info' => '货位存在', "status" => "y", "data" => $data);
            } else {
                $return_arr = array('info' => '货位未存在', "status" => "n");
            }

        } else {
            $Location_sku = M('location_sku', 'tb_wms_');
            $where_sku['tb_wms_location_sku.sku'] = I('post.GSKU');
            $where_sku['tb_wms_location_sku.count'] = array('GT', 0);
            $Location_sku_box = $Location_sku
                ->where($where_sku)
                ->join("left join tb_wms_location_details on  tb_wms_location_sku.box_name = tb_wms_location_details.box_name AND tb_wms_location_details.warehouse_id = '" . $house . "'")
                ->field('tb_wms_location_sku.box_name,tb_wms_location_sku.count')
                ->select();
            if (count($Location_sku_box) > 0) {
                $return_arr = array('info' => '货位存在', "status" => "i", "data" => $Location_sku_box);
            } else {
                $return_arr = array('info' => '无对应货位', "status" => "n", "data" => $Location_sku_box);
            }
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 栏位详情
     */
    public function position_add()
    {
        if (IS_POST) {
            $position_data = array(
                'location_code' => I("post.location_key"),
                'location_name' => I("post.location_name"),
                'location_sum' => null,
                'warehouse_id' => I('post.location_id'),
            );
//            检验货位
            $Location = M('location', 'tb_wms_');
            $check['location_code'] = $position_data['location_code'];
            $check['is_show'] = 1;
            $check_code_sum = $Location->where($check)->count();
            if ($check_code_sum != 0) {
                $return_arr = array('info' => '货位已存在', "status" => "y");
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit;
            }
            $qu = I("post.qu");
            $Location_details = M('location_details', 'tb_wms_');
            $check_s['area'] = $qu;
            $check_s['warehouse_id'] = I('post.location_id');
//            $check_s['location_id'] = $location_id;
            $check_s_sum = $Location_details->where($check_s)->count();
            if ($check_s_sum != 0) {
                $return_arr = array('info' => '区位冲突', "status" => "y");
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit;
            }
//          优先创建货位
            $location_id = $Location->add($position_data);
            if ($location_id) {
                $qu = I("post.qu");
                $pai = I("post.pai");
                $ceng = I("post.ceng");
                $ge = I("post.ge");
                for ($p = 1; $p <= $pai; $p++) {
                    for ($c = 1; $c <= $ceng; $c++) {
                        for ($g = 1; $g <= $ge; $g++) {
                            $data['box_name'] = $p . '-' . $c . '-' . $g;
                            $data['area'] = $qu;
                            $data['occupy'] = 0;
                            $data['location_id'] = $location_id;
                            $data['warehouse_id'] = I('post.location_id');
                            $datas[] = $data;
                            unset($data);
                        }
                    }
                }

//            join data
                $Location_details = M('location_details', 'tb_wms_');
                $data_start = $Location_details->addAll($datas);
                if ($data_start) {
                    $return_arr = array('info' => '保存成功', "status" => "y");
                } else {
                    $return_arr = array('info' => '保存失败', "status" => "y", 'datas' => $datas, 'start' => $data_start, 'post' => $position_data);
                }
            } else {
                $return_arr = array('info' => '保存失败', "status" => "y");
            }
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit;
        }
// 启用货位
        $this->location = 1;

        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     * 栏位导入
     */
    public function position_import()
    {
        if (IS_POST) {
            $location_id = I('post.location_id');

            $position_data = array(
                'location_name' => I("post.location_name"),
                'warehouse_id' => I('post.house_list_model'),
            );

            if (empty($location_id)) {
// add
                $Location = M('location', 'tb_wms_');
                if (empty($position_data['location_name']) || empty($position_data['warehouse_id'])) {
                    $error[][] = '基本数据为空';
                    goto echo_error;
                } else {
                    $where['location_name'] = $position_data['location_name'];
                    $where['warehouse_id'] = $position_data['warehouse_id'];
                    $where['is_show'] = 1;
                    if ($Location->where($where)->count() > 0) {
                        $error[][] = '货位名称重复';
                        goto echo_error;
                    }

                }
                header("content-type:text/html;charset=utf-8");
                $filePath = $_FILES['expe']['tmp_name'];
                vendor("PHPExcel.PHPExcel");
                $objPHPExcel = new PHPExcel();
                $PHPReader = new PHPExcel_Reader_Excel2007();
                if (!$PHPReader->canRead($filePath)) {
                    $PHPReader = new PHPExcel_Reader_Excel5();
                    if (!$PHPReader->canRead($filePath)) {
                        echo 'no Excel';
                        return;
                    }
                }
                $PHPExcel = $PHPReader->load($filePath);
                $sheet = $PHPExcel->getSheet(0);
                $allRow = $sheet->getHighestRow();
                $model = new Model();
                $model->startTrans();
                $location_id = $model->table('tb_wms_location')->add($position_data);
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    $box_name = (string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue();
                    if ($this->check_postition(trim($box_name), $position_data['warehouse_id']) > 0) {
                        $error[][] = $currentRow . '货位重复.';
                        goto echo_error;
                    }
                    if (!empty($box_name)) {
                        $check[] = $data['box_name'] = trim($box_name);
                        $data['location_id'] = $location_id;
                        $data['warehouse_id'] = $position_data['warehouse_id'];
                        $datas[] = $data;
                    }
                }
                $check_unique = array_unique($check);
                if (count($check) != count($check_unique)) {
                    $diff = array_diff_assoc($check, $check_unique);

                    $model->rollback();
                    foreach ($diff as $i) {
                        $error[][] = $i . '货位重复.';
                    }
                    goto echo_error;
                }

                if (count($datas) > 0) {
                    $details = $model->table('tb_wms_location_details')->addAll($datas);
                    if ($details > 0) {
                        $model->commit();
                        $this->redirect('position_show', array('location_id' => $location_id));
                    } else {
                        $model->rollback();
                        $error[][] = 'excel数据异常，新增失败.';
                    }
                } else {
                    $model->rollback();
                    $error[][] = 'excel数据为空';
                }
                echo_error:

                $this->assign('check_data', $error);
                $go_url = U('Stock/position_import');
                $this->assign('go_url', $go_url);
                $this->display('error');
                exit;
            } else {
//                upd
                $location_id = I('location_id');
                header("content-type:text/html;charset=utf-8");
                $filePath = $_FILES['expe']['tmp_name'];
                vendor("PHPExcel.PHPExcel");
                $objPHPExcel = new PHPExcel();
                $PHPReader = new PHPExcel_Reader_Excel2007();
                if (!$PHPReader->canRead($filePath)) {
                    $PHPReader = new PHPExcel_Reader_Excel5();
                    if (!$PHPReader->canRead($filePath)) {
                        echo 'no Excel';
                        return;
                    }
                }
                $PHPExcel = $PHPReader->load($filePath);
                $sheet = $PHPExcel->getSheet(0);
                $allRow = $sheet->getHighestRow();
                $model = new Model();
                $model->startTrans();
                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    $box_name = (string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue();

                    if (!empty($box_name)) {
                        $box_name = trim($box_name);
                        $check[] = $data['box_name'] = $box_name;
                        $data['location_id'] = $location_id;
                        $data['warehouse_id'] = $position_data['warehouse_id'];
                        $datas[] = $data;
                    }
                }
                $check_unique = array_unique($check);
                if (count($check) != count($check_unique)) {
                    $diff = array_diff_assoc($check, $check_unique);

                    $model->rollback();
                    foreach ($diff as $i) {
                        $error[][] = $i . '货位重复.';
                    }
                    goto echo_error1;
                }
                if (count($datas) > 0) {
                    $Location_details = M('location_details', 'tb_wms_');
                    $location_details = $Location_details->where('location_id = ' . $location_id)->getField('box_name,occupy');
                    $location_details_box = array_keys($location_details);
                    $box_merge = array_merge($check, $location_details_box);
                    $box_del = array_diff($box_merge, $check);
                    foreach ($box_del as $key => $val) {
                        if ($location_details[$val] > 0) {
                            $model->rollback();
                            $error[][] = $val . '货位有库存' . $location_details[$val] . '，不能删除';
                            goto echo_error1;
                        }
                    }
//                    add
                    $box_add = array_diff($box_merge, $location_details_box);
                    $where_del['box_name'] = array('in', $box_del);
                    if (count($box_del) > 0 || count($box_add) > 0) {
                        $model->table('tb_wms_location_details')->where($where_del)->delete();
                        foreach ($datas as $k => $v) {
                            if (in_array($v['box_name'], $box_add)) {
                                $box_add_data[] = $v;
                            }
                        }
                        $model->table('tb_wms_location_details')->addAll($box_add_data);
                        $model->commit();
                        $this->redirect('position_show', array('location_id' => $location_id));
                    } else {
                        $model->rollback();
                        $error[][] = 'excel无可处理数据';
                    }
                } else {
                    $model->rollback();
                    $error[][] = 'excel数据为空';
                }
                echo_error1:

                $this->assign('check_data', $error);
                $go_url = U('Stock/position_show', array('location_id' => $location_id));
                $this->assign('go_url', $go_url);
                $this->display('error');
                exit;

            }
        }
        $this->location = 1;
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     * 栏位展示
     */
    public function position_show()
    {
        $location_id = I('location_id');
        $Location = M('location', 'tb_wms_');
        $location = $Location->where('id = ' . $location_id)->select();
        $Location_details = M('location_details', 'tb_wms_');
        $location_details = $Location_details
            ->join('left join tb_wms_location_sku on tb_wms_location_sku.warehouse_id  = tb_wms_location_details.warehouse_id AND tb_wms_location_sku.box_name  = tb_wms_location_details.box_name ')
            ->where('location_id = ' . $location_id)
            ->field('tb_wms_location_details.*,tb_wms_location_sku.count as occupy')
            ->select();

        $this->assign('location', json_encode($location, JSON_UNESCAPED_UNICODE));
        $this->assign('location_details', json_encode($location_details, JSON_UNESCAPED_UNICODE));
        $this->assign('location_sum', json_encode(count($location_details), JSON_UNESCAPED_UNICODE));

        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     * 栏位编辑
     */
    public function position_edit()
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        $house_list = $Warehouse->getField('id,company_id,warehouse');
        $this->assign('house_list', json_encode($house_list, JSON_UNESCAPED_UNICODE));
        $l_id = I("l_id");

        $Location = M('location', 'tb_wms_');
        $d_where['location_id'] = $where['id'] = $l_id;
        $location_data = $Location->where($where)->select();

        $Location_details = M('location_details', 'tb_wms_');
        $l_d_data = $Location_details->where($d_where)->select();
        $this->assign('location_data', json_encode($location_data, JSON_UNESCAPED_UNICODE));
        $this->assign('l_d_data', json_encode($l_d_data, JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     *  栏位删除
     */
    public function position_del()
    {
        $id = I("post.id");
        $Location = M('stream', 'tb_wms_');
        $where['location_id'] = $id;
        $location_link = $Location->where($where)->count();
        if ($location_link == 0) {
            $Location = M('location', 'tb_wms_');
            $id = I("post.id");
            $Location->is_show = 0;
            if ($Location->where('id = ' . $id)->save()) {
                $return_arr = array('info' => '删除成功', "status" => "y");
            } else {
                $return_arr = array('info' => '删除失败', "status" => "n");
            }
        } else {
            $return_arr = array('info' => '请先删除关联商品', "status" => "n");
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 出入库管理
     */
    public function warehouse_switch()
    {
        $Bill = M('bill', 'tb_wms_');
        $where['is_show'] = 1;
        if (IS_POST) {
            empty(I("post.link_bill_id")) ? '' : $where['link_bill_id'] = I("post.link_bill_id");
            empty(I("post.batch")) ? '' : $where['batch'] = I("post.batch");
            empty(I("post.bill_id")) ? '' : $where['bill_id'] = I("post.bill_id");
            empty(I("post.other_code")) ? '' : $where['other_code'] = I("post.other_code");
            empty(I("post.bill_type")) ? '' : $where['bill_type'] = I("post.bill_type");
            empty(I("post.house_list_model")) ? '' : $where['warehouse_id'] = I("post.house_list_model");
            empty(I("post.bill_date_ation")) ? '' : $where['bill_date'] = array('EGT', I("post.bill_date_ation"));
            empty(I("post.bill_date_end")) ? '' : $where['bill_date'] = array('ELT', I("post.bill_date_end"));
            empty(I("post.sale_no")) ? '' : $where['sale_no'] = array('eq', I("post.sale_no"));
            if (!empty(I("post.GSKU"))) {
                $Stream = M('stream', 'tb_wms_');
                $where_sku['GSKU'] = I("post.GSKU");
                $bill_id_arr = $Stream->where($where_sku)->field('bill_id')->select();
                foreach ($bill_id_arr as $key => $val) {
                    $bill_id_arr_un[] = $val['bill_id'];
                }
                $bill_id_arr_un = array_unique($bill_id_arr_un);
                $where['id'] = array('in', $bill_id_arr_un);
            }

        }
        $_param = $this->_param();
        $_param = empty($_param) ? 0 : $_param;
        $this->assign('param', json_encode($_param, JSON_UNESCAPED_UNICODE));
        import('ORG.Util.Page');
        $count = $Bill->where($where)->count();
        $Page = new Page($count, 100);
        $show = $Page->show();
        $bills = $Bill->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('bills', json_encode($bills, JSON_UNESCAPED_UNICODE));
        $this->assign('pages', $show);
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('bill_state', json_encode($this->bill_state, JSON_UNESCAPED_UNICODE));
        $this->assign('warehouse_use', json_encode($this->get_use_extends(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('outgo', json_encode($this->get_outgo(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));
        $this->assign('currency', json_encode($this->get_currency(), JSON_UNESCAPED_UNICODE));
        $this->assign('out_storage', json_encode($this->get_outgo('outgoing'), JSON_UNESCAPED_UNICODE));
        $this->assign('rule_storage', json_encode(BaseModel::getRuleStorage(), JSON_UNESCAPED_UNICODE));
        $this->assign('go_url', GO_URL);
        $this->assign('this_user', session('m_loginname'));

        $this->display();
    }

    /**
     * 批量导入
     */
    public function import_bill()
    {
        if (!session('m_loginname')) {
            header("Location:/index.php?m=public&a=login");
        }
        ini_set('max_execution_time', 18000);
        header("content-type:text/html;charset=utf-8");
        $filePath = $_FILES['expe']['tmp_name'];
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);
        $sheet = $PHPExcel->getSheet(0);

        $allRow = $sheet->getHighestRow();
        $outgo_state = I('post.outgo_state');
        $outgo_s = '入库类型';
        if ('-' == $outgo_state) {
            $outgo_s = '出库类型';
        }

        $Guds_opt = M('guds_opt', 'tb_ms_');
        $Location_details = M('location_details', 'tb_wms_');
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            //$bill['company_id'] = $this->upd_cd((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue());
            $bill['channel'] = empty($this->upd_cd((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue())) ? '' : $this->upd_cd((string)$PHPExcel->getActiveSheet()->getCell("A" . $currentRow)->getValue());
            $bill['warehouse_id'] = $this->upd_warehouse((string)$PHPExcel->getActiveSheet()->getCell("B" . $currentRow)->getValue());
            $bill['bill_type'] = $this->upd_cd((string)$PHPExcel->getActiveSheet()->getCell("C" . $currentRow)->getValue(), $outgo_s);
            $bill['bill_state'] = 1;
            //$bill['user_id'] = $this->upd_use((string)$PHPExcel->getActiveSheet()->getCell("E" . $currentRow)->getValue());
            //$bill_date = $PHPExcel->getActiveSheet()->getCell("F" . $currentRow)->getFormattedValue();
            $bill ['user_id'] = BaseModel::getName();
            $bill_date = date('Y-m-d', time());
            $data_all = explode('-', $bill_date);
            $bill['bill_date'] = date('Y-m-d', mktime(0, 0, 0, $data_all[1], $data_all[2] , $data_all[0]));
            $bill['zd_user'] = session('m_loginname');
            $bill['zd_date'] = date('Y-m-d H:m:s');
            $bill_all = $bill['company_id'] . $bill['channel'] . $bill['warehouse_id'] . $bill['bill_type'] . $bill['user_id'] . $bill['bill_date'];
            $bill_hash = md5($bill_all);
            if (!empty($bill_data[$bill_hash]['row'])) {
                $bill['row'] = $currentRow . ',' . $bill_data[$bill_hash]['row'];
            } else {
                $bill['row'] = $currentRow;
            }
            $bill_data[$bill_hash] = $bill;

            $stream['GSKU'] = (string)$PHPExcel->getActiveSheet()->getCell("D" . $currentRow)->getValue();
            $stream['GUDS_OPT_ID'] = $qr_code = (string)$PHPExcel->getActiveSheet()->getCell("E" . $currentRow)->getValue();
            if (empty($stream['GSKU']) && !empty($stream['GUDS_OPT_ID'])) {
                $where_qr['GUDS_OPT_UPC_ID'] = $qr_code;
                $res = $Guds_opt->where($where_qr)->field('GUDS_OPT_ID')->find();
                empty($res) ? $stream['GSKU'] = '' : $stream['GSKU'] = $res['GUDS_OPT_ID'];

            }
            $stream['should_num'] = $stream['send_num'] = (string)$PHPExcel->getActiveSheet()->getCell("F" . $currentRow)->getValue();
            if ('-' == $outgo_state) {
                $stream['unit_price'] = static::get_power($stream['GSKU']);
            } else {
                $stream['unit_price'] = (string)$PHPExcel->getActiveSheet()->getCell("G" . $currentRow)->getValue();
            }

            $stream['taxes'] = (string)$PHPExcel->getActiveSheet()->getCell("H" . $currentRow)->getValue();
            $stream['currency_id'] = $this->upd_cd((string)$PHPExcel->getActiveSheet()->getCell("I" . $currentRow)->getValue());
            $stream['sum'] = (string)$PHPExcel->getActiveSheet()->getCell("J" . $currentRow)->getValue();
            $location = (string)$PHPExcel->getActiveSheet()->getCell("N" . $currentRow)->getValue();
// 货位
            $model = new Model();
            if (!empty($location)) {
                $where_details['box_name'] = $location;
                $where_details['warehouse_id'] = $bill['warehouse_id'];
                $location_id = $Location_details->where($where_details)->getField('id');
//货位增加
                //                      check location
                if (!empty($location_id)) {
                    $wher_sku['sku'] = $stream['GSKU'];
                    $wher_sku['box_name'] = $location;
                    $wher_sku['warehouse_id'] = $bill['warehouse_id'];
                    $location_sku = $model->table('tb_wms_location_sku')->where($wher_sku)->getField('count');
//                                出库
                    if ($outgo_state == "-") {
                        if ($location_sku > $stream['send_num']) {
                            $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setDec('count', $stream['send_num']);
                        } elseif ($location_sku == $stream['send_num']) {
                            $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->delete();
                        } else {
                            $this->back = 2;
                            $return_arr = array('info' => $location . '货位不够', "status" => "n", 'data' => '');
                            break;
                        }
                    } else {
                        if ($location_sku > 0) {
                            $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setInc('count', $stream['send_num']);
                        } else {
                            $wher_sku['count'] = $stream['send_num'];
                            $location_sku_state = $model->table('tb_wms_location_sku')->data($wher_sku)->add();
                        }
                    }
                    $stream['location_id'] = $location_id;
                }
            }

            $stream['row'] .= ' ' . $currentRow;
            $stream_data[$bill_hash][] = $stream;
            unset($bill);
            unset($stream);
        }
// check excel
        if ('-' == $outgo_state) {
            $check_data = $this->check_excel($bill_data, $stream_data, 1);
        } else {
            $check_data = $this->check_excel($bill_data, $stream_data);
        }

        if ('-' == $outgo_state) {
            $check_del_bill = $this->check_del_bill($stream_data, $outgo_state);

            if ($check_del_bill['state']) {
                $check_data[][] = $check_del_bill['sku'];
                $this->assign('check_data', $check_data);
                $this->display();
                exit();
            }
        }
        if (0 == count($check_data)) {
            $model = new Model();
            $model->startTrans();
            $count = 0;
            foreach ($bill_data as $key => $val) {
                $val['bill_id'] = $this->get_bill_id($val['bill_type'], $val['bill_date']);
                unset($val['row']);
                $b_id = $model->table('tb_wms_bill')->data($val)->add();
                if ($b_id) {
                    $stream_datas_s = array();
                    foreach ($stream_data[$key] as $k => &$v) {
                        unset($v['row']);
                        unset($v['GUDS_OPT_ID']);
                        unset($v['sum']);
                        $v['bill_id'] = $b_id;
                        $skuId = $v['GSKU'];
                        $gudsId = substr($v['GSKU'], 0, -2);
                        $changeNm = $v['send_num'];
                        $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm;
                        trace($url, '$url');
                        $get_start = json_decode(curl_request($url), 1);
                        if ($get_start['code'] != 2000) {
                            $this->back = 1;
                            break;
                        }
                        $stream_datas_s[] = $v;
                        $stream_datas_s[] = 1;
                        $count++;
                    }


                    $s_id = $model->table('tb_wms_stream')->addAll($stream_data[$key]);
                    unset($stream_datas);

                }
            }
            if (1 == $this->back || $s_id == 0) {
                $model->rollback();
                $this->error('接口异常' . serialize($get_start) . $url);
            } else {
                $model->commit();
                $this->success($count . '条导入成功');
            }
        } else {
            $this->assign('check_data', $check_data);
            $this->display();
        }
    }


    /**
     *删除订单
     */
    public function del_bill()
    {
        $Bill = M('bill', 'tb_wms_');
        $bill_id = $id = I("post.id");
        $check_del_bill = $this->check_del_bill($bill_id, 'delord');
        if ($check_del_bill['state'] == 1) {
            $return_arr = array('info' => '删除失败,sku>' . $check_del_bill['sku'] . '<数量不足', "status" => "n");
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit();
        }
        $Bill->is_show = 0;
        $bill = $Bill->where('id=' . $id)->save();
        $bill_val = $Bill->where('id=' . $id)->select();

        $Stream = M('stream', 'tb_wms_');
        $all_list = $Stream->where('bill_id=' . $id)->select();

        $get_outgoing = $this->get_outgoing();
        $data['get_outgoing'] = $get_outgoing;
        $data['bill_val'] = $bill_val;
        if (in_array($bill_val[0]['bill_type'], array_keys($get_outgoing))) {
            $outgo_state = null;
        } else {
            $outgo_state = '-';
        }
        $data['$outgo_state'] = $outgo_state;

        foreach ($all_list as $key => $val) {
            $skuId = $val['GSKU'];
            $gudsId = substr($val['GSKU'], 0, -2);
            $changeNm = $val['send_num'];
            $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm;
            $data['urls'][] = $url;

            $get_start = json_decode(curl_request($url), 1);
            if ($get_start['code'] != 2000) {
                throw new Exception('接口异常' . $get_start);
            }
        }


        if ($bill) {
            $return_arr = array('info' => '删除成功', "status" => "y", 'data' => $data);
        } else {
            $return_arr = array('info' => '删除失败', "status" => "n");
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);

    }

    /**
     * get this order good
     */
    /*    public function loading()
        {
            $Stream = D('Stream');
            $where['bill_id'] = I('post.bill_id');
            $stream_arr = $Stream->relation(true)->where($where)->select();
            if ($stream_arr) {
                $return_arr = array('info' => '成功', "status" => "y", 'data' => $stream_arr);
            } else {
                $return_arr = array('info' => '失败', "status" => "n");
            }
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
        }*/

    /**
     * 获取订单商品
     */
    public function loading()
    {
        $Stream = M('stream', 'tb_wms_');
        $where['bill_id'] = I('post.bill_id');
        $stream_arr = $Stream->where($where)
            ->join('left join tb_wms_location_details on tb_wms_location_details.id = tb_wms_stream.location_id')
            ->field('tb_wms_stream.*,tb_wms_location_details.box_name as location')
            ->select();
        $Bill = M('bill', 'tb_wms_');
        $where_bill['id'] = I('post.bill_id');
        $bill_code = $Bill->where($where_bill)->getField('bill_type');
        if (in_array($bill_code, array_keys($this->get_out()))) {
            $bill_type = L('收');
        } else {
            $bill_type = L('出');
        }
        if ($stream_arr) {
            foreach ($stream_arr as $key => &$val) {
                $model = D('Opt');
                $GUDS_ID = $val['GSKU'];
                $guds = $model->relation(true)->where('GUDS_OPT_ID = ' . $GUDS_ID)->select();
                if (empty($guds)) {
                    $this->ajaxReturn(0, $guds, 0);
                    exit();
                }
                $guds['Opt'] = $guds;
                foreach ($guds['Opt'] as $key => $opt) {
                    $opt_val = explode(';', $opt['GUDS_OPT_VAL_MPNG']);
                    foreach ($opt_val as $v) {
                        $val_str = '';
                        $o = explode(':', $v);
                        $model = M('ms_opt', 'tb_');
                        $opt_val_str = $model->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')->where('tb_ms_opt.OPT_ID = ' . $o[0] . ' and tb_ms_opt_val.OPT_VAL_ID = ' . $o[1])->field('tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')->find();
                        if (empty($opt_val_str)) {
                            $val_str = L('标配');
                        } elseif ($opt_val_str['OPT_ID'] == '8000') {
                            $val_str = L('标配');
                        } elseif ($opt_val_str['OPT_ID'] != '8000') {
                            $val_str = $opt_val_str['OPT_CNS_NM'] . ':' . $opt_val_str['OPT_VAL_CNS_NM'] . ' ';
                        }
                        $guds['opt_val'][$key]['val'] .= $val_str;
                        $guds['opt_val'][$key]['GUDS_OPT_ID'] = $opt['GUDS_OPT_ID'];
                        $guds['opt_val'][$key]['GUDS_ID'] = $opt['GUDS_ID'];
                        $guds['opt_val'][$key]['SLLR_ID'] = $opt['SLLR_ID'];
                    }
                }
                $val['guds'] = $guds;
                unset($guds);
            }


            $return_arr = array('info' => '成功', "status" => "y", 'data' => $stream_arr, 'bill_type' => $bill_type);
        } else {
            $return_arr = array('info' => '失败', "status" => "n", 'data' => $stream_arr);
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 出入库详情
     */
    public function inventory_details()
    {
        if (IS_POST) {
            $get_outgo = I("post.outgo");
            switch ($get_outgo) {
                case 'outgoing':
                    $outgo_state = "-";
                    $outgo_state_del = null;
                    break;
                case  'storage':
                    $outgo_state = null;
                    $outgo_state_del = "-";
                    break;
                default:
                    $return_arr = array('info' => '订单出入状态异常', "status" => "n", 'data' => '');
                    echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                    exit;
            }


            $bill_id = I("post.bill_id");
            $data['link_bill_id'] = I("post.link_bill_id");
            $data['warehouse_id'] = I("post.house_list");
            $data['company_id'] = I("post.company");
            $data['bill_type'] = I("post.bill_type");
            $data['bill_date'] = I("post.date");
            $data['batch'] = I("post.batch");
            $data['user_id'] = I("post.warehouse_use");
            $data['bill_state'] = I("post.bill_state");

//
            $data['channel'] = I("post.channel");
            $data['invoice'] = I("post.invoice");
            $data['business'] = I("post.business");
            $data['supplier'] = I("post.supplier");
            $data['due_date'] = I("post.due_date");
            $data['incidental'] = I("post.incidental");
            $data['total_cost'] = I("post.total_cost");
            $data['warehouse_rule'] = I("post.warehouse_rule");
            $data['sale_no'] = I("post.sale_no");
            $data['purchase_logistics_cost'] = I("post.purchase_logistics_cost");
//          outgo filter
            $this->outgoing = array_keys($this->get_outgoing());
            if (in_array($data['bill_type'], $this->outgoing)) {
                $all_list = $this->_param();

                $Stream = M('stream', 'tb_wms_');
                foreach ($all_list['order_lists'] as $key => $val) {
                    $where['bill_id'] = array('neq', '');
                    $where['GSKU'] = $val['GSKU'];
                    $stream_arr = $Stream->where($where)->field('*,sum(send_num) AS sum_num')->group('GSKU,warehouse_id')->order('id')->select();
                    if (1 != 1) {
                        $return_arr = array('info' => '库存不足', "status" => "n", 'data' => '');
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
            }


            if (empty($bill_id)) {
                $data['zd_user'] = session('m_loginname');
                $data['zd_date'] = date('Y-m-d H:m:s');
                $data['bill_id'] = $this->get_bill_id(I("post.bill_type"));
                $model = new Model();
                $model->startTrans();

                $b_id = $model->table('tb_wms_bill')->data($data)->add();

                if ($b_id) {
// add order
                    $all_list = $this->_param();

                    foreach ($all_list['order_lists'] as $key => &$val) {
                        $val['line_number'] = $key;
                        $val['bill_id'] = $b_id;
                        $val['warehouse_id'] = $data['warehouse_id'];
                        $arr_unique[] = $val['GSKU'] . '-' . $val['production_date'];
                        $skuId = $val['GSKU'];
                        $channel = $data['channel'];
                        $gudsId = substr($skuId, 0, -2);
                        $changeNm = (int)$val['send_num'];
                        trace($changeNm, '$changeNm');
                        if ($changeNm <= 0) {
                            $this->back = 3;
                            break;
                        }
//                      check location
                        if (!empty($val['location_id']) || !empty($val['location'])) {
                            $wher_sku['sku'] = $val['GSKU'];
                            $wher_sku['box_name'] = $val['location'];
                            $wher_sku['warehouse_id'] = $data['warehouse_id'];
                            $location_sku = $model->table('tb_wms_location_sku')->where($wher_sku)->getField('count');
//                                出库
                            if ($outgo_state == "-") {
                                if ($location_sku > $val['send_num']) {
                                    $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setDec('count', $val['send_num']);
                                } elseif ($location_sku == $val['send_num']) {
                                    $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->delete();
                                } else {
                                    $this->back = 2;
                                    $return_arr = array('info' => $val['location'] . '货位不够', "status" => "n", 'data' => '');
                                    break;
                                }
                            } else {
                                if ($location_sku > 0) {
                                    $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setInc('count', $val['send_num']);
                                } else {
                                    $wher_sku['count'] = $val['send_num'];
                                    $location_sku_state = $model->table('tb_wms_location_sku')->data($wher_sku)->add();
                                }
                            }

                        }
                        if ($changeNm >= 0) {
                            if ($outgo_state == "-") {
                                $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm . '&channel=' . $channel;
                            } else {
                                $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm . '&channel=' . $channel;
                            }
                            trace($url, '$url1');
                            $urls[] = $url;
                            $get_start = json_decode(curl_request($url), 1);
                            if ($get_start['code'] != 2000) {
                                $this->back = 1;
                                break;
                            }
                        }
                        if (empty($val['location_id'])) {
                            $val['location_id'] = $model->table('tb_wms_location_details')->where("box_name = '" . $val['location'] . "' AND warehouse_id = '" . $data['warehouse_id'] . "'")->getField('id');

                        }
//  组装数组
                        unset($val['goods_name']);
                        unset($val['UP_SKU']);
                        unset($val['bar_code']);
                        unset($val['location']);
                        unset($val['location_list']);
                        $order_lists[] = $val;


                    }
                    if ($this->back == 2) {
                        $model->rollback();
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    if ($this->back == 3) {
                        $model->rollback();
                        $return_arr = array('info' => '数量异常', "status" => "n", 'data' => $all_list);
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    if ($this->back == 1) {
                        $model->rollback();
                        $return_arr = array('info' => '接口异常：回滚订单', "status" => "n", 'data' => $url);
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    if (count($arr_unique) != count(array_unique($arr_unique))) {
                        $model->rollback();
                        $return_arr = array('info' => '单订单SKU编码+生产日期重复', "status" => "n", 'data' => $arr_unique);
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    }


                    $data_start = $model->table('tb_wms_stream')->addAll($order_lists);
                    if ($data_start) {
                        $model->commit();
                        $return_arr = array('info' => '创建成功', "status" => "y");
                    } else {
                        $error_arr['all_list'] = $all_list;
                        $error_arr['urls'] = $urls;
                        $error_arr['data_start_sql'] = $model->getLastSql();
                        $error_arr['data_start_err'] = $model->getDbError();
                        $model->rollback();
                        $return_arr = array('info' => '创建失败', "status" => "n", "data" => $data_start, "error_arr" => $error_arr);
                    }
                } else {
                    $error_arr['data_start_sql'] = $model->getLastSql();
                    $error_arr['data_start_err'] = $model->getDbError();
                    $model->rollback();
                    $return_arr = array('info' => '根订单创建失败', "status" => "n", "data" => $data, "error_arr" => $error_arr);
                }

                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                $data['zd_user'] = I("post.zd_user");
                $data['zd_date'] = I("post.zd_date");

                $data['xg_user'] = session('m_loginname');
                $data['xg_date'] = date('Y-m-d H:m:s');

                $model = new Model();
                $model->startTrans();
                $where_save['bill_id'] = $bill_id;
                $b_id = $model->table('tb_wms_bill')->where($where_save)->save($data);
// remove old order
                $where_bid['bill_id'] = I("post.this_id");
                $all_id = $model->table('tb_wms_stream')->where($where_bid)->field('id')->select();
                foreach ($all_id as $key => $val) {
                    $all_id_all[] = $val['id'];
                }
                $del_api = $model->table('tb_wms_stream')->where($where_bid)->field('id,GSKU,send_num,location_id')->select();

                foreach ($del_api as $key => $val) {
//                      check location
                    if (!empty($val['location_id'])) {
                        $wher_sku['sku'] = $val['GSKU'];
                        $wher_sku['box_name'] = $model->table('tb_wms_location_details')->where("id = '" . $val['location_id'] . "'")->getField('box_name');
                        $wher_sku['warehouse_id'] = $data['warehouse_id'];
                        $location_sku = $model->table('tb_wms_location_sku')->where($wher_sku)->getField('count');
//                                出库
                        if ($outgo_state == "-") {
                            if ($location_sku == 0) {
                                $wher_sku['count'] = $val['send_num'];
                                $location_sku_state = $model->table('tb_wms_location_sku')->data($wher_sku)->add();
                            } else {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setInc('count', $val['send_num']);
                            }
                        } else {
                            if ($location_sku == $val['send_num']) {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->delete();
                            } else {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setDec('count', $val['send_num']);
                            }
                        }
                    }

                    $skuId = $val['GSKU'];
                    $gudsId = substr($skuId, 0, -2);
                    $changeNm = $val['send_num'];
                    $channel = $data['channel'];
                    if ($outgo_state == "-") {
                        $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state_del . $changeNm . '&channel=' . $channel;
                    } else {
                        $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state_del . $changeNm . '&channel=' . $channel;
                    }
                    trace($outgo_state_del, '$outgo_state_del');
                    trace($outgo_state, '$outgo_state');
                    trace($url, '$url3');
                    $get_start = json_decode(curl_request($url), 1);

                    if ($get_start['code'] != 2000) {
                        $return_arr = array('info' => '接口异常：订单处理失败' . $get_start['msg'], "status" => "n", 'data' => $url);
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                }
                $del_where['id'] = array('in', $all_id_all);
                $model->table('tb_wms_stream')->where($del_where)->delete();
// add order
                $all_list = $this->_param();
                foreach ($all_list['order_lists'] as $key => &$val) {
                    $val['id'] = null;
                    $val['bill_id'] = I("post.this_id");
                    $val['line_number'] = $key;
                    $val['warehouse_id'] = $data['warehouse_id'];
                    $arr_unique[] = $val['GSKU'] . '-' . $val['production_date'];
                    empty($val['goods_id']) ? $val['goods_id'] = 0 : null;
                    empty($val['should_num']) ? $val['should_num'] = 0 : null;
                    empty($val['location_id']) ? $val['location_id'] = 0 : null;
                    empty($val['duty']) ? $val['duty'] = 0 : null;
                    empty($val['add_time']) ? $val['add_time'] = '0000-00-00 00:00:00' : null;

                    //                      check location
                    if (!empty($val['location_id']) || !empty($val['location'])) {
                        $wher_sku['sku'] = $val['GSKU'];
                        $wher_sku['box_name'] = $val['location'];
                        $wher_sku['warehouse_id'] = $data['warehouse_id'];
                        $location_sku = $model->table('tb_wms_location_sku')->where($wher_sku)->getField('count');
//                                出库
                        if ($outgo_state == "-") {
                            if ($location_sku > $val['send_num']) {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setDec('count', $val['send_num']);
                            } elseif ($location_sku == $val['send_num']) {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->delete();
                            } else {
                                $this->back = 2;
                                $return_arr = array('info' => $val['location'] . '货位不够', "status" => "n", 'data' => '');
                                break;
                            }
                        } else {
                            if ($location_sku != 0) {
                                $location_sku_state = $model->table('tb_wms_location_sku')->where($wher_sku)->setInc('count', $val['send_num']);
                            } else {
                                $wher_sku['count'] = $val['send_num'];
                                $location_sku_state = $model->table('tb_wms_location_sku')->data($wher_sku)->add();
                            }
                        }

                    }


                    $skuId = $val['GSKU'];
                    $gudsId = substr($skuId, 0, -2);
                    $changeNm = $val['send_num'];
                    $channel = $data['channel'];
                    if ($changeNm > 0) {

                        if ($outgo_state == "-") {
                            $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm . '&channel=' . $channel;
                        } else {
                            $url = HOST_URL_API . '/guds_stock/update_total.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&changeNm=' . $outgo_state . $changeNm . '&channel=' . $channel;

                        }
                        trace($url, '$url2');
                        $get_start = json_decode(curl_request($url), 1);

                        if ($get_start['code'] != 2000) {
                            $model->rollback();
                            $return_arr = array('info' => '接口异常：订单处理失败' . $get_start['msg'], "status" => "n", 'data' => $url);
                            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                            exit();
                        }
                    }
                    unset($val['goods_name']);
                    unset($val['UP_SKU']);
                    unset($val['bar_code']);
                    unset($val['location']);
                    unset($val['location_list']);
                    $order_lists[] = $val;
                }

                if (count($arr_unique) != count(array_unique($arr_unique))) {
                    $return_arr = array('info' => '单订单SKU编码+生产日期重复', "status" => "n", 'data' => $arr_unique);
                    $model->rollback();
                    echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                    exit();
                }
                if ($this->back == 0) {
                    $data_start = $model->table('tb_wms_stream')->addAll($order_lists);
                    if ($data_start) {
                        $model->commit();
                        $return_arr = array('info' => '修改成功', "status" => "y", 'data' => $data_start);
                    } else {
                        $model->rollback();
                        $return_arr = array('info' => '修改失败', "status" => "n", 'data' => $data_start);
                    }
                } else {
                    $model->rollback();
                }

                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit();
            }
        }
        $company_arr = $this->get_company();
        $this->assign('currency_ids', json_encode(BaseModel::getCurrency(), JSON_UNESCAPED_UNICODE));
        $this->assign('company_arr', json_encode($company_arr, JSON_UNESCAPED_UNICODE));
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list_be', json_encode($this->get_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $outgo = $this->get_outgo();
        $this->assign('outgo', json_encode($outgo, JSON_UNESCAPED_UNICODE));
        $warehouse_use = $this->get_use();
        $this->assign('warehouse_use', json_encode($warehouse_use, JSON_UNESCAPED_UNICODE));
        $this->assign('currency', json_encode($this->get_currency(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));
        $this->assign('bill_state', json_encode($this->bill_state, JSON_UNESCAPED_UNICODE));
        $this->assign('user_name', $_SESSION['m_loginname']);
        $this->assign('user_default', json_encode($this->get_default(), JSON_UNESCAPED_UNICODE));

//        add m
        $this->assign('business_list', json_encode($this->get_business_list(), JSON_UNESCAPED_UNICODE));
        $this->assign('supplier_list', json_encode($this->get_supplier_list(), JSON_UNESCAPED_UNICODE));

        $this->display();
    }

    /**
     * 出入库展示
     */
    public function inventory_edit()
    {
//        init loading
        $this->assign('company_arr', json_encode($this->get_company(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('outgo', json_encode($this->get_outgo(), JSON_UNESCAPED_UNICODE));
        $this->assign('warehouse_use', json_encode($this->get_use(), JSON_UNESCAPED_UNICODE));
        $this->assign('currency', json_encode($this->get_currency(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));
        $this->assign('bill_state', json_encode($this->bill_state, JSON_UNESCAPED_UNICODE));
        $this->assign('warehouse_rule', json_encode($this->getwarehouse_rule(I('get.outgoing')), JSON_UNESCAPED_UNICODE));
        $this->assign('outgoing', json_encode(I('get.outgoing'), JSON_UNESCAPED_UNICODE));
        $id = I('get.bill_id');
        $Bill = M('bill', 'tb_wms_');
        $where['id'] = $id;
        $bills = $Bill->where($where)->select();
        $this->assign('bills', json_encode($bills, JSON_UNESCAPED_UNICODE));
        if (in_array($bills[0]['bill_type'], array_keys($this->get_out()))) {
            $this->assign('outgo_state', 'storage');
        } else {
            $this->assign('outgo_state', 'outgoing');
        }
        $Stream = M('stream', 'tb_wms_');
        $wheres['bill_id'] = $id;
        $stream = $Stream->where($wheres)
            ->join('LEFT JOIN tb_wms_location_details on tb_wms_location_details.id = tb_wms_stream.location_id')
            ->field('tb_wms_stream.*,tb_wms_location_details.box_name as location')
            ->select();
        $this->assign('stream', json_encode($stream, JSON_UNESCAPED_UNICODE));
        $this->assign('this_user', session('m_loginname'));
//        add m
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('business_list', json_encode($this->get_business_list(), JSON_UNESCAPED_UNICODE));
        $this->assign('supplier_list', json_encode($this->get_supplier_list(), JSON_UNESCAPED_UNICODE));

        $this->display();
    }

    /**
     * 出入库修改
     */
    public function inventory_xiugai()
    {
//        init loading
        $this->assign('company_arr', json_encode($this->get_company(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list_be', json_encode($this->get_warehouse(), JSON_UNESCAPED_UNICODE));

        $this->assign('warehouse_use', json_encode($this->get_use(), JSON_UNESCAPED_UNICODE));
        $this->assign('currency', json_encode($this->get_currency(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));
        $this->assign('bill_state', json_encode($this->bill_state, JSON_UNESCAPED_UNICODE));

        $id = I('get.bill_id');
        $Bill = M('bill', 'tb_wms_');
        $where['id'] = $id;
        $bills = $Bill->where($where)->select();
        $this->assign('bills', json_encode($bills, JSON_UNESCAPED_UNICODE));

        if (in_array($bills[0]['bill_type'], array_keys($this->get_out()))) {
            $this->assign('outgo_state', 'storage');
            $this->assign('outgo', json_encode($this->get_outgo('storage'), JSON_UNESCAPED_UNICODE));
        } else {
            $this->assign('outgo_state', 'outgoing');
            $this->assign('outgo', json_encode($this->get_outgo('outgoing'), JSON_UNESCAPED_UNICODE));

        }

        $Stream = M('stream', 'tb_wms_');
        $wheres['bill_id'] = $id;
        $stream = $Stream->where($wheres)
            ->join('left join tb_wms_location_details on tb_wms_location_details.id = tb_wms_stream.location_id')
            ->field('tb_wms_stream.*,tb_wms_location_details.box_name as location')
            ->select();
        $this->assign('stream', json_encode($stream, JSON_UNESCAPED_UNICODE));
        $this->assign('this_user', session('m_loginname'));
//        add m
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('business_list', json_encode($this->get_business_list(), JSON_UNESCAPED_UNICODE));
        $this->assign('supplier_list', json_encode($this->get_supplier_list(), JSON_UNESCAPED_UNICODE));

        $this->display();
    }

    /**
     * 出入库确认
     */
    public function confirm_order()
    {
        $Bill = M('bill', 'tb_wms_');
        $where['id'] = I('post.bill_id');
        $data['bill_state'] = 1;
        $data['qr_user'] = session('m_loginname');
        $data['qr_date'] = date('Y-m-d H:m:s');
        $bills = $Bill->where($where)->save($data);
        if ($bills) {
            $return_arr = array('info' => '确认成功', "status" => "y", 'data' => $data);
        } else {
            $return_arr = array('info' => '确认失败', "status" => "n", 'bills' => $bills);
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 取消确认
     */
    public function confirm_no()
    {
        $Bill = M('bill', 'tb_wms_');
        $where['id'] = I('post.bill_id');
        $data['bill_state'] = 0;
        $data['qr_user'] = '';
        $data['qr_date'] = '';
        $bills = $Bill->where($where)->save($data);
        if ($bills) {
            $return_arr = array('info' => '取消确认成功', "status" => "y", 'data' => $data);
        } else {
            $return_arr = array('info' => '取消确认失败', "status" => "n", 'bills' => $bills);
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * SKU查询
     */
    public function search_sku()
    {
        if (IS_POST) {
            $location = $this->get_goods(I("post.GSKU"));
            if (!empty($location)) {
                if (count($location) == 1) {
                    foreach ($location as $key => $val) {
                        $data_one = $val;
                    }
                    $return_arr = array('info' => '查询成功', "status" => "y", 'data' => $data_one, 'key' => 0);
                } else {
                    $return_arr = array('info' => '查询成功', "status" => "y", 'data' => $location, 'key' => 1);
                }
            } else {
                $return_arr = array('info' => '查询无结果', "status" => "n", 'data' => $location);
            }
        } else {
            $return_arr = array('info' => '错误请求', "status" => "n");
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 库存锁定/解锁
     */
    public function lock()
    {
        $post = $this->_param();
        $skuadd = I('get.skuadd');
        if (!empty($skuadd)) {
            $this->assign('skuadd', $skuadd);
        }
        if (empty($post) || !empty($skuadd)) {
            $post = array(
                'init_key' => 'SKU',
                'init_value' => '',
                'DELIVERY_WAREHOUSE' => '',
            );
        }


        $this->assign('lock_list', $this->get_lock('goods', $post));

        $this->assign('post', json_encode($post, JSON_UNESCAPED_UNICODE));
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));

        $this->display();
    }

    /**
     * 锁库日志
     */
    public function lock_log()
    {
        $post = $this->_param();
        if (empty($post)) {
            $post = array(
                'init_key' => 'SKU',
                'init_value' => '',
                'DELIVERY_WAREHOUSE' => '',
            );
        }
        $this->assign('lock_list', $this->get_lock_log($post));

        $this->assign('post', json_encode($post, JSON_UNESCAPED_UNICODE));
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('metering', json_encode($this->get_metering(), JSON_UNESCAPED_UNICODE));
        $this->display();
    }

    /**
     * 获取渠道SKU
     */
    public function get_gshopper_sku()
    {

        $post_json = file_get_contents('php://input', 'r');
        $post = json_decode($post_json);
        /*  $Guds_store = M('guds_store', 'tb_ms_');
          $where['GUDS_ID'] = substr($post->sku, 0, -2);
  //        $where['PLAT_CD'] = $post->channel;
          $where['STORE_STATUS'] = 'N000840300';
          $guds_store = $Guds_store->where($where)->field('ID,GUDS_ID,GUDS_NM')->select();
          if (count($guds_store) > 0) {*/
        $Drguds_opt = M('drguds_opt', 'tb_ms_');
        $where_opt['GUDS_OPT_ID'] = $post->sku;
        $where_opt['THRD_SKU_ID'] = array('neq', '');
        $drguds_opt = $Drguds_opt->where($where_opt)->field('GUDS_OPT_ID,SKU_ID,THRD_SKU_ID')->select();
        if (count($drguds_opt) > 0) {
            $return_data = [
                code => '20000',
                msg => 'success',
                data => $drguds_opt
            ];
        } else {
            $return_data = [
                code => '40001',
                msg => '没有相关数据',
                data => $drguds_opt
            ];
        }

        /* } else {
             $return_data = [
                 code => '30001',
                 msg => '商品可能未发布',
                 data => $guds_store
             ];
         }*/
        echo json_encode($return_data, JSON_UNESCAPED_UNICODE);
    }


    /**
     *展示商品
     */
    public function search_goods()
    {
        $GUDS_ID = I('post.GSKU');
        $guds_s = M('guds', 'tb_ms_');
        $where_guds['MAIN_GUDS_ID'] = substr($GUDS_ID, 0, -2);
        $res = $guds_s->where($where_guds)->field('GUDS_CNS_NM,GUDS_CODE,VALUATION_UNIT,DELIVERY_WAREHOUSE')->select();
        if (count($res) > 0) {
            $return = array('status' => 'y', 'msg' => '', 'data' => $res);
        } else {
            $return = array('status' => 'n', 'msg' => 'SKU异常', 'data' => $res);
        }
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }


    /**
     * 获取锁定
     */
    public function get_lock($goods = null, $post = null, $sku = null, $channel = null)
    {
        $Stand = M('standing', 'tb_wms_');
        $where_stand['total_inventory'] = array('neq', 0);
        $where_stand['channel'] = array('neq', 'N000830100');

        if (!empty($sku) && !empty($channel)) {
            $where_stand['SKU_ID'] = $sku;
            $where_stand['channel'] = $channel;
        }

        if ($post['init_key'] == 'SKU') {
            $where_stand['SKU_ID'] = array('like', "%" . $post['init_value'] . "%");
        }
        $show = null;
        if (($post['init_key'] != 'SKU' && empty($post['init_value'])) && empty($post['DELIVERY_WAREHOUSE'])) {
            import('ORG.Util.Page');
            $count = $Stand->where($where_stand)->count();
            $Page = new Page($count, 30);
            $show = $Page->show();
        } else {
            $stream_arr = $Stand->where($where_stand)
                ->field('SKU_ID,total_inventory,channel,CHANNEL_SKU_ID')
                ->order('SKU_ID,channel desc')
                ->select();
        }
        $this->assign('pages', $show);


        if ('goods' == $goods) {
            $guds_s = M('guds', 'tb_ms_');
            foreach ($stream_arr as $key => $val) {
                $where_guds['MAIN_GUDS_ID'] = substr($val['SKU_ID'], 0, -2);
                if (empty($post['init_value']) && empty($post['DELIVERY_WAREHOUSE'])) {
                    $val['goods'] = $guds_s->where($where_guds)->field('GUDS_CNS_NM,GUDS_CODE,VALUATION_UNIT,DELIVERY_WAREHOUSE')->select();
                    empty($val['goods']) ? '' : $vals[] = $val;

                } else {
                    if (($post['init_key'] != 'SKU' && empty($post['init_value'])) && empty($post['DELIVERY_WAREHOUSE'])) {

                    } else {
                        empty($post['init_value']) ? '' : $where_guds[$post['init_key']] = array('like', "%" . $post['init_value'] . "%");
                        empty($post['DELIVERY_WAREHOUSE']) ? '' : $where_guds['DELIVERY_WAREHOUSE'] = array('like', "%" . $post['DELIVERY_WAREHOUSE'] . "%");
                        $val['goods'] = $guds_s->where($where_guds)->field('GUDS_CNS_NM,GUDS_CODE,VALUATION_UNIT,DELIVERY_WAREHOUSE')->select();
                        empty($val['goods']) ? '' : $vals[] = $val;

                    }
                }

            }
        }
        $return = $vals;
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    /**
     *获取锁日志
     */
    public function get_lock_log($post, $goods = 'goods')
    {
        $Lock_log = M('lock_log', 'tb_wms_');
        if (!empty($sku) && !empty($channel)) {
            $where_stand['GSKU'] = $sku;
            $where_stand['channel'] = $channel;
        }

        if ($post['init_key'] == 'SKU') {
            $where_stand['GSKU'] = array('like', "%" . $post['init_value'] . "%");
        }
        $show = null;
        if (($post['init_key'] != 'SKU' && empty($post['init_value'])) && empty($post['DELIVERY_WAREHOUSE'])) {
            import('ORG.Util.Page');
            $count = $Lock_log->where($where_stand)->count();
            $Page = new Page($count, 30);
            $show = $Page->show();
            $stream_arr = $Lock_log->where($where_stand)
                ->field('tb_wms_lock_log.GSKU,tb_wms_lock_log.lock_sum,tb_wms_lock_log.unlock_sum,tb_wms_lock_log.channel,tb_wms_lock_log.operate_time,bbm_admin.M_NAME as user_name')
                ->join('left join bbm_admin on bbm_admin.M_ID =  tb_wms_lock_log.user_id')
                ->order('tb_wms_lock_log.operate_time desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        } else {
            $stream_arr = $Lock_log->where($where_stand)
                ->field('tb_wms_lock_log.GSKU,tb_wms_lock_log.lock_sum,tb_wms_lock_log.unlock_sum,tb_wms_lock_log.channel,tb_wms_lock_log.operate_time,bbm_admin.M_NAME as user_name')
                ->join('left join bbm_admin on bbm_admin.M_ID =  tb_wms_lock_log.user_id')
                ->order('tb_wms_lock_log.operate_time desc')
                ->select();
        }

        $this->assign('pages', $show);


        if ('goods' == $goods) {
            $guds_s = M('guds', 'tb_ms_');
            trace($post, '$post');
            foreach ($stream_arr as $key => $val) {
                $where_guds['MAIN_GUDS_ID'] = substr($val['GSKU'], 0, -2);
                if (empty($post['init_value']) && empty($post['DELIVERY_WAREHOUSE'])) {
                    $val['goods'] = $guds_s->where($where_guds)->field('GUDS_CNS_NM,GUDS_CODE,VALUATION_UNIT,DELIVERY_WAREHOUSE')->select();
                    empty($val['goods']) ? '' : $vals[] = $val;

                } else {
                    if (($post['init_key'] != 'SKU' && empty($post['init_value'])) && empty($post['DELIVERY_WAREHOUSE'])) {

                    } else {
                        empty($post['init_value']) ? '' : $where_guds[$post['init_key']] = array('like', "%" . $post['init_value'] . "%");
                        empty($post['DELIVERY_WAREHOUSE']) ? '' : $where_guds['DELIVERY_WAREHOUSE'] = array('like', "%" . $post['DELIVERY_WAREHOUSE'] . "%");
                        $val['goods'] = $guds_s->where($where_guds)->field('GUDS_CNS_NM,GUDS_CODE,VALUATION_UNIT,DELIVERY_WAREHOUSE')->select();
                        empty($val['goods']) ? '' : $vals[] = $val;

                    }
                }

            }
        }
        $return = $vals;
        return json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    /**
     *保存锁
     */
    public function save_lock()
    {
        $post = file_get_contents('php://input', 'r');
        $post = json_decode($post);
        $params = $post->params;

        $log['sku'] = $lock['skuid'] = $params->sku;
        $lock['gudsid'] = substr($lock['skuid'], 0, -2);
        $log['total_inventory'] = $lock['number'] = $params->total_inventory;
        $log['channel'] = $lock['channel'] = $params->channel;

        $log['channelSkuId'] = $lock['channelSkuId'] = $params->channel_sku;
        if (empty($lock['channel'])) {
            $return = array('status' => 'n', 'code' => 4001, 'msg' => '渠道缺失', 'curl_data' => '', 'data' => '', 'url' => '', 'post_msg' => '');
            goto return_back;
        }
        $lock_arr[] = $lock;
        $url = HOST_URL_API . '/guds_stock/muti_lock.json';

        /* if ($get_start['code'] == 2000) {
 //            gshopper-kr N000831400
             if ($lock['channel'] == 'N000831400') {
                 $Standing = M('standing', 'tb_wms_');
                 $total_inventory = $Standing->where("channel = 'N000831400' AND SKU_ID = " . $lock['skuid'])->getField('total_inventory');
                 $rbmq = new RabbitMqModel();
                 $rbmq->exchangeName = EXCHANGENAME;
                 $rbmq->routeKey = ROUTEKEY;
                 $rbmq->queueName = 'Q-B5C2GS-03';
                 $msg = [
                     "platCode" => 'N000831400',
                     "processId" => create_guid(),
                     "data" => [
                         "stocks" => [
                             "skuId" => $lock['skuid'],
                             "stockCount" => $lock['number'],
                             "totalStockCount" => $total_inventory,
                             "status" => 0
                         ]
                     ]
                 ];
                 $rbmq->setData($msg);
                 $rbmq->submit();
             }
             $return = array('status' => 'y', 'msg' => '锁定成功', 'data' => $params, 'code' => $get_start['code'], 'msg' => $get_start['msg']);
         } else {
             $return = array('status' => 'n', 'code' => $get_start['code'], 'msg' => $get_start['msg'], 'curl_data' => $get_start['data'], 'data' => $lock_arr, 'url' => $url);
         }*/

        if ($lock['channel'] == 'N000831400') {

            $Standing = M('standing', 'tb_wms_');
            $total_inventory = $Standing->where("channel = 'N000831400' AND SKU_ID = " . $lock['skuid'])->getField('total_inventory');

            $Drguds_opt = M('drguds_opt', 'tb_ms_');
            $where_opt['tb_ms_drguds_opt.SKU_ID'] = $lock['channelSkuId'];
            $drguds_opts = $Drguds_opt->where($where_opt)
                ->join('left join tb_ms_guds_store on tb_ms_guds_store.ID = tb_ms_drguds_opt.GUDS_ID')
                ->getField('tb_ms_drguds_opt.GUDS_ID,tb_ms_drguds_opt.GUDS_OPT_ID,tb_ms_drguds_opt.SKU_ID,tb_ms_drguds_opt.THRD_SKU_ID,tb_ms_guds_store.THRD_GUDS_ID');
            $drguds_opt = array_values($drguds_opts)[0];
            $get_data['get_msg'] = $msg = [
                "platCode" => 'N000831400',
                "processId" => create_guid(),
                "data" => [
                    "stocks" => [
                        [
                            "gudsId" => $drguds_opt['GUDS_ID'],
                            "thrdGudsId" => $drguds_opt['THRD_GUDS_ID'],
                            "skuId" => $drguds_opt['SKU_ID'],
                            "thrdSkuId" => $drguds_opt['THRD_SKU_ID'],
                            "stockCount" => $lock['number'],
                            "totalStockCount" => $total_inventory,
                            "status" => 0
                        ]
                    ]
                ]
            ];

            $get_data['get_url'] = $url_asyn = GSHOPPER . '/product/allotProductStock.json';
            $get_data['get_data'] = $get_datas = curl_get_json($url_asyn, json_encode($msg));
            $get_data['get_asyn'] = $get_asyn = json_decode($get_datas, 1);
            $get_data['time'] = date("Y-m-d H:i:s");
            trace($get_data, '$get_data');
            if ($get_asyn['code'] != 2000) {
                $return = array('status' => 'n', 'code' => $get_asyn['code'], 'msg' => $get_asyn['data'], 'curl_data' => $get_asyn['data'], 'data' => $get_asyn, 'url' => $url, 'post_msg' => $msg);
                goto return_back;
            }
        }
        $get_start = json_decode(curl_get_json($url, json_encode($lock_arr)), 1);

        if ($get_start['code'] == 2000) {
            $return = array('status' => 'y', 'msg' => '锁定成功', 'data' => $params, 'code' => $get_start['code'], 'msg' => $get_start['msg'], 'post_data' => $lock_arr);
        } else {
            $return = array('status' => 'n', 'code' => $get_start['code'], 'msg' => $get_start['msg'], 'curl_data' => $get_start['data'], 'data' => $lock_arr, 'url' => $url);
        }


        return_back:
        //        add log
        $log['return'] = serialize($return);
        $log_arr[] = $log;
        $this->add_lock_log($log_arr);

        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 删除锁
     */
    public function del_lock()
    {
        $post = file_get_contents('php://input', 'r');
        $post = json_decode($post);
        $params = $post->params;

//        join array
        $where['SKU_ID'] = $log['sku'] = $lock['skuid'] = $params->sku;
        $where['GUSD_ID'] = $lock['gudsid'] = substr($lock['skuid'], 0, -2);
        $where['channel'] = $log['channel'] = $lock['channel'] = $params->channel;
        $where['CHANNEL_SKU_ID'] = $log['channelSkuId'] = $lock['channelSkuId'] = $params->channel_sku;
        $init_num = $params->init_num;
//       有占用禁止删除 ，can update total_inventory to sale
        $where['sale'] = $log['sale'] = $params->total_inventory;


        //        search
        $Standing = M('standing', 'tb_wms_');
        $sale = $Standing->where($where)->getField('sale');
        if ($sale < $init_num) {
            $return = array('status' => 'n', 'code' => '', 'msg' => '库存不够', 'sale' => $sale);
            echo json_encode($return, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $log['unlock_sum'] = $lock['number'] = '-' . $init_num;

        $lock_arr[] = $lock;
        $url = HOST_URL_API . '/guds_stock/muti_lock.json';

        if ($lock['channel'] == 'N000831400') {

            $Standing = M('standing', 'tb_wms_');
            $total_inventory = $Standing->where("channel = 'N000831400' AND SKU_ID = " . $lock['skuid'])->getField('total_inventory');

            $Drguds_opt = M('drguds_opt', 'tb_ms_');
            $where_opt['tb_ms_drguds_opt.SKU_ID'] = $lock['channelSkuId'];
            $drguds_opts = $Drguds_opt->where($where_opt)
                ->join('left join tb_ms_guds_store on tb_ms_guds_store.ID = tb_ms_drguds_opt.GUDS_ID')
                ->getField('tb_ms_drguds_opt.GUDS_ID,tb_ms_drguds_opt.GUDS_OPT_ID,tb_ms_drguds_opt.SKU_ID,tb_ms_drguds_opt.THRD_SKU_ID,tb_ms_guds_store.THRD_GUDS_ID');
            $drguds_opt = array_values($drguds_opts)[0];
            $get_data['get_msg'] = $msg = [
                "platCode" => 'N000831400',
                "processId" => create_guid(),
                "data" => [
                    "stocks" => [
                        [
                            "gudsId" => $drguds_opt['GUDS_ID'],
                            "thrdGudsId" => $drguds_opt['THRD_GUDS_ID'],
                            "skuId" => $drguds_opt['SKU_ID'],
                            "thrdSkuId" => $drguds_opt['THRD_SKU_ID'],
//                        "stockCount" => $total_inventory + $lock['number'],
                            "stockCount" => $lock['number'],
                            "totalStockCount" => $total_inventory,
                            "status" => 0  // should upd 1 del stock type
                        ]
                    ]
                ]
            ];

            $get_data['get_url'] = $url_asyn = GSHOPPER . '/product/allotProductStock.json';
            $get_data['get_data'] = $get_datas = curl_get_json($url_asyn, json_encode($msg));
            $get_data['get_asyn'] = $get_asyn = json_decode($get_datas, 1);
            $get_data['time'] = date("Y-m-d H:i:s");
            trace($get_data, '$get_data');
            if ($get_asyn['code'] != 2000) {
                $return = array('status' => 'n', 'code' => $get_asyn['code'], 'msg' => $get_asyn['data'], 'curl_data' => $get_asyn['data'], 'data' => $lock_arr, 'url' => $url, 'post_msg' => $msg);
                goto return_back_to;
            }
        }

        $get_start = json_decode(curl_get_json($url, json_encode($lock_arr)), 1);
        if ($get_start['code'] == 2000) {
            $return = array('status' => 'y', 'msg' => '解锁成功', 'data' => $params, 'code' => $get_start['code'], 'msg' => $get_start['msg'], 'curl_data' => $get_start['data']);
        } else {
            $return = array('status' => 'n', 'code' => $get_start['code'], 'msg' => $get_start['msg'], 'curl_data' => $get_start['data'], 'data' => $lock_arr, 'url' => $url);
        }
        return_back_to:
        $log['return'] = serialize($return);
        $log_arr[] = $log;
        $this->add_lock_log($log_arr, 'del');
        echo json_encode($return, JSON_UNESCAPED_UNICODE);
    }

    /**
     * lock log add
     */
    private function add_lock_log($log_arr, $type = '')
    {
        foreach ($log_arr as $key => $val) {
            $add['GSKU'] = $val['sku'];
            $add['lock_sum'] = $val['total_inventory'];
            $add['unlock_sum'] = $val['unlock_sum'];
            $add['channel'] = $val['channel'];
            $add['return'] = $val['return'];
            $add['user_id'] = $_SESSION['userId'];
            $add['operate_time'] = date("Y-m-d H:i:s");
            $adds[] = $add;
        }
        trace($adds, '$adds');
        $Lock_log = M('lock_log', 'tb_wms_');
        $log_start = $Lock_log->addAll($adds);
        return $log_start;
    }

    /**
     * 效验锁数目
     */
    public function check_lock_num()
    {
        $post = file_get_contents('php://input', 'r');
        $post = json_decode($post);
        $params = $post->params;

        $sku = $params->sku;
        $channel = $params->channel;
        $channel_sku = $params->channel_sku;
        $where['SKU_ID'] = $sku;
        if ($channel != 1) {
            $where['channel'] = $channel;
        }
        if ($channel_sku != '') {
            $where['CHANNEL_SKU_ID'] = $channel_sku;
        }
        $Standing = M('standing', 'tb_wms_');
        echo json_decode($Standing->where($where)->getField('sale'), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 现存量查询
     */
    public function existing()
    {
        $params = $this->_param();
        $Stand = M('standing', 'tb_wms_');
        $where_stand['total_inventory'] = array('neq', 0);

        $get_data = $this->_param();
        $check_data = ['SKU', 'GUDS_CNS_NM', 'DELIVERY_WAREHOUSE'];
        $show = null;

        if ('down' != I('post.down')) {
            if (!empty(I("SKU"))) {
                $where_stand['tb_wms_standing.SKU_ID'] = array('like', "%" . I("SKU") . "%");
                // $where ['tb_wms_standing.SKU_ID'] = ['like', '%' . I("SKU") . '%'];
                // $where ['tmg.GUDS_CODE'] = ['like', '%' . I("SKU") . '%'];
                //$where ['tmg.GUDS_OPT_UPC_ID'] = ['like', '%' . I("SKU") . '%'];
                // $where['_logic'] = 'or';
                // $where_stand['_complex'] = $where;
            }
            if (!empty(I("GUDS_CNS_NM"))) {
                $where_stand['tmg.GUDS_NM'] = array('like', "%" . I("GUDS_CNS_NM") . "%");
            }
            if (!empty(I("DELIVERY_WAREHOUSE"))) {
                $where_stand['tmg.DELIVERY_WAREHOUSE'] = array('eq', I("DELIVERY_WAREHOUSE"));
            }
            if (1 != I("channel")) {
                $where_stand['tb_wms_standing.channel'] = array('eq', 'N000830100');
            }
        }
        if (!empty($params['sku_none'])) {
            $nwhere_stand = $where_stand;
            $nwhere_stand ['total_inventory'] = ['eq', 0];
            unset($where_stand['total_inventory']);
        }
        if (empty(I("DELIVERY_WAREHOUSE")) && empty(I("GUDS_CNS_NM"))) {
            $top_data = $Stand->cache(true, 300)->where($where_stand)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->field('tb_wms_power.weight,tb_wms_standing.total_inventory')
                ->select();
            $ntop_data = $Stand->cache(true, 300)->where($nwhere_stand)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->field('tb_wms_power.weight,tb_wms_standing.total_inventory')
                ->select();
        } else {
            $top_data = $Stand->cache(true, 30)->where($where_stand)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                //->join('left join tb_ms_guds_opt on tmg.MAIN_GUDS_ID = tb_ms_guds_opt.GUDS_ID')
                ->field('tb_wms_power.weight,tb_wms_standing.total_inventory')
                ->select();

            $ntop_data = $Stand->cache(true, 30)->where($nwhere_stand)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                ->field('tb_wms_power.weight,tb_wms_standing.total_inventory')
                ->select();
        }

        $count = count($top_data);
        $ncount = count($ntop_data);
        $top_nums = 0;
        $top_sums = 0;
        foreach ($top_data as $v) {
            $top_nums += $v['total_inventory'];
            $top_sums += $v['total_inventory'] * $v['weight'];
        }

        if ('down' != I('post.down')) {
            import('ORG.Util.Page');
            $page_num = I('page_num') > 0 ? I('page_num') : 20;
            $Page = new Page($count, $page_num);
            $Page->page_num = $page_num;
            $show = $Page->show();
            $model_s = M();
            $sql_s = $model_s->table('tb_wms_standing')->where($where_stand)
                ->field('tb_wms_standing.*,tmg.DELIVERY_WAREHOUSE as warehouse_id')
                ->join('left join  (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg  on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select(false);
            $stream_arr = $model_s->table($sql_s . ' s')
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = s.SKU_ID')
                ->order('s.SKU_ID,s.channel desc')
                ->field('s.*,tb_wms_power.weight')
                ->select();
        } else {
            $stream_arr = $Stand->where($where_stand)
                ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
                ->join('left join (select * from tb_ms_guds group by tb_ms_guds.DELIVERY_WAREHOUSE,tb_ms_guds.MAIN_GUDS_ID) tmg on tmg.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
                ->order('tb_wms_standing.SKU_ID,tb_wms_standing.channel desc')
                ->field('tb_wms_standing.*,tb_wms_power.weight,tmg.DELIVERY_WAREHOUSE as warehouse_id')
                ->select();
        }
        if ($stream_arr) {
            foreach ($stream_arr as $key => &$val) {
                $sku = $val['SKU_ID'];
                if (empty($val['warehouse_id'])) {
                } else {
                    $model = D('Opt');
                    $GUDS_ID = $val['SKU_ID'];
                    // tb_wms_stand 表中的sku_id等于tb_ms_guds_opt表中的GUDS_OPT_ID
                    $guds = $model->relation(true)->where('GUDS_OPT_ID = ' . $GUDS_ID)->select();
                    $guds['Opt'] = $guds;
                    // 查询商品的属性，在tb_ms_opt表中
                    foreach ($guds['Opt'] as $key => $opt) {
                        $opt_val = explode(';', $opt['GUDS_OPT_VAL_MPNG']);
                        foreach ($opt_val as $v) {
                            $val_str = '';
                            $o = explode(':', $v);
                            $model = M('ms_opt', 'tb_');
                            $opt_val_str = $model->cache(true, 300)->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')->where('tb_ms_opt.OPT_ID = ' . $o[0] . ' and tb_ms_opt_val.OPT_VAL_ID = ' . $o[1])->field('tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')->find();
                            if (empty($opt_val_str)) {
                                $val_str = L('标配');
                            } elseif ($opt_val_str['OPT_ID'] == '8000') {
                                $val_str = L('标配');
                            } elseif ($opt_val_str['OPT_ID'] != '8000') {
                                $val_str = $opt_val_str['OPT_CNS_NM'] . ':' . $opt_val_str['OPT_VAL_CNS_NM'] . ' ';
                            }
                            $guds['opt_val'][$key]['val'] .= $val_str;
                        }
                    }
                    $val['guds'] = $guds;
                    unset($guds);
                    $new_stream_arr[] = $val;
                }
            }
        }
        if ('down' == I('post.down')) {
            $this->down_existing($new_stream_arr);
        } else {
            $_param = $this->_param();
            $_param = empty($_param) ? 0 : $_param;
            $this->assign('param', json_encode($_param, JSON_UNESCAPED_UNICODE));
            $this->assign('go_url', GO_URL);
            $this->assign('stream_arr', json_encode($new_stream_arr, JSON_UNESCAPED_UNICODE));
            $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
            $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
            $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
            $this->assign('pages', $show);
            $this->assign('count', $count);
            $this->assign('ncount', $ncount);
            $this->assign('top_nums', $top_nums);
            $this->assign('top_sums', number_format($top_sums, 2));
            $this->display();
        }
    }

    /**
     * 可以展示无库存切有在途的数据
     *
     */
    public function existing_extend()
    {
        import('ORG.Util.Page');
        $params = $this->getParams();
        $model = D('TbWmsStanding');
        // 生成查询条件
        $conditions = $model->search($params);
        // 总量
        $count = $model->join('left join tb_ms_guds_opt on tb_wms_standing.SKU_ID = tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_ms_guds on tb_wms_standing.GUDS_ID = tb_ms_guds.GUDS_ID')
            ->where($conditions)
            ->count();
        $page = new Page($count, 20);
        $show = $page->show();
        // 数据
        $ret = $model->join('left join tb_ms_guds_opt on tb_wms_standing.SKU_ID = tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_wms_power on tb_wms_standing.SKU_ID = tb_wms_power.SKU_ID')
            ->join('left join tb_ms_guds on tb_wms_standing.GUDS_ID = tb_ms_guds.GUDS_ID')
            ->field('tb_wms_standing.*, tb_ms_guds_opt.*, tb_ms_guds.*, tb_wms_power.weight')
            ->where($conditions)
            ->limit($page->firstRow, $page->listRows)
            ->select();
        // 总量、总价
        $statistics = $model->join('left join tb_ms_guds_opt on tb_wms_standing.SKU_ID = tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_wms_power on tb_wms_standing.SKU_ID = tb_wms_power.SKU_ID')
            ->join('left join tb_ms_guds on tb_wms_standing.GUDS_ID = tb_ms_guds.GUDS_ID')
            ->field('sum(tb_wms_standing.total_inventory*tb_wms_power.weight) as moneny, sum(tb_wms_standing.total_inventory) as num, sum(on_way) as ow, sum(on_way_money) as owm')
            ->where($conditions)
            ->find();
        // 在途sku
        $conditions ['on_way'] = ['neq', 0];
        $statistics_sku_on_way = $model->join('left join tb_ms_guds_opt on tb_wms_standing.SKU_ID = tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_wms_power on tb_wms_standing.SKU_ID = tb_wms_power.SKU_ID')
            ->join('left join tb_ms_guds on tb_wms_standing.GUDS_ID = tb_ms_guds.GUDS_ID')
            ->field('count(tb_wms_standing.SKU_ID) as on_ways')
            ->where($conditions)
            ->find();
        foreach ($ret as $key => &$val) {
            $val ['GUDS_OPT_VAL_MPNG'] = $this->gudsOptsMerge($val ['GUDS_OPT_VAL_MPNG']);
        }
        $this->assign('stream_arr', json_encode($ret, JSON_UNESCAPED_UNICODE));
        $this->assign('param', json_encode($params, JSON_UNESCAPED_UNICODE));
        $this->assign('go_url', GO_URL);
        $this->assign('sale_teams', json_encode(BaseModel::saleTeamCd(), JSON_UNESCAPED_UNICODE));
        $this->assign('sp_teams', json_encode(BaseModel::spTeamCd(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('all_channel', json_encode($this->get_all_channel(), JSON_UNESCAPED_UNICODE));
        $this->assign('pages', $show);
        $this->assign('count', $count);
        $this->assign('top_nums', $statistics ['num']);
        $this->assign('top_sums', number_format($statistics ['moneny'], 2));
        $this->assign('on_ways', $statistics_sku_on_way['on_ways']);
        $this->assign('ow', $statistics ['ow']);
        $this->assign('owm', $statistics ['owm']);
        $this->assign('top_sums', number_format($statistics ['moneny'], 2));
        $this->assign('detail_show', '/index.php?m=orders&a=get_order_type&ordId=');
        $this->display();
    }

    /**
     * 商品属性组装
     *
     */
    public function gudsOptsMerge($val)
    {
        $str = explode(';', $val);
        $opt = BaseModel::getGudsOpt();
        $shtml = '';
        $length = count($str);
        for ($i = 0; $i < $length; $i ++) {
            if ($opt[$str[$i]]['OPT_CNS_NM'] and $opt[$str[$i]]['OPT_VAL_CNS_NM']) $shtml .= $opt[$str[$i]]['OPT_CNS_NM'] . ':' . $opt[$str[$i]]['OPT_VAL_CNS_NM'] . ' ';
            else $shtml .=  $opt[$str[$i]]['OPT_VAL_CNS_NM'];
        }
        return $shtml;
    }

    /**
     * 现存量数据下载，全部下载
     *
     */
    public function download_existing_to_excel()
    {
        $model = D('TbWmsStanding');
        $ret = $model->join('left join tb_ms_guds_opt on tb_wms_standing.SKU_ID = tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_wms_power on tb_wms_standing.SKU_ID = tb_wms_power.SKU_ID')
            ->join('left join tb_ms_guds on tb_wms_standing.GUDS_ID = tb_ms_guds.GUDS_ID')
            ->field('tb_wms_standing.*, tb_ms_guds_opt.*, tb_ms_guds.*, tb_wms_power.weight')
            //->where($conditions)
            ->select();
        $this->down_existing_extends($ret);
    }

    public function down_existing_extends($e)
    {
        $expTitle = "现存量查询";
        $expCellName = array(
            array('SKU_ID', 'SKU编码'),
            array('channel', '渠道'),
            array('GUDS_CNS_NM', '商品名称'),
            array('GUDS_OPT_UPC_ID', '条形码'),
            array('opt_val', '属性'),
            array('warehouse', '仓库'),
            array('total_inventory', '总库存数'),
            array('sale', '可售'),
            array('occupy', '占用'),
            array('locking', '锁定'),
            array('weight', '成本价'),
            array('weighting_sum', '库存成本'),
            array('on_way', '在途数量'),
            array('on_way_money', '在途金额'),
        );
        $all_channel = $this->get_all_channel();
        $house_all_list = $this->get_all_warehouse();
//        join exp excel
        $gudsOpt = BaseModel::getGudsOpt();
        foreach ($e as $key => $val) {
            $join_data['SKU_ID'] = $val['SKU_ID'];
            $join_data['channel'] = $all_channel[$val['channel']]['CD_VAL'];
            $join_data['GUDS_CNS_NM'] = $val['GUDS_CNS_NM'];
            $join_data['GUDS_OPT_UPC_ID'] = $val['GUDS_OPT_UPC_ID'];
            $str = '';
            foreach (explode(';', $val['GUDS_OPT_VAL_MPNG']) as $k => $v) {
                if ($gudsOpt [$v]['OPT_CNS_NM'] and $gudsOpt [$v]['OPT_VAL_CNS_NM']) $str .= $gudsOpt [$v]['OPT_CNS_NM'] . ' ' . $gudsOpt [$v]['OPT_VAL_CNS_NM'] . ' ';
                else $str .= $gudsOpt [$v]['OPT_VAL_CNS_NM'];
            }
            $join_data['opt_val'] = $str;
            $join_data['warehouse'] = $house_all_list[$val['DELIVERY_WAREHOUSE']]['warehouse'];
            $join_data['total_inventory'] = $val['total_inventory'];
            $join_data['sale'] = $val['sale'];
            $join_data['occupy'] = $val['occupy'];
            $join_data['locking'] = $val['locking'];
            $join_data['weight'] = number_format($val['weight'], 4);
            $join_data['weighting_sum'] = number_format($val['weight'] * $val['total_inventory'], 2);
            $join_data['on_way'] = $val ['on_way'];
            $join_data['on_way_money'] = number_format($val['on_way_money'], 2);
            $expTableData[] = $join_data;
        }
        $this->exportExcel($expTitle, $expCellName, $expTableData);
    }

    /**
     * 占用订单查询
     */
    public function take_up()
    {
        $Operation_history = M('operation_history', 'tb_wms_');
        $where['sku_id'] = I("post.SKU_ID");
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
        $show['sku'] = I("post.SKU_ID");

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
        $return_arr = array('info' => $info, "status" => $status, 'data' => $operation_history, 'show' => $show);
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * order查询
     */
    public function search_up()
    {
        $Order = M('order', 'tb_op_');
        $where['tb_op_order.B5C_ORDER_NO'] = I("post.order_id");
        $ope_field = 'tb_op_order.B5C_ORDER_NO,tb_wms_operation_history.*';
        $order = $Order->field($ope_field)
            ->where($where)
            ->group('tb_wms_operation_history.order_id')
            ->having('count(tb_wms_operation_history.id)=1')
            ->join('left join tb_wms_operation_history on tb_op_order.B5C_ORDER_NO = tb_wms_operation_history.order_id')
            ->select();
        if ($order) {
            $info = '查询正常';
            $status = 'y';
        } else {
            $Orders = M('ord', 'tb_ms_');
            $wheres['tb_ms_ord.ORD_ID'] = I("post.order_id");
            $ope_field = 'tb_ms_ord.ORD_ID,tb_wms_operation_history.*';
            $order = $Orders->field($ope_field)
                ->where($wheres)
                ->group('tb_wms_operation_history.order_id')
                ->having('count(tb_wms_operation_history.id)=1')
                ->join('left join tb_wms_operation_history on tb_ms_ord.ORD_ID = tb_wms_operation_history.order_id')
                ->select();
            if ($order) {
                $info = '查询正常';
                $status = 'y';
            } else {
                $info = '查询无结果';
                $status = 'n';

            }
        }
        $return_arr = array('info' => $info, "status" => $status, 'data' => $order);
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 客商档案详情页
     */
    public function supplier()
    {
        $Supplier = M('supplier', 'tb_wms_');
        if (IS_POST) {
            $type = I("post.type");
            $post_data = $this->_param();
            if (!empty($post_data['add_data'])) {
                $where_name['suppli_name'] = $post_data['add_data']['suppli_name'];
                $supplier_name = $Supplier->where($where_name)->order('id asc')->select();
            }
            switch ($type) {
                case 'add':
                    if ($supplier_name[0]['id'] > 0 && $supplier_name[0]['suppli_name'] == $post_data['add_data']['suppli_name']) {
                        $return_arr = array('info' => '供应商名称重复', "status" => "n");
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    $return_arr = $this->supplier_add($post_data['add_data']);
                    break;
                case 'upd':


                    if ($supplier_name[0]['id'] > 0 && $supplier_name[0]['suppli_name'] == $post_data['add_data']['suppli_name'] && $supplier_name[0]['id'] != $post_data['add_data']['id']) {
                        $return_arr = array('info' => '供应商名称重复', "status" => "n");
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    $return_arr = $this->supplier_upd($post_data['add_data']);
                    break;
                case 'del':
                    $id = I("post.id");
                    $return_arr = $this->supplier_del($id);
                    break;
                case 'see':
                    if ('warehouse' == $post_data['search']) {

                        $Res = M('cmn_cd', 'tb_ms_');
                        $where['CD_VAL'] = $post_data['search_val'];
                        $house_key = $Res->where($where)->Field('CD,CD_VAL,ETc')->find();
                        $where[$post_data['search']] = $house_key['CD'];

                    } elseif ('suppli_name' == $post_data['search']) {
                        $where['suppli_name|abbreviation|en_name|en_ab'] = array('LIKE', "%" . $post_data['search_val'] . "%");
                    } else {
                        $where[$post_data['search']] = array('LIKE', "%" . $post_data['search_val'] . "%");
                    }
                    $this->assign('search_val', $post_data['search_val']);
                    $this->assign('search', $post_data['search']);
                    break;
            }
            if ('see' != $type) {
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        if (empty($post_data['search_val'])) {
            $where = '';
        }
        $supplier = $Supplier->where($where)->order('id asc')->select();
        if (empty($post_data['search'])) {
            $this->assign('search', 'suppli_name');
        }
        $this->assign('house_list', json_encode($this->get_show_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('house_all_list', json_encode($this->get_all_warehouse(), JSON_UNESCAPED_UNICODE));
        $this->assign('producer', json_encode($this->get_producer(), JSON_UNESCAPED_UNICODE));
        $this->assign('supplier', json_encode($supplier, JSON_UNESCAPED_UNICODE));
        $this->display();

    }


    private function supplier_add($get_data)
    {
        $Supplier = M('supplier', 'tb_wms_');
        foreach ($get_data as $key => $val) {
            $data[$key] = $val;
        }
        $data_pust['id'] = $add = $Supplier->data($data)->add();
        if ($add) {
            $return_arr = array('info' => '新增成功', "status" => "y", "data" => $data_pust);
        } else {
            $return_arr = array('info' => '新增失败', "status" => "n", "data" => $data);
        }
        return $return_arr;
    }

    private function supplier_del($id)
    {
        $Supplier = M('supplier', 'tb_wms_');
        $where_del['id'] = $id;
        $del = $Supplier->where($where_del)->delete();
        if ($del) {
            $return_arr = array('info' => '删除成功', "status" => "y");
        } else {
            $return_arr = array('info' => '删除失败', "status" => "n", "data" => $del);
        }
        return $return_arr;
    }

    private function supplier_upd($get_data)
    {
        $Supplier = M('supplier', 'tb_wms_');
//        $old = $Supplier->where('id = ' . $get_data['id'])->find();
        $upd = $Supplier->where('id = ' . $get_data['id'])->data($get_data)->save();
        if ($upd || $upd == 0) {
            $return_arr = array('info' => '修改成功', "status" => "y");
        } else {
            $return_arr = array('info' => '修改失败', "status" => "n", "data" => $get_data);
        }
        return $return_arr;
    }

    public function Curl_post($url, $data)
    {

        $ch = curl_init();
        $header=array(
            "Accept: application/json",
            "Content-Type: application/json"
        );
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $PostData = $data;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
        $temp = curl_exec($ch);
        return $temp;
        curl_close($ch);
    }
    /**
     * 发货出库 qoo10
     * true
     */
    public function deliver()
    {
        //
        if (1 != 1) {   //?????
            $data_start = null;
            if ($data_start) {
                $return_arr = array('info' => '创建成功', "status" => "y");
            } else {
                $return_arr = array('info' => '无可用库存', "status" => "n");
            }
            $return_arr = array('info' => '占用解锁异常', "status" => "n");
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $ordId = I('post.ordId');
        if (empty($ordId)) {
            redirect(U('Public/error'), 2, '无订单号');
            return false;
            exit;
        }
//        check orderid exist,property ? >.<
        $Bill = M('bill', 'tb_wms_');    //库存单据表
        $check['other_code'] = $ordId;
        $b_count = $Bill->where($check)->count();
        if ($Bill->where($check)->count() != 0) {
            $return_arr = array('info' => '单据已处理', "status" => "n", "code" => 'x01');
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $orderWhere['tb_op_order.ORDER_ID'] = $ordId;
        $order = M('op_order', 'tb_');
        $orderField = 'tb_op_order.ORDER_ID,tb_op_order.B5C_ORDER_NO, tb_op_order.BWC_ORDER_STATUS, tb_op_order.PLAT_CD,
                        tb_op_order.SHOP_ID, tb_op_order.PLAT_NAME, tb_op_order.USER_ID, tb_op_order.USER_NAME, tb_op_order.USER_EMAIL, tb_op_order.PAY_METHOD,
                        tb_op_order.PAY_TRANSACTION_ID, tb_op_order.PAY_CURRENCY, tb_op_order.PAY_SETTLE_PRICE, tb_op_order.PAY_VOUCHER_AMOUNT,
                        tb_op_order.PAY_SHIPING_PRICE, tb_op_order.PAY_PRICE, tb_op_order.PAY_TOTAL_PRICE, tb_op_order.ADDRESS_USER_NAME, tb_op_order.ADDRESS_USER_PHONE,
                        tb_op_order.ADDRESS_USER_COUNTRY, tb_op_order.ADDRESS_USER_ADDRESS1, tb_op_order.ADDRESS_USER_POST_CODE, tb_op_order.SHIPPING_MSG,
                        tb_op_order.SHIPPING_TYPE,tb_op_order.SHIPPING_DELIVERY_COMPANY,tb_op_order.SHIPPING_TRACKING_CODE,tb_op_order.PAY_SHIPING_PRICE';
        $detail = $order->field($orderField)->where($orderWhere)->find();
        $detail['ORD_STAT_CD_NAME'] = L($detail['BWC_ORDER_STATUS']);

        //订单商品list
        $gud = M('op_order_guds', 'tb_');
        $gudField = 'tb_op_order_guds.SKU_ID, tb_op_order_guds.ORDER_ITEM_ID, tb_op_order_guds.ITEM_NAME, tb_op_order_guds.SKU_MESSAGE,
                    tb_op_order_guds.ITEM_PRICE, tb_op_order_guds.ITEM_COUNT,tb_ms_guds_opt.GUDS_OPT_ID,
                    tb_op_order_guds.B5C_SKU_ID,tb_ms_guds.DELIVERY_WAREHOUSE';
        $gudWhere['tb_op_order_guds.ORDER_ID'] = $ordId;
        $gud_list = $gud->field($gudField)
            ->join('left join tb_ms_guds_opt on tb_op_order_guds.SKU_ID=tb_ms_guds_opt.GUDS_OPT_ID')
            ->join('left join tb_ms_guds on tb_op_order_guds.B5C_ITEM_ID=tb_ms_guds.GUDS_ID')
            ->where($gudWhere)->select();

        foreach ($gud_list as $k => $v) {
            $detail['gudAmount'] += $v['RMB_PRICE'] * $v['ORD_GUDS_QTY'];
        }
        $array['detail'] = $detail;
        $array['gudList'] = $gud_list;
        if (empty($gud_list) || empty($detail)) {
            if (empty($gud_list)) $msg = '商品信息获取异常';
            if (empty($detail)) $msg = '金额异常';
            $return_arr = array('info' => $msg, "status" => "n", 'data' => serialize($detail) . '_' . serialize($gud_list));
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit();
        }
        if (IS_POST) {
            $bill_id = I("post.bill_id");

            $data['other_code'] = $ordId;
            if (empty($detail['B5C_ORDER_NO'])) {
                $return_arr = array('info' => 'B5C订单号缺失', "status" => "n");
                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit();
            }
            $data['link_bill_id'] = $detail['B5C_ORDER_NO'];
            $data['channel'] = $detail['PLAT_NAME'];

            $data['warehouse_id'] = empty($array['gudList'][0]['DELIVERY_WAREHOUSE']) ? 'N000680100' : $array['gudList'][0]['DELIVERY_WAREHOUSE']; // 国内仓
            $data['company_id'] = 'N000980400'; // 载鸿
            $data['user_id'] = I("post.userId"); //
            $data['bill_type'] = 'N000950100';
            $data['bill_date'] = date('Y-m-d');
            $data['batch'] = null;
            $data['bill_state'] = 1;
//          outgo filter,check out stock

            $data['zd_user'] = boolval(session('m_loginname')) ? session('m_loginname') : 'admin';
            $data['zd_date'] = date('Y-m-d H:m:s');

            $data['bill_id'] = $this->get_bill_id($data['bill_type']);

            $b_id = $Bill->data($data)->add();
            if ($b_id) {
// add order
                $all_list = array();
                $Stream = M('stream', 'tb_wms_');

                foreach ($array['gudList'] as $key => $val) {
                    if (empty($val['B5C_SKU_ID'])) {
                        $where_del['id'] = $b_id;
                        $Bill->where($where_del)->delete();
                        unset($where_del);
                        $return_arr = array('info' => 'SKU缺失', "status" => "n");
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    } else {
                        $skuId = $unique['GSKU'] = $array_l['GSKU'] = $val['B5C_SKU_ID'];
                        /* if(empty($val['B5C_SKU_ID'])){
                             $return_arr = array('info' => 'SKU缺失', "status" => "n");
                             echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                             exit();
                         }*/
                        $array_l['line_number'] = $key;
                        $array_l['bill_id'] = $b_id;
                        $array_l['should_num'] = $val['ITEM_COUNT'];
                        $array_l['send_num'] = $val['ITEM_COUNT'];
                        $array_l['unit_price'] = $val['ITEM_PRICE'];
                        $array_l['no_unit_price'] = $val['ITEM_PRICE'];
                        $array_l['taxes'] = 0;
                        if (empty($detail['PAY_CURRENCY'])) {
                            $return_arr = array('info' => '币种缺失', "status" => "n");
                            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                            exit();
                        }
                        if (10 == strlen($detail['PAY_CURRENCY'])) {
                            $array_l['currency_id'] = $detail['PAY_CURRENCY'];
                        } else {
                            $Currency = M('cmn_cd', 'tb_ms_');
                            $where['CD_VAL'] = $detail['PAY_CURRENCY'];
                            $res = $Currency->where($where)->select();
                            $array_l['currency_id'] = $res[0]['CD'];
                        }
                        $arr_unique[] = $unique['GSKU'];
                        $all_list[] = $array_l;

                        $gudsId = substr($skuId, 0, -2);
//                        $ordId = $detail['B5C_ORDER_NO'];
                        $number = $val['ITEM_COUNT'];
                        if ($val['ITEM_COUNT'] > 0) {
                            $url = HOST_URL_API . '/guds_stock/export.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&number=' . $number . '&ordId=' . trim($detail['B5C_ORDER_NO']);
                            $get_start = json_decode(curl_request($url), 1);
                            if ($get_start['code'] != 2000) {
                                $where_del['id'] = $b_id;
                                $Bill->where($where_del)->delete();
                                if ($get_start['code'] == 40056031) {
                                    $return_arr = array('info' => '总库存不足:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
                                } else {
                                    $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => $get_start . $url);
                                }
                                $this->back = 0;
                                echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                                exit();
                            } else {
                                $back['gudsId'] = $gudsId;
                                $back['skuId'] = $skuId;
                                $back['number'] = $number;
                                $back_arr[] = $back;
                            }
                        }

                    }
                }

                if (count($arr_unique) != count(array_unique($arr_unique))) {
                    $where_del['id'] = $b_id;
                    $Bill->where($where_del)->delete();
                    unset($where_del);
                    $return_arr = array('info' => '单订单SKU编码重复', "status" => "n");
                    echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $data_start = $Stream->addAll($all_list);
                if ($data_start) {
                    $return_arr = array('info' => '创建成功', "status" => "y");
                } else {
                    $where_del['id'] = $b_id;
                    $Bill->where($where_del)->delete();
                    unset($where_del);
                    $return_arr = array('info' => '创建商品失败', "status" => "n", "data" => $array['gudList']);
                }
            } else {
                $return_arr = array('info' => '根订单创建失败', "status" => "n");
            }
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit();
        }
        $return_arr = array('info' => '参数获取失败', "status" => "n");
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    /*code:
    * GudsStore_NOT_ENOUGH(40056028,"商品库存不足"),
    * No_Change(40056029,"数据无变化"),
    * Guds_ON_OVERSALE(40056030,"商品超卖中"),
    * Guds_ON_OVEROccupy(40056031,"占用总数不够出库"),
    * Operation_Done(40056032,"该操作已经执行，无需重复"),
    * @return bool*/

    /**
     *发货出库回滚
     */
    public function deliver_back()
    {
        $ordId = I('post.ordId');
        $delthis = I('post.delthis');
        if (empty($ordId)) {
            redirect(U('Public/error'), 2, '无订单号');
            return false;
            exit;
        }
        $Bill = M('bill', 'tb_wms_');
        $check['other_code'] = $ordId;
        $b_id = $Bill->where($check)->field('id,other_code,link_bill_id')->select();

        if ($b_id[0]['id']) {
            $Stream = M('stream', 'tb_wms_');
            $s_where['bill_id'] = $b_id[0]['id'];
            try {
                $GSKU_count = $Stream->where($s_where)->field('id,GSKU,send_num')->select();
                foreach ($GSKU_count as $key) {
                    $gudsId = substr($key['GSKU'], 0, -2);
                    $number = $key['send_num'];
                    $url = HOST_URL_API . '/guds_stock/export.json?gudsId=' . $gudsId . '&skuId=' . $key['GSKU'] . '&number=-' . $number . '&ordId=' . $b_id[0]['other_code'];

                    $get_start = json_decode(curl_request($url), 1);
                    if ($get_start['code'] != 2000) {
                        $where_del['id'] = $b_id;
                        $Bill->where($where_del)->delete();
                        $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
                        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                        exit();
                    } else {
                        $back['gudsId'] = $gudsId;
                        $back['skuId'] = $key['GSKU'];
                        $back['number'] = $number;
                        $back_arr[] = $back;
                    }
                }
                if (1 == $delthis) {
                    $s_count = $Stream->where($s_where)->delete();
                    $b_count = $Bill->where($check)->delete();
                    if ($b_count) {
                        $return_arr = array('info' => '删除成功', "status" => "n");
                    }

                } else {
                    $Bill->is_show = 0;
                    $b_count = $Bill->where($check)->save();
                    $return_arr = array('info' => '回滚成功', "status" => "y");
                }

            } catch (Exception $e) {
                $return_arr = array('info' => '回滚异常', "status" => "n");
            }

        } else {
            $return_arr = array('info' => '无效订单号', "status" => "n");
        }


        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }


    /**
     *保税仓出库 费舍尔
     */
    public function deliver_warehouse()    //发货后要经过保税仓出库
    {
        $b5c_id = I('post.b5c_id');
        if (!$this->checkOccupy($b5c_id)) {    //查询是否有操作记录
            $return_arr = array('info' => '数据无占用', "code" => '5001', "status" => "n", 'data' => '');
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $Ord = M('ord', 'tb_ms_');
        $where['ORD_ID'] = $b5c_id;
        $ord = $Ord->where($where)->find();  //查找到这条订单

        $bill['link_bill_id'] = $b5c_id;   //订单号
        $bill['channel'] = $this->get_channel($ord['PLAT_FORM']);   //通过数据字典查询到订单平台

        $bill['user_id'] = I("post.userId"); //用户id
        $bill['bill_type'] = 'N000950100';   //设置订单号前缀
        $bill['bill_id'] = $this->get_bill_id($bill['bill_type']);  //拼接处b5c订单号
        $bill['warehouse_id'] = empty($ord['DELIVERY_WAREHOUSE']) ? 'N000680100' : $ord['DELIVERY_WAREHOUSE']; // 国内仓


        $bill['company_id'] = 'N000980400'; // 载鸿
        $bill['bill_date'] = date('Y-m-d');
        $bill['batch'] = null;
        $bill['bill_state'] = 1;

        $bill['zd_user'] = boolval(session('m_loginname')) ? session('m_loginname') : 'admin';
        $bill['zd_date'] = date('Y-m-d H:m:s');

//        $Bill = M('bill', 'tb_wms_');
//        $b_id = $Bill->data($bill)->add();
        $model = new Model();
        $model->startTrans();
        $b_id = $model->table('tb_wms_bill')->add($bill);

        if ($b_id) {
            try {
                $Ord_guds_opt = M('ord_guds_opt', 'tb_ms_');
                $where['ORD_ID'] = $b5c_id;
                $ord_guds_opt = $Ord_guds_opt->where($where)->select();

                if (empty($ord_guds_opt)) {
                    $model->rollback();
                    $return_arr = array('info' => '订单异常,ord_guds_opt检索不到', "code" => 400, "status" => "n", 'data' => '');
                    echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                    exit();
                } else {

                    foreach ($ord_guds_opt as $key => $val) {
                        $stream['GSKU'] = $val['GUDS_OPT_ID'];
                        $stream['no_unit_price'] = $stream['unit_price'] = $val['RMB_PRICE'];
                        $stream['send_num'] = $stream['should_num'] = intval($val['ORD_GUDS_QTY']);
                        $stream['taxes'] = 0;
                        $stream['currency_id'] = 'N000590300';//RMB
                        $stream['bill_id'] = $b_id;
                        $stream['line_number'] = $key;
                        $stream['batch'] = $val['wrapped_skuid']; //
                        $stream_all[] = $stream;
                    }

//                $Stream = M('stream', 'tb_wms_');
//                $Stream->startTrans();
//                $stream_data = $Stream->addAll($stream_all);
                    $stream_data = $model->table('tb_wms_stream')->addAll($stream_all);


                    if ($stream_data) {

                        foreach ($stream_all as $key) {
                            $gudsId = substr($key['GSKU'], 0, -2);
                            $number = $key['send_num'];
//                        不走占用直接扣减？
                            if ($ord['PLAT_FORM'] == 'N000831400') {
                                $url = HOST_URL_API . '/guds_stock/export.json?gudsId=' . $gudsId . '&skuId=' . $key['GSKU'] . '&number=' . $number . '&ordId=' . $b5c_id . '&channel=' . $ord['PLAT_FORM'] . '&channelSkuId=' . $key['batch'];
                                //echo $url;
                            } else {
                                $url = HOST_URL_API . '/guds_stock/export.json?gudsId=' . $gudsId . '&skuId=' . $key['GSKU'] . '&number=' . $number . '&ordId=' . $b5c_id;
                            }

                            trace($url, '$url');
                            $get_start = json_decode(curl_request($url), 1);
                            if ($get_start['code'] != 2000) {
                                $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
                            } else {
                                $back['gudsId'] = $gudsId;
                                $back['skuId'] = $key['GSKU'];
                                $back['number'] = $number;
                                $back_arr[] = $back;
                            }
                        }

                    }
                }

            } catch (Exception $e) {
                $error['error'] = $e;
                echo json_encode($error, JSON_UNESCAPED_UNICODE);
                exit();
            }

            if (empty($return_arr)) {
                $model->commit();
                $return_arr = array('info' => '创建成功', 'code' => '200', "status" => "y");
            } else {
                $model->rollback();
                $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
            }


        }

        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);

//        $array_l['bill_id'] = $b_id;


    }

    /**
     * 下载
     */
    public function download()
    {
        $name = I('get.name');
        import('ORG.Net.Http');
        $filename = APP_PATH . 'Tpl/Home/Stock/' . $name;
        Http::download($filename, $filename);
    }

    /**
     * 获取公司
     * @return mixed
     */
    private function get_company()
    {
        $Company = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '公司档案';
        return $Company->where($where)->getField('CD,CD_VAL,ETc');
    }

    /**
     * 获取仓库
     * @return mixed
     */
    /*    private function get_warehouse()
        {
            $Warehouse = M('warehouse', 'tb_wms_');
            $where['is_show'] = 1;
            if ($this->location == 1) {
                $where['location_switch'] = 1;
                return $Warehouse->where($where)->getField('id,company_id,warehouse');
            }
            return $Warehouse->where($where)->getField('id,company_id,warehouse');
        }   */

    private function get_show_warehouse()
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        $where['is_show'] = 1;
        $this->location == 1 ? $where['location_switch'] = 1 : ''; //货位
        return $Warehouse->where($where)->getField('CD,company_id,warehouse');
    }

    private function get_all_warehouse()
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        return $Warehouse->getField('CD,company_id,warehouse');
    }


    private function get_warehouse()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = 'DELIVERY_WAREHOUSE';
        return $Res->where($where)->getField('CD,CD_VAL,ETc');
    }

    private function warehouse_cd($w)
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_VAL'] = $w;
        $val = $Res->where($where)->field('CD')->find();
        return $val['CD'];
    }

//
    private function get_business_list()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = 'DELIVERY_WAREHOUSE';
        return $Res->where($where)->getField('CD,CD_VAL,ETc');
    }

//  供应商
    private function get_supplier_list()
    {
        $Warehouse = M('supplier', 'tb_wms_');
        return $Warehouse->getField('id,suppli_name,en_name');
    }

    /**
     * 收发类别
     * @return mixed
     */
    private function get_outgo($get_outgo = null)
    {
        $outgo = I('get.outgo');
        if ($get_outgo) {
            $outgo = $get_outgo;
        }
        switch ($outgo) {
            case 'storage':
                $where['CD_NM'] = '入库类型';
                break;
            case 'outgoing':
                $where['CD_NM'] = '出库类型';
                break;
            default:
                $where['CD_NM'] = array('in', '出库类型,入库类型');
        }
        $Res = M('cmn_cd', 'tb_ms_');
        return $Res->where($where)->getField('CD,CD_VAL,ETc');
    }

    private function get_outgoing()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '出库类型';
        return $Res->where($where)->getField('CD,CD_VAL');
    }

    private function get_out()
    {
        $Res = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '入库类型';
        return $Res->where($where)->getField('CD,CD_VAL');
    }

    /**
     * 出入库规则
     *
     */
    private function getwarehouse_rule($type = 'in_storage')
    {
        $rules = [
            'out_storage' => [
                2 => '默认规则(先进先出/效期敏感商品将以效期优先)',
                3 => '指定采购批次出库',
                5 => '虚拟出库'
            ],
            'in_storage' => [
                1 => '实际入库',
                0 => '虚拟入库(直接发给客户)',
            ]
        ];

        return $rules [$type];
    }

    //    人员
    private function get_use_extends()
    {
        $Role = M('role', 'bbm_');
        $Admin = M('admin', 'bbm_');
        return $Admin->getField('M_ID as id,M_NAME as nickname,M_ID code_id');
    }
    private function get_use()
    {
        $Role = M('role', 'bbm_');
        $Admin = M('admin', 'bbm_');
        return $Admin->where('ROLE_ID = ' . $Role->where('ROLE_ID = ' . ROLE_ID)->getField('ROLE_ID'))->getField('M_ID as id,M_NAME as nickname,M_ID code_id');
    }

    /**
     *货位获取
     */
    public function get_location($warehouse_id)
    {
        $Location = M('location', 'tb_wms_');
        $where['warehouse_id'] = $warehouse_id;
        return $Location->where($where)->getField('id,location_name,location_code');
    }


    /**
     *商品获取
     */
    public function get_goods($GSKU)
    {
        $Goods = M('goods', 'tb_wms_');
        $where['GSKU'] = $GSKU;
        return $Goods->where($where)->getField('id,goods_name,UP_SKU,bar_code,digit');
    }

//    商品
    /*    public function get_goods($GSKU)
        {
            $Goods = M('guds', 'tb_ms_');
            $GSKU_id = substr($GSKU,0,-2);
            $where['GUDS_ID'] = $GSKU_id ;
            return $Goods->where($where)->select();
        }*/


    /**
     * 币种获取
     */
    public function get_currency()
    {
        $Currency = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = '기준환율종류코드';
        return $Currency->where($where)->getField('CD,CD_VAL,ETc');
    }

    /**
     * 计量单位
     */
    private function get_metering()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = 'VALUATION_UNIT';
        return $Cmn_cd->where($where)->getField('CD,CD_VAL,ETc');
    }


    /**
     * 查询SPU
     */
    public function searchguds()
    {
        $GUDS_ID = I('post.GSKU');
        if (strlen($GUDS_ID) > 10) {
            $qr_code = $GUDS_ID;
            $Guds_opt = M('guds_opt', 'tb_ms_');
            if (!empty($qr_code)) {
                $where_qr['GUDS_OPT_UPC_ID'] = $GUDS_ID;
                $res = $Guds_opt->where($where_qr)->field('GUDS_OPT_ID')->find();
                $GUDS_ID = empty($res) ? '' : $res['GUDS_OPT_ID'];
            }
        }

        $model = D('Opt');
        $guds = $model->relation(true)->where('GUDS_OPT_ID = ' . $GUDS_ID)->select();

        if (empty($guds)) {
            $this->ajaxReturn(0, $guds, 0);
            exit();
        }
        $guds['Opt'] = $guds;
        foreach ($guds['Opt'] as $key => $opt) {
            $opt_val = explode(';', $opt['GUDS_OPT_VAL_MPNG']);
            foreach ($opt_val as $v) {
                $val_str = '';
                $o = explode(':', $v);
                $model = M('ms_opt', 'tb_');
                $opt_val_str = $model->join('left join tb_ms_opt_val on tb_ms_opt_val.OPT_ID = tb_ms_opt.OPT_ID')->where('tb_ms_opt.OPT_ID = ' . $o[0] . ' and tb_ms_opt_val.OPT_VAL_ID = ' . $o[1])->field('tb_ms_opt.OPT_CNS_NM,tb_ms_opt_val.OPT_VAL_CNS_NM,tb_ms_opt.OPT_ID')->find();
                if (empty($opt_val_str)) {
                    $val_str = L('标配');
                } elseif ($opt_val_str['OPT_ID'] == '8000') {
                    $val_str = L('标配');
                } elseif ($opt_val_str['OPT_ID'] != '8000') {
                    $val_str = $opt_val_str['OPT_CNS_NM'] . ':' . $opt_val_str['OPT_VAL_CNS_NM'] . ' ';
                }
                $guds['opt_val'][$key]['val'] .= $val_str;
                $guds['opt_val'][$key]['price'] = sprintf("%.2f", $opt['GUDS_OPT_BELOW_SALE_PRC'] / $rate);
                $guds['opt_val'][$key]['GUDS_OPT_ID'] = $opt['GUDS_OPT_ID'];
                $guds['opt_val'][$key]['GUDS_ID'] = $opt['GUDS_ID'];
                $guds['opt_val'][$key]['SLLR_ID'] = $opt['SLLR_ID'];
            }
        }


        $this->ajaxReturn(0, $guds, 1);
    }

    /**
     *获取订单
     */
    public function get_bill_id($e, $get_date = null)
    {
        $Bill = M('bill', 'tb_wms_');
        $date = date("Y-m-d");
        empty($get_date) ? '' : $date = $get_date;
        $where['bill_date'] = $date;

        $max_id = $Bill->where($where)->order('id')->limit(1)->count();
//CR+170122+0001
        $type = '';
        switch ($e) {
            case 'N000940100':
                $type = 'CGR';
                break;
            case 'N000940200':
                $type = 'THR';
                break;
            case 'N000940300':
                $type = 'QTR';
                break;
            case 'N000950100':
                $type = 'XSC';
                break;
            case 'N000950200':
                $type = 'BSC';
                break;
            case 'N000950300':
                $type = 'QTC';
                break;
            case 'N000940400':
                $type = 'PYR';
                break;
            case 'N000950400':
                $type = 'PKC';
                break;
        }
        $date = date("Ymd");
        empty($get_date) ? '' : $date = date("Ymd", strtotime($get_date));
        $date = substr($date, 2);
        $wrate_id = $max_id + 1;
        $w_len = strlen($wrate_id);
        $b_id = '';
        if ($w_len < 4) {
            for ($i = 0; $i < 4 - $w_len; $i++) {
                $b_id .= '0';
            }
        }
        return $type . $date . $b_id . $wrate_id;
    }

    private function get_default()
    {
        $user_id = $_SESSION['userId'];
        $Relation = M('relation', 'tb_wms_');
        $where['user_id'] = $user_id;
        return $Relation->where($where)->find();
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

    private function upd_cd($cd_v, $cd_nm)
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_VAL'] = $cd_v;
        if (!empty($cd_nm)) {
            $where['CD_NM'] = $cd_nm;
        }
        $res = $Cmn_cd->where($where)->Field('CD')->find();
        return $res['CD'];
    }

    private function upd_warehouse($w)
    {
        $Warehouse = M('warehouse', 'tb_wms_');
        return $Warehouse->where('is_show = 1 AND warehouse = \'' . $w . '\'')->getField('CD');
    }

    private function get_all_channel()
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD_NM'] = SALE_CHANNEL;
        return $Cmn_cd->where($where)->getField('CD,CD_VAL,ETc');
    }

    private function get_channel($e)
    {
        $Cmn_cd = M('cmn_cd', 'tb_ms_');
        $where['CD'] = $e;
        $res = $Cmn_cd->where($where)->Field('CD_VAL')->find();
        return $res['CD_VAL'];
    }

    /*private function upd_use($cd_v)
    {
        $Res = M('user', 'tb_wms_');
        $where['nickname'] = $cd_v;
        $res = $Res->where($where)->Field('id')->find();
        return $res['id'];
    }*/

    private function upd_use($cd_v)
    {
//        $cd_v = 'yangsu';
        $Role = M('role', 'bbm_');;
        $Admin = M('admin', 'bbm_');
        $user_arr = $Admin->where('ROLE_ID = ' . $Role->where('ROLE_ID = ' . ROLE_ID)->getField('ROLE_ID'))->where('M_NAME = \'' . $cd_v . '\'')->limit(1)->getField('M_NAME as nickname,M_ID as id,M_ID code_id');
        return $user_arr[$cd_v]['code_id'];
    }

    private function check_excel($b, $s, $stage = '')
    {
        $code_zh = [
            'company_id' => '公司',
            'channel' => '渠道',
            'warehouse_id' => '仓库',
            'bill_type' => '收发类别',
            'user_id' => '库管员',
            'bill_date' => '日期',
            'GSKU' => '商品编码',
            'GUDS_OPT_ID' => '条形码',
            'should_num' => '数量',
            'unit_price' => '单价',
            'taxes' => '税率',
            'currency_id' => '币种',
            'sum' => '金额',
        ];
        foreach ($b as $key => $val) {
            foreach ($val as $k => $v) {
                empty($v) ? $error[] = '第' . $val['row'] . '行的<' . $code_zh[$k] . '>异常' : '';
            }
            empty($error) ? '' : $error_data[] = $error;
            unset($error);
        }
        $Guds = M('guds', 'tb_ms_');
        $Warehouse = M('warehouse', 'tb_wms_');
        $Opt = D('Opt');
        foreach ($s as $key => $val) {
            foreach ($val as $n => $m) {
                foreach ($m as $k => $v) {
                    if ('GSKU' == $k || 'GUDS_OPT_ID' == $k) {
                        if (empty($m['GSKU']) && empty($m['GUDS_OPT_ID'])) {
                            $error[] = '商品>第' . $m['row'] . '行的<' . $code_zh[$k] . '>异常!';
                        }
                    } elseif (('taxes' == $k && $m['taxes'] == 0) || $m['should_num'] == 0) {

                    } elseif ($stage == 1 && ('unit_price' == $k || 'sum' == $k || 'currency_id' == $k || 'taxes' == $k)) {

                    } else {
                        empty($v) ? $error[] = '商品>第' . $m['row'] . '行的<' . $code_zh[$k] . '>异常' : '';
                    }
                    if ('GSKU' == $k) {
                        $check_SKU[$key][] = $v;
                    }
                }
//               check  sku and warehouse
                /*$where_house['MAIN_GUDS_ID'] = substr($m['GSKU'], 0, -2);
                $delivery_warehouse = $Guds->where($where_house)->getField('DELIVERY_WAREHOUSE');
                if ($delivery_warehouse != $b[$key]['warehouse_id']) {
                    $error[] = '商品>第' . $m['row'] . '行的SKU所属仓库与excel中仓库不一致！';
                }                */
                /* if($Warehouse->where('warehouse = '.$b[$key]['warehouse_id'])->count()){
                     $error[] = '商品>第' . $m['row'] . '行的仓库不存在！';
                 }*/
                $guds = $Opt->relation(true)->where('GUDS_OPT_ID = ' . $m['GSKU'])->select();
                if (empty($guds)) {
                    $error[] = '商品>第' . $m['row'] . '行的SKU属性不存在';
                }
            }
            empty($error) ? '' : $error_data[] = $error;
            unset($error);
        }
        foreach ($check_SKU as $key => $val) {
            $diff_data = array_diff_assoc($val, array_unique($val));
            if ($diff_data) {
                foreach ($diff_data as $k => $v) {
                    $error_data[][] = '商品SKU>' . $v . '<重复';
                }
            }
            break;
        }
        return $error_data;
    }

    private function check_del_bill($b, $outgo_state)
    {
        $res['state'] = 0;
        $res['sku'] = '';
        if ('-' == $outgo_state) {

            foreach ($b as $key => $val) {
                foreach ($val as $k => $v) {
                    empty($all[$v['GSKU']]) ? $all[$v['GSKU']] = $v['send_num'] : $all[$v['GSKU']] += $v['send_num'];
                }
            }
            $Standing = M('Standing', 'tb_wms_');
            $where['SKU_ID'] = array('in', array_keys($all));

            $standing_data = $Standing->where($where)->getField('SKU_ID,sale');

            foreach ($all as $key => $val) {
                if ($all[$key] - $standing_data[$key] > 0) {
                    $res['state'] = 1;
                    $res['sku'] .= '&nbsp&nbsp' . $key . '>数量不足,需要出库' . $all[$key] . '，实际余数为' . $standing_data[$key] . '<br>';
                }
            }

        } elseif ('delord' == $outgo_state) {
            $Bill = M('bill', 'tb_wms_');
            $bill_type = $Bill->where('id=' . $b)->getField('bill_type');

            if (!in_array($bill_type, array_keys($this->get_outgoing()))) {
                $Stream = M('stream', 'tb_wms_');
                $all_list = $Stream->where('bill_id=' . $b)->select();
                $sum = 0;
                foreach ($all_list as $key => $val) {
                    $all[$val['GSKU']] = $val['send_num'] + $sum;
                }
                $Standing = M('Standing', 'tb_wms_');
                $where['SKU_ID'] = array('in', array_keys($all));
                $standing_data = $Standing->where($where)->getField('SKU_ID,total_inventory');
                trace($all, '$all');
                trace($standing_data, '$standing_data');
                $Guds = M('guds', 'tb_ms_');
                foreach ($all as $key => $val) {
                    $where_over['GUDS_ID'] = substr($key, 0, -2);
//                    check OVER_YN
                    $guds_over = $Guds->where($where_over)->getField('OVER_YN');
                    trace($guds_over, '$guds_over');
                    if ($all[$key] - $standing_data[$key] > 0) {
                        if ($guds_over == 'N') {
                            $res['state'] = 1;
                        }
                        $res['sku'] .= '&nbsp' . $key;

                    }
                }
            }
        } else {
            $Bill = M('bill', 'tb_wms_');
            $bill_type = $Bill->where('id=' . $b)->getField('bill_type');

            if (!in_array($bill_type, array_keys($this->get_outgoing()))) {
                $Stream = M('stream', 'tb_wms_');
                $all_list = $Stream->where('bill_id=' . $b)->select();
                $sum = 0;
                foreach ($all_list as $key => $val) {
                    $all[$val['GSKU']] = $val['send_num'] + $sum;
                }
                $Standing = M('Standing', 'tb_wms_');
                $where['SKU_ID'] = array('in', array_keys($all));
                $standing_data = $Standing->where($where)->getField('SKU_ID,sale');
                foreach ($all as $key => $val) {
                    if ($all[$key] - $standing_data[$key] > 0) {
                        $res['state'] = 1;
                        $res['sku'] .= '&nbsp' . $key;
                    }
                }
            }
        }

        return $res;
    }

    /**
     *效验货位
     */
    public function check_postition($p, $w)
    {
        $Location_details = M('location_details', 'tb_wms_');
        $where['box_name'] = $p;
        $where['warehouse_id'] = $w;
        return $Location_details->where($where)->count();
    }

    /*   public function clear_bill_all()
       {
           $Bill = M('bill', 'tb_wms_');
           $where['id'] = array('lt', $_POST['id']);
           return $Bill->where($where)->delete();
       }

       public function clear_stream_all()
       {
           $Stream = M('stream', 'tb_wms_');
           $where['id'] = array('lt', $_POST['id']);
           return $Stream->where($where)->delete();
       }

       public function clear_standing_all()
       {
           $Standing = M('standing', 'tb_wms_');
           $where['SKU_ID'] = array('lt', $_POST['id']);
           return $Standing->where($where)->delete();
       }

       public function upd_standing()
       {
           $Standing = M('standing', 'tb_wms_');
           $where['SKU_ID'] = array('eq', $_POST['id']);
           $key = I('post.key');
           $data[$key] = I('post.val');
           return $Standing->where($where)->save($data);
       }

       public function clear_operation_history_all()
       {
           $Operation_history = M('operation_history', 'tb_wms_');
           $where['id'] = array('lt', $_POST['id']);
           return $Operation_history->where($where)->delete();
       }*/

    /**
     * 更新数据
     */
    public function upd_data()
    {
        $Standing = M('standing', 'tb_wms_');
        $Operation_history = M('operation_history', 'tb_wms_');
        $standing = $Standing->field('SKU_ID,occupy,total_inventory')->select();

        foreach ($standing as $key => $val) {

            $where['sku_id'] = $val['SKU_ID'];
            $Operation_history_sql = $Operation_history->where($where)->group('tb_wms_operation_history.order_id')->having('count(tb_wms_operation_history.id)=1')->select(false);
            $model = new Model();
            $count = $model->table($Operation_history_sql . ' a')->where("a.ope_type = 'N001010100'")->count();

            $counts = 0;
            foreach ($count as $k => $v) {
                $counts += $v['change_num'];
            }

            $Standing->occupy = $counts;
            $Standing->sale = $val['total_inventory'] - $counts;
            $where_sku['SKU_ID'] = $val['SKU_ID'];
            $Standing->where($where_sku)->save();
            print_r($counts);
            echo '<br/>';
        }

    }

    /*'N000550100'               => '待确认',
    'N000550200'               => '确认中',
    'N000550300'               => '待付款',
    'N000550301'               => '支付中',
    'N000550302'               => '信息异常',
    'N000550400'               => '待发货',
    'N000550500'               => '待收货',
    'N000550600'               => '已收货',
    'N000550700'               => '已付尾款',
    'N000550800'               => '交易成功',
    'N000550900'               => '交易关闭',*/

    /**
     * 同步异常订单
     */
    public function syn_occupy()
    {
        $Operation_history = M('operation_history', 'tb_wms_');
        $Ord = M('ord', 'tb_ms_');
        $model = new Model();
        $close_subQuery = $model->table('tb_wms_operation_history')->where("tb_wms_operation_history.ope_type = 'N001010200'")->select(false);
        $all_close = $model->table($close_subQuery . ' c,tb_ms_ord')->where("tb_ms_ord.ORD_ID = c.order_id AND tb_ms_ord.ORD_STAT_CD != 'N000550900'")->field('c.order_id,c.sku_id,c.change_num')->select();
        $yfh_close = $model->table($close_subQuery . ' c,tb_ms_ord')->where("tb_ms_ord.ORD_ID = c.order_id AND tb_ms_ord.ORD_STAT_CD != 'N000550900' AND (tb_ms_ord.ORD_STAT_CD = 'N000550500' OR  tb_ms_ord.ORD_STAT_CD = 'N000550800')")->field('c.id,c.order_id,c.sku_id,c.change_num')->select();
        echo '<pre>';
        print_r($yfh_close);
        $wfh_close = $model->table($close_subQuery . ' c,tb_ms_ord')->where("tb_ms_ord.ORD_ID = c.order_id AND tb_ms_ord.ORD_STAT_CD NOT IN ('N000550900','N000550500','N000550800')")->field('c.id,c.order_id,c.sku_id,c.change_num')->select();
        print_r($wfh_close);
        $err = 0;

        echo 'yfh';

        foreach ($yfh_close as $v) {
            $deldata = $model->table('tb_wms_operation_history')->where("order_id = '" . $v['order_id'] . "' AND sku_id = '" . $v['sku_id'] . "' AND ope_type = 'N001010200'")->delete();
            $new_deldata = $model->table('tb_wms_operation_history')->where("order_id = '" . $v['order_id'] . "' AND sku_id = '" . $v['sku_id'] . "' AND ope_type = 'N001010100'")->delete();
            print_r($deldata);
            print_r($new_deldata);
            if ($deldata == 0 || $new_deldata == 0) {
                $err = 1;
                goto errors;
                break;
            } else {
                $s_deldata = $model->table('tb_wms_operation_history')->where("order_id = '" . $v['order_id'] . "'")->select();
                echo '$s_deldata';
                var_dump($s_deldata);
                //            补占用
                $skuId = $v['sku_id'];
                $gudsId = substr($v['sku_id'], 0, -2);
                $changeNm = (int)$v['change_num'];
                $ordId = $v['order_id'];
                $url = HOST_URL_API . '/guds_stock/update_occupy.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&number=' . abs($changeNm) . '&ordId=' . $ordId;
                print_r($url);
                $results = json_decode(curl_request($url), 1);
                print_r($results);
                if ($results['code'] != 2000) {
                    $err = 1;
                    goto errors;
                    break;
                }
//            走出库

                $result = json_decode($this->deliver_warehouse_this($v['order_id'], 1), 1);
                echo 'chu';
                print_r($result);
                if ($result['code'] != 2000) {
                    $err = 1;
                    goto errors;
                    break;
                }
                $log = A('Log');
                $log->index($v['order_id'], $v['sku_id'], $result['msg']);
            }

        }
        echo 'wfh';
        if ($err != 1) {
            foreach ($wfh_close as $v) {
                //            删除错误关闭记录
                $deldata = $model->table('tb_wms_operation_history')->where("order_id = '" . $v['order_id'] . "' AND sku_id = '" . $v['sku_id'] . "' AND ope_type = 'N001010200'")->delete();
                //            删除占用记录
                $new_deldata = $model->table('tb_wms_operation_history')->where("order_id = '" . $v['order_id'] . "' AND sku_id = '" . $v['sku_id'] . "' AND ope_type = 'N001010100'")->delete();
                print_r($deldata);
                print_r($new_deldata);
                if ($deldata == 0 || $new_deldata == 0) {
                    $err = 1;
                    goto errors;
                    break;
                } else {
//            补占用
                    $skuId = $v['sku_id'];
                    $gudsId = substr($v['sku_id'], 0, -2);
                    $changeNm = (int)$v['change_num'];
                    $ordId = $v['order_id'];
                    $url = HOST_URL_API . '/guds_stock/update_occupy.json?gudsId=' . $gudsId . '&skuId=' . trim($skuId) . '&number=' . abs($changeNm) . '&ordId=' . $ordId;
                    print_r($url);
                    $result = json_decode(curl_request($url), 1);
                    print_r($result);
                    if ($result['code'] != 2000) {
                        $err = 1;
                        goto errors;
                        break;
                    }
                    $log = A('Log');
                    $log->index($v['GUDS_OPT_ID'], $skuId, $result['msg'] . $url);
                    $urls[] = $url;
                }
            }

        }
        errors:
        if ($err != 1) {
//            $model->commit();
        } else {
            echo 'roll1';
//            $model->rollback();
        }
        echo '</pre>';
    }

    /**
     *创建出入库订单
     */
    public function deliver_warehouse_this($b5c_id, $userId)
    {
        $b5c_id = $b5c_id;
        $Ord = M('ord', 'tb_ms_');
        $where['ORD_ID'] = $b5c_id;
        $ord = $Ord->where($where)->find();

        $bill['link_bill_id'] = $b5c_id;
        $bill['channel'] = $this->get_channel($ord['PLAT_FORM']);

        $bill['user_id'] = $userId; //
        $bill['bill_type'] = 'N000950100';
        $bill['bill_id'] = $this->get_bill_id($bill['bill_type']);
        $bill['warehouse_id'] = empty($ord['DELIVERY_WAREHOUSE']) ? 'N000680100' : $ord['DELIVERY_WAREHOUSE']; // 国内仓


        $bill['company_id'] = 'N000980400'; // 载鸿
        $bill['bill_date'] = date('Y-m-d');
        $bill['batch'] = null;
        $bill['bill_state'] = 1;

        $bill['zd_user'] = boolval(session('m_loginname')) ? session('m_loginname') : 'admin';
        $bill['zd_date'] = date('Y-m-d H:m:s');

//        $Bill = M('bill', 'tb_wms_');
//        $b_id = $Bill->data($bill)->add();
        $model = new Model();
        $model->startTrans();
        $b_id = $model->table('tb_wms_bill')->add($bill);

        if ($b_id) {

            $Ord_guds_opt = M('ord_guds_opt', 'tb_ms_');
            $where['ORD_ID'] = $b5c_id;
            $ord_guds_opt = $Ord_guds_opt->where($where)->select();

            if (empty($ord_guds_opt)) {
                $is_error = 1;
                $return_arr = array('info' => '订单异常', "code" => 400, "status" => "n", 'data' => '');
                goto echo_this;
            } else {

                foreach ($ord_guds_opt as $key => $val) {
                    $stream['GSKU'] = $val['GUDS_OPT_ID'];
                    $stream['no_unit_price'] = $stream['unit_price'] = $val['RMB_PRICE'];
                    $stream['send_num'] = $stream['should_num'] = intval($val['ORD_GUDS_QTY']);
                    $stream['taxes'] = 0;
                    $stream['currency_id'] = 'N000590300';//RMB
                    $stream['bill_id'] = $b_id;
                    $stream['line_number'] = $key;
                }


                $stream_all[] = $stream;

                $stream_data = $model->table('tb_wms_stream')->addAll($stream_all);

                if ($stream_data) {

                    foreach ($stream_all as $key) {
                        $gudsId = substr($key['GSKU'], 0, -2);
                        $number = $key['send_num'];
//                        不走占用直接扣减？
                        $url = HOST_URL_API . '/guds_stock/export.json?gudsId=' . $gudsId . '&skuId=' . $key['GSKU'] . '&number=' . $number . '&ordId=' . $b5c_id;

                        $get_start = json_decode(curl_request($url), 1);
                        if ($get_start['code'] != 2000) {
                            $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
                        } else {
                            $back['gudsId'] = $gudsId;
                            $back['skuId'] = $key['GSKU'];
                            $back['number'] = $number;
                            $back_arr[] = $back;
                        }
                    }

                }
            }

            echo_this:
            if ($is_error == 1) {
                $model->rollback();
                return json_encode($return_arr, JSON_UNESCAPED_UNICODE);
                exit;
            }
            if (empty($return_arr)) {
                $model->commit();
                $return_arr = array('info' => '创建成功', 'code' => '2000', "status" => "y");
            } else {
                $model->rollback();
                $return_arr = array('info' => '接口处理异常:' . $get_start['msg'], "code" => $get_start['code'], "status" => "n", 'data' => '');
            }


        }

        return json_encode($return_arr, JSON_UNESCAPED_UNICODE);

//        $array_l['bill_id'] = $b_id;


    }

    //check Occupy
    private function checkOccupy($o, $ope_type = null)
    {
        $Operation = M('operation_history', 'tb_wms_');    //出库操作记录
        $occupy = $Operation->where("ope_type = 'N001010100'  and order_id = '" . $o . "'")->count();
        if ($ope_type == 'onlyOccupy') {
            $release = 0;
        } else {
            $release = $Operation->where("ope_type != 'N001010100'  and order_id = '" . $o . "'")->count();
        }

        if ($occupy > 0 && $release == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新权值
     */
    private function update_weighting($sku = null, $all = null)
    {
        if ($all == 'goallben') {
            $Stand = M('standing', 'tb_wms_');
            $where_stand['total_inventory'] = array('neq', 0);
            $where_stand['channel'] = array('eq', 'N000830100');
            $stream_arr = $Stand->where($where_stand)
                ->field('SKU_ID')
                ->select();
            foreach ($stream_arr as $key => $val) {
                $this->update_weighting($val['SKU_ID']);
            }
            $update_state = true;
        } elseif (!empty($sku)) {
            $Model = new Model();
            $where['GSKU'] = $sku;
            $stream_sql = $Model->table('tb_wms_stream')->where($where)->select(false);
            $stage_type = implode("','", array_keys($this->get_out()));
            $stage_arr = $Model->table($stream_sql . ' s,tb_wms_bill')
                ->where('s.bill_id = tb_wms_bill.id AND tb_wms_bill.is_show = 1  AND tb_wms_bill.bill_type in (\'' . $stage_type . '\')')
                ->field('s.GSKU,s.send_num,s.unit_price,s.no_unit_price')
                ->select();
            $out_type = implode("','", array_keys($this->get_outgoing()));
            $out_arr = $Model->table($stream_sql . ' s,tb_wms_bill')
                ->where('s.bill_id = tb_wms_bill.id AND tb_wms_bill.is_show = 1 AND tb_wms_bill.bill_type in (\'' . $out_type . '\')')
                ->field('s.GSKU,s.send_num,s.unit_price,s.no_unit_price')
                ->select();
            $stage_all_num = $stage_all_sum = 0;
            foreach ($stage_arr as $key => $val) {
                $stage_all_sum += $val['send_num'] * $val['unit_price'];
                $stage_all_num += $val['send_num'];
            }
            $out_all_num = $out_all_sum = 0;
            foreach ($out_arr as $key => $val) {
                $out_all_sum += $val['send_num'] * $val['unit_price'];
                $out_all_num += $val['send_num'];
            }
            $data['weight'] = round($stage_all_sum / $stage_all_num, 4);
            $data['weighting_out'] = round($out_all_sum / $out_all_num, 4);

            $update_sate = $Model->table('tb_wms_power')->where('SKU_ID = ' . $sku)->save($data);
            if (!$update_sate) {
                $data['SKU_ID'] = $sku;
                $update_sate = $Model->table('tb_wms_power')->add($data);
            }
            $update_state = true;
        } else {
            $update_state = false;
        }
        return $update_state;
    }

    /**
     *获取所有仓库对应sku数
     */
    public function get_all_house_sku()
    {
        $Standing = M('standing', 'tb_wms_');
        $standing = $Standing->where('tb_wms_standing.total_inventory > 0 AND tb_wms_standing.channel = \'N000830100\'')
            ->join('left join tb_wms_power on tb_wms_power.SKU_ID = tb_wms_standing.SKU_ID')
            ->join('left join tb_ms_guds on tb_ms_guds.MAIN_GUDS_ID = tb_wms_standing.GUDS_ID')
            ->order('tb_wms_standing.SKU_ID,tb_wms_standing.channel desc')
            ->field('tb_wms_standing.SKU_ID,tb_wms_standing.GUDS_ID,tb_wms_standing.GUDS_ID,tb_wms_standing.total_inventory,tb_wms_power.weight,tb_ms_guds.DELIVERY_WAREHOUSE')
            ->select();
        $warehouse_count = array_count_values(array_column($standing, 'DELIVERY_WAREHOUSE'));
        foreach ($standing as $key => $val) {
            $warehouse[$val['DELIVERY_WAREHOUSE']]['sku_count'] = $warehouse_count[$val['DELIVERY_WAREHOUSE']];
            $warehouse[$val['DELIVERY_WAREHOUSE']]['total_inventory_all'] += $val['total_inventory'];
            $warehouse[$val['DELIVERY_WAREHOUSE']]['all_num'] += $val['total_inventory'] * $val['weight'];
        }
        return $warehouse;
    }

    /**
     *下载
     */
    public function down_existing($e)
    {
        $expTitle = "现存量查询";
        $expCellName = array(
            array('SKU_ID', 'SKU编码'),
            array('channel', '渠道'),
            array('GUDS_CNS_NM', '商品名称'),
            array('GUDS_OPT_UPC_ID', '条形码'),
            array('opt_val', '属性'),
            array('warehouse', '仓库'),
            array('total_inventory', '总库存数'),
            array('sale', '可售'),
            array('occupy', '占用'),
            array('locking', '锁定'),
            array('weight', '成本价'),
            array('weighting_sum', '库存成本'),
        );
        $all_channel = $this->get_all_channel();
        $house_all_list = $this->get_all_warehouse();
//        join exp excel
        foreach ($e as $key => $val) {
            $join_data['SKU_ID'] = $val['SKU_ID'];
            $join_data['channel'] = $all_channel[$val['channel']]['CD_VAL'];
            $join_data['GUDS_CNS_NM'] = $val['guds'][0]['Guds']['GUDS_CNS_NM'];
            $join_data['GUDS_OPT_UPC_ID'] = $val['guds'][0]['Guds']['GUDS_OPT_UPC_ID'];
            $join_data['opt_val'] = $val['guds']['opt_val'][0]['val'];
            $join_data['warehouse'] = $house_all_list[$val['warehouse_id']]['warehouse'];
            $join_data['total_inventory'] = $val['total_inventory'];
            $join_data['sale'] = $val['sale'];
            $join_data['occupy'] = $val['occupy'];
            $join_data['locking'] = $val['locking'];
            $join_data['weight'] = number_format($val['weight'], 4);
            $join_data['weighting_sum'] = number_format($val['weight'] * $val['total_inventory'], 2);
            $expTableData[] = $join_data;
        }
        $this->exportExcel($expTitle, $expCellName, $expTableData);
    }

    /**
     *excel处理
     */
    public function exportExcel($expTitle, $expCellName, $expTableData, $type = 0)
    {
        ini_set('memory_limit', '512M');
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        // 组装为二维数组
        if (count($expTableData) == count($expTableData, 1)) {
            $expTableData = array($expTableData);
        }
        if ($type == 0) {
            $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
            $objPHPExcel->getActiveSheet()->setTitle($expTitle);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(10);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(40);        // Miscellaneous glyphs, UTF-8
            $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);        // Miscellaneous glyphs, UTF-8
        }
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $fileName);
        // 设置标题
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
        }

        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        $column_index = 0;    // 控制行数据
        foreach ($this->procBigData($expTableData) as $k => $v) {
            $title_index = 0; // 控制标题对应相应的数据格
            foreach ($expCellName as $field_name => $title) {
                $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($cellName[$title_index] . ($column_index + 3), $v[$title[0]] ,PHPExcel_Cell_DataType::TYPE_STRING);
                $title_index++;
            }
            unset($v);
            $column_index++;
        }
        unset($expTableData);
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     *数据处理
     */
    public function procBigData($expTableData)
    {
        foreach ($expTableData as $key => $value) {
            yield $value;
        }
    }

    /**
     * 获取仓库对应SKU数
     */
    public function get_all_house_sku_old()
    {
        $Stream = M('stream', 'tb_wms_');
        $Bill = M('bill', 'tb_wms_');
        $where_in['tb_wms_bill.bill_type'] = array('in', array_keys($this->get_out()));
        $where_out['tb_wms_bill.bill_type'] = array('in', array_keys($this->get_outgoing()));
        $all_in = $Bill->where($where_in)->field('id')->select();
        $all_out = $Bill->where($where_out)->field('id')->select();
        $where_s_in['bill_id'] = array('in', array_column($all_in, 'id'));
        $data_in = $Stream->where($where_s_in)->field('GSKU,warehouse_id,sum(send_num) as all_sum')->group('GSKU,warehouse_id')->select();
        $where_s_out['bill_id'] = array('in', array_column($all_out, 'id'));
        $data_out = $Stream->where($where_s_out)->field('GSKU,warehouse_id,sum(send_num) as all_sum')->group('GSKU,warehouse_id')->select();
        foreach ($data_in as $key => $val) {
            $new_data_in[$val['warehouse_id'] . '-' . $val['GSKU']]['all_sum'] += empty($val['all_sum']) ? 0 : $val['all_sum'];
        }
        foreach ($data_out as $key => $val) {
            $new_data_out[$val['warehouse_id'] . '-' . $val['GSKU']]['all_sum'] -= empty($val['all_sum']) ? 0 : $val['all_sum'];
        }
        echo '<pre>';
//        print_r($new_data_in);
        print_r(array_merge_recursive($new_data_in, $new_data_out));
    }

    /**
     * 获取城市
     */
    public function getCity()
    {
        $p = I('provinces');
        $end = I('end');
        $province = $this->runCity($p);
        if (count($province) > 0) {
            $info = [
                'msg' => '查询成功',
                'keys' => array_column($province, 'ID')
            ];
            $this->ajaxReturn($this->packCountry($province, $end), $info, 1);
        } else {
            $info = [
                'msg' => '查询失败',
            ];
            $this->ajaxReturn($province, $info, 0);
        }


    }

    private function packCountry($c, $end = null)
    {
        foreach ($c as $v) {
            $c_n['value'] = $v['ID'];
            $c_n['label'] = $v['NAME'];
            if ('end' != $end) {
                $c_n['children'] = array(new stdClass());
            }
            $c_n_arr[] = $c_n;
        }
        return $c_n_arr;
    }


    // 获得市
    private function runCity($parent_id)
    {
        $model = M('_crm_site', 'tb_');
        $ret = $model->field('NAME, ID')->where('PARENT_ID = "' . $parent_id . '"')->select();
        return $ret;
    }

    /**
     * 测试
     */
    public function test()
    {
        print_r($_SERVER);
    }

    //check Occupy
    private function searchOccupy($o, $ope_type = null)
    {
        $Operation = M('operation_history', 'tb_wms_');
        $occupy = $Operation->where("ope_type = 'N001010100'")->select();


    }

    private function check_post($p, $c)
    {
        $state = 0;
        foreach ($c as $k => $v) {
            empty($p[$v]) ? '' : $state = 1;
        }
        return $state;
    }

    /**
     *权值获取
     */
    public static function get_power($sku)
    {
        $Power = M('power', 'tb_wms_');
        $where['SKU_ID'] = $sku;
        $res = $Power->cache(50)->where($where)->getField('weight');
        trace($res, '$res');
        if ($res) {
            return $res;
        } else {
            return 0;
        }
    }

    /**
     * 同步订单状态已更改，未发货
     */
    public function sync_order()
    {
        ini_set('max_execution_time', 18000);
        ini_set('request_terminate_timeout', 18000);
        $models = M('ms_ord', 'tb_');
        $date = empty(I('date')) ? '2017-06-01' : I('date');
        $date_end = empty(I('date_end')) ? '2017-06-19 23:59:59' : I('date_end');

        $where['tb_ms_ord.PAY_DTTM'] = array(array('gt', $date), array('lt', $date_end), 'and');
        $where['tb_ms_ord.PLAT_FORM'] = array(array('EXP', 'IS NULL'), array('exp', ' IN ("N000830100","N000830200","N000831300")'), 'or');
        $where['ORD_STAT_CD'] = array('eq', 'N000550500');

        $datas = $models->where($where)->field('ORD_ID')->select();
        $datas_key = array_column($datas, 'ORD_ID');
        $fun = empty(I('fun')) ? 'deliver_warehouse' : I('fun');
        $url = SMS2_URL . 'index.php?m=stock&a=' . $fun;

        foreach ($this->procBigData($datas_key) as $k => $v) {
            $post_data['b5c_id'] = $v;
            $states[$v] = curl_request($url, $post_data);
        }
        var_dump($states);
    }

    /**
     * 清理异常qoo10
     */
    public function clear_qoo10()
    {
        ini_set('max_execution_time', 18000);
        ini_set('request_terminate_timeout', 18000);
        $arrs = [];
        $Operation_history = M('operation_history', 'tb_wms_');
        $where['order_id'] = array('in', $arrs);
        $where['ope_type'] = array('eq', 'N001010100');
        $operation_history_arr = $Operation_history->where($where)->field('order_id,sku_id,change_num')->select();
        echo '<pre>';
        foreach ($this->procBigData($operation_history_arr) as $k => $v) {

            $skuId = $v['sku_id'];
            $gudsId = substr($skuId, 0, -2);
            $changeNm = (int)$v['change_num'];
            $outgo_state = '-';
            $ordId = $v['order_id'];
            $url = HOST_URL_API . '/guds_stock/update_occupy.json?gudsId=' . $gudsId . '&skuId=' . $skuId . '&number=' . $outgo_state . $changeNm . '&ordId=' . $ordId;
            $urls[] = $url;
            $result[] = json_decode(curl_request($url), 1);
        }
        print_r($result);
        print_r($urls);

    }

    /**
     * 清理异常第三方
     */
    public function clear_old_thr()
    {
        ini_set('max_execution_time', 18000);
        ini_set('request_terminate_timeout', 18000);

        $Operation_history = M('operation_history', 'tb_wms_');
        $where['ope_type'] = array('eq', 'N001010301');
        $operation_history_arr = $Operation_history->where($where)->field(' order_id,sku_id,ope_type,change_num')->select();

        echo '<pre>';
        foreach ($this->procBigData($operation_history_arr) as $k => $v) {
            $where['order_id'] = $v['order_id'];
            $where['sku_id'] = $v['sku_id'];
            $save['ope_type'] = 'N001010100';
            $save['change_num'] = abs($v['sku_id']);
            $Operation_history->where($where)->save($save);

            $skuId = $v['sku_id'];
            $gudsId = substr($skuId, 0, -2);
            $changeNm = (int)$v['change_num'];
            $ordId = $v['order_id'];
            $url = HOST_URL_API . '/guds_stock/update_occupy.json?gudsId=' . $gudsId . '&skuId=' . $skuId . '&number=' . $changeNm . '&ordId=' . $ordId;
            $urls[] = $url;
            $result[] = json_decode(curl_request($url), 1);
        }
        print_r($result);
        print_r($urls);
    }

    public function clean_occupy()
    {
        $arr = [['8000378401', '2', 'b5cb499051299380']];
        print_r($this->get_occupy_api($arr));
    }

    private function get_occupy_api($operation_history_arr)
    {
        $result = null;
        foreach ($this->procBigData($operation_history_arr) as $k => $v) {
            $skuId = $v[0];
            $gudsId = substr($skuId, 0, -2);
            $changeNm = (int)$v[1];
            $ordId = $v[2];
            $host_url_api = 'http://i.b5cai.com';
            $url = $host_url_api . '/guds_stock/update_occupy.json?gudsId=' . $gudsId . '&skuId=' . $skuId . '&number=-' . $changeNm . '&ordId=' . $ordId;
            $urls[] = $url;
            $result[] = json_decode(curl_request($url), 1);
        }
        return array($urls, $result);
    }

    public function show_batch()
    {
        $_params = $this->_param();
        $model = M('_wms_batch', 'tb_');
        is_null($_params ['channel']) or  $_params ['channel'] = 'N000830100';
        if (!empty($_params ['sku_id'])) {
            $condition ['SKU_ID'] = ['eq', $_params ['sku_id']];
            $condition ['channel'] = ['eq', $_params ['channel']];
            //$ret = $model->where($condition)->select();
            $where = ' where a.sku_id = "' . $_params ['sku_id'] . '" and ';
            $where .= 'a.channel = "' . $_params ['channel'].'" and ';
            $where .= '(a.available_for_sale_num != 0 or ';
            $where .= 'a.occupied != 0 or ';
            $where .= 'a.locking != 0) ';
            $model = new Model();
            $sql = "SELECT
	a.*, DATEDIFF(
		DATE_ADD(
			a.date_of_manufacture,
			INTERVAL c.SHELF_LIFE DAY
		),
		now()
	) AS pd
FROM
	tb_wms_batch a
LEFT JOIN tb_wms_standing b ON a.SKU_ID = b.SKU_ID
LEFT JOIN tb_ms_guds c ON b.GUDS_ID = c.MAIN_GUDS_ID
" .$where. "
ORDER BY
	(
		CASE a.is_it_sensitive
		WHEN 1 THEN
			pd
		END
	), batch_code ASC";
            $ret = $model->query($sql);
            $return_arr = array('info' => '成功', "status" => "y", 'data' => $ret);
        } else {
            $return_arr = array('info' => '失败', "status" => "n", 'data' => null);
        }
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }

    public function show_data()
    {
        $batch_id = $this->_param()['batch_id'];
        $batch = new TbWmsBatchModel();
        if ($ret = $batch->getBatchIdSkuId($batch_id)) {
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
            $info = '查询无结果asd';
            $status = 'n';
        }
        $return_arr = array('info' => $info, "status" => $status, 'data' => $operation_history, 'show' => $show);
        echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    }
}