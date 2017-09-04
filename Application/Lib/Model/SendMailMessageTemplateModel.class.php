<?php

/**
 *
1、初次审核邮件
（1）触发条件：
->提交供应商&B2B客户时，触发邮件
->判断条件：供应商&B2B客户注册地为大陆区域（后期的判断条件会调整，注意这里的扩展性）
->接收人，Legal@gshopper.com
->发送成功后，页面弹窗提示“提醒法务审核的邮件已经发送，请等待法务审核”

（2）邮件内容
->发件人：erpservice@gshopper.com
->收件人：Legal@gshopper.com
->标题：新供应商上海动景贸易有限公司信息审核提醒
->正文：
DearLegal：
供应商：XXXX
注册地：CN-中国，上海市，浦东新区
创建人：huali 
采购团队：AY-阿月
创建时间：2017-05-31 00:00:00
请点击（查看详情）登录ERP审核

（3）异常情况
->发送邮件失败（网络问题）

2、年度审核
（1）触发条件：当达到年度审核的时候，调用邮件服务，发送给Legal提醒审核抄送给上一次审核人，半年为一次年审

（2）邮件内容：
->发件人：erpservice@gshopper.com
->收件人：Legal@gshopper.com，抄送给上一次审核人，比如huali，邮件则为huali@gshopper.com
->标题：供应商上海动景贸易有限公司信息年审提醒

->正文：
DearLegal&huali：
供应商：XXXX
注册地：CN-中国，上海市，浦东新区
创建人：huali 
采购团队：AY-阿月
创建时间：2017-05-31 00:00:00
上一次审核人：huali
上一次审核时间：2017-06-02 00:00:00
请点击（查看详情）登录ERP审核

（3）异常情况
->发送邮件失败（网络问题） 
 * 
 */
class SendMailMessageTemplateModel extends BaseModel
{
    /**
     * 初审模板
     * 提醒法务审核
     */
    public function firstTrial()
    {
        $shtml = 'DearLegal：<br />';
        $shtml .= '供应商：%s<br />';
        $shtml .= '注册地：%s,%s,%s<br />';
        $shtml .= '创建人：%s<br />';
        $shtml .= '采购团队：%s<br />';
        $shtml .= '创建时间：%s<br />';
        $shtml .= '请点击<a href="%s">查看详情</a>登录ERP审核';
        
        return $shtml;
    }
    
    /**
     * 供应商年度审核模板
     * 
     */
    public function supplierYearExamine()
    {
        $shtml = 'DearLegal：<br />';
        $shtml .= '供应商：%s<br />';
        $shtml .= '注册地：%s,%s,%s<br />';
        $shtml .= '创建人：%s<br />';
        $shtml .= '采购团队：%s<br />';
        $shtml .= '创建时间：%s<br />';
        $shtml .= '上一次审核人：%s<br />';
        $shtml .= '上一次审核时间：%s<br />';
        $shtml .= '请点击<a href="%s">查看详情</a>登录ERP审核';
        
        return $shtml;
    }
    
    /**
     * 供应商年审标题
     * 
     */
    public function supplierYearTitle()
    {
        $shtml = '供应商';
        $shtml .= '%s';
        $shtml .= '信息年审提醒';
        
        return $shtml;
    }
    
    /**
     * 客户年度审核模板
     * 
     */
    public function customerYearExamine()
    {
        $shtml = 'DearLegal：<br />';
        $shtml .= '  客户：%s<br />';
        $shtml .= '注册地：%s,%s,%s<br />';
        $shtml .= '创建人：%s<br />';
        $shtml .= '销售团队：%s<br />';
        $shtml .= '创建时间：%s<br />';
        $shtml .= '上一次审核人：%s<br />';
        $shtml .= '上一次审核时间：%s<br />';
        $shtml .= '请点击<a href="%s">查看详情</a>登录ERP审核';
        
        return $shtml;
    }
    
    /**
     * 客户年审标题
     * 
     */
    public function customerYearTitle()
    {
        $shtml = '客户';
        $shtml .= '%s';
        $shtml .= '信息年审提醒';
        
        return $shtml;
    }
    
    /**
     * title
     * 
     */
    public function supplierTitle()
    {
        $shtml = '新供应商';
        $shtml .= '%s';
        $shtml .= '信息审核提醒';
        
        return $shtml;
    }
    
    /**
     * B2B客户管理审核
     * 
     *
     */
    public function customerFirstTrial()
    {
        $shtml = 'DearLegal：<br />';
        $shtml .= '  客户：%s<br />';
        $shtml .= '注册地：%s,%s,%s<br />';
        $shtml .= '创建人：%s<br />';
        $shtml .= '销售团队：%s<br />';
        $shtml .= '创建时间：%s<br />';
        $shtml .= '请点击<a href="%s">查看详情</a>登录ERP审核';
        
        return $shtml;
    }
    
    /**
     * 客户title
     * 
     */
    public function customerTitle()
    {
        $shtml = '新客户';
        $shtml .= '%s';
        $shtml .= '信息审核提醒';
        
        return $shtml;
    }
}