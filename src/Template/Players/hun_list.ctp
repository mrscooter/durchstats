<!-- Players -->

<div class="row">
    <div class="col-md-12">
        <?php
            foreach($allPlayers as $player){
                $player['club_name'] = $player['club_name'] ? $player['club_name'] : "Aktuálne bez klubu";
                
                echo '<div>';
                echo $this->Html->link(
                        $player['name'].' '.$player['surname'].' ('.$player['club_name'].')',
                        ['controller' => 'Players', 'action' => 'hunEdit', $player['id'] ],
                        ['class' => 'listed_clickable_item']
                    );
                echo '</div>';
            }
        ?>
    </div>
</div>