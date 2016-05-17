<div class="row">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('changeMatchPhase.insertedMsg')){
                echo $this->request->session()->consume('changeMatchPhase.insertedMsg');
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form class="form-inline" role="form" method="post" accept-charset="UTF-8" 
              action="<?= $this->Url->build(["controller" => "Matches", "action" => "hun_change_match_phase", $matchInfo['id'] ]) ?>">
            <div class="form-group">
                <select class="form-control" id="match_phase_id_select" name="match_phase_id">
                    <option value="0">Zápas sa ešte nezačal</option>
                <?php
                    foreach($possibleMatchPhases as $phase){
                        echo '<option value="'.$phase['id'].'">'.$phase['name'].'</option>';
                    }
                ?>
                </select>
            </div>

            <input type="submit" onclick="changeMatchPhase();return false;" class="btn btn-default" value="Aktualizuj fázu zápasu"/>
            
            <button type="button" onclick="completeMatch();return false;" class="btn btn-default">Ukonči zápas</button>
        </form>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('changeMatchPhase.validationErrors')){
                $changeMatchPhaseValidationErrors = $this->request->session()->consume('changeMatchPhase.validationErrors');
                
                echo $this->Nicnajder->errorsForCell($changeMatchPhaseValidationErrors, 'match_phase_id');
            }
        ?>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12" id="change_match_phase_ajax_error"></div>
</div>

<?php
if($this->request->session()->check('changeMatchPhase')){
    $this->request->session()->delete('changeMatchPhase');
}
?>

<script type="text/javascript">
    function changeMatchPhase(){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Matches", "action" => "hun_change_match_phase", $matchInfo['id'] ]); ?>",
            method : "POST",
            data: {
                match_phase_id: $("#match_phase_id_select option:selected").val(),
            },
            error: function(jqXHR, status, error){
                $('#change_match_phase_ajax_error').html("chyba pri AJAXovom volaní pri aktualizácii fázy zápasu. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
    
    function completeMatch(){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Matches", "action" => "hun_complete_match", $matchInfo['id'] ]); ?>",
            method : "POST",
            data: {},
            error: function(jqXHR, status, error){
                $('#change_match_phase_ajax_error').html("chyba pri AJAXovom volaní pri aktualizácii fázy zápasu. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
                console.log(status);
                console.log(error);
                console.log(jqXHR);
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
</script>