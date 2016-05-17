<!-- Players -->

<?php
    $requestData = $this->request->data;
?>

<div class="row">
    <div class="col-md-12 form_info_msg">
        <?php
            if(isset($actionMsg)){
                echo $actionMsg;
            }
        ?>
    </div>
</div>

<form class="form-horizontal" role="form" method="post" accept-charset="UTF-8">
    <div class="form-group">
        <label class="control-label col-md-1" for="name">Meno:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php
                    if(isset($requestData['name'])){
                        echo $requestData['name'];
                    }
                   ?>" placeholder="Meno" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'name') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="surname">Priezvisko:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="surname" name="surname" 
                   value="<?php
                    if(isset($requestData['surname'])){
                        echo $requestData['surname'];
                    }
                   ?>" placeholder="Priezvisko" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'surname') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="club_id">Klub:</label>
        <div class="col-md-4">
            <select class="form-control" id="club_id" name="club_id">
                <option value="0">Žiadny klub</option>
                <?php
                    foreach($allClubs as $club){
                        $selected = '';
                        if(isset($requestData['club_id']) && $requestData['club_id'] == $club['id']){
                            $selected = 'selected';
                        }
                        
                        echo '<option value="'.$club['id'].'" '.$selected.'>'.$club['name'].'</option>';
                    }
                ?>
            </select>
        </div>
        
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'club_id') ?>
        </div>
    </div>
    
    <div class="form-group">
        <div class="col-md-offset-1 col-md-10">
            <button type="submit" class="btn btn-default">
                <?php
                    if($this->request->params['action'] == 'hunEdit'){
                        echo "Uprav!";
                    }
                    else if($this->request->params['action'] == 'hunAdd'){
                        echo "Pridaj!";
                    }
                ?>
            </button>
        </div>
    </div>
</form>