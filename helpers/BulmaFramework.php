<?php
namespace Helpers;

use Former\Interfaces\FrameworkInterface;
use Former\Traits\Field;
use Former\Traits\Framework;
use HtmlObject\Element;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * The Twitter Bootstrap form framework
 */
class BulmaFramework extends Framework implements FrameworkInterface
{
    /**
     * Form types that trigger special styling for this Framework
     *
     * @var array
     */
    protected $availableTypes = ['horizontal', 'vertical', 'inline', 'search'];

    /**
     * The button types available
     *
     * @var array
     */
    private $buttons = [
        'large',
        'small',
        'mini',
        'block',
        'danger',
        'info',
        'inverse',
        'link',
        'primary',
        'success',
        'warning',
    ];

    /**
     * The field sizes available
     *
     * @var array
     */
    private $fields = [
        'mini',
        'small',
        'medium',
        'large',
        'xlarge',
        'xxlarge',
        'span1',
        'span2',
        'span3',
        'span4',
        'span5',
        'span6',
        'span7',
        'span8',
        'span9',
        'span10',
        'span11',
        'span12',
    ];

    /**
     * The field states available
     *
     * @var array
     */
    protected $states = [
        'success',
        'warning',
        'error',
        'info',
    ];

    /**
     * Create a new TwitterBootstrap instance
     *
     * @param \Illuminate\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->setFrameworkDefaults();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// FILTER ARRAYS //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Filter buttons classes
     *
     * @param  array $classes An array of classes
     *
     * @return string[] A filtered array
     */
    public function filterButtonClasses($classes)
    {
        // Filter classes
        // $classes = array_intersect($classes, $this->buttons);

        // Prepend button type
        $classes = $this->prependWith($classes, 'is-');
        $classes[] = 'button';

        return $classes;
    }

    /**
     * Filter field classes
     *
     * @param  array $classes An array of classes
     *
     * @return array A filtered array
     */
    public function filterFieldClasses($classes)
    {
        // Filter classes
        $classes = array_intersect($classes, $this->fields);

        // Prepend field type
        $classes = array_map(function ($class) {
            return Str::startsWith($class, 'span') ? $class : 'is-'.$class;
        }, $classes);

        return $classes;
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// ADD CLASSES //////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Add classes to a field
     *
     * @param Field $field
     * @param array $classes The possible classes to add
     *
     * @return Field
     */
    public function getFieldClasses(Field $field, $classes)
    {
        // Add inline class for checkables
        if ($field->isCheckable() and in_array('inline', $classes)) {
            $field->inline();
        }

        // Filter classes according to field type
        if ($field->isButton()) {
            $classes = $this->filterButtonClasses($classes);
        } else {
            $classes = $this->filterFieldClasses($classes);
        }

        //array_push($classes, 'is-small');

        if (in_array($field->getType(), ['text', 'password'])) {
            $classes[] = 'input';
        }

        return $this->addClassesToField($field, $classes);
    }

    /**
     * Add group classes
     *
     * @return string A list of group classes
     */
    public function getGroupClasses()
    {
        return 'field';
    }

    /**
     * Add label classes
     *
     * @return string An array of attributes with the label class
     */
    public function getLabelClasses()
    {
        return 'label';
    }

    /**
     * Add uneditable field classes
     *
     * @return string An array of attributes with the uneditable class
     */
    public function getUneditableClasses()
    {
        return 'uneditable-input';
    }

    public function getPlainTextClasses()
    {
        return null;
    }

    /**
     * Add form class
     *
     * @param  string $type The type of form to add
     *
     * @return string|null
     */
    public function getFormClasses($type)
    {
        return $type ? 'form-'.$type : null;
    }

    /**
     * Add actions block class
     *
     * @return string
     */
    public function getActionClasses()
    {
        return 'form-actions';
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// RENDER BLOCKS /////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Render an help text
     *
     * @param string $text
     * @param array  $attributes
     *
     * @return Element
     */
    public function createHelp($text, $attributes = [])
    {
        return Element::create('span', $text, $attributes)->addClass('help');
    }

    /**
     * Render a block help text
     *
     * @param string $text
     * @param array  $attributes
     *
     * @return Element
     */
    public function createBlockHelp($text, $attributes = [])
    {
        return Element::create('p', $text, $attributes)->addClass('help');
    }

    /**
     * Render a disabled field
     *
     * @param Field $field
     *
     * @return Element
     */
    public function createDisabledField(Field $field)
    {
        return Element::create('span', $field->getValue(), $field->getAttributes());
    }

    /**
     * Render a plain text field
     * Which fallback to a disabled field
     *
     * @param Field $field
     *
     * @return Element
     */
    public function createPlainTextField(Field $field)
    {
        return $this->createDisabledField($field);
    }

    /**
     * Render an icon
     *
     * @param array $attributes Its general attributes
     *
     * @return string
     */
    public function createIcon($iconType, $attributes = [], $iconSettings = [])
    {
        // Check for empty icons
        if (!$iconType) {
            return false;
        }

        // Create tag
        $tag = array_get($iconSettings, 'tag', $this->iconTag);
        $icon = Element::create($tag, null, $attributes);

        // White icons ignore user overrides to use legacy Bootstrap styling
        if (Str::contains($iconType, 'white')) {
            $iconType = str_replace('white', '', $iconType);
            $iconType = trim($iconType, '-');
            $icon->addClass('icon-white');
            $set = null;
            $prefix = 'icon';
        } else {
            $set = array_get($iconSettings, 'set', $this->iconSet);
            $prefix = array_get($iconSettings, 'prefix', $this->iconPrefix);
        }
        $icon->addClass("$set $prefix-$iconType");

        return $icon;
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// WRAP BLOCKS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Wrap an item to be prepended or appended to the current field
     *
     * @param  string $item
     *
     * @return string A wrapped item
     */
    public function placeAround($item)
    {
        // Render object
        if (is_object($item) and method_exists($item, '__toString')) {
            $item = $item->__toString();
        }

        // Return unwrapped if button
        if (strpos($item, '<button') !== false) {
            return $item;
        }

        return Element::create('span', $item)->addClass('add-on');
    }

    /**
     * Wrap a field with prepended and appended items
     *
     * @param  Field $field
     * @param  array $prepend
     * @param  array $append
     *
     * @return string A field concatented with prepended and/or appended items
     */
    public function prependAppend($field, $prepend, $append)
    {
        $class = [];
        if ($prepend) {
            $class[] = 'input-prepend';
        }
        if ($append) {
            $class[] = 'input-append';
        }

        $return = '<div class="'.join(' ', $class).'">';
        $return .= join(null, $prepend);
        $return .= $field->render();
        $return .= join(null, $append);
        $return .= '</div>';

        return $return;
    }

    /**
     * Wrap a field with potential additional tags
     *
     * @param  Field $field
     *
     * @return Element A wrapped field
     */
    public function wrapField($field)
    {
        if (strpos($field, 'checkbox') !== false) {
            return Element::create('div', $field)->addClass('control');
        } elseif (strpos($field, '<select') !== false) {
            return Element::create('div', $field)->addClass('select');
        } else {
            return Element::create('div', $field)->addClass('control');
        }
    }

    /**
     * Wrap actions block with potential additional tags
     *
     * @param  Actions $actions
     *
     * @return string A wrapped actions block
     */
    public function wrapActions($actions)
    {
        return $actions;
    }
}
