/**
 * wymagane opcje w obiekcie przekazanym jako opcje:
 * <b>iFileUploadLimit</b> - limit pobieranych zdjec<br />
 * <b>iId_container</b> - identyfikator do identyfikacji zdjec<br />
 * <b>sImgPrefix</b> - prefix doklejany do id zdjecia<br />
 * <b>urlUploadEngine</b> - link do skryptu kopiowania zdjec przekazanych z obiektu flashowego<br />
 * <b>sImg_wektor_id</b> - id obiektu html przechowujacego wektor identyfikatorow zdjec<br />
 * <b>sImg_container_class_id</b> - nazwa kalsy przypisanej elementom html w ktorych znajduja sie poszczegolne zdjecia<br />
 * <b>sAll_images_div_container</b> - id elementu, w ktorym znajduja sie wszystkie kontenery zdjec<br />
 * <b>sStatis_html_id</b> - nazwa id elementu w ktorym wyswietlany jest status pobranych zdjec<br />
 * <b>sFlash_url</b> - link do obiektu swf pobierania zdjec<br />
 * <b>urlImageFolder</b> - sciezka do folderu, gdzie maja byc wgrywane zdjecia<br />
 * <b>urlhttpImageFolder</b> - link do folderu, gdzie maja byc wgrywane zdjecia<br />
 * <b>CFG_WWW</b> - link do folderu poczatkowego programu<br />
 */
function SWFUploadSettings(options) {
    if(!options)
    {
        throw new Error('brak opcji');
        return false;
    }
    if(!options['iFileUploadLimit'])
    {
        throw new Error('brak opcji iFileUploadLimit');
        return false;
    }
    if(!options['iId_container'])
    {
        throw new Error('brak opcji iId_container');
        return false;
    }
    if(!options['sImgPrefix'])
    {
        throw new Error('brak opcji sImgPrefix');
        return false;
    }
    if(!options['urlUploadEngine'])
    {
        throw new Error('brak opcji urlUploadEngine');
        return false;
    }
    if(!options['sImg_wektor_id'])
    {
        throw new Error('brak opcji sImg_wektor_id');
        return false;
    }
    if(!options['sImg_container_class_id'])
    {
        throw new Error('brak opcji sImg_container_class_id');
        return false;
    }
    if(!options['sAll_images_div_container'])
    {
        throw new Error('brak opcji sAll_images_div_container');
        return false;
    }
    if(!options['sStatis_html_id'])
    {
        throw new Error('brak opcji iFilsStatis_html_ideUploadLimit');
        return false;
    }
    if(!options['sFlash_url'])
    {
        throw new Error('brak opcji sFlash_url');
        return false;
    }
    if(!options['sButtonPlaceHolder'])
    {
        throw new Error('brak opcji sButtonPlaceHolder');
        return false;
    }
    if(!options['CFG_WWW'])
    {
        throw new Error('brak opcji CFG_WWW');
        return false;
    }
    var sMultiupload_button = options['CFG_WWW']+"/tpl/default/img/buttons/dodaj_zdjecia_multiupload.jpg";
    if(
        options['urlButon_img']
    )
    {
        sMultiupload_button = options['urlButon_img'];
    }
    var sMultiupload_button_width = "100";
    var sMultiupload_button_height = 22;
    if(
        options['iButonWidth']
    )
    {
        sMultiupload_button_width = options['iButonWidth'].toString();
    }
    if(
        options['iButonHeight']
    )
    {
        sMultiupload_button_height = options['iButonHeight'].toString();
    }
    var settings = {
        flash_url : options['sFlash_url'],
        upload_url: options['urlUploadEngine'],
        file_size_limit : "100 MB",
        file_types : "*.jpg;*.jpeg;*.JPG;*.JPEG",
        file_types_description : "All jpg Files",
        file_upload_limit : options['iFileUploadLimit'],
        file_queue_limit : 0,
        custom_settings : {
            progressTarget : "fsUploadProgress",
            statis_html_id : options['sStatis_html_id'],
            cancelButtonId : "btnCancel",
            iId_container : options['iId_container'],
            all_images_div_container : options['sAll_images_div_container'],
            image_name_prefix : options['sImgPrefix'],
            img_wektor_id : options['sImg_wektor_id'],
            img_container_class : options['sImg_container_class_id']
        },
        post_params: {
            "PHPSESSID" : "",
            "urlImageFolder" : options['urlImageFolder']?options['urlImageFolder']:'',
            "urlhttpImageFolder" : options['urlhttpImageFolder']?options['urlhttpImageFolder']:''
        },
        debug: false,

        // Button settings
        button_image_url: sMultiupload_button,
        button_width: sMultiupload_button_width,
        button_height: sMultiupload_button_height,
        button_placeholder_id: options['sButtonPlaceHolder'],

        file_queued_handler : fileQueued,
        file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : uploadStart,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete,
        queue_complete_handler : queueComplete // Queue plugin event
    };

    return settings;        
};