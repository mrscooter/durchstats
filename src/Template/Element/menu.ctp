 <nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainMenu">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="mainMenu">
        <ul class="nav navbar-nav">
          <li>
            <?= $this->Html->link($this->Html->image("guatemala_football2.icon.png", ["class" => "menu-icon"])."Aktuálna sezóna",
                    ['controller' => 'Matches', 'action' => 'viewSeason', $actualSeasonId ],
                    ['escape' => false]) 
            ?>
          </li>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <?= $this->Html->image("moldova_shield.icon.png", ["class" => "menu-icon"]) ?>
                Aktívne tímy 
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php
                foreach ($activeClubsInActualSeason as $club){
                    echo "<li>";
                    echo $this->Html->link(
                                        $club['name'],
                                        ['controller' => 'Clubs', 'action' => 'view', $club['id'] ]
                                    );
                    echo "</li>";
                }
              ?>
            </ul>
          </li>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <?= $this->Html->image("sri_lanka_archive.icon.png", ["class" => "menu-icon"]) ?>
                Archív Sezón
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">Page 1-1</a></li>
              <li><a href="#">Page 1-2</a></li>
              <li><a href="#">Page 1-3</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <?= $this->Html->image("footbal_player.icon.png", ["class" => "menu-icon"]) ?>
                Hráčske štatistiky
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">Page 1-1</a></li>
              <li><a href="#">Page 1-2</a></li>
              <li><a href="#">Page 1-3</a></li>
            </ul>
          </li>
        </ul>
    </div>    
  </div>
</nav>