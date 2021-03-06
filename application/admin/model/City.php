<?php
namespace app\admin\model;
use think\Model;

class City extends Model
{
	protected $updateTime = false;
	protected $autoWriteTimestamp = true;
	
	public function getTree($order,$where='')
	{
		$data = $this->where($where)->order($order)->select();
		return $this->_reSort($data);
	}
	
	public function _reSort($data, $parent_id=0, $level=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$v['level'] = $level;
				$ret[] = $v;
				$this->_reSort($data, $v['id'], $level+1, FALSE);
			}
		}
		return $ret;
	}

	public function getChildren($id)
	{
		$data = $this->select();
		return $this->_children($data, $id);
	}
	
	private function _children($data, $parent_id=0, $isClear=TRUE)
	{
		static $ret = array();
		if($isClear)
			$ret = array();
		foreach ($data as $k => $v)
		{
			if($v['parent_id'] == $parent_id)
			{
				$ret[] = $v['id'];
				$this->_children($data, $v['id'], FALSE);
			}
		}
		return $ret;
	}

	public function urlSimplify()
    {
        return $this->hasOne('urlSimplify','other_id','id')->where('table_name','City');
    }

    public function getCityAll()
    {
    	return $this->where(['parent_id'=>['eq',0]])->order('order_key asc')->select();
    }

    public function getRegionByParentId($parent_id)
    {
    	return $this->where(['parent_id'=>['eq',$parent_id]])->order('order_key asc')->select();
    }

    public function getBsByParentId($parent_id)
    {
    	return $this->where(['parent_id'=>['eq',$parent_id]])->order('order_key asc')->select();
    }

}