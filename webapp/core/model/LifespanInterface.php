<?php

/**
 * Interface describing a dispute lifespan and all of the functions that any dispute lifespan classes must implement.
 */
interface LifespanInterface {

    /**
     * Lifespan constructor
     * @param array $lifespan Array of lifespan data.
     */
    public function __construct($lifespan);

    /**
     * Returns the human-readable status of the lifespan, such as 'Dispute ended.'
     * @return string Human-readable status.
     */
    public function status();

    /**
     * Returns true if the lifespan has been agreed by both agents, has started and has not finished, and is therefore 'current'.
     * @return boolean True if current, false if not.
     */
    public function isCurrent();

    /**
     * Denotes whether a lifespan has been offered, but not agreed or declined.
     * @return boolean True if offered, false if not.
     */
    public function offered();

    /**
     * Denotes whether a lifespan has been accepted, but not offered or declined.
     * @return boolean True if accepted, false if not.
     */
    public function accepted();

    /**
     * Denotes whether a lifespan has been declined, but not agreed or offered.
     * @return boolean True if declined, false if not.
     */
    public function declined();

    /**
     * Denotes whether or not the end time of the lifespan is in the past. This function does not care whether or not the lifespan has been accepted or offered; it only cares that the lifespan still has relevancy.
     * @return boolean True if lifespan end time is in the past, otherwise false.
     */
    public function isEnded();

    /**
     * Change the end time of the lifespan to be the current time, and therefore end the lifespan. Used when dispute is closed (successfully or otherwise).
     */
    public function endLifespan();
}
