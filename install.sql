CREATE TABLE IF NOT EXISTS `ip_area` (
  `id` int(10) NOT NULL auto_increment,
  `startIp` bigint(20) NOT NULL COMMENT '开始IP段',
  `endIp` bigint(20) NOT NULL COMMENT '结束IP段',
  `country` varchar(50) NOT NULL COMMENT '国家名称',
  `province` varchar(50) default NULL COMMENT '省份名称',
  `city` varchar(50) default NULL COMMENT '城市名称',
  `isp` varchar(10) default NULL COMMENT '接入服务商',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `startIp` (`startIp`,`endIp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='ip区域表';

--
-- Table structure for table `qqwry`
--

CREATE TABLE IF NOT EXISTS `qqwry` (
  `ip` bigint(20) NOT NULL COMMENT '转换long型后的ip地址',
  `status` tinyint(1) NOT NULL default '1' COMMENT '状态，1为未同步，2为已经同步',
  `modifyTime` int(11) NOT NULL default '0' COMMENT '更新时间',
  `createTime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='纯真IP库表';