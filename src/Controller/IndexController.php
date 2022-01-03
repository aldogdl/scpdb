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
    
    /**
     * @Route("/pdf-cot/", name="pdf_cot")
    */
    public function pdfCot(): Response
    {
        return $this->render('pdf_cot.html.twig');
    }


}
