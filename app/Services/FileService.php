<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Class FileService
 *
 * Handles file uploading, storage, retrieval and deletion,
 * including attaching files to models with metadata tracking.
 */
class FileService
{
    /**
     * Upload a file to a specified disk, ensuring the file name is made unique.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $disk
     * @param  string  $path
     * @return string Stored file path
     */
    public function uploadFile(UploadedFile $file, string $disk = 'public', string $path = 'uploads'): string
    {
        // Get the base file name without extension
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Get the file extension
        $extension = $file->getClientOriginalExtension();

        // Ensure filename is unique
        $uniqueFileName = $this->generateUniqueFileName($fileName, $extension, $disk, $path);

        // Store and return file path
        return $file->storeAs($path, $uniqueFileName, $disk);
    }

    /**
     * Generate a unique file name to prevent overwriting.
     *
     * @param  string  $fileName
     * @param  string  $extension
     * @param  string  $disk
     * @param  string  $path
     * @return string
     */
    private function generateUniqueFileName(string $fileName, string $extension, string $disk, string $path): string
    {
        $uniqueFileName = "{$fileName}.{$extension}";
        $counter = 1;

        // Check existence and append incrementing number if needed
        while (Storage::disk($disk)->exists("{$path}/{$uniqueFileName}")) {
            $uniqueFileName = "{$fileName}-{$counter}.{$extension}";
            $counter++;
        }

        return $uniqueFileName;
    }

    /**
     * Upload and associate a file with a given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Http\UploadedFile  $uploadedFile
     * @param  int  $typeId
     * @param  string  $disk
     * @param  string  $path
     * @return \App\Models\File
     */
    public function attachFile($model, UploadedFile $uploadedFile, int $typeId, string $disk = 'public', string $path = 'uploads'): File
    {
        $filePath = $this->uploadFile($uploadedFile, $disk, $path);

        return File::create([
            'model_id'      => $model->id,
            'model_type'    => get_class($model),
            'type_id'       => $typeId,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path'          => $filePath,
            'user_id'       => Auth::id(),
        ]);
    }

    /**
     * Delete a file from storage and its DB record.
     *
     * @param  \App\Models\File  $file
     * @param  string  $disk
     * @return bool
     */
    public function deleteFile(File $file, string $disk = 'public'): bool
    {
        if ($file->path && Storage::disk($disk)->exists($file->path)) {
            Storage::disk($disk)->delete($file->path);
        }

        return $file->delete();
    }

    /**
     * Get the contents of a stored file.
     *
     * @param  string  $filePath
     * @param  string  $disk
     * @return string|false
     */
    public function getFileContent(string $filePath, string $disk = 'public')
    {
        return Storage::disk($disk)->get($filePath);
    }

    /**
     * Check if a file exists in storage.
     *
     * @param  string  $filePath
     * @param  string  $disk
     * @return bool
     */
    public function fileExists(string $filePath, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($filePath);
    }

    /**
     * Get the public URL for a stored file.
     *
     * @param  string  $filePath
     * @param  string  $disk
     * @return string
     */
    public function getFileUrl(string $filePath, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Get all files linked to a given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  int|null  $typeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getModelFiles($model, ?int $typeId = null)
    {
        $query = File::where('model_id', $model->id)->where('model_type', get_class($model));

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query->get();
    }
}
