<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace adamyxt\coin\eth;

use adamyxt\coin\cli\Command as CliCommand;

/**
 * Manages application amqp-queue.
 *
 * @author Maksym Kotliar <kotlyar.maksim@gmail.com>
 * @since 2.0.2
 */
class Command extends CliCommand
{
    /**
     * @var \adamyxt\coin\Coin
     */
    public $coin;


    /**
     * @inheritdoc
     */
    protected function isWorkerAction($actionID)
    {
        return $actionID === 'deposit';
    }

    /**
     * It can be used as daemon process.
     */
    public function actionDeposit()
    {
        $this->coin->deposit();
    }

    /**
     * 创建地址与密钥
     * @param int $num
     * @param string $url
     */
    public function actionAddress(int $num, string $url)
    {
        if ($num < 10000) {
            print_r('生成的key数量必须大于10000' . PHP_EOL);
            return;
        }
        $generator_path = dirname(__FILE__) . '/ethkey -n=' . $num . ' -u="' . $url . '"';
        exec($generator_path, $info);
        var_dump($info);
    }
}
