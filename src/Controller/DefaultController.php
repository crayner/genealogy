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

use App\Manager\FileNameDiscriminator;
use App\Manager\GedFileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/",name="home")
     * @param FileNameDiscriminator $fileNameDiscriminator
     * @param GedFileHandler $handler
     * @return Response
     */
    public function home(FileNameDiscriminator $fileNameDiscriminator, GedFileHandler $handler): Response
    {
        $file = $fileNameDiscriminator->execute();

        $handler->setFileName($file);

        return $this->render('base.html.twig',
            [
                'stuff' => $handler->parse(),
            ]
        );
    }
}