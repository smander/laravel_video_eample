<?php

namespace App\Repositories;

use App\Media;

class MediaRepository implements MediaRepositoryInterface
{

    protected $mediaModel;

    public function __construct(Media $mediaModel)
    {
        $this->mediaModel = $mediaModel;
    }

    /**
    * Get's a post by it's ID
    *
    * @param int
    * @return collection
    */
    public function get($media_id)
    {
        return $this->mediaModel::find($media_id);
    }

    /**
     * Get's a post by it's ID
     *
     * @param int
     * @return collection
     */
    public function create(array $attributes)
    {
        return $this->mediaModel->create($attributes);
    }

    /**
    * Get's all posts.
    *
    * @return mixed
    */
    public function all()
    {
        return $this->mediaModel::all();
    }

    /**
    * Deletes a post.
    *
    * @param int
     *
     * @return bool
    */
    public function delete($media_id)
    {
        return $this->mediaModel::destroy($media_id);
    }

    /**
    * Updates a post.
    *
    * @param int
    * @param array
     * @return Media
    */
    public function update($media_id, array $media_data)
    {
        return $this->mediaModel::find($media_id)->update($media_data);
    }

}
