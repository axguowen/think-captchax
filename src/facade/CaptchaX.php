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

namespace think\facade;

use think\Facade;

/**
 * Class CaptchaX
 * @package think\facade
 * @mixin \think\CaptchaX
 * @method static Platform platform(string $name = null) ,null|string
 * @method static mixed getConfig(null|string $name = null, mixed $default = null) 获取配置
 * @method static array getPlatformConfig(string $platform, null $name = null, null $default = null) 获取平台配置
 * @method static string|null getDefaultDriver() 默认平台
 * @method static array create() 生成验证码
 * @method static bool validate() 验证验证码
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
        return \think\CaptchaX::class;
    }
}