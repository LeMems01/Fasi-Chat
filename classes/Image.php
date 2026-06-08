<?php
/**
 * Image — Fichier image avec compression GD
 * Concept POO : Héritage, Polymorphisme, extension GD
 */
class Image extends Fichier
{
    public function getTypeLabel(): string { return 'Image'; }
    public function getIcone(): string     { return 'fa-image'; }

    /**
     * Traite l'image : redimensionnement et compression via l'extension GD
     */
    public function traiterFichier(string $tmpPath, string $destPath): bool
    {
        $info = @getimagesize($tmpPath);
        if ($info === false) return false;

        [$width, $height, , $mime] = [$info[0], $info[1], $info[2], $info['mime']];

        $src = match($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png'  => @imagecreatefrompng($tmpPath),
            'image/gif'  => @imagecreatefromgif($tmpPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
            default      => false,
        };
        if ($src === false) return copy($tmpPath, $destPath); // fallback

        // Redimensionnement si nécessaire
        $maxW = IMAGE_MAX_WIDTH;
        $maxH = IMAGE_MAX_HEIGHT;

        if ($width > $maxW || $height > $maxH) {
            $ratio = min($maxW / $width, $maxH / $height);
            $nW    = (int)($width  * $ratio);
            $nH    = (int)($height * $ratio);
            $dst   = imagecreatetruecolor($nW, $nH);

            if (in_array($mime, ['image/png', 'image/gif'])) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $t = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $nW, $nH, $t);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nW, $nH, $width, $height);
            imagedestroy($src);
            $src = $dst;
        }

        // Sauvegarde compressée
        $ok = match($mime) {
            'image/jpeg' => imagejpeg($src, $destPath, IMAGE_QUALITY),
            'image/png'  => imagepng($src, $destPath, (int)round((100 - IMAGE_QUALITY) * 9 / 100)),
            'image/gif'  => imagegif($src, $destPath),
            'image/webp' => function_exists('imagewebp') ? imagewebp($src, $destPath, IMAGE_QUALITY) : imagejpeg($src, $destPath . '.jpg', IMAGE_QUALITY),
            default      => false,
        };

        imagedestroy($src);
        if ($ok && file_exists($destPath)) $this->taille = filesize($destPath);
        return (bool)$ok;
    }
}
