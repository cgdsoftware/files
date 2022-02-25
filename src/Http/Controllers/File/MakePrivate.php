<?php

namespace LaravelEnso\Files\Http\Controllers\File;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use LaravelEnso\Files\Http\Resources\File as Resource;
use LaravelEnso\Files\Models\File;

class MakePrivate extends Controller
{
    use AuthorizesRequests;

    public function __invoke(File $file)
    {
        $this->authorize('manage', $file);

        $file->update(['is_public' => false]);

        return new Resource($file);
    }
}
