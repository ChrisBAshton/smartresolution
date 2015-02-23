<?php

/**
 * This is an example module declaration file. It is intended to be used as a template that other developers can base their modules on, allowing us to plug in more modules of business logic into the system.
 *
 * @author <Chris Ashton>
 */

/**
 * First, we declare our module to the system.
 * The first argument is an array which describes the metadata concerning our module.
 * The second argument is our module definition
 */
declare_module(array(
    'key'         => 'simple_test',
    'title'       => 'Simple Test',
    'description' => 'A simple test module that shows what a module can do!'
), function () {

    /**
     * Our module can hook into events, using the following pattern:
     *
     *      On [EVENT], [FUNCTION TO CALL], [PRIORITY]
     *
     * The function definitions exist outside of this function, and can exist in other files.
     * Each function should ideally be namespaced (by using classes) but can also be defined globally.
     * Naming conventions, indentation and so on are up to you. Your module really is self-contained.
     *
     * Priority is optional and defaults to 'medium'. Priority can be defined by string:
     *     'low', 'medium', 'high', 'critical'
     *
     * It can also be defined by number, from 1 to 100, where 1 is low and 100 is high.
     */
    
    on('dispute_creation', 'sayHello');

    on('dispute_dashboard', 'SimpleTest->offer_ai_solution', 'high');

    on('mediation_creation', 'SimpleTest->apologise', 15);
});

function sayHello() {
    return "You've created a Dispute! Consider using the SimpleTest AI Solution facility to automatically resolve your Dispute.";
}

class SimpleTest {

    /**
     * Some events, such as 'dispute_dashboard', provide objects necessary for your function.
     */
    public function offer_ai_solution($dashboardActions) {

        $aiSolutionAction = array(
            'title' => 'SimpleTest AI Solution'
        );

        $indexOfCommunicationAction = $this->getPositionOfActionWhoseTitleIs('Communication');

        // insert $aiSolutionAction after 'Communication'
        array_splice($dashboardActions, ($indexOfCommunicationAction + 1), 0, $aiSolutionAction);

        return $dashboardActions;
    }

    private function getPositionOfActionWhoseTitleIs($title, $dashboardActions) {
        $i = 0;
        foreach ($dashboardActions as $action) {
            if ($action['title'] === $title) {
                return $i;
            }
            $i++;
        }
        return false;
    }

    public function apologise() {
        return "We're sorry that the SimpleTest could not automatically resolve your Dispute.";
    }

}