<?php
namespace app\modules\backstage\common;

class CBillCommon{
    public function getPageInfo($page = 0,$pagesize = 500){
        $offset = $page*$pagesize;
        $limit = $pagesize;
        return ['limit'=>$limit,'offset'=>$offset];
    }
}