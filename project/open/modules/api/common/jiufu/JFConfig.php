<?php
namespace app\modules\api\common\jiufu;

/**
 * 系统静态配置类
 */
class JFConfig {
	public static $env;// 系统配置,仅初始化一次
	private static $_instance;
	private $config;
	/**
	 * 单例模式
	 */
	private function __construct($env) {
		$this->config = $this->initConfig($env);
	}
	/**
	 * 读取配置项
	 * @param  str $env 
	 * @return []
	 */
	private function initConfig($env) {
		$env = $env == 'prod' ? 'prod' : 'dev';
		$configPath = __DIR__ . "/config/config.{$env}.php";
		$config = include $configPath;
		return $config;
	}
	/**
	 * 静态实例 化
	 */
	public static function model() {
		if (static::$_instance) {
			return static::$_instance;
		}
		if (!static::$env) {
			throw new \Exception("JFConfig::$env必须初始化", 6000);
		}
		return static::$_instance = new self(static::$env);
	}
	/**
	 * 获取配置项
	 * @param  str $param
	 * @return [] | str
	 */
	public function getConfig($param = null) {
		if(!$param){
			return $this->config;
		}
		$value = isset($this->config[$param]) ?  $this->config[$param] : null;
		return $value;
	}
}