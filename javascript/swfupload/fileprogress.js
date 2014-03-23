/*
 A simple class for displaying file information and progress
 Note: This is a demonstration only and not part of SWFUpload.
 Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
 */

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID, imgContainer, img_wektor_id) 
{
    this.fileProgressID = file.id;
    this.ImageContainerID = imgContainer;

    this.opacity = 100;
    this.height = 0;

    this.fileProgressWrapper = document.getElementById(this.fileProgressID);
    if (!this.fileProgressWrapper) 
    {
        this.fileProgressWrapper = document.createElement("div");
        this.fileProgressWrapper.className = "progressWrapper";
        this.fileProgressWrapper.id = this.fileProgressID;

        this.fileProgressElement = document.createElement("div");
        this.fileProgressElement.className = "progressContainer";


        var fileProgressElementLeft = document.createElement("div");
        fileProgressElementLeft.className = "progressContainer_left";

        var fileProgressElement_right = document.createElement("div");
        fileProgressElement_right.className = "progressContainer_right";

        var progressCancel = document.createElement("a");
        progressCancel.className = "progressCancel";
        progressCancel.href = "#";
        progressCancel.title = "przerwij pobieranie pliku";
        progressCancel.style.visibility = "hidden";
        progressCancel.appendChild(document.createTextNode(" "));

        var progressRemove = document.createElement("a");
        progressRemove.className = "progressRemove";
        progressRemove.href = "#";
        progressRemove.style.visibility = "hidden";
        progressRemove.title = "skasuj pobieranie pliku";
        var this_fileProgressWrapper = this.fileProgressWrapper;
        var this_fileProgressElement = this.fileProgressElement;
        
        progressRemove.onclick = function() 
        {
            this_fileProgressWrapper.removeChild(this_fileProgressElement);
            return false;
        };

        var ClearBoth = document.createElement("div");
        ClearBoth.style.clear = "both";

        var progressText = document.createElement("div");
        progressText.className = "progressName";
        progressText.appendChild(document.createTextNode(file.name));

        var progressBar = document.createElement("div");
        progressBar.className = "progressBarInProgress";

        var progressStatus = document.createElement("div");
        progressStatus.className = "progressBarStatus";
        progressStatus.innerHTML = "&nbsp;";

        fileProgressElement_right.aChildNodes = {};
        fileProgressElement_right.aChildNodes.progressRemove = progressRemove;
        fileProgressElement_right.aChildNodes.progressCancel = progressCancel;

        fileProgressElement_right.appendChild(progressRemove);
        fileProgressElement_right.appendChild(progressCancel);

        fileProgressElementLeft.aChildNodes = {};
        fileProgressElementLeft.aChildNodes.progressText = progressText;
        fileProgressElementLeft.aChildNodes.progressStatus = progressStatus;
        fileProgressElementLeft.aChildNodes.progressBar = progressBar;

        fileProgressElementLeft.appendChild(progressText);
        fileProgressElementLeft.appendChild(progressStatus);
        fileProgressElementLeft.appendChild(progressBar);

        this.fileProgressElement.aChildNodes = {};
        this.fileProgressElement.aChildNodes.fileProgressElementLeft = fileProgressElementLeft;
        this.fileProgressElement.aChildNodes.fileProgressElement_right = fileProgressElement_right;

        this.fileProgressElement.appendChild(fileProgressElementLeft);
        this.fileProgressElement.appendChild(fileProgressElement_right);
        this.fileProgressElement.appendChild(ClearBoth);

        this.fileProgressWrapper.aChildNodes = {};
        this.fileProgressWrapper.aChildNodes.fileProgressElement = this.fileProgressElement;

        this.fileProgressWrapper.appendChild(this.fileProgressElement);

        document.getElementById(targetID).appendChild(this.fileProgressWrapper);
    } else {
        this.fileProgressElement = this.fileProgressWrapper.aChildNodes.fileProgressElement;
        this.img_wektor_id = img_wektor_id;
        this.reset();
    }
}

FileProgress.prototype.setTimer = function(timer) {
    this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function(timer) {
    return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function() 
{
    this.fileProgressElement.className = "progressContainer";

    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressStatus.innerHTML = "&nbsp;";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressStatus.className = "progressBarStatus";

    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.className = "progressBarInProgress";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.style.width = "0%";

    this.appear();
};

FileProgress.prototype.setProgress = function(percentage) {
    this.fileProgressElement.className = "progressContainer green";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.className = "progressBarInProgress";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.style.width = percentage + "%";

    this.appear();
};

FileProgress.prototype.setComplete = function() {
    //sleep(121212122);
    this.fileProgressElement.className = "progressContainer blue";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.className = "progressBarComplete";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.style.width = "";

    var oSelf = this;
};

FileProgress.prototype.setError = function() {
    this.fileProgressElement.className = "progressContainer red";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.className = "progressBarError";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.style.width = "";

    var oSelf = this;
};

FileProgress.prototype.setCancelled = function() {
    this.fileProgressElement.className = "progressContainer";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.className = "progressBarError";
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressBar.style.width = "";

    var oSelf = this;
    this.setTimer(setTimeout(function() {
        oSelf.disappear();
    }, 2000));
};

FileProgress.prototype.setStatus = function(status) {
    this.fileProgressElement.aChildNodes.fileProgressElementLeft.aChildNodes.progressStatus.innerHTML = status;
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function(show, swfUploadInstance) 
{
    this.fileProgressElement.aChildNodes.fileProgressElement_right.aChildNodes.progressCancel.style.visibility = show ? "visible" : "hidden";
    if (swfUploadInstance) {
        var fileID = this.fileProgressID;
        this.fileProgressElement.aChildNodes.fileProgressElement_right.aChildNodes.progressCancel.onclick = function() {
            swfUploadInstance.cancelUpload(fileID);
            return false;
        };
    }
};

// Show/Hide the cancel button
FileProgress.prototype.toggleRemove = function(show) 
{
    this.fileProgressElement.aChildNodes.fileProgressElement_right.aChildNodes.progressRemove.style.visibility = show ? "visible" : "hidden";
};

// Show/Hide the cancel button
FileProgress.prototype.imageRemove = function(removed_div_id, img_id, objSwfu) {
    if (removed_div_id) {
        var htmlImageContainer = document.getElementById(this.ImageContainerID);
        var htmlImage = document.getElementById(removed_div_id);
        if (htmlImage !== null)
            htmlImageContainer.removeChild(htmlImage);
    }
    if (img_id) {
        //kasowanie id z wektora
        var elem_wektor = document.getElementById(this.img_wektor_id);
        if (elem_wektor != null)
        {
            var wektor_val = elem_wektor.value;
            if (wektor_val == img_id.toString())
            {
                wektor_val = "";
            }
            else
            {
                wektor_val = wektor_val.replace(',' + img_id.toString(), '');
                wektor_val = wektor_val.replace(img_id.toString() + ',', '');
            }
            elem_wektor.value = wektor_val;
        }
    }
    if (typeof swfu_status_down === "function")
        swfu_status_down(objSwfu);
};
// add image
FileProgress.prototype.toggleReplace = function(image_name_id, imageUrl, img_id, img_div_class, swfu)
{
    if (imageUrl) {
        var fileImageContener = document.createElement("div");
        
        fileImageContener.className = "imageContainer " + img_div_class;
        fileImageContener.id = image_name_id;

        var imageRemove = document.createElement("a");
        imageRemove.className = "imageRemove";
        imageRemove.href = "#";
        imageRemove.title = "Skasuj zdjęcie";
        imageRemove.onclick = function() {
            if (typeof remove_image === "function")
                remove_image(image_name_id, img_id, objSwfu);
            else
                objFileProgress.imageRemove(image_name_id, img_id, objSwfu);
            return false;
        };
        
        var objFileProgress = this;
        var objSwfu = swfu;

        var imageImg = document.createElement("img");
        imageImg.className = "imageImg";
        imageImg.src = imageUrl;

        function createHtmlAttribute(name, value) {
            var attribute = document.createAttribute(name)
            attribute.nodeValue = value
            return attribute
        }

        var ImageContener = document.createElement("div");
        
        ImageContener.className = "miltiupload_img_container_class";
        ImageContener.aChildNodes = {};
        ImageContener.aChildNodes.imageImg = imageImg;
        ImageContener.appendChild(imageRemove);
        ImageContener.appendChild(imageImg);

        fileImageContener.aChildNodes = {};
        fileImageContener.aChildNodes.ImageContener = ImageContener;
        fileImageContener.appendChild(ImageContener);

        var ClearBoth = document.createElement("div");
        ClearBoth.style.clear = "both";

        var htmlImgContainer = document.getElementById(this.ImageContainerID);
        if (htmlImgContainer.lastChild !== null)
            htmlImgContainer.removeChild(htmlImgContainer.lastChild);

        htmlImgContainer.appendChild(fileImageContener);
        htmlImgContainer.appendChild(ClearBoth);

        if (this.fileProgressElement)
            this.fileProgressWrapper.removeChild(this.fileProgressElement);

        var elem_wektor = document.getElementById(this.img_wektor_id);

        if (elem_wektor != null)
        {
            var wektor_val = elem_wektor.value;
            if (wektor_val != null && wektor_val.length > 0)
                wektor_val += ',' + img_id;
            else
                wektor_val = img_id;
            elem_wektor.value = wektor_val;
            //elem_wektor.setAttribute('value', wektor_val);
        }
    }
};

// Show/Hide the cancel button
FileProgress.prototype.ProgressRemove = function(remove) {
    if (remove) {
        this.fileProgressWrapper.removeChild(this.fileProgressElement);
    }
};

FileProgress.prototype.appear = function() 
{
    if (this.getTimer() !== null) {
        clearTimeout(this.getTimer());
        this.setTimer(null);
    }

    if (this.fileProgressWrapper.filters) {
        try {
            this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
        } catch (e) {
            // If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
            this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
        }
    } else {
        this.fileProgressWrapper.style.opacity = 1;
    }

    this.fileProgressWrapper.style.height = "";

    this.height = this.fileProgressWrapper.offsetHeight;
    this.opacity = 100;
    this.fileProgressWrapper.style.display = "";

};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function() {

    var reduceOpacityBy = 15;
    var reduceHeightBy = 4;
    var rate = 30;	// 15 fps

    if (this.opacity > 0) {
        this.opacity -= reduceOpacityBy;
        if (this.opacity < 0) {
            this.opacity = 0;
        }

        if (this.fileProgressWrapper.filters) {
            try {
                this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
            } catch (e) {
                // If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
                this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
            }
        } else {
            this.fileProgressWrapper.style.opacity = this.opacity / 100;
        }
    }

    if (this.height > 0) {
        this.height -= reduceHeightBy;
        if (this.height < 0) {
            this.height = 0;
        }

        this.fileProgressWrapper.style.height = this.height + "px";
    }

    if (this.height > 0 || this.opacity > 0) {
        var oSelf = this;
        this.setTimer(setTimeout(function() {
            oSelf.disappear();
        }, rate));
    } else {
        this.fileProgressWrapper.style.display = "none";
        this.setTimer(null);
    }
};