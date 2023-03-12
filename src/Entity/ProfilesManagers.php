<?php
namespace App\Entity;

use App\Repository\ProfilesManagersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfilesManagersRepository::class)]
#[ORM\Table(name: 'profiles_managers', options: ['collate' => 'utf8mb4_unicode_ci'])]
class ProfilesManagers
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', options: ['unsigned'])]
    #[ORM\GeneratedValue]
    private int $id;

    /**
     * @var Individual
     */
    #[ORM\ManyToOne(targetEntity: Individual::class, inversedBy: 'managers')]
    #[ORM\Column(name: 'manager')]
    private Individual $manager;

    /**
     * @var Individual
     */
    #[ORM\ManyToOne(targetEntity: Individual::class, inversedBy: 'profiles')]
    #[ORM\Column(name: 'profile')]
    private Individual $profile;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProfilesManagers
     */
    public function setId(int $id): ProfilesManagers
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Individual
     */
    public function getManager(): Individual
    {
        return $this->manager;
    }

    /**
     * @param Individual $manager
     * @return ProfilesManagers
     */
    public function setManager(Individual $manager): ProfilesManagers
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * @return Individual
     */
    public function getProfile(): Individual
    {
        return $this->profile;
    }

    /**
     * @param Individual $profile
     * @return ProfilesManagers
     */
    public function setProfile(Individual $profile): ProfilesManagers
    {
        $this->profile = $profile;
        return $this;
    }
}