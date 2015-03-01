<?php

abstract class DisputeState {
}

class DisputeInitialised extends DisputeState {

    const LAW_FIRM_B_SET         = false;
    const LAW_FIRM_A_SUMMARISED  = true;
    const LAW_FIRM_B_SUMMARISED  = false;
    const AGENT_B_SET            = false;
    const AGENTS_CAN_COMMUNICATE = false;

}

class DisputeOpened extends DisputeState {

    const LAW_FIRM_B_SET         = true;
    const LAW_FIRM_A_SUMMARISED  = true;
    const LAW_FIRM_B_SUMMARISED  = false;
    const AGENT_B_SET            = false;
    const AGENTS_CAN_COMMUNICATE = false;

}

class DisputeLifespanNegotiating extends DisputeState {

    const LAW_FIRM_B_SET         = true;
    const LAW_FIRM_A_SUMMARISED  = true;
    const LAW_FIRM_B_SUMMARISED  = true;
    const AGENT_B_SET            = true;
    const AGENTS_CAN_COMMUNICATE = false;

}

class DisputeInProgress extends DisputeState {

    const LAW_FIRM_B_SET         = true;
    const LAW_FIRM_A_SUMMARISED  = true;
    const LAW_FIRM_B_SUMMARISED  = true;
    const AGENT_B_SET            = true;
    const AGENTS_CAN_COMMUNICATE = true;

}

class DisputeInMediation extends DisputeState {

    const LAW_FIRM_B_SET         = true;
    const LAW_FIRM_A_SUMMARISED  = true;
    const LAW_FIRM_B_SUMMARISED  = true;
    const AGENT_B_SET            = true;
    const AGENTS_CAN_COMMUNICATE = false;

}