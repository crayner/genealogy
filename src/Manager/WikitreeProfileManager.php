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
 * Date: 15/09/2022
 * Time: 08:57
 */

namespace App\Manager;
use Symfony\Component\Yaml\Yaml;

/**
 * Class WikitreeProfileManager
 * @package App\Manager
 * @author  Craig Rayner <craig@craigrayner.com>
 * 15/09/2022 09:00
 */
class WikitreeProfileManager
{
    /**
     * @var array
     */
    private array $profiles;

    /**
     * @var string
     */
    private string $spreadSheet;

    /**
     * @return void
     */
    public function execute()
    {
        $profiles = $this->getProfiles();
        $spreadSheet = "ID\tName\tDate of Birth\tDate of Death\tM1 ID\tM1 Name\tM1 Date\tM2 ID\tM2 Name\tM2 Date\tM3 ID\tM3 Name\tM3 Date\tM4 ID\tM4 Name\tM4 Date\r\n";

        foreach ($profiles as $id=>$profile) {
            $profile = $this->tidyName($profile);
            $spreadSheet .= $id."\t".$profile['name'];
            $profile = $this->dateOfBirth($profile);
            $spreadSheet .= "\t".$this->getDateString($profile['dob']);
            $profile = $this->dateOfDeath($profile);
            $spreadSheet .= "\t".$this->getDateString($profile['dod']);

            if (is_array($profile['dom'])) {
                foreach ($profile['dom'] as $spouseID => $spouse) {
                    $spouse = $this->dateOfMarriage($spouse);
                    $spreadSheet .= "\t".$spouseID."\t".$spouse['name']."\t".$this->getDateString($spouse['date']);
                    $profile['dom'][$spouseID] = $spouse;
                }
            }
            $profiles[$id] = $profile;
            $spreadSheet .= "\r\n";

        }
        $this->setProfiles($profiles);
        $this->setSpreadSheet($spreadSheet);


        file_put_contents( 'F:\Google Drive\My Documents\Genealogy\profileList.tsv', $this->getSpreadSheet());
    }

    /**
     * @return array
     */
    public function getProfiles(): array
    {
        if (!isset($this->profiles)) {
            $profiles = Yaml::parse(file_get_contents(__DIR__ . '/../../config/packages/profiles.yaml'));
            $this->profiles = $profiles['parameters']['profiles'];
        }
        return $this->profiles;
    }

    /**
     * @param array $profiles
     * @return WikitreeProfileManager
     */
    public function setProfiles(array $profiles): WikitreeProfileManager
    {
        $this->profiles = $profiles;
        return $this;
    }

    /**
     * @param array $profile
     * @return array
     */
    private function tidyName(array $profile): array
    {
        $name = $profile['name'];
        while (str_contains($name, '  ')) {
            $name = str_replace('  ', ' ', $name);
        }
        $profile['name'] = $name;
        return $profile;
    }

    /**
     * @param array $profile
     * @return array
     */
    private function dateOfBirth(array $profile): array
    {
        $dob = $this->parseDate($profile['dob']);

        $profile['dob'] = $dob;
        return $profile;
    }

    /**
     * @param array $profile
     * @return array
     * @throws \Exception
     */
    private function dateOfDeath(array $profile): array
    {
        $dod = $this->parseDate($profile['dod']);

        $profile['dod'] = $dod;
        return $profile;
    }

    /**
     * @param array $spouse
     * @return array
     * @throws \Exception
     */
    private function dateOfMarriage(array $spouse): array
    {
        $dom = $this->parseDate($spouse['date']);

        $spouse['date'] = $dom;
        return $spouse;
    }

    /**
     * @param string|null $x
     * @return \DateTimeImmutable|string|null
     * @throws \Exception
     */
    private function parseDate(?string $x): mixed
    {
        if (empty($x)) return null;
        if (strlen($x) <= 4) return $x;
        if (strlen($x) <= 15) return $x;
        return new \DateTimeImmutable($x);
    }

    /**
     * @return string
     */
    public function getSpreadSheet(): string
    {
        return $this->spreadSheet;
    }

    /**
     * @param string $spreadSheet
     * @return WikitreeProfileManager
     */
    public function setSpreadSheet(string $spreadSheet): WikitreeProfileManager
    {
        $this->spreadSheet = $spreadSheet;
        return $this;
    }

    /**
     * @param \DateTimeImmutable|null $date
     * @return string
     */
    private function getDateString($date): string
    {
        if (is_string($date)) return $date;
        return $date instanceof \DateTimeImmutable ? $date->format("d/m/Y"): '';
    }
}