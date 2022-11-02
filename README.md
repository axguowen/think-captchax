# think-captchax

ThinkPHP6.0 行为验证码扩展

主要功能：

支持多平台配置：目前支持极验(Geetest)、Vaptcha平台；

可扩展自定义平台驱动；

支持facade门面方式调用；

支持动态切换平台；

## 安装

~~~php
composer require axguowen/think-captchax
~~~

## 用法示例

本扩展不能单独使用，依赖ThinkPHP6.0+

首先配置config目录下的captchax.php配置文件，然后可以按照下面的用法使用。

### 生成验证码数据

~~~php

use axguowen\facade\CaptchaX;

// 生成极验验证码数据
$captchaData = CaptchaX::create([
    'user_id' => '',
    'client_type' => \think\facade\Request::isMobile() ? 'h5' : 'web',
    'ip_address' => \think\facade\Request::ip(),
]);

print_r($captchaData);

~~~

### 验证码校验

~~~php

$validateStatus = CaptchaX::validate([
    'user_id' => '',
    'client_type' => \think\facade\Request::isMobile() ? 'h5' : 'web',
    'ip_address' => \think\facade\Request::ip(),
]);

// 验证不通过
if(!$validateStatus){
    throw new \think\Exception('验证不通过', 400);
}

~~~

### 动态切换平台

~~~php

use axguowen\facade\CaptchaX;

// 使用Vaptcha平台
$validateStatus = CaptchaX::platform('vaptcha')->validate([
    'ip' => \think\facade\Request::ip(),
]);

// 验证不通过
if(!$validateStatus){
    throw new \think\Exception('验证不通过', 400);
}

~~~

## 配置说明

~~~php

// 验证码配置
return [
    // 默认验证平台
    'default' => 'geetest',
    // 验证平台配置
    'platforms' => [
        // 极验平台
        'geetest' => [
            // 极验验证ID
            'captcha_id' => '',
            // 极验私钥
            'private_key' => '',
            // 加密模式, 支持md5/sha256/hmac-sha256, 默认为md5
            'digestmod' => '',
            // 二次验证challenge字段名, 默认geetest_challenge
            'challenge_field' => '',
            // 二次验证validate字段名, 默认geetest_validate
            'validate_field' => '',
            // 二次验证seccode字段名, 默认geetest_seccode
            'seccode_field' => '',
        ],
        // vaptcha平台
        'vaptcha' => [
            // 验证单元的VID
            'vid' => '',
            // 验证单元的KEY
            'key' => '',
            // 验证场景
            'scene' => 0,
            // 二次验证server字段名 默认server
            'server_field' => '',
            // 二次验证token字段名 默认token
            'token_field' => '',
        ],
    ],
];

~~~

## 自定义平台驱动

如果需要扩展自定义验证平台驱动，需要继承axguowen\captchax\Platform类, 并实现create方法和validate方法。

具体代码可以参考现有的平台驱动

扩展自定义驱动后，只需要在配置文件captchax.php中设置default的值为该驱动类名（包含命名空间）即可。