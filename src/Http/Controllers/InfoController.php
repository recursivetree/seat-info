<?php

namespace RecursiveTree\Seat\InfoPlugin\Http\Controllers;

use RecursiveTree\Seat\InfoPlugin\Validation\SaveArticle;
use Seat\Web\Http\Controllers\Controller;


class InfoController extends Controller
{
    public function getEditView(){
        return view("info::edit");
    }

    public function getCreateView(){
        return view("info::edit");
    }

    public function getSaveInterface(SaveArticle $request){

        return view("info::manage", [
            'article_saved' => [
                'name' => $request->name
            ]
        ]);
    }

    public function getListView(){
        return view("info::list");
    }

    public function getManageView(){
        return view("info::manage");
    }
}