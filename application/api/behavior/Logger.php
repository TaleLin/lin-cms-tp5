<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/5/16
 * Time: 17:19
 */

namespace app\api\behavior;


use app\api\model\admin\LinLog;
use app\api\service\token\LoginToken;
use app\lib\exception\OperationException;
use think\facade\Request;
use think\facade\Response;

class Logger
{
    /**
     * @param $params
     * @throws OperationException
     */
    public function run($params)
    {

        // 行为逻辑
        if (empty($params)) {
            throw new OperationException([
                'msg' => '日志信息不能为空'
            ]);
        }

        if (is_array($params)) {
            list('uid' => $uid, 'username' => $username, 'msg' => $message) = $params;
        } else {
            $tokenService = LoginToken::getInstance();
            $uid = $tokenService->getCurrentUid();
            $username = $tokenService->getCurrentUserName();
            $message = $params;
        }

        $data = [
            'message' => $username . $message,
            'user_id' => $uid,
            'username' => $username,
            'status_code' => Response::getCode(),
            'method' => Request::method(),
            'path' => '/' . Request::path(),
            'permission' => null
        ];

        LinLog::create($data);

    }
}