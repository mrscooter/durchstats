<div class="row">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addRefereeToMatch.insertedMsg')){
                echo $this->request->session()->consume('addRefereeToMatch.insertedMsg');
            }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <form class="form-inline" role="form" method="post" accept-charset="UTF-8" 
              action="<?= $this->Url->build(["controller" => "Matches", "action" => "hun_add_referee_to_match", $matchInfo['id'] ]) ?>">
            
            <input type="text" class="form-control" name="name" id="input_referee_name" placeholder="Meno" />
            <input type="text" class="form-control" name="surname" id="input_referee_surname" placeholder="Priezvisko" />

            <input type="submit" onclick="addReferee();return false;" class="btn btn-default" value="Pridaj rozhodcu"/>
        </form>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addRefereeToMatch.validationErrors')){
                $addRefereeToMatchValidationErrors = $this->request->session()->consume('addRefereeToMatch.validationErrors');
                
                echo $this->Nicnajder->errorsForCell($addRefereeToMatchValidationErrors, 'name');
                echo $this->Nicnajder->errorsForCell($addRefereeToMatchValidationErrors, 'surname');
            }
        ?>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12" id="add_referee_to_match_ajax_error"></div>
</div>

<?php
if($this->request->session()->check('addRefereeToMatch')){
    $this->request->session()->delete('addRefereeToMatch');
}
?>

<script type="text/javascript">
    function addReferee(){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Matches", "action" => "hun_add_referee_to_match", $matchInfo['id'] ]); ?>",
            method : "POST",
            data: {
                name: $("#input_referee_name").val(),
                surname: $("#input_referee_surname").val()
            },
            error: function(jqXHR, status, error){
                $('#add_referee_to_match_ajax_error').html("chyba pri AJAXovom volaní pri pridávaní rozhodcu. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
</script>