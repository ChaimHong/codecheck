<?php
require_once("../codecheck.php");
$zimofile = "../zimo.php";

$obj = new CodeCheck("test.png", $zimofile);
$obj->SetOpt(array(	
	'size_num'   => 5,
	'tolerance'  => 12, // 调整容错值，找出合适的结果
));

$res = $obj->Check();
echo "结果：";
foreach ($res as $v) {
	echo $v;
}
echo "\n";
