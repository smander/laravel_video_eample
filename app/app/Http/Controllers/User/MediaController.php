<?php

namespace App\Http\Controllers\User;

use App\Repositories\MediaRepository;
use App\Services\Media\MediaService;
use App\Services\Media\Service;
use App\User;
use Validator,DB, Hash, Mail;
use Illuminate\Http\Request;
use App\Media;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Requests\UserPhotos\MediaStoreRequest;

class MediaController extends \App\Http\Controllers\Controller
{

    protected $mediaRepository;

    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository  =   $mediaRepository;
    }

    /**
     *  Store method
     *
     *
     *  @param MediaStoreRequest $request
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function store(MediaStoreRequest $request)
    {

        try {

            $media = (new MediaService())->save($request,auth()->user());



            if($media){
                return response()->json(['success' => true, 'data' => $media->id ], 200);
            }
            else{
                return response()->json(['success' => false, 'data' => 'Cannot save due issue'], 400);
            }

        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => $e], 500);
        }

    }

    /**
     *  Update method
     *
     *
     *  @param Request $request
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        try {

            $prepared_data = $request->all();
            $media = (new MediaService())->update($prepared_data,auth()->user());


            if($media){
                return response()->json(['success' => true, 'data' => $media->id ], 200);
            }
            else{
                return response()->json(['success' => false, 'data' => 'Cannot save due issue'], 400);
            }

        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => $e], 500);
        }

    }




    /**
     *  Store method
     *
     *
     *  @param Request $request
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function total(Request $request)
    {

        try {
            $user= User::where('username',$request->username)->firstOrFail();

            $totalVideosSize = $user->videosCount()->first()->aggregate;

            $Mbytes = number_format($totalVideosSize / 1048576, 2);


            return response()->json(['success' => true, 'data' => $Mbytes], 500);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => $e], 500);
        }

    }



    /**
     *  Store method
     *
     *
     *  @param Request $request
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {

        try {
            $media = Media::where('model_id',$request->video_id)->with('model')->firstOrFail();


            return new MediaResource($media);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => $e], 500);
        }

    }





}
