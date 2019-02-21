# Host: localhost  (Version: 5.5.53)
# Date: 2019-02-20 14:23:16
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

INSERT INTO `think_admin` VALUES (1,'admin','218dbb225911693af03a713581a7227f','218dbb225911693af03a713581a7227f','127.0.0.1',1550629517,'68091021ad0c70a80495c3a44d648374',1,1);

#
# Structure for table "think_article"
#

CREATE TABLE `think_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL COMMENT '文章组分类id',
  `title` varchar(200) DEFAULT '' COMMENT '文章标题',
  `remark` varchar(255) DEFAULT '' COMMENT '文章描述',
  `img_path` varchar(100) DEFAULT '' COMMENT '图片路径',
  `content` text COMMENT '文章详情',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `state` int(1) DEFAULT '1' COMMENT '文章状态0禁用 1 开启',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='文章列表';

#
# Data for table "think_article"
#

INSERT INTO `think_article` VALUES (3,6,'我是帮助','我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介','/uploads/article/20190220/12d31324b4b48a01213299177ad37691.jpg','<p>我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介我是简介</p>',1550630301,1),(4,5,'我是公告','我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告','/uploads/article/20190220/8e9328a96cccee3dce907c97f8617cbe.jpg','<p>我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告我是公告</p>',1550630239,1),(5,8,'服务协议','服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议','/uploads/article/20190220/dae0533454b037399c8c3cf9d36ef735.jpg','<p>服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议服务协议</p>',1550634228,1);

#
# Structure for table "think_article_group"
#

CREATE TABLE `think_article_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL COMMENT '分类标题',
  `state` int(1) DEFAULT '1' COMMENT '0禁用  1 开启',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='新闻分类';

#
# Data for table "think_article_group"
#

INSERT INTO `think_article_group` VALUES (5,'系统公告',1,1550629800),(6,'帮助中心',1,1550629810),(7,'服务协议',1,1550629964),(8,'首页公告',1,1550634015);

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "think_auth_group"
#

INSERT INTO `think_auth_group` VALUES (1,'超级管理员',1,'',1446535750,1446535750),(4,'测试2号',1,'5,8,6,9,10,11',1550208437,1550284617),(5,'系统测试',1,'',1550209950,1550209950);

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
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "think_auth_rule"
#

INSERT INTO `think_auth_rule` VALUES (1,'#','会员管理',1,1,'fa fa-users','',0,1,1446535750,1446535750),(2,'#','资金管理',1,1,'fa fa-money','',0,2,1446535750,1446535750),(3,'#','订单管理',1,1,'fa  fa-pie-chart','',0,3,1446535750,1446535750),(4,'#','日志管理',1,1,'fa fa-file-text','',0,5,1446535750,1446535750),(5,'#','配置管理',1,1,'fa fa-cogs','',0,6,1446535750,1446535750),(6,'#','系统管理',1,1,'fa fa-cog','',0,7,1446535750,1446535750),(7,'#','信息管理',1,1,'fa fa-file-image-o','',0,4,1446535750,1446535750),(8,'admins/config/web_config','网站配置',1,1,'','',5,1,1446535750,1446535750),(9,'admins/adminlist/user_list','管理列表',1,1,'','',6,1,1446535750,1446535750),(10,'admins/adminlist/role_list','角色管理',1,1,'','',6,2,1446535750,1446535750),(11,'admins/adminlist/menu_list','菜单管理',1,1,'','',6,3,1446535750,1446535750),(16,'admins/member/index','会员列表',1,1,'','',1,1,1550303579,1550303892),(17,'#','团队列表',1,1,'','',1,2,1550303614,1550303614),(18,'#','资金明细',1,1,'','',2,1,1550303635,1550303635),(19,'admins/article/group_index','文章分类',1,1,'','',7,1,1550460976,1550460993),(20,'admins/article/index','文章列表',1,1,'','',7,2,1550474467,1550474467),(21,'admins/Banner/group_index','图片分类',1,1,'','',7,3,1550630738,1550630738),(22,'admins/Banner/index','图片列表',1,1,'','',7,4,1550630989,1550630989);

#
# Structure for table "think_bank_list"
#

CREATE TABLE `think_bank_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bankname` varchar(50) DEFAULT '' COMMENT '银行卡名称',
  `state` int(1) DEFAULT '1' COMMENT '1 使用  2 禁用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='银行列表';

#
# Data for table "think_bank_list"
#

INSERT INTO `think_bank_list` VALUES (1,'中国建设银行',1),(2,'中国农业银行',1),(3,'中国商业银行',1);

#
# Structure for table "think_banner"
#

CREATE TABLE `think_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT '' COMMENT '图片标题',
  `group_id` int(11) DEFAULT NULL COMMENT '图片归属组',
  `state` int(1) DEFAULT '1' COMMENT '0禁用 1开启',
  `path` varchar(255) DEFAULT NULL COMMENT '图片路径',
  `sort` int(11) DEFAULT NULL COMMENT '图片排序',
  `web_url` varchar(150) DEFAULT NULL COMMENT '图片跳转路径',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='广告图';

#
# Data for table "think_banner"
#

INSERT INTO `think_banner` VALUES (1,'测试',2,1,'/uploads/article/20190220/c86f33dddf8fe70be0a71b38b3e4359b.jpg',NULL,'',1550633258),(2,'测试2',1,1,'/uploads/article/20190220/84d580ba369b5399d21e1ae51c236b81.jpg',NULL,'',1550632596);

#
# Structure for table "think_banner_group"
#

CREATE TABLE `think_banner_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL COMMENT '图片类型名称',
  `state` int(1) DEFAULT '1' COMMENT '0 禁用 1 开启',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='广告图分组';

#
# Data for table "think_banner_group"
#

INSERT INTO `think_banner_group` VALUES (1,'首页轮播图',1,1550461676),(2,'个人中心',1,1550461676);

#
# Structure for table "think_member"
#

CREATE TABLE `think_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(150) DEFAULT NULL,
  `user_img` varchar(200) DEFAULT '' COMMENT '微信头像',
  `account` varchar(50) DEFAULT NULL COMMENT '账号',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `password` varchar(32) DEFAULT NULL COMMENT '密码',
  `pay_password` varchar(32) DEFAULT NULL COMMENT '支付密码',
  `state` int(1) DEFAULT '1' COMMENT '0禁止 1开启',
  `pid` int(11) DEFAULT NULL COMMENT '父级ID',
  `uuid` varchar(40) DEFAULT NULL COMMENT '微信注册id',
  `type` int(11) DEFAULT '1' COMMENT '会员类型  1注册会员 2 VIP会员 3代理',
  `create_time` int(11) DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`),
  KEY `uuid` (`uuid`),
  KEY `mobile` (`mobile`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='会员列表';

#
# Data for table "think_member"
#

INSERT INTO `think_member` VALUES (1,'测试','http://img4.duitang.com/uploads/item/201407/16/20140716132526_TcyTY.thumb.600_0.jpeg','15039157666','15039157666','218dbb225911693af03a713581a7227f','218dbb225911693af03a713581a7227f',1,0,'218dbb225911693af03a713581a7227f',1,1550455221),(2,'测试','img_path',NULL,'15031957667',NULL,NULL,1,NULL,'1213123',1,1550554886);

#
# Structure for table "think_member_bank"
#

CREATE TABLE `think_member_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `bank_name` varchar(50) DEFAULT '' COMMENT '银行卡名称',
  `bankcard` varchar(32) DEFAULT '' COMMENT '银行卡号',
  `username` varchar(50) DEFAULT '' COMMENT '开户人',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户银行卡列表';

#
# Data for table "think_member_bank"
#


#
# Structure for table "think_money"
#

CREATE TABLE `think_money` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '会员表id',
  `balance` decimal(10,2) DEFAULT '0.00' COMMENT '账户余额',
  `integral` decimal(10,2) DEFAULT '0.00' COMMENT '账户积分',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员资金列表';

#
# Data for table "think_money"
#


#
# Structure for table "think_money_log"
#

CREATE TABLE `think_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '会员id',
  `type` int(11) DEFAULT NULL COMMENT '资金类型  1 余额 2 积分  3 微信',
  `state` int(11) DEFAULT NULL COMMENT '1增加 2 减少',
  `info` text COMMENT '资金详情',
  `source` varchar(50) DEFAULT NULL COMMENT '资金来源/红包/充值 订单号',
  `trend` int(11) DEFAULT NULL COMMENT '1红包  2 升级',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员资金记录';

#
# Data for table "think_money_log"
#


#
# Structure for table "think_web_config"
#

CREATE TABLE `think_web_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '配置名称',
  `value` varchar(255) DEFAULT '' COMMENT '配置参数',
  `info` varchar(255) DEFAULT '' COMMENT '详情',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='网站配置';

#
# Data for table "think_web_config"
#

INSERT INTO `think_web_config` VALUES (1,'web_for_short','LBW','网站简称'),(2,'web_names','Style','网站名称'),(3,'web_keywords','刘柏伟测试站点','网站关键词'),(4,'web_description','刘柏伟测试站点','网站描述'),(5,'web_login_view','2','登陆页面模板'),(6,'app_state','1','app是否允许登录'),(7,'app_sms','0','app是否使用短信');
