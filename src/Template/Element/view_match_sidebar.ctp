<div class="row header">
    <div class="col-md-12 sidebar_event_header_match_view">
        GÓLY
    </div>
</div>
<?php
    $home_score = 0;
    $away_score = 0;
    foreach($events['goals'] as $goal){
        if($goal['club_id'] == $homeId){
            if($goal['event_id'] == GOAL_EVENT_ID){
                $home_score++;
            }
            else if($goal['event_id'] == OWN_GOAL_EVENT_ID){
                $away_score++;
            }
        }
        else if($goal['club_id'] == $awayId){
            if($goal['event_id'] == GOAL_EVENT_ID){
                $away_score++;
            }
            else if($goal['event_id'] == OWN_GOAL_EVENT_ID){
                $home_score++;
            }
        }
        ?>
            <div class="row">
                <div class="col-md-5" style="text-align: right;">
                <?php
                    if($goal['club_id'] == $homeId && $goal['event_id'] == GOAL_EVENT_ID){
                        echo '<span class="surname">'.$players[$goal['player_id']]['surname'].'</span>';
                    }
                    if($goal['club_id'] == $awayId && $goal['event_id'] == OWN_GOAL_EVENT_ID){
                        echo '<span class="surname">'.$players[$goal['player_id']]['surname'].'</span> (vl.)';
                    }
                ?>
                    &nbsp; &nbsp; <span style="font-weight: bold;"><?= $home_score ?></span>
                </div>

                <div class="col-md-2" style="text-align: center;">
                    (<?= $goal['minute'] ?>)
                </div>

                <div class="col-md-5" style="text-align: left;">
                    <span style="font-weight: bold;"><?= $away_score ?></span> &nbsp; &nbsp;
                <?php
                    if($goal['club_id'] == $awayId && $goal['event_id'] == GOAL_EVENT_ID){
                        echo '<span class="surname">'.$players[$goal['player_id']]['surname'].'</span>';
                    }
                    if($goal['club_id'] == $homeId && $goal['event_id'] == OWN_GOAL_EVENT_ID){
                        echo '<span class="surname">'.$players[$goal['player_id']]['surname'].'</span> (vl.)';
                    }
                ?>
                </div>
            </div>
        <?php
    }
    
    if($home_score == 0 && $away_score == 0){
        ?>
        <div class="row">
            <div class="col-md-12" style="text-align: center">
                bez gólov
            </div>
        </div>
        <?php
    }
?>
<br />

<?php
    if($events['shootout_goals']){
        $home_shootout_score = 0;
        $away_shootout_score = 0;
    ?>
    <div class="row header">
        <div class="col-md-12 sidebar_event_header_match_view">
            PENALTOVÝ ROZSTREL
        </div>
    </div>
    <?php
        foreach($events['shootout_goals'] as $shootout_goal){
            if($shootout_goal['club_id'] == $homeId){
                $home_shootout_score++;
            }
            else if($shootout_goal['club_id'] == $awayId){
                $away_shootout_score++;
            }
        ?>    
        <div class="row">
            <div class="col-md-5" style="text-align: right;">
            <?php
                if($shootout_goal['club_id'] == $homeId){
                    echo '<span class="surname">'.$players[$shootout_goal['player_id']]['surname'].'</span>';
                }
            ?>
                &nbsp; &nbsp;   
            </div>

            <div class="col-md-2" style="text-align: center;">
                <span style="font-weight: bold;"><?= $home_shootout_score ?></span>
                :
                <span style="font-weight: bold;"><?= $away_shootout_score?></span>
            </div>

            <div class="col-md-5" style="text-align: left;">
                 &nbsp; &nbsp;
            <?php
                if($shootout_goal['club_id'] == $awayId){
                    echo '<span class="surname">'.$players[$shootout_goal['player_id']]['surname'].'</span>';
                }
            ?>
            </div>
        </div>   
        <?php  
        }
    echo "<br />";
    }
?>

<div class="row header">
    <div class="col-md-12 sidebar_event_header_match_view" style="color: yellow;">
        ŽLTÉ KARTY
    </div>
</div>
<?php
    foreach($events['yellow_cards'] as $yellowCard){
    ?>    
    <div class="row">
        <div class="col-md-5" style="text-align: right;">
        <?php
            if($yellowCard['club_id'] == $homeId){
                echo '<span class="surname">'.$players[$yellowCard['player_id']]['surname'].'</span>';
            }
        ?>
        </div>

        <div class="col-md-2" style="text-align: center;">
            (<?= $yellowCard['minute'] ?>)
        </div>

        <div class="col-md-5" style="text-align: left;">
        <?php
            if($yellowCard['club_id'] == $awayId){
                echo '<span class="surname">'.$players[$yellowCard['player_id']]['surname'].'</span>';
            }
        ?>
        </div>
    </div>   
    <?php  
    }
    
    if(!$events['yellow_cards']){
        ?>
        <div class="row">
            <div class="col-md-12" style="text-align: center">
                bez žltých kariet
            </div>
        </div>
        <?php
    }
?>
<br />

<div class="row header">
    <div class="col-md-12 sidebar_event_header_match_view" style="color: red;">
        ČERVENÉ KARTY
    </div>
</div>
<?php
    foreach($events['red_cards'] as $redCard){
    ?>    
    <div class="row">
        <div class="col-md-5" style="text-align: right;">
        <?php
            if($redCard['club_id'] == $homeId){
                echo '<span class="surname">'.$players[$redCard['player_id']]['surname'].'</span>';
            }
        ?>
        </div>

        <div class="col-md-2" style="text-align: center;">
            (<?= $redCard['minute'] ?>)
        </div>

        <div class="col-md-5" style="text-align: left;">
        <?php
            if($redCard['club_id'] == $awayId){
                echo '<span class="surname">'.$players[$redCard['player_id']]['surname'].'</span>';
            }
        ?>
        </div>
    </div>   
    <?php  
    }
    
    if(!$events['red_cards']){
        ?>
        <div class="row">
            <div class="col-md-12" style="text-align: center">
                bez červených kariet
            </div>
        </div>
        <?php
    }
?>