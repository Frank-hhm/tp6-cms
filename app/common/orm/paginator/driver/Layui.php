<?php

/**
 * @Author: 小明
 * @Date:   2020-02-22 02:06:28
 * @Email:  Frank_hhm@163.com
 * @Last Modified by:   根子科技
 */

namespace app\common\orm\paginator\driver;
use think\Paginator;
use think\facade\Request;
class Layui extends Paginator
{
    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = "上一页")
    {
        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->url(
            $this->currentPage() - 1
        );
        return $this->getPageLinkWrapper($url, $this->currentPage() - 1,$text);
    }
    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '下一页')
    {
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }
        $url = $this->url($this->currentPage() + 1);
        return $this->getPageLinkWrapper($url,$this->currentPage() + 1,$text);
    }
    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        if ($this->simple)
            return '';
        $block = [
            'first' => null,
            'slider' => null,
            'last' => null
        ];
        $side = 3;
        $window = $side * 2;
        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last'] = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last'] = $this->getUrlRange($this->lastPage - ($window + 2), $this->lastPage);
        } else {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last'] = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }
        $html = '';
        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }
        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }
        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }
        return $html;
    }
    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
    	$limit = intval(Request::get('limit', cookie('page-limit')));
    	cookie('page-limit', $limit = $limit >= 10 ? $limit : 10);
    	// if ($this->listRows > 0) $limit = $this->listRows;
    	$query = Request::get();
    	$rows = [];
        $ahref = 'data-open';
    	foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160, 170, 180, 190, 200] as $num) {
            list($query['limit'], $query['page'], $selected) = [$num, '1', $limit === $num ? 'selected' : ''];
            if(!empty($query['pageType']) && $query['pageType'] =='iframe'){
                $url = Request::baseUrl() . '?' . urldecode(http_build_query($query));
                $ahref = 'href';
            }else{
                $url = url('@'. app('http')->getName()). '#' . Request::baseUrl() . '?' . urldecode(http_build_query($query));
            }
            array_push($rows, "<option data-num='{$num}' value='{$url}' {$selected}>{$num}</option>");
        }

        $select = "<select onchange='location.href=this.options[this.selectedIndex].value' data-auto-none>" . join('', $rows) . "</select>";
        // $html = "<div class='pagination-container nowrap'><span>共 {$this->total()} 条记录，每页显示 {$select} 条，共 {$this->lastPage()} 页当前显示第 {$this->currentPage()} 页。</span></div>";
        // return sprintf(preg_replace('|href="(.*?)"|', 'data-open="$1" onclick="return false" href="$1"', $html));die;
        return sprintf(
    		preg_replace('|href="(.*?)"|',$ahref.'="$1" onclick="return false" '. $ahref.'="$1"',"<div class='pagination-container nowrap'><span>共 {$this->total()} 条记录，每页显示 {$select} 条，共 {$this->lastPage()} 页当前显示第 {$this->currentPage()} 页。</span><div class='layui-box layui-laypage layui-laypage-default'>%s %s %s</div></div>"),
            $this->getPreviousButton(),
            $this->getLinks(),
            $this->getNextButton()
        );
    }
    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page,$text='')
    {
        $ahref = 'data-open';
    	$query = Request::get();
        if(!empty($query['pageType']) && $query['pageType'] =='iframe'){
            $url = Request::baseUrl() . '?' . urldecode(http_build_query($query));
            $ahref = 'href';
        }else{
            $url = url('@'. app('http')->getName()). '#' . Request::baseUrl() . '?' . urldecode(http_build_query($query));
        }
        $url = replace_var(add_querystring_var($url,'page',$page),'page',$page);
        if ($text == '上一页')
            return '<a '.$ahref.'="' . $url . '" class="layui-laypage-prev">上一页</a>';
        else if ($text == '下一页')
            return '<a '.$ahref.'="' . $url . '" class="layui-laypage-next">下一页</a>';
        else
            return '<a '.$ahref.'="' . $url . '">' . $page . '</a>';
    }
    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a class="layui-laypage-prev layui-disabled" >' . $text . '</a>';
    }
    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<span class="layui-laypage-curr"><em class="layui-laypage-em" style="background-color:#007bff;color:#fff;"></em><em style="color:#fff;">' . $text . '</em></span>';
    }
    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }
    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';
        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }
        return $html;
    }
    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int $page
     * @return string
     */
    protected function getPageLinkWrapper($url, $page,$text='')
    {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }
        return $this->getAvailablePageWrapper($url, $page,$text);
    }
}