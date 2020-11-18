<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:09 下午
 */

namespace app\api\service\admin;

use app\api\model\admin\LinGroup as LinGroupModel;
use app\api\model\admin\LinGroupPermission as LinGroupPermissionModel;
use app\api\model\admin\LinPermission as LinPermissionModel;
use app\api\model\admin\LinUser;
use app\api\model\admin\LinUser as LinUserModel;
use app\api\model\admin\LinUserGroup as LinUserGroupModel;
use app\api\model\admin\LinUserIdentity as LinUserIdentityModel;
use app\api\service\token\LoginToken;
use app\lib\enum\GroupLevelEnum;
use app\lib\enum\IdentityTypeEnum;
use app\lib\enum\MountTypeEnum;
use app\lib\exception\AuthFailedException;
use app\lib\exception\NotFoundException;
use app\lib\exception\OperationException;
use app\lib\exception\RepeatException;
use app\lib\exception\token\ForbiddenException;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

class User
{
    /**
     * @param array $params
     * @return LinUserModel
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ForbiddenException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     * @throws OperationException
     * @throws RepeatException
     */
    public static function createUser(array $params): LinUserModel
    {
        $user = LinUserModel::where('username', $params['username'])->find();
        if ($user) {
            throw new RepeatException(['msg' => '用户名已存在']);
        }

        if (isset($params['email'])) {
            $user = LinUserModel::where('email', $params['email'])->find();
            if ($user) {
                throw new RepeatException(['msg' => '邮箱地址已存在']);
            }
        }

        if (isset($params['group_ids'])) {
            $groups = LinGroupModel::select($params['group_ids']);
            foreach ($groups as $group) {
                if ($group['level'] === GroupLevelEnum::ROOT) {
                    throw new ForbiddenException(['msg' => '不允许分配用户到root分组']);
                }
            }

            if ($groups->isEmpty()) {
                throw new NotFoundException();
            }
        }

        return self::registerUser($params);
    }

    /**
     * @param string $username
     * @param string $password
     * @return Model
     * @throws AuthFailedException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws NotFoundException
     */
    public static function verify(string $username, string $password): Model
    {
        $user = new LinUserIdentityModel();

        $user = $user->where('identifier', $username)
            ->where('identity_type', IdentityTypeEnum::PASSWORD)
            ->find();

        if (!$user) {
            throw new NotFoundException(['msg' => '用户不存在']);
        }

        if (!$user->checkPassword($password)) {
            throw new AuthFailedException();
        }
        return $user;
    }

    public static function generateTokenExtend(Model $linUserIdentityModel)
    {
        $user = LinUserModel::get($linUserIdentityModel['user_id']);
        $userPermissions = self::getPermissions($user->getAttr('id'));
        return [
            'id' => $user->getAttr('id'),
            'identifier' => $linUserIdentityModel->getAttr('identifier'),
            'email' => $user->getAttr('email'),
            'admin' => $userPermissions['admin'],
            'permissions' => $userPermissions['permissions'],
        ];
    }

    public static function getPermissions(int $uid): array
    {
        $user = LinUserModel::get($uid);

        $groupIds = LinUserGroupModel::where('user_id', $uid)
            ->column('group_id');

        $root = LinGroupModel::where('level', GroupLevelEnum::ROOT)
            ->whereIn('id', $groupIds)->find();

        $user = $user->hidden(['username'])->toArray();
        $user['admin'] = $root ? true : false;

        if ($root) {
            $permissions = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
                ->select()
                ->toArray();
            $user['permissions'] = formatPermissions($permissions);
        } else {
            $permissionIds = LinGroupPermissionModel::whereIn('group_id', $groupIds)
                ->column('permission_id');
            $permissions = LinPermissionModel::where('mount', MountTypeEnum::MOUNT)
                ->select($permissionIds)->toArray();

            $user['permissions'] = formatPermissions($permissions);

        }

        return $user;
    }

    public static function getInformation(int $uid)
    {
        return LinUser::get($uid, 'groups');
    }

    public static function updateUser(array $params): int
    {
        $user = LoginToken::getInstance()->getTokenExtend();
        if (isset($params['username']) && $params['username'] !== $user['username']) {
            $isExit = LinUserModel::where('username', $params['username'])
                ->find();
            if ($isExit) {
                throw new RepeatException(['msg' => "用户名已被占用"]);
            }
        }

        if (isset($params['email']) && $params['email'] !== $user['email']) {
            $isExit = LinUserModel::where('email', $params['email'])
                ->find();
            if ($isExit) {
                throw new RepeatException(['msg' => "邮箱已被占用"]);
            }
        }

        $user = LinUserModel::get($user['id']);
        return $user->allowField(true)->save($params);
    }

    public static function changePassword(string $oldPassword, string $newPassword): int
    {
        $currentUser = LoginToken::getInstance()->getTokenExtend();
        $user = new LinUserIdentityModel();

        $user = $user::where('identity_type', IdentityTypeEnum::PASSWORD)
            ->where('identifier', $currentUser['identifier'])
            ->find();

        if (!$user) {
            throw new NotFoundException();
        }

        if (!$user->checkPassword($oldPassword)) {
            throw new AuthFailedException();
        }

        $user->credential = md5($newPassword);
        return $user->save();
    }

    /**
     * @param array $params
     * @return LinUserModel
     * @throws OperationException
     */
    private static function registerUser(array $params): LinUserModel
    {
        Db::startTrans();
        try {
            $user = LinUserModel::create($params, true);
            $user->identity()->save([
                'identity_type' => IdentityTypeEnum::PASSWORD,
                'identifier' => $user['username'],
                'credential' => md5($params['password'])
            ]);

            // 判断是否同时分配了分组
            if (isset($params['group_ids']) && count($params['group_ids']) > 0) {
                $user->groups()->attach($params['group_ids']);
            } else {
                //  没有分配分组，添加到游客分组
                $group = LinGroupModel::where('level', GroupLevelEnum::GUEST)->find();
                $user->groups()->attach([$group['id']]);
            }
            Db::commit();
            return $user;
        } catch (Exception $ex) {
            Db::rollback();
            throw new OperationException(['msg' => "注册用户失败：{$ex->getMessage()}"]);
        }

    }

    // private static function formatPermissions(array $permissions)
    // {
    //     $groupPermission = [];
    //     foreach ($permissions as $permission) {
    //         $item = [
    //             'name' => $permission['name'],
    //             'module' => $permission['module']
    //         ];
    //         $groupPermission[$permission['module']][] = $item;
    //     }
    //
    //     $result[] = array_map(function ($item) {
    //         return $item;
    //     }, $groupPermission);
    //     return $result;
    // }
}