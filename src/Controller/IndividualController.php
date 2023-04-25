<?php

namespace App\Controller;

use App\Entity\Individual;
use App\Form\IndividualType;
use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\FormManager;
use App\Manager\IndividualManager;
use App\Manager\IndividualNameManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndividualController extends AbstractController
{
    /**
     * @param IndividualManager $manager
     * @param Individual|null $individual
     * @return Response
     */
    #[Route('/genealogy/individual/{individual}/modify', name: 'genealogy_individual_modify')]
    public function modifyIndividual(IndividualManager $manager, FormManager $formManager, IndividualNameManager $nameManager, ?Individual $individual = null): Response
    {
        if ($individual instanceof Individual) {
            $manager->setIndividual($individual);
        } else {
            $manager->retrieveIndividual($_GET['individual']);
        }
        $form = $this->createForm(IndividualType::class);

        return $this->render('individual/individual.html.twig',
            [
                'manager' => $manager,
                'name_manager' => $nameManager,
                'full_form' => $formManager->extractForm($form),
                'form' => $form->createView(),
            ]
        );
    }

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

}