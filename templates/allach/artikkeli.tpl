          <a name="{$section}" class="anchor"></a>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tinted">
            <tr>
              <td background="{$style.images}/harmaa50.gif" height="1" colspan="2"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
            </tr>
            <tr>
              <td align="left">
                <strong>&nbsp;
                  {if isset( $info.links[$section])}
                    <a href="{$info.links[$section]}">{$section|escape:"html"}</a>
                  {else}
                    {$section}
                  {/if}
                </strong>
              </td>
              <td align="right">
                &nbsp;
                {if count( $content.cont ) > 3 }
                 <a href="{$cwd|escape:"url"}#top" title="siirry sivun yläreunaan">&nbsp;[ylös]&nbsp;</a>
                {/if}
              </td>
            </tr>
          </table>
          <div class="content">
          {if $sectCont.image}
            {include file="imagebox.tpl"}
          {/if}
          <div name="{$section|escape:"html"}">
          {$sectCont.text}
          </div>
          </div>
