

ALTER TABLE `tb_ms_cust` ADD `parent_cust_id` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '所属客户 ',
ADD `parent_store_name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '所属店铺',
ADD `parent_plat_cd` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '所属平台',
ADD `is_recieve` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '接收优惠信息',
ADD `recieve_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '接收渠道(1短信2邮箱)' ;

ALTER TABLE `tb_ms_cust` CHANGE `parent_store_name` `parent_store_id` INT( 11 ) NOT NULL DEFAULT '0' COMMENT '所属店铺id';
ALTER TABLE `tb_ms_cust` CHANGE `is_recieve` `is_recieve` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '接收优惠信息(0否1是)';
ALTER TABLE `tb_ms_cust` CHANGE `recieve_type` `recieve_types` VARCHAR( 30 ) NOT NULL DEFAULT '' COMMENT '接收渠道集合(1短信2邮箱)';


-- 20170316
ALTER TABLE `tb_ms_cust` ADD INDEX `idx_statcd` ( `CUST_STAT_CD` ) ;
ALTER TABLE `tb_ms_cust` CHANGE `recieve_types` `recieve_types` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '接收渠道集合(1短信2邮箱)(逗号分割)' ;


