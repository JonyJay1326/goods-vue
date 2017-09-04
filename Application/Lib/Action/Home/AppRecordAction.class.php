<?php

class AppRecordAction extends BaseAction
{
    private $startTime = ' 00:00:00';
    private $endTime = ' 23:59:59';

    public function _initialize()
    {
//   定义变量
        $HI_PATH = '../Public/';
        $this->assign('HI_PATH', $HI_PATH);
        ini_set('date.timezone', 'Asia/Shanghai');
    }

    /**
     * 用户搜索记录
     */
    public function searchRecordList()
    {
        $searchRecordModel = D('SearchRecord');
        $cdModel = D('ZZmscmncd');#渠道model
        $cdList = $cdModel->getCdValues('销售渠道');
        import('ORG.Util.Page');// 导入分页类
        $params = [];
        $where['CD'] = $cdCode = 'N000831400';#暂时只有kr
        $page_num = empty(I('get.page_num')) ? 15 : I('get.page_num');
        if ($this->isPost()) {
            if (I('post.userId')) {
                $params['userId'] = $where['userId'] = I('post.userId');
            }
            if (I('post.keyword')) {
                $params['keyword'] = $where['keyword'] = I('post.keyword');
            }
            if (I('post.startDate') && I('post.endDate')) {
                $where['createDate'] = array(array('gt', I('post.startDate') . $this->startTime), array('lt', I('post.endDate') . $this->endTime));
                $params['startDate'] = I('post.startDate');
                $params['endDate'] = I('post.endDate');

            } elseif (I('post.startDate')) {
                $where['createDate'] = array('gt', I('post.startDate') . $this->startTime);
                $params['startDate'] = I('post.startDate');
            } elseif (I('post.endDate')) {
                $where['createDate'] = array('lt', I('post.endDate') . $this->endTime);
                $params['endDate'] = I('post.endDate');
            }
            if (I('post.CD')) {
                $cdCode = $where['CD'] = I('post.CD');
            }
        }
        if ($this->isGet()) {
            if (I('get.userId')) {
                $params['userId'] = $where['userId'] = I('get.userId');
            }
            if (I('get.keyword')) {
                $params['keyword'] = $where['keyword'] = I('get.keyword');
            }
            if (I('get.startDate') && I('get.endDate')) {
                $where['createDate'] = array(array('gt', I('get.startDate') . $this->startTime), array('lt', I('get.endDate') . $this->endTime));
                $params['startDate'] = I('get.startDate');
                $params['endDate'] = I('get.endDate');
            } elseif (I('get.startDate')) {
                $where['createDate'] = array('gt', I('get.startDate') . $this->startTime);
                $params['startDate'] = I('get.startDate');
            } elseif (I('get.endDate')) {
                $where['createDate'] = array('lt', I('get.endDate') . $this->endTime);
                $params['endDate'] = I('get.endDate');
            }
            if (I('get.CD')) {
                $cdCode = $where['CD'] = I('get.CD');
            }
        }

        $count = $searchRecordModel->where($where)->count();
        $page = new Page($count, $page_num);
        $page->page_num = $page_num;
        $page->parameter = http_build_query($params);
        $show = $page->show();
        $result = $searchRecordModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('createDate desc')->select();
        $this->assign('cdList', json_encode(array_values($cdList)));
        $this->assign('result', json_encode($result));
        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('cdCode', $cdCode);
        $this->assign('all_channel', json_encode($cdList));
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 用户商品浏览记录
     */
    public function userViewProductList()
    {
        $userViewProductModel = D('UserViewProduct');
        $cdModel = D('ZZmscmncd');#渠道model
        $cdList = $cdModel->getCdValues('销售渠道');
        import('ORG.Util.Page');// 导入分页类
        $params = [];
        $where['CD'] = $cdCode = 'N000831400';#暂时只有kr
        $page_num = empty(I('get.page_num')) ? 15 : I('get.page_num');
        if ($this->isPost()) {
            if (I('post.userId')) {
                $params['userId'] = $where['userId'] = I('post.userId');
            }
            if (I('post.productId')) {
                $params['productId'] = $where['productId'] = I('post.productId');
            }
            if (I('post.startDate') && I('post.endDate')) {
                $where['createDate'] = array(array('gt', I('post.startDate') . $this->startTime), array('lt', I('post.endDate') . $this->endTime));
                $params['startDate'] = I('post.startDate');
                $params['endDate'] = I('post.endDate');

            } elseif (I('post.startDate')) {
                $where['createDate'] = array('gt', I('post.startDate') . $this->startTime);
                $params['startDate'] = I('post.startDate');
            } elseif (I('post.endDate')) {
                $where['createDate'] = array('lt', I('post.endDate') . $this->endTime);
                $params['endDate'] = I('post.endDate');
            }
            if (I('post.CD')) {
                $cdCode = $where['CD'] = I('post.CD');
            }
        }
        if ($this->isGet()) {
            if (I('get.userId')) {
                $params['userId'] = $where['userId'] = I('get.userId');
            }
            if (I('get.productId')) {
                $params['productId'] = $where['productId'] = I('get.productId');
            }
            if (I('get.startDate') && I('get.endDate')) {
                $where['createDate'] = array(array('gt', I('get.startDate') . $this->startTime), array('lt', I('get.endDate') . $this->endTime));
                $params['startDate'] = I('get.startDate');
                $params['endDate'] = I('get.endDate');
            } elseif (I('get.startDate')) {
                $where['createDate'] = array('gt', I('get.startDate') . $this->startTime);
                $params['startDate'] = I('get.startDate');
            } elseif (I('get.endDate')) {
                $where['createDate'] = array('lt', I('get.endDate') . $this->endTime);
                $params['endDate'] = I('get.endDate');
            }
            if (I('get.CD')) {
                $cdCode = $where['CD'] = I('get.CD');
            }
        }

        $count = $userViewProductModel->where($where)->count();
        $page = new Page($count, $page_num);
        $page->page_num = $page_num;
        $page->parameter = http_build_query($params);
        $show = $page->show();
        $result = $userViewProductModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('createDate desc')->select();
        $this->assign('cdList', json_encode(array_values($cdList)));
        $this->assign('result', json_encode($result));
        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('cdCode', $cdCode);
        $this->assign('all_channel', json_encode($cdList));
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 用户求购记录
     */
    public function userRequestProductList()
    {
        $userRequestProductModel = D('UserRequestProduct');
        $cdModel = D('ZZmscmncd');#渠道model
        $cdList = $cdModel->getCdValues('销售渠道');
        import('ORG.Util.Page');// 导入分页类
        $params = [];
        $where['CD'] = $cdCode = 'N000831400';#暂时只有kr
        $page_num = empty(I('get.page_num')) ? 15 : I('get.page_num');
        if ($this->isPost()) {
            if (I('post.userId')) {
                $params['userId'] = $where['userId'] = I('post.userId');
            }
            if (I('post.productId')) {
                $params['productId'] = $where['productId'] = I('post.productId');
            }
            if (I('post.startDate') && I('post.endDate')) {
                $where['createDate'] = array(array('gt', I('post.startDate') . $this->startTime), array('lt', I('post.endDate') . $this->endTime));
                $params['startDate'] = I('post.startDate');
                $params['endDate'] = I('post.endDate');

            } elseif (I('post.startDate')) {
                $where['createDate'] = array('gt', I('post.startDate') . $this->startTime);
                $params['startDate'] = I('post.startDate');
            } elseif (I('post.endDate')) {
                $where['createDate'] = array('lt', I('post.endDate') . $this->endTime);
                $params['endDate'] = I('post.endDate');
            }
            if (I('post.CD')) {
                $cdCode = $where['CD'] = I('post.CD');
            }
        }
        if ($this->isGet()) {
            if (I('get.userId')) {
                $params['userId'] = $where['userId'] = I('get.userId');
            }
            if (I('get.productId')) {
                $params['productId'] = $where['productId'] = I('get.productId');
            }
            if (I('get.startDate') && I('get.endDate')) {
                $where['createDate'] = array(array('gt', I('get.startDate') . $this->startTime), array('lt', I('get.endDate') . $this->endTime));
                $params['startDate'] = I('get.startDate');
                $params['endDate'] = I('get.endDate');
            } elseif (I('get.startDate')) {
                $where['createDate'] = array('gt', I('get.startDate') . $this->startTime);
                $params['startDate'] = I('get.startDate');
            } elseif (I('get.endDate')) {
                $where['createDate'] = array('lt', I('get.endDate') . $this->endTime);
                $params['endDate'] = I('get.endDate');
            }
            if (I('get.CD')) {
                $cdCode = $where['CD'] = I('get.CD');
            }
        }

        $count = $userRequestProductModel->where($where)->count();
        $page = new Page($count, $page_num);
        $page->page_num = $page_num;
        $page->parameter = http_build_query($params);
        $show = $page->show();
        $result = $userRequestProductModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('createDate desc')->select();
        $this->assign('cdList', json_encode(array_values($cdList)));
        $this->assign('result', json_encode($result));
        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('cdCode', $cdCode);
        $this->assign('all_channel', json_encode($cdList));
        $this->assign('params', $params);
        $this->display();
    }

    /**
     * 获取热搜词列表
     */
    public function hotSearchKeyList()
    {
        $hotSearchKeyModel = D('HotSearchKey');
        $cdModel = D('ZZmscmncd');#渠道model
        $cdList = $cdModel->getCdValues('销售渠道');
        import('ORG.Util.Page');// 导入分页类
        $params = [];
        $sortTypes = [['key' => 'createDate', 'val' => L('创建时间')],
            ['key' => 'sortVal', 'val' => L('排序字段')]];
        $sortDescs = [['key' => 'asc', 'val' => L('升序')],
            ['key' => 'desc', 'val' => L('降序')]];
        $params['CD'] = $where['CD'] = $cdCode = 'N000831400';#暂时只有kr
        $sortType = 'sortVal';
        $sortDesc = 'asc';
        $page_num = empty(I('get.page_num')) ? 15 : I('get.page_num');
        if ($this->isPost()) {
            if (I('post.keyword')) {
                $params['keyword'] = $where['keyword'] = I('post.keyword');
            }
            if (I('post.startDate') && I('post.endDate')) {
                $where['createDate'] = array(array('gt', I('post.startDate') . $this->startTime), array('lt', I('post.endDate') . $this->endTime));
                $params['startDate'] = I('post.startDate');
                $params['endDate'] = I('post.endDate');

            } elseif (I('post.startDate')) {
                $where['createDate'] = array('gt', I('post.startDate') . $this->startTime);
                $params['startDate'] = I('post.startDate');
            } elseif (I('post.endDate')) {
                $where['createDate'] = array('lt', I('post.endDate') . $this->endTime);
                $params['endDate'] = I('post.endDate');
            }
            if (I('post.CD')) {
                $cdCode = $where['CD'] = I('post.CD');
            }
            if (I('post.sortType')) {
                $sortType = I('post.sortType');
            }
            if (I('post.sortDesc')) {
                $sortDesc = I('post.sortDesc');
            }

        }
        if ($this->isGet()) {
            if (I('get.keyword')) {
                $params['keyword'] = $where['keyword'] = I('get.keyword');
            }
            if (I('get.startDate') && I('get.endDate')) {
                $where['createDate'] = array(array('gt', I('get.startDate') . $this->startTime), array('lt', I('get.endDate') . $this->endTime));
                $params['startDate'] = I('get.startDate');
                $params['endDate'] = I('get.endDate');
            } elseif (I('get.startDate')) {
                $where['createDate'] = array('gt', I('get.startDate') . $this->startTime);
                $params['startDate'] = I('get.startDate');
            } elseif (I('get.endDate')) {
                $where['createDate'] = array('lt', I('get.endDate') . $this->endTime);
                $params['endDate'] = I('get.endDate');
            }
            if (I('get.CD')) {
                $cdCode = $where['CD'] = I('get.CD');
            }
            if (I('get.sortType')) {
                $sortType = I('get.sortType');
            }
            if (I('get.sortDesc')) {
                $sortDesc = I('get.sortDesc');
            }
        }
        $params['sortType'] = $sortType;
        $params['sortDesc'] = $sortDesc;
        $order = [$sortType => $sortDesc,'recommendKeywordId'=>'desc'];
        $count = $this->getHotKeywordCount($where);
        $page = new Page($count, $page_num);
        $page->page_num = $page_num;
        $page->parameter = http_build_query($params);
        $show = $page->show();
        $params['p'] = empty(I('get.p')) ? 1 : I('get.p');
        $params['page_num'] = $page_num;
        $result = $hotSearchKeyModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order($order)->select();
        $result = empty($result) ? [] : $result;
        $this->assign('cdList', json_encode(array_values($cdList)));
        $this->assign('result', json_encode($result));
        $this->assign('pages', $show);
        $this->assign('total', $count);
        $this->assign('cdCode', $cdCode);
        $this->assign('all_channel', json_encode($cdList));
        $this->assign('params', $params);
        $this->assign('sortTypes', json_encode($sortTypes));
        $this->assign('sortDescs', json_encode($sortDescs));
        $this->display();
    }

    /**
     * 添加和修改热搜词
     */
    public function setHotKeyword()
    {
        $recommendKeywordId = 0;
        $hotSearchKeyModel = D('HotSearchKey');
        if ($this->isPost()) {
            if (empty(I('post.cd'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            if (empty(I('post.keyword'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            $data['CD'] = I('post.cd');
            $data['keyword'] = trim(I('post.keyword'));
            $result = $hotSearchKeyModel->where($data)->select();
            if (!empty($result)) {
                echo json_encode(array('info' => L('热词已经存在'), "status" => "n", 'data' => null));
                die;
            }
            $data['sortVal'] = (int)I('post.sortVal');
            $recommendKeywordId = I('post.id');
        }
        $data['createDate'] = date('Y-m-d H:i:s');
        if (!empty($recommendKeywordId)) {
            $row = $hotSearchKeyModel->where('recommendKeywordId=' . $recommendKeywordId)->save($data);
            if ($row) {
                $return_arr = array('info' => L('更新成功'), "status" => "y", 'id' => $recommendKeywordId, 'data' => $data);
            } else {
                $return_arr = array('info' => L('更新失败'), "status" => "n", 'data' => $data);
            }
            echo json_encode($return_arr);
            die;
        }
        $id = $hotSearchKeyModel->add($data);
        if ($id) {
            $where['CD'] = $data['CD'];
            $count = $this->getHotKeywordCount($where);
            $total = $count == 0 ? 1 :  $count + 1;
            $return_arr = array('info' => L('创建成功'), "status" => "y", 'id' => $id, 'total'=>$total,'data' => $data);
        } else {
            $return_arr = array('info' => L('创建失败'), "status" => "n", 'data' => $data);
        }
        echo json_encode($return_arr);
        die;
    }

    /**
     *
     */
    public function delHotKeyword()
    {
        $recommendKeywordId = 0;
        $hotSearchKeyModel = D('HotSearchKey');
        if ($this->isPost()) {
            $recommendKeywordId = I('post.id');
            if (empty($recommendKeywordId)) {
                echo json_encode(['info' => L('参数不正确'), "status" => "n", 'data' => null]);
                die;
            }
        }

        $row = $hotSearchKeyModel->where('recommendKeywordId=' . $recommendKeywordId)->delete();
        if ($row) {
            $return_arr = ['info' => L('删除成功'), "status" => "y", 'id' => $recommendKeywordId, 'data' => null];
        } else {
            $return_arr = ['info' => L('删除失败'), "status" => "n", 'data' => null];
        }
        echo json_encode($return_arr);
        die;

    }

    /**
     * 推送热词给from b5c to gshopperApp
     */
    public function pushHotKeyword()
    {
        $data = file_get_contents("php://input");
        $arr = [];
        $postData['processCode'] =  'TB_GS_RECOMMENT_KEYWORDS';#此处会因后续业务发展修改
        $platCode = 'N000831400';
        if(empty($data))
        {
            echo   json_encode(['info' => L('参数不正确'), "status" => "n", 'data' => null]);die;
        }
        $hotSearchKeyModel = D('HotSearchKey');
        $ids = json_decode($data,true);
        $map['recommendKeywordId'] = ['in',$ids];
        $result = $hotSearchKeyModel->where($map)->order(['sortVal'=>'asc'])->select();
        foreach ((array)$result as $value)
        {
            $arr[] = [
                "recommentKeywordId" => $value['recommendKeywordId'],
                "keyword" => $value['keyword']
            ];
            $platCode = $value['CD'];
        }
        if(!empty($arr))
        {
            $postData['platCode'] = $platCode;
            $postData['processId'] = md5(uniqid('keyword_',true));
            $postData['data']['tbGsRecommentKeywords'] = $arr;
            $responseData = curl_get_json(RECOMMENDKEYWORD_URL,json_encode($postData));
            echo $responseData;die;
        }
    }

    /**
     * 修改热词的排序功能
     * add  +1 decr -1
     */
    public function changeSortVal()
    {
        $recommendKeywordId = 0;
        $hotSearchKeyModel = D('HotSearchKey');
        if ($this->isPost()) {
            if (empty(I('post.id'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            if (empty(I('post.oid'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            if (empty(I('post.sortval'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            if (empty(I('post.osortval'))) {
                echo json_encode(array('info' => L('参数不正确'), "status" => "n", 'data' => null));
                die;
            }
            $recommendKeywordId  = I('post.id');
            $orecommendKeywordId = I('post.oid');
            $sortval  = I('post.sortval');
            $osortval = I('post.osortval');
        }
        $row = $hotSearchKeyModel->where('recommendKeywordId=' . $recommendKeywordId)->save(['sortVal' => $osortval]);
        $nrow = $hotSearchKeyModel->where('recommendKeywordId=' . $orecommendKeywordId)->save(['sortVal' => $sortval]);
        if ($row + $nrow) {
            //$data = $this->returnAjaxData($_GET);
            $return_arr = array('info' => L('更新成功'), "status" => "y", 'id' => $recommendKeywordId, 'data' => null);
        } else {
            $return_arr = array('info' => L('更新失败'), "status" => "n", 'data' => null);
        }
        echo json_encode($return_arr);
        die;

    }

    /**
     * 获取热搜词总数
     * @param array $where
     * @return mixed
     */
    public function getHotKeywordCount($where = [])
    {
        $hotSearchKeyModel = D('HotSearchKey');
        return  $hotSearchKeyModel->where($where)->count();
    }

    /**
     * ajax请求获取数据列表
     * @param $data
     * @return array
     */
    public function  returnAjaxData($data)
    {
        $hotSearchKeyModel = D('HotSearchKey');
        import('ORG.Util.Page');// 导入分页类
        $params = [];
        $sortTypes = [['key' => 'createDate', 'val' => L('创建时间')],
            ['key' => 'sortVal', 'val' => L('排序字段')]];
        $sortDescs = [['key' => 'asc', 'val' => L('升序')],
            ['key' => 'desc', 'val' => L('降序')]];
        $where['CD'] = $cdCode = 'N000831400';#暂时只有kr
        $sortType = 'sortVal';
        $sortDesc = 'asc';
        $page_num = empty($data['page_num']) ? 15 : $data['page_num'];
        $p = $data['p'];
        if ($data['keyword']) {
           $where['keyword'] = I('post.keyword');
        }
        if ($data['startDate'] && $data['endDate']) {
            $where['createDate'] = array(array('gt', $data['startDate'] . $this->startTime), array('lt', $data['endDate'] . $this->endTime));

        } elseif ($data['startDate']) {
            $where['createDate'] = array('gt', $data['startDate'] . $this->startTime);
           $data['startDate'];
        } elseif ($data['endDate']) {
            $where['createDate'] = array('lt',$data['endDate'] . $this->endTime);
            $data['endDate'];
        }
        if ($data['CD']) {
            $cdCode = $where['CD'] = $data['CD'];
        }
        if ($data['sortType']) {
            $sortType = $data['sortType'];
        }
        if ($data['sortDesc']) {
            $sortDesc = $data['sortDesc'];
        }
        $order = [$sortType => $sortDesc,'recommendKeywordId'=>'desc'];
        $result = $hotSearchKeyModel->where($where)->limit(($p - 1) * $page_num  . ',' . $page_num)->order($order)->select();
        $result = empty($result) ? [] : $result;
        return $result;
    }


}