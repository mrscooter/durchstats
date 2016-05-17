<?php
    if($this->request->session()->read('admin.login')){
?>
    <div class="panel admin_toolbar">
        <div class="panel-body">
            Vitaj <?= $this->request->session()->read('admin.login') ?>! Si admin a môžeš robiť vela cool vecí.
            <a href="<?= $this->Url->build(["controller" => 'Admins', "action" => 'logout']) ?>" class="btn btn-default" role="button" style="float: right;">Odhlásiť</a>
            <a href="<?= $this->Url->build(["controller" => 'Seasons', "action" => 'hunAdd']) ?>" class="btn btn-primary" role="button">Pridaj sezónu</a>
            <a href="<?= $this->Url->build(["controller" => 'Seasons', "action" => 'hunList']) ?>" class="btn btn-primary" role="button">Prehlad sezón</a>
            <a href="<?= $this->Url->build(["controller" => 'Players', "action" => 'hunAdd']) ?>" class="btn btn-primary" role="button">Pridaj hráča</a>
            <a href="<?= $this->Url->build(["controller" => 'Players', "action" => 'hunList']) ?>" class="btn btn-primary" role="button">Prehlad hráčov</a>
            <a href="<?= $this->Url->build(["controller" => 'Clubs', "action" => 'hunAdd']) ?>" class="btn btn-primary" role="button">Pridaj klub</a>
            <a href="<?= $this->Url->build(["controller" => 'Clubs', "action" => 'hunList']) ?>" class="btn btn-primary" role="button">Prehlad klubov</a>
        </div>
    </div>
<?php
    }
?>