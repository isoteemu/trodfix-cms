{* Smarty *}
          <br />
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tinted">
            <tr>
              <td background="images/harmaa50.gif" height="1"><img src="images/harmaa50.gif" width="1" height="1" /></td>
            </tr>
            <tr>
              <td align="right">
                Tulokset <b>{$google.firstdisplayed}</b> - <b>{$google.lastdisplayed}</b> noin <b>{$google.matches}</b> osuman joukosta haulla <b>{$google.haku|escape:"html"}</b>
              </td>
            </tr>
          </table>
          <br />
          {if count( $google.results ) > 0}
            {foreach from=$google.results item=result}
                <dl>
                  <dt class="result"><a href="{$result.url}" class="external"><strong>{$result.title}</strong></a></dt>
                  <dd>{$result.excerpt}</dd>
                  <dd><a href="{$result.url}"><font color="Gray">{$result.url}</font></a><font color="Gray"> - {$result.size} - {$result.modified}</font></dd>
                </dl>
            {/foreach}
          {else}
             Haullasi - <b>{$google.haku|escape:"html"}</b> - ei löytynyt yhtään vastaavaa sivua.<br />
               <br />
               <dl>
                 <df>Suositeltavaa:</df>
                 <dd>- Varmista, että sanat on kirjoitettu oikein.</dd>
                 <dd>- Kokeile hieman muunnetuilla hakusanoilla.</dd>
               </dl>
          {/if}
