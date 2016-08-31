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
    
    public function isAdminLogged(){
        return $this->request->session()->read('admin.login');
    }
    
    public function formatAvgStat($statCount, $matchesCount){
        return number_format($matchesCount == 0 ? 0 : $statCount/$matchesCount, 2, ",", " ");
    }
}
?>