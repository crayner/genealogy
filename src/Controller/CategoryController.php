<?php
namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryAddType;
use App\Form\CategoryType;
use App\Manager\CategoryManager;
use App\Manager\FormManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class CategoryController extends AbstractController
{
    /**
     * @param Category $category
     * @param RouterInterface $router
     * @return JsonResponse
     */
    #[Route('/genealogy/category/{category}/members', name: 'genealogy_category_members')]
    public function getCategoryMembers(Category $category, RouterInterface $router): JsonResponse
    {
        $members = [];
        foreach ($category->getIndividuals() as $q => $individual) {
            $members[$q] = $individual->__toArray();
            $members[$q]['path'] = $router->generate('genealogy_record_modify', ['individual' => $individual->getUserID()]);
            $members[0]['fetch'] = false;
        }
        return new JsonResponse(
            [
                'members' => $members,
            ],
            200);
    }

    /**
     * @param CategoryManager $manager
     * @return Response
     */
    #[Route('/genealogy/category/{category}/modify', name: 'genealogy_category_modify')]
    public function categoryModify(?Category $category, CategoryManager $manager, FormManager $formManager): Response
    {
        if (!$category instanceof Category) {
            return $this->redirectToRoute('genealogy_category_add');
        }

        if (!$manager->getCategory() instanceof Category) $manager->setCategory($category);
        $form = $this->createForm(CategoryType::class, $manager->getCategory(), ['method' => 'POST', 'manager' => $manager]);

        return $this->render('genealogy/category.html.twig', [
            'manager' => $manager,
            'full_form' => $formManager->extractForm($form),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param CategoryManager $manager
     * @param Request $request
     * @param FormManager $formManager
     * @return Response
     */
    #[Route('/genealogy/category/add', name: 'genealogy_category_add')]
    public function categoryAdd(CategoryManager $manager, Request $request, FormManager $formManager): Response
    {
        $form = $this->createForm(CategoryAddType::class);

        if ($request->getMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                $value = $request->request->all();
                $type = '\App\Entity\\' . $value['category']['categoryType'];
                $name = $value['category']['name'];
                $category = new $type();
                $category->setName($name)
                    ->setShowProfiles(true);
                $manager->getEntityManager()->persist($category);
                $manager->getEntityManager()->flush();
                return $this->redirectToRoute('genealogy_category_modify', ['category' => $category->getId()]);
            }
        }

        return $this->render('genealogy/category_add.html.twig',
            [
                'manager' => $manager,
                'full_form' => $formManager->extractForm($form),
                'form' => $form->createView(),
            ]
        );
    }

}