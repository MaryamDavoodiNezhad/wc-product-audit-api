<?php
/**
 * Plugin Name: Mashhad Daghigh Product Audit API
 * Plugin URI:  https://iranbtm.com
 * Description: Custom REST API Endpoint for auditing products missing featured images or prices.
 * Version:     1.0.0
 * Author:      Maryam Davoodi
 * Text Domain: md-product-audit-api
 */

if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم به فایل
}

class MD_Product_Audit_API {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('mashhad-daghigh/v1', '/audit-products', array(
            'methods'             => WP_REST_Server::READABLE, // همان GET
            'callback'            => array($this, 'audit_products_callback'),
            'permission_callback' => array($this, 'check_permission'),
        ));
    }

    public function check_permission() {
        // برای تست اولیه روی لوکال اگر لاگین نبودید می‌توانید temporary __return_true بگذارید
        return current_user_can('edit_products') || current_user_can('manage_options');
    }

    public function audit_products_callback($request) {
        $type  = $request->get_param('type') ? sanitize_text_field($request->get_param('type')) : 'all_issues';
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 50;

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
        );

        $products = get_posts($args);
        $report   = array();

        foreach ($products as $post) {
            $product_id = $post->ID;
            $product    = function_exists('wc_get_product') ? wc_get_product($product_id) : null;

            $has_image = has_post_thumbnail($product_id);
            $price     = $product ? $product->get_price() : get_post_meta($product_id, '_price', true);
            $has_price = ($price !== '' && $price !== false && floatval($price) > 0);

            $issues = array();
            if (!$has_image) {
                $issues[] = 'no_image';
            }
            if (!$has_price) {
                $issues[] = 'no_price';
            }

            $should_add = false;
            if ($type === 'no_image' && !$has_image) {
                $should_add = true;
            } elseif ($type === 'no_price' && !$has_price) {
                $should_add = true;
            } elseif ($type === 'all_issues' && (!$has_image || !$has_price)) {
                $should_add = true;
            }

            if ($should_add) {
                $report[] = array(
                    'id'        => $product_id,
                    'title'     => get_the_title($product_id),
                    'sku'       => $product ? $product->get_sku() : get_post_meta($product_id, '_sku', true),
                    'has_image' => $has_image,
                    'has_price' => $has_price,
                    'issues'    => $issues,
                    'edit_link' => admin_url('post.php?post=' . $product_id . '&action=edit'),
                    'view_link' => get_permalink($product_id),
                );
            }
        }

        return new WP_REST_Response(array(
            'status'             => 'success',
            'total_issues_found' => count($report),
            'filter_type'        => $type,
            'data'               => $report
        ), 200);
    }
}

// ساخت نمونه از کلاس پلاگین
new MD_Product_Audit_API();