<!-- Seasons -->

<div class="row">
    <div class="col-md-12 form_info_msg">
        <?php
            if($this->request->session()->check('hunSetActual.updateMsg')){
                echo $this->request->session()->consume('hunSetActual.updateMsg');
                echo '<br />';
                echo '<br />';
            }
        ?>
    </div>
</div>
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
                echo ' ';
                echo $this->Html->link(
                    'označ ako aktuálnu',
                    ['controller' => 'Seasons', 'action' => 'hunSetActual', $season['id'] ],
                    ['class' => 'listed_clickable_item']
                );
                if($season['actual']){
                    echo ' (aktuálna sezóna)';
                }
                echo '</div>';
            }
        ?>
    </div>
</div>