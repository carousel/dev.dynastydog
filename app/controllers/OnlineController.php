<?php

class OnlineController extends AuthorizedController {

    public function getIndex()
    {
        // Show the page
        return View::make('frontend.online.index');
    }

}
