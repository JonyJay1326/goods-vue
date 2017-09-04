

CREATE TABLE `tb_ms_coupons` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '优惠券ID',
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `plat_cd` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '适用平台',
  `valid_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '有效期类型[1绝对时间2相对时间]',
  `valid_start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效期开始',
  `valid_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '有效期结束',
  `valid_receive_after_days` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '有效期领取后天数',
  `circulation` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总发行量',
  `is_limit_circulation` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否限制发行量[1yes0no]',
  `circulation_daily` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单日发行量(每天最多发行)',
  `receive_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '领取限制类型(1总共可领取2每天可领取)',
  `receive_num_each` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '每人总共可领取',
  `receive_interval_days` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '领取每几天',
  `receive_num_day` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '每几天可领取',
  `usage_can_superposition` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '使用限制与其他优惠(1可叠加0不可叠加)',
  `using_conditions_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '使用条件类型(1无条件2满金额)',
  `using_conditions_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '使用条件满金额',
  `favorable_results_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '优惠结果类型[1减2打折3免运费4免税费5送赠品6返券]',
  `favorable_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠减钱',
  `favorable_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠打折',
  `favorable_gift` bigint(12) unsigned NOT NULL DEFAULT '0' COMMENT '优惠赠品',
  `favorable_coupon` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '优惠返券',
  `use_scope_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '使用范围[1全网优惠券2类目优惠券3品牌优惠券4商品优惠券]',
  `use_scope_cat` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '范围类目',
  `use_scope_brand` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '范围品牌',
  `use_scope_product` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '范围商品',
  `delivering_way_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '发放方式类型[1系统发放2手工发放3用户领取]',
  `delivering_way_sys` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '系统发放预设(1新注册成功用户2首次下单用户3首次确认收货用户)',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态(0关闭（默认）1启用)',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `create_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `remain_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余量',
  `already_receive_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已领取',
  `available_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可使用',
  `already_use_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用',
  `expired_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已过期',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20170328
ALTER TABLE `tb_ms_coupons` ADD UNIQUE `uni_code` ( `code` ) ;
ALTER TABLE `tb_ms_coupons` ADD INDEX `idx_create` ( `create_time` ) ;

-- 20170329
ALTER TABLE `tb_ms_cust` ADD `THIRD_UID` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT '第三方用户编号',
ADD `MERCHANT_ID` VARCHAR( 100 ) NOT NULL DEFAULT '' COMMENT '商人编号' ;


