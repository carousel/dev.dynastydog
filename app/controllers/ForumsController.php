<?php 

class ForumsController extends AuthorizedController {

    public function __construct()
    {
        parent::__construct();

        $this->beforeFilter(function()
        {
            if ( ! $this->currentUser->agreedToCommunityGuidelines())
            {
                // Redirect them to the rules
                return Redirect::route('community_guidelines');
            }
        }, array('except' => 'postAgreeToCommunityGuidelines'));
    }

    public function getIndex()
    {
        // Get all the forums
        $forums = Forum::with(array(
                'topics.author', 
            ))
            ->orderBy('title', 'asc')
            ->get(); 

        // Show the page
        return View::make('frontend/forums/index', compact('forums'));
    }

    public function getActiveTopics()
    {
        // Get all active topics
        $topics = ForumTopic::with(array(
                'author', 
                'forum', 
            ))
            ->orderBy('last_activity_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(25)
            ->get(); 

        // Show the page
        return View::make('frontend/forums/active_topics', compact('topics'));
    }

    public function getUserTopics()
    {
        // Get all topics made by the current user
        $topics = $this->currentUser->forumTopics()->with(array(
                'forum', 
            ))
            ->orderBy('last_activity_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(25)
            ->get(); 

        // Show the page
        return View::make('frontend/forums/user_topics', compact('topics'));
    }

    public function getCreateTopic()
    {
        // Get all forums
        $forums = Forum::orderBy('title', 'asc')->get(); 

        // Show the page
        return View::make('frontend/forums/create_topic', compact('forums'));
    }

    public function postCreateTopic()
    {
        // Declare the rules for the form validation
        $rules = array(
            'forum' => 'required|exists:forums,id',
            'title' => 'required|max:255',
            'body'  => 'required|max:10000',
        );

        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);

        // If validation fails, we'll exit the operation now.
        if ($validator->fails())
        {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->with('error', $validator->errors()->first());
        }

        try
        {
            $topic = null;

            // Start transaction
            DB::transaction(function() use (&$topic)
            {
                // Create the topic
                $topic = ForumTopic::create(array(
                    'author_id' => $this->currentUser->id, 
                    'forum_id'  => Input::get('forum'), 
                    'title'     => Input::get('title'), 
                    'last_activity_at' => Carbon::now(), 
                    'replies'   => 0, 
                    'views'     => 0, 
                    'locked'    => false, 
                    'stickied'  => false, 
                ));

                // Add the opening post
                $post = ForumPost::create(array(
                    'author_id' => $this->currentUser->id, 
                    'topic_id'  => $topic->id, 
                    'body'      => Input::get('body'), 
                ));
            });

            $success = Lang::get('forms/forums.create_topic.success');

            return Redirect::route('forums/topic', $topic->id)->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/forums.create_topic.error');
        }

        return Redirect::route('forums/topic/create')->withInput()->with('error', $error);
    }

    public function getForum($forum)
    {
        $topics = $forum->topics()->orderBy('stickied', 'desc')->orderBy('last_activity_at', 'desc')->orderBy('id', 'desc')->paginate(15);

        // Show the page
        return View::make('frontend/forums/forum', compact('forum', 'topics'));
    }

    public function getTopic($topic)
    {
        // Add a view
        $topic->increment('views');

        $posts = $topic->posts()->orderBy('created_at', 'asc')->orderBy('id', 'asc')->paginate(10);

        // Get all the forums
        $forums = Forum::orderBy('title', 'asc')->get(); 

        // Show the page
        return View::make('frontend/forums/topic', compact('topic', 'posts', 'forums'));
    }

    public function postTopic($topic)
    {
        if (Input::get('reply'))
        {
            return $this->postReplyToTopic($topic);
        }
        else if (Input::get('preview'))
        {
            return $this->postPreviewReply($topic);
        }

        // Redirect to the main news page
        return Redirect::route('forum/topic', $topic->id);
    }

    public function getBumpTopic($topic)
    {
        try
        {
            if ($topic->isLocked())
            {
                throw new Dynasty\ForumTopics\Exceptions\IsLockedException;
            }

            // Adjust the topic's last activity
            $topic->last_activity_at = Carbon::now();
            $topic->save();

            $success = Lang::get('forms/forums.bump_topic.success');

            // Redirect to the topic
            return Redirect::route('forums/topic', $topic->id)->with('success', $success);
        }
        catch (Dynasty\ForumTopics\Exceptions\IsLockedException $e)
        {
            $error = Lang::get('forms/forums.bump_topic.locked');
        }

        return Redirect::route('forum/topic', $topic->id)->with('error', $error);
    }
    public function postPreviewReply($topic)
    {
        $now = Carbon::now();

        $forumPost = new ForumPost(array(
            'author_id'  => $this->currentUser->id, 
            'body'       => Input::get('body'), 
            'created_at' => $now, 
            'updated_at' => $now, 
        ));


        // Redirect back to the creation page
        return Redirect::back()->withInput(array('preview' => $forumPost) + Input::all());
    }

    public function postReplyToTopic($topic)
    {
        try
        {
            if ($topic->isLocked())
            {
                throw new Dynasty\ForumTopics\Exceptions\IsLockedException;
            }

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
                return Redirect::back()->withInput()->with('error', $validator->errors()->first());
            }

            // Start transaction
            DB::transaction(function() use ($topic)
            {
                // Create the reply
                $post = ForumPost::create(array(
                    'author_id' => $this->currentUser->id, 
                    'topic_id'  => $topic->id, 
                    'body'      => Input::get('body'), 
                ));

                // Increment the topic's reply count
                $topic->last_activity_at = Carbon::now();
                $topic->replies++;
                $topic->save();

                if ( ! is_null($topic->author) and $topic->author->id != $this->currentUser->id)
                {
                    $params = array(
                        'topicRoute' => URL::route('forums/topic', $topic->id), 
                        'topic'  => $topic->title, 
                        'author' => $this->currentUser->nameplate(), 
                    );

                    $body = Lang::get('notifications/user.reply_to_forum_topic.to_forum_topic_author', array_map('htmlentities', array_dot($params)));

                    $topic->author->notify($body, UserNotification::TYPE_INFO);
                }
            });

            $paginated = $topic->posts()->paginate(10);
            $lastPage  = $paginated->getLastPage();

            // Redirect to the topic
            $success = Lang::get('forms/forums.reply_to_topic.success');

            return Redirect::route('forums/topic', ['topic' => $topic->id, 'page' => $lastPage])->with('success', $success);
        }
        catch (Dynasty\ForumTopics\Exceptions\IsLockedException $e)
        {
            $error = Lang::get('forms/forums.reply_to_topic.locked');
        }
        catch (Exception $e)
        {
            $error = Lang::get('forms/forums.reply_to_topic.error');
        }

        return Redirect::route('forums/topic', $topic->id)->withInput()->with('error', $error);
    }

    public function postAgreeToCommunityGuidelines()
    {
        if ( ! $this->currentUser->agreedToCommunityGuidelines())
        {
            $this->currentUser->read_community_rules = true;
            $this->currentUser->save();
        }

        return Redirect::route('forums');
    }

}
