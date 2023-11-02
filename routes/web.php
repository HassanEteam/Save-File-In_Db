<?php

use App\Models\Attachment;
use App\Models\Chunk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $attachments =  Attachment::all();
    return view('welcome', compact('attachments'));
});

Route::post('/upload', function (Request $req) {

    try {
        DB::beginTransaction();

        foreach ($req->file('files') as $file) {
            $attachment = new Attachment([
                'name' => $file->getClientOriginalName(),
                'hashname' => $file->hashname(),
                'source' => $file->guessClientExtension(),
                'mime_type' => $file->getClientMimeType(),
            ]);
            $attachment->save();

            $chunkSize = 1048576; // 1 MB

            $contents = file_get_contents($file->getRealPath());
            $chunks = str_split($contents, $chunkSize);

            $chunkData = array_map(function ($chunk) use ($attachment) {
                return [
                    'chunk' => base64_encode($chunk),
                    'attachment_id' => $attachment->id,
                ];
            }, $chunks);

            Chunk::insert($chunkData);

            Log::info($file->getClientOriginalName() . " processed.");
        }

        /*$attachment = new Attachment();
        foreach ($req->file('files') as $i => $file) {
            // dd($file->getClientMimeType());
            $filename = $file->getClientOriginalName();
            $hashname = $file->hashname();
            $extension = $file->guessClientExtension();
            $mime = $file->getClientMimeType();
            $attachment->create([
                'name' => $filename,
                'hashname' => $hashname,
                'extension' => $extension,
                'mime_type' => $mime,
            ]);

            $attachment->save();
            // dd($attachment->id);
            $chunkSize = 1000; // or any other value you prefer

            $contents = file_get_contents($file->getRealPath());
            $chunks = str_split($contents, $chunkSize);

            foreach ($chunks as $chunk) {
                Chunk::create([
                    'chunk' => base64_encode($chunk),
                    'attachment_id' => $attachment->id,
                    // 'attachment_id' => ++$i,
                ]);
            }

            Log::info("$i \n  " . $file->getClientOriginalName() . " \n");
        }
        dd($req->all());*/

        DB::commit();
        return redirect()->back()->with(['message' => 'Uploaded Successfully!!', 'success' => true]);
    } catch (Exception $ex) {
        DB::rollback();
        return redirect()->back()->with(['message' => 'Something went wrong!!', 'success' => false]);
        dd($ex->getMessage(), $ex);
    }
});

Route::get('/download-file/{hash}', function ($hash) {

    $attachment = null;
    if ($hash == 'All') {
        $attachment = Attachment::with('chunks')->get();
        // dd($attachment[0]->chunks);
    } else {
        $attachment = Attachment::with('chunks')->where('hashname', $hash)->first();
    }
    if ($attachment) {
        if ($hash == 'All') {
            $zipName = storage_path('app\public\files.zip');
            $zip = new ZipArchive();
            if ($zip->open($zipName, ZipArchive::CREATE) === TRUE) {
                foreach ($attachment as $i => $item) {
                    $fileData = '';

                    foreach ($item->chunks as $val) {
                        $fileData .= base64_decode($val->chunk);
                    }
                    $filename = $i . '_' . $item->name;
                    // $fileContent = base64_decode($fileData);
                    $zip->addFromString($filename, $fileData);
                }

                $zip->close();
                return response()->download($zipName)->deleteFileAfterSend(true);
            }
        } else {

            $fileData = '';
            foreach ($attachment->chunks as $item) {
                $fileData .= base64_decode($item->chunk);
            }
            // $fileContent = base64_decode($fileData);
            $filename = $attachment->name;
            // Create a response object with the file content and set the appropriate headers
            $response = response($fileData)
                ->header('Content-Type', $attachment->mime_type)
                ->header("Content-Disposition", "attachment; filename=$filename");
            // Return the response object to initiate the download
            return $response;
        }
    }
    return redirect()->back();
});

Route::get('/delete-file/{id}', function ($id) {
    if ($id && $id == "All") {
        Chunk::truncate();
        Attachment::truncate();
        return redirect()->back()->with(['message' => 'All Files Deleted Successfully!!', 'success' => true]);
    } elseif ($id) {
        Chunk::where('attachment_id', $id)->delete();
        Attachment::find($id)->delete();
        return redirect()->back()->with(['message' => 'File Deleted!!', 'success' => true]);
    } else
        return redirect()->back()->with(['message' => 'Something went wrong!!', 'success' => false]);
});
