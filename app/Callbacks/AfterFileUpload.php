<?php

namespace App\Callbacks;

use App\Enums\Category;
use App\Models\Document;
use App\Models\File as ModelFile;
use LivewireFilemanager\Filemanager\Models\Media;

class AfterFileUpload
{
    /**
     * Handle the after upload event.
     * Updates the code to show in filemanager with user, category, date, size, format.
     */
    public static function handle(Media $media, array $fileInfo): void
    {
        // Get or create document for this media
        $document = Document::firstOrCreate(
            ['name' => pathinfo($media->file_name, PATHINFO_FILENAME)],
            [
                'category' => self::getCategoryFromExtension(pathinfo($media->file_name, PATHINFO_EXTENSION)),
            ]
        );

        // Create or update the file record with all metadata
        ModelFile::updateOrCreate(
            ['media_id' => $media->id],
            [
                'document_id' => $document->id,
                'path' => $media->file_name,
                'file_size' => $media->size,
                'mime' => $media->mime_type,
                'version' => 1,
                'user_id' => auth()->id(),
            ]
        );
    }

    /**
     * Get category from file extension
     */
    private static function getCategoryFromExtension(string $extension): Category
    {
        $extension = strtolower($extension);
        
        // Map extensions to categories
        $categoryMap = [
            'dwg' => Category::Blueprint,
            'dxf' => Category::Blueprint,
            'pdf' => Category::Manual,
            'doc' => Category::Manual,
            'docx' => Category::Manual,
            'xls' => Category::Report,
            'xlsx' => Category::Report,
            'ppt' => Category::Specs,
            'pptx' => Category::Specs,
            'jpg' => Category::Photo,
            'jpeg' => Category::Photo,
            'png' => Category::Photo,
            'gif' => Category::Photo,
            'webp' => Category::Photo,
        ];

        return $categoryMap[$extension] ?? Category::Report;
    }
}
