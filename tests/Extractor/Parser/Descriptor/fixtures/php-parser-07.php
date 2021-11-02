<?php

$internationalization->formatMessage(
    [
        'id' => 'photos.count',
        'defaultMessage' => <<<EOM

            You have {numPhotos, plural,
                =0 {no photos.}
                =1 {one photo.}
                other {# photos.}
            }

            EOM,
        'description' => "  A description with \n multiple lines    \n   and extra whitespace.   ",
    ],
    [
        'numPhotos' => $photosCount,
    ],
);
