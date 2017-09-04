<?php

/**
 * User: yuanshixiao
 * Date: 2017/6/1
 * Time: 16:12
 */
include_once './ThinkPHP/Extend/Vendor/PHPMailer/class.phpmailer.php';

class SMSEmail extends PHPMailer
{
    public function __construct($exceptions = false)
    {
        parent::__construct($exceptions);
        $this->isSMTP();
        $this->SMTPAuth     = true;
        $this->Host         = C('email_host');
        $this->Port         = C('email_port');
        $this->Username     = C('email_address');
        $this->Password     = C('email_password');
        $this->From         = C('email_address');
        $this->FromName     = C('email_user');
        $this->SMTPSecure   = 'ssl';
        $this->CharSet      = 'UTF-8';
    }

    public function sendEmail($address,$title,$content,$cc = null) {
        try {
            $this->isHTML(true);
            if(is_array($address)) {
                foreach ($address as $v) {
                    $this->addAddress($v);
                }
            }else {
                $this->addAddress($address);
            }
            if($cc){
                foreach ($cc as $v){
                    $this->addCC($v);
                }
            }
            $this->Subject = $title;
            $this->Body = $content;
            $this->Send();
            return true;
        } catch (phpmailerException $e) {
            return false;
        }
    }
}