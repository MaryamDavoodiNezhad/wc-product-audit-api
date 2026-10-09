WooCommerce Product Audit API
A lightweight Custom REST API endpoint for WooCommerce that identifies published products missing featured images or valid prices.

🚀 Quick Start
Place the plugin folder in wp-content/plugins/wc-product-audit-api.

Activate via WordPress Admin > Plugins.

Call the API using Basic Auth (Application Password) or logged-in admin credentials.

📡 Endpoint
GET /wp-json/wc-audit/v1/products

Query Parameters
type (optional): all_issues (default), no_image, no_price

limit (optional): Number of products to inspect (default: 50)

📄 Example Response
{
    "status": "success",
    "total_issues_found": 1,
    "filter_type": "all_issues",
    "data": [
        {
            "id": 22,
            "title": "Hioki Earth Tester Model FT6031-50",
            "sku": "",
            "has_image": false,
            "has_price": false,
            "issues": ["no_image", "no_price"],
            "edit_link": "http://localhost/iranmdt/wp-admin/post.php?post=22&action=edit",
            "view_link": "http://localhost/iranmdt/product/hioki-earth-tester-model-ft6031-50/"
        }
    ]
}

🔒 Security
Access requires edit_products capabilities via permission_callback.