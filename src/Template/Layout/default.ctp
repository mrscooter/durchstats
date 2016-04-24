<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        DurchStats
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('bootstrap/bootstrap.css') ?>
    <?= $this->Html->css('base.css') ?>
    
    <?= $this->Html->script('jquery-1.12.3.js') ?>
    <?= $this->Html->script('bootstrap.js') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?= $this->Flash->render() ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <?= $this->Html->image("header.png", ["class" => "img-responsive"]) ?>
            </div>
        </div>
        <div class="row">
            <?= $this->element('admin_toolbar') ?>
        </div>
        <div class="row">
            <?= $this->element('menu') ?>
        </div>
        <div class="row content">
            <div id="content_container" class="col-md-12 col-xs-12">
                <?= $this->fetch('content') ?>
            </div>
        </div>
        <div class="row footer">
            <div class="col-md-12 col-xs-12">
                Hovadsky ďakujem stránke <?= $this->Html->link('freeflagicons.com', 'http://www.freeflagicons.com') ?> za ich neuveritelne geniálne ikonky s vlajkami Guatemaly, Surinamu a podobných krajín.
            </div>
        </div>
    </div>
</body>
</html>
