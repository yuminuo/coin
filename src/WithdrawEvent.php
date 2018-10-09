<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 13:37
 */

namespace adamyxt\coin;

use yii\base\Event;

class WithdrawEvent extends Event
{
    /**
     * @var string|null unique id of a job
     */
    public $id;

    /**
     * @var
     */
    public $user_withdraw;
}