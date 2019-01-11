--
-- ��Ľṹ `jx_address`
--

CREATE TABLE IF NOT EXISTS `jx_address` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `uid` int(11) NOT NULL COMMENT '�û�ID',
  `name` varchar(40) NOT NULL COMMENT '�ջ�������',
  `mobile` varchar(11) default NULL COMMENT '�ֻ�����',
  `phone` varchar(20) default NULL COMMENT '�̶��绰',
  `province_id` int(5) NOT NULL COMMENT 'ʡ��ID',
  `city_id` int(5) NOT NULL COMMENT '����ID',
  `area_id` int(5) NOT NULL COMMENT '����ID',
  `street` varchar(130) default NULL COMMENT '�ֵ���',
  `postcode` int(6) default NULL COMMENT '�ʱ�',
  `default_type` int(1) default NULL COMMENT 'Ĭ���ջ���ַ����Ϊ1ʱΪĬ���ջ���ַ�����ֶν�����ͬһ�ջ��˾��ж���ջ���ַʱ',
  `createtime` int(10) NOT NULL COMMENT '��ַ���ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_orders`
--

CREATE TABLE IF NOT EXISTS `jx_orders` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `pay_id` varchar(20) NOT NULL COMMENT '�������',
  `uid` int(11) NOT NULL COMMENT '������ID',
  `sid` int(11) NOT NULL COMMENT '����ID',
  `pid` int(11) NOT NULL COMMENT '��ƷID',
  `quantity` int(11) NOT NULL COMMENT '��Ʒ��������',
  `price` double(10,2) NOT NULL COMMENT '��Ʒ����',
  `property` varchar(60) default NULL COMMENT '��Ʒ����',
  `origin` double(10,2) NOT NULL COMMENT '�����ܼ�',
  `express` enum('y','n') NOT NULL default 'y' COMMENT '�����Ƿ����˷ѣ�yΪ�У�nΪû��',
  `express_name` varchar(20) default NULL COMMENT '��ݹ�˾������',
  `express_price` double(10,2) default NULL COMMENT '��ݵķ���',
  `express_id` varchar(20) default NULL COMMENT '��ݵ���',
  `realname` varchar(40) NOT NULL COMMENT '�ջ�������',
  `mobile` varchar(12) default NULL COMMENT '�ֻ�����',
  `phone` varchar(20) default NULL COMMENT '�̶��绰',
  `province_id` int(5) NOT NULL COMMENT 'ʡ��ID',
  `city_id` int(5) NOT NULL COMMENT '����ID',
  `area_id` int(5) NOT NULL COMMENT '����ID',
  `street` varchar(130) NOT NULL COMMENT '�ֵ�����',
  `postcode` int(6) NOT NULL COMMENT '�ʱ�',
  `remark` text COMMENT '�������',
  `paytype` varchar(20) default NULL COMMENT '֧����ʽ',
  `state` enum('unpay','pay','del') NOT NULL default 'unpay' COMMENT '����״̬��unpayĬ��Ϊδ���payΪ�Ѹ��delΪ��ɾ��',
  `status` int(1) NOT NULL default '1' COMMENT '��̨����״̬��1Ϊ����������0Ϊ�������վ',
  `createtime` int(10) NOT NULL COMMENT '��������ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_products`
--


CREATE TABLE IF NOT EXISTS `jx_products` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `uid` int(11) default NULL COMMENT '��Ʒ������ID',
  `pname` varchar(40) default NULL COMMENT '��Ʒ����',
  `description` text COMMENT '��Ʒ����',
  `price` double(13,2) default NULL COMMENT '��Ʒ�۸�',
  `type` int(1) NOT NULL COMMENT '��Ʒ״̬��Ĭ��Ϊ1����ʵ��',
  `max_number` int(10) default NULL COMMENT '��Ʒ��������',
  `sale_number` int(10) default NULL COMMENT '��Ʒ��������',
  `old_price` double(13,2) default NULL COMMENT '��Ʒԭ��',
  `express_price` double(13,2) default NULL COMMENT '��ݷ���',
  `end_time` int(10) default NULL COMMENT '������ֹʱ��',
  `pageview` int(10) default '0' COMMENT '��Ʒ�����',
  `status` int(1) default NULL COMMENT '��Ʒ״̬��1Ϊ����������2Ϊ����Ϊ�ݸ壬0Ϊ�������վ',
  `createtime` int(10) NOT NULL COMMENT '��Ʒ���ʱ��',
  `modifytime` int(10) default NULL COMMENT '��Ʒ�༭ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- ��Ľṹ `jx_products_image`
--

CREATE TABLE IF NOT EXISTS `jx_products_image` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `pid` int(11) NOT NULL COMMENT '��ƷID',
  `image` varchar(128) NOT NULL COMMENT '��ƷͼƬURL',
  `type` int(1) default NULL COMMENT 'ͼƬ���ͣ�Ϊ1ʱ����ʾ�Ĵ�ͼ���б�ҳҲ��ʾ��ͼƬ',
  `createtime` int(11) NOT NULL COMMENT '���ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_products_property`
--

CREATE TABLE IF NOT EXISTS `jx_products_property` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `pid` int(11) NOT NULL COMMENT '��ƷID',
  `name` varchar(20) NOT NULL COMMENT '��������',
  `content` text NOT NULL COMMENT '�������ݣ������л���ʽ������������',
  `createtime` int(10) NOT NULL COMMENT '���ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_users`
--

CREATE TABLE IF NOT EXISTS `jx_users` (
  `id` int(11) NOT NULL auto_increment COMMENT '����',
  `email` varchar(60) default NULL COMMENT '����',
  `mobile` varchar(12) default NULL COMMENT '�ֻ�����',
  `password` varchar(40) NOT NULL COMMENT '����',
  `headerurl` varchar(128) default NULL COMMENT '����ͷ��URL',
  `nickname` varchar(40) default NULL COMMENT '�ǳ�',
  `website` varchar(128) default NULL COMMENT '������վ',
  `description` varchar(160) default NULL COMMENT '���˼��',
  `qq_openid` varchar(32) default NULL COMMENT 'QQ��openid',
  `weibo_id` int(11) default NULL COMMENT '����΢��ID',
  `qzone` varchar(20) default NULL COMMENT 'QQ�ռ���ǳ�',
  `sina` varchar(50) default NULL COMMENT '����΢�����ǳ�',
  `status` int(1) default NULL COMMENT '�û�״̬��Ĭ��Ϊ1,0Ϊ��ֹ��½',
  `type` int(1) NOT NULL COMMENT '�ж��û���������ע�ỹ���ֻ���ע�ᣬ1Ϊ����ע�ᣬ2Ϊ�ֻ���ע��',
  `createtime` int(10) NOT NULL COMMENT 'ע��ʱ��',
  `updatetime` int(10) default NULL COMMENT '�޸�ʱ��',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_feedbacks`
--

CREATE TABLE IF NOT EXISTS `jx_feedbacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '����',
  `email` varchar(40) DEFAULT NULL COMMENT '����',
  `mobile` varchar(12) DEFAULT NULL COMMENT '�ֻ�����',
  `name` varchar(40) DEFAULT NULL COMMENT '����',
  `content` text NOT NULL COMMENT '����',
  `reply` text COMMENT '�ظ�������',
  `createtime` int(10) NOT NULL COMMENT '���ʱ��',
  `replytime` int(10) DEFAULT NULL COMMENT '�ظ�ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='���������' AUTO_INCREMENT=1 ;


--
-- ��Ľṹ `jx_smscodes`
--

CREATE TABLE IF NOT EXISTS `jx_smscodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(12) NOT NULL COMMENT '�ֻ�����',
  `code` int(4) NOT NULL COMMENT '������֤��',
  `content` varchar(128) NOT NULL COMMENT '��������',
  `ret` varchar(2) NOT NULL,
  `mid` int(2) NOT NULL,
  `cpmid` int(10) NOT NULL,
  `type` varchar(12) NOT NULL COMMENT '�ж϶��ŷ��͵����ͣ�registerΪע�ᣬrepassΪ��������ʱ����',
  `addtime` int(10) NOT NULL COMMENT '������֤��ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='������֤���' AUTO_INCREMENT=1 ;



--
-- ��Ľṹ `jx_smslogs`
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
