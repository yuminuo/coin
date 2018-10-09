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
     * Listens amqp-queue and runs new jobs.
     * It can be used as daemon process.
     */
    public function actionDeposit()
    {
        $this->coin->deposit();
    }
}
