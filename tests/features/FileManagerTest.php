<?php

namespace Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LaravelEnso\Core\app\Exceptions\EnsoException;
use LaravelEnso\FileManager\Classes\FileManager;
use Tests\TestCase;

class FileManagerTest extends TestCase
{
    private $fileManager;
    private $files;

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->fileManager = new FileManager('uploadTest', config('enso.config.paths.temp'));
        $this->files       = [
            'firstFile'  => UploadedFile::fake()->image('picture.png'),
            'secondFile' => UploadedFile::fake()->create('document.doc'),
        ];
    }

    /** @test */
    public function upload_files_to_temp()
    {
        $this->fileManager->startUpload($this->files);
        $uploadedFiles = $this->fileManager->getUploadedFiles();

        $this->assertEquals(2, $uploadedFiles->count());

        $uploadedFiles->each(function ($file) {
            Storage::assertExists('temp/' . $file['saved_name']);
        });

        $this->fileManager->deleteTempFiles();

        $uploadedFiles->each(function ($file) {
            Storage::assertMissing('temp/' . $file['saved_name']);
        });
    }

    /** @test */
    public function commit_upload()
    {
        $this->fileManager->startUpload($this->files)
            ->commitUpload();

        $uploadedFiles = $this->fileManager->getUploadedFiles();

        $uploadedFiles->each(function ($file) {
            Storage::assertExists('uploadTest/' . $file['saved_name']);
        });

        $this->cleanUp();
    }

    /** @test */
    public function can_upload_file_with_valid_extension()
    {
        $file = UploadedFile::fake()->image('image.png');
        $this->fileManager->setValidExtensions(['png']);

        $this->fileManager->startUpload([$file])->commitUpload();

        Storage::assertExists('uploadTest/' . $file->hashName());

        $this->cleanUp();
    }

    /** @test */
    public function cant_upload_file_with_invalid_extension()
    {
        $file = UploadedFile::fake()->create('invalid.extension');
        $this->fileManager->setValidExtensions(['png', 'doc']);

        $this->expectException(EnsoException::class);

        $this->fileManager->startUpload([$file])->commitUpload();
    }

    /** @test */
    public function can_upload_file_with_valid_mime_type()
    {
        $file = UploadedFile::fake()->image('image.png');
        $this->fileManager->setValidMimeTypes(['image/png']);

        $this->fileManager->startUpload([$file])->commitUpload();

        Storage::assertExists('uploadTest/' . $file->hashName());

        $this->cleanUp();
    }

    /** @test */
    public function cant_upload_file_with_invalid_mime_type()
    {
        $file = UploadedFile::fake()->image('image.png');
        $this->fileManager->setValidMimeTypes(['application/msword']);

        $this->expectException(EnsoException::class);

        $this->fileManager->startUpload([$file])->commitUpload();
    }

    /** @test */
    public function getInline()
    {
        $this->fileManager->startUpload($this->files)->commitUpload();
        $uploadedFile = $this->fileManager->getUploadedFiles()->first();
        $response     = $this->fileManager->getInline($uploadedFile['saved_name']);

        $this->assertEquals(200, $response->getStatusCode());

        $this->cleanUp();
    }

    /** @test */
    public function download()
    {
        $this->fileManager->startUpload($this->files)->commitUpload();
        $uploadedFile = $this->fileManager->getUploadedFiles()->first();
        $response     = $this->fileManager->download($uploadedFile['original_name'], $uploadedFile['saved_name']);

        $this->assertEquals(200, $response->getStatusCode());

        $this->cleanUp();
    }

    private function cleanUp()
    {
        Storage::deleteDirectory('uploadTest');
    }
}
