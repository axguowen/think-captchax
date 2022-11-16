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
     * 返回平台句柄对象，可执行其它高级方法
     * @access public
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 生成验证码
     * @access public
     * @return array
     */
    abstract public function create();

    /**
     * 验证验证码
     * @access public
     * @return array
     */
    abstract public function validate();

	public function __call($method, $args)
    {
        return call_user_func_array([$this->handler, $method], $args);
    }
}