<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/16
 * Time: 11:33
 */

namespace app\lib\token;

use app\lib\exception\token\TokenException;
use Firebase\JWT\JWT;
use LinCmsTp5\admin\model\LinUser;
use think\Exception;
use think\facade\Request;

class Token
{
    public static function getToken($user)
    {
        $accessToken = self::createAccessToken($user);
        $refreshToken = self::createRefreshToken($user);
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * @return array
     * @throws Exception
     * @throws TokenException
     */
    public static function refreshToken()
    {
        try {
            $uid = self::getCurrentTokenVar('id','refresh_token_salt');
            $user = LinUser::get($uid);
            $accessToken = self::createAccessToken($user);
        } catch (TokenException $ex) {
            throw new TokenException(['msg' => $ex->msg,'error_code' => 10010]);
        }

        return [
            'access_token' => $accessToken,
        ];
    }

    private static function createAccessToken($user)
    {
        $key = config('secure.access_token_salt');
        $payload = [
            'iss' => 'lin-cms-tp5', //签发者
            'iat' => time(), //什么时候签发的
            'exp' => time() + 7200, //过期时间
            'user' => $user,
        ];
        $token = JWT::encode($payload, $key);
        return $token;

    }

    private static function createRefreshToken($user)
    {
        $key = config('secure.refresh_token_salt');
        $payload = [
            'iss' => 'lin-cms-tp5', //签发者
            'iat' => time(), //什么时候签发的
            'exp' => time() + 604800, //过期时间，一个星期
            'user' => ['id' => $user->id],
        ];
        $token = JWT::encode($payload, $key);
        return $token;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentUser()
    {
        $uid = self::getCurrentUID();
        $user = LinUser::get($uid);
        return $user->hidden(['password']);
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentUID()
    {
        $uid = self::getCurrentTokenVar('id');
        return $uid;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    public static function getCurrentName()
    {
        $uid = self::getCurrentTokenVar('username');
        return $uid;
    }

    /**
     * @param string $key
     * @param string $tokenType
     * @return mixed
     * @throws Exception
     * @throws TokenException
     */
    private static function getCurrentTokenVar($key, $tokenType = 'access_token_salt')
    {
        $authorization = Request::header('authorization');

        if (!$authorization) {
            throw new TokenException(['msg' => '请求未携带Authorization信息']);
        }

        list($type, $token) = explode(' ', $authorization);

        if ($type !== 'Bearer') throw new TokenException(['msg' => '接口认证方式需为Bearer']);

        if (!$token || $token === 'undefined') {
            throw new TokenException(['msg' => '尝试获取的Authorization信息不存在']);
        }

        $secretKey = config("secure.{$tokenType}");

        try {
            $jwt = (array)JWT::decode($token, $secretKey, ['HS256']);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {  //签名不正确
            throw new TokenException(['msg' => '令牌签名不正确，请确认令牌有效性或令牌类型']);
        } catch (\Firebase\JWT\BeforeValidException $e) {  // 签名在某个时间点之后才能用
            throw new TokenException(['msg' => '令牌尚未生效']);
        } catch (\Firebase\JWT\ExpiredException $e) {  // token过期
            throw new TokenException(['msg' => '令牌已过期，刷新浏览器重试', 'error_code' => 10050]);
        } catch (Exception $e) {  //其他错误
            throw new Exception($e->getMessage());
        }
        if (array_key_exists($key, $jwt['user'])) {
            return $jwt['user']->$key;
        } else {
            throw new TokenException(['msg' => '尝试获取的Token变量不存在']);
        }

    }
}
