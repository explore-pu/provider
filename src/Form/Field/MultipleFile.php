<?php

namespace Elegant\Admin\Form\Field;

use Elegant\Admin\Form;
use Elegant\Admin\Form\Field;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleFile extends Field
{
    use UploadField;

    /**
     * Css.
     *
     * @var array
     */
    protected static $css = [
        '/vendor/elegant-admin/bootstrap-fileinput/css/fileinput.min.css?v=4.5.2',
    ];

    /**
     * Js.
     *
     * @var array
     */
    protected static $js = [
        '/vendor/elegant-admin/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js',
        '/vendor/elegant-admin/bootstrap-fileinput/js/fileinput.min.js?v=4.5.2',
        '/vendor/elegant-admin/bootstrap-fileinput/js/plugins/sortable.min.js?v=4.5.2',
    ];

    /**
     * @var bool
     */
    protected $useCallbackUrl = false;

    /**
     * Create a new File instance.
     *
     * @param string $column
     * @param array $arguments
     * @throws \Exception
     */
    public function __construct($column, $arguments = [])
    {
        $this->initStorage();

        parent::__construct($column, $arguments);
    }

    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.file');
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(array $input)
    {
        if (!request()->hasFile($this->column)) {
            return false;
        }

        if ($this->validator) {
            return $this->validator->call($this, $input);
        }

        $attributes = [];

        if (!$fieldRules = $this->getRules()) {
            return false;
        }

        $attributes[$this->column] = $this->label;

        list($rules, $input) = $this->hydrateFiles(Arr::get($input, $this->column, []));

        return \validator($input, $rules, $this->getValidationMessages(), $attributes);
    }

    /**
     * Hydrate the files array.
     *
     * @param array $value
     *
     * @return array
     */
    protected function hydrateFiles(array $value)
    {
        if (empty($value)) {
            return [[$this->column => $this->getRules()], []];
        }

        $rules = $input = [];

        foreach ($value as $key => $file) {
            $rules[$this->column.'@'.$key] = is_object($file) ? $this->getRules() : 'string';
            $input[$this->column.'@'.$key] = $file;
        }

        return [$rules, $input];
    }

    /**
     * Sort files.
     *
     * @param $original
     * @return array
     */
    protected function sortFiles($original)
    {
        $fileSort = request(static::FILE_SORT_FLAG);
        $column = $fileSort[$this->column];

        if ($column) {
            $order = explode(',', $column);

            $new = [];

            foreach ($order as $item) {
                $new[] = Arr::get($original, $item);
            }

            return $new;
        } else {
            return $original;
        }
    }

    /**
     * Prepare for saving.
     *
     * @param UploadedFile|array $files
     *
     * @return mixed|string
     */
    public function prepare($files)
    {
        if (request()->has(static::FILE_DELETE_FLAG)) {
            if ($this->pathColumn) {
                return $this->destroyFromHasMany(request(static::FILE_DELETE_FLAG));
            }

            return $this->destroy(request(static::FILE_DELETE_FLAG));
        }

        // 将新旧数据分开
        $original = $uploadFiles = [];
        foreach ($files as $file) {
            if (is_object($file)) {
                array_push($uploadFiles, $file);
            } else {
                array_push($original, $file);
            }
        }

        if (request()->has(static::FILE_SORT_FLAG)) {
            $original = $this->sortFiles($original);
        }

        $targets = array_map([$this, 'prepareForeach'], $uploadFiles);

        // for create or update
        if ($this->pathColumn) {
            $targets = array_map(function ($target) {
                return [$this->pathColumn => $target];
            }, $targets);
        }

        return array_merge($original, $targets);
    }

    /**
     * @return array|mixed
     */
    public function original()
    {
        if (empty($this->original)) {
            return [];
        }

        return $this->original;
    }

    /**
     * Prepare for each file.
     *
     * @param UploadedFile $file
     *
     * @return mixed|string
     */
    protected function prepareForeach(UploadedFile $file = null)
    {
        $this->name = $this->getStoreName($file);

        return tap($this->upload($file), function () {
            $this->name = null;
        });
    }

    /**
     * Preview html for file-upload plugin.
     *
     * @return array
     */
    protected function preview()
    {
        $files = $this->value ?: [];

        return array_values(array_map([$this, 'objectUrl'], $files));
    }

    /**
     * Initialize the caption.
     *
     * @param array $caption
     *
     * @return string
     */
    protected function initialCaption($caption)
    {
        if (empty($caption)) {
            return '';
        }

        $caption = array_map('basename', $caption);

        return implode(',', $caption);
    }

    /**
     * @return array
     */
    protected function initialPreviewConfig()
    {
        $files = $this->value ?: [];

        $config = [];

        foreach ($files as $index => $file) {
            if (is_array($file) && $this->pathColumn) {
                $index = Arr::get($file, $this->getRelatedKeyName(), $index);
                $file = Arr::get($file, $this->pathColumn);
            }

            $preview = array_merge([
                'caption' => basename($file),
                'key'     => $index,
            ], $this->guessPreviewType($file));

            $config[] = $preview;
        }

        return $config;
    }

    /**
     * Get related model key name.
     *
     * @return string
     */
    protected function getRelatedKeyName()
    {
        if (is_null($this->form)) {
            return;
        }

        return $this->form->model()->{$this->column}()->getRelated()->getKeyName();
    }

    /**
     * Allow to sort files.
     *
     * @return $this
     */
    public function sortable()
    {
        $this->fileActionSettings['showDrag'] = true;

        return $this;
    }

    /**
     * @param bool $useCallbackUrl
     */
    public function useCallbackUrl($useCallbackUrl = true)
    {
        $this->useCallbackUrl = $useCallbackUrl;
    }

    /**
     * @param string $options
     */
    protected function setupScripts($options)
    {
        $this->script = <<<EOT
$("input{$this->getElementClassSelector()}").fileinput({$options});
EOT;

        if ($this->fileActionSettings['showRemove']) {
            $text = [
                'title'   => admin_trans('delete_confirm'),
                'confirm' => admin_trans('confirm'),
                'cancel'  => admin_trans('cancel'),
            ];

            $this->script .= <<<EOT
$("input{$this->getElementClassSelector()}").on('filebeforedelete', function(event, id) {
    var old_files_elm = $(this).parents('.file-input:first').nextAll('{$this->getElementClassSelector()}_old');
    var old_files = JSON.parse(old_files_elm.val());
    old_files.splice(id, 1);
    var old_files_val = JSON.stringify(old_files);

    return new Promise(function(resolve, reject) {
        var remove = resolve;
        swal({
            title: "{$text['title']}",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "{$text['confirm']}",
            showLoaderOnConfirm: true,
            cancelButtonText: "{$text['cancel']}",
            preConfirm: function() {
                return new Promise(function(resolve) {
                    resolve(remove());
                    old_files_elm.val(old_files_val);
                });
            }
        });
    });
});
EOT;
        }

        $this->addVariables([
            'old_flag' => static::FILE_OLD_FLAG,
        ]);

        if ($this->fileActionSettings['showDrag']) {
            $this->addVariables([
                'sortable'  => true,
                'sort_flag' => static::FILE_SORT_FLAG,
            ]);

            $this->script .= <<<EOT
$("input{$this->getElementClassSelector()}").on('filesorted', function(event, params) {

    var order = [];

    params.stack.forEach(function (item) {
        order.push(item.key);
    });

    $("input{$this->getElementClassSelector()}_sort").val(order);
});
EOT;
        }
    }

    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->attribute('multiple', true);

        $this->setupDefaultOptions();

        if (!empty($this->value)) {
            $this->options(['initialPreview' => $this->preview()]);
            $this->setupPreviewOptions();
        }

        $options = json_encode($this->options);

        $this->setupScripts($options);

        return parent::render();
    }

    /**
     * Destroy original files.
     *
     * @param string $key
     *
     * @return array
     */
    public function destroy($key)
    {
        $files = $this->original ?: [];

        $path = Arr::get($files, $key);

        if (!$this->retainable && $this->storage->exists($path)) {
            /* If this field class is using ImageField trait i.e MultipleImage field,
            we loop through the thumbnails to delete them as well. */

            if (isset($this->thumbnails) && method_exists($this, 'destroyThumbnailFile')) {
                foreach ($this->thumbnails as $name => $_) {
                    $this->destroyThumbnailFile($path, $name);
                }
            }
            $this->storage->delete($path);
        }

        unset($files[$key]);

        return $files;
    }

    /**
     * Destroy original files from hasmany related model.
     *
     * @param int $key
     *
     * @return array
     */
    public function destroyFromHasMany($key)
    {
        $files = collect($this->original ?: [])->keyBy($this->getRelatedKeyName())->toArray();

        $path = Arr::get($files, "{$key}.{$this->pathColumn}");

        if (!$this->retainable && $this->storage->exists($path)) {
            $this->storage->delete($path);
        }

        $files[$key][Form::REMOVE_FLAG_NAME] = 1;

        return $files;
    }
}
