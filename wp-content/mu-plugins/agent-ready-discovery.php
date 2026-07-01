<?php
/**
 * Plugin Name: Agent-Ready Discovery (Content Blog)
 * Description: Thêm metadata phát hiện agent cho blog nội dung: Link headers (RFC 8288),
 *              Content Signals trong robots.txt, Markdown for Agents, và API catalog (RFC 9727).
 *              Không bao gồm các mục cần backend thật (OAuth, thanh toán x402/MPP/UCP/ACP,
 *              MCP Server Card) vì site này không có API/dịch vụ trả phí — xem ghi chú cuối file.
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

/* ---------------------------------------------------------------------
 * 1. Link response header trỏ tới API catalog (RFC 8288)
 * ------------------------------------------------------------------- */
add_action('send_headers', function () {
    if (is_admin()) return;
    header('Link: <' . home_url('/.well-known/api-catalog') . '>; rel="api-catalog"', false);
});

/* ---------------------------------------------------------------------
 * 2. Content Signals trong robots.txt
 *    ai-train=no  -> không cho phép train model trên nội dung
 *    search=yes   -> cho phép index để tìm kiếm
 *    ai-input=yes -> cho phép agent đọc nội dung làm input trả lời câu hỏi
 *    Chỉnh 3 giá trị này theo đúng mong muốn của bạn trước khi dùng.
 * ------------------------------------------------------------------- */
add_filter('robots_txt', function ($output, $public) {
    if ((int) $public !== 1) return $output; // site không public thì không thêm
    $output .= "\n# Content Signals (https://contentsignals.org/)\n";
    $output .= "Content-Signal: ai-train=no, search=yes, ai-input=yes\n";
    return $output;
}, 10, 2);

/* ---------------------------------------------------------------------
 * 3. API catalog: /.well-known/api-catalog (RFC 9727)
 *    Trỏ vào REST API có sẵn của WordPress (wp-json).
 * ------------------------------------------------------------------- */
add_action('init', function () {
    $path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path !== '/.well-known/api-catalog') return;

    header('Content-Type: application/linkset+json');
    echo json_encode([
        'linkset' => [[
            'anchor' => home_url('/wp-json/'),
            'service-desc' => [[
                'href' => home_url('/wp-json/'),
                'title' => 'WordPress REST API root',
            ]],
            'service-doc' => [[
                'href' => 'https://developer.wordpress.org/rest-api/',
                'title' => 'WordPress REST API documentation',
            ]],
        ]],
    ], JSON_UNESCAPED_SLASHES);
    exit;
});

/* ---------------------------------------------------------------------
 * 4. Markdown for Agents
 *    Khi request bài viết có header Accept: text/markdown, trả về
 *    bản Markdown thay vì HTML. Trình duyệt thường không gửi header
 *    này nên hành vi mặc định cho người dùng không đổi.
 * ------------------------------------------------------------------- */
add_action('template_redirect', function () {
    if (!is_singular('post')) return;

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'text/markdown') === false) return;

    global $post;
    $title   = get_the_title($post);
    $content = apply_filters('the_content', $post->post_content);
    $body    = agent_ready_html_to_markdown($content);

    header('Content-Type: text/markdown; charset=utf-8');
    header('x-markdown-tokens: 1'); // báo hiệu đây là markdown chuyển đổi tự động
    echo "# {$title}\n\n{$body}\n";
    exit;
});

/**
 * Chuyển HTML cơ bản sang Markdown. Đủ dùng cho nội dung blog thông thường
 * (heading, bold, italic, link, list, đoạn văn). Không xử lý bảng/nhúng phức tạp.
 */
function agent_ready_html_to_markdown($html) {
    $html = preg_replace_callback('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', function ($m) {
        return "\n" . str_repeat('#', (int) $m[1]) . ' ' . trim(wp_strip_all_tags($m[2])) . "\n";
    }, $html);
    $html = preg_replace('/<(strong|b)[^>]*>(.*?)<\/\1>/is', '**$2**', $html);
    $html = preg_replace('/<(em|i)[^>]*>(.*?)<\/\1>/is', '*$2*', $html);
    $html = preg_replace('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is', '[$2]($1)', $html);
    $html = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "- $1\n", $html);
    $html = preg_replace('/<\/p>/i', "\n\n", $html);
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $html = wp_strip_all_tags($html);
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('/\n{3,}/', "\n\n", $html);
    return trim($html);
}

/* ---------------------------------------------------------------------
 * CÁC MỤC KHÔNG TRIỂN KHAI — và lý do:
 *
 * - OAuth/OIDC discovery, OAuth Protected Resource, auth.md:
 *     Chỉ có ý nghĩa khi site có API cần bảo vệ bằng token. Blog này
 *     không có tài nguyên trả phí/riêng tư cho agent, nên khai báo các
 *     endpoint này sẽ trỏ vào chỗ không tồn tại.
 *
 * - MCP Server Card (/.well-known/mcp/server-card.json):
 *     Chỉ áp dụng nếu bạn thực sự chạy một MCP server. Site này không có.
 *
 * - Agent Skills index, WebMCP:
 *     Dùng khi site có "hành động"/"công cụ" muốn cho agent gọi trực tiếp
 *     (ví dụ tìm kiếm, đặt hàng...). Có thể thêm sau nếu bạn muốn cho agent
 *     dùng chức năng tìm kiếm của blog.
 *
 * - x402 / MPP / UCP / ACP (thanh toán agent):
 *     Đều cần hạ tầng thanh toán thật (ví crypto, tài khoản merchant,
 *     facilitator). Vì blog miễn phí không bán gì, khai báo các endpoint
 *     này là không trung thực và có thể khiến agent thử trả tiền cho nội
 *     dung không thực sự được bán. Nên bỏ qua cho tới khi bạn có nhu cầu
 *     bán nội dung/API thật.
 *
 * - DNS for AI Discovery (DNS-AID):
 *     Cần thêm bản ghi SVCB/HTTPS + DNSSEC tại nhà cung cấp DNS của bạn
 *     (Cloudflare, GoDaddy...). Đây là thao tác ở DNS panel, không thể
 *     làm qua plugin WordPress. Ví dụ bản ghi cần thêm:
 *       _index._agents.yourdomain.com  SVCB  1 . alpn="h2" endpoint="https://yourdomain.com/.well-known/agents"
 *     Chỉ nên làm nếu bạn tự tin quản lý DNS, vì cấu hình sai có thể ảnh
 *     hưởng đến toàn bộ domain.
 * ------------------------------------------------------------------- */
