<?php

declare(strict_types=1);

$sources = [
    'mark' => 'C:\\Users\\syam8\\Downloads\\WhatsApp Image 2026-07-23 at 11.30.03 AM (1).jpeg',
    'full' => 'C:\\Users\\syam8\\Downloads\\WhatsApp Image 2026-07-23 at 11.30.03 AM.jpeg',
];

$out = __DIR__ . '/../public/assets/brand';

if (! is_dir($out)) {
    mkdir($out, 0775, true);
}

foreach ($sources as $name => $path) {
    $src = imagecreatefromjpeg($path);

    if (! $src) {
        throw new RuntimeException("Unable to open {$path}");
    }

    $w = imagesx($src);
    $h = imagesy($src);
    $minX = $w;
    $minY = $h;
    $maxX = 0;
    $maxY = 0;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($src, $x, $y);
            $r = ($rgb >> 16) & 255;
            $g = ($rgb >> 8) & 255;
            $b = $rgb & 255;

            if (! ($r > 246 && $g > 246 && $b > 246)) {
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }
    }

    $pad = 28;
    $minX = max(0, $minX - $pad);
    $minY = max(0, $minY - $pad);
    $maxX = min($w - 1, $maxX + $pad);
    $maxY = min($h - 1, $maxY + $pad);
    $cw = $maxX - $minX + 1;
    $ch = $maxY - $minY + 1;

    foreach (['dark', 'light'] as $variant) {
        $dst = imagecreatetruecolor($cw, $ch);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefilledrectangle($dst, 0, 0, $cw, $ch, imagecolorallocatealpha($dst, 0, 0, 0, 127));

        for ($yy = 0; $yy < $ch; $yy++) {
            for ($xx = 0; $xx < $cw; $xx++) {
                $rgb = imagecolorat($src, $minX + $xx, $minY + $yy);
                $r = ($rgb >> 16) & 255;
                $g = ($rgb >> 8) & 255;
                $b = $rgb & 255;

                if ($r > 246 && $g > 246 && $b > 246) {
                    continue;
                }

                if ($variant === 'light' && $r < 130 && $g < 130 && $b < 130) {
                    $avg = (int) (($r + $g + $b) / 3);
                    $v = max(218, 255 - $avg);
                    $color = imagecolorallocatealpha($dst, $v, $v, $v, 0);
                } else {
                    $color = imagecolorallocatealpha($dst, $r, $g, $b, 0);
                }

                imagesetpixel($dst, $xx, $yy, $color);
            }
        }

        imagepng($dst, "{$out}/adxon-{$name}-{$variant}.png");
        imagedestroy($dst);
    }

    imagedestroy($src);
}

echo "Logo assets generated.\n";
