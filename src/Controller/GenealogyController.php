<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\LocationType;
use App\Form\ParentCategoryType;
use App\Manager\CategoryManager;
use App\Manager\CategoryParse;
use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\IndividualManager;
use App\Manager\ManagerParse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenealogyController extends AbstractController
{
    /**
     * @param DumpPeopleUsers $individual
     * @param DumpPeopleMarriage $marriage
     * @return Response
     */
    #[Route('/individual/parse', name: 'individual_parse')]
    public function dump(DumpPeopleUsers $individual, DumpPeopleMarriage $marriage): Response
    {
        $offset = $individual->execute();

        if (is_array($offset)) dd($offset);
        if ($offset > 0) {
            return $this->render('wikitree/dump_individual.html.twig', ['offset' => $offset, 'manager' => $individual]);
        }

        // $marriage->execute();

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param DumpPeopleMarriage $marriage
     * @return Response
     */
    #[Route('/marriage/parse', name: 'marriage_parse')]
    public function marriageParse(DumpPeopleMarriage $marriage): Response
    {
        $offset = $marriage->execute();

        if (is_array($offset)) dd($offset);
        if ($offset > 0) {
            return $this->render('wikitree/dump_marriage.html.twig', [
                'offset' => $offset,
                'manager' => $marriage,
                'title' => 'Parsing Marriages from Wikitree Dump',
                'route' => 'marriage_parse'
            ]);
        }

        // $marriage->execute();

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param ManagerParse $manager
     * @return Response
     * @throws UniqueConstraintViolationException
     */
    #[Route('/manager/parse', name: 'manager_parse')]
    public function managersParse(ManagerParse $manager): Response
    {
        $offset = $manager->execute();

        if (is_array($offset)) dd($offset);
        if ($offset > 0) {
            return $this->render('wikitree/dump_marriage.html.twig', [
                'offset' => $offset,
                'manager' => $manager,
                'title' => 'Parsing Managers from Wikitree Dump',
                'route' => 'manager_parse'
            ]);
        }

        // $manager->execute();

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param CategoryParse $manager
     * @return Response
     * @throws UniqueConstraintViolationException
     */
    #[Route('/category/parse', name: 'category_parse')]
    public function categoryParse(CategoryParse $manager): Response
    {
        $offset = $manager->execute();

        if ($offset > 0) {
            return $this->render('wikitree/dump_marriage.html.twig', [
                'offset' => $offset,
                'manager' => $manager,
                'title' => 'Parsing Categories from Wikitree Dump',
                'route' => 'category_parse'
            ]);
        }

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param IndividualManager $manager
     * @return Response
     */
    #[Route('/genealogy/record/modify', name: 'genealogy_record_modify')]
    public function modifyRecord(IndividualManager $manager): Response
    {
        $manager->retrieveIndividual($_GET['individual']);
        return $this->render('genealogy/individual.html.twig', ['manager' => $manager]);
    }

    /**
     * @param CategoryManager $manager
     * @return Response
     */
    #[Route('/genealogy/category/modify', name: 'genealogy_category_modify')]
    public function categoryModify(CategoryManager $manager): Response
    {
        if (!$manager->getCategory() instanceof Category) $manager->retrieveCategory($_GET['category']);

        $location = $this->createForm(LocationType::class, ['id' => $manager->getCategory()->getId(), 'field' => $manager->getCategory()->getLocation()], ['method' => 'POST', 'action' => $this->generateUrl('category_form_location')]);
        $parents = $this->createForm(ParentCategoryType::class, ['id' => $manager->getCategory()->getId(), 'field' => $manager->getCategory()->getParents()], ['method' => 'POST', 'action' => $this->generateUrl('category_form_parents')]);

        return $this->render('genealogy/category.html.twig', [
            'manager' => $manager,
            'location_form' => $location->createView(),
            'parents_form' => $parents->createView(),
        ]);
    }
}