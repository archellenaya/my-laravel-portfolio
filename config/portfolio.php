<?php

return [
    'static' => filter_var(env('ISSTATIC', false), FILTER_VALIDATE_BOOLEAN),
    'snapshot' => resource_path('data/portfolio.json'),
];
