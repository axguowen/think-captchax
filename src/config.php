<?php
// +----------------------------------------------------------------------
// | ThinkCaptchaX [Behavior captcha package for ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP行为验证码扩展
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

return [
    // 默认验证平台
    'default' => 'geetest',
    // 验证平台配置
    'platforms' => [
        // 极验平台
        'geetest' => [
            // 驱动类型
            'type' => 'Geetest',
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
            // 驱动类型
            'type' => 'Vaptcha',
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
