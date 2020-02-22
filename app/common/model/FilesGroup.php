<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 16:28:13
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\model;
use SoftDelete;
/**
 * 文件组模型
 * Class Menu
 * @package app\common\model
 */
class FilesGroup extends Base
{
    protected $name = 'files_group';

    protected $pk = 'group_id';

    protected $pkVal = [
		'name',
		'type'
	];

	protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    protected $deleteTime = 'delete_at';
    protected $defaultSoftDelete = NULL;

    /**
     * 所有
     * @return mixed
     */
    public static function getALL($where=[])
    {
        $model = new static;
        return $model->where($where)->order(['create_at' => 'asc'])->select()->ToArray();
    }

    
    /**
     * 添加
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->allowField($this->pkVal)->save($data);
    }


    /**
     * 修改
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
		return $this->allowField($this->pkVal)->update($data);
    }


    public static function detail($group_id){
    	return static::find($group_id);
    }

    /**
     * 删除记录
     * @return int
     */
    public function remove()
    {
        // 更新该分组下的所有文件
        (new Files)->where('group_id', '=', $this['group_id'])
            ->update(['group_id' => 0]);
        // 删除分组
        return $this->delete();
    }
}