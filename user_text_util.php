#!/usr/bin/php
<?php

require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function($className){
    $way = explode('\\', $className);
    require '.'  . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $way) . '.php';
});

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;
use app\UserTextParser;

class UserTextUtil extends CLI
{
    /**
     * @param Options $options
     */
    protected function setup(Options $options)
    {
        $options->setHelp('Утилита для обработки файлов с юзерами');

        $options->registerArgument('separator', 'Тип разделителя для CSV файлов' . PHP_EOL .
                                    '- comma для запятой' . PHP_EOL .
                                    '- semicolon для точки с запятой' . PHP_EOL
        , false);
        $options->registerArgument('action', 'Тип действия' . PHP_EOL . PHP_EOL .
            'countAverageLineCount - Для каждого пользователя считает среднее количество строк в его текстовых файлах и выводит на экран вместе с именем пользователя.' . PHP_EOL.PHP_EOL.
            'replaceDates - Помещает тексты пользователей в папку ./output_texts, заменяет в каждом тексте даты в формате dd/mm/yy на даты в формате mm-dd-yyyy. Выводит на экран количество совершенных для каждого пользователя замен вместе с именем пользователя.'
            , false);
    }

    /**
     * @param Options $options
     */
    protected function main(Options $options)
    {
        $args = $options->getArgs();
        if (count($args) !== 2) {
            echo $options->help();
        } else {
            $parser = new UserTextParser($args[0]);
            $action = $args[1] . 'Action';
            if (method_exists($parser, $action)) {
                echo $parser->{$action}();
            } else {
                echo $options->help();
            }
        }
    }
}

$cli = new UserTextUtil();
$cli->run();
