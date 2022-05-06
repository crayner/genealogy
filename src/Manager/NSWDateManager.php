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
 * Date: 1/03/2022
 * Time: 07:39
 */

namespace App\Manager;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * Class NSWDateManager
 * @selectPure App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/03/2022 08:20
 */
class NSWDateManager
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $fromDate;

    /**
     * @var \DateTimeImmutable
     */
    private \DateTimeImmutable $toDate;

    /**
     * @var HttpBrowser
     */
    private HttpBrowser $browser;

    /**
     * @var Crawler
     */
    private Crawler $crawler;

    /**
     * @var Form
     */
    private ?Form $form;

    /**
     * @param array $data
     * @return void
     */
    public function openNSWBDM(array $data)
    {
        $this->setData($data);
        if ($data['registration_year'] === null) return;

        $this->createDates($data['registration_year']);

        $url = 'https://familyhistory.bdm.nsw.gov.au/lifelink/familyhistory/search/' . $data['searchIn'];
        $this->getCrawler($url);
        $this->getDetails();

        $this->loopToAnswer();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return NSWDateManager
     */
    public function setData(array $data): NSWDateManager
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFromDate(): \DateTimeImmutable
    {
        return $this->fromDate;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @return NSWDateManager
     */
    public function setFromDate(\DateTimeImmutable $fromDate): NSWDateManager
    {
        $this->fromDate = $fromDate;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getToDate(): \DateTimeImmutable
    {
        return $this->toDate;
    }

    /**
     * @param \DateTimeImmutable $toDate
     * @return NSWDateManager
     */
    public function setToDate(\DateTimeImmutable $toDate): NSWDateManager
    {
        $this->toDate = $toDate;
        return $this;
    }

    /**
     * @param $year
     * @return NSWDateManager
     */
    private function createDates($year): NSWDateManager
    {
        $this->setFromDate(new \DateTimeImmutable('01-09-'.strval($year - 1)));
        return $this->setToDate(new \DateTimeImmutable('31-12-'.strval($year)));
    }

    /**
     * @param string $line
     * @return string
     */
    private function mapLineToForm(string $line): string
    {
        switch ($this->getData()['searchIn']) {
            case 'births':
                switch ($line) {
                    case 'id_search':
                        return 'searchSwitch:birthContainer:idSearchMode:edit';
                    case 'line1':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:subjectName:familyName:edit';
                    case 'line2':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:subjectName:givenName:edit';
                    case 'line3':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:subjectName:otherNames:edit';
                    case 'line4':
                        return 'earchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:fatherGivenName:edit';
                    case 'line5':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:fatherOtherNames:edit';
                    case 'line6':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:motherGivenName:edit';
                    case 'line7':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:motherOtherNames:edit';
                    case 'fromDay':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateFrom:day';
                    case 'fromMonth':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateFrom:month';
                    case 'fromYear':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateFrom:year';
                    case 'toDay':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateTo:day';
                    case 'toMonth':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateTo:month';
                    case 'toYear':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:dateOfEvent:switchGroup:range:dateTo:year';
                    case 'district':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthNameSearchContainer:district:edit';
                    case 'registration':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthIdSearchContainer:regNumber:regNumber';
                    case 'registration_year':
                        return 'searchSwitch:birthContainer:birthIdSearchSwitch:birthIdSearchContainer:regNumber:regYear';
                    default:
                        throw new \Exception($line . ' is not valid!');
                }
                break;
        }
        return '';
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function buildForm()
    {
        $form = $this->getForm();

        foreach($this->getData() as $name=>$value) {
            if ($value !== null && substr($name,0, 4) === 'line') {
               $form[$this->mapLineToForm($name)] = $value;
            }
        }

        $form[$this->mapLineToForm('fromDay')] = $this->getFromDate()->format('d');
        $form[$this->mapLineToForm('fromMonth')] = $this->getFromDate()->format('m');
        $form[$this->mapLineToForm('fromYear')] = $this->getFromDate()->format('Y');
        $form[$this->mapLineToForm('toDay')] = $this->getToDate()->format('d');
        $form[$this->mapLineToForm('toMonth')] = $this->getToDate()->format('m');
        $form[$this->mapLineToForm('toYear')] = $this->getToDate()->format('Y');
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function loopToAnswer()
    {
        $this->buildForm();
        $crawler = $this->getBrowser()->submit($this->getForm());

        $result = $crawler->filterXPath('//span[contains(@wicketpath, "mainContent_form_checkgroup_results_1_itemNum")]')->evaluate('count(@wicketpath)');

        dump($result,$crawler);
    }

    /**
     * @return HttpBrowser
     */
    public function getBrowser(): HttpBrowser
    {
        return $this->browser = isset($this->browser) ? $this->browser : new HttpBrowser();
    }

    /**
     * @param HttpBrowser $browser
     * @return NSWDateManager
     */
    public function setBrowser(HttpBrowser $browser): NSWDateManager
    {
        $this->browser = $browser;
        return $this;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form|null $form
     * @return NSWDateManager
     */
    public function setForm(?Form $form): NSWDateManager
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return Crawler
     */
    public function getCrawler(?string $url = null): Crawler
    {
        if (!is_null($url)) {
            $client = $this->getBrowser();
            $this->setCrawler($client->request("GET", $url));
        }
        return $this->crawler;
    }

    /**
     * @param Crawler $crawler
     * @return NSWDateManager
     */
    public function setCrawler(Crawler $crawler): NSWDateManager
    {
        $this->crawler = $crawler;
        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function getDetails()
    {
        $this->setForm($this->getCrawler()->selectButton("search-button")->form());

        $form = $this->getForm();

        $form[$this->mapLineToForm('id_search')] = 'Yes';
        $this->setCrawler($this->getBrowser()->submit($form));
        $this->setForm(null);
        dump($this);

        $this->setForm($this->getCrawler()->selectButton("search-button")->form());
        $form = $this->getForm();
        dump($form);
        $form[$this->mapLineToForm('registration')] = $this->getData()['registration'];
        $form[$this->mapLineToForm('registration_year')] = $this->getData()['registration_year'];
        $this->setCrawler($this->getBrowser()->submit($form));








       // $form[$this->mapLineToForm('registration')] = $this->getData()['registration'];
       // $form[$this->mapLineToForm('registration_year')] = $this->getData()['registration_year'];



        dump($this);
    }
}
