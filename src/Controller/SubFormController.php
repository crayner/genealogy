<?php
namespace App\Controller;

use App\Form\LocationType;
use App\Form\ParentCategoryType;
use App\Manager\CategoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $location = $form->getData()['field'];
            $manager->saveLocation($location, $manager->retrieveCategoryByID($form->getData()['id'], true));
        }

        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/genealogy/category/form/parent', name: 'category_form_parents', methods: ['POST'])]
    public function parentsForm(Request $request, CategoryManager $manager): Response
    {
        $form = $this->createForm(ParentCategoryType::class, ['method' => 'POST', 'action' => $this->generateUrl('category_form_parents')]);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $parents = $form->getData()['field'];
            $manager->saveParents($parents, $manager->retrieveCategoryByID($form->getData()['id'], true));
        }

        return $this->forward(GenealogyController::class.'::categoryModify', ['manager' => $manager], ['category' => $manager->getCategory()->getName()]);
    }
}