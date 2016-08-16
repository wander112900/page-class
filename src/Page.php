<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: huguijian <292438151.qq.com> 
// +----------------------------------------------------------------------
namespace phpclass\page;

class Page
{
    public $firstRow; // 起始行数
    public $listRows; // 列表每页显示行数
    public $parameter; // 分页跳转时要带的参数
    public $totalRows; // 总行数
    public $totalPages; // 分页总页面数
    public $rollPage = 11; // 分页栏每页显示的页数

    private $p       = 'p'; //分页参数名
    private $url_model = 0;
    private $url     = ''; //当前链接URL
    private $nowPage = 1;
    private static $var_page = 'p';

    // 分页显示定制
    private $config = array(
        'page_url' => '',
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<<',
        'next'   => '>>',
        'first'  => '1...',
        'last'   => '...%TOTAL_PAGE%',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%',
        'full_tag_open' => '',
        'full_tag_close' => '',
        'num_link_open' =>'',
        'num_link_close' => '',
        'current_tag_open'  => '',
        'current_tag_close' => '',
        'prev_tag_open' => '',
        'prev_tag_close' => '',
        'next_tag_open' => '',
        'next_tag_close' =>'',
        'first_tag_open' => '',
        'first_tag_close' => '',
        'end_tag_open' => '',
        'end_tag_close' => ''
    );

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows, $listRows = 20, $parameter = array(),$url_model=0)
    {
        self::$var_page && $this->p = self::$var_page; //设置分页参数名称
        /* 基础设置 */
        $this->totalRows = $totalRows; //设置总记录数
        $this->listRows  = $listRows; //设置每页显示行数
        $this->parameter = empty($parameter) ? $_GET : $parameter;
        $this->nowPage   = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);
        $this->nowPage   = $this->nowPage > 0 ? $this->nowPage : 1;
        $this->firstRow  = $this->listRows * ($this->nowPage - 1);
        $this->url_model = $url_model;

    }

    /**
     * 定制分页链接设置
     * @param string $name  设置名称
     * @param string $value 设置值
     */
    public function setConfig($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     * @param  integer $page 页码
     * @return string
     */
    private function url($page)
    {
        return str_replace('[PAGE]', $page, $this->url);
    }

    /**
     * 组装分页链接
     * @return string
     */
    public function show()
    {
        if (0 == $this->totalRows) {
            return '';
        }

        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $parameters = "";
        if($this->url_model==1) {
            foreach($this->parameter as $s=>$url_p) {
                $parameters .= "/".$s."/".$url_p;
            }
        }else{
            foreach($this->parameter as $s=>$url_p) {
                $parameters .= "&".$s."=".$url_p;
            }
            $parameters = preg_replace("/^&/","?",$parameters);
        }
        $this->url                 = $this->config['page_url'].$parameters;
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); //总页数
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }

        /* 计算分页临时变量 */
        $now_cool_page      = $this->rollPage / 2;
        $now_cool_page_ceil = ceil($now_cool_page);

        //上一页
        $up_row  = $this->nowPage - 1;
        $up_page = $up_row > 0 ? str_replace("%URL%",$this->url($up_row),$this->config['prev_tag_open']) .$this->config['prev']. $this->config['prev_tag_close'] : '';

        //下一页
        $down_row  = $this->nowPage + 1;
        $down_page = ($down_row <= $this->totalPages) ? str_replace("%URL%",$this->url($down_row),$this->config['next_tag_open']).$this->config['next'].$this->config['next_tag_close']: '';
        //第一页
        $the_first = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1) {
            $the_first = str_replace("%URL%",$this->url(1),$this->config['first_tag_open']) . $this->config['first'] . $this->config['first_tag_close'];
        }

        //最后一页
        $the_end = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages) {
            $the_end = str_replace("%URL%",$this->url($this->totalPages),$this->config['end_tag_open']) . $this->config['last'] . $this->config['end_tag_close'];
        }

        //数字连接
        $link_page = "";
        for ($i = 1; $i <= $this->rollPage; $i++) {
            if (($this->nowPage - $now_cool_page) <= 0) {
                $page = $i;
            } elseif (($this->nowPage + $now_cool_page - 1) >= $this->totalPages) {
                $page = $this->totalPages - $this->rollPage + $i;
            } else {
                $page = $this->nowPage - $now_cool_page_ceil + $i;
            }
            if ($page > 0 && $page != $this->nowPage) {

                if ($page <= $this->totalPages) {
                    $link_page .= str_replace("%URL%",$this->url($page),$this->config['num_link_open']).$page.$this->config['num_link_close'];
                } else {
                    break;
                }
            } else {
                if ($page > 0 && 1 != $this->totalPages) {
                    $link_page .= $this->config['current_tag_open'].$page.$this->config['current_tag_close'];
                }
            }
        }

        //替换分页内容
        $page_str = str_replace(
            array('%HEADER%', '%NOW_PAGE%', '%UP_PAGE%', '%DOWN_PAGE%', '%FIRST%', '%LINK_PAGE%', '%END%', '%TOTAL_ROW%', '%TOTAL_PAGE%'),
            array($this->config['header'], $this->nowPage, $up_page, $down_page, $the_first, $link_page, $the_end, $this->totalRows, $this->totalPages),
            $this->config['theme']);
        return $this->config['full_tag_open']."{$page_str}".$this->config['full_tag_close'];
    }
}
