<?php
/**
 * Default Data Loader
 *
 * Reads default-classes.json once per request and serves
 * the parsed data to any class that needs it.
 *
 * @package DynamicClassesElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DCE_Data_Loader {

    /**
     * In-memory cache so the file is read only once per request.
     *
     * @var array|null
     */
    private static $cache = null;

    /**
     * Return all default classes for a given type.
     *
     * @param  string $type  'gap' | 'padding' | 'margin' | 'min_height' | 'max_width'
     * @return array
     */
    public static function get( string $type ): array {
        self::maybe_load();
        return ( isset( self::$cache[ $type ] ) && is_array( self::$cache[ $type ] ) )
            ? self::$cache[ $type ]
            : [];
    }

    /**
     * Return the full decoded JSON array (useful for debugging / filters).
     *
     * @return array
     */
    public static function all(): array {
        self::maybe_load();
        return self::$cache ?? [];
    }

    /**
     * Load and decode the JSON file if not already cached.
     */
    private static function maybe_load(): void {
        if ( self::$cache !== null ) {
            return;
        }

        $file = DCE_PLUGIN_DIR . 'data/default-classes.json';

        if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
            self::$cache = [];
            error_log( 'DCE: default-classes.json not found at ' . $file );
            return;
        }

        $json    = file_get_contents( $file );
        $decoded = json_decode( $json, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            self::$cache = [];
            error_log( 'DCE: JSON parse error – ' . json_last_error_msg() );
            return;
        }

        /**
         * Allow developers to modify the default data after it is loaded.
         *
         * @param array $decoded  Parsed JSON data.
         */
        self::$cache = apply_filters( 'dce_default_data', $decoded );
    }
}
