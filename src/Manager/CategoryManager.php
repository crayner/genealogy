<?php

namespace App\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

class CategoryManager
{
    /**
     * FILENAME
     */
    private $fileName = __DIR__ . '/../../var/log/categories.yaml';
    /**
     * @var string|null
     */
    private ?string $category = '';

    /**
     * @var ArrayCollection
     */
    private ArrayCollection $categories;

    /**
     * @var string|null
     */
    private ?string $error;

    /**
     * @var HttpBrowser
     */
    private HttpBrowser $client;

    /**
     * @var bool
     */
    private $initiated = false;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var array
     */
    private array $loader;

    /**
     * @var Session
     */
    private SessionInterface $session;

    /**
     * @param array $loader
     * @param RequestStack $stack
     * @param LoggerInterface $wikitreeLogger
     */
    public function __construct(array $loader, RequestStack $stack, LoggerInterface $wikitreeLogger)
    {
        $this->logger = $wikitreeLogger;
        $this->loader = $loader;
        $this->setSession($stack->getSession());
    }

    /**
     * @param Form $form
     * @return void
     */
    public function addNextCategory(FormInterface $form): bool
    {
        $data = $form->getData();
        $session = $this->getSession();

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
        $result = $this->parse($form);

        $session->set("cookieJar", $this->getClient()->getCookieJar());
        if (!$session->has('groupSize') || $session->get('group_size') === 0) {
            $session->set("groupSize", rand($this->getGroupSizeMin(), $this->getGroupSizeMax()));
        }
        $session->set("groupSize", $session->get("groupSize") - 1);

        return $result;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category ?: "No category Selected";
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
    public function setCategory(?string $category = null): CategoryManager
    {
        if (is_null($category) && $this->getCategories()->count() > 0) {
            $this->getCategories()->first();
            $category = $this->getCategories()->key();
        }
        $this->category = trim(str_replace(["]]","[[","Category:"], "", $category !== null ? $category: ''));
        return $this;
    }

    /**
     * @param $form
     * @return bool
     */
    private function parse($form): bool
    {
        $biography = trim($form['wpTextbox1']->getValue());
        if (!(str_contains($biography, $this->buildCategory()) || str_contains($biography, $this->buildCategory(false)))) {
            $biography = $this->buildCategory() . "\n" . $biography;
            $form['wpTextbox1'] = $biography;
            $form['wpSummary'] = 'Categorisation';
            $crawler = $this->getClient()->submit($form);
            $status = $crawler->filterXPath('//div[contains(@class, "status red")]')->evaluate('count(@class)');
            if ($status !== [] && !$crawler->filterXPath('//div[contains(@class, "status red")]')->text() === "") {
                $this->setError($crawler->filterXPath('//div[contains(@class, "status red")]')->text());
                return false;
            }
        } else {
            $this->setError("The category already existed in the profile.");
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

    /**
     * @param array $privateProfiles
     * @return CategoryManager
     */
    public function setPrivateProfiles(array $privateProfiles): CategoryManager
    {
        $this->privateProfiles = $privateProfiles;
        return $this;
    }

    /**
     * @param FormInterface $form
     * @return void
     */
    public function handleForm(FormInterface $form): bool
    {
        $this->initiateCategories();
        $data = $form->getData();
        $this->addCategory([$this->setCategory($data['category'])->getCategory() => explode("\r\n",$data['profileList'])]);

        $this->writeCategories();
        return true;
    }

    /**
     * @return void
     */
    public function initiateCategories()
    {
        if ($this->isInitiated())
            return;

        if (!is_file($this->fileName)) {
            file_put_contents($this->fileName, Yaml::dump([], 8));
        }
        $file = new File($this->fileName);
        $this->setCategories(new ArrayCollection(Yaml::parse($file->getContent())));
        foreach ($this->getCategories() as $name=>$profiles) {
            $this->getCategories()->set($name, new ArrayCollection($profiles));
        }
        $this->setInitiated();
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories(): ArrayCollection
    {
        return $this->categories = $this->categories ?? new ArrayCollection();
    }

    /**
     * @param array $category
     * @return $this
     */
    public function addCategory(array $category): CategoryManager
    {
        $key = key($category);
        if ($this->getCategories()->containsKey(trim($key))) {
            $profiles = $this->getCategories()->get(trim($key));
            foreach ($category[$key] as $profile) {
                if (!$profiles->contains(trim($profile))) $profiles->add(trim($profile));
            }
        } else {
            $this->getCategories()->set(trim($key), new ArrayCollection([]));
            $profiles = $this->getCategories()->get(trim($key));
            foreach ($category[$key] as $profile) {
                $profiles->add(trim($profile));
            }
        }
        $this->getCategories()->set(trim($key), $profiles);
        return $this;
    }

    /**
     * @param string $categoryName
     * @return $this
     */
    public function removeCategory(string $categoryName): CategoryManager
    {
        if ($this->getCategories()->containsKey($categoryName)) {
            $this->getCategories()->remove($categoryName);
        }
        return $this->setCategory();
    }

    /**
     * @param ArrayCollection $categories
     * @return CategoryManager
     */
    public function setCategories(ArrayCollection $categories): CategoryManager
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return CategoryManager
     */
    public function writeCategories(): CategoryManager
    {
        $data = [];
        foreach ($this->getCategories()->toArray() as $category=>$profiles)
        {
            $data[$category] = $profiles->toArray();
        }

        file_put_contents($this->fileName, Yaml::dump($data,8));
        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error = $this->error ?? "";
    }

    /**
     * @param string|null $error
     * @return CategoryManager
     */
    public function setError(?string $error): CategoryManager
    {
        $this->error = $error;
        if (!is_null($error)) $this->getLogger()->warning($error, $this->getContext());
        return $this;
    }

    /**
     * @param bool $current
     * @return array
     */
    public function statistics(bool $current): array
    {
        $result = [];
        $result['categories'] = $this->getCategories()->count();
        $result['profiles'] = 0;
        if ($current) {
            $result['profile'] = $this->firstProfile();
            $result['category'] = $this->getCategory();
        }
        foreach ($this->getCategories()->toArray() as $profiles) {
            $result['profiles'] += $profiles->count();
        }
        $result['wait'] = rand($this->getProfilePauseMin(), $this->getProfilePauseMax());
        $result['pause'] = $this->session->get("groupSize");
        if ($result['pause'] === 0) {
            $result['wait'] += rand($this->getGroupPauseMin(), $this->getGroupPauseMax());
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isInitiated(): bool
    {
        return $this->initiated;
    }

    /**
     * @param bool $initiated
     * @return $this
     */
    public function setInitiated(bool $initiated = true): CategoryManager
    {
        $this->initiated = $initiated;
        return $this;
    }

    /**
     * @return string
     */
    public function firstProfile(): string
    {
        $category = $this->getCategories()->first();
        if (!$category) return "No profile set.";
        while ($category->count() === 0) {
            $this->removeCategory($category);
            $category = $this->getCategories()->first();
        }
        $this->setCategory();
        return $category->first();
    }

    /**
     * @return $this
     */
    public function removeProfile(): CategoryManager
    {
        if (is_null($this->getCategory()) || $this->getCategory() === "No category Selected")
            return $this;

        $profile = $this->firstProfile();
        $this->getCategories()->get($this->getCategory())->removeElement($profile);

        $profiles = $this->getCategories()->get($this->getCategory());
        if ($profiles->count() === 0) {
            $this->removeCategory($this->getCategory());
        }
        if ($this->getCategories()->count() > 0) {
            $this->getCategories()->set($this->getCategory(), new ArrayCollection(array_values($profiles->toArray())));

            $this->firstProfile();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function profilesInCategory(): int
    {
        if ($this->getCategories()->count() === 0 || ! $this->getCategories()->containsKey($this->getCategory())) return 0;
        return $this->getCategories()->get($this->getCategory())->count();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        $context = [];
        $context['profile'] = $this->firstProfile();
        $context['category'] = $this->getCategory();
        return $context;
    }

    /**
     * @return array
     */
    public function getLoader(): array
    {
        return $this->loader;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }
    /**
     * @param Session $session
     * @return $this
     */
    public function setSession(SessionInterface $session): CategoryManager
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupSizeMin(): int
    {
        return intval($this->getLoader()['group_size_min']);
    }

    /**
     * @return int
     */
    public function getGroupSizeMax(): int
    {
        return intval($this->getLoader()['group_size_max']);
    }

    /**
     * @return int
     */
    public function getGroupPauseMin(): int
    {
        return ($this->getLoader()['group_pause_min']);
    }

    /**
     * @return int
     */
    public function getGroupPauseMax(): int
    {
        return intval($this->getLoader()['group_pause_max']);
    }

    /**
     * @return int
     */
    public function getProfilePauseMin(): int
    {
        return intval($this->getLoader()['group_pause_min']);
    }

    /**
     * @return int
     */
    public function getProfilePauseMax(): int
    {
        return intval($this->getLoader()['group_pause_max']);
    }
}