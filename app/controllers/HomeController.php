<?php

final class HomeController extends Controller
{
    /**
     * Página de inicio con el formulario de login.
     */
    public function index()
    {
        $this->render('home/index');
    }
}
