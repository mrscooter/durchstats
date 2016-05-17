<?php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validator;

class PlayersController extends AppController {
    
    private function getPlayerValidator($possibleClubsIds){
        $validator = new Validator();
        
        $validator
            ->requirePresence('name')
            ->requirePresence('surname')
            ->notEmpty('name', 'Musíš zadať meno')
            ->notEmpty('surname', 'Musíš zadať priezvisko')
            ->add('name', 'name', [
                'rule' => ['alphaNumeric'],
                'message' => 'Meno obsahuje nepovolené znaky'
            ])
            ->add('surname', 'surname', [
                'rule' => ['alphaNumeric'],
                'message' => 'Priezvisko obsahuje nepovolené znaky'
            ])
            ->add('club_id', 'club_id', [
                'rule' => ['inList', $possibleClubsIds],
                'message' => 'Zvolený klub neexistuje'
            ])
            ;
        
        return $validator;
    }
    
    public function hunAdd(){
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
        
        $validationErrors = [];
        if($this->request->is('post')){
            
            $playerValidator = $this->getPlayerValidator($this->getIdsArray($allClubs, [0]));
            
            $validationErrors = $playerValidator->errors($this->request->data);
            
            if(empty($validationErrors)){
                
                $insertError = $conn->insert('players', [
                    'name' => $this->request->data['name'],
                    'surname' => $this->request->data['surname'],
                    'club_id' => $this->request->data['club_id'] ? $this->request->data['club_id'] : null
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError != 0){
                    $this->set('actionMsg', 'Chyba pri pridávaní hráča. Skús to znovu.');
                }
                else{
                    $this->set('actionMsg', 'Hráč úspešne pridaný.');
                }
            }
        }
        
        $this->set('validationErrors', $validationErrors);
        $this->render("player_form");
    }
    
    public function hunList(){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $allPlayers = $conn->execute('
            SELECT p.*, c.name as club_name
            FROM players p
            LEFT JOIN clubs c ON p.club_id = c.id
        ')->fetchAll('assoc');
        $this->set('allPlayers', $allPlayers);
    }
    
    public function hunEdit($player_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($player_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $player = $conn->execute('
            SELECT p.*, c.name as club_name
            FROM players p
            LEFT JOIN clubs c ON p.club_id = c.id
            WHERE p.id = :player_id
        ',['player_id' => $player_id], ['player_id' => 'integer'])->fetch('assoc');
        $this->set('player', $player);
        
        $allClubs = $conn->execute('
            SELECT *
            FROM clubs
        ')->fetchAll('assoc');
        $this->set('allClubs', $allClubs);
        
        $validationErrors = [];
        if(!$this->request->is('post')){
            $this->request->data['name'] = $player['name'];
            $this->request->data['surname'] = $player['surname'];
            $this->request->data['club_id'] = $player['club_id'];
        }
        else {
            
            $playerValidator = $this->getPlayerValidator($this->getIdsArray($allClubs, [0]));
            
            $validationErrors = $playerValidator->errors($this->request->data);
            
            if(empty($validationErrors)){
                
                $updateError = $conn->update('players', [
                    'name' => $this->request->data['name'],
                    'surname' => $this->request->data['surname'],
                    'club_id' => $this->request->data['club_id'] ? $this->request->data['club_id'] : null
                ], ['id' => $player_id])->errorCode();
                
                if($updateError != 0){
                    $this->set('actionMsg', 'Chyba pri upravovaní hráča. Skús to znovu.');
                }
                else{
                    $this->set('actionMsg', 'Hráč úspešne upravený.');
                }
            }
        }
        
        $this->set('validationErrors', $validationErrors);
        $this->render("player_form");
    }
}

?>