<?php

// 通过函数类
class functions{
    public $id;
    public $secret;

    // 在这里定义微信公众号的id和secret
    // 注意  只有服务号的才可以
    public function __construct(){
      $this->id='wx796a066fb7470da2';
      $this->secret='96182f682b215e63164564aaff0eb449';
    }

    // 模拟https的get请求
    public function http_get($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
      curl_setopt($ch, CURLOPT_TIMEOUT, 15);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      $res = curl_exec($ch);
      curl_close($ch);
      return $res;
    }

    // 网页授权获取用户信息
    // 在授权url里面定义好成功后的跳转地址
    // 在新的地址文件里里面new一个对象，调用这个方法
    public function getInfo($code){
      // 授权成功，拿到url里面的code,通过code换取token
      $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->id.'&secret='.$this->secret.'&code='.$code.'&grant_type=authorization_code';
      $res=json_decode($this->http_get($url));
      //拿到token,通过token换取包含用户信息json字符串
      $url='https://api.weixin.qq.com/sns/userinfo?access_token='.$res->access_token.'&openid='.$res->openid.'&lang=zh_CN';
      $user=$this->http_get($url);
      return $user;
    }

    // 保存二维码到本地服务器上
    public function download($url){
      $s = file_get_contents($url);
      $path = '/a.jpg';  //文件路径和文件名
      file_put_contents($path, $s);
    }

}
?>
