/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container);
		progress.setStatus("Pending...");
		progress.toggleCancel(true, this);
                progress.toggleRemove(false);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
                    var komunikat = "Limit "+message+" zdjęć zostanie przekroczony. Obecnie możesz dodać jeszcze " + message;
                    if(message == 1)
                    {
                        komunikat += " zdjęcie.";
                    }
                    else if(message < 5)
                    {
                        komunikat += " zdjęcia.";
                    }
                    else
                    {
                        komunikat += " zdjęć.";
                    }
                    alert(komunikat);
                    return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container);
		progress.setError();
		progress.toggleCancel(false);
                progress.toggleRemove(true);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("File is too big.");
			this.debug("Zdjęcie " + file.name + " jest zbyt duże i nie może być dodane.");
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("Nie można dodawać pustych zdjęć.");
			this.debug("Nie można dodawać pustych zdjęć.");
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("Format pliku " + file.name + " nie jest ubsługiwany.");
			this.debug("Format pliku nie jest ubsługiwany.");
			break;
		default:
			if (file !== null) {
				progress.setStatus("Nie udało się dodać zdjęcia " + file.name);
			}
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}

		/* I want auto start the upload and I can do that here */
		this.startUpload();
	} catch (ex)  {
        //this.debug(ex);
	}
}

function uploadStart(file) {
        try {
            /* I don't want to do any file validation or anything,  I'll just update the UI and
            return true to indicate that the upload should start.
            It's important to update the UI here because in Linux no uploadProgress events are called. The best
            we can do is say we are uploading.
             */
            var imgId = parseInt(file.id.replace(this.movieName+'_', ''), 10)+1;
            this.addPostParam('img_id', imgId);
            this.addPostParam('iId_container', this.customSettings.iId_container);
            var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container);
            progress.setStatus("Uploading...");
            progress.toggleCancel(true, this);
            progress.toggleRemove(false);
	}
	catch (ex) {}

	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container);
		progress.setProgress(percent);
		progress.setStatus("Uploading...");
	} catch (ex) {
		this.debug(ex);
	}
}
/*
*serverData wymaga specjalnego formatu zwrotnego typu json:
* obiect[
* 'id' => '', //id pojazdu,
* 'url' => '', // link html do miniatury zdjecia
* 'error' => true/false // informacja czy bly blady podczas przetwazania zdjecia
* ]
*/
function uploadSuccess(file, serverData) {
        var parsed_Data = JSON.parse(serverData);

        if(parsed_Data && !parsed_Data.error)
        {
            try {
                    var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container,this.customSettings.img_wektor_id);
                    progress.setComplete();
                    progress.toggleCancel(false);
                    progress.toggleRemove(false);
                    progress.setStatus("Complete.");
                    progress.toggleReplace(this.customSettings.image_name_prefix+parsed_Data.id, parsed_Data.url, parsed_Data.id,this.customSettings.img_container_class, this);

            } catch (ex) {
                    this.debug(ex);
            }
            if( typeof update_html_status === 'function')
                update_html_status(this, this.customSettings.statis_html_id);
            if( typeof ustalWektorZdjec === 'function')
                ustalWektorZdjec();
        }
        else
        {
            try {
                    var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container,this.customSettings.img_wektor_id);
                    progress.setError();
                    progress.toggleCancel(false);
                    progress.toggleRemove(true);

            } catch (ex) {
                    this.debug(ex);
            }

        }
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget,this.customSettings.all_images_div_container, this.customSettings.img_wektor_id);
		progress.setError();
		progress.toggleCancel(false);
                progress.toggleRemove(true);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("Upload Error: " + message);
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("Upload Failed.");
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("Server (IO) Error");
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("Security Error");
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("Upload limit exceeded.");
			this.debug("Przekroczono limit 12 zdjęć.");
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("Failed Validation.  Upload skipped.");
			this.debug("Nie udało się dodać zdjęcia " + file.name + ". Zdjęcie zostało pominięte.");
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("Pobieranie zdjęcia " + file.name + " zostało anulowane.");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("Pobieranie zdjęcia " + file.name + " zostało zatrzymane.");
			break;
		default:
			progress.setStatus("Unhandled Error: " + errorCode);
			this.debug("Nie udało się dodać zdjęcia " + file.name);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
}