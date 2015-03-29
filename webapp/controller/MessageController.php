<?php

class MessageController {

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

    public function view ($f3, $params) {
        $this->setUp($f3, $params);
        $messages = new Messages($this->dispute->getDisputeId());
        $f3->set('messages', $messages->getMessages());
        $f3->set('content', 'messages.html');
        echo View::instance()->render('layout.html');
    }

    public function newMessage ($f3, $params) {
        $this->setUp($f3, $params);
        $message = $f3->get('POST.message');

        if ($message) {
            DBL::createMessage(array(
                'dispute_id' => $this->dispute->getDisputeId(),
                'author_id'  => $this->account->getLoginId(),
                'message'    => $message
            ));
        }
        header('Location: ' . $this->dispute->getUrl() . '/chat');
    }
}
