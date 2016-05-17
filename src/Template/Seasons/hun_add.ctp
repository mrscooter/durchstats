<!-- Seasons -->
<div class="row">
    <div class="col-md-12 form_info_msg">
        <?php
            if(isset($insertMsg)){
                echo $insertMsg;
            }
        ?>
    </div>
</div>
<form class="form-horizontal" role="form" method="post" accept-charset="UTF-8">
    <div class="form-group">
        <label class="control-label col-md-1" for="year">Rok:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="year" name="year" placeholder="Zadaj rok" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'year') ?>
        </div>
    </div>
    
    <div class="form-group">
        <div class="col-md-offset-1 col-md-10">
            <button type="submit" class="btn btn-default">
                Pridaj!
            </button>
        </div>
    </div>
</form>