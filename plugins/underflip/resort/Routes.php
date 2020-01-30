<?php

use Underflip\Resort\Models\Resort;
use Underflip\Resort\Models\Statistic;

Route::get('api/resorts', function() {
    $resorts = Resort::all();
    return Response::make($resorts)
    ->header('Access-Control-Allow-Origin', '*');
});

?>