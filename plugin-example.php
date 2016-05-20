<?php
function mytest() {
    $returnVal = array(
        'title' => 'mytitle',
        'content' => 'mycontent'
    );
    return $returnVal;
}

add_action('mswwprouter_add_route', function() {
    MswWpRouter::addRoute('test', 'mytest');
}, 999);


if ( ! class_exists( 'MswWpRouterTester' ) ) {
    class MswWpRouterTester {
        function __construct() {
            MswWpRouter::addRoute('testing', array(&$this, 'testing'));
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
    add_action('mswwprouter_add_route', function() {
        $MswWpRouterTester = new MswWpRouterTester();
    }, 999);
}
