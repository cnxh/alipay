<?php

return [
    // 接口列表 https://b.alipay.com/order/techService.htm
    'apis' => [
        'alipay.wap.create.direct.pay.by.user',
        'create_direct_pay_by_user',
    ],

    // 通用配置
    'common' => [
        'partner' => env('ALIPAY_PARTNER', ''),
        'key' => env('ALIPAY_KEY', ''),
    ],

    // 手机网站支付 (WAP即时到账收款)
    'alipay.wap.create.direct.pay.by.user' => [
        'transport' => 'http',
        'seller_id' => env('ALIPAY_SELLER_ID', ''),
    ],

    // PC即时到账收款
    'create_direct_pay_by_user' => [
        'seller_email' => env('ALIPAY_SELLER_EMAIL', ''),
    ],

];
