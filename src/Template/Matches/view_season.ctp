<?php
    if($this->request->session()->read('admin.login')){
?>
    <div class="row">
        <a href="<?= $this->Url->build(['controller' => 'Matches', 'action' => 'hun_add_to_season', $season_id]) ?>" class="btn btn-success" role="button">
            Pridaj zápas do tejto sezóny
        </a>
    </div>
    <br />
<?php
    }
?>

<?php
    foreach ($matchesInActualSeason as $match){
?>
    <div class="row match_short_info">
        <div class="col-md-2 col-xs-6 match_date_time">
            <?= $match["date_time"]->format("j.n.Y - H:i") ?>
        </div>
        <div class="col-md-1 col-xs-6 match_round">
            <?= $match["round"] ?>
        </div>
        <div class="col-md-9 col-xs-12 match_short_info_main">
            <?= $match["home_name"] ?> 
            <?php
                if($match["completed"] || $match["match_phase_id"]){
                    echo $match['score']["home_score"].":".$match['score']["away_score"];
                    
                    if($match['score']['home_shootout_score'] || $match['score']['away_shootout_score']){
                        echo ' ('.$match['score']['home_shootout_score'].':'.
                        $match['score']['away_shootout_score'].' pen.)';
                    }
                }
                else{
                    echo "vs. ";
                }
            ?>
             <?= $match["away_name"] ?>
            <?php
                if($match["completed"]){
                    echo $this->Html->link("Pozri zápis", 
                        ["controller" => "Matches", "action" => "view", $match["id"]]);
                }
                else if($match["match_phase_id"]){
                    echo $this->Html->link("Zobraz live prenos", 
                        ["controller" => "Matches", "action" => "view", $match["id"]]);
                }
            ?>
        </div>
    </div>
<?php
    }
?>
