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

class Geetest
{
    // 基础URL
    const BASE_URL = 'http://api.geetest.com';
    // SDK版本
    const VERSION = 'php-laravel:3.1.0';

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 极验验证ID
        'captcha_id' => '',
        // 极验私钥
        'private_key' => '',
        // 加密模式, 支持md5/sha256/hmac-sha256, 默认为md5
        'digestmod' => 'md5',
        // 二次验证challenge字段名, 默认geetest_challenge
        'challenge_field' => '',
        // 二次验证validate字段名, 默认geetest_validate
        'validate_field' => '',
        // 二次验证seccode字段名, 默认geetest_seccode
        'seccode_field' => '',
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
        if (empty($this->options['challenge_field'])){
            $this->options['challenge_field'] = 'geetest_challenge';
        }
        if (empty($this->options['validate_field'])){
            $this->options['validate_field'] = 'geetest_validate';
        }
        if (empty($this->options['seccode_field'])){
            $this->options['seccode_field'] = 'geetest_seccode';
        }
    }

    /**
     * 创建验证码
     * @access public
     * @param array $params 验证码参数
     * @return mixed
     */
    public function create($params = [])
    {
        // 验证码流水号
        $originChallenge = null;
        // 如果服务器正常
        if($this->serverCheck()){
            // 从服务器获取流水号数据源
            $originChallenge = $this->requestRegister($params);
        }
        // 返回
        return $this->buildRegisterResult($originChallenge);
    }

    /**
     * 验证码校验
     * @access public
     * @param  string $params 额外参数
     * @return bool
     */
    public function validate($params = [])
    {
        // 获取请求中的流水号
        $challenge = Request::param($this->options['challenge_field']);
        // 获取请求中的验证数据
        $validate = Request::param($this->options['validate_field']);
        // 获取请求中的加密代码
        $seccode = Request::param($this->options['seccode_field']);
        
        // 参数校验未通过
        if (!$this->paramsCheck($challenge, $validate, $seccode)) {
            // 返回失败
            return false;
        }
        // 如果服务器正常
        if($this->serverCheck()){
            return $this->successValidate($challenge, $validate, $seccode, $params);
        }
        // 简单校验
        return $this->failValidate($challenge, $validate, $seccode);
    }

    /**
     * 从极验服务器获取验证码流水号数据源
     * @access protected
     * @param array $params 验证码参数
     * @return mixed
     */
    protected function requestRegister($params)
    {
        // 请求参数
        $data = [
            'user_id' => '',
            'client_type' => 'web',
            'ip_address' => '',
            'gt' => $this->options['captcha_id'],
            'sdk' => self::VERSION,
            'json_format' => 1,
            'digestmod' => $this->options['digestmod'],
        ];
        // 初始化允许传入的参数获取
        $initParams = [];
        if(isset($params['user_id'])){
            $initParams['user_id'] = $params['user_id'];
        }
        if(isset($params['client_type'])){
            $initParams['client_type'] = $params['client_type'];
        }
        if(isset($params['ip_address'])){
            $initParams['ip_address'] = $params['ip_address'];
        }
        // 合并参数
        $data = array_merge($data, $initParams);

        // 验证码流水号
        $originChallenge = null;
        // 发送请求
        $resBody = $this->httpGet(self::BASE_URL . '/register.php', $data);
        // 反序列化响应结果
        $resArray = json_decode($resBody, true);
        // 数据有效
        if(is_array($resArray) && isset($resArray['challenge'])){
            $originChallenge = $resArray['challenge'];
        }
        // 返回
        return $originChallenge;
    }

    /**
     * 构建验证码初始化数据
     * @access protected
     * @param array $originChallenge 验证码流水号数据源
     * @return array
     */
    protected function buildRegisterResult($originChallenge)
    {
        // originChallenge为空或者值为0代表失败
        if (empty($originChallenge)) {
            // 本地随机生成32位字符串
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
            // 验证码流水号
            $challenge = '';
            for ($i = 0; $i < 32; $i++) {
                $challenge .= $characters[rand(0, strlen($characters) - 1)];
            }
            // 返回
            return [
                'success' => 0,
                'gt' => $this->options['captcha_id'],
                'challenge' => $challenge,
                'new_captcha' => true
            ];
        }
        
        // 验证码流水号
        $challenge = null;
        switch($this->options['digestmod']){
            case 'md5':
                $challenge = $this->md5_encode($originChallenge . $this->options['private_key']);
                break;
            case 'sha256':
                $challenge = $this->sha256_encode($originChallenge . $this->options['private_key']);
                break;
            case 'hmac-sha256':
                $challenge = $this->hmac_sha256_encode($originChallenge, $this->options['private_key']);
                break;
            default:
                $challenge = $this->md5_encode($originChallenge . $this->options['private_key']);
        }
        // 返回
        return [
            'success' => 1,
            'gt' => $this->options['captcha_id'],
            'challenge' => $challenge,
            'new_captcha' => true
        ];
    }

    /**
     * 正常流程下（即验证初始化成功），二次验证
     * @access protected
     * @param  string $challenge 流水号
     * @param  string $validate 验证数据
     * @param  string $seccode 加密代码
     * @param  string $params 额外参数
     * @return bool
     */
    protected function successValidate($challenge, $validate, $seccode, $params = [])
    {
        // 构造验证码流水号加密串
        $secChallenge = null;
        $secSeccode = null;
        // 构造加密代码的加密串
        switch($this->options['digestmod']){
            case 'md5':
                $secChallenge = $this->md5_encode($this->options['private_key'] . 'geetest' . $challenge);
                $secSeccode = $this->md5_encode($seccode);
                break;
            case 'sha256':
                $secChallenge = $this->sha256_encode($this->options['private_key'] . 'geetest' . $challenge);
                $secSeccode = $this->sha256_encode($seccode);
                break;
            case 'hmac-sha256':
                $secChallenge = $this->hmac_sha256_encode('geetest' . $challenge, $this->options['private_key']);
                $secSeccode = $this->hmac_sha256_encode($seccode, $this->options['private_key']);
                break;
            default:
                $secChallenge = $this->md5_encode($this->options['private_key'] . 'geetest' . $challenge);
                $secSeccode = $this->md5_encode($seccode);
        }
        // 本地校验验证串是否正确
        if($secChallenge != $validate){
            // 验证失败
            return false;
        }
        // 发送验证请求
        $responseSeccode = $this->requestValidate($challenge, $validate, $seccode, $params);
        if (empty($responseSeccode) || $responseSeccode === 'false' || $responseSeccode != $secSeccode) {
            // 验证失败
            return false;
        }
        // 验证成功
        return true;
    }

    /**
     * 异常流程下（即验证初始化失败，宕机模式），二次验证
     * 注意：由于是宕机模式，初衷是保证验证业务不会中断正常业务，所以此处只作简单的参数校验，可自行设计逻辑。
     * @access protected
     * @param  string $challenge 流水号
     * @param  string $validate 验证数据
     * @param  string $seccode 加密代码
     * @return bool
     */
    protected function failValidate($challenge, $validate, $seccode)
    {
        // 本地校验验证串是否正确
        if($this->md5_encode($challenge) != $validate){
            // 验证失败
            return false;
        }
        // 验证成功
        return true;
    }

    /**
     * 向极验发送二次验证的请求，POST方式
     * @access protected
     * @param  string $challenge 流水号
     * @param  string $validate 验证数据
     * @param  string $seccode 加密代码
     * @param  string $params 额外参数
     * @return bool
     */
    protected function requestValidate($challenge, $validate, $seccode, $params = [])
    {
        // 请求参数
        $data = [
            'user_id' => '',
            'client_type' => 'web',
            'ip_address' => '',
            'seccode' => $seccode,
            'json_format' => 1,
            'challenge' => $challenge,
            'sdk' => self::VERSION,
            'captchaid' => $this->options['captcha_id']
        ];
        // 验证允许传入的参数获取
        $validateParams = [];
        if(isset($params['user_id'])){
            $validateParams['user_id'] = $params['user_id'];
        }
        if(isset($params['client_type'])){
            $validateParams['client_type'] = $params['client_type'];
        }
        if(isset($params['ip_address'])){
            $validateParams['ip_address'] = $params['ip_address'];
        }
        // 合并参数
        $data = array_merge($data, $validateParams);
        // 验证结果
        $responseSeccode = null;
        // 发送请求
        $resBody = $this->httpPost(self::BASE_URL . '/validate.php', $data);
        // 反序列化响应结果
        $resArray = json_decode($resBody, true);
        // 数据有效
        if(is_array($resArray) && isset($resArray['seccode'])){
            $responseSeccode = $resArray['seccode'];
        }
        // 返回
        return $responseSeccode;
    }

    /**
     * 校验二次验证的三个参数，校验通过返回true，校验失败返回false
     * @access protected
     * @param  string $challenge 流水号
     * @param  string $validate 验证数据
     * @param  string $seccode 加密代码
     * @return bool
     */
    protected function paramsCheck($challenge, $validate, $seccode)
    {
        return !(empty($challenge) || ctype_space($challenge) || empty($validate) || ctype_space($validate) || empty($seccode) || ctype_space($seccode));
    }

    /**
     * 检测极验服务器是否宕机
     * @access protected
     * @return bool
     */
    protected function serverCheck()
    {
        // 请求参数
        $data = [
            'gt' => $this->options['captcha_id'],
        ];
        // 发送请求
        $resBody = $this->httpGet('https://bypass.geetest.com/v1/bypass_status.php', $data);
        // 反序列化响应结果
        $resArray = json_decode($resBody, true);
        // 如果数据无效
        if(!is_array($resArray) || !isset($resArray['status'])){
            return false;
        }
        // 如果返回失败
        if($resArray['status'] != 'success'){
            return false;
        }
        // 返回成功
        return true;
    }

    /**
     * 发送GET请求，获取服务器返回结果
     * @access protected
     * @param  string $url 请求地址
     * @param  string $params 请求参数
     * @return mixed
     */
    protected function httpGet($url, $params)
    {
        $url .= '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 设置连接主机超时（单位：秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 允许 cURL 函数执行的最长秒数（单位：秒）
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 发送POST请求，获取服务器返回结果
     * @access protected
     * @param  string $url 请求地址
     * @param  string $params 请求参数
     * @return mixed
     */
    protected function httpPost($url, $param)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 设置连接主机超时（单位：秒）
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 允许 cURL 函数执行的最长秒数（单位：秒）
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type:application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * md5 加密
     * @access protected
     * @param  string $value 要加密的内容
     * @return string
     */
    protected function md5_encode($value)
    {
        return hash('md5', $value);
    }

    /**
     * sha256加密
     * @access protected
     * @param  string $value 要加密的内容
     * @return string
     */
    protected function sha256_encode($value)
    {
        return hash('sha256', $value);
    }

    /**
     * hmac-sha256 加密
     * @access protected
     * @param  string $value 要加密的内容
     * @return string
     */
    protected function hmac_sha256_encode($value, $key)
    {
        return hash_hmac('sha256', $value, $key);
    }
}