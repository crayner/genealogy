<?php
/**
 * Created by PhpStorm.
 *
 * genealogy
 * (c) 2021 Craig Rayner <craig@craigrayner.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: Craig Rayner
 * Date: 16/12/2021
 * Time: 12:18
 */

namespace App\Controller;

use App\Form\CategoryAddType;
use App\Form\CategoryLoginType;
use App\Form\CategoryType;
use App\Form\WikiTreeBiographyType;
use App\Form\WikiTreeLoginType;
use App\Manager\CategoryManager;
use App\Manager\MarriageSentenceManager;
use App\Manager\WikiTreeManager;
use App\Manager\WikitreeProfileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WikiTreeController
 * @package App\Controller
 * @author  Craig Rayner <craig@craigrayner.com>
 * 16/12/2021 12:18
 */
class WikiTreeController extends AbstractController
{
    /**
     * @param RequestStack $stack
     * @param WikiTreeManager $manager
     * @Route("/wikitree/biography/",name="wikitree_biography")
     * @return Response
     */
    public function biography(RequestStack $stack, WikiTreeManager $manager, TranslatorInterface $translator): Response
    {

        $request = $stack->getCurrentRequest();
        $form = $this->createForm(WikiTreeLoginType::class);
        $form->handleRequest($request);
        $result = [];
        $result['valid'] = false;
        $result['error'] = 'Nothing done yet.';
        $data = [];

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if (is_array($data['interredCemetery']) && count($data['interredCemetery']) > 1) {
                $data['interredCemetery'] = null;
                $data['passedAwayJoiner'] = null;
            }

            if (is_array($data['interredCemetery']) && count($data['interredCemetery']) === 0) {
                $data['interredLocation'] = null;
            }

            if (!key_exists('profileIdentifier', $data) || $data['profileIdentifier'] === null) {
                $data['marriageCongregations'] = null;
                $data['marriageDate'] = null;
                $data['spouseName'] = null;
                $data['marriageLocation'] = [];
                $data['marriageCongregation'] = [];
                $data['profileIdentifier'] = null;
            }

            if ($form->get('reset')->isClicked()) {
                $data["interredCemetery"] = null;
                $data["interredLocation"] = null;
                $data["baptismDate"] = null;
                $data["baptismLocation"] = null;
                $data["congregations"] = [];
                $data["locations"] = [];
                $data["raynerPage"] = null;
                $data['passedAwayJoiner'] = null;
                $data['marriageJoiner'] = null;
                $data['marriageDate'] = null;
                $data['spouseName'] = null;
                $data['marriageLocation'] = [];
                $data['marriageCongregation'] = [];
                $data['profileIdentifier'] = null;
            }

            $result = $manager->login($data, $stack->getSession());
            if ($result['valid'])
                $form = $this->createForm(WikiTreeBiographyType::class, $data);

        }

        $marriageSentence = new MarriageSentenceManager($result, $data, $translator);
        $result = $marriageSentence->getResult();

        return $this->render('wikitree/biography.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result,
                'manager' => $manager,
            ]
        );
    }

    /**
     * @param WikitreeProfileManager $manager
     * @Route("/wikitree/profiles/",name="wikitree_profiles")
     * @return Response
     */
    public function profiles(WikitreeProfileManager $manager): Response
    {
        $manager->execute();
        return $this->render('base.html.twig',
            [
                'stuff' => $manager,
            ]
        );
    }

    /**
     * @param RequestStack $stack
     * @param CategoryManager $manager
     * @Route("/wikitree/category/",name="wikitree_category")
     * @return Response
     */
    public function category(RequestStack $stack, CategoryManager $manager): Response
    {
        $request = $stack->getCurrentRequest();
        $form = $this->createForm(CategoryLoginType::class);
        $form->handleRequest($request);
        $manager->initiateCategories();

        $result = $manager->statistics(true);
        if ($form->isSubmitted()) {
            $manager->addNextCategory($form, $stack->getSession());
            $data = $form->getData();
            $form = $this->createForm(CategoryType::class, $data);
            $manager->removeProfile()
                ->writeCategories();
            $result = array_merge($result, $manager->statistics(false));
        }

        return $this->render('wikitree/category.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result,
                'manager' => $manager,
            ]
        );

    }

    /**
     * @param RequestStack $stack
     * @param CategoryManager $manager
     * @Route("/wikitree/add/category/",name="wikitree_add_category")
     * @return Response
     */
    public function addCategories(RequestStack $stack, CategoryManager $manager): Response
    {
        $result = [];
        $form = $this->createForm(CategoryAddType::class);
        $request = $stack->getCurrentRequest();
        $form->handleRequest($request);
        $manager->initiateCategories();

        if ($form->isSubmitted()) {
            $manager->handleForm($form, $stack->getSession());
            $form = $this->createForm(CategoryAddType::class);
        }

        $result = $manager->statistics(true);
        return $this->render('wikitree/add_category.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result,
                'manager' => $manager,
            ]
        );
    }
}