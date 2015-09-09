laravel-forms
=============

Extends the `laravelcollective/html` FormBuilder object (Form:: facade) to automatically output form fields with Foundation- or Bootstrap-specific wrapping markup around them.

Example
=======

With `foundationBasicGrid` selected, the following call:

    {{ Form::text('name',null,['errors'=>$errors]) }}

Will output:

    <div class="row">
        <div class="medium-12 columns">
            <label for="name">
            	Name
                <input id="name" placeholder="Name" maxlength="150"
                    name="name" type="text" value="Parent Organization">
            </label>
        </div>
    </div>

If there is a validation error on the field, it will output:

    <div class="row">
        <div class="medium-12 columns">
            <label for="name">
            	Name
                <input id="name" placeholder="Name" maxlength="150"
                    name="name" type="text" value="Parent Organization">
                <small class="error">The img field is required.</small>
            </label>            
        </div>
    </div>

You can easily change the grid column classes, and if you use `bootstrapBasicGrid` the equivalent Bootstrap 3 markup will be used.

Installation
============

1. Require `"npmweb/laravel-forms": "~1.0"` in your `composer.json` file.
2. Run `composer install` or `composer update` to download it and have the autoloader updated.3. 
3. Open `app/config/app.php` and make the following changes under the `providers` key:
	a. If `'Collective\Html\HtmlServiceProvider'` is there, comment it out
	b. Add `'NpmWeb\FormBuilder\HtmlServiceProvider'`

Configuration
=============

You will probably want to change the default configuration. First, publish the package config file:

    $ php artisan config:publish npmweb/laravel-forms
    
The following options are available:

- `'driver'`: Defaults to `'foundationBasicGrid'` (Foundation 5) but can also be set to `'bootstrapBasicGrid'` (Bootstrap 3). This changes what markup is generated to fit with the given CSS framework's default form layout.
- `'col_width'`: The grid class for the form field's container element. For example, in Foundation `'large-6'` will make the container take up 6 out of 12 columns.
- `'row_per_field'`: Defaults to `false`, in which case each form field is outputted directly, and you have to wrap it in a row yourself. If you set it to `true`, each form field container is wrapped in a row, forcing it to be one field per row.

Here's some more information about the included drivers:

- **foundationBasicGrid**: Uses the basic [Foundation form markup](http://foundation.zurb.com/docs/components/forms.html), with a `<label>` tag wrapping the inputs. Layed out within a grid row and column.
- **bootstrapBasicGrid**: Uses the basic [Bootstrap form markup](http://getbootstrap.com/css/#forms-example), with  a `<label>` tag as a prior-child of the input. Layed out within a grid row and column.


Usage
=====

Just use any of Laravel's normal `Form::` methods, such as `Form::text()` or `Form::select()`. Instead of outputting the bare form control, it will also output the wrapping DOM elements appropriate to your selected CSS framework.

Additionally, a `Form::readonly()` method has been added. This outputs a read-only value wrapped in the same DOM elements, allowing you to include read-only "fields" in your forms.

License
=======

This code is released under the MIT License. See the LICENSE file for details.