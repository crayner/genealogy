<?php

namespace App\Manager;

use App\Entity\Category;
use App\Entity\Cemetery;
use App\Entity\Location;
use App\Entity\Migrant;
use App\Entity\Theme;
use App\Repository\CategoryRepository;
use ContainerD2G9WVm\get_ServiceLocator_CtWRYQmService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryManager
{
    /**
     * @var Category|null
     */
    var ?Category $category;

    /**
     * @var EntityManagerInterface
     */
    var EntityManagerInterface $entityManager;

    var CategoryRepository $categoryRepository;

    /**
     * @var IndividualNameManager
     */
    var IndividualNameManager $nameManager;

    /**
     * @var SessionInterface
     */
    var SessionInterface $session;

    /**
     * @var RouterInterface
     */
    var RouterInterface $router;

    /**
     * @var ValidatorInterface
     */
    var ValidatorInterface $validator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $stack
     * @param SerializerAwareInterface $serialiser
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack, RouterInterface $router, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
        $this->session = $stack->getSession();
        $this->router = $router;
        $this->validator = $validator;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category = $this->category ?? null;
    }

    /**
     * @param Category|null $category
     * @return CategoryManager
     */
    public function setCategory(?Category $category): CategoryManager
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param string|null $categoryName
     * @return Category|null
     */
    public function retrieveCategory(?string $categoryName): ?Category
    {
        $this->setCategory($this->getCategoryRepository()->findOneBy(['name' => $categoryName]));
        return $this->getCategory();
    }

    /**
     * @param string|null $categoryName
     * @return Category|null
     */
    public function retrieveCategoryByID(?string $categoryName): ?Category
    {
        $this->setCategory($this->getCategoryRepository()->find($categoryName));
        return $this->getCategory();
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return CategoryRepository
     */
    public function getCategoryRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }

    /**
     * @return IndividualNameManager
     */
    public function getNameManager(): IndividualNameManager
    {
        return $this->nameManager = $this->nameManager ?? new IndividualNameManager();
    }

    /**
     * @param Location $location
     * @param Category $category
     * @return void
     */
    public function saveLocation(Location $location, Category $category)
    {
        $category->addParent($location);
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Category|null $category
     * @return CategoryManager
     */
    public function saveCategory(?Category $category = null): CategoryManager
    {
        if (is_null($category)) $this->category = $this->getCategory();
        if (is_null($category)) return $this;
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
        return $this;
    }

    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * @return string
     */
    public function getCategoryProps(array $template): array
    {
        if (is_null($this->getCategory())) {
            return [
                'id' => null,
                'name' => '',
            ];
        }

        $result = [
            'id' => $this->getCategory()->getId(),
            'name' => $this->getCategory()->getName(),
            'displayName' => $this->getCategory()->getDisplayName(),
            'sortName' => $this->getCategory()->getSortName(),
            'aka' => $this->getCategory()->getAka() ?: false,
            'location' => $this->getCategory()->getLocation() instanceof Category ? $this->getCategory()->getLocation()->getDisplayName() : '',
            'address' => method_exists($this->getCategory(), 'getAddress') && $this->getCategory()->getAddress() ? $this->getCategory()->getAddress() : '',
            'template' => $template,
            'rules' => $this->getRules(),
            'coordinates' => [],
            'errors' => []
        ];

        if ($this->getCategory()->getIndividuals()->count() < 1000) {

            foreach ($this->getCategory()->getIndividuals() as $q => $individual) {
                $result['individuals'][$q] = $individual->__toArray();
                $result['individuals'][$q]['path'] = $this->getRouter()->generate('genealogy_record_modify', ['individual' => $individual->getUserID()]);
                $result['individuals'][0]['fetch'] = false;
            }
        } else {
            $result['individuals'][0] = [];
            $result['individuals'][0]['path'] = $this->getRouter()->generate('genealogy_record_modify', ['individual' => '{individual}']);
            $result['individuals'][0]['fetch'] = true;
        }
        foreach ($this->getCategory()->getParents() as $q => $parent) {
            $result['parents'][$q] = $parent->toArray();
            $result['parents'][$q]['path'] = $this->getRouter()->generate('genealogy_category_modify', ['category' => $parent->getId()]);
        }
        $childrenCategories = $this->getCategoryRepository()->findAllByParent($this->getCategory());
        foreach($childrenCategories as $q => $child) {
            $result['childrenCategories'][$q] = $child->toArray();
            $result['childrenCategories'][$q]['path'] = $this->getRouter()->generate('genealogy_category_modify', ['category' => $child->getId()]);
        }
        $result['webpages'] = [] ;
        foreach($this->getCategory()->getWebpages() as $page) {
            $result['webpages'][] = $page->__toArray();
        }
        $coord = method_exists($this->getCategory(), 'getCoordinates') ? $this->getCategory()->getCoordinates() : null;
        if (is_string($coord)) {
            $coord = explode(',', $coord);
            if (count($coord) >= 2) {
                $coord['longitude'] = trim($coord[1]);
                $coord['latitude'] = trim($coord[0]);
                unset($coord[0], $coord[1]);
            }
            if (count($coord) === 3) {
                $coord['zoom'] = trim($coord[2]);
                unset($coord[2]);
            } else {
                $coord['zoom'] = $this->getCategory()->getZoomLevel();
            }
        }
        $result['coordinates'] = is_array($coord) ? $coord : false;
        if ($result['coordinates'] === false && $this->getCategory() instanceof Location) {
            $result['coordinates'] = [];
        }
        $result['google_map_type'] = $this->getCategory()->getGoogleMapType();

        foreach ($this->getValidator()->validate($this->getCategory()) as $error) {
            $result['errors'][] = $error->getMessage();
        }
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'parents' => [],
            'individuals' => [],
            'childrenCategories' => [],
        ]);
        $resolver->setRequired([
            'id',
            'name',
            'sortName',
            'aka',
            'displayName',
            'location',
            'address',
            'template',
            'rules',
            'webpages',
            'coordinates',
            'google_map_type',
            'errors',
        ]);

        return $resolver->resolve($result);
    }

    /**
     * @param array $template
     * @return string
     */
    public function getJsonCategoryProps(array $template): string
    {
        return json_encode($this->getCategoryProps($template), 0, 1024);
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param string $categoryType
     * @return $this
     * @throws \Doctrine\DBAL\Exception
     */
    public function writeCategoryDiscriminator(string $categoryType): CategoryManager
    {
        if ($this->getCategoryType() === $categoryType || !$this->checkCategoryType($categoryType)) return $this;
        $query = "UPDATE category SET discriminator = :categoryType WHERE id = :categoryId";
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $resultSet = $stmt->executeQuery(['categoryType' => $categoryType, 'categoryId' => $this->getCategory()->getId()]);

        $id = $this->getCategory()->getId();
        $this->setCategory(null);
        $this->retrieveCategoryByID($id);

        return $this;
    }

    /**
     * @return string
     */
    protected function getCategoryType(): string
    {
        switch (get_class($this->getCategory())) {
            case (Location::class):
                return 'location';
                break;
            case (Category::class):
                return 'category';
                break;
            case (Cemetery::class):
                return 'cemetery';
                break;
            case (App\Entity\Collection::class):
                return 'collection';
                break;
            case (Migrant::class):
                return 'migrant';
                break;
            case (Theme::class):
                return 'theme';
                break;
            default:
                dd(get_class($this->getCategory()));
        }
    }

    /**
     * @param string $categoryType
     * @return bool
     */
    protected function checkCategoryType(string $categoryType): bool
    {
        return match ($categoryType) {
            'category', 'cemetery', 'collection', 'location', 'migrant', 'theme' => true,
            default => false,
        };
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    private function getRules(): array
    {
        $result = [];
        $result[] = [
            'name' => '^category_webpages_([0-9]{1,2}|__name__)_definedType$',
        ];
        $result[] = [
            'name' => '^category_webpages_([0-9]{1,2}|__name__)_key$',
            'disabled' => [
                'name' => '^category_webpages_([0-9]{1,2}|__name__)_definedType$',
                'value' => '(!(^NotUsed$|^$))'
            ]
        ];
        $result[] = [
            'name' => '^category_webpages_([0-9]{1,2}|__name__)_name$',
            'disabled' => [
                'name' => '^category_webpages_([0-9]{1,2}|__name__)_definedType$',
                'value' => '(^NotUsed$|^$)'
            ]
        ];
        $result[] = [
            'name' => '^category_webpages_([0-9]{1,2}|__name__)_url$',
            'disabled' => [
                'name' => '^category_webpages_([0-9]{1,2}|__name__)_definedType$',
                'value' => '(^NotUsed$|^$)'
            ]
        ];
        return $result;
    }
}