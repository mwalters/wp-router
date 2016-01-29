<?php
/*
Plugin Name: Virtual Page
Plugin URI: https://www.pragmatticode.com/
Description: Helper plugin for creating virtual pages
Version: 1.0
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

if ( ! class_exists( 'PragRouter' ) ) {
    class PragRouter {

        private $url;
        private $title;
        private $content;

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

        public function makePage($posts) {
            global $wp, $wp_query;

            if (count($posts) == 0 && (strcasecmp($wp->request, $this->getUrl()) == 0 || $wp->query_vars['page_id'] == $this->getUrl())) {
                $post = new stdClass;
                $post->ID                    = 0;
                $post->post_author           = 0;
                $post->post_date             = current_time('mysql');
                $post->post_date_gmt         = current_time('mysql', 1);
                $post->post_content          = $this->content;
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

        public function addRoute($args) {
            $url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            if ($url == $args['url']) {
                $this->setUrl($args['url']);
                $this->setTitle($args['title']);
                $this->setContent($args['content']);
                add_filter('the_posts', array(&$this, 'makePage'));
            }
        }
    }
}

// Create object if needed
if ( ! @$PragRouter && function_exists('add_action')) { $PragRouter = new PragRouter(); }

/**
 * Example usage
 *
 * $args = array(
 *     'url' => 'prag_testing',
 *     'title' => 'hihi',
 *     'content' => 'howdy what is up'
 * );
 * $PragRouter->addRoute($args);
 */
