# Host: localhost  (Version: 5.5.53)
# Date: 2019-02-12 13:49:25
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "think_admin"
#

CREATE TABLE `think_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(40) DEFAULT NULL,
  `check_password` varchar(32) DEFAULT NULL COMMENT '管理校验密码',
  `ip` varchar(40) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `token` varchar(40) DEFAULT NULL COMMENT '登录令牌',
  `group_id` int(11) DEFAULT NULL COMMENT '角色组id',
  `state` int(1) DEFAULT '1' COMMENT '1 开启 0 禁用',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理列表';

#
# Data for table "think_admin"
#

INSERT INTO `think_admin` VALUES (1,'admin','218dbb225911693af03a713581a7227f',NULL,'127.0.0.1',1549941913,'f7eba55c22229a26175ff45d942c5a6a',1,1);

#
# Structure for table "think_auth_group"
#

CREATE TABLE `think_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` text NOT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "think_auth_group"
#

INSERT INTO `think_auth_group` VALUES (1,'超级管理员',1,'',1446535750,1446535750);

#
# Structure for table "think_auth_group_access"
#

CREATE TABLE `think_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `group_id` (`group_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

#
# Data for table "think_auth_group_access"
#

INSERT INTO `think_auth_group_access` VALUES (1,1);

#
# Structure for table "think_auth_rule"
#

CREATE TABLE `think_auth_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `css` varchar(20) NOT NULL COMMENT '样式',
  `condition` char(100) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "think_auth_rule"
#

INSERT INTO `think_auth_rule` VALUES (1,'#','会员管理',1,1,'fa fa-users','',0,1,0,NULL),(2,'#','资金管理',1,1,'fa fa-money','',0,2,0,NULL),(3,'#','订单管理',1,1,'fa  fa-pie-chart','',0,3,0,NULL),(4,'#','日志管理',1,1,'fa fa-file-text','',0,5,0,NULL),(5,'#','配置管理',1,1,'fa fa-cogs','',0,6,0,NULL),(6,'#','系统管理',1,1,'fa fa-cog','',0,7,0,NULL),(7,'#','信息管理',1,1,'fa fa-file-image-o','',0,4,0,NULL),(8,'admins/config/web_config','网站配置',1,1,'','',5,1,0,NULL),(9,'admins/adminlist/user_list','管理列表',1,1,'','',6,1,0,NULL),(10,'','角色管理',1,1,'','',6,2,0,NULL),(11,'','菜单管理',1,1,'','',6,3,0,NULL);

#
# Structure for table "think_web_config"
#

CREATE TABLE `think_web_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '配置名称',
  `value` varchar(255) DEFAULT '' COMMENT '配置参数',
  `info` varchar(255) DEFAULT '' COMMENT '详情',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='网站配置';

#
# Data for table "think_web_config"
#

INSERT INTO `think_web_config` VALUES (1,'web_for_short','LBW','网站简称'),(2,'web_names','Style','网站名称'),(3,'web_keywords','刘柏伟测试站点','网站关键词'),(4,'web_description','刘柏伟测试站点','网站描述'),(5,'web_login_view','2','登陆页面模板');
