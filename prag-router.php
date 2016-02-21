<?php
/*
Plugin Name: WordPress Router
Plugin URI: https://www.pragmatticode.com/
Description: Helper plugin for creating virtual pages
Version: 1.0.0
Author: Pragmattic
Author URI: https://www.pragmatticode.com/
License: GPLv2
Text Domain: prag-router
*/

/**
 * TODO
 *
 * Make this more like a routing mechanism.  Below gets the needed job done for now though.
 */

if ( ! class_exists('PragRouter')) {
    class PragRouter {

        private $url;
        private $title;
        private $content;
        private $routes;

        public function __construct() {
            $this->setTitle('');
            $this->setContent('');
            add_filter('init', array(&$this, 'processUrl'));
        }

        /**
         * Check the requested URL to see if the requested URL is one we should route
         */
        public function processUrl() {
            $url = $this->normalizeRoute($_SERVER['REQUEST_URI']);

            $urlHash = md5($url);

            if (isset($this->routes[$urlHash])) {
                if (strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($this->routes[$urlHash]['method'])) {
                    $this->executeRoute($url, $urlHash);
                }
            }
        }

        /**
         * Execute the requested route
         */
        private function executeRoute($route = '', $routeHash = '') {
            if ($route === '') {
                return false;
            }
            if ($routeHash === '') {
                $routeHash = md5($route);
            }

            $pageContent = call_user_func_array($this->routes[$routeHash]['callback'], array('route' => $route));

            if ( ! empty($pageContent)) {
                if (is_bool($pageContent) && $pageContent === false) {
                    return;
                }

                if (is_array($pageContent)) {
                    if (isset($pageContent['title'])) {
                        $this->setTitle($pageContent['title']);
                    }
                    if (isset($pageContent['content'])) {
                        $this->setContent($pageContent['content']);
                    }
                }

                if (is_string($pageContent)) {
                    $this->setContent($pageContent);
                }

                $this->setUrl($route);

                add_filter('the_posts', array(&$this, 'makePage'));
            }
        }

        /**
         * Add a route to process if requested
         */
        public function addRoute($route = '', $callback = null, $method = 'GET') {
            if ($route === '' || $callback === null) {
                return false;
            }

            $route = $this->normalizeRoute($route);

            $routeHash = md5($route);

            if (isset($this->routes[$routeHash])) {
                return false;
            }

            $this->routes[$routeHash] = array(
                'callback' => $callback,
                'method' => $method,
                'route' => $route
            );

            return true;
        }

        /**
         * Normalize a given route so it is compared apples to apples
         */
        private function normalizeRoute($route = '') {
            if ($route === '') {
                return $route;
            }

            if (substr($route, 0, 1) === '/') {
                $route = substr($route, 1, strlen($route));
            }

            $route = untrailingslashit(trim($route));

            return $route;
        }

        public function setUrl($url = '') {
            $this->url = $url;
        }
        public function getUrl() {
            return $this->url;
        }

        public function setTitle($title = '') {
            $this->title = $title;
        }
        public function getTitle() {
            return $this->title;
        }

        public function setContent($content = '') {
            $this->content = $content;
        }
        public function getContent() {
            return $this->content;
        }

        /**
         * Add a faux page to WordPress to display our content
         */
        public function makePage($posts) {
            global $wp, $wp_query;

            if (count($posts) == 0 && (strcasecmp($wp->request, $this->getUrl()) == 0 || $wp->query_vars['page_id'] == $this->getUrl())) {
                $post = new stdClass;
                $post->ID                    = 0;
                $post->post_author           = 0;
                $post->post_date             = current_time('mysql');
                $post->post_date_gmt         = current_time('mysql', 1);
                $post->post_content          = $this->getContent();
                $post->post_title            = $this->getTitle();
                $post->post_excerpt          = '';
                $post->post_status           = 'publish';
                $post->comment_status        = 'closed';
                $post->ping_status           = 'closed';
                $post->post_password         = '';
                $post->post_name             = $this->getUrl();
                $post->to_ping               = '';
                $post->pinged                = '';
                $post->modified              = $post->post_date;
                $post->modified_gmt          = $post->post_date_gmt;
                $post->post_content_filtered = '';
                $post->post_parent           = 0;
                $post->guid                  = get_home_url('/' . $this->getUrl());
                $post->menu_order            = 0;
                $post->post_type             = 'page';
                $post->post_mime_type        = '';
                $post->comment_count         = 0;

                // set filter results
                $posts = array($post);

                // reset wp_query properties to simulate a found page
                unset($wp_query->query['error']);
                $wp_query->is_page             = TRUE;
                $wp_query->is_singular         = TRUE;
                $wp_query->is_home             = FALSE;
                $wp_query->is_archive          = FALSE;
                $wp_query->is_category         = FALSE;
                $wp_query->query_vars['error'] = '';
                $wp_query->is_404              = FALSE;
            }

            return ($posts);
        }
    }
}

// Create object if needed
if ( ! @$PragRouter && function_exists('add_action')) { $PragRouter = new PragRouter(); }
