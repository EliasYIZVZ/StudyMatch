<?php

final class HomeController extends Controller
{
    /**
     * Página de inicio con el formulario de login.
     */
    public function index()
    {
        // Métrica pública: si la base falla, el inicio igual se muestra (sin el número).
        $totalMaterias = null;

        try {
            $totalMaterias = (new Materia(Database::getConnection()))->contarActivas();
        } catch (PDOException $exception) {
            error_log('Error al contar las materias para el inicio: ' . $exception->getMessage());
        }

        $this->render('home/index', ['totalMaterias' => $totalMaterias]);
    }
}
