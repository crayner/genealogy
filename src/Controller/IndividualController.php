<?php

namespace App\Controller;

use App\Entity\Individual;
use App\Form\IndividualType;
use App\Form\NameSearchType;
use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\FormManager;
use App\Manager\IndividualManager;
use App\Manager\IndividualNameManager;
use App\Manager\NameSearch;
use App\Repository\IndividualRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        $search = $this->createForm(NameSearchType::class, new NameSearch(), ['method' => 'POST', 'action' => $this->generateUrl('individual_quick_search')]);

        return $this->render('individual/individual.html.twig',
            [
                'manager' => $manager,
                'name_manager' => $nameManager,
                'full_form' => $formManager->extractForm($form),
                'form' => $form->createView(),
                'full_search' => $formManager->extractForm($search),
                'search' => $search->createView(),
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

        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param Request $request
     * @param IndividualRepository $repository
     * @param FormManager $formManager
     * @return JsonResponse
     */
    #[Route('/genealogy/individual/quick/search', name: 'individual_quick_search')]
    public function searchNames(Request $request, IndividualRepository $repository, FormManager $formManager): JsonResponse
    {
        $search = $this->createForm(NameSearchType::class, new NameSearch(), ['method' => 'POST', 'action' => $this->generateUrl('individual_quick_search')]);

        if ($request->getContentTypeFormat() === 'json' && $request->getMethod() === 'POST') {
            $content = json_decode($request->getContent(), true);
            $search->submit($content);
            $data = $search->getData();
            $listChoices = $repository->quickNameSearch($content);
            $search = $this->createForm(NameSearchType::class, $data,
                [
                    'method' => 'POST',
                    'action' => $this->generateUrl('individual_quick_search'),
                    'list_choices' => $listChoices !== null ? $listChoices->toArray() : [],
                ]
            );
        }

        return new JsonResponse(
            [
                'full_search' => $formManager->extractForm($search),
            ],
            200
        );
    }

}