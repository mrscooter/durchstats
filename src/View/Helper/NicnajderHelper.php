<?php
namespace App\View\Helper;

use Cake\View\Helper;

class NicnajderHelper extends Helper {
    
    public function errorsForCell($validationErrors, $cellName){
        $errorStr = "";
        
        if(isset($validationErrors[$cellName])){
            foreach($validationErrors[$cellName] as $errorMsg){
                $errorStr .= "*".$errorMsg.". ";
            }
        }
        
        return $errorStr;
    }
}
?>