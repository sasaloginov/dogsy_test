<?php
namespace app;


interface UserTextParserInterface
{
    public function setSeparator($separator);

    /**
     * Для каждого пользователя считает среднее количество строк в его текстовых файлах
     *
     * @return string
     */
    public function countAverageLineCountAction();

    /**
     * Помещает тексты пользователей в папку вывода, заменяет в каждом тексте даты в формате dd/mm/yy на даты в
     * формате mm-dd-yyyy
     *
     * @return string
     */
    public function replaceDatesAction();
}