<?php

namespace App\Entity;

use App\Exception\SourceDataException;
use App\Repository\SourceDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SourceData
 * @package App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 1/04/2021 15:56
 * @ORM\Entity(repositoryClass=SourceDataRepository::class)
 * @ORM\Table(name="source_data")
 */
class SourceData
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private string $id;

    /**
     * @var Source
     * @ORM\ManyToOne(targetEntity=Source::class)
     * @ORM\JoinColumn(name="source",nullable=false)
     */
    private Source $source;

    /**
     * @var string
     * @ORM\Column(type="text",nullable=true)
     */
    private string $page;

    /**
     * #var strinf
     * @ORM\Column(type="enum")
     */
    private string $qualityOfData;

    /**
     * @var array|string[]
     */
    private static array $qualityOfDataList = [
        'Unreliable evidence or estimated data',
        'Questionable reliability of evidence (interviews, census, oral genealogies, or potential
for bias for example, an autobiography)',
        'Secondary evidence, data officially recorded sometime after event',
        'Direct and primary evidence used, or by dominance of the evidence',
    ];

    /**
     * @var Data
     * @ORM\OneToOne(targetEntity=Data::class, inversedBy="sourceData")
     */
    private Data $data;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $note;

    /**
     * SourceData constructor.
     * @param Source|null $source
     */
    public function __construct(?Source $source = null)
    {
        if (!is_null($source)) $this->setSource($source);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Source
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @param Source $source
     * @return SourceData
     */
    public function setSource(Source $source): SourceData
    {
        if (isset($this->source) && $source !== $this->source) throw new SourceDataException($this, sprintf('The source record cannot be changed.'));
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getPage(): ?string
    {
        return isset($this->page) ? $this->page : null;
    }

    /**
     * @param string $page
     * @return SourceData
     */
    public function setPage(string $page): SourceData
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return string
     */
    public function getQualityOfData(): string
    {
        return $this->qualityOfData;
    }

    /**
     * @param string $qualityOfData
     * @return SourceData
     */
    public function setQualityOfData(string $qualityOfData): SourceData
    {
        if (strval(intval($qualityOfData)) === $qualityOfData) {
            $qualityOfData = self::$qualityOfDataList[$qualityOfData];
        }
        if (!in_array($qualityOfData, self::getQualityOfDataList())) throw new SourceDataException($this, sprintf('The quality of data given (%s) must be one of [%s]', $qualityOfData, implode(', ', self::getQualityOfDataList())));
        $this->qualityOfData = $qualityOfData;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getQualityOfDataList(): array
    {
        return self::$qualityOfDataList;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param Data $data
     * @param bool $mirror
     * @return SourceData
     */
    public function setData(Data $data, bool $mirror = true): SourceData
    {
        $this->data = $data;
        if ($mirror) $this->data->setSourceData($this, false);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return isset($this->note) ? $this->note : null;
    }

    /**
     * @param string $note
     * @return SourceData
     */
    public function setNote(string $note): SourceData
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param string $note
     * @return SourceData
     */
    public function concatNote(string $note): SourceData
    {
        $this->note .= $note;

        return $this;
    }
}
