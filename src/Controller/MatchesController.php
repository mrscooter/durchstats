<?php
// src/Controller/ArticlesController.php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validator;

class MatchesController extends AppController {
    
    public function viewSeason($season_id){
        if(!$this->isNaturalNumber($season_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $matchesInActualSeason = $conn->execute('
            SELECT matches.*, home_club.name home_name, away_club.name away_name
            FROM matches
            JOIN clubs home_club ON matches.home_id = home_club.id
            JOIN clubs away_club ON matches.away_id = away_club.id
            WHERE matches.season_id = :season_id
            ORDER BY matches.date_time ASC
        ', ['season_id' => $season_id], ['season_id' => 'integer']);
        $matchesInActualSeason = $matchesInActualSeason->fetchAll('assoc');
        
        foreach($matchesInActualSeason as &$match){
            $match["date_time"] = new \DateTime($match["date_time"]);
            
            $eventsInMatch = $conn->execute('
                SELECT mem.event_id, mp.club_id
                FROM match_events_matches mem
                JOIN matches_players mp ON (mp.player_id = mem.player_id AND mp.match_id = :match_id)
                WHERE mem.match_id = :match_id
            ', ["match_id" => $match["id"]], 
               ["match_id" => "integer"]
            );
            
            $eventsInMatch = $eventsInMatch->fetchAll('assoc');
            $match['score'] = $this->countScore($eventsInMatch, $match['home_id'], $match['away_id']);
        }
        unset($match);
        
        $this->set('matchesInActualSeason', $matchesInActualSeason);
        $this->set('season_id', $season_id);
    }
    
    private function countScore($eventsInMatch, $home_id, $away_id){       
        $matchScore["home_score"] = 0;
        $matchScore["away_score"] = 0;
        $matchScore["home_shootout_score"] = 0;
        $matchScore["away_shootout_score"] = 0;

        foreach($eventsInMatch as $event){
            if($event["club_id"] == $home_id){
                if($event["event_id"] == GOAL_EVENT_ID){
                    $matchScore["home_score"]++;
                }
                else if($event["event_id"] == OWN_GOAL_EVENT_ID){
                    $matchScore["away_score"]++;
                }
                else if($event["event_id"] == SHOOTOUT_GOAL_EVENT_ID){
                    $matchScore["home_shootout_score"]++;
                }
            }
            else if($event["club_id"] == $away_id){
                if($event["event_id"] == GOAL_EVENT_ID){
                    $matchScore["away_score"]++;
                }
                else if($event["event_id"] == OWN_GOAL_EVENT_ID){
                    $matchScore["home_score"]++;
                }
                else if($event["event_id"] == SHOOTOUT_GOAL_EVENT_ID){
                    $matchScore["away_shootout_score"]++;
                }
            }
        }
        
        return $matchScore;
    }
    
    public function view($match_id){
        if(!$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $matchInfo = $conn->execute('
            SELECT matches.*, home_club.name home_name, away_club.name away_name, match_phases.name match_phase_name
            FROM matches
            JOIN clubs home_club ON matches.home_id = home_club.id
            JOIN clubs away_club ON matches.away_id = away_club.id
            LEFT JOIN match_phases ON match_phases.id = matches.match_phase_id
            WHERE matches.id = :match_id
        ',["match_id" => $match_id]);
        $matchInfo = $matchInfo->fetch('assoc');
        $matchInfo["date_time"] = new \DateTime($matchInfo["date_time"]);
        
        $matchRefrees = $conn->execute('
            SELECT id, name, surname
            FROM referees_matches
            WHERE match_id = :match_id
        ',["match_id" => $match_id]);
        $matchRefrees = $matchRefrees->fetchAll('assoc');
        
        $matchPlayers = $conn->execute('
            SELECT mp.player_id, mp.club_id, p.name, p.surname, c.name club_name
            FROM matches_players mp
            JOIN players p ON mp.player_id = p.id
            JOIN clubs c ON mp.club_id = c.id
            WHERE match_id = :match_id
            ORDER BY mp.club_id
        ',["match_id" => $match_id]);
        $matchPlayers = $this->associateByColumn($matchPlayers->fetchAll('assoc'), "player_id");
                
        $matchEvents = $conn->execute('
            SELECT mem.*, mp.club_id
            FROM match_events_matches mem
            JOIN matches_players mp ON (mp.player_id = mem.player_id AND mp.match_id = :match_id)
            WHERE mem.match_id = :match_id
            ORDER BY minute ASC, id ASC
        ',["match_id" => $match_id]);
        $matchEvents = $matchEvents->fetchAll('assoc');
        
        $matchInfo['score'] = $this->countScore($matchEvents, $matchInfo['home_id'], $matchInfo['away_id']);
        
        $events['goals'] = array();
        $events['shootout_goals'] = array();
        $events['yellow_cards'] = array();
        $events['red_cards'] = array();
        foreach ($matchEvents as $event){
            if($event['event_id'] == GOAL_EVENT_ID){
                if(isset($matchPlayers[$event['player_id']]['goals'])){
                    $matchPlayers[$event['player_id']]['goals']++;
                }
                else{
                    $matchPlayers[$event['player_id']]['goals'] = 1;
                }
                
                $events['goals'][] = $event;
            }
            else if($event['event_id'] == OWN_GOAL_EVENT_ID){
                if(isset($matchPlayers[$event['player_id']]['own_goals'])){
                    $matchPlayers[$event['player_id']]['own_goals']++;
                }
                else{
                    $matchPlayers[$event['player_id']]['own_goals'] = 1;
                }
                
                $events['goals'][] = $event;
            }
            else if($event['event_id'] == YELLOW_CARD_EVENT_ID){
                if(isset($matchPlayers[$event['player_id']]['yellow_cards'])){
                    $matchPlayers[$event['player_id']]['yellow_cards'] = 2;
                    $matchPlayers[$event['player_id']]['red_cards'] = 1;
                    
                    $events['red_cards'][] = $event;
                }
                else{
                    $matchPlayers[$event['player_id']]['yellow_cards'] = 1;
                }
                
                $events['yellow_cards'][] = $event;
            }
            else if($event['event_id'] == RED_CARD_EVENT_ID){
                $matchPlayers[$event['player_id']]['red_cards'] = 1;
                
                $events['red_cards'][] = $event;
            }
            else if($event['event_id'] == SHOOTOUT_GOAL_EVENT_ID){
                $events['shootout_goals'][] = $event;
            }
        }
        
        $this->set('matchInfo',$matchInfo);
        $this->set('events',$events);
        $this->set('players',$matchPlayers);
        $this->set('referees',$matchRefrees);
        
        if($this->isAdminLogged()){
            $possibleMatchEvents = $conn->execute("
                SELECT *
                FROM match_events
            ")->fetchAll('assoc');
            $this->set('possibleMatchEvents',$possibleMatchEvents);
            
            $possibleMatchPhases = $conn->execute("
                SELECT *
                FROM match_phases
            ")->fetchAll('assoc');
            $this->set('possibleMatchPhases',$possibleMatchPhases);
            
            $matchEventsForAdminsEventList = $conn->execute('
                SELECT mem.*, mp.club_id, p.name player_name, p.surname player_surname, me.name event_name
                FROM match_events_matches mem
                JOIN matches_players mp ON (mp.player_id = mem.player_id AND mp.match_id = :match_id)
                JOIN players p ON (p.id = mem.player_id)
                JOIN match_events me ON (me.id = mem. event_id)
                WHERE mem.match_id = :match_id
                ORDER BY minute ASC, id ASC
            ',["match_id" => $match_id])->fetchAll('assoc');
            $this->set('matchEventsForAdminsEventList',$matchEventsForAdminsEventList);
        }
    }
    
    private function getMatchValidator(array $teamsInSeasonsIds, array $matchPhasesIds, array $seasonPhasesIds){
        $validator = new Validator();
        
        $validator
            ->requirePresence('home_id')
            ->requirePresence('away_id')
            ->requirePresence('round')
            ->requirePresence('date_time')
            ->requirePresence('match_phase_id')
            ->requirePresence('season_phase_id')
            ->add('home_id', 'in_this_season', [
                'rule' => ['inList', $teamsInSeasonsIds],
                'message' => 'Vybraný domáci tím nie je definovaný ako účastník tejto sezóny'
            ])
            ->add('away_id', [
                'in_this_season' => [
                    'rule' => ['inList', $teamsInSeasonsIds],
                    'message' => 'Vybraný hosťujúci tím nie je definovaný ako účastník tejto sezóny'
                ],
                'not_the_same_home_away' => [
                    'rule' => function ($value, $context){
                            return $context['data']['home_id'] != $context['data']['away_id'];
                        },
                    'message' => 'Súperiace tímy nesmú byť ten istý tím.'
                ]
            ])
            ->add('round', 'alphaNumericDot', [
                'rule' => ['custom', '/^[0-9\p{L}\. ]+$/u'],
                'message' => 'Údaj o kole musí byť neprázdny a môže obsahovať iba číslice, písmená, medzere a bodky'
            ])
            ->add('date_time', 'date_time', [
                'rule' => [$this, 'validateSlovakDateTime'],
                'message' => 'Nesprávny čas a dátum. Musí byť dodržaný formát &quot;DD.MM.RRRR HH:MM&quot; (napr. 07.05.2015)'
            ])
            ->add('playtime', 'alphaNumericDot', [
                'rule' => ['custom', '/^[0-9\p{L}\. ]+$/u'],
                'message' => 'Údaj o hracom čase musí byť neprázdny a môže obsahovať iba číslice, písmená, medzere a bodky'
            ])
            ->add('match_phase_id', 'match_phase_id', [
                'rule' => ['inList', $matchPhasesIds],
                'message' => 'Vybratá neplatná fáza zápasu'
            ])
            ->add('season_phase_id', 'season_phase_id', [
                'rule' => ['inList', $seasonPhasesIds],
                'message' => 'Vybratá neplatná fáza sezóny'
            ]);
        
        return $validator;
    }
      
    private function insertAllPlayersInMatch(array $playersInMatch, $matchId, $conn){
        foreach($playersInMatch as $player){
            $insertError = $conn->insert('matches_players', [
                'match_id' => $matchId,
                'player_id' => $player['id'],
                'club_id' => $player['club_id']
            ], [
                'match_id' => 'integer',
                'player_id' => 'integer',
                'club_id' => 'integer'
            ])->errorCode();
            
            if($insertError != 0){
                return false;
            }
        }
        
        return true;
    }
    
    public function hunAddToSeason($season_id){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        if(!$this->isNaturalNumber($season_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $clubsInSeason = $conn->execute('
            SELECT clubs.*
            FROM clubs
            JOIN clubs_seasons ON clubs.id = clubs_seasons.club_id
            WHERE clubs_seasons.season_id = :season_id
        ', 
        ["season_id" => $season_id], 
        ["season_id" => "integer"])->fetchAll('assoc');
        $this->set('clubsInSeason', $clubsInSeason);
        
        $matchPhases = $conn->execute('
            SELECT *
            FROM match_phases
        ')->fetchAll('assoc');
        $this->set('matchPhases', $matchPhases);
        
        $seasonPhases = $conn->execute('
            SELECT *
            FROM season_phases
        ')->fetchAll('assoc');
        $this->set('seasonPhases', $seasonPhases);
        
        $this->set('validationErrors',[]);
        if($this->request->is('post')){
            $matchValidator = $this->getMatchValidator(
                    $this->getIdsArray($clubsInSeason), 
                    $this->getIdsArray($matchPhases, ['0']),
                    $this->getIdsArray($seasonPhases));
            
            $validationErrors = $matchValidator->errors($this->request->data);
            $this->set('validationErrors',$validationErrors);
            
            if(empty($validationErrors)){
                $this->request->data['date_time'] = new \DateTime($this->request->data['date_time']);
                if($this->request->data['match_phase_id'] == 0){
                    $this->request->data['match_phase_id'] = null;
                }
                
                $insertOK = $conn->transactional(function ($conn) use ($season_id) {
                    $insertMatchStmtMedziksicht = $conn->insert('matches', [
                        'season_id' => $season_id,
                        'home_id' => $this->request->data['home_id'],
                        'away_id' => $this->request->data['away_id'],
                        'round' => $this->request->data['round'],
                        'date_time' => $this->request->data['date_time'],
                        'playtime' => $this->request->data['playtime'],
                        'match_phase_id' => $this->request->data['match_phase_id'],
                        'completed' => isset($this->request->data['completed']),
                        'season_phase_id' => $this->request->data['season_phase_id']
                    ], [
                        'season_id' => 'integer',
                        'home_id' => 'integer',
                        'away_id' => 'integer',
                        'date_time' => 'datetime',
                        'match_phase_id' => 'integer',
                        'completed' => 'boolean',
                        'season_phase_id' => 'integer'
                    ]);
                    
                    if($insertMatchStmtMedziksicht->errorCode() != 0){
                        return false;
                    }
                    $insertedMatchId = $insertMatchStmtMedziksicht->lastInsertId();
                    
                    $playersInMatchStmtMedziksicht = $conn->execute("
                        SELECT id, club_id
                        FROM players
                        WHERE club_id = :home_id OR club_id = :away_id
                    ",["home_id" => $this->request->data['home_id'], "away_id" => $this->request->data['away_id']],
                      ["home_id" => "integer", "away_id" => "integer"]);
                    
                    if($playersInMatchStmtMedziksicht->errorCode() != 0){
                        return false;
                    }
                    $playersInMatch = $playersInMatchStmtMedziksicht->fetchAll('assoc');

                    if(!$this->insertAllPlayersInMatch($playersInMatch, $insertedMatchId, $conn)){
                        return false;
                    }
                    
                    return true;
                });

                $this->request->data = [];
                
                if($insertOK){
                    $this->set('actionMsg', 'Zápas úspešne pridaný.');
                }
                else {
                    $this->set('actionMsg', 'Chyba pri pridávaní zápasu. Skús to znova.');
                }
            }
        }
        
        $this->render("match_info_form");
    }
    
    public function hunDelete($match_id, $season_id){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        if(!$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $conn->execute("
            DELETE FROM matches
            WHERE id = :match_id
        ", ["match_id" => $match_id], ["match_id" => 'integer']);
        
        $this->redirect(["controller" => "Matches", "action" => "view_season", $season_id]);
    }
    
    private function getMatchEventValidator(array $possibleEventsIds, array $playersInMatchIds){
        $validator = new Validator();
        
        $validator
            ->requirePresence('event_id')
            ->requirePresence('player_id')
            ->requirePresence('minute', true)
            ->notEmpty('minute', 'Musíš zadať minútu')
            ->add('event_id', 'event_id', [
                'rule' => ['inList', $possibleEventsIds],
                'message' => 'Vybraná udalosť neexistuje'
            ])
            ->add('player_id', 'player_in_match', [
                'rule' => ['inList', $playersInMatchIds],
                'message' => 'Vybraný hráč nieje na súpiske v tomto zápase'
            ])
            ->add('minute', 'minute', [
                'rule' => ['naturalNumber', true],
                'message' => 'Minúta musí byť prirodzené číslo (a bez bodky na konci)'
            ])
            ;
        
        return $validator;
    }
    
    public function hunAddEventToMatch($match_id){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        if(!$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        $this->request->session()->write('addEventToMatch.validationErrors',[]);
        
        if($this->request->is('post')){
            
            $possibleEventsIds = $this->getIdsArray(
                $conn->execute("
                    SELECT id
                    FROM match_events
                ")->fetchAll('assoc')
            );
            
            $playersInMatchIds = $this->getIdsArray(
                $conn->execute('
                    SELECT mp.player_id id
                    FROM matches_players mp
                    WHERE match_id = :match_id
                ',["match_id" => $match_id])->fetchAll('assoc')
            );
            
            $matchEventValidator = $this->getMatchEventValidator($possibleEventsIds, $playersInMatchIds);
            
            $validationErrors = $matchEventValidator->errors($this->request->data);
            $this->request->session()->write('addEventToMatch.validationErrors', $validationErrors);
            
            if(empty($validationErrors)){
                
                $insertError = $conn->insert('match_events_matches', [
                    'match_id' => $match_id,
                    'event_id' => $this->request->data['event_id'],
                    'player_id' => $this->request->data['player_id'],
                    'minute' => $this->request->data['minute'],
                ], [
                    'match_id' => 'integer',
                    'event_id' => 'integer',
                    'player_id' => 'integer',
                    'minute' => 'integer',
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError != 0){
                    $this->request->session()->write('addEventToMatch.insertedMsg', 'Chyba pri pridávaní zápasu. Skús to znova.');
                }
            }
            
            $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
        }
    }
    
    private function getMatchPhaseValidator(array $possiblePhaseIds){
        $validator = new Validator();
        
        $validator
            ->requirePresence('match_phase_id')
            ->add('match_phase_id', 'match_phase_id', [
                'rule' => ['inList', $possiblePhaseIds],
                'message' => 'Vybraná fáza zápasu neexistuje'
            ]);
        
        return $validator;
    }
    
    public function hunChangeMatchPhase($match_id){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        if(!$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        $this->request->session()->write('changeMatchPhase.validationErrors',[]);
        
        if($this->request->is('post')){
            
            $possibleMatchPhaseIds = $this->getIdsArray(
                $conn->execute("
                    SELECT id
                    FROM match_phases
                ")->fetchAll('assoc'),
                ['0']
            );
            
            $changeMatchPhaseValidator = $this->getMatchPhaseValidator($possibleMatchPhaseIds);
            
            $validationErrors = $changeMatchPhaseValidator->errors($this->request->data);
            $this->request->session()->write('changeMatchPhase.validationErrors', $validationErrors);
            
            if(empty($validationErrors)){
                if($this->request->data['match_phase_id'] == 0){
                    $this->request->data['match_phase_id'] = null;
                }
                
                $updateError = $conn->execute("
                    UPDATE matches
                    SET match_phase_id = :match_phase_id, completed = 0
                    WHERE id = :match_id
                ", ["match_phase_id" => $this->request->data['match_phase_id'], "match_id" => $match_id],
                   ["match_phase_id" => "integer", "match_id" => "integer"])->errorCode();
                
                $this->request->data = [];
                
                if($updateError != 0){
                    $this->request->session()->write('changeMatchPhase.insertedMsg', 'Chyba pri zmene fázy zápasu. Skús to znova.');
                }
            }
            
            $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
        }
    }
    
     public function hunCompleteMatch($match_id){
        if(!$this->isAdminLogged()){
            $this->redirect('/');
            return;
        }
        
        if(!$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $updateError = $conn->execute("
            UPDATE matches
            SET match_phase_id = NULL, completed = 1
            WHERE id = :match_id
        ", ["match_id" => $match_id],
           ["match_id" => "integer"])->errorCode();

        if($updateError != 0){
            $this->request->session()->write('changeMatchPhase.insertedMsg', 'Chyba pri zmene fázy zápasu. Skús to znova.');
        }
        
        $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
     }
     
     public function hunDeletePlayerFromMatch($match_id, $player_id){
         if(!$this->isAdminLogged() || !$this->isNaturalNumber($match_id) || !$this->isNaturalNumber($player_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $deleteOK = $conn->transactional(function ($conn) use ($match_id, $player_id) {
            $deleteErrorCode = $conn->execute("
                DELETE FROM matches_players
                WHERE match_id = :match_id AND player_id = :player_id
            ", ["match_id" => $match_id, "player_id" => $player_id],
               ["match_id" => "integer", "player_id" => "integer"])->errorCode();
            
            if($deleteErrorCode != 0){
                return false;
            }
            
            $deleteErrorCode = $conn->execute("
                DELETE FROM match_events_matches
                WHERE match_id = :match_id AND player_id = :player_id
            ", ["match_id" => $match_id, "player_id" => $player_id],
               ["match_id" => "integer", "player_id" => "integer"])->errorCode();
            
            if($deleteErrorCode != 0){
                return false;
            }

            return true;
        });
        
        if(!$deleteOK){
            $this->request->session()->write('deletePlayerFromMatch.deleteError','Hráča sa nepodarilo zmazať. Skús to znovu.');
        }
        
        $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
     }
     
     public function hunDeleteEventInMatch($match_id, $event_in_match_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($event_in_match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $deleteErrorCode = $conn->execute("
            DELETE FROM match_events_matches
            WHERE id = :event_in_match_id
        ", ["event_in_match_id" => $event_in_match_id],
           ["event_in_match_id" => "integer"])->errorCode();
        
        if($deleteErrorCode != 0){
            $this->request->session()->write('deleteEventInMatch.deleteError','Udalosť sa nepodarilo zmazať. Skús to znovu.');
        }
        
        $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
     }
     
    private function getRefereeValidator(){
        $validator = new Validator();
        
        $validator
            ->requirePresence('name')
            ->requirePresence('surname')
            ->notEmpty('name', 'Musíš zadať meno')
            ->notEmpty('surname', 'Musíš zadať priezvisko')
            ->add('name', 'name', [
                'rule' => ['alphaNumeric'],
                'message' => 'Meno môže obsahovať iba písmená'
            ])
            ->add('surname', 'surname', [
                'rule' => ['alphaNumeric'],
                'message' => 'Priezvisko môže obsahovať iba písmená'
            ])
            ;
        
        return $validator;
    }
     
    public function hunAddRefereeToMatch($match_id){
         if(!$this->isAdminLogged() || !$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        $this->request->session()->write('addRefereeToMatch.validationErrors',[]);
        
        if($this->request->is('post')){
            
            $refereeValidator = $this->getRefereeValidator();
            
            $validationErrors = $refereeValidator->errors($this->request->data);
            $this->request->session()->write('addRefereeToMatch.validationErrors', $validationErrors);
            
            if(empty($validationErrors)){
                
                $insertError = $conn->insert('referees_matches', [
                    'match_id' => $match_id,
                    'name' => $this->request->data['name'],
                    'surname' => $this->request->data['surname'],
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError != 0){
                    $this->request->session()->write('addRefereeToMatch.insertedMsg', 'Chyba pri pridávaní rozhodcu. Skús to znova.');
                }
            }
            
            $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
        }
    }
     
    public function hunDeleteRefereeFromMatch($match_id, $referee_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($match_id) || !$this->isNaturalNumber($referee_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $deleteErrorCode = $conn->execute("
            DELETE FROM referees_matches
            WHERE id = :referee_id
        ", ["referee_id" => $referee_id],
           ["referee_id" => "integer"])->errorCode();
        
        if($deleteErrorCode != 0){
            $this->request->session()->write('deleteReferee.deleteError','Rozhodcu sa nepodarilo zmazať. Skús to znovu.');
        }
        
        $this->redirect(["controller" => "Matches", "action" => "view", $match_id]);
    }
     
     public function hunEdit($match_id){
        if(!$this->isAdminLogged() || !$this->isNaturalNumber($match_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $matchOriginalInfo = $conn->execute('
            SELECT *
            FROM matches
            WHERE id = :match_id
        ',["match_id" => $match_id], ["match_id" => "integer"])->fetch('assoc');
        
        $season_id = $matchOriginalInfo["season_id"];
        
        $clubsInSeason = $conn->execute('
            SELECT clubs.*
            FROM clubs
            JOIN clubs_seasons ON clubs.id = clubs_seasons.club_id
            WHERE clubs_seasons.season_id = :season_id
        ', 
        ["season_id" => $season_id], 
        ["season_id" => "integer"])->fetchAll('assoc');
        $this->set('clubsInSeason', $clubsInSeason);
        
        $matchPhases = $conn->execute('
            SELECT *
            FROM match_phases
        ')->fetchAll('assoc');
        $this->set('matchPhases', $matchPhases);
        
        $seasonPhases = $conn->execute('
            SELECT *
            FROM season_phases
        ')->fetchAll('assoc');
        $this->set('seasonPhases', $seasonPhases);
        
        $this->set('validationErrors',[]);
        if(!$this->request->is('post')){
            $matchOriginalInfo['date_time'] = (new \DateTime($matchOriginalInfo['date_time']))->format("d.m.Y H:i");
            $this->request->data = $matchOriginalInfo;
        }
        else {
            $matchValidator = $this->getMatchValidator(
                    $this->getIdsArray($clubsInSeason), 
                    $this->getIdsArray($matchPhases, ['0']),
                    $this->getIdsArray($seasonPhases));
            
            $validationErrors = $matchValidator->errors($this->request->data);
            $this->set('validationErrors',$validationErrors);
            
            if(empty($validationErrors)){
                $this->request->data['date_time'] = new \DateTime($this->request->data['date_time']);
                if($this->request->data['match_phase_id'] == 0){
                    $this->request->data['match_phase_id'] = null;
                }
                
                $updateMatchStmtMedziksicht = $conn->update('matches', [
                    'home_id' => $this->request->data['home_id'],
                    'away_id' => $this->request->data['away_id'],
                    'round' => $this->request->data['round'],
                    'date_time' => $this->request->data['date_time'],
                    'playtime' => $this->request->data['playtime'],
                    'match_phase_id' => $this->request->data['match_phase_id'],
                    'completed' => isset($this->request->data['completed']),
                    'season_phase_id' => $this->request->data['season_phase_id']
                ], 
                [
                    'id' => $match_id
                ],        
                [
                    'home_id' => 'integer',
                    'away_id' => 'integer',
                    'date_time' => 'datetime',
                    'match_phase_id' => 'integer',
                    'completed' => 'boolean',
                    'season_phase_id' => 'integer'
                ]);
                
                if($updateMatchStmtMedziksicht->errorCode() != 0){
                    $this->set('actionMsg', 'Chyba pri upravovaní zápasu. Skús to znova.');
                }
                else {
                    $this->set('actionMsg', 'Zápas úspešne upravený.');
                    $this->request->data['date_time'] = $this->request->data['date_time']->format("d.m.Y H:i");
                }
            }
        }
        
        $this->render("match_info_form");
     }
    
}

?>