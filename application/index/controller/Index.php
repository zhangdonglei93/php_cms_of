<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\News as newsMode;
use app\index\model\Product as productMode;
use app\index\model\City as cityModel;
class Index extends Base
{
    public function _initialize(){
        parent::_initialize();
    }

    public function index() {
        //新闻推荐
    	$newsData = newsMode::getIndexRecom();
    	$pMolde   = new productMode();
        //推荐
        $prData    = $pMolde->getProductRecom();
        $prKeys    = array_keys($prData);
        //精选 的逻辑是在 推荐的上海城市在细分
        $pcData    = $pMolde->getProductChoice();
        $pcKeys    = array_keys($pcData);
        // dd($pData);
    	$this->setPageInfo(
    			$this->conf['title']['v'],
	    		$this->conf['seo_title']['v'],
	    		$this->conf['seo_des']['v']
    		);
    	$this->assign('newsData',$newsData);
        $this->assign('prKeys',$prKeys);
        $this->assign('prData',$prData);
        $this->assign('pcKeys',$pcKeys);
        $this->assign('pcData',$pcData);
    	return $this->fetch();
    }

    public function city($city) {
        $cityMode = new cityModel;
        $pData    = $cityMode->getCityProductByUrltitle($city);
        if (empty($pData)) return $this->notFound();
        $cData    = $cityMode->getCityInfoByUrltitle($city);
        $this->setPageInfo(
                $cData['name'],
                $cData['seo_title'],
                $cData['seo_des']
            );
        $this->assign('pData',$pData);
        $this->assign('cityId',$cityMode->getParentIsCity($cData['id']));
        return $this->fetch();
    }

    public function product($product) {
        $productMode = new productMode;
        $pData = $productMode->getProductInfo($product);
        $this->setPageInfo(
                $pData['name'],
                $pData['seo_title'],
                $pData['seo_des']
            );
        $this->assign('pData',$pData);
        $this->assign('indexProduct','indexProduct');
        return $this->fetch();
    }
    public function aboutus() {
        $this->setPageInfo(
                '关于我们',
                $this->conf['seo_title']['v'],
                $this->conf['seo_des']['v']
            );
        return $this->fetch();
    }
    public function contactus() {
        $this->setPageInfo(
                '联系我们',
                $this->conf['seo_title']['v'],
                $this->conf['seo_des']['v']
            );
        return $this->fetch();
    }

    public function ajaxGetRegin() {
        $cityMode = new cityModel;
        $parent_id = input('get.parent_id');
        $data  = $cityMode->getRegionByParentId($parent_id);
        foreach ($data as $key => $value) {
            $data[$key]['of_url'] = of_url($value->urlSimplify->url_title,'tag');
        }
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    public function ajaxGetBs() {
        $cityMode = new cityModel;
        $parentIds = input('get.parentIds');
        $data = $cityMode->getBsByParentIds($parentIds);
        foreach ($data as $key => $value) {
            $data[$key]['of_url'] = of_url($value->urlSimplify->url_title,'tag');
        }
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    public function news() {
        $newsMode = new newsMode();
        $pMolde   = new productMode();
        $newsList  = $newsMode->getNewsList();
        $pData    = $pMolde->getProductList();
        $this->setPageInfo(
                '行业资讯',
                $this->conf['seo_title']['v'],
                $this->conf['seo_des']['v']
            );
        $this->assign('newsList',$newsList);
        $this->assign('pData',$pData);
        return $this->fetch();
    }

    public function news_detail($url_title) {
        $newsMode = new newsMode();
        $pMolde   = new productMode();
        $cityData = $newsMode->getCityInfo($url_title);
        $prev     = $newsMode->getPrev($cityData);
        $next     = $newsMode->getNext($cityData);
        $pData    = $pMolde->getProductList();
        $this->setPageInfo(
                $cityData['title'],
                $cityData['seo_title'],
                $cityData['seo_des']
            );
        $this->assign('prev',$prev);
        $this->assign('next',$next);
        $this->assign('pData',$pData);
        $this->assign('cityData',$cityData);
        return $this->fetch();
    }

    public function sendMail() {
        $rule = [
            'tel'       => 'require|length:4,25',
            'name'      => 'require|max:255',
            'other'     => 'max:500', 
            'city'      => 'max:255'
        ];
        $msg = [
            'tel.require'   => '电话号码不能为空',
            'tel.length'    => '电话号码不符合长度范围4~25',
            'name.require'  => '名称不能为空',
            'name.max'      => '姓名过长',
            'other.max'     => '其他输入过长',
            'city'          => '城市输入过长',
        ];
        if ($this->checkToken(input('post.csrf_token')) === false) {
            $this->error('请勿重复提交');
        }
        $validate = new \think\Validate($rule,$msg);
        $result   = $validate->check(input('post.'),$rule,$msg);
        if (!$result) {
            $this->error($validate->getError());
        } else {
            $to      = $this->conf['email_to']['v'];
            $title   = '网站用户提交';
            $content = "<h4>姓名：".input('post.name')."</h4><h4>电话：".input('post.tel')."</h4><h4>城市：".input('post.city')."</h4><h4>其他：".input('post.other')."</h4>";
            sendMail($to, $title, $content);
            $this->success('提交成功！');
        }
    }

}