<?php

//============================================================+
// File name   : tce_pdf_report.php
// Begin       : 2026-06-22
// Author      : Nicola Asuni - Tecnick.com LTD - tecnick.com - info@tecnick.com
//
// Description : TCExam PDF report base class built on tc-lib-pdf (replaces the
//               legacy TCPDF-based tcpdfex.php for the report/export documents).
//               Reports are rendered by building HTML and letting the tc-lib-pdf
//               HTML/CSS engine handle layout, wrapping and page breaks.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * TCExam PDF report base class built on tc-lib-pdf.
 * @package com.tecnick.tcexam.shared
 * @author Nicola Asuni
 * @since 2026-06-22
 */

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * @class TcePdfReport
 * tc-lib-pdf subclass that renders TCExam report PDFs from HTML.
 * Provides an automatic page header/footer (via defaultPageContent) and a
 * vertical content cursor so successive HTML blocks flow down the page.
 */
class TcePdfReport extends \Com\Tecnick\Pdf\Tcpdf
{
    /** Left content margin in mm. */
    protected float $contentX = PDF_MARGIN_LEFT;

    /** Width of the content area in mm. */
    protected float $contentW = 0.0;

    /** Y position (mm) where page content starts, below the header band. */
    protected float $contentTop = PDF_MARGIN_TOP;

    /** Current vertical cursor (mm) for the next content block. */
    protected float $cursorY = 0.0;

    /** Cached default content font handle (['out' => ...]). */
    protected ?array $contentFont = null;

    /** Header title (left, bold). */
    protected string $headerTitle = '';

    /** Header descriptive string (below the title). */
    protected string $headerString = '';

    /** Optional header logo file name (resolved under K_PATH_IMAGES). */
    protected string $headerLogo = '';

    /** Header logo width in mm. */
    protected float $headerLogoWidth = 0.0;

    /** Optional URL printed as a QR-Code in the header (back-link to TCExam). */
    protected string $tcexam_backlink = '';

    /** When false, defaultPageContent() draws nothing (used for clean OMR scan pages). */
    protected bool $renderDecoration = true;

    /**
     * Constructor: A4 portrait, millimetres, unicode, compressed.
     */
    public function __construct()
    {
        // All engine options are configurable via TCExam config (shared/config tce_pdf.php),
        // falling back to sensible defaults when a constant is not defined.
        $unit = defined('PDF_UNIT') ? (string) PDF_UNIT : 'mm';
        $unicode = defined('K_PDF_UNICODE') ? (bool) K_PDF_UNICODE : true;
        $subsetfont = defined('K_PDF_SUBSET_FONT') ? (bool) K_PDF_SUBSET_FONT : false;
        $compress = defined('K_PDF_COMPRESS') ? (bool) K_PDF_COMPRESS : true;
        $mode = defined('K_PDF_MODE') ? (string) K_PDF_MODE : '';
        parent::__construct($unit, $unicode, $subsetfont, $compress, $mode, null, self::buildFileOptions());
        // A4 portrait default; refined from the real page in addReportPage().
        $this->contentW = 210.0 - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;
        $this->cursorY = $this->contentTop;
        $this->enableDefaultPageContent();
        // Sign the document when enabled in configuration (the engine signs at output time).
        $this->applyDigitalSignature();
    }

    /**
     * Apply a digital signature to the document when enabled in configuration.
     *
     * Reads the K_DIGSIG_* constants (shared/config tce_pdf.php) and maps them onto the
     * tc-lib-pdf setSignature() contract. A no-op unless K_DIGSIG_ENABLE is true and a signing
     * certificate is configured; the actual signing is performed by the engine at output time.
     */
    protected function applyDigitalSignature(): void
    {
        if (!defined('K_DIGSIG_ENABLE') || !K_DIGSIG_ENABLE) {
            return;
        }
        $signcert = defined('K_DIGSIG_CERTIFICATE') ? (string) K_DIGSIG_CERTIFICATE : '';
        if ($signcert === '') {
            return;
        }
        $data = [
            'signcert' => $signcert,
            'privkey' => defined('K_DIGSIG_PRIVATE_KEY') ? (string) K_DIGSIG_PRIVATE_KEY : $signcert,
            'password' => defined('K_DIGSIG_PASSWORD') ? (string) K_DIGSIG_PASSWORD : '',
            'cert_type' => defined('K_DIGSIG_CERT_TYPE') ? (int) K_DIGSIG_CERT_TYPE : 2,
            'info' => [
                'Name' => defined('K_DIGSIG_NAME') ? (string) K_DIGSIG_NAME : '',
                'Location' => defined('K_DIGSIG_LOCATION') ? (string) K_DIGSIG_LOCATION : '',
                'Reason' => defined('K_DIGSIG_REASON') ? (string) K_DIGSIG_REASON : '',
                'ContactInfo' => defined('K_DIGSIG_CONTACT') ? (string) K_DIGSIG_CONTACT : '',
            ],
        ];
        // Optional bundle of extra certificates (only pass when configured).
        if (defined('K_DIGSIG_EXTRA_CERTS') && (string) K_DIGSIG_EXTRA_CERTS !== '') {
            $data['extracerts'] = (string) K_DIGSIG_EXTRA_CERTS;
        }
        $this->setSignature($data);
    }

    /**
     * Build the tc-lib-file security options from TCExam configuration constants.
     * Mirrors the tc-lib-pdf fileOptions contract so the allowed local paths, remote
     * hosts and download size limit are configurable (see shared/config tce_pdf.php).
     * Returns null when no constants are defined, so the library defaults apply.
     *
     * @return array<string,mixed>|null
     */
    protected static function buildFileOptions(): ?array
    {
        $opts = [];
        if (defined('K_PDF_ALLOWED_PATHS')) {
            $paths = @unserialize((string) K_PDF_ALLOWED_PATHS, ['allowed_classes' => false]);
            if (is_array($paths) && $paths !== []) {
                $opts['allowedPaths'] = array_values(array_filter(array_map('strval', $paths)));
            }
        }
        if (defined('K_PDF_ALLOWED_HOSTS')) {
            $hosts = @unserialize((string) K_PDF_ALLOWED_HOSTS, ['allowed_classes' => false]);
            if (is_array($hosts)) {
                // Empty list keeps remote loading disabled (SSRF-safe default).
                $opts['allowedHosts'] = array_values(array_filter(array_map('strval', $hosts)));
            }
        }
        if (defined('K_PDF_MAX_REMOTE_SIZE')) {
            $opts['maxRemoteSize'] = (int) K_PDF_MAX_REMOTE_SIZE;
        }
        return $opts === [] ? null : $opts;
    }

    /**
     * Set the page header content.
     *
     * @param string $title     Header title (left, bold).
     * @param string $string    Header descriptive text.
     * @param string $logo      Logo file name (under K_PATH_IMAGES), or empty.
     * @param float  $logowidth Logo width in mm.
     */
    public function setReportHeader(string $title, string $string = '', string $logo = '', float $logowidth = 0.0): void
    {
        $this->headerTitle = $title;
        $this->headerString = $string;
        $this->headerLogo = $logo;
        $this->headerLogoWidth = $logowidth;
    }

    /**
     * Set a URL printed as a QR-Code in the page header (back-link to TCExam).
     *
     * @param string $link URL link.
     */
    public function setTCExamBackLink(string $link): void
    {
        $this->tcexam_backlink = $link;
    }

    /**
     * Add a new content page and reset the content cursor below the header.
     */
    public function addReportPage(): void
    {
        $data = [];
        if (defined('PDF_PAGE_FORMAT')) {
            $data['format'] = (string) PDF_PAGE_FORMAT;
        }
        if (defined('PDF_PAGE_ORIENTATION')) {
            $data['orientation'] = (string) PDF_PAGE_ORIENTATION;
        }
        // Reserve the header/footer bands as page margins so the HTML engine's
        // writable region (and therefore the automatic page-break resume position)
        // starts below the header and ends above the footer. Without this the page
        // region defaults to the full page (RY=0) and content that overflows onto a
        // new page resumes at the very top, overprinting the header band.
        $data['margin'] = [
            'PL' => (float) PDF_MARGIN_LEFT,
            'PR' => (float) PDF_MARGIN_RIGHT,
            'PT' => (float) PDF_MARGIN_TOP,
            'PB' => (float) PDF_MARGIN_BOTTOM,
        ];
        $this->addPage($data);
        $page = $this->page->getPage($this->page->getPageId());
        if (isset($page['width'])) {
            $this->contentW = (float) $page['width'] - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT;
        }
        $this->cursorY = $this->contentTop;
    }

    /**
     * Enable or disable the automatic page header/footer (turn off for clean OMR scan pages).
     *
     * @param bool $on True to draw the header/footer on subsequent pages.
     */
    public function enablePageDecoration(bool $on): void
    {
        $this->renderDecoration = $on;
    }

    /**
     * Ensure a default content font is active (required before HTML/text output).
     */
    protected function ensureContentFont(): void
    {
        if ($this->contentFont === null) {
            $this->contentFont = $this->font->insert($this->pon, PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
        }
        $this->page->addContent($this->contentFont['out']);
    }

    /**
     * Render an HTML block at the current cursor and advance the cursor.
     * The tc-lib-pdf HTML engine handles wrapping and internal page breaks.
     *
     * @param string $html HTML content to render.
     */
    public function writeReportHTML(string $html): void
    {
        if (trim($html) === '') {
            return;
        }
        if ($this->page->getPageId() < 0) {
            $this->addReportPage();
        }
        // If too little vertical room remains in the writable region to start a new
        // block, move to a fresh page first. The HTML engine paginates table rows
        // but not a tall inline figure, so a block that begins near the page bottom
        // can overrun the footer; bumping the whole block keeps it intact on the
        // next page. A fresh page always has the full region height, so this cannot
        // loop. See defaultPageContent() for the reserved header/footer bands.
        $region = $this->page->getRegion();
        $regionTop = (float) $region['RY'];
        $regionBottom = $regionTop + (float) $region['RH'];
        $minBlockRoom = 0.15 * (float) $region['RH'];
        if ($this->cursorY > ($regionTop + 0.1) && ($regionBottom - $this->cursorY) < $minBlockRoom) {
            $this->addReportPage();
        }
        $this->ensureContentFont();
        $html = $this->resolveHtmlImagePaths($html);
        $this->addHTMLCell(html: $html, posx: $this->contentX, posy: $this->cursorY, width: $this->contentW);
        $bbox = $this->getLastBBox();
        $this->cursorY = (float) ($bbox['y'] + $bbox['h']) + 1.5;
    }

    /**
     * Rewrite cache image URLs to absolute filesystem paths for the PDF engine.
     *
     * Question/answer content and LaTeX formulas are decoded with F_decode_tcecode(), which
     * emits <img src="..."> using the URL-cache path (K_PATH_URL_CACHE). The browser resolves
     * that URL, but tc-lib-pdf's image loader needs a local filesystem path; an unresolved URL
     * is silently dropped (image / rendered-LaTeX missing). Rewrite the cache URL prefix to the
     * on-disk cache directory (K_PATH_CACHE) so the assets embed.
     *
     * @param string $html HTML content to render.
     *
     * @return string HTML with cache image sources rewritten to filesystem paths.
     */
    protected function resolveHtmlImagePaths(string $html): string
    {
        if (!defined('K_PATH_URL_CACHE') || !defined('K_PATH_CACHE') || K_PATH_URL_CACHE === '') {
            return $html;
        }

        return str_replace(
            ['src="' . K_PATH_URL_CACHE, "src='" . K_PATH_URL_CACHE],
            ['src="' . K_PATH_CACHE, "src='" . K_PATH_CACHE],
            $html,
        );
    }

    /**
     * Output the report to the browser as a downloadable PDF.
     *
     * @param string $filename Suggested download file name.
     */
    public function outputReport(string $filename = 'tcexam_report.pdf'): void
    {
        $this->setPDFFilename($filename);
        $raw = $this->getOutPDFString();
        $this->downloadPDF($raw);
    }

    /**
     * Generate the repeating page header (and an empty footer) for every page.
     * Invoked automatically by addPage() when enableDefaultPageContent() is on.
     *
     * @param int $pid Page index.
     *
     * @return string Raw PDF content stream prepended to the page.
     */
    public function defaultPageContent(int $pid = -1): string
    {
        if (!$this->renderDecoration) {
            return '';
        }
        if ($pid < 0) {
            $pid = $this->page->getPageId();
        }
        $page = $this->page->getPage($pid);
        // The page-decoration callback runs from addPage() *before* it sets the graph page
        // dimensions, so barcode/rect helpers (which resolve Y against $this->graph->pageh) would
        // otherwise position against a stale/zero page height and draw the QR back-link off-page.
        // Set the current page geometry on the graph now so the QR lands inside the header.
        $this->graph->setPageWidth((float) $page['width']);
        $this->graph->setPageHeight((float) $page['height']);
        $pw = (float) $page['width'];
        $lm = (float) PDF_MARGIN_LEFT;
        $rm = $pw - (float) PDF_MARGIN_RIGHT;
        $tw = $rm - $lm;

        $out = $this->graph->getStartTransform();

        // Resolve the optional header logo (configured via PDF_HEADER_LOGO / setReportHeader).
        $logoPath = '';
        $logoW = 0.0;
        $logoH = 0.0;
        if ($this->headerLogo !== '' && defined('K_PATH_MAIN')) {
            $candidate = K_PATH_MAIN . 'images/' . $this->headerLogo;
            if (is_file($candidate)) {
                $logoPath = $candidate;
                $logoW = $this->headerLogoWidth > 0 ? $this->headerLogoWidth : 20.0;
                $size = @getimagesize($logoPath);
                $logoH = is_array($size) && (float) $size[0] > 0
                    ? $logoW * ((float) $size[1] / (float) $size[0])
                    : $logoW;
            }
        }

        // Header layout: [logo] [title / description] .................. [QR].
        // The logo sits at the left margin; the title block flows to its right and
        // shrinks to clear the QR back-link cluster in the top-right corner.
        $gap = 3.0;
        $qrSize = 18.0;
        $qrPresent = $this->tcexam_backlink !== '';

        // Header logo: top-left corner, with the title/description to its right.
        $textX = $lm;
        if ($logoPath !== '') {
            try {
                $iid = $this->addMarkupImage($logoPath);
                $out .= $this->image->getSetImage(
                    $iid,
                    $lm,
                    (float) PDF_MARGIN_HEADER,
                    $logoW,
                    $logoH,
                    (float) $page['height'],
                );
                $textX = $lm + $logoW + $gap;
            } catch (\Throwable) {
                // ignore logo rendering errors (missing/unsupported image)
            }
        }

        // Title block width: from the right edge of the logo to the left edge of the QR.
        $titleRight = $qrPresent ? $rm - $qrSize - $gap : $rm;
        $titleW = max(0.0, $titleRight - $textX);

        // Header title (bold) and descriptive string.
        $titleFont = $this->font->insert($this->pon, PDF_FONT_NAME_MAIN, 'B', PDF_FONT_SIZE_MAIN + 1);
        $out .= $titleFont['out'];
        $out .= $this->color->getPdfColor('#000000');
        $out .= $this->getTextCell(
            txt: $this->headerTitle,
            posx: $textX,
            posy: (float) PDF_MARGIN_HEADER,
            width: $titleW,
            height: 6.0,
            offset: 0,
            linespace: 0,
            valign: 'T',
            halign: 'L',
        );
        if ($this->headerString !== '') {
            $strFont = $this->font->insert($this->pon, PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);
            $out .= $strFont['out'];
            $out .= $this->getTextCell(
                txt: $this->headerString,
                posx: $textX,
                posy: (float) PDF_MARGIN_HEADER + 6.0,
                width: $titleW,
                height: 0,
                offset: 0,
                linespace: 0,
                valign: 'T',
                halign: 'L',
            );
        }

        // QR-Code back-link, top-right corner.
        if ($qrPresent) {
            $out .= $this->getBarcode(
                type: 'QRCODE,L',
                code: $this->tcexam_backlink,
                posx: $rm - $qrSize,
                posy: (float) PDF_MARGIN_HEADER,
                width: (int) $qrSize,
                height: (int) $qrSize,
                style: ['fillColor' => '#000000'], // bars must be black; do not inherit current fill colour
            );
        }

        // Divider line below the header band.
        $liney = $this->contentTop - 2.0;
        $out .= $this->graph->getLine($lm, $liney, $rm, $liney, ['lineWidth' => 0.3, 'lineColor' => '#000000']);

        // Footer: separator line, branding (left) and page number (right).
        $footerMargin = defined('PDF_MARGIN_FOOTER') ? (float) PDF_MARGIN_FOOTER : 10.0;
        $footerY = (float) $page['height'] - $footerMargin;
        $ffont = $this->font->insert($this->pon, PDF_FONT_NAME_DATA, '', max(5, PDF_FONT_SIZE_DATA - 1));
        $out .= $ffont['out'];
        $out .= $this->graph->getLine($lm, $footerY - 1.0, $rm, $footerY - 1.0, [
            'lineWidth' => 0.2,
            'lineColor' => '#999999',
        ]);
        $out .= $this->color->getPdfColor('#7f7f7f');
        $out .= $this->getTextCell(
            txt: 'Powered by TCExam (www.tcexam.org)',
            posx: $lm,
            posy: $footerY,
            width: $tw,
            height: 5.0,
            offset: 0,
            linespace: 0,
            valign: 'C',
            halign: 'L',
        );
        $out .= $this->getTextCell(
            txt: (string) ($pid + 1),
            posx: $lm,
            posy: $footerY,
            width: $tw,
            height: 5.0,
            offset: 0,
            linespace: 0,
            valign: 'C',
            halign: 'R',
        );
        $out .= $this->color->getPdfColor('#000000');
        $out .= $this->graph->getStopTransform();

        return $out;
    }

    /**
     * Print question statistics as an HTML table.
     * @param array $stats        Data to print.
     * @param int   $display_mode 2 = module; 3 = + subject; 4 = + question; 5 = + answer.
     */
    public function printQuestionStats($stats, $display_mode = 2): void
    {
        if ($display_mode < 2 || empty($stats)) {
            return;
        }
        global $l;

        $title = $l['w_statistics'] . ' [' . $l['w_all'] . ' + ' . $l['w_module'];
        if ($display_mode > 2) {
            $title .= ' + ' . $l['w_subject'];
            if ($display_mode > 3) {
                $title .= ' + ' . $l['w_question'];
                if ($display_mode > 4) {
                    $title .= ' + ' . $l['w_answer'];
                }
            }
        }
        $title .= ']';

        $cols = [
            $l['w_recurrence'],
            $l['w_score'],
            $l['w_answer_time'],
            $l['w_answers_right_th'],
            $l['w_answers_wrong_th'],
            $l['w_questions_unanswered_th'],
            $l['w_questions_undisplayed_th'],
            $l['w_questions_unrated_th'],
        ];

        $html = '<table border="0.5" cellpadding="2" style="font-size:7pt;">';
        $html .=
            '<tr><td colspan="9" style="text-align:center;font-weight:bold;border-bottom:0.5px solid #000;">'
            . htmlspecialchars((string) $title)
            . '</td></tr>';
        $html .= '<tr style="background-color:#cccccc;font-weight:bold;text-align:center;">';
        $html .= '<td>#</td>';
        foreach ($cols as $c) {
            $html .= '<td>' . htmlspecialchars((string) $c) . '</td>';
        }
        $html .= '</tr>';

        // overall "all" row
        $html .= $this->statsRow('#ffeeee', $l['w_all'], $stats, true);

        $num_module = 0;
        foreach ($stats['module'] as $module) {
            ++$num_module;
            $mcode = 'M' . $num_module;
            $html .= $this->statsRow('#ddeeff', $mcode, $module, true);
            $html .= $this->statsNameRow('#ddeeff', F_decode_tcecode($module['name']));

            if ($display_mode > 2) {
                $num_subject = 0;
                foreach ($module['subject'] as $subject) {
                    ++$num_subject;
                    $scode = $mcode . 'S' . $num_subject;
                    $html .= $this->statsRow('#ddffdd', $scode, $subject, true);
                    $html .= $this->statsNameRow('#ddffdd', F_decode_tcecode($subject['name']));

                    if ($display_mode > 3) {
                        $num_question = 0;
                        foreach ($subject['question'] as $question) {
                            ++$num_question;
                            $qcode = $scode . 'Q' . $num_question;
                            $html .= $this->statsRow('#fffacd', $qcode, $question, true);
                            $html .= $this->statsNameRow('#fffacd', F_decode_tcecode($question['description']));

                            if ($display_mode > 4) {
                                $num_answer = 0;
                                foreach ($question['answer'] as $answer) {
                                    ++$num_answer;
                                    $acode = $qcode . 'A' . $num_answer;
                                    $html .= $this->statsRow('#ffffff', $acode, $answer, false);
                                    $html .= $this->statsNameRow('#ffffff', F_decode_tcecode($answer['description']));
                                }
                            }
                        }
                    }
                }
            }
        }
        $html .= '</table>';

        $this->writeReportHTML($html);
    }

    /**
     * Build a <colgroup> with explicit per-column percentage widths.
     * Used to override the HTML engine's content-based auto-layout where the
     * default sizing would over-widen short columns and starve others.
     *
     * @param array<int,float> $widths Column widths in percent of the table width.
     */
    protected function colGroup(array $widths): string
    {
        $out = '<colgroup>';
        foreach ($widths as $w) {
            $out .= '<col style="width:' . $w . '%;"/>';
        }
        return $out . '</colgroup>';
    }

    /**
     * Print the test results statistics table.
     * @param array $data    Test statistics.
     * @param bool  $pubmode If true, filter for the public interface (hide user column).
     * @param int   $stats   2 = full stats; 1 = user stats; 0 = disabled.
     */
    public function printTestResultStat($data, $pubmode = false, $stats = 2): void
    {
        global $l;
        $this->setBookmark($l['w_results']);
        $rtl = ($l['a_meta_dir'] ?? '') === 'rtl';

        $headers = ['#', $l['w_time_begin'], $l['w_time'], $l['w_test']];
        if (!$pubmode) {
            $headers[] = $l['w_user'] . ' - ' . $l['w_lastname'] . ', ' . $l['w_firstname'];
        }
        $headers[] = $l['w_score'];
        if ($stats > 0) {
            $headers = array_merge($headers, [
                $l['w_answers_right_th'],
                $l['w_answers_wrong_th'],
                $l['w_questions_unanswered_th'],
                $l['w_questions_undisplayed_th'],
                $l['w_questions_unrated_th'],
            ]);
        }

        // Explicit per-column widths (percent of the content width). Without them the
        // HTML engine auto-sizes columns from the short first-row body text, which
        // over-widens the date/time/score columns and starves the long statistics
        // headers (they overlap). The leading #/begin/time/score widths are fixed to
        // fit their content; the test (and user) column takes the remaining space.
        $hasStats = $stats > 0;
        $wNum = 4.0;
        $wBegin = 16.0;
        $wTime = 8.0;
        $wScore = 14.0;
        // The admin report adds a User column, so trim the stat columns a touch there
        // to leave the two text columns (test/user) a usable share of the width.
        $wStat = $pubmode ? 9.0 : 8.0;
        $flexCols = $pubmode ? 1 : 2; // test (and user when not in public mode)
        $fixed = $wNum + $wBegin + $wTime + $wScore + ($hasStats ? 5 * $wStat : 0.0);
        $wFlex = max(1.0, (100.0 - $fixed) / $flexCols);

        $mainCols = [$wNum, $wBegin, $wTime, $wFlex];
        if (!$pubmode) {
            $mainCols[] = $wFlex;
        }
        $mainCols[] = $wScore;
        if ($hasStats) {
            $mainCols = array_merge($mainCols, array_fill(0, 5, $wStat));
        }

        $html =
            '<table border="0.5" cellpadding="2" style="font-size:8pt;">'
            . $this->colGroup($mainCols)
            . '<thead><tr style="background-color:#cccccc;font-weight:bold;text-align:center;">';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars((string) $h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data['testuser'] as $tu) {
            $bg = !empty($tu['passmsg']) ? '#ddffdd' : '#ffeeee';
            $html .= '<tr style="background-color:' . $bg . ';">';
            $html .= '<td style="text-align:right;">' . htmlspecialchars((string) $tu['num']) . '</td>';
            $html .=
                '<td style="text-align:right;">' . htmlspecialchars((string) $tu['testuser_creation_time']) . '</td>';
            $html .= '<td style="text-align:right;">' . htmlspecialchars((string) $tu['time_diff']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string) $tu['test']['test_name']) . '</td>';
            if (!$pubmode) {
                $html .=
                    '<td>'
                    . htmlspecialchars($tu['user_name'] . ' - ' . $tu['user_lastname'] . ', ' . $tu['user_firstname'])
                    . '</td>';
            }
            // Monospace + fixed 3-decimal score + space-padded percentage so the numbers
            // line up vertically (F_formatPdfPercentage already pads via sprintf('% 3d')).
            $html .=
                '<td style="text-align:right;font-weight:bold;font-family:courier;">'
                . htmlspecialchars(
                    F_formatFloat($tu['total_score']) . ' '
                        . F_formatPdfPercentage(floatval($tu['total_score_perc']), false),
                )
                . '</td>';
            if ($stats > 0) {
                foreach (['right', 'wrong', 'unanswered', 'undisplayed', 'unrated'] as $k) {
                    $html .=
                        '<td style="text-align:right;">'
                        . htmlspecialchars($tu[$k] . ' ' . F_formatPdfPercentage(floatval($tu[$k . '_perc']), false))
                        . '</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // passed summary
        $bg = $data['passed_perc'] > 50 ? '#ddffdd' : '#ffeeee';
        $html .=
            '<table border="0.5" cellpadding="2" style="font-size:8pt;"><tr style="background-color:'
            . $bg
            . ';font-weight:bold;"><td>'
            . htmlspecialchars(
                $l['w_passed'] . ': ' . $data['passed'] . ' '
                    . F_formatPdfPercentage(floatval($data['passed_perc']), false),
            )
            . '</td></tr></table>';

        // distribution statistics
        $printstat = ['mean', 'median', 'mode', 'standard_deviation', 'skewness', 'kurtosi'];
        $noperc = ['skewness', 'kurtosi'];
        $srows = '';
        foreach ($data['statistics'] as $row => $col) {
            if (!in_array($row, $printstat, true)) {
                continue;
            }
            $cells = [];
            $keys = ['score_perc', 'right_perc', 'wrong_perc', 'unanswered_perc', 'undisplayed_perc', 'unrated_perc'];
            foreach ($keys as $i => $k) {
                if ($i > 0 && $stats <= 0) {
                    break;
                }
                $cells[] = in_array($row, $noperc, true) ? F_formatFloat($col[$k]) : round($col[$k]) . '%';
            }
            $srows .=
                '<tr><td style="font-weight:bold;text-align:right;">' . htmlspecialchars($l['w_' . $row]) . '</td>';
            foreach ($cells as $c) {
                $srows .=
                    '<td style="text-align:right;font-family:courier;">' . htmlspecialchars((string) $c) . '</td>';
            }
            $srows .= '</tr>';
        }
        if ($srows !== '') {
            // Mirror the main-table score/stat widths so this table lines up beneath it;
            // the label column spans the leading #/begin/time/test(/user) columns.
            $statCols = array_merge(
                [$wNum + $wBegin + $wTime + ($wFlex * $flexCols), $wScore],
                $hasStats ? array_fill(0, 5, $wStat) : [],
            );
            $html .=
                '<table border="0.5" cellpadding="2" style="font-size:8pt;">'
                . $this->colGroup($statCols)
                . $srows
                . '</table>';
        }

        if ($rtl) {
            $html = '<div dir="rtl">' . $html . '</div>';
        }
        $this->writeReportHTML($html);
    }

    /**
     * Print the test/user info box followed by the per-question details.
     * @param array $data     Testuser data.
     * @param bool  $onlytext If true, print only free-text questions.
     */
    public function printTestUserInfo($data, $onlytext = false): void
    {
        global $l;
        $rtl = ($l['a_meta_dir'] ?? '') === 'rtl';

        $this->setBookmark(
            ($data['user_lastname'] ?? '')
                . ' '
                . ($data['user_firstname'] ?? '')
                . ' ('
                . ($data['user_name'] ?? '')
                . '), '
                . ($data['total_score'] ?? 0)
                . ' '
                . F_formatPdfPercentage(floatval($data['total_score_perc'] ?? 0), false),
        );

        if (
            !isset($data['test']['user_test_end_time'])
            || $data['test']['user_test_end_time'] <= 0
            || strtotime($data['test']['user_test_end_time']) < strtotime($data['test']['user_test_start_time'])
        ) {
            $time_diff = ($data['test']['test_duration_time'] ?? 0) * 60;
        } else {
            $time_diff =
                strtotime($data['test']['user_test_end_time']) - strtotime($data['test']['user_test_start_time']);
        }

        $rec = $data['recurrence'] ?? '';
        $info = [
            $l['w_lastname'] => $data['user_lastname'] ?? '',
            $l['w_firstname'] => $data['user_firstname'] ?? '',
            $l['w_user'] => $data['user_name'] ?? '',
            $l['w_time_begin'] => $data['test']['user_test_start_time'] ?? '',
            $l['w_time_end'] => $data['test']['user_test_end_time'] ?? '',
            $l['w_time'] => gmdate('H:i:s', $time_diff),
        ];

        $passmsg = '';
        if (($data['test']['test_score_threshold'] ?? 0) > 0) {
            $info[$l['w_test_score_threshold']] = $data['test']['test_score_threshold'];
            $passmsg = $data['total_score'] >= $data['test']['test_score_threshold']
                ? ' - ' . $l['w_passed']
                : ' - ' . $l['w_not_passed'];
        }

        foreach ([
            'right' => 'w_answers_right',
            'wrong' => 'w_answers_wrong',
            'unanswered' => 'w_questions_unanswered',
            'undisplayed' => 'w_questions_undisplayed',
            'unrated' => 'w_questions_unrated',
        ] as $k => $label) {
            $info[$l[$label]] =
                ($data[$k] ?? '')
                . ' / '
                . $rec
                . ' '
                . F_formatPdfPercentage(floatval($data[$k . '_perc'] ?? 0), false);
        }

        $html = '<table border="0.5" cellpadding="3" style="font-size:8pt;">';
        $html .=
            '<tr style="background-color:#cccccc;font-weight:bold;"><td colspan="2">'
            . htmlspecialchars(($l['w_test'] ?? '') . ': ' . ($data['test']['test_name'] ?? ''))
            . '</td></tr>';
        foreach ($info as $k => $v) {
            $html .=
                '<tr><td style="font-weight:bold;width:35%;">'
                . htmlspecialchars((string) $k)
                . '</td><td>'
                . htmlspecialchars((string) $v)
                . '</td></tr>';
        }
        $html .=
            '<tr style="font-weight:bold;"><td>'
            . htmlspecialchars($l['w_score'])
            . '</td><td>'
            . htmlspecialchars(
                ($data['total_score'] ?? '')
                . ' / '
                . ($data['test']['test_max_score'] ?? '')
                . ' '
                . F_formatPdfPercentage(floatval($data['total_score_perc'] ?? 0), false)
                . $passmsg,
            )
            . '</td></tr>';
        $html .= '</table>';

        if (!empty($data['test']['test_description'])) {
            $html .= '<div style="font-size:8pt;">' . $data['test']['test_description'] . '</div>';
        }
        if (!empty($data['test']['user_comment'])) {
            $html .=
                '<div style="font-size:8pt;"><b>'
                . htmlspecialchars($l['w_comment'])
                . '</b>: '
                . $data['test']['user_comment']
                . '</div>';
        }

        if ($rtl) {
            $html = '<div dir="rtl">' . $html . '</div>';
        }
        $this->writeReportHTML($html);

        $this->printUserTestDetails($data, $onlytext);
    }

    /**
     * Print the per-question test details for the selected user.
     * @param array $data     Testuser data.
     * @param bool  $onlytext If true, print only free-text questions.
     */
    public function printUserTestDetails($data, $onlytext = false): void
    {
        global $db, $l;
        $testuser_id = (int) ($data['id'] ?? 0);
        $qtype = ['S', 'M', 'T', 'O'];

        $sql =
            'SELECT * FROM '
            . K_TABLE_QUESTIONS
            . ', '
            . K_TABLE_TESTS_LOGS
            . ', '
            . K_TABLE_SUBJECTS
            . ', '
            . K_TABLE_MODULES
            . '
			WHERE question_id=testlog_question_id
				AND testlog_testuser_id='
            . $testuser_id
            . '
				AND question_subject_id=subject_id
				AND subject_module_id=module_id';
        if ($onlytext) {
            $sql .= ' AND question_type=3';
        }
        $sql .= ' ORDER BY testlog_id';

        if ($r = F_db_query($sql, $db)) {
            $itemcount = 1;
            while ($m = F_db_fetch_array($r)) {
                $display_time = isset($m['testlog_display_time']) && strlen($m['testlog_display_time']) > 0
                    ? substr($m['testlog_display_time'], 11, 8)
                    : '--:--:--';
                $change_time = isset($m['testlog_change_time']) && strlen($m['testlog_change_time']) > 0
                    ? substr($m['testlog_change_time'], 11, 8)
                    : '--:--:--';
                $diff_time = isset($m['testlog_display_time'], $m['testlog_change_time'])
                    ? date('i:s', strtotime($m['testlog_change_time']) - strtotime($m['testlog_display_time']))
                    : '--:--';
                $reaction_time = isset($m['testlog_reaction_time']) && strlen($m['testlog_reaction_time']) > 0
                    ? $m['testlog_reaction_time'] / 1000
                    : '';

                $html = '<table border="0.5" cellpadding="2" style="font-size:8pt;"><tr style="background-color:#cccccc;font-weight:bold;text-align:center;">';
                foreach ([
                    '#',
                    $l['w_score'],
                    $l['w_ip'],
                    $l['w_start'],
                    $l['w_end'],
                    $l['w_time'],
                    $l['w_reaction'] . ' [sec]',
                ] as $h) {
                    $html .= '<td>' . htmlspecialchars((string) $h) . '</td>';
                }
                $html .= '</tr><tr style="text-align:center;">';
                foreach ([
                    $itemcount . ' ' . $qtype[$m['question_type'] - 1],
                    $m['testlog_score'],
                    getIpAsString($m['testlog_user_ip']),
                    $display_time,
                    $change_time,
                    $diff_time,
                    $reaction_time,
                ] as $c) {
                    $html .= '<td>' . htmlspecialchars((string) $c) . '</td>';
                }
                $html .= '</tr></table>';

                $html .= '<div style="font-size:8pt;">' . F_decode_tcecode($m['question_description']) . '</div>';
                if (K_ENABLE_QUESTION_EXPLANATION && !empty($m['question_explanation'])) {
                    $html .=
                        '<div style="font-size:8pt;border:0.5px solid #000000;"><b><i><u>'
                        . htmlspecialchars($l['w_explanation'])
                        . '</u></i></b><br/>'
                        . F_decode_tcecode($m['question_explanation'])
                        . '</div>';
                }

                if ($m['question_type'] == 3) {
                    // free-text answer
                    $html .=
                        '<div style="font-size:8pt;border:0.5px solid #000000;">'
                        . F_decode_tcecode($m['testlog_answer_text'])
                        . '</div>';
                } else {
                    $sqla =
                        'SELECT * FROM '
                        . K_TABLE_LOG_ANSWER
                        . ', '
                        . K_TABLE_ANSWERS
                        . ' WHERE logansw_answer_id=answer_id AND logansw_testlog_id='
                        . $m['testlog_id']
                        . ' ORDER BY logansw_order';
                    if ($ra = F_db_query($sqla, $db)) {
                        // width:100% so the answer rows span the full content width,
                        // visually consistent with the full-width stats/info tables.
                        $html .= '<table border="0.5" cellpadding="2" style="width:100%;font-size:8pt;">';
                        $idx = 0;
                        while ($ma = F_db_fetch_array($ra)) {
                            ++$idx;
                            [$marker, $markfill, $index, $idxfill] = $this->answerMarker(
                                (int) $m['question_type'],
                                $ma,
                                $idx,
                            );
                            $mbg = $markfill ? ' background-color:#cccccc;' : '';
                            $ibg = $idxfill ? ' background-color:#cccccc;' : '';
                            $html .= '<tr>';
                            $html .=
                                '<td style="width:6%;text-align:center;'
                                . $mbg
                                . '">'
                                . htmlspecialchars((string) $marker)
                                . '</td>';
                            $html .=
                                '<td style="width:6%;text-align:center;'
                                . $ibg
                                . '">'
                                . htmlspecialchars((string) $index)
                                . '</td>';
                            // Explicit width so the three columns sum to 100%: an auto column would
                            // otherwise default to availableWidth/cols and leave the table ~45% wide.
                            $html .= '<td style="width:88%;">' . F_decode_tcecode($ma['answer_description']) . '</td>';
                            $html .= '</tr>';
                            if (K_ENABLE_ANSWER_EXPLANATION && !empty($ma['answer_explanation'])) {
                                $html .=
                                    '<tr><td colspan="3" style="font-size:7pt;"><b><i><u>'
                                    . htmlspecialchars($l['w_explanation'])
                                    . '</u></i></b><br/>'
                                    . F_decode_tcecode($ma['answer_explanation'])
                                    . '</td></tr>';
                            }
                        }
                        $html .= '</table>';
                    } else {
                        F_display_db_error();
                    }
                }

                if (strlen($m['testlog_comment'] ?? '') > 0) {
                    $html .=
                        '<div style="font-size:8pt;color:#ff0000;border:0.5px solid #000000;">'
                        . F_decode_tcecode($m['testlog_comment'])
                        . '</div>';
                }

                $this->writeReportHTML($html);
                ++$itemcount;
            }
        } else {
            F_display_db_error();
        }

        $stats = F_getTestStat($data['test']['test_id'] ?? 0, 0, $data['user_id'] ?? 0, 0, 0, $data['id'] ?? 0);
        $this->printQuestionStats($stats['qstats'], 1);
    }

    /**
     * Compute the answer marker symbol/fill and index symbol/fill for an answer row.
     * Mirrors the legacy selected/right/position logic.
     *
     * @param int   $qtype Question type (1=MCSA, 2=MCMA, 4=ordering).
     * @param array $ma    Answer log record.
     * @param int   $idx   1-based answer index.
     *
     * @return array{0:string,1:bool,2:string,3:bool} [marker, markerFilled, index, indexFilled]
     */
    protected function answerMarker(int $qtype, array $ma, int $idx): array
    {
        $marker = ' ';
        $markfill = false;
        $right = F_getBoolean($ma['answer_isright']);

        if ($qtype == 4) {
            $marker = $ma['logansw_position'] > 0 ? (string) $ma['logansw_position'] : ' ';
            $markfill = $ma['logansw_position'] > 0 && $ma['logansw_position'] == $ma['answer_position'];
            $index = (string) $ma['answer_position'];
            $idxfill = $markfill;
            return [$marker, $markfill, $index, $idxfill];
        }

        if ($ma['logansw_selected'] > 0) {
            $marker = $right ? '+' : '-';
            $markfill = true;
        } elseif ($qtype == 1) {
            $marker = ' ';
        } elseif ($ma['logansw_selected'] == 0) {
            $marker = $right ? '-' : '+';
        }

        $index = (string) $idx;
        $idxfill = $right;
        return [$marker, $markfill, $index, $idxfill];
    }

    /**
     * Print an SVG statistics graph with a coloured legend.
     * @param string $svgdata SVG graph data (legacy F_getSVGGraphCode input).
     */
    public function printSVGStatsGraph($svgdata): void
    {
        global $l;
        if (preg_match_all('/[x]/', (string) $svgdata, $match) <= 1) {
            return;
        }
        $legend =
            '<div style="text-align:center;font-size:8pt;">'
            . '<span style="background-color:#ff0000;color:#ffffff;">&nbsp;'
            . htmlspecialchars($l['w_score'])
            . '&nbsp;</span> '
            . '<span style="background-color:#0000ff;color:#ffffff;">&nbsp;'
            . htmlspecialchars($l['w_answers_right'])
            . '&nbsp;</span> / '
            . '<span style="background-color:#dddddd;color:#000000;">&nbsp;'
            . htmlspecialchars($l['w_tests'])
            . '&nbsp;</span></div>';
        $this->writeReportHTML($legend);

        // F_getSVGGraphCode() lives in a standalone helper that the PDF report endpoints
        // do not otherwise include; load it on demand so the graph is not silently skipped
        // (the legend above would still print, leaving a confusing legend-without-graph).
        if (!function_exists('F_getSVGGraphCode')) {
            $svgfn = __DIR__ . '/tce_functions_svg_graph.php';
            if (is_file($svgfn)) {
                require_once $svgfn;
            }
        }
        if (!function_exists('F_getSVGGraphCode')) {
            return;
        }
        // Render the SVG at a high native resolution (as the web view does) so its
        // fixed-size axis labels stay small relative to the viewport. Generating it at
        // the content width (~180 units) would map roughly 1 unit -> 1 mm, blowing the
        // labels up to ~30pt and overlapping them.
        $svg = F_getSVGGraphCode(substr((string) $svgdata, 1), 800, 450);
        if (!isset($svg[0]) || $svg[0] !== '<') {
            return;
        }
        // Placement box: full content width, height following the SVG's native aspect
        // so the graph is not distorted regardless of the number of plotted points.
        $natW = 800.0;
        $natH = 450.0;
        if (
            preg_match('/<svg\b[^>]*?\bwidth="([0-9.]+)"[^>]*?\bheight="([0-9.]+)"/', $svg, $mm)
            && (float) $mm[1] > 0.0
        ) {
            $natW = (float) $mm[1];
            $natH = (float) $mm[2];
        }
        $w = $this->contentW;
        $h = ($w * $natH) / $natW;
        try {
            // Move to a fresh page if the fixed-height graph would overrun the
            // writable region (footer band) — addSVG draws a single block the
            // engine does not paginate.
            $region = $this->page->getRegion();
            $regionTop = (float) $region['RY'];
            $regionBottom = $regionTop + (float) $region['RH'];
            if ($this->cursorY > ($regionTop + 0.1) && ($regionBottom - $this->cursorY) < ($h + 2.0)) {
                $this->addReportPage();
            }
            $this->ensureContentFont();
            $page = $this->page->getPage($this->page->getPageId());
            $pageHeight = (float) ($page['height'] ?? 0.0);
            // Pass the SVG inline via the '@' prefix (image-from-string) rather than a
            // temp file: a file path must live in a writable dir that is also inside the
            // engine's allowed-paths, which fails under Apache (PrivateTmp / restricted
            // sys_get_temp_dir), silently dropping the graph. addSVG only builds the SVG
            // object; its content must then be flushed to the page via getSetSVG().
            $soid = $this->addSVG('@' . $svg, $this->contentX, $this->cursorY, $w, $h, $pageHeight);
            $this->page->addContent($this->getSetSVG($soid));
            $this->cursorY += $h + 2.0;
        } catch (\Throwable $e) {
            // Graph is supplementary; ignore rendering failures.
        }
    }

    /**
     * Build a statistics data row (9 columns).
     *
     * @param string $bgcolor Row background colour.
     * @param string $code    Row label (#, M1, M1S1, ...).
     * @param array  $d       Stats record.
     * @param bool   $full    If false, score/time/undisplayed/unrated are blank (answer rows).
     */
    protected function statsRow(string $bgcolor, string $code, array $d, bool $full): string
    {
        $cells = [
            ($d['recurrence'] ?? '') . $this->pctOf($d, 'recurrence_perc'),
            $full
                ? number_format((float) ($d['average_score'] ?? 0), 3, '.', '') . $this->pctOf($d, 'average_score_perc')
                : '',
            $full ? date('i:s', (int) ($d['average_time'] ?? 0)) : '',
            ($d['right'] ?? '') . $this->pctOf($d, 'right_perc'),
            ($d['wrong'] ?? '') . $this->pctOf($d, 'wrong_perc'),
            ($d['unanswered'] ?? '') . $this->pctOf($d, 'unanswered_perc'),
            $full ? ($d['undisplayed'] ?? '') . $this->pctOf($d, 'undisplayed_perc') : '',
            $full ? ($d['unrated'] ?? '') . $this->pctOf($d, 'unrated_perc') : '',
        ];
        $row = '<tr style="background-color:' . $bgcolor . ';">';
        $row .= '<td style="font-family:courier;font-weight:bold;">' . htmlspecialchars($code) . '</td>';
        foreach ($cells as $c) {
            $row .= '<td style="text-align:right;font-family:courier;">' . htmlspecialchars((string) $c) . '</td>';
        }
        return $row . '</tr>';
    }

    /**
     * Build a full-width name/description row (decoded TCEcode HTML).
     */
    protected function statsNameRow(string $bgcolor, string $htmlname): string
    {
        return '<tr style="background-color:' . $bgcolor . ';"><td colspan="9">' . $htmlname . '</td></tr>';
    }

    /**
     * Format the percentage suffix for a given key, or '' when absent.
     */
    protected function pctOf(array $d, string $key): string
    {
        return isset($d[$key]) ? ' ' . F_formatPdfPercentage(floatval($d[$key]), false) : '';
    }
}
