<?php

//============================================================+
// File name   : tce_functions_svg_check.php
// Begin       : 2026-10-17
// Last Update : 2026-10-17
//
// Description : Content check for uploaded SVG files.
//
// License:
//    Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD
//    See LICENSE file for more information.
//============================================================+

/**
 * @file
 * Functions to check that uploaded SVG files contain only static graphics.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2026-10-17
 */

/**
 * Returns the SVG elements accepted in uploaded SVG files.
 * Script, foreign content, animation, link and font elements are not listed.
 *
 * @return list<string> element local names
 */
function F_get_svg_allowed_elements(): array
{
    return [
        'svg',
        'g',
        'defs',
        'symbol',
        'use',
        'title',
        'desc',
        'metadata',
        'style',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'text',
        'tspan',
        'textPath',
        'image',
        'linearGradient',
        'radialGradient',
        'stop',
        'pattern',
        'clipPath',
        'mask',
        'marker',
        'filter',
        'feBlend',
        'feColorMatrix',
        'feComponentTransfer',
        'feComposite',
        'feConvolveMatrix',
        'feDiffuseLighting',
        'feDisplacementMap',
        'feDistantLight',
        'feDropShadow',
        'feFlood',
        'feFuncA',
        'feFuncB',
        'feFuncG',
        'feFuncR',
        'feGaussianBlur',
        'feImage',
        'feMerge',
        'feMergeNode',
        'feMorphology',
        'feOffset',
        'fePointLight',
        'feSpecularLighting',
        'feSpotLight',
        'feTile',
        'feTurbulence',
    ];
}

/**
 * Returns the attributes without namespace accepted in uploaded SVG files.
 * Attributes named 'data-*' or 'aria-*' are also accepted; 'href' is checked by F_is_safe_svg_href().
 *
 * @return list<string> attribute names
 */
function F_get_svg_allowed_attributes(): array
{
    return [
        // core and structure
        'id',
        'class',
        'style',
        'lang',
        'role',
        'type',
        'version',
        'baseProfile',
        'viewBox',
        'preserveAspectRatio',
        'transform',
        'transform-origin',
        // geometry and text layout
        'x',
        'y',
        'x1',
        'y1',
        'x2',
        'y2',
        'cx',
        'cy',
        'r',
        'rx',
        'ry',
        'fx',
        'fy',
        'fr',
        'z',
        'width',
        'height',
        'd',
        'points',
        'pathLength',
        'dx',
        'dy',
        'rotate',
        'textLength',
        'lengthAdjust',
        'startOffset',
        'method',
        'spacing',
        'side',
        // paint servers, markers, clipping and masking
        'offset',
        'gradientUnits',
        'gradientTransform',
        'spreadMethod',
        'patternUnits',
        'patternContentUnits',
        'patternTransform',
        'clipPathUnits',
        'maskUnits',
        'maskContentUnits',
        'markerUnits',
        'markerWidth',
        'markerHeight',
        'refX',
        'refY',
        'orient',
        // filters
        'filterUnits',
        'primitiveUnits',
        'in',
        'in2',
        'result',
        'mode',
        'values',
        'tableValues',
        'slope',
        'intercept',
        'amplitude',
        'exponent',
        'operator',
        'k1',
        'k2',
        'k3',
        'k4',
        'order',
        'kernelMatrix',
        'kernelUnitLength',
        'divisor',
        'bias',
        'targetX',
        'targetY',
        'edgeMode',
        'preserveAlpha',
        'stdDeviation',
        'surfaceScale',
        'diffuseConstant',
        'specularConstant',
        'specularExponent',
        'scale',
        'xChannelSelector',
        'yChannelSelector',
        'radius',
        'baseFrequency',
        'numOctaves',
        'seed',
        'stitchTiles',
        'azimuth',
        'elevation',
        'pointsAtX',
        'pointsAtY',
        'pointsAtZ',
        'limitingConeAngle',
        // presentation
        'alignment-baseline',
        'baseline-shift',
        'clip',
        'clip-path',
        'clip-rule',
        'color',
        'color-interpolation',
        'color-interpolation-filters',
        'color-rendering',
        'direction',
        'display',
        'dominant-baseline',
        'enable-background',
        'fill',
        'fill-opacity',
        'fill-rule',
        'filter',
        'flood-color',
        'flood-opacity',
        'font',
        'font-family',
        'font-size',
        'font-size-adjust',
        'font-stretch',
        'font-style',
        'font-variant',
        'font-weight',
        'glyph-orientation-horizontal',
        'glyph-orientation-vertical',
        'image-rendering',
        'isolation',
        'kerning',
        'letter-spacing',
        'lighting-color',
        'marker',
        'marker-start',
        'marker-mid',
        'marker-end',
        'mask',
        'mix-blend-mode',
        'opacity',
        'overflow',
        'paint-order',
        'pointer-events',
        'shape-rendering',
        'solid-color',
        'solid-opacity',
        'stop-color',
        'stop-opacity',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'text-decoration',
        'text-rendering',
        'unicode-bidi',
        'vector-effect',
        'visibility',
        'white-space',
        'word-spacing',
        'writing-mode',
    ];
}

/**
 * Returns the RDF metadata namespaces accepted inside the SVG 'metadata' element.
 *
 * @return list<string> namespace URIs
 */
function F_get_svg_metadata_namespaces(): array
{
    return [
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'http://creativecommons.org/ns#',
        'http://web.resource.org/cc/',
        'http://purl.org/dc/elements/1.1/',
        'http://purl.org/dc/terms/',
    ];
}

/**
 * Check that an SVG attribute value or style sheet contains no external reference or script.
 * url() references must point to a fragment (#id); CSS escapes, @import, expression(),
 * behaviors, bindings and script URLs are rejected.
 *
 * @param string $value Attribute value or style sheet text.
 *
 * @return bool True if the value is accepted.
 */
function F_is_safe_svg_value(string $value): bool
{
    return (
        preg_match(
            '/\\\\|@import|expression\s*\(|behavior\s*:|-moz-binding|(?:java|vb)script\s*:|url\s*\(\s*+(?![\'"]?\s*#)/i',
            $value,
        ) !== 1
    );
}

/**
 * Check an SVG 'href' or 'xlink:href' value.
 * The 'image' element accepts only base64 PNG, JPEG or GIF data URLs; other elements accept only
 * fragment references (#id).
 *
 * @param string $element Local name of the element that holds the attribute.
 * @param string $value   Attribute value.
 *
 * @return bool True if the value is accepted.
 */
function F_is_safe_svg_href(string $element, string $value): bool
{
    $value = trim($value);
    if ($element === 'image') {
        return preg_match('#^data:image/(?:png|jpeg|gif);base64,[A-Za-z0-9+/=\s]+$#D', $value) === 1;
    }

    return preg_match('/^#\S+$/D', $value) === 1;
}

/**
 * Check an attribute of an uploaded SVG element.
 *
 * @param DOMElement $element     Element that holds the attribute.
 * @param DOMAttr    $attr        Attribute to check.
 * @param bool       $in_metadata True if the element is inside the SVG 'metadata' element.
 *
 * @return bool True if the attribute is accepted.
 */
function F_is_safe_svg_attribute(DOMElement $element, DOMAttr $attr, bool $in_metadata): bool
{
    $name = (string) $attr->localName;
    $namespace = $attr->namespaceURI;
    if ($name === 'href' && ($namespace === null || $namespace === 'http://www.w3.org/1999/xlink')) {
        return !$in_metadata && F_is_safe_svg_href((string) $element->localName, $attr->value);
    }

    $allowed = match ($namespace) {
        null => !$in_metadata
            && (
                in_array($name, F_get_svg_allowed_attributes(), true)
                || preg_match('/^(?:aria|data)-[a-z0-9_.-]+$/iD', $name) === 1
            ),
        'http://www.w3.org/XML/1998/namespace' => in_array($name, ['space', 'lang'], true),
        'http://www.w3.org/1999/02/22-rdf-syntax-ns#' => $in_metadata,
        default => false,
    };

    return $allowed && F_is_safe_svg_value($attr->value);
}

/**
 * Check the child nodes of an uploaded SVG document or element.
 * Only elements, text, CDATA sections and comments are accepted; elements must be listed in
 * F_get_svg_allowed_elements(), or belong to an RDF metadata namespace inside 'metadata'.
 *
 * @param DOMNode $node        Document or element to check.
 * @param bool    $in_metadata True if the node is inside the SVG 'metadata' element.
 *
 * @return bool True if all child nodes are accepted.
 */
function F_is_safe_svg_node(DOMNode $node, bool $in_metadata): bool
{
    foreach ($node->childNodes as $child) {
        if ($child instanceof DOMText || $child instanceof DOMComment || $child instanceof DOMDocumentType) {
            // DOMCdataSection extends DOMText; the document type is checked by F_is_safe_svg_content()
            continue;
        }

        if (!$child instanceof DOMElement) {
            return false;
        }

        $name = (string) $child->localName;
        $is_svg = $child->namespaceURI === 'http://www.w3.org/2000/svg';
        if ($is_svg) {
            if ($in_metadata || !in_array($name, F_get_svg_allowed_elements(), true)) {
                return false;
            }
        } elseif (!$in_metadata || !in_array($child->namespaceURI, F_get_svg_metadata_namespaces(), true)) {
            return false;
        }

        foreach ($child->attributes ?? [] as $attr) {
            if (!F_is_safe_svg_attribute($child, $attr, $in_metadata)) {
                return false;
            }
        }

        if ($is_svg && $name === 'style' && !F_is_safe_svg_value($child->textContent)) {
            return false;
        }

        if (!F_is_safe_svg_node($child, $in_metadata || $is_svg && $name === 'metadata')) {
            return false;
        }
    }

    return true;
}

/**
 * Check that an SVG file contains only static graphics.
 * The file must be well-formed UTF-8 XML with an 'svg' root element in the SVG namespace.
 * Entity declarations, internal DTD subsets, processing instructions, elements and attributes
 * outside the allowed lists, event handlers, external references and script URLs are rejected.
 * Files saved as "Plain SVG" or "Optimized SVG" pass; editor-specific namespaces do not.
 *
 * @param string $filepath Path of the file to check.
 *
 * @return bool True if the content is accepted.
 */
function F_is_safe_svg_content(string $filepath): bool
{
    if (!class_exists('DOMDocument') || !is_file($filepath) || !is_readable($filepath)) {
        return false;
    }

    $data = file_get_contents($filepath);
    if ($data === false || $data === '' || preg_match('//u', $data) !== 1 || stripos($data, '<!ENTITY') !== false) {
        return false;
    }

    // the declared encoding must be UTF-8
    $match = [];
    if (
        preg_match('/^(?:\xEF\xBB\xBF)?<\?xml\s[^>]*?\bencoding\s*=\s*["\']([^"\']*)["\']/', $data, $match) === 1
        && strtolower($match[1] ?? '') !== 'utf-8'
    ) {
        return false;
    }

    $doc = new DOMDocument();
    $use_errors = libxml_use_internal_errors(true);
    $loaded = $doc->loadXML($data, LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($use_errors);
    if (!$loaded) {
        return false;
    }

    $root = $doc->documentElement;
    if ($root === null || $root->namespaceURI !== 'http://www.w3.org/2000/svg' || $root->localName !== 'svg') {
        return false;
    }

    $doctype = $doc->doctype;
    if ($doctype !== null && ($doctype->name !== 'svg' || trim((string) $doctype->internalSubset) !== '')) {
        return false;
    }

    return F_is_safe_svg_node($doc, false);
}
