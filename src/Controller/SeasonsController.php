<?php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validator;

class SeasonsController extends AppController {
    
    private function getSeasonValidator(array $allSeasonsYears){
        $validator = new Validator();
        
        $validator
            ->requirePresence('year')
            ->notEmpty('year', 'Musíš zadať rok')
            ->add('year', 'year', [
                'rule' => ['naturalNumber'],
                'message' => 'Rok musí byť číslo'
            ])
            ->add('year', 'Duplicitný rok', [
                'rule' => function ($value, $context) use ($allSeasonsYears){
                    foreach($allSeasonsYears as $year){
                        if($value == $year['year']){
                            return false;
                        }
                    }
                    
                    return true;
                },
                'message' => 'Sezóna v tomto roku už existuje'
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
        
        $validationErrors = [];
        if($this->request->is('post')){
            
            $allSeasonsYears = $conn->execute('
                SELECT year
                FROM seasons
            ')->fetchAll('assoc');
            $seasonValidator = $this->getSeasonValidator($allSeasonsYears);
            
            $validationErrors = $seasonValidator->errors($this->request->data);
            
            if(empty($validationErrors)){
                $insertError = $conn->insert('seasons', [
                    'year' => $this->request->data['year']
                ],[
                    'year' => 'integer'
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError != 0){
                    $this->set('insertMsg', 'Chyba pri pridávaní sezóny. Skús to znovu.');
                }
                else{
                    $this->set('insertMsg', 'Sezóna úspešne pridaná.');
                }
            }
        }
        
        $this->set('validationErrors', $validationErrors);
    }
    
    public function hunList(){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $allSeasons = $conn->execute('
            SELECT *
            FROM seasons
        ')->fetchAll('assoc');
    }
    
    public function hunSetActual($season_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($season_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');

        $setActualOK = $conn->transactional(function ($conn) use ($season_id){
            $setActualZeroStmt = $conn->execute('
                UPDATE seasons
                SET actual=0
            ');

            if($setActualZeroStmt->errorCode() != 0){
                return false;
            }

            $setActualStmt = $conn->execute('
                UPDATE seasons
                SET actual=1
                WHERE id=:season_id
            ',['season_id' => $season_id],['season_id' => 'integer']);

            if($setActualStmt->errorCode() != 0){
                return false;
            }
            
            return true;
        });
        
        if(!$setActualOK){
            $this->request->session()->write('hunSetActual.updateMsg','* Pri nastavovaní aktuálnej sezóny vznikla neočakávaná chyba. Skús to znovu.');
        }
        $this->redirect(['controller' => 'Seasons', 'action' => 'hunList']);
    }
    
    public function hunEdit($season_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($season_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $clubsInSeason = $conn->execute('
            SELECT c.*
            FROM clubs_seasons cs
            JOIN clubs c ON c.id = cs.club_id
            WHERE cs.season_id = :season_id
            ORDER BY c.name ASC
        ', ['season_id' => $season_id], ['season_id' => 'integer'])->fetchAll('assoc');
        $this->set('clubsInSeason', $clubsInSeason);
        
        $clubsNotInSeason = $conn->execute('
            SELECT c.name, c.id
            FROM 
                (SELECT c.id, c.name, cs.season_id
                FROM clubs c
                JOIN clubs_seasons cs ON c.id = cs.club_id
                WHERE cs.season_id = :season_id 
                ) c_in_s
            RIGHT JOIN clubs c ON c.id = c_in_s.id
            WHERE c_in_s.season_id IS NULL
            ORDER BY c.name ASC
        ', ['season_id' => $season_id], ['season_id' => 'integer'])->fetchAll('assoc');
        $this->set('clubsNotInSeason', $clubsNotInSeason);
        
        $season = $conn->execute('
            SELECT *
            FROM seasons
            WHERE id = :season_id
        ', ['season_id' => $season_id], ['season_id' => 'integer'])->fetch('assoc');
        $this->set('season', $season);
    }
    
    private function getAddClubValidator($possibleClubIds){
        $validator = new Validator();
        
        $validator
            ->requirePresence('club_id')
            ->add('club_id', 'club_id', [
                'rule' => ['inList', $possibleClubIds],
                'message' => 'Tento klub alebo neexistuje alebo už v danej sezóne účinkuje'
            ])
            ;
        
        return $validator;
    }
    
    public function hunAddClub($season_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($season_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $clubsNotInSeason = $conn->execute('
            SELECT c.name, c.id
            FROM 
                (SELECT c.id, c.name, cs.season_id
                FROM clubs c
                JOIN clubs_seasons cs ON c.id = cs.club_id
                WHERE cs.season_id = :season_id 
                ) c_in_s
            RIGHT JOIN clubs c ON c.id = c_in_s.id
            WHERE c_in_s.season_id IS NULL
        ', ['season_id' => $season_id], ['season_id' => 'integer'])->fetchAll('assoc');
        
        if($this->request->is('post')){
            
            $addClubValidator = $this->getAddClubValidator($this->getIdsArray($clubsNotInSeason));
            
            $validationErrors = $addClubValidator->errors($this->request->data);
            $this->request->session()->write('addClubToSeason.validationErrors', $validationErrors);
            
            if(empty($validationErrors)){
                $insertError = $conn->insert('clubs_seasons', [
                    'club_id' => $this->request->data['club_id'],
                    'season_id' => $season_id
                ],[
                    'club_id' => 'integer',
                    'season_id' => 'integer'
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError != 0){
                    $this->request->session()->write('addClubToSeason.insertMsg', 'Chyba pri pridávaní klubu. Skús to znovu.');
                }
                else{
                    $this->request->session()->write('addClubToSeason.insertMsg', 'Klub úspešne pridaný');
                }
            }
        }
        
        $this->redirect(['controller' => 'Seasons', 'action' => 'hunEdit', $season_id]);
    }
    
    public function hunDeleteClub($season_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($season_id) || !$this->isNaturalNumber($this->request->data['club_id'])){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        if($this->request->is('post')){
            $deleteError = $conn->execute('
                DELETE FROM clubs_seasons
                WHERE season_id = :season_id AND club_id = :club_id
            ', 
            ['season_id' => $season_id, 'club_id' => $this->request->data['club_id'] ], 
            ['season_id' => 'integer', 'club_id' => 'integer'])->errorCode();
            
            if($deleteError != 0){
                $this->request->session()->write('deleteClubInSeason.deleteError', 'Chyba pri mazaní klubu. Skús to znovu.');
            }
        }
        
        $this->redirect(['controller' => 'Seasons', 'action' => 'hunEdit', $season_id]);
    }
}

?>