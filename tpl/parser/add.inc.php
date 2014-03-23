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
        <div class="page container-fluid">
            
            <?php
                include CFG_ROOT . '/tpl/header.inc.php';
            ?>
            
            <?php
            if(isset($aAddErrors) && count($aAddErrors) > 0)
            {
            ?>
            <div class="row">
                <div class="alert alert-danger">
                    <h1>Nie udało sie przeanalizowac przekazanego pliku XML..</h1>
                    <?php 
                    foreach ($aAddErrors as $sError)
                    {
                    ?>
                    <div>
                        <?php
                         echo $sError;
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($aAddSuccess) && count($aAddSuccess) > 0)
            {
            ?>
            <div class="row">
                <div class="alert alert-success">
                    <h1>Dodano plik XML do bazy danych.</h1>
                    <?php 
                    foreach ($aAddSuccess as $sMessage)
                    {
                    ?>
                    <div>
                        <?php
                         echo $sMessage;
                        ?>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
            <?php
            }
            ?>
            <div class="row">
                <div class="alert alert-info">
                    <h1>XML-e można dodawać na kilka sposobów.</h1>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>1. Przeciągnij i upuść dowolną ilość XML-i w wyznaczone do tego miejsce na stronie (prostokąt poniżej)</h4>
                        </div>
                        <div class="panel-body">
                            <div id="dragandrophandler_container" class="col-md-12">
                                <div id="dragandrophandler">&nbsp;Upuść pliki XML tutaj</div>
                            </div>
                        </div>
                    </div>
                    <br />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>2. Dodać XML metodą klastyczną poprzez formularz poniżej.</h4>
                        </div>
                        <div class="panel-body">
                            <form class="form-inline" role="form" action="dodaj_xml.php" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="sr-only" for="exampleInputFile">Dodanie pliku XML</label>
                                    <input name="file" class="btn btn-primary btn-lg" type="file" id="exampleInputFile">
                                </div>
                                <div class="form-group">
                                  <div class="">
                                    <button name="dodaj" type="submit" class="btn btn-primary btn-lg">Wyślij Formularz</button>
                                  </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12" id="progresBarsContent"></div>
            </div>
            
            <?php 
                include CFG_ROOT . '/tpl/foter.inc.php';
            ?>
        </div>
        
        <script type="text/javascript" src="<?php echo CFG_JQUERY_MIN_JS ?>"></script>
        <script type="text/javascript" src="<?php echo CFG_WWW_JS ?>/drag_drop_here.js"></script>

        <script>
            function sendFileToServer(formData,status)
            {
                var uploadURL = "<?php echo CFG_WWW; ?>/upload.php"; //Upload URL
                var extraData ={}; //Extra Data.
                var jqXHR=$.ajax({            
                    xhr: function() {
                        var xhrobj = $.ajaxSettings.xhr();
                        if (xhrobj.upload) 
                        {
                            xhrobj.upload.addEventListener('progress', function(event) 
                            {
                                var percent = 0;
                                var position = event.loaded || event.position;
                                var total = event.total;
                                if (event.lengthComputable) 
                                {
                                    percent = Math.ceil(position / total * 100);
                                }
                                //Set progress
                                status.setProgress(percent);
                            }, false);
                        }
                        return xhrobj;
                    },
                    url: uploadURL,
                    type: "POST",
                    contentType:false,
                    processData: false,
                    cache: false,
                    data: formData,
                    success: function(obj) 
                    {
                        var wynik = JSON.parse(obj);
                        if('undefined' != typeof wynik['status'])
                        {
                            if(true == wynik['status'])
                            {
                                status.setSuccess();
                                return;
                            }
                            else if(false == wynik['status'])
                            {
                                status.setError(wynik['info']);
                                return;
                            }
                        }
                        status.setError(wynik['info']);
                        return;
                    }
                });

                status.setAbort(jqXHR);
            }
        </script>
    </body>
</html> 