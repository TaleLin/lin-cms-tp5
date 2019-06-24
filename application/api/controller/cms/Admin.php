<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/16
 * Time: 11:21
 */

namespace app\api\controller\cms;

use app\lib\auth\AuthMap;
use LinCmsTp5\admin\exception\group\GroupException;
use LinCmsTp5\admin\model\LinAuth;
use LinCmsTp5\admin\model\LinGroup;
use LinCmsTp5\admin\model\LinUser;
use think\facade\Hook;
use think\Request;

class Admin
{

    /**
     * 配置hidden后，这个权限信息不会挂载到权限图，获取所有可分配的权限时不会显示这个权限
     * @auth('查询所有用户','管理员','hidden')
     * @param Request $request
     * @return array
     * @throws \think\exception\DbException
     */
    public function getAdminUsers(Request $request)
    {
        $params = $request->get();

        $result = LinUser::getAdminUsers($params);
        return $result;
    }

    /**
     * @auth('修改用户密码','管理员','hidden')
     * @param Request $request
     * @return \think\response\Json
     * @throws \LinCmsTp5\admin\exception\user\UserException
     */
    public function changeUserPassword(Request $request)
    {
        $params = $request->param();

        LinUser::resetPassword($params);
        return writeJson(201, '', '密码修改成功');
    }

    /**
     * @auth('删除用户','管理员','hidden')
     * @param $uid
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function deleteUser($uid)
    {
        LinUser::deleteUser($uid);
        Hook::listen('logger', '删除了用户id为' . $uid . '的用户');
        return writeJson(201, '', '操作成功');
    }

    /**
     * @auth('管理员更新用户信息','管理员','hidden')
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \LinCmsTp5\admin\exception\user\UserException
     */
    public function updateUser(Request $request)
    {
        $params = $request->param();
        LinUser::updateUser($params);

        return writeJson(201, '', '操作成功');
    }

    /**
     * @auth('查询所有权限组','管理员','hidden')
     * @return mixed
     */
    public function getGroupAll()
    {
        $result = LinGroup::all();

        return $result;
    }

    /**
     * @auth('查询一个权限组及其权限','管理员','hidden')
     * @param $id
     * @return array|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws GroupException
     */
    public function getGroup($id)
    {
        $result = LinGroup::getGroupByID($id);

        return $result;
    }


    /**
     * @auth('删除一个权限组','管理员','hidden')
     * @param $id
     * @return \think\response\Json
     * @throws GroupException
     */
    public function deleteGroup($id)
    {
        //查询当前权限组下是否存在用户
        $hasUser = LinUser::get(['group_id'=>$id]);
        if($hasUser)
        {
            throw new GroupException([
                'code' => 412,
                'msg' => '分组下存在用户，删除分组失败',
                'error_code' => 30005
            ]);
        }
        LinGroup::deleteGroupAuth($id);
        Hook::listen('logger', '删除了权限组id为' . $id . '的权限组');
        return writeJson(201, '', '删除分组成功');
    }

    /**
     * @auth('新建权限组','管理员','hidden')
     * @param Request $request
     * @return \think\response\Json
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws GroupException
     */
    public function createGroup(Request $request)
    {
        $params = $request->post();

        LinGroup::createGroup($params);
        return writeJson(201, '', '成功');
    }

    /**
     * @auth('更新一个权限组','管理员','hidden')
     * @param Request $request
     * @param $id
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function updateGroup(Request $request, $id)
    {
        $params = $request->put();

        $group = LinGroup::find($id);
        if (!$group) {
            throw new GroupException([
                'code' => 404,
                'msg' => '指定的分组不存在',
                'errorCode' => 30003
            ]);
        }
        $group->save($params);
        return writeJson(201, '', '更新分组成功');
    }

    /**
     * @auth('查询所有可分配的权限','管理员','hidden')
     * @return array
     * @throws \ReflectionException
     * @throws \WangYu\exception\ReflexException
     */
    public function authority()
    {
        $result = (new AuthMap())->run();

        return $result;
    }

    /**
     * @auth('删除多个权限','管理员','hidden')
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function removeAuths(Request $request)
    {
        $params = $request->post();

        LinAuth::where(['group_id' => $params['group_id'], 'auth' => $params['auths']])
            ->delete();
        return writeJson(201, '', '删除权限成功');
    }

    /**
     * @auth('分配多个权限','管理员','hidden')
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function dispatchAuths(Request $request)
    {
        $params = $request->post();

        LinAuth::dispatchAuths($params);
        Hook::listen('logger', '修改了id为' . $params['group_id'] . '的权限');
        return writeJson(201, '', '添加权限成功');
    }
}