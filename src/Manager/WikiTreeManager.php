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
 * Date: 16/12/2021
 * Time: 12:54
 */

namespace App\Manager;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class WikiTreeManager
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 16/12/2021 12:55
 */
class WikiTreeManager
{
    /**
     * @var array
     */
    var array $cemeteries = [];

    /**
     * @var array
     */
    var array $congregations = [];

    /**
     * @var array
     */
    var array $locations = [];

    var bool $doTheSort = false;

    /**
     * @param array $cemeteries
     * @param array $congregations
     * @param array $locations
     * @param bool $doTheSort
     */
    public function __construct(array $cemeteries, array $congregations, array $locations, bool $doTheSort)
    {
        foreach($cemeteries as $name=>$details) {
            if (!key_exists('name', $details)) {
                $cemeteries[$name]['name'] = $details['category'];
            }
        }

        $this->setDoTheSort($doTheSort);
        $this->cemeteries = $cemeteries;
        $this->congregations = $congregations;
        sort($locations);
        $this->locations = $locations;
        if ($this->isDoTheSort()) {
            $this->writeParameters();
        }
    }

    /**
     * @return array
     */
    public function getCemeteries(): array
    {
        return $this->cemeteries;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCemeteryCategory(string $name): string
    {
        return key_exists($name, $this->cemeteries) ? $this->cemeteries[$name]['category'] : 'No cemetery selected.';
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCemeteryName(string $name): string
    {
        return key_exists($name, $this->cemeteries) ? $this->cemeteries[$name]['name'] : 'No cemetery selected.';
    }

    /**
     * @return array
     */
    public function getCongregations(): array
    {
        return $this->congregations;
    }

    /**
     * @param string $data
     * @return void
     */
    public function parseWikiTreeData(string $data): array
    {
        $crawler = new Crawler($data);

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
                $result['spouse'][$q]['location'] = trim(str_replace([$result['spouse'][$q]['name'], 'Husband of', 'husband of', "\n", "\r", "wife of", "Wife of", 'married', ' in', ' at'], '', $parents->first()->extract(['_text'])[0]));
                $result['spouse'][$q]['location'] = trim(mb_substr($result['spouse'][$q]['location'], 1));

                $x = explode(' ', $result['spouse'][$q]['location']);
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
        $resolver->setAllowedValues('gender', ['male','female','','unknown']);
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
            ]
        );
        $resolver->setAllowedTypes('ID', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $result['father'] = $resolver->resolve($result['father']);
        $result['mother'] = $resolver->resolve($result['mother']);
        foreach($result['children'] as $q=>$child) {
            $result['children'][$q] = $resolver->resolve($child);
        }
        foreach($result['siblings'] as $q=>$sibling) {
            $result['siblings'][$q] = $resolver->resolve($sibling);
        }
        $resolver->setDefault('date', '');
        $resolver->setDefault('source', '');
        $resolver->setDefault('dateStatus', 'invalid');
        $resolver->setDefault('location', '');
        $resolver->setDefault('children', []);
        $resolver->setAllowedTypes('date', ['string', \DateTimeImmutable::class]);
        $resolver->setAllowedTypes('source', 'string');
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
            if (str_contains($result['death']['location'], 'about')) $result['death']['about'] = true;
        }
        $result['death']['location'] = trim(str_replace(['[uncertain]',"\r","\n",'Died','before','after','about','[death date?]','in ', $xxx],'', $result['death']['location']));

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

        $states = ['New South Wales','Victoria','South Australia','Western Australia','Tasmania','Queensland','Northern Territory','Australian Capital Territory'];
        $location = '';


        if ($result['age']['status'] || $result['birth']['dateStatus'] !== 'invalid') {
            foreach(explode(',', $result['birth']['location']) as $value) {
                if (in_array(trim($value), $states)) {
                    $location = trim($value);
                    break;
                }
            }
            if (intval(substr($result['birth']['source'], 0, 4)) >= 1901) {
                $result['templates'][] = '{{Australia Sticker|'.$location.'}}';
            } else {
                $result['templates'][] = '{{Australia Born in Colony|colony=Colony of '.$location.'}}';
            }
        }
        if ($result['age']['status']) {
            if ($result['age']['y'] >= 100) {
                $result['templates'][] = '{{Centenarian |age= 100 |living= no }}';
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
        } else if ($result['birth']['date'] instanceof \DateTimeImmutable && $result['birth']['date']->format('Ymd') >= date('Ymd', strtotime('1901-01-01'))) {
            $result['templates'][] = '{{Australia Sticker|New South Wales}}';
        } else if ($result['birth']['date'] instanceof \DateTimeImmutable && $result['birth']['date']->format('Ymd') < date('Ymd', strtotime('1901-01-01'))) {
            $result['templates'][] = '{{Australia Born in Colony|colony=Colony of New South Wales}}';
        }
        $result['valid'] = true;
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    public function login(array $data, Session $session): array
    {
        if ($session->has('cookieJar')) {
            $cookieJar = $session->get('cookieJar');
        } else {
            $cookieJar = null;
        }

        $client = new HttpBrowser(null, null, $cookieJar);
        $url = 'https://www.wikitree.com/wiki/' . $data['wikiTreeUserID'];
        $crawler = $client->request("GET", $url);

        $login = $crawler->filterXPath('//a[contains(@href, "Special:Userlogin")]')->evaluate('count(@href)');
        $didLogin = false;

        if ($login !== [] && key_exists('wikiTreeUser', $data)) {
            $crawler = $client->request('GET', 'https://www.wikitree.com/index.php?title=Special:Userlogin');

            // select the form and fill in some values
            $form = $crawler->selectButton('wpLoginattempt')->form();
            $form['wpEmail'] = $data['wikiTreeUser'];
            $form['wpPassword'] = $data['wikiTreePassword'];
            $form['wpRemember']->tick();

            // submit that form
            $crawler = $client->submit($form);
            $cookieJar = $client->getCookieJar();
            $status = $crawler->filterXPath('//div[contains(@class, "status red")]')->evaluate('count(@class)');

            //has the login been successful.
            if ($status !== []) {
                $result['error'] = $crawler->filterXPath('//div[contains(@class, "status red")]')->text();
                $result['valid'] = false;
                return $result;
            }
            $didLogin = true;
        } else if ($login !== []) {
            $result['error'] = 'You have not logged into the Wikitree site.';
            $result['valid'] = false;
            return $result;
        }

        if ($didLogin) {
            $crawler = $client->request("GET", $url);
            $cookieJar = $client->getCookieJar();
        }

        $result = $this->parseWikiTreeData($crawler->html());

        if ($result['hints']['private children']) {
            $result = $this->parseWikiTreeFamilyData($result, $data['wikiTreeUserID'], $cookieJar);
        }

        if (!is_null($data['interredCemetery'])) {
            if (key_exists($data['interredCemetery'], $this->getCemeteries())) {
                $name = trim($data['interredCemetery']);
                $result['categories'][] = '[[Category: ' . $this->getCemeteryCategory($name) . ']]';
                $result['interredSite'] = trim($this->getCemeteryName($name) . ', ' . $data['interredLocation'], " ,.");
            }
        } else {
            $result['interredSite'] = '';
        }
        foreach ($data['congregations'] as $congregation) {
            $category = '[[Category: '.$this->getCongregation($congregation).']]';
            if (array_search($category, $result['categories']) === false) {
                $result['categories'][] = $category;
            }
        }

        foreach ($data['locations'] as $location) {
            $category = '[[Category: '.$location.']]';
            if (array_search($category, $result['categories']) === false) {
                $result['categories'][] = $category;
            }
        }

        $result['page'] = $data['raynerPage'] ?: null;
        $result['baptism']['date'] = $data['baptismDate'] instanceof \DateTimeImmutable ? $data['baptismDate']->format('l, jS F Y') : null;
        $result['baptism']['location'] = $data['baptismLocation'] === '' ? null : $data['baptismLocation'];
        $result['didLogin'] = $didLogin;
        $result['cookieJar'] = $cookieJar;
        $session->set('cookieJar',$cookieJar);
        $session->set('result', $result);
        $session->save();
        return $result;
    }

    /**
     * @param $result
     * @param $wikitreeID
     * @return array
     */
    private function parseWikiTreeFamilyData(array $result, string $wikitreeID, $cookieJar): array
    {
        $wikitreeID = implode('-Family-Tree-', explode('-', $wikitreeID));

        $client = new HttpBrowser(null, null, $cookieJar);
        $url = 'https://www.wikitree.com/genealogy/' . $wikitreeID;
        $crawler = $client->request("GET", $url);
        $content = $crawler->filter('body')->filter('div.container')->filter('p')->each(function ($tr, $i) {
            return $tr;
        });
        $children = [];

        foreach($content as $element) {
            $text = $element->extract(['_text']);
            if (str_contains($text[0], 'Mother of') || str_contains($text[0], 'Father of')) {

                $id = $element->filterXPath('//a[contains(@title, "Go to Profile for ")]')->evaluate('substring-after(@title, "Go to Profile for ")');
                foreach ($id as $q=>$w) {
                    $children[$q]['ID'] = $w;
                    $children[$q]['name'] = $element->filterXPath('//a[contains(@title, "Go to Profile for ")]')->extract(['_text'])[$q];
                }
                break;
            }
        }
        foreach ($children as $q=>$child) {
            if ($child === null) unset($children[$q]);
        }

        if (count($children) >= count($result['children'])) {
            foreach ($result['children'] as $e => $r) {
                $result['children'][$e] = array_shift($children);
            }
        }

        foreach ($result['spouse'] as $q=>$spouse) {
            if (count($children) >= count($spouse['children'])) {
                foreach ($spouse['children'] as $w => $child) {
                    $result['spouse'][$q]['children'][$w] = array_shift($children);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $congregation
     * @return string
     */
    private function getCongregation(string $congregation): string
    {
        if (key_exists($congregation, $this->getCongregations())) {
            return $this->getCongregations()[$congregation]['category'];
        }
        return '';
    }

    /**
     * @return array
     */
    public function getLocations(): array
    {
        return $this->locations ?: [];
    }

    /**
     * @return bool
     */
    public function isDoTheSort(): bool
    {
        return $this->doTheSort;
    }

    /**
     * @param bool $doTheSort
     * @return WikiTreeManager
     */
    public function setDoTheSort(bool $doTheSort): WikiTreeManager
    {
        $this->doTheSort = $doTheSort;
        return $this;
    }

    private function writeParameters()
    {
        $result = [];
        $result['parameters']['locations'] = $this->getLocations();
        $result['parameters']['locations'] = array_values(array_unique($result['parameters']['locations']));
        file_put_contents(__DIR__ . '/../../config/packages/locations.yaml', Yaml::dump($result, 8, 4));

        $result = [];
        $result['parameters']['congregations'] = $this->getCongregations();
        ksort($result['parameters']['congregations']);
        file_put_contents(__DIR__ . '/../../config/packages/congregations.yaml', Yaml::dump($result, 8, 4));

        $result = [];
        $result['parameters']['cemeteries'] = $this->getCemeteries();
        ksort($result['parameters']['cemeteries']);
        file_put_contents(__DIR__ . '/../../config/packages/cemeteries.yaml', Yaml::dump($result, 8, 4));

    }
}