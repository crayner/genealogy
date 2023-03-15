<?php

namespace App\Controller;

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
    #[Route('/genealogy/modify/record', name: 'genealogy_modify_record')]
    public function modifyRecord(IndividualManager $manager): Response
    {
        $manager->retrieveIndividual($_GET['individual']);
        return $this->render('genealogy/modify_record.html.twig', ['manager' => $manager]);
    }

    /**
     * @param CategoryManager $manager
     * @return Response
     */
    #[Route('/genealogy/modify/category', name: 'genealogy_modify_category')]
    public function modifyCategory(CategoryManager $manager): Response
    {
        $manager->retrieveCategory($_GET['category']);
        return $this->render('genealogy/modify_category.html.twig', ['manager' => $manager]);
    }
}