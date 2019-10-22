<?php


namespace App\Models\Traits;


use App\Exceptions\Friends\DoesntHaveFriendRequestFromUser;
use App\Exceptions\FriendsDoesntHaveFriendRequestFromUser;
use App\Friendship;
use App\User;
use App\UserConnections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
/**
 * Class Status.
 */
class ConnectionStatus
{
    const PENDING  = 0;
    const ACCEPTED = 1;
    const DENIED   = 2;
    const BLOCKED  = 3;
}

class ConnectionViewStatus
{
    const NOT_VIEWED  = 0;
    const VIEWED = 1;
}

trait Connectionable
{

    public function connections()
    {
        return $this->hasMany(UserConnections::class);
    }

    public function requested()
    {
        return $this->hasOne(UserConnections::class, 'user_id')->with('connection');
    }

    public function connected()
    {
        return $this->hasMany(User::class, 'id','connections_with_user_id');
    }

    public function unfriend($otherUser)
    {
        $otherUser->connections()->status(ConnectionStatus::ACCEPTED)
            ->other($this->getId())
            ->delete();

        $this->connections()->status(ConnectionStatus::ACCEPTED)
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
    public function isConnectionsWith($otherUser)
    {
        return $this->connections()->other($otherUser->id)->status(Status::ACCEPTED)->first();
    }


    /**
     * Allows us to check if we are friends with another user
     *
     * @param $otherUser
     *
     * @return mixed
     */
    public function isConnectionsRequestWith($otherUser)
    {
        return $this->connections()->Other($otherUser->getId())->status(Status::PENDING)->first();
    }

    public function getAcceptedConnectionIds()
    {
        return $this->connections()
            ->where('status', Status::ACCEPTED)
            ->get(['connections_with_user_id'])
            ->pluck('connections_with_user_id')
            ->toArray();
    }

    public function getBlockedConnectionIds()
    {
        return $this->connections()
            ->where('status', Status::BLOCKED)
            ->get(['connections_with_user_id'])
            ->pluck('connections_with_user_id')
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
    public function getMyConnections($status, $order = 'desc')
    {
        $query = $this
            ->whereHas('connections', function ($builder) use ($status) {
                return $builder
                    ->where('connections_with_user_id', $this->getId())
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
     * Gets sent requests with the status required
     *
     * @param        $status
     *
     * @param string $order
     *
     * @return Builder
     */
    public function getSentConnectionRequests($order = 'desc')
    {
        return $this
            ->whereHas('connections', function ($builder) {
                return $builder
                    ->where('user_id', $this->getId())
                    ->where('status', Status::PENDING);
            })
            ->orderBy('username', $order);
    }


    /**
     * Gets a list of users who have requested to be friends with me
     *
     * @param string $order
     *
     * @return mixed
     */
    public function getMyConnectionRequests($order = 'desc')
    {
        return $this
            ->whereHas('connections', function ($builder) {
                return $builder
                    ->where('connections_with_user_id', $this->getId())
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
    public function sendConnectionRequestTo(Model $otherUser, $custom = null)
    {
        if ($request = $this->hasDeclinedRequestFrom($otherUser)) {
            $request->delete();
        }

        if (!$this->canBeConnections($otherUser)) {
            return false;
        }

        //If we have a friend request from the other user and we're sending one to them
        //At this point, this should make us friends, we both have mutual interest in becoming friends.
        /*if ($otherUser->hasSentRequestTo($this)) {
            list($myRequest, $otherRequest) = $this->acceptFriendRequest($otherUser);

            return $myRequest;
        }*/

        $request                       = new UserConnections();
        $request->connections_with_user_id = $otherUser->id;
        $request->status               = Status::PENDING;
        $request->user_id              = $this->getId();
        if($custom->has('message')){
            $request->message          = $custom->message;
        }
        $request->save();

        return $request;
    }

    /**
     *
     * @param Model $otherUser
     */
    public function removeConnectionOrRequest(Model $otherUser)
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
    public function acceptConnectionRequest(Model $otherUser)
    {
        $request = $otherUser->hasSentRequestTo($this);

        if (!$request) {
            throw new DoesntHaveFriendRequestFromUser('You do not have a request to accept from this user.');
        }

        $myRequest                       = new UserConnections();
        $myRequest->user_id              = $this->getId();
        $myRequest->connections_with_user_id = $otherUser->id;
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
    public function canBeConnections(Model $otherUser)
    {
        //The other user has blocked us from sending requests to them
        if ($this->hasBeenBlockedBy($otherUser)) {
            return false;
        }

//        //Reached Limit of connections
//        if  ($this->isReachedLimit()){
//            return false;
//        }
        //We cant send requests to ourselves obviously
        if ($this->getId() == $otherUser->id) {
            return false;
        }

        //If we already have a pending friend request with this user
        if ($this->hasSentRequestTo($otherUser)) {
            return false;
        }

        //If the other user has declined our friend request already
        if ($otherUser->hasDeclinedRequestTo($this)) {
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
        return $this->connections()
            ->other($otherUser->id)
            ->where('status', Status::PENDING)
            ->first();
    }

    public function hasDeclinedRequestFrom(Model $otherUser)
    {
        return $otherUser->connections()
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
        return $this->connections()
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
        return $this->connections()
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
        return $this->connections()
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
        $this->connections()
            ->me($this->getId())
            ->other($model->id)
            ->where('friends_with_user_id')
            ->status(Status::BLOCKED)
            ->delete();
    }


    /**
     * Check if the user Reached Limit to Connect
     *
     */
    public function isReachedLimit()
    {
        $user_connections_count = (new UserConnections())->getLatestConnectionRequestsCount($this);
        //Check Like Limit
        if ($this->isPremiumUser()) {
            if ($user_connections_count >= 50) {
                return false;
            }
        } else {
            if ($this->isCoupleAccount()) {
                if ($user_connections_count >= 10) {
                    return false;
                }
            }
            if ($this->gender == \Config::get('constants.GENDER.MAN')) {
                if ($user_connections_count >= 5) {
                    return false;
                }
            } elseif ($this->gender == \Config::get('constants.GENDER.WOMAN')) {
                if ($user_connections_count >= 10) {
                    return false;
                }
            }
        }

        return true;
    }


}