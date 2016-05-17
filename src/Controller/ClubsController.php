<?php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validator;

class ClubsController extends AppController {
    
    private function getClubValidator(){
        $validator = new Validator();
        
        $validator
            ->requirePresence('name')
            ->notEmpty('name', 'Musíš zadať názov klubu')
            ->add('name', 'name', [
                'rule' => ['custom', '/^[0-9\p{L}\. ]+$/u'],
                'message' => 'Názov klubu môže obsahovať iba číslice, písmená, medzere a bodky'
            ])
            ->add('logo', [
                'isPng' => [
                    'rule' => function ($value, $context){
                            $imageFileType = pathinfo($value['name'],PATHINFO_EXTENSION);
                            return strcasecmp($imageFileType, 'png') == 0;
                        },
                    'message' => 'Logo musí byť vo formáte .png'
                ],
                'noUploadError' => [
                    'rule' => function ($value, $context){
                            return $value['error'] == 0;
                        },
                    'message' => 'Chyba pri nahrávaní obrázku. (Nie je náhodou príliš velký?)'
                ]
            ]);
        
        return $validator;
    }
    
    public function hunAdd(){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $validationErrors = [];
        if($this->request->is('post')){
            $clubValidator = $this->getClubValidator();
            
            $validationErrors = $clubValidator->errors($this->request->data);
            
            if(empty($validationErrors)){
                
                $insertOK = $conn->transactional(function ($conn){
                    $insertStmt = $conn->insert('clubs', [
                        'name' => $this->request->data['name']
                    ]);
                    
                    if($insertStmt->errorCode() != 0){
                        return false;
                    }
                    
                    if(!move_uploaded_file($this->request->data['logo']['tmp_name'], 'img/teams/'.$insertStmt->lastInsertId().'.png')){
                        return false;
                    }
                    shell_exec('chmod g+w '.'img/teams/'.$insertStmt->lastInsertId().'.png');
                    
                    return true;
                });
                                
                $this->request->data = [];
                
                if(!$insertOK){
                    $this->set('actionMsg', 'Chyba pri pridávaní klubu. Skús to znovu.');
                }
                else{
                    $this->set('actionMsg', 'Klub úspešne pridaný.');
                }
            }
        }
        
        $this->set('validationErrors', $validationErrors);
        $this->render("club_form");
    }
    
    public function hunEdit($club_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($club_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $club = $conn->execute('
            SELECT *
            FROM clubs
            WHERE id = :club_id
        ', ['club_id' => $club_id], ['club_id' => 'integer'])->fetch('assoc');
        
        $validationErrors = [];
        if(!$this->request->is('post')){
            $this->request->data = $club;
        }
        else {
            $clubValidator = $this->getClubValidator();
            
            $validationErrors = $clubValidator->errors($this->request->data);
            
            if(empty($validationErrors)){
                
                $updateOK = $conn->transactional(function ($conn) use ($club_id){
                    $updateStmt = $conn->update('clubs', [
                        'name' => $this->request->data['name']
                    ], ['id' => $club_id]);
                    
                    if($updateStmt->errorCode() != 0){
                        return false;
                    }
                    
                    if(!move_uploaded_file($this->request->data['logo']['tmp_name'], 'img/teams/'.$club_id.'.png')){
                        return false;
                    }
                    shell_exec('chmod g+w '.'img/teams/'.$club_id.'.png');
                    
                    return true;
                });
                                
                if(!$updateOK){
                    $this->set('actionMsg', 'Chyba pri upravovaní klubu. Skús to znovu.');
                }
                else{
                    $this->set('actionMsg', 'Klub úspešne upravený.');
                }
            }
        }
        
        $this->set('validationErrors', $validationErrors);
        $this->render("club_form");
    }
    
    public function hunList(){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $allClubs = $conn->execute('
            SELECT *
            FROM clubs
        ')->fetchAll('assoc');
        $this->set('allClubs', $allClubs);
    }
    
}

?>