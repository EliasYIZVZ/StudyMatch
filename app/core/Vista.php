<?php

/**
 * Funciones de ayuda para las vistas.
 */
final class Vista
{
    /** Escapa un valor para mostrarlo en HTML. */
    public static function e($valor)
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Texto de un mensaje listo para HTML: escapado, con los links http(s)
     * convertidos en enlaces y los saltos de línea respetados.
     */
    public static function enlazar($texto)
    {
        $partes = preg_split('~(https?://[^\s<>"]+)~iu', (string) $texto, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';

        foreach ($partes as $indice => $parte) {
            if ($indice % 2 === 0) {
                $html .= self::e($parte);
                continue;
            }

            // Puntuación final que no forma parte del link (ej. "mirá https://x.com.")
            $resto = '';
            if (preg_match('~[.,;:!?)\]]+$~u', $parte, $coincidencia)) {
                $resto = $coincidencia[0];
                $parte = substr($parte, 0, -strlen($resto));
            }

            $html .= '<a href="' . self::e($parte) . '" target="_blank" rel="noopener noreferrer nofollow">'
                . self::e($parte) . '</a>' . self::e($resto);
        }

        return nl2br($html, false);
    }

    /** Iniciales de un nombre (máx. 2 letras). */
    public static function iniciales($nombre)
    {
        $partes = preg_split('/\s+/u', trim((string) $nombre), -1, PREG_SPLIT_NO_EMPTY);
        $letras = '';
        foreach (array_slice($partes, 0, 2) as $parte) {
            $letras .= mb_substr($parte, 0, 1, 'UTF-8');
        }

        return mb_strtoupper($letras !== '' ? $letras : '?', 'UTF-8');
    }

    /** Fecha de la base de datos con formato local. */
    public static function fecha($valor, $formato = 'd/m/Y H:i')
    {
        $timestamp = strtotime((string) $valor);

        return $timestamp === false ? '' : date($formato, $timestamp);
    }

    /** Etiqueta y estilo de cada estado de una solicitud. */
    public static function estadoSolicitud($estado)
    {
        $estados = [
            'pendiente'  => ['Pendiente', 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
            'aceptada'   => ['Aceptada', 'bg-success-subtle text-success border border-success-subtle'],
            'rechazada'  => ['Rechazada', 'bg-danger-subtle text-danger border border-danger-subtle'],
            'cancelada'  => ['Cancelada', 'bg-secondary-subtle text-secondary border'],
            'finalizada' => ['Finalizada', 'bg-primary-subtle text-primary border border-primary-subtle'],
        ];

        return isset($estados[$estado]) ? $estados[$estado] : [ucfirst((string) $estado), 'bg-secondary-subtle text-secondary border'];
    }

    /** Nombre visible de una modalidad de tutoría. */
    public static function modalidad($modalidad)
    {
        $nombres = ['Virtual' => 'Virtual', 'Presencial en UTN' => 'Presencial en UTN', 'Hibrido' => 'Híbrido'];

        return isset($nombres[$modalidad]) ? $nombres[$modalidad] : (string) $modalidad;
    }

    /** Ícono de Bootstrap Icons para una modalidad. */
    public static function iconoModalidad($modalidad)
    {
        $iconos = ['Virtual' => 'bi-laptop', 'Presencial en UTN' => 'bi-building', 'Hibrido' => 'bi-arrow-left-right'];

        return isset($iconos[$modalidad]) ? $iconos[$modalidad] : 'bi-geo-alt';
    }

    /** Medios de pago guardados como SET ("efectivo,transferencia") -> nombres visibles. */
    public static function mediosPago($valor)
    {
        $nombres = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'mercado_pago' => 'Mercado Pago'];
        $medios = [];
        foreach (explode(',', (string) $valor) as $medio) {
            if (isset($nombres[$medio])) {
                $medios[] = $nombres[$medio];
            }
        }

        return $medios;
    }

    /** Precio por clase en formato local; 0 = "Gratuita". */
    public static function precio($valor)
    {
        return (float) $valor > 0
            ? '$ ' . number_format((float) $valor, 2, ',', '.') . ' por clase'
            : 'Gratuita';
    }
}
