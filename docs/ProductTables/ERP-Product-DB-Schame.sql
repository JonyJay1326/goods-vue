-- 商品数据库表结构及关系
-- GUDS_ID 与
-- 
--
--
-- 商品主表，商品主要基本信息表
--
CREATE TABLE `tb_ms_guds` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '판매자아이디 | 销售者ID，实际上是商家id（可能是品牌id）',
  `MAIN_GUDS_ID` varchar(20) DEFAULT NULL COMMENT '主 SPU ID，当多语言的时候会有多个商品，用该属性区分并关联库存等',
  `GUDS_CODE` varchar(10) DEFAULT NULL COMMENT '商品SPU 自编码',
  `GUDS_ID` varchar(20) NOT NULL COMMENT '상품아이디 | 商品SPU ID属性',
  `CAT_CD` varchar(10) DEFAULT NULL COMMENT '카테고리코드 | 所属分类的对应编码',
  `GUDS_NM` varchar(1000) DEFAULT NULL COMMENT '상품명 | 商品名称，主标题',
  `GUDS_CNS_NM` varchar(1000) DEFAULT NULL COMMENT '상품중문명 | 商品中文标题',
  `GUDS_VICE_NM` varchar(1000) DEFAULT NULL COMMENT '商品副标题(韩)',
  `GUDS_VICE_CNS_NM` varchar(1000) DEFAULT NULL COMMENT '商品副标题(中)',
  `BRND_NM` varchar(100) DEFAULT NULL COMMENT '브랜드명 | 品牌名称',
  `GUDS_PVDR_NM` varchar(100) DEFAULT NULL COMMENT '상품공급자명 | 商品供应者名',
  `GUDS_ORGP_CD` varchar(10) DEFAULT NULL COMMENT '상품원산지코드 | 商品原产地编码',
  `GUDS_REG_STAT_CD` varchar(10) DEFAULT NULL COMMENT '상품등록상태코드 | 商品登录状态编码',
  `GUDS_SALE_STAT_CD` varchar(10) DEFAULT NULL COMMENT '상품판매상태코드 | 商品销售状态编码',
  `GUDS_AUTH_YN` varchar(50) DEFAULT NULL COMMENT '상품수권서여부 | 商品授权书有无',
  `GUDS_VAT_RFND_YN` varchar(50) DEFAULT NULL COMMENT '상품VAT환급여부 | 商品VAT是否返还(VAT=Value Added Tax 增值税，附加税，消费税)，即是否退税',
  `GUDS_DLVC_DESN_VAL_1` decimal(10,2) DEFAULT NULL COMMENT '상품배송비결정값 (부피:가로) | 商品运费结算价 (体积:长)',
  `GUDS_DLVC_DESN_VAL_2` decimal(10,2) DEFAULT NULL COMMENT '상품배송비결정값 (부피:세로) | 商品运费结算价 (体积:宽)',
  `GUDS_DLVC_DESN_VAL_3` decimal(10,2) DEFAULT NULL COMMENT '상품배송비결정값 (부피:높이) | 商品运费结算价(体积:高度)',
  `GUDS_DLVC_DESN_VAL_4` decimal(10,2) DEFAULT NULL COMMENT '상품배송비결정값 (무게)     | 商品运费结算价 (重量)',
  `GUDS_DLVC_DESN_VAL_5` decimal(10,2) DEFAULT NULL COMMENT '상품배송비결정값 (박스당상품수)|商品运费结算价 (箱子优等商品数量)',
  `GUDS_OPT_YN` char(1) NOT NULL COMMENT '상품옵션여부 | 商品选项是否启用，----指商品SKU是否启用？',
  `GUDS_XPSR_CNT_USE_YN` char(1) DEFAULT NULL COMMENT '상품노출수사용여부 | 商品露出数是否使用 ----是否显示库存量？',
  `GUDS_CLCK_CNT` decimal(10,2) DEFAULT NULL COMMENT '상품클릭수 | 商品点击数',
  `GUDS_MIN_CLCK_CNT` decimal(10,2) DEFAULT NULL COMMENT '상품최소클릭수 | 商品最少点击数',
  `GUDS_ADD_CLCK_CNT` decimal(2,0) DEFAULT NULL COMMENT '상품추가클릭수 | 商品追加点点击数',
  `GUDS_SALE_QTY` decimal(10,2) DEFAULT NULL COMMENT '상품판매수량 | 商品销售数量',
  `GUDS_MIN_SALE_QTY` decimal(10,2) DEFAULT NULL COMMENT '상품최소판매수량 | 商品最小销售数量',
  `GUDS_ADD_SALE_QTY` decimal(2,0) DEFAULT NULL COMMENT '상품추가판매수량 | 商品追加销售数量',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统登录者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일시 | 系统登录日期',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일시 | 系统变更日期',
  `GUDS_FLAG` varchar(10) DEFAULT NULL COMMENT 'Goods Flag | 商品标记码，如：HOT 热销标记，New 新品等',
  `GUDS_SUB_FLAG` varchar(10) DEFAULT NULL COMMENT 'Goods Sub Flag | 商品自标记或者副标记',
  `GUDS_DLPY_YN` char(1) DEFAULT NULL COMMENT 'Goods Delivery Proxy YN | 代理发货是否启用， ----是否一件代发？',
  `DELIVERY_WAREHOUSE` varchar(15) DEFAULT NULL COMMENT '发货地',
  `VALUATION_UNIT` varchar(15) DEFAULT 'N000690101' COMMENT '计价单位',
  `MIN_BUY_NUM` int(11) DEFAULT NULL COMMENT '起订量',
  `MAX_BUY_NUM` int(11) DEFAULT NULL COMMENT '最大订购量',
  `BELONG_SEND_CAT` varchar(20) DEFAULT NULL COMMENT '归属类别',
  `LOGISTICS_TYPE` varchar(10) DEFAULT NULL COMMENT '物流类别',
  `LOGUSTICS_FEE` decimal(12,4) DEFAULT NULL COMMENT '物流费',
  `IS_NOPOSTAGE` varchar(2) DEFAULT 'N' COMMENT '是否包邮(Y:包邮  N:不包邮)',
  `LANGUAGE` varchar(10) DEFAULT NULL COMMENT '语言类型',
  `OVER_YN` varchar(1) DEFAULT 'N' COMMENT '是否允许超卖 Y 允许 N不允许',
  `PROCUREMENT_SOURCE` varchar(255) DEFAULT 'N001030100' COMMENT '采购来源(用分号给开)',
  `TEMP1` varchar(1000) DEFAULT NULL COMMENT '临时字段 存储 GUDS_NNM',
  `TEMP2` varchar(1000) DEFAULT NULL COMMENT '临时字段 存储 GUDS_VICE_NM',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'BIͬ???ֶΣ?ϵͳ?Զ?ά????ֵ',
  `SHELF_LIFE` int(11) DEFAULT '0' COMMENT '商品保质期',
  `STD_XCHR_KIND_CD` varchar(10) DEFAULT 'N000590200' COMMENT '商品报价类型,默认韩元',
  `SALE_CHANNEL` varchar(255) DEFAULT NULL COMMENT '上架渠道（，隔开）',
  `RETURN_RATE` varchar(10) DEFAULT NULL COMMENT '返税比率',
  `ADDED_TAX` varchar(10) DEFAULT NULL COMMENT '增值税',
  `OVERSEAS_RATE` varchar(10) DEFAULT NULL COMMENT '跨境综合税率',
  `PUSH_STATUS` varchar(10) DEFAULT 'N000890100' COMMENT '推送状态',
  `PUSH_TIME` datetime DEFAULT NULL COMMENT '推送时间',
  `GUDS_STAT_CD` varchar(20) DEFAULT 'N001180100' COMMENT '商品业务类型',
  PRIMARY KEY (`SLLR_ID`,`GUDS_ID`),
  UNIQUE KEY `ix_ms_guds_01` (`GUDS_ID`),
  KEY `mian_guds_id_index` (`MAIN_GUDS_ID`) USING BTREE,
  KEY `index_guds_sales_stat_cd` (`GUDS_SALE_STAT_CD`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 商品简介，商品简介信息，区分不同语言的； 
-- 所以与商品主表是一对多的关系，一个商品主信息 对应多个 描述信息。
CREATE TABLE `tb_ms_guds_describe` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '销售者ID，商家id',
  `MAIN_GUDS_ID` varchar(20) DEFAULT NULL COMMENT '主 SPU ID，区分语言的SPU ID，每一种语言都有单独的，与库存表有关联 ',
  `GUDS_ID` varchar(20) NOT NULL COMMENT '商品id',
  `GUDS_DESCRIBE` text COMMENT '商品详细描述，JSON格式的文本',
  `LANGUAGE` varchar(10) DEFAULT NULL COMMENT '语言类型',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'BI',
  PRIMARY KEY (`SLLR_ID`,`GUDS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 商品详情信息，商品详情页的商品详细信息，主要是图片列表，一般带有HRML标签
-- 一个主商品，对应多个商品详情，每一种语言一个记录，一对多关系。
CREATE TABLE `tb_ms_guds_dtl` (
  `SLLR_ID` varchar(50) NOT NULL COMMENT '판매자아이디 | 销售者ID，实际是商家ID',
  `MAIN_GUDS_ID` varchar(20) DEFAULT NULL COMMENT '主SPU ID, 区分语言的SPU ID，每一种语言是一个单独的商品信息',
  `GUDS_ID` varchar(20) NOT NULL COMMENT '상품아이디 | 商品ID，关联商品主表',
  `GUDS_DTL_CONT` text COMMENT '상품상세내용 | 商品详情内容，富文本信息 会包含 HTML标记和图片，详情内容主要以图片方式展示',
  `GUDS_DTL_CONT_WEB` text COMMENT '商品的详细内容HTML（数据内容上多数情况下与 GUDS_DTL_CONT相同或者相似）',
  `GUDS_DTL_CDN_ADDR` varchar(1000) DEFAULT NULL COMMENT '상품상세CDN주소 | 商品详细内容分发网络地址（具体不知道做什么用）',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 商品的系统录入者ID(发布人ID)',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일자 | 系统录入时间',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID（变更人ID）',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일자 | 系统变更时间(变更时间)',
  `LANGUAGE` varchar(10) NOT NULL DEFAULT '' COMMENT '语言类型',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI 统计',
  PRIMARY KEY (`SLLR_ID`,`GUDS_ID`,`LANGUAGE`),
  KEY `INDEX_DTL_GUDS_ID` (`GUDS_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 商品图片表，商品详情的图片列表，主图、幻灯图等
-- 一对多关系，每个商品有一组图片；并且每一种语言 有单独的一组图片。
CREATE TABLE `tb_ms_guds_img` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '판매자아이디 | 销售者ID，实际是商家ID',
  `MAIN_GUDS_ID` varchar(20) DEFAULT NULL COMMENT '主 SPU ID',
  `GUDS_ID` varchar(20) NOT NULL COMMENT '상품아이디 | 商品ID，关联商品主表',
  `GUDS_IMG_CD` varchar(10) NOT NULL COMMENT '상품이미지코드 | 商品图片code，N000080100=代表图片；N000080200=登录图片',
  `GUDS_IMG_ORGT_FILE_NM` varchar(100) DEFAULT NULL COMMENT '상품이미지원본파일명 | 商品图片原版本文件名',
  `GUDS_IMG_SYS_FILE_NM` varchar(100) DEFAULT NULL COMMENT '상품이미지시스템파일명 | 商品图片系统生成的新文件名',
  `GUDS_IMG_CDN_ADDR` varchar(1000) DEFAULT NULL COMMENT '상품이미지CDN주소 | 商品图片CDN地址（就是图片访问地址）',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统登录者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일자 | 系统登录日期',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일자 | 系统变更日期',
  `LANGUAGE` varchar(10) NOT NULL DEFAULT '' COMMENT '语言类型',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`,`GUDS_IMG_CD`,`GUDS_ID`,`LANGUAGE`),
  KEY `GUDS_IMG_CD` (`GUDS_IMG_CD`) USING BTREE,
  KEY `INDEX_GUDS_ID` (`GUDS_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 商品SKU表（选项表），
-- 与商品主表是一对多关系，一个商品有多个 SKU。
-- 与属性值表是一对多关系，一个SKU有多个属性值。
-- 与属性表是一对多关系，一个SKU有属性。
CREATE TABLE `tb_ms_guds_opt` (
  `SLLR_ID` varchar(50) NOT NULL COMMENT '판매자이이디 | 销售者ID，实际是商家ID',
  `GUDS_ID` varchar(20) NOT NULL COMMENT '상품아이디 | 商品ID，关联商品主表',
  `GUDS_OPT_ID` varchar(20) NOT NULL COMMENT '상품옵션아이디 | 商品SKU ID',
  `GUDS_OPT_CODE` varchar(14) DEFAULT NULL COMMENT '商品SKU自编码（同仓不能相同 不同仓可以相同）',
  `GUDS_OPT_VAL_MPNG` varchar(1000) DEFAULT NULL COMMENT '상품옵션값매핑(옵션값 여러 개 매핑) | 商品SKU的属性和属性值映射(一个属性映射多个值)',
  `GUDS_OPT_UPC_ID` varchar(20) DEFAULT NULL COMMENT '상품옵션바코드 | 商品条形码(SKU条形码)',
  `GUDS_OPT_MNG_CD` varchar(20) DEFAULT NULL COMMENT '상품옵션관리코드 | 商品SKU管理编码',
  `GUDS_OPT_REG_STAT_CD` varchar(10) DEFAULT NULL COMMENT '상품옵션등록상태코드 | 商品SKU录入状态编码',
  `GUDS_OPT_SALE_STAT_CD` varchar(10) DEFAULT NULL COMMENT '상품옵션판매상태코드 | 商品SKU销售状态编码,',
  `GUDS_OPT_MIN_ORD_QTY` decimal(10,2) DEFAULT NULL COMMENT '상품옵션최소주문수량 | 商品SKU最少可下订单量',
  `GUDS_OPT_EXP_DT` varchar(8) DEFAULT NULL COMMENT '상품옵션만료일자 | 商品SKU过期日期',
  `GUDS_OPT_LOCK_QTY` decimal(10,0) DEFAULT '0' COMMENT '锁定库存',
  `GUDS_OPT_SALE_QTY` decimal(10,0) DEFAULT '0' COMMENT '销售量',
  `GUDS_OPT_ORDER_QTY` decimal(10,0) DEFAULT '0' COMMENT '订单占用库存',
  `GUDS_OPT_STK_QTY` decimal(10,2) DEFAULT NULL COMMENT '상품옵션재고수량 | 商品SKU总库存数量',
  `GUDS_OPT_ORG_PRC` decimal(12,4) DEFAULT NULL COMMENT '상품옵션원가(옵션공급가) | 商品SKU原价(SKU供应商价格，采购价)',
  `GUDS_OPT_SALE_PRC` decimal(12,4) DEFAULT NULL COMMENT '상품옵션판매가 | 市场价(或称吊牌价：非真实卖价)',
  `GUDS_OPT_BELOW_SALE_PRC` decimal(12,4) DEFAULT NULL COMMENT '一件代发价格(实际销售价, 供货商一件代发的卖价)',
  `GUDS_OPT_BELOW_SALE_PRC_YN` char(1) DEFAULT NULL COMMENT '是否显示 一件代发价格',
  `GUDS_OPT_BELOW_SALE_PRC_MARGIN_RATE` decimal(4,1) DEFAULT NULL COMMENT 'SKU一件代发价利润率，毛利率',
  `GUDS_OPT_HIGH_SALE_PRC` decimal(12,2) DEFAULT NULL COMMENT 'SKU最高价',
  `GUDS_OPT_HIGH_SALE_PRC_YN` char(1) DEFAULT NULL COMMENT 'MOQ X 1 표시여부 | 是否显示最高价,[起订量 X 1] 到 [起订量 X 4] 的价格',
  `GUDS_OPT_HIGH_SALE_PRC_XNUM` varchar(6) DEFAULT NULL COMMENT '最高价：最小起订量的倍数，1个~4个单位',
  `GUDS_OPT_HIGH_SALE_PRC_MARGIN_RATE` decimal(4,1) DEFAULT NULL COMMENT '最高价毛利率',
  `GUDS_OPT_MID_SALE_PRC` decimal(12,2) DEFAULT NULL COMMENT 'SKU 中间价',
  `GUDS_OPT_MID_SALE_PRC_YN` char(1) DEFAULT NULL COMMENT 'MOQ X 5 표시여부 | 是否显示中间价，[起订量 X 5] 到 [起订量 X 9] 的价格',
  `GUDS_OPT_MID_SALE_PRC_XNUM` varchar(6) DEFAULT NULL COMMENT '中间价起订量的倍数，5个~9个单位',
  `GUDS_OPT_MID_SALE_PRC_MARGIN_RATE` decimal(4,1) DEFAULT NULL COMMENT '中间价利润率，毛利率',
  `GUDS_OPT_LOW_SALE_PRC` decimal(12,2) DEFAULT NULL COMMENT 'SKU最低价',
  `GUDS_OPT_LOW_SALE_PRC_YN` char(1) DEFAULT NULL COMMENT 'MOQ X 10 표시여부 | 是否显示最低价，[起订量 X 10]以上的价格',
  `GUDS_OPT_LOW_SALE_PRC_XNUM` varchar(6) DEFAULT NULL COMMENT '最低价起订量的倍数：10个单位以上',
  `GUDS_OPT_LOW_SALE_PRC_MARGIN_RATE` decimal(4,1) DEFAULT NULL COMMENT '最低价利润率，毛利率',
  `GUDS_OPT_USE_YN` char(1) DEFAULT NULL COMMENT '상품옵션사용여부 | 该SKU是否启用(是否可用,上架)',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统录入者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일자 | 系统录入时间',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일자 | 系统变更时间',
  `GUDS_ASK_BUYNUM` varchar(12) DEFAULT NULL COMMENT '求购数量',
  `GUDS_MIN_PROFIT_RATE` varchar(6) DEFAULT NULL COMMENT '最小利润率',
  `GUDS_MAX_PROFIT_RATE` varchar(6) DEFAULT NULL COMMENT '最大利润率',
  `GUDS_NEW_PRICES` varchar(1000) DEFAULT NULL COMMENT '十段价格 ---- 什么叫做十段价格？',
  `GUDS_HS_CODE` varchar(20) DEFAULT '' COMMENT '海关备案编码',
  `GUDS_HS_CODE2` varchar(20) DEFAULT '' COMMENT 'HS编码(海关编码，中国目前HS编码，一共10位，前面8位为主码，后两位为附加码。)',
  `PUSH_STATUS` varchar(10) DEFAULT 'N000890100' COMMENT '推送状态(发布到商家的状态),N000890100=待推送,N000890200=推送成功,N000890300=推送失败',
  `PUSH_TIME` datetime DEFAULT NULL COMMENT '推送时间',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'BI统计使用',
  PRIMARY KEY (`SLLR_ID`,`GUDS_ID`,`GUDS_OPT_ID`),
  KEY `GUDS_ID` (`GUDS_ID`) USING BTREE,
  KEY `GUDS_OPT_ID` (`GUDS_OPT_ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- SKU规格属性表，所有的规格属性名称 
-- 与商品SKU关联，多对多的关系，一个SKU包含多个属性，一个属性属于多个SKU。
-- 与属性值表关联，一对多的关系，一个属性有多个属性值，一个属性值属于一个属性。
CREATE TABLE `tb_ms_opt` (
  `OPT_ID` varchar(20) NOT NULL COMMENT '옵션아이디 | 属性 ID',
  `OPT_NM` varchar(40) DEFAULT NULL COMMENT '옵션명 | 属性名称(韩语？)',
  `OPT_CNS_NM` varchar(40) DEFAULT NULL COMMENT '옵션중문명 | 属性中文名称',
  `OPT_SORT_NO` decimal(4,0) DEFAULT NULL COMMENT '옵션정렬번호 | 属性排序编号',
  `OPT_USE_YN` char(1) DEFAULT NULL COMMENT '옵션사용여부 | 属性是否启用',
  `OPT_CONT` varchar(100) DEFAULT NULL COMMENT '옵션내용 | 属性内容，目前都是NULL',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  `OPT_ENG_NM` varchar(40) DEFAULT NULL COMMENT '属性英文名称',
  `OPT_JPA_NM` varchar(40) DEFAULT NULL COMMENT '属性日文名称',
  PRIMARY KEY (`OPT_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- SKU 属性值表，所有属性对应的值内容
-- 与SKU属性 直接相关，与SKU直接相关。
-- 与属性表是 一对多的关系，多个属性值属于一个属性，一个属性包含多个属性值。一个属性值不能属于多给属性。
CREATE TABLE `tb_ms_opt_val` (
  `OPT_ID` varchar(20) NOT NULL COMMENT '옵션아이디 | 属性ID，关联属性表',
  `OPT_VAL_ID` varchar(20) NOT NULL COMMENT '옵션값아이디 | 属性值ID',
  `OPT_VAL_NM` varchar(40) DEFAULT NULL COMMENT '옵션값명 | 属性值名称(韩语？)',
  `OPT_VAL_CNS_NM` varchar(40) DEFAULT NULL COMMENT '옵션값중문명 | 属性值中文名称',
  `OPT_VAL_SORT_NO` decimal(4,0) DEFAULT NULL COMMENT '옵션값정렬번호 | 属性值排序编号',
  `OPT_VAL_USE_YN` char(1) DEFAULT NULL COMMENT '옵션값사용여부 | 属性值是否启用',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  `OPT_VAL_ENG_NM` varchar(40) DEFAULT NULL COMMENT '属性值英文名称',
  `OPT_VAL_JPA_NM` varchar(40) DEFAULT NULL COMMENT '属性值日文名称',
  PRIMARY KEY (`OPT_ID`,`OPT_VAL_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- SKU属性及值的多语言编码表。
-- 与SKU属性表的关系是，多对多。
-- 与SKU属性值表的关系是多对多。
CREATE TABLE `tb_ms_multi_code` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `SLLR_ID` varchar(20) DEFAULT NULL COMMENT '品牌名唯一标识(实际没有卵用)',
  `PAR_CODE` varchar(10) DEFAULT NULL COMMENT '父类Code值',
  `CODE` varchar(10) DEFAULT NULL COMMENT '传输值，属性或属性值的编码',
  `VAL` varchar(20) DEFAULT NULL COMMENT '编码对应的属性或属性值的内容，用于展示的可读内容(每种语言一条)。',
  `ALL_VAL` varchar(100) DEFAULT NULL COMMENT '多国语言拼到一起(用"/"拼接)',
  `TYPE` varchar(10) DEFAULT NULL COMMENT '类型编码:N000960100=属性名；N000960200=属性值',
  `LANGUAGE` varchar(10) DEFAULT NULL COMMENT '语言类型编码：N000920100=中文,N000920200=English,N000920300=日文,N000920400=韩文,',
  `USED_YN` varchar(1) DEFAULT 'Y' COMMENT '是否可用 （Y: 可用 N:不可用）',
  `updateTime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21553 DEFAULT CHARSET=utf8;

-- 品牌供应商标，品牌商家
CREATE TABLE `tb_ms_sllr` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '品牌商家ID，供应商ID，ID',
  `SLLR_PWD` varchar(100) NOT NULL COMMENT '판매자비밀번호 | 入驻品牌商家（供应商）密码，Pwd',
  `BZOP_CONM` varchar(100) DEFAULT NULL COMMENT '사업자상호 | 供应商企业名称，Company Name',
  `CRN` varchar(12) DEFAULT NULL COMMENT '사업자등록번호 | 经营许可证编号，Business License ID',
  `BZOP_REPR_NM` varchar(40) DEFAULT NULL COMMENT '사업자대표자명 | 供应商企业法定代表人姓名，Representative',
  `COMM_RTL_YN` char(1) DEFAULT NULL COMMENT '통신판매업여부 | 邮购业务，是否开启邮购？',
  `COMM_RTL_ANC_NO` varchar(20) DEFAULT NULL COMMENT '통신판매업신고번호 | 邮购业务等登记备案号，Mail order business registration number',
  `BZOP_REP_TEL_NO` varchar(13) DEFAULT NULL COMMENT '사업자대표전화번호 | 供应商企业总机电话号码，Company Phone Number',
  `DLV_MODE_CD` varchar(10) DEFAULT NULL COMMENT '배송방식코드 | 通讯方式编码',
  `CALP_DPST_BANK_CD` varchar(10) DEFAULT NULL COMMENT '정산대금입금은행코드 | 开户行银行编码',
  `CALP_DPSR_NM` varchar(40) DEFAULT NULL COMMENT '정산대금예금주명 | 对账收款账户名',
  `CALP_ACNT_NO` varchar(20) DEFAULT NULL COMMENT '정산대금계좌번호 | 对账收款账户号码',
  `BZOP_DIV_CD` varchar(10) DEFAULT NULL COMMENT '사업자구분코드 | 企业代码',
  `CRP_REG_NO` varchar(14) DEFAULT NULL COMMENT '법인등록번호 | 法人注册号码，',
  `BZTP_NM` varchar(100) DEFAULT NULL COMMENT '업태명 | 业务名：批发和零售业务，Business Type',
  `ITEM_NM` varchar(100) DEFAULT NULL COMMENT '종목명 | 经营项目，Project：进出口，电子商务',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统录入者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일시 | 系统录入时间',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일시 | 系统变更时间',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 品牌商家(供应商) 地址表
CREATE TABLE `tb_ms_sllr_addr` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '品牌商家ID，供应商ID',
  `SLLR_ADDR_DIV_CD` varchar(10) NOT NULL COMMENT '판매자주소구분코드 | 商家地址分类编码',
  `SLLR_ZPNO` varchar(7) NOT NULL COMMENT '판매자우편번호 | 供应商 邮编',
  `SLLR_ADDR` varchar(1000) NOT NULL COMMENT '판매자주소 | 供应商地址',
  `SLLR_DTL_ADDR` varchar(1000) DEFAULT NULL COMMENT '판매자상세주소 | 供应商详细地址',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统录入者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일자 | 系统录入时间',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일자 | 系统变更时间',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`,`SLLR_ADDR_DIV_CD`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 商家类目数据包，有点类似是 通用类目做为后端类目的话，该表数据就是前端类目。
-- 跟商品是一对多关系，一个商品属于一个商家类目，一个上商家类目包含都个商品
-- 与类目的关系是，一对一关系。
CREATE TABLE `tb_ms_sllr_cat` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '销售者ID，实际是商家ID',
  `CAT_CD` varchar(10) NOT NULL COMMENT '关联的通用类目编码',
  `COMM_CAT_CD` varchar(10) DEFAULT NULL COMMENT '商家类目编码，---- 商家自定义的类目编码？',
  `CAT_NM` varchar(20) DEFAULT NULL COMMENT '카테고리명 | （商家自定义）类目名称',
  `CAT_KOR_NM` varchar(20) DEFAULT NULL COMMENT '(商家自定义)类目韩语名称',
  `CAT_SORT_NO` decimal(2,0) DEFAULT NULL COMMENT '카테고리정렬번호 | 商家类目排序编号',
  `CAT_MPNG_YN` char(10) DEFAULT NULL COMMENT '카테고리매핑여부 | 是否启用类别映射',
  `CAT_DSC` varchar(4000) DEFAULT NULL COMMENT '카테고리설명 | 类目描述',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'BI统计更新时间',
  PRIMARY KEY (`CAT_CD`,`SLLR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 通用类目数据表， 商家类目会关联这个通用类目。
-- 
CREATE TABLE `tb_ms_cmn_cat` (
  `CAT_CD` varchar(10) NOT NULL COMMENT '카테고리코드|类目CODE',
  `CAT_NM` varchar(20) DEFAULT NULL COMMENT '카테고리명|类目名',
  `CAT_CNS_NM` varchar(20) DEFAULT NULL COMMENT '카테고리중문명 | 类目名中文名',
  `CAT_OPR_EML_1` varchar(100) DEFAULT NULL COMMENT '카테고리담당자이메일1 | 类目负责人邮箱1',
  `CAT_OPR_EML_2` varchar(100) DEFAULT NULL COMMENT '카테고리담당자이메일2 |类目负责人邮箱2',
  `CAT_OPR_EML_3` varchar(100) DEFAULT NULL COMMENT '카테고리담당자이메일3 | 类目负责人邮箱3',
  `CAT_OPR_EML_4` varchar(100) DEFAULT NULL COMMENT '카테고리담당자이메일4 | 类目负责人邮箱1',
  `CAT_SORT` int(2) DEFAULT NULL COMMENT 'Category Sort',
  `PAR_CAT_CD` varchar(10) DEFAULT NULL COMMENT '父类目的Code码',
  `CAT_NM_PATH` varchar(100) DEFAULT NULL COMMENT '类目名层级路径，如：母婴专区>童装>儿童配饰',
  `ALIAS` varchar(100) DEFAULT NULL COMMENT '别名',
  `CAT_LEVEL` varchar(2) DEFAULT NULL COMMENT '类目层级',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `status` smallint(1) DEFAULT NULL COMMENT '类目状态：1=表示，目前只有：1；',
  `DISABLE_YN` varchar(2) DEFAULT 'N' COMMENT '是否显示',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'For BI',
  PRIMARY KEY (`id`),
  KEY `index_cat_cd` (`CAT_CD`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8;

-- 商品品牌数据表
CREATE TABLE `tb_ms_brnd_str` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '销售者ID（商家ID，实际就是品牌ID）',
  `BRND_STR_NM` varchar(100) DEFAULT NULL COMMENT '브랜드스토어명(중문) | 品牌店铺名称(中文)',
  `BRND_STR_KR_NM` varchar(255) DEFAULT NULL COMMENT '品牌店名（韩文）',
  `BRND_STR_JPA_NM` varchar(255) DEFAULT NULL COMMENT '品牌店名（日文）',
  `BRND_STR_ENG_NM` varchar(100) DEFAULT NULL COMMENT '브랜드스토어영문명 | 品牌店名称(英文)',
  `BRND_STR_STAT_CD` varchar(10) DEFAULT NULL COMMENT '브랜드스토어상태코드 | 品牌店状态CODE,N000040100=入驻申请,N000040200=完成批准(等待),N000040300=完成批准(展示),N000040400=入驻退回,N000040500=闭店',
  `BRND_STR_OP_OPR_NM` varchar(40) DEFAULT NULL COMMENT '브랜드스토어운영담당자명 | 品牌店运营负责人姓名',
  `BRND_STR_OP_OPR_CP_NO` varchar(13) DEFAULT NULL COMMENT '브랜드스토어운영담당자휴대폰번호 | 品牌店运营负责人手机号',
  `BRND_STR_OP_OPR_CMP_TEL_NO` varchar(13) DEFAULT NULL COMMENT '브랜드스토어운영담당자회사전화번호 | 品牌店运营负责人公司座机',
  `BRND_STR_OP_OPR_EML` varchar(100) DEFAULT NULL COMMENT '브랜드스토어운영담당자이메일 | 品牌店运营负责人邮箱',
  `BRND_STR_BACT_OPR_NM` varchar(40) DEFAULT NULL COMMENT '브랜드스토어정산영담당자명 | 品牌店清算负责人姓名',
  `BRND_STR_BACT_OPR_CP_NO` varchar(13) DEFAULT NULL COMMENT '브랜드스토어정산담당자휴대폰번호 | 品牌店清算负责人手机号',
  `BRND_STR_BACT_OPR_CMP_TEL_NO` varchar(13) DEFAULT NULL COMMENT '브랜드스토어정사담당자회사전화번호 | 品牌店清算负责人公司座机',
  `BRND_STR_BACT_OPR_EML` varchar(100) DEFAULT NULL COMMENT '브랜드스토어정산담당자이메일 | 品牌店清算负责人邮箱',
  `BRND_AUTH_YN` char(1) DEFAULT NULL COMMENT '브랜드스토어수권서여부 | 品牌店是否授权',
  `ENST_REQ_DT` varchar(8) DEFAULT NULL COMMENT '입점요청일자 | 入驻申请日期',
  `ENST_APRV_DT` varchar(8) DEFAULT NULL COMMENT '입점승인(반려)일자 | 入驻批准（未批准）日期',
  `ENST_APRV_DNY_RSN_CONT` varchar(200) DEFAULT NULL COMMENT '입점승인거절사유내용 | 入驻未批准理由',
  `MULTI_BRND_SLLR_ID` varchar(500) DEFAULT NULL COMMENT '多品牌商家ID ？',
  `MULTI_BRND_STR_ENG_NM` varchar(2500) DEFAULT NULL COMMENT '多品牌商家英文名称？',
  `SYS_REGR_ID` varchar(20) NOT NULL COMMENT '시스템등록자아이디 | 系统录入者ID',
  `SYS_REG_DTTM` datetime NOT NULL COMMENT '시스템등록일시 | 系统录入时间',
  `SYS_CHGR_ID` varchar(20) DEFAULT NULL COMMENT '시스템변경자아이디 | 系统变更者ID',
  `SYS_CHG_DTTM` datetime DEFAULT NULL COMMENT '시스템변경일시 | 系统变更时间',
  `BRND_ORGP_CD` varchar(10) DEFAULT NULL COMMENT 'country | 品牌原产国编码，N000410100=韩国,N000410200=中国,N000410300=日本,N000410400=越南,N000410500=缅甸,N000410600=印度尼西亚',
  `BRND_INTD_CONT` varchar(500) DEFAULT NULL COMMENT 'Brand Introduction Content | 品牌介绍内容',
  `VEST_WAY` varchar(20) DEFAULT 'N000740100' COMMENT '授权方式',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  `SALE_CHANNEL` varchar(255) DEFAULT NULL COMMENT '上架渠道（，隔开）,N000830100=B5C,N000830200=BHB,N000830300=Qoo10-SG,N000830400=Qoo10-KRS,N000830500=Qoo10-JP,N000830600=Wish,N000830700=Lazada-MY,N000830800=Lazada-ID,N000830900=Lazada-TH,N000831000=Lazada-PH,N000831100=Lazada-SG,N000831200=Ebay,N000831300=YT,N000831400=Gshopper-KR,N000831500=auction,N000831600=11ST,N000831700=NAVER,N000831800=Gmarket,N000831900=Taobao,N000832000=Tmall,N000832100=1688,N000832200=aliexpress,',
  PRIMARY KEY (`SLLR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 品牌商家图片表
-- 关联品牌商家表，一对多关心，一个品牌商家有多个品牌图片。
-- 
CREATE TABLE `tb_ms_brnd_str_img` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '판매자아이디 | 销售者ID（商家ID，实际就是品牌ID）',
  `BRND_STR_IMG_CD` varchar(10) NOT NULL COMMENT '브랜드스토어이미지코드 | 品牌图片编码',
  `BRND_STR_IMG_STAT_CD` varchar(10) NOT NULL COMMENT '브랜드스토어이미지상태코드 | 品牌图片状态',
  `BRND_STR_IMG_ORGT_FILE_NM` varchar(100) DEFAULT NULL COMMENT '브랜드스토어이미지원본파일명 | 品牌图片原始文件名',
  `BRND_STR_IMG_SYS_FILE_NM` varchar(100) DEFAULT NULL COMMENT '브랜드스토어이미지시스템파일명 | 品牌图片系统生成的新文件名',
  `BRND_STR_IMG_CDN_ADDR` varchar(1000) DEFAULT NULL COMMENT '브랜드스토어이미지CDN주소 | 品牌图片CDN访问地址',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`,`BRND_STR_IMG_CD`,`BRND_STR_IMG_STAT_CD`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 品牌自定义类目
-- 新增品牌的时候,品牌关联的商品类目
-- 手动自定义添加的类目
CREATE TABLE `tb_ms_brnd_str_rep_cat` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '판매자아이디 | 销售者ID（商家ID，实际就是品牌ID）',
  `CAT_CD` varchar(10) NOT NULL COMMENT '카테고리코드 | 类目CODE，关联通用类目表',
  `CAT_DIV_CD` varchar(10) DEFAULT NULL COMMENT '카테고리구분코드 | 类目区分编码',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`,`CAT_CD`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 品牌视频表
CREATE TABLE `tb_ms_brnd_str_video` (
  `SLLR_ID` varchar(20) NOT NULL COMMENT '销售者ID',
  `BRND_STR_VIDEO_TYPE` varchar(10) NOT NULL COMMENT '品牌店铺视频类型',
  `BRND_STR_VIDEO_STAT_CD` varchar(10) NOT NULL COMMENT '品牌店铺视频状态code',
  `BRND_STR_VIDEO_ORGT_FILE_NM` varchar(100) DEFAULT NULL COMMENT '品牌店铺视频源文件名',
  `BRND_STR_VIDEO_CDN_ADDR` varchar(1000) DEFAULT NULL COMMENT '品牌店铺视频CDN地址',
  `updated_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'FOR BI统计',
  PRIMARY KEY (`SLLR_ID`,`BRND_STR_VIDEO_TYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





























