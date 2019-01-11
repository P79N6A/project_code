<?php
/**
 * @author: shwdai@gmail.com
 */
class Pager{

	public $rowCount = 0;
	public $pageNo = 1;
	public $pageSize = 20;
	public $pageCount = 0;
	public $offset = 0;
	public $pageString = 'page';

	private $script = null;
	private $valueArray = array();

	public function __construct($count=0, $size=20, $string='page')
	{
		$this->defaultQuery();
		$this->pageString = $string;
		$this->pageSize = abs($size);
		$this->rowCount = abs($count);

		$this->pageCount = ceil($this->rowCount/$this->pageSize);
		$this->pageCount = ($this->pageCount<=0)?1:$this->pageCount;
		$this->pageNo = abs(intval(@$_GET[$this->pageString]));
		$this->pageNo = $this->pageNo==0 ? 1 : $this->pageNo;
		$this->pageNo = $this->pageNo>$this->pageCount 
			? $this->pageCount : $this->pageNo;
		$this->offset = ( $this->pageNo - 1 ) * $this->pageSize;
	}

	private function genURL( $param, $value ){
		$valueArray = $this->valueArray;
		$valueArray[$param] = $value;
		return $this->script . '?' . http_build_query($valueArray);
	}

	private function defaultQuery()
	{
		($script_uri = @$_SERVER['SCRIPT_URI']) || ($script_uri = @$_SERVER['REQUEST_URI']);
		$q_pos = strpos($script_uri,'?');
		if ( $q_pos > 0 )
		{
			$qstring = substr($script_uri, $q_pos+1);
			parse_str($qstring, $valueArray);
			$script = substr($script_uri,0,$q_pos);
		}
		else
		{
			$script = $script_uri;
			$valueArray = array();
		}
		$this->valueArray = empty($valueArray) ? array() : $valueArray;
		$this->script = $script;
	}

	public function paginate($switch=1){
		$from = $this->pageSize*($this->pageNo-1)+1;
		$from = ($from>$this->rowCount) ? $this->rowCount : $from;
		$to = $this->pageNo * $this->pageSize;
		$to = ($to>$this->rowCount) ? $this->rowCount : $to;
		$size = $this->pageSize;
		$no = $this->pageNo;
		$max = $this->pageCount;
		$total = $this->rowCount;

		return array(
			'offset' => $this->offset,
			'from' => $from,
			'to' => $to,
			'size' => $size,
			'no' => $no,
			'max' => $max,
			'total' => $total,
		);
	}

	public function GenWap() {
		$r = $this->paginate();
		$pagestring= '<p align="right">';
		if( $this->pageNo > 1 ){
			$pageString.= '4 <a href="' . $this->genURL($this->pageString, $this->pageNo-1) . '" accesskey="4">上页</a>';
		}
		if( $this->pageNo >1 && $this->pageNo < $this->pageCount ){
			$pageString.= '｜';
		}
		if( $this->pageNo < $this->pageCount ) {
			$pageString.= '<a href="' .$this->genURL($this->pageString, $this->pageNo+1) . '" accesskey="6">下页</a> 6';
		}
		$pageString.= '</p>';
		return $pageString;
	}
	public function GenWap2() {
		$r = $this->paginate();
		$pagestring= '<li>';
		if( $this->pageNo > 1 ){
			$pageString.= '4 <a href="' . $this->genURL($this->pageString, $this->pageNo-1) . '" accesskey="4">上页</a>';
		}
		if( $this->pageNo >1 && $this->pageNo < $this->pageCount ){
			$pageString.= '｜';
		}
		if( $this->pageNo < $this->pageCount ) {
			$pageString.= '<a href="' .$this->genURL($this->pageString, $this->pageNo+1) . '" accesskey="6">下页</a>6 ';
		}
		$pageString.= '</li>';
		return $pageString;
	}

	public function GenBasic() {
		$r = $this->paginate();
		$buffer = null;
		$index = '首页';
		$pre = '上一页';
		$next = '下一页';
		$last = '末页';

		if ($this->pageCount<=6) { 
			$min = 1;
			$max = $this->pageCount;
			$range = range(1,$this->pageCount);
		} else {
			if($this->pageNo == 1)
			{
				$min = 1;
				$max = $this->pageNo + 4;
			}
			else if($this->pageNo == 2)
			{
				$min = 1;
				$max = $this->pageNo + 3;
			}
			else if($this->pageNo > 2 && $this->pageNo<=$this->pageCount)
			{
				if($this->pageNo == $this->pageCount -1)
				{
					$min = $this->pageNo-3;
					$max = $this->pageNo + 1;
				}
				else if($this->pageNo == $this->pageCount)
				{
					$min = $this->pageNo-4;
					$max = $this->pageCount;
				}
				else 
				{
					$min = $this->pageNo-2;
					$max = $this->pageNo + 2;
				}
			}
			$range = range($min, $max);
		}
		$buffer .= '<div class="page">';
		//$buffer .= "<li>({$this->rowCount})</li>";
		if ($this->pageNo > 1 && $this->pageNo < 3) {
			$buffer .= "<a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a>";
		}
		else if($this->pageNo > 2 && $this->pageNo<=$this->pageCount)
		{
			if($min == 1)
			{
				$buffer .= "<a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a>";
			}
			else if($min == 2)
			{
				$buffer .= "<a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a><a href='".$this->genURL($this->pageString,1)."'>1</a>";
			}
			else 
			{
				$buffer .= "<a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a><a href='".$this->genURL($this->pageString,1)."'>1</a><em>...</em>";
			}
		}
		foreach($range AS $one) {
			if ( $one == $this->pageNo ) {
				$buffer .= "<span>{$one}</span>";
			} else {
				$buffer .= "<a href='".$this->genURL($this->pageString,$one)."'>{$one}</a>";
			}
		}
		if ($this->pageNo < $this->pageCount) {
			if(($max+1) > $this->pageCount)
			{
				$buffer .= "<a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a>";
			}
			else if(($max+1) == $this->pageCount)
			{
				$buffer .= "<a href='".$this->genURL($this->pageString, $this->pageCount)."'>{$this->pageCount}</a><a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a>";
			}
			else 
			{
				$buffer .= "<em>...</em><a href='".$this->genURL($this->pageString, $this->pageCount)."'>{$this->pageCount}</a><a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a>";
			}
			//$buffer .= "";
		}
		return $buffer . '</div>';
	}
	public function GenBasic2() {
		$r = $this->paginate();
		$buffer = null;
		$index = '首页';
		$pre = '上一页';
		$next = '下一页';
		$last = '末页';
	
		if ($this->pageCount<=6) {
			$min = 1;
			$max = $this->pageCount;
			$range = range(1,$this->pageCount);
		} else {
			if($this->pageNo == 1)
			{
				$min = 1;
				$max = $this->pageNo + 4;
			}
			else if($this->pageNo == 2)
			{
				$min = 1;
				$max = $this->pageNo + 3;
			}
			else if($this->pageNo > 2 && $this->pageNo<=$this->pageCount)
			{
				if($this->pageNo == $this->pageCount -1)
				{
					$min = $this->pageNo-3;
					$max = $this->pageNo + 1;
				}
				else if($this->pageNo == $this->pageCount)
				{
					$min = $this->pageNo-4;
					$max = $this->pageCount;
				}
				else
				{
					$min = $this->pageNo-2;
					$max = $this->pageNo + 2;
				}
			}
			$range = range($min, $max);
		}
		$buffer .= '<ul>';
		//$buffer .= "<li>({$this->rowCount})</li>";
		if ($this->pageNo > 1 && $this->pageNo < 3) {
			$buffer .= "<li><a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a></li>";
		}
		else if($this->pageNo > 2 && $this->pageNo<=$this->pageCount)
		{
			if($min == 1)
			{
				$buffer .= "<li><a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a></li>";
			}
			else if($min == 2)
			{
				$buffer .= "<li><a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a></li><li><a href='".$this->genURL($this->pageString,1)."'>1</a></li>";
			}
			else
			{
				$buffer .= "<li><a href='".$this->genURL($this->pageString,$this->pageNo-1)."'>{$pre}</a></li><li><a href='".$this->genURL($this->pageString,1)."'>1</a></li><li>...</li>";
			}
		}
		foreach($range AS $one) {
			if ( $one == $this->pageNo ) {
				$buffer .= "<li><span>{$one}</span></li>";
			} else {
				$buffer .= "<li><a href='".$this->genURL($this->pageString,$one)."'>{$one}</a></li>";
			}
		}
		if ($this->pageNo < $this->pageCount) {
			if(($max+1) > $this->pageCount)
			{
				$buffer .= "<li><a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a></li>";
			}
			else if(($max+1) == $this->pageCount)
			{
				$buffer .= "<li><a href='".$this->genURL($this->pageString, $this->pageCount)."'>{$this->pageCount}</a></li><li><a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a></li>";
			}
			else
			{
				$buffer .= "<li>...</li><li><a href='".$this->genURL($this->pageString, $this->pageCount)."'>{$this->pageCount}</a></li><li><a href='".$this->genURL($this->pageString,$this->pageNo+1)."'>{$next}</a></li>";
			}
			//$buffer .= "";
		}
// 		echo $buffer;die;
		return $buffer . '<li>转到第</li>
                    <li><input type="text" id="page_url_jump" class="ipt" /></li><li><input type="button" id="page_url_button" value="跳转" class="btn" /></li></ul>';
	}
}
?>
