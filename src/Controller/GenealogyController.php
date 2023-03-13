<?php

namespace App\Controller;

use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\IndividualManager;
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
    #[Route('/dump', name: 'dump')]
    public function dump(DumpPeopleUsers $individual, DumpPeopleMarriage $marriage): Response
    {
        $offset = $individual->execute();

        if (is_array($offset)) dd($offset);
        if ($offset > 0) {
            return $this->render('wikitree/dump_wikitree.html.twig', ['offset' => $offset, 'manager' => $individual]);
        }

        // $marriage->execute();

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
}