<?php
// src/Controller/ArticlesController.php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validator;

class MatchesController extends AppController {
    
    public function viewSeason($season_id){
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
            SELECT name, surname
            FROM referees_matches
            WHERE match_id = :match_id
        ',["match_id" => $match_id]);
        $matchRefrees = $matchRefrees->fetchAll('assoc');
        
        $matchPlayers = $conn->execute('
            SELECT mp.player_id, mp.club_id, p.name, p.surname
            FROM matches_players mp
            JOIN players p ON mp.player_id = p.id
            WHERE match_id = :match_id
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
                'rule' => ['custom', '/^[0-9\p{L}\. ]+$/'],
                'message' => 'Údaj o kole musí byť neprázdny a môže obsahovať iba číslice, písmená, medzere a bodky'
            ])
            ->add('date_time', 'date_time', [
                'rule' => [$this, 'validateSlovakDateTime'],
                'message' => 'Nesprávny čas a dátum. Musí byť dodržaný formát &quot;DD.MM.RRRR HH:MM&quot; (napr. 07.05.2015)'
            ])
            ->add('playtime', 'alphaNumericDot', [
                'rule' => ['custom', '/^[0-9\p{L}\. ]+$/'],
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
    
    private function getIdsArray(array $rowsFromDB, array $explicitIds = []){
        $ids = $explicitIds;
        
        foreach ($rowsFromDB as $row){
            $ids[] = $row['id'];
        }
        
        return $ids;
    }
    
    public function hunAddToSeason($season_id){
        $this->checkAdminRights();
        
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
                
                $insertError = $conn->insert('matches', [
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
                ])->errorCode();
                
                $this->request->data = [];
                
                if($insertError == 0){
                    $this->set('insertedMsg', 'Zápas úspešne pridaný.');
                }
                else {
                    $this->set('insertedMsg', 'Chyba pri pridávaní zápasu. Skús to znova.');
                }
            }
        }
    }
    
}

?>