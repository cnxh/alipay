<?php

namespace Cnxh\Alipay\CreateDirectPayByUser;

use Cnxh\Alipay\ServiceBase;

class Service extends ServiceBase
{
    protected $service = 'create_direct_pay_by_user';

    // HTTPS形式消息验证地址
    protected $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    // HTTP形式消息验证地址
    protected $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    //收款支付宝账号
    protected $seller_email;

    // 支付类型 必填，不能修改
    protected $payment_type = '1';

    // 服务器异步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数
    protected $notify_url;

    // 页面跳转同步通知页面路径 
    // 需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
    protected $return_url;

    // 商户订单号 商户网站订单系统中唯一订单号，必填
    protected $out_trade_no;

    // 订单名称 必填
    protected $subject;

    // 付款金额 必填
    protected $total_fee;

    // 订单描述
    protected $body;

    // 商品展示地址 需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
    protected $show_url;

    // 防钓鱼时间戳 若要使用请调用类文件submit中的query_timestamp函数
    protected $anti_phishing_key;

    // 客户端的IP地址
    protected $exter_invoke_ip;

    public function setSellerEmail($seller_email)
    {
        $this->seller_email = $seller_email;

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

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setShowUrl($show_url)
    {
        $this->show_url = $show_url;

        return $this;
    }

    public function setAntiPhishingKey($anti_phishing_key)
    {
        $this->anti_phishing_key = $anti_phishing_key;

        return $this;
    }

    public function setExterInvokeIp($exter_invoke_ip)
    {
        $this->exter_invoke_ip = $exter_invoke_ip;

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

    protected function getParameter()
    {
        return [
            'service' => $this->service,
            'partner' => $this->partner,
            'seller_email' => $this->seller_email,
            'payment_type' => $this->payment_type,
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'out_trade_no' => $this->out_trade_no,
            'subject' => $this->subject,
            'total_fee' => $this->total_fee,
            'body' => $this->body,
            'show_url' => $this->show_url,
            'anti_phishing_key' => $this->anti_phishing_key,
            'exter_invoke_ip' => $this->exter_invoke_ip,
            '_input_charset' => $this->input_charset,
        ];
    }
}
