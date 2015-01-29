<?php namespace Controllers\Admin;

use AdminController;
use View;
use DB;
use Carbon;
use Config;
use Input;
use URL;
use Validator;
use Lang;
use Redirect;
use HelpCategory;
use HelpPage;
use Exception;

class HelpController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Help Categories', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/help/help/category/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/help'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Help Pages', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/help/help/page/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/help/help/pages'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new HelpCategory;

        if (Input::get('search'))
        {
            $id    = Input::get('id');
            $title = Input::get('title');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($title) > 0)
            {
                $results = $results->where('title', 'LIKE', '%'.$title.'%');
            }
        }

        $helpCategories = $results->orderBy('title', 'asc')->paginate();

        // Show the page
        return View::make('admin/help/index', compact('helpCategories'));
    }

    public function getHelpPages()
    {
        $results = new HelpPage;

        if (Input::get('search'))
        {
            $id    = Input::get('id');
            $title = Input::get('title');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($title) > 0)
            {
                $results = $results->where('title', 'LIKE', '%'.$title.'%');
            }
        }

        $helpPages = $results->orderBy('title', 'asc')->paginate();

        // Show the page
        return View::make('admin/help/help_pages', compact('helpPages'));
    }

    public function getCreateHelpPage()
    {
        // Show the page
        return View::make('admin/help/create_help_page');
    }

    public function getCreateHelpCategory()
    {
        $parentHelpCategories = HelpCategory::orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/help/create_help_category', compact('parentHelpCategories'));
    }

    public function getEditHelpCategory($helpCategory)
    {
        $parentHelpCategories = HelpCategory::where('id', '<>', $helpCategory->id)->orderBy('title', 'asc')->get();
        $helpPages = HelpPage::orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/help/edit_help_category', compact('helpCategory', 'parentHelpCategories', 'helpPages'));
    }

    public function getEditHelpPage($helpPage)
    {
        $helpCategories = HelpCategory::orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/help/edit_help_page', compact('helpPage', 'helpCategories'));
    }

    public function getDeleteHelpCategory($helpCategory)
    {
        try
        {
            $helpCategory->delete();

            $success = Lang::get('forms/admin.delete_help_category.success');

            return Redirect::route('admin/help')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_help_category.error');
        }

        return Redirect::route('admin/help/help/category/edit', $helpCategory->id)->withInput()->with('error', $error);
    }

    public function getDeleteHelpPage($helpPage)
    {
        try
        {
            $helpPage->delete();

            $success = Lang::get('forms/admin.delete_help_page.success');

            return Redirect::route('admin/help/help/pages')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_help_page.error');
        }

        return Redirect::route('admin/help/help/page/edit', $helpPage->id)->withInput()->with('error', $error);
    }

    public function postCreateHelpCategory()
    {
        // Declare the rules for the form validation
        $rules = array(
            'title'  => 'required|max:32',
            'parent' => 'exists:help_categories,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/help/help/category/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the help category
            $helpCategory = HelpCategory::create(array( 
                'title'     => Input::get('title'), 
                'parent_id' => Input::get('parent'), 
            ));

            $success = Lang::get('forms/admin.create_help_category.success');

            return Redirect::route('admin/help/help/category/edit', $helpCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_help_category.error');
        }

        return Redirect::route('admin/help/help/category/create')->withInput()->with('error', $error);
    }

    public function postCreateHelpPage()
    {
        // Declare the rules for the form validation
        $rules = array(
            'title'   => 'required|max:32',
            'content' => 'required|max:20000',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/help/help/page/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the help page
            $helpPage = HelpPage::create(array( 
                'title'   => Input::get('title'), 
                'content' => Input::get('content'), 
            ));

            $success = Lang::get('forms/admin.create_help_page.success');

            return Redirect::route('admin/help/help/page/edit', $helpPage->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_help_page.error');
        }

        return Redirect::route('admin/help/help/page/create')->withInput()->with('error', $error);
    }

    public function postEditHelpCategory($helpCategory)
    {
        $validParentHelpCategoryIds = HelpCategory::where('id', '<>', $helpCategory->id)->lists('id');

        // Declare the rules for the form validation
        $rules = array(
            'title'  => 'required|max:32',
            'parent' => 'in:'.implode(',', $validParentHelpCategoryIds),
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/help/help/category/edit', $helpCategory->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($helpCategory)
            {
                $helpCategory->title = Input::get('title');
                $helpCategory->parent_id = Input::get('parent');
                $helpCategory->save();

                $helpPageIds = (array) Input::get('help_pages');

                // Always add -1
                $helpPageIds[] = -1;

                // Find the page IDs
                $validHelpPageIds = HelpPage::whereIn('id', $helpPageIds)->lists('id');

                $helpCategory->pages()->sync($validHelpPageIds);
            });

            $success = Lang::get('forms/admin.update_help_category.success');

            return Redirect::route('admin/help/help/category/edit', $helpCategory->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_help_category.error');
        }

        return Redirect::route('admin/help/help/category/edit', $helpCategory->id)->withInput()->with('error', $error);
    }

    public function postEditHelpPage($helpPage)
    {
        // Declare the rules for the form validation
        $rules = array(
            'title'   => 'required|max:32',
            'content' => 'required|max:20000',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/help/help/page/edit', $helpPage->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            DB::transaction(function() use ($helpPage)
            {
                $helpPage->title   = Input::get('title');
                $helpPage->content = Input::get('content');
                $helpPage->save();

                $helpCategoryIds = (array) Input::get('help_categories');

                // Always add -1
                $helpCategoryIds[] = -1;

                // Find the page IDs
                $validHelpCategoryIds = HelpCategory::whereIn('id', $helpCategoryIds)->lists('id');

                $helpPage->categories()->sync($validHelpCategoryIds);
            });

            $success = Lang::get('forms/admin.update_help_page.success');

            return Redirect::route('admin/help/help/page/edit', $helpPage->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_help_page.error');
        }

        return Redirect::route('admin/help/help/page/edit', $helpPage->id)->withInput()->with('error', $error);
    }

}
