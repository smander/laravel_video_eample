<?php

namespace App\Services\Media;
use App\Media;
use App\Repositories\MediaRepository;
use App\User;
use Illuminate\Support\Facades\Log;
use Validator, DB, Hash, Mail;
use Illuminate\Http\Request;
use Exception;

class MediaService{


    public $disk = 'custom'; // FS

    protected $mediaRepository ;

    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository  =   $mediaRepository;
    }

    public function save($media_request, $model){

        try{


            $prepared_data =[
                'model_type'        =>  class_basename($model),
                'model_id'          =>  $model->id,
                'collection_name'   =>  $media_request->collection_name,
                'name'              =>  $media_request->file('media')->getClientOriginalName(),
                'file_name'         =>  $media_request->file('media')->getClientOriginalName(),
                'mime_type'         =>  $media_request->file('media')->getMimeType(),
                'size'              =>  $media_request->file('media')->getSize()
            ];

            $media = $this->mediaRepository->create($prepared_data);

            if($media){
                return $media;
            }
            else{
                throw new Exception('Cannot save Media');
            }


        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }


    public function update($media_request, $model){

        try{


            $media = $this->mediaRepository->update($media_request);

            if($media){
                return $media;
            }
            else{
                throw new Exception('Cannot save Media');
            }


        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }

}
