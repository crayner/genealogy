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
 * Time: 11:57
 */

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class MarriageExtension
 * @package App\Twig\Extension
 * @author  Craig Rayner <craig@craigrayner.com>
 * 31/08/2022 11:57
 */
class MarriageExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private array $result = [];

    /**
     * getFunctions
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('parseMarriageDetails', [$this, 'parseMarriageDetails']),
            new TwigFunction('setResult', [$this, 'setResult']),
        ];
    }

    /**
     * @param array $details
     * @param string $joiner
     * @return array
     */
    public function parseMarriageDetails(array $spouse, string $joiner): array
    {
        $details = [];
        $details['joiner'] = $joiner;
        $details['preferred'] = $this->getResult()['name']['preferred'];
        $details['date'] = $spouse['source'];
        if ($spouse['dateStatus'] === 'full') $details['date'] = $spouse['date'];
        $details['spouse'] =  '\'\'\'[['.$spouse['ID'].'|' . $spouse['nameAtBirth'] . ']]\'\'\'';
        $details['location'] = trim(str_replace(['before', '[uncertain]', 'about'], '', $spouse['location']));
        $details['endDate'] = '';
        $details['end_date_status'] = 'empty';
        if ($spouse['endDate'] !== '') {
            $xx = str_replace(['before', 'after'], '', $spouse['endDate']);
            if ($xx === $spouse['endDate']) {
                $details['endDate'] = date('l, jS F Y', strtotime($spouse['endDate']));
                $details['end_date_status'] = 'full';
            } else {
                $details['endDate'] = $spouse['endDate'];
                $details['end_date_status'] = 'full';
            }
        }
        $details['date_status'] = $spouse['dateStatus'];
        $xx = str_replace(['before'], '', $spouse['location']);
        if ($xx !== $spouse['location']) {
            $details['date_status'] = 'before';
        }
        $xx = str_replace(['about'], '', $spouse['location']);
        if ($xx !== $spouse['location']) {
            $details['date_status'] = 'about';
        }

        return $details;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array $result
     * @return void
     */
    public function setResult(array $result):void
    {
        $this->result = $result;
    }
}