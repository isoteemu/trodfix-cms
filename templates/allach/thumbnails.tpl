                      {if $thumbimage}
                      <div id="imgviewer">
                        <a name="imgviewer"></a>
                        <div id="toolbar" style="border-bottom-color : #006DA6; border-bottom-style : solid; border-bottom-width : 1px; border-right-color : #006DA6; border-right-style : solid; border-right-width : 1px; position : absolute; filter:alpha(opacity=90); opacity: 0.9; -moz-opacity:0.9;" class="tinted">
                          <table border="0" cellpadding="0" heigth="34" cellspacing="0" style="position:static; vertical-align:middle;">
                            <tr>
                              <td>
                                <a href="{$thumbimage.prev.viewUrl}#imgviewer" class="infoLinks" title="Listan edellinen kuva" onmouseover="this.T_WIDTH={$thumbimage.prev.width}+4;this.T_HEIGHT={$thumbimage.prev.heigth}+4;return escape('<img src=\'{$thumbimage.prev.url}\' heigth=\'{$thumbimage.prev.height}\' width=\'{$thumbimage.prev.width}\' /><br />{$thumbimage.prev.name|escape:"htmlall"}')"><img src="{$style.images}/leftarrow.png" border="0" hspace="5" height="32" width="32" align="center" alt="edellinen"  /></a>
                              </td>
                              <td>
                                <a href="{$thumbimage.next.viewUrl}#imgviewer" class="infoLinks" title="Listan seuraava kuva" onmouseover="this.T_WIDTH={$thumbimage.next.width}+4;this.T_HEIGHT={$thumbimage.next.heigth}+4;return escape('<img src=\'{$thumbimage.next.url}\' heigth=\'{$thumbimage.next.height}\' width=\'{$thumbimage.next.width}\' /><br />{$thumbimage.prev.name|escape:"htmlall"}')"><img src="{$style.images}/rightarrow.png" border="0" hspace="5" height="32" width="32" align="center" alt="seuraava" /></a>
                              </td>
                              <td>
                                <a href="{$cwd}#{$thumbimage.name|escape:"html"}" class="infoLinks" title=" kuva"><img src="{$style.images}/window_nofullscreen.png" border="0" hspace="5" height="32" width="32" align="center" alt="" /></a>
                              </td>
                              <td width="15">
                                <img src="{$style.images}/spacer.gif" width="15" height="32" align="left" alt="" />
                              </td>
                              <td>
                                <strong style="overflow: hidden;white-space: nowrap;">{$thumbimage.name|escape:"html"}</strong><br  />
                                {$thumbimage.desc|default:"(Ei kommenttia)"}
                              </td>
                              <td width="10">
                                <img src="{$style.images}/spacer.gif" width="10" height="32" align="left" alt="" />
                              </td>
                            </tr>
                          </table>
                        </div>
                        <div id="imgviewercontainer"><img src="{$thumbimage.url}" height="{$thumbimage.height}" width="{$thumbimage.width}" alt="{$thumbimage.desc|escape:"htmlall"}" style="border-bottom-color : #006DA6; border-bottom-style : solid; border-bottom-width : 1px; border-left-color : #006DA6; border-left-style : solid; border-left-width : 1px; border-right-color : #006DA6; border-right-style : solid; border-right-width : 1px;; border-top-color : #006DA6; border-top-style : solid; border-top-width : 1px;" /></div>
                      </div>
                      {/if}
                      <table cellpadding="0" cellspacing="10" border="0" align="center">
                        {foreach from=$thumbs name=thumbs item=thumb}
                          {if $smarty.foreach.thumbs.iteration % 4 == 1}
                            <tr>
                          {/if}
                             <td valign="top" align="center">
                                <a name="{$thumb.name}"></a>
                                <table width="170" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
                                    <tr>
                                      <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
                                    </tr>
                                    <tr>
                                      <td align="left" style="word-wrap:break-word; overflow:hidden; display: block;" title="{$thumb.name|escape:"html"}" class="tinted">
                                        <a href="{$thumb.viewUrl}#imgviewer" class="infoLinks">&nbsp;{$thumb.name|escape:"html"}</a>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td align="center" valign="middle" style="background-image : url('{$style.images}/image.png'); background-position : center center; background-repeat : no-repeat; float : center;">
                                        <a href="{$thumb.viewUrl}#imgviewer" onmouseover="this.T_WIDTH={$thumb.width}+6;this.T_HEIGHT={$thumb.heigth}+6;return escape('<img src=\'{$thumb.url}\' heigth=\'{$thumb.height}\' width=\'{$thumb.width}\' /><br />{$thumb.name|escape:"htmlall"}')"><img src="{$style.images}/spacer.gif" alt="{$thumb.name|escape:"htmlall"}" width="150" height="120" border="0" style="background-image : url('{$thumb.url}'); background-position : center center; background-repeat : no-repeat; border-left-color : #006DA6; border-left-style : solid; border-left-width : 1px;; border-right-color : #006DA6; border-right-style : solid; border-right-width : 1px;; float : center;" /></a>
                                      </td>
                                    </tr>
                                    {if $thumb.desc}
                                    <tr>
                                      <td align="left" style="padding:10px;">
                                        {$thumb.desc|truncate:60}
                                      </td>
                                    </tr>
                                    <tr>
                                      <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
                                    </tr>
                                    {/if}
                                    <tr>
                                      <td align="right" class="tinted" style="text-align:right; padding-right:10px;" height="16">
                                        <table border="0" cellpadding="0" cellspacing="0" align="right">
                                          <tr>
                                            <td><a href="{$thumb.imageUrl}" class="infoLinks"><img src="{$style.images}/download.png" alt="download" width="16" height="16" border="0" title="Katso kuva" /></a></td>
                                            <td><a href="{$thumb.viewUrl}#imgviewer" class="infoLinks"><img src="{$style.images}/demo.png" alt="view" width="16" height="16" border="0" title="Katsele koko kuva" /></a></td>
                                          </tr>
                                        </table></td>
                                    </tr>
                                    <tr>
                                      <td background="{$style.images}/harmaa50.gif" height="1"><img src="{$style.images}/harmaa50.gif" width="1" height="1" /></td>
                                    </tr>
                                </table>
                            </td>
                          {if $smarty.foreach.thumb.iteration % 4 == 1}
                            </tr>
                          {/if}
                        {/foreach}
                      </table>
                      <script language="JavaScript" type="text/javascript" src="{$style.path}/wz_tooltip.js"></script>
