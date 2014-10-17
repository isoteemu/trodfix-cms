{* kate: space-indent false; encoding utf-8; indent-width 4; *}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="{$header.lang}">
<head>
	<title>{$header.org} &#187; {$header.title}</title>
	<base href="{$header.base}" />
	<meta http-equiv="Content-Language" content="{$header.lang}" />
	<meta http-equiv="Content-Type" content="text/html; charset={$header.charset}" />
{if $header.noindex}
	<meta name="robots" content="noindex,nofollow" />
	<meta name="htdig-noindex" />
{else}
	<meta name="robots" content="index,follow" />
	<meta name="description" content="{$header.description}" />
	<meta name="keywords" content="{$header.keywords}" />
{/if}
	<meta name="author" content="{$header.author}, Teemu A (teemu@rautakuu.org) @ Trodfix Oy, Template by Luka Cvrk (www.solucija.com)" />
	<link href="css.php" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="wrap">
		<div id="container">
			<div id="header">
				<p>{$header.org}</p>
			</div>

			<div id="hmenu">
				{foreach from=$menu.links key=linkname item=link}
				 <a href="{$link}" {$linkname|ack}>{$linkname|ackv}</a>
				{/foreach}
    		</div>

      		<div id="left_column">

    			<div id="menu">
					{foreach from=$info.links key=linkname item=link}
					<a href="{$link}" {$linkname|upper|ack}>{$linkname|upper|ackv}</a>
					{/foreach}
				</div>
				{if $info.content}
					{$info.content}
				{/if}
				&nbsp;
			</div>

			<div id="right_column">
				<div class="main_article">
					{foreach from=$content.cont key=section item=sectCont}
						{if isset( $info.links[$section])}
							<h3><a href="{$info.links[$section]}">{$section|escape:"html"}</a></h3>
						{else}
							<h3>{$section}</h3>
						{/if}
						{$sectCont.text}
					{/foreach}
      			</div>
                <!-- preContent -->
				{if $precontent != ""}
					{$precontent}
				{/if}
                <!-- /preContent -->
    	</div>
    </div>
	<div id="footer">
		{if $foot}
			{$foot}
		{else}
			&copy; 2005, <a href="{$header.base}">{$header.org}</a>, Design: <a href="http://trodfix.jsp.fi">Trodfix Oy</a> and <a href="http://www.solucija.com/">Luka Cvrk</a>
		{/if}
	</div>
</div>
</body>
</html>