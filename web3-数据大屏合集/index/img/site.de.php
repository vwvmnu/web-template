<?php
//decode by http://www.yunlu99.com/
/**
 * we7_bybook模块微站定义
 *
 * @author hu182838
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class We7_bybookModuleSite extends WeModuleSite
{
    public $table_yangben = "we7_yangben";
    public $table_yangben_share = "we7_yangben_share";
    public $table_yangben_content = "we7_yangben_content";
    public $table_yangben_contents = "we7_yangben_contents";
    public $table_yangben_setting = "we7_yangben_setting";
    public $table_yangben_zuji = "we7_yangben_zuji";
    public $table_yangben_url = "we7_yangben_url";
    public $table_yangben_ip = "we7_yangben_ip";
    public $table_yangben_customer = "we7_yangben_custmer";
    public $table_yangben_fans = "we7_yangben_fans";
    public $table_yangben_related_fans = "we7_yangben_related_fans";

    public $table_yangben_video_category = "we7_yangben_video_category";
    public $table_yangben_video = "we7_yangben_video";
    public $table_yangben_video_comment = "we7_yangben_video_comment";
    public $table_yangben_video_reply = "we7_yangben_video_reply";

    public $table_yangben_category = "we7_yangben_category";
    public $table_yangben_source = "we7_yangben_source";
	public $table_yangben_params = "we7_yangben_params";

    public $table_yangben_mingpian = "we7_yangben_mingpian";
    public $table_yangben_mingpian_category = "we7_yangben_mingpian_category";
    public $table_yangben_mingpian_img = "we7_yangben_mingpian_img";

    public $table_yangben_article_category = "we7_yangben_article_category";
    public $table_yangben_article = "we7_yangben_article";
    public $table_yangben_article_img = "we7_yangben_article_img";
    public $table_yangben_article_comment = "we7_yangben_article_comment";
    public $table_yangben_article_reply = "we7_yangben_article_reply";

    public $table_yangben_company = "we7_yangben_company";
    public $table_yangben_company_user = "we7_yangben_company_user";

    //加密key
    private $_lock = "Yang";
    //公众号后台默认登录密码
    private $_default_pwd = '123456';
    private $_fans = null;

    public function doWebYangben(){

    }

    public function __construct()
    {
        //设置粉丝
        $this->_fans = $this->setUserFans();
        $this->repairBug();
    }

    /**
     * bug自动修复
     */
    public function repairBug()
    {
        //1.修复单买小程序样本详情页不存在
        $path = IA_ROOT . '/addons/we7_bybook/template';
        $source = $path . "/mobile_";
        $dest = $path . '/mobile';
        if (!file_exists($dest)) {
            rename($source, $dest);
        }
    }

    /**
     * 设置公众号浏览记录
     * @return array|bool|mixed
     */
    public function setUserFans()
    {
        global $_W, $_GPC;
        //var_dump($_W['wx_userid']);exit();
        if (isset($_GPC['flag']) && $_GPC['flag'] == 1) {
            //var_dump(1);exit();
            return array();
        }
		$s = pdo_fetch('select `value` from ' . tablename($this->table_yangben_setting) . ' where uniacid=:uniacid and item="is_doauth"', array(':uniacid' => $_W['uniacid']));
		//var_dump($s);exit();
		if($s['value'] == 2){
			return array();
		}
        $user = pdo_get($this->table_yangben_fans, array('openid' => $_SESSION['openid']));
		
		//var_dump($_W['siteroot']);
		//var_dump($_SESSION['openid']);
		//exit;
		
        if (!$user && $_SESSION['openid']) {
            $res = mc_oauth_userinfo($_W['uniacid']);
            $user = array(
                'uniacid' => $_W['uniacid'],
                'password' => md5($this->_default_pwd),
                'nickname' => $res['nickname'],
                'openid' => $res['openid'],
                'avatar' => $res['avatar'],
                'nationality' => $res['country'],
                'resideprovince' => $res['province'],
                'residecity' => $res['city'],
                'gender' => $res['sex'],
                'addtime' => time(),
            );
            pdo_insert($this->table_yangben_fans, $user);
            $fans_id = pdo_insertid();
            $user['id'] = $fans_id;
        }
        return $user;
    }

    //=====================================微擎后台管理=================================

    private function getCompanyList()
    {
        global $_W, $_GPC;
        return pdo_getall($this->table_yangben_company, array('is_display' => 1, 'uniacid' => $_W['uniacid']));
    }

    /**
     * 样本管理
     */
    public function doWebYb_manager()
    {
        //这个操作被定义用来呈现 管理中心导航菜单
        global $_W, $_GPC;

        $ops = array('edit', 'delete', 'display', 'related', 'pdf', 'contents', 'delete_contents', 'qrcode', 'designer');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' y.uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND (y.`title` like :keyword or c.cat_name like :keyword)';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben) . " as y left join " . tablename($this->table_yangben_category) . " as c on c.id = y.category_id where " . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = 'SELECT y.*, c.cat_name FROM ' . tablename($this->table_yangben) . " as y left join " . tablename($this->table_yangben_category) . " as c on c.id = y.category_id WHERE {$where} ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
            $yb = pdo_fetchall($sql, $params, 'id');

            include $this->template('yangben');
        } elseif ($op == 'edit') {

            $id = intval($_GPC['id']);
            $type = $_W['account']['type'];
            if (!empty($id)) {
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);
                if (empty($yb)) {
                    message('未找到指定的样本.', $this->createWebUrl('yb_manager'));
                }
            }
            if (checksubmit()) {
                $data = $_GPC['yb']; // 获取打包值
                $cat = pdo_get($this->table_yangben_category, array('id' => $data['category_id']), array('company_id'));
                $data['company_id'] = $cat['company_id'];
                empty($data['title']) && message('请填写样本名称');
                empty($data['thumb']) && message('请上传样本封面');
                $data['updatetime'] = TIMESTAMP;
                if (empty($yb)) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben, $data);

                    if (!empty($ret)) {
                        $id = pdo_insertid();
                    }
                } else {
                    $ret = pdo_update($this->table_yangben, $data, array('id' => $id));
                    //pdo_debug(true);
                }

                if (!empty($ret)) {
                    message('样本保存成功', $this->createWebUrl('yb_manager', array('op' => 'display')), 'success');
                } else {
                    message('样本保存失败');
                }
            }
            $company = $this->getCompanyList();
            //获取分类
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            include $this->template('add_yangben');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定样本');
            }
            $result = pdo_delete($this->table_yangben, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                pdo_delete($this->table_yangben_content, array('yangben_id' => $id));
                message('删除样本成功.', $this->createWebUrl('yb_manager'), 'success');
            } else {
                message('删除样本失败.');
            }
        } elseif ($op == 'related') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定样本');
            } else {
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);

                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id ORDER BY `sort` asc, id asc";
                $page_list = pdo_fetchall($sql, array(':yangben_id' => $id), 'id');
            }

            if ($_POST) {
                pdo_delete($this->table_yangben_content, array("yangben_id" => $id));
                $sort = $_GPC['sort'];
                $content = $_GPC['content'];
                $level = $_GPC['level'];
                $name = $_GPC['title'];
                for ($i = 0; $i < count($content); $i++) {
                    $data = array(
                        'yangben_id' => $id,
                        'name' => $name[$i],
                        'sort' => $sort[$i],
                        'level' => $level[$i],
                        'content' => $content[$i]
                    );
                    pdo_insert($this->table_yangben_content, $data);
                }
                message('添加样本内容页成功.', $this->createWebUrl('yb_manager', array('op' => 'display')), 'success');
            }
			$is_edit = 1;
			if($this->checkTemplatePlugin(IA_ROOT."/addons/we7_bybook_plugin_edit/lock.txt") == true) {
				$is_edit = 1;
			}
            include $this->template('related_yangben');
        } elseif ($op == 'pdf') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定样本');
            } else {
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id ORDER BY `sort`, `id` asc";
                $page_list = pdo_fetchall($sql, array(':yangben_id' => $id), 'id');
            }
            if (checksubmit()) {
                pdo_delete($this->table_yangben_content, array("yangben_id" => $id));
                $sort = $_GPC['sort'];
                $content = $_GPC['content'];
                $level = $_GPC['level'];
                $name = $_GPC['title'];
                for ($i = 0; $i < count($content); $i++) {
                    $data = array(
                        'yangben_id' => $id,
                        'name' => $name[$i],
                        'sort' => $sort[$i],
                        'level' => $level[$i],
                        'content' => $content[$i]
                    );
                    pdo_insert($this->table_yangben_content, $data);
                }
                message('添加样本内容页成功.', $this->createWebUrl('yb_manager', array('op' => 'display')), 'success');
            }
            include $this->template('pdf_yangben');
        } elseif ($op == 'contents') {
            $id = $_GPC['id'];
            $contents_id = $_GPC['contents_id'];
            if (checksubmit()) {
                $data = $_GPC['data'];
                $data['yangben_id'] = $id;
                if (!$_GPC['edit_id']) {
                    $data['addtime'] = TIMESTAMP;
                    pdo_insert($this->table_yangben_contents, $data);
                } else {
                    pdo_update($this->table_yangben_contents, $data, array('id' => $_GPC['edit_id']));
                }
                message('', $this->createWebUrl('yb_manager', array('op' => 'contents', 'id' => $id)), 'success');
            }
            if ($contents_id) {
                //获取目录内容
                $info = pdo_get($this->table_yangben_contents, array('id' => $contents_id));
            }
            //获取目录
            $datas = pdo_getall($this->table_yangben_contents, array('yangben_id' => $id), array(), '', array('page asc'));
            $contents = $this->getContentsCategory($datas);
            include $this->template('yangben_contents');
        } elseif ($op == 'delete_contents') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定目录');
            }
            //判断是否有子级
            $child = pdo_get($this->table_yangben_contents, array('parent_id' => $id));
            if ($child) {
                message('存在子级，不能删除', $this->createWebUrl('yb_manager', array('op' => 'contents', 'id' => $_GPC['yangben_id'])), 'error');
            } else {
                pdo_delete($this->table_yangben_contents, array('id' => $id));
                message('', $this->createWebUrl('yb_manager', array('op' => 'contents', 'id' => $_GPC['yangben_id'])), 'success');
            }
        }elseif ($op == 'qrcode'){
            header('Content-type: image/jpg');
            if ($_GPC['is_show_ad'] == 0){
                if ($_W['account']['type'] == 4){
                    echo $this->getWeixinQrcode('we7_bybook/pages/book/index', 'id='.$_GPC['id']);
                }else{
                    $this->getMobileQrcode('yb_show', array('id' => $_GPC['id']));
                }
            }else{
                if ($_W['account']['type'] == 4){
                    echo $this->getWeixinQrcode('we7_bybook/pages/book/ad', 'id='.$_GPC['id']);
                }else{
                    $this->getMobileQrcode('yb_ad', array('id' => $_GPC['id']));
                }
            }
        }elseif($op == 'designer') {
			$id = $_GPC['id'];
			if($_W['isajax']){
				if($this->checkTemplatePlugin(IA_ROOT."/addons/we7_bybook_plugin_edit/lock.txt") == false) {
					//die(json_encode(array('code' => 0, 'msg' => '您还没有安装该插件')));
				}
				
				$md5 = $_GPC['js_md5'];
				$params = htmlspecialchars_decode($_GPC['params']);
				$p = pdo_get($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $md5));
				if($p){
					pdo_update($this->table_yangben_params, array('params' => $params), array('id' => $p['id']));
				}else{
					pdo_insert($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $md5, 'params' => $params));
				}
				die(json_encode(array('code' => 1, 'msg' => '页面参数配置成功')));
			}
			$src = $_GPC['src'];
			$js_md5 = md5($src);
			//获取参数
            $params = pdo_get($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $js_md5));
			$obj_rect = '[]';
            if($params) {
				$obj_rect = $params['params'];
            }
			include $this->template('yangben_designer');
		}
    }

    /**
     * 生成手机端二维码
     * @param $do
     * @param $data
     */
    private function getMobileQrcode($do, $data)
    {
        global $_W;
        if ($_W['ishttps']) {
            $server_request_scheme = "https";
        } else {
            $server_request_scheme = "http";
        }
        //访问 url
        $url = $server_request_scheme . '://' . $_SERVER['HTTP_HOST'] . '/app/' . $this->createMobileUrl($do, $data);
        load()->library('qrcode');
        QRcode::png($url, false, QR_ECLEVEL_L, 5, 1);
    }

    /**
     * 获取无限极分类
     * @param $data
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    private function getContentsCategory($data, $parent_id = 0, $level = 0)
    {
        static $tree = array();
        foreach ($data as $k => $v) {
            if ($v["parent_id"] == $parent_id) {
                $v["level"] = $level;
                $tree[] = $v;
                $this->getContentsCategory($data, $v["id"], $level + 1);
            }
        }
        return $tree;
    }

    /**
     * 获取样本内容
     * @date: 2020-10-6 下午2:11:59
     * @author: Mr.Yang
     * @param: variable
     * @return: json
     */
    public function doWebGet_page()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['yangben_id']);
        if (empty($id)) {
            //message('未找到指定样本');
            die(json_encode(array('code' => -1, 'msg' => '未找到指定样本')));
        } else {
            $page = pdo_getall($this->table_yangben_content, array('yangben_id' => $id), '*', 'id', array('sort asc', 'id asc'));
            $data = array();
            foreach ($page as $k => $v) {
                $v['content'] = tomedia($v['content']);
                $data[] = $v;
            }
            die(json_encode(array('code' => 0, 'data' => $data)));
        }
    }

    /**
     * 浏览管理
     */
    public function doWebYb_user()
    {
        global $_W, $_GPC;

        //1、普通接入公众号2、易信3、授权接入公众号4、正常接入小程序5、正常接入PC
        $type = $_W['account']['type'];

        $ops = array('edit', 'display', 'mini', 'related', 'add', 'delete', 'company', 'add_company', 'delete_company');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        $pager = "";
        $user = array();
        //0的自行处理就可以   1为男 2为女
        if ($op == 'display') {

            if ($type == 1 || $type == 3) {
                $pageindex = max(intval($_GPC['page']), 1); // 当前页码
                $pagesize = 10; // 设置分页大小

                $where = ' WHERE uniacid=:uniacid';
                $params = array(
                    ':uniacid' => $_W['uniacid']
                );
                if (!empty($_GPC['keyword'])) {
                    $where .= ' AND `nickname` like :keyword';
                    $params[':keyword'] = "%{$_GPC['keyword']}%";
                }

                $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_fans) . $where;
                $total = pdo_fetchcolumn($sql, $params);
                //分页
                $pager = pagination($total, $pageindex, $pagesize);

                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_fans) . " {$where} ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
                $user = pdo_fetchall($sql, $params, 'id');
                include $this->template('yangben_user');
            } elseif ($type == 4) {
                $pageindex = max(intval($_GPC['page']), 1); // 当前页码
                $pagesize = 10; // 设置分页大小

                $where = ' WHERE uniacid=:uniacid and avatar != ""';
                $where = ' WHERE uniacid=:uniacid ';
                $params = array(
                    ':uniacid' => $_W['uniacid']
                );

                if (!empty($_GPC['keyword'])) {
                    $where .= ' AND `nickname` like :keyword';
                    $params[':keyword'] = "%{$_GPC['keyword']}%";
                }

                $sql = 'SELECT COUNT(*) FROM ' . tablename('mc_members') . $where;
                $total = pdo_fetchcolumn($sql, $params);
                //分页
                $pager = pagination($total, $pageindex, $pagesize);

                $sql = 'SELECT * FROM ' . tablename('mc_members') . " {$where} ORDER BY `uid` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;

                $user = pdo_fetchall($sql, $params, 'id');

                foreach ($user as $k => $v) {
                    $source = pdo_fetch("select s.*, m.nickname from " . tablename($this->table_yangben_source) . " as s left join " . tablename("mc_members") . " as m on m.uid = s.to_user_id where to_user_id=:to_user_id", array(
                        ':to_user_id' => $v['uid']
                    ));
                    if ($source['from_user_id']) {
                        $from_user = pdo_get("mc_members", array('uid' => $source['from_user_id']), array('nickname'));
                        $msg = $from_user['nickname'] . "的" . $source['type'];
                    } else {
                        $msg = $source['type'];
                    }
                    $user[$k]['source'] = $msg;
                    $user[$k]['nickname'] = $v['nickname'] ? $v['nickname'] : '未完善';
                }

                include $this->template('yangben_user_mini');
            }

        } elseif ($op == 'edit') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定用户');
            }
            if (checksubmit()) {
                $user = $_GPC['user'];
                $data = array(
                    'mobile' => $user['mobile']
                );
                if ($user['password']) {
                    $data['password'] = md5(trim($user['password']));
                }
                pdo_update($this->table_yangben_fans, $data, array('id' => $id));
                message('修改浏览用户信息成功.', $this->createWebUrl('yb_user', array('op' => 'display')), 'success');
            }
            $user = pdo_get($this->table_yangben_fans, array('id' => $id));
            include $this->template('yangben_user_edit');
        } elseif ($op == 'mini') {
            if ($type == 4) {
                $pageindex = max(intval($_GPC['page']), 1); // 当前页码
                $pagesize = 10; // 设置分页大小

                $where = ' WHERE uniacid=:uniacid';
                $params = array(
                    ':uniacid' => $_W['uniacid']
                );

                if (!empty($_GPC['keyword'])) {
                    $where .= ' AND `nickname` like :keyword';
                    $params[':keyword'] = "%{$_GPC['keyword']}%";
                }

                $sql = 'SELECT COUNT(*) FROM ' . tablename('mc_members') . $where;
                $total = pdo_fetchcolumn($sql, $params);
                //分页
                $pager = pagination($total, $pageindex, $pagesize);

                $sql = 'SELECT * FROM ' . tablename('mc_members') . " {$where} ORDER BY `uid` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
                $user = pdo_fetchall($sql, $params, 'id');
            }

            include $this->template('yangben_user_mini');
        } elseif ($op == 'related') {
            //获取该用户的样本
            $id = intval($_GPC['id']);
            $user_yb = pdo_fetchall("select y.*,rf.id as rid from " . tablename($this->table_yangben_related_fans) . " as rf left join " . tablename($this->table_yangben) . " as y on y.id = rf.yangben_id where rf.fans_id=:fans_id and rf.uniacid=:uniacid", array(':fans_id' => $id, ':uniacid' => $_W['uniacid']));
            $yangben_ids = array();
            foreach ($user_yb as $k => $v) {
                $yangben_ids[] = $v['id'];
            }

            //获取所有的样本
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );

            if ($yangben_ids) {
                $where .= ' and `id` not in(' . implode(',', $yangben_ids) . ')';
            }

            if (!empty($_GPC['keyword'])) {
                $where .= ' AND `title` like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = 'SELECT * FROM ' . tablename($this->table_yangben) . " {$where} ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
            $yb = pdo_fetchall($sql, $params, 'id');

            include $this->template('yangben_related_user');
        } elseif ($op == 'company') {
            $id = intval($_GPC['id']);
            $user_yb = pdo_fetch("select u.company_id, c.*, u.id from " . tablename($this->table_yangben_company_user) . " as u left join " . tablename($this->table_yangben_company) . " as c on c.id = u.company_id where u.user_id=:user_id and u.uniacid=:uniacid", array(':user_id' => $id, ':uniacid' => $_W['uniacid']));

            //获取所有的公司
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid and is_display=1 ';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );

            if ($user_yb) {
                $where .= ' and `id` not in(' . $user_yb['company_id'] . ')';
            }

            if (!empty($_GPC['keyword'])) {
                $where .= ' AND `company_name` like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_company) . $where;

            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = 'SELECT * FROM ' . tablename($this->table_yangben_company) . " {$where} ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
            $yb = pdo_fetchall($sql, $params, 'id');
            include $this->template('yangben_related_company');
        } elseif ($op == 'add_company') {
            $fans_id = $_GPC['fans_id'];

            $company_id = $_GPC['company_id'];
            $condition = array('user_id' => $fans_id, 'company_id' => $company_id, 'uniacid' => $_W['uniacid']);
            $relation = pdo_get($this->table_yangben_company_user, $condition);
            if ($relation) {
                $msg = '关联公司成功.';
            } else {
                $result = pdo_insert($this->table_yangben_company_user, $condition);
                if ($result) {
                    $msg = '关联公司成功.';
                } else {
                    $msg = '关联公司失败.';
                }
            }
            message($msg, $this->createWebUrl('yb_user', array('op' => 'company', 'id' => $fans_id)), 'success');
        } elseif ($op == 'delete_company') {
            $fans_id = $_GPC['fans_id'];
            $id = $_GPC['id'];
            pdo_delete($this->table_yangben_company_user, array('id' => $id));
            message('取消关联公司成功.', $this->createWebUrl('yb_user', array('op' => 'company', 'id' => $fans_id)), 'success');
        } elseif ($op == 'add') {
            $fans_id = $_GPC['fans_id'];
            $yangben_id = $_GPC['yangben_id'];
            $condition = array('fans_id' => $fans_id, 'yangben_id' => $yangben_id, 'uniacid' => $_W['uniacid']);
            $relation = pdo_get($this->table_yangben_related_fans, $condition);
            if ($relation) {
                $msg = '关联样本成功.';
            } else {
                $result = pdo_insert($this->table_yangben_related_fans, $condition);
                if ($result) {
                    $msg = '关联样本成功.';
                } else {
                    $msg = '关联样本失败.';
                }
            }
            message($msg, $this->createWebUrl('yb_user', array('op' => 'related', 'id' => $fans_id)), 'success');
        } elseif ($op == 'delete') {
            $fans_id = $_GPC['fans_id'];
            $id = $_GPC['id'];
            pdo_delete($this->table_yangben_related_fans, array('id' => $id));
            message('取消关联样本成功.', $this->createWebUrl('yb_user', array('op' => 'related', 'id' => $fans_id)), 'success');
        }
    }

    /**
     * 获取性别
     * @param $gender
     * @return mixed
     */
    public function getGender($gender)
    {
        $list = array('未知', '男', '女');
        return $list[$gender];
    }

    /**
     * 检测是否安装样本模板插件
     * @return bool
     */
    public function checkTemplatePlugin($plugin_more_path = IA_ROOT . '/addons/we7_bybook_plugin_more/lock.txt')
    {
        global $_W;
        if (file_exists($plugin_more_path)) {
            $content = file_get_contents($plugin_more_path);
            $content_array = explode('_', base64_decode($content));
            if ($content_array[1] == $this->_lock && $content_array[2] == $_W['siteroot']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 配置管理
     */
    public function doWebYb_setting()
    {
        global $_W, $_GPC;
        $ops = array('weixin', 'display', 'seo', 'syn', 'template', 'tabbar', 'wxtempl', 'webtempl');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';

        $type = $_W['account']['type'];

        $data = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        $setting = array();
        foreach ($data as $k => $v) {
            $setting[$v['item']] = $v['value'];
        }

        $is_template = 1;
        //判断是否已经安装多模板插件
        if ($this->checkTemplatePlugin()) {
            $is_template = 1;
        }
        if ($op == 'display') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                $set_setting['banner'] = implode(',', $set_setting['banner']);
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'basic'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'basic','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting'), 'success');
            }
            include $this->template('yangben_setting');
        } elseif ($op == 'weixin') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'weixin'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'weixin','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'weixin')), 'success');
            }
            include $this->template('yangben_setting_wx');
        } elseif ($op == 'seo') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'seo'));
                //提交配置seo信息
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'seo','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'seo')), 'success');
            }
            include $this->template('yangben_setting_seo');
        } elseif ($op == 'syn') {
            if (checksubmit()) {
                $wx_uniacid = $_GPC['setting']['uniacid'];
                if (empty($wx_uniacid)) {
                    message('请选择微信公众号平台.', $this->createWebUrl('yb_setting', array('op' => 'syn')), 'error');
                }
                //删除当前分类
                $mini_cat = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where uniacid=:uniacid", array(":uniacid" => $_W['uniacid']));
                foreach ($mini_cat as $k => $v) {
                    pdo_delete($this->table_yangben_category, array('id' => $v['id']));
                }
                //插入新分类
                $new_cat = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where uniacid=:uniacid", array(":uniacid" => $wx_uniacid));
                $catid_array = array();
                foreach ($new_cat as $k => $v) {
                    $catid = $v['id'];
                    unset($v['id']);
                    $v['uniacid'] = $_W['uniacid'];
                    pdo_insert($this->table_yangben_category, $v);
                    $new_catid = pdo_insertid();
                    $catid_array[$catid] = $new_catid;
                }
                //删除当前的所有样本
                $mini_yb = pdo_fetchall("select * from " . tablename($this->table_yangben) . " where uniacid=:uniacid", array(":uniacid" => $_W['uniacid']));
                foreach ($mini_yb as $k => $v) {
                    $this->deleteMiniBook($v['id']);
                }
                //插入新样本数据
                $yb = pdo_fetchall("select * from " . tablename($this->table_yangben) . " where uniacid=:uniacid", array(":uniacid" => $wx_uniacid));
                foreach ($yb as $k => $v) {
                    $old_yangben_id = $v['id'];
                    unset($v['id']);
                    $v['uniacid'] = $_W['uniacid'];
                    $v['category_id'] = $catid_array[$v['category_id']] ? $catid_array[$v['category_id']] : 0;
                    pdo_insert($this->table_yangben, $v);
                    $yangben_id = pdo_insertid();
                    if ($yangben_id) {
                        $yb_content = pdo_fetchall("select * from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id", array(':yangben_id' => $old_yangben_id));
                        foreach ($yb_content as $key => $value) {
                            unset($value['id']);
                            $value['yangben_id'] = $yangben_id;
                            pdo_insert($this->table_yangben_content, $value);
                        }
                    }
                }
                message('同步公众号样本信息成功.', $this->createWebUrl('yb_setting', array('op' => 'syn')), 'success');
            }
            $platform = pdo_fetchall("select w.name, w.uniacid from " . tablename("account") . " as a left join " . tablename("account_wechats") . " as w on w.uniacid=a.uniacid where a.isdeleted=0 and (`type`=1 or `type`=3) order by a.acid desc");
            include $this->template('yangben_setting_syn');
        } elseif ($op == 'template') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                if (($set_setting['template'] == 2 && $this->checkTemplatePlugin() == false) || ($set_setting['template'] == 3 && $this->checkTemplatePlugin(IA_ROOT . '/addons/we7_bybook_plugin_temp2/lock.txt') == false)) {
                    message('您还没有安装该模板插件.', $this->createWebUrl('yb_setting', array('op' => 'template')), 'error');
                }
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'template'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'template','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'template')), 'success');
            }
            $template_id = $setting['template'] ? $setting['template'] : 1;
            //var_dump($this->checkTemplatePlugin(IA_ROOT . '/addons/we7_bybook_plugin_temp2/lock.txt'));exit();
            $html = '<img onclick="selectTemplete(this,1)" class="'.($template_id==1?'template_active':'').'" src="/addons/we7_bybook/template/image/temp1.png" width="120" alt="" style="margin-right: 30px;">';
            if ($this->checkTemplatePlugin()) {
                $html .= '<img onclick="selectTemplete(this,2)" class="'.($template_id==2?'template_active':'').'" src="/addons/we7_bybook/template/image/temp2.png" width="120" alt="" style="margin-right: 30px;">';
            }
            if ($this->checkTemplatePlugin(IA_ROOT . '/addons/we7_bybook_plugin_temp2/lock.txt')) {
                $html .= '<img onclick="selectTemplete(this,3)" class="'.($template_id==3?'template_active':'').'" src="/addons/we7_bybook/template/image/temp3.png" width="120" alt="" style="margin-right: 30px;">';
            }
            $html .= '<img onclick="selectTemplete(this,4)" class="'.($template_id==4?'template_active':'').'" src="/addons/we7_bybook/template/image/temp_web_2.png" width="120" alt="" style="margin-right: 30px;">';
            include $this->template('yangben_setting_template');
        } elseif ($op == 'tabbar') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                //var_dump($set_setting);exit();
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'tabbar'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'tabbar','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'tabbar')), 'success');
            }
            include $this->template('yangben_setting_tabbar');
        } elseif ($op == 'wxtempl') {
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                //var_dump($set_setting);exit();
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'wxtempl'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'wxtempl','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'wxtempl')), 'success');
            }
            $platform = pdo_fetchall("select w.name, w.uniacid from " . tablename("account") . " as a left join " . tablename("account_wechats") . " as w on w.uniacid=a.uniacid where a.isdeleted=0 and (`type`=1 or `type`=3) order by a.acid desc");
            include $this->template('yangben_setting_wxtempl');
        }elseif ($op == 'webtempl') {
            //免费模板设置
            if (checksubmit()) {
                $set_setting = $_GPC['setting'];
                pdo_delete($this->table_yangben_setting, array("uniacid" => $_W['uniacid'], 'type' => 'webtempl'));
                foreach ($set_setting as $k => $v) {
                    $sql = "insert into " . tablename($this->table_yangben_setting) . " (uniacid, `type`,item, `value`) values ({$_W['uniacid']}, 'webtempl','{$k}', '{$v}')";
                    pdo_query($sql);
                }
                message('修改配置成功.', $this->createWebUrl('yb_setting', array('op' => 'webtempl')), 'success');
            }
            $template_id = $setting['web_template'] ? $setting['web_template'] : 1;
            include $this->template('yangben_setting_webtemplate');
        }
    }


    /**
     * 获取小程序足迹二维码
     */
    public function doWebFoot_qrcode()
    {
        header('Content-type: image/jpg');
        echo $this->getWeixinQrcode('we7_bybook/pages/foot/foot', 1);
    }

    public function doWebMingpian_qrcode()
    {
        header('Content-type: image/jpg');
        echo $this->getWeixinQrcode('we7_bybook/pages/list/index', 1);
    }

    /**
     * 删除样本
     * @param $yb_id
     */
    public function deleteMiniBook($yb_id)
    {
        pdo_delete($this->table_yangben, array('id' => $yb_id));
        pdo_delete($this->table_yangben_content, array('yangben_id' => $yb_id));
    }

    /**
     * 视频分类
     */
    public function doWebYb_video_category()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND cat_name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_video_category) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_video_category) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            foreach ($list as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_video) . " where category_id=:category_id and uniacid=:uniacid", array(
                    ':category_id' => $v['id'],
                    ':uniacid' => $_W['uniacid']
                ));
                $list[$k]['count'] = $count;

                $company = pdo_get($this->table_yangben_company, array('id' => $v['company_id']), array('company_name'));
                $list[$k]['company_name'] = $company ? $company['company_name'] : '';
            }
            include $this->template('yangben_video_category');
        } elseif ($op == 'edit') {
            $cate_id = $_GPC['id'];
            $company = $this->getCompanyList();
            if (!empty($cate_id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_video_category) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $cate_id, ':uniacid' => $_W['uniacid']);
                $category = pdo_fetch($sql, $params);
                if (empty($category)) {
                    message('未找到指定的视频分类.', $this->createWebUrl('yb_video_category'), 'error');
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                if (!$cate_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_video_category, $data);
                    $cate_id = pdo_insertid();
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_update($this->table_yangben_video_category, $data, array('id' => $cate_id));
                }
                if (!empty($ret)) {
                    //修改样本关联的公司id
                    pdo_update($this->table_yangben_video, array('company_id' => $data['company_id']), array('uniacid' => $_W['uniacid'], 'category_id' => $cate_id));
                    message('分类保存成功', $this->createWebUrl('yb_video_category', array('op' => 'display')), 'success');
                } else {
                    message('分类保存失败');
                }
            }
            include $this->template('yangben_video_category_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定分类');
            }
            $result = pdo_delete($this->table_yangben_video_category, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除分类成功.', $this->createWebUrl('yb_video_category'), 'success');
            } else {
                message('删除分类失败.');
            }
        }
    }

    /**
     * 视频管理
     */
    public function doWebYb_video()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display', 'comment', 'delete_comment', 'comment_reply', 'show_comment', 'show_reply', 'delete_reply');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' v.uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND v.title like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            if (!empty($_GPC['yangben_id'])) {
                $where .= " and v.yangben_id=:yangben_id";
                $params[':yangben_id'] = $_GPC['yangben_id'];
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_video) . ' as v where ' . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = "SELECT v.*, c.content, c.name, y.title as ytitle, vc.cat_name FROM " . tablename($this->table_yangben_video) . " as v left join " . tablename($this->table_yangben_content) . " as c on c.id=v.page_id left join " . tablename($this->table_yangben) . " as y on y.id = v.yangben_id left join " . tablename($this->table_yangben_video_category) . " as vc on vc.id=v.category_id where $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $video = pdo_fetchall($sql, $params, 'id');
            $yb = pdo_getall($this->table_yangben, array("uniacid" => $_W['uniacid'], 'is_display' => 1));

            include $this->template('yangben_video');
        } elseif ($op == 'edit') {
            $company = $this->getCompanyList();
            $video_id = $_GPC['id'];
            if (!empty($video_id)) {
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_video) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $video_id, ':uniacid' => $_W['uniacid']);
                $video = pdo_fetch($sql, $params);
                if (empty($video)) {
                    message('未找到指定的视频.', $this->createWebUrl('yb_video'));
                }
            }
            if (checksubmit()) {
                $data = $_GPC['video'];
                //获取公司id
                $cat = pdo_get($this->table_yangben_video_category, array('id' => $data['category_id']), array('company_id'));
                $data['company_id'] = $cat['company_id'];
                empty($data['title']) && message('请填写视频名称');
                $data['updatetime'] = TIMESTAMP;
                if ($data['type'] == 1) {
                    $data['yangben_id'] = $data['article_id'];
                }
                unset($data['article_id']);
                if (!$video_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_video, $data);
                } else {
                    //var_dump($data);exit();
                    $ret = pdo_update($this->table_yangben_video, $data, array('id' => $video_id));
                }

                if (!empty($ret)) {
                    message('视频保存成功', $this->createWebUrl('yb_video', array('op' => 'display')), 'success');
                } else {
                    message('视频保存失败');
                }
            }
            $yb = pdo_getall($this->table_yangben, array("uniacid" => $_W['uniacid'], 'is_display' => 1));
            $article = pdo_getall($this->table_yangben_article, array('uniacid' => $_W['uniacid'], 'is_display' => 1), array('id', 'title'), '', array('id desc'));
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_video_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            include $this->template('yangben_video_edit');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定视频');
            }
            $result = pdo_delete($this->table_yangben_video, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除视频成功.', $this->createWebUrl('yb_video'), 'success');
            } else {
                message('删除视频失败.');
            }
        }elseif ($op == 'comment')
        {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定视频');
            }
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小
            $where = ' v.video_id='.$id;
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_video_comment) . ' as v where ' . $where;
            $total = pdo_fetchcolumn($sql, $params);

            pdo_update($this->table_yangben_video,  array('comment_num' => $total), array('id' => $id));

            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = "SELECT v.*, m.nickname, m.avatar FROM " . tablename($this->table_yangben_video_comment) . " as v left join " . tablename("mc_members") . "as m on m.uid = v.user_id where $where ORDER BY v.id desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $video_comment = pdo_fetchall($sql, $params, 'id');
            foreach ($video_comment as $k => $v) {
                $sql = "SELECT COUNT(*) FROM " . tablename($this->table_yangben_video_reply) . " where comment_id={$v['id']}";
                $total = pdo_fetchcolumn($sql, array());
                $video_comment[$k]['reply_num'] = $total;
            }
            include $this->template('yangben_video_comment');
        }elseif ($op == 'delete_comment')
        {
            $id = intval($_GPC['id']);
            $comment_id = intval($_GPC['comment_id']);
            if (empty($comment_id)) {
                message('未找到指定评论');
            }
            $result = pdo_delete($this->table_yangben_video_comment, array('id' => $comment_id));
            if (intval($result) == 1) {
                pdo_delete($this->table_yangben_video_reply, array('comment_id' => $comment_id));
                message('', $this->createWebUrl('yb_video', array('op' => 'comment', 'id' => $id)), 'success');
            } else {
                message('删除评论失败.');
            }
        }elseif ($op == 'show_comment') {
            $id = intval($_GPC['id']);
            $is_check = intval($_GPC['is_check']);
            $comment_id = intval($_GPC['comment_id']);
            $result = pdo_update($this->table_yangben_video_comment, array('is_check' => $is_check), array('id' => $comment_id));
            if (intval($result) == 1) {
                message('', $this->createWebUrl('yb_video', array('op' => 'comment', 'id' => $id)), 'success');
            }else{
                message('设置评论失败.');
            }
        } elseif ($op == 'comment_reply') {
            $id = intval($_GPC['id']);
            $comment_id = intval($_GPC['comment_id']);
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小
            $where = ' v.comment_id='.$comment_id;
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_video_reply) . ' as v where ' . $where;
            $total = pdo_fetchcolumn($sql, $params);

            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = "SELECT v.*, m.nickname, m.avatar FROM " . tablename($this->table_yangben_video_reply) . " as v left join " . tablename("mc_members") . "as m on m.uid = v.from_user_id where $where ORDER BY v.id desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $video_reply = pdo_fetchall($sql, $params, 'id');
            include $this->template('yangben_video_reply');
        }elseif ($op=='show_reply'){
            $id = intval($_GPC['id']);
            $is_check = intval($_GPC['is_check']);
            $comment_id = intval($_GPC['comment_id']);
            $reply_id = intval($_GPC['reply_id']);
            $result = pdo_update($this->table_yangben_video_reply, array('is_check' => $is_check), array('id' => $reply_id));
            if (intval($result) == 1) {
                message('', $this->createWebUrl('yb_video', array('op' => 'comment_reply', 'id' => $id, 'comment_id' => $comment_id)), 'success');
            }else{
                message('设置回复失败.');
            }
        }elseif ($op=='delete_reply') {
            $id = intval($_GPC['id']);
            $comment_id = intval($_GPC['comment_id']);
            $reply_id = intval($_GPC['reply_id']);
            $result = pdo_delete($this->table_yangben_video_reply, array('id' => $reply_id));
            if (intval($result) == 1) {
                pdo_delete($this->table_yangben_video_reply, array('comment_id' => $reply_id));
                message('', $this->createWebUrl('yb_video', array('op' => 'comment_reply', 'id' => $id, 'comment_id' => $comment_id)), 'success');
            } else {
                message('删除回复失败.');
            }
        }
    }

    /**
     * 分类管理
     */
    public function doWebYb_category()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display', 'qrcode');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND cat_name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_category) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_category) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            foreach ($list as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben) . " where category_id=:category_id and uniacid=:uniacid", array(
                    ':category_id' => $v['id'],
                    ':uniacid' => $_W['uniacid']
                ));
                $list[$k]['count'] = $count;

                $company = pdo_get($this->table_yangben_company, array('id' => $v['company_id']), array('company_name'));
                $list[$k]['company_name'] = $company ? $company['company_name'] : '';
            }
            include $this->template('yangben_category');
        } elseif ($op == 'edit') {
            $cate_id = $_GPC['id'];
            $company = $this->getCompanyList();
            if (!empty($cate_id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_category) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $cate_id, ':uniacid' => $_W['uniacid']);
                $category = pdo_fetch($sql, $params);
                if (empty($category)) {
                    message('未找到指定的文章分类.', $this->createWebUrl('yb_category'), 'error');
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];

                if (!$cate_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_category, $data);
                    $cate_id = pdo_insertid();
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_update($this->table_yangben_category, $data, array('id' => $cate_id));
                }
                if (!empty($ret)) {
                    //修改样本关联的公司id
                    pdo_update($this->table_yangben, array('company_id' => $data['company_id']), array('uniacid' => $_W['uniacid'], 'category_id' => $cate_id));
                    message('分类保存成功', $this->createWebUrl('yb_category', array('op' => 'display')), 'success');
                } else {
                    message('分类保存失败');
                }
            }
            include $this->template('yangben_category_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定分类');
            }
            $result = pdo_delete($this->table_yangben_category, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除分类成功.', $this->createWebUrl('yb_category'), 'success');
            } else {
                message('删除分类失败.');
            }
        } elseif ($op == 'qrcode') {
            //生成二维码
            header('Content-type: image/jpg');
            if ($_W['account']['type'] == 4) {
                //获取模板id
                $setting = pdo_fetch("select * from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='template' and item='template'", array(':uniacid' => $_W['uniacid']));
                if ($setting['value'] == 4) {
                    //模板4地址
                    $path = 'temp4/pages/index/index';
                }else{
                    $path = 'we7_bybook/pages/bookcase/index';
                }
                //生成小程序二维码
                echo $this->getWeixinQrcode($path, "category_id={$_GPC['id']}");
            } else {
                if ($_W['ishttps']) {
                    $server_request_scheme = "https";
                } else {
                    $server_request_scheme = "http";
                }
                //访问 url
                $url = $server_request_scheme . '://' . $_SERVER['HTTP_HOST'] . '/app/' . $this->createMobileUrl('yb_bookcase', array('category_id' => $_GPC['id']));
                load()->library('qrcode');
                QRcode::png($url, false, QR_ECLEVEL_L, 5, 1);
                //echo $this->getWeixinQrcode('we7_bybook/pages/bookcase/index', "category_id={$_GPC['id']}");
            }
        }
    }


    /**
     * 名片分类管理
     */
    public function doWebYb_mingpian_category()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND cat_name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_mingpian_category) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_mingpian_category) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            foreach ($list as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_mingpian) . " where category_id=:category_id", array(
                    ':category_id' => $v['id']
                ));
                $list[$k]['count'] = $count;
            }
            include $this->template('yangben_mingpian_category');
        } elseif ($op == 'edit') {
            $cate_id = $_GPC['id'];
            if (!empty($cate_id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_mingpian_category) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $cate_id, ':uniacid' => $_W['uniacid']);
                $category = pdo_fetch($sql, $params);
                if (empty($category)) {
                    message('未找到指定的文章分类.', $this->createWebUrl('yb_mingpian_category'), 'error');
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                if (!$cate_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_mingpian_category, $data);
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_update($this->table_yangben_mingpian_category, $data, array('id' => $cate_id));
                }
                if (!empty($ret)) {
                    message('分类保存成功', $this->createWebUrl('yb_mingpian_category', array('op' => 'display')), 'success');
                } else {
                    message('分类保存失败');
                }
            }
            include $this->template('yangben_mingpian_category_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定分类');
            }
            $result = pdo_delete($this->table_yangben_mingpian_category, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除分类成功.', $this->createWebUrl('yb_mingpian_category'), 'success');
            } else {
                message('删除分类失败.');
            }
        }
    }

    /**
     * 名片管理
     */
    public function doWebYb_mingpian()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display', 'copy', 'qrcode');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' uniacid=:uniacid ';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_mingpian) . ' WHERE' . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_mingpian) . " WHERE $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            include $this->template('yangben_mingpian');
        } elseif ($op == 'edit') {
            //获取公司列表
            $company = $this->getCompanyList();
            $id = $_GPC['id'];
            if (!empty($id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_mingpian) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);
                if (empty($yb)) {
                    message('未找到指定的名片.', $this->createWebUrl('yb_mingpian'), 'error');
                }
                //获取图片
                $imgs = pdo_getall($this->table_yangben_mingpian_img, array('mingpian_id' => $id), 'thumb');
                foreach ($imgs as $k => $v) {
                    $yb['imgs'][] = $v['thumb'];
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                if ($data['company_id'] > 0) {
                    $company_info = pdo_get($this->table_yangben_company, array('id' => $data['company_id']));
                    $data['company_name'] = $company_info['company_name'];
                    $data['company_logo'] = $company_info['company_logo'];
                    $data['company_address'] = $company_info['company_address'];
                    $data['phone'] = $company_info['phone'];
                }
                if (!$id) {
                    $imgs = $data['imgs'];
                    unset($data['imgs']);
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_mingpian, $data);
                    if ($ret) {
                        //插入图片
                        $mingpian_id = pdo_insertid();
                        foreach ($imgs as $k => $v) {
                            pdo_insert($this->table_yangben_mingpian_img, array(
                                'mingpian_id' => $mingpian_id,
                                'thumb' => $v
                            ));
                        }
                    }
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $imgs = $data['imgs'];
                    unset($data['imgs']);
                    $ret = pdo_update($this->table_yangben_mingpian, $data, array('id' => $id));
                    if ($ret) {
                        //删除名片图片
                        pdo_delete($this->table_yangben_mingpian_img, array('mingpian_id' => $id));
                        //插入图片
                        foreach ($imgs as $k => $v) {
                            pdo_insert($this->table_yangben_mingpian_img, array(
                                'mingpian_id' => $id,
                                'thumb' => $v
                            ));
                        }
                    }
                }
                if (!empty($ret)) {
                    message('名片保存成功', $this->createWebUrl('yb_mingpian', array('op' => 'display')), 'success');
                } else {
                    message('名片保存失败');
                }
            }
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_mingpian_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            include $this->template('yangben_mingpian_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定名片');
            }
            $result = pdo_delete($this->table_yangben_mingpian, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除名片成功.', $this->createWebUrl('yb_mingpian'), 'success');
            } else {
                message('删除名片失败.');
            }
        } elseif ($op == 'copy') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定名片');
            }
            $mingpian_info = pdo_get($this->table_yangben_mingpian, array('id' => $id));
            $imgs = pdo_getall($this->table_yangben_mingpian_img, array('mingpian_id' => $id));
            unset($mingpian_info['id']);
            $mingpian_info['name'] = $mingpian_info['name'] . '_复制';
            $mingpian_info['click_num'] = $mingpian_info['uid'] = $mingpian_info['hits'] = 0;
            $mingpian_info['addtime'] = $mingpian_info['updatetime'] = TIMESTAMP;
            $res = pdo_insert($this->table_yangben_mingpian, $mingpian_info);
            //pdo_debug();
            if ($res) {
                $mingpian_id = pdo_insertid();
                if ($imgs) {
                    foreach ($imgs as $k => $v) {
                        pdo_insert($this->table_yangben_mingpian_img, array(
                            'mingpian_id' => $mingpian_id,
                            'thumb' => $v['thumb']
                        ));
                    }
                }
            } else {
                message('复制名片失败.');
            }
            message('复制名片成功.', $this->createWebUrl('yb_mingpian'), 'success');
        } elseif ($op == 'qrcode') {
            //名片二维码
            header('Content-type: image/jpg');
            echo $this->getWeixinQrcode("we7_bybook/pages/contact/index", "id={$_GPC['id']}");
        }
    }

    /**
     * 小程序获取二维码
     * @param $page
     * @param $scene
     * @return mixed
     */
    private function getWeixinQrcode($page, $scene)
    {
        $account = WeAccount::create();
        $access_token = $account->getAccessToken();
        $post = array("page" => $page, 'width' => 430, "scene" => $scene);
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $Qr_code = ihttp_request($url, json_encode($post), array('Content-Type' => 'application/json'));
        //var_dump($Qr_code);exit();
        return $Qr_code['content'];
    }

    /**
     * 文章分类管理
     */
    public function doWebCategory_manager()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND cat_name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_article_category) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_article_category) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            foreach ($list as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article) . " where category_id=:category_id", array(
                    ':category_id' => $v['id']
                ));
                $list[$k]['count'] = $count;

                $company = pdo_get($this->table_yangben_company, array('id' => $v['company_id']), array('company_name'));
                $list[$k]['company_name'] = $company ? $company['company_name'] : '';
            }
            include $this->template('article_category');
        } elseif ($op == 'edit') {
            $cate_id = $_GPC['id'];
            $company = $this->getCompanyList();
            if (!empty($cate_id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_article_category) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $cate_id, ':uniacid' => $_W['uniacid']);
                $category = pdo_fetch($sql, $params);
                if (empty($category)) {
                    message('未找到指定的文章分类.', $this->createWebUrl('category_manager'), 'error');
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                if (!$cate_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_article_category, $data);
                    $cate_id = pdo_insertid();
                } else {
                    $data['updatetime'] = TIMESTAMP;

                    $ret = pdo_update($this->table_yangben_article_category, $data, array('id' => $cate_id));
                }
                if (!empty($ret)) {
                    //修改样本关联的公司id
                    pdo_update($this->table_yangben_article, array('company_id' => $data['company_id']), array('uniacid' => $_W['uniacid'], 'category_id' => $cate_id));
                    message('分类保存成功', $this->createWebUrl('category_manager', array('op' => 'display')), 'success');
                } else {
                    message('分类保存失败');
                }
            }
            include $this->template('article_category_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定分类');
            }
            $result = pdo_delete($this->table_yangben_article_category, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除分类成功.', $this->createWebUrl('category_manager'), 'success');
            } else {
                message('删除分类失败.');
            }
        }
    }

    /**
     * 文章管理
     */
    public function doWebArticle_manager()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' a.uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND (a.title like :keyword or c.cat_name like :keyword)';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_article) . ' as a left join ' . tablename($this->table_yangben_article_category) . ' as c on c.id=a.category_id WHERE ' . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT a.*, c.cat_name FROM " . tablename($this->table_yangben_article) . " as a left join " . tablename($this->table_yangben_article_category) . " as c on c.id=a.category_id WHERE $where ORDER BY a.`id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'a.id');
            foreach ($list as $k => $v) {
                //获取精选留言数
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_comment) . " where is_best=1 and article_id=:article_id", array(
                    ':article_id' => $v['id']
                ));
                $list[$k]['count'] = $count;
                //获取未精选留言
                $total = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_comment) . " where is_best=0 and article_id=:article_id", array(
                    ':article_id' => $v['id']
                ));
                $list[$k]['nobest'] = $total;
            }
            include $this->template('article');
        } elseif ($op == 'edit') {
            $company = $this->getCompanyList();
            $id = $_GPC['id'];
            if (!empty($id)) {
                //编辑
                $sql = 'SELECT a.* FROM ' . tablename($this->table_yangben_article) . ' as a  WHERE a.id=:id AND a.uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $article = pdo_fetch($sql, $params);
                if (empty($article)) {
                    message('未找到指定的文章.', $this->createWebUrl('article_manager'), 'error');
                }

                //获取图片
                $imgs = pdo_getall($this->table_yangben_article_img, array('article_id' => $id), 'thumb');
                foreach ($imgs as $k => $v) {
                    $article['imgs'][] = $v['thumb'];
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                //获取公司id
                $cat = pdo_get($this->table_yangben_article_category, array('id' => $data['category_id']), array('company_id'));
                $data['company_id'] = $cat['company_id'];
                $imgs = $data['imgs'];
                unset($data['imgs']);
                if (!$id) {

                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_article, $data);
                    //插入图片
                    $article_id = pdo_insertid();
                    foreach ($imgs as $k => $v) {
                        pdo_insert($this->table_yangben_article_img, array(
                            'article_id' => $article_id,
                            'thumb' => $v
                        ));
                    }
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    //删除名片图片
                    pdo_delete($this->table_yangben_article_img, array('article_id' => $id));
                    //插入图片
                    foreach ($imgs as $k => $v) {
                        pdo_insert($this->table_yangben_article_img, array(
                            'article_id' => $id,
                            'thumb' => $v
                        ));
                    }
                    $ret = pdo_update($this->table_yangben_article, $data, array('id' => $id));
                }

                if (!empty($ret)) {
                    message('文章保存成功', $this->createWebUrl('article_manager', array('op' => 'display')), 'success');
                } else {
                    message('文章保存失败');
                }
            }
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_article_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            include $this->template('article_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定文章');
            }
            $result = pdo_delete($this->table_yangben_article, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除文章成功.', $this->createWebUrl('article_manager'), 'success');
            } else {
                message('删除文章失败.');
            }
        }
    }

    /**
     * 文章留言
     */
    public function doWebComment()
    {
        global $_W, $_GPC;
        $ops = array('reply', 'delete', 'display');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $id = $_GPC['id'];
            if (!$id) {
                message('未找到指定文章');
            }

            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' c.article_id=:article_id';
            $params = array(
                ':article_id' => $id
            );

            if (!empty($_GPC['keyword'])) {
                $where .= ' AND m.`nickname` like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_article_comment) . " as c WHERE " . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);

            $sql = 'SELECT c.*, r.reply_msg as msg, m.nickname, m.avatar FROM ' . tablename($this->table_yangben_article_comment) . " as c left join " . tablename($this->table_yangben_article_reply) . " as r on r.comment_id=c.id left join " . tablename("mc_members") . " as m on m.uid=c.user_id WHERE {$where} ORDER BY c.`id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
            $comment = pdo_fetchall($sql, $params, 'id');
            include $this->template('article_comment');
        } elseif ($op == 'reply') {
            $id = $_GPC['id'];
            $comment_id = $_GPC['comment_id'];
            if (checksubmit()) {

                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_reply) . " where comment_id=:comment_id", array(
                    ':comment_id' => $comment_id
                ));

                if ($count) {
                    pdo_update($this->table_yangben_article_reply, array(
                        'reply_msg' => $_GPC['reply_msg'],
                        'addtime' => time()
                    ), array('comment_id' => $comment_id));
                } else {
                    pdo_insert($this->table_yangben_article_reply, array(
                        'comment_id' => $comment_id,
                        'reply_msg' => $_GPC['reply_msg'],
                        'addtime' => time(),
                    ));
                    pdo_update($this->table_yangben_article_comment, array('is_best' => 1), array('id' => $comment_id));
                }
                message('回复成功', $this->createWebUrl('comment', array('id' => $id)), 'success');
            }
            $info = pdo_get($this->table_yangben_article_comment, array('id' => $comment_id));
            $reply = pdo_get($this->table_yangben_article_reply, array('comment_id' => $comment_id));

            include $this->template('article_comment_reply');
        } elseif ($op == 'delete') {
            pdo_delete($this->table_yangben_article_comment, array('id' => $_GPC['comment_id']));
            message('删除成功', $this->createWebUrl('comment', array('id' => $_GPC['id'])), 'success');
        }
    }

    /**
     * 获取样本类型
     * @param $open
     * @return mixed
     */
    protected function getOpen($open)
    {
        $list = array('公开', '密码', '问题');
        return $list[$open];
    }

    /**
     * 获取授权
     */
    public function getOauth()
    {
        global $_W, $_GPC;
        if (empty($_W['openid'])) {
            if (empty($_SESSION['openid'])) {
                if ($_GPC['wgateid']) {
                    $_W['openid'] = $_SESSION['openid'] = $_GPC['wgateid'];
                } else {
                    $thisUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                    $gateUrl = "http://www.weixingate.com/gate.php?back=$thisUrl&force=1&info=none";
                    header('Location: ' . $gateUrl);
                    exit;
                }
            } else {
                $_W['openid'] = $_SESSION['openid'];
            }
        }
    }

    /**
     * 检查微信平台
     */
    private function checkPlatform()
    {
        global $_W, $_GPC;
        if (empty($_W['openid'])) {
            $thisUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            //$gateUrl = "http://www.weixingate.com/gate.php?back=$thisUrl&force=1&info=none";
			$account = WeAccount::create();
            $gateUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$account['key']}&response_type=code&scope=snsapi_userinfo&connect_redirect=1&state=&redirect_uri=$thisUrl#wechat_redirect";
            header('Location: ' . $gateUrl);
            exit;
        }
    }

    /**
     * 检查是否显示PC
     * @return bool
     */
    private function checkIsShowPc()
    {
        global $_W;
        $filename = IA_ROOT . '/addons/we7_bybook_plugin_showpc/lock.txt';
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $content_array = explode('_', base64_decode($content));
            if ($content_array[1] == $this->_lock && $content_array[2] == $_W['siteroot']) {
                return true;
            }
        }
        $this->checkPlatform();
        return false;
    }


    public function doWebYb_company()
    {
        global $_W, $_GPC;
        $ops = array('edit', 'delete', 'display', 'qrcode');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            $pageindex = max(intval($_GPC['page']), 1); // 当前页码
            $pagesize = 10; // 设置分页大小

            $where = ' WHERE uniacid=:uniacid';
            $params = array(
                ':uniacid' => $_W['uniacid']
            );
            if (!empty($_GPC['keyword'])) {
                $where .= ' AND company_name like :keyword';
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_company) . $where;
            $total = pdo_fetchcolumn($sql, $params);
            //分页
            $pager = pagination($total, $pageindex, $pagesize);
            $sql = "SELECT * FROM " . tablename($this->table_yangben_company) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
            $list = pdo_fetchall($sql, $params, 'id');
            foreach ($list as $k => $v) {
                $list[$k]['company_logo'] = tomedia($v['company_logo']);
            }
            include $this->template('yangben_company');
        } elseif ($op == 'edit') {
            $id = $_GPC['id'];
            if (!empty($id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_company) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);
                if (empty($yb)) {
                    message('未找到指定的公司.', $this->createWebUrl('yb_company'), 'error');
                }
            }
            if (checksubmit()) {
                $data = $_GPC['data'];
                if (!$id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_company, $data);
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_update($this->table_yangben_company, $data, array('id' => $id));
                    //更新名片信息
                    pdo_update($this->table_yangben_mingpian, array(
                        'company_name' => $data['company_name'],
                        'company_logo' => $data['company_logo'],
                        'company_address' => $data['company_address'],
                        'phone' => $data['phone'],
                    ), array('company_id' => $id, 'is_display' => 1));
                }
                if (!empty($ret)) {
                    message('公司保存成功', $this->createWebUrl('yb_company', array('op' => 'display')), 'success');
                } else {
                    message('公司保存失败');
                }
            }
            include $this->template('yangben_company_add');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定公司');
            }
            $result = pdo_delete($this->table_yangben_company, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除公司成功.', $this->createWebUrl('yb_company'), 'success');
            } else {
                message('删除公司失败.');
            }
        } elseif ($op == 'qrcode') {
            //名片二维码
            header('Content-type: image/jpg');
            echo $this->getWeixinQrcode("we7_bybook/pages/contact/index", "company_id={$_GPC['id']}");
        }
    }

    //===========================================微擎后台结束====================================

    //===========================================公众号========================================

    /**
     *
     */
    public function doMobile_bookcase()
    {
        global $_W, $_GPC;

        $this->checkIsShowPc();

        //var_dump($_W['fans']);exit();

        $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='new_day' limit 1", array(':uniacid' => $_W['uniacid']));

        $day = empty($setting['value']) ? 3 : $setting['value'];

        $yb = pdo_fetchall("select * from" . tablename($this->table_yangben) . " where uniacid=:uniacid and is_display = 1 order by `sort` asc", array(':uniacid' => $_W['uniacid'], 'category_id' => $_GPC['category_id']), 'id');
        $yb_data = array();
        foreach ($yb as $k => $v) {
            $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id", array(":yangben_id" => $v['id']));
            $yb_data[] = array(
                'id' => $v['id'],
                'title' => $v['title'],
                'is_show_ad' => $v['is_show_ad'],
                'uniacid' => $v['uniacid'],
                'category_id' => $v['category_id'],
                'thumb' => tomedia($v['thumb']),
                'addtime' => $v['addtime'],
                'introduce' => strip_tags(html_entity_decode($v['introduce'])),
                'pages' => $count,
                'time' => date("Y-m-d", $v['updatetime']),
                'newTime' => date("Y-m-d H:i:s", $v['updatetime']),
                'is_new' => time() - $v['updatetime'] < $day * 24 * 3600 ? 1 : 0
            );
        }

        $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='qrcode' limit 1", array(':uniacid' => $_W['uniacid']));

        $setting_wx = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='weixin'", array(':uniacid' => $_W['uniacid']));
        $setting_weixin = array();
        foreach ($setting_wx as $k => $v) {
            $setting_weixin[$v['item']] = $v['value'];
        }

        $setting_seo = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='seo'", array(':uniacid' => $_W['uniacid']));
        $setting_s = array();
        foreach ($setting_seo as $k => $v) {
            $setting_s[$v['item']] = $v['value'];
        }


        if ($setting_s['seo_name']) {
            $_W['page']['title'] = $setting_s['seo_name'];
        }
        if ($setting_s['seo_keyword']) {
            $_W['page']['copyright']['keywords'] = $setting_s['seo_keyword'];
        }
        if ($setting_s['seo_desc']) {
            $_W['page']['copyright']['description'] = $setting_s['seo_desc'];
        }

        //var_dump($_W['page']['title']);exit();

        $account_api = WeAccount::create();
        $jssdk = $account_api->getJssdkConfig();


        include $this->template('bookcase');
    }

    /**
     * 公众号首页
     */
    public function doMobileYb_bookcase()
    {
        global $_W, $_GPC;

        $this->checkIsShowPc();

        $category_id = $_GPC['category_id'];

        //获取样本分类列表
        $yb = pdo_fetchall("select * from" . tablename($this->table_yangben) . " where uniacid=:uniacid and is_display = 1 and category_id=:category_id order by `sort` asc", array(':uniacid' => $_W['uniacid'], 'category_id' => $category_id), 'id');

        $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='qrcode' limit 1", array(':uniacid' => $_W['uniacid']));

        $setting_wx = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='weixin'", array(':uniacid' => $_W['uniacid']));
        $setting_weixin = array();
        foreach ($setting_wx as $k => $v) {
            $setting_weixin[$v['item']] = $v['value'];
        }

        $setting_seo = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='seo'", array(':uniacid' => $_W['uniacid']));
        $setting_s = array();
        foreach ($setting_seo as $k => $v) {
            $setting_s[$v['item']] = $v['value'];
        }

        //seo设置
        if ($setting_s['seo_name']) {
            $_W['page']['title'] = $setting_s['seo_name'];
        }
        if ($setting_s['seo_keyword']) {
            $_W['page']['copyright']['keywords'] = $setting_s['seo_keyword'];
        }
        if ($setting_s['seo_desc']) {
            $_W['page']['copyright']['description'] = $setting_s['seo_desc'];
        }

        //jssdk配置
        $account_api = WeAccount::create();
        $jssdk = $account_api->getJssdkConfig();

        //获取模板
        $setting_temp = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='webtempl'", array(':uniacid' => $_W['uniacid']));
        $setting_temp_array = array();
        foreach ($setting_temp as $k => $v) {
            $setting_temp_array[$v['item']] = $v['value'];
        }
        $templet_id = isset($setting_temp_array['web_template']) ? $setting_temp_array['web_template'] : 1;

        if ($templet_id == 1) {
            $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='new_day' limit 1", array(':uniacid' => $_W['uniacid']));
            $day = empty($setting['value']) ? 3 : $setting['value'];
            $yb_data = array();
            foreach ($yb as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id", array(":yangben_id" => $v['id']));
                $yb_data[] = array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'is_show_ad' => $v['is_show_ad'],
                    'uniacid' => $v['uniacid'],
                    'category_id' => $v['category_id'],
                    'thumb' => tomedia($v['thumb']),
                    'addtime' => $v['addtime'],
                    'introduce' => strip_tags(html_entity_decode($v['introduce'])),
                    'pages' => $count,
                    'time' => date("Y-m-d", $v['updatetime']),
                    'newTime' => date("Y-m-d H:i:s", $v['updatetime']),
                    'is_new' => time() - $v['updatetime'] < $day * 24 * 3600 ? 1 : 0
                );
            }
            //获取样本分类
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));

            foreach ($category as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben) . " where category_id=:category_id and is_display=1", array(
                    ':category_id' => $v['id']
                ));
                $category[$k]['count'] = $count;
            }
            include $this->template('bookcase');
        }elseif ($templet_id == 2) {
            //获取模板2数据
            $yb_data = array();
            foreach ($yb as $k => $v) {
                //获取样本内容，获取最多5条
                $content = pdo_fetchall("select * from ".tablename($this->table_yangben_content)." where yangben_id=:yangben_id order by `sort` asc limit 5", array(':yangben_id' => $v['id']));
                $content_data = array();
                foreach ($content as $k1 => $v1) {
                    $content_data[] = tomedia($v1['content']);
                }
                $yb_data[] = array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'is_show_ad' => $v['is_show_ad'],
                    'uniacid' => $v['uniacid'],
                    'category_id' => $v['category_id'],
                    'thumb' => tomedia($v['thumb']),
                    'addtime' => $v['addtime'],
                    'introduce' => strip_tags(html_entity_decode($v['introduce'])),
                    'time' => date("Y-m-d", $v['updatetime']),
                    'newTime' => date("Y-m-d H:i:s", $v['updatetime']),
                    'content' => $content_data
                );
            }
            include $this->template('web_template_2');
        }
    }

    /**
     * 公众号首页
     */
    public function doMobileYb_index()
    {
        global $_W, $_GPC;

        $this->checkIsShowPc();

        //设置默认分类
        $category_id = 0;

        //获取seo
        $setting_seo = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='seo'", array(':uniacid' => $_W['uniacid']));
        $setting_s = array();
        foreach ($setting_seo as $k => $v) {
            $setting_s[$v['item']] = $v['value'];
        }
        if ($setting_s['seo_name']) {
            $_W['page']['title'] = $setting_s['seo_name'];
        }
        if ($setting_s['seo_keyword']) {
            $_W['page']['copyright']['keyword'] = $setting_s['seo_keyword'];
        }
        if ($setting_s['seo_desc']) {
            $_W['page']['copyright']['description'] = $setting_s['seo_desc'];
        }

        //获取微信jsdk配置
        $account_api = WeAccount::create();
        $jssdk = $account_api->getJssdkConfig();

        //获取分享标题，分享描述，图标
        $setting_wx = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='weixin'", array(':uniacid' => $_W['uniacid']));
        $setting_weixin = array();
        foreach ($setting_wx as $k => $v) {
            $setting_weixin[$v['item']] = $v['value'];
        }

        //获取样本
        $yb = pdo_fetchall("select * from" . tablename($this->table_yangben) . " where uniacid=:uniacid and is_display = 1 order by `sort` asc", array(':uniacid' => $_W['uniacid']), 'id');

        //获取模板
        $setting_temp = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and type='webtempl'", array(':uniacid' => $_W['uniacid']));
        $setting_temp_array = array();
        foreach ($setting_temp as $k => $v) {
            $setting_temp_array[$v['item']] = $v['value'];
        }
        $templet_id = isset($setting_temp_array['web_template']) ? $setting_temp_array['web_template'] : 1;

        //判断所属哪个模板
        if ($templet_id == 2) {
            //获取模板2数据
            $yb_data = array();
            foreach ($yb as $k => $v) {
                //获取样本内容
                $content = pdo_fetchall("select * from ".tablename($this->table_yangben_content)." where yangben_id=:yangben_id order by `sort` asc limit 5", array(':yangben_id' => $v['id']));
                $content_data = array();
                foreach ($content as $k1 => $v1) {
                    $content_data[] = tomedia($v1['content']);
                }
                $yb_data[] = array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'is_show_ad' => $v['is_show_ad'],
                    'uniacid' => $v['uniacid'],
                    'category_id' => $v['category_id'],
                    'thumb' => tomedia($v['thumb']),
                    'addtime' => $v['addtime'],
                    'introduce' => strip_tags(html_entity_decode($v['introduce'])),
                    'time' => date("Y-m-d", $v['updatetime']),
                    'newTime' => date("Y-m-d H:i:s", $v['updatetime']),
                    'content' => $content_data
                );
            }
			$is_mobile = $this->isMobile();
            include $this->template('web_template_2');
        }elseif ($templet_id == 1) {
            $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='new_day' limit 1", array(':uniacid' => $_W['uniacid']));
            $day = empty($setting['value']) ? 3 : $setting['value'];

            $yb_data = array();
            foreach ($yb as $k => $v) {
				if($v['mode'] == 2){
					$count = $v['pdf_page'];
				}else{
					$count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id", array(":yangben_id" => $v['id']));
				}
                $yb_data[] = array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'is_show_ad' => $v['is_show_ad'],
                    'uniacid' => $v['uniacid'],
                    'category_id' => $v['category_id'],
                    'thumb' => tomedia($v['thumb']),
                    'addtime' => $v['addtime'],
                    'introduce' => strip_tags(html_entity_decode($v['introduce'])),
                    'pages' => $count,
                    'time' => date("Y-m-d", $v['updatetime']),
                    'newTime' => date("Y-m-d H:i:s", $v['updatetime']),
                    'is_new' => time() - $v['updatetime'] < $day * 24 * 3600 ? 1 : 0
                );
            }
            //获取二维码
            $setting = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='qrcode' limit 1", array(':uniacid' => $_W['uniacid']));

            //获取样本分类
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            foreach ($category as $k => $v) {
                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben) . " where category_id=:category_id and is_display=1", array(
                    ':category_id' => $v['id']
                ));
                $category[$k]['count'] = $count;
            }
            include $this->template('index');
        }

    }

    /**
     * 同步
     */
    public function doMobileSync()
    {
        global $_GPC, $_W;
        //访问地址
        //echo $_W['siteroot'].'app'.trim($this->createMobileUrl('sync'), '.');
        $account_api = WeAccount::create();
        $type = empty($_GPC['type']) ? 'news' : $_GPC['type'];
        //获取总数
        $test = $account_api->batchGetMaterial($type);
        $result = $this->material_sync($test['item'], array(), $type);
        $id = implode(',', $result);
        $total = ceil($test['total_count'] / 20);
        unset($test, $result);
        if ($total > 1) {
            for ($i = 2; $i <= $total; $i++) {
                $data = $account_api->batchGetMaterial($type, ($i - 1) * 20);
                $result = $this->material_sync($data['item'], array(), $type);
                $id .= implode(',', $result);
            }
        }
        unset($data, $result);
        die('同步完成！');
        echo $id;
    }

    /**
     * 处理
     * @param $material
     * @param $exist_material
     * @param $type
     * @return array
     */
    function material_sync($material, $exist_material, $type)
    {

        global $_W;

        $material = empty($material) ? array() : $material;

        foreach ($material as $news) {

            $attachid = '';

            $material_exist = pdo_get('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));

            if (empty($material_exist)) {

                $material_data = array(

                    'uniacid' => $_W['uniacid'],

                    'acid' => $_W['acid'],

                    'media_id' => $news['media_id'],

                    'type' => $type,

                    'model' => 'perm',

                    'createtime' => $news['update_time']

                );

                if ($type == 'image') {

                    $material_data['filename'] = $news['name'];

                    $material_data['attachment'] = $news['url'];

                }

                if ($type == 'voice') {

                    $material_data['filename'] = $news['name'];

                }

                if ($type == 'video') {

                    $material_data['tag'] = iserializer(array('title' => $news['name']));

                }

                pdo_insert('wechat_attachment', $material_data);

                $attachid = pdo_insertid();

            } else {

                if ($type == 'image') {

                    $material_data = array(

                        'createtime' => $news['update_time'],

                        'attachment' => $news['url'],

                        'filename' => $news['name']

                    );

                    pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));

                }

                if ($type == 'voice') {

                    $material_data = array(

                        'createtime' => $news['update_time'],

                        'filename' => $news['name']

                    );

                    pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));

                }

                if ($type == 'video') {

                    $tag = empty($material_exist['tag']) ? array() : iunserializer($material_exist['tag']);

                    $material_data = array(

                        'createtime' => $news['update_time'],

                        'tag' => iserializer(array('title' => $news['name'], 'url' => $tag['url']))

                    );

                    pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));

                }

                $exist_material[] = $material_exist['id'];

            }

            if ($type == 'news') {

                $attachid = empty($attachid) ? $material_exist['id'] : $attachid;

                pdo_delete('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $attachid));

                foreach ($news['content']['news_item'] as $key => $new) {

                    $new_data = array(

                        'uniacid' => $_W['uniacid'],

                        'attach_id' => $attachid,

                        'thumb_media_id' => $new['thumb_media_id'],

                        'thumb_url' => $new['thumb_url'],

                        'title' => $new['title'],

                        'author' => $new['author'],

                        'digest' => $new['digest'],

                        'content' => $new['content'],

                        'content_source_url' => $new['content_source_url'],

                        'show_cover_pic' => $new['show_cover_pic'],

                        'url' => $new['url'],

                        'displayorder' => $key,

                    );

                    pdo_insert('wechat_news', $new_data);

                }

                pdo_update('wechat_attachment', array('createtime' => $news['update_time']), array('media_id' => $news['media_id']));

            }

        }

        return $exist_material;

    }

    function doMobileTest()
    {
        $this->doSendTemplate('test', '', '123', '546');
    }


    public function doSendTemplate($nickname, $mobile, $msg, $notify_user = '')
    {
        global $_GPC, $_W;

        $type = $_W['account']['type'];

        if (!$nickname || !$notify_user) {
            return;
        }

        //主账号信息
        $uid = array();

        if ($type == 1 || $type == 3) {
            $uniacid = $_W['uniacid'];

        }elseif ($type == 4) {
            //小程序
            $data = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='uniacid'", array(':uniacid' => $_W['uniacid']));
            $uniacid = $data['value'];

//            $udata = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='basic' and item='uid'", array(':uniacid' => $uniacid));
//            if ($udata['value']) {
//                $setting['uid'] = $udata['value'];
//            }
        }

        $account_api = WeAccount::createByUniacid($uniacid);
        //获取模板id
        $data = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='wx_template_id'", array(':uniacid' => $uniacid));
        $template_id = $data['value'];

        $data1 = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='first_keyword1'", array(':uniacid' => $uniacid));
        $first_keyword1 = $data1['value'];
        $data2 = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='first_keyword2'", array(':uniacid' => $uniacid));
        $first_keyword2 = $data2['value'];
        $data3 = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='first_keyword3'", array(':uniacid' => $uniacid));
        $first_keyword3 = $data3['value'];
        $data4 = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='first_keyword4'", array(':uniacid' => $uniacid));
        $first_keyword4 = $data4['value'];

        $data_remark = pdo_fetch("select value from ".tablename($this->table_yangben_setting)." where uniacid=:uniacid and type='wxtempl' and item='remark'", array(':uniacid' => $uniacid));
        $remark = $data_remark['value'];

        //获取粉丝openid
        $fans = pdo_fetchall("select nickname,openid from ".tablename($this->table_yangben_fans)." where id in({$notify_user})");

        //var_dump($fans);
        $post_data['first'] = array(
            'value' => $msg,
        );
        if ($first_keyword1) {
            $post_data[$first_keyword1] =  array(
                'value' => $nickname,
            );
        }
        if ($first_keyword2) {
            $post_data[$first_keyword2] =  array(
                'value' => $mobile?$mobile:'0',
            );
        }
        if ($first_keyword3) {
            $post_data[$first_keyword3] =  array(
                'value' => date('Y年m月d日 H:i:s', time()),
            );

        }
        if ($first_keyword4) {
            $post_data[$first_keyword4] =  array(
                'value' => $msg,
            );
        }
        $post_data['remark'] = array(
            'value' => $remark,
        );
        //公众号
        foreach ($fans as $k => $v) {
            $res = $account_api->sendTplNotice($v['openid'], $template_id, $post_data);
            //var_dump($res);
        }

    }

    /*
    public function doMobileYb_center()
    {
        global $_W, $_GPC;
        $wx_uid = intval($_GPC['wx_uid']) ? $_GPC['wx_uid'] : 0;
        $flag = intval($_GPC['flag']) ? $_GPC['flag'] : 0;
		
		if($_GPC['p']) {
			//显示指定页
			$page = $_GPC['p'];
		}else{
			//获取最后访问第几页
			$zuji = pdo_fetch("select `page` from ".tablename($this->table_yangben_zuji)." where uniacid=:uniacid and type=1 and yangben_id=:yangben_id and uid=:uid order by id desc", array(':uniacid' => $_W['uniacid'],':yangben_id' =>$id, ':uid' => $uid));
			$page = $zuji['page'] ? $zuji['page'] : 1;
		}
		
		$url = $this->createMobileUrl('yb_show', array('id' => $_GPC['id'], 'category_id' => $category_id)) . '#p=' . $page;
		
    }
    */

    /**
     * 样本详情
     */
    public function doMobileYb_show()
    {
        global $_W, $_GPC;

        //如果不是小程序访问，则检查
        if (!$_GPC['wx_uid']) {
            $this->checkIsShowPc();
        }
        $id = intval($_GPC['id']);
        if (empty($id)) {
            message('未找到指定样本');
        } else {
            $wx_uid = intval($_GPC['wx_uid']) ? $_GPC['wx_uid'] : 0;
            $flag = intval($_GPC['flag']) ? $_GPC['flag'] : 0;
            $uid = $this->_fans ? $this->_fans['id'] : $wx_uid;
            $is_share = intval($_GPC['is_share']) ? $_GPC['is_share'] : 0;

            if($_GPC['p']) {
				//显示指定页
				$page = $_GPC['p'];
			}else{
				//获取最后访问第几页
				$zuji = pdo_fetch("select `page` from ".tablename($this->table_yangben_zuji)." where uniacid=:uniacid and type=1 and yangben_id=:yangben_id and uid=:uid order by id desc", array(':uniacid' => $_W['uniacid'],':yangben_id' =>$id, ':uid' => $uid));
				$page = $zuji['page'] ? $zuji['page'] : 1;
			}

            //获取jssdk数据
            $account_api = WeAccount::create();
            $jssdk = $account_api->getJssdkConfig();

            //获取样本数据
            $yb = pdo_fetch("select * from " . tablename($this->table_yangben) . " where id=:id LIMIT 1", array(':id' => $id));
            extract($yb);

            //测试
            //message('', $this->createMobileUrl('yb_password', array('id' => $id, 'wx_uid' => $wx_uid, 'flag' => $flag), false), 'error');

            //判断是否输入密码
            if ($_SESSION['check_yangben'] != $id && $yb['open'] > 0) {
                message('', $this->createMobileUrl('yb_password', array('id' => $id, 'wx_uid' => $wx_uid, 'flag' => $flag), false), 'error');
            }
			
			$point_data = array();
            //样本内容
            $yb_content = pdo_fetchall("select * from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id order by `sort` asc, id asc", array(':yangben_id' => $id));
            $items = count($yb_content);
            //数据拼装
            $stryb = "";
            foreach ($yb_content as $k => $v) {
                $thumb = tomedia($v['content']);
                $stryb = $stryb . "," . "{'l':'$thumb', 'n':'$thumb', 't':'$thumb'}";
				//获取页面参数
				$param = pdo_get($this->table_yangben_params, array('yangben_id' => $id, 'md5' => md5($v['content'])));
				if($param) {
					$field_data = json_decode($param['params'], true);
					foreach($field_data as $ks => $vs) {
						if($vs['type'] == 'video') {
							$field_data[$ks]['url'] = tomedia($vs['url']);
						}
					}
					$point_data[$k] = $field_data;
				}else{
					$point_data[$k] = array();
				}
            }
			$point_data = json_encode($point_data, JSON_UNESCAPED_UNICODE);
			
            $stryb = substr($stryb, 1);

            //计算页数
            $i = 0;
            foreach ($yb_content as $k => $v) {
                $yb_content[$k]['page'] = $k + 1;
            }

            //更新浏览次数
            pdo_update($this->table_yangben, array('hits +=' => 1), array('id' => $id));

            //没用了
            $folder = $_GPC['folder'] ? $_GPC['folder'] : 'loading';


            $company_id = isset($_GPC['company_id']) ? $_GPC['company_id'] : 0;

            //获取联系
            if ($yb['is_type'] == 1) {
                //is_close_show：是否显示联系方式
                $s = pdo_fetch('select `value` from ' . tablename($this->table_yangben_setting) . ' where uniacid=:uniacid and item="is_close_show"', array(':uniacid' => $_W['uniacid']));
                $setting = array(
                    'company' => $yb['contact_company'],
                    'qq' => $yb['contact_qq'],
                    'email' => $yb['contact_email'],
                    'mobile' => $yb['contact_mobile'],
                    'qrcode' => $yb['contact_qrcode'],
                    'left_name' => $yb['contact_left_name'],
                    'left_url' => $yb['contact_left_url'],
                    'right_name' => $yb['contact_right_name'],
                    'right_url' => $yb['contact_right_url'],
                    'is_close_show' => $s['value']
                );
            } else {
                $setting = $this->getSettings();
            }


            //是否显示联系按钮
            $global_c = pdo_fetch('select `value` from ' . tablename($this->table_yangben_setting) . ' where uniacid=:uniacid and item="contact_is_show"', array(':uniacid' => $_W['uniacid']));
            if ($global_c['value'] != 2) {
                //显示
                $setting['contact_is_show'] = $yb['contact_is_show'];
            } else {
                //隐藏
                $setting['contact_is_show'] = 2;
            }

            //设置外部链接
            if ($setting['left_url']) {
                if (strpos($setting['left_url'], '?') !== false) {
                    $setting['left_url'] = $setting['left_url'] . '&uniacid=' . $_W['uniacid'] . '&type=' . $_W['account']['type'] . '&uid=' . $uid;
                } else {
                    $setting['left_url'] = $setting['left_url'] . '?uniacid=' . $_W['uniacid'] . '&type=' . $_W['account']['type'] . '&uid=' . $uid;
                }
            }
            if ($setting['right_url']) {
                if (strpos($setting['right_url'], '?') !== false) {
                    $setting['right_url'] = $setting['right_url'] . '&uniacid=' . $_W['uniacid'] . '&type=' . $_W['account']['type'] . '&uid=' . $uid;
                } else {
                    $setting['right_url'] = $setting['right_url'] . '?uniacid=' . $_W['uniacid'] . '&type=' . $_W['account']['type'] . '&uid=' . $uid;
                }
            }

            //获取目录
            $datas = pdo_getall($this->table_yangben_contents, array('yangben_id' => $id), array(), '', array('page asc'));
            $yb_contents = $this->getContentsCategory($datas);


            if ($uid > 0) {
                if ($_W['account']['type'] != 4) {
                    $nickname = $this->_fans['nickname'];
                    $mobile = $this->_fans['mobile'];
                }else{
                    $member = pdo_fetch("select nickname, mobile from ".tablename("mc_members")." where uniacid=:uniacid and uid=:uid", array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
                    $nickname = $member['nickname'];
                    $mobile = $member['mobile'];

                    $s1 = pdo_fetch('select `value` from ' . tablename($this->table_yangben_setting) . ' where uniacid=:uniacid and item="share_btn"', array(':uniacid' => $_W['uniacid']));
                    $s2 = pdo_fetch('select `value` from ' . tablename($this->table_yangben_setting) . ' where uniacid=:uniacid and item="share_uid"', array(':uniacid' => $_W['uniacid']));
                    if ($s1['value'] == 2){
                        $share_uid_array = explode('|', $s2['value']);
                        if (!in_array($uid, $share_uid_array)){
                            //保存浏览记录
                            $share = pdo_get($this->table_yangben_share, array('uid' => $uid, 'yangben_id' => $id));
                            if (!$share){
                                pdo_insert($this->table_yangben_share, array(
                                    'uniacid' => $_W['uniacid'],
                                    'yangben_id' => $id,
                                    'uid' => $uid,
                                ));
                            }
                        }
                    }

                }
                //发送模板通知
                //获取通知用户
                $setting2 = $this->getSettings($_W['uniacid']);
                if ($setting2['notify_user']) {
                    $setting2['notify_user'] = str_replace('|', ',', $setting2['notify_user']);
                }else{
                    $setting2['notify_user'] = "";
                }
                //获取样本关联的用户
                $related = pdo_getall($this->table_yangben_related_fans, array('yangben_id' => $id, 'uniacid' => $_W['uniacid']));
                if ($related) {
                    //如果当前样本存在关联，只通知关联的人
                    $notify_user = explode(',', $setting2['notify_user']);
                    $fans_id = array();
                    foreach ($related as $k => $v) {
                        if (in_array($v['fans_id'], $notify_user)) {
                            $fans_id[] = $v['fans_id'];
                        }
                    }
                    //已存在关联的，只通知给关联客户
                    if ($fans_id) {
                        $setting2['notify_user'] = implode(',', $fans_id);
                    }
                }
                //echo $setting['notify_user'];exit;
                $this->doSendTemplate($nickname, $mobile, $yb['title'].'被访问了。', $setting2['notify_user']);
            }
			if($yb['mode'] == 2){
				include $this->template('pdf_show');
			}else{
				
				include $this->template('show');
			}
        }
    }

    /**
     * 获取会员昵称
     * @param $uid
     * @return mixed
     */
    public function getNickName($uid) {
        global $_W, $_GPC;
        if ($this->_fans) {
            $nickname = $this->_fans['nickname'];
        }else{
            $member = pdo_fetch("select nickname from ".tablename("mc_members")." where uniacid=:uniacid and uid=:uid", array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
            $nickname = $member['nickname'];
        }
        return $nickname;
    }


    public function doMobileYb_ad()
    {
        global $_GPC;
        $ad = pdo_get($this->table_yangben, array("id" => $_GPC['id']), array('id', 'ad_content_img', 'ad_play_time'));
        $ad['ad_content_img'] = tomedia($ad['ad_content_img']);
        extract($ad);
        $p = $_GPC['p'] ? $_GPC['p'] : 0;
        $category_id = $_GPC['category_id'] ? $_GPC['category_id'] : 0;
		//$is_share = $_GPC['is_share'] ? $_GPC['is_share'] : 0;
        $url = $this->createMobileUrl('yb_show', array('id' => $_GPC['id'], 'category_id' => $category_id));
        include $this->template('ad');
    }

    public function doMobileMini_qrcode()
    {
        global $_GPC;
        header('Content-type: image/jpg');
        echo $this->getWeixinQrcode('we7_bybook/pages/book/index', "id={$_GPC['id']}");
    }

    /**
     * 获取手机端二维码
     */
    public function doMobileGet_qrcode()
    {
        global $_GPC;
        //生成二维码
        if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) {
            $server_request_scheme = 'https';

        } else {
            $server_request_scheme = 'http';
        }
        $value = $server_request_scheme . '://' . $_SERVER['HTTP_HOST'] . '/app/' . $this->createMobileUrl('yb_show', array('id' => $_GPC['id']));
        load()->library('qrcode');
        QRcode::png($value, false, QR_ECLEVEL_L, 5, 1);
    }

    /**
     * 获取配置
     * @date: 2020-10-6 下午1:26:40
     * @author: Mr.Yang
     * @param: variable
     * @return: array
     */
    public function getSettings($uniacid='')
    {
        global $_W, $_GPC;
        $uniacid = $uniacid ? $uniacid : $_W['uniacid'];
        $setting_data = pdo_fetchall("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid", array(':uniacid' => $uniacid));
        $setting = array();
        foreach ($setting_data as $k => $v) {
            $setting[$v['item']] = $v['value'];
        }
        return $setting;
    }

    /**
     * 密码显示
     */
    public function doMobileYb_password()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            message('未找到指定样本');
        } else {
            $yb = pdo_fetch("select * from " . tablename($this->table_yangben) . " where id=:id LIMIT 1", array(':id' => $id));
            if ($yb['is_type'] == 1) {
                $setting = array(
                    'qq' => $yb['contact_qq'],
                    'email' => $yb['contact_email'],
                    'mobile' => $yb['contact_mobile']
                );
            } elseif ($yb['is_type'] == 0) {
                $setting = $this->getSettings();
            }

            //是否为微信
            $flag = intval($_GPC['flag']) ? $_GPC['flag'] : 0;

            //微信用户id
            $wx_uid = intval($_GPC['wx_uid']) ? $_GPC['wx_uid'] : 0;

            include $this->template('password');
        }
    }

    /**
     * 检查密码
     */
    public function doMobileYb_check()
    {
        global $_W, $_GPC;
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        $password = $_GPC['password'];
        $answer = $_GPC['answer'];

        $yb = pdo_fetch("select * from " . tablename($this->table_yangben) . " where id=:id LIMIT 1", array(':id' => $id));
        if ($type == 1) {
            if ($yb['password'] == $password) {
                $_SESSION['check_yangben'] = $id;
                die(json_encode(array('code' => 0)));
            } else {
                die(json_encode(array('code' => -1, 'msg' => '密码输入错误')));
            }
        } elseif ($type == 2) {
            if ($yb['answer'] == $answer) {
                $_SESSION['check_yangben'] = $id;
                die(json_encode(array('code' => 0)));
            } else {
                die(json_encode(array('code' => -1, 'msg' => '答案输入错误')));
            }
        }
    }

    /**
     * 记录足迹
     */
    public function doMobileYb_zuji()
    {
        global $_W, $_GPC;
        $zjid = $_GPC['zjid'];
        $uid = $_GPC['uid'] ? $_GPC['uid'] : 0;
        if ($zjid) {
            pdo_update($this->table_yangben_zuji, array('endtime' => time()), array('id' => $zjid));
            pdo_update($this->table_yangben_customer, array('lasttime' => time()), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
        }
        if ($_GPC['flag'] == 1 || $uid < 1) {
            //电脑版不记录
            return;
        }
        $ip_id = $this->insertIp($_GPC['ip']);
        $url_id = $this->insertUrl($_GPC['url']);

        $data = array(
            'uniacid' => $_W['uniacid'],
            'uid' => $uid,
            'ip_id' => $ip_id,
            'url_id' => $url_id,
            'page' => $_GPC['page'],
            'yangben_id' => $_GPC['yb_id'],
            'total_page' => $_GPC['total_page'],
            'is_mobile' => $_GPC['is_mobile'],
            'addtime' => time()
        );

        //判断是否在客户表中
        if (!$this->getFootCustmer($uid)) {
            pdo_insert($this->table_yangben_customer, array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'company_id' => $_GPC['company_id'] ? $_GPC['company_id'] : 0,
                'addtime' => time(),
                'lasttime' => time()
            ));
        }

        pdo_insert($this->table_yangben_zuji, $data);
        die(pdo_insertid());
    }

    private function getFootCustmer($uid)
    {
        global $_W, $_GPC;
        return pdo_fetch("select * from " . tablename($this->table_yangben_customer) . " where uniacid=:uniacid and uid=:uid limit 1", array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
    }

    private function getIpId($ip)
    {
        return pdo_getcolumn($this->table_yangben_ip, array('ip' => $ip), 'id');
    }

    private function insertIp($ip)
    {
        $ip_id = $this->getIpId($ip);
        if ($ip_id) {
            return $ip_id;
        }
        pdo_insert($this->table_yangben_ip, array('ip' => $ip));
        return pdo_insertid();
    }

    private function getUrlId($url)
    {
        return pdo_getcolumn($this->table_yangben_url, array('url' => $url), 'id');
    }

    private function insertUrl($url)
    {
        $url_id = $this->getUrlId($url);
        if ($url_id) {
            return $url_id;
        }
        pdo_insert($this->table_yangben_url, array('url' => $url));
        return pdo_insertid();
    }

    /**
     * 查查是否有权限访问足迹
     * @return bool
     */
    private function checkAuth()
    {
        global $_W, $_GPC;
        $uid = $this->_fans['id'] ? $this->_fans['id'] : $_GPC['wx_uid'];
        $uids = pdo_fetch("select * from " . tablename($this->table_yangben_setting) . " where uniacid=:uniacid and item='uid' limit 1", array(':uniacid' => $_W['uniacid']));
        $uid_list = explode("|", $uids['value']);

        if (in_array($uid, $uid_list)) {
            return true;
        }
        message("您没有权限访问，请在后台配置。", '', 'error');
    }

    public function doMobileYb_foot()
    {
        global $_W, $_GPC;

        //$this->checkPlatform();

        $this->checkAuth();

        $ops = array('info', 'display', 'doajax');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            //获取用户id
            $fans_id = $this->_fans['id'] ? $this->_fans['id'] : $_GPC['wx_uid'];
            include $this->template('foot_list');
        } elseif ($op == 'info') {
            $uid = $_GPC['id'];
            if ($_W['account']['type'] == 4) {
                $member = pdo_fetch("select * from " . tablename("mc_members") . " where uid=:uid limit 1", array(':uid' => $uid));
            } else {

                $member = pdo_fetch("select * from " . tablename($this->table_yangben_fans) . " where id=:uid limit 1", array(':uid' => $uid));
            }
            $area = '';
            if ($member['nationality']) {
                $area .= $member['nationality'] . '-';
            }
            $province = trim($member['resideprovince'], '省');
            if ($province) {
                $area .= $province . '-';
            }
            $city = trim($member['residecity'], '市');
            if ($city) {
                $area .= $city;
            }
            $area = trim($area, '-');
            $wx_uid = $_GPC['wx_uid'];
            include $this->template('foot_info');
        }
    }

    /**
     * 获取足迹列表
     * @param $type  类型：1、样本，2：名片，3：文章，4：视频
     * @return array
     */
    private function getZujiInfo($type)
    {
        global $_W, $_GPC;

        $uid = $_GPC['id'];
        $condition = "uid=:uid and uniacid=:uniacid and yangben_id > 0 and type={$type} and endtime>0";
        $return_data = array();

        $sql = "SELECT DISTINCT yangben_id FROM (select * from " . tablename($this->table_yangben_zuji) . " where $condition ORDER BY addtime desc) as a ";
        $url_list = pdo_fetchall($sql, array(':uid' => $uid, ':uniacid' => $_W['uniacid']));

        $yids = array();
        if ($type == 1){
            $fans_id = $this->_fans['id'] ? $this->_fans['id'] : $_GPC['wx_uid'];
            $yids = array();
            //获取关联的样本
            $yangben_ids = pdo_fetchall("select yangben_id from " . tablename($this->table_yangben_related_fans) . " where uniacid=:uniacid and fans_id=:fans_id", array(
                ':uniacid' => $_W['uniacid'],
                ':fans_id' => $fans_id,
            ));
            if (count($yangben_ids) > 0){
                foreach ($yangben_ids as $k => $v){
                    $yids[] = $v['yangben_id'];
                }
            }
        }


        foreach ($url_list as $k => $v) {
            //$data = pdo_fetch("select * from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id order by id desc", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id']));

            //获取总时长
            $list = pdo_fetchall("select * from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id and type=:type and endtime>0 order by id desc", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id'], ':type' => $type));
            $data = $list[0];
            if ($type == 1) {
                //获取浏览的电子样本
                //获取样布名称
                $yangben = pdo_get($this->table_yangben, array('id' => $data['yangben_id']), array('title'));
                if (!$yangben) {
                    //判断是否存在电子样本
                    continue;
                }

                //判断是否关联样本
                if (count($yids) > 0){
                    if (!in_array($v['yangben_id'], $yids)){
                        continue;
                    }
                }

                //获取样本信息
                //查看总页数
                $view_page_count = pdo_fetchcolumn("select count(*) from (SELECT  COUNT(*)  FROM " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id and endtime>0 group by `page`) as a", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id']));

                $offset_time = 0;
                $time_a = array();
                foreach ($list as $key => $value) {
                    $offset = $value['endtime'] - $value['addtime'];
                    $offset = $offset > 0 ? $offset : 0;
                    $time_a[] = array(
                        "page" => $value['page'],
                        "time" => $offset
                    );
                    $offset_time += $offset;
                }

                $a = $q = array();
                foreach ($time_a as $i => $j) {
                    if (!in_array($j['page'], $a)) {
                        $a[] = $j['page'];
                        $q[$j['page']] = $j['time'];
                    } else {
                        $q[$j['page']] += $j['time'];
                    }
                }

                $time_a = array();
                foreach ($q as $i => $j) {
                    $time_a[] = array(
                        "page" => $i,
                        "time" => $j
                    );
                }

                foreach ($time_a as $key => $row) {
                    $volume[$key] = $row['time'];
                }
                array_multisort($volume, SORT_DESC, $time_a);
                $three_time = 0;
                foreach ($time_a as $i => $j) {
                    if ($i < 3) {
                        $three_time += $j['time'];
                    }
                }
                $percent = array();
                foreach ($time_a as $i => $j) {
                    if ($i < 3) {
                        $percent[] = array(
                            "page" => $j['page'],
                            "rate" => sprintf("%.4f", $j['time'] / $three_time) * 100 . '%'
                        );
                    }
                }
                $msg = "浏览了你的《{$yangben['title']}》，共浏览了{$view_page_count}页，浏览时长{$this->getTime($offset_time)}，";
                foreach ($percent as $i => $j) {
                    $msg .= "第" . $j['page'] . "页兴趣度({$j['rate']})，";
                }
                $msg = trim($msg, '，');
            } elseif ($type == 2) {
                //获取电子样本足迹详情
                //获取名片
                $mingpian = pdo_get($this->table_yangben_mingpian, array('id' => $data['yangben_id']), array('name'));
                if (!$mingpian) {
                    //判断是否存在电子名片
                    continue;
                }
                $msg = "浏览了{$mingpian['name']}的电子名片";
            } elseif ($type == 3) {
                //获取文章足迹详情
                $article = pdo_get($this->table_yangben_article, array('id' => $data['yangben_id']), array('title'));
                if (!$article) {
                    //判断是否存在电子名片
                    continue;
                }
                $msg = "浏览了文章：{$article['title']}";
            } elseif ($type == 4) {
                //获取视频足迹详情
                $video = pdo_get($this->table_yangben_video, array('id' => $data['yangben_id']), array('title'));
                if (!$video) {
                    continue;
                }
                $msg = "浏览了视频：{$video['title']}";
            }
            $url_list[$k]['msg'] = $msg . "。";
            $url_list[$k]['num'] = count($list);
            $url_list[$k]['time'] = date("Y-m-d H:i:s", $data['addtime']);
            $return_data[] = $url_list[$k];
        }
        return $return_data;
    }

    /**
     * 足迹详情
     */
    public function doMobileYb_zuji_info()
    {
        global $_W, $_GPC;
        $foot_info = array();
        $yangben = $this->getZujiInfo(1);
        //var_dump($yangben);
        $mingpian = $this->getZujiInfo(2);
        //var_dump($mingpian);
        $article = $this->getZujiInfo(3);
        //var_dump($article);
        $video = $this->getZujiInfo(4);
        $foot_info = array_merge($foot_info, $yangben);
        $foot_info = array_merge($foot_info, $mingpian);
        $foot_info = array_merge($foot_info, $article);
        $foot_info = array_merge($foot_info, $video);
        //排序
        $foot_info = $this->arraySort($foot_info, 'time');
        //var_dump(array('data' => $foot_info, 'pageCount' => count($foot_info)));
        die(json_encode(array('data' => $foot_info, 'pageCount' => count($foot_info))));
    }


    /**
     * 足迹详情备份
     */
    public function doMobileYb_zuji_info_()
    {
        global $_W, $_GPC;
        $uid = $_GPC['id'];
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 10; // 设置分页大小
        $offset = ($pageindex - 1) * $pagesize;
        $condition = "uid=:uid and uniacid=:uniacid and yangben_id > 0";
        $sql = "SELECT DISTINCT yangben_id FROM (select * from " . tablename($this->table_yangben_zuji) . " where $condition ORDER BY addtime desc) as a ";
        $pageCount = count(pdo_fetchall($sql, array(':uid' => $uid, ':uniacid' => $_W['uniacid'])));
        $sql .= " LIMIT {$offset},{$pagesize}";
        $url_list = pdo_fetchall($sql, array(':uid' => $uid, ':uniacid' => $_W['uniacid']));
        foreach ($url_list as $k => $v) {
            $data = pdo_fetch("select * from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id order by id desc", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id']));
            //获取样本信息
            //查看总页数
            $view_page_count = pdo_fetchcolumn("select count(*) from (SELECT  COUNT(*)  FROM " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id group by `page`) as a", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id']));
            //获取总时长
            $list = pdo_fetchall("select * from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and  uid=:uid and yangben_id=:yangben_id", array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':yangben_id' => $v['yangben_id']));
            $offset_time = 0;
            $time_a = array();
            foreach ($list as $key => $value) {
                $offset = $value['endtime'] - $value['addtime'];
                $offset = $offset > 0 ? $offset : 0;
                $time_a[] = array(
                    "page" => $value['page'],
                    "time" => $offset
                );
                $offset_time += $offset;
            }
            //获取样布名称
            $yangben = pdo_get($this->table_yangben, array('id' => $data['yangben_id']), array('title'));


            $a = $q = array();
            foreach ($time_a as $i => $j) {
                if (!in_array($j['page'], $a)) {
                    $a[] = $j['page'];
                    $q[$j['page']] = $j['time'];
                } else {
                    $q[$j['page']] += $j['time'];
                }
            }

            $time_a = array();
            foreach ($q as $i => $j) {
                $time_a[] = array(
                    "page" => $i,
                    "time" => $j
                );
            }

            foreach ($time_a as $key => $row) {
                $volume[$key] = $row['time'];
            }
            array_multisort($volume, SORT_DESC, $time_a);
            $three_time = 0;
            foreach ($time_a as $i => $j) {
                if ($i < 3) {
                    $three_time += $j['time'];
                }
            }
            $rate = array();
            $percent = array();
            foreach ($time_a as $i => $j) {
                if ($i < 3) {
                    $percent[] = array(
                        "page" => $j['page'],
                        "rate" => sprintf("%.4f", $j['time'] / $three_time) * 100 . '%'
                    );
                }
            }
            $msg = "浏览了你的《{$yangben['title']}》，共浏览了{$view_page_count}页，浏览时长{$this->getTime($offset_time)}，";
            foreach ($percent as $i => $j) {
                $msg .= "第" . $j['page'] . "页兴趣度({$j['rate']})，";
            }
            $url_list[$k]['msg'] = trim($msg, '，') . "。";

            $url_list[$k]['num'] = count($list);
            $url_list[$k]['time'] = date("Y-m-d H:i:s", $data['addtime']);
        }

        die(json_encode(array('data' => $url_list, 'pageCount' => $pageCount)));
    }

    public function getTime($offset_time)
    {
        $offset_time = $offset_time > 0 ? $offset_time : 1;
        if ($offset_time > 60) {
            $fen = floor($offset_time / 60);
            $miao = $offset_time - $fen * 60;
            return $fen . "分" . $miao . "秒";
        } else {
            return $offset_time . '秒';
        }
    }

    //获取足迹用户列表
    public function doMobileYb_get()
    {
        global $_W, $_GPC;
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 20; // 设置分页大小
        $offset = ($pageindex - 1) * $pagesize;

        //判断当前用户是否关联了样本(关联了样本的，只显示关联的样本的足迹)
        $yangben_ids = pdo_fetchall("select yangben_id from " . tablename($this->table_yangben_related_fans) . " where uniacid=:uniacid and fans_id=:fans_id", array(
            ':uniacid' => $_W['uniacid'],
            ':fans_id' => $_GPC['fans_id'],
        ));
        $uid_str = "";
        if (count($yangben_ids) > 0) {
            $yangben_id_str = "";
            foreach ($yangben_ids as $k => $v) {
                $yangben_id_str .= $v['yangben_id'] . ',';
            }
            $yangben_id_str = trim($yangben_id_str, ',');
            //获取浏览记录的用户id
            $relate_user = pdo_fetchall("select uid from " . tablename($this->table_yangben_zuji) . " where yangben_id in ($yangben_id_str) and uid>0 and `type`=1 group by uid");
            foreach ($relate_user as $k => $v) {
                $uid_str .= $v['uid'] . ',';
            }
            $uid_str = trim($uid_str, ',');
        }

        $where = "c.uid > 0 and c.uniacid=:uniacid";
        if ($uid_str) {
            $where .= " and c.uid in ($uid_str)";
        }

        if ($_W['account']['type'] == 4) {
            //获取当前用户所属公司id
            $company_ids = $this->getCompanyIdByFansId($_GPC['fans_id']);

            if ($company_ids) {
                //当前查看足迹用户允许查看得公司id
                $where .= " and c.company_id in ($company_ids)";
            }else{
                //没有关联公司，获取足迹用户
                if (empty($uid_str)) {

                }else{
                    //关联了样本，名片模板数据没有
                    /* 处理关联样本用户时间排序
                    $sql = "select m.uid as id, m.nickname, m.avatar, mobile, addtime as lasttime from (select * from ".tablename($this->table_yangben_zuji)." where uid > 0 and uniacid=:uniacid and uid in ($uid_str) order by id desc ) as c left join " . tablename("mc_members") . " as m on m.uid = c.uid  where 1  group by c.uid order by c.addtime desc";
                    $z_all = pdo_fetchall($sql,  array(':uniacid' => $_W['uniacid']));
                    $new = array();
                    $data = pdo_fetchall($sql." limit $offset, $pagesize", array(':uniacid' => $_W['uniacid']));
                    foreach ($data as $k => $v) {
                        $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and uid=:uid and `type`=1", array(':uniacid' => $_W['uniacid'], ':uid' => $v['id']));
                        if ($count==0){
                            continue;
                        }
                        //获取关联最后访问时间
                        $last = pdo_fetch("select addtime as lasttime from " . tablename($this->table_yangben_zuji) . " where yangben_id in($yangben_id_str) and uniacid=:uniacid and uid=:uid and `type`=1 order by id desc", array(':uniacid' => $_W['uniacid'], ':uid' => $v['id']));
                        $v['view'] = $count;
                        $v['lasttime'] = $this->time_tran(date("Y-m-d H:i:s", $last['lasttime']));
                        $v['nickname'] = $v['nickname'] ? $v['nickname'] : '	未完善';
                        $new[] = $v;
                    }
                    die(json_encode(array('data' => $new, 'pageCount' => count($z_all))));
                    */
                }
            }

            $pageCount = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_customer) . " as c left join " . tablename("mc_members") . " as m on m.uid = c.uid where $where", array(':uniacid' => $_W['uniacid']));

            $data = pdo_fetchall("select m.uid as id, m.nickname, m.avatar, mobile, c.lasttime from " . tablename($this->table_yangben_customer) . " as c left join " . tablename("mc_members") . " as m on m.uid = c.uid  where $where order by  c.lasttime desc limit $offset, $pagesize", array(':uniacid' => $_W['uniacid']));

        } else {
            $pageCount = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_customer) . " as c left join " . tablename($this->table_yangben_fans) . " as m on m.id = c.uid where $where", array(':uniacid' => $_W['uniacid']));
            $data = pdo_fetchall("select m.id, m.nickname, m.avatar, mobile, c.lasttime from " . tablename($this->table_yangben_customer) . " as c left join " . tablename($this->table_yangben_fans) . " as m on m.id = c.uid  where $where order by  c.lasttime desc limit $offset, $pagesize", array(':uniacid' => $_W['uniacid']));
        }

        $new = array();
        foreach ($data as $k => $v) {
            //获取访问次数
            $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_zuji) . " where uniacid=:uniacid and uid=:uid ", array(':uniacid' => $_W['uniacid'], ':uid' => $v['id']));
            if ($count==0){
                continue;
            }
            $v['view'] = $count;
            $v['lasttime'] = $this->time_tran(date("Y-m-d H:i:s", $v['lasttime']));
            $v['nickname'] = $v['nickname'] ? $v['nickname'] : '	未完善';
            $new[] = $v;
        }
        die(json_encode(array('data' => $new, 'pageCount' => $pageCount)));
    }

    /**
     * 获取粉丝所属公司
     * @param $uid
     * @return string
     */
    function getCompanyIdByFansId($uid)
    {
        global $_W, $_GPC;
        $data = pdo_fetchall("select id from " . tablename($this->table_yangben_company) . " where locate($uid, uid) > 0 and uniacid={$_W['uniacid']}");
        $ids = array();
        foreach ($data as $k => $v) {
            $ids[] = $v['id'];
        }
        return implode(',', $ids);
    }

    /**
     * 时间转换
     * @param $the_time
     * @return false|string
     */
    public function time_tran($the_time)
    {
        $now_time = date("Y-m-d H:i:s", time());
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return date("Y-m-d H:i", $show_time);
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) { // 3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return date("Y-m-d H:i", $show_time);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取用户信息
     * https://wx.hmzb.cn/app/index.php?i=2&c=entry&uid=306&do=yb_user_info&m=we7_bybook
     */
    public function doMobileYb_user_info()
    {
        global $_GPC, $_W;
        $type = $_W['account']['type'];
        $uid = $_GPC['uid'];
        $where = ' WHERE uniacid=' . $_W['uniacid'];
        if ($type == 1) {
            //公众号
            $where .= " and id=$uid";
            $sql = 'SELECT * FROM ' . tablename($this->table_yangben_fans) . " {$where} ";
            $user = pdo_fetch($sql);
        } elseif ($type == 4) {
            //小程序
            $where .= " and uid=$uid";
            $sql = 'SELECT * FROM ' . tablename('mc_members') . " {$where}";
            $user = pdo_fetch($sql);
        }
        die(json_encode($user ? $user : array()));
    }

    public function doMobileYb_get_fans()
    {
        global $_GPC, $_W;
    }


    //================================公众号结束=================================================


    //================================自定义后台开始==============================================
    public function doMobileYb_admin()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        //获取我的样本
        $id = $_SESSION['admin']['id'];

        $yb = pdo_fetchall("select y.*,rf.id as rid from " . tablename($this->table_yangben_related_fans) . " as rf left join " . tablename($this->table_yangben) . " as y on y.id = rf.yangben_id where rf.fans_id=:fans_id and rf.uniacid=:uniacid", array(':fans_id' => $id, ':uniacid' => $_W['uniacid']));

        include $this->template('admin_index');
    }

    public function doMobileYb_admin_video()
    {
        global $_W, $_GPC;

        $this->checkLogin();

        $ops = array('edit', 'display', 'delete');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            include $this->template('admin_video');
        } elseif ($op == 'edit') {
            $video_id = $id = $_GPC['id'];
            if (!empty($video_id)) {
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_video) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $video_id, ':uniacid' => $_W['uniacid']);
                $video = pdo_fetch($sql, $params);
            }
            if ($_POST) {
                $data = $_GPC['video'];
                empty($data['title']) && message('请填写视频名称');
                $data['updatetime'] = TIMESTAMP;
                if ($data['type'] == 1) {
                    $data['yangben_id'] = $data['article_id'];
                }
                unset($data['article_id']);
                if (!$video_id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = TIMESTAMP;

                    $ret = pdo_insert($this->table_yangben_video, $data);
                } else {
                    //var_dump($data);exit();
                    $ret = pdo_update($this->table_yangben_video, $data, array('id' => $video_id));
                }

                if (!empty($ret)) {
                    message('视频保存成功', $this->createMobileUrl('yb_admin_video', array('op' => 'display')), 'success');
                } else {
                    message('视频保存失败');
                }
            }
            $yb = pdo_getall($this->table_yangben, array("uniacid" => $_W['uniacid'], 'is_display' => 1));
            $article = pdo_getall($this->table_yangben_article, array('uniacid' => $_W['uniacid'], 'is_display' => 1), array('id', 'title'), '', array('id desc'));
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_video_category) . " where is_display=1 and uniacid=:uniacid and company_id=:company_id order by `sort` asc", array(':uniacid' => $_W['uniacid'], ':company_id' => $_SESSION['company']['id']));

            include $this->template('admin_video_edit');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定视频');
            }
            $result = pdo_delete($this->table_yangben_video, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除视频成功.', $this->createMobileUrl('yb_admin_video', array('op' => 'display')), 'success');
            } else {
                message('删除视频失败.');
            }
        }
    }

    public function doMobileGet_page()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['yangben_id']);
        if (empty($id)) {
            //message('未找到指定样本');
            die(json_encode(array('code' => -1, 'msg' => '未找到指定样本')));
        } else {
            $page = pdo_getall($this->table_yangben_content, array('yangben_id' => $id), '*', 'id', array('sort asc', 'id asc'));
            $data = array();
            foreach ($page as $k => $v) {
                $v['content'] = tomedia($v['content']);
                $data[] = $v;
            }
            die(json_encode(array('code' => 0, 'data' => $data)));
        }
    }

    public function doMobileYb_get_video()
    {
        global $_W, $_GPC;
        //$arr = $this->room_model->get($start, $limit);
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 10; // 设置分页大小

        $where = ' v.uniacid=:uniacid and v.company_id=' . $_GPC['company_id'];
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        if (!empty($_GPC['keyword'])) {
            $where .= ' AND v.title like :keyword';
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        if (!empty($_GPC['yangben_id'])) {
            $where .= " and v.yangben_id=:yangben_id";
            $params[':yangben_id'] = $_GPC['yangben_id'];
        }

        $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_video) . ' as v where ' . $where;
        $total = pdo_fetchcolumn($sql, $params);

        $sql = "SELECT v.*, c.content, c.name, y.title as ytitle, vc.cat_name FROM " . tablename($this->table_yangben_video) . " as v left join " . tablename($this->table_yangben_content) . " as c on c.id=v.page_id left join " . tablename($this->table_yangben) . " as y on y.id = v.yangben_id left join " . tablename($this->table_yangben_video_category) . " as vc on vc.id=v.category_id where $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
        $video = pdo_fetchall($sql, $params);
        foreach ($video as $k => $v) {
            $video[$k]['thumb'] = tomedia($v['thumb']);
            $video[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
        }
        echo json_encode(array(
            'err' => 0,
            'data' => $video,
            'total' => $total
        ));
    }

    public function doMobileYb_admin_mingpian()
    {
        global $_W, $_GPC;

        $this->checkLogin();

        $ops = array('edit', 'delete', 'display', 'copy', 'qrcode');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            include $this->template('admin_mingpian');
        } elseif ($op == 'edit') {
            $id = $_GPC['id'];
            if (!empty($id)) {
                //编辑
                $sql = 'SELECT * FROM ' . tablename($this->table_yangben_mingpian) . ' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $yb = pdo_fetch($sql, $params);
                if (empty($yb)) {
                    message('未找到指定的名片.', $this->createMobileUrl('yb_mingpian'), 'error');
                }
                //获取图片
                $imgs = pdo_getall($this->table_yangben_mingpian_img, array('mingpian_id' => $id), 'thumb');
                foreach ($imgs as $k => $v) {
                    $yb['imgs'][] = $v['thumb'];
                }
            } else {
                $yb = pdo_fetch("select * from " . tablename($this->table_yangben_company) . " where id=:id", array(':id' => $_SESSION['company']['id']));
                //var_dump($yb);
            }
            if ($_POST) {
                $data = $_GPC['data'];
                //$data['user_id'] = $_SESSION['admin']['uid'];
                if (!$id) {
                    $imgs = $data['imgs'];
                    unset($data['imgs']);
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;

                    //绑定公司的用户id
                    $data['user_id'] = $_SESSION['admin']['id'];

                    $ret = pdo_insert($this->table_yangben_mingpian, $data);
                    if ($ret) {
                        //插入图片
                        $mingpian_id = pdo_insertid();
                        foreach ($imgs as $k => $v) {
                            pdo_insert($this->table_yangben_mingpian_img, array(
                                'mingpian_id' => $id,
                                'thumb' => $v
                            ));
                        }
                    }

                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $imgs = $data['imgs'];
                    unset($data['imgs']);
                    $ret = pdo_update($this->table_yangben_mingpian, $data, array('id' => $id));
                    if ($ret) {
                        //删除名片图片
                        pdo_delete($this->table_yangben_mingpian_img, array('mingpian_id' => $id));
                        //插入图片
                        foreach ($imgs as $k => $v) {
                            pdo_insert($this->table_yangben_mingpian_img, array(
                                'mingpian_id' => $id,
                                'thumb' => $v
                            ));
                        }
                    }
                }
                if (!empty($ret)) {
                    message('名片保存成功', $this->createMobileUrl('yb_admin_mingpian', array('op' => 'display')), 'success');
                } else {
                    message('名片保存失败');
                }
            }

            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_mingpian_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
            include $this->template('admin_mingpian_edit');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定名片');
            }
            $result = pdo_delete($this->table_yangben_mingpian, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除名片成功.', $this->createMobileUrl('yb_admin_mingpian'), 'success');
            } else {
                message('删除名片失败.');
            }
        } elseif ($op == 'copy') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定名片');
            }
            $mingpian_info = pdo_get($this->table_yangben_mingpian, array('id' => $id));
            $imgs = pdo_getall($this->table_yangben_mingpian_img, array('mingpian_id' => $id));
            unset($mingpian_info['id']);
            $mingpian_info['name'] = $mingpian_info['name'] . '_复制';
            $mingpian_info['click_num'] = $mingpian_info['uid'] = $mingpian_info['hits'] = 0;
            $mingpian_info['addtime'] = $mingpian_info['updatetime'] = TIMESTAMP;
            $res = pdo_insert($this->table_yangben_mingpian, $mingpian_info);
            if ($res) {
                $mingpian_id = pdo_insertid();
                if ($imgs) {
                    foreach ($imgs as $k => $v) {
                        pdo_insert($this->table_yangben_mingpian_img, array(
                            'mingpian_id' => $mingpian_id,
                            'thumb' => $v['thumb']
                        ));
                    }
                }
            } else {
                message('复制名片失败.');
            }
            message('复制名片成功.', $this->createMobileUrl('yb_admin_mingpian'), 'success');
        } elseif ($op == 'qrcode') {
            //名片二维码
            header('Content-type: image/jpg');
            echo $this->getWeixinQrcode("we7_bybook/pages/contact/index", "id={$_GPC['id']}");
        }
    }

    public function doMobileYb_get_mingpian()
    {
        global $_W, $_GPC;
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 10; // 设置分页大小

        $where = ' where uniacid=:uniacid and company_id=' . $_GPC['company_id'];
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        if (!empty($_GPC['keyword'])) {
            $where .= ' AND name like :keyword';
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }

        $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_mingpian) . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $sql = "SELECT * FROM " . tablename($this->table_yangben_mingpian) . " $where ORDER BY `id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            $list[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
            $list[$k]['avatar_url'] = tomedia($v['avatar_url']);
        }
        echo json_encode(array(
            'err' => 0,
            'data' => $list,
            'total' => $total
        ));
    }

    public function doMobileYb_admin_article()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        $ops = array('edit', 'delete', 'display', 'comment', 'reply');
        $op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
        if ($op == 'display') {
            include $this->template('admin_article');
        } elseif ($op == 'edit') {
            $id = $_GPC['id'];
            if (!empty($id)) {
                //编辑
                $sql = 'SELECT a.* FROM ' . tablename($this->table_yangben_article) . ' as a  WHERE a.id=:id AND a.uniacid=:uniacid LIMIT 1';
                $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
                $article = pdo_fetch($sql, $params);
                if (empty($article)) {
                    message('未找到指定的文章.', $this->createMobileUrl('yb_admin_article'), 'error');
                }
            }
            if ($_POST) {
                $data = $_GPC['data'];
                if (!$id) {
                    $data['uniacid'] = $_W['uniacid'];
                    $data['addtime'] = $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_insert($this->table_yangben_article, $data);
                } else {
                    $data['updatetime'] = TIMESTAMP;
                    $ret = pdo_update($this->table_yangben_article, $data, array('id' => $id));
                }
                if (!empty($ret)) {
                    message('文章保存成功', $this->createMobileUrl('yb_admin_article', array('op' => 'display')), 'success');
                } else {
                    message('文章保存失败');
                }
            }
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_article_category) . " where is_display=1 and uniacid=:uniacid and company_id=:company_id order by `sort` asc", array(':uniacid' => $_W['uniacid'], ':company_id' => $_SESSION['company']['id']));
            include $this->template('admin_article_edit');
        } elseif ($op == 'delete') {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('未找到指定文章');
            }
            $result = pdo_delete($this->table_yangben_article, array('id' => $id, 'uniacid' => $_W['uniacid']));
            if (intval($result) == 1) {
                message('删除文章成功.', $this->createMobileUrl('yb_admin_article'), 'success');
            } else {
                message('删除文章失败.');
            }
        } elseif ($op == 'comment') {
            $id = $_GPC['id'];
            include $this->template('admin_comment');
        } elseif ($op == 'reply') {
            $comment_id = $_GPC['comment_id'];
            $id = $_GPC['id'];
            if ($_POST) {

                $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_reply) . " where comment_id=:comment_id", array(
                    ':comment_id' => $comment_id
                ));

                if ($count) {
                    pdo_update($this->table_yangben_article_reply, array(
                        'reply_msg' => $_GPC['reply_msg'],
                        'addtime' => time()
                    ), array('comment_id' => $comment_id));
                } else {
                    pdo_insert($this->table_yangben_article_reply, array(
                        'comment_id' => $comment_id,
                        'reply_msg' => $_GPC['reply_msg'],
                        'addtime' => time(),
                    ));
                    pdo_update($this->table_yangben_article_comment, array('is_best' => 1), array('id' => $comment_id));
                }
                message('回复成功', $this->createMobileUrl('yb_admin_article', array('op' => 'comment', 'id' => $id)), 'success');
            }
            $info = pdo_get($this->table_yangben_article_comment, array('id' => $comment_id));
            $reply = pdo_get($this->table_yangben_article_reply, array('comment_id' => $comment_id));
            include $this->template('admin_comment_reply');
        }

    }

    public function doMobileYb_get_comment()
    {
        global $_W, $_GPC;
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 10; // 设置分页大小

        $where = ' c.article_id=:article_id';
        $params = array(
            ':article_id' => $_GPC['id']
        );

        if (!empty($_GPC['keyword'])) {
            $where .= ' AND m.`nickname` like :keyword';
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }

        $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_article_comment) . " as c WHERE " . $where;
        $total = pdo_fetchcolumn($sql, $params);

        $sql = 'SELECT c.*, r.reply_msg as msg, m.nickname, m.avatar FROM ' . tablename($this->table_yangben_article_comment) . " as c left join " . tablename($this->table_yangben_article_reply) . " as r on r.comment_id=c.id left join " . tablename("mc_members") . " as m on m.uid=c.user_id WHERE {$where} ORDER BY c.`id` desc LIMIT " . (($pageindex - 1) * $pagesize) . ',' . $pagesize;
        $comment = pdo_fetchall($sql, $params);
        foreach ($comment as $k => $v) {
            $comment[$k]['addtime'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        echo json_encode(array(
            'err' => 0,
            'data' => $comment,
            'total' => $total
        ));
    }

    public function doMobileYb_get_article()
    {
        global $_W, $_GPC;
        $pageindex = max(intval($_GPC['page']), 1); // 当前页码
        $pagesize = 10; // 设置分页大小

        $where = ' a.uniacid=:uniacid and a.company_id=' . $_GPC['company_id'];
        $params = array(
            ':uniacid' => $_W['uniacid']
        );
        if (!empty($_GPC['keyword'])) {
            $where .= ' AND (a.title like :keyword or c.cat_name like :keyword)';
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }

        $sql = 'SELECT COUNT(*) FROM ' . tablename($this->table_yangben_article) . ' as a left join ' . tablename($this->table_yangben_article_category) . ' as c on c.id=a.category_id WHERE ' . $where;
        $total_ = pdo_fetchcolumn($sql, $params);

        $sql = "SELECT a.*, c.cat_name FROM " . tablename($this->table_yangben_article) . " as a left join " . tablename($this->table_yangben_article_category) . " as c on c.id=a.category_id WHERE $where ORDER BY a.`id` desc LIMIT " . (($pageindex - 1) * $pagesize) . "," . $pagesize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            //获取精选留言数
            $count = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_comment) . " where is_best=1 and article_id=:article_id", array(
                ':article_id' => $v['id']
            ));
            $list[$k]['count'] = $count;

            //获取未精选留言
            $total = pdo_fetchcolumn("select count(*) from " . tablename($this->table_yangben_article_comment) . " where is_best=0 and article_id=:article_id", array(
                ':article_id' => $v['id']
            ));
            $list[$k]['nobest'] = $total;
            $list[$k]['thumb'] = tomedia($v['thumb']);
            $list[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
        }
        echo json_encode(array(
            'err' => 0,
            'data' => $list,
            'total' => $total_
        ));
    }

    private function checkIsMine($yangben_id)
    {
        global $_W, $_GPC;
        $id = $_SESSION['admin']['id'];
        $yb = pdo_get($this->table_yangben_related_fans, array('uniacid' => $_W['uniacid'], 'fans_id' => $id, 'yangben_id' => $yangben_id));
        if ($yb) {
            return true;
        }
        return false;
    }

    /**
     * 删除样本
     */
    public function doMobileYb_delete()
    {
        global $_W, $_GPC;
        $fans_id = $_SESSION['admin']['id'];
        $yangben_id = $_GPC['yangben_id'];
        $res = pdo_delete($this->table_yangben_related_fans, array('fans_id' => $fans_id, 'yangben_id' => $yangben_id, 'uniacid' => $_W['uniacid']));
        if ($res) {
            die(json_encode(array('code' => 0, 'msg' => '删除成功')));
        }
        die(json_encode(array('code' => 1, 'msg' => '删除样本失败')));
    }

    /**
     * 编辑样本
     */
    public function doMobileYb_edit()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        $id = $_GPC['id'];
        if (!$this->checkIsMine($id)) {
            header('Location:' . $this->createMobileUrl('yb_admin'));
        }
        if ($_W['account']['type'] == 4) {
            //获取分类
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where is_display=1 and uniacid=:uniacid and company_id=:company_id order by `sort` asc", array(':uniacid' => $_W['uniacid'], ':company_id' => $_SESSION['company']['id']));
        } else {
            //获取分类
            $category = pdo_fetchall("select * from " . tablename($this->table_yangben_category) . " where is_display=1 and uniacid=:uniacid order by `sort` asc", array(':uniacid' => $_W['uniacid']));
        }

        $yb = pdo_get($this->table_yangben, array('id' => $id));
        //$yb_content = pdo_get($this->table_yangben_content, array('yangben_id' => $id));
        include $this->template('admin_edit');
    }

    /**
     * 目录管理
     */
    public function doMobileYb_contents()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        $id = $_GPC['id'];
        if (!$this->checkIsMine($id)) {
            header('Location:' . $this->createMobileUrl('yb_admin'));
        }
        $contents_id = $_GPC['contents_id'];
        if ($contents_id) {
            $info = pdo_get($this->table_yangben_contents, array('id' => $contents_id));
        }
        //获取目录
        $datas = pdo_getall($this->table_yangben_contents, array('yangben_id' => $id), array(), '', array('page asc'));
        $contents = $this->getContentsCategory($datas);
        include $this->template('admin_edit_contents');
    }

    public function doMobileYb_doeditcontents()
    {
        global $_W, $_GPC;
        $data = $_GPC['data'];
        $data['yangben_id'] = $_GPC['yangben_id'];
        if (!$_GPC['edit_id']) {
            $data['addtime'] = TIMESTAMP;
            pdo_insert($this->table_yangben_contents, $data);
        } else {
            pdo_update($this->table_yangben_contents, $data, array('id' => $_GPC['edit_id']));
        }
        message('', $this->createMobileUrl('yb_contents', array('id' => $_GPC['yangben_id'])), 'success');
    }

    public function doMobileYb_delete_contents()
    {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        if (empty($id)) {
            message('未找到指定目录');
        }
        //判断是否有子级目录
        $data = pdo_get($this->table_yangben_contents, array('parent_id' => $id));
        if ($data) {
            message('存在子级目录，不能删除.', $this->createMobileUrl('yb_contents', array('id' => $_GPC['yangben_id'])), 'error');
        } else {
            $result = pdo_delete($this->table_yangben_contents, array('id' => $id));
            if (intval($result) == 1) {
                message('删除目录成功.', $this->createMobileUrl('yb_contents', array('id' => $_GPC['yangben_id'])), 'success');
            } else {
                message('删除目录失败.');
            }
        }
    }

    /**
     * 内容页
     */
    public function doMobileYb_editcontent()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        $id = $_GPC['id'];
        if (!$this->checkIsMine($id)) {
            header('Location:' . $this->createMobileUrl('yb_admin'));
        }
        $yb = pdo_get($this->table_yangben, array('id' => $id), array('title'));
        $yb_content = pdo_fetchall("select * from " . tablename($this->table_yangben_content) . " where yangben_id=:yangben_id order by `sort` asc, id asc", array(':yangben_id' => $id));
		$is_edit = 1;
		if($this->checkTemplatePlugin(IA_ROOT."/addons/we7_bybook_plugin_edit/lock.txt") == true) {
			$is_edit = 1;
		}
        include $this->template('admin_edit_content');
    }
	
	public function doMobileYb_designer() {
		global $_W, $_GPC;
        $this->checkLogin();
        $id = $_GPC['id'];
        if (!$this->checkIsMine($id)) {
            header('Location:' . $this->createMobileUrl('yb_admin'));
        }
		$id = $_GPC['id'];
		if($_W['isajax']){
			if($this->checkTemplatePlugin(IA_ROOT."/addons/we7_bybook_plugin_edit/lock.txt") == false) {
				//die(json_encode(array('code' => 0, 'msg' => '您还没有安装该插件')));
			}
			
			$md5 = $_GPC['js_md5'];
			$params = htmlspecialchars_decode($_GPC['params']);
			$p = pdo_get($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $md5));
			if($p){
				pdo_update($this->table_yangben_params, array('params' => $params), array('id' => $p['id']));
			}else{
				pdo_insert($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $md5, 'params' => $params));
			}
			die(json_encode(array('code' => 1, 'msg' => '页面参数配置成功')));
		}
		$src = $_GPC['src'];
		$js_md5 = md5($src);
		//获取参数
		$params = pdo_get($this->table_yangben_params, array('yangben_id' => $id, 'md5' => $js_md5));
		$obj_rect = '[]';
		if($params) {
			$obj_rect = $params['params'];
		}
		include $this->template('admin_designer');
	}

    /**
     * 处理内容页
     */
    public function doMobileYb_doeditcontent()
    {
        global $_W, $_GPC;
        $id = $_GPC['yangben_id'];
        pdo_delete($this->table_yangben_content, array("yangben_id" => $id));
        $sort = $_GPC['sort'];
        $level = $_GPC['level'];
        $content = $_GPC['content'];
        $name = $_GPC['title'];
        for ($i = 0; $i < count($content); $i++) {
            $data = array(
                'yangben_id' => $id,
                'name' => $name[$i],
                'level' => $level[$i],
                'sort' => $sort[$i],
                'content' => $content[$i]
            );
            pdo_insert($this->table_yangben_content, $data);
        }
        header('Location:' . $this->createMobileUrl('yb_editcontent', array('id' => $id)));
    }

    /**
     * 文件上传
     */
    public function doWebYb_upload()
    {
        global $_W, $_GPC;
        if (strtolower($_FILES['file']['name']) == 'blob') {
            $_FILES['file']['name'] = time() . ".png";
        }
        $type = $_GPC['type'];
        load()->func('file');
        $result = file_upload($_FILES['file'], $type);
        if ($result['success']) {
            if ($_W['setting']['remote']) {
                file_remote_upload($result['path']);
            }
            $uid = $_W['user']['uid'];
            $data = array(
                'uniacid' => $_W['uniacid'],
                'uid' => $uid,
                'filename' => substr($result['path'], strrpos($result['path'], '/')+1),
                'attachment' => $result['path'],
                'type' => 1,
                'createtime' => time(),
                'group_id' => -1
            );
            pdo_insert("core_attachment", $data);
            die(json_encode(array('code' => 0, 'msg' => '上传成功', 'path' => $result['path'], 'real' => tomedia($result['path']))));
        }
        die(json_encode(array('code' => 1, 'msg' => $result['message'])));
    }
	
	public function doWebYb_upload2() {
		$this->doUpload();
	}
	
	private function doUpload()
	{
		global $_W, $_GPC;
		 //得到文件的名字
        $oldName = $_FILES[$_GPC['name']]['name'];
        //得到文件的mime类型
        $mime = $_FILES[$_GPC['name']]['type'];
        //得到文件的临时文件
        $tmpName = $_FILES[$_GPC['name']]['tmp_name'];
        //得到文件大小
        $size = $_FILES[$_GPC['name']]['size'];
        //得到文件后缀
        $suffix = pathinfo($oldName)['extension'];
		$newName = 'up_' . uniqid() . '.' . $suffix;
		
		$date = date('Y-m-d');
		$dir = MODULE_ROOT . '/template/upload/pdf/'.$date;
		if(!is_dir($dir)){
			mkdirs($dir);
		}
		$path = "/template/upload/pdf/$date/";
		if (is_uploaded_file($tmpName)) {
            if (move_uploaded_file($tmpName, MODULE_ROOT. $path . $newName)) {
                die(json_encode(array('code' => 0, 'msg' => '上传成功', 'path' => $path . $newName, 'real' => MODULE_URL . $path . $newName)));
            } else {
                die(json_encode(array('code' => 1, 'msg' => '移动失败')));
            }
        }
		die(json_encode(array('code' => 1, 'msg' => '不是上传文件')));
	}
	
	public function doMobileYb_upload2()
	{
		$this->doUpload();
	}

    /**
     * 文件上传
     */
    public function doMobileYb_upload()
    {
        global $_W, $_GPC;
        if (strtolower($_FILES['file']['name']) == 'blob') {
            $_FILES['file']['name'] = time() . ".png";
        }
        //var_dump($_FILES['file']);exit();
        $type = $_GPC['type'];
        load()->func('file');
        $result = file_upload($_FILES['file'], $type);
        if ($result['success']) {
            if ($_W['setting']['remote']) {
                file_remote_upload($result['path']);
            }
            die(json_encode(array('code' => 0, 'msg' => '上传成功', 'path' => $result['path'], 'real' => tomedia($result['path']))));
        }
        die(json_encode(array('code' => 1, 'msg' => $result['message'])));
    }

    /**
     * 处理样本编辑
     */
    public function doMobileYb_doedit()
    {
        global $_W, $_GPC;
        $this->checkLogin();
        $data = $_GPC['yb']; // 获取打包值
        $id = $_GPC['yangben_id'];
        $data['updatetime'] = TIMESTAMP;
        $ret = pdo_update($this->table_yangben, $data, array('id' => $id));
        header('Location:' . $this->createMobileUrl('yb_edit', array('id' => $id)));
    }

    public function checkLogin()
    {
        global $_GPC, $_W;
        session_start();
        $admin = igetcookie('admin');
        if (!$admin) {
            header('Location:' . $this->createMobileUrl('yb_login', array('id' => igetcookie('admin_id'))));
        }
        $_SESSION['admin'] = json_decode($admin, true);
        if ($_W['account']['type'] == 4) {
            //判断是否关联
            $company = pdo_fetch("select * from " . tablename($this->table_yangben_company_user) . " where user_id = {$_SESSION['admin']['uid']} and uniacid=" . $_W['uniacid']);
            $_SESSION['is_related'] = 0;
            if ($company) {
                $_SESSION['is_related'] = 1;
                $company_info = pdo_get($this->table_yangben_company, array('id' => $company['company_id']));
                $_SESSION['company'] = $company_info;
            }
            $company_id = $_SESSION['company']['id'];
            $_SESSION['admin']['id'] = $_SESSION['admin']['uid'];
        }
    }

    public function doMobileYb_login()
    {
        global $_W, $_GPC;
        $id = $_GPC['id'] ? $_GPC['id'] : 0;
        include $this->template('admin_login');
    }

    public function doMobileYb_logout()
    {
        isetcookie("admin", "");
        header('Location:' . $this->createMobileUrl('yb_login', array('id' => igetcookie('admin_id'))));
    }

    /**
     * 处理登录
     */
    public function doMobileYb_dologin()
    {
        global $_W, $_GPC;
        $type = $_W['account']['type'];
        $mobile = $_GPC['mobile'];
        $password = $_GPC['password'];
        if (empty($mobile) || empty($password)) {
            die(json_encode(array('code' => 1, 'msg' => '手机号或密码错误')));
        }
        $user = array();
        if ($type == 4) {
            //小程序
            $uid = $_GPC['uid'];
            if (!$uid) {
                die(json_encode(array('code' => 1, 'msg' => '访问的网址不正确，请联系管理员')));
            }
            $user = pdo_get('mc_members', array('uid' => $uid));
            $password = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
            if ($user['mobile'] != $mobile || $user['password'] != $password) {
                die(json_encode(array('code' => 1, 'msg' => '手机号或密码错误')));
            }
            //$_SESSION['admin_id'] = $user['id'] = $uid;
            //设置保存用户id
            isetcookie("admin_id", $uid);
        } elseif ($type == 1 || $type == 3) {
            //公众号
            $user = pdo_get($this->table_yangben_fans, array('mobile' => $mobile, 'password' => md5($password), 'uniacid' => $_W['uniacid']));
        }
        if ($user) {
            //$_SESSION['admin'] = $user;
            isetcookie('admin', json_encode($user));
            die(json_encode(array('code' => 0, 'msg' => '登录成功')));
        }
        die(json_encode(array('code' => 1, 'msg' => '手机号或密码错误')));
    }

    //================================自定义后台结束==============================================

    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys 要排序的键字段
     * @param string $sort 排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    function arraySort($array, $keys, $sort = SORT_DESC)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }

    /**
     * 判断是否为手机端
     * @return bool
     */
    public function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为false,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}