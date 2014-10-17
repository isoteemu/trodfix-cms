{* Smarty *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- $Id: printview.tpl,v 1.1.2.3 2004/08/03 19:32:34 teemu Exp $ -->
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$header.lang}">
  <head>
    <title>{$header.org} :: {$header.title} :: Print</title>
    <base href="{$header.base}" />
    <meta http-equiv="Content-Language" content="{$header.lang}" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
    <link rel="stylesheet" type="text/css" href="css.php?print" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="htdig-noindex" />
    <meta name="GENERATOR" content="Quanta Plus" />
    <meta name="description" content="{$header.description}" />
    <meta name="keywords" content="{$header.keywords}" />
    <meta name="author" content="{$header.author}" />
  </head>
  <body background="#ffffff" vlink="Black" alink="Black" link="Black">
    <script type="text/javascript" language="javascript1.2">
    <!--
    {literal}
    // Do print the page
    if (typeof(window.print) != 'undefined') {
        window.print();
    }
    {/literal}
    //-->
    </script>
    {foreach from=$content.cont key=section item=sect}
    <table border="0">
      <tr>
        <td><h2>{$section}</h2></td>
      </tr>
      <tr>
        <td valign="middle" class="content">
          {$sect.text}
          <span class="imagebox">
            {$sect.image}
          </span>
        </td>
      </tr>
    </table>
    {/foreach}
  </body>
</html>