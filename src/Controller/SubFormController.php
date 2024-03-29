<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryWebPage;
use App\Entity\Enum\CemeteryWebPageEnum;
use App\Entity\Location;
use App\Form\CategoryType;
use App\Form\LocationType;
use App\Form\ParentCategoryType;
use App\Manager\CategoryManager;
use App\Manager\FormManager;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class SubFormController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/genealogy/category/form/location', name: 'category_form_location', methods: ['POST'])]
    public function locationForm(Request $request, CategoryManager $manager): Response
    {
        $form = $this->createForm(LocationType::class, ['method' => 'POST', 'action' => $this->generateUrl('category_form_location')]);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $location = $form->getData()['location'];
            $manager->saveLocation($location, $manager->retrieveCategoryByID($form->getData()['id'], true));
        }

        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/genealogy/category/parent/add', name: 'category_form_parent_add', methods: ['POST'])]
    public function addCategoryParent(Request $request, CategoryManager $manager): Response
    {
        $form = $this->createForm(ParentCategoryType::class, ['method' => 'POST', 'action' => $this->generateUrl('category_form_parent_add')]);

        $form->handleRequest($request);
        $manager->retrieveCategoryByID($form->getData()['id']);
        if ($form->isSubmitted()) {
            $parent = $form->getData()['parent'];
            $manager->getCategory()->addParent($parent);
            $manager->saveCategory();
        }

        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
    }

    /**
     * @param Category $category
     * @param Category $parent
     * @param CategoryManager $manager
     * @param FormManager $formManager
     * @return JsonResponse
     */
    #[Route('/genealogy/category/parent/{category}/{parent}/remove', name: 'category_form_parent_remove', methods: ['POST'])]
    public function removeCategoryParent(Category $category, Category $parent, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        $manager->setCategory($category);
        $category->removeParent($parent);
        $manager->getEntityManager()->persist($category);
        $manager->getEntityManager()->flush();

        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
            ],
            200);
    }

    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/genealogy/category/name/save', name: 'category_name_save', methods: ['POST'])]
    public function saveCategoryName(Request $request, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
            $manager->retrieveCategoryByID($content['id']);

            $manager->getCategory()->setName($content['name']);
            $manager->getCategory()->setSortName($content['sortName']);
            $manager->getCategory()->setAka($content['aka']);
            $manager->getCategory()->setDisplayName($content['displayName']);
            $manager->getEntityManager()->persist($manager->getCategory());
            $manager->getEntityManager()->flush();

            $manager->writeCategoryDiscriminator($content['categoryType']);
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
                'message' => [
                    'id' => Uuid::v4(),
                    'text' => 'Data Save: Success!',
                    'timeOut' => '5000',
                    'level' => 'success',
                ],
            ],
            200);
    }

    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @param FormManager $formManager
     * @return JsonResponse
     */
    #[Route('/genealogy/category/parents/save', name: 'category_parents_save', methods: ['POST'])]
    public function saveCategoryParent(Request $request, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        $content = [];
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
            $manager->retrieveCategoryByID($content['id']);

            $ok = false;
            if (is_array($content['parents'])) {
                foreach ($content['parents'] as $parent) {
                    $item = $manager->getCategoryRepository()->findOneBy(['id' => $parent['value']]);
                    if ($item instanceof Category) {
                        $ok = $ok || $manager->getCategory()->addParent($item);
                    }
                }
            }
            if ($ok) {
                $manager->getEntityManager()->persist($manager->getCategory());
                $manager->getEntityManager()->flush();
            }
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
                'message' => [
                    'id' => Uuid::v4(),
                    'text' => 'Data Save: Success!',
                    'timeOut' => '5000',
                    'level' => 'success',
                ],
            ],
            200);
    }

    /**
     * @param CategoryRepository $repository
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/genealogy/category/parents/fetch', name: 'category_parents_fetch', methods: ['POST'])]
    public function fetchCategoryParents(CategoryRepository $repository, Request $request)
    {
        $content['search'] = '';
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
        }

        return new JsonResponse(
            [
                'choices' => $repository->findBySearch($content['search']),
            ],
            200);
    }
    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/genealogy/category/address/save', name: 'category_address_save', methods: ['POST'])]
    public function saveCategoryAddress(Request $request, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
            $manager->retrieveCategoryByID($content['id']);
            if (array_key_exists('address', $content)) {
                $manager->getCategory()
                    ->setAddress($content['address'])
                    ->setCoordinates($content['coordinates']);
            } else {
                $manager->getCategory()
                    ->setCoordinates($content['coordinates']);
            }
            if (array_key_exists('location', $content) && $content['location'] > 0) {
                $location = $manager->getCategoryRepository()->findOneBy(['id' => $content['location']]);
                if ($location instanceof Location) {
                    $manager->getCategory()->addParent($location);
                }
            }
            $manager->getEntityManager()->persist($manager->getCategory());
            $manager->getEntityManager()->flush();
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
                'message' => [
                    'id' => Uuid::v4(),
                    'text' => 'Data Save: Success!',
                    'timeOut' => '5000',
                    'level' => 'success',
                ],
            ],
            200);
    }

    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @param FormManager $formManager
     * @return JsonResponse
     */
    #[Route('/genealogy/category/webpages/save', name: 'category_webpages_save', methods: ['POST'])]
    public function saveWebPages(Request $request, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
            $manager->retrieveCategoryByID($content['id']);
            foreach ($content['webpages'] as $webpageData) {
                $webpage = $manager->getCategory()->getExistingWebpage($webpageData['name']);
                if ($webpage === false) $webpage = $manager->getCategory()->getExistingWebpage($webpageData['definedType']);
                if ($webpage === false) $webpage = new CategoryWebPage();
                if ($webpageData['definedType'] === []) $webpageData['definedType'] = 'NotUsed';
                $webpage->setCategory($manager->getCategory())
                    ->setName($webpageData['name'])
                    ->setKey($webpageData['key'])
                    ->setUrl($webpageData['url'])
                    ->setPrompt($webpageData['prompt'])
                    ->setDefinedType(CemeteryWebPageEnum::from($webpageData['definedType']))
                ;
                $manager->getCategory()->addWebpage($webpage);
                $manager->getEntityManager()->persist($webpage);
            }
            $manager->getEntityManager()->persist($manager->getCategory());
            $manager->getEntityManager()->flush();
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
                'message' => [
                    'id' => Uuid::v4(),
                    'text' => 'Data Save: Success!',
                    'timeOut' => '5000',
                    'level' => 'success',
                ],
            ],
            200);
    }

    /**
     * @param FormManager $formManager
     * @param CategoryManager $manager
     * @param CategoryWebPage $item
     * @param Category $category
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/genealogy/category/webpage/{category}/{item}/remove', name: 'category_webpage_remove', methods: ['POST'])]
    public function removeWebpage(FormManager $formManager, CategoryManager $manager, CategoryWebPage $item, Category $category, Request $request): JsonResponse
    {
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $category->removeWebPage($item);
            $manager->getEntityManager()->remove($item);
            $manager->getEntityManager()->persist($category);
            $manager->getEntityManager()->flush();
            $manager->setCategory($category);
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);
        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps($form->createView()->vars['template']),
                'message' => [
                    'id' => Uuid::v4(),
                    'text' => 'Data Save: Success!',
                    'timeOut' => '5000',
                    'level' => 'success',
                ],
            ],
            200);

    }
}