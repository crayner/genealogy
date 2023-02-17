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
 * Date: 23/09/2022
 * Time: 13:14
 */

namespace App\Manager;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class MarriageSentenceManager
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/09/2022 13:15
 */
class MarriageSentenceManager
{
    /**
     * @var array
     */
    var array $result;

    /**
     * @var array
     */
    var array $spouses;

    /**
     * @var array
     */
    var array $formData;

    /**
     * @var TranslatorInterface
     */
    var TranslatorInterface $translator;

    /**
     * @param array $result
     */
    public function __construct(array $result, array $formData, TranslatorInterface $translator)
    {
        $this->setResult($result)
            ->setFormData($formData)
            ->setTranslator($translator)
            ->parseMarriageSentence();
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
     * @return MarriageSentenceManager
     */
    public function setResult(array $result): MarriageSentenceManager
    {
        $this->result = $result;
        if (!key_exists('spouse', $result)) $result['spouse'] = [];
        $this->setSpouses($result['spouse']);
        return $this;
    }

    /**
     * @return array
     */
    public function getSpouses(): array
    {
        return $this->spouses;
    }

    /**
     * @param array $spouses
     * @return MarriageSentenceManager
     */
    public function setSpouses(array $spouses): MarriageSentenceManager
    {
        $this->spouses = $spouses;
        return $this;
    }

    /**
     * @return void
     */
    private function parseMarriageSentence(): void
    {
        foreach ($this->getSpouses() as $q=>$spouse) {
            $details = [];
            $details['joiner'] = $this->getJoiner();
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

            if ($details['date_status'] === 'invalid' && strlen($details['date']) === 4) {
                $details['date_status'] = 'Year';
            }


            $details['endDate'] = $this->getTranslator()->trans('marriage.endDate',$details);

            $spouse['sentence'] = $details;

            $this->spouses[$q] = $spouse;
        }
        $this->result['spouse'] = $this->getSpouses();

        if (key_exists('profileIdentifier', $this->getFormData()) && !empty($this->getFormData()['profileIdentifier']) && in_array('[[Category: Unmarried]]', $this->getResult()['categories'])) {
            $this->result['categories'] = array_diff($this->getResult()['categories'], ['[[Category: Unmarried]]']);
        }
    }

    /**
     * @return array
     */
    public function getFormData(): array
    {
        return $this->formData;
    }

    /**
     * @param array $formData
     * @return MarriageSentenceManager
     */
    public function setFormData(array $formData): MarriageSentenceManager
    {
        $this->formData = $formData;
        return $this;
    }

    /**
     * @return string
     */
    private function getJoiner(): string
    {
        return key_exists('marriageJoiner', $this->getFormData()) && is_string($this->getFormData()['marriageJoiner']) ? $this->getFormData()['marriageJoiner'] : 'in';
    }

    /**
     * @return TranslatorInterface
     */
    private function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param TranslatorInterface $translator
     * @return MarriageSentenceManager
     */
    private function setTranslator(TranslatorInterface $translator): MarriageSentenceManager
    {
        $this->translator = $translator;
        return $this;
    }
}
