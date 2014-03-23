/* 
 * skrypt obslugi drag and drop here
 */
var obj = $("#dragandrophandler");
var pbObjContener = $("#progresBarsContent");
var ddObjContener = $("#dragandrophandler_container");
var pbObj = $("#progresBarsContent");

obj.on('dragenter', function (e)
{
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '2px solid #0B85A1');
});

obj.on('dragover', function (e)
{
     e.stopPropagation();
     e.preventDefault();
});

obj.on('drop', function (e,e2)
{
     $(this).css('border', '2px dotted #0B85A1');
     e.preventDefault();
     var files = e.originalEvent.dataTransfer.files;
     //We need to send dropped files to Server
     handleFileUpload(files,pbObj);
});

$(document).on('dragenter', function (e)
{
    e.stopPropagation();
    e.preventDefault();
});

$(document).on('dragover', function (e)
{
  e.stopPropagation();
  e.preventDefault();
  obj.css('border', '2px dotted #0B85A1');
});

$(document).on('drop', function (e)
{
    e.stopPropagation();
    e.preventDefault();
});

function createStatusbar(obj)
{
     this.statusbar = $('<div class="alert alert-info col-md-12"></div>');
     this.statusbarContenr = $('<div class="col-md-12"></div>').appendTo(this.statusbar);
     this.filename = $('<div class="col-md-5 filename"></div>').appendTo(this.statusbarContenr);
     this.size = $('<div class="col-md-1 filesize"></div>').appendTo(this.statusbarContenr);
     this.progressBar = $('<div class="col-md-4 progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div>').appendTo(this.statusbarContenr);
     this.abort = $('<div class="col-md-2 abort">Abort</div>').appendTo(this.statusbarContenr);
     this.statusbarError = $('<div class="col-md-12"></div>').appendTo(this.statusbar);
     obj.prepend(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    
    this.setProgress = function(progress)
    {      
        var progressBarWidth =progress+'%';//*this.progressBar.width()/ 100; 
        if(0 == this.progressBar.width())
        {
            progressBarWidth = 100;
        }
        this.progressBar.find('.progress-bar').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
        }
    }
    
    this.setError = function(errorText)
    {
        $('<div class="alert alert-danger">'+errorText+'</div>').appendTo(this.statusbarError);
        this.progressBar.addClass('progressError').html("Error").find('div').animate({ width: 0 }, 1);
        this.statusbar.removeClass('alert-info').addClass('alert-warning');
        this.abort.hide();
    }
    
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
        });
    }
    
    this.setSuccess = function()
    {
        this.progressBar.find('.progress-bar').animate({ width: '100%' }, 10).html("100% ");
        this.statusbar.removeClass('alert-info').addClass('alert-success');
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
        }
    }
}

function handleFileUpload(files,obj)
{
   for (var i = 0; i < files.length; i++)
   {
        var fd = new FormData();
        fd.append('file', files[i]);
 
 
        var status = new createStatusbar(obj); //Using this we can set progress.
        status.setFileNameSize(files[i].name,files[i].size);
        sendFileToServer(fd,status);
 
   }
}
