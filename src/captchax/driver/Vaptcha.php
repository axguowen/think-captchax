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

namespace axguowen\captchax\driver;

use think\facade\Request;

class Vaptcha
{
    /**
     * 平台配置参数
     * @var array
     */
    protected $options = [
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
    ];

    /**
     * 架构函数
     * @access public
     * @param array $options 配置参数
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        if (empty($this->options['server_field'])){
            $this->options['server_field'] = 'server';
        }
        if (empty($this->options['token_field'])){
            $this->options['token_field'] = 'token';
        }
    }

    /**
     * 创建验证码
     * @access public
     * @return mixed
     */
    public function create()
    {
        // 返回配置
        return [
            'vid' => $this->options['vid'],
            'scene' => $this->options['scene'],
        ];
    }

    /**
     * 获取验证结果
     * @access public
     * @return bool
     */
    public function validate() {
        // 获取请求中的server参数
        $server = Request::param($this->options['server_field']);
        if(empty($server)){
            return false;
        }
        // 获取请求中的token参数
        $token = Request::param($this->options['token_field']);
        if(empty($token)){
            return false;
        }
        // 请求参数
        $data = [
            'id' => $this->options['vid'],
            'secretkey' => $this->options['key'],
            'scene' => $this->options['scene'],
            'token' => $token,
            'ip' => Request::ip(),
        ];
        // 发送请求
        $resBody = $this->httpJson($server, json_encode($data));
        // 反序列化响应结果
        $resArray = json_decode($resBody, true);
        // 数据有效
        if(is_array($resArray) && isset($resArray['success']) && $resArray['success'] == 1){
            return true;
        }
        // 返回
        return false;
    }

    /**
     * 发送POST请求，获取服务器返回结果
     * @access protected
     * @param  string $url 请求地址
     * @param  string $data json格式请求参数
     * @return mixed
     */
    protected function httpJson($url, $data = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 设置连接主机超时（单位：秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 允许 cURL 函数执行的最长秒数（单位：秒）
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
