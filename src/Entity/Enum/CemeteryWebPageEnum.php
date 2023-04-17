<?php
namespace App\Entity\Enum;

enum CemeteryWebPageEnum: string
{
    case NotUsed = 'NotUsed';
    case FaG = 'FaG';
    case BillionGraves = 'BillionGraves';
    case ACI = 'ACI';
    case CWGC = 'CWGC';
    case Wikipedia = 'Wikipedia';

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return match ($this) {
            CemeteryWebPageEnum::NotUsed => [
                'name' => 'Not Used',
                'key' => null,
                'test' => false,
                'url' => null,
                'prompt' => null,
            ],
            CemeteryWebPageEnum::FaG => [
                'name' => 'Find a Grave',
                'key' => 0,
                'test' => "/^([\d+]{1,9})$/",
                'url' => 'https://www.findagrave.com/cemetery/{cemetery}',
                'prompt' => '{cemetery}'
            ],
            CemeteryWebPageEnum::BillionGraves => [
                'name' => 'Billion Graves',
                'key' => 0,
                'test' => "/^([\d+]{1,8})$/",
                'url' => 'https://billiongraves.com/cemetery/Cemetery/{cemetery}',
                'prompt' => '{cemetery}'
            ],
            CemeteryWebPageEnum::ACI => [
                'name' => 'Australian Cemetery Index',
                'key' => 0,
                'test' => "/^([\d+]{1,6})$/",
                'url' => 'https://austcemindex.com/cemetery?cemid={cemetery}',
                'prompt' => '{cemetery}'
            ],
            CemeteryWebPageEnum::CWGC => [
                'name' => 'Commonwealth War Graves Commission',
                'key' => 0,
                'test' => "/^([\d+]{1,8})$/",
                'url' => 'https://www.cwgc.org/visit-us/find-cemeteries-memorials/cemetery-details/{cemetery}',
                'prompt' => '{cemetery}'
            ],
            CemeteryWebPageEnum::Wikipedia => [
                'name' => 'Wikipedia',
                'key' => 0,
                'test' => "/^Q([\d+]{1,8})$/",
                'url' => 'https://www.wikidata.org/wiki/Special:GoToLinkedPage?site=enwiki&itemid={wikidataid}',
                'prompt' => '{wikidataid}'
            ],
        };
    }

    /**
     * @return bool
     */
    public function isNotUsed(): bool
    {
        if (CemeteryWebPageEnum::NotUsed === $this) return true;
        return false;
    }
}