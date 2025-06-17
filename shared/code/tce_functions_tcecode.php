<?php

//============================================================+
// File name   : tce_functions_tcecode.php
// Begin       : 2002-01-09
// Last Update : 2025-06-13
//
// Description : Functions to translate TCExam code into XHTML.
//               The TCExam code is compatible to the common BBCode.
//               Supports LaTeX and MathML.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2025 Nicola Asuni - Tecnick.com LTD
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions to translate TCExam proprietary code into XHTML.
 * The TCExam code is compatible to the common BBCode.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2002-01-09
 */

/**
 * Returns XHTML code from text marked-up with TCExam Code Tags
 * @param $text_to_decode (string) text to convert
 * @return string XHTML code
 */
function F_decode_tcecode($text_to_decode)
{
    require_once('../config/tce_config.php');
    global $l, $db;

    // Patterns and replacements
    $pattern = [];
    $replacement = [];
    $i = 0;

    if (empty($text_to_decode)) {
        return '';
    }

    // escape some special HTML characters
    $newtext = htmlspecialchars($text_to_decode ?? '', ENT_QUOTES, $l['a_meta_charset']);

    $newtext = F_bbcode_to_tcecode($newtext);

    // [tex]LaTeX_code[/tex]
    $newtext = preg_replace_callback("#\[tex\](.*?)\[/tex\]#si", 'F_latex_callback', $newtext);

    // [mathml]MathML_code[/mathml]
    $newtext = preg_replace_callback("#\[mathml\](.*?)\[/mathml\]#si", 'F_mathml_callback', $newtext);

    // [object]object_url[/object:width:height:alt]
    $newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\:(.*?)\:(.*?)\:(.*?)\]#si", 'F_objects_callback', $newtext);
    // [object]object_url[/object:width:height]
    $newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\:(.*?)\:(.*?)\]#si", 'F_objects_callback', $newtext);
    // [object]object_url[/object]
    $newtext = preg_replace_callback("#\[object\](.*?)\.(.*?)\[/object\]#si", 'F_objects_callback', $newtext);

    while (preg_match("'\[code\](.*?) (.*?)\[/code\]'si", $newtext)) {
        $newtext = preg_replace("'\[code\](.*?) (.*?)\[/code\]'si", "[code]\\1&nbsp;\\2[/code]", $newtext);
    }

    $newtext = F_tcecode_url($newtext);
    $newtext = F_tcecode_tag($newtext);
    $newtext = F_tcecode_tag_arg($newtext);

    if (empty($newtext)) {
        return '';
    }

    // Convert multiple spaces to &nbsp; to support indentation.
    preg_match_all("#[ ]{2,}#", $newtext, $matches);
    if (isset($matches[0])) {
        foreach($matches[0] as $match) {
            $pos = strpos($newtext, $match);
            if ($pos !== false) {
                $len = strlen($match);
                $newtext = substr_replace($newtext, str_repeat('&nbsp;', $len), $pos, $len);
            }
        }
    }

    // line breaks
    $newtext = preg_replace("'(\r\n|\n|\r)'", '<br />', $newtext);
    $newtext = str_replace('<br /><li', '<li', $newtext);
    $newtext = str_replace('</li><br />', '</li>', $newtext);
    $newtext = (str_replace('<br /><param', '<param', $newtext));

    return $newtext;
}

// ============================================================

/**
 * Convert some BBCode-style to TCECode.
 * @param mixed $text
 * @return string
 */
function F_bbcode_to_tcecode($text) {
    // [*]list item - convert to new [li] tag
    $text = preg_replace("'\[\*\](.*?)\n'i", "[li]\\1[/li]", $text);
    // [img]image[/img] - convert to new object tag
    $text = preg_replace("'\[img\](.*?)\[/img\]'si", "[object]\\1[/object]", $text);
    // [img=WIDTHxHEIGHT]image[/img] - convert to new object tag
    $text = preg_replace("'\[img=(.*?)x(.*?)\](.*?)\[/img\]'si", "[object]\\3[/object:\\1:\\2]", $text);
    return $text;
}

/**
 * Convert [url]...[/url] and [url=...]...[/url] to HTML anchor tags.
 * @param mixed $text
 * @return string
 */
function F_tcecode_url($text) {
    if (empty($text)) {
        return '';
    }
    $text = preg_replace_callback(
        '#\[url\](.*?)\[/url\]#si',
        function ($matches) {
            $url = $matches[1];
            // Optionally validate URL
            if (!preg_match('/^https?:\/\//i', $url)
               || !filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
            return '<a class="tcecode" href="' . $url . '" rel="noopener noreferrer" target="_blank">' . $url . '</a>';
        },
        $text
    );
    return preg_replace_callback(
        '#\[url=(.*?)\](.*?)\[/url\]#si',
        function ($matches) {
            $url = $matches[1];
            $label = $matches[2];
            if (!preg_match('/^https?:\/\//i', $url)
               || !filter_var($url, FILTER_VALIDATE_URL)) {
                return $label;
            }
            return '<a class="tcecode" href="' . $url . '" rel="noopener noreferrer" target="_blank">' . $label . '</a>';
        },
        $text
    );
}

/**
 * Convert TCECode simple tags to XHTML tags.
 * @param mixed $text
 * @return string
 */
function F_tcecode_tag($text) {
    // Patterns and replacements
    $tag = [
        '#\[dir=ltr\](.*?)\[/dir\]#si' => '<span dir="ltr">\1</span>',
        '#\[dir=rtl\](.*?)\[/dir\]#si' => '<span dir="rtl">\1</span>',
        "#\[small\](.*?)\[/small\]#si" => '<small class="tcecode">\1</small>',
        "#\[b\](.*?)\[/b\]#si" => '<strong class="tcecode">\1</strong>',
        "#\[i\](.*?)\[/i\]#si" => '<em class="tcecode">\1</em>',
        "#\[s\](.*?)\[/s\]#si" => '<span style="text-decoration:line-through;">\1</span>',
        "#\[u\](.*?)\[/u\]#si" => '<span style="text-decoration:underline;">\1</span>',
        "#\[o\](.*?)\[/o\]#si" => '<span style="text-decoration:overline;">\1</span>',
        "#\[sub\](.*?)\[/sub\]#si" => '<sub class="tcecode">\1</sub>',
        "#\[sup\](.*?)\[/sup\]#si" => '<sup class="tcecode">\1</sup>',
        "#\[ulist\](.*?)\[/ulist\]#si" => '<ul class="tcecode">\1</ul>',
        "#\[olist\](.*?)\[/olist\]#si" => '<ol class="tcecode">\1</ol>',
        "#\[olist=1\](.*?)\[/olist\]#si" => '<ol class="tcecode" style="list-style-type:arabic-numbers">\1</ol>',
        "#\[olist=a\](.*?)\[/olist\]#si" => '<ol class="tcecode" style="list-style-type:lower-alpha">\1</ol>',
        "#\[li\](.*?)\[/li\]#si" => '<li class="tcecode">\1</li>',
        "#\[code\](.*?)\[/code\]#si" => '<div class="tcecodepre">\1</div>',
    ];

    foreach ($tag as $pattern => $replacement) {
        if (empty($text)) {
            break;
        }
        $text = preg_replace_callback(
            $pattern,
            fn($matches) => str_replace('\1', $matches[1], $replacement),
            $text
        );
    }

    return $text;
}

/**
 * Convert TCECode tags with arguments to XHTML tags.
 * @param mixed $text
 * @return string
 */
function F_tcecode_tag_arg($text) {
    // Patterns and replacements
    $tag = [
        "#\[align=(left|right|center|justify)\](.*?)\[/align\]#si" => '<span style="text-align:\1;">\2</span>',
        "#\[color=(\#[0-9a-fA-F]{6})\](.*?)\[/color\]#si" => '<span style="color:\1">\2</span>',
        "#\[color=(rgb\(\d{1,3},\d{1,3},\d{1,3}\))\](.*?)\[/color\]#si" => '<span style="color:\1">\2</span>',
        "#\[color=([a-zA-Z]+)\](.*?)\[/color\]#si" => '<span style="color:\1">\2</span>',
        "#\[bgcolor=(\#[0-9a-fA-F]{6})\](.*?)\[/bgcolor\]#si" => '<span style="background-color:\1">\2</span>',
        "#\[bgcolor=(rgb\(\d{1,3},\d{1,3},\d{1,3}\))\](.*?)\[/bgcolor\]#si" => '<span style="background-color:\1">\2</span>',
        "#\[bgcolor=([a-zA-Z]+)\](.*?)\[/bgcolor\]#si" => '<span style="background-color:\1">\2</span>',
        "#\[font=([a-zA-Z0-9 \-_,]+)\](.*?)\[/font\]#si" => '<span style="font-family:\1">\2</span>',
        "#\[size=([+\-]?[0-9a-z\-]+[%]?)\](.*?)\[/size\]#si" => '<span style="font-size:\1">\2</span>',
    ];

    foreach ($tag as $pattern => $replacement) {
        if (empty($text)) {
            break;
        }
        $text = preg_replace_callback(
            $pattern,
            fn($matches) => str_replace(['\1', '\2'], [$matches[1], $matches[2]], $replacement),
            $text
        );
    }

    return $text;
}

// ============================================================

/**
 * Callback function for preg_replace_callback (LaTeX replacement).
 * Returns replacement image for LaTeX code.
 * @param $matches (string) array containing matches: $matches[0] is the complete match, $matches[1] the match for the first subpattern enclosed in '(...)' (the LaTeX code)
 * @return string replacement HTML code string to include the equivalent LaTeX image.
 */
function F_latex_callback($matches)
{
    require_once('../../shared/config/tce_latex.php');
    // extract latex code and convert some entities
    $latex = unhtmlentities($matches[1], true);

    $dr = 3; // density ratio
    // generate file name
    $filename = K_LATEX_IMG_PREFIX . md5($latex);
    $imgpath = K_LATEX_PATH_PICTURE . $filename;
    $imgurl = false;
    $error = '';
    // check if file is already cached
    if (is_file($imgpath . '.' . K_LATEX_IMG_FORMAT)) {
        $imgurl = K_LATEX_PATH_PICTURE_HTTPD . $filename . '.' . K_LATEX_IMG_FORMAT;
    } elseif (strlen($latex) > K_LATEX_MAX_LENGHT) {
        // check if the formula
        $error = 'the formula is too long';
    } elseif (preg_match('/(include|def|command|loop|repeat|open|toks|output|input|catcode|name|[\^]{2}|\\\\every|\\\\errhelp|\\\\errorstopmode|\\\\scrollmode|\\\\nonstopmode|\\\\batchmode|\\\\read|\\\\write|csname|\\\\newhelp|\\\\uppercase|\\\\lowercase|\\\\relax|\\\\aftergroup|\\\\afterassignment|\\\\expandafter|\\\\noexpand|\\\\special)/i', $latex) > 0) {
        $error = 'invalid command';
    } else {
        // wrap the formula
        $ltx = '\nonstopmode' . "\n";
        $ltx .= '\documentclass{' . K_LATEX_CLASS . '}' . "\n";
        $ltx .= '\usepackage[T1]{fontenc}' . "\n";
        $ltx .= '\usepackage{amsmath,amsfonts,amssymb,wasysym,latexsym,marvosym,txfonts}' . "\n";
        $ltx .= '\usepackage[pdftex]{color}' . "\n";
        $ltx .= '\pagestyle{empty}' . "\n";
        $ltx .= '\begin{document}' . "\n";
        $ltx .= '\fontsize{' . K_LATEX_FONT_SIZE . '}{' . (2 * K_LATEX_FONT_SIZE) . '}' . "\n";
        $ltx .= '\selectfont' . "\n";
        $ltx .= '\color{black}' . "\n";
        $ltx .= '\pagecolor{white}' . "\n";
        $ltx .= '$' . $latex . '$' . "\n";
        $ltx .= '\end{document}' . "\n";
        if (file_put_contents($imgpath . '.tex', $ltx) === false) {
            $error = 'unable to write on the cache folder';
        } else {
            $cmd = 'cd ' . K_LATEX_PATH_PICTURE . ' && ' . K_LATEX_PDFLATEX . ' ' . $imgpath . '.tex';
            $sts = exec($cmd, $out, $ret);
            if (($sts === false) || ($ret != 0)) {
                $error = implode("\n", $out);
            } else {
                // convert code using ImageMagick
                $cmd = 'cd ' . K_LATEX_PATH_PICTURE . ' && ' . K_LATEX_PATH_CONVERT . ' -density ' . (K_LATEX_FORMULA_DENSITY * $dr) . ' -trim +repage ' . $imgpath . '.pdf -depth 8 -quality 100 ' . $imgpath . '.' . K_LATEX_IMG_FORMAT . ' 2>&1';
                unset($out);
                $sts = exec($cmd, $out, $ret);
                if (($sts === false) || ($ret != 0)) {
                    $error = implode("\n", $out);
                } else {
                    $imsize = @getimagesize($imgpath . '.' . K_LATEX_IMG_FORMAT);
                    [$w, $h] = $imsize;
                    if (($w / $dr) > K_LATEX_MAX_WIDTH || ($h / $dr) > K_LATEX_MAX_HEIGHT) {
                        $error = 'image size exceed limits';
                    } else {
                        $imgurl = K_LATEX_PATH_PICTURE_HTTPD . $filename . '.' . K_LATEX_IMG_FORMAT;
                    }
                }
            }
        }

        // remove temporary files (if any)
        $tmpext = ['tex', 'aux', 'log', 'pdf'];
        foreach ($tmpext as $ext) {
            if (F_file_exists($imgpath . '.' . $ext)) {
                @unlink($imgpath . '.' . $ext);
            }
        }
    }

    if ($imgurl === false) {
        return '[LaTeX: ERROR ' . $error . ']';
    }

    // alternative text to image
    $alt_latex = '[LaTeX]' . "\n" . htmlentities($latex, ENT_QUOTES);
    $replaceTable = [
        "\r" => '&#13;',
        "\n" => '&#10;',
    ];
    $alt_latex = strtr($alt_latex, $replaceTable);
    // XHTML code for image
    $imsize = @getimagesize($imgpath . '.' . K_LATEX_IMG_FORMAT);
    [$w, $h] = $imsize;

    return '<img src="' . $imgurl . '" alt="' . $alt_latex . '" class="tcecode" width="' . round($w / $dr) . '" height="' . round($h / $dr) . '" />';
}

/**
 * Callback function for preg_replace_callback (MathML replacement).
 * Returns replacement code for MathML code.
 * @param $matches (string) array containing matches: $matches[0] is the complete match, $matches[1] the match for the first subpattern enclosed in '(...)' (the MathML code)
 * @return string MathML code.
 */
function F_mathml_callback($matches)
{
    $mathml_tags = '<abs><and><annotation><annotation-xml><apply><approx><arccos><arccosh><arccot><arccoth><arccsc><arccsch><arcsec><arcsech><arcsin><arcsinh><arctan><arctanh><arg><bind><bvar><card><cartesianproduct><cbytes><ceiling><cerror><ci><cn><codomain><complexes><compose><condition><conjugate><cos><cosh><cot><coth><cs><csc><csch><csymbol><curl><declare><degree><determinant><diff><divergence><divide><domain><domainofapplication><el><emptyset><eq><equivalent><eulergamma><exists><exp><exponentiale><factorial><factorof><false><floor><fn><forall><gcd><geq><grad><gt><ident><image><imaginary><imaginaryi><implies><in><infinity><int><integers><intersect><interval><inverse><lambda><laplacian><lcm><leq><limit><list><ln><log><logbase><lowlimit><lt><maction><malign><maligngroup><malignmark><malignscope><math><matrix><matrixrow><max><mean><median><menclose><merror><mfenced><mfrac><mfraction><mglyph><mi><min><minus><mlabeledtr><mlongdiv><mmultiscripts><mn><mo><mode><moment><momentabout><mover><mpadded><mphantom><mprescripts><mroot><mrow><ms><mscarries><mscarry><msgroup><msline><mspace><msqrt><msrow><mstack><mstyle><msub><msubsup><msup><mtable><mtd><mtext><mtr><munder><munderover><naturalnumbers><neq><none><not><notanumber><note><notin><notprsubset><notsubset><or><otherwise><outerproduct><partialdiff><pi><piece><piecewise><plus><power><primes><product><prsubset><quotient><rationals><real><reals><reln><rem><root><scalarproduct><sdev><sec><sech><selector><semantics><sep><set><setdiff><share><sin><sinh><subset><sum><tan><tanh><tendsto><times><transpose><true><union><uplimit><variance><vector><vectorproduct><xor>';
    // extract latex code and convert some entities
    $mathml = unhtmlentities($matches[1], true);
    // $mathml = str_replace("&gt;", '>', $mathml);
    // $mathml = str_replace("&lt;", '<', $mathml);
    // remove all non-MathML tags
    $mathml = strip_tags($mathml, $mathml_tags);
    $mathml = preg_replace("/[\n\r\s]+/", ' ', $mathml);
    $mathml = trim($mathml);
    if (! str_starts_with($mathml, '<math')) {
        // add default math parent tag
        return '<math xmlns="http://www.w3.org/1998/Math/MathML">' . $mathml . '</math>';
    }

    return $mathml;
}

/**
 * Callback function for preg_replace_callback.
 * Returns replacement code by MIME type.
 * @param $matches (string) array containing matches: $matches[0] is the complete match, $matches[1] the match for the first subpattern enclosed in '(...)' and so on
 * @return string replacement string by file extension
 */
function F_objects_callback($matches)
{
    $width = 0;
    $height = 0;
    $alt = '';
    if (isset($matches[3]) && $matches[3] > 0) {
        $width = intval($matches[3]);
    }

    if (isset($matches[4]) && $matches[4] > 0) {
        $height = intval($matches[4]);
    }

    if (isset($matches[5]) && ! empty($matches[5])) {
        $alt = F_tcecodeToTitle($matches[5]);
    }

    return F_objects_replacement($matches[1], $matches[2], $width, $height, $alt);
}

/**
 * Returns the xhtml code needed to display the object by MIME type.
 * @param $name (string) object path excluded extension
 * @param $extension (string) object extension (e.g.: gif, jpg, swf, ...)
 * @param $width (int) object width
 * @param $height (int) object height
 * @param $alt (string) alternative content
 * @param $maxwidth (int) object max or default width
 * @param $maxheight (int) object max or default height
 * @return string replacement string
 */
function F_objects_replacement($name, $extension, $width = 0, $height = 0, $alt = '', &$maxwidth = 0, &$maxheight = 0)
{
    require_once('../config/tce_config.php');
    global $l, $db;
    $filename = $name . '.' . $extension;
    $arr_extension = explode('.', $filename);
    $extension = strtolower($arr_extension[count($arr_extension) - 1]);
    $htmlcode = '';
    switch ($extension) {
        case 'gif':
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'svg': { // images
            if (preg_match("#^http[s]?://#", $filename)) {
                $htmlcode = '<img src="'.$filename.'"';
                if (!empty($alt)) {
                    $htmlcode .= ' alt="'.$alt.'"';
                }
                $imsize = false;
            }
            else {
                $htmlcode = '<img src="' . K_PATH_URL_CACHE . $filename . '"';
                if (! empty($alt)) {
                    $htmlcode .= ' alt="' . $alt . '"';
                } else {
                    $htmlcode .= ' alt="image:' . $filename . '"';
                }

                $imsize = @getimagesize(K_PATH_CACHE . $filename);
                if ($imsize !== false) {
                    [$pixw, $pixh] = $imsize;
                    if ($width <= 0 && $height <= 0) {
                        // get default size
                        $width = $pixw;
                        $height = $pixh;
                    } elseif ($width <= 0) {
                        $width = $height * $pixw / $pixh;
                    } elseif ($height <= 0) {
                        $height = $width * $pixh / $pixw;
                    }
                }
            }

            $ratio = 1;
            if ($width > 0 && $height > 0) {
                $ratio = $width / $height;
            }

            // fit image on max dimensions
            if ($maxwidth > 0 && $width > $maxwidth) {
                $width = $maxwidth;
                $height = round($width / $ratio);
                $maxheight = min($maxheight, $height);
            }

            if ($maxheight > 0 && $height > $maxheight) {
                $height = $maxheight;
                $width = round($height * $ratio);
            }

            // print size
            if ($width > 0) {
                $htmlcode .= ' width="' . $width . '"';
            }

            if ($height > 0) {
                $htmlcode .= ' height="' . $height . '"';
            }

            $htmlcode .= ' class="tcecode" />';
            if ($imsize !== false) {
                $maxwidth = $pixw;
                $maxheight = $pixh;
            }

            break;
        }
        default: {
            include('../../shared/config/tce_mime.php');
            if (isset($mime[$extension])) {
                if (preg_match("#^http[s]?://#", $filename)) {
                    $htmlcode = '<object type="' . $mime[$extension] . '" data="' . $filename . '"';
                }
                else {
                    $htmlcode = '<object type="' . $mime[$extension] . '" data="' . K_PATH_URL_CACHE . $filename . '"';
                }

                if ($width > 0) {
                    $htmlcode .= ' width="' . $width . '"';
                } elseif ($maxwidth > 0) {
                    $htmlcode .= ' width="' . $maxwidth . '"';
                }

                if ($height > 0) {
                    $htmlcode .= ' height="' . $height . '"';
                } elseif ($maxheight > 0) {
                    $htmlcode .= ' height="' . $maxheight . '"';
                }

                $htmlcode .= '>';
                $htmlcode .= '<param name="type" value="' . $mime[$extension] . '" />';
                $htmlcode .= '<param name="src" value="' . K_PATH_URL_CACHE . $filename . '" />';
                $htmlcode .= '<param name="filename" value="' . K_PATH_URL_CACHE . $filename . '" />';
                if ($width > 0) {
                    $htmlcode .= '<param name="width" value="' . $width . '" />';
                } elseif ($maxwidth > 0) {
                    $htmlcode .= '<param name="width" value="' . $maxwidth . '" />';
                }

                if ($height > 0) {
                    $htmlcode .= '<param name="height" value="' . $height . '" />';
                } elseif ($maxheight > 0) {
                    $htmlcode .= '<param name="height" value="' . $maxheight . '" />';
                }

                if (! empty($alt)) {
                    $htmlcode .= '' . $alt . '';
                } else {
                    $htmlcode .= '[' . $mime[$extension] . ']:' . $filename . '';
                }

                $htmlcode .= '</object>';
            } else {
                $htmlcode = '[ERROR - UNKNOW MIME TYPE FOR: ' . $extension . ']';
            }

            break;
        }
    }

    return $htmlcode;
}

/**
 * Returns specified string without tcecode mark-up tags
 * @param $str (string) text to process
 * @return string without tcecode markup tags
 */
function F_remove_tcecode($str)
{
    $str = preg_replace("'\[object\](.*?)\[/object([^\]]*?)\]'si", '[OBJ]', $str);
    $str = preg_replace("'\[img([^\]]*?)\](.*?)\[/img\]'si", '[IMG]', $str);
    $str = preg_replace("'\[code\](.*?)\[/code\]'si", '\1', $str);
    $str = preg_replace("'\[b\](.*?)\[/b\]'si", '\1', $str);
    $str = preg_replace("'\[i\](.*?)\[/i\]'si", '\1', $str);
    $str = preg_replace("'\[s\](.*?)\[/s\]'si", '\1', $str);
    $str = preg_replace("'\[u\](.*?)\[/u\]'si", '\1', $str);
    $str = preg_replace("'\[o\](.*?)\[/o\]'si", '\1', $str);
    $str = preg_replace("'\[color([^\]]*?)\](.*?)\[/color\]'si", '\2', $str);
    $str = preg_replace("'\[bgcolor([^\]]*?)\](.*?)\[/bgcolor\]'si", '\2', $str);
    $str = preg_replace("'\[font([^\]]*?)\](.*?)\[/font\]'si", '\2', $str);
    $str = preg_replace("'\[size([^\]]*?)\](.*?)\[/size\]'si", '\2', $str);
    $str = preg_replace("'\[small\](.*?)\[/small\]'si", '\1', $str);
    $str = preg_replace("'\[sub\](.*?)\[/sub\]'si", '\1', $str);
    $str = preg_replace("'\[sup\](.*?)\[/sup\]'si", '\1', $str);
    $str = preg_replace("'\[url([^\]]*?)\](.*?)\[/url\]'si", '\2', $str);
    $str = preg_replace("'\[li\](.*?)\[/li\]'si", ' * \1', $str);
    $str = preg_replace("'\[\*\](.*?)\n'i", ' * \1', $str);
    $str = preg_replace("'\[ulist\](.*?)\[/ulist\]'si", '\1', $str);
    $str = preg_replace("'\[olist([^\]]*?)\](.*?)\[/olist\]'si", '\2', $str);
    return preg_replace("'\[tex\](.*?)\[/tex\]'si", '[TEX]', $str);
}

/**
 * Converts tcecode text to a single XHTML string removing some objects.
 * @param $str (string) text to process
 * return string
 */
function F_tcecodeToLine($str)
{
    $str = preg_replace("'\[object\](.*?)\[/object([^\]]*?)\]'si", '[OBJ]', $str);
    $str = preg_replace("'\[img([^\]]*?)\](.*?)\[/img\]'si", '[IMG]', $str);
    $str = preg_replace("'\[code\](.*?)\[/code\]'si", '\1', $str);
    $str = preg_replace("'\[li\](.*?)\[/li\]'si", ' * \1', $str);
    $str = preg_replace("'\[\*\](.*?)\n'i", ' * \1', $str);
    $str = preg_replace("'\[ulist\](.*?)\[/ulist\]'si", '\1', $str);
    $str = preg_replace("'\[olist([^\]]*?)\](.*?)\[/olist\]'si", '\2', $str);
    $str = preg_replace("'\[url([^\]]*?)\](.*?)\[/url\]'si", '\2', $str);
    $str = preg_replace("'\[tex\](.*?)\[/tex\]'si", '[TEX]', $str);
    $str = F_compact_string($str);
    $str = F_decode_tcecode($str);
    $str = F_compact_string($str);
    if (strlen($str) > K_QUESTION_LINE_MAX_LENGTH) {
        return F_substrHTML($str, K_QUESTION_LINE_MAX_LENGTH, 20) . ' ...';
    }

    return $str;
}

/**
 * Converts tcecode text to simple string for XHTML title attribute.
 * @param $str (string) text to process
 * return string
 */
function F_tcecodeToTitle($str)
{
    require_once('../config/tce_config.php');
    global $l;
    $str = F_remove_tcecode($str);
    $str = F_compact_string($str);
    return htmlspecialchars($str, ENT_QUOTES | ENT_COMPAT, $l['a_meta_charset']);
}

/**
 * Return a substring of XHTML code while making sure no html tags are chopped.
 * It also prevents chopping while a tag is still open.
 * this function is based on a public-domain script posted on www.php.net by fox@conskript.server and mr@bbp.biz
 * @param $htmltext (string)
 * @param $min_length (int) (default=100) the approximate length you want the concatenated text to be
 * @param $offset_length (int) (default=20) the max variation in how long the text can be
 */
function F_substrHTML($htmltext, $min_length = 100, $offset_length = 20)
{
    // Reset tag counter and quote checker
    $tag_counter = 0;
    $quotes_on = false;
    // Check if the text is too long
    if (strlen($htmltext) > $min_length) {
        // Reset the tag_counter and pass through (part of) the entire text
        $c = 0;
        for ($i = 0; $i < strlen($htmltext); ++$i) {
            // Load the current character and the next one if the string has not arrived at the last character
            $current_char = substr($htmltext, $i, 1);
            $next_char = $i < strlen($htmltext) - 1 ? substr($htmltext, $i + 1, 1) : '';

            // First check if quotes are on
            if (! $quotes_on) {
                // Check if it's a tag On a "<" add 3 if it's an opening tag (like <a href...) or add only 1 if it's an ending tag (like </a>)
                if ($current_char == '<') {
                    if ($next_char == '/') {
                        ++$tag_counter;
                    } else {
                        $tag_counter += 3;
                    }
                }

                // Slash signifies an ending (like </a> or ... />) substract 2
                if ($current_char == '/' && $tag_counter != 0) {
                    $tag_counter -= 2;
                }

                // On a ">" substract 1
                if ($current_char == '>') {
                    --$tag_counter;
                }

                // If quotes are encountered, start ignoring the tags (for directory slashes)
                if ($current_char == '"') {
                    $quotes_on = true;
                }
            } elseif ($current_char == '"') {
                // IF quotes are encountered again, turn it back off
                $quotes_on = false;
            }

            // Count only the chars outside html tags
            if ($tag_counter == 2 || $tag_counter == 0) {
                ++$c;
            }

            // Check if the counter has reached the minimum length yet,
            // then wait for the tag_counter to become 0, and chop the string there
            if ($c > $min_length - $offset_length && $tag_counter == 0 && $next_char == ' ') {
                return substr($htmltext, 0, $i + 1);
            }
        }
    }

    return $htmltext;
}


//============================================================+
// END OF FILE
//============================================================+
