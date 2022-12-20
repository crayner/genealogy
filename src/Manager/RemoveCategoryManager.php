<?php

namespace App\Manager;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class RemoveCategoryManager extends CategoryManager
{

    /**
     * @param Form $form
     * @return void
     */
    public function removeNextCategory(FormInterface $form, Session $session): bool
    {
        $data = $form->getData();

        if ($session->has('cookieJar')) {
            $cookieJar = $session->get('cookieJar');
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
            $result['valid'] = false;
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
        $result = $this->remove($form);

        $session->set("cookieJar", $this->getClient()->getCookieJar());

        return $result;
    }

    /**
     * @param $form
     * @return bool
     */
    private function remove($form): bool
    {
        $biography = trim($form['wpTextbox1']->getValue());
        if (str_contains($biography, $this->buildCategory()) || str_contains($biography, $this->buildCategory(false))) {
            $biography = str_replace([$this->buildCategory()."\n", $this->buildCategory(false)."\n", $this->buildCategory(), $this->buildCategory(false)], "", $biography);
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
}