<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/30
 * Time: 16:22
 */

namespace app\lib\exception\token;


use LinCmsTp5\exception\BaseException;

/**
 * Class TokenException
 * @package app\lib\exception\token
 */
class TokenException extends BaseException
{
    public $code = 401;
    public $msg  = '令牌解析失败';
    public $error_code = 10000;
}