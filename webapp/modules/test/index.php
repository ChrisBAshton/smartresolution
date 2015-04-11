<?php

declare_module(array(
    'key'         => 'test',
    'title'       => 'Test Module',
    'description' => 'This module is used only in testing the SmartResolution module functionality and should not be included in the production environment.'
), function () {

    declare_table('my_test_table', array(
        'a_text_field' => 'TEXT NOT NULL',
        'an_int_field' => 'INTEGER DEFAULT 0'
    ));

    // test global function
    route('/test', 'show_test_screen');
    // test class function
    top_level_route('/module-test', 'TestModule->topLevelRoute');
    // tests anonymous function
    on('dispute_dashboard', function () {
        dashboard_add_item(array(
            'title' => 'Test Dashboard Item',
            'image' => '/core/view/images/dispute.png',
            'href'  => get_dispute_url() . '/test'
        ), true);
    });
    route('/test-database', function () {
        createRow('my_test_table', array(
            'a_text_field' => 'test',
            'an_int_field' => 1337
        ));
        header('Location: ' . get_dispute_url() . '/test');
    });
});

function show_test_screen() {

    $databaseEntries = get_multiple('my_test_table.*');

    render(
        get_module_url() . '/hello.html',
        array(
            'message'    => 'Hello world!',
            'disputeUrl' => get_dispute_url(),
            'entryCount' => count($databaseEntries)
        )
    );
}

class TestModule {
    public function topLevelRoute() {
        render_markdown(get_module_url() . '/about.md');
    }
}