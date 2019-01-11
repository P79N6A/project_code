<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\Userwx;
use app\models\dev\Webunion_user_list;
use yii\data\Pagination;

class FriendController extends SubController {

    public $layout = 'index_n';
    public $colors = array('black', 'greengray', 'red', 'black', 'red');
    public $status = array('已认证', '未认证', '借款中', '已还款', '已逾期');
    public $pageSize = 20;

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    //好友列表(全部)
    public function actionIndex() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $keyword = '';
        $more = '';
        //获得要筛选的好友类型
        $type = $this->get('type');
        //获取一级用户的uid
        $uids_one = User::find()->select('user_id')->where(['from_code' => $user->invite_code])->asArray()->all();
        //获取二级用户的uid
        $uids_two = Webunion_user_list::find()->select('user_id')->distinct()->where(['type' => 2, 'top_user_id' => $user->user_id])->asArray()->all();
        //格式化数组，获取一级和二级的好友人数
        $uids_one = $this->array_column($uids_one, 'user_id');
        $friend_one = count(array_unique($uids_one)); //User::find()->where(['in', 'user_id', $uids_one])->count();
        $uids_two = $this->array_column($uids_two, 'user_id');
        $friend_two = count(array_unique($uids_two)); //User::find()->where(['in', 'user_id', $uids_two])->count();
        $uids = array_merge($uids_one, $uids_two);
        $query = User::find()->where(['in', 'user_id', $uids]);
        if ($type) {
            $query = $this->screen($query, $uids, $type);
        }
        $count = count(array_unique($uids)); //$query->count();
        $max_page = ceil($count / $this->pageSize);
        if ($max_page >= 2) {
            $more = 2;
        }
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
        $friends = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
        //0 已认证；1 未认证；2 借款中；3 已还款；4 已逾期
        foreach ($friends as $key => $val) {
            $result = $this->get_friend_status($val);
            $userwx = Userwx::find()->where(['openid' => $val->openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/webunion/icon.png';
            } else {
                $heads = $userwx->head;
            }
            $val->openid = $heads;
            $val->status = $result['status'];
            $friends[$key] = $val;
        }
        $page = $this->get('page');
        if ($page) {
            $html = '';
            foreach ($friends as $key => $val) {
                $html.='<a href="/background/friend/detail?user_id=' . $val->user_id . '"><section class="list haoyoulist"><img src="' . $val->openid . '" class="icon" /><div class="name">' . $val->realname . '</div><div class="phone">' . substr_replace($val->mobile, '****', 3, 4) . '</div><div class=" ' . $this->colors[$val->status] . '">' . $this->status[$val->status] . '</div></section></a>';
            }
            if ($page < $max_page) {
                $page += 1;
            } else {
                $page = 0;
            }
            echo json_encode(array('data' => $html, 'page' => $page));
            exit;
        }
        $type = $type ? $type : 0;
        $jsinfo = $this->getWxParam();
        $this->layout = "index";
        $returnUrl = '/background/default/index';
        $this->getView()->title = "好友列表";
        return $this->render('index', [
                    'keyword' => $keyword,
                    'more' => $more,
                    'type' => $type,
                    'pages' => $pages,
                    'friends' => $friends,
                    'friend_one' => $friend_one,
                    'friend_two' => $friend_two,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //好友搜索
    public function actionSearch() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $keyword = $this->get('keyword');
        if (!$keyword) {
            return $this->redirect('/background/friend/index');
        }
        // print_r($user);
        //获取一级用户的uid
        $uids_one = User::find()->select('user_id')->where(['from_code' => $user->invite_code])->asArray()->all();
        //获取二级用户的uid
        $uids_two = Webunion_user_list::find()->select('user_id')->distinct()->where(['type' => 2, 'top_user_id' => $user->user_id])->asArray()->all();
        //格式化数组，获取一级和二级的好友人数
        $uids_one = $this->array_column($uids_one, 'user_id');
        $friend_one = count(array_unique($uids_one)); //User::find()->where(['in', 'user_id', $uids_one])->count();
        $uids_two = $this->array_column($uids_two, 'user_id');
        $friend_two = count(array_unique($uids_two)); //User::find()->where(['in', 'user_id', $uids_two])->count();
        $uids = array_merge($uids_one, $uids_two);
        $search_uid1 = User::find()->select('user_id')->where(['in', 'user_id', $uids])->andWhere(['like', 'realname', $keyword])->asArray()->all();
        $search_uid1 = $this->array_column($search_uid1, 'user_id');
        $search_uid2 = User::find()->select('user_id')->where(['in', 'user_id', $uids])->andWhere(['like', 'mobile', $keyword])->asArray()->all();
        $search_uid2 = $this->array_column($search_uid2, 'user_id');
        $search_uids = array_merge($search_uid1, $search_uid2);
        if ($search_uids) {
            $friends = User::find()->andWhere(['in', 'user_id', $search_uids])->all();
        } else {
            $friends = '';
        }
        //0 已认证；1 未认证；2 借款中；3 已还款；4 已逾期
        if ($friends) {
            foreach ($friends as $key => $val) {
                $result = $this->get_friend_status($val);
                $userwx = Userwx::find()->where(['openid' => $val->openid])->one();
                if (empty($userwx) || empty($userwx->head)) {
                    $heads = '/images/webunion/icon.png';
                } else {
                    $heads = $userwx->head;
                }
                $val->openid = $heads;
                $val->status = $result['status'];
                $friends[$key] = $val;
            }
        }

        $jsinfo = $this->getWxParam();
        $this->layout = "index";
        $returnUrl = '/background/default/index';
        $this->getView()->title = "好友列表";
        return $this->render('index', [
                    'keyword' => $keyword,
                    'more' => 0,
                    'type' => 0,
                    'friends' => $friends,
                    'friend_one' => $friend_one,
                    'friend_two' => $friend_two,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    /**
     * 好友筛选
     * type：1 未认证；2 借款中；3 已还款；4 已逾期；5 已认证
     * query： 目前组成的query
     * uids： 当前用户所有的一二级用户id
     */
    public function screen($query, $uids, $type) {
        if (!$uids) {
            return $query;
        }
        $uids_new = array();
        switch ($type) {
            case '1':
                $uids = array_unique($uids);
                foreach ($uids as $key => $val) {
                    $loaninfo = User_loan::find()->where(['user_id' => $val])->orderBy('create_time desc')->one();
                    if ($loaninfo && $loaninfo->status == 12) {
                        $uids_new[] = $val;
                    } elseif ($loaninfo) {
                        $repaytime = strtotime($loaninfo->last_modify_time) + 3 * 24 * 3600;
                        if ($loaninfo->status == 8 && $repaytime > time()) {
                            $uids_new[] = $val;
                        }
                    } elseif ($loaninfo && in_array($loaninfo->status, ['6', '9', '11', '13'])) {
                        $uids_new[] = $val;
                    }
                }
                return $query->andWhere(['not in', 'user_id', $uids_new])->andWhere(['not in', 'status', [3]]);
            case '2':
                $uids = array_unique($uids);
                foreach ($uids as $key => $val) {
                    $loaninfo = User_loan::find()->where(['user_id' => $val])->orderBy('create_time desc')->one();
                    if ($loaninfo && in_array($loaninfo->status, ['6', '9', '11', '13'])) {
                        $uids_new[] = $val;
                    }
                }
                return $query->andWhere(['in', 'user_id', $uids_new]);
            case '3':
                $uids = array_unique($uids);
                foreach ($uids as $key => $val) {
                    $loaninfo = User_loan::find()->where(['user_id' => $val])->orderBy('create_time desc')->one();
                    if ($loaninfo) {
                        $repaytime = strtotime($loaninfo->last_modify_time) + 3 * 24 * 3600;
                        if ($loaninfo->status == 8 && $repaytime > time()) {
                            $uids_new[] = $val;
                        }
                    }
                }
                return $query->andWhere(['in', 'user_id', $uids_new]);
            case '4':
                $uids = array_unique($uids);
                foreach ($uids as $key => $val) {
                    $loaninfo = User_loan::find()->where(['user_id' => $val])->orderBy('create_time desc')->one();
                    if ($loaninfo && $loaninfo->status == 12) {
                        $uids_new[] = $val;
                    }
                }
                return $query->andWhere(['in', 'user_id', $uids_new]);
            case '5':
                $uids = array_unique($uids);
                foreach ($uids as $key => $val) {
                    $loaninfo = User_loan::find()->where(['user_id' => $val])->orderBy('create_time desc')->one();
                    if ($loaninfo && $loaninfo->status == 12) {
                        $uids_new[] = $val;
                    } elseif ($loaninfo) {
                        $repaytime = strtotime($loaninfo->last_modify_time) + 3 * 24 * 3600;
                        if ($loaninfo->status == 8 && $repaytime > time()) {
                            $uids_new[] = $val;
                        }
                    } elseif ($loaninfo && in_array($loaninfo->status, ['6', '9', '11', '13'])) {
                        $uids_new[] = $val;
                    }
                }
                return $query->andWhere(['not in', 'user_id', $uids_new])->andWhere(['status' => 3]);
            default:
                return $query;
        }
    }

    //获得好友当前所处的状态
    public function get_friend_status($user) {
        $amount = 0;
        $friend_status = 0;
        //0 已认证；1 未认证；2 借款中；3 已还款；4 已逾期
        $loaninfo = User_loan::find()->where(['user_id' => $user->user_id])->orderBy('create_time desc')->one();
        if (empty($loaninfo) && !empty($user)) {
            if ($user->status == 3) {
                $friend_status = 0;
            } else {
                $friend_status = 1;
            }
        } else {
            if (in_array($loaninfo->status, [6, 9, 11, 13])) {
                $friend_status = 2;
                $amount = $loaninfo->amount;
            } elseif ($loaninfo->status == 12) {
                $amount = $loaninfo->amount;
            } elseif ($loaninfo->status == 8) {
                if (time() - strtotime($loaninfo->last_modify_time) < 3 * 24 * 3600) {
                    $friend_status = 3;
                } else {
                    if ($user->status == 3) {
                        $friend_status = 0;
                    } else {
                        $friend_status = 1;
                    }
                }
            } else {
                if ($user->status == 3) {
                    $friend_status = 0;
                } else {
                    $friend_status = 1;
                }
            }
        }
        return array('status' => $friend_status, 'amount' => $amount);
    }

    //好友详情
    public function actionDetail() {
        $users = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($users)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user_id = $this->get('user_id');
        $user = User::find()->where(['user_id' => $user_id])->one();
        $result = $this->get_friend_status($user);
        $userwx = Userwx::find()->where(['openid' => $user->openid])->one();
        $this->getView()->title = '好友详情';
        $jsinfo = $this->getWxParam();
        return $this->render('detail', [
                    'userinfo' => $user,
                    'userwx' => $userwx,
                    'amount' => $result['amount'],
                    'status' => $result['status'],
                    'jsinfo' => $jsinfo
        ]);
    }

    //一级好友列表
    public function actionFriendone() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $more = 0;
        $query = User::find()->where(['from_code' => $user->invite_code]);
        $count = $query->count();
        $max_page = ceil($count / $this->pageSize);
        if ($max_page >= 2) {
            $more = 2;
        }
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
        $friends = $query->offset($pages->offset)->limit($pages->limit)->all();
        foreach ($friends as $key => $val) {
            $result = $this->get_friend_status($val);
            $userwx = Userwx::find()->where(['openid' => $val->openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/webunion/icon.png';
            } else {
                $heads = $userwx->head;
            }
            $val->openid = $heads;
            $val->status = $result['status'];
            $friends[$key] = $val;
        }
        $page = $this->get('page');
        if ($page) {
            $html = '';
            foreach ($friends as $key => $val) {
                $html.='<a href="/background/friend/detail?user_id=' . $val->user_id . '"><section class="list"><img src="' . $val->openid . '" alt="" class="icon" /><div class="name">' . $val->realname . '</div><div class="phone">' . substr_replace($val->mobile, '****', 3, 4) . '</div><div class="state ';
                if ($val->status == 1) {
                    $html.= 'greengray';
                }
                $html .= '">' . $this->status[$val->status] . '</div></section></a>';
            }
            if ($page < $max_page) {
                $page += 1;
            } else {
                $page = 0;
            }
            echo json_encode(array('data' => $html, 'page' => $page));
            exit;
        }
        $this->getView()->title = '好友列表';
        $returnUrl = '/background/friend/index';
        $jsinfo = $this->getWxParam();
        return $this->render('friendone', [
                    'more' => $more,
                    'friends' => $friends,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //二级好友列表
    public function actionFriendtwo() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $more = 0;
        //获取二级用户的uid
        $uids = Webunion_user_list::find()->select('user_id')->distinct()->where(['type' => 2, 'top_user_id' => $user->user_id])->asArray()->all();
        //格式化数组，获取二级的好友人数
        $uids = $this->array_column($uids, 'user_id');
        $query = User::find()->where(['in', 'user_id', $uids]);
        $count = count(array_unique($uids));//$query->count();
        $max_page = ceil($count / $this->pageSize);
        if ($max_page >= 2) {
            $more = 2;
        }
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => $this->pageSize]);
        $friends = $query->offset($pages->offset)->limit($pages->limit)->all();
        foreach ($friends as $key => $val) {
            $result = $this->get_friend_status($val);
            if (empty($val->openid)) {
                $heads = '/images/webunion/icon.png';
            } else {
                $userwx = Userwx::find()->where(['openid' => $val->openid])->one();
                if (empty($userwx) && !isset($userwx)) {
                    $heads = '/images/webunion/icon.png';
                } else {
                    $heads = $userwx->head;
                }
            }
            $val->openid = $heads;
            $val->status = $result['status'];
            $friends[$key] = $val;
        }
        $page = $this->get('page');
        if ($page) {
            $html = '';
            foreach ($friends as $key => $val) {
                $html.='<a href="/background/friend/detail?user_id=' . $val->user_id . '"><section class="list"><img src="' . $val->openid . '" alt="" class="icon" /><div class="name">' . $val->realname . '</div><div class="phone">' . substr_replace($val->mobile, '****', 3, 4) . '</div><div class="state ';
                if ($val->status == 1) {
                    $html.= 'greengray';
                }
                $html .= '">' . $this->status[$val->status] . '</div></section></a>';
            }
            if ($page < $max_page) {
                $page += 1;
            } else {
                $page = 0;
            }
            echo json_encode(array('data' => $html, 'page' => $page));
            exit;
        }
        $returnUrl = '/background/friend/index';
        $this->getView()->title = '好友列表';
        $jsinfo = $this->getWxParam();
        return $this->render('friendtwo', [
                    'more' => $more,
                    'friends' => $friends,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    /**
     * getpost 返回get,post的数据，简单封装下
     */
    public function get($name = null, $defaultValue = null) {
        $v = Yii::$app->request->get($name, $defaultValue);
        $v = $v ? $this->new_trim($v) : $v;
        return $v;
    }

    public function post($name = null, $defaultValue = null) {
        $v = Yii::$app->request->post($name, $defaultValue);
        $v = $this->new_trim($v);
        return $v;
    }

    public function getParam($name, $defaultValue = null) {
        $v = $this->get($name);
        if (is_null($v)) {
            $v = $this->post($name, $defaultValue);
        }
        $v = $v ? $this->new_trim($v) : $v;
        return $v;
    }

    public function isPost() {
        return Yii::$app->request->isPost;
    }

    /**
     * 去除空格
     *
     * @param str | array $string
     * @return 同输入
     */
    public function new_trim($string) {
        if (!is_array($string))
            return trim($string);
        foreach ($string as $key => $val) {
            $string[$key] = self::new_trim($val);
        }
        return $string;
    }

    public function array_column($input, $columnKey, $indexKey = NULL) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull = (is_null($indexKey)) ? TRUE : FALSE;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? TRUE : FALSE;
        $result = array();

        foreach ((array) $input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }

            $result[$key] = $tmp;
        }

        return $result;
    }

}
