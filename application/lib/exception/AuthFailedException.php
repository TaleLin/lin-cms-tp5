<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/7
 * Time: 12:16 下午
 */

namespace app\lib\exception;


use LinCmsTp5\exception\BaseException;

class AuthFailedException extends BaseException
{
    public $code = 403;
    public $msg = '用户身份认证失败';
    public $error_code = 10021;

}