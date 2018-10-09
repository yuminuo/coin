<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 14:54
 */

namespace adamyxt\coin\cli;

use adamyxt\coin\Coin as BaseCoin;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\console\Application as ConsoleApp;

abstract class Coin extends BaseCoin implements BootstrapInterface
{

    /**
     * @var string command class name
     */
    public $commandClass = Command::class;
    /**
     * @var array of additional options of command
     */
    public $commandOptions = [];


    /**
     * @return string command id
     * @throws
     */
    protected function getCommandId()
    {
        foreach (Yii::$app->getComponents(false) as $id => $component) {
            if ($component === $this) {
                return Inflector::camel2id($id);
            }
        }
        throw new InvalidConfigException('Queue must be an application component.');
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApp) {
            $app->controllerMap[$this->getCommandId()] = [
                    'class' => $this->commandClass,
                    'coin' => $this,
                ] + $this->commandOptions;
        }
    }

}