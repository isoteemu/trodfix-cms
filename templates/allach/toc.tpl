                    <script type="text/javascript">
                    {literal}
                    <!--
                    function showTocToggle(show,hide) {
                        if(document.getElementById) {
                            document.writeln('<span class=\'toctoggle\'><a href="javascript:toggleToc()" class="infoLinks">' +
                            ' <img src="{$style.images}/nuolipunvas.gif" width="10" height="10" valign="middle" border="0" alt="'+ show +'" id="showlink" style="display:none;" />' +
                            ' <img src="{$style.images}/nuolipun.gif" width="10" height="10" valign="middle" border="0" alt="'+ hide +'" id="hidelink" />' +
                            ' &nbsp;Sivun Aiheet&nbsp;&nbsp;&nbsp; '+
                            '</a></span>');
                        }
                    }

                    function toggleToc() {
                        var toc = document.getElementById('tocinside');
                        var showlink=document.getElementById('showlink');
                        var hidelink=document.getElementById('hidelink');
                        if(toc.style.display == 'none') {
                            toc.style.display = tocWas;
                            hidelink.style.display='';
                            showlink.style.display='none';

                        } else {
                            tocWas = toc.style.display;
                            toc.style.display = 'none';
                            hidelink.style.display='none';
                            showlink.style.display='';

                        }
                    }

                    //-->
                    {/literal}
                    </script>
                    <table border="0" id="toc" summary="Sisällys" cellpadding="0" cellspacing="0" width="200">
                      <thead id="toctitle">
                        <tr>
                           <td><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                           <td height="10" height="10" colspan="2"><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                           <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                           <td width="10" height="10"><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                        </tr>
                        <tr>
                          <td  width="10" height="1" ><img src="{$style.images}/spacer.gif" height="1" width="10" alt="" border="0" /></td>
                          <td colspan="4" bgcolor="#006da6" height="1"><img src="{$style.images}/harmaa50.gif" alt="" border="0" height="1" width="1" /></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                          <td nowrap><script type="text/javascript">showTocToggle();</script><noscript>Sivun aiheet</noscript></td>
                          <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                          <td>&nbsp;</td>
                        </tr>
                      </thead>
                      <tbody id="tocinside" class="content">
                        <tr>
                          <td>&nbsp;</td>
                          <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                          <td nowrap>
                            <ul>
                              {foreach from=$content.cont key=tocEntry item=null}
                              <li><a href="{$cwd}#{$tocEntry|escape:"url"}">{$tocEntry|escape:"html"}</a></li>
                              {/foreach}
                            </ul>
                          </td>
                          <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                          <td>&nbsp;</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="4" bgcolor="#006da6" height="1"><img src="{$style.images}/harmaa50.gif" alt="" border="0" height="1" width="1" /></td>
                          <td><img src="{$style.images}/spacer.gif" height="1" width="10" alt="" border="0" /></td>
                        </tr>
                        <tr>
                          <td height="10" height="10"><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                          <td bgcolor="#006da6" width="1"><img src="{$style.images}/harmaa50.gif" height="1" width="1" alt="" border="0" /></td>
                          <td width="10" height="10" colspan="2"><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                          <td><img src="{$style.images}/spacer.gif" height="10" width="10" alt="" border="0" /></td>
                        </tr>
                      </tfoot>
                    </table>
                    <br />