<?php
/*前台首页*/
class IndexAction extends CommonAction{
    //首页
	public function index(){
		/* $this->assign('diary',D('Diary')->where('status=1')->order('add_time DESC')->limit(5)->select());
		$this->assign('slide',D('Photo')->where('status=1 AND tid=5')->select());//幻灯片调用ID
		$this->assign('video',D('Video')->where('status=1')->find(1));//视频调用ID
		 */
		//同校恋情
		$schoolwhere['islock']="2";
		$field=array('username,member_id,nationName,schoolName,age,height,cPoint,avatar,OnlineTF,loginCount');
		$schoolyard = M()->table("mxczhyk_member r")->join("mxczhyk_member_detail s on r.member_id=s.member_id")->where($schoolwhere)->field($field)->order('OnlineTF DESC,schoolName,nationName,loginCount DESC,cPoint DESC')->limit(10)->select();
	/* 	dump($schoolyard);
	 * exit();
	 */
		//牵手恋人
		$successwhere['status']="1";
		$succcess = M("Success_object")->where($successwhere)->order('create_time DESC')->limit(10)->select();
		$this->assign('schoolyard',$schoolyard);
		//广告
		$this->assign('ad_top',M('Advertisement')->where('type=1 AND available=1')->order('displayorder')->limit(4)->select());
		//爱情攻略
		$com['cid']=$this->getclass('爱情攻略','Article');
		$com['islock']="1";
		$this->assign('loveStrategy',M('Article')->where($com)->order('update_time DESC')->limit(6)->select());
		$this->display();
    }
    /*  
     *  public function index() {
        import('QRCode');
        $QRCode = new QRCode('',150);
        $img_url = $QRCode->getUrl("http://blog.51edm.org");
        echo '<img src="' . $img_url . '" />';
    }

    public function mail() {
        send_mail("281978297@qq.com", "李欧", "测试邮箱", "测试邮件是否能正常发送");
    }*/
	//站长日记
	public function diary(){
		$Diary = D("Diary"); 
		import("ORG.Util.Page");
		$count = $Diary->count(); 
		$Page = new Page($count,18);
		$show = $Page->show(); 
		$list = $Diary->order('add_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->seo('站长日记', C('SITE_KEYWORDS'), C('SITE_DESCRIPTION'), 0);
		$this->display();
	}
	//站内搜索
	public function search(){
		$data = $_POST['words'];
		$r = D('Article')->where("status=1 AND title LIKE '%$data%' OR content LIKE '%$data%'")->select();
		foreach($r as $val){
			$val['title']=str_replace($data,"<font color=red><b>$data</b></font>",$val['title']);
			$val['content']=str_replace($data,"<font color=red><b>$data</b></font>",$val['content']);
			$list[]=$this->changurl($val);
		}
		$this->assign('list',$list);
		$this->seo('搜索'.$data.'结果', C('SITE_KEYWORDS'), C('SITE_DESCRIPTION'), 0);
		$this->display();
	}
	
}