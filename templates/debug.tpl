{* Smarty *}
<script language="JavaScript">
<!--
    _trace_console = window.open("","Trace console","toolbar=no,scrollbars,width=750,height=120,dependent");
    _trace_console.document.write("<html><head><title>Trace Console</title>");

    {literal}
    _trace_console.document.write("<script language=\"JavaScript\">");
    _trace_console.document.write("function toggleTrace(id) {");
    _trace_console.document.write("  var toc = document.getElementById('trace' + id);");
    _trace_console.document.write("  if(toc.style.display == 'none') {");
    _trace_console.document.write("    toc.style.display = 'block';");
    _trace_console.document.write("  } else {");
    _trace_console.document.write("    toc.style.display = 'none';");
    _trace_console.document.write("  }");
    _trace_console.document.write("}");
    _trace_console.document.write("</scr"+"ipt>");
    {/literal}

    _trace_console.document.write("</head><body>");
    _trace_console.document.write("<div style=\"font : 9px 'Courier 10 Pitch', Courier, 'Courier New';\">");
    {foreach from=$_debugmsgs key=id item=_dmsg}
    _trace_console.document.write("<a href=\"javascript:toggleTrace({$id});\" style=\"background-color:#F5F5F5; border : dashed 1px; border-color:#D7D7D7; text-decoration: none; display: block;\">");
    _trace_console.document.write("<font color=\"Gray\">{$_dmsg.time|truncate:4:""|escape:"html"}</font> - ");
    _trace_console.document.write("<font color=\"#004BB4\" title=\"{$_dmsg.realfile|escape:"html"}\">{$_dmsg.file|escape:"javascript"}</font>");
    _trace_console.document.write("<font color=\"Gray\">[{$_dmsg.line|escape:"javascript"}]: </font>");
    _trace_console.document.write("<font color=\"Green\" title=\"{$_dmsg.time|escape:"html"}\"> {$_dmsg.caller|escape:"javascript"}</font>");
    _trace_console.document.write("<font color=\"Black\"> {$_dmsg.message|escape:"javascript"}</font>");
    _trace_console.document.write("<br />");
    _trace_console.document.write("<div id=\"trace{$id}\" style=\"display:none; margin-left:20px; margin-top:4px; margin-bottom:4px;\">");
    {if $_dmsg.stack}
        {foreach from=$_dmsg.stack item=_trace}
        _trace_console.document.write("<font color=\"#004BB4\" title=\"{$_trace.realfile|escape:"html"}\">{$_trace.file|escape:"javascript"}</font>");
        _trace_console.document.write("<font color=\"Gray\">[{$_trace.line|escape:"javascript"}]: </font>");
        _trace_console.document.write("<font color=\"Green\"> {$_trace.caller|escape:"javascript"}</font>");
        _trace_console.document.write("<br />");
        {/foreach}
    {/if}
    _trace_console.document.write("</a>");
    _trace_console.document.write("</div>");
    {/foreach}
    _trace_console.document.write("</div>");
    _trace_console.document.write("</body></html>");
    _trace_console.document.close();
// -->
</script>
