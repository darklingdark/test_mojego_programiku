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
        <link rel="stylesheet" type="text/css" href="<?php echo CFG_WWW_STYLE ?>/css/jquery-ui-1.9.1.custom.min.css">
        <title>Parser</title>
    </head>
    <body>
        <div class="page container">

            <?php
            include CFG_ROOT . '/tpl/header.inc.php';
            ?>
                <div id="formularz-wyszukiwania" class="collapse<?php echo $bSearch?'':' in' ?> panel panel-default">
                    <div class="panel-heading">Wyszukaj zaimportowany XML</div>
                    <div class="panel-body">
                        <form role="form" method="get" action="list.php">
                            <div>
                                <input name="limit" type="hidden" value="<?php echo $aFiltered['limit'];?>">
                                <input name="sort" type="hidden" value="<?php echo $aFiltered['sort'];?>">
                                <input name="nas" type="hidden" value="<?php echo $aFiltered['nas'];?>">
                            </div>
                            <div>
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <label for="id_raportu">Id Raportu</label>
                                        <input name="idr" type="text" id="id_raportu" class="form-control" placeholder="Id Raportu" value="<?php echo $aFiltered['idr'];?>">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="rok">Rok Sprawozdania</label>
                                        <select name="rok" id="rok" class="form-control">
                                            <option value="">Wybierz Rok</option>
        <?php                       
                                            $mSelected = false;
                                            if('' != $aFiltered['rok'])
                                            {
                                                $mSelected = $aFiltered['rok'];
                                            }
                                            foreach ($aRok as $sRok)
                                            {
                                                $sSelectedProp = '';
                                                if($mSelected && $sRok == $mSelected)
                                                {
                                                    $sSelectedProp = 'selected="selected"';
                                                }
        ?>
                                            <option <?php echo $sSelectedProp ?> value="<?php echo $sRok; ?>"><?php echo $sRok; ?></option>
        <?php
                                            }
        ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="miesiac">Miesiąc Sprawozdania</label>
                                        <input name="mies" type="text" id="miesiac" class="form-control" placeholder="Miesiąc Sprawozdania" value="<?php echo $aFiltered['mies'];?>">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="placowka">placowka</label>
                                        <select name="plc" id="placowka" class="form-control">
                                            <option value="">Wybierz placówkę</option>
        <?php                       
                                            $mSelected = false;
                                            if('' != $aFiltered['plc'])
                                            {
                                                $mSelected = $aFiltered['plc'];
                                            }
                                            foreach ($aPlacowki as $iPlc =>  $sPlacowka)
                                            {
                                                $sSelectedProp = '';
                                                if($mSelected && $iPlc == $mSelected)
                                                {
                                                    $sSelectedProp = 'selected="selected"';
                                                }
        ?>
                                            <option <?php echo $sSelectedProp ?> value="<?php echo $iPlc; ?>"><?php echo $sPlacowka; ?></option>
        <?php
                                            }
        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <label for="data_od">Data Sprawozdania</label>
                                        <input name="dspr" type="text" id="data_od" class="form-control" placeholder="Data Sprawozdania" value="<?php echo $aFiltered['dspr'];?>">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="data_do">Data Dodania Sprawozdania</label>
                                        <input name="dwpr" type="text" id="data_do" class="form-control" placeholder="Data Dodania Sprawozdania" value="<?php echo $aFiltered['dwpr'];?>">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label for="spra">Typ Sprawozdania</label>
                                        <select name="spra" id="spra" class="form-control">
                                            <option value="">Wybierz Sprawozdanie</option>
        <?php                       
                                            $mSelected = false;
                                            if('' != $aFiltered['spra'])
                                            {
                                                $mSelected = $aFiltered['spra'];
                                            }
                                            foreach ($aSprawozdania as $sSprawozdanie)
                                            {
                                                $sSelectedProp = '';
                                                if($mSelected && $sSprawozdanie == $mSelected)
                                                {
                                                    $sSelectedProp = 'selected="selected"';
                                                }
        ?>
                                            <option <?php echo $sSelectedProp ?> value="<?php echo $sSprawozdanie; ?>"><?php echo $sSprawozdanie; ?></option>
        <?php
                                            }
        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                  <div class="">
                                    <button name="search" type="submit" class="btn btn-primary  btn-bloc">Wyszukaj Przygotowane XML-e</button>
                                  </div>
                                </div>
                            </div>
                        </form>
                    </div>
                        
                </div>
                        <div class="form-group">
                          <div class="">
                            <button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#formularz-wyszukiwania">Zwiń / Rozwiń formularz wyszukiwania XML-i</button>
                          </div>
                        </div>

            
            <?php
                if(count($aPaczki) > 0)
                {
                    $aKolumns = array_keys(current($aPaczki));
            ?>
            <div id="" class="panel panel-default">
                <div class="panel-heading">
                    XML-e pasujące do parametrów wyszukiwania
                </div>
                <div class="panel-body">
<?php 
                    //zmienna $aErrorList ustawiana jest w pbierz.php
                    if(isset($aErrorList) && count($aErrorList) > 0)
                    {
                    ?>
                    <div class="alert alert-danger fade in">
                          <h4>Błąd pobierania !!!</h4>
                          <p><?php echo implode('<br />', $aErrorList); ?></p>
                        </div>
                    <?php 
                    }
?>
                    <form id="sortowanie" action="list.php" method="get">
                        <div>
                            <input name="idr" type="hidden" value="<?php echo $aFiltered['idr'];?>">
                            <input name="rok" type="hidden" value="<?php echo $aFiltered['rok'];?>">
                            <input name="mies" type="hidden" value="<?php echo $aFiltered['mies'];?>">
                            <input name="plc" type="hidden" value="<?php echo $aFiltered['plc'];?>">
                            <input name="dspr" type="hidden" value="<?php echo $aFiltered['dspr'];?>">
                            <input name="dwpr" type="hidden" value="<?php echo $aFiltered['dwpr'];?>">
                            <input name="spra" type="hidden" value="<?php echo $aFiltered['spra'];?>">
                            <input name="limit" type="hidden" value="<?php echo $aFiltered['limit'];?>">
                        </div>
                        <div class="row">
                            <div class="col-md-<?php echo ($iCountAll / $aFiltered['nas'] < 6)?'9 col-md-push-3':'12';?>">
                                <div class="col-md-3 form-group">
                                    <label for="sort">Rok Sprawozdania</label>
                                    <select name="sort" id="sort" class="form-control">
                                        <option value="">Wybierz Rok</option>
            <?php                       
                                        $mSelected = false;
                                        if('' != $aFiltered['sort'])
                                        {
                                            $mSelected = $aFiltered['sort'];
                                        }
                                        foreach (Verify_List::sortowanieList() as $sSort => $sSortLabel)
                                        {
                                            $sSelectedProp = '';
                                            if($mSelected && $sSort == $mSelected)
                                            {
                                                $sSelectedProp = 'selected="selected"';
                                            }
            ?>
                                        <option <?php echo $sSelectedProp ?> value="<?php echo $sSort; ?>"><?php echo $sSortLabel; ?></option>
            <?php
                                        }
            ?>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="nas">XML-i na stronie</label>
                                    <input name="nas" type="text" id="nas" class="form-control" placeholder="XML-i na stronie" value="<?php echo $aFiltered['nas'];?>">
                                </div>
                            </div>
                            <div class="col-md-<?php echo ($iCountAll / $aFiltered['nas'] < 6)?'3 col-md-pull-9':'12';?> form-group">
                                <br />
                                <ul class="pagination">
                                    <?php
                                        $sDisabeldPrev = $aFiltered['limit'] == 0 ? 'class="disabled"':'';
                                    ?>
                                    <li <?php echo $sDisabeldPrev; ?>>
                                        <a href="list.php?limit=0&amp;<?php echo $sUrlParams; ?>">&laquo;</a>
                                    </li>
        <?php
                                    $iLicznik = $iCount = 0;
                                    while (($iCount) < $iCountAll)
                                    {
                                        $sActive = $aFiltered['limit'] == $iCount ? 'class="active"':'';
        ?>
                                    <li <?php echo $sActive; ?> >
                                        <a href="<?php echo 'list.php?limit='.($iCount).'&amp;' . $sUrlParams; ?>"><?php echo $iLicznik + 1; ?></a>
                                    </li>
        <?php
                                        $iCount += $aFiltered['nas'];
                                        $iLicznik++;
                                    }
                                    $sDisabeldNext = $aFiltered['limit'] == ($iCount-$aFiltered['nas']) ? 'class="disabled"':'';
        ?>
                                    <li <?php echo $sDisabeldNext; ?>>
                                        <a href="<?php echo 'list.php?limit='.$aFiltered['limit'].'&amp;' . $sUrlParams; ?>">&raquo;</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <?php
                            foreach ($aKolumns as $value)
                            {
                            ?>
                            <th><?php echo $value; ?></th>
                            <?php
                            }
                            ?>
                            <?php
                            ?>
                            <th>
                                Link
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($aPaczki as $aOne)
                        {
                        ?>
                        <tr>
                            <?php
                            foreach ($aOne as $value)
                            {
                            ?>
                            <td><?php echo $value; ?></td>
                            <?php
                            }
                            ?>
                            <td><a href="<?php echo CFG_WWW . '/pobierz.php?id=' . $aOne['id']; ?>&amp;<?php echo $sUrlParams; ?>">Pobierz</a></td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <div class="panel-body">
<?php
                    $sDisabeldPrev = $aFiltered['limit'] == 0 ? 'class="disabled"':'';
?>
                    <ul class="pagination">
                        <li <?php echo $sDisabeldPrev; ?>><a href="list.php?limit=0&amp;<?php echo $sUrlParams; ?>">&laquo;</a></li>
<?php
                    $iLicznik = $iCount = 0;
                    while (($iCount) < $iCountAll)
                    {
                        $sActive = $aFiltered['limit'] == $iCount ? 'class="active"':'';
?>
                        <li <?php echo $sActive; ?>><a href="<?php echo 'list.php?limit='.($iCount).'&amp;' . $sUrlParams; ?>"><?php echo $iLicznik + 1; ?></a></li>
<?php
                        $iCount += $aFiltered['nas'];
                        $iLicznik++;
                    }
                    $sDisabeldNext = $aFiltered['limit'] == ($iCount-$aFiltered['nas']) ? 'class="disabled"':'';
?>
                        <li <?php echo $sDisabeldNext; ?>><a href="<?php echo 'list.php?limit='.$aFiltered['limit'].'&amp;' . $sUrlParams; ?>">&raquo;</a></li>
                    </ul>
                </div>

            </div>
            <?php
                    
                }
            ?>
            
            <?php 
                include CFG_ROOT . '/tpl/foter.inc.php';
            ?>
        </div>
        
        <script type="text/javascript" src="<?php echo CFG_JQUERY_MIN_JS ?>"></script>
        <script type="text/javascript" src="<?php echo CFG_JQUERYUI_MIN_JS ?>"></script>
        <script type="text/javascript" src="<?php echo CFG_BOOTSTRAP_MIN_JS ?>"></script>
        
        <script>
            $('#data_od').datepicker({
                dateFormat: "yy-mm-dd",
                dayNamesMin: [ "Pon", "Wt", "Śr", "Czw", "Pt", "Sob", "Niedz" ],
                monthNames: [ "Styczeń", "Luty", "Marzec", "Kwieceiń", "Maj", "Czerwiec", "Lipiec", "Śierpień", "Wrzesień", "Październik", "Listopad", "Grudzień" ],
                monthNamesShort: [ "Styczeń", "Luty", "Marzec", "Kwieceiń", "Maj", "Czerwiec", "Lipiec", "Śierpień", "Wrzesień", "Październik", "Listopad", "Grudzień" ],
                showButtonPanel: true,
                currentText: "Dzisiaj",
                closeText: "Zamknij",
                autoSize: true,
                changeMonth: true,
                changeYear: true,
//                maxDate: new Date(2014, 2, 17),
//                minDate: new Date(2014, 2, 14)
            });
            $('#data_do').datepicker({
                dateFormat: "yy-mm-dd",
                dayNamesMin: [ "Pon", "Wt", "Śr", "Czw", "Pt", "Sob", "Niedz" ],
                monthNames: [ "Styczeń", "Luty", "Marzec", "Kwieceiń", "Maj", "Czerwiec", "Lipiec", "Śierpień", "Wrzesień", "Październik", "Listopad", "Grudzień" ],
                monthNamesShort: [ "Styczeń", "Luty", "Marzec", "Kwieceiń", "Maj", "Czerwiec", "Lipiec", "Śierpień", "Wrzesień", "Październik", "Listopad", "Grudzień" ],
                showButtonPanel: true,
                currentText: "Dzisiaj",
                closeText: "Zamknij",
                autoSize: true,
                changeMonth: true,
                changeYear: true,
//                maxDate: new Date(2014, 2, 17),
//                minDate: new Date(2014, 2, 14)
            });
            $('#sortowanie #sort').change(function(){
                $('#sortowanie').submit();
            });
        </script>
    </body>
</html> 