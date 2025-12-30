<?php

require __DIR__ . '/vendor/autoload.php';

$html = '<figure class="image" style="width:25%; display:block; margin-left:auto; margin-right:auto;"><img src="test.jpg" style="aspect-ratio:1152/912;"></figure>';

echo "INPUT:\n" . $html . "\n\n";

$result = App\Helpers\HtmlSanitizer::sanitize($html);

echo "OUTPUT:\n" . $result . "\n";
