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

/**
 * Class WikiTreeManager
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 16/12/2021 12:55
 */
class WikiTreeManager
{
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
            }
            $span = $element->filterXPath('//time[contains(@itemprop, "birthDate")]')->evaluate('count(@itemprop)');
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
                $result['birth']['date'] = str_replace('-00', -'01', $span->attr('datetime'));
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
                $result['spouse'][$q]['location'] = trim(str_replace([$result['spouse'][$q]['name'], 'Husband of', "\n", "\r", "Wife of", 'married', ' in', ' at'], '', $parents->first()->extract(['_text'])[0]));
                $result['spouse'][$q]['location'] = trim(mb_substr($result['spouse'][$q]['location'], 1));
                $result['spouse'][$q]['date'] = substr($result['spouse'][$q]['location'], 0, 11);
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

                $result['death']['date'] = str_replace('-00', -'01', $span->attr('datetime'));
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
        $result = $resolver->resolve($result);
        $resolver->clear();
        $resolver->setDefaults(
            [
                'date' => '',
                'location' => '',
                'before' => false,
                'after' => false,
            ]
        );
        $resolver->setAllowedTypes('date', 'string');
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
            ]
        );
        $resolver->setAllowedTypes('given', 'string');
        $resolver->setAllowedTypes('additional', 'string');
        $resolver->setAllowedTypes('currentLast', 'string');
        $resolver->setAllowedTypes('atBirth', 'string');
        $resolver->setAllowedTypes('full', 'string');
        $resolver->setAllowedTypes('nick', 'string');
        $resolver->setAllowedTypes('preferred', 'string');

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
        $resolver->setDefault('location', '');
        $resolver->setDefault('children', []);
        $resolver->setAllowedTypes('date', 'string');
        $resolver->setAllowedTypes('location', 'string');
        $resolver->setAllowedTypes('children', 'array');
        foreach($result['spouse'] as $q=>$spouse) {
            $result['spouse'][$q] = $resolver->resolve($spouse);

            if (strtotime($result['spouse'][$q]['date']) !== false) {
                $result['spouse'][$q]['location'] = str_replace($result['spouse'][$q]['date'], '', $result['spouse'][$q]['location']);
                $result['spouse'][$q]['date'] = new \DateTimeImmutable($result['spouse'][$q]['date']);
            } else {
                $result['spouse'][$q]['date'] = '';
            }
        }
        if (strtotime($result['birth']['date']) !== false) {
            $result['birth']['date'] = new \DateTimeImmutable($result['birth']['date']);
            if (str_contains($result['birth']['location'], 'before')) $result['birth']['before'] = true;
            if (str_contains($result['birth']['location'], 'after')) $result['birth']['after'] = true;
            $result['birth']['location'] = trim(str_replace(["\r","\n",'Born','before', 'in ', $result['birth']['date']->format('j M Y')],'', $result['birth']['location']), ' -0123456789');
        }


        if (strtotime($result['death']['date']) !== false) {
            if (str_contains($result['death']['location'], 'before')) $result['death']['before'] = true;
            if (str_contains($result['death']['location'], 'after')) $result['death']['after'] = true;
            $result['death']['date'] = new \DateTimeImmutable($result['death']['date']);
            $result['death']['location'] = trim(str_replace(["\r","\n",'Died','before', 'in ', $result['death']['date']->format('j M Y')],'', $result['death']['location']), ' -0123456789');
            $age = $result['death']['date']->diff($result['birth']['date']);
            $result['age']['y'] = $age->format('%y');
            $result['age']['m'] = $age->format('%m');
            $result['age']['d'] = $age->format('%d');
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

        $result['age']['valid'] = false;
        if ($result['birth']['date'] instanceof \DateTimeImmutable && $result['death']['date'] instanceof \DateTimeImmutable) {
            if ($result['birth']['before'] === false && $result['birth']['after'] === false) {
                if ($result['death']['before'] === false && $result['death']['after'] === false) $result['age']['valid'] = true;
            }
        }

        if ($result['name']['atBirth'] === '' && $result['name']['currentLast'] !== '') $result['name']['atBirth'] = $result['name']['currentLast'];

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

        $result['interredSite'] = $data['interredSite'] ?: '';

        $result['didLogin'] = $didLogin;
        $result['cookieJar'] = $cookieJar;
        $session->set('cookieJar',$cookieJar);
        $session->set('result', $result);
        $session->save();
        return $result;
    }
}