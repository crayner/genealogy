<?php

namespace App\Manager;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Session;

class CategoryManager
{
    /**
     * @var string
     */
    private string $category;

    /**
     * @var array
     */
    private array $profiles;

    /**
     * @var HttpBrowser
     */
    private HttpBrowser $client;

    /**
     * @param Form $form
     * @return void
     */
    public function handleForm(Form $form, Session $session)
    {
        $data = $form->getData();
        $this->setCategory($data['category'])
            ->setProfiles(explode("\r\n", $data['profileList']));

        if ($session->has('cookieJar')) {
            $cookieJar = $session->get('cookieJar');
        } else {
            $cookieJar = null;
        }

        $client = $this->setClient(new HttpBrowser(null, null, $cookieJar))->getClient();
        $url = 'https://www.wikitree.com/index.php?action=edit&title=' . $this->getProfiles()[0];
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
                return false;
            }
            $didLogin = true;
        } else if ($login !== []) {
            $result['error'] = 'You have not logged into the Wikitree site.';
            $result['valid'] = false;
            return false;
        }
        if ($didLogin) {
            $crawler = $client->request("GET", $url);
            $cookieJar = $client->getCookieJar();
        }

        $form = $crawler->selectButton('wpSave')->form();
        $result = $this->parse($form);

        return $result;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function buildCategory(bool $space=true): string
    {
        if ($space)
            return "[[Category: " . $this->getCategory() . "]]";
        else
            return "[[Category:" . $this->getCategory() . "]]";
    }

    /**
     * @param string $category
     * @return CategoryManager
     */
    public function setCategory(string $category): CategoryManager
    {
        $this->category = trim(str_replace(["]]","[[","Category:"], "", $category));
        return $this;
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
     * @return CategoryManager
     */
    public function setProfiles(array $profiles): CategoryManager
    {
        $this->profiles = $profiles;
        return $this;
    }

    /**
     * @param $form
     * @return bool
     */
    private function parse($form)
    {
        $biography = trim($form['wpTextbox1']->getValue());
        if (!(str_contains($biography, $this->buildCategory()) || str_contains($biography, $this->buildCategory(false)))) {
            $biography = $this->buildCategory() . "\n" . $biography;
            $form['wpTextbox1'] = $biography;
            $form['wpSummary'] = 'Categorisation';
            $crawler = $this->getClient()->submit($form);
            $status = $crawler->filterXPath('//div[contains(@class, "status red")]')->evaluate('count(@class)');
            if ($status !== []) return false;
        }
        return true;
    }

    /**
     * @return HttpBrowser
     */
    public function getClient(): HttpBrowser
    {
        return $this->client;
    }

    /**
     * @param HttpBrowser $client
     * @return CategoryManager
     */
    public function setClient(HttpBrowser $client): CategoryManager
    {
        $this->client = $client;
        return $this;
    }
}