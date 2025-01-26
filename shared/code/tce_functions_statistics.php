<?php

//============================================================+
// File name   : tce_functions_statistics.php
// Begin       : 2008-12-25
// Last Update : 2023-11-30
//
// Description : Functions to calculate descriptive statistics.
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
 * Functions to calculate descriptive statistics.
 * @package com.tecnick.tcexam.admin
 * @author Nicola Asuni
 * @since 2008-12-25
 */

/**
 * Return an array containing descriptive statistics for the bidimensional input array.
 * @author Nicola Asuni
 * @since 2008-12-25
 * @param $data (array) input data as bidimesional array. The first dimension is a set of data, the second contains data.
 * @return array of statistical results. The keys of the input data are peserved.
 */
function F_getArrayStatistics($data)
{
    $stats = [];
    $stats['number'] = []; // number of items
    $stats['sum'] = []; // sum of all elements
    $stats['mean'] = []; // mean or average value
    $stats['median'] = []; // median
    $stats['mode'] = []; // mode
    $stats['minimum'] = []; // minimum value
    $stats['maximum'] = []; // maximum value
    $stats['range'] = []; // range
    $stats['variance'] = []; // variance
    $stats['standard_deviation'] = []; // standard deviation
    $stats['skewness'] = []; // skewness
    $stats['kurtosi'] = []; // kurtosi
    foreach ($data as $set => $dataset) {
        sort($dataset);
        $stats['number'][$set] = 0;
        $stats['minimum'][$set] = $dataset[0];
        $stats['sum'][$set] = 0;
        $datastr = [];
        foreach ($dataset as $num => $value) {
            ++$stats['number'][$set];
            $stats['sum'][$set] += (float) $value;
            $datastr[] = '' . $value . ''; // convert value to string
        }

        if ($stats['number'][$set] > 0) {
            $stats['maximum'][$set] = $dataset[($stats['number'][$set] - 1)];
            $stats['range'][$set] = (float) $stats['maximum'][$set] - (float) $stats['minimum'][$set];
            $stats['mean'][$set] = $stats['sum'][$set] / $stats['number'][$set];
            $nsdiv = (int) ($stats['number'][$set] / 2);
            if (($nsdiv > 0) && (($stats['number'][$set] % 2) == 0)) {
                $stats['median'][$set] = (((float) $dataset[$nsdiv] + (float) $dataset[($nsdiv - 1)]) / 2);
            } else {
                $stats['median'][$set] = (float) $dataset[(($stats['number'][$set] - 1) / 2)];
            }

            $freq = array_count_values($datastr);
            arsort($freq, SORT_NUMERIC);
            $freq = array_keys($freq);
            $stats['mode'][$set] = (float) $freq[0];
            $dev = 0;
            foreach ($dataset as $num => $value) {
                // deviance
                $dev += ((float) $value - (float) $stats['mean'][$set]) ** 2;
            }

            $stats['variance'][$set] = $dev / $stats['number'][$set];
            $stats['standard_deviation'][$set] = sqrt($stats['variance'][$set]);
            $stats['skewness'][$set] = 0;
            $stats['kurtosi'][$set] = 0;
            if ($stats['standard_deviation'][$set] != 0) {
                foreach ($dataset as $num => $value) {
                    $tmpval = (($value - $stats['mean'][$set]) / $stats['standard_deviation'][$set]);
                    $stats['skewness'][$set] += $tmpval ** 3;
                    $stats['kurtosi'][$set] += $tmpval ** 4;
                }

                $stats['skewness'][$set] /= $stats['number'][$set];
                $stats['kurtosi'][$set] /= $stats['number'][$set];
            }
        }
    }

    return $stats;
}

//============================================================+
// END OF FILE
//============================================================+
