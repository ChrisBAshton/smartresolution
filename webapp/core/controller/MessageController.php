<?php

/**
 * Links message-related HTTP requests to their actions.
 */
class MessageController {

    /**
     * View the agent:agent/round-table communication messages of a dispute.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function view ($f3, $params) {
        $this->setUp($f3, $params);
        $f3->set('messages', $this->dispute->getMessages());
        $f3->set('content', 'messages.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Create a new message for round-table communication.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function newMessage ($f3, $params) {
        $this->setUp($f3, $params);
        $message = $f3->get('POST.message');

        if ($message) {
            DBCreate::instance()->message(array(
                'dispute_id' => $this->dispute->getDisputeId(),
                'author_id'  => $this->account->getLoginId(),
                'message'    => $message
            ));
            DBCreate::instance()->notification(array(
                'recipient_id' => $this->dispute->getOpposingPartyId($this->account->getLoginId()),
                'message'      => $this->account->getName() . ' has sent you a message.',
                'url'          => $this->dispute->getUrl() . '/chat'
            ));
        }
        header('Location: ' . $this->dispute->getUrl() . '/chat');
    }

    /**
     * Instantiates account and dispute objects, checks if logged in account has permission to view dispute and send message.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    private function setUp($f3, $params) {
        $this->account = mustBeLoggedInAsAn('Individual');
        $this->dispute = setDisputeFromParams($f3, $params);

        if (!$this->dispute->canBeViewedBy($this->account->getLoginId())) {
            errorPage('You do not have permission to view this Dispute!');
        }
        else if (!$this->dispute->getState($this->account)->canSendMessage()) {
            if ($this->account instanceof Mediator &&
                $this->dispute->getMediationState()->inMediation()){

                errorPage('Round-Table Communication is currently disabled. You can enable this feature from the Mediation screen.');
            }
            else {
                errorPage('You cannot communicate with the other party at this time. This may be because the dispute is in mediation, has finished, or has not started yet.');
            }
        }
    }
}