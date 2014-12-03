<?php

namespace Zantolov\ZambEcommerce\Controller\Admin;

use Zantolov\Zamb\Controller\AdminCRUDController;

class AdminProductsController extends AdminCRUDController
{
    /** @var  \Repository\ImageRepository $imageRepository */
    protected $imageRepository;

    /**
     * CRUD controller specifics
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->repository = new \Repository\ProductRepository();
        $this->templateRoot = 'Admin.Products';
        $this->baseRoute = 'Admin.Products';
        $this->imageRepository = new \Repository\ImageRepository();
    }


    /**
     * Show a list of all the users formatted for Datatables.
     * @return Datatables JSON
     */
    public function getData()
    {
        $items = DB::table('products')->select(array('products.id', 'products.title', 'products.created_at'));

        return Datatables::of($items)
            ->add_column('actions', $this->getActions(array(self::EDIT_ACTION, self::DELETE_ACTION)))
            //->remove_column('id')
            ->make();
    }


    /**
     * Store a newly created model in storage.
     * @return Response
     */
    public function postStore()
    {

        $model = $this->repository->getNew();
        if ($model->updateUniques()) {

            $newImages = $this->processImages($model);
            $model->images()->saveMany($newImages);

            $this->processRelatedEntities($model);
            return \Illuminate\Http\Response::create($this->getSuccessJSResponse());
        } else {
            return Redirect::back()->withErrors($model->errors())->withInput();
        }
    }


    /**
     * Update the specified model in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function postUpdate($id)
    {
        $model = $this->repository->findOrFail($id);
        if ($model->updateUniques()) {

            $newImages = $this->processImages($model);
            $model->images()->saveMany($newImages);
            $model->detachImages(Input::get('imagesToDelete'));

            $this->processRelatedEntities($model);
            return \Illuminate\Http\Response::create($this->getSuccessJSResponse());
        } else {
            return Redirect::back()->withErrors($model->errors())->withInput();
        }
    }


    /**
     * Process uploaded files and save them to temp location
     *
     * @return array
     */
    public function processImages($model)
    {

        $uploadImages = Input::file('images');
        $imagesToAttach = Input::get('imagesToAdd');
        if (!empty($imagesToAttach)) {
            $imagesToAttach = json_decode($imagesToAttach, true);
            $model->images()->attach($imagesToAttach);
        }

        if (empty($uploadImages)) {
            return array();
        }

        $images = array();

        foreach ($uploadImages as $image) {

            if (empty($image)) {
                continue;
            }

            if ($image->isValid()) {
                $images[] = $this->imageRepository->createImageByUploadedFile($image);
            }
        }

        return $images;

    }


    /**
     * Override with custom params for this method
     * @return Response
     */
    public function getCreate()
    {
        $params = array('tags' => DB::table('tags')->lists('name', 'id'));
        $this->setParamsForMethod('getCreate', $params);
        return parent::getCreate();
    }

    /**
     * Override with custom params for this method
     * @param int $id
     * @return Response
     */
    public function getEdit($id)
    {
        $params = array('tags' => DB::table('tags')->lists('name', 'id'));
        $this->setParamsForMethod('getEdit', $params);
        return parent::getEdit($id);
    }

}