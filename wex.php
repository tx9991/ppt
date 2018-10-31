<?php
/**
 * 小程序中的微信支付
 * Class wxPayment
 */

class wxPayment{

    private $key;

    private $mch_id;

    private $appid;

    private $trade_type;

    public function __construct($data)
    {
       $this->key=$data['key'];
       $this->mch_id=$data['mch_id'];
       $this->appid=$data['appid'];
        $this->trade_type=$data['trade_type'];
    }

    private $url="https://api.mch.weixin.qq.com/pay/unifiedorder";
    private $url2="https://api.mch.weixin.qq.com/pay/orderquery";
    /**
     * Sig加密
     * @param $params
     * @return string
     */
   private function MakeSign( $params){
//签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
//签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
//签名步骤三：MD5加密
        $string = md5($string);
//签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 组成加密的字符串
     * @param $params
     * @return string
     */
   private function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }

    /**
     * 加密
     * @param int $len
     * @return string
     */
   private function genRandomString($len = 32) {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
// 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    /**
     * 预下单
     * @param $data
     * @return array|bool|mix|mixed|stdClass|string
     */
   public function unifiedOrder($data){
        $body = $data['body'];//内容
        $out_trade_no = $data['out_trade_no'];//自定义单号
        $total_fee = $data['total_fee'];//订单金额
        $trade_type = $this->trade_type;//JSAPI---交易类型
        $nonce_str =$this->genRandomString();//随机数
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];//获取ip
        $params['appid'] =$this->appid;//appid
        $params['openid'] =$data['openid'];//用户的支付的openid
        $params['mch_id'] =$this->mch_id;//商户号
        $params['nonce_str'] =$nonce_str;
        $params['body'] = $body;
        $params['out_trade_no'] = $out_trade_no;
        $params['total_fee'] = $total_fee;
        $params['spbill_create_ip'] =$spbill_create_ip;
        $params['notify_url'] = $data['url'];//回调地址
        $params['trade_type'] = $trade_type;
//获取签名数据
        $sign =$this->MakeSign( $params,$this->key);
        $params['sign'] = $sign;
        $xml =$this->data_to_xml($params);
        $response =$this->postXmlCurl($xml, $this->url);
        if( !$response ){
            return false;
        }
        $result =$this->xml_to_data( $response );

        if( !empty($result['result_code']) && !empty($result['err_code']) ){
            $result['err_msg'] =$this->error_code( $result['err_code'] );
        }
        return $result;
    }

    /**
     * 加改xml
     * @param $xml
     * @return array|bool|mix|mixed|stdClass|string
     */
   private function xml_to_data($xml){
       if(!$xml){
           return false;
       }
//将XML转为array
//禁止引用外部xml实体
       libxml_disable_entity_loader(true);
       $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
       return $data;
   }

    /**
     * 修改为xml
     * @param $params
     * @return bool|string
     */
   private function data_to_xml($params){
       if(!is_array($params)|| count($params) <= 0)
       {
           return false;
       }
       $xml = "<xml>";
       foreach ($params as $key=>$val)
       {
           if (is_numeric($val)){
               $xml.="<".$key.">".$val."</".$key.">";
           }else{
               $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
           }
       }
       $xml.="</xml>";
       return $xml;
   }

    /**
     * post获取
     * @param $xml
     * @param $url
     * @param bool $useCert
     * @param int $second
     * @return bool|mixed
     */
   private function postXmlCurl($xml, $url, $useCert = false, $second = 30){
       $ch = curl_init();
//设置超时
       curl_setopt($ch, CURLOPT_TIMEOUT, $second);
       curl_setopt($ch,CURLOPT_URL, $url);
       curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
       curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
//设置header
       curl_setopt($ch, CURLOPT_HEADER, FALSE);
//要求结果为字符串且输出到屏幕上
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       if($useCert == true){
//设置证书
//使用证书：cert 与 key 分别属于两个.pem文件
           curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
           curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
       }
//post提交方式
       curl_setopt($ch, CURLOPT_POST, TRUE);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//运行curl
       $data = curl_exec($ch);
//返回结果
       if($data){
           curl_close($ch);
           return $data;
       } else {
           $error = curl_errno($ch);
           curl_close($ch);
           return false;
       }
   }

    /**
     * 返回参数判断
     * @param $code
     * @return mixed
     */
   private function error_code( $code ){
        $errList = array(
            'NOAUTH' => '商户未开通此接口权限',
            'NOTENOUGH' => '用户帐号余额不足',
            'ORDERNOTEXIST' => '订单号不存在',
            'ORDERPAID' => '商户订单已支付，无需重复操作',
            'ORDERCLOSED' => '当前订单已关闭，无法支付',
            'SYSTEMERROR' => '系统错误!系统超时',
            'APPID_NOT_EXIST' => '参数中缺少APPID',
            'MCHID_NOT_EXIST' => '参数中缺少MCHID',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS' => '缺少必要的请求参数',
            'OUT_TRADE_NO_USED' => '同一笔交易不能多次提交',
            'SIGNERROR' => '参数签名结果不正确',
            'XML_FORMAT_ERROR' => 'XML格式错误',
            'REQUIRE_POST_METHOD' => '未使用post传递参数 ',
            'POST_DATA_EMPTY' => 'post数据不能为空',
            'NOT_UTF8' => '未使用指定编码格式',
        );
        if( array_key_exists( $code , $errList ) ){
            return $errList[$code];
        }
    }

    /**
     * 返回出下单参数
     * @param $sign
     * @return mixed
     */
   public function place($sign){
       $data['appId'] = $this->appid;
       $data['timeStamp'] = time();
       $data['nonceStr'] = $this->genRandomString();
       $data['package'] =$sign;
       $data['signType'] = 'MD5';
       $data['paySign']=$this->MakeSign($data);
       unset($data['appId']);
       return $data;
   }

    /**
     * 支付单查询
     * @param $number
     * @return array|bool|mix|mixed|stdClass|string
     */
   public function getpay($number){
       $data['appId'] = $this->appid;
       $data['mch_id'] =$this->mch_id;
       $data['out_trade_no']=$number;
       $data['nonce_str']=$this->genRandomString();
       $sign=$this->MakeSign($data);
       $data['sign']=$sign;
       $xml =$this->data_to_xml($data);
       $response =$this->postXmlCurl($xml, $this->url2);
       if( !$response ){
           return false;
       }
       $result =$this->xml_to_data( $response );

       if( !empty($result['result_code']) && !empty($result['err_code']) ){
           $result['err_msg'] =$this->error_code( $result['err_code'] );
       }
       return $result;
   }
}

$arr['key']='12345678';
$arr['mch_id']='12345678';
$arr['appid']='12345678';
$arr['trade_type']='JSAPI';
$dsb=new wxPayment($arr);
$data['body']='产品描述';
$data['out_trade_no']='1101101101101';
$data['total_fee']=100;
$data['openid']='1xxx212131x21312xqe123qew';
$data['url']='xxxxxxxxxxxlxxxxxx';
$cont=$dsb->unifiedOrder($data);
$package="prepay_id=".$cont['prepay_id'];
$dsb->place($package);
$number="1231231321323122312";
$dsb->getpay($number);