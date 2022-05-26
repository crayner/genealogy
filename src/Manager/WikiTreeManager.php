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

    /**
     * @var array
     */
    private array $profiles = [];

    /**
     * @var bool
     */
    var bool $doTheSort = false;

    /**
     * @var array
     */
    private array $joiners;

    /**
     * @var WikitreeParser|null
     */
    private ?WikitreeParser $parser = null;

    /**
     * @param array $cemeteries
     * @param array $congregations
     * @param array $locations
     * @param bool $doTheSort
     */
    public function __construct(array $cemeteries, array $congregations, array $locations, bool $doTheSort, array $profiles)
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
        $this->profiles = $profiles;
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

        $result = $this->getParser()->setData($crawler->html())->parse();
        $result['id'] = $data['wikiTreeUserID'];

        if ($result['hints']['private children']) {
            $result = $this->parseWikiTreeFamilyData($result, $data['wikiTreeUserID'], $cookieJar);
        }

        if (!empty($data['interredCemetery'])) {
            $name = $data['interredCemetery'][0];
            if (key_exists($name, $this->getCemeteries())) {
                $name = trim($name);
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
        $this->writeProfile($result);
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

    /**
     * @return array
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    /**
     * @param array $profiles
     */
    public function setProfiles(array $profiles): void
    {
        $this->profiles = $profiles;
    }

    /**
     * @param array $result
     */
    private function writeProfile(array $result)
    {
        $profile = $this->getProfile($result['id']);

        $profile['name'] = $result['name']['full'];

        $profiles = $this->getProfiles();
        $profile['dob'] = $result['birth']['date'];
        $profile['dod'] = $result['death']['date'];
        foreach($result['spouse'] as $q=>$spouse) {
            $profile['dom'][$spouse['ID']]['id'] = $spouse['ID'];
            $profile['dom'][$spouse['ID']]['date'] = $spouse['date'];
            $profile['dom'][$spouse['ID']]['name'] = $spouse['name'];
        }
        $profiles[$result['id']] = $profile;

        $this->setProfiles($profiles);
        $this->writeProfiles();
    }

    /**
     * @param string $id
     * @return array
     */
    private function getProfile(string $id): array
    {
        $profiles = $this->getProfiles();
        $profile = key_exists($id, $profiles) ? $profiles[$id] : [];

        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'id' => $id,
                'name' => '',
                'dob' => null,
                'dom' => [],
                'dod' => null,
            ]
        );

        $resolver->setAllowedTypes('id', 'string');
        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('dod', ['null','string']);
        $resolver->setAllowedTypes('dob', ['null','string']);
        $resolver->setAllowedTypes('dom', ['null','array']);
        $profile = $resolver->resolve($profile);
        $profiles[$id] = $profile;
        $this->setProfiles($profiles);

        return $profile;
    }

    /**
     * @return void
     */
    private function writeProfiles()
    {
        $profiles = [];
        $profiles['parameters']['profiles'] = $this->getProfiles();
        file_put_contents(__DIR__.'/../../config/packages/profiles.yaml', Yaml::dump($profiles, 8));
    }

    /**
     * @return WikitreeParser
     */
    public function getParser(): WikitreeParser
    {
        return $this->parser = $this->parser ?: new WikitreeParser();
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function getJoiners(?string $type = null): array
    {
        switch ($type) {
            case 'passedAway':
                return $this->joiners['passed_away'];
            case 'marriage':
                return $this->joiners['marriage'];
            default:
                return $this->joiners;
        }
    }

    /**
     * @param array $joiners
     * @return WikiTreeManager
     */
    public function setJoiners(array $joiners): WikiTreeManager
    {
        $this->joiners = $joiners;
        return $this;
    }
}
