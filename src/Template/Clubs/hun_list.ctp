<!-- Clubs -->

<div class="row">
    <div class="col-md-12">
        <?php
            foreach($allClubs as $club){
                echo '<div>';
                echo $this->Html->link(
                        $this->Html->image("/img/teams/".$club['id'].".png", ["class" => "club_logo_list", "alt" => 'chýba logo']).' '.$club['name'],
                        ['controller' => 'Clubs', 'action' => 'hunEdit', $club['id'] ],
                        ['class' => 'listed_clickable_item', 'escape' => false]
                    );
                echo '</div>';
            }
        ?>
    </div>
</div>