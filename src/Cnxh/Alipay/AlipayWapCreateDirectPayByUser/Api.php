<?php

namespace Cnxh\Alipay\AlipayWapCreateDirectPayByUser;

use DOMDocument;
use Cnxh\Alipay\ApiBase;

class Api extends ApiBase
{
    // HTTPS形式消息验证地址
    protected $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    // HTTP形式消息验证地址
    protected $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

     // 支付宝网关地址（新）
    protected $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';

    protected $service = 'alipay.wap.create.direct.pay.by.user';

    protected $seller_id;

    protected $sign_type = 'MD5';

    protected $input_charset = 'UTF-8';

    protected $transport = 'https';

    protected $private_key_path;

    protected $ali_public_key_path;

    //支付类型 必填，不能修改
    protected $payment_type = '1';

    //服务器异步通知页面路径
    protected $notify_url;

    //页面跳转同步通知页面路径
    protected $return_url;

    //商户订单号 必填
    protected $out_trade_no;

    //订单名称 必填
    protected $subject;

    //付款金额 必填
    protected $total_fee;

    //商品展示地址 必填
    protected $show_url;

    //订单描述 选填
    protected $body;

    //超时时间 选填
    protected $it_b_pay;

    //钱包token 选填
    protected $extern_token;

    public function setSellerId($seller_id)
    {
        $this->seller_id = $seller_id;

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

    public function setNotifyUrl($notify_url)
    {
        $this->notify_url = $notify_url;

        return $this;
    }

    public function setReturnUrl($return_url)
    {
        $this->return_url = $return_url;

        return $this;
    }
    public function setOutTradeNo($out_trade_no)
    {
        $this->out_trade_no = $out_trade_no;

        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function setTotalFee($total_fee)
    {
        $this->total_fee = $total_fee;

        return $this;
    }

    public function setShowUrl($show_url)
    {
        $this->show_url = $show_url;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setItBPay($it_b_pay)
    {
        $this->it_b_pay = $it_b_pay;

        return $this;
    }

    public function setExternToken($extern_token)
    {
        $this->extern_token = $extern_token;

        return $this;
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息.
     *
     * @return 验证结果
     */
    public function verifyNotify()
    {
        if (empty($_POST)) {
            //判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_POST, $_POST['sign']);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_POST['notify_id'])) {
                $responseTxt = $this->getResponse($_POST['notify_id']);
            }

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match('/true$/i', $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 针对return_url验证消息是否是支付宝发出的合法消息.
     *
     * @return 验证结果
     */
    public function verifyReturn()
    {
        if (empty($_GET)) {
            //判断POST来的数组是否为空
            return false;
        } else {
            //生成签名结果
            $isSign = $this->getSignVeryfy($_GET, $_GET['sign']);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'true';
            if (!empty($_GET['notify_id'])) {
                $responseTxt = $this->getResponse($_GET['notify_id']);
            }

            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match('/true$/i', $responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
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

        $isSgin = false;
        switch (strtoupper(trim($this->sign_type))) {
            case 'RSA' :
                $isSgin = $this->rsaVerify($prestr, trim($this->ali_public_key_path), $sign);
                break;
            case 'MD5':
                $is_sgin = $this->md5Verify($prestr, $sign, $this->key);
                break;
            default :
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL.
     *
     * @param $notify_id 通知校验ID
     *
     * @return 服务器ATN结果
     *                            验证结果集：
     *                            invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空 
     *                            true 返回正确信息
     *                            false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    public function getResponse($notify_id)
    {
        $transport = strtolower(trim($this->transport));
        $partner = trim($this->partner);
        $veryfy_url = '';
        if ($transport == 'https') {
            $veryfy_url = $this->https_verify_url;
        } else {
            $veryfy_url = $this->http_verify_url;
        }
        $veryfy_url = $veryfy_url.'partner='.$partner.'&notify_id='.$notify_id;
        $responseTxt = $this->getHttpResponseGET($veryfy_url, $this->cacert);

        return $responseTxt;
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
    public function query_timestamp()
    {
        $url = $this->alipay_gateway_new.'service=query_timestamp&partner='.trim(strtolower($this->partner)).'&_input_charset='.trim(strtolower($this->input_charset));
        $encrypt_key = '';

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName('encrypt_key');
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

    private function getParameter()
    {
        return [
            'service' => $this->service,
            'partner' => $this->partner,
            'seller_id' => $this->seller_id,
            'payment_type' => $this->payment_type,
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'out_trade_no' => $this->out_trade_no,
            'subject' => $this->subject,
            'total_fee' => $this->total_fee,
            'show_url' => $this->show_url,
            'body' => $this->body,
            'it_b_pay' => $this->it_b_pay,
            'extern_token' => $this->extern_token,
            '_input_charset' => trim(strtolower($this->input_charset)),
        ];
    }
}
