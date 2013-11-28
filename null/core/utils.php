<?php

function makeShortName($fullname){
    $names = split(' ', $fullname);
    $result = $names[0].' ';
    foreach(array_slice($names, 1, 2) as $name)
        $result .= mb_substr($name,0,1).'. ';
            

    return $result;
}


function random_string($length){
$seed = str_split('abcdefghijklmnopqrstuvwxyz'
                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                 .'0123456789!@#$%^&*()'); // and any other characters
return $rand = implode('', array_rand($seed, $length));
}
//mb_internal_encoding('UTF-8');
//print makeShortName("Лайонел Майкл Ритчи-Джонсон");

?>
