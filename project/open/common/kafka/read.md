**kafka说明：**
php扩展
	PHP版本要求高于7.0
	kafka扩展下载地址（内有测试代码）：http://pecl.php.net/package/rdkafka
	安装参考代码：https://blog.csdn.net/u013713010/article/details/68947679
	安装步骤：
		1.进入kafka扩展
		2./opt/phpize
		3./configure --enable-kafka --with-php-config=/usr/local/php/bin/php-config
		4.修改php配置文件：extension="kafka.so"			
测试环境：
	生产者：php7 /data/wwwroot/open/yii kafka/producer
	消费者：php7 /data/wwwroot/open/yii kafka/consumer start 