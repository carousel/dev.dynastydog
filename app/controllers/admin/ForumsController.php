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
use Forum;
use ForumTopic;
use ForumPost;
use Exception;

class ForumsController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->sidebarGroups = array(
            array(
                'heading' => 'Forums', 
                'items' => array(
                    array(
                        'title' => 'New', 
                        'url' => URL::route('admin/forums/forum/create'), 
                    ), 
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/forums'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Forum Posts', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/forums/forum/posts'), 
                    ), 
                ), 
            ),
            array(
                'heading' => 'Forum Topics', 
                'items' => array(
                    array(
                        'title' => 'Existing', 
                        'url' => URL::route('admin/forums/forum/topics'), 
                    ), 
                ), 
            ),
        );
    }

    public function getIndex()
    {
        $results = new Forum;

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

        $forums = $results->orderBy('title', 'asc')->paginate();

        // Show the page
        return View::make('admin/forums/index', compact('forums'));
    }

    public function getForumPosts()
    {
        $results = new ForumPost;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $body = Input::get('body');
            $status = Input::get('status', 'all');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($body) > 0)
            {
                $results = $results->where('body', 'LIKE', '%'.$body.'%');
            }

            if ($status === 'trashed')
            {
                $results = $results->onlyTrashed();
            }
            else if ($status === 'all')
            {
                $results = $results->withTrashed();
            }
        }
        else
        {
            $results = $results->withTrashed();
        }

        $forumPosts = $results->orderBy('body', 'asc')->paginate();

        // Show the page
        return View::make('admin/forums/forum_posts', compact('forumPosts'));
    }

    public function getForumTopics()
    {
        $results = new ForumTopic;

        if (Input::get('search'))
        {
            $id = Input::get('id');
            $title = Input::get('title');
            $status = Input::get('status');

            if (strlen($id) > 0)
            {
                $results = $results->where('id', $id);
            }

            if (strlen($title) > 0)
            {
                $results = $results->where('title', 'LIKE', '%'.$title.'%');
            }

            if ($status === 'trashed')
            {
                $results = $results->onlyTrashed();
            }
            else if ($status === 'all')
            {
                $results = $results->withTrashed();
            }
        }
        else
        {
            $results = $results->withTrashed();
        }

        $forumTopics = $results->orderBy('title', 'asc')->paginate();

        // Show the page
        return View::make('admin/forums/forum_topics', compact('forumTopics'));
    }

    public function postDeleteForumTopics($forum)
    {
        try
        {
            // Grab the ids
            $forumTopicIds = (array) Input::get('forum_topics');

            // Always add -1
            $forumTopicIds[] = -1;

            // Remove the topics
            ForumTopic::whereIn('id', $forumTopicIds)->delete();

            $success = Lang::get('forms/admin.delete_forum_topics.success');

            return Redirect::route('forums/forum', $forum->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_forum_topics.error');
        }

            return Redirect::route('forums/forum', $forum->id)->with('error', $error);
    }

    public function getCreateForum()
    {
        // Show the page
        return View::make('admin/forums/create_forum');
    }

    public function getEditForum($forum)
    {
        // Show the page
        return View::make('admin/forums/edit_forum', compact('forum'));
    }

    public function getEditForumTopic($forumTopic)
    {
        $forums = Forum::orderBy('title', 'asc')->get();

        // Show the page
        return View::make('admin/forums/edit_forum_topic', compact('forumTopic', 'forums'));
    }

    public function getEditForumPost($forumPost)
    {
        // Show the page
        return View::make('admin/forums/edit_forum_post', compact('forumPost'));
    }

    public function getDeleteForum($forum)
    {
        try
        {
            $forum->delete();

            $success = Lang::get('forms/admin.delete_forum.success');

            return Redirect::route('admin/forums')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_forum.error');
        }

        return Redirect::route('admin/forums/forum/edit', $forum->id)->withInput()->with('error', $error);
    }

    public function getDeleteForumPost($forumPost)
    {
        try
        {
            $forumPost->delete();

            $success = Lang::get('forms/admin.delete_forum_post.success');

            return Redirect::route('admin/forums/forum/posts')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_forum_post.error');
        }

        return Redirect::route('admin/forums/forum/post/edit', $forumPost->id)->withInput()->with('error', $error);
    }

    public function getPermanentlyDeleteForumPost($forumPostId)
    {
        try
        {
            $forumPost = ForumPost::where('id', $forumPostId)->withTrashed()->first();

            if (is_null($forumPost))
            {
                App::abort(404, 'Forum post does not exist!');
            }

            $forumPost->forceDelete();

            $success = Lang::get('forms/admin.permanently_delete_forum_post.success');

            return Redirect::route('admin/forums/forum/posts')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.permanently_delete_forum_post.error');
        }

        return Redirect::route('admin/forums/forum/posts')->withInput()->with('error', $error);
    }

    public function getRestoreForumPost($forumPostId)
    {
        try
        {
            $forumPost = ForumPost::where('id', $forumPostId)->withTrashed()->first();

            if (is_null($forumPost))
            {
                App::abort(404, 'Forum post does not exist!');
            }

            $forumPost->restore();

            $success = Lang::get('forms/admin.restore_forum_post.success');

            return Redirect::route('admin/forums/forum/post/edit', $forumPost->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.restore_forum_post.error');
        }

        return Redirect::route('admin/forums/forum/posts')->withInput()->with('error', $error);
    }

    public function getDeleteForumTopic($forumTopic)
    {
        try
        {
            $forumTopic->delete();

            $success = Lang::get('forms/admin.delete_forum_topic.success');

            return Redirect::route('admin/forums/forum/topics')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.delete_forum_topic.error');
        }

        return Redirect::route('admin/forums/forum/topic/edit', $forumTopic->id)->withInput()->with('error', $error);
    }

    public function getPermanentlyDeleteForumTopic($forumTopicId)
    {
        try
        {
            $forumTopic = ForumTopic::where('id', $forumTopicId)->withTrashed()->first();

            if (is_null($forumTopic))
            {
                App::abort(404, 'Forum topic does not exist!');
            }

            $forumTopic->forceDelete();

            $success = Lang::get('forms/admin.permanently_delete_forum_topic.success');

            return Redirect::route('admin/forums/forum/topics')->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.permanently_delete_forum_topic.error');
        }

        return Redirect::route('admin/forums/forum/topics')->withInput()->with('error', $error);
    }

    public function getRestoreForumTopic($forumTopicId)
    {
        try
        {
            $forumTopic = ForumTopic::where('id', $forumTopicId)->withTrashed()->first();

            if (is_null($forumTopic))
            {
                App::abort(404, 'Forum topic does not exist!');
            }

            $forumTopic->restore();

            $success = Lang::get('forms/admin.restore_forum_topic.success');

            return Redirect::route('admin/forums/forum/topic/edit', $forumTopic->id)->withInput()->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.restore_forum_topic.error');
        }

        return Redirect::route('admin/forums/forum/topics')->withInput()->with('error', $error);
    }

    public function postCreateForum()
    {
        // Declare the rules for the form validation
        $rules = array(
            'title'       => 'required|max:32',
            'description' => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/forums/forum/create')->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            // Create the forum
            $forum = Forum::create(array(
                'title'       => Input::get('title'), 
                'description' => Input::get('description'), 
            ));

            $success = Lang::get('forms/admin.create_forum.success');

            return Redirect::route('admin/forums/forum/edit', $forum->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.create_forum.error');
        }

        return Redirect::route('admin/forums/forum/create')->withInput()->with('error', $error);
    }

    public function postEditForum($forum)
    {
        // Declare the rules for the form validation
        $rules = array(
            'title'       => 'required|max:32',
            'description' => 'max:255',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/forums/forum/edit', $forum->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $forum->title       = Input::get('title');
            $forum->description = Input::get('description');
            $forum->save();

            $success = Lang::get('forms/admin.update_forum.success');

            return Redirect::route('admin/forums/forum/edit', $forum->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_forum.error');
        }

        return Redirect::route('admin/forums/forum/edit', $forum->id)->withInput()->with('error', $error);
    }

    public function postUpdateForumTopic($forumTopic)
    {
        // Declare the rules for the form validation
        $rules = array(
            'title' => 'required|max:255',
            'forum' => 'required|exists:forums,id',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::route('admin/forums/forum/topic/edit', $forumTopic->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $forumTopic->title     = Input::get('title');
            $forumTopic->forum_id  = Input::get('forum');
            $forumTopic->editor_id = $this->currentUser->id;
            $forumTopic->save();

            $success = Lang::get('forms/admin.update_forum_topic.success');

            return Redirect::route('admin/forums/forum/topic/edit', $forumTopic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_forum_topic.error');
        }

        return Redirect::route('admin/forums/forum/topic/edit', $forumTopic->id)->withInput()->with('error', $error);
    }

    public function postUpdateForumPost($forumPost)
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
            return Redirect::route('admin/forums/forum/post/edit', $forumPost->id)->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $forumPost->body      = Input::get('body');
            $forumPost->editor_id = $this->currentUser->id;
            $forumPost->save();

            $success = Lang::get('forms/admin.update_forum_post.success');

            return Redirect::route('admin/forums/forum/post/edit', $forumPost->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/admin.update_forum_post.error');
        }

        return Redirect::route('admin/forums/forum/post/edit', $forumPost->id)->withInput()->with('error', $error);
    }

    public function getLockForumTopic($topic)
    {
        // Lock or unlock the topic
        if ($topic->isLocked())
        {
            $topic->unlock();

            // Prepare the success message
            $success = Lang::get('forms/admin.unlock_forum_topic.success');
        }
        else
        {
            $topic->lock();

            // Prepare the success message
            $success = Lang::get('forms/admin.lock_forum_topic.success');
        }

        // Redirect to the topic
        return Redirect::route('forums/topic', $topic->id)->with('success', $success);
    }

    public function getStickyForumTopic($topic)
    {
        // Sticky or unsticky the topic
        if ($topic->isStickied())
        {
            $topic->unsticky();

            // Prepare the success message
            $success = Lang::get('forms/admin.unsticky_forum_topic.success');
        }
        else
        {
            $topic->sticky();

            // Prepare the success message
            $success = Lang::get('forms/admin.sticky_forum_topic.success');
        }

        // Redirect to the topic
        return Redirect::route('forums/topic', $topic->id)->with('success', $success);
    }

    public function postMoveForumTopic($topic)
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'forum' => 'required|exists:forums,id',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::back()->withInput()->with('error', $validator->errors()->first());
            }

            // Move the topic
            $topic->forum_id = Input::get('forum');
            $topic->save();

            // Redirect to the topic
            $success = Lang::get('forms/admin.move_forum_topic.success');

            return Redirect::route('forums/topic', ['topic' => $topic->id])->with('success', $success);
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/admin.move_forum_topic.error');
        }

        return Redirect::route('forums/topic', $topic->id)->withInput()->with('error', $error);
    }

}
