<?php
namespace App\Entity\Enum;

enum LocationWebPageEnum: string
{
    case NotUsed = 'NotUsed';

    case Wikipedia = 'Wikipedia';

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return match ($this) {
            LocationWebPageEnum::Wikipedia => [
                'name' => 'Wikipedia',
                'key' => 0,
                'test' => "/Q([\d+]{1,6})/",
                'url' => 'https://www.wikidata.org/wiki/Special:GoToLinkedPage?site=enwiki&itemid={wikidataid}',
                'prompt' => '{wikidataid}'
            ],
            LocationWebPageEnum::NotUsed => [
                'name' => 'Not Used',
                'key' => null,
                'test' => false,
                'url' => null,
                'prompt' => null,
            ],

        };
    }

    /**
     * @return bool
     */
    public function isNotUsed(): bool
    {
        if (LocationWebPageEnum::NotUsed === $this) return true;
        return false;
    }
}