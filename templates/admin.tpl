{literal}
<script lang="javascript">
var editing = false;
var _curID = null;

var xmlHttp = null;
var _movingdata = false;

var uri="backend.php";

/**
 * Tiedon k‰sittely
 */

function requesterInit() {
    try{
       xmlHttp=new ActiveXObject("Msxml2.XMLHTTP")
    } catch(e){
        try{
            xmlHttp=new ActiveXObject("Microsoft.XMLHTTP")
        } catch(sc) {
            xmlHttp=null;
        }
    }
    if(!xmlHttp&&typeof XMLHttpRequest!="undefined") {
        xmlHttp=new XMLHttpRequest();
    } else {
        alert("Selaimesi ei tue XMLHttpRequestia.\nEt voi p‰ivitt‰‰ sivuja :(");
        _movingdata=true;
    }
}

function getXMLHTTPResult() {
    if(xmlHttp&&xmlHttp.readyState!=0&&_movingdata==false) {
        xmlHttp.abort();
        requesterInit();
    } else if(!xmlHttp) {
        alert("Selaimesi ei tue XMLHttpRequestia.\nEt voi p‰ivitt‰‰ sivuja :(");
        _movingdata = true;
    } else {
        if(_movingdata == false ) {
            xmlHttp.open("GET",topuri,true);
            xmlHttp.onreadystatechange=gotResult;
            xmlHttp.send();
            return true;
        }
    }
}

function gotResult() {
    if(xmlHttp.readyState==4&&xmlHttp.responseText) {
        eval(xmlHttp.responseText);
    }
}

function parseData(data) {

}


/**
 * Sivun editointi osa
 */

function createToolbar() {
    var toolbar = document.createElement('DIV');
    toolbar.id="toolbar";

    var doneBtn = document.createElement('button');
    var doneTxt = document.createTextNode('Talleta');
    doneBtn.appendChild(doneTxt);
    doneBtn.onclick = saveEdit;

    toolbar.appendChild(doneBtn);
    return toolbar;
}

function catchIt(e)
{
    if (!document.getElementById || !document.createElement) return;
    if (!e) var obj = window.event.srcElement;
    else var obj = e.target;
    while (obj.nodeType != 1)
    {
        obj = obj.parentNode;
    }
    // Skipataan jos klikattiin linkki‰
    if (obj.tagName == 'a') return;
    if (obj.tagName == 'TEXTAREA') return;
    if (editing) saveEdit();
    while (obj.nodeName != 'HTML' && obj.parentNode.getAttribute('class') != 'textContent' && obj.nodeName != 'DIV')
    {
        obj = obj.parentNode;
    }

    if (obj.nodeName == 'HTML') return;
    if(obj.parentNode.getAttribute('class') != 'content' || obj.nodeName != 'DIV') {
        return;
    }
    curID = obj.name;
    var oldStyle = new Array();

    if(obj.parentNode.offsetWidth < 50 ) oldStyle['width']  = 40;
    else oldStyle['width']  = obj.parentNode.offsetWidth;

    if(obj.parentNode.parentNode.offsetHeight < 50 ) oldStyle['height']  = 40;
    else oldStyle['height']  = obj.parentNode.parentNode.offsetHeight;

    var editBox = document.createElement('DIV');
    editBox.id="editBox";

    var content = obj.innerHTML;
    var editAre = document.createElement('TEXTAREA');
    var parent = obj.parentNode;
    var toolbar = createToolbar();

    parent.insertBefore(editBox,obj);
    editBox.appendChild(editAre,obj);
    //editBox.insertBefore(toolbar,editAre);

    parent.removeChild(obj);

    editBox.style.width="100%";
    editBox.style.height=oldStyle['height']+"px";
    editBox.style.position="relative";

//    alert(oldStyle.toString);

    editAre.id = "editingArea";
    editAre.value = content;
    editAre.name=curID;

    editAre.style.width="100%";
    editAre.style.height="100%";
    editAre.style.position="absolute";

    editAre.focus();

    editing = true;
}

function saveEdit()
{
    var area = document.getElementById('editBox');
    var edit = document.getElementById('editingArea');
    var newD = document.createElement('DIV');
    var pare = area.parentNode;
    newD.innerHTML = edit.value;
    newD.className="content";
    newD.name=curID;
    pare.insertBefore(newD,area);
    // Poistetaan editointi hommelit
    //edit.removeChild(document.getElementById('toolbar'));
    //area.removeChild(edit);
    pare.removeChild(area);
    editing = false;
}

document.onclick = catchIt;
</script>
{/literal}