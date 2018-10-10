<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 14:46
 */

namespace adamyxt\coin\eth;

use Achse\GethJsonRpcPhpClient\JsonRpc\Client;
use Achse\GethJsonRpcPhpClient\JsonRpc\GuzzleClient;
use Achse\GethJsonRpcPhpClient\JsonRpc\GuzzleClientFactory;
use adamyxt\coin\cli\Coin as BaseCoin;
use adamyxt\coin\jobs\BlockJob;
use adamyxt\coin\UserWithdraw;
use Yii;

class Coin extends BaseCoin
{
    /**
     * @var
     */
    public $url;
    /**
     * @var
     */
    public $port;
    /**
     * The property contains a command class which used in cli.
     *
     * @var string command class name
     */
    public $commandClass = Command::class;

    /**
     * 创建一个钱包客户端
     * @return mixed
     */
    public function open(): void
    {
        $httpClient = new GuzzleClient(new GuzzleClientFactory(), $this->url, $this->port);
        $this->_client = new Client($httpClient);
    }

    /**
     * @return int
     */
    public function block(): string
    {
        return Yii::$app->queue->push(new BlockJob([
            'client' => $this->_client
        ]));
    }

    /**
     * @param UserWithdraw $user_withdraw
     * @return int
     */
    public function push(UserWithdraw $user_withdraw): string
    {
        // TODO: Implement push() method.
    }
}