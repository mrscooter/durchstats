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
    $i=0;
    foreach($players as $player){
        if($player['club_id'] == $clubId){
            ?>
        <div class="row <?= ($i%2==1) ? "table_alternative_row" : "" ?>">
            <div class="col-md-8" >
                <?php
                    if($this->Nicnajder->isAdminLogged()){
                        echo $this->Html->link($this->Html->image(
                            "delete.icon.png", ["class" => "delete_icon_small"]),
                            "http://www.pino.webekacko.com",
                            ['escapeTitle' => false, 'onclick' => 'deletePlayerFromMatch('.$player['player_id'].'); return false;']
                        );
                    }
                ?>
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
            $i++;
        }
    }
?>