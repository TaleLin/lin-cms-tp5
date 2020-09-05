<?php

namespace app\api\controller\cms;

//use app\api\validate\user\LoginForm;  # 开启注释验证器以后，本行可以去掉，这里做更替说明
//use app\api\validate\user\RegisterForm; # 开启注释验证器以后，本行可以去掉，这里做更替说明
use app\api\service\token\LoginToken;
use app\lib\exception\token\TokenException;
use LinCmsTp5\admin\exception\user\UserException;
use LinCmsTp5\admin\model\LinUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\facade\Hook;
use think\Model;
use think\Request;
use think\response\Json;

class User
{

    /**
     * @var LoginToken
     */
    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }

    /**
     * 账户登陆
     * @param Request $request
     * @validate('LoginForm')
     * @return array
     * @throws Exception
     */
    public function login(Request $request)
    {
        //        (new LoginForm())->goCheck();  # 开启注释验证器以后，本行可以去掉，这里做更替说明
        $params = $request->post();

        $user = LinUser::verify($params['username'], $params['password']);
        $token = $this->loginTokenService->getToken($user->toArray());
        Hook::listen('logger', array('uid' => $user->id, 'username' => $user->username, 'msg' => '登陆成功获取了令牌'));

        return [
            'access_token' => $token['accessToken'],
            'refresh_token' => $token['refreshToken']
        ];
    }

    /**
     * 用户更新信息
     * @param Request $request
     * @return Json
     * @throws UserException
     */
    public function update(Request $request)
    {
        $params = $request->put();
        $uid = $this->loginTokenService->getCurrentUid();
        LinUser::updateUserInfo($uid, $params);
        return writeJson(201, '', '操作成功');
    }

    /**
     * 修改密码
     * @validate('ChangePasswordForm')
     * @param Request $request
     * @return Json
     * @throws UserException
     */
    public function changePassword(Request $request)
    {
        $params = $request->put();
        $uid = $this->loginTokenService->getCurrentUid();
        LinUser::changePassword($uid, $params);

        Hook::listen('logger', '修改了自己的密码');
        return writeJson(201, '', '密码修改成功');
    }


    /**
     * 查询自己拥有的权限
     * @return array|string|Model
     * @throws UserException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getAllowedApis()
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $result = LinUser::getUserByUID($uid);
        return $result;
    }

    /**
     * @auth('创建用户','管理员','hidden')
     * @param Request $request
     * @validate('RegisterForm')
     * @return Json
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function register(Request $request)
    {
        // (new RegisterForm())->goCheck(); # 开启注释验证器以后，本行可以去掉，这里做更替说明
        $params = $request->post();
        LinUser::createUser($params);

        Hook::listen('logger', '创建了一个用户');

        return writeJson(201, '', '用户创建成功');
    }

    /**
     * @return mixed
     */
    public function getInformation()
    {
        $uid = $this->loginTokenService->getCurrentUid();
        $user = LinUser::get($uid);
        return $user->hidden(['password']);
    }

    /**
     * @param Request $request
     * @return Json
     * @throws UserException
     */
    public function setAvatar(Request $request)
    {
        $url = $request->put('avatar');
        $uid = $this->loginTokenService->getCurrentUid();

        LinUser::updateUserAvatar($uid, $url);

        return writeJson(201, '', '更新头像成功');
    }


    /**
     * @return array
     * @throws TokenException
     * @throws Exception
     */
    public function refresh()
    {
        $token = $this->loginTokenService->getTokenFromHeaders();
        $result = $this->loginTokenService->refresh($token);
        return $result;
    }
}
