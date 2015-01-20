<?php
// 验证码解析
class CodeCheck {
	private $filename;
	private $zimo;

	public $opt = array(
		'left'         => 10,  // 首字母开始坐标x
		'top'          => 5,   // 首字母开始坐标y
		'word_spacing' => 0,   // 字符间距
		'height'       => 10,  // 单字符高度
		'width'        => 9,   // 单字符宽度
		'color'        => 215, // 二值化阀值
		'size_num'     => 5,   //
		'tolerance'	   => 12,  // 单字错误数量
	);

	public function __construct($filename, $zimofile){
		$this->filename = $filename;			
		$this->zimo = require $zimofile;
	}

	public function SetOpt($opt) {
		foreach ($opt as $key => $value) {
			$this->opt[$key] = $value;
		}
	}

	public function Check(){
		$filename = $this->filename;

		list($width,$height) = getimagesize($filename);	
		$rs = imagecreatefrompng($this->filename);

		//取特征值
		for ($i=0; $i < $height; $i++) { 
			for ($j=0; $j < $width; $j++) { 
				$index = imagecolorat($rs, $j, $i);
				$rgb = imagecolorsforindex($rs, $index);
				
				if ($rgb['red']> $this->opt['color'] && $rgb['blue']> $this->opt['color'] && $rgb['green']> $this->opt['color']) {					
					$sourceData[$i][$j]=1;	
				}else{
					$sourceData[$i][$j]=0;
				}
			}
		}

		for ($i=0; $i < $height; $i++) { 
			for ($j=0; $j < $width; $j++) { 
				echo $sourceData[$i][$j];
			}
			echo "\n";
		}

		$desData = $sourceData;

		$chData = $this->getCH($desData, 5);

		$digtal = $this->vertifyCode($chData);

		return $digtal;
	}

	//去噪点 阀值5
	function clear($sourceData){
		$desData = array();
		$h =count($sourceData,0);
		$w =count($sourceData[0]);

		for ($i=1; $i < $h-1; $i++) { 
			for ($j=1; $j < $w-1; $j++) { 
				$value = $sourceData[$i-1][$j]+$sourceData[$i+1][$j]+$sourceData[$i][$j-1]+$sourceData[$i][$j+1]
	                +$sourceData[$i-1][$j-1]+$sourceData[$i+1][$j+1]+$sourceData[$i-1][$j+1]+$sourceData[$i+1][$j-1];
				if ($value>=5) {
					$desData[$i-1][$j-1] = 1;
				}else{
					$desData[$i-1][$j-1] = 0;
				}
			}
		}
		return $desData;
	}

	//字符分割
	function getCH($data){
		//第一个左上角坐标
		$y = $this->opt['top'];
		$x = $this->opt['left'];
		$height = $this->opt['height'];
		$width = $this->opt['width'];

		$chData = array();

		for ($z=0; $z < $this->opt['size_num']; $z++) { 
			$ch = 0;
			$startX = $x + $z * $this->opt['width'];			
			if ($z > 0) {
				$startX += ($z-1) * $this->opt['word_spacing'];
			}

			for ($i=$y; $i < $y + $this->opt['height']; $i++) {
				for ($j=$startX; $j < $startX + $this->opt['width']; $j++) { 
					
					$chData[$z][$ch] = $data[$i][$j];
					$ch++;
				}
			}
		}
		
		return $chData;
	}

	function vertifyCode($chData){
		//字模
		$typehead = $this->zimo;
		$ch = array();
		$w = count($chData[0]);

		for ($i=0; $i < count($chData); $i++) { 
			for ($k=0; $k < 10; $k++) { 
				if (!is_array($typehead[$k])) {
					if ($typehead[$k] == $chData[$i]){
						$ch[$i] = $k;
						break;
					}
				}else{
					foreach ($typehead[$k] as $v) {
						$subMount = 0;
						$sub = array();
						for ($m=0; $m < $w; $m++) { 
							if ($chData[$i][$m] == $v[$m]) {
								$subMount++;
							}
						}
						$sub[]=$subMount;
					}
					$mount = max($sub);
					

					if ($w - $mount < $this->opt['tolerance']) {
						$ch[$i] = $k;
						break;
					}
				}
			}

			if (!isset($ch[$i])) {
				$ch[$i] = "?";
			}
		}

		return $ch;
	}
}

?>