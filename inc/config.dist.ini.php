;<?php die(); ?>
;
;/***************************************************************************
;        config.ini.php  -  Setup file
;           -------------------
;    begin                : Sat Jul 10 2004
;    copyright            : (C) 2004 by Teemu A
;    email                : teemu@terrasolid.fi
; ***************************************************************************/
;
;/***************************************************************************
; *                                                                         *
; *   This program is free software; you can redistribute it and/or modify  *
; *   it under the terms of the GNU General Public License as published by  *
; *   the Free Software Foundation; either version 2 of the License, or     *
; *   (at your option) any later version.                                   *
; *                                                                         *
; ***************************************************************************/
; $Id: config.dist.ini.php,v 1.1.2.3 2004/08/25 18:15:39 teemu Exp $


;; Sivukohtaiset asetukset
;; N‰it‰ l‰hinn‰ tarvitsee asettaa per sivu.
[Sivukohtaiset]
otsikko = "Alkemisti"
hakusanat = ""
kuvaus = ""
author = "Teemu A"

;; Sivuston hienos‰‰tˆasetukset
[Site]
; Mik‰ sivu toimii etusivuna
frontpage = "Etusivu"
; Sivujen relatiivinen sijainti
site = "sivut"
; Sessiotunnistin. Tarvitsee muuttaa vain, jos samalla sivulla on
; useampi PHP sovellus.
identifier = "alkemisti"
; MSIE kiertotiet.
msiefixes = true
; Teema
theme = allach
; (posix) Locale
;locale = "fi_FI@euro"
locale = "fi_FI.utf8"

; Encoding
;encoding = "ISO-8859-15"
encoding = "UTF-8";

; Thumbnail height and width
thumbHeight = 120
thumbWidth  = 150

;; Style parametrit syˆtet‰‰n suoraan
[style]
; Spread Firefox logo
sfx = true


[System]
; Salasana tiedostona toimivan tiedoston sijainti tiedostoj‰rjestelm‰ss‰.
passwd = "inc/passwd"

; Mit‰ URL j‰rjestelm‰‰ k‰ytet‰‰n. Vaihtoedot rewrite, multiviews & get.
; rewrite:    http://www.example.com/section/page
;             Vaatii mod_rewrite m‰‰rittelyn.
; multiviews: http://www.example.com/index.php/section/page
;             Vaatii Apachen MultiViews option.
; get:        http://www.example.com/index.php?path=section/page
;             Ei vaadi mit‰‰n erikoisj‰rjestelyj‰.
uristyle = rewrite
; Enable backward compatibility?
backwardCompatibility = false

; Directory that contains help documents.
; Works as Site/site setting
helpdir = "docs"

; Which dir works as temporary dir? Needed in safe-mode
temp = "templates/compile"

; Memory limit. Sometimes we need more than we are given.
memory_limit = "16M"

; Use gzip encoding for page. Good for bandwith, bad for cpu.
gzip = "false"

;;
;; List of modules, which are enabled
;;
[Modules]



[Search]
;; K‰ytet‰‰nkˆ hakua.
use = true
cgi = "http://rautakuu.org/cgi-bin/htsearch"
config = "htdig"
section = "Haku"

[@ADMIN]
adminInterface = true

[Smarty]
;; (bool) K‰ytet‰‰nkˆ smartyn cache ominaisuutta?
cache = false
; Cachen hakemisto. Pit‰‰ olla rw.
compile_dir = "templates/compile/"
;; Kuinka kauan ennen kuin cache p‰ivitet‰‰n (sekunteina)
;; Oletus 300 sekunttia.
cache_lifetime = 300
cache_dir = "templates/cache/"

;;
;; Tidy config
;; Tidy is used, if can be used, for cleaning up HTML code from
;; user generated webpages. Look for setting from:
;; http://tidy.sourceforge.net/docs/quickref.html
[Tidy]
show-body-only=true
force-output=true
tidy-mark=false
wrap=0
wrap-attributes=false
literal-attributes=false
output-xhtml=true
numeric-entities=true
enclose-text=true
enclose-block-text=true
quiet=true
quote-nbsp=true
fix-backslash=false
fix-uri=false
