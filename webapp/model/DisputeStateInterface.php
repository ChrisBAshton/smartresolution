<?php

interface DisputeStateInterface {
    public function __construct($dispute, $account);
    public function getStateDescription();
    public function canOpenDispute();
    public function canAssignDisputeToAgent();
    public function canWriteSummary();
    public function canNegotiateLifespan();
    public function canSendMessage();
    public function canUploadDocuments();
    public function canEditSummary();
    public function canProposeMediation();
    public function canCloseDispute();
}
