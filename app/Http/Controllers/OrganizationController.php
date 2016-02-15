<?php

namespace App\Http\Controllers;

use App\Organization;
use NilPortugues\Laravel5\JsonApi\Controller\JsonApiController;

class OrganizationController extends JsonApiController
{
    /**
     * Return the Eloquent model that will be used
     * to model the JSON API resources.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getDataModel()
    {
        return new Organization();
    }


}