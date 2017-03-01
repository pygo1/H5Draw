<?php
/**
 * 默认接口服务类
 *
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */

class Api_Default extends PhalApi_Api {

	public function getRules() {
        return array(
            'index' => array(),
            'saveimg' => array(
                'img' => array('name' => 'img')
            ),
            'saveimg1' => array(
                'img' => array('name' => 'img'),
                'pixel' => array('name' => 'pixel','type' => 'array')
            ),
            'editimg' =>array(
                'x1' => array('name' => 'x1'),
                'y1' => array('name' => 'y1'),
                'w' => array('name' => 'w'),
                'h' => array('name' => 'h'),
                'source' => array('name' => 'source')
            ),
            'testPost' => array(
                'id' => array('name' => 'id','type' =>'int','require' =>true)
            ),
        );
	}
	
	/**
	 * 默认接口服务
     * * @desc 用于获取服务名称 GET
	 * @return string info 服务名称
	 * @return string version 版本，格式：X.X.X
	 * @return int time 当前时间戳
	 */
	public function index() {
        return array(
            'info' => 'HPWebService',
            'version' => PHALAPI_VERSION,
            'time' => $_SERVER['REQUEST_TIME'],
        );
	}
    public function saveimg(){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $this->img, $result)){
            $type = $result[2];
            $date = date('Ymd',time());
            $new_file = "D:/PhalApi/Public/fileMg/userfiles/draw/".$date."/";
            if(!file_exists($new_file))
            {
//检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file,0700,true);
            }
            $time = time();
            $new_file = $new_file.$time.".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $this->img)))){
                return array(
                    'imgUrl' => "http://192.168.0.110:82/fileMg/userfiles/draw/".$date.'/'.$time.".{$type}",
                    'time' => $_SERVER['REQUEST_TIME'],
                );
            }else{
                echo '新文件保存失败';
            }
        }else {
            return array();
        }
    }
    public function saveimg1(){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $this->img, $result)){
            $type = $result[2];
            $date = date('Ymd',time());
            $_new_file = "D:/PhalApi/Public/fileMg/userfiles/draw/".$date."/";
            if(!file_exists($_new_file))
            {
//检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($_new_file,0700,true);
            }
            $time = time();
            $new_file = $_new_file.'_'.$time.".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $this->img)))){
                $PhalApi_Image = new Image_Lite();
                $PhalApi_Image->open($new_file);
                $PhalApi_Image->crop(500, 500, $this->pixel[0], $this->pixel[1])->save($new_file);

                return array(
                    'x1' => $this->pixel[0],
                    'y1' => $this->pixel[1]
                );
            }else{
                echo '新文件保存失败';
            }
        }
    }
    public function editimg(){
        $url = parse_url($this->source);
        $time = time();
        $type = strtolower(trim(substr(strrchr($url['path'], '.'), 1)));
        $date = date('Ymd',time());
        $new_file = "D:/PhalApi/Public/fileMg/userfiles/draw/".$date."/";
        $fileName = $new_file.'_'.$time.".{$type}";

        $PhalApi_Image = new Image_Lite();
        $PhalApi_Image->open('..'.$url['path']);
        $PhalApi_Image->crop($this->w, $this->h, $this->x1, $this->y1)->save($fileName);

        return array(
            'x1' => $this->x1,
            'y1' => $this->y1,
            'w' => $this->w,
            'h' => $this->h,
            'source' => '..'.$url['path']
        );
    }
    /**
     * 测试POST
     * * @desc 测试POST服务
     * @return string id id
     * @return int time 当前时间戳
     */
    public function testPost() {
        $PhalApi_Image = new Image_Lite();
        $PhalApi_Image->open('./1.png');
        $size   = $PhalApi_Image->size();
        return array(
            'id' => $this->id,
            'size' => $size,
            'time' => $_SERVER['REQUEST_TIME'],
        );
    }
}
