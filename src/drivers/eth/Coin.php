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
use adamyxt\coin\EthTransaction;
use adamyxt\coin\jobs\BlockJob;
use adamyxt\coin\UserWithdraw;
use Danhunsaker\BC;
use kornrunner\Ethereum\Transaction;
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
    public function push(UserWithdraw $userWithdraw): string
    {
        $nonce = $this->_client->callMethod('eth_getTransactionCount', [Yii::$app->params['eth_system_address'], "pending"]);
        if (!property_exists($nonce,'result')) {
            Yii::error($nonce->error->message,"eth");
            return false;
        }
        $local_nonce = UserWithdraw::find()->select(['nonce'])->orderBy(['nonce'=>SORT_DESC])->asArray()->one();
        if (empty($local_nonce)) {
            $local_nonce_num = $local_nonce['nonce'];
            if (base_convert($nonce->result,16,10) - 1 != $local_nonce_num) {
                Yii::error('NONCE 值有问题,请排查',"eth");
                return false;
            }
        }

        $price = $this->_client->callMethod('eth_gasPrice', []);
        if (!property_exists($price,'result')) {
            Yii::error($price->error->message,"eth");
            return false;
        }

        $userWithdraw->nonce = base_convert($nonce->result,16,10);
        if (!$userWithdraw->save()) {
            Yii::error('save nonce failed',"eth");
            return false;
        }

        $amount = '0x'.base_convert(BC::mul($userWithdraw->amount,'1000000000000000000', 0),10,16);
        $prepare = [];
        $prepare['from'] = Yii::$app->params['eth_system_address'];
        $prepare['to'] = $userWithdraw->address;
        $prepare['value'] = $amount;
        $gas = $this->_client->callMethod('eth_estimateGas', [$prepare]);
        if (!property_exists($gas,'result')) {
            Yii::error($gas->error->message,"eth");
            return false;
        }
        $o_transaction = new Transaction(substr($nonce->result,2),substr($price->result,2),substr($gas->result,2),substr($userWithdraw->address,2),substr($amount,2));
        $privateKey = substr(Yii::$app->params['eth_system_key'],2);
        $signed_tx = '0x'.$o_transaction->getRaw($privateKey,Yii::$app->params['eth_chain_id']);
        $send = $this->_client->callMethod('eth_sendRawTransaction', [$signed_tx]);
        if (!property_exists($send,'result')) {
            Yii::error($send->error->message,"eth");
            return false;
        }
        $eth_transaction = new EthTransaction();
        $eth_transaction->u_id = $userWithdraw->id;
        $eth_transaction->hash = $send->result;
        $eth_transaction->type = EthTransaction::TYPE_WITHDRAW;
        if (!$eth_transaction->save()) {
            //此次如果失败交易订单已经不能撤销了 所以只能存入mongodb以备处理
            // to do some
            Yii::error('存入交易单失败',"eth");
            Yii::$app->mongodb->getCollection('eth_send_failed_transactions')->insert([
                'u_id' => $userWithdraw->id,
                'hash' => $send->result,
                'type' => EthTransaction::TYPE_WITHDRAW
            ]);

        }
        return true;
    }
}