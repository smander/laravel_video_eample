<?php

namespace App\Repositories;


interface MediaRepositoryInterface
{
    /**
     * Create New
     *
     * @param int
     * @return array
     */
    public function create(array $attributes);

    /**
    * Get's a media by it's ID
    *
    * @param int
    * @return collection
    */
    public function get($media_id);

    /**
    * Get's all media.
    *
    * @return mixed
    */
    public function all();

    /**
    * Deletes a media.
    *
    * @param int
    */
    public function delete($media_id);

    /**
    * Updates a media.
    *
    * @param int
    * @param array
    */
    public function update($media_id, array $media_data);

}
