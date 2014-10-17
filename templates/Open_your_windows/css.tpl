/*
	author: Luka Cvrk (www.solucija.com)
	project: Open Source Web Design contest, Theme: Fall (Autumn)
*/

/* {START CSS BARBECUE} */

body {
	margin: 0px;
	padding: 0px;
	font-size: 70%;
	font-family: "Arial", Tahoma, Sans-Serif;
	background: #353F49;
	color: #000;
}

/* LINKS */
a {
	color: #768C00;
	text-decoration: none;
	background-color: inherit;
}

a:hover {
	color: #000;
	background-color: inherit;
}


/* HEADINGS */
h1 {
	padding: 0px 0px 22px 0px;
	font-size: 1.4em;
}

h2 {
	font-size: 1.2em;
	margin: 0px;
}

h3 {
	width: 100%;
	font-size: 1.5em;
	color: #404240;
	font-weight: bold;
	letter-spacing: -1px;
	line-height: 1.5em;
	padding: 0px 0px 0px 30px;
	background-color: inherit;
	background-image: url("/*{$style.images}*//titlebg.gif");
	background-repeat: no-repeat;
	background-position: center left;
}

.red {
	color: #8B1714;
	background-color: inherit;
}

/* PARAGRAPH */
p {
	font-size: 1em;
	color: #353F49;
	line-height:1.6em;
	margin: 0px 0px 5px 0px;
	padding: 0px;
	background-color: inherit;
}


/*-------------------------
DIVS IN ORDER OF APPEARANCE
-------------------------*/

/* WRAP, HOLDS EVERYTHING TOGETHER */
#wrap {
	margin: 0px auto;
	padding: 0px;
	width: 691px;

}

#container {
	float: left;
	margin: 0px;
	padding: 0px;
	width: 780px;
	background: url("/*{$style.images}*//middle.gif") repeat-y top left;
}


#top {
	width: 700px;
	height: 25px;
	margin: 0px;
	padding: 0px;
	background: url("/*{$style.images}*//top.gif") no-repeat top left;
}

#header {
	margin: 0px;
	padding: 55px 0 0 100px;
	background-color: inherit;
	background-image: url("/*{$style.images}*//header.gif");
	background-repeat: no-repeat;
	height: 70px;
	color: #919FAE;
	font-weight: bold;
	font-size: 1.3em;
}


/* HORIZONTAL MENU */
#hmenu {
	margin: 0 96px 0 15px;
	padding: 10px 0 20px 0;
	background: #ffffff url("/*{$style.images}*//hmenu.gif") repeat-x top left;
	color: #808080;
}

#hmenu a {
	color: #74879A;
	margin: 0px 3px 0px 8px;
	padding: 0 0px 0 9px;
	background-color: transparent;
	background-image: url("/*{$style.images}*//arrow.gif");
	background-repeat: no-repeat;
	background-position: center left;

}

#hmenu a:hover {
	color: #F0F2F4;
	background-color: transparent;
}

/* LEFT COLUMN */
#left_column {
	float: left;
	margin: 0px 0px 0px 18px;
	width: 160px;
}

#left_column p {
	color: #828482;
	padding: 7px;
	margin: 0px;
	background-color: inherit;
}


/* MAIN MENU (LEFT) */
#menu {
}

#menu a {
	display: block;
	line-height: 20px;
	padding: 0px 0px 0px 4px;
	color: #353F49;
	background: #FFFFFF;
}

#menu a:hover {
	background: #353F49;
	color: #FFF;
}

/* LINK TITLE - visible on hover */
.underline, .accesskey {
	border-bottom: 1px dotted #74879A;
}

.white {
	color: #FFF;
	font-size: 0.8em;
	background-color: inherit;
	background: url("/*{$style.images}*//menudivider.gif");
	background-repeat: no-repeat;
	background-position: center left;
	padding: 0px 0px 0px 8px;
}


#right_column {
	float: left;
	width: 505px;
	margin: 0px 15px 0px 0px;
	padding: 0px;
 }

/* MAIN ARTICLES */
.main_article {
	margin: 0px 0px 2px 0px;
	padding: 0px 30px 8px 0px;
}

.main_article h3, .main_article h3 a {
	color: #404240;
	display:block;
}

.main_article p {
	padding: 3px 8px 0px 4px;
}

/* THE FOLLOWING SHORT ARTICLES */
.other {
	margin: 0px 0px 0px 0px;
}

.other p {
	padding: 5px;
	color: #808080;
	background-color: inherit;
}

/* LEFT SHORT ARTICLE */
.left {

	width: 44%;
	float: left;
	background: #eee url("/*{$style.images}*//greybg.gif") repeat-x top left;
	padding: 5px;
	color: #808080;
}

/* RIGHT SHORT ARTICLE */
.right {
	float: left;
	width: 44%;
	background: #FDF7DF url("/*{$style.images}*//yellowbg.gif") repeat-x top left;
	border-left: 2px solid #FFF;
	padding: 5px;
	color: #808080;
}

.thumb {
	text-align:center;
	display:block;
	padding:5px;
}

/* FOOTER */

#footer {
	float: left;
	margin: 0px 0px 20px 0px;
	padding: 15px 0px 0px 0px;
	width: 691px;
	background-color: inherit;
	background-image: url("/*{$style.images}*//bottom.gif");
	background-repeat: no-repeat;
	text-align: center;
	color: #919FAE;
}

#footer a {
	color: #BDC6CE;
	background-color: inherit;
	border-bottom: 1px dotted #919FAE;
}

/* LIGHTBOX PLUS */

#lightbox {
	margin:0px;
	padding:10px;
	background-color: #eee;
	border-bottom: 1px solid #666;
	border-right: 1px solid #666;
	-moz-border-radius: 0.3em;  /* Mozilla */
    border-radius: 0.3em;       /* CSS 3 */
}

#lightboxCaption {
	padding:0px;
	color: #333;
	background-color: #eee;
	font-size: 90%;
	text-align: center;
	border-bottom: 1px solid #666;
	border-right: 1px solid #666;
}

#lightbox img{ border: none; clear: both;}
#overlay img{ border: none; }

#overlay {
	background-image: url('/*{$style.images}*//overlay.png');
}

* html #overlay{
	background-color: #333;
	back\ground-color: transparent;
	background-image: url(blank.gif);
	filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src="/*{$style.images}*//overlay.png", sizingMethod="scale");
	}


/* {END BARBECUE} */
