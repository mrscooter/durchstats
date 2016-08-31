<!-- Stats -->

<h2>Štatistiky klubov</h2>
<div class="row">
    <div class="col-md-12">
        <?php
            foreach($allClubs as $club){
                echo '<div>';
                echo $club['name']." - ";
                echo $this->Html->link(
                        "štatistiky klubu",
                        ['controller' => 'Stats', 'action' => 'clubsStats', $club['id'] ],
                        ['class' => 'listed_clickable_item', "style" => "color:#996600;"]
                    );
                echo " ";
                echo $this->Html->link(
                        "štatistiky hráčov klubu",
                        ['controller' => 'Stats', 'action' => 'clubsPlayersStats', $club['id'] ],
                        ['class' => 'listed_clickable_item', "style" => "color:red;"]
                    );
                echo '</div>';
            }
        ?>
    </div>
</div>