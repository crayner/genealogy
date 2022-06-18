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

use App\Form\WikiTreeBiographyType;
use App\Form\WikiTreeLoginType;
use App\Manager\WikiTreeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

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
    public function biography(RequestStack $stack, WikiTreeManager $manager): Response
    {

        $request = $stack->getCurrentRequest();
        $form = $this->createForm(WikiTreeLoginType::class);
        $form->handleRequest($request);
        $result = [];
        $result['valid'] = false;
        $result['error'] = 'Nothing done yet.';

        if ($form->isSubmitted()) {
            $data = $form->getData();

            if (is_array($data['interredCemetery']) && count($data['interredCemetery']) > 1) {
                $data['interredCemetery'] = null;
                $data['passedAwayJoiner'] = null;
            }

            if (! is_array($data['congregations']) || count($data['congregations']) === 0) {
                $data['marriageJoiner'] = null;
            }

            if ($data['profileIdentifier'] === null) {
                $data['marriageCongregations'] = null;
                $data['marriageDate'] = null;
                $data['spouseName'] = null;
                $data['marriageLocation'] = null;
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
                $data['marriageCongregations'] = null;
                $data['marriageDate'] = null;
                $data['spouseName'] = null;
                $data['marriageLocation'] = null;
                $data['profileIdentifier'] = null;
            }

            $result = $manager->login($data, $stack->getSession());
            if ($result['valid'])
                $form = $this->createForm(WikiTreeBiographyType::class, $data);

        }

        return $this->render('wikitree/biography.html.twig',
            [
                'form' => $form->createView(),
                'result' => $result
            ]
        );
    }
}
