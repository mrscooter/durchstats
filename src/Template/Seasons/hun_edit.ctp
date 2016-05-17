<!-- Seasons -->
<div class="row">
    <div class="col-md-12 page_heading_text">
        <?= $season['year']."/".($season['year']+1) ?>
    </div>
</div>
<br />

<div class="row">
    <div class="col-md-12 input_error" id="delete_club_in_season_ajax_error"></div>
    <div class="col-md-12 input_error">
        <?php
            if($this->request->session()->check("deleteClubInSeason.deleteError")){
                echo $this->request->session()->consume("deleteClubInSeason.deleteError");
            }
        ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="small_heading">
            Kluby účinkujúce v tejto sezóne
        </div>
        <?php
            foreach($clubsInSeason as $club){
                echo '<div>';
                echo $this->Html->link($this->Html->image(
                            "delete.icon.png", ["class" => "delete_icon_small"]),
                            "http://www.pino.webekacko.com",
                            ['escapeTitle' => false, 'onclick' => 'deleteClubInSeason('.$club['id'].'); return false;']
                        );
                echo ' '.$club['name'];
                echo '</div>';
            }
        ?>
    </div>
</div>
<br />

<div class="row">
    <div class="col-md-12">
        <form class="form-inline" role="form" method="post" accept-charset="UTF-8" 
              action="<?= $this->Url->build(["controller" => "Seasons", "action" => "hun_add_club", $season['id'] ]) ?>">
            <div class="form-group">
                <select class="form-control" id="add_to_season_club_id_select" name="club_id">
                <?php
                    foreach($clubsNotInSeason as $club){
                        echo '<option value="'.$club['id'].'">'.$club['name'].'</option>';
                    }
                ?>
                </select>
            </div>

            <input type="submit" onclick="addClubToSeason(); return false;" class="btn btn-default" value="Pridaj klub do sezóny"/>
        </form>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addClubToSeason.validationErrors')){
                $addClubValidationErrors = $this->request->session()->consume('addClubToSeason.validationErrors');
                
                echo $this->Nicnajder->errorsForCell($addClubValidationErrors, 'club_id');
            }
        ?>
    </div>
</div>

<div class="row input_error">
    <div class="col-md-12" id="add_club_to_season_ajax_error"></div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php
            if($this->request->session()->check('addClubToSeason.insertMsg')){
                echo $this->request->session()->consume('addClubToSeason.insertMsg');
            }
        ?>
    </div>
</div>

<script type="text/javascript">
    function addClubToSeason(){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Seasons", "action" => "hun_add_club", $season['id'] ]); ?>",
            method : "POST",
            data: {
                club_id: $("#add_to_season_club_id_select option:selected").val()
            },
            error: function(jqXHR, status, error){
                $('#add_club_to_season_ajax_error').html("chyba pri AJAXovom volaní pri pridávaní klubu. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
    
    function deleteClubInSeason(club_to_delete_id){
        $.ajax({
            url: "<?= $this->Url->build(["controller" => "Seasons", "action" => "hun_delete_club", $season['id'] ]); ?>",
            method : "POST",
            data: {
                club_id: club_to_delete_id
            },
            error: function(jqXHR, status, error){
                $('#delete_club_in_season_ajax_error').html("chyba pri AJAXovom volaní pri mazaní klubu. Skús to znova a ak problém pretrváva ozvy sa Šimonovi.");
            },
            success: function(result){
                $("#content_container").html(result);
            }
        });
    }
</script>

<?php
    $this->request->session()->delete('addClubToSeason');
    $this->request->session()->delete('deleteClubInSeason');
?>