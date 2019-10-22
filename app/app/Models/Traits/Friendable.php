<?php


namespace App\Models\Traits;


use App\Exceptions\Friends\DoesntHaveFriendRequestFromUser;
use App\Exceptions\FriendsDoesntHaveFriendRequestFromUser;
use App\Friendship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class Status.
 */
class Status
{
    const PENDING  = 0;
    const ACCEPTED = 1;
    const DENIED   = 2;
    const BLOCKED  = 3;
}

trait Friendable
{

    public function friends()
    {
        return $this->hasMany(Friendship::class);
    }

    public function requested()
    {
        return $this->hasMany(Friendship::class, 'friends_with_user_id');
    }

    public function unfriend($otherUser)
    {
        $otherUser->friends()->status(Status::ACCEPTED)
            ->other($this->getId())
            ->delete();

        $this->friends()->status(Status::ACCEPTED)
            ->other($otherUser->id)
            ->delete();

    }

    /**
     * Allows us to check if we are friends with another user
     *
     * @param $otherUser
     *
     * @return mixed
     */
    public function isFriendsWith($otherUser)
    {
        return $this->friends()->other($otherUser->id)->status(Status::ACCEPTED)->first();
    }

    public function getAcceptedFriendshipsIds()
    {
        return $this->friends()
            ->where('status', Status::ACCEPTED)
            ->get(['friends_with_user_id'])
            ->pluck('friends_with_user_id')
            ->toArray();
    }

    /**
     * Gets the users friends with the status required
     *
     * @param        $status
     *
     * @param string $order
     *
     * @return Builder
     */
    public function getMyFriends($status, $order = 'desc')
    {
        $query = $this
            ->whereHas('friends', function ($builder) use ($status) {
                return $builder
                    ->where('friends_with_user_id', $this->getId())
                    ->where('status', $status);
            })
            ->whereDoesntHave('blockedByOthers', function ($builder) {
                $builder->where('user_id', $this->getId());
            });

        if ($order !== null) {
            $query = $query->orderBy('username', $order);
        }

        return $query;
    }

    /**
     * Gets a list of users who have requested to be friends with me
     *
     * @param string $order
     *
     * @return mixed
     */
    public function getMyFriendRequests($order = 'desc')
    {
        return $this
            ->whereHas('friends', function ($builder) {
                return $builder
                    ->where('friends_with_user_id', $this->getId())
                    ->where('status', Status::PENDING);
            })
            ->orderBy('username', $order);
    }

    /**
     * Sends a friend request to the other user
     *
     * @param Model $otherUser
     *
     * @param null  $status
     *
     * @return bool
     * @throws DoesntHaveFriendRequestFromUser
     */
    public function sendFriendRequestTo(Model $otherUser)
    {
        if ($request = $this->hasDeclinedRequestFrom($otherUser)) {
            $request->delete();
        }

        if (!$this->canBeFriends($otherUser)) {
            return false;
        }

        //If we have a friend request from the other user and we're sending one to them
        //At this point, this should make us friends, we both have mutual interest in becoming friends.
        /*if ($otherUser->hasSentRequestTo($this)) {
            list($myRequest, $otherRequest) = $this->acceptFriendRequest($otherUser);

            return $myRequest;
        }*/

        $request                       = new Friendship;
        $request->friends_with_user_id = $otherUser->id;
        $request->status               = Status::PENDING;
        $request->user_id              = $this->getId();
        $request->save();

        return $request;
    }

    /**
     *
     * @param Model $otherUser
     */
    public function removeFriendOrRequest(Model $otherUser)
    {
        if ($request = $this->hasSentRequestTo($otherUser)) {
            $request->delete();
        }
    }

    /**
     * Accepts the friend request from the other user
     *
     * This basically creates another row in the table for that user_id, with the other users id.
     * It will then the current users to accepted and set the others to accepted also
     *
     * @param Model $otherUser
     *
     * @return array | MyRequest, OthersRequest
     * @throws DoesntHaveFriendRequestFromUser
     */
    public function acceptFriendRequest(Model $otherUser)
    {
        $request = $otherUser->hasSentRequestTo($this);

        if (!$request) {
            throw new DoesntHaveFriendRequestFromUser('You do not have a request to accept from this user.');
        }

        $myRequest                       = new Friendship;
        $myRequest->user_id              = $this->getId();
        $myRequest->friends_with_user_id = $otherUser->id;
        $myRequest->status               = Status::ACCEPTED;
        $myRequest->save();

        $request->status = Status::ACCEPTED;
        $request->save();

        return [$myRequest, $request];
    }

    /**
     * Decline the friend request from the other user
     *
     * @param Model $otherUser
     */
    public function declineFriendRequest(Model $otherUser)
    {
        if ($request = $otherUser->hasSentRequestTo($this)) {
            $request->status = Status::DENIED;
            $request->save();
        }
    }

    /**
     * Checks if the other user has blocked us
     * Checks if we're sending a request to ourself
     * Checks if we've already sent any kind of request, whether it is accepted, denied, blocked etc
     *
     * IMPORTANT: We need to prevent any duplicates from being created, for this code to be efficient
     *
     * @param Model $otherUser
     *
     * @return bool
     */
    public function canBeFriends(Model $otherUser)
    {
        //The other user has blocked us from sending requests to them
        if ($this->hasBeenBlockedBy($otherUser)) {
            return false;
        }

        //We cant send requests to ourselves obviously
        if ($this->getId() == $otherUser->id) {
            return false;
        }

        //If we already have a pending friend request with this user
        if ($this->hasSentRequestTo($otherUser)) {
            return false;
        }

        //If the other user has declined our friend request already
        if (!$otherUser->hasDeclinedRequestTo($this)) {
            return false;
        }

        //We have to accept the friend request, not send another
        /*if (!$otherUser->hasSentRequestTo($this)) {
            return false;
        }*/

        return true;
    }


    /**
     * Check if we have sent a friend request or are already friends with the other user
     *
     * @param Model $otherUser
     *
     * @return mixed
     */
    public function hasSentRequestTo(Model $otherUser)
    {
        return $this->friends()
            ->other($otherUser->id)
            ->where('status', Status::PENDING)
            ->first();
    }

    public function hasDeclinedRequestFrom(Model $otherUser)
    {
        return $otherUser->friends()
            ->other($this->getId())
            ->where('status', Status::DENIED)
            ->first();
    }

    /**
     * Check if the other user declined our request, preventing us from sending another
     *
     * @param Model $otherUser
     *
     * @return mixed
     */
    public function hasDeclinedRequestTo(Model $otherUser)
    {
        return $this->friends()
            ->other($otherUser->getId())
            ->where('status', Status::DENIED)
            ->first();
    }

    /**
     * ALL BLOCKING BASED LOGIC
     */

    /**
     * Check if the other user has blocked us from sending friend requests to them
     *
     * @param Model $model
     *
     * @return Friendship
     */
    public function hasBeenBlockedBy(Model $model)
    {
        return $this->friends()
            ->other($model->id)
            ->me($this->getId())
            ->where('status', Status::BLOCKED)
            ->first();
    }

    /**
     * Check if the current user has blocked requests from the other user
     *
     * @param Model $model
     *
     * @return Friendship
     */
    public function hasBlocked(Model $model)
    {
        return $this->friends()
            ->me($model->id)
            ->other($this->getId())
            ->where('status', Status::BLOCKED)
            ->first();
    }

    /**
     * Lets the current user unblock the other use from sending requests
     *
     * @param Model $model
     */
    public function unblock(Model $model)
    {
        $this->friends()
            ->me($this->getId())
            ->other($model->id)
            ->where('friends_with_user_id')
            ->status(Status::BLOCKED)
            ->delete();
    }

}