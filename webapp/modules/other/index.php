<?php
/**
 * This is the default Dispute type and should not be removed from the system. It adds nothing to the core functionality of the system and therefore should only be selected if no other Dispute type is appropriate.
 */
declare_module(array(
    'key'         => 'other',
    'title'       => 'Other',
    'description' => 'If no other Dispute types seem appropriate, select this one. It adds nothing to the default functionality of the platform.'
), function () {});