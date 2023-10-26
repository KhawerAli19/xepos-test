<?php

namespace App\Core\Traits;

use Intervention\Image\Facades\Image;

trait ImageOptimize
{

    public function optimizeable($request)
    {
        $image = $request->file('image');

        $imageName = time() . '.' . $image->extension();

        // $destinationPathThumbnail = storage_path('app/public/user/image');
        $destinationPathThumbnail = public_path('/thumbnail');
        // if (file_exists($destinationPathThumbnail . '' . $imageName)) {
        //     unlink($destinationPathThumbnail . '' . $imageName);
        // }
        $img = Image::make($image->path());
        $img->resize(100, 100, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail . '/' . $imageName);
        $destinationPath = public_path('/images');
        $image->move($destinationPath, $imageName);
        return $imageName;
    }
}
