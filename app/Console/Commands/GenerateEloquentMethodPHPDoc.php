<?php

namespace App\Console\Commands;

use stdClass;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GenerateEloquentMethodPHPDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ide-helper:eloquent-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate autocompletion for models extending an abstract model';

    protected $abstract_model = Command::class;
    protected $eloquent_model = Model::class;
    protected $eloquent_methods = [];
    protected $scraped_class = Builder::class;
    protected $scraped_methods = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getEloquentMethods();

        $scrapedInfo = new ReflectionClass($this->scraped_class);
        foreach ($scrapedInfo->getMethods() as $method) {
            if (!$this->isMagicMethod($method) && !$this->isModelMethod($method)) {
                $this->log('* @method static ', null, false);
                $docBlockTags = (new DocBlock($method))->getTags();

                $definition = $this->getMethodDefinition($method, $docBlockTags);

                $this->log("$definition->return ", null, false);

                $this->log(sprintf(
                    '%s(%s)',
                    $method->getName(),
                    implode($definition->params, ', ')
                ));
            }
        }
    }

    /**
     * Get all methods from the main Eloquent model class.
     */
    protected function getEloquentMethods()
    {
        $eloquentInfo = new ReflectionClass($this->eloquent_model);
        $eloquentMethods = $eloquentInfo->getMethods();
        foreach ($eloquentMethods as $method) {
            $this->eloquent_methods[] = $method->getName();
        }
    }

    /**
     * Naively check if a given method is a magic method.
     *
     * @param ReflectionMethod $method
     * @return bool true if method name stars with __, else false
     */
    protected function isMagicMethod($method)
    {
        return starts_with($method->getName(), '__');
    }

    /**
     * Check if a given method is a method in the main Eloquent model.
     *
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function isModelMethod($method)
    {
        return array_search($method->getName(), $this->eloquent_methods) !== false;
    }

    /**
     * Retrieves the parameters and return type for a given method.
     *
     * @param ReflectionMethod $method
     * @param Tag[]|Tag\ParamTag[]|Tag\ReturnTag[] $tags
     * @return \stdClass
     */
    protected function getMethodDefinition($method, $tags)
    {
        $definition = new stdClass();
        $definition->params = [];
        $definition->return = '';

        foreach ($tags as $tag) {
            if ($tag->getName() === 'param') {
                $definition->params[] = $this->getParameterDefinition($method, $tag);
            } elseif ($tag->getName() === 'return' && $tag->getType() !== 'void') {
                $definition->return = $this->getReturnDefinition($tag);
            }
        }

        return $definition;
    }

    /**
     * Get the parameter type, name, and default value.
     *
     * @param ReflectionMethod $method
     * @param Tag\ParamTag $tag
     * @return string
     */
    protected function getParameterDefinition($method, $tag)
    {
        $defaultValue = $this->getParameterDefault($method, $tag);
        $returnType = str_contains($tag->getType(), '|') ? 'mixed' : $tag->getType();
        $parameterName = $tag->getVariableName();

        return "$returnType $parameterName$defaultValue";
    }

    /**
     * Get the default value of a parameter;.
     *
     * @param ReflectionMethod $method
     * @param Tag\ParamTag $tag
     * @return string
     */
    protected function getParameterDefault($method, $tag)
    {
        $methodParameters = $method->getParameters();
        foreach ($methodParameters as $parameter) {
            if (sprintf('$%s', $parameter->getName()) === $tag->getVariableName()) {
                try {
                    $rawValue = var_export($parameter->getDefaultValue(), true);
                    $value = preg_replace('/\n+|\s+/', '', $rawValue);

                    return " = $value";
                } catch (ReflectionException $e) {
                }
            }
        }

        return '';
    }

    /**
     * Get the return type from the given return tag.
     *
     * @param Tag\ReturnTag $tag
     * @return string
     */
    protected function getReturnDefinition($tag)
    {
        if ($tag->getType() === '$this' || $tag->getType() === 'self') {
            return '\\'.$this->scraped_class;
        } else {
            return $tag->getType();
        }
    }
}
