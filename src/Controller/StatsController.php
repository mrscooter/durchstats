<?php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;

class StatsController extends AppController {
    
    public function listPlayers(){
        $conn = ConnectionManager::get('default');
        
        $allPlayers = $conn->execute('
            SELECT *
            FROM players
        ')->fetchAll('assoc');
        
        $this->set('allPlayers', $allPlayers);
    }
    
    private function playerStatsForView(array $matchCount, array $playersStats, array $yellowCardsStats){
        $statsForView = [
            'allTime' => ['matches' => 0, 'goals' => 0, 'ownGoals' => 0, 'yellowCards' => 0, 'redCards' => 0, 'redCards2' => 0]
        ];
        
        foreach($matchCount as $matchesInSeason){
            $statsForView[$matchesInSeason['season_id']] = ['matches' => 0, 'goals' => 0, 'ownGoals' => 0, 'yellowCards' => 0, 'redCards' => 0, 'redCards2' => 0];
            $statsForView[$matchesInSeason['season_id']]['year'] = $matchesInSeason['year'];
            $statsForView[$matchesInSeason['season_id']]['matches'] = $matchesInSeason['count'];
            $statsForView['allTime']['matches'] += $matchesInSeason['count'];
        }
        
        foreach($playersStats as $stat){
            if($stat['event_id'] == GOAL_EVENT_ID){
                $statsForView[$stat['season_id']]['goals'] = $stat['count'];
                $statsForView['allTime']['goals'] += $stat['count'];
            }
            else if($stat['event_id'] == OWN_GOAL_EVENT_ID){
                $statsForView[$stat['season_id']]['ownGoals'] = $stat['count'];
                $statsForView['allTime']['ownGoals'] += $stat['count'];
            }
            else if($stat['event_id'] == YELLOW_CARD_EVENT_ID){
                $statsForView[$stat['season_id']]['yellowCards'] = $stat['count'];
                $statsForView['allTime']['yellowCards'] += $stat['count'];
            }
            else if($stat['event_id'] == RED_CARD_EVENT_ID){
                $statsForView[$stat['season_id']]['redCards'] = $stat['count'];
                $statsForView['allTime']['redCards'] += $stat['count'];
            }
        }
        
        foreach($yellowCardsStats as $ystat){
            if($ystat['count'] == 2){
                $statsForView[$ystat['season_id']]['redCards2']++;
                $statsForView['allTime']['redCards2']++;
                $statsForView[$ystat['season_id']]['yellowCards'] -= 2;
                $statsForView['allTime']['yellowCards'] -= 2;
            }
        }
        
        return $statsForView;
    }
    
    public function playersStats($player_id){
        if(!$this->isNaturalNumber($player_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $player = $conn->execute('
            SELECT *
            FROM players
            WHERE id = :player_id
        ',['player_id' => $player_id],['player_id' => 'integer'])->fetchAll('assoc');
        $this->set('player',$player[0]);
        
        $matchCount = $conn->execute('
            SELECT m.season_id, s.year, COUNT(*) count
            FROM matches_players mp
            JOIN matches m ON mp.match_id = m.id
            JOIN seasons s on s.id = m.season_id
            WHERE mp.player_id = :player_id AND (m.completed OR m.match_phase_id IS NOT NULL)
            GROUP BY m.season_id
            ORDER BY s.year DESC
        ',['player_id' => $player_id],['player_id' => 'integer'])->fetchAll('assoc');
        
        $playersStats = $conn->execute('
            SELECT mem.event_id, m.season_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            WHERE mem.player_id = :player_id
            GROUP BY m.season_id, mem.event_id
        ',['player_id' => $player_id],['player_id' => 'integer'])->fetchAll('assoc');
        
        $yellowCardsStats = $conn->execute('
            SELECT m.season_id, mem.match_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            WHERE mem.player_id = :player_id AND mem.event_id = :yellow_card_event_id
            GROUP BY mem.match_id, m.season_id
            ORDER BY season_id
        ',['player_id' => $player_id, 'yellow_card_event_id' => YELLOW_CARD_EVENT_ID],
          ['player_id' => 'integer', 'yellow_card_event_id' => 'integer'])->fetchAll('assoc');
                
        $statsForView = $this->playerStatsForView($matchCount, $playersStats, $yellowCardsStats);
        $this->set('playersStats',$statsForView);
    }
    
    public function listClubs(){
        $conn = ConnectionManager::get('default');
        
        $allClubs = $conn->execute('
            SELECT *
            FROM clubs
        ')->fetchAll('assoc');
        
        $this->set('allClubs', $allClubs);
    }
    
    public function clubsStats($club_id){
        if(!$this->isNaturalNumber($club_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $club = $conn->execute('
            SELECT *
            FROM clubs
            WHERE id = :club_id
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetch('assoc');
        $this->set('club',$club);
        
        $matchCount = $conn->execute('
            SELECT m.season_id, s.year, COUNT(*) count
            FROM matches m
            JOIN seasons s on s.id = m.season_id
            WHERE (m.home_id = :club_id OR m.away_id = :club_id) AND (m.completed OR m.match_phase_id IS NOT NULL)
            GROUP BY m.season_id
            ORDER BY s.year DESC
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetchAll('assoc');
        
        $clubsStats = $conn->execute('
            SELECT mem.event_id, m.season_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            JOIN matches_players mp on (mp.match_id = m.id AND mp.player_id = mem.player_id)
            WHERE mp.club_id = :club_id 
            GROUP BY m.season_id, mem.event_id
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetchAll('assoc');
        
        $yellowCardsStats = $conn->execute('
            SELECT mem.player_id, m.season_id, mem.match_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            JOIN matches_players mp on (mp.match_id = m.id AND mp.player_id = mem.player_id)
            WHERE mp.club_id = :club_id AND mem.event_id = :yellow_card_event_id
            GROUP BY mem.player_id, mem.match_id, m.season_id
            ORDER BY season_id, m.id
        ',['club_id' => $club_id, 'yellow_card_event_id' => YELLOW_CARD_EVENT_ID],
          ['club_id' => 'integer', 'yellow_card_event_id' => 'integer'])->fetchAll('assoc');
        
        $ownGoalsAgainst = $conn->execute('
            SELECT m.season_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            JOIN matches_players mp on (mp.match_id = m.id AND mp.player_id = mem.player_id)
            WHERE (m.home_id = :club_id OR m.away_id = :club_id) AND mp.club_id != :club_id AND mem.event_id = :own_goal_event_id
            GROUP BY m.season_id
            ORDER BY season_id
        ',['club_id' => $club_id, 'own_goal_event_id' => OWN_GOAL_EVENT_ID],
          ['club_id' => 'integer', 'yellow_card_event_id' => 'integer'])->fetchAll('assoc');
        
        $goalsAgainst = $conn->execute('
            SELECT m.season_id, COUNT(*) count
            FROM match_events_matches mem
            JOIN matches m on m.id = mem.match_id
            JOIN matches_players mp on (mp.match_id = m.id AND mp.player_id = mem.player_id)
            WHERE (m.home_id = :club_id OR m.away_id = :club_id) AND mp.club_id != :club_id AND mem.event_id = :own_goal_event_id
            GROUP BY m.season_id
            ORDER BY season_id
        ',['club_id' => $club_id, 'own_goal_event_id' => GOAL_EVENT_ID],
          ['club_id' => 'integer', 'yellow_card_event_id' => 'integer'])->fetchAll('assoc');
                     
        $statsForView = $this->clubStatsForView($matchCount, $clubsStats, $yellowCardsStats, $ownGoalsAgainst, $goalsAgainst);
        $this->set('clubsStats',$statsForView);
    }
    
    private function clubStatsForView(array $matchCount, array $clubsStats, 
                                        array $yellowCardsStats, array $ownGoalsAgainst, array $goalsAgainst){
        $statsForView = [
            'allTime' => [
                'matches' => 0,
                'goals' => 0,
                'ownGoals' => 0,
                'goalsAgainst' => 0,
                'yellowCards' => 0,
                'redCards' => 0,
                'redCards2' => 0
            ]
        ];
        
        foreach($matchCount as $matchesInSeason){
            $statsForView[$matchesInSeason['season_id']] =
                [
                    'matches' => 0,
                    'goals' => 0,
                    'ownGoals' => 0,
                    'goalsAgainst' => 0,
                    'yellowCards' => 0,
                    'redCards' => 0,
                    'redCards2' => 0
                ];
            $statsForView[$matchesInSeason['season_id']]['year'] = $matchesInSeason['year'];
            $statsForView[$matchesInSeason['season_id']]['matches'] = $matchesInSeason['count'];
            $statsForView['allTime']['matches'] += $matchesInSeason['count'];
        }
        
        foreach($clubsStats as $stat){
            if($stat['event_id'] == GOAL_EVENT_ID){
                $statsForView[$stat['season_id']]['goals'] = $stat['count'];
                $statsForView['allTime']['goals'] += $stat['count'];
            }
            else if($stat['event_id'] == OWN_GOAL_EVENT_ID){
                $statsForView[$stat['season_id']]['ownGoals'] = $stat['count'];
                $statsForView[$stat['season_id']]['goalsAgainst'] += $stat['count'];
                $statsForView['allTime']['ownGoals'] += $stat['count'];
                $statsForView['allTime']['goalsAgainst'] += $stat['count'];
            }
            else if($stat['event_id'] == YELLOW_CARD_EVENT_ID){
                $statsForView[$stat['season_id']]['yellowCards'] = $stat['count'];
                $statsForView['allTime']['yellowCards'] += $stat['count'];
            }
            else if($stat['event_id'] == RED_CARD_EVENT_ID){
                $statsForView[$stat['season_id']]['redCards'] = $stat['count'];
                $statsForView['allTime']['redCards'] += $stat['count'];
            }
        }
        
        foreach($yellowCardsStats as $ystat){
            if($ystat['count'] == 2){
                $statsForView[$ystat['season_id']]['redCards2']++;
                $statsForView['allTime']['redCards2']++;
                $statsForView[$ystat['season_id']]['yellowCards'] -= 2;
                $statsForView['allTime']['yellowCards'] -= 2;
            }
        }
        
        foreach($ownGoalsAgainst as $ogStat){
            $statsForView[$ogStat['season_id']]['goals'] += $ogStat['count'];
            $statsForView['allTime']['goals'] += $ogStat['count'];
        }
        
        foreach($goalsAgainst as $gaStat){
            $statsForView[$gaStat['season_id']]['goalsAgainst'] += $gaStat['count'];
            $statsForView['allTime']['goalsAgainst'] += $gaStat['count'];
        }
        
        return $statsForView;
    }
    
    public function clubsPlayersStats($club_id){
        if(!$this->isNaturalNumber($club_id)){
            $this->redirect('/');
            return;
        }
        
        $conn = ConnectionManager::get('default');
        
        $club = $conn->execute('
            SELECT *
            FROM clubs
            WHERE id = :club_id
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetch('assoc');
        $this->set('club',$club);
        
        $playersMatchesForClubCount = $conn->execute('
            SELECT p.id as player_id, p.name, p.surname, COUNT(*) as count
            FROM matches_players mp
            JOIN players p ON mp.player_id = p.id
            JOIN matches m ON mp.match_id = m.id
            WHERE mp.club_id = :club_id AND (m.completed OR m.match_phase_id IS NOT NULL)
            GROUP BY p.id
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetchAll('assoc');
        
        $playersForClubStats = $conn->execute('
            SELECT mem.player_id, mem.event_id, COUNT(*) as count
            FROM match_events_matches mem
            JOIN matches_players mp ON (mp.match_id = mem.match_id AND mem.player_id = mp.player_id)
            WHERE mp.club_id = :club_id
            GROUP BY mem.player_id, mem.event_id
        ',['club_id' => $club_id],['club_id' => 'integer'])->fetchAll('assoc');
        
        $playersForClubYellowCardStats = $conn->execute('
            SELECT mem.player_id, COUNT(*) as count
            FROM match_events_matches mem
            JOIN matches_players mp ON (mp.match_id = mem.match_id AND mem.player_id = mp.player_id)
            WHERE mp.club_id = :club_id AND mem.event_id = :yellow_card_event_id
            GROUP BY mem.player_id, mem.match_id
        ',['club_id' => $club_id, "yellow_card_event_id" => YELLOW_CARD_EVENT_ID],
          ['club_id' => 'integer', "yellow_card_event_id" => "integer"])->fetchAll('assoc');
        
        $statsForView = $this->clubsPlayerStatsForView($playersMatchesForClubCount, $playersForClubStats, $playersForClubYellowCardStats);
        $this->set('clubsPlayersStats',$statsForView);
    }
    
    private function clubsPlayerStatsForView(array $playersMatchesForClubCount, array $playersForClubStats, array $playersForClubYellowCardStats){
        $statsForView = [];
        
        foreach($playersMatchesForClubCount as $onePlayerMatchesCount){
            $statsForView[$onePlayerMatchesCount['player_id']] = 
                [
                    'matches' => $onePlayerMatchesCount['count'], 
                    'goals' => 0,
                    'ownGoals' => 0,
                    'yellowCards' => 0,
                    'redCards' => 0,
                    'redCards2' => 0
                ];
            $statsForView[$onePlayerMatchesCount['player_id']]['name'] = $onePlayerMatchesCount['name'];
            $statsForView[$onePlayerMatchesCount['player_id']]['surname'] = $onePlayerMatchesCount['surname'];
        }
        
        foreach($playersForClubStats as $stat){
            if($stat['event_id'] == GOAL_EVENT_ID){
                $statsForView[$stat['player_id']]['goals'] = $stat['count'];
            }
            else if($stat['event_id'] == OWN_GOAL_EVENT_ID){
                $statsForView[$stat['player_id']]['ownGoals'] = $stat['count'];
            }
            else if($stat['event_id'] == YELLOW_CARD_EVENT_ID){
                $statsForView[$stat['player_id']]['yellowCards'] = $stat['count'];
            }
            else if($stat['event_id'] == RED_CARD_EVENT_ID){
                $statsForView[$stat['player_id']]['redCards'] = $stat['count'];
            }
        }
        
        foreach($playersForClubYellowCardStats as $ystat){
            if($ystat['count'] == 2){
                $statsForView[$ystat['player_id']]['redCards2']++;
                $statsForView[$ystat['player_id']]['yellowCards'] -= 2;
            }
        }
        
        return $statsForView;
    }
    
}

?>