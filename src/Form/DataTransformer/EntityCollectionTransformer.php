<?php
namespace App\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;

class EntityCollectionTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private string $entityClass;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed $collection
     * @return mixed
     */
    public function transform(mixed $collection)
    {
        $result = [];
        $q = 0;
        foreach($collection->toArray() as $item) {
            $result[$q]['value'] = $item->getId();
            $result[$q]['label'] = $item->getLabel();
            $q++;
        }
        return $result;
    }

    /**
     * @param mixed $result
     * @return ArrayCollection
     */
    public function reverseTransform(mixed $result)
    {
        if (is_array($result)) {
            dump($result);
            return new ArrayCollection($result);
        } else {
            return new ArrayCollection();
        }
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     * @return EntityCollectionTransformer
     */
    public function setEntityClass(string $entityClass): EntityCollectionTransformer
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}