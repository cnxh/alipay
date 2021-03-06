<?php

namespace Cnxh\Alipay;

use DOMDocument;
use Illuminate\Support\Str;

abstract class ServiceBase
{
    // 支付宝网关地址（新）
    protected $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    // 商户id  2088*********
    protected $partner;

    // key
    protected $key;

    // 默认签名方式为md5
    protected $sign_type = 'MD5';

    // 默认编码为utf-8
    protected $input_charset = 'UTF-8';

    // 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    protected $transport = 'https';

    // 私钥路径地址 签名方式为RSA时设置
    protected $private_key_path;

    // 公钥路径地址 签名方式为RSA时设置
    protected $ali_public_key_path;

    // ca证书路径地址，用于curl中ssl校验
    protected $cacert = __DIR__.'/../../../../cacert.pem';

    public function __construct($config)
    {
        foreach ($config as $k => $v) {
            $method = 'set'.Str::studly($k);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $v);
            }
        }
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function setSignType($sign_type)
    {
        $this->sign_type = $sign_type;

        return $this;
    }

    public function setInputCharset($input_charset)
    {
        $this->input_charset = $input_charset;

        return $this;
    }

    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    public function setPrivateKeyPath($private_key_path)
    {
        $this->private_key_path = $private_key_path;

        return $this;
    }

    public function setAliPublicKeyPath($ali_public_key_path)
    {
        $this->ali_public_key_path = $ali_public_key_path;

        return $this;
    }

    public function setCacert($cacert)
    {
        $this->cacert = $cacert;

        return $this;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串.
     *
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstring($para)
    {
        $arg = '';
        while (list($key, $val) = each($para)) {
            $arg .= $key.'='.$val.'&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码.
     *
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstringUrlencode($para)
    {
        $arg = '';
        while (list($key, $val) = each($para)) {
            $arg .= $key.'='.urlencode($val).'&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 除去数组中的空值和签名参数.
     *
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    protected function paraFilter($para)
    {
        $para_filter = array();
        while (list($key, $val) = each($para)) {
            if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }

        return $para_filter;
    }

    /**
     * 对数组排序.
     *
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    protected function argSort($para)
    {
        ksort($para);
        reset($para);

        return $para;
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置.
     *
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    protected function logResult($word = '')
    {
        $fp = fopen('log.txt', 'a');
        flock($fp, LOCK_EX);
        fwrite($fp, '执行日期：'.strftime('%Y%m%d%H%M%S', time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'.
     *
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * @param $para 请求的数据
     * @param $input_charset 编码格式。默认值：空值
     * return 远程输出的数据
     */
    protected function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '', $follow_location = false)
    {
        if (trim($input_charset) != '') {
            $url = $url.'_input_charset='.$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para);// post传输数据
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $follow_location); // 跳转
        $responseText = curl_exec($curl);
        curl_close($curl);

        return $responseText;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'.
     *
     * @param $url 指定URL完整路径地址
     * @param $cacert_url 指定当前工作目录绝对路径
     * return 远程输出的数据
     */
    protected function getHttpResponseGET($url, $cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url);//证书地址
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    /**
     * 实现多种字符编码方式.
     *
     * @param $input 需要编码的字符串
     * @param $_output_charset 输出的编码格式
     * @param $_input_charset 输入的编码格式
     * return 编码后的字符串
     */
    protected function charsetEncode($input, $_output_charset, $_input_charset)
    {
        $output = '';
        if (!isset($_output_charset)) {
            $_output_charset = $_input_charset;
        }
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists('mb_convert_encoding')) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists('iconv')) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else {
            die('sorry, you have no libs support for charset change.');
        }

        return $output;
    }

    /**
     * 实现多种字符解码方式.
     *
     * @param $input 需要解码的字符串
     * @param $_output_charset 输出的解码格式
     * @param $_input_charset 输入的解码格式
     * return 解码后的字符串
     */
    protected function charsetDecode($input, $_input_charset, $_output_charset)
    {
        $output = '';
        if (!isset($_input_charset)) {
            $_input_charset = $_input_charset;
        }
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists('mb_convert_encoding')) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists('iconv')) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else {
            die('sorry, you have no libs support for charset changes.');
        }

        return $output;
    }

    /**
     * RSA签名.
     *
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    protected function rsaSign($data, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);

        return $sign;
    }

    /**
     * RSA验签.
     *
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    protected function rsaVerify($data, $ali_public_key_path, $sign)
    {
        $pubKey = file_get_contents($ali_public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);

        return $result;
    }

    /**
     * RSA解密.
     *
     * @param $content 需要解密的内容，密文
     * @param $private_key_path 商户私钥文件路径
     * return 解密后内容，明文
     */
    protected function rsaDecrypt($content, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        //用base64将内容还原成二进制
        $content = base64_decode($content);
        //把需要解密的内容，按128位拆开解密
        $result = '';
        for ($i = 0; $i < strlen($content) / 128; ++$i) {
            $data = substr($content, $i * 128, 128);
            openssl_private_decrypt($data, $decrypt, $res);
            $result .= $decrypt;
        }
        openssl_free_key($res);

        return $result;
    }

    /**
     * 签名字符串.
     *
     * @param $prestr 需要签名的字符串
     * @param $key 私钥
     * return 签名结果
     */
    protected function md5Sign($prestr, $key)
    {
        $prestr = $prestr.$key;

        return md5($prestr);
    }

    /**
     * 验证签名.
     *
     * @param $prestr 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * return 签名结果
     */
    protected function md5Verify($prestr, $sign, $key)
    {
        $prestr = $prestr.$key;
        $mysgin = md5($prestr);

        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取返回时的签名验证结果.
     *
     * @param $para_temp 通知返回来的参数数组
     * @param $sign 返回的签名结果
     *
     * @return 签名验证结果
     */
    public function getSignVeryfy($para_temp, $sign)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $is_sgin = false;
        switch (strtoupper(trim($this->sign_type))) {
            case 'RSA' :
                $is_sgin = $this->rsaVerify($prestr, trim($this->ali_public_key_path), $sign);
                break;
            case 'MD5':
                $is_sgin = $this->md5Verify($prestr, $sign, $this->key);
                break;
            default :
                $is_sgin = false;
        }

        return $is_sgin;
    }

    /**
     * 生成签名结果.
     *
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    protected function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $mysign = '';
        switch (strtoupper(trim($this->sign_type))) {
            case 'RSA' :
                $mysign = $this->rsaSign($prestr, $this->private_key_path);
                break;
            case 'MD5':
                $mysign = $this->md5Sign($prestr, $this->key);
                break;
            default :
                $mysign = '';
        }

        return $mysign;
    }

    /**
     * 生成要请求给支付宝的参数数组.
     *
     *
     * @return 要请求的参数数组
     */
    protected function buildRequestPara()
    {

        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($this->getParameter());

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->sign_type));

        return $para_sort;
    }

    /**
     * 生成要请求给支付宝的参数数组.
     *
     *
     * @return 要请求的参数数组字符串
     */
    protected function buildRequestParaToString()
    {
        //待请求参数数组
        $para = $this->buildRequestPara();

        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = $this->createLinkstringUrlencode($para);

        return $request_data;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）.
     *
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     *
     * @return 提交表单HTML文本
     */
    public function buildRequestForm($method, $button_name)
    {
        //待请求参数数组
        $para = $this->buildRequestPara();

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->alipay_gateway_new."' method='".$method."'>";
        while (list($key, $val) = each($para)) {
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";

        $sHtml = $sHtml."<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果.
     *
     *
     * @return 支付宝处理结果
     */
    public function buildRequestHttp()
    {
        $sResult = '';

        //待请求参数数组字符串
        $request_data = $this->buildRequestPara($this->getParameter());

        //远程获取数据
        $sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->cacert, $request_data, trim(strtolower($this->input_charset)), true);

        return $sResult;
    }

    /**
     * 建立请求，以模拟远程HTTP的POST请求方式构造并获取支付宝的处理结果，带文件上传功能.
     *
     * @param $para_temp 请求参数数组
     * @param $file_para_name 文件类型的参数名
     * @param $file_name 文件完整绝对路径
     *
     * @return 支付宝返回处理结果
     */
    public function buildRequestHttpInFile($file_para_name, $file_name)
    {

        //待请求参数数组
        $para = $this->buildRequestPara($this->param);
        $para[$file_para_name] = '@'.$file_name;

        //远程获取数据
        $sResult = $this->getHttpResponsePOST($this->alipay_gateway_new, $this->cacert, $para, '', true);

        return $sResult;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串.
     */
    public function queryTimestamp()
    {
        $url = $this->alipay_gateway_new.'service=query_timestamp&partner='.trim(strtolower($this->partner)).'&_input_charset='.trim(strtolower($this->input_charset));
        $encrypt_key = '';

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName('encrypt_key');
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

    public function parseXml($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        if ($dom->getElementsByTagName('is_success')->item(0)->nodeValue != 'T') {
            throw new AlipayException($dom->getElementsByTagName('error')->item(0)->nodeValue);
        }

        return $dom;
    }
}
