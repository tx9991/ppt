<?php
header('Content-type:text/html; Charset=utf-8');
/*** 请填写以下配置信息 ***/

$config['appId'] = '2019092867849771'; //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
$config['returnUrl'] = 'http://www.xxx.com/alipay/return.php';     //付款成功后的同步回调地址
$config['notifyUrl'] = 'http://www.xxx.com/alipay/notify.php';     //付款成功后的异步回调地址
$config['signType'] = 'RSA2';            //签名算法类型，支持RSA2和RSA，推荐使用RSA2
$config['rsaPrivateKey'] = 'MIIEpAIBAAKCAQEAgxECsvZLEHhpLoXT+zjfeHsR3459IBWhkCg2Cksb+b/NvPGyt0gmCFcJIzcnI6lSoIrI57kBlrJLVjoGCB0YcgXqJEjgWc9ks+E6k37KivXd5nCtMVb6T5tMm664gS1OTGF4NgIpJb7l05knjNtDWILx7HxiDtlvaifi+BZG0/+jer7u785KH94yj/M+A1YdkffPEnTNpGGQ128wBtvnQ3VYXXEO3Ji7LrYfpPF697St04BM7lRDODl8wHhjEHttFTZPrIklN2lAyeFrIhJuCHVB8ElbHpRVdOIKPx1XASnluV/22tuE+avt3Fav9cJ4dnYev3kfpXkg4jItHsVvhwIDAQABAoIBACvV4aA3Ta3Jh+w+aEKqp9sk1jp97o1vjqSnPkO9ETM7mjTqZYp1P/skGPNFO/rqY688G6ucrdJX+WhyaZgHGyjvn3rZQOfovFaJKs0v59AIIVo0L0jEPV2opDuheSYDIB/drZXqrRZpvXZCz/6FSwXsbbtZcnZi9Kd9haTR1oiKieIpbRp7EeDeEjRgtpSiMC574HaRCBRoG0HCvef7jIB5kK5iAPwY2yBnDtR4rCMMOBQDYrDmw4x2GcnfuOCtHouTJtG5nyGR6C/+5eqK1jAR2wxtG5w2ONzaY4L5oZ8Df621Xt4AlZ7+ZiBlldWDl55efElgw0TUZJc6wXSBwIkCgYEAvtYbZe+s/wea4+XTfE97RY7sRA0IQsjI8keR84a4colFs3OuI7+mErLaKpzebYLGPwpjiImk0frwhJakkyb1dPUB6/df4YHIUo7c7NBcwB/Ws7qKxvFwCRLYHGuF5HAxGtBdBo0mrnm++RfzZgPmtIELoAR15S2v/r6PhTcQePMCgYEAr9IjU+JDjnBQchsuZy71Q8IDgh3IB/BNL7D5p/cxUE+Nf2xIL/Y1lAq7ELqTcspKrMbmSVAT3jGNJo3a7YPF0VeaeWXOcoqf8NAUjbgYCM9tLHxDO4yxUGxTS2W43e1arlQQX/ouloZrSlgfnaxHjYKJWx8pn2HkDKRThSroVB0CgYAwvrfY3dOugN7Lf5T5l8PBDLTE3R9TD75kRGu/bJYIi4/GOrIsZ3OgKxcW82LNTi4dRCYQZjg3eoWdGG2JONZRvZN0FofjLidhDyTXSJGocXmCBxATqPAZITsaZ0yYASBJ3Rcl6UAMKQTi039Ue4KecKfrg7gphfYCd+JpAJuwiQKBgQCoHl1ddnt2lfq2QTtrusEXW0sTffqpbKPBuI3giEOFMaxxbU7RXbH5rxEUe/NFzhz9fOryqzl0OxlnBjmS52+eLxAsiKaRa/Bnye/9W0zQD9eqs99t76gU6J/09n2A59bP3t4RiB5IHJw26HDCydtZerpwgSnBl08Cr5aVPWQOGQKBgQC86VdBcwyqMq+kOQiAoA+23k+Xqh+jHCq28BFg5QX6M4p2uUDEZXoVD2eZFmP4jpfvAS0FMxNOIQvI/M7P0Q9VhoIIcomM816TlWdcNEO+C/UFHnPrxCouAwo6HeV/ZSDEGJvVWK6T78GdejKPya0UCLLr72pwW6LmZlVkjOrtdw==';        //商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
$config['charset'] = 'utf8';
/*** 配置结束 ***/
$aliPay = new AlipayService($config);

/*** 订单信息 ***/
$order['outTradeNo'] = uniqid();     //你自己的商品订单号，不能重复
$order['totalFee'] = 0.01;          //付款金额，单位:元
$order['orderName'] = '支付测试';    //订单标题
$sHtml = $aliPay->doPay($order);
echo $sHtml;

class AlipayService
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }


    /**
     * 发起订单
     * @return array
     */
    public function doPay($order)
    {
        //请求参数
        $requestConfigs = array(
            'out_trade_no' => $order['outTradeNo'],//订单号
            'product_code' => 'FAST_INSTANT_TRADE_PAY',//销售产品码，目前仅支持FAST_INSTANT_TRADE_PAY
            'total_amount' => $order['totalFee'], //单位 元
            'subject' => $order['orderName'],  //订单标题
        );
        $commonConfigs = array(
            //公共参数
            'app_id' => $this->config['appId'],
            'method' => 'alipay.trade.page.pay',             //接口名称
            'format' => 'JSON',
            'return_url' => $this->config['returnUrl'],
            'charset' => $this->config['charset'],
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->config['notifyUrl'],
            'biz_content' => json_encode($requestConfigs),
        );
        $commonConfigs["sign"] = $this->generateSign($commonConfigs, $commonConfigs['sign_type']);
        return $this->buildRequestForm($commonConfigs);
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @return 提交表单HTML文本
     */
    protected function buildRequestForm($para_temp)
    {

        $sHtml = "正在跳转至支付页面...<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=" . $this->config['charset'] . "' method='POST'>";
        foreach ($para_temp as $key => $val) {
            if (false === $this->checkEmpty($val)) {
                $val = str_replace("'", "&apos;", $val);
                $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
            }
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    public function generateSign($params, $signType = "RSA")
    {
        return $this->sign($this->getSignContent($params), $signType);
    }

    protected function sign($data, $signType = "RSA")
    {
        $priKey = $this->config['rsaPrivateKey'];
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .//在指定位置插入关键字
            "\n-----END RSA PRIVATE KEY-----";
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, version_compare(PHP_VERSION, '5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
        } else {
            openssl_sign($data, $sign, $res);
        }
        ///openssl_sign(字符串,&sign,$res);生成一个签名文件，$res密钥
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 拼接内容
     * @param $params
     * @return string
     */
    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->config['charset']);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = $this->config['charset'];
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }

    function dd($all)
    {
        echo '<pre>';
        var_dump($all);
        echo '</pre>';
        exit();
    }
}