<!-- Stats -->

<h2><?= $player['name'] ?> <?= $player['surname'] ?> - individuálne štatistiky</h2>
<div class="row">
    <div class="col-md-6" >
        <div class="row header" style="text-transform: uppercase; font-weight: bold;">
            <div class="col-md-2" >
                Sezóna
            </div>
            <div class="col-md-2" >
                Z
            </div>
            <div class="col-md-2">
                G
            </div>
            <div class="col-md-2">
                GV
            </div>
            <div class="col-md-2" style="color: yellow;">
                ŽK
            </div>
            <div class="col-md-2" style="color: red;">
                ČK
            </div>
        </div>
        
        <?php
            $i = 0;
            foreach($playersStats as $season_id => $statsForSeason){
                if(strcmp($season_id,'allTime') == 0) continue;
                ?>
                <div class="row <?= ($i%2==1) ? "table_alternative_row" : "" ?>">
                    <div class="col-md-2" >
                        <?= $statsForSeason['year'].'/'.($statsForSeason['year']+1) ?>
                    </div>
                    <div class="col-md-2" >
                        <?= $statsForSeason['matches'] ?>
                    </div>
                    <div class="col-md-2">
                        <?= $statsForSeason['goals'] ?>
                    </div>
                    <div class="col-md-2">
                        <?= $statsForSeason['ownGoals'] ?>
                    </div>
                    <div class="col-md-2" style="color: yellow;">
                        <?= $statsForSeason['yellowCards'] ?>
                    </div>
                    <div class="col-md-2" style="color: red;">
                        <?= $statsForSeason['redCards'] ?>
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
            <div class="col-md-2" >
                <?= $playersStats['allTime']['matches'] ?>
            </div>
            <div class="col-md-2">
                <?= $playersStats['allTime']['goals'] ?>
            </div>
            <div class="col-md-2">
                <?= $playersStats['allTime']['ownGoals'] ?>
            </div>
            <div class="col-md-2" style="color: yellow;">
                <?= $playersStats['allTime']['yellowCards'] ?>
            </div>
            <div class="col-md-2" style="color: red;">
                <?= $playersStats['allTime']['redCards'] ?>
            </div>
        </div>
    </div>
</div>

<div class="note">* Z=Zápasy, G=Góly, GV=Vlastné góly, ŽK=Žlté karty, ČK=Červené karty.</div>