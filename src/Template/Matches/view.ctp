<div class="row header">
    <div class="col-md-3">
        hrací deň
    </div>
    <div class="col-md-3">
        dátum a čas
    </div>
    <div class="col-md-3">
        rozhodcovia
    </div>
    <div class="col-md-3">
        hrací čas
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <?= $matchInfo['round'] ?>
    </div>
    <div class="col-md-3">
        <div><?= $matchInfo['date_time']->format("j.n.Y") ?></div>
        <div><?= $matchInfo['date_time']->format("H:i") ?></div>
    </div>
    <div class="col-md-3">
        <?php
            foreach ($referees as $ref){
                echo '<div><span class="surname">'.$ref["surname"]."</span> ".$ref['name'].'</div>';
            }
        ?>
    </div>
    <div class="col-md-3">
        <?= $matchInfo['playtime'] ?>
    </div>
</div>
<br />

<div class="row header">
    <div class="col-md-4" style="text-align: right">
        domáci
    </div>
    <div class="col-md-4" style="text-align: center">
        výsledok
    </div>
    <div class="col-md-4" style="text-align: left">
        hostia
    </div>
</div>
<br />

<div class="row">
    <div class="col-md-4" style="text-align: right">
        <?= $this->Html->image("teams/".$matchInfo["home_id"].'.png', ["class" => "team_logo_match_view", "alt" => "home logo"]) ?>
        <span class="team_name_match_view"><?= $matchInfo['home_name'] ?></span>
    </div>
    <div class="col-md-4" style="text-align: center">
        <div class="score_match_view">
            <?php
                if($matchInfo["completed"] || $matchInfo["match_phase_id"]){
                    echo $matchInfo['score']["home_score"]." : ".$matchInfo['score']["away_score"];
                }
                else{
                    echo " vs. ";
                }
            ?>
        </div>
        <div class="shootout_score_match_view">
        <?php
            if($matchInfo['score']['home_shootout_score'] != 0 || $matchInfo['score']['away_shootout_score'] != 0){
                echo '('.$matchInfo['score']['home_shootout_score'].':'.
                        $matchInfo['score']['away_shootout_score'].' na penalty)';
            }
        ?>
        </div>
        <div class="match_phase_match_view">
            <?php
                if($matchInfo["completed"]){
                    echo "koniec zápasu";
                }
                else if($matchInfo['match_phase_name']){
                    echo $matchInfo['match_phase_name'];
                }
            ?>
        </div>
    </div>
    <div class="col-md-4" style="text-align: left">
        <span class="team_name_match_view"><?= $matchInfo['away_name'] ?></span>
        <?= $this->Html->image("teams/".$matchInfo["away_id"].'.png', ["class" => "team_logo_match_view", "alt" => "away logo"]) ?>
    </div>
</div>
<br />

<div class="row">
    <div class="col-md-7">
        <?= $this->element('view_match_players_stats', 
            ['players' => $players, 'clubName' => $matchInfo['home_name'], 'clubId' => $matchInfo['home_id'] ]) ?>
        <br />
        
        <?= $this->element('view_match_players_stats', 
            ['players' => $players, 'clubName' => $matchInfo['away_name'], 'clubId' => $matchInfo['away_id'] ]) ?>
    </div>
    <div class="col-md-1">
    </div>
    <div class="col-md-4">
        <?= $this->element('view_match_sidebar',
            ['events' => $events, 'homeId' => $matchInfo['home_id'], 'awayId' => $matchInfo['away_id'], 
             'players' => $players ]) ?>
    </div>
</div>

<div id="error"></div>

<?php if(!$matchInfo['completed'] && $matchInfo['match_phase_id']){ ?>
    <script type="text/javascript">
    $(document).ready(function(){
        window.setTimeout(function(){
            $.ajax({
                url: "<?= $this->Url->build(['controller' => 'Matches', 'action' => 'view', $matchInfo['id']]); ?>",
                method : "POST",
                error: function(jqXHR, status, error){
                    $('#error').html(status);
                },
                success: function(result){
                    $("#content_container").html(result);
                }
            });
        }, 10000);
    });
    </script>
<?php } ?>