<?php
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\LocationType;
use App\Form\ParentCategoryType;
use App\Manager\CategoryManager;
use App\Manager\FormManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param Request $request
     * @return Response
     */
    #[Route('/genealogy/category/parent/{category}/{parent}/remove', name: 'category_form_parent_remove', methods: ['GET'])]
    public function removeCategoryParent(Category $category, Category $parent, Request $request, CategoryManager $manager): Response
    {
        $manager->setCategory($category);
        $category->removeParent($parent);
        $manager->getEntityManager()->persist($category);
        $manager->getEntityManager()->flush();
        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
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

            $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'action' => $this->generateUrl('category_name_save')]);
dump($content, $manager->getCategory());
            if ($content['name'] !== $manager->getCategory()->getName()) {
                $manager->getCategory()->setName($content['name']);
                $manager->getEntityManager()->persist($manager->getCategory());
                $manager->getEntityManager()->flush();
            }

            $manager->writeCategoryDiscriminator($content['categoryType']);
        }

        return new JsonResponse(
            [
                'form' => $formManager->extractForm($form),
            ],
            200);
    }

    /**
     * @param Request $request
     * @param CategoryManager $manager
     * @return Response
     */
    #[Route('/genealogy/category/Parent/save', name: 'category_parent_save', methods: ['POST'])]
    public function saveCategoryParent(Request $request, CategoryManager $manager): Response
    {
        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
    }
}