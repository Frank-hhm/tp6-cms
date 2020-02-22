<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 16:28:43
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\common\model;
use think\facade\Request;
use SoftDelete;
/**
 * 文件模型
 * Class Files
 * @package app\common\model
 */
class Files extends Base
{



    protected $name = 'files';

    protected $pk = 'file_id';

    protected $pkVal = [
		'storage',
		'file_domain',
		'file_name',
		'file_path',
		'file_url',
		'file_size',
		'file_type',
		'extension',
		'delete_at'
	];

	protected $autoWriteTimestamp = true;
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    protected $deleteTime = 'delete_at';
    protected $defaultSoftDelete = NULL;


    /**
     * 关联分组
     * @return \think\model\relation\HasOne
     */
    public function filesGroup()
    {
        return $this->belongsTo('FilesGroup', 'group_id');
    }



    /**
     * 获取器：改变url
     * @param $value
     * @param $data
     * @return array
     */
    public function getFileUrlAttr($value,$data)
    {
        return $data['file_domain'].$value;
    }

    
    /**
     * 获取列表记录
     * @param int $groupId 分组id
     * @param string $type 文件类型
     * @param bool|int $isRecycle
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($groupId = -1)
    {
        // 文件分组
        $where=[];
        $groupId != -1 && $where['group_id']=(int)$groupId;
        // 查询列表数据
        return $this->with('files_group')->where($where)
            ->order('file_id desc')
            ->paginate(12, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 文件详情
     * @param $file_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($file_id)
    {
        return self::get($file_id);
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
     * 批量移动文件分组
     * @param $group_id
     * @param $fileIds
     * @return $this
     */
    public function moveGroup($group_id, $fileIds)
    {
        return $this->where('file_id', 'in', $fileIds)->update(compact('group_id'));
    }

}