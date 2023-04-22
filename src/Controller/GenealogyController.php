<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Individual;
use App\Form\CategoryAddType;
use App\Form\CategorySearchType;
use App\Form\CategoryType;
use App\Manager\CategoryManager;
use App\Manager\CategoryParse;
use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\FormManager;
use App\Manager\IndividualManager;
use App\Manager\ManagerParse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function individualParse(DumpPeopleUsers $individual, DumpPeopleMarriage $marriage): Response
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
     * @param string $letter
     * @return Response
     * @throws UniqueConstraintViolationException
     */
    #[Route('/category/{offset}/{letter}/parse', name: 'category_parse')]
    public function categoryParse(CategoryParse $manager, string $letter, int $offset): Response
    {
        $offset = $manager->execute($offset, $letter);

        if ($offset > 0) {
            return $this->render('wikitree/dump_marriage.html.twig', [
                'offset' => $offset,
                'manager' => $manager,
                'title' => 'Parsing Categories from Wikitree Dump',
                'route' => 'category_parse',
                'letter' => $letter,
            ]);
        }

        if ($offset === 0 && $letter < 'Z') {
            $letter = chr(ord($letter) + 1);
            return $this->render('wikitree/dump_marriage.html.twig', [
                'offset' => $offset,
                'manager' => $manager,
                'title' => 'Parsing Categories from Wikitree Dump',
                'route' => 'category_parse',
                'letter' => $letter,
            ]);
        }

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param IndividualManager $manager
     * @param Individual|null $individual
     * @return Response
     */
    #[Route('/genealogy/record/{individual}/modify', name: 'genealogy_record_modify')]
    public function modifyRecord(IndividualManager $manager, ?Individual $individual = null): Response
    {
        if ($individual instanceof Individual) {
            $manager->setIndividual($individual);
        } else {
            $manager->retrieveIndividual($_GET['individual']);
        }
        return $this->render('genealogy/individual.html.twig', ['manager' => $manager]);
    }

    #[Route('/genealogy/category/add', name: 'genealogy_category_add')]
    public function categoryAdd(CategoryManager $manager, Request $request, FormManager $formManager): Response
    {
        $form = $this->createForm(CategoryAddType::class);

        if ($request->getMethod('POST')) {
            $form->handleRequest($request);
            dump($form->getData());
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