<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;


use BIT\EMS\Controller\AbstractBaseController;

abstract class AbstractShortcodeController extends AbstractBaseController
{
    /**
     * Shortcode aliases. Normally the class name (TestClass => [ems_test_class]) is used.
     * But sometimes it makes sense to use a shorter name or use another name for backward compatibility
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * @param array $atts
     * @param string|null $content
     * @return void
     */
    abstract public function printContent($atts = [], $content = null);

    /**
     * @param array $atts
     * @param string|null $content
     * @return string
     */
    public function getContent($atts = [], $content = null)
    {
        ob_start();
        $this->printContent();
        return ob_get_clean();
    }

    /**
     * Register class as shortcode
     */
    public function register()
    {
        $shortcodes = array_merge([$this->getShortcode()], $this->aliases);

        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode, [
                $this,
                'getContent'
            ]);
        }

        // Enqueue scripts/styles if short code is found in posts: https://wpgurus.net/enqueue-scripts-style-sheets-on-shortcode-pages/
        add_action('the_posts', [$this, 'hasShortcodeAndEnqueueAssets']);
    }

    /**
     * Check if one of the used posts contains the current shortcode and enqueue needed assets in this case
     * Idea from https://wpgurus.net/enqueue-scripts-style-sheets-on-shortcode-pages/
     * @param $posts
     * @return array
     */
    public function hasShortcodeAndEnqueueAssets($posts)
    {
        if (empty($posts) || is_admin() || !is_array($posts)) {
            return $posts;
        }

        $shortcodes = array_merge([$this->getShortcode()], $this->aliases);

        foreach ($posts as $post) {
            foreach ($shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    $this->enqueueAssets();
                    break;
                }
            }
        }

        return $posts;
    }

    /**
     * Converts class name to short code
     * Example: Class name ParticipantListController Return value: ems_participant_list
     * @return string
     */
    protected function getShortcode()
    {
        $shortClassName = (new \ReflectionClass($this))->getShortName();
        return \Ems_Conf::PREFIX . strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortClassName));
    }
}