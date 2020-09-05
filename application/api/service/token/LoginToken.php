<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/9/5
 * Time: 6:09 下午
 */

namespace app\api\service\token;


use app\lib\exception\token\TokenException;
use qinchen\token\TokenConfig;
use think\facade\Config;
use qinchen\token\Token as TokenUtils;
use think\facade\Request;


class LoginToken
{
    /**
     * @var LoginToken
     */
    private static $instance;

    /**
     * @var TokenConfig
     */
    private $tokenConfig;

    /**
     * LoginToken constructor.
     */
    private function __construct()
    {
        $config = Config::pull('token');
        $this->tokenConfig = (new TokenConfig())
            ->dualToken($config['enable_dual_token'])
            ->setAlgorithms($config['algorithms'])
            ->setIat(time())
            ->setIss($config['issuer'])
            ->setAccessSecretKey($config['access_secret_key'])
            ->setAccessExp($config['access_expire_time'])
            ->setRefreshSecretKey($config['refresh_secret_key'])
            ->setRefreshExp($config['refresh_expire_time']);
    }

    public static function getInstance(): LoginToken
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取令牌
     * @param array $extend 要插入到令牌扩展字段中的信息
     * @return array
     * @throws \Exception
     */
    public function getToken(array $extend): array
    {
        $this->tokenConfig->setExtend($extend);
        return TokenUtils::makeToken($this->tokenConfig);
    }

    /**
     * 令牌刷新
     * @param string $refreshToken 当开启了双令牌后颁发的refreshToken,用于刷新accessToken
     * @return array
     */
    public function refresh(string $refreshToken): array
    {
        return TokenUtils::refresh($refreshToken, $this->tokenConfig);
    }

    /**
     * 获取令牌扩展字段内容
     * @param string|null $token
     * @param string $tokenType
     * @return array
     * @throws TokenException
     */
    public function getTokenExtend(string $token = null, string $tokenType = 'access'): array
    {
        $token = $token ?: $this->getTokenFromHeaders();
        $payload = TokenUtils::verifyToken($token, $tokenType, $this->tokenConfig);
        return (array)$payload['extend'];
    }

    /**
     * 获取指定令牌扩展内容字段的值
     * @param string $val
     * @return mixed
     * @throws TokenException
     */
    public function getExtendVal(string $val)
    {
        return $this->getTokenExtend()[$val];
    }

    public function getCurrentUid()
    {
        return $this->getExtendVal('id');
    }

    public function getCurrentUserName()
    {
        return $this->getExtendVal('username');
    }

    public function getTokenFromHeaders(): string
    {
        $authorization = Request::header('authorization');

        if (!$authorization) {
            throw new TokenException(['msg' => '请求未携带Authorization信息', 'error_code' => 10000]);
        }

        list($type, $token) = explode(' ', $authorization);

        if ($type !== 'Bearer') throw new TokenException(['msg' => '接口认证方式需为Bearer', 'error_code' => 10000]);

        if (!$token || $token === 'undefined') {
            throw new TokenException(['msg' => '尝试获取的Authorization信息不存在', 'error_code' => 10000]);
        }

        return $token;
    }
}