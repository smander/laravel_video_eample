<?php

namespace App\Http\Controllers\User;

use Validator,DB, Hash, Mail;
use Illuminate\Http\Request;
use App\Http\Requests\UserMediaUpload\UserMediaUpload as UserMediaUploadRequest;
use App\Http\Requests\UserPhotos\ProfilePhoto as UserSetDefaultPhotoRequest;
use App\Http\Requests\UserPhotos\Delete as DeleteUserPhotoRequest;
use App\Http\Requests\UserPhotos\ReorderPhotos as ReorderPhotosRequest;
use App\User;
use App\UserPhotos;
use App\Media;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Media\MediaCollection;
use App\Events\Photos\SetDefaultPhoto;
use App\Services\Clarifai;

class UserPhotosController extends \App\Http\Controllers\Controller
{


    public function show(Request $request)
    {
        try {

            $user = User::current()->firstOrFail();
            $user_medias= $user->photos()->paginate(10);

            return new MediaCollection(MediaResource::collection($user_medias));
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }

    /**
     * Store method
     *  @param UserMediaUploadRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserMediaUploadRequest $request)
    {

        try {
            $user = User::current()->firstOrFail();


            if(empty($request->private) && $request->private == 1){
                $media  =   $user->addMediaFromRequest('image')->toMediaCollection('photos', 's3');
            }
            else{
                $media  =   $user->addMediaFromRequest('image')->toMediaCollection('photos', 's3');
            }

            $order_position =   UserPhotos::getLatestOrderPosition();


            $data   = [
                'user_id'   =>      $request->user()->id,
                'media_id'  =>      $media->id,
                'private'   =>      empty($request->private) ? '0' : '1',
                'avatar'    =>      empty($request->avatar) ? '0' : '1',
                'order_position'    =>  $order_position,
                'status'            =>  'active',
            ];

            $user_media = UserPhotos::create($data);

            //Check Public Photos on Clarafai
            if(empty($request->private)){
                $check_photo = (new Clarifai\Client())->Analyze($media);
                if(!$check_photo){
                    $user_media->status ==  'rejected';
                    $user_media->save();
                    return response()->json(['success' => false, 'data' => 'Your photo is not validated for our application'], 402);
                }
            }

            return response()->json(['success' => true, 'data' => $media->id ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }

    public function setDefaultPhoto(UserSetDefaultPhotoRequest $request)
    {

        try {
            //remove avatar flag
            UserPhotos::removeProfilePhotoAttribute();

            $userPhoto = UserPhotos::where('media_id', $request->media_id)->firstOrFail();
            $userPhoto->avatar =   1;
            $userPhoto->save();

            event(new SetDefaultPhoto($userPhoto));

            return response()->json(['success' => true, 'data' => $userPhoto->media_id ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }

    public function getDefaultPhoto(Request $request)
    {

        try {

            $user = User::current()->firstOrFail();
            $userPhoto  =   $user->getDefaultPhoto();

            return new MediaResource($userPhoto);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }


    public function getPublicPhotos(Request $request)
    {
        try {
            $user = User::current()->firstOrFail();
            $user_medias= $user->getPublicPhotos();

            return new MediaCollection(MediaResource::collection($user_medias));

        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }


    public function getPrivatePhotos(Request $request)
    {
        try {
            $user = User::current()->firstOrFail();
            $user_medias= $user->getPrivatePhotos();

            return new MediaCollection(MediaResource::collection($user_medias));

        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }

    }

    public function destroy(DeleteUserPhotoRequest $request) {

        try {
            $photo = UserPhotos::where('media_id',$request->media_id)->delete();
            $media  =   Media::where('id',$request->media_id)->delete();

            return response()->json(['success' => true, 'data' =>  'Photo was deleted'], 200);


        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }
    }


    public function reorder(Request $request){

        try {

            $media_arr  =   $request->media_ids;
            if (is_array($media_arr) or ($media_arr instanceof Traversable)) {

                $order_position = 1;
                foreach($media_arr  as $media_id){
                    $photo = UserPhotos::current()->where('media_id',$media_id)->firstOrFail();
                    //Update Avatar
                    if($order_position ==  1){
                        if( UserPhotos::removeProfilePhotoAttribute()){
                            $photo->avatar  =   1;
                        }
;                    }
                    $photo->order_position  =   $order_position;
                    $photo->update();
                    $order_position++;
                }
            }
            else{
                return response()->json(['success' => false, 'data' => 'Please check income params'], 500);
            }

            return response()->json(['success' => true, 'data' =>  'Photo was reordered'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }
    }



    public function appealPhoto(Request $request){

        try {

            $user_photo =   UserPhotos::where('media_id',$request->media_id)->firstorFaiL();
            $user_photo->status =   'pending';
            $user_photo->save();

            return response()->json(['success' => true, 'data' =>  'Photo appeal was applied'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => 'Oops, something went wrong!'], 500);
        }
    }




    
}
