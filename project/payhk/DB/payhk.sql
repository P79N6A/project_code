/*
Navicat MySQL Data Transfer

Source Server         : 182.92.80.211
Source Server Version : 50517
Source Host           : 182.92.80.211:3306
Source Database       : payhk

Target Server Type    : MYSQL
Target Server Version : 50517
File Encoding         : 65001

Date: 2019-01-08 11:33:10
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for authbank_cardbin
-- ----------------------------
DROP TABLE IF EXISTS `authbank_cardbin`;
CREATE TABLE `authbank_cardbin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `bank_name` varchar(60) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bank_code` varchar(8) NOT NULL DEFAULT '' COMMENT '银行机构代码',
  `bank_abbr` varchar(8) NOT NULL DEFAULT '' COMMENT '银行简称',
  `card_name` varchar(60) NOT NULL DEFAULT '' COMMENT '卡名称',
  `card_length` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '卡长度',
  `prefix_length` int(2) unsigned NOT NULL DEFAULT '6' COMMENT '比较卡号前面的位数',
  `prefix_value` varchar(10) NOT NULL DEFAULT '' COMMENT '卡前面的数值',
  `card_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '卡类型:0为借记卡，1为贷记卡,2预付费卡，3准贷记卡',
  PRIMARY KEY (`id`),
  KEY `idx_card_length` (`card_length`)
) ENGINE=InnoDB AUTO_INCREMENT=3969 DEFAULT CHARSET=utf8 COMMENT='卡bin表';

-- ----------------------------
-- Records of authbank_cardbin
-- ----------------------------
INSERT INTO `authbank_cardbin` VALUES ('1', '中国邮政储蓄', '1000000', 'POST', '绿卡通', '19', '6', '621098', '0');
INSERT INTO `authbank_cardbin` VALUES ('2', '中国邮政储蓄', '1000000', 'POST', '绿卡银联标准卡', '19', '6', '622150', '0');
INSERT INTO `authbank_cardbin` VALUES ('3', '中国邮政储蓄', '1000000', 'POST', '绿卡银联标准卡', '19', '6', '622151', '0');
INSERT INTO `authbank_cardbin` VALUES ('4', '中国邮政储蓄', '1000000', 'POST', '绿卡专用卡', '19', '6', '622181', '0');
INSERT INTO `authbank_cardbin` VALUES ('5', '中国邮政储蓄', '1000000', 'POST', '绿卡银联标准卡', '19', '6', '622188', '0');
INSERT INTO `authbank_cardbin` VALUES ('6', '中国邮政储蓄', '1000000', 'POST', '绿卡(银联卡)', '19', '6', '955100', '0');
INSERT INTO `authbank_cardbin` VALUES ('7', '中国邮政储蓄', '1000000', 'POST', '绿卡VIP卡', '19', '6', '621095', '0');
INSERT INTO `authbank_cardbin` VALUES ('8', '中国邮政储蓄', '1000000', 'POST', '银联标准卡', '19', '6', '620062', '0');
INSERT INTO `authbank_cardbin` VALUES ('9', '中国邮政储蓄', '1000000', 'POST', '中职学生资助卡', '19', '6', '621285', '0');
INSERT INTO `authbank_cardbin` VALUES ('10', '中国邮政储蓄', '1000000', 'POST', 'IC绿卡通VIP卡', '19', '6', '621798', '0');
INSERT INTO `authbank_cardbin` VALUES ('11', '中国邮政储蓄', '1000000', 'POST', 'IC绿卡通', '19', '6', '621799', '0');
INSERT INTO `authbank_cardbin` VALUES ('12', '中国邮政储蓄', '1000000', 'POST', 'IC联名卡', '19', '6', '621797', '0');
INSERT INTO `authbank_cardbin` VALUES ('13', '中国邮政储蓄', '1000000', 'POST', '绿卡银联标准卡', '19', '6', '622199', '0');
INSERT INTO `authbank_cardbin` VALUES ('14', '中国邮政储蓄', '1000000', 'POST', '绿卡通', '19', '6', '621096', '0');
INSERT INTO `authbank_cardbin` VALUES ('15', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62215049', '0');
INSERT INTO `authbank_cardbin` VALUES ('16', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62215050', '0');
INSERT INTO `authbank_cardbin` VALUES ('17', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62215051', '0');
INSERT INTO `authbank_cardbin` VALUES ('18', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62218850', '0');
INSERT INTO `authbank_cardbin` VALUES ('19', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62218851', '0');
INSERT INTO `authbank_cardbin` VALUES ('20', '中国邮政储蓄', '1004900', 'POST', '绿卡储蓄卡(银联卡)', '19', '8', '62218849', '0');
INSERT INTO `authbank_cardbin` VALUES ('21', '中国邮政储蓄', '1009999', 'POST', '武警军人保障卡', '19', '6', '621622', '0');
INSERT INTO `authbank_cardbin` VALUES ('22', '中国邮政储蓄', '1009999', 'POST', '中国旅游卡（金卡）', '19', '6', '623219', '0');
INSERT INTO `authbank_cardbin` VALUES ('23', '中国邮政储蓄', '1009999', 'POST', '普通高中学生资助卡', '19', '6', '621674', '0');
INSERT INTO `authbank_cardbin` VALUES ('24', '中国邮政储蓄', '1009999', 'POST', '中国旅游卡（普卡）', '19', '6', '623218', '0');
INSERT INTO `authbank_cardbin` VALUES ('25', '中国邮政储蓄', '1009999', 'POST', '福农卡', '19', '6', '621599', '0');
INSERT INTO `authbank_cardbin` VALUES ('26', '中国邮政储蓄', '1009999', 'POST', 'IC预付费卡', '19', '6', '620529', '2');
INSERT INTO `authbank_cardbin` VALUES ('27', '中国邮政储蓄', '1009999', 'POST', '绿卡通IC联名卡', '19', '6', '623686', '0');
INSERT INTO `authbank_cardbin` VALUES ('28', '中国邮政储蓄', '1009999', 'POST', '绿卡通IC卡-白金卡', '19', '6', '623698', '0');
INSERT INTO `authbank_cardbin` VALUES ('29', '中国邮政储蓄', '1009999', 'POST', '绿卡通IC卡-钻石卡', '19', '6', '623699', '0');
INSERT INTO `authbank_cardbin` VALUES ('30', '工商银行', '1020000', 'ICBC', '牡丹运通卡金卡', '15', '6', '370246', '1');
INSERT INTO `authbank_cardbin` VALUES ('31', '工商银行', '1020000', 'ICBC', '牡丹运通卡金卡', '15', '6', '370248', '1');
INSERT INTO `authbank_cardbin` VALUES ('32', '工商银行', '1020000', 'ICBC', '牡丹运通卡金卡', '15', '6', '370249', '1');
INSERT INTO `authbank_cardbin` VALUES ('33', '工商银行', '1020000', 'ICBC', '牡丹VISA卡(单位卡)', '16', '6', '427010', '1');
INSERT INTO `authbank_cardbin` VALUES ('34', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427018', '1');
INSERT INTO `authbank_cardbin` VALUES ('35', '工商银行', '1020000', 'ICBC', '牡丹VISA卡(单位卡)', '16', '6', '427019', '1');
INSERT INTO `authbank_cardbin` VALUES ('36', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427020', '1');
INSERT INTO `authbank_cardbin` VALUES ('37', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427029', '1');
INSERT INTO `authbank_cardbin` VALUES ('38', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427030', '1');
INSERT INTO `authbank_cardbin` VALUES ('39', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427039', '1');
INSERT INTO `authbank_cardbin` VALUES ('40', '工商银行', '1020000', 'ICBC', '牡丹运通卡普通卡', '15', '6', '370247', '1');
INSERT INTO `authbank_cardbin` VALUES ('41', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '438125', '1');
INSERT INTO `authbank_cardbin` VALUES ('42', '工商银行', '1020000', 'ICBC', '牡丹VISA白金卡', '16', '6', '438126', '1');
INSERT INTO `authbank_cardbin` VALUES ('43', '工商银行', '1020000', 'ICBC', '牡丹贷记卡(银联卡)', '16', '6', '451804', '1');
INSERT INTO `authbank_cardbin` VALUES ('44', '工商银行', '1020000', 'ICBC', '牡丹贷记卡(银联卡)', '16', '6', '451810', '1');
INSERT INTO `authbank_cardbin` VALUES ('45', '工商银行', '1020000', 'ICBC', '牡丹贷记卡(银联卡)', '16', '6', '451811', '1');
INSERT INTO `authbank_cardbin` VALUES ('46', '工商银行', '1020000', 'ICBC', '牡丹信用卡(银联卡)', '16', '5', '45806', '1');
INSERT INTO `authbank_cardbin` VALUES ('47', '工商银行', '1020000', 'ICBC', '牡丹贷记卡(银联卡)', '16', '6', '458071', '1');
INSERT INTO `authbank_cardbin` VALUES ('48', '工商银行', '1020000', 'ICBC', '牡丹欧元卡', '16', '6', '489734', '1');
INSERT INTO `authbank_cardbin` VALUES ('49', '工商银行', '1020000', 'ICBC', '牡丹欧元卡', '16', '6', '489735', '1');
INSERT INTO `authbank_cardbin` VALUES ('50', '工商银行', '1020000', 'ICBC', '牡丹欧元卡', '16', '6', '489736', '1');
INSERT INTO `authbank_cardbin` VALUES ('51', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427062', '1');
INSERT INTO `authbank_cardbin` VALUES ('52', '工商银行', '1020000', 'ICBC', '牡丹VISA信用卡', '16', '6', '427064', '1');
INSERT INTO `authbank_cardbin` VALUES ('53', '工商银行', '1020000', 'ICBC', '牡丹万事达信用卡', '16', '6', '530970', '1');
INSERT INTO `authbank_cardbin` VALUES ('54', '工商银行', '1020000', 'ICBC', '牡丹信用卡(银联卡)', '16', '5', '53098', '1');
INSERT INTO `authbank_cardbin` VALUES ('55', '工商银行', '1020000', 'ICBC', '牡丹万事达信用卡', '16', '6', '530990', '1');
INSERT INTO `authbank_cardbin` VALUES ('56', '工商银行', '1020000', 'ICBC', '牡丹万事达信用卡', '16', '6', '558360', '1');
INSERT INTO `authbank_cardbin` VALUES ('57', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620200', '0');
INSERT INTO `authbank_cardbin` VALUES ('58', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620302', '0');
INSERT INTO `authbank_cardbin` VALUES ('59', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620402', '0');
INSERT INTO `authbank_cardbin` VALUES ('60', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620403', '0');
INSERT INTO `authbank_cardbin` VALUES ('61', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620404', '0');
INSERT INTO `authbank_cardbin` VALUES ('62', '工商银行', '1020000', 'ICBC', '牡丹万事达白金卡', '16', '6', '524047', '1');
INSERT INTO `authbank_cardbin` VALUES ('63', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620406', '0');
INSERT INTO `authbank_cardbin` VALUES ('64', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620407', '0');
INSERT INTO `authbank_cardbin` VALUES ('65', '工商银行', '1020000', 'ICBC', '海航信用卡个人普卡', '16', '6', '525498', '1');
INSERT INTO `authbank_cardbin` VALUES ('66', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620409', '0');
INSERT INTO `authbank_cardbin` VALUES ('67', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620410', '0');
INSERT INTO `authbank_cardbin` VALUES ('68', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620411', '0');
INSERT INTO `authbank_cardbin` VALUES ('69', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620412', '0');
INSERT INTO `authbank_cardbin` VALUES ('70', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620502', '0');
INSERT INTO `authbank_cardbin` VALUES ('71', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620503', '0');
INSERT INTO `authbank_cardbin` VALUES ('72', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620405', '0');
INSERT INTO `authbank_cardbin` VALUES ('73', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620408', '0');
INSERT INTO `authbank_cardbin` VALUES ('74', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620512', '0');
INSERT INTO `authbank_cardbin` VALUES ('75', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620602', '0');
INSERT INTO `authbank_cardbin` VALUES ('76', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620604', '0');
INSERT INTO `authbank_cardbin` VALUES ('77', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620607', '0');
INSERT INTO `authbank_cardbin` VALUES ('78', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620611', '0');
INSERT INTO `authbank_cardbin` VALUES ('79', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620612', '0');
INSERT INTO `authbank_cardbin` VALUES ('80', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620704', '0');
INSERT INTO `authbank_cardbin` VALUES ('81', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620706', '0');
INSERT INTO `authbank_cardbin` VALUES ('82', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620707', '0');
INSERT INTO `authbank_cardbin` VALUES ('83', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620708', '0');
INSERT INTO `authbank_cardbin` VALUES ('84', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620709', '0');
INSERT INTO `authbank_cardbin` VALUES ('85', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620710', '0');
INSERT INTO `authbank_cardbin` VALUES ('86', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620609', '0');
INSERT INTO `authbank_cardbin` VALUES ('87', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620712', '0');
INSERT INTO `authbank_cardbin` VALUES ('88', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620713', '0');
INSERT INTO `authbank_cardbin` VALUES ('89', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620714', '0');
INSERT INTO `authbank_cardbin` VALUES ('90', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620802', '0');
INSERT INTO `authbank_cardbin` VALUES ('91', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620711', '0');
INSERT INTO `authbank_cardbin` VALUES ('92', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620904', '0');
INSERT INTO `authbank_cardbin` VALUES ('93', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620905', '0');
INSERT INTO `authbank_cardbin` VALUES ('94', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621001', '0');
INSERT INTO `authbank_cardbin` VALUES ('95', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '620902', '0');
INSERT INTO `authbank_cardbin` VALUES ('96', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621103', '0');
INSERT INTO `authbank_cardbin` VALUES ('97', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621105', '0');
INSERT INTO `authbank_cardbin` VALUES ('98', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621106', '0');
INSERT INTO `authbank_cardbin` VALUES ('99', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621107', '0');
INSERT INTO `authbank_cardbin` VALUES ('100', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621102', '0');
INSERT INTO `authbank_cardbin` VALUES ('101', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621203', '0');
INSERT INTO `authbank_cardbin` VALUES ('102', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621204', '0');
INSERT INTO `authbank_cardbin` VALUES ('103', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621205', '0');
INSERT INTO `authbank_cardbin` VALUES ('104', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621206', '0');
INSERT INTO `authbank_cardbin` VALUES ('105', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621207', '0');
INSERT INTO `authbank_cardbin` VALUES ('106', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621208', '0');
INSERT INTO `authbank_cardbin` VALUES ('107', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621209', '0');
INSERT INTO `authbank_cardbin` VALUES ('108', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621210', '0');
INSERT INTO `authbank_cardbin` VALUES ('109', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621302', '0');
INSERT INTO `authbank_cardbin` VALUES ('110', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621303', '0');
INSERT INTO `authbank_cardbin` VALUES ('111', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621202', '0');
INSERT INTO `authbank_cardbin` VALUES ('112', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621305', '0');
INSERT INTO `authbank_cardbin` VALUES ('113', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621306', '0');
INSERT INTO `authbank_cardbin` VALUES ('114', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621307', '0');
INSERT INTO `authbank_cardbin` VALUES ('115', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621309', '0');
INSERT INTO `authbank_cardbin` VALUES ('116', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621311', '0');
INSERT INTO `authbank_cardbin` VALUES ('117', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621313', '0');
INSERT INTO `authbank_cardbin` VALUES ('118', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621211', '0');
INSERT INTO `authbank_cardbin` VALUES ('119', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621315', '0');
INSERT INTO `authbank_cardbin` VALUES ('120', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621304', '0');
INSERT INTO `authbank_cardbin` VALUES ('121', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621402', '0');
INSERT INTO `authbank_cardbin` VALUES ('122', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621404', '0');
INSERT INTO `authbank_cardbin` VALUES ('123', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621405', '0');
INSERT INTO `authbank_cardbin` VALUES ('124', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621406', '0');
INSERT INTO `authbank_cardbin` VALUES ('125', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621407', '0');
INSERT INTO `authbank_cardbin` VALUES ('126', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621408', '0');
INSERT INTO `authbank_cardbin` VALUES ('127', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621409', '0');
INSERT INTO `authbank_cardbin` VALUES ('128', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621410', '0');
INSERT INTO `authbank_cardbin` VALUES ('129', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621502', '0');
INSERT INTO `authbank_cardbin` VALUES ('130', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621317', '0');
INSERT INTO `authbank_cardbin` VALUES ('131', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621511', '0');
INSERT INTO `authbank_cardbin` VALUES ('132', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621602', '0');
INSERT INTO `authbank_cardbin` VALUES ('133', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621603', '0');
INSERT INTO `authbank_cardbin` VALUES ('134', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621604', '0');
INSERT INTO `authbank_cardbin` VALUES ('135', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621605', '0');
INSERT INTO `authbank_cardbin` VALUES ('136', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621608', '0');
INSERT INTO `authbank_cardbin` VALUES ('137', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621609', '0');
INSERT INTO `authbank_cardbin` VALUES ('138', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621610', '0');
INSERT INTO `authbank_cardbin` VALUES ('139', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621611', '0');
INSERT INTO `authbank_cardbin` VALUES ('140', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621612', '0');
INSERT INTO `authbank_cardbin` VALUES ('141', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621613', '0');
INSERT INTO `authbank_cardbin` VALUES ('142', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621614', '0');
INSERT INTO `authbank_cardbin` VALUES ('143', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621615', '0');
INSERT INTO `authbank_cardbin` VALUES ('144', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621616', '0');
INSERT INTO `authbank_cardbin` VALUES ('145', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621617', '0');
INSERT INTO `authbank_cardbin` VALUES ('146', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621607', '0');
INSERT INTO `authbank_cardbin` VALUES ('147', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621606', '0');
INSERT INTO `authbank_cardbin` VALUES ('148', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621804', '0');
INSERT INTO `authbank_cardbin` VALUES ('149', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621807', '0');
INSERT INTO `authbank_cardbin` VALUES ('150', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621813', '0');
INSERT INTO `authbank_cardbin` VALUES ('151', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621814', '0');
INSERT INTO `authbank_cardbin` VALUES ('152', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621817', '0');
INSERT INTO `authbank_cardbin` VALUES ('153', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621901', '0');
INSERT INTO `authbank_cardbin` VALUES ('154', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621904', '0');
INSERT INTO `authbank_cardbin` VALUES ('155', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621905', '0');
INSERT INTO `authbank_cardbin` VALUES ('156', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621906', '0');
INSERT INTO `authbank_cardbin` VALUES ('157', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621907', '0');
INSERT INTO `authbank_cardbin` VALUES ('158', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621908', '0');
INSERT INTO `authbank_cardbin` VALUES ('159', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621909', '0');
INSERT INTO `authbank_cardbin` VALUES ('160', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621910', '0');
INSERT INTO `authbank_cardbin` VALUES ('161', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621911', '0');
INSERT INTO `authbank_cardbin` VALUES ('162', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621912', '0');
INSERT INTO `authbank_cardbin` VALUES ('163', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621913', '0');
INSERT INTO `authbank_cardbin` VALUES ('164', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621915', '0');
INSERT INTO `authbank_cardbin` VALUES ('165', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622002', '0');
INSERT INTO `authbank_cardbin` VALUES ('166', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621903', '0');
INSERT INTO `authbank_cardbin` VALUES ('167', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622004', '0');
INSERT INTO `authbank_cardbin` VALUES ('168', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622005', '0');
INSERT INTO `authbank_cardbin` VALUES ('169', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622006', '0');
INSERT INTO `authbank_cardbin` VALUES ('170', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622007', '0');
INSERT INTO `authbank_cardbin` VALUES ('171', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622008', '0');
INSERT INTO `authbank_cardbin` VALUES ('172', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622010', '0');
INSERT INTO `authbank_cardbin` VALUES ('173', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622011', '0');
INSERT INTO `authbank_cardbin` VALUES ('174', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622012', '0');
INSERT INTO `authbank_cardbin` VALUES ('175', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '621914', '0');
INSERT INTO `authbank_cardbin` VALUES ('176', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622015', '0');
INSERT INTO `authbank_cardbin` VALUES ('177', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622016', '0');
INSERT INTO `authbank_cardbin` VALUES ('178', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622003', '0');
INSERT INTO `authbank_cardbin` VALUES ('179', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622018', '0');
INSERT INTO `authbank_cardbin` VALUES ('180', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622019', '0');
INSERT INTO `authbank_cardbin` VALUES ('181', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622020', '0');
INSERT INTO `authbank_cardbin` VALUES ('182', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622102', '0');
INSERT INTO `authbank_cardbin` VALUES ('183', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622103', '0');
INSERT INTO `authbank_cardbin` VALUES ('184', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622104', '0');
INSERT INTO `authbank_cardbin` VALUES ('185', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622105', '0');
INSERT INTO `authbank_cardbin` VALUES ('186', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622013', '0');
INSERT INTO `authbank_cardbin` VALUES ('187', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622111', '0');
INSERT INTO `authbank_cardbin` VALUES ('188', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622114', '0');
INSERT INTO `authbank_cardbin` VALUES ('189', '工商银行', '1020000', 'ICBC', '灵通卡', '19', '6', '622200', '0');
INSERT INTO `authbank_cardbin` VALUES ('190', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622017', '0');
INSERT INTO `authbank_cardbin` VALUES ('191', '工商银行', '1020000', 'ICBC', 'E时代卡', '19', '6', '622202', '0');
INSERT INTO `authbank_cardbin` VALUES ('192', '工商银行', '1020000', 'ICBC', 'E时代卡', '19', '6', '622203', '0');
INSERT INTO `authbank_cardbin` VALUES ('193', '工商银行', '1020000', 'ICBC', '理财金卡', '19', '6', '622208', '0');
INSERT INTO `authbank_cardbin` VALUES ('194', '工商银行', '1020000', 'ICBC', '准贷记卡(个普)', '16', '6', '622210', '3');
INSERT INTO `authbank_cardbin` VALUES ('195', '工商银行', '1020000', 'ICBC', '准贷记卡(个普)', '16', '6', '622211', '3');
INSERT INTO `authbank_cardbin` VALUES ('196', '工商银行', '1020000', 'ICBC', '准贷记卡(个普)', '16', '6', '622212', '3');
INSERT INTO `authbank_cardbin` VALUES ('197', '工商银行', '1020000', 'ICBC', '准贷记卡(个普)', '16', '6', '622213', '3');
INSERT INTO `authbank_cardbin` VALUES ('198', '工商银行', '1020000', 'ICBC', '准贷记卡(个普)', '16', '6', '622214', '3');
INSERT INTO `authbank_cardbin` VALUES ('199', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622110', '0');
INSERT INTO `authbank_cardbin` VALUES ('200', '工商银行', '1020000', 'ICBC', '准贷记卡(商普)', '16', '6', '622220', '3');
INSERT INTO `authbank_cardbin` VALUES ('201', '工商银行', '1020000', 'ICBC', '牡丹卡(商务卡)', '16', '6', '622223', '3');
INSERT INTO `authbank_cardbin` VALUES ('202', '工商银行', '1020000', 'ICBC', '准贷记卡(商金)', '16', '6', '622225', '3');
INSERT INTO `authbank_cardbin` VALUES ('203', '工商银行', '1020000', 'ICBC', '牡丹卡(商务卡)', '16', '6', '622229', '3');
INSERT INTO `authbank_cardbin` VALUES ('204', '工商银行', '1020000', 'ICBC', '贷记卡(个普)', '16', '6', '622230', '1');
INSERT INTO `authbank_cardbin` VALUES ('205', '工商银行', '1020000', 'ICBC', '牡丹卡(个人卡)', '16', '6', '622231', '1');
INSERT INTO `authbank_cardbin` VALUES ('206', '工商银行', '1020000', 'ICBC', '牡丹卡(个人卡)', '16', '6', '622232', '1');
INSERT INTO `authbank_cardbin` VALUES ('207', '工商银行', '1020000', 'ICBC', '牡丹卡(个人卡)', '16', '6', '622233', '1');
INSERT INTO `authbank_cardbin` VALUES ('208', '工商银行', '1020000', 'ICBC', '牡丹卡(个人卡)', '16', '6', '622234', '1');
INSERT INTO `authbank_cardbin` VALUES ('209', '工商银行', '1020000', 'ICBC', '贷记卡(个金)', '16', '6', '622235', '1');
INSERT INTO `authbank_cardbin` VALUES ('210', '工商银行', '1020000', 'ICBC', '牡丹交通卡', '16', '6', '622237', '1');
INSERT INTO `authbank_cardbin` VALUES ('211', '工商银行', '1020000', 'ICBC', '准贷记卡(个金)', '16', '6', '622215', '3');
INSERT INTO `authbank_cardbin` VALUES ('212', '工商银行', '1020000', 'ICBC', '牡丹交通卡', '16', '6', '622239', '1');
INSERT INTO `authbank_cardbin` VALUES ('213', '工商银行', '1020000', 'ICBC', '贷记卡(商普)', '16', '6', '622240', '1');
INSERT INTO `authbank_cardbin` VALUES ('214', '工商银行', '1020000', 'ICBC', '贷记卡(商金)', '16', '6', '622245', '1');
INSERT INTO `authbank_cardbin` VALUES ('215', '工商银行', '1020000', 'ICBC', '牡丹卡(商务卡)', '16', '6', '622224', '3');
INSERT INTO `authbank_cardbin` VALUES ('216', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622303', '0');
INSERT INTO `authbank_cardbin` VALUES ('217', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622304', '0');
INSERT INTO `authbank_cardbin` VALUES ('218', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622305', '0');
INSERT INTO `authbank_cardbin` VALUES ('219', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622306', '0');
INSERT INTO `authbank_cardbin` VALUES ('220', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622307', '0');
INSERT INTO `authbank_cardbin` VALUES ('221', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622308', '0');
INSERT INTO `authbank_cardbin` VALUES ('222', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622309', '0');
INSERT INTO `authbank_cardbin` VALUES ('223', '工商银行', '1020000', 'ICBC', '牡丹交通卡', '16', '6', '622238', '1');
INSERT INTO `authbank_cardbin` VALUES ('224', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622314', '0');
INSERT INTO `authbank_cardbin` VALUES ('225', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622315', '0');
INSERT INTO `authbank_cardbin` VALUES ('226', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622317', '0');
INSERT INTO `authbank_cardbin` VALUES ('227', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622302', '0');
INSERT INTO `authbank_cardbin` VALUES ('228', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622402', '0');
INSERT INTO `authbank_cardbin` VALUES ('229', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622403', '0');
INSERT INTO `authbank_cardbin` VALUES ('230', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622404', '0');
INSERT INTO `authbank_cardbin` VALUES ('231', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622313', '0');
INSERT INTO `authbank_cardbin` VALUES ('232', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622504', '0');
INSERT INTO `authbank_cardbin` VALUES ('233', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622505', '0');
INSERT INTO `authbank_cardbin` VALUES ('234', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622509', '0');
INSERT INTO `authbank_cardbin` VALUES ('235', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622513', '0');
INSERT INTO `authbank_cardbin` VALUES ('236', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622517', '0');
INSERT INTO `authbank_cardbin` VALUES ('237', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622502', '0');
INSERT INTO `authbank_cardbin` VALUES ('238', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622604', '0');
INSERT INTO `authbank_cardbin` VALUES ('239', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622605', '0');
INSERT INTO `authbank_cardbin` VALUES ('240', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622606', '0');
INSERT INTO `authbank_cardbin` VALUES ('241', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622510', '0');
INSERT INTO `authbank_cardbin` VALUES ('242', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622703', '0');
INSERT INTO `authbank_cardbin` VALUES ('243', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622715', '0');
INSERT INTO `authbank_cardbin` VALUES ('244', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622806', '0');
INSERT INTO `authbank_cardbin` VALUES ('245', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622902', '0');
INSERT INTO `authbank_cardbin` VALUES ('246', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622903', '0');
INSERT INTO `authbank_cardbin` VALUES ('247', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622706', '0');
INSERT INTO `authbank_cardbin` VALUES ('248', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623002', '0');
INSERT INTO `authbank_cardbin` VALUES ('249', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623006', '0');
INSERT INTO `authbank_cardbin` VALUES ('250', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623008', '0');
INSERT INTO `authbank_cardbin` VALUES ('251', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623011', '0');
INSERT INTO `authbank_cardbin` VALUES ('252', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623012', '0');
INSERT INTO `authbank_cardbin` VALUES ('253', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '622904', '0');
INSERT INTO `authbank_cardbin` VALUES ('254', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623015', '0');
INSERT INTO `authbank_cardbin` VALUES ('255', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623100', '0');
INSERT INTO `authbank_cardbin` VALUES ('256', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623202', '0');
INSERT INTO `authbank_cardbin` VALUES ('257', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623301', '0');
INSERT INTO `authbank_cardbin` VALUES ('258', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623400', '0');
INSERT INTO `authbank_cardbin` VALUES ('259', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623500', '0');
INSERT INTO `authbank_cardbin` VALUES ('260', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623602', '0');
INSERT INTO `authbank_cardbin` VALUES ('261', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623803', '0');
INSERT INTO `authbank_cardbin` VALUES ('262', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623901', '0');
INSERT INTO `authbank_cardbin` VALUES ('263', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623014', '0');
INSERT INTO `authbank_cardbin` VALUES ('264', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '624100', '0');
INSERT INTO `authbank_cardbin` VALUES ('265', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '624200', '0');
INSERT INTO `authbank_cardbin` VALUES ('266', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '624301', '0');
INSERT INTO `authbank_cardbin` VALUES ('267', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '624402', '0');
INSERT INTO `authbank_cardbin` VALUES ('268', '工商银行', '1020000', 'ICBC', '牡丹贷记卡', '16', '8', '62451804', '1');
INSERT INTO `authbank_cardbin` VALUES ('269', '工商银行', '1020000', 'ICBC', '牡丹贷记卡', '16', '8', '62451810', '1');
INSERT INTO `authbank_cardbin` VALUES ('270', '工商银行', '1020000', 'ICBC', '牡丹贷记卡', '16', '8', '62451811', '1');
INSERT INTO `authbank_cardbin` VALUES ('271', '工商银行', '1020000', 'ICBC', '牡丹信用卡', '16', '7', '6245806', '1');
INSERT INTO `authbank_cardbin` VALUES ('272', '工商银行', '1020000', 'ICBC', '牡丹贷记卡', '16', '8', '62458071', '1');
INSERT INTO `authbank_cardbin` VALUES ('273', '工商银行', '1020000', 'ICBC', '牡丹信用卡', '16', '7', '6253098', '1');
INSERT INTO `authbank_cardbin` VALUES ('274', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '623700', '0');
INSERT INTO `authbank_cardbin` VALUES ('275', '工商银行', '1020000', 'ICBC', '中央预算单位公务卡', '16', '6', '628288', '1');
INSERT INTO `authbank_cardbin` VALUES ('276', '工商银行', '1020000', 'ICBC', '牡丹灵通卡', '18', '6', '624000', '0');
INSERT INTO `authbank_cardbin` VALUES ('277', '工商银行', '1020000', 'ICBC', '牡丹灵通卡(银联卡)', '19', '4', '9558', '0');
INSERT INTO `authbank_cardbin` VALUES ('278', '工商银行', '1020000', 'ICBC', '财政预算单位公务卡', '16', '6', '628286', '1');
INSERT INTO `authbank_cardbin` VALUES ('279', '工商银行', '1020000', 'ICBC', '牡丹卡白金卡', '16', '6', '622206', '1');
INSERT INTO `authbank_cardbin` VALUES ('280', '工商银行', '1020000', 'ICBC', '牡丹卡普卡', '19', '6', '621225', '0');
INSERT INTO `authbank_cardbin` VALUES ('281', '工商银行', '1020000', 'ICBC', '国航知音牡丹信用卡', '16', '6', '526836', '1');
INSERT INTO `authbank_cardbin` VALUES ('282', '工商银行', '1020000', 'ICBC', '国航知音牡丹信用卡', '16', '6', '513685', '1');
INSERT INTO `authbank_cardbin` VALUES ('283', '工商银行', '1020000', 'ICBC', '国航知音牡丹信用卡', '16', '6', '543098', '1');
INSERT INTO `authbank_cardbin` VALUES ('284', '工商银行', '1020000', 'ICBC', '国航知音牡丹信用卡', '16', '6', '458441', '1');
INSERT INTO `authbank_cardbin` VALUES ('285', '工商银行', '1020000', 'ICBC', '银联标准卡', '19', '6', '620058', '0');
INSERT INTO `authbank_cardbin` VALUES ('286', '工商银行', '1020000', 'ICBC', '中职学生资助卡', '19', '6', '621281', '0');
INSERT INTO `authbank_cardbin` VALUES ('287', '工商银行', '1020000', 'ICBC', '专用信用消费卡', '16', '6', '622246', '1');
INSERT INTO `authbank_cardbin` VALUES ('288', '工商银行', '1020000', 'ICBC', '牡丹社会保障卡', '19', '6', '900000', '0');
INSERT INTO `authbank_cardbin` VALUES ('289', '中国工商银行', '1020000', 'ICBC', '牡丹东航联名卡', '16', '6', '544210', '1');
INSERT INTO `authbank_cardbin` VALUES ('290', '中国工商银行', '1020000', 'ICBC', '牡丹东航联名卡', '16', '6', '548943', '1');
INSERT INTO `authbank_cardbin` VALUES ('291', '中国工商银行', '1020000', 'ICBC', '牡丹运通白金卡', '15', '6', '370267', '1');
INSERT INTO `authbank_cardbin` VALUES ('292', '中国工商银行', '1020000', 'ICBC', '福农灵通卡', '19', '6', '621558', '0');
INSERT INTO `authbank_cardbin` VALUES ('293', '中国工商银行', '1020000', 'ICBC', '福农灵通卡', '19', '6', '621559', '0');
INSERT INTO `authbank_cardbin` VALUES ('294', '工商银行', '1020000', 'ICBC', '灵通卡', '19', '6', '621722', '0');
INSERT INTO `authbank_cardbin` VALUES ('295', '工商银行', '1020000', 'ICBC', '灵通卡', '19', '6', '621723', '0');
INSERT INTO `authbank_cardbin` VALUES ('296', '中国工商银行', '1020000', 'ICBC', '中国旅行卡', '19', '6', '620086', '0');
INSERT INTO `authbank_cardbin` VALUES ('297', '工商银行', '1020000', 'ICBC', '牡丹卡普卡', '19', '6', '621226', '0');
INSERT INTO `authbank_cardbin` VALUES ('298', '工商银行', '1020000', 'ICBC', '国际借记卡', '16', '6', '402791', '0');
INSERT INTO `authbank_cardbin` VALUES ('299', '工商银行', '1020000', 'ICBC', '国际借记卡', '16', '6', '427028', '0');
INSERT INTO `authbank_cardbin` VALUES ('300', '工商银行', '1020000', 'ICBC', '国际借记卡', '16', '6', '427038', '0');
INSERT INTO `authbank_cardbin` VALUES ('301', '工商银行', '1020000', 'ICBC', '国际借记卡', '16', '6', '548259', '0');
INSERT INTO `authbank_cardbin` VALUES ('302', '中国工商银行', '1020000', 'ICBC', '牡丹JCB信用卡', '16', '6', '356879', '1');
INSERT INTO `authbank_cardbin` VALUES ('303', '中国工商银行', '1020000', 'ICBC', '牡丹JCB信用卡', '16', '6', '356880', '1');
INSERT INTO `authbank_cardbin` VALUES ('304', '中国工商银行', '1020000', 'ICBC', '牡丹JCB信用卡', '16', '6', '356881', '1');
INSERT INTO `authbank_cardbin` VALUES ('305', '中国工商银行', '1020000', 'ICBC', '牡丹JCB信用卡', '16', '6', '356882', '1');
INSERT INTO `authbank_cardbin` VALUES ('306', '中国工商银行', '1020000', 'ICBC', '牡丹多币种卡', '16', '6', '528856', '1');
INSERT INTO `authbank_cardbin` VALUES ('307', '中国工商银行', '1020000', 'ICBC', '武警军人保障卡', '19', '6', '621618', '0');
INSERT INTO `authbank_cardbin` VALUES ('308', '工商银行', '1020000', 'ICBC', '预付芯片卡', '19', '6', '620516', '0');
INSERT INTO `authbank_cardbin` VALUES ('309', '工商银行', '1020000', 'ICBC', '理财金账户金卡', '19', '6', '621227', '0');
INSERT INTO `authbank_cardbin` VALUES ('310', '工商银行', '1020000', 'ICBC', '灵通卡', '19', '6', '621721', '0');
INSERT INTO `authbank_cardbin` VALUES ('311', '工商银行', '1020000', 'ICBC', '牡丹宁波市民卡', '19', '6', '900010', '0');
INSERT INTO `authbank_cardbin` VALUES ('312', '中国工商银行', '1020000', 'ICBC', '中国旅游卡', '16', '6', '625330', '1');
INSERT INTO `authbank_cardbin` VALUES ('313', '中国工商银行', '1020000', 'ICBC', '中国旅游卡', '16', '6', '625331', '1');
INSERT INTO `authbank_cardbin` VALUES ('314', '中国工商银行', '1020000', 'ICBC', '中国旅游卡', '16', '6', '625332', '1');
INSERT INTO `authbank_cardbin` VALUES ('315', '中国工商银行', '1020000', 'ICBC', '借记卡', '19', '6', '623062', '0');
INSERT INTO `authbank_cardbin` VALUES ('316', '中国工商银行', '1020000', 'ICBC', '借贷合一卡', '16', '6', '622236', '1');
INSERT INTO `authbank_cardbin` VALUES ('317', '中国工商银行', '1020000', 'ICBC', '普通高中学生资助卡', '19', '6', '621670', '0');
INSERT INTO `authbank_cardbin` VALUES ('318', '中国工商银行', '1020000', 'ICBC', '牡丹多币种卡', '16', '6', '524374', '1');
INSERT INTO `authbank_cardbin` VALUES ('319', '中国工商银行', '1020000', 'ICBC', '牡丹多币种卡', '16', '6', '550213', '1');
INSERT INTO `authbank_cardbin` VALUES ('320', '工商银行', '1020000', 'ICBC', '工银财富卡', '19', '6', '621288', '0');
INSERT INTO `authbank_cardbin` VALUES ('321', '中国工商银行', '1020000', 'ICBC', '中小商户采购卡', '16', '6', '625708', '1');
INSERT INTO `authbank_cardbin` VALUES ('322', '中国工商银行', '1020000', 'ICBC', '中小商户采购卡', '16', '6', '625709', '1');
INSERT INTO `authbank_cardbin` VALUES ('323', '中国工商银行', '1020000', 'ICBC', '环球旅行金卡', '16', '6', '622597', '1');
INSERT INTO `authbank_cardbin` VALUES ('324', '中国工商银行', '1020000', 'ICBC', '环球旅行白金卡', '16', '6', '622599', '1');
INSERT INTO `authbank_cardbin` VALUES ('325', '中国工商银行', '1020000', 'ICBC', '牡丹工银大来卡', '14', '6', '360883', '1');
INSERT INTO `authbank_cardbin` VALUES ('326', '中国工商银行', '1020000', 'ICBC', '牡丹工银大莱卡', '14', '6', '360884', '1');
INSERT INTO `authbank_cardbin` VALUES ('327', '中国工商银行', '1020000', 'ICBC', 'IC金卡', '16', '6', '625865', '1');
INSERT INTO `authbank_cardbin` VALUES ('328', '中国工商银行', '1020000', 'ICBC', 'IC白金卡', '16', '6', '625866', '1');
INSERT INTO `authbank_cardbin` VALUES ('329', '中国工商银行', '1020000', 'ICBC', '工行IC卡（红卡）', '16', '6', '625899', '1');
INSERT INTO `authbank_cardbin` VALUES ('330', '中国工商银行', '1020000', 'ICBC', '牡丹百夫长信用卡', '15', '6', '374738', '1');
INSERT INTO `authbank_cardbin` VALUES ('331', '中国工商银行', '1020000', 'ICBC', '牡丹百夫长信用卡', '15', '6', '374739', '1');
INSERT INTO `authbank_cardbin` VALUES ('332', '工商银行', '1020000', 'ICBC', '牡丹万事达国际借记卡', '16', '6', '510529', '1');
INSERT INTO `authbank_cardbin` VALUES ('333', '工商银行', '1020000', 'ICBC', '海航信用卡', '16', '6', '524091', '1');
INSERT INTO `authbank_cardbin` VALUES ('334', '中国工商银行', '1020000', 'ICBC', 'YL企业公司卡信用IC金卡', '16', '6', '625650', '1');
INSERT INTO `authbank_cardbin` VALUES ('335', '中国工商银行', '1020000', 'ICBC', '工银环球旅行信用卡', '16', '6', '622910', '1');
INSERT INTO `authbank_cardbin` VALUES ('336', '中国工商银行', '1020000', 'ICBC', '银联金卡', '16', '6', '622911', '1');
INSERT INTO `authbank_cardbin` VALUES ('337', '中国工商银行', '1020000', 'ICBC', '银联白金卡', '16', '6', '622912', '1');
INSERT INTO `authbank_cardbin` VALUES ('338', '中国工商银行', '1020000', 'ICBC', '银联普卡', '16', '6', '622913', '1');
INSERT INTO `authbank_cardbin` VALUES ('339', '中国工商银行', '1020000', 'ICBC', '--', '16', '6', '625160', '1');
INSERT INTO `authbank_cardbin` VALUES ('340', '中国工商银行', '1020000', 'ICBC', '--', '16', '6', '625161', '1');
INSERT INTO `authbank_cardbin` VALUES ('341', '中国工商银行', '1020000', 'ICBC', '--', '16', '6', '625162', '1');
INSERT INTO `authbank_cardbin` VALUES ('342', '中国工商银行', '1020000', 'ICBC', '工银I运动信用卡银联白金卡', '16', '6', '625858', '1');
INSERT INTO `authbank_cardbin` VALUES ('343', '中国工商银行', '1020000', 'ICBC', '工银I运动信用卡银联金卡', '16', '6', '625859', '1');
INSERT INTO `authbank_cardbin` VALUES ('344', '中国工商银行', '1020000', 'ICBC', '工银I运动信用卡银联普卡', '16', '6', '625860', '1');
INSERT INTO `authbank_cardbin` VALUES ('345', '工行布鲁塞尔', '1020056', '', '贷记卡', '16', '6', '625929', '1');
INSERT INTO `authbank_cardbin` VALUES ('346', '中国工商银行布鲁塞尔分行', '1020056', 'ICBC', '借记卡', '16', '6', '621376', '0');
INSERT INTO `authbank_cardbin` VALUES ('347', '中国工商银行布鲁塞尔分行', '1020056', 'ICBC', '预付卡', '16', '6', '620054', '2');
INSERT INTO `authbank_cardbin` VALUES ('348', '中国工商银行布鲁塞尔分行', '1020056', 'ICBC', '预付卡', '16', '6', '620142', '2');
INSERT INTO `authbank_cardbin` VALUES ('349', '中国工商银行（巴西）', '1020076', 'ICBC', '借记卡', '16', '6', '621423', '0');
INSERT INTO `authbank_cardbin` VALUES ('350', '中国工商银行（巴西）', '1020076', 'ICBC', '贷记卡', '16', '6', '625927', '1');
INSERT INTO `authbank_cardbin` VALUES ('351', '中国工商银行金边分行', '1020116', 'ICBC', '借记卡', '16', '6', '621428', '0');
INSERT INTO `authbank_cardbin` VALUES ('352', '中国工商银行金边分行', '1020116', 'ICBC', '信用卡', '16', '6', '625939', '1');
INSERT INTO `authbank_cardbin` VALUES ('353', '中国工商银行金边分行', '1020116', 'ICBC', '借记卡', '16', '6', '621434', '0');
INSERT INTO `authbank_cardbin` VALUES ('354', '中国工商银行金边分行', '1020116', 'ICBC', '信用卡', '16', '6', '625987', '1');
INSERT INTO `authbank_cardbin` VALUES ('355', '中国工商银行加拿大分行', '1020124', 'ICBC', '借记卡', '16', '6', '621761', '0');
INSERT INTO `authbank_cardbin` VALUES ('356', '中国工商银行加拿大分行', '1020124', 'ICBC', '借记卡', '16', '6', '621749', '0');
INSERT INTO `authbank_cardbin` VALUES ('357', '中国工商银行加拿大分行', '1020124', 'ICBC', '预付卡', '16', '6', '620184', '2');
INSERT INTO `authbank_cardbin` VALUES ('358', '工行加拿大', '1020124', '', '贷记卡', '16', '6', '625930', '1');
INSERT INTO `authbank_cardbin` VALUES ('359', '中国工商银行巴黎分行', '1020250', 'ICBC', '借记卡', '16', '6', '621300', '0');
INSERT INTO `authbank_cardbin` VALUES ('360', '中国工商银行巴黎分行', '1020250', 'ICBC', '借记卡', '16', '6', '621378', '0');
INSERT INTO `authbank_cardbin` VALUES ('361', '中国工商银行巴黎分行', '1020250', 'ICBC', '贷记卡', '16', '6', '625114', '1');
INSERT INTO `authbank_cardbin` VALUES ('362', '中国工商银行法兰克福分行', '1020276', 'ICBC', '贷记卡', '16', '6', '622159', '1');
INSERT INTO `authbank_cardbin` VALUES ('363', '中国工商银行法兰克福分行', '1020276', 'ICBC', '借记卡', '19', '6', '621720', '0');
INSERT INTO `authbank_cardbin` VALUES ('364', '中国工商银行法兰克福分行', '1020276', 'ICBC', '贷记卡', '16', '6', '625021', '1');
INSERT INTO `authbank_cardbin` VALUES ('365', '中国工商银行法兰克福分行', '1020276', 'ICBC', '贷记卡', '16', '6', '625022', '1');
INSERT INTO `authbank_cardbin` VALUES ('366', '工银法兰克福', '1020276', 'ICBC', '贷记卡', '16', '6', '625932', '1');
INSERT INTO `authbank_cardbin` VALUES ('367', '中国工商银行法兰克福分行', '1020276', 'ICBC', '借记卡', '19', '6', '621379', '0');
INSERT INTO `authbank_cardbin` VALUES ('368', '中国工商银行法兰克福分行', '1020276', 'ICBC', '预付卡', '19', '6', '620114', '2');
INSERT INTO `authbank_cardbin` VALUES ('369', '中国工商银行法兰克福分行', '1020276', 'ICBC', '预付卡', '19', '6', '620146', '2');
INSERT INTO `authbank_cardbin` VALUES ('370', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', 'ICBC(Asia) Credit', '16', '6', '622889', '1');
INSERT INTO `authbank_cardbin` VALUES ('371', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', 'ICBC Credit Card', '16', '6', '625900', '1');
INSERT INTO `authbank_cardbin` VALUES ('372', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', 'EliteClubATMCard', '16', '6', '622949', '0');
INSERT INTO `authbank_cardbin` VALUES ('373', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '港币信用卡', '16', '6', '625915', '1');
INSERT INTO `authbank_cardbin` VALUES ('374', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '港币信用卡', '16', '6', '625916', '1');
INSERT INTO `authbank_cardbin` VALUES ('375', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '工银亚洲预付卡', '16', '6', '620030', '2');
INSERT INTO `authbank_cardbin` VALUES ('376', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '预付卡', '16', '6', '620050', '2');
INSERT INTO `authbank_cardbin` VALUES ('377', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', 'CNYEasylinkCard', '16', '6', '622944', '0');
INSERT INTO `authbank_cardbin` VALUES ('378', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '工银银联公司卡', '16', '6', '625115', '1');
INSERT INTO `authbank_cardbin` VALUES ('379', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '', '16', '6', '620101', '2');
INSERT INTO `authbank_cardbin` VALUES ('380', '中国工商银行(亚洲)有限公司', '1020344', 'ICBC', '', '16', '6', '623335', '2');
INSERT INTO `authbank_cardbin` VALUES ('381', '中国工商银行(印尼)', '1020360', 'ICBC', '印尼盾复合卡', '16', '6', '622171', '1');
INSERT INTO `authbank_cardbin` VALUES ('382', '中国工商银行(印尼)', '1020360', 'ICBC', '借记卡', '19', '6', '621240', '0');
INSERT INTO `authbank_cardbin` VALUES ('383', '中国工商银行印尼分行', '1020360', 'ICBC', '借记卡', '19', '6', '621724', '0');
INSERT INTO `authbank_cardbin` VALUES ('384', '工银印尼', '1020360', 'ICBC', '贷记卡', '16', '6', '625931', '1');
INSERT INTO `authbank_cardbin` VALUES ('385', '中国工商银行（印度尼西亚）', '1020360', 'ICBC', '借记卡', '19', '6', '621762', '0');
INSERT INTO `authbank_cardbin` VALUES ('386', '中国工商银行印尼分行', '1020360', 'ICBC', '信用卡', '16', '6', '625918', '1');
INSERT INTO `authbank_cardbin` VALUES ('387', '工行米兰', '1020380', '', '贷记卡', '16', '6', '625113', '1');
INSERT INTO `authbank_cardbin` VALUES ('388', '中国工商银行米兰分行', '1020380', 'ICBC', '借记卡', '16', '6', '621371', '0');
INSERT INTO `authbank_cardbin` VALUES ('389', '中国工商银行米兰分行', '1020380', 'ICBC', '预付卡', '16', '6', '620143', '2');
INSERT INTO `authbank_cardbin` VALUES ('390', '中国工商银行米兰分行', '1020380', 'ICBC', '预付卡', '16', '6', '620149', '2');
INSERT INTO `authbank_cardbin` VALUES ('391', '工行东京分行', '1020392', '', '工行东京借记卡', '16', '6', '621730', '0');
INSERT INTO `authbank_cardbin` VALUES ('392', '工行阿拉木图', '1020398', '', '贷记卡', '16', '6', '625928', '1');
INSERT INTO `authbank_cardbin` VALUES ('393', '中国工商银行阿拉木图子行', '1020398', 'ICBC', '借记卡', '19', '6', '621414', '0');
INSERT INTO `authbank_cardbin` VALUES ('394', '中国工商银行阿拉木图子行', '1020398', 'ICBC', '贷记卡', '16', '6', '625914', '1');
INSERT INTO `authbank_cardbin` VALUES ('395', '中国工商银行阿拉木图子行', '1020398', 'ICBC', '借记卡', '19', '6', '621375', '0');
INSERT INTO `authbank_cardbin` VALUES ('396', '中国工商银行阿拉木图子行', '1020398', 'ICBC', '预付卡', '19', '6', '620187', '2');
INSERT INTO `authbank_cardbin` VALUES ('397', '工行首尔', '1020410', '', '借记卡', '16', '6', '621734', '0');
INSERT INTO `authbank_cardbin` VALUES ('398', '中国工商银行万象分行', '1020418', 'ICBC', '借记卡', '16', '6', '621433', '0');
INSERT INTO `authbank_cardbin` VALUES ('399', '中国工商银行万象分行', '1020418', 'ICBC', '贷记卡', '16', '6', '625986', '1');
INSERT INTO `authbank_cardbin` VALUES ('400', '中国工商银行卢森堡分行', '1020442', 'ICBC', '借记卡', '16', '6', '621370', '0');
INSERT INTO `authbank_cardbin` VALUES ('401', '中国工商银行卢森堡分行', '1020442', 'ICBC', '贷记卡', '16', '6', '625925', '1');
INSERT INTO `authbank_cardbin` VALUES ('402', '中国工商银行澳门分行', '1020446', 'ICBC', 'E时代卡', '19', '6', '622926', '0');
INSERT INTO `authbank_cardbin` VALUES ('403', '中国工商银行澳门分行', '1020446', 'ICBC', 'E时代卡', '19', '6', '622927', '0');
INSERT INTO `authbank_cardbin` VALUES ('404', '中国工商银行澳门分行', '1020446', 'ICBC', 'E时代卡', '19', '6', '622928', '0');
INSERT INTO `authbank_cardbin` VALUES ('405', '中国工商银行澳门分行', '1020446', 'ICBC', '理财金账户', '19', '6', '622929', '0');
INSERT INTO `authbank_cardbin` VALUES ('406', '中国工商银行澳门分行', '1020446', 'ICBC', '理财金账户', '19', '6', '622930', '0');
INSERT INTO `authbank_cardbin` VALUES ('407', '中国工商银行澳门分行', '1020446', 'ICBC', '理财金账户', '19', '6', '622931', '0');
INSERT INTO `authbank_cardbin` VALUES ('408', '中国工商银行（澳门）', '1020446', 'ICBC', '借记卡', '19', '6', '621733', '0');
INSERT INTO `authbank_cardbin` VALUES ('409', '中国工商银行（澳门）', '1020446', 'ICBC', '借记卡', '19', '6', '621732', '0');
INSERT INTO `authbank_cardbin` VALUES ('410', '中国工商银行澳门分行', '1020446', 'ICBC', '预付卡', '16', '6', '620124', '2');
INSERT INTO `authbank_cardbin` VALUES ('411', '中国工商银行澳门分行', '1020446', 'ICBC', '预付卡', '16', '6', '620183', '2');
INSERT INTO `authbank_cardbin` VALUES ('412', '中国工商银行澳门分行', '1020446', 'ICBC', '工银闪付预付卡', '16', '6', '620561', '2');
INSERT INTO `authbank_cardbin` VALUES ('413', '中国工商银行澳门分行', '1020446', 'ICBC', '工银银联公司卡', '16', '6', '625116', '1');
INSERT INTO `authbank_cardbin` VALUES ('414', '中国工商银行澳门分行', '1020446', 'ICBC', 'Diamond', '16', '6', '622227', '1');
INSERT INTO `authbank_cardbin` VALUES ('415', '工行马来西亚', '1020458', '', '贷记卡', '16', '6', '625921', '1');
INSERT INTO `authbank_cardbin` VALUES ('416', '工银马来西亚', '1020458', 'ICBC', '借记卡', '16', '6', '621764', '0');
INSERT INTO `authbank_cardbin` VALUES ('417', '工行阿姆斯特丹', '1020528', '', '贷记卡', '16', '6', '625926', '1');
INSERT INTO `authbank_cardbin` VALUES ('418', '中国工商银行阿姆斯特丹', '1020528', 'ICBC', '借记卡', '19', '6', '621372', '0');
INSERT INTO `authbank_cardbin` VALUES ('419', '工银新西兰', '1020554', 'ICBC', '借记卡', '16', '6', '623034', '0');
INSERT INTO `authbank_cardbin` VALUES ('420', '工银新西兰', '1020554', 'ICBC', '信用卡', '16', '6', '625110', '1');
INSERT INTO `authbank_cardbin` VALUES ('421', '中国工商银行卡拉奇分行', '1020586', 'ICBC', '借记卡', '16', '6', '621464', '0');
INSERT INTO `authbank_cardbin` VALUES ('422', '中国工商银行卡拉奇分行', '1020586', 'ICBC', '贷记卡', '16', '6', '625942', '1');
INSERT INTO `authbank_cardbin` VALUES ('423', '中国工商银行新加坡分行', '1020702', 'ICBC', '贷记卡', '16', '6', '622158', '1');
INSERT INTO `authbank_cardbin` VALUES ('424', '中国工商银行新加坡分行', '1020702', 'ICBC', '贷记卡', '16', '6', '625917', '1');
INSERT INTO `authbank_cardbin` VALUES ('425', '中国工商银行新加坡分行', '1020702', 'ICBC', '借记卡', '16', '6', '621765', '0');
INSERT INTO `authbank_cardbin` VALUES ('426', '中国工商银行新加坡分行', '1020702', 'ICBC', '预付卡', '16', '6', '620094', '2');
INSERT INTO `authbank_cardbin` VALUES ('427', '中国工商银行新加坡分行', '1020702', 'ICBC', '预付卡', '16', '6', '620186', '2');
INSERT INTO `authbank_cardbin` VALUES ('428', '中国工商银行新加坡分行', '1020702', 'ICBC', '借记卡', '16', '6', '621719', '0');
INSERT INTO `authbank_cardbin` VALUES ('429', '中国工商银行新加坡分行', '1020702', 'ICBC', '借记卡', '19', '6', '621719', '0');
INSERT INTO `authbank_cardbin` VALUES ('430', '工行河内', '1020704', '', '贷记卡', '16', '6', '625922', '1');
INSERT INTO `authbank_cardbin` VALUES ('431', '工银河内', '1020704', 'ICBC', '借记卡', '19', '6', '621369', '0');
INSERT INTO `authbank_cardbin` VALUES ('432', '工银河内', '1020704', 'ICBC', '工银越南盾借记卡', '19', '6', '621763', '0');
INSERT INTO `authbank_cardbin` VALUES ('433', '工银河内', '1020704', 'ICBC', '工银越南盾信用卡', '16', '6', '625934', '1');
INSERT INTO `authbank_cardbin` VALUES ('434', '工银河内', '1020704', 'ICBC', '预付卡', '19', '6', '620046', '2');
INSERT INTO `authbank_cardbin` VALUES ('435', '中国工商银行马德里分行', '1020724', 'ICBC', '借记卡', '16', '6', '621750', '0');
INSERT INTO `authbank_cardbin` VALUES ('436', '工行马德里', '1020724', '', '贷记卡', '16', '6', '625933', '1');
INSERT INTO `authbank_cardbin` VALUES ('437', '中国工商银行马德里分行', '1020724', 'ICBC', '借记卡', '16', '6', '621377', '0');
INSERT INTO `authbank_cardbin` VALUES ('438', '中国工商银行马德里分行', '1020724', 'ICBC', '预付卡', '16', '6', '620148', '2');
INSERT INTO `authbank_cardbin` VALUES ('439', '中国工商银行马德里分行', '1020724', 'ICBC', '预付卡', '16', '6', '620185', '2');
INSERT INTO `authbank_cardbin` VALUES ('440', '工银泰国', '1020764', 'ICBC', '贷记卡', '16', '6', '625920', '1');
INSERT INTO `authbank_cardbin` VALUES ('441', '工银泰国', '1020764', 'ICBC', '借记卡', '16', '6', '621367', '0');
INSERT INTO `authbank_cardbin` VALUES ('442', '工行伦敦', '1020826', '', '贷记卡', '16', '6', '625924', '1');
INSERT INTO `authbank_cardbin` VALUES ('443', '中国工商银行伦敦子行', '1020826', 'ICBC', '借记卡', '16', '6', '621374', '0');
INSERT INTO `authbank_cardbin` VALUES ('444', '中国工商银行伦敦子行', '1020826', 'ICBC', '工银伦敦借记卡', '16', '6', '621731', '0');
INSERT INTO `authbank_cardbin` VALUES ('445', '中国工商银行伦敦子行', '1020826', 'ICBC', '借记卡', '16', '6', '621781', '0');
INSERT INTO `authbank_cardbin` VALUES ('446', '农业银行', '1030000', 'ABC', '金穗借记卡', '19', '3', '103', '0');
INSERT INTO `authbank_cardbin` VALUES ('447', '农业银行', '1030000', 'ABC', '金穗贷记卡', '16', '6', '552599', '1');
INSERT INTO `authbank_cardbin` VALUES ('448', '农业银行', '1030000', 'ABC', '金穗信用卡', '16', '7', '6349102', '3');
INSERT INTO `authbank_cardbin` VALUES ('449', '农业银行', '1030000', 'ABC', '金穗信用卡', '16', '7', '6353591', '3');
INSERT INTO `authbank_cardbin` VALUES ('450', '农业银行', '1030000', 'ABC', '中国旅游卡', '19', '6', '623206', '0');
INSERT INTO `authbank_cardbin` VALUES ('451', '农业银行', '1030000', 'ABC', '普通高中学生资助卡', '19', '6', '621671', '0');
INSERT INTO `authbank_cardbin` VALUES ('452', '农业银行', '1030000', 'ABC', '银联标准卡', '19', '6', '620059', '0');
INSERT INTO `authbank_cardbin` VALUES ('453', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '403361', '1');
INSERT INTO `authbank_cardbin` VALUES ('454', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '404117', '1');
INSERT INTO `authbank_cardbin` VALUES ('455', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '404118', '1');
INSERT INTO `authbank_cardbin` VALUES ('456', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '404119', '1');
INSERT INTO `authbank_cardbin` VALUES ('457', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '404120', '1');
INSERT INTO `authbank_cardbin` VALUES ('458', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '404121', '1');
INSERT INTO `authbank_cardbin` VALUES ('459', '农业银行', '1030000', 'ABC', 'VISA白金卡', '16', '6', '463758', '1');
INSERT INTO `authbank_cardbin` VALUES ('460', '农业银行', '1030000', 'ABC', '金穗信用卡(银联卡)', '16', '5', '49102', '3');
INSERT INTO `authbank_cardbin` VALUES ('461', '农业银行', '1030000', 'ABC', '万事达白金卡', '16', '6', '514027', '1');
INSERT INTO `authbank_cardbin` VALUES ('462', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '519412', '1');
INSERT INTO `authbank_cardbin` VALUES ('463', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '519413', '1');
INSERT INTO `authbank_cardbin` VALUES ('464', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '520082', '1');
INSERT INTO `authbank_cardbin` VALUES ('465', '农业银行', '1030000', 'ABC', '金穗贷记卡(银联卡)', '16', '6', '520083', '1');
INSERT INTO `authbank_cardbin` VALUES ('466', '农业银行', '1030000', 'ABC', '金穗信用卡(银联卡)', '16', '5', '53591', '3');
INSERT INTO `authbank_cardbin` VALUES ('467', '农业银行', '1030000', 'ABC', '金穗贷记卡', '16', '6', '558730', '1');
INSERT INTO `authbank_cardbin` VALUES ('468', '农业银行', '1030000', 'ABC', '中职学生资助卡', '19', '6', '621282', '0');
INSERT INTO `authbank_cardbin` VALUES ('469', '农业银行', '1030000', 'ABC', '专用惠农卡', '19', '6', '621336', '0');
INSERT INTO `authbank_cardbin` VALUES ('470', '农业银行', '1030000', 'ABC', '武警军人保障卡', '19', '6', '621619', '0');
INSERT INTO `authbank_cardbin` VALUES ('471', '农业银行', '1030000', 'ABC', '金穗校园卡(银联卡)', '19', '6', '622821', '0');
INSERT INTO `authbank_cardbin` VALUES ('472', '农业银行', '1030000', 'ABC', '金穗星座卡(银联卡)', '19', '6', '622822', '0');
INSERT INTO `authbank_cardbin` VALUES ('473', '农业银行', '1030000', 'ABC', '金穗社保卡(银联卡)', '19', '6', '622823', '0');
INSERT INTO `authbank_cardbin` VALUES ('474', '农业银行', '1030000', 'ABC', '金穗旅游卡(银联卡)', '19', '6', '622824', '0');
INSERT INTO `authbank_cardbin` VALUES ('475', '农业银行', '1030000', 'ABC', '金穗青年卡(银联卡)', '19', '6', '622825', '0');
INSERT INTO `authbank_cardbin` VALUES ('476', '农业银行', '1030000', 'ABC', '复合介质金穗通宝卡', '19', '6', '622826', '0');
INSERT INTO `authbank_cardbin` VALUES ('477', '农业银行', '1030000', 'ABC', '金穗海通卡', '19', '6', '622827', '0');
INSERT INTO `authbank_cardbin` VALUES ('478', '农业银行', '1030000', 'ABC', '退役金卡', '19', '6', '622828', '0');
INSERT INTO `authbank_cardbin` VALUES ('479', '农业银行', '1030000', 'ABC', '金穗贷记卡', '16', '6', '622836', '1');
INSERT INTO `authbank_cardbin` VALUES ('480', '农业银行', '1030000', 'ABC', '金穗贷记卡', '16', '6', '622837', '1');
INSERT INTO `authbank_cardbin` VALUES ('481', '农业银行', '1030000', 'ABC', '金穗通宝卡(银联卡)', '19', '6', '622840', '0');
INSERT INTO `authbank_cardbin` VALUES ('482', '农业银行', '1030000', 'ABC', '金穗惠农卡', '19', '6', '622841', '0');
INSERT INTO `authbank_cardbin` VALUES ('483', '农业银行', '1030000', 'ABC', '金穗通宝银卡', '19', '6', '622843', '0');
INSERT INTO `authbank_cardbin` VALUES ('484', '农业银行', '1030000', 'ABC', '金穗通宝卡(银联卡)', '19', '6', '622844', '0');
INSERT INTO `authbank_cardbin` VALUES ('485', '农业银行', '1030000', 'ABC', '金穗通宝卡(银联卡)', '19', '6', '622845', '0');
INSERT INTO `authbank_cardbin` VALUES ('486', '农业银行', '1030000', 'ABC', '金穗通宝卡', '19', '6', '622846', '0');
INSERT INTO `authbank_cardbin` VALUES ('487', '农业银行', '1030000', 'ABC', '金穗通宝卡(银联卡)', '19', '6', '622847', '0');
INSERT INTO `authbank_cardbin` VALUES ('488', '农业银行', '1030000', 'ABC', '金穗通宝卡(银联卡)', '19', '6', '622848', '0');
INSERT INTO `authbank_cardbin` VALUES ('489', '农业银行', '1030000', 'ABC', '金穗通宝钻石卡', '19', '6', '622849', '0');
INSERT INTO `authbank_cardbin` VALUES ('490', '农业银行', '1030000', 'ABC', '掌尚钱包', '19', '6', '623018', '0');
INSERT INTO `authbank_cardbin` VALUES ('491', '农业银行', '1030000', 'ABC', '银联IC卡金卡', '16', '6', '625996', '1');
INSERT INTO `authbank_cardbin` VALUES ('492', '农业银行', '1030000', 'ABC', '银联预算单位公务卡金卡', '16', '6', '625997', '1');
INSERT INTO `authbank_cardbin` VALUES ('493', '农业银行', '1030000', 'ABC', '银联IC卡白金卡', '16', '6', '625998', '1');
INSERT INTO `authbank_cardbin` VALUES ('494', '农业银行', '1030000', 'ABC', '金穗公务卡', '16', '6', '628268', '1');
INSERT INTO `authbank_cardbin` VALUES ('495', '农业银行', '1030000', 'ABC', '借记卡(银联卡)', '19', '5', '95595', '0');
INSERT INTO `authbank_cardbin` VALUES ('496', '农业银行', '1030000', 'ABC', '借记卡(银联卡)', '19', '5', '95596', '0');
INSERT INTO `authbank_cardbin` VALUES ('497', '农业银行', '1030000', 'ABC', '借记卡(银联卡)', '19', '5', '95597', '0');
INSERT INTO `authbank_cardbin` VALUES ('498', '农业银行', '1030000', 'ABC', '借记卡(银联卡)', '19', '5', '95598', '0');
INSERT INTO `authbank_cardbin` VALUES ('499', '农业银行', '1030000', 'ABC', '借记卡(银联卡)', '19', '5', '95599', '0');
INSERT INTO `authbank_cardbin` VALUES ('500', '中国农业银行贷记卡', '1030001', 'ABC', 'IC普卡', '16', '6', '625826', '1');
INSERT INTO `authbank_cardbin` VALUES ('501', '中国农业银行贷记卡', '1030001', 'ABC', 'IC金卡', '16', '6', '625827', '1');
INSERT INTO `authbank_cardbin` VALUES ('502', '中国农业银行贷记卡', '1030001', 'ABC', '澳元卡', '16', '6', '548478', '1');
INSERT INTO `authbank_cardbin` VALUES ('503', '中国农业银行贷记卡', '1030001', 'ABC', '欧元卡', '16', '6', '544243', '1');
INSERT INTO `authbank_cardbin` VALUES ('504', '中国农业银行贷记卡', '1030001', 'ABC', '金穗通商卡', '16', '6', '622820', '3');
INSERT INTO `authbank_cardbin` VALUES ('505', '中国农业银行贷记卡', '1030001', 'ABC', '金穗通商卡', '16', '6', '622830', '3');
INSERT INTO `authbank_cardbin` VALUES ('506', '中国农业银行贷记卡', '1030001', 'ABC', '银联白金卡', '16', '6', '622838', '1');
INSERT INTO `authbank_cardbin` VALUES ('507', '中国农业银行贷记卡', '1030001', 'ABC', '中国旅游卡', '16', '6', '625336', '1');
INSERT INTO `authbank_cardbin` VALUES ('508', '中国农业银行贷记卡', '1030001', 'ABC', '银联IC公务卡', '16', '6', '628269', '1');
INSERT INTO `authbank_cardbin` VALUES ('509', '宁波市农业银行', '1033320', 'ABC', '市民卡B卡', '19', '6', '620501', '0');
INSERT INTO `authbank_cardbin` VALUES ('510', '中国银行', '1040000', 'BOC', '联名卡', '19', '6', '621660', '0');
INSERT INTO `authbank_cardbin` VALUES ('511', '中国银行', '1040000', 'BOC', '个人普卡', '19', '6', '621661', '0');
INSERT INTO `authbank_cardbin` VALUES ('512', '中国银行', '1040000', 'BOC', '个人金卡', '19', '6', '621662', '0');
INSERT INTO `authbank_cardbin` VALUES ('513', '中国银行', '1040000', 'BOC', '员工普卡', '19', '6', '621663', '0');
INSERT INTO `authbank_cardbin` VALUES ('514', '中国银行', '1040000', 'BOC', '员工金卡', '19', '6', '621665', '0');
INSERT INTO `authbank_cardbin` VALUES ('515', '中国银行', '1040000', 'BOC', '理财普卡', '19', '6', '621667', '0');
INSERT INTO `authbank_cardbin` VALUES ('516', '中国银行', '1040000', 'BOC', '理财金卡', '19', '6', '621668', '0');
INSERT INTO `authbank_cardbin` VALUES ('517', '中国银行', '1040000', 'BOC', '理财银卡', '19', '6', '621669', '0');
INSERT INTO `authbank_cardbin` VALUES ('518', '中国银行', '1040000', 'BOC', '理财白金卡', '19', '6', '621666', '0');
INSERT INTO `authbank_cardbin` VALUES ('519', '中国银行', '1040000', 'BOC', '中行金融IC卡白金卡', '16', '6', '625908', '1');
INSERT INTO `authbank_cardbin` VALUES ('520', '中国银行', '1040000', 'BOC', '中行金融IC卡普卡', '16', '6', '625910', '1');
INSERT INTO `authbank_cardbin` VALUES ('521', '中国银行', '1040000', 'BOC', '中行金融IC卡金卡', '16', '6', '625909', '1');
INSERT INTO `authbank_cardbin` VALUES ('522', '中国银行', '1040000', 'BOC', '中银JCB卡金卡', '16', '6', '356833', '1');
INSERT INTO `authbank_cardbin` VALUES ('523', '中国银行', '1040000', 'BOC', '中银JCB卡普卡', '16', '6', '356835', '1');
INSERT INTO `authbank_cardbin` VALUES ('524', '中国银行', '1040000', 'BOC', '员工普卡', '16', '6', '409665', '1');
INSERT INTO `authbank_cardbin` VALUES ('525', '中国银行', '1040000', 'BOC', '个人普卡', '16', '6', '409666', '1');
INSERT INTO `authbank_cardbin` VALUES ('526', '中国银行', '1040000', 'BOC', '中银威士信用卡员', '16', '6', '409668', '1');
INSERT INTO `authbank_cardbin` VALUES ('527', '中国银行', '1040000', 'BOC', '中银威士信用卡员', '16', '6', '409669', '1');
INSERT INTO `authbank_cardbin` VALUES ('528', '中国银行', '1040000', 'BOC', '个人白金卡', '16', '6', '409670', '1');
INSERT INTO `authbank_cardbin` VALUES ('529', '中国银行', '1040000', 'BOC', '中银威士信用卡', '16', '6', '409671', '1');
INSERT INTO `authbank_cardbin` VALUES ('530', '中国银行', '1040000', 'BOC', '长城公务卡', '16', '6', '409672', '1');
INSERT INTO `authbank_cardbin` VALUES ('531', '中国银行', '1040000', 'BOC', '长城电子借记卡', '19', '6', '456351', '0');
INSERT INTO `authbank_cardbin` VALUES ('532', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '512315', '1');
INSERT INTO `authbank_cardbin` VALUES ('533', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '512316', '1');
INSERT INTO `authbank_cardbin` VALUES ('534', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '512411', '1');
INSERT INTO `authbank_cardbin` VALUES ('535', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '512412', '1');
INSERT INTO `authbank_cardbin` VALUES ('536', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '514957', '1');
INSERT INTO `authbank_cardbin` VALUES ('537', '中国银行', '1040000', 'BOC', '中银威士信用卡员', '16', '6', '409667', '1');
INSERT INTO `authbank_cardbin` VALUES ('538', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '518378', '3');
INSERT INTO `authbank_cardbin` VALUES ('539', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '518379', '3');
INSERT INTO `authbank_cardbin` VALUES ('540', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '518474', '3');
INSERT INTO `authbank_cardbin` VALUES ('541', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '518475', '3');
INSERT INTO `authbank_cardbin` VALUES ('542', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '518476', '3');
INSERT INTO `authbank_cardbin` VALUES ('543', '中国银行', '1040000', 'BOC', '中银奥运信用卡', '16', '6', '438088', '1');
INSERT INTO `authbank_cardbin` VALUES ('544', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '524865', '3');
INSERT INTO `authbank_cardbin` VALUES ('545', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '525745', '3');
INSERT INTO `authbank_cardbin` VALUES ('546', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '525746', '3');
INSERT INTO `authbank_cardbin` VALUES ('547', '中国银行', '1040000', 'BOC', '长城万事达信用卡', '16', '6', '547766', '3');
INSERT INTO `authbank_cardbin` VALUES ('548', '中国银行', '1040000', 'BOC', '长城公务卡', '16', '6', '552742', '1');
INSERT INTO `authbank_cardbin` VALUES ('549', '中国银行', '1040000', 'BOC', '长城公务卡', '16', '6', '553131', '1');
INSERT INTO `authbank_cardbin` VALUES ('550', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '558868', '3');
INSERT INTO `authbank_cardbin` VALUES ('551', '中国银行', '1040000', 'BOC', '中银万事达信用卡', '16', '6', '514958', '1');
INSERT INTO `authbank_cardbin` VALUES ('552', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622752', '3');
INSERT INTO `authbank_cardbin` VALUES ('553', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622753', '3');
INSERT INTO `authbank_cardbin` VALUES ('554', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622755', '3');
INSERT INTO `authbank_cardbin` VALUES ('555', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '524864', '3');
INSERT INTO `authbank_cardbin` VALUES ('556', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622757', '3');
INSERT INTO `authbank_cardbin` VALUES ('557', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622758', '3');
INSERT INTO `authbank_cardbin` VALUES ('558', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '622759', '3');
INSERT INTO `authbank_cardbin` VALUES ('559', '中国银行', '1040000', 'BOC', '银联单币贷记卡', '16', '6', '622760', '1');
INSERT INTO `authbank_cardbin` VALUES ('560', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '622761', '3');
INSERT INTO `authbank_cardbin` VALUES ('561', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '622762', '3');
INSERT INTO `authbank_cardbin` VALUES ('562', '中国银行', '1040000', 'BOC', '长城信用卡', '16', '6', '622763', '3');
INSERT INTO `authbank_cardbin` VALUES ('563', '中国银行', '1040000', 'BOC', '长城电子借记卡', '19', '6', '601382', '0');
INSERT INTO `authbank_cardbin` VALUES ('564', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622756', '3');
INSERT INTO `authbank_cardbin` VALUES ('565', '中国银行', '1040000', 'BOC', '银联标准公务卡', '16', '6', '628388', '1');
INSERT INTO `authbank_cardbin` VALUES ('566', '中国银行', '1040000', 'BOC', '一卡双账户普卡', '19', '6', '621256', '0');
INSERT INTO `authbank_cardbin` VALUES ('567', '中国银行', '1040000', 'BOC', '财互通卡', '19', '6', '621212', '0');
INSERT INTO `authbank_cardbin` VALUES ('568', '中国银行', '1040000', 'BOC', '电子现金卡', '16', '6', '620514', '2');
INSERT INTO `authbank_cardbin` VALUES ('569', '中国银行', '1040000', 'BOC', '长城人民币信用卡', '16', '6', '622754', '3');
INSERT INTO `authbank_cardbin` VALUES ('570', '中国银行', '1040000', 'BOC', '长城单位信用卡普卡', '16', '6', '622764', '3');
INSERT INTO `authbank_cardbin` VALUES ('571', '中国银行', '1040000', 'BOC', '中银女性主题信用卡', '16', '6', '518377', '1');
INSERT INTO `authbank_cardbin` VALUES ('572', '中国银行', '1040000', 'BOC', '长城单位信用卡金卡', '16', '6', '622765', '3');
INSERT INTO `authbank_cardbin` VALUES ('573', '中国银行', '1040000', 'BOC', '白金卡', '16', '6', '622788', '1');
INSERT INTO `authbank_cardbin` VALUES ('574', '中国银行', '1040000', 'BOC', '中职学生资助卡', '19', '6', '621283', '0');
INSERT INTO `authbank_cardbin` VALUES ('575', '中国银行', '1040000', 'BOC', '银联标准卡', '19', '6', '620061', '0');
INSERT INTO `authbank_cardbin` VALUES ('576', '中国银行', '1040000', 'BOC', '金融IC卡', '19', '6', '621725', '0');
INSERT INTO `authbank_cardbin` VALUES ('577', '中国银行', '1040000', 'BOC', '长城社会保障卡', '19', '6', '620040', '2');
INSERT INTO `authbank_cardbin` VALUES ('578', '中国银行', '1040000', 'BOC', '世界卡', '16', '6', '558869', '3');
INSERT INTO `authbank_cardbin` VALUES ('579', '中国银行', '1040000', 'BOC', '社保联名卡', '19', '6', '621330', '0');
INSERT INTO `authbank_cardbin` VALUES ('580', '中国银行', '1040000', 'BOC', '社保联名卡', '19', '6', '621331', '0');
INSERT INTO `authbank_cardbin` VALUES ('581', '中国银行', '1040000', 'BOC', '医保联名卡', '19', '6', '621332', '0');
INSERT INTO `authbank_cardbin` VALUES ('582', '中国银行', '1040000', 'BOC', '医保联名卡', '19', '6', '621333', '0');
INSERT INTO `authbank_cardbin` VALUES ('583', '中国银行', '1040000', 'BOC', '公司借记卡', '19', '6', '621297', '0');
INSERT INTO `authbank_cardbin` VALUES ('584', '中国银行', '1040000', 'BOC', '银联美运顶级卡', '15', '6', '377677', '3');
INSERT INTO `authbank_cardbin` VALUES ('585', '中国银行', '1040000', 'BOC', '长城福农借记卡金卡', '19', '6', '621568', '0');
INSERT INTO `authbank_cardbin` VALUES ('586', '中国银行', '1040000', 'BOC', '长城福农借记卡普卡', '19', '6', '621569', '0');
INSERT INTO `authbank_cardbin` VALUES ('587', '中国银行', '1040000', 'BOC', '中行金融IC卡普卡', '16', '6', '625905', '3');
INSERT INTO `authbank_cardbin` VALUES ('588', '中国银行', '1040000', 'BOC', '中行金融IC卡金卡', '16', '6', '625906', '3');
INSERT INTO `authbank_cardbin` VALUES ('589', '中国银行', '1040000', 'BOC', '中行金融IC卡白金卡', '16', '6', '625907', '3');
INSERT INTO `authbank_cardbin` VALUES ('590', '中国银行', '1040000', 'BOC', '长城银联公务IC卡白金卡', '16', '6', '628313', '1');
INSERT INTO `authbank_cardbin` VALUES ('591', '中国银行', '1040000', 'BOC', '中银旅游信用卡', '16', '6', '625333', '3');
INSERT INTO `authbank_cardbin` VALUES ('592', '中国银行', '1040000', 'BOC', '长城银联公务IC卡金卡', '16', '6', '628312', '1');
INSERT INTO `authbank_cardbin` VALUES ('593', '中国银行', '1040000', 'BOC', '中国旅游卡', '19', '6', '623208', '0');
INSERT INTO `authbank_cardbin` VALUES ('594', '中国银行', '1040000', 'BOC', '武警军人保障卡', '19', '6', '621620', '0');
INSERT INTO `authbank_cardbin` VALUES ('595', '中国银行', '1040000', 'BOC', '社保联名借记IC卡', '19', '6', '621756', '0');
INSERT INTO `authbank_cardbin` VALUES ('596', '中国银行', '1040000', 'BOC', '社保联名借记IC卡', '19', '6', '621757', '0');
INSERT INTO `authbank_cardbin` VALUES ('597', '中国银行', '1040000', 'BOC', '医保联名借记IC卡', '19', '6', '621758', '0');
INSERT INTO `authbank_cardbin` VALUES ('598', '中国银行', '1040000', 'BOC', '医保联名借记IC卡', '19', '6', '621759', '0');
INSERT INTO `authbank_cardbin` VALUES ('599', '中国银行', '1040000', 'BOC', '借记IC个人普卡', '19', '6', '621785', '0');
INSERT INTO `authbank_cardbin` VALUES ('600', '中国银行', '1040000', 'BOC', '借记IC个人金卡', '19', '6', '621786', '0');
INSERT INTO `authbank_cardbin` VALUES ('601', '中国银行', '1040000', 'BOC', '借记IC个人普卡', '19', '6', '621787', '0');
INSERT INTO `authbank_cardbin` VALUES ('602', '中国银行', '1040000', 'BOC', '借记IC白金卡', '19', '6', '621788', '0');
INSERT INTO `authbank_cardbin` VALUES ('603', '中国银行', '1040000', 'BOC', '借记IC钻石卡', '19', '6', '621789', '0');
INSERT INTO `authbank_cardbin` VALUES ('604', '中国银行', '1040000', 'BOC', '借记IC联名卡', '19', '6', '621790', '0');
INSERT INTO `authbank_cardbin` VALUES ('605', '中国银行', '1040000', 'BOC', '普通高中学生资助卡', '19', '6', '621672', '0');
INSERT INTO `authbank_cardbin` VALUES ('606', '中国银行', '1040000', 'BOC', '长城环球通港澳台旅游金卡', '16', '6', '625337', '3');
INSERT INTO `authbank_cardbin` VALUES ('607', '中国银行', '1040000', 'BOC', '长城环球通港澳台旅游白金卡', '16', '6', '625338', '3');
INSERT INTO `authbank_cardbin` VALUES ('608', '中国银行', '1040000', 'BOC', '中银福农信用卡', '16', '6', '625568', '3');
INSERT INTO `authbank_cardbin` VALUES ('609', '中国银行', '1040000', 'BOC', '中银单位结算卡', '19', '6', '623263', '0');
INSERT INTO `authbank_cardbin` VALUES ('610', '中国银行（澳大利亚）', '1040036', 'BOC', '预付卡', '16', '6', '620025', '2');
INSERT INTO `authbank_cardbin` VALUES ('611', '中国银行（澳大利亚）', '1040036', 'BOC', '预付卡', '16', '6', '620026', '2');
INSERT INTO `authbank_cardbin` VALUES ('612', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621293', '0');
INSERT INTO `authbank_cardbin` VALUES ('613', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621294', '0');
INSERT INTO `authbank_cardbin` VALUES ('614', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621342', '0');
INSERT INTO `authbank_cardbin` VALUES ('615', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621343', '0');
INSERT INTO `authbank_cardbin` VALUES ('616', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621364', '0');
INSERT INTO `authbank_cardbin` VALUES ('617', '中国银行（澳大利亚）', '1040036', 'BOC', '借记卡', '16', '6', '621394', '0');
INSERT INTO `authbank_cardbin` VALUES ('618', '中国银行金边分行', '1040116', 'BOC', '借记卡', '16', '6', '621648', '0');
INSERT INTO `authbank_cardbin` VALUES ('619', '中国银行雅加达分行', '1040360', 'BOC', '借记卡', '16', '6', '621248', '0');
INSERT INTO `authbank_cardbin` VALUES ('620', '中银东京分行', '1040392', '', '借记卡普卡', '16', '6', '621215', '0');
INSERT INTO `authbank_cardbin` VALUES ('621', '中国银行首尔分行', '1040410', 'BOC', '借记卡', '16', '6', '621249', '0');
INSERT INTO `authbank_cardbin` VALUES ('622', '中国银行澳门分行', '1040446', 'BOC', '人民币信用卡', '16', '6', '622750', '1');
INSERT INTO `authbank_cardbin` VALUES ('623', '中国银行澳门分行', '1040446', 'BOC', '人民币信用卡', '16', '6', '622751', '1');
INSERT INTO `authbank_cardbin` VALUES ('624', '中国银行澳门分行', '1040446', 'BOC', '中银卡', '19', '6', '622771', '0');
INSERT INTO `authbank_cardbin` VALUES ('625', '中国银行澳门分行', '1040446', 'BOC', '中银卡', '19', '6', '622772', '0');
INSERT INTO `authbank_cardbin` VALUES ('626', '中国银行澳门分行', '1040446', 'BOC', '中银卡', '19', '6', '622770', '0');
INSERT INTO `authbank_cardbin` VALUES ('627', '中国银行澳门分行', '1040446', 'BOC', '中银银联双币商务卡', '16', '6', '625145', '1');
INSERT INTO `authbank_cardbin` VALUES ('628', '中国银行澳门分行', '1040446', 'BOC', '预付卡', '19', '6', '620531', '2');
INSERT INTO `authbank_cardbin` VALUES ('629', '中国银行澳门分行', '1040446', 'BOC', '澳门中国银行银联预付卡', '16', '6', '620210', '2');
INSERT INTO `authbank_cardbin` VALUES ('630', '中国银行澳门分行', '1040446', 'BOC', '澳门中国银行银联预付卡', '16', '6', '620211', '2');
INSERT INTO `authbank_cardbin` VALUES ('631', '中国银行澳门分行', '1040446', 'BOC', '熊猫卡', '16', '6', '622479', '1');
INSERT INTO `authbank_cardbin` VALUES ('632', '中国银行澳门分行', '1040446', 'BOC', '财富卡', '16', '6', '622480', '1');
INSERT INTO `authbank_cardbin` VALUES ('633', '中国银行澳门分行', '1040446', 'BOC', '银联港币卡', '19', '6', '622273', '0');
INSERT INTO `authbank_cardbin` VALUES ('634', '中国银行澳门分行', '1040446', 'BOC', '银联澳门币卡', '19', '6', '622274', '0');
INSERT INTO `authbank_cardbin` VALUES ('635', '中国银行澳门分行', '1040446', 'BOC', '中银全币种银联尊贵卡', '16', '6', '622380', '1');
INSERT INTO `authbank_cardbin` VALUES ('636', '中国银行(马来西亚)', '1040458', 'BOC', '预付卡', '16', '6', '620019', '2');
INSERT INTO `authbank_cardbin` VALUES ('637', '中国银行(马来西亚)', '1040458', 'BOC', '预付卡', '16', '6', '620035', '2');
INSERT INTO `authbank_cardbin` VALUES ('638', '中国银行马尼拉分行', '1040608', 'BOC', '双币种借记卡', '16', '6', '621231', '0');
INSERT INTO `authbank_cardbin` VALUES ('639', '中行新加坡分行', '1040702', '', 'BOCCUPPLATINUMCARD', '16', '6', '622789', '1');
INSERT INTO `authbank_cardbin` VALUES ('640', '中国银行胡志明分行', '1040704', 'BOC', '借记卡', '16', '6', '621638', '0');
INSERT INTO `authbank_cardbin` VALUES ('641', '中国银行曼谷分行', '1040764', 'BOC', '借记卡', '16', '6', '621334', '0');
INSERT INTO `authbank_cardbin` VALUES ('642', '中国银行曼谷分行', '1040764', 'BOC', '长城信用卡环球通', '16', '6', '625140', '1');
INSERT INTO `authbank_cardbin` VALUES ('643', '中国银行曼谷分行', '1040764', 'BOC', '借记卡', '16', '6', '621395', '0');
INSERT INTO `authbank_cardbin` VALUES ('644', '中行宁波分行', '1043320', '', '长城宁波市民卡', '19', '6', '620513', '2');
INSERT INTO `authbank_cardbin` VALUES ('645', '建设银行', '1050000', 'CCB', '龙卡信用卡', '18', '7', '5453242', '1');
INSERT INTO `authbank_cardbin` VALUES ('646', '建设银行', '1050000', 'CCB', '龙卡信用卡', '18', '7', '5491031', '1');
INSERT INTO `authbank_cardbin` VALUES ('647', '建设银行', '1050000', 'CCB', '龙卡信用卡', '18', '7', '5544033', '1');
INSERT INTO `authbank_cardbin` VALUES ('648', '建设银行', '1050000', 'CCB', '龙卡准贷记卡', '16', '6', '622725', '3');
INSERT INTO `authbank_cardbin` VALUES ('649', '建设银行', '1050000', 'CCB', '龙卡准贷记卡金卡', '16', '6', '622728', '3');
INSERT INTO `authbank_cardbin` VALUES ('650', '建设银行', '1050000', 'CCB', '中职学生资助卡', '19', '6', '621284', '0');
INSERT INTO `authbank_cardbin` VALUES ('651', '建设银行', '1050000', 'CCB', '乐当家银卡VISA', '16', '6', '421349', '0');
INSERT INTO `authbank_cardbin` VALUES ('652', '建设银行', '1050000', 'CCB', '乐当家金卡VISA', '16', '6', '434061', '0');
INSERT INTO `authbank_cardbin` VALUES ('653', '建设银行', '1050000', 'CCB', '乐当家白金卡', '16', '6', '434062', '0');
INSERT INTO `authbank_cardbin` VALUES ('654', '建设银行', '1050000', 'CCB', '龙卡普通卡VISA', '16', '6', '436728', '3');
INSERT INTO `authbank_cardbin` VALUES ('655', '建设银行', '1050000', 'CCB', '龙卡储蓄卡', '19', '6', '436742', '0');
INSERT INTO `authbank_cardbin` VALUES ('656', '建设银行', '1050000', 'CCB', 'VISA准贷记卡(银联卡)', '16', '6', '453242', '3');
INSERT INTO `authbank_cardbin` VALUES ('657', '建设银行', '1050000', 'CCB', 'VISA准贷记金卡', '16', '6', '491031', '3');
INSERT INTO `authbank_cardbin` VALUES ('658', '建设银行', '1050000', 'CCB', '乐当家', '16', '6', '524094', '0');
INSERT INTO `authbank_cardbin` VALUES ('659', '建设银行', '1050000', 'CCB', '乐当家', '16', '6', '526410', '0');
INSERT INTO `authbank_cardbin` VALUES ('660', '建设银行', '1050000', 'CCB', 'MASTER准贷记卡', '16', '5', '53242', '3');
INSERT INTO `authbank_cardbin` VALUES ('661', '建设银行', '1050000', 'CCB', '乐当家', '16', '5', '53243', '3');
INSERT INTO `authbank_cardbin` VALUES ('662', '建设银行', '1050000', 'CCB', '准贷记金卡', '16', '6', '544033', '3');
INSERT INTO `authbank_cardbin` VALUES ('663', '建设银行', '1050000', 'CCB', '乐当家白金卡', '16', '6', '552245', '0');
INSERT INTO `authbank_cardbin` VALUES ('664', '建设银行', '1050000', 'CCB', '金融复合IC卡', '19', '6', '589970', '0');
INSERT INTO `authbank_cardbin` VALUES ('665', '建设银行', '1050000', 'CCB', '银联标准卡', '19', '6', '620060', '0');
INSERT INTO `authbank_cardbin` VALUES ('666', '建设银行', '1050000', 'CCB', '银联理财钻石卡', '16', '6', '621080', '0');
INSERT INTO `authbank_cardbin` VALUES ('667', '建设银行', '1050000', 'CCB', '金融IC卡', '19', '6', '621081', '0');
INSERT INTO `authbank_cardbin` VALUES ('668', '建设银行', '1050000', 'CCB', '理财白金卡', '16', '6', '621466', '0');
INSERT INTO `authbank_cardbin` VALUES ('669', '建设银行', '1050000', 'CCB', '社保IC卡', '19', '6', '621467', '0');
INSERT INTO `authbank_cardbin` VALUES ('670', '建设银行', '1050000', 'CCB', '财富卡私人银行卡', '16', '6', '621488', '0');
INSERT INTO `authbank_cardbin` VALUES ('671', '建设银行', '1050000', 'CCB', '理财金卡', '16', '6', '621499', '0');
INSERT INTO `authbank_cardbin` VALUES ('672', '建设银行', '1050000', 'CCB', '福农卡', '19', '6', '621598', '0');
INSERT INTO `authbank_cardbin` VALUES ('673', '建设银行', '1050000', 'CCB', '武警军人保障卡', '19', '6', '621621', '0');
INSERT INTO `authbank_cardbin` VALUES ('674', '建设银行', '1050000', 'CCB', '龙卡通', '19', '6', '621700', '0');
INSERT INTO `authbank_cardbin` VALUES ('675', '建设银行', '1050000', 'CCB', '银联储蓄卡', '19', '6', '622280', '0');
INSERT INTO `authbank_cardbin` VALUES ('676', '建设银行', '1050000', 'CCB', '龙卡储蓄卡(银联卡)', '19', '6', '622700', '0');
INSERT INTO `authbank_cardbin` VALUES ('677', '建设银行', '1050000', 'CCB', '准贷记卡', '16', '6', '622707', '3');
INSERT INTO `authbank_cardbin` VALUES ('678', '建设银行', '1050000', 'CCB', '理财白金卡', '16', '6', '622966', '0');
INSERT INTO `authbank_cardbin` VALUES ('679', '建设银行', '1050000', 'CCB', '理财金卡', '16', '6', '622988', '0');
INSERT INTO `authbank_cardbin` VALUES ('680', '建设银行', '1050000', 'CCB', '准贷记卡普卡', '16', '6', '625955', '3');
INSERT INTO `authbank_cardbin` VALUES ('681', '建设银行', '1050000', 'CCB', '准贷记卡金卡', '16', '6', '625956', '3');
INSERT INTO `authbank_cardbin` VALUES ('682', '建设银行', '1050000', 'CCB', '龙卡信用卡', '18', '6', '553242', '1');
INSERT INTO `authbank_cardbin` VALUES ('683', '建设银行', '1050000', 'CCB', '建行陆港通龙卡', '16', '6', '621082', '0');
INSERT INTO `authbank_cardbin` VALUES ('684', '建设银行', '1050000', 'CCB', '单位结算卡', '16', '6', '623251', '0');
INSERT INTO `authbank_cardbin` VALUES ('685', '中国建设银行', '1050001', 'CCB', '普通高中学生资助卡', '19', '6', '621673', '0');
INSERT INTO `authbank_cardbin` VALUES ('686', '中国建设银行', '1050001', 'CCB', '结算通借记卡', '19', '6', '623668', '0');
INSERT INTO `authbank_cardbin` VALUES ('687', '中国建设银行', '1050001', 'CCB', '中国旅游卡', '19', '6', '623211', '0');
INSERT INTO `authbank_cardbin` VALUES ('688', '建行厦门分行', '1053930', 'CCB', '龙卡储蓄卡', '19', '9', '436742193', '0');
INSERT INTO `authbank_cardbin` VALUES ('689', '建行厦门分行', '1053930', 'CCB', '银联储蓄卡', '19', '9', '622280193', '0');
INSERT INTO `authbank_cardbin` VALUES ('690', '中国建设银行', '1059999', 'CCB', '龙卡JCB金卡', '16', '6', '356896', '1');
INSERT INTO `authbank_cardbin` VALUES ('691', '中国建设银行', '1059999', 'CCB', '龙卡JCB白金卡', '16', '6', '356899', '1');
INSERT INTO `authbank_cardbin` VALUES ('692', '中国建设银行', '1059999', 'CCB', '龙卡JCB普卡', '16', '6', '356895', '1');
INSERT INTO `authbank_cardbin` VALUES ('693', '中国建设银行', '1059999', 'CCB', '龙卡贷记卡公司卡', '16', '6', '436718', '1');
INSERT INTO `authbank_cardbin` VALUES ('694', '中国建设银行', '1059999', 'CCB', '龙卡贷记卡', '16', '6', '436738', '1');
INSERT INTO `authbank_cardbin` VALUES ('695', '中国建设银行', '1059999', 'CCB', '龙卡国际普通卡VISA', '16', '6', '436745', '1');
INSERT INTO `authbank_cardbin` VALUES ('696', '中国建设银行', '1059999', 'CCB', '龙卡国际金卡VISA', '16', '6', '436748', '1');
INSERT INTO `authbank_cardbin` VALUES ('697', '中国建设银行', '1059999', 'CCB', 'VISA白金信用卡', '16', '6', '489592', '1');
INSERT INTO `authbank_cardbin` VALUES ('698', '中国建设银行', '1059999', 'CCB', '龙卡国际白金卡', '16', '6', '531693', '1');
INSERT INTO `authbank_cardbin` VALUES ('699', '中国建设银行', '1059999', 'CCB', '龙卡国际普通卡MASTER', '16', '6', '532450', '1');
INSERT INTO `authbank_cardbin` VALUES ('700', '中国建设银行', '1059999', 'CCB', '龙卡国际金卡MASTER', '16', '6', '532458', '1');
INSERT INTO `authbank_cardbin` VALUES ('701', '中国建设银行', '1059999', 'CCB', '龙卡万事达金卡', '16', '6', '544887', '1');
INSERT INTO `authbank_cardbin` VALUES ('702', '中国建设银行', '1059999', 'CCB', '龙卡贷记卡', '16', '6', '552801', '1');
INSERT INTO `authbank_cardbin` VALUES ('703', '中国建设银行', '1059999', 'CCB', '龙卡万事达白金卡', '16', '6', '557080', '1');
INSERT INTO `authbank_cardbin` VALUES ('704', '中国建设银行', '1059999', 'CCB', '龙卡贷记卡', '16', '6', '558895', '1');
INSERT INTO `authbank_cardbin` VALUES ('705', '中国建设银行', '1059999', 'CCB', '龙卡万事达信用卡', '16', '6', '559051', '1');
INSERT INTO `authbank_cardbin` VALUES ('706', '中国建设银行', '1059999', 'CCB', '龙卡人民币信用卡', '16', '6', '622166', '1');
INSERT INTO `authbank_cardbin` VALUES ('707', '中国建设银行', '1059999', 'CCB', '龙卡人民币信用金卡', '16', '6', '622168', '1');
INSERT INTO `authbank_cardbin` VALUES ('708', '中国建设银行', '1059999', 'CCB', '龙卡人民币白金卡', '16', '6', '622708', '1');
INSERT INTO `authbank_cardbin` VALUES ('709', '中国建设银行', '1059999', 'CCB', '龙卡IC信用卡普卡', '16', '6', '625964', '1');
INSERT INTO `authbank_cardbin` VALUES ('710', '中国建设银行', '1059999', 'CCB', '龙卡IC信用卡金卡', '16', '6', '625965', '1');
INSERT INTO `authbank_cardbin` VALUES ('711', '中国建设银行', '1059999', 'CCB', '龙卡IC信用卡白金卡', '16', '6', '625966', '1');
INSERT INTO `authbank_cardbin` VALUES ('712', '中国建设银行', '1059999', 'CCB', '龙卡银联公务卡普卡', '16', '6', '628266', '1');
INSERT INTO `authbank_cardbin` VALUES ('713', '中国建设银行', '1059999', 'CCB', '龙卡银联公务卡金卡', '16', '6', '628366', '1');
INSERT INTO `authbank_cardbin` VALUES ('714', '中国建设银行', '1059999', 'CCB', '中国旅游卡', '16', '6', '625362', '1');
INSERT INTO `authbank_cardbin` VALUES ('715', '中国建设银行', '1059999', 'CCB', '中国旅游卡', '16', '6', '625363', '1');
INSERT INTO `authbank_cardbin` VALUES ('716', '中国建设银行', '1059999', 'CCB', '龙卡IC公务卡', '16', '6', '628316', '1');
INSERT INTO `authbank_cardbin` VALUES ('717', '中国建设银行', '1059999', 'CCB', '龙卡IC公务卡', '16', '6', '628317', '1');
INSERT INTO `authbank_cardbin` VALUES ('718', '交通银行', '3010000', 'BCM', '交行预付卡', '19', '6', '620021', '2');
INSERT INTO `authbank_cardbin` VALUES ('719', '交通银行', '3010000', 'BCM', '世博预付IC卡', '19', '6', '620521', '2');
INSERT INTO `authbank_cardbin` VALUES ('720', '交通银行', '3010000', 'BCM', '太平洋互连卡', '17', '8', '00405512', '0');
INSERT INTO `authbank_cardbin` VALUES ('721', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '7', '0049104', '1');
INSERT INTO `authbank_cardbin` VALUES ('722', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '7', '0053783', '1');
INSERT INTO `authbank_cardbin` VALUES ('723', '交通银行', '3010000', 'BCM', '太平洋万事顺卡', '17', '8', '00601428', '0');
INSERT INTO `authbank_cardbin` VALUES ('724', '交通银行', '3010000', 'BCM', '太平洋互连卡(银联卡)', '17', '6', '405512', '0');
INSERT INTO `authbank_cardbin` VALUES ('725', '交通银行', '3010000', 'BCM', '太平洋白金信用卡', '16', '6', '434910', '1');
INSERT INTO `authbank_cardbin` VALUES ('726', '交通银行', '3010000', 'BCM', '太平洋双币贷记卡', '16', '6', '458123', '1');
INSERT INTO `authbank_cardbin` VALUES ('727', '交通银行', '3010000', 'BCM', '太平洋双币贷记卡', '16', '6', '458124', '1');
INSERT INTO `authbank_cardbin` VALUES ('728', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '5', '49104', '1');
INSERT INTO `authbank_cardbin` VALUES ('729', '交通银行', '3010000', 'BCM', '太平洋双币贷记卡', '16', '6', '520169', '1');
INSERT INTO `authbank_cardbin` VALUES ('730', '交通银行', '3010000', 'BCM', '太平洋白金信用卡', '16', '6', '522964', '1');
INSERT INTO `authbank_cardbin` VALUES ('731', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '5', '53783', '1');
INSERT INTO `authbank_cardbin` VALUES ('732', '交通银行', '3010000', 'BCM', '太平洋双币贷记卡', '16', '6', '552853', '1');
INSERT INTO `authbank_cardbin` VALUES ('733', '交通银行', '3010000', 'BCM', '太平洋万事顺卡', '17', '6', '601428', '0');
INSERT INTO `authbank_cardbin` VALUES ('734', '交通银行', '3010000', 'BCM', '太平洋人民币贷记卡', '16', '6', '622250', '1');
INSERT INTO `authbank_cardbin` VALUES ('735', '交通银行', '3010000', 'BCM', '太平洋人民币贷记卡', '16', '6', '622251', '1');
INSERT INTO `authbank_cardbin` VALUES ('736', '交通银行', '3010000', 'BCM', '太平洋双币贷记卡', '16', '6', '521899', '1');
INSERT INTO `authbank_cardbin` VALUES ('737', '交通银行', '3010000', 'BCM', '太平洋准贷记卡', '16', '6', '622254', '3');
INSERT INTO `authbank_cardbin` VALUES ('738', '交通银行', '3010000', 'BCM', '太平洋准贷记卡', '16', '6', '622255', '3');
INSERT INTO `authbank_cardbin` VALUES ('739', '交通银行', '3010000', 'BCM', '太平洋准贷记卡', '16', '6', '622256', '3');
INSERT INTO `authbank_cardbin` VALUES ('740', '交通银行', '3010000', 'BCM', '太平洋准贷记卡', '16', '6', '622257', '3');
INSERT INTO `authbank_cardbin` VALUES ('741', '交通银行', '3010000', 'BCM', '太平洋借记卡', '17', '6', '622258', '0');
INSERT INTO `authbank_cardbin` VALUES ('742', '交通银行', '3010000', 'BCM', '太平洋借记卡', '17', '6', '622259', '0');
INSERT INTO `authbank_cardbin` VALUES ('743', '交通银行', '3010000', 'BCM', '太平洋人民币贷记卡', '16', '6', '622253', '1');
INSERT INTO `authbank_cardbin` VALUES ('744', '交通银行', '3010000', 'BCM', '太平洋借记卡', '19', '6', '622261', '0');
INSERT INTO `authbank_cardbin` VALUES ('745', '交通银行', '3010000', 'BCM', '太平洋MORE卡', '16', '6', '622284', '3');
INSERT INTO `authbank_cardbin` VALUES ('746', '交通银行', '3010000', 'BCM', '白金卡', '16', '6', '622656', '1');
INSERT INTO `authbank_cardbin` VALUES ('747', '交通银行', '3010000', 'BCM', '交通银行公务卡普卡', '16', '6', '628216', '1');
INSERT INTO `authbank_cardbin` VALUES ('748', '交通银行', '3010000', 'BCM', '太平洋人民币贷记卡', '16', '6', '622252', '1');
INSERT INTO `authbank_cardbin` VALUES ('749', '交通银行', '3010000', 'BCM', '太平洋互连卡', '17', '8', '66405512', '0');
INSERT INTO `authbank_cardbin` VALUES ('750', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '7', '6649104', '1');
INSERT INTO `authbank_cardbin` VALUES ('751', '交通银行', '3010000', 'BCM', '太平洋借记卡', '19', '6', '622260', '0');
INSERT INTO `authbank_cardbin` VALUES ('752', '交通银行', '3010000', 'BCM', '太平洋万事顺卡', '17', '8', '66601428', '0');
INSERT INTO `authbank_cardbin` VALUES ('753', '交通银行', '3010000', 'BCM', '太平洋贷记卡(银联卡)', '16', '6', '955590', '1');
INSERT INTO `authbank_cardbin` VALUES ('754', '交通银行', '3010000', 'BCM', '太平洋贷记卡(银联卡)', '16', '6', '955591', '1');
INSERT INTO `authbank_cardbin` VALUES ('755', '交通银行', '3010000', 'BCM', '太平洋贷记卡(银联卡)', '16', '6', '955592', '1');
INSERT INTO `authbank_cardbin` VALUES ('756', '交通银行', '3010000', 'BCM', '太平洋贷记卡(银联卡)', '16', '6', '955593', '1');
INSERT INTO `authbank_cardbin` VALUES ('757', '交通银行', '3010000', 'BCM', '太平洋信用卡', '16', '7', '6653783', '1');
INSERT INTO `authbank_cardbin` VALUES ('758', '交通银行', '3010000', 'BCM', '交通银行公务卡金卡', '16', '6', '628218', '1');
INSERT INTO `authbank_cardbin` VALUES ('759', '交通银行', '3010000', 'BCM', '交银IC卡', '19', '6', '622262', '0');
INSERT INTO `authbank_cardbin` VALUES ('760', '交通银行', '3010000', 'BCM', '交通银行单位结算卡', '19', '6', '623261', '0');
INSERT INTO `authbank_cardbin` VALUES ('761', '交通银行香港分行', '3010344', 'BCM', '交通银行港币借记卡', '19', '6', '621069', '0');
INSERT INTO `authbank_cardbin` VALUES ('762', '交通银行香港分行', '3010344', 'BCM', '港币礼物卡', '16', '6', '620013', '0');
INSERT INTO `authbank_cardbin` VALUES ('763', '交通银行香港分行', '3010344', 'BCM', '双币种信用卡', '16', '6', '625028', '1');
INSERT INTO `authbank_cardbin` VALUES ('764', '交通银行香港分行', '3010344', 'BCM', '双币种信用卡', '16', '6', '625029', '1');
INSERT INTO `authbank_cardbin` VALUES ('765', '交通银行香港分行', '3010344', 'BCM', '双币卡', '19', '6', '621436', '0');
INSERT INTO `authbank_cardbin` VALUES ('766', '交通银行香港分行', '3010344', 'BCM', '银联人民币卡', '19', '6', '621002', '0');
INSERT INTO `authbank_cardbin` VALUES ('767', '交通银行澳门分行', '3010446', 'BCM', '银联借记卡', '19', '6', '621335', '0');
INSERT INTO `authbank_cardbin` VALUES ('768', '中信银行', '3020000', 'ECITIC', '中信借记卡', '16', '6', '433670', '0');
INSERT INTO `authbank_cardbin` VALUES ('769', '中信银行', '3020000', 'ECITIC', '中信借记卡', '16', '6', '433680', '0');
INSERT INTO `authbank_cardbin` VALUES ('770', '中信银行', '3020000', 'ECITIC', '中信国际借记卡', '16', '6', '442729', '0');
INSERT INTO `authbank_cardbin` VALUES ('771', '中信银行', '3020000', 'ECITIC', '中信国际借记卡', '16', '6', '442730', '0');
INSERT INTO `authbank_cardbin` VALUES ('772', '中信银行', '3020000', 'ECITIC', '中国旅行卡', '16', '6', '620082', '0');
INSERT INTO `authbank_cardbin` VALUES ('773', '中信银行', '3020000', 'ECITIC', '中信借记卡(银联卡)', '16', '6', '622690', '0');
INSERT INTO `authbank_cardbin` VALUES ('774', '中信银行', '3020000', 'ECITIC', '中信借记卡(银联卡)', '16', '6', '622691', '0');
INSERT INTO `authbank_cardbin` VALUES ('775', '中信银行', '3020000', 'ECITIC', '中信贵宾卡(银联卡)', '16', '6', '622692', '0');
INSERT INTO `authbank_cardbin` VALUES ('776', '中信银行', '3020000', 'ECITIC', '中信理财宝金卡', '16', '6', '622696', '0');
INSERT INTO `authbank_cardbin` VALUES ('777', '中信银行', '3020000', 'ECITIC', '中信理财宝白金卡', '16', '6', '622698', '0');
INSERT INTO `authbank_cardbin` VALUES ('778', '中信银行', '3020000', 'ECITIC', '中信钻石卡', '16', '6', '622998', '0');
INSERT INTO `authbank_cardbin` VALUES ('779', '中信银行', '3020000', 'ECITIC', '中信钻石卡', '16', '6', '622999', '0');
INSERT INTO `authbank_cardbin` VALUES ('780', '中信银行', '3020000', 'ECITIC', '中信借记卡', '16', '6', '433671', '0');
INSERT INTO `authbank_cardbin` VALUES ('781', '中信银行', '3020000', 'ECITIC', '中信理财宝(银联卡)', '16', '6', '968807', '0');
INSERT INTO `authbank_cardbin` VALUES ('782', '中信银行', '3020000', 'ECITIC', '中信理财宝(银联卡)', '16', '6', '968808', '0');
INSERT INTO `authbank_cardbin` VALUES ('783', '中信银行', '3020000', 'ECITIC', '中信理财宝(银联卡)', '16', '6', '968809', '0');
INSERT INTO `authbank_cardbin` VALUES ('784', '中信银行', '3020000', 'ECITIC', '借记卡', '16', '6', '621771', '0');
INSERT INTO `authbank_cardbin` VALUES ('785', '中信银行', '3020000', 'ECITIC', '理财宝IC卡', '16', '6', '621767', '0');
INSERT INTO `authbank_cardbin` VALUES ('786', '中信银行', '3020000', 'ECITIC', '理财宝IC卡', '16', '6', '621768', '0');
INSERT INTO `authbank_cardbin` VALUES ('787', '中信银行', '3020000', 'ECITIC', '理财宝IC卡', '16', '6', '621770', '0');
INSERT INTO `authbank_cardbin` VALUES ('788', '中信银行', '3020000', 'ECITIC', '理财宝IC卡', '16', '6', '621772', '0');
INSERT INTO `authbank_cardbin` VALUES ('789', '中信银行', '3020000', 'ECITIC', '理财宝IC卡', '16', '6', '621773', '0');
INSERT INTO `authbank_cardbin` VALUES ('790', '中信银行', '3020000', 'ECITIC', '中信银行钻石卡', '16', '6', '621769', '0');
INSERT INTO `authbank_cardbin` VALUES ('791', '中信银行', '3020000', 'ECITIC', '主账户复合电子现金卡', '19', '6', '620527', '0');
INSERT INTO `authbank_cardbin` VALUES ('792', '光大银行', '3030000', 'CEB', '阳光卡', '16', '3', '303', '0');
INSERT INTO `authbank_cardbin` VALUES ('793', '光大银行', '3030000', 'CEB', '阳光商旅信用卡', '16', '6', '356837', '1');
INSERT INTO `authbank_cardbin` VALUES ('794', '光大银行', '3030000', 'CEB', '阳光商旅信用卡', '16', '6', '356838', '1');
INSERT INTO `authbank_cardbin` VALUES ('795', '光大银行', '3030000', 'CEB', '阳光商旅信用卡', '16', '6', '486497', '1');
INSERT INTO `authbank_cardbin` VALUES ('796', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622660', '0');
INSERT INTO `authbank_cardbin` VALUES ('797', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622662', '0');
INSERT INTO `authbank_cardbin` VALUES ('798', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622663', '0');
INSERT INTO `authbank_cardbin` VALUES ('799', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622664', '0');
INSERT INTO `authbank_cardbin` VALUES ('800', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622665', '0');
INSERT INTO `authbank_cardbin` VALUES ('801', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622666', '0');
INSERT INTO `authbank_cardbin` VALUES ('802', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622667', '0');
INSERT INTO `authbank_cardbin` VALUES ('803', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622669', '0');
INSERT INTO `authbank_cardbin` VALUES ('804', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622670', '0');
INSERT INTO `authbank_cardbin` VALUES ('805', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622671', '0');
INSERT INTO `authbank_cardbin` VALUES ('806', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622672', '0');
INSERT INTO `authbank_cardbin` VALUES ('807', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622668', '0');
INSERT INTO `authbank_cardbin` VALUES ('808', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622661', '0');
INSERT INTO `authbank_cardbin` VALUES ('809', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622674', '0');
INSERT INTO `authbank_cardbin` VALUES ('810', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '5', '90030', '0');
INSERT INTO `authbank_cardbin` VALUES ('811', '光大银行', '3030000', 'CEB', '阳光卡(银联卡)', '16', '6', '622673', '0');
INSERT INTO `authbank_cardbin` VALUES ('812', '光大银行', '3030000', 'CEB', '借记卡普卡', '16', '6', '620518', '0');
INSERT INTO `authbank_cardbin` VALUES ('813', '光大银行', '3030000', 'CEB', '社会保障IC卡', '16', '6', '621489', '0');
INSERT INTO `authbank_cardbin` VALUES ('814', '光大银行', '3030000', 'CEB', 'IC借记卡普卡', '16', '6', '621492', '0');
INSERT INTO `authbank_cardbin` VALUES ('815', '光大银行', '3030000', 'CEB', '手机支付卡', '19', '6', '620535', '0');
INSERT INTO `authbank_cardbin` VALUES ('816', '光大银行', '3030000', 'CEB', '联名IC卡普卡', '16', '6', '623156', '0');
INSERT INTO `authbank_cardbin` VALUES ('817', '光大银行', '3030000', 'CEB', '借记IC卡白金卡', '16', '6', '621490', '0');
INSERT INTO `authbank_cardbin` VALUES ('818', '光大银行', '3030000', 'CEB', '借记IC卡金卡', '16', '6', '621491', '0');
INSERT INTO `authbank_cardbin` VALUES ('819', '光大银行', '3030000', 'CEB', '阳光旅行卡', '16', '6', '620085', '0');
INSERT INTO `authbank_cardbin` VALUES ('820', '光大银行', '3030000', 'CEB', '借记IC卡钻石卡', '16', '6', '623155', '0');
INSERT INTO `authbank_cardbin` VALUES ('821', '光大银行', '3030000', 'CEB', '联名IC卡金卡', '16', '6', '623157', '0');
INSERT INTO `authbank_cardbin` VALUES ('822', '光大银行', '3030000', 'CEB', '联名IC卡白金卡', '16', '6', '623158', '0');
INSERT INTO `authbank_cardbin` VALUES ('823', '光大银行', '3030000', 'CEB', '联名IC卡钻石卡', '16', '6', '623159', '0');
INSERT INTO `authbank_cardbin` VALUES ('824', '华夏银行', '3040000', 'HXB', '华夏卡(银联卡)', '16', '6', '999999', '0');
INSERT INTO `authbank_cardbin` VALUES ('825', '华夏银行', '3040000', 'HXB', '华夏白金卡', '16', '6', '621222', '0');
INSERT INTO `authbank_cardbin` VALUES ('826', '华夏银行', '3040000', 'HXB', '华夏普卡', '16', '6', '623020', '0');
INSERT INTO `authbank_cardbin` VALUES ('827', '华夏银行', '3040000', 'HXB', '华夏金卡', '16', '6', '623021', '0');
INSERT INTO `authbank_cardbin` VALUES ('828', '华夏银行', '3040000', 'HXB', '华夏白金卡', '16', '6', '623022', '0');
INSERT INTO `authbank_cardbin` VALUES ('829', '华夏银行', '3040000', 'HXB', '华夏钻石卡', '16', '6', '623023', '0');
INSERT INTO `authbank_cardbin` VALUES ('830', '华夏银行', '3040000', 'HXB', '华夏卡(银联卡)', '16', '6', '622630', '0');
INSERT INTO `authbank_cardbin` VALUES ('831', '华夏银行', '3040000', 'HXB', '华夏至尊金卡(银联卡)', '16', '6', '622631', '0');
INSERT INTO `authbank_cardbin` VALUES ('832', '华夏银行', '3040000', 'HXB', '华夏丽人卡(银联卡)', '16', '6', '622632', '0');
INSERT INTO `authbank_cardbin` VALUES ('833', '华夏银行', '3040000', 'HXB', '华夏万通卡', '16', '6', '622633', '0');
INSERT INTO `authbank_cardbin` VALUES ('834', '民生银行', '3050000', 'CMBC', '民生借记卡(银联卡)', '16', '6', '622615', '0');
INSERT INTO `authbank_cardbin` VALUES ('835', '民生银行', '3050000', 'CMBC', '民生银联借记卡－金卡', '16', '6', '622616', '0');
INSERT INTO `authbank_cardbin` VALUES ('836', '民生银行', '3050000', 'CMBC', '钻石卡', '16', '6', '622618', '0');
INSERT INTO `authbank_cardbin` VALUES ('837', '民生银行', '3050000', 'CMBC', '民生借记卡(银联卡)', '16', '6', '622622', '0');
INSERT INTO `authbank_cardbin` VALUES ('838', '民生银行', '3050000', 'CMBC', '民生借记卡(银联卡)', '16', '6', '622617', '0');
INSERT INTO `authbank_cardbin` VALUES ('839', '民生银行', '3050000', 'CMBC', '民生借记卡(银联卡)', '16', '6', '622619', '0');
INSERT INTO `authbank_cardbin` VALUES ('840', '民生银行', '3050000', 'CMBC', '民生借记卡', '16', '6', '415599', '0');
INSERT INTO `authbank_cardbin` VALUES ('841', '民生银行', '3050000', 'CMBC', '民生国际卡', '16', '6', '421393', '0');
INSERT INTO `authbank_cardbin` VALUES ('842', '民生银行', '3050000', 'CMBC', '民生国际卡(银卡)', '16', '6', '421865', '0');
INSERT INTO `authbank_cardbin` VALUES ('843', '民生银行', '3050000', 'CMBC', '民生国际卡(欧元卡)', '16', '6', '427570', '0');
INSERT INTO `authbank_cardbin` VALUES ('844', '民生银行', '3050000', 'CMBC', '民生国际卡(澳元卡)', '16', '6', '427571', '0');
INSERT INTO `authbank_cardbin` VALUES ('845', '民生银行', '3050000', 'CMBC', '民生国际卡', '16', '6', '472067', '0');
INSERT INTO `authbank_cardbin` VALUES ('846', '民生银行', '3050000', 'CMBC', '民生国际卡', '16', '6', '472068', '0');
INSERT INTO `authbank_cardbin` VALUES ('847', '民生银行', '3050000', 'CMBC', '薪资理财卡', '16', '6', '622620', '0');
INSERT INTO `authbank_cardbin` VALUES ('848', '民生银行', '3050000', 'CMBC', '借记卡普卡', '16', '6', '621691', '0');
INSERT INTO `authbank_cardbin` VALUES ('849', '民生银行', '3050000', 'CMBC', '借记卡', '16', '6', '623255', '0');
INSERT INTO `authbank_cardbin` VALUES ('850', '民生银行', '3050000', 'CMBC', '--', '16', '6', '623258', '0');
INSERT INTO `authbank_cardbin` VALUES ('851', '民生银行', '3050001', 'CMBC', '民生MasterCard', '16', '6', '545392', '1');
INSERT INTO `authbank_cardbin` VALUES ('852', '民生银行', '3050001', 'CMBC', '民生MasterCard', '16', '6', '545393', '1');
INSERT INTO `authbank_cardbin` VALUES ('853', '民生银行', '3050001', 'CMBC', '民生MasterCard', '16', '6', '545431', '1');
INSERT INTO `authbank_cardbin` VALUES ('854', '民生银行', '3050001', 'CMBC', '民生MasterCard', '16', '6', '545447', '1');
INSERT INTO `authbank_cardbin` VALUES ('855', '民生银行', '3050001', 'CMBC', '民生JCB信用卡', '16', '6', '356859', '1');
INSERT INTO `authbank_cardbin` VALUES ('856', '民生银行', '3050001', 'CMBC', '民生JCB金卡', '16', '6', '356857', '1');
INSERT INTO `authbank_cardbin` VALUES ('857', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '407405', '1');
INSERT INTO `authbank_cardbin` VALUES ('858', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '421869', '1');
INSERT INTO `authbank_cardbin` VALUES ('859', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '421870', '1');
INSERT INTO `authbank_cardbin` VALUES ('860', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '421871', '1');
INSERT INTO `authbank_cardbin` VALUES ('861', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '512466', '1');
INSERT INTO `authbank_cardbin` VALUES ('862', '民生银行', '3050001', 'CMBC', '民生JCB普卡', '16', '6', '356856', '1');
INSERT INTO `authbank_cardbin` VALUES ('863', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '528948', '1');
INSERT INTO `authbank_cardbin` VALUES ('864', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '552288', '1');
INSERT INTO `authbank_cardbin` VALUES ('865', '民生银行', '3050001', 'CMBC', '民生信用卡(银联卡)', '16', '6', '622600', '1');
INSERT INTO `authbank_cardbin` VALUES ('866', '民生银行', '3050001', 'CMBC', '民生信用卡(银联卡)', '16', '6', '622601', '1');
INSERT INTO `authbank_cardbin` VALUES ('867', '民生银行', '3050001', 'CMBC', '民生银联白金信用卡', '16', '6', '622602', '1');
INSERT INTO `authbank_cardbin` VALUES ('868', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '517636', '1');
INSERT INTO `authbank_cardbin` VALUES ('869', '民生银行', '3050001', 'CMBC', '民生银联个人白金卡', '16', '6', '622621', '1');
INSERT INTO `authbank_cardbin` VALUES ('870', '民生银行', '3050001', 'CMBC', '公务卡金卡', '16', '6', '628258', '1');
INSERT INTO `authbank_cardbin` VALUES ('871', '民生银行', '3050001', 'CMBC', '民生贷记卡(银联卡)', '16', '6', '556610', '1');
INSERT INTO `authbank_cardbin` VALUES ('872', '民生银行', '3050001', 'CMBC', '民生银联商务信用卡', '16', '6', '622603', '1');
INSERT INTO `authbank_cardbin` VALUES ('873', '民生银行', '3050001', 'CMBC', '民VISA无限卡', '16', '6', '464580', '1');
INSERT INTO `authbank_cardbin` VALUES ('874', '民生银行', '3050001', 'CMBC', '民生VISA商务白金卡', '16', '6', '464581', '1');
INSERT INTO `authbank_cardbin` VALUES ('875', '民生银行', '3050001', 'CMBC', '民生万事达钛金卡', '16', '6', '523952', '1');
INSERT INTO `authbank_cardbin` VALUES ('876', '民生银行', '3050001', 'CMBC', '民生万事达世界卡', '16', '6', '545217', '1');
INSERT INTO `authbank_cardbin` VALUES ('877', '民生银行', '3050001', 'CMBC', '民生万事达白金公务卡', '16', '6', '553161', '1');
INSERT INTO `authbank_cardbin` VALUES ('878', '民生银行', '3050001', 'CMBC', '民生JCB白金卡', '16', '6', '356858', '1');
INSERT INTO `authbank_cardbin` VALUES ('879', '民生银行', '3050001', 'CMBC', '银联标准金卡', '16', '6', '622623', '1');
INSERT INTO `authbank_cardbin` VALUES ('880', '民生银行', '3050001', 'CMBC', '银联芯片普卡', '16', '6', '625911', '1');
INSERT INTO `authbank_cardbin` VALUES ('881', '民生银行', '3050001', 'CMBC', '民生运通双币信用卡普卡', '15', '6', '377152', '1');
INSERT INTO `authbank_cardbin` VALUES ('882', '民生银行', '3050001', 'CMBC', '民生运通双币信用卡金卡', '15', '6', '377153', '1');
INSERT INTO `authbank_cardbin` VALUES ('883', '民生银行', '3050001', 'CMBC', '民生运通双币信用卡钻石卡', '15', '6', '377158', '1');
INSERT INTO `authbank_cardbin` VALUES ('884', '民生银行', '3050001', 'CMBC', '民生运通双币标准信用卡白金卡', '15', '6', '377155', '1');
INSERT INTO `authbank_cardbin` VALUES ('885', '民生银行', '3050001', 'CMBC', '银联芯片金卡', '16', '6', '625912', '1');
INSERT INTO `authbank_cardbin` VALUES ('886', '民生银行', '3050001', 'CMBC', '银联芯片白金卡', '16', '6', '625913', '1');
INSERT INTO `authbank_cardbin` VALUES ('887', '广发银行股份有限公司', '3060000', 'GDB', '广发VISA信用卡', '16', '6', '406365', '1');
INSERT INTO `authbank_cardbin` VALUES ('888', '广发银行股份有限公司', '3060000', 'GDB', '广发VISA信用卡', '16', '6', '406366', '1');
INSERT INTO `authbank_cardbin` VALUES ('889', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '428911', '1');
INSERT INTO `authbank_cardbin` VALUES ('890', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '436768', '1');
INSERT INTO `authbank_cardbin` VALUES ('891', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '436769', '1');
INSERT INTO `authbank_cardbin` VALUES ('892', '广发银行股份有限公司', '3060000', 'GDB', '广发VISA信用卡', '16', '6', '487013', '1');
INSERT INTO `authbank_cardbin` VALUES ('893', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491032', '1');
INSERT INTO `authbank_cardbin` VALUES ('894', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491034', '1');
INSERT INTO `authbank_cardbin` VALUES ('895', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491035', '1');
INSERT INTO `authbank_cardbin` VALUES ('896', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491036', '1');
INSERT INTO `authbank_cardbin` VALUES ('897', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491037', '1');
INSERT INTO `authbank_cardbin` VALUES ('898', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491038', '1');
INSERT INTO `authbank_cardbin` VALUES ('899', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '518364', '1');
INSERT INTO `authbank_cardbin` VALUES ('900', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '16', '6', '520152', '1');
INSERT INTO `authbank_cardbin` VALUES ('901', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '16', '6', '520382', '1');
INSERT INTO `authbank_cardbin` VALUES ('902', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '548844', '1');
INSERT INTO `authbank_cardbin` VALUES ('903', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '16', '6', '552794', '1');
INSERT INTO `authbank_cardbin` VALUES ('904', '广发银行股份有限公司', '3060000', 'GDB', '广发银联标准金卡', '16', '6', '622555', '1');
INSERT INTO `authbank_cardbin` VALUES ('905', '广发银行股份有限公司', '3060000', 'GDB', '广发银联标准普卡', '16', '6', '622556', '1');
INSERT INTO `authbank_cardbin` VALUES ('906', '广发银行股份有限公司', '3060000', 'GDB', '广发银联标准真情金卡', '16', '6', '622557', '1');
INSERT INTO `authbank_cardbin` VALUES ('907', '广发银行股份有限公司', '3060000', 'GDB', '广发银联标准白金卡', '16', '6', '622558', '1');
INSERT INTO `authbank_cardbin` VALUES ('908', '广发银行股份有限公司', '3060000', 'GDB', '广发银联标准真情普卡', '16', '6', '622559', '1');
INSERT INTO `authbank_cardbin` VALUES ('909', '广发银行股份有限公司', '3060000', 'GDB', '广发真情白金卡', '16', '6', '622560', '1');
INSERT INTO `authbank_cardbin` VALUES ('910', '广发银行股份有限公司', '3060000', 'GDB', '广发理财通卡', '19', '6', '622568', '0');
INSERT INTO `authbank_cardbin` VALUES ('911', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '16', '6', '528931', '1');
INSERT INTO `authbank_cardbin` VALUES ('912', '广发银行股份有限公司', '3060000', 'GDB', '广发理财通(银联卡)', '19', '4', '9111', '0');
INSERT INTO `authbank_cardbin` VALUES ('913', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '16', '6', '558894', '1');
INSERT INTO `authbank_cardbin` VALUES ('914', '广发银行股份有限公司', '3060000', 'GDB', '银联标准金卡', '16', '6', '625072', '1');
INSERT INTO `authbank_cardbin` VALUES ('915', '广发银行股份有限公司', '3060000', 'GDB', '银联标准普卡', '16', '6', '625071', '1');
INSERT INTO `authbank_cardbin` VALUES ('916', '广发银行股份有限公司', '3060000', 'GDB', '银联公务金卡', '16', '6', '628260', '1');
INSERT INTO `authbank_cardbin` VALUES ('917', '广发银行股份有限公司', '3060000', 'GDB', '银联公务普卡', '16', '6', '628259', '1');
INSERT INTO `authbank_cardbin` VALUES ('918', '广发银行股份有限公司', '3060000', 'GDB', '理财通卡', '19', '6', '621462', '0');
INSERT INTO `authbank_cardbin` VALUES ('919', '广发银行股份有限公司', '3060000', 'GDB', '银联真情普卡', '16', '6', '625805', '1');
INSERT INTO `authbank_cardbin` VALUES ('920', '广发银行股份有限公司', '3060000', 'GDB', '银联真情金卡', '16', '6', '625806', '1');
INSERT INTO `authbank_cardbin` VALUES ('921', '广发银行股份有限公司', '3060000', 'GDB', '银联真情白金卡', '16', '6', '625807', '1');
INSERT INTO `authbank_cardbin` VALUES ('922', '广发银行股份有限公司', '3060000', 'GDB', '银联标准普卡', '16', '6', '625808', '1');
INSERT INTO `authbank_cardbin` VALUES ('923', '广发银行股份有限公司', '3060000', 'GDB', '银联标准金卡', '16', '6', '625809', '1');
INSERT INTO `authbank_cardbin` VALUES ('924', '广发银行股份有限公司', '3060000', 'GDB', '银联标准白金卡', '16', '6', '625810', '1');
INSERT INTO `authbank_cardbin` VALUES ('925', '广发银行股份有限公司', '3060000', 'GDB', '广发万事达信用卡', '19', '6', '685800', '1');
INSERT INTO `authbank_cardbin` VALUES ('926', '广发银行股份有限公司', '3060000', 'GDB', '广发青年银行预付卡', '19', '6', '620037', '2');
INSERT INTO `authbank_cardbin` VALUES ('927', '广发银行股份有限公司', '3060000', 'GDB', '广发理财通', '19', '7', '6858000', '1');
INSERT INTO `authbank_cardbin` VALUES ('928', '广发银行股份有限公司', '3060000', 'GDB', '广发理财通', '19', '7', '6858001', '0');
INSERT INTO `authbank_cardbin` VALUES ('929', '广发银行股份有限公司', '3060000', 'GDB', '广发理财通', '19', '7', '6858009', '0');
INSERT INTO `authbank_cardbin` VALUES ('930', '广发银行股份有限公司', '3060000', 'GDB', '广发财富管理多币IC卡', '19', '6', '623506', '0');
INSERT INTO `authbank_cardbin` VALUES ('931', '广发银行股份有限公司', '3060000', 'GDB', '借记卡', '19', '6', '623259', '0');
INSERT INTO `authbank_cardbin` VALUES ('932', '平安银行', '3070010', 'PINGAN', '发展借记卡', '16', '6', '412963', '0');
INSERT INTO `authbank_cardbin` VALUES ('933', '平安银行', '3070010', 'PINGAN', '国际借记卡', '16', '6', '415752', '0');
INSERT INTO `authbank_cardbin` VALUES ('934', '平安银行', '3070010', 'PINGAN', '国际借记卡', '16', '6', '415753', '0');
INSERT INTO `authbank_cardbin` VALUES ('935', '平安银行', '3070010', 'PINGAN', '聚财卡金卡', '16', '6', '622535', '0');
INSERT INTO `authbank_cardbin` VALUES ('936', '平安银行', '3070010', 'PINGAN', '聚财卡VIP金卡', '16', '6', '622536', '0');
INSERT INTO `authbank_cardbin` VALUES ('937', '平安银行', '3070010', 'PINGAN', '发展卡(银联卡)', '16', '6', '622538', '0');
INSERT INTO `authbank_cardbin` VALUES ('938', '平安银行', '3070010', 'PINGAN', '聚财卡白金卡和钻石卡', '16', '6', '622539', '0');
INSERT INTO `authbank_cardbin` VALUES ('939', '平安银行', '3070010', 'PINGAN', '发展借记卡(银联卡)', '16', '6', '998800', '0');
INSERT INTO `authbank_cardbin` VALUES ('940', '平安银行', '3070010', 'PINGAN', '发展借记卡', '16', '6', '412962', '0');
INSERT INTO `authbank_cardbin` VALUES ('941', '平安银行', '3070010', 'PINGAN', '聚财卡钻石卡', '16', '6', '622983', '0');
INSERT INTO `authbank_cardbin` VALUES ('942', '平安银行', '3070010', 'PINGAN', '公益预付卡', '16', '6', '620010', '2');
INSERT INTO `authbank_cardbin` VALUES ('943', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356885', '1');
INSERT INTO `authbank_cardbin` VALUES ('944', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356886', '1');
INSERT INTO `authbank_cardbin` VALUES ('945', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356887', '1');
INSERT INTO `authbank_cardbin` VALUES ('946', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356888', '1');
INSERT INTO `authbank_cardbin` VALUES ('947', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356890', '1');
INSERT INTO `authbank_cardbin` VALUES ('948', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '439188', '1');
INSERT INTO `authbank_cardbin` VALUES ('949', '招商银行', '3080000', 'CMB', 'VISA商务信用卡', '16', '6', '439227', '1');
INSERT INTO `authbank_cardbin` VALUES ('950', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '479229', '1');
INSERT INTO `authbank_cardbin` VALUES ('951', '招商银行', '3080000', 'CMB', '世纪金花联名信用卡', '16', '6', '521302', '1');
INSERT INTO `authbank_cardbin` VALUES ('952', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '356889', '1');
INSERT INTO `authbank_cardbin` VALUES ('953', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '552534', '1');
INSERT INTO `authbank_cardbin` VALUES ('954', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622575', '1');
INSERT INTO `authbank_cardbin` VALUES ('955', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622576', '1');
INSERT INTO `authbank_cardbin` VALUES ('956', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622581', '1');
INSERT INTO `authbank_cardbin` VALUES ('957', '招商银行', '3080000', 'CMB', '招行一卡通', '15', '6', '690755', '0');
INSERT INTO `authbank_cardbin` VALUES ('958', '招商银行', '3080000', 'CMB', '一卡通(银联卡)', '16', '5', '95555', '0');
INSERT INTO `authbank_cardbin` VALUES ('959', '招商银行', '3080000', 'CMB', 'IC公务卡', '16', '6', '628290', '1');
INSERT INTO `authbank_cardbin` VALUES ('960', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622578', '1');
INSERT INTO `authbank_cardbin` VALUES ('961', '招商银行', '3080000', 'CMB', '两地一卡通', '16', '6', '402658', '0');
INSERT INTO `authbank_cardbin` VALUES ('962', '招商银行', '3080000', 'CMB', '招行国际卡(银联卡)', '16', '6', '410062', '0');
INSERT INTO `authbank_cardbin` VALUES ('963', '招商银行', '3080000', 'CMB', '招行国际卡(银联卡)', '16', '6', '468203', '0');
INSERT INTO `authbank_cardbin` VALUES ('964', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '479228', '1');
INSERT INTO `authbank_cardbin` VALUES ('965', '招商银行', '3080000', 'CMB', '招行国际卡(银联卡)', '16', '6', '512425', '0');
INSERT INTO `authbank_cardbin` VALUES ('966', '招商银行', '3080000', 'CMB', '招行国际卡(银联卡)', '16', '6', '524011', '0');
INSERT INTO `authbank_cardbin` VALUES ('967', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545619', '1');
INSERT INTO `authbank_cardbin` VALUES ('968', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545620', '1');
INSERT INTO `authbank_cardbin` VALUES ('969', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545621', '1');
INSERT INTO `authbank_cardbin` VALUES ('970', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545623', '1');
INSERT INTO `authbank_cardbin` VALUES ('971', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545947', '1');
INSERT INTO `authbank_cardbin` VALUES ('972', '招商银行', '3080000', 'CMB', '万事达信用卡', '16', '6', '545948', '1');
INSERT INTO `authbank_cardbin` VALUES ('973', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '552587', '1');
INSERT INTO `authbank_cardbin` VALUES ('974', '招商银行', '3080000', 'CMB', '电子现金卡', '19', '6', '620520', '2');
INSERT INTO `authbank_cardbin` VALUES ('975', '招商银行', '3080000', 'CMB', '金葵花卡', '16', '6', '621286', '0');
INSERT INTO `authbank_cardbin` VALUES ('976', '招商银行', '3080000', 'CMB', '银联IC普卡', '16', '6', '621483', '0');
INSERT INTO `authbank_cardbin` VALUES ('977', '招商银行', '3080000', 'CMB', '银联IC金卡', '16', '6', '621485', '0');
INSERT INTO `authbank_cardbin` VALUES ('978', '招商银行', '3080000', 'CMB', '银联金葵花IC卡', '16', '6', '621486', '0');
INSERT INTO `authbank_cardbin` VALUES ('979', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622577', '1');
INSERT INTO `authbank_cardbin` VALUES ('980', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622579', '1');
INSERT INTO `authbank_cardbin` VALUES ('981', '招商银行', '3080000', 'CMB', '一卡通(银联卡)', '16', '6', '622580', '0');
INSERT INTO `authbank_cardbin` VALUES ('982', '招商银行', '3080000', 'CMB', '招商银行信用卡', '16', '6', '622582', '1');
INSERT INTO `authbank_cardbin` VALUES ('983', '招商银行', '3080000', 'CMB', '一卡通(银联卡)', '16', '6', '622588', '0');
INSERT INTO `authbank_cardbin` VALUES ('984', '招商银行', '3080000', 'CMB', '公司卡(银联卡)', '16', '6', '622598', '0');
INSERT INTO `authbank_cardbin` VALUES ('985', '招商银行', '3080000', 'CMB', '金卡', '16', '6', '622609', '0');
INSERT INTO `authbank_cardbin` VALUES ('986', '招商银行', '3080000', 'CMB', '招行一卡通', '18', '6', '690755', '0');
INSERT INTO `authbank_cardbin` VALUES ('987', '招商银行', '3080010', 'CMB', '美国运通绿卡', '15', '6', '370285', '1');
INSERT INTO `authbank_cardbin` VALUES ('988', '招商银行', '3080010', 'CMB', '美国运通金卡', '15', '6', '370286', '1');
INSERT INTO `authbank_cardbin` VALUES ('989', '招商银行', '3080010', 'CMB', '美国运通商务绿卡', '15', '6', '370287', '1');
INSERT INTO `authbank_cardbin` VALUES ('990', '招商银行', '3080010', 'CMB', '美国运通商务金卡', '15', '6', '370289', '1');
INSERT INTO `authbank_cardbin` VALUES ('991', '招商银行', '3080010', 'CMB', 'VISA信用卡', '16', '6', '439225', '1');
INSERT INTO `authbank_cardbin` VALUES ('992', '招商银行', '3080010', 'CMB', 'MASTER信用卡', '16', '6', '518710', '1');
INSERT INTO `authbank_cardbin` VALUES ('993', '招商银行', '3080010', 'CMB', 'MASTER信用金卡', '16', '6', '518718', '1');
INSERT INTO `authbank_cardbin` VALUES ('994', '招商银行', '3080010', 'CMB', '银联标准公务卡(金卡)', '16', '6', '628362', '1');
INSERT INTO `authbank_cardbin` VALUES ('995', '招商银行', '3080010', 'CMB', 'VISA信用卡', '16', '6', '439226', '1');
INSERT INTO `authbank_cardbin` VALUES ('996', '招商银行', '3080010', 'CMB', '银联标准财政公务卡', '16', '6', '628262', '1');
INSERT INTO `authbank_cardbin` VALUES ('997', '招商银行', '3080010', 'CMB', '芯片IC信用卡', '16', '6', '625802', '1');
INSERT INTO `authbank_cardbin` VALUES ('998', '招商银行', '3080010', 'CMB', '芯片IC信用卡', '16', '6', '625803', '1');
INSERT INTO `authbank_cardbin` VALUES ('999', '招商银行', '3080344', 'CMB', '香港一卡通', '16', '6', '621299', '0');
INSERT INTO `authbank_cardbin` VALUES ('1000', '兴业银行', '3090000', 'CIB', '兴业卡', '16', '5', '90592', '0');
INSERT INTO `authbank_cardbin` VALUES ('1001', '兴业银行', '3090000', 'CIB', '兴业卡(银联卡)', '18', '6', '966666', '0');
INSERT INTO `authbank_cardbin` VALUES ('1002', '兴业银行', '3090000', 'CIB', '兴业卡(银联标准卡)', '18', '6', '622909', '0');
INSERT INTO `authbank_cardbin` VALUES ('1003', '兴业银行', '3090000', 'CIB', '兴业自然人生理财卡', '18', '6', '622908', '0');
INSERT INTO `authbank_cardbin` VALUES ('1004', '兴业银行', '3090000', 'CIB', '兴业智能卡(银联卡)', '18', '6', '438588', '0');
INSERT INTO `authbank_cardbin` VALUES ('1005', '兴业银行', '3090000', 'CIB', '兴业智能卡', '18', '6', '438589', '0');
INSERT INTO `authbank_cardbin` VALUES ('1006', '兴业银行', '3090010', 'CIB', 'visa标准双币个人普卡', '16', '6', '461982', '1');
INSERT INTO `authbank_cardbin` VALUES ('1007', '兴业银行', '3090010', 'CIB', 'VISA商务普卡', '16', '6', '486493', '1');
INSERT INTO `authbank_cardbin` VALUES ('1008', '兴业银行', '3090010', 'CIB', 'VISA商务金卡', '16', '6', '486494', '1');
INSERT INTO `authbank_cardbin` VALUES ('1009', '兴业银行', '3090010', 'CIB', 'VISA运动白金信用卡', '16', '6', '486861', '1');
INSERT INTO `authbank_cardbin` VALUES ('1010', '兴业银行', '3090010', 'CIB', '万事达信用卡(银联卡)', '16', '6', '523036', '1');
INSERT INTO `authbank_cardbin` VALUES ('1011', '兴业银行', '3090010', 'CIB', 'VISA信用卡(银联卡)', '16', '6', '451289', '1');
INSERT INTO `authbank_cardbin` VALUES ('1012', '兴业银行', '3090010', 'CIB', '加菲猫信用卡', '16', '6', '527414', '1');
INSERT INTO `authbank_cardbin` VALUES ('1013', '兴业银行', '3090010', 'CIB', '个人白金卡', '16', '6', '528057', '1');
INSERT INTO `authbank_cardbin` VALUES ('1014', '兴业银行', '3090010', 'CIB', '银联信用卡(银联卡)', '16', '6', '622901', '1');
INSERT INTO `authbank_cardbin` VALUES ('1015', '兴业银行', '3090010', 'CIB', '银联信用卡(银联卡)', '16', '6', '622902', '1');
INSERT INTO `authbank_cardbin` VALUES ('1016', '兴业银行', '3090010', 'CIB', '银联白金信用卡', '16', '6', '622922', '1');
INSERT INTO `authbank_cardbin` VALUES ('1017', '兴业银行', '3090010', 'CIB', '银联标准公务卡', '16', '6', '628212', '1');
INSERT INTO `authbank_cardbin` VALUES ('1018', '兴业银行', '3090010', 'CIB', 'VISA信用卡(银联卡)', '16', '6', '451290', '1');
INSERT INTO `authbank_cardbin` VALUES ('1019', '兴业银行', '3090010', 'CIB', '万事达信用卡(银联卡)', '16', '6', '524070', '1');
INSERT INTO `authbank_cardbin` VALUES ('1020', '兴业银行', '3090010', 'CIB', '银联标准贷记普卡', '16', '6', '625084', '1');
INSERT INTO `authbank_cardbin` VALUES ('1021', '兴业银行', '3090010', 'CIB', '银联标准贷记金卡', '16', '6', '625085', '1');
INSERT INTO `authbank_cardbin` VALUES ('1022', '兴业银行', '3090010', 'CIB', '银联标准贷记金卡', '16', '6', '625086', '1');
INSERT INTO `authbank_cardbin` VALUES ('1023', '兴业银行', '3090010', 'CIB', '银联标准贷记金卡', '16', '6', '625087', '1');
INSERT INTO `authbank_cardbin` VALUES ('1024', '兴业银行', '3090010', 'CIB', '兴业信用卡', '16', '6', '548738', '1');
INSERT INTO `authbank_cardbin` VALUES ('1025', '兴业银行', '3090010', 'CIB', '兴业信用卡', '16', '6', '549633', '1');
INSERT INTO `authbank_cardbin` VALUES ('1026', '兴业银行', '3090010', 'CIB', '兴业信用卡', '16', '6', '552398', '1');
INSERT INTO `authbank_cardbin` VALUES ('1027', '兴业银行', '3090010', 'CIB', '银联标准贷记普卡', '16', '6', '625082', '1');
INSERT INTO `authbank_cardbin` VALUES ('1028', '兴业银行', '3090010', 'CIB', '银联标准贷记普卡', '16', '6', '625083', '1');
INSERT INTO `authbank_cardbin` VALUES ('1029', '兴业银行', '3090010', 'CIB', '兴业芯片普卡', '16', '6', '625960', '1');
INSERT INTO `authbank_cardbin` VALUES ('1030', '兴业银行', '3090010', 'CIB', '兴业芯片金卡', '16', '6', '625961', '1');
INSERT INTO `authbank_cardbin` VALUES ('1031', '兴业银行', '3090010', 'CIB', '兴业芯片白金卡', '16', '6', '625962', '1');
INSERT INTO `authbank_cardbin` VALUES ('1032', '兴业银行', '3090010', 'CIB', '兴业芯片钻石卡', '16', '6', '625963', '1');
INSERT INTO `authbank_cardbin` VALUES ('1033', '兴业银行', '3090010', 'CIB', '--', '16', '6', '625353', '1');
INSERT INTO `authbank_cardbin` VALUES ('1034', '兴业银行', '3090010', 'CIB', '--', '16', '6', '625356', '1');
INSERT INTO `authbank_cardbin` VALUES ('1035', '浦东发展银行', '3100000', 'SPDB', '浦发JCB金卡', '16', '6', '356851', '1');
INSERT INTO `authbank_cardbin` VALUES ('1036', '浦东发展银行', '3100000', 'SPDB', '浦发JCB白金卡', '16', '6', '356852', '1');
INSERT INTO `authbank_cardbin` VALUES ('1037', '浦东发展银行', '3100000', 'SPDB', '信用卡VISA普通', '16', '6', '404738', '1');
INSERT INTO `authbank_cardbin` VALUES ('1038', '浦东发展银行', '3100000', 'SPDB', '信用卡VISA金卡', '16', '6', '404739', '1');
INSERT INTO `authbank_cardbin` VALUES ('1039', '浦东发展银行', '3100000', 'SPDB', '浦发银行VISA年青卡', '16', '6', '456418', '1');
INSERT INTO `authbank_cardbin` VALUES ('1040', '浦东发展银行', '3100000', 'SPDB', 'VISA白金信用卡', '16', '6', '498451', '1');
INSERT INTO `authbank_cardbin` VALUES ('1041', '浦东发展银行', '3100000', 'SPDB', '浦发万事达白金卡', '16', '6', '515672', '1');
INSERT INTO `authbank_cardbin` VALUES ('1042', '浦东发展银行', '3100000', 'SPDB', '浦发JCB普卡', '16', '6', '356850', '1');
INSERT INTO `authbank_cardbin` VALUES ('1043', '浦东发展银行', '3100000', 'SPDB', '浦发万事达金卡', '16', '6', '517650', '1');
INSERT INTO `authbank_cardbin` VALUES ('1044', '浦东发展银行', '3100000', 'SPDB', '浦发万事达普卡', '16', '6', '525998', '1');
INSERT INTO `authbank_cardbin` VALUES ('1045', '浦东发展银行', '3100000', 'SPDB', '浦发单币卡', '16', '6', '622177', '1');
INSERT INTO `authbank_cardbin` VALUES ('1046', '浦东发展银行', '3100000', 'SPDB', '浦发银联单币麦兜普卡', '16', '6', '622277', '1');
INSERT INTO `authbank_cardbin` VALUES ('1047', '浦东发展银行', '3100000', 'SPDB', '东方轻松理财卡', '16', '6', '622516', '0');
INSERT INTO `authbank_cardbin` VALUES ('1048', '浦东发展银行', '3100000', 'SPDB', '东方-轻松理财卡普卡', '16', '6', '622517', '0');
INSERT INTO `authbank_cardbin` VALUES ('1049', '浦东发展银行', '3100000', 'SPDB', '东方轻松理财卡', '16', '6', '622518', '0');
INSERT INTO `authbank_cardbin` VALUES ('1050', '浦东发展银行', '3100000', 'SPDB', '东方轻松理财智业金卡', '16', '6', '622520', '3');
INSERT INTO `authbank_cardbin` VALUES ('1051', '浦东发展银行', '3100000', 'SPDB', '东方卡(银联卡)', '16', '6', '622521', '0');
INSERT INTO `authbank_cardbin` VALUES ('1052', '浦东发展银行', '3100000', 'SPDB', '东方卡(银联卡)', '16', '6', '622522', '0');
INSERT INTO `authbank_cardbin` VALUES ('1053', '浦东发展银行', '3100000', 'SPDB', '东方卡(银联卡)', '16', '6', '622523', '0');
INSERT INTO `authbank_cardbin` VALUES ('1054', '浦东发展银行', '3100000', 'SPDB', '公务卡金卡', '16', '6', '628222', '1');
INSERT INTO `authbank_cardbin` VALUES ('1055', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84301', '0');
INSERT INTO `authbank_cardbin` VALUES ('1056', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84336', '0');
INSERT INTO `authbank_cardbin` VALUES ('1057', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84373', '0');
INSERT INTO `authbank_cardbin` VALUES ('1058', '浦东发展银行', '3100000', 'SPDB', '公务卡普卡', '16', '6', '628221', '1');
INSERT INTO `authbank_cardbin` VALUES ('1059', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84385', '0');
INSERT INTO `authbank_cardbin` VALUES ('1060', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84390', '0');
INSERT INTO `authbank_cardbin` VALUES ('1061', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '87000', '0');
INSERT INTO `authbank_cardbin` VALUES ('1062', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '87010', '0');
INSERT INTO `authbank_cardbin` VALUES ('1063', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '87030', '0');
INSERT INTO `authbank_cardbin` VALUES ('1064', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '87040', '0');
INSERT INTO `authbank_cardbin` VALUES ('1065', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84380', '0');
INSERT INTO `authbank_cardbin` VALUES ('1066', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '6', '984301', '0');
INSERT INTO `authbank_cardbin` VALUES ('1067', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '6', '984303', '0');
INSERT INTO `authbank_cardbin` VALUES ('1068', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84361', '0');
INSERT INTO `authbank_cardbin` VALUES ('1069', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '87050', '0');
INSERT INTO `authbank_cardbin` VALUES ('1070', '浦东发展银行', '3100000', 'SPDB', '浦发单币卡', '16', '6', '622176', '1');
INSERT INTO `authbank_cardbin` VALUES ('1071', '浦东发展银行', '3100000', 'SPDB', '浦发联名信用卡', '16', '6', '622276', '1');
INSERT INTO `authbank_cardbin` VALUES ('1072', '浦东发展银行', '3100000', 'SPDB', '浦发银联白金卡', '16', '6', '622228', '1');
INSERT INTO `authbank_cardbin` VALUES ('1073', '浦东发展银行', '3100000', 'SPDB', '轻松理财普卡', '16', '6', '621352', '0');
INSERT INTO `authbank_cardbin` VALUES ('1074', '浦东发展银行', '3100000', 'SPDB', '移动联名卡', '16', '6', '621351', '0');
INSERT INTO `authbank_cardbin` VALUES ('1075', '浦东发展银行', '3100000', 'SPDB', '轻松理财消贷易卡', '16', '6', '621390', '0');
INSERT INTO `authbank_cardbin` VALUES ('1076', '浦东发展银行', '3100000', 'SPDB', '轻松理财普卡（复合卡）', '16', '6', '621792', '0');
INSERT INTO `authbank_cardbin` VALUES ('1077', '浦东发展银行', '3100000', 'SPDB', '贷记卡', '16', '6', '625957', '1');
INSERT INTO `authbank_cardbin` VALUES ('1078', '浦东发展银行', '3100000', 'SPDB', '贷记卡', '16', '6', '625958', '1');
INSERT INTO `authbank_cardbin` VALUES ('1079', '浦东发展银行', '3100000', 'SPDB', '东方借记卡（复合卡）', '16', '6', '621791', '0');
INSERT INTO `authbank_cardbin` VALUES ('1080', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '5', '84342', '0');
INSERT INTO `authbank_cardbin` VALUES ('1081', '浦东发展银行', '3100000', 'SPDB', '电子现金卡（IC卡）', '19', '6', '620530', '2');
INSERT INTO `authbank_cardbin` VALUES ('1082', '浦东发展银行', '3100000', 'SPDB', '移动浦发联名卡', '16', '6', '625993', '1');
INSERT INTO `authbank_cardbin` VALUES ('1083', '浦东发展银行', '3100000', 'SPDB', '东方-标准准贷记卡', '16', '6', '622519', '3');
INSERT INTO `authbank_cardbin` VALUES ('1084', '浦东发展银行', '3100000', 'SPDB', '轻松理财金卡（复合卡）', '16', '6', '621793', '0');
INSERT INTO `authbank_cardbin` VALUES ('1085', '浦东发展银行', '3100000', 'SPDB', '轻松理财白金卡（复合卡）', '16', '6', '621795', '0');
INSERT INTO `authbank_cardbin` VALUES ('1086', '浦东发展银行', '3100000', 'SPDB', '轻松理财钻石卡（复合卡）', '16', '6', '621796', '0');
INSERT INTO `authbank_cardbin` VALUES ('1087', '浦东发展银行', '3100000', 'SPDB', '东方卡', '16', '6', '622500', '1');
INSERT INTO `authbank_cardbin` VALUES ('1088', '浦东发展银行', '3100000', 'SPDB', '轻松理财米卡（复合卡）', '16', '6', '623250', '0');
INSERT INTO `authbank_cardbin` VALUES ('1089', '恒丰银行', '3110000', '', '九州IC卡', '19', '6', '623078', '0');
INSERT INTO `authbank_cardbin` VALUES ('1090', '恒丰银行', '3114560', '', '九州借记卡(银联卡)', '17', '6', '622384', '0');
INSERT INTO `authbank_cardbin` VALUES ('1091', '恒丰银行', '3114560', '', '九州借记卡(银联卡)', '17', '6', '940034', '0');
INSERT INTO `authbank_cardbin` VALUES ('1092', '天津市商业银行', '3131100', '', '津卡', '18', '7', '6091201', '0');
INSERT INTO `authbank_cardbin` VALUES ('1093', '天津市商业银行', '3131100', '', '银联卡(银联卡)', '18', '6', '940015', '0');
INSERT INTO `authbank_cardbin` VALUES ('1094', '齐鲁银行股份有限公司', '3134500', '', '齐鲁卡(银联卡)', '19', '6', '940008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1095', '齐鲁银行股份有限公司', '3134510', '', '齐鲁卡(银联卡)', '19', '6', '622379', '0');
INSERT INTO `authbank_cardbin` VALUES ('1096', '烟台商业银行', '3134560', '', '金通卡', '16', '6', '622886', '0');
INSERT INTO `authbank_cardbin` VALUES ('1097', '潍坊银行', '3134580', '', '鸢都卡(银联卡)', '16', '6', '622391', '0');
INSERT INTO `authbank_cardbin` VALUES ('1098', '潍坊银行', '3134580', '', '鸳都卡(银联卡)', '16', '6', '940072', '0');
INSERT INTO `authbank_cardbin` VALUES ('1099', '临沂商业银行', '3134730', '', '沂蒙卡(银联卡)', '19', '6', '622359', '0');
INSERT INTO `authbank_cardbin` VALUES ('1100', '临沂商业银行', '3134730', '', '沂蒙卡(银联卡)', '19', '6', '940066', '0');
INSERT INTO `authbank_cardbin` VALUES ('1101', '日照市商业银行', '3134732', '', '黄海卡', '19', '6', '622857', '0');
INSERT INTO `authbank_cardbin` VALUES ('1102', '日照市商业银行', '3134732', '', '黄海卡(银联卡)', '19', '6', '940065', '0');
INSERT INTO `authbank_cardbin` VALUES ('1103', '浙商银行', '3160000', '', '商卡', '19', '6', '621019', '0');
INSERT INTO `authbank_cardbin` VALUES ('1104', '浙商银行', '3160000', '', '商卡', '19', '6', '622309', '0');
INSERT INTO `authbank_cardbin` VALUES ('1105', '浙商银行天津分行', '3161100', '', '商卡', '19', '10', '6223091100', '0');
INSERT INTO `authbank_cardbin` VALUES ('1106', '浙商银行上海分行', '3162900', '', '商卡', '19', '10', '6223092900', '0');
INSERT INTO `authbank_cardbin` VALUES ('1107', '浙商银行营业部', '3163310', '', '商卡(银联卡)', '19', '10', '6223093310', '0');
INSERT INTO `authbank_cardbin` VALUES ('1108', '浙商银行宁波分行', '3163320', '', '商卡(银联卡)', '19', '10', '6223093320', '0');
INSERT INTO `authbank_cardbin` VALUES ('1109', '浙商银行温州分行', '3163330', '', '商卡(银联卡)', '19', '10', '6223093330', '0');
INSERT INTO `authbank_cardbin` VALUES ('1110', '浙商银行绍兴分行', '3163370', '', '商卡', '19', '10', '6223093370', '0');
INSERT INTO `authbank_cardbin` VALUES ('1111', '浙商银行义乌分行', '3163380', '', '商卡(银联卡)', '19', '10', '6223093380', '0');
INSERT INTO `authbank_cardbin` VALUES ('1112', '浙商银行成都分行', '3166510', '', '商卡(银联卡)', '19', '10', '6223096510', '0');
INSERT INTO `authbank_cardbin` VALUES ('1113', '浙商银行西安分行', '3167910', '', '商卡', '19', '10', '6223097910', '0');
INSERT INTO `authbank_cardbin` VALUES ('1114', '渤海银行', '3170000', '', '浩瀚金卡', '16', '6', '621268', '0');
INSERT INTO `authbank_cardbin` VALUES ('1115', '渤海银行', '3170000', '', '渤海银行借记卡', '16', '6', '622884', '0');
INSERT INTO `authbank_cardbin` VALUES ('1116', '渤海银行', '3170000', '', '金融IC卡', '16', '6', '621453', '0');
INSERT INTO `authbank_cardbin` VALUES ('1117', '渤海银行', '3170000', '', '渤海银行公司借记卡', '16', '6', '622684', '0');
INSERT INTO `authbank_cardbin` VALUES ('1118', '花旗银行(中国)有限公司', '3190001', '', '借记卡普卡', '16', '6', '621062', '0');
INSERT INTO `authbank_cardbin` VALUES ('1119', '花旗银行(中国)有限公司', '3190001', '', '借记卡高端卡', '16', '6', '621063', '0');
INSERT INTO `authbank_cardbin` VALUES ('1120', '花旗中国', '3190002', '', '花旗礼享卡', '16', '6', '625076', '1');
INSERT INTO `authbank_cardbin` VALUES ('1121', '花旗中国', '3190002', '', '花旗礼享卡', '16', '6', '625077', '1');
INSERT INTO `authbank_cardbin` VALUES ('1122', '花旗中国', '3190003', '', '花旗礼享卡', '16', '6', '625074', '1');
INSERT INTO `authbank_cardbin` VALUES ('1123', '花旗中国', '3190003', '', '花旗礼享卡', '16', '6', '625075', '1');
INSERT INTO `authbank_cardbin` VALUES ('1124', '东亚银行中国有限公司', '3200000', '', '紫荆卡', '19', '6', '622933', '0');
INSERT INTO `authbank_cardbin` VALUES ('1125', '东亚银行中国有限公司', '3200000', '', '显卓理财卡', '19', '6', '622938', '0');
INSERT INTO `authbank_cardbin` VALUES ('1126', '东亚银行中国有限公司', '3200000', '', '借记卡', '19', '6', '623031', '0');
INSERT INTO `authbank_cardbin` VALUES ('1127', '汇丰银(中国)有限公司', '3210000', '', '汇丰中国卓越理财卡', '16', '6', '622946', '0');
INSERT INTO `authbank_cardbin` VALUES ('1128', '渣打银行中国有限公司', '3220000', '', '渣打银行智通借记卡', '16', '6', '622942', '0');
INSERT INTO `authbank_cardbin` VALUES ('1129', '渣打银行中国有限公司', '3220000', '', '渣打银行白金借记卡', '16', '6', '622994', '0');
INSERT INTO `authbank_cardbin` VALUES ('1130', '渣打银行(中国)', '3220001', '', '贷记卡', '16', '6', '622583', '1');
INSERT INTO `authbank_cardbin` VALUES ('1131', '渣打银行(中国)', '3220001', '', '贷记卡', '16', '6', '622584', '1');
INSERT INTO `authbank_cardbin` VALUES ('1132', '星展银行', '3240000', '', '星展银行借记卡', '19', '6', '621016', '0');
INSERT INTO `authbank_cardbin` VALUES ('1133', '星展银行', '3240000', '', '星展银行借记卡', '19', '6', '621015', '0');
INSERT INTO `authbank_cardbin` VALUES ('1134', '恒生银行', '3260000', '', '恒生通财卡', '16', '6', '622950', '0');
INSERT INTO `authbank_cardbin` VALUES ('1135', '恒生银行', '3260000', '', '恒生优越通财卡', '16', '6', '622951', '0');
INSERT INTO `authbank_cardbin` VALUES ('1136', '友利银行(中国)有限公司', '3270000', '', '友利借记卡', '19', '6', '621060', '0');
INSERT INTO `authbank_cardbin` VALUES ('1137', '新韩银行', '3280000', '', '新韩卡', '19', '6', '621072', '0');
INSERT INTO `authbank_cardbin` VALUES ('1138', '韩亚银行（中国）', '3290000', '', '韩亚卡', '16', '6', '621201', '0');
INSERT INTO `authbank_cardbin` VALUES ('1139', '华侨银行（中国）', '3300000', '', '卓锦借记卡', '16', '6', '621077', '0');
INSERT INTO `authbank_cardbin` VALUES ('1140', '永亨银行（中国）有限公司', '3310000', '', '永亨卡', '18', '6', '621298', '0');
INSERT INTO `authbank_cardbin` VALUES ('1141', '南洋商业银行（中国）', '3320000', '', '借记卡', '19', '6', '621213', '0');
INSERT INTO `authbank_cardbin` VALUES ('1142', '南洋商业银行（中国）', '3320000', '', '财互通卡', '19', '6', '621289', '0');
INSERT INTO `authbank_cardbin` VALUES ('1143', '南洋商业银行（中国）', '3320000', '', '财互通卡', '19', '6', '621290', '0');
INSERT INTO `authbank_cardbin` VALUES ('1144', '南洋商业银行（中国）', '3320000', '', '财互通卡', '19', '6', '621291', '0');
INSERT INTO `authbank_cardbin` VALUES ('1145', '南洋商业银行（中国）', '3320000', '', '财互通卡', '19', '6', '621292', '0');
INSERT INTO `authbank_cardbin` VALUES ('1146', '法国兴业银行（中国）', '3330001', 'CIB', '法兴标准借记卡', '16', '6', '621245', '0');
INSERT INTO `authbank_cardbin` VALUES ('1147', '大华银行（中国）', '3340000', '', '尊享理财卡', '19', '6', '621328', '0');
INSERT INTO `authbank_cardbin` VALUES ('1148', '大新银行（中国）', '3350000', '', '借记卡', '16', '6', '621277', '0');
INSERT INTO `authbank_cardbin` VALUES ('1149', '企业银行（中国）', '3360000', '', '瑞卡', '19', '6', '621651', '0');
INSERT INTO `authbank_cardbin` VALUES ('1150', '上海银行', '4010000', 'SHB', '慧通钻石卡', '18', '6', '623183', '0');
INSERT INTO `authbank_cardbin` VALUES ('1151', '上海银行', '4010000', 'SHB', '慧通金卡', '18', '6', '623185', '0');
INSERT INTO `authbank_cardbin` VALUES ('1152', '上海银行', '4010000', 'SHB', '私人银行卡', '18', '6', '621005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1153', '上海银行', '4012900', 'SHB', '综合保险卡', '18', '6', '622172', '0');
INSERT INTO `authbank_cardbin` VALUES ('1154', '上海银行', '4012900', 'SHB', '申卡社保副卡(有折)', '18', '6', '622985', '0');
INSERT INTO `authbank_cardbin` VALUES ('1155', '上海银行', '4012900', 'SHB', '申卡社保副卡(无折)', '18', '6', '622987', '0');
INSERT INTO `authbank_cardbin` VALUES ('1156', '上海银行', '4012900', 'SHB', '白金IC借记卡', '18', '6', '622267', '0');
INSERT INTO `authbank_cardbin` VALUES ('1157', '上海银行', '4012900', 'SHB', '慧通白金卡(配折)', '18', '6', '622278', '0');
INSERT INTO `authbank_cardbin` VALUES ('1158', '上海银行', '4012900', 'SHB', '慧通白金卡(不配折)', '18', '6', '622279', '0');
INSERT INTO `authbank_cardbin` VALUES ('1159', '上海银行', '4012900', 'SHB', '申卡(银联卡)', '18', '6', '622468', '0');
INSERT INTO `authbank_cardbin` VALUES ('1160', '上海银行', '4012900', 'SHB', '申卡借记卡', '18', '6', '622892', '0');
INSERT INTO `authbank_cardbin` VALUES ('1161', '上海银行', '4012900', 'SHB', '银联申卡(银联卡)', '18', '6', '940021', '0');
INSERT INTO `authbank_cardbin` VALUES ('1162', '上海银行', '4012900', 'SHB', '单位借记卡', '18', '6', '621050', '0');
INSERT INTO `authbank_cardbin` VALUES ('1163', '上海银行', '4012900', 'SHB', '首发纪念版IC卡', '18', '6', '620522', '0');
INSERT INTO `authbank_cardbin` VALUES ('1164', '上海银行', '4012902', 'SHB', '申卡贷记卡', '16', '6', '356827', '1');
INSERT INTO `authbank_cardbin` VALUES ('1165', '上海银行', '4012902', 'SHB', '申卡贷记卡', '16', '6', '356828', '1');
INSERT INTO `authbank_cardbin` VALUES ('1166', '上海银行', '4012902', 'SHB', 'J分期付款信用卡', '16', '6', '356830', '1');
INSERT INTO `authbank_cardbin` VALUES ('1167', '上海银行', '4012902', 'SHB', '申卡贷记卡', '16', '6', '402673', '1');
INSERT INTO `authbank_cardbin` VALUES ('1168', '上海银行', '4012902', 'SHB', '申卡贷记卡', '16', '6', '402674', '1');
INSERT INTO `authbank_cardbin` VALUES ('1169', '上海银行', '4012902', 'SHB', '上海申卡IC', '16', '6', '438600', '0');
INSERT INTO `authbank_cardbin` VALUES ('1170', '上海银行', '4012902', 'SHB', '申卡贷记卡', '16', '6', '486466', '1');
INSERT INTO `authbank_cardbin` VALUES ('1171', '上海银行', '4012902', 'SHB', '申卡贷记卡普通卡', '16', '6', '519498', '1');
INSERT INTO `authbank_cardbin` VALUES ('1172', '上海银行', '4012902', 'SHB', '申卡贷记卡金卡', '16', '6', '520131', '1');
INSERT INTO `authbank_cardbin` VALUES ('1173', '上海银行', '4012902', 'SHB', '万事达白金卡', '16', '6', '524031', '1');
INSERT INTO `authbank_cardbin` VALUES ('1174', '上海银行', '4012902', 'SHB', '万事达星运卡', '16', '6', '548838', '1');
INSERT INTO `authbank_cardbin` VALUES ('1175', '上海银行', '4012902', 'SHB', '申卡贷记卡金卡', '16', '6', '622148', '1');
INSERT INTO `authbank_cardbin` VALUES ('1176', '上海银行', '4012902', 'SHB', '申卡贷记卡普通卡', '16', '6', '622149', '1');
INSERT INTO `authbank_cardbin` VALUES ('1177', '上海银行', '4012902', 'SHB', '安融卡', '16', '6', '622268', '1');
INSERT INTO `authbank_cardbin` VALUES ('1178', '上海银行', '4012902', 'SHB', '分期付款信用卡', '16', '6', '356829', '1');
INSERT INTO `authbank_cardbin` VALUES ('1179', '上海银行', '4012902', 'SHB', '信用卡', '16', '6', '622300', '1');
INSERT INTO `authbank_cardbin` VALUES ('1180', '上海银行', '4012902', 'SHB', '个人公务卡', '16', '6', '628230', '1');
INSERT INTO `authbank_cardbin` VALUES ('1181', '上海银行', '4012902', 'SHB', '安融卡', '16', '6', '622269', '1');
INSERT INTO `authbank_cardbin` VALUES ('1182', '上海银行', '4012902', 'SHB', '上海银行银联白金卡', '16', '6', '625099', '1');
INSERT INTO `authbank_cardbin` VALUES ('1183', '上海银行', '4012902', 'SHB', '贷记IC卡', '16', '6', '625953', '1');
INSERT INTO `authbank_cardbin` VALUES ('1184', '上海银行', '4012902', 'SHB', '中国旅游卡（IC普卡）', '16', '6', '625350', '1');
INSERT INTO `authbank_cardbin` VALUES ('1185', '上海银行', '4012902', 'SHB', '中国旅游卡（IC金卡）', '16', '6', '625351', '1');
INSERT INTO `authbank_cardbin` VALUES ('1186', '上海银行', '4012902', 'SHB', '中国旅游卡（IC白金卡）', '16', '6', '625352', '1');
INSERT INTO `authbank_cardbin` VALUES ('1187', '上海银行', '4012902', 'SHB', '万事达钻石卡', '16', '6', '519961', '1');
INSERT INTO `authbank_cardbin` VALUES ('1188', '上海银行', '4012902', 'SHB', '淘宝IC普卡', '16', '6', '625839', '1');
INSERT INTO `authbank_cardbin` VALUES ('1189', '厦门银行股份有限公司', '4023930', '', '银鹭借记卡(银联卡)', '16', '6', '622393', '0');
INSERT INTO `authbank_cardbin` VALUES ('1190', '厦门银行股份有限公司', '4023930', '', '银鹭卡', '18', '7', '6886592', '0');
INSERT INTO `authbank_cardbin` VALUES ('1191', '厦门银行股份有限公司', '4023930', '', '银联卡(银联卡)', '16', '6', '940023', '0');
INSERT INTO `authbank_cardbin` VALUES ('1192', '厦门银行股份有限公司', '4023930', '', '凤凰花卡', '19', '6', '623019', '0');
INSERT INTO `authbank_cardbin` VALUES ('1193', '厦门银行股份有限公司', '4023930', '', '凤凰花卡', '19', '6', '621600', '0');
INSERT INTO `authbank_cardbin` VALUES ('1194', '北京银行', '4031000', 'BOB', '京卡借记卡', '16', '6', '421317', '0');
INSERT INTO `authbank_cardbin` VALUES ('1195', '北京银行', '4031000', 'BOB', '京卡(银联卡)', '16', '6', '602969', '0');
INSERT INTO `authbank_cardbin` VALUES ('1196', '北京银行', '4031000', 'BOB', '京卡借记卡', '16', '6', '621030', '0');
INSERT INTO `authbank_cardbin` VALUES ('1197', '北京银行', '4031000', 'BOB', '京卡', '16', '6', '621420', '0');
INSERT INTO `authbank_cardbin` VALUES ('1198', '北京银行', '4031000', 'BOB', '京卡', '16', '6', '621468', '0');
INSERT INTO `authbank_cardbin` VALUES ('1199', '北京银行', '4031000', 'BOB', '借记IC卡', '16', '6', '623111', '0');
INSERT INTO `authbank_cardbin` VALUES ('1200', '北京银行', '4031000', 'BOB', '京卡贵宾金卡', '16', '6', '422160', '0');
INSERT INTO `authbank_cardbin` VALUES ('1201', '北京银行', '4031000', 'BOB', '京卡贵宾白金卡', '16', '6', '422161', '0');
INSERT INTO `authbank_cardbin` VALUES ('1202', '福建海峡银行股份有限公司', '4053910', '', '榕城卡(银联卡)', '16', '6', '622388', '0');
INSERT INTO `authbank_cardbin` VALUES ('1203', '福建海峡银行股份有限公司', '4053910', '', '福州市民卡', '18', '6', '621267', '0');
INSERT INTO `authbank_cardbin` VALUES ('1204', '福建海峡银行股份有限公司', '4053910', '', '福州市民卡', '18', '6', '620043', '2');
INSERT INTO `authbank_cardbin` VALUES ('1205', '福建海峡银行股份有限公司', '4053910', '', '海福卡（IC卡）', '18', '6', '623063', '0');
INSERT INTO `authbank_cardbin` VALUES ('1206', '福建海峡银行', '4053919', '', '公务卡', '16', '6', '628360', '1');
INSERT INTO `authbank_cardbin` VALUES ('1207', '吉林银行', '4062410', '', '君子兰一卡通(银联卡)', '19', '6', '622865', '0');
INSERT INTO `authbank_cardbin` VALUES ('1208', '吉林银行', '4062410', '', '君子兰卡(银联卡)', '16', '6', '940012', '0');
INSERT INTO `authbank_cardbin` VALUES ('1209', '吉林银行', '4062410', '', '长白山金融IC卡', '19', '6', '623131', '0');
INSERT INTO `authbank_cardbin` VALUES ('1210', '吉林银行', '4062418', '', '信用卡', '16', '6', '622178', '1');
INSERT INTO `authbank_cardbin` VALUES ('1211', '吉林银行', '4062418', '', '信用卡', '16', '6', '622179', '1');
INSERT INTO `authbank_cardbin` VALUES ('1212', '吉林银行', '4062418', '', '公务卡', '16', '6', '628358', '1');
INSERT INTO `authbank_cardbin` VALUES ('1213', '镇江市商业银行', '4073140', '', '金山灵通卡(银联卡)', '16', '6', '622394', '0');
INSERT INTO `authbank_cardbin` VALUES ('1214', '镇江市商业银行', '4073140', '', '金山灵通卡(银联卡)', '16', '6', '940025', '0');
INSERT INTO `authbank_cardbin` VALUES ('1215', '宁波银行', '4083320', '', '银联标准卡', '16', '6', '621279', '0');
INSERT INTO `authbank_cardbin` VALUES ('1216', '宁波银行', '4083320', '', '汇通借记卡', '16', '6', '622281', '0');
INSERT INTO `authbank_cardbin` VALUES ('1217', '宁波银行', '4083320', '', '汇通卡(银联卡)', '16', '6', '622316', '0');
INSERT INTO `authbank_cardbin` VALUES ('1218', '宁波银行', '4083320', '', '明州卡', '16', '6', '940022', '0');
INSERT INTO `authbank_cardbin` VALUES ('1219', '宁波银行', '4083320', '', '汇通借记卡', '19', '6', '621418', '0');
INSERT INTO `authbank_cardbin` VALUES ('1220', '宁波银行', '4083320', '', '——', '19', '6', '623252', '0');
INSERT INTO `authbank_cardbin` VALUES ('1221', '宁波银行', '4083320', '', '预付卡', '19', '6', '620533', '2');
INSERT INTO `authbank_cardbin` VALUES ('1222', '宁波银行', '4083329', '', '汇通国际卡银联双币卡', '16', '6', '512431', '1');
INSERT INTO `authbank_cardbin` VALUES ('1223', '宁波银行', '4083329', '', '汇通国际卡银联双币卡', '16', '6', '520194', '1');
INSERT INTO `authbank_cardbin` VALUES ('1224', '平安银行', '4100000', 'PINGAN', '新磁条借记卡', '19', '6', '621626', '0');
INSERT INTO `authbank_cardbin` VALUES ('1225', '平安银行', '4100000', 'PINGAN', '平安银行IC借记卡', '19', '6', '623058', '0');
INSERT INTO `authbank_cardbin` VALUES ('1226', '平安银行', '4105840', 'PINGAN', '万事顺卡', '16', '6', '602907', '0');
INSERT INTO `authbank_cardbin` VALUES ('1227', '平安银行', '4105840', 'PINGAN', '平安银行借记卡', '16', '6', '622986', '0');
INSERT INTO `authbank_cardbin` VALUES ('1228', '平安银行', '4105840', 'PINGAN', '平安银行借记卡', '16', '6', '622989', '0');
INSERT INTO `authbank_cardbin` VALUES ('1229', '平安银行', '4105840', 'PINGAN', '万事顺借记卡', '16', '6', '622298', '0');
INSERT INTO `authbank_cardbin` VALUES ('1230', '焦作市商业银行', '4115010', '', '月季借记卡(银联卡)', '19', '6', '622338', '0');
INSERT INTO `authbank_cardbin` VALUES ('1231', '焦作市商业银行', '4115010', '', '月季城市通(银联卡)', '16', '6', '940032', '0');
INSERT INTO `authbank_cardbin` VALUES ('1232', '焦作市商业银行', '4115010', '', '中国旅游卡', '19', '6', '623205', '0');
INSERT INTO `authbank_cardbin` VALUES ('1233', '温州银行', '4123330', '', '金鹿卡', '16', '6', '621977', '0');
INSERT INTO `authbank_cardbin` VALUES ('1234', '广州银行股份有限公司', '4135810', '', '羊城借记卡', '19', '6', '603445', '0');
INSERT INTO `authbank_cardbin` VALUES ('1235', '广州银行股份有限公司', '4135810', '', '羊城借记卡(银联卡)', '19', '6', '622467', '0');
INSERT INTO `authbank_cardbin` VALUES ('1236', '广州银行股份有限公司', '4135810', '', '羊城借记卡(银联卡)', '19', '6', '940016', '0');
INSERT INTO `authbank_cardbin` VALUES ('1237', '广州银行股份有限公司', '4135810', '', '金融IC借记卡', '19', '6', '621463', '0');
INSERT INTO `authbank_cardbin` VALUES ('1238', '汉口银行', '4145210', '', '九通卡(银联卡)', '18', '6', '990027', '0');
INSERT INTO `authbank_cardbin` VALUES ('1239', '汉口银行', '4145210', '', '九通卡', '16', '6', '622325', '0');
INSERT INTO `authbank_cardbin` VALUES ('1240', '汉口银行', '4145210', '', '借记卡', '16', '6', '623029', '0');
INSERT INTO `authbank_cardbin` VALUES ('1241', '汉口银行', '4145210', '', '借记卡', '16', '6', '623105', '0');
INSERT INTO `authbank_cardbin` VALUES ('1242', '龙江银行股份有限公司', '4162640', '', '金鹤卡', '16', '6', '622475', '0');
INSERT INTO `authbank_cardbin` VALUES ('1243', '盛京银行', '4170000', '', '玫瑰卡', '16', '6', '621244', '0');
INSERT INTO `authbank_cardbin` VALUES ('1244', '盛京银行', '4170001', '', '玫瑰IC卡', '16', '6', '623081', '0');
INSERT INTO `authbank_cardbin` VALUES ('1245', '盛京银行', '4170001', '', '玫瑰IC卡', '16', '6', '623108', '0');
INSERT INTO `authbank_cardbin` VALUES ('1246', '盛京银行', '4172210', '', '玫瑰卡', '18', '6', '566666', '0');
INSERT INTO `authbank_cardbin` VALUES ('1247', '盛京银行', '4172210', '', '玫瑰卡', '19', '6', '622455', '0');
INSERT INTO `authbank_cardbin` VALUES ('1248', '盛京银行', '4172210', '', '玫瑰卡(银联卡)', '19', '6', '940039', '0');
INSERT INTO `authbank_cardbin` VALUES ('1249', '盛京银行', '4172210', '', '医保卡', '16', '6', '622955', '0');
INSERT INTO `authbank_cardbin` VALUES ('1250', '盛京银行', '4172211', '', '玫瑰卡(银联卡)', '16', '6', '622466', '1');
INSERT INTO `authbank_cardbin` VALUES ('1251', '盛京银行', '4172211', '', '盛京银行公务卡', '16', '6', '628285', '1');
INSERT INTO `authbank_cardbin` VALUES ('1252', '洛阳银行', '4184930', '', '都市一卡通(银联卡)', '17', '6', '622420', '0');
INSERT INTO `authbank_cardbin` VALUES ('1253', '洛阳银行', '4184930', '', '都市一卡通(银联卡)', '17', '6', '940041', '0');
INSERT INTO `authbank_cardbin` VALUES ('1254', '洛阳银行', '4184930', '', '--', '19', '6', '623118', '0');
INSERT INTO `authbank_cardbin` VALUES ('1255', '辽阳银行股份有限公司', '4192310', '', '新兴卡(银联卡)', '17', '6', '622399', '0');
INSERT INTO `authbank_cardbin` VALUES ('1256', '辽阳银行股份有限公司', '4192310', '', '新兴卡(银联卡)', '17', '6', '940043', '0');
INSERT INTO `authbank_cardbin` VALUES ('1257', '辽阳银行股份有限公司', '4192310', '', '公务卡', '16', '6', '628309', '1');
INSERT INTO `authbank_cardbin` VALUES ('1258', '辽阳银行股份有限公司', '4192310', '', '新兴卡', '19', '6', '623151', '0');
INSERT INTO `authbank_cardbin` VALUES ('1259', '大连银行', '4202220', '', '北方明珠卡', '17', '6', '603708', '0');
INSERT INTO `authbank_cardbin` VALUES ('1260', '大连银行', '4202220', '', '人民币借记卡', '19', '6', '622993', '0');
INSERT INTO `authbank_cardbin` VALUES ('1261', '大连银行', '4202220', '', '金融IC借记卡', '19', '6', '623070', '0');
INSERT INTO `authbank_cardbin` VALUES ('1262', '大连银行', '4202220', '', '大连市社会保障卡', '19', '6', '623069', '0');
INSERT INTO `authbank_cardbin` VALUES ('1263', '大连银行', '4202220', '', '借记IC卡', '19', '6', '623172', '0');
INSERT INTO `authbank_cardbin` VALUES ('1264', '大连银行', '4202220', '', '借记IC卡', '19', '6', '623173', '0');
INSERT INTO `authbank_cardbin` VALUES ('1265', '大连银行', '4202221', '', '大连市商业银行贷记卡', '16', '6', '622383', '1');
INSERT INTO `authbank_cardbin` VALUES ('1266', '大连银行', '4202221', '', '大连市商业银行贷记卡', '16', '6', '622385', '1');
INSERT INTO `authbank_cardbin` VALUES ('1267', '大连银行', '4202221', '', '银联标准公务卡', '16', '6', '628299', '1');
INSERT INTO `authbank_cardbin` VALUES ('1268', '苏州市商业银行', '4213050', '', '姑苏卡', '19', '6', '603506', '0');
INSERT INTO `authbank_cardbin` VALUES ('1269', '河北银行股份有限公司', '4221210', '', '如意借记卡(银联卡)', '19', '6', '622498', '0');
INSERT INTO `authbank_cardbin` VALUES ('1270', '河北银行股份有限公司', '4221210', '', '如意借记卡(银联卡)', '19', '6', '622499', '0');
INSERT INTO `authbank_cardbin` VALUES ('1271', '河北银行股份有限公司', '4221210', '', '如意卡(银联卡)', '19', '6', '940046', '0');
INSERT INTO `authbank_cardbin` VALUES ('1272', '河北银行股份有限公司', '4221210', '', '借记IC卡', '19', '6', '623000', '0');
INSERT INTO `authbank_cardbin` VALUES ('1273', '杭州商业银行', '4233310', '', '西湖卡', '18', '6', '603367', '0');
INSERT INTO `authbank_cardbin` VALUES ('1274', '杭州商业银行', '4233310', '', '西湖卡', '18', '6', '622878', '0');
INSERT INTO `authbank_cardbin` VALUES ('1275', '杭州商业银行', '4233310', '', '借记IC卡', '18', '6', '623061', '0');
INSERT INTO `authbank_cardbin` VALUES ('1276', '杭州商业银行', '4233310', '', '', '18', '6', '623209', '0');
INSERT INTO `authbank_cardbin` VALUES ('1277', '南京银行', '4240001', '', '梅花信用卡公务卡', '16', '6', '628242', '1');
INSERT INTO `authbank_cardbin` VALUES ('1278', '南京银行', '4240001', '', '梅花信用卡商务卡', '16', '6', '622595', '1');
INSERT INTO `authbank_cardbin` VALUES ('1279', '南京银行', '4240001', '', '梅花贷记卡(银联卡)', '16', '6', '622303', '1');
INSERT INTO `authbank_cardbin` VALUES ('1280', '南京银行', '4243010', '', '梅花借记卡(银联卡)', '16', '6', '622305', '0');
INSERT INTO `authbank_cardbin` VALUES ('1281', '南京银行', '4243010', '', '白金卡', '16', '6', '621259', '0');
INSERT INTO `authbank_cardbin` VALUES ('1282', '南京银行', '4243010', '', '商务卡', '16', '6', '622596', '1');
INSERT INTO `authbank_cardbin` VALUES ('1283', '东莞市商业银行', '4256020', '', '万顺通卡(银联卡)', '16', '6', '622333', '0');
INSERT INTO `authbank_cardbin` VALUES ('1284', '东莞市商业银行', '4256020', '', '万顺通卡(银联卡)', '16', '6', '940050', '0');
INSERT INTO `authbank_cardbin` VALUES ('1285', '东莞市商业银行', '4256020', '', '万顺通借记卡', '19', '6', '621439', '0');
INSERT INTO `authbank_cardbin` VALUES ('1286', '东莞市商业银行', '4256020', '', '社会保障卡', '19', '6', '623010', '0');
INSERT INTO `authbank_cardbin` VALUES ('1287', '金华银行股份有限公司', '4263380', '', '双龙卡(银联卡)', '16', '6', '940051', '0');
INSERT INTO `authbank_cardbin` VALUES ('1288', '金华银行股份有限公司', '4263380', '', '公务卡', '16', '6', '628204', '1');
INSERT INTO `authbank_cardbin` VALUES ('1289', '金华银行股份有限公司', '4263380', '', '双龙借记卡', '16', '6', '622449', '0');
INSERT INTO `authbank_cardbin` VALUES ('1290', '金华银行股份有限公司', '4263380', '', '双龙社保卡', '16', '6', '623067', '0');
INSERT INTO `authbank_cardbin` VALUES ('1291', '金华银行股份有限公司', '4263380', '', '双龙贷记卡(银联卡)', '16', '6', '622450', '1');
INSERT INTO `authbank_cardbin` VALUES ('1292', '乌鲁木齐市商业银行', '4270001', '', '雪莲借记IC卡', '19', '6', '621751', '0');
INSERT INTO `authbank_cardbin` VALUES ('1293', '乌鲁木齐市商业银行', '4270001', '', '乌鲁木齐市公务卡', '16', '6', '628278', '1');
INSERT INTO `authbank_cardbin` VALUES ('1294', '乌鲁木齐市商业银行', '4270001', '', '福农卡贷记卡', '16', '6', '625502', '1');
INSERT INTO `authbank_cardbin` VALUES ('1295', '乌鲁木齐市商业银行', '4270001', '', '福农卡准贷记卡', '16', '6', '625503', '3');
INSERT INTO `authbank_cardbin` VALUES ('1296', '乌鲁木齐市商业银行', '4270001', '', '雪莲准贷记卡', '16', '6', '625135', '3');
INSERT INTO `authbank_cardbin` VALUES ('1297', '乌鲁木齐市商业银行', '4270001', '', '雪莲贷记卡(银联卡)', '16', '6', '622476', '1');
INSERT INTO `authbank_cardbin` VALUES ('1298', '乌鲁木齐市商业银行', '4270001', '', '雪莲贷记IC卡', '16', '6', '625155', '1');
INSERT INTO `authbank_cardbin` VALUES ('1299', '乌鲁木齐市商业银行', '4278810', '', '雪莲借记IC卡', '19', '6', '621754', '0');
INSERT INTO `authbank_cardbin` VALUES ('1300', '乌鲁木齐市商业银行', '4278810', '', '雪莲借记卡(银联卡)', '19', '6', '622143', '0');
INSERT INTO `authbank_cardbin` VALUES ('1301', '乌鲁木齐市商业银行', '4278810', '', '雪莲卡(银联卡)', '19', '6', '940001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1302', '绍兴银行股份有限公司', '4283370', '', '兰花卡(银联卡)', '16', '6', '622486', '0');
INSERT INTO `authbank_cardbin` VALUES ('1303', '绍兴银行股份有限公司', '4283370', '', '兰花卡', '18', '6', '603602', '0');
INSERT INTO `authbank_cardbin` VALUES ('1304', '绍兴银行', '4283371', '', '兰花IC借记卡', '16', '6', '623026', '0');
INSERT INTO `authbank_cardbin` VALUES ('1305', '绍兴银行', '4283371', '', '社保IC借记卡', '16', '6', '623086', '0');
INSERT INTO `authbank_cardbin` VALUES ('1306', '绍兴银行', '4283379', '', '兰花公务卡', '16', '6', '628291', '1');
INSERT INTO `authbank_cardbin` VALUES ('1307', '成都商业银行', '4296510', '', '芙蓉锦程福农卡', '19', '6', '621532', '0');
INSERT INTO `authbank_cardbin` VALUES ('1308', '成都商业银行', '4296510', '', '芙蓉锦程天府通卡', '19', '6', '621482', '0');
INSERT INTO `authbank_cardbin` VALUES ('1309', '成都商业银行', '4296510', '', '锦程卡(银联卡)', '19', '6', '622135', '0');
INSERT INTO `authbank_cardbin` VALUES ('1310', '成都商业银行', '4296510', '', '锦程卡金卡', '19', '6', '622152', '0');
INSERT INTO `authbank_cardbin` VALUES ('1311', '成都商业银行', '4296510', '', '锦程卡定活一卡通金卡', '19', '6', '622153', '0');
INSERT INTO `authbank_cardbin` VALUES ('1312', '成都商业银行', '4296510', '', '锦程卡定活一卡通', '19', '6', '622154', '0');
INSERT INTO `authbank_cardbin` VALUES ('1313', '成都商业银行', '4296510', '', '锦程力诚联名卡', '19', '6', '622996', '0');
INSERT INTO `authbank_cardbin` VALUES ('1314', '成都商业银行', '4296510', '', '锦程力诚联名卡', '19', '6', '622997', '0');
INSERT INTO `authbank_cardbin` VALUES ('1315', '成都商业银行', '4296510', '', '锦程卡(银联卡)', '19', '6', '940027', '0');
INSERT INTO `authbank_cardbin` VALUES ('1316', '抚顺银行股份有限公司', '4302240', '', '绿叶卡(银联卡)', '17', '6', '622442', '0');
INSERT INTO `authbank_cardbin` VALUES ('1317', '抚顺银行股份有限公司', '4302240', '', '启运卡', '19', '6', '622442', '0');
INSERT INTO `authbank_cardbin` VALUES ('1318', '抚顺银行股份有限公司', '4302240', '', '绿叶卡(银联卡)', '18', '6', '940053', '0');
INSERT INTO `authbank_cardbin` VALUES ('1319', '抚顺银行', '4302248', '', '借记IC卡', '19', '6', '623099', '0');
INSERT INTO `authbank_cardbin` VALUES ('1320', '抚顺银行', '4302249', '', '贷记卡', '16', '6', '628302', '1');
INSERT INTO `authbank_cardbin` VALUES ('1321', '临商银行', '4314730', '', '借记卡', '19', '6', '623007', '0');
INSERT INTO `authbank_cardbin` VALUES ('1322', '宜昌市商业银行', '4325210', '', '三峡卡(银联卡)', '17', '6', '940055', '0');
INSERT INTO `authbank_cardbin` VALUES ('1323', '宜昌市商业银行', '4325261', '', '信用卡(银联卡)', '16', '6', '622397', '1');
INSERT INTO `authbank_cardbin` VALUES ('1324', '葫芦岛市商业银行', '4332350', '', '一通卡', '16', '6', '622398', '0');
INSERT INTO `authbank_cardbin` VALUES ('1325', '葫芦岛市商业银行', '4332350', '', '一卡通(银联卡)', '16', '6', '940054', '0');
INSERT INTO `authbank_cardbin` VALUES ('1326', '天津市商业银行', '4341100', '', '津卡', '18', '6', '622331', '0');
INSERT INTO `authbank_cardbin` VALUES ('1327', '天津市商业银行', '4341100', '', '津卡贷记卡(银联卡)', '16', '6', '622426', '1');
INSERT INTO `authbank_cardbin` VALUES ('1328', '天津市商业银行', '4341100', '', '贷记IC卡', '16', '6', '625995', '1');
INSERT INTO `authbank_cardbin` VALUES ('1329', '天津市商业银行', '4341100', '', '--', '18', '6', '621452', '0');
INSERT INTO `authbank_cardbin` VALUES ('1330', '天津银行', '4341101', '', '商务卡', '16', '6', '628205', '1');
INSERT INTO `authbank_cardbin` VALUES ('1331', '郑州银行股份有限公司', '4354910', '', '世纪一卡通(银联卡)', '19', '6', '622421', '0');
INSERT INTO `authbank_cardbin` VALUES ('1332', '郑州银行股份有限公司', '4354910', '', '世纪一卡通', '17', '6', '940056', '0');
INSERT INTO `authbank_cardbin` VALUES ('1333', '郑州银行股份有限公司', '4354910', '', '世纪一卡通', '16', '5', '96828', '0');
INSERT INTO `authbank_cardbin` VALUES ('1334', '宁夏银行', '4360010', '', '宁夏银行公务卡', '16', '6', '628214', '1');
INSERT INTO `authbank_cardbin` VALUES ('1335', '宁夏银行', '4360010', '', '宁夏银行福农贷记卡', '16', '6', '625529', '1');
INSERT INTO `authbank_cardbin` VALUES ('1336', '宁夏银行', '4368710', '', '如意卡(银联卡)', '16', '6', '622428', '1');
INSERT INTO `authbank_cardbin` VALUES ('1337', '宁夏银行', '4368710', '', '宁夏银行福农借记卡', '19', '6', '621529', '0');
INSERT INTO `authbank_cardbin` VALUES ('1338', '宁夏银行', '4368710', '', '如意借记卡', '19', '6', '622429', '0');
INSERT INTO `authbank_cardbin` VALUES ('1339', '宁夏银行', '4368719', '', '如意IC卡', '19', '6', '621417', '0');
INSERT INTO `authbank_cardbin` VALUES ('1340', '宁夏银行', '4368719', '', '宁夏银行如意借记卡', '19', '6', '623089', '0');
INSERT INTO `authbank_cardbin` VALUES ('1341', '宁夏银行', '4368719', '', '中国旅游卡', '19', '6', '623200', '0');
INSERT INTO `authbank_cardbin` VALUES ('1342', '珠海华润银行股份有限公司', '4375850', '', '万事顺卡', '19', '6', '622363', '0');
INSERT INTO `authbank_cardbin` VALUES ('1343', '珠海华润银行股份有限公司', '4375850', '', '万事顺卡(银联卡)', '19', '6', '940048', '0');
INSERT INTO `authbank_cardbin` VALUES ('1344', '珠海华润银行股份有限公司', '4375850', '', '珠海华润银行IC借记卡', '19', '6', '621455', '0');
INSERT INTO `authbank_cardbin` VALUES ('1345', '齐商银行', '4384530', '', '金达卡(银联卡)', '17', '6', '940057', '0');
INSERT INTO `authbank_cardbin` VALUES ('1346', '齐商银行', '4384530', '', '金达借记卡(银联卡)', '17', '6', '622311', '0');
INSERT INTO `authbank_cardbin` VALUES ('1347', '齐商银行', '4384530', '', '金达IC卡', '19', '6', '623119', '0');
INSERT INTO `authbank_cardbin` VALUES ('1348', '锦州银行股份有限公司', '4392270', '', '7777卡', '17', '6', '622990', '0');
INSERT INTO `authbank_cardbin` VALUES ('1349', '锦州银行股份有限公司', '4392270', '', '万通卡(银联卡)', '17', '6', '940003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1350', '徽商银行', '4403600', '', '黄山卡', '19', '6', '622877', '0');
INSERT INTO `authbank_cardbin` VALUES ('1351', '徽商银行', '4403600', '', '黄山卡', '19', '6', '622879', '0');
INSERT INTO `authbank_cardbin` VALUES ('1352', '徽商银行', '4403601', '', '借记卡', '19', '6', '621775', '0');
INSERT INTO `authbank_cardbin` VALUES ('1353', '徽商银行', '4403601', '', '徽商银行中国旅游卡（安徽）', '19', '6', '623203', '0');
INSERT INTO `authbank_cardbin` VALUES ('1354', '徽商银行合肥分行', '4403610', '', '黄山卡', '17', '6', '603601', '0');
INSERT INTO `authbank_cardbin` VALUES ('1355', '徽商银行芜湖分行', '4403620', '', '黄山卡(银联卡)', '17', '6', '622137', '0');
INSERT INTO `authbank_cardbin` VALUES ('1356', '徽商银行马鞍山分行', '4403650', '', '黄山卡(银联卡)', '17', '6', '622327', '0');
INSERT INTO `authbank_cardbin` VALUES ('1357', '徽商银行淮北分行', '4403660', '', '黄山卡(银联卡)', '17', '6', '622340', '0');
INSERT INTO `authbank_cardbin` VALUES ('1358', '徽商银行安庆分行', '4403680', '', '黄山卡(银联卡)', '17', '6', '622366', '0');
INSERT INTO `authbank_cardbin` VALUES ('1359', '重庆银行', '4416530', '', '长江卡', '16', '4', '9896', '0');
INSERT INTO `authbank_cardbin` VALUES ('1360', '重庆银行', '4416530', '', '长江卡(银联卡)', '16', '6', '622134', '0');
INSERT INTO `authbank_cardbin` VALUES ('1361', '重庆银行', '4416530', '', '长江卡(银联卡)', '16', '6', '940018', '0');
INSERT INTO `authbank_cardbin` VALUES ('1362', '重庆银行', '4416900', '', '长江卡', '16', '6', '623016', '0');
INSERT INTO `authbank_cardbin` VALUES ('1363', '重庆银行', '4416900', '', '借记IC卡', '19', '6', '623096', '0');
INSERT INTO `authbank_cardbin` VALUES ('1364', '哈尔滨银行', '4422610', '', '丁香一卡通(银联卡)', '18', '6', '940049', '0');
INSERT INTO `authbank_cardbin` VALUES ('1365', '哈尔滨银行', '4422610', '', '丁香借记卡(银联卡)', '17', '6', '622425', '0');
INSERT INTO `authbank_cardbin` VALUES ('1366', '哈尔滨银行', '4422610', '', '丁香卡', '19', '6', '622425', '0');
INSERT INTO `authbank_cardbin` VALUES ('1367', '哈尔滨银行', '4422610', '', '福农借记卡', '19', '6', '621577', '0');
INSERT INTO `authbank_cardbin` VALUES ('1368', '贵阳银行股份有限公司', '4437010', '', '甲秀银联借记卡', '19', '6', '622133', '0');
INSERT INTO `authbank_cardbin` VALUES ('1369', '贵阳银行股份有限公司', '4437010', '', '甲秀卡', '16', '3', '888', '0');
INSERT INTO `authbank_cardbin` VALUES ('1370', '贵阳银行股份有限公司', '4437010', '', '一卡通', '19', '6', '621735', '0');
INSERT INTO `authbank_cardbin` VALUES ('1371', '贵阳银行股份有限公司', '4437010', '', '', '19', '6', '622170', '0');
INSERT INTO `authbank_cardbin` VALUES ('1372', '西安银行股份有限公司', '4447910', '', '金丝路卡', '18', '6', '622981', '0');
INSERT INTO `authbank_cardbin` VALUES ('1373', '西安银行股份有限公司', '4447910', '', '金丝路借记卡', '18', '6', '623165', '0');
INSERT INTO `authbank_cardbin` VALUES ('1374', '西安银行股份有限公司', '4447910', '', '福瑞卡', '18', '6', '622136', '0');
INSERT INTO `authbank_cardbin` VALUES ('1375', '无锡市商业银行', '4453020', '', '太湖卡', '16', '8', '60326500', '0');
INSERT INTO `authbank_cardbin` VALUES ('1376', '无锡市商业银行', '4453020', '', '太湖卡', '18', '8', '60326513', '0');
INSERT INTO `authbank_cardbin` VALUES ('1377', '无锡市商业银行', '4453020', '', '太湖金保卡(银联卡)', '18', '6', '622485', '0');
INSERT INTO `authbank_cardbin` VALUES ('1378', '丹东银行股份有限公司', '4462260', '', '银杏卡(银联卡)', '16', '6', '622415', '0');
INSERT INTO `authbank_cardbin` VALUES ('1379', '丹东银行股份有限公司', '4462260', '', '银杏卡(银联卡)', '16', '6', '940060', '0');
INSERT INTO `authbank_cardbin` VALUES ('1380', '丹东银行', '4462262', '', '借记IC卡', '19', '6', '623098', '0');
INSERT INTO `authbank_cardbin` VALUES ('1381', '丹东银行', '4462269', '', '丹东银行公务卡', '16', '6', '628329', '1');
INSERT INTO `authbank_cardbin` VALUES ('1382', '兰州银行股份有限公司', '4478210', '', '敦煌国际卡(银联卡)', '16', '6', '622139', '0');
INSERT INTO `authbank_cardbin` VALUES ('1383', '兰州银行股份有限公司', '4478210', '', '敦煌卡', '16', '6', '940040', '0');
INSERT INTO `authbank_cardbin` VALUES ('1384', '兰州银行股份有限公司', '4478210', '', '敦煌卡', '19', '6', '621242', '0');
INSERT INTO `authbank_cardbin` VALUES ('1385', '兰州银行', '4478210', '', '敦煌卡', '19', '6', '621538', '0');
INSERT INTO `authbank_cardbin` VALUES ('1386', '兰州银行股份有限公司', '4478210', '', '敦煌金融IC卡', '19', '6', '621496', '0');
INSERT INTO `authbank_cardbin` VALUES ('1387', '兰州银行股份有限公司', '4478210', '', '金融社保卡', '19', '6', '623129', '0');
INSERT INTO `authbank_cardbin` VALUES ('1388', '南昌银行', '4484210', '', '金瑞卡(银联卡)', '17', '6', '940006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1389', '南昌银行', '4484210', '', '南昌银行借记卡', '16', '6', '621269', '0');
INSERT INTO `authbank_cardbin` VALUES ('1390', '南昌银行', '4484210', '', '金瑞卡', '16', '6', '622275', '0');
INSERT INTO `authbank_cardbin` VALUES ('1391', '晋商银行', '4491610', '', '晋龙一卡通', '19', '6', '621216', '0');
INSERT INTO `authbank_cardbin` VALUES ('1392', '晋商银行', '4491610', '', '晋龙一卡通', '17', '6', '622465', '0');
INSERT INTO `authbank_cardbin` VALUES ('1393', '晋商银行', '4491610', '', '晋龙卡(银联卡)', '17', '6', '940031', '0');
INSERT INTO `authbank_cardbin` VALUES ('1394', '晋商银行', '4491610', '', '借记卡', '19', '6', '623179', '0');
INSERT INTO `authbank_cardbin` VALUES ('1395', '青岛银行', '4504520', '', '金桥通卡', '16', '6', '621252', '0');
INSERT INTO `authbank_cardbin` VALUES ('1396', '青岛银行', '4504520', '', '金桥卡(银联卡)', '16', '6', '622146', '0');
INSERT INTO `authbank_cardbin` VALUES ('1397', '青岛银行', '4504520', '', '金桥卡(银联卡)', '16', '6', '940061', '0');
INSERT INTO `authbank_cardbin` VALUES ('1398', '青岛银行', '4504520', '', '金桥卡', '19', '6', '621419', '0');
INSERT INTO `authbank_cardbin` VALUES ('1399', '青岛银行', '4504520', '', '借记IC卡', '19', '6', '623170', '0');
INSERT INTO `authbank_cardbin` VALUES ('1400', '青岛银行', '4504520', '', '——', '19', '6', '620551', '2');
INSERT INTO `authbank_cardbin` VALUES ('1401', '吉林银行', '4512420', '', '雾凇卡(银联卡)', '16', '6', '622440', '0');
INSERT INTO `authbank_cardbin` VALUES ('1402', '吉林银行', '4512420', '', '雾凇卡(银联卡)', '16', '6', '940047', '0');
INSERT INTO `authbank_cardbin` VALUES ('1403', '南通商业银行', '4523060', '', '金桥卡', '18', '5', '69580', '0');
INSERT INTO `authbank_cardbin` VALUES ('1404', '南通商业银行', '4523060', '', '金桥卡(银联卡)', '18', '6', '940017', '0');
INSERT INTO `authbank_cardbin` VALUES ('1405', '南通商业银行', '4523060', '', '金桥卡(银联卡)', '16', '6', '622418', '0');
INSERT INTO `authbank_cardbin` VALUES ('1406', '九江银行股份有限公司', '4544240', '', '庐山卡(银联卡)', '19', '6', '622307', '0');
INSERT INTO `authbank_cardbin` VALUES ('1407', '九江银行股份有限公司', '4544240', '', '庐山卡', '19', '6', '622162', '0');
INSERT INTO `authbank_cardbin` VALUES ('1408', '日照银行', '4554770', '', '黄海卡、财富卡借记卡', '19', '6', '623077', '0');
INSERT INTO `authbank_cardbin` VALUES ('1409', '鞍山银行', '4562230', '', '千山卡(银联卡)', '16', '6', '622413', '0');
INSERT INTO `authbank_cardbin` VALUES ('1410', '鞍山银行', '4562230', '', '千山卡(银联卡)', '16', '6', '940002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1411', '鞍山银行', '4562239', '', '千山卡', '16', '6', '623188', '0');
INSERT INTO `authbank_cardbin` VALUES ('1412', '秦皇岛银行股份有限公司', '4571260', '', '秦卡', '19', '6', '621237', '0');
INSERT INTO `authbank_cardbin` VALUES ('1413', '秦皇岛银行股份有限公司', '4571260', '', '秦卡', '19', '8', '62249802', '0');
INSERT INTO `authbank_cardbin` VALUES ('1414', '秦皇岛银行股份有限公司', '4571260', '', '秦卡', '19', '8', '94004602', '0');
INSERT INTO `authbank_cardbin` VALUES ('1415', '秦皇岛银行股份有限公司', '4571261', '', '秦卡-IC卡', '19', '6', '623003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1416', '青海银行', '4588510', '', '三江银行卡(银联卡)', '17', '6', '622310', '0');
INSERT INTO `authbank_cardbin` VALUES ('1417', '青海银行', '4588510', '', '三江卡', '17', '6', '940068', '0');
INSERT INTO `authbank_cardbin` VALUES ('1418', '台州银行', '4593450', '', '大唐贷记卡', '16', '6', '622321', '1');
INSERT INTO `authbank_cardbin` VALUES ('1419', '台州银行', '4593450', '', '大唐准贷记卡', '16', '6', '625001', '3');
INSERT INTO `authbank_cardbin` VALUES ('1420', '台州银行', '4593451', '', '大唐卡(银联卡)', '16', '6', '622427', '0');
INSERT INTO `authbank_cardbin` VALUES ('1421', '台州银行', '4593451', '', '大唐卡', '17', '6', '940069', '0');
INSERT INTO `authbank_cardbin` VALUES ('1422', '台州银行', '4593451', '', '借记卡', '19', '6', '623039', '0');
INSERT INTO `authbank_cardbin` VALUES ('1423', '台州银行', '4593451', '', '公务卡', '16', '6', '628273', '1');
INSERT INTO `authbank_cardbin` VALUES ('1424', '盐城商行', '4603110', '', '金鹤卡(银联卡)', '16', '6', '940070', '0');
INSERT INTO `authbank_cardbin` VALUES ('1425', '长沙银行股份有限公司', '4615510', '', '芙蓉卡', '18', '6', '694301', '0');
INSERT INTO `authbank_cardbin` VALUES ('1426', '长沙银行股份有限公司', '4615510', '', '芙蓉卡(银联卡)', '19', '6', '940071', '0');
INSERT INTO `authbank_cardbin` VALUES ('1427', '长沙银行股份有限公司', '4615510', '', '芙蓉卡(银联卡)', '19', '6', '622368', '0');
INSERT INTO `authbank_cardbin` VALUES ('1428', '长沙银行股份有限公司', '4615510', '', '芙蓉金融IC卡', '19', '6', '621446', '0');
INSERT INTO `authbank_cardbin` VALUES ('1429', '长沙银行股份有限公司', '4615511', '', '市民卡', '16', '6', '625901', '1');
INSERT INTO `authbank_cardbin` VALUES ('1430', '长沙银行股份有限公司', '4615511', '', '芙蓉贷记卡', '16', '6', '622898', '1');
INSERT INTO `authbank_cardbin` VALUES ('1431', '长沙银行股份有限公司', '4615511', '', '芙蓉贷记卡', '16', '6', '622900', '1');
INSERT INTO `authbank_cardbin` VALUES ('1432', '长沙银行股份有限公司', '4615511', '', '公务卡钻石卡', '16', '6', '628281', '1');
INSERT INTO `authbank_cardbin` VALUES ('1433', '长沙银行股份有限公司', '4615511', '', '公务卡金卡', '16', '6', '628282', '1');
INSERT INTO `authbank_cardbin` VALUES ('1434', '长沙银行股份有限公司', '4615511', '', '银联标准钻石卡', '16', '6', '622806', '1');
INSERT INTO `authbank_cardbin` VALUES ('1435', '长沙银行股份有限公司', '4615511', '', '公务卡普卡', '16', '6', '628283', '1');
INSERT INTO `authbank_cardbin` VALUES ('1436', '长沙银行股份有限公司', '4615511', '', '市民卡', '19', '6', '620519', '2');
INSERT INTO `authbank_cardbin` VALUES ('1437', '长沙银行股份有限公司', '4615511', '', '借记IC卡', '19', '6', '621739', '0');
INSERT INTO `authbank_cardbin` VALUES ('1438', '赣州银行股份有限公司', '4634280', '', '长征卡', '19', '6', '622967', '0');
INSERT INTO `authbank_cardbin` VALUES ('1439', '赣州银行股份有限公司', '4634280', '', '长征卡(银联卡)', '19', '6', '940073', '0');
INSERT INTO `authbank_cardbin` VALUES ('1440', '泉州银行', '4643970', '', '海峡银联卡(银联卡)', '19', '6', '622370', '0');
INSERT INTO `authbank_cardbin` VALUES ('1441', '泉州银行', '4643970', '', '海峡储蓄卡', '18', '6', '683970', '0');
INSERT INTO `authbank_cardbin` VALUES ('1442', '泉州银行', '4643970', '', '海峡银联卡(银联卡)', '18', '6', '940074', '0');
INSERT INTO `authbank_cardbin` VALUES ('1443', '泉州银行', '4643970', '', '海峡卡', '19', '6', '621437', '0');
INSERT INTO `authbank_cardbin` VALUES ('1444', '泉州银行', '4643970', '', '公务卡', '16', '6', '628319', '1');
INSERT INTO `authbank_cardbin` VALUES ('1445', '营口银行股份有限公司', '4652280', '', '辽河一卡通(银联卡)', '17', '6', '622400', '0');
INSERT INTO `authbank_cardbin` VALUES ('1446', '营口银行股份有限公司', '4652280', '', '营银卡', '19', '6', '623177', '0');
INSERT INTO `authbank_cardbin` VALUES ('1447', '昆明商业银行', '4667310', '', '春城卡(银联卡)', '17', '6', '622308', '0');
INSERT INTO `authbank_cardbin` VALUES ('1448', '昆明商业银行', '4667310', '', '富滇IC卡（复合卡）', '19', '6', '621415', '0');
INSERT INTO `authbank_cardbin` VALUES ('1449', '昆明商业银行', '4667310', '', '春城卡(银联卡)', '18', '6', '990871', '0');
INSERT INTO `authbank_cardbin` VALUES ('1450', '阜新银行股份有限公司', '4672290', '', '金通卡(银联卡)', '18', '6', '622126', '0');
INSERT INTO `authbank_cardbin` VALUES ('1451', '阜新银行', '4672299', '', '借记IC卡', '18', '6', '623166', '0');
INSERT INTO `authbank_cardbin` VALUES ('1452', '嘉兴银行', '4703350', '', '南湖借记卡(银联卡)', '16', '6', '622132', '0');
INSERT INTO `authbank_cardbin` VALUES ('1453', '廊坊银行', '4721460', '', '白金卡', '16', '6', '621340', '0');
INSERT INTO `authbank_cardbin` VALUES ('1454', '廊坊银行', '4721460', '', '金卡', '16', '6', '621341', '0');
INSERT INTO `authbank_cardbin` VALUES ('1455', '廊坊银行', '4721460', '', '银星卡(银联卡)', '16', '6', '622140', '0');
INSERT INTO `authbank_cardbin` VALUES ('1456', '廊坊银行', '4721460', '', '龙凤呈祥卡', '16', '6', '623073', '0');
INSERT INTO `authbank_cardbin` VALUES ('1457', '泰隆城市信用社', '4733450', '', '泰隆卡(银联卡)', '16', '6', '622141', '0');
INSERT INTO `authbank_cardbin` VALUES ('1458', '泰隆城市信用社', '4733450', '', '借记IC卡', '19', '6', '621480', '0');
INSERT INTO `authbank_cardbin` VALUES ('1459', '内蒙古银行', '4741910', '', '百灵卡(银联卡)', '19', '6', '622147', '0');
INSERT INTO `authbank_cardbin` VALUES ('1460', '内蒙古银行', '4741910', '', '成吉思汗卡', '19', '6', '621633', '0');
INSERT INTO `authbank_cardbin` VALUES ('1461', '湖州市商业银行', '4753360', '', '百合卡', '19', '6', '622301', '0');
INSERT INTO `authbank_cardbin` VALUES ('1462', '湖州市商业银行', '4753360', '', '', '19', '6', '623171', '0');
INSERT INTO `authbank_cardbin` VALUES ('1463', '沧州银行股份有限公司', '4761430', '', '狮城卡', '19', '6', '621266', '0');
INSERT INTO `authbank_cardbin` VALUES ('1464', '沧州银行股份有限公司', '4761430', '', '狮城卡', '19', '8', '62249804', '0');
INSERT INTO `authbank_cardbin` VALUES ('1465', '沧州银行股份有限公司', '4761430', '', '狮城卡', '19', '8', '94004604', '0');
INSERT INTO `authbank_cardbin` VALUES ('1466', '沧州银行', '4761431', '', '狮城卡', '19', '6', '621422', '0');
INSERT INTO `authbank_cardbin` VALUES ('1467', '南宁市商业银行', '4786110', '', '桂花卡(银联卡)', '16', '6', '622335', '0');
INSERT INTO `authbank_cardbin` VALUES ('1468', '包商银行', '4791920', '', '雄鹰卡(银联卡)', '17', '6', '622336', '0');
INSERT INTO `authbank_cardbin` VALUES ('1469', '包商银行', '4791921', '', '包头市商业银行借记卡', '16', '6', '622165', '0');
INSERT INTO `authbank_cardbin` VALUES ('1470', '包商银行', '4791921', '', '雄鹰贷记卡', '16', '6', '622315', '1');
INSERT INTO `authbank_cardbin` VALUES ('1471', '包商银行', '4791921', '', '包商银行内蒙古自治区公务卡', '16', '6', '628295', '1');
INSERT INTO `authbank_cardbin` VALUES ('1472', '包商银行', '4791921', '', '贷记卡', '16', '6', '625950', '1');
INSERT INTO `authbank_cardbin` VALUES ('1473', '包商银行', '4791921', '', '--', '16', '6', '623210', '1');
INSERT INTO `authbank_cardbin` VALUES ('1474', '包商银行', '4791922', '', '借记卡', '17', '6', '621760', '0');
INSERT INTO `authbank_cardbin` VALUES ('1475', '连云港市商业银行', '4803070', '', '金猴神通借记卡', '16', '6', '622337', '0');
INSERT INTO `authbank_cardbin` VALUES ('1476', '威海商业银行', '4814650', '', '通达卡(银联卡)', '16', '6', '622411', '0');
INSERT INTO `authbank_cardbin` VALUES ('1477', '威海市商业银行', '4814659', '', '通达借记IC卡', '16', '6', '623102', '0');
INSERT INTO `authbank_cardbin` VALUES ('1478', '攀枝花市商业银行', '4836560', '', '攀枝花卡(银联卡)', '19', '6', '622342', '0');
INSERT INTO `authbank_cardbin` VALUES ('1479', '攀枝花市商业银行', '4836560', '', '攀枝花卡', '19', '6', '623048', '0');
INSERT INTO `authbank_cardbin` VALUES ('1480', '绵阳市商业银行', '4856590', '', '科技城卡(银联卡)', '19', '6', '622367', '0');
INSERT INTO `authbank_cardbin` VALUES ('1481', '泸州市商业银行', '4866570', '', '酒城卡(银联卡)', '19', '6', '622392', '0');
INSERT INTO `authbank_cardbin` VALUES ('1482', '泸州市商业银行', '4866570', '', '酒城IC卡', '19', '6', '623085', '0');
INSERT INTO `authbank_cardbin` VALUES ('1483', '大同市商业银行', '4871620', '', '云冈卡(银联卡)', '19', '6', '622395', '0');
INSERT INTO `authbank_cardbin` VALUES ('1484', '三门峡银行', '4885050', '', '天鹅卡(银联卡)', '16', '6', '622441', '0');
INSERT INTO `authbank_cardbin` VALUES ('1485', '三门峡银行', '4885050', '', '借记卡', '16', '6', '623505', '0');
INSERT INTO `authbank_cardbin` VALUES ('1486', '广东南粤银行', '4895910', '', '南珠卡(银联卡)', '16', '6', '622448', '0');
INSERT INTO `authbank_cardbin` VALUES ('1487', '张家口市商业银行股份有限公司', '4901380', '', '如意借记卡', '19', '6', '622982', '0');
INSERT INTO `authbank_cardbin` VALUES ('1488', '张家口市商业银行', '4901381', '', '好运IC借记卡', '19', '6', '621413', '0');
INSERT INTO `authbank_cardbin` VALUES ('1489', '桂林市商业银行', '4916170', '', '漓江卡(银联卡)', '17', '6', '622856', '0');
INSERT INTO `authbank_cardbin` VALUES ('1490', '龙江银行', '4922600', '', '福农借记卡', '19', '6', '621037', '0');
INSERT INTO `authbank_cardbin` VALUES ('1491', '龙江银行', '4922600', '', '联名借记卡', '19', '6', '621097', '0');
INSERT INTO `authbank_cardbin` VALUES ('1492', '龙江银行', '4922600', '', '福农借记卡', '19', '6', '621588', '0');
INSERT INTO `authbank_cardbin` VALUES ('1493', '龙江银行', '4922600', '', '中国旅游卡', '19', '8', '62321601', '0');
INSERT INTO `authbank_cardbin` VALUES ('1494', '龙江银行', '4922600', '', '龙江IC卡', '19', '6', '623032', '0');
INSERT INTO `authbank_cardbin` VALUES ('1495', '龙江银行', '4922600', '', '社会保障卡', '19', '6', '622644', '0');
INSERT INTO `authbank_cardbin` VALUES ('1496', '龙江银行', '4922600', '', '--', '19', '6', '623518', '0');
INSERT INTO `authbank_cardbin` VALUES ('1497', '龙江银行股份有限公司', '4922690', '', '玉兔卡(银联卡)', '16', '6', '622860', '0');
INSERT INTO `authbank_cardbin` VALUES ('1498', '江苏长江商业银行', '4933120', '', '长江卡', '16', '6', '622870', '0');
INSERT INTO `authbank_cardbin` VALUES ('1499', '徐州市商业银行', '4943030', '', '彭城借记卡(银联卡)', '16', '6', '622866', '0');
INSERT INTO `authbank_cardbin` VALUES ('1500', '柳州银行股份有限公司', '4956140', '', '龙城卡', '18', '6', '622292', '0');
INSERT INTO `authbank_cardbin` VALUES ('1501', '柳州银行股份有限公司', '4956140', '', '龙城卡', '18', '6', '622291', '0');
INSERT INTO `authbank_cardbin` VALUES ('1502', '柳州银行股份有限公司', '4956140', '', '龙城IC卡', '18', '6', '621412', '0');
INSERT INTO `authbank_cardbin` VALUES ('1503', '柳州银行股份有限公司', '4956140', '', '龙城卡VIP卡', '16', '6', '622880', '0');
INSERT INTO `authbank_cardbin` VALUES ('1504', '柳州银行股份有限公司', '4956140', '', '龙城致富卡', '16', '6', '622881', '0');
INSERT INTO `authbank_cardbin` VALUES ('1505', '柳州银行股份有限公司', '4956140', '', '东盟商旅卡', '16', '6', '620118', '2');
INSERT INTO `authbank_cardbin` VALUES ('1506', '南充市商业银行', '4966730', '', '借记IC卡', '19', '6', '623072', '0');
INSERT INTO `authbank_cardbin` VALUES ('1507', '南充市商业银行', '4966730', '', '熊猫团团卡', '19', '6', '622897', '0');
INSERT INTO `authbank_cardbin` VALUES ('1508', '莱商银行', '4974634', '', '银联标准卡', '16', '6', '628279', '1');
INSERT INTO `authbank_cardbin` VALUES ('1509', '莱芜银行', '4974634', '', '金凤卡', '16', '6', '622864', '0');
INSERT INTO `authbank_cardbin` VALUES ('1510', '莱商银行', '4974790', '', '借记IC卡', '19', '6', '621403', '0');
INSERT INTO `authbank_cardbin` VALUES ('1511', '德阳银行', '4986580', '', '锦程卡定活一卡通', '19', '6', '622561', '0');
INSERT INTO `authbank_cardbin` VALUES ('1512', '德阳银行', '4986580', '', '锦程卡定活一卡通金卡', '19', '6', '622562', '0');
INSERT INTO `authbank_cardbin` VALUES ('1513', '德阳银行', '4986580', '', '锦程卡定活一卡通', '19', '6', '622563', '0');
INSERT INTO `authbank_cardbin` VALUES ('1514', '德阳银行', '4986580', '', '--', '19', '6', '623264', '0');
INSERT INTO `authbank_cardbin` VALUES ('1515', '唐山市商业银行', '4991240', '', '唐山市城通卡', '19', '6', '622167', '0');
INSERT INTO `authbank_cardbin` VALUES ('1516', '唐山市商业银行', '4991240', '', '盛唐芯卡', '19', '6', '623193', '0');
INSERT INTO `authbank_cardbin` VALUES ('1517', '六盘水商行', '5007020', '', '凉都卡', '16', '6', '622508', '0');
INSERT INTO `authbank_cardbin` VALUES ('1518', '曲靖市商业银行', '5027360', '', '珠江源卡', '16', '6', '622777', '0');
INSERT INTO `authbank_cardbin` VALUES ('1519', '曲靖市商业银行', '5027361', '', '珠江源IC卡', '16', '6', '621497', '0');
INSERT INTO `authbank_cardbin` VALUES ('1520', '晋城银行股份有限公司', '5031680', '', '珠联璧合卡', '19', '6', '622532', '0');
INSERT INTO `authbank_cardbin` VALUES ('1521', '东莞商行', '5056020', '', '恒通贷记卡', '16', '6', '622888', '1');
INSERT INTO `authbank_cardbin` VALUES ('1522', '东莞商行', '5056020', '', '公务卡', '16', '6', '628398', '1');
INSERT INTO `authbank_cardbin` VALUES ('1523', '温州银行', '5063330', '', '金鹿信用卡', '16', '6', '622868', '1');
INSERT INTO `authbank_cardbin` VALUES ('1524', '温州银行', '5063330', '', '金鹿信用卡', '16', '6', '622899', '1');
INSERT INTO `authbank_cardbin` VALUES ('1525', '温州银行', '5063330', '', '金鹿公务卡', '16', '6', '628255', '1');
INSERT INTO `authbank_cardbin` VALUES ('1526', '温州银行', '5063330', '', '贷记IC卡', '16', '6', '625988', '1');
INSERT INTO `authbank_cardbin` VALUES ('1527', '汉口银行', '5075210', '', '汉口银行贷记卡', '16', '6', '622566', '1');
INSERT INTO `authbank_cardbin` VALUES ('1528', '汉口银行', '5075210', '', '汉口银行贷记卡', '16', '6', '622567', '1');
INSERT INTO `authbank_cardbin` VALUES ('1529', '汉口银行', '5075210', '', '九通香港旅游贷记普卡', '16', '6', '622625', '1');
INSERT INTO `authbank_cardbin` VALUES ('1530', '汉口银行', '5075210', '', '九通香港旅游贷记金卡', '16', '6', '622626', '1');
INSERT INTO `authbank_cardbin` VALUES ('1531', '汉口银行', '5075210', '', '贷记卡', '16', '6', '625946', '1');
INSERT INTO `authbank_cardbin` VALUES ('1532', '汉口银行', '5075210', '', '九通公务卡', '16', '6', '628200', '1');
INSERT INTO `authbank_cardbin` VALUES ('1533', '江苏银行', '5083000', '', '聚宝借记卡', '19', '6', '621076', '0');
INSERT INTO `authbank_cardbin` VALUES ('1534', '江苏银行', '5083000', '', '月季卡', '16', '6', '504923', '0');
INSERT INTO `authbank_cardbin` VALUES ('1535', '江苏银行', '5083000', '', '紫金卡', '19', '6', '622173', '0');
INSERT INTO `authbank_cardbin` VALUES ('1536', '江苏银行', '5083000', '', '绿扬卡(银联卡)', '16', '6', '622422', '0');
INSERT INTO `authbank_cardbin` VALUES ('1537', '江苏银行', '5083000', '', '月季卡(银联卡)', '16', '6', '622447', '0');
INSERT INTO `authbank_cardbin` VALUES ('1538', '江苏银行', '5083000', '', '九州借记卡(银联卡)', '19', '6', '622131', '0');
INSERT INTO `authbank_cardbin` VALUES ('1539', '江苏银行', '5083000', '', '月季卡(银联卡)', '16', '6', '940076', '0');
INSERT INTO `authbank_cardbin` VALUES ('1540', '江苏银行', '5083000', '', '聚宝惠民福农卡', '19', '6', '621579', '0');
INSERT INTO `authbank_cardbin` VALUES ('1541', '江苏银行', '5083000', '', '江苏银行聚宝IC借记卡', '19', '6', '622876', '0');
INSERT INTO `authbank_cardbin` VALUES ('1542', '江苏银行', '5083000', '', '聚宝IC借记卡VIP卡', '19', '6', '622873', '0');
INSERT INTO `authbank_cardbin` VALUES ('1543', '平安银行', '5105840', 'PINGAN', '白金信用卡', '16', '6', '531659', '1');
INSERT INTO `authbank_cardbin` VALUES ('1544', '平安银行', '5105840', 'PINGAN', '白金信用卡', '16', '6', '622157', '1');
INSERT INTO `authbank_cardbin` VALUES ('1545', '平安银行', '5105840', 'PINGAN', '沃尔玛百分卡', '16', '6', '435744', '1');
INSERT INTO `authbank_cardbin` VALUES ('1546', '平安银行', '5105840', 'PINGAN', '沃尔玛百分卡', '16', '6', '435745', '1');
INSERT INTO `authbank_cardbin` VALUES ('1547', '平安银行', '5105840', 'PINGAN', 'VISA白金卡', '16', '6', '483536', '1');
INSERT INTO `authbank_cardbin` VALUES ('1548', '平安银行', '5105840', 'PINGAN', '人民币信用卡金卡', '16', '6', '622525', '1');
INSERT INTO `authbank_cardbin` VALUES ('1549', '平安银行', '5105840', 'PINGAN', '人民币信用卡普卡', '16', '6', '622526', '1');
INSERT INTO `authbank_cardbin` VALUES ('1550', '平安银行', '5105840', 'PINGAN', '发展信用卡(银联卡)', '16', '6', '998801', '1');
INSERT INTO `authbank_cardbin` VALUES ('1551', '平安银行', '5105840', 'PINGAN', '发展信用卡(银联卡)', '16', '6', '998802', '1');
INSERT INTO `authbank_cardbin` VALUES ('1552', '平安银行', '5105840', 'PINGAN', '平安银行信用卡', '16', '6', '528020', '1');
INSERT INTO `authbank_cardbin` VALUES ('1553', '平安银行', '5105840', 'PINGAN', '平安银行信用卡', '16', '6', '622155', '1');
INSERT INTO `authbank_cardbin` VALUES ('1554', '平安银行', '5105840', 'PINGAN', '平安银行信用卡', '16', '6', '622156', '1');
INSERT INTO `authbank_cardbin` VALUES ('1555', '平安银行', '5105840', 'PINGAN', '平安银行信用卡', '16', '6', '526855', '1');
INSERT INTO `authbank_cardbin` VALUES ('1556', '平安银行', '5105840', 'PINGAN', '信用卡', '16', '6', '356869', '1');
INSERT INTO `authbank_cardbin` VALUES ('1557', '平安银行', '5105840', 'PINGAN', '信用卡', '16', '6', '356868', '1');
INSERT INTO `authbank_cardbin` VALUES ('1558', '平安银行', '5105840', 'PINGAN', '平安中国旅游信用卡', '16', '6', '625360', '1');
INSERT INTO `authbank_cardbin` VALUES ('1559', '平安银行', '5105840', 'PINGAN', '平安中国旅游白金信用卡', '16', '6', '625361', '1');
INSERT INTO `authbank_cardbin` VALUES ('1560', '平安银行', '5105840', 'PINGAN', '公务卡', '16', '6', '628296', '1');
INSERT INTO `authbank_cardbin` VALUES ('1561', '平安银行', '5105840', 'PINGAN', '白金IC卡', '16', '6', '625825', '1');
INSERT INTO `authbank_cardbin` VALUES ('1562', '平安银行', '5105840', 'PINGAN', '贷记IC卡', '16', '6', '625823', '1');
INSERT INTO `authbank_cardbin` VALUES ('1563', '长治市商业银行', '5121660', '', '长治商行银联晋龙卡', '17', '6', '622962', '0');
INSERT INTO `authbank_cardbin` VALUES ('1564', '承德市商业银行', '5131410', '', '热河卡', '19', '6', '622936', '0');
INSERT INTO `authbank_cardbin` VALUES ('1565', '承德银行', '5131419', '', '借记IC卡', '19', '6', '623060', '0');
INSERT INTO `authbank_cardbin` VALUES ('1566', '德州银行', '5154680', '', '长河借记卡', '19', '6', '622937', '0');
INSERT INTO `authbank_cardbin` VALUES ('1567', '德州银行', '5154680', '', '--', '19', '6', '623101', '0');
INSERT INTO `authbank_cardbin` VALUES ('1568', '邯郸市商业银行', '5171270', '', '邯银卡', '18', '6', '622960', '0');
INSERT INTO `authbank_cardbin` VALUES ('1569', '邯郸市商业银行', '5171270', '', '邯郸银行贵宾IC借记卡', '18', '6', '623523', '0');
INSERT INTO `authbank_cardbin` VALUES ('1570', '江苏银行', '5213000', '', '紫金信用卡(公务卡)', '16', '6', '628210', '1');
INSERT INTO `authbank_cardbin` VALUES ('1571', '江苏银行', '5213000', '', '紫金信用卡', '16', '6', '622283', '1');
INSERT INTO `authbank_cardbin` VALUES ('1572', '江苏银行', '5213000', '', '天翼联名信用卡', '16', '6', '625902', '1');
INSERT INTO `authbank_cardbin` VALUES ('1573', '平凉市商业银行', '5238333', '', '广成卡', '16', '6', '621010', '0');
INSERT INTO `authbank_cardbin` VALUES ('1574', '玉溪市商业银行', '5247410', '', '红塔卡', '19', '6', '622980', '0');
INSERT INTO `authbank_cardbin` VALUES ('1575', '玉溪市商业银行', '5247410', '', '红塔卡', '19', '6', '623135', '0');
INSERT INTO `authbank_cardbin` VALUES ('1576', '玉溪市商业银行', '5247410', '', '红塔卡', '16', '6', '628351', '1');
INSERT INTO `authbank_cardbin` VALUES ('1577', '浙江民泰商业银行', '5253450', '', '金融IC卡', '19', '6', '621726', '0');
INSERT INTO `authbank_cardbin` VALUES ('1578', '浙江民泰商业银行', '5253450', '', '民泰借记卡', '19', '6', '621088', '0');
INSERT INTO `authbank_cardbin` VALUES ('1579', '浙江民泰商业银行', '5253450', '', '金融IC卡C卡', '19', '6', '620517', '2');
INSERT INTO `authbank_cardbin` VALUES ('1580', '浙江民泰商业银行', '5253454', '', '银联标准普卡金卡', '16', '6', '622740', '1');
INSERT INTO `authbank_cardbin` VALUES ('1581', '浙江民泰商业银行', '5253454', '', '商惠通', '16', '6', '625036', '3');
INSERT INTO `authbank_cardbin` VALUES ('1582', '上饶市商业银行', '5264330', '', '三清山卡', '16', '6', '621014', '0');
INSERT INTO `authbank_cardbin` VALUES ('1583', '东营银行', '5274550', '', '胜利卡', '18', '6', '621004', '0');
INSERT INTO `authbank_cardbin` VALUES ('1584', '东营银行', '5274550', '', '借记卡', '18', '6', '623130', '0');
INSERT INTO `authbank_cardbin` VALUES ('1585', '泰安市商业银行', '5284630', '', '岱宗卡', '19', '6', '622972', '0');
INSERT INTO `authbank_cardbin` VALUES ('1586', '泰安市商业银行', '5284630', '', '市民一卡通', '19', '6', '623196', '0');
INSERT INTO `authbank_cardbin` VALUES ('1587', '浙江稠州商业银行', '5303380', '', '义卡', '16', '6', '621028', '0');
INSERT INTO `authbank_cardbin` VALUES ('1588', '浙江稠州商业银行', '5303380', '', '义卡借记IC卡', '19', '6', '623083', '0');
INSERT INTO `authbank_cardbin` VALUES ('1589', '浙江稠州商业银行', '5303387', '', '公务卡', '16', '6', '628250', '1');
INSERT INTO `authbank_cardbin` VALUES ('1590', '乌海银行股份有限公司', '5311930', '', '狮城借记卡', '19', '6', '622973', '0');
INSERT INTO `authbank_cardbin` VALUES ('1591', '乌海银行股份有限公司', '5311930', '', '--', '19', '6', '623153', '0');
INSERT INTO `authbank_cardbin` VALUES ('1592', '自贡市商业银行', '5326550', '', '借记IC卡', '19', '6', '623121', '0');
INSERT INTO `authbank_cardbin` VALUES ('1593', '自贡市商业银行', '5326560', '', '锦程卡', '19', '6', '621070', '0');
INSERT INTO `authbank_cardbin` VALUES ('1594', '龙江银行股份有限公司', '5332740', '', '万通卡', '19', '6', '622977', '0');
INSERT INTO `authbank_cardbin` VALUES ('1595', '鄂尔多斯银行股份有限公司', '5342050', '', '天骄卡', '19', '6', '622978', '0');
INSERT INTO `authbank_cardbin` VALUES ('1596', '鄂尔多斯银行', '5342050', '', '天骄公务卡', '16', '6', '628253', '1');
INSERT INTO `authbank_cardbin` VALUES ('1597', '鄂尔多斯银行股份有限公司', '5342050', '', '天骄借记复合卡', '19', '6', '623093', '0');
INSERT INTO `authbank_cardbin` VALUES ('1598', '鄂尔多斯银行股份有限公司', '5342050', '', '--', '16', '6', '628378', '1');
INSERT INTO `authbank_cardbin` VALUES ('1599', '鹤壁银行', '5354970', '', '鹤卡', '19', '6', '622979', '0');
INSERT INTO `authbank_cardbin` VALUES ('1600', '许昌银行', '5365030', '', '连城卡', '19', '6', '621035', '0');
INSERT INTO `authbank_cardbin` VALUES ('1601', '济宁银行股份有限公司', '5374610', '', '儒商卡', '19', '6', '621200', '0');
INSERT INTO `authbank_cardbin` VALUES ('1602', '济宁银行股份有限公司', '5374610', '', '--', '19', '6', '623116', '0');
INSERT INTO `authbank_cardbin` VALUES ('1603', '铁岭银行', '5392330', '', '龙凤卡', '19', '6', '621038', '0');
INSERT INTO `authbank_cardbin` VALUES ('1604', '铁岭银行', '5392339', '', '--', '19', '6', '623180', '0');
INSERT INTO `authbank_cardbin` VALUES ('1605', '乐山市商业银行', '5406650', '', '大福卡', '19', '6', '621086', '0');
INSERT INTO `authbank_cardbin` VALUES ('1606', '乐山市商业银行', '5406650', '', '--', '19', '6', '621498', '0');
INSERT INTO `authbank_cardbin` VALUES ('1607', '长安银行', '5417900', '', '长长卡', '19', '6', '621296', '0');
INSERT INTO `authbank_cardbin` VALUES ('1608', '长安银行', '5417900', '', '借记IC卡', '19', '6', '621448', '0');
INSERT INTO `authbank_cardbin` VALUES ('1609', '长安银行', '5417901', '', '--', '16', '6', '628385', '1');
INSERT INTO `authbank_cardbin` VALUES ('1610', '宝鸡商行', '5417930', '', '姜炎卡', '19', '6', '621044', '0');
INSERT INTO `authbank_cardbin` VALUES ('1611', '重庆三峡银行', '5426900', '', '财富人生卡', '16', '6', '622945', '0');
INSERT INTO `authbank_cardbin` VALUES ('1612', '重庆三峡银行', '5426900', '', '借记卡', '16', '6', '621755', '0');
INSERT INTO `authbank_cardbin` VALUES ('1613', '石嘴山银行', '5438720', '', '麒麟借记卡', '19', '6', '622940', '0');
INSERT INTO `authbank_cardbin` VALUES ('1614', '石嘴山银行', '5438720', '', '麒麟借记卡', '19', '6', '623120', '0');
INSERT INTO `authbank_cardbin` VALUES ('1615', '石嘴山银行', '5438729', '', '麒麟公务卡', '16', '6', '628355', '1');
INSERT INTO `authbank_cardbin` VALUES ('1616', '盘锦市商业银行', '5442320', '', '鹤卡', '19', '6', '621089', '0');
INSERT INTO `authbank_cardbin` VALUES ('1617', '盘锦市商业银行', '5442329', '', '盘锦市商业银行鹤卡', '19', '6', '623161', '0');
INSERT INTO `authbank_cardbin` VALUES ('1618', '昆仑银行股份有限公司', '5478820', '', '瑞卡', '19', '6', '621029', '0');
INSERT INTO `authbank_cardbin` VALUES ('1619', '昆仑银行股份有限公司', '5478820', '', '金融IC卡', '19', '6', '621766', '0');
INSERT INTO `authbank_cardbin` VALUES ('1620', '昆仑银行股份有限公司', '5478820', '', '', '19', '6', '623139', '0');
INSERT INTO `authbank_cardbin` VALUES ('1621', '平顶山银行股份有限公司', '5484950', '', '佛泉卡', '19', '6', '621071', '0');
INSERT INTO `authbank_cardbin` VALUES ('1622', '平顶山银行股份有限公司', '5484950', '', '--', '19', '6', '623152', '0');
INSERT INTO `authbank_cardbin` VALUES ('1623', '平顶山银行', '5484959', '', '平顶山银行公务卡', '16', '6', '628339', '1');
INSERT INTO `authbank_cardbin` VALUES ('1624', '朝阳银行', '5492340', '', '鑫鑫通卡', '19', '6', '621074', '0');
INSERT INTO `authbank_cardbin` VALUES ('1625', '朝阳银行', '5492340', '', '朝阳银行福农卡', '19', '6', '621515', '0');
INSERT INTO `authbank_cardbin` VALUES ('1626', '朝阳银行', '5492341', '', '红山卡', '19', '6', '623030', '0');
INSERT INTO `authbank_cardbin` VALUES ('1627', '宁波东海银行', '5503320', '', '绿叶卡', '18', '6', '621345', '0');
INSERT INTO `authbank_cardbin` VALUES ('1628', '遂宁市商业银行', '5516620', '', '锦程卡', '19', '6', '621090', '0');
INSERT INTO `authbank_cardbin` VALUES ('1629', '遂宁是商业银行', '5516629', '', '金荷卡', '19', '6', '623178', '0');
INSERT INTO `authbank_cardbin` VALUES ('1630', '保定银行', '5521340', '', '直隶卡', '19', '6', '621091', '0');
INSERT INTO `authbank_cardbin` VALUES ('1631', '保定银行', '5521379', '', '直隶卡', '19', '6', '623168', '0');
INSERT INTO `authbank_cardbin` VALUES ('1632', '邢台银行股份有限公司', '5541310', '', '金牛卡', '19', '6', '621238', '0');
INSERT INTO `authbank_cardbin` VALUES ('1633', '凉山州商业银行', '5556840', '', '锦程卡', '19', '6', '621057', '0');
INSERT INTO `authbank_cardbin` VALUES ('1634', '凉山州商业银行', '5556859', '', '金凉山卡', '19', '6', '623199', '0');
INSERT INTO `authbank_cardbin` VALUES ('1635', '漯河银行', '5565040', '', '福卡', '19', '6', '621075', '0');
INSERT INTO `authbank_cardbin` VALUES ('1636', '漯河银行', '5565040', '', '福源卡', '19', '6', '623037', '0');
INSERT INTO `authbank_cardbin` VALUES ('1637', '漯河银行', '5565040', '', '福源公务卡', '16', '6', '628303', '1');
INSERT INTO `authbank_cardbin` VALUES ('1638', '达州市商业银行', '5576750', '', '锦程卡', '19', '6', '621233', '0');
INSERT INTO `authbank_cardbin` VALUES ('1639', '新乡市商业银行', '5584980', '', '新卡', '18', '6', '621235', '0');
INSERT INTO `authbank_cardbin` VALUES ('1640', '晋中银行', '5591750', '', '九州方圆借记卡', '19', '6', '621223', '0');
INSERT INTO `authbank_cardbin` VALUES ('1641', '晋中银行', '5591750', '', '九州方圆卡', '19', '6', '621780', '0');
INSERT INTO `authbank_cardbin` VALUES ('1642', '驻马店银行', '5605110', '', '驿站卡', '19', '6', '621221', '0');
INSERT INTO `authbank_cardbin` VALUES ('1643', '驻马店银行', '5605128', '', '驿站卡', '19', '6', '623138', '0');
INSERT INTO `authbank_cardbin` VALUES ('1644', '驻马店银行', '5605129', '', '公务卡', '16', '6', '628389', '1');
INSERT INTO `authbank_cardbin` VALUES ('1645', '衡水银行', '5611480', '', '金鼎卡', '19', '6', '621239', '0');
INSERT INTO `authbank_cardbin` VALUES ('1646', '衡水银行', '5611481', '', '借记IC卡', '19', '6', '623068', '0');
INSERT INTO `authbank_cardbin` VALUES ('1647', '周口银行', '5625080', '', '如愿卡', '19', '6', '621271', '0');
INSERT INTO `authbank_cardbin` VALUES ('1648', '周口银行', '5625081', '', '公务卡', '16', '6', '628315', '1');
INSERT INTO `authbank_cardbin` VALUES ('1649', '阳泉市商业银行', '5631650', '', '金鼎卡', '16', '6', '621272', '0');
INSERT INTO `authbank_cardbin` VALUES ('1650', '阳泉市商业银行', '5631650', '', '金鼎卡', '16', '6', '621738', '0');
INSERT INTO `authbank_cardbin` VALUES ('1651', '宜宾市商业银行', '5646710', '', '锦程卡', '19', '6', '621273', '0');
INSERT INTO `authbank_cardbin` VALUES ('1652', '宜宾市商业银行', '5646710', '', '借记IC卡', '19', '6', '623079', '0');
INSERT INTO `authbank_cardbin` VALUES ('1653', '库尔勒市商业银行', '5658880', '', '孔雀胡杨卡', '18', '6', '621263', '0');
INSERT INTO `authbank_cardbin` VALUES ('1654', '雅安市商业银行', '5666770', '', '锦城卡', '19', '6', '621325', '0');
INSERT INTO `authbank_cardbin` VALUES ('1655', '雅安市商业银行', '5666770', '', '--', '19', '6', '623084', '0');
INSERT INTO `authbank_cardbin` VALUES ('1656', '商丘商行', '5675060', '', '百汇卡', '19', '6', '621337', '0');
INSERT INTO `authbank_cardbin` VALUES ('1657', '安阳银行', '5684960', '', '安鼎卡', '19', '6', '621327', '0');
INSERT INTO `authbank_cardbin` VALUES ('1658', '信阳银行', '5695150', '', '信阳卡', '19', '6', '621753', '0');
INSERT INTO `authbank_cardbin` VALUES ('1659', '信阳银行', '5695151', '', '公务卡', '16', '6', '628331', '1');
INSERT INTO `authbank_cardbin` VALUES ('1660', '信阳银行', '5695169', '', '信阳卡', '19', '6', '623160', '0');
INSERT INTO `authbank_cardbin` VALUES ('1661', '华融湘江银行', '5705500', '', '华融卡', '19', '6', '621366', '0');
INSERT INTO `authbank_cardbin` VALUES ('1662', '华融湘江银行', '5705500', '', '华融卡', '19', '6', '621388', '0');
INSERT INTO `authbank_cardbin` VALUES ('1663', '营口沿海银行', '5722280', '', '祥云借记卡', '19', '6', '621348', '0');
INSERT INTO `authbank_cardbin` VALUES ('1664', '景德镇商业银行', '5734220', '', '瓷都卡', '18', '6', '621359', '0');
INSERT INTO `authbank_cardbin` VALUES ('1665', '景德镇商业银行', '5734224', '', '贷记卡', '16', '6', '628361', '1');
INSERT INTO `authbank_cardbin` VALUES ('1666', '哈密市商业银行', '5748840', '', '瓜香借记卡', '19', '6', '621360', '0');
INSERT INTO `authbank_cardbin` VALUES ('1667', '哈密市商业银行', '5748844', '', '--', '19', '6', '623566', '0');
INSERT INTO `authbank_cardbin` VALUES ('1668', '湖北银行', '5755200', '', '金牛卡', '18', '6', '621217', '0');
INSERT INTO `authbank_cardbin` VALUES ('1669', '湖北银行', '5755200', '', '汉江卡', '19', '6', '622959', '0');
INSERT INTO `authbank_cardbin` VALUES ('1670', '湖北银行', '5755200', '', '借记卡', '18', '6', '621270', '0');
INSERT INTO `authbank_cardbin` VALUES ('1671', '湖北银行', '5755200', '', '三峡卡', '17', '6', '622396', '0');
INSERT INTO `authbank_cardbin` VALUES ('1672', '湖北银行', '5755200', '', '至尊卡', '17', '6', '622511', '0');
INSERT INTO `authbank_cardbin` VALUES ('1673', '湖北银行', '5755201', '', '金融IC卡', '19', '6', '623076', '0');
INSERT INTO `authbank_cardbin` VALUES ('1674', '西藏银行', '5767700', '', '借记IC卡', '19', '6', '621391', '0');
INSERT INTO `authbank_cardbin` VALUES ('1675', '新疆汇和银行', '5778981', '', '汇和卡', '19', '6', '621339', '0');
INSERT INTO `authbank_cardbin` VALUES ('1676', '广东华兴银行', '5785800', '', '借记卡', '19', '6', '621469', '0');
INSERT INTO `authbank_cardbin` VALUES ('1677', '广东华兴银行', '5785800', '', '华兴银联公司卡', '19', '6', '621625', '0');
INSERT INTO `authbank_cardbin` VALUES ('1678', '广东华兴银行', '5785800', '', '华兴联名IC卡', '19', '6', '623688', '0');
INSERT INTO `authbank_cardbin` VALUES ('1679', '广东华兴银行', '5785800', '', '华兴金融IC借记卡', '19', '6', '623113', '0');
INSERT INTO `authbank_cardbin` VALUES ('1680', '濮阳银行', '5795020', '', '龙翔卡', '19', '6', '621601', '0');
INSERT INTO `authbank_cardbin` VALUES ('1681', '宁波通商银行', '5803320', '', '借记卡', '19', '6', '621655', '0');
INSERT INTO `authbank_cardbin` VALUES ('1682', '甘肃银行', '5818200', '', '神舟兴陇借记卡', '19', '6', '621636', '0');
INSERT INTO `authbank_cardbin` VALUES ('1683', '甘肃银行', '5818201', '', '甘肃银行神州兴陇IC卡', '19', '6', '623182', '0');
INSERT INTO `authbank_cardbin` VALUES ('1684', '甘肃银行', '5818202', '', '贷记卡', '16', '6', '628356', '1');
INSERT INTO `authbank_cardbin` VALUES ('1685', '枣庄银行', '5824540', '', '借记IC卡', '19', '6', '623087', '0');
INSERT INTO `authbank_cardbin` VALUES ('1686', '本溪市商业银行', '5832250', '', '借记卡', '19', '6', '621696', '0');
INSERT INTO `authbank_cardbin` VALUES ('1687', '贵州银行', '5847000', '', '社保卡', '19', '6', '621460', '0');
INSERT INTO `authbank_cardbin` VALUES ('1688', '贵州银行', '5847000', '', '尊卡', '17', '6', '622939', '0');
INSERT INTO `authbank_cardbin` VALUES ('1689', '平安银行', '6105840', 'PINGAN', '一账通借贷合一钻石卡', '16', '6', '627069', '0');
INSERT INTO `authbank_cardbin` VALUES ('1690', '平安银行', '6105840', 'PINGAN', '一账通借贷合一卡普卡', '16', '6', '627066', '0');
INSERT INTO `authbank_cardbin` VALUES ('1691', '平安银行', '6105840', 'PINGAN', '一账通借贷合一卡金卡', '16', '6', '627067', '0');
INSERT INTO `authbank_cardbin` VALUES ('1692', '平安银行', '6105840', 'PINGAN', '一账通借贷合一白金卡', '16', '6', '627068', '0');
INSERT INTO `authbank_cardbin` VALUES ('1693', '上海农商银行', '14012900', '', '如意卡(银联卡)', '16', '6', '622478', '0');
INSERT INTO `authbank_cardbin` VALUES ('1694', '上海农商银行', '14012900', '', '如意卡(银联卡)', '16', '6', '940013', '0');
INSERT INTO `authbank_cardbin` VALUES ('1695', '上海农商银行', '14012900', '', '鑫通卡', '16', '6', '621495', '0');
INSERT INTO `authbank_cardbin` VALUES ('1696', '上海农商银行', '14012900', '', '国际如意卡', '19', '6', '621688', '0');
INSERT INTO `authbank_cardbin` VALUES ('1697', '上海农商银行', '14012900', '', '借记IC卡', '19', '6', '623162', '0');
INSERT INTO `authbank_cardbin` VALUES ('1698', '昆山农信社', '14023052', '', '江通卡(银联卡)', '19', '6', '622443', '0');
INSERT INTO `authbank_cardbin` VALUES ('1699', '昆山农信社', '14023052', '', '银联汇通卡(银联卡)', '19', '6', '940029', '0');
INSERT INTO `authbank_cardbin` VALUES ('1700', '昆山农信社', '14023052', '', '琼花卡', '19', '6', '623132', '0');
INSERT INTO `authbank_cardbin` VALUES ('1701', '常熟市农村商业银行', '14030001', '', '粒金贷记卡(银联卡)', '16', '6', '622462', '1');
INSERT INTO `authbank_cardbin` VALUES ('1702', '常熟市农村商业银行', '14030001', '', '公务卡', '16', '6', '628272', '1');
INSERT INTO `authbank_cardbin` VALUES ('1703', '常熟市农村商业银行', '14030001', '', '粒金准贷卡', '16', '6', '625101', '3');
INSERT INTO `authbank_cardbin` VALUES ('1704', '常熟农村商业银行', '14033055', '', '粒金借记卡(银联卡)', '19', '6', '622323', '0');
INSERT INTO `authbank_cardbin` VALUES ('1705', '常熟农村商业银行', '14033055', '', '粒金卡(银联卡)', '19', '7', '9400301', '0');
INSERT INTO `authbank_cardbin` VALUES ('1706', '常熟农村商业银行', '14033055', '', '粒金IC卡', '19', '6', '623071', '0');
INSERT INTO `authbank_cardbin` VALUES ('1707', '常熟农村商业银行', '14033055', '', '粒金卡', '19', '6', '603694', '0');
INSERT INTO `authbank_cardbin` VALUES ('1708', '深圳农村商业银行', '14045840', '', '信通卡(银联卡)', '16', '6', '622128', '0');
INSERT INTO `authbank_cardbin` VALUES ('1709', '深圳农村商业银行', '14045840', '', '信通商务卡(银联卡)', '16', '6', '622129', '0');
INSERT INTO `authbank_cardbin` VALUES ('1710', '深圳农村商业银行', '14045840', '', '信通卡', '16', '6', '623035', '0');
INSERT INTO `authbank_cardbin` VALUES ('1711', '深圳农村商业银行', '14045840', '', '信通商务卡', '16', '6', '623186', '0');
INSERT INTO `authbank_cardbin` VALUES ('1712', '广州农村商业银行股份有限公司', '14055810', '', '麒麟卡', '18', '6', '909810', '0');
INSERT INTO `authbank_cardbin` VALUES ('1713', '广州农村商业银行股份有限公司', '14055810', '', '麒麟卡(银联卡)', '18', '6', '940035', '0');
INSERT INTO `authbank_cardbin` VALUES ('1714', '广州农村商业银行', '14055810', '', '福农太阳卡', '18', '6', '621522', '0');
INSERT INTO `authbank_cardbin` VALUES ('1715', '广州农村商业银行股份有限公司', '14055810', '', '麒麟储蓄卡', '18', '6', '622439', '0');
INSERT INTO `authbank_cardbin` VALUES ('1716', '广东南海农村商业银行', '14075882', '', '盛通卡', '18', '6', '622271', '0');
INSERT INTO `authbank_cardbin` VALUES ('1717', '广东南海农村商业银行', '14075882', '', '盛通卡(银联卡)', '18', '6', '940037', '0');
INSERT INTO `authbank_cardbin` VALUES ('1718', '广东顺德农村商业银行', '14085883', '', '恒通卡(银联卡)', '16', '6', '940038', '0');
INSERT INTO `authbank_cardbin` VALUES ('1719', '广东顺德农村商业银行', '14085883', '', '恒通卡', '16', '6', '985262', '0');
INSERT INTO `authbank_cardbin` VALUES ('1720', '广东顺德农村商业银行', '14085883', '', '恒通卡(银联卡)', '16', '6', '622322', '0');
INSERT INTO `authbank_cardbin` VALUES ('1721', '昆明农联社', '14097310', '', '金碧白金卡', '19', '6', '621017', '0');
INSERT INTO `authbank_cardbin` VALUES ('1722', '昆明农联社', '14097310', '', '金碧卡', '18', '6', '018572', '0');
INSERT INTO `authbank_cardbin` VALUES ('1723', '昆明农联社', '14097310', '', '金碧一卡通(银联卡)', '16', '6', '622369', '0');
INSERT INTO `authbank_cardbin` VALUES ('1724', '昆明农联社', '14097310', '', '银联卡(银联卡)', '18', '6', '940042', '0');
INSERT INTO `authbank_cardbin` VALUES ('1725', '昆明农联社', '14097310', '', '金碧卡一卡通', '19', '6', '623190', '0');
INSERT INTO `authbank_cardbin` VALUES ('1726', '湖北农信社', '14105200', '', '信通卡', '16', '6', '622412', '0');
INSERT INTO `authbank_cardbin` VALUES ('1727', '湖北农信', '14105200', '', '福农小康卡', '16', '6', '621523', '0');
INSERT INTO `authbank_cardbin` VALUES ('1728', '湖北农信社', '14105200', '', '福卡IC借记卡', '16', '6', '623055', '0');
INSERT INTO `authbank_cardbin` VALUES ('1729', '湖北农信社', '14105200', '', '福卡(VIP卡)', '16', '6', '621013', '0');
INSERT INTO `authbank_cardbin` VALUES ('1730', '武汉农信', '14105210', '', '信通卡(银联卡)', '17', '6', '940044', '0');
INSERT INTO `authbank_cardbin` VALUES ('1731', '徐州市郊农村信用合作联社', '14113030', '', '信通卡(银联卡)', '16', '6', '622312', '0');
INSERT INTO `authbank_cardbin` VALUES ('1732', '江阴农村商业银行', '14123020', '', '暨阳公务卡', '16', '6', '628381', '1');
INSERT INTO `authbank_cardbin` VALUES ('1733', '江阴市农村商业银行', '14123020', '', '合作贷记卡(银联卡)', '16', '6', '622481', '1');
INSERT INTO `authbank_cardbin` VALUES ('1734', '江阴农村商业银行', '14123022', '', '合作借记卡', '16', '6', '622341', '0');
INSERT INTO `authbank_cardbin` VALUES ('1735', '江阴农村商业银行', '14123022', '', '合作卡(银联卡)', '16', '6', '940058', '0');
INSERT INTO `authbank_cardbin` VALUES ('1736', '江阴农村商业银行', '14123022', '', '暨阳卡', '16', '6', '623115', '0');
INSERT INTO `authbank_cardbin` VALUES ('1737', '重庆农村商业银行股份有限公司', '14136530', '', '信合平安卡', '16', '6', '622867', '0');
INSERT INTO `authbank_cardbin` VALUES ('1738', '重庆农村商业银行股份有限公司', '14136530', '', '信合希望卡', '16', '6', '622885', '0');
INSERT INTO `authbank_cardbin` VALUES ('1739', '重庆农村商业银行股份有限公司', '14136530', '', '信合一卡通(银联卡)', '16', '6', '940020', '0');
INSERT INTO `authbank_cardbin` VALUES ('1740', '重庆农村商业银行', '14136900', '', '江渝借记卡VIP卡', '16', '6', '621258', '0');
INSERT INTO `authbank_cardbin` VALUES ('1741', '重庆农村商业银行', '14136900', '', '江渝IC借记卡', '16', '6', '621465', '0');
INSERT INTO `authbank_cardbin` VALUES ('1742', '重庆农村商业银行', '14136900', '', '江渝乡情福农卡', '16', '6', '621528', '0');
INSERT INTO `authbank_cardbin` VALUES ('1743', '山东农村信用联合社', '14144500', '', '信通卡', '16', '6', '900105', '0');
INSERT INTO `authbank_cardbin` VALUES ('1744', '山东农村信用联合社', '14144500', '', '信通卡', '16', '6', '900205', '0');
INSERT INTO `authbank_cardbin` VALUES ('1745', '山东农村信用联合社', '14144500', '', '信通卡', '16', '6', '622319', '0');
INSERT INTO `authbank_cardbin` VALUES ('1746', '山东省农村信用社联合社', '14144500', '', '泰山福农卡', '16', '6', '621521', '0');
INSERT INTO `authbank_cardbin` VALUES ('1747', '山东省农村信用社联合社', '14144500', '', 'VIP卡', '16', '6', '621690', '0');
INSERT INTO `authbank_cardbin` VALUES ('1748', '山东省农村信用社联合社', '14144500', '', '泰山如意卡', '16', '6', '622320', '0');
INSERT INTO `authbank_cardbin` VALUES ('1749', '青岛农信', '14144520', '', '信通卡', '16', '8', '62231902', '0');
INSERT INTO `authbank_cardbin` VALUES ('1750', '青岛农信', '14144520', '', '信通卡', '16', '8', '90010502', '0');
INSERT INTO `authbank_cardbin` VALUES ('1751', '青岛农信', '14144520', '', '信通卡', '16', '8', '90020502', '0');
INSERT INTO `authbank_cardbin` VALUES ('1752', '东莞农村商业银行', '14156020', '', '信通卡(银联卡)', '19', '6', '622328', '0');
INSERT INTO `authbank_cardbin` VALUES ('1753', '东莞农村商业银行', '14156020', '', '信通卡(银联卡)', '19', '6', '940062', '0');
INSERT INTO `authbank_cardbin` VALUES ('1754', '东莞农村商业银行', '14156020', '', '信通信用卡', '16', '6', '625288', '1');
INSERT INTO `authbank_cardbin` VALUES ('1755', '东莞农村商业银行', '14156020', '', '信通借记卡', '19', '6', '623038', '0');
INSERT INTO `authbank_cardbin` VALUES ('1756', '东莞农村商业银行', '14156020', '', '贷记IC卡', '16', '6', '625888', '1');
INSERT INTO `authbank_cardbin` VALUES ('1757', '张家港农村商业银行', '14163056', '', '一卡通(银联卡)', '17', '6', '622332', '0');
INSERT INTO `authbank_cardbin` VALUES ('1758', '张家港农村商业银行', '14163056', '', '一卡通(银联卡)', '17', '6', '940063', '0');
INSERT INTO `authbank_cardbin` VALUES ('1759', '张家港农村商业银行', '14163056', '', '', '17', '6', '623123', '0');
INSERT INTO `authbank_cardbin` VALUES ('1760', '福建省农村信用社联合社', '14173900', '', '万通(借记)卡', '19', '6', '622127', '0');
INSERT INTO `authbank_cardbin` VALUES ('1761', '福建省农村信用社联合社', '14173900', '', '万通(借记)卡', '19', '6', '622184', '0');
INSERT INTO `authbank_cardbin` VALUES ('1762', '福建省农村信用社联合社', '14173900', '', '福建海峡旅游卡', '19', '6', '621251', '0');
INSERT INTO `authbank_cardbin` VALUES ('1763', '福建省农村信用社联合社', '14173900', '', '福万通福农卡', '19', '6', '621589', '0');
INSERT INTO `authbank_cardbin` VALUES ('1764', '福建省农村信用社联合社', '14173900', '', '借记卡', '19', '6', '623036', '0');
INSERT INTO `authbank_cardbin` VALUES ('1765', '福建省农村信用社联合社', '14173900', '', '社保卡', '19', '6', '621701', '0');
INSERT INTO `authbank_cardbin` VALUES ('1766', '北京农村商业银行', '14181000', '', '信通卡', '19', '6', '622138', '0');
INSERT INTO `authbank_cardbin` VALUES ('1767', '北京农村商业银行', '14181000', '', '惠通卡', '19', '6', '621066', '0');
INSERT INTO `authbank_cardbin` VALUES ('1768', '北京农村商业银行', '14181000', '', '凤凰福农卡', '19', '6', '621560', '0');
INSERT INTO `authbank_cardbin` VALUES ('1769', '北京农村商业银行', '14181000', '', '惠通卡', '19', '6', '621068', '0');
INSERT INTO `authbank_cardbin` VALUES ('1770', '北京农村商业银行', '14181000', '', '中国旅行卡', '19', '6', '620088', '0');
INSERT INTO `authbank_cardbin` VALUES ('1771', '北京农村商业银行', '14181000', '', '凤凰卡', '19', '6', '621067', '0');
INSERT INTO `authbank_cardbin` VALUES ('1772', '北京农商行', '14181001', '', '凤凰标准卡', '16', '6', '625186', '1');
INSERT INTO `authbank_cardbin` VALUES ('1773', '北京农商行', '14181001', '', '凤凰公务卡', '16', '6', '628336', '1');
INSERT INTO `authbank_cardbin` VALUES ('1774', '北京农商行', '14181001', '', '凤凰福农卡', '16', '6', '625526', '1');
INSERT INTO `authbank_cardbin` VALUES ('1775', '天津农村商业银行', '14191100', '', '吉祥商联IC卡', '19', '6', '622531', '0');
INSERT INTO `authbank_cardbin` VALUES ('1776', '天津农村商业银行', '14191100', '', '信通借记卡(银联卡)', '19', '6', '622329', '0');
INSERT INTO `authbank_cardbin` VALUES ('1777', '天津农村商业银行', '14191100', '', '借记IC卡', '19', '6', '623103', '0');
INSERT INTO `authbank_cardbin` VALUES ('1778', '鄞州农村合作银行', '14203320', '', '蜜蜂借记卡(银联卡)', '16', '6', '622339', '0');
INSERT INTO `authbank_cardbin` VALUES ('1779', '宁波鄞州农村合作银行', '14203323', '', '蜜蜂电子钱包(IC)', '16', '6', '620500', '0');
INSERT INTO `authbank_cardbin` VALUES ('1780', '宁波鄞州农村合作银行', '14203323', '', '蜜蜂IC借记卡', '16', '6', '621024', '0');
INSERT INTO `authbank_cardbin` VALUES ('1781', '宁波鄞州农村合作银行', '14203323', '', '蜜蜂贷记IC卡', '16', '6', '622289', '1');
INSERT INTO `authbank_cardbin` VALUES ('1782', '宁波鄞州农村合作银行', '14203323', '', '蜜蜂贷记卡', '16', '6', '622389', '1');
INSERT INTO `authbank_cardbin` VALUES ('1783', '宁波鄞州农村合作银行', '14203323', '', '公务卡', '16', '6', '628300', '1');
INSERT INTO `authbank_cardbin` VALUES ('1784', '宁波鄞州农村合作银行', '14203323', '', '--', '19', '6', '623539', '0');
INSERT INTO `authbank_cardbin` VALUES ('1785', '佛山市三水区农村信用合作社', '14215881', '', '信通卡(银联卡)', '19', '6', '622343', '0');
INSERT INTO `authbank_cardbin` VALUES ('1786', '成都农村商业银行', '14226510', '', '福农卡', '16', '6', '625516', '3');
INSERT INTO `authbank_cardbin` VALUES ('1787', '成都农村商业银行', '14226510', '', '福农卡', '19', '6', '621516', '0');
INSERT INTO `authbank_cardbin` VALUES ('1788', '成都农村商业银行股份有限公司', '14226510', '', '天府借记卡(银联卡)', '19', '6', '622345', '0');
INSERT INTO `authbank_cardbin` VALUES ('1789', '江苏农信社', '14243000', '', '圆鼎卡(银联卡)', '19', '6', '622452', '0');
INSERT INTO `authbank_cardbin` VALUES ('1790', '江苏省农村信用社联合社', '14243000', '', '福农卡', '19', '6', '621578', '0');
INSERT INTO `authbank_cardbin` VALUES ('1791', '江苏农信社', '14243000', '', '圆鼎卡(银联卡)', '19', '6', '622324', '0');
INSERT INTO `authbank_cardbin` VALUES ('1792', '江苏省农村信用社联合社', '14243001', '', '圆鼎借记IC卡', '19', '6', '623066', '0');
INSERT INTO `authbank_cardbin` VALUES ('1793', '吴江农商行', '14283054', '', '垂虹贷记卡', '16', '6', '622648', '1');
INSERT INTO `authbank_cardbin` VALUES ('1794', '吴江农商行', '14283054', '', '银联标准公务卡', '16', '6', '628248', '1');
INSERT INTO `authbank_cardbin` VALUES ('1795', '吴江农商行', '14283054', '', '垂虹卡(银联卡)', '16', '6', '622488', '0');
INSERT INTO `authbank_cardbin` VALUES ('1796', '吴江农商行', '14283054', '', '--', '16', '6', '623110', '0');
INSERT INTO `authbank_cardbin` VALUES ('1797', '浙江省农村信用社联合社', '14293300', '', '丰收卡(银联卡)', '19', '6', '622858', '0');
INSERT INTO `authbank_cardbin` VALUES ('1798', '浙江省农村信用社联合社', '14293300', '', '丰收小额贷款卡', '19', '6', '621058', '0');
INSERT INTO `authbank_cardbin` VALUES ('1799', '浙江省农村信用社联合社', '14293300', '', '丰收福农卡', '19', '6', '621527', '0');
INSERT INTO `authbank_cardbin` VALUES ('1800', '浙江省农村信用社联合社', '14293300', '', '借记IC卡', '19', '6', '623091', '0');
INSERT INTO `authbank_cardbin` VALUES ('1801', '浙江省农村信用社联合社', '14293300', '', '丰收贷记卡', '16', '6', '622288', '1');
INSERT INTO `authbank_cardbin` VALUES ('1802', '浙江省农村信用社联合社', '14293300', '', '银联标准公务卡', '16', '6', '628280', '1');
INSERT INTO `authbank_cardbin` VALUES ('1803', '浙江省农村信用社联合社', '14293301', '', '--', '16', '6', '622686', '1');
INSERT INTO `authbank_cardbin` VALUES ('1804', '苏州银行股份有限公司', '14303050', '', '新苏卡(银联卡)', '19', '6', '622855', '0');
INSERT INTO `authbank_cardbin` VALUES ('1805', '苏州银行股份有限公司', '14303050', '', '新苏卡', '19', '6', '621461', '0');
INSERT INTO `authbank_cardbin` VALUES ('1806', '苏州银行股份有限公司', '14303050', '', '金桂卡', '19', '6', '623521', '0');
INSERT INTO `authbank_cardbin` VALUES ('1807', '珠海农村商业银行', '14315850', '', '信通卡(银联卡)', '19', '6', '622859', '0');
INSERT INTO `authbank_cardbin` VALUES ('1808', '太仓农村商业银行', '14333051', '', '郑和卡(银联卡)', '19', '6', '622869', '0');
INSERT INTO `authbank_cardbin` VALUES ('1809', '太仓农村商业银行', '14333051', '', '郑和IC借记卡', '19', '6', '623075', '0');
INSERT INTO `authbank_cardbin` VALUES ('1810', '尧都区农村信用合作社联社', '14341770', '', '天河卡', '19', '6', '622882', '0');
INSERT INTO `authbank_cardbin` VALUES ('1811', '贵州省农村信用社联合社', '14367000', '', '信合卡', '19', '6', '622893', '0');
INSERT INTO `authbank_cardbin` VALUES ('1812', '贵州省农村信用社联合社', '14367000', '', '信合福农卡', '19', '6', '621590', '0');
INSERT INTO `authbank_cardbin` VALUES ('1813', '无锡农村商业银行', '14373020', '', '金阿福', '16', '6', '622895', '0');
INSERT INTO `authbank_cardbin` VALUES ('1814', '无锡农村商业银行', '14373020', '', '借记IC卡', '16', '6', '623125', '0');
INSERT INTO `authbank_cardbin` VALUES ('1815', '湖南省农村信用社联合社', '14385500', '', '福祥借记IC卡', '19', '6', '623090', '0');
INSERT INTO `authbank_cardbin` VALUES ('1816', '湖南省农村信用社联合社', '14385500', '', '福农借记卡', '19', '6', '621519', '0');
INSERT INTO `authbank_cardbin` VALUES ('1817', '湖南省农村信用社联合社', '14385500', '', '福祥便民卡', '19', '6', '621539', '0');
INSERT INTO `authbank_cardbin` VALUES ('1818', '湖南省农村信用社联合社', '14385500', '', '福祥借记卡', '19', '6', '622169', '0');
INSERT INTO `authbank_cardbin` VALUES ('1819', '江西农信联合社', '14394200', '', '百福卡', '19', '6', '622681', '0');
INSERT INTO `authbank_cardbin` VALUES ('1820', '江西农信联合社', '14394200', '', '百福卡', '19', '6', '622682', '0');
INSERT INTO `authbank_cardbin` VALUES ('1821', '江西农信联合社', '14394200', '', '百福卡', '19', '6', '622683', '0');
INSERT INTO `authbank_cardbin` VALUES ('1822', '江西农信联合社', '14394200', '', '百福福农卡', '16', '6', '621592', '0');
INSERT INTO `authbank_cardbin` VALUES ('1823', '河南省农村信用社联合社', '14404900', '', '金燕卡', '18', '6', '622991', '0');
INSERT INTO `authbank_cardbin` VALUES ('1824', '河南省农村信用社联合社', '14404900', '', '金燕快货通福农卡', '18', '6', '621585', '0');
INSERT INTO `authbank_cardbin` VALUES ('1825', '河南省农村信用社联合社', '14404900', '', '借记卡', '18', '6', '623013', '0');
INSERT INTO `authbank_cardbin` VALUES ('1826', '河南省农村信用社联合社', '14404900', '', '', '18', '6', '623059', '0');
INSERT INTO `authbank_cardbin` VALUES ('1827', '河北省农村信用社联合社', '14411200', '', '信通卡', '19', '6', '621021', '0');
INSERT INTO `authbank_cardbin` VALUES ('1828', '河北省农村信用社联合社', '14411200', '', '信通卡(银联卡)', '19', '6', '622358', '0');
INSERT INTO `authbank_cardbin` VALUES ('1829', '河北省农村信用社联合社', '14411200', '', '借记卡', '19', '6', '623025', '0');
INSERT INTO `authbank_cardbin` VALUES ('1830', '河北省农村信用社联合社', '14411202', '', '信通IC卡', '19', '6', '623501', '0');
INSERT INTO `authbank_cardbin` VALUES ('1831', '陕西省农村信用社联合社', '14427900', '', '陕西信合富秦卡', '19', '6', '622506', '0');
INSERT INTO `authbank_cardbin` VALUES ('1832', '陕西省农村信用社联合社', '14427900', '', '富秦家乐福农卡', '19', '6', '621566', '0');
INSERT INTO `authbank_cardbin` VALUES ('1833', '陕西省农村信用社联合社', '14427900', '', '富秦卡', '19', '6', '623027', '0');
INSERT INTO `authbank_cardbin` VALUES ('1834', '陕西省农村信用社联合社', '14427900', '', '社会保障卡（陕西信合）', '19', '6', '623028', '0');
INSERT INTO `authbank_cardbin` VALUES ('1835', '陕西省农村信用社联合社', '14427901', '', '富秦公务卡', '16', '6', '628323', '1');
INSERT INTO `authbank_cardbin` VALUES ('1836', '广西农村信用社联合社', '14436100', '', '桂盛卡', '19', '6', '622992', '0');
INSERT INTO `authbank_cardbin` VALUES ('1837', '广西农村信用社联合社', '14436100', '', '桂盛IC借记卡', '19', '6', '623133', '0');
INSERT INTO `authbank_cardbin` VALUES ('1838', '广西壮族自治区农村信用社联合社', '14436101', '', '', '16', '6', '628330', '1');
INSERT INTO `authbank_cardbin` VALUES ('1839', '新疆维吾尔自治区农村信用社联合', '14448800', '', '玉卡', '16', '6', '621008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1840', '新疆农村信用社联合社', '14448800', '', '福农卡', '16', '6', '621525', '0');
INSERT INTO `authbank_cardbin` VALUES ('1841', '新疆维吾尔自治区农村信用社联合', '14448800', '', '玉卡金融IC借记卡', '16', '6', '621287', '0');
INSERT INTO `authbank_cardbin` VALUES ('1842', '新疆维吾尔自治区农村信用社联合社', '14448802', '', '玉卡公务卡', '16', '6', '628277', '1');
INSERT INTO `authbank_cardbin` VALUES ('1843', '吉林农信联合社', '14452400', '', '吉卡', '19', '6', '622935', '0');
INSERT INTO `authbank_cardbin` VALUES ('1844', '吉林农信联合社', '14452400', '', '吉林农信银联标准吉卡福农借记卡', '19', '6', '621531', '0');
INSERT INTO `authbank_cardbin` VALUES ('1845', '吉林省农村信用社联合社', '14452402', '', '借记IC卡', '19', '6', '623181', '0');
INSERT INTO `authbank_cardbin` VALUES ('1846', '黄河农村商业银行', '14468700', '', '黄河卡', '19', '6', '622947', '0');
INSERT INTO `authbank_cardbin` VALUES ('1847', '黄河农村商业银行', '14468700', '', '黄河富农卡福农卡', '19', '6', '621561', '0');
INSERT INTO `authbank_cardbin` VALUES ('1848', '黄河农村商业银行', '14468700', '', '借记IC卡', '19', '6', '623095', '0');
INSERT INTO `authbank_cardbin` VALUES ('1849', '宁夏黄河农村商业银行', '14468702', '', '黄河贷记卡', '16', '6', '625150', '1');
INSERT INTO `authbank_cardbin` VALUES ('1850', '安徽省农村信用社联合社', '14473600', '', '金农易贷福农卡', '19', '6', '621526', '0');
INSERT INTO `authbank_cardbin` VALUES ('1851', '安徽省农村信用社联合社', '14473600', '', '金农卡', '19', '6', '622953', '0');
INSERT INTO `authbank_cardbin` VALUES ('1852', '海南省农村信用社联合社', '14486400', '', '大海福农卡', '19', '6', '621536', '0');
INSERT INTO `authbank_cardbin` VALUES ('1853', '海南省农村信用社联合社', '14486400', '', '大海卡', '19', '6', '621036', '0');
INSERT INTO `authbank_cardbin` VALUES ('1854', '海南省农村信用社联合社', '14486400', '', '金融IC借记卡', '19', '6', '621458', '0');
INSERT INTO `authbank_cardbin` VALUES ('1855', '青海省农村信用社联合社', '14498500', '', '紫丁香福农卡', '16', '6', '621517', '0');
INSERT INTO `authbank_cardbin` VALUES ('1856', '青海省农村信用社联合社', '14498500', '', '紫丁香借记卡', '16', '6', '621065', '0');
INSERT INTO `authbank_cardbin` VALUES ('1857', '青海省农村信用社联合社', '14498500', '', '紫丁香', '16', '6', '623017', '0');
INSERT INTO `authbank_cardbin` VALUES ('1858', '青海省农村信用社联合社', '14498501', '', '青海省公务卡', '16', '6', '628289', '1');
INSERT INTO `authbank_cardbin` VALUES ('1859', '广东省农村信用社联合社', '14505800', '', '信通卡(银联卡)', '19', '6', '622477', '0');
INSERT INTO `authbank_cardbin` VALUES ('1860', '广东省农村信用社联合社', '14505800', '', '信通白金卡', '19', '6', '622509', '0');
INSERT INTO `authbank_cardbin` VALUES ('1861', '广东省农村信用社联合社', '14505800', '', '信通金卡', '19', '6', '622510', '0');
INSERT INTO `authbank_cardbin` VALUES ('1862', '广东省农村信用社联合社', '14505800', '', '信通卡(银联卡)', '16', '6', '622302', '0');
INSERT INTO `authbank_cardbin` VALUES ('1863', '广东省农村信用社联合社', '14505800', '', '信通卡(银联卡)', '19', '6', '622362', '0');
INSERT INTO `authbank_cardbin` VALUES ('1864', '广东省农村信用社联合社', '14505800', '', '珠江平安卡', '19', '6', '621018', '0');
INSERT INTO `authbank_cardbin` VALUES ('1865', '广东省农村信用社联合社', '14505800', '', '珠江平安福农卡', '19', '6', '621518', '0');
INSERT INTO `authbank_cardbin` VALUES ('1866', '广东省农村信用社联合社', '14505800', '', '珠江平安卡', '19', '6', '621728', '0');
INSERT INTO `authbank_cardbin` VALUES ('1867', '广东省农村信用社联合社', '14505800', '', '信通卡(银联卡)', '19', '6', '622470', '0');
INSERT INTO `authbank_cardbin` VALUES ('1868', '内蒙古自治区农村信用社联合式', '14511900', '', '信合金牛卡', '19', '6', '622976', '0');
INSERT INTO `authbank_cardbin` VALUES ('1869', '内蒙古自治区农村信用社联合式', '14511900', '', '金牛福农卡', '19', '6', '621533', '0');
INSERT INTO `authbank_cardbin` VALUES ('1870', '内蒙古自治区农村信用社联合式', '14511900', '', '白金卡', '19', '6', '621362', '0');
INSERT INTO `authbank_cardbin` VALUES ('1871', '四川省农村信用社联合社', '14526500', '', '蜀信卡', '19', '6', '621033', '0');
INSERT INTO `authbank_cardbin` VALUES ('1872', '四川省农村信用社联合社', '14526500', '', '蜀信贵宾卡', '19', '6', '621099', '0');
INSERT INTO `authbank_cardbin` VALUES ('1873', '四川省农村信用社联合社', '14526500', '', '蜀信卡', '19', '6', '621457', '0');
INSERT INTO `authbank_cardbin` VALUES ('1874', '四川省农村信用社联合社', '14526500', '', '蜀信社保卡', '19', '6', '621459', '0');
INSERT INTO `authbank_cardbin` VALUES ('1875', '四川省农村信用社联合社', '14526500', '', '蜀信福农卡', '19', '6', '621530', '0');
INSERT INTO `authbank_cardbin` VALUES ('1876', '四川省农村信用社联合社', '14526500', '', '蜀信旅游卡', '19', '6', '623201', '0');
INSERT INTO `authbank_cardbin` VALUES ('1877', '四川省农村信用社联合社', '14526501', '', '兴川公务卡', '16', '6', '628297', '1');
INSERT INTO `authbank_cardbin` VALUES ('1878', '甘肃省农村信用社联合社', '14538200', '', '飞天卡', '19', '6', '621061', '0');
INSERT INTO `authbank_cardbin` VALUES ('1879', '甘肃省农村信用社联合社', '14538200', '', '福农卡', '19', '6', '621520', '0');
INSERT INTO `authbank_cardbin` VALUES ('1880', '甘肃省农村信用社联合社', '14538200', '', '飞天金融IC借记卡', '19', '6', '623065', '0');
INSERT INTO `authbank_cardbin` VALUES ('1881', '甘肃省农村信用社联合社', '14538202', '', '公务卡', '16', '6', '628332', '1');
INSERT INTO `authbank_cardbin` VALUES ('1882', '辽宁省农村信用社联合社', '14540001', '', '金信卡', '19', '6', '621449', '0');
INSERT INTO `authbank_cardbin` VALUES ('1883', '辽宁省农村信用社联合社', '14542200', '', '金信卡', '19', '6', '621026', '0');
INSERT INTO `authbank_cardbin` VALUES ('1884', '山西省农村信用社联合社', '14551600', '', '关帝银行卡', '19', '6', '622968', '0');
INSERT INTO `authbank_cardbin` VALUES ('1885', '山西省农村信用社', '14551600', '', '信合通', '19', '6', '621280', '0');
INSERT INTO `authbank_cardbin` VALUES ('1886', '山西省农村信用社联合社', '14551600', '', '信合通', '19', '6', '621580', '0');
INSERT INTO `authbank_cardbin` VALUES ('1887', '山西省农村信用社联合社', '14551600', '', '信合通金融IC卡', '19', '6', '623051', '0');
INSERT INTO `authbank_cardbin` VALUES ('1888', '天津滨海农村商业银行', '14561100', '', '四海通卡', '19', '6', '621073', '0');
INSERT INTO `authbank_cardbin` VALUES ('1889', '天津滨海农村商业银行', '14561100', '', '四海通e芯卡', '19', '6', '623109', '0');
INSERT INTO `authbank_cardbin` VALUES ('1890', '黑龙江省农村信用社联合社', '14572600', '', '', '19', '6', '623516', '0');
INSERT INTO `authbank_cardbin` VALUES ('1891', '黑龙江省农村信用社联合社', '14572600', '', '鹤卡', '19', '6', '621228', '0');
INSERT INTO `authbank_cardbin` VALUES ('1892', '黑龙江省农村信用社联合社', '14572600', '', '丰收时贷福农卡', '19', '6', '621557', '0');
INSERT INTO `authbank_cardbin` VALUES ('1893', '武汉农村商业银行', '14595210', '', '汉卡', '19', '6', '621361', '0');
INSERT INTO `authbank_cardbin` VALUES ('1894', '武汉农村商业银行', '14595210', '', '汉卡', '19', '6', '623033', '0');
INSERT INTO `authbank_cardbin` VALUES ('1895', '武汉农村商业银行', '14595210', '', '中国旅游卡', '19', '6', '623207', '0');
INSERT INTO `authbank_cardbin` VALUES ('1896', '江南农村商业银行', '14603040', '', '借记IC卡', '19', '6', '623189', '0');
INSERT INTO `authbank_cardbin` VALUES ('1897', '海口联合农村商业银行', '14616410', '', '海口联合农村商业银行合卡', '19', '6', '623510', '0');
INSERT INTO `authbank_cardbin` VALUES ('1898', '安吉交银村镇银行', '15003363', '', '吉祥借记卡', '19', '9', '621056802', '0');
INSERT INTO `authbank_cardbin` VALUES ('1899', '大邑交银兴民村镇银行', '15006518', '', '借记卡', '19', '9', '621056801', '0');
INSERT INTO `authbank_cardbin` VALUES ('1900', '石河子交银村镇银行', '15009028', '', '戈壁明珠卡', '19', '9', '621056803', '0');
INSERT INTO `authbank_cardbin` VALUES ('1901', '湖北嘉鱼吴江村镇银行', '15015363', '', '垂虹卡', '16', '6', '622995', '0');
INSERT INTO `authbank_cardbin` VALUES ('1902', '青岛即墨京都村镇银行', '15024521', '', '凤凰卡', '19', '10', '6229756114', '0');
INSERT INTO `authbank_cardbin` VALUES ('1903', '湖北仙桃北农商村镇银行', '15025371', '', '凤凰卡', '19', '10', '6229756115', '0');
INSERT INTO `authbank_cardbin` VALUES ('1904', '句容茅山村镇银行', '15033142', '', '暨阳卡', '16', '8', '62105913', '0');
INSERT INTO `authbank_cardbin` VALUES ('1905', '兴化苏南村镇银行', '15033161', '', '暨阳卡', '16', '8', '62105916', '0');
INSERT INTO `authbank_cardbin` VALUES ('1906', '海口苏南村镇银行', '15036410', '', '暨阳卡', '16', '8', '62105915', '0');
INSERT INTO `authbank_cardbin` VALUES ('1907', '海口苏南村镇银行', '15036410', '', '暨阳卡', '16', '8', '62105905', '0');
INSERT INTO `authbank_cardbin` VALUES ('1908', '双流诚民村镇银行', '15036512', '', '暨阳卡', '16', '8', '62105901', '0');
INSERT INTO `authbank_cardbin` VALUES ('1909', '宣汉诚民村镇银行', '15036753', '', '暨阳卡', '16', '8', '62105900', '0');
INSERT INTO `authbank_cardbin` VALUES ('1910', '福建建瓯石狮村镇银行', '15044015', '', '玉竹卡', '19', '6', '621053', '0');
INSERT INTO `authbank_cardbin` VALUES ('1911', '恩施常农商村镇银行', '15055411', '', '恩施村镇银行借记卡', '19', '9', '621260002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1912', '咸丰常农商村镇银行', '15055416', '', '借记卡', '19', '9', '621260001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1913', '哈尔滨呼兰浦发村镇银行股份有限公司', '15072610', '', '哈尔滨呼兰浦发村镇银行借记卡', '19', '9', '621275281', '0');
INSERT INTO `authbank_cardbin` VALUES ('1914', '奉贤浦发村镇银行', '15072900', '', '奉贤浦发村镇银行借记卡', '19', '9', '621275111', '0');
INSERT INTO `authbank_cardbin` VALUES ('1915', '资兴浦发村镇银行', '15075632', '', '资兴浦发村镇银行借记卡', '19', '9', '621275141', '0');
INSERT INTO `authbank_cardbin` VALUES ('1916', '临武浦发村镇银行', '15075638', '', '临武浦发村镇银行借记卡', '19', '9', '621275261', '0');
INSERT INTO `authbank_cardbin` VALUES ('1917', '韩城浦发村镇银行股份有限公司', '15077972', '', '韩城浦发村镇银行借记卡', '19', '9', '621275211', '0');
INSERT INTO `authbank_cardbin` VALUES ('1918', '浙江乐清联合村镇银行', '15083333', '', '联合卡', '19', '9', '621092003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1919', '浙江嘉善联合村镇银行', '15083351', '', '联合卡', '19', '9', '621092002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1920', '浙江长兴联合村镇银行', '15083362', '', '联合卡', '19', '9', '621092001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1921', '浙江绍兴县联合村镇银行', '15083370', '', '联合卡', '19', '9', '621092008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1922', '浙江义乌联合村镇银行', '15083387', '', '联合卡', '19', '9', '621092006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1923', '浙江常山联合村镇银行', '15083412', '', '联合卡', '19', '9', '621092004', '0');
INSERT INTO `authbank_cardbin` VALUES ('1924', '浙江温岭联合村镇银行', '15083454', '', '联合卡', '19', '9', '621092005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1925', '浙江平湖工银村镇银行', '15103352', '', '金平卡', '19', '6', '621230', '0');
INSERT INTO `authbank_cardbin` VALUES ('1926', '重庆璧山工银村镇银行', '15106919', '', '翡翠卡', '19', '6', '621229', '0');
INSERT INTO `authbank_cardbin` VALUES ('1927', '北京密云汇丰村镇银行', '15111027', '', '借记卡', '16', '9', '621250004', '0');
INSERT INTO `authbank_cardbin` VALUES ('1928', '福建永安汇丰村镇银行', '15113961', '', '借记卡', '16', '9', '621250003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1929', '湖北随州曾都汇丰村镇银行', '15115270', '', '借记卡', '16', '9', '621250001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1930', '广东恩平汇丰村镇银行', '15115893', '', '借记卡', '16', '9', '621250005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1931', '重庆大足汇丰村镇银行有限责任公司', '15116917', '', '借记卡', '16', '9', '621250002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1932', '江苏沭阳东吴村镇银行', '15123181', '', '新苏借记卡', '19', '9', '621241001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1933', '重庆农村商业银行', '15136900', '', '银联标准贷记卡', '16', '6', '622218', '1');
INSERT INTO `authbank_cardbin` VALUES ('1934', '重庆农村商业银行', '15136900', '', '公务卡', '16', '6', '628267', '1');
INSERT INTO `authbank_cardbin` VALUES ('1935', '鄂尔多斯市东胜蒙银村镇银行', '15142050', '', '龙源腾借记卡', '19', '9', '621346003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1936', '方大村镇银行', '15142080', '', '胡杨卡神州卡', '19', '9', '621346002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1937', '深圳龙岗鼎业村镇银行', '15145840', '', '鼎业卡', '19', '9', '621346001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1938', '北京大兴九银村镇银行', '15151000', '', '北京大兴九银村镇银行卡', '19', '9', '621326919', '0');
INSERT INTO `authbank_cardbin` VALUES ('1939', '中山小榄村镇银行', '15156030', '', '菊卡', '18', '9', '621326763', '0');
INSERT INTO `authbank_cardbin` VALUES ('1940', '江苏邗江民泰村镇银行', '15173120', '', '金荷花借记卡', '19', '9', '621338001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1941', '天津静海新华村镇银行', '15181123', '', '新华卡', '19', '9', '621353008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1942', '天津静海新华村镇银行', '15181123', '', '新华卡', '19', '9', '621353108', '0');
INSERT INTO `authbank_cardbin` VALUES ('1943', '安徽当涂新华村镇银行', '15183651', '', '新华卡', '19', '9', '621353002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1944', '安徽当涂新华村镇银行', '15183651', '', '新华卡', '19', '9', '621353102', '0');
INSERT INTO `authbank_cardbin` VALUES ('1945', '安徽和县新华村镇银行', '15183653', '', '新华卡', '19', '9', '621353005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1946', '安徽和县新华村镇银行', '15183653', '', '新华卡', '19', '9', '621353105', '0');
INSERT INTO `authbank_cardbin` VALUES ('1947', '望江新华村镇银行', '15183687', '', '新华卡', '19', '9', '621353007', '0');
INSERT INTO `authbank_cardbin` VALUES ('1948', '望江新华村镇银行', '15183687', '', '新华卡', '19', '9', '621353107', '0');
INSERT INTO `authbank_cardbin` VALUES ('1949', '郎溪新华村镇银行', '15183772', '', '新华卡', '19', '9', '621353003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1950', '郎溪新华村镇银行', '15183772', '', '新华卡', '19', '9', '621353103', '0');
INSERT INTO `authbank_cardbin` VALUES ('1951', '江西兴国新华村镇银行', '15184299', '', '新华卡', '19', '9', '621353006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1952', '广州番禹新华村镇银行', '15185810', '', '新华卡', '19', '9', '621353001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1953', '广州番禹新华村镇银行', '15185810', '', '新华卡', '19', '9', '621353101', '0');
INSERT INTO `authbank_cardbin` VALUES ('1954', '睢宁中银富登村镇银行', '15193034', '', '借记卡', '19', '9', '621356032', '0');
INSERT INTO `authbank_cardbin` VALUES ('1955', '宁波镇海中银富登村镇银行', '15193320', '', '借记卡', '19', '9', '621356014', '0');
INSERT INTO `authbank_cardbin` VALUES ('1956', '宁海中银富登村镇银行', '15193322', '', '借记卡', '19', '9', '621356013', '0');
INSERT INTO `authbank_cardbin` VALUES ('1957', '颍上中银富登村镇银行', '15193729', '', '借记卡', '19', '9', '621356030', '0');
INSERT INTO `authbank_cardbin` VALUES ('1958', '界首中银富登村镇银行', '15193731', '', '借记卡', '19', '9', '621356026', '0');
INSERT INTO `authbank_cardbin` VALUES ('1959', '来安中银富登村镇银行', '15193753', '', '借记卡', '19', '9', '621356016', '0');
INSERT INTO `authbank_cardbin` VALUES ('1960', '全椒中银富登村镇银行', '15193754', '', '借记卡', '19', '9', '621356015', '0');
INSERT INTO `authbank_cardbin` VALUES ('1961', '青州中银富登村镇银行', '15194588', '', '借记卡', '19', '9', '621356005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1962', '嘉祥中银富登村镇银行', '15194616', '', '借记卡', '19', '9', '621356018', '0');
INSERT INTO `authbank_cardbin` VALUES ('1963', '曲阜中银富登村镇银行', '15194619', '', '借记卡', '19', '9', '621356024', '0');
INSERT INTO `authbank_cardbin` VALUES ('1964', '临邑中银富登村镇银行', '15194689', '', '借记卡', '19', '9', '621356006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1965', '沂水中银富登村镇银行', '15194737', '', '借记卡', '19', '9', '621356004', '0');
INSERT INTO `authbank_cardbin` VALUES ('1966', '曹县中银富登村镇银行', '15194752', '', '借记卡', '19', '9', '621356003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1967', '单县中银富登村镇银行', '15194755', '', '借记卡', '19', '9', '621356017', '0');
INSERT INTO `authbank_cardbin` VALUES ('1968', '五莲中银富登村镇银行', '15194771', '', '借记卡', '19', '9', '621356025', '0');
INSERT INTO `authbank_cardbin` VALUES ('1969', '谷城中银富登村镇银行', '15195284', '', '借记卡', '19', '9', '621356007', '0');
INSERT INTO `authbank_cardbin` VALUES ('1970', '老河口中银富登村镇银行', '15195287', '', '中银富登村镇银行借记卡', '19', '9', '621356009', '0');
INSERT INTO `authbank_cardbin` VALUES ('1971', '枣阳中银富登村镇银行', '15195288', '', '借记卡', '19', '9', '621356008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1972', '京山中银富登村镇银行', '15195321', '', '京山富登借记卡', '19', '9', '621356002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1973', '蕲春中银富登村镇银行', '15195338', '', '中银富登借记卡', '19', '9', '621356001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1974', '潜江中银富登村镇银行', '15195375', '', '中银富登村镇银行借记卡', '19', '9', '621356010', '0');
INSERT INTO `authbank_cardbin` VALUES ('1975', '松滋中银富登村镇银行', '15195377', '', '借记卡', '19', '9', '621356012', '0');
INSERT INTO `authbank_cardbin` VALUES ('1976', '监利中银富登村镇银行', '15195379', '', '借记卡', '19', '9', '621356011', '0');
INSERT INTO `authbank_cardbin` VALUES ('1977', '重庆长寿中银富登村镇银行', '15196900', '', '借记卡', '19', '9', '621356028', '0');
INSERT INTO `authbank_cardbin` VALUES ('1978', '乾县中银富登村镇银行', '15197954', '', '借记卡', '19', '9', '621356031', '0');
INSERT INTO `authbank_cardbin` VALUES ('1979', '北京顺义银座村镇银行', '15201000', '', '大唐卡', '16', '9', '621347002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1980', '浙江景宁银座村镇银行', '15203438', '', '大唐卡', '16', '9', '621347008', '0');
INSERT INTO `authbank_cardbin` VALUES ('1981', '浙江三门银座村镇银行', '15203457', '', '大唐卡', '16', '9', '621347005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1982', '江西赣州银座村镇银行', '15204280', '', '大唐卡', '16', '9', '621347003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1983', '深圳福田银座村镇银行', '15205840', '', '大唐卡', '16', '9', '621347001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1984', '重庆渝北银座村镇银行', '15206900', '', '大唐卡', '16', '9', '621347006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1985', '重庆黔江银座村镇银行', '15206925', '', '大唐卡', '16', '9', '621347007', '0');
INSERT INTO `authbank_cardbin` VALUES ('1986', '北京怀柔融兴村镇银行', '15211000', '', '融兴普惠卡', '19', '9', '621350010', '0');
INSERT INTO `authbank_cardbin` VALUES ('1987', '河间融惠村镇银行', '15211443', '', '融兴普惠卡', '19', '9', '621350020', '0');
INSERT INTO `authbank_cardbin` VALUES ('1988', '榆树融兴村镇银行', '15212411', '', '融兴普惠卡', '19', '9', '621350431', '0');
INSERT INTO `authbank_cardbin` VALUES ('1989', '巴彦融兴村镇银行', '15212625', '', '融兴普惠卡', '19', '9', '621350451', '0');
INSERT INTO `authbank_cardbin` VALUES ('1990', '延寿融兴村镇银行', '15212629', '', '融兴普惠卡', '19', '9', '621350001', '0');
INSERT INTO `authbank_cardbin` VALUES ('1991', '拜泉融兴村镇银行', '15212652', '', '融兴普惠卡', '19', '9', '621350013', '0');
INSERT INTO `authbank_cardbin` VALUES ('1992', '桦川融兴村镇银行', '15212723', '', '融兴普惠卡', '19', '9', '621350005', '0');
INSERT INTO `authbank_cardbin` VALUES ('1993', '江苏如东融兴村镇银行', '15213063', '', '融兴普惠卡', '19', '9', '621350009', '0');
INSERT INTO `authbank_cardbin` VALUES ('1994', '安义融兴村镇银行', '15214213', '', '融兴普惠卡', '19', '9', '621350003', '0');
INSERT INTO `authbank_cardbin` VALUES ('1995', '乐平融兴村镇银行', '15214221', '', '', '19', '9', '621350002', '0');
INSERT INTO `authbank_cardbin` VALUES ('1996', '偃师融兴村镇银行', '15214931', '', '融兴普惠卡', '19', '9', '621350015', '0');
INSERT INTO `authbank_cardbin` VALUES ('1997', '新安融兴村镇银行', '15214933', '', '融兴普惠卡', '19', '9', '621350004', '0');
INSERT INTO `authbank_cardbin` VALUES ('1998', '应城融兴村镇银行', '15215352', '', '融兴普惠卡', '19', '9', '621350006', '0');
INSERT INTO `authbank_cardbin` VALUES ('1999', '洪湖融兴村镇银行', '15215373', '', '融兴普惠卡', '19', '9', '621350011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2000', '株洲县融兴村镇银行', '15215521', '', '融兴普惠卡', '19', '9', '621350016', '0');
INSERT INTO `authbank_cardbin` VALUES ('2001', '耒阳融兴村镇银行', '15215547', '', '融兴普惠卡', '19', '9', '621350007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2002', '深圳宝安融兴村镇银行', '15215840', '', '融兴普惠卡', '19', '9', '621350755', '0');
INSERT INTO `authbank_cardbin` VALUES ('2003', '海南保亭融兴村镇银行', '15216437', '', '融兴普惠卡', '19', '9', '621350017', '0');
INSERT INTO `authbank_cardbin` VALUES ('2004', '遂宁安居融兴村镇银行', '15216620', '', '融兴普惠卡', '19', '9', '621350014', '0');
INSERT INTO `authbank_cardbin` VALUES ('2005', '重庆沙坪坝融兴村镇银行', '15216900', '', '融兴普惠卡', '19', '9', '621350019', '0');
INSERT INTO `authbank_cardbin` VALUES ('2006', '重庆大渡口融兴村镇银行', '15216922', '', '融兴普惠卡', '19', '9', '621350012', '0');
INSERT INTO `authbank_cardbin` VALUES ('2007', '重庆市武隆融兴村镇银行', '15216925', '', '融兴普惠卡', '19', '9', '621350008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2008', '重庆市酋阳融兴村镇银行', '15216935', '', '融兴普惠卡', '19', '9', '621350018', '0');
INSERT INTO `authbank_cardbin` VALUES ('2009', '会宁会师村镇银行', '15218242', '', '会师普惠卡', '19', '9', '621350943', '0');
INSERT INTO `authbank_cardbin` VALUES ('2010', '南阳村镇银行', '15265130', '', '玉都卡', '18', '6', '621392', '0');
INSERT INTO `authbank_cardbin` VALUES ('2011', '宁晋民生村镇银行', '15271329', '', '宁晋民生村镇银行借记卡', '16', '9', '621399017', '0');
INSERT INTO `authbank_cardbin` VALUES ('2012', '梅河口民生村镇银行', '15272454', '', '梅河口民生村镇银行借记卡', '16', '9', '621399008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2013', '上海松江民生村镇银行', '15272900', '', '借记卡', '16', '9', '621399001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2014', '嘉定民生村镇银行', '15272929', '', '借记卡', '16', '9', '621399012', '0');
INSERT INTO `authbank_cardbin` VALUES ('2015', '太仓民生村镇银行', '15273051', '', '--', '16', '9', '621399016', '0');
INSERT INTO `authbank_cardbin` VALUES ('2016', '阜宁民生村镇银行', '15273113', '', '--', '16', '9', '621399015', '0');
INSERT INTO `authbank_cardbin` VALUES ('2017', '天台民生村镇银行', '15273456', '', '天台民生村镇银行借记卡', '16', '9', '621399025', '0');
INSERT INTO `authbank_cardbin` VALUES ('2018', '天长民生村镇银行', '15273752', '', '天长民生村镇银行借记卡', '16', '9', '621399026', '0');
INSERT INTO `authbank_cardbin` VALUES ('2019', '宁国民生村镇银行', '15273774', '', '宁国民生村镇银行借记卡', '16', '9', '621399023', '0');
INSERT INTO `authbank_cardbin` VALUES ('2020', '池州贵池民生村镇银行', '15273790', '', '--', '16', '9', '621399024', '0');
INSERT INTO `authbank_cardbin` VALUES ('2021', '厦门翔安民生村镇银行', '15273930', '', '--', '16', '9', '621399028', '0');
INSERT INTO `authbank_cardbin` VALUES ('2022', '安溪民生村镇银行', '15273974', '', '借记卡', '16', '9', '621399002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2023', '漳浦民生村镇银行', '15273993', '', '漳浦民生村镇银行借记卡', '16', '9', '621399018', '0');
INSERT INTO `authbank_cardbin` VALUES ('2024', '蓬莱民生村镇银行', '15274561', '', '--', '16', '9', '621399014', '0');
INSERT INTO `authbank_cardbin` VALUES ('2025', '长垣民生村镇银行', '15274986', '', '长垣民生村镇银行借记卡', '16', '9', '621399010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2026', '江夏民生村镇银行', '15275210', '', '借记卡', '16', '9', '621399009', '0');
INSERT INTO `authbank_cardbin` VALUES ('2027', '宜都民生村镇银行', '15275251', '', '宜都民生村镇银行借记卡', '19', '9', '621399011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2028', '宜都民生村镇银行', '15275251', '', '宜都民生村镇银行借记卡', '16', '9', '621399011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2029', '钟祥民生村镇银行', '15275323', '', '借记卡', '16', '9', '621399013', '0');
INSERT INTO `authbank_cardbin` VALUES ('2030', '彭州民生村镇银行', '15276516', '', '--', '16', '9', '621399003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2031', '资阳民生村镇银行', '15276880', '', '--', '16', '9', '621399007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2032', '綦江民生村镇银行', '15276900', '', '綦江民生村镇银行借记卡', '16', '9', '621399005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2033', '潼南民生村镇银行', '15276914', '', '潼南民生村镇银行借记卡', '16', '9', '621399006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2034', '普洱民生村镇银行', '15277470', '', '--', '16', '9', '621399021', '0');
INSERT INTO `authbank_cardbin` VALUES ('2035', '景洪民生村镇银行', '15277491', '', '--', '16', '9', '621399019', '0');
INSERT INTO `authbank_cardbin` VALUES ('2036', '腾冲民生村镇银行', '15277533', '', '腾冲民生村镇银行', '16', '9', '621399027', '0');
INSERT INTO `authbank_cardbin` VALUES ('2037', '志丹民生村镇银行', '15278046', '', '', '16', '9', '621399020', '0');
INSERT INTO `authbank_cardbin` VALUES ('2038', '榆林榆阳民生村镇银行', '15278060', '', '--', '16', '9', '621399022', '0');
INSERT INTO `authbank_cardbin` VALUES ('2039', '浙江萧山湖商村镇银行', '15283310', '', '湖商卡', '19', '9', '621365006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2040', '浙江建德湖商村镇银行', '15283315', '', '湖商卡', '19', '9', '621365001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2041', '浙江德清湖商村镇银行', '15283361', '', '湖商卡', '19', '9', '621365005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2042', '安徽粤西湖商村镇银行', '15283688', '', '湖商卡', '19', '9', '621365004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2043', '安徽蒙城湖商村镇银行', '15283812', '', '湖商卡', '19', '9', '621365003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2044', '安徽利辛湖商村镇银行', '15283813', '', '湖商卡', '19', '9', '621365002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2045', '晋中市榆次融信村镇银行', '15301750', '', '魏榆卡', '19', '6', '621481', '0');
INSERT INTO `authbank_cardbin` VALUES ('2046', '梅县客家村镇银行', '15315960', '', '围龙借记卡', '19', '9', '621393001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2047', '宝生村镇银行', '15335840', '', '宝生村镇银行一卡通', '19', '9', '621623001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2048', '江苏大丰江南村镇银行', '15343116', '', '江南卡', '19', '9', '621397001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2049', '江苏东台稠州村镇银行', '15353117', '', '义卡借记卡', '19', '9', '621627008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2050', '吉安稠州村镇银行', '15354353', '', '', '19', '9', '621627001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2051', '广州花都稠州村镇银行', '15355810', '', '义卡借记卡', '19', '9', '621627007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2052', '重庆北碚稠州村镇银行', '15356900', '', '义卡', '19', '9', '621627003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2053', '忠县稠州村镇银行', '15356926', '', '义卡', '19', '9', '621627006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2054', '云南安宁稠州村镇银行', '15357313', '', '义卡', '19', '9', '621627010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2055', '象山国民村镇银行', '15363321', '', '', '19', '9', '621635101', '0');
INSERT INTO `authbank_cardbin` VALUES ('2056', '宁波市鄞州国民村镇银行', '15363323', '', '鄞州国民村镇银行借记IC卡', '19', '9', '621635114', '0');
INSERT INTO `authbank_cardbin` VALUES ('2057', '南宁江南国民村镇银行', '15366110', '', '借记卡', '19', '9', '621635003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2058', '南宁江南国民村镇银行', '15366110', '', '蜜蜂借记IC卡', '19', '9', '621635103', '0');
INSERT INTO `authbank_cardbin` VALUES ('2059', '桂林国民村镇银行', '15366170', '', '蜜蜂卡', '19', '9', '621635004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2060', '桂林国民村镇银行', '15366170', '', '桂林国民村镇银行蜜蜂IC借记卡', '19', '9', '621635104', '0');
INSERT INTO `authbank_cardbin` VALUES ('2061', '银海国民村镇银行', '15366230', '', '', '19', '9', '621635112', '0');
INSERT INTO `authbank_cardbin` VALUES ('2062', '合浦国民村镇银行', '15366231', '', '', '19', '9', '621635109', '0');
INSERT INTO `authbank_cardbin` VALUES ('2063', '平果国民村镇银行', '15366264', '', '平果国民村镇银行蜜蜂借记卡', '19', '9', '621635111', '0');
INSERT INTO `authbank_cardbin` VALUES ('2064', '钦州市钦南国民村镇银行', '15366310', '', '钦南国民村镇银行蜜蜂借记卡', '19', '9', '621635013', '0');
INSERT INTO `authbank_cardbin` VALUES ('2065', '钦州市钦南国民村镇银行', '15366310', '', '钦南国民村镇银行蜜蜂IC借记卡', '19', '9', '621635113', '0');
INSERT INTO `authbank_cardbin` VALUES ('2066', '防城港防城国民村镇银行', '15366320', '', '蜜蜂借记卡', '19', '9', '621635010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2067', '东兴国民村镇银行', '15366322', '', '——', '19', '9', '621635005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2068', '东兴国民村镇银行', '15366322', '', '', '19', '9', '621635105', '0');
INSERT INTO `authbank_cardbin` VALUES ('2069', '哈密红星国民村镇银行', '15368841', '', '哈密红星国民村镇银行IC复合卡', '19', '9', '621635115', '0');
INSERT INTO `authbank_cardbin` VALUES ('2070', '昌吉国民村镇银行', '15368851', '', '', '19', '9', '621635108', '0');
INSERT INTO `authbank_cardbin` VALUES ('2071', '石河子国民村镇银行', '15369028', '', '--', '19', '9', '621635106', '0');
INSERT INTO `authbank_cardbin` VALUES ('2072', '五家渠国民村镇银行', '15369043', '', '五家渠国民村镇银行借记IC卡', '19', '9', '621635102', '0');
INSERT INTO `authbank_cardbin` VALUES ('2073', '文昌国民村镇银行', '15386423', '', '赀业卡', '19', '9', '621650002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2074', '琼海国民村镇银行', '15386424', '', '椰卡', '19', '9', '621650001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2075', '北京门头沟珠江村镇银行', '15401000', '', '珠江太阳卡', '18', '8', '62163113', '0');
INSERT INTO `authbank_cardbin` VALUES ('2076', '大连保税区珠江村镇银行', '15402220', '', '珠江太阳卡', '18', '8', '62163103', '0');
INSERT INTO `authbank_cardbin` VALUES ('2077', '启东珠江村镇银行', '15403066', '', '启东珠江卡', '18', '8', '62163119', '0');
INSERT INTO `authbank_cardbin` VALUES ('2078', '盱眙珠江村镇银行', '15403088', '', '盱眙珠江卡', '18', '8', '62163120', '0');
INSERT INTO `authbank_cardbin` VALUES ('2079', '青岛城阳珠江村镇银行', '15404520', '', '珠江太阳卡', '18', '8', '62163117', '0');
INSERT INTO `authbank_cardbin` VALUES ('2080', '福山珠江村镇银行', '15404560', '', '珠江太阳卡', '18', '8', '62163114', '0');
INSERT INTO `authbank_cardbin` VALUES ('2081', '海阳珠江村镇银行', '15404564', '', '珠江太阳卡', '18', '8', '62163116', '0');
INSERT INTO `authbank_cardbin` VALUES ('2082', '莱州珠江村镇银行', '15404569', '', '珠江太阳卡', '18', '8', '62163115', '0');
INSERT INTO `authbank_cardbin` VALUES ('2083', '莱芜珠江村镇银行', '15404790', '', '珠江太阳卡', '18', '8', '62163104', '0');
INSERT INTO `authbank_cardbin` VALUES ('2084', '安阳珠江村镇银行', '15404960', '', '珠江太阳卡', '18', '8', '62163118', '0');
INSERT INTO `authbank_cardbin` VALUES ('2085', '辉县珠江村镇银行', '15404988', '', '珠江太阳卡', '18', '8', '62163108', '0');
INSERT INTO `authbank_cardbin` VALUES ('2086', '信阳珠江村镇银行', '15405150', '', '珠江太阳卡', '18', '8', '62163107', '0');
INSERT INTO `authbank_cardbin` VALUES ('2087', '常宁珠江村镇银行', '15405545', '', '珠江太阳卡', '18', '8', '62163121', '0');
INSERT INTO `authbank_cardbin` VALUES ('2088', '三水珠江村镇银行', '15405881', '', '珠江太阳卡', '18', '6', '621310', '0');
INSERT INTO `authbank_cardbin` VALUES ('2089', '鹤山珠江村镇银行', '15405895', '', '珠江太阳卡', '18', '8', '62163101', '0');
INSERT INTO `authbank_cardbin` VALUES ('2090', '中山东凤珠江村镇银行', '15406030', '', '珠江太阳卡', '18', '8', '62163102', '0');
INSERT INTO `authbank_cardbin` VALUES ('2091', '新津珠江村镇银行', '15406522', '', '珠江太阳卡', '18', '8', '62163109', '0');
INSERT INTO `authbank_cardbin` VALUES ('2092', '广汉珠江村镇银行', '15406584', '', '', '18', '8', '62163110', '0');
INSERT INTO `authbank_cardbin` VALUES ('2093', '彭山珠江村镇银行', '15406672', '', '珠江太阳卡', '18', '8', '62163111', '0');
INSERT INTO `authbank_cardbin` VALUES ('2094', '唐县汇泽村镇银行', '15421358', '', '汇泽借记卡', '19', '9', '621637009', '0');
INSERT INTO `authbank_cardbin` VALUES ('2095', '古交汇泽村镇银行', '15421614', '', '汇泽借记卡', '19', '9', '621637008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2096', '兴县汇泽村镇银行', '15421735', '', '汇泽借记卡', '19', '9', '621637006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2097', '柳林汇泽村镇银行', '15421737', '', '汇泽借记卡', '19', '9', '621637007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2098', '正蓝旗汇泽村镇银行', '15422022', '', '汇泽借记卡', '19', '9', '621637005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2099', '兴和汇泽村镇银行', '15422038', '', '汇泽借记卡', '19', '9', '621637004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2100', '鄂尔多斯市康巴什村镇银行', '15422050', '', '汇泽借记卡', '19', '9', '621637001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2101', '罕台村镇银行', '15422051', '', '汇泽借记卡', '19', '9', '621637002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2102', '鄂托克旗汇泽村镇银行', '15422055', '', '汇泽借记卡', '19', '9', '621637003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2103', '安徽肥西石银村镇银行', '15433613', '', '借记卡', '19', '9', '621653002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2104', '青岛莱西元泰村镇银行', '15434523', '', '--', '19', '9', '621653003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2105', '重庆南川石银村镇银行', '15436900', '', '麒麟借记卡', '19', '9', '621653004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2106', '重庆江津石银村镇银行', '15436901', '', '麒麟借记卡', '19', '9', '621653005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2107', '银川掌政石银村镇银行', '15438710', '', '麒麟借记卡', '19', '9', '621653007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2108', '大武口石银村镇银行', '15438720', '', '麒麟借记卡', '19', '9', '621653006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2109', '吴忠市滨河村镇银行', '15438730', '', '麒麟借记卡', '19', '9', '621653001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2110', '广元贵商村镇银行', '15446610', '', '利卡', '19', '8', '62308299', '0');
INSERT INTO `authbank_cardbin` VALUES ('2111', '佛山高明顺银村镇银行', '15455880', '', '恒通卡', '19', '9', '621628660', '0');
INSERT INTO `authbank_cardbin` VALUES ('2112', '青岛胶南海汇村镇银行', '15464520', '', '海汇卡', '16', '9', '621316001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2113', '惠州仲恺东盈村镇银行', '15475950', '', '--', '19', '8', '62319801', '0');
INSERT INTO `authbank_cardbin` VALUES ('2114', '东莞大朗东盈村镇银行', '15476020', '', '东盈卡', '19', '8', '62319806', '0');
INSERT INTO `authbank_cardbin` VALUES ('2115', '云浮新兴东盈民生村镇银行', '15476061', '', '东盈卡', '19', '8', '62319802', '0');
INSERT INTO `authbank_cardbin` VALUES ('2116', '贺州八步东盈村镇银行', '15476340', '', '东盈卡', '19', '8', '62319803', '0');
INSERT INTO `authbank_cardbin` VALUES ('2117', '宜兴阳羡村镇银行', '15483023', '', '阳羡卡', '16', '9', '621355002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2118', '昆山鹿城村镇银行', '15483052', '', '鹿城卡', '16', '9', '621355001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2119', '南昌大丰村镇银行', '15494210', '', '金丰卡', '19', '9', '621675001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2120', '长子县融汇村镇银行', '15501668', '', '融汇卡', '19', '8', '62309701', '0');
INSERT INTO `authbank_cardbin` VALUES ('2121', '天津武清村镇银行', '15511100', '', '京津之翼卡', '19', '9', '621656002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2122', '东营莱商村镇银行', '15514550', '', '绿洲卡', '16', '6', '621396', '0');
INSERT INTO `authbank_cardbin` VALUES ('2123', '河南方城凤裕村镇银行', '15515134', '', '金裕卡', '19', '9', '621656001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2124', '沧县吉银村镇银行', '15521430', '', '长白山卡', '19', '9', '621659002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2125', '永清吉银村镇银行', '15521463', '', '长白山卡', '19', '9', '621659001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2126', '长春双阳吉银村镇银行', '15522410', '', '', '19', '9', '621659006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2127', '江都吉银村镇银行', '15523120', '', '长白山卡', '19', '9', '621398001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2128', '湖北咸安武农商村镇银行', '15535360', '', '汉卡', '19', '9', '621676001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2129', '湖北赤壁武弄商村镇银行', '15535367', '', '汉卡', '19', '9', '621676002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2130', '广州增城长江村镇银行', '15535813', '', '汉卡', '19', '9', '621676003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2131', '张家港渝农商村镇银行', '15563056', '', '江渝卡', '16', '9', '621680002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2132', '福建平潭渝农商村镇银行', '15563918', '', '江渝借记卡', '16', '9', '621680007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2133', '福建沙县渝农商村镇银行', '15563956', '', '江渝卡', '16', '9', '621680009', '0');
INSERT INTO `authbank_cardbin` VALUES ('2134', '福建福安渝农商村镇银行', '15564034', '', '借记卡', '16', '9', '621680010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2135', '广西鹿寨渝农商村镇银行', '15566152', '', '江渝卡', '16', '9', '621680005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2136', '大竹渝农商村镇银行', '15566761', '', '江渝卡', '16', '9', '621680003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2137', '云南大理渝农商村镇银行', '15567511', '', '江渝卡', '16', '9', '621680004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2138', '云南祥云渝农商村镇银行', '15567513', '', '江渝卡', '16', '9', '621680006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2139', '云南鹤庆渝农商村镇银行', '15567523', '', '江渝卡', '16', '9', '621680008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2140', '云南香格里拉渝农商村镇银行', '15567571', '', '江渝卡', '16', '9', '621680011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2141', '沈阳于洪永安村镇银行', '15572210', '', '永安卡', '18', '9', '621681001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2142', '北京房山沪农商村镇银行', '15581000', '', '借记卡', '19', '9', '621682002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2143', '济南长清沪农商村镇银行', '15584510', '', '借记卡', '19', '9', '621682101', '0');
INSERT INTO `authbank_cardbin` VALUES ('2144', '济南槐荫沪农商村镇银行', '15584513', '', '借记卡', '19', '9', '621682102', '0');
INSERT INTO `authbank_cardbin` VALUES ('2145', '泰安沪农商村镇银行', '15584630', '', '借记卡', '19', '9', '621682106', '0');
INSERT INTO `authbank_cardbin` VALUES ('2146', '宁阳沪农商村镇银行', '15584631', '', '借记卡', '19', '9', '621682103', '0');
INSERT INTO `authbank_cardbin` VALUES ('2147', '东平沪农商村镇银行', '15584633', '', '借记卡', '19', '9', '621682105', '0');
INSERT INTO `authbank_cardbin` VALUES ('2148', '聊城沪农商村镇银行', '15584710', '', '借记卡', '19', '9', '621682110', '0');
INSERT INTO `authbank_cardbin` VALUES ('2149', '临清沪农商村镇银行', '15584712', '', '借记卡', '19', '9', '621682111', '0');
INSERT INTO `authbank_cardbin` VALUES ('2150', '阳谷沪农商村镇银行', '15584713', '', '借记卡', '19', '9', '621682109', '0');
INSERT INTO `authbank_cardbin` VALUES ('2151', '茌平沪农商村镇银行', '15584715', '', '借记卡', '19', '9', '621682108', '0');
INSERT INTO `authbank_cardbin` VALUES ('2152', '日照沪农商村镇银行', '15584770', '', '借记卡', '19', '9', '621682107', '0');
INSERT INTO `authbank_cardbin` VALUES ('2153', '长沙星沙沪农商村镇银行', '15585511', '', '借记卡', '19', '9', '621682202', '0');
INSERT INTO `authbank_cardbin` VALUES ('2154', '宁乡沪农商行村镇银行', '15585514', '', '借记卡', '19', '9', '621682201', '0');
INSERT INTO `authbank_cardbin` VALUES ('2155', '醴陵沪农商村镇银行', '15585525', '', '借记卡', '19', '9', '621682203', '0');
INSERT INTO `authbank_cardbin` VALUES ('2156', '衡阳沪农商村镇银行', '15585541', '', '借记卡', '19', '9', '621682205', '0');
INSERT INTO `authbank_cardbin` VALUES ('2157', '澧县沪农商村镇银行', '15585583', '', '借记卡', '19', '9', '621682209', '0');
INSERT INTO `authbank_cardbin` VALUES ('2158', '临澧沪农商村镇银行', '15585584', '', '借记卡', '19', '9', '621682208', '0');
INSERT INTO `authbank_cardbin` VALUES ('2159', '石门沪农商村镇银行', '15585586', '', '借记卡', '19', '9', '621682210', '0');
INSERT INTO `authbank_cardbin` VALUES ('2160', '慈利沪农商村镇银行', '15585591', '', '借记卡', '19', '9', '621682213', '0');
INSERT INTO `authbank_cardbin` VALUES ('2161', '涟源沪农商村镇银行', '15585623', '', '借记卡', '19', '9', '621682211', '0');
INSERT INTO `authbank_cardbin` VALUES ('2162', '双峰沪农商村镇银行', '15585624', '', '借记卡', '19', '9', '621682212', '0');
INSERT INTO `authbank_cardbin` VALUES ('2163', '桂阳沪农商村镇银行', '15585634', '', '借记卡', '19', '9', '621682207', '0');
INSERT INTO `authbank_cardbin` VALUES ('2164', '永兴沪农商村镇银行', '15585635', '', '借记卡', '19', '9', '621682206', '0');
INSERT INTO `authbank_cardbin` VALUES ('2165', '深圳光明沪农商村镇银行', '15585840', '', '借记卡', '19', '9', '621682003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2166', '阿拉沪农商村镇银行', '15587310', '', '借记卡', '19', '9', '621682301', '0');
INSERT INTO `authbank_cardbin` VALUES ('2167', '嵩明沪农商村镇银行', '15587317', '', '借记卡', '19', '9', '621682302', '0');
INSERT INTO `authbank_cardbin` VALUES ('2168', '个旧沪农商村镇银行', '15587431', '', '借记卡', '19', '9', '621682305', '0');
INSERT INTO `authbank_cardbin` VALUES ('2169', '开远沪农商村镇银行', '15587432', '', '借记卡', '19', '9', '621682307', '0');
INSERT INTO `authbank_cardbin` VALUES ('2170', '蒙自沪农商村镇银行', '15587433', '', '借记卡', '19', '9', '621682306', '0');
INSERT INTO `authbank_cardbin` VALUES ('2171', '建水沪农商村镇银行', '15587435', '', '借记卡', '19', '9', '621682309', '0');
INSERT INTO `authbank_cardbin` VALUES ('2172', '弥勒沪农商村镇银行', '15587437', '', '借记卡', '19', '9', '621682308', '0');
INSERT INTO `authbank_cardbin` VALUES ('2173', '保山隆阳沪农商村镇银行', '15587530', '', '借记卡', '19', '9', '621682310', '0');
INSERT INTO `authbank_cardbin` VALUES ('2174', '瑞丽沪农商村镇银行', '15587546', '', '借记卡', '19', '9', '621682303', '0');
INSERT INTO `authbank_cardbin` VALUES ('2175', '临沧临翔沪农商村镇银行', '15587580', '', '借记卡', '19', '9', '621682311', '0');
INSERT INTO `authbank_cardbin` VALUES ('2176', '宝丰豫丰村镇银行', '15604951', '', '豫丰卡', '18', '9', '621687913', '0');
INSERT INTO `authbank_cardbin` VALUES ('2177', '灵宝融丰村镇银行', '15605053', '', '--', '19', '9', '621687001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2178', '中牟郑银村镇银行', '15624912', '', '福鼎卡', '19', '8', '62169502', '0');
INSERT INTO `authbank_cardbin` VALUES ('2179', '新密郑银村镇银行', '15624916', '', '', '19', '8', '62169501', '0');
INSERT INTO `authbank_cardbin` VALUES ('2180', '鄢陵郑银村镇银行', '15625033', '', '', '19', '8', '62169503', '0');
INSERT INTO `authbank_cardbin` VALUES ('2181', '安徽五河永泰村镇银行', '15633632', '', '借记卡', '19', '8', '62352801', '0');
INSERT INTO `authbank_cardbin` VALUES ('2182', '天津华明村镇银行', '15641100', '', '借记卡', '18', '9', '621697813', '0');
INSERT INTO `authbank_cardbin` VALUES ('2183', '任丘泰寿村镇银行', '15641442', '', '同心卡', '18', '9', '621697793', '0');
INSERT INTO `authbank_cardbin` VALUES ('2184', '芜湖泰寿村镇银行', '15643621', '', '同心卡', '18', '9', '621697873', '0');
INSERT INTO `authbank_cardbin` VALUES ('2185', '开封新东方村镇银行', '15654924', '', '--', '19', '8', '62311702', '0');
INSERT INTO `authbank_cardbin` VALUES ('2186', '长葛轩辕村镇银行', '15655031', '', '', '18', '8', '62311701', '0');
INSERT INTO `authbank_cardbin` VALUES ('2187', '从化柳银村镇银行', '15695812', '', '广州从化柳银村镇银行龙城卡', '18', '9', '621689007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2188', '柳江柳银村镇银行', '15696141', '', '柳江柳银村镇银行龙城卡', '18', '9', '621689001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2189', '融水柳银村镇银行', '15696158', '', '融水柳银村镇银行龙城卡', '18', '9', '621689002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2190', '北流柳银村镇银行', '15696246', '', '广西北流柳银村镇银行龙城卡', '18', '9', '621689004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2191', '陆川柳银村镇银行', '15696247', '', '借记卡', '18', '9', '621689005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2192', '博白柳银村镇银行', '15696248', '', '龙城卡', '18', '9', '621689006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2193', '兴业柳银村镇银行', '15696249', '', '龙城卡', '18', '9', '621689003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2194', '浙江兰溪越商村镇银行', '15713386', '', '兰江卡', '18', '9', '621387973', '0');
INSERT INTO `authbank_cardbin` VALUES ('2195', '北京昌平兆丰村镇银行', '15731000', '', '', '19', '9', '621382019', '0');
INSERT INTO `authbank_cardbin` VALUES ('2196', '天津津南村镇银行', '15731100', '', '', '19', '9', '621382018', '0');
INSERT INTO `authbank_cardbin` VALUES ('2197', '清徐惠民村镇银行', '15731611', '', '', '19', '9', '621382020', '0');
INSERT INTO `authbank_cardbin` VALUES ('2198', '固阳包商惠农村镇银行', '15731922', '', '', '19', '9', '621382001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2199', '宁城包商村镇银行', '15731948', '', '', '19', '9', '621382002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2200', '莫力达瓦包商村镇银行', '15731966', '', '借记卡', '19', '9', '621382006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2201', '鄂温克旗包商村镇银行', '15731971', '', '借记卡', '19', '9', '621382005', '0');
INSERT INTO `authbank_cardbin` VALUES ('2202', '科尔沁包商村镇银行', '15731982', '', '', '19', '9', '621382010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2203', '西乌珠穆沁包商惠丰村镇银行', '15732017', '', '借记卡', '19', '9', '621382009', '0');
INSERT INTO `authbank_cardbin` VALUES ('2204', '集宁包商村镇银行', '15732030', '', '', '19', '9', '621382007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2205', '化德包商村镇银行', '15732036', '', '借记卡', '19', '9', '621382008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2206', '准格尔旗包商村镇银行', '15732053', '', '', '19', '9', '621382003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2207', '乌审旗包商村镇银行', '15732057', '', '', '19', '9', '621382004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2208', '大连金州联丰村镇银行', '15732220', '', '', '19', '9', '621382025', '0');
INSERT INTO `authbank_cardbin` VALUES ('2209', '九台龙嘉村镇银行', '15732415', '', '--', '19', '9', '621382013', '0');
INSERT INTO `authbank_cardbin` VALUES ('2210', '江苏南通如皋包商村镇银行', '15733062', '', '', '19', '9', '621382017', '0');
INSERT INTO `authbank_cardbin` VALUES ('2211', '仪征包商村镇银行', '15733129', '', '', '19', '9', '621382021', '0');
INSERT INTO `authbank_cardbin` VALUES ('2212', '鄄城包商村镇银行', '15734759', '', '', '19', '9', '621382023', '0');
INSERT INTO `authbank_cardbin` VALUES ('2213', '漯河市郾城包商村镇银行', '15735040', '', '--', '19', '9', '621382015', '0');
INSERT INTO `authbank_cardbin` VALUES ('2214', '掇刀包商村镇银行', '15735320', '', '', '19', '9', '621382016', '0');
INSERT INTO `authbank_cardbin` VALUES ('2215', '武冈包商村镇银行', '15735556', '', '借记卡', '19', '9', '621382012', '0');
INSERT INTO `authbank_cardbin` VALUES ('2216', '新都桂城村镇银行', '15736510', '', '', '19', '9', '621382014', '0');
INSERT INTO `authbank_cardbin` VALUES ('2217', '广元包商贵民村镇银行', '15736610', '', '', '19', '9', '621382024', '0');
INSERT INTO `authbank_cardbin` VALUES ('2218', '贵阳花溪建设村镇银行', '15737010', '', '借记卡', '19', '9', '621382027', '0');
INSERT INTO `authbank_cardbin` VALUES ('2219', '息烽包商黔隆村镇银行', '15737012', '', '', '19', '9', '621382011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2220', '毕节发展村镇银行', '15737090', '', '', '19', '9', '621382022', '0');
INSERT INTO `authbank_cardbin` VALUES ('2221', '宁夏贺兰回商村镇银行', '15738712', '', '', '19', '9', '621382026', '0');
INSERT INTO `authbank_cardbin` VALUES ('2222', '辽宁大石桥隆丰村镇银行', '15752281', '', '隆丰卡', '19', '9', '621383001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2223', '辽宁辰州汇通村镇银行股份有限公司', '15752282', '', '兰卡', '19', '9', '621383002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2224', '内江兴隆村镇银行', '15766630', '', '--', '18', '9', '621385663', '0');
INSERT INTO `authbank_cardbin` VALUES ('2225', '枞阳泰业村镇银行', '15773683', '', '枞阳泰业村镇银行泰业卡', '19', '8', '62316904', '0');
INSERT INTO `authbank_cardbin` VALUES ('2226', '东源泰业村镇银行', '15775985', '', '东源泰业村镇银行泰业卡', '19', '8', '62316905', '0');
INSERT INTO `authbank_cardbin` VALUES ('2227', '东莞长安村镇银行', '15776020', '', '长银卡', '19', '8', '62316902', '0');
INSERT INTO `authbank_cardbin` VALUES ('2228', '灵山泰业村镇银行', '15776314', '', '灵山泰业村镇银行泰业卡', '19', '8', '62316903', '0');
INSERT INTO `authbank_cardbin` VALUES ('2229', '开县泰业村镇银行', '15776927', '', '开县泰业村镇银行泰业卡', '19', '8', '62316901', '0');
INSERT INTO `authbank_cardbin` VALUES ('2230', '东莞厚街华业村镇银行', '15786020', '', '易事通卡', '19', '8', '62316906', '0');
INSERT INTO `authbank_cardbin` VALUES ('2231', '长春高新惠民村镇银行', '15802410', '', '福娃卡', '18', '9', '621278293', '0');
INSERT INTO `authbank_cardbin` VALUES ('2232', '通城惠民村镇银行', '15805364', '', '--', '18', '9', '621278333', '0');
INSERT INTO `authbank_cardbin` VALUES ('2233', '山东莒南村镇银行', '15834735', '', '山东莒南村镇银行富民卡', '19', '9', '621368001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2234', '武陟射阳村镇银行', '15845013', '', '金鹤卡', '19', '9', '621386001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2235', '河南沁阳江南村镇银行', '15845014', '', '借记卡', '19', '9', '621386003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2236', '孟州射阳村镇银行', '15845016', '', '', '19', '9', '621386004', '0');
INSERT INTO `authbank_cardbin` VALUES ('2237', '锦州太和益民村镇银行', '15852270', '', '7777卡', '17', '9', '621699002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2238', '锦州太和益民村镇银行', '15852270', '', '7777卡', '19', '9', '621699002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2239', '山东临朐聚丰村镇银行', '15884583', '', '聚丰卡', '18', '9', '623678353', '0');
INSERT INTO `authbank_cardbin` VALUES ('2240', '德庆华润村镇银行', '15915936', '', '德庆华润村镇银行借记金卡', '19', '9', '623608001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2241', '百色右江华润村镇银行', '15916260', '', '百色右江华润村镇银行金卡', '19', '9', '623608002', '0');
INSERT INTO `authbank_cardbin` VALUES ('2242', '西安高陵阳光村镇银行', '15947916', '', '金丝路阳光卡', '18', '8', '62361026', '0');
INSERT INTO `authbank_cardbin` VALUES ('2243', '陕西洛南阳光村镇银行', '15948032', '', '金丝路阳光卡', '18', '8', '62361025', '0');
INSERT INTO `authbank_cardbin` VALUES ('2244', '江苏丹阳保得村镇银行', '16013144', '', '丹桂IC借记卡', '19', '8', '62351501', '0');
INSERT INTO `authbank_cardbin` VALUES ('2245', '襄城汇浦村镇银行', '16015035', '', '金汇卡', '19', '9', '623517001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2246', '江苏溧水民丰村镇银行', '16023010', '', '金鼎卡', '19', '8', '62168305', '0');
INSERT INTO `authbank_cardbin` VALUES ('2247', '江苏丰县民丰村镇银行', '16023031', '', '金鼎卡', '16', '8', '62168301', '0');
INSERT INTO `authbank_cardbin` VALUES ('2248', '江苏灌云民丰村镇银行', '16023073', '', '金鼎卡', '19', '8', '62168304', '0');
INSERT INTO `authbank_cardbin` VALUES ('2249', '江苏灌南民丰村镇银行', '16023074', '', '金鼎卡', '19', '8', '62168302', '0');
INSERT INTO `authbank_cardbin` VALUES ('2250', '安徽明光民丰村镇银行', '16023757', '', '金鼎卡', '19', '8', '62168308', '0');
INSERT INTO `authbank_cardbin` VALUES ('2251', '山东肥城民丰村镇银行', '16024632', '', '金鼎卡', '19', '8', '62168303', '0');
INSERT INTO `authbank_cardbin` VALUES ('2252', '东亚银行有限公司', '25020344', '', 'cup credit card', '16', '6', '622372', '1');
INSERT INTO `authbank_cardbin` VALUES ('2253', '东亚银行有限公司', '25020344', '', '电子网络人民币卡', '17', '6', '622365', '0');
INSERT INTO `authbank_cardbin` VALUES ('2254', '东亚银行有限公司', '25020344', '', '人民币信用卡(银联卡)', '16', '6', '622471', '1');
INSERT INTO `authbank_cardbin` VALUES ('2255', '东亚银行有限公司', '25020344', '', '银联借记卡', '19', '6', '622943', '0');
INSERT INTO `authbank_cardbin` VALUES ('2256', '东亚银行有限公司', '25020344', '', '人民币信用卡金卡', '16', '6', '622472', '1');
INSERT INTO `authbank_cardbin` VALUES ('2257', '东亚银行有限公司', '25020344', '', '银联双币借记卡', '19', '6', '623318', '0');
INSERT INTO `authbank_cardbin` VALUES ('2258', '东亚银行澳门分行', '25020446', '', '银联借记卡', '19', '6', '621411', '0');
INSERT INTO `authbank_cardbin` VALUES ('2259', '花旗银行有限公司', '25030344', '', '花旗人民币信用卡', '16', '6', '622371', '1');
INSERT INTO `authbank_cardbin` VALUES ('2260', '花旗银行有限公司', '25030344', '', '双币卡', '16', '6', '625091', '1');
INSERT INTO `authbank_cardbin` VALUES ('2261', '大新银行有限公司', '25040344', '', '信用卡(普通卡)', '16', '6', '622293', '1');
INSERT INTO `authbank_cardbin` VALUES ('2262', '大新银行有限公司', '25040344', '', '商务信用卡', '16', '6', '622295', '1');
INSERT INTO `authbank_cardbin` VALUES ('2263', '大新银行有限公司', '25040344', '', '商务信用卡', '16', '6', '622296', '1');
INSERT INTO `authbank_cardbin` VALUES ('2264', '大新银行有限公司', '25040344', '', '预付卡(普通卡)', '16', '6', '622297', '0');
INSERT INTO `authbank_cardbin` VALUES ('2265', '大新银行有限公司', '25040344', '', '人民币信用卡', '16', '6', '622373', '1');
INSERT INTO `authbank_cardbin` VALUES ('2266', '大新银行有限公司', '25040344', '', '人民币借记卡(银联卡)', '17', '6', '622375', '0');
INSERT INTO `authbank_cardbin` VALUES ('2267', '大新银行有限公司', '25040344', '', '大新人民币信用卡金卡', '16', '6', '622451', '1');
INSERT INTO `authbank_cardbin` VALUES ('2268', '大新银行有限公司', '25040344', '', '大新港币信用卡(金卡)', '16', '6', '622294', '1');
INSERT INTO `authbank_cardbin` VALUES ('2269', '大新银行有限公司', '25040344', '', '贷记卡', '16', '6', '625940', '1');
INSERT INTO `authbank_cardbin` VALUES ('2270', '大新银行有限公司', '25040344', '', '借记卡(银联卡)', '17', '6', '622489', '0');
INSERT INTO `authbank_cardbin` VALUES ('2271', '永亨银行', '25060344', '', '永亨尊贵理财卡', '16', '6', '622871', '0');
INSERT INTO `authbank_cardbin` VALUES ('2272', '永亨银行', '25060344', '', '永亨贵宾理财卡', '16', '6', '622958', '0');
INSERT INTO `authbank_cardbin` VALUES ('2273', '永亨银行', '25060344', '', '永亨贵宾理财卡', '16', '6', '622963', '0');
INSERT INTO `authbank_cardbin` VALUES ('2274', '永亨银行', '25060344', '', '永亨贵宾理财卡', '16', '6', '622957', '0');
INSERT INTO `authbank_cardbin` VALUES ('2275', '永亨银行', '25060344', '', '港币贷记卡', '16', '6', '622798', '1');
INSERT INTO `authbank_cardbin` VALUES ('2276', '永亨银行', '25060344', '', '永亨银联白金卡', '16', '6', '625010', '1');
INSERT INTO `authbank_cardbin` VALUES ('2277', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '人民币信用卡', '16', '6', '622381', '1');
INSERT INTO `authbank_cardbin` VALUES ('2278', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '银联卡', '16', '6', '622675', '1');
INSERT INTO `authbank_cardbin` VALUES ('2279', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '银联卡', '16', '6', '622676', '1');
INSERT INTO `authbank_cardbin` VALUES ('2280', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '银联卡', '16', '6', '622677', '1');
INSERT INTO `authbank_cardbin` VALUES ('2281', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '人民币卡(银联卡)', '16', '6', '622382', '0');
INSERT INTO `authbank_cardbin` VALUES ('2282', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '借记卡', '16', '6', '621487', '0');
INSERT INTO `authbank_cardbin` VALUES ('2283', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', '建行陆港通龙卡', '16', '6', '621083', '0');
INSERT INTO `authbank_cardbin` VALUES ('2284', '中国建设银行亚洲股份有限公司', '25070344', 'CCB', 'CCBA Diamond Dual', '16', '6', '624329', '1');
INSERT INTO `authbank_cardbin` VALUES ('2285', '星展银行香港有限公司', '25080344', '', '银联人民币银行卡', '16', '6', '622487', '0');
INSERT INTO `authbank_cardbin` VALUES ('2286', '星展银行香港有限公司', '25080344', '', '银联人民币银行卡', '17', '6', '622487', '0');
INSERT INTO `authbank_cardbin` VALUES ('2287', '星展银行香港有限公司', '25080344', '', '银联人民币银行卡', '16', '6', '622490', '0');
INSERT INTO `authbank_cardbin` VALUES ('2288', '星展银行香港有限公司', '25080344', '', '银联人民币银行卡', '17', '6', '622490', '0');
INSERT INTO `authbank_cardbin` VALUES ('2289', '星展银行香港有限公司', '25080344', '', '银联银行卡', '16', '6', '622491', '0');
INSERT INTO `authbank_cardbin` VALUES ('2290', '星展银行香港有限公司', '25080344', '', '银联港币银行卡', '17', '6', '622491', '0');
INSERT INTO `authbank_cardbin` VALUES ('2291', '星展银行香港有限公司', '25080344', '', '银联银行卡', '16', '6', '622492', '0');
INSERT INTO `authbank_cardbin` VALUES ('2292', '星展银行香港有限公司', '25080344', '', '银联港币银行卡', '17', '6', '622492', '0');
INSERT INTO `authbank_cardbin` VALUES ('2293', '星展银行香港有限公司', '25080344', '', '借记卡', '17', '6', '621744', '0');
INSERT INTO `authbank_cardbin` VALUES ('2294', '星展银行香港有限公司', '25080344', '', '借记卡', '17', '6', '621745', '0');
INSERT INTO `authbank_cardbin` VALUES ('2295', '星展银行香港有限公司', '25080344', '', '借记卡', '17', '6', '621746', '0');
INSERT INTO `authbank_cardbin` VALUES ('2296', '星展银行香港有限公司', '25080344', '', '借记卡', '17', '6', '621747', '0');
INSERT INTO `authbank_cardbin` VALUES ('2297', '上海商业银行', '25090344', '', '上银卡', '16', '6', '621034', '0');
INSERT INTO `authbank_cardbin` VALUES ('2298', '上海商业银行', '25090344', '', '人民币信用卡(银联卡)', '16', '6', '622386', '1');
INSERT INTO `authbank_cardbin` VALUES ('2299', '上海商业银行', '25090344', '', '上银卡ShacomCard', '16', '6', '622952', '0');
INSERT INTO `authbank_cardbin` VALUES ('2300', '上海商业银行', '25090344', '', 'Dual Curr.Corp.Card', '16', '6', '625107', '1');
INSERT INTO `authbank_cardbin` VALUES ('2301', '永隆银行有限公司', '25100344', '', '永隆人民币信用卡', '16', '6', '622387', '1');
INSERT INTO `authbank_cardbin` VALUES ('2302', '永隆银行有限公司', '25100344', '', '永隆人民币信用卡', '16', '6', '622423', '1');
INSERT INTO `authbank_cardbin` VALUES ('2303', '永隆银行有限公司', '25100344', '', '永隆港币卡', '17', '6', '622971', '0');
INSERT INTO `authbank_cardbin` VALUES ('2304', '永隆银行有限公司', '25100344', '', '永隆人民币卡', '17', '6', '622970', '0');
INSERT INTO `authbank_cardbin` VALUES ('2305', '永隆银行有限公司', '25100344', '', '永隆双币卡', '16', '6', '625062', '1');
INSERT INTO `authbank_cardbin` VALUES ('2306', '永隆银行有限公司', '25100344', '', '永隆双币卡', '16', '6', '625063', '1');
INSERT INTO `authbank_cardbin` VALUES ('2307', '香港上海汇丰银行有限公司', '25120344', '', '人民币卡(银联卡)', '16', '6', '622360', '1');
INSERT INTO `authbank_cardbin` VALUES ('2308', '香港上海汇丰银行有限公司', '25120344', '', '人民币金卡(银联卡)', '16', '6', '622361', '1');
INSERT INTO `authbank_cardbin` VALUES ('2309', '香港上海汇丰银行有限公司', '25120344', '', '银联卡', '16', '6', '625034', '1');
INSERT INTO `authbank_cardbin` VALUES ('2310', '香港上海汇丰银行有限公司', '25120344', '', '汇丰银联双币卡', '16', '6', '625096', '1');
INSERT INTO `authbank_cardbin` VALUES ('2311', '香港上海汇丰银行有限公司', '25120344', '', '汇丰银联双币钻石卡', '16', '6', '625098', '1');
INSERT INTO `authbank_cardbin` VALUES ('2312', '香港上海汇丰银行有限公司', '25130344', '', 'ATMCard', '17', '6', '622406', '0');
INSERT INTO `authbank_cardbin` VALUES ('2313', '香港上海汇丰银行有限公司', '25130344', '', 'ATMCard', '19', '6', '622407', '0');
INSERT INTO `authbank_cardbin` VALUES ('2314', '香港上海汇丰银行有限公司', '25130344', '', '借记卡', '17', '6', '621442', '0');
INSERT INTO `authbank_cardbin` VALUES ('2315', '香港上海汇丰银行有限公司', '25130344', '', '借记卡', '19', '6', '621443', '0');
INSERT INTO `authbank_cardbin` VALUES ('2316', '恒生银行有限公司', '25140344', '', '港币贷记白金卡', '16', '6', '625026', '1');
INSERT INTO `authbank_cardbin` VALUES ('2317', '恒生银行有限公司', '25140344', '', '港币贷记普卡', '16', '6', '625024', '1');
INSERT INTO `authbank_cardbin` VALUES ('2318', '恒生银行有限公司', '25140344', '', '恒生人民币信用卡', '16', '6', '622376', '1');
INSERT INTO `authbank_cardbin` VALUES ('2319', '恒生银行有限公司', '25140344', '', '恒生人民币白金卡', '16', '6', '622378', '1');
INSERT INTO `authbank_cardbin` VALUES ('2320', '恒生银行有限公司', '25140344', '', '恒生人民币金卡', '16', '6', '622377', '1');
INSERT INTO `authbank_cardbin` VALUES ('2321', '恒生银行有限公司', '25140344', '', '银联人民币钻石商务卡', '16', '6', '625092', '1');
INSERT INTO `authbank_cardbin` VALUES ('2322', '恒生银行', '25150344', '', '恒生银行港卡借记卡', '19', '6', '622409', '0');
INSERT INTO `authbank_cardbin` VALUES ('2323', '恒生银行', '25150344', '', '恒生银行港卡借记卡', '17', '6', '622410', '0');
INSERT INTO `authbank_cardbin` VALUES ('2324', '恒生银行', '25150344', '', '港币借记卡（普卡）', '17', '6', '621440', '0');
INSERT INTO `authbank_cardbin` VALUES ('2325', '恒生银行', '25150344', '', '港币借记卡（金卡）', '19', '6', '621441', '0');
INSERT INTO `authbank_cardbin` VALUES ('2326', '恒生银行', '25150344', '', '港币借记卡(普卡)', '17', '6', '623106', '0');
INSERT INTO `authbank_cardbin` VALUES ('2327', '恒生银行', '25150344', '', '港币借记卡(普卡)', '19', '6', '623107', '0');
INSERT INTO `authbank_cardbin` VALUES ('2328', '中信嘉华银行有限公司', '25160344', '', '人民币信用卡金卡', '16', '6', '622453', '1');
INSERT INTO `authbank_cardbin` VALUES ('2329', '中信嘉华银行有限公司', '25160344', '', '信用卡普通卡', '16', '6', '622456', '1');
INSERT INTO `authbank_cardbin` VALUES ('2330', '中信嘉华银行有限公司', '25160344', '', '人民币借记卡(银联卡)', '17', '6', '622459', '0');
INSERT INTO `authbank_cardbin` VALUES ('2331', '中信嘉华银行有限公司', '25160344', '', '信银国际国航知音双币信用卡', '16', '6', '624303', '1');
INSERT INTO `authbank_cardbin` VALUES ('2332', '中信嘉华银行有限公司', '25160344', '', 'CNCBI HKD CUP Debit Card', '17', '6', '623328', '0');
INSERT INTO `authbank_cardbin` VALUES ('2333', '创兴银行有限公司', '25170344', '', '银联贺礼卡(创兴银行)', '16', '6', '622272', '0');
INSERT INTO `authbank_cardbin` VALUES ('2334', '创兴银行有限公司', '25170344', '', '港币借记卡', '19', '6', '622463', '0');
INSERT INTO `authbank_cardbin` VALUES ('2335', '创兴银行有限公司', '25170344', '', '人民币提款卡', '19', '6', '621087', '0');
INSERT INTO `authbank_cardbin` VALUES ('2336', '创兴银行有限公司', '25170344', '', '银联双币信用卡', '16', '6', '625008', '1');
INSERT INTO `authbank_cardbin` VALUES ('2337', '创兴银行有限公司', '25170344', '', '银联双币信用卡', '16', '6', '625009', '1');
INSERT INTO `authbank_cardbin` VALUES ('2338', '创兴银行有限公司', '25170344', '', '贷记卡', '16', '6', '624337', '1');
INSERT INTO `authbank_cardbin` VALUES ('2339', '中银信用卡(国际)有限公司', '25180344', '', '商务金卡', '16', '6', '625055', '1');
INSERT INTO `authbank_cardbin` VALUES ('2340', '中银信用卡(国际)有限公司', '25180344', '', '中银银联双币信用卡', '16', '6', '625040', '1');
INSERT INTO `authbank_cardbin` VALUES ('2341', '中银信用卡(国际)有限公司', '25180344', '', '中银银联双币信用卡', '16', '6', '625042', '1');
INSERT INTO `authbank_cardbin` VALUES ('2342', '中银信用卡(国际)有限公司', '25180446', '', '澳门币贷记卡', '16', '6', '625141', '1');
INSERT INTO `authbank_cardbin` VALUES ('2343', '中银信用卡(国际)有限公司', '25180446', '', '澳门币贷记卡', '16', '6', '625143', '1');
INSERT INTO `authbank_cardbin` VALUES ('2344', '中国银行（香港）', '25190344', 'BOC', '接触式晶片借记卡', '19', '6', '621741', '0');
INSERT INTO `authbank_cardbin` VALUES ('2345', '中国银行（香港）', '25190344', 'BOC', '接触式银联双币预制晶片借记卡', '16', '6', '623040', '0');
INSERT INTO `authbank_cardbin` VALUES ('2346', '中国银行（香港）', '25190344', 'BOC', '中国银行银联预付卡', '16', '6', '620202', '2');
INSERT INTO `authbank_cardbin` VALUES ('2347', '中国银行（香港）', '25190344', 'BOC', '中国银行银联预付卡', '16', '6', '620203', '2');
INSERT INTO `authbank_cardbin` VALUES ('2348', '中国银行（香港）', '25190344', 'BOC', '中银Good Day银联双币白金卡', '16', '6', '625136', '1');
INSERT INTO `authbank_cardbin` VALUES ('2349', '中国银行（香港）', '25190344', 'BOC', '中银纯电子现金双币卡', '19', '6', '621782', '0');
INSERT INTO `authbank_cardbin` VALUES ('2350', '中国银行（香港）', '25190344', 'BOC', '中国银行银联公司借记卡', '19', '6', '623309', '0');
INSERT INTO `authbank_cardbin` VALUES ('2351', '南洋商业银行', '25200344', '', '银联双币信用卡', '16', '6', '625046', '1');
INSERT INTO `authbank_cardbin` VALUES ('2352', '南洋商业银行', '25200344', '', '银联双币信用卡', '16', '6', '625044', '1');
INSERT INTO `authbank_cardbin` VALUES ('2353', '南洋商业银行', '25200344', '', '双币商务卡', '16', '6', '625058', '1');
INSERT INTO `authbank_cardbin` VALUES ('2354', '南洋商业银行', '25200344', '', '接触式晶片借记卡', '19', '6', '621743', '0');
INSERT INTO `authbank_cardbin` VALUES ('2355', '南洋商业银行', '25200344', '', '接触式银联双币预制晶片借记卡', '16', '6', '623041', '0');
INSERT INTO `authbank_cardbin` VALUES ('2356', '南洋商业银行', '25200344', '', '南洋商业银行银联预付卡', '16', '6', '620208', '2');
INSERT INTO `authbank_cardbin` VALUES ('2357', '南洋商业银行', '25200344', '', '南洋商业银行银联预付卡', '16', '6', '620209', '2');
INSERT INTO `authbank_cardbin` VALUES ('2358', '南洋商业银行', '25200344', '', '银联港币卡', '19', '6', '621042', '0');
INSERT INTO `authbank_cardbin` VALUES ('2359', '南洋商业银行', '25200344', '', '中银纯电子现金双币卡', '19', '6', '621783', '0');
INSERT INTO `authbank_cardbin` VALUES ('2360', '南洋商业银行', '25200344', '', '南洋商业银联公司借记卡', '19', '6', '623308', '0');
INSERT INTO `authbank_cardbin` VALUES ('2361', '集友银行', '25210344', '', '银联双币信用卡', '16', '6', '625048', '1');
INSERT INTO `authbank_cardbin` VALUES ('2362', '集友银行', '25210344', '', '银联双币信用卡', '16', '6', '625053', '1');
INSERT INTO `authbank_cardbin` VALUES ('2363', '集友银行', '25210344', '', '双币商务卡', '16', '6', '625060', '1');
INSERT INTO `authbank_cardbin` VALUES ('2364', '集友银行', '25210344', '', '接触式晶片借记卡', '19', '6', '621742', '0');
INSERT INTO `authbank_cardbin` VALUES ('2365', '集友银行', '25210344', '', '接触式银联双币预制晶片借记卡', '16', '6', '623042', '0');
INSERT INTO `authbank_cardbin` VALUES ('2366', '集友银行', '25210344', '', '集友银行银联预付卡', '16', '6', '620206', '2');
INSERT INTO `authbank_cardbin` VALUES ('2367', '集友银行', '25210344', '', '集友银行银联预付卡', '16', '6', '620207', '2');
INSERT INTO `authbank_cardbin` VALUES ('2368', '集友银行', '25210344', '', '银联港币卡', '19', '6', '621043', '0');
INSERT INTO `authbank_cardbin` VALUES ('2369', '集友银行', '25210344', '', '中银纯电子现金双币卡', '19', '6', '621784', '0');
INSERT INTO `authbank_cardbin` VALUES ('2370', '集友银行', '25210344', '', '集友银行银联公司借记卡', '19', '6', '623310', '0');
INSERT INTO `authbank_cardbin` VALUES ('2371', 'AEON信贷财务亚洲有限公司', '25230344', '', 'AEONJUSCO银联卡', '16', '6', '622493', '1');
INSERT INTO `authbank_cardbin` VALUES ('2372', '大丰银行有限公司', '25250446', '', '银联双币白金卡', '16', '6', '625198', '1');
INSERT INTO `authbank_cardbin` VALUES ('2373', '大丰银行有限公司', '25250446', '', '银联双币金卡', '16', '6', '625196', '1');
INSERT INTO `authbank_cardbin` VALUES ('2374', '大丰银行有限公司', '25250446', '', '港币借记卡', '19', '6', '622547', '0');
INSERT INTO `authbank_cardbin` VALUES ('2375', '大丰银行有限公司', '25250446', '', '澳门币借记卡', '19', '6', '622548', '0');
INSERT INTO `authbank_cardbin` VALUES ('2376', '大丰银行有限公司', '25250446', '', '人民币借记卡', '19', '6', '622546', '0');
INSERT INTO `authbank_cardbin` VALUES ('2377', '澳门大丰银行', '25250446', '', '中银银联双币商务卡', '16', '6', '625147', '1');
INSERT INTO `authbank_cardbin` VALUES ('2378', '大丰银行有限公司', '25250446', '', '大丰预付卡', '19', '6', '620072', '2');
INSERT INTO `authbank_cardbin` VALUES ('2379', '大丰银行有限公司', '25250446', '', '大丰银行预付卡', '16', '6', '620204', '2');
INSERT INTO `authbank_cardbin` VALUES ('2380', '大丰银行有限公司', '25250446', '', '大丰银行预付卡', '16', '6', '620205', '2');
INSERT INTO `authbank_cardbin` VALUES ('2381', 'AEON信贷财务亚洲有限公司', '25260344', '', 'AEON银联礼品卡', '16', '6', '621064', '0');
INSERT INTO `authbank_cardbin` VALUES ('2382', 'AEON信贷财务亚洲有限公司', '25260344', '', 'AEON银联礼品卡', '16', '6', '622941', '0');
INSERT INTO `authbank_cardbin` VALUES ('2383', 'AEON信贷财务亚洲有限公司', '25260344', '', 'AEON银联礼品卡', '16', '6', '622974', '0');
INSERT INTO `authbank_cardbin` VALUES ('2384', '中国建设银行澳门股份有限公司', '25270446', 'CCB', '扣款卡', '16', '6', '621084', '0');
INSERT INTO `authbank_cardbin` VALUES ('2385', '中国建设银行澳门股份有限公司', '25270446', 'CCB', '借记卡', '16', '6', '623350', '0');
INSERT INTO `authbank_cardbin` VALUES ('2386', '渣打银行香港有限公司', '25280344', '', '港币借记卡', '16', '6', '622948', '0');
INSERT INTO `authbank_cardbin` VALUES ('2387', '渣打银行（香港）', '25280344', '', '银联标准卡', '16', '6', '621740', '0');
INSERT INTO `authbank_cardbin` VALUES ('2388', '渣打银行香港有限公司', '25280344', '', '双币信用卡', '16', '6', '622482', '1');
INSERT INTO `authbank_cardbin` VALUES ('2389', '渣打银行香港有限公司', '25280344', '', '双币信用卡', '16', '6', '622483', '1');
INSERT INTO `authbank_cardbin` VALUES ('2390', '渣打银行香港有限公司', '25280344', '', '双币信用卡', '16', '6', '622484', '1');
INSERT INTO `authbank_cardbin` VALUES ('2391', '中国银盛', '25290344', '', '中国银盛预付卡', '16', '6', '620070', '2');
INSERT INTO `authbank_cardbin` VALUES ('2392', '中国银盛', '25300344', '', '中国银盛预付卡', '16', '6', '620068', '2');
INSERT INTO `authbank_cardbin` VALUES ('2393', '中国建设银行（亚洲）', '25330344', 'CCB', '预付卡', '16', '6', '620107', '0');
INSERT INTO `authbank_cardbin` VALUES ('2394', 'K&RInternationalLimited', '25380344', '', '环球通', '16', '6', '623334', '2');
INSERT INTO `authbank_cardbin` VALUES ('2395', 'KasikornBankPCL', '26030764', '', '贷记卡', '16', '6', '625842', '1');
INSERT INTO `authbank_cardbin` VALUES ('2396', 'KasikornBankPCL', '26030764', '', '贷记卡', '16', '7', '6258433', '1');
INSERT INTO `authbank_cardbin` VALUES ('2397', 'KasikornBankPCL', '26030764', '', '贷记卡', '16', '7', '6258434', '1');
INSERT INTO `authbank_cardbin` VALUES ('2398', 'Travelex', '26040344', '', 'Travelex港币卡', '16', '6', '622495', '0');
INSERT INTO `authbank_cardbin` VALUES ('2399', 'Travelex', '26040344', '', 'Travelex美元卡', '16', '6', '622496', '0');
INSERT INTO `authbank_cardbin` VALUES ('2400', 'Travelex', '26040344', '', 'CashPassportCounsumer', '16', '6', '620152', '2');
INSERT INTO `authbank_cardbin` VALUES ('2401', 'Travelex', '26040344', '', 'CashPassportCounsumer', '16', '6', '620153', '2');
INSERT INTO `authbank_cardbin` VALUES ('2402', '新加坡大华银行', '26070702', '', 'UOBCUPCARD', '16', '6', '622433', '1');
INSERT INTO `authbank_cardbin` VALUES ('2403', '澳门永亨银行股份有限公司', '26080446', '', '人民币卡', '16', '6', '622861', '0');
INSERT INTO `authbank_cardbin` VALUES ('2404', '澳门永亨银行股份有限公司', '26080446', '', '港币借记卡', '16', '6', '622932', '0');
INSERT INTO `authbank_cardbin` VALUES ('2405', '澳门永亨银行股份有限公司', '26080446', '', '澳门币借记卡', '16', '6', '622862', '0');
INSERT INTO `authbank_cardbin` VALUES ('2406', '澳门永亨银行股份有限公司', '26080446', '', '澳门币贷记卡', '16', '6', '622775', '1');
INSERT INTO `authbank_cardbin` VALUES ('2407', '澳门永亨银行股份有限公司', '26080446', '', '港币贷记卡', '16', '6', '622785', '1');
INSERT INTO `authbank_cardbin` VALUES ('2408', '日本三井住友卡公司', '26110392', '', 'MITSUISUMITOMOGINREN', '16', '6', '622920', '1');
INSERT INTO `authbank_cardbin` VALUES ('2409', '澳门国际银行', '26220446', '', '人民币卡', '19', '6', '622434', '0');
INSERT INTO `authbank_cardbin` VALUES ('2410', '澳门国际银行', '26220446', '', '澳门币卡', '19', '6', '622436', '0');
INSERT INTO `authbank_cardbin` VALUES ('2411', '澳门国际银行', '26220446', '', '港币卡', '19', '6', '622435', '0');
INSERT INTO `authbank_cardbin` VALUES ('2412', '大西洋银行股份有限公司', '26230446', '', '财运卡', '19', '6', '621232', '0');
INSERT INTO `authbank_cardbin` VALUES ('2413', '大西洋银行股份有限公司', '26230446', '', '澳门币卡', '19', '6', '622432', '0');
INSERT INTO `authbank_cardbin` VALUES ('2414', '大西洋银行股份有限公司', '26230446', '', '财运卡', '19', '6', '621247', '0');
INSERT INTO `authbank_cardbin` VALUES ('2415', '大西洋银行股份有限公司', '26230446', '', '财运卡', '16', '6', '623043', '0');
INSERT INTO `authbank_cardbin` VALUES ('2416', '大西洋银行股份有限公司', '26230446', '', '财运卡', '16', '6', '623064', '0');
INSERT INTO `authbank_cardbin` VALUES ('2417', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601100', '1');
INSERT INTO `authbank_cardbin` VALUES ('2418', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601101', '1');
INSERT INTO `authbank_cardbin` VALUES ('2419', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112010', '1');
INSERT INTO `authbank_cardbin` VALUES ('2420', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112011', '1');
INSERT INTO `authbank_cardbin` VALUES ('2421', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112012', '1');
INSERT INTO `authbank_cardbin` VALUES ('2422', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112089', '1');
INSERT INTO `authbank_cardbin` VALUES ('2423', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601121', '1');
INSERT INTO `authbank_cardbin` VALUES ('2424', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601123', '1');
INSERT INTO `authbank_cardbin` VALUES ('2425', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601124', '1');
INSERT INTO `authbank_cardbin` VALUES ('2426', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601125', '1');
INSERT INTO `authbank_cardbin` VALUES ('2427', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601126', '1');
INSERT INTO `authbank_cardbin` VALUES ('2428', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601127', '1');
INSERT INTO `authbank_cardbin` VALUES ('2429', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601128', '1');
INSERT INTO `authbank_cardbin` VALUES ('2430', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011290', '1');
INSERT INTO `authbank_cardbin` VALUES ('2431', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011291', '1');
INSERT INTO `authbank_cardbin` VALUES ('2432', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011292', '1');
INSERT INTO `authbank_cardbin` VALUES ('2433', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011293', '1');
INSERT INTO `authbank_cardbin` VALUES ('2434', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112013', '1');
INSERT INTO `authbank_cardbin` VALUES ('2435', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011295', '1');
INSERT INTO `authbank_cardbin` VALUES ('2436', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601122', '1');
INSERT INTO `authbank_cardbin` VALUES ('2437', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011297', '1');
INSERT INTO `authbank_cardbin` VALUES ('2438', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112980', '1');
INSERT INTO `authbank_cardbin` VALUES ('2439', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112981', '1');
INSERT INTO `authbank_cardbin` VALUES ('2440', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112986', '1');
INSERT INTO `authbank_cardbin` VALUES ('2441', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112987', '1');
INSERT INTO `authbank_cardbin` VALUES ('2442', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112988', '1');
INSERT INTO `authbank_cardbin` VALUES ('2443', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112989', '1');
INSERT INTO `authbank_cardbin` VALUES ('2444', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112990', '1');
INSERT INTO `authbank_cardbin` VALUES ('2445', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112991', '1');
INSERT INTO `authbank_cardbin` VALUES ('2446', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112992', '1');
INSERT INTO `authbank_cardbin` VALUES ('2447', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112993', '1');
INSERT INTO `authbank_cardbin` VALUES ('2448', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011294', '1');
INSERT INTO `authbank_cardbin` VALUES ('2449', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011296', '1');
INSERT INTO `authbank_cardbin` VALUES ('2450', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112996', '1');
INSERT INTO `authbank_cardbin` VALUES ('2451', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112997', '1');
INSERT INTO `authbank_cardbin` VALUES ('2452', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011300', '1');
INSERT INTO `authbank_cardbin` VALUES ('2453', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113080', '1');
INSERT INTO `authbank_cardbin` VALUES ('2454', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113081', '1');
INSERT INTO `authbank_cardbin` VALUES ('2455', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113089', '1');
INSERT INTO `authbank_cardbin` VALUES ('2456', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601131', '1');
INSERT INTO `authbank_cardbin` VALUES ('2457', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601136', '1');
INSERT INTO `authbank_cardbin` VALUES ('2458', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601137', '1');
INSERT INTO `authbank_cardbin` VALUES ('2459', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601138', '1');
INSERT INTO `authbank_cardbin` VALUES ('2460', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011390', '1');
INSERT INTO `authbank_cardbin` VALUES ('2461', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112995', '1');
INSERT INTO `authbank_cardbin` VALUES ('2462', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011392', '1');
INSERT INTO `authbank_cardbin` VALUES ('2463', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011393', '1');
INSERT INTO `authbank_cardbin` VALUES ('2464', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113940', '1');
INSERT INTO `authbank_cardbin` VALUES ('2465', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113941', '1');
INSERT INTO `authbank_cardbin` VALUES ('2466', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113943', '1');
INSERT INTO `authbank_cardbin` VALUES ('2467', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113944', '1');
INSERT INTO `authbank_cardbin` VALUES ('2468', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113945', '1');
INSERT INTO `authbank_cardbin` VALUES ('2469', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113946', '1');
INSERT INTO `authbank_cardbin` VALUES ('2470', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113984', '1');
INSERT INTO `authbank_cardbin` VALUES ('2471', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113985', '1');
INSERT INTO `authbank_cardbin` VALUES ('2472', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113986', '1');
INSERT INTO `authbank_cardbin` VALUES ('2473', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113988', '1');
INSERT INTO `authbank_cardbin` VALUES ('2474', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60112994', '1');
INSERT INTO `authbank_cardbin` VALUES ('2475', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011391', '1');
INSERT INTO `authbank_cardbin` VALUES ('2476', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601140', '1');
INSERT INTO `authbank_cardbin` VALUES ('2477', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601142', '1');
INSERT INTO `authbank_cardbin` VALUES ('2478', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601143', '1');
INSERT INTO `authbank_cardbin` VALUES ('2479', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601144', '1');
INSERT INTO `authbank_cardbin` VALUES ('2480', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601145', '1');
INSERT INTO `authbank_cardbin` VALUES ('2481', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601146', '1');
INSERT INTO `authbank_cardbin` VALUES ('2482', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601147', '1');
INSERT INTO `authbank_cardbin` VALUES ('2483', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601148', '1');
INSERT INTO `authbank_cardbin` VALUES ('2484', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601149', '1');
INSERT INTO `authbank_cardbin` VALUES ('2485', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601174', '1');
INSERT INTO `authbank_cardbin` VALUES ('2486', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '8', '60113989', '1');
INSERT INTO `authbank_cardbin` VALUES ('2487', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601178', '1');
INSERT INTO `authbank_cardbin` VALUES ('2488', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '7', '6011399', '1');
INSERT INTO `authbank_cardbin` VALUES ('2489', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601186', '1');
INSERT INTO `authbank_cardbin` VALUES ('2490', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601187', '1');
INSERT INTO `authbank_cardbin` VALUES ('2491', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601188', '1');
INSERT INTO `authbank_cardbin` VALUES ('2492', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601189', '1');
INSERT INTO `authbank_cardbin` VALUES ('2493', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '3', '644', '1');
INSERT INTO `authbank_cardbin` VALUES ('2494', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '2', '65', '1');
INSERT INTO `authbank_cardbin` VALUES ('2495', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '4', '6506', '1');
INSERT INTO `authbank_cardbin` VALUES ('2496', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '4', '6507', '1');
INSERT INTO `authbank_cardbin` VALUES ('2497', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '4', '6508', '1');
INSERT INTO `authbank_cardbin` VALUES ('2498', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601177', '1');
INSERT INTO `authbank_cardbin` VALUES ('2499', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '6', '601179', '1');
INSERT INTO `authbank_cardbin` VALUES ('2500', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '4', '6509', '1');
INSERT INTO `authbank_cardbin` VALUES ('2501', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '5', '60110', '1');
INSERT INTO `authbank_cardbin` VALUES ('2502', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '5', '60112', '1');
INSERT INTO `authbank_cardbin` VALUES ('2503', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '5', '60113', '1');
INSERT INTO `authbank_cardbin` VALUES ('2504', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '5', '60114', '1');
INSERT INTO `authbank_cardbin` VALUES ('2505', 'DiscoverFinancialServices，I', '26290840', '', '发现卡', '16', '5', '60119', '1');
INSERT INTO `authbank_cardbin` VALUES ('2506', '澳门商业银行', '26320446', '', '银联人民币卡', '19', '6', '621253', '0');
INSERT INTO `authbank_cardbin` VALUES ('2507', '澳门商业银行', '26320446', '', '银联澳门币卡', '19', '6', '621254', '0');
INSERT INTO `authbank_cardbin` VALUES ('2508', '澳门商业银行', '26320446', '', '银联港币卡', '19', '6', '621255', '0');
INSERT INTO `authbank_cardbin` VALUES ('2509', '澳门商业银行', '26320446', '', '双币种普卡', '16', '6', '625014', '1');
INSERT INTO `authbank_cardbin` VALUES ('2510', '澳门商业银行', '26320446', '', '双币种白金卡', '16', '6', '625016', '1');
INSERT INTO `authbank_cardbin` VALUES ('2511', '哈萨克斯坦国民储蓄银行', '26330398', '', 'HalykbankClassic', '16', '6', '622549', '0');
INSERT INTO `authbank_cardbin` VALUES ('2512', '哈萨克斯坦国民储蓄银行', '26330398', '', 'HalykbankGolden', '16', '6', '622550', '0');
INSERT INTO `authbank_cardbin` VALUES ('2513', 'BangkokBankPcl', '26350764', '', '贷记卡', '16', '6', '622354', '1');
INSERT INTO `authbank_cardbin` VALUES ('2514', '中国工商银行（澳门）', '26470446', 'ICBC', '普卡', '16', '6', '625017', '1');
INSERT INTO `authbank_cardbin` VALUES ('2515', '中国工商银行（澳门）', '26470446', 'ICBC', '金卡', '16', '6', '625018', '1');
INSERT INTO `authbank_cardbin` VALUES ('2516', '中国工商银行（澳门）', '26470446', 'ICBC', '白金卡', '16', '6', '625019', '1');
INSERT INTO `authbank_cardbin` VALUES ('2517', '可汗银行', '26530496', '', '借记卡', '16', '6', '621224', '0');
INSERT INTO `authbank_cardbin` VALUES ('2518', '可汗银行', '26530496', '', '银联蒙图借记卡', '16', '6', '622954', '0');
INSERT INTO `authbank_cardbin` VALUES ('2519', '越南Vietcombank', '26550704', '', '借记卡', '16', '6', '621295', '0');
INSERT INTO `authbank_cardbin` VALUES ('2520', '越南Vietcombank', '26550704', '', '贷记卡', '16', '6', '625124', '1');
INSERT INTO `authbank_cardbin` VALUES ('2521', '越南Vietcombank', '26550704', '', '贷记卡', '16', '6', '625154', '1');
INSERT INTO `authbank_cardbin` VALUES ('2522', '蒙古郭勒姆特银行', '26620496', '', 'Golomt Unionpay', '16', '6', '621049', '0');
INSERT INTO `authbank_cardbin` VALUES ('2523', '蒙古郭勒姆特银行', '26620496', '', '贷记卡', '16', '6', '622444', '1');
INSERT INTO `authbank_cardbin` VALUES ('2524', '蒙古郭勒姆特银行', '26620496', '', '借记卡', '16', '6', '622414', '0');
INSERT INTO `authbank_cardbin` VALUES ('2525', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2526', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620027', '0');
INSERT INTO `authbank_cardbin` VALUES ('2527', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620031', '0');
INSERT INTO `authbank_cardbin` VALUES ('2528', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620039', '0');
INSERT INTO `authbank_cardbin` VALUES ('2529', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620103', '0');
INSERT INTO `authbank_cardbin` VALUES ('2530', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620106', '0');
INSERT INTO `authbank_cardbin` VALUES ('2531', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620120', '0');
INSERT INTO `authbank_cardbin` VALUES ('2532', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620123', '0');
INSERT INTO `authbank_cardbin` VALUES ('2533', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620125', '0');
INSERT INTO `authbank_cardbin` VALUES ('2534', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620220', '0');
INSERT INTO `authbank_cardbin` VALUES ('2535', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620278', '0');
INSERT INTO `authbank_cardbin` VALUES ('2536', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620812', '0');
INSERT INTO `authbank_cardbin` VALUES ('2537', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2538', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2539', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621012', '0');
INSERT INTO `authbank_cardbin` VALUES ('2540', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621020', '0');
INSERT INTO `authbank_cardbin` VALUES ('2541', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621023', '0');
INSERT INTO `authbank_cardbin` VALUES ('2542', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621025', '0');
INSERT INTO `authbank_cardbin` VALUES ('2543', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621027', '0');
INSERT INTO `authbank_cardbin` VALUES ('2544', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621031', '0');
INSERT INTO `authbank_cardbin` VALUES ('2545', 'BC卡公司', '26630410', '', 'BC-CUPGiftCard', '16', '6', '620132', '0');
INSERT INTO `authbank_cardbin` VALUES ('2546', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621039', '0');
INSERT INTO `authbank_cardbin` VALUES ('2547', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621078', '0');
INSERT INTO `authbank_cardbin` VALUES ('2548', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621220', '0');
INSERT INTO `authbank_cardbin` VALUES ('2549', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625003', '1');
INSERT INTO `authbank_cardbin` VALUES ('2550', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2551', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625011', '1');
INSERT INTO `authbank_cardbin` VALUES ('2552', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625012', '1');
INSERT INTO `authbank_cardbin` VALUES ('2553', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625020', '1');
INSERT INTO `authbank_cardbin` VALUES ('2554', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625023', '1');
INSERT INTO `authbank_cardbin` VALUES ('2555', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625025', '1');
INSERT INTO `authbank_cardbin` VALUES ('2556', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625027', '1');
INSERT INTO `authbank_cardbin` VALUES ('2557', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625031', '1');
INSERT INTO `authbank_cardbin` VALUES ('2558', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '621032', '0');
INSERT INTO `authbank_cardbin` VALUES ('2559', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625039', '1');
INSERT INTO `authbank_cardbin` VALUES ('2560', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625078', '1');
INSERT INTO `authbank_cardbin` VALUES ('2561', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625079', '1');
INSERT INTO `authbank_cardbin` VALUES ('2562', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625103', '1');
INSERT INTO `authbank_cardbin` VALUES ('2563', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625106', '1');
INSERT INTO `authbank_cardbin` VALUES ('2564', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625006', '1');
INSERT INTO `authbank_cardbin` VALUES ('2565', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625112', '1');
INSERT INTO `authbank_cardbin` VALUES ('2566', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625120', '1');
INSERT INTO `authbank_cardbin` VALUES ('2567', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625123', '1');
INSERT INTO `authbank_cardbin` VALUES ('2568', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625125', '1');
INSERT INTO `authbank_cardbin` VALUES ('2569', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625127', '1');
INSERT INTO `authbank_cardbin` VALUES ('2570', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625131', '1');
INSERT INTO `authbank_cardbin` VALUES ('2571', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625032', '1');
INSERT INTO `authbank_cardbin` VALUES ('2572', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625139', '1');
INSERT INTO `authbank_cardbin` VALUES ('2573', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625178', '1');
INSERT INTO `authbank_cardbin` VALUES ('2574', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625179', '1');
INSERT INTO `authbank_cardbin` VALUES ('2575', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625220', '1');
INSERT INTO `authbank_cardbin` VALUES ('2576', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625320', '1');
INSERT INTO `authbank_cardbin` VALUES ('2577', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625111', '1');
INSERT INTO `authbank_cardbin` VALUES ('2578', 'BC卡公司', '26630410', '', '中国通卡', '16', '6', '625132', '1');
INSERT INTO `authbank_cardbin` VALUES ('2579', 'BC卡公司', '26630410', '', '贷记卡', '16', '6', '625244', '1');
INSERT INTO `authbank_cardbin` VALUES ('2580', 'BC卡公司', '26630410', '', '贷记卡', '16', '6', '625243', '1');
INSERT INTO `authbank_cardbin` VALUES ('2581', 'BC卡公司', '26630410', '', '借记卡', '16', '6', '621484', '0');
INSERT INTO `authbank_cardbin` VALUES ('2582', 'BC卡公司', '26630410', '', '借记卡', '16', '6', '621640', '0');
INSERT INTO `authbank_cardbin` VALUES ('2583', 'BC卡公司', '26630410', '', 'BC Gwangju', '16', '6', '621641', '0');
INSERT INTO `authbank_cardbin` VALUES ('2584', 'BC卡公司', '26630410', '', 'BC Gwangju', '16', '6', '625245', '1');
INSERT INTO `authbank_cardbin` VALUES ('2585', 'BC卡公司', '26630410', '', 'BC Gwangju', '16', '6', '625246', '1');
INSERT INTO `authbank_cardbin` VALUES ('2586', '莫斯科人民储蓄银行', '26690643', '', 'cup-unioncard', '16', '6', '621040', '0');
INSERT INTO `authbank_cardbin` VALUES ('2587', '丝绸之路银行', '26700860', '', 'Classic/Gold', '16', '6', '621045', '0');
INSERT INTO `authbank_cardbin` VALUES ('2588', '俄罗斯远东商业银行', '26780643', '', '借记卡', '16', '6', '621264', '0');
INSERT INTO `authbank_cardbin` VALUES ('2589', 'CSC', '26790422', '', '贷记卡', '16', '6', '622356', '1');
INSERT INTO `authbank_cardbin` VALUES ('2590', 'CSC', '26790422', '', 'CSC借记卡', '16', '6', '621234', '0');
INSERT INTO `authbank_cardbin` VALUES ('2591', 'CSC', '26790422', '', 'EximCard', '16', '8', '62349550', '2');
INSERT INTO `authbank_cardbin` VALUES ('2592', 'AlliedBank', '26930608', '', '贷记卡', '16', '6', '622145', '1');
INSERT INTO `authbank_cardbin` VALUES ('2593', 'AlliedBank', '26930608', '', '贷记卡', '16', '6', '625013', '1');
INSERT INTO `authbank_cardbin` VALUES ('2594', '日本三菱信用卡公司', '27090392', '', '贷记卡', '16', '6', '622130', '1');
INSERT INTO `authbank_cardbin` VALUES ('2595', 'BaiduriBankBerhad', '27130096', '', '借记卡', '16', '6', '621257', '0');
INSERT INTO `authbank_cardbin` VALUES ('2596', '越南西贡商业银行', '27200704', '', '借记卡', '16', '6', '621055', '0');
INSERT INTO `authbank_cardbin` VALUES ('2597', '越南西贡商业银行', '27200704', '', '预付卡', '16', '6', '620009', '2');
INSERT INTO `authbank_cardbin` VALUES ('2598', '越南西贡商业银行', '27200704', '', '贷记卡', '16', '6', '625002', '1');
INSERT INTO `authbank_cardbin` VALUES ('2599', '菲律宾BDO', '27240608', '', '银联卡', '16', '6', '625033', '1');
INSERT INTO `authbank_cardbin` VALUES ('2600', '菲律宾BDO', '27240608', '', '银联卡', '16', '6', '625035', '1');
INSERT INTO `authbank_cardbin` VALUES ('2601', '菲律宾RCBC', '27250608', '', '贷记卡', '16', '6', '625007', '1');
INSERT INTO `authbank_cardbin` VALUES ('2602', '新加坡星网电子付款私人有限公司', '27520702', '', '预付卡', '16', '6', '620015', '2');
INSERT INTO `authbank_cardbin` VALUES ('2603', 'RoyalBankOpenStockCompany', '27550031', '', '预付卡', '16', '6', '620024', '2');
INSERT INTO `authbank_cardbin` VALUES ('2604', 'RoyalBankOpenStockCompany', '27550031', '', '贷记卡', '16', '6', '625004', '1');
INSERT INTO `authbank_cardbin` VALUES ('2605', 'RoyalBankOpenStockCompany', '27550031', '', '借记卡', '19', '6', '621344', '0');
INSERT INTO `authbank_cardbin` VALUES ('2606', '乌兹别克斯坦INFINBANK', '27650860', '', '借记卡', '16', '6', '621349', '0');
INSERT INTO `authbank_cardbin` VALUES ('2607', 'RussianStandardBank', '27670643', '', '预付卡', '16', '6', '620108', '2');
INSERT INTO `authbank_cardbin` VALUES ('2608', 'RussianStandardBank', '27670643', '', 'UnionPay', '16', '7', '6216846', '0');
INSERT INTO `authbank_cardbin` VALUES ('2609', 'RussianStandardBank', '27670643', '', 'UnionPay', '16', '7', '6216848', '0');
INSERT INTO `authbank_cardbin` VALUES ('2610', 'RussianStandardBank', '27670643', '', 'UnionPay', '16', '7', '6250386', '1');
INSERT INTO `authbank_cardbin` VALUES ('2611', 'RussianStandardBank', '27670643', '', 'UnionPay', '16', '7', '6250388', '1');
INSERT INTO `authbank_cardbin` VALUES ('2612', 'RussianStandardBank', '27670643', '', '预付卡', '16', '7', '6201086', '2');
INSERT INTO `authbank_cardbin` VALUES ('2613', 'RussianStandardBank', '27670643', '', '预付卡', '16', '7', '6201088', '2');
INSERT INTO `authbank_cardbin` VALUES ('2614', 'BCEL', '27710418', '', '借记卡', '16', '6', '621354', '0');
INSERT INTO `authbank_cardbin` VALUES ('2615', '澳门BDA', '27860446', '', '汇业卡', '19', '6', '621274', '0');
INSERT INTO `authbank_cardbin` VALUES ('2616', '澳门BDA', '27860446', '', '汇业卡', '19', '6', '621324', '0');
INSERT INTO `authbank_cardbin` VALUES ('2617', '澳门通股份有限公司', '28020446', '', '双币闪付卡', '16', '6', '620532', '2');
INSERT INTO `authbank_cardbin` VALUES ('2618', '澳门通股份有限公司', '28020446', '', '旅游卡', '19', '6', '620126', '2');
INSERT INTO `authbank_cardbin` VALUES ('2619', '澳门通股份有限公司', '28020446', '', '旅游卡', '19', '6', '620537', '2');
INSERT INTO `authbank_cardbin` VALUES ('2620', '韩国乐天', '28030410', '', '贷记卡', '16', '6', '625904', '1');
INSERT INTO `authbank_cardbin` VALUES ('2621', '巴基斯坦FAYSALBANK', '28040586', '', '借记卡', '16', '6', '621645', '0');
INSERT INTO `authbank_cardbin` VALUES ('2622', 'OJSCBASIAALLIANCEBANK', '28160860', '', 'UnionPay', '16', '6', '621624', '0');
INSERT INTO `authbank_cardbin` VALUES ('2623', 'CambodiaMekongBankPL', '28250116', '', '借记卡', '16', '6', '623332', '0');
INSERT INTO `authbank_cardbin` VALUES ('2624', 'CambodiaMekongBankPL', '28250116', '', '贷记卡', '16', '6', '624338', '1');
INSERT INTO `authbank_cardbin` VALUES ('2625', 'OJSCRussianInvestmentBank', '28260417', '', '借记卡', '16', '6', '623339', '0');
INSERT INTO `authbank_cardbin` VALUES ('2626', '俄罗斯ORIENTEXPRESSBANK', '28450643', '', '信用卡', '16', '6', '625104', '1');
INSERT INTO `authbank_cardbin` VALUES ('2627', '俄罗斯ORIENTEXPRESSBANK', '28450643', '', '借记卡', '16', '6', '621647', '0');
INSERT INTO `authbank_cardbin` VALUES ('2628', 'MongoliaTradeDevelop.Bank', '28530496', '', '普卡/金卡', '16', '6', '621642', '0');
INSERT INTO `authbank_cardbin` VALUES ('2629', 'KrungThajBankPublicCo.Ltd', '28550764', '', '借记卡', '16', '6', '621654', '0');
INSERT INTO `authbank_cardbin` VALUES ('2630', '韩国KB', '28590410', '', '贷记卡', '16', '6', '625804', '1');
INSERT INTO `authbank_cardbin` VALUES ('2631', '韩国三星卡公司', '28660410', '', '三星卡', '16', '6', '625814', '1');
INSERT INTO `authbank_cardbin` VALUES ('2632', '韩国三星卡公司', '28660410', '', '三星卡', '16', '6', '625817', '1');
INSERT INTO `authbank_cardbin` VALUES ('2633', 'CJSCFononbank', '28720762', '', 'Fonon Bank Card', '16', '6', '621649', '0');
INSERT INTO `authbank_cardbin` VALUES ('2634', 'CRDBBANKPLC', '28730834', '', '', '16', '6', '623316', '0');
INSERT INTO `authbank_cardbin` VALUES ('2635', 'CRDBBANKPLC', '28730834', '', '借记卡', '16', '6', '623317', '0');
INSERT INTO `authbank_cardbin` VALUES ('2636', 'CommercialBankofDubai', '28790784', '', 'PrepaidCard', '16', '6', '620079', '0');
INSERT INTO `authbank_cardbin` VALUES ('2637', 'CommercialBankofDubai', '28790784', '', 'PrepaidCard', '16', '6', '620091', '0');
INSERT INTO `authbank_cardbin` VALUES ('2638', 'TheBancorpBank', '28880840', '', 'UnionPay Travel Card', '16', '6', '620105', '2');
INSERT INTO `authbank_cardbin` VALUES ('2639', 'TheBancorpBank', '28880840', '', 'China UnionPay Travel Card', '16', '6', '622164', '2');
INSERT INTO `authbank_cardbin` VALUES ('2640', '巴基斯坦HabibBank', '28990586', '', '借记卡', '16', '6', '621657', '0');
INSERT INTO `authbank_cardbin` VALUES ('2641', '新韩卡公司', '29010410', '', '借记卡', '16', '6', '623024', '0');
INSERT INTO `authbank_cardbin` VALUES ('2642', '新韩卡公司', '29010410', '', '贷记卡', '16', '6', '625840', '1');
INSERT INTO `authbank_cardbin` VALUES ('2643', '新韩卡公司', '29010410', '', '贷记卡', '16', '6', '625841', '1');
INSERT INTO `authbank_cardbin` VALUES ('2644', 'PhongsavanhBankLimited', '29110418', '', 'PSVBUPIDEBIT', '16', '6', '621794', '0');
INSERT INTO `authbank_cardbin` VALUES ('2645', 'PhongsavanhBankLimited', '29110418', '', 'PSVBUPICREDIT', '16', '6', '625944', '1');
INSERT INTO `authbank_cardbin` VALUES ('2646', 'CapitalBankofMongolia', '29120496', '', '借记卡', '16', '6', '621694', '0');
INSERT INTO `authbank_cardbin` VALUES ('2647', 'JSCLibertyBank', '29140268', '', 'Classic', '16', '7', '6233451', '0');
INSERT INTO `authbank_cardbin` VALUES ('2648', 'JSCLibertyBank', '29140268', '', 'Gold', '16', '7', '6233452', '0');
INSERT INTO `authbank_cardbin` VALUES ('2649', 'JSCLibertyBank', '29140268', '', 'Diamond', '16', '6', '623347', '0');
INSERT INTO `authbank_cardbin` VALUES ('2650', 'JSCLibertyBank', '29140268', '', 'Classic', '16', '7', '6233453', '0');
INSERT INTO `authbank_cardbin` VALUES ('2651', 'TheMauritiusCommercialBank', '29170480', '', '预付卡', '16', '6', '620129', '0');
INSERT INTO `authbank_cardbin` VALUES ('2652', '格鲁吉亚InvestBank', '29230268', '', '借记卡', '16', '6', '621301', '0');
INSERT INTO `authbank_cardbin` VALUES ('2653', 'CimFinanceLtd', '29440480', '', '贷记卡', '16', '6', '624306', '1');
INSERT INTO `authbank_cardbin` VALUES ('2654', 'CimFinanceLtd', '29440480', '', '贷记卡', '16', '6', '624322', '1');
INSERT INTO `authbank_cardbin` VALUES ('2655', 'RawbankS.a.r.l', '29460180', '', '预付卡', '16', '6', '623300', '2');
INSERT INTO `authbank_cardbin` VALUES ('2656', 'PVBCardCorporation', '29470608', '', '预付卡', '16', '6', '623302', '2');
INSERT INTO `authbank_cardbin` VALUES ('2657', 'PVBCardCorporation', '29470608', '', '预付卡', '16', '6', '623303', '2');
INSERT INTO `authbank_cardbin` VALUES ('2658', 'PVBCardCorporation', '29470608', '', '预付卡', '16', '6', '623304', '0');
INSERT INTO `authbank_cardbin` VALUES ('2659', 'PVBCardCorporation', '29470608', '', '预付卡', '16', '6', '623324', '0');
INSERT INTO `authbank_cardbin` VALUES ('2660', 'UMicrofinanceBankLimited', '29600586', '', 'U Paisa ATM &Debit Card', '16', '6', '623307', '0');
INSERT INTO `authbank_cardbin` VALUES ('2661', 'EcobankNigeria', '29620566', '', 'Prepaid Card', '16', '6', '623311', '2');
INSERT INTO `authbank_cardbin` VALUES ('2662', 'AlBarakaBank(Pakistan)', '29630586', '', 'al baraka classic card', '16', '6', '623312', '0');
INSERT INTO `authbank_cardbin` VALUES ('2663', 'OJSCHamkorbank', '29640860', '', '借记卡', '16', '6', '623313', '0');
INSERT INTO `authbank_cardbin` VALUES ('2664', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '623323', '0');
INSERT INTO `authbank_cardbin` VALUES ('2665', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '623341', '0');
INSERT INTO `authbank_cardbin` VALUES ('2666', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '624320', '1');
INSERT INTO `authbank_cardbin` VALUES ('2667', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '624321', '1');
INSERT INTO `authbank_cardbin` VALUES ('2668', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '624324', '1');
INSERT INTO `authbank_cardbin` VALUES ('2669', 'NongHyupBank', '29650410', '', 'NH Card', '16', '6', '624325', '1');
INSERT INTO `authbank_cardbin` VALUES ('2670', 'FidelityBankPlc', '29660566', '', '借记卡', '16', '6', '623314', '0');
INSERT INTO `authbank_cardbin` VALUES ('2671', 'StateBankofMauritius', '29810480', '', 'Prepaid card', '16', '6', '623331', '2');
INSERT INTO `authbank_cardbin` VALUES ('2672', 'StateBankofMauritius', '29810480', '', 'Debit Card', '16', '6', '623348', '0');
INSERT INTO `authbank_cardbin` VALUES ('2673', 'JSCATFBank', '29830398', '', '预付卡', '16', '6', '623336', '0');
INSERT INTO `authbank_cardbin` VALUES ('2674', 'JSCATFBank', '29830398', '', '借记卡', '16', '6', '623337', '0');
INSERT INTO `authbank_cardbin` VALUES ('2675', 'JSCATFBank', '29830398', '', '借记卡', '16', '6', '623338', '0');
INSERT INTO `authbank_cardbin` VALUES ('2676', 'JSCATFBank', '29830398', '', '贷记卡', '16', '6', '624323', '1');
INSERT INTO `authbank_cardbin` VALUES ('2677', 'Statebank', '29920496', '', 'Debit/Classic', '16', '8', '62335201', '0');
INSERT INTO `authbank_cardbin` VALUES ('2678', 'Statebank', '29920496', '', 'Debit/Gold', '16', '8', '62335202', '0');
INSERT INTO `authbank_cardbin` VALUES ('2679', 'Statebank', '29920496', '', 'Debit/Platinum', '16', '8', '62335203', '0');
INSERT INTO `authbank_cardbin` VALUES ('2680', 'HimalayanBankLimited', '30020524', '', 'CUP Prepaid IC Card', '16', '6', '623499', '2');
INSERT INTO `authbank_cardbin` VALUES ('2681', 'CJSC“SpitamenBank”', '30030762', '', 'classic', '16', '8', '62335101', '0');
INSERT INTO `authbank_cardbin` VALUES ('2682', 'CJSC“SpitamenBank”', '30030762', '', 'gold', '16', '8', '62335102', '0');
INSERT INTO `authbank_cardbin` VALUES ('2683', 'CJSC“SpitamenBank”', '30030762', '', 'platinum', '16', '8', '62335103', '0');
INSERT INTO `authbank_cardbin` VALUES ('2684', 'CJSC“SpitamenBank”', '30030762', '', 'diamond', '16', '8', '62335104', '0');
INSERT INTO `authbank_cardbin` VALUES ('2685', 'CJSC“SpitamenBank”', '30030762', '', 'classic', '16', '8', '62335105', '0');
INSERT INTO `authbank_cardbin` VALUES ('2686', 'CJSC“SpitamenBank”', '30030762', '', 'gold', '16', '8', '62335106', '0');
INSERT INTO `authbank_cardbin` VALUES ('2687', 'CJSC“SpitamenBank”', '30030762', '', 'platinum', '16', '8', '62335107', '0');
INSERT INTO `authbank_cardbin` VALUES ('2688', 'CJSC“SpitamenBank”', '30030762', '', 'diamond', '16', '8', '62335108', '0');
INSERT INTO `authbank_cardbin` VALUES ('2689', 'Co-OperativeBankLimited', '30090104', '', 'EASI Travel Prepaid', '16', '6', '623493', '2');
INSERT INTO `authbank_cardbin` VALUES ('2690', '中国银行香港有限公司', '47980344', 'BOC', '人民币信用卡金卡', '16', '6', '622346', '1');
INSERT INTO `authbank_cardbin` VALUES ('2691', '中国银行香港有限公司', '47980344', 'BOC', '信用卡普通卡', '16', '6', '622347', '1');
INSERT INTO `authbank_cardbin` VALUES ('2692', '中国银行香港有限公司', '47980344', 'BOC', '中银卡(人民币)', '16', '6', '622348', '0');
INSERT INTO `authbank_cardbin` VALUES ('2693', '南洋商业银行', '47980344', '', '人民币信用卡金卡', '16', '6', '622349', '1');
INSERT INTO `authbank_cardbin` VALUES ('2694', '南洋商业银行', '47980344', '', '信用卡普通卡', '16', '6', '622350', '1');
INSERT INTO `authbank_cardbin` VALUES ('2695', '集友银行', '47980344', '', '人民币信用卡金卡', '16', '6', '622352', '1');
INSERT INTO `authbank_cardbin` VALUES ('2696', '集友银行', '47980344', '', '信用卡普通卡', '16', '6', '622353', '1');
INSERT INTO `authbank_cardbin` VALUES ('2697', '集友银行', '47980344', '', '中银卡', '16', '6', '622355', '0');
INSERT INTO `authbank_cardbin` VALUES ('2698', '中国银行(香港)', '47980344', 'BOC', '银联港币借记卡', '19', '6', '621041', '0');
INSERT INTO `authbank_cardbin` VALUES ('2699', '南洋商业银行', '47980344', '', '中银卡(人民币)', '16', '6', '622351', '0');
INSERT INTO `authbank_cardbin` VALUES ('2700', '宁夏黄河农村商业银行', '48028702', '', '黄河公务卡', '16', '6', '628326', '1');
INSERT INTO `authbank_cardbin` VALUES ('2701', '中银通商务支付有限公司', '48080000', '', '预付卡', '16', '6', '620048', '2');
INSERT INTO `authbank_cardbin` VALUES ('2702', '中银通商务支付有限公司', '48080000', '', '预付卡', '16', '6', '620515', '2');
INSERT INTO `authbank_cardbin` VALUES ('2703', '中银通商务支付有限公司', '48080000', '', '预付卡', '16', '6', '920000', '2');
INSERT INTO `authbank_cardbin` VALUES ('2704', '中银通商务支付有限公司', '48080000', '', '', '19', '6', '620550', '2');
INSERT INTO `authbank_cardbin` VALUES ('2705', '中银通商务支付有限公司', '48080000', '', '', '19', '6', '621563', '2');
INSERT INTO `authbank_cardbin` VALUES ('2706', '中银通商务支付有限公司', '48080000', '', '', '19', '6', '921001', '2');
INSERT INTO `authbank_cardbin` VALUES ('2707', '中银通商务支付有限公司', '48080000', '', '', '19', '6', '921002', '2');
INSERT INTO `authbank_cardbin` VALUES ('2708', '中银通支付', '48080001', '', '安徽合肥通卡', '19', '6', '921000', '2');
INSERT INTO `authbank_cardbin` VALUES ('2709', '中银通商务支付有限公司', '48100000', '', '铁路卡', '19', '6', '620038', '2');
INSERT INTO `authbank_cardbin` VALUES ('2710', '中国邮政储蓄银行信用卡中心', '61000000', 'POST', '银联标准白金卡', '16', '6', '622812', '1');
INSERT INTO `authbank_cardbin` VALUES ('2711', '中国邮政储蓄银行信用卡中心', '61000000', 'POST', '银联标准贷记卡', '16', '6', '622810', '1');
INSERT INTO `authbank_cardbin` VALUES ('2712', '中国邮政储蓄银行信用卡中心', '61000000', 'POST', '银联标准贷记卡', '16', '6', '622811', '1');
INSERT INTO `authbank_cardbin` VALUES ('2713', '中国邮政储蓄银行信用卡中心', '61000000', 'POST', '银联标准公务卡', '16', '6', '628310', '1');
INSERT INTO `authbank_cardbin` VALUES ('2714', '中国邮政储蓄银行信用卡中心', '61000000', 'POST', '上海购物信用卡', '16', '6', '625919', '1');
INSERT INTO `authbank_cardbin` VALUES ('2715', '中信银行', '63020000', 'ECITIC', '中信贷记卡银联卡', '15', '6', '376968', '1');
INSERT INTO `authbank_cardbin` VALUES ('2716', '中信银行', '63020000', 'ECITIC', '中信贷记卡银联卡', '15', '6', '376969', '1');
INSERT INTO `authbank_cardbin` VALUES ('2717', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '400360', '1');
INSERT INTO `authbank_cardbin` VALUES ('2718', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '403391', '1');
INSERT INTO `authbank_cardbin` VALUES ('2719', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '403392', '1');
INSERT INTO `authbank_cardbin` VALUES ('2720', '中信银行', '63020000', 'ECITIC', '中信贷记卡银联卡', '15', '6', '376966', '1');
INSERT INTO `authbank_cardbin` VALUES ('2721', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404158', '1');
INSERT INTO `authbank_cardbin` VALUES ('2722', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404159', '1');
INSERT INTO `authbank_cardbin` VALUES ('2723', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404171', '1');
INSERT INTO `authbank_cardbin` VALUES ('2724', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404172', '1');
INSERT INTO `authbank_cardbin` VALUES ('2725', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404173', '1');
INSERT INTO `authbank_cardbin` VALUES ('2726', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404174', '1');
INSERT INTO `authbank_cardbin` VALUES ('2727', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '404157', '1');
INSERT INTO `authbank_cardbin` VALUES ('2728', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '433667', '1');
INSERT INTO `authbank_cardbin` VALUES ('2729', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '433668', '1');
INSERT INTO `authbank_cardbin` VALUES ('2730', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '433669', '1');
INSERT INTO `authbank_cardbin` VALUES ('2731', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '514906', '1');
INSERT INTO `authbank_cardbin` VALUES ('2732', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '403393', '1');
INSERT INTO `authbank_cardbin` VALUES ('2733', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '520108', '1');
INSERT INTO `authbank_cardbin` VALUES ('2734', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '433666', '1');
INSERT INTO `authbank_cardbin` VALUES ('2735', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '558916', '1');
INSERT INTO `authbank_cardbin` VALUES ('2736', '中信银行', '63020000', 'ECITIC', '中信银联标准贷记卡', '16', '6', '622678', '1');
INSERT INTO `authbank_cardbin` VALUES ('2737', '中信银行', '63020000', 'ECITIC', '中信银联标准贷记卡', '16', '6', '622679', '1');
INSERT INTO `authbank_cardbin` VALUES ('2738', '中信银行', '63020000', 'ECITIC', '中信银联标准贷记卡', '16', '6', '622680', '1');
INSERT INTO `authbank_cardbin` VALUES ('2739', '中信银行', '63020000', 'ECITIC', '中信银联标准贷记卡', '16', '6', '622688', '1');
INSERT INTO `authbank_cardbin` VALUES ('2740', '中信银行', '63020000', 'ECITIC', '中信银联标准贷记卡', '16', '6', '622689', '1');
INSERT INTO `authbank_cardbin` VALUES ('2741', '中信银行', '63020000', 'ECITIC', '中信银联公务卡', '16', '6', '628206', '1');
INSERT INTO `authbank_cardbin` VALUES ('2742', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '556617', '1');
INSERT INTO `authbank_cardbin` VALUES ('2743', '中信银行', '63020000', 'ECITIC', '中信银联公务卡', '16', '6', '628209', '1');
INSERT INTO `authbank_cardbin` VALUES ('2744', '中信银行', '63020000', 'ECITIC', '中信贷记卡', '16', '6', '518212', '1');
INSERT INTO `authbank_cardbin` VALUES ('2745', '中信银行', '63020000', 'ECITIC', '中信银联公务卡', '16', '6', '628208', '1');
INSERT INTO `authbank_cardbin` VALUES ('2746', '中信银行', '63020000', 'ECITIC', '中信JCB美元卡', '16', '6', '356390', '1');
INSERT INTO `authbank_cardbin` VALUES ('2747', '中信银行', '63020000', 'ECITIC', '中信JCB美元卡', '16', '6', '356391', '1');
INSERT INTO `authbank_cardbin` VALUES ('2748', '中信银行', '63020000', 'ECITIC', '中信JCB美元卡', '16', '6', '356392', '1');
INSERT INTO `authbank_cardbin` VALUES ('2749', '中信银行', '63020000', 'ECITIC', '中信银联IC卡普卡', '16', '6', '622916', '1');
INSERT INTO `authbank_cardbin` VALUES ('2750', '中信银行', '63020000', 'ECITIC', '中信银联IC卡金卡', '16', '6', '622918', '1');
INSERT INTO `authbank_cardbin` VALUES ('2751', '中信银行', '63020000', 'ECITIC', '中信银联IC卡白金卡', '16', '6', '622919', '1');
INSERT INTO `authbank_cardbin` VALUES ('2752', '中信银行', '63020000', 'ECITIC', '公务IC普卡', '16', '6', '628370', '1');
INSERT INTO `authbank_cardbin` VALUES ('2753', '中信银行', '63020000', 'ECITIC', '公务IC金卡', '16', '6', '628371', '1');
INSERT INTO `authbank_cardbin` VALUES ('2754', '中信银行', '63020000', 'ECITIC', '公务IC白金卡', '16', '6', '628372', '1');
INSERT INTO `authbank_cardbin` VALUES ('2755', '光大银行', '63030000', 'CEB', '存贷合一白金卡', '16', '6', '622657', '1');
INSERT INTO `authbank_cardbin` VALUES ('2756', '光大银行', '63030000', 'CEB', '存贷合一卡普卡', '16', '6', '622685', '1');
INSERT INTO `authbank_cardbin` VALUES ('2757', '光大银行', '63030000', 'CEB', '理财信用卡', '16', '6', '622659', '1');
INSERT INTO `authbank_cardbin` VALUES ('2758', '中国光大银行', '63030000', 'CEB', '存贷合一钻石卡', '16', '6', '622687', '1');
INSERT INTO `authbank_cardbin` VALUES ('2759', '中国光大银行', '63030000', 'CEB', '存贷合一IC卡', '16', '6', '625981', '1');
INSERT INTO `authbank_cardbin` VALUES ('2760', '中国光大银行', '63030000', 'CEB', '存贷合一IC卡', '16', '6', '625979', '1');
INSERT INTO `authbank_cardbin` VALUES ('2761', '中国光大银行', '63030000', 'CEB', '阳光商旅信用卡', '16', '6', '356839', '1');
INSERT INTO `authbank_cardbin` VALUES ('2762', '中国光大银行', '63030000', 'CEB', '阳光商旅信用卡', '16', '6', '356840', '1');
INSERT INTO `authbank_cardbin` VALUES ('2763', '中国光大银行', '63030000', 'CEB', '阳光信用卡(银联', '16', '6', '406252', '1');
INSERT INTO `authbank_cardbin` VALUES ('2764', '中国光大银行', '63030000', 'CEB', '阳光信用卡(银联', '16', '6', '406254', '1');
INSERT INTO `authbank_cardbin` VALUES ('2765', '中国光大银行', '63030000', 'CEB', '阳光商旅信用卡', '16', '6', '425862', '1');
INSERT INTO `authbank_cardbin` VALUES ('2766', '中国光大银行', '63030000', 'CEB', '阳光白金信用卡', '16', '6', '481699', '1');
INSERT INTO `authbank_cardbin` VALUES ('2767', '中国光大银行', '63030000', 'CEB', '安邦俱乐部信用卡', '16', '6', '524090', '1');
INSERT INTO `authbank_cardbin` VALUES ('2768', '中国光大银行', '63030000', 'CEB', '足球锦标赛纪念卡', '16', '6', '543159', '1');
INSERT INTO `authbank_cardbin` VALUES ('2769', '中国光大银行', '63030000', 'CEB', '光大银行联名公务卡', '16', '6', '622161', '1');
INSERT INTO `authbank_cardbin` VALUES ('2770', '中国光大银行', '63030000', 'CEB', '积分卡', '16', '6', '622570', '1');
INSERT INTO `authbank_cardbin` VALUES ('2771', '中国光大银行', '63030000', 'CEB', '炎黄卡普卡', '16', '6', '622650', '1');
INSERT INTO `authbank_cardbin` VALUES ('2772', '中国光大银行', '63030000', 'CEB', '炎黄卡白金卡', '16', '6', '622655', '1');
INSERT INTO `authbank_cardbin` VALUES ('2773', '中国光大银行', '63030000', 'CEB', '炎黄卡金卡', '16', '6', '622658', '1');
INSERT INTO `authbank_cardbin` VALUES ('2774', '中国光大银行', '63030000', 'CEB', '贷记IC卡', '16', '6', '625975', '1');
INSERT INTO `authbank_cardbin` VALUES ('2775', '中国光大银行', '63030000', 'CEB', '贷记IC卡', '16', '6', '625977', '1');
INSERT INTO `authbank_cardbin` VALUES ('2776', '中国光大银行', '63030000', 'CEB', '银联公务卡', '16', '6', '628201', '1');
INSERT INTO `authbank_cardbin` VALUES ('2777', '中国光大银行', '63030000', 'CEB', '银联公务卡', '16', '6', '628202', '1');
INSERT INTO `authbank_cardbin` VALUES ('2778', '中国光大银行', '63030000', 'CEB', '贷记IC卡', '16', '6', '625976', '1');
INSERT INTO `authbank_cardbin` VALUES ('2779', '中国光大银行', '63030000', 'CEB', '银联贷记IC旅游卡', '16', '6', '625339', '1');
INSERT INTO `authbank_cardbin` VALUES ('2780', '中国光大银行', '63030000', 'CEB', '贷记IC卡', '16', '6', '622801', '1');
INSERT INTO `authbank_cardbin` VALUES ('2781', '中国光大银行', '63030000', 'CEB', '存贷合一IC卡', '16', '6', '625978', '1');
INSERT INTO `authbank_cardbin` VALUES ('2782', '中国光大银行', '63030000', 'CEB', '存贷合一IC卡', '16', '6', '625980', '1');
INSERT INTO `authbank_cardbin` VALUES ('2783', '华夏银行', '63040000', 'HXB', '华夏万事达信用卡', '16', '6', '523959', '1');
INSERT INTO `authbank_cardbin` VALUES ('2784', '华夏银行', '63040000', 'HXB', '万事达信用卡金卡', '16', '6', '528709', '1');
INSERT INTO `authbank_cardbin` VALUES ('2785', '华夏银行', '63040000', 'HXB', '万事达普卡', '16', '6', '539867', '1');
INSERT INTO `authbank_cardbin` VALUES ('2786', '华夏银行', '63040000', 'HXB', '万事达普卡', '16', '6', '539868', '1');
INSERT INTO `authbank_cardbin` VALUES ('2787', '华夏银行', '63040000', 'HXB', '华夏信用卡金卡', '16', '6', '622637', '1');
INSERT INTO `authbank_cardbin` VALUES ('2788', '华夏银行', '63040000', 'HXB', '华夏白金卡', '16', '6', '622638', '1');
INSERT INTO `authbank_cardbin` VALUES ('2789', '华夏银行', '63040000', 'HXB', '华夏公务信用卡', '16', '6', '628318', '1');
INSERT INTO `authbank_cardbin` VALUES ('2790', '华夏银行', '63040000', 'HXB', '万事达信用卡金卡', '16', '6', '528708', '1');
INSERT INTO `authbank_cardbin` VALUES ('2791', '华夏银行', '63040000', 'HXB', '华夏信用卡普卡', '16', '6', '622636', '1');
INSERT INTO `authbank_cardbin` VALUES ('2792', '华夏银行', '63040000', 'HXB', '华夏标准金融IC信用卡', '16', '6', '625967', '1');
INSERT INTO `authbank_cardbin` VALUES ('2793', '华夏银行', '63040000', 'HXB', '华夏标准金融IC信用卡', '16', '6', '625968', '1');
INSERT INTO `authbank_cardbin` VALUES ('2794', '华夏银行', '63040000', 'HXB', '华夏标准金融IC信用卡', '16', '6', '625969', '1');
INSERT INTO `authbank_cardbin` VALUES ('2795', '浦发银行信用卡中心', '63100000', '', '移动浦发借贷合一联名卡', '16', '6', '625971', '1');
INSERT INTO `authbank_cardbin` VALUES ('2796', '浦发银行信用卡中心', '63100000', '', '贷记卡', '16', '6', '625970', '1');
INSERT INTO `authbank_cardbin` VALUES ('2797', '浦发银行信用卡中心', '63100000', '', '浦发私人银行信用卡', '15', '6', '377187', '1');
INSERT INTO `authbank_cardbin` VALUES ('2798', '浦发银行信用卡中心', '63100000', '', '中国移动浦发银行联名手机卡', '16', '6', '625831', '1');
INSERT INTO `authbank_cardbin` VALUES ('2799', '东亚银行(中国)有限公司', '63200000', '', '东亚银行普卡', '16', '6', '622265', '1');
INSERT INTO `authbank_cardbin` VALUES ('2800', '东亚银行(中国)有限公司', '63200000', '', '东亚银行金卡', '16', '6', '622266', '1');
INSERT INTO `authbank_cardbin` VALUES ('2801', '东亚银行(中国)有限公司', '63200000', '', '百家网点纪念版IC贷记卡', '16', '6', '625972', '1');
INSERT INTO `authbank_cardbin` VALUES ('2802', '东亚银行(中国)有限公司', '63200000', '', '百家网点纪念版IC贷记卡', '16', '6', '625973', '1');
INSERT INTO `authbank_cardbin` VALUES ('2803', '南洋商业银行', '63320000', '', '银联个人白金信用卡', '16', '6', '625093', '1');
INSERT INTO `authbank_cardbin` VALUES ('2804', '南洋商业银行', '63320000', '', '银联商务白金信用卡', '16', '6', '625095', '1');
INSERT INTO `authbank_cardbin` VALUES ('2805', '北京银行', '64031000', 'BOB', '万事达双币金卡', '16', '6', '522001', '1');
INSERT INTO `authbank_cardbin` VALUES ('2806', '北京银行', '64031000', 'BOB', '银联标准贷记卡', '16', '6', '622163', '1');
INSERT INTO `authbank_cardbin` VALUES ('2807', '北京银行', '64031000', 'BOB', '银联标准贷记卡', '16', '6', '622853', '1');
INSERT INTO `authbank_cardbin` VALUES ('2808', '北京银行', '64031000', 'BOB', '银联标准公务卡', '16', '6', '628203', '1');
INSERT INTO `authbank_cardbin` VALUES ('2809', '北京银行', '64031000', 'BOB', '北京银行中荷人寿联名卡', '16', '6', '622851', '1');
INSERT INTO `authbank_cardbin` VALUES ('2810', '北京银行', '64031000', 'BOB', '尊尚白金卡', '16', '6', '622852', '1');
INSERT INTO `authbank_cardbin` VALUES ('2811', '宁波银行', '64083300', '', '汇通贷记卡', '16', '6', '625903', '1');
INSERT INTO `authbank_cardbin` VALUES ('2812', '宁波银行', '64083300', '', '汇通白金卡', '16', '6', '622778', '1');
INSERT INTO `authbank_cardbin` VALUES ('2813', '宁波银行', '64083300', '', '汇通公务卡', '16', '6', '628207', '1');
INSERT INTO `authbank_cardbin` VALUES ('2814', '宁波银行', '64083300', '', '汇通贷记卡（IC）', '16', '6', '622282', '1');
INSERT INTO `authbank_cardbin` VALUES ('2815', '宁波银行', '64083300', '', '汇通贷记卡(IC)', '16', '6', '622318', '1');
INSERT INTO `authbank_cardbin` VALUES ('2816', '齐鲁银行股份有限公司', '64094510', '', '泉城公务卡', '16', '6', '628379', '1');
INSERT INTO `authbank_cardbin` VALUES ('2817', '广州银行股份有限公司', '64135810', '', '广州银行信用卡', '16', '6', '625050', '1');
INSERT INTO `authbank_cardbin` VALUES ('2818', '广州银行股份有限公司', '64135810', '', '贷记IC卡', '16', '6', '625836', '1');
INSERT INTO `authbank_cardbin` VALUES ('2819', '广州银行股份有限公司', '64135810', '', '银联标准公务卡', '16', '6', '628367', '1');
INSERT INTO `authbank_cardbin` VALUES ('2820', '龙江银行股份有限公司', '64162640', '', '公务卡', '16', '6', '628333', '1');
INSERT INTO `authbank_cardbin` VALUES ('2821', '河北银行股份有限公司', '64221210', '', '如意贷记卡', '16', '6', '622921', '1');
INSERT INTO `authbank_cardbin` VALUES ('2822', '河北银行股份有限公司', '64221210', '', '如意贷记卡', '16', '6', '628321', '1');
INSERT INTO `authbank_cardbin` VALUES ('2823', '河北银行股份有限公司', '64221210', '', '福农卡', '16', '6', '625598', '1');
INSERT INTO `authbank_cardbin` VALUES ('2824', '杭州市商业银行', '64233310', '', '西湖卡IC卡', '19', '6', '603367', '0');
INSERT INTO `authbank_cardbin` VALUES ('2825', '杭州市商业银行', '64233311', '', '西湖贷记卡', '16', '6', '622286', '1');
INSERT INTO `authbank_cardbin` VALUES ('2826', '杭州市商业银行', '64233311', '', '西湖贷记卡', '16', '6', '628236', '1');
INSERT INTO `authbank_cardbin` VALUES ('2827', '杭州市商业银行', '64233311', '', '西湖信用卡', '16', '6', '625800', '1');
INSERT INTO `authbank_cardbin` VALUES ('2828', '南京银行', '64243010', '', '借记IC卡', '16', '6', '621777', '0');
INSERT INTO `authbank_cardbin` VALUES ('2829', '成都市商业银行', '64296510', '', '银联标准公务卡', '16', '6', '628228', '1');
INSERT INTO `authbank_cardbin` VALUES ('2830', '成都市商业银行', '64296510', '', '银联标准卡', '16', '6', '622813', '1');
INSERT INTO `authbank_cardbin` VALUES ('2831', '成都市商业银行', '64296510', '', '银联标准卡', '16', '6', '622818', '1');
INSERT INTO `authbank_cardbin` VALUES ('2832', '临商银行', '64314730', '', '公务卡', '16', '6', '628359', '1');
INSERT INTO `authbank_cardbin` VALUES ('2833', '珠海华润银行', '64375850', '', '公务信用卡', '16', '6', '628270', '1');
INSERT INTO `authbank_cardbin` VALUES ('2834', '齐商银行', '64384530', '', '金达公务卡', '16', '6', '628311', '1');
INSERT INTO `authbank_cardbin` VALUES ('2835', '锦州银行', '64392270', '', '公务卡', '16', '6', '628261', '1');
INSERT INTO `authbank_cardbin` VALUES ('2836', '徽商银行', '64403600', '', '银联标准公务卡', '16', '6', '628251', '1');
INSERT INTO `authbank_cardbin` VALUES ('2837', '徽商银行', '64403600', '', '贷记卡', '16', '6', '622651', '1');
INSERT INTO `authbank_cardbin` VALUES ('2838', '徽商银行', '64403600', '', '贷记IC卡', '16', '6', '625828', '1');
INSERT INTO `authbank_cardbin` VALUES ('2839', '徽商银行', '64403600', '', '公司卡', '16', '6', '625652', '1');
INSERT INTO `authbank_cardbin` VALUES ('2840', '徽商银行', '64403600', '', '采购卡', '16', '6', '625700', '1');
INSERT INTO `authbank_cardbin` VALUES ('2841', '重庆银行股份有限公司', '64416910', '', '银联标准卡', '16', '6', '622613', '1');
INSERT INTO `authbank_cardbin` VALUES ('2842', '重庆银行股份有限公司', '64416910', '', '银联标准公务卡', '16', '6', '628220', '1');
INSERT INTO `authbank_cardbin` VALUES ('2843', '哈尔滨商行', '64422610', '', '丁香贷记卡', '16', '6', '622809', '1');
INSERT INTO `authbank_cardbin` VALUES ('2844', '哈尔滨商行', '64422610', '', '哈尔滨银行公务卡', '16', '6', '628224', '1');
INSERT INTO `authbank_cardbin` VALUES ('2845', '哈尔滨银行', '64422610', '', '联名卡', '16', '6', '625119', '1');
INSERT INTO `authbank_cardbin` VALUES ('2846', '哈尔滨银行', '64422610', '', '福农准贷记卡', '16', '6', '625577', '3');
INSERT INTO `authbank_cardbin` VALUES ('2847', '哈尔滨银行', '64422610', '', '贷记IC卡', '16', '6', '625952', '1');
INSERT INTO `authbank_cardbin` VALUES ('2848', '哈尔滨银行', '64422611', '', '金融IC借记卡', '19', '6', '621752', '0');
INSERT INTO `authbank_cardbin` VALUES ('2849', '贵阳银行股份有限公司', '64437010', '', '甲秀公务卡', '16', '6', '628213', '1');
INSERT INTO `authbank_cardbin` VALUES ('2850', '兰州银行', '64478210', '', '敦煌公务卡', '16', '6', '628263', '1');
INSERT INTO `authbank_cardbin` VALUES ('2851', '南昌银行', '64484210', '', '银联标准公务卡', '16', '6', '628305', '1');
INSERT INTO `authbank_cardbin` VALUES ('2852', '青岛银行', '64504520', '', '公务卡', '16', '6', '628239', '1');
INSERT INTO `authbank_cardbin` VALUES ('2853', '九江银行股份有限公司', '64544240', '', '庐山公务卡', '16', '6', '628238', '1');
INSERT INTO `authbank_cardbin` VALUES ('2854', '日照银行', '64554770', '', '黄海公务卡', '16', '6', '628257', '1');
INSERT INTO `authbank_cardbin` VALUES ('2855', '青海银行', '64588510', '', '三江贷记卡', '16', '6', '622817', '1');
INSERT INTO `authbank_cardbin` VALUES ('2856', '青海银行', '64588510', '', '三江贷记卡(公务卡)', '16', '6', '628287', '1');
INSERT INTO `authbank_cardbin` VALUES ('2857', '青海银行', '64588510', '', '三江贷记IC卡', '16', '6', '625959', '1');
INSERT INTO `authbank_cardbin` VALUES ('2858', '青海银行', '64588510', '', '中国旅游卡', '16', '8', '62536601', '1');
INSERT INTO `authbank_cardbin` VALUES ('2859', '江南农村商业银行', '64603040', '', '江南信用卡', '16', '6', '625129', '1');
INSERT INTO `authbank_cardbin` VALUES ('2860', '潍坊银行', '64624580', '', '鸢都公务卡', '16', '6', '628391', '1');
INSERT INTO `authbank_cardbin` VALUES ('2861', '赣州银行股份有限公司', '64634280', '', '长征公务卡', '16', '6', '628233', '1');
INSERT INTO `authbank_cardbin` VALUES ('2862', '富滇银行', '64667310', '', '富滇公务卡', '16', '6', '628231', '1');
INSERT INTO `authbank_cardbin` VALUES ('2863', '浙江泰隆商业银行', '64733450', '', '泰隆公务卡(单位卡)', '16', '6', '628275', '1');
INSERT INTO `authbank_cardbin` VALUES ('2864', '浙江泰隆商业银行', '64733450', '', '泰隆尊尚白金卡、钻石卡', '16', '6', '622565', '1');
INSERT INTO `authbank_cardbin` VALUES ('2865', '浙江泰隆商业银行', '64733450', '', '泰隆信用卡', '16', '6', '622287', '1');
INSERT INTO `authbank_cardbin` VALUES ('2866', '浙江泰隆商业银行', '64733450', '', '融易通', '16', '6', '622717', '3');
INSERT INTO `authbank_cardbin` VALUES ('2867', '内蒙古银行', '64741910', '', '银联标准公务卡', '16', '6', '628252', '1');
INSERT INTO `authbank_cardbin` VALUES ('2868', '湖州银行', '64753360', '', '公务卡', '16', '6', '628306', '1');
INSERT INTO `authbank_cardbin` VALUES ('2869', '广西北部湾银行', '64786110', '', '银联标准公务卡', '16', '6', '628227', '1');
INSERT INTO `authbank_cardbin` VALUES ('2870', '广西北部湾银行', '64786110', '', 'IC借记卡', '16', '6', '623001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2871', '威海市商业银行', '64814650', '', '通达公务卡', '16', '6', '628234', '1');
INSERT INTO `authbank_cardbin` VALUES ('2872', '广东南粤银行股份有限公司', '64895910', '', '湛江市民卡', '19', '6', '621727', '0');
INSERT INTO `authbank_cardbin` VALUES ('2873', '广东南粤银行股份有限公司', '64895910', '', '--', '19', '6', '623128', '0');
INSERT INTO `authbank_cardbin` VALUES ('2874', '广东南粤银行', '64895919', '', '公务卡', '16', '6', '628237', '1');
INSERT INTO `authbank_cardbin` VALUES ('2875', '桂林银行', '64916170', '', '漓江公务卡', '16', '6', '628219', '1');
INSERT INTO `authbank_cardbin` VALUES ('2876', '桂林银行', '64916170', '', '漓江卡', '17', '6', '621456', '0');
INSERT INTO `authbank_cardbin` VALUES ('2877', '桂林银行', '64916170', '', '福农IC卡', '19', '6', '621562', '0');
INSERT INTO `authbank_cardbin` VALUES ('2878', '龙江银行股份有限公司', '64922690', '', '玉兔贷记卡', '16', '6', '622270', '1');
INSERT INTO `authbank_cardbin` VALUES ('2879', '龙江银行股份有限公司', '64922690', '', '玉兔贷记卡(公务卡)', '16', '6', '628368', '1');
INSERT INTO `authbank_cardbin` VALUES ('2880', '龙江银行', '64922690', '', '福农准贷记卡', '16', '6', '625588', '3');
INSERT INTO `authbank_cardbin` VALUES ('2881', '龙江银行股份有限公司', '64922690', '', '联名贷记卡', '16', '6', '625090', '1');
INSERT INTO `authbank_cardbin` VALUES ('2882', '龙江银行股份有限公司', '64922690', '', '中国旅游卡', '16', '8', '62536602', '1');
INSERT INTO `authbank_cardbin` VALUES ('2883', '柳州银行', '64956140', '', '龙城公务卡', '16', '6', '628293', '1');
INSERT INTO `authbank_cardbin` VALUES ('2884', '上海农商银行贷记卡', '65012900', '', '鑫卡', '16', '6', '622611', '1');
INSERT INTO `authbank_cardbin` VALUES ('2885', '上海农商银行贷记卡', '65012900', '', '商务卡', '16', '6', '622722', '1');
INSERT INTO `authbank_cardbin` VALUES ('2886', '上海农商银行贷记卡', '65012900', '', '银联标准公务卡', '16', '6', '628211', '1');
INSERT INTO `authbank_cardbin` VALUES ('2887', '上海农商银行贷记卡', '65012900', '', '福农卡', '16', '6', '625500', '3');
INSERT INTO `authbank_cardbin` VALUES ('2888', '上海农商银行贷记卡', '65012900', '', '鑫通卡', '16', '6', '625989', '1');
INSERT INTO `authbank_cardbin` VALUES ('2889', '广州农村商业银行', '65055810', '', '太阳信用卡', '16', '6', '625080', '1');
INSERT INTO `authbank_cardbin` VALUES ('2890', '广州农村商业银行', '65055810', '', '公务卡', '16', '6', '628235', '1');
INSERT INTO `authbank_cardbin` VALUES ('2891', '广东顺德农村商业银行', '65085883', '', '恒通贷记卡（公务卡）', '16', '6', '628322', '1');
INSERT INTO `authbank_cardbin` VALUES ('2892', '广东顺德农村商业银行', '65085883', '', '恒通贷记卡', '16', '6', '625088', '1');
INSERT INTO `authbank_cardbin` VALUES ('2893', '云南省农村信用社', '65097300', '', '金碧贷记卡', '16', '6', '622469', '1');
INSERT INTO `authbank_cardbin` VALUES ('2894', '云南省农村信用社', '65097300', '', '金碧公务卡', '16', '6', '628307', '1');
INSERT INTO `authbank_cardbin` VALUES ('2895', '承德银行', '65131410', '', '热河公务卡', '16', '6', '628229', '1');
INSERT INTO `authbank_cardbin` VALUES ('2896', '德州银行', '65154680', '', '长河公务卡', '16', '6', '628397', '1');
INSERT INTO `authbank_cardbin` VALUES ('2897', '福建省农村信用社联合社', '65173900', '', '万通贷记卡', '16', '6', '622802', '1');
INSERT INTO `authbank_cardbin` VALUES ('2898', '福建省农村信用社联合社', '65173900', '', '福建海峡旅游卡', '16', '6', '622290', '1');
INSERT INTO `authbank_cardbin` VALUES ('2899', '福建省农村信用社联合社', '65173900', '', '万通贷记卡', '16', '6', '628232', '1');
INSERT INTO `authbank_cardbin` VALUES ('2900', '福建省农村信用社联合社', '65173900', '', '福万通贷记卡', '16', '6', '625128', '1');
INSERT INTO `authbank_cardbin` VALUES ('2901', '福建省农村信用社联合社', '65173900', '', '福万通万里行联名IC贷记卡', '16', '6', '625357', '1');
INSERT INTO `authbank_cardbin` VALUES ('2902', '天津农村商业银行', '65191100', '', '吉祥信用卡', '16', '6', '622829', '1');
INSERT INTO `authbank_cardbin` VALUES ('2903', '天津农村商业银行', '65191100', '', '贷记IC卡', '16', '6', '625819', '1');
INSERT INTO `authbank_cardbin` VALUES ('2904', '天津农村商业银行', '65191100', '', '吉祥信用卡', '16', '6', '628301', '1');
INSERT INTO `authbank_cardbin` VALUES ('2905', '成都农村商业银行股份有限公司', '65226510', '', '天府贷记卡', '16', '6', '622808', '1');
INSERT INTO `authbank_cardbin` VALUES ('2906', '成都农村商业银行股份有限公司', '65226510', '', '天府公务卡', '16', '6', '628308', '1');
INSERT INTO `authbank_cardbin` VALUES ('2907', '成都农村商业银行股份有限公司', '65226510', '', '天府借记卡', '19', '6', '623088', '0');
INSERT INTO `authbank_cardbin` VALUES ('2908', '江苏省农村信用社联合社', '65243000', '', '圆鼎贷记卡', '16', '6', '622815', '1');
INSERT INTO `authbank_cardbin` VALUES ('2909', '江苏省农村信用社联合社', '65243000', '', '圆鼎贷记卡', '16', '6', '622816', '1');
INSERT INTO `authbank_cardbin` VALUES ('2910', '江苏省农村信用社联合社', '65243000', '', '银联标准公务卡', '16', '6', '628226', '1');
INSERT INTO `authbank_cardbin` VALUES ('2911', '上饶银行', '65264330', '', '三清山公务卡', '16', '6', '628223', '1');
INSERT INTO `authbank_cardbin` VALUES ('2912', '上饶银行', '65264331', '', '三清山IC卡', '16', '6', '621416', '0');
INSERT INTO `authbank_cardbin` VALUES ('2913', '东营银行', '65274550', '', '财政公务卡', '16', '6', '628217', '1');
INSERT INTO `authbank_cardbin` VALUES ('2914', '临汾市尧都区农村信用合作联社', '65341770', '', '天河贷记公务卡', '16', '6', '628382', '1');
INSERT INTO `authbank_cardbin` VALUES ('2915', '临汾市尧都区农村信用合作联社', '65341770', '', '天河贷记卡', '16', '6', '625158', '1');
INSERT INTO `authbank_cardbin` VALUES ('2916', '无锡农村商业银行', '65373020', '', '金阿福贷记卡', '16', '6', '622569', '1');
INSERT INTO `authbank_cardbin` VALUES ('2917', '无锡农村商业银行', '65373020', '', '银联标准公务卡', '16', '6', '628369', '1');
INSERT INTO `authbank_cardbin` VALUES ('2918', '湖南农信', '65385500', '', '福祥贷记卡（福农卡）', '16', '6', '625506', '1');
INSERT INTO `authbank_cardbin` VALUES ('2919', '湖南农村信用社联合社', '65385500', '', '福祥贷记卡', '16', '6', '622906', '1');
INSERT INTO `authbank_cardbin` VALUES ('2920', '湖南农村信用社联合社', '65385500', '', '银联标准公务卡', '16', '6', '628386', '1');
INSERT INTO `authbank_cardbin` VALUES ('2921', '湖南省农村信用联合社', '65385500', '', '福农贷记卡', '16', '6', '625519', '1');
INSERT INTO `authbank_cardbin` VALUES ('2922', '江西省农村信用社联合社', '65394200', '', '百福公务卡', '16', '6', '628392', '1');
INSERT INTO `authbank_cardbin` VALUES ('2923', '江西省农村信用社联合社', '65394200', '', '借记IC卡', '16', '6', '623092', '0');
INSERT INTO `authbank_cardbin` VALUES ('2924', '安徽省农村信用社', '65473600', '', '金农卡', '19', '6', '621778', '0');
INSERT INTO `authbank_cardbin` VALUES ('2925', '邢台银行', '65541310', '', '金牛市民卡', '19', '6', '620528', '0');
INSERT INTO `authbank_cardbin` VALUES ('2926', '武汉农村商业银行', '65595210', '', '汉卡', '16', '6', '625156', '1');
INSERT INTO `authbank_cardbin` VALUES ('2927', '商丘市商业银行', '65675060', '', '百汇卡', '19', '6', '621748', '0');
INSERT INTO `authbank_cardbin` VALUES ('2928', '商丘市商业银行', '65675061', '', '公务卡', '16', '6', '628271', '1');
INSERT INTO `authbank_cardbin` VALUES ('2929', '华融湘江银行', '65705500', '', '华融湘江银行华融公务卡普卡', '16', '6', '628328', '1');
INSERT INTO `authbank_cardbin` VALUES ('2930', 'BankofChina(Malaysia)', '99900458', '', '贷记卡', '16', '6', '625829', '1');
INSERT INTO `authbank_cardbin` VALUES ('2931', 'BankofChina(Malaysia)', '99900458', '', '贷记卡', '16', '6', '625943', '1');
INSERT INTO `authbank_cardbin` VALUES ('2932', '中行新加坡分行', '99900702', '', 'Great Wall Platinum', '16', '6', '622790', '1');
INSERT INTO `authbank_cardbin` VALUES ('2933', '农业银行', '1030000', 'ABC', '农行钻石信用卡', '16', '6', '622839', '1');
INSERT INTO `authbank_cardbin` VALUES ('2934', '温州银行', '4123330', '', '借记卡', '19', '6', '623112', '0');
INSERT INTO `authbank_cardbin` VALUES ('2935', '哈尔滨银行', '4422610', '', '银联标准单位结算卡', '19', '8', '62326501', '0');
INSERT INTO `authbank_cardbin` VALUES ('2936', '德阳银行', '4986580', '', '德阳银行鼎诚钻石卡', '19', '6', '623137', '0');
INSERT INTO `authbank_cardbin` VALUES ('2937', '大连甘井子浦发村镇银行', '15072220', '', '大连甘井子浦发村镇银行借记卡', '19', '9', '621275181', '0');
INSERT INTO `authbank_cardbin` VALUES ('2938', '江苏邗江联合村镇银行', '15083120', '', '借记卡', '19', '9', '621092007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2939', '大连普兰店汇丰村镇银行', '15112221', '', '大连普兰店汇丰村镇银行借记卡', '16', '9', '621250006', '0');
INSERT INTO `authbank_cardbin` VALUES ('2940', '山东荣成汇丰村镇银行', '15114653', '', '山东荣成汇丰村镇银行借记卡', '16', '9', '621250011', '0');
INSERT INTO `authbank_cardbin` VALUES ('2941', '湖北麻城汇丰村镇银行', '15115331', '', '湖北麻城汇丰村镇银行借记卡', '16', '9', '621250012', '0');
INSERT INTO `authbank_cardbin` VALUES ('2942', '湖北天门汇丰村镇银行', '15115374', '', '湖北天门汇丰村镇银行借记卡', '16', '9', '621250008', '0');
INSERT INTO `authbank_cardbin` VALUES ('2943', '湖南平江汇丰村镇银行', '15115575', '', '湖南平江汇丰村镇银行借记卡', '16', '9', '621250010', '0');
INSERT INTO `authbank_cardbin` VALUES ('2944', '重庆荣昌汇丰村镇银行', '15116918', '', '重庆荣昌汇丰村镇银行借记卡', '16', '9', '621250009', '0');
INSERT INTO `authbank_cardbin` VALUES ('2945', '重庆丰都汇丰村镇银行', '15116923', '', '重庆丰都汇丰村镇银行借记卡', '16', '9', '621250007', '0');
INSERT INTO `authbank_cardbin` VALUES ('2946', '重庆石柱中银富登村镇银行', '15196933', '', '借记卡', '19', '9', '621356022', '0');
INSERT INTO `authbank_cardbin` VALUES ('2947', '海南儋州绿色村镇银行', '15676431', '', '金融IC借记卡', '19', '8', '62312201', '0');
INSERT INTO `authbank_cardbin` VALUES ('2948', '锦州北镇益民村镇银行', '15852273', '', '7777卡', '19', '9', '621699001', '0');
INSERT INTO `authbank_cardbin` VALUES ('2949', '辽宁义县祥和村镇银行', '15852275', '', '7777卡', '19', '9', '621699003', '0');
INSERT INTO `authbank_cardbin` VALUES ('2950', '韩国乐天', '28030410', '', 'Lotte Corporate Card', '16', '6', '624313', '1');
INSERT INTO `authbank_cardbin` VALUES ('2951', '韩国乐天', '28030410', '', 'Diamond Card', '16', '6', '624333', '1');
INSERT INTO `authbank_cardbin` VALUES ('2952', 'OSJCMTSBank', '30280643', '', 'Classic', '16', '7', '6243420', '1');
INSERT INTO `authbank_cardbin` VALUES ('2953', 'OSJCMTSBank', '30280643', '', 'Gold', '16', '7', '6243421', '1');
INSERT INTO `authbank_cardbin` VALUES ('2954', 'OSJCMTSBank', '30280643', '', 'Platinum', '16', '7', '6243422', '1');
INSERT INTO `authbank_cardbin` VALUES ('2955', '贵州银行', '5847000', '', '黄果树福农卡', '16', '6', '621591', '0');
INSERT INTO `authbank_cardbin` VALUES ('2956', '贵州银行', '5847000', '', '黄果树卡', '16', '6', '622961', '0');
INSERT INTO `authbank_cardbin` VALUES ('2957', '江南农村商业银行', '14603040', '', '江南卡', '19', '6', '622891', '0');
INSERT INTO `authbank_cardbin` VALUES ('2958', '江南农村商业银行', '14603040', '', '财缘卡', '19', '6', '621363', '0');
INSERT INTO `authbank_cardbin` VALUES ('2959', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '436770', '1');
INSERT INTO `authbank_cardbin` VALUES ('2960', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '491033', '1');
INSERT INTO `authbank_cardbin` VALUES ('2961', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '436771', '1');
INSERT INTO `authbank_cardbin` VALUES ('2962', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '541709', '1');
INSERT INTO `authbank_cardbin` VALUES ('2963', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '541710', '1');
INSERT INTO `authbank_cardbin` VALUES ('2964', '广发银行股份有限公司', '3060000', 'GDB', '广发信用卡', '16', '6', '493427', '1');
INSERT INTO `authbank_cardbin` VALUES ('2965', '邮政储蓄银行', '1009999', '', '绿卡通IC卡全国联名卡', '19', '6', '622180', '0');
INSERT INTO `authbank_cardbin` VALUES ('2966', '邮政储蓄银行', '1009999', '', '绿卡芯片卡', '19', '6', '622182', '0');
INSERT INTO `authbank_cardbin` VALUES ('2967', '中国工商银行', '1020000', '', '财智账户卡', '19', '6', '623260', '0');
INSERT INTO `authbank_cardbin` VALUES ('2968', '中国工商银行', '1020000', '', '', '19', '6', '623271', '0');
INSERT INTO `authbank_cardbin` VALUES ('2969', '中国工商银行', '1020000', '', '', '19', '6', '623272', '0');
INSERT INTO `authbank_cardbin` VALUES ('2970', '中国工商银行', '1020000', '', '借记白金卡', '19', '6', '621218', '0');
INSERT INTO `authbank_cardbin` VALUES ('2971', '中国工商银行', '1020000', '', '借记卡灵通卡', '19', '6', '621475', '0');
INSERT INTO `authbank_cardbin` VALUES ('2972', '中国工商银行', '1020000', '', '借记卡金卡', '19', '6', '621476', '0');
INSERT INTO `authbank_cardbin` VALUES ('2973', '中国工商银行', '1020000', '', '中国旅游卡', '19', '6', '623229', '0');
INSERT INTO `authbank_cardbin` VALUES ('2974', '中国工商银行', '1020000', '', '', '16', '6', '625651', '1');
INSERT INTO `authbank_cardbin` VALUES ('2975', '中国工商银行加拿大分行', '1020124', '', '预付卡', '16', '6', '623321', '0');
INSERT INTO `authbank_cardbin` VALUES ('2976', '中国工商银行(亚洲)有限公司', '1020344', '', '', '16', '6', '625941', '1');
INSERT INTO `authbank_cardbin` VALUES ('2977', '中国工商银行(亚洲)有限公司', '1020344', '', '工银亚洲贷记卡', '16', '6', '625801', '1');
INSERT INTO `authbank_cardbin` VALUES ('2978', '中国工商银行米兰分行', '1020380', '', '借记卡', '16', '8', '62137310', '0');
INSERT INTO `authbank_cardbin` VALUES ('2979', '中国工商银行米兰分行', '1020380', '', '借记卡', '16', '8', '62137320', '0');
INSERT INTO `authbank_cardbin` VALUES ('2980', '中国工商银行米兰分行', '1020380', '', '贷记卡', '16', '8', '62592310', '1');
INSERT INTO `authbank_cardbin` VALUES ('2981', '中国工商银行米兰分行', '1020380', '', '贷记卡', '16', '8', '62592320', '1');
INSERT INTO `authbank_cardbin` VALUES ('2982', '中国工商银行米兰分行', '1020380', '', '贷记卡', '16', '8', '62592340', '1');
INSERT INTO `authbank_cardbin` VALUES ('2983', '中国工商银行米兰分行', '1020380', '', '', '16', '8', '62013101', '2');
INSERT INTO `authbank_cardbin` VALUES ('2984', '中国工商银行米兰分行', '1020380', '', '', '16', '8', '62013102', '2');
INSERT INTO `authbank_cardbin` VALUES ('2985', '中国工商银行卡拉奇分行', '1020586', '', '银联公司卡', '16', '8', '62594250', '1');
INSERT INTO `authbank_cardbin` VALUES ('2986', '中国工商银行卡拉奇分行', '1020586', '', '银联公司卡', '16', '8', '62594260', '1');
INSERT INTO `authbank_cardbin` VALUES ('2987', '中国工商银行卡拉奇分行', '1020586', '', '银联公司卡', '16', '8', '62594270', '1');
INSERT INTO `authbank_cardbin` VALUES ('2988', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6243190', '1');
INSERT INTO `authbank_cardbin` VALUES ('2989', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6243191', '1');
INSERT INTO `authbank_cardbin` VALUES ('2990', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6243192', '1');
INSERT INTO `authbank_cardbin` VALUES ('2991', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6243193', '1');
INSERT INTO `authbank_cardbin` VALUES ('2992', 'ICBC(USA) NA', '1020840', '', '', '16', '8', '62431940', '1');
INSERT INTO `authbank_cardbin` VALUES ('2993', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6244220', '1');
INSERT INTO `authbank_cardbin` VALUES ('2994', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6244221', '1');
INSERT INTO `authbank_cardbin` VALUES ('2995', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6244222', '1');
INSERT INTO `authbank_cardbin` VALUES ('2996', 'ICBC(USA) NA', '1020840', '', '', '16', '7', '6244223', '1');
INSERT INTO `authbank_cardbin` VALUES ('2997', 'ICBC(USA) NA', '1020840', '', '', '16', '8', '62442240', '1');
INSERT INTO `authbank_cardbin` VALUES ('2998', '中国农业银行贷记卡', '1030001', '', '农行商务卡', '16', '6', '625653', '1');
INSERT INTO `authbank_cardbin` VALUES ('2999', '中国农业银行贷记卡', '1030001', '', '农行标准公务卡', '16', '6', '628346', '1');
INSERT INTO `authbank_cardbin` VALUES ('3000', '中国农业银行贷记卡', '1030001', '', '农行标准金卡', '16', '6', '625171', '3');
INSERT INTO `authbank_cardbin` VALUES ('3001', '中国农业银行贷记卡', '1030001', '', '农行标准金卡', '16', '6', '625170', '1');
INSERT INTO `authbank_cardbin` VALUES ('3002', '中国银行', '1040000', '', '长城代发薪借记IC卡（钻石卡）', '19', '6', '623571', '0');
INSERT INTO `authbank_cardbin` VALUES ('3003', '中国银行', '1040000', '', '长城代发薪借记IC卡（白金卡）', '19', '6', '623572', '0');
INSERT INTO `authbank_cardbin` VALUES ('3004', '中国银行', '1040000', '', '长城代发薪借记IC卡（普卡）', '19', '6', '623575', '0');
INSERT INTO `authbank_cardbin` VALUES ('3005', '中国银行', '1040000', '', '银联保障类借记IC卡（金卡）', '19', '6', '623184', '0');
INSERT INTO `authbank_cardbin` VALUES ('3006', '中国银行', '1040000', '', '银联保障类借记IC卡（白金卡）', '19', '6', '623569', '0');
INSERT INTO `authbank_cardbin` VALUES ('3007', '中国银行', '1040000', '', '银联多币借记IC卡（钻石卡）', '19', '6', '623586', '0');
INSERT INTO `authbank_cardbin` VALUES ('3008', '中国银行', '1040000', '', '银联HCE移动支付信用卡', '16', '6', '625834', '3');
INSERT INTO `authbank_cardbin` VALUES ('3009', '中国银行', '1040000', '', '长城代发薪借记IC卡（金卡）', '19', '6', '623573', '0');
INSERT INTO `authbank_cardbin` VALUES ('3010', '中国银行', '1040000', '', '', '19', '6', '627025', '0');
INSERT INTO `authbank_cardbin` VALUES ('3011', '中国银行', '1040000', '', '', '19', '6', '627026', '0');
INSERT INTO `authbank_cardbin` VALUES ('3012', '中国银行', '1040000', '', '', '19', '6', '627027', '0');
INSERT INTO `authbank_cardbin` VALUES ('3013', '中国银行', '1040000', '', '', '19', '6', '627028', '0');
INSERT INTO `authbank_cardbin` VALUES ('3014', '中国银行澳门分行', '1040446', '', '', '16', '6', '626200', '1');
INSERT INTO `authbank_cardbin` VALUES ('3015', '中国建设银行', '1050001', '', '代发工资借记IC卡', '19', '6', '623094', '0');
INSERT INTO `authbank_cardbin` VALUES ('3016', '中国建设银行', '1050001', '', '全国联名卡', '19', '6', '623669', '0');
INSERT INTO `authbank_cardbin` VALUES ('3017', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233670', '0');
INSERT INTO `authbank_cardbin` VALUES ('3018', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233671', '0');
INSERT INTO `authbank_cardbin` VALUES ('3019', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233672', '0');
INSERT INTO `authbank_cardbin` VALUES ('3020', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233673', '0');
INSERT INTO `authbank_cardbin` VALUES ('3021', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233674', '0');
INSERT INTO `authbank_cardbin` VALUES ('3022', 'LLC Bank (Russia)', '1050643', '', 'Classic', '16', '7', '6233675', '0');
INSERT INTO `authbank_cardbin` VALUES ('3023', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234340', '0');
INSERT INTO `authbank_cardbin` VALUES ('3024', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234341', '0');
INSERT INTO `authbank_cardbin` VALUES ('3025', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234342', '0');
INSERT INTO `authbank_cardbin` VALUES ('3026', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234343', '0');
INSERT INTO `authbank_cardbin` VALUES ('3027', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234344', '0');
INSERT INTO `authbank_cardbin` VALUES ('3028', 'LLC Bank (Russia)', '1050643', '', '', '16', '7', '6234345', '0');
INSERT INTO `authbank_cardbin` VALUES ('3029', '交通银行', '3010000', '', 'more银联金卡', '16', '6', '622285', '3');
INSERT INTO `authbank_cardbin` VALUES ('3030', '中信银行', '3020000', '', '浙江高院案款管理系统专用卡', '16', '6', '623280', '0');
INSERT INTO `authbank_cardbin` VALUES ('3031', '光大银行', '3030000', '', '', '16', '6', '623253', '0');
INSERT INTO `authbank_cardbin` VALUES ('3032', '华夏银行', '3040000', '', '', '16', '6', '620552', '2');
INSERT INTO `authbank_cardbin` VALUES ('3033', '民生银行', '3050000', '', '借记卡', '16', '6', '900003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3034', '民生银行', '3050000', '', '民生银行银联借记卡银卡', '16', '6', '623683', '0');
INSERT INTO `authbank_cardbin` VALUES ('3035', '民生银行', '3050001', '', '', '16', '6', '625188', '1');
INSERT INTO `authbank_cardbin` VALUES ('3036', '招商银行', '3080000', '', '招商银行钻石IC卡', '16', '6', '623126', '0');
INSERT INTO `authbank_cardbin` VALUES ('3037', '招商银行', '3080000', '', '招商银行私人银行IC卡', '16', '6', '623136', '0');
INSERT INTO `authbank_cardbin` VALUES ('3038', '招商银行', '3080000', '', '招商银行“公司一卡通”IC卡', '16', '6', '623262', '0');
INSERT INTO `authbank_cardbin` VALUES ('3039', '兴业银行', '3090010', '', '', '16', '6', '622571', '1');
INSERT INTO `authbank_cardbin` VALUES ('3040', '兴业银行', '3090010', '', '', '16', '6', '622572', '1');
INSERT INTO `authbank_cardbin` VALUES ('3041', '兴业银行', '3090010', '', '', '16', '6', '622573', '1');
INSERT INTO `authbank_cardbin` VALUES ('3042', '兴业银行', '3090010', '', '', '16', '6', '622591', '1');
INSERT INTO `authbank_cardbin` VALUES ('3043', '兴业银行', '3090010', '', '', '16', '6', '622592', '1');
INSERT INTO `authbank_cardbin` VALUES ('3044', '兴业银行', '3090010', '', '', '16', '6', '622593', '1');
INSERT INTO `authbank_cardbin` VALUES ('3045', '浙商银行', '3160001', '', '', '16', '6', '625821', '1');
INSERT INTO `authbank_cardbin` VALUES ('3046', '渤海银行股份有限公司', '3170003', '', '渤海银行信用卡', '16', '6', '625122', '1');
INSERT INTO `authbank_cardbin` VALUES ('3047', '平安银行', '3180000', '', '', '19', '6', '623269', '0');
INSERT INTO `authbank_cardbin` VALUES ('3048', '星展银行', '3240000', '', '星展卡', '19', '6', '623187', '0');
INSERT INTO `authbank_cardbin` VALUES ('3049', '友利银行(中国)有限公司', '3270000', '', '友利借记卡', '19', '6', '623551', '0');
INSERT INTO `authbank_cardbin` VALUES ('3050', '新韩银行', '3280000', '', '', '19', '6', '623630', '0');
INSERT INTO `authbank_cardbin` VALUES ('3051', '韩亚银行（中国）', '3290000', '', '借记卡', '16', '6', '623513', '0');
INSERT INTO `authbank_cardbin` VALUES ('3052', '南洋商业银行（中国）', '3320000', '', '', '19', '6', '623555', '0');
INSERT INTO `authbank_cardbin` VALUES ('3053', '大华银行（中国）', '3340000', '', '大华银行尊享借记卡', '19', '6', '623176', '0');
INSERT INTO `authbank_cardbin` VALUES ('3054', '企业银行（中国）', '3360000', '', '', '19', '8', '62326516', '0');
INSERT INTO `authbank_cardbin` VALUES ('3055', '华商银行', '3370000', '', '', '19', '6', '623163', '0');
INSERT INTO `authbank_cardbin` VALUES ('3056', '中德住房储蓄银行', '3380000', '', '住储卡', '19', '6', '623526', '0');
INSERT INTO `authbank_cardbin` VALUES ('3057', '富邦华一银行', '3390000', '', '富邦华一银行借记卡', '19', '6', '623565', '0');
INSERT INTO `authbank_cardbin` VALUES ('3058', '深圳前海微众银行', '3400000', '', '', '19', '6', '623633', '0');
INSERT INTO `authbank_cardbin` VALUES ('3059', '天津金城银行', '3420000', '', '金城卡', '18', '6', '623616', '0');
INSERT INTO `authbank_cardbin` VALUES ('3060', '上海华瑞银行股份有限公司', '3430000', '', '', '19', '6', '623622', '0');
INSERT INTO `authbank_cardbin` VALUES ('3061', '温州民商', '3440000', '', '民商卡', '19', '6', '623632', '0');
INSERT INTO `authbank_cardbin` VALUES ('3062', '上海银行', '4012900', '', '单位卡', '18', '6', '621243', '0');
INSERT INTO `authbank_cardbin` VALUES ('3063', '上海银行', '4012902', '', '钻石卡', '16', '6', '625180', '1');
INSERT INTO `authbank_cardbin` VALUES ('3064', '北京银行', '4031000', '', '北京银行借记卡', '19', '6', '623561', '0');
INSERT INTO `authbank_cardbin` VALUES ('3065', '北京银行', '4031000', '', '北京银行借记卡', '19', '6', '623562', '0');
INSERT INTO `authbank_cardbin` VALUES ('3066', '烟台市商业银行', '4044560', '', '', '19', '6', '623533', '0');
INSERT INTO `authbank_cardbin` VALUES ('3067', '福建海峡银行股份有限公司', '4053910', '', '', '18', '6', '621664', '0');
INSERT INTO `authbank_cardbin` VALUES ('3068', '福建海峡银行', '4053919', '', '信用卡', '16', '6', '622695', '1');
INSERT INTO `authbank_cardbin` VALUES ('3069', '焦作中旅银行', '4115010', '', '', '19', '6', '623511', '0');
INSERT INTO `authbank_cardbin` VALUES ('3070', '温州银行', '4123330', '', '', '19', '6', '623578', '0');
INSERT INTO `authbank_cardbin` VALUES ('3071', '汉口银行', '4145210', '', '九通汇融通卡', '16', '8', '62326510', '0');
INSERT INTO `authbank_cardbin` VALUES ('3072', '河北银行', '4221219', '', '', '19', '6', '623582', '0');
INSERT INTO `authbank_cardbin` VALUES ('3073', '杭州商业银行', '4233310', '', '单位结算卡IC卡', '18', '8', '62326513', '0');
INSERT INTO `authbank_cardbin` VALUES ('3074', '杭州商业银行', '4233310', '', '单位结算卡IC卡', '19', '8', '62326527', '0');
INSERT INTO `authbank_cardbin` VALUES ('3075', '葫芦岛银行', '4332369', '', '金融IC借记一通卡', '16', '6', '623598', '0');
INSERT INTO `authbank_cardbin` VALUES ('3076', '天津市商业银行', '4341100', '', '', '18', '6', '623554', '0');
INSERT INTO `authbank_cardbin` VALUES ('3077', '天津市商业银行', '4341100', '', '', '18', '6', '623574', '0');
INSERT INTO `authbank_cardbin` VALUES ('3078', '郑州银行股份有限公司', '4354910', '', '', '19', '6', '623531', '0');
INSERT INTO `authbank_cardbin` VALUES ('3079', '宁夏银行', '4360010', '', '中国旅游卡', '16', '6', '625335', '1');
INSERT INTO `authbank_cardbin` VALUES ('3080', '锦州银行股份有限公司', '4392270', '', '锦州银行7777卡', '19', '6', '623568', '0');
INSERT INTO `authbank_cardbin` VALUES ('3081', '贵阳银行股份有限公司', '4437010', '', '贷记卡', '16', '6', '622537', '1');
INSERT INTO `authbank_cardbin` VALUES ('3082', '贵阳银行股份有限公司', '4437010', '', '', '19', '8', '62326519', '0');
INSERT INTO `authbank_cardbin` VALUES ('3083', '西安银行股份有限公司', '4447910', '', '金丝路单位结算卡', '18', '6', '623277', '0');
INSERT INTO `authbank_cardbin` VALUES ('3084', '兰州银行股份有限公司', '4478210', '', '', '19', '6', '623275', '0');
INSERT INTO `authbank_cardbin` VALUES ('3085', '兰州银行股份有限公司', '4478210', '', '兰州三维市民卡', '19', '6', '623541', '0');
INSERT INTO `authbank_cardbin` VALUES ('3086', '九江银行股份有限公司', '4544240', '', '庐山卡', '19', '6', '623146', '0');
INSERT INTO `authbank_cardbin` VALUES ('3087', '秦皇岛银行', '4571269', '', '', '16', '6', '628357', '1');
INSERT INTO `authbank_cardbin` VALUES ('3088', '青海银行', '4588510', '', '', '17', '6', '623599', '0');
INSERT INTO `authbank_cardbin` VALUES ('3089', '青海银行', '4588510', '', '', '17', '6', '623670', '0');
INSERT INTO `authbank_cardbin` VALUES ('3090', '赣州银行股份有限公司', '4634280', '', '', '19', '6', '623127', '0');
INSERT INTO `authbank_cardbin` VALUES ('3091', '内蒙古银行', '4741910', '', '借记卡', '19', '6', '623057', '0');
INSERT INTO `authbank_cardbin` VALUES ('3092', '内蒙古银行', '4741910', '', '', '19', '8', '62326525', '0');
INSERT INTO `authbank_cardbin` VALUES ('3093', '沧州银行', '4761432', '', '', '16', '6', '628395', '1');
INSERT INTO `authbank_cardbin` VALUES ('3094', '包商银行', '4791920', '', '', '17', '6', '623273', '0');
INSERT INTO `authbank_cardbin` VALUES ('3095', '包商银行', '4791920', '', '', '19', '6', '623592', '0');
INSERT INTO `authbank_cardbin` VALUES ('3096', '包商银行', '4791920', '', '', '19', '6', '623631', '0');
INSERT INTO `authbank_cardbin` VALUES ('3097', '包商银行', '4791921', '', '', '16', '6', '625359', '1');
INSERT INTO `authbank_cardbin` VALUES ('3098', '大同银行', '4871620', '', '大同银行借记卡', '19', '6', '622925', '0');
INSERT INTO `authbank_cardbin` VALUES ('3099', '广东南粤银行', '4895910', '', '', '19', '6', '623595', '0');
INSERT INTO `authbank_cardbin` VALUES ('3100', '桂林市商业银行', '4916170', '', '中国旅游卡', '17', '6', '623221', '0');
INSERT INTO `authbank_cardbin` VALUES ('3101', '龙江银行', '4922600', '', '龙江银行单位结算卡', '19', '8', '62326503', '0');
INSERT INTO `authbank_cardbin` VALUES ('3102', '龙江银行', '4922600', '', '中国旅游卡黑龙江IC借记卡普卡', '19', '6', '623216', '0');
INSERT INTO `authbank_cardbin` VALUES ('3103', '江苏长江商业银行', '4933160', '', '借记卡', '16', '6', '623150', '0');
INSERT INTO `authbank_cardbin` VALUES ('3104', '晋城银行股份有限公司', '5031680', '', '借记卡', '19', '6', '623197', '0');
INSERT INTO `authbank_cardbin` VALUES ('3105', '江苏银行', '5083000', '', '', '19', '6', '623279', '0');
INSERT INTO `authbank_cardbin` VALUES ('3106', '长治银行股份有限公司', '5121660', '', '', '19', '6', '623509', '0');
INSERT INTO `authbank_cardbin` VALUES ('3107', '承德银行', '5131419', '', '', '19', '8', '62326515', '0');
INSERT INTO `authbank_cardbin` VALUES ('3108', '宁波东海银行', '5503320', '', '洪福卡', '18', '6', '623167', '0');
INSERT INTO `authbank_cardbin` VALUES ('3109', '邢台银行', '5541311', '', '邢台银行金牛公务卡', '16', '6', '628325', '1');
INSERT INTO `authbank_cardbin` VALUES ('3110', '达州市商业银行', '5576751', '', '丹凤卡借记IC卡', '19', '6', '623588', '0');
INSERT INTO `authbank_cardbin` VALUES ('3111', '衡水银行', '5611499', '', '金鼎公务卡', '16', '6', '628380', '1');
INSERT INTO `authbank_cardbin` VALUES ('3112', '营口沿海银行', '5722289', '', '', '19', '6', '620063', '0');
INSERT INTO `authbank_cardbin` VALUES ('3113', '景德镇商业银行', '5734220', '', '瓷都借记IC卡（普卡）', '18', '6', '623080', '0');
INSERT INTO `authbank_cardbin` VALUES ('3114', '湖北银行', '5755202', '', '湖北银行贷记IC卡', '16', '6', '625850', '1');
INSERT INTO `authbank_cardbin` VALUES ('3115', '湖北银行', '5755202', '', '', '16', '6', '628340', '1');
INSERT INTO `authbank_cardbin` VALUES ('3116', '广东华兴银行', '5785800', '', 'IC卡', '19', '6', '623611', '0');
INSERT INTO `authbank_cardbin` VALUES ('3117', '广东华兴银行', '5785800', '', '', '19', '6', '623665', '0');
INSERT INTO `authbank_cardbin` VALUES ('3118', '广东华兴银行', '5785800', '', '', '19', '6', '623627', '0');
INSERT INTO `authbank_cardbin` VALUES ('3119', '宁波通商银行', '5803320', '', '', '19', '6', '623537', '0');
INSERT INTO `authbank_cardbin` VALUES ('3120', '本溪市商业银行', '5832250', '', '山城枫叶卡', '19', '6', '623577', '0');
INSERT INTO `authbank_cardbin` VALUES ('3121', '中原银行', '5864910', '', '', '16', '6', '623660', '0');
INSERT INTO `authbank_cardbin` VALUES ('3122', '中原银行', '5864910', '', '世纪一卡通(银联卡)', '19', '7', '6224217', '0');
INSERT INTO `authbank_cardbin` VALUES ('3123', '厦门国际银行', '5870000', '', '', '16', '6', '623623', '0');
INSERT INTO `authbank_cardbin` VALUES ('3124', '海南银行', '5886400', '', '', '19', '6', '623621', '0');
INSERT INTO `authbank_cardbin` VALUES ('3125', '邯郸银行', '5890000', '', '', '16', '6', '628377', '1');
INSERT INTO `authbank_cardbin` VALUES ('3126', '上海农商银行', '14012900', '', '上海农商银行鑫通卡', '19', '6', '623552', '0');
INSERT INTO `authbank_cardbin` VALUES ('3127', '上海农商银行', '14012900', '', '上海农商银行单位结算卡', '19', '8', '62326508', '0');
INSERT INTO `authbank_cardbin` VALUES ('3128', '广州农村商业银行股份有限公司', '14055810', '', '单位结算卡', '18', '6', '623257', '0');
INSERT INTO `authbank_cardbin` VALUES ('3129', '湖北农信社', '14105200', '', '福卡信用卡', '16', '6', '625889', '1');
INSERT INTO `authbank_cardbin` VALUES ('3130', '湖北农信社', '14105200', '', '', '16', '6', '628254', '1');
INSERT INTO `authbank_cardbin` VALUES ('3131', '湖北农信社', '14105200', '', '福卡单位结算卡', '16', '6', '623276', '0');
INSERT INTO `authbank_cardbin` VALUES ('3132', '山东省农村信用社联合社', '14144501', '', '', '16', '6', '628375', '1');
INSERT INTO `authbank_cardbin` VALUES ('3133', '北京农村商业银行', '14181000', '', '', '19', '8', '62326520', '0');
INSERT INTO `authbank_cardbin` VALUES ('3134', '江苏省农村信用社联合社', '14243001', '', '圆鼎单位结算卡', '19', '6', '623267', '0');
INSERT INTO `authbank_cardbin` VALUES ('3135', '浙江省农村信用社联合社', '14293300', '', '丰收IC福农卡', '19', '6', '621537', '0');
INSERT INTO `authbank_cardbin` VALUES ('3136', '浙江省农村信用社联合社', '14293300', '', '丰收IC小额贷款卡', '19', '6', '621736', '0');
INSERT INTO `authbank_cardbin` VALUES ('3137', '浙江省农村信用社联合社', '14293300', '', '', '19', '6', '623540', '0');
INSERT INTO `authbank_cardbin` VALUES ('3138', '贵州省农村信用社联合社', '14367000', '', '', '19', '6', '621779', '0');
INSERT INTO `authbank_cardbin` VALUES ('3139', '广西壮族自治区农村信用社联合社', '14436103', '', '桂盛信用卡', '16', '6', '625121', '1');
INSERT INTO `authbank_cardbin` VALUES ('3140', '吉林省农村信用联合社', '14452404', '', '', '19', '6', '623618', '0');
INSERT INTO `authbank_cardbin` VALUES ('3141', '安徽省农村信用社联合社', '14473601', '', '', '16', '6', '628387', '1');
INSERT INTO `authbank_cardbin` VALUES ('3142', '青海省农村信用社联合社', '14498500', '', '中国旅游卡', '16', '6', '623213', '0');
INSERT INTO `authbank_cardbin` VALUES ('3143', '内蒙古自治区农村信用联合社', '14511900', '', '借记卡', '19', '6', '621737', '0');
INSERT INTO `authbank_cardbin` VALUES ('3144', '四川省农村信用社联合社', '14526500', '', '单位结算卡', '19', '6', '623256', '0');
INSERT INTO `authbank_cardbin` VALUES ('3145', '四川省农村信用社联合社', '14526501', '', '兴川贷记卡', '16', '6', '625097', '1');
INSERT INTO `authbank_cardbin` VALUES ('3146', '辽宁省农村信用社联合社', '14542201', '', '公务卡', '16', '6', '628399', '1');
INSERT INTO `authbank_cardbin` VALUES ('3147', '黑龙江省农村信用社联合社', '14572600', '', '鹤卡单位结算卡', '19', '8', '62326502', '0');
INSERT INTO `authbank_cardbin` VALUES ('3148', '江南农村商业银行', '14603040', '', '江南贵宾卡', '19', '6', '623576', '0');
INSERT INTO `authbank_cardbin` VALUES ('3149', '海口农村商业银行', '14636410', '', '', '16', '6', '625126', '1');
INSERT INTO `authbank_cardbin` VALUES ('3150', '海口农村商业银行', '14636410', '', '', '16', '6', '628320', '1');
INSERT INTO `authbank_cardbin` VALUES ('3151', '青岛崂山交银村镇银行', '15004526', '', '', '19', '9', '621056805', '0');
INSERT INTO `authbank_cardbin` VALUES ('3152', '青岛即墨京都村镇银行', '15024521', '', '', '19', '9', '622975682', '0');
INSERT INTO `authbank_cardbin` VALUES ('3153', '湖北仙桃京都村镇银行', '15025371', '', '', '19', '9', '622975681', '0');
INSERT INTO `authbank_cardbin` VALUES ('3154', '句容苏南村镇银行', '15033142', '', '暨阳卡', '16', '8', '62105903', '0');
INSERT INTO `authbank_cardbin` VALUES ('3155', '兴化苏南村镇银行', '15033161', '', '暨阳卡', '16', '8', '62105906', '0');
INSERT INTO `authbank_cardbin` VALUES ('3156', '双流诚民村镇银行', '15036512', '', '暨阳卡', '16', '8', '62105911', '0');
INSERT INTO `authbank_cardbin` VALUES ('3157', '宣汉诚民村镇银行', '15036753', '', '暨阳卡', '16', '8', '62105902', '0');
INSERT INTO `authbank_cardbin` VALUES ('3158', '金坛常农商村镇银行', '15053042', '', '金坛磁条芯片复合卡', '19', '9', '621260103', '0');
INSERT INTO `authbank_cardbin` VALUES ('3159', '天津宝坻浦发村镇银行', '15071126', '', '借记卡', '19', '9', '621275341', '0');
INSERT INTO `authbank_cardbin` VALUES ('3160', '泽州浦发村镇银行', '15071685', '', '泽州浦发村镇银行借记卡', '19', '9', '621275171', '0');
INSERT INTO `authbank_cardbin` VALUES ('3161', '公主岭浦发村镇银行股份有限公司', '15072434', '', '借记卡', '19', '9', '621275291', '0');
INSERT INTO `authbank_cardbin` VALUES ('3162', '江苏江阴浦发村镇银行', '15073022', '', '江苏江阴浦发村镇银行借记卡', '19', '9', '621275191', '0');
INSERT INTO `authbank_cardbin` VALUES ('3163', '溧阳浦发村镇银行', '15073043', '', '', '19', '9', '621275121', '0');
INSERT INTO `authbank_cardbin` VALUES ('3164', '宁波海曙浦发村镇银行', '15073327', '', '宁波海曙浦发村镇银行借记卡', '19', '9', '621275321', '0');
INSERT INTO `authbank_cardbin` VALUES ('3165', '浙江平阳浦发村镇银行', '15073335', '', '', '19', '9', '621275221', '0');
INSERT INTO `authbank_cardbin` VALUES ('3166', '浙江新昌浦发村镇银行', '15073374', '', '浙江新昌浦发村镇银行借记卡', '19', '9', '621275201', '0');
INSERT INTO `authbank_cardbin` VALUES ('3167', '江西临川浦发村镇银行', '15074383', '', '', '19', '9', '621275251', '0');
INSERT INTO `authbank_cardbin` VALUES ('3168', '邹平浦发村镇银行', '15074667', '', '邹平浦发村镇银行借记卡', '19', '9', '621275161', '0');
INSERT INTO `authbank_cardbin` VALUES ('3169', '河南巩义浦发村镇银行', '15074914', '', '巩义浦发村镇银行借记卡', '19', '9', '621275131', '0');
INSERT INTO `authbank_cardbin` VALUES ('3170', '湖南茶陵浦发村镇银行', '15075523', '', '茶陵浦发村镇银行借记卡', '19', '9', '621275241', '0');
INSERT INTO `authbank_cardbin` VALUES ('3171', '湖南衡南浦发村镇银行', '15075542', '', '衡南浦发村镇银行借记卡', '19', '9', '621275271', '0');
INSERT INTO `authbank_cardbin` VALUES ('3172', '湖南沅江浦发村镇银行', '15075612', '', '借记卡', '19', '9', '621275231', '0');
INSERT INTO `authbank_cardbin` VALUES ('3173', '绵竹浦发村镇银行', '15076581', '', '借记卡', '19', '9', '621275101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3174', '重庆巴南浦发村镇银行', '15076900', '', '重庆巴南浦发村镇银行借记卡', '19', '9', '621275151', '0');
INSERT INTO `authbank_cardbin` VALUES ('3175', '富民浦发村镇银行', '15077314', '', '富民浦发村镇银行借记卡', '19', '9', '621275301', '0');
INSERT INTO `authbank_cardbin` VALUES ('3176', '甘肃榆中浦发村镇银行', '15078213', '', '', '19', '9', '621275311', '0');
INSERT INTO `authbank_cardbin` VALUES ('3177', '乌鲁木齐米东浦发村镇银行', '15078812', '', '', '19', '9', '621275331', '0');
INSERT INTO `authbank_cardbin` VALUES ('3178', '江苏邗江联合村镇银行', '15083120', '', '', '19', '8', '62109209', '0');
INSERT INTO `authbank_cardbin` VALUES ('3179', '浙江乐清联合村镇银行', '15083333', '', '雁荡卡', '19', '8', '62109207', '0');
INSERT INTO `authbank_cardbin` VALUES ('3180', '浙江嘉善联合村镇银行', '15083351', '', '联合卡', '19', '8', '62109202', '0');
INSERT INTO `authbank_cardbin` VALUES ('3181', '浙江长兴联合村镇银行', '15083362', '', '联合卡', '19', '8', '62109203', '0');
INSERT INTO `authbank_cardbin` VALUES ('3182', '浙江柯桥联合村镇银行', '15083370', '', '联合卡', '19', '8', '62109208', '0');
INSERT INTO `authbank_cardbin` VALUES ('3183', '浙江诸暨联合村镇银行', '15083375', '', '联合卡', '19', '8', '62109211', '0');
INSERT INTO `authbank_cardbin` VALUES ('3184', '浙江义乌联合村镇银行', '15083387', '', '', '19', '8', '62109205', '0');
INSERT INTO `authbank_cardbin` VALUES ('3185', '浙江云和联合村镇银行', '15083433', '', '联合卡', '19', '8', '62109212', '0');
INSERT INTO `authbank_cardbin` VALUES ('3186', '浙江温岭联合村镇银行', '15083454', '', '联合卡', '19', '8', '62109206', '0');
INSERT INTO `authbank_cardbin` VALUES ('3187', '安徽寿县联合村镇银行', '15083763', '', '', '19', '9', '621092011', '0');
INSERT INTO `authbank_cardbin` VALUES ('3188', '安徽寿县联合村镇银行', '15083763', '', '联合卡', '19', '8', '62109214', '0');
INSERT INTO `authbank_cardbin` VALUES ('3189', '安徽霍邱联合村镇银行', '15083764', '', '', '19', '9', '621092012', '0');
INSERT INTO `authbank_cardbin` VALUES ('3190', '安徽霍邱联合村镇银行', '15083764', '', '联合卡', '19', '8', '62109215', '0');
INSERT INTO `authbank_cardbin` VALUES ('3191', '安徽霍山联合村镇银行', '15083767', '', '联合卡', '19', '9', '621092010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3192', '安徽霍山联合村镇银行', '15083767', '', '联合卡', '19', '8', '62109213', '0');
INSERT INTO `authbank_cardbin` VALUES ('3193', '江苏东海张农商村镇银行', '15093072', '', '水晶卡', '17', '6', '621308', '0');
INSERT INTO `authbank_cardbin` VALUES ('3194', '寿光张农商村镇银行', '15094582', '', '丰收卡', '17', '6', '621219', '0');
INSERT INTO `authbank_cardbin` VALUES ('3195', '三河蒙银村镇银行', '15141461', '', '蒙银借记卡', '19', '8', '62134615', '0');
INSERT INTO `authbank_cardbin` VALUES ('3196', '朔州市城区蒙银村镇银行', '15141695', '', '蒙银借记卡', '19', '8', '62134614', '0');
INSERT INTO `authbank_cardbin` VALUES ('3197', '呼和浩特市如意蒙银村镇银行', '15141917', '', '蒙银借记卡', '19', '8', '62134617', '0');
INSERT INTO `authbank_cardbin` VALUES ('3198', '呼和浩特市赛罕蒙银村镇银行', '15141918', '', '蒙银借记卡', '19', '8', '62134616', '0');
INSERT INTO `authbank_cardbin` VALUES ('3199', '呼和浩特市新城蒙银村镇银行', '15141919', '', '蒙银借记卡', '19', '8', '62134605', '0');
INSERT INTO `authbank_cardbin` VALUES ('3200', '土默特右旗蒙银村镇银行', '15141921', '', '蒙银借记卡', '19', '8', '62134608', '0');
INSERT INTO `authbank_cardbin` VALUES ('3201', '包头市昆都仑蒙银村镇银行', '15141925', '', '蒙银借记卡', '19', '8', '62134620', '0');
INSERT INTO `authbank_cardbin` VALUES ('3202', '扎兰屯蒙银村镇银行', '15141963', '', '蒙银借记卡', '19', '8', '62134603', '0');
INSERT INTO `authbank_cardbin` VALUES ('3203', '扎赉特蒙银村镇银行', '15141984', '', '蒙银借记卡', '19', '8', '62134604', '0');
INSERT INTO `authbank_cardbin` VALUES ('3204', '突泉屯蒙银村镇银行', '15141985', '', '蒙银借记卡', '19', '8', '62134609', '0');
INSERT INTO `authbank_cardbin` VALUES ('3205', '霍林郭勒蒙银村镇银行', '15141992', '', '蒙银借记卡', '19', '8', '62134622', '0');
INSERT INTO `authbank_cardbin` VALUES ('3206', '开鲁蒙银村镇银行', '15141995', '', '蒙银借记卡', '19', '8', '62134621', '0');
INSERT INTO `authbank_cardbin` VALUES ('3207', '卓资蒙银村镇银行', '15142035', '', '蒙银借记卡', '19', '8', '62134602', '0');
INSERT INTO `authbank_cardbin` VALUES ('3208', '察哈尔右翼前旗蒙银村镇银行', '15142042', '', '蒙银借记卡', '19', '8', '62134601', '0');
INSERT INTO `authbank_cardbin` VALUES ('3209', '四子王蒙银村镇银行', '15142046', '', '蒙银借记卡', '19', '8', '62134613', '0');
INSERT INTO `authbank_cardbin` VALUES ('3210', '鄂尔多斯市东胜蒙银村镇银行', '15142050', '', '春雨卡', '19', '8', '62134625', '0');
INSERT INTO `authbank_cardbin` VALUES ('3211', '鄂尔多斯市铁西蒙银村镇银行', '15142051', '', '蒙银借记卡', '19', '8', '62134611', '0');
INSERT INTO `authbank_cardbin` VALUES ('3212', '鄂托克前旗蒙银村镇银行', '15142054', '', '蒙银借记卡', '19', '8', '62134624', '0');
INSERT INTO `authbank_cardbin` VALUES ('3213', '五原蒙银村镇银行', '15142072', '', '蒙银借记卡', '19', '8', '62134606', '0');
INSERT INTO `authbank_cardbin` VALUES ('3214', '磴口蒙银村镇银行', '15142073', '', '蒙银借记卡', '19', '8', '62134626', '0');
INSERT INTO `authbank_cardbin` VALUES ('3215', '乌拉特中旗蒙银村镇银行', '15142075', '', '蒙银借记卡', '19', '8', '62134607', '0');
INSERT INTO `authbank_cardbin` VALUES ('3216', '大连旅顺口蒙银村镇银行', '15142229', '', '蒙银借记卡', '19', '8', '62134618', '0');
INSERT INTO `authbank_cardbin` VALUES ('3217', '南京六合九银村镇银行', '15153010', '', '南京六合九银村镇银行银行卡', '19', '9', '621326969', '0');
INSERT INTO `authbank_cardbin` VALUES ('3218', '南昌昌东九银村镇银行', '15154215', '', '', '19', '9', '621326979', '0');
INSERT INTO `authbank_cardbin` VALUES ('3219', '修水九银村镇银行', '15154244', '', '', '19', '9', '621326939', '0');
INSERT INTO `authbank_cardbin` VALUES ('3220', '贵溪九银村镇银行股份有限公司', '15154271', '', '贵溪九银村镇银行银行卡', '19', '9', '621326959', '0');
INSERT INTO `authbank_cardbin` VALUES ('3221', '井冈山九银村镇银行', '15154352', '', '', '19', '9', '621326929', '0');
INSERT INTO `authbank_cardbin` VALUES ('3222', '日照九银村镇银行', '15154770', '', '日照九银村镇银行银行卡', '19', '9', '621326949', '0');
INSERT INTO `authbank_cardbin` VALUES ('3223', '江苏惠山民泰村镇银行', '15173020', '', '借记卡', '19', '9', '621338005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3224', '江苏金湖民泰村镇银行', '15173089', '', '', '19', '9', '621338008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3225', '浙江桐乡民泰村镇银行', '15173354', '', '', '19', '9', '621338007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3226', '浙江龙泉民泰村镇银行', '15173439', '', '', '19', '9', '621338003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3227', '福建漳平民泰村镇银行', '15174056', '', '', '19', '9', '621338002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3228', '广州白云民泰村镇银行', '15175810', '', '借记卡', '19', '9', '621338010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3229', '重庆九龙坡民泰村镇银行', '15176900', '', '借记卡', '19', '9', '621338006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3230', '重庆彭水民泰村镇银行', '15176936', '', '', '19', '9', '621338009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3231', '沧州盐山新华村镇银行', '15181449', '', '新华卡', '19', '9', '621353113', '0');
INSERT INTO `authbank_cardbin` VALUES ('3232', '沧州海兴新华村镇银行', '15181453', '', '新华卡', '19', '9', '621353115', '0');
INSERT INTO `authbank_cardbin` VALUES ('3233', '大厂回族自治县新华村镇银行', '15181468', '', '新华卡', '19', '9', '621353117', '0');
INSERT INTO `authbank_cardbin` VALUES ('3234', '江西兴国新华村镇银行', '15184299', '', '新华卡', '19', '9', '621353106', '0');
INSERT INTO `authbank_cardbin` VALUES ('3235', '山东博兴新华村镇银行', '15184666', '', '新华卡', '19', '9', '621353109', '0');
INSERT INTO `authbank_cardbin` VALUES ('3236', '佛山南海新华村镇银行', '15185882', '', '新华卡', '19', '9', '621353110', '0');
INSERT INTO `authbank_cardbin` VALUES ('3237', '江门新会新华村镇银行', '15185897', '', '新华卡', '19', '9', '621353112', '0');
INSERT INTO `authbank_cardbin` VALUES ('3238', '东莞常平新华村镇银行', '15186020', '', '新华卡', '19', '9', '621353111', '0');
INSERT INTO `authbank_cardbin` VALUES ('3239', '兰州永登新华村镇银行', '15188211', '', '新华卡', '19', '9', '621353118', '0');
INSERT INTO `authbank_cardbin` VALUES ('3240', '兰州皋兰新华村镇银行', '15188212', '', '新华卡', '19', '9', '621353119', '0');
INSERT INTO `authbank_cardbin` VALUES ('3241', '兰州七里河新华村镇银行', '15188214', '', '新华卡', '19', '9', '621353120', '0');
INSERT INTO `authbank_cardbin` VALUES ('3242', '苏州吴江中银富登村镇银行', '15193050', '', '', '19', '9', '621356034', '0');
INSERT INTO `authbank_cardbin` VALUES ('3243', '响水中银富登村镇银行', '15193111', '', '', '19', '9', '621356040', '0');
INSERT INTO `authbank_cardbin` VALUES ('3244', '象山中银富登村镇银行', '15193321', '', '', '19', '9', '621356038', '0');
INSERT INTO `authbank_cardbin` VALUES ('3245', '临泉中银富登村镇银行', '15193724', '', '', '19', '9', '621356055', '0');
INSERT INTO `authbank_cardbin` VALUES ('3246', '太和中银富登村镇银行', '15193725', '', '', '19', '9', '621356046', '0');
INSERT INTO `authbank_cardbin` VALUES ('3247', '阜南中银富登村镇银行', '15193728', '', '借记卡', '19', '9', '621356044', '0');
INSERT INTO `authbank_cardbin` VALUES ('3248', '宜丰中银富登村镇银行', '15194318', '', '', '19', '9', '621356029', '0');
INSERT INTO `authbank_cardbin` VALUES ('3249', '上饶中银富登村镇银行', '15194332', '', '', '19', '9', '621356047', '0');
INSERT INTO `authbank_cardbin` VALUES ('3250', '泰和中银富登村镇银行', '15194358', '', '', '19', '9', '621356054', '0');
INSERT INTO `authbank_cardbin` VALUES ('3251', '安福中银富登村镇银行', '15194362', '', '', '19', '9', '621356027', '0');
INSERT INTO `authbank_cardbin` VALUES ('3252', '栖霞中银富登村镇银行', '15194563', '', '', '19', '9', '621356023', '0');
INSERT INTO `authbank_cardbin` VALUES ('3253', '汶上中银富登村镇银行', '15194617', '', '', '19', '9', '621356048', '0');
INSERT INTO `authbank_cardbin` VALUES ('3254', '巨野中银富登村镇银行', '15194756', '', '', '19', '9', '621356036', '0');
INSERT INTO `authbank_cardbin` VALUES ('3255', '东明中银富登村镇银行', '15194761', '', '', '19', '9', '621356049', '0');
INSERT INTO `authbank_cardbin` VALUES ('3256', '滑县中银富登村镇银行', '15194964', '', '', '19', '9', '621356020', '0');
INSERT INTO `authbank_cardbin` VALUES ('3257', '临颍中银富登村镇银行', '15195042', '', '', '19', '9', '621356019', '0');
INSERT INTO `authbank_cardbin` VALUES ('3258', '项城中银富登村镇银行', '15195091', '', '', '19', '9', '621356021', '0');
INSERT INTO `authbank_cardbin` VALUES ('3259', '南漳中银富登村镇银行', '15195283', '', '', '19', '9', '621356050', '0');
INSERT INTO `authbank_cardbin` VALUES ('3260', '沙洋中银富登村镇银行', '15195322', '', '', '19', '9', '621356045', '0');
INSERT INTO `authbank_cardbin` VALUES ('3261', '武穴中银富登村镇银行', '15195332', '', '', '19', '9', '621356062', '0');
INSERT INTO `authbank_cardbin` VALUES ('3262', '黄梅中银富登村镇银行', '15195339', '', '', '19', '9', '621356061', '0');
INSERT INTO `authbank_cardbin` VALUES ('3263', '公安中银富登村镇银行', '15195378', '', '', '19', '9', '621356042', '0');
INSERT INTO `authbank_cardbin` VALUES ('3264', '武胜中银富登村镇银行', '15196692', '', '', '19', '9', '621356053', '0');
INSERT INTO `authbank_cardbin` VALUES ('3265', '重庆合川中银富登村镇银行', '15196902', '', '', '19', '9', '621356037', '0');
INSERT INTO `authbank_cardbin` VALUES ('3266', '万州中银富登村镇银行', '15196903', '', '', '19', '9', '621356052', '0');
INSERT INTO `authbank_cardbin` VALUES ('3267', '重庆城口中银富登村镇银行', '15196922', '', '', '19', '9', '621356058', '0');
INSERT INTO `authbank_cardbin` VALUES ('3268', '重庆奉节中银富登村镇银行', '15196929', '', '', '19', '9', '621356041', '0');
INSERT INTO `authbank_cardbin` VALUES ('3269', '重庆巫山中银富登村镇银行', '15196931', '', '借记卡', '19', '9', '621356039', '0');
INSERT INTO `authbank_cardbin` VALUES ('3270', '凤翔中银富登村镇银行', '15197932', '', '', '19', '9', '621356057', '0');
INSERT INTO `authbank_cardbin` VALUES ('3271', '蒲城中银富登村镇银行', '15197977', '', '', '19', '9', '621356043', '0');
INSERT INTO `authbank_cardbin` VALUES ('3272', '浙江三门银座村镇银行', '15203450', '', '大唐IC贷记卡', '16', '8', '62515901', '1');
INSERT INTO `authbank_cardbin` VALUES ('3273', '深圳福田银座村镇银行', '15205840', '', '大唐IC借记卡', '19', '9', '621347101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3274', '北京怀柔融兴村镇银行', '15211039', '', '融兴普惠卡', '19', '9', '623589010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3275', '河间融惠村镇银行', '15211440', '', '融兴普惠卡', '19', '9', '623589020', '0');
INSERT INTO `authbank_cardbin` VALUES ('3276', '榆树融兴村镇银行', '15212419', '', '融兴普惠卡', '19', '9', '623589431', '0');
INSERT INTO `authbank_cardbin` VALUES ('3277', '延寿融兴村镇银行', '15212619', '', '融兴普惠卡', '19', '9', '623589001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3278', '巴彦融兴村镇银行', '15212620', '', '融兴普惠卡', '19', '9', '623589451', '0');
INSERT INTO `authbank_cardbin` VALUES ('3279', '拜泉融兴村镇银行', '15212659', '', '融兴普惠卡', '19', '9', '623589013', '0');
INSERT INTO `authbank_cardbin` VALUES ('3280', '桦川融兴村镇银行', '15212722', '', '融兴普惠卡', '19', '9', '623589005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3281', '江苏如东融兴村镇银行', '15213069', '', '融兴普惠卡', '19', '9', '623589009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3282', '安义融兴村镇银行', '15214219', '', '融兴普惠卡', '19', '9', '623589003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3283', '乐平融兴村镇银行', '15214229', '', '融兴普惠卡', '19', '9', '623589002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3284', '新安融兴村镇银行', '15214948', '', '融兴普惠卡', '19', '9', '623589004', '0');
INSERT INTO `authbank_cardbin` VALUES ('3285', '偃师融兴村镇银行', '15214949', '', '融兴普惠卡', '19', '9', '623589015', '0');
INSERT INTO `authbank_cardbin` VALUES ('3286', '应城融兴村镇银行', '15215354', '', '融兴普惠卡', '19', '9', '623589006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3287', '洪湖融兴村镇银行', '15215399', '', '融兴普惠卡', '19', '9', '623589011', '0');
INSERT INTO `authbank_cardbin` VALUES ('3288', '株洲县融兴村镇银行', '15215529', '', '融兴普惠卡', '19', '9', '623589016', '0');
INSERT INTO `authbank_cardbin` VALUES ('3289', '耒阳融兴村镇银行', '15215549', '', '融兴普惠卡', '19', '9', '623589007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3290', '深圳宝安融兴村镇银行', '15215841', '', '融兴普惠卡', '19', '9', '623589755', '0');
INSERT INTO `authbank_cardbin` VALUES ('3291', '海南保亭融兴村镇银行', '15216422', '', '融兴普惠卡', '19', '9', '623589017', '0');
INSERT INTO `authbank_cardbin` VALUES ('3292', '遂宁安居融兴村镇银行', '15216624', '', '融兴普惠卡', '19', '9', '623589014', '0');
INSERT INTO `authbank_cardbin` VALUES ('3293', '重庆市大渡口融兴村镇银行', '15216920', '', '融兴普惠卡', '19', '9', '623589012', '0');
INSERT INTO `authbank_cardbin` VALUES ('3294', '重庆市武隆融兴村镇银行', '15216930', '', '融兴普惠卡', '19', '9', '623589008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3295', '重庆市沙坪坝融兴村镇银行', '15216937', '', '融兴普惠卡', '19', '9', '623589019', '0');
INSERT INTO `authbank_cardbin` VALUES ('3296', '重庆市酉阳融兴村镇银行', '15216939', '', '融兴普惠卡', '19', '9', '623589018', '0');
INSERT INTO `authbank_cardbin` VALUES ('3297', '会宁会师村镇银行', '15218249', '', '会师普惠卡', '19', '9', '623589943', '0');
INSERT INTO `authbank_cardbin` VALUES ('3298', '南阳村镇银行', '15265130', '', '玉都一卡通', '18', '9', '621392887', '0');
INSERT INTO `authbank_cardbin` VALUES ('3299', '浙江瑞安湖商村镇银行', '15283339', '', '湖商卡', '19', '9', '621365012', '0');
INSERT INTO `authbank_cardbin` VALUES ('3300', '浙江海盐湖商村镇银行', '15283353', '', '湖商卡', '19', '9', '621365011', '0');
INSERT INTO `authbank_cardbin` VALUES ('3301', '安徽濉溪湖商卡村镇银行', '15283661', '', '湖商卡', '19', '9', '621365007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3302', '安徽宣州湖商村镇银行', '15283778', '', '湖商卡', '19', '9', '621365009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3303', '安徽涡阳湖商村镇银行', '15283811', '', '湖商卡', '19', '9', '621365008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3304', '安徽谯城湖商村镇银行', '15283815', '', '湖商卡', '19', '9', '621365010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3305', '福建连江恒欣村镇银行', '15293912', '', '恒欣卡', '19', '9', '621401001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3306', '福鼎恒兴村镇银行', '15294032', '', '', '19', '8', '62140101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3307', '福鼎恒兴村镇银行', '15294032', '', '', '19', '9', '621401002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3308', '吕梁孝义汇通村镇银行', '15301734', '', '', '19', '9', '623143003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3309', '晋中市左权华丰村镇银行', '15301753', '', '祝融卡', '19', '9', '623143001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3310', '汾西县亿通村镇银行', '15301788', '', '亿通卡', '19', '9', '623143002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3311', '新疆库尔勒富民村镇银行', '15318881', '', '', '18', '8', '62139383', '0');
INSERT INTO `authbank_cardbin` VALUES ('3312', '江苏大丰江南村镇银行', '15343116', '', '大丰江南卡', '19', '9', '621397101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3313', '浙江舟山普陀稠州村镇银行', '15353420', '', '银联义卡借记卡', '19', '9', '621627005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3314', '浙江岱山稠州村镇银行', '15353421', '', '', '19', '9', '621627002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3315', '龙泉驿稠州村镇银行', '15356510', '', '', '19', '9', '621627009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3316', '广西上林国民村镇银行', '15366124', '', '', '19', '9', '621635124', '0');
INSERT INTO `authbank_cardbin` VALUES ('3317', '广西浦北国民村镇银行', '15366315', '', '', '19', '9', '621635119', '0');
INSERT INTO `authbank_cardbin` VALUES ('3318', '防城港防城国民村镇银行', '15366320', '', '', '19', '9', '621635110', '0');
INSERT INTO `authbank_cardbin` VALUES ('3319', '邛崃国民村镇银行', '15366519', '', '蜜蜂磁条借记卡', '19', '9', '621635007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3320', '邛崃国民村镇银行', '15366519', '', '邛崃国民村镇银行蜜蜂借机IC卡', '19', '9', '621635107', '0');
INSERT INTO `authbank_cardbin` VALUES ('3321', '绿洲国民村镇银行', '15368810', '', '蜜蜂借记IC卡', '19', '9', '621635118', '0');
INSERT INTO `authbank_cardbin` VALUES ('3322', '克拉玛依金龙国民村镇银行', '15368820', '', '蜜蜂借记IC卡', '19', '9', '621635122', '0');
INSERT INTO `authbank_cardbin` VALUES ('3323', '博乐国民村镇银行', '15368871', '', '博乐国民村镇银行蜜蜂借记IC卡', '19', '9', '621635117', '0');
INSERT INTO `authbank_cardbin` VALUES ('3324', '库车国民村镇银行', '15368913', '', '蜜蜂借记IC卡', '19', '9', '621635125', '0');
INSERT INTO `authbank_cardbin` VALUES ('3325', '奎屯国民村镇银行', '15368981', '', '奎屯国民村镇银行蜜蜂借记卡', '19', '9', '621635123', '0');
INSERT INTO `authbank_cardbin` VALUES ('3326', '伊犁国民村镇银行', '15368991', '', '伊犁国民村镇银行蜜蜂借记IC卡', '19', '9', '621635120', '0');
INSERT INTO `authbank_cardbin` VALUES ('3327', '北屯国民村镇银行', '15369044', '', '北屯国民村镇银行蜜蜂借记卡', '19', '9', '621635116', '0');
INSERT INTO `authbank_cardbin` VALUES ('3328', '文昌国民村镇银行', '15386423', '', '赀业卡', '19', '9', '621650102', '0');
INSERT INTO `authbank_cardbin` VALUES ('3329', '琼海国民村镇银行', '15386424', '', '椰卡', '19', '9', '621650101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3330', '迁安襄隆村镇银行', '15391246', '', '襄隆汇通卡', '19', '9', '621630002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3331', '沙河襄通村镇银行', '15391322', '', '襄通卡', '19', '9', '621630003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3332', '清河金农村镇银行', '15391336', '', '金农汇通卡', '19', '9', '621630001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3333', '苏州吴中珠江村镇银行', '15403057', '', '珠江太阳卡', '18', '8', '62163122', '0');
INSERT INTO `authbank_cardbin` VALUES ('3334', '吉安吉州珠江村镇银行', '15404350', '', '珠江太阳卡', '18', '8', '62163112', '0');
INSERT INTO `authbank_cardbin` VALUES ('3335', '深圳坪山珠江村镇银行', '15405847', '', '珠江太阳卡', '18', '8', '62163125', '0');
INSERT INTO `authbank_cardbin` VALUES ('3336', '兴宁珠江村镇银行', '15405965', '', '珠江太阳卡', '18', '8', '62163123', '0');
INSERT INTO `authbank_cardbin` VALUES ('3337', '鄂托克旗汇泽村镇银行', '15422055', '', '金羊卡', '19', '8', '62163750', '0');
INSERT INTO `authbank_cardbin` VALUES ('3338', '丰城顺银村镇银行', '15454312', '', '', '19', '9', '621628661', '0');
INSERT INTO `authbank_cardbin` VALUES ('3339', '樟树顺银村镇银行', '15454313', '', '恒通卡', '19', '9', '621628662', '0');
INSERT INTO `authbank_cardbin` VALUES ('3340', '宜兴阳羡村镇银行', '15483020', '', '阳羡卡', '16', '9', '621355052', '0');
INSERT INTO `authbank_cardbin` VALUES ('3341', '昆山鹿城村镇银行', '15483050', '', '', '16', '9', '621355051', '0');
INSERT INTO `authbank_cardbin` VALUES ('3342', '襄垣县融汇村镇银行', '15501663', '', '', '19', '8', '62309702', '0');
INSERT INTO `authbank_cardbin` VALUES ('3343', '五台莱商村镇银行', '15511713', '', '金莲卡', '16', '9', '621656003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3344', '东营莱商村镇银行', '15514550', '', '', '19', '8', '62165666', '0');
INSERT INTO `authbank_cardbin` VALUES ('3345', '舒兰吉银村镇银行', '15522422', '', '', '19', '9', '621659009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3346', '双辽吉银村镇银行', '15522433', '', '长白山卡', '19', '9', '621659008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3347', '东丰吉银村镇银行', '15522441', '', '', '19', '9', '621659010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3348', '珲春吉银村镇银行', '15522494', '', '长白山卡', '19', '9', '621659003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3349', '镇江润州长江村镇银行', '15533145', '', '长江卡', '19', '9', '621676014', '0');
INSERT INTO `authbank_cardbin` VALUES ('3350', '湖北红安长江村镇银行股份有限公司', '15535334', '', '长江卡', '19', '9', '621676009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3351', '湖北英山长江村镇银行股份有限公司', '15535336', '', '长江卡', '19', '9', '621676010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3352', '汕头龙湖长江村镇银行', '15535864', '', '长江卡', '19', '9', '621676006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3353', '茂名电白长江村镇银行', '15535923', '', '长江卡', '19', '9', '621676013', '0');
INSERT INTO `authbank_cardbin` VALUES ('3354', '惠州博罗长江村镇银行', '15535952', '', '长江卡', '19', '9', '621676012', '0');
INSERT INTO `authbank_cardbin` VALUES ('3355', '东莞虎门长江村镇银行', '15536020', '', '长江卡', '19', '9', '621676011', '0');
INSERT INTO `authbank_cardbin` VALUES ('3356', '潮州潮安长江村镇银行', '15536041', '', '长江卡', '19', '9', '621676008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3357', '海南五指山长江村镇银行股份有限公司', '15536421', '', '长江卡', '19', '9', '621676005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3358', '海南澄迈长江村镇银行股份有限公司', '15536428', '', '长江卡', '19', '9', '621676004', '0');
INSERT INTO `authbank_cardbin` VALUES ('3359', '昆明五华长江村镇银行', '15537322', '', '', '19', '9', '621676015', '0');
INSERT INTO `authbank_cardbin` VALUES ('3360', '曲靖宣威长江村镇银行', '15537363', '', '', '19', '9', '621676019', '0');
INSERT INTO `authbank_cardbin` VALUES ('3361', '曲靖会泽长江村镇银行', '15537369', '', '长江卡', '19', '9', '621676020', '0');
INSERT INTO `authbank_cardbin` VALUES ('3362', '西双版纳勐海长江村镇银行', '15537492', '', '长江卡', '19', '9', '621676018', '0');
INSERT INTO `authbank_cardbin` VALUES ('3363', '丽水永胜长江村镇银行', '15537552', '', '长江卡', '19', '9', '621676016', '0');
INSERT INTO `authbank_cardbin` VALUES ('3364', '丽江玉龙长江村镇银行', '15537555', '', '长江卡', '19', '9', '621676017', '0');
INSERT INTO `authbank_cardbin` VALUES ('3365', '浙江上虞富民村镇银行', '15543376', '', '上虞富民村镇银行富民卡', '19', '9', '621678105', '0');
INSERT INTO `authbank_cardbin` VALUES ('3366', '浙江遂昌富民村镇银行', '15543436', '', '借记卡', '19', '9', '621678101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3367', '浙江台州路桥富民村镇银行', '15543453', '', '路桥富民村镇银行富民IC卡', '19', '9', '621678106', '0');
INSERT INTO `authbank_cardbin` VALUES ('3368', '萍乡安源富民村镇银行', '15544230', '', '借记卡', '19', '9', '621678001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3369', '江西上栗富民村镇银行', '15544232', '', '', '19', '9', '621678002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3370', '贵阳乌当富民村镇银行', '15547010', '', '富民卡', '19', '9', '621678208', '0');
INSERT INTO `authbank_cardbin` VALUES ('3371', '开阳富民村镇银行', '15547011', '', '富民卡', '19', '9', '621678213', '0');
INSERT INTO `authbank_cardbin` VALUES ('3372', '贵阳观山湖富民村镇银行', '15547015', '', '富民卡', '19', '9', '621678207', '0');
INSERT INTO `authbank_cardbin` VALUES ('3373', '贵阳云岩富民村镇银行', '15547016', '', '富民卡', '19', '9', '621678209', '0');
INSERT INTO `authbank_cardbin` VALUES ('3374', '贵阳南明富民村镇银行', '15547017', '', '富民卡', '19', '9', '621678211', '0');
INSERT INTO `authbank_cardbin` VALUES ('3375', '遵义红花岗富民村镇银行', '15547030', '', '富民卡', '19', '9', '621678212', '0');
INSERT INTO `authbank_cardbin` VALUES ('3376', '大方富民村镇银行', '15547092', '', '富民卡', '19', '9', '621678202', '0');
INSERT INTO `authbank_cardbin` VALUES ('3377', '金沙富民村镇银行', '15547094', '', '富民卡', '19', '9', '621678201', '0');
INSERT INTO `authbank_cardbin` VALUES ('3378', '纳雍富民村镇银行', '15547096', '', '富民卡', '19', '9', '621678203', '0');
INSERT INTO `authbank_cardbin` VALUES ('3379', '威宁富民村镇银行', '15547097', '', '富民卡', '19', '9', '621678205', '0');
INSERT INTO `authbank_cardbin` VALUES ('3380', '赫章富民村镇银行', '15547098', '', '富民卡', '19', '9', '621678206', '0');
INSERT INTO `authbank_cardbin` VALUES ('3381', '安顺西秀富民村镇银行', '15547123', '', '借记卡', '19', '9', '621678210', '0');
INSERT INTO `authbank_cardbin` VALUES ('3382', '福泉富民村镇银行', '15547154', '', '富民卡', '19', '9', '621678215', '0');
INSERT INTO `authbank_cardbin` VALUES ('3383', '张家港渝农商村镇银行', '15563056', '', '', '16', '9', '621680102', '0');
INSERT INTO `authbank_cardbin` VALUES ('3384', '福建平潭渝农商村镇银行', '15563918', '', '', '16', '9', '621680107', '0');
INSERT INTO `authbank_cardbin` VALUES ('3385', '福建沙县渝农商村镇银行', '15563956', '', '', '16', '9', '621680109', '0');
INSERT INTO `authbank_cardbin` VALUES ('3386', '福建福安渝农商村镇银行', '15564034', '', '', '16', '9', '621680110', '0');
INSERT INTO `authbank_cardbin` VALUES ('3387', '广西鹿寨渝农商村镇银行', '15566152', '', '', '16', '9', '621680105', '0');
INSERT INTO `authbank_cardbin` VALUES ('3388', '大竹渝农商村镇银行', '15566761', '', '', '16', '9', '621680103', '0');
INSERT INTO `authbank_cardbin` VALUES ('3389', '云南大理渝农商村镇银行', '15567511', '', '', '16', '9', '621680104', '0');
INSERT INTO `authbank_cardbin` VALUES ('3390', '云南祥云渝农商村镇银行', '15567513', '', '', '16', '9', '621680106', '0');
INSERT INTO `authbank_cardbin` VALUES ('3391', '云南鹤庆渝农商村镇银行', '15567523', '', '', '16', '9', '621680108', '0');
INSERT INTO `authbank_cardbin` VALUES ('3392', '云南香格里拉渝农商村镇银行', '15567571', '', '', '16', '9', '621680111', '0');
INSERT INTO `authbank_cardbin` VALUES ('3393', '辽宁彰武金通村镇银行股份有限公司', '15572292', '', '鑫荷卡', '18', '9', '621681002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3394', '铁岭农商村镇银行', '15572330', '', '', '18', '9', '621681003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3395', '济南长清沪农商村镇银行', '15584510', '', '', '19', '9', '621682803', '0');
INSERT INTO `authbank_cardbin` VALUES ('3396', '瑞丽沪农商村镇银行', '15587546', '', '', '19', '9', '621682830', '0');
INSERT INTO `authbank_cardbin` VALUES ('3397', '广东澄海潮商村镇银行', '15595866', '', '潮商卡', '19', '9', '621685701', '0');
INSERT INTO `authbank_cardbin` VALUES ('3398', '广东普宁汇成村镇银行', '15596054', '', '铁山兰花卡', '19', '9', '621685702', '0');
INSERT INTO `authbank_cardbin` VALUES ('3399', '丹东鼎元村镇银行', '15612265', '', '鼎元一卡通', '19', '8', '62169301', '0');
INSERT INTO `authbank_cardbin` VALUES ('3400', '丹东鼎安村镇银行', '15612266', '', '鼎安杜鹃卡', '19', '8', '62169302', '0');
INSERT INTO `authbank_cardbin` VALUES ('3401', '丹东福汇村镇银行', '15612267', '', '江城卡', '19', '8', '62169303', '0');
INSERT INTO `authbank_cardbin` VALUES ('3402', '天津宁河村镇银行', '15641101', '', '同心卡', '18', '8', '62169757', '0');
INSERT INTO `authbank_cardbin` VALUES ('3403', '山东兰陵村镇银行股份有限公司', '15644734', '', '同心卡', '18', '8', '62169716', '0');
INSERT INTO `authbank_cardbin` VALUES ('3404', '尉氏合益村镇银行', '15654923', '', '合益卡', '18', '8', '62311703', '0');
INSERT INTO `authbank_cardbin` VALUES ('3405', '楚雄兴彝村镇银行', '15687381', '', '兴彝卡', '19', '8', '62317502', '0');
INSERT INTO `authbank_cardbin` VALUES ('3406', '玉溪红塔区兴和村镇银行', '15687410', '', '新兴卡', '19', '8', '62317501', '0');
INSERT INTO `authbank_cardbin` VALUES ('3407', '遂平恒生村镇银行', '15705114', '', '乡缘卡', '19', '9', '621380001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3408', '韶山光大村镇银行', '15725533', '', '韶山光大村镇银行阳光借记卡', '19', '9', '621381001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3409', '浙江泰顺温银村镇银行', '15743338', '', '', '18', '8', '62319563', '0');
INSERT INTO `authbank_cardbin` VALUES ('3410', '萍乡湘东黄海村镇银行', '15794230', '', '', '19', '9', '621265001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3411', '滨海惠民村镇银行', '15801127', '', '', '18', '8', '62127852', '0');
INSERT INTO `authbank_cardbin` VALUES ('3412', '文安县惠民村镇银行股份有限公司', '15801466', '', '', '18', '8', '62127834', '0');
INSERT INTO `authbank_cardbin` VALUES ('3413', '廊坊市安次区惠民村镇银行', '15801469', '', '', '18', '8', '62127843', '0');
INSERT INTO `authbank_cardbin` VALUES ('3414', '安平惠民村镇银行', '15801488', '', '', '18', '9', '621278193', '0');
INSERT INTO `authbank_cardbin` VALUES ('3415', '长春南关惠民村镇银行', '15802416', '', '', '18', '8', '62127825', '0');
INSERT INTO `authbank_cardbin` VALUES ('3416', '桦甸惠民村镇银行', '15802425', '', '桦卡', '18', '9', '621278503', '0');
INSERT INTO `authbank_cardbin` VALUES ('3417', '吉林丰满惠民村镇银行', '15802426', '', '丰惠卡', '18', '8', '62127839', '0');
INSERT INTO `authbank_cardbin` VALUES ('3418', '大安惠民村镇银行', '15802474', '', '', '19', '8', '62127823', '0');
INSERT INTO `authbank_cardbin` VALUES ('3419', '乾安惠民村镇银行', '15802514', '', '', '18', '8', '62127822', '0');
INSERT INTO `authbank_cardbin` VALUES ('3420', '松原宁江惠民村镇银行', '15802515', '', '', '19', '8', '62127821', '0');
INSERT INTO `authbank_cardbin` VALUES ('3421', '双城惠民村镇银行有限责任公司', '15802621', '', '', '18', '8', '62127828', '0');
INSERT INTO `authbank_cardbin` VALUES ('3422', '五常惠民村镇银行', '15802624', '', '', '18', '8', '62127820', '0');
INSERT INTO `authbank_cardbin` VALUES ('3423', '庐江惠民村镇银行', '15803614', '', '', '18', '8', '62127836', '0');
INSERT INTO `authbank_cardbin` VALUES ('3424', '含山惠民村镇银行', '15803652', '', '', '18', '8', '62127837', '0');
INSERT INTO `authbank_cardbin` VALUES ('3425', '青岛平度惠民村镇银行', '15804524', '', '', '18', '8', '62127831', '0');
INSERT INTO `authbank_cardbin` VALUES ('3426', '荆门东宝惠民村镇银行', '15805324', '', '', '18', '8', '62127855', '0');
INSERT INTO `authbank_cardbin` VALUES ('3427', '广州萝岗惠民村镇银行', '15805810', '', '木棉金惠卡', '18', '8', '62127810', '0');
INSERT INTO `authbank_cardbin` VALUES ('3428', '雷州惠民村镇银行', '15805914', '', '雷州古韵卡', '18', '8', '62127816', '0');
INSERT INTO `authbank_cardbin` VALUES ('3429', '惠东惠民村镇银行', '15805953', '', '双月湾卡', '18', '8', '62127817', '0');
INSERT INTO `authbank_cardbin` VALUES ('3430', '五华惠民村镇银行', '15805964', '', '诚惠卡', '18', '8', '62127812', '0');
INSERT INTO `authbank_cardbin` VALUES ('3431', '清远清新惠民村镇银行', '15806017', '', '禾雀清惠卡', '18', '8', '62127815', '0');
INSERT INTO `authbank_cardbin` VALUES ('3432', '云安惠民村镇银行', '15806063', '', '', '18', '8', '62127813', '0');
INSERT INTO `authbank_cardbin` VALUES ('3433', '合阳惠民村镇银行', '15807970', '', '旭日东升卡', '18', '8', '62127818', '0');
INSERT INTO `authbank_cardbin` VALUES ('3434', '青县青隆村镇银行', '15821432', '', '', '19', '8', '62138402', '0');
INSERT INTO `authbank_cardbin` VALUES ('3435', '东光青隆村镇银行', '15821447', '', '', '19', '8', '62138404', '0');
INSERT INTO `authbank_cardbin` VALUES ('3436', '黄骅青隆村镇银行', '15821451', '', '', '19', '8', '62138403', '0');
INSERT INTO `authbank_cardbin` VALUES ('3437', '沧州市运河青隆村镇银行', '15821454', '', '', '19', '8', '62138401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3438', '山东邹平青隆村镇银行', '15824667', '', '', '19', '9', '621384001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3439', '江苏海安盐海村镇银行', '15843061', '', '', '19', '9', '621386002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3440', '辽宁黑山锦行村镇银行', '15852274', '', '7777卡', '19', '9', '621699005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3441', '哈密天山村镇银行', '15868841', '', '金瓜卡', '19', '9', '623601001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3442', '南京浦口靖发村镇银行', '15873010', '', '阳光卡', '19', '9', '623604001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3443', '滦县中成村镇银行', '15891243', '', '', '19', '8', '62360636', '0');
INSERT INTO `authbank_cardbin` VALUES ('3444', '滦南中成村镇银行', '15891244', '', '', '19', '8', '62360637', '0');
INSERT INTO `authbank_cardbin` VALUES ('3445', '定州中成村镇银行', '15891351', '', '', '19', '8', '62360634', '0');
INSERT INTO `authbank_cardbin` VALUES ('3446', '涿州中成村镇银行', '15891352', '', '', '19', '8', '62360630', '0');
INSERT INTO `authbank_cardbin` VALUES ('3447', '望都中成村镇银行', '15891359', '', '', '19', '8', '62360632', '0');
INSERT INTO `authbank_cardbin` VALUES ('3448', '高碑店中成村镇银行', '15891366', '', '', '19', '8', '62360631', '0');
INSERT INTO `authbank_cardbin` VALUES ('3449', '曲阳中成村镇银行', '15891367', '', '', '19', '8', '62360633', '0');
INSERT INTO `authbank_cardbin` VALUES ('3450', '安国中成村镇银行', '15891369', '', '', '19', '8', '62360635', '0');
INSERT INTO `authbank_cardbin` VALUES ('3451', '宣化中成村镇银行', '15891381', '', '', '19', '8', '62360638', '0');
INSERT INTO `authbank_cardbin` VALUES ('3452', '常州新北中成村镇银行', '15893044', '', '', '19', '8', '62360602', '0');
INSERT INTO `authbank_cardbin` VALUES ('3453', '建湖中成村镇银行', '15893115', '', '', '19', '8', '62360603', '0');
INSERT INTO `authbank_cardbin` VALUES ('3454', '扬州广陵中成村镇银行', '15893130', '', '', '19', '8', '62360629', '0');
INSERT INTO `authbank_cardbin` VALUES ('3455', '惠安中成村镇银行', '15893971', '', '', '19', '8', '62360625', '0');
INSERT INTO `authbank_cardbin` VALUES ('3456', '南靖中成村镇银行', '15893997', '', '', '19', '8', '62360626', '0');
INSERT INTO `authbank_cardbin` VALUES ('3457', '浦城中成村镇银行', '15894016', '', '', '19', '8', '62360628', '0');
INSERT INTO `authbank_cardbin` VALUES ('3458', '上杭中成村镇银行', '15894054', '', '', '19', '8', '62360627', '0');
INSERT INTO `authbank_cardbin` VALUES ('3459', '青岛胶州中成村镇银行', '15894525', '', '', '19', '8', '62360601', '0');
INSERT INTO `authbank_cardbin` VALUES ('3460', '东营河口中成村镇银行', '15894554', '', '', '19', '8', '62360620', '0');
INSERT INTO `authbank_cardbin` VALUES ('3461', '奎文中成村镇银行', '15894590', '', '', '19', '8', '62360622', '0');
INSERT INTO `authbank_cardbin` VALUES ('3462', '衮州中成村镇银行', '15894622', '', '', '19', '8', '62360624', '0');
INSERT INTO `authbank_cardbin` VALUES ('3463', '无棣中成村镇银行', '15894664', '', '', '19', '8', '62360623', '0');
INSERT INTO `authbank_cardbin` VALUES ('3464', '莱芜中成村镇银行', '15894791', '', '', '19', '8', '62360621', '0');
INSERT INTO `authbank_cardbin` VALUES ('3465', '自贡中成村镇银行', '15896555', '', '', '19', '8', '62360600', '0');
INSERT INTO `authbank_cardbin` VALUES ('3466', '犍为中成村镇银行', '15896653', '', '', '19', '8', '62360607', '0');
INSERT INTO `authbank_cardbin` VALUES ('3467', '峨嵋山中成村镇银行', '15896664', '', '', '19', '8', '62360608', '0');
INSERT INTO `authbank_cardbin` VALUES ('3468', '长宁中成村镇银行', '15896715', '', '', '19', '8', '62360605', '0');
INSERT INTO `authbank_cardbin` VALUES ('3469', '筠连中成村镇银行', '15896717', '', '', '19', '8', '62360604', '0');
INSERT INTO `authbank_cardbin` VALUES ('3470', '南部县中成村镇银行', '15896734', '', '', '19', '8', '62360606', '0');
INSERT INTO `authbank_cardbin` VALUES ('3471', '南充嘉陵中成村镇银行', '15896744', '', '', '19', '8', '62360609', '0');
INSERT INTO `authbank_cardbin` VALUES ('3472', '昆明马金铺中成村镇银行', '15897310', '', '借记卡', '19', '8', '62360612', '0');
INSERT INTO `authbank_cardbin` VALUES ('3473', '昆明东川中成村镇银行', '15897311', '', '', '19', '8', '62360613', '0');
INSERT INTO `authbank_cardbin` VALUES ('3474', '昆明石林中成村镇银行', '15897316', '', '', '19', '8', '62360610', '0');
INSERT INTO `authbank_cardbin` VALUES ('3475', '昆明禄劝中成村镇银行', '15897318', '', '', '19', '8', '62360614', '0');
INSERT INTO `authbank_cardbin` VALUES ('3476', '昆明寻甸中成村镇银行', '15897319', '', '借记卡', '19', '8', '62360615', '0');
INSERT INTO `authbank_cardbin` VALUES ('3477', '玉溪澄江中成村镇银行', '15897413', '', '', '19', '8', '62360616', '0');
INSERT INTO `authbank_cardbin` VALUES ('3478', '大理古城中成村镇银行', '15897511', '', '', '19', '8', '62360611', '0');
INSERT INTO `authbank_cardbin` VALUES ('3479', '泸水中成村镇银行', '15897561', '', '', '19', '8', '62360617', '0');
INSERT INTO `authbank_cardbin` VALUES ('3480', '鄯善中成村镇银行', '15898832', '', '鄯善中成村镇银行金融', '19', '8', '62360618', '0');
INSERT INTO `authbank_cardbin` VALUES ('3481', '富蕴中成村镇银行', '15899023', '', '', '19', '8', '62360619', '0');
INSERT INTO `authbank_cardbin` VALUES ('3482', '江苏沛县汉源村镇银行', '15933032', '', '', '19', '9', '623609001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3483', '江苏新沂汉源村镇银行', '15933036', '', '', '19', '9', '623609002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3484', '西安高陵阳光村镇银行', '15947916', '', '金丝路阳光卡', '18', '8', '62361027', '0');
INSERT INTO `authbank_cardbin` VALUES ('3485', '陕西洛南阳光村镇银行', '15948032', '', '金丝路阳光卡', '18', '8', '62361028', '0');
INSERT INTO `authbank_cardbin` VALUES ('3486', '浙江缙云杭银村镇银行', '15953435', '', '', '18', '9', '623502001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3487', '江苏邳州陇海村镇银行股份有限公司', '15963035', '', '陇海卡', '19', '9', '623512001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3488', '定安合丰村镇银行', '15976426', '', '合丰卡', '19', '9', '623045001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3489', '澳洲联邦银行（辛集）村镇银行', '15991221', '', '', '19', '9', '623607006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3490', '澳洲联邦银行（磁县）村镇银行', '15991291', '', '', '19', '9', '623607008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3491', '澳洲联邦银行（永年）村镇银行', '15991293', '', '', '19', '9', '623607007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3492', '澳洲联邦银行登封村镇银行', '15994915', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3493', '澳洲联邦村镇银行兰考村镇银行', '15994925', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3494', '澳洲联邦村镇银行伊川村镇银行', '15994939', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607004', '0');
INSERT INTO `authbank_cardbin` VALUES ('3495', '澳洲联邦村镇银行温县村镇银行', '15995015', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3496', '澳洲联邦村镇银行济源村镇银行', '15995017', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3497', '澳洲联邦银行(渑池)村镇银行', '15995051', '', '澳洲联邦银行村镇银行借记卡', '19', '9', '623607005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3498', '澳洲联邦银行(永城)村镇银行', '15995069', '', '澳洲联邦村镇银行借记卡', '19', '9', '623607009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3499', '天津滨海德商村镇银行', '16011127', '', '', '19', '9', '623504010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3500', '余杭德商村镇银行', '16013310', '', '德商卡', '19', '9', '623504002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3501', '海宁德商村镇银行', '16013355', '', '', '19', '9', '623504008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3502', '浙江秀洲德商村镇银行', '16013356', '', '', '19', '9', '623504004', '0');
INSERT INTO `authbank_cardbin` VALUES ('3503', '浙江定海德商村镇银行', '16013423', '', '', '19', '9', '623504009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3504', '范县德商村镇银行', '16015023', '', '借记卡', '19', '9', '623504001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3505', '民权德商村镇银行', '16015064', '', '借记卡', '19', '9', '623504003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3506', '宁陵德商村镇银行', '16015065', '', '', '19', '9', '623504006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3507', '睢县德商村镇银行', '16015066', '', '德商卡', '19', '9', '623504007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3508', '安徽宿松民丰村镇银行', '16023686', '', '', '19', '8', '62168309', '0');
INSERT INTO `authbank_cardbin` VALUES ('3509', '山东梁山民丰村镇银行', '16024621', '', '金鼎卡', '19', '8', '62168306', '0');
INSERT INTO `authbank_cardbin` VALUES ('3510', '庆阳市西峰瑞信村镇银行', '16038349', '', '龙凤卡', '18', '9', '623519533', '0');
INSERT INTO `authbank_cardbin` VALUES ('3511', '浙江永嘉恒升村镇银行', '16043334', '', '恒升卡', '19', '8', '62352501', '0');
INSERT INTO `authbank_cardbin` VALUES ('3512', '惠水恒升村镇银行', '16047162', '', '恒升卡', '19', '9', '623525001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3513', '香河益民村镇银行', '16091464', '', '益通卡', '18', '9', '623530953', '0');
INSERT INTO `authbank_cardbin` VALUES ('3514', '江苏洪泽金阳光村镇银行', '16103087', '', '金阳光卡', '19', '9', '623532001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3515', '大洼恒丰村镇银行', '16122321', '', '借记卡', '18', '8', '62314061', '0');
INSERT INTO `authbank_cardbin` VALUES ('3516', '盘山安泰村镇银行股份有限公司', '16122322', '', '', '18', '8', '62314062', '0');
INSERT INTO `authbank_cardbin` VALUES ('3517', '浙江庆元泰隆村镇银行', '16133434', '', '浙江庆元泰隆村镇银行泰隆借记卡', '16', '9', '623535002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3518', '湖北大冶泰隆村镇银行', '16135221', '', '', '16', '9', '623535001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3519', '浙江龙游义商村镇银行', '16153414', '', '义商借记卡', '19', '9', '623538001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3520', '汝州玉川村镇银行', '16184956', '', '玉川卡', '18', '8', '62355001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3521', '沂源博商村镇银行', '16194533', '', '借记卡', '18', '8', '62355358', '0');
INSERT INTO `authbank_cardbin` VALUES ('3522', '北京大兴华夏村镇银行', '16201000', '', '梦卡', '16', '8', '62355701', '0');
INSERT INTO `authbank_cardbin` VALUES ('3523', '四川江油华夏村镇银行', '16206597', '', '', '16', '8', '62355702', '0');
INSERT INTO `authbank_cardbin` VALUES ('3524', '昆明呈贡华夏村镇银行', '16207321', '', '花都卡', '16', '8', '62355703', '0');
INSERT INTO `authbank_cardbin` VALUES ('3525', '浙江富阳恒通村镇银行', '16213312', '', '惠通卡', '19', '9', '623558618', '0');
INSERT INTO `authbank_cardbin` VALUES ('3526', '浙江松阳恒通村镇银行', '16213437', '', '惠通卡', '18', '8', '62355865', '0');
INSERT INTO `authbank_cardbin` VALUES ('3527', '鹰潭月湖恒通村镇银行', '16214273', '', '惠通卡', '19', '9', '623558611', '0');
INSERT INTO `authbank_cardbin` VALUES ('3528', '横峰恒通村镇银行', '16214336', '', '惠通卡', '19', '9', '623558616', '0');
INSERT INTO `authbank_cardbin` VALUES ('3529', '余干恒通村镇银行', '16214338', '', '惠通卡', '19', '9', '623558612', '0');
INSERT INTO `authbank_cardbin` VALUES ('3530', '太原市尖草坪区信都村镇银行', '16221615', '', '', '19', '8', '62357012', '0');
INSERT INTO `authbank_cardbin` VALUES ('3531', '大同市南郊区京都村镇银行', '16221638', '', '', '19', '8', '62357009', '0');
INSERT INTO `authbank_cardbin` VALUES ('3532', '代县泓都村镇银行', '16221715', '', '', '19', '8', '62357010', '0');
INSERT INTO `authbank_cardbin` VALUES ('3533', '忻州市忻府区秀都村镇银行', '16221726', '', '', '19', '8', '62357006', '0');
INSERT INTO `authbank_cardbin` VALUES ('3534', '汾阳市九都村镇银行有限公司', '16221731', '', '', '19', '8', '62357002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3535', '文水县润都村镇银行', '16221732', '', '', '19', '8', '62357008', '0');
INSERT INTO `authbank_cardbin` VALUES ('3536', '临县泉都村镇银行', '16221736', '', '', '19', '8', '62357011', '0');
INSERT INTO `authbank_cardbin` VALUES ('3537', '交口融都村镇银行', '16221744', '', '', '19', '8', '62357007', '0');
INSERT INTO `authbank_cardbin` VALUES ('3538', '寿阳县汇都村镇银行', '16221756', '', '', '19', '8', '62357004', '0');
INSERT INTO `authbank_cardbin` VALUES ('3539', '介休市华都村镇银行', '16221761', '', '', '19', '8', '62357003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3540', '襄汾万都村镇银行', '16221775', '', '', '19', '8', '62357001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3541', '洪洞县洪都村镇银行', '16221776', '', '', '19', '8', '62357005', '0');
INSERT INTO `authbank_cardbin` VALUES ('3542', '费县梁邹村镇银行', '16234742', '', '', '18', '8', '62359430', '0');
INSERT INTO `authbank_cardbin` VALUES ('3543', '辽宁海城金海村镇银行', '16242232', '', '金海卡', '18', '8', '62368001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3544', '辽宁岫岩金玉村镇银行', '16242233', '', '金玉卡', '18', '8', '62368002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3545', '辽宁千山金泉村镇银行', '16242237', '', '金泉卡', '18', '8', '62368003', '0');
INSERT INTO `authbank_cardbin` VALUES ('3546', '宁夏中宁青银村镇银行', '16308751', '', '红枸杞金融IC借记卡', '19', '8', '62358101', '0');
INSERT INTO `authbank_cardbin` VALUES ('3547', '浙江玉环永兴村镇银行', '16323458', '', '', '19', '9', '623579001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3548', '铁岭新星村镇银行', '16342330', '', '', '18', '8', '62358563', '0');
INSERT INTO `authbank_cardbin` VALUES ('3549', '无为徽银村镇银行', '16353624', '', '徽银卡', '19', '9', '623587001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3550', '金寨徽银村镇银行', '16353766', '', '黄山卡', '19', '9', '623587002', '0');
INSERT INTO `authbank_cardbin` VALUES ('3551', '调兵山惠民村镇银行', '16362336', '', '调兵山惠民村镇银行惠民卡', '18', '8', '62359065', '0');
INSERT INTO `authbank_cardbin` VALUES ('3552', '庄河汇通村镇银行', '16372223', '', '汇通IC卡', '19', '8', '62361702', '0');
INSERT INTO `authbank_cardbin` VALUES ('3553', '大连经济技术开发区鑫汇村镇银行', '16372371', '', '鑫汇借记卡', '19', '8', '62361703', '0');
INSERT INTO `authbank_cardbin` VALUES ('3554', '江苏通州华商村镇银行', '16393067', '', '紫琅卡', '19', '6', '621389', '0');
INSERT INTO `authbank_cardbin` VALUES ('3555', '乐亭舜丰村镇银行', '16401245', '', '', '18', '8', '62359364', '0');
INSERT INTO `authbank_cardbin` VALUES ('3556', '唐山市丰南舜丰村镇银行', '16401251', '', '舜丰卡，唐人卡', '18', '8', '62359354', '0');
INSERT INTO `authbank_cardbin` VALUES ('3557', '大城舜丰村镇银行', '16401465', '', '', '18', '8', '62359356', '0');
INSERT INTO `authbank_cardbin` VALUES ('3558', '霸州舜丰村镇银行', '16401467', '', '', '18', '8', '62359350', '0');
INSERT INTO `authbank_cardbin` VALUES ('3559', '广阳舜丰村镇银行', '16401471', '', '', '18', '8', '62359353', '0');
INSERT INTO `authbank_cardbin` VALUES ('3560', '青岛黄岛舜丰村镇银行', '16404527', '', '向阳卡青年卡舜丰卡', '18', '8', '62359329', '0');
INSERT INTO `authbank_cardbin` VALUES ('3561', '山东利津舜丰村镇银行', '16404552', '', '金桥卡，金凤卡，舜丰卡', '18', '8', '62359349', '0');
INSERT INTO `authbank_cardbin` VALUES ('3562', '山东惠民舜丰村镇银行', '16404662', '', '惠民卡,舜丰卡', '18', '8', '62359351', '0');
INSERT INTO `authbank_cardbin` VALUES ('3563', '黔西花都村镇银行', '16427093', '', '杜鹃卡', '18', '8', '62144735', '0');
INSERT INTO `authbank_cardbin` VALUES ('3564', '中山古镇南粤村镇银行', '16436030', '', '灯都卡', '19', '8', '62006401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3565', '元氏信融村镇银行', '16441229', '', '元氏信融村镇银行信融卡', '19', '9', '621658001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3566', '元氏信融村镇银行', '16441229', '', '信融卡', '18', '8', '62165826', '0');
INSERT INTO `authbank_cardbin` VALUES ('3567', '朝阳柳城村镇银行', '16462347', '', '龙翔卡', '19', '8', '62300401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3568', '修文江海村镇银行', '16497013', '', '', '19', '8', '62310402', '0');
INSERT INTO `authbank_cardbin` VALUES ('3569', '贵阳小河科技村镇银行', '16497018', '', '', '19', '8', '62310401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3570', '垦利乐安村镇银行', '16524551', '', '', '18', '8', '62314292', '0');
INSERT INTO `authbank_cardbin` VALUES ('3571', '莱阳胶东村镇银行', '16524568', '', '竹卡', '18', '8', '62361528', '0');
INSERT INTO `authbank_cardbin` VALUES ('3572', '牟平胶东村镇银行', '16524571', '', '竹卡', '18', '8', '62361527', '0');
INSERT INTO `authbank_cardbin` VALUES ('3573', '夏津胶东村镇银行', '16524685', '', '竹卡', '18', '8', '62361523', '0');
INSERT INTO `authbank_cardbin` VALUES ('3574', '齐河胶东村镇银行', '16524687', '', '竹卡', '18', '8', '62361522', '0');
INSERT INTO `authbank_cardbin` VALUES ('3575', '禹城胶东村镇银行', '16524688', '', '竹卡', '18', '8', '62361525', '0');
INSERT INTO `authbank_cardbin` VALUES ('3576', '宁津胶东村镇银行', '16524693', '', '竹卡', '18', '8', '62361526', '0');
INSERT INTO `authbank_cardbin` VALUES ('3577', '许昌新浦村镇银行', '16535032', '', '金鸡卡', '18', '9', '623194773', '0');
INSERT INTO `authbank_cardbin` VALUES ('3578', '北京昌平包商村镇银行', '16561000', '', '北京昌平包商村镇银行润禾卡', '19', '9', '621382076', '0');
INSERT INTO `authbank_cardbin` VALUES ('3579', '天津津南村镇银行', '16561100', '', '天津津南村镇银行润禾卡', '19', '9', '621382070', '0');
INSERT INTO `authbank_cardbin` VALUES ('3580', '清徐惠民村镇银行', '16561611', '', '清徐惠民村镇银行润禾卡', '19', '9', '621382053', '0');
INSERT INTO `authbank_cardbin` VALUES ('3581', '固阳包商惠农村镇银行', '16561922', '', '固阳包商惠农村镇银行润禾卡', '19', '9', '621382066', '0');
INSERT INTO `authbank_cardbin` VALUES ('3582', '宁城包商村镇银行', '16561948', '', '宁城包商村镇银行润禾卡', '19', '9', '621382063', '0');
INSERT INTO `authbank_cardbin` VALUES ('3583', '莫力达瓦包商村镇银行', '16561966', '', '莫力达瓦包商村镇银行润禾卡', '19', '9', '621382052', '0');
INSERT INTO `authbank_cardbin` VALUES ('3584', '鄂温克包商村镇银行', '16561971', '', '鄂温克包商村镇银行润禾卡', '19', '9', '621382068', '0');
INSERT INTO `authbank_cardbin` VALUES ('3585', '科尔沁包商村镇银行', '16561982', '', '兴安盟科尔沁包商村镇银行润禾卡', '19', '9', '621382074', '0');
INSERT INTO `authbank_cardbin` VALUES ('3586', '西乌珠穆沁包商惠丰村镇银行', '16562017', '', '西乌珠穆沁包商惠丰润禾卡', '19', '9', '621382051', '0');
INSERT INTO `authbank_cardbin` VALUES ('3587', '集宁包闹村镇银行', '16562031', '', '集宁包闹村镇银行润禾卡', '19', '9', '621382057', '0');
INSERT INTO `authbank_cardbin` VALUES ('3588', '化德包商村镇银行', '16562036', '', '化德包商村镇银行润禾卡', '19', '9', '621382058', '0');
INSERT INTO `authbank_cardbin` VALUES ('3589', '准格尔旗包商村镇银行', '16562053', '', '准格尔旗包商村镇银行润禾卡', '19', '9', '621382061', '0');
INSERT INTO `authbank_cardbin` VALUES ('3590', '乌审旗包商村镇银行', '16562056', '', '乌审旗包商村镇银行润禾卡', '19', '9', '621382062', '0');
INSERT INTO `authbank_cardbin` VALUES ('3591', '大连金州联丰村镇银行', '16562371', '', '', '19', '9', '621382067', '0');
INSERT INTO `authbank_cardbin` VALUES ('3592', '九台龙嘉村镇银行', '16562415', '', '九台龙嘉村镇银行润禾卡', '19', '9', '621382060', '0');
INSERT INTO `authbank_cardbin` VALUES ('3593', '江苏南通如皋包商村镇银行', '16563062', '', '江苏南通如皋包商村镇银行润禾卡', '19', '9', '621382072', '0');
INSERT INTO `authbank_cardbin` VALUES ('3594', '仪征包商村镇银行', '16563129', '', '江苏仪征包商村镇银行润禾卡', '19', '9', '621382064', '0');
INSERT INTO `authbank_cardbin` VALUES ('3595', '鄄城包商村镇银行', '16564759', '', '鄄城包商村镇银行润禾卡', '19', '9', '621382056', '0');
INSERT INTO `authbank_cardbin` VALUES ('3596', '漯河市郾城包商村镇银行', '16565040', '', '郾城包商村镇银行润禾卡', '19', '9', '621382050', '0');
INSERT INTO `authbank_cardbin` VALUES ('3597', '掇刀包商村镇银行', '16565320', '', '掇刀包商村镇银行润禾卡', '19', '9', '621382075', '0');
INSERT INTO `authbank_cardbin` VALUES ('3598', '武冈包商村镇银行', '16565556', '', '武冈包商村镇银行润禾卡', '19', '9', '621382073', '0');
INSERT INTO `authbank_cardbin` VALUES ('3599', '新都桂城村镇银行', '16566510', '', '新都桂城村镇银行润禾卡', '19', '9', '621382069', '0');
INSERT INTO `authbank_cardbin` VALUES ('3600', '广元包商贵民村镇银行', '16566610', '', '广元市包商贵民村镇银行润禾卡', '19', '9', '621382071', '0');
INSERT INTO `authbank_cardbin` VALUES ('3601', '贵阳花溪建设村镇银行', '16567010', '', '贵阳花溪建设村镇银行润禾卡', '19', '9', '621382054', '0');
INSERT INTO `authbank_cardbin` VALUES ('3602', '息烽包商黔隆村镇银行', '16567012', '', '息烽包商黔隆村镇银行润禾卡', '19', '9', '621382065', '0');
INSERT INTO `authbank_cardbin` VALUES ('3603', '毕节发展村镇银行', '16567090', '', '毕节发展村镇银行润禾卡', '19', '9', '621382059', '0');
INSERT INTO `authbank_cardbin` VALUES ('3604', '宁夏贺兰回商村镇银行', '16568712', '', '宁夏贺兰回商村镇银行润禾卡', '19', '9', '621382055', '0');
INSERT INTO `authbank_cardbin` VALUES ('3605', '临高惠丰村镇银行', '16586429', '', '', '18', '8', '62363440', '0');
INSERT INTO `authbank_cardbin` VALUES ('3606', '东方惠丰村镇银行', '16586434', '', '', '18', '8', '62363441', '0');
INSERT INTO `authbank_cardbin` VALUES ('3607', '乐东惠丰村镇银行', '16586435', '', '', '18', '8', '62363442', '0');
INSERT INTO `authbank_cardbin` VALUES ('3608', '临沂河东齐商村镇银行', '16604736', '', '', '19', '8', '62362401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3609', '虞城通商村镇银行', '16625062', '', '虞城通商村镇银行润禾卡', '19', '9', '623174001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3610', '繁峙县新田村镇银行', '16631716', '', '', '19', '8', '62359602', '0');
INSERT INTO `authbank_cardbin` VALUES ('3611', '曲沃县新田村镇银行', '16631773', '', '', '19', '8', '62359601', '0');
INSERT INTO `authbank_cardbin` VALUES ('3612', '临猗县新田村镇银行', '16631814', '', '', '19', '8', '62359603', '0');
INSERT INTO `authbank_cardbin` VALUES ('3613', '新绛县新田村镇银行', '16631816', '', '', '19', '8', '62359605', '0');
INSERT INTO `authbank_cardbin` VALUES ('3614', '信阳平桥恒丰村镇银行', '16665162', '', '旭日卡', '18', '9', '621652853', '0');
INSERT INTO `authbank_cardbin` VALUES ('3615', '中信嘉华银行有限公司', '25160344', '', 'CNCBI HKD Diamond Debit Card', '17', '6', '623397', '0');
INSERT INTO `authbank_cardbin` VALUES ('3616', '创兴银行有限公司', '25170344', '', 'UnionPay RMB Gift Card', '16', '6', '623413', '2');
INSERT INTO `authbank_cardbin` VALUES ('3617', '中银信用卡(国际)有限公司', '25180344', '', '中银银联双币钻石卡', '16', '6', '625172', '1');
INSERT INTO `authbank_cardbin` VALUES ('3618', '中银信用卡(国际)有限公司', '25180344', '', '中银银联双币钻石卡', '16', '6', '625174', '1');
INSERT INTO `authbank_cardbin` VALUES ('3619', '中银信用卡(国际)有限公司', '25180344', '', '中银银联双币钻石卡', '16', '6', '625176', '1');
INSERT INTO `authbank_cardbin` VALUES ('3620', '中国建设银行澳门分行', '25270446', '', 'CCB Macau Platinum Credit Card', '16', '6', '624412', '1');
INSERT INTO `authbank_cardbin` VALUES ('3621', '澳门永亨银行股份有限公司', '26080446', '', 'OCBCWH CUP Platinum', '16', '6', '624300', '1');
INSERT INTO `authbank_cardbin` VALUES ('3622', '澳门永亨银行股份有限公司', '26080446', '', 'OCBCWH CUP Platinum', '16', '6', '624302', '1');
INSERT INTO `authbank_cardbin` VALUES ('3623', '澳门永亨银行股份有限公司', '26080446', '', 'OCBCWH CUP Diamond', '16', '6', '624308', '1');
INSERT INTO `authbank_cardbin` VALUES ('3624', '澳门永亨银行股份有限公司', '26080446', '', 'OCBCWH CUP Platinum', '16', '6', '624309', '1');
INSERT INTO `authbank_cardbin` VALUES ('3625', '澳门国际银行', '26220446', '', '澳门国际银行银联金卡', '16', '6', '624407', '1');
INSERT INTO `authbank_cardbin` VALUES ('3626', '澳门国际银行', '26220446', '', '澳门国际银行银联钻石卡', '16', '6', '624408', '1');
INSERT INTO `authbank_cardbin` VALUES ('3627', '大西洋银行股份有限公司', '26230446', '', 'BNU Unionpay Triple', '16', '6', '624371', '1');
INSERT INTO `authbank_cardbin` VALUES ('3628', '大西洋银行股份有限公司', '26230446', '', 'BNU Unionpay Triple', '16', '6', '624372', '1');
INSERT INTO `authbank_cardbin` VALUES ('3629', '大西洋银行股份有限公司', '26230446', '', 'BNU Unionpay Triple', '16', '6', '624398', '1');
INSERT INTO `authbank_cardbin` VALUES ('3630', '哈萨克斯坦国民储蓄银行', '26330398', '', 'Classic/Gold', '16', '6', '623357', '0');
INSERT INTO `authbank_cardbin` VALUES ('3631', '哈萨克斯坦国民储蓄银行', '26330398', '', 'Classic/Gold', '16', '6', '624341', '1');
INSERT INTO `authbank_cardbin` VALUES ('3632', '哈萨克斯坦国民储蓄银行', '26330398', '', '', '16', '7', '6234500', '0');
INSERT INTO `authbank_cardbin` VALUES ('3633', '哈萨克斯坦国民储蓄银行', '26330398', '', '', '16', '7', '6234501', '0');
INSERT INTO `authbank_cardbin` VALUES ('3634', 'Bangkok Bank Pcl', '26350764', '', 'Be 1st Smart UnionPay', '16', '6', '623355', '0');
INSERT INTO `authbank_cardbin` VALUES ('3635', '可汗银行', '26530496', '', '', '16', '8', '62345800', '0');
INSERT INTO `authbank_cardbin` VALUES ('3636', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '623379', '0');
INSERT INTO `authbank_cardbin` VALUES ('3637', 'BC卡公司', '26630410', '', 'BC Baro', '16', '6', '624344', '1');
INSERT INTO `authbank_cardbin` VALUES ('3638', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '624424', '1');
INSERT INTO `authbank_cardbin` VALUES ('3639', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '624446', '1');
INSERT INTO `authbank_cardbin` VALUES ('3640', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '624346', '1');
INSERT INTO `authbank_cardbin` VALUES ('3641', 'BC卡公司', '26630410', '', 'BC-IBK', '16', '6', '623395', '0');
INSERT INTO `authbank_cardbin` VALUES ('3642', 'BC卡公司', '26630410', '', 'BC-Woori', '16', '6', '624373', '1');
INSERT INTO `authbank_cardbin` VALUES ('3643', 'BC卡公司', '26630410', '', 'BC-Woori', '16', '6', '624374', '1');
INSERT INTO `authbank_cardbin` VALUES ('3644', 'BC卡公司', '26630410', '', 'BC-IBK', '16', '6', '624384', '1');
INSERT INTO `authbank_cardbin` VALUES ('3645', 'BC卡公司', '26630410', '', 'BC-Woori', '16', '6', '624420', '1');
INSERT INTO `authbank_cardbin` VALUES ('3646', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '624380', '1');
INSERT INTO `authbank_cardbin` VALUES ('3647', 'BC卡公司', '26630410', '', 'Woori', '16', '6', '624404', '1');
INSERT INTO `authbank_cardbin` VALUES ('3648', 'BC卡公司', '26630410', '', 'BC-IBK', '16', '6', '624345', '1');
INSERT INTO `authbank_cardbin` VALUES ('3649', 'BC卡公司', '26630410', '', 'BC-NH NongHyup', '16', '6', '623391', '0');
INSERT INTO `authbank_cardbin` VALUES ('3650', 'BC卡公司', '26630410', '', 'BC-NH NongHyup', '16', '6', '623392', '0');
INSERT INTO `authbank_cardbin` VALUES ('3651', 'BC卡公司', '26630410', '', 'BC-Woori', '16', '6', '623393', '0');
INSERT INTO `authbank_cardbin` VALUES ('3652', 'BC卡公司', '26630410', '', 'BC-Woori', '16', '6', '624343', '1');
INSERT INTO `authbank_cardbin` VALUES ('3653', 'BC卡公司', '26630410', '', 'BC-NH NongHyup', '16', '6', '624381', '1');
INSERT INTO `authbank_cardbin` VALUES ('3654', 'BC卡公司', '26630410', '', 'BC-NH NongHyup', '16', '6', '624382', '1');
INSERT INTO `authbank_cardbin` VALUES ('3655', 'BC卡公司', '26630410', '', '', '16', '6', '623451', '0');
INSERT INTO `authbank_cardbin` VALUES ('3656', 'BC卡公司', '26630410', '', '', '16', '6', '623452', '0');
INSERT INTO `authbank_cardbin` VALUES ('3657', 'BC卡公司', '26630410', '', '', '16', '6', '623453', '0');
INSERT INTO `authbank_cardbin` VALUES ('3658', 'BC卡公司', '26630410', '', '', '16', '6', '623454', '0');
INSERT INTO `authbank_cardbin` VALUES ('3659', 'CSC', '26790422', '', 'Fransubank Crdit Platinum UPI', '16', '8', '62235600', '1');
INSERT INTO `authbank_cardbin` VALUES ('3660', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6233710', '0');
INSERT INTO `authbank_cardbin` VALUES ('3661', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6233711', '0');
INSERT INTO `authbank_cardbin` VALUES ('3662', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6233712', '0');
INSERT INTO `authbank_cardbin` VALUES ('3663', 'Gazprombank', '27300643', '', 'Gold', '16', '7', '6233720', '0');
INSERT INTO `authbank_cardbin` VALUES ('3664', 'Gazprombank', '27300643', '', 'Gold', '16', '7', '6233721', '0');
INSERT INTO `authbank_cardbin` VALUES ('3665', 'Gazprombank', '27300643', '', 'Gold', '16', '7', '6233722', '0');
INSERT INTO `authbank_cardbin` VALUES ('3666', 'Gazprombank', '27300643', '', 'Gold', '16', '7', '6233723', '0');
INSERT INTO `authbank_cardbin` VALUES ('3667', 'Gazprombank', '27300643', '', 'Platinum', '16', '7', '6233730', '0');
INSERT INTO `authbank_cardbin` VALUES ('3668', 'Gazprombank', '27300643', '', 'Platinum', '16', '7', '6233731', '0');
INSERT INTO `authbank_cardbin` VALUES ('3669', 'Gazprombank', '27300643', '', 'Platinum', '16', '7', '6233732', '0');
INSERT INTO `authbank_cardbin` VALUES ('3670', 'Gazprombank', '27300643', '', 'Diamond', '16', '7', '6233760', '0');
INSERT INTO `authbank_cardbin` VALUES ('3671', 'Gazprombank', '27300643', '', 'Diamond', '16', '7', '6233761', '0');
INSERT INTO `authbank_cardbin` VALUES ('3672', 'Gazprombank', '27300643', '', 'Diamond', '16', '7', '6233762', '0');
INSERT INTO `authbank_cardbin` VALUES ('3673', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6243650', '1');
INSERT INTO `authbank_cardbin` VALUES ('3674', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6243651', '1');
INSERT INTO `authbank_cardbin` VALUES ('3675', 'Gazprombank', '27300643', '', 'Classic', '16', '7', '6243652', '1');
INSERT INTO `authbank_cardbin` VALUES ('3676', 'EQUITY BANK KENYA LIMITED', '27390404', '', 'DEBIT', '16', '7', '6234280', '0');
INSERT INTO `authbank_cardbin` VALUES ('3677', 'EQUITY BANK KENYA LIMITED', '27390404', '', 'PREPAID', '16', '7', '6234290', '2');
INSERT INTO `authbank_cardbin` VALUES ('3678', 'Kyrgyz Investment Credit Bank', '27730417', '', 'China Union Pay - Elcard', '16', '6', '623326', '0');
INSERT INTO `authbank_cardbin` VALUES ('3679', 'Kyrgyz Investment Credit Bank', '27730417', '', 'China Union Pay - Elcard', '16', '6', '623327', '0');
INSERT INTO `authbank_cardbin` VALUES ('3680', 'Kyrgyz Investment Credit Bank', '27730417', '', '', '16', '7', '6234610', '0');
INSERT INTO `authbank_cardbin` VALUES ('3681', 'Kyrgyz Investment Credit Bank', '27730417', '', '', '16', '7', '6292740', '0');
INSERT INTO `authbank_cardbin` VALUES ('3682', '韩国乐天', '28030410', '', 'Lotte Check Card,PointPlus', '16', '6', '623358', '0');
INSERT INTO `authbank_cardbin` VALUES ('3683', 'Cambodia Mekong Bank PL', '28250116', '', 'MekongBank UnionPay Card', '16', '6', '624351', '1');
INSERT INTO `authbank_cardbin` VALUES ('3684', 'DE SURINAAMSCHE BANK N.V.', '28280740', '', 'General purpose', '16', '6', '623370', '2');
INSERT INTO `authbank_cardbin` VALUES ('3685', 'Mongolia Trade Develop. Bank', '28530496', '', 'GOLD UNIONPAY CARD', '16', '6', '624366', '1');
INSERT INTO `authbank_cardbin` VALUES ('3686', '韩国KB', '28590410', '', 'KB Kookmin Card CO.,Ltd', '16', '6', '623374', '0');
INSERT INTO `authbank_cardbin` VALUES ('3687', '韩国KB', '28590410', '', 'KB Kookmin card', '16', '6', '624370', '1');
INSERT INTO `authbank_cardbin` VALUES ('3688', 'Credit Saison', '28630392', '', 'NEO MONEY', '16', '6', '620056', '2');
INSERT INTO `authbank_cardbin` VALUES ('3689', '韩国三星卡公司', '28660410', '', 'SAMSUNG CORPORATE PLATINUM', '16', '6', '624399', '1');
INSERT INTO `authbank_cardbin` VALUES ('3690', '韩国三星卡公司', '28660410', '', 'SAMSUNG SHINSEGAE CARD', '16', '6', '624400', '1');
INSERT INTO `authbank_cardbin` VALUES ('3691', '韩国三星卡公司', '28660410', '', 'SAMSUNG CHCK CARD', '16', '6', '624401', '1');
INSERT INTO `authbank_cardbin` VALUES ('3692', '韩国三星卡公司', '28660410', '', 'SAMSUNG CARD', '16', '6', '624410', '1');
INSERT INTO `authbank_cardbin` VALUES ('3693', '韩国三星卡公司', '28660410', '', 'SAMSUNG CHECK CARD', '16', '6', '624411', '1');
INSERT INTO `authbank_cardbin` VALUES ('3694', '韩国三星卡公司', '28660410', '', '', '16', '6', '626395', '1');
INSERT INTO `authbank_cardbin` VALUES ('3695', '韩国三星卡公司', '28660410', '', '', '16', '6', '624413', '1');
INSERT INTO `authbank_cardbin` VALUES ('3696', 'CRDB BANK PLC', '28730834', '', 'UICC DEBIT-RMB', '16', '6', '623315', '0');
INSERT INTO `authbank_cardbin` VALUES ('3697', 'The Bancorp Bank', '28880840', '', 'Unionpay Gift Card', '16', '6', '624357', '2');
INSERT INTO `authbank_cardbin` VALUES ('3698', '新韩卡公司', '29010410', '', 'Shinhan Platinum#', '16', '6', '624331', '1');
INSERT INTO `authbank_cardbin` VALUES ('3699', '新韩卡公司', '29010410', '', 'Shinhan Platinum', '16', '6', '624348', '1');
INSERT INTO `authbank_cardbin` VALUES ('3700', '新韩卡公司', '29010410', '', 'Shinhan', '16', '6', '624332', '1');
INSERT INTO `authbank_cardbin` VALUES ('3701', '新韩卡公司', '29010410', '', 'Shinhan', '16', '6', '626394', '1');
INSERT INTO `authbank_cardbin` VALUES ('3702', '新韩卡公司', '29010410', '', '', '16', '6', '624339', '1');
INSERT INTO `authbank_cardbin` VALUES ('3703', 'Capital Bank of Mongolia', '29120496', '', '', '16', '8', '62341602', '0');
INSERT INTO `authbank_cardbin` VALUES ('3704', 'Capital Bank of Mongolia', '29120496', '', '', '16', '8', '62341601', '0');
INSERT INTO `authbank_cardbin` VALUES ('3705', 'JSC Liberty Bank', '29140268', '', 'Classic', '16', '7', '6233454', '0');
INSERT INTO `authbank_cardbin` VALUES ('3706', 'JSC Liberty Bank', '29140268', '', 'Gold', '16', '7', '6233455', '0');
INSERT INTO `authbank_cardbin` VALUES ('3707', 'Non-banking credit', '29180643', '', '', '16', '8', '62927300', '0');
INSERT INTO `authbank_cardbin` VALUES ('3708', 'PT Bank Sinarmas,Tbk', '29270360', '', 'CUP Debit Gold', '16', '7', '6214455', '0');
INSERT INTO `authbank_cardbin` VALUES ('3709', 'PT Bank Sinarmas,Tbk', '29270360', '', 'Debit CUP Diamond', '16', '7', '6214458', '0');
INSERT INTO `authbank_cardbin` VALUES ('3710', 'JSC Kazkommertsbank', '29430398', '', 'Classic', '16', '7', '6234240', '0');
INSERT INTO `authbank_cardbin` VALUES ('3711', 'JSC Kazkommertsbank', '29430398', '', 'Gold', '16', '7', '6234241', '0');
INSERT INTO `authbank_cardbin` VALUES ('3712', 'JSC Kazkommertsbank', '29430398', '', 'Gold', '16', '7', '6234242', '0');
INSERT INTO `authbank_cardbin` VALUES ('3713', 'PVB Card Corporation', '29470608', '', '预付卡', '16', '6', '623492', '0');
INSERT INTO `authbank_cardbin` VALUES ('3714', 'PVB Card Corporation', '29470608', '', 'LANDBANK CUP Propaid Card', '16', '6', '623398', '2');
INSERT INTO `authbank_cardbin` VALUES ('3715', 'PVB Card Corporation', '29470608', '', '', '16', '8', '62334910', '2');
INSERT INTO `authbank_cardbin` VALUES ('3716', 'PVB Card Corporation', '29470608', '', '', '16', '8', '62334911', '2');
INSERT INTO `authbank_cardbin` VALUES ('3717', 'PVB Card Corporation', '29470608', '', '', '16', '8', '62334912', '2');
INSERT INTO `authbank_cardbin` VALUES ('3718', 'PT BANK SINARMAS TBK', '29530360', '', 'SBankUnionPay', '16', '7', '6243051', '1');
INSERT INTO `authbank_cardbin` VALUES ('3719', 'PT BANK SINARMAS TBK', '29530360', '', 'SBankUnionPay', '16', '7', '6243052', '1');
INSERT INTO `authbank_cardbin` VALUES ('3720', 'NongHyup Bank', '29650410', '', 'NH Card', '16', '6', '624387', '1');
INSERT INTO `authbank_cardbin` VALUES ('3721', 'NongHyup Bank', '29650410', '', 'NH Card', '16', '6', '624388', '1');
INSERT INTO `authbank_cardbin` VALUES ('3722', 'Davr Bank', '29710860', '', 'Magnetic stripe', '16', '6', '623325', '0');
INSERT INTO `authbank_cardbin` VALUES ('3723', 'Advanced Bank of Asia Ltd.', '29740116', '', 'UPI Debit Classic', '16', '6', '623375', '0');
INSERT INTO `authbank_cardbin` VALUES ('3724', 'Global Bank of Commerce Ltd', '29820028', '', 'GBC Individual Debit', '16', '7', '6243550', '0');
INSERT INTO `authbank_cardbin` VALUES ('3725', 'Global Bank of Commerce Ltd', '29820028', '', 'GBC Corporate Debit', '16', '7', '6243551', '0');
INSERT INTO `authbank_cardbin` VALUES ('3726', 'Global Bank of Commerce Ltd', '29820028', '', 'GBC Individual Prepaid', '16', '7', '6243560', '2');
INSERT INTO `authbank_cardbin` VALUES ('3727', 'Global Bank of Commerce Ltd', '29820028', '', 'GBC Corporate Prepaid', '16', '7', '6243561', '2');
INSERT INTO `authbank_cardbin` VALUES ('3728', 'Optima Bank OJSC', '29830417', '', 'Debit/Classic/Individual', '16', '6', '623360', '0');
INSERT INTO `authbank_cardbin` VALUES ('3729', 'Light Bank', '30040643', '', 'Classic', '16', '7', '6233531', '0');
INSERT INTO `authbank_cardbin` VALUES ('3730', 'Light Bank', '30040643', '', 'Gold', '16', '7', '6233532', '0');
INSERT INTO `authbank_cardbin` VALUES ('3731', 'Light Bank', '30040643', '', 'Platinum', '16', '7', '6233533', '0');
INSERT INTO `authbank_cardbin` VALUES ('3732', 'Light Bank', '30040643', '', 'Classic', '16', '7', '6233534', '0');
INSERT INTO `authbank_cardbin` VALUES ('3733', 'Light Bank', '30040643', '', 'Gold', '16', '7', '6233535', '0');
INSERT INTO `authbank_cardbin` VALUES ('3734', 'Light Bank', '30040643', '', 'Platinum', '16', '7', '6233536', '0');
INSERT INTO `authbank_cardbin` VALUES ('3735', 'Light Bank', '30040643', '', 'Prepaid', '16', '7', '6234980', '2');
INSERT INTO `authbank_cardbin` VALUES ('3736', 'Light Bank', '30040643', '', 'Prepaid Classic', '16', '7', '6234981', '2');
INSERT INTO `authbank_cardbin` VALUES ('3737', 'Light Bank', '30040643', '', 'Debit Diamond', '16', '7', '6233537', '0');
INSERT INTO `authbank_cardbin` VALUES ('3738', 'Light Bank', '30040643', '', 'Debit Classic', '16', '7', '6233538', '0');
INSERT INTO `authbank_cardbin` VALUES ('3739', 'Light Bank', '30040643', '', 'Debit Platinum', '16', '7', '6233539', '0');
INSERT INTO `authbank_cardbin` VALUES ('3740', 'Co-Operative Bank Limited', '30090104', '', '', '16', '8', '62441900', '1');
INSERT INTO `authbank_cardbin` VALUES ('3741', 'Travelex Card Services', '30170344', '', '', '16', '7', '6221440', '2');
INSERT INTO `authbank_cardbin` VALUES ('3742', 'Travelex Japan KK', '30170392', '', '', '16', '7', '6221441', '2');
INSERT INTO `authbank_cardbin` VALUES ('3743', 'Bank AL Habib Limited', '30180586', '', '', '16', '6', '623359', '0');
INSERT INTO `authbank_cardbin` VALUES ('3744', 'Bank of China(Australia)', '30230036', '', 'Load & Go China', '16', '6', '623362', '2');
INSERT INTO `authbank_cardbin` VALUES ('3745', 'OSJC MTS Bank', '30280643', '', 'Classic', '16', '7', '6243423', '1');
INSERT INTO `authbank_cardbin` VALUES ('3746', 'OSJC MTS Bank', '30280643', '', 'Gold', '16', '7', '6243424', '1');
INSERT INTO `authbank_cardbin` VALUES ('3747', 'OSJC MTS Bank', '30280643', '', 'Platinum', '16', '7', '6243425', '1');
INSERT INTO `authbank_cardbin` VALUES ('3748', 'Heritage International Bank', '30350084', '', 'Heritage Traveler', '16', '6', '623369', '2');
INSERT INTO `authbank_cardbin` VALUES ('3749', 'OJSC Tojiksodirotbank', '30400762', '', 'Classic', '16', '7', '6233681', '0');
INSERT INTO `authbank_cardbin` VALUES ('3750', 'MetaBank', '30420840', '', 'Integrated Solutions GPR', '16', '6', '624364', '2');
INSERT INTO `authbank_cardbin` VALUES ('3751', 'Banque Pour Le Commerce', '30470418', '', 'Lao Airlines Classic', '16', '6', '624352', '1');
INSERT INTO `authbank_cardbin` VALUES ('3752', 'Banque Pour Le Commerce', '30470418', '', 'Lao Airlines Gold', '16', '6', '624353', '1');
INSERT INTO `authbank_cardbin` VALUES ('3753', 'Banque Pour Le Commerce', '30470418', '', 'Lao Airline Platinum', '16', '6', '624354', '1');
INSERT INTO `authbank_cardbin` VALUES ('3754', 'Bank Alfalah Limited', '30590586', '', 'TBD', '16', '6', '623381', '0');
INSERT INTO `authbank_cardbin` VALUES ('3755', 'Cooperative & Agricultural', '30700887', '', 'Debit', '16', '6', '623380', '0');
INSERT INTO `authbank_cardbin` VALUES ('3756', 'Cooperative & Agricultural', '30700887', '', 'Credit', '16', '6', '624367', '1');
INSERT INTO `authbank_cardbin` VALUES ('3757', 'Open Joint-stock Company', '30770643', '', 'BaikalBank', '16', '7', '6233780', '0');
INSERT INTO `authbank_cardbin` VALUES ('3758', 'Open Joint-stock Company', '30770643', '', 'BaikalBank', '16', '7', '6233781', '0');
INSERT INTO `authbank_cardbin` VALUES ('3759', 'BANCO SOL,S.A.', '30780024', '', '密码借记卡', '16', '7', '6233840', '0');
INSERT INTO `authbank_cardbin` VALUES ('3760', 'BANCO SOL,S.A.', '30780024', '', '密码借记卡', '16', '7', '6233841', '0');
INSERT INTO `authbank_cardbin` VALUES ('3761', 'BANCO SOL,S.A.', '30780024', '', '预付卡', '16', '6', '623385', '2');
INSERT INTO `authbank_cardbin` VALUES ('3762', 'Subsidiary Bank Sberbank RUS', '30810398', '', 'Classic', '16', '7', '6233820', '0');
INSERT INTO `authbank_cardbin` VALUES ('3763', 'Subsidiary Bank Sberbank RUS', '30810398', '', 'Gold', '16', '7', '6233821', '0');
INSERT INTO `authbank_cardbin` VALUES ('3764', 'Subsidiary Bank Sberbank RUS', '30810398', '', 'Platinum', '16', '7', '6233822', '0');
INSERT INTO `authbank_cardbin` VALUES ('3765', 'Subsidiary Bank Sberbank RUS', '30810398', '', 'Diamond', '16', '7', '6233823', '0');
INSERT INTO `authbank_cardbin` VALUES ('3766', 'Subsidiary Bank Sberbank RUS', '30810398', '', 'Classic Corporate', '16', '7', '6233824', '0');
INSERT INTO `authbank_cardbin` VALUES ('3767', 'Lao China Bank Co.,Ltd', '30850418', '', 'Debit card - Gold Card', '16', '6', '623383', '0');
INSERT INTO `authbank_cardbin` VALUES ('3768', 'Lao China Bank Co.,Ltd', '30850418', '', 'Debit card - Classic Card', '16', '6', '623386', '0');
INSERT INTO `authbank_cardbin` VALUES ('3769', 'Lao China Bank Co.,Ltd', '30850418', '', 'Debit card - Platinum Card', '16', '6', '623388', '0');
INSERT INTO `authbank_cardbin` VALUES ('3770', 'Hyundaicard', '30880410', '', 'Hyundai', '16', '6', '624368', '1');
INSERT INTO `authbank_cardbin` VALUES ('3771', 'Hyundaicard', '30880410', '', 'Hyundai', '16', '6', '624376', '1');
INSERT INTO `authbank_cardbin` VALUES ('3772', 'Hyundaicard', '30880410', '', 'Hyundai', '16', '6', '624377', '1');
INSERT INTO `authbank_cardbin` VALUES ('3773', 'Hyundaicard', '30880410', '', 'Hyundai', '16', '6', '624378', '1');
INSERT INTO `authbank_cardbin` VALUES ('3774', 'Bank of Moscow OJSC', '31020643', '', 'Classic', '16', '7', '6233960', '0');
INSERT INTO `authbank_cardbin` VALUES ('3775', 'Bank of Moscow OJSC', '31020643', '', 'Gold', '16', '7', '6233961', '0');
INSERT INTO `authbank_cardbin` VALUES ('3776', 'Sindh Bank Limited', '31090586', '', 'Classic Debit', '16', '6', '623389', '0');
INSERT INTO `authbank_cardbin` VALUES ('3777', 'Russian Agricultural Bank', '31150643', '', '', '16', '7', '6234460', '0');
INSERT INTO `authbank_cardbin` VALUES ('3778', 'ROSENERGOBANK', '31350643', '', 'Gold', '16', '7', '6234120', '0');
INSERT INTO `authbank_cardbin` VALUES ('3779', 'ROSENERGOBANK', '31350643', '', 'Gold', '16', '7', '6243970', '1');
INSERT INTO `authbank_cardbin` VALUES ('3780', 'ROSENERGOBANK', '31350643', '', 'Gold', '16', '7', '6234122', '0');
INSERT INTO `authbank_cardbin` VALUES ('3781', 'ROSENERGOBANK', '31350643', '', '', '16', '7', '6234121', '0');
INSERT INTO `authbank_cardbin` VALUES ('3782', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '623417', '0');
INSERT INTO `authbank_cardbin` VALUES ('3783', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '623418', '0');
INSERT INTO `authbank_cardbin` VALUES ('3784', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '623419', '0');
INSERT INTO `authbank_cardbin` VALUES ('3785', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '623420', '0');
INSERT INTO `authbank_cardbin` VALUES ('3786', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '623421', '0');
INSERT INTO `authbank_cardbin` VALUES ('3787', 'Subsidiary JSC VTB Bank', '31360398', '', '', '16', '6', '629180', '0');
INSERT INTO `authbank_cardbin` VALUES ('3788', 'Credit Ural Bank Joint Stock', '31510643', '', 'Classic Unembossed', '16', '7', '6234090', '0');
INSERT INTO `authbank_cardbin` VALUES ('3789', 'Credit Ural Bank Joint Stock', '31510643', '', 'Classic', '16', '7', '6243900', '1');
INSERT INTO `authbank_cardbin` VALUES ('3790', 'Credit Ural Bank Joint Stock', '31510643', '', 'Gold', '16', '7', '6243901', '1');
INSERT INTO `authbank_cardbin` VALUES ('3791', 'Credit Ural Bank Joint Stock', '31510643', '', 'Platinum', '16', '7', '6243902', '1');
INSERT INTO `authbank_cardbin` VALUES ('3792', 'FIDELITY BANK GHANA LIMITED', '31570288', '', 'FIDELITY UPI PREPAID CARD', '16', '7', '6233990', '2');
INSERT INTO `authbank_cardbin` VALUES ('3793', 'FIDELITY BANK GHANA LIMITED', '31570288', '', 'FIDELITY UPI PREPAID CARD', '16', '7', '6233991', '2');
INSERT INTO `authbank_cardbin` VALUES ('3794', 'DBS Bank Ltd', '31600702', '', 'POSB UnionPay ATM Card', '16', '6', '621052', '0');
INSERT INTO `authbank_cardbin` VALUES ('3795', 'XacBank', '31700496', '', 'Debit Gold card', '16', '8', '62341401', '0');
INSERT INTO `authbank_cardbin` VALUES ('3796', 'XacBank', '31700496', '', 'Debit Classic card', '16', '8', '62341402', '0');
INSERT INTO `authbank_cardbin` VALUES ('3797', 'XacBank', '31700496', '', 'Debit Platinum card', '16', '8', '62341403', '0');
INSERT INTO `authbank_cardbin` VALUES ('3798', 'XacBank', '31700496', '', '', '16', '6', '623414', '0');
INSERT INTO `authbank_cardbin` VALUES ('3799', 'DBS Bank Ltd', '31730702', '', 'DBS UnionPay Debit Card', '16', '6', '624391', '1');
INSERT INTO `authbank_cardbin` VALUES ('3800', 'Sinopay（Singapore）PTE Ltd', '31760702', '', 'SINO Card', '16', '7', '6234150', '2');
INSERT INTO `authbank_cardbin` VALUES ('3801', 'Sinopay（Singapore）PTE Ltd', '31760702', '', 'SINO Card', '16', '7', '6234151', '2');
INSERT INTO `authbank_cardbin` VALUES ('3802', 'Sinopay（Singapore）PTE Ltd', '31760702', '', '', '16', '7', '6234152', '2');
INSERT INTO `authbank_cardbin` VALUES ('3803', 'JSCB Primorye', '31910643', '', 'Classic', '16', '7', '6234250', '0');
INSERT INTO `authbank_cardbin` VALUES ('3804', 'JSCB Primorye', '31910643', '', 'Gold', '16', '7', '6234251', '0');
INSERT INTO `authbank_cardbin` VALUES ('3805', 'Habib Bank Limited', '31930404', '', '', '16', '6', '623448', '0');
INSERT INTO `authbank_cardbin` VALUES ('3806', 'HBL', '31930480', '', 'HBL DebitCard', '16', '7', '6234270', '0');
INSERT INTO `authbank_cardbin` VALUES ('3807', 'Habib Bank Limited', '31930690', '', '', '16', '6', '623443', '0');
INSERT INTO `authbank_cardbin` VALUES ('3808', 'Joint-Stock Commercial Bank', '31950643', '', 'Platinum USD', '16', '7', '6244092', '1');
INSERT INTO `authbank_cardbin` VALUES ('3809', 'Joint-Stock Commercial Bank', '31950643', '', 'Platinum RUB', '16', '7', '6244096', '1');
INSERT INTO `authbank_cardbin` VALUES ('3810', 'MIP BANK', '31990643', '', 'MIP-UPI-GOLD', '16', '7', '6234310', '0');
INSERT INTO `authbank_cardbin` VALUES ('3811', 'Banque S C pour Afrique', '32040178', '', '', '16', '8', '62344901', '0');
INSERT INTO `authbank_cardbin` VALUES ('3812', 'Banque S C pour Afrique', '32040178', '', '', '16', '8', '62441701', '1');
INSERT INTO `authbank_cardbin` VALUES ('3813', 'OJS SCBP Primsotsbank', '32060643', '', 'Classic CNY', '16', '7', '6234320', '0');
INSERT INTO `authbank_cardbin` VALUES ('3814', 'OJS SCBP Primsotsbank', '32060643', '', 'Gold CNY', '16', '7', '6234321', '0');
INSERT INTO `authbank_cardbin` VALUES ('3815', 'OJS SCBP Primsotsbank', '32060643', '', 'Classic RUB', '16', '7', '6234322', '0');
INSERT INTO `authbank_cardbin` VALUES ('3816', 'OJS SCBP Primsotsbank', '32060643', '', 'Gold RUB', '16', '7', '6234323', '0');
INSERT INTO `authbank_cardbin` VALUES ('3817', 'AGD Bank Ltd', '32090104', '', '', '16', '7', '6234330', '0');
INSERT INTO `authbank_cardbin` VALUES ('3818', 'Conservative commercial bank', '32230643', '', 'Classic', '16', '7', '6234350', '0');
INSERT INTO `authbank_cardbin` VALUES ('3819', 'Conservative commercial bank', '32230643', '', 'Gold', '16', '7', '6234351', '0');
INSERT INTO `authbank_cardbin` VALUES ('3820', 'Conservative commercial bank', '32230643', '', 'Platinum', '16', '7', '6234352', '0');
INSERT INTO `authbank_cardbin` VALUES ('3821', 'Noor Bank PJSC', '32270784', '', '', '16', '7', '6234420', '2');
INSERT INTO `authbank_cardbin` VALUES ('3822', 'PROCREDIT BANK CONGO', '32390180', '', 'PCB Congo UPI Debit Card', '16', '8', '62343800', '0');
INSERT INTO `authbank_cardbin` VALUES ('3823', 'National Bank of Kenya Limited', '32460404', '', '', '16', '8', '62344000', '0');
INSERT INTO `authbank_cardbin` VALUES ('3824', 'National Bank of Kenya Limited', '32460404', '', '', '16', '8', '62344001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3825', 'National Bank of Kenya Limited', '32460404', '', '', '16', '8', '62441500', '1');
INSERT INTO `authbank_cardbin` VALUES ('3826', 'National Bank of Kenya Limited', '32460404', '', '', '16', '8', '62441501', '1');
INSERT INTO `authbank_cardbin` VALUES ('3827', 'National Bank of Kenya Limited', '32460404', '', '', '16', '8', '62344100', '2');
INSERT INTO `authbank_cardbin` VALUES ('3828', 'Financiera Cuallix', '32570484', '', '', '16', '6', '624416', '1');
INSERT INTO `authbank_cardbin` VALUES ('3829', 'CrediMax', '32680048', '', 'UPI Gold Card', '16', '6', '624418', '1');
INSERT INTO `authbank_cardbin` VALUES ('3830', 'Al Tail Money Transfer', '32710368', '', '', '16', '7', '6292720', '2');
INSERT INTO `authbank_cardbin` VALUES ('3831', 'BRD Commercal Bank Limited', '32770646', '', '', '16', '8', '62345500', '0');
INSERT INTO `authbank_cardbin` VALUES ('3832', 'ABC BANKING CORPORATION LTD', '32780480', '', '', '16', '8', '62346000', '0');
INSERT INTO `authbank_cardbin` VALUES ('3833', 'ABC BANKING CORPORATION LTD', '32780480', '', '', '16', '8', '62346001', '0');
INSERT INTO `authbank_cardbin` VALUES ('3834', '中银通支付商务有限公司', '48080710', '', '', '19', '7', '6234390', '2');
INSERT INTO `authbank_cardbin` VALUES ('3835', '中国邮政储蓄银行信用卡中心', '61000000', '', '中国旅游卡', '16', '6', '625368', '1');
INSERT INTO `authbank_cardbin` VALUES ('3836', '中国邮政储蓄银行信用卡中心', '61000000', '', '中国旅游卡', '16', '6', '625367', '1');
INSERT INTO `authbank_cardbin` VALUES ('3837', '中国邮政储蓄银行信用卡中心', '61000000', '', '全币种信用卡', '16', '6', '518905', '1');
INSERT INTO `authbank_cardbin` VALUES ('3838', '中国邮政储蓄银行信用卡中心', '61000000', '', '大白金卡', '16', '6', '622835', '1');
INSERT INTO `authbank_cardbin` VALUES ('3839', '中国邮政储蓄银行信用卡中心', '61000000', '', '商务卡金卡', '16', '6', '625603', '1');
INSERT INTO `authbank_cardbin` VALUES ('3840', '中信银行信用卡中心', '63020000', '', '中信银行网付IC卡', '16', '6', '622766', '1');
INSERT INTO `authbank_cardbin` VALUES ('3841', '中信银行信用卡中心', '63020000', '', '中信银行网付IC卡', '16', '6', '622767', '1');
INSERT INTO `authbank_cardbin` VALUES ('3842', '中信银行信用卡中心', '63020000', '', '中信银行网付IC卡', '16', '6', '622768', '1');
INSERT INTO `authbank_cardbin` VALUES ('3843', '浦发银行信用卡中心', '63100000', '', '', '16', '6', '622693', '1');
INSERT INTO `authbank_cardbin` VALUES ('3844', '浦发银行信用卡中心', '63100000', '', '苹果手机支付卡', '16', '6', '625833', '1');
INSERT INTO `authbank_cardbin` VALUES ('3845', '北京银行', '64031000', '', '', '16', '6', '625816', '1');
INSERT INTO `authbank_cardbin` VALUES ('3846', '常熟农村商业银行', '64033055', '', '', '16', '6', '625165', '1');
INSERT INTO `authbank_cardbin` VALUES ('3847', '深圳农村商业银行', '64045840', '', '深圳农村商业银行信用卡', '16', '6', '625169', '1');
INSERT INTO `authbank_cardbin` VALUES ('3848', '广州银行股份有限公司', '64135810', '', '贷记白金卡', '16', '6', '622875', '1');
INSERT INTO `authbank_cardbin` VALUES ('3849', '苏州银行', '64303050', '', '', '16', '6', '625166', '1');
INSERT INTO `authbank_cardbin` VALUES ('3850', '郑州银行', '64354910', '', '商鼎信用卡', '16', '6', '625163', '1');
INSERT INTO `authbank_cardbin` VALUES ('3851', '贵州省农村信用联合社', '64367000', '', '信合公务卡', '16', '6', '628341', '1');
INSERT INTO `authbank_cardbin` VALUES ('3852', '锦州银行', '64392270', '', '7777信用卡', '16', '6', '622710', '1');
INSERT INTO `authbank_cardbin` VALUES ('3853', '锦州银行', '64392270', '', '7777公务卡', '16', '6', '628294', '1');
INSERT INTO `authbank_cardbin` VALUES ('3854', '徽商银行', '64403600', '', '云逸卡', '19', '6', '623664', '0');
INSERT INTO `authbank_cardbin` VALUES ('3855', '徽商银行', '64403600', '', '中国旅游卡', '16', '6', '625358', '1');
INSERT INTO `authbank_cardbin` VALUES ('3856', '贵阳银行股份有限公司', '64437010', '', '贷记卡', '16', '6', '622533', '1');
INSERT INTO `authbank_cardbin` VALUES ('3857', '西安银行', '64447910', '', '', '16', '6', '625167', '1');
INSERT INTO `authbank_cardbin` VALUES ('3858', '西安银行', '64447910', '', '', '16', '6', '628345', '1');
INSERT INTO `authbank_cardbin` VALUES ('3859', '兰州银行', '64478210', '', '敦煌信用卡', '16', '6', '622416', '1');
INSERT INTO `authbank_cardbin` VALUES ('3860', '营口银行', '64652280', '', '', '16', '6', '628264', '1');
INSERT INTO `authbank_cardbin` VALUES ('3861', '阜新银行股份有限公司', '64672290', '', '', '16', '6', '628353', '1');
INSERT INTO `authbank_cardbin` VALUES ('3862', '嘉兴银行', '64703350', '', '', '16', '6', '628327', '1');
INSERT INTO `authbank_cardbin` VALUES ('3863', '桂林银行', '64916170', '', '', '16', '6', '625168', '1');
INSERT INTO `authbank_cardbin` VALUES ('3864', '桂林银行', '64916170', '', '', '16', '6', '625369', '1');
INSERT INTO `authbank_cardbin` VALUES ('3865', '柳州银行', '64956140', '', '', '16', '6', '628373', '1');
INSERT INTO `authbank_cardbin` VALUES ('3866', '上海农商银行贷记卡', '65012900', '', '', '16', '6', '625701', '1');
INSERT INTO `authbank_cardbin` VALUES ('3867', '泰安银行', '65284630', '', '', '16', '6', '628284', '1');
INSERT INTO `authbank_cardbin` VALUES ('3868', '临汾市尧都区农村信用合作联社', '65341770', '', '天河贷记卡', '16', '6', '625820', '1');
INSERT INTO `authbank_cardbin` VALUES ('3869', '安徽省农村信用社', '65473600', '', '', '19', '6', '621550', '0');
INSERT INTO `authbank_cardbin` VALUES ('3870', '安徽省农村信用社', '65473600', '', '', '19', '6', '623220', '0');
INSERT INTO `authbank_cardbin` VALUES ('3871', '安徽省农村信用社', '65473600', '', '', '19', '6', '623637', '0');
INSERT INTO `authbank_cardbin` VALUES ('3872', '天津滨海农村商业银行', '65561100', '', '', '16', '6', '625152', '1');
INSERT INTO `authbank_cardbin` VALUES ('3873', '华融湘江银行', '65705500', '', '华融湘江银行磁条信用卡', '16', '6', '625886', '1');
INSERT INTO `authbank_cardbin` VALUES ('3874', 'Bank of China,Canada', '99900124', '', 'DEBIT CARD', '16', '6', '623423', '0');
INSERT INTO `authbank_cardbin` VALUES ('3875', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227890', '1');
INSERT INTO `authbank_cardbin` VALUES ('3876', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227891', '1');
INSERT INTO `authbank_cardbin` VALUES ('3877', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227892', '1');
INSERT INTO `authbank_cardbin` VALUES ('3878', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227893', '1');
INSERT INTO `authbank_cardbin` VALUES ('3879', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227894', '1');
INSERT INTO `authbank_cardbin` VALUES ('3880', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227895', '1');
INSERT INTO `authbank_cardbin` VALUES ('3881', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227896', '1');
INSERT INTO `authbank_cardbin` VALUES ('3882', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227897', '1');
INSERT INTO `authbank_cardbin` VALUES ('3883', '中行新加坡分行', '99900702', '', 'GREAT WALL PLATINUM', '16', '7', '6227898', '1');
INSERT INTO `authbank_cardbin` VALUES ('3884', '中行新加坡分行', '99900702', '', 'DUAL CURRENCY TRAVEL CARD', '16', '7', '6227899', '1');
INSERT INTO `authbank_cardbin` VALUES ('3885', '中行新加坡分行', '99900702', '', 'Dual Currency Debit Card', '16', '6', '624405', '1');
INSERT INTO `authbank_cardbin` VALUES ('3886', '邮政储蓄银行', '1009999', '', '绿卡通IC卡福农卡', '19', '6', '625586', '0');
INSERT INTO `authbank_cardbin` VALUES ('3887', '潍坊市寒亭区蒙银村镇银行', '15144592', '', '蒙银借记卡', '19', '8', '62134623', '0');
INSERT INTO `authbank_cardbin` VALUES ('3888', '杞县中银富登村镇银行', '15194921', '', '', '19', '9', '621356051', '0');
INSERT INTO `authbank_cardbin` VALUES ('3889', '岳池中银富登村镇银行', '15196691', '', '', '19', '9', '621356035', '0');
INSERT INTO `authbank_cardbin` VALUES ('3890', '广饶梁邹村镇银行', '16234553', '', '', '18', '8', '62358421', '0');
INSERT INTO `authbank_cardbin` VALUES ('3891', '保德县慧融村镇银行', '16591724', '', '', '18', '8', '62361912', '0');
INSERT INTO `authbank_cardbin` VALUES ('3892', '鄂尔多斯市天骄蒙银村镇银行', '16782051', '', '蒙银借记卡', '19', '8', '62134612', '0');
INSERT INTO `authbank_cardbin` VALUES ('3893', 'Canadia Bank PLC', '26610116', '', '', '16', '6', '623456', '0');
INSERT INTO `authbank_cardbin` VALUES ('3894', 'Canadia Bank PLC', '26610116', '', '', '16', '6', '623457', '0');
INSERT INTO `authbank_cardbin` VALUES ('3895', 'XacBank', '31700496', '', '', '16', '8', '62341407', '0');
INSERT INTO `authbank_cardbin` VALUES ('3896', 'XacBank', '31700496', '', '', '16', '8', '62341408', '0');
INSERT INTO `authbank_cardbin` VALUES ('3897', 'XacBank', '31700496', '', '', '16', '8', '62341409', '0');
INSERT INTO `authbank_cardbin` VALUES ('3898', 'Joint-Stock bank', '32280643', '', '', '16', '7', '6234450', '0');
INSERT INTO `authbank_cardbin` VALUES ('3899', 'Joint-Stock bank', '32280643', '', '', '16', '7', '6234451', '0');
INSERT INTO `authbank_cardbin` VALUES ('3900', 'Joint-Stock bank', '32280643', '', '', '16', '7', '6234452', '0');
INSERT INTO `authbank_cardbin` VALUES ('3901', 'Joint-Stock bank', '32280643', '', '', '16', '7', '6234453', '0');
INSERT INTO `authbank_cardbin` VALUES ('3902', '中国邮政储蓄银行信用卡中心', '61000000', '', '商务卡白金卡', '16', '6', '625605', '1');
INSERT INTO `authbank_cardbin` VALUES ('3903', '张家港农村商业银行', '64163056', '', '东渡信用卡', '16', '6', '625208', '1');
INSERT INTO `authbank_cardbin` VALUES ('3904', '张家港农村商业银行', '64163056', '', '张家港农村商业银行公务卡', '16', '6', '628349', '1');
INSERT INTO `authbank_cardbin` VALUES ('3905', '晋城银行', '65031680', '', '', '16', '6', '628243', '1');
INSERT INTO `authbank_cardbin` VALUES ('3906', '中国银联支付标记', '0010030', '', '中国银联移动支付标记化产品', '19', '6', '623529', '0');
INSERT INTO `authbank_cardbin` VALUES ('3907', '中国银联支付标记', '0010030', '', '中国银联移动支付标记化产品', '16', '6', '625153', '1');
INSERT INTO `authbank_cardbin` VALUES ('3908', '中国银联支付标记', '0010030', '', '', '16', '7', '6201361', '2');
INSERT INTO `authbank_cardbin` VALUES ('3909', '中国银联支付标记', '0010030', '', '', '17', '7', '6201362', '2');
INSERT INTO `authbank_cardbin` VALUES ('3910', '中国银联支付标记', '0010030', '', '', '18', '7', '6201363', '2');
INSERT INTO `authbank_cardbin` VALUES ('3911', '中国银联支付标记', '0010030', '', '', '19', '7', '6201365', '2');
INSERT INTO `authbank_cardbin` VALUES ('3912', '中国银联支付标记', '0010030', '', '', '19', '7', '6201366', '2');
INSERT INTO `authbank_cardbin` VALUES ('3913', '中国银联支付标记', '0010030', '', '', '19', '7', '6201367', '2');
INSERT INTO `authbank_cardbin` VALUES ('3914', '中国银联支付标记', '0010030', '', '', '19', '7', '6201368', '2');
INSERT INTO `authbank_cardbin` VALUES ('3915', '中国银联支付标记', '0010030', '', '', '19', '7', '6201369', '2');
INSERT INTO `authbank_cardbin` VALUES ('3916', '中国银联支付标记', '0010030', '', '', '16', '7', '6230740', '0');
INSERT INTO `authbank_cardbin` VALUES ('3917', '中国银联支付标记', '0010030', '', '', '16', '7', '6230741', '0');
INSERT INTO `authbank_cardbin` VALUES ('3918', '中国银联支付标记', '0010030', '', '', '17', '7', '6230742', '0');
INSERT INTO `authbank_cardbin` VALUES ('3919', '中国银联支付标记', '0010030', '', '', '18', '7', '6230743', '0');
INSERT INTO `authbank_cardbin` VALUES ('3920', '中国银联支付标记', '0010030', '', '', '19', '7', '6230745', '0');
INSERT INTO `authbank_cardbin` VALUES ('3921', '中国银联支付标记', '0010030', '', '', '19', '7', '6230746', '0');
INSERT INTO `authbank_cardbin` VALUES ('3922', '中国银联支付标记', '0010030', '', '', '19', '7', '6230747', '0');
INSERT INTO `authbank_cardbin` VALUES ('3923', '中国银联支付标记', '0010030', '', '', '19', '7', '6230748', '0');
INSERT INTO `authbank_cardbin` VALUES ('3924', '中国银联支付标记', '0010030', '', '', '19', '7', '6230749', '0');
INSERT INTO `authbank_cardbin` VALUES ('3925', '中国银联支付标记', '0010030', '', '', '16', '7', '6235241', '0');
INSERT INTO `authbank_cardbin` VALUES ('3926', '中国银联支付标记', '0010030', '', '', '17', '7', '6235242', '0');
INSERT INTO `authbank_cardbin` VALUES ('3927', '中国银联支付标记', '0010030', '', '', '18', '7', '6235243', '0');
INSERT INTO `authbank_cardbin` VALUES ('3928', '中国银联支付标记', '0010030', '', '', '19', '7', '6235244', '0');
INSERT INTO `authbank_cardbin` VALUES ('3929', '中国银联支付标记', '0010030', '', '', '19', '7', '6235245', '0');
INSERT INTO `authbank_cardbin` VALUES ('3930', '中国银联支付标记', '0010030', '', '', '19', '7', '6235246', '0');
INSERT INTO `authbank_cardbin` VALUES ('3931', '中国银联支付标记', '0010030', '', '', '19', '7', '6235248', '0');
INSERT INTO `authbank_cardbin` VALUES ('3932', '中国银联支付标记', '0010030', '', '', '16', '7', '6251640', '1');
INSERT INTO `authbank_cardbin` VALUES ('3933', '中国银联支付标记', '0010030', '', '', '16', '7', '6251642', '1');
INSERT INTO `authbank_cardbin` VALUES ('3934', '中国银联支付标记', '0010030', '', '', '16', '7', '6251643', '1');
INSERT INTO `authbank_cardbin` VALUES ('3935', '中国银联支付标记', '0010030', '', '', '16', '7', '6251644', '1');
INSERT INTO `authbank_cardbin` VALUES ('3936', '中国银联支付标记', '0010030', '', '', '16', '7', '6251645', '1');
INSERT INTO `authbank_cardbin` VALUES ('3937', '中国银联支付标记', '0010030', '', '', '17', '7', '6251646', '1');
INSERT INTO `authbank_cardbin` VALUES ('3938', '中国银联支付标记', '0010030', '', '', '18', '7', '6251647', '1');
INSERT INTO `authbank_cardbin` VALUES ('3939', '中国银联支付标记', '0010030', '', '', '19', '7', '6251648', '1');
INSERT INTO `authbank_cardbin` VALUES ('3940', '中国银联支付标记', '0010030', '', '', '19', '7', '6251649', '1');
INSERT INTO `authbank_cardbin` VALUES ('3941', '中国银联支付标记', '0010030', '', '', '16', '7', '6258240', '1');
INSERT INTO `authbank_cardbin` VALUES ('3942', '中国银联支付标记', '0010030', '', '', '16', '7', '6258241', '1');
INSERT INTO `authbank_cardbin` VALUES ('3943', '中国银联支付标记', '0010030', '', '', '16', '7', '6258242', '1');
INSERT INTO `authbank_cardbin` VALUES ('3944', '中国银联支付标记', '0010030', '', '', '16', '7', '6258243', '1');
INSERT INTO `authbank_cardbin` VALUES ('3945', '中国银联支付标记', '0010030', '', '', '16', '7', '6258244', '1');
INSERT INTO `authbank_cardbin` VALUES ('3946', '中国银联支付标记', '0010030', '', '', '16', '7', '6258245', '1');
INSERT INTO `authbank_cardbin` VALUES ('3947', '中国银联支付标记', '0010030', '', '', '17', '7', '6258246', '1');
INSERT INTO `authbank_cardbin` VALUES ('3948', '中国银联支付标记', '0010030', '', '', '18', '7', '6258247', '1');
INSERT INTO `authbank_cardbin` VALUES ('3949', '中国银联支付标记', '0010030', '', '', '19', '7', '6258248', '1');
INSERT INTO `authbank_cardbin` VALUES ('3950', '中国银联支付标记', '0010030', '', '', '19', '7', '6258249', '1');
INSERT INTO `authbank_cardbin` VALUES ('3951', '中国银联支付标记', '0010030', '', '', '19', '7', '6230744', '0');
INSERT INTO `authbank_cardbin` VALUES ('3952', '中国银联支付标记', '0010030', '', '', '19', '7', '6235247', '0');
INSERT INTO `authbank_cardbin` VALUES ('3953', '中国银联支付标记', '0010030', '', '', '16', '7', '6251641', '1');
INSERT INTO `authbank_cardbin` VALUES ('3954', '中国银联支付标记', '0010030', '', '', '19', '7', '6201360', '2');
INSERT INTO `authbank_cardbin` VALUES ('3955', '中国银联支付标记', '0010030', '', '', '16', '7', '6201364', '2');
INSERT INTO `authbank_cardbin` VALUES ('3956', '中国银联支付标记', '0010030', '', '', '19', '7', '6235240', '0');
INSERT INTO `authbank_cardbin` VALUES ('3957', '中国银联支付标记', '0010030', '', '', '16', '7', '6235249', '0');
INSERT INTO `authbank_cardbin` VALUES ('3958', '邮政储蓄银行', '1009999', '', '绿卡通区域性主题卡', '19', '6', '622187', '0');
INSERT INTO `authbank_cardbin` VALUES ('3959', '邮政储蓄银行', '1009999', '', '教育卡', '19', '6', '622189', '0');
INSERT INTO `authbank_cardbin` VALUES ('3960', '江西银行', '4484210', '', '南昌银行单位结算卡', '16', '8', '62326522', '0');
INSERT INTO `authbank_cardbin` VALUES ('3961', '邯郸银行', '5171270', '', '', '18', '6', '623653', '0');
INSERT INTO `authbank_cardbin` VALUES ('3962', '江西银行', '64484210', '', '南昌银行信用卡', '16', '6', '622718', '1');
INSERT INTO `authbank_cardbin` VALUES ('3963', '张家口银行', '64901380', '', '张家口市商业银行好运公务卡', '16', '6', '628365', '1');
INSERT INTO `authbank_cardbin` VALUES ('3964', '工商银行', '', '', '', '19', '6', '622202', '1');
INSERT INTO `authbank_cardbin` VALUES ('3965', '工商银行', '', '', '', '19', '6', '622202', '1');
INSERT INTO `authbank_cardbin` VALUES ('3966', '工商银行', '', '', '', '19', '6', '622202', '1');
INSERT INTO `authbank_cardbin` VALUES ('3967', '工商银行', '', '', '', '19', '6', '622202', '1');
INSERT INTO `authbank_cardbin` VALUES ('3968', '工商银行', '', '', '', '19', '6', '622202', '1');

-- ----------------------------
-- Table structure for authbank_channel
-- ----------------------------
DROP TABLE IF EXISTS `authbank_channel`;
CREATE TABLE `authbank_channel` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '应用id',
  `company_name` varchar(30) NOT NULL COMMENT '通道名称',
  `product_name` varchar(30) DEFAULT NULL COMMENT '产品名称',
  `mechart_num` varchar(100) NOT NULL COMMENT '商编',
  `gateway` varchar(50) NOT NULL COMMENT '通道程序入口',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0:未开通; 1:已开通; 2:临时关闭;',
  `is_dict` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:默认通道;(如获取不到卡bin走默认通道)',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `sort_num` int(10) NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8 COMMENT='鉴权通道表';

-- ----------------------------
-- Records of authbank_channel
-- ----------------------------
INSERT INTO `authbank_channel` VALUES ('223', '18', '天行', '先花一个亿', '', 'txsk\\TxskServer', '1', '1', '2018-10-31 00:00:00', '2');

-- ----------------------------
-- Table structure for authbank_channel_bank
-- ----------------------------
DROP TABLE IF EXISTS `authbank_channel_bank`;
CREATE TABLE `authbank_channel_bank` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) NOT NULL COMMENT '通道表id',
  `std_bankname` varchar(30) NOT NULL COMMENT '标准银行名称',
  `bankname` varchar(30) NOT NULL COMMENT '银行名称',
  `bankcode` varchar(30) NOT NULL COMMENT '银行编号',
  `card_type` int(10) NOT NULL DEFAULT '0' COMMENT '1:储蓄卡; 2:信用卡',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '0:未启用; 1:正常;  2:临时关闭;',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='鉴权通道银行卡支持';

-- ----------------------------
-- Records of authbank_channel_bank
-- ----------------------------
INSERT INTO `authbank_channel_bank` VALUES ('1', '223', '工商银行', '工商银行', 'ICBC', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('2', '223', '农业银行', '农业银行', 'ABC', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('3', '223', '中国银行', '中国银行', 'BOC', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('4', '223', '建设银行', '建设银行', 'CCB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('5', '223', '交通银行', '交通银行', 'BCM', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('6', '223', '中国邮政储蓄', '中国邮政储蓄', 'POST', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('7', '223', '招商银行', '招商银行', 'CMB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('8', '223', '光大银行', '光大银行', 'CEB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('9', '223', '中信银行', '中信银行', 'ECITIC', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('10', '223', '华夏银行', '华夏银行', 'HXB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('11', '223', '浦发银行', '浦发银行', 'SPDB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('12', '223', '民生银行', '民生银行', 'CMBC', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('13', '223', '平安银行', '平安银行', 'PINGAN', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('14', '223', '广发银行', '广发银行', 'GDB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('15', '223', '兴业银行', '兴业银行', 'CIB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('16', '223', '北京银行', '北京银行', 'BOB', '1', '1', '2018-05-10 14:00:00');
INSERT INTO `authbank_channel_bank` VALUES ('17', '223', '上海银行', '上海银行', 'SHB', '1', '1', '2018-05-10 14:00:00');

-- ----------------------------
-- Table structure for authbank_order
-- ----------------------------
DROP TABLE IF EXISTS `authbank_order`;
CREATE TABLE `authbank_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) NOT NULL COMMENT '通道id',
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户姓名',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `card_type` int(1) NOT NULL COMMENT '1:储蓄卡; 2:信用卡',
  `phone` varchar(20) NOT NULL COMMENT '银行预留手机号码',
  `bankname` varchar(50) NOT NULL COMMENT '银行名称',
  `bankcode` varchar(20) NOT NULL COMMENT '银行编号',
  `create_time` datetime NOT NULL,
  `modify_time` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '绑卡状态：0 初始：1 成功；2 失败 3 解绑 ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cardno` (`cardno`),
  KEY `idx_createtime` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='鉴权主表';


-- ----------------------------
-- Table structure for cj_xy_order
-- ----------------------------
DROP TABLE IF EXISTS `cj_xy_order`;
CREATE TABLE `cj_xy_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `payorder_id` int(10) unsigned NOT NULL COMMENT '主订单id',
  `aid` int(11) unsigned NOT NULL COMMENT '应用id',
  `channel_id` int(10) unsigned NOT NULL COMMENT '通道id',
  `orderid` varchar(30) NOT NULL COMMENT '客户订单号',
  `cli_orderid` varchar(50) NOT NULL COMMENT '发送畅捷的订单号',
  `amount` int(10) unsigned NOT NULL COMMENT '交易金额',
  `productcatalog` int(10) unsigned NOT NULL COMMENT '商品类别码',
  `productname` varchar(50) NOT NULL COMMENT '商品名称',
  `productdesc` varchar(200) NOT NULL COMMENT '商品描述',
  `identityid` varchar(20) NOT NULL COMMENT '用户标识',
  `cli_identityid` varchar(50) DEFAULT NULL COMMENT '畅捷用户标识',
  `orderexpdate` int(10) unsigned NOT NULL COMMENT '订单有效期时间 以分为单位',
  `userip` varchar(50) NOT NULL COMMENT '用户IP',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡序列号',
  `bankcardtype` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未知 1借记 2信用',
  `bankcode` varchar(20) NOT NULL COMMENT '银行编号',
  `idcardtype` varchar(10) NOT NULL COMMENT '证件类型 01：身份证',
  `idcard` varchar(50) NOT NULL COMMENT '证件号',
  `name` varchar(30) NOT NULL COMMENT '持卡人姓名',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `expiry_date` char(32) NOT NULL DEFAULT '0' COMMENT '贷记卡有效期',
  `cvv2` char(32) NOT NULL DEFAULT '0' COMMENT '贷记卡安全码',
  `create_time` datetime NOT NULL COMMENT '(内部)创建时间',
  `modify_time` datetime NOT NULL COMMENT '(内部)最后修改时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '(内部)0:默认; 2:支付成功 ;11:支付失败(应该不存在此种状态)',
  `other_orderid` varchar(50) NOT NULL COMMENT '(内部)畅捷流水号',
  `error_code` varchar(50) NOT NULL DEFAULT '0' COMMENT '(内部)畅捷返回错误码',
  `error_msg` varchar(50) NOT NULL COMMENT '(内部)畅捷返回错误描述',
  `version` int(10) NOT NULL DEFAULT '0' COMMENT '乐观锁版本',
  `bind_id` int(10) unsigned DEFAULT '0' COMMENT '绑定id',
  `has_send` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已申请',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_orderid` (`orderid`) USING BTREE,
  KEY `idx_idcard` (`idcard`),
  KEY `idx_aid` (`aid`),
  KEY `idx_payorder_id` (`payorder_id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_cli_orderid` (`cli_orderid`),
  KEY `idx_identityid` (`identityid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='畅捷协议支付订单表';


-- ----------------------------
-- Table structure for jxl_request
-- ----------------------------
DROP TABLE IF EXISTS `jxl_request`;
CREATE TABLE `jxl_request` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL COMMENT '应用id',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `idcard` varchar(20) NOT NULL COMMENT '银行卡',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `token` varchar(50) NOT NULL COMMENT 'token',
  `account` varchar(50) NOT NULL COMMENT '帐号',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `query_pwd` varchar(50) DEFAULT '' COMMENT '网站查询密码',
  `captcha` varchar(20) NOT NULL COMMENT '网站动态验证',
  `method` varchar(20) NOT NULL DEFAULT '' COMMENT '融下一步请求接口的名字',
  `type` varchar(20) NOT NULL COMMENT 'SUBMIT_CAPTCHA（提交动态验证码） |  RESEND_CAPTCHA（重发动态验证码）',
  `website` varchar(50) NOT NULL COMMENT '网站英文名称',
  `response_type` varchar(20) NOT NULL COMMENT '1 CONTROL控制类型的响应结果; 2 ERROR错误类型的响应结果 ;3 RUNNING 正在运行',
  `process_code` int(1) NOT NULL DEFAULT '0' COMMENT '流程码，见文档',
  `source` int(1) DEFAULT '1' COMMENT '来源:1:XIANHUAHUA; 2:kuaip',
  `from` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '来源 1: H5  2: app',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `modify_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `result_status` int(1) NOT NULL DEFAULT '0' COMMENT '1 采集完成',
  `result` text NOT NULL COMMENT '采集结果',
  `contacts` text NOT NULL COMMENT '常见联系人',
  `callbackurl` varchar(100) NOT NULL COMMENT '回调地址',
  `client_status` int(10) NOT NULL DEFAULT '0' COMMENT '客户端响应状态',
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='聚信立请求纪录表';


-- ----------------------------
-- Table structure for jxl_stat
-- ----------------------------
DROP TABLE IF EXISTS `jxl_stat`;
CREATE TABLE `jxl_stat` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '应用id',
  `requestid` int(10) NOT NULL DEFAULT '0' COMMENT '请求id',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `idcard` varchar(20) NOT NULL COMMENT '身份证',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `website` varchar(50) NOT NULL COMMENT '网站英文名称',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `is_valid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '检查;1:报告ok;2:详情ok;3:都ok',
  `url` varchar(100) NOT NULL COMMENT '统计JSON存储地址',
  `source` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '来源:1:XIANHUAHUA; 2:kuaip 3:rong360 4:上数',
  PRIMARY KEY (`id`),
  KEY `idx_requestid` (`requestid`),
  KEY `idx_phone` (`phone`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of jxl_stat
-- ----------------------------

-- ----------------------------
-- Table structure for operator_client_notify
-- ----------------------------
DROP TABLE IF EXISTS `operator_client_notify`;
CREATE TABLE `operator_client_notify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestid` int(11) NOT NULL COMMENT '请求id',
  `tip` varchar(255) NOT NULL COMMENT '通知内容',
  `grab_status` int(1) NOT NULL DEFAULT '0' COMMENT '采集状态:0:初始 1:成功 2:处理中;3:失败;',
  `notify_num` int(1) NOT NULL DEFAULT '0' COMMENT '通知次数: 上限7次',
  `notify_status` int(1) NOT NULL DEFAULT '0' COMMENT '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 4:通知失败 5:通知达上限',
  `notify_time` datetime NOT NULL COMMENT '下次通知时间',
  `reason` varchar(20) NOT NULL COMMENT '通知失败原因:例如没有回调地址',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_remit_id` (`requestid`),
  KEY `idx_notify_time` (`notify_time`),
  KEY `idx_notify_status` (`notify_status`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='运营商采集发通知';


-- ----------------------------
-- Table structure for pay_alipay_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_alipay_order`;
CREATE TABLE `pay_alipay_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `payorder_id` varchar(50) NOT NULL COMMENT '商户订单号',
  `cli_orderid` varchar(50) NOT NULL COMMENT '发送给宝付的orderid',
  `aid` int(11) NOT NULL DEFAULT '1' COMMENT '应用id',
  `channel_id` int(10) NOT NULL DEFAULT '0' COMMENT '通道id',
  `amount` decimal(10,4) NOT NULL COMMENT '交易金额(单位：分)',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '0:默认;2:成功;4处理中;11:失败',
  `other_orderid` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方交易号',
  `error_code` varchar(20) NOT NULL DEFAULT '' COMMENT '返回错误码',
  `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '返回错误描述',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime NOT NULL COMMENT '最后修改时间',
  `version` int(10) NOT NULL COMMENT '版本号',
  `type` tinyint(10) NOT NULL DEFAULT '0' COMMENT '支付类型：1支付宝H5，2支付宝扫码，3微信扫码，4快捷支付',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='支付宝微信支付订单';


-- ----------------------------
-- Table structure for pay_app
-- ----------------------------
DROP TABLE IF EXISTS `pay_app`;
CREATE TABLE `pay_app` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用编号',
  `name` varchar(50) NOT NULL COMMENT '商家名称',
  `app_id` varchar(100) NOT NULL COMMENT '商家帐号',
  `auth_type` int(1) NOT NULL DEFAULT '1' COMMENT '1: 3des',
  `auth_key` varchar(100) DEFAULT NULL COMMENT '加密秘钥（3des）',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='商户表';

-- ----------------------------
-- Records of pay_app
-- ----------------------------
INSERT INTO `pay_app` VALUES ('18', '花卡打包', '66015253283250201901', '1', 'S3p4xgJtA1u5M7302npievwO9JM7Kdju4kDD', '1');

-- ----------------------------
-- Table structure for pay_bank_standard
-- ----------------------------
DROP TABLE IF EXISTS `pay_bank_standard`;
CREATE TABLE `pay_bank_standard` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `bankname` varchar(30) NOT NULL COMMENT '标准化名称',
  `alias` varchar(30) NOT NULL COMMENT '别名:银行卡或编码',
  `bankcode` varchar(20) NOT NULL COMMENT '银行编码(暂时无用)',
  `forbidden` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0' COMMENT '禁用状态:0:启用 1禁用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_bank_standard
-- ----------------------------
INSERT INTO `pay_bank_standard` VALUES ('1', '上海银行', '上海银行', 'SHB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('2', '中信银行', '中信银行', 'ECITIC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('3', '中国银行', '中国银行(马来西亚)', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('4', '中国银行', '中国银行雅加达分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('5', '中国银行', '中国银行马尼拉分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('6', '中国银行', '中国银行香港有限公司', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('7', '中国银行', '中国银行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('8', '中国银行', '中国银行首尔分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('9', '中国银行', '中国银行（香港）', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('10', '中国银行', '中国银行胡志明分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('11', '中国银行', '中国银行(香港)', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('12', '中国银行', '中国银行（澳大利亚）', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('13', '中国银行', '中国银行澳门分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('14', '中国银行', '中国银行曼谷分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('15', '中国银行', '中国银行金边分行', 'BOC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('16', '交通银行', '交通银行香港分行', 'BCM', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('17', '交通银行', '交通银行澳门分行', 'BCM', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('18', '交通银行', '交通银行', 'BCM', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('19', '光大银行', '光大银行', 'CEB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('20', '光大银行', '中国光大银行', 'CEB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('21', '兴业银行', '法国兴业银行（中国）', 'CIB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('22', '兴业银行', '兴业银行', 'CIB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('23', '农业银行', '宁波市农业银行', 'ABC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('24', '农业银行', '农业银行', 'ABC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('25', '农业银行', '中国农业银行贷记卡', 'ABC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('26', '北京银行', '北京银行', 'BOB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('27', '华夏银行', '华夏银行', 'HXB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('28', '工商银行', '中国工商银行巴黎分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('29', '工商银行', '中国工商银行新加坡分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('30', '工商银行', '中国工商银行卢森堡分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('31', '工商银行', '中国工商银行(印尼)', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('32', '工商银行', '中国工商银行（巴西）', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('33', '工商银行', '中国工商银行伦敦子行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('34', '工商银行', '中国工商银行阿姆斯特丹', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('35', '工商银行', '中国工商银行米兰分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('36', '工商银行', '中国工商银行法兰克福分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('37', '工商银行', '工商银行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('38', '工商银行', '工银河内', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('39', '工商银行', '中国工商银行澳门分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('40', '工商银行', '中国工商银行印尼分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('41', '工商银行', '中国工商银行金边分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('42', '工商银行', '工银新西兰', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('43', '工商银行', '中国工商银行阿拉木图子行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('44', '工商银行', '工银法兰克福', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('45', '工商银行', '中国工商银行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('46', '工商银行', '中国工商银行马德里分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('47', '工商银行', '中国工商银行（澳门）', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('48', '工商银行', '工银印尼', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('49', '工商银行', '中国工商银行加拿大分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('50', '工商银行', '中国工商银行卡拉奇分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('51', '工商银行', '中国工商银行万象分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('52', '工商银行', '中国工商银行(亚洲)有限公司', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('53', '工商银行', '中国工商银行布鲁塞尔分行', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('54', '工商银行', '工银泰国', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('55', '工商银行', '工银马来西亚', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('56', '工商银行', '中国工商银行（印度尼西亚）', 'ICBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('57', '平安银行', '平安银行', 'PINGAN', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('58', '广发银行', '广发银行股份有限公司', 'GDB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('59', '广州银行', '广州银行', 'GZCB', '1', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('60', '建设银行', '中国建设银行（亚洲）', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('61', '建设银行', '建设银行', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('62', '建设银行', '中国建设银行亚洲股份有限公司', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('63', '建设银行', '中国建设银行', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('64', '建设银行', '建行厦门分行', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('65', '建设银行', '中国建设银行澳门股份有限公司', 'CCB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('66', '招商银行', '招商银行', 'CMB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('67', '民生银行', '民生银行', 'CMBC', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('68', '浦发银行', '浦东发展银行', 'SPDB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('69', '邮政储蓄', '中国邮政储蓄', 'POST', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('70', '邮政储蓄', '中国邮政储蓄银行信用卡中心', 'POST', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('71', '广发银行', '广发银行', 'GDB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('72', '浦发银行', '浦发银行', 'SPDB', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('73', '邮政储蓄', '邮政储蓄', 'POST', '0', '2016-11-22 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('75', '上海农村商业银行', '上海农村商业银行', 'SHRCB', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('76', '上海农村商业银行', '上海农商银行贷记卡', 'SHRCB', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('77', '上海农村商业银行', '上海农商银行', 'SHRCB', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('78', '东亚银行', '东亚银行', 'HKBEA', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('79', '东亚银行', '东亚银行中国有限公司', 'HKBEA', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('80', '东亚银行', '东亚银行有限公司', 'HKBEA', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('81', '东亚银行', '东亚银行澳门分行', 'HKBEA', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('82', '东亚银行', '东亚银行(中国)有限公司', 'HKBEA', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('83', '南京银行', '南京银行', 'NJCB', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('84', '宁波银行', '宁波银行', 'NBCB', '0', '0000-00-00 00:00:00');
INSERT INTO `pay_bank_standard` VALUES ('85', '杭州银行', '杭州银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('86', '浙商银行', '浙商银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('87', '东莞银行', '东莞银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('88', '恒丰银行', '恒丰银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('89', '东莞农村商业银行', '东莞农村商业银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('90', '广东省农村信用社联合社', '广东省农村信用社联合社', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('91', '广州农村商业银行', '广州农村商业银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('92', '深圳农村商业银行', '深圳农村商业银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('93', '甘肃省农村信用社联合社', '甘肃省农村信用社联合社', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('94', '江苏长江商业银行', '江苏长江商业银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('95', '黑龙江省农村信用社联合社', '黑龙江省农村信用社联合社', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('96', '广东南粤银行', '广东南粤银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('97', '武汉农村商业银行', '武汉农村商业银行', '', '0', '2017-05-02 09:52:04');
INSERT INTO `pay_bank_standard` VALUES ('98', '江苏银行', '江苏银行', '', '0', '2017-05-11 10:59:15');

-- ----------------------------
-- Table structure for pay_bf_sign
-- ----------------------------
DROP TABLE IF EXISTS `pay_bf_sign`;
CREATE TABLE `pay_bf_sign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `aid` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '应用id',
  `channel_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '支付通道id',
  `identityid` varchar(20) NOT NULL DEFAULT '' COMMENT '用户唯一',
  `cli_identityid` varchar(50) NOT NULL DEFAULT '' COMMENT '宝付唯一用户标识',
  `pre_sign_msg` varchar(50) NOT NULL DEFAULT '' COMMENT '预签约报文流水号',
  `sign_msg` varchar(50) NOT NULL DEFAULT '' COMMENT '签约报文流水号',
  `bankname` varchar(50) NOT NULL DEFAULT '' COMMENT '银行名称',
  `bankcard_type` char(5) NOT NULL DEFAULT '101' COMMENT '银行卡类型 101借记卡,102信用卡',
  `cardno` varchar(50) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `card_cvv2` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '信用卡安全码',
  `card_date` varchar(5) NOT NULL DEFAULT '' COMMENT '信用卡有效期',
  `idcard` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号',
  `idcard_type` char(10) NOT NULL DEFAULT '01' COMMENT '证件类型 01身份证',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '银行留存电话',
  `pre_sign_code` varchar(50) NOT NULL DEFAULT '' COMMENT '预签约唯一码',
  `sign_code` varchar(150) NOT NULL DEFAULT '' COMMENT '宝付返回签约码',
  `create_time` datetime NOT NULL COMMENT '(内部)创建时间',
  `modify_time` datetime NOT NULL COMMENT '(内部)最后修改时间',
  `error_code` varchar(20) NOT NULL DEFAULT '0' COMMENT '(内部)宝付返回错误码',
  `error_msg` varchar(100) NOT NULL DEFAULT '' COMMENT '(内部)宝付返回原因',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:默认;1:发送验证码成功;2:签约成功;3:发送验证码失败,11:签约失败;',
  PRIMARY KEY (`id`),
  KEY `idx_cardno` (`cardno`),
  KEY `idx_identityid` (`identityid`),
  KEY `idx_phone` (`phone`) USING BTREE,
  KEY `idx_pre_sign_msgid` (`pre_sign_msg`) USING BTREE,
  KEY `idx_sign_msgid` (`sign_msg`) USING BTREE,
  KEY `idx_sign_code` (`sign_code`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='宝付签约表';


-- ----------------------------
-- Table structure for pay_bfxy_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_bfxy_order`;
CREATE TABLE `pay_bfxy_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `aid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '应用id',
  `channel_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '支付通道',
  `payorder_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主订单id',
  `bind_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签约id',
  `orderid` varchar(30) NOT NULL COMMENT '客户订单号',
  `cli_orderid` varchar(50) NOT NULL COMMENT '发送给宝付的orderid',
  `identityid` varchar(20) NOT NULL COMMENT '用户标识',
  `cli_identityid` varchar(50) NOT NULL COMMENT '宝付用户标识',
  `cardno` varchar(50) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易金额',
  `productname` varchar(50) NOT NULL DEFAULT '' COMMENT '商品名称',
  `productdesc` varchar(200) NOT NULL DEFAULT '' COMMENT '商品描述',
  `orderexpdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单有效期时间 以分为单位',
  `userip` varchar(30) NOT NULL DEFAULT '' COMMENT '用户ip',
  `create_time` datetime NOT NULL COMMENT '(内部)创建时间',
  `modify_time` datetime NOT NULL COMMENT '(内部)最后修改时间',
  `status` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '(内部)0:默认; 1:请求成功; 2:成功; 3:未处理; 4:处理中; 5:已撤消; 11:支付失败;  12:请求失败;',
  `other_orderid` varchar(50) NOT NULL DEFAULT '' COMMENT '(内部)宝付流水号',
  `error_code` varchar(50) NOT NULL DEFAULT '0' COMMENT '(内部)宝付返回错误码',
  `error_msg` varchar(50) NOT NULL DEFAULT '' COMMENT '(内部)宝付返回错误描述',
  `version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '乐观锁',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_orderid` (`orderid`),
  KEY `idx_aid` (`aid`),
  KEY `idx_payorder_id` (`payorder_id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_cli_orderid` (`cli_orderid`),
  KEY `idx_identityid` (`identityid`),
  KEY `idx_status` (`status`),
  KEY `idx_cardno` (`cardno`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='宝付认证api订单表';


-- ----------------------------
-- Table structure for pay_bindbank
-- ----------------------------
DROP TABLE IF EXISTS `pay_bindbank`;
CREATE TABLE `pay_bindbank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bind_no` varchar(100) NOT NULL COMMENT '支付通首绑卡id',
  `aid` int(11) NOT NULL,
  `channel_id` int(10) NOT NULL,
  `identityid` varchar(50) NOT NULL COMMENT '商户生成的用户唯一标识',
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `name` varchar(20) NOT NULL COMMENT '用户姓名',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `card_type` int(1) NOT NULL COMMENT '1:储蓄卡; 2:信用卡',
  `phone` varchar(20) NOT NULL COMMENT '银行预留手机号码',
  `bankname` varchar(50) NOT NULL COMMENT '银行名称',
  `bankcode` varchar(20) NOT NULL COMMENT '银行编号',
  `validate` varchar(50) NOT NULL DEFAULT '0' COMMENT '贷记卡有效期',
  `cvv2` varchar(50) NOT NULL DEFAULT '0' COMMENT '贷记卡cvv2码',
  `userip` varchar(30) NOT NULL COMMENT '绑卡IP',
  `create_time` datetime NOT NULL,
  `modify_time` datetime NOT NULL,
  `smscode` varchar(10) NOT NULL DEFAULT '0' COMMENT '短信验证码',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '绑卡状态：0 初始：1 成功；2 失败',
  PRIMARY KEY (`id`),
  KEY `idx_aid` (`aid`),
  KEY `idx_channelid` (`channel_id`),
  KEY `idx_identityid` (`identityid`),
  KEY `idx_cardno` (`cardno`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='支付绑卡表';


-- ----------------------------
-- Table structure for pay_black_ip
-- ----------------------------
DROP TABLE IF EXISTS `pay_black_ip`;
CREATE TABLE `pay_black_ip` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `ip` varchar(30) NOT NULL COMMENT 'IP地址',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='黑名单';

-- ----------------------------
-- Records of pay_black_ip
-- ----------------------------

-- ----------------------------
-- Table structure for pay_business
-- ----------------------------
DROP TABLE IF EXISTS `pay_business`;
CREATE TABLE `pay_business` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `aid` int(10) NOT NULL COMMENT '应用id',
  `name` varchar(50) NOT NULL COMMENT '先花一亿元; 花生米富',
  `business_code` varchar(50) NOT NULL COMMENT '唯一: 业务号',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0:未开通; 1:已开通; 2:临时关闭;',
  `tip` varchar(255) NOT NULL COMMENT '说明',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_business_code` (`business_code`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='业务表';

-- ----------------------------
-- Records of pay_business
-- ----------------------------
INSERT INTO `pay_business` VALUES ('34', '18', '花卡打包快捷', 'HKDBKJ', '1', '花卡打包', '2018-12-02 17:00:00');
INSERT INTO `pay_business` VALUES ('35', '18', '花卡打包展期', 'HKDBZQ', '1', '花卡打包', '2018-12-02 17:00:00');
INSERT INTO `pay_business` VALUES ('37', '18', '花卡打包支付宝', 'HKDBZFB', '1', '花卡打包', '2018-12-02 17:00:00');

-- ----------------------------
-- Table structure for pay_business_chan
-- ----------------------------
DROP TABLE IF EXISTS `pay_business_chan`;
CREATE TABLE `pay_business_chan` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '应用id',
  `business_id` int(10) NOT NULL COMMENT '业务id',
  `channel_id` int(10) NOT NULL COMMENT '通道id',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0:未开通; 1:已开通; 2:临时关闭;',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `sort_num` int(10) NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='产品对应支付通道';

-- ----------------------------
-- Records of pay_business_chan
-- ----------------------------
INSERT INTO `pay_business_chan` VALUES ('134', '18', '34', '171', '1', '2018-10-30 16:00:00', '3');
INSERT INTO `pay_business_chan` VALUES ('135', '18', '34', '184', '1', '2018-10-30 16:00:00', '1');
INSERT INTO `pay_business_chan` VALUES ('136', '18', '34', '177', '1', '2018-10-30 16:00:00', '2');
INSERT INTO `pay_business_chan` VALUES ('154', '18', '37', '170', '1', '0000-00-00 00:00:00', '1');

-- ----------------------------
-- Table structure for pay_channel
-- ----------------------------
DROP TABLE IF EXISTS `pay_channel`;
CREATE TABLE `pay_channel` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(30) NOT NULL COMMENT '通道名称:易宝, 融宝',
  `product_name` varchar(30) DEFAULT NULL COMMENT '产品名称:易宝投资通, 融宝快捷',
  `mechart_num` varchar(100) NOT NULL COMMENT '商编',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '0:未开通; 1:已开通; 2:临时关闭;',
  `tip` varchar(255) NOT NULL COMMENT '支付说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=188 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='支付通道';

-- ----------------------------
-- Records of pay_channel
-- ----------------------------
INSERT INTO `pay_channel` VALUES ('170', '支付宝-萍乡', '一麻袋(萍乡海桐)', 'yft2018061400010', '1', '');
INSERT INTO `pay_channel` VALUES ('171', '宝付', '宝付协议支付(萍乡海桐)', '1223576', '1', '');
INSERT INTO `pay_channel` VALUES ('177', '畅捷', '畅捷协议支付(萍乡海桐)', '200001800004', '1', '畅捷协议支付');
INSERT INTO `pay_channel` VALUES ('184', '融宝', '融宝协议支付(海桐)', '100000001304472', '1', '协议支付，代付');

-- ----------------------------
-- Table structure for pay_channel_bank
-- ----------------------------
DROP TABLE IF EXISTS `pay_channel_bank`;
CREATE TABLE `pay_channel_bank` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `channel_id` int(10) NOT NULL COMMENT 'pay_channel.id',
  `std_bankname` varchar(30) NOT NULL COMMENT '标准银行名称',
  `bankname` varchar(30) NOT NULL COMMENT '银行名称',
  `bankcode` varchar(30) NOT NULL COMMENT '银行编号',
  `card_type` int(10) NOT NULL DEFAULT '0' COMMENT '1:储蓄卡; 2:信用卡',
  `status` int(1) NOT NULL DEFAULT '1' COMMENT '0:未启用; 1:正常;  2:临时关闭;',
  `limit_max_amount` decimal(12,2) NOT NULL DEFAULT '50000.00' COMMENT '单笔限额:默认5w',
  `limit_day_amount` decimal(12,2) NOT NULL DEFAULT '50000.00' COMMENT '日限额:默认5w',
  `limit_day_total` int(10) NOT NULL DEFAULT '999999' COMMENT '日限数',
  `limit_type` int(1) NOT NULL DEFAULT '0' COMMENT '0:不限; 1:时间段; 2:每日限; 3:周末限',
  `limit_start_time` varchar(50) NOT NULL COMMENT '限定起始时间',
  `limit_end_time` varchar(50) NOT NULL COMMENT '限定结束时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4093 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='支付通道银行卡支持';

-- ----------------------------
-- Records of pay_channel_bank
-- ----------------------------
INSERT INTO `pay_channel_bank` VALUES ('3791', '170', '', '', '', '1', '1', '5000.00', '20000.00', '99999', '0', '', '', '2018-06-25 11:39:54');
INSERT INTO `pay_channel_bank` VALUES ('3792', '171', '招商银行', '招商银行', 'CMB', '1', '0', '0.00', '0.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3793', '171', '交通银行', '交通银行', 'BCOM', '1', '0', '0.00', '0.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3794', '171', '上海银行', '上海银行', 'SHB', '1', '1', '100000.00', '400000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3795', '171', '平安银行', '平安银行', 'PAB', '1', '1', '50000.00', '50000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3796', '171', '华夏银行', '华夏银行', 'HXB', '1', '1', '20000.00', '20000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3797', '171', '光大银行', '光大银行', 'CEB', '1', '1', '10000.00', '10000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3798', '171', '中信银行', '中信银行', 'CITIC', '1', '1', '100000.00', '300000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3799', '171', '广发银行', '广发银行', 'GDB', '1', '1', '30000.00', '30000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3800', '171', '兴业银行', '兴业银行', 'CIB', '1', '1', '20000.00', '20000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3801', '171', '民生银行', '民生银行', 'CMBC', '1', '1', '10000.00', '50000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3802', '171', '邮政储蓄', '邮储银行', 'PSBC', '1', '1', '5000.00', '5000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3803', '171', '浦发银行', '浦发银行', 'SPDB', '1', '1', '20000.00', '20000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3804', '171', '建设银行', '建设银行', 'CCB', '1', '1', '50000.00', '100000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3805', '171', '中国银行', '中国银行', 'BOC', '1', '1', '10000.00', '10000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3806', '171', '农业银行', '农业银行', 'ABC', '1', '1', '5000.00', '5000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3807', '171', '工商银行', '工商银行', 'ICBC', '1', '1', '5000.00', '50000.00', '999999', '0', '', '', '2018-06-27 15:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3891', '177', '中国银行', '中国银行', 'BOC', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3892', '177', '农业银行', '农业银行', 'ABC', '2', '0', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3893', '177', '工商银行', '工商银行', 'ICBC', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3894', '177', '建设银行', '建设银行', 'CCB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3895', '177', '交通银行', '交通银行', 'BCOM', '2', '1', '5000.00', '20000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3896', '177', '招商银行', '招商银行', 'CMB', '2', '1', '5000.00', '20000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3897', '177', '民生银行', '民生银行', 'CMBC', '2', '0', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3898', '177', '兴业银行', '兴业银行', 'CIB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3899', '177', '中信银行', '中信银行', 'CITIC', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3900', '177', '平安银行', '平安银行', 'PAYH', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3901', '177', '光大银行', '光大银行', 'CEB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3902', '177', '广发银行', '广发银行', 'CGB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3903', '177', '浦发银行', '浦发银行', 'SPDB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3904', '177', '华夏银行', '华夏银行', 'HXB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3905', '177', '邮政储蓄', '邮政储蓄', 'PSBC', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3906', '177', '北京银行', '北京银行', 'BOB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3907', '177', '上海银行', '上海银行', 'SHB', '2', '1', '20000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3908', '177', '花旗银行', '花旗银行', '', '2', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3909', '177', '中国银行', '中国银行', 'BOC', '1', '1', '50000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3910', '177', '农业银行', '农业银行', 'ABC', '1', '0', '20000.00', '20000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3911', '177', '工商银行', '工商银行', 'ICBC', '1', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3912', '177', '建设银行', '建设银行', 'CCB', '1', '1', '20000.00', '20000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3913', '177', '民生银行', '民生银行', 'CMBC', '1', '0', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3914', '177', '兴业银行', '兴业银行', 'CIB', '1', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3915', '177', '中信银行', '中信银行', 'CITIC', '1', '1', '1000.00', '2000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3916', '177', '平安银行', '平安银行', 'PAYH', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3917', '177', '光大银行', '光大银行', 'CEB', '1', '1', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3918', '177', '广发银行', '广发银行', 'CGB', '1', '1', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3919', '177', '邮政储蓄', '邮政储蓄', 'PSBC', '1', '1', '5000.00', '5000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3920', '177', '北京银行', '北京银行', 'BOB', '1', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3921', '177', '上海银行', '上海银行', 'SHB', '1', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3922', '177', '宁波银行', '宁波银行', 'NBCB', '1', '0', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3923', '177', '重庆农商行', '重庆农商行', '', '1', '1', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3924', '177', '太仓农村商业银行', '太仓农村商业银行', '', '1', '1', '50000.00', '50000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3925', '177', '山东农信银', '山东农信银', '', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3926', '177', '长江商业银行', '长江商业银行', '', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3927', '177', '武汉农商行', '武汉农商行', '', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3928', '177', '黑龙江农信社', '黑龙江农信社', '', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3929', '177', '甘肃农信社', '甘肃农信社', '', '1', '1', '500000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3930', '177', '江南农村商业银行', '江南农村商业银行', '', '1', '1', '100000.00', '100000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3931', '177', '吉林省农信社', '吉林省农信社', '', '1', '1', '200000.00', '1000000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3932', '177', '绵阳市商业银行', '绵阳市商业银行', '', '1', '1', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3933', '177', '湖南省农村信用社联合社', '湖南省农村信用社联合社', '', '1', '1', '500000.00', '500000.00', '99999', '0', '', '', '2017-07-19 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3934', '177', '交通银行', '交通银行', 'BCM', '1', '1', '100000.00', '100000.00', '99999', '0', '', '', '2018-06-26 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3935', '177', '浦发银行', '浦发银行', 'SPDB', '1', '1', '100000.00', '100000.00', '99999', '0', '', '', '2018-06-26 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3936', '177', '浙商银行', '浙商银行', '', '1', '1', '100000.00', '100000.00', '99999', '0', '', '', '2018-06-26 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('3937', '177', '广州银行', '广州银行', 'GZCB', '1', '1', '100000.00', '100000.00', '99999', '0', '', '', '2018-06-26 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4028', '184', '中国银行', '中国银行', 'BOC', '1', '1', '10000.00', '10000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4029', '184', '农业银行', '农业银行', 'ABC', '1', '1', '2000.00', '10000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4030', '184', '工商银行', '工商银行', 'ICBC', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4031', '184', '建设银行', '建设银行', 'CCB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4032', '184', '兴业银行', '兴业银行', 'CIB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4033', '184', '光大银行', '光大银行', 'CEB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4034', '184', '民生银行', '民生银行', 'CMBC', '1', '1', '10000.00', '20000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4035', '184', '邮政储蓄', '邮政储蓄', 'PSBC', '1', '0', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4036', '184', '交通银行', '交通银行', 'BOCM', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4037', '184', '中信银行', '中信银行', 'CITIC', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4038', '184', '平安银行', '平安银行', 'PAYH', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4039', '184', '华夏银行', '华夏银行', 'HXB', '1', '0', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4040', '184', '浦发银行', '浦发银行', 'SPDB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4041', '184', '广发银行', '广发银行', 'CGB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4042', '184', '招商银行', '招商银行', 'CMB', '1', '0', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4043', '184', '北京银行', '北京银行', 'BCCB', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');
INSERT INTO `pay_channel_bank` VALUES ('4044', '184', '上海银行', '上海银行', 'SHBANK', '1', '1', '10000.00', '30000.00', '99999', '0', '', '', '2018-05-29 14:00:00');

-- ----------------------------
-- Table structure for pay_log
-- ----------------------------
DROP TABLE IF EXISTS `pay_log`;
CREATE TABLE `pay_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `app_id` varchar(50) NOT NULL COMMENT '客户端账号',
  `service_id` int(11) NOT NULL DEFAULT '0' COMMENT '服务id',
  `req_url` varchar(50) NOT NULL COMMENT '请求的相对地址',
  `req_ip` varchar(20) NOT NULL COMMENT '请求IP',
  `req_encrypt` text NOT NULL COMMENT '请求原信息(序列化)',
  `req_info` text NOT NULL COMMENT '请求解密信息(序列化)',
  `rsp_status` int(10) NOT NULL DEFAULT '0' COMMENT '响应状态0成功，其余失败',
  `rsp_info` text NOT NULL COMMENT '响应信息(序列化)',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `modify_time` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_app_id` (`app_id`),
  KEY `idx_service_id` (`service_id`),
  KEY `idx_req_ip` (`req_ip`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='日志表';

-- ----------------------------
-- Table structure for pay_notify
-- ----------------------------
DROP TABLE IF EXISTS `pay_notify`;
CREATE TABLE `pay_notify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payorder_id` int(11) NOT NULL COMMENT '支付主订单id',
  `notify_num` int(1) NOT NULL DEFAULT '0' COMMENT '通知次数: 上限7次',
  `notify_status` int(1) NOT NULL DEFAULT '0' COMMENT '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败; 13:通知超限',
  `notify_time` datetime NOT NULL COMMENT '下次通知时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_remit_id` (`payorder_id`),
  KEY `idx_notify_time` (`notify_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='支付通知表';

-- ----------------------------
-- Table structure for pay_payorder
-- ----------------------------
DROP TABLE IF EXISTS `pay_payorder`;
CREATE TABLE `pay_payorder` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `aid` int(10) NOT NULL COMMENT '商户应用id',
  `business_id` int(10) NOT NULL COMMENT '业务id',
  `channel_id` int(10) NOT NULL COMMENT '支付通道',
  `identityid` varchar(50) NOT NULL COMMENT '商户生成的用户唯一用户id',
  `orderid` varchar(30) NOT NULL COMMENT '商户生成的唯一绑卡请求号，最长',
  `other_orderid` varchar(50) NOT NULL COMMENT '第三方支付订单',
  `bankname` varchar(50) NOT NULL COMMENT '银行名称(标准化)',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `card_type` int(1) NOT NULL COMMENT '1:借记卡; 2:信用卡',
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `name` varchar(20) NOT NULL COMMENT '姓名',
  `phone` varchar(20) NOT NULL COMMENT '银行留存电话',
  `productcatalog` int(10) NOT NULL DEFAULT '0' COMMENT '商品类别码',
  `productname` varchar(50) NOT NULL COMMENT '商品名称',
  `productdesc` varchar(200) NOT NULL COMMENT '商品描述',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '交易金额：分',
  `orderexpdate` int(10) NOT NULL DEFAULT '0' COMMENT '订单有效期',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '(内部)创建时间',
  `modify_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '(内部)最后修改时间',
  `pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:初始状态,未处理; 1:未绑卡;  2:已支付; 11:支付失败',
  `res_code` varchar(50) NOT NULL COMMENT '响应码(标准化)',
  `res_msg` varchar(200) NOT NULL COMMENT '响应原因(标准化)',
  `callbackurl` varchar(200) NOT NULL COMMENT '回调地址',
  `userip` varchar(20) NOT NULL COMMENT '用户id地址',
  `smscode` varchar(20) NOT NULL COMMENT '短信验证码',
  `client_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '客户端状态',
  PRIMARY KEY (`id`),
  KEY `idx_orderid` (`orderid`),
  KEY `idx_aid` (`business_id`),
  KEY `idx_business_id` (`business_id`),
  KEY `idx_channel_id` (`channel_id`),
  KEY `idx_identityid` (`identityid`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for pay_rbxy_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_rbxy_order`;
CREATE TABLE `pay_rbxy_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payorder_id` int(11) NOT NULL COMMENT '主订单id',
  `aid` tinyint(2) NOT NULL COMMENT '应用id',
  `channel_id` int(11) NOT NULL COMMENT '通道id',
  `orderid` varchar(30) NOT NULL COMMENT '客户订单号--常用',
  `cli_orderid` varchar(50) NOT NULL COMMENT '唯一订单号',
  `loan_id` varchar(30) NOT NULL COMMENT '借款id',
  `identityid` varchar(50) NOT NULL COMMENT '用户id',
  `amount` int(11) NOT NULL COMMENT '交易金额（分）',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态(内部)，0默认，1获取验证码，8签约成功，5签约失败，4支付(处理）中，2支付成功，11支付失败',
  `modify_time` datetime NOT NULL COMMENT '更新时间（内部）',
  `create_time` datetime NOT NULL COMMENT '创建时间（内部）',
  `coupon_repay_amount` decimal(10,4) NOT NULL COMMENT '优惠卷之类',
  `account_id` varchar(30) NOT NULL COMMENT '账户id（没啥用）',
  `interest_fee` decimal(10,4) NOT NULL COMMENT '利息',
  `sign_no` varchar(100) NOT NULL COMMENT '签约协议号',
  `other_orderid` varchar(50) NOT NULL COMMENT '第三方流水号（融宝协议）',
  `error_code` varchar(50) NOT NULL COMMENT '返回错误码（融宝协议）',
  `error_msg` varchar(255) NOT NULL COMMENT '返回错误信息（融宝协议）',
  `callbackurl` varchar(255) NOT NULL COMMENT '回调地址',
  `name` varchar(20) NOT NULL COMMENT '姓名',
  `idcard` varchar(20) NOT NULL COMMENT '身份证',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `bankname` varchar(50) NOT NULL COMMENT '银行名称',
  `card_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '银行卡类型（1:借记卡; 2:信用卡）',
  `productname` varchar(50) NOT NULL COMMENT '商品名称',
  `productdesc` varchar(255) NOT NULL COMMENT '商品描述',
  `productcatalog` int(11) NOT NULL COMMENT '商品编码',
  `version` int(11) NOT NULL COMMENT '版本号',
  `userip` varchar(50) NOT NULL COMMENT '用户ip',
  `smscode` varchar(8) NOT NULL COMMENT '短信验证码',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_payorder_id` (`payorder_id`),
  KEY `idx_orderid` (`orderid`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_modify_time` (`modify_time`),
  KEY `idx_phone` (`phone`),
  KEY `idx_sign_no` (`sign_no`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='融宝协议支付';

-- ----------------------------
-- Table structure for pay_white_ip
-- ----------------------------
DROP TABLE IF EXISTS `pay_white_ip`;
CREATE TABLE `pay_white_ip` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '应用表id',
  `ip` varchar(50) NOT NULL COMMENT 'IP地址',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:未启用, 1:启用',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=286 DEFAULT CHARSET=utf8 COMMENT='IP白名单表';

-- ----------------------------
-- Records of pay_white_ip
-- ----------------------------
INSERT INTO `pay_white_ip` VALUES ('283', '18', '192.168.8.142', '1', '0000-00-00 00:00:00');
INSERT INTO `pay_white_ip` VALUES ('284', '18', '127.0.0.1', '1', '0000-00-00 00:00:00');
INSERT INTO `pay_white_ip` VALUES ('285', '18', '121.69.71.58', '1', '0000-00-00 00:00:00');
INSERT INTO `pay_white_ip` VALUES ('233', '18', '116.62.3.91', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('234', '18', '120.55.133.64', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('235', '18', '120.27.253.153', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('236', '18', '121.199.128.74', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('237', '18', '121.199.129.41', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('238', '18', '120.55.214.13', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('239', '18', '120.55.215.229', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('240', '18', '116.62.1.169', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('241', '18', '120.55.214.227', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('242', '18', '116.62.1.196', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('243', '18', '120.55.215.181', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('244', '18', '121.199.129.14', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('245', '18', '120.55.135.2', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('246', '18', '116.62.0.29', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('247', '18', '120.55.134.250', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('248', '18', '116.62.1.12', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('249', '18', '120.55.214.228', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('250', '18', '120.55.215.123', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('251', '18', '116.62.0.93', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('252', '18', '116.62.1.116', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('253', '18', '116.62.0.200', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('254', '18', '115.29.182.182', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('255', '18', '121.199.129.218', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('256', '18', '114.55.158.72', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('257', '18', '182.92.80.211', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('258', '18', '117.114.147.199', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('259', '18', '124.193.149.180', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('260', '18', '121.199.129.165', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('261', '18', '124.200.104.130', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('262', '18', '120.55.213.70', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('263', '18', '120.55.133.48', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('264', '18', '120.55.213.81', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('265', '18', '182.92.80.211', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('266', '18', '120.55.108.13', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('267', '18', '121.199.129.167', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('268', '18', '121.199.129.16', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('269', '18', '121.199.129.222', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('270', '18', '120.55.108.133', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('271', '18', '222.128.0.138', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('272', '18', '222.128.0.137', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('273', '18', '127.0.0.1', '1', '2018-10-24 16:00:00');
INSERT INTO `pay_white_ip` VALUES ('274', '18', '47.93.121.86', '1', '0000-00-00 00:00:00');
INSERT INTO `pay_white_ip` VALUES ('282', '18', '47.95.140.218', '1', '0000-00-00 00:00:00');

-- ----------------------------
-- Table structure for pay_xy_bindbank
-- ----------------------------
DROP TABLE IF EXISTS `pay_xy_bindbank`;
CREATE TABLE `pay_xy_bindbank` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `aid` int(11) NOT NULL COMMENT '应用id',
  `channel_id` int(10) NOT NULL COMMENT '支付通道id',
  `identityid` varchar(20) NOT NULL COMMENT '用户唯一',
  `cli_identityid` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL COMMENT '新颜唯一用户标识',
  `requestid` varchar(50) NOT NULL COMMENT '商户生成的唯一绑卡请求号，最长',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `bankname` varchar(50) NOT NULL COMMENT '银行名称',
  `bankcode` varchar(20) NOT NULL COMMENT '银行编号',
  `idcardtype` char(10) NOT NULL DEFAULT '01' COMMENT '证件类型固定值01',
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `name` varchar(20) NOT NULL COMMENT '姓名',
  `phone` varchar(20) NOT NULL COMMENT '银行留存电话',
  `userip` varchar(20) NOT NULL COMMENT '（可选）用户请求ip地址',
  `create_time` datetime NOT NULL COMMENT '(内部)创建时间',
  `modify_time` datetime NOT NULL COMMENT '(内部)最后修改时间',
  `code` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '0 ：亲，认证成功（收费）1 ：亲，认证信息不一致（收费）3 ：亲，认证失败（不收费）9 ：亲，其他异常（不收费）',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '认证结果描述',
  `trade_no` varchar(255) NOT NULL DEFAULT '' COMMENT '新颜交易响应流水号',
  `org_code` varchar(255) NOT NULL DEFAULT '' COMMENT '机构响应码',
  `org_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '机构响应描述',
  `bank_id` varchar(20) NOT NULL COMMENT '银行编号',
  `bank_description` varchar(50) NOT NULL COMMENT '银行名称',
  `fee` varchar(5) NOT NULL DEFAULT '' COMMENT 'Y ：收费 ;N ：不收费',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_requestid` (`requestid`),
  KEY `idx_aid` (`aid`),
  KEY `idx_cardno` (`cardno`),
  KEY `idx_identityid` (`identityid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='新颜绑卡表';


-- ----------------------------
-- Table structure for rb_client_notify
-- ----------------------------
DROP TABLE IF EXISTS `rb_client_notify`;
CREATE TABLE `rb_client_notify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remit_id` int(11) NOT NULL COMMENT '出款id',
  `tip` varchar(255) NOT NULL COMMENT '通知内容',
  `remit_status` int(1) NOT NULL DEFAULT '0' COMMENT '出款状态:3:处理中(暂不存在);6:成功:11:失败;',
  `notify_num` int(1) NOT NULL DEFAULT '0' COMMENT '通知次数: 上限7次',
  `notify_status` int(1) NOT NULL DEFAULT '0' COMMENT '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败',
  `notify_time` datetime NOT NULL COMMENT '下次通知时间',
  `reason` varchar(20) NOT NULL COMMENT '通知失败原因:例如没有回调地址',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_remit_id` (`remit_id`),
  KEY `idx_notify_time` (`notify_time`),
  KEY `idx_remit_status` (`remit_status`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC COMMENT='发通知';


-- ----------------------------
-- Table structure for rb_remit
-- ----------------------------
DROP TABLE IF EXISTS `rb_remit`;
CREATE TABLE `rb_remit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '结算记录ID',
  `aid` int(11) NOT NULL COMMENT '应用id',
  `channel_id` int(10) NOT NULL COMMENT '通道id',
  `req_id` varchar(40) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '请求ID(业务)',
  `batch_no` int(10) DEFAULT NULL COMMENT '发送给融宝时候的批次id',
  `batch_id` int(10) NOT NULL DEFAULT '1' COMMENT '批次明细id',
  `client_id` varchar(30) NOT NULL COMMENT '[系统生成]流水号(内部对融宝)',
  `settle_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '[必填]结算金额',
  `settle_fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '结算手续费',
  `real_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际划款金额（除去手续费）',
  `remit_type` int(1) NOT NULL DEFAULT '1' COMMENT '[必填]打款业务类型：1表示借款；2担保提现；3收益提现',
  `remit_status` int(10) NOT NULL DEFAULT '0' COMMENT '打款状态:0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)',
  `rsp_status` varchar(50) NOT NULL COMMENT '融宝:响应状态:空为新加, HTTP_NOT_200表示无响应(即不是200)',
  `rsp_status_text` varchar(255) NOT NULL COMMENT '融宝:响应结果',
  `identityid` varchar(20) NOT NULL COMMENT '[选填]用户身份证',
  `user_mobile` varchar(60) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[选填]用户手机',
  `guest_account_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[必填]帐号名称(持卡人姓名)',
  `account_type` int(1) NOT NULL DEFAULT '0' COMMENT '账户类型:0对私；1对公 目前全是私',
  `guest_account_bank` varchar(60) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[收款]开户行名称',
  `guest_account` varchar(30) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[必填]银行账号',
  `guest_account_province` varchar(150) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[收款]银行所属省',
  `guest_account_city` varchar(150) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[收款]银行所属市',
  `guest_account_bank_branch` varchar(150) CHARACTER SET utf8 COLLATE utf8_latvian_ci NOT NULL COMMENT '[收款]银行所属支行',
  `settlement_desc` varchar(255) DEFAULT NULL COMMENT '[选填]结算描述信息',
  `callbackurl` varchar(255) NOT NULL COMMENT '异步通知回调url',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime NOT NULL COMMENT '更新时间',
  `remit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '出款时间',
  `sub_remit_time` datetime DEFAULT NULL COMMENT '提交第三方出款时间',
  `query_time` datetime NOT NULL COMMENT '下次查询时间',
  `query_num` int(1) NOT NULL DEFAULT '0' COMMENT '查询次数',
  `version` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '乐观锁',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_req_id` (`req_id`),
  UNIQUE KEY `idx_client_id` (`client_id`),
  KEY `idx_aid` (`aid`),
  KEY `idx_remit_status` (`remit_status`),
  KEY `idx_rsp_status` (`rsp_status`),
  KEY `idx_query_time` (`query_time`),
  KEY `idx_remit_time` (`remit_time`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='融宝出款记录表';


-- ----------------------------
-- Table structure for rt_setting
-- ----------------------------
DROP TABLE IF EXISTS `rt_setting`;
CREATE TABLE `rt_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` bigint(11) NOT NULL COMMENT '商户编号',
  `day_max_mount` decimal(12,4) NOT NULL COMMENT '商户最大日限额',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_aid` (`aid`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rt_setting
-- ----------------------------
INSERT INTO `rt_setting` VALUES ('1', '18', '3000.0000', '2016-05-12 00:00:00');

-- ----------------------------
-- Table structure for sjt_request
-- ----------------------------
DROP TABLE IF EXISTS `sjt_request`;
CREATE TABLE `sjt_request` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL COMMENT '应用id',
  `requestid` int(11) unsigned NOT NULL COMMENT 'jxl请求id',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `idcard` varchar(20) NOT NULL DEFAULT '' COMMENT '身份证号',
  `phone` varchar(20) NOT NULL COMMENT '手机号',
  `task_id` varchar(50) NOT NULL COMMENT '任务id',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `website` varchar(50) NOT NULL COMMENT '网站英文名称',
  `code` varchar(30) NOT NULL COMMENT '返回码',
  `message` varchar(100) NOT NULL COMMENT '响应信息',
  `task_stage` varchar(30) NOT NULL DEFAULT '' COMMENT '阶段名称',
  `is_smscode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0否1是手机验证码',
  `is_authcode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0否1是图片验证码',
  `auth_code` text COMMENT '图片验证码',
  `auth_code_path` varchar(100) DEFAULT NULL COMMENT '验证码路径',
  `source` int(1) unsigned NOT NULL DEFAULT '1' COMMENT '来源:1:XIANHUAHUA; 2:kuaip',
  `from` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '来源 1: H5  2: app',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime NOT NULL COMMENT '更新时间',
  `query_time` datetime NOT NULL COMMENT '查询时间',
  `query_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查询次数',
  `callbackurl` varchar(100) NOT NULL COMMENT '回调地址',
  `client_status` int(10) NOT NULL DEFAULT '0' COMMENT '客户端响应状态',
  `result_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0初始1采集中2成功3失败',
  `version` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '乐观锁',
  PRIMARY KEY (`id`),
  KEY `idx_requestid` (`requestid`),
  KEY `idx_phone` (`phone`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_query_time` (`query_time`),
  KEY `idx_result_status` (`result_status`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='数聚通请求纪录表';


-- ----------------------------
-- Table structure for soup_ocr_idcard
-- ----------------------------
DROP TABLE IF EXISTS `soup_ocr_idcard`;
CREATE TABLE `soup_ocr_idcard` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '结算记录ID',
  `image_id` varchar(64) DEFAULT '' COMMENT '图片id',
  `request_id` varchar(64) DEFAULT '' COMMENT '本次请求的id',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '响应状态',
  `message` varchar(64) DEFAULT '' COMMENT '响应信息',
  `side` varchar(10) DEFAULT '' COMMENT 'front 代表身份证正面，back 代表身份证反面',
  `name` varchar(32) DEFAULT NULL COMMENT '姓名',
  `number` varchar(20) DEFAULT NULL COMMENT '身份证号',
  `info` text COMMENT '身份证文字信息',
  `validity` text COMMENT '各项信息有效性',
  `type` varchar(10) DEFAULT '' COMMENT '身份证来源类型：normal 正常拍摄，photocopy 复印件， ps PS， reversion 屏幕翻拍， other 其他， unknown 未知（识别失败',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `modify_time` datetime NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_image_id` (`image_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_code` (`code`),
  KEY `idx_name` (`name`),
  KEY `idx_number` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='身份证图片上的文字信息';


-- ----------------------------
-- Table structure for soup_pic
-- ----------------------------
DROP TABLE IF EXISTS `soup_pic`;
CREATE TABLE `soup_pic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '结算记录ID',
  `pic_file_path` varchar(128) DEFAULT NULL COMMENT '图片地址',
  `pic_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1正面  2背面',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '响应状态',
  `message` varchar(64) DEFAULT '' COMMENT '响应信息',
  `pic_id` varchar(64) DEFAULT '' COMMENT '图片id',
  `request_id` varchar(64) DEFAULT '' COMMENT '本次请求的id',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `modify_time` datetime NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_pic_file_path` (`pic_file_path`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='图片上传到商汤';


-- ----------------------------
-- Table structure for soup_stateless
-- ----------------------------
DROP TABLE IF EXISTS `soup_stateless`;
CREATE TABLE `soup_stateless` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '结算记录ID',
  `video_file` varchar(200) DEFAULT NULL COMMENT '图片地址',
  `request_id` varchar(64) DEFAULT '' COMMENT '本次请求的id',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '响应状态',
  `passed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否通过活体检测1:通过  0没有通过',
  `liveness_score` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '静默活体检测得分（供参考）',
  `image_timestamp` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '视频选帧时间戳',
  `base64_image` varchar(255) DEFAULT '' COMMENT '图片地址',
  `message` varchar(64) DEFAULT '' COMMENT '响应信息',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `modify_time` datetime NOT NULL COMMENT '最后修改时间',
  `yirequestid` varchar(200) NOT NULL DEFAULT '' COMMENT '一亿元id',
  PRIMARY KEY (`id`),
  KEY `idx_video_file` (`video_file`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='静默活体检测';


-- ----------------------------
-- Table structure for soup_video
-- ----------------------------
DROP TABLE IF EXISTS `soup_video`;
CREATE TABLE `soup_video` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '应用编号',
  `video_file` varchar(200) DEFAULT NULL COMMENT '图片地址',
  `callbackurl` varchar(200) NOT NULL COMMENT '回调地址',
  `notify_status` int(1) NOT NULL DEFAULT '0' COMMENT '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败; 13:通知超限',
  `create_time` datetime NOT NULL COMMENT '添加时间',
  `modify_time` datetime NOT NULL COMMENT '最后修改时间',
  `requestid` varchar(200) NOT NULL DEFAULT '' COMMENT '一亿元id',
  PRIMARY KEY (`id`),
  KEY `idx_video_file` (`video_file`),
  KEY `idx_aid` (`aid`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='活体视频地址';


-- ----------------------------
-- Table structure for txsk_bind_bank
-- ----------------------------
DROP TABLE IF EXISTS `txsk_bind_bank`;
CREATE TABLE `txsk_bind_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `channel_id` int(11) NOT NULL COMMENT '通道ID',
  `cardno` varchar(50) NOT NULL COMMENT '银行卡号',
  `idcard` varchar(20) NOT NULL COMMENT '身份证号',
  `username` varchar(20) NOT NULL COMMENT '姓名',
  `phone` varchar(20) NOT NULL COMMENT '银行留存电话',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '状态:0:初始; 1:成功; 2:失败; 3解绑',
  `error_code` int(10) NOT NULL DEFAULT '0' COMMENT '错误码',
  `error_msg` varchar(255) NOT NULL COMMENT '错误原因',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_cardno` (`cardno`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='天行绑卡记录表';


-- ----------------------------
-- Table structure for xhh_anti_fruad
-- ----------------------------
DROP TABLE IF EXISTS `xhh_anti_fruad`;
CREATE TABLE `xhh_anti_fruad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL COMMENT '应用id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(50) DEFAULT NULL COMMENT '姓名',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `idcardno` varchar(30) DEFAULT NULL COMMENT '身份证号',
  `status` int(3) NOT NULL DEFAULT '0' COMMENT '请求状态 0：默认，1抓取中，2成功，11失败',
  `error_msg` varchar(255) NOT NULL,
  `id_found` int(2) NOT NULL COMMENT '该条记录中的身份证能否查到;1：能查到;-1：查不到',
  `found` int(2) NOT NULL COMMENT '该条记录能否查到1：能查到;-1：查不到',
  `risk_score` int(3) NOT NULL DEFAULT '0' COMMENT '分数',
  `risk_info` text COMMENT '结果集',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_mobile` (`mobile`) USING BTREE,
  KEY `idx_idcardno` (`idcardno`) USING BTREE,
  KEY `idx_name` (`name`) USING BTREE,
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='腾讯云反欺诈信用';


-- ----------------------------
-- Table structure for xhh_face
-- ----------------------------
DROP TABLE IF EXISTS `xhh_face`;
CREATE TABLE `xhh_face` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `identity` varchar(20) NOT NULL COMMENT '身份证号',
  `img_url1` varchar(256) DEFAULT NULL COMMENT '图片1',
  `img_url2` varchar(256) DEFAULT NULL COMMENT '图片2',
  `score` varchar(20) DEFAULT NULL COMMENT '分数',
  `result` varchar(256) NOT NULL COMMENT '返回结果',
  `create_time` datetime NOT NULL COMMENT '请求时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='人脸识别';


-- ----------------------------
-- Table structure for xhh_fulin
-- ----------------------------
DROP TABLE IF EXISTS `xhh_fulin`;
CREATE TABLE `xhh_fulin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `idcardno` varchar(30) DEFAULT NULL COMMENT '身份证号',
  `status` int(3) NOT NULL DEFAULT '0' COMMENT '请求状态 0：默认，1抓取中，2成功，11失败',
  `score` int(3) NOT NULL DEFAULT '0' COMMENT '分数',
  `msg` text COMMENT '返回信息',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_mobile` (`mobile`) USING BTREE,
  KEY `idx_idcardno` (`idcardno`) USING BTREE,
  KEY `idx_score` (`score`) USING BTREE,
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='孚临查询个人信用分表';


-- ----------------------------
-- Table structure for xhh_mf_risk
-- ----------------------------
DROP TABLE IF EXISTS `xhh_mf_risk`;
CREATE TABLE `xhh_mf_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `event_type` varchar(30) NOT NULL DEFAULT '' COMMENT '事件类型',
  `request_id` varchar(50) NOT NULL DEFAULT '' COMMENT '请求ID',
  `token_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'JS方式对接，用于关联设备指纹',
  `black_box` varchar(128) NOT NULL DEFAULT '' COMMENT 'sdk方式对接，用于关联设备指纹',
  `resp_detail_type` varchar(50) NOT NULL DEFAULT '' COMMENT '可支持API实时返回设备或解析信息',
  `event_occur_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '事件时间',
  `account_login` varchar(20) NOT NULL DEFAULT '' COMMENT '注册账户(如昵称等默认账户名)',
  `account_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '注册手机',
  `account_email` varchar(64) NOT NULL DEFAULT '' COMMENT '注册邮箱',
  `id_number` varchar(32) NOT NULL DEFAULT '' COMMENT '注册身份证',
  `account_password` varchar(64) NOT NULL DEFAULT '' COMMENT '注册密码摘要：建议先哈希加密后再提供（保证相同密码Hash值一致即可）',
  `rem_code` varchar(10) NOT NULL DEFAULT '' COMMENT '注册邀请码',
  `ip_address` varchar(32) NOT NULL DEFAULT '' COMMENT '注册IP地址',
  `state` varchar(10) NOT NULL DEFAULT '' COMMENT '状态校验结果（密码校验结果：0表示账户及密码一致性校验成功，1表示账户及密码一致性校验失败）',
  `refer_cust` varchar(128) NOT NULL DEFAULT '' COMMENT '网页端请求来源，即用户HTTP请求的refer值（JS方式对接）',
  `success` tinyint(1) NOT NULL DEFAULT '0' COMMENT '提交是否成功',
  `reason_code` varchar(255) NOT NULL DEFAULT '' COMMENT '错误代码',
  `seq_id` varchar(255) NOT NULL DEFAULT '' COMMENT '本次调用的请求id，用于事后反查事件',
  `spend_time` int(11) NOT NULL DEFAULT '0' COMMENT '本次调用在服务端的执行时间',
  `final_decision` varchar(30) NOT NULL DEFAULT '' COMMENT '风险评估结果（Accept无风险，通过；Review低风险，审查；Reject高风险，拒绝）',
  `final_score` int(11) NOT NULL DEFAULT '0' COMMENT '风险系数',
  `policy_set_name` varchar(64) NOT NULL DEFAULT '' COMMENT '策略集名称',
  `url` varchar(255) DEFAULT NULL COMMENT '日志URL',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '版本号',
  `create_time` datetime NOT NULL COMMENT '保存时间',
  PRIMARY KEY (`id`),
  KEY `xhh_request_id` (`request_id`),
  KEY `xhh_token_id` (`token_id`),
  KEY `xhh_account_login` (`account_login`),
  KEY `xhh_seq_id` (`seq_id`),
  KEY `xhh_final_decision` (`final_decision`),
  KEY `xhh_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='花生米富同盾';

-- ----------------------------
-- Records of xhh_mf_risk
-- ----------------------------

-- ----------------------------
-- Table structure for xhh_risk
-- ----------------------------
DROP TABLE IF EXISTS `xhh_risk`;
CREATE TABLE `xhh_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `event_type` varchar(30) NOT NULL DEFAULT '' COMMENT '事件类型',
  `request_id` varchar(50) NOT NULL DEFAULT '' COMMENT '请求ID',
  `account_name` varchar(20) NOT NULL DEFAULT '' COMMENT '姓名',
  `account_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码',
  `id_number` varchar(50) NOT NULL DEFAULT '' COMMENT '身份证号',
  `ext_birth_year` varchar(20) DEFAULT '' COMMENT '出生年',
  `ext_school` varchar(20) DEFAULT '' COMMENT '学校',
  `ext_diploma` varchar(20) DEFAULT '' COMMENT '学历',
  `ext_start_year` varchar(20) DEFAULT '' COMMENT '入学时间',
  `ext_industry` varchar(20) DEFAULT '' COMMENT '行业',
  `ext_position` varchar(20) DEFAULT '' COMMENT '职位',
  `organization` varchar(50) DEFAULT '' COMMENT '公司',
  `card_number` varchar(40) DEFAULT '' COMMENT '银行卡号',
  `pay_amount` decimal(10,2) DEFAULT '0.00' COMMENT '申请提现金额',
  `event_occur_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT '申请提现时间',
  `final_decision` varchar(30) NOT NULL DEFAULT '' COMMENT '风险评估结果（Accept无风险，通过；Review低风险，审查；Reject高风险，拒绝）',
  `final_score` int(11) NOT NULL DEFAULT '0' COMMENT '风险系数',
  `url` varchar(100) DEFAULT NULL COMMENT '日志URL',
  `seq_id` varchar(255) NOT NULL DEFAULT '' COMMENT '本次调用的请求id，用于事后反查事件',
  `version` int(11) NOT NULL DEFAULT '0' COMMENT '版本号',
  `create_time` datetime NOT NULL COMMENT '保存时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='风险服务表';


-- ----------------------------
-- Table structure for xhh_shenyue
-- ----------------------------
DROP TABLE IF EXISTS `xhh_shenyue`;
CREATE TABLE `xhh_shenyue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID（用户标识）',
  `loan_id` varchar(100) DEFAULT '',
  `aid` varchar(10) DEFAULT '',
  `name` varchar(50) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL COMMENT '手机号',
  `data_url` varchar(200) DEFAULT NULL COMMENT '采集的结果',
  `idcard` varchar(30) DEFAULT NULL COMMENT '身份证号',
  `source` varchar(20) DEFAULT NULL COMMENT '来源1流量接口2神月接口',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '请求状态 0：默认,2成功,11失败',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `modify_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_mobile` (`mobile`) USING BTREE,
  KEY `idx_idcard` (`idcard`) USING BTREE,
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='神月和流量监控接口';

