Provide all kinds of encryption currency exchange related business logic driver.
================================================================================
Provide all kinds of encryption currency exchange related business logic driver.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist adamyxt/coin "dev-master"
```

or add

```
"adamyxt/coin": "dev-master"
```

to the require section of your `composer.json` file.


需要的扩展
------------
```
composer require enqueue/amqp-lib
composer require achse/geth-jsonrpc-php-client
composer require danhunsaker/bcmath
```

生成相关数据库表
------------
```
yii migrate --migrationPath=@adamyxt/coin/src/migrations
```
Usage
-----
```php
'bootstrap' => [
        'coin', 'queue', // The component registers its own console commands
    ],
    'components' => [
        'coin' => [
            'class' => adamyxt\coin\eth\Coin::class,
            'url' => '192.168.0.200',
            'port' => 8545
        ],
        'queue' => [
            'class' => \yii\queue\amqp_interop\Queue::class,
            'host' => '192.168.99.100',  //MQ地址
            'port' => 5672,          //端口号
            'user' => 'guest',       //用户名
            'password' => 'guest',   //密码
            'queueName' => 'coin',    //队列名称
            'driver' => yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_LIB, //驱动方式
            'as log' => \yii\queue\LogBehavior::class, //日志
            'exchangeName' => 'coin'
        ],
    ]
```

生成ETH私钥方法
-----
```
php yii coin/address 10001 "root:123456@tcp(172.17.0.4:3306)/awesome?charset=utf8"
```

开启充值提现功能
-----
```
做一个定时任务执行下面命令
php yii coin/deposit
```