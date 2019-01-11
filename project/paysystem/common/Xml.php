<?php
namespace app\common;
class Xml {

	/**
	 * xml格式, 字符集请指定. 转码请提前完成
	 * 只支持键值对格式, 暂不支持索引数组
	 */
	public function toXml($data, $charset = 'UTF-8') {
		//1 转换成gbk的格式
		if (!is_array($data)) {
			return null;
		}
		$content = $this -> _array2xml($data);

		//2 根据数据生成
		$result = '<?xml version="1.0" encoding="' . $charset . '"?><stream>' . $content . '</stream>';
		return $result;
	}

	private function _array2xml($data) {
		$xml = [];
		foreach ($data as $k => $v) {
			if (is_array($v)) {
				$xml[] = $this -> _array2xml($v);
			} else {
				$xml[] = "<{$k}>{$v}</{$k}>";
			}
		}
		return implode("\n", $xml);
	}

	/**
	 * convert xml string to php array - useful to get a serializable value
	 *
	 * @param string $xmlstr
	 * @return array
	 *
	 * @author Adrien aka Gaarf & contributors
	 * @see http://gaarf.info/2009/08/13/xml-string-to-php-array/
	 */
	public function toArray($xmlstr) {
		$doc = new \DOMDocument();
		$doc -> loadXML($xmlstr);
		$root = $doc -> documentElement;
		$output = $this -> domnode_to_array($root);
		$output['@root'] = $root -> tagName;
		return $output;
	}

	private function domnode_to_array($node) {
		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE :
			case XML_TEXT_NODE :
				$output = trim($node -> textContent);
				break;
			case XML_ELEMENT_NODE :
				for ($i = 0, $m = $node -> childNodes -> length; $i < $m; $i++) {
					$child = $node -> childNodes -> item($i);
					$v = $this -> domnode_to_array($child);
					if (isset($child -> tagName)) {
						$t = $child -> tagName;
						if (!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} elseif ($v || $v === '0') {
						$output = (string)$v;
					}
				}
				if ($node -> attributes -> length && !is_array($output)) {//Has attributes but isn't an array
					$output = array('@content' => $output);
					//Change output into an array.
				}
				if (is_array($output)) {
					if ($node -> attributes -> length) {
						$a = array();
						foreach ($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string)$attrNode -> value;
						}
						$output['@attributes'] = $a;
					}
					foreach ($output as $t => $v) {
						if (is_array($v) && count($v) == 1 && $t != '@attributes') {
							$output[$t] = $v[0];
						}
					}
				}
				break;
		}
		return $output;
	}

}
