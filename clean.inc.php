<?php
/* function clean($elem) 
transform $elem content so that it cant be harmful to your code */

function clean($elem) 
{ 
    if(!is_array($elem)) 
        $elem = htmlentities($elem,ENT_QUOTES,"UTF-8"); 
    else 
        foreach ($elem as $key => $value) 
            $elem[$key] = clean($value); 
    return $elem; 
}