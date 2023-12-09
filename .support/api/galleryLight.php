<?php
/* galleryLight
 * ~ light-gallery helper
 * authored by 9r3i
 * https://github.com/9r3i
 * started at october 3rd 2019
 */
class galleryLight{
  const version='1.0.4';
  const info='Helper for light-gallery.';
  private $dir=null;
  private $files=null;
  private $height=100;
  private $tdir=null;
  public $error=false;
  public function __construct(string $d='.'){
    /* prepare gallery directory */
    if(!is_dir($d)){
      $this->error='Invalid directory.';
      return false;
    }$d=str_replace('\\','/',$d);
    $d.=substr($d,-1)!='/'?'/':'';
    $this->dir=$d;
    /* prepare thumbnail directory */
    $this->tdir='./.thumbnail/';
    if(!is_dir($this->tdir)){
      @mkdir($this->tdir,0755,true);
    }
    /* return this object */
    return $this;
  }
  /* request handler */
  public function requestHandler(){
    /* content */
    $content='';
    /* request path */
    if(isset($_GET['gallery-path'])){
      $callback=isset($_GET['gallery-callback'])
        ?$_GET['gallery-callback']:'galeryCallback';
      $gl=new galleryLight($_GET['gallery-path']);
      header('Content-Type: text/javascript');
      $content="{$callback}([]);";
      if(!$gl->error){
        $gallery=$gl->prepareImages();
        $json=$gallery->getJSON();
        $content="{$callback}($json);";
      }
    }else{
      $content='Error: Invalid request.';
      header('Content-Type: text/plain');
    }
    /* return the response */
    header('Content-Length: '.strlen($content));
    exit($content);
  }
  /* convert scanned directory files to json */
  public function getJSON(){
    return @json_encode($this->getArray());
  }
  /* get scanned directory files */
  public function getArray(){
    return is_array($this->files)?$this->files:[];
  }
  /* prepare image files */
  public function prepareImages(){
    if(!is_string($this->dir)||!is_dir($this->dir)){
      return $this;
    }$d=$this->dir;
    $s=@array_diff(@scandir($d),['.','..']);
    $s=is_array($s)?$s:[];
    $out=[];$outd=[];
    foreach($s as $f){
      if(is_file($d.$f)
        &&is_readable($d.$f)
        &&preg_match('/\.(jpg|jpeg|png|gif)$/i',$f)){
        $tf=$this->tdir.md5_file($d.$f);
        if(!is_file($tf)){
          $thumb=$this->copyImage($d.$f,$tf);
        }else{
          $thumb=true;
        }
        if($thumb){
          $out[$f]=(object)[
            'file'=>$d.$f,
            'thumb'=>$tf,
          ];
        }
      }elseif(is_dir($d.$f)){
        $dImage=$this->getDirImage($d.$f);
        if(is_object($dImage)){
          $outd[$f]=$dImage;
        }
      }
    }ksort($outd);
    ksort($out);
    $this->files=array_merge(array_values($outd),array_values($out));
    return $this;
  }
  /* directory image */
  private function getDirImage(string $d){
    if(!is_dir($d)){return false;}
    $s=@array_diff(@scandir($d),['.','..']);
    if(!is_array($s)){return false;}
    $od=$d;
    $d.=substr($d,-1)!='/'?'/':'';
    natsort($s);
    $rf=(object)[
      'file'=>'.support/images/folder.png',
      'thumb'=>'.support/images/folder-100.png',
      'folder'=>$od,
    ];
    foreach($s as $f){
      if(is_file($d.$f)
        &&is_readable($d.$f)
        &&preg_match('/\.(jpg|jpeg|png|gif)$/i',$f)){
        $tf=$this->tdir.md5_file($d.$f);
        if(!is_file($tf)){
          $thumb=$this->copyImage($d.$f,$tf);
        }else{
          $thumb=true;
        }
        if($thumb){
          $rf=(object)[
            'file'=>$d.$f,
            'thumb'=>$tf,
            'folder'=>$od,
          ];break;
        }
      }
    }
    return $rf;
  }
  /* stand-alone private method
   * @type: jpg, jpeg, gif, png
   * @parameters:
   *   $f = string of source of image to copy;
   *   $r = string of result image save or target path;
   *   $w = int of result image width in pixel; default: 200
   *   $h = int of result image height in pixel; default: 100
   *   $c = bool of crop image; default: false
   * @return: string of image type (jpg|png|gif)
   */
  private function copyImage($f=null,$r=null,$w=400,$h=100,$c=false){
    if(is_string($f)&&is_file($f)){
      $i=@getimagesize($f);
      if(!$i){return false;}
      $t=isset($i['mime'])&&preg_match('/(jpeg|png|gif)$/',$i['mime'],$a)?$a[1]:false;
      switch($t){
        case 'gif':$d=@imagecreatefromgif($f);break;
        case 'png':$d=@imagecreatefrompng($f);break;
        case 'jpeg':$d=@imagecreatefromjpeg($f);break;
        default:$d=@imagecreatefromstring(@file_get_contents($f));
      }
      $i=@getimagesize($f);
      if(!$d){return false;}
      $w=is_int($w)?$w:100;
      $h=is_int($h)?$h:100;
      $nh=$i[1];$nw=$i[0];
      $x=0;$y=0;
      if($c){
        if($nw>=$w and $nh>=$h){
          $ratio=max($w/$nw,$h/$nh);
          $y=($nh-$h/$ratio)/2;
          $nh=$h/$ratio;
          $x=($nw-$w/$ratio)/2;
          $nw=$w/$ratio;
        }else{return false;}
      }else{
        if($nw>=$w or $nh>=$h){
          $ratio=min($w/$nw,$h/$nh);
          $w=$i[0]*$ratio;
          $h=$i[1]*$ratio;
        }else{return false;}
      }
      $n=imagecreatetruecolor($w,$h);
      if($t=="gif" or $t=="png"){
        imagecolortransparent($n,imagecolorallocatealpha($n,0,0,0,127));
        imagealphablending($n,false);
        imagesavealpha($n,true);
      }
      imagecopyresampled($n,$d,0,0,$x,$y,$w,$h,$nw,$nh);
      switch($t){
        case 'gif':@imagegif($n,$r);break;
        case 'png':@imagepng($n,$r);break;
        case 'jpeg':@imagejpeg($n,$r);break;
        default:@imagejpeg($n,$r);break;
      }return $t?($t=='jpeg'?'jpg':$t):'jpg';
    }return false;
  }
}


