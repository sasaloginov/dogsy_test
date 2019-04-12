<?php
namespace components;


class FileParser
{
    /**
     * Считает количество срок в файле
     *
     * @param $file
     * @return int
     */
    protected static function countLines($file)
    {
        $handle = fopen($file, "r");
        $count = 0;
        while (!feof($handle)) {
            $bufer = fread($handle, 1048576);
            $count += substr_count($bufer, "\n");
        }
        fclose($handle);
        $count++;

        return $count;
    }

    /**
     * @param $fileIn
     * @param $fileOut
     * @param $regExpFindDate
     * @param string $dateFormatReplace
     * @return int
     */
    protected static function copyFileWithReplaceDates($fileIn, $fileOut, $regExpFindDate, $dateFormatReplace = 'm-d-Y')
    {
        $handleIn = fopen($fileIn, "r");
        $handleOut = fopen($fileOut, "w");
        $totalCount = 0;
        while (!feof($handleIn)) {
            $bufer = fgets($handleIn);
            if (false !== preg_match_all($regExpFindDate, $bufer, $matches)) {
                foreach ( $matches[0] as $date) {
                    $dDigs = explode('/', $date);
                    $d = $dDigs[0];
                    $m = $dDigs[1];
                    $y = $dDigs[2];

                    if (false === $time = strtotime($m.'/'.$d.'/'.$y)) {
                        continue;
                    }
                    $replacementDate = date($dateFormatReplace, $time);
                    $bufer = str_replace($date, $replacementDate, $bufer, $count);
                    $totalCount += $count;
                }
                fputs($handleOut, $bufer);
            }
        }
        fclose($handleIn);
        fclose($handleOut);

        return $totalCount;
    }
}