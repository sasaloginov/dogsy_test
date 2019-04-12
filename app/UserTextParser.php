<?php
namespace app;

use components\FileParser;
use splitbrain\phpcli\Exception;

/**
 * Class UserTextParser
 * @package app
 */
class UserTextParser extends FileParser implements UserTextParserInterface
{
    protected $rootDirName = 'data';
    protected $peopleFileName = 'people.csv';
    protected $textsDirName = 'texts';
    protected $outputsDirName = 'output_texts';

    protected $fullTextsDir;
    protected $fullOutputsDir;
    protected $fullPeopleFile;

    protected $separator;
    protected $allowSeparators = [
        'comma' => ',',
        'semicolon' => ';',
    ];

    public function __construct($separator)
    {
        if (false === $this->setSeparator($separator)) {
            throw new Exception('Разделитель не поддерживается');
        }
        $this->fullPeopleFile =  __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->rootDirName . DIRECTORY_SEPARATOR . $this->peopleFileName;
        $this->fullTextsDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->rootDirName . DIRECTORY_SEPARATOR . $this->textsDirName;
        $this->fullOutputsDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->rootDirName . DIRECTORY_SEPARATOR . $this->outputsDirName;
        $this->createOutputsDir();
    }

    /**
     * @param $separator
     * @return bool
     */
    public function setSeparator($separator)
    {
        if (in_array($separator, array_keys($this->allowSeparators))) {
            $this->separator = $this->allowSeparators[$separator];
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function countAverageLineCountAction()
    {
        if (($handle = fopen($this->fullPeopleFile, "r")) !== false) {

            $result = 'Среднее количество строк в файлах пользователей' . PHP_EOL . PHP_EOL;

            while (($data = fgetcsv($handle, 0, $this->separator, chr(8))) !== false) {
                if (count($data) === 2) {
                    $userId = $data[0];
                    $userName = $data[1];

                    $result .= $userName . ': ';

                    $textFiles = $this->getTextsFilesByUserId();

                    $cntLines = 0;
                    if (count($textFiles[$userId]) > 0) {
                        foreach ($textFiles[$userId] as $file) {
                            $cntLines += self::countLines($this->fullTextsDir . DIRECTORY_SEPARATOR . $file);
                        }
                        $result .= $cntLines / count($textFiles[$userId]);
                    } else {
                        $result .= 'нет файлов для этого юзера';
                    }
                    $result .= PHP_EOL;
                } else {
                    break;
                }
            }
            fclose($handle);
        } else {
            $result = 'Не найден файл с пользователями';
        }

        return $result;
    }

    public function replaceDatesAction()
    {

        if (($handle = fopen($this->fullPeopleFile, "r")) !== false) {

            $result = 'Произведено замен дат в файлах пользователей' . PHP_EOL . PHP_EOL;

            while (($data = fgetcsv($handle, 0, $this->separator, chr(8))) !== false) {
                if (count($data) === 2) {
                    $userId = $data[0];
                    $userName = $data[1];

                    $result .= $userName . ': ';
                    $textFiles = $this->getTextsFilesByUserId();
                    $cntReplaces = 0;
                    if (count($textFiles[$userId]) > 0) {
                        foreach ($textFiles[$userId] as $file) {

                            $cntReplaces += self::copyFileWithReplaceDates(
                                $this->fullTextsDir . DIRECTORY_SEPARATOR . $file,
                                $this->fullOutputsDir . DIRECTORY_SEPARATOR . $file,
                                "/\d{2}\/\d{2}\/\d{2}/"
                            );

                        }
                        $result .= $cntReplaces;
                    } else {
                        $result .= 'нет файлов для этого юзера';
                    }
                    $result .= PHP_EOL;
                } else {
                    break;
                }
            }
            fclose($handle);
        } else {
            $result = 'Не найден файл с пользователями';
        }

        return $result;

    }

    /**
     * @return array
     */
    public function getTextsFilesByUserId()
    {
        $files = scandir($this->fullTextsDir);
        $filesByUserId = [];
        foreach ($files as $file) {
            $fUserId = substr($file, 0,  stripos($file, '-'));
            if (!isset($filesByUserId[$fUserId])) {
                $filesByUserId[$fUserId] = [];
            }
            $filesByUserId[$fUserId][] = $file;
        }

        return $filesByUserId;
    }

    protected function createOutputsDir()
    {
        if (false === file_exists($this->fullOutputsDir)) {
            if (false === mkdir($this->fullOutputsDir)) {
                throw new Exception('Не удается создать директорию ' . $this->fullOutputsDir);
            }
        }
    }

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