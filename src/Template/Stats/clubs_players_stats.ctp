<!-- Stats -->

<h2><?= $club['name'] ?> - štatistiky hráčov klubu</h2>
<div class="row">
    <div class="col-md-6" >
        <div class="row header" style="text-transform: uppercase; font-weight: bold;">
            <div class="col-md-7" >
                Hráč
            </div>
            <div class="col-md-1" >
                Z
            </div>
            <div class="col-md-1">
                G
            </div>
            <div class="col-md-1">
                GV
            </div>
            <div class="col-md-1" style="color: yellow;">
                ŽK
            </div>
            <div class="col-md-1" style="color: red;">
                ČK
            </div>
        </div>
        
        <?php
            $i = 0;
            foreach($clubsPlayersStats as $player_id => $playersStats){
                ?>
                <div class="row <?= ($i%2==1) ? "table_alternative_row" : "" ?>">
                    <div class="col-md-7" >
                        <?= $playersStats['name'].' '.$playersStats['surname'] ?>
                    </div>
                    <div class="col-md-1" >
                        <?= $playersStats['matches'] ?>
                    </div>
                    <div class="col-md-1">
                        <?= $playersStats['goals'] ?>
                    </div>
                    <div class="col-md-1">
                        <?= $playersStats['ownGoals'] ?>
                    </div>
                    <div class="col-md-1" style="color: yellow;">
                        <?= $playersStats['yellowCards'] ?>
                    </div>
                    <div class="col-md-1" style="color: red;">
                        <?= $playersStats['redCards'] ?>
                    </div>
                </div>    
                <?php
                $i++;
            }
        ?>
    </div>
</div>
<br />
<div class="note">* Z=Zápasy, G=Góly, GV=Vlastné góly, ŽK=Žlté karty, ČK=Červené karty.</div>