<?php

/**
 * Procesa las fotos de perfil subidas por los usuarios.
 *
 * - Acepta JPG, PNG o WEBP de hasta 2 MB (se valida el contenido real, no la extensión).
 * - Recorta al centro en un cuadrado y la guarda como JPG de 256x256.
 *   Volver a generar la imagen elimina metadatos (ej. ubicación GPS) y
 *   cualquier contenido extra escondido en el archivo original.
 * - Se guarda en la carpeta del usuario: public/uploads/usuarios/{id}/perfil_<aleatorio>.jpg
 * - El tipo se detecta con getimagesize() (incluido siempre en PHP); no depende
 *   de la extensión fileinfo, que en MAMP viene desactivada.
 *
 * Los errores pensados para el usuario se lanzan como RuntimeException.
 */
final class FotoPerfil
{
    public const MAX_BYTES = 2097152; // 2 MB
    private const LADO = 256;
    private const MAX_PIXELES = 40000000; // evita imágenes gigantes que agoten la memoria
    private const CARPETA_USUARIOS = 'public/uploads/usuarios';
    private const TIPOS = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Valida y guarda la imagen subida. Devuelve la ruta relativa para guardar en la base.
     */
    public static function guardarSubida($archivo, $idUsuario)
    {
        if (!is_array($archivo) || !isset($archivo['error']) || is_array($archivo['error'])) {
            throw new RuntimeException('Elegí una imagen para subir.');
        }

        switch ($archivo['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Elegí una imagen para subir.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('La imagen supera el máximo de 2 MB.');
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException('La imagen no se subió completa. Intentá nuevamente.');
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                error_log('FotoPerfil: PHP no puede usar su carpeta temporal (código ' . $archivo['error'] . '). Revisar upload_tmp_dir en php.ini.');
                throw new RuntimeException('El servidor no puede recibir archivos en este momento.');
            default:
                error_log('FotoPerfil: error de subida con código ' . $archivo['error'] . '.');
                throw new RuntimeException('No se pudo subir la imagen. Intentá nuevamente.');
        }

        if ($archivo['size'] > self::MAX_BYTES) {
            throw new RuntimeException('La imagen supera el máximo de 2 MB.');
        }

        $origen = $archivo['tmp_name'];
        if (!is_uploaded_file($origen)) {
            throw new RuntimeException('No se pudo subir la imagen. Intentá nuevamente.');
        }

        // Tipo real según el contenido del archivo (no la extensión ni lo que informa el navegador)
        $medidas = @getimagesize($origen);
        $tipo = $medidas !== false && isset($medidas['mime']) ? $medidas['mime'] : '';
        if (!in_array($tipo, self::TIPOS, true)) {
            throw new RuntimeException('El archivo debe ser una imagen JPG, PNG o WEBP.');
        }
        if ($medidas[0] < 1 || $medidas[1] < 1) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }
        if ($medidas[0] * $medidas[1] > self::MAX_PIXELES) {
            throw new RuntimeException('La imagen tiene una resolución demasiado grande.');
        }

        if (!function_exists('imagecreatetruecolor')) {
            error_log('FotoPerfil: la extensión GD de PHP no está habilitada.');
            throw new RuntimeException('El servidor no puede procesar imágenes en este momento.');
        }

        $imagen = self::abrir($origen, $tipo);
        if ($imagen === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $imagen = self::corregirOrientacion($imagen, $origen, $tipo);
        $cuadrada = self::recortarCuadrado($imagen);
        imagedestroy($imagen);

        $carpetaRelativa = self::CARPETA_USUARIOS . '/' . (int) $idUsuario;
        $carpeta = dirname(APP_PATH) . '/' . $carpetaRelativa;
        if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true)) {
            imagedestroy($cuadrada);
            error_log('FotoPerfil: no se pudo crear la carpeta ' . $carpeta);
            throw new RuntimeException('No se pudo guardar la imagen. Intentá nuevamente.');
        }

        $nombre = 'perfil_' . bin2hex(random_bytes(8)) . '.jpg';
        $guardado = imagejpeg($cuadrada, $carpeta . '/' . $nombre, 85);
        imagedestroy($cuadrada);

        if (!$guardado) {
            error_log('FotoPerfil: no se pudo escribir ' . $nombre);
            throw new RuntimeException('No se pudo guardar la imagen. Intentá nuevamente.');
        }

        return $carpetaRelativa . '/' . $nombre;
    }

    /**
     * Borra del disco una foto subida (nunca un avatar precargado).
     */
    public static function borrarSiEsSubida($ruta)
    {
        // Solo fotos subidas (carpeta del usuario o la ubicación anterior), nunca avatares
        if (!is_string($ruta)
            || !preg_match('~^public/uploads/(usuarios/\d+/perfil_[a-f0-9]{16}|perfiles/u\d+_[a-f0-9]{16})\.jpg$~', $ruta)) {
            return;
        }

        $archivo = dirname(APP_PATH) . '/' . $ruta;
        if (is_file($archivo)) {
            @unlink($archivo);
        }
    }

    private static function abrir($ruta, $tipo)
    {
        switch ($tipo) {
            case 'image/jpeg':
                return @imagecreatefromjpeg($ruta);
            case 'image/png':
                return @imagecreatefrompng($ruta);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($ruta) : false;
            default:
                return false;
        }
    }

    /** Las fotos de celular guardan la rotación en los metadatos EXIF. */
    private static function corregirOrientacion($imagen, $ruta, $tipo)
    {
        if ($tipo !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $imagen;
        }

        $exif = @exif_read_data($ruta);
        $grados = ['3' => 180, '6' => -90, '8' => 90];
        $orientacion = isset($exif['Orientation']) ? (string) $exif['Orientation'] : '1';

        if (!isset($grados[$orientacion])) {
            return $imagen;
        }

        $rotada = imagerotate($imagen, $grados[$orientacion], 0);
        if ($rotada === false) {
            return $imagen;
        }
        imagedestroy($imagen);

        return $rotada;
    }

    private static function recortarCuadrado($imagen)
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $lado = min($ancho, $alto);

        $destino = imagecreatetruecolor(self::LADO, self::LADO);
        // Fondo blanco para imágenes con transparencia (PNG / WEBP)
        imagefill($destino, 0, 0, imagecolorallocate($destino, 255, 255, 255));
        imagecopyresampled(
            $destino,
            $imagen,
            0,
            0,
            (int) (($ancho - $lado) / 2),
            (int) (($alto - $lado) / 2),
            self::LADO,
            self::LADO,
            $lado,
            $lado
        );

        return $destino;
    }
}
