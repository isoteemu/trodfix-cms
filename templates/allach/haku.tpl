{* Smarty *}
          <br />
          <table width="100%" border="0" cellspacing="0" cellpadding="0" id="gbox">
            <tr>
              <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
            </tr>
            <tr>
              <td align="center">
                {if $header.msie == "true" }
                <!-- Hover image preload -->
                <script language="JavaScript">
                <!--
                    image1 = new Image();
                    image1.src = "{$style.images}/noIE-normal.jpg";
                // -->
                </script>
                <a href="http://www.google.com/search?q=why+not+to+use+internet+explorer" type="text/html" title="If you were looking at this in any browser but Microsoft Internet Explorer, it would look and run better and faster." onmouseover="document.noIE.src='{$style.images}/noIE-normal.jpg'" onmouseout="document.noIE.src='{$style.images}/noIE.jpg'"><img src="{$style.images}/noIE.jpg" alt="no IE" align="left" border="0" style="float : left; left : 0px; top : 0px; position: relative;" name="noIE" width="55" height="64" /></a>
                {/if}
                {if $style.sfx != "false"}
                <!-- Hover image preload -->
                <script language="JavaScript">
                <!--
                    image1 = new Image();
                    image1.src = "{$style.images}/ff-normal.jpg";
                // -->
                </script>
                <a href="http://www.spreadfirefox.com/?q=affiliates&id=9387&t=68" type="text/html" title="Rediscover the web - Get FireFox!" onmouseover="document.FireFox.src='{$style.images}/ff-normal.jpg'" onmouseout="document.FireFox.src='{$style.images}/ff.jpg'"><img src="{$style.images}/ff.jpg" alt="Get Firefox" width="50" height="67" align="right" border="0" style="float : right; right : 0px; top : 0px; position: relative;" name="FireFox" /></a>
                {/if}
                <p align="center"  style="margin : 15px 0 0; padding:0px;">
                <form name="haku" action="{$google.section}" method="POST">
                  <a name="searchbox"></a>
                  <input type="text" size="30" maxlength="60" name="q" value="{$google.haku|escape:"html"}" onFocus="if(this.value=='{$google.haku|escape:"html"}')this.value='';" />&nbsp;<input type="submit" name="hae" value="hae" />
                </form>
                </p>
                <p align="center" style="padding : 0px; margin : 0 0 15px;">
                 <font color="#006da6">[</font>{foreach name=glinks from=$menu.links key=linkname item=link}<a href="{$link}">&nbsp;{$linkname}&nbsp;</a>{if $smarty.foreach.glinks.last != true}<font color="#006da6">|</font>{/if}{/foreach}<font color="#006da6">]</font>
                </p>
              </td>
            </tr>
            <tr>
              <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
            </tr>
          </table>
          <p>&nbsp;</p>
