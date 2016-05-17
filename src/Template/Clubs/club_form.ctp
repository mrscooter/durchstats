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

<form class="form-horizontal" role="form" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <div class="form-group">
        <label class="control-label col-md-1" for="name">Názov klubu:</label>
        <div class="col-md-4">
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php
                    if(isset($requestData['name'])){
                        echo $requestData['name'];
                    }
                   ?>" placeholder="Názov klubu" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'name') ?>
        </div>
    </div>
    
    <div class="form-group">
        <label class="control-label col-md-1" for="name">Logo klubu (vo formáte .png):</label>
        <div class="col-md-4">
            <input type="file" id="logo" name="logo" />
        </div>
        <div class="col-md-7 input_error">
            <?= $this->Nicnajder->errorsForCell($validationErrors, 'logo') ?>
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