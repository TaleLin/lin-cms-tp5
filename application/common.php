<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use app\lib\auth\AuthMap;
use LinCmsTp5\exception\ParameterException;
use think\facade\Request;

/**
 * @param $code
 * @param $errorCode
 * @param $data
 * @param $msg
 * @return \think\response\Json
 */

function writeJson($code, $data, $msg = 'ok', $errorCode = 0)
{
    $data = [
        'error_code' => $errorCode,
        'result' => $data,
        'msg' => $msg
    ];
    return json($data, $code);
}

function rand_char($length)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

function split_modules($auths, $module = 'module')
{
    if (empty($auths)) {
        return [];
    }

    $items = [];
    $result = [];

    foreach ($auths as $key => $value) {
        if (isset($items[$value['module']])) {
            $items[$value['module']][] = $value;
        } else {
            $items[$value['module']] = [$value];
        }
    }
    foreach ($items as $key => $value) {
        $item = [
            $key => $value
        ];
        array_push($result, $item);
    }
    return $result;

}

/**
 * @param $auth
 * @return array
 * @throws ReflectionException
 */
function findAuthModule($auth)
{
    $authMap = (new AuthMap())->run();
    foreach ($authMap as $key => $value) {
        foreach ($value as $k => $v) {
            if ($auth === $k) {
                return [
                    'auth' => $k,
                    'module' => $key
                ];
            }
        }
    }
}

///**
// * @param string $message
// * @param string $uid
// * @param string $nickname
// * @throws \app\lib\exception\token\TokenException
// * @throws \think\Exception
// */
//function logger(string $message, $uid = '', $nickname = '')
//{
//    if ($message === '') {
//        throw new LoggerException([
//            'msg' => '日志信息不能为空'
//        ]);
//    }
//
//    $params = [
//        'message' => $nickname ? $nickname . $message : Token::getCurrentName() . $message,
//        'user_id' => $uid ? $uid : Token::getCurrentUID(),
//        'user_name' => $nickname ? $nickname : Token::getCurrentName(),
//        'status_code' => Response::getCode(),
//        'method' => Request::method(),
//        'path' => Request::path(),
//        'authority' => ''
//    ];
//    LinLog::create($params);
//}

/**
 * @return array
 * @throws ParameterException
 */
function paginate()
{
    $count = intval(Request::get('count'));
    $start = intval(Request::get('page'));

    $count = $count >= 15 ? 15 : $count;

    $start = $start * $count;

    if ($start < 0 || $count < 0) {
        throw new ParameterException();
    }

    return [$start, $count];
}