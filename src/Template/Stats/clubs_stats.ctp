<!-- Stats -->

<h2><?= $club['name'] ?> - štatistiky klubu</h2>
<div class="row">
    <div class="col-md-12" >
        <div class="row header" style="text-transform: uppercase; font-weight: bold;">
            <div class="col-md-2" >
                Sezóna
            </div>
            <div class="col-md-1" >
                Z
            </div>
            <div class="col-md-1">
                G
            </div>
            <div class="col-md-1">
                OG
            </div>
            <div class="col-md-1">
                GV
            </div>
            <div class="col-md-1" style="color: yellow;">
                ŽK
            </div>
            <div class="col-md-1" style="color: red;">
                ČKp
            </div>
            <div class="col-md-1" style="color: red;">
                ČK2ž
            </div>
        </div>
        
        <?php
            $i = 0;
            foreach($clubsStats as $season_id => $statsForSeason){
                if(strcmp($season_id,'allTime') == 0) continue;
                ?>
                <div class="row <?= ($i%2==1) ? "table_alternative_row" : "" ?>">
                    <div class="col-md-2" >
                        <?= $statsForSeason['year'].'/'.($statsForSeason['year']+1) ?>
                    </div>
                    <div class="col-md-1" >
                        <?= $statsForSeason['matches'] ?>
                    </div>
                    <div class="col-md-1">
                        <?= $statsForSeason['goals'].' ('.$this->Nicnajder->formatAvgStat($statsForSeason['goals'], $statsForSeason['matches']).')' ?>
                    </div>
                    <div class="col-md-1">
                        <?= $statsForSeason['goalsAgainst'].' ('.$this->Nicnajder->formatAvgStat($statsForSeason['goalsAgainst'], $statsForSeason['matches']).')' ?>
                    </div>
                    <div class="col-md-1">
                        <?= $statsForSeason['ownGoals'] ?>
                    </div>
                    <div class="col-md-1" style="color: yellow;">
                        <?= $statsForSeason['yellowCards'].' ('.$this->Nicnajder->formatAvgStat($statsForSeason['yellowCards'], $statsForSeason['matches']).')' ?>
                    </div>
                    <div class="col-md-1" style="color: red;">
                        <?= $statsForSeason['redCards'].' ('.$this->Nicnajder->formatAvgStat($statsForSeason['redCards'], $statsForSeason['matches']).')' ?>
                    </div>
                    <div class="col-md-1" style="color: red;">
                        <?= $statsForSeason['redCards2'].' ('.$this->Nicnajder->formatAvgStat($statsForSeason['redCards2'], $statsForSeason['matches']).')' ?>
                    </div>
                </div>    
                <?php
                $i++;
            }
        ?>
        
        <div class="row header">
            <div class="col-md-2" >
                SPOLU
            </div>
            <div class="col-md-1" >
                <?= $clubsStats['allTime']['matches'] ?>
            </div>
            <div class="col-md-1">
                <?= $clubsStats['allTime']['goals'].' ('.$this->Nicnajder->formatAvgStat($clubsStats['allTime']['goals'], $statsForSeason['matches']).')' ?>
            </div>
            <div class="col-md-1">
                <?= $clubsStats['allTime']['goalsAgainst'].' ('.$this->Nicnajder->formatAvgStat($clubsStats['allTime']['goalsAgainst'], $statsForSeason['matches']).')' ?>
            </div>
            <div class="col-md-1">
                <?= $clubsStats['allTime']['ownGoals'] ?>
            </div>
            <div class="col-md-1" style="color: yellow;">
                <?= $clubsStats['allTime']['yellowCards'].' ('.$this->Nicnajder->formatAvgStat($clubsStats['allTime']['yellowCards'], $statsForSeason['matches']).')' ?>
            </div>
            <div class="col-md-1" style="color: red;">
                <?= $clubsStats['allTime']['redCards'].' ('.$this->Nicnajder->formatAvgStat($clubsStats['allTime']['redCards'], $statsForSeason['matches']).')' ?>
            </div>
            <div class="col-md-1" style="color: red;">
                <?= $clubsStats['allTime']['redCards2'].' ('.$this->Nicnajder->formatAvgStat($clubsStats['allTime']['redCards2'], $statsForSeason['matches']).')' ?>
            </div>
        </div>
    </div>
</div>

<div class="note">* Z=Zápasy, G=Góly, OG=Obdržané góly, GV=Vlastné góly, ŽK=Žlté karty, ČKp=Červené karty udelené priamo, ČK2ž=Červené karty udelené po dvoch žltých.</div>
<div class="note">* Číslo v zátvorke udáva priemer na jeden zápas.</div>