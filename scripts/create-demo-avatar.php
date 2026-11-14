<?php

// A deterministic, non-personal image for the upload walkthrough.
$image = imagecreatetruecolor(900, 600);
$background = imagecolorallocate($image, 36, 55, 74);
$accent = imagecolorallocate($image, 63, 143, 168);
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $background);
imagefilledellipse($image, 450, 300, 420, 420, $accent);
imagesetthickness($image, 6);
imageellipse($image, 450, 300, 440, 440, $white);
imagestring($image, 5, 405, 292, 'TFA7 POS', $white);
if (! is_dir(__DIR__.'/../docs/fixtures')) {
    mkdir(__DIR__.'/../docs/fixtures', 0755, true);
}
imagepng($image, __DIR__.'/../docs/fixtures/demo-avatar.png');
imagedestroy($image);
