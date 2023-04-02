<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Location;
use App\Form\CategoryType;
use App\Form\LocationType;
use App\Form\ParentCategoryType;
use App\Manager\CategoryManager;
use App\Manager\FormManager;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

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
                'category' => $manager->getCategoryProps(),
            ],
            200);
    }

    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @return JsonResponse
     */
    #[Route('/genealogy/category/name/save', name: 'category_name_save', methods: ['POST'])]
    public function saveCategoryName(Request $request, CategoryManager $manager, FormManager $formManager): JsonResponse
    {
        $content = [];
        if ($request->getContentTypeFormat() === 'json' && $request->getMethod('POST')) {
            $content = json_decode($request->getContent(), true);
            $manager->retrieveCategoryByID($content['id']);

            if ($content['name'] !== $manager->getCategory()->getName()) {
                $manager->getCategory()->setName($content['name']);
                $manager->getEntityManager()->persist($manager->getCategory());
                $manager->getEntityManager()->flush();
            }

            $manager->writeCategoryDiscriminator($content['categoryType']);
        }
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
                'category' => $manager->getCategoryProps(),
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
            if (array_key_exists('location', $content) && $content['location'] > 0) {
                $location = $manager->getCategoryRepository()->findOneBy(['id' => $content['location']]);
                if ($location instanceof Location) {
                    $ok = $manager->getCategory()->addParent($location);
                }
            }

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
                'category' => $manager->getCategoryProps(),
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
}