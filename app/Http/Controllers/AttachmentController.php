<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Asset;
use App\Ticket;
use App\AssetMaintenanceLog;

class AttachmentController extends Controller
{
    /**
     * Upload file for any model
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'model_type' => 'required|in:asset,ticket,maintenance,maintenance_log',
            'model_id' => 'required|integer',
            'collection' => 'required|string',
        ]);

        try {
            $model = $this->getModel($request->model_type, $request->model_id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found'
                ], 404);
            }

            $media = $model->addMediaFromRequest('file')
                          ->toMediaCollection($request->collection);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'url' => $media->getUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all attachments for a model
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'model_type' => 'required|in:asset,ticket,maintenance',
            'model_id' => 'required|integer',
            'collection' => 'nullable|string',
        ]);

        try {
            $model = $this->getModel($request->model_type, $request->model_id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found'
                ], 404);
            }

            $query = $model->getMedia();
            
            if ($request->collection) {
                $query = $model->getMedia($request->collection);
            }

            $attachments = $query->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'size' => $media->human_readable_size,
                    'mime_type' => $media->mime_type,
                    'collection' => $media->collection_name,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('M d, Y H:i'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $attachments
            ]);

        } catch (\Exception $e) {
            Log::error('Get attachments error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attachments'
            ], 500);
        }
    }

    /**
     * Download attachment
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        try {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);
            
            return response()->download($media->getPath(), $media->file_name);

        } catch (\Exception $e) {
            Log::error('Download attachment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }
    }

    /**
     * Delete attachment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::findOrFail($id);
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete attachment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file'
            ], 500);
        }
    }

    /**
     * Get model instance based on type and ID
     *
     * @param string $type
     * @param int $id
     * @return mixed
     */
    private function getModel($type, $id)
    {
        switch ($type) {
            case 'asset':
                return Asset::find($id);
            case 'ticket':
                return Ticket::find($id);
            case 'maintenance':
            case 'maintenance_log':
                return AssetMaintenanceLog::find($id);
            default:
                return null;
        }
    }

    /**
     * Bulk upload files
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
            'model_type' => 'required|in:asset,ticket,maintenance,maintenance_log',
            'model_id' => 'required|integer',
            'collection' => 'required|string',
        ]);

        try {
            $model = $this->getModel($request->model_type, $request->model_id);
            
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found'
                ], 404);
            }

            $uploaded = [];
            $errors = [];

            foreach ($request->file('files') as $file) {
                try {
                    $media = $model->addMedia($file)
                                  ->toMediaCollection($request->collection);
                    
                    $uploaded[] = [
                        'id' => $media->id,
                        'name' => $media->file_name,
                        'url' => $media->getUrl(),
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => count($errors) === 0,
                'message' => count($uploaded) . ' files uploaded successfully',
                'data' => [
                    'uploaded' => $uploaded,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files'
            ], 500);
        }
    }
}
