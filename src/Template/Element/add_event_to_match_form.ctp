<div class="row">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addEventToMatch.insertedMsg')){
                echo $this->request->session()->consume('addEventToMatch.insertedMsg');
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form class="form-inline" role="form" method="post" accept-charset="UTF-8" 
              action="<?= $this->Url->build(["controller" => "Matches", "action" => "hun_add_event_to_match", $matchInfo['id'] ]) ?>">
            <div class="form-group">
                <select class="form-control" id="event_id_select" name="event_id">
                <?php
                    foreach($possibleMatchEvents as $event){
                        $selected = '';
                        echo '<option value="'.$event['id'].'" '.$selected.'>'.$event['name'].'</option>';
                    }
                ?>
                </select>
            </div>

            <div class="form-group">
                <select class="form-control" id="player_id_select" name="player_id">
                <?php
                    foreach($players as $player){
                        $selected = '';
                        echo '<option value="'.$player['player_id'].'" '.$selected.'>'.$player['name'].' '.$player['surname'].' ('.$player['club_name'].')</option>';
                    }
                ?>
                </select>
            </div>
            
            <div class="form-group">
                <input type="text" class="form-control" name="minute" id="input_minute" placeholder="Minúta" />
            </div>

            <input type="submit" onclick="addMatchEvent();return false;" class="btn btn-default" value="Pridaj udalosť"/>
        </form>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addEventToMatch.validationErrors')){
                $addEventToMatchValidationErrors = $this->request->session()->consume('addEventToMatch.validationErrors');
                
                echo $this->Nicnajder->errorsForCell($addEventToMatchValidationErrors, 'event_id');
                echo $this->Nicnajder->errorsForCell($addEventToMatchValidationErrors, 'player_id');
                echo $this->Nicnajder->errorsForCell($addEventToMatchValidationErrors, 'minute');
            }
        ?>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12" id="add_event_to_match_ajax_error"></div>
</div>

<?php
if($this->request->session()->check('addEventToMatch')){
    $this->request->session()->delete('addEventToMatch');
}
?>

<script type="text/javascript">
    function addMatchEvent(){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Matches", "action" => "hun_add_event_to_match", $matchInfo['id'] ]); ?>",
            method : "POST",
            data: {
                minute: $("#input_minute").val(),
                event_id: $("#event_id_select option:selected").val(),
                player_id: $("#player_id_select option:selected").val()
            },
            error: function(jqXHR, status, error){
                $('#add_event_to_match_ajax_error').html("chyba pri AJAXovom volaní pri pridávaní udalosti. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
</script>