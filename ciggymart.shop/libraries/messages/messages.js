// START OF MESSAGE SCRIPT //

var MSGTIMER = 20;
var MSGSPEED = 5;
var MSGOFFSET = 3;
var MSGHIDE = 3;


///////////////////////////////////////////////////////

function inlineMsg2(target,string,autohide) {
  var msg2;
  var msgcontent;
  if(!document.getElementById('msg2')) {
    msg2 = document.createElement('div');
    msg2.id = 'msg2';
    msgcontent = document.createElement('div');
    msgcontent.id = 'msgcontent';
    document.body.appendChild(msg2);
    msg2.appendChild(msgcontent);
    msg2.style.filter = 'alpha(opacity=0)';
    msg2.style.opacity = 0;
    msg2.alpha = 0;
  } else {
    msg2 = document.getElementById('msg2');
    msgcontent = document.getElementById('msgcontent');
  }
  msgcontent.innerHTML = string;
  msg2.style.display = 'block';
  var msgheight = msg2.offsetHeight;
  var targetdiv = document.getElementById(target);
  targetdiv.focus();
  var targetheight = targetdiv.offsetHeight;
  var targetwidth = targetdiv.offsetWidth;
  var topposition = topPosition(targetdiv) - ((msgheight - targetheight) / 2);
  var leftposition = leftPosition(targetdiv) + targetwidth + MSGOFFSET;
  msg2.style.top = topposition + 'px';
  msg2.style.left = leftposition + 'px';
  clearInterval(msg2.timer);
  msg2.timer = setInterval("fadeMsg2(1)", MSGTIMER);
  if(!autohide) {
    autohide = MSGHIDE;  
  }
 window.setTimeout("hideMsg2()", (autohide * 1000));
}

function hideMsg2(msg) {
  var msg2 = document.getElementById('msg2');
  if(!msg2.timer) {
    msg2.timer = setInterval("fadeMsg2(0)", MSGTIMER);
  }
}

// face the message box //
function fadeMsg2(flag) {
  if(flag == null) {
    flag = 1;
  }
  var msg2 = document.getElementById('msg2');
  var value;
  if(flag == 1) {
    value = msg2.alpha + MSGSPEED;
  } else {
    value = msg2.alpha - MSGSPEED;
  }
  msg2.alpha = value;
  msg2.style.opacity = (value / 100);
  msg2.style.filter = 'alpha(opacity=' + value + ')';
  if(value >= 99) {
    clearInterval(msg2.timer);
    msg2.timer = null;
  } else if(value <= 1) {
    msg2.style.display = "none";
    clearInterval(msg2.timer);
  }
}

///////////////////////////////////////////////////////







// build out the divs, set attributes and call the fade function //
function inlineMsg(target,string,autohide) {
  var msg;
  var msgcontent;
  if(!document.getElementById('msg')) {
    msg = document.createElement('div');
    msg.id = 'msg';
    msgcontent = document.createElement('div');
    msgcontent.id = 'msgcontent';
    document.body.appendChild(msg);
    msg.appendChild(msgcontent);
    msg.style.filter = 'alpha(opacity=0)';
    msg.style.opacity = 0;
    msg.alpha = 0;
  } else {
    msg = document.getElementById('msg');
    msgcontent = document.getElementById('msgcontent');
  }
  msgcontent.innerHTML = string;
  msg.style.display = 'block';
  var msgheight = msg.offsetHeight;
  var targetdiv = document.getElementById(target);
  targetdiv.focus();
  var targetheight = targetdiv.offsetHeight;
  var targetwidth = targetdiv.offsetWidth;
  var topposition = topPosition(targetdiv) - ((msgheight - targetheight) / 2);
  var leftposition = leftPosition(targetdiv) + targetwidth + MSGOFFSET;
  msg.style.top = topposition + 'px';
  msg.style.left = leftposition + 'px';
  clearInterval(msg.timer);
  msg.timer = setInterval("fadeMsg(1)", MSGTIMER);
  if(!autohide) {
    autohide = MSGHIDE;  
  }
 window.setTimeout("hideMsg()", (autohide * 1000));
}

// hide the form alert //
function hideMsg(msg) {
  var msg = document.getElementById('msg');
  if(!msg.timer) {
    msg.timer = setInterval("fadeMsg(0)", MSGTIMER);
  }
}

// face the message box //
function fadeMsg(flag) {
  if(flag == null) {
    flag = 1;
  }
  var msg = document.getElementById('msg');
  var value;
  if(flag == 1) {
    value = msg.alpha + MSGSPEED;
  } else {
    value = msg.alpha - MSGSPEED;
  }
  msg.alpha = value;
  msg.style.opacity = (value / 100);
  msg.style.filter = 'alpha(opacity=' + value + ')';
  if(value >= 99) {
    clearInterval(msg.timer);
    msg.timer = null;
  } else if(value <= 1) {
    msg.style.display = "none";
    clearInterval(msg.timer);
  }
}

// calculate the position of the element in relation to the left of the browser //
function leftPosition(target) {
  var left = 0;
  if(target.offsetParent) {
    while(1) {
      left += target.offsetLeft;
      if(!target.offsetParent) {
        break;
      }
      target = target.offsetParent;
    }
  } else if(target.x) {
    left += target.x;
  }
  return left;
}

// calculate the position of the element in relation to the top of the browser window //
function topPosition(target) {
  var top = 0;
  if(target.offsetParent) {
    while(1) {
      top += target.offsetTop;
      if(!target.offsetParent) {
        break;
      }
      target = target.offsetParent;
    }
  } else if(target.y) {
    top += target.y;
  }
  return top;
}

// preload the arrow //
if(document.images) {
  arrow = new Image(7,80); 
  arrow.src = "images/msg_arrow.gif"; 
}
function trimString (str) 
{
	str = this != window? this : str;
	return str.replace(/^\s+/g, '').replace(/\s+$/g, '');
}

function emailValidator(email)
{
	 var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
  			// var email = document.getElementById(id).value;
			 if(reg.test(email) == false) {
		   		
				return false;
				}
				else
					return true;
}