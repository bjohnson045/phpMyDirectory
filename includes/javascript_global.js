function htmlspecialchars(str) {
    if(typeof(str) == "string") {
        str = str.replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    }
    return str;
}

function newWindow(mypage,myname,w,h,features) {
    if(screen.width) {
          var winl = (screen.width-w)/2;
          var wint = (screen.height-h)/2;
      } else {
          winl = 0;wint =0;
      }

      if (winl < 0) winl = 0;
      if (wint < 0) wint = 0;

      var settings = 'height=' + h + ',';
      settings += 'width=' + w + ',';
      settings += 'top=' + wint + ',';
      settings += 'left=' + winl + ',';
      settings += features;
      settings += ' scrollbars=yes ';

      win = window.open(mypage,myname,settings);

      win.window.focus();
}

var timer_handles = [];
function set_timer(id,code,time) {
    if(id in timer_handles) {
        clearTimeout(timer_handles[id]);
    }
    timer_handles[id] = setTimeout(code,time)
}

$(document).ready(function(){
    $.ajaxSetup({
        error: function(x,e){
            if(x.status==0) {
                // Do nothing, this results in bad popups if changing pages fast
            } else if(x.status==404) {
                alert('Requested URL not found.');
            } else if(x.status==401) {
                window.location = x.responseText;
            } else if(x.status==302) {
                window.location = x.responseText;
            } else if(x.status==500) {
                if(x.responseText == 'Bad Token') {
                    location.reload(true);
                } else {
                    alert('Internal Server Error\n'+x.responseText);
                }
            } else if(e=='parsererror') {
                alert('Error.\nParsing request failed.');
            } else if(e=='timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n'+x.responseText);
            }
        }
    });
});

function addMessage(type,message,container) {
    if(!container.length) {
        container = "message_container";
    }
    $.ajax({
        data: ({
            action: "message_add",
            type: type,
            message: message
        }),
        success: function(data) {
            $("#"+container).append(data);
        }
    });
}