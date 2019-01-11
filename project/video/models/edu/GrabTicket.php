<?php

namespace app\models\edu;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "xhh_grab_ticket".
 *
 * @property integer $id
 * @property string $ducredit_appid
 * @property string $ducredit_ticket
 * @property string $ducredit_token
 * @property string $real_name
 * @property string $sex
 * @property string $birth_date
 * @property string $nation
 * @property string $id_card
 * @property string $school_name
 * @property string $level
 * @property string $specialty
 * @property string $length_of_schooling
 * @property string $edu_type
 * @property string $edu_form
 * @property string $branch_school
 * @property string $department
 * @property string $class_name
 * @property string $student_id
 * @property string $enrollment_time
 * @property string $leave_school_time
 * @property string $status
 * @property string $input_img
 * @property string $output_img
 * @property string $log_id
 * @property string $modify_time
 * @property string $create_time
 */
class GrabTicket extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_grab_ticket';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ducredit_appid', 'ducredit_ticket', 'ducredit_token', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['ducredit_appid', 'open_id', 'task_status', 'task_info', 'ducredit_ticket', 'ducredit_token', 'real_name', 'sex', 'birth_date', 'nation', 'id_card', 'school_name', 'level', 'specialty', 'length_of_schooling', 'edu_type', 'edu_form', 'branch_school', 'department', 'class_name', 'enrollment_time', 'leave_school_time', 'status', 'input_img', 'output_img', 'log_id'], 'string', 'max' => 50],
            [['student_id'], 'string', 'max' => 10],
            [['errmsg', 'app_info'], 'string', 'max' => 255],
            [['input_img', 'output_img'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ducredit_appid' => 'Ducredit Appid',
            'ducredit_ticket' => 'Ducredit Ticket',
            'ducredit_token' => 'Ducredit Token',
            'real_name' => 'Real Name',
            'sex' => 'Sex',
            'birth_date' => 'Birth Date',
            'nation' => 'Nation',
            'id_card' => 'Id Card',
            'school_name' => 'School Name',
            'level' => 'Level',
            'specialty' => 'Specialty',
            'length_of_schooling' => 'Length Of Schooling',
            'edu_type' => 'Edu Type',
            'edu_form' => 'Edu Form',
            'branch_school' => 'Branch School',
            'department' => 'Department',
            'class_name' => 'Class Name',
            'student_id' => 'Student ID',
            'enrollment_time' => 'Enrollment Time',
            'leave_school_time' => 'Leave School Time',
            'status' => 'Status',
            'input_img' => 'Input Img',
            'output_img' => 'Output Img',
            'log_id' => 'Log ID',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
            'errmsg'    => 'Errmsg',
            'app_info' => 'App Info',
            'task_info' => 'Task Info',
            'open_id' => 'Open Id',
            'task_status' => 'Task Status',

        ];
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $save_data = [
            'ducredit_appid'				=> ArrayHelper::getValue($data_set, 'ducredit_appid', ''), //Ducredit appid',
            'ducredit_ticket'				=> ArrayHelper::getValue($data_set, 'ducredit_ticket', ''), //TICKET',
            'ducredit_token'				=> ArrayHelper::getValue($data_set, 'ducredit_token', ''), //访问TOKEN，从3获取，一个TOKEN只能使用一次',
            'real_name'						=> ArrayHelper::getValue($data_set, 'real_name', ''), //COMMENT '姓名',
            'sex'							=> ArrayHelper::getValue($data_set, 'sex', ''), //性别',
            'birth_date'					=> ArrayHelper::getValue($data_set, 'birth_date', ''), //出生日期',
            'nation'						=> ArrayHelper::getValue($data_set, 'nation', ''), //民族',
            'id_card'						=> ArrayHelper::getValue($data_set, 'id_card', ''), //证件号码',
            'school_name'					=> ArrayHelper::getValue($data_set, 'school_name', ''), //学校名称',
            'level'							=> ArrayHelper::getValue($data_set, 'level', ''), //层次',
            'specialty'						=> ArrayHelper::getValue($data_set, 'specialty', ''), //专业',
            'length_of_schooling'			=> ArrayHelper::getValue($data_set, 'length_of_schooling', ''), //学制',
            'edu_type'						=> ArrayHelper::getValue($data_set, 'edu_type', ''), //学历类别',
            'edu_form'						=> ArrayHelper::getValue($data_set, 'edu_form', ''), //学习形式',
            'branch_school'					=> ArrayHelper::getValue($data_set, 'branch_school', ''), //分院',
            'department'					=> ArrayHelper::getValue($data_set, 'department', ''), //系（所、函授站）',
            'class_name'					=> ArrayHelper::getValue($data_set, 'class_name', ''), //班级',
            'student_id'					=> ArrayHelper::getValue($data_set, 'student_id', ''), //学号',
            'enrollment_time'				=> ArrayHelper::getValue($data_set, 'enrollment_time', ''), //入学日期',
            'leave_school_time'				=> ArrayHelper::getValue($data_set, 'leave_school_time', ''), //离校日期',
            'status'						=> ArrayHelper::getValue($data_set, 'status', ''), //学籍状态',
            'input_img'						=> ArrayHelper::getValue($data_set, 'input_img', ''), //录取照片（base64编码的图片）',
            'output_img'					=> ArrayHelper::getValue($data_set, 'output_img', ''), //学历照片（base64编码的图片）',
            'log_id'						=> ArrayHelper::getValue($data_set, 'log_id', ''), //,
            //'modify_time'					=> '', //更新时间',
            'create_time'					=> date("Y-m-d H:i:s"), //创建时间',
            'errmsg'                        => ArrayHelper::getValue($data_set, 'errmsg', ''),
            'app_info'                      => ArrayHelper::getValue($data_set, 'app_info', ''),
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog("edu/GrabTicket", '保存数据出错提示', json_encode($errors));
        }
        return $this->save();
    }

    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        foreach($data_set as $k => $v){
            $this->$k = $v;
        }
        $this->modify_time = date("Y-m-d H:i:s");
        return $this->save();
    }
}