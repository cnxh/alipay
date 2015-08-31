<?php

namespace Cnxh\Alipay\AlipayWapCreateDirectPayByUser;

use Cnxh\Alipay\ServiceBase;

class Service extends ServiceBase
{
    protected $service = 'alipay.wap.create.direct.pay.by.user';

    // HTTPS形式消息验证地址
    protected $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    // HTTP形式消息验证地址
    protected $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    // 商户 email
    protected $seller_id;

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
