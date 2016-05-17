<!-- Seasons -->

<div class="row">
    <div class="col-md-12">
        <?php
            foreach($allSeasons as $season){
                echo '<div>';
                echo $this->Html->link(
                        $season['year']."/".($season['year']+1),
                        ['controller' => 'Seasons', 'action' => 'hunEdit', $season['id'] ],
                        ['class' => 'listed_clickable_item']
                    );
                echo '</div>';
            }
        ?>
    </div>
</div>