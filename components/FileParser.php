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
}