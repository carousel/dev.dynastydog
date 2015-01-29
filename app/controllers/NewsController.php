<?php

class NewsController extends AuthorizedController {

    public function getIndex()
    {
        $newsPosts = NewsPost::orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(10);

        // Show the page
        return View::make('frontend/news/index', compact('newsPosts'));
    }

    public function getPost($newsPost)
    {
    	$comments = $newsPost->comments()->with('author')->orderBy('created_at', 'asc')->orderBy('id', 'asc')->paginate(10);

        // Show the page
        return View::make('frontend/news/post', compact('newsPost', 'comments'));
    }

    public function postCommentOnPost($newsPost)
    {
        try
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
                return Redirect::back()->withInput()->with('error', $validator->errors()->first());
            }

            // Start transaction
            DB::transaction(function() use ($newsPost)
            {
                // Create the reply
                $message = NewsPostComment::create(array(
                    'author_id'    => $this->currentUser->id, 
                    'news_post_id' => $newsPost->id, 
                    'body'         => Input::get('body'), 
                ));
            });

            $paginated = $newsPost->comments()->paginate(10);
            $lastPage  = $paginated->getLastPage();

            $success = Lang::get('forms/user.comment_on_news_post.success');

            return Redirect::route('news/post', ['newsPost' => $newsPost->id, 'page' => $lastPage])->with('success', $success);
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.comment_on_news_post.error');
        }

        return Redirect::route('news/post', $newsPost->id)->withInput()->with('error', $error);
    }

    public function postVoteOnPoll($newsPoll)
    {
        try
        {
            // Declare the rules for the form validation
            $rules = array(
                'answer' => 'required|exists:news_poll_answers,id',
            );

            // Create a new validator instance from our validation rules
            $validator = Validator::make(Input::all(), $rules);

            // If validation fails, we'll exit the operation now.
            if ($validator->fails())
            {
                // Ooops.. something went wrong
                return Redirect::back()->withInput()->with('error', $validator->errors()->first());
            }

            // Make sure they haven't already voted on this poll
            if ($newsPoll->votedOnBy($this->currentUser))
            {
                throw new Dynasty\NewsPolls\Exceptions\AlreadyVotedOnException;
            }

            // Create the vote
            NewsPollAnswerVote::create(array(
                'user_id'   => $this->currentUser->id, 
                'answer_id' => Input::get('answer'), 
            ));;

            $success = Lang::get('forms/user.vote_on_news_poll.success');

            return Redirect::back()->with('success', $success);
        }
        catch(Dynasty\NewsPolls\Exceptions\AlreadyVotedOnException $e)
        {
            $error = Lang::get('forms/user.vote_on_news_poll.already_voted_on');
        }
        catch(Exception $e)
        {
            $error = Lang::get('forms/user.vote_on_news_poll.error');
        }

        return Redirect::back()->withInput()->with('error', $error);
    }

}
