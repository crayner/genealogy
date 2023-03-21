<?php
namespace App\Entity;

use App\Repository\MigrantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MigrantRepository::class)]
class Migrant extends Category
{
}