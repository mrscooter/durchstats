<!-- Stats -->

<h2>Individuálne štatistiky hráčov</h2>
<div class="row">
    <div class="col-md-12">
        <?php
            foreach($allPlayers as $player){
                echo '<div>';
                echo $this->Html->link(
                        $player['name'].' '.$player['surname'],
                        ['controller' => 'Stats', 'action' => 'playersStats', $player['id'] ],
                        ['class' => 'listed_clickable_item']
                    );
                echo '</div>';
            }
        ?>
    </div>
</div>