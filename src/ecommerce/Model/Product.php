<?php

namespace Zantolov\ZambEcommerce\Model;

use Zantolov\Zamb\Model\BaseModel;

class Product extends BaseModel
{
    protected $table = 'products';
    public static $rules = array(
        'title' => 'required',
        'description' => 'required',
        'price' => 'numeric',
    );

    protected $fillable = array('id', 'title', 'description', 'price');

    public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
    public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called

    public $relatedIds = array(
        'tags' => array()
    );

    /* RELATED MODELS START */
    /**********************/
    public function tagsLoadIds()
    {
        $this->relatedIds['tags'] = $this->tags()->lists('tag_id');
    }

    public function tagsSave($tags)
    {
        if (!empty($tags)) {
            $this->tags()->sync($tags);
        } else {
            $this->tags()->detach();
        }

    }
    /**********************/
    /* RELATED MODELS END */

    public function images()
    {
        return $this->morphToMany('Image', 'imageable');
    }


    public function detachImages($images)
    {
        $images = json_decode($images, true);
        if (count($images)) {
            $this->images()->detach($images);
        }
    }

    public function tags()
    {
        return $this->morphToMany('Tag', 'taggable');
    }

    public function getLeadingImage()
    {
        $image = $this->getImages()->first();
        if (empty($image)) {
            $image = \Helpers\ImageHelper::getPlaceholder();
        }
        return $image;
    }

    public function getImages()
    {
        $images = $this->images()->get();
        if ($images->count() < 1) {
            $images->add(\Helpers\ImageHelper::getPlaceholder());
        }
        return $images;
    }

}
