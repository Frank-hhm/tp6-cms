<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 16:33:06
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */
namespace app\controller\api;
use app\BaseController;
use app\common\model\FilesGroup as FilesGroupModel;
use app\common\model\Files as FilesModel;

class Files extends BaseController
{
    /**
     * 文件库列表
     * @param string $type
     * @param int $group_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list($group_id = -1)
    {
        // 分组列表
        $group_list = (new FilesGroupModel)->getALL();

        // 文件列表
        $file_list = (new FilesModel)->getList(intval($group_id),0);
        return $this->success('success', '', compact('group_list', 'file_list'));
    }

    /**
     * 新增分组
     * @param $group_name
     * @param string $group_type
     * @return array
     */
    public function addGroup($name)
    {
        $model = new FilesGroupModel;
        if ($model->add(compact('name'))) {
            $group_id = $model->group_id;
            return $this->success('添加成功', '', compact('group_id', 'name'));
        }
        return $this->error($model->getError() ?: '添加失败');
    }

    /**
     * 编辑分组
     * @param $group_id
     * @param $group_name
     * @return array
     * @throws \think\exception\DbException
     */
    public function editGroup($group_id, $name)
    {
        $model = new FilesGroupModel;
        if ($model->edit(compact('group_id', 'name') )) {
            return $this->success('修改成功');
        }
        return $this->error($model->getError() ?: '修改失败');
    }

    /**
     * 删除分组
     * @param $group_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function deleteGroup($group_id)
    {
        $model =FilesGroupModel::detail($group_id);
        if ($model->remove()) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError() ?: '删除失败');
    }

    /**
     * 批量删除文件
     * @param $fileIds
     * @return array
     */
    public function deleteFiles()
    {
        $model = new FilesModel;
        if ($model->destroy($this->request->post('file_id'))) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError() ?: '删除失败');
    }

    /**
     * 批量移动文件分组
     * @param $group_id
     * @param $fileIds
     * @return array
     */
    public function moveFiles()
    {
        $model = new FilesModel;
        if ($model->moveGroup($this->request->post('group_id'),$this->request->post('file_id')) !== false) {
            return $this->success('移动成功');
        }
        return $this->error($model->getError() ?: '移动失败');
    }
}