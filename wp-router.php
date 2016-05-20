<?php
/*
Plugin Name: WordPress Router
Plugin URI: http://www.mattwalters.net/projects/wordpress/wp-router/
Description: Helper plugin for creating virtual pages
Version: 2.0.0
Author: Matt Walters
Author URI: http://www.mattwalters.net/
License: GPLv2
Text Domain: msw-wp-router
*/

/*
Copyright (C) 2016 Matthew Walters.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


if ( ! class_exists('MRouter')) {
    class MRouter {

        private static $url;            // URL for faux page
        private static $title   = '';   // Title for faux page
        private static $content = '';   // Content for faux page
        private static $routes;         // Array of routes being tracked

        public static function addWpHooks() {
            // Hook into WordPress
            add_filter('init', array('MRouter', 'processUrl'));
            add_action('plugins_loaded', array('MRouter', 'registerRoutes'));
        }

        public static function registerRoutes() {
            do_action('mr_register_route');
        }

        /**
         * Check the requested URL to see if the requested URL is one we should route
         */
        public static function processUrl() {
            // Ensure we have a normalized URL for comparison
            $url = self::normalizeRoute($_SERVER['REQUEST_URI']);

            // Check to see if URL is one that is being handled by the router
            $urlHash = md5($url);
            if (isset(self::$routes[$urlHash])) {
                if (strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper(self::$routes[$urlHash]['method'])) {
                    // This is a route that is being handled by the router, so execute it
                    self::executeRoute($url, $urlHash);
                }
            }
        }

        /**
         * Execute the requested route
         *
         * @param string $route The route being executed
         * @param string $routeHash Hash of the route being executed; optional
         */
        protected static function executeRoute($route = '', $routeHash = '') {
            if ($route === '') {
                // Return `false` if no route was provided
                return false;
            }

            if ($routeHash === '') {
                // If a hash was not previously calculated, then calculate it now
                $routeHash = md5($route);
            }

            // Call the specified PHP Callable and retrieve the returned value
            $pageContent = call_user_func_array(
                self::$routes[$routeHash]['callback'],
                array(
                    'route' => $route
                )
            );

            if ( ! empty($pageContent)) {
                if (is_bool($pageContent) && $pageContent === false) {
                    // If `false` was returned, return nothing and let WordPress 404
                    return;
                }

                if (is_array($pageContent)) {
                    if (isset($pageContent['title'])) {
                        // If an array key of `title` was returned, then set the page title to it
                        self::setTitle($pageContent['title']);
                    }
                    if (isset($pageContent['content'])) {
                        // If an array key of `content` was returned, then set the page content to it
                        self::setContent($pageContent['content']);
                    }
                }

                if (is_string($pageContent)) {
                    // If a string was returned, then set the page content to it
                    self::setContent($pageContent);
                }

                // Set the URL we are handling
                self::setUrl($route);

                // Hook into WordPress so we can display a faux page
                add_filter('the_posts', array('MRouter', 'makePage'));
            }
        }

        /**
         * Add a route to process if requested
         *
         * @param string $route Route to listen for
         * @param string $callback PHP Callable to be executed if this route is encountered
         * @param string $method The HTTP method to listen for on this route
         */
        public static function addRoute($route = '', $callback = null, $method = 'GET') {
            if ($route === '' || $callback === null) {
                // If no route or no callback was provided, then return false
                return false;
            }

            // Normalize the route to avoid collisions
            $route = self::normalizeRoute($route);

            // Hash the route to make comparisons and storage easier
            $routeHash = md5($route);

            if (isset(self::$routes[$routeHash])) {
                // This route is already claimed, so reject this request
                return false;
            }

            // Store the route so it can be listened for and executed when called
            self::$routes[$routeHash] = array(
                'callback' => $callback,
                'method' => $method,
                'route' => $route
            );

            return true;
        }

        /**
         * Normalize a given route so it is compared apples to apples
         *
         * @param string $route Route to be normalized
         * @return string Normalized route
         */
        protected static function normalizeRoute($route = '') {
            if ($route === '') {
                return $route;
            }

            if (substr($route, 0, 1) === '/') {
                // Easiest to just make sure there is no prepended slash
                $route = substr($route, 1, strlen($route));
            }

            // Remove trailing slash on the route
            $route = untrailingslashit(trim($route));

            return $route;
        }

        // Start getters and setters
        protected static function url() {
            return self::$url;
        }
        protected static function setUrl($url = '') {
            self::$url = $url;
        }

        protected static function title() {
            return self::$title;
        }
        protected static function setTitle($title = '') {
            self::$title = $title;
        }

        protected static function content() {
            return self::$content;
        }
        protected static function setContent($content = '') {
            self::$content = $content;
        }
        // End getters and setters

        /**
         * Add a faux page to WordPress to display our content.  "Butcher" the post object and
         * WP Query into thinking the requested page exists so that it will be displayed.
         */
        public static function makePage($posts) {
            global $wp, $wp_query;

            if (count($posts) == 0 && (strcasecmp($wp->request, self::url()) == 0 || $wp->query_vars['page_id'] == self::url())) {
                $post = new stdClass;
                $post->ID                    = -1;
                $post->post_author           = 0;
                $post->post_date             = current_time('mysql');
                $post->post_date_gmt         = current_time('mysql', 1);
                $post->post_content          = self::content();
                $post->post_title            = self::title();
                $post->post_excerpt          = '';
                $post->post_status           = 'publish';
                $post->comment_status        = 'closed';
                $post->ping_status           = 'closed';
                $post->post_password         = '';
                $post->post_name             = self::url();
                $post->to_ping               = '';
                $post->pinged                = '';
                $post->modified              = $post->post_date;
                $post->modified_gmt          = $post->post_date_gmt;
                $post->post_content_filtered = '';
                $post->post_parent           = 0;
                $post->guid                  = get_home_url('/' . self::url());
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

MRouter::addWpHooks();
