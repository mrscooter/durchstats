<div class="row header" style="text-transform: uppercase; font-weight: bold;">
    <div class="col-md-8" >
        <?= $clubName ?>
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
    foreach($players as $player){
        if($player['club_id'] == $clubId){
            ?>
        <div class="row">
            <div class="col-md-8" >
                <span class="surname"><?= $player["surname"] ?></span> <?= $player['name'] ?>
            </div>
            <div class="col-md-1">
                <?php
                    if(isset($player['goals'])){
                        echo $player['goals'];
                    }
                ?>
            </div>
            <div class="col-md-1">
                <?php
                    if(isset($player['own_goals'])){
                        echo $player['own_goals'];
                    }
                ?>
            </div>
            <div class="col-md-1">
                <?php
                    if(isset($player['yellow_cards'])){
                        echo $player['yellow_cards'];
                    }
                ?>
            </div>
            <div class="col-md-1">
                <?php
                    if(isset($player['red_cards'])){
                        echo $player['red_cards'];
                    }
                ?>
            </div>
        </div>
            <?php
        }
    }
?>