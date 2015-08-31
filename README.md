# alipay
alipay api for laravel 5.1+

## 接口列表
使用前请确认已经申请了**支付宝对应产品** https://b.alipay.com/order/productSet.htm

service|接口名|是否整合|sdk下载地址|备注
---|---|---|---|---
create_direct_pay_by_user|即时到账收款|已整合|[下载](http://download.alipay.com/public/api/base/alipaydirect.zip)|
create_direct_pay_by_user|网银支付|未整合|[下载]( http://download.alipay.com/public/api/base/alipaydirect_bankpay_single.zip)|跟及时到账收款一致
create_partner_trade_by_buyer|担保交易收款|未整合|[下载](http://download.alipay.com/public/api/base/alipayescow.zip)|
mobile.securitypay.pay|移动支付|未整合|[下载](http://download.alipay.com/public/api/base/WS_MOBILE_PAY_SDK_BASE.zip)|
alipay.wap.create.direct.pay.by.user|手机网站支付|已整合|[下载](http://download.alipay.com/public/api/base/alipaywapdirect.zip)|
batch_trans_notify|批量付款到支付宝账户|未整合|[下载](http://download.alipay.com/public/api/base/alipaytranspay.zip)|
alipay.auth.authorize|快捷登录|未整合|[下载](http://download.alipay.com/public/api/base/alipayfastlogin.zip)|

## 接口说明

#### create_direct_pay_by_user (即时到账收款、网银支付)

#### create_partner_trade_by_buyer (担保交易收款)

#### mobile.securitypay.pay (移动支付)

#### alipay.wap.create.direct.pay.by.user (手机网站支付)

#### batch_trans_notify (批量付款到支付宝账户)

#### alipay.auth.authorize (快捷登录)

