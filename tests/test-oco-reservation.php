<?php

define( 'ABSPATH', __DIR__ . '/' );
define( 'DAY_IN_SECONDS', 86400 );

function current_time( $type, $gmt = false ) {
    return gmdate( 'Y-m-d H:i:s' );
}

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
    // WordPress hook registration stub for isolated tests.
    return true;
}

class Fake_WPDB {
    public $prefix = 'wp_';
    public $insert_result = false;
    public $insert_calls = 0;
    public $last_data = array();

    public function insert( $table, $data, $format = null ) {
        $this->insert_calls++;
        $this->last_data = $data;
        return $this->insert_result;
    }
}

$wpdb = new Fake_WPDB();

require_once dirname( __DIR__ ) .
    '/includes/class-wcs-oco-idempotency.php';

function assert_test( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}

$request_id = '550e8400-e29b-41d4-a716-446655440000';

$wpdb->insert_result = false;

$result = VELTRIXA_OCO_Idempotency::reserve( $request_id );

assert_test(
    false === $result,
    'Failed SQL insert must reject reservation.'
);

assert_test(
    1 === $wpdb->insert_calls,
    'Expected exactly one insert attempt.'
);

assert_test(
    'processing' === $wpdb->last_data['status'],
    'Reservation must start as processing.'
);

assert_test(
    hash( 'sha256', strtolower( $request_id ) ) ===
    $wpdb->last_data['request_hash'],
    'Request hash mismatch.'
);

$wpdb->insert_result = 1;

$result = VELTRIXA_OCO_Idempotency::reserve( $request_id );

assert_test(
    true === $result,
    'Successful SQL insert must reserve request.'
);

assert_test(
    2 === $wpdb->insert_calls,
    'Expected exactly two insert attempts.'
);

echo "PASS: SQL insert failure returns false\n";
echo "PASS: SQL insert success returns true\n";
echo "PASS: Request hash and initial status verified\n";