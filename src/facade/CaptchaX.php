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

namespace axguowen\facade;

use think\Facade;

/**
 * Class CaptchaX
 * @package axguowen\facade
 * @mixin \axguowen\CaptchaX
 * @method static Platform platform(string $name = null) ,null|string
 * @method static mixed getConfig(null|string $name = null, mixed $default = null) 获取配置
 * @method static array getPlatformConfig(string $platform, null $name = null, null $default = null) 获取平台配置
 * @method static string|null getDefaultDriver() 默认平台
 * @method static array create(array $params) 生成验证码
 * @method static bool validate(array $params) 验证码校验
 */
class CaptchaX extends Facade
{
    /**
     * 获取当前Facade对应类名
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return \axguowen\CaptchaX::class;
    }
}