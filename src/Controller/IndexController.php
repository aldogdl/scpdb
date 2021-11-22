<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    
    /**
     * @Route("/", name="intro_index")
    */
    public function introIndex(): Response
    {
        
        return $this->render('base.html.twig');
    }


}
