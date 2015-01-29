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
use NewsPost;
use NewsPostComment;
use NewsPoll;
use NewsPollAnswer;
use User;
use UserNotification;

use Exception;
use Dynasty\NewsPosts\Exceptions as DynastyNewsPostsExceptions;

class NewsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Posts', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/news/post/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/news'), 
                    ), 
                    array(
                        'title' => 'Comments', 
                        'url' => URL::route('admin/news/post/comments'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Polls', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/news/poll/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/news/polls'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new NewsPost;

        if (Input::get('search'))
        {
            $id = Input::get('id');
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

        $newsPosts = $results->orderBy('created_at', 'desc')->paginate();

        // Show the page
        return View::make('admin/news/index', compact('newsPosts'));
    }

    public function getNewsPolls()
    {
        $results = new NewsPoll;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $question = Input::get('question');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($question) > 0)
            {
                $results = $results->where('question', 'LIKE', '%'.$question.'%');
            }
        }

        $newsPolls = $results->orderBy('question', 'asc')->paginate();

        // Show the page
        return View::make('admin/news/news_polls', compact('newsPolls'));
    }

    public function getNewsPostComments()
    {
        $results = new NewsPostComment;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $body = Input::get('body');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($body) > 0)
            {
                $results = $results->where('body', 'LIKE', '%'.$body.'%');
            }
        }

        $newsPostComments = $results->orderBy('created_at', 'desc')->paginate();

        // Show the page
        return View::make('admin/news/news_post_comments', compact('newsPostComments'));
    }

    public function getCreateNewsPost()
    {
        // Show the page
        return View::make('admin/news/create_news_post');
    }

    public function getCreateNewsPoll()
    {
        // Show the page
        return View::make('admin/news/create_news_poll');
    }

    public function getDeleteNewsPost($newsPost)
    {
        try
        {
            $newsPost->delete();

            $success = Lang::get('forms/admin.delete_news_post.success');

            return Redirect::route('admin/news')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_news_post.error');
        }

        return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('error', $error);
    }

    public function getDeleteNewsPoll($newsPoll)
    {
        try
        {
            $newsPoll->delete();

            $success = Lang::get('forms/admin.delete_news_poll.success');

            return Redirect::route('admin/news/polls')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_news_poll.error');
        }

        return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $error);
    }

    public function getDeleteNewsPostComment($newsPostComment)
    {
        try
        {
            $newsPostComment->delete();

            $success = Lang::get('forms/admin.delete_news_post_comment.success');

            return Redirect::route('admin/news/post/comments')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_news_post_comment.error');
        }

        return Redirect::route('admin/news/post/comment/edit', $newsPostComment->id)->withInput()->with('error', $error);
    }

    public function getDeleteNewsPollAnswer($newsPollAnswer)
    {
        // Grab the news poll
        $newsPoll = $newsPollAnswer->poll;

        try
        {
            $newsPollAnswer->delete();

            $success = Lang::get('forms/admin.delete_news_poll_answer.success');

            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_news_poll_answer.error');
        }

        return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $error);
    }

    public function getEditNewsPost($newsPost)
    {
        // Grab all the polls
        $newsPolls = NewsPoll::orderBy('question', 'asc')->get();

        // Grab the polls attached
        $attachedNewsPollIds = $newsPost->polls()->lists('id');

        // Grab the comments
        $newsPostComments = $newsPost->comments()->orderBy('created_at', 'desc')->get();

        // Show the page
        return View::make('admin/news/edit_news_post', compact('newsPost', 'newsPolls', 'attachedNewsPollIds', 'newsPostComments'));
    }

    public function getEditNewsPoll($newsPoll)
    {
        // Grab the answers
        $newsPollAnswers = $newsPoll->answers()->orderBy('body', 'asc')->get();

        // Grab the posts
        $newsPosts = $newsPoll->posts()->orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/news/edit_news_poll', compact('newsPoll', 'newsPollAnswers', 'newsPosts'));
    }

    public function getEditNewsPostComment($newsPostComment)
    {
        // Show the page
        return View::make('admin/news/edit_news_post_comment', compact('newsPostComment'));
    }

    public function getAddNewsPollToNewsPost($newsPost, $newsPoll)
    {
        try
        {
            if ($newsPost->polls->contains($newsPoll->id))
            {
                throw new DynastyNewsPostsExceptions\NewsPollAlreadyAttachedException;
                
            }

            $newsPost->polls()->attach($newsPoll);

            $success = Lang::get('forms/admin.add_news_poll_to_news_post.success');

            return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('success', $success);
        }
        catch(DynastyNewsPostsExceptions\NewsPollAlreadyAttachedException $e)
        {
            $error = Lang::get('forms/admin.add_news_poll_to_news_post.news_poll_already_attached');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_news_poll_to_news_post.error');
        }

        return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('error', $error);
    }

    public function getRemoveNewsPollFromNewsPost($newsPost, $newsPoll)
    {
        try
        {
            $newsPost->polls()->detach($newsPoll);

            $success = Lang::get('forms/admin.remove_news_poll_from_news_post.success');

            return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.remove_news_poll_from_news_post.error');
        }

        return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('error', $error);
    }

    public function postCreateNewsPost()
    {
        // Declare the rules for the form validation
        $rules = array(
            'title' => 'required|max:255',
            'body'  => 'required',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/post/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newsPost = null;

            DB::transaction(function() use (&$newsPost)
            {
                // Create the news post
                $newsPost = NewsPost::create(array(
                    'title' => Input::get('title'), 
                    'body'  => Input::get('body'), 
                ));

                $params = array(
                    'news_post'  => $newsPost->title, 
                    'news_route' => route('news/post', $newsPost->id), 
                );

                $body = Lang::get('notifications/admin.updated_news.to_user', array_map('htmlentities', array_dot($params)));

                // Notify all users
                User::notifyAll($body, UserNotification::TYPE_INFO);
            });

            $success = Lang::get('forms/admin.create_news_post.success');

            return Redirect::route('admin/news/post/edit', $newsPost->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_news_post.error');
        }

        return Redirect::route('admin/news/post/create')->withInput()->with('error', $error);
    }

    public function postCreateNewsPoll()
    {
        // Declare the rules for the form validation
        $rules = array(
            'question' => 'required|max:255',
            'reward'   => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/poll/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the news poll
            $newsPoll = NewsPoll::create(array(
                'question' => Input::get('question'), 
                'reward'   => Input::get('reward'), 
            ));

            $success = Lang::get('forms/admin.create_news_poll.success');

            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_news_poll.error');
        }

        return Redirect::route('admin/news/poll/create')->withInput()->with('error', $error);
    }

    public function postEditNewsPost($newsPost)
    {
        // Declare the rules for the form validation
        $rules = array(
            'title' => 'required|max:255',
            'body'  => 'required',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newsPost->title = Input::get('title');
            $newsPost->body  = Input::get('body');
            $newsPost->save();

            $success = Lang::get('forms/admin.update_news_post.success');

            return Redirect::route('admin/news/post/edit', $newsPost->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_news_post.error');
        }

        return Redirect::route('admin/news/post/edit', $newsPost->id)->withInput()->with('error', $error);
    }

    public function postEditNewsPoll($newsPoll)
    {
        // Declare the rules for the form validation
        $rules = array(
            'question' => 'required|max:255',
            'reward'   => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newsPoll->question = Input::get('question');
            $newsPoll->reward  = Input::get('reward');
            $newsPoll->save();

            $success = Lang::get('forms/admin.update_news_poll.success');

            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_news_poll.error');
        }

        return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $error);
    }

    public function postEditNewsPostComment($newsPostComment)
    {
        // Declare the rules for the form validation
        $rules = array(
            'body' => 'required|max:10000',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/post/comment/edit', $newsPostComment->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newsPostComment->body = Input::get('body');
            $newsPostComment->save();

            $success = Lang::get('forms/admin.update_news_post_comment.success');

            return Redirect::route('admin/news/post/comment/edit', $newsPostComment->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_news_post_comment.error');
        }

        return Redirect::route('admin/news/post/comment/edit', $newsPost->id)->withInput()->with('error', $error);
    }

    public function postCreateNewsPollAnswer($newsPoll)
    {
        // Declare the rules for the form validation
        $rules = array(
            'answer_body' => 'required|max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the answer
            $newsPollAnswer = NewsPollAnswer::create(array(
                'poll_id' => $newsPoll->id, 
                'body' => Input::get('answer_body'), 
            ));

            $success = Lang::get('forms/admin.add_answer_to_news_poll.success');

            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.add_answer_to_news_poll.error');
        }

        return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $error);
    }

    public function postEditNewsPollAnswer($newsPollAnswer)
    {
        // Grab the news poll
        $newsPoll = $newsPollAnswer->poll;

        // Declare the rules for the form validation
        $rules = array(
            'body' => 'required|max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $newsPollAnswer->body = Input::get('body');
            $newsPollAnswer->save();

            $success = Lang::get('forms/admin.update_news_poll_answer.success');

            return Redirect::route('admin/news/poll/edit', $newsPoll->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_news_poll_answer.error');
        }

        return Redirect::route('admin/news/poll/edit', $newsPoll->id)->withInput()->with('error', $error);
    }

}
