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
 * Date: 23/03/2021
 * Time: 11:45
 */

namespace App\Controller;

use App\Entity\Individual;
use App\Manager\DumpPeopleMarriage;
use App\Manager\DumpPeopleUsers;
use App\Manager\FileNameDiscriminator;
use App\Manager\GedFileHandler;
use App\Manager\ParameterManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 * @author  Craig Rayner <craig@craigrayner.com>
 * 23/03/2021 11:46
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/importer/",name="importer")
     * @param FileNameDiscriminator $fileNameDiscriminator
     * @param GedFileHandler $handler
     * @param ParameterManager $parameterManager
     * @return Response
     */
    public function importer(FileNameDiscriminator $fileNameDiscriminator, GedFileHandler $handler, ParameterManager $parameterManager): Response
    {
        $individuals = $this->getDoctrine()->getManager()->getRepository(Individual::class)->findAll();

        if (count($individuals) > 1) {
            return $this->redirectToRoute('ready');
        }
        $file = $fileNameDiscriminator->execute();

        $handler->setFileName($file);

        return $this->render('base.html.twig',
            [
                'stuff' => $handler->parse(),
            ]
        );
    }

    /**
     * @Route("/importer/ready/",name="importer_ready")
     *
     */
    public function ready()
    {
        $individuals = $this->getDoctrine()->getManager()->getRepository(Individual::class)->findAll();

        return $this->render('base.html.twig',
            [
                'stuff' => $individuals,
            ]
        );
    }

    /**
     * @Route("/",name="home")
     * @return Response
     */
    public function home() {
        return $this->redirectToRoute('wikitree_biography');
    }

    /**
     * @param DumpPeopleUsers $individual
     * @param DumpPeopleMarriage $marriage
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/dump/",name="dump")
     */
    public function dump(DumpPeopleUsers $individual, DumpPeopleMarriage $marriage): Response
    {

        $offset = $individual->execute();

        if (is_array($offset)) dd($offset);
        if ($offset > 0) {
            return $this->render('wikitree/dump_wikitree.html.twig', ['offset' => $offset]);
        }

       // $marriage->execute();

        return $this->redirectToRoute('wikitree_biography');
    }
}