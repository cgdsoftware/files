<?php

namespace LaravelEnso\Files\app\Http\Controllers\Upload;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelEnso\Files\app\Models\Upload;

class Store extends Controller
{
    public function __invoke(Request $request)
    {
        return Upload::store($request->allFiles());
    }
}
