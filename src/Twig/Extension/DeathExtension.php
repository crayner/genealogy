<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: Craig Rayner
 * Date: 31/08/2022
 * Time: 13:48
 */

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class BirthExtension
 * @package App\Twig\Extension
 * @author  Craig Rayner <craig@craigrayner.com>
 * 31/08/2022 13:48
 */
class DeathExtension extends AbstractExtension
{
    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('parseDeathDetails', [$this, 'parseBirthDetails']),
        ];
    }

    /**
     * @param array $result
     * @return array
     */
    public function parseBirthDetails(array $result): array
    {
        $details = [];
        $tense = 'on';
        if ($result['death']['before']) $tense = 'before';
        if ($result['death']['after']) $tense = 'after';
        if ($result['death']['about']) $tense = 'about';
        if ($tense === 'on' && $result['death']['dateStatus'] !== 'full') $tense = 'in';


        $details['tense'] = $tense;
        $details['{preferred'] = $result['name']['preferred'];
        $details['{date}'] = $result['death']['date'];
        $details['{location}'] = $result['death']['location'];
        $details['{joiner}'] = $result['joiner'];
        return $details;
    }
}