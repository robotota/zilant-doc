<?php 
class Plural { 

const MALE = 1; 
const FEMALE = 2; 
const NEUTRAL = 3; 

protected static $_digits = array( 
self::MALE => array('ноль', 'один', 'два', 'три', 'четыре','пять', 'шесть', 'семь', 'восемь', 'девять'), 
self::FEMALE => array('ноль', 'одна', 'две', 'три', 'четыре','пять', 'шесть', 'семь', 'восемь', 'девять'), 
self::NEUTRAL => array('ноль', 'одно', 'два', 'три', 'четыре','пять', 'шесть', 'семь', 'восемь', 'девять') 
); 

protected static $_ths = array( 
0 => array('','',''), 
1=> array('тысяча', 'тысячи', 'тысяч'),	
2 => array('миллион', 'миллиона', 'миллионов'), 
3 => array('миллиард','миллиарда','миллиардов'), 
4 => array('триллион','триллиона','триллионов'), 
5 => array('квадриллион','квадриллиона','квадриллионов') 
); 

protected static $_ths_g = array(self::NEUTRAL, self::FEMALE, self::MALE, self::MALE, self::MALE, self::MALE); // hack 4 thsds 

protected static $_teens = array( 
0=>'десять', 
1=>'одиннадцать', 
2=>'двенадцать', 
3=>'тринадцать', 
4=>'четырнадцать', 
5=>'пятнадцать', 
6=>'шестнадцать', 
7=>'семнадцать', 
8=>'восемнадцать', 
9=>'девятнадцать' 
); 

protected static $_tens = array( 
2=>'двадцать', 
3=>'тридцать', 
4=>'сорок', 
5=>'пятьдесят', 
6=>'шестьдесят', 
7=>'семьдесят', 
8=>'восемьдесят', 
9=>'девяносто' 
); 

protected static $_hundreds = array( 
1=>'сто', 
2=>'двести', 
3=>'триста', 
4=>'четыреста', 
5=>'пятьсот', 
6=>'шестьсот', 
7=>'семьсот', 
8=>'восемьсот', 
9=>'девятьсот' 
); 

protected function _ending($value, array $endings = array()) {
         $result = '';
         if ($value < 2) {
            if ($value == 0) {
               $result = $endings[2];
            } else {
               $result = $endings[0];
            }
         } elseif ($value < 5) {
            $result = $endings[1];
         } else $result = $endings[2];
         return $result;    
     }
     
protected function _triade($value, $mode = self::MALE, array $endings = array(), $final) { 
	$result = ''; 
	if ($value == 0 )
		//if (!$final)
	   		{ return $result; }
	   	//else {
	   	//	$result .= "рублей";
	   	//	return $result;
	   	//};	 
	
	$triade = str_split(str_pad($value,3,'0',STR_PAD_LEFT)); 
	if ($triade[0]!=0) { $result.= (self::$_hundreds[$triade[0]].' '); } 
	if ($triade[1]==1) { $result.= (self::$_teens[$triade[2]].' '); } 
	elseif(($triade[1]!=0)) { $result.= (self::$_tens[$triade[1]].' '); } 
	if (($triade[2]!=0)&&($triade[1]!=1)) { $result.= (self::$_digits[$mode][$triade[2]].' '); } 
	if ($value!=0) { $ends = ($triade[1]==1?'1':'').$triade[2];
	$result.= self::_ending($ends,$endings).' '; } 
	return $result; 
} 

public function asString($value, $mode = self::MALE, array $endings = array()) { 
if (empty($endings)) { $endings = array('','',''); } 
$result = ''; 
$steps = ceil(strlen($value)/3); 
$sv = str_pad($value, $steps*3, '0', STR_PAD_LEFT); 
for ($i=0; $i<$steps; $i++) { 
	$triade = substr($sv, $i*3, 3); 
	$iter = $steps - $i; 
	$ends = ($iter!=1)?(self::$_ths[$iter-1]):($endings); 
	$gender = ($iter!=1)?(self::$_ths_g[$iter-1]):($mode); 
	$result.= self::_triade($triade,$gender, $ends, $iter == 1); 
} 
return $result; 
} 

} 
?>