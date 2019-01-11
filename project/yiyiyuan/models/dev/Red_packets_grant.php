<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "access_token".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Red_packets_grant extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_red_packets_grant';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
    
    /**
     * 修改红包的总额度和已发放额度
     */
    public function setRedPacketsAmount($grant_id, $version, $amount){
    	$now_time = date('Y-m-d H:i:s');
    	$red_packet = Red_packets_grant::find()->where(['grant_id'=>$grant_id,'version'=>$version])->one();
  		if(intval($red_packet->current_amount) >= 100){
  			return 'complete';
  		}else{
  			if((intval($red_packet->current_amount) + $amount) >= 100){
  				$red_packet->status = 'FINISHED';
  			}
	    	$red_packet->amount -= $amount;
	    	$red_packet->current_amount += $amount;
	    	$red_packet->last_modify_time = $now_time;
	    	$red_packet->version += 1;
	    	
	    	if($red_packet->save()){
	    		return 'success';
	    	}else{
	    		return 'error';
	    	}
  		}
    }
    
    /**
     * 发放红包
     * 
     */
    public function setFiveRedPacket($grant_id, $version, $amount){
    	
    	$transaction = Yii::$app->db->beginTransaction();
    	$ret_repacket_five = $this->setRedPacketsAmount($grant_id, $version, $amount);
    	
    	if($ret_repacket_five == 'complete'){
    		return 'complete';
    	}else{
	    	if($ret_repacket_five == 'success'){
	    		//添加一条发送记录
	    		$conditon = array(
	    				'grant_id' => $grant_id,
	    				'amount' => $amount,
	    		);
	    		$red_packets_list = new Red_packets_list();
	    		$ret_list = $red_packets_list->addRedPacketsList($conditon);
	    		if($ret_list){
	    			$transaction->commit();
	    			return $amount;
	    		}else{
	    			$transaction->rollBack();
	    			return 'error';
	    		}
	    	}else{
	    		$transaction->rollBack();
	    		return 'error';
	    	}
    	}
    }
    
    /**
     * 获取红包的额度
     */
	public function getRedPacketsAmount($grant_id){
		$red_packet = Red_packets_grant::find()->select(array('amount','current_amount','status','version'))->where(['grant_id'=>$grant_id])->one();
		if(!empty($red_packet)){
			if($red_packet->status == 'NORMAL'){
				$rand_code = rand(1,10);
				//判断红包的金额
				if(intval($red_packet->current_amount) <= 80){
					if($rand_code == 5){
						//查询5元红包已经发放几个
						$red_packet_five = Red_packets_list::find()->where(['grant_id'=>$grant_id,'amount'=>'5.0000'])->count();
						if($red_packet_five < 2){
							//发放一个5元的红包
							$ret_five_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, $rand_code);
							return $ret_five_redpacket;
						}else{
							//发放一个2元的红包
							$ret_two_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, 2);
							return $ret_two_redpacket;
						}
					}else if($rand_code == 10){
						//查询10元红包发放个数
						$red_packet_ten = Red_packets_list::find()->where(['grant_id'=>$grant_id,'amount'=>'10.0000'])->count();
						if($red_packet_ten == 0){
							//发放10元红包
							$ret_ten_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, $rand_code);
							return $ret_ten_redpacket;
						}else{
							//发放2元红包
							$ret_two_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, 2);
							return $ret_two_redpacket;
						}
					}else{
						//发放2元红包
						$ret_two_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, 2);
						return $ret_two_redpacket;
					} 
				}else{
					 //判断有没有发10元红包
					$red_packet_ten = Red_packets_list::find()->where(['grant_id'=>$grant_id,'amount'=>'10.0000'])->count();
					if($red_packet_ten == 0){
						//发放10元红包
						$ret_ten_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, $rand_code);
						return $ret_ten_redpacket;
					}else{
						//判断有没有发5元红包
						$red_packet_five = Red_packets_list::find()->where(['grant_id'=>$grant_id,'amount'=>'5.0000'])->count();
						if($red_packet_five < 2){
							//发放5元红包
							$ret_five_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, $rand_code);
							return $ret_five_redpacket;
						}else{
							//发放2元红包
							$ret_two_redpacket = $this->setFiveRedPacket($grant_id, $red_packet->version, 2);
							return $ret_two_redpacket;
						}
					}
				}
			}else{
				return 'complete';
			}
		}else{
			return 'noredpackets';
		}
	}
    
    /**
     * 发放红包
     */
    public function sendPacket($user,$amount,$current_amount,$status){
        $time = date('Y-m-d H:i:s');
        $redPacketModel = new Red_packets_grant();
        $redPacketModel->user_id = $user->user_id;
        $redPacketModel->amount = $amount;
        $redPacketModel->current_amount = $current_amount;
        $redPacketModel->status = $status;
        $redPacketModel->create_time = $time;
        $redPacketModel->last_modify_time = $time;
        $redPacketModel->version = 1;
        $result = $redPacketModel->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }
}
