<?php
/*
 * Template dla index.php
 */
?><!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo CFG_WWW_STYLE ?>/css/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo CFG_WWW_STYLE ?>/css/bootstrap.min.css">
        <title>Parser</title>
    </head>
    <body>
        <div class="page container">
            
            <?php
                include CFG_ROOT . '/tpl/header.inc.php';
            ?>
            
            <div id="" class="row">
                <div class="col-md-12 form-group">
                    <label class="control-label" for="dodaj_nowe">Dodaj nowe wpisy do tablicy</label>
                    
                    <a class="form-control" id="dodaj_nowe" href="<?php echo CFG_WWW; ?>/add.php"><span class="glyphicon glyphicon-download-alt"></span> Dodaj XML-e z BeStia</a>
                </div>
                
                <div class="col-md-12 form-group">
                    <label class="control-label" for="dodaj_nowe">Dodaj nowe wpisy do tablicy</label>
                    
                    <a class="form-control" id="dodaj_nowe" href="<?php echo CFG_WWW; ?>/add.php"><span class="glyphicon glyphicon-list-alt"></span> Pobierz przygotowane XML-e</a>
                </div>
            </div>
            
            <?php 
                include CFG_ROOT . '/tpl/foter.inc.php';
            ?>
        </div>
        
        <script type="text/javascript" src="<?php echo CFG_JQUERY_MIN_JS ?>"></script>
    </body>
</html> 