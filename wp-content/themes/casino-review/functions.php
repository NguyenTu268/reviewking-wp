<?php
// Add custom Theme Functions here
// Xóa tab "Additional Information" và "Reviews"
add_filter( 'woocommerce_product_tabs', 'remove_unwanted_tabs', 98 );

function remove_unwanted_tabs( $tabs ) {
    unset( $tabs['additional_information'] ); // Xóa tab Thông tin bổ sung
    unset( $tabs['reviews'] ); // Xóa tab Đánh giá
    return $tabs;
}

function custom_product_reviews() {
    global $product;
    if ( ! $product ) return;
    
    comments_template();
}
add_shortcode('custom_reviews', 'custom_product_reviews');

// Function lấy ngày đăng sản phẩm và tạo shortcode
function get_product_publish_date() {
    if (is_singular('product')) { // Chỉ hiển thị trên trang sản phẩm
        $date_icon = '<svg width="16px" height="16px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 11H21M16 15H8M12 7V3M8 7V3M16 7V3M7 3H17C18.6569 3 20 4.34315 20 6V20C20 21.6569 18.6569 23 17 23H7C5.34315 23 4 21.6569 4 20V6C4 4.34315 5.34315 3 7 3Z" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';

        $formatted_date = get_the_date('d/m/Y'); // Định dạng ngày 28/01/2025

        return '<p class="product-date" style="display: flex; align-items: center; gap: 5px;">' . $date_icon . ' ' . $formatted_date . '</p>';
    }
    return ''; // Không hiển thị nếu không phải trang sản phẩm
}

// Đăng ký shortcode [product_date] để sử dụng
add_shortcode('product_date', 'get_product_publish_date');