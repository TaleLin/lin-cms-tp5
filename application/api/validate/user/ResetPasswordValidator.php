<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 10:58 下午
 */

namespace app\api\validate\user;


use LinCmsTp5\validate\BaseValidate;

class ResetPasswordValidator extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer',
        'new_password|新密码' => 'require|confirm:confirm_password',
        'confirm_password|确认密码' => 'require',
    ];
}