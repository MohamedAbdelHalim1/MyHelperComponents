<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Store image
        $path = $request->file('file')->store('public/assets/images');
        $image = Image::create(['path' => $path]);

        // Return image ID and path in response
        return response()->json(['id' => $image->id, 'path' => Storage::url($path)]);
    }

    public function uploadAll(Request $request)
    {
        $request->validate([
            'files.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $uploadedPaths = [];
        
        foreach ($request->file('files') as $file) {
            // Store the image
            $path = $file->store('public/assets/images');
            
            // Save the image record in the database
            $image = Image::create(['path' => $path]);
            
            // Collect the image ID and URL for response
            $uploadedPaths[] = [
                'id' => $image->id,
                'url' => Storage::url($path)
            ];
        }

        return response()->json(['success' => true, 'images' => $uploadedPaths]);
    }

    

    public function destroy($id)
    {
        $image = Image::find($id);

        if ($image) {
            // Delete the image file from storage
            Storage::delete($image->path);
            $image->delete();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Image not found'], 404);
    }
}
