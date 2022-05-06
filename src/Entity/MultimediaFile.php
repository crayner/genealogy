<?php

namespace App\Entity;

use App\Exception\MultiMediaFileException;
use App\Repository\MultimediaFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * Class MultimediaFile
 * @selectPure App\Entity
 * @author  Craig Rayner <craig@craigrayner.com>
 * 2/04/2021 14:24
 * @ORM\Entity(repositoryClass=MultimediaFileRepository::class)
 * @ORM\Table(name="multimedia_file")
 */
class MultimediaFile
{
    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)    
     */
    private string $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private string $reference;

    /**
     * @var string
     * @ORM\Column(type="enum",length=4)
     */
    private string $format;
    /**
     * @var array|string[]
     */
    private static array $formatList = [
        'AAC',
        'AVI',
        'BMP',
        'ePUB',
        'FLAC',
        'GIF',
        'JPEG',
        'MKV',
        'mobi',
        'MP3',
        'PCX',
        'PDF',
        'PNG',
        'TIFF',
        'WAV',
        'MP4',
        'MOV',
        'HTML',
    ];

    /**
     * @var string|null
     * @ORM\Column(length=15, nullable=true)
     */
    private ?string $mediaType;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $title;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return MultimediaFile
     */
    public function setReference(string $reference): MultimediaFile
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return MultimediaFile
     */
    public function setFormat(string $format): MultimediaFile
    {
        if (preg_match('/jpg/i',$format)) $format = 'JPEG';
        if (preg_match('/tif/i',$format)) $format = 'TIFF';
        if (preg_match('/htm/i',$format)) $format = 'HTML';
        if (!preg_match('/'.implode('|', self::getFormatList()).'/i', $format)) throw new MultiMediaFileException($this, sprintf('The file format (%s) must be one of [%s]',$format,implode(', ',self::getFormatList())));
        $this->format = $format;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getFormatList(): array
    {
        return self::$formatList;
    }

    /**
     * @return string|null
     */
    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    /**
     * @param string|null $mediaType
     * @return MultimediaFile
     */
    public function setMediaType(?string $mediaType): MultimediaFile
    {
        $this->mediaType = $mediaType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return MultimediaFile
     */
    public function setTitle(?string $title): MultimediaFile
    {
        $this->title = $title;
        return $this;
    }
}
