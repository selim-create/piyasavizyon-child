<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Child-owned cache for Piyasa Vizyon market payloads.
 *
 * During the migration we can read the historical BirFinans cache as a
 * compatibility fallback, but all new writes go to a Piyasa Vizyon-owned
 * directory.
 */
final class PV_Market_Cache {
    private $ttl;
    private $directory = 'piyasa-vizyon-market-cache';
    private $legacy_directory = 'birfinans-cache';

    public function __construct( $ttl_minutes = 5 ) {
        $ttl_minutes = max( 1, (int) $ttl_minutes );
        $this->ttl = $ttl_minutes * MINUTE_IN_SECONDS;
    }

    private function uploads_base_dir() {
        $uploads = wp_upload_dir( null, false );
        return ! empty( $uploads['basedir'] ) ? untrailingslashit( $uploads['basedir'] ) : '';
    }

    private function read_file( $path ) {
        if ( ! $path || ! is_readable( $path ) ) {
            return false;
        }

        $decoded = json_decode( (string) file_get_contents( $path ), true );
        if ( ! is_array( $decoded ) || ! array_key_exists( 'data', $decoded ) || empty( $decoded['time'] ) ) {
            return false;
        }

        if ( time() >= ( (int) $decoded['time'] + $this->ttl ) ) {
            return false;
        }

        return $decoded['data'];
    }

    public function get( $file ) {
        $file = sanitize_file_name( (string) $file );
        if ( $file === '' ) {
            return false;
        }

        $base = $this->uploads_base_dir();
        if ( $base === '' ) {
            return false;
        }

        $current = $this->read_file( $base . '/' . $this->directory . '/' . $file );
        if ( $current !== false ) {
            return $current;
        }

        // Temporary read-only bridge to the historical cache. This lets the
        // migration start without forcing fresh provider calls on deployment.
        return $this->read_file( $base . '/' . $this->legacy_directory . '/' . $file );
    }

    public function set( $file, $data ) {
        $file = sanitize_file_name( (string) $file );
        if ( $file === '' ) {
            return false;
        }

        $base = $this->uploads_base_dir();
        if ( $base === '' ) {
            return false;
        }

        $dir = $base . '/' . $this->directory;
        if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
            return false;
        }

        $payload = wp_json_encode(
            array(
                'time' => time(),
                'data' => $data,
            ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ( ! is_string( $payload ) ) {
            return false;
        }

        return file_put_contents( $dir . '/' . $file, $payload, LOCK_EX ) !== false;
    }
}
