<?php

declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/wordpress/');
    }

    if (!function_exists('add_filter')) {
        function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
        {
            $GLOBALS['wwp_test_filters'][$hook][] = compact('callback', 'priority', 'accepted_args');

            return true;
        }
    }

    if (!function_exists('remove_filter')) {
        function remove_filter($hook, $callback, $priority = 10)
        {
            $GLOBALS['wwp_test_removed_filters'][] = compact('hook', 'callback', 'priority');

            return true;
        }
    }

    if (!function_exists('wp_handle_upload')) {
        function wp_handle_upload($file, $overrides = false, $time = null)
        {
            return $GLOBALS['wwp_test_wp_handle_upload_result'] ?? [
                'file' => '/uploads/test.jpg',
                'url' => 'https://example.test/uploads/test.jpg',
                'type' => 'image/jpeg',
            ];
        }
    }

    if (!class_exists('wpdb', false)) {
        class wpdb
        {
            public string $posts = 'wp_posts';

            public string $postmeta = 'wp_postmeta';

            /** @var array<int, string> */
            public array $guidCol = [];

            /** @var array<int, string> */
            public array $metaCol = [];

            /** @var list<string> */
            public array $preparedStatements = [];

            public function prepare($query, ...$args)
            {
                $this->preparedStatements[] = (string) $query;

                return vsprintf(str_replace(['%s', '%d'], ['\'%s\'', '%d'], (string) $query), $args);
            }

            public function get_col($query)
            {
                if (str_contains((string) $query, 'wp_posts') && str_contains((string) $query, 'guid')) {
                    return $this->guidCol;
                }

                if (str_contains((string) $query, 'wp_postmeta')) {
                    return $this->metaCol;
                }

                return [];
            }
        }
    }
}
