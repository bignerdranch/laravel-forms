<?php namespace NpmWeb\FormBuilder;

use Config;
use Illuminate\Database\Eloquent\Model;
use Log;
use NpmWeb\FormBuilder\Renderers\RendererInterface;

class FormBuilder
    extends \NpmWeb\ClientValidationGenerator\Laravel\FormBuilder
{

    protected $renderer;

    private $default_col_width = 'large-6';
    private $default_row_per_field = false;
    private $default_css_framework = 'foundation';
    private $auto_placeholders = true;

    public function __construct(
        \Collective\Html\HtmlBuilder $html,
        \Illuminate\Routing\UrlGenerator $url,
        $csrfToken,
        RendererInterface $renderer)
    {
        parent::__construct($html,$url,$csrfToken);
        $this->renderer = $renderer;

        $this->default_col_width = Config::get('forms.col_width');
        $this->default_row_per_field = Config::get('forms.row_per_field');
        $this->default_css_framework = Config::get('forms.css_framework');
        $this->auto_placeholders = Config::get('forms.auto_placeholders');
    }

    /**
     * Open up a new HTML form.
     *
     * @param  array   $options
     * @return string
     */
    /*
    public function open(array $options = array())
    {
        $cssFramework = $this->default_css_framework;
        if( array_key_exists('css_framework',$options) ) {
            $cssFramework = $options->css_framework;
        }

        if( 'bootstrap' == $cssFramework ) {
            if( array_key_exists('class',$options) ) {
                $options['class'] .= ' form-horizontal';
            } else {
                $options['class'] = 'form-horizontal';
            }
        }

        return parent::open($options);
    }
    */

    /**
     * Display a field read-only
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function readonly($fieldname, $default = null, $options = [])
    {
        $label = ( isset($options['label']) ? $options['label'] : $this->label($fieldname) );
        $columns_class = ( isset($options['columns_class'] ) ? $options['columns_class'] : $this->default_col_width );

        // get model
        if( $this->model ) {
            $model = $this->model;
        } else {
            throw new \Exception('no model specified');
        }

        // format data
        $value = null;
        if( !is_null($default) ) {
            $value = $default;
        } elseif( property_exists($model, $fieldname) ) {
            $value = $model->$fieldname;
        } elseif( $model instanceof Model
            && array_key_exists( $fieldname, $model->getAttributes())
        ) {
            $value = $model->$fieldname;
        } elseif( $model instanceof Model
            && method_exists($model, $fieldname)
        ) {
            // relationship
            $value = $model->$fieldname;
        }
        if( array_key_exists('format',$options) ) {
            $format = $options['format'];
            switch($format) {
                case 'url':
                    if(isset($options['url'])) {
                        $callback = $options['url'];
                        $url = $callback($value);
                    } else {
                        $url = $value;
                    }
                    $value = '<a href="'.e($url).'" target="_blank">'.e($value).'</a>';
                    $options['escape'] = false;
                    break;
                case 'image':
                    if(isset($options['url'])) {
                        $callback = $options['url'];
                        $url = $callback($value);
                    } else {
                        $url = $value;
                    }
                    if( $value ) {
                        $value = '<img src="'.e($url).'" />';
                    } else {
                        $value = '(none)';
                    }
                    $options['escape'] = false;
                    break;
                case 'email':
                    $value = '<a href="mailto:'.e($value).'">'.e($value).'</a>';
                    $options['escape'] = false;
                    break;
                case 'checkbox':
                    $value = $value ? 'Yes' : 'No';
                    break;
                default: // date/time
                    if( $value ) {
                        $value = $value->format($format);
                    }
                    break;
            }
        }
        if( !array_key_exists('escape',$options) || true == $options['escape'] ) {
            $value = e($value);
        }
        $value = '<div><strong>'.$value.'</strong></div>';

        $config = $this->_processOptions($fieldname, 'readonly', $options);
        return $this->_outputHelper( $fieldname, $config, $value );
    }

    /**
     * Create a text input field.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function text($name, $default = null, $options = [])
    {
        //Log::debug(__METHOD__.'()');

        if( !$default && array_key_exists('format',$options) ) {
            $format = $options['format'];
            $value = $this->model->$name;
            switch($format) {
                case 'url':
                    $value = '<a href="'.e($value).'">'.e($value).'</a>';
                    break;
                case 'email':
                    $value = '<a href="mailto:'.e($value).'">'.e($value).'</a>';
                    break;
                default: // date/time
                    if( $value ) {
                        $value = $value->format($format);
                    }
                    break;
            }
            $default = $value;
        }

        $config = $this->_processOptions($name, 'text', $options);
        return $this->_outputHelper( $name, $config, parent::text($name, $default, $config->extras) );
    }

    /**
     * Create a password input field.
     *
     * @param  string  $name
     * @param  array   $options
     * @return string
     */
    public function password($name, $options = array())
    {
        //Log::debug(__METHOD__.'()');
        $config = $this->_processOptions($name, 'password', $options);
        return $this->_outputHelper( $name, $config,
            parent::password($name, $config->extras) );
    }

    /**
     * Create an e-mail input field.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function email($name, $value = null, $options = array())
    {
        //Log::debug(__METHOD__.'()');
        $config = $this->_processOptions($name, 'email', $options);
        return $this->_outputHelper( $name, $config,
            parent::email($name, $value, $config->extras) );
    }

    /**
     * Create a telephone input field.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function tel($name, $value = null, $options = array())
    {
        //Log::debug(__METHOD__.'()');
        $config = $this->_processOptions($name, 'tel', $options);
        return $this->_outputHelper( $name, $config,
            parent::input('tel', $name, $value, $config->extras) );
    }

    /**
     * Create a number input field.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function number($name, $value = null, $options = array())
    {
        //Log::debug(__METHOD__.'()');
        $config = $this->_processOptions($name, 'number', $options);
        return $this->_outputHelper( $name, $config,
            parent::input('number', $name, $value, $config->extras) );
    }

    /**
     * Create a file input field.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function file($name, $value = null, $options = array())
    {
        //Log::debug(__METHOD__.'()');
        $config = $this->_processOptions($name, 'file', $options);
        $value = $this->getValueAttribute($name,$value);
        $input = '';
        if( $value ) {
            $value = $this->getValueAttribute($name,$value);
            if( array_key_exists('format',$options) ) {
                switch($options['format']) {
                    case 'url':
                        $callback = $options['url'];
                        $url = $callback($value);
                        $value = '<a href="'.e($url).'" target="_blank">'.e($value).'</a>';
                        break;
                    case 'image':
                        if(isset($options['url'])) {
                            $callback = $options['url'];
                            $url = $callback($value);
                        } else {
                            $url = $value;
                        }
                        $value = '<img src="'.e($url).'" />';
                        break;
                    }
            }
            $input = 'Current: '.$value;
            if( isset($options['remove']) && $options['remove'] ) {
                $input .= '<br /><label>'.parent::checkbox($name.'_remove', 1).' Remove</label>';
            }
            // $model = $this->model;
            // if already uploaded, not "required" to upload a new one
            // $model::$rules[$name] = array_diff( $model::$rules[$name], ['required']);
            // var_dump($model::$rules);exit;
        }
        $input .= '<br />Upload New: '.parent::file($name, $config->extras);
        return $this->_outputHelper( $name, $config, $input );
    }

    /**
     * Create a textarea.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function textarea($name, $value = null, $options = array())
    {
        $config = $this->_processOptions($name, 'textarea', $options);
        return $this->_outputHelper( $name, $config,
            parent::textarea($name, $value, $config->extras) );
    }

    /**
     * Create a select box field.
     *
     * @param  string  $name
     * @param  array   $list
     * @param  string  $selected
     * @param  array   $options
     * @return string
     */
    public function select($name, $list = array(), $selected = null, $options = array())
    {
        $config = $this->_processOptions($name, 'select', $options);

        return $this->_outputHelper( $name, $config, parent::select($name, $list, $selected, $config->extras) );
    }

    /**
     * Create a radio button input field.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  bool    $checked
     * @param  array   $options
     * @return string
     */
    public function radio($name, $value = null, $checked = null, $options = array())
    {
        $config = $this->_processOptions($name, 'radio', $options);
        //$radioHtml = parent::radio($name, $value, $checked, $config->extras);
        $id = (array_key_exists('id', $config->extras) ? $config->extras['id'] : e($name.'_'.$value) );
        ob_start();
        ?>
        <input type="radio" name="<?php echo e($name) ?>" value="<?php echo e($value) ?>" id="<?php echo e($config->extras['id']) ?>"<?php if ($checked) echo ' checked="checked"'; ?>><span class="radio"><?php echo e($config->label); ?></span>
        <?php
        $control = ob_get_clean();
        return $this->_checkboxOrRadioOutputHelper( $name, $config, $control );
    }

    /**
     * Create a single checkbox input field.
     *
     * @see http://nielson.io/2014/02/handling-checkbox-input-in-laravel/
     * @param  string  $name
     * @param  mixed   $value
     * @param  bool    $checked
     * @param  array   $options
     * @return string
     */
    public function checkbox($name, $value = 1, $checked = null, $options = array())
    {
        $config = $this->_processOptions($name, 'checkbox', $options);
        //$radioHtml = parent::radio($name, $value, $checked, $config->extras);
        if( !array_key_exists('id', $config->extras) ) {
            $config->extras['id'] = $name.'_'.$value;
        }
        ob_start();
        ?>
            <?php echo parent::hidden( $name, false ) ?>
            <?php echo parent::checkbox( $name, $value, $checked, $config->extras ) ?>
        <?php
        $control = ob_get_clean();
        return $this->_checkboxOrRadioOutputHelper( $name, $config, $control );
    }


    // go through the options and pull out the pieces we're interested in
    protected function _processOptions( $name, $type, $options )
    {
        $config = new \stdClass();
        $config->label = ( isset($options['label']) ?
                            e($options['label']) :
                            ( isset($options['labelHtml']) ? $options['labelHtml'] : $this->formatLabel($name,null) )
                        );
        $config->columns_class = ( isset($options['columns_class'] ) ? $options['columns_class'] : $this->default_col_width );
        $config->row_per_field = ( isset($options['row_per_field'] ) ? $options['row_per_field'] : $this->default_row_per_field );
        // \Log::debug('processing options for '.$name);
        $config->extras = array_key_exists('extras',$options)
            ? $options['extras']
            : [];

        if (!array_key_exists('id', $config->extras) ) $config->extras['id'] = e( $name );
        if ($this->auto_placeholders && !array_key_exists('placeholder',$config->extras) ) {
            $config->extras['placeholder'] = $config->label;
        }


        $config->errors = array_key_exists('errors',$options)
            ? $options['errors']
            : null;

        if (array_key_exists('prefix', $options)) $config->prefix = $options['prefix'];
        if (array_key_exists('main', $options)) $config->main = $options['main'];
        if (array_key_exists('postfix', $options)) $config->postfix = $options['postfix'];
        if (array_key_exists('example', $options)) $config->example = $options['example'];
        if (array_key_exists('tooltip', $options)) $config->tooltip = $options['tooltip'];

        $model = $this->model;
        if( !array_key_exists( 'maxlength', $config->extras )
            && ($model && property_exists($model,'rules'))
            && ($model && is_array($model::$rules))
            && array_key_exists( $name, $model::$rules ) )
        {
            foreach( $model::$rules[$name] as $fieldRule ) {
                if( FALSE !== strpos($fieldRule,'max:') ) {
                    $config->extras['maxlength'] = substr($fieldRule,4);
                }
            }
        }

        $config = $this->renderer->processOptions( $config, $type );

        return $config;

    }

    protected function _outputHelper( $fieldname, $config, $control ) {
        $error = null;
        if( property_exists($config,'errors')
            && ($config->errors instanceof \Illuminate\Support\MessageBag
                || $config->errors instanceof \Illuminate\Support\ViewErrorBag)
        ) {
            $error = $config->errors->first($fieldname);
        }

        return $this->renderer->renderFormControl($fieldname, $config, $error, $control);
    }

    protected function _checkboxOrRadioOutputHelper( $fieldname, $config, $control ) {
        $error = null;
        if( property_exists($config,'errors')
            && ($config->errors instanceof \Illuminate\Support\MessageBag
                || $config->errors instanceof \Illuminate\Support\ViewErrorBag)
        ) {
            $error = $config->errors->first($fieldname);
        }

        return $this->renderer->renderCheckboxOrRadio($fieldname, $config, $error, $control);
    }

}
