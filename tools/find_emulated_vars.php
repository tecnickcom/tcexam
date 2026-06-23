<?php

//============================================================+
// File name   : find_emulated_vars.php
// Begin       : 2026-06-22
//
// Description : Static analysis helper for the Stage 8.2 register-globals removal.
//               For each given PHP file it reports the variables that are READ
//               but never ASSIGNED in that file (excluding superglobals and a
//               safelist of include-provided globals) — i.e. the bare variables
//               that today are fed by the $_POST register-globals emulation in
//               shared/config/tce_config.php and must be read explicitly instead.
//
// Usage       : php tools/find_emulated_vars.php <file.php> [<file.php> ...]
//               Outputs one line per file: "<file>: $a $b $c" (only if any found).
//
// License: AGPL-3.0-or-later (see LICENSE).
//============================================================+

// Variables provided by the runtime / common includes (config, authorization, page header),
// NOT by the POST emulation — never treat these as form inputs.
const SAFE = [
    'l', 'db', 'lang_resources', 'GLOBALS', 'this',
    '_GET', '_POST', '_REQUEST', '_SERVER', '_SESSION', '_COOKIE', '_FILES', '_ENV',
    // Page/scaffolding globals set by included headers/auth/form-functions (NOT form inputs):
    // $menu_mode + $formstatus come from tce_functions_form.php; the rest are controller→fragment
    // scope hand-offs ($enable_calendar, $menu, $thispage_title) or framework state.
    'thispage_title', 'pagelevel', 'menu_mode', 'formstatus', 'enable_calendar', 'menu',
    'pagedir', 'pagenum', 'examateid',
    'http_response_header', 'argv', 'argc',
];

/** True when a token opens a brace scope (incl. string-interpolation "{$" / "${"). */
function isBraceOpen(mixed $t): bool
{
    return $t === '{'
        || (is_array($t) && in_array($t[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true));
}

/**
 * Remove every token that lives inside a function/closure body (signature + body), so the caller
 * only sees top-level code. Brace matching counts interpolation openers so "{$x}" stays balanced.
 *
 * @param array<int,mixed> $tokens
 * @return array<int,mixed>
 */
function stripFunctionBodies(array $tokens): array
{
    $out = [];
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if (! is_array($t) || $t[0] !== T_FUNCTION) {
            $out[] = $t;
            continue;
        }

        // Scan past the signature to the body-opening '{' (or ';' for a body-less declaration).
        $paren = 0;
        $j = $i + 1;
        for (; $j < $n; $j++) {
            $s = $tokens[$j];
            if ($s === '(') {
                $paren++;
            } elseif ($s === ')') {
                $paren--;
            } elseif ($paren === 0 && $s === ';') {
                break; // abstract/interface method: no body
            } elseif ($paren === 0 && $s === '{') {
                // Brace-match the body.
                $depth = 1;
                for ($k = $j + 1; $k < $n; $k++) {
                    if (isBraceOpen($tokens[$k])) {
                        $depth++;
                    } elseif ($tokens[$k] === '}') {
                        $depth--;
                        if ($depth === 0) {
                            $j = $k;
                            break;
                        }
                    }
                }
                break;
            }
        }
        $i = $j; // skip the whole function (and its body)
    }
    return $out;
}

/**
 * Return, per top-level variable, the token index of its first WRITE and first READ.
 * A variable read before it is ever written (firstRead < firstWrite, or never written) was
 * supplied by the register-globals emulation and must now be read explicitly. This catches form
 * fields that are only (re)assigned in a 'clear'/reset branch yet consumed in add/update branches.
 *
 * @return array{0:array<string,int>,1:array<string,int>} [firstWrite, firstRead] index maps
 */
function analyze(array $tokens): array
{
    // The register-globals emulation only creates TOP-LEVEL (file-scope) variables, so drop every
    // token that lives inside a function/closure/arrow-fn body — those are params/locals, a
    // separate scope that cannot see the emulated globals (barring an explicit `global`, which is
    // absent from these controllers).
    $tokens = stripFunctionBodies($tokens);

    $firstWrite = [];
    $firstRead = [];
    $n = count($tokens);

    // Index of the next significant (non-whitespace/comment) token after $i.
    $nextSig = static function (int $i) use ($tokens, $n): int {
        for ($j = $i + 1; $j < $n; $j++) {
            $t = $tokens[$j];
            if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            return $j;
        }
        return $n;
    };

    for ($i = 0; $i < $n; $i++) {
        $tok = $tokens[$i];
        if (! is_array($tok) || $tok[0] !== T_VARIABLE) {
            continue;
        }
        $name = ltrim($tok[1], '$');

        // Look at the previous significant token to detect foreach/global/static/param-by-ref.
        $prev = null;
        for ($p = $i - 1; $p >= 0; $p--) {
            $t = $tokens[$p];
            if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $prev = $t;
            break;
        }

        // Next significant token, to detect "$x =" style assignment.
        $j = $nextSig($i);
        $next = $j < $n ? $tokens[$j] : null;

        // Assignment forms.
        $isAssign = false;
        $isPlainAssign = false; // "$x = ..." (RHS may read $x self-referentially)
        $isCompound = false;    // "$x .= ...", "++$x" etc. always read $x
        if (is_string($next)) {
            if ($next === '=') {
                $isAssign = true;
                $isPlainAssign = true;
            }
        } elseif (is_array($next)) {
            if (in_array($next[0], [
                T_PLUS_EQUAL, T_MINUS_EQUAL, T_MUL_EQUAL, T_DIV_EQUAL, T_CONCAT_EQUAL,
                T_MOD_EQUAL, T_AND_EQUAL, T_OR_EQUAL, T_XOR_EQUAL, T_SL_EQUAL, T_SR_EQUAL,
                T_POW_EQUAL, T_COALESCE_EQUAL, T_INC, T_DEC,
            ], true)) {
                $isAssign = true;
                $isCompound = true; // reads the existing (formerly emulated) value
            }
        }

        // foreach (... as [$k =>] $v), global $x, static $x, by-ref &$x, catch (E $x).
        if (is_array($prev)) {
            if (in_array($prev[0], [T_AS, T_GLOBAL, T_STATIC, T_DOUBLE_ARROW], true)) {
                $isAssign = true;
            }
        } elseif ($prev === '&') {
            $isAssign = true; // reference (&$x) — treated as written
        }

        // A self-referential assignment ("$x = isset($x) ? ... : default", "$x = (int) $x", "$x .= ..")
        // READS the prior (emulation-provided) value, so it does not establish $x independently.
        // Record a read at this position so the var is flagged like any read-before-write.
        $selfRef = $isCompound;
        if ($isPlainAssign) {
            for ($k = $j + 1, $depth = 0; $k < $n; $k++) {
                $tk = $tokens[$k];
                if ($tk === '(' || $tk === '[') {
                    $depth++;
                } elseif ($tk === ')' || $tk === ']') {
                    if ($depth === 0) {
                        break; // exited the enclosing paren (e.g. assignment inside if(...)/while(...))
                    }
                    $depth--;
                } elseif ($depth === 0 && ($tk === ';' || $tk === ',')) {
                    break;
                } elseif (is_array($tk) && $tk[0] === T_VARIABLE && ltrim($tk[1], '$') === $name) {
                    $selfRef = true;
                    break;
                }
            }
        }

        if ($selfRef && ! isset($firstRead[$name])) {
            $firstRead[$name] = $i; // treat as read at the assignment site
        }

        if ($isAssign) {
            if (! isset($firstWrite[$name])) {
                $firstWrite[$name] = $selfRef ? $i + 1 : $i; // self-ref: read precedes the effective write
            }
        } elseif (! isset($firstRead[$name])) {
            $firstRead[$name] = $i;
        }
    }

    return [$firstWrite, $firstRead];
}

$files = array_slice($argv, 1);
foreach ($files as $file) {
    $src = @file_get_contents($file);
    if ($src === false) {
        fwrite(STDERR, "cannot read: {$file}\n");
        continue;
    }
    [$firstWrite, $firstRead] = analyze(token_get_all($src));

    $candidates = [];
    foreach ($firstRead as $name => $readIdx) {
        if (in_array($name, SAFE, true)) {
            continue;
        }
        // Emulation-dependent if read before ever written (or never written).
        if (! isset($firstWrite[$name]) || $readIdx < $firstWrite[$name]) {
            $candidates[] = $name;
        }
    }
    sort($candidates);
    if ($candidates !== []) {
        echo $file . ': $' . implode(' $', $candidates) . "\n";
    }
}
