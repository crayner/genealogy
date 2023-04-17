<?php
namespace App\Entity\Enum;

enum CategoryWebPageEnum: string
{
    case NotUsed = 'NotUsed';

    case Wikipedia = 'Wikipedia';

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return match ($this) {
            CategoryWebPageEnum::Wikipedia => [
                'name' => 'Wikipedia',
                'key' => 0,
                'test' => "/^Q([\d+]{1,6})$/",
                'url' => 'https://www.wikidata.org/wiki/Special:GoToLinkedPage?site=enwiki&itemid={wikidataid}',
                'prompt' => '{wikidataid}'
            ],
            CategoryWebPageEnum::NotUsed => [
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
        if (CategoryWebPageEnum::NotUsed === $this) return true;
        return false;
    }
}