<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 8:54
 *
 */

namespace Badtomcat\View;


use InvalidArgumentException;

class Badtomcat
{
    /**
     * All of the finished, captured sections.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * 可以用于SECTION 中 嵌套 SECTION
     * 用于END SECTION时知道是哪个SECTION
     * The stack of in-progress sections.
     *
     * @var array
     */
    protected $sectionStack = [];

    /***
     * @var array
     */
    protected $yields = [];
    /**
     * The parent placeholder for the request.
     *
     * @var array
     */
    protected static $parentPlaceholder = [];

    protected $viewFile;

    protected $data = [];

    public $response = '';
    public function __construct($path)
    {
        $this->viewFile = $path;
    }

    public function extend($path)
    {
        $path = $this->fixPath($path);
        if (file_exists($path))
        {
            extract($this->data);
            ob_start();
            include $path;
            $this->response = ob_get_clean();
        }
    }

    protected function fixPath($path)
    {
        if (strpos($path,"/") === false)
        {
            return dirname($this->viewFile)."/$path";
        }
        return $path;
    }

    /**
     * Start injecting content into a section.
     *
     * @param  string $section
     * @param  string|null $content
     * @return void
     */
    public function start($section, $content = null)
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content);
        }
    }

    /**
     * Stop injecting content into a section.
     *
     * @param  bool $overwrite
     * @return string
     * @throws \InvalidArgumentException
     */
    public function end($overwrite = true)
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    /**
     * Stop injecting content into a section and append it.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function appendSection()
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    /**
     * Append content to a given section.
     *
     * @param  string $section
     * @param  string $content
     * @param bool $prepend
     * @return void
     */
    protected function extendSection($section, $content, $prepend = true)
    {
        if (isset($this->sections[$section])) {
            if ($prepend)
            {
                $content = $this->sections[$section] . $content;
            }
            else
            {
                $content = $content . $this->sections[$section];
            }
        }

        $this->sections[$section] = $content;
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string $section
     * @param  string $default
     */
    public function yieldContent($section, $default = '')
    {
        if (!$this->hasSection($section))
            $this->sections[$section] = $default;
        $this->yields[$section] = static::parentPlaceholder($section);
        print $this->yields[$section];
    }

    /**
     * Get the parent placeholder for the current request.
     *
     * @param  string $section
     * @return string
     */
    public static function parentPlaceholder($section = '')
    {
        if (!isset(static::$parentPlaceholder[$section])) {
            static::$parentPlaceholder[$section] = '##parent-placeholder-' . sha1($section) . '##';
        }

        return static::$parentPlaceholder[$section];
    }

    /**
     * Check if section exists.
     *
     * @param  string $name
     * @return bool
     */
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * Get the contents of a section.
     *
     * @param  string $name
     * @param  string $default
     * @return mixed
     */
    public function getSection($name, $default = null)
    {
        return isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }

    /**
     * Get the entire array of sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Flush all of the sections.
     *
     * @return void
     */
    public function flushSections()
    {
        $this->sections = [];
        $this->sectionStack = [];
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        if (file_exists($this->viewFile)) {
            extract($this->data);
            ob_start();
            include $this->viewFile;
            $this->response .= ob_get_clean();
        } else {
            return "{$this->viewFile} is not exists";
        }
        $placeholders = [];
        foreach ($this->yields as $key => $value)
        {
            $placeholders[$value] = $this->sections[$key];
        }
        return strtr($this->response,$placeholders) ;
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return $this->viewFile;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }
}