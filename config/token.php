<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/9/5
 * Time: 6:31 下午
 */
return [
    # 是否开启双令牌模式，本项目必须
    'enable_dual_token' => true,
    # 令牌算法类型
    'algorithms' => 'HS256',
    # 令牌签发者
    'issuer' => 'lin-cms-tp5',
    # accessToken秘钥
    'access_secret_key' => 'w.kx(c82jkA',
    # accessToken过期时间，单位秒
    'access_expire_time' => 7200,
    # refreshToken秘钥
    'refresh_secret_key' => 'xUh.@3s8A8',
    # refreshToken过期时间，建议设置较长时间
    # 在有效期内可用于刷新accessToken，单位秒
    'refresh_expire_time' => 604800,
];