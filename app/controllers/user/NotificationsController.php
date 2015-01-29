<?php namespace Controllers\User;

use AuthorizedController;
use View;
use UserNotification;
use Input;
use Lang;
use Redirect;
use DB;

class NotificationsController extends AuthorizedController {

    public function getIndex()
    {
        $notifications = $this->currentUser->notifications()->orderBy('id', 'desc')->paginate(10);

        // Show the page
        return View::make('frontend/user/notifications/index', compact('notifications'));
    }

    public function postRead()
    {
        $notification = UserNotification::find(Input::get('id', null));

        // We're going to show a success message regardless of what happens
        $success = Lang::get('forms/user.mark_notification_as_read.success');

        if ( ! is_null($notification) and $notification->user_id == $this->currentUser->id and $notification->isUnread())
        {
            // Read it
            $notification->unread = false;
            $notification->save();
        }

        return Redirect::back()->with('success', $success);
    }

    public function postReadAll()
    {
        // Update all notifications of this user as read
        DB::table('user_notifications')
            ->where('user_id', $this->currentUser->id)
            ->where('unread', true)
            ->update(array(
                'unread' => false, 
            ));

        $success = Lang::get('forms/user.mark_all_notifications_as_read.success');

        return Redirect::back()->with('success', $success);
    }

}
