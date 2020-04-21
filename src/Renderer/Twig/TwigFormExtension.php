<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 03/02/2019
 * Time: 13:35
 */

namespace Keiryo\Renderer\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigFormExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('form_open', [$this, 'open']),
            new TwigFunction('form_widget', [$this, 'widget'], ['needs_environment' => true, 'needs_context' => true]),
            new TwigFunction('form_field', [$this, 'field'], ['needs_environment' => true, 'needs_context' => true]),
            new TwigFunction('form_error', [$this, 'error'], ['needs_environment' => true, 'needs_context' => true]),
            new TwigFunction('form_label', [$this, 'label']),
            new TwigFunction('form_submit', [$this, 'submit']),
            new TwigFunction('form_close', [$this, 'close'])
        ];
    }

    /**
     * Render opening tag of a form
     *
     * @param array $attributes
     */
    public function open(array $attributes)
    {
        $attrs = [];
        foreach ($attributes as $name => $value) {
            $attrs[] = "$name=$value";
        }

        $attrs = implode(' ', $attrs);

        echo <<<HTML
<form $attrs>
HTML;
    }

    /**
     * Render closing tag of a form
     */
    public function close()
    {
        echo '</form>';
    }

    /**
     * @param string $title
     * @param string $for
     * @param array $attributes
     */
    public function label(string $title, string $for, array $attributes = [])
    {
        $classes = $attributes['class'] ?? '';
        echo "<label for='$for' class='form-label $classes'> $title </label>";
    }

    /**
     * @param Environment $env
     * @param array $context
     * @param string $name
     */
    public function error(Environment $env, array $context, string $name)
    {
        $output = <<<HTML
{% if errors.has('$name') %}
    <span class="text-error">{{ errors.get('$name') }}</span>
{% endif %}
HTML;

        echo $env->createTemplate($output)->render($context);
    }

    /**
     * @param Environment $env
     * @param array $context
     * @param string $type
     * @param string $name
     * @param string $label
     * @param null $value
     * @param array $attributes
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function widget(
        Environment $env,
        array $context,
        string $type,
        string $name,
        string $label,
        $value = null,
        array $attributes = []
    )
    {
        switch ($type) {
            case 'checkbox':
                echo $this->checkbox($name, $label, $attributes['switch'] ?? false);
                return;
            case 'textarea':
                $field = $this->textarea($name, $value, $attributes);
                break;
            default:
                $field = $this->input($type, $name, $value);
                break;
        }

        $output = <<<HTML
<div class="form-group column">
    <label for="$name" class="form-label"> $label </label>
    {% if errors.has('$name') %}
        <span class="text-error">{{ errors.get('$name') }}</span>
    {% endif %}
    $field
</div>
HTML;

        echo $env->createTemplate($output)->render($context);
    }

    /**
     * Creates a checkbox
     *
     * @param string $name
     * @param string $text
     * @param bool $switch
     * @return string
     */
    public function checkbox(string $name, string $text, bool $switch = true)
    {
        $class = $switch ? 'form-switch' : 'form-checkbox';

        return <<<HTML
<div class="form-group flex-centered">
    <label class="$class">
        <input type="checkbox" name="$name">
            <i class="form-icon"></i> $text
    </label>
</div>
HTML;
    }

    /**
     * @param string $name
     * @param null $value
     * @param array $attributes
     * @return string
     */
    public function textarea(string $name, $value = null, array $attributes = []): string
    {
        $cols = $attributes['cols'] ?? 30;
        $rows = $attributes['rows'] ?? 5;

        return <<<HTML
<textarea name="$name" id="$name" cols="$cols" rows="$rows" class="form-input{% if errors.has('$name') %} is-error{% endif %}">$value</textarea>
HTML;
    }

    /**
     * Generates an input field
     *
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function input(string $type, string $name, $value = null, array $attributes = [])
    {
        $classes = $attributes['class'] ?? '';
        if ($value) {
            return <<<HTML
<input type="$type" id="$name" name="$name" value="$value" class="form-input $classes{% if errors.has('$name') %} is-error{% endif %}">
HTML;
        } else {
            return <<<HTML
<input type="$type" id="$name" name="$name" class="form-input $classes{% if errors.has('$name') %} is-error{% endif %}">
HTML;
        }
    }

    /**
     * Adds a submit button
     *
     * @param string $value
     * @param array $attributes
     */
    public function submit(string $value, array $attributes = [])
    {
        $class = $attributes['class'] ?? '';
        $class = "form-group column $class";
        echo <<<HTML
<div class="$class">
    <button class="btn btn-primary">$value</button>
</div>
HTML;
    }

    public function field(Environment $env, array $context, string $type, string $name, $value = null, array $attributes = [])
    {
        switch ($type) {
            case 'checkbox':
                $class = isset($attributes['switch']) ? 'form-switch' : 'form-checkbox';
                $checked = ($attributes['checked'] ?? false) ? 'checked' : '';
                $label = $attributes['label'] ?? '';
                $value = $value ? "value=$value" : '';
                $field = <<<HTML
<label class="$class">
    <input type="checkbox" name="$name" id="$name" $value $checked>
            <i class="form-icon"></i>$label
</label>
HTML;
                break;
            case 'textarea':
                $field = $this->textarea($name, $value, $attributes);
                break;
            default:
                $field = $this->input($type, $name, $value);
                break;
        }

        echo $env->createTemplate($field)->render($context);
    }
}
