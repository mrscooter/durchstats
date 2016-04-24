<?php
// src/Controller/ArticlesController.php

namespace App\Controller;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Datasource\ConnectionManager;

class AdminsController extends AppController {
    
    public function matchLoginPass($value, $context){
        $conn = ConnectionManager::get('default');
        
        $loginAndPass = $conn->execute('
            SELECT login, password
            FROM admins
            WHERE login = :login
        ', ['login' => $context['data']['login']])->fetch('assoc');
        
        if(!$loginAndPass){
            return false;
        }    
        
        if((new DefaultPasswordHasher)->check($context['data']['password'],$loginAndPass['password'])){
            return true;
        }
        else{
            return false;
        }   
    }
    
    private function getLoginValidator(){
        $validator = new Validator();
        $validator
            ->requirePresence('login')
            ->requirePresence('password')
            ->add('login', 'match', [
                'rule' => [$this, 'matchLoginPass'],
                'message' => 'Nesprávne prihlasovacie údaje.'
            ]);
        
        return $validator;
    }

    public function login(){
        $loginValidator = $this->getLoginValidator();
        
        if($this->request->is('post')) {
            $errors = $loginValidator->errors($this->request->data);
            
            if(empty($errors)){
                $this->request->session()->write('admin.login', $this->request->data['login']);
                $this->redirect('/');
            }
            else{
                $this->set('errors',$errors);
            }
        }
        
    }
    
    public function logout(){
        $this->request->session()->delete('admin.login');
        $this->redirect('/');
    }
    
}

?>