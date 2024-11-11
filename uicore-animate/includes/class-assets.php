<?php

namespace UiCoreAnimate;

/**
 * Scripts and Styles Class
 */
class Assets
{

    function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'register'], 5);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'register'], 5);
        }
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'register'], 1);


        add_action('elementor/editor/after_enqueue_scripts', [$this, 'register'], 1);

        if (\class_exists('\UiCoreBlocks\Base')) {
            // add inline script and styles to gutenberg editor
            add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register()
    {
        $this->register_scripts($this->get_scripts());
        $this->register_styles($this->get_styles());
        //add animations if is elementor editor
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
?>
            <script>
                var uicore_animations_list = <?php echo wp_json_encode(\Elementor\Control_Animation::get_animations()); ?>;
                var uicore_split_animations_list = <?php echo wp_json_encode(Helper::get_split_animations_list()); ?>;
            </script>
        <?php
        }
    }


    function enqueue_block_assets()
    {
        if (! is_admin()) {
            return;
        }

        if (is_customize_preview()) {
            return;
        }
        $list = Helper::get_animations_list();
        $animations = [];
        foreach ($list as $value => $label) {
            $animations[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        $style = Settings::get_option('uianim_style');
        if (is_array($style)) {
            $style = $style['value'];
        } else {
            $style = 'style1';
        }
        wp_enqueue_style('uianim-style', UICORE_ANIMATE_ASSETS . '/css/' . $style . '.css');

        \wp_enqueue_script('uicore_animate-editor');
        \wp_add_inline_script('uicore_animate-editor', 'var uicore_animations_list = ' . wp_json_encode($animations) . ';');
        ?>
        <script>
            var uicore_animations_list = <?php echo wp_json_encode($animations); ?>;
        </script>
        <style>
            .uicore-animate-panel h2 button::after {
                content: "UiCore";
                font-size: 11px;
                font-weight: 500;
                background: #5dbad8;
                color: black;
                padding: 2px 5px;
                border-radius: 3px;
                margin-left: 8px;
            }
        </style>
<?php
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts($scripts)
    {
        foreach ($scripts as $handle => $script) {
            $deps      = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version   = isset($script['version']) ? $script['version'] : UICORE_ANIMATE_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles($styles)
    {
        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;

            wp_register_style($handle, $style['src'], $deps, UICORE_ANIMATE_VERSION);
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $editor_data = require UICORE_ANIMATE_PATH . '/assets/build/editor.asset.php';
        $scripts = [
            'uicore_animate-vendor' => [
                'src'       => UICORE_ANIMATE_ASSETS . '/js/vendor' . $prefix . '.js',
                'version'   => UICORE_ANIMATE_VERSION,
                'in_footer' => true
            ],
            'uicore_animate-settings' => [
                'src'       => UICORE_ANIMATE_ASSETS . '/js/settings' . $prefix . '.js',
                'deps'      => ['jquery', 'uicore_animate-vendor'],
                'version'   => UICORE_ANIMATE_VERSION,
                'in_footer' => true
            ],
            'uicore_animate-admin' => [
                'src'       => UICORE_ANIMATE_ASSETS . '/js/admin' . $prefix . '.js',
                'deps'      => ['jquery', 'uicore_animate-vendor'],
                'version'   => UICORE_ANIMATE_VERSION,
                'in_footer' => true
            ],
            'uicore_animate-editor' => [
                'src'       =>  UICORE_ANIMATE_ASSETS . '/build/editor.js',
                'deps'      => $editor_data['dependencies'],
                'version'   => $editor_data['version'],
                'in_footer' => true
            ]
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles()
    {

        $styles = [
            'uicore_animate-settings' => [
                'src' =>  UICORE_ANIMATE_ASSETS . '/css/settings.css'
            ],
            'uicore_animate-admin' => [
                'src' =>  UICORE_ANIMATE_ASSETS . '/css/admin.css'
            ],
        ];

        return $styles;
    }
}
