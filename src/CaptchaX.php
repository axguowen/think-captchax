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

namespace axguowen;

use think\Manager;
use think\helper\Arr;
use think\exception\InvalidArgumentException;

/**
 * Class CaptchaX
 * @package think
 * @mixin Platform
 */
class CaptchaX extends Manager
{
	/**
     * 驱动的命名空间
     * @var string
     */
	protected $namespace = '\\think\\captchax\\driver\\';

	/**
     * 默认驱动
     * @access public
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

	/**
     * 获取配置
     * @access public
     * @param null|string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig($name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('captchax.' . $name, $default);
        }

        return $this->app->config->get('captchax');
    }

	/**
     * 获取平台配置
     * @param string $platform 平台名称
     * @param null|string $name 配置名称
     * @param null|string $default 默认值
     * @return array
     */
    public function getPlatformConfig($platform, $name = null, $default = null)
    {
		// 读取驱动配置文件
        if ($config = $this->getConfig('platforms.' . $platform)) {
            return Arr::get($config, $name, $default);
        }
		// 驱动不存在
        throw new \InvalidArgumentException('验证码平台 [' . $platform . '] 配置不存在.');
    }

    /**
     * 当前平台的驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveType($name)
    {
        return $this->getPlatformConfig($name, 'type', 'geetest');
    }

	/**
     * 获取驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveConfig($name)
    {
        return $this->getPlatformConfig($name);
    }

	/**
     * 选择或者切换平台
     * @access public
     * @param string $name 平台的配置名
     * @return \think\captchax\Platform
     */
    public function platform($name = null)
    {
        return $this->driver($name);
    }

    /**
     * 生成验证码
     * @access public
     * @param array $data 验证码数据
     * @return mixed
     */
    public function create()
    {
        return $this->platform()->create();
    }

    /**
     * 验证验证码
     * @access public
     * @param array $data 验证码数据
     * @return mixed
     */
    public function validate()
    {
        return $this->platform()->validate();
    }
}