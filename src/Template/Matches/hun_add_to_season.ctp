<?= $this->Html->css('jquery.simple-dtpicker.css'); ?>
<?= $this->Html->script('jquery.simple-dtpicker.js') ?> 

<?php
    $requestData = $this->request->data;
?>
<div class="row">
    <div class="col-md-12 form_info_msg">
        <?php
            if(isset($insertedMsg)){
                echo $insertedMsg;
            }
        ?>
    </div>
</div>
<form class="form-horizontal" role="form" method="post" accept-charset="UTF-8">
    <div class="form-group">
        <label class="control-label col-md-1" for="home_id">Domáci:</label>
        <div class="col-md-4">
            <select class="form-control" id="home_id" name="home_id">
                <?php
                    foreach($clubsInSeason as $club){
                        $selected = '';
                        if(isset($requestData['home_id']) && $requestData['home_id'] == $club['id']){
                            $selected = 'selected';
                        }
                        
                        echo '<option value="'.$club['id'].'" '.$selected.'>'.$club['name'].'</option>';
                    }
                ?>
            </select>
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'home_id') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="away_id">Hostia:</label>
        <div class="col-md-4">
            <select class="form-control" id="away_id" name="away_id">
                <?php
                    foreach($clubsInSeason as $club){
                        $selected = '';
                        if(isset($requestData['away_id']) && $requestData['away_id'] == $club['id']){
                            $selected = 'selected';
                        }
                        
                        echo '<option value="'.$club['id'].'" '.$selected.'>'.$club['name'].'</option>';
                    }
                ?>
            </select>
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'away_id') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="round">Kolo:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="round" name="round" 
                   value="<?php
                    if(isset($requestData['round'])){
                        echo $requestData['round'];
                    }
                   ?>" 
                   placeholder="Zadaj kolo" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'round') ?>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-md-1" for="date_time_in">Dátum a čas:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" name="date_time" id="date_time_in" 
                   value="<?php
                    if(isset($requestData['date_time'])){
                        echo $requestData['date_time'];
                    }
                   ?>">
            <script type="text/javascript">
                    $(function(){
                            $('#date_time_in').appendDtpicker({
                                "locale": "cz",
                                "minuteInterval": 15,
                                "firstDayOfWeek": 1
                            });
                    });
            </script>
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'date_time') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="playtime">Hrací čas:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="playtime" name="playtime" 
                   value="<?php
                    if(isset($requestData['playtime'])){
                        echo $requestData['playtime'];
                    }
                   ?>" placeholder="Hrací čas" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'playtime') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="match_phase_id">Fáza zápasu:</label>
        <div class="col-md-4">
            <select class="form-control" id="match_phase_id" name="match_phase_id">
                <option value="0">Zápas sa momentálne nehrá</option>
                <?php
                    foreach($matchPhases as $phase){
                        $selected = '';
                        if(isset($requestData['match_phase_id']) && $requestData['match_phase_id'] == $phase['id']){
                            $selected = 'selected';
                        }
                        
                        echo '<option value="'.$phase['id'].'" '.$selected.'>'.$phase['name'].'</option>';
                    }
                ?>
            </select>
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'match_phase_id') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="season_phase_id">Fáza sezóny:</label>
        <div class="col-md-4">
        <?php
            foreach($seasonPhases as $phase){
                $checked = '';
                if(isset($requestData['season_phase_id']) && $requestData['season_phase_id'] == $phase['id']){
                    $checked = 'checked';
                }
                
                echo '<label class="radio-inline"><input type="radio" name="season_phase_id" value="'.$phase['id'].'" '.$checked.'>'.$phase['name'].'</label>';
            }
        ?>
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'season_phase_id') ?>
        </div>
    </div>
    
    <div class="form-group">
        <div class="checkbox col-md-offset-1 col-md-10">
            <label><input type="checkbox" value="completed" name="completed" 
                   <?php
                    if(isset($requestData['completed'])){
                        echo "checked";
                    }
                   ?>
                   >Zápas je ukončený</label>
        </div>
    </div>
    
    <div class="form-group">
        <div class="col-md-offset-1 col-md-10">
            <button type="submit" class="btn btn-default">Vytvor zápas</button>
        </div>
    </div>
</form>