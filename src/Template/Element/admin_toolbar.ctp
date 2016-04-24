<?php
    if($this->request->session()->read('admin.login')){
?>
    <div class="panel admin_toolbar">
        <div class="panel-body">
            Vitaj <?= $this->request->session()->read('admin.login') ?>! Si admin a môžeš robiť vela cool vecí.
            <a href="<?= $this->Url->build(["controller" => 'Admins', "action" => 'logout']) ?>" class="btn btn-default" role="button" style="float: right;">Odhlásiť</a>
        </div>
    </div>
<?php
    }
?>