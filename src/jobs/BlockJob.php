<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 16:42
 */

namespace adamyxt\coin\jobs;

use adamyxt\coin\Address;
use adamyxt\coin\AddressNum;
use adamyxt\coin\EthBlockInfo;
use adamyxt\coin\EthTransaction;
use adamyxt\coin\UserWithdraw;
use adamyxt\helper\mysql\MysqlHelper;
use Danhunsaker\BC;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BlockJob extends BaseObject implements JobInterface
{

    public $client;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws Exception
     */
    public function execute($queue)
    {
        $block_num = $this->client->callMethod('eth_blockNumber', []);
        if (property_exists($block_num, 'result')) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $local_num = EthBlockInfo::find()->orderBy(['number' => SORT_DESC])->one();
                if (empty($local_num)) {
                    $analysis_result = $this->analysis($block_num->result);
                    if (!$analysis_result['status']) {
                        throw new Exception($analysis_result['msg']);
                    }
                } else {
                    $block_num_ten = base_convert($block_num->result, 16, 10);
                    $local_num_ten = $initial_num = $local_num->number;
                    //递归上账以及解析区块信息
                    while ($local_num_ten < $block_num_ten) {
                        $local_num_ten++;
                        $analysis_result = $this->analysis('0x' . base_convert($local_num_ten, 10, 16), $local_num);
                        if (!$analysis_result['status']) {
                            throw new Exception($analysis_result['msg']);
                        }
                        $local_num = $analysis_result['localBlockInfo'];
                    }
                    //一次性上账，不每次增加一个高度就上账一次，避免性能瓶颈
                    if ($initial_num < $block_num_ten) {
                        if (!$this->checks($block_num_ten)) {
                            throw  new Exception('checks failed');
                        }
                    }
                }
                $transaction->commit();
                Yii::info('Successful', 'eth');
                exit;
            } catch (Exception $e) {
                $transaction->rollBack();
                if ($e->getMessage() == 'Block isolation') {
                    Yii::info('节点孤立,回调解决孤立方法', 'eth');
                    $fix_result = $this->fixBlockFork();
                    echo $fix_result['msg'];
                    if ($fix_result['status']) {
                        Yii::info($fix_result['msg'], 'eth');
                    } else {
                        Yii::error($fix_result['msg'], 'eth');
                    }
                    exit;
                }
                Yii::error($e->getMessage(), 'eth');
                exit;
            }
        } else {
            Yii::error($block_num->error->message, 'eth');
            exit;
        }
    }

    /**
     * 解决孤立节点造成的数据错误
     * @return array
     * @throws Exception
     */
    private function fixBlockFork(): array
    {
        $local_num = EthBlockInfo::find()->orderBy(['number' => SORT_DESC])->one();
        $local_top_num = EthBlockInfo::find()->orderBy(['number' => SORT_ASC])->one();
        do {
            $fork_point = $local_num->number;
            $block_info = $this->client->callMethod('eth_getBlockByNumber', ['0x' . base_convert($local_num->number, 10, 16), true]);
            if (!property_exists($block_info, 'result')) {
                return ['status' => false, 'msg' => 'connect eth error'];
            }
            $local_parent_number = $local_num->number--;
            $local_num = EthBlockInfo::find()->where(['number' => $local_parent_number])->one();

        } while ($local_num->hash != $block_info->result->parentHash && $local_num->number <= $local_top_num->number);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!EthBlockInfo::deleteAll('number >= ' . $fork_point)) {
                throw new Exception('clean eth block info table failed');
            }
            //clean eth transactions table
            EthTransaction::deleteAll('blockNumber >= ' . $fork_point . ' and type = ' . EthTransaction::TYPE_DEPOSIT);
            $transaction->commit();
            return ['status' => true, 'msg' => 'fix block isolation Successful'];
        } catch (Exception $e) {
            $transaction->rollBack();
            return ['status' => false, 'msg' => 'fix block isolation failed'];
        }

    }

    /**
     * 上账
     * @param int $currentHigh
     * @return bool
     * @throws Exception
     */
    private function checks(int $currentHigh): bool
    {
        $confirmed = [];
        $clean_et_ids = [];
        foreach (EthTransaction::find()->asArray()->batch(1000) as $eth_transactions) {
            $deposit = [];
            $withdraw = [];
            foreach ($eth_transactions as $v) {
                if ($currentHigh - $v['blockNumber'] + 1 >= 8) {
                    $block_transaction = $this->client->callMethod('eth_getTransactionByBlockHashAndIndex', [$v['blockHash'], $v['transactionIndex']]);
                    if (property_exists($block_transaction, 'result')) {
                        if ($block_transaction->result == null) {
                            $v['status'] = 2;//节点孤立造成的错误的订单交易状态
                            $v['created_at'] = time();
                            $v['updated_at'] = time();
                            $clean_et_ids[] = $v['et_id'];
                            unset($v['et_id']);
                            $confirmed[] = $v;
                        } else {
                            $v['status'] = 1;//充值提现成功状态
                            $v['created_at'] = time();
                            $v['updated_at'] = time();
                            $clean_et_ids[] = $v['et_id'];
                            unset($v['et_id']);
                            $confirmed[] = $v;
                            if ($v['type'] == 1) {
                                if (isset($deposit[$v['to_a']])) {
                                    $deposit[$v['to_a']] = BC::add(BC::div(base_convert($v['value'], 16, 10), '1000000000000000000', 8), $deposit[$v['to_a']], 8);
                                } else {
                                    $deposit[$v['to_a']] = BC::div(base_convert($v['value'], 16, 10), '1000000000000000000', 8);
                                }
                            } elseif ($v['type'] == 2) {
                                $withdraw[] = $v['from_a'];
                            }
                        }
                    } else {
                        return false;
                    }
                }
            }
            if (!empty($deposit)) {
                $sql1 = 'insert into ' . AddressNum::tableName() . ' (address, type, num, ice_num, status, tags, created_at, updated_at) values ';
                $sql2 = 'on duplicate key update num = num + values(num), updated_at = values(updated_at)';
                $values = '';
                foreach ($deposit as $k => $v) {
                    $values .= '(' . $k . ', ' . AddressNum::TYPE_ETH . ', "' . $v . '", "0", "1", "' . md5($k . AddressNum::TYPE_ETH) . '", "' . time() . '", "' . time() . '"),';
                }
                $query = $sql1 . substr($values, 0, -1) . $sql2 . ';';
                Yii::$app->db->createCommand($query)->execute();
            }

            if (!empty($withdraw)) {
                $user_withdraw = UserWithdraw::find()->select(['id', 'address', 'amount', 'fees'])->where(['address' => $withdraw, 'account_type' => UserWithdraw::TYPE_ETH, 'status' => 1])->asArray()->all();
                if (!empty($user_withdraw)) {
                    $withdraw_ice = [];
                    foreach ($user_withdraw as $v) {
                        if (isset($withdraw_ice[$v['address']])) {
                            $withdraw_ice[$v['address']]['ice_num']['value'] = BC::add(BC::add($v['amount'], $v['fees'], 8), $withdraw_ice[$v['address']]['ice_num'], 8);
                        } else {
                            $withdraw_ice[$v['address']] = [
                                'ice_num' => [
                                    'value' => BC::add($v['amount'], $v['fees'], 8),
                                    'type' => MysqlHelper::UPDATE_MINUS
                                ]
                            ];
                        }
                    }
                    //修改用户提现订单状态
                    if (!UserWithdraw::updateAll(['status' => 2], ['address' => ArrayHelper::getColumn($user_withdraw, 'address', false)])) {
                        return false;
                    }
                    //扣除用户冻结数量
                    if (!MysqlHelper::batchUpdate('address_num', 'address', $withdraw_ice)) {
                        return false;
                    }
                }
            }

        }
        if (!empty($confirmed)) {
            if (!EthTransaction::deleteAll(['et_id' => $clean_et_ids])) {
                return false;
            }
            $res = Yii::$app->db->createCommand()->batchInsert(EthTransaction::tableName(), [
                'blockHash', 'blockNumber', 'from_a', 'gas', 'gasPrice', 'hash', 'nonce', 'to_a', 'transactionIndex', 'value', 'type', 'status', 'created_at', 'updated_at'
            ], $confirmed)->execute();
            if (!$res) {
                return false;
            }

        }
        return true;
    }

    /**
     * 解析区块并把和钱包相关的交易存入本地
     * @param string $block_num
     * @return array
     * @throws Exception
     */
    private function analysis(string $blockNum, EthBlockInfo $ethBlockInfo = null): array
    {
        if (base_convert($blockNum, 16, 10) == 0) {
            return ['status' => false, 'msg' => 'Node synchronization is not completed'];
        }
        $block_info = $this->client->callMethod('eth_getBlockByNumber', [$blockNum, true]);
        if (property_exists($block_info, 'result')) {
            $block_info_result = $block_info->result;
            //每当新增加区块就验证是否孤立
            if ($ethBlockInfo != null) {
                if ($ethBlockInfo->hash != $block_info_result->parentHash) {
                    return ['status' => false, 'msg' => 'Block isolation'];
                }
            }

            $eth_block_info = new EthBlockInfo();
            $eth_block_info->hash = $block_info_result->hash;
            $eth_block_info->parentHash = $block_info_result->parentHash;
            $eth_block_info->number = base_convert($block_info_result->number, 16, 10);
            $eth_block_info->timestamp = base_convert($block_info_result->timestamp, 16, 10);
            if (!$eth_block_info->save()) {
                return ['status' => false, 'msg' => 'save block info failed'];
            }
            if (!empty($block_info_result->transactions)) {
                $to_addresses = [];
                $out_hash = [];
                $transactions = [];
                $out_transactions = [];
                foreach ($block_info_result->transactions as $v) {
                    $to_addresses[] = $v->to;
                    $out_hash[] = $v->hash;
                }
                //查找充值到平台的交易
                $local_addresses = Address::find()->select(['address'])->where(['address' => $to_addresses, 'type' => Address::TYPE_ETH])->indexBy('address')->asArray()->all();
                if (!empty($local_addresses)) {
                    $local_addresses_array = ArrayHelper::getColumn($local_addresses, 'address', false);
                    foreach ($block_info_result->transactions as $v) {
                        if (in_array($v->to, $local_addresses_array)) {
                            $transactions[$v->hash] = [
                                'blockHash' => $v->blockHash,
                                'blockNumber' => base_convert($v->blockNumber, 16, 10),
                                'from_a' => $v->from,
                                'gas' => $v->gas,
                                'gasPrice' => $v->gasPrice,
                                'hash' => $v->hash,
                                'nonce' => $v->nonce,
                                'to_a' => $v->to,
                                'transactionIndex' => $v->transactionIndex,
                                'value' => $v->value,
                                'type' => EthTransaction::TYPE_DEPOSIT,
                                'created_at' => time(),
                                'updated_at' => time()
                            ];
                        }
                    }
                }
                //查找平台提现的交易
                $withdraw_tx = EthTransaction::find()->select(['hash'])->where(['hash' => $out_hash, 'type' => EthTransaction::TYPE_WITHDRAW])->indexBy('hash')->asArray()->all();
                if (!empty($withdraw_tx)) {
                    $withdraw_hash_array = ArrayHelper::getColumn($withdraw_tx, 'hash', false);
                    foreach ($block_info_result->transactions as $v) {
                        //通过提现地址以及NONCE值来关联提现订单和链上交易
                        if (in_array($v->hash, $withdraw_hash_array)) {
                            $out_transactions[$v->hash] = [
                                'blockHash' => $v->blockHash,
                                'blockNumber' => base_convert($v->blockNumber, 16, 10),
                                'from_a' => $v->from,
                                'gas' => $v->gas,
                                'gasPrice' => $v->gasPrice,
                                'nonce' => $v->nonce,
                                'to_a' => $v->to,
                                'transactionIndex' => $v->transactionIndex,
                                'value' => $v->value,
                                'updated_at' => time()
                            ];
                        }
                    }
                }
                if (!empty($transactions)) {
                    $res = Yii::$app->db->createCommand()->batchInsert(EthTransaction::tableName(), [
                        'blockHash', 'blockNumber', 'from_a', 'gas', 'gasPrice', 'hash', 'nonce', 'to_a', 'transactionIndex', 'value', 'type', 'created_at', 'updated_at'
                    ], $transactions)->execute();
                    if (!$res) {
                        return ['status' => false, 'msg' => 'save in transactions failed'];
                    }
                }

                if (!empty($out_transactions)) {
                    if (!MysqlHelper::batchUpdate('eth_transaction', 'hash', $out_transactions)) {
                        return ['status' => false, 'msg' => 'update out transactions failed'];
                    }
                }

            }
            return ['status' => true, 'msg' => 'Successful', 'localBlockInfo' => $eth_block_info];
        }

        return ['status' => false, 'msg' => $block_info->error->message];
    }

}