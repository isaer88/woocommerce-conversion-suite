<?php

define( 'ABSPATH', __DIR__ . '/' );
define( 'DAY_IN_SECONDS', 86400 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'VELTRIXA_OCO_DB_VERSION', '1.0.0' );

$GLOBALS['test_options'] = array(
    'wcs_oco_table_version' => '1.0.0',
);
$GLOBALS['orders_created'] = 0;
$GLOBALS['test_logs'] = array();

function add_action( ...$args ) {}
function current_time( $type, $gmt = false ) {
    return gmdate( 'Y-m-d H:i:s' );
}
function wp_unslash( $value ) { return $value; }
function sanitize_text_field( $value ) { return trim( $value ); }
function absint( $value ) { return abs( (int) $value ); }

if ( ! function_exists( 'mb_strlen' ) ) {
    function mb_strlen( $value, $encoding = null ) {
        return strlen( $value );
    }
}
function check_ajax_referer( ...$args ) { return true; }
function get_option( $key, $default = false ) {
    return $GLOBALS['test_options'][$key] ?? $default;
}
function add_option( $key, $value, ...$args ) {
    if ( array_key_exists( $key, $GLOBALS['test_options'] ) ) {
        return false;
    }
    $GLOBALS['test_options'][$key] = $value;
    return true;
}
function wp_send_json_error( $data, $status = 400 ) {
    throw new Test_Ajax_Response( $status, $data );
}
function wc_create_order( ...$args ) {
    $GLOBALS['orders_created']++;
    throw new RuntimeException( 'Order creation must not occur' );
}
function wc_get_product( $id ) {
    return new Fake_Product();
}
function wc_tax_enabled() { return false; }

class Test_Ajax_Response extends RuntimeException {
    public $data;
    public function __construct( $status, $data ) {
        parent::__construct( 'AJAX response', $status );
        $this->data = $data;
    }
}

class Fake_Logger {
    public function error( $message, $context = array() ) {
        $GLOBALS['test_logs'][] = array(
            'message' => $message,
            'context' => $context,
        );
    }
}

function wc_get_logger() {
    return new Fake_Logger();
}

class Fake_Product {
    public function is_type( $type ) { return $type === 'simple'; }
    public function get_status() { return 'publish'; }
    public function is_visible() { return true; }
    public function is_purchasable() { return true; }
    public function is_in_stock() { return true; }
    public function backorders_allowed() { return false; }
    public function get_price() { return '10.00'; }
    public function is_sold_individually() { return false; }
    public function managing_stock() { return false; }
}

class Fake_WPDB {
    public $prefix = 'wp_';
    public $last_error = 'Simulated SQL insert failure';
    public function insert( ...$args ) { return false; }
    public function prepare( $query, ...$args ) { return $query; }
    public function get_var( $query ) { return null; }
}

class VELTRIXA_One_Click_Order_Settings {
    public static function get_settings() {
        return array( 'enabled' => 'yes' );
    }
}

$wpdb = new Fake_WPDB();

require_once dirname( __DIR__ ) .
    '/includes/class-wcs-oco-idempotency.php';

require_once dirname( __DIR__ ) .
    '/modules/one-click-order/class-wcs-one-click-order-ajax.php';

$_POST = array(
    'product_id' => '1',
    'quantity' => '1',
    'customer_name' => 'Test Customer',
    'customer_phone' => '+380501234567',
    'request_id' => '550e8400-e29b-41d4-a716-446655440000',
);

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$handler = new VELTRIXA_One_Click_Order_Ajax();

try {
    $handler->create_order();
    throw new RuntimeException( 'Expected AJAX error was not returned' );
} catch ( Test_Ajax_Response $response ) {
    if ( 503 !== $response->getCode() ) {
        throw new RuntimeException(
            'Expected HTTP 503, got ' . $response->getCode()
        );
    }
}

$request_key = 'wcs_oco_request_' .
    hash( 'sha256', strtolower( $_POST['request_id'] ) );

if ( get_option( $request_key ) !== 'processing' ) {
    throw new RuntimeException( 'Legacy reservation was not preserved' );
}

if ( $GLOBALS['orders_created'] !== 0 ) {
    throw new RuntimeException( 'Unexpected order creation' );
}

echo "PASS: SQL failure returns HTTP 503\n";
echo "PASS: Legacy reservation preserved\n";
echo "PASS: No WooCommerce order created\n";

// Retry with the same request identifier.
try {
    $handler->create_order();
    throw new RuntimeException( 'Duplicate request was accepted' );
} catch ( Test_Ajax_Response $response ) {
    if ( 409 !== $response->getCode() ) {
        throw new RuntimeException(
            'Expected HTTP 409 on retry, got ' . $response->getCode()
        );
    }
}

if ( get_option( $request_key ) !== 'processing' ) {
    throw new RuntimeException( 'Reservation changed after retry' );
}

if ( $GLOBALS['orders_created'] !== 0 ) {
    throw new RuntimeException( 'Duplicate order creation attempted' );
}

echo "PASS: Duplicate request returns HTTP 409\n";
echo "PASS: Reservation preserved after retry\n";
echo "PASS: No order created after retry\n";

$found_log = false;

foreach ( $GLOBALS['test_logs'] as $log ) {
    if (
        str_contains(
            $log['message'],
            'Reservation failed or database state is uncertain'
        )
        && ( $log['context']['source'] ?? '' ) === 'wcs-one-click-order'
    ) {
        $found_log = true;
        break;
    }
}

if ( ! $found_log ) {
    throw new RuntimeException( 'Expected SQL failure log not found' );
}

echo "PASS: SQL reservation failure logged\n";