<?php

namespace app\models\jiufu;

/**
 * 继承自中信通知
 */
class JFClientNotify extends \app\models\remit\ClientNotify {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jf_client_notify';
	}
}
