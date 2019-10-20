<?php


namespace app\api\validate\user;


use LinCmsTp5\validate\BaseValidate;

class ChangePasswordForm extends BaseValidate
{
    protected $rule = [
        'old_password|原始密码' => 'require',
        'new_password|新密码' => 'require|confirm:confirm_password',
        'confirm_password|确认密码' => 'require',
    ];
}