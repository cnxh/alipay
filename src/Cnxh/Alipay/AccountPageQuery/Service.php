<?php

namespace Cnxh\Alipay\AccountPageQuery;

use Cnxh\Alipay\ServiceBase;

class Service extends ServiceBase
{
    // 接口名称
    protected $service = 'account.page.query';

    // 查询页号
    protected $page_no = 1;

    // 账务查询开始时间 
    protected $gmt_start_time;

    // 账务查询结束时间
    protected $gmt_end_time;

    // 交易收款账户
    protected $logon_id;

    // 账务流水号
    protected $iw_account_log_id;

    // 业务流水号
    protected $trade_no;

    // 商户订单号
    protected $merchant_out_order_no;

    // 充值网银流水号
    protected $deposit_bank_no;

    // 分页大小
    protected $page_size;

    // 交易类型代码
    protected $trans_code;

    public function setPageNo($page_no)
    {
        $this->page_no = $page_no;

        return $this;
    }

    public function setGmtStartTime($gmt_start_time)
    {
        $this->gmt_start_time = $gmt_start_time;

        return $this;
    }

    public function setGmtEndTime($gmt_end_time)
    {
        $this->gmt_end_time = $gmt_end_time;

        return $this;
    }

    public function setLogonId($logon_id)
    {
        $this->logon_id = $logon_id;

        return $this;
    }

    public function setIwAccountLogId($iw_account_log_id)
    {
        $this->iw_account_log_id = $iw_account_log_id;

        return $this;
    }

    public function setTradeNo($trade_no)
    {
        $this->trade_no = $trade_no;

        return $this;
    }

    public function setMerchantOutOrderNo($merchant_out_order_no)
    {
        $this->merchant_out_order_no = $merchant_out_order_no;

        return $this;
    }

    public function setDepositBankNo($deposit_bank_no)
    {
        $this->deposit_bank_no = $deposit_bank_no;

        return $this;
    }

    public function setPageSize($page_size)
    {
        $this->page_size = $page_size;

        return $this;
    }

    public function setTransCode($trans_code)
    {
        $this->trans_code = $trans_code;

        return $this;
    }

    public function query()
    {
        return $this->buildRequestHttp($this->getParameter());
    }

    protected function getParameter()
    {
        return [
            'service' => $this->service,
            'partner' => $this->partner,
            'page_no' => $this->page_no,
            'gmt_start_time' => $this->gmt_start_time,
            'gmt_end_time' => $this->gmt_end_time,
            'logon_id' => $this->logon_id,
            'iw_account_log_id' => $this->iw_account_log_id,
            'trade_no' => $this->trade_no,
            'merchant_out_order_no' => $this->merchant_out_order_no,
            'deposit_bank_no' => $this->deposit_bank_no,
            'page_size' => $this->page_size,
            'trans_code' => $this->trans_code,
            '_input_charset' => trim($this->input_charset),
        ];
    }
}
