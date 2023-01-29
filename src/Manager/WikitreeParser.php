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
 * Date: 8/05/2022
 * Time: 09:07
 */

namespace App\Manager;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WikitreeParser
{
    /**
     * @var string
     */
    private string $data;

    /**
     * @var array
     */
    private array $result;

    /**
     * @return array
     * @throws \Exception
     */
    public function parse(): array
    {
        $crawler = new Crawler($this->getData());

        $content = $crawler->filter('body')->filter('div.VITALS')->each(function ($tr, $i) {
            return $tr;
        });

        $result = [];
        $result['children'] = [];
        $result['categories'] = [];
        $result['templates'] = [];
        $result['hints']['private children'] = false;

        foreach($content as $element) {
            $span = $element->filterXPath('//span[contains(@itemprop, "givenName")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                $result['name']['given'] = $element->filterXPath('//span[contains(@itemprop, "givenName")]')->extract(['_text'])[0];
            }
            $span = $element->filterXPath('//span[contains(@itemprop, "additionalName")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                $result['name']['additional'] = $element->filterXPath('//span[contains(@itemprop, "additionalName")]')->extract(['_text'])[0];
            }
            $span = $element->filterXPath('//a[contains(@title, "Current Last Name")]')->evaluate('count(@title)');
            if ($span !== []) {
                $result['name']['currentLast']= $element->filterXPath('//a[contains(@title, "Current Last Name")]')->extract(['_text'])[0];
            }
            $span = $element->filterXPath('//a[contains(@title, "Last Name at Birth")]')->evaluate('count(@title)');
            if ($span !== []) {
                $result['name']['atBirth'] = $element->filterXPath('//a[contains(@title, "Last Name at Birth")]')->extract(['_text'])[0];
                $result['name']['additional'] = key_exists('additional', $result['name']) ? $result['name']['additional'] : '';
                $result['name']['atBirthFull'] = implode(' ', [$result['name']['given'],$result['name']['additional'],$result['name']['atBirth']]);
            }
            $span = $element->filterXPath('//meta[contains(@itemprop, "familyName")]')->evaluate('count(@itemProp)');
            if ($span !== []) {
                $parents = $element->filterXPath('//meta[contains(@itemprop, "familyName")]')->ancestors();
                $result['name']['full'] = trim(str_replace(["\r","\n"], ' ', $parents->first()->extract(['_text'])[0]));
                $result['name']['family'] = $element->filterXPath('//meta[contains(@itemprop, "familyName")]')->attr('content');
            }
            $span = $element->filterXPath('//time[contains(@itemprop, "birthDate")]')->evaluate('count(@itemprop)');
            $birthDate = false;
            if ($span !== []) {
                $span =  $element->filterXPath('//time[contains(@itemprop, "birthDate")]');
                $parents = $span->ancestors();
                $vital = null;
                foreach ($parents as $node) {
                    if ($node->getAttribute('class') === 'VITALS') {
                        $vital = $node;
                        break;
                    }
                }
                $result['birth']['source'] = $span->attr('datetime');
                $result['birth']['date'] = str_replace('-00', '', $span->attr('datetime'));
                $result['birth']['location'] = $vital ? $vital->textContent : '';
                $birthDate = true;
            }
            $span = $element->filterXPath('//a[contains(@href, "birth-date")]')->evaluate('count(@href)');
            if ($span !== [] && !$birthDate) {
                $span =  $element->filterXPath('//a[contains(@href, "birth-date")]');
                $parents = $span->ancestors();
                $vital = null;
                foreach ($parents as $node) {
                    if ($node->getAttribute('class') === 'VITALS') {
                        $vital = $node;
                        break;
                    }
                }
                $result['birth']['source'] = '';
                $result['birth']['date'] = '';
                $result['birth']['location'] = $vital ? $vital->textContent : '';
            }
            $span = $element->filterXPath('//meta[contains(@itemprop, "gender")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                $result['gender'] = $element->filterXPath('//meta[contains(@itemprop, "gender")]')->attr('content') ?: '';
            }
            $span = $element->filterXPath('//a[contains(@title, "Father:")]')->evaluate('count(@title)');
            if ($span !== []) {
                $x = $element->filterXPath('//a[contains(@title, "Father:")]');
                $result['father']['name'] = $x->extract(['_text'])[0];
                $result['father']['ID'] = str_replace('/wiki/', '', $x->attr('href'));
            }
            $span = $element->filterXPath('//a[contains(@title, "Mother:")]')->evaluate('count(@title)');
            if ($span !== []) {
                $x = $element->filterXPath('//a[contains(@title, "Mother:")]');
                $result['mother']['name'] = $x->extract(['_text'])[0];
                $result['mother']['ID'] = str_replace('/wiki/', '', $x->attr('href'));
            }
            $span = $element->filterXPath('//span[contains(@itemprop, "sibling")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                $result['siblings'] = [];
                foreach($element->filterXPath('//span[contains(@itemprop, "sibling")]') as $q=>$sibling) {
                    $result['siblings'][$q]['name'] = $sibling->textContent;
                    $result['siblings'][$q]['ID'] = str_replace('/wiki/', '', $sibling->firstChild->getAttribute('href'));
                }
            }
            $span = $element->filterXPath('//span[contains(@itemprop, "spouse")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                if (empty($result['spouse'])) $result['spouse'] = [];
                $q = count($result['spouse']);
                $spouse = $element->filterXPath('//span[contains(@itemprop, "spouse")]');
                $anchor = $spouse->filterXPath('//a[contains(@itemprop, "url")]');
                $result['spouse'][$q]['name'] = $anchor->extract(['_text'])[0];
                $result['spouse'][$q]['ID'] = str_replace('/wiki/', '', $anchor->attr('href'));
                $parents = $spouse->ancestors();
                $result['spouse'][$q]['raw'] = $parents->first()->extract(['_text'])[0];
                $result['spouse'][$q]['location'] = trim(str_replace([$result['spouse'][$q]['name'], 'Husband of', 'husband of', "\n", "\r", "wife of", "Wife of", 'married', ' in', ' at'], '', $result['spouse'][$q]['raw']));
                $result['spouse'][$q]['location'] = trim(mb_substr($result['spouse'][$q]['location'], 1));

                $x = explode(' ', $result['spouse'][$q]['location']);
                $result['spouse'][$q]['nameAtBirth'] =  $result['spouse'][$q]['name'];
                if (str_contains($result['spouse'][$q]['nameAtBirth'], '(')) {
                    $name = mb_substr($result['spouse'][$q]['nameAtBirth'], 0, strpos($result['spouse'][$q]['nameAtBirth'], ')'));

                    $result['spouse'][$q]['nameAtBirth'] = str_replace('(', '', $name);
                }

                $date = '';
                foreach ($x as $e=>$r) {
                    if ($e === 0 && intval($r) >= 1) {
                        $date = $r;
                    } else if ($e === 1 && in_array($r, ['Jan','Feb','Mar','Apr','May',"Jun",'Jul','Aug','Sep','Oct','Nov','Dec'])) {
                        $date .= ' ' . $r;
                    } else if ($e === 0 && in_array($r, ['Jan','Feb','Mar','Apr','May',"Jun",'Jul','Aug','Sep','Oct','Nov','Dec'])) {
                        $date .= $r;
                    } else if ($e === 2 and intval($r) > 1770) {
                        $date .= ' ' . $r;
                    } else if ($e === 1 and intval($r) > 1770) {
                        $date .= ' ' . $r;
                    }
                }
                $result['spouse'][$q]['date'] = trim($date);
                $result['spouse'][$q]['location'] = trim(str_replace([trim($date),"[marriage date?]"], '', $result['spouse'][$q]['location']));
                if (mb_substr($result['spouse'][$q]['location'],0,1) === '(') {
                    $toDate = mb_substr($result['spouse'][$q]['location'], 0, strpos($result['spouse'][$q]['location'], ')') + 1);
                    $result['spouse'][$q]['endDate'] =  trim(mb_substr($toDate, 3, strlen($toDate)-4));
                    $result['spouse'][$q]['location'] = trim(str_replace($toDate, '', $result['spouse'][$q]['location']));
                }
            }

            $span = $element->filterXPath('//span[contains(@itemprop, "children")]')->evaluate('count(@itemprop)');
            if ($span !== []) {
                foreach($element->filterXPath('//span[contains(@itemprop, "children")]') as $q=>$sibling) {
                    $result['children'][$q]['name'] = $sibling->textContent;
                    $result['children'][$q]['ID'] = str_replace('/wiki/', '', $sibling->firstChild->getAttribute('href'));
                }
            }

            $span = $element->filterXPath('//span[starts-with(@title, "Daughter")]')->evaluate('count(@title)');
            if ($span !== []) {
                $w = count($result['children']);
                foreach($element->filterXPath('//span[starts-with(@title, "Daughter")]') as $q=>$sibling) {
                    $result['children'][$w + $q]['name'] = $sibling->textContent;
                    $result['children'][$w + $q]['ID'] = 'Private Daughter';
                    $result['hints']['private children'] = true;
                }
            }

            $span = $element->filterXPath('//span[starts-with(@title, "Son")]')->evaluate('count(@title)');
            if ($span !== []) {
                $w = count($result['children']);
                foreach($element->filterXPath('//span[starts-with(@title, "Son")]') as $q=>$sibling) {
                    $result['children'][$w + $q]['name'] = $sibling->textContent;
                    $result['children'][$w + $q]['ID'] = 'Private Son';
                    $result['hints']['private children'] = true;
                }
            }


            $span = $element->filterXPath('//time[contains(@itemprop, "deathDate")]')->evaluate('count(@itemprop)');
            $deathDate = false;
            if ($span !== []) {
                $span =  $element->filterXPath('//time[contains(@itemprop, "deathDate")]');
                $parents = $span->ancestors();
                $vital = null;
                foreach ($parents as $node) {
                    if ($node->getAttribute('class') === 'VITALS') {
                        $vital = $node;
                        break;
                    }
                }
                $result['death']['source'] = $span->attr('datetime');
                $result['death']['date'] = str_replace('-00', '', $span->attr('datetime'));
                $result['death']['location'] = $vital ? $vital->textContent : '';
                $deathDate = true;
            }
            $span = $element->filterXPath('//a[contains(@href, "death-date")]')->evaluate('count(@href)');
            if ($span !== [] && !$deathDate) {
                $span =  $element->filterXPath('//a[contains(@href, "death-date")]');
                $parents = $span->ancestors();
                $vital = null;
                foreach ($parents as $node) {
                    if ($node->getAttribute('class') === 'VITALS') {
                        $vital = $node;
                        break;
                    }
                }
                $result['death']['source'] = '';
                $result['death']['date'] = '';
                $result['death']['location'] = $vital ? $vital->textContent : '';
            }
        }
        $privacy = $crawler->filterXPath('//img[contains(@title, "Privacy ")]')->evaluate('substring-after(@title, "Privacy ")');

        $join = $crawler->filterXPath('//li/a[contains(@href, "joinnetwork")]')->evaluate('count(@href)');
        if ($join !== []) {
            $join = $crawler->filterXPath('//li/a[contains(@href, "joinnetwork")]')->ancestors()->first();
            $result['join'] = $join->text();
        }
        $join = $crawler->filterXPath('//li[@class="GREEN-ARROW"]/a[contains(@href, "Special:TrustedList")]')->evaluate('count(@href)');
        if ($join !== []) {
            $join = $crawler->filterXPath('//li[@class="GREEN-ARROW"]/a[contains(@href, "Special:TrustedList")]')->ancestors()->first();
            $result['join'] = $join->text();
        }
        $result['privacy'] = key_exists(0, $privacy) ? $privacy[0] : 'Error';

        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'birth' => [],
                'children' => [],
                'father' => [],
                'mother' => [],
                'name' => [],
                'siblings' => [],
                'spouse' => [],
                'gender' => '',
                'death' => [],
                'join' => '',
                'privacy' => '',
                'hints' => [],
                'categories' => [],
                'templates' => [],
            ]
        );
        $resolver->setAllowedValues('gender', ['male','female','','unknown','no-gender']);
        $resolver->setAllowedTypes('gender',  'string');
        $resolver->setAllowedTypes('birth',  'array');
        $resolver->setAllowedTypes('children',  'array');
        $resolver->setAllowedTypes('father',  'array');
        $resolver->setAllowedTypes('mother',  'array');
        $resolver->setAllowedTypes('name',  'array');
        $resolver->setAllowedTypes('siblings',  'array');
        $resolver->setAllowedTypes('spouse',  'array');
        $resolver->setAllowedTypes('categories',  'array');
        $resolver->setAllowedTypes('templates',  'array');
        $result = $resolver->resolve($result);
        $resolver->clear();
        $resolver->setDefaults(
            [
                'date' => '',
                'source' => '',
                'location' => '',
                'before' => false,
                'after' => false,
                'about' => false,
            ]
        );
        $resolver->setAllowedTypes('date', ['string', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('source', 'string');
        $resolver->setAllowedTypes('location', 'string');
        $resolver->setAllowedTypes('before', 'boolean');
        $resolver->setAllowedTypes('after', 'boolean');
        $result['birth'] = $resolver->resolve($result['birth']);
        $result['death'] = $resolver->resolve($result['death']);

        $resolver->clear();
        $resolver->setDefaults(
            [
                "given" => "",
                "additional" => "",
                "currentLast" => "",
                "atBirth" => "",
                "atBirthFull" => "",
                "full" => "",
                'nick' => '',
                'preferred' => '',
                'family' => '',
            ]
        );
        $resolver->setAllowedTypes('given', 'string');
        $resolver->setAllowedTypes('additional', 'string');
        $resolver->setAllowedTypes('currentLast', 'string');
        $resolver->setAllowedTypes('atBirth', 'string');
        $resolver->setAllowedTypes('atBirthFull', 'string');
        $resolver->setAllowedTypes('full', 'string');
        $resolver->setAllowedTypes('nick', 'string');
        $resolver->setAllowedTypes('preferred', 'string');
        $resolver->setAllowedTypes('family', 'string');


        $result['name'] = $resolver->resolve($result['name']);

        if (strpos($result['name']['full'], '"') !== false) {
            $nick = explode('"', $result['name']['full']);
            $result['name']['nick'] = $nick[1];
        }
        $preferred = explode('(', $result['name']['full']);
        if (count($preferred) === 2) {
            $result['name']['preferred'] = substr($preferred[1], 0, strpos($preferred[1], ')'));
        }


        $resolver->clear();
        $resolver->setDefaults(
            [
                'ID' => '',
                'name' => '',
                'nameAtBirth' => '',
            ]
        );
        $resolver->setAllowedTypes('ID', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('nameAtBirth', 'string');
        $result['father'] = $resolver->resolve($result['father']);
        $result['mother'] = $resolver->resolve($result['mother']);
        foreach($result['children'] as $q=>$child) {
            $result['children'][$q] = $resolver->resolve($child);
        }
        foreach($result['siblings'] as $q=>$sibling) {
            $result['siblings'][$q] = $resolver->resolve($sibling);
        }
        $resolver->setDefault('date', '');
        $resolver->setDefault('endDate', '');
        $resolver->setDefault('source', '');
        $resolver->setDefault('raw', '');
        $resolver->setDefault('dateStatus', 'invalid');
        $resolver->setDefault('location', '');
        $resolver->setDefault('children', []);
        $resolver->setAllowedTypes('date', ['string', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('endDate', ['string', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('source', 'string');
        $resolver->setAllowedTypes('raw', 'string');
        $resolver->setAllowedTypes('dateStatus', 'string');
        $resolver->setAllowedTypes('location', 'string');
        $resolver->setAllowedTypes('children', 'array');

        foreach($result['spouse'] as $q=>$spouse) {
            $result['spouse'][$q] = $resolver->resolve($spouse);

            if (strtotime($result['spouse'][$q]['date']) !== false) {
                $result['spouse'][$q]['source'] = $result['spouse'][$q]['date'];
                $marriageDate = $result['spouse'][$q]['date'] = new \DateTimeImmutable($result['spouse'][$q]['source']);
                $result['spouse'][$q]['dateStatus'] = 'invalid';
                if ($marriageDate->format('j M Y') === $result['spouse'][$q]['source']) {
                    $result['spouse'][$q]['dateStatus'] = 'full';
                    $result['spouse'][$q]['date'] = $marriageDate->format('l, jS F Y');
                } else if ($marriageDate->format('M Y') === $result['spouse'][$q]['source']) {
                    $result['spouse'][$q]['dateStatus'] = 'monthYear';
                    $result['spouse'][$q]['date'] = $marriageDate->format('M Y');
                } else if ($marriageDate->format('Y') === $result['spouse'][$q]['source']) {
                    $result['spouse'][$q]['dateStatus'] = 'Year';
                    $result['spouse'][$q]['date'] = $marriageDate->format('Y');
                }
            } else {
                $result['spouse'][$q]['date'] = '';
                $result['spouse'][$q]['dateStatus'] = 'invalid';
            }
            if ($result['spouse'][$q]['date'] instanceof \DateTimeImmutable) {
                $result['spouse'][$q]['date'] = '';
                $result['spouse'][$q]['dateStatus'] = 'invalid';
            }
        }

        $birthDate = explode('-',$result['birth']['source']);
        $result['birth']['dateStatus'] = 'invalid';
        $result['birth']['date'] = '';
        $xxx = '';
        if (count($birthDate) === 3) {
            if (intval($birthDate[2] > 0)) {
                $result['birth']['dateStatus'] = 'full';
                $result['birth']['date'] = date('l, jS F Y', strtotime($result['birth']['source']));
                $xxx = date('j M Y', strtotime($result['birth']['source']));
            } else if (intval($birthDate[1] > 0)) {
                $result['birth']['dateStatus'] = 'monthYear';
                $result['birth']['date'] = date('F Y', strtotime(str_replace('-00', '', $result['birth']['source'])));
                $xxx = date('M Y', strtotime(str_replace('-00', '', $result['birth']['source'])));
            } else if (intval($birthDate[0] > 1769)) {
                $result['birth']['dateStatus'] = 'year';
                $result['birth']['date'] = str_replace('-00', '', $result['birth']['source']);
                $xxx = str_replace('-00', '', $result['birth']['source']);
            }
            if (str_contains($result['birth']['location'], 'before')) $result['birth']['before'] = true;
            if (str_contains($result['birth']['location'], 'after')) $result['birth']['after'] = true;
            if (str_contains($result['birth']['location'], 'about')) $result['birth']['about'] = true;
        }

        $result['birth']['location'] = trim(str_replace(["\r","\n",'Born','before','after','[birth date?]','in ','about','[uncertain]', $xxx], '', $result['birth']['location']));
        $result['death']['status'] = false;
        if (str_contains($result['death']['location'], 'Died')) {
            $result['death']['status'] = true;
        }
        $deathDate = explode('-',$result['death']['source']);
        $result['death']['dateStatus'] = 'invalid';
        $result['death']['date'] = '';
        $xxx = '';
        if (count($deathDate) === 3) {
            if (intval($deathDate[2] > 0)) {
                $result['death']['dateStatus'] = 'full';
                $result['death']['date'] = date('l, jS F Y', strtotime($result['death']['source']));
                $xxx = date('j M Y', strtotime($result['death']['source']));
            } else if (intval($deathDate[1] > 0)) {
                $result['death']['dateStatus'] = 'monthYear';
                $result['death']['date'] = date('F Y', strtotime(str_replace('-00', '', $result['death']['source'])));
                $xxx = date('M Y', strtotime(str_replace('-00', '', $result['death']['source'])));
            } else if (intval($deathDate[0] > 1769)) {
                $result['death']['dateStatus'] = 'year';
                $result['death']['date'] = str_replace('-00', '', $result['death']['source']);
                $xxx = str_replace('-00', '', $result['death']['source']);
            }
            if (str_contains($result['death']['location'], 'before')) $result['death']['before'] = true;
            if (str_contains($result['death']['location'], 'after')) $result['death']['after'] = true;
            if (str_contains($result['death']['location'], '[uncertain]') && !$result['birth']['about']) $result['death']['about'] = true;
        }
        $result['death']['location'] = trim(str_replace(['[uncertain]',"\r","\n",'Died','before','after','about','[death date?]', $xxx],'', $result['death']['location']));
        $result['death']['location'] = preg_replace('/^in /',"", $result['death']['location']);
        $result['death']['location'] = preg_replace("/at([ ]{1,2})age([ ]{1,2})(\d.*)in([ ]{1,2})/","", $result['death']['location']);
        $result['death']['location'] = preg_replace("/at([ ]{1,2})age([ ]{1,2})(\d.*)/","", $result['death']['location']);

        $result['age']['status'] = false;
        $result['age']['y'] = 0;
        $result['age']['m'] = 0;
        $result['age']['d'] = 0;
        if ($result['death']['dateStatus'] === 'full' && $result['birth']['dateStatus'] === 'full') {
            $death = new \DateTimeImmutable($result['death']['source']);
            $birth = new \DateTimeImmutable($result['birth']['source']);
            $age = $death->diff($birth);
            $result['age']['y'] = $age->format('%y');
            $result['age']['m'] = $age->format('%m');
            $result['age']['d'] = $age->format('%d');
            $result['age']['status'] = true;
        }
        if (!$result['age']['status'] && $result['death']['dateStatus'] !== 'invalid' && $result['birth']['dateStatus'] !== 'invalid'){
            $result['age']['y'] = intval(substr($result['death']['source'],0,4)) - intval(substr($result['birth']['source'],0,4));
            $result['age']['status'] = true;
        }
        if ($result['birth']['dateStatus'] !== 'invalid' && $result['death']['dateStatus'] === 'invalid') {
            $death = new \DateTimeImmutable("today");
            $birth = new \DateTimeImmutable($result['birth']['source']);
            $age = $death->diff($birth);
            $result['age']['y'] = $age->format('%y');
            $result['age']['m'] = $age->format('%m');
            $result['age']['d'] = $age->format('%d');
            if ($result['age']['y'] < 90) {
                $result['death']['status'] = false;
            }
        }

        if ($result['birth']['about'] || $result['birth']['before']|| $result['birth']['after']) {
            $result['categories'][] = '[[Category: Estimated Birth Date]]';
            $result['categories'][] = '[[Category: Australia, Needs Birth Source Researched]]';
        }
        if ($result['death']['status'] && $result['death']['dateStatus'] === 'invalid' && $result['age']['y'] > 85 && $result['age']['status'] === false) {
            $result['categories'][] = '[[Category: Estimated Death Date]]';
            $result['categories'][] = '[[Category: Australia, Needs Death Source Researched]]';
        }

        if ($result['name']['preferred'] === '') {
            $result['name']['preferred'] = $result['name']['given'];
        }

        // Work out child with the marriage/s

        if (count($result['spouse']) === 1) {
            $result['spouse'][0]['children'] = $result['children'];
            $result['children'] = [];
        } else if (count($result['spouse']) > 1) {
            foreach ($result['spouse'] as $q=>$spouse) {
                $spouseID = explode('-', $spouse['ID']);
                $spouseID = $spouseID[0];
                foreach($result['children'] as $e=>$child) {
                    $childID = explode('-', $child['ID']);
                    $childID = $childID[0];
                    if ($childID === $spouseID) {
                        $key = count($result['spouse'][$q]['children']);
                        $result['spouse'][$q]['children'][$key] = $result['children'][$e];
                        unset($result['children'][$e]);
                    }
                }
            }
        }


        if ($result['name']['atBirth'] === '' && $result['name']['currentLast'] !== '') {
            $result['name']['atBirth'] = $result['name']['currentLast'];
        }

        if ($result['name']['atBirth'] === '' && $result['name']['currentLast'] === '' && $result['name']['family'] !== '') {
            $result['name']['atBirth'] = $result['name']['currentLast'] = $result['name']['family'];
        }

        $result = $this->generateBirthSticker($result);

        if ($result['age']['status']) {
            if ($result['age']['y'] >= 100) {
                $result['templates'][] = '{{Centenarian | age= ' . $result['age']['y'] . ' }}';
            }
            if ($result['age']['y'] < 18) {
                $result['templates'][] = '{{Died Young}}';
                if ($result['age']['y'] < 1) {
                    $result['categories'][] = '[[Category: New South Wales, Infant Mortality]]';
                } else {
                    $result['categories'][] = '[[Category: New South Wales, Child Mortality]]';
                }
            }
            if ($result['spouse'] === [] && $result['age']['y'] >= 30) {
                $result['categories'][] = '[[Category: Unmarried]]';
            }
        }
        if ($result['birth']['date'] instanceof \DateTimeImmutable && $result['birth']['date']->format('Ymd') >= date('Ymd', strtotime("+100 Years"))) {
            $result['templates'][] = '{{Centenarian | age= ' . $result['age']['y'] . '  | living = yes }}';
        }

        $result['valid'] = true;
        if ($result['death']['status'] === false && $result['age']['y'] > 85) {
            $result['categories'][] = '[[Category: Estimated Death Date]]';
            $result['categories'][] = '[[Category: Australia, Needs Death Source Researched]]';
        }

        $this->setResult($result);
        return $this->getResult();
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): WikitreeParser
    {
        $this->data = $data;
        return $this;
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
     */
    public function setResult(array $result): WikitreeParser
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param array $result
     * @return array
     */
    private function generateBirthSticker(array $result): array
    {
        $location = '';

        if ($result['age']['status'] || $result['birth']['dateStatus'] !== 'invalid') {

            if (str_contains($result['birth']['location'], 'Australia')) {
                $states = ['New South Wales', 'Victoria', 'South Australia', 'Western Australia', 'Tasmania', 'Queensland', 'Northern Territory', 'Australian Capital Territory'];
                foreach (explode(',', $result['birth']['location']) as $value) {
                    if (in_array(trim($value), $states)) {
                        $location = str_replace(['Australian '],'',trim($value));
                        break;
                    }
                }

                if (intval(substr($result['birth']['source'], 0, 4)) >= 1901) {
                    $result['templates'][] = '{{Australia Sticker|' . $location . '}}';
                } else {
                    if ($location !== "") $result['templates'][] = '{{Australia Born in Colony|colony=Colony of ' . $location . '}}';
                }
            }

            if (str_contains($result['birth']['location'], 'United Kingdom')) {
                if (str_contains($result['birth']['location'], 'England')) {
                    $place = explode(',', $result['birth']['location']);
                    if (count($place) > 2) {
                        $result['templates'][] = '{{England Sticker|' . trim($place[1]) . '|' . trim($place[0]) . '}}';
                    } else {
                        $result['templates'][] = '{{England Sticker}}';
                    }
                } else if (str_contains($result['birth']['location'], 'Wales')) {
                    $place = explode(',', $result['birth']['location']);
                    $result['templates'][] = '{{Wales Sticker|' . trim($place[1]) . '|' . trim($place[0]) . '}}';
                } else if (str_contains($result['birth']['location'], 'Scotland')) {
                    $place = explode(',', $result['birth']['location']);
                    $result['templates'][] = '{{Scotland Sticker|' . trim($place[1]) . '|' . trim($place[0]) . '}}';
                } else if (str_contains($result['birth']['location'], 'Ireland')) {
                    $result['templates'][] = '{{Ireland Native}}';
                } else {
                    $result['templates'][] = '{{United Kingdom Sticker}}';
                }
            } else if (str_contains($result['birth']['location'], 'England')) {
                $place = explode(',', $result['birth']['location']);
                if (count($place) > 2) {
                    $result['templates'][] = '{{England Sticker|' . trim($place[1]) . '|' . trim($place[0]) . '}}';
                } else {
                    $result['templates'][] = '{{England Sticker}}';
                }
            } else if (str_contains($result['birth']['location'], 'Ireland')) {
                $result['templates'][] = '{{Ireland Native}}';
            }


            if (str_contains($result['birth']['location'], 'New Zealand')) {
                $place = explode(',', $result['birth']['location']);
                if (count($place) === 3 && trim($place[2]) === 'New Zealand') {
                    $result['templates'][] = '{{New Zealand Sticker|region=' . trim($place[1]) . '|place=' . trim($place[0]) . '}}';
                } else {
                    $result['templates'][] = '{{New Zealand Sticker}}';
                }
            }
        }

        return $result;
    }
}
