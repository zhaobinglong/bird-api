<?php

class img{

  private $appId;
  // public  $log;

  public function __construct($appId, ) {
    $this->appId = $appId;
  }

  // 返回图片信息

  // 合并图片
  public function mergeImg($openid){

    $QR = "../img/".$openid.".jpg"; //二维码
    $bk = '../img/share.jpg'; //背景图片

    $QR = imagecreatefromstring ( file_get_contents ( $QR ) );   //open picture source
    $bk = imagecreatefromstring ( file_get_contents ( $bk ) ); //open picture source
    $QR_width=430;
    $QR_height=430;
    imagecopyresampled ( $bk, $QR,200,600,0,0,355,355,$QR_width, $QR_height ); // mixed picture
    $result_png = $openid . ".png"; // file name
    $file = '../img/' . $result_png;
    $res = imagepng ( $bk, $file );//输出最终的图片
    if($res){
       $this->log->success('图片合并成功，本次openid='.$openid);
    }else{
       $this->log->error('图片合并失败，本次openid='.$openid);
    }
  }

}


 ?>
