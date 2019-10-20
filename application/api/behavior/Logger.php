<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/5/16
 * Time: 17:19
 */

namespace app\api\behavior;


use app\lib\token\Token;
use LinCmsTp5\admin\exception\logger\LoggerException;
use LinCmsTp5\admin\model\LinLog;
use think\facade\Request;
use think\facade\Response;

class Logger
{
    /**
     * @param $params
     * @throws LoggerException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     */
    public function run($params)
    {

        // 行为逻辑
        if (empty($params)) {
            throw new LoggerException([
                'msg' => '日志信息不能为空'
            ]);
        }

        if (is_array($params)) {
            list('uid' => $uid, 'username' => $username, 'msg' => $message) = $params;
        } else {
            $uid = Token::getCurrentUID();
            $username = Token::getCurrentName();
            $message = $params;
        }

        $data = [
            'message' => $username . $message,
            'user_id' => $uid,
            'user_name' => $username,
            'status_code' => Response::getCode(),
            'method' => Request::method(),
            'path' => Request::path(),
            'authority' => ''
        ];

        LinLog::create($data);

    }
}