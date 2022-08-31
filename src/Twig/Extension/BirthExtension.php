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
class BirthExtension extends AbstractExtension
{
    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('parseBirthDetails', [$this, 'parseBirthDetails']),
        ];
    }

    /**
     * @param array $result
     * @return array
     */
    public function parseBirthDetails(array $result): array
    {
        $details = [];
        $birth = $result['birth'];
        $details['joiner'] = 'in';
        $details['citation'] = '';
        if ($birth['about']) {
            $details['joiner'] = 'about';
            $details['citation'] = '{{Citation Needed}}';
        }
        $details['date'] = $birth['date'];
        $details['name'] = '\'\'\'' . $result['name']['given'];
        if ($result['name']['additional'] !== '') $details['name'] .= ' ' . $result['name']['additional'];
        $details['name'] .= ' ' . $result['name']['atBirth'] . '\'\'\'';
        $details['gender'] = $result['gender'] !== '' ? $result['gender'] : 'not_stated';
        $details['date_status'] = $birth['dateStatus'];
        $details['location'] = $birth['location'];
        $details['father'] = $result['father']['ID'] === '' ? 'birth.unknown.father' : '\'\'\'[[' . $result['father']['ID'] . '|' .$result['father']['name'] . ']]\'\'\'';
        $details['mother'] = $result['mother']['ID'] === '' ? 'birth.unknown.mother' : '\'\'\'[[' . $result['mother']['ID'] . '|' .$result['mother']['name'] . ']]\'\'\'';


        /*        {{ 'birth.sentence'|trans({'{father}': father, '{mother}': mother}) }}
*/
dump($birth,$details);
        return $details;
    }
}