<?php 
require_once("$CFG->dirroot/mod/casecourseware/lib.php");
function casecourseware_fh32($str) {
	$ss = "KFITUGAQWYFHDGERUTYGHDSAEUTIGHDNCJGUQOFKGLHKBMVPTOYIGJDHSYFHRUFGKFITUGAQWYFHDGERUTYGHDSAEUTIGHDNCJGUQOFKGLHKBMVPTOYIGJDHSYFHRUFG";
	$data = str_split($str);
        $bit_array = array();
        $secret = str_split($ss);
        $l;
	$c;
        $r = "";
        $i = 0;
	$j = 0;

	$l = count($secret);
        for($i=0;$i<$l;$i++) {
               	if($i < 32) { 
			$bit_array[$i] = ord($secret[$i]);
       		} 
	}

	$l = count($data);
        for($i=0;$i<$l;$i++) {
                $c = ord($data[$i]);
                $bit_array[$c%32]+=$c;
        }

	$l = count($bit_array);
        for($i=0;$i<7;$i++) {
                for($j=0;$j<$l;$j++) {
			$c = $bit_array[$j];
                        $bit_array[(($c%32)+1)%32]+=$c;
                }
        }

        for($i=0;$i<$l;$i++) {
                $r .= chr(($bit_array[$i]%26)+65);
        }

        return $r;
}
?>