<?php
/**
 * 合同自动同步、审核
 * cli命令使用方法
 * 进入到项目根目录
 * eg: php index.php console/test
 * 参数解析
 * index.php 项目入口文件
 * console/test 控制器/action
 * 带参数命令
 * php index.php console/test/id/3/page/5
 * 注:参数是以$_GET的形式获得，并且参数名不得为a，否则会造成框架的action解析错误
 * 
 */
class ConsoleAction extends BaseAction
{
    
    // 间隔时间
    public function _initialize()
    {
        //parent::_initialize();
        ini_set('date.timezone', 'Asia/Shanghai');
        set_time_limit(0);
    }
    
    /**
     * 对于合同已经超过设置的审核日期的合同进行状态重置
     * 
     */
    public function run()
    {
        $ret = $this->getAllHaveAuditInfo();
        if (empty($ret)) return;
        $su = [];
        foreach ($ret as $key => $value) {
            $tz = new TimeZone($value['REV_TIME']);
            $tz->add('P6M');
            // 已达到审核时间
            if (time() > $tz->transformationDate('U')) {
                // 获得需要删除的审核信息
                $needDeleteAuditId [] = $value['ID'];
                $needBakData [] = $value;
                $temp ['SP_CHARTER_NO'] = $value ['SP_CHARTER_NO'];
                $temp ['DATA_MARKING']  = $value ['CRM_CON_TYPE'];
                $su [] = $temp;
                $temp = null;
            }
        }
        if ($needDeleteAuditId and $needBakData) {
            // 删除审核信息
            $map ['ID'] = ['in', $needDeleteAuditId];
            $model = M('_ms_forensic_audit', 'tb_');
            $model->where($map)->delete();
            // 审核信息备份
            $model = M('_ms_forensic_audit_history', 'tb_');
            $model->addAll($needBakData);
            $this->updateTbcrmSupplierAndCustomer($su);
            $sm = new BaseModel();
            // 邮件提醒
            foreach ($needBakData as $k => $v) {
                $m = M('_crm_sp_supplier', 'tb_');
                $tmap ['DATA_MARKING'] = ['eq', $v ['CRM_CON_TYPE']];
                $tmap ['SP_CHARTER_NO'] = ['eq', $v ['SP_CHARTER_NO']];
                $rs = $m->where($tmap)->find();
                if ($v ['CRM_CON_TYPE'] == 0) {
                    $sm->supplierYearSendMail($rs, $v);
                } else {
                    $sm->customerYearSendMail($rs, $v);
                }
            }
        }
        
        return;
    }
    
    /**
     * 更新供应商或者客户表
     * 
     */
    public function updateTbcrmSupplierAndCustomer($data)
    {
        $model = M('_crm_sp_supplier', 'tb_');
        $d ['AUDIT_STATE'] = 1;
        $d ['RISK_RATING'] = null;
        foreach ($data as $key => $value) {
            $model->where($value)->save($d);
        }
    }
    
    /**
     * 获取所有审核信息
     * 
     */
    public function getAllHaveAuditInfo()
    {
        $model = M('_ms_forensic_audit', 'tb_');
        $ret = $model->select();
        return $ret;
    }
}