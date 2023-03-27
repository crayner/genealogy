<?php

namespace App\Manager;

interface TemplateManagerInterface
{
    /**
     * getTranslationsDomain
     *
     * @return string
     */
    public function getTranslationDomain(): string;

    /**
     * isLocale
     *
     * @return bool
     */
    public function isLocale(): bool;

    /**
     * getTargetDivision
     *
     * @return string
     */
    public function getTargetDivision(): string;
}