<?php

/**
 * Páginas del área privada. El Router ya verificó la sesión antes de llegar aquí.
 */
final class DashboardController extends Controller
{
    public function alumno()
    {
        $this->render('dashboard/dashboard');
    }

    public function tutor()
    {
        $this->render('dashboard/dashboard-tutor');
    }

    public function buscarTutor()
    {
        $this->render('dashboard/buscar-tutor');
    }
}
