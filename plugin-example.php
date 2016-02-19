<?php
function mytest() {
    $returnVal = array(
        'title' => 'mytitle',
        'content' => 'mycontent'
    );
    return $returnVal;
}

$PragRouter->addRoute('test', 'mytest');


if ( ! class_exists( 'PragRouterTester' ) ) {
    class PragRouterTester {
        function __construct() {
            global $PragRouter;
            $PragRouter->addRoute('testing', array(&$this, 'testing'));
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
if ( ! @$PragRouterTester && function_exists('add_action')) { $PragRouterTester = new PragRouterTester(); }
