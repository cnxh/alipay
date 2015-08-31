<?php

namespace Cnxh\Alipay\AlipayWapCreateDirectPayByUser;

use Cnxh\Alipay\ServiceBase;
use Cnxh\Alipay\Traits\NotifyReturnTrait;

class Service extends ServiceBase
{
    use NotifyReturnTrait;

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

    protected function getParameter()
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
