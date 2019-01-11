<?php
namespace app\common;

/**
 * 如果对方传来值对应为空，默认所属省市主区（第一个区）
 * Class Areas
 * @package app\common
 */
class Areas {

    public static function getarea() {
        $area = array(
            110101 =>
            array(
                'code' => 110101,
                'area_name' => '东城区',
                'name' => '东城区',
            ),
            110102 =>
            array(
                'code' => 110102,
                'area_name' => '西城区',
                'name' => '西城区',
            ),
            110105 =>
            array(
                'code' => 110105,
                'area_name' => '朝阳区',
                'name' => '朝阳区',
            ),
            110106 =>
            array(
                'code' => 110106,
                'area_name' => '丰台区',
                'name' => '丰台区',
            ),
            110107 =>
            array(
                'code' => 110107,
                'area_name' => '石景山区',
                'name' => '石景山区',
            ),
            110108 =>
            array(
                'code' => 110108,
                'area_name' => '海淀区',
                'name' => '海淀区',
            ),
            110109 =>
            array(
                'code' => 110109,
                'area_name' => '门头沟区',
                'name' => '门头沟区',
            ),
            110111 =>
            array(
                'code' => 110110,
                'area_name' => '房山区',
                'name' => '房山区',
            ),
            110112 =>
            array(
                'code' => 110111,
                'area_name' => '通州区',
                'name' => '通州区',
            ),
            110113 =>
            array(
                'code' => 110112,
                'area_name' => '顺义区',
                'name' => '顺义区',
            ),
            110114 =>
            array(
                'code' => 110113,
                'area_name' => '昌平区',
                'name' => '昌平区',
            ),
            110115 =>
            array(
                'code' => 110114,
                'area_name' => '大兴区',
                'name' => '大兴区',
            ),
            110116 =>
            array(
                'code' => 110115,
                'area_name' => '怀柔区',
                'name' => '怀柔区',
            ),
            110117 =>
            array(
                'code' => 110116,
                'area_name' => '平谷区',
                'name' => '平谷区',
            ),
            110228 =>
            array(
                'code' => 110117,
                'area_name' => '密云县',
                'name' => '密云县',
            ),
            110229 =>
            array(
                'code' => 110118,
                'area_name' => '延庆县',
                'name' => '延庆县',
            ),
            120101 =>
            array(
                'code' => 120101,
                'area_name' => '和平区',
                'name' => '和平区',
            ),
            120102 =>
            array(
                'code' => 120102,
                'area_name' => '河东区',
                'name' => '河东区',
            ),
            120103 =>
            array(
                'code' => 120103,
                'area_name' => '河西区',
                'name' => '河西区',
            ),
            120104 =>
            array(
                'code' => 120104,
                'area_name' => '南开区',
                'name' => '南开区',
            ),
            120105 =>
            array(
                'code' => 120105,
                'area_name' => '河北区',
                'name' => '河北区',
            ),
            120106 =>
            array(
                'code' => 120106,
                'area_name' => '红桥区',
                'name' => '红桥区',
            ),
            120110 =>
            array(
                'code' => 120110,
                'area_name' => '东丽区',
                'name' => '东丽区',
            ),
            120111 =>
            array(
                'code' => 120111,
                'area_name' => '西青区',
                'name' => '西青区',
            ),
            120112 =>
            array(
                'code' => 120112,
                'area_name' => '津南区',
                'name' => '津南区',
            ),
            120113 =>
            array(
                'code' => 120113,
                'area_name' => '北辰区',
                'name' => '北辰区',
            ),
            120114 =>
            array(
                'code' => 120114,
                'area_name' => '武清区',
                'name' => '武清区',
            ),
            120115 =>
            array(
                'code' => 120115,
                'area_name' => '宝坻区',
                'name' => '宝坻区',
            ),
            120116 =>
            array(
                'code' => '',
                'area_name' => '滨海新区',
                'name' => '',
            ),
            120221 =>
            array(
                'code' => 120116,
                'area_name' => '宁河县',
                'name' => '宁河县',
            ),
            120223 =>
            array(
                'code' => 120117,
                'area_name' => '静海县',
                'name' => '静海县',
            ),
            120225 =>
            array(
                'code' => '',
                'area_name' => '蓟县',
                'name' => '',
            ),
            130102 =>
            array(
                'code' => 230101,
                'area_name' => '长安区',
                'name' => '长安区',
            ),
            130103 =>
            array(
                'code' => 230102,
                'area_name' => '桥东区',
                'name' => '桥东区',
            ),
            130104 =>
            array(
                'code' => 230103,
                'area_name' => '桥西区',
                'name' => '桥西区',
            ),
            130105 =>
            array(
                'code' => 230104,
                'area_name' => '新华区',
                'name' => '新华区',
            ),
            130107 =>
            array(
                'code' => 230106,
                'area_name' => '井陉矿区',
                'name' => '井陉矿区',
            ),
            130108 =>
            array(
                'code' => '',
                'area_name' => '裕华区',
                'name' => '',
            ),
            130121 =>
            array(
                'code' => 230113,
                'area_name' => '井陉县',
                'name' => '井陉县',
            ),
            130123 =>
            array(
                'code' => 230115,
                'area_name' => '正定县',
                'name' => '正定县',
            ),
            130124 =>
            array(
                'code' => 230114,
                'area_name' => '栾城县',
                'name' => '栾城县',
            ),
            130125 =>
            array(
                'code' => 230116,
                'area_name' => '行唐县',
                'name' => '行唐县',
            ),
            130126 =>
            array(
                'code' => 230117,
                'area_name' => '灵寿县',
                'name' => '灵寿县',
            ),
            130127 =>
            array(
                'code' => 230118,
                'area_name' => '高邑县',
                'name' => '高邑县',
            ),
            130128 =>
            array(
                'code' => 230121,
                'area_name' => '深泽县',
                'name' => '深泽县',
            ),
            130129 =>
            array(
                'code' => 230120,
                'area_name' => '赞皇县',
                'name' => '赞皇县',
            ),
            130130 =>
            array(
                'code' => 230122,
                'area_name' => '无极县',
                'name' => '无极县',
            ),
            130131 =>
            array(
                'code' => 230112,
                'area_name' => '平山县',
                'name' => '平山县',
            ),
            130132 =>
            array(
                'code' => 230123,
                'area_name' => '元氏县',
                'name' => '元氏县',
            ),
            130133 =>
            array(
                'code' => '',
                'area_name' => '赵县',
                'name' => '',
            ),
            130181 =>
            array(
                'code' => 230107,
                'area_name' => '辛集市',
                'name' => '辛集市',
            ),
            130182 =>
            array(
                'code' => 230108,
                'area_name' => '藁城市',
                'name' => '藁城市',
            ),
            130183 =>
            array(
                'code' => 230109,
                'area_name' => '晋州市',
                'name' => '晋州市',
            ),
            130184 =>
            array(
                'code' => 230110,
                'area_name' => '新乐市',
                'name' => '新乐市',
            ),
            130185 =>
            array(
                'code' => 230111,
                'area_name' => '鹿泉市',
                'name' => '鹿泉市',
            ),
            130202 =>
            array(
                'code' => 230202,
                'area_name' => '路南区',
                'name' => '路南区',
            ),
            130203 =>
            array(
                'code' => 230201,
                'area_name' => '路北区',
                'name' => '路北区',
            ),
            130204 =>
            array(
                'code' => 230203,
                'area_name' => '古冶区',
                'name' => '古冶区',
            ),
            130205 =>
            array(
                'code' => 230204,
                'area_name' => '开平区',
                'name' => '开平区',
            ),
            130207 =>
            array(
                'code' => 230205,
                'area_name' => '丰南区',
                'name' => '丰南区',
            ),
            130208 =>
            array(
                'code' => 230206,
                'area_name' => '丰润区',
                'name' => '丰润区',
            ),
            130223 =>
            array(
                'code' => '',
                'area_name' => '滦县',
                'name' => '',
            ),
            130224 =>
            array(
                'code' => 230210,
                'area_name' => '滦南县',
                'name' => '滦南县',
            ),
            130225 =>
            array(
                'code' => 230213,
                'area_name' => '乐亭县',
                'name' => '乐亭县',
            ),
            130227 =>
            array(
                'code' => 230209,
                'area_name' => '迁西县',
                'name' => '迁西县',
            ),
            130229 =>
            array(
                'code' => 230211,
                'area_name' => '玉田县',
                'name' => '玉田县',
            ),
            130230 =>
            array(
                'code' => '',
                'area_name' => '曹妃甸区',
                'name' => '',
            ),
            130281 =>
            array(
                'code' => 230207,
                'area_name' => '遵化市',
                'name' => '遵化市',
            ),
            130283 =>
            array(
                'code' => 230208,
                'area_name' => '迁安市',
                'name' => '迁安市',
            ),
            130302 =>
            array(
                'code' => 230301,
                'area_name' => '海港区',
                'name' => '海港区',
            ),
            130303 =>
            array(
                'code' => 230302,
                'area_name' => '山海关区',
                'name' => '山海关区',
            ),
            130304 =>
            array(
                'code' => 230303,
                'area_name' => '北戴河区',
                'name' => '北戴河区',
            ),
            130321 =>
            array(
                'code' => 230307,
                'area_name' => '青龙满族自治县',
                'name' => '青龙满族自治县',
            ),
            130322 =>
            array(
                'code' => 230304,
                'area_name' => '昌黎县',
                'name' => '昌黎县',
            ),
            130323 =>
            array(
                'code' => 230306,
                'area_name' => '抚宁县',
                'name' => '抚宁县',
            ),
            130324 =>
            array(
                'code' => 230305,
                'area_name' => '卢龙县',
                'name' => '卢龙县',
            ),
            130402 =>
            array(
                'code' => 230403,
                'area_name' => '邯山区',
                'name' => '邯山区',
            ),
            130403 =>
            array(
                'code' => '',
                'area_name' => '丛台区',
                'name' => '',
            ),
            130404 =>
            array(
                'code' => 230402,
                'area_name' => '复兴区',
                'name' => '复兴区',
            ),
            130406 =>
            array(
                'code' => 230404,
                'area_name' => '峰峰矿区',
                'name' => '峰峰矿区',
            ),
            130421 =>
            array(
                'code' => 230406,
                'area_name' => '邯郸县',
                'name' => '邯郸县',
            ),
            130423 =>
            array(
                'code' => 230418,
                'area_name' => '临漳县',
                'name' => '临漳县',
            ),
            130424 =>
            array(
                'code' => 230411,
                'area_name' => '成安县',
                'name' => '成安县',
            ),
            130425 =>
            array(
                'code' => 230412,
                'area_name' => '大名县',
                'name' => '大名县',
            ),
            130426 =>
            array(
                'code' => '',
                'area_name' => '涉县',
                'name' => '',
            ),
            130427 =>
            array(
                'code' => '',
                'area_name' => '磁县',
                'name' => '',
            ),
            130428 =>
            array(
                'code' => 230417,
                'area_name' => '肥乡县',
                'name' => '肥乡县',
            ),
            130429 =>
            array(
                'code' => 230407,
                'area_name' => '永年县',
                'name' => '永年县',
            ),
            130430 =>
            array(
                'code' => '',
                'area_name' => '邱县',
                'name' => '',
            ),
            130431 =>
            array(
                'code' => 230414,
                'area_name' => '鸡泽县',
                'name' => '鸡泽县',
            ),
            130432 =>
            array(
                'code' => 230416,
                'area_name' => '广平县',
                'name' => '广平县',
            ),
            130433 =>
            array(
                'code' => 230409,
                'area_name' => '馆陶县',
                'name' => '馆陶县',
            ),
            130434 =>
            array(
                'code' => '',
                'area_name' => '魏县',
                'name' => '',
            ),
            130435 =>
            array(
                'code' => 230408,
                'area_name' => '曲周县',
                'name' => '曲周县',
            ),
            130481 =>
            array(
                'code' => 230405,
                'area_name' => '武安市',
                'name' => '武安市',
            ),
            130502 =>
            array(
                'code' => 230501,
                'area_name' => '桥东区',
                'name' => '桥东区',
            ),
            130503 =>
            array(
                'code' => 230502,
                'area_name' => '桥西区',
                'name' => '桥西区',
            ),
            130521 =>
            array(
                'code' => 230505,
                'area_name' => '邢台县',
                'name' => '邢台县',
            ),
            130522 =>
            array(
                'code' => 230512,
                'area_name' => '临城县',
                'name' => '临城县',
            ),
            130523 =>
            array(
                'code' => 230515,
                'area_name' => '内丘县',
                'name' => '内丘县',
            ),
            130524 =>
            array(
                'code' => 230506,
                'area_name' => '柏乡县',
                'name' => '柏乡县',
            ),
            130525 =>
            array(
                'code' => 230511,
                'area_name' => '隆尧县',
                'name' => '隆尧县',
            ),
            130526 =>
            array(
                'code' => '',
                'area_name' => '任县',
                'name' => '',
            ),
            130527 =>
            array(
                'code' => 230519,
                'area_name' => '南和县',
                'name' => '南和县',
            ),
            130528 =>
            array(
                'code' => 230509,
                'area_name' => '宁晋县',
                'name' => '宁晋县',
            ),
            130529 =>
            array(
                'code' => 230517,
                'area_name' => '巨鹿县',
                'name' => '巨鹿县',
            ),
            130530 =>
            array(
                'code' => 230518,
                'area_name' => '新河县',
                'name' => '新河县',
            ),
            130531 =>
            array(
                'code' => 230513,
                'area_name' => '广宗县',
                'name' => '广宗县',
            ),
            130532 =>
            array(
                'code' => 230516,
                'area_name' => '平乡县',
                'name' => '平乡县',
            ),
            130533 =>
            array(
                'code' => '',
                'area_name' => '威县',
                'name' => '',
            ),
            130534 =>
            array(
                'code' => 230508,
                'area_name' => '清河县',
                'name' => '清河县',
            ),
            130535 =>
            array(
                'code' => 230514,
                'area_name' => '临西县',
                'name' => '临西县',
            ),
            130581 =>
            array(
                'code' => 230503,
                'area_name' => '南宫市',
                'name' => '南宫市',
            ),
            130582 =>
            array(
                'code' => 230504,
                'area_name' => '沙河市',
                'name' => '沙河市',
            ),
            130602 =>
            array(
                'code' => 230601,
                'area_name' => '新市区',
                'name' => '新市区',
            ),
            130603 =>
            array(
                'code' => 230603,
                'area_name' => '北市区',
                'name' => '北市区',
            ),
            130604 =>
            array(
                'code' => 230602,
                'area_name' => '南市区',
                'name' => '南市区',
            ),
            130621 =>
            array(
                'code' => 230608,
                'area_name' => '满城县',
                'name' => '满城县',
            ),
            130622 =>
            array(
                'code' => 230609,
                'area_name' => '清苑县',
                'name' => '清苑县',
            ),
            130623 =>
            array(
                'code' => 230610,
                'area_name' => '涞水县',
                'name' => '涞水县',
            ),
            130624 =>
            array(
                'code' => 230611,
                'area_name' => '阜平县',
                'name' => '阜平县',
            ),
            130625 =>
            array(
                'code' => 230612,
                'area_name' => '徐水县',
                'name' => '徐水县',
            ),
            130626 =>
            array(
                'code' => 230613,
                'area_name' => '定兴县',
                'name' => '定兴县',
            ),
            130627 =>
            array(
                'code' => '',
                'area_name' => '唐县',
                'name' => '',
            ),
            130628 =>
            array(
                'code' => 230615,
                'area_name' => '高阳县',
                'name' => '高阳县',
            ),
            130629 =>
            array(
                'code' => 230616,
                'area_name' => '容城县',
                'name' => '容城县',
            ),
            130630 =>
            array(
                'code' => 230617,
                'area_name' => '涞源县',
                'name' => '涞源县',
            ),
            130631 =>
            array(
                'code' => 230618,
                'area_name' => '望都县',
                'name' => '望都县',
            ),
            130632 =>
            array(
                'code' => 230619,
                'area_name' => '安新县',
                'name' => '安新县',
            ),
            130633 =>
            array(
                'code' => '',
                'area_name' => '易县',
                'name' => '',
            ),
            130634 =>
            array(
                'code' => 230621,
                'area_name' => '曲阳县',
                'name' => '曲阳县',
            ),
            130635 =>
            array(
                'code' => '',
                'area_name' => '蠡县',
                'name' => '',
            ),
            130636 =>
            array(
                'code' => 230623,
                'area_name' => '顺平县',
                'name' => '顺平县',
            ),
            130637 =>
            array(
                'code' => 230624,
                'area_name' => '博野县',
                'name' => '博野县',
            ),
            130638 =>
            array(
                'code' => '',
                'area_name' => '雄县',
                'name' => '',
            ),
            130681 =>
            array(
                'code' => 230604,
                'area_name' => '涿州市',
                'name' => '涿州市',
            ),
            130682 =>
            array(
                'code' => 230605,
                'area_name' => '定州市',
                'name' => '定州市',
            ),
            130683 =>
            array(
                'code' => 230606,
                'area_name' => '安国市',
                'name' => '安国市',
            ),
            130684 =>
            array(
                'code' => 230607,
                'area_name' => '高碑店市',
                'name' => '高碑店市',
            ),
            130702 =>
            array(
                'code' => 230702,
                'area_name' => '桥东区',
                'name' => '桥东区',
            ),
            130703 =>
            array(
                'code' => 230701,
                'area_name' => '桥西区',
                'name' => '桥西区',
            ),
            130705 =>
            array(
                'code' => 230703,
                'area_name' => '宣化区',
                'name' => '宣化区',
            ),
            130706 =>
            array(
                'code' => 230704,
                'area_name' => '下花园区',
                'name' => '下花园区',
            ),
            130721 =>
            array(
                'code' => 230705,
                'area_name' => '宣化县',
                'name' => '宣化县',
            ),
            130722 =>
            array(
                'code' => 230707,
                'area_name' => '张北县',
                'name' => '张北县',
            ),
            130723 =>
            array(
                'code' => 230706,
                'area_name' => '康保县',
                'name' => '康保县',
            ),
            130724 =>
            array(
                'code' => 230710,
                'area_name' => '沽源县',
                'name' => '沽源县',
            ),
            130725 =>
            array(
                'code' => 230714,
                'area_name' => '尚义县',
                'name' => '尚义县',
            ),
            130726 =>
            array(
                'code' => '',
                'area_name' => '蔚县',
                'name' => '',
            ),
            130727 =>
            array(
                'code' => 230708,
                'area_name' => '阳原县',
                'name' => '阳原县',
            ),
            130728 =>
            array(
                'code' => 230711,
                'area_name' => '怀安县',
                'name' => '怀安县',
            ),
            130729 =>
            array(
                'code' => 230717,
                'area_name' => '万全县',
                'name' => '万全县',
            ),
            130730 =>
            array(
                'code' => 230712,
                'area_name' => '怀来县',
                'name' => '怀来县',
            ),
            130731 =>
            array(
                'code' => 230716,
                'area_name' => '涿鹿县',
                'name' => '涿鹿县',
            ),
            130732 =>
            array(
                'code' => 230709,
                'area_name' => '赤城县',
                'name' => '赤城县',
            ),
            130733 =>
            array(
                'code' => 230713,
                'area_name' => '崇礼县',
                'name' => '崇礼县',
            ),
            130802 =>
            array(
                'code' => 230801,
                'area_name' => '双桥区',
                'name' => '双桥区',
            ),
            130803 =>
            array(
                'code' => 230802,
                'area_name' => '双滦区',
                'name' => '双滦区',
            ),
            130804 =>
            array(
                'code' => 230803,
                'area_name' => '鹰手营子矿区',
                'name' => '鹰手营子矿区',
            ),
            130821 =>
            array(
                'code' => 230804,
                'area_name' => '承德县',
                'name' => '承德县',
            ),
            130822 =>
            array(
                'code' => 230805,
                'area_name' => '兴隆县',
                'name' => '兴隆县',
            ),
            130823 =>
            array(
                'code' => 230807,
                'area_name' => '平泉县',
                'name' => '平泉县',
            ),
            130824 =>
            array(
                'code' => 230808,
                'area_name' => '滦平县',
                'name' => '滦平县',
            ),
            130825 =>
            array(
                'code' => 230806,
                'area_name' => '隆化县',
                'name' => '隆化县',
            ),
            130826 =>
            array(
                'code' => 230809,
                'area_name' => '丰宁满族自治县',
                'name' => '丰宁满族自治县',
            ),
            130827 =>
            array(
                'code' => 230811,
                'area_name' => '宽城满族自治县',
                'name' => '宽城满族自治县',
            ),
            130828 =>
            array(
                'code' => 230810,
                'area_name' => '围场满族蒙古族自治县',
                'name' => '围场满族蒙古族自治县',
            ),
            130902 =>
            array(
                'code' => 230902,
                'area_name' => '新华区',
                'name' => '新华区',
            ),
            130903 =>
            array(
                'code' => 230901,
                'area_name' => '运河区',
                'name' => '运河区',
            ),
            130921 =>
            array(
                'code' => '',
                'area_name' => '沧县',
                'name' => '',
            ),
            130922 =>
            array(
                'code' => '',
                'area_name' => '青县',
                'name' => '',
            ),
            130923 =>
            array(
                'code' => 230910,
                'area_name' => '东光县',
                'name' => '东光县',
            ),
            130924 =>
            array(
                'code' => 230911,
                'area_name' => '海兴县',
                'name' => '海兴县',
            ),
            130925 =>
            array(
                'code' => 230912,
                'area_name' => '盐山县',
                'name' => '盐山县',
            ),
            130926 =>
            array(
                'code' => 230913,
                'area_name' => '肃宁县',
                'name' => '肃宁县',
            ),
            130927 =>
            array(
                'code' => 230914,
                'area_name' => '南皮县',
                'name' => '南皮县',
            ),
            130928 =>
            array(
                'code' => 230915,
                'area_name' => '吴桥县',
                'name' => '吴桥县',
            ),
            130929 =>
            array(
                'code' => '',
                'area_name' => '献县',
                'name' => '',
            ),
            130930 =>
            array(
                'code' => 230916,
                'area_name' => '孟村回族自治县',
                'name' => '孟村回族自治县',
            ),
            130981 =>
            array(
                'code' => 230903,
                'area_name' => '泊头市',
                'name' => '泊头市',
            ),
            130982 =>
            array(
                'code' => 230904,
                'area_name' => '任丘市',
                'name' => '任丘市',
            ),
            130983 =>
            array(
                'code' => 230905,
                'area_name' => '黄骅市',
                'name' => '黄骅市',
            ),
            130984 =>
            array(
                'code' => 230906,
                'area_name' => '河间市',
                'name' => '河间市',
            ),
            131002 =>
            array(
                'code' => 231001,
                'area_name' => '安次区',
                'name' => '安次区',
            ),
            131003 =>
            array(
                'code' => 231002,
                'area_name' => '广阳区',
                'name' => '广阳区',
            ),
            131022 =>
            array(
                'code' => 231005,
                'area_name' => '固安县',
                'name' => '固安县',
            ),
            131023 =>
            array(
                'code' => 231006,
                'area_name' => '永清县',
                'name' => '永清县',
            ),
            131024 =>
            array(
                'code' => 231007,
                'area_name' => '香河县',
                'name' => '香河县',
            ),
            131025 =>
            array(
                'code' => 231008,
                'area_name' => '大城县',
                'name' => '大城县',
            ),
            131026 =>
            array(
                'code' => 231009,
                'area_name' => '文安县',
                'name' => '文安县',
            ),
            131028 =>
            array(
                'code' => 231010,
                'area_name' => '大厂回族自治县',
                'name' => '大厂回族自治县',
            ),
            131081 =>
            array(
                'code' => 231003,
                'area_name' => '霸州市',
                'name' => '霸州市',
            ),
            131082 =>
            array(
                'code' => 231004,
                'area_name' => '三河市',
                'name' => '三河市',
            ),
            131102 =>
            array(
                'code' => 231101,
                'area_name' => '桃城区',
                'name' => '桃城区',
            ),
            131121 =>
            array(
                'code' => 231105,
                'area_name' => '枣强县',
                'name' => '枣强县',
            ),
            131122 =>
            array(
                'code' => 231109,
                'area_name' => '武邑县',
                'name' => '武邑县',
            ),
            131123 =>
            array(
                'code' => 231111,
                'area_name' => '武强县',
                'name' => '武强县',
            ),
            131124 =>
            array(
                'code' => 231104,
                'area_name' => '饶阳县',
                'name' => '饶阳县',
            ),
            131125 =>
            array(
                'code' => 231108,
                'area_name' => '安平县',
                'name' => '安平县',
            ),
            131126 =>
            array(
                'code' => 231106,
                'area_name' => '故城县',
                'name' => '故城县',
            ),
            131127 =>
            array(
                'code' => '',
                'area_name' => '景县',
                'name' => '',
            ),
            131128 =>
            array(
                'code' => 231107,
                'area_name' => '阜城县',
                'name' => '阜城县',
            ),
            131181 =>
            array(
                'code' => 231102,
                'area_name' => '冀州市',
                'name' => '冀州市',
            ),
            131182 =>
            array(
                'code' => 231103,
                'area_name' => '深州市',
                'name' => '深州市',
            ),
            140105 =>
            array(
                'code' => 240102,
                'area_name' => '小店区',
                'name' => '小店区',
            ),
            140106 =>
            array(
                'code' => 240103,
                'area_name' => '迎泽区',
                'name' => '迎泽区',
            ),
            140107 =>
            array(
                'code' => 240101,
                'area_name' => '杏花岭区',
                'name' => '杏花岭区',
            ),
            140108 =>
            array(
                'code' => 240104,
                'area_name' => '尖草坪区',
                'name' => '尖草坪区',
            ),
            140109 =>
            array(
                'code' => 240105,
                'area_name' => '万柏林区',
                'name' => '万柏林区',
            ),
            140110 =>
            array(
                'code' => 240106,
                'area_name' => '晋源区',
                'name' => '晋源区',
            ),
            140121 =>
            array(
                'code' => 240109,
                'area_name' => '清徐县',
                'name' => '清徐县',
            ),
            140122 =>
            array(
                'code' => 240108,
                'area_name' => '阳曲县',
                'name' => '阳曲县',
            ),
            140123 =>
            array(
                'code' => 240110,
                'area_name' => '娄烦县',
                'name' => '娄烦县',
            ),
            140181 =>
            array(
                'code' => 240107,
                'area_name' => '古交市',
                'name' => '古交市',
            ),
            140202 =>
            array(
                'code' => '',
                'area_name' => '城区',
                'name' => '',
            ),
            140203 =>
            array(
                'code' => '',
                'area_name' => '矿区',
                'name' => '',
            ),
            140211 =>
            array(
                'code' => 240203,
                'area_name' => '南郊区',
                'name' => '南郊区',
            ),
            140212 =>
            array(
                'code' => 240204,
                'area_name' => '新荣区',
                'name' => '新荣区',
            ),
            140221 =>
            array(
                'code' => 240208,
                'area_name' => '阳高县',
                'name' => '阳高县',
            ),
            140222 =>
            array(
                'code' => 240206,
                'area_name' => '天镇县',
                'name' => '天镇县',
            ),
            140223 =>
            array(
                'code' => 240210,
                'area_name' => '广灵县',
                'name' => '广灵县',
            ),
            140224 =>
            array(
                'code' => 240207,
                'area_name' => '灵丘县',
                'name' => '灵丘县',
            ),
            140225 =>
            array(
                'code' => 240211,
                'area_name' => '浑源县',
                'name' => '浑源县',
            ),
            140226 =>
            array(
                'code' => 240209,
                'area_name' => '左云县',
                'name' => '左云县',
            ),
            140227 =>
            array(
                'code' => 240205,
                'area_name' => '大同县',
                'name' => '大同县',
            ),
            140302 =>
            array(
                'code' => '',
                'area_name' => '城区',
                'name' => '',
            ),
            140303 =>
            array(
                'code' => '',
                'area_name' => '矿区',
                'name' => '',
            ),
            140311 =>
            array(
                'code' => '',
                'area_name' => '郊区',
                'name' => '',
            ),
            140321 =>
            array(
                'code' => 240304,
                'area_name' => '平定县',
                'name' => '平定县',
            ),
            140322 =>
            array(
                'code' => '',
                'area_name' => '盂县',
                'name' => '',
            ),
            140421 =>
            array(
                'code' => 240404,
                'area_name' => '长治县',
                'name' => '长治县',
            ),
            140423 =>
            array(
                'code' => 240407,
                'area_name' => '襄垣县',
                'name' => '襄垣县',
            ),
            140424 =>
            array(
                'code' => 240409,
                'area_name' => '屯留县',
                'name' => '屯留县',
            ),
            140425 =>
            array(
                'code' => 240406,
                'area_name' => '平顺县',
                'name' => '平顺县',
            ),
            140426 =>
            array(
                'code' => 240410,
                'area_name' => '黎城县',
                'name' => '黎城县',
            ),
            140427 =>
            array(
                'code' => 240413,
                'area_name' => '壶关县',
                'name' => '壶关县',
            ),
            140428 =>
            array(
                'code' => 240405,
                'area_name' => '长子县',
                'name' => '长子县',
            ),
            140429 =>
            array(
                'code' => 240411,
                'area_name' => '武乡县',
                'name' => '武乡县',
            ),
            140430 =>
            array(
                'code' => '',
                'area_name' => '沁县',
                'name' => '',
            ),
            140431 =>
            array(
                'code' => 240408,
                'area_name' => '沁源县',
                'name' => '沁源县',
            ),
            140481 =>
            array(
                'code' => 240403,
                'area_name' => '潞城市',
                'name' => '潞城市',
            ),
            140482 =>
            array(
                'code' => '',
                'area_name' => '城区',
                'name' => '',
            ),
            140483 =>
            array(
                'code' => '',
                'area_name' => '郊区',
                'name' => '',
            ),
            140502 =>
            array(
                'code' => '',
                'area_name' => '城区',
                'name' => '',
            ),
            140521 =>
            array(
                'code' => 240506,
                'area_name' => '沁水县',
                'name' => '沁水县',
            ),
            140522 =>
            array(
                'code' => 240505,
                'area_name' => '阳城县',
                'name' => '阳城县',
            ),
            140524 =>
            array(
                'code' => 240504,
                'area_name' => '陵川县',
                'name' => '陵川县',
            ),
            140525 =>
            array(
                'code' => 240503,
                'area_name' => '泽州县',
                'name' => '泽州县',
            ),
            140581 =>
            array(
                'code' => 240502,
                'area_name' => '高平市',
                'name' => '高平市',
            ),
            140602 =>
            array(
                'code' => 240601,
                'area_name' => '朔城区',
                'name' => '朔城区',
            ),
            140603 =>
            array(
                'code' => 240602,
                'area_name' => '平鲁区',
                'name' => '平鲁区',
            ),
            140621 =>
            array(
                'code' => 240603,
                'area_name' => '山阴县',
                'name' => '山阴县',
            ),
            140622 =>
            array(
                'code' => '',
                'area_name' => '应县',
                'name' => '',
            ),
            140623 =>
            array(
                'code' => 240604,
                'area_name' => '右玉县',
                'name' => '右玉县',
            ),
            140624 =>
            array(
                'code' => 240606,
                'area_name' => '怀仁县',
                'name' => '怀仁县',
            ),
            140702 =>
            array(
                'code' => 240701,
                'area_name' => '榆次区',
                'name' => '榆次区',
            ),
            140721 =>
            array(
                'code' => 240711,
                'area_name' => '榆社县',
                'name' => '榆社县',
            ),
            140722 =>
            array(
                'code' => 240706,
                'area_name' => '左权县',
                'name' => '左权县',
            ),
            140723 =>
            array(
                'code' => 240709,
                'area_name' => '和顺县',
                'name' => '和顺县',
            ),
            140724 =>
            array(
                'code' => 240703,
                'area_name' => '昔阳县',
                'name' => '昔阳县',
            ),
            140725 =>
            array(
                'code' => 240707,
                'area_name' => '寿阳县',
                'name' => '寿阳县',
            ),
            140726 =>
            array(
                'code' => 240708,
                'area_name' => '太谷县',
                'name' => '太谷县',
            ),
            140727 =>
            array(
                'code' => '',
                'area_name' => '祁县',
                'name' => '',
            ),
            140728 =>
            array(
                'code' => 240710,
                'area_name' => '平遥县',
                'name' => '平遥县',
            ),
            140729 =>
            array(
                'code' => 240704,
                'area_name' => '灵石县',
                'name' => '灵石县',
            ),
            140781 =>
            array(
                'code' => 240702,
                'area_name' => '介休市',
                'name' => '介休市',
            ),
            140802 =>
            array(
                'code' => 241001,
                'area_name' => '盐湖区',
                'name' => '盐湖区',
            ),
            140821 =>
            array(
                'code' => 241013,
                'area_name' => '临猗县',
                'name' => '临猗县',
            ),
            140822 =>
            array(
                'code' => 241012,
                'area_name' => '万荣县',
                'name' => '万荣县',
            ),
            140823 =>
            array(
                'code' => 241004,
                'area_name' => '闻喜县',
                'name' => '闻喜县',
            ),
            140824 =>
            array(
                'code' => 241009,
                'area_name' => '稷山县',
                'name' => '稷山县',
            ),
            140825 =>
            array(
                'code' => 241005,
                'area_name' => '新绛县',
                'name' => '新绛县',
            ),
            140826 =>
            array(
                'code' => '',
                'area_name' => '绛县',
                'name' => '',
            ),
            140827 =>
            array(
                'code' => 241007,
                'area_name' => '垣曲县',
                'name' => '垣曲县',
            ),
            140828 =>
            array(
                'code' => '',
                'area_name' => '夏县',
                'name' => '',
            ),
            140829 =>
            array(
                'code' => 241006,
                'area_name' => '平陆县',
                'name' => '平陆县',
            ),
            140830 =>
            array(
                'code' => 241010,
                'area_name' => '芮城县',
                'name' => '芮城县',
            ),
            140881 =>
            array(
                'code' => 241003,
                'area_name' => '永济市',
                'name' => '永济市',
            ),
            140882 =>
            array(
                'code' => 241002,
                'area_name' => '河津市',
                'name' => '河津市',
            ),
            140902 =>
            array(
                'code' => 240801,
                'area_name' => '忻府区',
                'name' => '忻府区',
            ),
            140921 =>
            array(
                'code' => 240813,
                'area_name' => '定襄县',
                'name' => '定襄县',
            ),
            140922 =>
            array(
                'code' => 240806,
                'area_name' => '五台县',
                'name' => '五台县',
            ),
            140923 =>
            array(
                'code' => '',
                'area_name' => '代县',
                'name' => '',
            ),
            140924 =>
            array(
                'code' => 240810,
                'area_name' => '繁峙县',
                'name' => '繁峙县',
            ),
            140925 =>
            array(
                'code' => 240808,
                'area_name' => '宁武县',
                'name' => '宁武县',
            ),
            140926 =>
            array(
                'code' => 240809,
                'area_name' => '静乐县',
                'name' => '静乐县',
            ),
            140927 =>
            array(
                'code' => 240804,
                'area_name' => '神池县',
                'name' => '神池县',
            ),
            140928 =>
            array(
                'code' => 240805,
                'area_name' => '五寨县',
                'name' => '五寨县',
            ),
            140929 =>
            array(
                'code' => 240814,
                'area_name' => '岢岚县',
                'name' => '岢岚县',
            ),
            140930 =>
            array(
                'code' => 240811,
                'area_name' => '河曲县',
                'name' => '河曲县',
            ),
            140931 =>
            array(
                'code' => 240812,
                'area_name' => '保德县',
                'name' => '保德县',
            ),
            140932 =>
            array(
                'code' => 240807,
                'area_name' => '偏关县',
                'name' => '偏关县',
            ),
            140981 =>
            array(
                'code' => 240802,
                'area_name' => '原平市',
                'name' => '原平市',
            ),
            141002 =>
            array(
                'code' => 240901,
                'area_name' => '尧都区',
                'name' => '尧都区',
            ),
            141021 =>
            array(
                'code' => 240915,
                'area_name' => '曲沃县',
                'name' => '曲沃县',
            ),
            141022 =>
            array(
                'code' => 240912,
                'area_name' => '翼城县',
                'name' => '翼城县',
            ),
            141023 =>
            array(
                'code' => 240911,
                'area_name' => '襄汾县',
                'name' => '襄汾县',
            ),
            141024 =>
            array(
                'code' => 240916,
                'area_name' => '洪洞县',
                'name' => '洪洞县',
            ),
            141025 =>
            array(
                'code' => '',
                'area_name' => '古县',
                'name' => '',
            ),
            141026 =>
            array(
                'code' => 240906,
                'area_name' => '安泽县',
                'name' => '安泽县',
            ),
            141027 =>
            array(
                'code' => 240908,
                'area_name' => '浮山县',
                'name' => '浮山县',
            ),
            141028 =>
            array(
                'code' => '',
                'area_name' => '吉县',
                'name' => '',
            ),
            141029 =>
            array(
                'code' => 240914,
                'area_name' => '乡宁县',
                'name' => '乡宁县',
            ),
            141030 =>
            array(
                'code' => 240907,
                'area_name' => '大宁县',
                'name' => '大宁县',
            ),
            141031 =>
            array(
                'code' => '',
                'area_name' => '隰县',
                'name' => '',
            ),
            141032 =>
            array(
                'code' => 240913,
                'area_name' => '永和县',
                'name' => '永和县',
            ),
            141033 =>
            array(
                'code' => '',
                'area_name' => '蒲县',
                'name' => '',
            ),
            141034 =>
            array(
                'code' => 240904,
                'area_name' => '汾西县',
                'name' => '汾西县',
            ),
            141081 =>
            array(
                'code' => 240902,
                'area_name' => '侯马市',
                'name' => '侯马市',
            ),
            141082 =>
            array(
                'code' => 240903,
                'area_name' => '霍州市',
                'name' => '霍州市',
            ),
            141102 =>
            array(
                'code' => '',
                'area_name' => '离石区',
                'name' => '',
            ),
            141121 =>
            array(
                'code' => 241104,
                'area_name' => '文水县',
                'name' => '文水县',
            ),
            141122 =>
            array(
                'code' => 241112,
                'area_name' => '交城县',
                'name' => '交城县',
            ),
            141123 =>
            array(
                'code' => '',
                'area_name' => '兴县',
                'name' => '',
            ),
            141124 =>
            array(
                'code' => '',
                'area_name' => '临县',
                'name' => '',
            ),
            141125 =>
            array(
                'code' => 241109,
                'area_name' => '柳林县',
                'name' => '柳林县',
            ),
            141126 =>
            array(
                'code' => 241113,
                'area_name' => '石楼县',
                'name' => '石楼县',
            ),
            141127 =>
            array(
                'code' => '',
                'area_name' => '岚县',
                'name' => '',
            ),
            141128 =>
            array(
                'code' => 241108,
                'area_name' => '方山县',
                'name' => '方山县',
            ),
            141129 =>
            array(
                'code' => 241105,
                'area_name' => '中阳县',
                'name' => '中阳县',
            ),
            141130 =>
            array(
                'code' => 241111,
                'area_name' => '交口县',
                'name' => '交口县',
            ),
            141181 =>
            array(
                'code' => 241102,
                'area_name' => '孝义市',
                'name' => '孝义市',
            ),
            141182 =>
            array(
                'code' => 241103,
                'area_name' => '汾阳市',
                'name' => '汾阳市',
            ),
            150102 =>
            array(
                'code' => 150102,
                'area_name' => '新城区',
                'name' => '新城区',
            ),
            150103 =>
            array(
                'code' => 150101,
                'area_name' => '回民区',
                'name' => '回民区',
            ),
            150104 =>
            array(
                'code' => 150103,
                'area_name' => '玉泉区',
                'name' => '玉泉区',
            ),
            150105 =>
            array(
                'code' => 150104,
                'area_name' => '赛罕区',
                'name' => '赛罕区',
            ),
            150121 =>
            array(
                'code' => 150105,
                'area_name' => '土默特左旗',
                'name' => '土默特左旗',
            ),
            150122 =>
            array(
                'code' => 150106,
                'area_name' => '托克托县',
                'name' => '托克托县',
            ),
            150123 =>
            array(
                'code' => 150107,
                'area_name' => '和林格尔县',
                'name' => '和林格尔县',
            ),
            150124 =>
            array(
                'code' => 150108,
                'area_name' => '清水河县',
                'name' => '清水河县',
            ),
            150125 =>
            array(
                'code' => 150109,
                'area_name' => '武川县',
                'name' => '武川县',
            ),
            150202 =>
            array(
                'code' => 150202,
                'area_name' => '东河区',
                'name' => '东河区',
            ),
            150203 =>
            array(
                'code' => 150201,
                'area_name' => '昆都仑区',
                'name' => '昆都仑区',
            ),
            150204 =>
            array(
                'code' => 150203,
                'area_name' => '青山区',
                'name' => '青山区',
            ),
            150205 =>
            array(
                'code' => 150204,
                'area_name' => '石拐区',
                'name' => '石拐区',
            ),
            150206 =>
            array(
                'code' => '',
                'area_name' => '白云鄂博矿区',
                'name' => '',
            ),
            150207 =>
            array(
                'code' => 150206,
                'area_name' => '九原区',
                'name' => '九原区',
            ),
            150221 =>
            array(
                'code' => 150207,
                'area_name' => '土默特右旗',
                'name' => '土默特右旗',
            ),
            150222 =>
            array(
                'code' => 150208,
                'area_name' => '固阳县',
                'name' => '固阳县',
            ),
            150223 =>
            array(
                'code' => 150209,
                'area_name' => '达尔罕茂明安联合旗',
                'name' => '达尔罕茂明安联合旗',
            ),
            150302 =>
            array(
                'code' => 150301,
                'area_name' => '海勃湾区',
                'name' => '海勃湾区',
            ),
            150303 =>
            array(
                'code' => 150302,
                'area_name' => '海南区',
                'name' => '海南区',
            ),
            150304 =>
            array(
                'code' => 150303,
                'area_name' => '乌达区',
                'name' => '乌达区',
            ),
            150402 =>
            array(
                'code' => 150401,
                'area_name' => '红山区',
                'name' => '红山区',
            ),
            150403 =>
            array(
                'code' => 150402,
                'area_name' => '元宝山区',
                'name' => '元宝山区',
            ),
            150404 =>
            array(
                'code' => 150403,
                'area_name' => '松山区',
                'name' => '松山区',
            ),
            150421 =>
            array(
                'code' => 150404,
                'area_name' => '阿鲁科尔沁旗',
                'name' => '阿鲁科尔沁旗',
            ),
            150422 =>
            array(
                'code' => 150405,
                'area_name' => '巴林左旗',
                'name' => '巴林左旗',
            ),
            150423 =>
            array(
                'code' => 150406,
                'area_name' => '巴林右旗',
                'name' => '巴林右旗',
            ),
            150424 =>
            array(
                'code' => 150412,
                'area_name' => '林西县',
                'name' => '林西县',
            ),
            150425 =>
            array(
                'code' => 150407,
                'area_name' => '克什克腾旗',
                'name' => '克什克腾旗',
            ),
            150426 =>
            array(
                'code' => 150408,
                'area_name' => '翁牛特旗',
                'name' => '翁牛特旗',
            ),
            150428 =>
            array(
                'code' => 150409,
                'area_name' => '喀喇沁旗',
                'name' => '喀喇沁旗',
            ),
            150429 =>
            array(
                'code' => 150411,
                'area_name' => '宁城县',
                'name' => '宁城县',
            ),
            150430 =>
            array(
                'code' => 150410,
                'area_name' => '敖汉旗',
                'name' => '敖汉旗',
            ),
            150502 =>
            array(
                'code' => 150501,
                'area_name' => '科尔沁区',
                'name' => '科尔沁区',
            ),
            150521 =>
            array(
                'code' => 150503,
                'area_name' => '科尔沁左翼中旗',
                'name' => '科尔沁左翼中旗',
            ),
            150522 =>
            array(
                'code' => 150504,
                'area_name' => '科尔沁左翼后旗',
                'name' => '科尔沁左翼后旗',
            ),
            150523 =>
            array(
                'code' => 150508,
                'area_name' => '开鲁县',
                'name' => '开鲁县',
            ),
            150524 =>
            array(
                'code' => 150505,
                'area_name' => '库伦旗',
                'name' => '库伦旗',
            ),
            150525 =>
            array(
                'code' => 150506,
                'area_name' => '奈曼旗',
                'name' => '奈曼旗',
            ),
            150526 =>
            array(
                'code' => 150507,
                'area_name' => '扎鲁特旗',
                'name' => '扎鲁特旗',
            ),
            150581 =>
            array(
                'code' => 150502,
                'area_name' => '霍林郭勒市',
                'name' => '霍林郭勒市',
            ),
            150602 =>
            array(
                'code' => 150601,
                'area_name' => '东胜区',
                'name' => '东胜区',
            ),
            150621 =>
            array(
                'code' => 150602,
                'area_name' => '达拉特旗',
                'name' => '达拉特旗',
            ),
            150622 =>
            array(
                'code' => 150603,
                'area_name' => '准格尔旗',
                'name' => '准格尔旗',
            ),
            150623 =>
            array(
                'code' => 150604,
                'area_name' => '鄂托克前旗',
                'name' => '鄂托克前旗',
            ),
            150624 =>
            array(
                'code' => 150605,
                'area_name' => '鄂托克旗',
                'name' => '鄂托克旗',
            ),
            150625 =>
            array(
                'code' => 150606,
                'area_name' => '杭锦旗',
                'name' => '杭锦旗',
            ),
            150626 =>
            array(
                'code' => 150607,
                'area_name' => '乌审旗',
                'name' => '乌审旗',
            ),
            150627 =>
            array(
                'code' => 150608,
                'area_name' => '伊金霍洛旗',
                'name' => '伊金霍洛旗',
            ),
            150702 =>
            array(
                'code' => 150701,
                'area_name' => '海拉尔区',
                'name' => '海拉尔区',
            ),
            150703 =>
            array(
                'code' => '',
                'area_name' => '扎赉诺尔区',
                'name' => '',
            ),
            150721 =>
            array(
                'code' => 150707,
                'area_name' => '阿荣旗',
                'name' => '阿荣旗',
            ),
            150722 =>
            array(
                'code' => '',
                'area_name' => '莫力达瓦达斡尔族自治旗',
                'name' => '',
            ),
            150723 =>
            array(
                'code' => 150708,
                'area_name' => '鄂伦春自治旗',
                'name' => '鄂伦春自治旗',
            ),
            150724 =>
            array(
                'code' => 150709,
                'area_name' => '鄂温克族自治旗',
                'name' => '鄂温克族自治旗',
            ),
            150725 =>
            array(
                'code' => 150710,
                'area_name' => '陈巴尔虎旗',
                'name' => '陈巴尔虎旗',
            ),
            150726 =>
            array(
                'code' => 150711,
                'area_name' => '新巴尔虎左旗',
                'name' => '新巴尔虎左旗',
            ),
            150727 =>
            array(
                'code' => 150712,
                'area_name' => '新巴尔虎右旗',
                'name' => '新巴尔虎右旗',
            ),
            150781 =>
            array(
                'code' => 150702,
                'area_name' => '满洲里市',
                'name' => '满洲里市',
            ),
            150782 =>
            array(
                'code' => 150703,
                'area_name' => '牙克石市',
                'name' => '牙克石市',
            ),
            150783 =>
            array(
                'code' => 150704,
                'area_name' => '扎兰屯市',
                'name' => '扎兰屯市',
            ),
            150784 =>
            array(
                'code' => 150705,
                'area_name' => '额尔古纳市',
                'name' => '额尔古纳市',
            ),
            150785 =>
            array(
                'code' => 150706,
                'area_name' => '根河市',
                'name' => '根河市',
            ),
            150802 =>
            array(
                'code' => 150801,
                'area_name' => '临河区',
                'name' => '临河区',
            ),
            150821 =>
            array(
                'code' => 150802,
                'area_name' => '五原县',
                'name' => '五原县',
            ),
            150822 =>
            array(
                'code' => 150803,
                'area_name' => '磴口县',
                'name' => '磴口县',
            ),
            150823 =>
            array(
                'code' => 150804,
                'area_name' => '乌拉特前旗',
                'name' => '乌拉特前旗',
            ),
            150824 =>
            array(
                'code' => 150805,
                'area_name' => '乌拉特中旗',
                'name' => '乌拉特中旗',
            ),
            150825 =>
            array(
                'code' => 150806,
                'area_name' => '乌拉特后旗',
                'name' => '乌拉特后旗',
            ),
            150826 =>
            array(
                'code' => 150807,
                'area_name' => '杭锦后旗',
                'name' => '杭锦后旗',
            ),
            150902 =>
            array(
                'code' => 150901,
                'area_name' => '集宁区',
                'name' => '集宁区',
            ),
            150921 =>
            array(
                'code' => 150903,
                'area_name' => '卓资县',
                'name' => '卓资县',
            ),
            150922 =>
            array(
                'code' => 150904,
                'area_name' => '化德县',
                'name' => '化德县',
            ),
            150923 =>
            array(
                'code' => 150905,
                'area_name' => '商都县',
                'name' => '商都县',
            ),
            150924 =>
            array(
                'code' => 150906,
                'area_name' => '兴和县',
                'name' => '兴和县',
            ),
            150925 =>
            array(
                'code' => 150907,
                'area_name' => '凉城县',
                'name' => '凉城县',
            ),
            150926 =>
            array(
                'code' => 150908,
                'area_name' => '察哈尔右翼前旗',
                'name' => '察哈尔右翼前旗',
            ),
            150927 =>
            array(
                'code' => 150909,
                'area_name' => '察哈尔右翼中旗',
                'name' => '察哈尔右翼中旗',
            ),
            150928 =>
            array(
                'code' => 150910,
                'area_name' => '察哈尔右翼后旗',
                'name' => '察哈尔右翼后旗',
            ),
            150929 =>
            array(
                'code' => 150911,
                'area_name' => '四子王旗',
                'name' => '四子王旗',
            ),
            150981 =>
            array(
                'code' => 150902,
                'area_name' => '丰镇市',
                'name' => '丰镇市',
            ),
            152201 =>
            array(
                'code' => 151001,
                'area_name' => '乌兰浩特市',
                'name' => '乌兰浩特市',
            ),
            152202 =>
            array(
                'code' => 151002,
                'area_name' => '阿尔山市',
                'name' => '阿尔山市',
            ),
            152221 =>
            array(
                'code' => 151003,
                'area_name' => '科尔沁右翼前旗',
                'name' => '科尔沁右翼前旗',
            ),
            152222 =>
            array(
                'code' => 151004,
                'area_name' => '科尔沁右翼中旗',
                'name' => '科尔沁右翼中旗',
            ),
            152223 =>
            array(
                'code' => 151005,
                'area_name' => '扎赉特旗',
                'name' => '扎赉特旗',
            ),
            152224 =>
            array(
                'code' => 151006,
                'area_name' => '突泉县',
                'name' => '突泉县',
            ),
            152501 =>
            array(
                'code' => 151101,
                'area_name' => '二连浩特市',
                'name' => '二连浩特市',
            ),
            152502 =>
            array(
                'code' => 151102,
                'area_name' => '锡林浩特市',
                'name' => '锡林浩特市',
            ),
            152522 =>
            array(
                'code' => 151103,
                'area_name' => '阿巴嘎旗',
                'name' => '阿巴嘎旗',
            ),
            152523 =>
            array(
                'code' => 151104,
                'area_name' => '苏尼特左旗',
                'name' => '苏尼特左旗',
            ),
            152524 =>
            array(
                'code' => 151105,
                'area_name' => '苏尼特右旗',
                'name' => '苏尼特右旗',
            ),
            152525 =>
            array(
                'code' => 151106,
                'area_name' => '东乌珠穆沁旗',
                'name' => '东乌珠穆沁旗',
            ),
            152526 =>
            array(
                'code' => 151107,
                'area_name' => '西乌珠穆沁旗',
                'name' => '西乌珠穆沁旗',
            ),
            152527 =>
            array(
                'code' => 151108,
                'area_name' => '太仆寺旗',
                'name' => '太仆寺旗',
            ),
            152528 =>
            array(
                'code' => 151109,
                'area_name' => '镶黄旗',
                'name' => '镶黄旗',
            ),
            152529 =>
            array(
                'code' => 151110,
                'area_name' => '正镶白旗',
                'name' => '正镶白旗',
            ),
            152530 =>
            array(
                'code' => 151111,
                'area_name' => '正蓝旗',
                'name' => '正蓝旗',
            ),
            152531 =>
            array(
                'code' => 151112,
                'area_name' => '多伦县',
                'name' => '多伦县',
            ),
            152921 =>
            array(
                'code' => 151201,
                'area_name' => '阿拉善左旗',
                'name' => '阿拉善左旗',
            ),
            152922 =>
            array(
                'code' => 151202,
                'area_name' => '阿拉善右旗',
                'name' => '阿拉善右旗',
            ),
            152923 =>
            array(
                'code' => 151203,
                'area_name' => '额济纳旗',
                'name' => '额济纳旗',
            ),
            210102 =>
            array(
                'code' => 200102,
                'area_name' => '和平区',
                'name' => '和平区',
            ),
            210103 =>
            array(
                'code' => 200101,
                'area_name' => '沈河区',
                'name' => '沈河区',
            ),
            210104 =>
            array(
                'code' => 200103,
                'area_name' => '大东区',
                'name' => '大东区',
            ),
            210105 =>
            array(
                'code' => 200104,
                'area_name' => '皇姑区',
                'name' => '皇姑区',
            ),
            210106 =>
            array(
                'code' => 200105,
                'area_name' => '铁西区',
                'name' => '铁西区',
            ),
            210111 =>
            array(
                'code' => 200106,
                'area_name' => '苏家屯区',
                'name' => '苏家屯区',
            ),
            210112 =>
            array(
                'code' => 200107,
                'area_name' => '东陵区',
                'name' => '东陵区',
            ),
            210114 =>
            array(
                'code' => 200109,
                'area_name' => '于洪区',
                'name' => '于洪区',
            ),
            210122 =>
            array(
                'code' => 200111,
                'area_name' => '辽中县',
                'name' => '辽中县',
            ),
            210123 =>
            array(
                'code' => 200112,
                'area_name' => '康平县',
                'name' => '康平县',
            ),
            210124 =>
            array(
                'code' => 200113,
                'area_name' => '法库县',
                'name' => '法库县',
            ),
            210181 =>
            array(
                'code' => 200110,
                'area_name' => '新民市',
                'name' => '新民市',
            ),
            210184 =>
            array(
                'code' => 200108,
                'area_name' => '沈北新区',
                'name' => '沈北新区',
            ),
            210202 =>
            array(
                'code' => 200202,
                'area_name' => '中山区',
                'name' => '中山区',
            ),
            210203 =>
            array(
                'code' => 200201,
                'area_name' => '西岗区',
                'name' => '西岗区',
            ),
            210204 =>
            array(
                'code' => 200203,
                'area_name' => '沙河口区',
                'name' => '沙河口区',
            ),
            210211 =>
            array(
                'code' => 200204,
                'area_name' => '甘井子区',
                'name' => '甘井子区',
            ),
            210212 =>
            array(
                'code' => 200205,
                'area_name' => '旅顺口区',
                'name' => '旅顺口区',
            ),
            210213 =>
            array(
                'code' => 200206,
                'area_name' => '金州区',
                'name' => '金州区',
            ),
            210224 =>
            array(
                'code' => 200210,
                'area_name' => '长海县',
                'name' => '长海县',
            ),
            210281 =>
            array(
                'code' => 200207,
                'area_name' => '瓦房店市',
                'name' => '瓦房店市',
            ),
            210282 =>
            array(
                'code' => 200208,
                'area_name' => '普兰店市',
                'name' => '普兰店市',
            ),
            210283 =>
            array(
                'code' => 200209,
                'area_name' => '庄河市',
                'name' => '庄河市',
            ),
            210302 =>
            array(
                'code' => 200301,
                'area_name' => '铁东区',
                'name' => '铁东区',
            ),
            210303 =>
            array(
                'code' => 200302,
                'area_name' => '铁西区',
                'name' => '铁西区',
            ),
            210304 =>
            array(
                'code' => 200303,
                'area_name' => '立山区',
                'name' => '立山区',
            ),
            210311 =>
            array(
                'code' => 200304,
                'area_name' => '千山区',
                'name' => '千山区',
            ),
            210321 =>
            array(
                'code' => 200306,
                'area_name' => '台安县',
                'name' => '台安县',
            ),
            210323 =>
            array(
                'code' => 200307,
                'area_name' => '岫岩满族自治县',
                'name' => '岫岩满族自治县',
            ),
            210381 =>
            array(
                'code' => 200305,
                'area_name' => '海城市',
                'name' => '海城市',
            ),
            210402 =>
            array(
                'code' => 200402,
                'area_name' => '新抚区',
                'name' => '新抚区',
            ),
            210403 =>
            array(
                'code' => 200403,
                'area_name' => '东洲区',
                'name' => '东洲区',
            ),
            210404 =>
            array(
                'code' => 200404,
                'area_name' => '望花区',
                'name' => '望花区',
            ),
            210411 =>
            array(
                'code' => 200401,
                'area_name' => '顺城区',
                'name' => '顺城区',
            ),
            210421 =>
            array(
                'code' => 200405,
                'area_name' => '抚顺县',
                'name' => '抚顺县',
            ),
            210422 =>
            array(
                'code' => 200406,
                'area_name' => '新宾满族自治县',
                'name' => '新宾满族自治县',
            ),
            210423 =>
            array(
                'code' => 200407,
                'area_name' => '清原满族自治县',
                'name' => '清原满族自治县',
            ),
            210502 =>
            array(
                'code' => 200501,
                'area_name' => '平山区',
                'name' => '平山区',
            ),
            210503 =>
            array(
                'code' => 200502,
                'area_name' => '溪湖区',
                'name' => '溪湖区',
            ),
            210504 =>
            array(
                'code' => 200503,
                'area_name' => '明山区',
                'name' => '明山区',
            ),
            210505 =>
            array(
                'code' => 200504,
                'area_name' => '南芬区',
                'name' => '南芬区',
            ),
            210521 =>
            array(
                'code' => 200505,
                'area_name' => '本溪满族自治县',
                'name' => '本溪满族自治县',
            ),
            210522 =>
            array(
                'code' => 200506,
                'area_name' => '桓仁满族自治县',
                'name' => '桓仁满族自治县',
            ),
            210602 =>
            array(
                'code' => 200602,
                'area_name' => '元宝区',
                'name' => '元宝区',
            ),
            210603 =>
            array(
                'code' => 200601,
                'area_name' => '振兴区',
                'name' => '振兴区',
            ),
            210604 =>
            array(
                'code' => 200603,
                'area_name' => '振安区',
                'name' => '振安区',
            ),
            210624 =>
            array(
                'code' => 200606,
                'area_name' => '宽甸满族自治县',
                'name' => '宽甸满族自治县',
            ),
            210681 =>
            array(
                'code' => 200604,
                'area_name' => '东港市',
                'name' => '东港市',
            ),
            210682 =>
            array(
                'code' => 200605,
                'area_name' => '凤城市',
                'name' => '凤城市',
            ),
            210702 =>
            array(
                'code' => 200702,
                'area_name' => '古塔区',
                'name' => '古塔区',
            ),
            210703 =>
            array(
                'code' => 200703,
                'area_name' => '凌河区',
                'name' => '凌河区',
            ),
            210711 =>
            array(
                'code' => 200701,
                'area_name' => '太和区',
                'name' => '太和区',
            ),
            210726 =>
            array(
                'code' => 200706,
                'area_name' => '黑山县',
                'name' => '黑山县',
            ),
            210727 =>
            array(
                'code' => 200707,
                'area_name' => '义县',
                'name' => '义县',
            ),
            210781 =>
            array(
                'code' => 200704,
                'area_name' => '凌海市',
                'name' => '凌海市',
            ),
            210782 =>
            array(
                'code' => 200705,
                'area_name' => '北镇市',
                'name' => '北镇市',
            ),
            210802 =>
            array(
                'code' => 200801,
                'area_name' => '站前区',
                'name' => '站前区',
            ),
            210803 =>
            array(
                'code' => 200802,
                'area_name' => '西市区',
                'name' => '西市区',
            ),
            210804 =>
            array(
                'code' => 200803,
                'area_name' => '鲅鱼圈区',
                'name' => '鲅鱼圈区',
            ),
            210811 =>
            array(
                'code' => 200804,
                'area_name' => '老边区',
                'name' => '老边区',
            ),
            210881 =>
            array(
                'code' => 200805,
                'area_name' => '盖州市',
                'name' => '盖州市',
            ),
            210882 =>
            array(
                'code' => 200806,
                'area_name' => '大石桥市',
                'name' => '大石桥市',
            ),
            210902 =>
            array(
                'code' => 200901,
                'area_name' => '海州区',
                'name' => '海州区',
            ),
            210903 =>
            array(
                'code' => 200902,
                'area_name' => '新邱区',
                'name' => '新邱区',
            ),
            210904 =>
            array(
                'code' => 200903,
                'area_name' => '太平区',
                'name' => '太平区',
            ),
            210905 =>
            array(
                'code' => 200904,
                'area_name' => '清河门区',
                'name' => '清河门区',
            ),
            210911 =>
            array(
                'code' => 200905,
                'area_name' => '细河区',
                'name' => '细河区',
            ),
            210921 =>
            array(
                'code' => 200906,
                'area_name' => '阜新蒙古族自治县',
                'name' => '阜新蒙古族自治县',
            ),
            210922 =>
            array(
                'code' => 200907,
                'area_name' => '彰武县',
                'name' => '彰武县',
            ),
            211002 =>
            array(
                'code' => 201001,
                'area_name' => '白塔区',
                'name' => '白塔区',
            ),
            211003 =>
            array(
                'code' => 201002,
                'area_name' => '文圣区',
                'name' => '文圣区',
            ),
            211004 =>
            array(
                'code' => 201003,
                'area_name' => '宏伟区',
                'name' => '宏伟区',
            ),
            211005 =>
            array(
                'code' => 201004,
                'area_name' => '弓长岭区',
                'name' => '弓长岭区',
            ),
            211011 =>
            array(
                'code' => 201005,
                'area_name' => '太子河区',
                'name' => '太子河区',
            ),
            211021 =>
            array(
                'code' => 201007,
                'area_name' => '辽阳县',
                'name' => '辽阳县',
            ),
            211081 =>
            array(
                'code' => 201006,
                'area_name' => '灯塔市',
                'name' => '灯塔市',
            ),
            211102 =>
            array(
                'code' => 201102,
                'area_name' => '双台子区',
                'name' => '双台子区',
            ),
            211103 =>
            array(
                'code' => 201101,
                'area_name' => '兴隆台区',
                'name' => '兴隆台区',
            ),
            211121 =>
            array(
                'code' => 201103,
                'area_name' => '大洼县',
                'name' => '大洼县',
            ),
            211122 =>
            array(
                'code' => 201104,
                'area_name' => '盘山县',
                'name' => '盘山县',
            ),
            211202 =>
            array(
                'code' => 201201,
                'area_name' => '银州区',
                'name' => '银州区',
            ),
            211204 =>
            array(
                'code' => 201202,
                'area_name' => '清河区',
                'name' => '清河区',
            ),
            211221 =>
            array(
                'code' => 201205,
                'area_name' => '铁岭县',
                'name' => '铁岭县',
            ),
            211223 =>
            array(
                'code' => 201206,
                'area_name' => '西丰县',
                'name' => '西丰县',
            ),
            211224 =>
            array(
                'code' => 201207,
                'area_name' => '昌图县',
                'name' => '昌图县',
            ),
            211281 =>
            array(
                'code' => 201203,
                'area_name' => '调兵山市',
                'name' => '调兵山市',
            ),
            211282 =>
            array(
                'code' => 201204,
                'area_name' => '开原市',
                'name' => '开原市',
            ),
            211302 =>
            array(
                'code' => 201301,
                'area_name' => '双塔区',
                'name' => '双塔区',
            ),
            211303 =>
            array(
                'code' => 201302,
                'area_name' => '龙城区',
                'name' => '龙城区',
            ),
            211321 =>
            array(
                'code' => 201305,
                'area_name' => '朝阳县',
                'name' => '朝阳县',
            ),
            211322 =>
            array(
                'code' => 201306,
                'area_name' => '建平县',
                'name' => '建平县',
            ),
            211324 =>
            array(
                'code' => 201307,
                'area_name' => '喀喇沁左翼蒙古族自治县',
                'name' => '喀喇沁左翼蒙古族自治县',
            ),
            211381 =>
            array(
                'code' => 201303,
                'area_name' => '北票市',
                'name' => '北票市',
            ),
            211382 =>
            array(
                'code' => 201304,
                'area_name' => '凌源市',
                'name' => '凌源市',
            ),
            211402 =>
            array(
                'code' => 201402,
                'area_name' => '连山区',
                'name' => '连山区',
            ),
            211403 =>
            array(
                'code' => 201401,
                'area_name' => '龙港区',
                'name' => '龙港区',
            ),
            211404 =>
            array(
                'code' => 201403,
                'area_name' => '南票区',
                'name' => '南票区',
            ),
            211421 =>
            array(
                'code' => 201405,
                'area_name' => '绥中县',
                'name' => '绥中县',
            ),
            211422 =>
            array(
                'code' => 201406,
                'area_name' => '建昌县',
                'name' => '建昌县',
            ),
            211481 =>
            array(
                'code' => 201404,
                'area_name' => '兴城市',
                'name' => '兴城市',
            ),
            220102 =>
            array(
                'code' => 210102,
                'area_name' => '南关区',
                'name' => '南关区',
            ),
            220103 =>
            array(
                'code' => 210103,
                'area_name' => '宽城区',
                'name' => '宽城区',
            ),
            220104 =>
            array(
                'code' => 210101,
                'area_name' => '朝阳区',
                'name' => '朝阳区',
            ),
            220105 =>
            array(
                'code' => 210104,
                'area_name' => '二道区',
                'name' => '二道区',
            ),
            220106 =>
            array(
                'code' => 210105,
                'area_name' => '绿园区',
                'name' => '绿园区',
            ),
            220112 =>
            array(
                'code' => 210106,
                'area_name' => '双阳区',
                'name' => '双阳区',
            ),
            220122 =>
            array(
                'code' => 210110,
                'area_name' => '农安县',
                'name' => '农安县',
            ),
            220181 =>
            array(
                'code' => 210108,
                'area_name' => '九台市',
                'name' => '九台市',
            ),
            220182 =>
            array(
                'code' => 210109,
                'area_name' => '榆树市',
                'name' => '榆树市',
            ),
            220183 =>
            array(
                'code' => 210107,
                'area_name' => '德惠市',
                'name' => '德惠市',
            ),
            220202 =>
            array(
                'code' => 210403,
                'area_name' => '昌邑区',
                'name' => '昌邑区',
            ),
            220203 =>
            array(
                'code' => 210402,
                'area_name' => '龙潭区',
                'name' => '龙潭区',
            ),
            220204 =>
            array(
                'code' => 210401,
                'area_name' => '船营区',
                'name' => '船营区',
            ),
            220211 =>
            array(
                'code' => 210404,
                'area_name' => '丰满区',
                'name' => '丰满区',
            ),
            220221 =>
            array(
                'code' => 210409,
                'area_name' => '永吉县',
                'name' => '永吉县',
            ),
            220281 =>
            array(
                'code' => 210406,
                'area_name' => '蛟河市',
                'name' => '蛟河市',
            ),
            220282 =>
            array(
                'code' => 210407,
                'area_name' => '桦甸市',
                'name' => '桦甸市',
            ),
            220283 =>
            array(
                'code' => 210408,
                'area_name' => '舒兰市',
                'name' => '舒兰市',
            ),
            220284 =>
            array(
                'code' => 210405,
                'area_name' => '磐石市',
                'name' => '磐石市',
            ),
            220302 =>
            array(
                'code' => 210501,
                'area_name' => '铁西区',
                'name' => '铁西区',
            ),
            220303 =>
            array(
                'code' => 210502,
                'area_name' => '铁东区',
                'name' => '铁东区',
            ),
            220322 =>
            array(
                'code' => 210505,
                'area_name' => '梨树县',
                'name' => '梨树县',
            ),
            220323 =>
            array(
                'code' => 210506,
                'area_name' => '伊通满族自治县',
                'name' => '伊通满族自治县',
            ),
            220381 =>
            array(
                'code' => 210504,
                'area_name' => '公主岭市',
                'name' => '公主岭市',
            ),
            220382 =>
            array(
                'code' => 210503,
                'area_name' => '双辽市',
                'name' => '双辽市',
            ),
            220402 =>
            array(
                'code' => 210601,
                'area_name' => '龙山区',
                'name' => '龙山区',
            ),
            220403 =>
            array(
                'code' => 210602,
                'area_name' => '西安区',
                'name' => '西安区',
            ),
            220421 =>
            array(
                'code' => 210603,
                'area_name' => '东丰县',
                'name' => '东丰县',
            ),
            220422 =>
            array(
                'code' => 210604,
                'area_name' => '东辽县',
                'name' => '东辽县',
            ),
            220502 =>
            array(
                'code' => 210701,
                'area_name' => '东昌区',
                'name' => '东昌区',
            ),
            220503 =>
            array(
                'code' => '',
                'area_name' => '二道江区',
                'name' => '',
            ),
            220521 =>
            array(
                'code' => 210705,
                'area_name' => '通化县',
                'name' => '通化县',
            ),
            220523 =>
            array(
                'code' => 210706,
                'area_name' => '辉南县',
                'name' => '辉南县',
            ),
            220524 =>
            array(
                'code' => 210707,
                'area_name' => '柳河县',
                'name' => '柳河县',
            ),
            220581 =>
            array(
                'code' => 210703,
                'area_name' => '梅河口市',
                'name' => '梅河口市',
            ),
            220582 =>
            array(
                'code' => '',
                'area_name' => '集安市',
                'name' => '',
            ),
            220602 =>
            array(
                'code' => '',
                'area_name' => '浑江区',
                'name' => '',
            ),
            220621 =>
            array(
                'code' => 210804,
                'area_name' => '抚松县',
                'name' => '抚松县',
            ),
            220622 =>
            array(
                'code' => 210805,
                'area_name' => '靖宇县',
                'name' => '靖宇县',
            ),
            220623 =>
            array(
                'code' => 210806,
                'area_name' => '长白朝鲜族自治县',
                'name' => '长白朝鲜族自治县',
            ),
            220625 =>
            array(
                'code' => '',
                'area_name' => '江源区',
                'name' => '',
            ),
            220681 =>
            array(
                'code' => 210802,
                'area_name' => '临江市',
                'name' => '临江市',
            ),
            220702 =>
            array(
                'code' => 210301,
                'area_name' => '宁江区',
                'name' => '宁江区',
            ),
            220721 =>
            array(
                'code' => 210305,
                'area_name' => '前郭尔罗斯蒙古族自治县',
                'name' => '前郭尔罗斯蒙古族自治县',
            ),
            220722 =>
            array(
                'code' => 210303,
                'area_name' => '长岭县',
                'name' => '长岭县',
            ),
            220723 =>
            array(
                'code' => 210304,
                'area_name' => '乾安县',
                'name' => '乾安县',
            ),
            220724 =>
            array(
                'code' => '',
                'area_name' => '扶余市',
                'name' => '',
            ),
            220802 =>
            array(
                'code' => 210201,
                'area_name' => '洮北区',
                'name' => '洮北区',
            ),
            220821 =>
            array(
                'code' => 210204,
                'area_name' => '镇赉县',
                'name' => '镇赉县',
            ),
            220822 =>
            array(
                'code' => 210205,
                'area_name' => '通榆县',
                'name' => '通榆县',
            ),
            220881 =>
            array(
                'code' => 210203,
                'area_name' => '洮南市',
                'name' => '洮南市',
            ),
            220882 =>
            array(
                'code' => 210202,
                'area_name' => '大安市',
                'name' => '大安市',
            ),
            222401 =>
            array(
                'code' => '',
                'area_name' => '延吉市',
                'name' => '',
            ),
            222402 =>
            array(
                'code' => '',
                'area_name' => '图们市',
                'name' => '',
            ),
            222403 =>
            array(
                'code' => '',
                'area_name' => '敦化市',
                'name' => '',
            ),
            222404 =>
            array(
                'code' => '',
                'area_name' => '珲春市',
                'name' => '',
            ),
            222405 =>
            array(
                'code' => '',
                'area_name' => '龙井市',
                'name' => '',
            ),
            222406 =>
            array(
                'code' => '',
                'area_name' => '和龙市',
                'name' => '',
            ),
            222424 =>
            array(
                'code' => '',
                'area_name' => '汪清县',
                'name' => '',
            ),
            222426 =>
            array(
                'code' => '',
                'area_name' => '安图县',
                'name' => '',
            ),
            230102 =>
            array(
                'code' => 220102,
                'area_name' => '道里区',
                'name' => '道里区',
            ),
            230103 =>
            array(
                'code' => 220103,
                'area_name' => '南岗区',
                'name' => '南岗区',
            ),
            230104 =>
            array(
                'code' => 220104,
                'area_name' => '道外区',
                'name' => '道外区',
            ),
            230106 =>
            array(
                'code' => 220105,
                'area_name' => '香坊区',
                'name' => '香坊区',
            ),
            230108 =>
            array(
                'code' => 220107,
                'area_name' => '平房区',
                'name' => '平房区',
            ),
            230109 =>
            array(
                'code' => 220101,
                'area_name' => '松北区',
                'name' => '松北区',
            ),
            230111 =>
            array(
                'code' => 220108,
                'area_name' => '呼兰区',
                'name' => '呼兰区',
            ),
            230123 =>
            array(
                'code' => 220113,
                'area_name' => '依兰县',
                'name' => '依兰县',
            ),
            230124 =>
            array(
                'code' => 220114,
                'area_name' => '方正县',
                'name' => '方正县',
            ),
            230125 =>
            array(
                'code' => 220115,
                'area_name' => '宾县',
                'name' => '宾县',
            ),
            230126 =>
            array(
                'code' => 220116,
                'area_name' => '巴彦县',
                'name' => '巴彦县',
            ),
            230127 =>
            array(
                'code' => 220117,
                'area_name' => '木兰县',
                'name' => '木兰县',
            ),
            230128 =>
            array(
                'code' => 220118,
                'area_name' => '通河县',
                'name' => '通河县',
            ),
            230129 =>
            array(
                'code' => 220119,
                'area_name' => '延寿县',
                'name' => '延寿县',
            ),
            230181 =>
            array(
                'code' => '',
                'area_name' => '阿城区',
                'name' => '',
            ),
            230182 =>
            array(
                'code' => 220109,
                'area_name' => '双城市',
                'name' => '双城市',
            ),
            230183 =>
            array(
                'code' => 220110,
                'area_name' => '尚志市',
                'name' => '尚志市',
            ),
            230184 =>
            array(
                'code' => 220111,
                'area_name' => '五常市',
                'name' => '五常市',
            ),
            230202 =>
            array(
                'code' => 220301,
                'area_name' => '龙沙区',
                'name' => '龙沙区',
            ),
            230203 =>
            array(
                'code' => 220304,
                'area_name' => '建华区',
                'name' => '建华区',
            ),
            230204 =>
            array(
                'code' => '',
                'area_name' => '铁锋区',
                'name' => '',
            ),
            230205 =>
            array(
                'code' => 220302,
                'area_name' => '昂昂溪区',
                'name' => '昂昂溪区',
            ),
            230206 =>
            array(
                'code' => 220305,
                'area_name' => '富拉尔基区',
                'name' => '富拉尔基区',
            ),
            230207 =>
            array(
                'code' => 220306,
                'area_name' => '碾子山区',
                'name' => '碾子山区',
            ),
            230208 =>
            array(
                'code' => '',
                'area_name' => '梅里斯达斡尔族区',
                'name' => '',
            ),
            230221 =>
            array(
                'code' => 220316,
                'area_name' => '龙江县',
                'name' => '龙江县',
            ),
            230223 =>
            array(
                'code' => 220312,
                'area_name' => '依安县',
                'name' => '依安县',
            ),
            230224 =>
            array(
                'code' => 220314,
                'area_name' => '泰来县',
                'name' => '泰来县',
            ),
            230225 =>
            array(
                'code' => 220311,
                'area_name' => '甘南县',
                'name' => '甘南县',
            ),
            230227 =>
            array(
                'code' => 220309,
                'area_name' => '富裕县',
                'name' => '富裕县',
            ),
            230229 =>
            array(
                'code' => 220313,
                'area_name' => '克山县',
                'name' => '克山县',
            ),
            230230 =>
            array(
                'code' => 220315,
                'area_name' => '克东县',
                'name' => '克东县',
            ),
            230231 =>
            array(
                'code' => 220310,
                'area_name' => '拜泉县',
                'name' => '拜泉县',
            ),
            230281 =>
            array(
                'code' => 220308,
                'area_name' => '讷河市',
                'name' => '讷河市',
            ),
            230302 =>
            array(
                'code' => 221001,
                'area_name' => '鸡冠区',
                'name' => '鸡冠区',
            ),
            230303 =>
            array(
                'code' => 221002,
                'area_name' => '恒山区',
                'name' => '恒山区',
            ),
            230304 =>
            array(
                'code' => 221004,
                'area_name' => '滴道区',
                'name' => '滴道区',
            ),
            230305 =>
            array(
                'code' => 221005,
                'area_name' => '梨树区',
                'name' => '梨树区',
            ),
            230306 =>
            array(
                'code' => 221003,
                'area_name' => '城子河区',
                'name' => '城子河区',
            ),
            230307 =>
            array(
                'code' => 221006,
                'area_name' => '麻山区',
                'name' => '麻山区',
            ),
            230321 =>
            array(
                'code' => 221009,
                'area_name' => '鸡东县',
                'name' => '鸡东县',
            ),
            230381 =>
            array(
                'code' => 221008,
                'area_name' => '虎林市',
                'name' => '虎林市',
            ),
            230382 =>
            array(
                'code' => 221007,
                'area_name' => '密山市',
                'name' => '密山市',
            ),
            230402 =>
            array(
                'code' => 220605,
                'area_name' => '向阳区',
                'name' => '向阳区',
            ),
            230403 =>
            array(
                'code' => 220602,
                'area_name' => '工农区',
                'name' => '工农区',
            ),
            230404 =>
            array(
                'code' => 220603,
                'area_name' => '南山区',
                'name' => '南山区',
            ),
            230405 =>
            array(
                'code' => 220604,
                'area_name' => '兴安区',
                'name' => '兴安区',
            ),
            230406 =>
            array(
                'code' => 220606,
                'area_name' => '东山区',
                'name' => '东山区',
            ),
            230407 =>
            array(
                'code' => 220601,
                'area_name' => '兴山区',
                'name' => '兴山区',
            ),
            230421 =>
            array(
                'code' => 220607,
                'area_name' => '萝北县',
                'name' => '萝北县',
            ),
            230422 =>
            array(
                'code' => 220608,
                'area_name' => '绥滨县',
                'name' => '绥滨县',
            ),
            230502 =>
            array(
                'code' => 220901,
                'area_name' => '尖山区',
                'name' => '尖山区',
            ),
            230503 =>
            array(
                'code' => 220902,
                'area_name' => '岭东区',
                'name' => '岭东区',
            ),
            230505 =>
            array(
                'code' => 220903,
                'area_name' => '四方台区',
                'name' => '四方台区',
            ),
            230506 =>
            array(
                'code' => 220904,
                'area_name' => '宝山区',
                'name' => '宝山区',
            ),
            230521 =>
            array(
                'code' => 220905,
                'area_name' => '集贤县',
                'name' => '集贤县',
            ),
            230522 =>
            array(
                'code' => 220907,
                'area_name' => '友谊县',
                'name' => '友谊县',
            ),
            230523 =>
            array(
                'code' => 220906,
                'area_name' => '宝清县',
                'name' => '宝清县',
            ),
            230524 =>
            array(
                'code' => 220908,
                'area_name' => '饶河县',
                'name' => '饶河县',
            ),
            230602 =>
            array(
                'code' => 220501,
                'area_name' => '萨尔图区',
                'name' => '萨尔图区',
            ),
            230603 =>
            array(
                'code' => 220503,
                'area_name' => '龙凤区',
                'name' => '龙凤区',
            ),
            230604 =>
            array(
                'code' => 220504,
                'area_name' => '让胡路区',
                'name' => '让胡路区',
            ),
            230605 =>
            array(
                'code' => 220502,
                'area_name' => '红岗区',
                'name' => '红岗区',
            ),
            230606 =>
            array(
                'code' => 220505,
                'area_name' => '大同区',
                'name' => '大同区',
            ),
            230621 =>
            array(
                'code' => 220507,
                'area_name' => '肇州县',
                'name' => '肇州县',
            ),
            230622 =>
            array(
                'code' => 220508,
                'area_name' => '肇源县',
                'name' => '肇源县',
            ),
            230623 =>
            array(
                'code' => 220506,
                'area_name' => '林甸县',
                'name' => '林甸县',
            ),
            230624 =>
            array(
                'code' => 220509,
                'area_name' => '杜尔伯特蒙古族自治县',
                'name' => '杜尔伯特蒙古族自治县',
            ),
            230702 =>
            array(
                'code' => 220701,
                'area_name' => '伊春区',
                'name' => '伊春区',
            ),
            230703 =>
            array(
                'code' => 220703,
                'area_name' => '南岔区',
                'name' => '南岔区',
            ),
            230704 =>
            array(
                'code' => 220709,
                'area_name' => '友好区',
                'name' => '友好区',
            ),
            230705 =>
            array(
                'code' => 220705,
                'area_name' => '西林区',
                'name' => '西林区',
            ),
            230706 =>
            array(
                'code' => 220708,
                'area_name' => '翠峦区',
                'name' => '翠峦区',
            ),
            230707 =>
            array(
                'code' => 220713,
                'area_name' => '新青区',
                'name' => '新青区',
            ),
            230708 =>
            array(
                'code' => 220706,
                'area_name' => '美溪区',
                'name' => '美溪区',
            ),
            230709 =>
            array(
                'code' => 220704,
                'area_name' => '金山屯区',
                'name' => '金山屯区',
            ),
            230710 =>
            array(
                'code' => 220711,
                'area_name' => '五营区',
                'name' => '五营区',
            ),
            230711 =>
            array(
                'code' => 220707,
                'area_name' => '乌马河区',
                'name' => '乌马河区',
            ),
            230712 =>
            array(
                'code' => 220714,
                'area_name' => '汤旺河区',
                'name' => '汤旺河区',
            ),
            230713 =>
            array(
                'code' => 220702,
                'area_name' => '带岭区',
                'name' => '带岭区',
            ),
            230714 =>
            array(
                'code' => 220715,
                'area_name' => '乌伊岭区',
                'name' => '乌伊岭区',
            ),
            230715 =>
            array(
                'code' => 220712,
                'area_name' => '红星区',
                'name' => '红星区',
            ),
            230716 =>
            array(
                'code' => 220710,
                'area_name' => '上甘岭区',
                'name' => '上甘岭区',
            ),
            230722 =>
            array(
                'code' => 220717,
                'area_name' => '嘉荫县',
                'name' => '嘉荫县',
            ),
            230781 =>
            array(
                'code' => 220716,
                'area_name' => '铁力市',
                'name' => '铁力市',
            ),
            230803 =>
            array(
                'code' => 220803,
                'area_name' => '向阳区',
                'name' => '向阳区',
            ),
            230804 =>
            array(
                'code' => 220801,
                'area_name' => '前进区',
                'name' => '前进区',
            ),
            230805 =>
            array(
                'code' => 220804,
                'area_name' => '东风区',
                'name' => '东风区',
            ),
            230811 =>
            array(
                'code' => '',
                'area_name' => '郊区',
                'name' => '',
            ),
            230822 =>
            array(
                'code' => 220810,
                'area_name' => '桦南县',
                'name' => '桦南县',
            ),
            230826 =>
            array(
                'code' => 220808,
                'area_name' => '桦川县',
                'name' => '桦川县',
            ),
            230828 =>
            array(
                'code' => 220811,
                'area_name' => '汤原县',
                'name' => '汤原县',
            ),
            230833 =>
            array(
                'code' => 220809,
                'area_name' => '抚远县',
                'name' => '抚远县',
            ),
            230881 =>
            array(
                'code' => 220806,
                'area_name' => '同江市',
                'name' => '同江市',
            ),
            230882 =>
            array(
                'code' => 220807,
                'area_name' => '富锦市',
                'name' => '富锦市',
            ),
            230902 =>
            array(
                'code' => 220202,
                'area_name' => '新兴区',
                'name' => '新兴区',
            ),
            230903 =>
            array(
                'code' => 220201,
                'area_name' => '桃山区',
                'name' => '桃山区',
            ),
            230904 =>
            array(
                'code' => 220203,
                'area_name' => '茄子河区',
                'name' => '茄子河区',
            ),
            230921 =>
            array(
                'code' => 220204,
                'area_name' => '勃利县',
                'name' => '勃利县',
            ),
            231002 =>
            array(
                'code' => 221102,
                'area_name' => '东安区',
                'name' => '东安区',
            ),
            231003 =>
            array(
                'code' => 221103,
                'area_name' => '阳明区',
                'name' => '阳明区',
            ),
            231004 =>
            array(
                'code' => 221101,
                'area_name' => '爱民区',
                'name' => '爱民区',
            ),
            231005 =>
            array(
                'code' => 221104,
                'area_name' => '西安区',
                'name' => '西安区',
            ),
            231024 =>
            array(
                'code' => 221110,
                'area_name' => '东宁县',
                'name' => '东宁县',
            ),
            231025 =>
            array(
                'code' => 221109,
                'area_name' => '林口县',
                'name' => '林口县',
            ),
            231081 =>
            array(
                'code' => 221105,
                'area_name' => '绥芬河市',
                'name' => '绥芬河市',
            ),
            231083 =>
            array(
                'code' => 221107,
                'area_name' => '海林市',
                'name' => '海林市',
            ),
            231084 =>
            array(
                'code' => 221106,
                'area_name' => '宁安市',
                'name' => '宁安市',
            ),
            231085 =>
            array(
                'code' => 221108,
                'area_name' => '穆棱市',
                'name' => '穆棱市',
            ),
            231102 =>
            array(
                'code' => 220401,
                'area_name' => '爱辉区',
                'name' => '爱辉区',
            ),
            231121 =>
            array(
                'code' => 220405,
                'area_name' => '嫩江县',
                'name' => '嫩江县',
            ),
            231123 =>
            array(
                'code' => 220404,
                'area_name' => '逊克县',
                'name' => '逊克县',
            ),
            231124 =>
            array(
                'code' => 220406,
                'area_name' => '孙吴县',
                'name' => '孙吴县',
            ),
            231181 =>
            array(
                'code' => 220402,
                'area_name' => '北安市',
                'name' => '北安市',
            ),
            231182 =>
            array(
                'code' => 220403,
                'area_name' => '五大连池市',
                'name' => '五大连池市',
            ),
            231202 =>
            array(
                'code' => 221201,
                'area_name' => '北林区',
                'name' => '北林区',
            ),
            231221 =>
            array(
                'code' => 221210,
                'area_name' => '望奎县',
                'name' => '望奎县',
            ),
            231222 =>
            array(
                'code' => 221206,
                'area_name' => '兰西县',
                'name' => '兰西县',
            ),
            231223 =>
            array(
                'code' => 221208,
                'area_name' => '青冈县',
                'name' => '青冈县',
            ),
            231224 =>
            array(
                'code' => 221209,
                'area_name' => '庆安县',
                'name' => '庆安县',
            ),
            231225 =>
            array(
                'code' => 221207,
                'area_name' => '明水县',
                'name' => '明水县',
            ),
            231226 =>
            array(
                'code' => 221205,
                'area_name' => '绥棱县',
                'name' => '绥棱县',
            ),
            231281 =>
            array(
                'code' => 221202,
                'area_name' => '安达市',
                'name' => '安达市',
            ),
            231282 =>
            array(
                'code' => 221203,
                'area_name' => '肇东市',
                'name' => '肇东市',
            ),
            231283 =>
            array(
                'code' => 221204,
                'area_name' => '海伦市',
                'name' => '海伦市',
            ),
            232702 =>
            array(
                'code' => '',
                'area_name' => '松岭区',
                'name' => '',
            ),
            232703 =>
            array(
                'code' => '',
                'area_name' => '新林区',
                'name' => '',
            ),
            232704 =>
            array(
                'code' => '',
                'area_name' => '呼中区',
                'name' => '',
            ),
            232721 =>
            array(
                'code' => '',
                'area_name' => '呼玛县',
                'name' => '',
            ),
            232722 =>
            array(
                'code' => '',
                'area_name' => '塔河县',
                'name' => '',
            ),
            232723 =>
            array(
                'code' => '',
                'area_name' => '漠河县',
                'name' => '',
            ),
            232724 =>
            array(
                'code' => '',
                'area_name' => '加格达奇区',
                'name' => '',
            ),
            310101 =>
            array(
                'code' => 130101,
                'area_name' => '黄浦区',
                'name' => '黄浦区',
            ),
            310104 =>
            array(
                'code' => 130103,
                'area_name' => '徐汇区',
                'name' => '徐汇区',
            ),
            310105 =>
            array(
                'code' => 130104,
                'area_name' => '长宁区',
                'name' => '长宁区',
            ),
            310106 =>
            array(
                'code' => 130105,
                'area_name' => '静安区',
                'name' => '静安区',
            ),
            310107 =>
            array(
                'code' => 130106,
                'area_name' => '普陀区',
                'name' => '普陀区',
            ),
            310108 =>
            array(
                'code' => 130107,
                'area_name' => '闸北区',
                'name' => '闸北区',
            ),
            310109 =>
            array(
                'code' => 130108,
                'area_name' => '虹口区',
                'name' => '虹口区',
            ),
            310110 =>
            array(
                'code' => 130109,
                'area_name' => '杨浦区',
                'name' => '杨浦区',
            ),
            310112 =>
            array(
                'code' => 130111,
                'area_name' => '闵行区',
                'name' => '闵行区',
            ),
            310113 =>
            array(
                'code' => 130110,
                'area_name' => '宝山区',
                'name' => '宝山区',
            ),
            310114 =>
            array(
                'code' => 130112,
                'area_name' => '嘉定区',
                'name' => '嘉定区',
            ),
            310115 =>
            array(
                'code' => 130113,
                'area_name' => '浦东新区',
                'name' => '浦东新区',
            ),
            310116 =>
            array(
                'code' => 130114,
                'area_name' => '金山区',
                'name' => '金山区',
            ),
            310117 =>
            array(
                'code' => 130115,
                'area_name' => '松江区',
                'name' => '松江区',
            ),
            310118 =>
            array(
                'code' => 130116,
                'area_name' => '青浦区',
                'name' => '青浦区',
            ),
            310120 =>
            array(
                'code' => 130118,
                'area_name' => '奉贤区',
                'name' => '奉贤区',
            ),
            310230 =>
            array(
                'code' => 130119,
                'area_name' => '崇明县',
                'name' => '崇明县',
            ),
            320102 =>
            array(
                'code' => 250101,
                'area_name' => '玄武区',
                'name' => '玄武区',
            ),
            320104 =>
            array(
                'code' => 250105,
                'area_name' => '秦淮区',
                'name' => '秦淮区',
            ),
            320105 =>
            array(
                'code' => 250103,
                'area_name' => '建邺区',
                'name' => '建邺区',
            ),
            320106 =>
            array(
                'code' => 250102,
                'area_name' => '鼓楼区',
                'name' => '鼓楼区',
            ),
            320111 =>
            array(
                'code' => 250108,
                'area_name' => '浦口区',
                'name' => '浦口区',
            ),
            320113 =>
            array(
                'code' => 250109,
                'area_name' => '栖霞区',
                'name' => '栖霞区',
            ),
            320114 =>
            array(
                'code' => 250107,
                'area_name' => '雨花台区',
                'name' => '雨花台区',
            ),
            320115 =>
            array(
                'code' => 250110,
                'area_name' => '江宁区',
                'name' => '江宁区',
            ),
            320116 =>
            array(
                'code' => 250111,
                'area_name' => '六合区',
                'name' => '六合区',
            ),
            320124 =>
            array(
                'code' => '',
                'area_name' => '溧水区',
                'name' => '',
            ),
            320125 =>
            array(
                'code' => '',
                'area_name' => '高淳区',
                'name' => '',
            ),
            320202 =>
            array(
                'code' => 251201,
                'area_name' => '崇安区',
                'name' => '崇安区',
            ),
            320203 =>
            array(
                'code' => 251203,
                'area_name' => '南长区',
                'name' => '南长区',
            ),
            320204 =>
            array(
                'code' => 251202,
                'area_name' => '北塘区',
                'name' => '北塘区',
            ),
            320205 =>
            array(
                'code' => 251204,
                'area_name' => '锡山区',
                'name' => '锡山区',
            ),
            320206 =>
            array(
                'code' => 251205,
                'area_name' => '惠山区',
                'name' => '惠山区',
            ),
            320211 =>
            array(
                'code' => 251206,
                'area_name' => '滨湖区',
                'name' => '滨湖区',
            ),
            320281 =>
            array(
                'code' => 251207,
                'area_name' => '江阴市',
                'name' => '江阴市',
            ),
            320282 =>
            array(
                'code' => 251208,
                'area_name' => '宜兴市',
                'name' => '宜兴市',
            ),
            320302 =>
            array(
                'code' => 250202,
                'area_name' => '鼓楼区',
                'name' => '鼓楼区',
            ),
            320303 =>
            array(
                'code' => 250201,
                'area_name' => '云龙区',
                'name' => '云龙区',
            ),
            320305 =>
            array(
                'code' => 250204,
                'area_name' => '贾汪区',
                'name' => '贾汪区',
            ),
            320311 =>
            array(
                'code' => 250205,
                'area_name' => '泉山区',
                'name' => '泉山区',
            ),
            320321 =>
            array(
                'code' => '',
                'area_name' => '丰县',
                'name' => '',
            ),
            320322 =>
            array(
                'code' => '',
                'area_name' => '沛县',
                'name' => '',
            ),
            320323 =>
            array(
                'code' => '',
                'area_name' => '铜山区',
                'name' => '',
            ),
            320324 =>
            array(
                'code' => 250209,
                'area_name' => '睢宁县',
                'name' => '睢宁县',
            ),
            320381 =>
            array(
                'code' => 250207,
                'area_name' => '新沂市',
                'name' => '新沂市',
            ),
            320382 =>
            array(
                'code' => 250206,
                'area_name' => '邳州市',
                'name' => '邳州市',
            ),
            320402 =>
            array(
                'code' => 251102,
                'area_name' => '天宁区',
                'name' => '天宁区',
            ),
            320404 =>
            array(
                'code' => 251101,
                'area_name' => '钟楼区',
                'name' => '钟楼区',
            ),
            320405 =>
            array(
                'code' => 251103,
                'area_name' => '戚墅堰区',
                'name' => '戚墅堰区',
            ),
            320411 =>
            array(
                'code' => 251104,
                'area_name' => '新北区',
                'name' => '新北区',
            ),
            320412 =>
            array(
                'code' => 251105,
                'area_name' => '武进区',
                'name' => '武进区',
            ),
            320481 =>
            array(
                'code' => 251107,
                'area_name' => '溧阳市',
                'name' => '溧阳市',
            ),
            320482 =>
            array(
                'code' => 251106,
                'area_name' => '金坛市',
                'name' => '金坛市',
            ),
            320505 =>
            array(
                'code' => 251304,
                'area_name' => '虎丘区',
                'name' => '虎丘区',
            ),
            320506 =>
            array(
                'code' => 251305,
                'area_name' => '吴中区',
                'name' => '吴中区',
            ),
            320507 =>
            array(
                'code' => 251306,
                'area_name' => '相城区',
                'name' => '相城区',
            ),
            320508 =>
            array(
                'code' => '',
                'area_name' => '姑苏区',
                'name' => '',
            ),
            320581 =>
            array(
                'code' => 251307,
                'area_name' => '常熟市',
                'name' => '常熟市',
            ),
            320582 =>
            array(
                'code' => 251308,
                'area_name' => '张家港市',
                'name' => '张家港市',
            ),
            320583 =>
            array(
                'code' => 251310,
                'area_name' => '昆山市',
                'name' => '昆山市',
            ),
            320584 =>
            array(
                'code' => '',
                'area_name' => '吴江区',
                'name' => '',
            ),
            320585 =>
            array(
                'code' => 251309,
                'area_name' => '太仓市',
                'name' => '太仓市',
            ),
            320602 =>
            array(
                'code' => 250901,
                'area_name' => '崇川区',
                'name' => '崇川区',
            ),
            320611 =>
            array(
                'code' => 250902,
                'area_name' => '港闸区',
                'name' => '港闸区',
            ),
            320612 =>
            array(
                'code' => '',
                'area_name' => '通州区',
                'name' => '',
            ),
            320621 =>
            array(
                'code' => 250907,
                'area_name' => '海安县',
                'name' => '海安县',
            ),
            320623 =>
            array(
                'code' => 250908,
                'area_name' => '如东县',
                'name' => '如东县',
            ),
            320681 =>
            array(
                'code' => 250906,
                'area_name' => '启东市',
                'name' => '启东市',
            ),
            320682 =>
            array(
                'code' => 250903,
                'area_name' => '如皋市',
                'name' => '如皋市',
            ),
            320684 =>
            array(
                'code' => 250905,
                'area_name' => '海门市',
                'name' => '海门市',
            ),
            320703 =>
            array(
                'code' => 250302,
                'area_name' => '连云区',
                'name' => '连云区',
            ),
            320705 =>
            array(
                'code' => 250301,
                'area_name' => '新浦区',
                'name' => '新浦区',
            ),
            320706 =>
            array(
                'code' => 250303,
                'area_name' => '海州区',
                'name' => '海州区',
            ),
            320721 =>
            array(
                'code' => 250306,
                'area_name' => '赣榆县',
                'name' => '赣榆县',
            ),
            320722 =>
            array(
                'code' => 250304,
                'area_name' => '东海县',
                'name' => '东海县',
            ),
            320723 =>
            array(
                'code' => 250305,
                'area_name' => '灌云县',
                'name' => '灌云县',
            ),
            320724 =>
            array(
                'code' => 250307,
                'area_name' => '灌南县',
                'name' => '灌南县',
            ),
            320802 =>
            array(
                'code' => 250401,
                'area_name' => '清河区',
                'name' => '清河区',
            ),
            320803 =>
            array(
                'code' => '',
                'area_name' => '淮安区',
                'name' => '',
            ),
            320804 =>
            array(
                'code' => 250404,
                'area_name' => '淮阴区',
                'name' => '淮阴区',
            ),
            320811 =>
            array(
                'code' => 250402,
                'area_name' => '清浦区',
                'name' => '清浦区',
            ),
            320826 =>
            array(
                'code' => 250405,
                'area_name' => '涟水县',
                'name' => '涟水县',
            ),
            320829 =>
            array(
                'code' => 250406,
                'area_name' => '洪泽县',
                'name' => '洪泽县',
            ),
            320830 =>
            array(
                'code' => 250408,
                'area_name' => '盱眙县',
                'name' => '盱眙县',
            ),
            320831 =>
            array(
                'code' => 250407,
                'area_name' => '金湖县',
                'name' => '金湖县',
            ),
            320902 =>
            array(
                'code' => '',
                'area_name' => '亭湖区',
                'name' => '',
            ),
            320903 =>
            array(
                'code' => '',
                'area_name' => '盐都区',
                'name' => '',
            ),
            320921 =>
            array(
                'code' => 250606,
                'area_name' => '响水县',
                'name' => '响水县',
            ),
            320922 =>
            array(
                'code' => 250609,
                'area_name' => '滨海县',
                'name' => '滨海县',
            ),
            320923 =>
            array(
                'code' => 250607,
                'area_name' => '阜宁县',
                'name' => '阜宁县',
            ),
            320924 =>
            array(
                'code' => 250608,
                'area_name' => '射阳县',
                'name' => '射阳县',
            ),
            320925 =>
            array(
                'code' => 250605,
                'area_name' => '建湖县',
                'name' => '建湖县',
            ),
            320981 =>
            array(
                'code' => 250602,
                'area_name' => '东台市',
                'name' => '东台市',
            ),
            320982 =>
            array(
                'code' => 250603,
                'area_name' => '大丰市',
                'name' => '大丰市',
            ),
            321002 =>
            array(
                'code' => 250701,
                'area_name' => '广陵区',
                'name' => '广陵区',
            ),
            321003 =>
            array(
                'code' => 250703,
                'area_name' => '邗江区',
                'name' => '邗江区',
            ),
            321023 =>
            array(
                'code' => 250707,
                'area_name' => '宝应县',
                'name' => '宝应县',
            ),
            321081 =>
            array(
                'code' => 250706,
                'area_name' => '仪征市',
                'name' => '仪征市',
            ),
            321084 =>
            array(
                'code' => 250704,
                'area_name' => '高邮市',
                'name' => '高邮市',
            ),
            321088 =>
            array(
                'code' => '',
                'area_name' => '江都区',
                'name' => '',
            ),
            321102 =>
            array(
                'code' => 251001,
                'area_name' => '京口区',
                'name' => '京口区',
            ),
            321111 =>
            array(
                'code' => 251002,
                'area_name' => '润州区',
                'name' => '润州区',
            ),
            321112 =>
            array(
                'code' => 251003,
                'area_name' => '丹徒区',
                'name' => '丹徒区',
            ),
            321181 =>
            array(
                'code' => 251004,
                'area_name' => '丹阳市',
                'name' => '丹阳市',
            ),
            321182 =>
            array(
                'code' => 251005,
                'area_name' => '扬中市',
                'name' => '扬中市',
            ),
            321183 =>
            array(
                'code' => 251006,
                'area_name' => '句容市',
                'name' => '句容市',
            ),
            321202 =>
            array(
                'code' => '',
                'area_name' => '海陵区',
                'name' => '',
            ),
            321203 =>
            array(
                'code' => 250802,
                'area_name' => '高港区',
                'name' => '高港区',
            ),
            321281 =>
            array(
                'code' => 250806,
                'area_name' => '兴化市',
                'name' => '兴化市',
            ),
            321282 =>
            array(
                'code' => 250805,
                'area_name' => '靖江市',
                'name' => '靖江市',
            ),
            321283 =>
            array(
                'code' => 250803,
                'area_name' => '泰兴市',
                'name' => '泰兴市',
            ),
            321284 =>
            array(
                'code' => '',
                'area_name' => '姜堰区',
                'name' => '',
            ),
            321302 =>
            array(
                'code' => 250501,
                'area_name' => '宿城区',
                'name' => '宿城区',
            ),
            321311 =>
            array(
                'code' => '',
                'area_name' => '宿豫区',
                'name' => '',
            ),
            321322 =>
            array(
                'code' => 250503,
                'area_name' => '沭阳县',
                'name' => '沭阳县',
            ),
            321323 =>
            array(
                'code' => 250504,
                'area_name' => '泗阳县',
                'name' => '泗阳县',
            ),
            321324 =>
            array(
                'code' => 250505,
                'area_name' => '泗洪县',
                'name' => '泗洪县',
            ),
            330102 =>
            array(
                'code' => 260103,
                'area_name' => '上城区',
                'name' => '上城区',
            ),
            330103 =>
            array(
                'code' => 260104,
                'area_name' => '下城区',
                'name' => '下城区',
            ),
            330104 =>
            array(
                'code' => 260105,
                'area_name' => '江干区',
                'name' => '江干区',
            ),
            330105 =>
            array(
                'code' => 260101,
                'area_name' => '拱墅区',
                'name' => '拱墅区',
            ),
            330106 =>
            array(
                'code' => 260102,
                'area_name' => '西湖区',
                'name' => '西湖区',
            ),
            330108 =>
            array(
                'code' => 260106,
                'area_name' => '滨江区',
                'name' => '滨江区',
            ),
            330109 =>
            array(
                'code' => 260108,
                'area_name' => '萧山区',
                'name' => '萧山区',
            ),
            330110 =>
            array(
                'code' => 260107,
                'area_name' => '余杭区',
                'name' => '余杭区',
            ),
            330122 =>
            array(
                'code' => 260112,
                'area_name' => '桐庐县',
                'name' => '桐庐县',
            ),
            330127 =>
            array(
                'code' => 260113,
                'area_name' => '淳安县',
                'name' => '淳安县',
            ),
            330182 =>
            array(
                'code' => 260109,
                'area_name' => '建德市',
                'name' => '建德市',
            ),
            330183 =>
            array(
                'code' => 260110,
                'area_name' => '富阳市',
                'name' => '富阳市',
            ),
            330185 =>
            array(
                'code' => 260111,
                'area_name' => '临安市',
                'name' => '临安市',
            ),
            330203 =>
            array(
                'code' => 260201,
                'area_name' => '海曙区',
                'name' => '海曙区',
            ),
            330204 =>
            array(
                'code' => 260202,
                'area_name' => '江东区',
                'name' => '江东区',
            ),
            330205 =>
            array(
                'code' => 260203,
                'area_name' => '江北区',
                'name' => '江北区',
            ),
            330206 =>
            array(
                'code' => 260205,
                'area_name' => '北仑区',
                'name' => '北仑区',
            ),
            330211 =>
            array(
                'code' => 260204,
                'area_name' => '镇海区',
                'name' => '镇海区',
            ),
            330212 =>
            array(
                'code' => 260206,
                'area_name' => '鄞州区',
                'name' => '鄞州区',
            ),
            330225 =>
            array(
                'code' => 260211,
                'area_name' => '象山县',
                'name' => '象山县',
            ),
            330226 =>
            array(
                'code' => 260210,
                'area_name' => '宁海县',
                'name' => '宁海县',
            ),
            330281 =>
            array(
                'code' => 260207,
                'area_name' => '余姚市',
                'name' => '余姚市',
            ),
            330282 =>
            array(
                'code' => 260208,
                'area_name' => '慈溪市',
                'name' => '慈溪市',
            ),
            330283 =>
            array(
                'code' => 260209,
                'area_name' => '奉化市',
                'name' => '奉化市',
            ),
            330302 =>
            array(
                'code' => 260301,
                'area_name' => '鹿城区',
                'name' => '鹿城区',
            ),
            330303 =>
            array(
                'code' => 260302,
                'area_name' => '龙湾区',
                'name' => '龙湾区',
            ),
            330304 =>
            array(
                'code' => 260303,
                'area_name' => '瓯海区',
                'name' => '瓯海区',
            ),
            330322 =>
            array(
                'code' => 260307,
                'area_name' => '洞头县',
                'name' => '洞头县',
            ),
            330324 =>
            array(
                'code' => 260306,
                'area_name' => '永嘉县',
                'name' => '永嘉县',
            ),
            330326 =>
            array(
                'code' => 260308,
                'area_name' => '平阳县',
                'name' => '平阳县',
            ),
            330327 =>
            array(
                'code' => 260309,
                'area_name' => '苍南县',
                'name' => '苍南县',
            ),
            330328 =>
            array(
                'code' => 260310,
                'area_name' => '文成县',
                'name' => '文成县',
            ),
            330329 =>
            array(
                'code' => 260311,
                'area_name' => '泰顺县',
                'name' => '泰顺县',
            ),
            330381 =>
            array(
                'code' => 260304,
                'area_name' => '瑞安市',
                'name' => '瑞安市',
            ),
            330382 =>
            array(
                'code' => 260305,
                'area_name' => '乐清市',
                'name' => '乐清市',
            ),
            330402 =>
            array(
                'code' => '',
                'area_name' => '南湖区',
                'name' => '',
            ),
            330411 =>
            array(
                'code' => 260402,
                'area_name' => '秀洲区',
                'name' => '秀洲区',
            ),
            330421 =>
            array(
                'code' => 260406,
                'area_name' => '嘉善县',
                'name' => '嘉善县',
            ),
            330424 =>
            array(
                'code' => 260407,
                'area_name' => '海盐县',
                'name' => '海盐县',
            ),
            330481 =>
            array(
                'code' => 260403,
                'area_name' => '海宁市',
                'name' => '海宁市',
            ),
            330482 =>
            array(
                'code' => 260404,
                'area_name' => '平湖市',
                'name' => '平湖市',
            ),
            330483 =>
            array(
                'code' => 260405,
                'area_name' => '桐乡市',
                'name' => '桐乡市',
            ),
            330502 =>
            array(
                'code' => '',
                'area_name' => '吴兴区',
                'name' => '',
            ),
            330503 =>
            array(
                'code' => '',
                'area_name' => '南浔区',
                'name' => '',
            ),
            330521 =>
            array(
                'code' => 260502,
                'area_name' => '德清县',
                'name' => '德清县',
            ),
            330522 =>
            array(
                'code' => 260501,
                'area_name' => '长兴县',
                'name' => '长兴县',
            ),
            330523 =>
            array(
                'code' => 260503,
                'area_name' => '安吉县',
                'name' => '安吉县',
            ),
            330602 =>
            array(
                'code' => 260601,
                'area_name' => '越城区',
                'name' => '越城区',
            ),
            330621 =>
            array(
                'code' => 260605,
                'area_name' => '绍兴县',
                'name' => '绍兴县',
            ),
            330624 =>
            array(
                'code' => 260606,
                'area_name' => '新昌县',
                'name' => '新昌县',
            ),
            330681 =>
            array(
                'code' => 260602,
                'area_name' => '诸暨市',
                'name' => '诸暨市',
            ),
            330682 =>
            array(
                'code' => 260603,
                'area_name' => '上虞市',
                'name' => '上虞市',
            ),
            330683 =>
            array(
                'code' => 260604,
                'area_name' => '嵊州市',
                'name' => '嵊州市',
            ),
            330702 =>
            array(
                'code' => 260701,
                'area_name' => '婺城区',
                'name' => '婺城区',
            ),
            330703 =>
            array(
                'code' => 260702,
                'area_name' => '金东区',
                'name' => '金东区',
            ),
            330723 =>
            array(
                'code' => 260707,
                'area_name' => '武义县',
                'name' => '武义县',
            ),
            330726 =>
            array(
                'code' => 260708,
                'area_name' => '浦江县',
                'name' => '浦江县',
            ),
            330727 =>
            array(
                'code' => 260709,
                'area_name' => '磐安县',
                'name' => '磐安县',
            ),
            330781 =>
            array(
                'code' => 260703,
                'area_name' => '兰溪市',
                'name' => '兰溪市',
            ),
            330782 =>
            array(
                'code' => 260704,
                'area_name' => '义乌市',
                'name' => '义乌市',
            ),
            330783 =>
            array(
                'code' => 260705,
                'area_name' => '东阳市',
                'name' => '东阳市',
            ),
            330784 =>
            array(
                'code' => 260706,
                'area_name' => '永康市',
                'name' => '永康市',
            ),
            330802 =>
            array(
                'code' => 260801,
                'area_name' => '柯城区',
                'name' => '柯城区',
            ),
            330803 =>
            array(
                'code' => 260802,
                'area_name' => '衢江区',
                'name' => '衢江区',
            ),
            330822 =>
            array(
                'code' => 260805,
                'area_name' => '常山县',
                'name' => '常山县',
            ),
            330824 =>
            array(
                'code' => 260806,
                'area_name' => '开化县',
                'name' => '开化县',
            ),
            330825 =>
            array(
                'code' => 260804,
                'area_name' => '龙游县',
                'name' => '龙游县',
            ),
            330881 =>
            array(
                'code' => 260803,
                'area_name' => '江山市',
                'name' => '江山市',
            ),
            330902 =>
            array(
                'code' => 260901,
                'area_name' => '定海区',
                'name' => '定海区',
            ),
            330903 =>
            array(
                'code' => 260902,
                'area_name' => '普陀区',
                'name' => '普陀区',
            ),
            330921 =>
            array(
                'code' => 260903,
                'area_name' => '岱山县',
                'name' => '岱山县',
            ),
            330922 =>
            array(
                'code' => 260904,
                'area_name' => '嵊泗县',
                'name' => '嵊泗县',
            ),
            331002 =>
            array(
                'code' => 261001,
                'area_name' => '椒江区',
                'name' => '椒江区',
            ),
            331003 =>
            array(
                'code' => 261002,
                'area_name' => '黄岩区',
                'name' => '黄岩区',
            ),
            331004 =>
            array(
                'code' => 261003,
                'area_name' => '路桥区',
                'name' => '路桥区',
            ),
            331021 =>
            array(
                'code' => 261006,
                'area_name' => '玉环县',
                'name' => '玉环县',
            ),
            331022 =>
            array(
                'code' => 261009,
                'area_name' => '三门县',
                'name' => '三门县',
            ),
            331023 =>
            array(
                'code' => 261007,
                'area_name' => '天台县',
                'name' => '天台县',
            ),
            331024 =>
            array(
                'code' => 261008,
                'area_name' => '仙居县',
                'name' => '仙居县',
            ),
            331081 =>
            array(
                'code' => 261005,
                'area_name' => '温岭市',
                'name' => '温岭市',
            ),
            331082 =>
            array(
                'code' => 261004,
                'area_name' => '临海市',
                'name' => '临海市',
            ),
            331102 =>
            array(
                'code' => 261101,
                'area_name' => '莲都区',
                'name' => '莲都区',
            ),
            331121 =>
            array(
                'code' => 261104,
                'area_name' => '青田县',
                'name' => '青田县',
            ),
            331122 =>
            array(
                'code' => 261103,
                'area_name' => '缙云县',
                'name' => '缙云县',
            ),
            331123 =>
            array(
                'code' => 261106,
                'area_name' => '遂昌县',
                'name' => '遂昌县',
            ),
            331124 =>
            array(
                'code' => 261107,
                'area_name' => '松阳县',
                'name' => '松阳县',
            ),
            331125 =>
            array(
                'code' => 261105,
                'area_name' => '云和县',
                'name' => '云和县',
            ),
            331126 =>
            array(
                'code' => 261108,
                'area_name' => '庆元县',
                'name' => '庆元县',
            ),
            331127 =>
            array(
                'code' => 261109,
                'area_name' => '景宁畲族自治县',
                'name' => '景宁畲族自治县',
            ),
            331181 =>
            array(
                'code' => 261102,
                'area_name' => '龙泉市',
                'name' => '龙泉市',
            ),
            340102 =>
            array(
                'code' => 270102,
                'area_name' => '瑶海区',
                'name' => '瑶海区',
            ),
            340103 =>
            array(
                'code' => 270101,
                'area_name' => '庐阳区',
                'name' => '庐阳区',
            ),
            340104 =>
            array(
                'code' => 270103,
                'area_name' => '蜀山区',
                'name' => '蜀山区',
            ),
            340111 =>
            array(
                'code' => 270104,
                'area_name' => '包河区',
                'name' => '包河区',
            ),
            340121 =>
            array(
                'code' => 270105,
                'area_name' => '长丰县',
                'name' => '长丰县',
            ),
            340122 =>
            array(
                'code' => 270106,
                'area_name' => '肥东县',
                'name' => '肥东县',
            ),
            340123 =>
            array(
                'code' => 270107,
                'area_name' => '肥西县',
                'name' => '肥西县',
            ),
            340202 =>
            array(
                'code' => 270201,
                'area_name' => '镜湖区',
                'name' => '镜湖区',
            ),
            340203 =>
            array(
                'code' => '',
                'area_name' => '弋江区',
                'name' => '',
            ),
            340207 =>
            array(
                'code' => 270204,
                'area_name' => '鸠江区',
                'name' => '鸠江区',
            ),
            340208 =>
            array(
                'code' => '',
                'area_name' => '三山区',
                'name' => '',
            ),
            340221 =>
            array(
                'code' => 270205,
                'area_name' => '芜湖县',
                'name' => '芜湖县',
            ),
            340222 =>
            array(
                'code' => 270207,
                'area_name' => '繁昌县',
                'name' => '繁昌县',
            ),
            340223 =>
            array(
                'code' => 270206,
                'area_name' => '南陵县',
                'name' => '南陵县',
            ),
            340302 =>
            array(
                'code' => '',
                'area_name' => '龙子湖区',
                'name' => '',
            ),
            340303 =>
            array(
                'code' => '',
                'area_name' => '蚌山区',
                'name' => '',
            ),
            340304 =>
            array(
                'code' => '',
                'area_name' => '禹会区',
                'name' => '',
            ),
            340311 =>
            array(
                'code' => '',
                'area_name' => '淮上区',
                'name' => '',
            ),
            340321 =>
            array(
                'code' => 270305,
                'area_name' => '怀远县',
                'name' => '怀远县',
            ),
            340322 =>
            array(
                'code' => 270307,
                'area_name' => '五河县',
                'name' => '五河县',
            ),
            340323 =>
            array(
                'code' => 270306,
                'area_name' => '固镇县',
                'name' => '固镇县',
            ),
            340402 =>
            array(
                'code' => 270402,
                'area_name' => '大通区',
                'name' => '大通区',
            ),
            340403 =>
            array(
                'code' => 270401,
                'area_name' => '田家庵区',
                'name' => '田家庵区',
            ),
            340404 =>
            array(
                'code' => 270403,
                'area_name' => '谢家集区',
                'name' => '谢家集区',
            ),
            340405 =>
            array(
                'code' => 270404,
                'area_name' => '八公山区',
                'name' => '八公山区',
            ),
            340406 =>
            array(
                'code' => 270405,
                'area_name' => '潘集区',
                'name' => '潘集区',
            ),
            340421 =>
            array(
                'code' => 270406,
                'area_name' => '凤台县',
                'name' => '凤台县',
            ),
            340503 =>
            array(
                'code' => 270502,
                'area_name' => '花山区',
                'name' => '花山区',
            ),
            340504 =>
            array(
                'code' => 270501,
                'area_name' => '雨山区',
                'name' => '雨山区',
            ),
            340506 =>
            array(
                'code' => '',
                'area_name' => '博望区',
                'name' => '',
            ),
            340521 =>
            array(
                'code' => 270504,
                'area_name' => '当涂县',
                'name' => '当涂县',
            ),
            340602 =>
            array(
                'code' => 270602,
                'area_name' => '杜集区',
                'name' => '杜集区',
            ),
            340603 =>
            array(
                'code' => 270601,
                'area_name' => '相山区',
                'name' => '相山区',
            ),
            340604 =>
            array(
                'code' => 270603,
                'area_name' => '烈山区',
                'name' => '烈山区',
            ),
            340621 =>
            array(
                'code' => 270604,
                'area_name' => '濉溪县',
                'name' => '濉溪县',
            ),
            340702 =>
            array(
                'code' => 270701,
                'area_name' => '铜官山区',
                'name' => '铜官山区',
            ),
            340703 =>
            array(
                'code' => 270702,
                'area_name' => '狮子山区',
                'name' => '狮子山区',
            ),
            340711 =>
            array(
                'code' => '',
                'area_name' => '郊区',
                'name' => '',
            ),
            340721 =>
            array(
                'code' => 270704,
                'area_name' => '铜陵县',
                'name' => '铜陵县',
            ),
            340802 =>
            array(
                'code' => 270801,
                'area_name' => '迎江区',
                'name' => '迎江区',
            ),
            340803 =>
            array(
                'code' => 270802,
                'area_name' => '大观区',
                'name' => '大观区',
            ),
            340811 =>
            array(
                'code' => '',
                'area_name' => '宜秀区',
                'name' => '',
            ),
            340822 =>
            array(
                'code' => 270808,
                'area_name' => '怀宁县',
                'name' => '怀宁县',
            ),
            340823 =>
            array(
                'code' => 270806,
                'area_name' => '枞阳县',
                'name' => '枞阳县',
            ),
            340824 =>
            array(
                'code' => 270811,
                'area_name' => '潜山县',
                'name' => '潜山县',
            ),
            340825 =>
            array(
                'code' => 270807,
                'area_name' => '太湖县',
                'name' => '太湖县',
            ),
            340826 =>
            array(
                'code' => 270805,
                'area_name' => '宿松县',
                'name' => '宿松县',
            ),
            340827 =>
            array(
                'code' => 270810,
                'area_name' => '望江县',
                'name' => '望江县',
            ),
            340828 =>
            array(
                'code' => 270809,
                'area_name' => '岳西县',
                'name' => '岳西县',
            ),
            340881 =>
            array(
                'code' => 270804,
                'area_name' => '桐城市',
                'name' => '桐城市',
            ),
            341002 =>
            array(
                'code' => 270901,
                'area_name' => '屯溪区',
                'name' => '屯溪区',
            ),
            341003 =>
            array(
                'code' => 270902,
                'area_name' => '黄山区',
                'name' => '黄山区',
            ),
            341004 =>
            array(
                'code' => 270903,
                'area_name' => '徽州区',
                'name' => '徽州区',
            ),
            341021 =>
            array(
                'code' => '',
                'area_name' => '歙县',
                'name' => '',
            ),
            341022 =>
            array(
                'code' => 270904,
                'area_name' => '休宁县',
                'name' => '休宁县',
            ),
            341023 =>
            array(
                'code' => '',
                'area_name' => '黟县',
                'name' => '',
            ),
            341024 =>
            array(
                'code' => 270906,
                'area_name' => '祁门县',
                'name' => '祁门县',
            ),
            341102 =>
            array(
                'code' => 271001,
                'area_name' => '琅琊区',
                'name' => '琅琊区',
            ),
            341103 =>
            array(
                'code' => 271002,
                'area_name' => '南谯区',
                'name' => '南谯区',
            ),
            341122 =>
            array(
                'code' => 271006,
                'area_name' => '来安县',
                'name' => '来安县',
            ),
            341124 =>
            array(
                'code' => 271005,
                'area_name' => '全椒县',
                'name' => '全椒县',
            ),
            341125 =>
            array(
                'code' => 271007,
                'area_name' => '定远县',
                'name' => '定远县',
            ),
            341126 =>
            array(
                'code' => 271008,
                'area_name' => '凤阳县',
                'name' => '凤阳县',
            ),
            341181 =>
            array(
                'code' => 271003,
                'area_name' => '天长市',
                'name' => '天长市',
            ),
            341182 =>
            array(
                'code' => 271004,
                'area_name' => '明光市',
                'name' => '明光市',
            ),
            341202 =>
            array(
                'code' => '',
                'area_name' => '颍州区',
                'name' => '',
            ),
            341203 =>
            array(
                'code' => '',
                'area_name' => '颍东区',
                'name' => '',
            ),
            341204 =>
            array(
                'code' => '',
                'area_name' => '颍泉区',
                'name' => '',
            ),
            341221 =>
            array(
                'code' => 271105,
                'area_name' => '临泉县',
                'name' => '临泉县',
            ),
            341222 =>
            array(
                'code' => 271108,
                'area_name' => '太和县',
                'name' => '太和县',
            ),
            341225 =>
            array(
                'code' => 271107,
                'area_name' => '阜南县',
                'name' => '阜南县',
            ),
            341226 =>
            array(
                'code' => '',
                'area_name' => '颍上县',
                'name' => '',
            ),
            341282 =>
            array(
                'code' => 271104,
                'area_name' => '界首市',
                'name' => '界首市',
            ),
            341302 =>
            array(
                'code' => 271201,
                'area_name' => '埇桥区',
                'name' => '埇桥区',
            ),
            341321 =>
            array(
                'code' => 271204,
                'area_name' => '砀山县',
                'name' => '砀山县',
            ),
            341322 =>
            array(
                'code' => '',
                'area_name' => '萧县',
                'name' => '',
            ),
            341323 =>
            array(
                'code' => 271205,
                'area_name' => '灵璧县',
                'name' => '灵璧县',
            ),
            341324 =>
            array(
                'code' => '',
                'area_name' => '泗县',
                'name' => '',
            ),
            341400 =>
            array(
                'code' => '',
                'area_name' => '巢湖市',
                'name' => '',
            ),
            341421 =>
            array(
                'code' => '',
                'area_name' => '庐江县',
                'name' => '',
            ),
            341422 =>
            array(
                'code' => '',
                'area_name' => '无为县',
                'name' => '',
            ),
            341423 =>
            array(
                'code' => '',
                'area_name' => '含山县',
                'name' => '',
            ),
            341424 =>
            array(
                'code' => '',
                'area_name' => '和县',
                'name' => '',
            ),
            341502 =>
            array(
                'code' => 271401,
                'area_name' => '金安区',
                'name' => '金安区',
            ),
            341503 =>
            array(
                'code' => 271402,
                'area_name' => '裕安区',
                'name' => '裕安区',
            ),
            341521 =>
            array(
                'code' => '',
                'area_name' => '寿县',
                'name' => '',
            ),
            341522 =>
            array(
                'code' => 271405,
                'area_name' => '霍邱县',
                'name' => '霍邱县',
            ),
            341523 =>
            array(
                'code' => 271406,
                'area_name' => '舒城县',
                'name' => '舒城县',
            ),
            341524 =>
            array(
                'code' => 271407,
                'area_name' => '金寨县',
                'name' => '金寨县',
            ),
            341525 =>
            array(
                'code' => 271404,
                'area_name' => '霍山县',
                'name' => '霍山县',
            ),
            341602 =>
            array(
                'code' => 271501,
                'area_name' => '谯城区',
                'name' => '谯城区',
            ),
            341621 =>
            array(
                'code' => 271503,
                'area_name' => '涡阳县',
                'name' => '涡阳县',
            ),
            341622 =>
            array(
                'code' => 271504,
                'area_name' => '蒙城县',
                'name' => '蒙城县',
            ),
            341623 =>
            array(
                'code' => 271502,
                'area_name' => '利辛县',
                'name' => '利辛县',
            ),
            341702 =>
            array(
                'code' => 271701,
                'area_name' => '贵池区',
                'name' => '贵池区',
            ),
            341721 =>
            array(
                'code' => 271702,
                'area_name' => '东至县',
                'name' => '东至县',
            ),
            341722 =>
            array(
                'code' => 271703,
                'area_name' => '石台县',
                'name' => '石台县',
            ),
            341723 =>
            array(
                'code' => 271704,
                'area_name' => '青阳县',
                'name' => '青阳县',
            ),
            341802 =>
            array(
                'code' => 271601,
                'area_name' => '宣州区',
                'name' => '宣州区',
            ),
            341821 =>
            array(
                'code' => 271604,
                'area_name' => '郎溪县',
                'name' => '郎溪县',
            ),
            341822 =>
            array(
                'code' => 271603,
                'area_name' => '广德县',
                'name' => '广德县',
            ),
            341823 =>
            array(
                'code' => '',
                'area_name' => '泾县',
                'name' => '',
            ),
            341824 =>
            array(
                'code' => 271607,
                'area_name' => '绩溪县',
                'name' => '绩溪县',
            ),
            341825 =>
            array(
                'code' => 271606,
                'area_name' => '旌德县',
                'name' => '旌德县',
            ),
            341881 =>
            array(
                'code' => 271602,
                'area_name' => '宁国市',
                'name' => '宁国市',
            ),
            350102 =>
            array(
                'code' => 280101,
                'area_name' => '鼓楼区',
                'name' => '鼓楼区',
            ),
            350103 =>
            array(
                'code' => 280102,
                'area_name' => '台江区',
                'name' => '台江区',
            ),
            350104 =>
            array(
                'code' => 280103,
                'area_name' => '仓山区',
                'name' => '仓山区',
            ),
            350105 =>
            array(
                'code' => 280104,
                'area_name' => '马尾区',
                'name' => '马尾区',
            ),
            350111 =>
            array(
                'code' => 280105,
                'area_name' => '晋安区',
                'name' => '晋安区',
            ),
            350121 =>
            array(
                'code' => 280108,
                'area_name' => '闽侯县',
                'name' => '闽侯县',
            ),
            350122 =>
            array(
                'code' => 280111,
                'area_name' => '连江县',
                'name' => '连江县',
            ),
            350123 =>
            array(
                'code' => 280112,
                'area_name' => '罗源县',
                'name' => '罗源县',
            ),
            350124 =>
            array(
                'code' => 280109,
                'area_name' => '闽清县',
                'name' => '闽清县',
            ),
            350125 =>
            array(
                'code' => 280110,
                'area_name' => '永泰县',
                'name' => '永泰县',
            ),
            350128 =>
            array(
                'code' => 280113,
                'area_name' => '平潭县',
                'name' => '平潭县',
            ),
            350181 =>
            array(
                'code' => 280106,
                'area_name' => '福清市',
                'name' => '福清市',
            ),
            350182 =>
            array(
                'code' => 280107,
                'area_name' => '长乐市',
                'name' => '长乐市',
            ),
            350203 =>
            array(
                'code' => 280202,
                'area_name' => '思明区',
                'name' => '思明区',
            ),
            350205 =>
            array(
                'code' => '',
                'area_name' => '海沧区',
                'name' => '',
            ),
            350206 =>
            array(
                'code' => 280205,
                'area_name' => '湖里区',
                'name' => '湖里区',
            ),
            350211 =>
            array(
                'code' => 280206,
                'area_name' => '集美区',
                'name' => '集美区',
            ),
            350212 =>
            array(
                'code' => 280207,
                'area_name' => '同安区',
                'name' => '同安区',
            ),
            350213 =>
            array(
                'code' => '',
                'area_name' => '翔安区',
                'name' => '',
            ),
            350302 =>
            array(
                'code' => 280401,
                'area_name' => '城厢区',
                'name' => '城厢区',
            ),
            350303 =>
            array(
                'code' => 280402,
                'area_name' => '涵江区',
                'name' => '涵江区',
            ),
            350304 =>
            array(
                'code' => 280403,
                'area_name' => '荔城区',
                'name' => '荔城区',
            ),
            350305 =>
            array(
                'code' => 280404,
                'area_name' => '秀屿区',
                'name' => '秀屿区',
            ),
            350322 =>
            array(
                'code' => 280405,
                'area_name' => '仙游县',
                'name' => '仙游县',
            ),
            350402 =>
            array(
                'code' => 280301,
                'area_name' => '梅列区',
                'name' => '梅列区',
            ),
            350403 =>
            array(
                'code' => 280302,
                'area_name' => '三元区',
                'name' => '三元区',
            ),
            350421 =>
            array(
                'code' => 280304,
                'area_name' => '明溪县',
                'name' => '明溪县',
            ),
            350423 =>
            array(
                'code' => 280311,
                'area_name' => '清流县',
                'name' => '清流县',
            ),
            350424 =>
            array(
                'code' => 280307,
                'area_name' => '宁化县',
                'name' => '宁化县',
            ),
            350425 =>
            array(
                'code' => 280306,
                'area_name' => '大田县',
                'name' => '大田县',
            ),
            350426 =>
            array(
                'code' => 280310,
                'area_name' => '尤溪县',
                'name' => '尤溪县',
            ),
            350427 =>
            array(
                'code' => '',
                'area_name' => '沙县',
                'name' => '',
            ),
            350428 =>
            array(
                'code' => 280305,
                'area_name' => '将乐县',
                'name' => '将乐县',
            ),
            350429 =>
            array(
                'code' => 280312,
                'area_name' => '泰宁县',
                'name' => '泰宁县',
            ),
            350430 =>
            array(
                'code' => 280308,
                'area_name' => '建宁县',
                'name' => '建宁县',
            ),
            350481 =>
            array(
                'code' => 280303,
                'area_name' => '永安市',
                'name' => '永安市',
            ),
            350502 =>
            array(
                'code' => 280501,
                'area_name' => '鲤城区',
                'name' => '鲤城区',
            ),
            350503 =>
            array(
                'code' => 280502,
                'area_name' => '丰泽区',
                'name' => '丰泽区',
            ),
            350504 =>
            array(
                'code' => 280503,
                'area_name' => '洛江区',
                'name' => '洛江区',
            ),
            350505 =>
            array(
                'code' => 280504,
                'area_name' => '泉港区',
                'name' => '泉港区',
            ),
            350521 =>
            array(
                'code' => 280508,
                'area_name' => '惠安县',
                'name' => '惠安县',
            ),
            350524 =>
            array(
                'code' => 280510,
                'area_name' => '安溪县',
                'name' => '安溪县',
            ),
            350525 =>
            array(
                'code' => 280509,
                'area_name' => '永春县',
                'name' => '永春县',
            ),
            350526 =>
            array(
                'code' => 280511,
                'area_name' => '德化县',
                'name' => '德化县',
            ),
            350527 =>
            array(
                'code' => 280512,
                'area_name' => '金门县',
                'name' => '金门县',
            ),
            350581 =>
            array(
                'code' => 280505,
                'area_name' => '石狮市',
                'name' => '石狮市',
            ),
            350582 =>
            array(
                'code' => 280506,
                'area_name' => '晋江市',
                'name' => '晋江市',
            ),
            350583 =>
            array(
                'code' => 280507,
                'area_name' => '南安市',
                'name' => '南安市',
            ),
            350602 =>
            array(
                'code' => 280601,
                'area_name' => '芗城区',
                'name' => '芗城区',
            ),
            350603 =>
            array(
                'code' => 280602,
                'area_name' => '龙文区',
                'name' => '龙文区',
            ),
            350622 =>
            array(
                'code' => 280611,
                'area_name' => '云霄县',
                'name' => '云霄县',
            ),
            350623 =>
            array(
                'code' => 280607,
                'area_name' => '漳浦县',
                'name' => '漳浦县',
            ),
            350624 =>
            array(
                'code' => 280606,
                'area_name' => '诏安县',
                'name' => '诏安县',
            ),
            350625 =>
            array(
                'code' => 280610,
                'area_name' => '长泰县',
                'name' => '长泰县',
            ),
            350626 =>
            array(
                'code' => 280609,
                'area_name' => '东山县',
                'name' => '东山县',
            ),
            350627 =>
            array(
                'code' => 280605,
                'area_name' => '南靖县',
                'name' => '南靖县',
            ),
            350628 =>
            array(
                'code' => 280604,
                'area_name' => '平和县',
                'name' => '平和县',
            ),
            350629 =>
            array(
                'code' => 280608,
                'area_name' => '华安县',
                'name' => '华安县',
            ),
            350681 =>
            array(
                'code' => 280603,
                'area_name' => '龙海市',
                'name' => '龙海市',
            ),
            350702 =>
            array(
                'code' => 280701,
                'area_name' => '延平区',
                'name' => '延平区',
            ),
            350721 =>
            array(
                'code' => 280708,
                'area_name' => '顺昌县',
                'name' => '顺昌县',
            ),
            350722 =>
            array(
                'code' => 280709,
                'area_name' => '浦城县',
                'name' => '浦城县',
            ),
            350723 =>
            array(
                'code' => 280707,
                'area_name' => '光泽县',
                'name' => '光泽县',
            ),
            350724 =>
            array(
                'code' => 280706,
                'area_name' => '松溪县',
                'name' => '松溪县',
            ),
            350725 =>
            array(
                'code' => 280710,
                'area_name' => '政和县',
                'name' => '政和县',
            ),
            350781 =>
            array(
                'code' => 280703,
                'area_name' => '邵武市',
                'name' => '邵武市',
            ),
            350782 =>
            array(
                'code' => 280704,
                'area_name' => '武夷山市',
                'name' => '武夷山市',
            ),
            350783 =>
            array(
                'code' => 280702,
                'area_name' => '建瓯市',
                'name' => '建瓯市',
            ),
            350784 =>
            array(
                'code' => 280705,
                'area_name' => '建阳市',
                'name' => '建阳市',
            ),
            350802 =>
            array(
                'code' => 280801,
                'area_name' => '新罗区',
                'name' => '新罗区',
            ),
            350821 =>
            array(
                'code' => 280803,
                'area_name' => '长汀县',
                'name' => '长汀县',
            ),
            350822 =>
            array(
                'code' => 280806,
                'area_name' => '永定县',
                'name' => '永定县',
            ),
            350823 =>
            array(
                'code' => 280805,
                'area_name' => '上杭县',
                'name' => '上杭县',
            ),
            350824 =>
            array(
                'code' => 280804,
                'area_name' => '武平县',
                'name' => '武平县',
            ),
            350825 =>
            array(
                'code' => 280807,
                'area_name' => '连城县',
                'name' => '连城县',
            ),
            350881 =>
            array(
                'code' => 280802,
                'area_name' => '漳平市',
                'name' => '漳平市',
            ),
            350902 =>
            array(
                'code' => 280901,
                'area_name' => '蕉城区',
                'name' => '蕉城区',
            ),
            350921 =>
            array(
                'code' => 280905,
                'area_name' => '霞浦县',
                'name' => '霞浦县',
            ),
            350922 =>
            array(
                'code' => 280908,
                'area_name' => '古田县',
                'name' => '古田县',
            ),
            350923 =>
            array(
                'code' => 280907,
                'area_name' => '屏南县',
                'name' => '屏南县',
            ),
            350924 =>
            array(
                'code' => 280904,
                'area_name' => '寿宁县',
                'name' => '寿宁县',
            ),
            350925 =>
            array(
                'code' => 280909,
                'area_name' => '周宁县',
                'name' => '周宁县',
            ),
            350926 =>
            array(
                'code' => 280906,
                'area_name' => '柘荣县',
                'name' => '柘荣县',
            ),
            350981 =>
            array(
                'code' => 280902,
                'area_name' => '福安市',
                'name' => '福安市',
            ),
            350982 =>
            array(
                'code' => 280903,
                'area_name' => '福鼎市',
                'name' => '福鼎市',
            ),
            360102 =>
            array(
                'code' => 290101,
                'area_name' => '东湖区',
                'name' => '东湖区',
            ),
            360103 =>
            array(
                'code' => 290102,
                'area_name' => '西湖区',
                'name' => '西湖区',
            ),
            360104 =>
            array(
                'code' => 290103,
                'area_name' => '青云谱区',
                'name' => '青云谱区',
            ),
            360105 =>
            array(
                'code' => 290104,
                'area_name' => '湾里区',
                'name' => '湾里区',
            ),
            360111 =>
            array(
                'code' => 290105,
                'area_name' => '青山湖区',
                'name' => '青山湖区',
            ),
            360121 =>
            array(
                'code' => 290107,
                'area_name' => '南昌县',
                'name' => '南昌县',
            ),
            360122 =>
            array(
                'code' => 290106,
                'area_name' => '新建县',
                'name' => '新建县',
            ),
            360123 =>
            array(
                'code' => 290109,
                'area_name' => '安义县',
                'name' => '安义县',
            ),
            360124 =>
            array(
                'code' => 290108,
                'area_name' => '进贤县',
                'name' => '进贤县',
            ),
            360202 =>
            array(
                'code' => 290202,
                'area_name' => '昌江区',
                'name' => '昌江区',
            ),
            360203 =>
            array(
                'code' => 290201,
                'area_name' => '珠山区',
                'name' => '珠山区',
            ),
            360222 =>
            array(
                'code' => 290204,
                'area_name' => '浮梁县',
                'name' => '浮梁县',
            ),
            360281 =>
            array(
                'code' => 290203,
                'area_name' => '乐平市',
                'name' => '乐平市',
            ),
            360302 =>
            array(
                'code' => 290301,
                'area_name' => '安源区',
                'name' => '安源区',
            ),
            360313 =>
            array(
                'code' => 290302,
                'area_name' => '湘东区',
                'name' => '湘东区',
            ),
            360321 =>
            array(
                'code' => 290303,
                'area_name' => '莲花县',
                'name' => '莲花县',
            ),
            360322 =>
            array(
                'code' => 290304,
                'area_name' => '上栗县',
                'name' => '上栗县',
            ),
            360323 =>
            array(
                'code' => 290305,
                'area_name' => '芦溪县',
                'name' => '芦溪县',
            ),
            360402 =>
            array(
                'code' => 290502,
                'area_name' => '庐山区',
                'name' => '庐山区',
            ),
            360403 =>
            array(
                'code' => 290501,
                'area_name' => '浔阳区',
                'name' => '浔阳区',
            ),
            360421 =>
            array(
                'code' => 290504,
                'area_name' => '九江县',
                'name' => '九江县',
            ),
            360423 =>
            array(
                'code' => 290506,
                'area_name' => '武宁县',
                'name' => '武宁县',
            ),
            360424 =>
            array(
                'code' => 290509,
                'area_name' => '修水县',
                'name' => '修水县',
            ),
            360425 =>
            array(
                'code' => 290508,
                'area_name' => '永修县',
                'name' => '永修县',
            ),
            360426 =>
            array(
                'code' => 290511,
                'area_name' => '德安县',
                'name' => '德安县',
            ),
            360427 =>
            array(
                'code' => 290505,
                'area_name' => '星子县',
                'name' => '星子县',
            ),
            360428 =>
            array(
                'code' => 290512,
                'area_name' => '都昌县',
                'name' => '都昌县',
            ),
            360429 =>
            array(
                'code' => 290510,
                'area_name' => '湖口县',
                'name' => '湖口县',
            ),
            360430 =>
            array(
                'code' => 290507,
                'area_name' => '彭泽县',
                'name' => '彭泽县',
            ),
            360481 =>
            array(
                'code' => 290503,
                'area_name' => '瑞昌市',
                'name' => '瑞昌市',
            ),
            360483 =>
            array(
                'code' => '',
                'area_name' => '共青城市',
                'name' => '',
            ),
            360502 =>
            array(
                'code' => 290401,
                'area_name' => '渝水区',
                'name' => '渝水区',
            ),
            360521 =>
            array(
                'code' => 290402,
                'area_name' => '分宜县',
                'name' => '分宜县',
            ),
            360602 =>
            array(
                'code' => 290601,
                'area_name' => '月湖区',
                'name' => '月湖区',
            ),
            360622 =>
            array(
                'code' => 290603,
                'area_name' => '余江县',
                'name' => '余江县',
            ),
            360681 =>
            array(
                'code' => 290602,
                'area_name' => '贵溪市',
                'name' => '贵溪市',
            ),
            360702 =>
            array(
                'code' => 290701,
                'area_name' => '章贡区',
                'name' => '章贡区',
            ),
            360721 =>
            array(
                'code' => '',
                'area_name' => '赣县',
                'name' => '',
            ),
            360722 =>
            array(
                'code' => 290715,
                'area_name' => '信丰县',
                'name' => '信丰县',
            ),
            360723 =>
            array(
                'code' => 290717,
                'area_name' => '大余县',
                'name' => '大余县',
            ),
            360724 =>
            array(
                'code' => 290711,
                'area_name' => '上犹县',
                'name' => '上犹县',
            ),
            360725 =>
            array(
                'code' => 290714,
                'area_name' => '崇义县',
                'name' => '崇义县',
            ),
            360726 =>
            array(
                'code' => 290705,
                'area_name' => '安远县',
                'name' => '安远县',
            ),
            360727 =>
            array(
                'code' => 290713,
                'area_name' => '龙南县',
                'name' => '龙南县',
            ),
            360728 =>
            array(
                'code' => 290710,
                'area_name' => '定南县',
                'name' => '定南县',
            ),
            360729 =>
            array(
                'code' => 290716,
                'area_name' => '全南县',
                'name' => '全南县',
            ),
            360730 =>
            array(
                'code' => 290707,
                'area_name' => '宁都县',
                'name' => '宁都县',
            ),
            360731 =>
            array(
                'code' => 290712,
                'area_name' => '于都县',
                'name' => '于都县',
            ),
            360732 =>
            array(
                'code' => 290709,
                'area_name' => '兴国县',
                'name' => '兴国县',
            ),
            360733 =>
            array(
                'code' => 290718,
                'area_name' => '会昌县',
                'name' => '会昌县',
            ),
            360734 =>
            array(
                'code' => 290708,
                'area_name' => '寻乌县',
                'name' => '寻乌县',
            ),
            360735 =>
            array(
                'code' => 290704,
                'area_name' => '石城县',
                'name' => '石城县',
            ),
            360781 =>
            array(
                'code' => 290702,
                'area_name' => '瑞金市',
                'name' => '瑞金市',
            ),
            360782 =>
            array(
                'code' => 290703,
                'area_name' => '南康市',
                'name' => '南康市',
            ),
            360802 =>
            array(
                'code' => 290801,
                'area_name' => '吉州区',
                'name' => '吉州区',
            ),
            360803 =>
            array(
                'code' => 290802,
                'area_name' => '青原区',
                'name' => '青原区',
            ),
            360821 =>
            array(
                'code' => 290804,
                'area_name' => '吉安县',
                'name' => '吉安县',
            ),
            360822 =>
            array(
                'code' => 290812,
                'area_name' => '吉水县',
                'name' => '吉水县',
            ),
            360823 =>
            array(
                'code' => 290809,
                'area_name' => '峡江县',
                'name' => '峡江县',
            ),
            360824 =>
            array(
                'code' => 290807,
                'area_name' => '新干县',
                'name' => '新干县',
            ),
            360825 =>
            array(
                'code' => 290805,
                'area_name' => '永丰县',
                'name' => '永丰县',
            ),
            360826 =>
            array(
                'code' => 290808,
                'area_name' => '泰和县',
                'name' => '泰和县',
            ),
            360827 =>
            array(
                'code' => 290810,
                'area_name' => '遂川县',
                'name' => '遂川县',
            ),
            360828 =>
            array(
                'code' => 290813,
                'area_name' => '万安县',
                'name' => '万安县',
            ),
            360829 =>
            array(
                'code' => 290811,
                'area_name' => '安福县',
                'name' => '安福县',
            ),
            360830 =>
            array(
                'code' => 290806,
                'area_name' => '永新县',
                'name' => '永新县',
            ),
            360881 =>
            array(
                'code' => 290803,
                'area_name' => '井冈山市',
                'name' => '井冈山市',
            ),
            360902 =>
            array(
                'code' => 290901,
                'area_name' => '袁州区',
                'name' => '袁州区',
            ),
            360921 =>
            array(
                'code' => 290908,
                'area_name' => '奉新县',
                'name' => '奉新县',
            ),
            360922 =>
            array(
                'code' => 290909,
                'area_name' => '万载县',
                'name' => '万载县',
            ),
            360923 =>
            array(
                'code' => 290910,
                'area_name' => '上高县',
                'name' => '上高县',
            ),
            360924 =>
            array(
                'code' => 290907,
                'area_name' => '宜丰县',
                'name' => '宜丰县',
            ),
            360925 =>
            array(
                'code' => 290906,
                'area_name' => '靖安县',
                'name' => '靖安县',
            ),
            360926 =>
            array(
                'code' => 290905,
                'area_name' => '铜鼓县',
                'name' => '铜鼓县',
            ),
            360981 =>
            array(
                'code' => 290902,
                'area_name' => '丰城市',
                'name' => '丰城市',
            ),
            360982 =>
            array(
                'code' => 290903,
                'area_name' => '樟树市',
                'name' => '樟树市',
            ),
            360983 =>
            array(
                'code' => 290904,
                'area_name' => '高安市',
                'name' => '高安市',
            ),
            361002 =>
            array(
                'code' => 291001,
                'area_name' => '临川区',
                'name' => '临川区',
            ),
            361021 =>
            array(
                'code' => 291005,
                'area_name' => '南城县',
                'name' => '南城县',
            ),
            361022 =>
            array(
                'code' => 291010,
                'area_name' => '黎川县',
                'name' => '黎川县',
            ),
            361023 =>
            array(
                'code' => 291002,
                'area_name' => '南丰县',
                'name' => '南丰县',
            ),
            361024 =>
            array(
                'code' => 291011,
                'area_name' => '崇仁县',
                'name' => '崇仁县',
            ),
            361025 =>
            array(
                'code' => 291003,
                'area_name' => '乐安县',
                'name' => '乐安县',
            ),
            361026 =>
            array(
                'code' => 291008,
                'area_name' => '宜黄县',
                'name' => '宜黄县',
            ),
            361027 =>
            array(
                'code' => 291004,
                'area_name' => '金溪县',
                'name' => '金溪县',
            ),
            361028 =>
            array(
                'code' => 291007,
                'area_name' => '资溪县',
                'name' => '资溪县',
            ),
            361029 =>
            array(
                'code' => 291006,
                'area_name' => '东乡县',
                'name' => '东乡县',
            ),
            361030 =>
            array(
                'code' => 291009,
                'area_name' => '广昌县',
                'name' => '广昌县',
            ),
            361102 =>
            array(
                'code' => 291101,
                'area_name' => '信州区',
                'name' => '信州区',
            ),
            361121 =>
            array(
                'code' => 291103,
                'area_name' => '上饶县',
                'name' => '上饶县',
            ),
            361122 =>
            array(
                'code' => 291104,
                'area_name' => '广丰县',
                'name' => '广丰县',
            ),
            361123 =>
            array(
                'code' => 291111,
                'area_name' => '玉山县',
                'name' => '玉山县',
            ),
            361124 =>
            array(
                'code' => 291107,
                'area_name' => '铅山县',
                'name' => '铅山县',
            ),
            361125 =>
            array(
                'code' => 291109,
                'area_name' => '横峰县',
                'name' => '横峰县',
            ),
            361126 =>
            array(
                'code' => 291110,
                'area_name' => '弋阳县',
                'name' => '弋阳县',
            ),
            361127 =>
            array(
                'code' => 291108,
                'area_name' => '余干县',
                'name' => '余干县',
            ),
            361128 =>
            array(
                'code' => 291105,
                'area_name' => '鄱阳县',
                'name' => '鄱阳县',
            ),
            361129 =>
            array(
                'code' => 291112,
                'area_name' => '万年县',
                'name' => '万年县',
            ),
            361130 =>
            array(
                'code' => 291106,
                'area_name' => '婺源县',
                'name' => '婺源县',
            ),
            361181 =>
            array(
                'code' => 291102,
                'area_name' => '德兴市',
                'name' => '德兴市',
            ),
            370102 =>
            array(
                'code' => 300102,
                'area_name' => '历下区',
                'name' => '历下区',
            ),
            370103 =>
            array(
                'code' => 300101,
                'area_name' => '市中区',
                'name' => '市中区',
            ),
            370104 =>
            array(
                'code' => 300104,
                'area_name' => '槐荫区',
                'name' => '槐荫区',
            ),
            370105 =>
            array(
                'code' => 300103,
                'area_name' => '天桥区',
                'name' => '天桥区',
            ),
            370112 =>
            array(
                'code' => 300105,
                'area_name' => '历城区',
                'name' => '历城区',
            ),
            370113 =>
            array(
                'code' => 300106,
                'area_name' => '长清区',
                'name' => '长清区',
            ),
            370124 =>
            array(
                'code' => 300108,
                'area_name' => '平阴县',
                'name' => '平阴县',
            ),
            370125 =>
            array(
                'code' => 300109,
                'area_name' => '济阳县',
                'name' => '济阳县',
            ),
            370126 =>
            array(
                'code' => 300110,
                'area_name' => '商河县',
                'name' => '商河县',
            ),
            370181 =>
            array(
                'code' => 300107,
                'area_name' => '章丘市',
                'name' => '章丘市',
            ),
            370202 =>
            array(
                'code' => 300201,
                'area_name' => '市南区',
                'name' => '市南区',
            ),
            370203 =>
            array(
                'code' => 300202,
                'area_name' => '市北区',
                'name' => '市北区',
            ),
            370211 =>
            array(
                'code' => 300206,
                'area_name' => '黄岛区',
                'name' => '黄岛区',
            ),
            370212 =>
            array(
                'code' => 300207,
                'area_name' => '崂山区',
                'name' => '崂山区',
            ),
            370213 =>
            array(
                'code' => 300205,
                'area_name' => '李沧区',
                'name' => '李沧区',
            ),
            370214 =>
            array(
                'code' => 300203,
                'area_name' => '城阳区',
                'name' => '城阳区',
            ),
            370281 =>
            array(
                'code' => 300209,
                'area_name' => '胶州市',
                'name' => '胶州市',
            ),
            370282 =>
            array(
                'code' => 300212,
                'area_name' => '即墨市',
                'name' => '即墨市',
            ),
            370283 =>
            array(
                'code' => 300210,
                'area_name' => '平度市',
                'name' => '平度市',
            ),
            370285 =>
            array(
                'code' => 300211,
                'area_name' => '莱西市',
                'name' => '莱西市',
            ),
            370302 =>
            array(
                'code' => 300303,
                'area_name' => '淄川区',
                'name' => '淄川区',
            ),
            370303 =>
            array(
                'code' => 300301,
                'area_name' => '张店区',
                'name' => '张店区',
            ),
            370304 =>
            array(
                'code' => 300304,
                'area_name' => '博山区',
                'name' => '博山区',
            ),
            370305 =>
            array(
                'code' => 300302,
                'area_name' => '临淄区',
                'name' => '临淄区',
            ),
            370306 =>
            array(
                'code' => 300305,
                'area_name' => '周村区',
                'name' => '周村区',
            ),
            370321 =>
            array(
                'code' => 300306,
                'area_name' => '桓台县',
                'name' => '桓台县',
            ),
            370322 =>
            array(
                'code' => 300307,
                'area_name' => '高青县',
                'name' => '高青县',
            ),
            370323 =>
            array(
                'code' => 300308,
                'area_name' => '沂源县',
                'name' => '沂源县',
            ),
            370402 =>
            array(
                'code' => 300401,
                'area_name' => '市中区',
                'name' => '市中区',
            ),
            370403 =>
            array(
                'code' => 300405,
                'area_name' => '薛城区',
                'name' => '薛城区',
            ),
            370404 =>
            array(
                'code' => '',
                'area_name' => '峄城区',
                'name' => '',
            ),
            370405 =>
            array(
                'code' => '',
                'area_name' => '台儿庄区',
                'name' => '',
            ),
            370406 =>
            array(
                'code' => 300402,
                'area_name' => '山亭区',
                'name' => '山亭区',
            ),
            370481 =>
            array(
                'code' => 300406,
                'area_name' => '滕州市',
                'name' => '滕州市',
            ),
            370502 =>
            array(
                'code' => 300501,
                'area_name' => '东营区',
                'name' => '东营区',
            ),
            370503 =>
            array(
                'code' => 300502,
                'area_name' => '河口区',
                'name' => '河口区',
            ),
            370521 =>
            array(
                'code' => 300503,
                'area_name' => '垦利县',
                'name' => '垦利县',
            ),
            370522 =>
            array(
                'code' => 300505,
                'area_name' => '利津县',
                'name' => '利津县',
            ),
            370523 =>
            array(
                'code' => 300504,
                'area_name' => '广饶县',
                'name' => '广饶县',
            ),
            370602 =>
            array(
                'code' => 300701,
                'area_name' => '芝罘区',
                'name' => '芝罘区',
            ),
            370611 =>
            array(
                'code' => 300702,
                'area_name' => '福山区',
                'name' => '福山区',
            ),
            370612 =>
            array(
                'code' => 300703,
                'area_name' => '牟平区',
                'name' => '牟平区',
            ),
            370613 =>
            array(
                'code' => 300704,
                'area_name' => '莱山区',
                'name' => '莱山区',
            ),
            370634 =>
            array(
                'code' => 300712,
                'area_name' => '长岛县',
                'name' => '长岛县',
            ),
            370681 =>
            array(
                'code' => 300705,
                'area_name' => '龙口市',
                'name' => '龙口市',
            ),
            370682 =>
            array(
                'code' => 300706,
                'area_name' => '莱阳市',
                'name' => '莱阳市',
            ),
            370683 =>
            array(
                'code' => 300707,
                'area_name' => '莱州市',
                'name' => '莱州市',
            ),
            370684 =>
            array(
                'code' => 300709,
                'area_name' => '蓬莱市',
                'name' => '蓬莱市',
            ),
            370685 =>
            array(
                'code' => 300708,
                'area_name' => '招远市',
                'name' => '招远市',
            ),
            370686 =>
            array(
                'code' => 300710,
                'area_name' => '栖霞市',
                'name' => '栖霞市',
            ),
            370687 =>
            array(
                'code' => 300711,
                'area_name' => '海阳市',
                'name' => '海阳市',
            ),
            370702 =>
            array(
                'code' => 300601,
                'area_name' => '潍城区',
                'name' => '潍城区',
            ),
            370703 =>
            array(
                'code' => 300602,
                'area_name' => '寒亭区',
                'name' => '寒亭区',
            ),
            370704 =>
            array(
                'code' => 300603,
                'area_name' => '坊子区',
                'name' => '坊子区',
            ),
            370705 =>
            array(
                'code' => 300604,
                'area_name' => '奎文区',
                'name' => '奎文区',
            ),
            370724 =>
            array(
                'code' => 300612,
                'area_name' => '临朐县',
                'name' => '临朐县',
            ),
            370725 =>
            array(
                'code' => 300611,
                'area_name' => '昌乐县',
                'name' => '昌乐县',
            ),
            370781 =>
            array(
                'code' => 300605,
                'area_name' => '青州市',
                'name' => '青州市',
            ),
            370782 =>
            array(
                'code' => 300606,
                'area_name' => '诸城市',
                'name' => '诸城市',
            ),
            370783 =>
            array(
                'code' => 300607,
                'area_name' => '寿光市',
                'name' => '寿光市',
            ),
            370784 =>
            array(
                'code' => 300608,
                'area_name' => '安丘市',
                'name' => '安丘市',
            ),
            370785 =>
            array(
                'code' => 300609,
                'area_name' => '高密市',
                'name' => '高密市',
            ),
            370786 =>
            array(
                'code' => 300610,
                'area_name' => '昌邑市',
                'name' => '昌邑市',
            ),
            370802 =>
            array(
                'code' => 300901,
                'area_name' => '市中区',
                'name' => '市中区',
            ),
            370811 =>
            array(
                'code' => 300902,
                'area_name' => '任城区',
                'name' => '任城区',
            ),
            370826 =>
            array(
                'code' => 300909,
                'area_name' => '微山县',
                'name' => '微山县',
            ),
            370827 =>
            array(
                'code' => 300906,
                'area_name' => '鱼台县',
                'name' => '鱼台县',
            ),
            370828 =>
            array(
                'code' => 300907,
                'area_name' => '金乡县',
                'name' => '金乡县',
            ),
            370829 =>
            array(
                'code' => 300908,
                'area_name' => '嘉祥县',
                'name' => '嘉祥县',
            ),
            370830 =>
            array(
                'code' => 300910,
                'area_name' => '汶上县',
                'name' => '汶上县',
            ),
            370831 =>
            array(
                'code' => 300911,
                'area_name' => '泗水县',
                'name' => '泗水县',
            ),
            370832 =>
            array(
                'code' => 300912,
                'area_name' => '梁山县',
                'name' => '梁山县',
            ),
            370881 =>
            array(
                'code' => 300903,
                'area_name' => '曲阜市',
                'name' => '曲阜市',
            ),
            370882 =>
            array(
                'code' => 300904,
                'area_name' => '兖州市',
                'name' => '兖州市',
            ),
            370883 =>
            array(
                'code' => 300905,
                'area_name' => '邹城市',
                'name' => '邹城市',
            ),
            370902 =>
            array(
                'code' => 301001,
                'area_name' => '泰山区',
                'name' => '泰山区',
            ),
            370903 =>
            array(
                'code' => 301002,
                'area_name' => '岱岳区',
                'name' => '岱岳区',
            ),
            370921 =>
            array(
                'code' => 301005,
                'area_name' => '宁阳县',
                'name' => '宁阳县',
            ),
            370923 =>
            array(
                'code' => 301006,
                'area_name' => '东平县',
                'name' => '东平县',
            ),
            370982 =>
            array(
                'code' => 301003,
                'area_name' => '新泰市',
                'name' => '新泰市',
            ),
            370983 =>
            array(
                'code' => 301004,
                'area_name' => '肥城市',
                'name' => '肥城市',
            ),
            371002 =>
            array(
                'code' => 300801,
                'area_name' => '环翠区',
                'name' => '环翠区',
            ),
            371081 =>
            array(
                'code' => 300803,
                'area_name' => '文登市',
                'name' => '文登市',
            ),
            371082 =>
            array(
                'code' => 300804,
                'area_name' => '荣成市',
                'name' => '荣成市',
            ),
            371083 =>
            array(
                'code' => 300802,
                'area_name' => '乳山市',
                'name' => '乳山市',
            ),
            371102 =>
            array(
                'code' => 301101,
                'area_name' => '东港区',
                'name' => '东港区',
            ),
            371103 =>
            array(
                'code' => '',
                'area_name' => '岚山区',
                'name' => '',
            ),
            371121 =>
            array(
                'code' => 301102,
                'area_name' => '五莲县',
                'name' => '五莲县',
            ),
            371122 =>
            array(
                'code' => '',
                'area_name' => '莒县',
                'name' => '',
            ),
            371202 =>
            array(
                'code' => 301201,
                'area_name' => '莱城区',
                'name' => '莱城区',
            ),
            371203 =>
            array(
                'code' => 301202,
                'area_name' => '钢城区',
                'name' => '钢城区',
            ),
            371302 =>
            array(
                'code' => 301401,
                'area_name' => '兰山区',
                'name' => '兰山区',
            ),
            371311 =>
            array(
                'code' => 301402,
                'area_name' => '罗庄区',
                'name' => '罗庄区',
            ),
            371312 =>
            array(
                'code' => 301403,
                'area_name' => '河东区',
                'name' => '河东区',
            ),
            371321 =>
            array(
                'code' => 301404,
                'area_name' => '沂南县',
                'name' => '沂南县',
            ),
            371322 =>
            array(
                'code' => 301405,
                'area_name' => '郯城县',
                'name' => '郯城县',
            ),
            371323 =>
            array(
                'code' => 301406,
                'area_name' => '沂水县',
                'name' => '沂水县',
            ),
            371324 =>
            array(
                'code' => 301407,
                'area_name' => '苍山县',
                'name' => '苍山县',
            ),
            371325 =>
            array(
                'code' => '',
                'area_name' => '费县',
                'name' => '',
            ),
            371326 =>
            array(
                'code' => 301409,
                'area_name' => '平邑县',
                'name' => '平邑县',
            ),
            371327 =>
            array(
                'code' => 301410,
                'area_name' => '莒南县',
                'name' => '莒南县',
            ),
            371328 =>
            array(
                'code' => 301411,
                'area_name' => '蒙阴县',
                'name' => '蒙阴县',
            ),
            371329 =>
            array(
                'code' => 301412,
                'area_name' => '临沭县',
                'name' => '临沭县',
            ),
            371402 =>
            array(
                'code' => 301301,
                'area_name' => '德城区',
                'name' => '德城区',
            ),
            371421 =>
            array(
                'code' => '',
                'area_name' => '陵县',
                'name' => '',
            ),
            371422 =>
            array(
                'code' => 301305,
                'area_name' => '宁津县',
                'name' => '宁津县',
            ),
            371423 =>
            array(
                'code' => 301308,
                'area_name' => '庆云县',
                'name' => '庆云县',
            ),
            371424 =>
            array(
                'code' => 301311,
                'area_name' => '临邑县',
                'name' => '临邑县',
            ),
            371425 =>
            array(
                'code' => 301306,
                'area_name' => '齐河县',
                'name' => '齐河县',
            ),
            371426 =>
            array(
                'code' => 301309,
                'area_name' => '平原县',
                'name' => '平原县',
            ),
            371427 =>
            array(
                'code' => 301310,
                'area_name' => '夏津县',
                'name' => '夏津县',
            ),
            371428 =>
            array(
                'code' => 301307,
                'area_name' => '武城县',
                'name' => '武城县',
            ),
            371481 =>
            array(
                'code' => 301302,
                'area_name' => '乐陵市',
                'name' => '乐陵市',
            ),
            371482 =>
            array(
                'code' => 301303,
                'area_name' => '禹城市',
                'name' => '禹城市',
            ),
            371502 =>
            array(
                'code' => 301501,
                'area_name' => '东昌府区',
                'name' => '东昌府区',
            ),
            371521 =>
            array(
                'code' => 301504,
                'area_name' => '阳谷县',
                'name' => '阳谷县',
            ),
            371522 =>
            array(
                'code' => '',
                'area_name' => '莘县',
                'name' => '',
            ),
            371523 =>
            array(
                'code' => 301505,
                'area_name' => '茌平县',
                'name' => '茌平县',
            ),
            371524 =>
            array(
                'code' => 301507,
                'area_name' => '东阿县',
                'name' => '东阿县',
            ),
            371525 =>
            array(
                'code' => '',
                'area_name' => '冠县',
                'name' => '',
            ),
            371526 =>
            array(
                'code' => 301503,
                'area_name' => '高唐县',
                'name' => '高唐县',
            ),
            371581 =>
            array(
                'code' => 301502,
                'area_name' => '临清市',
                'name' => '临清市',
            ),
            371602 =>
            array(
                'code' => 301601,
                'area_name' => '滨城区',
                'name' => '滨城区',
            ),
            371621 =>
            array(
                'code' => 301604,
                'area_name' => '惠民县',
                'name' => '惠民县',
            ),
            371622 =>
            array(
                'code' => 301606,
                'area_name' => '阳信县',
                'name' => '阳信县',
            ),
            371623 =>
            array(
                'code' => 301607,
                'area_name' => '无棣县',
                'name' => '无棣县',
            ),
            371624 =>
            array(
                'code' => 301603,
                'area_name' => '沾化县',
                'name' => '沾化县',
            ),
            371625 =>
            array(
                'code' => 301605,
                'area_name' => '博兴县',
                'name' => '博兴县',
            ),
            371626 =>
            array(
                'code' => 301602,
                'area_name' => '邹平县',
                'name' => '邹平县',
            ),
            371702 =>
            array(
                'code' => 301701,
                'area_name' => '牡丹区',
                'name' => '牡丹区',
            ),
            371721 =>
            array(
                'code' => '',
                'area_name' => '曹县',
                'name' => '',
            ),
            371722 =>
            array(
                'code' => '',
                'area_name' => '单县',
                'name' => '',
            ),
            371723 =>
            array(
                'code' => 301709,
                'area_name' => '成武县',
                'name' => '成武县',
            ),
            371724 =>
            array(
                'code' => 301707,
                'area_name' => '巨野县',
                'name' => '巨野县',
            ),
            371725 =>
            array(
                'code' => 301704,
                'area_name' => '郓城县',
                'name' => '郓城县',
            ),
            371726 =>
            array(
                'code' => 301702,
                'area_name' => '鄄城县',
                'name' => '鄄城县',
            ),
            371727 =>
            array(
                'code' => 301706,
                'area_name' => '定陶县',
                'name' => '定陶县',
            ),
            371728 =>
            array(
                'code' => 301708,
                'area_name' => '东明县',
                'name' => '东明县',
            ),
            410102 =>
            array(
                'code' => 310101,
                'area_name' => '中原区',
                'name' => '中原区',
            ),
            410103 =>
            array(
                'code' => 310103,
                'area_name' => '二七区',
                'name' => '二七区',
            ),
            410104 =>
            array(
                'code' => 310104,
                'area_name' => '管城回族区',
                'name' => '管城回族区',
            ),
            410105 =>
            array(
                'code' => 310102,
                'area_name' => '金水区',
                'name' => '金水区',
            ),
            410106 =>
            array(
                'code' => 310105,
                'area_name' => '上街区',
                'name' => '上街区',
            ),
            410108 =>
            array(
                'code' => '',
                'area_name' => '惠济区',
                'name' => '',
            ),
            410122 =>
            array(
                'code' => 310112,
                'area_name' => '中牟县',
                'name' => '中牟县',
            ),
            410181 =>
            array(
                'code' => 310107,
                'area_name' => '巩义市',
                'name' => '巩义市',
            ),
            410182 =>
            array(
                'code' => 310111,
                'area_name' => '荥阳市',
                'name' => '荥阳市',
            ),
            410183 =>
            array(
                'code' => 310109,
                'area_name' => '新密市',
                'name' => '新密市',
            ),
            410184 =>
            array(
                'code' => 310108,
                'area_name' => '新郑市',
                'name' => '新郑市',
            ),
            410185 =>
            array(
                'code' => 310110,
                'area_name' => '登封市',
                'name' => '登封市',
            ),
            410202 =>
            array(
                'code' => 310202,
                'area_name' => '龙亭区',
                'name' => '龙亭区',
            ),
            410203 =>
            array(
                'code' => 310203,
                'area_name' => '顺河回族区',
                'name' => '顺河回族区',
            ),
            410204 =>
            array(
                'code' => 310201,
                'area_name' => '鼓楼区',
                'name' => '鼓楼区',
            ),
            410205 =>
            array(
                'code' => '',
                'area_name' => '禹王台区',
                'name' => '',
            ),
            410211 =>
            array(
                'code' => '',
                'area_name' => '金明区',
                'name' => '',
            ),
            410221 =>
            array(
                'code' => '',
                'area_name' => '杞县',
                'name' => '',
            ),
            410222 =>
            array(
                'code' => 310210,
                'area_name' => '通许县',
                'name' => '通许县',
            ),
            410223 =>
            array(
                'code' => 310207,
                'area_name' => '尉氏县',
                'name' => '尉氏县',
            ),
            410224 =>
            array(
                'code' => 310206,
                'area_name' => '开封县',
                'name' => '开封县',
            ),
            410225 =>
            array(
                'code' => 310208,
                'area_name' => '兰考县',
                'name' => '兰考县',
            ),
            410302 =>
            array(
                'code' => 310302,
                'area_name' => '老城区',
                'name' => '老城区',
            ),
            410303 =>
            array(
                'code' => 310301,
                'area_name' => '西工区',
                'name' => '西工区',
            ),
            410304 =>
            array(
                'code' => 310304,
                'area_name' => '瀍河回族区',
                'name' => '瀍河回族区',
            ),
            410305 =>
            array(
                'code' => 310303,
                'area_name' => '涧西区',
                'name' => '涧西区',
            ),
            410306 =>
            array(
                'code' => 310306,
                'area_name' => '吉利区',
                'name' => '吉利区',
            ),
            410307 =>
            array(
                'code' => 310305,
                'area_name' => '洛龙区',
                'name' => '洛龙区',
            ),
            410322 =>
            array(
                'code' => 310308,
                'area_name' => '孟津县',
                'name' => '孟津县',
            ),
            410323 =>
            array(
                'code' => 310314,
                'area_name' => '新安县',
                'name' => '新安县',
            ),
            410324 =>
            array(
                'code' => 310315,
                'area_name' => '栾川县',
                'name' => '栾川县',
            ),
            410325 =>
            array(
                'code' => '',
                'area_name' => '嵩县',
                'name' => '',
            ),
            410326 =>
            array(
                'code' => 310309,
                'area_name' => '汝阳县',
                'name' => '汝阳县',
            ),
            410327 =>
            array(
                'code' => 310313,
                'area_name' => '宜阳县',
                'name' => '宜阳县',
            ),
            410328 =>
            array(
                'code' => 310311,
                'area_name' => '洛宁县',
                'name' => '洛宁县',
            ),
            410329 =>
            array(
                'code' => 310310,
                'area_name' => '伊川县',
                'name' => '伊川县',
            ),
            410381 =>
            array(
                'code' => 310307,
                'area_name' => '偃师市',
                'name' => '偃师市',
            ),
            410402 =>
            array(
                'code' => 310401,
                'area_name' => '新华区',
                'name' => '新华区',
            ),
            410403 =>
            array(
                'code' => 310402,
                'area_name' => '卫东区',
                'name' => '卫东区',
            ),
            410404 =>
            array(
                'code' => 310404,
                'area_name' => '石龙区',
                'name' => '石龙区',
            ),
            410411 =>
            array(
                'code' => 310403,
                'area_name' => '湛河区',
                'name' => '湛河区',
            ),
            410421 =>
            array(
                'code' => 310407,
                'area_name' => '宝丰县',
                'name' => '宝丰县',
            ),
            410422 =>
            array(
                'code' => '',
                'area_name' => '叶县',
                'name' => '',
            ),
            410423 =>
            array(
                'code' => 310410,
                'area_name' => '鲁山县',
                'name' => '鲁山县',
            ),
            410425 =>
            array(
                'code' => '',
                'area_name' => '郏县',
                'name' => '',
            ),
            410481 =>
            array(
                'code' => 310406,
                'area_name' => '舞钢市',
                'name' => '舞钢市',
            ),
            410482 =>
            array(
                'code' => 310405,
                'area_name' => '汝州市',
                'name' => '汝州市',
            ),
            410502 =>
            array(
                'code' => 310802,
                'area_name' => '文峰区',
                'name' => '文峰区',
            ),
            410503 =>
            array(
                'code' => 310801,
                'area_name' => '北关区',
                'name' => '北关区',
            ),
            410505 =>
            array(
                'code' => 310803,
                'area_name' => '殷都区',
                'name' => '殷都区',
            ),
            410506 =>
            array(
                'code' => 310804,
                'area_name' => '龙安区',
                'name' => '龙安区',
            ),
            410522 =>
            array(
                'code' => 310806,
                'area_name' => '安阳县',
                'name' => '安阳县',
            ),
            410523 =>
            array(
                'code' => 310809,
                'area_name' => '汤阴县',
                'name' => '汤阴县',
            ),
            410526 =>
            array(
                'code' => '',
                'area_name' => '滑县',
                'name' => '',
            ),
            410527 =>
            array(
                'code' => 310808,
                'area_name' => '内黄县',
                'name' => '内黄县',
            ),
            410581 =>
            array(
                'code' => 310805,
                'area_name' => '林州市',
                'name' => '林州市',
            ),
            410602 =>
            array(
                'code' => 310603,
                'area_name' => '鹤山区',
                'name' => '鹤山区',
            ),
            410603 =>
            array(
                'code' => 310602,
                'area_name' => '山城区',
                'name' => '山城区',
            ),
            410611 =>
            array(
                'code' => 310601,
                'area_name' => '淇滨区',
                'name' => '淇滨区',
            ),
            410621 =>
            array(
                'code' => '',
                'area_name' => '浚县',
                'name' => '',
            ),
            410622 =>
            array(
                'code' => '',
                'area_name' => '淇县',
                'name' => '',
            ),
            410702 =>
            array(
                'code' => 310702,
                'area_name' => '红旗区',
                'name' => '红旗区',
            ),
            410703 =>
            array(
                'code' => '',
                'area_name' => '卫滨区',
                'name' => '',
            ),
            410704 =>
            array(
                'code' => '',
                'area_name' => '凤泉区',
                'name' => '',
            ),
            410711 =>
            array(
                'code' => '',
                'area_name' => '牧野区',
                'name' => '',
            ),
            410721 =>
            array(
                'code' => 310707,
                'area_name' => '新乡县',
                'name' => '新乡县',
            ),
            410724 =>
            array(
                'code' => 310708,
                'area_name' => '获嘉县',
                'name' => '获嘉县',
            ),
            410725 =>
            array(
                'code' => 310709,
                'area_name' => '原阳县',
                'name' => '原阳县',
            ),
            410726 =>
            array(
                'code' => 310712,
                'area_name' => '延津县',
                'name' => '延津县',
            ),
            410727 =>
            array(
                'code' => 310711,
                'area_name' => '封丘县',
                'name' => '封丘县',
            ),
            410728 =>
            array(
                'code' => 310710,
                'area_name' => '长垣县',
                'name' => '长垣县',
            ),
            410781 =>
            array(
                'code' => 310705,
                'area_name' => '卫辉市',
                'name' => '卫辉市',
            ),
            410782 =>
            array(
                'code' => 310706,
                'area_name' => '辉县市',
                'name' => '辉县市',
            ),
            410802 =>
            array(
                'code' => 310501,
                'area_name' => '解放区',
                'name' => '解放区',
            ),
            410803 =>
            array(
                'code' => 310502,
                'area_name' => '中站区',
                'name' => '中站区',
            ),
            410804 =>
            array(
                'code' => 310503,
                'area_name' => '马村区',
                'name' => '马村区',
            ),
            410811 =>
            array(
                'code' => 310504,
                'area_name' => '山阳区',
                'name' => '山阳区',
            ),
            410821 =>
            array(
                'code' => 310507,
                'area_name' => '修武县',
                'name' => '修武县',
            ),
            410822 =>
            array(
                'code' => 310510,
                'area_name' => '博爱县',
                'name' => '博爱县',
            ),
            410823 =>
            array(
                'code' => 310509,
                'area_name' => '武陟县',
                'name' => '武陟县',
            ),
            410825 =>
            array(
                'code' => '',
                'area_name' => '温县',
                'name' => '',
            ),
            410882 =>
            array(
                'code' => 310505,
                'area_name' => '沁阳市',
                'name' => '沁阳市',
            ),
            410883 =>
            array(
                'code' => 310506,
                'area_name' => '孟州市',
                'name' => '孟州市',
            ),
            410902 =>
            array(
                'code' => 310901,
                'area_name' => '华龙区',
                'name' => '华龙区',
            ),
            410922 =>
            array(
                'code' => 310905,
                'area_name' => '清丰县',
                'name' => '清丰县',
            ),
            410923 =>
            array(
                'code' => 310903,
                'area_name' => '南乐县',
                'name' => '南乐县',
            ),
            410926 =>
            array(
                'code' => '',
                'area_name' => '范县',
                'name' => '',
            ),
            410927 =>
            array(
                'code' => 310904,
                'area_name' => '台前县',
                'name' => '台前县',
            ),
            410928 =>
            array(
                'code' => 310902,
                'area_name' => '濮阳县',
                'name' => '濮阳县',
            ),
            411002 =>
            array(
                'code' => 311001,
                'area_name' => '魏都区',
                'name' => '魏都区',
            ),
            411023 =>
            array(
                'code' => 311004,
                'area_name' => '许昌县',
                'name' => '许昌县',
            ),
            411024 =>
            array(
                'code' => 311005,
                'area_name' => '鄢陵县',
                'name' => '鄢陵县',
            ),
            411025 =>
            array(
                'code' => 311006,
                'area_name' => '襄城县',
                'name' => '襄城县',
            ),
            411081 =>
            array(
                'code' => 311002,
                'area_name' => '禹州市',
                'name' => '禹州市',
            ),
            411082 =>
            array(
                'code' => 311003,
                'area_name' => '长葛市',
                'name' => '长葛市',
            ),
            411102 =>
            array(
                'code' => 311101,
                'area_name' => '源汇区',
                'name' => '源汇区',
            ),
            411103 =>
            array(
                'code' => '',
                'area_name' => '郾城区',
                'name' => '',
            ),
            411104 =>
            array(
                'code' => '',
                'area_name' => '召陵区',
                'name' => '',
            ),
            411121 =>
            array(
                'code' => 311104,
                'area_name' => '舞阳县',
                'name' => '舞阳县',
            ),
            411122 =>
            array(
                'code' => 311103,
                'area_name' => '临颍县',
                'name' => '临颍县',
            ),
            411202 =>
            array(
                'code' => 311201,
                'area_name' => '湖滨区',
                'name' => '湖滨区',
            ),
            411221 =>
            array(
                'code' => 311204,
                'area_name' => '渑池县',
                'name' => '渑池县',
            ),
            411222 =>
            array(
                'code' => '',
                'area_name' => '陕县',
                'name' => '',
            ),
            411224 =>
            array(
                'code' => 311205,
                'area_name' => '卢氏县',
                'name' => '卢氏县',
            ),
            411281 =>
            array(
                'code' => 311202,
                'area_name' => '义马市',
                'name' => '义马市',
            ),
            411282 =>
            array(
                'code' => 311203,
                'area_name' => '灵宝市',
                'name' => '灵宝市',
            ),
            411302 =>
            array(
                'code' => 311302,
                'area_name' => '宛城区',
                'name' => '宛城区',
            ),
            411303 =>
            array(
                'code' => 311301,
                'area_name' => '卧龙区',
                'name' => '卧龙区',
            ),
            411321 =>
            array(
                'code' => 311309,
                'area_name' => '南召县',
                'name' => '南召县',
            ),
            411322 =>
            array(
                'code' => 311305,
                'area_name' => '方城县',
                'name' => '方城县',
            ),
            411323 =>
            array(
                'code' => 311313,
                'area_name' => '西峡县',
                'name' => '西峡县',
            ),
            411324 =>
            array(
                'code' => 311307,
                'area_name' => '镇平县',
                'name' => '镇平县',
            ),
            411325 =>
            array(
                'code' => 311310,
                'area_name' => '内乡县',
                'name' => '内乡县',
            ),
            411326 =>
            array(
                'code' => 311306,
                'area_name' => '淅川县',
                'name' => '淅川县',
            ),
            411327 =>
            array(
                'code' => 311312,
                'area_name' => '社旗县',
                'name' => '社旗县',
            ),
            411328 =>
            array(
                'code' => 311308,
                'area_name' => '唐河县',
                'name' => '唐河县',
            ),
            411329 =>
            array(
                'code' => 311311,
                'area_name' => '新野县',
                'name' => '新野县',
            ),
            411330 =>
            array(
                'code' => 311304,
                'area_name' => '桐柏县',
                'name' => '桐柏县',
            ),
            411381 =>
            array(
                'code' => 311303,
                'area_name' => '邓州市',
                'name' => '邓州市',
            ),
            411402 =>
            array(
                'code' => 311401,
                'area_name' => '梁园区',
                'name' => '梁园区',
            ),
            411403 =>
            array(
                'code' => 311402,
                'area_name' => '睢阳区',
                'name' => '睢阳区',
            ),
            411421 =>
            array(
                'code' => 311406,
                'area_name' => '民权县',
                'name' => '民权县',
            ),
            411422 =>
            array(
                'code' => '',
                'area_name' => '睢县',
                'name' => '',
            ),
            411423 =>
            array(
                'code' => 311404,
                'area_name' => '宁陵县',
                'name' => '宁陵县',
            ),
            411424 =>
            array(
                'code' => 311408,
                'area_name' => '柘城县',
                'name' => '柘城县',
            ),
            411425 =>
            array(
                'code' => 311405,
                'area_name' => '虞城县',
                'name' => '虞城县',
            ),
            411426 =>
            array(
                'code' => 311407,
                'area_name' => '夏邑县',
                'name' => '夏邑县',
            ),
            411481 =>
            array(
                'code' => 311403,
                'area_name' => '永城市',
                'name' => '永城市',
            ),
            411502 =>
            array(
                'code' => 311501,
                'area_name' => '浉河区',
                'name' => '浉河区',
            ),
            411503 =>
            array(
                'code' => 311502,
                'area_name' => '平桥区',
                'name' => '平桥区',
            ),
            411521 =>
            array(
                'code' => 311509,
                'area_name' => '罗山县',
                'name' => '罗山县',
            ),
            411522 =>
            array(
                'code' => 311510,
                'area_name' => '光山县',
                'name' => '光山县',
            ),
            411523 =>
            array(
                'code' => '',
                'area_name' => '新县',
                'name' => '',
            ),
            411524 =>
            array(
                'code' => 311507,
                'area_name' => '商城县',
                'name' => '商城县',
            ),
            411525 =>
            array(
                'code' => 311508,
                'area_name' => '固始县',
                'name' => '固始县',
            ),
            411526 =>
            array(
                'code' => 311503,
                'area_name' => '潢川县',
                'name' => '潢川县',
            ),
            411527 =>
            array(
                'code' => 311504,
                'area_name' => '淮滨县',
                'name' => '淮滨县',
            ),
            411528 =>
            array(
                'code' => '',
                'area_name' => '息县',
                'name' => '',
            ),
            411602 =>
            array(
                'code' => 311601,
                'area_name' => '川汇区',
                'name' => '川汇区',
            ),
            411621 =>
            array(
                'code' => 311608,
                'area_name' => '扶沟县',
                'name' => '扶沟县',
            ),
            411622 =>
            array(
                'code' => 311607,
                'area_name' => '西华县',
                'name' => '西华县',
            ),
            411623 =>
            array(
                'code' => 311603,
                'area_name' => '商水县',
                'name' => '商水县',
            ),
            411624 =>
            array(
                'code' => 311609,
                'area_name' => '沈丘县',
                'name' => '沈丘县',
            ),
            411625 =>
            array(
                'code' => 311610,
                'area_name' => '郸城县',
                'name' => '郸城县',
            ),
            411626 =>
            array(
                'code' => 311604,
                'area_name' => '淮阳县',
                'name' => '淮阳县',
            ),
            411627 =>
            array(
                'code' => 311605,
                'area_name' => '太康县',
                'name' => '太康县',
            ),
            411628 =>
            array(
                'code' => 311606,
                'area_name' => '鹿邑县',
                'name' => '鹿邑县',
            ),
            411681 =>
            array(
                'code' => 311602,
                'area_name' => '项城市',
                'name' => '项城市',
            ),
            411702 =>
            array(
                'code' => 311701,
                'area_name' => '驿城区',
                'name' => '驿城区',
            ),
            411721 =>
            array(
                'code' => 311705,
                'area_name' => '西平县',
                'name' => '西平县',
            ),
            411722 =>
            array(
                'code' => 311704,
                'area_name' => '上蔡县',
                'name' => '上蔡县',
            ),
            411723 =>
            array(
                'code' => 311707,
                'area_name' => '平舆县',
                'name' => '平舆县',
            ),
            411724 =>
            array(
                'code' => 311710,
                'area_name' => '正阳县',
                'name' => '正阳县',
            ),
            411725 =>
            array(
                'code' => 311702,
                'area_name' => '确山县',
                'name' => '确山县',
            ),
            411726 =>
            array(
                'code' => 311706,
                'area_name' => '泌阳县',
                'name' => '泌阳县',
            ),
            411727 =>
            array(
                'code' => 311708,
                'area_name' => '汝南县',
                'name' => '汝南县',
            ),
            411728 =>
            array(
                'code' => 311709,
                'area_name' => '遂平县',
                'name' => '遂平县',
            ),
            411729 =>
            array(
                'code' => 311703,
                'area_name' => '新蔡县',
                'name' => '新蔡县',
            ),
            420102 =>
            array(
                'code' => 320101,
                'area_name' => '江岸区',
                'name' => '江岸区',
            ),
            420103 =>
            array(
                'code' => 320103,
                'area_name' => '江汉区',
                'name' => '江汉区',
            ),
            420104 =>
            array(
                'code' => 320104,
                'area_name' => '硚口区',
                'name' => '硚口区',
            ),
            420105 =>
            array(
                'code' => 320105,
                'area_name' => '汉阳区',
                'name' => '汉阳区',
            ),
            420106 =>
            array(
                'code' => 320102,
                'area_name' => '武昌区',
                'name' => '武昌区',
            ),
            420107 =>
            array(
                'code' => 320106,
                'area_name' => '青山区',
                'name' => '青山区',
            ),
            420111 =>
            array(
                'code' => 320107,
                'area_name' => '洪山区',
                'name' => '洪山区',
            ),
            420112 =>
            array(
                'code' => 320108,
                'area_name' => '东西湖区',
                'name' => '东西湖区',
            ),
            420113 =>
            array(
                'code' => 320109,
                'area_name' => '汉南区',
                'name' => '汉南区',
            ),
            420114 =>
            array(
                'code' => 320110,
                'area_name' => '蔡甸区',
                'name' => '蔡甸区',
            ),
            420115 =>
            array(
                'code' => 320111,
                'area_name' => '江夏区',
                'name' => '江夏区',
            ),
            420116 =>
            array(
                'code' => 320112,
                'area_name' => '黄陂区',
                'name' => '黄陂区',
            ),
            420117 =>
            array(
                'code' => 320113,
                'area_name' => '新洲区',
                'name' => '新洲区',
            ),
            420202 =>
            array(
                'code' => 320201,
                'area_name' => '黄石港区',
                'name' => '黄石港区',
            ),
            420203 =>
            array(
                'code' => '',
                'area_name' => '西塞山区',
                'name' => '',
            ),
            420204 =>
            array(
                'code' => 320203,
                'area_name' => '下陆区',
                'name' => '下陆区',
            ),
            420205 =>
            array(
                'code' => 320204,
                'area_name' => '铁山区',
                'name' => '铁山区',
            ),
            420222 =>
            array(
                'code' => 320206,
                'area_name' => '阳新县',
                'name' => '阳新县',
            ),
            420281 =>
            array(
                'code' => 320205,
                'area_name' => '大冶市',
                'name' => '大冶市',
            ),
            420302 =>
            array(
                'code' => 320402,
                'area_name' => '茅箭区',
                'name' => '茅箭区',
            ),
            420303 =>
            array(
                'code' => 320401,
                'area_name' => '张湾区',
                'name' => '张湾区',
            ),
            420321 =>
            array(
                'code' => '',
                'area_name' => '郧县',
                'name' => '',
            ),
            420322 =>
            array(
                'code' => 320407,
                'area_name' => '郧西县',
                'name' => '郧西县',
            ),
            420323 =>
            array(
                'code' => 320405,
                'area_name' => '竹山县',
                'name' => '竹山县',
            ),
            420324 =>
            array(
                'code' => 320408,
                'area_name' => '竹溪县',
                'name' => '竹溪县',
            ),
            420325 =>
            array(
                'code' => '',
                'area_name' => '房县',
                'name' => '',
            ),
            420381 =>
            array(
                'code' => 320403,
                'area_name' => '丹江口市',
                'name' => '丹江口市',
            ),
            420502 =>
            array(
                'code' => 320601,
                'area_name' => '西陵区',
                'name' => '西陵区',
            ),
            420503 =>
            array(
                'code' => 320602,
                'area_name' => '伍家岗区',
                'name' => '伍家岗区',
            ),
            420504 =>
            array(
                'code' => 320603,
                'area_name' => '点军区',
                'name' => '点军区',
            ),
            420505 =>
            array(
                'code' => 320604,
                'area_name' => '猇亭区',
                'name' => '猇亭区',
            ),
            420506 =>
            array(
                'code' => 320605,
                'area_name' => '夷陵区',
                'name' => '夷陵区',
            ),
            420525 =>
            array(
                'code' => '',
                'area_name' => '远安县',
                'name' => '',
            ),
            420526 =>
            array(
                'code' => '',
                'area_name' => '兴山县',
                'name' => '',
            ),
            420527 =>
            array(
                'code' => 320609,
                'area_name' => '秭归县',
                'name' => '秭归县',
            ),
            420528 =>
            array(
                'code' => 320612,
                'area_name' => '长阳土家族自治县',
                'name' => '长阳土家族自治县',
            ),
            420529 =>
            array(
                'code' => 320611,
                'area_name' => '五峰土家族自治县',
                'name' => '五峰土家族自治县',
            ),
            420581 =>
            array(
                'code' => 320606,
                'area_name' => '宜都市',
                'name' => '宜都市',
            ),
            420582 =>
            array(
                'code' => 320607,
                'area_name' => '当阳市',
                'name' => '当阳市',
            ),
            420583 =>
            array(
                'code' => 320608,
                'area_name' => '枝江市',
                'name' => '枝江市',
            ),
            420602 =>
            array(
                'code' => '',
                'area_name' => '襄城区',
                'name' => '',
            ),
            420606 =>
            array(
                'code' => '',
                'area_name' => '樊城区',
                'name' => '',
            ),
            420607 =>
            array(
                'code' => '',
                'area_name' => '襄州区',
                'name' => '',
            ),
            420624 =>
            array(
                'code' => '',
                'area_name' => '南漳县',
                'name' => '',
            ),
            420625 =>
            array(
                'code' => '',
                'area_name' => '谷城县',
                'name' => '',
            ),
            420626 =>
            array(
                'code' => '',
                'area_name' => '保康县',
                'name' => '',
            ),
            420682 =>
            array(
                'code' => '',
                'area_name' => '老河口市',
                'name' => '',
            ),
            420683 =>
            array(
                'code' => '',
                'area_name' => '枣阳市',
                'name' => '',
            ),
            420684 =>
            array(
                'code' => '',
                'area_name' => '宜城市',
                'name' => '',
            ),
            420702 =>
            array(
                'code' => 320803,
                'area_name' => '梁子湖区',
                'name' => '梁子湖区',
            ),
            420703 =>
            array(
                'code' => 320802,
                'area_name' => '华容区',
                'name' => '华容区',
            ),
            420704 =>
            array(
                'code' => 320801,
                'area_name' => '鄂城区',
                'name' => '鄂城区',
            ),
            420802 =>
            array(
                'code' => 320701,
                'area_name' => '东宝区',
                'name' => '东宝区',
            ),
            420804 =>
            array(
                'code' => 320702,
                'area_name' => '掇刀区',
                'name' => '掇刀区',
            ),
            420821 =>
            array(
                'code' => 320704,
                'area_name' => '京山县',
                'name' => '京山县',
            ),
            420822 =>
            array(
                'code' => 320705,
                'area_name' => '沙洋县',
                'name' => '沙洋县',
            ),
            420881 =>
            array(
                'code' => 320703,
                'area_name' => '钟祥市',
                'name' => '钟祥市',
            ),
            420902 =>
            array(
                'code' => 320901,
                'area_name' => '孝南区',
                'name' => '孝南区',
            ),
            420921 =>
            array(
                'code' => 320907,
                'area_name' => '孝昌县',
                'name' => '孝昌县',
            ),
            420922 =>
            array(
                'code' => 320906,
                'area_name' => '大悟县',
                'name' => '大悟县',
            ),
            420923 =>
            array(
                'code' => 320905,
                'area_name' => '云梦县',
                'name' => '云梦县',
            ),
            420981 =>
            array(
                'code' => 320902,
                'area_name' => '应城市',
                'name' => '应城市',
            ),
            420982 =>
            array(
                'code' => 320903,
                'area_name' => '安陆市',
                'name' => '安陆市',
            ),
            420984 =>
            array(
                'code' => 320904,
                'area_name' => '汉川市',
                'name' => '汉川市',
            ),
            421002 =>
            array(
                'code' => 320501,
                'area_name' => '沙市区',
                'name' => '沙市区',
            ),
            421003 =>
            array(
                'code' => 320502,
                'area_name' => '荆州区',
                'name' => '荆州区',
            ),
            421022 =>
            array(
                'code' => 320507,
                'area_name' => '公安县',
                'name' => '公安县',
            ),
            421023 =>
            array(
                'code' => 320506,
                'area_name' => '监利县',
                'name' => '监利县',
            ),
            421024 =>
            array(
                'code' => 320508,
                'area_name' => '江陵县',
                'name' => '江陵县',
            ),
            421081 =>
            array(
                'code' => 320504,
                'area_name' => '石首市',
                'name' => '石首市',
            ),
            421083 =>
            array(
                'code' => 320503,
                'area_name' => '洪湖市',
                'name' => '洪湖市',
            ),
            421087 =>
            array(
                'code' => 320505,
                'area_name' => '松滋市',
                'name' => '松滋市',
            ),
            421102 =>
            array(
                'code' => 321001,
                'area_name' => '黄州区',
                'name' => '黄州区',
            ),
            421121 =>
            array(
                'code' => 321010,
                'area_name' => '团风县',
                'name' => '团风县',
            ),
            421122 =>
            array(
                'code' => 321004,
                'area_name' => '红安县',
                'name' => '红安县',
            ),
            421123 =>
            array(
                'code' => 321005,
                'area_name' => '罗田县',
                'name' => '罗田县',
            ),
            421124 =>
            array(
                'code' => 321009,
                'area_name' => '英山县',
                'name' => '英山县',
            ),
            421125 =>
            array(
                'code' => 321006,
                'area_name' => '浠水县',
                'name' => '浠水县',
            ),
            421126 =>
            array(
                'code' => 321007,
                'area_name' => '蕲春县',
                'name' => '蕲春县',
            ),
            421127 =>
            array(
                'code' => 321008,
                'area_name' => '黄梅县',
                'name' => '黄梅县',
            ),
            421181 =>
            array(
                'code' => 321002,
                'area_name' => '麻城市',
                'name' => '麻城市',
            ),
            421182 =>
            array(
                'code' => 321003,
                'area_name' => '武穴市',
                'name' => '武穴市',
            ),
            421202 =>
            array(
                'code' => 321101,
                'area_name' => '咸安区',
                'name' => '咸安区',
            ),
            421221 =>
            array(
                'code' => 321103,
                'area_name' => '嘉鱼县',
                'name' => '嘉鱼县',
            ),
            421222 =>
            array(
                'code' => 321106,
                'area_name' => '通城县',
                'name' => '通城县',
            ),
            421223 =>
            array(
                'code' => 321105,
                'area_name' => '崇阳县',
                'name' => '崇阳县',
            ),
            421224 =>
            array(
                'code' => 321104,
                'area_name' => '通山县',
                'name' => '通山县',
            ),
            421281 =>
            array(
                'code' => 321102,
                'area_name' => '赤壁市',
                'name' => '赤壁市',
            ),
            421302 =>
            array(
                'code' => 321201,
                'area_name' => '曾都区',
                'name' => '曾都区',
            ),
            421321 =>
            array(
                'code' => '',
                'area_name' => '随县',
                'name' => '',
            ),
            421381 =>
            array(
                'code' => 321202,
                'area_name' => '广水市',
                'name' => '广水市',
            ),
            422801 =>
            array(
                'code' => 321401,
                'area_name' => '恩施市',
                'name' => '恩施市',
            ),
            422802 =>
            array(
                'code' => 321402,
                'area_name' => '利川市',
                'name' => '利川市',
            ),
            422822 =>
            array(
                'code' => 321403,
                'area_name' => '建始县',
                'name' => '建始县',
            ),
            422823 =>
            array(
                'code' => 321405,
                'area_name' => '巴东县',
                'name' => '巴东县',
            ),
            422825 =>
            array(
                'code' => 321407,
                'area_name' => '宣恩县',
                'name' => '宣恩县',
            ),
            422826 =>
            array(
                'code' => 321408,
                'area_name' => '咸丰县',
                'name' => '咸丰县',
            ),
            422827 =>
            array(
                'code' => 321404,
                'area_name' => '来凤县',
                'name' => '来凤县',
            ),
            422828 =>
            array(
                'code' => 321406,
                'area_name' => '鹤峰县',
                'name' => '鹤峰县',
            ),
            430102 =>
            array(
                'code' => 330102,
                'area_name' => '芙蓉区',
                'name' => '芙蓉区',
            ),
            430103 =>
            array(
                'code' => 330103,
                'area_name' => '天心区',
                'name' => '天心区',
            ),
            430104 =>
            array(
                'code' => 330101,
                'area_name' => '岳麓区',
                'name' => '岳麓区',
            ),
            430105 =>
            array(
                'code' => 330104,
                'area_name' => '开福区',
                'name' => '开福区',
            ),
            430111 =>
            array(
                'code' => 330105,
                'area_name' => '雨花区',
                'name' => '雨花区',
            ),
            430121 =>
            array(
                'code' => 330107,
                'area_name' => '长沙县',
                'name' => '长沙县',
            ),
            430122 =>
            array(
                'code' => '',
                'area_name' => '望城区',
                'name' => '',
            ),
            430124 =>
            array(
                'code' => 330109,
                'area_name' => '宁乡县',
                'name' => '宁乡县',
            ),
            430181 =>
            array(
                'code' => 330106,
                'area_name' => '浏阳市',
                'name' => '浏阳市',
            ),
            430202 =>
            array(
                'code' => 330202,
                'area_name' => '荷塘区',
                'name' => '荷塘区',
            ),
            430203 =>
            array(
                'code' => 330203,
                'area_name' => '芦淞区',
                'name' => '芦淞区',
            ),
            430204 =>
            array(
                'code' => 330204,
                'area_name' => '石峰区',
                'name' => '石峰区',
            ),
            430211 =>
            array(
                'code' => 330201,
                'area_name' => '天元区',
                'name' => '天元区',
            ),
            430221 =>
            array(
                'code' => 330206,
                'area_name' => '株洲县',
                'name' => '株洲县',
            ),
            430223 =>
            array(
                'code' => '',
                'area_name' => '攸县',
                'name' => '',
            ),
            430224 =>
            array(
                'code' => 330208,
                'area_name' => '茶陵县',
                'name' => '茶陵县',
            ),
            430225 =>
            array(
                'code' => 330207,
                'area_name' => '炎陵县',
                'name' => '炎陵县',
            ),
            430281 =>
            array(
                'code' => 330205,
                'area_name' => '醴陵市',
                'name' => '醴陵市',
            ),
            430302 =>
            array(
                'code' => 330301,
                'area_name' => '雨湖区',
                'name' => '雨湖区',
            ),
            430304 =>
            array(
                'code' => 330302,
                'area_name' => '岳塘区',
                'name' => '岳塘区',
            ),
            430321 =>
            array(
                'code' => 330305,
                'area_name' => '湘潭县',
                'name' => '湘潭县',
            ),
            430381 =>
            array(
                'code' => 330303,
                'area_name' => '湘乡市',
                'name' => '湘乡市',
            ),
            430382 =>
            array(
                'code' => 330304,
                'area_name' => '韶山市',
                'name' => '韶山市',
            ),
            430405 =>
            array(
                'code' => 330403,
                'area_name' => '珠晖区',
                'name' => '珠晖区',
            ),
            430406 =>
            array(
                'code' => 330402,
                'area_name' => '雁峰区',
                'name' => '雁峰区',
            ),
            430407 =>
            array(
                'code' => 330401,
                'area_name' => '石鼓区',
                'name' => '石鼓区',
            ),
            430408 =>
            array(
                'code' => 330404,
                'area_name' => '蒸湘区',
                'name' => '蒸湘区',
            ),
            430412 =>
            array(
                'code' => 330405,
                'area_name' => '南岳区',
                'name' => '南岳区',
            ),
            430421 =>
            array(
                'code' => 330408,
                'area_name' => '衡阳县',
                'name' => '衡阳县',
            ),
            430422 =>
            array(
                'code' => 330411,
                'area_name' => '衡南县',
                'name' => '衡南县',
            ),
            430423 =>
            array(
                'code' => 330410,
                'area_name' => '衡山县',
                'name' => '衡山县',
            ),
            430424 =>
            array(
                'code' => 330409,
                'area_name' => '衡东县',
                'name' => '衡东县',
            ),
            430426 =>
            array(
                'code' => 330412,
                'area_name' => '祁东县',
                'name' => '祁东县',
            ),
            430481 =>
            array(
                'code' => 330406,
                'area_name' => '耒阳市',
                'name' => '耒阳市',
            ),
            430482 =>
            array(
                'code' => 330407,
                'area_name' => '常宁市',
                'name' => '常宁市',
            ),
            430502 =>
            array(
                'code' => 330501,
                'area_name' => '双清区',
                'name' => '双清区',
            ),
            430503 =>
            array(
                'code' => 330502,
                'area_name' => '大祥区',
                'name' => '大祥区',
            ),
            430511 =>
            array(
                'code' => 330503,
                'area_name' => '北塔区',
                'name' => '北塔区',
            ),
            430521 =>
            array(
                'code' => 330505,
                'area_name' => '邵东县',
                'name' => '邵东县',
            ),
            430522 =>
            array(
                'code' => 330507,
                'area_name' => '新邵县',
                'name' => '新邵县',
            ),
            430523 =>
            array(
                'code' => 330510,
                'area_name' => '邵阳县',
                'name' => '邵阳县',
            ),
            430524 =>
            array(
                'code' => 330511,
                'area_name' => '隆回县',
                'name' => '隆回县',
            ),
            430525 =>
            array(
                'code' => 330506,
                'area_name' => '洞口县',
                'name' => '洞口县',
            ),
            430527 =>
            array(
                'code' => 330508,
                'area_name' => '绥宁县',
                'name' => '绥宁县',
            ),
            430528 =>
            array(
                'code' => 330509,
                'area_name' => '新宁县',
                'name' => '新宁县',
            ),
            430529 =>
            array(
                'code' => 330512,
                'area_name' => '城步苗族自治县',
                'name' => '城步苗族自治县',
            ),
            430581 =>
            array(
                'code' => 330504,
                'area_name' => '武冈市',
                'name' => '武冈市',
            ),
            430602 =>
            array(
                'code' => 330601,
                'area_name' => '岳阳楼区',
                'name' => '岳阳楼区',
            ),
            430603 =>
            array(
                'code' => 330603,
                'area_name' => '云溪区',
                'name' => '云溪区',
            ),
            430611 =>
            array(
                'code' => 330602,
                'area_name' => '君山区',
                'name' => '君山区',
            ),
            430621 =>
            array(
                'code' => 330606,
                'area_name' => '岳阳县',
                'name' => '岳阳县',
            ),
            430623 =>
            array(
                'code' => 330609,
                'area_name' => '华容县',
                'name' => '华容县',
            ),
            430624 =>
            array(
                'code' => 330607,
                'area_name' => '湘阴县',
                'name' => '湘阴县',
            ),
            430626 =>
            array(
                'code' => 330608,
                'area_name' => '平江县',
                'name' => '平江县',
            ),
            430681 =>
            array(
                'code' => 330605,
                'area_name' => '汨罗市',
                'name' => '汨罗市',
            ),
            430682 =>
            array(
                'code' => 330604,
                'area_name' => '临湘市',
                'name' => '临湘市',
            ),
            430702 =>
            array(
                'code' => 330701,
                'area_name' => '武陵区',
                'name' => '武陵区',
            ),
            430703 =>
            array(
                'code' => 330702,
                'area_name' => '鼎城区',
                'name' => '鼎城区',
            ),
            430721 =>
            array(
                'code' => 330708,
                'area_name' => '安乡县',
                'name' => '安乡县',
            ),
            430722 =>
            array(
                'code' => 330707,
                'area_name' => '汉寿县',
                'name' => '汉寿县',
            ),
            430723 =>
            array(
                'code' => '',
                'area_name' => '澧县',
                'name' => '',
            ),
            430724 =>
            array(
                'code' => 330705,
                'area_name' => '临澧县',
                'name' => '临澧县',
            ),
            430725 =>
            array(
                'code' => 330706,
                'area_name' => '桃源县',
                'name' => '桃源县',
            ),
            430726 =>
            array(
                'code' => 330709,
                'area_name' => '石门县',
                'name' => '石门县',
            ),
            430781 =>
            array(
                'code' => 330703,
                'area_name' => '津市市',
                'name' => '津市市',
            ),
            430802 =>
            array(
                'code' => 330801,
                'area_name' => '永定区',
                'name' => '永定区',
            ),
            430811 =>
            array(
                'code' => 330802,
                'area_name' => '武陵源区',
                'name' => '武陵源区',
            ),
            430821 =>
            array(
                'code' => 330803,
                'area_name' => '慈利县',
                'name' => '慈利县',
            ),
            430822 =>
            array(
                'code' => 330804,
                'area_name' => '桑植县',
                'name' => '桑植县',
            ),
            430902 =>
            array(
                'code' => 330902,
                'area_name' => '资阳区',
                'name' => '资阳区',
            ),
            430903 =>
            array(
                'code' => 330901,
                'area_name' => '赫山区',
                'name' => '赫山区',
            ),
            430921 =>
            array(
                'code' => '',
                'area_name' => '南县',
                'name' => '',
            ),
            430922 =>
            array(
                'code' => 330904,
                'area_name' => '桃江县',
                'name' => '桃江县',
            ),
            430923 =>
            array(
                'code' => 330906,
                'area_name' => '安化县',
                'name' => '安化县',
            ),
            430981 =>
            array(
                'code' => 330903,
                'area_name' => '沅江市',
                'name' => '沅江市',
            ),
            431002 =>
            array(
                'code' => 331001,
                'area_name' => '北湖区',
                'name' => '北湖区',
            ),
            431003 =>
            array(
                'code' => 331002,
                'area_name' => '苏仙区',
                'name' => '苏仙区',
            ),
            431021 =>
            array(
                'code' => 331011,
                'area_name' => '桂阳县',
                'name' => '桂阳县',
            ),
            431022 =>
            array(
                'code' => 331004,
                'area_name' => '宜章县',
                'name' => '宜章县',
            ),
            431023 =>
            array(
                'code' => 331010,
                'area_name' => '永兴县',
                'name' => '永兴县',
            ),
            431024 =>
            array(
                'code' => 331007,
                'area_name' => '嘉禾县',
                'name' => '嘉禾县',
            ),
            431025 =>
            array(
                'code' => 331008,
                'area_name' => '临武县',
                'name' => '临武县',
            ),
            431026 =>
            array(
                'code' => 331005,
                'area_name' => '汝城县',
                'name' => '汝城县',
            ),
            431027 =>
            array(
                'code' => 331009,
                'area_name' => '桂东县',
                'name' => '桂东县',
            ),
            431028 =>
            array(
                'code' => 331006,
                'area_name' => '安仁县',
                'name' => '安仁县',
            ),
            431081 =>
            array(
                'code' => 331003,
                'area_name' => '资兴市',
                'name' => '资兴市',
            ),
            431102 =>
            array(
                'code' => '',
                'area_name' => '零陵区',
                'name' => '',
            ),
            431103 =>
            array(
                'code' => 331101,
                'area_name' => '冷水滩区',
                'name' => '冷水滩区',
            ),
            431121 =>
            array(
                'code' => 331103,
                'area_name' => '祁阳县',
                'name' => '祁阳县',
            ),
            431122 =>
            array(
                'code' => 331107,
                'area_name' => '东安县',
                'name' => '东安县',
            ),
            431123 =>
            array(
                'code' => 331110,
                'area_name' => '双牌县',
                'name' => '双牌县',
            ),
            431124 =>
            array(
                'code' => '',
                'area_name' => '道县',
                'name' => '',
            ),
            431125 =>
            array(
                'code' => 331108,
                'area_name' => '江永县',
                'name' => '江永县',
            ),
            431126 =>
            array(
                'code' => 331105,
                'area_name' => '宁远县',
                'name' => '宁远县',
            ),
            431127 =>
            array(
                'code' => 331104,
                'area_name' => '蓝山县',
                'name' => '蓝山县',
            ),
            431128 =>
            array(
                'code' => 331106,
                'area_name' => '新田县',
                'name' => '新田县',
            ),
            431129 =>
            array(
                'code' => 331111,
                'area_name' => '江华瑶族自治县',
                'name' => '江华瑶族自治县',
            ),
            431202 =>
            array(
                'code' => 331201,
                'area_name' => '鹤城区',
                'name' => '鹤城区',
            ),
            431221 =>
            array(
                'code' => 331207,
                'area_name' => '中方县',
                'name' => '中方县',
            ),
            431222 =>
            array(
                'code' => 331204,
                'area_name' => '沅陵县',
                'name' => '沅陵县',
            ),
            431223 =>
            array(
                'code' => 331205,
                'area_name' => '辰溪县',
                'name' => '辰溪县',
            ),
            431224 =>
            array(
                'code' => 331206,
                'area_name' => '溆浦县',
                'name' => '溆浦县',
            ),
            431225 =>
            array(
                'code' => 331203,
                'area_name' => '会同县',
                'name' => '会同县',
            ),
            431226 =>
            array(
                'code' => 331212,
                'area_name' => '麻阳苗族自治县',
                'name' => '麻阳苗族自治县',
            ),
            431227 =>
            array(
                'code' => 331208,
                'area_name' => '新晃侗族自治县',
                'name' => '新晃侗族自治县',
            ),
            431228 =>
            array(
                'code' => 331209,
                'area_name' => '芷江侗族自治县',
                'name' => '芷江侗族自治县',
            ),
            431229 =>
            array(
                'code' => 331211,
                'area_name' => '靖州苗族侗族自治县',
                'name' => '靖州苗族侗族自治县',
            ),
            431230 =>
            array(
                'code' => 331210,
                'area_name' => '通道侗族自治县',
                'name' => '通道侗族自治县',
            ),
            431281 =>
            array(
                'code' => 331202,
                'area_name' => '洪江市',
                'name' => '洪江市',
            ),
            431302 =>
            array(
                'code' => 331301,
                'area_name' => '娄星区',
                'name' => '娄星区',
            ),
            431321 =>
            array(
                'code' => 331305,
                'area_name' => '双峰县',
                'name' => '双峰县',
            ),
            431322 =>
            array(
                'code' => 331304,
                'area_name' => '新化县',
                'name' => '新化县',
            ),
            431381 =>
            array(
                'code' => 331302,
                'area_name' => '冷水江市',
                'name' => '冷水江市',
            ),
            431382 =>
            array(
                'code' => 331303,
                'area_name' => '涟源市',
                'name' => '涟源市',
            ),
            433101 =>
            array(
                'code' => 331401,
                'area_name' => '吉首市',
                'name' => '吉首市',
            ),
            433122 =>
            array(
                'code' => 331406,
                'area_name' => '泸溪县',
                'name' => '泸溪县',
            ),
            433123 =>
            array(
                'code' => 331405,
                'area_name' => '凤凰县',
                'name' => '凤凰县',
            ),
            433124 =>
            array(
                'code' => 331408,
                'area_name' => '花垣县',
                'name' => '花垣县',
            ),
            433125 =>
            array(
                'code' => 331407,
                'area_name' => '保靖县',
                'name' => '保靖县',
            ),
            433126 =>
            array(
                'code' => 331402,
                'area_name' => '古丈县',
                'name' => '古丈县',
            ),
            433127 =>
            array(
                'code' => 331404,
                'area_name' => '永顺县',
                'name' => '永顺县',
            ),
            433130 =>
            array(
                'code' => 331403,
                'area_name' => '龙山县',
                'name' => '龙山县',
            ),
            440103 =>
            array(
                'code' => 340104,
                'area_name' => '荔湾区',
                'name' => '荔湾区',
            ),
            440104 =>
            array(
                'code' => 340101,
                'area_name' => '越秀区',
                'name' => '越秀区',
            ),
            440105 =>
            array(
                'code' => 340103,
                'area_name' => '海珠区',
                'name' => '海珠区',
            ),
            440106 =>
            array(
                'code' => 340105,
                'area_name' => '天河区',
                'name' => '天河区',
            ),
            440111 =>
            array(
                'code' => 340106,
                'area_name' => '白云区',
                'name' => '白云区',
            ),
            440112 =>
            array(
                'code' => 340107,
                'area_name' => '黄埔区',
                'name' => '黄埔区',
            ),
            440113 =>
            array(
                'code' => 340110,
                'area_name' => '番禺区',
                'name' => '番禺区',
            ),
            440114 =>
            array(
                'code' => 340109,
                'area_name' => '花都区',
                'name' => '花都区',
            ),
            440115 =>
            array(
                'code' => '',
                'area_name' => '南沙区',
                'name' => '',
            ),
            440116 =>
            array(
                'code' => '',
                'area_name' => '萝岗区',
                'name' => '',
            ),
            440183 =>
            array(
                'code' => 340112,
                'area_name' => '增城市',
                'name' => '增城市',
            ),
            440184 =>
            array(
                'code' => 340111,
                'area_name' => '从化市',
                'name' => '从化市',
            ),
            440203 =>
            array(
                'code' => 340503,
                'area_name' => '武江区',
                'name' => '武江区',
            ),
            440204 =>
            array(
                'code' => 340502,
                'area_name' => '浈江区',
                'name' => '浈江区',
            ),
            440205 =>
            array(
                'code' => '',
                'area_name' => '曲江区',
                'name' => '',
            ),
            440222 =>
            array(
                'code' => 340507,
                'area_name' => '始兴县',
                'name' => '始兴县',
            ),
            440224 =>
            array(
                'code' => 340506,
                'area_name' => '仁化县',
                'name' => '仁化县',
            ),
            440229 =>
            array(
                'code' => 340508,
                'area_name' => '翁源县',
                'name' => '翁源县',
            ),
            440232 =>
            array(
                'code' => 340511,
                'area_name' => '乳源瑶族自治县',
                'name' => '乳源瑶族自治县',
            ),
            440233 =>
            array(
                'code' => 340510,
                'area_name' => '新丰县',
                'name' => '新丰县',
            ),
            440281 =>
            array(
                'code' => 340504,
                'area_name' => '乐昌市',
                'name' => '乐昌市',
            ),
            440282 =>
            array(
                'code' => 340505,
                'area_name' => '南雄市',
                'name' => '南雄市',
            ),
            440303 =>
            array(
                'code' => 340202,
                'area_name' => '罗湖区',
                'name' => '罗湖区',
            ),
            440304 =>
            array(
                'code' => 340201,
                'area_name' => '福田区',
                'name' => '福田区',
            ),
            440305 =>
            array(
                'code' => 340203,
                'area_name' => '南山区',
                'name' => '南山区',
            ),
            440306 =>
            array(
                'code' => 340204,
                'area_name' => '宝安区',
                'name' => '宝安区',
            ),
            440307 =>
            array(
                'code' => 340205,
                'area_name' => '龙岗区',
                'name' => '龙岗区',
            ),
            440308 =>
            array(
                'code' => 340206,
                'area_name' => '盐田区',
                'name' => '盐田区',
            ),
            440320 =>
            array(
                'code' => '',
                'area_name' => '光明新区',
                'name' => '',
            ),
            440321 =>
            array(
                'code' => '',
                'area_name' => '坪山新区',
                'name' => '',
            ),
            440322 =>
            array(
                'code' => '',
                'area_name' => '大鹏新区',
                'name' => '',
            ),
            440323 =>
            array(
                'code' => '',
                'area_name' => '龙华新区',
                'name' => '',
            ),
            440402 =>
            array(
                'code' => 340301,
                'area_name' => '香洲区',
                'name' => '香洲区',
            ),
            440403 =>
            array(
                'code' => 340302,
                'area_name' => '斗门区',
                'name' => '斗门区',
            ),
            440404 =>
            array(
                'code' => 340303,
                'area_name' => '金湾区',
                'name' => '金湾区',
            ),
            440507 =>
            array(
                'code' => 340402,
                'area_name' => '龙湖区',
                'name' => '龙湖区',
            ),
            440511 =>
            array(
                'code' => '',
                'area_name' => '金平区',
                'name' => '',
            ),
            440512 =>
            array(
                'code' => '',
                'area_name' => '濠江区',
                'name' => '',
            ),
            440513 =>
            array(
                'code' => '',
                'area_name' => '潮阳区',
                'name' => '',
            ),
            440514 =>
            array(
                'code' => '',
                'area_name' => '潮南区',
                'name' => '',
            ),
            440515 =>
            array(
                'code' => '',
                'area_name' => '澄海区',
                'name' => '',
            ),
            440523 =>
            array(
                'code' => 340408,
                'area_name' => '南澳县',
                'name' => '南澳县',
            ),
            440604 =>
            array(
                'code' => 341301,
                'area_name' => '禅城区',
                'name' => '禅城区',
            ),
            440605 =>
            array(
                'code' => 341302,
                'area_name' => '南海区',
                'name' => '南海区',
            ),
            440606 =>
            array(
                'code' => 341303,
                'area_name' => '顺德区',
                'name' => '顺德区',
            ),
            440607 =>
            array(
                'code' => 341304,
                'area_name' => '三水区',
                'name' => '三水区',
            ),
            440608 =>
            array(
                'code' => 341305,
                'area_name' => '高明区',
                'name' => '高明区',
            ),
            440703 =>
            array(
                'code' => 341202,
                'area_name' => '蓬江区',
                'name' => '蓬江区',
            ),
            440704 =>
            array(
                'code' => 341201,
                'area_name' => '江海区',
                'name' => '江海区',
            ),
            440705 =>
            array(
                'code' => 341203,
                'area_name' => '新会区',
                'name' => '新会区',
            ),
            440781 =>
            array(
                'code' => 341204,
                'area_name' => '台山市',
                'name' => '台山市',
            ),
            440783 =>
            array(
                'code' => 341205,
                'area_name' => '开平市',
                'name' => '开平市',
            ),
            440784 =>
            array(
                'code' => 341206,
                'area_name' => '鹤山市',
                'name' => '鹤山市',
            ),
            440785 =>
            array(
                'code' => 341207,
                'area_name' => '恩平市',
                'name' => '恩平市',
            ),
            440802 =>
            array(
                'code' => 341501,
                'area_name' => '赤坎区',
                'name' => '赤坎区',
            ),
            440803 =>
            array(
                'code' => 341502,
                'area_name' => '霞山区',
                'name' => '霞山区',
            ),
            440804 =>
            array(
                'code' => 341503,
                'area_name' => '坡头区',
                'name' => '坡头区',
            ),
            440811 =>
            array(
                'code' => 341504,
                'area_name' => '麻章区',
                'name' => '麻章区',
            ),
            440823 =>
            array(
                'code' => 341508,
                'area_name' => '遂溪县',
                'name' => '遂溪县',
            ),
            440825 =>
            array(
                'code' => 341509,
                'area_name' => '徐闻县',
                'name' => '徐闻县',
            ),
            440881 =>
            array(
                'code' => 341505,
                'area_name' => '廉江市',
                'name' => '廉江市',
            ),
            440882 =>
            array(
                'code' => 341506,
                'area_name' => '雷州市',
                'name' => '雷州市',
            ),
            440883 =>
            array(
                'code' => 341507,
                'area_name' => '吴川市',
                'name' => '吴川市',
            ),
            440902 =>
            array(
                'code' => 341601,
                'area_name' => '茂南区',
                'name' => '茂南区',
            ),
            440903 =>
            array(
                'code' => 341602,
                'area_name' => '茂港区',
                'name' => '茂港区',
            ),
            440923 =>
            array(
                'code' => 341606,
                'area_name' => '电白县',
                'name' => '电白县',
            ),
            440981 =>
            array(
                'code' => 341603,
                'area_name' => '高州市',
                'name' => '高州市',
            ),
            440982 =>
            array(
                'code' => 341604,
                'area_name' => '化州市',
                'name' => '化州市',
            ),
            440983 =>
            array(
                'code' => 341605,
                'area_name' => '信宜市',
                'name' => '信宜市',
            ),
            441202 =>
            array(
                'code' => 341701,
                'area_name' => '端州区',
                'name' => '端州区',
            ),
            441203 =>
            array(
                'code' => 341702,
                'area_name' => '鼎湖区',
                'name' => '鼎湖区',
            ),
            441223 =>
            array(
                'code' => 341705,
                'area_name' => '广宁县',
                'name' => '广宁县',
            ),
            441224 =>
            array(
                'code' => 341708,
                'area_name' => '怀集县',
                'name' => '怀集县',
            ),
            441225 =>
            array(
                'code' => 341707,
                'area_name' => '封开县',
                'name' => '封开县',
            ),
            441226 =>
            array(
                'code' => 341706,
                'area_name' => '德庆县',
                'name' => '德庆县',
            ),
            441283 =>
            array(
                'code' => 341703,
                'area_name' => '高要市',
                'name' => '高要市',
            ),
            441284 =>
            array(
                'code' => 341704,
                'area_name' => '四会市',
                'name' => '四会市',
            ),
            441302 =>
            array(
                'code' => 340801,
                'area_name' => '惠城区',
                'name' => '惠城区',
            ),
            441303 =>
            array(
                'code' => '',
                'area_name' => '惠阳区',
                'name' => '',
            ),
            441322 =>
            array(
                'code' => 340804,
                'area_name' => '博罗县',
                'name' => '博罗县',
            ),
            441323 =>
            array(
                'code' => 340803,
                'area_name' => '惠东县',
                'name' => '惠东县',
            ),
            441324 =>
            array(
                'code' => 340805,
                'area_name' => '龙门县',
                'name' => '龙门县',
            ),
            441402 =>
            array(
                'code' => 340701,
                'area_name' => '梅江区',
                'name' => '梅江区',
            ),
            441421 =>
            array(
                'code' => '',
                'area_name' => '梅县',
                'name' => '',
            ),
            441422 =>
            array(
                'code' => 340705,
                'area_name' => '大埔县',
                'name' => '大埔县',
            ),
            441423 =>
            array(
                'code' => 340706,
                'area_name' => '丰顺县',
                'name' => '丰顺县',
            ),
            441424 =>
            array(
                'code' => 340707,
                'area_name' => '五华县',
                'name' => '五华县',
            ),
            441426 =>
            array(
                'code' => 340708,
                'area_name' => '平远县',
                'name' => '平远县',
            ),
            441427 =>
            array(
                'code' => 340704,
                'area_name' => '蕉岭县',
                'name' => '蕉岭县',
            ),
            441481 =>
            array(
                'code' => 340702,
                'area_name' => '兴宁市',
                'name' => '兴宁市',
            ),
            441502 =>
            array(
                'code' => '',
                'area_name' => '城区',
                'name' => '',
            ),
            441521 =>
            array(
                'code' => 340903,
                'area_name' => '海丰县',
                'name' => '海丰县',
            ),
            441523 =>
            array(
                'code' => 340904,
                'area_name' => '陆河县',
                'name' => '陆河县',
            ),
            441581 =>
            array(
                'code' => 340902,
                'area_name' => '陆丰市',
                'name' => '陆丰市',
            ),
            441602 =>
            array(
                'code' => 340601,
                'area_name' => '源城区',
                'name' => '源城区',
            ),
            441621 =>
            array(
                'code' => 340604,
                'area_name' => '紫金县',
                'name' => '紫金县',
            ),
            441622 =>
            array(
                'code' => 340603,
                'area_name' => '龙川县',
                'name' => '龙川县',
            ),
            441623 =>
            array(
                'code' => 340605,
                'area_name' => '连平县',
                'name' => '连平县',
            ),
            441624 =>
            array(
                'code' => 340602,
                'area_name' => '和平县',
                'name' => '和平县',
            ),
            441625 =>
            array(
                'code' => 340606,
                'area_name' => '东源县',
                'name' => '东源县',
            ),
            441702 =>
            array(
                'code' => 341401,
                'area_name' => '江城区',
                'name' => '江城区',
            ),
            441721 =>
            array(
                'code' => 341403,
                'area_name' => '阳西县',
                'name' => '阳西县',
            ),
            441723 =>
            array(
                'code' => 341404,
                'area_name' => '阳东县',
                'name' => '阳东县',
            ),
            441781 =>
            array(
                'code' => 341402,
                'area_name' => '阳春市',
                'name' => '阳春市',
            ),
            441802 =>
            array(
                'code' => 341801,
                'area_name' => '清城区',
                'name' => '清城区',
            ),
            441821 =>
            array(
                'code' => 341804,
                'area_name' => '佛冈县',
                'name' => '佛冈县',
            ),
            441823 =>
            array(
                'code' => 341805,
                'area_name' => '阳山县',
                'name' => '阳山县',
            ),
            441825 =>
            array(
                'code' => 341807,
                'area_name' => '连山壮族瑶族自治县',
                'name' => '连山壮族瑶族自治县',
            ),
            441826 =>
            array(
                'code' => 341808,
                'area_name' => '连南瑶族自治县',
                'name' => '连南瑶族自治县',
            ),
            441827 =>
            array(
                'code' => '',
                'area_name' => '清新区',
                'name' => '',
            ),
            441881 =>
            array(
                'code' => 341802,
                'area_name' => '英德市',
                'name' => '英德市',
            ),
            441882 =>
            array(
                'code' => 341803,
                'area_name' => '连州市',
                'name' => '连州市',
            ),
            445102 =>
            array(
                'code' => 341901,
                'area_name' => '湘桥区',
                'name' => '湘桥区',
            ),
            445121 =>
            array(
                'code' => '',
                'area_name' => '潮安区',
                'name' => '',
            ),
            445122 =>
            array(
                'code' => 341903,
                'area_name' => '饶平县',
                'name' => '饶平县',
            ),
            445202 =>
            array(
                'code' => 342001,
                'area_name' => '榕城区',
                'name' => '榕城区',
            ),
            445221 =>
            array(
                'code' => '',
                'area_name' => '揭东区',
                'name' => '',
            ),
            445222 =>
            array(
                'code' => 342004,
                'area_name' => '揭西县',
                'name' => '揭西县',
            ),
            445224 =>
            array(
                'code' => 342005,
                'area_name' => '惠来县',
                'name' => '惠来县',
            ),
            445281 =>
            array(
                'code' => 342002,
                'area_name' => '普宁市',
                'name' => '普宁市',
            ),
            445302 =>
            array(
                'code' => 342101,
                'area_name' => '云城区',
                'name' => '云城区',
            ),
            445321 =>
            array(
                'code' => 342104,
                'area_name' => '新兴县',
                'name' => '新兴县',
            ),
            445322 =>
            array(
                'code' => 342105,
                'area_name' => '郁南县',
                'name' => '郁南县',
            ),
            445323 =>
            array(
                'code' => 342103,
                'area_name' => '云安县',
                'name' => '云安县',
            ),
            445381 =>
            array(
                'code' => 342102,
                'area_name' => '罗定市',
                'name' => '罗定市',
            ),
            450102 =>
            array(
                'code' => 160102,
                'area_name' => '兴宁区',
                'name' => '兴宁区',
            ),
            450103 =>
            array(
                'code' => 160101,
                'area_name' => '青秀区',
                'name' => '青秀区',
            ),
            450105 =>
            array(
                'code' => 160105,
                'area_name' => '江南区',
                'name' => '江南区',
            ),
            450107 =>
            array(
                'code' => 160103,
                'area_name' => '西乡塘区',
                'name' => '西乡塘区',
            ),
            450108 =>
            array(
                'code' => 160104,
                'area_name' => '良庆区',
                'name' => '良庆区',
            ),
            450109 =>
            array(
                'code' => 160106,
                'area_name' => '邕宁区',
                'name' => '邕宁区',
            ),
            450122 =>
            array(
                'code' => 160107,
                'area_name' => '武鸣县',
                'name' => '武鸣县',
            ),
            450123 =>
            array(
                'code' => 160108,
                'area_name' => '隆安县',
                'name' => '隆安县',
            ),
            450124 =>
            array(
                'code' => 160109,
                'area_name' => '马山县',
                'name' => '马山县',
            ),
            450125 =>
            array(
                'code' => 160110,
                'area_name' => '上林县',
                'name' => '上林县',
            ),
            450126 =>
            array(
                'code' => 160111,
                'area_name' => '宾阳县',
                'name' => '宾阳县',
            ),
            450127 =>
            array(
                'code' => '',
                'area_name' => '横县',
                'name' => '',
            ),
            450202 =>
            array(
                'code' => 160201,
                'area_name' => '城中区',
                'name' => '城中区',
            ),
            450203 =>
            array(
                'code' => 160202,
                'area_name' => '鱼峰区',
                'name' => '鱼峰区',
            ),
            450204 =>
            array(
                'code' => 160204,
                'area_name' => '柳南区',
                'name' => '柳南区',
            ),
            450205 =>
            array(
                'code' => 160203,
                'area_name' => '柳北区',
                'name' => '柳北区',
            ),
            450221 =>
            array(
                'code' => 160205,
                'area_name' => '柳江县',
                'name' => '柳江县',
            ),
            450222 =>
            array(
                'code' => 160206,
                'area_name' => '柳城县',
                'name' => '柳城县',
            ),
            450223 =>
            array(
                'code' => 160207,
                'area_name' => '鹿寨县',
                'name' => '鹿寨县',
            ),
            450224 =>
            array(
                'code' => 160208,
                'area_name' => '融安县',
                'name' => '融安县',
            ),
            450225 =>
            array(
                'code' => 160209,
                'area_name' => '融水苗族自治县',
                'name' => '融水苗族自治县',
            ),
            450226 =>
            array(
                'code' => 160210,
                'area_name' => '三江侗族自治县',
                'name' => '三江侗族自治县',
            ),
            450302 =>
            array(
                'code' => 160302,
                'area_name' => '秀峰区',
                'name' => '秀峰区',
            ),
            450303 =>
            array(
                'code' => 160303,
                'area_name' => '叠彩区',
                'name' => '叠彩区',
            ),
            450304 =>
            array(
                'code' => 160301,
                'area_name' => '象山区',
                'name' => '象山区',
            ),
            450305 =>
            array(
                'code' => 160304,
                'area_name' => '七星区',
                'name' => '七星区',
            ),
            450311 =>
            array(
                'code' => 160305,
                'area_name' => '雁山区',
                'name' => '雁山区',
            ),
            450321 =>
            array(
                'code' => 160306,
                'area_name' => '阳朔县',
                'name' => '阳朔县',
            ),
            450322 =>
            array(
                'code' => '',
                'area_name' => '临桂区',
                'name' => '',
            ),
            450323 =>
            array(
                'code' => 160308,
                'area_name' => '灵川县',
                'name' => '灵川县',
            ),
            450324 =>
            array(
                'code' => 160309,
                'area_name' => '全州县',
                'name' => '全州县',
            ),
            450325 =>
            array(
                'code' => 160311,
                'area_name' => '兴安县',
                'name' => '兴安县',
            ),
            450326 =>
            array(
                'code' => 160315,
                'area_name' => '永福县',
                'name' => '永福县',
            ),
            450327 =>
            array(
                'code' => 160312,
                'area_name' => '灌阳县',
                'name' => '灌阳县',
            ),
            450328 =>
            array(
                'code' => 160316,
                'area_name' => '龙胜各族自治县',
                'name' => '龙胜各族自治县',
            ),
            450329 =>
            array(
                'code' => 160314,
                'area_name' => '资源县',
                'name' => '资源县',
            ),
            450330 =>
            array(
                'code' => 160310,
                'area_name' => '平乐县',
                'name' => '平乐县',
            ),
            450331 =>
            array(
                'code' => 160313,
                'area_name' => '荔浦县',
                'name' => '荔浦县',
            ),
            450332 =>
            array(
                'code' => 160317,
                'area_name' => '恭城瑶族自治县',
                'name' => '恭城瑶族自治县',
            ),
            450403 =>
            array(
                'code' => 160401,
                'area_name' => '万秀区',
                'name' => '万秀区',
            ),
            450405 =>
            array(
                'code' => 160403,
                'area_name' => '长洲区',
                'name' => '长洲区',
            ),
            450406 =>
            array(
                'code' => '',
                'area_name' => '龙圩区',
                'name' => '',
            ),
            450421 =>
            array(
                'code' => 160405,
                'area_name' => '苍梧县',
                'name' => '苍梧县',
            ),
            450422 =>
            array(
                'code' => '',
                'area_name' => '藤县',
                'name' => '',
            ),
            450423 =>
            array(
                'code' => 160407,
                'area_name' => '蒙山县',
                'name' => '蒙山县',
            ),
            450481 =>
            array(
                'code' => 160404,
                'area_name' => '岑溪市',
                'name' => '岑溪市',
            ),
            450502 =>
            array(
                'code' => 160501,
                'area_name' => '海城区',
                'name' => '海城区',
            ),
            450503 =>
            array(
                'code' => 160502,
                'area_name' => '银海区',
                'name' => '银海区',
            ),
            450512 =>
            array(
                'code' => 160503,
                'area_name' => '铁山港区',
                'name' => '铁山港区',
            ),
            450521 =>
            array(
                'code' => 160504,
                'area_name' => '合浦县',
                'name' => '合浦县',
            ),
            450602 =>
            array(
                'code' => 160601,
                'area_name' => '港口区',
                'name' => '港口区',
            ),
            450603 =>
            array(
                'code' => 160602,
                'area_name' => '防城区',
                'name' => '防城区',
            ),
            450621 =>
            array(
                'code' => 160604,
                'area_name' => '上思县',
                'name' => '上思县',
            ),
            450681 =>
            array(
                'code' => 160603,
                'area_name' => '东兴市',
                'name' => '东兴市',
            ),
            450702 =>
            array(
                'code' => 160701,
                'area_name' => '钦南区',
                'name' => '钦南区',
            ),
            450703 =>
            array(
                'code' => 160702,
                'area_name' => '钦北区',
                'name' => '钦北区',
            ),
            450721 =>
            array(
                'code' => 160703,
                'area_name' => '灵山县',
                'name' => '灵山县',
            ),
            450722 =>
            array(
                'code' => 160704,
                'area_name' => '浦北县',
                'name' => '浦北县',
            ),
            450802 =>
            array(
                'code' => 160801,
                'area_name' => '港北区',
                'name' => '港北区',
            ),
            450803 =>
            array(
                'code' => 160802,
                'area_name' => '港南区',
                'name' => '港南区',
            ),
            450804 =>
            array(
                'code' => 160803,
                'area_name' => '覃塘区',
                'name' => '覃塘区',
            ),
            450821 =>
            array(
                'code' => 160805,
                'area_name' => '平南县',
                'name' => '平南县',
            ),
            450881 =>
            array(
                'code' => 160804,
                'area_name' => '桂平市',
                'name' => '桂平市',
            ),
            450902 =>
            array(
                'code' => 160901,
                'area_name' => '玉州区',
                'name' => '玉州区',
            ),
            450903 =>
            array(
                'code' => '',
                'area_name' => '福绵区',
                'name' => '',
            ),
            450921 =>
            array(
                'code' => '',
                'area_name' => '容县',
                'name' => '',
            ),
            450922 =>
            array(
                'code' => 160904,
                'area_name' => '陆川县',
                'name' => '陆川县',
            ),
            450923 =>
            array(
                'code' => 160905,
                'area_name' => '博白县',
                'name' => '博白县',
            ),
            450924 =>
            array(
                'code' => 160906,
                'area_name' => '兴业县',
                'name' => '兴业县',
            ),
            450981 =>
            array(
                'code' => 160902,
                'area_name' => '北流市',
                'name' => '北流市',
            ),
            451002 =>
            array(
                'code' => 161001,
                'area_name' => '右江区',
                'name' => '右江区',
            ),
            451021 =>
            array(
                'code' => 161008,
                'area_name' => '田阳县',
                'name' => '田阳县',
            ),
            451022 =>
            array(
                'code' => 161010,
                'area_name' => '田东县',
                'name' => '田东县',
            ),
            451023 =>
            array(
                'code' => 161003,
                'area_name' => '平果县',
                'name' => '平果县',
            ),
            451024 =>
            array(
                'code' => 161006,
                'area_name' => '德保县',
                'name' => '德保县',
            ),
            451025 =>
            array(
                'code' => 161009,
                'area_name' => '靖西县',
                'name' => '靖西县',
            ),
            451026 =>
            array(
                'code' => 161011,
                'area_name' => '那坡县',
                'name' => '那坡县',
            ),
            451027 =>
            array(
                'code' => 161002,
                'area_name' => '凌云县',
                'name' => '凌云县',
            ),
            451028 =>
            array(
                'code' => 161005,
                'area_name' => '乐业县',
                'name' => '乐业县',
            ),
            451029 =>
            array(
                'code' => 161007,
                'area_name' => '田林县',
                'name' => '田林县',
            ),
            451030 =>
            array(
                'code' => 161004,
                'area_name' => '西林县',
                'name' => '西林县',
            ),
            451031 =>
            array(
                'code' => 161012,
                'area_name' => '隆林各族自治县',
                'name' => '隆林各族自治县',
            ),
            451102 =>
            array(
                'code' => 161101,
                'area_name' => '八步区',
                'name' => '八步区',
            ),
            451119 =>
            array(
                'code' => '',
                'area_name' => '平桂管理区',
                'name' => '',
            ),
            451121 =>
            array(
                'code' => 161103,
                'area_name' => '昭平县',
                'name' => '昭平县',
            ),
            451122 =>
            array(
                'code' => 161102,
                'area_name' => '钟山县',
                'name' => '钟山县',
            ),
            451123 =>
            array(
                'code' => 161104,
                'area_name' => '富川瑶族自治县',
                'name' => '富川瑶族自治县',
            ),
            451202 =>
            array(
                'code' => 161201,
                'area_name' => '金城江区',
                'name' => '金城江区',
            ),
            451221 =>
            array(
                'code' => 161205,
                'area_name' => '南丹县',
                'name' => '南丹县',
            ),
            451222 =>
            array(
                'code' => 161203,
                'area_name' => '天峨县',
                'name' => '天峨县',
            ),
            451223 =>
            array(
                'code' => 161204,
                'area_name' => '凤山县',
                'name' => '凤山县',
            ),
            451224 =>
            array(
                'code' => 161206,
                'area_name' => '东兰县',
                'name' => '东兰县',
            ),
            451225 =>
            array(
                'code' => 161208,
                'area_name' => '罗城仫佬族自治县',
                'name' => '罗城仫佬族自治县',
            ),
            451226 =>
            array(
                'code' => 161210,
                'area_name' => '环江毛南族自治县',
                'name' => '环江毛南族自治县',
            ),
            451227 =>
            array(
                'code' => 161209,
                'area_name' => '巴马瑶族自治县',
                'name' => '巴马瑶族自治县',
            ),
            451228 =>
            array(
                'code' => 161207,
                'area_name' => '都安瑶族自治县',
                'name' => '都安瑶族自治县',
            ),
            451229 =>
            array(
                'code' => 161211,
                'area_name' => '大化瑶族自治县',
                'name' => '大化瑶族自治县',
            ),
            451281 =>
            array(
                'code' => 161202,
                'area_name' => '宜州市',
                'name' => '宜州市',
            ),
            451302 =>
            array(
                'code' => 161301,
                'area_name' => '兴宾区',
                'name' => '兴宾区',
            ),
            451321 =>
            array(
                'code' => 161305,
                'area_name' => '忻城县',
                'name' => '忻城县',
            ),
            451322 =>
            array(
                'code' => 161303,
                'area_name' => '象州县',
                'name' => '象州县',
            ),
            451323 =>
            array(
                'code' => 161304,
                'area_name' => '武宣县',
                'name' => '武宣县',
            ),
            451324 =>
            array(
                'code' => 161306,
                'area_name' => '金秀瑶族自治县',
                'name' => '金秀瑶族自治县',
            ),
            451381 =>
            array(
                'code' => 161302,
                'area_name' => '合山市',
                'name' => '合山市',
            ),
            451402 =>
            array(
                'code' => 161401,
                'area_name' => '江州区',
                'name' => '江州区',
            ),
            451421 =>
            array(
                'code' => 161404,
                'area_name' => '扶绥县',
                'name' => '扶绥县',
            ),
            451422 =>
            array(
                'code' => 161403,
                'area_name' => '宁明县',
                'name' => '宁明县',
            ),
            451423 =>
            array(
                'code' => 161405,
                'area_name' => '龙州县',
                'name' => '龙州县',
            ),
            451424 =>
            array(
                'code' => 161406,
                'area_name' => '大新县',
                'name' => '大新县',
            ),
            451425 =>
            array(
                'code' => 161407,
                'area_name' => '天等县',
                'name' => '天等县',
            ),
            451481 =>
            array(
                'code' => 161402,
                'area_name' => '凭祥市',
                'name' => '凭祥市',
            ),
            460105 =>
            array(
                'code' => 350101,
                'area_name' => '秀英区',
                'name' => '秀英区',
            ),
            460106 =>
            array(
                'code' => 350102,
                'area_name' => '龙华区',
                'name' => '龙华区',
            ),
            460107 =>
            array(
                'code' => 350103,
                'area_name' => '琼山区',
                'name' => '琼山区',
            ),
            460108 =>
            array(
                'code' => 350104,
                'area_name' => '美兰区',
                'name' => '美兰区',
            ),
            460321 =>
            array(
                'code' => '',
                'area_name' => '西沙群岛',
                'name' => '',
            ),
            460322 =>
            array(
                'code' => '',
                'area_name' => '南沙群岛',
                'name' => '',
            ),
            460323 =>
            array(
                'code' => '',
                'area_name' => '中沙群岛的岛礁及其海域',
                'name' => '',
            ),
            500101 =>
            array(
                'code' => 140112,
                'area_name' => '万州区',
                'name' => '万州区',
            ),
            500102 =>
            array(
                'code' => 140113,
                'area_name' => '涪陵区',
                'name' => '涪陵区',
            ),
            500103 =>
            array(
                'code' => 140101,
                'area_name' => '渝中区',
                'name' => '渝中区',
            ),
            500104 =>
            array(
                'code' => '',
                'area_name' => '大渡口区',
                'name' => '',
            ),
            500105 =>
            array(
                'code' => 140103,
                'area_name' => '江北区',
                'name' => '江北区',
            ),
            500106 =>
            array(
                'code' => '',
                'area_name' => '沙坪坝区',
                'name' => '',
            ),
            500107 =>
            array(
                'code' => '',
                'area_name' => '九龙坡区',
                'name' => '',
            ),
            500108 =>
            array(
                'code' => 140106,
                'area_name' => '南岸区',
                'name' => '南岸区',
            ),
            500109 =>
            array(
                'code' => 140107,
                'area_name' => '北碚区',
                'name' => '北碚区',
            ),
            500112 =>
            array(
                'code' => 140110,
                'area_name' => '渝北区',
                'name' => '渝北区',
            ),
            500113 =>
            array(
                'code' => 140111,
                'area_name' => '巴南区',
                'name' => '巴南区',
            ),
            500114 =>
            array(
                'code' => 140114,
                'area_name' => '黔江区',
                'name' => '黔江区',
            ),
            500115 =>
            array(
                'code' => 140115,
                'area_name' => '长寿区',
                'name' => '长寿区',
            ),
            500222 =>
            array(
                'code' => '',
                'area_name' => '綦江区',
                'name' => '',
            ),
            500223 =>
            array(
                'code' => 140121,
                'area_name' => '潼南县',
                'name' => '潼南县',
            ),
            500224 =>
            array(
                'code' => 140122,
                'area_name' => '铜梁县',
                'name' => '铜梁县',
            ),
            500225 =>
            array(
                'code' => '',
                'area_name' => '大足区',
                'name' => '',
            ),
            500226 =>
            array(
                'code' => 140124,
                'area_name' => '荣昌县',
                'name' => '荣昌县',
            ),
            500227 =>
            array(
                'code' => 140125,
                'area_name' => '璧山县',
                'name' => '璧山县',
            ),
            500228 =>
            array(
                'code' => 140130,
                'area_name' => '梁平县',
                'name' => '梁平县',
            ),
            500229 =>
            array(
                'code' => 140129,
                'area_name' => '城口县',
                'name' => '城口县',
            ),
            500230 =>
            array(
                'code' => 140128,
                'area_name' => '丰都县',
                'name' => '丰都县',
            ),
            500231 =>
            array(
                'code' => 140126,
                'area_name' => '垫江县',
                'name' => '垫江县',
            ),
            500232 =>
            array(
                'code' => 140127,
                'area_name' => '武隆县',
                'name' => '武隆县',
            ),
            500233 =>
            array(
                'code' => '',
                'area_name' => '忠县',
                'name' => '',
            ),
            500234 =>
            array(
                'code' => '',
                'area_name' => '开县',
                'name' => '',
            ),
            500235 =>
            array(
                'code' => 140135,
                'area_name' => '云阳县',
                'name' => '云阳县',
            ),
            500236 =>
            array(
                'code' => 140134,
                'area_name' => '奉节县',
                'name' => '奉节县',
            ),
            500237 =>
            array(
                'code' => 140133,
                'area_name' => '巫山县',
                'name' => '巫山县',
            ),
            500238 =>
            array(
                'code' => 140132,
                'area_name' => '巫溪县',
                'name' => '巫溪县',
            ),
            500240 =>
            array(
                'code' => 140137,
                'area_name' => '石柱土家族自治县',
                'name' => '石柱土家族自治县',
            ),
            500241 =>
            array(
                'code' => 140140,
                'area_name' => '秀山土家族苗族自治县',
                'name' => '秀山土家族苗族自治县',
            ),
            500242 =>
            array(
                'code' => 140139,
                'area_name' => '酉阳土家族苗族自治县',
                'name' => '酉阳土家族苗族自治县',
            ),
            500243 =>
            array(
                'code' => 140138,
                'area_name' => '彭水苗族土家族自治县',
                'name' => '彭水苗族土家族自治县',
            ),
            500381 =>
            array(
                'code' => 140116,
                'area_name' => '江津区',
                'name' => '江津区',
            ),
            500382 =>
            array(
                'code' => 140117,
                'area_name' => '合川区',
                'name' => '合川区',
            ),
            500383 =>
            array(
                'code' => 140118,
                'area_name' => '永川区',
                'name' => '永川区',
            ),
            500384 =>
            array(
                'code' => 140119,
                'area_name' => '南川区',
                'name' => '南川区',
            ),
            510104 =>
            array(
                'code' => 360102,
                'area_name' => '锦江区',
                'name' => '锦江区',
            ),
            510105 =>
            array(
                'code' => 360101,
                'area_name' => '青羊区',
                'name' => '青羊区',
            ),
            510106 =>
            array(
                'code' => 360103,
                'area_name' => '金牛区',
                'name' => '金牛区',
            ),
            510107 =>
            array(
                'code' => 360104,
                'area_name' => '武侯区',
                'name' => '武侯区',
            ),
            510108 =>
            array(
                'code' => 360105,
                'area_name' => '成华区',
                'name' => '成华区',
            ),
            510112 =>
            array(
                'code' => 360106,
                'area_name' => '龙泉驿区',
                'name' => '龙泉驿区',
            ),
            510113 =>
            array(
                'code' => 360107,
                'area_name' => '青白江区',
                'name' => '青白江区',
            ),
            510114 =>
            array(
                'code' => 360108,
                'area_name' => '新都区',
                'name' => '新都区',
            ),
            510115 =>
            array(
                'code' => 360109,
                'area_name' => '温江区',
                'name' => '温江区',
            ),
            510121 =>
            array(
                'code' => 360114,
                'area_name' => '金堂县',
                'name' => '金堂县',
            ),
            510122 =>
            array(
                'code' => 360117,
                'area_name' => '双流县',
                'name' => '双流县',
            ),
            510124 =>
            array(
                'code' => '',
                'area_name' => '郫县',
                'name' => '',
            ),
            510129 =>
            array(
                'code' => 360119,
                'area_name' => '大邑县',
                'name' => '大邑县',
            ),
            510131 =>
            array(
                'code' => 360118,
                'area_name' => '蒲江县',
                'name' => '蒲江县',
            ),
            510132 =>
            array(
                'code' => 360116,
                'area_name' => '新津县',
                'name' => '新津县',
            ),
            510181 =>
            array(
                'code' => 360110,
                'area_name' => '都江堰市',
                'name' => '都江堰市',
            ),
            510182 =>
            array(
                'code' => 360111,
                'area_name' => '彭州市',
                'name' => '彭州市',
            ),
            510183 =>
            array(
                'code' => 360112,
                'area_name' => '邛崃市',
                'name' => '邛崃市',
            ),
            510184 =>
            array(
                'code' => 360113,
                'area_name' => '崇州市',
                'name' => '崇州市',
            ),
            510302 =>
            array(
                'code' => 360202,
                'area_name' => '自流井区',
                'name' => '自流井区',
            ),
            510303 =>
            array(
                'code' => 360203,
                'area_name' => '贡井区',
                'name' => '贡井区',
            ),
            510304 =>
            array(
                'code' => 360201,
                'area_name' => '大安区',
                'name' => '大安区',
            ),
            510311 =>
            array(
                'code' => 360204,
                'area_name' => '沿滩区',
                'name' => '沿滩区',
            ),
            510321 =>
            array(
                'code' => '',
                'area_name' => '荣县',
                'name' => '',
            ),
            510322 =>
            array(
                'code' => 360206,
                'area_name' => '富顺县',
                'name' => '富顺县',
            ),
            510402 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            510403 =>
            array(
                'code' => '',
                'area_name' => '西区',
                'name' => '',
            ),
            510411 =>
            array(
                'code' => 360303,
                'area_name' => '仁和区',
                'name' => '仁和区',
            ),
            510421 =>
            array(
                'code' => 360304,
                'area_name' => '米易县',
                'name' => '米易县',
            ),
            510422 =>
            array(
                'code' => 360305,
                'area_name' => '盐边县',
                'name' => '盐边县',
            ),
            510502 =>
            array(
                'code' => 360401,
                'area_name' => '江阳区',
                'name' => '江阳区',
            ),
            510503 =>
            array(
                'code' => 360402,
                'area_name' => '纳溪区',
                'name' => '纳溪区',
            ),
            510504 =>
            array(
                'code' => 360403,
                'area_name' => '龙马潭区',
                'name' => '龙马潭区',
            ),
            510521 =>
            array(
                'code' => '',
                'area_name' => '泸县',
                'name' => '',
            ),
            510522 =>
            array(
                'code' => 360405,
                'area_name' => '合江县',
                'name' => '合江县',
            ),
            510524 =>
            array(
                'code' => 360406,
                'area_name' => '叙永县',
                'name' => '叙永县',
            ),
            510525 =>
            array(
                'code' => 360407,
                'area_name' => '古蔺县',
                'name' => '古蔺县',
            ),
            510603 =>
            array(
                'code' => 360501,
                'area_name' => '旌阳区',
                'name' => '旌阳区',
            ),
            510623 =>
            array(
                'code' => 360506,
                'area_name' => '中江县',
                'name' => '中江县',
            ),
            510626 =>
            array(
                'code' => 360505,
                'area_name' => '罗江县',
                'name' => '罗江县',
            ),
            510681 =>
            array(
                'code' => 360502,
                'area_name' => '广汉市',
                'name' => '广汉市',
            ),
            510682 =>
            array(
                'code' => 360503,
                'area_name' => '什邡市',
                'name' => '什邡市',
            ),
            510683 =>
            array(
                'code' => 360504,
                'area_name' => '绵竹市',
                'name' => '绵竹市',
            ),
            510703 =>
            array(
                'code' => 360601,
                'area_name' => '涪城区',
                'name' => '涪城区',
            ),
            510704 =>
            array(
                'code' => 360602,
                'area_name' => '游仙区',
                'name' => '游仙区',
            ),
            510722 =>
            array(
                'code' => 360605,
                'area_name' => '三台县',
                'name' => '三台县',
            ),
            510723 =>
            array(
                'code' => 360604,
                'area_name' => '盐亭县',
                'name' => '盐亭县',
            ),
            510724 =>
            array(
                'code' => '',
                'area_name' => '安县',
                'name' => '',
            ),
            510725 =>
            array(
                'code' => 360609,
                'area_name' => '梓潼县',
                'name' => '梓潼县',
            ),
            510726 =>
            array(
                'code' => '',
                'area_name' => '北川羌族自治县',
                'name' => '',
            ),
            510727 =>
            array(
                'code' => 360606,
                'area_name' => '平武县',
                'name' => '平武县',
            ),
            510781 =>
            array(
                'code' => 360603,
                'area_name' => '江油市',
                'name' => '江油市',
            ),
            510802 =>
            array(
                'code' => '',
                'area_name' => '利州区',
                'name' => '',
            ),
            510811 =>
            array(
                'code' => '',
                'area_name' => '昭化区',
                'name' => '',
            ),
            510812 =>
            array(
                'code' => 360703,
                'area_name' => '朝天区',
                'name' => '朝天区',
            ),
            510821 =>
            array(
                'code' => 360705,
                'area_name' => '旺苍县',
                'name' => '旺苍县',
            ),
            510822 =>
            array(
                'code' => 360704,
                'area_name' => '青川县',
                'name' => '青川县',
            ),
            510823 =>
            array(
                'code' => 360706,
                'area_name' => '剑阁县',
                'name' => '剑阁县',
            ),
            510824 =>
            array(
                'code' => 360707,
                'area_name' => '苍溪县',
                'name' => '苍溪县',
            ),
            510903 =>
            array(
                'code' => '',
                'area_name' => '船山区',
                'name' => '',
            ),
            510904 =>
            array(
                'code' => '',
                'area_name' => '安居区',
                'name' => '',
            ),
            510921 =>
            array(
                'code' => 360803,
                'area_name' => '蓬溪县',
                'name' => '蓬溪县',
            ),
            510922 =>
            array(
                'code' => 360802,
                'area_name' => '射洪县',
                'name' => '射洪县',
            ),
            510923 =>
            array(
                'code' => 360804,
                'area_name' => '大英县',
                'name' => '大英县',
            ),
            511002 =>
            array(
                'code' => 360901,
                'area_name' => '市中区',
                'name' => '市中区',
            ),
            511011 =>
            array(
                'code' => 360902,
                'area_name' => '东兴区',
                'name' => '东兴区',
            ),
            511024 =>
            array(
                'code' => 360905,
                'area_name' => '威远县',
                'name' => '威远县',
            ),
            511025 =>
            array(
                'code' => 360903,
                'area_name' => '资中县',
                'name' => '资中县',
            ),
            511028 =>
            array(
                'code' => 360904,
                'area_name' => '隆昌县',
                'name' => '隆昌县',
            ),
            511102 =>
            array(
                'code' => 361001,
                'area_name' => '市中区',
                'name' => '市中区',
            ),
            511111 =>
            array(
                'code' => 361003,
                'area_name' => '沙湾区',
                'name' => '沙湾区',
            ),
            511112 =>
            array(
                'code' => 361002,
                'area_name' => '五通桥区',
                'name' => '五通桥区',
            ),
            511113 =>
            array(
                'code' => 361004,
                'area_name' => '金口河区',
                'name' => '金口河区',
            ),
            511123 =>
            array(
                'code' => 361008,
                'area_name' => '犍为县',
                'name' => '犍为县',
            ),
            511124 =>
            array(
                'code' => 361007,
                'area_name' => '井研县',
                'name' => '井研县',
            ),
            511126 =>
            array(
                'code' => 361006,
                'area_name' => '夹江县',
                'name' => '夹江县',
            ),
            511129 =>
            array(
                'code' => 361009,
                'area_name' => '沐川县',
                'name' => '沐川县',
            ),
            511132 =>
            array(
                'code' => 361011,
                'area_name' => '峨边彝族自治县',
                'name' => '峨边彝族自治县',
            ),
            511133 =>
            array(
                'code' => 361010,
                'area_name' => '马边彝族自治县',
                'name' => '马边彝族自治县',
            ),
            511181 =>
            array(
                'code' => 361005,
                'area_name' => '峨眉山市',
                'name' => '峨眉山市',
            ),
            511302 =>
            array(
                'code' => 361101,
                'area_name' => '顺庆区',
                'name' => '顺庆区',
            ),
            511303 =>
            array(
                'code' => 361102,
                'area_name' => '高坪区',
                'name' => '高坪区',
            ),
            511304 =>
            array(
                'code' => 361103,
                'area_name' => '嘉陵区',
                'name' => '嘉陵区',
            ),
            511321 =>
            array(
                'code' => 361108,
                'area_name' => '南部县',
                'name' => '南部县',
            ),
            511322 =>
            array(
                'code' => 361105,
                'area_name' => '营山县',
                'name' => '营山县',
            ),
            511323 =>
            array(
                'code' => 361106,
                'area_name' => '蓬安县',
                'name' => '蓬安县',
            ),
            511324 =>
            array(
                'code' => 361107,
                'area_name' => '仪陇县',
                'name' => '仪陇县',
            ),
            511325 =>
            array(
                'code' => 361109,
                'area_name' => '西充县',
                'name' => '西充县',
            ),
            511381 =>
            array(
                'code' => 361104,
                'area_name' => '阆中市',
                'name' => '阆中市',
            ),
            511402 =>
            array(
                'code' => 361701,
                'area_name' => '东坡区',
                'name' => '东坡区',
            ),
            511421 =>
            array(
                'code' => 361702,
                'area_name' => '仁寿县',
                'name' => '仁寿县',
            ),
            511422 =>
            array(
                'code' => 361703,
                'area_name' => '彭山县',
                'name' => '彭山县',
            ),
            511423 =>
            array(
                'code' => 361704,
                'area_name' => '洪雅县',
                'name' => '洪雅县',
            ),
            511424 =>
            array(
                'code' => 361705,
                'area_name' => '丹棱县',
                'name' => '丹棱县',
            ),
            511425 =>
            array(
                'code' => 361706,
                'area_name' => '青神县',
                'name' => '青神县',
            ),
            511502 =>
            array(
                'code' => 361201,
                'area_name' => '翠屏区',
                'name' => '翠屏区',
            ),
            511521 =>
            array(
                'code' => 361202,
                'area_name' => '宜宾县',
                'name' => '宜宾县',
            ),
            511522 =>
            array(
                'code' => '',
                'area_name' => '南溪区',
                'name' => '',
            ),
            511523 =>
            array(
                'code' => 361208,
                'area_name' => '江安县',
                'name' => '江安县',
            ),
            511524 =>
            array(
                'code' => 361206,
                'area_name' => '长宁县',
                'name' => '长宁县',
            ),
            511525 =>
            array(
                'code' => '',
                'area_name' => '高县',
                'name' => '',
            ),
            511526 =>
            array(
                'code' => '',
                'area_name' => '珙县',
                'name' => '',
            ),
            511527 =>
            array(
                'code' => 361209,
                'area_name' => '筠连县',
                'name' => '筠连县',
            ),
            511528 =>
            array(
                'code' => 361203,
                'area_name' => '兴文县',
                'name' => '兴文县',
            ),
            511529 =>
            array(
                'code' => 361210,
                'area_name' => '屏山县',
                'name' => '屏山县',
            ),
            511602 =>
            array(
                'code' => 361301,
                'area_name' => '广安区',
                'name' => '广安区',
            ),
            511603 =>
            array(
                'code' => '',
                'area_name' => '前锋区',
                'name' => '',
            ),
            511621 =>
            array(
                'code' => 361303,
                'area_name' => '岳池县',
                'name' => '岳池县',
            ),
            511622 =>
            array(
                'code' => 361305,
                'area_name' => '武胜县',
                'name' => '武胜县',
            ),
            511623 =>
            array(
                'code' => 361304,
                'area_name' => '邻水县',
                'name' => '邻水县',
            ),
            511681 =>
            array(
                'code' => 361302,
                'area_name' => '华蓥市',
                'name' => '华蓥市',
            ),
            511702 =>
            array(
                'code' => 361401,
                'area_name' => '通川区',
                'name' => '通川区',
            ),
            511721 =>
            array(
                'code' => '',
                'area_name' => '达川区',
                'name' => '',
            ),
            511722 =>
            array(
                'code' => 361405,
                'area_name' => '宣汉县',
                'name' => '宣汉县',
            ),
            511723 =>
            array(
                'code' => 361406,
                'area_name' => '开江县',
                'name' => '开江县',
            ),
            511724 =>
            array(
                'code' => 361407,
                'area_name' => '大竹县',
                'name' => '大竹县',
            ),
            511725 =>
            array(
                'code' => '',
                'area_name' => '渠县',
                'name' => '',
            ),
            511781 =>
            array(
                'code' => 361402,
                'area_name' => '万源市',
                'name' => '万源市',
            ),
            511802 =>
            array(
                'code' => 361601,
                'area_name' => '雨城区',
                'name' => '雨城区',
            ),
            511821 =>
            array(
                'code' => '',
                'area_name' => '名山区',
                'name' => '',
            ),
            511822 =>
            array(
                'code' => 361606,
                'area_name' => '荥经县',
                'name' => '荥经县',
            ),
            511823 =>
            array(
                'code' => 361608,
                'area_name' => '汉源县',
                'name' => '汉源县',
            ),
            511824 =>
            array(
                'code' => 361603,
                'area_name' => '石棉县',
                'name' => '石棉县',
            ),
            511825 =>
            array(
                'code' => 361605,
                'area_name' => '天全县',
                'name' => '天全县',
            ),
            511826 =>
            array(
                'code' => 361602,
                'area_name' => '芦山县',
                'name' => '芦山县',
            ),
            511827 =>
            array(
                'code' => 361607,
                'area_name' => '宝兴县',
                'name' => '宝兴县',
            ),
            511902 =>
            array(
                'code' => 361501,
                'area_name' => '巴州区',
                'name' => '巴州区',
            ),
            511903 =>
            array(
                'code' => '',
                'area_name' => '恩阳区',
                'name' => '',
            ),
            511921 =>
            array(
                'code' => 361504,
                'area_name' => '通江县',
                'name' => '通江县',
            ),
            511922 =>
            array(
                'code' => 361502,
                'area_name' => '南江县',
                'name' => '南江县',
            ),
            511923 =>
            array(
                'code' => 361503,
                'area_name' => '平昌县',
                'name' => '平昌县',
            ),
            512002 =>
            array(
                'code' => 361801,
                'area_name' => '雁江区',
                'name' => '雁江区',
            ),
            512021 =>
            array(
                'code' => 361803,
                'area_name' => '安岳县',
                'name' => '安岳县',
            ),
            512022 =>
            array(
                'code' => 361804,
                'area_name' => '乐至县',
                'name' => '乐至县',
            ),
            512081 =>
            array(
                'code' => 361802,
                'area_name' => '简阳市',
                'name' => '简阳市',
            ),
            513221 =>
            array(
                'code' => 361904,
                'area_name' => '汶川县',
                'name' => '汶川县',
            ),
            513222 =>
            array(
                'code' => '',
                'area_name' => '理县',
                'name' => '',
            ),
            513223 =>
            array(
                'code' => '',
                'area_name' => '茂县',
                'name' => '',
            ),
            513224 =>
            array(
                'code' => 361911,
                'area_name' => '松潘县',
                'name' => '松潘县',
            ),
            513225 =>
            array(
                'code' => 361902,
                'area_name' => '九寨沟县',
                'name' => '九寨沟县',
            ),
            513226 =>
            array(
                'code' => 361910,
                'area_name' => '金川县',
                'name' => '金川县',
            ),
            513227 =>
            array(
                'code' => 361908,
                'area_name' => '小金县',
                'name' => '小金县',
            ),
            513228 =>
            array(
                'code' => 361909,
                'area_name' => '黑水县',
                'name' => '黑水县',
            ),
            513229 =>
            array(
                'code' => 361901,
                'area_name' => '马尔康县',
                'name' => '马尔康县',
            ),
            513230 =>
            array(
                'code' => 361912,
                'area_name' => '壤塘县',
                'name' => '壤塘县',
            ),
            513231 =>
            array(
                'code' => 361905,
                'area_name' => '阿坝县',
                'name' => '阿坝县',
            ),
            513232 =>
            array(
                'code' => 361907,
                'area_name' => '若尔盖县',
                'name' => '若尔盖县',
            ),
            513233 =>
            array(
                'code' => 361903,
                'area_name' => '红原县',
                'name' => '红原县',
            ),
            513321 =>
            array(
                'code' => 362001,
                'area_name' => '康定县',
                'name' => '康定县',
            ),
            513322 =>
            array(
                'code' => 362017,
                'area_name' => '泸定县',
                'name' => '泸定县',
            ),
            513323 =>
            array(
                'code' => 362002,
                'area_name' => '丹巴县',
                'name' => '丹巴县',
            ),
            513324 =>
            array(
                'code' => 362004,
                'area_name' => '九龙县',
                'name' => '九龙县',
            ),
            513325 =>
            array(
                'code' => 362006,
                'area_name' => '雅江县',
                'name' => '雅江县',
            ),
            513326 =>
            array(
                'code' => 362008,
                'area_name' => '道孚县',
                'name' => '道孚县',
            ),
            513327 =>
            array(
                'code' => 362003,
                'area_name' => '炉霍县',
                'name' => '炉霍县',
            ),
            513328 =>
            array(
                'code' => 362005,
                'area_name' => '甘孜县',
                'name' => '甘孜县',
            ),
            513329 =>
            array(
                'code' => 362007,
                'area_name' => '新龙县',
                'name' => '新龙县',
            ),
            513330 =>
            array(
                'code' => 362011,
                'area_name' => '德格县',
                'name' => '德格县',
            ),
            513331 =>
            array(
                'code' => 362009,
                'area_name' => '白玉县',
                'name' => '白玉县',
            ),
            513332 =>
            array(
                'code' => 362013,
                'area_name' => '石渠县',
                'name' => '石渠县',
            ),
            513333 =>
            array(
                'code' => 362015,
                'area_name' => '色达县',
                'name' => '色达县',
            ),
            513334 =>
            array(
                'code' => 362010,
                'area_name' => '理塘县',
                'name' => '理塘县',
            ),
            513335 =>
            array(
                'code' => 362016,
                'area_name' => '巴塘县',
                'name' => '巴塘县',
            ),
            513336 =>
            array(
                'code' => 362012,
                'area_name' => '乡城县',
                'name' => '乡城县',
            ),
            513337 =>
            array(
                'code' => 362014,
                'area_name' => '稻城县',
                'name' => '稻城县',
            ),
            513338 =>
            array(
                'code' => 362018,
                'area_name' => '得荣县',
                'name' => '得荣县',
            ),
            513401 =>
            array(
                'code' => 362101,
                'area_name' => '西昌市',
                'name' => '西昌市',
            ),
            513422 =>
            array(
                'code' => 362117,
                'area_name' => '木里藏族自治县',
                'name' => '木里藏族自治县',
            ),
            513423 =>
            array(
                'code' => 362114,
                'area_name' => '盐源县',
                'name' => '盐源县',
            ),
            513424 =>
            array(
                'code' => 362115,
                'area_name' => '德昌县',
                'name' => '德昌县',
            ),
            513425 =>
            array(
                'code' => 362113,
                'area_name' => '会理县',
                'name' => '会理县',
            ),
            513426 =>
            array(
                'code' => 362111,
                'area_name' => '会东县',
                'name' => '会东县',
            ),
            513427 =>
            array(
                'code' => 362109,
                'area_name' => '宁南县',
                'name' => '宁南县',
            ),
            513428 =>
            array(
                'code' => 362108,
                'area_name' => '普格县',
                'name' => '普格县',
            ),
            513429 =>
            array(
                'code' => 362106,
                'area_name' => '布拖县',
                'name' => '布拖县',
            ),
            513430 =>
            array(
                'code' => 362104,
                'area_name' => '金阳县',
                'name' => '金阳县',
            ),
            513431 =>
            array(
                'code' => 362103,
                'area_name' => '昭觉县',
                'name' => '昭觉县',
            ),
            513432 =>
            array(
                'code' => 362110,
                'area_name' => '喜德县',
                'name' => '喜德县',
            ),
            513433 =>
            array(
                'code' => 362116,
                'area_name' => '冕宁县',
                'name' => '冕宁县',
            ),
            513434 =>
            array(
                'code' => 362112,
                'area_name' => '越西县',
                'name' => '越西县',
            ),
            513435 =>
            array(
                'code' => 362105,
                'area_name' => '甘洛县',
                'name' => '甘洛县',
            ),
            513436 =>
            array(
                'code' => 362102,
                'area_name' => '美姑县',
                'name' => '美姑县',
            ),
            513437 =>
            array(
                'code' => 362107,
                'area_name' => '雷波县',
                'name' => '雷波县',
            ),
            520102 =>
            array(
                'code' => 370101,
                'area_name' => '南明区',
                'name' => '南明区',
            ),
            520103 =>
            array(
                'code' => 370102,
                'area_name' => '云岩区',
                'name' => '云岩区',
            ),
            520111 =>
            array(
                'code' => 370103,
                'area_name' => '花溪区',
                'name' => '花溪区',
            ),
            520112 =>
            array(
                'code' => 370104,
                'area_name' => '乌当区',
                'name' => '乌当区',
            ),
            520113 =>
            array(
                'code' => 370105,
                'area_name' => '白云区',
                'name' => '白云区',
            ),
            520121 =>
            array(
                'code' => 370108,
                'area_name' => '开阳县',
                'name' => '开阳县',
            ),
            520122 =>
            array(
                'code' => 370110,
                'area_name' => '息烽县',
                'name' => '息烽县',
            ),
            520123 =>
            array(
                'code' => 370109,
                'area_name' => '修文县',
                'name' => '修文县',
            ),
            520151 =>
            array(
                'code' => '',
                'area_name' => '观山湖区',
                'name' => '',
            ),
            520181 =>
            array(
                'code' => 370107,
                'area_name' => '清镇市',
                'name' => '清镇市',
            ),
            520201 =>
            array(
                'code' => 370201,
                'area_name' => '钟山区',
                'name' => '钟山区',
            ),
            520203 =>
            array(
                'code' => 370204,
                'area_name' => '六枝特区',
                'name' => '六枝特区',
            ),
            520221 =>
            array(
                'code' => 370202,
                'area_name' => '水城县',
                'name' => '水城县',
            ),
            520222 =>
            array(
                'code' => '',
                'area_name' => '盘县',
                'name' => '',
            ),
            520302 =>
            array(
                'code' => 370301,
                'area_name' => '红花岗区',
                'name' => '红花岗区',
            ),
            520303 =>
            array(
                'code' => '',
                'area_name' => '汇川区',
                'name' => '',
            ),
            520321 =>
            array(
                'code' => 370304,
                'area_name' => '遵义县',
                'name' => '遵义县',
            ),
            520322 =>
            array(
                'code' => 370306,
                'area_name' => '桐梓县',
                'name' => '桐梓县',
            ),
            520323 =>
            array(
                'code' => 370305,
                'area_name' => '绥阳县',
                'name' => '绥阳县',
            ),
            520324 =>
            array(
                'code' => 370309,
                'area_name' => '正安县',
                'name' => '正安县',
            ),
            520325 =>
            array(
                'code' => 370312,
                'area_name' => '道真仡佬族苗族自治县',
                'name' => '道真仡佬族苗族自治县',
            ),
            520326 =>
            array(
                'code' => 370313,
                'area_name' => '务川仡佬族苗族自治县',
                'name' => '务川仡佬族苗族自治县',
            ),
            520327 =>
            array(
                'code' => 370308,
                'area_name' => '凤冈县',
                'name' => '凤冈县',
            ),
            520328 =>
            array(
                'code' => 370311,
                'area_name' => '湄潭县',
                'name' => '湄潭县',
            ),
            520329 =>
            array(
                'code' => 370310,
                'area_name' => '余庆县',
                'name' => '余庆县',
            ),
            520330 =>
            array(
                'code' => 370307,
                'area_name' => '习水县',
                'name' => '习水县',
            ),
            520381 =>
            array(
                'code' => 370302,
                'area_name' => '赤水市',
                'name' => '赤水市',
            ),
            520382 =>
            array(
                'code' => 370303,
                'area_name' => '仁怀市',
                'name' => '仁怀市',
            ),
            520402 =>
            array(
                'code' => 370401,
                'area_name' => '西秀区',
                'name' => '西秀区',
            ),
            520421 =>
            array(
                'code' => 370403,
                'area_name' => '平坝县',
                'name' => '平坝县',
            ),
            520422 =>
            array(
                'code' => 370402,
                'area_name' => '普定县',
                'name' => '普定县',
            ),
            520423 =>
            array(
                'code' => 370404,
                'area_name' => '镇宁布依族苗族自治县',
                'name' => '镇宁布依族苗族自治县',
            ),
            520424 =>
            array(
                'code' => 370406,
                'area_name' => '关岭布依族苗族自治县',
                'name' => '关岭布依族苗族自治县',
            ),
            520425 =>
            array(
                'code' => 370405,
                'area_name' => '紫云苗族布依族自治县',
                'name' => '紫云苗族布依族自治县',
            ),
            522201 =>
            array(
                'code' => '',
                'area_name' => '碧江区',
                'name' => '',
            ),
            522222 =>
            array(
                'code' => '',
                'area_name' => '江口县',
                'name' => '',
            ),
            522223 =>
            array(
                'code' => '',
                'area_name' => '玉屏侗族自治县',
                'name' => '',
            ),
            522224 =>
            array(
                'code' => '',
                'area_name' => '石阡县',
                'name' => '',
            ),
            522225 =>
            array(
                'code' => '',
                'area_name' => '思南县',
                'name' => '',
            ),
            522226 =>
            array(
                'code' => '',
                'area_name' => '印江土家族苗族自治县',
                'name' => '',
            ),
            522227 =>
            array(
                'code' => '',
                'area_name' => '德江县',
                'name' => '',
            ),
            522228 =>
            array(
                'code' => '',
                'area_name' => '沿河土家族自治县',
                'name' => '',
            ),
            522229 =>
            array(
                'code' => '',
                'area_name' => '松桃苗族自治县',
                'name' => '',
            ),
            522230 =>
            array(
                'code' => '',
                'area_name' => '万山区',
                'name' => '',
            ),
            522301 =>
            array(
                'code' => 370701,
                'area_name' => '兴义市',
                'name' => '兴义市',
            ),
            522322 =>
            array(
                'code' => 370703,
                'area_name' => '兴仁县',
                'name' => '兴仁县',
            ),
            522323 =>
            array(
                'code' => 370704,
                'area_name' => '普安县',
                'name' => '普安县',
            ),
            522324 =>
            array(
                'code' => 370706,
                'area_name' => '晴隆县',
                'name' => '晴隆县',
            ),
            522325 =>
            array(
                'code' => 370707,
                'area_name' => '贞丰县',
                'name' => '贞丰县',
            ),
            522326 =>
            array(
                'code' => 370702,
                'area_name' => '望谟县',
                'name' => '望谟县',
            ),
            522327 =>
            array(
                'code' => 370705,
                'area_name' => '册亨县',
                'name' => '册亨县',
            ),
            522328 =>
            array(
                'code' => 370708,
                'area_name' => '安龙县',
                'name' => '安龙县',
            ),
            522401 =>
            array(
                'code' => '',
                'area_name' => '七星关区',
                'name' => '',
            ),
            522422 =>
            array(
                'code' => '',
                'area_name' => '大方县',
                'name' => '',
            ),
            522423 =>
            array(
                'code' => '',
                'area_name' => '黔西县',
                'name' => '',
            ),
            522424 =>
            array(
                'code' => '',
                'area_name' => '金沙县',
                'name' => '',
            ),
            522425 =>
            array(
                'code' => '',
                'area_name' => '织金县',
                'name' => '',
            ),
            522426 =>
            array(
                'code' => '',
                'area_name' => '纳雍县',
                'name' => '',
            ),
            522427 =>
            array(
                'code' => '',
                'area_name' => '威宁彝族回族苗族自治县',
                'name' => '',
            ),
            522428 =>
            array(
                'code' => '',
                'area_name' => '赫章县',
                'name' => '',
            ),
            522601 =>
            array(
                'code' => 370801,
                'area_name' => '凯里市',
                'name' => '凯里市',
            ),
            522622 =>
            array(
                'code' => 370809,
                'area_name' => '黄平县',
                'name' => '黄平县',
            ),
            522623 =>
            array(
                'code' => 370802,
                'area_name' => '施秉县',
                'name' => '施秉县',
            ),
            522624 =>
            array(
                'code' => 370812,
                'area_name' => '三穗县',
                'name' => '三穗县',
            ),
            522625 =>
            array(
                'code' => 370805,
                'area_name' => '镇远县',
                'name' => '镇远县',
            ),
            522626 =>
            array(
                'code' => 370815,
                'area_name' => '岑巩县',
                'name' => '岑巩县',
            ),
            522627 =>
            array(
                'code' => 370808,
                'area_name' => '天柱县',
                'name' => '天柱县',
            ),
            522628 =>
            array(
                'code' => 370804,
                'area_name' => '锦屏县',
                'name' => '锦屏县',
            ),
            522629 =>
            array(
                'code' => 370811,
                'area_name' => '剑河县',
                'name' => '剑河县',
            ),
            522630 =>
            array(
                'code' => 370807,
                'area_name' => '台江县',
                'name' => '台江县',
            ),
            522631 =>
            array(
                'code' => 370814,
                'area_name' => '黎平县',
                'name' => '黎平县',
            ),
            522632 =>
            array(
                'code' => 370810,
                'area_name' => '榕江县',
                'name' => '榕江县',
            ),
            522633 =>
            array(
                'code' => 370803,
                'area_name' => '从江县',
                'name' => '从江县',
            ),
            522634 =>
            array(
                'code' => 370813,
                'area_name' => '雷山县',
                'name' => '雷山县',
            ),
            522635 =>
            array(
                'code' => 370806,
                'area_name' => '麻江县',
                'name' => '麻江县',
            ),
            522636 =>
            array(
                'code' => 370816,
                'area_name' => '丹寨县',
                'name' => '丹寨县',
            ),
            522701 =>
            array(
                'code' => 370901,
                'area_name' => '都匀市',
                'name' => '都匀市',
            ),
            522702 =>
            array(
                'code' => 370902,
                'area_name' => '福泉市',
                'name' => '福泉市',
            ),
            522722 =>
            array(
                'code' => 370907,
                'area_name' => '荔波县',
                'name' => '荔波县',
            ),
            522723 =>
            array(
                'code' => 370903,
                'area_name' => '贵定县',
                'name' => '贵定县',
            ),
            522725 =>
            array(
                'code' => 370906,
                'area_name' => '瓮安县',
                'name' => '瓮安县',
            ),
            522726 =>
            array(
                'code' => 370911,
                'area_name' => '独山县',
                'name' => '独山县',
            ),
            522727 =>
            array(
                'code' => 370909,
                'area_name' => '平塘县',
                'name' => '平塘县',
            ),
            522728 =>
            array(
                'code' => 370905,
                'area_name' => '罗甸县',
                'name' => '罗甸县',
            ),
            522729 =>
            array(
                'code' => 370910,
                'area_name' => '长顺县',
                'name' => '长顺县',
            ),
            522730 =>
            array(
                'code' => 370908,
                'area_name' => '龙里县',
                'name' => '龙里县',
            ),
            522731 =>
            array(
                'code' => 370904,
                'area_name' => '惠水县',
                'name' => '惠水县',
            ),
            522732 =>
            array(
                'code' => 370912,
                'area_name' => '三都水族自治县',
                'name' => '三都水族自治县',
            ),
            530102 =>
            array(
                'code' => 380102,
                'area_name' => '五华区',
                'name' => '五华区',
            ),
            530103 =>
            array(
                'code' => 380101,
                'area_name' => '盘龙区',
                'name' => '盘龙区',
            ),
            530111 =>
            array(
                'code' => 380103,
                'area_name' => '官渡区',
                'name' => '官渡区',
            ),
            530112 =>
            array(
                'code' => 380104,
                'area_name' => '西山区',
                'name' => '西山区',
            ),
            530113 =>
            array(
                'code' => 380105,
                'area_name' => '东川区',
                'name' => '东川区',
            ),
            530121 =>
            array(
                'code' => '',
                'area_name' => '呈贡区',
                'name' => '',
            ),
            530122 =>
            array(
                'code' => 380110,
                'area_name' => '晋宁县',
                'name' => '晋宁县',
            ),
            530124 =>
            array(
                'code' => 380107,
                'area_name' => '富民县',
                'name' => '富民县',
            ),
            530125 =>
            array(
                'code' => 380111,
                'area_name' => '宜良县',
                'name' => '宜良县',
            ),
            530126 =>
            array(
                'code' => 380113,
                'area_name' => '石林彝族自治县',
                'name' => '石林彝族自治县',
            ),
            530127 =>
            array(
                'code' => 380108,
                'area_name' => '嵩明县',
                'name' => '嵩明县',
            ),
            530128 =>
            array(
                'code' => 380112,
                'area_name' => '禄劝彝族苗族自治县',
                'name' => '禄劝彝族苗族自治县',
            ),
            530129 =>
            array(
                'code' => '',
                'area_name' => '寻甸回族彝族自治县',
                'name' => '',
            ),
            530181 =>
            array(
                'code' => 380106,
                'area_name' => '安宁市',
                'name' => '安宁市',
            ),
            530302 =>
            array(
                'code' => 380201,
                'area_name' => '麒麟区',
                'name' => '麒麟区',
            ),
            530321 =>
            array(
                'code' => 380207,
                'area_name' => '马龙县',
                'name' => '马龙县',
            ),
            530322 =>
            array(
                'code' => 380203,
                'area_name' => '陆良县',
                'name' => '陆良县',
            ),
            530323 =>
            array(
                'code' => 380208,
                'area_name' => '师宗县',
                'name' => '师宗县',
            ),
            530324 =>
            array(
                'code' => 380206,
                'area_name' => '罗平县',
                'name' => '罗平县',
            ),
            530325 =>
            array(
                'code' => 380205,
                'area_name' => '富源县',
                'name' => '富源县',
            ),
            530326 =>
            array(
                'code' => 380204,
                'area_name' => '会泽县',
                'name' => '会泽县',
            ),
            530328 =>
            array(
                'code' => 380209,
                'area_name' => '沾益县',
                'name' => '沾益县',
            ),
            530381 =>
            array(
                'code' => 380202,
                'area_name' => '宣威市',
                'name' => '宣威市',
            ),
            530402 =>
            array(
                'code' => 380301,
                'area_name' => '红塔区',
                'name' => '红塔区',
            ),
            530421 =>
            array(
                'code' => 380306,
                'area_name' => '江川县',
                'name' => '江川县',
            ),
            530422 =>
            array(
                'code' => 380303,
                'area_name' => '澄江县',
                'name' => '澄江县',
            ),
            530423 =>
            array(
                'code' => 380305,
                'area_name' => '通海县',
                'name' => '通海县',
            ),
            530424 =>
            array(
                'code' => 380302,
                'area_name' => '华宁县',
                'name' => '华宁县',
            ),
            530425 =>
            array(
                'code' => 380304,
                'area_name' => '易门县',
                'name' => '易门县',
            ),
            530426 =>
            array(
                'code' => 380309,
                'area_name' => '峨山彝族自治县',
                'name' => '峨山彝族自治县',
            ),
            530427 =>
            array(
                'code' => 380308,
                'area_name' => '新平彝族傣族自治县',
                'name' => '新平彝族傣族自治县',
            ),
            530428 =>
            array(
                'code' => 380307,
                'area_name' => '元江哈尼族彝族傣族自治县',
                'name' => '元江哈尼族彝族傣族自治县',
            ),
            530502 =>
            array(
                'code' => 380401,
                'area_name' => '隆阳区',
                'name' => '隆阳区',
            ),
            530521 =>
            array(
                'code' => 380402,
                'area_name' => '施甸县',
                'name' => '施甸县',
            ),
            530522 =>
            array(
                'code' => 380405,
                'area_name' => '腾冲县',
                'name' => '腾冲县',
            ),
            530523 =>
            array(
                'code' => 380404,
                'area_name' => '龙陵县',
                'name' => '龙陵县',
            ),
            530524 =>
            array(
                'code' => 380403,
                'area_name' => '昌宁县',
                'name' => '昌宁县',
            ),
            530602 =>
            array(
                'code' => 380501,
                'area_name' => '昭阳区',
                'name' => '昭阳区',
            ),
            530621 =>
            array(
                'code' => 380511,
                'area_name' => '鲁甸县',
                'name' => '鲁甸县',
            ),
            530622 =>
            array(
                'code' => 380507,
                'area_name' => '巧家县',
                'name' => '巧家县',
            ),
            530623 =>
            array(
                'code' => 380506,
                'area_name' => '盐津县',
                'name' => '盐津县',
            ),
            530624 =>
            array(
                'code' => 380505,
                'area_name' => '大关县',
                'name' => '大关县',
            ),
            530625 =>
            array(
                'code' => 380502,
                'area_name' => '永善县',
                'name' => '永善县',
            ),
            530626 =>
            array(
                'code' => 380503,
                'area_name' => '绥江县',
                'name' => '绥江县',
            ),
            530627 =>
            array(
                'code' => 380504,
                'area_name' => '镇雄县',
                'name' => '镇雄县',
            ),
            530628 =>
            array(
                'code' => 380508,
                'area_name' => '彝良县',
                'name' => '彝良县',
            ),
            530629 =>
            array(
                'code' => 380509,
                'area_name' => '威信县',
                'name' => '威信县',
            ),
            530630 =>
            array(
                'code' => 380510,
                'area_name' => '水富县',
                'name' => '水富县',
            ),
            530702 =>
            array(
                'code' => 380801,
                'area_name' => '古城区',
                'name' => '古城区',
            ),
            530721 =>
            array(
                'code' => 380802,
                'area_name' => '玉龙纳西族自治县',
                'name' => '玉龙纳西族自治县',
            ),
            530722 =>
            array(
                'code' => 380804,
                'area_name' => '永胜县',
                'name' => '永胜县',
            ),
            530723 =>
            array(
                'code' => 380803,
                'area_name' => '华坪县',
                'name' => '华坪县',
            ),
            530724 =>
            array(
                'code' => 380805,
                'area_name' => '宁蒗彝族自治县',
                'name' => '宁蒗彝族自治县',
            ),
            530802 =>
            array(
                'code' => '',
                'area_name' => '思茅区',
                'name' => '',
            ),
            530821 =>
            array(
                'code' => '',
                'area_name' => '宁洱哈尼族彝族自治县',
                'name' => '',
            ),
            530822 =>
            array(
                'code' => '',
                'area_name' => '墨江哈尼族自治县',
                'name' => '',
            ),
            530823 =>
            array(
                'code' => '',
                'area_name' => '景东彝族自治县',
                'name' => '',
            ),
            530824 =>
            array(
                'code' => '',
                'area_name' => '景谷傣族彝族自治县',
                'name' => '',
            ),
            530825 =>
            array(
                'code' => '',
                'area_name' => '镇沅彝族哈尼族拉祜族自治县',
                'name' => '',
            ),
            530826 =>
            array(
                'code' => '',
                'area_name' => '江城哈尼族彝族自治县',
                'name' => '',
            ),
            530827 =>
            array(
                'code' => '',
                'area_name' => '孟连傣族拉祜族佤族自治县',
                'name' => '',
            ),
            530828 =>
            array(
                'code' => '',
                'area_name' => '澜沧拉祜族自治县',
                'name' => '',
            ),
            530829 =>
            array(
                'code' => '',
                'area_name' => '西盟佤族自治县',
                'name' => '',
            ),
            530902 =>
            array(
                'code' => '',
                'area_name' => '临翔区',
                'name' => '',
            ),
            530921 =>
            array(
                'code' => '',
                'area_name' => '凤庆县',
                'name' => '',
            ),
            530922 =>
            array(
                'code' => '',
                'area_name' => '云县',
                'name' => '',
            ),
            530923 =>
            array(
                'code' => '',
                'area_name' => '永德县',
                'name' => '',
            ),
            530924 =>
            array(
                'code' => '',
                'area_name' => '镇康县',
                'name' => '',
            ),
            530925 =>
            array(
                'code' => '',
                'area_name' => '双江拉祜族佤族布朗族傣族自治县',
                'name' => '',
            ),
            530926 =>
            array(
                'code' => '',
                'area_name' => '耿马傣族佤族自治县',
                'name' => '',
            ),
            530927 =>
            array(
                'code' => '',
                'area_name' => '沧源佤族自治县',
                'name' => '',
            ),
            532301 =>
            array(
                'code' => 381201,
                'area_name' => '楚雄市',
                'name' => '楚雄市',
            ),
            532322 =>
            array(
                'code' => 381207,
                'area_name' => '双柏县',
                'name' => '双柏县',
            ),
            532323 =>
            array(
                'code' => 381204,
                'area_name' => '牟定县',
                'name' => '牟定县',
            ),
            532324 =>
            array(
                'code' => 381203,
                'area_name' => '南华县',
                'name' => '南华县',
            ),
            532325 =>
            array(
                'code' => 381210,
                'area_name' => '姚安县',
                'name' => '姚安县',
            ),
            532326 =>
            array(
                'code' => 381206,
                'area_name' => '大姚县',
                'name' => '大姚县',
            ),
            532327 =>
            array(
                'code' => 381209,
                'area_name' => '永仁县',
                'name' => '永仁县',
            ),
            532328 =>
            array(
                'code' => 381202,
                'area_name' => '元谋县',
                'name' => '元谋县',
            ),
            532329 =>
            array(
                'code' => 381205,
                'area_name' => '武定县',
                'name' => '武定县',
            ),
            532331 =>
            array(
                'code' => 381208,
                'area_name' => '禄丰县',
                'name' => '禄丰县',
            ),
            532501 =>
            array(
                'code' => 381001,
                'area_name' => '个旧市',
                'name' => '个旧市',
            ),
            532502 =>
            array(
                'code' => 381002,
                'area_name' => '开远市',
                'name' => '开远市',
            ),
            532522 =>
            array(
                'code' => '',
                'area_name' => '蒙自市',
                'name' => '',
            ),
            532523 =>
            array(
                'code' => 381013,
                'area_name' => '屏边苗族自治县',
                'name' => '屏边苗族自治县',
            ),
            532524 =>
            array(
                'code' => 381008,
                'area_name' => '建水县',
                'name' => '建水县',
            ),
            532525 =>
            array(
                'code' => 381010,
                'area_name' => '石屏县',
                'name' => '石屏县',
            ),
            532526 =>
            array(
                'code' => '',
                'area_name' => '弥勒市',
                'name' => '',
            ),
            532527 =>
            array(
                'code' => 381007,
                'area_name' => '泸西县',
                'name' => '泸西县',
            ),
            532528 =>
            array(
                'code' => 381009,
                'area_name' => '元阳县',
                'name' => '元阳县',
            ),
            532529 =>
            array(
                'code' => 381004,
                'area_name' => '红河县',
                'name' => '红河县',
            ),
            532530 =>
            array(
                'code' => 381011,
                'area_name' => '金平苗族瑶族傣族自治县',
                'name' => '金平苗族瑶族傣族自治县',
            ),
            532531 =>
            array(
                'code' => 381005,
                'area_name' => '绿春县',
                'name' => '绿春县',
            ),
            532532 =>
            array(
                'code' => 381012,
                'area_name' => '河口瑶族自治县',
                'name' => '河口瑶族自治县',
            ),
            532621 =>
            array(
                'code' => '',
                'area_name' => '文山市',
                'name' => '',
            ),
            532622 =>
            array(
                'code' => 380903,
                'area_name' => '砚山县',
                'name' => '砚山县',
            ),
            532623 =>
            array(
                'code' => 380907,
                'area_name' => '西畴县',
                'name' => '西畴县',
            ),
            532624 =>
            array(
                'code' => 380902,
                'area_name' => '麻栗坡县',
                'name' => '麻栗坡县',
            ),
            532625 =>
            array(
                'code' => 380905,
                'area_name' => '马关县',
                'name' => '马关县',
            ),
            532626 =>
            array(
                'code' => 380908,
                'area_name' => '丘北县',
                'name' => '丘北县',
            ),
            532627 =>
            array(
                'code' => 380904,
                'area_name' => '广南县',
                'name' => '广南县',
            ),
            532628 =>
            array(
                'code' => 380906,
                'area_name' => '富宁县',
                'name' => '富宁县',
            ),
            532801 =>
            array(
                'code' => 381101,
                'area_name' => '景洪市',
                'name' => '景洪市',
            ),
            532822 =>
            array(
                'code' => 381102,
                'area_name' => '勐海县',
                'name' => '勐海县',
            ),
            532823 =>
            array(
                'code' => 381103,
                'area_name' => '勐腊县',
                'name' => '勐腊县',
            ),
            532901 =>
            array(
                'code' => 381301,
                'area_name' => '大理市',
                'name' => '大理市',
            ),
            532922 =>
            array(
                'code' => 381309,
                'area_name' => '漾濞彝族自治县',
                'name' => '漾濞彝族自治县',
            ),
            532923 =>
            array(
                'code' => 381307,
                'area_name' => '祥云县',
                'name' => '祥云县',
            ),
            532924 =>
            array(
                'code' => '',
                'area_name' => '宾川县',
                'name' => '',
            ),
            532925 =>
            array(
                'code' => 381303,
                'area_name' => '弥渡县',
                'name' => '弥渡县',
            ),
            532926 =>
            array(
                'code' => 381311,
                'area_name' => '南涧彝族自治县',
                'name' => '南涧彝族自治县',
            ),
            532927 =>
            array(
                'code' => 381310,
                'area_name' => '巍山彝族回族自治县',
                'name' => '巍山彝族回族自治县',
            ),
            532928 =>
            array(
                'code' => '',
                'area_name' => '永平县',
                'name' => '',
            ),
            532929 =>
            array(
                'code' => 381304,
                'area_name' => '云龙县',
                'name' => '云龙县',
            ),
            532930 =>
            array(
                'code' => 381305,
                'area_name' => '洱源县',
                'name' => '洱源县',
            ),
            532931 =>
            array(
                'code' => 381302,
                'area_name' => '剑川县',
                'name' => '剑川县',
            ),
            532932 =>
            array(
                'code' => 381306,
                'area_name' => '鹤庆县',
                'name' => '鹤庆县',
            ),
            533102 =>
            array(
                'code' => 381402,
                'area_name' => '瑞丽市',
                'name' => '瑞丽市',
            ),
            533103 =>
            array(
                'code' => '',
                'area_name' => '芒市',
                'name' => '',
            ),
            533122 =>
            array(
                'code' => 381404,
                'area_name' => '梁河县',
                'name' => '梁河县',
            ),
            533123 =>
            array(
                'code' => 381403,
                'area_name' => '盈江县',
                'name' => '盈江县',
            ),
            533124 =>
            array(
                'code' => 381405,
                'area_name' => '陇川县',
                'name' => '陇川县',
            ),
            533321 =>
            array(
                'code' => '',
                'area_name' => '泸水县',
                'name' => '',
            ),
            533323 =>
            array(
                'code' => '',
                'area_name' => '福贡县',
                'name' => '',
            ),
            533324 =>
            array(
                'code' => '',
                'area_name' => '贡山独龙族怒族自治县',
                'name' => '',
            ),
            533325 =>
            array(
                'code' => '',
                'area_name' => '兰坪白族普米族自治县',
                'name' => '',
            ),
            533421 =>
            array(
                'code' => 381601,
                'area_name' => '香格里拉县',
                'name' => '香格里拉县',
            ),
            533422 =>
            array(
                'code' => 381602,
                'area_name' => '德钦县',
                'name' => '德钦县',
            ),
            533423 =>
            array(
                'code' => 381603,
                'area_name' => '维西傈僳族自治县',
                'name' => '维西傈僳族自治县',
            ),
            540102 =>
            array(
                'code' => 170101,
                'area_name' => '城关区',
                'name' => '城关区',
            ),
            540121 =>
            array(
                'code' => 170102,
                'area_name' => '林周县',
                'name' => '林周县',
            ),
            540122 =>
            array(
                'code' => 170105,
                'area_name' => '当雄县',
                'name' => '当雄县',
            ),
            540123 =>
            array(
                'code' => 170104,
                'area_name' => '尼木县',
                'name' => '尼木县',
            ),
            540124 =>
            array(
                'code' => 170106,
                'area_name' => '曲水县',
                'name' => '曲水县',
            ),
            540125 =>
            array(
                'code' => 170108,
                'area_name' => '堆龙德庆县',
                'name' => '堆龙德庆县',
            ),
            540126 =>
            array(
                'code' => 170103,
                'area_name' => '达孜县',
                'name' => '达孜县',
            ),
            540127 =>
            array(
                'code' => 170107,
                'area_name' => '墨竹工卡县',
                'name' => '墨竹工卡县',
            ),
            542121 =>
            array(
                'code' => 170301,
                'area_name' => '昌都县',
                'name' => '昌都县',
            ),
            542122 =>
            array(
                'code' => 170308,
                'area_name' => '江达县',
                'name' => '江达县',
            ),
            542123 =>
            array(
                'code' => 170303,
                'area_name' => '贡觉县',
                'name' => '贡觉县',
            ),
            542124 =>
            array(
                'code' => 170309,
                'area_name' => '类乌齐县',
                'name' => '类乌齐县',
            ),
            542125 =>
            array(
                'code' => 170310,
                'area_name' => '丁青县',
                'name' => '丁青县',
            ),
            542126 =>
            array(
                'code' => 170311,
                'area_name' => '察雅县',
                'name' => '察雅县',
            ),
            542127 =>
            array(
                'code' => 170304,
                'area_name' => '八宿县',
                'name' => '八宿县',
            ),
            542128 =>
            array(
                'code' => 170305,
                'area_name' => '左贡县',
                'name' => '左贡县',
            ),
            542129 =>
            array(
                'code' => 170302,
                'area_name' => '芒康县',
                'name' => '芒康县',
            ),
            542132 =>
            array(
                'code' => 170307,
                'area_name' => '洛隆县',
                'name' => '洛隆县',
            ),
            542133 =>
            array(
                'code' => 170306,
                'area_name' => '边坝县',
                'name' => '边坝县',
            ),
            542221 =>
            array(
                'code' => 170401,
                'area_name' => '乃东县',
                'name' => '乃东县',
            ),
            542222 =>
            array(
                'code' => 170409,
                'area_name' => '扎囊县',
                'name' => '扎囊县',
            ),
            542223 =>
            array(
                'code' => 170405,
                'area_name' => '贡嘎县',
                'name' => '贡嘎县',
            ),
            542224 =>
            array(
                'code' => 170408,
                'area_name' => '桑日县',
                'name' => '桑日县',
            ),
            542225 =>
            array(
                'code' => 170402,
                'area_name' => '琼结县',
                'name' => '琼结县',
            ),
            542226 =>
            array(
                'code' => 170407,
                'area_name' => '曲松县',
                'name' => '曲松县',
            ),
            542227 =>
            array(
                'code' => 170403,
                'area_name' => '措美县',
                'name' => '措美县',
            ),
            542228 =>
            array(
                'code' => 170406,
                'area_name' => '洛扎县',
                'name' => '洛扎县',
            ),
            542229 =>
            array(
                'code' => 170404,
                'area_name' => '加查县',
                'name' => '加查县',
            ),
            542231 =>
            array(
                'code' => 170411,
                'area_name' => '隆子县',
                'name' => '隆子县',
            ),
            542232 =>
            array(
                'code' => 170410,
                'area_name' => '错那县',
                'name' => '错那县',
            ),
            542233 =>
            array(
                'code' => 170412,
                'area_name' => '浪卡子县',
                'name' => '浪卡子县',
            ),
            542301 =>
            array(
                'code' => 170501,
                'area_name' => '日喀则市',
                'name' => '日喀则市',
            ),
            542322 =>
            array(
                'code' => 170518,
                'area_name' => '南木林县',
                'name' => '南木林县',
            ),
            542323 =>
            array(
                'code' => 170504,
                'area_name' => '江孜县',
                'name' => '江孜县',
            ),
            542324 =>
            array(
                'code' => 170506,
                'area_name' => '定日县',
                'name' => '定日县',
            ),
            542325 =>
            array(
                'code' => 170503,
                'area_name' => '萨迦县',
                'name' => '萨迦县',
            ),
            542326 =>
            array(
                'code' => 170505,
                'area_name' => '拉孜县',
                'name' => '拉孜县',
            ),
            542327 =>
            array(
                'code' => 170512,
                'area_name' => '昂仁县',
                'name' => '昂仁县',
            ),
            542328 =>
            array(
                'code' => 170511,
                'area_name' => '谢通门县',
                'name' => '谢通门县',
            ),
            542329 =>
            array(
                'code' => 170517,
                'area_name' => '白朗县',
                'name' => '白朗县',
            ),
            542330 =>
            array(
                'code' => 170516,
                'area_name' => '仁布县',
                'name' => '仁布县',
            ),
            542331 =>
            array(
                'code' => 170507,
                'area_name' => '康马县',
                'name' => '康马县',
            ),
            542332 =>
            array(
                'code' => 170502,
                'area_name' => '定结县',
                'name' => '定结县',
            ),
            542333 =>
            array(
                'code' => 170514,
                'area_name' => '仲巴县',
                'name' => '仲巴县',
            ),
            542334 =>
            array(
                'code' => 170510,
                'area_name' => '亚东县',
                'name' => '亚东县',
            ),
            542335 =>
            array(
                'code' => 170509,
                'area_name' => '吉隆县',
                'name' => '吉隆县',
            ),
            542336 =>
            array(
                'code' => 170508,
                'area_name' => '聂拉木县',
                'name' => '聂拉木县',
            ),
            542337 =>
            array(
                'code' => 170515,
                'area_name' => '萨嘎县',
                'name' => '萨嘎县',
            ),
            542338 =>
            array(
                'code' => 170513,
                'area_name' => '岗巴县',
                'name' => '岗巴县',
            ),
            542421 =>
            array(
                'code' => 170201,
                'area_name' => '那曲县',
                'name' => '那曲县',
            ),
            542422 =>
            array(
                'code' => 170202,
                'area_name' => '嘉黎县',
                'name' => '嘉黎县',
            ),
            542423 =>
            array(
                'code' => 170207,
                'area_name' => '比如县',
                'name' => '比如县',
            ),
            542424 =>
            array(
                'code' => 170205,
                'area_name' => '聂荣县',
                'name' => '聂荣县',
            ),
            542425 =>
            array(
                'code' => 170210,
                'area_name' => '安多县',
                'name' => '安多县',
            ),
            542426 =>
            array(
                'code' => 170203,
                'area_name' => '申扎县',
                'name' => '申扎县',
            ),
            542427 =>
            array(
                'code' => '',
                'area_name' => '索县',
                'name' => '',
            ),
            542428 =>
            array(
                'code' => 170209,
                'area_name' => '班戈县',
                'name' => '班戈县',
            ),
            542429 =>
            array(
                'code' => 170204,
                'area_name' => '巴青县',
                'name' => '巴青县',
            ),
            542430 =>
            array(
                'code' => 170206,
                'area_name' => '尼玛县',
                'name' => '尼玛县',
            ),
            542432 =>
            array(
                'code' => '',
                'area_name' => '双湖县',
                'name' => '',
            ),
            542521 =>
            array(
                'code' => 170603,
                'area_name' => '普兰县',
                'name' => '普兰县',
            ),
            542522 =>
            array(
                'code' => 170606,
                'area_name' => '札达县',
                'name' => '札达县',
            ),
            542523 =>
            array(
                'code' => 170601,
                'area_name' => '噶尔县',
                'name' => '噶尔县',
            ),
            542524 =>
            array(
                'code' => 170605,
                'area_name' => '日土县',
                'name' => '日土县',
            ),
            542525 =>
            array(
                'code' => 170604,
                'area_name' => '革吉县',
                'name' => '革吉县',
            ),
            542526 =>
            array(
                'code' => 170607,
                'area_name' => '改则县',
                'name' => '改则县',
            ),
            542527 =>
            array(
                'code' => 170602,
                'area_name' => '措勤县',
                'name' => '措勤县',
            ),
            542621 =>
            array(
                'code' => 170701,
                'area_name' => '林芝县',
                'name' => '林芝县',
            ),
            542622 =>
            array(
                'code' => 170707,
                'area_name' => '工布江达县',
                'name' => '工布江达县',
            ),
            542623 =>
            array(
                'code' => 170704,
                'area_name' => '米林县',
                'name' => '米林县',
            ),
            542624 =>
            array(
                'code' => 170702,
                'area_name' => '墨脱县',
                'name' => '墨脱县',
            ),
            542625 =>
            array(
                'code' => 170706,
                'area_name' => '波密县',
                'name' => '波密县',
            ),
            542626 =>
            array(
                'code' => 170705,
                'area_name' => '察隅县',
                'name' => '察隅县',
            ),
            542627 =>
            array(
                'code' => '',
                'area_name' => '朗县',
                'name' => '',
            ),
            610102 =>
            array(
                'code' => 390102,
                'area_name' => '新城区',
                'name' => '新城区',
            ),
            610103 =>
            array(
                'code' => 390103,
                'area_name' => '碑林区',
                'name' => '碑林区',
            ),
            610104 =>
            array(
                'code' => 390101,
                'area_name' => '莲湖区',
                'name' => '莲湖区',
            ),
            610111 =>
            array(
                'code' => 390105,
                'area_name' => '灞桥区',
                'name' => '灞桥区',
            ),
            610112 =>
            array(
                'code' => 390106,
                'area_name' => '未央区',
                'name' => '未央区',
            ),
            610113 =>
            array(
                'code' => 390104,
                'area_name' => '雁塔区',
                'name' => '雁塔区',
            ),
            610114 =>
            array(
                'code' => 390107,
                'area_name' => '阎良区',
                'name' => '阎良区',
            ),
            610115 =>
            array(
                'code' => '',
                'area_name' => '临潼区',
                'name' => '',
            ),
            610116 =>
            array(
                'code' => '',
                'area_name' => '长安区',
                'name' => '',
            ),
            610122 =>
            array(
                'code' => 390111,
                'area_name' => '蓝田县',
                'name' => '蓝田县',
            ),
            610124 =>
            array(
                'code' => 390113,
                'area_name' => '周至县',
                'name' => '周至县',
            ),
            610125 =>
            array(
                'code' => '',
                'area_name' => '户县',
                'name' => '',
            ),
            610126 =>
            array(
                'code' => 390110,
                'area_name' => '高陵县',
                'name' => '高陵县',
            ),
            610202 =>
            array(
                'code' => 390201,
                'area_name' => '王益区',
                'name' => '王益区',
            ),
            610203 =>
            array(
                'code' => 390202,
                'area_name' => '印台区',
                'name' => '印台区',
            ),
            610204 =>
            array(
                'code' => 390203,
                'area_name' => '耀州区',
                'name' => '耀州区',
            ),
            610222 =>
            array(
                'code' => 390204,
                'area_name' => '宜君县',
                'name' => '宜君县',
            ),
            610302 =>
            array(
                'code' => 390301,
                'area_name' => '渭滨区',
                'name' => '渭滨区',
            ),
            610303 =>
            array(
                'code' => 390302,
                'area_name' => '金台区',
                'name' => '金台区',
            ),
            610304 =>
            array(
                'code' => '',
                'area_name' => '陈仓区',
                'name' => '',
            ),
            610322 =>
            array(
                'code' => 390305,
                'area_name' => '凤翔县',
                'name' => '凤翔县',
            ),
            610323 =>
            array(
                'code' => 390304,
                'area_name' => '岐山县',
                'name' => '岐山县',
            ),
            610324 =>
            array(
                'code' => 390309,
                'area_name' => '扶风县',
                'name' => '扶风县',
            ),
            610326 =>
            array(
                'code' => '',
                'area_name' => '眉县',
                'name' => '',
            ),
            610327 =>
            array(
                'code' => '',
                'area_name' => '陇县',
                'name' => '',
            ),
            610328 =>
            array(
                'code' => 390310,
                'area_name' => '千阳县',
                'name' => '千阳县',
            ),
            610329 =>
            array(
                'code' => 390308,
                'area_name' => '麟游县',
                'name' => '麟游县',
            ),
            610330 =>
            array(
                'code' => '',
                'area_name' => '凤县',
                'name' => '',
            ),
            610331 =>
            array(
                'code' => 390307,
                'area_name' => '太白县',
                'name' => '太白县',
            ),
            610402 =>
            array(
                'code' => 390401,
                'area_name' => '秦都区',
                'name' => '秦都区',
            ),
            610403 =>
            array(
                'code' => 390403,
                'area_name' => '杨陵区',
                'name' => '杨陵区',
            ),
            610404 =>
            array(
                'code' => 390402,
                'area_name' => '渭城区',
                'name' => '渭城区',
            ),
            610422 =>
            array(
                'code' => 390408,
                'area_name' => '三原县',
                'name' => '三原县',
            ),
            610423 =>
            array(
                'code' => 390406,
                'area_name' => '泾阳县',
                'name' => '泾阳县',
            ),
            610424 =>
            array(
                'code' => '',
                'area_name' => '乾县',
                'name' => '',
            ),
            610425 =>
            array(
                'code' => 390405,
                'area_name' => '礼泉县',
                'name' => '礼泉县',
            ),
            610426 =>
            array(
                'code' => 390407,
                'area_name' => '永寿县',
                'name' => '永寿县',
            ),
            610427 =>
            array(
                'code' => '',
                'area_name' => '彬县',
                'name' => '',
            ),
            610428 =>
            array(
                'code' => 390411,
                'area_name' => '长武县',
                'name' => '长武县',
            ),
            610429 =>
            array(
                'code' => 390410,
                'area_name' => '旬邑县',
                'name' => '旬邑县',
            ),
            610430 =>
            array(
                'code' => 390414,
                'area_name' => '淳化县',
                'name' => '淳化县',
            ),
            610431 =>
            array(
                'code' => 390413,
                'area_name' => '武功县',
                'name' => '武功县',
            ),
            610481 =>
            array(
                'code' => 390404,
                'area_name' => '兴平市',
                'name' => '兴平市',
            ),
            610502 =>
            array(
                'code' => 390501,
                'area_name' => '临渭区',
                'name' => '临渭区',
            ),
            610521 =>
            array(
                'code' => '',
                'area_name' => '华县',
                'name' => '',
            ),
            610522 =>
            array(
                'code' => 390505,
                'area_name' => '潼关县',
                'name' => '潼关县',
            ),
            610523 =>
            array(
                'code' => 390511,
                'area_name' => '大荔县',
                'name' => '大荔县',
            ),
            610524 =>
            array(
                'code' => 390509,
                'area_name' => '合阳县',
                'name' => '合阳县',
            ),
            610525 =>
            array(
                'code' => 390507,
                'area_name' => '澄城县',
                'name' => '澄城县',
            ),
            610526 =>
            array(
                'code' => 390504,
                'area_name' => '蒲城县',
                'name' => '蒲城县',
            ),
            610527 =>
            array(
                'code' => 390506,
                'area_name' => '白水县',
                'name' => '白水县',
            ),
            610528 =>
            array(
                'code' => 390510,
                'area_name' => '富平县',
                'name' => '富平县',
            ),
            610581 =>
            array(
                'code' => 390502,
                'area_name' => '韩城市',
                'name' => '韩城市',
            ),
            610582 =>
            array(
                'code' => 390503,
                'area_name' => '华阴市',
                'name' => '华阴市',
            ),
            610602 =>
            array(
                'code' => 390601,
                'area_name' => '宝塔区',
                'name' => '宝塔区',
            ),
            610621 =>
            array(
                'code' => 390608,
                'area_name' => '延长县',
                'name' => '延长县',
            ),
            610622 =>
            array(
                'code' => 390606,
                'area_name' => '延川县',
                'name' => '延川县',
            ),
            610623 =>
            array(
                'code' => 390604,
                'area_name' => '子长县',
                'name' => '子长县',
            ),
            610624 =>
            array(
                'code' => 390602,
                'area_name' => '安塞县',
                'name' => '安塞县',
            ),
            610625 =>
            array(
                'code' => 390611,
                'area_name' => '志丹县',
                'name' => '志丹县',
            ),
            610626 =>
            array(
                'code' => '',
                'area_name' => '吴起县',
                'name' => '',
            ),
            610627 =>
            array(
                'code' => 390609,
                'area_name' => '甘泉县',
                'name' => '甘泉县',
            ),
            610628 =>
            array(
                'code' => '',
                'area_name' => '富县',
                'name' => '',
            ),
            610629 =>
            array(
                'code' => 390603,
                'area_name' => '洛川县',
                'name' => '洛川县',
            ),
            610630 =>
            array(
                'code' => 390610,
                'area_name' => '宜川县',
                'name' => '宜川县',
            ),
            610631 =>
            array(
                'code' => 390612,
                'area_name' => '黄龙县',
                'name' => '黄龙县',
            ),
            610632 =>
            array(
                'code' => 390605,
                'area_name' => '黄陵县',
                'name' => '黄陵县',
            ),
            610702 =>
            array(
                'code' => 390701,
                'area_name' => '汉台区',
                'name' => '汉台区',
            ),
            610721 =>
            array(
                'code' => 390705,
                'area_name' => '南郑县',
                'name' => '南郑县',
            ),
            610722 =>
            array(
                'code' => 390704,
                'area_name' => '城固县',
                'name' => '城固县',
            ),
            610723 =>
            array(
                'code' => '',
                'area_name' => '洋县',
                'name' => '',
            ),
            610724 =>
            array(
                'code' => 390710,
                'area_name' => '西乡县',
                'name' => '西乡县',
            ),
            610725 =>
            array(
                'code' => '',
                'area_name' => '勉县',
                'name' => '',
            ),
            610726 =>
            array(
                'code' => 390707,
                'area_name' => '宁强县',
                'name' => '宁强县',
            ),
            610727 =>
            array(
                'code' => 390711,
                'area_name' => '略阳县',
                'name' => '略阳县',
            ),
            610728 =>
            array(
                'code' => 390703,
                'area_name' => '镇巴县',
                'name' => '镇巴县',
            ),
            610729 =>
            array(
                'code' => 390702,
                'area_name' => '留坝县',
                'name' => '留坝县',
            ),
            610730 =>
            array(
                'code' => 390708,
                'area_name' => '佛坪县',
                'name' => '佛坪县',
            ),
            610802 =>
            array(
                'code' => 390801,
                'area_name' => '榆阳区',
                'name' => '榆阳区',
            ),
            610821 =>
            array(
                'code' => 390804,
                'area_name' => '神木县',
                'name' => '神木县',
            ),
            610822 =>
            array(
                'code' => 390806,
                'area_name' => '府谷县',
                'name' => '府谷县',
            ),
            610823 =>
            array(
                'code' => 390809,
                'area_name' => '横山县',
                'name' => '横山县',
            ),
            610824 =>
            array(
                'code' => 390808,
                'area_name' => '靖边县',
                'name' => '靖边县',
            ),
            610825 =>
            array(
                'code' => 390812,
                'area_name' => '定边县',
                'name' => '定边县',
            ),
            610826 =>
            array(
                'code' => 390803,
                'area_name' => '绥德县',
                'name' => '绥德县',
            ),
            610827 =>
            array(
                'code' => 390810,
                'area_name' => '米脂县',
                'name' => '米脂县',
            ),
            610828 =>
            array(
                'code' => '',
                'area_name' => '佳县',
                'name' => '',
            ),
            610829 =>
            array(
                'code' => 390811,
                'area_name' => '吴堡县',
                'name' => '吴堡县',
            ),
            610830 =>
            array(
                'code' => 390802,
                'area_name' => '清涧县',
                'name' => '清涧县',
            ),
            610831 =>
            array(
                'code' => 390807,
                'area_name' => '子洲县',
                'name' => '子洲县',
            ),
            610902 =>
            array(
                'code' => 390901,
                'area_name' => '汉滨区',
                'name' => '汉滨区',
            ),
            610921 =>
            array(
                'code' => 390910,
                'area_name' => '汉阴县',
                'name' => '汉阴县',
            ),
            610922 =>
            array(
                'code' => 390907,
                'area_name' => '石泉县',
                'name' => '石泉县',
            ),
            610923 =>
            array(
                'code' => 390908,
                'area_name' => '宁陕县',
                'name' => '宁陕县',
            ),
            610924 =>
            array(
                'code' => 390902,
                'area_name' => '紫阳县',
                'name' => '紫阳县',
            ),
            610925 =>
            array(
                'code' => 390903,
                'area_name' => '岚皋县',
                'name' => '岚皋县',
            ),
            610926 =>
            array(
                'code' => 390906,
                'area_name' => '平利县',
                'name' => '平利县',
            ),
            610927 =>
            array(
                'code' => 390905,
                'area_name' => '镇坪县',
                'name' => '镇坪县',
            ),
            610928 =>
            array(
                'code' => 390904,
                'area_name' => '旬阳县',
                'name' => '旬阳县',
            ),
            610929 =>
            array(
                'code' => 390909,
                'area_name' => '白河县',
                'name' => '白河县',
            ),
            611002 =>
            array(
                'code' => 391001,
                'area_name' => '商州区',
                'name' => '商州区',
            ),
            611021 =>
            array(
                'code' => 391004,
                'area_name' => '洛南县',
                'name' => '洛南县',
            ),
            611022 =>
            array(
                'code' => 391006,
                'area_name' => '丹凤县',
                'name' => '丹凤县',
            ),
            611023 =>
            array(
                'code' => 391005,
                'area_name' => '商南县',
                'name' => '商南县',
            ),
            611024 =>
            array(
                'code' => 391003,
                'area_name' => '山阳县',
                'name' => '山阳县',
            ),
            611025 =>
            array(
                'code' => 391002,
                'area_name' => '镇安县',
                'name' => '镇安县',
            ),
            611026 =>
            array(
                'code' => 391007,
                'area_name' => '柞水县',
                'name' => '柞水县',
            ),
            620102 =>
            array(
                'code' => 400101,
                'area_name' => '城关区',
                'name' => '城关区',
            ),
            620103 =>
            array(
                'code' => 400102,
                'area_name' => '七里河区',
                'name' => '七里河区',
            ),
            620104 =>
            array(
                'code' => 400103,
                'area_name' => '西固区',
                'name' => '西固区',
            ),
            620105 =>
            array(
                'code' => 400104,
                'area_name' => '安宁区',
                'name' => '安宁区',
            ),
            620111 =>
            array(
                'code' => 400105,
                'area_name' => '红古区',
                'name' => '红古区',
            ),
            620121 =>
            array(
                'code' => 400106,
                'area_name' => '永登县',
                'name' => '永登县',
            ),
            620122 =>
            array(
                'code' => 400108,
                'area_name' => '皋兰县',
                'name' => '皋兰县',
            ),
            620123 =>
            array(
                'code' => 400107,
                'area_name' => '榆中县',
                'name' => '榆中县',
            ),
            620302 =>
            array(
                'code' => 400201,
                'area_name' => '金川区',
                'name' => '金川区',
            ),
            620321 =>
            array(
                'code' => 400202,
                'area_name' => '永昌县',
                'name' => '永昌县',
            ),
            620402 =>
            array(
                'code' => 400301,
                'area_name' => '白银区',
                'name' => '白银区',
            ),
            620403 =>
            array(
                'code' => 400302,
                'area_name' => '平川区',
                'name' => '平川区',
            ),
            620421 =>
            array(
                'code' => 400303,
                'area_name' => '靖远县',
                'name' => '靖远县',
            ),
            620422 =>
            array(
                'code' => 400305,
                'area_name' => '会宁县',
                'name' => '会宁县',
            ),
            620423 =>
            array(
                'code' => 400304,
                'area_name' => '景泰县',
                'name' => '景泰县',
            ),
            620502 =>
            array(
                'code' => '',
                'area_name' => '秦州区',
                'name' => '',
            ),
            620503 =>
            array(
                'code' => '',
                'area_name' => '麦积区',
                'name' => '',
            ),
            620521 =>
            array(
                'code' => 400405,
                'area_name' => '清水县',
                'name' => '清水县',
            ),
            620522 =>
            array(
                'code' => 400406,
                'area_name' => '秦安县',
                'name' => '秦安县',
            ),
            620523 =>
            array(
                'code' => 400404,
                'area_name' => '甘谷县',
                'name' => '甘谷县',
            ),
            620524 =>
            array(
                'code' => 400403,
                'area_name' => '武山县',
                'name' => '武山县',
            ),
            620525 =>
            array(
                'code' => 400407,
                'area_name' => '张家川回族自治县',
                'name' => '张家川回族自治县',
            ),
            620602 =>
            array(
                'code' => 400601,
                'area_name' => '凉州区',
                'name' => '凉州区',
            ),
            620621 =>
            array(
                'code' => 400602,
                'area_name' => '民勤县',
                'name' => '民勤县',
            ),
            620622 =>
            array(
                'code' => 400603,
                'area_name' => '古浪县',
                'name' => '古浪县',
            ),
            620623 =>
            array(
                'code' => 400604,
                'area_name' => '天祝藏族自治县',
                'name' => '天祝藏族自治县',
            ),
            620702 =>
            array(
                'code' => 401101,
                'area_name' => '甘州区',
                'name' => '甘州区',
            ),
            620721 =>
            array(
                'code' => 401106,
                'area_name' => '肃南裕固族自治县',
                'name' => '肃南裕固族自治县',
            ),
            620722 =>
            array(
                'code' => 401102,
                'area_name' => '民乐县',
                'name' => '民乐县',
            ),
            620723 =>
            array(
                'code' => 401104,
                'area_name' => '临泽县',
                'name' => '临泽县',
            ),
            620724 =>
            array(
                'code' => 401105,
                'area_name' => '高台县',
                'name' => '高台县',
            ),
            620725 =>
            array(
                'code' => 401103,
                'area_name' => '山丹县',
                'name' => '山丹县',
            ),
            620802 =>
            array(
                'code' => 400801,
                'area_name' => '崆峒区',
                'name' => '崆峒区',
            ),
            620821 =>
            array(
                'code' => 400806,
                'area_name' => '泾川县',
                'name' => '泾川县',
            ),
            620822 =>
            array(
                'code' => 400802,
                'area_name' => '灵台县',
                'name' => '灵台县',
            ),
            620823 =>
            array(
                'code' => 400804,
                'area_name' => '崇信县',
                'name' => '崇信县',
            ),
            620824 =>
            array(
                'code' => 400805,
                'area_name' => '华亭县',
                'name' => '华亭县',
            ),
            620825 =>
            array(
                'code' => 400807,
                'area_name' => '庄浪县',
                'name' => '庄浪县',
            ),
            620826 =>
            array(
                'code' => 400803,
                'area_name' => '静宁县',
                'name' => '静宁县',
            ),
            620902 =>
            array(
                'code' => 401201,
                'area_name' => '肃州区',
                'name' => '肃州区',
            ),
            620921 =>
            array(
                'code' => 401205,
                'area_name' => '金塔县',
                'name' => '金塔县',
            ),
            620922 =>
            array(
                'code' => '',
                'area_name' => '瓜州县',
                'name' => '',
            ),
            620923 =>
            array(
                'code' => 401207,
                'area_name' => '肃北蒙古族自治县',
                'name' => '肃北蒙古族自治县',
            ),
            620924 =>
            array(
                'code' => 401206,
                'area_name' => '阿克塞哈萨克族自治县',
                'name' => '阿克塞哈萨克族自治县',
            ),
            620981 =>
            array(
                'code' => 401202,
                'area_name' => '玉门市',
                'name' => '玉门市',
            ),
            620982 =>
            array(
                'code' => 401203,
                'area_name' => '敦煌市',
                'name' => '敦煌市',
            ),
            621002 =>
            array(
                'code' => 400901,
                'area_name' => '西峰区',
                'name' => '西峰区',
            ),
            621021 =>
            array(
                'code' => 400902,
                'area_name' => '庆城县',
                'name' => '庆城县',
            ),
            621022 =>
            array(
                'code' => '',
                'area_name' => '环县',
                'name' => '',
            ),
            621023 =>
            array(
                'code' => 400905,
                'area_name' => '华池县',
                'name' => '华池县',
            ),
            621024 =>
            array(
                'code' => 400904,
                'area_name' => '合水县',
                'name' => '合水县',
            ),
            621025 =>
            array(
                'code' => 400908,
                'area_name' => '正宁县',
                'name' => '正宁县',
            ),
            621026 =>
            array(
                'code' => '',
                'area_name' => '宁县',
                'name' => '',
            ),
            621027 =>
            array(
                'code' => 400903,
                'area_name' => '镇原县',
                'name' => '镇原县',
            ),
            621102 =>
            array(
                'code' => '',
                'area_name' => '安定区',
                'name' => '',
            ),
            621121 =>
            array(
                'code' => '',
                'area_name' => '通渭县',
                'name' => '',
            ),
            621122 =>
            array(
                'code' => '',
                'area_name' => '陇西县',
                'name' => '',
            ),
            621123 =>
            array(
                'code' => '',
                'area_name' => '渭源县',
                'name' => '',
            ),
            621124 =>
            array(
                'code' => '',
                'area_name' => '临洮县',
                'name' => '',
            ),
            621125 =>
            array(
                'code' => '',
                'area_name' => '漳县',
                'name' => '',
            ),
            621126 =>
            array(
                'code' => '',
                'area_name' => '岷县',
                'name' => '',
            ),
            621202 =>
            array(
                'code' => '',
                'area_name' => '武都区',
                'name' => '',
            ),
            621221 =>
            array(
                'code' => '',
                'area_name' => '成县',
                'name' => '',
            ),
            621222 =>
            array(
                'code' => '',
                'area_name' => '文县',
                'name' => '',
            ),
            621223 =>
            array(
                'code' => '',
                'area_name' => '宕昌县',
                'name' => '',
            ),
            621224 =>
            array(
                'code' => '',
                'area_name' => '康县',
                'name' => '',
            ),
            621225 =>
            array(
                'code' => '',
                'area_name' => '西和县',
                'name' => '',
            ),
            621226 =>
            array(
                'code' => '',
                'area_name' => '礼县',
                'name' => '',
            ),
            621227 =>
            array(
                'code' => '',
                'area_name' => '徽县',
                'name' => '',
            ),
            621228 =>
            array(
                'code' => '',
                'area_name' => '两当县',
                'name' => '',
            ),
            622901 =>
            array(
                'code' => 401401,
                'area_name' => '临夏市',
                'name' => '临夏市',
            ),
            622921 =>
            array(
                'code' => 401402,
                'area_name' => '临夏县',
                'name' => '临夏县',
            ),
            622922 =>
            array(
                'code' => 401403,
                'area_name' => '康乐县',
                'name' => '康乐县',
            ),
            622923 =>
            array(
                'code' => 401404,
                'area_name' => '永靖县',
                'name' => '永靖县',
            ),
            622924 =>
            array(
                'code' => 401405,
                'area_name' => '广河县',
                'name' => '广河县',
            ),
            622925 =>
            array(
                'code' => 401406,
                'area_name' => '和政县',
                'name' => '和政县',
            ),
            622926 =>
            array(
                'code' => 401407,
                'area_name' => '东乡族自治县',
                'name' => '东乡族自治县',
            ),
            622927 =>
            array(
                'code' => 401408,
                'area_name' => '积石山保安族东乡族撒拉族自治县',
                'name' => '积石山保安族东乡族撒拉族自治县',
            ),
            623001 =>
            array(
                'code' => 401301,
                'area_name' => '合作市',
                'name' => '合作市',
            ),
            623021 =>
            array(
                'code' => 401302,
                'area_name' => '临潭县',
                'name' => '临潭县',
            ),
            623022 =>
            array(
                'code' => 401303,
                'area_name' => '卓尼县',
                'name' => '卓尼县',
            ),
            623023 =>
            array(
                'code' => 401304,
                'area_name' => '舟曲县',
                'name' => '舟曲县',
            ),
            623024 =>
            array(
                'code' => 401305,
                'area_name' => '迭部县',
                'name' => '迭部县',
            ),
            623025 =>
            array(
                'code' => 401306,
                'area_name' => '玛曲县',
                'name' => '玛曲县',
            ),
            623026 =>
            array(
                'code' => 401307,
                'area_name' => '碌曲县',
                'name' => '碌曲县',
            ),
            623027 =>
            array(
                'code' => 401308,
                'area_name' => '夏河县',
                'name' => '夏河县',
            ),
            630102 =>
            array(
                'code' => 410102,
                'area_name' => '城东区',
                'name' => '城东区',
            ),
            630103 =>
            array(
                'code' => 410101,
                'area_name' => '城中区',
                'name' => '城中区',
            ),
            630104 =>
            array(
                'code' => 410103,
                'area_name' => '城西区',
                'name' => '城西区',
            ),
            630105 =>
            array(
                'code' => 410104,
                'area_name' => '城北区',
                'name' => '城北区',
            ),
            630121 =>
            array(
                'code' => 410107,
                'area_name' => '大通回族土族自治县',
                'name' => '大通回族土族自治县',
            ),
            630122 =>
            array(
                'code' => 410106,
                'area_name' => '湟中县',
                'name' => '湟中县',
            ),
            630123 =>
            array(
                'code' => 410105,
                'area_name' => '湟源县',
                'name' => '湟源县',
            ),
            632121 =>
            array(
                'code' => '',
                'area_name' => '平安县',
                'name' => '',
            ),
            632122 =>
            array(
                'code' => '',
                'area_name' => '民和回族土族自治县',
                'name' => '',
            ),
            632123 =>
            array(
                'code' => '',
                'area_name' => '乐都区',
                'name' => '',
            ),
            632126 =>
            array(
                'code' => '',
                'area_name' => '互助土族自治县',
                'name' => '',
            ),
            632127 =>
            array(
                'code' => '',
                'area_name' => '化隆回族自治县',
                'name' => '',
            ),
            632128 =>
            array(
                'code' => '',
                'area_name' => '循化撒拉族自治县',
                'name' => '',
            ),
            632221 =>
            array(
                'code' => 410304,
                'area_name' => '门源回族自治县',
                'name' => '门源回族自治县',
            ),
            632222 =>
            array(
                'code' => 410302,
                'area_name' => '祁连县',
                'name' => '祁连县',
            ),
            632223 =>
            array(
                'code' => 410301,
                'area_name' => '海晏县',
                'name' => '海晏县',
            ),
            632224 =>
            array(
                'code' => 410303,
                'area_name' => '刚察县',
                'name' => '刚察县',
            ),
            632321 =>
            array(
                'code' => 410401,
                'area_name' => '同仁县',
                'name' => '同仁县',
            ),
            632322 =>
            array(
                'code' => 410403,
                'area_name' => '尖扎县',
                'name' => '尖扎县',
            ),
            632323 =>
            array(
                'code' => 410402,
                'area_name' => '泽库县',
                'name' => '泽库县',
            ),
            632324 =>
            array(
                'code' => 410404,
                'area_name' => '河南蒙古族自治县',
                'name' => '河南蒙古族自治县',
            ),
            632521 =>
            array(
                'code' => 410501,
                'area_name' => '共和县',
                'name' => '共和县',
            ),
            632522 =>
            array(
                'code' => 410502,
                'area_name' => '同德县',
                'name' => '同德县',
            ),
            632523 =>
            array(
                'code' => 410503,
                'area_name' => '贵德县',
                'name' => '贵德县',
            ),
            632524 =>
            array(
                'code' => 410504,
                'area_name' => '兴海县',
                'name' => '兴海县',
            ),
            632525 =>
            array(
                'code' => 410505,
                'area_name' => '贵南县',
                'name' => '贵南县',
            ),
            632621 =>
            array(
                'code' => 410601,
                'area_name' => '玛沁县',
                'name' => '玛沁县',
            ),
            632622 =>
            array(
                'code' => 410602,
                'area_name' => '班玛县',
                'name' => '班玛县',
            ),
            632623 =>
            array(
                'code' => 410603,
                'area_name' => '甘德县',
                'name' => '甘德县',
            ),
            632624 =>
            array(
                'code' => 410604,
                'area_name' => '达日县',
                'name' => '达日县',
            ),
            632625 =>
            array(
                'code' => 410605,
                'area_name' => '久治县',
                'name' => '久治县',
            ),
            632626 =>
            array(
                'code' => 410606,
                'area_name' => '玛多县',
                'name' => '玛多县',
            ),
            632721 =>
            array(
                'code' => '',
                'area_name' => '玉树市',
                'name' => '',
            ),
            632722 =>
            array(
                'code' => 410702,
                'area_name' => '杂多县',
                'name' => '杂多县',
            ),
            632723 =>
            array(
                'code' => 410703,
                'area_name' => '称多县',
                'name' => '称多县',
            ),
            632724 =>
            array(
                'code' => 410704,
                'area_name' => '治多县',
                'name' => '治多县',
            ),
            632725 =>
            array(
                'code' => 410705,
                'area_name' => '囊谦县',
                'name' => '囊谦县',
            ),
            632726 =>
            array(
                'code' => 410706,
                'area_name' => '曲麻莱县',
                'name' => '曲麻莱县',
            ),
            632801 =>
            array(
                'code' => 410802,
                'area_name' => '格尔木市',
                'name' => '格尔木市',
            ),
            632802 =>
            array(
                'code' => 410801,
                'area_name' => '德令哈市',
                'name' => '德令哈市',
            ),
            632821 =>
            array(
                'code' => 410803,
                'area_name' => '乌兰县',
                'name' => '乌兰县',
            ),
            632822 =>
            array(
                'code' => 410805,
                'area_name' => '都兰县',
                'name' => '都兰县',
            ),
            632823 =>
            array(
                'code' => 410804,
                'area_name' => '天峻县',
                'name' => '天峻县',
            ),
            640104 =>
            array(
                'code' => 180101,
                'area_name' => '兴庆区',
                'name' => '兴庆区',
            ),
            640105 =>
            array(
                'code' => 180103,
                'area_name' => '西夏区',
                'name' => '西夏区',
            ),
            640106 =>
            array(
                'code' => 180102,
                'area_name' => '金凤区',
                'name' => '金凤区',
            ),
            640121 =>
            array(
                'code' => 180105,
                'area_name' => '永宁县',
                'name' => '永宁县',
            ),
            640122 =>
            array(
                'code' => 180106,
                'area_name' => '贺兰县',
                'name' => '贺兰县',
            ),
            640181 =>
            array(
                'code' => 180104,
                'area_name' => '灵武市',
                'name' => '灵武市',
            ),
            640202 =>
            array(
                'code' => 180201,
                'area_name' => '大武口区',
                'name' => '大武口区',
            ),
            640205 =>
            array(
                'code' => 180202,
                'area_name' => '惠农区',
                'name' => '惠农区',
            ),
            640221 =>
            array(
                'code' => 180203,
                'area_name' => '平罗县',
                'name' => '平罗县',
            ),
            640302 =>
            array(
                'code' => 180301,
                'area_name' => '利通区',
                'name' => '利通区',
            ),
            640303 =>
            array(
                'code' => '',
                'area_name' => '红寺堡区',
                'name' => '',
            ),
            640323 =>
            array(
                'code' => 180304,
                'area_name' => '盐池县',
                'name' => '盐池县',
            ),
            640324 =>
            array(
                'code' => 180303,
                'area_name' => '同心县',
                'name' => '同心县',
            ),
            640381 =>
            array(
                'code' => 180302,
                'area_name' => '青铜峡市',
                'name' => '青铜峡市',
            ),
            640402 =>
            array(
                'code' => 180401,
                'area_name' => '原州区',
                'name' => '原州区',
            ),
            640422 =>
            array(
                'code' => 180402,
                'area_name' => '西吉县',
                'name' => '西吉县',
            ),
            640423 =>
            array(
                'code' => 180403,
                'area_name' => '隆德县',
                'name' => '隆德县',
            ),
            640424 =>
            array(
                'code' => 180404,
                'area_name' => '泾源县',
                'name' => '泾源县',
            ),
            640425 =>
            array(
                'code' => 180405,
                'area_name' => '彭阳县',
                'name' => '彭阳县',
            ),
            640502 =>
            array(
                'code' => 180501,
                'area_name' => '沙坡头区',
                'name' => '沙坡头区',
            ),
            640521 =>
            array(
                'code' => 180502,
                'area_name' => '中宁县',
                'name' => '中宁县',
            ),
            640522 =>
            array(
                'code' => 180503,
                'area_name' => '海原县',
                'name' => '海原县',
            ),
            650102 =>
            array(
                'code' => 190101,
                'area_name' => '天山区',
                'name' => '天山区',
            ),
            650103 =>
            array(
                'code' => 190102,
                'area_name' => '沙依巴克区',
                'name' => '沙依巴克区',
            ),
            650104 =>
            array(
                'code' => 190103,
                'area_name' => '新市区',
                'name' => '新市区',
            ),
            650105 =>
            array(
                'code' => 190104,
                'area_name' => '水磨沟区',
                'name' => '水磨沟区',
            ),
            650106 =>
            array(
                'code' => 190105,
                'area_name' => '头屯河区',
                'name' => '头屯河区',
            ),
            650107 =>
            array(
                'code' => 190106,
                'area_name' => '达坂城区',
                'name' => '达坂城区',
            ),
            650109 =>
            array(
                'code' => 190107,
                'area_name' => '米东区',
                'name' => '米东区',
            ),
            650121 =>
            array(
                'code' => 190108,
                'area_name' => '乌鲁木齐县',
                'name' => '乌鲁木齐县',
            ),
            650202 =>
            array(
                'code' => 190202,
                'area_name' => '独山子区',
                'name' => '独山子区',
            ),
            650203 =>
            array(
                'code' => 190201,
                'area_name' => '克拉玛依区',
                'name' => '克拉玛依区',
            ),
            650204 =>
            array(
                'code' => 190203,
                'area_name' => '白碱滩区',
                'name' => '白碱滩区',
            ),
            650205 =>
            array(
                'code' => 190204,
                'area_name' => '乌尔禾区',
                'name' => '乌尔禾区',
            ),
            652101 =>
            array(
                'code' => 190701,
                'area_name' => '吐鲁番市',
                'name' => '吐鲁番市',
            ),
            652122 =>
            array(
                'code' => 190703,
                'area_name' => '鄯善县',
                'name' => '鄯善县',
            ),
            652123 =>
            array(
                'code' => 190702,
                'area_name' => '托克逊县',
                'name' => '托克逊县',
            ),
            652201 =>
            array(
                'code' => 190801,
                'area_name' => '哈密市',
                'name' => '哈密市',
            ),
            652222 =>
            array(
                'code' => 190803,
                'area_name' => '巴里坤哈萨克自治县',
                'name' => '巴里坤哈萨克自治县',
            ),
            652223 =>
            array(
                'code' => 190802,
                'area_name' => '伊吾县',
                'name' => '伊吾县',
            ),
            652301 =>
            array(
                'code' => 191401,
                'area_name' => '昌吉市',
                'name' => '昌吉市',
            ),
            652302 =>
            array(
                'code' => 191402,
                'area_name' => '阜康市',
                'name' => '阜康市',
            ),
            652323 =>
            array(
                'code' => 191406,
                'area_name' => '呼图壁县',
                'name' => '呼图壁县',
            ),
            652324 =>
            array(
                'code' => 191404,
                'area_name' => '玛纳斯县',
                'name' => '玛纳斯县',
            ),
            652325 =>
            array(
                'code' => 191403,
                'area_name' => '奇台县',
                'name' => '奇台县',
            ),
            652327 =>
            array(
                'code' => 191405,
                'area_name' => '吉木萨尔县',
                'name' => '吉木萨尔县',
            ),
            652328 =>
            array(
                'code' => 191407,
                'area_name' => '木垒哈萨克自治县',
                'name' => '木垒哈萨克自治县',
            ),
            652701 =>
            array(
                'code' => 191501,
                'area_name' => '博乐市',
                'name' => '博乐市',
            ),
            652702 =>
            array(
                'code' => '',
                'area_name' => '阿拉山口市',
                'name' => '',
            ),
            652722 =>
            array(
                'code' => 191502,
                'area_name' => '精河县',
                'name' => '精河县',
            ),
            652723 =>
            array(
                'code' => 191503,
                'area_name' => '温泉县',
                'name' => '温泉县',
            ),
            652801 =>
            array(
                'code' => 191301,
                'area_name' => '库尔勒市',
                'name' => '库尔勒市',
            ),
            652822 =>
            array(
                'code' => 191307,
                'area_name' => '轮台县',
                'name' => '轮台县',
            ),
            652823 =>
            array(
                'code' => 191303,
                'area_name' => '尉犁县',
                'name' => '尉犁县',
            ),
            652824 =>
            array(
                'code' => 191308,
                'area_name' => '若羌县',
                'name' => '若羌县',
            ),
            652825 =>
            array(
                'code' => 191305,
                'area_name' => '且末县',
                'name' => '且末县',
            ),
            652826 =>
            array(
                'code' => 191309,
                'area_name' => '焉耆回族自治县',
                'name' => '焉耆回族自治县',
            ),
            652827 =>
            array(
                'code' => 191302,
                'area_name' => '和静县',
                'name' => '和静县',
            ),
            652828 =>
            array(
                'code' => 191304,
                'area_name' => '和硕县',
                'name' => '和硕县',
            ),
            652829 =>
            array(
                'code' => 191306,
                'area_name' => '博湖县',
                'name' => '博湖县',
            ),
            652901 =>
            array(
                'code' => 191001,
                'area_name' => '阿克苏市',
                'name' => '阿克苏市',
            ),
            652922 =>
            array(
                'code' => 191002,
                'area_name' => '温宿县',
                'name' => '温宿县',
            ),
            652923 =>
            array(
                'code' => 191006,
                'area_name' => '库车县',
                'name' => '库车县',
            ),
            652924 =>
            array(
                'code' => 191003,
                'area_name' => '沙雅县',
                'name' => '沙雅县',
            ),
            652925 =>
            array(
                'code' => 191008,
                'area_name' => '新和县',
                'name' => '新和县',
            ),
            652926 =>
            array(
                'code' => 191004,
                'area_name' => '拜城县',
                'name' => '拜城县',
            ),
            652927 =>
            array(
                'code' => 191009,
                'area_name' => '乌什县',
                'name' => '乌什县',
            ),
            652928 =>
            array(
                'code' => 191005,
                'area_name' => '阿瓦提县',
                'name' => '阿瓦提县',
            ),
            652929 =>
            array(
                'code' => 191007,
                'area_name' => '柯坪县',
                'name' => '柯坪县',
            ),
            653001 =>
            array(
                'code' => 191201,
                'area_name' => '阿图什市',
                'name' => '阿图什市',
            ),
            653022 =>
            array(
                'code' => 191204,
                'area_name' => '阿克陶县',
                'name' => '阿克陶县',
            ),
            653023 =>
            array(
                'code' => 191202,
                'area_name' => '阿合奇县',
                'name' => '阿合奇县',
            ),
            653024 =>
            array(
                'code' => 191203,
                'area_name' => '乌恰县',
                'name' => '乌恰县',
            ),
            653101 =>
            array(
                'code' => 191101,
                'area_name' => '喀什市',
                'name' => '喀什市',
            ),
            653121 =>
            array(
                'code' => 191111,
                'area_name' => '疏附县',
                'name' => '疏附县',
            ),
            653122 =>
            array(
                'code' => 191107,
                'area_name' => '疏勒县',
                'name' => '疏勒县',
            ),
            653123 =>
            array(
                'code' => 191109,
                'area_name' => '英吉沙县',
                'name' => '英吉沙县',
            ),
            653124 =>
            array(
                'code' => 191103,
                'area_name' => '泽普县',
                'name' => '泽普县',
            ),
            653125 =>
            array(
                'code' => 191110,
                'area_name' => '莎车县',
                'name' => '莎车县',
            ),
            653126 =>
            array(
                'code' => 191105,
                'area_name' => '叶城县',
                'name' => '叶城县',
            ),
            653127 =>
            array(
                'code' => 191108,
                'area_name' => '麦盖提县',
                'name' => '麦盖提县',
            ),
            653128 =>
            array(
                'code' => 191106,
                'area_name' => '岳普湖县',
                'name' => '岳普湖县',
            ),
            653129 =>
            array(
                'code' => 191104,
                'area_name' => '伽师县',
                'name' => '伽师县',
            ),
            653130 =>
            array(
                'code' => 191102,
                'area_name' => '巴楚县',
                'name' => '巴楚县',
            ),
            653131 =>
            array(
                'code' => 191112,
                'area_name' => '塔什库尔干塔吉克自治县',
                'name' => '塔什库尔干塔吉克自治县',
            ),
            653201 =>
            array(
                'code' => 190901,
                'area_name' => '和田市',
                'name' => '和田市',
            ),
            653221 =>
            array(
                'code' => 190902,
                'area_name' => '和田县',
                'name' => '和田县',
            ),
            653222 =>
            array(
                'code' => 190908,
                'area_name' => '墨玉县',
                'name' => '墨玉县',
            ),
            653223 =>
            array(
                'code' => 190905,
                'area_name' => '皮山县',
                'name' => '皮山县',
            ),
            653224 =>
            array(
                'code' => 190903,
                'area_name' => '洛浦县',
                'name' => '洛浦县',
            ),
            653225 =>
            array(
                'code' => 190906,
                'area_name' => '策勒县',
                'name' => '策勒县',
            ),
            653226 =>
            array(
                'code' => 190907,
                'area_name' => '于田县',
                'name' => '于田县',
            ),
            653227 =>
            array(
                'code' => 190904,
                'area_name' => '民丰县',
                'name' => '民丰县',
            ),
            654002 =>
            array(
                'code' => 191601,
                'area_name' => '伊宁市',
                'name' => '伊宁市',
            ),
            654003 =>
            array(
                'code' => 191602,
                'area_name' => '奎屯市',
                'name' => '奎屯市',
            ),
            654021 =>
            array(
                'code' => 191603,
                'area_name' => '伊宁县',
                'name' => '伊宁县',
            ),
            654022 =>
            array(
                'code' => 191610,
                'area_name' => '察布查尔锡伯自治县',
                'name' => '察布查尔锡伯自治县',
            ),
            654023 =>
            array(
                'code' => 191608,
                'area_name' => '霍城县',
                'name' => '霍城县',
            ),
            654024 =>
            array(
                'code' => 191609,
                'area_name' => '巩留县',
                'name' => '巩留县',
            ),
            654025 =>
            array(
                'code' => 191607,
                'area_name' => '新源县',
                'name' => '新源县',
            ),
            654026 =>
            array(
                'code' => 191606,
                'area_name' => '昭苏县',
                'name' => '昭苏县',
            ),
            654027 =>
            array(
                'code' => 191604,
                'area_name' => '特克斯县',
                'name' => '特克斯县',
            ),
            654028 =>
            array(
                'code' => 191605,
                'area_name' => '尼勒克县',
                'name' => '尼勒克县',
            ),
            654201 =>
            array(
                'code' => '',
                'area_name' => '塔城市',
                'name' => '',
            ),
            654202 =>
            array(
                'code' => '',
                'area_name' => '乌苏市',
                'name' => '',
            ),
            654221 =>
            array(
                'code' => '',
                'area_name' => '额敏县',
                'name' => '',
            ),
            654223 =>
            array(
                'code' => '',
                'area_name' => '沙湾县',
                'name' => '',
            ),
            654224 =>
            array(
                'code' => '',
                'area_name' => '托里县',
                'name' => '',
            ),
            654225 =>
            array(
                'code' => '',
                'area_name' => '裕民县',
                'name' => '',
            ),
            654226 =>
            array(
                'code' => '',
                'area_name' => '和布克赛尔蒙古自治县',
                'name' => '',
            ),
            654301 =>
            array(
                'code' => '',
                'area_name' => '阿勒泰市',
                'name' => '',
            ),
            654321 =>
            array(
                'code' => '',
                'area_name' => '布尔津县',
                'name' => '',
            ),
            654322 =>
            array(
                'code' => '',
                'area_name' => '富蕴县',
                'name' => '',
            ),
            654323 =>
            array(
                'code' => '',
                'area_name' => '福海县',
                'name' => '',
            ),
            654324 =>
            array(
                'code' => '',
                'area_name' => '哈巴河县',
                'name' => '',
            ),
            654325 =>
            array(
                'code' => '',
                'area_name' => '青河县',
                'name' => '',
            ),
            654326 =>
            array(
                'code' => '',
                'area_name' => '吉木乃县',
                'name' => '',
            ),
            710101 =>
            array(
                'code' => '',
                'area_name' => '中正区',
                'name' => '',
            ),
            710102 =>
            array(
                'code' => '',
                'area_name' => '大同区',
                'name' => '',
            ),
            710103 =>
            array(
                'code' => '',
                'area_name' => '中山区',
                'name' => '',
            ),
            710104 =>
            array(
                'code' => '',
                'area_name' => '松山区',
                'name' => '',
            ),
            710105 =>
            array(
                'code' => '',
                'area_name' => '大安区',
                'name' => '',
            ),
            710106 =>
            array(
                'code' => '',
                'area_name' => '万华区',
                'name' => '',
            ),
            710107 =>
            array(
                'code' => '',
                'area_name' => '信义区',
                'name' => '',
            ),
            710108 =>
            array(
                'code' => '',
                'area_name' => '士林区',
                'name' => '',
            ),
            710109 =>
            array(
                'code' => '',
                'area_name' => '北投区',
                'name' => '',
            ),
            710110 =>
            array(
                'code' => '',
                'area_name' => '内湖区',
                'name' => '',
            ),
            710111 =>
            array(
                'code' => '',
                'area_name' => '南港区',
                'name' => '',
            ),
            710112 =>
            array(
                'code' => '',
                'area_name' => '文山区',
                'name' => '',
            ),
            710201 =>
            array(
                'code' => '',
                'area_name' => '新兴区',
                'name' => '',
            ),
            710202 =>
            array(
                'code' => '',
                'area_name' => '前金区',
                'name' => '',
            ),
            710203 =>
            array(
                'code' => '',
                'area_name' => '芩雅区',
                'name' => '',
            ),
            710204 =>
            array(
                'code' => '',
                'area_name' => '盐埕区',
                'name' => '',
            ),
            710205 =>
            array(
                'code' => '',
                'area_name' => '鼓山区',
                'name' => '',
            ),
            710206 =>
            array(
                'code' => '',
                'area_name' => '旗津区',
                'name' => '',
            ),
            710207 =>
            array(
                'code' => '',
                'area_name' => '前镇区',
                'name' => '',
            ),
            710208 =>
            array(
                'code' => '',
                'area_name' => '三民区',
                'name' => '',
            ),
            710209 =>
            array(
                'code' => '',
                'area_name' => '左营区',
                'name' => '',
            ),
            710210 =>
            array(
                'code' => '',
                'area_name' => '楠梓区',
                'name' => '',
            ),
            710211 =>
            array(
                'code' => '',
                'area_name' => '小港区',
                'name' => '',
            ),
            710241 =>
            array(
                'code' => '',
                'area_name' => '苓雅区',
                'name' => '',
            ),
            710242 =>
            array(
                'code' => '',
                'area_name' => '仁武区',
                'name' => '',
            ),
            710243 =>
            array(
                'code' => '',
                'area_name' => '大社区',
                'name' => '',
            ),
            710244 =>
            array(
                'code' => '',
                'area_name' => '冈山区',
                'name' => '',
            ),
            710245 =>
            array(
                'code' => '',
                'area_name' => '路竹区',
                'name' => '',
            ),
            710246 =>
            array(
                'code' => '',
                'area_name' => '阿莲区',
                'name' => '',
            ),
            710247 =>
            array(
                'code' => '',
                'area_name' => '田寮区',
                'name' => '',
            ),
            710248 =>
            array(
                'code' => '',
                'area_name' => '燕巢区',
                'name' => '',
            ),
            710249 =>
            array(
                'code' => '',
                'area_name' => '桥头区',
                'name' => '',
            ),
            710250 =>
            array(
                'code' => '',
                'area_name' => '梓官区',
                'name' => '',
            ),
            710251 =>
            array(
                'code' => '',
                'area_name' => '弥陀区',
                'name' => '',
            ),
            710252 =>
            array(
                'code' => '',
                'area_name' => '永安区',
                'name' => '',
            ),
            710253 =>
            array(
                'code' => '',
                'area_name' => '湖内区',
                'name' => '',
            ),
            710254 =>
            array(
                'code' => '',
                'area_name' => '凤山区',
                'name' => '',
            ),
            710255 =>
            array(
                'code' => '',
                'area_name' => '大寮区',
                'name' => '',
            ),
            710256 =>
            array(
                'code' => '',
                'area_name' => '林园区',
                'name' => '',
            ),
            710257 =>
            array(
                'code' => '',
                'area_name' => '鸟松区',
                'name' => '',
            ),
            710258 =>
            array(
                'code' => '',
                'area_name' => '大树区',
                'name' => '',
            ),
            710259 =>
            array(
                'code' => '',
                'area_name' => '旗山区',
                'name' => '',
            ),
            710260 =>
            array(
                'code' => '',
                'area_name' => '美浓区',
                'name' => '',
            ),
            710261 =>
            array(
                'code' => '',
                'area_name' => '六龟区',
                'name' => '',
            ),
            710262 =>
            array(
                'code' => '',
                'area_name' => '内门区',
                'name' => '',
            ),
            710263 =>
            array(
                'code' => '',
                'area_name' => '杉林区',
                'name' => '',
            ),
            710264 =>
            array(
                'code' => '',
                'area_name' => '甲仙区',
                'name' => '',
            ),
            710265 =>
            array(
                'code' => '',
                'area_name' => '桃源区',
                'name' => '',
            ),
            710266 =>
            array(
                'code' => '',
                'area_name' => '那玛夏区',
                'name' => '',
            ),
            710267 =>
            array(
                'code' => '',
                'area_name' => '茂林区',
                'name' => '',
            ),
            710268 =>
            array(
                'code' => '',
                'area_name' => '茄萣区',
                'name' => '',
            ),
            710301 =>
            array(
                'code' => '',
                'area_name' => '中西区',
                'name' => '',
            ),
            710302 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            710303 =>
            array(
                'code' => '',
                'area_name' => '南区',
                'name' => '',
            ),
            710304 =>
            array(
                'code' => '',
                'area_name' => '北区',
                'name' => '',
            ),
            710305 =>
            array(
                'code' => '',
                'area_name' => '安平区',
                'name' => '',
            ),
            710306 =>
            array(
                'code' => '',
                'area_name' => '安南区',
                'name' => '',
            ),
            710339 =>
            array(
                'code' => '',
                'area_name' => '永康区',
                'name' => '',
            ),
            710340 =>
            array(
                'code' => '',
                'area_name' => '归仁区',
                'name' => '',
            ),
            710341 =>
            array(
                'code' => '',
                'area_name' => '新化区',
                'name' => '',
            ),
            710342 =>
            array(
                'code' => '',
                'area_name' => '左镇区',
                'name' => '',
            ),
            710343 =>
            array(
                'code' => '',
                'area_name' => '玉井区',
                'name' => '',
            ),
            710344 =>
            array(
                'code' => '',
                'area_name' => '楠西区',
                'name' => '',
            ),
            710345 =>
            array(
                'code' => '',
                'area_name' => '南化区',
                'name' => '',
            ),
            710346 =>
            array(
                'code' => '',
                'area_name' => '仁德区',
                'name' => '',
            ),
            710347 =>
            array(
                'code' => '',
                'area_name' => '关庙区',
                'name' => '',
            ),
            710348 =>
            array(
                'code' => '',
                'area_name' => '龙崎区',
                'name' => '',
            ),
            710349 =>
            array(
                'code' => '',
                'area_name' => '官田区',
                'name' => '',
            ),
            710350 =>
            array(
                'code' => '',
                'area_name' => '麻豆区',
                'name' => '',
            ),
            710351 =>
            array(
                'code' => '',
                'area_name' => '佳里区',
                'name' => '',
            ),
            710352 =>
            array(
                'code' => '',
                'area_name' => '西港区',
                'name' => '',
            ),
            710353 =>
            array(
                'code' => '',
                'area_name' => '七股区',
                'name' => '',
            ),
            710354 =>
            array(
                'code' => '',
                'area_name' => '将军区',
                'name' => '',
            ),
            710355 =>
            array(
                'code' => '',
                'area_name' => '学甲区',
                'name' => '',
            ),
            710356 =>
            array(
                'code' => '',
                'area_name' => '北门区',
                'name' => '',
            ),
            710357 =>
            array(
                'code' => '',
                'area_name' => '新营区',
                'name' => '',
            ),
            710358 =>
            array(
                'code' => '',
                'area_name' => '后壁区',
                'name' => '',
            ),
            710359 =>
            array(
                'code' => '',
                'area_name' => '白河区',
                'name' => '',
            ),
            710360 =>
            array(
                'code' => '',
                'area_name' => '东山区',
                'name' => '',
            ),
            710361 =>
            array(
                'code' => '',
                'area_name' => '六甲区',
                'name' => '',
            ),
            710362 =>
            array(
                'code' => '',
                'area_name' => '下营区',
                'name' => '',
            ),
            710363 =>
            array(
                'code' => '',
                'area_name' => '柳营区',
                'name' => '',
            ),
            710364 =>
            array(
                'code' => '',
                'area_name' => '盐水区',
                'name' => '',
            ),
            710365 =>
            array(
                'code' => '',
                'area_name' => '善化区',
                'name' => '',
            ),
            710366 =>
            array(
                'code' => '',
                'area_name' => '大内区',
                'name' => '',
            ),
            710367 =>
            array(
                'code' => '',
                'area_name' => '山上区',
                'name' => '',
            ),
            710368 =>
            array(
                'code' => '',
                'area_name' => '新市区',
                'name' => '',
            ),
            710369 =>
            array(
                'code' => '',
                'area_name' => '安定区',
                'name' => '',
            ),
            710401 =>
            array(
                'code' => '',
                'area_name' => '中区',
                'name' => '',
            ),
            710402 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            710403 =>
            array(
                'code' => '',
                'area_name' => '南区',
                'name' => '',
            ),
            710404 =>
            array(
                'code' => '',
                'area_name' => '西区',
                'name' => '',
            ),
            710405 =>
            array(
                'code' => '',
                'area_name' => '北区',
                'name' => '',
            ),
            710406 =>
            array(
                'code' => '',
                'area_name' => '北屯区',
                'name' => '',
            ),
            710407 =>
            array(
                'code' => '',
                'area_name' => '西屯区',
                'name' => '',
            ),
            710408 =>
            array(
                'code' => '',
                'area_name' => '南屯区',
                'name' => '',
            ),
            710431 =>
            array(
                'code' => '',
                'area_name' => '太平区',
                'name' => '',
            ),
            710432 =>
            array(
                'code' => '',
                'area_name' => '大里区',
                'name' => '',
            ),
            710433 =>
            array(
                'code' => '',
                'area_name' => '雾峰区',
                'name' => '',
            ),
            710434 =>
            array(
                'code' => '',
                'area_name' => '乌日区',
                'name' => '',
            ),
            710435 =>
            array(
                'code' => '',
                'area_name' => '丰原区',
                'name' => '',
            ),
            710436 =>
            array(
                'code' => '',
                'area_name' => '后里区',
                'name' => '',
            ),
            710437 =>
            array(
                'code' => '',
                'area_name' => '石冈区',
                'name' => '',
            ),
            710438 =>
            array(
                'code' => '',
                'area_name' => '东势区',
                'name' => '',
            ),
            710439 =>
            array(
                'code' => '',
                'area_name' => '和平区',
                'name' => '',
            ),
            710440 =>
            array(
                'code' => '',
                'area_name' => '新社区',
                'name' => '',
            ),
            710441 =>
            array(
                'code' => '',
                'area_name' => '潭子区',
                'name' => '',
            ),
            710442 =>
            array(
                'code' => '',
                'area_name' => '大雅区',
                'name' => '',
            ),
            710443 =>
            array(
                'code' => '',
                'area_name' => '神冈区',
                'name' => '',
            ),
            710444 =>
            array(
                'code' => '',
                'area_name' => '大肚区',
                'name' => '',
            ),
            710445 =>
            array(
                'code' => '',
                'area_name' => '沙鹿区',
                'name' => '',
            ),
            710446 =>
            array(
                'code' => '',
                'area_name' => '龙井区',
                'name' => '',
            ),
            710447 =>
            array(
                'code' => '',
                'area_name' => '梧栖区',
                'name' => '',
            ),
            710448 =>
            array(
                'code' => '',
                'area_name' => '清水区',
                'name' => '',
            ),
            710449 =>
            array(
                'code' => '',
                'area_name' => '大甲区',
                'name' => '',
            ),
            710450 =>
            array(
                'code' => '',
                'area_name' => '外埔区',
                'name' => '',
            ),
            710451 =>
            array(
                'code' => '',
                'area_name' => '大安区',
                'name' => '',
            ),
            710507 =>
            array(
                'code' => '',
                'area_name' => '金沙镇',
                'name' => '',
            ),
            710508 =>
            array(
                'code' => '',
                'area_name' => '金湖镇',
                'name' => '',
            ),
            710509 =>
            array(
                'code' => '',
                'area_name' => '金宁乡',
                'name' => '',
            ),
            710510 =>
            array(
                'code' => '',
                'area_name' => '金城镇',
                'name' => '',
            ),
            710511 =>
            array(
                'code' => '',
                'area_name' => '烈屿乡',
                'name' => '',
            ),
            710512 =>
            array(
                'code' => '',
                'area_name' => '乌坵乡',
                'name' => '',
            ),
            710614 =>
            array(
                'code' => '',
                'area_name' => '南投市',
                'name' => '',
            ),
            710615 =>
            array(
                'code' => '',
                'area_name' => '中寮乡',
                'name' => '',
            ),
            710616 =>
            array(
                'code' => '',
                'area_name' => '草屯镇',
                'name' => '',
            ),
            710617 =>
            array(
                'code' => '',
                'area_name' => '国姓乡',
                'name' => '',
            ),
            710618 =>
            array(
                'code' => '',
                'area_name' => '埔里镇',
                'name' => '',
            ),
            710619 =>
            array(
                'code' => '',
                'area_name' => '仁爱乡',
                'name' => '',
            ),
            710620 =>
            array(
                'code' => '',
                'area_name' => '名间乡',
                'name' => '',
            ),
            710621 =>
            array(
                'code' => '',
                'area_name' => '集集镇',
                'name' => '',
            ),
            710622 =>
            array(
                'code' => '',
                'area_name' => '水里乡',
                'name' => '',
            ),
            710623 =>
            array(
                'code' => '',
                'area_name' => '鱼池乡',
                'name' => '',
            ),
            710624 =>
            array(
                'code' => '',
                'area_name' => '信义乡',
                'name' => '',
            ),
            710625 =>
            array(
                'code' => '',
                'area_name' => '竹山镇',
                'name' => '',
            ),
            710626 =>
            array(
                'code' => '',
                'area_name' => '鹿谷乡',
                'name' => '',
            ),
            710701 =>
            array(
                'code' => '',
                'area_name' => '仁爱区',
                'name' => '',
            ),
            710702 =>
            array(
                'code' => '',
                'area_name' => '信义区',
                'name' => '',
            ),
            710703 =>
            array(
                'code' => '',
                'area_name' => '中正区',
                'name' => '',
            ),
            710704 =>
            array(
                'code' => '',
                'area_name' => '中山区',
                'name' => '',
            ),
            710705 =>
            array(
                'code' => '',
                'area_name' => '安乐区',
                'name' => '',
            ),
            710706 =>
            array(
                'code' => '',
                'area_name' => '暖暖区',
                'name' => '',
            ),
            710707 =>
            array(
                'code' => '',
                'area_name' => '七堵区',
                'name' => '',
            ),
            710801 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            710802 =>
            array(
                'code' => '',
                'area_name' => '北区',
                'name' => '',
            ),
            710803 =>
            array(
                'code' => '',
                'area_name' => '香山区',
                'name' => '',
            ),
            710901 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            710902 =>
            array(
                'code' => '',
                'area_name' => '西区',
                'name' => '',
            ),
            711130 =>
            array(
                'code' => '',
                'area_name' => '万里区',
                'name' => '',
            ),
            711131 =>
            array(
                'code' => '',
                'area_name' => '金山区',
                'name' => '',
            ),
            711132 =>
            array(
                'code' => '',
                'area_name' => '板桥区',
                'name' => '',
            ),
            711133 =>
            array(
                'code' => '',
                'area_name' => '汐止区',
                'name' => '',
            ),
            711134 =>
            array(
                'code' => '',
                'area_name' => '深坑区',
                'name' => '',
            ),
            711135 =>
            array(
                'code' => '',
                'area_name' => '石碇区',
                'name' => '',
            ),
            711136 =>
            array(
                'code' => '',
                'area_name' => '瑞芳区',
                'name' => '',
            ),
            711137 =>
            array(
                'code' => '',
                'area_name' => '平溪区',
                'name' => '',
            ),
            711138 =>
            array(
                'code' => '',
                'area_name' => '双溪区',
                'name' => '',
            ),
            711139 =>
            array(
                'code' => '',
                'area_name' => '贡寮区',
                'name' => '',
            ),
            711140 =>
            array(
                'code' => '',
                'area_name' => '新店区',
                'name' => '',
            ),
            711141 =>
            array(
                'code' => '',
                'area_name' => '坪林区',
                'name' => '',
            ),
            711142 =>
            array(
                'code' => '',
                'area_name' => '乌来区',
                'name' => '',
            ),
            711143 =>
            array(
                'code' => '',
                'area_name' => '永和区',
                'name' => '',
            ),
            711144 =>
            array(
                'code' => '',
                'area_name' => '中和区',
                'name' => '',
            ),
            711145 =>
            array(
                'code' => '',
                'area_name' => '土城区',
                'name' => '',
            ),
            711146 =>
            array(
                'code' => '',
                'area_name' => '三峡区',
                'name' => '',
            ),
            711147 =>
            array(
                'code' => '',
                'area_name' => '树林区',
                'name' => '',
            ),
            711148 =>
            array(
                'code' => '',
                'area_name' => '莺歌区',
                'name' => '',
            ),
            711149 =>
            array(
                'code' => '',
                'area_name' => '三重区',
                'name' => '',
            ),
            711150 =>
            array(
                'code' => '',
                'area_name' => '新庄区',
                'name' => '',
            ),
            711151 =>
            array(
                'code' => '',
                'area_name' => '泰山区',
                'name' => '',
            ),
            711152 =>
            array(
                'code' => '',
                'area_name' => '林口区',
                'name' => '',
            ),
            711153 =>
            array(
                'code' => '',
                'area_name' => '芦洲区',
                'name' => '',
            ),
            711154 =>
            array(
                'code' => '',
                'area_name' => '五股区',
                'name' => '',
            ),
            711155 =>
            array(
                'code' => '',
                'area_name' => '八里区',
                'name' => '',
            ),
            711156 =>
            array(
                'code' => '',
                'area_name' => '淡水区',
                'name' => '',
            ),
            711157 =>
            array(
                'code' => '',
                'area_name' => '三芝区',
                'name' => '',
            ),
            711158 =>
            array(
                'code' => '',
                'area_name' => '石门区',
                'name' => '',
            ),
            711214 =>
            array(
                'code' => '',
                'area_name' => '宜兰市',
                'name' => '',
            ),
            711215 =>
            array(
                'code' => '',
                'area_name' => '头城镇',
                'name' => '',
            ),
            711216 =>
            array(
                'code' => '',
                'area_name' => '礁溪乡',
                'name' => '',
            ),
            711217 =>
            array(
                'code' => '',
                'area_name' => '壮围乡',
                'name' => '',
            ),
            711218 =>
            array(
                'code' => '',
                'area_name' => '员山乡',
                'name' => '',
            ),
            711219 =>
            array(
                'code' => '',
                'area_name' => '罗东镇',
                'name' => '',
            ),
            711220 =>
            array(
                'code' => '',
                'area_name' => '三星乡',
                'name' => '',
            ),
            711221 =>
            array(
                'code' => '',
                'area_name' => '大同乡',
                'name' => '',
            ),
            711222 =>
            array(
                'code' => '',
                'area_name' => '五结乡',
                'name' => '',
            ),
            711223 =>
            array(
                'code' => '',
                'area_name' => '冬山乡',
                'name' => '',
            ),
            711224 =>
            array(
                'code' => '',
                'area_name' => '苏澳镇',
                'name' => '',
            ),
            711225 =>
            array(
                'code' => '',
                'area_name' => '南澳乡',
                'name' => '',
            ),
            711226 =>
            array(
                'code' => '',
                'area_name' => '钓鱼台',
                'name' => '',
            ),
            711314 =>
            array(
                'code' => '',
                'area_name' => '竹北市',
                'name' => '',
            ),
            711315 =>
            array(
                'code' => '',
                'area_name' => '湖口乡',
                'name' => '',
            ),
            711316 =>
            array(
                'code' => '',
                'area_name' => '新丰乡',
                'name' => '',
            ),
            711317 =>
            array(
                'code' => '',
                'area_name' => '新埔镇',
                'name' => '',
            ),
            711318 =>
            array(
                'code' => '',
                'area_name' => '关西镇',
                'name' => '',
            ),
            711319 =>
            array(
                'code' => '',
                'area_name' => '芎林乡',
                'name' => '',
            ),
            711320 =>
            array(
                'code' => '',
                'area_name' => '宝山乡',
                'name' => '',
            ),
            711321 =>
            array(
                'code' => '',
                'area_name' => '竹东镇',
                'name' => '',
            ),
            711322 =>
            array(
                'code' => '',
                'area_name' => '五峰乡',
                'name' => '',
            ),
            711323 =>
            array(
                'code' => '',
                'area_name' => '横山乡',
                'name' => '',
            ),
            711324 =>
            array(
                'code' => '',
                'area_name' => '尖石乡',
                'name' => '',
            ),
            711325 =>
            array(
                'code' => '',
                'area_name' => '北埔乡',
                'name' => '',
            ),
            711326 =>
            array(
                'code' => '',
                'area_name' => '峨眉乡',
                'name' => '',
            ),
            711414 =>
            array(
                'code' => '',
                'area_name' => '中坜市',
                'name' => '',
            ),
            711415 =>
            array(
                'code' => '',
                'area_name' => '平镇市',
                'name' => '',
            ),
            711416 =>
            array(
                'code' => '',
                'area_name' => '龙潭乡',
                'name' => '',
            ),
            711417 =>
            array(
                'code' => '',
                'area_name' => '杨梅市',
                'name' => '',
            ),
            711418 =>
            array(
                'code' => '',
                'area_name' => '新屋乡',
                'name' => '',
            ),
            711419 =>
            array(
                'code' => '',
                'area_name' => '观音乡',
                'name' => '',
            ),
            711420 =>
            array(
                'code' => '',
                'area_name' => '桃园市',
                'name' => '',
            ),
            711421 =>
            array(
                'code' => '',
                'area_name' => '龟山乡',
                'name' => '',
            ),
            711422 =>
            array(
                'code' => '',
                'area_name' => '八德市',
                'name' => '',
            ),
            711423 =>
            array(
                'code' => '',
                'area_name' => '大溪镇',
                'name' => '',
            ),
            711424 =>
            array(
                'code' => '',
                'area_name' => '复兴乡',
                'name' => '',
            ),
            711425 =>
            array(
                'code' => '',
                'area_name' => '大园乡',
                'name' => '',
            ),
            711426 =>
            array(
                'code' => '',
                'area_name' => '芦竹乡',
                'name' => '',
            ),
            711519 =>
            array(
                'code' => '',
                'area_name' => '竹南镇',
                'name' => '',
            ),
            711520 =>
            array(
                'code' => '',
                'area_name' => '头份镇',
                'name' => '',
            ),
            711521 =>
            array(
                'code' => '',
                'area_name' => '三湾乡',
                'name' => '',
            ),
            711522 =>
            array(
                'code' => '',
                'area_name' => '南庄乡',
                'name' => '',
            ),
            711523 =>
            array(
                'code' => '',
                'area_name' => '狮潭乡',
                'name' => '',
            ),
            711524 =>
            array(
                'code' => '',
                'area_name' => '后龙镇',
                'name' => '',
            ),
            711525 =>
            array(
                'code' => '',
                'area_name' => '通霄镇',
                'name' => '',
            ),
            711526 =>
            array(
                'code' => '',
                'area_name' => '苑里镇',
                'name' => '',
            ),
            711527 =>
            array(
                'code' => '',
                'area_name' => '苗栗市',
                'name' => '',
            ),
            711528 =>
            array(
                'code' => '',
                'area_name' => '造桥乡',
                'name' => '',
            ),
            711529 =>
            array(
                'code' => '',
                'area_name' => '头屋乡',
                'name' => '',
            ),
            711530 =>
            array(
                'code' => '',
                'area_name' => '公馆乡',
                'name' => '',
            ),
            711531 =>
            array(
                'code' => '',
                'area_name' => '大湖乡',
                'name' => '',
            ),
            711532 =>
            array(
                'code' => '',
                'area_name' => '泰安乡',
                'name' => '',
            ),
            711533 =>
            array(
                'code' => '',
                'area_name' => '铜锣乡',
                'name' => '',
            ),
            711534 =>
            array(
                'code' => '',
                'area_name' => '三义乡',
                'name' => '',
            ),
            711535 =>
            array(
                'code' => '',
                'area_name' => '西湖乡',
                'name' => '',
            ),
            711536 =>
            array(
                'code' => '',
                'area_name' => '卓兰镇',
                'name' => '',
            ),
            711727 =>
            array(
                'code' => '',
                'area_name' => '彰化市',
                'name' => '',
            ),
            711728 =>
            array(
                'code' => '',
                'area_name' => '芬园乡',
                'name' => '',
            ),
            711729 =>
            array(
                'code' => '',
                'area_name' => '花坛乡',
                'name' => '',
            ),
            711730 =>
            array(
                'code' => '',
                'area_name' => '秀水乡',
                'name' => '',
            ),
            711731 =>
            array(
                'code' => '',
                'area_name' => '鹿港镇',
                'name' => '',
            ),
            711732 =>
            array(
                'code' => '',
                'area_name' => '福兴乡',
                'name' => '',
            ),
            711733 =>
            array(
                'code' => '',
                'area_name' => '线西乡',
                'name' => '',
            ),
            711734 =>
            array(
                'code' => '',
                'area_name' => '和美镇',
                'name' => '',
            ),
            711735 =>
            array(
                'code' => '',
                'area_name' => '伸港乡',
                'name' => '',
            ),
            711736 =>
            array(
                'code' => '',
                'area_name' => '员林镇',
                'name' => '',
            ),
            711737 =>
            array(
                'code' => '',
                'area_name' => '社头乡',
                'name' => '',
            ),
            711738 =>
            array(
                'code' => '',
                'area_name' => '永靖乡',
                'name' => '',
            ),
            711739 =>
            array(
                'code' => '',
                'area_name' => '埔心乡',
                'name' => '',
            ),
            711740 =>
            array(
                'code' => '',
                'area_name' => '溪湖镇',
                'name' => '',
            ),
            711741 =>
            array(
                'code' => '',
                'area_name' => '大村乡',
                'name' => '',
            ),
            711742 =>
            array(
                'code' => '',
                'area_name' => '埔盐乡',
                'name' => '',
            ),
            711743 =>
            array(
                'code' => '',
                'area_name' => '田中镇',
                'name' => '',
            ),
            711744 =>
            array(
                'code' => '',
                'area_name' => '北斗镇',
                'name' => '',
            ),
            711745 =>
            array(
                'code' => '',
                'area_name' => '田尾乡',
                'name' => '',
            ),
            711746 =>
            array(
                'code' => '',
                'area_name' => '埤头乡',
                'name' => '',
            ),
            711747 =>
            array(
                'code' => '',
                'area_name' => '溪州乡',
                'name' => '',
            ),
            711748 =>
            array(
                'code' => '',
                'area_name' => '竹塘乡',
                'name' => '',
            ),
            711749 =>
            array(
                'code' => '',
                'area_name' => '二林镇',
                'name' => '',
            ),
            711750 =>
            array(
                'code' => '',
                'area_name' => '大城乡',
                'name' => '',
            ),
            711751 =>
            array(
                'code' => '',
                'area_name' => '芳苑乡',
                'name' => '',
            ),
            711752 =>
            array(
                'code' => '',
                'area_name' => '二水乡',
                'name' => '',
            ),
            711919 =>
            array(
                'code' => '',
                'area_name' => '番路乡',
                'name' => '',
            ),
            711920 =>
            array(
                'code' => '',
                'area_name' => '梅山乡',
                'name' => '',
            ),
            711921 =>
            array(
                'code' => '',
                'area_name' => '竹崎乡',
                'name' => '',
            ),
            711922 =>
            array(
                'code' => '',
                'area_name' => '阿里山乡',
                'name' => '',
            ),
            711923 =>
            array(
                'code' => '',
                'area_name' => '中埔乡',
                'name' => '',
            ),
            711924 =>
            array(
                'code' => '',
                'area_name' => '大埔乡',
                'name' => '',
            ),
            711925 =>
            array(
                'code' => '',
                'area_name' => '水上乡',
                'name' => '',
            ),
            711926 =>
            array(
                'code' => '',
                'area_name' => '鹿草乡',
                'name' => '',
            ),
            711927 =>
            array(
                'code' => '',
                'area_name' => '太保市',
                'name' => '',
            ),
            711928 =>
            array(
                'code' => '',
                'area_name' => '朴子市',
                'name' => '',
            ),
            711929 =>
            array(
                'code' => '',
                'area_name' => '东石乡',
                'name' => '',
            ),
            711930 =>
            array(
                'code' => '',
                'area_name' => '六脚乡',
                'name' => '',
            ),
            711931 =>
            array(
                'code' => '',
                'area_name' => '新港乡',
                'name' => '',
            ),
            711932 =>
            array(
                'code' => '',
                'area_name' => '民雄乡',
                'name' => '',
            ),
            711933 =>
            array(
                'code' => '',
                'area_name' => '大林镇',
                'name' => '',
            ),
            711934 =>
            array(
                'code' => '',
                'area_name' => '溪口乡',
                'name' => '',
            ),
            711935 =>
            array(
                'code' => '',
                'area_name' => '义竹乡',
                'name' => '',
            ),
            711936 =>
            array(
                'code' => '',
                'area_name' => '布袋镇',
                'name' => '',
            ),
            712121 =>
            array(
                'code' => '',
                'area_name' => '斗南镇',
                'name' => '',
            ),
            712122 =>
            array(
                'code' => '',
                'area_name' => '大埤乡',
                'name' => '',
            ),
            712123 =>
            array(
                'code' => '',
                'area_name' => '虎尾镇',
                'name' => '',
            ),
            712124 =>
            array(
                'code' => '',
                'area_name' => '土库镇',
                'name' => '',
            ),
            712125 =>
            array(
                'code' => '',
                'area_name' => '褒忠乡',
                'name' => '',
            ),
            712126 =>
            array(
                'code' => '',
                'area_name' => '东势乡',
                'name' => '',
            ),
            712127 =>
            array(
                'code' => '',
                'area_name' => '台西乡',
                'name' => '',
            ),
            712128 =>
            array(
                'code' => '',
                'area_name' => '仑背乡',
                'name' => '',
            ),
            712129 =>
            array(
                'code' => '',
                'area_name' => '麦寮乡',
                'name' => '',
            ),
            712130 =>
            array(
                'code' => '',
                'area_name' => '斗六市',
                'name' => '',
            ),
            712131 =>
            array(
                'code' => '',
                'area_name' => '林内乡',
                'name' => '',
            ),
            712132 =>
            array(
                'code' => '',
                'area_name' => '古坑乡',
                'name' => '',
            ),
            712133 =>
            array(
                'code' => '',
                'area_name' => '莿桐乡',
                'name' => '',
            ),
            712134 =>
            array(
                'code' => '',
                'area_name' => '西螺镇',
                'name' => '',
            ),
            712135 =>
            array(
                'code' => '',
                'area_name' => '二仑乡',
                'name' => '',
            ),
            712136 =>
            array(
                'code' => '',
                'area_name' => '北港镇',
                'name' => '',
            ),
            712137 =>
            array(
                'code' => '',
                'area_name' => '水林乡',
                'name' => '',
            ),
            712138 =>
            array(
                'code' => '',
                'area_name' => '口湖乡',
                'name' => '',
            ),
            712139 =>
            array(
                'code' => '',
                'area_name' => '四湖乡',
                'name' => '',
            ),
            712140 =>
            array(
                'code' => '',
                'area_name' => '元长乡',
                'name' => '',
            ),
            712434 =>
            array(
                'code' => '',
                'area_name' => '屏东市',
                'name' => '',
            ),
            712435 =>
            array(
                'code' => '',
                'area_name' => '三地门乡',
                'name' => '',
            ),
            712436 =>
            array(
                'code' => '',
                'area_name' => '雾台乡',
                'name' => '',
            ),
            712437 =>
            array(
                'code' => '',
                'area_name' => '玛家乡',
                'name' => '',
            ),
            712438 =>
            array(
                'code' => '',
                'area_name' => '九如乡',
                'name' => '',
            ),
            712439 =>
            array(
                'code' => '',
                'area_name' => '里港乡',
                'name' => '',
            ),
            712440 =>
            array(
                'code' => '',
                'area_name' => '高树乡',
                'name' => '',
            ),
            712441 =>
            array(
                'code' => '',
                'area_name' => '盐埔乡',
                'name' => '',
            ),
            712442 =>
            array(
                'code' => '',
                'area_name' => '长治乡',
                'name' => '',
            ),
            712443 =>
            array(
                'code' => '',
                'area_name' => '麟洛乡',
                'name' => '',
            ),
            712444 =>
            array(
                'code' => '',
                'area_name' => '竹田乡',
                'name' => '',
            ),
            712445 =>
            array(
                'code' => '',
                'area_name' => '内埔乡',
                'name' => '',
            ),
            712446 =>
            array(
                'code' => '',
                'area_name' => '万丹乡',
                'name' => '',
            ),
            712447 =>
            array(
                'code' => '',
                'area_name' => '潮州镇',
                'name' => '',
            ),
            712448 =>
            array(
                'code' => '',
                'area_name' => '泰武乡',
                'name' => '',
            ),
            712449 =>
            array(
                'code' => '',
                'area_name' => '来义乡',
                'name' => '',
            ),
            712450 =>
            array(
                'code' => '',
                'area_name' => '万峦乡',
                'name' => '',
            ),
            712451 =>
            array(
                'code' => '',
                'area_name' => '崁顶乡',
                'name' => '',
            ),
            712452 =>
            array(
                'code' => '',
                'area_name' => '新埤乡',
                'name' => '',
            ),
            712453 =>
            array(
                'code' => '',
                'area_name' => '南州乡',
                'name' => '',
            ),
            712454 =>
            array(
                'code' => '',
                'area_name' => '林边乡',
                'name' => '',
            ),
            712455 =>
            array(
                'code' => '',
                'area_name' => '东港镇',
                'name' => '',
            ),
            712456 =>
            array(
                'code' => '',
                'area_name' => '琉球乡',
                'name' => '',
            ),
            712457 =>
            array(
                'code' => '',
                'area_name' => '佳冬乡',
                'name' => '',
            ),
            712458 =>
            array(
                'code' => '',
                'area_name' => '新园乡',
                'name' => '',
            ),
            712459 =>
            array(
                'code' => '',
                'area_name' => '枋寮乡',
                'name' => '',
            ),
            712460 =>
            array(
                'code' => '',
                'area_name' => '枋山乡',
                'name' => '',
            ),
            712461 =>
            array(
                'code' => '',
                'area_name' => '春日乡',
                'name' => '',
            ),
            712462 =>
            array(
                'code' => '',
                'area_name' => '狮子乡',
                'name' => '',
            ),
            712463 =>
            array(
                'code' => '',
                'area_name' => '车城乡',
                'name' => '',
            ),
            712464 =>
            array(
                'code' => '',
                'area_name' => '牡丹乡',
                'name' => '',
            ),
            712465 =>
            array(
                'code' => '',
                'area_name' => '恒春镇',
                'name' => '',
            ),
            712466 =>
            array(
                'code' => '',
                'area_name' => '满州乡',
                'name' => '',
            ),
            712517 =>
            array(
                'code' => '',
                'area_name' => '台东市',
                'name' => '',
            ),
            712518 =>
            array(
                'code' => '',
                'area_name' => '绿岛乡',
                'name' => '',
            ),
            712519 =>
            array(
                'code' => '',
                'area_name' => '兰屿乡',
                'name' => '',
            ),
            712520 =>
            array(
                'code' => '',
                'area_name' => '延平乡',
                'name' => '',
            ),
            712521 =>
            array(
                'code' => '',
                'area_name' => '卑南乡',
                'name' => '',
            ),
            712522 =>
            array(
                'code' => '',
                'area_name' => '鹿野乡',
                'name' => '',
            ),
            712523 =>
            array(
                'code' => '',
                'area_name' => '关山镇',
                'name' => '',
            ),
            712524 =>
            array(
                'code' => '',
                'area_name' => '海端乡',
                'name' => '',
            ),
            712525 =>
            array(
                'code' => '',
                'area_name' => '池上乡',
                'name' => '',
            ),
            712526 =>
            array(
                'code' => '',
                'area_name' => '东河乡',
                'name' => '',
            ),
            712527 =>
            array(
                'code' => '',
                'area_name' => '成功镇',
                'name' => '',
            ),
            712528 =>
            array(
                'code' => '',
                'area_name' => '长滨乡',
                'name' => '',
            ),
            712529 =>
            array(
                'code' => '',
                'area_name' => '金峰乡',
                'name' => '',
            ),
            712530 =>
            array(
                'code' => '',
                'area_name' => '大武乡',
                'name' => '',
            ),
            712531 =>
            array(
                'code' => '',
                'area_name' => '达仁乡',
                'name' => '',
            ),
            712532 =>
            array(
                'code' => '',
                'area_name' => '太麻里乡',
                'name' => '',
            ),
            712615 =>
            array(
                'code' => '',
                'area_name' => '花莲市',
                'name' => '',
            ),
            712616 =>
            array(
                'code' => '',
                'area_name' => '新城乡',
                'name' => '',
            ),
            712617 =>
            array(
                'code' => '',
                'area_name' => '太鲁阁',
                'name' => '',
            ),
            712618 =>
            array(
                'code' => '',
                'area_name' => '秀林乡',
                'name' => '',
            ),
            712619 =>
            array(
                'code' => '',
                'area_name' => '吉安乡',
                'name' => '',
            ),
            712620 =>
            array(
                'code' => '',
                'area_name' => '寿丰乡',
                'name' => '',
            ),
            712621 =>
            array(
                'code' => '',
                'area_name' => '凤林镇',
                'name' => '',
            ),
            712622 =>
            array(
                'code' => '',
                'area_name' => '光复乡',
                'name' => '',
            ),
            712623 =>
            array(
                'code' => '',
                'area_name' => '丰滨乡',
                'name' => '',
            ),
            712624 =>
            array(
                'code' => '',
                'area_name' => '瑞穗乡',
                'name' => '',
            ),
            712625 =>
            array(
                'code' => '',
                'area_name' => '万荣乡',
                'name' => '',
            ),
            712626 =>
            array(
                'code' => '',
                'area_name' => '玉里镇',
                'name' => '',
            ),
            712627 =>
            array(
                'code' => '',
                'area_name' => '卓溪乡',
                'name' => '',
            ),
            712628 =>
            array(
                'code' => '',
                'area_name' => '富里乡',
                'name' => '',
            ),
            712707 =>
            array(
                'code' => '',
                'area_name' => '马公市',
                'name' => '',
            ),
            712708 =>
            array(
                'code' => '',
                'area_name' => '西屿乡',
                'name' => '',
            ),
            712709 =>
            array(
                'code' => '',
                'area_name' => '望安乡',
                'name' => '',
            ),
            712710 =>
            array(
                'code' => '',
                'area_name' => '七美乡',
                'name' => '',
            ),
            712711 =>
            array(
                'code' => '',
                'area_name' => '白沙乡',
                'name' => '',
            ),
            712712 =>
            array(
                'code' => '',
                'area_name' => '湖西乡',
                'name' => '',
            ),
            712805 =>
            array(
                'code' => '',
                'area_name' => '南竿乡',
                'name' => '',
            ),
            712806 =>
            array(
                'code' => '',
                'area_name' => '北竿乡',
                'name' => '',
            ),
            712807 =>
            array(
                'code' => '',
                'area_name' => '莒光乡',
                'name' => '',
            ),
            712808 =>
            array(
                'code' => '',
                'area_name' => '东引乡',
                'name' => '',
            ),
            810101 =>
            array(
                'code' => '',
                'area_name' => '中西区',
                'name' => '',
            ),
            810102 =>
            array(
                'code' => '',
                'area_name' => '湾仔',
                'name' => '',
            ),
            810103 =>
            array(
                'code' => '',
                'area_name' => '东区',
                'name' => '',
            ),
            810104 =>
            array(
                'code' => '',
                'area_name' => '南区',
                'name' => '',
            ),
            810201 =>
            array(
                'code' => '',
                'area_name' => '九龙城区',
                'name' => '',
            ),
            810202 =>
            array(
                'code' => '',
                'area_name' => '油尖旺区',
                'name' => '',
            ),
            810203 =>
            array(
                'code' => '',
                'area_name' => '深水埗区',
                'name' => '',
            ),
            810204 =>
            array(
                'code' => '',
                'area_name' => '黄大仙区',
                'name' => '',
            ),
            810205 =>
            array(
                'code' => '',
                'area_name' => '观塘区',
                'name' => '',
            ),
            810301 =>
            array(
                'code' => '',
                'area_name' => '北区',
                'name' => '',
            ),
            810302 =>
            array(
                'code' => '',
                'area_name' => '大埔区',
                'name' => '',
            ),
            810303 =>
            array(
                'code' => '',
                'area_name' => '沙田区',
                'name' => '',
            ),
            810304 =>
            array(
                'code' => '',
                'area_name' => '西贡区',
                'name' => '',
            ),
            810305 =>
            array(
                'code' => '',
                'area_name' => '元朗区',
                'name' => '',
            ),
            810306 =>
            array(
                'code' => '',
                'area_name' => '屯门区',
                'name' => '',
            ),
            810307 =>
            array(
                'code' => '',
                'area_name' => '荃湾区',
                'name' => '',
            ),
            810308 =>
            array(
                'code' => '',
                'area_name' => '葵青区',
                'name' => '',
            ),
            810309 =>
            array(
                'code' => '',
                'area_name' => '离岛区',
                'name' => '',
            ),
            460201100 =>
            array(
                'code' => '350201',
                'area_name' => '海棠湾镇',
                'name' => '海棠区',
            ),
            460201101 =>
            array(
                'code' => '350203',
                'area_name' => '吉阳镇',
                'name' => '吉阳区',
            ),
            460201104 =>
            array(
                'code' => '350204',
                'area_name' => '天涯镇',
                'name' => '天涯区',
            ),
            460201103 =>
            array(
                'code' => '350206',
                'area_name' => '崖城镇',
                'name' => '崖州区',
            ),
        );

        return $area;
    }

    public function getAreaCode($code) {
        $arr = self::getarea();
        if (isset($arr[$code]) && !empty($arr[$code]['code'])) {
            $city = $arr[$code];
        } else {
            $code = self::getCode($code);
            if (empty($code)) {
                return [];
            }
            $city = self::getAreaCode($code);
        }
        return $city;
    }

    private function getCode($code) {
        $arr = self::getarea();
        $n = 0;
        while (!isset($arr[$code]) || empty($arr[$code]['code'])) {
            $code = intval($code);
            $code--;
            $code = (string) $code;
            $n++;
            if ($n > 50) {
                $code = '';
                break;
            }
        }
        return $code;
    }

}

?>
