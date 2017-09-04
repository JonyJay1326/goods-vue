
CREATE TABLE `tb_ms_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plat_cd` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '平台',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '内容正文',
  `pic` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图片',
  `user_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送客户数量',
  `send_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态(0已删除1草稿2进行中3已发送)',
  `send_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `success_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功数量',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  `update_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_all_send` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送所有用户(0No1Yes)',
  PRIMARY KEY (`id`),
  KEY `plat_cd` (`plat_cd`),
  KEY `send_status` (`send_status`),
  KEY `is_all_send` (`is_all_send`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息列表';

CREATE TABLE `tb_ms_message_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `msg_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息ID',
  `CUST_ID` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `has_send` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送(0No1Yes)',
  PRIMARY KEY (`id`),
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息发送记录';


-- 20170531

CREATE TABLE `tb_ms_message_cron_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `msg_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息ID',
  `send_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态(0已删除1草稿2进行中3已发送)',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息脚本记录';

ALTER TABLE `tb_ms_message_cron_logs` DROP INDEX `msg_id` ,
ADD UNIQUE `msg_id` ( `msg_id` )  ;


-- 20170602
ALTER TABLE `tb_ms_messages` ADD `target_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '目标URL' ;


-- 20170622
ALTER TABLE `tb_ms_message_send` ADD INDEX `cust_id` ( `CUST_ID` ) ;


-- 20170626
CREATE TABLE `tb_ms_message_push_res` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `msg_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '消息ID',
  `CUST_ID` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '用户ID',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `failed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '失败(0No1Yes)',
  PRIMARY KEY (`id`),
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='消息发送结果数据';



