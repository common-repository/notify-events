<?php

namespace notify_events\models;

use notify_events\helpers\Html;
use notify_events\helpers\Inflector;

/**
 * Class Core
 * @package notify_events
 */
class Core
{
    /** @var array */
    protected $_modules = [];

    /** @var static */
    protected static $_instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static;
        }

        return static::$_instance;
    }

    /**
     * Core constructor.
     */
    public function __construct()
    {
        if (is_admin()) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            add_action('init', function() {
                ob_start();
            });

            add_filter('plugin_action_links', [$this, 'plugin_action_links'], 10, 2);
            add_filter('plugin_row_meta', [$this, 'register_plugin_links'], 10, 2);

            add_action('admin_menu', function() {
                add_options_page(__('Notify.Events', WPNE), __('Notify.Events', WPNE), 'manage_options', WPNE, [$this, 'route']);
            });

            Alert::register_post_type();
        }

        Channel::register_post_type();
        Event::register_post_type();

        do_action('wpne_init');

        add_action('plugins_loaded', function() {
            do_action('wpne_module_init');
        });
    }

    /**
     * @param string $plugin
     * @return boolean
     */
    public static function is_plugin_active($plugin)
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active( $plugin );
    }

    /**
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_action_links($links, $file)
    {
        if ($file == 'notify-events/notify-events.php') {
            $settings_link = Html::a(__('Settings', WPNE), 'options-general.php?page=notify-events');

            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * @param array $links
     * @param string $file
     * @return array
     */
    public function register_plugin_links($links, $file)
    {
        if ($file == 'notify-events/notify-events.php') {
            $links[] = Html::a(__('Settings', WPNE), 'options-general.php?page=notify-events');
        }

        return $links;
    }

	protected static function get_route_param($name, $default)
	{
		if (!array_key_exists($name, $_GET)) {
			return $default;
		}

		if (!is_string($_GET[$name])) {
			return $default;
		}

		return sanitize_key($_GET[$name]);
	}

    /**
     *
     */
    public function route()
    {
        $module_name     = static::get_route_param('module', 'notify_events');
	    $controller_name = static::get_route_param('controller', 'about');
	    $action_name     = static::get_route_param('action', 'index');

        $controller_name = Inflector::class_from_id($controller_name);

        $controller_class = $module_name . '\\controllers\\' . $controller_name . 'Controller';

        if (!class_exists($controller_class)) {
            wp_die(sprintf(__('Controller "%s" not found!', WPNE), esc_html($controller_class)));
        }

        /** @var Controller $controller */
        $controller = new $controller_class;

        echo $controller->do_action($action_name);
    }

    /**
     * @param Module $module
     */
    public function module_register($module)
    {
        $this->_modules[$module::module_name()] = $module;
    }

    /**
     * @return array
     */
    public function module_list()
    {
        $modules = $this->_modules;

        usort($modules, function($module_a, $module_b) {
            /** @var Module $module_a */
            /** @var Module $module_b */

            return ($module_a::module_order() > $module_b::module_order()) ? 1 : -1;
        });

        return $modules;
    }

    /**
     * @param string $module_name
     * @return Module
     */
    public function module_get($module_name)
    {
        if (!array_key_exists($module_name, $this->_modules)) {
            wp_die(__('Can\'t find module: ') . $module_name);
        }

        return $this->_modules[$module_name];
    }

    /**
     * @return array
     */
    public static function channel_list()
    {
        $result = [];

        $channels = Channel::find();

        foreach ($channels as $channel) {
            $result[$channel->id] = $channel->title;
        }

        return $result;
    }
}
