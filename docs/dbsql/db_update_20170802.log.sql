
CREATE TABLE `tb_ms_merchant_applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `tel` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '电话',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `apply_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态(0申请1已处理2其他)',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='申请资质记录表';
ALTER TABLE `tb_ms_merchant_applications` ADD UNIQUE `unq_name` ( `name` ) ;
