<?php
// 活动模块
class ActivityAction extends CommonAction {
	public function checkPass(){
		//通过审核
		$where['activity_id']=$_GET['id'];
		$model=M("Activity");
		$data['isLock'] = '2';
		$result=$model->where($where)->save($data);
		if($result){
			$this->success('已经开启！');
		}else{
			$this->error("开启失败！");
		}
	}
	public function forbidden(){
		//禁止状态
		$where['activity_id']=$_GET['id'];
		$model=M("Activity");
		$data['isLock'] = '1';
		$result=$model->where($where)->save($data);
		if($result){
			$this->success('已经禁用！');
		}else{
			$this->error("禁用失败！");
		}
	}
}