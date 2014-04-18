<?php

class CommonAction extends Action {

    public $loginMarked;

    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $systemConfig = include WEB_ROOT . 'Common/systemConfig.php';
        if (empty($systemConfig['TOKEN']['admin_marked'])) {
            $systemConfig['TOKEN']['admin_marked'] = "QQ群：102577846";
           // $systemConfig['TOKEN']['admin_timeout'] = 3600;设置不操作时间推出
            $systemConfig['TOKEN']['member_marked'] = "http://blog.51edm.org";
            $systemConfig['TOKEN']['member_timeout'] = 3600;
            F("systemConfig", $systemConfig, WEB_ROOT . "Common/");
            /* if (is_dir(WEB_ROOT . "install/")) {
                delDirAndFile(WEB_ROOT . "install/", TRUE);
            }  成功安装后删除安装文件*/
           
            
        }
		
        $this->loginMarked = md5($systemConfig['TOKEN']['admin_marked']);
        $this->checkLogin();

	   // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('ORG.Util.RBAC');
            if (!RBAC::AccessDecision()) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    //跳转到认证网关
                    redirect(C('USER_AUTH_GATEWAY'));
//                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
//                     echo L('_VALID_ACCESS_');
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
		
        $this->assign("menu", $this->show_menu());
        $this->assign("sub_menu", $this->show_sub_menu());
        $this->assign("my_info", $_SESSION['my_info']);
        $this->assign("site", $systemConfig);

       // $this->getQRCode(); 
    }
	
    /* protected function getQRCode($url = NULL) {
        if (IS_POST) {
            $this->assign("QRcodeUrl", "");
        } else {
//            $url = empty($url) ? C('WEB_ROOT') . $_SERVER['REQUEST_URI'] : $url;
            $url = empty($url) ? C('WEB_ROOT') . U(MODULE_NAME . '/' . ACTION_NAME) : $url;
            import('QRCode');
            $QRCode = new QRCode('', 80);
            $QRCodeUrl = $QRCode->getUrl($url);
            $this->assign("QRcodeUrl", $QRCodeUrl);
        }
    } */
    public function checkLogin() {
        if (isset($_COOKIE[$this->loginMarked])) {
            $cookie = explode("_", $_COOKIE[$this->loginMarked]);
            $timeout = C("TOKEN");
            if (time() > (end($cookie) + $timeout['admin_timeout'])) {
                setcookie("$this->loginMarked", NULL, -3600, "/");
                unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                $this->error("登录超时，请重新登录", U("Public/index"));
            } else {
                if ($cookie[0] == $_SESSION[$this->loginMarked]) {
                    setcookie("$this->loginMarked", $cookie[0] . "_" . time(), 0, "/");
                } else {
                    setcookie("$this->loginMarked", NULL, -3600, "/");
                    unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                    $this->error("帐号异常，请重新登录", U("Public/index"));
                }
            }
        } else {
            $this->redirect("Public/index");
        }
        return TRUE;
    }

    /**
      +----------------------------------------------------------
     * 验证token信息
      +----------------------------------------------------------
     */
    protected function checkToken() {
        if (IS_POST) {
            if (!M("Admin")->autoCheckToken($_POST)) {
                die(json_encode(array('status' => 0, 'info' => '令牌验证失败')));
            }
            unset($_POST[C("TOKEN_NAME")]);
        }
    }

    /**
      +----------------------------------------------------------
     * 显示一级菜单
      +----------------------------------------------------------
     */
    private function show_menu() {
        $cache = C('admin_big_menu');
        $count = count($cache);
        $i = 1;
        $menu = "";
        foreach ($cache as $url => $name) {
            if ($i == 1) {
                $css = $url == MODULE_NAME || !$cache[MODULE_NAME] ? "fisrt_current" : "fisrt";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            } else if ($i == $count) {
                $css = $url == MODULE_NAME ? "end_current" : "end";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            } else {
                $css = $url == MODULE_NAME ? "current" : "";
                $menu.='<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            }
            $i++;
        }
        return $menu;
    }

    /**
      +----------------------------------------------------------
     * 显示二级菜单
      +----------------------------------------------------------
     */
    private function show_sub_menu() {
        $big = MODULE_NAME == "Index" ? "Common" : MODULE_NAME;
        $cache = C('admin_sub_menu');
        $sub_menu = array();
        if ($cache[$big]) {
            $cache = $cache[$big];
            foreach ($cache as $url => $title) {
                $url = $big == "Common" ? $url : "$big/$url";
                $sub_menu[] = array('url' => U("$url"), 'title' => $title);
            }
            return $sub_menu;
        } else {
            return $sub_menu[] = array('url' => '#', 'title' => "该菜单组不存在");
        }
    }
    
    
    public function index() {
    	$name=$this->getActionName();
    	$M = M($name);
    	$count = $M->count();
    	import("ORG.Util.Page");       //载入分页类
    	$page = new Page($count, 20);
    	$showPage = $page->show();
    	$this->assign("page", $showPage);
    	$this->assign("list", D($name)->listNews($page->firstRow, $page->listRows));
    	$this->display();
    }
    public function search(){
    	//搜索会员信息
    	$M = D('Member');
    	// 构造查询条件
    	$condition['schoolName'] = $_POST['schoolName'];
    	$condition['gender'] = $_POST['gender'];
    	 
    	$condition['schoolName'] = $_POST['schoolName'];
    	$condition['schoolName'] = $_POST['schoolName'];
    	 
    	 
    	 
    	 
    	 
    	 
    	 
    	 
    
    	 
    	 
    	/*
    	 * $Dao = M("User");
    
    	// 计算总数
    	* $condition['email'] = array('like',"%".$_GET['email']."%");
    	$count = $Dao->where($condition)->count();
    	// 导入分页类
    	import("ORG.Util.Page");
    	// 实例化分页类
    	$p = new Page($count, 10);
    	// 获取查询参数
    	$map['status'] = $_GET['status'];
    	$map['email'] = $_GET['email'];
    	foreach($map as $key=>$val) {
    	$p->parameter .= "$key=".urlencode($val)."&";
    	}
    	// 分页显示输出
    	$page = $p->show();
    
    	// 当前页数据查询
    	$list = $Dao->where($condition)->order('uid ASC')->limit($p->firstRow.','.$p->listRows)->select();
    
    	// 赋值赋值
    	$this->assign('page', $page);
    	$this->assign('list', $list);
    
    	$this->display(); */
    	$condition = array();
    	 
    	$condition['name'] = !empty($_GET['name']) ?  trim($_GET['name']) : '';
    	 
    	$condition['star'] = isset($_GET['star']) ?   $_GET['star'] : 0;
    	 
    	$condition['address'] = !empty($_GET['address']) ?  trim($_GET['address']) : '';
    	 
    	$condition['is_recomm'] = isset($_GET['is_recomm']) ?   $_GET['is_recomm'] : 0;
    	 
    	//组合条件
    	 
    	$wherelist = array();
    	 
    	if(!empty($condition['name'])){
    		 
    		$wherelist[] = "name like '%{$condition['name']}%'";
    		 
    	}
    	 
    	if(($condition['star'])!=''){
    		 
    		$wherelist[] = "star = '{$condition['star']}'";
    		 
    	}
    	 
    	if(!empty($condition['address'])){
    		 
    		$wherelist[] = "content like '%{$condition['address']}%'";
    		 
    	}
    	 
    	if($condition['is_recomm'] != ''){
    		 
    		$wherelist[] = "is_recomm = '{$condition['is_recomm']}'";
    		 
    	}
    	 
    	//组装存在的查询条件
    	 
    	if(count($wherelist) > 0){
    		 
    		$where = " where ".implode(' AND ' , $wherelist);
    		 
    	}
    	 
    	$where = isset($where) ? $where : '';
    	 
    	//echo $where;
    	 
    	$result = $mysqli->query("SELECT * FROM `hotel_basic` {$where}");
    	 
    	$total = $result->num_rows;
    	 
    	$page = new page($total,10);
    	 
    	//传入控制条件变量
    	 
    	if(!empty($condition)){
    		 
    		foreach($condition as $key=>$val) {
    			 
    			$page->parameter .= "$key=".urlencode($val)."&";
    			 
    		}
    		 
    	}
    	 
    	//$limit = 'limit ('{$page->firstRow}','{$page->listRows}' ) ';
    	 
    	//注意limit的写法
    	 
    	$limit = "limit ".$page->firstRow.",{$page->listRows}";
    	 
    	$result = $mysqli->query("SELECT * FROM `hotel_basic` {$where} ORDER BY id {$limit}");
    	 
    	// echo $limit;
    }
    public function category() {
    	$name=$this->getActionName();
    	if (IS_POST) {
    		echo json_encode(D($name)->category());
    	} else {
    		$this->assign("list", D($name)->category());
    		$this->display();
    	}
    }
    
    public function add() {
    	$name=$this->getActionName();
    	if (IS_POST) {
    		//$this->checkToken();
    		$data=D($name)->upload();
    		echo json_encode(D($name)->addNews($data));
    	} else {
    		$this->assign("list", D($name)->category());
    		$this->display();
    	}
    }
    
    public function checkNewsTitle() {
    	$name=$this->getActionName();
    	$M = M($name);
    	$where = "title='" . $this->_get('title') . "'";
    	if (!empty($_GET['id'])) {
    		$where.=" And id !=" . (int) $_GET['id'];
    	}
    	if ($M->where($where)->count() > 0) {
    		echo json_encode(array("status" => 0, "info" => "已经存在，请修改标题"));
    	} else {
    		echo json_encode(array("status" => 1, "info" => "可以使用"));
    	}
    }
    
    public function edit() {
    	$name=$this->getActionName();
    	$M = M($name);
    	if (IS_POST) {
    		//$this->checkToken();
    		$data=D($name)->upload();
    		echo json_encode(D($name)->edit($data));
    	} else {
    		$info = $M->where("id=" . (int) $_GET['id'])->find();
    		if ($info['id'] == '') {
    			$this->error("不存在该记录");
    		}
    		$this->assign("info", $info);
    	    $this->assign("list", D($name)->category());
    		$this->display("add");
    	}
    }
    
    public function del() {
    	$name=$this->getActionName();
    	if (M($name)->where("id=".(int) $_GET['id'])->delete()) {
    		//D($name)->foreverdel();
    		$this->success("成功删除");
    		echo json_encode(array("status"=>1,"info"=>""));
    	} else {
    		$this->error("删除失败，可能是不存在该ID的记录");
    	}
    }
    
    
 
}