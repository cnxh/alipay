<?php

// 支付宝配置文件 (cnxh/alipay)

return [
    // 使用的支付宝产品 
    'services' => [
        'alipay.wap.create.direct.pay.by.user',
        'create_direct_pay_by_user',
        'account.page.query',
    ],

    // 通用配置
    'common' => [
        'partner' => env('ALIPAY_PARTNER', ''),
        'key' => env('ALIPAY_KEY', ''),
        'transport' => 'http', // 访问模式,根据自己的服务器是否支持ssl访问，选择https或http
    ],

    // 手机网站支付 (WAP即时到账收款)
    'alipay.wap.create.direct.pay.by.user' => [
        'seller_id' => env('ALIPAY_SELLER_ID', ''),
    ],

    // PC即时到账收款
    'create_direct_pay_by_user' => [
        'seller_email' => env('ALIPAY_SELLER_EMAIL', ''),
    ],

    // 账务明细分页查询接口（对账）
    'account.page.query' => [
        'page_size' => 1000,
    ],

];
