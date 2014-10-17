{* Smarty *}
<?xml version="1.0" encoding="{$header.charset}"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$header.lang}">
  <head>
    <title>{$header.org} :: {$header.title}</title>
    <base href="{$header.base}" />
    <meta http-equiv="Content-Language" content="{$header.lang}" />
    <meta http-equiv="Content-Type" content="text/html; charset={$header.charset}" />
{if $header.noindex}
    <meta name="robots" content="noindex,nofollow" />
    <meta name="htdig-noindex" />
{else}
    <meta name="robots" content="index,follow" />
    <meta name="revisit-after" content="14 days" />
    <meta name="description" content="{$header.description}" />
    <meta name="keywords" content="{$header.keywords}" />
{/if}
    <meta name="GENERATOR" content="Quanta Plus" />
    <meta name="author" content="{$header.author}" />
{if $header.msie == true or $header.css}
    <!-- compliance patch for microsoft browsers -->
    <style type="text/css">
    <!--
    {if $header.css}
    {$header.css}
    {/if}
    {if $header.msie == true}
    {literal}
    img {
        behavior: url("{$style.images}/pngbehavior.htc");
    }
    {/literal}
    {/if}
    // -->
    </style>
{/if}
{if $google}
    <link rel="search" title="Etsi sivustolta" href="{$cwd|escape:"html"}#searchbox" />
{/if}
    <link rel="top" title="{$header.org}" href="{$cwd}#top" />
    <link rel="stylesheet" type="text/css" href="css.php" />
  </head>
  <body bgcolor="#fafaff" text="#333333" background="{$style.images}/steelbg.png">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="170" height="75" align="center" class="smallTitle">
          <a href="{$header.uri}" class="smallTitle">{$header.section}</a>
          <br /><a href="{$header.pageuri}" class="smallTitle">{$header.page}</a>
        </td>
        <td height="75" width="1" background="{$style.images}/harmaa50.gif"><img src="{$style.images}/harmaa50.gif" width="1" height="1" alt="" /></td>
        {if $header.logo}
        <td height="75" align="left" valign="middle">
          <a href="{$header.base}" class="otsikko"><img src="{$header.logo}" alt="{$header.org}" align="left" border="0" /></a>
        </td>
        {/if}
        <td height="75" align="right">
          <h2>{$header.org}</h2>
        </td>
      </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="0" height="1" /></td>
      </tr>
    </table>
    <!-- INFO namespace -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="170" align="left" class="infoTable" valign="top">
          <p id="infoTable">&nbsp;</p>
          {if $info.content}
          <p id="infoTable">{$info.content}</p>
          <p><img src="{$style.images}/harmaa50.gif" width="100%" alt="" height="1" /></p>
          {/if}
          {foreach from=$info.links key=linkname item=link}
          <p>
            <a href="{$link}" class="infoLinks">&nbsp;<img src="{$style.images}/nuolipun.gif" width="10" height="10" border="0" alt="&#062;" />&nbsp;{$linkname}</a>
          </p>
          {/foreach}
          <p>
            <img src="{$style.images}/harmaa50.gif" width="100%" alt="" height="1" />
          </p>
          {if $info.prev }
          <ul id="backLinks">
            <li>
              <a class="infoLinks" href="{$info.prev[0].uri}">&nbsp;<img src="{$style.images}/nuolipunvas.gif" width="10" height="10" border="0" alt="&#060;" title="Edellisille sivuille" />&nbsp;Edelliselle Sivulle</a>
              {if count( $info.prev ) > 1}
              <ul>
                {foreach from=$info.prev item=back}
                <li class="infoLinks" title="Paluu {$back.title} sivuille"><a href="{$back.uri}">&nbsp;&nbsp;&nbsp;<img src="{$style.images}/nuolipunvas.gif" width="10" height="10" border="0" alt="&#060;" />&nbsp;{$back.title}</a></li>
                {/foreach}
              </ul>
              {/if}
            </li>
          </ul>
          {/if}
        </td>
        <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/spacer.gif" width="0" height="1" /></td>
        <td id="bread" valign="baseline">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td height="8" class="menuBorder"><img src="{$style.images}/spacer.gif" height="8" width="0" alt="" /></td>
            </tr>
            <tr>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" alt="" /></td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr valign="middle">
              {foreach from=$menu.links key=linkname item=link}
              <td nowrap>
               <a href="{$link}" class="infoLinks">&nbsp;<img src="{$style.images}/nuolipun.gif" width="10" height="10" valign="middle" border="0" alt="&#062;" />&nbsp;{$linkname}</a>
              </td>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
              {/foreach}
              <td nowrap width="18">
                  <a href="{$cwd}plus" class="infoLinks" style="padding:0px 0px 2px 0px; display:block; width:100%;" rel="nofollow"><div style="background : url('{$style.images}/plus.png') no-repeat center center;">&nbsp;</div></a>
              </td>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
              <td nowrap width="18">
                  <a href="{$cwd}minus" class="infoLinks" style="padding:0px 0px 2px 0px; display:block; width:100%;" rel="nofollow"><div style="background : url('{$style.images}/minus.png') no-repeat center center;">&nbsp;</div></a>
              </td>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
              <td nowrap width="18">
                  <a href="{$cwd}print" target="_blank" class="infoLinks" style="padding:0px 0px 2px 0px; display:block; width:100%" rel="nofollow"><div style="background : url('{$style.images}/print.png') no-repeat center center;">&nbsp;</div></a>
              </td>
            </tr>
          </table>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
            </tr>
            <tr>
              <td height="8" class="menuBorder"><img src="{$style.images}/spacer.gif" height="8" width="0"></td>
            </tr>
            <tr>
              <td background="{$style.images}/harmaa50.gif" width="1"><img src="{$style.images}/spacer.gif" width="0" height="1" /></td>
            </tr>
          </table>
          <table align="left" border="0" cellpadding="2" cellspacing="2" width="100%">
            <tr>
              <td width="10">&nbsp;</td>
              <td valign="top" width="100%">
                <a name="top"></a>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                {if count( $content.cont ) > 2 }
                  <tr>
                    <td>{include file="toc.tpl"}</td>
                  </tr>
                {/if}
                <!-- preContent -->
                {if $precontent != ""}
                  <tr>
                    <td valign="middle">
                      {$precontent}
                    </td>
                  </tr>
                {/if}
                <!-- Sisältö osio -->
                {foreach from=$content.cont key=section item=sectCont}
                  <tr>
                    <td valign="middle">
                      {include file="artikkeli.tpl"}
                    </td>
                  </tr>
                {/foreach}
                {if $google}
                  {if $google.showresults}
                  <tr>
                    <td>
                      {include file="haku-tulos.tpl"}
                    </td>
                  </tr>
                  {/if}
                  <tr>
                    <td>
                      {include file="haku.tpl"}
                    </td>
                  </tr>
                {/if}
                </table>
              </td>
              {if $content.ads}
              <td>&nbsp;</td>
              <td valign="top">
              {foreach from=$content.ads item=ad}
                {$ad}
              {/foreach}
              </td>
              {/if}
            </tr>
          </table>
        </td>
      </tr>
    </table>
{if $footnotes}
    {foreach from=$footnotes item=note}
        {$note}
    {/foreach}
{/if}
  </body>
</html>
