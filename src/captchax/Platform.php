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

namespace axguowen\captchax;

abstract class Platform
{
	/**
     * 平台句柄
     * @var object
     */
    protected $handler = null;

	/**
     * 平台配置参数
     * @var array
     */
	protected $options = [];

	/**
     * 获取平台配置
     * @access public
     * @param  string $name 配置名称
     * @return array
     */
    public function config($name = null)
    {
        // 未指定配置项
        if (is_null($name)) {
            // 返回全部配置
            return $this->options;
        }
        // 存在指定的配置项
        if(isset($this->options[$name])){
            return $this->options[$name];
        }
        // 指定的配置项不存在
        return null;
    }

    /**
     * 设置平台配置
     * @access public
     * @param  array $options 配置参数
     * @return $this
     */
    public function setConfig($options)
    {
        // 合并配置参数
        $this->options = array_merge($this->options, $options);
        // 返回
        return $this;
    }

	/**
     * 返回平台句柄对象，可执行其它高级方法
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

	public function __call($method, $args)
    {
        return call_user_func_array([$this->handler, $method], $args);
    }
}