<?php

namespace App\Manager;

class DumpPeopleMarriage
{
    /**
     * @return void
     */
    public function execute()
    {
        $fileName = realpath(__DIR__ . '/../../../dumps/dump_people_marriages.csv');

        $headers = [];

        if ($file = fopen($fileName, "r")) {
            while (!feof($file)) {
                $testLine = explode("\t", trim(fgets($file)));
                if ($headers === []) {
                    $headers = $testLine;
                    continue;
                }
                dump($headers, $testLine);
                break;
            }
        }

        dd($fileName);
    }
}