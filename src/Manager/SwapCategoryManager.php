<?php

namespace App\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SwapCategoryManager extends CategoryManager
{

    /**
     * @param array $loader
     * @param RequestStack $stack
     * @param LoggerInterface $wikitreeLogger
     */
    public function __construct(array $loader, RequestStack $stack, LoggerInterface $wikitreeLogger)
    {
        parent::__construct($loader, $stack, $wikitreeLogger);
    }

    /**
     * @param Form $form
     * @return void
     */
    public function swapNextCategory(FormInterface $form): bool
    {
        $data = $form->getData();
        

        if ($this->getSession()->has('cookieJar')) {
            $cookieJar = $this->getSession()->get('cookieJar');
        } else {
            $cookieJar = null;
        }

        if ($this->firstProfile() === "No profile set.") {
            return true;
        }

        $client = $this->setClient(new HttpBrowser(null, null, $cookieJar))->getClient();
        $url = 'https://www.wikitree.com/index.php?action=edit&title=' . $this->firstProfile();
        $crawler = $client->request("GET", $url);
        $login = $crawler->filterXPath('//a[contains(@href, "Special:Userlogin")]')->evaluate('count(@href)');
        $didLogin = false;
        if ($login !== [] && $crawler->filterXPath('//a[contains(@href, "Special:Userlogin")]')->text() === "login using a new window") $login = [];

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
            if ($status !== [] || strpos($crawler->getUri(), "errcode=blocked") !== false) {
                $this->setError($crawler->filterXPath('//div[contains(@class, "status red")]')->text());
                return false;
            }
            $didLogin = true;
        } else if ($login !== []) {
            $result['error'] = 'You have not logged into the Wikitree site.';
            $this->setError('You have not logged into the Wikitree site.');
            return false;
        }
        if ($didLogin) {
            $crawler = $client->request("GET", $url);
        }

        $status = $crawler->filterXPath('//div[contains(@class, "status red")]')->evaluate('count(@class)');
        if ($status !== [] && $crawler->filterXPath('//div[contains(@class, "status red")]')->text() === "You do not have permission to edit this profile. Request to join the Trusted List.") {
            $this->setError($crawler->filterXPath('//div[contains(@class, "status red")]')->text());
            return false;
        }
        $form = $crawler->selectButton('wpSave')->form();
        $result = $this->swap($form);

        $this->nextProfile()
            ->getSession()->set("cookieJar", $this->getClient()->getCookieJar());

        return $result;
    }

    /**
     * @param $form
     * @return bool
     */
    private function swap($form): bool
    {
        $biography = trim($form['wpTextbox1']->getValue());
        if (str_contains($biography, $this->buildCategory()) || str_contains($biography, $this->buildCategory(false))) {
            $biography = str_replace([$this->buildCategory(), $this->buildCategory(false)], [$this->swapCategory(), $this->swapCategory(false)], $biography);
            $form['wpTextbox1'] = $biography;
            $form['wpSummary'] = 'Categorisation';
            $crawler = $this->getClient()->submit($form);
            $status = $crawler->filterXPath('//div[contains(@class, "status red")]')->evaluate('count(@class)');
            if ($status !== [] && !$crawler->filterXPath('//div[contains(@class, "status red")]')->text() === "") {
                $this->setError($crawler->filterXPath('//div[contains(@class, "status red")]')->text());
                return false;
            }
        } else {
            $this->setError("The category did not exist in the profile.");
        }
        return true;
    }

    /**
     * @return string
     */
    public function getSwap(): string
    {
        return $this->getLoader()['swap'];
    }

    /**
     * @return string
     */
    public function swapCategory(bool $space=true): string
    {
        if ($space)
            return "[[Category: " . $this->getSwap() . "]]";
        else
            return "[[Category:" . $this->getSwap() . "]]";
    }

}