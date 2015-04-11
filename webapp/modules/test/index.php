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
});

function show_test_screen() {
    render(
        get_module_url() . '/hello.html',
        array(
            'message' => 'Hello world!'
        )
    );
}

class TestModule {
    public function topLevelRoute() {
        render_markdown(get_module_url() . '/about.md');
    }
}