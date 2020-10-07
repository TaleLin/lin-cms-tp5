<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/7
 * Time: 2:35 下午
 */

namespace app\api\validate\user;


use LinCmsTp5\validate\BaseValidate;

class UpdateUserForm extends BaseValidate
{
    protected $rule = [
        'username' => 'length:2,10',
        'email' => 'email',
        'nickname' => 'length:2,10',
    ];
}