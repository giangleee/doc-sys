<?php

namespace App\Services;

use App\Helper\Constant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

class FileService
{
    public function getFile($path)
    {
        try {
            return Storage::get($path);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': File Not Found ' . $e->getMessage());
        }
        return false;
    }

    public function getMimeType($path)
    {
        return Storage::mimeType($path);
    }

    public function uploadFile($path, $file)
    {
        return Storage::putFile($path, $file);

    }

    public function uploadFileChangeName($path, $file, $fileName)
    {
        return Storage::putFileAs($path, $file, $fileName);

    }

    public function downloadFile($fileInfo)
    {
        try {
            return Storage::download($fileInfo->url, $fileInfo->original_name);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': File Not Found ' . $e->getMessage());
            throw $e;
        }
    }

    public function signAPrivateDistribution($path, $expiresIn = 0)
    {
        try {
            $resourceKey = config('filesystems.disks.s3.url'). '/' . $path;
            if ($expiresIn > 0) {
                $expires = time() + $expiresIn;
            } else {
                $expires = time() + (int)config('filesystems.disks.s3.expired_time');
            }
            $privateKey = config('filesystems.disks.s3.path_private_key');
            $keyPairId = config('filesystems.disks.s3.key_cloudfront');

            $cloudFrontClient = new CloudFrontClient([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region')
            ]);

            return $cloudFrontClient->getSignedUrl([
                'url' => $resourceKey,
                'expires' => $expires,
                'private_key' => $privateKey,
                'key_pair_id' => $keyPairId
            ]);

        } catch (AwsException $e) {
            return 'Error: ' . $e->getAwsErrorMessage();
        }
    }

    public function getAllFile($path = '')
    {
        return Storage::allFiles($path);
    }

    public function deleteFile($path)
    {
        try {
            Storage::delete($path);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
