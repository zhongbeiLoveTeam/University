<?php
/*同校恋情  */
class MemberAction extends  CommonAction{
	public function index(){
		$schoolwhere['islock']="2";
		$field=array('username,r.member_id,nationName,schoolName,age,height,cPoint,headphoto,OnlineTF,loginCount');
		$schoolyard = M()->table("mxczhyk_member r")->join("mxczhyk_member_detail s on r.member_id=s.member_id")->where($schoolwhere)->field($field)->order('OnlineTF DESC,loginCount DESC,cPoint DESC')->limit(20)->select();
		$count =   M()->table("mxczhyk_member r")->join("mxczhyk_member_detail s on r.member_id=s.member_id")->where($schoolwhere)->count();
		$page = new Page($count, 20);
		$showPage = $page->show();
		$this->assign("page", $showPage);
		$this->assign('schoolyard',$schoolyard);
		$this->display();
	}
	public function _before_detail(){
		//查看会员的全部信息
    	$M = D('Member');
    	$where['member_id']=(int)$_GET['id'];
    	$list   =  $M->relation(true)->where($where)->select();
    	/* dump($list);
    	exit(); */
    	$this->assign('info',$list);
	}
	//约会
	public function appointment(){
		$M = D('Member');//(逻辑有问题)
		$where['member_id']=(int)$_GET['id'];
		$list   =  $M->relation(true)->where($where)->field('member_id,headphoto,username,sightml,monologue')->select();
		$this->assign('appointment',$list);
		$this->display();
	}
	public function dateDeal(){
		//约会安排
		$arra=M('appointment');
		if (empty($_POST['id'])) {
			$this->error('约会安排必须填写啊，请重新填写吧！');
		}
		$data['from_name']=$_SESSION['USER_AUTH_KEY'];//发送约会的人
		$data['to_name']=(int)$_POST['id'];//被约会的人
		$data['arra']=$_POST['arra'];
		$data['create_time']=time();
		$result=$arra->add($data);
		if ($result) {
			$this->success('约会申请已经发出，请耐心等待！');
		}else{
			$this->error('约会申请出现错误，请重新发送！');
		}
		
	}
	public function appointAsk(){
		//约会其他要求
	}
}