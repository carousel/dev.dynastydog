<?php

class HelpController extends AuthorizedController {

    public function getIndex()
    {
        $categories = HelpCategory::where('parent_id', null)->orderBy('title', 'asc')->get();

        $pages = HelpPage::has('categories', '<', 1)->orderBy('title', 'asc')->get();

        // Show the page
        return View::make('frontend/help/index', compact('categories', 'pages'));
    }

    public function getCategory($category)
    {
        $subCategories = $category->subCategories()->orderBy('title', 'asc')->get();
        $pages = $category->pages()->orderBy('title', 'asc')->get();

        // Show the page
        return View::make('frontend/help/category', compact('category', 'subCategories', 'pages'));
    }

    public function getPage($page)
    {
        // Show the page
        return View::make('frontend/help/page', compact('page'));
    }

}
