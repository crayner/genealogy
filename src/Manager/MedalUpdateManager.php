<?php

namespace App\Manager;

class MedalUpdateManager
{
    /**
     * @var string
     */
    private string $biography = "";

    /**
     * @var array
     */
    private array $medals = [];

    /**
     * @var array
     */
    private array $categories = [];

    /**
     * @var string
     */
    private string $currentName;
    /**
     * @var string
     */
    private string $currentCategory;

    /**
     * @var string|null
     */
    private ?string $newName;

    /**
     * @var string|null
     */
    private ?string $existingMedal;

    /**
     * @var string
     */
    private string $newCategory;

    /**
     * @var string
     */
    private string $defaultImage;

    /**
     * @var ?string
     */
    private ?string $existingImage = null;

    /**
     * @var bool
     */
    private bool $medalExists = false;

    /**
     * @var bool
     */
    private bool $categoryExists = false;

    /**
     * @param string $biography
     * @return string
     */
    public function searchBiography(string $biography): string
    {
        $this->setBiography($biography);
        $matches = [];
        preg_match_all("#{{[Mm]edal.*?}}#s", $this->getBiography(), $matches);
        $this->setMedals($matches[0]);

        $matches = [];
        preg_match_all("#\[\[[Cc]ategory.*?\]\]#", $this->getBiography(), $matches);
        $this->setCategories($matches[0]);

        //Does the Sticker exist?
        foreach($this->getMedals() as $q=>$medal) {
            if ($this->isMedalMatch($medal)) {
               $this->extractExistingImage($medal)
                   ->replaceMedalSticker($medal)
                   ->setExistingMedal($medal);
               break;
            }
        }

        //Does the category exist?
        $matches = [];
        if (preg_match("#\[\[[Cc]ategory:[ ]?".$this->getCurrentCategory()."[ ]?\]\][\r]?[\n]?#", $this->getBiography(), $matches) > 0)
        {
            $this->setCategoryExists(true);
            if ($this->isMedalExists()) {
                $this->setBiography(preg_replace("#\[\[[Cc]ategory:[ ]?".$this->getCurrentCategory()."[ ]?\]\][\r]?[\n]?#", "", $this->getBiography()));
            } else {
                $this->setBiography(preg_replace("#\[\[[Cc]ategory:[ ]?".$this->getCurrentCategory()."[ ]?\]\][\r]?[\n]?#", "[[Category: " . $this->getNewCategory() . "]]\r\n", $this->getBiography()));
            }
        }

        return $this->getBiography();
    }

    /**
     * @return array
     */
    public function getMedals(): array
    {
        return $this->medals;
    }

    /**
     * @param array $medals
     * @return MedalUpdateManager
     */
    public function setMedals(array $medals): MedalUpdateManager
    {
        $this->medals = $medals;
        return $this;
    }

    /**
     * @param string $medal
     * @return MedalUpdateManager
     */
    public function addMedal(string $medal): MedalUpdateManager
    {
        $medals = $this->getMedals();
        $medals[$medal] = $medal;
        return $this->setMedals($medals);
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return MedalUpdateManager
     */
    public function setCategories(array $categories): MedalUpdateManager
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentName(): string
    {
        return $this->currentName;
    }

    /**
     * @param string $currentName
     * @return MedalUpdateManager
     */
    public function setCurrentName(string $currentName): MedalUpdateManager
    {
        $this->currentName = $currentName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentCategory(): string
    {
        return $this->currentCategory;
    }

    /**
     * @param string $currentCategory
     * @return MedalUpdateManager
     */
    public function setCurrentCategory(string $currentCategory): MedalUpdateManager
    {
        $this->currentCategory = $currentCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewName(): string
    {
        return $this->newName !== null ? $this->newName : $this->getCurrentName();
    }

    /**
     * @param string|null $newName
     * @return MedalUpdateManager
     */
    public function setNewName(?string $newName): MedalUpdateManager
    {
        $this->newName = $newName;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewCategory(): string
    {
        return $this->newCategory;
    }

    /**
     * @param string $newCategory
     * @return MedalUpdateManager
     */
    public function setNewCategory(string $newCategory): MedalUpdateManager
    {
        $this->newCategory = $newCategory;
        return $this;
    }

    /**
     * @param string $medal
     * @return bool
     */
    private function isMedalMatch(string $medal): bool
    {
        $result = false;
        if (str_contains($medal, $this->getCurrentName())) $result = true;
        $this->setMedalExists($result);
        return $result;
    }

    /**
     * @return string
     */
    public function getDefaultImage(): string
    {
        return $this->defaultImage;
    }

    /**
     * @param string $defaultImage
     * @return MedalUpdateManager
     */
    public function setDefaultImage(string $defaultImage): MedalUpdateManager
    {
        $this->defaultImage = $defaultImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getExistingImage(): string
    {
        return $this->existingImage = $this->existingImage !== "" && $this->existingImage !== null ? $this->existingImage : $this->getDefaultImage();
    }

    /**
     * @param string|null $existingImage
     * @return MedalUpdateManager
     */
    public function setExistingImage(?string $existingImage): MedalUpdateManager
    {
        $this->existingImage = $existingImage;
        return $this;
    }

    /**
     * @param string $medal
     * @return $this
     */
    public function extractExistingImage(string $medal): MedalUpdateManager
    {
        $medal = explode("|", $medal);
        foreach ($medal as $q=>$w)
        {
            $m = [];
            if (preg_match("#[Ii]mage#", $w, $m) === 1) {
                $this->setExistingImage(trim(preg_replace("#[Ii]mage[ ]?=[ ]?#", "", $w)));
                break;
            }
        }
        return $this;
    }

    /**
     * @param string $existingMedal
     * @return $this
     */
    public function replaceMedalSticker(string $existingMedal): MedalUpdateManager
    {

        $this->setBiography(str_replace($existingMedal, $this->buildMedalSticker(), $this->getBiography()));
        return $this;
    }

    /**
     * @return string
     */
    public function buildMedalSticker(): string
    {
        return "{{Medal |medal= " . $this->getNewName() . " |image= " . $this->getExistingImage() . " |category= " . $this->getNewCategory() . "}}";
    }

    /**
     * @return string
     */
    public function getBiography(): string
    {
        return $this->biography;
    }

    /**
     * @return array
     */
    public function explodeBiography(): array
    {
        $biography = $this->getBiography();
        if ($this->isCategoryExists() && $this->isMedalExists()) {
            $biography = "<span style=\"color: red; text-decoration: line-through\">[[Category: " . $this->getCurrentCategory() . "]]</span>\r\n" . $biography;
        }
        if ($this->isMedalExists()) {
            $biography = str_replace($this->buildMedalSticker(), "<span style=\"color: red; text-decoration: line-through\">".str_replace("\r\n", " ", $this->getExistingMedal()) . "</span>\r\n<span style=\"color: green;\">" . $this->buildMedalSticker() . "</span>", $biography);
        }
        if ($this->isCategoryExists() && ! $this->isMedalExists()) {
            $biography = str_replace("[[Category: " . $this->getNewCategory() . "]]", "<span style=\"color: green;\">[[Category: " . $this->getNewCategory() . "]]</span>", $biography);
        }
        return explode("\r\n", $biography);
    }

    /**
     * @param string $biography
     * @return MedalUpdateManager
     */
    public function setBiography(string $biography): MedalUpdateManager
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMedalExists(): bool
    {
        return $this->medalExists;
    }

    /**
     * @param bool $medalExists
     * @return MedalUpdateManager
     */
    public function setMedalExists(bool $medalExists): MedalUpdateManager
    {
        $this->medalExists = $medalExists;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCategoryExists(): bool
    {
        return $this->categoryExists;
    }

    /**
     * @param bool $categoryExists
     * @return MedalUpdateManager
     */
    public function setCategoryExists(bool $categoryExists): MedalUpdateManager
    {
        $this->categoryExists = $categoryExists;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExistingMedal(): ?string
    {
        return $this->existingMedal;
    }

    /**
     * @param string|null $existingMedal
     * @return MedalUpdateManager
     */
    public function setExistingMedal(?string $existingMedal): MedalUpdateManager
    {
        $this->existingMedal = $existingMedal;
        return $this;
    }
}