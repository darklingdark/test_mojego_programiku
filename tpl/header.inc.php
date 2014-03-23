<?php

/* 
 * Naglowek
 */
?>
<div class="header">
    <div class="page-header">
        <h1>
            <span class="glyphicon glyphicon-stats"></span>
            Parser
            <small>Analiza XMLi</small>
        </h1>
    </div>
    <div class="page-header">
        <ul class="nav nav-pills nav-justified">
            <li class="<?php echo $sDZIAL == 'index'?'active':'' ?>">
                <a class="glyphicon glyphicon-home" href="<?php echo CFG_WWW; ?>">&nbsp;home</a>
            </li>
            <li class="<?php echo $sDZIAL == 'dodaj'?'active':'' ?>">
                <a class="glyphicon glyphicon-download-alt" href="<?php echo CFG_WWW; ?>/add.php">&nbsp;Dodaj XML-e z BeStia</a>
            </li>
            <li class="<?php echo $sDZIAL == 'lista'?'active':'' ?>">
                <a class="glyphicon glyphicon-list-alt" href="<?php echo CFG_WWW; ?>/list.php">&nbsp;Pobierz przygotowane XML-e</a>
            </li>
        </ul>
    </div>
</div>
