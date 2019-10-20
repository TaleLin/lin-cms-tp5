<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/28
 * Time: 21:38
 */

namespace app\api\validate\user;


use LinCmsTp5\validate\BaseValidate;

class LoginForm extends BaseValidate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
    ];

    protected $message = [
        'username' => '用户名不能为空',
        'password' => '密码不能为空'
    ];
}