<?php
function mytest() {
    $returnVal = array(
        'title' => 'mytitle',
        'content' => 'mycontent'
    );
    return $returnVal;
}

add_action('mr_register_route', function() {
    MRouter::addRoute('test', 'mytest');
});


if ( ! class_exists( 'MswWpRouterTester' ) ) {
    class MswWpRouterTester {
        function __construct() {
            MRouter::addRoute('testing', array(&$this, 'testing'));
        }

        public function testing() {
            $returnVal = array(
                'title' => 'mytitletesting',
                'content' => 'mycontenttesting'
            );
            return $returnVal;
        }
    }
}
if ( ! @$MswWpRouterTester && function_exists('add_action')) {
    add_action('mr_register_route', function() {
        $MswWpRouterTester = new MswWpRouterTester();
    });
}
