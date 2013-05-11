<?php

namespace Tyler\TopTrumpsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class JSONDeckController extends Controller
{
    public function allAction()
    {
        return new Response("{'a':1}"); 
    }

    public function createAction()
    {
        
    }

    public function getAction($deckId)
    {
        
    }

    public function removeAction($deckId)
    {
    
    }

    public function updateAction($deckId)
    {

    }
}
