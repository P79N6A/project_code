--
-- 表的结构 `jx_address`
--

CREATE TABLE IF NOT EXISTS `jx_address` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `name` varchar(40) NOT NULL COMMENT '收货人姓名',
  `mobile` varchar(11) default NULL COMMENT '手机号码',
  `phone` varchar(20) default NULL COMMENT '固定电话',
  `province_id` int(5) NOT NULL COMMENT '省份ID',
  `city_id` int(5) NOT NULL COMMENT '城市ID',
  `area_id` int(5) NOT NULL COMMENT '区域ID',
  `street` varchar(130) default NULL COMMENT '街道号',
  `postcode` int(6) default NULL COMMENT '邮编',
  `default_type` int(1) default NULL COMMENT '默认收货地址，当为1时为默认收货地址，此字段仅用于同一收货人具有多个收货地址时',
  `createtime` int(10) NOT NULL COMMENT '地址添加时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_orders`
--

CREATE TABLE IF NOT EXISTS `jx_orders` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `pay_id` varchar(20) NOT NULL COMMENT '订单编号',
  `uid` int(11) NOT NULL COMMENT '购买者ID',
  `sid` int(11) NOT NULL COMMENT '卖家ID',
  `pid` int(11) NOT NULL COMMENT '商品ID',
  `quantity` int(11) NOT NULL COMMENT '商品购买数量',
  `price` double(10,2) NOT NULL COMMENT '商品单价',
  `property` varchar(60) default NULL COMMENT '商品属性',
  `origin` double(10,2) NOT NULL COMMENT '订单总价',
  `express` enum('y','n') NOT NULL default 'y' COMMENT '订单是否有运费，y为有，n为没有',
  `express_name` varchar(20) default NULL COMMENT '快递公司的名称',
  `express_price` double(10,2) default NULL COMMENT '快递的费用',
  `express_id` varchar(20) default NULL COMMENT '快递单号',
  `realname` varchar(40) NOT NULL COMMENT '收货人姓名',
  `mobile` varchar(12) default NULL COMMENT '手机号码',
  `phone` varchar(20) default NULL COMMENT '固定电话',
  `province_id` int(5) NOT NULL COMMENT '省份ID',
  `city_id` int(5) NOT NULL COMMENT '城市ID',
  `area_id` int(5) NOT NULL COMMENT '地区ID',
  `street` varchar(130) NOT NULL COMMENT '街道名称',
  `postcode` int(6) NOT NULL COMMENT '邮编',
  `remark` text COMMENT '买家留言',
  `paytype` varchar(20) default NULL COMMENT '支付方式',
  `state` enum('unpay','pay','del') NOT NULL default 'unpay' COMMENT '订单状态，unpay默认为未付款，pay为已付款，del为已删除',
  `status` int(1) NOT NULL default '1' COMMENT '后台订单状态，1为正常订单，0为放入回收站',
  `createtime` int(10) NOT NULL COMMENT '订单生成时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_products`
--


CREATE TABLE IF NOT EXISTS `jx_products` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `uid` int(11) default NULL COMMENT '商品发布者ID',
  `pname` varchar(40) default NULL COMMENT '商品名称',
  `description` text COMMENT '商品描述',
  `price` double(13,2) default NULL COMMENT '商品价格',
  `type` int(1) NOT NULL COMMENT '商品状态，默认为1，即实物',
  `max_number` int(10) default NULL COMMENT '商品可售数量',
  `sale_number` int(10) default NULL COMMENT '商品已售数量',
  `old_price` double(13,2) default NULL COMMENT '商品原价',
  `express_price` double(13,2) default NULL COMMENT '快递费用',
  `end_time` int(10) default NULL COMMENT '售卖截止时间',
  `pageview` int(10) default '0' COMMENT '商品浏览数',
  `status` int(1) default NULL COMMENT '商品状态，1为正常发布，2为保存为草稿，0为放入回收站',
  `createtime` int(10) NOT NULL COMMENT '商品添加时间',
  `modifytime` int(10) default NULL COMMENT '商品编辑时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- 表的结构 `jx_products_image`
--

CREATE TABLE IF NOT EXISTS `jx_products_image` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '商品ID',
  `image` varchar(128) NOT NULL COMMENT '商品图片URL',
  `type` int(1) default NULL COMMENT '图片类型，为1时即显示的大图，列表页也显示该图片',
  `createtime` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_products_property`
--

CREATE TABLE IF NOT EXISTS `jx_products_property` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '商品ID',
  `name` varchar(20) NOT NULL COMMENT '属性名称',
  `content` text NOT NULL COMMENT '属性内容，以序列化格式保存属性内容',
  `createtime` int(10) NOT NULL COMMENT '添加时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_users`
--

CREATE TABLE IF NOT EXISTS `jx_users` (
  `id` int(11) NOT NULL auto_increment COMMENT '主键',
  `email` varchar(60) default NULL COMMENT '邮箱',
  `mobile` varchar(12) default NULL COMMENT '手机号码',
  `password` varchar(40) NOT NULL COMMENT '密码',
  `headerurl` varchar(128) default NULL COMMENT '个人头像URL',
  `nickname` varchar(40) default NULL COMMENT '昵称',
  `website` varchar(128) default NULL COMMENT '个人网站',
  `description` varchar(160) default NULL COMMENT '个人简介',
  `qq_openid` varchar(32) default NULL COMMENT 'QQ的openid',
  `weibo_id` int(11) default NULL COMMENT '新浪微博ID',
  `qzone` varchar(20) default NULL COMMENT 'QQ空间的昵称',
  `sina` varchar(50) default NULL COMMENT '新浪微博的昵称',
  `status` int(1) default NULL COMMENT '用户状态，默认为1,0为禁止登陆',
  `type` int(1) NOT NULL COMMENT '判断用户是用邮箱注册还是手机号注册，1为邮箱注册，2为手机号注册',
  `createtime` int(10) NOT NULL COMMENT '注册时间',
  `updatetime` int(10) default NULL COMMENT '修改时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_feedbacks`
--

CREATE TABLE IF NOT EXISTS `jx_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `email` varchar(40) DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(12) DEFAULT NULL COMMENT '手机号码',
  `name` varchar(40) DEFAULT NULL COMMENT '姓名',
  `content` text NOT NULL COMMENT '内容',
  `reply` text COMMENT '回复的内容',
  `createtime` int(10) NOT NULL COMMENT '添加时间',
  `replytime` int(10) DEFAULT NULL COMMENT '回复时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='意见反馈表' AUTO_INCREMENT=1 ;


--
-- 表的结构 `jx_smscodes`
--

CREATE TABLE IF NOT EXISTS `jx_smscodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(12) NOT NULL COMMENT '手机号码',
  `code` int(4) NOT NULL COMMENT '短信验证码',
  `content` varchar(128) NOT NULL COMMENT '短信内容',
  `ret` varchar(2) NOT NULL,
  `mid` int(2) NOT NULL,
  `cpmid` int(10) NOT NULL,
  `type` varchar(12) NOT NULL COMMENT '判断短信发送的类型，register为注册，repass为忘记密码时发送',
  `addtime` int(10) NOT NULL COMMENT '发送验证码时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='短信验证码表' AUTO_INCREMENT=1 ;



--
-- 表的结构 `jx_smslogs`
--

CREATE TABLE IF NOT EXISTS `jx_smslogs` (
  `id` int(11) NOT NULL auto_increment,
  `mid` varchar(50) NOT NULL default '0',
  `cpmid` varchar(50) NOT NULL,
  `port` varchar(10) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `msg` varchar(512) NOT NULL,
  `area` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `type` char(1) NOT NULL,
  `channel` char(1) NOT NULL,
  `reserve` varchar(1024) NOT NULL,
  `created` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
