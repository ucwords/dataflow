<?php


namespace App\DataFlow\Exception;


class ConfigErrorException extends \Exception
{

    /**
     * 异常信息
     * @return string
     */
    public function getErrorMsg()
    {
        return "配置异常！{$this->getMessage()}";
    }

    /**
     * 异常文件
     * @return string
     */
    public function getErrorFile()
    {
        return $this->getFile();
    }

    /**
     * 异常行数
     * @return int
     */
    public function getErrorLine()
    {
        return $this->getLine();
    }

    /**
     * 异常通知
     */
    public function sendException()
    {

    }

}