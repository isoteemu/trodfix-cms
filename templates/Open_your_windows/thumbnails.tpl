<script type="text/javascript" src="{$style.images}/lightbox_plus.js"></script>
<script>
var imgPath = '{$style.images}/';
{literal}
addEvent(window,"load",function() {
	var lightbox = new LightBox({
	loadingimg:imgPath+'loading.gif',
	expandimg:imgPath+'windows_fullscreen.png',
	shrinkimg:imgPath+'windows_nofullscreen.png',
	effectimg:imgPath+'zzoop.gif',
	effectpos:{x:-40,y:-20},
	effectclass:'effectable',
	closeimg:imgPath+'close.gif'
	});
});
{/literal}
</script>
<div class="other">
	<div class="left">
{* Hax, laske ensin kaikkien koko yhteen *}
{assign var="_totalheight" value="0"}
{foreach from=$thumbs name=thumbs item=thumb}
	{math equation="x + y" x=$_totalheight y=$thumb.height assign=_totalheight}
{/foreach}

{assign var="_sep" value="0"}
{assign var="_currentheight" value="0"}

{foreach from=$thumbs name=thumbs item=thumb}

	{math equation="x + y" x=$_currentheight y=$thumb.height assign=_currentheight}

	{* Hieman kikkailua, mutta laiskuus... *}
	{capture assign=thumbimg}
		<div class="thumb">
			<a href="{$thumb.imageUrl}" rel="lightbox"><img src="{$thumb.url}" alt="{$thumb.name|escape:"htmlall"}" width="{$thumb.width}" height="{$thumb.height}" border="0" alt="{$thumb.desc|truncate:60|escape:"quotes"}" title="{$thumb.desc|escape:"quotes"}" /></a>
		</div>
	{/capture}

	{if $_currentheight < $_totalheight / 2}
		{$thumbimg}
	{else}
		{if $_sep == 0}
			{assign var="_sep" value="1"}
	</div>
	<div class="right">
		{/if}
		{$thumbimg}
	{/if}
{/foreach}
	</div>
</div>
